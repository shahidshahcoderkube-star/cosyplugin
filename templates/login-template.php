<div class="cosy-login cosy-customer-registration">
    <div class="wrapper">
        <!-- Left Column: Form -->
        <div class="cosy-reg-form">
            <h2>Login</h2>
            <p class="subtitle">Welcome back! Please enter your details.</p>
            
            <form id="cosyLoginForm" class="cosy-form" method="post" data-action="cosy_login">
                <div class="cosy-message"></div>
                <?php wp_nonce_field('cosy_login_nonce', 'cosy_nonce'); ?>
                <input type="hidden" name="action" value="cosy_login">
                
                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="log">Username or Email</label>
                    <input type="text" name="log" id="log" placeholder="Enter your username" required>
                </div>
                
                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="pwd">Password</label>
                    <input type="password" name="pwd" id="pwd" placeholder="••••••••" required>
                </div>
                
                <p>
                    <button type="submit" class="button button-primary">Sign In</button>
                </p>
            </form>

            <div class="cosy-forgot">
                <a href="<?php echo wp_lostpassword_url(); ?>">Forgot Password?</a>
            </div>
            
            <p class="footer-text">
                Don't have an account? <a href="<?php echo home_url('user-registration'); ?>">Create one</a>
            </p>
        </div>

        <!-- Right Column: Image -->
        <div class="cosy-reg-message">
            <img src="<?php echo plugin_dir_url(__DIR__); ?>src/Assets/images/log-in.jpg" alt="Login Illustration">
        </div>
    </div>
</div>