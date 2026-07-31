<div id="registerPopup">
    <div class="popup-header">
        <span class="cc__choose"><?php esc_html_e('Choose Member Type', 'cosy-appointments'); ?></span>
        <span id="closePopup" class="popup-close">✖</span>
    </div>
    <div class="popup-body">
        <a href="<?php echo esc_url(cosy_get_page_url('user-registration')); ?>" id="customerBtn" class="button"><?php esc_html_e('Book Conversations', 'cosy-appointments'); ?></a>
        <a href="<?php echo esc_url(cosy_get_page_url('provider-registration')); ?>" id="providerBtn" class="button"><?php esc_html_e('Accept Bookings', 'cosy-appointments'); ?></a>
        <!-- <div id="formContainer" style="margin-top:20px;"></div> -->
    </div>
</div>