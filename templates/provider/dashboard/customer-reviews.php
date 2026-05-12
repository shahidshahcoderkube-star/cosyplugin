<style>
.cosy-reviews-card {
    background: #ffffff;
    border-radius: 20px !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05) !important;
    border: none !important;
    padding: 30px;
}

.cosy-reviews-card h3 {
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
    font-size: 1.5rem;
    color: #1e293b;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.cosy-reviews-card .rating-number {
    font-family: 'Poppins', sans-serif;
    font-weight: 800;
    font-size: 3.5rem;
    color: #a44390;
    line-height: 1;
}

.cosy-reviews-card .progress {
    height: 8px !important;
    border-radius: 10px !important;
    background-color: #f1f5f9 !important;
}

.cosy-reviews-card .progress-bar {
    background-color: #a44390 !important;
    border-radius: 10px !important;
}

.cosy-reviews-card .review-item {
    background: #f8fafc;
    border: 1.5px solid #f1f5f9;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
}

.cosy-reviews-card .review-item:hover {
    border-color: #a44390;
    background: #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.cosy-reviews-card .verified-badge {
    background: rgba(34, 197, 94, 0.1) !important;
    color: #22c55e !important;
    font-size: 0.65rem !important;
    padding: 4px 8px !important;
    border-radius: 6px !important;
    font-weight: 700 !important;
    text-transform: uppercase;
    margin-left: 8px;
}

.cosy-reviews-card .star-active { color: #f59e0b; }
.cosy-reviews-card .star-inactive { color: #e2e8f0; }

.cosy-reviews-card .custom-btn-outline {
    border: 1.5px solid #a44390 !important;
    color: #a44390 !important;
    border-radius: 12px !important;
    padding: 10px 30px !important;
    font-weight: 600;
    transition: all 0.3s ease;
}

.cosy-reviews-card .custom-btn-outline:hover {
    background: #a44390 !important;
    color: #fff !important;
}
</style>

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
                <div class="rating-number">4.2</div>
                <div class="mb-2">
                    <i class="fas fa-star star-active"></i>
                    <i class="fas fa-star star-active"></i>
                    <i class="fas fa-star star-active"></i>
                    <i class="fas fa-star star-active"></i>
                    <i class="fas fa-star-half-alt star-active"></i>
                </div>
                <p class="text-muted small fw-bold mb-0">Average Rating</p>
                <p class="text-muted small">Based on 120 reviews</p>
            </div>
            <div class="col-md-8 ps-md-5">
                <!-- Rating Distribution -->
                <div class="d-flex align-items-center mb-3">
                    <span class="text-muted small fw-bold" style="width: 25px;">5</span>
                    <i class="fas fa-star star-active small me-3"></i>
                    <div class="progress flex-grow-1">
                        <div class="progress-bar" style="width: 60%"></div>
                    </div>
                    <span class="ms-3 text-muted small fw-bold">72</span>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <span class="text-muted small fw-bold" style="width: 25px;">4</span>
                    <i class="fas fa-star star-active small me-3"></i>
                    <div class="progress flex-grow-1">
                        <div class="progress-bar" style="width: 25%"></div>
                    </div>
                    <span class="ms-3 text-muted small fw-bold">30</span>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <span class="text-muted small fw-bold" style="width: 25px;">3</span>
                    <i class="fas fa-star star-active small me-3"></i>
                    <div class="progress flex-grow-1">
                        <div class="progress-bar" style="width: 10%"></div>
                    </div>
                    <span class="ms-3 text-muted small fw-bold">12</span>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <span class="text-muted small fw-bold" style="width: 25px;">2</span>
                    <i class="fas fa-star star-active small me-3"></i>
                    <div class="progress flex-grow-1">
                        <div class="progress-bar" style="width: 3%"></div>
                    </div>
                    <span class="ms-3 text-muted small fw-bold">4</span>
                </div>
                <div class="d-flex align-items-center">
                    <span class="text-muted small fw-bold" style="width: 25px;">1</span>
                    <i class="fas fa-star star-active small me-3"></i>
                    <div class="progress flex-grow-1">
                        <div class="progress-bar" style="width: 2%"></div>
                    </div>
                    <span class="ms-3 text-muted small fw-bold">2</span>
                </div>
            </div>
        </div>

        <!-- Recent Reviews -->
        <div class="reviews-list">
            <div class="review-item">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="mb-0 fw-bold d-flex align-items-center">
                            Rahul Sharma 
                            <span class="badge verified-badge"><i class="fas fa-check-circle me-1"></i> Verified</span>
                        </h6>
                        <small class="text-muted">Haircut • 01 Jan 2026</small>
                    </div>
                    <div class="text-warning small">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i>
                        <i class="fas fa-star"></i><i class="fas fa-star"></i>
                        <i class="far fa-star star-inactive"></i>
                    </div>
                </div>
                <p class="mb-0 text-dark small mt-2">"Great service, very professional and friendly!"</p>
            </div>

            <div class="review-item">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="mb-0 fw-bold">Priya Patel</h6>
                        <small class="text-muted">Massage Therapy • 02 Jan 2026</small>
                    </div>
                    <div class="text-warning small">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i>
                        <i class="fas fa-star"></i><i class="far fa-star star-inactive"></i>
                        <i class="far fa-star star-inactive"></i>
                    </div>
                </div>
                <p class="mb-0 text-dark small mt-2">"Relaxing experience, could improve ambience."</p>
            </div>
        </div>

        <!-- View All Button -->
        <div class="text-center mt-4">
            <button class="btn custom-btn-outline" data-bs-toggle="modal" data-bs-target="#reviewsModal">
                View All Reviews
            </button>
        </div>
    </div>
</div>