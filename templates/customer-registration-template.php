<div class="cosy-customer-registration">
    <div class="wrapper">
        <!-- Left Column: Form -->
        <div class="cosy-reg-form">
            <h2>Customer Registration</h2>
            <form id="customerForm" class="cosy-form" method="post" data-action="cosy_customer_register">
                <div class="cosy-message"></div>
                <p><label>Name<br><input type="text" name="cust_name" required></label></p>
                <p><label>Email<br><input type="email" name="cust_email" required autocomplete="off"></label></p>
                <p><label>Password<br><input type="password" name="cust_pass" required></label></p>

                <!-- Terms and Conditions Checkbox -->
                <p>
                    <label>
                        <input type="checkbox" name="terms" id="terms" required>
                        <span>I have read and agree to the</span>
                        <a href="https://cosychats.com/terms-and-conditions" target="_blank" id="term_cond">Terms and Conditions.</a>
                    </label>
                </p>

                <div class="cosy-btn">
                    <p><button type="submit" name="cosy_customer_register" class="button button-primary">Submit</button></p>
                    <p><a href="<?php echo home_url('login'); ?>" class="button button-primary">Login</a></p>
                </div>

            </form>
        </div>

        <!-- Right Column: Image -->
        <div class="cosy-reg-message">
            <img src="<?php echo plugin_dir_url(__DIR__); ?>src/Assets/images/cosy.jpg" alt="">
        </div>
    </div>
</div>