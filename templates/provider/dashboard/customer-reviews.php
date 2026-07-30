<?php
$current_provider_id = get_current_user_id();

// Fetch reviews data
$reviews_data = $this->get_provider_reviews($current_provider_id);
$all_reviews = $reviews_data['all'];
$approved_reviews_db = $reviews_data['approved'];
$total_approved = $reviews_data['total_approved'];
$average_rating_db = $reviews_data['average_rating'];
$rating_counts = $reviews_data['rating_counts'];

// Fetch audit alerts if Admin deleted any review
$audit_alerts = get_user_meta($current_provider_id, 'cosy_review_audit_alerts', true) ?: [];
?>

<div class="card cosy-reviews-card mb-4 border-0 shadow-sm" style="border-radius: 16px;">
    <div class="card-body p-4">
        <div class="d-flex align-items-center gap-3 mb-2">
            <div class="header-icon-badge" style="background: rgba(164, 67, 144, 0.1); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-star" style="color: #a44390; font-size: 1.2rem;"></i>
            </div>
            <h3 class="mb-0" style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 1.3rem; color: #1e293b;"><?php esc_html_e('Parent Reviews & Ratings', 'cosy-appointments'); ?></h3>
        </div>
        <p class="text-muted mb-4" style="margin-left: 58px; font-size: 0.9rem;"><?php esc_html_e('View ratings and feedback from parents. Only Admin can moderate or delete reviews. You can post a public response under approved reviews.', 'cosy-appointments'); ?></p>

        <!-- Compact 1-Line Audit Trail Notice Banner -->
        <?php if (!empty($audit_alerts)) : 
            $reversed_alerts = array_reverse($audit_alerts);
            $latest_alert = reset($reversed_alerts);
            $total_alerts_count = count($audit_alerts);
        ?>
            <div class="cosy-audit-notice-banner mb-4 p-2 px-3 rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-2 shadow-sm" style="background: #eff6ff; border: 1px solid #bfdbfe; font-size: 0.85rem;">
                <div class="d-flex align-items-center gap-2 overflow-hidden" style="max-width: 82%;">
                    <i class="fas fa-info-circle flex-shrink-0" style="color: #3b82f6; font-size: 1rem;"></i>
                    <span class="text-truncate" style="color: #1e40af;">
                        <strong><?php esc_html_e('Audit Trail Notice:', 'cosy-appointments'); ?></strong>
                        <?php echo esc_html(preg_replace('/Review #\d+\s*/i', 'a review ', $latest_alert['message'])); ?>
                    </span>
                    <?php if ($total_alerts_count > 1) : ?>
                        <span class="badge bg-primary rounded-pill flex-shrink-0" style="font-size: 0.68rem; padding: 2px 7px;">+<?php echo ($total_alerts_count - 1); ?> <?php esc_html_e('more', 'cosy-appointments'); ?></span>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none btn-clear-audit-alerts flex-shrink-0" style="color: #3b82f6; font-size: 0.78rem; font-weight: 600;">
                    <i class="fas fa-check-circle me-1"></i><?php esc_html_e('Clear Notices', 'cosy-appointments'); ?>
                </button>
            </div>
        <?php endif; ?>

        <!-- Rating Summary -->
        <div class="row mb-5 align-items-center p-3" style="background: #fdf5fc; border-radius: 16px; border: 1px solid rgba(164, 67, 144, 0.12);">
            <div class="col-md-4 text-center border-end">
                <div class="rating-number" style="font-size: 2.8rem; font-weight: 800; color: #6d2e67; font-family: 'Outfit', sans-serif; line-height: 1;">
                    <?php echo esc_html($average_rating_db > 0 ? number_format($average_rating_db, 1) : '0.0'); ?>
                </div>
                <div class="my-2" style="color: #f59e0b; font-size: 1.1rem;">
                    <?php
                    $full_stars = floor($average_rating_db);
                    $half_star = ($average_rating_db - $full_stars) >= 0.5;
                    for ($star = 1; $star <= 5; $star++) {
                        if ($star <= $full_stars) {
                            echo '<i class="fas fa-star me-1"></i>';
                        } elseif ($star == $full_stars + 1 && $half_star) {
                            echo '<i class="fas fa-star-half-alt me-1"></i>';
                        } else {
                            echo '<i class="far fa-star me-1" style="color:#cbd5e1;"></i>';
                        }
                    }
                    ?>
                </div>
                <p class="text-muted small fw-bold mb-0"><?php esc_html_e('Average Rating', 'cosy-appointments'); ?></p>
                <p class="text-muted small mb-0"><?php printf(esc_html__('Based on %d approved reviews', 'cosy-appointments'), $total_approved); ?></p>
            </div>
            <div class="col-md-8 ps-md-4">
                <!-- Rating Distribution -->
                <?php for ($i = 5; $i >= 1; $i--):
                    $percent = $total_approved > 0 ? round(($rating_counts[$i] / $total_approved) * 100) : 0;
                ?>
                    <div class="d-flex align-items-center mb-2">
                        <span class="text-muted small fw-bold" style="width: 25px;"><?php echo esc_html($i); ?></span>
                        <i class="fas fa-star me-2 small" style="color: #f59e0b;"></i>
                        <div class="progress flex-grow-1" style="height: 8px; border-radius: 4px; background: #e2e8f0;">
                            <div class="progress-bar" style="width: <?php echo esc_attr($percent); ?>%; background: #a44390; border-radius: 4px;"></div>
                        </div>
                        <span class="ms-3 text-muted small fw-bold" style="width: 30px; text-align: right;"><?php echo esc_html($rating_counts[$i]); ?></span>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Recent Reviews List -->
        <h5 class="fw-bold mb-4 border-bottom pb-2" style="font-family: 'Poppins', sans-serif; color: #1e293b;"><?php esc_html_e('All Reviews & Public Responses', 'cosy-appointments'); ?></h5>
        <div class="reviews-list">
            <?php if (!empty($all_reviews)): ?>
                <?php foreach ($all_reviews as $r):
                    $is_pending = ($r['status'] === 'pending');
                    $is_rejected = ($r['status'] === 'rejected');
                ?>
                    <div class="card mb-4 border-0 shadow-sm" id="cosy-review-<?php echo esc_attr($r['id']); ?>" style="background: #ffffff; border-radius: 16px; border: 1px solid #f1f5f9;">
                        <div class="card-body p-4">
                            <!-- Customer Review Header & Content -->
                            <div class="d-flex gap-3 align-items-start">
                                <div class="cosy-review-avatar rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 fw-bold text-uppercase" style="width: 42px; height: 42px; background: #fdf5fc; color: #a44390; border: 1.5px solid rgba(164, 67, 144, 0.2); font-size: 1.1rem;">
                                    <?php echo esc_html(substr($r['customer_name'], 0, 1)); ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <h6 class="mb-0 fw-bold" style="font-size: 0.95rem; color: #0f172a;">
                                                <?php echo esc_html($r['customer_name']); ?>
                                            </h6>
                                            <?php if ($is_pending): ?>
                                                <span class="badge bg-warning text-dark" style="font-size: 0.68rem; font-weight: 700; border-radius: 12px; padding: 4px 9px;"><i class="fas fa-clock me-1"></i> <?php esc_html_e('Pending Admin Approval', 'cosy-appointments'); ?></span>
                                            <?php elseif ($is_rejected): ?>
                                                <span class="badge bg-danger text-white" style="font-size: 0.68rem; font-weight: 700; border-radius: 12px; padding: 4px 9px;"><i class="fas fa-times-circle me-1"></i> <?php esc_html_e('Rejected', 'cosy-appointments'); ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-success text-white" style="font-size: 0.68rem; font-weight: 700; border-radius: 12px; padding: 4px 9px;"><i class="fas fa-check-circle me-1"></i> <?php esc_html_e('Approved & Live', 'cosy-appointments'); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="color: #f59e0b; font-size: 0.85rem;">
                                            <?php for ($star = 1; $star <= 5; $star++): ?>
                                                <i class="<?php echo esc_attr(($star <= $r['rating']) ? 'fas fa-star' : 'far fa-star'); ?>" style="<?php echo ($star > $r['rating']) ? 'color:#cbd5e1;' : ''; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mb-2" style="font-size: 0.75rem;"><?php echo date('d M Y - h:i A', strtotime($r['created_at'])); ?></small>
                                    
                                    <p class="mb-0 text-dark small" style="font-size: 0.9rem; line-height: 1.5; color: #334155;">
                                        <?php echo esc_html($r['review']); ?>
                                    </p>

                                    <!-- 3-Level Threaded Review & Reply Section -->
                                    <?php if (!$is_pending && !$is_rejected) : 
                                        $replies = $r['replies'] ?? [];
                                        $has_level1 = false;
                                        $level1_text = '';
                                        $level1_date = '';
                                        $has_level2 = false;
                                        $level2_text = '';
                                        $level2_sender = '';
                                        $level2_date = '';
                                        $has_level3 = false;
                                        $level3_text = '';
                                        $level3_date = '';

                                        foreach ($replies as $rep) {
                                            if ($rep['reply_level'] == 1) {
                                                $has_level1 = true;
                                                $level1_text = $rep['reply_text'];
                                                $level1_date = $rep['created_at'];
                                            } elseif ($rep['reply_level'] == 2) {
                                                $has_level2 = true;
                                                $level2_text = $rep['reply_text'];
                                                $level2_sender = $rep['sender_name'];
                                                $level2_date = $rep['created_at'];
                                            } elseif ($rep['reply_level'] == 3) {
                                                $has_level3 = true;
                                                $level3_text = $rep['reply_text'];
                                                $level3_date = $rep['created_at'];
                                            }
                                        }
                                    ?>
                                        <div class="review-thread-container cosy-review-thread-wrap d-flex flex-column gap-2">
                                            <!-- Level 1: Initial Provider Reply -->
                                            <?php if ($has_level1) : ?>
                                                <div class="provider-reply-box cosy-thread-provider-box">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <strong class="cosy-thread-sender-name">
                                                            <i class="fas fa-reply me-1 cosy-thread-sender-icon-provider"></i> <?php esc_html_e('Your Public Reply:', 'cosy-appointments'); ?>
                                                        </strong>
                                                        <?php if (!$has_level2) : ?>
                                                            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none btn-edit-reply" data-id="<?php echo esc_attr($r['id']); ?>" style="color: #a44390; font-size: 0.78rem; font-weight: 600;">
                                                                <i class="fas fa-edit me-1"></i> <?php esc_html_e('Edit Reply', 'cosy-appointments'); ?>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                    <p class="cosy-thread-body-provider">
                                                        <?php echo esc_html($level1_text); ?>
                                                    </p>
                                                    <small class="cosy-thread-date d-block mt-1">
                                                        <?php echo date('d M Y - h:i A', strtotime($level1_date)); ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Level 2: Customer Follow-up -->
                                            <?php if ($has_level2) : ?>
                                                <div class="customer-followup-box cosy-thread-customer-box">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <strong class="cosy-thread-sender-name">
                                                            <i class="fas fa-comment-dots me-1 cosy-thread-sender-icon-customer"></i> <?php echo esc_html($level2_sender); ?>
                                                        </strong>
                                                    </div>
                                                    <p class="cosy-thread-body-customer">
                                                        <?php echo esc_html($level2_text); ?>
                                                    </p>
                                                    <small class="cosy-thread-date d-block mt-1">
                                                        <?php echo date('d M Y - h:i A', strtotime($level2_date)); ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Level 3: Provider Final Closing Response -->
                                            <?php if ($has_level3) : ?>
                                                <div class="provider-closing-box cosy-thread-provider-box">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <strong class="cosy-thread-sender-name">
                                                            <i class="fas fa-check-circle me-1 cosy-thread-sender-icon-provider"></i> <?php esc_html_e('Your Final Response:', 'cosy-appointments'); ?>
                                                        </strong>
                                                    </div>
                                                    <p class="cosy-thread-body-provider">
                                                        <?php echo esc_html($level3_text); ?>
                                                    </p>
                                                    <small class="cosy-thread-date d-block mt-1">
                                                        <?php echo date('d M Y - h:i A', strtotime($level3_date)); ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Action Button / Form Control for Provider -->
                                            <div class="mt-2">
                                                <?php if (!$has_level1) : ?>
                                                    <button type="button" class="btn btn-sm btn-toggle-reply-form text-white" data-id="<?php echo esc_attr($r['id']); ?>" style="background: #a44390; border: 1px solid #a44390; color: #ffffff; border-radius: 12px; font-weight: 600; font-size: 0.8rem; padding: 5px 14px;">
                                                        <i class="fas fa-reply me-1"></i> <?php esc_html_e('Post a Public Response', 'cosy-appointments'); ?>
                                                    </button>
                                                <?php elseif ($has_level2 && !$has_level3) : ?>
                                                    <button type="button" class="btn btn-sm btn-toggle-reply-form text-white" data-id="<?php echo esc_attr($r['id']); ?>" style="background: #6d2e67; border: 1px solid #6d2e67; color: #ffffff; border-radius: 12px; font-weight: 600; font-size: 0.8rem; padding: 5px 14px;">
                                                        <i class="fas fa-comment-dots me-1"></i> <?php esc_html_e('Post Final Response (Level 3)', 'cosy-appointments'); ?>
                                                    </button>
                                                <?php elseif ($has_level3) : ?>
                                                    <span class="badge bg-secondary p-2" style="font-size: 0.72rem; border-radius: 10px;">
                                                        <i class="fas fa-lock me-1"></i> <?php esc_html_e('Thread Closed (3-Level Cap Reached)', 'cosy-appointments'); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Hidden / Inline Reply Form -->
                                            <?php if (!$has_level3) : ?>
                                                <div class="reply-form-wrap mt-3" id="reply-form-wrap-<?php echo esc_attr($r['id']); ?>" style="display: none;">
                                                    <form class="cosy-provider-reply-form" data-id="<?php echo esc_attr($r['id']); ?>">
                                                        <div class="mb-2">
                                                            <textarea class="form-control form-control-sm reply-textarea" rows="3" placeholder="<?php echo esc_attr(($has_level2) ? __('Write your final closing response to this parent review thread...', 'cosy-appointments') : __('Write a polite public response to this parent review...', 'cosy-appointments')); ?>" style="border-radius: 10px; font-size: 0.9rem;" required><?php echo esc_textarea(($has_level1 && !$has_level2) ? $level1_text : ''); ?></textarea>
                                                        </div>
                                                        <div class="d-flex gap-2">
                                                            <button type="submit" class="btn btn-sm btn-filled px-3" style="background: #a44390; color: #fff; border-radius: 8px; font-weight: 600; font-size: 0.85rem;">
                                                                <?php echo esc_html(($has_level2) ? __('Submit Final Response', 'cosy-appointments') : __('Submit Response', 'cosy-appointments')); ?>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-light btn-cancel-reply" data-id="<?php echo esc_attr($r['id']); ?>" style="border-radius: 8px; font-size: 0.85rem;">
                                                                <?php esc_html_e('Cancel', 'cosy-appointments'); ?>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-5 rounded-4" style="background: #f8fafc; border: 1.5px dashed #cbd5e1;">
                    <i class="far fa-comments text-muted mb-3" style="font-size: 2.5rem;"></i>
                    <p class="text-muted mb-0"><?php esc_html_e('No parent reviews found for your profile yet.', 'cosy-appointments'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.btn-toggle-reply-form, .btn-edit-reply').on('click', function() {
        var reviewId = $(this).data('id');
        $('#reply-form-wrap-' + reviewId).slideToggle(200);
    });

    $('.btn-cancel-reply').on('click', function() {
        var reviewId = $(this).data('id');
        $('#reply-form-wrap-' + reviewId).slideUp(200);
    });

    $('.cosy-provider-reply-form').on('submit', function(e) {
        e.preventDefault();
        var reviewId = $(this).data('id');
        var form = $(this);
        var replyText = form.find('.reply-textarea').val().trim();
        var submitBtn = form.find('button[type="submit"]');

        if (!replyText) return;

        submitBtn.prop('disabled', true).css({ 'background-color': '#a44390', 'color': '#ffffff', 'opacity': '0.95' }).html('<i class="fas fa-spinner fa-spin me-1" style="color: #ffffff !important;"></i> <span style="color: #ffffff !important;">Posting...</span>');

        var ajaxUrl = (typeof cosy_ajax !== 'undefined' && cosy_ajax.ajax_url) ? cosy_ajax.ajax_url : '/wp-admin/admin-ajax.php';

        $.post(ajaxUrl, {
            action: 'cosy_provider_reply_review',
            review_id: reviewId,
            reply_text: replyText
        }, function(res) {
            if (res.success) {
                location.reload();
            } else {
                alert(res.data.message || 'Error posting reply.');
                submitBtn.prop('disabled', false).css({ 'opacity': '1' }).html('Submit Response');
            }
        }).fail(function() {
            alert('Server error posting reply. Please try again.');
            submitBtn.prop('disabled', false).css({ 'opacity': '1' }).html('Submit Response');
        });
    });

    $(document).on('click', '.btn-clear-audit-alerts', function(e) {
        e.preventDefault();
        $('.cosy-audit-notice-banner').remove();
        var ajaxUrl = (typeof cosy_ajax !== 'undefined' && cosy_ajax.ajax_url) ? cosy_ajax.ajax_url : '/wp-admin/admin-ajax.php';
        $.post(ajaxUrl, { action: 'cosy_dismiss_audit_alerts' });
    });
});
</script>