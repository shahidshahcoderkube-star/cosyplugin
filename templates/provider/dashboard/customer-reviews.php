<?php
$current_provider_id = get_current_user_id();

// Call OOP-based common method to fetch reviews and calculate metrics
$reviews_data = $this->get_provider_reviews($current_provider_id);
$all_reviews = $reviews_data['all'];
$approved_reviews_db = $reviews_data['approved'];
$total_approved = $reviews_data['total_approved'];
$average_rating_db = $reviews_data['average_rating'];
$rating_counts = $reviews_data['rating_counts'];
?>

<div class="card cosy-reviews-card mb-4">
    <div class="card-body p-0">
        <div class="d-flex align-items-center gap-3 mb-2">
            <div class="header-icon-badge" style="background: rgba(164, 67, 144, 0.1); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-star" style="color: #a44390; font-size: 1.2rem;"></i>
            </div>
            <h3 class="mb-0">Customer Reviews</h3>
        </div>
        <p class="text-muted mb-4" style="margin-left: 58px;">See overall ratings and feedback from your customers.</p>

        <!-- Rating Summary -->
        <div class="row mb-5 align-items-center">
            <div class="col-md-4 text-center border-end">
                <div class="rating-number"><?php echo $average_rating_db > 0 ? number_format($average_rating_db, 1) : '0.0'; ?></div>
                <div class="mb-2">
                    <?php
                    $full_stars = floor($average_rating_db);
                    $half_star = ($average_rating_db - $full_stars) >= 0.5 ? true : false;
                    for ($star = 1; $star <= 5; $star++) {
                        if ($star <= $full_stars) {
                            echo '<i class="fas fa-star star-active"></i>';
                        } elseif ($star == $full_stars + 1 && $half_star) {
                            echo '<i class="fas fa-star-half-alt star-active"></i>';
                        } else {
                            echo '<i class="far fa-star star-inactive"></i>';
                        }
                    }
                    ?>
                </div>
                <p class="text-muted small fw-bold mb-0">Average Rating</p>
                <p class="text-muted small">Based on <?php echo $total_approved; ?> reviews</p>
            </div>
            <div class="col-md-8 ps-md-5">
                <!-- Rating Distribution -->
                <?php for ($i = 5; $i >= 1; $i--):
                    $percent = $total_approved > 0 ? round(($rating_counts[$i] / $total_approved) * 100) : 0;
                ?>
                    <div class="d-flex align-items-center mb-3">
                        <span class="text-muted small fw-bold" style="width: 25px;"><?php echo $i; ?></span>
                        <i class="fas fa-star star-active small me-3"></i>
                        <div class="progress flex-grow-1">
                            <div class="progress-bar" style="width: <?php echo $percent; ?>%"></div>
                        </div>
                        <span class="ms-3 text-muted small fw-bold" style="width: 30px; text-align: right;"><?php echo $rating_counts[$i]; ?></span>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Recent Reviews -->
        <h5 class="fw-bold mb-3 border-bottom pb-2" style="font-family: 'Poppins', sans-serif; color: #1e293b;">Feedback List</h5>
        <div class="reviews-list">
            <?php if (!empty($all_reviews)): ?>
                <?php foreach ($all_reviews as $r):
                    $is_pending = ($r['status'] === 'pending');
                ?>
                    <div class="review-item border-start border-4 <?php echo $is_pending ? 'border-start-warning' : 'border-start-success'; ?>" id="cosy-review-<?php echo $r['id']; ?>" style="border-left-color: <?php echo $is_pending ? '#ffb800' : '#22c55e'; ?> !important;">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-0 fw-bold d-flex align-items-center">
                                    <?php echo esc_html($r['customer_name']); ?>
                                    <?php if ($is_pending): ?>
                                        <span class="badge bg-warning text-dark ms-2" style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase;"><i class="fas fa-clock me-1"></i> Pending Approval</span>
                                    <?php else: ?>
                                        <span class="badge bg-success text-white ms-2" style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase;"><i class="fas fa-check-circle me-1"></i> Approved</span>
                                    <?php endif; ?>
                                </h6>
                                <small class="text-muted"><?php echo date('d M Y - h:i A', strtotime($r['created_at'])); ?></small>
                            </div>
                            <div class="text-warning small">
                                <?php for ($star = 1; $star <= 5; $star++): ?>
                                    <i class="<?php echo ($star <= $r['rating']) ? 'fas fa-star' : 'far fa-star star-inactive'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p class="mb-3 text-dark small mt-2">"<?php echo esc_html($r['review']); ?>"</p>

                        <!-- Moderation Controls -->
                        <div class="d-flex gap-2">
                            <?php if ($is_pending): ?>
                                <button class="btn btn-sm btn-success approve-review-btn" data-id="<?php echo $r['id']; ?>" style="border-radius: 8px; font-weight: 600; font-size: 0.8rem; background-color: #22c55e; border-color: #22c55e;">
                                    <i class="fas fa-check me-1"></i> Approve
                                </button>
                                <button class="btn btn-sm btn-danger delete-review-btn" data-id="<?php echo $r['id']; ?>" style="border-radius: 8px; font-weight: 600; font-size: 0.8rem; background-color: #ef4444; border-color: #ef4444;">
                                    <i class="fas fa-times me-1"></i> Reject
                                </button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-outline-danger delete-review-btn" data-id="<?php echo $r['id']; ?>" style="border-radius: 8px; font-weight: 600; font-size: 0.8rem;">
                                    <i class="fas fa-trash-alt me-1"></i> Delete
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-5 rounded-4" style="background: #f8fafc; border: 1.5px dashed #cbd5e1;">
                    <i class="far fa-comments text-muted mb-3" style="font-size: 2.5rem;"></i>
                    <p class="text-muted mb-0">No customer reviews found for your profile yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>