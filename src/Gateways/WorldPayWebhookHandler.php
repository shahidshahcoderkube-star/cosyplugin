<?php

namespace Cosy\Appointments\Gateways;

use Cosy\Appointments\Loader;
use Cosy\Appointments\Common\LogManager;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WORLDPAY WEBHOOK HANDLER
 * 
 * Server-to-server webhook processor for Worldpay Access HPP events.
 * Serves as the primary Source of Truth for payment state changes (authorized, sentForSettlement, refused, cancelled, expired).
 */
class WorldPayWebhookHandler
{
    /**
     * REGISTERS WEBHOOK HOOKS
     * 
     * USE CASE:
     * Called during plugin loader registration to attach Webhook listener hooks.
     * 
     * HOW TO USE:
     * (new WorldPayWebhookHandler())->register($loader);
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Attaches 'listen_for_worldpay_webhook' to WordPress 'init' hook for GET query param triggers.
     * 2. Attaches 'register_rest_webhook_route' to 'rest_api_init' hook for REST API POST triggers.
     * 
     * @param Loader $loader Plugin loader instance.
     */
    public function register(Loader $loader): void
    {
        $loader->add_action('init', $this, 'listen_for_worldpay_webhook');
        $loader->add_action('rest_api_init', $this, 'register_rest_webhook_route');
    }

    /**
     * REGISTERS REST API WEBHOOK ROUTE
     * 
     * USE CASE:
     * Registers endpoint /wp-json/cosy-appointments/v1/worldpay-webhook for external server notifications.
     * 
     * HOW TO USE:
     * Automatically registered via WordPress 'rest_api_init' hook.
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Calls register_rest_route() with endpoint name.
     * 2. Sets HTTP POST callback method to handle_rest_webhook.
     * 3. Sets permission_callback to '__return_true' to receive public webhook POST calls.
     */
    public function register_rest_webhook_route(): void
    {
        register_rest_route('cosy-appointments/v1', '/worldpay-webhook', [
            'methods'             => 'POST',
            'callback'            => [$this, 'handle_rest_webhook'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * HANDLES REST API POST WEBHOOK REQUESTS
     * 
     * USE CASE:
     * Triggered when Worldpay Access servers send an asynchronous REST HTTP POST notification.
     * 
     * HOW TO USE:
     * Executed automatically by WordPress REST API controller on endpoint hit.
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Extracts raw POST body and request headers from \WP_REST_Request.
     * 2. Passes payload to process_webhook_payload() for validation and DB status sync.
     * 3. Returns HTTP 200 JSON success response to WorldPay server.
     * 
     * @param \WP_REST_Request $request Incoming REST request object.
     * @return \WP_REST_Response         HTTP REST response object.
     */
    public function handle_rest_webhook(\WP_REST_Request $request): \WP_REST_Response
    {
        $raw_body = $request->get_body();
        $headers  = $request->get_headers();

        $this->process_webhook_payload($raw_body, $headers);

        return new \WP_REST_Response(['status' => 'success', 'message' => 'Webhook received'], 200);
    }

    /**
     * LISTENS FOR QUERY PARAMETER WEBHOOK REQUESTS
     * 
     * USE CASE:
     * Serves as legacy/fallback listener for URL: https://site.com/?worldpay_webhook=1
     * 
     * HOW TO USE:
     * Executed automatically during WordPress 'init' hook.
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Checks if $_GET['worldpay_webhook'] === '1'.
     * 2. Reads raw php://input stream and headers.
     * 3. Calls process_webhook_payload() to update order state.
     * 4. Outputs HTTP 200 JSON success and exits execution.
     */
    public function listen_for_worldpay_webhook(): void
    {
        if (isset($_GET['worldpay_webhook']) && $_GET['worldpay_webhook'] === '1') {
            $raw_body = file_get_contents('php://input');
            $headers  = getallheaders();

            $this->process_webhook_payload($raw_body, is_array($headers) ? $headers : []);

            status_header(200);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Webhook received']);
            exit;
        }
    }

    /**
     * CORE PAYLOAD PROCESSOR FOR WORLDPAY EVENTS
     * 
     * USE CASE:
     * Processes raw JSON webhook payload from Worldpay Access servers to update appointment status.
     * 
     * HOW TO USE:
     * $this->process_webhook_payload($raw_body_string, $headers_array);
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Logs raw payload via LogManager.
     * 2. Parses JSON payload and extracts transactionReference and eventName.
     * 3. Extracts target Order ID from transaction reference string.
     * 4. Queries target 'cosy_appointment' post from WordPress database.
     * 5. Updates post_status, cosy_payment_status, and cosy_booking_status based on event (authorized, refused, cancelled).
     * 6. Syncs booking record and sends confirmation emails if payment is authorized.
     * 
     * @param string $raw_body Raw JSON HTTP request body string.
     * @param array  $headers  HTTP request headers array.
     */
    public function process_webhook_payload(string $raw_body, array $headers): void
    {
        $log_msg = 'Worldpay Webhook POST payload received: ' . $raw_body;
        LogManager::log('payments', 'worldpay_webhook_received', $log_msg);

        $data = json_decode($raw_body, true);
        if (empty($data)) {
            LogManager::log('payments', 'worldpay_webhook_error', 'Invalid JSON body received in Worldpay Webhook.');
            return;
        }

        // Extract transaction reference and event details
        // Expected payload keys from Worldpay Access API Webhook:
        // transactionReference (e.g. Cosy_347_1786441597), eventName/event, eventDetails
        $tx_ref     = isset($data['transactionReference']) ? sanitize_text_field($data['transactionReference']) : (isset($data['orderId']) ? sanitize_text_field($data['orderId']) : '');
        $event_name = isset($data['lastEvent']) ? sanitize_text_field($data['lastEvent']) : (isset($data['event']) ? sanitize_text_field($data['event']) : (isset($data['eventName']) ? sanitize_text_field($data['eventName']) : (isset($data['eventType']) ? sanitize_text_field($data['eventType']) : '')));

        if (empty($tx_ref)) {
            LogManager::log('payments', 'worldpay_webhook_warning', 'No transactionReference found in webhook payload.');
            return;
        }

        // Extract order ID from transaction reference (format: Cosy_{order_id}_{timestamp} or ORDER-{order_id})
        $order_id = 0;
        if (preg_match('/Cosy_(\d+)_/i', $tx_ref, $matches)) {
            $order_id = intval($matches[1]);
        } elseif (preg_match('/ORDER-(\d+)/i', $tx_ref, $matches)) {
            $order_id = intval($matches[1]);
        } elseif (is_numeric($tx_ref)) {
            $order_id = intval($tx_ref);
        }

        if ($order_id <= 0) {
            LogManager::log('payments', 'worldpay_webhook_warning', "Could not parse valid Order ID from transactionReference: $tx_ref");
            return;
        }

        $appt = get_post($order_id);
        if (!$appt || $appt->post_type !== 'cosy_appointment') {
            LogManager::log('payments', 'worldpay_webhook_warning', "Appointment post #$order_id not found in database.");
            return;
        }

        LogManager::log('payments', 'worldpay_webhook_processing', sprintf('Processing Worldpay Event [%s] for Order #%d', $event_name, $order_id));

        $event_lower = strtolower($event_name);

        switch ($event_lower) {
            case 'authorized':
            case 'sentforsettlement':
            case 'settlementrequestsubmitted':
            case 'settled':
            case 'success':
            case 'paid':
                // Update Order Status to Publish / Paid
                wp_update_post(['ID' => $order_id, 'post_status' => 'publish']);
                update_post_meta($order_id, 'cosy_payment_status', 'Paid');
                update_post_meta($order_id, 'cosy_booking_status', 'pending');
                update_post_meta($order_id, 'cosy_payment_gateway', 'worldpay');
                update_post_meta($order_id, 'cosy_worldpay_last_event', $event_name);

                LogManager::log('orders', 'payment_completed_worldpay_webhook', sprintf(__('Worldpay Webhook confirmed payment [%s] for Order #%d.', 'cosy-appointments'), $event_name, $order_id), $appt->post_author);
                \Cosy\Appointments\Common\Database::sync_booking_record($order_id);
                \Cosy\Appointments\Common\Database::record_worldpay_payment_entry($order_id, 'Paid', ['raw_response' => $data]);

                // Trigger booking confirmation emails (Customer, Provider & Admin)
                (new \Cosy\Appointments\Frontend\Frontend())->send_booking_emails($order_id);
                break;

            case 'refused':
            case 'cancelled':
            case 'canceled':
            case 'failed':
            case 'expired':
            case 'error':
                // Update Order Status to Cancelled / Failed
                update_post_meta($order_id, 'cosy_booking_status', 'cancelled');
                update_post_meta($order_id, 'cosy_payment_status', 'Cancelled');
                update_post_meta($order_id, 'cosy_worldpay_last_event', $event_name);

                if ($appt->post_status === 'draft') {
                    wp_update_post(['ID' => $order_id, 'post_status' => 'trash']);
                }

                LogManager::log('orders', 'payment_failed_worldpay_webhook', sprintf(__('Worldpay Webhook marked Order #%d as [%s].', 'cosy-appointments'), $order_id, $event_name), $appt->post_author);
                \Cosy\Appointments\Common\Database::sync_booking_record($order_id);
                \Cosy\Appointments\Common\Database::record_worldpay_payment_entry($order_id, ucfirst($event_name), ['raw_response' => $data]);
                break;

            default:
                LogManager::log('payments', 'worldpay_webhook_info', sprintf('Unhandled Worldpay Event [%s] received for Order #%d', $event_name, $order_id));
                break;
        }
    }
}
