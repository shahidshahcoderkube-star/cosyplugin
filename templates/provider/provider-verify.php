<div class="container mt-5">
    <?php if (is_user_logged_in()) : ?>
        <div class="alert alert-success text-center" role="alert">
            <h4 class="alert-heading"><?php esc_html_e('🎉 Account Verified!', 'cosy-appointments'); ?></h4>
            <p><?php esc_html_e('Your provider account has been successfully verified and you are now logged in.', 'cosy-appointments'); ?></p>
            <hr>
            <a href="<?php echo esc_url(home_url('/provider-dashboard/')); ?>" class="btn btn-success mt-3"><?php esc_html_e('Go to Dashboard', 'cosy-appointments'); ?></a>
        </div>
    <?php else : ?>
        <div class="alert alert-danger text-center" role="alert">
            <h4 class="alert-heading"><?php esc_html_e('⚠️ Access Denied', 'cosy-appointments'); ?></h4>
            <p><?php esc_html_e('You must verify your account and be logged in to access the dashboard.', 'cosy-appointments'); ?></p>
            <hr>
            <a href="<?php echo esc_url(home_url('/login/')); ?>" class="btn btn-danger mt-3"><?php esc_html_e('Login', 'cosy-appointments'); ?></a>
        </div>
    <?php endif; ?>
</div>