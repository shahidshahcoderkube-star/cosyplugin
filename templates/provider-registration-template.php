<div class="cosy-provider-registration">
    <div class="wrapper">
        <div class="cosy-reg-form">
            <h2>Service Provider Registration</h2>
            <form id="providerForm" class="cosy-form two-column-form" data-action="cosy_provider_register">
                <div class="cosy-message"></div>
                <div class="cc_provider_form">
                    <div class="form-column">
                        <p><input type="text" name="prov_username" placeholder="Username" required></p>
                        <p><input type="text" name="prov_mname" placeholder="Middle Name" required></p>
                        <p><input type="email" name="prov_email" placeholder="Email" required></p>
                        <p> <input type="date" name="dob" required></p>
                        <!-- <p><input type="date" name="dob" id="dob" placeholder="Date of Birth" onfocus="this.showPicker()"></p> -->
                        <p><input type="tel" name="prov_phone" placeholder="Telephone" required></p>
                        <p>
                            <label>
                                <input type="checkbox" name="terms" required>
                                I have read and agree to the
                                <a href="https://cosychats.com/terms-and-conditions" target="_blank">Terms and Conditions</a>.
                            </label>
                        </p>
                    </div>

                    <div class="form-column">
                        <p><input type="text" name="prov_fname" placeholder="First Name" required></p>
                        <p><input type="text" name="prov_sname" placeholder="Surname" required></p>
                        <p><input type="password" name="prov_pass" placeholder="Password" required></p>
                        <p><input type="password" name="prov_pass_confirm" placeholder="Confirm Password" required></p>
                        <p><textarea name="prov_address" rows="3" placeholder="Full Postal Address" required></textarea></p>

                    </div>

                    <div class="form-full">
                        <p><button type="submit" name="cosy_customer_register" class="button button-primary">Submit</button></p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>