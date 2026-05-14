<?php
$providers = $this->get_all_service_providers();
?>
<div class="cosy-premium-grid-container mb-5 mt-5">
    <?php if (empty($providers)): ?>
        <div class="no-providers-found text-center py-5 w-100"
            style="background: #fdfdfd; border: 1px dashed #d1d5db; border-radius: 12px;">
            <i class="fas fa-search fa-3x mb-3" style="color: #9ca3af;"></i>
            <h3 style="color: #4b5563; font-weight: 600;">No Providers Found</h3>
            <p style="color: #6b7280;">Currently, there are no service providers available for this selection.</p>
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
                                    <img src="<?php echo plugin_dir_url(__FILE__); ?>../images/profile.avif"
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
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                            class="fas fa-star"></i><i class="fas fa-star"></i>
                                    </div>
                                    <span class="rating-val">5.0</span>
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
                                <span class="currency">£</span>
                                <span class="amount"><?php echo esc_html($provider['price']); ?></span><span class="per">/ hr</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="card-action-footer">
                        <?php if (!empty($provider['introduction_video'])): ?>
                            <button class="btn-premium btn-intro-v2"
                                onclick="openVideo('<?php echo esc_url($provider['introduction_video']); ?>')">
                                <i class="fas fa-play-circle"></i> Intro
                            </button>
                        <?php endif; ?>
                        <a href="<?php echo get_author_posts_url($provider['ID']); ?>" class="btn-premium btn-profile-v2">
                            View Profile <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div id="videoModal" class="modal" onclick="closeVideo()">
    <div class="modal-content" onclick="event.stopPropagation()">
        <span class="close-modal" onclick="closeVideo()">&times;</span>
        <iframe id="videoFrame" width="100%" height="100%" src="" frameborder="0" allowfullscreen></iframe>
    </div>
</div>

<script>
    function openVideo(url) {
        document.getElementById('videoFrame').src = url;
        document.getElementById('videoModal').style.display = 'flex';
    }

    function closeVideo() {
        document.getElementById('videoModal').style.display = 'none';
        document.getElementById('videoFrame').src = '';
    }
</script>

</body>

</html>