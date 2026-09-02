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
                <li><a class="cdoc-nav-link" data-tab="email-templates"><i class="fas fa-envelope-open-text"></i> Email Templates & SMTP</a></li>
                <li><a class="cdoc-nav-link" data-tab="reviews-lifecycle"><i class="fas fa-comments"></i> Reviews & Moderation</a></li>
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
                        <h3>3-Level Reviews Lifecycle</h3>
                        <p>Customers submit ratings & reviews via secure single-use email invitation tokens. Supports a 3-level conversation thread (Provider Reply ➔ Customer Follow-up ➔ Provider Closing Response).</p>
                        <div style="margin-top:10px">
                            <span class="cdoc-tag">Token Links</span>
                            <span class="cdoc-tag">3-Level Replies</span>
                            <span class="cdoc-tag">Admin Moderation</span>
                        </div>
                    </div>
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-gift"></i></div>
                        <h3>Gift Bookings System</h3>
                        <p>Customers can book an appointment on behalf of someone else. The platform dispatches dedicated gift notification emails to the recipient with all session instructions.</p>
                        <div style="margin-top:10px">
                            <span class="cdoc-tag">Gift Checkout</span>
                            <span class="cdoc-tag">Recipient Alerts</span>
                            <span class="cdoc-tag">Admin Badges</span>
                        </div>
                    </div>
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-filter"></i></div>
                        <h3>Provider Filtering & AI Search</h3>
                        <p>Customers can filter providers by category, search by name, or use natural language AI Vector search powered by OpenAI embeddings to find their ideal provider.</p>
                        <div style="margin-top:10px">
                            <span class="cdoc-tag">AJAX Filter</span>
                            <span class="cdoc-tag">AI Semantic Search</span>
                            <span class="cdoc-tag">Instant Cache</span>
                        </div>
                    </div>
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-envelope-open-text"></i></div>
                        <h3>Email Templates & SMTP</h3>
                        <p>Visual split-screen WYSIWYG editor with live HTML preview, merge tags, protected system tables, test email dispatcher, and dynamic SMTP credentials manager.</p>
                        <div style="margin-top:10px">
                            <span class="cdoc-tag">WYSIWYG Editor</span>
                            <span class="cdoc-tag">Live Preview</span>
                            <span class="cdoc-tag">Dynamic SMTP</span>
                        </div>
                    </div>
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-cog"></i></div>
                        <h3>Central Settings Hub</h3>
                        <p>Administrators can configure WorldPay credentials, platform commission fees, video size limits, custom branding, and toggle audit logging per module.</p>
                        <div style="margin-top:10px">
                            <span class="cdoc-tag">WorldPay HPP</span>
                            <span class="cdoc-tag">Service Fees</span>
                            <span class="cdoc-tag">Audit Logs</span>
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

                <h3 class="fw-bold text-dark mt-4 mb-3" style="font-family: 'Outfit', sans-serif; font-size: 1.15rem;">3️⃣ Orders Management & 🎁 Gift Bookings (OrdersAdmin.php)</h3>
                <p>Treats bookings as discrete financial and service Orders, giving administrators order status controls, granular transaction details, and gift recipient insights:
                    <br>• <strong>Order ID:</strong> Formatted with a hash indicator (e.g. <code>#123</code>).
                    <br>• <strong>Cost Breakdown:</strong> Shows the provider's base fee, the net platform service fee, and the gross total paid.
                    <br>• <strong>Gift Bookings:</strong> When a customer books on behalf of someone else (<code>cosy_is_gift = 1</code>), the system records the recipient's name and email address. A dedicated <em>Gift Order Details</em> card is displayed in the Admin Order modal, and the recipient automatically receives a branded confirmation email with session instructions.
                </p>

                <h3 class="fw-bold text-dark mt-4 mb-3" style="font-family: 'Outfit', sans-serif; font-size: 1.15rem;">4️⃣ End-to-End Booking Data Flow</h3>
                <p>The booking lifecycle coordinates actions across all user roles, ensuring real-time notification alerts, data logs, and updates:</p>
                <div style="background: #faf5fb; border: 1px solid #eedced; border-radius: 12px; padding: 20px; font-family: 'Plus Jakarta Sans', sans-serif; margin-bottom: 25px;">
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <div><strong style="color: #a44390;">Step 1: Booking Initiation</strong> — Customer selects a service, calendar date, time slot, specifies if it's a gift (optional recipient info), and enters checkout. An unpaid draft appointment is created.</div>
                        <div><strong style="color: #a44390;">Step 2: Payment Confirmation</strong> — WorldPay processes payment. The draft booking shifts to published, updating payment state to Paid.</div>
                        <div><strong style="color: #a44390;">Step 3: Multi-Recipient Notifications</strong> — Customer receives booking confirmation receipt. If gifted, the recipient receives a dedicated gift booking alert. The provider and site administrator receive transaction alerts.</div>
                        <div><strong style="color: #a44390;">Step 4: Provider Dashboard Action</strong> — Provider confirms/completes/cancels the appointment. The customer receives status emails and activity is logged.</div>
                        <div><strong style="color: #a44390;">Step 5: Review Invitation</strong> — Upon appointment completion, the customer receives an automated email with a secure single-use token link to submit a review.</div>
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
                            <td style="padding: 12px; font-family: monospace;">cosy_is_gift / cosy_recipient_email</td>
                            <td style="padding: 12px;">Tracks whether the booking is a gift and stores the recipient's name and email.</td>
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
                            <td style="padding: 12px;">Custom table linking providers to services and pricing.</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-family: monospace;">wp_cosy_provider_reviews</td>
                            <td style="padding: 12px;">Custom table storing star ratings, customer reviews, and status (pending, approved, rejected).</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-family: monospace;">wp_cosy_review_replies</td>
                            <td style="padding: 12px;">Custom table storing 3-level threaded conversation replies between providers and customers.</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-family: monospace;">wp_cosy_review_tokens</td>
                            <td style="padding: 12px;">Custom table tracking single-use invitation tokens generated for customer reviews.</td>
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

            <!-- EMAIL TEMPLATES & SMTP -->
            <div id="cdoc-email-templates" class="cdoc-pane">
                <h2 class="cdoc-section-title">Email Templates Manager & Dynamic SMTP Setup</h2>
                <p class="cdoc-section-sub">Cosy Appointments includes a powerful split-screen Visual Email Templates Manager and Dynamic SMTP Configuration system located under <strong>CC Booking → Emails</strong> and <strong>CC Booking → Settings</strong>.</p>

                <div class="cdoc-alert">
                    <h4>📧 Fully Customizable Transactional Emails with Live Sync</h4>
                    <p>Administrators can customize the subject line, heading banner, intro text, and outro text for all 9 automated system emails. The editor features real-time live preview synchronization, brand signature protection, and live test email dispatching.</p>
                </div>

                <div class="cdoc-grid">
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-desktop"></i></div>
                        <h3>Interactive Live Preview</h3>
                        <p>Split-screen layout updates the right-hand email preview in real-time as you type in the WYSIWYG editor on the left with zero page reloads.</p>
                    </div>
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-tags"></i></div>
                        <h3>Dynamic Merge Tags</h3>
                        <p>One-click copy tags like <code>{customer_name}</code>, <code>{provider_name}</code>, <code>{service_name}</code>, <code>{order_id}</code>, and <code>{total_payable}</code> populate live data.</p>
                    </div>
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-shield-halved"></i></div>
                        <h3>Protected System Tables</h3>
                        <p>Order financial breakdowns, time slot badges, and brand signatures are rendered dynamically and protected from accidental template deletion.</p>
                    </div>
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-paper-plane"></i></div>
                        <h3>Instant Test Mail & Reset</h3>
                        <p>Send a real test email with sample data to your admin inbox instantly, or restore original factory defaults anytime with one click.</p>
                    </div>
                </div>

                <h3 class="fw-bold text-dark mt-4 mb-3" style="font-family: 'Outfit', sans-serif; font-size: 1.15rem;">📋 Standardized System Email Templates</h3>
                <table class="wp-list-table widefat fixed striped" style="margin-bottom: 25px; border: 1px solid #eedced; border-radius: 8px; overflow: hidden;">
                    <thead>
                        <tr style="background: #faf5fb;">
                            <th style="font-weight: 700; width: 30%; padding: 12px;">Template Name</th>
                            <th style="font-weight: 700; width: 25%; padding: 12px;">Recipient</th>
                            <th style="font-weight: 700; padding: 12px;">Trigger / Purpose</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 12px; font-weight: 600;">Customer Booking Confirmation</td>
                            <td style="padding: 12px;"><span class="cdoc-tag">Customer</span></td>
                            <td style="padding: 12px;">Dispatched immediately after WorldPay checkout payment is confirmed.</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-weight: 600;">Provider Booking Notification</td>
                            <td style="padding: 12px;"><span class="cdoc-tag">Provider</span></td>
                            <td style="padding: 12px;">Alerts provider when a customer books and pays for their service.</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-weight: 600;">Order Status Update</td>
                            <td style="padding: 12px;"><span class="cdoc-tag">Customer</span></td>
                            <td style="padding: 12px;">Sent when appointment is marked as Confirmed, Completed, or Cancelled.</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-weight: 600;">Booking Cancellation Alert</td>
                            <td style="padding: 12px;"><span class="cdoc-tag">Customer / Provider</span></td>
                            <td style="padding: 12px;">Dispatched if an appointment is cancelled by either party or admin.</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-weight: 600;">Customer Review Invitation</td>
                            <td style="padding: 12px;"><span class="cdoc-tag">Customer</span></td>
                            <td style="padding: 12px;">Delivers a secure single-use review link after an appointment is completed.</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-weight: 600;">Customer Email Verification</td>
                            <td style="padding: 12px;"><span class="cdoc-tag">Customer</span></td>
                            <td style="padding: 12px;">Sent on customer registration with an account activation link.</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-weight: 600;">Provider Email Verification</td>
                            <td style="padding: 12px;"><span class="cdoc-tag">Provider</span></td>
                            <td style="padding: 12px;">Sent on provider registration with identity verification instructions.</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-weight: 600;">Password Reset Request</td>
                            <td style="padding: 12px;"><span class="cdoc-tag">All Users</span></td>
                            <td style="padding: 12px;">Delivers secure temporary password reset URL upon forgot password request.</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; font-weight: 600;">Gift Recipient Booking Alert</td>
                            <td style="padding: 12px;"><span class="cdoc-tag">Recipient</span></td>
                            <td style="padding: 12px;">Sent to the gift recipient with details on their gifted parent conversation.</td>
                        </tr>
                    </tbody>
                </table>

                <h3 class="fw-bold text-dark mt-4 mb-3" style="font-family: 'Outfit', sans-serif; font-size: 1.15rem;">⚙️ Dynamic SMTP Configuration (CC Booking → Settings)</h3>
                <div class="cdoc-step">
                    <div class="cdoc-step-num"><i class="fas fa-server"></i></div>
                    <div class="cdoc-step-body">
                        <h4>Configure Custom SMTP Server</h4>
                        <p>Under <strong>CC Booking → Settings → SMTP Configuration</strong>, you can specify custom SMTP credentials to ensure 100% email deliverability into inboxes:
                            <br>• <strong>SMTP Host & Port:</strong> e.g. <code>smtp.gmail.com</code> (Port 587 or 465).
                            <br>• <strong>Encryption:</strong> TLS or SSL.
                            <br>• <strong>Authentication:</strong> Username (email) and App Password.
                            <br>• <strong>Custom From Name & Email:</strong> Set official sender name (e.g. <em>CosyChats Support</em>) and reply-to address.
                            <br>• <strong>Send SMTP Test:</strong> Verify connection directly with one-click test mail.
                        </p>
                    </div>
                </div>
            </div>

            <!-- REVIEWS LIFECYCLE & 3-LEVEL REPLIES -->
            <div id="cdoc-reviews-lifecycle" class="cdoc-pane">
                <h2 class="cdoc-section-title">Reviews Moderation & 3-Level Threaded Lifecycle</h2>
                <p class="cdoc-section-sub">Cosy Appointments includes a complete multi-level review and moderation ecosystem ensuring authentic parent feedback and structured public responses.</p>

                <div class="cdoc-alert">
                    <h4>⭐ Verified Token-Based Review Submissions</h4>
                    <p>To prevent fake reviews, only customers with confirmed appointments receive unique single-use review tokens (<code>/cosy-leave-review?token=...</code>). Once submitted, the token is automatically invalidated to prevent duplicate entries.</p>
                </div>

                <div class="cdoc-grid">
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-user-check"></i></div>
                        <h3>Admin Moderation Panel</h3>
                        <p>Navigate to <strong>CC Booking → Reviews</strong> to view all customer reviews with star ratings. Administrators can <strong>Approve</strong>, <strong>Reject</strong>, or <strong>Delete</strong> reviews with instant email alerts.</p>
                    </div>
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-comments"></i></div>
                        <h3>3-Level Threaded Replies</h3>
                        <p>Structured 3-tier conversation lifecycle between Provider and Customer allows resolving feedback publicly while maintaining professionalism.</p>
                    </div>
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-key"></i></div>
                        <h3>Single-Use Token Security</h3>
                        <p>Tokens are tracked in <code>wp_cosy_review_tokens</code> and mapped directly to customer ID and provider ID for foolproof verification.</p>
                    </div>
                    <div class="cdoc-card">
                        <div class="cdoc-card-icon"><i class="fas fa-bolt"></i></div>
                        <h3>Transient Cache Invalidation</h3>
                        <p>Whenever a review is approved, rejected, or replied to, directory cache transients are automatically cleared for instant public visibility.</p>
                    </div>
                </div>

                <h3 class="fw-bold text-dark mt-4 mb-3" style="font-family: 'Outfit', sans-serif; font-size: 1.15rem;">🔄 The 3-Level Conversation Lifecycle Explained</h3>
                <div class="cdoc-step">
                    <div class="cdoc-step-num">1</div>
                    <div class="cdoc-step-body">
                        <h4>Level 1: Provider Public Reply</h4>
                        <p>When an admin approves a review, the provider can post an initial public response directly from their Provider Dashboard (Customer Reviews tab). The customer automatically receives an email notification with the provider's reply.</p>
                    </div>
                </div>

                <div class="cdoc-step">
                    <div class="cdoc-step-num">2</div>
                    <div class="cdoc-step-body">
                        <h4>Level 2: Customer Follow-Up Response</h4>
                        <p>If the customer wants to add further context or thank the provider, they can post a Level 2 follow-up reply in their review thread. The provider receives an email notification about the customer's follow-up.</p>
                    </div>
                </div>

                <div class="cdoc-step">
                    <div class="cdoc-step-num">3</div>
                    <div class="cdoc-step-body">
                        <h4>Level 3: Provider Final Closing Response</h4>
                        <p>The provider can post a Level 3 final closing message. When submitted, the system compiles the full transcript of all 3 levels and emails the complete conversation history to the customer.</p>
                    </div>
                </div>
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
                        <h4>Comprehensive Activity Audit Logging</h4>
                        <p>Navigate to <strong>CC Booking → Logs</strong> to view a real-time table of logged actions across the entire platform. Every entry records the timestamp, user ID, user display name, user role (Admin, Provider, Customer, Guest), IP address, and a rich descriptive message.
                            <br><br><strong>Logged Activity Categories:</strong>
                            <br>• <strong>Authentication & Security:</strong> Successful user logins, failed login attempts (invalid credentials), blocked unverified accounts, blocked deactivated accounts, password reset requests & failures.
                            <br>• <strong>Bookings & Payments:</strong> Checkout sessions initiated, WorldPay webhooks received/processed, payment completions, payment failures, and status updates (Confirmed, Completed, Cancelled).
                            <br>• <strong>Experiences & Services:</strong> Admin service creations, updates, and trashing, plus Provider REST API service additions and edits.
                            <br>• <strong>Media Approvals:</strong> Provider introductory video uploads, video deletions, and admin approvals/rejections.
                            <br>• <strong>Reviews & Ratings:</strong> Customer review submissions, single-use token review submissions, 3-level threaded replies (Provider & Customer), and admin review moderation.
                            <br>• <strong>System & Settings:</strong> Setting modifications (with sensitive secrets masked), log section toggle switches, and automated 30-day cron cleanups.
                        </p>
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