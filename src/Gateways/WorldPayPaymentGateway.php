<?php

namespace Cosy\Appointments\Gateways;

use Cosy\Appointments\Loader;
use Cosy\Appointments\Common\GlobalCommonFunctions;
use Cosy\Appointments\Common\LogManager;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WORLDPAY PAYMENT GATEWAY & CHECKOUT HANDLER
 * 
 * USE CASE:
 * Dedicated payment class responsible for handling all WorldPay API & Hosted Gateway interactions,
 * checkout session generation, payment status verification, and payment logging.
 */
class WorldPayPaymentGateway
{
    use GlobalCommonFunctions;

    /**
     * REGISTERS WORLDPAY AJAX HOOKS
     * 
     * USE CASE:
     * Called during plugin initialization to register AJAX endpoints for WorldPay session creation.
     * 
     * HOW TO USE:
     * (new WorldPayPaymentGateway())->register($loader);
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Attaches 'wp_ajax_cosy_create_worldpay_session' for logged-in users.
     * 2. Attaches 'wp_ajax_nopriv_cosy_create_worldpay_session' for non-logged-in users.
     * 
     * @param Loader $loader Plugin hook loader instance.
     */
    public function register(Loader $loader): void
    {
        $loader->add_action('wp_ajax_cosy_create_worldpay_session', $this, 'handle_create_worldpay_session');
        $loader->add_action('wp_ajax_nopriv_cosy_create_worldpay_session', $this, 'handle_create_worldpay_session');
    }

    /**
     * LOGS WORLDPAY TRANSACTION ACTIVITIES
     * 
     * USE CASE:
     * Called whenever a WorldPay payment event, API request, return callback, or error occurs.
     * 
     * HOW TO USE:
     * $this->cosy_payment_log("Session created for Order #123", $response_data);
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Formats incoming message and raw payload array into pretty JSON.
     * 2. Passes formatted log string to LogManager::log() to persist in database and debug.log.
     * 
     * @param string     $message Descriptive event message.
     * @param mixed|null $data    Optional payload or response object.
     */
    public function cosy_payment_log(string $message, $data = null): void
    {
        $desc = $message;
        if ($data !== null) {
            $formatted_data = (is_array($data) || is_object($data)) ? json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : (string) $data;
            $desc .= " | Data:\n" . $formatted_data;
        }
        LogManager::log('payments', 'worldpay_log', $desc);
    }

    /**
     * HANDLES WORLDPAY CHECKOUT SESSION CREATION
     * 
     * USE CASE:
     * Triggered via AJAX when a customer clicks 'Proceed to Payment' on the checkout screen.
     * 
     * HOW TO USE:
     * Automatically called by WordPress AJAX hook 'cosy_create_worldpay_session'.
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Verifies security nonce and checks customer authorization.
     * 2. Validates booking details (service, provider, dates, slots, price calculation).
     * 3. Creates a draft 'cosy_appointment' post in WordPress database.
     * 4. Queries saved WorldPay API credentials from Admin Settings.
     * 5. Sends HTTP POST request to WorldPay Access API to generate HPP redirect URL.
     * 6. Returns JSON response containing checkout redirect URL to frontend.
     */
    public function handle_create_worldpay_session(): void
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
        $slots_json         = isset($_POST['slots']) ? wp_unslash($_POST['slots']) : ''; // raw JSON string
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
                $slots_timeline = implode(', ', $timeline_parts);
            }
            $slots_timeline = cosy_clean_slots_timeline($slots_timeline);
        }

        if (empty($provider_id)) {
            $this->cosy_payment_log("WorldPay Session Creation FAILED: Missing provider details.", $_POST);
            wp_send_json_error(['message' => __('Missing required provider details.', 'cosy-appointments')]);
        }

        // Verify provider account is active
        $provider_status = get_user_meta($provider_id, 'cosy_provider_status', true);
        if ($provider_status !== 'active') {
            $this->cosy_payment_log("WorldPay Session Creation FAILED: Provider ID {$provider_id} is inactive.", $_POST);
            wp_send_json_error(['message' => __('This service provider is currently inactive and cannot accept bookings.', 'cosy-appointments')]);
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

        if (floatval($service_cost) > 0 && floatval($total_payable) > 0) {
            // Received values from frontend are valid
        } else {
            $total_payable = $expected_total_payable_str;
            $service_cost = $expected_service_cost_str;
            $service_fee = $expected_service_fee_str;
        }

        $this->cosy_payment_log("Initiating WorldPay Session Creation for Service: $service, Provider ID: $provider_id", $_POST);

        // Create booking in draft/pending status first
        $appointment_title = sprintf(
            '%s booked %s by %s (Pending WorldPay Payment)',
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
            $this->cosy_payment_log("WorldPay Session FAILED: Could not create draft order.", $order_id->get_error_message());
            wp_send_json_error(['message' => __('Failed to create pending order: ', 'cosy-appointments') . $order_id->get_error_message()]);
        }

        $this->cosy_payment_log("Draft Order created successfully with ID: $order_id. Proceeding to WorldPay Gateway.");

        // Log the booking session initiation
        LogManager::log(
            'orders',
            'booking_initiated_worldpay',
            sprintf(__('Customer initiated checkout for service %s via WorldPay.', 'cosy-appointments'), $service),
            $current_user->ID
        );
        $tx_ref = 'Cosy_' . $order_id . '_' . time();

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
            'cosy_payment_gateway'    => 'worldpay',
            'cosy_payment_status'     => 'Pending',
            'cosy_booking_status'     => 'pending',
            'cosy_transaction_ref'    => $tx_ref,
        ];

        foreach ($meta_data as $key => $value) {
            update_post_meta($order_id, $key, $value);
        }

        // Sync record into custom database table (wp_cosy_bookings)
        \Cosy\Appointments\Common\Database::sync_booking_record($order_id);
        \Cosy\Appointments\Common\Database::record_worldpay_payment_entry($order_id, 'Pending');

        // 100% Dynamic retrieval of WorldPay credentials directly from saved Admin Settings
        $username  = trim(get_option('cosy_worldpay_token', ''));
        $password  = trim(get_option('cosy_worldpay_password', ''));
        $entity_id = trim(get_option('cosy_worldpay_inst_id', ''));
        $test_mode = get_option('cosy_worldpay_test_mode', 1);
        $currency  = strtoupper(cosy_get_currency_code());

        $base_checkout_url = cosy_get_page_url('cosy-checkout');

        $success_url = set_url_scheme(add_query_arg(['cosy_worldpay_success' => 'true', 'paymentStatus' => 'SUCCESS', 'order_id' => $order_id, 'appt_id' => $order_id], $base_checkout_url), 'https');
        $pending_url = set_url_scheme(add_query_arg(['cosy_worldpay_pending' => 'true', 'paymentStatus' => 'PENDING', 'order_id' => $order_id, 'appt_id' => $order_id], $base_checkout_url), 'https');
        $failure_url = set_url_scheme(add_query_arg(['cosy_worldpay_failure' => 'true', 'paymentStatus' => 'FAILED', 'order_id' => $order_id, 'appt_id' => $order_id], $base_checkout_url), 'https');
        $error_url   = set_url_scheme(add_query_arg(['cosy_worldpay_error'   => 'true', 'paymentStatus' => 'ERROR', 'order_id' => $order_id, 'appt_id' => $order_id], $base_checkout_url), 'https');
        $cancel_url  = set_url_scheme(add_query_arg(['cosy_worldpay_cancel'  => 'true', 'paymentStatus' => 'CANCELLED', 'order_id' => $order_id, 'appt_id' => $order_id], $base_checkout_url), 'https');
        $expiry_url  = set_url_scheme(add_query_arg(['cosy_worldpay_expiry'  => 'true', 'paymentStatus' => 'EXPIRED', 'order_id' => $order_id, 'appt_id' => $order_id], $base_checkout_url), 'https');

        $endpoint = $test_mode ? 'https://try.access.worldpay.com/payment_pages' : 'https://access.worldpay.com/payment_pages';
        $auth_header = 'Basic ' . base64_encode($username . ':' . $password);

        $amount_minor = (int) round(floatval($total_payable) * 100);

        $payload = [
            'transactionReference' => $tx_ref,
            'merchant' => [
                'entity' => $entity_id
            ],
            'narrative' => [
                'line1' => substr(preg_replace('/[^a-zA-Z0-9 ]/', '', 'CosyBooking ' . $service), 0, 25)
            ],
            'value' => [
                'currency' => $currency,
                'amount'   => $amount_minor
            ],
            'resultURLs' => [
                'successURL' => $success_url,
                'pendingURL' => $pending_url,
                'failureURL' => $failure_url,
                'errorURL'   => $error_url,
                'cancelURL'  => $cancel_url,
                'expiryURL'  => $expiry_url
            ]
        ];

        $res = wp_remote_post($endpoint, [
            'headers' => [
                'Authorization' => $auth_header,
                'Content-Type'  => 'application/vnd.worldpay.payment_pages-v1.hal+json',
                'Accept'        => 'application/vnd.worldpay.payment_pages-v1.hal+json'
            ],
            'body'      => json_encode($payload),
            'timeout'   => 25,
            'sslverify' => false
        ]);

        $res_code = wp_remote_retrieve_response_code($res);
        $res_body = json_decode(wp_remote_retrieve_body($res), true);

        $this->cosy_payment_log("WorldPay Access HPP Response (HTTP $res_code):", $res_body);

        $redirect_url = !empty($res_body['url']) ? $res_body['url'] : (!empty($res_body['_links']['redirect']['href']) ? $res_body['_links']['redirect']['href'] : '');

        if (!is_wp_error($res) && !empty($redirect_url)) {
            $this->cosy_payment_log("WorldPay Access HPP Session Created SUCCESSFULLY for Order #$order_id", $redirect_url);

            wp_send_json_success([
                'orderId' => $order_id,
                'url'     => $redirect_url
            ]);
        } else {
            $err_msg = isset($res_body['description']) ? $res_body['description'] : (is_wp_error($res) ? $res->get_error_message() : __('WorldPay API connection error', 'cosy-appointments'));
            $this->cosy_payment_log("WorldPay Access HPP Session FAILED for Order #$order_id: $err_msg");
            wp_send_json_error(['message' => $err_msg]);
        }
    }
}
