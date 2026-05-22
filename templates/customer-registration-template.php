<div class="cosy-customer-registration">
    <div class="wrapper">
        <!-- Left Column: Form -->
        <div class="cosy-reg-form">
            <h2><?php esc_html_e('Create Account', 'cosy-appointments'); ?></h2>
            <p class="subtitle"><?php esc_html_e('Join us today! Please fill in your details.', 'cosy-appointments'); ?></p>

            <form id="customerForm" class="cosy-form" method="post" data-action="cosy_customer_register">
                <div class="cosy-message"></div>
                <?php wp_nonce_field('cosy_customer_register_nonce', 'cosy_nonce'); ?>
                <input type="hidden" name="action" value="cosy_customer_register">

                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="cust_name"><?php esc_html_e('Full Name', 'cosy-appointments'); ?></label>
                    <input type="text" name="cust_name" id="cust_name" placeholder="<?php esc_attr_e('John Doe', 'cosy-appointments'); ?>" required>
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="cust_email"><?php esc_html_e('Email Address', 'cosy-appointments'); ?></label>
                    <input type="email" name="cust_email" id="cust_email" placeholder="<?php esc_attr_e('john@example.com', 'cosy-appointments'); ?>" required autocomplete="off">
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="cust_pass"><?php esc_html_e('Password', 'cosy-appointments'); ?></label>
                    <input type="password" name="cust_pass" id="cust_pass" placeholder="<?php esc_attr_e('••••••••', 'cosy-appointments'); ?>" required>
                </div>

                <!-- Terms and Conditions Checkbox -->
                <p class="terms-container">
                    <input type="checkbox" name="terms" id="terms" required>
                    <label for="terms">
                        <?php echo wp_kses(sprintf(__('I have read and agree to the <a href="%s" target="_blank">Terms and Conditions.</a>', 'cosy-appointments'), esc_url('https://cosychats.com/terms-and-conditions')), array('a' => array('href' => array(), 'target' => array()))); ?>
                    </label>
                </p>

                <div class="cosy-btn-group">
                    <button type="submit" name="cosy_customer_register" class="button button-primary"><?php esc_html_e('Create Account', 'cosy-appointments'); ?></button>

                    <p class="footer-text">
                        <?php esc_html_e('Already have an account?', 'cosy-appointments'); ?> <a href="<?php echo esc_url(home_url('login')); ?>"><?php esc_html_e('Sign In', 'cosy-appointments'); ?></a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Right Column: Image -->
        <div class="cosy-reg-message">
            <img src="<?php echo plugin_dir_url(__DIR__); ?>src/Assets/images/cosy.jpg" alt="">
        </div>
    </div>
</div>