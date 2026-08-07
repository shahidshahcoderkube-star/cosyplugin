<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table = $wpdb->prefix . 'cosy_provider_reviews';

$status_filter   = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$provider_filter = isset($_GET['provider']) ? intval($_GET['provider']) : 0;

$where_clauses = ["1=1"];
if (!empty($status_filter)) {
    $where_clauses[] = $wpdb->prepare("status = %s", $status_filter);
}
if (!empty($provider_filter)) {
    $where_clauses[] = $wpdb->prepare("provider_id = %d", $provider_filter);
}

$where_sql = implode(' AND ', $where_clauses);
$reviews = $wpdb->get_results("SELECT * FROM $table WHERE $where_sql ORDER BY id DESC");

// Fetch all providers for filter dropdown
$providers = get_users(['role' => 'provider']);
?>

<div class="wrap cosy-orders cosy-users-admin">
  <h1 class="wp-heading-inline"><?php esc_html_e('Reviews', 'cosy-appointments'); ?></h1>
  <hr class="wp-header-end">

  <div class="admin-succes" style="margin-top: 15px;"></div>

  <!-- Premium Control Bar (Identical to Orders & Media Pages) -->
  <div class="cosy-control-bar">
    <div class="cosy-control-left">
      <form method="get" class="cosy-filter-form-modern" style="margin: 0; display: flex; align-items: center; gap: 10px;">
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

        <button type="submit" class="cosy-filter-btn">
          <?php esc_html_e('Filter', 'cosy-appointments'); ?>
        </button>
      </form>
    </div>

    <div class="cosy-control-right">
      <button type="button" class="cosy-btn-delete-selected-modern" id="cosy-reviews-btn-delete-selected" disabled>
        <span class="dashicons dashicons-trash" style="font-size: 16px; width: 16px; height: 16px; margin-right: 6px; display: inline-block; vertical-align: middle;"></span>
        <span class="cosy-btn-text" style="vertical-align: middle;"><?php esc_html_e('Delete', 'cosy-appointments'); ?></span>
      </button>
    </div>
  </div>

  <!-- Orders Style Table -->
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
              <?php $rating_val = max(1, min(10, intval($rev->rating))); ?>
              <span class="cosy-badge" style="background: #fdf5fc; color: #a44390; border: 1px solid rgba(164, 67, 144, 0.25); padding: 4px 12px; border-radius: 20px; font-weight: 700; font-size: 13px; display: inline-block; white-space: nowrap !important;">
                <?php echo $rating_val; ?>/10
              </span>
            </td>
            <td style="color:#475569; font-size:12px; vertical-align: top; max-width: 420px;">
              <div class="cosy-admin-review-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <!-- Customer Root Review -->
                <div style="font-size: 12px; color: #1e293b; font-weight: 600; line-height: 1.4; margin-bottom: 6px; word-break: break-word;">
                  <span style="color: #a44390; font-size: 14px; font-weight: 800; margin-right: 2px;">“</span><?php echo esc_html($rev->review); ?><span style="color: #a44390; font-size: 14px; font-weight: 800; margin-left: 2px;">”</span>
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
                  echo '<div class="admin-thread-timeline" style="border-left: 2px solid #cbd5e1; padding-left: 10px; margin-left: 4px; margin-top: 8px; display: flex; flex-direction: column; gap: 6px;">';
                  foreach ($thread_replies as $tr) :
                    $r_lvl = intval($tr['reply_level']);
                    if ($r_lvl === 1) {
                      $badge_bg = '#fdf5fc';
                      $badge_text = '#6d2e67';
                      $badge_border = '#f1e4ef';
                      $label_title = 'Provider Reply (L1)';
                    } elseif ($r_lvl === 2) {
                      $badge_bg = '#eff6ff';
                      $badge_text = '#1e40af';
                      $badge_border = '#dbeafe';
                      $label_title = 'Customer Follow-up (L2)';
                    } else {
                      $badge_bg = '#f0fdf4';
                      $badge_text = '#065f46';
                      $badge_border = '#dcfce7';
                      $label_title = 'Provider Closing (L3)';
                    }
                ?>
                    <div style="font-size: 11px; line-height: 1.4; word-break: break-word;">
                      <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 2px; flex-wrap: wrap;">
                        <span style="background: <?php echo $badge_bg; ?>; color: <?php echo $badge_text; ?>; border: 1px solid <?php echo $badge_border; ?>; font-size: 9px; font-weight: 700; padding: 1px 6px; border-radius: 10px; text-transform: uppercase; letter-spacing: 0.3px;"><?php echo esc_html($label_title); ?></span>
                        <strong style="color: #1e293b; font-size: 11px;"><?php echo esc_html($tr['sender_name']); ?>:</strong>
                      </div>
                      <div style="color: #475569; font-size: 11px; padding-left: 2px;"><?php echo esc_html($tr['reply_text']); ?></div>
                    </div>
                <?php
                  endforeach;
                  echo '</div>';
                endif;
                ?>
              </div>
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

  <!-- Pagination -->
  <div class="tablenav bottom">
    <div class="tablenav-pages">
      <span class="displaying-num"><?php echo count($reviews); ?> item(s)</span>
    </div>
  </div>
</div>
