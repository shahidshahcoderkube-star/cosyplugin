<div class="cosy-provider-registration">
    <div class="wrapper">
        <div class="cosy-reg-form">
            <h2>Service Provider Registration</h2>

            <form id="providerForm" class="cosy-form two-column-form" data-action="cosy_provider_register">
                <div class="cosy-message"></div>
                <?php wp_nonce_field('cosy_provider_register_nonce', 'cosy_nonce'); ?>
                <input type="hidden" name="action" value="cosy_provider_register">

                <div class="cc_provider_form">
                    <!-- Left Column -->
                    <div class="form-column">
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="prov_username">Username</label>
                            <input type="text" name="prov_username" id="prov_username" placeholder="Choose a username"
                                required>
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="prov_mname">Middle Name</label>
                            <input type="text" name="prov_mname" id="prov_mname" placeholder="Middle Name" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="prov_email">Email Address</label>
                            <input type="email" name="prov_email" id="prov_email" placeholder="email@example.com"
                                required>
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="dob">Date of Birth</label>
                            <input type="date" name="dob" id="dob" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="prov_phone">Telephone</label>
                            <input type="tel" name="prov_phone" id="prov_phone" placeholder="+1 234 567 890" required>
                        </div>

                        <div class="terms-container" style="margin-top: 20px;">
                            <input type="checkbox" name="terms" id="terms" required>
                            <label for="terms" style="display: inline; font-weight: 400; font-size: 0.9rem;">
                                I have read and agree to the
                                <a href="https://cosychats.com/terms-and-conditions" target="_blank"
                                    style="color: #a44390; font-weight: 600;">Terms and Conditions</a>.
                            </label>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="form-column">
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="prov_fname">First Name</label>
                            <input type="text" name="prov_fname" id="prov_fname" placeholder="First Name" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="prov_sname">Surname</label>
                            <input type="text" name="prov_sname" id="prov_sname" placeholder="Surname" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="prov_pass">Password</label>
                            <input type="password" name="prov_pass" id="prov_pass" placeholder="••••••••" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="prov_pass_confirm">Confirm Password</label>
                            <input type="password" name="prov_pass_confirm" id="prov_pass_confirm"
                                placeholder="••••••••" required>
                        </div>

                        <div class="form-group">
                            <label for="prov_address">Full Postal Address</label>
                            <textarea name="prov_address" id="prov_address" rows="3"
                                placeholder="Enter your full address" required></textarea>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="form-full">
                        <div class="cosy-btn-group">
                            <button type="submit" name="cosy_customer_register"
                                class="button button-primary">Submit</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>