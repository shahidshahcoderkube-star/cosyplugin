<?php
get_header();
$author_slug = get_query_var('author_name');
$common = new class {
    use \Cosy\Appointments\Common\GlobalCommonFunctions;
};
$provider_data = $common->get_provider_with_services($author_slug);

?>
<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-7">

            <div class="card border-0 shadow-sm mb-4 overflow-hidden" style="border-radius: 24px;">
                <div class="card-header border-0 py-4 px-5"
                    style="background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: white;">
                    <div class="d-flex align-items-center flex-wrap gap-4">
                        <div class="profile-avatar-wrapper-premium"
                            style="position: relative; width: 110px; height: 110px;">
                            <?php
                            $profile_image = !empty($provider_data['profile_image']) ? $provider_data['profile_image'] : 'https://via.placeholder.com/120';
                            ?>
                            <img src="<?php echo esc_url($profile_image); ?>"
                                style="width: 100%; height: 100%; object-fit: cover; border-radius: 30px; border: 4px solid rgba(255,255,255,0.3); box-shadow: 0 0 20px rgba(0,0,0,0.15);"
                                alt="<?php echo esc_attr($provider_data['prov_mname']); ?>">
                        </div>
                        <div class="profile-info-top">
                            <?php
                            if (!empty($provider_data['prov_mname'])) { ?>
                                <h2 class="mb-2 fw-bold h4">
                                    <?php echo esc_html($provider_data['prov_mname']); ?>
                                </h2>
                            <?php } ?>
                            <div class="d-flex gap-3 opacity-75 small fw-medium">
                                <?php if (!empty($provider_data['gender'])): ?>
                                    <span><i class="fas fa-venus me-1"></i>
                                        <?php echo esc_html(ucwords(strtolower($provider_data['gender']))); ?></span>
                                <?php endif ?>
                                <span><i class="fas fa-user-check me-1"></i> Verified Specialist</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="row text-center g-0 border-bottom" style="background: #fafbfc;">
                        <div class="col-4 py-3">
                            <?php if (!empty($provider_data['hourly_rate'])) { ?>
                                <div class="h5 fw-bold mb-1" style="color: #a44390; letter-spacing: -0.5px;">
                                    £<?php echo esc_html($provider_data['hourly_rate']); ?></div>
                            <?php } ?>
                            <small class="text-muted text-uppercase fw-bold"
                                style="font-size: 0.6rem; letter-spacing: 0.8px;">Hourly Rate</small>

                        </div>
                        <div class="col-4 py-3 border-start border-end">
                            <div class="h5 fw-bold mb-1 text-warning" style="letter-spacing: -0.5px;"><i
                                    class="fas fa-star me-1" style="font-size: 1rem;"></i>5.0</div>

                            <small class="text-muted text-uppercase fw-bold"
                                style="font-size: 0.6rem; letter-spacing: 0.8px;">(12 Reviews)</small>
                        </div>
                        <div class="col-4 py-3">
                            <?php if (!empty($provider_data['age_group'])) { ?>
                                <div class="h5 fw-bold mb-1" style="color: #1e293b; letter-spacing: -0.5px;">
                                    <?php echo esc_html($provider_data['age_group']); ?>
                                </div>
                            <?php } ?>
                            <small class="text-muted text-uppercase fw-bold"
                                style="font-size: 0.6rem; letter-spacing: 0.8px;">Age Group</small>
                        </div>
                    </div>
                </div>
                <div class="card-body py-4 px-5">
                    <p class="text-muted text-center italic mb-0" style="font-size: 0.95rem;">Experience premium
                        sessions tailored to your needs with our verified specialists.</p>
                </div>
            </div>

            <!-- Separate Services Section -->
            <?php if (!empty($provider_data['services'])): ?>
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 24px;">
                    <div class="card-body p-4 px-5">
                        <div class="d-flex align-items-center gap-3 mb-3 pb-2 border-bottom"
                            style="border-color: #f1f5f9 !important;">
                            <div
                                style="width: 40px; height: 40px; background: #fdf2fb; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-concierge-bell" style="color: #a44390;"></i>
                            </div>
                            <h5 class="fw-bold mb-0" style="color: #a44390; letter-spacing: -0.5px;">Our Services</h5>
                        </div>
                        <div class="services-list-premium">
                            <?php foreach ($provider_data['services'] as $service): ?>
                                <div class="service-item-row d-flex justify-content-between align-items-center p-3 mb-3"
                                    style="background: #f8fafc; border-radius: 16px; border: 1px solid #e2e8f0; transition: all 0.3s ease;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div
                                            style="width: 40px; height: 40px; background: #fff; border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                                            <i class="fas fa-check-circle" style="color: #a44390;"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold" style="color: #1e293b;">
                                                <?php echo esc_html($service['title']); ?>
                                            </h6>
                                            <small class="text-muted"><?php echo esc_html($service['time'] ?? '60'); ?> mins
                                                session</small>
                                        </div>
                                    </div>
                                    <div
                                        style="background: #a44390; color: #fff; padding: 8px 15px; border-radius: 10px; font-weight: 700;">
                                        £<?php echo esc_html($service['price']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm mb-4" style="border-radius: 24px;">
                <div class="card-body p-4 px-5">
                    <div class="d-flex align-items-center gap-3 mb-3 pb-2 border-bottom"
                        style="border-color: #f1f5f9 !important;">
                        <div
                            style="width: 40px; height: 40px; background: #fdf2fb; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-user" style="color: #a44390;"></i>
                        </div>
                        <h5 class="fw-bold mb-0" style="color: #a44390; letter-spacing: -0.5px;">About Me</h5>
                    </div>
                    <p class="text-muted lh-lg mb-0" style="font-size: 1.05rem; color: #475569 !important;">
                        <?php
                        $about_text = !empty($provider_data['description']) ? $provider_data['description'] : 'As a mother of a young man who has thrived despite ADHD and ASD, and with years of experience as an Early Years specialist and teacher, I deeply understand the challenges faced by families. I offer a compassionate listening ear and tailored plans to help your child succeed.';
                        echo nl2br(esc_html($about_text));
                        ?>
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4" style="border-radius: 24px;">
                <div class="card-body p-4 px-5">
                    <div class="d-flex align-items-center gap-3 mb-3 pb-2 border-bottom"
                        style="border-color: #f1f5f9 !important;">
                        <div
                            style="width: 40px; height: 40px; background: #fdf2fb; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-calendar-check" style="color: #a44390;"></i>
                        </div>
                        <h5 class="fw-bold mb-0" style="color: #a44390; letter-spacing: -0.5px;">Working Hours</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table border-0 mb-0">
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

            <div class="card border-0 shadow-sm mb-4" style="border-radius: 24px;">
                <div class="card-body p-4 px-5">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3 pb-2 border-bottom"
                        style="border-color: #f1f5f9 !important;">
                        <div class="d-flex align-items-center gap-3">
                            <div
                                style="width: 40px; height: 40px; background: #fdf2fb; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-comment-dots" style="color: #a44390;"></i>
                            </div>
                            <h5 class="fw-bold mb-0" style="color: #a44390; letter-spacing: -0.5px;">Reviews</h5>
                        </div>
                        <button class="btn btn-sm text-white px-3"
                            style="background-color: #a44390; border-radius: 10px;" data-bs-toggle="collapse"
                            data-bs-target="#reviewForm">
                            + Add Review
                        </button>
                    </div>

                    <div class="collapse mb-4" id="reviewForm">
                        <div class="p-4 rounded-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <label class="small fw-bold text-muted mb-2 d-block">Rating</label>
                            <div class="star-rating-input d-flex gap-2 mb-3">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fa-star far cursor-pointer rating-star" data-rating="<?php echo $i; ?>"
                                        style="color: #cbd5e1; font-size: 1.2rem; cursor: pointer; transition: all 0.2s;"></i>
                                <?php endfor; ?>
                                <input type="hidden" name="rating" id="selectedRating" value="0">
                            </div>

                            <label class="small fw-bold text-muted mb-2 d-block">Your Review</label>
                            <textarea class="form-control mb-3 border-0 shadow-sm" rows="3"
                                placeholder="Share your experience..."
                                style="border-radius: 14px; padding: 15px; font-size: 0.95rem; resize: none;"></textarea>

                            <button class="btn w-100 py-2 fw-bold text-white shadow-sm"
                                style="background: linear-gradient(135deg, #a44390, #6d2e67); border-radius: 12px; border: none; font-size: 0.9rem; transition: all 0.3s;">
                                Post Review
                            </button>
                        </div>
                    </div>

                    <div class="d-flex gap-3 pb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width: 45px; height: 45px; background: #fdf2fb; color: #a44390;">
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
            <div class="sticky-top" style="top: 20px;">
                <!-- Calendar Card -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 24px; overflow: hidden;">
                    <div class="p-4 pb-0">
                        <div class="d-flex align-items-center gap-3 mb-4 pb-2 border-bottom"
                            style="border-color: #f1f5f9 !important;">
                            <div
                                style="width: 40px; height: 40px; background: #fdf2fb; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-calendar-alt" style="color: #a44390;"></i>
                            </div>
                            <h5 class="fw-bold mb-0" style="color: #a44390; letter-spacing: -0.5px;">Select Date</h5>
                        </div>

                        <!-- Month Navigation -->
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <div style="width:32px; height:32px; flex-shrink:0;">
                                <button onclick="changeMonth(-1)"
                                    style="width:32px; height:32px; padding:0; margin:0; border-radius:50%; background:#fff; border:1.5px solid #e2e8f0; color:#a44390; font-size:0.7rem; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 6px rgba(0,0,0,0.08); transition:all 0.2s; box-sizing:border-box;"
                                    onmouseover="this.style.background='#a44390';this.style.color='#fff';"
                                    onmouseout="this.style.background='#fff';this.style.color='#a44390';">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                            </div>
                            <span class="fw-bold" id="currentMonthYear"
                                style="color:#1e293b; font-size:0.95rem;"></span>
                            <div style="width:32px; height:32px; flex-shrink:0;">
                                <button onclick="changeMonth(1)"
                                    style="width:32px; height:32px; padding:0; margin:0; border-radius:50%; background:#fff; border:1.5px solid #e2e8f0; color:#a44390; font-size:0.7rem; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 6px rgba(0,0,0,0.08); transition:all 0.2s; box-sizing:border-box;"
                                    onmouseover="this.style.background='#a44390';this.style.color='#fff';"
                                    onmouseout="this.style.background='#fff';this.style.color='#a44390';">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Day Labels -->
                        <div id="calendarGrid"
                            style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; text-align: center; margin-bottom: 8px;">
                            <?php foreach (['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'] as $d): ?>
                                <div style="font-size: 0.72rem; font-weight: 700; color: #94a3b8; padding: 6px 0;">
                                    <?php echo $d; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div id="calendarDays"
                            style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; text-align: center; margin-bottom: 16px;">
                        </div>
                    </div>

                    <div class="p-4 pt-2">
                        <div class="d-flex gap-3 justify-content-center mb-0 small">
                            <span style="display: flex; align-items: center; gap: 6px;">
                                <span
                                    style="width: 10px; height: 10px; border-radius: 50%; background: #a44390; display: inline-block;"></span>
                                Selected
                            </span>
                            <span style="display: flex; align-items: center; gap: 6px;">
                                <span
                                    style="width: 10px; height: 10px; border-radius: 50%; background: #e9d5e9; display: inline-block;"></span>
                                Available
                            </span>
                            <span style="display: flex; align-items: center; gap: 6px;">
                                <span
                                    style="width: 10px; height: 10px; border-radius: 50%; background: #e2e8f0; display: inline-block;"></span>
                                Unavailable
                            </span>
                        </div>
                    </div>
                </div>

                <div id="bookingTimeSlots" class="card border-0 shadow-sm"
                    style="display: none; border-radius: 24px; overflow: hidden; background: #fff;">
                    <div class="card-body p-4 pb-2">
                        <div class="d-flex align-items-center gap-3 mb-4 pb-2 border-bottom"
                            style="border-color: #f1f5f9 !important;">
                            <div
                                style="width: 40px; height: 40px; background: #fdf2fb; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-clock" style="color: #a44390;"></i>
                            </div>
                            <h5 class="fw-bold mb-0" style="color: #a44390; letter-spacing: -0.5px;">Call Schedule</h5>
                        </div>

                        <div class="d-flex align-items-center gap-2 mb-3 p-2 bg-light rounded-3 border"
                            style="border-color: #edf2f7 !important;">
                            <input type="checkbox" id="bookingForAnother"
                                style="width: 18px; height: 18px; cursor: pointer; accent-color: #a44390;">
                            <label for="bookingForAnother" class="small text-muted fw-bold mb-0 cursor-pointer"
                                style="font-size: 0.75rem;">Booking for another person</label>
                        </div>

                        <div class="p-2 px-3 fw-bold text-dark mb-3 d-flex align-items-center justify-content-between"
                            style="background: #fdf2fb; border: 1px solid #f9e6f5; border-radius: 10px; font-size: 0.8rem;">
                            <span><i class="fas fa-calendar-day me-2 text-muted"></i> Start Date:</span>
                            <span id="displaySelectedDate" class="text-primary">May 13, 2026</span>
                        </div>

                        <div id="timeSlotsList" class="d-flex flex-column gap-2 mb-0">
                            <!-- Rows will be populated by JS -->
                        </div>

                        <!-- DYNAMIC WEEKLY PRICING SECTION -->
                        <div id="weeklyPricingSection" style="display: none;" class="mt-4 pt-4 border-top">
                            <div class="text-center">
                                <p class="small text-muted fw-bold mb-3 text-uppercase"
                                    style="letter-spacing: 0.8px; font-size: 0.7rem;">Select Booking Duration</p>

                                <div class="px-2 mb-3 position-relative">
                                    <select id="totalBookingWeeks"
                                        class="form-select border shadow-sm fw-bold py-2 ps-3 pe-5"
                                        style="border-radius: 12px; background: #ffffff; border-color: #e2e8f0 !important; color: #1e293b; font-size: 0.85rem; cursor: pointer; appearance: none !important; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23a44390%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 1rem center; background-size: 1.2em;"
                                        onchange="updateFinalPrice()">
                                        <option value="1">1 Week Duration</option>
                                        <option value="2">2 Weeks Recurring</option>
                                        <option value="3">3 Weeks Recurring</option>
                                        <option value="4">4 Weeks (1 Month)</option>
                                        <option value="5">5 Weeks Recurring</option>
                                        <option value="6">6 Weeks Recurring</option>
                                        <option value="7">7 Weeks Recurring</option>
                                        <option value="8">8 Weeks (2 Months)</option>
                                        <option value="9">9 Weeks Recurring</option>
                                        <option value="10">10 Weeks Recurring</option>
                                        <option value="11">11 Weeks Recurring</option>
                                        <option value="12">12 Weeks (Quarterly)</option>
                                    </select>
                                </div>

                                <div class="p-2 mb-3 rounded-4"
                                    style="background: #fdf2fb; border: 1px dashed #a44390;">
                                    <span class="text-muted d-block mb-1 fw-bold"
                                        style="font-size: 0.65rem; text-uppercase; letter-spacing: 0.5px;">Total Service
                                        Amount</span>
                                    <h4 class="fw-bold mb-0" id="finalTotalAmountText" style="color: #a44390;">£ 0.00
                                    </h4>
                                </div>

                                <button class="btn w-100 py-2 fw-bold text-white shadow-sm"
                                    style="background: linear-gradient(135deg, #a44390, #6d2e67); border-radius: 12px; border: none; font-size: 0.95rem; transition: all 0.2s;"
                                    onmouseover="this.style.opacity='0.9';" onmouseout="this.style.opacity='1';"
                                    id="bookServiceBtn">
                                    Book Service Now
                                </button>
                                <p class="small text-muted mt-3 mb-0" style="font-size: 0.7rem;">Secure payment via
                                    CosyChats Checkout</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Video Popup Modal -->
<div class="modal fade" id="videoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 bg-transparent">
            <div class="modal-header border-0 p-0 mb-2 justify-content-end">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="ratio ratio-16x9 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                    <iframe id="videoIframe" src="" title="Video Intro" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // ===== Custom Premium Calendar =====
    let currentDate = new Date();
    let selectedDate = null;
    let selectedTimeSlotsByDay = {};

    function renderCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'];
        document.getElementById('currentMonthYear').textContent = monthNames[month] + ' ' + year;

        const firstDay = new Date(year, month, 1).getDay(); // 0=Sun
        const offset = (firstDay === 0) ? 6 : firstDay - 1;
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const today = new Date();

        const container = document.getElementById('calendarDays');
        container.innerHTML = '';

        for (let i = 0; i < offset; i++) {
            container.innerHTML += `<div></div>`;
        }

        for (let d = 1; d <= daysInMonth; d++) {
            const cellDate = new Date(year, month, d);
            const isPast = cellDate < new Date(today.getFullYear(), today.getMonth(), today.getDate());
            const isToday = cellDate.toDateString() === today.toDateString();
            const isSelected = selectedDate && cellDate.toDateString() === selectedDate.toDateString();

            let bg = '#f8fafc';
            let color = '#1e293b';
            let border = '1px solid transparent';
            let fontWeight = '600';

            if (isPast) {
                bg = 'transparent'; color = '#cbd5e1';
            } else if (isSelected) {
                bg = '#fff'; color = '#a44390'; border = '1.5px solid #a44390'; fontWeight = '700';
            } else if (isToday) {
                bg = '#fdf2fb'; color = '#a44390'; border = '1.5px solid #a44390'; fontWeight = '700';
            }

            container.innerHTML += `
            <div onclick="${isPast ? '' : 'selectDay(this, ' + d + ')'}" 
                 data-day="${d}" data-month="${month}" data-year="${year}"
                 style="aspect-ratio:1; display:flex; align-items:center; justify-content:center;
                        font-size:0.85rem; font-weight:${fontWeight}; border-radius:12px;
                        background:${bg}; color:${color}; border:${border};
                        cursor:${isPast ? 'not-allowed' : 'pointer'}; transition:all 0.2s;">
                ${d}
            </div>`;
        }
    }

    function selectDay(el, day) {
        const year = parseInt(el.dataset.year);
        const month = parseInt(el.dataset.month);
        selectedDate = new Date(year, month, day);
        renderCalendar();

        const bookingSection = document.getElementById('bookingTimeSlots');
        const displayDateText = document.getElementById('displaySelectedDate');
        const slotsList = document.getElementById('timeSlotsList');

        if (bookingSection) {
            bookingSection.style.display = 'block';
            displayDateText.textContent = selectedDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });

            slotsList.innerHTML = '';
            const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

            let addedCount = 0;
            let dayOffset = 0;

            while (addedCount < 6 && dayOffset < 14) {
                const nextDate = new Date(selectedDate);
                nextDate.setDate(selectedDate.getDate() + dayOffset);
                const dayIndex = nextDate.getDay();
                const dateStr = nextDate.toDateString();

                dayOffset++;
                if (dayIndex === 0) continue; // Skip Sundays
                addedCount++;

                const duration = selectedTimeSlotsByDay[dateStr] ? selectedTimeSlotsByDay[dateStr].length * 15 : 0;

                slotsList.innerHTML += `
                <div class="d-flex align-items-center justify-content-between p-3 mb-2 rounded-4 border bg-white" 
                     style="border-color: #f1f5f9 !important; transition: all 0.2s;">
                    <div class="text-start">
                        <h6 class="fw-bold mb-1" style="color: #1e293b; font-size: 0.9rem;">${dayNames[dayIndex]}</h6>
                        <p class="small text-muted mb-0" id="duration-${dateStr}" style="font-size: 0.75rem;">${duration} minutes Call Duration</p>
                    </div>
                    <button onclick="openTimeSlotModal('${dateStr}')" class="btn btn-sm px-3 py-2 fw-bold text-white shadow-sm" 
                            style="background: linear-gradient(135deg, #a44390, #c25ca9); border-radius: 12px; border: none; font-size: 0.7rem;">
                        ${duration > 0 ? 'Edit Time' : 'Select Time'}
                    </button>
                </div>
            `;
            }
        }
    }

    function changeMonth(dir) {
        currentDate.setMonth(currentDate.getMonth() + dir);
        renderCalendar();
    }

    document.addEventListener('DOMContentLoaded', renderCalendar);

    // ===== Modal Booking Logic =====
    let currentModalDate = '';

    function openTimeSlotModal(dateStr) {
        currentModalDate = dateStr;
        const modal = new bootstrap.Modal(document.getElementById('timeSlotModal'));
        const grid = document.getElementById('timeGrid');
        grid.innerHTML = '';

        for (let hour = 9; hour < 21; hour++) {
            for (let min = 0; min < 60; min += 15) {
                const timeStr = `${hour.toString().padStart(2, '0')}:${min.toString().padStart(2, '0')}`;
                const isSelected = selectedTimeSlotsByDay[dateStr] && selectedTimeSlotsByDay[dateStr].includes(timeStr);

                grid.innerHTML += `
                <div class="time-block p-2 text-center small fw-bold ${isSelected ? 'selected' : ''}" 
                     onclick="toggleTimeSlot('${timeStr}', this)">
                    ${timeStr}
                </div>
            `;
            }
        }
        updateModalDuration();
        modal.show();
    }

    function toggleTimeSlot(time, el) {
        if (!selectedTimeSlotsByDay[currentModalDate]) selectedTimeSlotsByDay[currentModalDate] = [];
        const index = selectedTimeSlotsByDay[currentModalDate].indexOf(time);
        if (index > -1) {
            selectedTimeSlotsByDay[currentModalDate].splice(index, 1);
            el.classList.remove('selected');
        } else {
            selectedTimeSlotsByDay[currentModalDate].push(time);
            el.classList.add('selected');
        }
        updateModalDuration();
    }

    function updateModalDuration() {
        const count = selectedTimeSlotsByDay[currentModalDate] ? selectedTimeSlotsByDay[currentModalDate].length : 0;
        const durationEl = document.getElementById('modalTotalDuration');
        if (durationEl) durationEl.textContent = `${count * 15} minutes`;
    }

    function confirmTimeSlots() {
        const modalEl = document.getElementById('timeSlotModal');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();

        // Update Sidebar Row
        const selectedSlots = selectedTimeSlotsByDay[currentModalDate] || [];
        const duration = selectedSlots.length * 15;
        const durationTextEl = document.getElementById(`duration-${currentModalDate}`);
        if (durationTextEl) {
            durationTextEl.textContent = `${duration} minutes Call Duration`;
            const btn = durationTextEl.closest('.d-flex').querySelector('button');
            if (btn) btn.textContent = duration > 0 ? 'Edit Time' : 'Select Time';
        }

        // Pricing visibility
        let grandTotalMin = 0;
        for (const d in selectedTimeSlotsByDay) {
            grandTotalMin += selectedTimeSlotsByDay[d].length * 15;
        }

        const pricingSection = document.getElementById('weeklyPricingSection');
        if (pricingSection) {
            if (grandTotalMin > 0) {
                pricingSection.style.display = 'block';
                updateFinalPrice();
            } else {
                pricingSection.style.display = 'none';
            }
        }
    }

    function updateFinalPrice() {
        let totalMinutes = 0;
        for (const d in selectedTimeSlotsByDay) {
            totalMinutes += selectedTimeSlotsByDay[d].length * 15;
        }
        const weeks = parseInt(document.getElementById('totalBookingWeeks').value) || 1;
        const totalHours = (totalMinutes / 60) * weeks;
        const totalPrice = totalHours * (selectedService ? selectedService.price : 0);
        const amountText = document.getElementById('finalTotalAmountText');
        if (amountText) amountText.textContent = `£ ${totalPrice.toFixed(2)}`;
    }

    // ===== Extra Utilities =====
    function openVideoPopup(url) {
        const modal = new bootstrap.Modal(document.getElementById('videoModal'));
        const iframe = document.getElementById('videoIframe');
        let embedUrl = url;
        if (url.includes('youtube.com/watch?v=')) embedUrl = url.replace('watch?v=', 'embed/');
        else if (url.includes('youtu.be/')) embedUrl = url.replace('youtu.be/', 'youtube.com/embed/');
        iframe.src = embedUrl;
        modal.show();
    }

    document.addEventListener('DOMContentLoaded', () => {
        const stars = document.querySelectorAll('.rating-star');
        const ratingInput = document.getElementById('selectedRating');
        if (!stars.length) return;

        stars.forEach(star => {
            star.addEventListener('mouseover', function () { highlightStars(this.dataset.rating); });
            star.addEventListener('mouseout', function () { highlightStars(ratingInput.value); });
            star.addEventListener('click', function () {
                ratingInput.value = this.dataset.rating;
                highlightStars(ratingInput.value);
            });
        });

        function highlightStars(val) {
            stars.forEach(s => {
                if (s.dataset.rating <= val) {
                    s.classList.replace('far', 'fas');
                    s.style.color = '#ffb800';
                } else {
                    s.classList.replace('fas', 'far');
                    s.style.color = '#cbd5e1';
                }
            });
        }
    });
</script>

<style>
    .btn-video-overlay:hover {
        background: #a44390 !important;
        color: #fff !important;
        transform: scale(1.1);
    }
</style>

<style>
    .time-grid-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
    }

    .time-block {
        cursor: pointer;
        transition: all 0.2s ease;
        border-radius: 10px !important;
        background: #fff;
        border: 1.5px solid #edf2f7;
        color: #475569;
        font-weight: 600;
    }

    .time-block:hover {
        border-color: #a44390 !important;
        background: #fdf2fb !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(164, 67, 144, 0.1);
    }

    .time-block.selected {
        background: #a44390 !important;
        color: #fff !important;
        border-color: #a44390 !important;
    }

    @media (max-width: 576px) {
        .time-grid-container {
            grid-template-columns: repeat(3, 1fr);
        }
    }
</style>

<!-- Time Slot Selection Modal -->
<div class="modal fade" id="timeSlotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 pb-0 justify-content-between align-items-center p-4">
                <div class="d-flex align-items-center gap-3">
                    <div
                        style="width: 40px; height: 40px; background: #fdf2fb; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-clock" style="color: #a44390;"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0" style="color: #1e293b; letter-spacing: -0.5px;">Select Call Start Time
                        </h5>
                        <small class="text-muted fw-medium">Additional call blocks can be selected</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex gap-4 mb-4 small fw-medium justify-content-center">
                    <span class="d-flex align-items-center gap-2">
                        <span
                            style="width: 12px; height: 12px; background: #fff; border: 1.5px solid #edf2f7; border-radius: 3px;"></span>
                        Available
                    </span>
                    <span class="d-flex align-items-center gap-2">
                        <span style="width: 12px; height: 12px; background: #a44390; border-radius: 3px;"></span>
                        Selected
                    </span>
                    <span class="d-flex align-items-center gap-2">
                        <span style="width: 12px; height: 12px; background: #e2e8f0; border-radius: 3px;"></span> Booked
                    </span>
                </div>

                <div id="timeGrid" class="time-grid-container p-3 rounded-4"
                    style="background: #f8fafc; border: 1px solid #edf2f7;">
                    <!-- Time blocks generated by JS -->
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-2">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div class="text-start">
                        <small class="text-muted d-block fw-bold text-uppercase"
                            style="font-size: 0.65rem; letter-spacing: 0.5px;">Total Duration</small>
                        <span id="modalTotalDuration" class="fw-bold" style="color: #a44390; font-size: 1.1rem;">0
                            minutes</span>
                    </div>
                    <button type="button" class="btn px-4 py-2 fw-bold text-white shadow-sm"
                        onclick="confirmTimeSlots()"
                        style="background: linear-gradient(135deg, #a44390, #6d2e67); border-radius: 12px; border: none; min-width: 140px;">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer() ?>