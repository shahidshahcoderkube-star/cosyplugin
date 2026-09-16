<?php

/**
 * PROVIDER DASHBOARD INTRODUCTION VIDEO UPLOAD TEMPLATE
 * 
 * USE CASE:
 * Renders the introduction video dropzone upload and status viewer in Provider Dashboard.
 * 
 * HOW TO USE:
 * Loaded dynamically via AJAX when provider selects the "Introduction Video" tab.
 * 
 * WHAT IT DOES INTERNALLY:
 * 1. Checks provider video approval status ('pending', 'approved', 'rejected').
 * 2. Displays upload form dropzone or video preview.
 * 3. Interactions handled by validation.js (initVideoUpload).
 */
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="card cosy-video-card mb-4">
    <div class="card-body p-0">
        <h3><i class="fas fa-video" style="color: #a44390;"></i> <?php esc_html_e('Introduction Video', 'cosy-appointments'); ?></h3>

        <?php
        $user_id = get_current_user_id();
        $data = $this->get_provider_data($user_id);

        $video_status = $data['video_status'];
        $introduction_video_url = $data['introduction_video'];
        $pending_video_url = get_user_meta($user_id, 'pending_introduction_video', true);

        $limit_mb = intval(get_option('cosy_max_video_upload_size', 3));
        if ($limit_mb <= 0) {
            $limit_mb = 3;
        }
        ?>

        <?php if (!empty($pending_video_url) && !empty($introduction_video_url)) : ?>
            <!-- Replacement video is pending admin review while approved video stays active -->
            <div class="alert alert-info border-0 rounded-4 shadow-sm py-3 mb-4" role="alert">
                <i class="fas fa-clock me-2"></i> <?php esc_html_e('Your new video is currently under review. Your existing approved video remains live on your profile until approved.', 'cosy-appointments'); ?>
            </div>

            <!-- Current Video Player -->
            <div class="mb-3">
                <div id="existing-video-<?php echo esc_attr($user_id); ?>" class="mt-1" style="position:relative;">
                    <video controls width="100%">
                        <source src="<?php echo esc_url($introduction_video_url); ?>" type="video/mp4">
                    </video>
                    <button id="remove-video-<?php echo esc_attr($user_id); ?>"
                        data-action="delete_video"
                        type="button"
                        class="btn remove-video"
                        data-id="<?php echo esc_attr($user_id); ?>"
                        style="position:absolute; top:15px; right:15px;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

        <?php elseif (!empty($pending_video_url) || $video_status === 'pending') : ?>
            <!-- Fresh upload pending review -->
            <div class="alert alert-warning border-0 rounded-4 shadow-sm py-3" role="alert">
                <i class="fas fa-clock me-2"></i> <?php esc_html_e('Your video is currently under review.', 'cosy-appointments'); ?>
            </div>

        <?php elseif ($video_status === 'rejected') : ?>
            <div class="alert alert-danger border-0 rounded-4 shadow-sm py-3" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php esc_html_e('Your video was not approved. Please upload a new one.', 'cosy-appointments'); ?>
            </div>

            <!-- Show upload form again -->
            <form id="video-upload-form-<?php echo esc_attr($user_id); ?>"
                class="cosy_form_video video-upload-form mt-4"
                data-action="cosy_provider_video"
                method="post"
                enctype="multipart/form-data">

                <div class="cosy-message"></div>

                <div id="video-dropzone-<?php echo esc_attr($user_id); ?>"
                    class="video-dropzone d-flex align-items-center justify-content-center flex-column p-5"
                    style="cursor:pointer; min-height:340px;">
                    <i class="fas fa-cloud-upload-alt" style="font-size:72px;"></i>
                    <span class="mt-3 fw-bold"><?php esc_html_e('Drag & drop your video here, or click to browse', 'cosy-appointments'); ?></span>
                    <p class="text-muted small mt-1"><?php echo esc_html(sprintf(__('Recommended: MP4 format (Max %d MB)', 'cosy-appointments'), $limit_mb)); ?></p>
                </div>

                <input type="file" id="video-upload-<?php echo esc_attr($user_id); ?>" class="video-upload" name="video_upload" accept="video/*" hidden>

                <div id="video-upload-preview-<?php echo esc_attr($user_id); ?>" class="video-upload-preview mt-4" style="display:none; position:relative;">
                    <video controls width="100%">
                        <source src="" type="video/mp4">
                    </video>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary custom-btn"><?php esc_html_e('Save Video', 'cosy-appointments'); ?></button>
                </div>
            </form>

        <?php elseif (!empty($introduction_video_url)) : ?>
            <!-- Approved Video Player + Replace Option -->
            <div class="d-flex justify-content-end align-items-center mb-3 flex-wrap gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary" id="btn-toggle-replace-video" onclick="jQuery('#replace-video-form-container').slideToggle();" style="border-radius: 8px; font-weight: 600;">
                    <i class="fas fa-sync-alt me-1"></i> <?php esc_html_e('Upload Replacement Video', 'cosy-appointments'); ?>
                </button>
            </div>

            <div id="existing-video-<?php echo esc_attr($user_id); ?>" class="mb-4" style="position:relative;">
                <video controls width="100%">
                    <source src="<?php echo esc_url($introduction_video_url); ?>" type="video/mp4">
                </video>
                <button id="remove-video-<?php echo esc_attr($user_id); ?>"
                    data-action="delete_video"
                    type="button"
                    class="btn remove-video"
                    data-id="<?php echo esc_attr($user_id); ?>"
                    style="position:absolute; top:15px; right:15px;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Collapsible replacement upload form -->
            <div id="replace-video-form-container" style="display:none;" class="p-4 border rounded-4 bg-light mb-4">
                <h5 class="fw-bold mb-1" style="color: #a44390;"><i class="fas fa-upload me-2"></i><?php esc_html_e('Upload Replacement Video', 'cosy-appointments'); ?></h5>
                <p class="small text-muted mb-3"><?php esc_html_e('Your current video will remain live on your profile until the new video is reviewed and approved by the administrator.', 'cosy-appointments'); ?></p>

                <form id="video-upload-form-<?php echo esc_attr($user_id); ?>"
                    class="cosy_form_video video-upload-form"
                    data-action="cosy_provider_video"
                    method="post"
                    enctype="multipart/form-data">

                    <div class="cosy-message"></div>

                    <div id="video-dropzone-<?php echo esc_attr($user_id); ?>"
                        class="video-dropzone d-flex align-items-center justify-content-center flex-column p-5"
                        style="cursor:pointer; min-height:240px; background:#fff; border:2px dashed #cbd5e1; border-radius:12px;">
                        <i class="fas fa-cloud-upload-alt" style="font-size:54px; color:#a44390;"></i>
                        <span class="mt-3 fw-bold"><?php esc_html_e('Drag & drop your new video here, or click to browse', 'cosy-appointments'); ?></span>
                        <p class="text-muted small mt-1"><?php echo esc_html(sprintf(__('Recommended: MP4 format (Max %d MB)', 'cosy-appointments'), $limit_mb)); ?></p>
                    </div>

                    <input type="file" id="video-upload-<?php echo esc_attr($user_id); ?>" class="video-upload" name="video_upload" accept="video/*" hidden>

                    <div id="video-upload-preview-<?php echo esc_attr($user_id); ?>" class="video-upload-preview mt-4" style="display:none; position:relative;">
                        <video controls width="100%">
                            <source src="" type="video/mp4">
                        </video>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary custom-btn"><?php esc_html_e('Submit for Review', 'cosy-appointments'); ?></button>
                    </div>
                </form>
            </div>

        <?php else : ?>
            <!-- Default upload form -->
            <form id="video-upload-form-<?php echo esc_attr($user_id); ?>"
                class="cosy_form_video video-upload-form"
                data-action="cosy_provider_video"
                method="post"
                enctype="multipart/form-data">

                <div class="cosy-message"></div>

                <div id="video-dropzone-<?php echo esc_attr($user_id); ?>"
                    class="video-dropzone d-flex align-items-center justify-content-center flex-column p-5"
                    style="cursor:pointer; min-height:340px;">
                    <i class="fas fa-cloud-upload-alt" style="font-size:72px;"></i>
                    <span class="mt-3 fw-bold"><?php esc_html_e('Drag & drop your video here, or click to browse', 'cosy-appointments'); ?></span>
                    <p class="text-muted small mt-1"><?php echo esc_html(sprintf(__('Recommended: MP4 format (Max %d MB)', 'cosy-appointments'), $limit_mb)); ?></p>
                </div>

                <input type="file" id="video-upload-<?php echo esc_attr($user_id); ?>" class="video-upload" name="video_upload" accept="video/*" hidden>

                <div id="video-upload-preview-<?php echo esc_attr($user_id); ?>" class="video-upload-preview mt-4" style="display:none; position:relative;">
                    <video controls width="100%">
                        <source src="" type="video/mp4">
                    </video>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary custom-btn"><?php esc_html_e('Save Video', 'cosy-appointments'); ?></button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>