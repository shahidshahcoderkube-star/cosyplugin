<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Retrieve centralized provider data using GlobalCommonFunctions
$common = new class {
    use \Cosy\Appointments\Common\GlobalCommonFunctions;
};
$provider_data = $common->get_provider_data($user_id);
?>

<div class="card cosy-profile-card mb-4">
    <div class="card-body p-0">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="header-icon-badge" style="background: rgba(164, 67, 144, 0.1); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-user-circle" style="color: #a44390; font-size: 1.3rem;"></i>
            </div>
            <h3 class="mb-0" style="margin-bottom: 0 !important;"><?php esc_html_e('Profile Information', 'cosy-appointments'); ?></h3>
        </div>
        <form method="post" class="cosy-form-update" data-action="cosy_provider_information_update" enctype="multipart/form-data">
            <div class="cosy-message"></div>
            <div class="row">
                <!-- Left Column -->
                <div class="col-md-6">
                    <div class="mb-5 d-flex justify-content-center">
                        <div class="position-relative" style="width: 140px; height: 140px;">
                            <div class="circle mx-auto" style="width: 140px; height: 140px; border-radius: 50%; overflow: hidden;">
                                <?php if (!empty($provider_data['profile_image'])): ?>
                                    <img class="profile-pic" src="<?php echo esc_url($provider_data['profile_image']); ?>" style="width: 100%; height: 100%; object-fit: cover; object-position: center; display: block;">
                                <?php else: ?>
                                    <img class="profile-pic" src="https://t3.ftcdn.net/jpg/03/46/83/96/360_F_346839683_6nAPzbhpSkIpb8pmAwufkC7c5eD7wYws.jpg" style="width: 100%; height: 100%; object-fit: cover; object-position: center; display: block;">
                                <?php endif; ?>
                            </div>
                            <div class="p-image position-absolute" style="bottom: 0px; right: 0px; transform: none; top: auto; left: auto; z-index: 10;">
                                <i class="bi bi-camera upload-button"></i>
                                <input class="file-upload" id="upload-button" name="profile_image" type="file" accept="image/*" />
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php esc_html_e('Username', 'cosy-appointments'); ?></label>
                        <input type="text" name="prov_username" class="form-control" value="<?php echo esc_attr($provider_data['prov_username'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php esc_html_e('First Name', 'cosy-appointments'); ?></label>
                        <input type="text" name="prov_fname" class="form-control" value="<?php echo esc_attr($provider_data['first_name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php esc_html_e('Middle Name', 'cosy-appointments'); ?></label>
                        <input type="text" name="prov_mname" class="form-control" value="<?php echo esc_attr($provider_data['prov_mname'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php esc_html_e('Surname', 'cosy-appointments'); ?></label>
                        <input type="text" name="prov_sname" class="form-control" value="<?php echo esc_attr($provider_data['last_name'] ?? ''); ?>">
                    </div>

                </div>

                <!-- Right Column -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label"><?php esc_html_e('Email', 'cosy-appointments'); ?></label>
                        <input type="email" name="prov_email" class="form-control" value="<?php echo esc_attr($provider_data['prov_email'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php esc_html_e('Phone', 'cosy-appointments'); ?></label>
                        <input type="text" name="prov_phone" class="form-control" value="<?php echo esc_attr($provider_data['prov_phone'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?php esc_html_e('Date of Birth', 'cosy-appointments'); ?></label>
                        <input type="date" name="dob" class="form-control" value="<?php echo esc_attr($provider_data['dob'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php esc_html_e('Gender', 'cosy-appointments'); ?></label>
                        <select name="gender" class="form-select" required>
                            <option value="">--<?php esc_html_e('Select', 'cosy-appointments'); ?>--</option>
                            <option value="male" <?php selected($provider_data['gender'] ?? '', 'male'); ?>><?php esc_html_e('Male', 'cosy-appointments'); ?></option>
                            <option value="female" <?php selected($provider_data['gender'] ?? '', 'female'); ?>><?php esc_html_e('Female', 'cosy-appointments'); ?></option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php esc_html_e('Age Group', 'cosy-appointments'); ?></label>
                        <select name="age_group" class="form-select" required>
                            <option value="">--<?php esc_html_e('Select', 'cosy-appointments'); ?>--</option>
                            <option value="Teenager" <?php selected($provider_data['age_group'] ?? '', 'Teenager'); ?>><?php esc_html_e('Teenager', 'cosy-appointments'); ?></option>
                            <option value="Young Adult" <?php selected($provider_data['age_group'] ?? '', 'Young Adult'); ?>><?php esc_html_e('Young Adult', 'cosy-appointments'); ?></option>
                            <option value="Middle Aged" <?php selected($provider_data['age_group'] ?? '', 'Middle Aged'); ?>><?php esc_html_e('Middle Aged', 'cosy-appointments'); ?></option>
                            <option value="Senior" <?php selected($provider_data['age_group'] ?? '', 'Senior'); ?>><?php esc_html_e('Senior', 'cosy-appointments'); ?></option>
                            <option value="Golden Senior" <?php selected($provider_data['age_group'] ?? '', 'Golden Senior'); ?>><?php esc_html_e('Golden Senior', 'cosy-appointments'); ?></option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php esc_html_e('Postal Code', 'cosy-appointments'); ?></label>
                        <input type="text" name="postal_code" class="form-control" value="<?php echo esc_attr($provider_data['postal_code'] ?? ''); ?>" required>
                    </div>

                </div>
            </div>
            <div class="mb-3">
                <label class="form-label"><?php esc_html_e('Address', 'cosy-appointments'); ?></label>
                <textarea name="prov_address" class="form-control" required><?php echo esc_textarea($provider_data['prov_address'] ?? ''); ?></textarea>
            </div>
            <!-- Full Width Fields -->
            <div class="mb-3">
                <label class="form-label"><?php esc_html_e('Bio', 'cosy-appointments'); ?></label>
                <textarea name="bio" class="form-control" rows="3" required><?php echo esc_textarea($provider_data['description'] ?? ''); ?></textarea>
            </div>

            <div class="text-center mt-3">
                <button type="submit" name="update_provider_profile" class="btn btn-filled custom-btn"><?php esc_html_e('Update Profile', 'cosy-appointments'); ?></button>
            </div>
        </form>
    </div>
</div>