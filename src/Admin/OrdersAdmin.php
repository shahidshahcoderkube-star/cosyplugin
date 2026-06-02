<?php

namespace Cosy\Appointments\Admin;

use WP_Query;

class OrdersAdmin
{
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
            <th scope="col" class="manage-column">Service</th>
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
                No orders found matching the filter criteria.
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
              $slots_timeline  = get_post_meta($appt_id, 'cosy_slots_timeline', true);
              if (empty($booking_status)) {
                $booking_status = 'pending';
              }
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
                    data-slots-timeline="<?php echo esc_attr($slots_timeline); ?>">
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
            <th scope="col">Service</th>
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
          <h2 id="modalAdminOrderTitle">Order Details</h2>
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
              </div>
            </div>
          </div>

          <div class="cosy-admin-card info-card full" style="margin-top: 12px;">
            <h3><span class="dashicons dashicons-clipboard"></span> Service &amp; Schedule</h3>
            <p><strong>Service:</strong> <span id="modalAdminServiceName"></span></p>
            <p><strong>Schedule:</strong> <span id="modalAdminSchedule"></span></p>
            <p><strong>Duration:</strong> <span id="modalAdminDuration"></span></p>
            <p><strong>Weeks Booked:</strong> <span id="modalAdminWeeks"></span></p>
            <p><strong>Week Days:</strong> <span id="modalAdminWeekDays"></span></p>
            <p><strong>Selected Slots:</strong> <span id="modalAdminSlotsTimeline"></span></p>
          </div>

          <div class="cosy-admin-card info-card full" style="margin-top: 12px;">
            <h3><span class="dashicons dashicons-cart"></span> Financial Statement</h3>
            <table class="cosy-admin-table-details">
              <tr>
                <td>Provider Share (Cost):</td>
                <td style="text-align: right; font-weight: 600;" id="modalAdminCost"></td>
              </tr>
              <tr>
                <td>Platform Fee:</td>
                <td style="text-align: right; font-weight: 600;" id="modalAdminFee"></td>
              </tr>
              <tr style="font-weight: bold; border-top: 1px solid #dcdcde;">
                <td style="padding-top: 10px; color:#1d2327;">Total Paid:</td>
                <td style="text-align: right; padding-top: 10px; color: #2271b1; font-size: 1.1em;" id="modalAdminTotal"></td>
              </tr>
            </table>
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
