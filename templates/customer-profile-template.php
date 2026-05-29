<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
if (!$current_user->exists()) {
    // If not logged in, redirect to login page or show login link
?>
    <div class="container my-5 text-center" style="font-family: 'Plus Jakarta Sans', sans-serif;">
        <div class="card border-0 shadow-sm p-5 mx-auto" style="max-width: 500px; border-radius: 20px;">
            <div class="mb-4">
                <i class="fas fa-lock text-warning" style="font-size: 3rem;"></i>
            </div>
            <h3 class="fw-bold mb-3"><?php esc_html_e('Access Denied', 'cosy-appointments'); ?></h3>
            <p class="text-muted"><?php esc_html_e('Please log in to view and manage your profile details.', 'cosy-appointments'); ?></p>
            <a href="<?php echo esc_url(site_url('/login')); ?>" class="btn text-white fw-bold mt-3 px-4 py-2" style="background: linear-gradient(135deg, #a44390, #6d2e67); border-radius: 10px;">
                <?php esc_html_e('Log In Now', 'cosy-appointments'); ?>
            </a>
        </div>
    </div>
<?php
    return;
}

$user_id = $current_user->ID;
$first_name = get_user_meta($user_id, 'first_name', true) ?: $current_user->display_name;
$last_name = get_user_meta($user_id, 'last_name', true);
$email = $current_user->user_email;
?>

<div class="container my-5" style="font-family: 'Plus Jakarta Sans', sans-serif;">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <!-- Header Section -->
            <div class="d-flex align-items-center gap-3 mb-4 pb-3" style="border-bottom: 1.5px solid #f1f5f9;">
                <div class="header-icon-badge" style="background: rgba(164, 67, 144, 0.1); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-user-cog" style="color: #a44390; font-size: 1.2rem;"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold" style="color: #1e293b; font-family: 'Outfit', sans-serif; font-size: 1.75rem;"><?php esc_html_e('Profile Settings', 'cosy-appointments'); ?></h3>
                    <p class="text-muted mb-0" style="font-size: 0.95rem;"><?php esc_html_e('Update your personal details and manage your account password.', 'cosy-appointments'); ?></p>
                </div>
            </div>

            <div class="row g-4">
                <!-- Left: Edit Profile Details -->
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 20px; background: #fff;">
                        <h4 class="fw-bold mb-4" style="color: #1e293b; font-size: 1.2rem; font-family: 'Outfit', sans-serif;">
                            <i class="fas fa-id-card me-2 text-primary" style="color: #a44390 !important;"></i> <?php esc_html_e('Personal Information', 'cosy-appointments'); ?>
                        </h4>

                        <form id="cosyCustomerProfileForm" method="post">
                            <?php wp_nonce_field('cosy_customer_profile_nonce', 'cosy_profile_nonce_field'); ?>
                            <div class="profile-msg mb-3"></div>

                            <div class="mb-3">
                                <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;"><?php esc_html_e('First Name', 'cosy-appointments'); ?></label>
                                <input type="text" name="first_name" class="form-control px-3 py-2" value="<?php echo esc_attr($first_name); ?>" required style="border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: 0.95rem;">
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;"><?php esc_html_e('Last Name', 'cosy-appointments'); ?></label>
                                <input type="text" name="last_name" class="form-control px-3 py-2" value="<?php echo esc_attr($last_name); ?>" style="border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: 0.95rem;">
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;"><?php esc_html_e('Email Address', 'cosy-appointments'); ?></label>
                                <input type="email" name="email" class="form-control px-3 py-2" value="<?php echo esc_attr($email); ?>" required style="border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: 0.95rem;">
                            </div>

                            <button type="submit" class="btn text-white fw-bold w-100 py-2" style="background: linear-gradient(135deg, #a44390, #6d2e67); border-radius: 10px; border: none; font-size: 0.95rem; transition: all 0.2s;">
                                <?php esc_html_e('Save Changes', 'cosy-appointments'); ?>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right: Change Password -->
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 20px; background: #fff;">
                        <h4 class="fw-bold mb-4" style="color: #1e293b; font-size: 1.2rem; font-family: 'Outfit', sans-serif;">
                            <i class="fas fa-key me-2 text-primary" style="color: #a44390 !important;"></i> <?php esc_html_e('Change Password', 'cosy-appointments'); ?>
                        </h4>

                        <form id="cosyCustomerPasswordForm" method="post">
                            <?php wp_nonce_field('cosy_customer_password_nonce', 'cosy_password_nonce_field'); ?>
                            <div class="password-msg mb-3"></div>

                            <div class="mb-3">
                                <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;"><?php esc_html_e('New Password', 'cosy-appointments'); ?></label>
                                <input type="password" name="new_password" class="form-control px-3 py-2" required style="border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: 0.95rem;">
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-muted fw-semibold" style="font-size: 0.85rem;"><?php esc_html_e('Confirm New Password', 'cosy-appointments'); ?></label>
                                <input type="password" name="confirm_password" class="form-control px-3 py-2" required style="border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: 0.95rem;">
                            </div>

                            <button type="submit" class="btn text-white fw-bold w-100 py-2" style="background: linear-gradient(135deg, #a44390, #6d2e67); border-radius: 10px; border: none; font-size: 0.95rem; transition: all 0.2s;">
                                <?php esc_html_e('Update Password', 'cosy-appointments'); ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        // Helper to generate dynamic alert alerts matching the theme
        function cosyAlert(type, message) {
            var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            var bg = type === 'success' ? 'rgba(34, 197, 94, 0.1)' : 'rgba(239, 68, 68, 0.1)';
            var color = type === 'success' ? '#22c55e' : '#ef4444';

            return '<div class="alert d-flex align-items-center border-0 p-3 mb-3" style="background: ' + bg + '; border-radius: 12px; color: ' + color + '; font-weight: 500; font-size: 0.9rem;" role="alert">' +
                '<i class="fas ' + icon + ' me-2" style="font-size: 1.1rem;"></i>' +
                '<div>' + message + '</div>' +
                '</div>';
        }

        // Handle Profile Form Submission
        $('#cosyCustomerProfileForm').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var $btn = $form.find("button[type='submit']");
            var $msg = $form.find(".profile-msg");

            var formData = {
                action: 'cosy_customer_profile_update',
                cosy_profile_nonce: $('#cosy_profile_nonce_field').val(),
                first_name: $form.find("input[name='first_name']").val(),
                last_name: $form.find("input[name='last_name']").val(),
                email: $form.find("input[name='email']").val()
            };

            $.ajax({
                url: cosy_ajax.ajax_url,
                type: 'POST',
                data: formData,
                beforeSend: function() {
                    $btn.prop('disabled', true).text('Saving...');
                    $msg.html('');
                },
                success: function(response) {
                    if (response.success) {
                        $msg.html(cosyAlert('success', response.data));
                    } else {
                        $msg.html(cosyAlert('danger', response.data));
                    }
                },
                error: function() {
                    $msg.html(cosyAlert('danger', 'An unexpected error occurred. Please try again.'));
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Save Changes');
                }
            });
        });

        // Handle Password Form Submission
        $('#cosyCustomerPasswordForm').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var $btn = $form.find("button[type='submit']");
            var $msg = $form.find(".password-msg");

            var formData = {
                action: 'cosy_customer_password_update',
                cosy_password_nonce: $('#cosy_password_nonce_field').val(),
                new_password: $form.find("input[name='new_password']").val(),
                confirm_password: $form.find("input[name='confirm_password']").val()
            };

            $.ajax({
                url: cosy_ajax.ajax_url,
                type: 'POST',
                data: formData,
                beforeSend: function() {
                    $btn.prop('disabled', true).text('Updating...');
                    $msg.html('');
                },
                success: function(response) {
                    if (response.success) {
                        $msg.html(cosyAlert('success', response.data));
                        $form[0].reset();
                    } else {
                        $msg.html(cosyAlert('danger', response.data));
                    }
                },
                error: function() {
                    $msg.html(cosyAlert('danger', 'An unexpected error occurred. Please try again.'));
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Update Password');
                }
            });
        });
    });
</script>