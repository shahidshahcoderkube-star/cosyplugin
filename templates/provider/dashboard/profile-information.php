<?php

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Reuse the GlobalCommonFunctions trait to retrieve all user details in a single query
$common = new class {
    use \Cosy\Appointments\Common\GlobalCommonFunctions;
};
$provider_data = $common->get_provider_data($user_id);

// Map variables from centralized data array with fallback defaults
$prov_username = $provider_data['prov_username'] ?? '';
$prov_fname    = $provider_data['first_name'] ?? '';
$prov_mname    = $provider_data['prov_mname'] ?? '';
$prov_sname    = $provider_data['last_name'] ?? '';
$prov_email    = $provider_data['prov_email'] ?? '';
$prov_phone    = $provider_data['prov_phone'] ?? '';
$prov_address  = $provider_data['prov_address'] ?? '';
$dob           = $provider_data['dob'] ?? '';
$postal_code   = $provider_data['postal_code'] ?? '';
$bio           = $provider_data['description'] ?? '';
$gender        = $provider_data['gender'] ?? '';
$profile_image = $provider_data['profile_image'] ?? '';
$age_group     = $provider_data['age_group'] ?? '';
?>

<div class="card cosy-profile-card mb-4">
    <div class="card-body p-0">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="header-icon-badge" style="background: rgba(164, 67, 144, 0.1); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-user-circle" style="color: #a44390; font-size: 1.3rem;"></i>
            </div>
            <h3 class="mb-0" style="margin-bottom: 0 !important;">Profile Information</h3>
        </div>
        <form method="post" class="cosy-form-update" data-action="cosy_provider_information_update" enctype="multipart/form-data">
            <div class="cosy-message"></div>
            <div class="row">
                <!-- Left Column -->
                <div class="col-md-6">
                    <div class="mb-5 d-flex justify-content-center">
                        <div class="position-relative" style="width: 140px;">
                            <div class="circle mx-auto">
                                <?php if ($profile_image): ?>
                                    <img class="profile-pic" src="<?php echo esc_url($profile_image); ?>">
                                <?php else: ?>
                                    <img class="profile-pic" src="https://t3.ftcdn.net/jpg/03/46/83/96/360_F_346839683_6nAPzbhpSkIpb8pmAwufkC7c5eD7wYws.jpg">
                                <?php endif; ?>
                            </div>
                            <div class="p-image position-absolute" style="bottom: 5px; right: 5px; transform: none; top: auto; left: auto;">
                                <i class="bi bi-camera upload-button"></i>
                                <input class="file-upload" id="upload-button" name="profile_image" type="file" accept="image/*" />
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="prov_username" class="form-control" value="<?php echo esc_attr($prov_username); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" name="prov_fname" class="form-control" value="<?php echo esc_attr($prov_fname); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Middle Name</label>
                        <input type="text" name="prov_mname" class="form-control" value="<?php echo esc_attr($prov_mname); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Surname</label>
                        <input type="text" name="prov_sname" class="form-control" value="<?php echo esc_attr($prov_sname); ?>" required>
                    </div>


                </div>

                <!-- Right Column -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="prov_email" class="form-control" value="<?php echo esc_attr($prov_email); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="prov_phone" class="form-control" value="<?php echo esc_attr($prov_phone); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="dob" class="form-control" value="<?php echo esc_attr($dob); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select" required>
                            <option value="">--Select--</option>
                            <option value="male" <?php selected($gender, 'male'); ?>>Male</option>
                            <option value="female" <?php selected($gender, 'female'); ?>>Female</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Age Group</label>
                        <select name="age_group" class="form-select" required>
                            <option value="">--Select--</option>
                            <option value="Teenager" <?php selected($age_group, 'Teenager'); ?>>Teenager</option>
                            <option value="Young Adult" <?php selected($age_group, 'Young Adult'); ?>>Young Adult</option>
                            <option value="Middle Aged" <?php selected($age_group, 'Middle Aged'); ?>>Middle Aged</option>
                            <option value="Senior" <?php selected($age_group, 'Senior'); ?>>Senior</option>
                            <option value="Golden Senior" <?php selected($age_group, 'Golden Senior'); ?>>Golden Senior</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Postal Code</label>
                        <input type="text" name="postal_code" class="form-control" value="<?php echo esc_attr($postal_code); ?>">
                    </div>

                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Address</label>
                <textarea name="prov_address" class="form-control"><?php echo esc_textarea($prov_address); ?></textarea>
            </div>
            <!-- Full Width Fields -->
            <div class="mb-3">
                <label class="form-label">Bio</label>
                <textarea name="bio" class="form-control" rows="3"><?php echo esc_textarea($bio); ?></textarea>
            </div>

            <div class="text-center mt-3">
                <button type="submit" name="update_provider_profile" class="btn btn-filled custom-btn">Update Profile</button>
            </div>
        </form>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        // Trigger file input when camera icon is clicked
        $('.upload-button').on('click', function() {
            $('.file-upload').click();
        });

        // Preview image after selection
        $('.file-upload').on('change', function() {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('.profile-pic').attr('src', e.target.result);
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
</script>