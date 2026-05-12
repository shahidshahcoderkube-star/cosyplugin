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

<style>
.cosy-profile-card {
    background: #ffffff;
    border-radius: 20px !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05) !important;
    border: none !important;
    padding: 30px;
}

.cosy-profile-card h3 {
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
    font-size: 1.5rem;
    color: #1e293b;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.cosy-profile-card .form-label {
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    color: #475569;
    font-size: 0.9rem;
    margin-bottom: 10px;
}

.cosy-profile-card .form-control,
.cosy-profile-card .form-select {
    border: 1.5px solid #e2e8f0 !important;
    border-radius: 12px !important;
    font-family: 'Poppins', sans-serif;
    font-size: 0.95rem;
    color: #334155;
    background-color: #f8fafc !important;
    padding: 10px 15px !important;
    transition: all 0.3s ease;
}

.cosy-profile-card .form-select {
    background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23a44390%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E') !important;
    background-repeat: no-repeat !important;
    background-position: right 1rem center !important;
    background-size: 1.1em !important;
}

.cosy-profile-card .form-control:focus,
.cosy-profile-card .form-select:focus {
    border-color: #a44390 !important;
    background-color: #fff !important;
    box-shadow: 0 0 0 4px rgba(164, 67, 144, 0.1) !important;
}

.cosy-profile-card .custom-btn {
    background: linear-gradient(135deg, #a44390 0%, #833573 100%) !important;
    border: none !important;
    border-radius: 12px !important;
    padding: 14px 45px !important;
    color: #fff !important;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2) !important;
}

.cosy-profile-card .circle {
    width: 140px;
    height: 140px;
    border: 5px solid #fff;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    overflow: hidden;
    border-radius: 50%;
}

.cosy-profile-card .p-image {
    background: #a44390;
    color: #fff;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: 3px solid #fff;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>

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