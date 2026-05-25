<div class="container-fluid mt-4">
    <h4 class="mb-3">📂 Pending Media Approvals</h4>
    <?php wp_nonce_field('cosy_media_nonce', 'cosy_media_nonce_field'); ?>
    <div class="admin-succes"></div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Media</th>
                    <th>Provider</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Uploaded On</th>
                    <th>Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'cosy_media_approvals';
                
                // Fetch all approvals, prioritizing pending first, then by uploaded date
                $results = $wpdb->get_results(
                    "SELECT * FROM {$table_name} ORDER BY FIELD(status, 'pending', 'approved', 'rejected'), uploaded_at DESC"
                );

                if (empty($results)) {
                    echo '<tr><td colspan="7" class="text-center py-4 text-muted">No media approvals found.</td></tr>';
                }

                foreach ($results as $media):
                    $user_id = $media->user_id;
                    $data    = $this->get_provider_data($user_id);
                    $status  = $media->status;
                    ?>
                    <tr data-id="<?php echo esc_attr($user_id); ?>">
                        <!-- Media -->
                        <td style="max-width:220px;">
                            <?php if ($status !== 'rejected' && !empty($media->media_url)) { ?>
                                <video controls class="w-100" style="max-height:140px;">
                                    <source src="<?php echo esc_url($media->media_url); ?>" type="video/mp4">
                                </video>
                            <?php } else { ?>
                                <span class="text-muted">Deleted</span>
                            <?php } ?>
                        </td>

                        <!-- Provider Name -->
                        <td>
                            <?php echo esc_html(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')); ?>
                        </td>

                        <!-- Email -->
                        <td><?php echo esc_html($data['user_email'] ?? $data['email'] ?? ''); ?></td>

                        <!-- Phone -->
                        <td><?php echo esc_html($data['prov_phone'] ?? ''); ?></td>

                        <!-- Uploaded On -->
                        <td>
                            <?php echo !empty($media->uploaded_at)
                                ? esc_html($media->uploaded_at)
                                : '<span class="text-muted">N/A</span>'; ?>
                        </td>

                        <!-- Status -->
                        <td>
                            <?php
                            if ($status === 'approved') {
                                echo '<span class="badge bg-success status-badge">Approved</span>';
                            } elseif ($status === 'rejected') {
                                echo '<span class="badge bg-danger status-badge">Rejected</span>';
                            } else {
                                echo '<span class="badge bg-warning text-dark status-badge">Pending</span>';
                            }
                            ?>
                        </td>

                        <!-- Actions -->
                        <td class="text-center">
                            <?php if ($status === 'pending') { ?>
                                <button class="btn btn-success btn-sm approve-media"
                                    data-id="<?php echo esc_attr($user_id); ?>">
                                    Approve
                                </button>
                                <button class="btn btn-outline-danger btn-sm reject-media"
                                    data-id="<?php echo esc_attr($user_id); ?>">
                                    Reject
                                </button>
                            <?php } elseif ($status === 'approved') { ?>
                                <button class="btn btn-outline-danger btn-sm reject-media"
                                    data-id="<?php echo esc_attr($user_id); ?>">
                                    Reject
                                </button>
                            <?php } else { ?>
                                <span class="text-muted">No Action</span>
                            <?php } ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>