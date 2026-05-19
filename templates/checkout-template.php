<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
$customer_name = $current_user->exists() ? esc_html($current_user->display_name) : '';
$customer_email = $current_user->exists() ? esc_html($current_user->user_email) : '';
?>

<!-- Load Google Font and FontAwesome for absolute consistency -->
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Premium Checkout Theme Core Override */
    .cosy-checkout-root {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        background-color: transparent; /* Inherit theme background color */
        color: #1e293b;
        min-height: auto;
        padding: 0px 0 40px 0; /* Minimized top padding to zero */
    }

    .cosy-checkout-root h2,
    .cosy-checkout-root h3,
    .cosy-checkout-root h4,
    .cosy-checkout-root h5 {
        font-family: 'Outfit', sans-serif !important;
        font-weight: 700;
    }

    .cosy-checkout-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Top Header navigation matching profile styles */
    .cosy-checkout-header {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 40px;
        border-bottom: 2px solid #f1e4ef;
        padding-bottom: 20px;
    }

    .cosy-checkout-back-btn {
        background: #6d2e67 !important; /* Branded deep purple */
        color: #ffffff !important;
        border: none !important;
        padding: 10px 24px !important;
        font-size: 0.9rem !important;
        font-weight: 700 !important;
        border-radius: 12px !important; /* Branded rounded corner */
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        text-decoration: none !important;
        box-shadow: 0 4px 10px rgba(109, 46, 103, 0.15);
    }

    .cosy-checkout-back-btn:hover {
        background: #a44390 !important; /* Highlight brand purple */
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(164, 67, 144, 0.25);
    }

    .cosy-checkout-back-btn i,
    .cosy-checkout-back-btn svg {
        color: #ffffff !important; /* Force white arrow color */
        font-size: 0.95rem !important; /* Match text size proportions */
        line-height: 1 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 !important;
        padding: 0 !important;
        height: auto !important;
        width: auto !important;
    }

    .cosy-checkout-title {
        font-size: 2rem;
        font-weight: 800;
        color: #6d2e67; /* Branded deep purple */
        margin: 0;
        letter-spacing: -0.5px;
    }

    /* Bento Cards Styling */
    .cosy-bento-panel {
        background: #ffffff;
        border-radius: 18px; /* Elegant curves */
        border: 1px solid #f1e4ef;
        box-shadow: 0 10px 30px rgba(109, 46, 103, 0.04);
        margin-bottom: 30px;
        overflow: hidden;
    }

    .cosy-bento-panel-header {
        background: #fdf2fb; /* Selected service row color tint */
        border-bottom: 1px solid #f1e4ef;
        padding: 18px 24px;
        font-weight: 700;
        font-size: 1.1rem;
        color: #6d2e67; /* Branded purple heading */
        letter-spacing: -0.3px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .cosy-bento-panel-header::before {
        content: '';
        display: inline-block;
        width: 6px;
        height: 18px;
        background: #a44390;
        border-radius: 3px;
    }

    .cosy-bento-panel-body {
        padding: 24px;
    }

    /* Table layout matches screenshot design exactly */
    .cosy-checkout-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }

    .cosy-checkout-table tr {
        border-bottom: 1px solid #fdf2fb;
    }

    .cosy-checkout-table tr:last-child {
        border-bottom: none;
    }

    .cosy-checkout-table td {
        padding: 16px 24px;
        font-size: 0.95rem;
        color: #475569;
        vertical-align: middle;
    }

    .cosy-checkout-table td.label-col {
        width: 32%;
        font-weight: 600;
        color: #475569;
    }

    .cosy-checkout-table td.separator-col {
        width: 5%;
        text-align: center;
        color: #c25ca9;
    }

    .cosy-checkout-table td.value-col {
        width: 63%;
        font-weight: 600;
        color: #1e293b;
    }

    /* Striped rows inside tables to match screenshot exactly */
    .cosy-checkout-table tr:nth-child(odd) td {
        background-color: #fdfafc;
    }

    /* Pricing specific typography */
    .cost-value {
        font-weight: 700;
    }

    .total-payable-row td {
        background-color: #fdf2fb !important;
    }
    
    .total-payable-row td.value-col {
        color: #a44390; /* Brand Accent purple for total */
        font-size: 1.15rem;
        font-weight: 800;
    }

    /* Worldpay Information Section */
    .cosy-checkout-disclaimer {
        font-size: 0.8rem;
        color: #64748b;
        line-height: 1.6;
        margin-bottom: 25px;
        background: rgba(253, 242, 251, 0.4);
        padding: 15px 20px;
        border-radius: 12px;
        border: 1.5px solid #f1e4ef;
    }

    .cosy-checkout-disclaimer a {
        color: #a44390;
        text-decoration: underline;
        font-weight: 600;
    }

    .cosy-checkout-disclaimer a:hover {
        color: #6d2e67;
    }

    /* Payment Logos Grid */
    .cosy-payment-logos-wrapper {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 40px;
    }

    .cosy-worldpay-brand {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.8rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .cosy-worldpay-brand span {
        color: #a44390;
    }

    .cosy-payment-card-icons {
        display: flex;
        gap: 10px;
    }

    .cosy-payment-card-icons svg,
    .cosy-payment-card-icons img {
        height: 28px;
        width: auto;
        object-fit: contain;
    }

    /* Pay Now Button (Bottom Left) */
    .cosy-pay-now-btn {
        background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%) !important; /* Premium brand purple gradient */
        color: #ffffff !important;
        border: none !important;
        padding: 16px 50px !important;
        font-size: 1rem !important;
        font-weight: 700 !important;
        border-radius: 12px !important; /* Curvy matching theme */
        box-shadow: 0 8px 20px rgba(164, 67, 144, 0.25) !important;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .cosy-pay-now-btn:hover {
        background: linear-gradient(135deg, #b852a3 0%, #7d3b76 100%) !important;
        box-shadow: 0 10px 25px rgba(164, 67, 144, 0.35) !important;
        transform: translateY(-2px);
    }

    /* Empty state */
    .cosy-checkout-empty-state {
        text-align: center;
        padding: 60px 40px;
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
    }

    .cosy-checkout-empty-state i {
        font-size: 3rem;
        color: #a44390;
        margin-bottom: 20px;
    }

    .cosy-checkout-empty-state h3 {
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 10px;
    }

    .cosy-checkout-empty-state p {
        color: #64748b;
        margin-bottom: 24px;
    }
</style>

<div class="cosy-checkout-root">
    <div class="cosy-checkout-container" id="cosyCheckoutContainer">
        <!-- Rendered dynamically by JavaScript -->
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Hide the default WordPress page title "Checkout" and its parent spacing
        document.querySelectorAll('h1, h2, .entry-title, .page-title').forEach(el => {
            if (el.textContent.trim().toLowerCase() === 'checkout' && !el.classList.contains('cosy-checkout-title')) {
                el.style.display = 'none';
                
                // Hide parent entry-header or page-header containers to completely eliminate spacing
                const parentHeader = el.closest('.entry-header, .page-header, header');
                if (parentHeader) {
                    parentHeader.style.display = 'none';
                    parentHeader.style.margin = '0';
                    parentHeader.style.padding = '0';
                }
            }
        });

        const container = document.getElementById('cosyCheckoutContainer');
        const pendingBookingData = localStorage.getItem('cosy_pending_booking');

        if (!pendingBookingData) {
            container.innerHTML = `
                <div class="cosy-checkout-empty-state shadow-sm">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No Active Booking Session</h3>
                    <p>It seems you haven't selected a provider service or booking slots yet.</p>
                    <a href="<?php echo esc_url(site_url('/service-provider')); ?>" class="cosy-checkout-back-btn">
                        <i class="fas fa-arrow-left"></i> Browse Providers
                    </a>
                </div>
            `;
            return;
        }

        const booking = JSON.parse(pendingBookingData);

        // Fetch logged-in user info from window object or local PHP injection
        const customerName = <?php echo json_encode($customer_name); ?> || window.currentUser.name || 'Valued Customer';
        const customerEmail = <?php echo json_encode($customer_email); ?> || window.currentUser.email || '';

        container.innerHTML = `
            <!-- Top Header Block -->
            <div class="cosy-checkout-header">
                <button onclick="goBack()" class="cosy-checkout-back-btn">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
                <h1 class="cosy-checkout-title">Your Booking Summary</h1>
            </div>

            <!-- Bento Card 1: Service Information -->
            <div class="cosy-bento-panel">
                <div class="cosy-bento-panel-header">
                    Service Information :
                </div>
                <div class="cosy-bento-panel-body p-0">
                    <table class="cosy-checkout-table">
                        <tr>
                            <td class="label-col">Service</td>
                            <td class="separator-col">:</td>
                            <td class="value-col fw-bold text-dark">${booking.service}</td>
                        </tr>
                        <tr>
                            <td class="label-col">Provider</td>
                            <td class="separator-col">:</td>
                            <td class="value-col">${booking.providerName}</td>
                        </tr>
                        <tr>
                            <td class="label-col">Start Date</td>
                            <td class="separator-col">:</td>
                            <td class="value-col">${booking.startDate}</td>
                        </tr>
                        <tr>
                            <td class="label-col">Weekly Booking</td>
                            <td class="separator-col">:</td>
                            <td class="value-col fw-semibold text-dark">${booking.weeklyBooking}</td>
                        </tr>
                        <tr>
                            <td class="label-col">End Date</td>
                            <td class="separator-col">:</td>
                            <td class="value-col">${booking.endDate}</td>
                        </tr>
                        <tr>
                            <td class="label-col">Number of Weeks</td>
                            <td class="separator-col">:</td>
                            <td class="value-col">${booking.numberOfWeeks}</td>
                        </tr>
                        <tr>
                            <td class="label-col">Number of Booking slots</td>
                            <td class="separator-col">:</td>
                            <td class="value-col">${booking.numberOfBookings}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Bento Card 2: Customer Information -->
            <div class="cosy-bento-panel">
                <div class="cosy-bento-panel-header">
                    Customer Information :
                </div>
                <div class="cosy-bento-panel-body p-0">
                    <table class="cosy-checkout-table">
                        <tr>
                            <td class="label-col">Name</td>
                            <td class="separator-col">:</td>
                            <td class="value-col">${customerName}</td>
                        </tr>
                        <tr>
                            <td class="label-col">Email</td>
                            <td class="separator-col">:</td>
                            <td class="value-col">${customerEmail}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Bento Card 3: Costing Information -->
            <div class="cosy-bento-panel">
                <div class="cosy-bento-panel-header">
                    Costing Information :
                </div>
                <div class="cosy-bento-panel-body p-0">
                    <table class="cosy-checkout-table">
                        <tr>
                            <td class="label-col">Service Cost</td>
                            <td class="separator-col">:</td>
                            <td class="value-col cost-value">£ ${booking.serviceCost}</td>
                        </tr>
                        <tr>
                            <td class="label-col">Service Fee</td>
                            <td class="separator-col">:</td>
                            <td class="value-col cost-value">£ ${booking.serviceFee}</td>
                        </tr>
                        <tr class="total-payable-row">
                            <td class="label-col fw-bold text-dark">Total Payable Amount</td>
                            <td class="separator-col fw-bold text-dark">:</td>
                            <td class="value-col cost-value">£ ${booking.totalPayable}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Disclaimer Paragraph -->
            <div class="cosy-checkout-disclaimer">
                Once you have selected 'Pay Now' you will be directed to the Secure page of our Global payment provider Worldpay FIS. 
                Here you will be asked to enter your details in order that your payment be processed on behalf of our company, 
                Out of Darkness Comes Light Ltd. A UK registered company, office Address Byways House, Ardleighgreen Road, Hornchurch, Essex, RM11 2LE. 
                Registered Company number 08812437. Below is our <a href="#">Refund Policy</a> and <a href="#">Contact us</a> details. 
                Please note we are currently only able to accept GBP payments. Thank you.
            </div>

            <!-- Payment Logos -->
            <div class="cosy-payment-logos-wrapper">
                <div class="cosy-worldpay-brand">
                    <span>world</span>pay
                </div>
                <div class="cosy-payment-card-icons">
                    <!-- High definition inline SVGs of Visa, Mastercard, Maestro, JCB -->
                    <!-- Visa Logo SVG -->
                    <svg viewBox="0 0 24 15" width="40" height="25" xmlns="http://www.w3.org/2000/svg">
                        <rect width="24" height="15" fill="#1A1F71" rx="2"/>
                        <path d="M9.8 11.2h.9l.6-3.7H10.4zm2.8-3.7c-.2-.6-.8-.8-1.4-.8-.9 0-1.7.5-1.7 1.4 0 .9.8 1.2 1.3 1.5.5.2.7.4.7.6 0 .4-.5.5-.9.5-.7 0-1.1-.3-1.4-.6l-.2 1.4c.4.2 1 .3 1.6.3 1.7 0 2.2-.8 2.2-1.7 0-1.1-.9-1.4-1.5-1.7-.5-.3-.7-.5-.7-.7.1-.4.5-.6 1.1-.6.5 0 .9.2 1.1.4zm3-.1L14.4 11.2h.9L16.5 7.4zm-7.7.1L6.4 11.2h1.1l.6-3.1h1.1l.2-1.2H8.3L8.9 4H7.8z" fill="#F7B600"/>
                    </svg>
                    <!-- Mastercard Logo SVG -->
                    <svg viewBox="0 0 24 15" width="40" height="25" xmlns="http://www.w3.org/2000/svg">
                        <rect width="24" height="15" fill="#0A0A0A" rx="2"/>
                        <circle cx="10" cy="7.5" r="4.5" fill="#EB001B"/>
                        <circle cx="14" cy="7.5" r="4.5" fill="#F79E1B" fill-opacity="0.85"/>
                    </svg>
                    <!-- Maestro Logo SVG -->
                    <svg viewBox="0 0 24 15" width="40" height="25" xmlns="http://www.w3.org/2000/svg">
                        <rect width="24" height="15" fill="#003B70" rx="2"/>
                        <circle cx="10" cy="7.5" r="4.5" fill="#00A2E1"/>
                        <circle cx="14" cy="7.5" r="4.5" fill="#EB001B" fill-opacity="0.85"/>
                    </svg>
                    <!-- JCB Logo SVG -->
                    <svg viewBox="0 0 24 15" width="40" height="25" xmlns="http://www.w3.org/2000/svg">
                        <rect width="24" height="15" fill="#FFFFFF" rx="2" stroke="#e2e8f0" stroke-width="0.5"/>
                        <rect x="2" y="3" width="6" height="9" fill="#062F87" rx="1"/>
                        <rect x="9" y="3" width="6" height="9" fill="#D8121A" rx="1"/>
                        <rect x="16" y="3" width="6" height="9" fill="#008138" rx="1"/>
                        <text x="5" y="10" font-family="'Outfit', sans-serif" font-weight="800" font-size="6" fill="#FFFFFF" text-anchor="middle">J</text>
                        <text x="12" y="10" font-family="'Outfit', sans-serif" font-weight="800" font-size="6" fill="#FFFFFF" text-anchor="middle">C</text>
                        <text x="19" y="10" font-family="'Outfit', sans-serif" font-weight="800" font-size="6" fill="#FFFFFF" text-anchor="middle">B</text>
                    </svg>
                </div>
            </div>

            <!-- Pay Now Trigger -->
            <button onclick="processPayNow()" id="cosyPayNowBtn" class="cosy-pay-now-btn">
                Pay Now
            </button>
        `;
    });

    function goBack() {
        window.history.back();
    }

    function processPayNow() {
        const btn = document.getElementById('cosyPayNowBtn');
        const pendingBookingData = localStorage.getItem('cosy_pending_booking');

        if (!pendingBookingData) return;
        const booking = JSON.parse(pendingBookingData);

        // Show elegant loading spinner
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing Secure Payment...';

        // Prepare raw slots JSON data to post
        const slotsJson = JSON.stringify(booking.slots);

        jQuery.ajax({
            url: <?php echo json_encode(admin_url('admin-ajax.php')); ?>,
            type: 'POST',
            data: {
                action: 'cosy_create_booking',
                service: booking.service,
                providerId: booking.providerId,
                providerName: booking.providerName,
                startDate: booking.startDate,
                endDate: booking.endDate,
                weeklyBooking: booking.weeklyBooking,
                numberOfWeeks: booking.numberOfWeeks,
                numberOfBookings: booking.numberOfBookings,
                serviceCost: booking.serviceCost,
                serviceFee: booking.serviceFee,
                totalPayable: booking.totalPayable,
                slots: slotsJson
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Payment Successful!',
                        text: 'Your booking has been registered and payment of £' + booking.totalPayable + ' was processed securely via Worldpay FIS.',
                        icon: 'success',
                        confirmButtonText: 'View My Bookings',
                        confirmButtonColor: '#dc2626',
                        background: '#ffffff',
                        customClass: {
                            popup: 'swal2-bento-popup',
                            title: 'swal2-bento-title',
                            htmlContainer: 'swal2-bento-text',
                            confirmButton: 'swal2-bento-btn'
                        }
                    }).then((result) => {
                        // Clear the local storage
                        localStorage.removeItem('cosy_pending_booking');
                        // Redirect to the customer profile / customer orders page
                        window.location.href = <?php echo json_encode(site_url('/customer-profile')); ?>;
                    });
                } else {
                    btn.disabled = false;
                    btn.innerHTML = 'Pay Now';
                    Swal.fire({
                        title: 'Payment Failed',
                        text: response.data.message || 'Unable to authorize transaction.',
                        icon: 'error',
                        confirmButtonColor: '#dc2626'
                    });
                }
            },
            error: function() {
                btn.disabled = false;
                btn.innerHTML = 'Pay Now';
                Swal.fire({
                    title: 'System Error',
                    text: 'An error occurred during communication with Worldpay. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#dc2626'
                });
            }
        });
    }
</script>