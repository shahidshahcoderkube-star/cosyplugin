<?php $providers = $this->get_all_service_providers(); ?>
<div class="cosy-premium-grid-container mb-5 mt-5">

    <!-- Filters Bar -->
    <div class="cosy-providers-filter-bar mb-4 p-3">
        <form id="cosyProvidersFilterForm" class="d-flex gap-3 align-items-center justify-content-center w-100">

            <input type="text" name="search_name" id="filter_search_name" class="form-control cosy-filter-input" placeholder="<?php esc_attr_e('Search by name', 'cosy-appointments'); ?>" style="min-width: 220px; height: 46px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 14px; color: #334155; font-weight: 500; padding: 10px 16px; box-shadow: none;">

            <select name="service_category" id="filter_category" class="form-select cosy-filter-select" style="min-width: 160px; height: 46px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 14px; color: #334155; font-weight: 500; padding: 10px 36px 10px 16px; box-shadow: none;">
                <option value=""><?php esc_html_e('--Category--', 'cosy-appointments'); ?></option>
                <?php
                $services = get_posts(['post_type' => 'cosy_service', 'numberposts' => -1]);
                foreach ($services as $srv) {
                    echo '<option value="' . esc_attr($srv->post_name) . '">' . esc_html($srv->post_title) . '</option>';
                }
                ?>
            </select>

            <select name="price_range" id="filter_price" class="form-select cosy-filter-select" style="min-width: 140px; height: 46px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 14px; color: #334155; font-weight: 500; padding: 10px 36px 10px 16px; box-shadow: none;">
                <option value=""><?php esc_html_e('--Price--', 'cosy-appointments'); ?></option>
                <option value="low_high"><?php esc_html_e('Low to High', 'cosy-appointments'); ?></option>
                <option value="high_low"><?php esc_html_e('High to Low', 'cosy-appointments'); ?></option>
            </select>

            <select name="gender" id="filter_gender" class="form-select cosy-filter-select" style="min-width: 140px; height: 46px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 14px; color: #334155; font-weight: 500; padding: 10px 36px 10px 16px; box-shadow: none;">
                <option value=""><?php esc_html_e('--Gender--', 'cosy-appointments'); ?></option>
                <option value="male"><?php esc_html_e('Male', 'cosy-appointments'); ?></option>
                <option value="female"><?php esc_html_e('Female', 'cosy-appointments'); ?></option>
            </select>

            <select name="age_group" id="filter_age" class="form-select cosy-filter-select" style="min-width: 140px; height: 46px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 14px; color: #334155; font-weight: 500; padding: 10px 36px 10px 16px; box-shadow: none;">
                <option value=""><?php esc_html_e('--Age--', 'cosy-appointments'); ?></option>
                <option value="Teenager"><?php esc_html_e('Teenager', 'cosy-appointments'); ?></option>
                <option value="Young Adult"><?php esc_html_e('Young Adult', 'cosy-appointments'); ?></option>
                <option value="Middle Aged"><?php esc_html_e('Middle Aged', 'cosy-appointments'); ?></option>
                <option value="Senior"><?php esc_html_e('Senior', 'cosy-appointments'); ?></option>
                <option value="Golden Senior"><?php esc_html_e('Golden Senior', 'cosy-appointments'); ?></option>
            </select>

            <select name="rating" id="filter_rating" class="form-select cosy-filter-select" style="min-width: 140px; height: 46px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 14px; color: #334155; font-weight: 500; padding: 10px 36px 10px 16px; box-shadow: none;">
                <option value=""><?php esc_html_e('--Rating--', 'cosy-appointments'); ?></option>
                <option value="5">5 Stars</option>
                <option value="4">4+ Stars</option>
                <option value="3">3+ Stars</option>
            </select>

            <input type="hidden" name="action" value="filter_service_providers">
        </form>
    </div>

    <div id="cosyProvidersGridWrap">
        <?php include COSY_APPT_PATH . 'templates/service-provider-grid-template.php'; ?>
    </div> <!-- /#cosyProvidersGridWrap -->
</div>

<div id="videoModal" class="modal" onclick="closeVideo()">
    <div class="cosy-video-modal-content-v2" onclick="event.stopPropagation()">
        <span class="close-modal" onclick="closeVideo()">&times;</span>
        <iframe id="videoFrame" width="100%" height="100%" src="" frameborder="0" allowfullscreen style="display:none; border:none; width:100%; height:100%;"></iframe>
        <video id="videoPlayer" controls width="100%" height="100%" src="" style="display:none; width:100%; height:100%; object-fit:contain; border-radius:20px; outline:none; background:#000;"></video>
    </div>
</div>

<script>
    function openVideo(url) {
        var videoFrame = document.getElementById('videoFrame');
        var videoPlayer = document.getElementById('videoPlayer');
        
        // Detect direct video files (mp4, webm, ogg, mov, etc.)
        var isDirectVideo = url.match(/\.(mp4|webm|ogg|mov|3gp)($|\?)/i) || url.includes('/wp-content/uploads/');
        
        if (isDirectVideo) {
            videoFrame.style.display = 'none';
            videoFrame.src = '';
            
            videoPlayer.src = url;
            videoPlayer.style.display = 'block';
            videoPlayer.play().catch(function(e) {
                console.log("Autoplay was prevented:", e);
            });
        } else {
            videoPlayer.style.display = 'none';
            videoPlayer.pause();
            videoPlayer.src = '';
            
            videoFrame.src = url;
            videoFrame.style.display = 'block';
        }
        
        document.getElementById('videoModal').style.display = 'flex';
    }

    function closeVideo() {
        document.getElementById('videoModal').style.display = 'none';
        
        var videoFrame = document.getElementById('videoFrame');
        var videoPlayer = document.getElementById('videoPlayer');
        
        videoFrame.src = '';
        videoPlayer.pause();
        videoPlayer.src = '';
    }
</script>