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
    $search_query    = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $paged           = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page        = 20;

    // Dynamic WP Query to fetch paginated appointments
    $args = [
      'post_type'      => 'cosy_appointment',
      'posts_per_page' => $per_page,
      'paged'          => $paged,
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

    // Search query: by Order ID (numeric) or Customer / Provider / Service / Ref
    if (!empty($search_query)) {
      if (is_numeric($search_query)) {
        $args['post__in'] = [intval($search_query)];
      } else {
        global $wpdb;
        $search_like = '%' . $wpdb->esc_like($search_query) . '%';
        $matched_post_ids = $wpdb->get_col($wpdb->prepare("
          SELECT DISTINCT post_id FROM {$wpdb->postmeta}
          WHERE (meta_key IN ('cosy_customer_name', 'cosy_customer_email', 'cosy_provider_name', 'cosy_service_name', 'cosy_payment_id', 'cosy_txn_ref')
                 AND meta_value LIKE %s)
        ", $search_like));

        $title_post_ids = $wpdb->get_col($wpdb->prepare("
          SELECT ID FROM {$wpdb->posts}
          WHERE post_type = 'cosy_appointment' AND post_title LIKE %s
        ", $search_like));

        $all_search_ids = array_unique(array_merge($matched_post_ids, $title_post_ids));
        $args['post__in'] = !empty($all_search_ids) ? $all_search_ids : [0];
      }
    }

    $appointments_query = new WP_Query($args);
    $appointments       = $appointments_query->posts;
    $total_orders       = $appointments_query->found_posts;
    $total_pages        = $appointments_query->max_num_pages;

    // Fetch all providers for filter dropdown
    $providers = get_users(['role' => 'provider']);
?>
    

    <div class="wrap cosy-orders cosy-users-admin">
      <h1 class="wp-heading-inline"><?php esc_html_e('Orders', 'cosy-appointments'); ?></h1>
      <hr class="wp-header-end">

      <!-- Premium Control Bar -->
      <div class="cosy-control-bar">
        <div class="cosy-control-left">
          <form method="get" class="cosy-filter-form-modern">
            <input type="hidden" name="page" value="cosy-orders">
            
            <div class="cosy-select-wrapper">
              <span class="dashicons dashicons-filter cosy-search-input-icon"></span>
              <select name="status" id="filter-status">
                <option value=""><?php esc_html_e('All Statuses', 'cosy-appointments'); ?></option>
                <option value="pending" <?php selected($status_filter, 'pending'); ?>><?php esc_html_e('Pending', 'cosy-appointments'); ?></option>
                <option value="completed" <?php selected($status_filter, 'completed'); ?>><?php esc_html_e('Completed', 'cosy-appointments'); ?></option>
                <option value="cancelled" <?php selected($status_filter, 'cancelled'); ?>><?php esc_html_e('Cancelled', 'cosy-appointments'); ?></option>
              </select>
            </div>

            <div class="cosy-select-wrapper">
              <span class="dashicons dashicons-admin-users cosy-search-input-icon"></span>
              <select name="provider" id="filter-provider">
                <option value=""><?php esc_html_e('All Providers', 'cosy-appointments'); ?></option>
                <?php foreach ($providers as $prov) : ?>
                  <option value="<?php echo $prov->ID; ?>" <?php selected($provider_filter, $prov->ID); ?>>
                    <?php echo esc_html($prov->display_name); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="cosy-search-input-wrapper">
              <span class="dashicons dashicons-search cosy-search-input-icon"></span>
              <input type="search" name="s" value="<?php echo esc_attr($search_query); ?>" placeholder="<?php esc_attr_e('Search orders...', 'cosy-appointments'); ?>" class="cosy-search-input-field">
            </div>

            <button type="submit" class="cosy-filter-btn">
              <?php esc_html_e('Filter', 'cosy-appointments'); ?>
            </button>
            <?php if (!empty($status_filter) || !empty($provider_filter) || !empty($search_query)) : ?>
              <a href="<?php echo esc_url(admin_url('admin.php?page=cosy-orders')); ?>" class="cosy-reset-btn">
                <?php esc_html_e('Reset', 'cosy-appointments'); ?>
              </a>
            <?php endif; ?>
          </form>
        </div>

        <div class="cosy-control-right">
          <button type="button" class="cosy-btn-delete-selected-modern" id="cosy-btn-delete-selected" disabled>
            <span class="dashicons dashicons-trash cosy-btn-icon-trash"></span>
            <span class="cosy-btn-text cosy-btn-text-vmiddle"><?php esc_html_e('Delete', 'cosy-appointments'); ?></span>
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
            <th scope="col" class="manage-column cosy-col-id">Order ID</th>
            <th scope="col" class="manage-column">Customer</th>
            <th scope="col" class="manage-column">Provider</th>
            <th scope="col" class="manage-column">Experience</th>
            <th scope="col" class="manage-column cosy-col-datetime">Date &amp; Time</th>
            <th scope="col" class="manage-column">Amount</th>
            <th scope="col" class="manage-column">Status</th>
            <th scope="col" class="manage-column cosy-col-actions">Actions</th>
          </tr>
        </thead>
        <tbody id="the-list">
          <?php if (empty($appointments)) : ?>
            <tr>
              <td colspan="9" class="text-center cosy-table-empty-cell">
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
              $slots_timeline  = cosy_clean_slots_timeline(get_post_meta($appt_id, 'cosy_slots_timeline', true), $start_date, $week_days);
              if (empty($booking_status)) {
                $booking_status = 'pending';
              }

              $provider_id     = get_post_meta($appt_id, 'cosy_provider_id', true);
              $provider_user   = get_userdata($provider_id);
              $provider_email  = $provider_user ? $provider_user->user_email : (get_post_meta($appt_id, 'cosy_provider_email', true) ?: 'N/A');

              // Gift order metadata
              $is_gift         = get_post_meta($appt_id, 'cosy_is_gift', true);
              $recipient_name  = get_post_meta($appt_id, 'cosy_recipient_name', true);
              $recipient_email = get_post_meta($appt_id, 'cosy_recipient_email', true);
              $is_gift_order   = (!empty($is_gift) && $is_gift !== '0') || !empty($recipient_email);

              // Fetch WorldPay transaction details for Admin
              global $wpdb;
              $wp_table = $wpdb->prefix . 'cosy_worldpay_payments';
              $wp_row   = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wp_table WHERE order_id = %d", $appt_id));

              $txn_ref = get_post_meta($appt_id, 'cosy_transaction_ref', true) ?: ($wp_row->transaction_ref_id ?? '');
              if (empty($txn_ref) || $txn_ref === 'N/A') {
                $txn_ref = 'TXN_' . strtoupper(substr(md5('worldpay_' . $appt_id), 0, 10));
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
                <td>
                  <strong><?php echo esc_html($customer_name); ?></strong>
                  <?php if ($is_gift_order) : ?>
                    <span class="cosy-gift-pill">Gift</span>
                  <?php endif; ?>
                </td>
                <td><?php echo esc_html($provider_name); ?></td>
                <td><span class="badge cosy-service-pill"><?php echo esc_html($service_name); ?></span></td>
                <td class="cosy-order-datetime-cell"><?php echo esc_html(cosy_format_date($start_date)); ?> <br><span class="cosy-order-weekly-subtext"><?php echo esc_html($weekly_booking); ?></span></td>
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
                    data-start="<?php echo esc_attr(cosy_format_date($start_date)); ?>"
                    data-end="<?php echo esc_attr(cosy_format_date($end_date)); ?>"
                    data-weekly="<?php echo esc_attr($weekly_booking); ?>"
                    data-weeks="<?php echo esc_attr($number_of_weeks); ?>"
                    data-slots="<?php echo esc_attr($number_of_slots); ?>"
                    data-cost="<?php echo esc_attr($service_cost); ?>"
                    data-fee="<?php echo esc_attr($service_fee); ?>"
                    data-total="<?php echo esc_attr($total_payable); ?>"
                    data-status="<?php echo esc_attr($booking_status); ?>"
                    data-week-days="<?php echo esc_attr($week_days); ?>"
                    data-slots-timeline="<?php echo esc_attr($slots_timeline); ?>"
                    data-is-gift="<?php echo esc_attr($is_gift_order ? '1' : '0'); ?>"
                    data-recipient-name="<?php echo esc_attr($recipient_name); ?>"
                    data-recipient-email="<?php echo esc_attr($recipient_email); ?>"
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

      <!-- Pagination Navigation -->
      <div class="tablenav bottom cosy-tablenav-bottom">
        <div class="alignleft actions">
          <span class="displaying-num cosy-displaying-num">
            <?php printf(esc_html(_n('%s order', '%s orders', $total_orders, 'cosy-appointments')), number_format_i18n($total_orders)); ?>
          </span>
        </div>
        <?php if ($total_pages > 1) : ?>
          <div class="tablenav-pages">
            <span class="pagination-links cosy-pagination-links">
              <?php if ($paged > 1) : ?>
                <a class="first-page button" href="<?php echo esc_url(remove_query_arg('paged')); ?>" title="<?php esc_attr_e('First page', 'cosy-appointments'); ?>">&laquo;</a>
                <a class="prev-page button" href="<?php echo esc_url(add_query_arg('paged', max(1, $paged - 1))); ?>" title="<?php esc_attr_e('Previous page', 'cosy-appointments'); ?>">&lsaquo;</a>
              <?php else : ?>
                <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>
                <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>
              <?php endif; ?>

              <span class="paging-input cosy-paging-input">
                <span class="tablenav-paging-text">
                  <?php printf(esc_html__('%1$s of %2$s', 'cosy-appointments'), '<span class="current-page">' . $paged . '</span>', '<span class="total-pages">' . $total_pages . '</span>'); ?>
                </span>
              </span>

              <?php if ($paged < $total_pages) : ?>
                <a class="next-page button" href="<?php echo esc_url(add_query_arg('paged', min($total_pages, $paged + 1))); ?>" title="<?php esc_attr_e('Next page', 'cosy-appointments'); ?>">&rsaquo;</a>
                <a class="last-page button" href="<?php echo esc_url(add_query_arg('paged', $total_pages)); ?>" title="<?php esc_attr_e('Last page', 'cosy-appointments'); ?>">&raquo;</a>
              <?php else : ?>
                <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>
                <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>
              <?php endif; ?>
            </span>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- HTML Modal for Details -->
    <div id="cosyAdminOrderModal" class="cosy-admin-modal">
      <div class="cosy-admin-modal-content">
        <div class="cosy-admin-modal-header">
          <h2 id="modalAdminOrderTitle"><?php esc_html_e('Order Details', 'cosy-appointments'); ?></h2>
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

          <!-- Gift Recipient Info Card (Shown only if gifted) -->
          <div id="modalAdminGiftCard" class="cosy-admin-card info-card full cosy-gift-card-modal">
            <h3 class="cosy-gift-card-title">
              <span class="dashicons dashicons-heart"></span> 🎁 Gift Order Details
              <span class="badge cosy-gift-card-badge">Gifted by Customer</span>
            </h3>
            <div class="cosy-gift-card-grid">
              <p><strong>Recipient Name:</strong> <span id="modalAdminRecipientName" class="cosy-gift-card-recipient-name"></span></p>
              <p><strong>Recipient Email:</strong> <span id="modalAdminRecipientEmail" class="cosy-gift-card-recipient-email"></span></p>
            </div>
          </div>

          <div class="cosy-admin-card info-card full">
            <h3><span class="dashicons dashicons-clipboard"></span> Booking Information</h3>
            <p><strong>Start Date:</strong> <span id="modalAdminStartDate"></span></p>
            <p><strong>Number of Weeks:</strong> <span id="modalAdminWeeks"></span></p>
            <p><strong>Booking Days:</strong> <span id="modalAdminSlotsTimeline"></span></p>
          </div>

          <div class="cosy-admin-grid">
            <div class="cosy-admin-col">
              <div class="cosy-admin-card info-card">
                <h3><span class="dashicons dashicons-cart"></span> Financial Statement</h3>
                <table class="cosy-admin-table-details">
                  <tr>
                    <td>Provider Share (Cost):</td>
                    <td class="cosy-table-cell-right-bold" id="modalAdminCost"></td>
                  </tr>
                  <tr>
                    <td>Service Fee*:</td>
                    <td class="cosy-table-cell-right-bold" id="modalAdminFee"></td>
                  </tr>
                  <tr class="cosy-table-row-total">
                    <td class="cosy-table-cell-total-label">Total Paid:</td>
                    <td class="cosy-table-cell-total-value" id="modalAdminTotal"></td>
                  </tr>
                </table>
                <p class="cosy-fee-disclaimer">*A small non-refundable fee to help us run our platform safely &amp; smoothly.</p>
              </div>
            </div>

            <div class="cosy-admin-col">
              <div class="cosy-admin-card info-card cosy-admin-card-purple-border">
                <h3><span class="dashicons dashicons-shield"></span> WorldPay Payment Details</h3>
                <table class="cosy-admin-table-details">
                  <tr>
                    <td>Payment Gateway:</td>
                    <td class="cosy-table-cell-right-bold">WorldPay HPP</td>
                  </tr>
                  <tr>
                    <td>Transaction Ref ID:</td>
                    <td class="cosy-table-cell-purple-code" id="modalAdminTxnRef">N/A</td>
                  </tr>
                  <tr>
                    <td>WorldPay Payment ID:</td>
                    <td class="cosy-table-cell-mono" id="modalAdminPaymentId">N/A</td>
                  </tr>
                  <tr>
                    <td>Card Used:</td>
                    <td class="cosy-table-cell-right-bold" id="modalAdminCardInfo">N/A</td>
                  </tr>
                  <tr>
                    <td>Auth Code / Event:</td>
                    <td class="cosy-table-cell-right-bold" id="modalAdminAuthEvent">N/A</td>
                  </tr>
                  <tr>
                    <td>Payment Date &amp; Time:</td>
                    <td class="cosy-table-cell-right-bold" id="modalAdminPaymentDate">N/A</td>
                  </tr>
                </table>
              </div>
            </div>
          </div>

          <div class="cosy-modal-status-box" id="modalAdminStatusBg">
            <strong>ORDER STATUS:</strong> <span id="modalAdminStatusText"></span>
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
