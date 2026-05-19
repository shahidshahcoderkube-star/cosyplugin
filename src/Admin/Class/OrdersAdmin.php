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
    <style>
      /* Space & Padding Fixes matching WordPress standard */
      .cosy-orders-filters {
        display: block !important;
        width: 100% !important;
      }

      .cosy-orders-filters .tablenav.top {
        margin: 0 0 16px 0 !important;
        height: auto !important;
        padding: 0 !important;
      }

      .cosy-orders-filters select {
        margin-right: 8px !important;
        height: 30px !important;
        padding: 0 24px 0 8px !important;
        border-radius: 4px !important;
        border: 1px solid #8c8f94 !important;
        background-color: #ffffff !important;
        color: #2c3338 !important;
        font-size: 13px !important;
      }

      /* WordPress Standard Primary Blue Button */
      .cosy-orders-filters input[type="submit"] {
        margin-left: 4px !important;
        height: 30px !important;
        line-height: 28px !important;
        padding: 0 14px !important;
        background: #2271b1 !important;
        border-color: #2271b1 !important;
        color: #ffffff !important;
        text-shadow: none !important;
        font-weight: 500 !important;
        border-radius: 4px !important;
        box-shadow: none !important;
        cursor: pointer !important;
        transition: background 0.1s ease-in-out, border-color 0.1s ease-in-out, color 0.1s ease-in-out !important;
      }

      .cosy-orders-filters input[type="submit"]:hover {
        background: #135e96 !important;
        border-color: #135e96 !important;
        color: #ffffff !important;
      }

      /* Dynamic Table Spacing Gap */
      .cosy-orders-table {
        margin-top: 16px !important;
        border: 1px solid #c3c4c7 !important;
        box-shadow: none !important;
      }

      /* Native WordPress status indicator styles */
      .wrap.cosy-orders span.status {
        padding: 3px 10px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 11px;
        display: inline-block;
        text-align: center;
        text-transform: capitalize;
        border: 1px solid transparent;
      }

      .wrap.cosy-orders span.status.pending {
        background: #fcf0e1;
        color: #d97706;
        border-color: #fcd34d;
      }

      .wrap.cosy-orders span.status.completed,
      .wrap.cosy-orders span.status.confirmed {
        background: #e6fcf5;
        color: #0ca678;
        border-color: #c3fae8;
      }

      .wrap.cosy-orders span.status.cancelled {
        background: #fff5f5;
        color: #fa5252;
        border-color: #ffe3e3;
      }

      .cosy-orders-table td,
      .cosy-orders-table th {
        vertical-align: middle !important;
      }

      /* Native conforming modal UI */
      .cosy-admin-modal {
        display: none;
        position: fixed;
        z-index: 99999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.4);
      }

      .cosy-admin-modal-content {
        background-color: #ffffff;
        margin: 7% auto;
        padding: 0;
        border-radius: 4px;
        width: 90%;
        max-width: 650px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        overflow: hidden;
        border: 1px solid #c3c4c7;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
      }

      .cosy-admin-modal-header {
        background: #1d2327;
        color: #ffffff;
        padding: 14px 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #c3c4c7;
      }

      .cosy-admin-modal-header h2 {
        color: #ffffff !important;
        margin: 0 !important;
        font-size: 15px !important;
        font-weight: 600 !important;
      }

      .cosy-admin-modal-close {
        color: #ffffff;
        font-size: 22px;
        font-weight: 400;
        cursor: pointer;
        line-height: 1;
        opacity: 0.8;
      }

      .cosy-admin-modal-close:hover {
        opacity: 1;
      }

      .cosy-admin-modal-body {
        padding: 20px;
        color: #2c3338;
      }

      .cosy-admin-grid {
        display: flex;
        gap: 12px;
        margin-bottom: 12px;
      }

      .cosy-admin-col {
        flex: 1;
        min-width: 0;
      }

      .cosy-admin-card {
        padding: 12px 14px;
        border-radius: 4px;
        border: 1px solid #c3c4c7;
        background: #f6f7f7;
      }

      .cosy-admin-card.info-card {
        background-color: #ffffff;
      }

      .cosy-admin-card h3 {
        margin-top: 0 !important;
        margin-bottom: 10px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #1d2327 !important;
        display: flex;
        align-items: center;
        gap: 6px;
        border-bottom: 1px solid #dcdcde;
        padding-bottom: 6px;
      }

      .cosy-admin-card h3 span.dashicons {
        color: #2271b1;
        font-size: 16px;
        width: 16px;
        height: 16px;
      }

      .cosy-admin-card p {
        margin: 6px 0 !important;
        font-size: 12px !important;
        color: #2c3338;
      }

      .cosy-admin-table-details {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
      }

      .cosy-admin-table-details td {
        padding: 6px 0;
        color: #2c3338;
      }

      .cosy-admin-modal-footer {
        padding: 12px 18px;
        background: #f6f7f7;
        border-top: 1px solid #c3c4c7;
        display: flex;
        justify-content: flex-end;
      }

      .cosy-admin-modal-btn {
        background: #2271b1;
        border: 1px solid #2271b1;
        color: white;
        padding: 6px 14px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
      }

      .cosy-admin-modal-btn:hover {
        background: #135e96;
        border-color: #135e96;
      }

      form.cosy-orders-filters {
        padding-bottom: 20px !important;
      }
    </style>

    <div class="wrap cosy-orders">
      <h1 class="wp-heading-inline">Orders</h1>
      <hr class="wp-header-end">

      <!-- Filters -->
      <form method="get" class="cosy-orders-filters">
        <input type="hidden" name="page" value="cosy-orders">
        <div class="tablenav top">
          <div class="alignleft actions">
            <label for="filter-status" class="screen-reader-text">Filter by status</label>
            <select name="status" id="filter-status">
              <option value="">All Statuses</option>
              <option value="pending" <?php selected($status_filter, 'pending'); ?>>Pending</option>
              <option value="completed" <?php selected($status_filter, 'completed'); ?>>Completed</option>
              <option value="cancelled" <?php selected($status_filter, 'cancelled'); ?>>Cancelled</option>
            </select>

            <label for="filter-provider" class="screen-reader-text">Filter by provider</label>
            <select name="provider" id="filter-provider">
              <option value="">All Providers</option>
              <?php foreach ($providers as $prov) : ?>
                <option value="<?php echo $prov->ID; ?>" <?php selected($provider_filter, $prov->ID); ?>>
                  <?php echo esc_html($prov->display_name); ?>
                </option>
              <?php endforeach; ?>
            </select>

            <input type="submit" class="button" value="Filter">
          </div>
        </div>
      </form>

      <!-- Orders Table -->
      <table class="wp-list-table widefat fixed striped table-view-list cosy-orders-table">
        <thead>
          <tr>
            <td id="cb" class="manage-column column-cb check-column">
              <input type="checkbox">
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
              if (empty($booking_status)) {
                $booking_status = 'pending';
              }
            ?>
              <tr>
                <th scope="row" class="check-column"><input type="checkbox"></th>
                <td>#<?php echo $appt_id; ?></td>
                <td><strong><?php echo esc_html($customer_name); ?></strong></td>
                <td><?php echo esc_html($provider_name); ?></td>
                <td><span class="badge" style="background:#f1f5f9; padding:4px 8px; border-radius:4px; border:1px solid #cbd5e1; color:#334155; font-size:0.85em;"><?php echo esc_html($service_name); ?></span></td>
                <td style="color:#475569; font-size:12px;"><?php echo esc_html($start_date); ?> <br><span style="color:#94a3b8; font-size:11px;"><?php echo esc_html($weekly_booking); ?></span></td>
                <td><strong>£<?php echo esc_html($total_payable); ?></strong></td>
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
                    data-status="<?php echo esc_attr($booking_status); ?>">
                    View Details
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
        <tfoot>
          <tr>
            <td class="manage-column column-cb check-column"><input type="checkbox"></td>
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

    <script>
      jQuery(document).ready(function($) {
        // Open modal and populate fields dynamically
        $('.btn-view-admin-order-details').on('click', function(e) {
          e.preventDefault();
          const id = $(this).data('id');
          const customer = $(this).data('customer');
          const email = $(this).data('customer-email');
          const provider = $(this).data('provider');
          const service = $(this).data('service');
          const start = $(this).data('start');
          const end = $(this).data('end');
          const weekly = $(this).data('weekly');
          const weeks = $(this).data('weeks');
          const slots = $(this).data('slots');
          const cost = $(this).data('cost');
          const fee = $(this).data('fee');
          const total = $(this).data('total');
          const status = $(this).data('status');

          $('#modalAdminOrderTitle').text('Order Details - #' + id);
          $('#modalAdminCustomerName').text(customer);
          $('#modalAdminCustomerEmail').text(email || 'N/A');
          $('#modalAdminProviderName').text(provider);
          $('#modalAdminServiceName').text(service);
          $('#modalAdminSchedule').text(weekly);
          if (start && end) {
            $('#modalAdminDuration').text(start + ' to ' + end);
          } else {
            $('#modalAdminDuration').text(start || 'N/A');
          }
          $('#modalAdminWeeks').text(weeks + ' week(s) (' + slots + ' slots booked)');

          $('#modalAdminCost').text('£' + cost);
          $('#modalAdminFee').text('£' + fee);
          $('#modalAdminTotal').text('£' + total);

          // Status styling helper inside modal conforming to WP standards
          let bg = '';
          let color = '';
          let border = '';
          if (status === 'completed' || status === 'confirmed') {
            bg = '#e6fcf5';
            color = '#0ca678';
            border = '#c3fae8';
          } else if (status === 'cancelled') {
            bg = '#fff5f5';
            color = '#fa5252';
            border = '#ffe3e3';
          } else {
            bg = '#fcf0e1';
            color = '#d97706';
            border = '#fcd34d';
          }
          $('#modalAdminStatusBg').css({
            'background-color': bg,
            'color': color,
            'border-color': border
          });
          $('#modalAdminStatusText').text(status.toUpperCase());

          $('#cosyAdminOrderModal').fadeIn(150);
        });

        // Close modal triggers
        $('.cosy-admin-modal-close, .cosy-admin-modal-close-btn').on('click', function() {
          $('#cosyAdminOrderModal').fadeOut(120);
        });

        // Outside modal bounds close
        $(window).on('click', function(event) {
          if ($(event.target).is('#cosyAdminOrderModal')) {
            $('#cosyAdminOrderModal').fadeOut(120);
          }
        });
      });
    </script>
<?php
  }
}
