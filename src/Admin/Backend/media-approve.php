<div class="wrap cosy-orders cosy-users-admin cosy-media-approve-page">
    <h1 class="wp-heading-inline"><?php esc_html_e('Media Approvals', 'cosy-appointments'); ?></h1>
    <hr class="wp-header-end">

    <!-- Media Upload Configuration Card -->
    <div class="cosy-media-config-card" style="background: #ffffff; border-radius: 12px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 25px; border: 1px solid #e2e8f0;">
        <form method="post" action="options.php" style="margin: 0;">
            <?php settings_fields('cosy_media_settings'); ?>
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 15px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span class="dashicons dashicons-video-alt3" style="font-size: 24px; width: 24px; height: 24px; color: #a44390; display: flex; align-items: center; justify-content: center;"></span>
                    <div>
                        <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #1e293b; line-height: 1.2;"><?php esc_html_e('Video Upload Configuration', 'cosy-appointments'); ?></h3>
                        <p style="margin: 3px 0 0 0; font-size: 12px; color: #64748b; line-height: 1.2;"><?php esc_html_e('Set the maximum file size limit allowed for provider intro videos.', 'cosy-appointments'); ?></p>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <label for="cosy_max_video_upload_size" style="font-weight: 600; color: #475569; font-size: 13px; margin: 0;"><?php esc_html_e('Max Size (MB):', 'cosy-appointments'); ?></label>
                    <input type="number" min="1" step="1" name="cosy_max_video_upload_size" id="cosy_max_video_upload_size" value="<?php echo esc_attr(get_option('cosy_max_video_upload_size', '3')); ?>" style="width: 80px; padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-weight: 600; text-align: center; height: 35px; box-shadow: none; outline: none; margin: 0;">
                    <button type="submit" class="button button-primary" style="background: #a44390; border-color: #a44390; font-weight: 600; border-radius: 6px; padding: 0 15px; height: 35px; line-height: 33px; box-shadow: 0 2px 4px rgba(164, 67, 144, 0.2);"><?php esc_html_e('Save Limit', 'cosy-appointments'); ?></button>
                </div>
            </div>
        </form>
    </div>
    
    <?php wp_nonce_field('cosy_media_nonce', 'cosy_media_nonce_field'); ?>
    <div class="admin-succes" style="margin-top: 15px;"></div>

    <!-- Premium Control Bar for Media Approvals -->
    <div class="cosy-control-bar" style="margin-top: 15px; margin-bottom: 15px;">
        <div class="cosy-control-left">
            <button type="button" class="cosy-btn-delete-selected-modern" id="cosy-media-btn-delete-selected" disabled>
                <span class="dashicons dashicons-trash" style="font-size: 16px; width: 16px; height: 16px; margin-right: 6px; display: inline-block; vertical-align: middle;"></span>
                <span class="cosy-btn-text" style="vertical-align: middle;"><?php esc_html_e('Delete', 'cosy-appointments'); ?></span>
            </button>
        </div>
    </div>
    
    <div class="table-responsive" style="margin-top: 15px;">
        <table class="wp-list-table widefat fixed striped table-view-list cosy-orders-table cosy-media-table">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-cb check-column">
                        <input type="checkbox" id="cosy-select-all-media">
                    </th>
                    <th scope="col" style="width: 240px;">Media</th>
                    <th scope="col">Provider</th>
                    <th scope="col">Email</th>
                    <th scope="col" style="width: 150px;">Phone</th>
                    <th scope="col" style="width: 180px;">Uploaded On</th>
                    <th scope="col" style="width: 120px;">Status</th>
                    <th scope="col" class="text-center" style="width: 180px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'cosy_media_approvals';
                
                // Fetch only the latest media approval/upload status for each provider
                $results = $wpdb->get_results(
                    "SELECT m1.* FROM {$table_name} m1
                     INNER JOIN (
                         SELECT user_id, MAX(id) as max_id
                         FROM {$table_name}
                         GROUP BY user_id
                     ) m2 ON m1.id = m2.max_id
                     ORDER BY FIELD(m1.status, 'pending', 'approved', 'rejected'), m1.uploaded_at DESC"
                 );

                if (empty($results)) {
                    echo '<tr><td colspan="8" class="text-center py-4 text-muted" style="text-align: center; padding: 40px; color: #64748b;">No media approvals found.</td></tr>';
                }

                foreach ($results as $media):
                    $user_id = $media->user_id;
                    $data    = $this->get_provider_data($user_id);
                    $status  = $media->status;
                    ?>
                    <tr id="media-row-<?php echo esc_attr($media->id); ?>" data-id="<?php echo esc_attr($user_id); ?>">
                        <!-- Checkbox -->
                        <th scope="row" class="check-column">
                            <input type="checkbox" class="cosy-media-checkbox" value="<?php echo esc_attr($media->id); ?>" data-user-id="<?php echo esc_attr($user_id); ?>">
                        </th>
                        <!-- Media -->
                        <td>
                            <?php if ($status !== 'rejected' && $status !== 'deleted' && !empty($media->media_url)) { ?>
                                <video controls class="w-100" style="max-height:130px; display: block; border-radius: 8px;">
                                    <source src="<?php echo esc_url($media->media_url); ?>" type="video/mp4">
                                </video>
                            <?php } else { ?>
                                <span class="text-muted">Deleted</span>
                            <?php } ?>
                        </td>

                        <!-- Provider Name -->
                        <td>
                            <strong><?php echo esc_html(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')); ?></strong>
                        </td>

                        <!-- Email -->
                        <td><?php echo esc_html($data['user_email'] ?? $data['email'] ?? ''); ?></td>

                        <!-- Phone -->
                        <td><?php echo esc_html($data['prov_phone'] ?? ''); ?></td>

                        <!-- Uploaded On -->
                        <td style="color:#475569; font-size:12px;">
                            <?php echo !empty($media->uploaded_at)
                                ? esc_html($media->uploaded_at)
                                : '<span class="text-muted">N/A</span>'; ?>
                        </td>

                        <!-- Status -->
                        <td>
                            <?php
                            if ($status === 'approved') {
                                echo '<span class="cosy-badge cosy-badge-approved">Approved</span>';
                            } elseif ($status === 'rejected') {
                                echo '<span class="cosy-badge cosy-badge-rejected">Rejected</span>';
                            } elseif ($status === 'deleted') {
                                echo '<span class="cosy-badge cosy-badge-deleted">Deleted</span>';
                            } else {
                                echo '<span class="cosy-badge cosy-badge-pending">Pending</span>';
                            }
                            ?>
                        </td>

                        <!-- Actions -->
                        <td class="text-center">
                            <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                                <?php if ($status === 'pending') { ?>
                                    <button class="cosy-btn-approve approve-media"
                                        data-id="<?php echo esc_attr($user_id); ?>">
                                        Approve
                                    </button>
                                    <button class="cosy-btn-reject reject-media"
                                        data-id="<?php echo esc_attr($user_id); ?>">
                                        Reject
                                    </button>
                                <?php } elseif ($status === 'approved') { ?>
                                    <button class="cosy-btn-reject reject-media"
                                        data-id="<?php echo esc_attr($user_id); ?>">
                                        Reject
                                    </button>
                                <?php } else { ?>
                                    <span class="text-muted">No Action</span>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>