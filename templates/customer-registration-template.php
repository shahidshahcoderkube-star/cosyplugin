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

                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="cust_name"><?php esc_html_e('UserName', 'cosy-appointments'); ?></label>
                    <input type="text" name="cust_name" id="cust_name" placeholder="<?php esc_attr_e('username', 'cosy-appointments'); ?>" required>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="cust_email"><?php esc_html_e('Email Address', 'cosy-appointments'); ?></label>
                    <input type="email" name="cust_email" id="cust_email" placeholder="<?php esc_attr_e('john@example.com', 'cosy-appointments'); ?>" required autocomplete="off">
                </div>

                <div class="form-group" style="margin-bottom: 18px;">
                    <label for="cust_pass"><?php esc_html_e('Password', 'cosy-appointments'); ?></label>
                    <div class="cosy-password-wrapper">
                        <input type="password" name="cust_pass" id="cust_pass" placeholder="<?php esc_attr_e('••••••••', 'cosy-appointments'); ?>" required>
                        <span class="cosy-toggle-password">
                            <i class="fa-regular fa-eye"></i>
                        </span>
                    </div>
                </div>

                <!-- 5 Mandatory Registration Declarations Card -->
                <div class="cosy-declarations-box">
                    <div class="cosy-declarations-header">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span><?php esc_html_e('Platform Declarations', 'cosy-appointments'); ?></span>
                    </div>
                    <div class="cosy-declarations-list">
                        <div class="cosy-declaration-wrapper">
                            <label class="cosy-declaration-item">
                                <input type="checkbox" name="declaration_1" id="declaration_1" required>
                                <span class="cosy-declaration-text">I am 18 years of age or over.</span>
                            </label>
                        </div>
                        <div class="cosy-declaration-wrapper">
                            <label class="cosy-declaration-item">
                                <input type="checkbox" name="declaration_2" id="declaration_2" required>
                                <span class="cosy-declaration-text">I understand that CosyChats is an introduction platform for parent-to-parent conversations based on personal lived experiences.</span>
                            </label>
                        </div>
                        <div class="cosy-declaration-wrapper">
                            <label class="cosy-declaration-item">
                                <input type="checkbox" name="declaration_3" id="declaration_3" required>
                                <span class="cosy-declaration-text">I understand that CosyChats is not a crisis or emergency service and is not intended for vulnerable adults or situations requiring professional, medical, clinical or urgent support.</span>
                            </label>
                        </div>
                        <div class="cosy-declaration-wrapper">
                            <label class="cosy-declaration-item">
                                <input type="checkbox" name="declaration_4" id="declaration_4" required>
                                <span class="cosy-declaration-text">I understand that conversations are based on personal experiences only and do not constitute advice, guidance, counselling, therapy or any other professional service.</span>
                            </label>
                        </div>
                        <div class="cosy-declaration-wrapper">
                            <label class="cosy-declaration-item">
                                <input type="checkbox" name="declaration_5" id="declaration_5" required>
                                <span class="cosy-declaration-text">I have read and agree to the CosyChats <a href="https://cosychats.com/terms-and-conditions" target="_blank">Terms &amp; Conditions</a> and <a href="https://cosychats.com/privacy-policy" target="_blank">Privacy Policy</a>.</span>
                            </label>
                        </div>
                    </div>
                </div>

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
            <?php $reg_img = get_option('cosy_registration_image_url') ?: (plugin_dir_url(__DIR__) . 'src/Assets/images/cosy.jpg'); ?>
            <img src="<?php echo esc_url($reg_img); ?>" alt="<?php esc_attr_e('Registration Banner', 'cosy-appointments'); ?>">
        </div>
    </div>
</div>