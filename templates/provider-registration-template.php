<div class="cosy-provider-registration">
    <div class="wrapper">
        <div class="cosy-reg-form">
            <h2><?php esc_html_e('Service Provider Registration', 'cosy-appointments'); ?></h2>

            <form id="providerForm" class="cosy-form two-column-form" data-action="cosy_provider_register">
                <div class="cosy-message"></div>
                <?php wp_nonce_field('cosy_provider_register_nonce', 'cosy_nonce'); ?>
                <input type="hidden" name="action" value="cosy_provider_register">

                <div class="cc_provider_form">
                    <!-- Left Column -->
                    <div class="form-column">
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="prov_username"><?php esc_html_e('Username', 'cosy-appointments'); ?></label>
                            <input type="text" name="prov_username" id="prov_username" placeholder="<?php esc_attr_e('Choose a username', 'cosy-appointments'); ?>"
                                required>
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="prov_mname"><?php esc_html_e('Middle Name', 'cosy-appointments'); ?></label>
                            <input type="text" name="prov_mname" id="prov_mname" placeholder="<?php esc_attr_e('Middle Name', 'cosy-appointments'); ?>">
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="prov_email"><?php esc_html_e('Email Address', 'cosy-appointments'); ?></label>
                            <input type="email" name="prov_email" id="prov_email" placeholder="<?php esc_attr_e('email@example.com', 'cosy-appointments'); ?>"
                                required>
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="dob"><?php esc_html_e('Date of Birth', 'cosy-appointments'); ?></label>
                            <input type="date" name="dob" id="dob" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="prov_phone"><?php esc_html_e('Telephone', 'cosy-appointments'); ?></label>
                            <input type="tel" name="prov_phone" id="prov_phone" placeholder="<?php esc_attr_e('+1 234 567 890', 'cosy-appointments'); ?>" required>
                        </div>

                        <div class="terms-container" style="margin-top: 20px;">
                            <input type="checkbox" name="terms" id="terms" required>
                            <label for="terms" style="display: inline; font-weight: 400; font-size: 0.9rem;">
                                <?php echo wp_kses(sprintf(__('I have read and agree to the <a href="%s" target="_blank">Terms and Conditions</a>.', 'cosy-appointments'), esc_url('https://cosychats.com/terms-and-conditions')), array('a' => array('href' => array(), 'target' => array()))); ?>
                            </label>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="form-column">
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="prov_fname"><?php esc_html_e('First Name', 'cosy-appointments'); ?></label>
                            <input type="text" name="prov_fname" id="prov_fname" placeholder="<?php esc_attr_e('First Name', 'cosy-appointments'); ?>" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="prov_sname"><?php esc_html_e('Surname', 'cosy-appointments'); ?></label>
                            <input type="text" name="prov_sname" id="prov_sname" placeholder="<?php esc_attr_e('Surname', 'cosy-appointments'); ?>">
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="prov_pass"><?php esc_html_e('Password', 'cosy-appointments'); ?></label>
                            <input type="password" name="prov_pass" id="prov_pass" placeholder="<?php esc_attr_e('••••••••', 'cosy-appointments'); ?>" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="prov_pass_confirm"><?php esc_html_e('Confirm Password', 'cosy-appointments'); ?></label>
                            <input type="password" name="prov_pass_confirm" id="prov_pass_confirm"
                                placeholder="<?php esc_attr_e('••••••••', 'cosy-appointments'); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="prov_address"><?php esc_html_e('Full Postal Address', 'cosy-appointments'); ?></label>
                            <textarea name="prov_address" id="prov_address" rows="3"
                                placeholder="<?php esc_attr_e('Enter your full address', 'cosy-appointments'); ?>" required></textarea>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="form-full">
                        <div class="cosy-btn-group">
                            <button type="submit" name="cosy_customer_register"
                                class="button button-primary"><?php esc_html_e('Submit', 'cosy-appointments'); ?></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>