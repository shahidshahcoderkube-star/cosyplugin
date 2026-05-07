<?php
get_header();

// Initialize frontend class to use its methods
$frontend = new \Cosy\Appointments\Frontend\Frontend();
$author_slug = get_query_var('author_name');
$provider_data = $frontend->get_provider_with_services($author_slug);

if (empty($provider_data)) {
    echo '<div class="container py-5"><div class="alert alert-info">Provider not found.</div></div>';
    get_footer();
    return;
}

$profile_pic = !empty($provider_data['profile_image']) ? $provider_data['profile_image'] : 'https://ui-avatars.com/api/?name=' . urlencode($provider_data['name']) . '&background=9b4593&color=fff';
$fee = $provider_data['service_fee'] ?? '20';
?>

<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-7">

            <!-- Profile Header Card -->
            <div class="card border-0 shadow-sm mb-4 overflow-hidden" style="border-radius: 20px;">
                <div class="card-header border-0 py-4"
                    style="background: linear-gradient(135deg, #9b4593 0%, #6d2e67 100%); color: white;">
                    <div class="d-flex align-items-center">
                        <img src="<?php echo esc_url($profile_pic); ?>"
                            class="rounded-circle border border-4 border-white shadow-sm"
                            style="width: 100px; height: 100px; object-fit: cover;"
                            alt="<?php echo esc_attr($provider_data['name']); ?>">
                        <div class="ms-4">
                            <h2 class="mb-1 fw-bold d-flex align-items-center">
                                <?php echo esc_html($provider_data['name']); ?>
                                <i class="bi bi-patch-check-fill ms-2 text-info" style="font-size: 1.3rem;"
                                    title="Verified Provider"></i>
                            </h2>
                            <div class="d-flex flex-wrap gap-3 opacity-90 small mt-2">
                                <?php if (!empty($provider_data['gender'])): ?>
                                    <span
                                        class="badge bg-white bg-opacity-25 border border-white border-opacity-25 px-3 py-2 rounded-pill">
                                        <i class="bi bi-person-fill me-1"></i>
                                        <?php echo esc_html(ucwords(strtolower($provider_data['gender']))); ?>
                                    </span>
                                <?php endif ?>
                                <?php if (!empty($provider_data['age_group'])): ?>
                                    <span
                                        class="badge bg-white bg-opacity-25 border border-white border-opacity-25 px-3 py-2 rounded-pill">
                                        <i class="bi bi-people-fill me-1"></i>
                                        <?php echo esc_html($provider_data['age_group']); ?>
                                    </span>
                                <?php endif ?>

                                <?php
                                $video_url = get_user_meta($provider_data['ID'], 'introduction_video', true);
                                if (!empty($video_url)): ?>
                                    <a href="javascript:void(0)"
                                        class="badge bg-white text-dark px-3 py-2 rounded-pill text-decoration-none shadow-sm transition-scale"
                                        data-bs-toggle="modal" data-bs-target="#cosyVideoModal"
                                        data-video-url="<?php echo esc_url($video_url); ?>">
                                        <i class="bi bi-play-circle-fill me-1 text-danger"></i> Watch Intro
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row text-center mb-0 pb-0">
                        <div class="col-4">
                            <div class="h5 fw-bold mb-0" style="color: #9b4593;">£<?php echo esc_html($fee); ?></div>
                            <small class="text-muted text-uppercase" style="font-size: 0.7rem;">Hourly Rate</small>
                        </div>
                        <div class="col-4 border-start border-end">
                            <div class="h5 fw-bold mb-0 text-warning">★★★★★</div>
                            <small class="text-muted text-uppercase" style="font-size: 0.7rem;">5.0 (12 Reviews)</small>
                        </div>
                        <div class="col-4">
                            <div class="h5 fw-bold mb-0"><?php echo esc_html($provider_data['age_group'] ?? 'N/A'); ?>
                            </div>
                            <small class="text-muted text-uppercase" style="font-size: 0.7rem;">Specialist</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- About Me Card -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3" style="color: #9b4593;"><i class="bi bi-person-badge-fill me-2"></i> About
                        Me</h5>
                    <div class="ps-3 border-start border-4" style="border-color: #9b4593 !important;">
                        <p class="text-muted lh-lg mb-0" style="font-size: 1.05rem; font-style: italic;">
                            <?php echo !empty($provider_data['description']) ? nl2br(esc_html($provider_data['description'])) : 'No description provided.'; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Services Card -->
            <?php if (!empty($provider_data['services'])): ?>
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4" style="color: #9b4593;">
                            <i class="bi bi-briefcase-fill me-2"></i> My Services
                        </h5>
                        <div class="row g-3">
                            <?php 
                            $first_service = true;
                            foreach ($provider_data['services'] as $service): 
                                $service_id = sanitize_title($service['service']);
                                ?>
                                <div class="col-12">
                                    <input type="radio" name="cosy_selected_service" id="svc-<?php echo esc_attr($service_id); ?>" 
                                        class="cosy-service-radio d-none" 
                                        value="<?php echo esc_attr($service['service']); ?>"
                                        data-price="<?php echo esc_attr($service['price']); ?>"
                                        data-duration="<?php echo esc_attr($service['duration']); ?>"
                                        <?php checked($first_service); ?>>
                                    <label for="svc-<?php echo esc_attr($service_id); ?>" class="w-100 cursor-pointer">
                                        <div class="p-4 rounded-4 bg-white border d-flex flex-column align-items-center service-card transition"
                                            style="border-color: rgba(0,0,0,0.05) !important; cursor: pointer; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                                            
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="service-icon-box rounded-3 me-3 d-flex align-items-center justify-content-center transition"
                                                    style="width: 55px; height: 55px; background-color: rgba(155, 69, 147, 0.1); color: #9b4593;">
                                                    <i class="bi bi-check2 fs-4"></i>
                                                </div>
                                                <div class="text-start">
                                                    <h5 class="fw-bold mb-1" style="color: #2d3436;"><?php echo esc_html($service['service']); ?></h5>
                                                    <div class="text-muted small">
                                                        <i class="bi bi-clock me-1"></i>
                                                        <?php echo esc_html($service['duration']); ?> mins
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="text-center">
                                                <span class="badge rounded-pill px-4 py-2"
                                                    style="background-color: #9b4593; color: white; font-size: 1rem; font-weight: 700; box-shadow: 0 4px 10px rgba(155, 69, 147, 0.2);">
                                                    £<?php echo esc_html($service['price']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            <?php 
                            $first_service = false;
                            endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Working Hours Card -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4" style="color: #9b4593;">
                        <i class="bi bi-calendar-week-fill me-2"></i> Working Hours
                    </h5>
                    <div class="table-responsive">
                        <table class="table border-0 mb-0">
                            <tbody class="text-secondary small">
                                <?php
                                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                $availability = maybe_unserialize(get_user_meta($provider_data['ID'], 'cosy_availability', true));
                                $today = date('l');

                                foreach ($days as $day):
                                    $is_enabled = isset($availability[$day]['enabled']) && $availability[$day]['enabled'] === 'yes';
                                    $start = $availability[$day]['start'] ?? '';
                                    $end = $availability[$day]['end'] ?? '';
                                    $is_today = ($day === $today);
                                    ?>
                                    <tr class="<?php echo $is_today ? 'bg-light-subtle' : ''; ?>"
                                        style="border-bottom: 1px solid rgba(0,0,0,0.03);">
                                        <td
                                            class="border-0 py-2 <?php echo $is_enabled ? 'text-dark fw-bold' : 'text-muted'; ?>">
                                            <span class="dot me-2"
                                                style="background: <?php echo $is_enabled ? '#28a745' : '#dc3545'; ?>; width: 6px; height: 6px;"></span>
                                            <?php echo $day; ?>
                                            <?php if ($is_today): ?>
                                                <span class="badge rounded-pill ms-1"
                                                    style="background-color: rgba(155, 69, 147, 0.1); color: #9b4593; font-size: 0.55rem;">TODAY</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="border-0 text-end py-2 <?php echo $is_enabled ? 'fw-bold' : 'text-muted'; ?>"
                                            style="color: <?php echo $is_enabled ? '#9b4593' : ''; ?>; font-size: 0.85rem;">
                                            <?php if ($is_enabled): ?>
                                                <?php echo date('h:i A', strtotime($start)) . ' - ' . date('h:i A', strtotime($end)); ?>
                                            <?php else: ?>
                                                Closed
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Reviews Card -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <!-- Header Section -->
                    <div class="mb-4 pb-3 border-bottom">
                        <h5 class="fw-bold mb-3" style="color: #9b4593;">
                            <i class="bi bi-chat-square-text-fill me-2"></i> Reviews
                        </h5>
                        <button class="btn text-white px-4 py-2 shadow-sm transition-scale"
                            style="background-color: #9b4593; border-radius: 12px; font-weight: 600;"
                            data-bs-toggle="collapse" data-bs-target="#reviewForm">
                            <i class="bi bi-plus-lg me-1"></i> Add Review
                        </button>
                    </div>

                    <!-- Add Review Form -->
                    <div class="collapse mb-5" id="reviewForm">
                        <div class="p-4 rounded-4" style="background-color: #fdf6fd; border: 1px dashed #9b4593;">
                            <h6 class="fw-bold mb-3" style="color: #9b4593;">Write Your Review</h6>
                            <textarea class="form-control mb-3 border-0 shadow-sm" rows="3" style="border-radius: 12px;"
                                placeholder="How was your experience?"></textarea>
                            <button class="btn btn-dark px-4 py-2 rounded-pill fw-bold">Submit Review</button>
                        </div>
                    </div>

                    <!-- Reviews List -->
                    <div class="reviews-list">
                        <div class="d-flex gap-3 py-3 border-bottom">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm"
                                style="width: 50px; height: 50px; background: #f5eef5; color: #9b4593; font-size: 1.2rem;">
                                <span class="fw-bold">S</span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 fw-bold">Sarah Jenkins</h6>
                                    <small class="text-muted" style="font-size: 0.75rem;">2 days ago</small>
                                </div>
                                <div class="mb-2" style="color: #ffc107; font-size: 0.85rem;">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                </div>
                                <p class="text-muted mb-0 lh-base" style="font-size: 0.9rem;">
                                    Very professional and helpful. Highly recommended! The session was really
                                    productive.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5" id="cosyProviderProfile" 
            data-provider-id="<?php echo esc_attr($provider_data['ID']); ?>" 
            data-hourly-rate="<?php echo esc_attr($fee); ?>"
            data-slot-duration="<?php echo esc_attr(get_user_meta($provider_data['ID'], 'cosy_slot_duration', true) ?: 30); ?>">
            <div class="card border-0 shadow-sm p-4 sticky-top" style="border-radius: 20px; top: 20px;">
                <div class="text-center mb-4">
                    <h5 class="fw-bold mb-1" style="color: #9b4593;">Booking Calendar</h5>
                    <p class="text-muted small mb-0">Select your preferred date</p>
                </div>

                <!-- Custom Premium Calendar -->
                <div class="cosy-custom-calendar mb-4">
                    <div
                        class="calendar-header d-flex justify-content-between align-items-center mb-4 p-2 bg-light rounded-pill">
                        <button type="button" class="prev-month btn-nav shadow-sm"><i
                                class="bi bi-chevron-left"></i></button>
                        <h6 class="current-month fw-bold mb-0 text-dark"
                            style="font-size: 1rem; letter-spacing: 0.5px;"></h6>
                        <button type="button" class="next-month btn-nav shadow-sm"><i
                                class="bi bi-chevron-right"></i></button>
                    </div>
                    <div class="calendar-weekdays d-grid mb-3" style="grid-template-columns: repeat(7, 1fr);">
                        <div class="text-center fw-bold text-muted" style="font-size: 0.65rem; letter-spacing: 1px;">SUN
                        </div>
                        <div class="text-center fw-bold text-muted" style="font-size: 0.65rem; letter-spacing: 1px;">MON
                        </div>
                        <div class="text-center fw-bold text-muted" style="font-size: 0.65rem; letter-spacing: 1px;">TUE
                        </div>
                        <div class="text-center fw-bold text-muted" style="font-size: 0.65rem; letter-spacing: 1px;">WED
                        </div>
                        <div class="text-center fw-bold text-muted" style="font-size: 0.65rem; letter-spacing: 1px;">THU
                        </div>
                        <div class="text-center fw-bold text-muted" style="font-size: 0.65rem; letter-spacing: 1px;">FRI
                        </div>
                        <div class="text-center fw-bold text-muted" style="font-size: 0.65rem; letter-spacing: 1px;">SAT
                        </div>
                    </div>
                    <div class="calendar-days d-grid" style="grid-template-columns: repeat(7, 1fr); gap: 8px;">
                        <!-- Days will be injected by JS -->
                    </div>
                </div>

                <!-- Calendar Legend -->
                <div class="p-2 rounded-4 mb-3 border shadow-sm" style="background: #fdf6fd; border-color: rgba(155, 69, 147, 0.1) !important;">
                    <div class="d-flex gap-4 justify-content-center align-items-center" style="font-size: 0.75rem;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="dot" style="background: #9b4593; width: 10px; height: 10px; border-radius: 50%; display: inline-block; box-shadow: 0 0 5px rgba(155, 69, 147, 0.4);"></span>
                            <span class="fw-bold" style="color: #6d2e67;">SELECTED</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="dot" style="background: #222; width: 10px; height: 10px; border-radius: 50%; display: inline-block;"></span>
                            <span class="fw-bold" style="color: #333;">UNAVAILABLE</span>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold mb-3 small text-uppercase text-muted">Weekly Schedule</h6>
                <!-- Weekly Schedule List Container -->
                <div id="cosyWeeklySchedule" class="mb-4">
                    <p class="text-muted small">Select a start date from the calendar to see the schedule.</p>
                </div>

                <!-- Slots Modal Trigger or Dropdown (Hidden initially) -->
                <div id="cosyTimeSlots" class="d-none"></div>

                <!-- Selection Summary -->
                <div id="cosySelectionSummary" class="p-4 rounded-4 mb-4 d-none"
                    style="background-color: #fdf6fd; border: 1px dashed #9b4593;">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="bi bi-alarm-fill text-danger"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block mb-0">You've selected:</small>
                            <div id="summaryDateTime"></div>
                        </div>
                    </div>
                </div>

                <!-- Weeks Selector & Total Amount -->
                <div id="cosyPricingSection" class="p-4 rounded-4 mb-4 d-none" style="background: linear-gradient(135deg, #fdf6fd 0%, #fff 100%); border: 1px solid rgba(155, 69, 147, 0.1);">
                    <div class="mb-3">
                        <label class="fw-bold text-muted small text-uppercase d-block mb-2 text-center">Duration of Booking</label>
                        <div class="d-flex justify-content-center">
                            <select id="cosyWeeksSelect" class="form-select shadow-sm" style="max-width: 250px; border-radius: 12px; border: 1px solid rgba(155, 69, 147, 0.2); font-size: 0.95rem; font-weight: 600; color: #6d2e67;">
                                <option value="1">1 Week</option>
                                <option value="2">2 Weeks</option>
                                <option value="3">3 Weeks</option>
                                <option value="4">4 Weeks</option>
                                <option value="5">5 Weeks</option>
                                <option value="6">6 Weeks</option>
                                <option value="7">7 Weeks</option>
                                <option value="8">8 Weeks</option>
                                <option value="9">9 Weeks</option>
                                <option value="10">10 Weeks</option>
                                <option value="11">11 Weeks</option>
                                <option value="12">12 Weeks</option>
                            </select>
                        </div>
                    </div>
                    <div class="pt-3 border-top border-dashed text-center">
                        <span class="text-muted small text-uppercase d-block mb-1">Total Service Amount</span>
                        <h4 class="fw-bold mb-0" style="color: #28a745; letter-spacing: -0.5px;"><span id="cosyTotalAmount">£ 0.00</span></h4>
                    </div>
                </div>

                <button id="cosyBookNowBtn" class="btn w-100 py-3 text-white fw-bold shadow-sm transition-scale"
                    style="background: linear-gradient(135deg, #9b4593 0%, #6d2e67 100%); border-radius: 15px; border: none;">
                    Confirm & Proceed
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom Calendar Styling */
    .btn-nav {
        width: 36px !important;
        height: 36px !important;
        flex-shrink: 0 !important;
        border-radius: 50%;
        border: none;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #333;
        transition: all 0.2s ease;
        padding: 0 !important;
    }
    .btn-nav:hover {
        background: #9b4593;
        color: white;
    }
    .calendar-day {
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 500;
        border-radius: 50%;
        transition: all 0.2s ease;
        color: #444;
    }
    .calendar-day:hover:not(.empty):not(.disabled):not(.past-date) {
        background: #f0f0f0;
    }
    .calendar-day.today {
        color: #9b4593;
        font-weight: 800;
        border: 2px solid rgba(155, 69, 147, 0.2);
    }
    .calendar-day.selected {
        background: #9b4593 !important;
        color: white !important;
        font-weight: 700;
        box-shadow: 0 4px 10px rgba(155, 69, 147, 0.3);
    }
    .calendar-day.disabled {
        background: #222 !important; /* Holiday Black */
        color: #fff !important;
        cursor: not-allowed !important;
        pointer-events: none !important;
        opacity: 1 !important;
        border-radius: 50%;
    }
    .calendar-day.past-date {
        color: #ccc !important;
        cursor: not-allowed !important;
        pointer-events: none !important;
        text-decoration: line-through;
        background: transparent !important;
    }
    .calendar-day.empty {
        cursor: default;
    }

    /* Weekly Schedule Styling */
    .schedule-row {
        background: #fdfdfd;
        border-radius: 8px;
        padding: 5px 12px;
        margin-bottom: 5px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.2s ease;
        border: 1px solid rgba(0,0,0,0.03);
    }
    .schedule-row:hover {
        background: #fff;
        border-color: rgba(155, 69, 147, 0.2);
        box-shadow: 0 3px 12px rgba(0,0,0,0.04);
    }
    .schedule-day-name {
        font-weight: 700;
        color: #333;
        font-size: 0.9rem;
        line-height: 1.2;
    }
    .schedule-date-small {
        font-size: 0.7rem;
        color: #999;
        display: block;
    }
    .btn-select-time {
        width: 100px !important;
        height: 28px !important;
        padding: 0 !important;
        font-size: 0.7rem !important;
        font-weight: 700 !important;
        border-radius: 6px !important;
        background: #9b4593 !important;
        color: white !important;
        border: none !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 2px 6px rgba(155, 69, 147, 0.15) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin-left: auto !important; /* Forces it to the right */
    }
    .btn-select-time:hover {
        background: #6d2e67;
        transform: translateY(-1px);
    }
    .btn-select-time.active {
        background: #111 !important;
        color: #fff !important;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .cosy-slot-btn {
        width: calc(16.66% - 8px);
        min-width: 80px;
        padding: 10px 5px;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 6px;
        border: none;
        background: #90ee90; /* Light Green */
        color: #1a5e1a;
        transition: all 0.2s ease;
        margin-bottom: 5px;
        text-align: center;
    }
    .cosy-slot-btn:hover {
        background: #77dd77;
    }
    .cosy-slot-btn.active {
        background: #9b4593 !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(155, 69, 147, 0.3);
    }
    .cosy-slot-btn.booked {
        background: #ff4d4d !important;
        color: white !important;
        cursor: not-allowed;
        opacity: 0.8;
    }

    .duration-badge {
        background: #f0f0f0;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.8rem;
        color: #666;
        border: 1px solid #ddd;
    }
    
    .schedule-row.has-selection {
        background: #fff9ff;
        border-color: #9b4593;
    }

    .slot-grid-inline {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 20px;
        background: #fff;
        border-radius: 12px;
        border: 1px solid #f0f0f0;
        margin-top: 5px;
        margin-bottom: 20px;
        justify-content: center;
    }

    @media (max-width: 576px) {
        .cosy-slot-btn {
            width: calc(33.33% - 8px);
        }
    }
    .cosy-service-radio:checked + label .service-card {
        background-color: #fff9ff !important;
        border-color: #9b4593 !important;
        box-shadow: 0 4px 15px rgba(155, 69, 147, 0.1);
    }
    .cosy-service-radio:checked + label .service-card .service-icon-box {
        background-color: #9b4593 !important;
        color: white !important;
    }
    .service-card:hover {
        transform: none !important;
    }
</style>

<!-- Video Introduction Modal -->
<div class="modal fade" id="cosyVideoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden; background: #000;">
            <div class="modal-header border-0 position-absolute top-0 end-0 z-3 p-3">
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="ratio ratio-16x9">
                    <video id="cosyIntroVideo" controls controlsList="nodownload">
                        <source src="" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Video Logic ---
    const videoModal = document.getElementById('cosyVideoModal');
    const videoPlayer = document.getElementById('cosyIntroVideo');
    if (videoModal && videoPlayer) {
        videoModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const videoUrl = button.getAttribute('data-video-url');
            if (videoUrl) {
                videoPlayer.querySelector('source').src = videoUrl;
                videoPlayer.load();
                videoPlayer.play();
            }
        });
        videoModal.addEventListener('hide.bs.modal', function() {
            videoPlayer.pause();
            videoPlayer.currentTime = 0;
        });
    }

    // --- Custom Calendar Logic ---
    let currentDate = new Date();
    const calendarDays = document.querySelector('.calendar-days');
    const currentMonthText = document.querySelector('.current-month');
    const prevBtn = document.querySelector('.prev-month');
    const nextBtn = document.querySelector('.next-month');
    const profileEl = document.getElementById('cosyProviderProfile');
    const providerId = profileEl.dataset.providerId;
    const hourlyRate = parseFloat(profileEl.dataset.hourlyRate) || 20;
    const providerSlotDuration = parseInt(profileEl.dataset.slotDuration) || 30;
    const weeklyScheduleEl = document.getElementById('cosyWeeklySchedule');

    let providerHolidays = [];

    // 1. Fetch holidays first
    function fetchHolidays() {
        jQuery.ajax({
            url: cosy_ajax.ajax_url,
            type: "POST",
            data: {
                action: "get_cosy_holidays",
                nonce: cosy_ajax.cosy_nonce,
                provider_id: providerId
            },
            success(res) {
                if (res.success && Array.isArray(res.data)) {
                    providerHolidays = res.data.map(h => h.holiday_date);
                    console.log("Holidays loaded:", providerHolidays);
                } else {
                    console.warn("Holiday fetch returned:", res);
                }
                renderCalendar();
            },
            error() {
                console.error("Failed to fetch holidays");
                renderCalendar(); // Still render calendar even if holidays fail
            }
        });
    }

    function renderCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        currentMonthText.innerText = new Intl.DateTimeFormat('en-US', { month: 'long', year: 'numeric' }).format(currentDate);
        const firstDay = new Date(year, month, 1).getDay();
        const lastDay = new Date(year, month + 1, 0).getDate();
        const today = new Date();
        calendarDays.innerHTML = '';
        for (let i = 0; i < firstDay; i++) {
            const div = document.createElement('div');
            div.classList.add('calendar-day', 'empty');
            calendarDays.appendChild(div);
        }
        for (let i = 1; i <= lastDay; i++) {
            const dayDiv = document.createElement('div');
            dayDiv.classList.add('calendar-day');
            dayDiv.innerText = i;
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
            const checkDate = new Date(year, month, i);
            if (checkDate.toDateString() === today.toDateString()) dayDiv.classList.add('today');
            
            const isHoliday = providerHolidays.includes(dateStr.trim());
            const isPast = checkDate < new Date().setHours(0,0,0,0);

            if (isHoliday) {
                dayDiv.classList.add('disabled');
            } else if (isPast) {
                dayDiv.classList.add('past-date');
            } else {
                dayDiv.onclick = () => selectStartDate(dateStr, dayDiv);
            }
            calendarDays.appendChild(dayDiv);
        }
    }
    
    // Call fetchHolidays initially
    fetchHolidays();

    function selectStartDate(dateStr, element) {
        if (element.classList.contains('disabled') || providerHolidays.includes(dateStr.trim())) {
            console.log("Selection blocked: Holiday or Disabled date.");
            return; 
        }
        document.querySelectorAll('.calendar-day').forEach(d => d.classList.remove('selected'));
        element.classList.add('selected');
        renderWeeklySchedule(dateStr);
    }

    // --- Multi-Slot Booking Logic ---
    window.selectedBookingSlots = []; 

    function renderWeeklySchedule(startDateStr) {
        const startDate = new Date(startDateStr);
        weeklyScheduleEl.innerHTML = '';
        for (let i = 0; i < 7; i++) {
            const date = new Date(startDate);
            date.setDate(startDate.getDate() + i);
            const dateStr = date.toISOString().split('T')[0];
            const dayName = date.toLocaleDateString('en-US', { weekday: 'long' });
            const displayDate = date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });

            const isHoliday = providerHolidays.includes(dateStr);
            const holidayClass = isHoliday ? 'is-holiday opacity-75' : '';
            const btnText = isHoliday ? 'Holiday' : 'Select Time';
            const btnDisabled = isHoliday ? 'disabled' : '';

            const row = document.createElement('div');
            row.classList.add('schedule-container');
            row.innerHTML = `
                <div class="schedule-row ${holidayClass}" id="row-${dateStr}">
                    <div class="d-flex align-items-center flex-grow-1">
                        <div style="min-width: 80px;">
                            <span class="schedule-day-name">${dayName}</span>
                            <span class="schedule-date-small">${displayDate}</span>
                        </div>
                        <div class="selection-info-${dateStr} ms-3">
                            ${isHoliday ? '<span class="badge bg-danger-subtle text-danger border-0 px-2 py-1" style="font-size:0.65rem;">Closed (Holiday)</span>' : '<span class="duration-badge d-none"><i class="bi bi-clock-history me-1"></i> <span class="mins-val">0</span> mins</span>'}
                        </div>
                    </div>
                    <button class="btn btn-select-time" id="btn-${dateStr}" onclick="toggleSlots('${dateStr}')" ${btnDisabled}>
                        ${btnText}
                    </button>
                </div>
                <div id="slots-${dateStr}" class="slot-container-inline" style="display:none;"></div>
            `;
            weeklyScheduleEl.appendChild(row);
        }
    }

    window.toggleSlots = function(dateStr) {
        const container = document.getElementById(`slots-${dateStr}`);
        const btn = document.querySelector(`#row-${dateStr} .btn-select-time`);

        if (container.style.display === 'block') {
            container.style.display = 'none';
            btn.classList.remove('active');
            return;
        }

        container.style.display = 'block';
        btn.classList.add('active');

        jQuery.ajax({
            url: cosy_ajax.ajax_url,
            type: "POST",
            data: {
                action: "get_cosy_booking_slots",
                nonce: cosy_ajax.cosy_nonce,
                provider_id: providerId,
                date: dateStr
            },
            beforeSend() {
                container.innerHTML = '<div class="p-3 text-center"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
            },
            success(res) {
                if (res.success) {
                    container.innerHTML = `<div class="slot-grid-inline">${res.data.html}</div>`;
                    
                    // Highlight already selected slots
                    window.selectedBookingSlots.forEach(s => {
                        if (s.date === dateStr) {
                            const activeBtn = container.querySelector(`.cosy-slot-btn[data-start="${s.start}"]`);
                            if (activeBtn) activeBtn.classList.add('active');
                        }
                    });

                    container.querySelectorAll('.cosy-slot-btn').forEach(slotBtn => {
                        slotBtn.onclick = function() {
                            // Safe date display without timezone shift
                            const dParts = dateStr.split('-');
                            const dObj = new Date(dParts[0], dParts[1] - 1, dParts[2]);
                            const slotData = {
                                date: dateStr,
                                start: this.dataset.start,
                                end: this.dataset.end,
                                label: this.innerText,
                                displayDate: dObj.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' })
                            };

                            const index = window.selectedBookingSlots.findIndex(s => s.date === slotData.date && s.start === slotData.start);
                            
                            if (index > -1) {
                                window.selectedBookingSlots.splice(index, 1);
                                this.classList.remove('active');
                            } else {
                                window.selectedBookingSlots.push(slotData);
                                this.classList.add('active');
                            }
                            
                            updateBookingSummary();
                        };
                    });
                }
            }
        });
    }

    function updateBookingSummary() {
        const summaryEl = document.getElementById('cosySelectionSummary');
        const summaryTextEl = document.getElementById('summaryDateTime');
        const pricingSection = document.getElementById('cosyPricingSection');
        const totalAmountEl = document.getElementById('cosyTotalAmount');
        const weeksSelect = document.getElementById('cosyWeeksSelect');
        
        // Group by date to calculate duration per day
        const grouped = {};
        window.selectedBookingSlots.forEach(s => {
            if (!grouped[s.date]) grouped[s.date] = { date: s.date, count: 0, start: s.start, label: s.label, displayDate: s.displayDate };
            grouped[s.date].count++;
            if (s.start < grouped[s.date].start) {
                grouped[s.date].start = s.start;
                grouped[s.date].label = s.label;
            }
        });

        // Convert to array and sort by date
        const sortedDays = Object.values(grouped).sort((a, b) => a.date.localeCompare(b.date));

        // Update individual day rows
        document.querySelectorAll('.schedule-row').forEach(row => {
            const dateStr = row.id.replace('row-', '');
            const infoBox = document.querySelector(`.selection-info-${dateStr}`);
            const btn = document.getElementById(`btn-${dateStr}`);
            
            if (grouped[dateStr]) {
                const totalMins = grouped[dateStr].count * providerSlotDuration; 
                row.classList.add('has-selection');
                infoBox.classList.remove('d-none');
                infoBox.querySelector('.mins-val').innerText = totalMins;
                btn.innerText = 'Edit Time';
                btn.classList.add('active');
            } else {
                row.classList.remove('has-selection');
                infoBox.classList.add('d-none');
                btn.innerText = 'Select Time';
                btn.classList.remove('active');
            }
        });

        if (window.selectedBookingSlots.length === 0) {
            summaryEl.classList.add('d-none');
            pricingSection.classList.add('d-none');
            return;
        }

        summaryEl.classList.remove('d-none');
        pricingSection.classList.remove('d-none');
        
        // Get selected service data
        const selectedSvcRadio = document.querySelector('input[name="cosy_selected_service"]:checked');
        const svcPrice = selectedSvcRadio ? parseFloat(selectedSvcRadio.dataset.price) : hourlyRate;
        const svcDuration = selectedSvcRadio ? parseInt(selectedSvcRadio.dataset.duration) : 60;
        
        // Calculate total minutes across all selected slots
        const totalMinutes = window.selectedBookingSlots.length * providerSlotDuration;
        const pricePerMinute = svcPrice / svcDuration;
        const weeks = parseInt(weeksSelect.value) || 1;
        const totalPrice = (totalMinutes * pricePerMinute * weeks).toFixed(2);
        
        totalAmountEl.innerText = `£ ${totalPrice}`;

        let summaryHtml = '';
        sortedDays.forEach(data => {
            const duration = data.count * providerSlotDuration;
            const dayPrice = (duration * pricePerMinute).toFixed(2);
            summaryHtml += `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-bold" style="font-size: 0.95rem;">
                        ${data.displayDate}: ${data.label} (${duration} mins) - £${dayPrice}
                    </div>
                    <i class="bi bi-trash3 text-danger" style="cursor:pointer;" onclick="removeDaySelection('${data.date}')"></i>
                </div>
            `;
        });
        summaryTextEl.innerHTML = summaryHtml;
    }

    // Recalculate price when weeks or service changes
    document.getElementById('cosyWeeksSelect').addEventListener('change', updateBookingSummary);
    document.querySelectorAll('input[name="cosy_selected_service"]').forEach(radio => {
        radio.addEventListener('change', updateBookingSummary);
    });

    window.removeDaySelection = function(date) {
        window.selectedBookingSlots = window.selectedBookingSlots.filter(s => s.date !== date);
        updateBookingSummary();
        // Unhighlight all in UI if open
        const container = document.getElementById(`slots-${date}`);
        if (container) {
            container.querySelectorAll('.cosy-slot-btn').forEach(b => b.classList.remove('active'));
        }
    }

    window.removeSelectedSlot = function(date, start) {
        const index = window.selectedBookingSlots.findIndex(s => s.date === date && s.start === start);
        if (index > -1) {
            window.selectedBookingSlots.splice(index, 1);
            updateBookingSummary();
            const btn = document.querySelector(`#slots-${date} .cosy-slot-btn[data-start="${start}"]`);
            if (btn) btn.classList.remove('active');
        }
    }

    document.getElementById('cosyBookNowBtn').onclick = function() {
        if (window.selectedBookingSlots.length === 0) {
            Swal.fire('Empty!', 'Please select at least one time slot.', 'warning');
            return;
        }

        const btn = this;
        const originalHtml = btn.innerHTML;

        const selectedService = document.querySelector('input[name="cosy_selected_service"]:checked');

        jQuery.ajax({
            url: cosy_ajax.ajax_url,
            type: "POST",
            data: {
                action: "confirm_cosy_booking",
                nonce: cosy_ajax.cosy_nonce,
                provider_id: providerId,
                service: selectedService ? selectedService.value : '',
                slots: JSON.stringify(window.selectedBookingSlots)
            },
            beforeSend() {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';
            },
            success(res) {
                if (res.success) {
                    Swal.fire('Success!', res.data.message, 'success').then(() => {
                        if (res.data.redirect) window.location.href = res.data.redirect;
                    });
                } else {
                    Swal.fire('Error!', res.data.message, 'error');
                }
            },
            complete() {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
    };

    prevBtn.onclick = () => { currentDate.setMonth(currentDate.getMonth() - 1); renderCalendar(); };
    nextBtn.onclick = () => { currentDate.setMonth(currentDate.getMonth() + 1); renderCalendar(); };
});
</script>

<?php get_footer() ?>