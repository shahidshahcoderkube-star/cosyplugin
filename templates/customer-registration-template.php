<div class="cosy-customer-registration">
    <div class="wrapper">
        <!-- Left Column: Form -->
        <div class="cosy-reg-form">
            <h2>Create Account</h2>
            <p class="subtitle">Join us today! Please fill in your details.</p>

            <form id="customerForm" class="cosy-form" method="post" data-action="cosy_customer_register">
                <div class="cosy-message"></div>
                <?php wp_nonce_field('cosy_customer_register_nonce', 'cosy_nonce'); ?>
                <input type="hidden" name="action" value="cosy_customer_register">
                
                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="cust_name">Full Name</label>
                    <input type="text" name="cust_name" id="cust_name" placeholder="John Doe" required>
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="cust_email">Email Address</label>
                    <input type="email" name="cust_email" id="cust_email" placeholder="john@example.com" required autocomplete="off">
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="cust_pass">Password</label>
                    <input type="password" name="cust_pass" id="cust_pass" placeholder="••••••••" required>
                </div>

                <!-- Terms and Conditions Checkbox -->
                <p class="terms-container">
                    <input type="checkbox" name="terms" id="terms" required>
                    <label for="terms">
                        I have read and agree to the 
                        <a href="https://cosychats.com/terms-and-conditions" target="_blank">Terms and Conditions.</a>
                    </label>
                </p>

                <div class="cosy-btn-group">
                    <button type="submit" name="cosy_customer_register" class="button button-primary">Create Account</button>
                    
                    <p class="footer-text">
                        Already have an account? <a href="<?php echo home_url('login'); ?>">Sign In</a>
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