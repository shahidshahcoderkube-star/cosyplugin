<div class="container mt-5">
    <?php if (is_user_logged_in()) : ?>
        <div class="alert alert-success text-center" role="alert">
            <h4 class="alert-heading">🎉 Account Verified!</h4>
            <p>Your provider account has been successfully verified and you are now logged in.</p>
            <hr>
            <a href="<?php echo home_url('/provider-dashboard/'); ?>" class="btn btn-success mt-3">Go to Dashboard</a>
        </div>
    <?php else : ?>
        <div class="alert alert-danger text-center" role="alert">
            <h4 class="alert-heading">⚠️ Access Denied</h4>
            <p>You must verify your account and be logged in to access the dashboard.</p>
            <hr>
            <a href="<?php echo home_url('/login/'); ?>" class="btn btn-danger mt-3">Login</a>
        </div>
    <?php endif; ?>
</div>