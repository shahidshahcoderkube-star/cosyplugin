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
                            <div class="rating-box-premium">
                                <div class="stars-flex">
                                    <?php
                                    $rating = $provider['rating'] > 0 ? $provider['rating'] : 5;
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
                            </div>
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
                    <a href="<?php echo esc_url(get_author_posts_url($provider['ID'])); ?>" class="btn-premium btn-profile-v2">
                        <?php esc_html_e('View Profile', 'cosy-appointments'); ?> <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>