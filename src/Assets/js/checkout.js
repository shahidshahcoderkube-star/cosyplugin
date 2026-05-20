/**
 * checkout.js
 * Handles dynamic rendering and payment processing for the checkout page.
 * Uses the Modular Pattern and Event Delegation for safe execution.
 */
jQuery(document).ready(function($) {
    'use strict';

    const container = document.getElementById('cosyCheckoutContainer');
    
    // Abort if we are not on the checkout page (container doesn't exist)
    if (!container) return;

    // 1. Clean UI: Hide the default WordPress page title "Checkout" and its parent spacing
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

    const pendingBookingData = localStorage.getItem('cosy_pending_booking');

    if (!pendingBookingData) {
        container.innerHTML = `
            <div class="cosy-checkout-empty-state shadow-sm">
                <i class="fas fa-calendar-times"></i>
                <h3>No Active Booking Session</h3>
                <p>It seems you haven't selected a provider service or booking slots yet.</p>
                <a href="${cosyCheckout.providerUrl}" class="cosy-checkout-back-btn">
                    <i class="fas fa-arrow-left"></i> Browse Providers
                </a>
            </div>
        `;
        return;
    }

    const booking = JSON.parse(pendingBookingData);

    // Fetch logged-in user info safely from the wp_localize_script object (cosyCheckout)
    const customerName = cosyCheckout.customerName || 'Valued Customer';
    const customerEmail = cosyCheckout.customerEmail || '';

    // 2. Render Checkout UI
    container.innerHTML = `
        <!-- Top Header Block -->
        <div class="cosy-checkout-header">
            <button id="cosyCheckoutBackBtn" class="cosy-checkout-back-btn">
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
            Once you have selected 'Pay Now' you will be redirected to the secure, encrypted payment checkout page powered by Stripe. 
            Here you will be asked to enter your payment details in order that your booking be completed on behalf of our company, 
            Out of Darkness Comes Light Ltd. A UK registered company. Please note we are currently only able to accept GBP payments. Thank you.
        </div>

        <!-- Payment Logos -->
        <div class="cosy-payment-logos-wrapper">
            <div class="cosy-worldpay-brand" style="color: #635bff !important; font-weight: 800;">
                <span>stripe</span>
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
            </div>
        </div>

        <!-- Pay Now Trigger -->
        <button id="cosyPayNowBtn" class="cosy-pay-now-btn">
            Pay Now
        </button>
    `;

    // 3. Bind Events using robust jQuery Event Delegation (replaces onclick="")
    $(document).on('click', '#cosyCheckoutBackBtn', function(e) {
        e.preventDefault();
        window.history.back();
    });

    $(document).on('click', '#cosyPayNowBtn', function(e) {
        e.preventDefault();
        
        const btn = $(this);
        const bookingDataRaw = localStorage.getItem('cosy_pending_booking');

        if (!bookingDataRaw) return;
        const currentBooking = JSON.parse(bookingDataRaw);

        // Validate that Stripe key is loaded
        if (!cosyCheckout.stripePublishableKey) {
            Swal.fire({
                title: 'Configuration Error',
                text: 'Stripe is not fully configured. Please configure keys in dashboard.',
                icon: 'error',
                confirmButtonColor: '#635bff'
            });
            return;
        }

        // Show elegant loading spinner and disable button
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Initializing Secure Stripe Checkout...');

        // Prepare raw slots JSON data to post
        const slotsJson = JSON.stringify(currentBooking.slots);

        // Perform AJAX Request using safely localized URL
        $.ajax({
            url: cosyCheckout.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cosy_create_stripe_session',
                service: currentBooking.service,
                providerId: currentBooking.providerId,
                providerName: currentBooking.providerName,
                startDate: currentBooking.startDate,
                endDate: currentBooking.endDate,
                weeklyBooking: currentBooking.weeklyBooking,
                numberOfWeeks: currentBooking.numberOfWeeks,
                numberOfBookings: currentBooking.numberOfBookings,
                serviceCost: currentBooking.serviceCost,
                serviceFee: currentBooking.serviceFee,
                totalPayable: currentBooking.totalPayable,
                slots: slotsJson
            },
            success: function(response) {
                if (response.success && response.data.url) {
                    // Redirect to the Stripe hosted secure checkout URL
                    window.location.href = response.data.url;
                } else {
                    btn.prop('disabled', false).html('Pay Now');
                    Swal.fire({
                        title: 'Stripe Error',
                        text: response.data.message || 'Unable to create payment session.',
                        icon: 'error',
                        confirmButtonColor: '#635bff'
                    });
                }
            },
            error: function() {
                btn.prop('disabled', false).html('Pay Now');
                Swal.fire({
                    title: 'System Error',
                    text: 'An error occurred during communication with the server. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#635bff'
                });
            }
        });
    });
});
