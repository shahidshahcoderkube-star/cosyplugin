<?php $providers = $this->get_all_service_providers(); ?>
<div class="cosy-premium-grid-container mb-5 mt-5">

    <!-- Filters Bar -->
    <div class="cosy-providers-filter-bar mb-4 p-3" style="background: #ffffff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <form id="cosyProvidersFilterForm" class="d-flex gap-3 align-items-center justify-content-center">

            <input type="text" name="search_name" id="filter_search_name" class="form-control cosy-filter-input" placeholder="<?php esc_attr_e('Search by name', 'cosy-appointments'); ?>" style="min-width: 200px; border-radius: 8px;">

            <select name="service_category" id="filter_category" class="form-select cosy-filter-select" style="min-width: 150px; background-color: #f1f5f9; border-radius: 8px; border: 1px solid #cbd5e1;">
                <option value=""><?php esc_html_e('--Category--', 'cosy-appointments'); ?></option>
                <?php
                $services = get_posts(['post_type' => 'cosy_service', 'numberposts' => -1]);
                foreach ($services as $srv) {
                    echo '<option value="' . esc_attr($srv->post_name) . '">' . esc_html($srv->post_title) . '</option>';
                }
                ?>
            </select>

            <select name="price_range" id="filter_price" class="form-select cosy-filter-select" style="min-width: 130px; background-color: #e2e8f0; border-radius: 8px; border: 1px solid #cbd5e1; color: #475569;">
                <option value=""><?php esc_html_e('--Price--', 'cosy-appointments'); ?></option>
                <option value="low_high"><?php esc_html_e('Low to High', 'cosy-appointments'); ?></option>
                <option value="high_low"><?php esc_html_e('High to Low', 'cosy-appointments'); ?></option>
            </select>

            <select name="gender" id="filter_gender" class="form-select cosy-filter-select" style="min-width: 130px; background-color: #e2e8f0; border-radius: 8px; border: 1px solid #cbd5e1; color: #475569;">
                <option value=""><?php esc_html_e('--Gender--', 'cosy-appointments'); ?></option>
                <option value="male"><?php esc_html_e('Male', 'cosy-appointments'); ?></option>
                <option value="female"><?php esc_html_e('Female', 'cosy-appointments'); ?></option>
            </select>

            <select name="age_group" id="filter_age" class="form-select cosy-filter-select" style="min-width: 130px; background-color: #e2e8f0; border-radius: 8px; border: 1px solid #cbd5e1; color: #475569;">
                <option value=""><?php esc_html_e('--Age--', 'cosy-appointments'); ?></option>
                <option value="Teenager"><?php esc_html_e('Teenager', 'cosy-appointments'); ?></option>
                <option value="Young Adult"><?php esc_html_e('Young Adult', 'cosy-appointments'); ?></option>
                <option value="Middle Aged"><?php esc_html_e('Middle Aged', 'cosy-appointments'); ?></option>
                <option value="Senior"><?php esc_html_e('Senior', 'cosy-appointments'); ?></option>
                <option value="Golden Senior"><?php esc_html_e('Golden Senior', 'cosy-appointments'); ?></option>
            </select>

            <select name="rating" id="filter_rating" class="form-select cosy-filter-select" style="min-width: 130px; background-color: #e2e8f0; border-radius: 8px; border: 1px solid #cbd5e1; color: #475569;">
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