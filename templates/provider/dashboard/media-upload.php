<div class="card cosy-video-card mb-4">
    <div class="card-body p-0">
        <h3><i class="fas fa-video" style="color: #a44390;"></i> Introduction Video</h3>

        <?php
        $user_id = get_current_user_id();
        $data = $this->get_provider_data($user_id);

        $video_status = $data['video_status'];
        $introduction_video_url = $data['introduction_video'];
        ?>

        <?php if ($video_status === 'pending') : ?>
            <div class="alert alert-warning border-0 rounded-4 shadow-sm py-3" role="alert"> 
                <i class="fas fa-clock me-2"></i> Your video is currently under review. 
            </div>

        <?php elseif ($video_status === 'rejected') : ?>
            <div class="alert alert-danger border-0 rounded-4 shadow-sm py-3" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> Your video was not approved. Please upload a new one.
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
                    style="cursor:pointer; min-height:220px;">
                    <i class="fas fa-cloud-upload-alt" style="font-size:54px;"></i>
                    <span class="mt-3 fw-bold">Click to upload your intro video</span>
                    <p class="text-muted small mt-1">Recommended: MP4 format (Max 20MB)</p>
                </div>

                <input type="file" id="video-upload-<?php echo esc_attr($user_id); ?>" class="video-upload" name="video_upload" accept="video/*" hidden>

                <div id="video-upload-preview-<?php echo esc_attr($user_id); ?>" class="video-upload-preview mt-4" style="display:none; position:relative;">
                    <video controls width="100%">
                        <source src="" type="video/mp4">
                    </video>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary custom-btn">Save Video</button>
                </div>
            </form>

        <?php elseif (!empty($introduction_video_url)) : ?>
            <!-- ✅ Video Player -->
            <div id="existing-video-<?php echo esc_attr($user_id); ?>" class="mt-2" style="position:relative;">
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
                    style="cursor:pointer; min-height:220px;">
                    <i class="fas fa-cloud-upload-alt" style="font-size:54px;"></i>
                    <span class="mt-3 fw-bold">Click to upload your intro video</span>
                    <p class="text-muted small mt-1">Recommended: MP4 format (Max 20MB)</p>
                </div>

                <input type="file" id="video-upload-<?php echo esc_attr($user_id); ?>" class="video-upload" name="video_upload" accept="video/*" hidden>

                <div id="video-upload-preview-<?php echo esc_attr($user_id); ?>" class="video-upload-preview mt-4" style="display:none; position:relative;">
                    <video controls width="100%">
                        <source src="" type="video/mp4">
                    </video>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary custom-btn">Save Video</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>