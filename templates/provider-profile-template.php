<?php get_header(); ?>

<div class="container py-5">
    <div class="row g-4">

        <div class="col-md-6">
            <div class="bento-box">
                <span class="section-label">Provider</span>
                <div class="d-flex align-items-center gap-4 mb-4">
                    <img src="https://via.placeholder.com/150" class="profile-pic" alt="Amanda">
                    <div>
                        <h3 class="fw-800 mb-0">Amanda</h3>
                        <div class="d-flex gap-1 text-warning my-1">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="text-muted small mb-0">Early Years Expert</p>
                    </div>
                </div>
                <div class="mt-auto bg-custom-gradient p-3 rounded-4 d-flex justify-content-between align-items-center">
                    <span class="small fw-500">Service Fee</span>
                    <h4 class="mb-0 fw-800">£20<small>/hr</small></h4>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="bento-box">
                <span class="section-label">Availability</span>
                <div id="calendar"></div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="bento-box">
                <span class="section-label">About Specialist</span>
                <h5 class="fw-700 mb-3">Empowering Families</h5>
                <p class="text-muted small lh-lg">
                    I provide specialized support for ADHD/ASD families, helping you navigate systems and find nurturing solutions for your child's growth.
                </p>
                <div class="mt-auto">
                    <button class="btn-main"><i class="fas fa-calendar-check me-2"></i>Secure Booking</button>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="bento-box">
                <span class="section-label">Schedule & Feedback</span>
                <div class="working-tag d-flex justify-content-between mb-3">
                    <span>Monday - Tuesday</span>
                    <span>09:00 - 19:00</span>
                </div>

                <div class="mt-auto border-top pt-3">
                    <p class="small fw-700 mb-2">Leave a Review</p>
                    <div class="rating-input mb-2" id="star-rating">
                        <i class="fas fa-star active"></i><i class="fas fa-star active"></i><i class="fas fa-star active"></i><i class="fas fa-star active"></i><i class="fas fa-star"></i>
                    </div>
                    <div class="d-flex gap-2">
                        <input type="text" class="form-control form-control-sm border-0 bg-light rounded-3" placeholder="Write feedback...">
                        <button class="btn btn-sm text-white" style="background:#9b4593; border-radius:10px;">Send</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php get_footer(); ?>