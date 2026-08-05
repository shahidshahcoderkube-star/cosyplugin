<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $wpdb;
$token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';
$token_data = null;
$error_message = '';

if (!empty($token)) {
    $tokens_table = $wpdb->prefix . 'cosy_review_tokens';
    $token_data = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $tokens_table WHERE token = %s LIMIT 1",
        $token
    ));

    if (!$token_data) {
        $error_message = __('Invalid review link. Please check your email for the correct link.', 'cosy-appointments');
    } elseif (intval($token_data->used) === 1) {
        $error_message = __('This review link has already been used. Thank you for submitting your feedback!', 'cosy-appointments');
    }
} else {
    $error_message = __('No review token provided. Please use the review link provided in your email.', 'cosy-appointments');
}

$provider_name = 'Parent';
$service_name  = 'Parent Conversation';

if ($token_data && empty($error_message)) {
    $provider_id   = intval($token_data->provider_id);
    $order_id      = intval($token_data->order_id);
    $provider_user = get_userdata($provider_id);
    if ($provider_user) {
        $provider_name = $provider_user->first_name ?: $provider_user->display_name;
    }
    $meta_service = get_post_meta($order_id, 'cosy_service_name', true);
    if (!empty($meta_service)) {
        $service_name = $meta_service;
    }
}
?>

<div class="cosy-leave-review-root container py-5" style="max-width: 650px;">
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="card-header border-0 text-white text-center py-4 px-4" style="background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%);">
            <h3 class="fw-bold mb-1 h4 text-white"><i class="fas fa-star text-warning me-2"></i><?php esc_html_e('Leave a Review', 'cosy-appointments'); ?></h3>
            <p class="small text-white-50 mb-0"><?php esc_html_e('Share your feedback on your conversation', 'cosy-appointments'); ?></p>
        </div>

        <div class="card-body p-4 p-md-5">
            <?php if (!empty($error_message)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-circle text-muted mb-3" style="font-size: 3.5rem; color: #a44390 !important;"></i>
                    <h4 class="fw-bold h5 text-dark mb-2"><?php esc_html_e('Notice', 'cosy-appointments'); ?></h4>
                    <p class="text-muted small mb-4" style="line-height: 1.6;"><?php echo esc_html($error_message); ?></p>
                    <a href="<?php echo esc_url(site_url('/')); ?>" class="btn text-white fw-bold px-4 py-2" style="background: #a44390; border-radius: 50px; text-decoration: none;">
                        <?php esc_html_e('Return to Home', 'cosy-appointments'); ?>
                    </a>
                </div>
            <?php else: ?>
                <div class="session-info-card p-3 rounded-3 mb-4" style="background: #fdf5fc; border: 1.5px solid rgba(164, 67, 144, 0.2);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle" style="width: 48px; height: 48px; background: #a44390; color: #fff; font-weight: 700; font-size: 1.2rem;">
                            <?php echo esc_html(substr($provider_name, 0, 1)); ?>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark"><?php echo esc_html($provider_name); ?></h6>
                            <small class="text-muted"><i class="fas fa-tags me-1" style="color: #a44390;"></i><?php echo esc_html($service_name); ?> (Order #<?php echo esc_html($token_data->order_id); ?>)</small>
                        </div>
                    </div>
                </div>

                <form id="cosyTokenReviewForm">
                    <input type="hidden" name="token" value="<?php echo esc_attr($token); ?>">
                    <input type="hidden" name="action" value="cosy_submit_token_review">
                    <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('cosy_review_nonce')); ?>">

                    <!-- 1 to 10 Rating Selector -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark d-block mb-2">
                            <?php esc_html_e('Your Rating (out of 10)', 'cosy-appointments'); ?> <span class="text-danger">*</span>
                        </label>
                        <div class="rating-scale-container d-flex flex-wrap gap-2 justify-content-between">
                            <?php for ($num = 1; $num <= 10; $num++): ?>
                                <button type="button" class="btn btn-outline-secondary rating-score-btn flex-fill" data-score="<?php echo $num; ?>" style="min-width: 42px; border-radius: 8px; font-weight: 600; transition: all 0.2s ease;">
                                    <?php echo $num; ?>
                                </button>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="selectedScore" value="0" required>
                        <small class="text-muted d-block mt-2" id="ratingHelpText" style="font-size: 0.8rem;"><?php esc_html_e('Select a score from 1 (lowest) to 10 (highest)', 'cosy-appointments'); ?></small>
                    </div>

                    <!-- Review Textarea -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark d-block mb-2">
                            <?php esc_html_e('Your Review', 'cosy-appointments'); ?> <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control p-3 border-1" name="review" id="reviewCommentText" rows="4" placeholder="<?php esc_attr_e('Share how your conversation went and how it helped you...', 'cosy-appointments'); ?>" style="border-radius: 12px; border-color: #cbd5e1; font-size: 0.95rem;" required></textarea>
                    </div>

                    <!-- Disclaimer -->
                    <p class="fst-italic text-muted small mb-4" style="font-size: 0.82rem; line-height: 1.5;">
                        <i class="fas fa-info-circle me-1" style="color: #a44390;"></i>
                        <?php esc_html_e('Reviews are only accepted from customers who have completed a conversation. Your review will be reviewed by admin before appearing publicly.', 'cosy-appointments'); ?>
                    </p>

                    <!-- Alert Box -->
                    <div id="reviewResponseAlert" class="alert d-none mb-3" style="border-radius: 10px;"></div>

                    <!-- Submit Button -->
                    <button type="submit" id="btnSubmitTokenReview" class="btn w-100 py-3 fw-bold text-white shadow-sm" style="background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); border-radius: 50px; font-size: 1rem;">
                        <i class="fas fa-paper-plane me-2"></i><?php esc_html_e('Submit Review', 'cosy-appointments'); ?>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function ($) {
    // Rating 1-10 Button selection logic
    $(document).on('click', '.rating-score-btn', function (e) {
        e.preventDefault();
        $('.rating-score-btn').removeClass('active btn-primary text-white').addClass('btn-outline-secondary').css({
            'background': '',
            'border-color': '',
            'color': ''
        });

        $(this).removeClass('btn-outline-secondary').addClass('active').css({
            'background': '#a44390',
            'border-color': '#a44390',
            'color': '#ffffff'
        });

        const score = $(this).data('score');
        $('#selectedScore').val(score);
        $('#ratingHelpText').html('<span class="fw-bold" style="color: #a44390;">Selected Rating: ' + score + ' / 10</span>');
    });

    // Handle Form Submit via AJAX
    $('#cosyTokenReviewForm').on('submit', function (e) {
        e.preventDefault();

        const score = parseInt($('#selectedScore').val()) || 0;
        const reviewText = $.trim($('#reviewCommentText').val());
        const $alert = $('#reviewResponseAlert');
        const $btn = $('#btnSubmitTokenReview');

        if (score < 1 || score > 10) {
            $alert.removeClass('d-none alert-success').addClass('alert-danger').html('Please select a rating score between 1 and 10.').slideDown();
            return;
        }

        if (reviewText.length < 5) {
            $alert.removeClass('d-none alert-success').addClass('alert-danger').html('Please write a brief review comment before submitting.').slideDown();
            return;
        }

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Submitting...');
        $alert.addClass('d-none');

        const formData = $(this).serialize();

        $.ajax({
            url: window.ajaxUrl || '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: formData,
            success: function (res) {
                if (res.success) {
                    $alert.removeClass('d-none alert-danger').addClass('alert-success').html(res.data.message || 'Thank you! Your review has been submitted successfully.').slideDown();
                    $('#cosyTokenReviewForm').find('input, textarea, button').prop('disabled', true);
                    setTimeout(function () {
                        window.location.href = res.data.redirect_url || '/';
                    }, 3000);
                } else {
                    $alert.removeClass('d-none alert-success').addClass('alert-danger').html(res.data.message || 'Failed to submit review. Please try again.').slideDown();
                    $btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Submit Review');
                }
            },
            error: function () {
                $alert.removeClass('d-none alert-success').addClass('alert-danger').html('An unexpected error occurred. Please try again.').slideDown();
                $btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Submit Review');
            }
        });
    });
});
</script>
