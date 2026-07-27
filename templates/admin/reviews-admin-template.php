<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap cosy-orders cosy-users-admin">
  <h1 class="wp-heading-inline"><?php esc_html_e('Reviews', 'cosy-appointments'); ?></h1>
  <hr class="wp-header-end">

  <div class="admin-succes" style="margin-top: 15px;"></div>

  <!-- Premium Control Bar (Identical to Orders Page) -->
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
              <div style="color: #f59e0b; font-weight: 700;">
                <?php echo str_repeat('★', intval($rev->rating)); ?><?php echo str_repeat('☆', 5 - intval($rev->rating)); ?>
              </div>
            </td>
            <td style="color:#475569; font-size:12px;">
              "<?php echo esc_html($rev->review); ?>"
              <?php if (!empty($rev->provider_reply)) : ?>
                <br><span style="color:#a44390; font-weight:600; font-size:11px;">Reply: <?php echo esc_html($rev->provider_reply); ?></span>
              <?php endif; ?>
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
