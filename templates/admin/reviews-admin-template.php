<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap cosy-orders cosy-users-admin">
  <h1 class="wp-heading-inline"><?php esc_html_e('Reviews', 'cosy-appointments'); ?></h1>
  <?php wp_nonce_field('cosy_admin_nonce', 'cosy_admin_nonce'); ?>
  <hr class="wp-header-end">

  <div class="admin-succes" style="margin-top: 15px;"></div>

  <!-- Premium Control Bar (Identical to Orders Page) -->
  <div class="cosy-control-bar">
    <div class="cosy-control-left">
      <form method="get" class="cosy-filter-form-modern" style="margin: 0; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
        <input type="hidden" name="page" value="cosy-reviews">
        
        <div class="cosy-select-wrapper">
          <span class="dashicons dashicons-filter" style="color: #94a3b8; margin-left: 10px; margin-right: 2px;"></span>
          <select name="status" id="filter-status">
            <option value=""><?php esc_html_e('All Statuses', 'cosy-appointments'); ?></option>
            <option value="pending" <?php selected($status_filter, 'pending'); ?>><?php esc_html_e('Pending', 'cosy-appointments'); ?></option>
            <option value="approved" <?php selected($status_filter, 'approved'); ?>><?php esc_html_e('Approved', 'cosy-appointments'); ?></option>
            <option value="rejected" <?php selected($status_filter, 'rejected'); ?>><?php esc_html_e('Rejected', 'cosy-appointments'); ?></option>
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

        <div class="cosy-search-input-wrapper">
          <span class="dashicons dashicons-search" style="color: #94a3b8; margin-left: 10px; margin-right: 2px;"></span>
          <input type="search" name="s" value="<?php echo esc_attr($search_query ?? ''); ?>" placeholder="<?php esc_attr_e('Search reviews...', 'cosy-appointments'); ?>" style="border: none; background: transparent; height: 34px; font-size: 13px; color: #334155; outline: none; padding-right: 10px; min-width: 180px;">
        </div>

        <button type="submit" class="cosy-filter-btn">
          <?php esc_html_e('Filter', 'cosy-appointments'); ?>
        </button>
        <?php if (!empty($status_filter) || !empty($provider_filter) || !empty($search_query)) : ?>
          <a href="<?php echo esc_url(admin_url('admin.php?page=cosy-reviews')); ?>" class="cosy-reset-btn">
            <?php esc_html_e('Reset', 'cosy-appointments'); ?>
          </a>
        <?php endif; ?>
      </form>
    </div>

    <div class="cosy-control-right">
      <button type="button" class="cosy-btn-delete-selected-modern" id="cosy-reviews-btn-delete-selected" disabled>
        <span class="dashicons dashicons-trash" style="font-size: 16px; width: 16px; height: 16px; margin-right: 6px; display: inline-block; vertical-align: middle;"></span>
        <span class="cosy-btn-text" style="vertical-align: middle;"><?php esc_html_e('Delete', 'cosy-appointments'); ?></span>
      </button>
    </div>
  </div>

  <!-- Orders Style Table (100% Identical Copy of Orders Table) -->
  <table class="wp-list-table widefat fixed striped table-view-list cosy-orders-table">
    <thead>
      <tr>
        <td id="cb" class="manage-column column-cb check-column">
          <input type="checkbox" id="cosy-select-all-reviews">
        </td>
        <th scope="col" class="manage-column">Customer</th>
        <th scope="col" class="manage-column">Provider</th>
        <th scope="col" class="manage-column" style="width: 90px;">Rating</th>
        <th scope="col" class="manage-column" style="width: 30%;">Review &amp; Reply</th>
        <th scope="col" class="manage-column">Date</th>
        <th scope="col" class="manage-column">Status</th>
        <th scope="col" class="manage-column" style="width: 170px;">Actions</th>
      </tr>
    </thead>
    <tbody id="the-list">
      <?php if (empty($reviews)) : ?>
        <tr>
          <td colspan="8" class="text-center" style="text-align: center; padding: 40px; color: #64748b;">
            No reviews found matching the filter criteria.
          </td>
        </tr>
      <?php else : ?>
        <?php foreach ($reviews as $rev) :
          $provider_user = get_userdata($rev->provider_id);
          $provider_name = $provider_user ? $provider_user->display_name : 'Provider #' . $rev->provider_id;
          $status_class  = strtolower($rev->status);
        ?>
          <tr id="review-row-<?php echo $rev->id; ?>">
            <th scope="row" class="check-column"><input type="checkbox" class="cosy-review-checkbox" value="<?php echo $rev->id; ?>"></th>
            <td><strong><?php echo esc_html($rev->customer_name); ?></strong></td>
            <td><?php echo esc_html($provider_name); ?></td>
            <td>
              <div style="color: #a44390; font-weight: 700;">
                <?php echo intval($rev->rating); ?> / 10
              </div>
            </td>
            <td style="color:#475569; font-size:12px;">
              <div style="font-style: normal; background: #f8fafc; padding: 8px 12px; border-radius: 8px; border-left: 3px solid #cbd5e1; margin-bottom: 6px; color: #334155; line-height: 1.4;">
                <strong>Customer Review:</strong> "<?php echo esc_html($rev->review); ?>"
              </div>
              <?php
              $replies_table = $wpdb->prefix . 'cosy_review_replies';
              $thread_replies = $wpdb->get_results($wpdb->prepare("SELECT * FROM $replies_table WHERE review_id = %d ORDER BY reply_level ASC, created_at ASC", $rev->id), ARRAY_A);
              
              // Prepend legacy provider_reply if Level 1 is missing in replies table
              $has_l1_adm = false;
              foreach ($thread_replies as $tr_chk) {
                if (intval($tr_chk['reply_level']) === 1) {
                  $has_l1_adm = true;
                  break;
                }
              }
              if (!$has_l1_adm && !empty($rev->provider_reply)) {
                array_unshift($thread_replies, [
                  'reply_level' => 1,
                  'sender_name' => $provider_name,
                  'sender_role' => 'provider',
                  'reply_text'  => $rev->provider_reply,
                  'created_at'  => $rev->reply_date ?: $rev->created_at
                ]);
              }

              if (!empty($thread_replies)) :
                foreach ($thread_replies as $tr) :
                  $r_lvl = intval($tr['reply_level']);
                  $level_label = ($r_lvl === 1) ? 'Provider Reply (L1)' : (($r_lvl === 2) ? 'Customer Follow-up (L2)' : 'Provider Closing (L3)');
                  $badge_bg = ($r_lvl === 1) ? '#fdf5fc' : (($r_lvl === 2) ? '#eff6ff' : '#f0fdf4');
                  $badge_color = ($r_lvl === 1) ? '#6d2e67' : (($r_lvl === 2) ? '#1e40af' : '#065f46');
                  $border_color = ($r_lvl === 1) ? '#a44390' : (($r_lvl === 2) ? '#3b82f6' : '#10b981');
                  $indent = ($r_lvl > 1) ? 'margin-left: ' . (($r_lvl - 1) * 14) . 'px;' : '';
              ?>
                  <div style="margin-top: 6px; <?php echo $indent; ?> padding: 8px 10px; background: <?php echo $badge_bg; ?>; border-left: 3px solid <?php echo $border_color; ?>; border-radius: 8px; font-size: 11px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3px;">
                      <strong style="color: <?php echo $badge_color; ?>; font-weight: 700;">
                        <span style="display: inline-block; padding: 2px 7px; background: <?php echo $border_color; ?>; color: #fff; border-radius: 10px; font-size: 9px; margin-right: 5px; text-transform: uppercase; letter-spacing: 0.3px;"><?php echo esc_html($level_label); ?></span>
                        <?php echo esc_html($tr['sender_name']); ?>
                      </strong>
                      <span style="color: #94a3b8; font-size: 10px;"><?php echo esc_html(date('d M Y - h:i A', strtotime($tr['created_at']))); ?></span>
                    </div>
                    <div style="color: #334155; line-height: 1.4; padding-left: 2px;"><?php echo esc_html($tr['reply_text']); ?></div>
                  </div>
              <?php
                endforeach;
              endif;
              ?>
            </td>
            <td style="color:#475569; font-size:12px;"><?php echo esc_html(date('M d, Y', strtotime($rev->created_at))); ?></td>
            <td><span class="status <?php echo $status_class; ?>"><?php echo esc_html($rev->status); ?></span></td>
            <td>
              <div style="display: flex; gap: 6px; align-items: center;">
                <?php if ($rev->status !== 'approved') : ?>
                  <button type="button" class="button button-small btn-approve-review" data-id="<?php echo $rev->id; ?>" title="<?php esc_attr_e('Approve Review', 'cosy-appointments'); ?>" style="color: #16a34a; border-color: #bbf7d0; background: #f0fdf4; padding: 3px 8px; height: 30px; border-radius: 6px;">
                    <span class="dashicons dashicons-yes-alt" style="font-size: 16px; width: 16px; height: 16px; line-height: 16px; vertical-align: middle;"></span>
                  </button>
                <?php endif; ?>
                <?php if ($rev->status !== 'rejected') : ?>
                  <button type="button" class="button button-small btn-reject-review" data-id="<?php echo $rev->id; ?>" title="<?php esc_attr_e('Reject Review', 'cosy-appointments'); ?>" style="color: #d97706; border-color: #fef08a; background: #fefce8; padding: 3px 8px; height: 30px; border-radius: 6px;">
                    <span class="dashicons dashicons-dismiss" style="font-size: 16px; width: 16px; height: 16px; line-height: 16px; vertical-align: middle;"></span>
                  </button>
                <?php endif; ?>
                <button type="button" class="button button-small btn-delete-review" data-id="<?php echo $rev->id; ?>" title="<?php esc_attr_e('Delete Review', 'cosy-appointments'); ?>" style="color: #dc2626; border-color: #fecaca; background: #fef2f2; padding: 3px 8px; height: 30px; border-radius: 6px;">
                  <span class="dashicons dashicons-trash" style="font-size: 16px; width: 16px; height: 16px; line-height: 16px; vertical-align: middle;"></span>
                </button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
    <tfoot>
      <tr>
        <td class="manage-column column-cb check-column"><input type="checkbox" id="cosy-select-all-reviews-footer"></td>
        <th scope="col">Customer</th>
        <th scope="col">Provider</th>
        <th scope="col">Rating</th>
        <th scope="col">Review &amp; Reply</th>
        <th scope="col">Date</th>
        <th scope="col">Status</th>
        <th scope="col">Actions</th>
      </tr>
    </tfoot>
  </table>

  <!-- Pagination Navigation -->
  <div class="tablenav bottom" style="margin-top: 15px; display: flex; justify-content: space-between; align-items: center;">
    <div class="alignleft actions">
      <span class="displaying-num" style="color: #64748b; font-weight: 600;">
        <?php printf(esc_html(_n('%s review', '%s reviews', $total_reviews, 'cosy-appointments')), number_format_i18n($total_reviews)); ?>
      </span>
    </div>
    <?php if ($total_pages > 1) : ?>
      <div class="tablenav-pages">
        <span class="pagination-links" style="display: flex; align-items: center; gap: 4px;">
          <?php if ($paged > 1) : ?>
            <a class="first-page button" href="<?php echo esc_url(remove_query_arg('paged')); ?>" title="<?php esc_attr_e('First page', 'cosy-appointments'); ?>">&laquo;</a>
            <a class="prev-page button" href="<?php echo esc_url(add_query_arg('paged', max(1, $paged - 1))); ?>" title="<?php esc_attr_e('Previous page', 'cosy-appointments'); ?>">&lsaquo;</a>
          <?php else : ?>
            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>
            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>
          <?php endif; ?>

          <span class="paging-input" style="margin: 0 8px; font-weight: 500;">
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
