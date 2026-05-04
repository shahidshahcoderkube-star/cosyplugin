<?php

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Fetch saved meta
$prov_username = get_user_meta($user_id, 'prov_username', true);
$prov_fname    = get_user_meta($user_id, 'first_name', true);
$prov_mname    = get_user_meta($user_id, 'prov_mname', true);
$prov_sname    = get_user_meta($user_id, 'last_name', true);
$prov_email    = get_user_meta($user_id, 'prov_email', true);
$prov_phone    = get_user_meta($user_id, 'prov_phone', true);
$prov_address  = get_user_meta($user_id, 'prov_address', true);
$dob           = get_user_meta($user_id, 'dob', true);
$postal_code   = get_user_meta($user_id, 'postal_code', true);
$bio           = get_user_meta($user_id, 'description', true);
$gender        = get_user_meta($user_id, 'gender', true);
$profile_image = get_user_meta($user_id, 'profile_image', true);
$age_group     = get_user_meta($user_id, 'age_group', true);
?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <h3 class="text-dark">👤 Profile Information</h3>
        <form method="post" class="cosy-form-update" data-action="cosy_provider_information_update" enctype="multipart/form-data">
            <div class="cosy-message"></div>
            <div class="row">
                <!-- Left Column -->
                <div class="col-md-6">
                    <div class="mb-5">
                        <div class="circle">
                            <?php if ($profile_image): ?>
                                <img class="profile-pic" src="<?php echo esc_url($profile_image); ?>">
                            <?php else: ?>
                                <img class="profile-pic" src="https://t3.ftcdn.net/jpg/03/46/83/96/360_F_346839683_6nAPzbhpSkIpb8pmAwufkC7c5eD7wYws.jpg">
                            <?php endif; ?>
                        </div>
                        <div class="p-image">
                            <i class="bi bi-camera upload-button"></i>
                            <input class="file-upload" id="upload-button" name="profile_image" type="file" accept="image/*" />
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