<?php if (empty($providers)): ?>
    <div class="no-providers-found text-center py-5 w-100"
        style="background: #fdfdfd; border: 1px dashed #d1d5db; border-radius: 12px;">
        <i class="fas fa-search fa-3x mb-3" style="color: #9ca3af;"></i>
        <h3 style="color: #4b5563; font-weight: 600;">
            <?php esc_html_e('No Providers Found', 'cosy-appointments'); ?>
        </h3>
        <p style="color: #6b7280;">
            <?php esc_html_e('Currently, there are no service providers available for this selection.', 'cosy-appointments'); ?>
        </p>
    </div>
<?php else: ?>
    <div class="cosy-premium-grid">
        <?php foreach ($providers as $provider): ?>
            <div class="cosy-card-v2">
                <div class="card-top-header">
                    <div class="header-inner-flex">
                        <div class="profile-avatar-wrapper">
                            <?php if (!empty($provider['profile_image'])): ?>
                                <img src="<?php echo esc_url($provider['profile_image']); ?>"
                                    alt="<?php echo esc_attr($provider['first_name']); ?>" class="avatar">
                            <?php else: ?>
                                <img src="<?php echo COSY_APPT_URL . 'images/profile.avif'; ?>"
                                    alt="<?php echo esc_attr($provider['first_name']); ?>" class="avatar">
                            <?php endif; ?>
                        </div>
                        <div class="profile-details-top">
                            <h3 class="provider-name">
                                <?php echo esc_html($provider['first_name']); ?>
                                <i class="fas fa-check-circle verified-tick"></i>
                            </h3>
                            <?php if (!empty($provider['rating']) && $provider['rating'] > 0): ?>
                                <div class="rating-box-premium">
                                    <div class="stars-flex">
                                        <?php
                                        $rating = floatval($provider['rating']);
                                        $full_stars = floor($rating);
                                        $half_star = ($rating - $full_stars >= 0.5);

                                        for ($i = 0; $i < $full_stars; $i++) {
                                            echo '<i class="fas fa-star"></i>';
                                        }
                                        if ($half_star) {
                                            echo '<i class="fas fa-star-half-alt"></i>';
                                        }
                                        ?>
                                    </div>
                                    <span class="rating-val"><?php echo number_format($rating, 1); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card-main-content">
                    <p class="description-text">
                        <?php echo esc_html(wp_trim_words($provider['description'], 25)); ?>
                    </p>

                    <?php if (!empty($provider['price'])): ?>
                        <div class="pricing-premium">
                            <span class="currency"><?php echo esc_html(cosy_get_currency_symbol()); ?></span>
                            <span class="amount"><?php echo esc_html($provider['price']); ?></span><span class="per"><?php esc_html_e('/ hr', 'cosy-appointments'); ?></span>
                        </div>
                    <?php endif; ?>

                </div>

                <div class="card-action-footer">
                    <?php if (!empty($provider['introduction_video'])): ?>
                        <button class="btn-premium btn-intro-v2"
                            onclick="openVideo('<?php echo esc_url($provider['introduction_video']); ?>')">
                            <i class="fas fa-play-circle"></i> <?php esc_html_e('Intro', 'cosy-appointments'); ?>
                        </button>
                    <?php endif; ?>
                    <?php
                    // Retrieve provider profile base URL
                    $profile_url = get_author_posts_url($provider['ID']);
                    // Detect active service category selected in directory filters
                    $selected_cat = !empty($active_service_slug) ? $active_service_slug : (isset($_POST['service_category']) ? sanitize_text_field($_POST['service_category']) : (isset($_GET['service_category']) ? sanitize_text_field($_GET['service_category']) : (isset($_GET['service_name']) ? sanitize_text_field($_GET['service_name']) : '')));
                    // Append service_name query parameter if a specific category was filtered
                    if (!empty($selected_cat)) {
                        $profile_url = add_query_arg('service_name', $selected_cat, $profile_url);
                    }
                    ?>
                    <a href="<?php echo esc_url($profile_url); ?>" class="btn-premium btn-profile-v2">
                        <?php esc_html_e('View Profile', 'cosy-appointments'); ?> <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php
    $paged           = isset($paged) ? max(1, intval($paged)) : 1;
    $total_pages     = isset($total_pages) ? max(1, intval($total_pages)) : 1;
    $total_providers = isset($total_providers) ? intval($total_providers) : count($providers);
    $per_page        = 9;

    $start_num = min($total_providers, (($paged - 1) * $per_page) + 1);
    $end_num   = min($total_providers, $paged * $per_page);
    ?>

    <?php if ($total_pages > 1): ?>
        <div class="cosy-pagination-container mt-5 d-flex flex-column align-items-center gap-3">
            <div class="cosy-pagination-info text-muted small fw-medium">
                <?php printf(esc_html__('Showing %1$d–%2$d of %3$d Parent Guides', 'cosy-appointments'), $start_num, $end_num, $total_providers); ?>
            </div>
            
            <nav class="cosy-pagination-nav">
                <ul class="pagination pagination-rounded gap-2 m-0 align-items-center list-unstyled d-flex">
                    <!-- Prev Button -->
                    <li class="page-item <?php echo ($paged <= 1) ? 'disabled' : ''; ?>">
                        <button type="button" class="page-link cosy-page-link" data-page="<?php echo max(1, $paged - 1); ?>" <?php echo ($paged <= 1) ? 'disabled' : ''; ?>>
                            <i class="fas fa-chevron-left me-1"></i> <?php esc_html_e('Prev', 'cosy-appointments'); ?>
                        </button>
                    </li>

                    <!-- Page Numbers -->
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == 1 || $i == $total_pages || ($i >= $paged - 2 && $i <= $paged + 2)): ?>
                            <li class="page-item <?php echo ($i === $paged) ? 'active' : ''; ?>">
                                <button type="button" class="page-link cosy-page-link <?php echo ($i === $paged) ? 'active-page' : ''; ?>" data-page="<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </button>
                            </li>
                        <?php elseif ($i == 2 || $i == $total_pages - 1): ?>
                            <li class="page-item disabled"><span class="page-link border-0 text-muted">...</span></li>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <!-- Next Button -->
                    <li class="page-item <?php echo ($paged >= $total_pages) ? 'disabled' : ''; ?>">
                        <button type="button" class="page-link cosy-page-link" data-page="<?php echo min($total_pages, $paged + 1); ?>" <?php echo ($paged >= $total_pages) ? 'disabled' : ''; ?>>
                            <?php esc_html_e('Next', 'cosy-appointments'); ?> <i class="fas fa-chevron-right ms-1"></i>
                        </button>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
<?php endif; ?>