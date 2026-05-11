<?php

$providers = $this->get_all_service_providers();

if (empty($providers)) {
    echo '<p>No service providers found.</p>';
    return;
}

?>
<div class="cosy-premium-grid-container mb-5 mt-5">
    <div class="cosy-premium-grid">
        <?php foreach ($providers as $provider): 
            $profile_pic = !empty($provider['profile_image']) ? $provider['profile_image'] : 'https://i.pravatar.cc/300?u=' . $provider['ID'];
            $video_url   = !empty($provider['introduction_video']) ? $provider['introduction_video'] : 'https://www.youtube.com/embed/ScMzIvxBSi4';
            $hourly_rate = !empty($provider['hourly_rate']) ? $provider['hourly_rate'] : '45';
        ?>
            <div class="cosy-card-v2">
                <div class="card-top-header">
                    <div class="header-inner-flex">
                        <div class="profile-avatar-wrapper">
                            <img src="<?php echo esc_url($profile_pic); ?>" alt="<?php echo esc_attr($provider['first_name']); ?>" class="avatar">
                        </div>
                        <div class="profile-details-top">
                            <h3 class="provider-name">
                                <?php echo esc_html($provider['first_name'] . ' ' . $provider['last_name']); ?>
                                <i class="fas fa-check-circle verified-tick"></i>
                            </h3>
                            <div class="rating-box-premium">
                                <div class="stars-flex">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                </div>
                                <span class="rating-val">5.0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-main-content">
                    <p class="description-text">
                        <?php 
                        $description = !empty($provider['description']) ? $provider['description'] : 'Experience premium service with our expert professionals who are dedicated to providing the highest quality sessions tailored to your specific needs.';
                        echo esc_html(wp_trim_words($description, 25)); 
                        ?>
                    </p>
                    
                    <div class="pricing-premium">
                        <span class="currency">$</span><span class="amount"><?php echo esc_html($hourly_rate); ?></span><span class="per">/ hr</span>
                    </div>
                </div>

                <div class="card-action-footer">
                    <button class="btn-premium btn-intro-v2" onclick="openVideo('<?php echo esc_url($video_url); ?>')">
                        <i class="fas fa-play-circle"></i> Intro
                    </button>
                    <a href="<?php echo get_author_posts_url($provider['ID']); ?>" class="btn-premium btn-profile-v2">
                        View Profile <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
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