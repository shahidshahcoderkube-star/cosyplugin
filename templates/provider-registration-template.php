<div class="cosy-provider-registration">
    <div class="wrapper">
        <div class="cosy-reg-form">
            <h2><?php esc_html_e('Become a CosyChats Parent', 'cosy-appointments'); ?></h2>

            <form id="providerForm" class="cosy-form two-column-form" data-action="cosy_provider_register">
                <div class="cosy-message"></div>
                <?php wp_nonce_field('cosy_provider_register_nonce', 'cosy_nonce'); ?>
                <input type="hidden" name="action" value="cosy_provider_register">

                <div class="cc_provider_form">
                    <!-- Left Column -->
                    <div class="form-column">
                        <div class="form-group">
                            <label for="prov_username"><?php esc_html_e('Username', 'cosy-appointments'); ?></label>
                            <input type="text" name="prov_username" id="prov_username" placeholder="<?php esc_attr_e('Choose a username', 'cosy-appointments'); ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="prov_mname"><?php esc_html_e('Middle Name', 'cosy-appointments'); ?></label>
                            <input type="text" name="prov_mname" id="prov_mname" placeholder="<?php esc_attr_e('Middle Name', 'cosy-appointments'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="prov_email"><?php esc_html_e('Email Address', 'cosy-appointments'); ?></label>
                            <input type="email" name="prov_email" id="prov_email" placeholder="<?php esc_attr_e('Email', 'cosy-appointments'); ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="dob"><?php esc_html_e('Date of Birth', 'cosy-appointments'); ?></label>
                            <input type="date" name="dob" id="dob" required>
                        </div>

                        <div class="form-group">
                            <label for="prov_phone"><?php esc_html_e('Telephone', 'cosy-appointments'); ?></label>
                            <input type="tel" name="prov_phone" id="prov_phone" placeholder="<?php esc_attr_e('+1 234 567 890', 'cosy-appointments'); ?>" required>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="form-column">
                        <div class="form-group">
                            <label for="prov_fname"><?php esc_html_e('First Name', 'cosy-appointments'); ?></label>
                            <input type="text" name="prov_fname" id="prov_fname" placeholder="<?php esc_attr_e('First Name', 'cosy-appointments'); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="prov_sname"><?php esc_html_e('Surname', 'cosy-appointments'); ?></label>
                            <input type="text" name="prov_sname" id="prov_sname" placeholder="<?php esc_attr_e('Surname', 'cosy-appointments'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="prov_pass"><?php esc_html_e('Password', 'cosy-appointments'); ?></label>
                            <div class="cosy-password-wrapper">
                                <input type="password" name="prov_pass" id="prov_pass" placeholder="<?php esc_attr_e('••••••••', 'cosy-appointments'); ?>" required>
                                <span class="cosy-toggle-password">
                                    <i class="fa-regular fa-eye"></i>
                                </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="prov_pass_confirm"><?php esc_html_e('Confirm Password', 'cosy-appointments'); ?></label>
                            <div class="cosy-password-wrapper">
                                <input type="password" name="prov_pass_confirm" id="prov_pass_confirm"
                                    placeholder="<?php esc_attr_e('••••••••', 'cosy-appointments'); ?>" required>
                                <span class="cosy-toggle-password">
                                    <i class="fa-regular fa-eye"></i>
                                </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="prov_address"><?php esc_html_e('Full Postal Address', 'cosy-appointments'); ?></label>
                            <textarea name="prov_address" id="prov_address" rows="1"
                                placeholder="<?php esc_attr_e('Enter your full address', 'cosy-appointments'); ?>" required></textarea>
                        </div>
                    </div>

                    <!-- 5 Mandatory Registration Declarations Card -->
                    <div class="form-full cosy-declarations-box" style="margin-top: 15px; margin-bottom: 20px;">
                        <div class="cosy-declarations-header">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span><?php esc_html_e('Platform Declarations', 'cosy-appointments'); ?></span>
                        </div>
                        <div class="cosy-declarations-list">
                            <div class="cosy-declaration-wrapper">
                                <label class="cosy-declaration-item">
                                    <input type="checkbox" name="declaration_1" id="declaration_1" required>
                                    <span class="cosy-declaration-text"><?php esc_html_e('I am 18 years of age or over.', 'cosy-appointments'); ?></span>
                                </label>
                            </div>
                            <div class="cosy-declaration-wrapper">
                                <label class="cosy-declaration-item">
                                    <input type="checkbox" name="declaration_2" id="declaration_2" required>
                                    <span class="cosy-declaration-text"><?php esc_html_e('I understand that CosyChats is an introduction platform for parent-to-parent conversations based on personal lived experiences.', 'cosy-appointments'); ?></span>
                                </label>
                            </div>
                            <div class="cosy-declaration-wrapper">
                                <label class="cosy-declaration-item">
                                    <input type="checkbox" name="declaration_3" id="declaration_3" required>
                                    <span class="cosy-declaration-text"><?php esc_html_e('I understand that CosyChats is not a crisis or emergency service and is not intended for vulnerable adults or situations requiring professional, medical, clinical or urgent support.', 'cosy-appointments'); ?></span>
                                </label>
                            </div>
                            <div class="cosy-declaration-wrapper">
                                <label class="cosy-declaration-item">
                                    <input type="checkbox" name="declaration_4" id="declaration_4" required>
                                    <span class="cosy-declaration-text"><?php esc_html_e('I understand that conversations are based on personal experiences only and do not constitute advice, guidance, counselling, therapy or any other professional service.', 'cosy-appointments'); ?></span>
                                </label>
                            </div>
                            <div class="cosy-declaration-wrapper">
                                <label class="cosy-declaration-item">
                                    <input type="checkbox" name="declaration_5" id="declaration_5" required>
                                    <span class="cosy-declaration-text"><?php echo wp_kses(sprintf(__('I have read and agree to the CosyChats <a href="%s" target="_blank">Terms & Conditions</a> and <a href="%s" target="_blank">Privacy Policy</a>.', 'cosy-appointments'), esc_url('https://cosychats.com/terms-and-conditions'), esc_url('https://cosychats.com/privacy-policy')), array('a' => array('href' => array(), 'target' => array()))); ?></span>
                                </label>
                            </div>
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