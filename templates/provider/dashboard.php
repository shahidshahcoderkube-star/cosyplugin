<style>
/* Force Theme Containers to be Full Width */
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

#cosy-dashboard-container {
    width: 100% !important;
    max-width: 100% !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
    margin: 0 !important;
}
/* Sidebar responsive height fix */
@media (max-width: 767px) {
    #cosy-sidebar {
        min-height: auto !important;
        height: auto !important;
    }
}
</style>

<div class="container-fluid mt-0 mt-md-4 px-0" id="cosy-dashboard-container">
    <?php wp_nonce_field('cosy_dashboard_nonce', 'cosy_dashboard_nonce_field'); ?>
    <div class="row">
        <!-- Sidebar -->
        <div class="col-12 col-md-3 bg-gradient p-3 text-white" id="cosy-sidebar"
            style="background: linear-gradient(180deg, #4facfe 0%, #00f2fe 100%); min-height:100vh;">
            <h4 class="mb-4 text-center fw-bold">Provider Dashboard</h4>
            <div class="nav flex-column cc__dashboard nav-pills" id="cosyDashboardTabs" role="tablist" aria-orientation="vertical">
                <button class="nav-link cosy-tab active mb-2" data-tab="profile" id="profile-tab" data-bs-toggle="pill" data-bs-target="#profile" type="button" role="tab">
                    👤 Profile
                </button>
                <button class="nav-link cosy-tab mb-2" data-tab="video" id="video-tab" data-bs-toggle="pill" data-bs-target="#video" type="button" role="tab">
                    🎥 Video
                </button>
                <button class="nav-link cosy-tab mb-2" data-tab="services" id="services-tab" data-bs-toggle="pill" data-bs-target="#services" type="button" role="tab">
                    🛠 Services
                </button>
                <button class="nav-link cosy-tab mb-2" data-tab="availability" id="availability-tab" data-bs-toggle="pill" data-bs-target="#availability" type="button" role="tab">
                    📅 Availability
                </button>
                <button class="nav-link cosy-tab mb-2" data-tab="orders" id="orders-tab" data-bs-toggle="pill" data-bs-target="#orders" type="button" role="tab">
                    📦 Orders
                </button>
                <button class="nav-link cosy-tab cosy-tab mb-2" data-tab="nonworking" id="nonworking-tab" data-bs-toggle="pill" data-bs-target="#nonworking" type="button" role="tab">
                    🚫 Holidays
                </button>
                <button class="nav-link cosy-tab mb-2" data-tab="reviews" id="reviews-tab" data-bs-toggle="pill" data-bs-target="#reviews" type="button" role="tab">
                    ⭐ Reviews
                </button>
                <button class="nav-link cosy-tab mb-2" data-tab="invoices" id="invoices-tab" data-bs-toggle="pill" data-bs-target="#invoices" type="button" role="tab">
                    💳 Invoices
                </button>
            </div>
        </div>

        <!-- Content Area -->
        <div class="col-12 col-md-9 p-3 p-md-4" style="background:#f9f9f9;">
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