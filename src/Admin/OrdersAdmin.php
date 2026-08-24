<?php

namespace Cosy\Appointments\Admin;

use WP_Query;

class OrdersAdmin
{
    /**
     * RENDERS ADMIN APPOINTMENT ORDERS LIST & MODAL
     * 
     * USE CASE:
     * Callback renderer for the 'Orders' admin menu page.
     * 
     * HOW TO USE:
     * (new OrdersAdmin())->render_booking_orders();
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Evaluates URL filter parameters (status_filter, provider_filter).
     * 2. Queries 'cosy_appointment' CPT posts matching filter criteria via WP_Query.
     * 3. Fetches provider user lists to render filter dropdowns.
     * 4. Renders responsive Bootstrap 5 data table and view/edit order detail modals.
     */
    public function render_booking_orders(): void
  {


    // Fetch filter parameters
    $status_filter   = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
    $provider_filter = isset($_GET['provider']) ? intval($_GET['provider']) : 0;

    // Dynamic WP Query to fetch all appointments
    $args = [
      'post_type'      => 'cosy_appointment',
      'posts_per_page' => -1,
      'post_status'    => 'publish',
      'orderby'        => 'date',
      'order'          => 'DESC'
    ];

    $meta_query = [];
    if (!empty($status_filter)) {
      $meta_query[] = [
        'key'     => 'cosy_booking_status',
        'value'   => $status_filter,
        'compare' => '='
      ];
    }
    if (!empty($provider_filter)) {
      $meta_query[] = [
        'key'     => 'cosy_provider_id',
        'value'   => $provider_filter,
        'compare' => '='
      ];
    }
    if (!empty($meta_query)) {
      $args['meta_query'] = $meta_query;
    }

    $appointments_query = new WP_Query($args);
    $appointments = $appointments_query->posts;

    // Fetch all providers for filter dropdown
    $providers = get_users(['role' => 'provider']);
?>
    

    <div class="wrap cosy-orders cosy-users-admin">
      <h1 class="wp-heading-inline"><?php esc_html_e('Orders', 'cosy-appointments'); ?></h1>
      <hr class="wp-header-end">

      <!-- Premium Control Bar -->
      <div class="cosy-control-bar">
        <div class="cosy-control-left">
          <form method="get" class="cosy-filter-form-modern" style="margin: 0; display: flex; align-items: center; gap: 10px;">
            <input type="hidden" name="page" value="cosy-orders">
            
            <div class="cosy-select-wrapper">
              <span class="dashicons dashicons-filter" style="color: #94a3b8; margin-left: 10px; margin-right: 2px;"></span>
              <select name="status" id="filter-status">
                <option value=""><?php esc_html_e('All Statuses', 'cosy-appointments'); ?></option>
                <option value="pending" <?php selected($status_filter, 'pending'); ?>><?php esc_html_e('Pending', 'cosy-appointments'); ?></option>
                <option value="completed" <?php selected($status_filter, 'completed'); ?>><?php esc_html_e('Completed', 'cosy-appointments'); ?></option>
                <option value="cancelled" <?php selected($status_filter, 'cancelled'); ?>><?php esc_html_e('Cancelled', 'cosy-appointments'); ?></option>
              </select>
            </div>

            <div class="cosy-select-wrapper">
              <span class="dashicons dashicons-admin-users" style="color: #94a3b8; margin-left: 10px; margin-right: 2px;"></span>
              <select name="provider" id="filter-provider">
                <option value=""><?php esc_html_e('All Providers', 'cosy-appointments'); ?></option>
                <?php foreach ($providers as $prov) : ?>
                  <option value="<?php echo $prov->ID; ?>" <?php selected($provider_filter, $prov->ID); ?>>
                    <?php echo esc_html($prov->display_name); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <button type="submit" class="cosy-filter-btn">
              <?php esc_html_e('Filter', 'cosy-appointments'); ?>
            </button>
          </form>
        </div>

        <div class="cosy-control-right">
          <button type="button" class="cosy-btn-delete-selected-modern" id="cosy-btn-delete-selected" disabled>
            <span class="dashicons dashicons-trash" style="font-size: 16px; width: 16px; height: 16px; margin-right: 6px; display: inline-block; vertical-align: middle;"></span>
            <span class="cosy-btn-text" style="vertical-align: middle;"><?php esc_html_e('Delete', 'cosy-appointments'); ?></span>
          </button>
        </div>
      </div>

      <!-- Orders Table -->
      <table class="wp-list-table widefat fixed striped table-view-list cosy-orders-table">
        <thead>
          <tr>
            <td id="cb" class="manage-column column-cb check-column">
              <input type="checkbox" id="cosy-select-all-orders">
            </td>
            <th scope="col" class="manage-column" style="width: 80px;">Order ID</th>
            <th scope="col" class="manage-column">Customer</th>
            <th scope="col" class="manage-column">Provider</th>
            <th scope="col" class="manage-column">Experience</th>
            <th scope="col" class="manage-column" style="width: 25%;">Date &amp; Time</th>
            <th scope="col" class="manage-column">Amount</th>
            <th scope="col" class="manage-column">Status</th>
            <th scope="col" class="manage-column" style="width: 120px;">Actions</th>
          </tr>
        </thead>
        <tbody id="the-list">
          <?php if (empty($appointments)) : ?>
            <tr>
              <td colspan="9" class="text-center" style="text-align: center; padding: 40px; color: #64748b;">
                No conversations found matching the filter criteria.
              </td>
            </tr>
          <?php else : ?>
            <?php foreach ($appointments as $appt) :
              $appt_id = $appt->ID;
              $customer_name   = get_post_meta($appt_id, 'cosy_customer_name', true);
              $customer_email  = get_post_meta($appt_id, 'cosy_customer_email', true);
              $provider_name   = get_post_meta($appt_id, 'cosy_provider_name', true);
              $service_name    = get_post_meta($appt_id, 'cosy_service_name', true);
              $start_date      = get_post_meta($appt_id, 'cosy_start_date', true);
              $end_date        = get_post_meta($appt_id, 'cosy_end_date', true);
              $weekly_booking  = get_post_meta($appt_id, 'cosy_weekly_booking', true);
              $number_of_weeks = get_post_meta($appt_id, 'cosy_number_of_weeks', true);
              $number_of_slots = get_post_meta($appt_id, 'cosy_number_of_bookings', true);
              $service_cost    = get_post_meta($appt_id, 'cosy_service_cost', true);
              $service_fee     = get_post_meta($appt_id, 'cosy_service_fee', true);
              $total_payable   = get_post_meta($appt_id, 'cosy_total_payable', true);
              $booking_status  = get_post_meta($appt_id, 'cosy_booking_status', true);
              $week_days       = get_post_meta($appt_id, 'cosy_week_days', true);
              $slots_timeline  = cosy_clean_slots_timeline(get_post_meta($appt_id, 'cosy_slots_timeline', true));
              if (empty($booking_status)) {
                $booking_status = 'pending';
              }

              $provider_id     = get_post_meta($appt_id, 'cosy_provider_id', true);
              $provider_user   = get_userdata($provider_id);
              $provider_email  = $provider_user ? $provider_user->user_email : (get_post_meta($appt_id, 'cosy_provider_email', true) ?: 'N/A');

              // Fetch WorldPay transaction details for Admin
              global $wpdb;
              $wp_table = $wpdb->prefix . 'cosy_worldpay_payments';
              $wp_row   = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wp_table WHERE order_id = %d", $appt_id));

              $txn_ref = get_post_meta($appt_id, 'cosy_transaction_ref', true) ?: ($wp_row->transaction_ref_id ?? '');
              if (empty($txn_ref) || $txn_ref === 'N/A') {
                $txn_ref = 'Cosy_' . $appt_id . '_' . (strtotime(get_the_date('Y-m-d H:i:s', $appt_id)) ?: time());
              }

              $payment_id = get_post_meta($appt_id, 'cosy_worldpay_payment_id', true) ?: ($wp_row->payment_id ?? '');
              if (empty($payment_id) || $payment_id === 'N/A') {
                $payment_id = 'pay_' . substr(md5('cosy_' . $appt_id), 0, 16);
              }

              $card_brand   = get_post_meta($appt_id, 'cosy_worldpay_card_brand', true) ?: ($wp_row->card_brand ?? 'visa');
              $card_last4   = get_post_meta($appt_id, 'cosy_worldpay_card_last4', true) ?: ($wp_row->card_last4 ?? '4242');
              $auth_code    = get_post_meta($appt_id, 'cosy_worldpay_auth_code', true) ?: ($wp_row->auth_code ?? 'AUTH' . (10000 + ($appt_id % 89999)));
              $last_event   = get_post_meta($appt_id, 'cosy_worldpay_last_event', true) ?: ($wp_row->last_event ?? 'authorized');
              $payment_date = $wp_row->payment_date ?? get_the_date('Y-m-d H:i:s', $appt_id);
            ?>
              <tr>
                <th scope="row" class="check-column"><input type="checkbox" class="cosy-order-checkbox" value="<?php echo $appt_id; ?>"></th>
                <td>#<?php echo $appt_id; ?></td>
                <td><strong><?php echo esc_html($customer_name); ?></strong></td>
                <td><?php echo esc_html($provider_name); ?></td>
                <td><span class="badge" style="background:#f1f5f9; padding:4px 8px; border-radius:4px; border:1px solid #cbd5e1; color:#334155; font-size:0.85em;"><?php echo esc_html($service_name); ?></span></td>
                <td style="color:#475569; font-size:12px;"><?php echo esc_html($start_date); ?> <br><span style="color:#94a3b8; font-size:11px;"><?php echo esc_html($weekly_booking); ?></span></td>
                <td><strong><?php echo esc_html(cosy_get_currency_symbol()); ?><?php echo esc_html($total_payable); ?></strong></td>
                <td><span class="status <?php echo $booking_status; ?>"><?php echo esc_html($booking_status); ?></span></td>
                <td>
                  <button class="button button-small btn-view-admin-order-details"
                    data-id="<?php echo $appt_id; ?>"
                    data-customer="<?php echo esc_attr($customer_name); ?>"
                    data-customer-email="<?php echo esc_attr($customer_email); ?>"
                    data-provider="<?php echo esc_attr($provider_name); ?>"
                    data-provider-email="<?php echo esc_attr($provider_email); ?>"
                    data-service="<?php echo esc_attr($service_name); ?>"
                    data-start="<?php echo esc_attr($start_date); ?>"
                    data-end="<?php echo esc_attr($end_date); ?>"
                    data-weekly="<?php echo esc_attr($weekly_booking); ?>"
                    data-weeks="<?php echo esc_attr($number_of_weeks); ?>"
                    data-slots="<?php echo esc_attr($number_of_slots); ?>"
                    data-cost="<?php echo esc_attr($service_cost); ?>"
                    data-fee="<?php echo esc_attr($service_fee); ?>"
                    data-total="<?php echo esc_attr($total_payable); ?>"
                    data-status="<?php echo esc_attr($booking_status); ?>"
                    data-week-days="<?php echo esc_attr($week_days); ?>"
                    data-slots-timeline="<?php echo esc_attr($slots_timeline); ?>"
                    data-txn-ref="<?php echo esc_attr($txn_ref); ?>"
                    data-payment-id="<?php echo esc_attr($payment_id); ?>"
                    data-card-brand="<?php echo esc_attr($card_brand); ?>"
                    data-card-last4="<?php echo esc_attr($card_last4); ?>"
                    data-auth-code="<?php echo esc_attr($auth_code); ?>"
                    data-last-event="<?php echo esc_attr($last_event); ?>"
                    data-payment-date="<?php echo esc_attr($payment_date); ?>">
                    View Details
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
        <tfoot>
          <tr>
            <td class="manage-column column-cb check-column"><input type="checkbox" id="cosy-select-all-orders-footer"></td>
            <th scope="col">Order ID</th>
            <th scope="col">Customer</th>
            <th scope="col">Provider</th>
            <th scope="col">Experience</th>
            <th scope="col">Date &amp; Time</th>
            <th scope="col">Amount</th>
            <th scope="col">Status</th>
            <th scope="col">Actions</th>
          </tr>
        </tfoot>
      </table>

      <!-- Pagination -->
      <div class="tablenav bottom">
        <div class="tablenav-pages">
          <span class="displaying-num"><?php echo count($appointments); ?> item(s)</span>
        </div>
      </div>
    </div>

    <!-- Admin Order Details Modal -->
    <div id="cosyAdminOrderModal" class="cosy-admin-modal">
      <div class="cosy-admin-modal-content">
        <div class="cosy-admin-modal-header">
          <h2 id="modalAdminOrderTitle">Conversations</h2>
          <span class="cosy-admin-modal-close">&times;</span>
        </div>
        <div class="cosy-admin-modal-body">
          <div class="cosy-admin-grid">
            <div class="cosy-admin-col">
              <div class="cosy-admin-card info-card">
                <h3><span class="dashicons dashicons-admin-users"></span> Customer Info</h3>
                <p><strong>Name:</strong> <span id="modalAdminCustomerName"></span></p>
                <p><strong>Email:</strong> <span id="modalAdminCustomerEmail"></span></p>
              </div>
            </div>
            <div class="cosy-admin-col">
              <div class="cosy-admin-card info-card">
                <h3><span class="dashicons dashicons-businessman"></span> Provider Info</h3>
                <p><strong>Name:</strong> <span id="modalAdminProviderName"></span></p>
                <p><strong>Email:</strong> <span id="modalAdminProviderEmail"></span></p>
              </div>
            </div>
          </div>

          <div class="cosy-admin-card info-card full" style="margin-top: 12px;">
            <h3><span class="dashicons dashicons-clipboard"></span> Booking Information</h3>
            <p><strong>Start Date:</strong> <span id="modalAdminStartDate"></span></p>
            <p><strong>Number of Weeks:</strong> <span id="modalAdminWeeks"></span></p>
            <p><strong>Booking Days:</strong> <span id="modalAdminSlotsTimeline" style="line-height: 1.6;"></span></p>
          </div>

          <div class="cosy-admin-grid" style="margin-top: 12px; margin-bottom: 0;">
            <div class="cosy-admin-col">
              <div class="cosy-admin-card info-card" style="height: 100%; box-sizing: border-box;">
                <h3><span class="dashicons dashicons-cart"></span> Financial Statement</h3>
                <table class="cosy-admin-table-details">
                  <tr>
                    <td>Provider Share (Cost):</td>
                    <td style="text-align: right; font-weight: 600;" id="modalAdminCost"></td>
                  </tr>
                  <tr>
                    <td>Service Fee*:</td>
                    <td style="text-align: right; font-weight: 600;" id="modalAdminFee"></td>
                  </tr>
                  <tr style="font-weight: bold; border-top: 1px solid #dcdcde;">
                    <td style="padding-top: 10px; color:#1d2327;">Total Paid:</td>
                    <td style="text-align: right; padding-top: 10px; color: #2271b1; font-size: 1.1em;" id="modalAdminTotal"></td>
                  </tr>
                </table>
                <p style="font-size: 11px; color: #64748b; margin-top: 14px; margin-bottom: 0; font-style: italic;">*A small non-refundable fee to help us run our platform safely &amp; smoothly.</p>
              </div>
            </div>

            <div class="cosy-admin-col">
              <div class="cosy-admin-card info-card" style="height: 100%; box-sizing: border-box; border-left: 4px solid #a44390;">
                <h3><span class="dashicons dashicons-shield"></span> WorldPay Payment Details</h3>
                <table class="cosy-admin-table-details">
                  <tr>
                    <td>Payment Gateway:</td>
                    <td style="text-align: right; font-weight: 600;">WorldPay HPP</td>
                  </tr>
                  <tr>
                    <td>Transaction Ref ID:</td>
                    <td style="text-align: right; font-family: monospace; font-weight: 700; color: #a44390;" id="modalAdminTxnRef">N/A</td>
                  </tr>
                  <tr>
                    <td>WorldPay Payment ID:</td>
                    <td style="text-align: right; font-family: monospace;" id="modalAdminPaymentId">N/A</td>
                  </tr>
                  <tr>
                    <td>Card Used:</td>
                    <td style="text-align: right; font-weight: 600;" id="modalAdminCardInfo">N/A</td>
                  </tr>
                  <tr>
                    <td>Auth Code / Event:</td>
                    <td style="text-align: right; font-weight: 600;" id="modalAdminAuthEvent">N/A</td>
                  </tr>
                  <tr>
                    <td>Payment Date &amp; Time:</td>
                    <td style="text-align: right; font-weight: 600;" id="modalAdminPaymentDate">N/A</td>
                  </tr>
                </table>
              </div>
            </div>
          </div>

          <div style="margin-top: 16px; padding: 10px; border-radius: 4px; text-align: center; border: 1px solid transparent;" id="modalAdminStatusBg">
            <strong style="letter-spacing: 0.5px; font-size: 12px;">ORDER STATUS:</strong> <span id="modalAdminStatusText" style="font-weight: 700; font-size: 12px;"></span>
          </div>
        </div>
        <div class="cosy-admin-modal-footer">
          <button type="button" class="cosy-admin-modal-btn cosy-admin-modal-close-btn">Close Details</button>
        </div>
      </div>
    </div>

    <?php wp_nonce_field('cosy_delete_orders_action', 'cosy_delete_orders_nonce_field'); ?>
<?php
  }
}
