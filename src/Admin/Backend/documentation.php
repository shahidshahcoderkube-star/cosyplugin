<?php if (!defined('ABSPATH')) exit; ?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;700;800&display=swap');

    .cdoc-wrap * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    .cdoc-wrap {
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: #1e293b;
        margin: 20px 20px 40px 0;
        background: #f8f4fb;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 4px 30px rgba(155, 69, 147, 0.08);
    }

    .cdoc-header {
        background: linear-gradient(135deg, #9b4593 0%, #6d2e67 100%);
        padding: 40px 48px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }

    .cdoc-header::after {
        content: '';
        position: absolute;
        top: -60px;
        right: -60px;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.06);
    }

    .cdoc-header h1 {
        font-family: 'Outfit', sans-serif;
        font-size: 2.2rem;
        font-weight: 800;
        color: #fff;
        letter-spacing: -0.02em;
        margin-bottom: 8px;
    }

    .cdoc-header p {
        font-size: 1rem;
        opacity: 0.85;
        font-weight: 300;
        max-width: 520px;
    }

    .cdoc-version-badge {
        display: inline-block;
        background: rgba(255, 255, 255, 0.18);
        color: #fff;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 4px 14px;
        border-radius: 20px;
        margin-bottom: 14px;
        letter-spacing: 0.05em;
    }

    .cdoc-body {
        display: flex;
        background: #fff;
        min-height: 650px;
    }

    .cdoc-sidebar {
        width: 230px;
        min-width: 230px;
        background: #faf5fb;
        border-right: 1px solid #eedced;
        padding: 28px 16px;
    }

    .cdoc-sidebar ul {
        list-style: none;
    }

    .cdoc-sidebar ul li {
        margin-bottom: 6px;
    }

    .cdoc-nav-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px 16px;
        border-radius: 10px;
        color: #6d2e67;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .cdoc-nav-link i {
        font-size: 1rem;
        width: 18px;
        text-align: center;
    }

    .cdoc-nav-link:hover {
        background: #eedced;
        color: #9b4593;
    }

    .cdoc-nav-link.active {
        background: #9b4593;
        color: #ffffff;
        box-shadow: 0 4px 14px rgba(155, 69, 147, 0.25);
    }

    .cdoc-content {
        flex: 1;
        padding: 40px 44px;
    }

    .cdoc-pane {
        display: none;
        animation: fadeUp 0.3s ease;
    }

    .cdoc-pane.active {
        display: block;
    }

    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(12px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .cdoc-section-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.65rem;
        font-weight: 700;
        color: #0f172a;
        border-bottom: 2px solid #f1e4ef;
        padding-bottom: 12px;
        margin-bottom: 28px;
    }

    .cdoc-section-sub {
        font-size: 0.95rem;
        color: #64748b;
        margin-bottom: 24px;
        line-height: 1.6;
    }

    .cdoc-alert {
        background: #fff8fe;
        border-left: 4px solid #9b4593;
        border-radius: 0 12px 12px 0;
        padding: 18px 22px;
        margin-bottom: 28px;
    }

    .cdoc-alert h4 {
        color: #6d2e67;
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .cdoc-alert p {
        color: #7a3673;
        font-size: 0.9rem;
        line-height: 1.6;
    }

    .cdoc-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 32px;
    }

    .cdoc-card {
        border: 1px solid #eedced;
        border-radius: 14px;
        padding: 22px;
        background: #fff;
        transition: all 0.25s ease;
    }

    .cdoc-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(155, 69, 147, 0.1);
        border-color: #9b4593;
    }

    .cdoc-card-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: #f1e4ef;
        color: #9b4593;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        margin-bottom: 14px;
    }

    .cdoc-card h3 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 8px;
    }

    .cdoc-card p {
        font-size: 0.88rem;
        color: #64748b;
        line-height: 1.65;
    }

    .cdoc-tag {
        display: inline-block;
        background: #f1e4ef;
        color: #6d2e67;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
        margin: 4px 3px 0 0;
    }

    .cdoc-table-wrap {
        border: 1px solid #eedced;
        border-radius: 14px;
        overflow: hidden;
        margin-top: 20px;
    }

    .cdoc-table {
        width: 100%;
        border-collapse: collapse;
    }

    .cdoc-table th {
        background: #faf5fb;
        padding: 14px 18px;
        font-size: 0.82rem;
        font-weight: 700;
        color: #6d2e67;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #eedced;
        text-align: left;
    }

    .cdoc-table td {
        padding: 14px 18px;
        border-bottom: 1px solid #f5eaf4;
        font-size: 0.9rem;
        color: #334155;
        vertical-align: middle;
    }

    .cdoc-table tr:last-child td {
        border-bottom: none;
    }

    .cdoc-table tr:hover td {
        background: #fdf8fe;
    }

    .cdoc-slug {
        font-family: monospace;
        background: #f1e4ef;
        color: #6d2e67;
        padding: 3px 9px;
        border-radius: 6px;
        font-size: 0.85rem;
    }

    .cdoc-sc-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .cdoc-sc-code {
        font-family: monospace;
        background: #eef2ff;
        color: #4338ca;
        padding: 5px 10px;
        border-radius: 7px;
        font-size: 0.85rem;
        font-weight: 700;
    }

    .cdoc-copy-btn {
        border: 1px solid #eedced;
        background: #fff;
        color: #9b4593;
        padding: 5px 10px;
        border-radius: 7px;
        cursor: pointer;
        font-size: 0.8rem;
        transition: all 0.2s;
    }

    .cdoc-copy-btn:hover {
        background: #9b4593;
        color: #fff;
        border-color: #9b4593;
    }

    .cdoc-step {
        display: flex;
        gap: 18px;
        align-items: flex-start;
        margin-bottom: 18px;
        padding: 18px 20px;
        background: #faf5fb;
        border-radius: 12px;
        border: 1px solid #eedced;
    }

    .cdoc-step-num {
        background: #9b4593;
        color: #fff;
        width: 36px;
        height: 36px;
        min-width: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1rem;
    }

    .cdoc-step-body h4 {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 5px;
    }

    .cdoc-step-body p {
        font-size: 0.88rem;
        color: #64748b;
        line-height: 1.6;
    }

    .cdoc-step-body code {
        background: #f1e4ef;
        color: #6d2e67;
        padding: 1px 6px;
        border-radius: 5px;
        font-size: 0.85rem;
    }

    .cdoc-toast {
        position: fixed;
        bottom: 28px;
        right: 28px;
        background: #9b4593;
        color: #fff;
        padding: 12px 22px;
        border-radius: 10px;
        font-size: 0.9rem;
        font-weight: 600;
        box-shadow: 0 8px 20px rgba(155, 69, 147, 0.3);
        display: none;
        z-index: 99999;
    }

    .cdoc-structure-diagram {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin: 25px 0;
    }

    .cdoc-structure-node {
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.95rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        border: 1px solid #eedced;
        background: #fff;
        color: #475569;
        transition: all 0.3s ease;
    }

    .cdoc-structure-node.main-node {
        background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%);
        color: #fff;
        border: none;
        font-size: 1.1rem;
        box-shadow: 0 6px 18px rgba(164, 67, 144, 0.25);
    }

    .cdoc-structure-line {
        width: 2px;
        height: 20px;
        background: #eedced;
    }

    .cdoc-structure-children {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .cdoc-structure-children .cdoc-structure-node:hover {
        transform: translateY(-2px);
        border-color: #a44390;
        color: #a44390;
        box-shadow: 0 6px 15px rgba(164, 67, 144, 0.1);
    }

    .cdoc-media-container {
        margin: 25px 0;
        text-align: center;
        background: #fdfafc;
        padding: 16px;
        border-radius: 16px;
        border: 1px dashed #e2ccd9;
    }

    .cdoc-doc-image {
        max-width: 100%;
        height: auto;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(109, 46, 103, 0.08);
        border: 1px solid #eedced;
        transition: transform 0.3s ease;
    }

    .cdoc-doc-image:hover {
        transform: scale(1.01);
    }

    .cdoc-media-caption {
        display: block;
        margin-top: 10px;
        font-size: 0.85rem;
        color: #6d2e67;
        font-weight: 600;
    }
</style>

<div class="cdoc-wrap">

    <div class="cdoc-header">
        <span class="cdoc-version-badge">Version 1.0.3</span>
        <h1>Cosy Appointments — Documentation</h1>
        <p>A complete guide to setting up, configuring, and managing your multi-provider appointment booking platform.</p>
    </div>

    <div class="cdoc-body">

        <div class="cdoc-sidebar">
            <ul>
                <li><a class="cdoc-nav-link active" data-tab="overview"><i class="fas fa-home"></i> Overview</a></li>
                <li><a class="cdoc-nav-link" data-tab="features"><i class="fas fa-star"></i> Features</a></li>
                <li><a class="cdoc-nav-link" data-tab="pages"><i class="fas fa-file-alt"></i> Pages & Shortcodes</a></li>
                <li><a class="cdoc-nav-link" data-tab="workflow"><i class="fas fa-sitemap"></i> How It Works</a></li>
                <li><a class="cdoc-nav-link" data-tab="bookings-explained"><i class="fas fa-book-open"></i> Bookings Explained</a></li>
                <li><a class="cdoc-nav-link" data-tab="stripe"><i class="fab fa-stripe-s"></i> Stripe Setup</a></li>
                <li><a class="cdoc-nav-link" data-tab="users-management"><i class="fas fa-users-cog"></i> Users Management</a></li>
                <li><a class="cdoc-nav-link" data-tab="security-logs"><i class="fas fa-shield-alt"></i> Security & Logs</a></li>
            </ul>
        </div>

        <div class="cdoc-content">

            <!-- OVERVIEW -->
            <div id="cdoc-overview" class="cdoc-pane active">
                <h2 class="cdoc-section-title">Plugin Overview</h2>
                <p class="cdoc-section-sub">Cosy Appointments is a multi-provider booking plugin for WordPress. It allows service providers to register, manage their availability, and accept bookings from customers — all through a secure, integrated platform.</p>

                <div class="cdoc-alert">
                    <h4>Automatic Page Setup</h4>
                    <p>When the plugin is activated, all required pages are created automatically. If a page is accidentally deleted, simply <strong>Deactivate</strong> and <strong>Reactivate</strong> the plugin — any missing pages will be restored without affecting your existing data.</p>
                </div>

                <div class="cdoc-grid">
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-users"></i></div>
                        <h3>Multi-Provider Support</h3>
                        <p>Multiple service providers can register and operate independently on the same platform, each with their own profile, services, and availability.</p>
                    </div>
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-credit-card"></i></div>
                        <h3>Stripe Payment Integration</h3>
                        <p>Customers complete payments through a secure Stripe checkout. Both Live and Sandbox (test) modes are supported for safe configuration.</p>
                    </div>
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-calendar-check"></i></div>
                        <h3>Appointment Booking</h3>
                        <p>Customers can browse providers, select a service and available date, choose a time slot, and confirm a booking in just a few steps.</p>
                    </div>
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-shield-alt"></i></div>
                        <h3>Admin Management Panel</h3>
                        <p>Administrators can verify providers, approve media uploads, review orders, and manage all platform settings from one central dashboard.</p>
                    </div>
                </div>
            </div>

            <!-- FEATURES -->
            <div id="cdoc-features" class="cdoc-pane">
                <h2 class="cdoc-section-title">Plugin Features</h2>
                <p class="cdoc-section-sub">Below is a summary of the core features included in Cosy Appointments.</p>

                <div class="cdoc-grid">
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-user-shield"></i></div>
                        <h3>Custom User Roles</h3>
                        <p>The plugin creates two dedicated roles — <strong>Customer</strong> and <strong>Provider</strong> — each with appropriate permissions and access to their respective dashboards.</p>
                        <div style="margin-top:10px">
                            <span class="cdoc-tag">customer</span>
                            <span class="cdoc-tag">provider</span>
                            <span class="cdoc-tag">admin</span>
                        </div>
                    </div>
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-concierge-bell"></i></div>
                        <h3>Service Management</h3>
                        <p>Providers can add and manage their services — including name, description, price, and duration — directly from their provider dashboard.</p>
                        <div style="margin-top:10px">
                            <span class="cdoc-tag">Add Service</span>
                            <span class="cdoc-tag">Edit / Delete</span>
                            <span class="cdoc-tag">Pricing</span>
                        </div>
                    </div>
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-clock"></i></div>
                        <h3>Availability & Time Slots</h3>
                        <p>Providers can configure their working hours, day-wise availability, holidays, and non-working days. Already booked slots are automatically blocked for other customers.</p>
                        <div style="margin-top:10px">
                            <span class="cdoc-tag">Hourly Slots</span>
                            <span class="cdoc-tag">Holidays</span>
                            <span class="cdoc-tag">Non-Working Days</span>
                        </div>
                    </div>
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-photo-video"></i></div>
                        <h3>Media Approval System</h3>
                        <p>Providers can upload profile images and introductory videos. All media is held for admin review and only becomes publicly visible once approved.</p>
                        <div style="margin-top:10px">
                            <span class="cdoc-tag">Image Upload</span>
                            <span class="cdoc-tag">Admin Review</span>
                            <span class="cdoc-tag">Approve / Reject</span>
                        </div>
                    </div>
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-star"></i></div>
                        <h3>Ratings & Reviews</h3>
                        <p>After a completed appointment, customers can leave a star rating and written review for the provider. Reviews are displayed on the provider's public profile.</p>
                        <div style="margin-top:10px">
                            <span class="cdoc-tag">5-Star Rating</span>
                            <span class="cdoc-tag">Written Review</span>
                            <span class="cdoc-tag">Pending / Published</span>
                        </div>
                    </div>
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-filter"></i></div>
                        <h3>Provider Filtering</h3>
                        <p>Customers can filter providers by service category or search by name. Filtering is powered by AJAX, so results update instantly without reloading the page.</p>
                        <div style="margin-top:10px">
                            <span class="cdoc-tag">Category Filter</span>
                            <span class="cdoc-tag">Search</span>
                            <span class="cdoc-tag">AJAX-Powered</span>
                        </div>
                    </div>
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-envelope"></i></div>
                        <h3>Email Notifications</h3>
                        <p>Once a booking is confirmed and payment is processed, both the customer and the provider automatically receive a confirmation email with appointment details.</p>
                        <div style="margin-top:10px">
                            <span class="cdoc-tag">Customer Email</span>
                            <span class="cdoc-tag">Provider Email</span>
                            <span class="cdoc-tag">Booking Details</span>
                        </div>
                    </div>
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-cog"></i></div>
                        <h3>Settings Panel</h3>
                        <p>Administrators can configure payment gateway credentials, currency, and test/live mode settings from the dedicated Settings page in the admin panel.</p>
                        <div style="margin-top:10px">
                            <span class="cdoc-tag">Stripe Keys</span>
                            <span class="cdoc-tag">Currency</span>
                            <span class="cdoc-tag">Live / Sandbox</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PAGES & SHORTCODES -->
            <div id="cdoc-pages" class="cdoc-pane">
                <h2 class="cdoc-section-title">Required Pages & Shortcodes</h2>
                <p class="cdoc-section-sub">The following pages are created automatically when the plugin is activated. If any page is deleted, you can recreate it manually by creating a new page in WordPress with the correct <strong>Slug</strong> and pasting the corresponding <strong>Shortcode</strong> into the page content.</p>

                <div class="cdoc-alert">
                    <h4>How to Restore a Deleted Page</h4>
                    <p>Go to <strong>Pages → Add New</strong> in your WordPress dashboard. Set the page title and assign the exact slug shown below. Paste the shortcode into the page body, then publish. The page will work immediately.</p>
                </div>

                <div class="cdoc-table-wrap">
                    <table class="cdoc-table">
                        <thead>
                            <tr>
                                <th>Page Title</th>
                                <th>URL Slug</th>
                                <th>Shortcode</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $pages = [
                                ['Service Provider Listing', 'service-provider',     '[cosy_service_provider_list]'],
                                ['Provider Dashboard',       'provider-dashboard',   '[cosy_provider_dashboard]'],
                                ['Provider Registration',    'provider-registration', '[cosy_provider_registration]'],
                                ['Provider Profile',         'provider-profile',     '[cosy_profile_dashboard]'],
                                ['Provider Verification',    'provider-verify',      '[cosy_verify_provider]'],
                                ['Customer Registration',    'user-registration',    '[cosy_customer_registration]'],
                                ['Login',                    'login',                '[cosy_login_form]'],
                                ['Customer Profile',         'customer-profile',     '[customer_profile]'],
                                ['My Orders',                'customer-order',       '[cosy_customer_order]'],
                                ['Appointments',             'appointments',         '[cosy_appointments]'],
                                ['Checkout',                 'cosy-checkout',        '[cosy_checkout]'],
                                ['Orders',                   'orders',               '[cosy_orders]'],
                            ];
                            foreach ($pages as $p): ?>
                                <tr>
                                    <td><strong><?php echo esc_html($p[0]); ?></strong></td>
                                    <td><span class="cdoc-slug"><?php echo esc_html($p[1]); ?></span></td>
                                    <td>
                                        <div class="cdoc-sc-wrap">
                                            <span class="cdoc-sc-code"><?php echo esc_html($p[2]); ?></span>
                                            <button class="cdoc-copy-btn" onclick="cdocCopy('<?php echo esc_js($p[2]); ?>')"><i class="far fa-copy"></i> Copy</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- HOW IT WORKS -->
            <div id="cdoc-workflow" class="cdoc-pane">
                <h2 class="cdoc-section-title">How It Works</h2>
                <p class="cdoc-section-sub">The following steps describe the end-to-end flow of the platform — from provider registration to a completed and confirmed appointment.</p>

                <div class="cdoc-step">
                    <div class="cdoc-step-num">1</div>
                    <div class="cdoc-step-body">
                        <h4>Provider Registration</h4>
                        <p>A service provider registers via the <code>/provider-registration</code> page and is assigned the Provider role. Their account becomes fully active after the administrator approves their identity and media uploads.</p>
                    </div>
                </div>
                <div class="cdoc-step">
                    <div class="cdoc-step-num">2</div>
                    <div class="cdoc-step-body">
                        <h4>Service & Availability Configuration</h4>
                        <p>After logging in, the provider accesses the <code>/provider-dashboard</code> to add services with pricing and duration, and configure their working hours, available days, and any holidays or days off.</p>
                    </div>
                </div>
                <div class="cdoc-step">
                    <div class="cdoc-step-num">3</div>
                    <div class="cdoc-step-body">
                        <h4>Customer Browsing & Booking</h4>
                        <p>Customers visit the <code>/service-provider</code> page to browse and filter providers. They open a provider profile, select a service, choose a date, and pick an available time slot to proceed with the booking.</p>
                    </div>
                </div>
                <div class="cdoc-step">
                    <div class="cdoc-step-num">4</div>
                    <div class="cdoc-step-body">
                        <h4>Payment via Stripe Checkout</h4>
                        <p>The customer is redirected to the <code>/cosy-checkout</code> page where they complete a secure payment through Stripe. Once payment is confirmed, the appointment is published and both parties receive a confirmation email.</p>
                    </div>
                </div>
                <div class="cdoc-step">
                    <div class="cdoc-step-num">5</div>
                    <div class="cdoc-step-body">
                        <h4>Admin Oversight & Management</h4>
                        <p>Administrators can view and manage all bookings under <strong>CC Booking → Orders</strong>. Provider verification, media approvals, and customer reviews are all handled from the admin panel.</p>
                    </div>
                </div>
            </div>

            <!-- STRIPE SETUP -->
            <div id="cdoc-stripe" class="cdoc-pane">
                <h2 class="cdoc-section-title">Stripe Payment Configuration</h2>
                <p class="cdoc-section-sub">Follow the steps below to connect your Stripe account and enable payments on the platform.</p>

                <div class="cdoc-alert">
                    <h4>Where to Find Your API Keys</h4>
                    <p>Log in to your Stripe account and visit <a href="https://dashboard.stripe.com/apikeys" target="_blank" style="color:#9b4593;font-weight:700;">dashboard.stripe.com/apikeys</a>. You will find both the Publishable Key and Secret Key listed there.</p>
                </div>

                <div class="cdoc-step">
                    <div class="cdoc-step-num">1</div>
                    <div class="cdoc-step-body">
                        <h4>Open the Settings Page</h4>
                        <p>In the WordPress admin panel, navigate to <strong>CC Booking → Settings</strong> and select the Stripe tab.</p>
                    </div>
                </div>
                <div class="cdoc-step">
                    <div class="cdoc-step-num">2</div>
                    <div class="cdoc-step-body">
                        <h4>Enter Your API Keys</h4>
                        <p>Paste your <strong>Publishable Key</strong> (beginning with <code>pk_live_</code> or <code>pk_test_</code>) and your <strong>Secret Key</strong> (beginning with <code>sk_live_</code> or <code>sk_test_</code>) into the respective fields.</p>
                    </div>
                </div>
                <div class="cdoc-step">
                    <div class="cdoc-step-num">3</div>
                    <div class="cdoc-step-body">
                        <h4>Set the Currency</h4>
                        <p>Specify the currency code to be used for transactions — for example, <code>USD</code>, <code>GBP</code>, <code>EUR</code>, or <code>INR</code>. This will be displayed on the checkout page.</p>
                    </div>
                </div>
                <div class="cdoc-step">
                    <div class="cdoc-step-num">4</div>
                    <div class="cdoc-step-body">
                        <h4>Save Settings & Test</h4>
                        <p>Save your configuration. Before going live, use Stripe's test mode with a test card to verify that payments are processed correctly. Switch to Live mode only when you are ready to accept real transactions.</p>
                    </div>
                </div>
            </div>

            <!-- USERS MANAGEMENT -->
            <div id="cdoc-users-management" class="cdoc-pane">
                <h2 class="cdoc-section-title">Users Management Guide</h2>
                <p class="cdoc-section-sub">Administrators have centralized controls to manage both customers and service providers on the platform.</p>

                <div class="cdoc-step">
                    <div class="cdoc-step-num">1</div>
                    <div class="cdoc-step-body">
                        <h4>Filtering and Searching Users</h4>
                        <p>In <strong>CC Booking → Users</strong>, you can filter by role (All, Providers, Customers) or by service type to narrow down who you are viewing. You can also search directly by username, email, or display name.</p>
                    </div>
                </div>

                <div class="cdoc-step">
                    <div class="cdoc-step-num">2</div>
                    <div class="cdoc-step-body">
                        <h4>Provider Account Status & Verification</h4>
                        <p>Administrators can set a provider status to <strong>Active</strong> or <strong>Deactive</strong> using the inline dropdown. An email is automatically triggered to inform providers of approval/suspension status. In the main WordPress users list, a "Verify" column is also added to toggle status quickly.</p>
                    </div>
                </div>

                <div class="cdoc-step">
                    <div class="cdoc-step-num">3</div>
                    <div class="cdoc-step-body">
                        <h4>Viewing User Details (Modal)</h4>
                        <p>Click <strong>View Details</strong> to open a detailed modal showing basic profile details, extra provider information (middle name, DOB, phone, biography), and a chronological listing of their appointments and sessions.</p>
                    </div>
                </div>

                <div class="cdoc-step">
                    <div class="cdoc-step-num">4</div>
                    <div class="cdoc-step-body">
                        <h4>Bulk Deleting & Resending Verification</h4>
                        <p>Check the desired users in the list and click the <strong>Delete</strong> button to bulk delete users. If a user is pending email verification, you will see a <strong>Resend Email</strong> button to resend their verification link.</p>
                    </div>
                </div>
            </div>

            <!-- BOOKINGS EXPLAINED -->
            <div id="cdoc-bookings-explained" class="cdoc-pane">
                <h2 class="cdoc-section-title">Customer Appointments & Bookings Explained</h2>
                <p class="cdoc-section-sub">A detailed breakdown of the CC Booking parent menu, booking lifecycle, and database architecture.</p>

                <div class="cdoc-alert">
                    <h4>Centralized Administration Hub</h4>
                    <p>Located in the WordPress admin sidebar under <strong>"CC Booking"</strong>, this section empowers the Site Administrator to oversee and control all providers, customers, services, appointments, and payments from a single unified workspace.</p>
                </div>

                <h3 class="fw-bold text-dark mt-4 mb-3" style="font-family: 'Outfit', sans-serif; font-size: 1.15rem;">📌 Plugin's Overall Admin Structure</h3>
                <p>The administrative console is segmented into six primary modules located under the parent menu hierarchy:</p>

                <div class="cdoc-structure-diagram">
                    <div class="cdoc-structure-node main-node">CC Booking (Main Parent Menu)</div>
                    <div class="cdoc-structure-line"></div>
                    <div class="cdoc-structure-children">
                        <div class="cdoc-structure-node"><i class="fas fa-chart-line"></i> Booking Dashboard</div>
                        <div class="cdoc-structure-node"><i class="fas fa-cogs"></i> Services</div>
                        <div class="cdoc-structure-node"><i class="fas fa-shopping-basket"></i> Orders</div>
                        <div class="cdoc-structure-node"><i class="fas fa-photo-video"></i> Media Approve</div>
                        <div class="cdoc-structure-node"><i class="fas fa-users-cog"></i> Users</div>
                        <div class="cdoc-structure-node"><i class="fas fa-list-alt"></i> Logs</div>
                    </div>
                </div>

                <h3 class="fw-bold text-dark mt-4 mb-3" style="font-family: 'Outfit', sans-serif; font-size: 1.15rem;">1️⃣ Booking Dashboard (DashboardAdmin.php)</h3>
                <p>The dashboard operates as the central control room, displaying key statistics, booking patterns, and transaction status: Description of the KPI Cards:</p>

                <table class="wp-list-table widefat fixed striped" style="margin-bottom: 20px; border: 1px solid #eedced; border-radius: 8px; overflow: hidden;">
                    <thead>
                        <tr style="background: #faf5fb;">
                            <th style="font-weight: 700; width: 30%; padding: 12px;">KPI Metric Card</th>
                            <th style="font-weight: 700; padding: 12px;">Data Displayed & Purpose</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 12px; font-weight: 600; color: #a44390;"><i class="fas fa-calendar-check me-2"></i> Total Bookings</td>
                            <td style="padding: 12px;">Total booking count across all states: active confirmed, unpaid pending, and cancelled.</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-weight: 600; color: #10b981;"><i class="fas fa-check-circle me-2"></i> Confirmed Bookings</td>
                            <td style="padding: 12px;">Total appointments that are paid and active in the system.</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-weight: 600; color: #f59e0b;"><i class="fas fa-clock me-2"></i> Pending Bookings</td>
                            <td style="padding: 12px;">Count of draft appointments currently awaiting successful checkout.</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-weight: 600; color: #ef4444;"><i class="fas fa-times-circle me-2"></i> Cancelled Bookings</td>
                            <td style="padding: 12px;">Appointments marked as cancelled or deleted.</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-weight: 600; color: #3b82f6;"><i class="fas fa-user-tie me-2"></i> Providers</td>
                            <td style="padding: 12px;">Total registered and verified service provider profiles.</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-weight: 600; color: #6366f1;"><i class="fas fa-user me-2"></i> Customers</td>
                            <td style="padding: 12px;">Total registered customer accounts on the site.</td>
                        </tr>
                    </tbody>
                </table>

                <div class="cdoc-media-container">
                    <img src="<?php echo esc_url(COSY_APPT_URL . 'src/Admin/assets/manage_overview.png'); ?>" alt="Booking Dashboard Overview" class="cdoc-doc-image">
                    <span class="cdoc-media-caption">Figure 1: Booking Dashboard Overview with live KPIs and Line Graphs</span>
                </div>

                <h3 class="fw-bold text-dark mt-4 mb-3" style="font-family: 'Outfit', sans-serif; font-size: 1.15rem;">2️⃣ Users Management (UsersAdmin.php)</h3>
                <p>The administrative panel for overseeing registered accounts, reviewing appointments, and activating/deactivating service providers.</p>

                <div class="cdoc-step">
                    <div class="cdoc-step-num"><i class="fas fa-filter"></i></div>
                    <div class="cdoc-step-body">
                        <h4>Filtering & Quick Search Controls</h4>
                        <p>Provides filtering by role (All, Providers, Customers) and service type. Matches searches dynamically against usernames, display names, or emails.</p>
                    </div>
                </div>

                <div class="cdoc-media-container">
                    <img src="<?php echo esc_url(COSY_APPT_URL . 'src/Admin/assets/manage_users.png'); ?>" alt="Users Management View" class="cdoc-doc-image">
                    <span class="cdoc-media-caption">Figure 2: Users Management view listing registered accounts</span>
                </div>

                <div class="cdoc-step">
                    <div class="cdoc-step-num"><i class="fas fa-table"></i></div>
                    <div class="cdoc-step-body">
                        <h4>Appointments Column: Smart Display</h4>
                        <p>The system displays distinct layouts and styles depending on the user's role. For customers, the "Manage" override button has been removed from the session list, leaving only the status badges and session details visible. For providers, their session details are highlighted in their respective color-coded badges.</p>
                    </div>
                </div>

                <div style="display: flex; gap: 20px; margin: 20px 0; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 280px;">
                        <div class="cdoc-media-container" style="margin: 0; padding: 10px; height: 100%;">
                            <img src="<?php echo esc_url(COSY_APPT_URL . 'src/Admin/assets/customer_appointments.png'); ?>" alt="Customer Appointments & Bookings View" class="cdoc-doc-image">
                            <span class="cdoc-media-caption">Figure 2a: Customer Appointments list (with no "Manage" button)</span>
                        </div>
                    </div>
                    <div style="flex: 1; min-width: 280px;">
                        <div class="cdoc-media-container" style="margin: 0; padding: 10px; height: 100%;">
                            <img src="<?php echo esc_url(COSY_APPT_URL . 'src/Admin/assets/provider_appointments.png'); ?>" alt="Provider Appointments & Bookings View" class="cdoc-doc-image">
                            <span class="cdoc-media-caption">Figure 2b: Provider Appointments list (with color-coded sessions)</span>
                        </div>
                    </div>
                </div>

                <div class="cdoc-step">
                    <div class="cdoc-step-num"><i class="fas fa-tasks"></i></div>
                    <div class="cdoc-step-body">
                        <h4>Admin Action Capabilities</h4>
                        <p><strong>A. View Details Modal:</strong> Opens a detailed modal displaying complete profile attributes, biography, and full session history divided into tabs (<code>All</code>, <code>Upcoming</code>, <code>In Progress</code>, <code>Completed</code>, <code>Cancelled</code>) with direct edit shortcuts.
                            <br><strong>B. Verification Dropdowns:</strong> Enable or disable service providers. Toggling accounts automatically dispatches custom transactional emails and registers audit logs.
                            <br><strong>C. Resend Email Verifications:</strong> Resend activation tokens to pending users.
                            <br><strong>D. Bulk Delete:</strong> Delete multiple providers/customers simultaneously with built-in safety blocks preventing self-deletion.
                        </p>
                    </div>
                </div>

                <h3 class="fw-bold text-dark mt-4 mb-3" style="font-family: 'Outfit', sans-serif; font-size: 1.15rem;">3️⃣ Orders Management (OrdersAdmin.php)</h3>
                <p>Treats bookings as discrete financial and service Orders, giving administrators order status controls and granular transaction details:
                    <br>• <strong>Order ID:</strong> Formatted with a hash indicator (e.g. <code>#123</code>).
                    <br>• <strong>Cost Breakdown:</strong> Shows the provider's base fee, the net platform service fee, and the gross total paid.
                </p>

                <h3 class="fw-bold text-dark mt-4 mb-3" style="font-family: 'Outfit', sans-serif; font-size: 1.15rem;">4️⃣ End-to-End Booking Data Flow</h3>
                <p>The booking lifecycle coordinates actions across all user roles, ensuring real-time notification alerts, data logs, and updates:</p>
                <div style="background: #faf5fb; border: 1px solid #eedced; border-radius: 12px; padding: 20px; font-family: 'Plus Jakarta Sans', sans-serif; margin-bottom: 25px;">
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <div><strong style="color: #a44390;">Step 1: Booking Initiation</strong> — Customer selects a service, calendar date, time slot, and enters checkout. An unpaid draft appointment is created.</div>
                        <div><strong style="color: #a44390;">Step 2: Payment Confirmation</strong> — Stripe processes payment. The draft booking shifts to published, updating payment state to Paid.</div>
                        <div><strong style="color: #a44390;">Step 3: Multi-Recipient Notifications</strong> — Customer receives booking confirmation receipt. The provider and site administrator receive transaction alerts.</div>
                        <div><strong style="color: #a44390;">Step 4: Provider Dashboard Action</strong> — Provider confirms/completes/cancels the appointment. The customer receives status emails and activity is logged.</div>
                    </div>
                </div>

                <h3 class="fw-bold text-dark mt-4 mb-3" style="font-family: 'Outfit', sans-serif; font-size: 1.15rem;">5️⃣ Database & Metadata Architecture</h3>
                <p>All core plugin functionalities map to WordPress databases and user meta keys:</p>

                <table class="wp-list-table widefat fixed striped" style="margin-bottom: 20px; border: 1px solid #eedced; border-radius: 8px; overflow: hidden;">
                    <thead>
                        <tr style="background: #faf5fb;">
                            <th style="font-weight: 700; width: 35%; padding: 12px;">Meta Key / Table Name</th>
                            <th style="font-weight: 700; padding: 12px;">Functional Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 12px; font-family: monospace;">cosy_customer_id / cosy_provider_id</td>
                            <td style="padding: 12px;">WP User IDs mapping the customer and service provider to the appointment.</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-family: monospace;">cosy_start_date / cosy_end_date</td>
                            <td style="padding: 12px;">Dates identifying the start and end of the appointment.</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-family: monospace;">cosy_service_cost / cosy_service_fee</td>
                            <td style="padding: 12px;">Financial division of earnings (Provider's share and platform's commission fee).</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-family: monospace;">cosy_total_payable</td>
                            <td style="padding: 12px;">Total paid cost used to calculate revenue figures.</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-family: monospace;">account_status</td>
                            <td style="padding: 12px;">Tracks email verification status (active, pending, or deactive).</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-family: monospace;">cosy_provider_status</td>
                            <td style="padding: 12px;">Tracks admin-controlled activation status (active or deactive).</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-family: monospace;">wp_provider_services</td>
                            <td style="padding: 12px;">Custom table linking providers to services.</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-family: monospace;">wp_cosy_activity_logs</td>
                            <td style="padding: 12px;">Custom table storing comprehensive system and user activity logs.</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-family: monospace;">wp_cosy_media_approvals</td>
                            <td style="padding: 12px;">Custom table tracking provider video uploads and approval actions.</td>
                        </tr>
                    </tbody>
                </table>

                <h3 class="fw-bold text-dark mt-4 mb-3" style="font-family: 'Outfit', sans-serif; font-size: 1.15rem;">🔒 Security & Media Approval Workflows</h3>
                <p>• <strong>OTP Deactivation Guard:</strong> Unauthorized deactivation attempts are blocked. When triggered, a secure 6-digit One-Time Password is sent to the site admin's email and deactivation is locked until verified.
                    <br>• <strong>Media Verification:</strong> Provider introductory video uploads are held in pending status until reviewed by the admin. Approved media displays on provider pages, while rejected media is permanently deleted from storage.
                </p>
            </div>

            <!-- SECURITY & LOGS -->
            <div id="cdoc-security-logs" class="cdoc-pane">
                <h2 class="cdoc-section-title">Security & Audit Activity Logs</h2>
                <p class="cdoc-section-sub">Cosy Appointments includes robust security measures to protect plugin configuration and track key occurrences.</p>

                <div class="cdoc-alert">
                    <h4>🔒 Plugin Deactivation Protection</h4>
                    <p>To prevent unauthorized personnel or malicious attacks from disabling the booking engine, a <strong>One-Time Password (OTP) deactivation guard</strong> is active. Attempting to deactivate the plugin triggers a secure 6-digit code sent only to the Site Administrator's registered email address. The plugin cannot be deactivated without inputting this verified OTP code.</p>
                </div>

                <div class="cdoc-step">
                    <div class="cdoc-step-num">1</div>
                    <div class="cdoc-step-body">
                        <h4>Viewing Activity Audit Logs</h4>
                        <p>Navigate to <strong>CC Booking → Logs</strong> to view a table of logged actions. Each entry records the date/timestamp, user name, role (Admin, Provider, Customer, Guest), IP address, and a detailed description of the action taken.</p>
                    </div>
                </div>

                <div class="cdoc-step">
                    <div class="cdoc-step-num">2</div>
                    <div class="cdoc-step-body">
                        <h4>Toggle Log Modules Dynamically</h4>
                        <p>Under the Logs page, admins can toggle logging on or off for individual functional modules (e.g., Services, Users, Settings, Media approvals) using visual slider switches. Paused modules will skip database logging to keep the table clean.</p>
                    </div>
                </div>

                <div class="cdoc-step">
                    <div class="cdoc-step-num">3</div>
                    <div class="cdoc-step-body">
                        <h4>Automated Cleanups (Cron Job)</h4>
                        <p>To avoid bloating the database, the plugin automatically schedules a daily cron task (<code>cosy_cleanup_activity_logs_cron</code>) that deletes all activity log rows older than <strong>30 days</strong>. You can also manually clear all logs at any time using the <strong>Clear Logs</strong> action button.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="cdoc-toast" id="cdocToast">Copied to clipboard.</div>

<script>
    document.querySelectorAll('.cdoc-nav-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.cdoc-nav-link').forEach(function(l) {
                l.classList.remove('active');
            });
            document.querySelectorAll('.cdoc-pane').forEach(function(p) {
                p.classList.remove('active');
            });
            this.classList.add('active');
            var target = document.getElementById('cdoc-' + this.getAttribute('data-tab'));
            if (target) target.classList.add('active');
        });
    });

    function cdocCopy(text) {
        navigator.clipboard.writeText(text).then(function() {
            var t = document.getElementById('cdocToast');
            t.style.display = 'block';
            setTimeout(function() {
                t.style.display = 'none';
            }, 2200);
        });
    }
</script>