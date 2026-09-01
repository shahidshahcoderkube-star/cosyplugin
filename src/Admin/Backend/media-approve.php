<div class="wrap cosy-orders cosy-users-admin cosy-media-approve-page">
    <h1 class="wp-heading-inline"><?php esc_html_e('Media Approvals', 'cosy-appointments'); ?></h1>
    <hr class="wp-header-end">

    <!-- Media Upload Configuration Card -->
    <div class="cosy-media-config-card">
        <form method="post" action="options.php">
            <?php settings_fields('cosy_media_settings'); ?>
            <div class="cosy-media-config-inner">
                <div class="cosy-media-config-left">
                    <span class="dashicons dashicons-video-alt3 cosy-media-icon"></span>
                    <div>
                        <h3 class="cosy-media-config-title"><?php esc_html_e('Video Upload Configuration', 'cosy-appointments'); ?></h3>
                        <p class="cosy-media-config-subtext"><?php esc_html_e('Set the maximum file size limit allowed for provider intro videos.', 'cosy-appointments'); ?></p>
                    </div>
                </div>
                <div class="cosy-media-config-right">
                    <label for="cosy_max_video_upload_size" class="cosy-media-config-label"><?php esc_html_e('Max Size (MB):', 'cosy-appointments'); ?></label>
                    <input type="number" min="1" step="1" name="cosy_max_video_upload_size" id="cosy_max_video_upload_size" value="<?php echo esc_attr(get_option('cosy_max_video_upload_size', '3')); ?>" class="cosy-media-size-input">
                    <button type="submit" class="button button-primary cosy-media-save-btn"><?php esc_html_e('Save Limit', 'cosy-appointments'); ?></button>
                </div>
            </div>
        </form>
    </div>
    
    <?php wp_nonce_field('cosy_media_nonce', 'cosy_media_nonce_field'); ?>
    <div class="admin-succes cosy-media-table-container"></div>

    <!-- Premium Control Bar for Media Approvals -->
    <div class="cosy-control-bar">
        <div class="cosy-control-left">
            <button type="button" class="cosy-btn-delete-selected-modern" id="cosy-media-btn-delete-selected" disabled>
                <span class="dashicons dashicons-trash cosy-btn-icon-trash"></span>
                <span class="cosy-btn-text cosy-btn-text-vmiddle"><?php esc_html_e('Delete', 'cosy-appointments'); ?></span>
            </button>
        </div>
    </div>
    
    <div class="table-responsive cosy-media-table-container">
        <table class="wp-list-table widefat fixed striped table-view-list cosy-orders-table cosy-media-table">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-cb check-column">
                        <input type="checkbox" id="cosy-select-all-media">
                    </th>
                    <th scope="col" class="cosy-col-media"><?php esc_html_e('Media', 'cosy-appointments'); ?></th>
                    <th scope="col"><?php esc_html_e('Provider', 'cosy-appointments'); ?></th>
                    <th scope="col"><?php esc_html_e('Email', 'cosy-appointments'); ?></th>
                    <th scope="col" class="cosy-col-phone"><?php esc_html_e('Phone', 'cosy-appointments'); ?></th>
                    <th scope="col" class="cosy-col-uploaded-on"><?php esc_html_e('Uploaded On', 'cosy-appointments'); ?></th>
                    <th scope="col" class="cosy-col-status"><?php esc_html_e('Status', 'cosy-appointments'); ?></th>
                    <th scope="col" class="text-center cosy-col-actions"><?php esc_html_e('Action', 'cosy-appointments'); ?></th>
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
                    echo '<tr><td colspan="8" class="text-center cosy-table-empty-cell">' . esc_html__('No media approvals found.', 'cosy-appointments') . '</td></tr>';
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
                                <video controls class="w-100 cosy-media-video-preview">
                                    <source src="<?php echo esc_url($media->media_url); ?>" type="video/mp4">
                                </video>
                            <?php } else { ?>
                                <span class="cosy-muted-empty-text"><?php esc_html_e('Deleted', 'cosy-appointments'); ?></span>
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
                        <td class="cosy-order-datetime-cell">
                            <?php echo !empty($media->uploaded_at)
                                ? esc_html($media->uploaded_at)
                                : '<span class="cosy-muted-empty-text">' . esc_html__('N/A', 'cosy-appointments') . '</span>'; ?>
                        </td>

                        <!-- Status -->
                        <td>
                            <?php
                            if ($status === 'approved') {
                                echo '<span class="cosy-badge cosy-badge-approved">' . esc_html__('Approved', 'cosy-appointments') . '</span>';
                            } elseif ($status === 'rejected') {
                                echo '<span class="cosy-badge cosy-badge-rejected">' . esc_html__('Rejected', 'cosy-appointments') . '</span>';
                            } elseif ($status === 'deleted') {
                                echo '<span class="cosy-badge cosy-badge-deleted">' . esc_html__('Deleted', 'cosy-appointments') . '</span>';
                            } else {
                                echo '<span class="cosy-badge cosy-badge-pending">' . esc_html__('Pending', 'cosy-appointments') . '</span>';
                            }
                            ?>
                        </td>

                        <!-- Actions -->
                        <td class="text-center">
                            <div class="cosy-media-actions-wrapper">
                                <?php if ($status === 'pending') { ?>
                                    <button class="cosy-btn-approve approve-media"
                                        data-id="<?php echo esc_attr($user_id); ?>">
                                        <?php esc_html_e('Approve', 'cosy-appointments'); ?>
                                    </button>
                                    <button class="cosy-btn-reject reject-media"
                                        data-id="<?php echo esc_attr($user_id); ?>">
                                        <?php esc_html_e('Reject', 'cosy-appointments'); ?>
                                    </button>
                                <?php } elseif ($status === 'approved') { ?>
                                    <button class="cosy-btn-reject reject-media"
                                        data-id="<?php echo esc_attr($user_id); ?>">
                                        <?php esc_html_e('Reject', 'cosy-appointments'); ?>
                                    </button>
                                <?php } else { ?>
                                    <span class="cosy-muted-empty-text"><?php esc_html_e('No Action', 'cosy-appointments'); ?></span>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>