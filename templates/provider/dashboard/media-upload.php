<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <h3 class="text-danger">🎥 Upload Introduction Video</h3>

        <?php
        $user_id = get_current_user_id();
        $data = $this->get_provider_data($user_id);

        $video_status = $data['video_status'];
        $introduction_video_url = $data['introduction_video'];
        ?>

        <?php if ($video_status === 'pending') : ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert"> Your video is currently under review. You cannot upload a new video until the current one is reviewed. <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> </div>

        <?php elseif ($video_status === 'rejected') : ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Your video is not approved by admin. Please upload a new video.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <!-- Show upload form again -->
            <div class="cosy-message"></div>
            <form id="video-upload-form-<?php echo esc_attr($user_id); ?>"
                class="cosy_form_video video-upload-form"
                data-action="cosy_provider_video"
                method="post"
                enctype="multipart/form-data">

                <div class="cosy-message"></div>

                <!-- DROPZONE -->
                <div id="video-dropzone-<?php echo esc_attr($user_id); ?>"
                    class="video-dropzone border border-2 border-danger rounded
                            d-flex align-items-center justify-content-center
                            flex-column p-5"
                    style="cursor:pointer; min-height:200px; background:#fffaf9;">
                    <i class="bi bi-cloud-arrow-up text-danger" style="font-size:48px;"></i>
                    <span class="mt-2 text-danger fw-bold">Click to upload video</span>
                </div>

                <!-- FILE INPUT -->
                <input type="file"
                    id="video-upload-<?php echo esc_attr($user_id); ?>"
                    class="video-upload"
                    name="video_upload"
                    accept="video/*"
                    hidden>

                <!-- Preview -->
                <div id="video-upload-preview-<?php echo esc_attr($user_id); ?>"
                    class="video-upload-preview mt-3"
                    style="display:none; position:relative;">
                    <video controls width="100%" class="rounded shadow-sm">
                        <source src="" type="video/mp4">
                    </video>
                </div>

                <!-- Submit Button -->
                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-primary">Save Video</button>
                </div>
            </form>

        <?php elseif (!empty($introduction_video_url)) : ?>
            <!-- ✅ Agar video already hai to show karo -->
            <div id="existing-video-<?php echo esc_attr($user_id); ?>" class="mt-3" style="position:relative;">
                <video controls width="100%" class="rounded shadow-sm">
                    <source src="<?php echo esc_url($introduction_video_url); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <!-- Remove Icon -->
                <button id="remove-video-<?php echo esc_attr($user_id); ?>"
                    data-action="delete_video"
                    type="button"
                    class="btn btn-sm btn-danger remove-video"
                    data-id="<?php echo esc_attr($user_id); ?>"
                    style="position:absolute; top:10px; right:10px;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

        <?php else : ?>
            <!-- Default upload form -->
            <div class="cosy-message"></div>
            <form id="video-upload-form-<?php echo esc_attr($user_id); ?>"
                class="cosy_form_video video-upload-form"
                data-action="cosy_provider_video"
                method="post"
                enctype="multipart/form-data">

                <div class="cosy-message"></div>

                <!-- DROPZONE -->
                <div id="video-dropzone-<?php echo esc_attr($user_id); ?>"
                    class="video-dropzone border border-2 border-danger rounded
                            d-flex align-items-center justify-content-center
                            flex-column p-5"
                    style="cursor:pointer; min-height:200px; background:#fffaf9;">
                    <i class="bi bi-cloud-arrow-up text-danger" style="font-size:48px;"></i>
                    <span class="mt-2 text-danger fw-bold">Click to upload video</span>
                </div>

                <!-- FILE INPUT -->
                <input type="file"
                    id="video-upload-<?php echo esc_attr($user_id); ?>"
                    class="video-upload"
                    name="video_upload"
                    accept="video/*"
                    hidden>

                <!-- Preview -->
                <div id="video-upload-preview-<?php echo esc_attr($user_id); ?>"
                    class="video-upload-preview mt-3"
                    style="display:none; position:relative;">
                    <video controls width="100%" class="rounded shadow-sm">
                        <source src="" type="video/mp4">
                    </video>
                </div>

                <!-- Submit Button -->
                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-primary">Save Video</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>