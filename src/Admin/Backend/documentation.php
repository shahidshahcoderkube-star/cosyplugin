<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ADMIN DOCUMENTATION PAGE TEMPLATE VIEW
 * 
 * USE CASE:
 * Renders the Admin -> CC Booking -> Documentation user guide screen.
 * 
 * HOW TO USE:
 * Included by SettingsAdmin::render_documentation().
 * 
 * WHAT IT DOES INTERNALLY:
 * 1. Renders complete HTML/CSS guide covering shortcodes, payment configuration, webhooks, and provider setup.
 */
?>

<div class="cdoc-wrap">

    <div class="cdoc-header">
        <span class="cdoc-version-badge">Version <?php echo esc_html(defined('COSY_APPT_VER') ? COSY_APPT_VER : '1.0.15'); ?></span>
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
                <li><a class="cdoc-nav-link" data-tab="worldpay"><i class="fas fa-credit-card"></i> WorldPay Setup</a></li>
                <li><a class="cdoc-nav-link" data-tab="users-management"><i class="fas fa-users-cog"></i> Users Management</a></li>
                <li><a class="cdoc-nav-link" data-tab="fees-media"><i class="fas fa-sliders-h"></i> Fees & Media Limits</a></li>
                <li><a class="cdoc-nav-link" data-tab="ai-search"><i class="fas fa-brain"></i> AI Vector Search</a></li>
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
                        <h3>WorldPay Payment Integration</h3>
                        <p>Customers complete payments through a secure WorldPay hosted checkout. Both Live and Sandbox (test) modes are supported for safe configuration.</p>
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
                            <span class="cdoc-tag">WorldPay Keys</span>
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
                                ['Leave a Review',           'cosy-leave-review',    '[cosy_leave_review]'],
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
                        <h4>Payment via WorldPay Checkout</h4>
                        <p>The customer is redirected to the <code>/cosy-checkout</code> page where they complete a secure payment through WorldPay. Once payment is confirmed, the appointment is published and both parties receive a confirmation email.</p>
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

            <!-- WORLDPAY SETUP -->
            <div id="cdoc-worldpay" class="cdoc-pane">
                <h2 class="cdoc-section-title">WorldPay Payment Gateway Configuration</h2>
                <p class="cdoc-section-sub">Cosy Appointments supports dedicated WorldPay Access API HPP Hosted Gateway for secure customer payments.</p>

                <div class="cdoc-alert">
                    <h4>⭐ WorldPay Active Gateway</h4>
                    <p>In <strong>CC Booking → Settings</strong>, configure your WorldPay merchant credentials. WorldPay Access HPP is automatically activated during customer checkout.</p>
                </div>

                <h3 class="fw-bold text-dark mt-4 mb-3" style="font-family: 'Outfit', sans-serif; font-size: 1.15rem;">🌐 WorldPay Setup Steps</h3>
                <div class="cdoc-step">
                    <div class="cdoc-step-num">1</div>
                    <div class="cdoc-step-body">
                        <h4>Entity ID & API Credentials</h4>
                        <p>In <strong>CC Booking → Settings → WorldPay</strong>, enter your <strong>Entity ID</strong> (e.g. <code>PO4097986011</code>), <strong>Service API Username</strong>, and <strong>Password</strong> from your WorldPay Access merchant account.</p>
                    </div>
                </div>
                <div class="cdoc-step">
                    <div class="cdoc-step-num">2</div>
                    <div class="cdoc-step-body">
                        <h4>Sandbox / Test Mode Toggle</h4>
                        <p>Toggle <strong>Sandbox / Test Mode</strong> to enable WorldPay's test endpoint (<code>try.access.worldpay.com</code>) for test transactions before going live.</p>
                    </div>
                </div>
            </div>

            <!-- FEES & MEDIA LIMITS -->
            <div id="cdoc-fees-media" class="cdoc-pane">
                <h2 class="cdoc-section-title">Platform Fees & Media Limits</h2>
                <p class="cdoc-section-sub">Administrators can dynamically control platform commission fees and specify strict file size limits for provider media uploads.</p>

                <div class="cdoc-alert">
                    <h4>🔒 Server-Side Price Verification Active</h4>
                    <p>All checkout transactions undergo strict server-side price verification. The booking engine cross-references frontend-provided totals with official database records and currently configured fee values before initiating payment sessions to prevent frontend price tampering.</p>
                </div>

                <div class="cdoc-step">
                    <div class="cdoc-step-num"><i class="fas fa-calculator"></i></div>
                    <div class="cdoc-step-body">
                        <h4>Service Fee (Transaction Charge)</h4>
                        <p>The service fee is configured as a percentage via the <strong>Transaction Charge (%)</strong> field in <strong>CC Booking → Settings → WorldPay</strong>.
                            <br>• This percentage is applied to the base service cost to calculate the service fee.
                            <br>• For example, setting it to <code>10.00</code> will add a 10% fee to each booking.
                        </p>
                    </div>
                </div>

                <div class="cdoc-step">
                    <div class="cdoc-step-num"><i class="fas fa-video"></i></div>
                    <div class="cdoc-step-body">
                        <h4>Media Upload Size Configuration</h4>
                        <p>Specify the maximum allowed video size under <strong>CC Booking → Media Approve → Video Upload Configuration</strong>.
                            <br>• <strong>Max Size (MB):</strong> Define the limit in Megabytes (defaults to 3 MB).
                            <br>• This limit dynamically controls client-side dropzone validation, dashboard information messages, backend PHP file handlers, and video rejection warning emails automatically.
                        </p>
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
                    <img src="<?php echo esc_url(COSY_APPT_URL . 'src/Admin/Assets/manage_overview.png'); ?>" alt="Booking Dashboard Overview" class="cdoc-doc-image">
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
                    <img src="<?php echo esc_url(COSY_APPT_URL . 'src/Admin/Assets/manage_users.png'); ?>" alt="Users Management View" class="cdoc-doc-image">
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
                            <img src="<?php echo esc_url(COSY_APPT_URL . 'src/Admin/Assets/customer_appointments.png'); ?>" alt="Customer Appointments & Bookings View" class="cdoc-doc-image">
                            <span class="cdoc-media-caption">Figure 2a: Customer Appointments list (with no "Manage" button)</span>
                        </div>
                    </div>
                    <div style="flex: 1; min-width: 280px;">
                        <div class="cdoc-media-container" style="margin: 0; padding: 10px; height: 100%;">
                            <img src="<?php echo esc_url(COSY_APPT_URL . 'src/Admin/Assets/provider_appointments.png'); ?>" alt="Provider Appointments & Bookings View" class="cdoc-doc-image">
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
                        <div><strong style="color: #a44390;">Step 2: Payment Confirmation</strong> — WorldPay processes payment. The draft booking shifts to published, updating payment state to Paid.</div>
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
            </div>

            <!-- AI VECTOR SEARCH -->
            <div id="cdoc-ai-search" class="cdoc-pane">
                <h2 class="cdoc-section-title">AI-Powered Smart Vector Search & Caching</h2>
                <p class="cdoc-section-sub">Cosy Appointments features an advanced AI Semantic Search Engine powered by OpenAI's <code>text-embedding-3-small</code> vector embeddings and high-speed MySQL Cosine Similarity matching.</p>

                <div class="cdoc-alert">
                    <h4>🤖 Semantic Natural Language Search</h4>
                    <p>Unlike basic keyword search that fails when words don't match exactly, AI Vector Search understands user intent. For example, searching for <em>"someone to help my anxious child with math"</em> will intelligently match providers specializing in <em>"Child Psychology"</em> and <em>"Mathematics Tutoring"</em>.</p>
                </div>

                <div class="cdoc-grid">
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-brain"></i></div>
                        <h3>OpenAI Embedding Engine</h3>
                        <p>Uses <code>text-embedding-3-small</code> model to convert provider profiles into 1,536-dimensional vector arrays stored in <code>wp_provider_embeddings</code>.</p>
                    </div>
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-bolt"></i></div>
                        <h3>Instant Search Cache</h3>
                        <p>Search queries are hashed (MD5) and cached in <code>wp_cosychats_search_cache</code>. Repeat searches return in <strong>&lt; 10ms with $0.00 API cost</strong>.</p>
                    </div>
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-calculator"></i></div>
                        <h3>Cosine Similarity</h3>
                        <p>Calculates mathematical vector closeness score (0.00 to 1.00) between customer query and provider bio/services for instant ranking.</p>
                    </div>
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-sync"></i></div>
                        <h3>Auto Profile Indexer</h3>
                        <p>When a provider updates their profile or services, <code>ProfileIndexer.php</code> automatically re-indexes their vector embedding in the background.</p>
                    </div>
                </div>

                <h3 class="fw-bold text-dark mt-4 mb-3" style="font-family: 'Outfit', sans-serif; font-size: 1.15rem;">📌 Architecture & Database Tables</h3>
                <table class="wp-list-table widefat fixed striped" style="margin-bottom: 25px; border: 1px solid #eedced; border-radius: 8px; overflow: hidden;">
                    <thead>
                        <tr style="background: #faf5fb;">
                            <th style="font-weight: 700; width: 35%; padding: 12px;">Component / Table</th>
                            <th style="font-weight: 700; padding: 12px;">Function & Implementation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 12px; font-family: monospace;">wp_provider_embeddings</td>
                            <td style="padding: 12px;">Stores JSON-encoded 1536-dimensional float vector embeddings generated for each registered service provider.</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-family: monospace;">wp_cosychats_search_cache</td>
                            <td style="padding: 12px;">Stores MD5 query hashes, search prompt text, and matching provider IDs. Serves repeated user queries instantly without contacting OpenAI.</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-family: monospace;">REST API Endpoints</td>
                            <td style="padding: 12px;"><code>POST /wp-json/cosy/v1/ai-search</code> — Processes natural language search queries and returns ranked provider objects.</td>
                        </tr>
                    </tbody>
                </table>
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

                <div class="cdoc-step">
                    <div class="cdoc-step-num">4</div>
                    <div class="cdoc-step-body">
                        <h4>Standardized Exception & DB Error Logging</h4>
                        <p>The plugin includes centralized failure tracking via <code>LogManager::log_exception()</code> and <code>LogManager::log_db_error()</code>. Any caught PHP exception or failed MySQL query automatically logs the exact file name, line number, and <code>$wpdb->last_error</code> string into the audit log for zero-guesswork debugging.</p>
                    </div>
                </div>

                <div class="cdoc-step">
                    <div class="cdoc-step-num">5</div>
                    <div class="cdoc-step-body">
                        <h4>Centralized Admin AJAX Security (CSRF + Nonce + Capability)</h4>
                        <p>All administrative backend AJAX endpoints are secured via <code>verify_admin_ajax_request()</code> in <code>GlobalCommonFunctions.php</code>. This method auto-detects request token keys (<code>nonce</code> or <code>security</code>), checks current user login status, and validates required WP permissions before executing admin actions.</p>
                    </div>
                </div>

                <div class="cdoc-step">
                    <div class="cdoc-step-num">6</div>
                    <div class="cdoc-step-body">
                        <h4>High-Speed Directory Transients Caching</h4>
                        <p>Provider listings, prices, and ratings are cached using WordPress Transients (<code>get_transient</code> / <code>set_transient</code>) for sub-5ms repeat page load times. Whenever a provider updates profile details, working hours availability, or when a review is moderated, <code>cosy_clear_provider_transients()</code> automatically flushes stale caches.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="cdoc-toast" id="cdocToast">Copied to clipboard.</div>