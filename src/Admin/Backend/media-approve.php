<div class="container-fluid mt-4">
    <h4 class="mb-3">📂 Pending Media Approvals</h4>
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
                //----------- Fetch Providers with Media ----------------//
                $args = [
                    'role'    => 'provider',
                    'orderby' => 'registered',
                    'order'   => 'DESC',
                ];
                $providers = get_users($args);

                foreach ($providers as $provider):
                    $user_id = $provider->ID;
                    $data    = $this->get_provider_data($user_id);

                    // Skip if no video uploaded
                    if (empty($data['introduction_video'])) {
                        continue;
                    }
                ?>
                    <tr data-id="<?php echo esc_attr($user_id); ?>">
                        <!-- Media -->
                        <td style="max-width:220px;">
                            <video controls class="w-100" style="max-height:140px;">
                                <source src="<?php echo esc_url($data['introduction_video']); ?>" type="video/mp4">
                            </video>
                        </td>

                        <!-- Provider Name -->
                        <td>
                            <?php echo esc_html($data['prov_fname'] . ' ' . $data['prov_sname']); ?>
                        </td>

                        <!-- Email -->
                        <td><?php echo esc_html($data['prov_email']); ?></td>

                        <!-- Phone -->
                        <td><?php echo esc_html($data['prov_phone']); ?></td>

                        <!-- Uploaded On -->
                        <td>
                            <?php echo !empty($data['video_uploaded_on'])
                                ? esc_html($data['video_uploaded_on'])
                                : '<span class="text-muted">N/A</span>'; ?>
                        </td>

                        <!-- Status -->
                        <td>
                            <?php
                            $status = $data['video_status'];
                            if ($status === 'approved') {
                                echo '<span class="badge bg-success">Approved</span>';
                            } elseif ($status === 'rejected') {
                                echo '<span class="badge bg-danger">Rejected</span>';
                            } else {
                                echo '<span class="badge bg-warning text-dark">Pending</span>';
                            }
                            ?>
                        </td>

                        <!-- Actions -->
                        <td class="text-center">
                            <?php if ($status !== 'approved') { ?>
                                <button class="btn btn-success btn-sm approve-media" data-id="<?php echo esc_attr($user_id); ?>">
                                    Approve
                                </button>
                            <?php } ?>
                            <button class="btn btn-outline-danger btn-sm reject-media" data-id="<?php echo esc_attr($user_id); ?>">
                                Reject
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>