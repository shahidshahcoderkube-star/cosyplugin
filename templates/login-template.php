<div class="cosy-login cosy-customer-registration ">
    <div class="wrapper">
        <div class="cosy-reg-form">
            <h2>Login</h2>
            <form id="cosyLoginForm" class="cosy-form" method="post" data-action="cosy_login">
                <div class="cosy-message"></div>
                <p><input type="text" name="log" placeholder="Username" required></p>
                <p><input type="password" name="pwd" placeholder="Password" required></p>
                <p><button type="submit" class="button button-primary">Login</button></p>
            </form>

            <div class="cosy-forgot">
                <a href="<?php echo wp_lostpassword_url(); ?>">Forgot Password?</a>
            </div>
        </div>
        <!-- Right Column: Image -->
        <div class="cosy-reg-message">
            <img src="<?php echo plugin_dir_url(__DIR__); ?>src/Assets/images/log-in.jpg" alt="">
        </div>
    </div>
</div>