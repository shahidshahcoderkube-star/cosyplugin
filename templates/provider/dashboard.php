<style>
    /* Force Theme Containers to be Full Width specifically for the dashboard */
    .ast-container,
    .site-content,
    #primary,
    #main,
    .ast-container-fluid {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
</style>

<div class="container-fluid mt-0 mt-md-4 px-0" id="cosy-dashboard-container">
    <?php wp_nonce_field('cosy_dashboard_nonce', 'cosy_dashboard_nonce_field'); ?>
    <div class="row">
        <!-- Sidebar -->
        <div class="col-12 col-md-3 bg-white p-4 shadow-sm" id="cosy-sidebar"
            style="min-height:100vh; border-right: 1px solid #f1f5f9;">
            <div class="sidebar-header mb-4 pb-3 text-center" style="border-bottom: 1.5px solid #f8fafc;">
                <div class="d-inline-flex align-items-center justify-content-center p-2 mb-2"
                    style="background: rgba(164, 67, 144, 0.08); border-radius: 12px;">
                    <i class="fas fa-th-large" style="color: #a44390; font-size: 1.2rem;"></i>
                </div>
                <h4 class="m-0"
                    style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 1.1rem; color: #1e293b; letter-spacing: 0.5px; text-transform: uppercase;">
                    Provider Dashboard</h4>
            </div>

            <div class="nav flex-column cc__dashboard nav-pills" id="cosyDashboardTabs" role="tablist"
                aria-orientation="vertical">
                <button class="nav-link cosy-tab active mb-2" data-tab="profile" id="profile-tab" data-bs-toggle="pill"
                    data-bs-target="#profile" type="button" role="tab">
                    <i class="fas fa-user-circle"></i> Profile
                </button>
                <button class="nav-link cosy-tab mb-2" data-tab="video" id="video-tab" data-bs-toggle="pill"
                    data-bs-target="#video" type="button" role="tab">
                    <i class="fas fa-video"></i> Video
                </button>
                <button class="nav-link cosy-tab mb-2" data-tab="services" id="services-tab" data-bs-toggle="pill"
                    data-bs-target="#services" type="button" role="tab">
                    <i class="fas fa-concierge-bell"></i> Services
                </button>
                <button class="nav-link cosy-tab mb-2" data-tab="availability" id="availability-tab"
                    data-bs-toggle="pill" data-bs-target="#availability" type="button" role="tab">
                    <i class="fas fa-calendar-alt"></i> Availability
                </button>
                <button class="nav-link cosy-tab mb-2" data-tab="orders" id="orders-tab" data-bs-toggle="pill"
                    data-bs-target="#orders" type="button" role="tab">
                    <i class="fas fa-shopping-bag"></i> Orders
                </button>
                <button class="nav-link cosy-tab mb-2" data-tab="nonworking" id="nonworking-tab" data-bs-toggle="pill"
                    data-bs-target="#nonworking" type="button" role="tab">
                    <i class="fas fa-calendar-times"></i> Holidays
                </button>
                <button class="nav-link cosy-tab mb-2" data-tab="reviews" id="reviews-tab" data-bs-toggle="pill"
                    data-bs-target="#reviews" type="button" role="tab">
                    <i class="fas fa-star"></i> Reviews
                </button>
                <button class="nav-link cosy-tab mb-2" data-tab="invoices" id="invoices-tab" data-bs-toggle="pill"
                    data-bs-target="#invoices" type="button" role="tab">
                    <i class="fas fa-file-invoice-dollar"></i> Invoices
                </button>
            </div>
        </div>

        <!-- Content Area -->
        <div class="col-12 col-md-9 p-3 p-md-4" style="background:#f9f9f9;">
            <?php
            $user_id = get_current_user_id();
            $provider_status = get_user_meta($user_id, 'cosy_provider_status', true);
            if ($provider_status === 'deactive'):
                ?>
                <div class="alert d-flex align-items-center mb-4 border-0 shadow-sm"
                    style="background: #fff8e1; border-radius: 16px; color: #b78103;" role="alert">
                    <div
                        style="width: 40px; height: 40px; background: rgba(255, 193, 7, 0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 1.1rem; color: #d39e00;"></i>
                    </div>
                    <div>
                        <strong style="font-family: 'Poppins', sans-serif;">Account Under Review:</strong> <span
                            style="font-size: 0.95rem;">Your profile is currently under review by the administrator. Once
                            approved, it will be visible to parents.</span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="tab-content" id="dashboardTabsContent">
                <div class="tab-pane fade show active" id="profile" role="tabpanel">
                    <?php include 'dashboard/profile-information.php'; ?>
                </div>
                <div class="tab-pane fade" id="video" role="tabpanel">
                    <?php include 'dashboard/media-upload.php'; ?>
                </div>
                <div class="tab-pane fade" id="services" role="tabpanel">
                    <?php include 'dashboard/services.php'; ?>
                </div>
                <div class="tab-pane fade" id="availability" role="tabpanel">
                    <?php include 'dashboard/availability.php'; ?>
                </div>
                <div class="tab-pane fade" id="orders" role="tabpanel">
                    <?php include 'dashboard/orders.php'; ?>
                </div>
                <div class="tab-pane fade" id="nonworking" role="tabpanel">
                    <?php include 'dashboard/holidays.php'; ?>
                </div>
                <div class="tab-pane fade" id="reviews" role="tabpanel">
                    <?php include 'dashboard/customer-reviews.php'; ?>
                </div>
                <div class="tab-pane fade" id="invoices" role="tabpanel">
                    <?php include 'dashboard/invoices.php'; ?>
                </div>
            </div>
        </div>
    </div>
</div>