<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap cosy-orders cosy-users-admin">
  <h1 class="wp-heading-inline"><?php esc_html_e('Reviews', 'cosy-appointments'); ?></h1>
  <?php wp_nonce_field('cosy_admin_nonce', 'cosy_admin_nonce'); ?>
  <hr class="wp-header-end">

  <div class="admin-succes cosy-media-table-container"></div>

  <!-- Premium Control Bar (Identical to Orders Page) -->
  <div class="cosy-control-bar">
    <div class="cosy-control-left">
      <form method="get" class="cosy-filter-form-modern">
        <input type="hidden" name="page" value="cosy-reviews">
        
        <div class="cosy-select-wrapper">
          <span class="dashicons dashicons-filter cosy-search-input-icon"></span>
          <select name="status" id="filter-status">
            <option value=""><?php esc_html_e('All Statuses', 'cosy-appointments'); ?></option>
            <option value="pending" <?php selected($status_filter, 'pending'); ?>><?php esc_html_e('Pending', 'cosy-appointments'); ?></option>
            <option value="approved" <?php selected($status_filter, 'approved'); ?>><?php esc_html_e('Approved', 'cosy-appointments'); ?></option>
            <option value="rejected" <?php selected($status_filter, 'rejected'); ?>><?php esc_html_e('Rejected', 'cosy-appointments'); ?></option>
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
          <input type="search" name="s" value="<?php echo esc_attr($search_query ?? ''); ?>" placeholder="<?php esc_attr_e('Search reviews...', 'cosy-appointments'); ?>" class="cosy-search-input-field">
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
        <span class="dashicons dashicons-trash cosy-btn-icon-trash"></span>
        <span class="cosy-btn-text cosy-btn-text-vmiddle"><?php esc_html_e('Delete', 'cosy-appointments'); ?></span>
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
        <th scope="col" class="manage-column"><?php esc_html_e('Customer', 'cosy-appointments'); ?></th>
        <th scope="col" class="manage-column"><?php esc_html_e('Provider', 'cosy-appointments'); ?></th>
        <th scope="col" class="manage-column cosy-col-rating"><?php esc_html_e('Rating', 'cosy-appointments'); ?></th>
        <th scope="col" class="manage-column cosy-col-review-reply"><?php esc_html_e('Review &amp; Reply', 'cosy-appointments'); ?></th>
        <th scope="col" class="manage-column"><?php esc_html_e('Date', 'cosy-appointments'); ?></th>
        <th scope="col" class="manage-column"><?php esc_html_e('Status', 'cosy-appointments'); ?></th>
        <th scope="col" class="manage-column cosy-col-actions"><?php esc_html_e('Actions', 'cosy-appointments'); ?></th>
      </tr>
    </thead>
    <tbody id="the-list">
      <?php if (empty($reviews)) : ?>
        <tr>
          <td colspan="8" class="text-center cosy-table-empty-cell">
            <?php esc_html_e('No reviews found matching the filter criteria.', 'cosy-appointments'); ?>
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
              <span class="cosy-admin-rating-badge">
                <?php echo intval($rev->rating); ?> / 10
              </span>
            </td>
            <td class="cosy-admin-review-cell">
              <div class="cosy-admin-review-card">
                <div class="cosy-admin-review-text">
                  <span class="cosy-admin-review-quote">“</span><?php echo esc_html($rev->review); ?><span class="cosy-admin-review-quote">”</span>
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
                  echo '<div class="cosy-admin-thread-timeline">';
                  foreach ($thread_replies as $tr) :
                    $r_lvl = intval($tr['reply_level']);
                    $badge_class = ($r_lvl === 1) ? 'cosy-badge-lvl-1' : (($r_lvl === 2) ? 'cosy-badge-lvl-2' : 'cosy-badge-lvl-3');
                    $level_label = ($r_lvl === 1) ? __('Provider Reply (L1)', 'cosy-appointments') : (($r_lvl === 2) ? __('Customer Follow-up (L2)', 'cosy-appointments') : __('Provider Closing (L3)', 'cosy-appointments'));
                ?>
                    <div class="cosy-admin-thread-item">
                      <div class="cosy-admin-thread-header">
                        <span class="cosy-admin-thread-badge <?php echo esc_attr($badge_class); ?>"><?php echo esc_html($level_label); ?></span>
                        <strong class="cosy-admin-thread-sender"><?php echo esc_html($tr['sender_name']); ?>:</strong>
                        <span class="cosy-order-weekly-subtext ms-auto"><?php echo esc_html(date('d M Y - h:i A', strtotime($tr['created_at']))); ?></span>
                      </div>
                      <div class="cosy-admin-thread-body"><?php echo esc_html($tr['reply_text']); ?></div>
                    </div>
                <?php
                  endforeach;
                  echo '</div>';
                endif;
                ?>
              </div>
            </td>
            <td class="cosy-order-datetime-cell"><?php echo esc_html(date('M d, Y', strtotime($rev->created_at))); ?></td>
            <td><span class="status <?php echo $status_class; ?>"><?php echo esc_html($rev->status); ?></span></td>
            <td>
              <div class="cosy-actions-cell-wrapper">
                <?php if ($rev->status !== 'approved') : ?>
                  <button type="button" class="button button-small btn-approve-review cosy-btn-approve-review" data-id="<?php echo $rev->id; ?>" title="<?php esc_attr_e('Approve Review', 'cosy-appointments'); ?>">
                    <span class="dashicons dashicons-yes-alt cosy-review-action-icon"></span>
                  </button>
                <?php endif; ?>
                <?php if ($rev->status !== 'rejected') : ?>
                  <button type="button" class="button button-small btn-reject-review cosy-btn-reject-review" data-id="<?php echo $rev->id; ?>" title="<?php esc_attr_e('Reject Review', 'cosy-appointments'); ?>">
                    <span class="dashicons dashicons-dismiss cosy-review-action-icon"></span>
                  </button>
                <?php endif; ?>
                <button type="button" class="button button-small btn-delete-review cosy-btn-delete-review" data-id="<?php echo $rev->id; ?>" title="<?php esc_attr_e('Delete Review', 'cosy-appointments'); ?>">
                  <span class="dashicons dashicons-trash cosy-review-action-icon"></span>
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
        <th scope="col"><?php esc_html_e('Customer', 'cosy-appointments'); ?></th>
        <th scope="col"><?php esc_html_e('Provider', 'cosy-appointments'); ?></th>
        <th scope="col"><?php esc_html_e('Rating', 'cosy-appointments'); ?></th>
        <th scope="col"><?php esc_html_e('Review &amp; Reply', 'cosy-appointments'); ?></th>
        <th scope="col"><?php esc_html_e('Date', 'cosy-appointments'); ?></th>
        <th scope="col"><?php esc_html_e('Status', 'cosy-appointments'); ?></th>
        <th scope="col"><?php esc_html_e('Actions', 'cosy-appointments'); ?></th>
      </tr>
    </tfoot>
  </table>

  <!-- Pagination Navigation -->
  <div class="tablenav bottom cosy-tablenav-bottom">
    <div class="alignleft actions">
      <span class="displaying-num cosy-displaying-num">
        <?php printf(esc_html(_n('%s review', '%s reviews', $total_reviews, 'cosy-appointments')), number_format_i18n($total_reviews)); ?>
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
