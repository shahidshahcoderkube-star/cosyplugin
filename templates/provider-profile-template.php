<?php
get_header();
$author_slug = get_query_var('author_name');
$common = new class { use \Cosy\Appointments\Common\GlobalCommonFunctions; };
$provider_data = $common->get_provider_with_services($author_slug);
?>
<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-7">

            <div class="card border-0 shadow-sm mb-4 overflow-hidden" style="border-radius: 20px;">
                <div class="card-header border-0 py-4"
                    style="background: linear-gradient(135deg, #9b4593 0%, #6d2e67 100%); color: white;">
                    <div class="d-flex align-items-center">
                        <img src="https://via.placeholder.com/100"
                            class="rounded-circle border border-4 border-white shadow-sm" alt="Amanda">
                        <div class="ms-4">
                            <?php if (!empty($provider_data['username'])): ?>
                                <h2 class="mb-1 fw-bold">
                                    <?php echo esc_html(ucwords(strtolower($provider_data['username']))); ?>
                                </h2>
                            <?php endif ?>
                            <div class="d-flex gap-3 opacity-75 small">
                                <?php if (!empty($provider_data['gender'])): ?>
                                    <span><i class="fa-solid fa-venus me-1"></i>
                                        <?php echo esc_html(ucwords(strtolower($provider_data['gender']))); ?></span>
                                <?php endif ?>
                                <span><i class="fa-solid fa-user-group me-1"></i> Kids Specialist</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row text-center mb-4 pb-3 border-bottom">
                        <div class="col-4">
                            <div class="h5 fw-bold mb-0" style="color: #9b4593;">£20</div>
                            <small class="text-muted text-uppercase" style="font-size: 0.7rem;">Hourly Rate</small>
                        </div>
                        <div class="col-4 border-start border-end">
                            <div class="h5 fw-bold mb-0 text-warning">★★★★★</div>
                            <small class="text-muted text-uppercase" style="font-size: 0.7rem;">5.0 (12 Reviews)</small>
                        </div>
                        <div class="col-4">
                            <div class="h5 fw-bold mb-0">Middle</div>
                            <small class="text-muted text-uppercase" style="font-size: 0.7rem;">Age Group</small>
                        </div>
                    </div>

                    <div class="row text-center mb-4 pb-3 border-bottom">
                        <div class="col-4">
                            <div class="h5 fw-bold mb-0" style="color: #9b4593;">£20</div>
                            <small class="text-muted text-uppercase" style="font-size: 0.7rem;">Hourly Rate</small>
                        </div>
                        <div class="col-4 border-start border-end">
                            <div class="h5 fw-bold mb-0 text-warning">★★★★★</div>
                            <small class="text-muted text-uppercase" style="font-size: 0.7rem;">5.0 (12 Reviews)</small>
                        </div>
                        <div class="col-4">
                            <div class="h5 fw-bold mb-0">Middle</div>
                            <small class="text-muted text-uppercase" style="font-size: 0.7rem;">Age Group</small>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-3" style="color: #9b4593;">Bio</h5>
                    <p class="text-muted lh-lg">
                        As a mother of a young man who has thrived despite ADHD and ASD, and with years of experience as
                        an Early Years specialist...
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3" style="color: #9b4593;"><i class="fa-solid fa-user me-2"></i> About Me</h5>
                    <p class="text-muted lh-lg mb-0">
                        As a mother of a young man who has thrived despite ADHD and ASD, and with years of experience as
                        an Early Years specialist and teacher, I deeply understand the challenges faced by families. I
                        offer a compassionate listening ear and tailored plans to help your child succeed.
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4" style="color: #9b4593;"><i class="fa-solid fa-calendar-check me-2"></i>
                        Working Hours</h5>
                    <div class="table-responsive">
                        <table class="table table-hover border-0 mb-0">
                            <tbody class="text-secondary small">
                                <tr>
                                    <td class="border-0 fw-bold py-3 text-dark">Monday</td>
                                    <td class="border-0 text-end py-3">09:00 AM - 01:00 PM & 02:00 PM - 07:00 PM</td>
                                </tr>
                                <tr>
                                    <td class="border-0 fw-bold py-3 text-dark">Tuesday</td>
                                    <td class="border-0 text-end py-3">09:00 AM - 01:00 PM & 02:00 PM - 07:00 PM</td>
                                </tr>
                                <tr>
                                    <td class="border-0 fw-bold py-3 text-dark">Wednesday</td>
                                    <td class="border-0 text-end py-3">09:00 AM - 01:00 PM & 02:00 PM - 07:00 PM</td>
                                </tr>
                                <tr>
                                    <td class="border-0 fw-bold py-3 text-dark text-danger">Sunday</td>
                                    <td class="border-0 text-end py-3 text-danger fw-medium">Unavailable</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0" style="color: #9b4593;"><i class="fa-solid fa-comment-dots me-2"></i>
                            Reviews</h5>
                        <button class="btn btn-sm text-white px-3"
                            style="background-color: #9b4593; border-radius: 10px;" data-bs-toggle="collapse"
                            data-bs-target="#reviewForm">
                            + Add Review
                        </button>
                    </div>

                    <div class="collapse mb-4" id="reviewForm">
                        <div class="bg-light p-3 rounded-4">
                            <textarea class="form-control mb-2 border-0 shadow-sm" rows="3"
                                placeholder="Write your review..."></textarea>
                            <button class="btn btn-dark btn-sm px-4 rounded-pill">Submit Review</button>
                        </div>
                    </div>

                    <div class="d-flex gap-3 pb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width: 45px; height: 45px; background: #f5eef5; color: #9b4593;">
                            <span class="fw-bold">S</span>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Sarah Jenkins</h6>
                            <small class="text-warning"><i class="fa-solid fa-star"></i><i
                                    class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                    class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></small>
                            <p class="small text-muted mb-0">Amanda was absolutely amazing. Very helpful!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm p-4 sticky-top" style="border-radius: 20px; top: 20px;">
                <h5 class="fw-bold mb-4 text-center" style="color: #9b4593;">Select Date</h5>

                <div class="calendar-card p-3 rounded-4 bg-light mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button class="btn btn-sm btn-link text-dark shadow-none" onclick="changeMonth(-1)"><i
                                class="bi bi-chevron-left"></i></button>
                        <span class="fw-bold" id="currentMonthYear">February 2026</span>
                        <button class="btn btn-sm btn-link text-dark shadow-none" onclick="changeMonth(1)"><i
                                class="bi bi-chevron-right"></i></button>
                    </div>

                    <div class="calendar-grid" id="calendarGrid">
                        <div class="day-label">Mo</div>
                        <div class="day-label">Tu</div>
                        <div class="day-label">We</div>
                        <div class="day-label">Th</div>
                        <div class="day-label">Fr</div>
                        <div class="day-label">Sa</div>
                        <div class="day-label">Su</div>
                    </div>
                </div>

                <div class="d-flex gap-3 justify-content-center mb-4 small">
                    <span><span class="dot" style="background: #9b4593;"></span> Selected</span>
                    <span><span class="dot" style="background: #e9d5e9;"></span> Available</span>
                    <span><span class="dot" style="background: #dee2e6;"></span> Booked</span>
                </div>

                <button class="btn w-100 py-3 text-white fw-bold shadow-sm"
                    style="background: #9b4593; border-radius: 12px; border: none;">
                    Proceed to Book
                </button>
            </div>
        </div>
        <!-- <div class="col-lg-5">
            <div class="card border-0 shadow-sm p-4 sticky-top" style="border-radius: 20px; top: 20px;">
                <h5 class="fw-bold mb-4 text-center" style="color: #9b4593;">Select Date</h5>

                <div class="calendar-card p-3 rounded-4 bg-light mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button class="btn btn-sm btn-link text-dark"><i class="fa-solid fa-chevron-left"></i></button>
                        <span class="fw-bold">February 2026</span>
                        <button class="btn btn-sm btn-link text-dark"><i class="fa-solid fa-chevron-right"></i></button>
                    </div>

                    <div class="calendar-grid">
                        <div class="day-label">Mo</div>
                        <div class="day-label">Tu</div>
                        <div class="day-label">We</div>
                        <div class="day-label">Th</div>
                        <div class="day-label">Fr</div>
                        <div class="day-label">Sa</div>
                        <div class="day-label">Su</div>

                        <div class="cal-date disabled">29</div>
                        <div class="cal-date disabled">30</div>
                        <div class="cal-date disabled">31</div>
                        <div class="cal-date available">1</div>
                        <div class="cal-date available active">2</div>
                        <div class="cal-date booked">3</div>
                        <div class="cal-date available">4</div>
                        <div class="cal-date available">5</div>
                        <div class="cal-date available">6</div>
                        <div class="cal-date available">7</div>
                        <div class="cal-date available">8</div>
                        <div class="cal-date available">9</div>
                        <div class="cal-date available">10</div>
                        <div class="cal-date available">11</div>
                    </div>
                </div>

                <div class="d-flex gap-3 justify-content-center mb-4 small">
                    <span><span class="dot" style="background: #9b4593;"></span> Selected</span>
                    <span><span class="dot" style="background: #e9d5e9;"></span> Available</span>
                    <span><span class="dot" style="background: #dee2e6;"></span> Booked</span>
                </div>

                <button class="btn w-100 py-3 text-white fw-bold shadow-sm" style="background: #9b4593; border-radius: 12px; border: none;">
                    Proceed to Book
                </button>
            </div>
        </div> -->
    </div>
</div>
<?php get_footer() ?>