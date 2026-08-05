<?php
get_header();
$queried_obj = get_queried_object();
$author_slug = get_query_var('author_name');
if (empty($author_slug) && $queried_obj instanceof \WP_User) {
    $author_slug = $queried_obj->user_nicename ?: $queried_obj->user_login;
}

$common = new class {
    use \Cosy\Appointments\Common\GlobalCommonFunctions;
};
$provider_data = $common->get_provider_with_services($author_slug);
if (empty($provider_data['ID']) && $queried_obj instanceof \WP_User) {
    $provider_data = array_merge($common->get_provider_data($queried_obj->ID), $provider_data);
    $provider_data['ID'] = $queried_obj->ID;
}

$current_user = wp_get_current_user();
$is_logged_in = is_user_logged_in();

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

// Initialize selected service object and parse service parameter from URL
$selected_service_obj = null;
$url_service = isset($_GET['service_name']) ? sanitize_text_field($_GET['service_name']) : (isset($_GET['service_category']) ? sanitize_text_field($_GET['service_category']) : (isset($_GET['category']) ? sanitize_text_field($_GET['category']) : ''));

// Search provider's assigned services for a matching service title or slug
if (!empty($provider_data['services'])) {
    if (!empty($url_service)) {
        $clean_url_srv = strtolower(trim(str_replace(['-', '_'], ' ', $url_service)));
        $slug_url_srv  = strtolower(trim(str_replace(' ', '-', $url_service)));
        foreach ($provider_data['services'] as $srv) {
            $srv_title_clean = strtolower(trim(str_replace(['-', '_'], ' ', $srv['title'])));
            $srv_title_slug  = strtolower(trim(str_replace(' ', '-', $srv['title'])));
            if ($srv_title_clean === $clean_url_srv || $srv_title_slug === $slug_url_srv || strpos($srv_title_clean, $clean_url_srv) !== false || strpos($clean_url_srv, $srv_title_clean) !== false) {
                $selected_service_obj = $srv;
                break;
            }
        }
    }
}

// Fallback logic to ensure title, ID, price, and slug are always defined
if ($selected_service_obj) {
    $selected_service_title = $selected_service_obj['title'];
    $selected_service_id    = $selected_service_obj['ID'];
    $selected_service_price = $selected_service_obj['price'];
} elseif (!empty($url_service)) {
    $selected_service_title = ucwords(str_replace(['-', '_'], ' ', $url_service));
    $selected_service_id    = !empty($provider_data['services'][0]['ID']) ? $provider_data['services'][0]['ID'] : 1;
    $selected_service_price = !empty($provider_data['services'][0]['price']) ? $provider_data['services'][0]['price'] : 0;
} else {
    $selected_service_obj   = !empty($provider_data['services'][0]) ? $provider_data['services'][0] : null;
    $selected_service_title = $selected_service_obj ? $selected_service_obj['title'] : 'Parent Conversation';
    $selected_service_id    = $selected_service_obj ? $selected_service_obj['ID'] : 1;
    $selected_service_price = $selected_service_obj ? $selected_service_obj['price'] : 0;
}
$selected_service_slug = !empty($url_service) ? $url_service : strtolower(str_replace(' ', '-', $selected_service_title));
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
    window.cosyDefaultService = {
        id: <?php echo wp_json_encode($selected_service_id); ?>,
        title: <?php echo wp_json_encode($selected_service_title); ?>,
        slug: <?php echo wp_json_encode($selected_service_slug); ?>,
        price: <?php echo wp_json_encode($selected_service_price); ?>,
        duration: 10
    };
    window.ajaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
    window.checkoutUrl = <?php echo wp_json_encode(cosy_get_page_url('cosy-checkout')); ?>;
    window.nonce = <?php echo wp_json_encode(wp_create_nonce('cosy_calendar_nonce')); ?>;
    window.serviceFeeType = 'percent';
    window.serviceFeeValue = <?php echo wp_json_encode(floatval(get_option('cosy_worldpay_charge', '0'))); ?>;
</script>

<main id="primary" class="site-main cosy-main-page-content">
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
                            <div class="d-flex align-items-center gap-3 flex-wrap mb-2">
                                <?php if (!empty($provider_data['first_name'])) { ?>
                                    <h2 class="mb-0 fw-bold h4 text-white">
                                        <?php echo esc_html($provider_data['first_name']); ?>
                                    </h2>
                                <?php } ?>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-3 opacity-75 small fw-medium text-white mt-1">
                                <?php if (!empty($provider_data['gender'])): ?>
                                    <span class="text-white"><i class="fas fa-venus me-1 text-white"></i>
                                        <?php echo esc_html(ucwords(strtolower($provider_data['gender']))); ?></span>
                                <?php endif ?>
                                <span class="text-white"><i class="fas fa-user-check me-1 text-white"></i> <?php esc_html_e('Verified Parent', 'cosy-appointments'); ?></span>

                                <?php if (!empty($selected_service_title)): ?>
                                    <span class="text-white"><i class="fas fa-tags me-1 text-white"></i> <?php echo esc_html($selected_service_title); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="cosy-bg-fafbfc row text-center g-0 border-bottom">
                        <div class="col py-3">
                            <?php
                            if (!empty($provider_data['services'])):
                                $display_price = (!empty($selected_service_price) && floatval($selected_service_price) > 0) ? $selected_service_price : min(array_column($provider_data['services'], 'price'));
                            ?>
                                <div class="cosy-price-min h5 fw-bold mb-1">
                                    <?php echo esc_html(cosy_get_currency_symbol()); ?><?php echo esc_html($display_price); ?>
                                </div>
                                <small class="cosy-price-label text-muted text-uppercase fw-bold">
                                    <?php esc_html_e('Starting From Hourly Rate', 'cosy-appointments'); ?>
                                </small>
                            <?php endif; ?>
                        </div>
                        <div class="col py-3 border-start"><?php if (!empty($provider_data['age_group'])) { ?>
                                <div class="cosy-age-group h5 fw-bold mb-1" style="color: #a44390;">
                                    <?php echo esc_html($provider_data['age_group']); ?>
                                </div>
                                <small class="cosy-price-label text-muted text-uppercase fw-bold"><?php esc_html_e('Age Group', 'cosy-appointments'); ?></small>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="card-body py-4 px-5">
                    <p class="text-muted text-center italic mb-0" style="font-size: 0.95rem;">
                        <?php esc_html_e('Could this parent\'s experiences be relevant to your own?', 'cosy-appointments'); ?>
                    </p>
                </div>
            </div>

            <div class="cosy-card-rounded card border-0 shadow-sm mb-4">
                <div class="card-body p-4 px-5">
                    <div class="cosy-border-f1f5f9 d-flex align-items-center gap-3 mb-3 pb-2 border-bottom">
                        <div class="cosy-icon-box">
                            <i class="cosy-total-price fa-solid fa-user"></i>
                        </div>
                        <h5 class="cosy-price-min fw-bold mb-0"><?php esc_html_e('My Story', 'cosy-appointments'); ?></h5>
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
                            <i class="cosy-total-price fa-solid fa-star"></i>
                        </div>
                        <h5 class="cosy-price-min fw-bold mb-0"><?php esc_html_e('Parent Reviews', 'cosy-appointments'); ?></h5>
                    </div>

                    <!-- Rating Score Summary (Matches Client Mockup) -->
                    <div class="mb-4">
                        <div class="fw-bold mb-1" style="font-size: 1.4rem; color: #a44390; letter-spacing: -0.5px;">
                            <?php if ($total_reviews > 0 && $average_rating > 0): ?>
                                <?php echo number_format($average_rating, 1); ?> <span style="font-size: 0.85em; color: #64748b; font-weight: 500;">/ 10</span>
                            <?php else: ?>
                                &mdash; <span style="font-size: 0.85em; color: #64748b; font-weight: 500;">/ 10</span>
                            <?php endif; ?>
                        </div>
                        <div class="fw-semibold mb-2" style="font-size: 0.95rem; color: #475569;">
                            <?php echo $total_reviews > 0 ? esc_html(sprintf(_n('%s Review', '%s Reviews', $total_reviews, 'cosy-appointments'), $total_reviews)) : esc_html__('No reviews yet', 'cosy-appointments'); ?>
                        </div>
                        <p class="fst-italic text-muted mb-0" style="font-size: 0.85rem; line-height: 1.5; color: #64748b !important;">
                            <?php esc_html_e('Reviews are only accepted from customers who have completed a conversation.', 'cosy-appointments'); ?>
                        </p>
                    </div>

                    <div class="reviews-list-container d-flex flex-column gap-3">
                        <?php if (!empty($approved_reviews)): ?>
                            <?php foreach ($approved_reviews as $idx => $rev): ?>

                                <?php if ($idx === 1) : ?>
                                    <div class="cosy-extra-reviews-wrapper d-flex flex-column gap-3" style="display: none !important;">
                                <?php endif; ?>

                                <div class="cosy-border-f1f5f9 d-flex gap-3 pb-3 border-bottom animate__animated animate__fadeIn">
                                    <div class="cosy-review-avatar rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 fw-bold text-uppercase">
                                        <?php echo esc_html(substr($rev['customer_name'], 0, 1)); ?>
                                    </div>
                                    <div class="flex-grow-1">
                                         <div class="d-flex align-items-center gap-2 mb-1">
                                             <h6 class="mb-0 fw-bold" style="color: #6d2e67; font-size: 0.98rem;"><?php echo esc_html($rev['customer_name']); ?></h6>
                                             <span class="badge fw-bold" style="background: #fdf5fc; color: #a44390; border: 1.5px solid rgba(164, 67, 144, 0.25); font-size: 0.8rem; padding: 3px 10px; border-radius: 8px; margin-left: 4px;">
                                                 <?php echo intval($rev['rating']); ?> / 10
                                             </span>
                                         </div>
                                         <p class="cosy-review-text small text-muted mb-0"><?php echo esc_html($rev['review']); ?></p>

                                        <!-- 3-Level Thread Replies Container -->
                                        <?php
                                        $replies = $rev['replies'] ?? [];
                                        
                                        // Fallback if legacy provider_reply exists but level 1 is missing in replies array
                                        $has_l1_tpl = false;
                                        foreach ($replies as $rep_check) {
                                            if (intval($rep_check['reply_level']) === 1) {
                                                $has_l1_tpl = true;
                                                break;
                                            }
                                        }
                                        if (!$has_l1_tpl && !empty($rev['provider_reply'])) {
                                            $prov_name = !empty($provider_data['first_name']) ? $provider_data['first_name'] : 'Parent';
                                            array_unshift($replies, [
                                                'id'          => 0,
                                                'review_id'   => $rev['id'],
                                                'sender_id'   => $provider_data['ID'] ?? 0,
                                                'sender_role' => 'provider',
                                                'sender_name' => $prov_name,
                                                'reply_text'  => $rev['provider_reply'],
                                                'reply_level' => 1,
                                                'created_at'  => $rev['reply_date'] ?: $rev['created_at']
                                            ]);
                                        }

                                        $has_level1 = false;
                                        $has_level2 = false;
                                        $has_level3 = false;

                                        foreach ($replies as $rep) {
                                            if (intval($rep['reply_level']) === 1) $has_level1 = true;
                                            if (intval($rep['reply_level']) === 2) $has_level2 = true;
                                            if (intval($rep['reply_level']) === 3) $has_level3 = true;
                                        }
                                        ?>

                                        <?php if (!empty($replies)) : ?>
                                            <div class="review-nested-thread cosy-review-thread-wrap">
                                                     <?php foreach ($replies as $rep) :
                                                        $r_level = intval($rep['reply_level']);
                                                        if ($r_level === 1) :
                                                    ?>
                                                            <!-- Level 1: Provider Initial Public Reply -->
                                                            <div class="thread-item level-1-item cosy-thread-provider-box">
                                                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-1">
                                                                    <span class="cosy-thread-sender-name">
                                                                        <i class="fas fa-reply cosy-thread-sender-icon-provider"></i>
                                                                        <?php echo esc_html($rep['sender_name']); ?>
                                                                    </span>
                                                                    <small class="cosy-thread-date"><?php echo date('d M Y - h:i A', strtotime($rep['created_at'])); ?></small>
                                                                </div>
                                                                <p class="cosy-thread-body-provider"><?php echo esc_html($rep['reply_text']); ?></p>

                                                                <!-- Level 2 Reply Button (Visible strictly to the Customer who wrote this review) -->
                                                                <?php 
                                                                $is_review_author = $is_logged_in && !empty($rev['customer_id']) && (intval($current_user->ID) === intval($rev['customer_id']));
                                                                if (!$has_level2 && $is_review_author) : 
                                                                ?>
                                                                    <div class="mt-1 text-end">
                                                                        <button type="button" class="btn btn-sm btn-toggle-cust-reply cosy-btn-reply-toggle" data-review-id="<?php echo esc_attr($rev['id']); ?>">
                                                                            <i class="fas fa-reply me-1"></i> <?php esc_html_e('Reply', 'cosy-appointments'); ?>
                                                                        </button>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>

                                                        <?php elseif ($r_level === 2) : ?>
                                                            <!-- Level 2: Customer Follow-up Reply -->
                                                            <div class="thread-item level-2-item cosy-thread-customer-box">
                                                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-1">
                                                                    <span class="cosy-thread-sender-name">
                                                                        <i class="fas fa-comment-dots cosy-thread-sender-icon-customer"></i>
                                                                        <?php echo esc_html($rep['sender_name']); ?>
                                                                    </span>
                                                                    <small class="cosy-thread-date"><?php echo date('d M Y - h:i A', strtotime($rep['created_at'])); ?></small>
                                                                </div>
                                                                <p class="cosy-thread-body-customer"><?php echo esc_html($rep['reply_text']); ?></p>
                                                            </div>

                                                        <?php elseif ($r_level === 3) : ?>
                                                            <!-- Level 3: Provider Final Closing Response -->
                                                            <div class="thread-item level-3-item cosy-thread-provider-box">
                                                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-1">
                                                                    <span class="cosy-thread-sender-name">
                                                                        <i class="fas fa-check-circle cosy-thread-sender-icon-provider"></i>
                                                                        <?php echo esc_html($rep['sender_name']); ?>
                                                                    </span>
                                                                    <small class="cosy-thread-date"><?php echo date('d M Y - h:i A', strtotime($rep['created_at'])); ?></small>
                                                                </div>
                                                                <p class="cosy-thread-body-provider"><?php echo esc_html($rep['reply_text']); ?></p>
                                                            </div>
                                                    <?php
                                                        endif;
                                                    endforeach;
                                                    ?>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Customer Inline Reply Form (Expands under Level 1) -->
                                        <?php if ($is_logged_in && $has_level1 && !$has_level2) : ?>
                                            <div class="cust-reply-form-wrap mt-2 ms-3 p-2 px-3 rounded-3 shadow-sm" id="cust-reply-form-<?php echo esc_attr($rev['id']); ?>" style="display: none; background: #ffffff; border: 1.5px solid #a44390;">
                                                <form class="cosy-customer-reply-form" data-review-id="<?php echo esc_attr($rev['id']); ?>">
                                                    <label class="fw-bold small text-muted mb-1 d-block" style="font-size: 0.78rem;"><i class="fas fa-reply me-1" style="color:#a44390;"></i> <?php esc_html_e('Write a follow-up response:', 'cosy-appointments'); ?></label>
                                                    <div class="mb-2">
                                                        <textarea class="form-control form-control-sm cust-reply-text" rows="2" placeholder="<?php esc_attr_e('Type your follow-up message here...', 'cosy-appointments'); ?>" style="border-radius: 8px; font-size: 0.85rem;" required></textarea>
                                                    </div>
                                                    <div class="d-flex gap-2 justify-content-end">
                                                        <button type="button" class="btn btn-sm btn-light btn-cancel-cust-reply" data-review-id="<?php echo esc_attr($rev['id']); ?>" style="border-radius: 8px; font-size: 0.78rem; padding: 2px 10px;">
                                                            <?php esc_html_e('Cancel', 'cosy-appointments'); ?>
                                                        </button>
                                                        <button type="submit" class="btn btn-sm text-white px-3" style="background: #a44390; border-radius: 8px; font-weight: 600; font-size: 0.78rem; padding: 2px 12px;">
                                                            <?php esc_html_e('Post Follow-up', 'cosy-appointments'); ?>
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <!-- Load More / Show Less Reviews Toggle Button (Shown if count > 1) -->
                            <?php if (count($approved_reviews) > 1) : 
                                $remaining_count = count($approved_reviews) - 1;
                            ?>
                                </div> <!-- Close cosy-extra-reviews-wrapper -->
                                <button type="button" class="btn btn-sm w-100 py-2 mt-3 fw-bold d-flex align-items-center justify-content-center gap-2" id="btnLoadMoreReviews" data-remaining="<?php echo esc_attr($remaining_count); ?>" style="background: #fdf5fc; color: #a44390; border: 1.5px solid rgba(164, 67, 144, 0.3); border-radius: 12px; font-size: 0.85rem; transition: all 0.2s ease;">
                                    <span class="btn-text"><?php printf(esc_html__('Load More Reviews (%d remaining)', 'cosy-appointments'), $remaining_count); ?></span>
                                    <i class="fas fa-chevron-down btn-icon" style="font-size: 0.78rem; color: #a44390; transition: transform 0.3s ease;"></i>
                                </button>
                            <?php endif; ?>

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
                            <h5 class="cosy-price-min fw-bold mb-0"><?php esc_html_e('Choose the date you\'d like to begin', 'cosy-appointments'); ?></h5>
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
                                    style="width: 10px; height: 10px; border-radius: 50%; background: #fdf5fc; border: 1.5px solid #a44390; display: inline-block; box-sizing: border-box;"></span>
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
</main>

<!-- // ===== Custom Premium Calendar ===== -->
<?php
$default_service = null;
if (!empty($provider_data['services'])) {
    $first_srv = reset($provider_data['services']);
    $default_service = [
        'id'       => intval($first_srv['ID']),
        'title'    => $first_srv['title'],
        'price'    => floatval($first_srv['price']),
        'duration' => intval($first_srv['time'] ?? 10)
    ];
}

wp_enqueue_script(
    'provider-profile-js',
    COSY_APPT_URL . 'src/Assets/js/calendar.js',
    ['jquery', 'bootstrap-bundle', 'sweetalert2'],
    COSY_APPT_VER,
    true
);
wp_localize_script('provider-profile-js', 'cosyCalendar', [
    'currencySymbol' => cosy_get_currency_symbol(),
    'defaultService' => $default_service
]);
?>
<script>
    window.cosyDefaultService = <?php echo json_encode($default_service); ?>;
    window.providerId = <?php echo intval($provider_data['ID'] ?? 0); ?>;
    window.providerName = <?php echo json_encode($provider_data['first_name'] ?? ($provider_data['display_name'] ?? 'Provider')); ?>;
    window.checkoutUrl = <?php echo json_encode(cosy_get_page_url('cosy-checkout')); ?>;
</script>

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
<?php get_footer(); ?>