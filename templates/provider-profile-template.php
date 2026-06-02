<?php
get_header();
$author_slug = get_query_var('author_name');
$common = new class {
    use \Cosy\Appointments\Common\GlobalCommonFunctions;
};
$provider_data = $common->get_provider_with_services($author_slug);


$current_user = wp_get_current_user();
$is_logged_in = is_user_logged_in();
$user_role = !empty($current_user->roles) ? reset($current_user->roles) : '';
$is_customer = in_array('customer', (array) $current_user->roles);

$approved_reviews = [];
$total_reviews = 0;
$average_rating = 0;
if (!empty($provider_data['ID'])) {
    $reviews_data = $common->get_provider_reviews($provider_data['ID'], true);
    $approved_reviews = $reviews_data['approved'];
    $total_reviews = $reviews_data['total_approved'];
    $average_rating = $reviews_data['average_rating'];
}

/** 
 * PROVIDER AVAILABILITY DATA FETCHING
 * 
 * Retrieves the weekly schedule and holidays via reusable OOP helper function.
 */
$availability = [];
$holiday_dates = [];
if (!empty($provider_data['ID'])) {
    $availability_data = $common->get_provider_availability_data($provider_data['ID']);
    $availability      = $availability_data['availability'];
    $holiday_dates     = $availability_data['holiday_dates'];
}
?>

<!-- 
    Global JavaScript Object: Exposes provider availability data to the frontend.
    Allows interactive components (like booking calendars) to access slots in real-time.
-->
<script>
    window.providerAvailability = <?php echo wp_json_encode($availability); ?>;
    window.providerHolidays = <?php echo wp_json_encode($holiday_dates); ?>;
    window.currentUser = {
        isLoggedIn: <?php echo $is_logged_in ? 'true' : 'false'; ?>,
        role: <?php echo wp_json_encode($user_role); ?>,
        name: <?php echo wp_json_encode($current_user->display_name); ?>,
        id: <?php echo wp_json_encode($current_user->ID); ?>
    };
    window.providerId = <?php echo wp_json_encode($provider_data['ID'] ?? 0); ?>;
    window.providerName = <?php echo wp_json_encode($provider_data['first_name'] ?? ''); ?>;
    window.ajaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
    window.checkoutUrl = <?php echo wp_json_encode(cosy_get_page_url('cosy-checkout')); ?>;
    window.nonce = <?php echo wp_json_encode(wp_create_nonce('cosy_calendar_nonce')); ?>;
    window.serviceFeeType = <?php echo wp_json_encode(get_option('cosy_service_fee_type', 'flat')); ?>;
    window.serviceFeeValue = <?php echo wp_json_encode(floatval(get_option('cosy_service_fee_value', '0.10'))); ?>;
</script>

<?php

?>
<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-7">

            <div class="cosy-card-rounded card border-0 shadow-sm mb-4 overflow-hidden">
                <div class="cosy-gradient-bg card-header border-0 py-4 px-5">
                    <div class="d-flex align-items-center flex-wrap gap-4">
                        <div class="cosy-profile-img-wrap profile-avatar-wrapper-premium">
                            <?php
                            $profile_image = !empty($provider_data['profile_image']) ? $provider_data['profile_image'] : 'https://via.placeholder.com/120';
                            ?>
                            <img src="<?php echo esc_url($profile_image); ?>"
                                class="cosy-profile-img"
                                alt="<?php echo esc_attr($provider_data['first_name']); ?>">
                        </div>
                        <div class="profile-info-top">
                            <?php
                            if (!empty($provider_data['first_name'])) { ?>
                                <h2 class="mb-2 fw-bold h4">
                                    <?php echo esc_html($provider_data['first_name']); ?>
                                </h2>
                            <?php } ?>
                            <div class="d-flex gap-3 opacity-75 small fw-medium">
                                <?php if (!empty($provider_data['gender'])): ?>
                                    <span><i class="fas fa-venus me-1"></i>
                                        <?php echo esc_html(ucwords(strtolower($provider_data['gender']))); ?></span>
                                <?php endif ?>
                                <span><i class="fas fa-user-check me-1"></i> <?php esc_html_e('Verified Specialist', 'cosy-appointments'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="cosy-bg-fafbfc row text-center g-0 border-bottom">
                        <div class="col-4 py-3">
                            <?php
                            if (!empty($provider_data['services'])):
                                // Saari services ke prices nikaal kar sab se kam (minimum) price dhoondna
                                $prices = array_column($provider_data['services'], 'price');
                                $min_price = !empty($prices) ? min($prices) : '0.00';
                            ?>
                                <div class="cosy-price-min h5 fw-bold mb-1">
                                    <?php echo esc_html(cosy_get_currency_symbol()); ?><?php echo esc_html($min_price); ?>
                                </div>
                                <small class="cosy-price-label text-muted text-uppercase fw-bold">
                                    <?php esc_html_e('Starting From Hourly Rate', 'cosy-appointments'); ?>
                                </small>
                            <?php endif; ?>
                        </div>
                        <div class="col-4 py-3 border-start border-end">
                            <div class="cosy-section-title h5 fw-bold mb-1 text-warning"><i
                                    class="cosy-rating-star fas fa-star me-1"></i><?php echo ($average_rating > 0) ? number_format($average_rating, 1) : '0.0'; ?></div>

                            <small class="cosy-price-label text-muted text-uppercase fw-bold"><?php echo esc_html(sprintf(_n('(%s Review)', '(%s Reviews)', $total_reviews, 'cosy-appointments'), esc_html($total_reviews))); ?></small>
                        </div>
                        <div class="col-4 py-3">
                            <?php if (!empty($provider_data['age_group'])) { ?>
                                <div class="cosy-age-group h5 fw-bold mb-1">
                                    <?php echo esc_html($provider_data['age_group']); ?>
                                </div>
                                <small class="cosy-price-label text-muted text-uppercase fw-bold"><?php esc_html_e('Age Group', 'cosy-appointments'); ?></small>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="card-body py-4 px-5">
                    <p class="text-muted text-center italic mb-0" style="font-size: 0.95rem;">
                        <?php esc_html_e('Experience premium sessions tailored to your needs with our verified specialists.', 'cosy-appointments'); ?>
                    </p>
                </div>
            </div>

            <!-- Separate Services Section -->
            <?php if (!empty($provider_data['services'])): ?>
                <div class="cosy-card-rounded card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 px-5">
                        <div class="cosy-border-f1f5f9 d-flex align-items-center gap-3 mb-3 pb-2 border-bottom">
                            <div class="cosy-icon-box">
                                <i class="cosy-total-price fas fa-concierge-bell"></i>
                            </div>
                            <h5 class="cosy-price-min fw-bold mb-0"><?php esc_html_e('Offered Services', 'cosy-appointments'); ?></h5>
                        </div>
                        <div class="services-list-premium">
                            <?php foreach ($provider_data['services'] as $service):
                                $s_id = intval($service['ID']);
                                $s_title = esc_js($service['title']);
                                $s_price = esc_js($service['price']);
                                $s_time = esc_js($service['time'] ?? '60');
                            ?>
                                <div class="cosy-service-row service-item-row d-flex justify-content-between align-items-center p-3 mb-3 cursor-pointer"
                                    onclick="selectServiceItem(this, <?php echo $s_id; ?>, '<?php echo $s_title; ?>', <?php echo $s_price; ?>, <?php echo $s_time; ?>)">

                                    <div class="d-flex align-items-center gap-3">
                                        <div class="cosy-service-check-box">
                                            <i class="cosy-service-check-icon fas fa-check-circle service-check-icon"></i>
                                        </div>
                                        <div>
                                            <h6 class="cosy-service-title mb-0 fw-bold">
                                                <?php echo esc_html($service['title']); ?>
                                            </h6>
                                            <small class="text-muted"><?php printf(esc_html__('%s mins session', 'cosy-appointments'), esc_html($service['time'] ?? '60')); ?></small>
                                        </div>
                                    </div>
                                    <div class="cosy-service-price-box">
                                        <?php echo esc_html(cosy_get_currency_symbol()); ?><?php echo esc_html($service['price']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="cosy-card-rounded card border-0 shadow-sm mb-4">
                <div class="card-body p-4 px-5">
                    <div class="cosy-border-f1f5f9 d-flex align-items-center gap-3 mb-3 pb-2 border-bottom">
                        <div class="cosy-icon-box">
                            <i class="cosy-total-price fa-solid fa-user"></i>
                        </div>
                        <h5 class="cosy-price-min fw-bold mb-0"><?php esc_html_e('About Me', 'cosy-appointments'); ?></h5>
                    </div>
                    <p class="cosy-about-desc text-muted lh-lg mb-0">
                        <?php echo nl2br(esc_html($provider_data['description'])); ?>
                    </p>
                </div>
            </div>

            <div class="cosy-card-rounded card border-0 shadow-sm mb-4">
                <div class="card-body p-4 px-5">
                    <div class="cosy-border-f1f5f9 d-flex align-items-center gap-3 mb-3 pb-2 border-bottom">
                        <div class="cosy-icon-box">
                            <i class="cosy-total-price fa-solid fa-calendar-check"></i>
                        </div>
                        <h5 class="cosy-price-min fw-bold mb-0"><?php esc_html_e('Availability', 'cosy-appointments'); ?></h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table border-0 mb-0">
                            <tbody class="text-secondary small">
                                <?php
                                $days_display = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                foreach ($days_display as $day):
                                    $day_avail = $availability[$day];
                                    $is_available = !empty($day_avail) && !empty($day_avail['start_time']) && !empty($day_avail['end_time']);

                                    // Skip unavailable days
                                    if (!$is_available) continue;
                                ?>
                                    <tr>
                                        <td class="border-0 fw-bold py-3 text-dark">
                                            <?php echo esc_html($day); ?>
                                        </td>
                                        <td class="border-0 text-end py-3">
                                            <?php
                                            $start = date("h:i A", strtotime($day_avail['start_time']));
                                            $end = date("h:i A", strtotime($day_avail['end_time']));
                                             $is_break_valid = false;
                                             if (!empty($day_avail['break_start']) && !empty($day_avail['break_end'])) {
                                                 $start_ts = strtotime($day_avail['start_time']);
                                                 $end_ts = strtotime($day_avail['end_time']);
                                                 $b_start_ts = strtotime($day_avail['break_start']);
                                                 $b_end_ts = strtotime($day_avail['break_end']);
                                                 
                                                 if ($b_start_ts > $start_ts && $b_end_ts < $end_ts) {
                                                     $is_break_valid = true;
                                                 }
                                             }

                                             if ($is_break_valid) {
                                                 $b_start = date("h:i A", strtotime($day_avail['break_start']));
                                                 $b_end = date("h:i A", strtotime($day_avail['break_end']));
                                                 echo esc_html($start . " - " . $b_start . " & " . $b_end . " - " . $end);
                                             } else {
                                                 echo esc_html($start . " - " . $end);
                                             }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="cosy-card-rounded card border-0 shadow-sm mb-4">
                <div class="card-body p-4 px-5">
                    <div class="cosy-border-f1f5f9 d-flex align-items-center justify-content-between gap-3 mb-3 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            <div class="cosy-icon-box">
                                <i class="cosy-total-price fa-solid fa-comment-dots"></i>
                            </div>
                            <h5 class="cosy-price-min fw-bold mb-0"><?php esc_html_e('Reviews', 'cosy-appointments'); ?></h5>
                        </div>
                        <button class="cosy-btn-add-review btn btn-sm text-white px-3" id="addReviewBtn">
                            <?php echo '+ ' . esc_html__('Add Review', 'cosy-appointments'); ?>
                        </button>
                    </div>

                    <div class="collapse mb-4" id="reviewForm">
                        <div class="cosy-review-form-box p-4 rounded-4">
                            <label class="small fw-bold text-muted mb-2 d-block"><?php esc_html_e('Rating', 'cosy-appointments'); ?></label>
                            <div class="star-rating-input d-flex gap-2 mb-3">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="cosy-rating-star-btn fa-star far cursor-pointer rating-star" data-rating="<?php echo esc_attr($i); ?>"></i>
                                <?php endfor; ?>
                                <input type="hidden" name="rating" id="selectedRating" value="0">
                            </div>

                            <label class="small fw-bold text-muted mb-2 d-block"><?php esc_html_e('Your Review', 'cosy-appointments'); ?></label>
                            <textarea class="cosy-review-textarea form-control mb-3 border-0 shadow-sm" rows="3"
                                id="reviewText"
                                placeholder="<?php esc_attr_e('Share your experience...', 'cosy-appointments'); ?>"></textarea>

                            <button class="cosy-btn-primary btn w-100 py-2 fw-bold text-white shadow-sm" id="postReviewBtn">
                                <?php esc_html_e('Post Review', 'cosy-appointments'); ?>
                            </button>
                        </div>
                    </div>

                    <div class="reviews-list-container d-flex flex-column gap-3">
                        <?php if (!empty($approved_reviews)): ?>
                            <?php foreach ($approved_reviews as $rev): ?>
                                <div class="cosy-border-f1f5f9 d-flex gap-3 pb-3 border-bottom animate__animated animate__fadeIn">
                                    <div class="cosy-review-avatar rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 fw-bold text-uppercase">
                                        <?php echo esc_html(substr($rev['customer_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?php echo esc_html($rev['customer_name']); ?></h6>
                                        <small class="text-warning">
                                            <?php for ($star = 1; $star <= 5; $star++): ?>
                                                <i class="<?php echo ($star <= $rev['rating']) ? 'fa-solid' : 'fa-regular'; ?> fa-star"></i>
                                            <?php endfor; ?>
                                        </small>
                                        <p class="cosy-review-text small text-muted mb-0 mt-1"><?php echo esc_html($rev['review']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted small mb-0"><?php esc_html_e('No reviews yet for this provider.', 'cosy-appointments'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="sticky-top" style="top: 20px;">
                <!-- Calendar Card -->
                <div class="cosy-card-rounded card border-0 shadow-sm mb-4">
                    <div class="p-4 pb-0">
                        <div class="cosy-border-f1f5f9 d-flex align-items-center gap-3 mb-4 pb-2 border-bottom">
                            <div class="cosy-icon-box">
                                <i class="cosy-total-price fas fa-calendar-alt"></i>
                            </div>
                            <h5 class="cosy-price-min fw-bold mb-0"><?php esc_html_e('Select Date', 'cosy-appointments'); ?></h5>
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
                                <div class="cosy-cal-day-header">
                                    <?php echo esc_html($d); ?>
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
                                <?php esc_html_e('Selected', 'cosy-appointments'); ?>
                            </span>
                            <span style="display: flex; align-items: center; gap: 6px;">
                                <span
                                    style="width: 10px; height: 10px; border-radius: 50%; background: #e9d5e9; display: inline-block;"></span>
                                <?php esc_html_e('Available', 'cosy-appointments'); ?>
                            </span>
                            <span style="display: flex; align-items: center; gap: 6px;">
                                <span
                                    style="width: 10px; height: 10px; border-radius: 50%; background: #e2e8f0; display: inline-block;"></span>
                                <?php esc_html_e('Unavailable', 'cosy-appointments'); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div id="bookingTimeSlots" class="card border-0 shadow-sm"
                    style="display: none; border-radius: 24px; overflow: hidden; background: #fff;">
                    <div class="card-body p-4 pb-2">
                        <div class="cosy-border-f1f5f9 d-flex align-items-center gap-3 mb-4 pb-2 border-bottom">
                            <div class="cosy-icon-box">
                                <i class="cosy-total-price fas fa-clock"></i>
                            </div>
                            <h5 class="cosy-price-min fw-bold mb-0"><?php esc_html_e('Call Schedule', 'cosy-appointments'); ?></h5>
                        </div>


                        <div class="bookingForAnother">
                            <input class="cosy-checkbox" type="checkbox" id="bookingForAnother">
                            <label for="bookingForAnother" class="cosy-slot-duration-text small text-muted fw-bold mb-0 cursor-pointer"><?php esc_html_e('Booking for another person', 'cosy-appointments'); ?></label>
                        </div>

                        <div class="cosy-date-display-box p-2 px-3 fw-bold text-dark mb-3 d-flex align-items-center justify-content-between">
                            <span><i class="fas fa-calendar-day me-2 text-muted"></i> <?php esc_html_e('Start Date:', 'cosy-appointments'); ?></span>
                            <span class="cosy-date-text" id="displaySelectedDate"><?php esc_html_e('May 13, 2026', 'cosy-appointments'); ?></span>
                        </div>

                        <div id="timeSlotsList" class="d-flex flex-column gap-2 mb-0">
                            <!-- Rows will be populated by JS -->
                        </div>

                        <!-- DYNAMIC WEEKLY PRICING SECTION -->
                        <div id="weeklyPricingSection" style="display: none;" class="mt-4 pt-4 border-top">
                            <div class="text-center">
                                <p class="cosy-duration-label small text-muted fw-bold mb-3 text-uppercase"><?php esc_html_e('Select Booking Duration', 'cosy-appointments'); ?></p>

                                <div class="px-2 mb-3 position-relative">
                                    <select id="totalBookingWeeks"
                                        class="form-select border shadow-sm fw-bold py-2 ps-3 pe-5"
                                        style="border-radius: 12px; background: #ffffff; border-color: #e2e8f0; color: #1e293b; font-size: 0.85rem; cursor: pointer; appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%22%20fill%3D%22none%22%20stroke%3D%22%23a44390%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 1rem center; background-size: 1.2em;"
                                        onchange="updateFinalPrice()">
                                        <option value="1"><?php esc_html_e('1 Week Duration', 'cosy-appointments'); ?></option>
                                        <option value="2"><?php esc_html_e('2 Weeks Recurring', 'cosy-appointments'); ?></option>
                                        <option value="3"><?php esc_html_e('3 Weeks Recurring', 'cosy-appointments'); ?></option>
                                        <option value="4"><?php esc_html_e('4 Weeks (1 Month)', 'cosy-appointments'); ?></option>
                                        <option value="5"><?php esc_html_e('5 Weeks Recurring', 'cosy-appointments'); ?></option>
                                        <option value="6"><?php esc_html_e('6 Weeks Recurring', 'cosy-appointments'); ?></option>
                                        <option value="7"><?php esc_html_e('7 Weeks Recurring', 'cosy-appointments'); ?></option>
                                        <option value="8"><?php esc_html_e('8 Weeks (2 Months)', 'cosy-appointments'); ?></option>
                                        <option value="9"><?php esc_html_e('9 Weeks Recurring', 'cosy-appointments'); ?></option>
                                        <option value="10"><?php esc_html_e('10 Weeks Recurring', 'cosy-appointments'); ?></option>
                                        <option value="11"><?php esc_html_e('11 Weeks Recurring', 'cosy-appointments'); ?></option>
                                        <option value="12"><?php esc_html_e('12 Weeks (Quarterly)', 'cosy-appointments'); ?></option>
                                    </select>
                                </div>

                                <div class="cosy-total-amount-box p-2 mb-3 rounded-4">
                                    <span class="cosy-total-label text-muted d-block mb-1 fw-bold"><?php esc_html_e('Total Service Amount', 'cosy-appointments'); ?></span>
                                    <h4 class="cosy-total-price fw-bold mb-0" id="finalTotalAmountText"><?php echo esc_html(cosy_get_currency_symbol()); ?> 0.00
                                    </h4>
                                </div>

                                <?php if ($is_logged_in && $is_customer): ?>
                                    <button class="cosy-btn-book-now btn w-100 py-2 fw-bold text-white shadow-sm"
                                        onmouseover="this.style.opacity='0.9';" onmouseout="this.style.opacity='1';"
                                        id="bookServiceBtn">
                                        <?php esc_html_e('Book Service Now', 'cosy-appointments'); ?>
                                    </button>
                                <?php else: ?>
                                    <div class="alert alert-warning py-3 px-3 mb-0 text-center fw-bold d-flex align-items-center justify-content-center gap-2"
                                        style="border-radius: 12px; font-size: 0.8rem; background: #fffbeb; border: 1px solid #fef3c7; color: #d97706; font-family: var(--cosy-font-family);">
                                        <i class="cosy-login-alert-icon fas fa-lock"></i>
                                        <span><?php esc_html_e('Please log in as a Customer to book this service.', 'cosy-appointments'); ?></span>
                                    </div>
                                <?php endif; ?>
                                <p class="cosy-secure-payment-text small text-muted mt-3 mb-0"><?php esc_html_e('Secure payment via CosyChats Checkout', 'cosy-appointments'); ?></p>
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
                <div class="cosy-video-modal-ratio ratio ratio-16x9 shadow-lg">
                    <iframe id="videoIframe" src="" title="Video Intro" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- // ===== Custom Premium Calendar ===== -->
<?php
wp_enqueue_script(
    'provider-profile-js',
    COSY_APPT_URL . 'src/Assets/js/calendar.js',
    ['jquery', 'bootstrap-bundle', 'sweetalert2'],
    COSY_APPT_VER,
    true
);
wp_localize_script('provider-profile-js', 'cosyCalendar', [
    'currencySymbol' => cosy_get_currency_symbol()
]);
?>

<!-- Time Slot Selection Modal -->
<div class="modal fade" id="timeSlotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="cosy-card-rounded modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0 justify-content-between align-items-center p-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="cosy-modal-icon-box">
                        <i class="cosy-total-price fas fa-clock"></i>
                    </div>
                    <div>
                        <h5 class="cosy-age-group fw-bold mb-0"><?php esc_html_e('Select Call Start Time', 'cosy-appointments'); ?>
                        </h5>
                        <small class="text-muted fw-medium"><?php esc_html_e('Additional call blocks can be selected', 'cosy-appointments'); ?></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex gap-4 mb-4 small fw-medium justify-content-center">
                    <span class="d-flex align-items-center gap-2">
                        <span
                            style="width: 12px; height: 12px; background: #fff; border: 1.5px solid #edf2f7; border-radius: 3px;"></span>
                        <?php esc_html_e('Available', 'cosy-appointments'); ?>
                    </span>
                    <span class="d-flex align-items-center gap-2">
                        <span style="width: 12px; height: 12px; background: #a44390; border-radius: 3px;"></span>
                        <?php esc_html_e('Selected', 'cosy-appointments'); ?>
                    </span>
                    <span class="d-flex align-items-center gap-2">
                        <span style="width: 12px; height: 12px; background: #e2e8f0; border-radius: 3px;"></span> <?php esc_html_e('Booked', 'cosy-appointments'); ?>
                    </span>
                </div>
                <!-- Time blocks generated by JS -->
                <div id="timeGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(105px, 1fr)); gap: 10px;"></div>
            </div>
            <div class="modal-footer border-0 p-4 pt-2">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div class="text-start">
                        <small class="cosy-modal-total-duration-label text-muted d-block fw-bold text-uppercase"><?php esc_html_e('Total Duration', 'cosy-appointments'); ?></small>
                        <span id="modalTotalDuration" class="cosy-modal-total-duration-val fw-bold">0
                            <?php esc_html_e('minutes', 'cosy-appointments'); ?></span>
                    </div>
                    <button type="button" class="cosy-modal-confirm-btn btn px-4 py-2 fw-bold text-white shadow-sm"
                        onclick="confirmTimeSlots()">
                        <?php esc_html_e('Confirm', 'cosy-appointments'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer() ?>