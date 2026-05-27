<div class="cosy-login cosy-customer-registration">
    <div class="wrapper">
        <!-- Left Column: Form -->
        <div class="cosy-reg-form">
            <h2><?php esc_html_e('Login', 'cosy-appointments'); ?></h2>
            <p class="subtitle"><?php esc_html_e('Welcome back! Please enter your details.', 'cosy-appointments'); ?></p>

            <form id="cosyLoginForm" class="cosy-form" method="post" data-action="cosy_login">
                <div class="cosy-message"></div>
                <?php wp_nonce_field('cosy_login_nonce', 'cosy_nonce'); ?>
                <input type="hidden" name="action" value="cosy_login">

                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="log"><?php esc_html_e('Username or Email', 'cosy-appointments'); ?></label>
                    <input type="text" name="log" id="log" placeholder="<?php esc_attr_e('Enter your username', 'cosy-appointments'); ?>" required>
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="pwd"><?php esc_html_e('Password', 'cosy-appointments'); ?></label>
                    <div class="cosy-password-wrapper">
                        <input type="password" name="pwd" id="pwd" placeholder="<?php esc_attr_e('••••••••', 'cosy-appointments'); ?>" required>
                        <span class="cosy-toggle-password">
                            <i class="fa-regular fa-eye"></i>
                        </span>
                    </div>
                </div>

                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Sign In', 'cosy-appointments'); ?></button>
                </p>
            </form>

            <div class="cosy-forgot">
                <a href="<?php echo wp_lostpassword_url(); ?>"><?php esc_html_e('Forgot Password?', 'cosy-appointments'); ?></a>
            </div>

            <p class="footer-text">
                <?php printf(wp_kses(__('Don\'t have an account? <a href="%s">Create one</a>', 'cosy-appointments'), ['a' => ['href' => []]]), esc_url(home_url('user-registration'))); ?>
            </p>
        </div>

        <!-- Right Column: Image -->
        <div class="cosy-reg-message">
            <img src="<?php echo plugin_dir_url(__DIR__); ?>src/Assets/images/log-in.jpg" alt="<?php esc_attr_e('Login Illustration', 'cosy-appointments'); ?>">
        </div>
    </div>
</div>