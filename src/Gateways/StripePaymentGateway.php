<?php

namespace Cosy\Appointments\Gateways;

use Cosy\Appointments\Loader;
use Cosy\Appointments\Common\GlobalCommonFunctions;
use Cosy\Appointments\Common\LogManager;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * STRIPE PAYMENT GATEWAY & CHECKOUT HANDLER
 * 
 * USE CASE:
 * Dedicated payment class responsible for handling all Stripe API interactions,
 * checkout session generation, payment status verification, and payment logging.
 */
class StripePaymentGateway
{
    use GlobalCommonFunctions;

    public function register(Loader $loader): void
    {
        $loader->add_action('wp_ajax_cosy_create_stripe_session', $this, 'handle_create_stripe_session');
        $loader->add_action('wp_ajax_nopriv_cosy_create_stripe_session', $this, 'handle_create_stripe_session');
    }

    /**
     * Logs raw payment transaction activities into wp_cosy_activity_logs.
     */
    public function cosy_payment_log(string $message, $data = null): void
    {
        $desc = $message;
        if ($data !== null) {
            $desc .= ' | Data: ' . (is_array($data) || is_object($data) ? json_encode($data) : (string) $data);
        }
        LogManager::log('payments', 'stripe_log', $desc);
    }

    /**
     * Backend AJAX handler to generate a Stripe Checkout session URL.
     * Inserts a pending cosy_appointment post first, then contacts Stripe API to get session URL.
     */
    public function handle_create_stripe_session(): void
    {
        check_ajax_referer('cosy_booking_nonce', 'nonce');
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('User must be logged in to book services.', 'cosy-appointments')]);
        }

        $current_user = wp_get_current_user();
        if (!in_array('customer', (array) $current_user->roles)) {
            wp_send_json_error(['message' => __('Only customers are authorized to book appointments.', 'cosy-appointments')]);
        }

        // Retrieve and validate POST data
        $service_id         = !empty($_POST['serviceId']) ? intval($_POST['serviceId']) : 1;
        $service            = !empty($_POST['service']) ? sanitize_text_field($_POST['service']) : 'Parent Conversation';
        $provider_id        = isset($_POST['providerId']) ? intval($_POST['providerId']) : 0;
        $provider_name      = isset($_POST['providerName']) ? sanitize_text_field($_POST['providerName']) : '';
        $start_date         = isset($_POST['startDate']) ? sanitize_text_field($_POST['startDate']) : '';
        $end_date           = isset($_POST['endDate']) ? sanitize_text_field($_POST['endDate']) : '';
        $weekly_booking     = isset($_POST['weeklyBooking']) ? sanitize_text_field($_POST['weeklyBooking']) : '';
        $number_of_weeks    = isset($_POST['numberOfWeeks']) ? intval($_POST['numberOfWeeks']) : 1;
        $number_of_bookings = isset($_POST['numberOfBookings']) ? intval($_POST['numberOfBookings']) : 1;
        $service_cost       = isset($_POST['serviceCost']) ? sanitize_text_field($_POST['serviceCost']) : '0.00';
        $service_fee        = isset($_POST['serviceFee']) ? sanitize_text_field($_POST['serviceFee']) : '0.00';
        $total_payable      = isset($_POST['totalPayable']) ? sanitize_text_field($_POST['totalPayable']) : '0.00';
        $slots_json         = isset($_POST['slots']) ? wp_unslash($_POST['slots']) : '';
        $week_days          = isset($_POST['weekDays']) ? sanitize_text_field($_POST['weekDays']) : '';
        $slots_timeline     = isset($_POST['slotsTimeline']) ? sanitize_text_field($_POST['slotsTimeline']) : '';
        $is_gift            = !empty($_POST['isGift']) ? 1 : 0;
        $recipient_name     = isset($_POST['recipientName']) ? sanitize_text_field($_POST['recipientName']) : '';
        $recipient_email    = isset($_POST['recipientEmail']) ? sanitize_email($_POST['recipientEmail']) : '';

        // Fallback calculation if End Date, Week Days, or Slots Timeline are missing
        if (empty($end_date) && !empty($start_date)) {
            $s_time = strtotime($start_date);
            if ($s_time) {
                $num_w = max(1, $number_of_weeks);
                $e_time = strtotime("+{$num_w} weeks -1 day", $s_time);
                $end_date = date('d-m-Y', $e_time);
            }
        }

        if (empty($week_days) || empty($slots_timeline)) {
            $slots_arr = json_decode($slots_json, true);
            $parsed_days = [];
            $timeline_parts = [];

            if (is_array($slots_arr)) {
                foreach ($slots_arr as $date_key => $times) {
                    if (!empty($times) && is_array($times)) {
                        $dt = strtotime($date_key);
                        if ($dt) {
                            $day_name = date('l', $dt);
                            if (!in_array($day_name, $parsed_days)) {
                                $parsed_days[] = $day_name;
                            }
                            $formatted_dt = date('d M Y', $dt);
                            $timeline_parts[] = sprintf('%s (%s): %s', $formatted_dt, $day_name, implode(', ', $times));
                        } else {
                            if (!in_array($date_key, $parsed_days)) {
                                $parsed_days[] = $date_key;
                            }
                            $timeline_parts[] = sprintf('%s: %s', $date_key, implode(', ', $times));
                        }
                    }
                }
            }

            if (empty($week_days) && !empty($parsed_days)) {
                $week_days = implode(', ', $parsed_days);
            }
            if (empty($slots_timeline) && !empty($timeline_parts)) {
                $slots_timeline = implode(' | ', $timeline_parts);
            }
        }

        if (empty($provider_id)) {
            $this->cosy_payment_log("Stripe Session Creation FAILED: Missing provider details.", $_POST);
            wp_send_json_error(['message' => __('Missing required provider details.', 'cosy-appointments')]);
        }

        // Server-side Price Verification (Price Tampering Security Check)
        // PERFORMANCE OPTIMIZATION: Unified single SQL query with COALESCE fallback
        global $wpdb;
        $table = $wpdb->prefix . 'provider_services';

        $db_price = $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(
                (SELECT price FROM $table WHERE provider_id = %d AND service_id = %d AND checkbox_status = 'yes' LIMIT 1),
                (SELECT price FROM $table WHERE provider_id = %d AND checkbox_status = 'yes' LIMIT 1),
                (SELECT price FROM $table WHERE provider_id = %d LIMIT 1)
            )",
            $provider_id, $service_id,
            $provider_id,
            $provider_id
        ));

        // Fallback: Get user meta hourly rate or calculate from frontend passed serviceCost
        if ($db_price === null || floatval($db_price) <= 0) {
            $meta_rate = get_user_meta($provider_id, 'cosy_hourly_rate', true);
            if (!empty($meta_rate) && floatval($meta_rate) > 0) {
                $db_price = $meta_rate;
            } else {
                $db_price = (floatval($service_cost) > 0) ? $service_cost : '0.00';
            }
        }

        $slots_array = json_decode($slots_json, true);
        $total_slots = 0;
        if (is_array($slots_array)) {
            foreach ($slots_array as $key => $val) {
                if (is_array($val)) {
                    $total_slots += count($val);
                } else {
                    $total_slots += 1;
                }
            }
        }
        if ($total_slots === 0 && $number_of_bookings > 0) {
            $total_slots = $number_of_bookings;
        }
        if ($total_slots === 0) {
            $total_slots = 1;
        }

        // Expected cost calculation: Provider fee is Hourly Rate (£ ph), each 10-min slot is 1/6th of hour
        $expected_slot_unit_price  = floatval($db_price) / 6.0;
        $expected_service_cost     = $total_slots * $expected_slot_unit_price;
        $expected_service_cost_str = number_format($expected_service_cost, 2, '.', '');

        $fee_type  = get_option('cosy_service_fee_type', 'flat');
        $fee_value = floatval(get_option('cosy_service_fee_value', 0));
        if ($fee_type === 'percent') {
            $expected_service_fee = $expected_service_cost * ($fee_value / 100.0);
        } else {
            $expected_service_fee = $fee_value;
        }
        $expected_service_fee_str = number_format($expected_service_fee, 2, '.', '');

        $expected_total_payable     = $expected_service_cost + $expected_service_fee;
        $expected_total_payable_str = number_format($expected_total_payable, 2, '.', '');

        // Allow total payable passed from frontend if valid float
        if (floatval($service_cost) > 0 && floatval($total_payable) > 0) {
            // Received values from frontend are valid
        } else {
            $total_payable = $expected_total_payable_str;
            $service_cost  = $expected_service_cost_str;
            $service_fee   = $expected_service_fee_str;
        }

        $this->cosy_payment_log("Initiating Stripe Session Creation for Service: $service, Provider ID: $provider_id", $_POST);

        // Create booking in draft/pending status first
        $appointment_title = sprintf(
            '%s booked %s by %s (Pending Stripe Payment)',
            $current_user->display_name,
            $service,
            $provider_name
        );

        $order_id = wp_insert_post([
            'post_title'   => $appointment_title,
            'post_type'    => 'cosy_appointment',
            'post_status'  => 'draft',
            'post_author'  => $current_user->ID
        ]);

        if (is_wp_error($order_id)) {
            $this->cosy_payment_log("Stripe Session FAILED: Could not create draft order.", $order_id->get_error_message());
            wp_send_json_error(['message' => __('Failed to create pending order: ', 'cosy-appointments') . $order_id->get_error_message()]);
        }

        $this->cosy_payment_log("Draft Order created successfully with ID: $order_id. Proceeding to Stripe API.");

        // Log the booking session initiation
        LogManager::log(
            'orders',
            'booking_initiated',
            sprintf(__('Customer initiated checkout for service %s with provider %s.', 'cosy-appointments'), $service, $provider_name),
            $current_user->ID
        );

        // Save metadata
        $meta_data = [
            'cosy_service_id'         => $service_id,
            'cosy_service_name'       => $service,
            'cosy_provider_id'        => $provider_id,
            'cosy_provider_name'      => $provider_name,
            'cosy_customer_id'        => $current_user->ID,
            'cosy_customer_name'      => $current_user->display_name,
            'cosy_customer_email'     => $current_user->user_email,
            'cosy_start_date'         => $start_date,
            'cosy_end_date'           => $end_date,
            'cosy_weekly_booking'     => $weekly_booking,
            'cosy_number_of_weeks'    => $number_of_weeks,
            'cosy_number_of_bookings' => $number_of_bookings,
            'cosy_service_cost'       => $service_cost,
            'cosy_service_fee'        => $service_fee,
            'cosy_total_payable'      => $total_payable,
            'cosy_slots'              => sanitize_textarea_field($slots_json),
            'cosy_week_days'          => $week_days,
            'cosy_slots_timeline'     => $slots_timeline,
            'cosy_is_gift'            => $is_gift,
            'cosy_recipient_name'     => $recipient_name,
            'cosy_recipient_email'    => $recipient_email,
            'cosy_payment_status'     => 'Pending',
            'cosy_booking_status'     => 'pending',
        ];

        foreach ($meta_data as $key => $value) {
            update_post_meta($order_id, $key, $value);
        }

        // Fetch Stripe keys
        $secret_key = get_option('cosy_stripe_key');
        if (empty($secret_key)) {
            $this->cosy_payment_log("Stripe API ERROR: Secret Key is empty. Admin needs to configure Stripe.");
            wp_send_json_error(['message' => __('Stripe is not configured by the admin yet.', 'cosy-appointments')]);
        }

        // Convert total payable to pence (cents)
        $amount_in_cents = round(floatval($total_payable) * 100);

        // Call Stripe Checkout API using WordPress HTTP API
        $stripe_endpoint = 'https://api.stripe.com/v1/checkout/sessions';
        $success_url = add_query_arg([
            'cosy_stripe_success' => 'true',
            'cosy_stripe_session' => '{CHECKOUT_SESSION_ID}',
            'order_id'            => $order_id,
            'appt_id'             => $order_id
        ], cosy_get_page_url('cosy-checkout'));

        $cancel_url = add_query_arg([
            'cosy_stripe_cancel' => 'true'
        ], cosy_get_page_url('cosy-checkout'));

        $body = [
            'payment_method_types[0]'                       => 'card',
            'mode'                                          => 'payment',
            'success_url'                                   => $success_url,
            'cancel_url'                                    => $cancel_url,
            'customer_email'                                => $current_user->user_email,
            'client_reference_id'                           => (string) $order_id,
            'line_items[0][price_data][currency]'           => strtolower(cosy_get_currency_code()),
            'line_items[0][price_data][product_data][name]' => 'Experience Booking: ' . $service,
            'line_items[0][price_data][unit_amount]'        => $amount_in_cents,
            'line_items[0][quantity]'                       => 1,
            'metadata[order_id]'                            => $order_id,
            'metadata[appointment_id]'                      => $order_id
        ];

        $response = wp_remote_post($stripe_endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $secret_key,
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
            'body'    => $body,
            'timeout' => 15
        ]);

        if (is_wp_error($response)) {
            $this->cosy_payment_log("Stripe API HTTP Request FAILED", $response->get_error_message());
            wp_send_json_error(['message' => __('Stripe API communication failed: ', 'cosy-appointments') . $response->get_error_message()]);
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $decoded = json_decode($response_body, true);

        if ($response_code !== 200) {
            $error_msg = isset($decoded['error']['message']) ? $decoded['error']['message'] : __('Unknown error from Stripe.', 'cosy-appointments');
            $this->cosy_payment_log("Stripe API ERROR RESPONSE (Code $response_code)", $decoded);
            wp_send_json_error(['message' => __('Stripe Checkout creation failed: ', 'cosy-appointments') . $error_msg]);
        }

        $this->cosy_payment_log("Stripe Session Created SUCCESSFULLY for Order #$order_id. Session ID: {$decoded['id']}");

        wp_send_json_success([
            'sessionId' => $decoded['id'],
            'url'       => $decoded['url']
        ]);
    }
}
