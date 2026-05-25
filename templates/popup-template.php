<div id="registerPopup">
    <div class="popup-header">
        <span class="cc__choose"><?php esc_html_e('Choose Member Type', 'cosy-appointments'); ?></span>
        <span id="closePopup" class="popup-close">✖</span>
    </div>
    <div class="popup-body">
        <a href="<?php echo site_url('/user-registration/'); ?>" id="customerBtn" class="button"><?php esc_html_e('CUSTOMER', 'cosy-appointments'); ?></a>
        <a href="<?php echo site_url('/provider-registration/'); ?>" id="providerBtn" class="button"><?php esc_html_e('SERVICE PROVIDER', 'cosy-appointments'); ?></a>
        <!-- <div id="formContainer" style="margin-top:20px;"></div> -->
    </div>
</div>