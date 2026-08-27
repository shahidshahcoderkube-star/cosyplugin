/**
 * COSY CHECKOUT & CALL SCHEDULE CONTROLLER MODULE
 * 
 * USE CASE:
 * Manages dynamic rendering and interactive step transitions for Call Schedule selection and final Payment Summary.
 * 
 * HOW TO USE:
 * Executed on cosy-checkout page load. Reads URL parameters (?step=schedule&start_date=...) and localStorage.
 * 
 * WHAT IT DOES INTERNALLY:
 * 1. Checks current active checkout step ('schedule' vs 'summary').
 * 2. Renders interactive weekly call duration picker or final WooCommerce checkout summary.
 * 3. Handles hold countdown timers, slot validation, and AJAX order placement.
 */
jQuery(document).ready(function ($) {
    'use strict';

    const container = document.getElementById('cosyCheckoutContainer');
    if (!container) return;

    // 1. Hide default page header spacing
    document.querySelectorAll('h1, h2, .entry-title, .page-title').forEach(el => {
        if (el.textContent.trim().toLowerCase() === 'checkout' && !el.classList.contains('cosy-checkout-title')) {
            el.style.display = 'none';
            const parentHeader = el.closest('.entry-header, .page-header, header');
            if (parentHeader) {
                parentHeader.style.display = 'none';
                parentHeader.style.margin = '0';
                parentHeader.style.padding = '0';
            }
        }
    });

    const urlParams = new URLSearchParams(window.location.search);
    let currentStep = urlParams.get('step') || 'schedule';
    const startDateParam = urlParams.get('start_date') || localStorage.getItem('cosy_selected_start_date');
    const providerIdParam = urlParams.get('provider_id') || localStorage.getItem('cosy_selected_provider_id');
    const providerNameParam = localStorage.getItem('cosy_selected_provider_name') || 'Verified Parent';

    const rawServiceName = urlParams.get('service_name') || urlParams.get('service_category') || urlParams.get('service') || localStorage.getItem('cosy_selected_service_name') || 'Parent Conversation';
    
    /**
     * FORMAT SERVICE NAME NICE
     * 
     * USE CASE: Cleans up raw service category slug strings for public display.
     * HOW TO USE: formatServiceNameNice('parent-conversation');
     * WHAT IT DOES INTERNALLY: Replaces hyphens/underscores with spaces and capitalizes first letter of words.
     */
    function formatServiceNameNice(str) {
        if (!str) return 'Parent Conversation';
        let clean = str.replace(/[-_]+/g, ' ').trim();
        return clean.replace(/\b\w/g, l => l.toUpperCase());
    }
    const activeServiceTitle = formatServiceNameNice(rawServiceName);
    localStorage.setItem('cosy_selected_service_name', activeServiceTitle);

    const currencySymbol = (window.cosyCheckout && window.cosyCheckout.currencySymbol) || '£';
    const customerName = (window.cosyCheckout && window.cosyCheckout.customerName) || 'Valued Customer';
    const customerEmail = (window.cosyCheckout && window.cosyCheckout.customerEmail) || '';

    let selectedSlotsByDay = {};
    let currentModalDateStr = '';

    /**
     * NORMALIZE TIME STR
     * 
     * USE CASE: Standardizes time format strings to "hh:mm AM/PM" for slot comparison.
     * HOW TO USE: normalizeTimeStr('09:00'); // Returns "09:00 AM"
     * WHAT IT DOES INTERNALLY: Converts 24-hour military time to 12-hour AM/PM format.
     */
    function normalizeTimeStr(tStr) {
        if (!tStr) return '';
        tStr = String(tStr).trim().toUpperCase();
        if (/^\d{1,2}:\d{2}$/.test(tStr)) {
            const parts = tStr.split(':');
            let h = parseInt(parts[0]);
            const m = parts[1];
            const ap = h >= 12 ? 'PM' : 'AM';
            h = h % 12;
            h = h ? h : 12;
            return `${String(h).padStart(2, '0')}:${m} ${ap}`;
        }
        if (/^\d{1}:\d{2}\s*(AM|PM)$/i.test(tStr)) {
            return '0' + tStr;
        }
        return tStr;
    }

    // Restore saved slots from localStorage if available
    try {
        const storedPending = JSON.parse(localStorage.getItem('cosy_pending_booking') || '{}');
        if (storedPending && storedPending.slots) {
            if (typeof storedPending.slots === 'object' && !Array.isArray(storedPending.slots)) {
                for (const dKey in storedPending.slots) {
                    selectedSlotsByDay[dKey] = (storedPending.slots[dKey] || []).map(normalizeTimeStr);
                }
            } else if (Array.isArray(storedPending.slots)) {
                storedPending.slots.forEach(slot => {
                    if (slot && slot.date && slot.time) {
                        const dateKey = new Date(slot.date).toDateString();
                        const normT = normalizeTimeStr(slot.time);
                        if (!selectedSlotsByDay[dateKey]) {
                            selectedSlotsByDay[dateKey] = [];
                        }
                        if (!selectedSlotsByDay[dateKey].includes(normT)) {
                            selectedSlotsByDay[dateKey].push(normT);
                        }
                    }
                });
            }
        }
    } catch (err) {
        console.warn('Could not parse pending booking slots:', err);
    }

    // If step is schedule or start_date is present, render Call Schedule Screen first
    if (currentStep === 'schedule' || startDateParam) {
        renderCallScheduleScreen();
    } else {
        renderSummaryScreen();
    }

    function parseStartDate(dateStr) {
        if (!dateStr) return new Date();
        if (/^\d{2}-\d{2}-\d{4}$/.test(dateStr)) {
            const parts = dateStr.split('-');
            return new Date(parseInt(parts[2]), parseInt(parts[1]) - 1, parseInt(parts[0]));
        }
        return new Date(dateStr);
    }

    /**
     * RENDER STEP 2: CALL SCHEDULE SCREEN
     */
    function renderCallScheduleScreen() {
        window.scrollTo(0, 0);

        const baseDate = parseStartDate(startDateParam);
        const formattedDate = startDateParam ? baseDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : 'Select Date';

        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        let slotsRowsHtml = '';
        let addedCount = 0;
        let dayOffset = 0;

        while (dayOffset < 7) {
            const nextDate = new Date(baseDate);
            nextDate.setDate(baseDate.getDate() + dayOffset);
            const dateStr = nextDate.toDateString();
            const dayName = dayNames[nextDate.getDay()];
            dayOffset++;

            const cellYear = nextDate.getFullYear();
            const cellMonth = String(nextDate.getMonth() + 1).padStart(2, '0');
            const cellDayStr = String(nextDate.getDate()).padStart(2, '0');
            const dateISO = `${cellYear}-${cellMonth}-${cellDayStr}`;

            const isHoliday = window.providerHolidays && window.providerHolidays.includes(dateISO);
            let isDayOff = false;
            if (window.providerAvailability && typeof window.providerAvailability === 'object') {
                const dayConfig = window.providerAvailability[dayName];
                if (!dayConfig || (!dayConfig.start_time && !dayConfig.end_time)) {
                    isDayOff = true;
                }
            }

            // Skip provider's off-days completely so only active working days are rendered
            if (isDayOff) {
                continue;
            }

            const safeIdKey = dateStr.replace(/[^a-zA-Z0-9]/g, '-');
            const formattedDayDate = nextDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            const selectedDuration = selectedSlotsByDay[dateStr] ? selectedSlotsByDay[dateStr].length * 10 : 0;

            if (isHoliday) {
                slotsRowsHtml += `
                    <div class="d-flex align-items-center justify-content-between p-3 mb-3 rounded-4 border bg-light opacity-75 shadow-sm" style="border-color: #f1f5f9 !important;">
                        <div class="text-start">
                            <h6 class="fw-bold mb-1 text-muted" style="font-size: 0.95rem;">${dayName} (${formattedDayDate})</h6>
                            <p class="small text-danger mb-0">🚫 Holiday / Unavailable</p>
                        </div>
                        <button type="button" disabled class="btn btn-sm px-3 py-2 fw-semibold text-muted bg-white border" style="border-radius: 12px; font-size: 0.82rem; cursor: not-allowed;">
                            Unavailable
                        </button>
                    </div>
                `;
            } else {
                slotsRowsHtml += `
                    <div class="d-flex align-items-center justify-content-between p-3 mb-3 rounded-4 border bg-white shadow-sm" style="border-color: #f1f5f9 !important;">
                        <div class="text-start">
                            <h6 class="fw-bold mb-1" style="color: #1e293b; font-size: 0.95rem;">${dayName} (${formattedDayDate})</h6>
                            <p class="small text-muted mb-0" id="duration-${safeIdKey}">${selectedDuration} minutes Call Duration</p>
                        </div>
                        <button type="button" id="btn-time-${safeIdKey}" class="btn btn-sm px-3 py-2 fw-bold text-white shadow-sm btn-open-time-modal" data-date="${dateStr}" style="background: #a44390; border-radius: 12px; font-size: 0.82rem;">
                            ${selectedDuration > 0 ? 'Edit Time' : 'Select Time'}
                        </button>
                    </div>
                `;
            }
            addedCount++;
        }

        container.innerHTML = `
            <div class="cosy-checkout-header d-flex align-items-center justify-content-between mb-4">
                <button id="cosyCheckoutBackBtn" class="cosy-checkout-back-btn btn border-0 fw-bold px-0 py-2 d-inline-flex align-items-center gap-2" style="background: transparent !important; color: #a44390; box-shadow: none; border-radius: 0; font-size: 0.95rem; line-height: 1;">
                    <i class="fas fa-arrow-left" style="color: #a44390 !important; font-size: 0.95rem;"></i> <span>Back to Profile</span>
                </button>
                <h2 class="cosy-checkout-title h4 fw-bold mb-0 d-inline-flex align-items-center gap-2" style="color: #a44390; font-size: 1.25rem;">
                    <i class="fas fa-calendar-check" style="color: #a44390;"></i> <span>Call Schedule</span>
                </h2>
            </div>

            <!-- Header Card -->
            <div class="cosy-card-rounded card border-0 shadow-sm mb-4 p-4" style="border-radius: 20px; background: #ffffff;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pb-3 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="cosy-icon-box" style="width: 42px; height: 42px; background: #fdf5fc; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #a44390;">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0" style="color: #1e293b;">${providerNameParam}</h6>
                            <small class="text-muted">Start Date: <span class="fw-bold text-dark">${formattedDate}</span></small>
                        </div>
                    </div>
                </div>
                
                <div class="pt-4">
                    <p class="small text-muted fw-bold text-uppercase mb-3" style="letter-spacing: 0.5px;">Choose Your Time Slots:</p>
                    <div id="callScheduleSlotsContainer">
                        ${slotsRowsHtml}
                    </div>
                </div>
            </div>

            <!-- Booking Options & Calculation Panel (Hidden until at least 1 time slot is selected) -->
            <div class="cosy-card-rounded card border-0 shadow-sm mb-4 p-4" id="bookingCalculationPanel" style="display: none; border-radius: 20px; background: #ffffff;">
                
                <!-- Book for Another Person Option -->
                <div class="mb-4 pb-3 border-bottom">
                    <div class="form-check d-flex align-items-center gap-2">
                        <input class="form-check-input" type="checkbox" id="chkBookAnother" style="width: 18px; height: 18px; cursor: pointer; accent-color: #a44390;">
                        <label class="form-check-label fw-bold small text-dark cursor-pointer mb-0" for="chkBookAnother" style="cursor: pointer;">
                            Book for another person (Gift a Conversation 🎁)
                        </label>
                    </div>

                    <div id="anotherPersonFields" class="mt-3 p-3 rounded-4" style="display: none; background: #fdf5fc; border: 1px solid #fbcfe8;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted mb-1">Recipient Name</label>
                                <input type="text" id="recipientName" class="form-control form-control-sm" placeholder="e.g. Sarah Smith" style="border-radius: 8px;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted mb-1">Recipient Email</label>
                                <input type="email" id="recipientEmail" class="form-control form-control-sm" placeholder="e.g. sarah@example.com" style="border-radius: 8px;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recurring Duration Dropdown -->
                <div class="mb-4">
                    <label class="form-label small text-muted fw-bold text-uppercase mb-2">Select Booking Duration</label>
                    <select id="selDurationWeeks" class="form-select fw-bold py-2 px-3" style="border-radius: 12px; border-color: #e2e8f0; font-size: 0.9rem;">
                        <option value="1">1 Week Duration</option>
                        <option value="2">2 Weeks Recurring</option>
                        <option value="3">3 Weeks Recurring</option>
                        <option value="4">4 Weeks (1 Month)</option>
                        <option value="8">8 Weeks (2 Months)</option>
                        <option value="12">12 Weeks (Quarterly)</option>
                    </select>
                </div>

                <!-- Live Price Calculation -->
                <div class="p-3 rounded-4 mb-4 text-center" style="background: #faf5ff; border: 1.5px solid #e9d5ff;">
                    <small class="text-muted d-block fw-bold text-uppercase mb-1" style="font-size: 0.75rem;">Total Estimated Price</small>
                    <h3 class="fw-bold mb-0" id="txtLiveTotalAmount" style="color: #a44390;">${currencySymbol} 0.00</h3>
                    <small class="text-muted d-block" style="font-size: 0.78rem;">Calculated at 10-minute slot increments</small>
                    <div id="txtLiveTotalNote" class="mt-2 pt-2 border-top small text-muted" style="font-size: 0.76rem; color: #64748b; display: none;"></div>
                </div>

                <!-- Action Button -->
                <button type="button" id="btnProceedToSummary" class="btn w-100 py-3 fw-bold text-white shadow-sm" style="background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); border-radius: 14px; font-size: 1rem; transition: all 0.2s;">
                    Book Service Now <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </div>
        `;

        // Restore saved settings and show calculation panel if slots are present
        calculateLiveTotal();
        try {
            const savedPending = JSON.parse(localStorage.getItem('cosy_pending_booking') || '{}');
            if (savedPending && savedPending.numberOfWeeks) {
                $('#selDurationWeeks').val(savedPending.numberOfWeeks);
                calculateLiveTotal();
            }
            if (savedPending && savedPending.isGift) {
                $('#chkBookAnother').prop('checked', true);
                $('#anotherPersonFields').show();
                $('#recipientName').val(savedPending.recipientName || '');
                $('#recipientEmail').val(savedPending.recipientEmail || '');
            }
        } catch (e) { }
    }

    /**
     * RENDER STEP 3: BOOKING SUMMARY & PAYMENT
     */
    function renderSummaryScreen() {
        window.scrollTo(0, 0);
        const pendingBookingData = localStorage.getItem('cosy_pending_booking');
        if (!pendingBookingData) {
            container.innerHTML = `
                <div class="cosy-checkout-empty-state shadow-sm text-center py-5">
                    <i class="fas fa-calendar-times mb-3" style="font-size: 3rem; color: #a44390;"></i>
                    <h3 class="fw-bold">No Active Booking Session</h3>
                    <p class="text-muted">Please select a provider and booking slots first.</p>
                    <a href="${(window.cosyCheckout && window.cosyCheckout.providerUrl) || '/'}" class="btn text-white fw-bold px-4 py-2" style="background: #a44390; border-radius: 12px;">
                        <i class="fas fa-arrow-left me-1"></i> Browse Parents
                    </a>
                </div>
            `;
            return;
        }

        const booking = JSON.parse(pendingBookingData);

        function formatDateNice(dInput) {
            if (!dInput) return '';
            try {
                let dObj;
                if (/^\d{2}-\d{2}-\d{4}$/.test(dInput)) {
                    const parts = dInput.split('-');
                    dObj = new Date(parseInt(parts[2]), parseInt(parts[1]) - 1, parseInt(parts[0]));
                } else {
                    dObj = new Date(dInput);
                }
                if (!isNaN(dObj.getTime())) {
                    return dObj.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                }
            } catch (e) { }
            return dInput;
        }

        const displayStartDate = formatDateNice(booking.startDate);

        let displayEndDate = formatDateNice(booking.endDate);
        if (!displayEndDate && booking.startDate) {
            try {
                let sObj;
                if (/^\d{2}-\d{2}-\d{4}$/.test(booking.startDate)) {
                    const parts = booking.startDate.split('-');
                    sObj = new Date(parseInt(parts[2]), parseInt(parts[1]) - 1, parseInt(parts[0]));
                } else {
                    sObj = new Date(booking.startDate);
                }
                if (!isNaN(sObj.getTime())) {
                    const weeksNum = parseInt(booking.numberOfWeeks) || 1;
                    sObj.setDate(sObj.getDate() + (weeksNum * 7) - 1);
                    displayEndDate = sObj.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                }
            } catch (e) { }
        }

        // Compute week days string
        let computedWeekDays = booking.weekDays || '';
        if (!computedWeekDays && booking.slots && typeof booking.slots === 'object') {
            const daysList = [];
            Object.keys(booking.slots).forEach(dKey => {
                if (booking.slots[dKey] && booking.slots[dKey].length > 0) {
                    const dayName = new Date(dKey).toLocaleDateString('en-US', { weekday: 'long' });
                    if (!daysList.includes(dayName)) daysList.push(dayName);
                }
            });
            computedWeekDays = daysList.join(', ');
        }
        if (!computedWeekDays) computedWeekDays = 'Friday';

        // Compute selected slots timeline (fallback showing ONLY slot times)
        let computedSelectedSlots = '';
        if (booking.slots && typeof booking.slots === 'object') {
            const allTimes = [];
            Object.values(booking.slots).forEach(timesArr => {
                if (Array.isArray(timesArr)) {
                    timesArr.forEach(t => {
                        const rawT = (typeof t === 'object' && t !== null) ? (t.time || t.slot || '') : t;
                        const normT = normalizeTimeStr(rawT);
                        if (normT && !allTimes.includes(normT)) allTimes.push(normT);
                    });
                }
            });
            computedSelectedSlots = allTimes.join(', ');
        }
        if (!computedSelectedSlots && booking.slotsTimeline) {
            computedSelectedSlots = booking.slotsTimeline.replace(/^(?:[0-9]{1,2}\s+[A-Za-z]+\s+[0-9]{4}|[0-9]{2}-[0-9]{2}-[0-9]{4}):\s*/, '');
        }
        if (!computedSelectedSlots) computedSelectedSlots = '09:00 AM';

        // Compute Booking Days breakdown (e.g., "Mon 08:00 AM, 08:10 AM <br> Wed 09:00 AM")
        let formattedBookingDaysBreakdown = '';
        if (booking.slots) {
            const dayMap = {};
            if (Array.isArray(booking.slots) && booking.slots.length > 0) {
                booking.slots.forEach(s => {
                    let dayName = '';
                    let rawTime = '';
                    if (typeof s === 'object' && s !== null) {
                        if (s.date) {
                            const d = new Date(s.date);
                            if (!isNaN(d.getTime())) {
                                dayName = d.toLocaleDateString('en-US', { weekday: 'short' });
                            }
                        }
                        rawTime = s.time || s.slot || '';
                    } else if (typeof s === 'string') {
                        rawTime = s;
                    }
                    if (!dayName && booking.startDate) {
                        const d = new Date(booking.startDate);
                        if (!isNaN(d.getTime())) {
                            dayName = d.toLocaleDateString('en-US', { weekday: 'short' });
                        }
                    }
                    if (!dayName) dayName = 'Booked Day';
                    const normT = normalizeTimeStr(rawTime);
                    if (!dayMap[dayName]) dayMap[dayName] = [];
                    if (!dayMap[dayName].includes(normT)) dayMap[dayName].push(normT);
                });
            } else if (typeof booking.slots === 'object') {
                Object.keys(booking.slots).forEach(dKey => {
                    const times = booking.slots[dKey];
                    if (Array.isArray(times) && times.length > 0) {
                        let dayName = dKey;
                        const d = new Date(dKey);
                        if (!isNaN(d.getTime())) {
                            dayName = d.toLocaleDateString('en-US', { weekday: 'short' });
                        }
                        if (!dayMap[dayName]) dayMap[dayName] = [];
                        times.forEach(t => {
                            const normT = normalizeTimeStr(t);
                            if (!dayMap[dayName].includes(normT)) dayMap[dayName].push(normT);
                        });
                    }
                });
            }
            const dayKeys = Object.keys(dayMap);
            if (dayKeys.length > 0) {
                formattedBookingDaysBreakdown = dayKeys.map(d => `<span class="fw-bold" style="color: #7a2267;">${d}</span> ${dayMap[d].join(', ')}`).join('<br>');
            }
        }
        if (!formattedBookingDaysBreakdown) {
            const fallbackDay = booking.weekDays ? booking.weekDays.split(',')[0].trim().substring(0, 3) : 'Mon';
            formattedBookingDaysBreakdown = `<span class="fw-bold" style="color: #7a2267;">${fallbackDay}</span> ${computedSelectedSlots}`;
        }

        container.innerHTML = `
            <div class="cosy-checkout-header mb-4 d-flex align-items-center justify-content-between">
                <button id="btnBackToSchedule" class="btn border-0 fw-bold px-0 py-2 d-inline-flex align-items-center gap-2" style="background: transparent !important; color: #a44390; box-shadow: none; border-radius: 0; font-size: 0.95rem; line-height: 1;">
                    <i class="fas fa-arrow-left" style="color: #a44390 !important; font-size: 0.95rem;"></i> <span>Back to Schedule</span>
                </button>
                <h1 class="cosy-checkout-title h4 fw-bold mb-0 d-inline-flex align-items-center gap-2" style="color: #a44390; font-size: 1.25rem;">
                    <i class="fas fa-file-alt" style="color: #a44390;"></i> <span>Booking Summary</span>
                </h1>
            </div>

            <!-- Bento Card 1: Service Information -->
            <div class="cosy-bento-panel mb-4 rounded-4 shadow-sm overflow-hidden" style="background: #fff; border: 1px solid #f3e8fc;">
                <div class="py-3 px-4" style="background: #fdf5fc; border-bottom: 1px solid #f9e8f6;">
                    <span style="background: #a44390; width: 5px; height: 18px; border-radius: 3px; display: inline-block; margin-right: 10px; vertical-align: middle;"></span>
                    <h6 class="d-inline-block fw-bold mb-0" style="color: #7a2267; font-size: 1.05rem;">Service Information :</h6>
                </div>
                <div class="p-0">
                    <table class="cosy-checkout-table w-100 mb-0" style="border-collapse: collapse;">
                        <tbody>
                            <tr style="background: #ffffff;">
                                <td class="ps-4 pe-2 py-3 fw-semibold text-secondary" style="width: 32%; font-size: 0.92rem;">Provider</td>
                                <td class="pe-2 py-3 text-center" style="width: 5%; color: #c084fc; font-size: 0.92rem;">:</td>
                                <td class="pe-4 py-3 fw-bold text-dark" style="font-size: 0.95rem;">${booking.providerName}</td>
                            </tr>
                            <tr style="background: #fdfafc;">
                                <td class="ps-4 pe-2 py-3 fw-semibold text-secondary" style="width: 32%; font-size: 0.92rem;">Start Date</td>
                                <td class="pe-2 py-3 text-center" style="width: 5%; color: #c084fc; font-size: 0.92rem;">:</td>
                                <td class="pe-4 py-3 fw-bold text-dark" style="font-size: 0.95rem;">${displayStartDate}</td>
                            </tr>
                            <tr style="background: #ffffff;">
                                <td class="ps-4 pe-2 py-3 fw-semibold text-secondary" style="width: 32%; font-size: 0.92rem;">Booking Duration</td>
                                <td class="pe-2 py-3 text-center" style="width: 5%; color: #c084fc; font-size: 0.92rem;">:</td>
                                <td class="pe-4 py-3 fw-bold text-dark" style="font-size: 0.95rem;">${booking.weeklyBooking || '1 Week Duration'}</td>
                            </tr>
                            <tr style="background: #fdfafc;">
                                <td class="ps-4 pe-2 py-3 fw-semibold text-secondary" style="width: 32%; font-size: 0.92rem; vertical-align: top;">Booking Days</td>
                                <td class="pe-2 py-3 text-center" style="width: 5%; color: #c084fc; font-size: 0.92rem; vertical-align: top;">:</td>
                                <td class="pe-4 py-3 fw-bold text-dark" style="font-size: 0.95rem; line-height: 1.6;">${formattedBookingDaysBreakdown}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bento Card 2: Costing Information -->
            <div class="cosy-bento-panel mb-4 rounded-4 shadow-sm overflow-hidden" style="background: #fff; border: 1px solid #f3e8fc;">
                <div class="py-3 px-4" style="background: #fdf5fc; border-bottom: 1px solid #f9e8f6;">
                    <span style="background: #a44390; width: 5px; height: 18px; border-radius: 3px; display: inline-block; margin-right: 10px; vertical-align: middle;"></span>
                    <h6 class="d-inline-block fw-bold mb-0" style="color: #7a2267; font-size: 1.05rem;">Costing Information :</h6>
                </div>
                <div class="p-0">
                    <table class="cosy-checkout-table w-100 mb-0" style="border-collapse: collapse;">
                        <tbody>
                            <tr style="background: #ffffff;">
                                <td class="ps-4 pe-2 py-3 fw-semibold text-secondary" style="width: 32%; font-size: 0.92rem;">Service Cost</td>
                                <td class="pe-2 py-3 text-center" style="width: 5%; color: #c084fc; font-size: 0.92rem;">:</td>
                                <td class="pe-4 py-3 fw-bold text-dark" style="font-size: 0.95rem;">${currencySymbol} ${booking.serviceCost}</td>
                            </tr>
                            <tr style="background: #fdfafc;">
                                <td class="ps-4 pe-2 py-3 fw-semibold text-secondary" style="width: 32%; font-size: 0.92rem;">Service Fee *</td>
                                <td class="pe-2 py-3 text-center" style="width: 5%; color: #c084fc; font-size: 0.92rem;">:</td>
                                <td class="pe-4 py-3 fw-bold text-dark" style="font-size: 0.95rem;">${currencySymbol} ${booking.serviceFee}</td>
                            </tr>
                            <tr style="background: #ffffff; border-top: 1.5px dashed #f3e8fc;">
                                <td class="ps-4 pe-2 py-3 fw-bold text-dark" style="width: 32%; font-size: 1rem;">Total Payable Amount</td>
                                <td class="pe-2 py-3 text-center" style="width: 5%; color: #a44390; font-size: 1rem; font-weight: 700;">:</td>
                                <td class="pe-4 py-3 fw-bold h5 mb-0" style="color: #a44390 !important;">${currencySymbol} ${booking.totalPayable}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="px-4 py-3" style="background: #faf5fa; border-top: 1px solid #f5e6f3;">
                        <p class="small text-muted mb-0" style="font-size: 0.78rem; line-height: 1.4;">
                            * Service Charge – helps us provide and continually improve the CosyChats platform, including secure bookings, payment processing and customer support.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Pay Now Button -->
            <?php
            // Dynamic gateway label
            ?>
            <button id="cosyPayNowBtn" class="btn w-100 py-3 fw-bold text-white shadow-sm" style="background: #a44390; border-radius: 14px; font-size: 1.1rem;">
                Pay Now via WorldPay <i class="fas fa-lock ms-2"></i>
            </button>
        `;
    }

    // ================= EVENT DELEGATION HANDLERS ================= //

    // Toggle "Book for another person" fields
    $(document).on('change', '#chkBookAnother', function () {
        if (this.checked) {
            $('#anotherPersonFields').slideDown(200);
        } else {
            $('#anotherPersonFields').slideUp(200);
        }
    });

    function getDynamic10MinSlots(dateStr) {
        const dateObj = new Date(dateStr);
        const dayNamesMap = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const dayNameStr = dayNamesMap[dateObj.getDay()];

        if (!window.providerAvailability || typeof window.providerAvailability !== 'object') {
            return ['09:00 AM', '09:10 AM', '09:20 AM', '09:30 AM', '09:40 AM', '09:50 AM', '10:00 AM', '10:10 AM', '10:20 AM', '10:30 AM', '11:00 AM', '11:10 AM', '02:00 PM', '02:10 PM', '02:20 PM', '03:00 PM'];
        }

        const dayConfig = window.providerAvailability[dayNameStr];
        if (!dayConfig || !dayConfig.start_time || !dayConfig.end_time) {
            return [];
        }

        const parseMinutes = (tStr) => {
            if (!tStr) return -1;
            const parts = tStr.split(':');
            if (parts.length < 2) return -1;
            return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
        };

        const format12Hour = (totalMins) => {
            const normalizedMins = totalMins % (24 * 60);
            const h24 = Math.floor(normalizedMins / 60);
            const m = normalizedMins % 60;
            const ampm = h24 >= 12 ? 'PM' : 'AM';
            const h12 = h24 % 12 || 12;
            const mStr = String(m).padStart(2, '0');
            const hStr = String(h12).padStart(2, '0');
            return `${hStr}:${mStr} ${ampm}`;
        };

        const startMins = parseMinutes(dayConfig.start_time);
        let endMins = parseMinutes(dayConfig.end_time);
        let breakStartMins = parseMinutes(dayConfig.break_start_time || dayConfig.break_start);
        let breakEndMins = parseMinutes(dayConfig.break_end_time || dayConfig.break_end);

        if (startMins < 0 || endMins < 0) {
            return [];
        }

        // Support overnight shifts (e.g. 10:00 to 06:00 next day)
        if (endMins <= startMins) {
            endMins += 24 * 60;
        }

        if (breakStartMins >= 0 && breakStartMins < startMins) {
            breakStartMins += 24 * 60;
        }
        if (breakEndMins >= 0 && breakEndMins <= breakStartMins) {
            breakEndMins += 24 * 60;
        }

        const slots = [];
        let maxSlotsGuard = 300; // Safeguard against infinite loops (max 300 10-minute slots per day)
        for (let current = startMins; current < endMins && maxSlotsGuard > 0; current += 10) {
            maxSlotsGuard--;
            if (breakStartMins >= 0 && breakEndMins >= 0 && current >= breakStartMins && current < breakEndMins) {
                continue;
            }
            slots.push(format12Hour(current));
        }

        return slots;
    }

    let isDurationValidating = false;

    // Open Time Slot Modal for specific day
    $(document).on('click', '.btn-open-time-modal', function (e) {
        e.preventDefault();
        currentModalDateStr = $(this).data('date');

        const modal = new bootstrap.Modal(document.getElementById('timeSlotModal'));
        const grid = document.getElementById('timeGrid');

        grid.innerHTML = `
            <div class="text-center py-4 w-100" style="grid-column: 1 / -1;">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="small text-muted mt-2">Loading available 10-minute slots...</p>
            </div>
        `;
        modal.show();

        const ajaxUrl = (window.cosyCheckout && window.cosyCheckout.ajaxUrl) || window.ajaxurl || '/wp-admin/admin-ajax.php';
        const targetProviderId = providerIdParam || (window.cosyCheckout && window.cosyCheckout.providerId) || 0;

        // Fetch already booked slots for this provider & date via AJAX
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'cosy_get_booked_slots',
                provider_id: targetProviderId,
                date: currentModalDateStr,
                num_weeks: parseInt($('#selDurationWeeks').val()) || 1
            },
            success: function (res) {
                let bookedSlots = [];
                if (res.success && Array.isArray(res.data)) {
                    bookedSlots = res.data.map(normalizeTimeStr);
                }

                let slotsHtml = '';
                const times = getDynamic10MinSlots(currentModalDateStr);

                if (!times || times.length === 0) {
                    grid.innerHTML = `
                        <div class="text-center py-4 w-100" style="grid-column: 1 / -1;">
                            <p class="small text-muted mb-0">No working time slots available for this day.</p>
                        </div>
                    `;
                    return;
                }

                const activeSlots = (selectedSlotsByDay[currentModalDateStr] || []).map(normalizeTimeStr);

                times.forEach((t) => {
                    const normT = normalizeTimeStr(t);
                    const isSelected = activeSlots.includes(normT);
                    const isBooked = bookedSlots.includes(normT);

                    let bg = '#ffffff';
                    let color = '#1e293b';
                    let border = '1px solid #e2e8f0';
                    let btnClass = 'select-slot-btn';
                    let cursor = 'pointer';
                    let titleAttr = '';

                    if (isBooked) {
                        bg = '#e2e8f0';
                        color = '#94a3b8';
                        border = '1px solid #cbd5e1';
                        btnClass = 'booked-slot-btn';
                        cursor = 'not-allowed';
                        titleAttr = 'title="Already Booked"';
                    } else if (isSelected) {
                        bg = '#a44390';
                        color = '#ffffff';
                        border = '1.5px solid #a44390';
                    }

                    slotsHtml += `
                        <div class="time-block-item text-center p-2 rounded-3 ${btnClass}" data-time="${t}" ${titleAttr} style="background: ${bg}; color: ${color}; border: ${border}; font-weight: 600; font-size: 0.8rem; cursor: ${cursor}; transition: all 0.2s;">
                            ${t}
                        </div>
                    `;
                });
                grid.innerHTML = slotsHtml;
                $('#modalTotalDuration').text((activeSlots.length * 10) + ' minutes');
            },
            error: function () {
                let slotsHtml = '';
                const times = getDynamic10MinSlots(currentModalDateStr);
                const activeSlots = (selectedSlotsByDay[currentModalDateStr] || []).map(normalizeTimeStr);

                times.forEach((t) => {
                    const normT = normalizeTimeStr(t);
                    const isSelected = activeSlots.includes(normT);
                    const bg = isSelected ? '#a44390' : '#ffffff';
                    const color = isSelected ? '#ffffff' : '#1e293b';
                    const border = isSelected ? '1.5px solid #a44390' : '1px solid #e2e8f0';

                    slotsHtml += `
                        <div class="time-block-item text-center p-2 rounded-3 select-slot-btn" data-time="${t}" style="background: ${bg}; color: ${color}; border: ${border}; font-weight: 600; font-size: 0.8rem; cursor: pointer; transition: all 0.2s;">
                            ${t}
                        </div>
                    `;
                });
                grid.innerHTML = slotsHtml;
                $('#modalTotalDuration').text((activeSlots.length * 10) + ' minutes');
            }
        });
    });

    // Select/Deselect time slots inside modal
    $(document).on('click', '.select-slot-btn', function () {
        const rawTimeVal = $(this).data('time');
        const timeVal = normalizeTimeStr(rawTimeVal);

        if (!selectedSlotsByDay[currentModalDateStr]) {
            selectedSlotsByDay[currentModalDateStr] = [];
        }

        selectedSlotsByDay[currentModalDateStr] = selectedSlotsByDay[currentModalDateStr].map(normalizeTimeStr);

        const index = selectedSlotsByDay[currentModalDateStr].indexOf(timeVal);
        if (index > -1) {
            selectedSlotsByDay[currentModalDateStr].splice(index, 1);
            $(this).css({ background: '#ffffff', color: '#1e293b', border: '1px solid #e2e8f0' });
        } else {
            selectedSlotsByDay[currentModalDateStr].push(timeVal);
            $(this).css({ background: '#a44390', color: '#ffffff', border: '1.5px solid #a44390' });
        }

        calculateLiveTotal();
        const safeIdKey = currentModalDateStr.replace(/[^a-zA-Z0-9]/g, '-');
        const durationTextEl = document.getElementById(`duration-${safeIdKey}`);
        if (durationTextEl) {
            const duration = selectedSlotsByDay[currentModalDateStr].length * 10;
            durationTextEl.textContent = `${duration} minutes Call Duration`;
        }
        $('#modalTotalDuration').text((selectedSlotsByDay[currentModalDateStr].length * 10) + ' minutes');
    });

    // Confirm Time Slots in Modal
    $(document).on('click', '#btnConfirmTimeSlotsModal', function () {
        const modalEl = document.getElementById('timeSlotModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        const safeIdKey = currentModalDateStr.replace(/[^a-zA-Z0-9]/g, '-');
        const count = selectedSlotsByDay[currentModalDateStr] ? selectedSlotsByDay[currentModalDateStr].length : 0;

        // Update Duration label on Call Schedule row
        $('#duration-' + safeIdKey).text((count * 10) + ' minutes Call Duration');

        // Toggle button text between 'Edit Time' and 'Select Time'
        if (count > 0) {
            $('#btn-time-' + safeIdKey).text('Edit Time');
        } else {
            $('#btn-time-' + safeIdKey).text('Select Time');
        }

        calculateLiveTotal();
    });

    function getUnitPrice() {
        const existingPending = JSON.parse(localStorage.getItem('cosy_pending_booking') || '{}');
        let rawRate = 0;
        if (existingPending.unitPrice && parseFloat(existingPending.unitPrice) > 0) {
            rawRate = parseFloat(existingPending.unitPrice);
        } else if (existingPending.servicePrice && parseFloat(existingPending.servicePrice) > 0) {
            rawRate = parseFloat(existingPending.servicePrice);
        } else if (window.cosyDefaultService && window.cosyDefaultService.price) {
            rawRate = parseFloat(window.cosyDefaultService.price);
        } else {
            const savedPrice = localStorage.getItem('cosy_selected_service_price');
            if (savedPrice && parseFloat(savedPrice) > 0) {
                rawRate = parseFloat(savedPrice);
            }
        }
        // Configured rate is provider's Hourly Rate (£ ph). Each slot is 10 minutes (1/6 hour).
        // Dynamic Slot Unit Price = Provider Hourly Rate / 6
        return rawRate / 6.0;
    }

    function calculateTotalActiveSlotsAcrossWeeks(weeks) {
        let totalActiveSlots = 0;
        Object.keys(selectedSlotsByDay).forEach(dateStr => {
            const slotsCount = selectedSlotsByDay[dateStr] ? selectedSlotsByDay[dateStr].length : 0;
            if (slotsCount === 0) return;

            const baseSlotDate = new Date(dateStr);
            for (let w = 0; w < weeks; w++) {
                const checkDate = new Date(baseSlotDate);
                checkDate.setDate(baseSlotDate.getDate() + (w * 7));

                const cYear = checkDate.getFullYear();
                const cMonth = String(checkDate.getMonth() + 1).padStart(2, '0');
                const cDayStr = String(checkDate.getDate()).padStart(2, '0');
                const dateISO = `${cYear}-${cMonth}-${cDayStr}`;
                const dayNamesMap = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                const dayNameStr = dayNamesMap[checkDate.getDay()];

                const isHoliday = window.providerHolidays && window.providerHolidays.includes(dateISO);
                let isDayOff = false;
                if (window.providerAvailability && typeof window.providerAvailability === 'object') {
                    const dayConfig = window.providerAvailability[dayNameStr];
                    if (!dayConfig || (!dayConfig.start_time && !dayConfig.end_time)) {
                        isDayOff = true;
                    }
                }

                if (!isHoliday && !isDayOff) {
                    totalActiveSlots += slotsCount;
                }
            }
        });
        return totalActiveSlots;
    }

    // Recalculate Live Price Formula: Total Active Non-Holiday Slots Across Weeks * Unit Price
    function calculateLiveTotal() {
        let totalSlots1Week = 0;
        Object.values(selectedSlotsByDay).forEach(slots => {
            totalSlots1Week += slots.length;
        });

        const panel = $('#bookingCalculationPanel');
        if (totalSlots1Week > 0) {
            if (panel.is(':hidden')) {
                panel.slideDown(300);
            }
        } else {
            panel.slideUp(300);
        }

        const unitPrice = getUnitPrice();
        const weeks = parseInt($('#selDurationWeeks').val()) || 1;
        const totalActiveSlots = calculateTotalActiveSlotsAcrossWeeks(weeks);
        const totalCost = totalActiveSlots * unitPrice;

        $('#txtLiveTotalAmount').text(`${currencySymbol} ${totalCost.toFixed(2)}`);

        const $note = $('#txtLiveTotalNote');
        const maxPossibleSlots = totalSlots1Week * weeks;
        const hasHolidayExclusion = (weeks > 1 && totalActiveSlots < maxPossibleSlots);

        if ($note.length) {
            if (weeks > 1 && hasHolidayExclusion) {
                $note.html('<i class="fas fa-info-circle me-1" style="color: #a44390;"></i> Some of your selected booking dates fall during the parent\'s holiday. Those sessions will be skipped and won\'t be charged.').slideDown(200);
            } else if (weeks > 1) {
                $note.html('<i class="fas fa-info-circle me-1" style="color: #a44390;"></i> Note: Price is calculated ONLY for active available sessions.').slideDown(200);
            } else {
                $note.slideUp(200);
            }
        }
    }

    $(document).on('change', '#selDurationWeeks', function () {
        calculateLiveTotal();
        if (typeof currentModalDateStr !== 'undefined' && currentModalDateStr) {
            renderSlotsForDate(currentModalDateStr);
        }

        // Instant validation check when duration changes if slots are already selected
        let hasSlots = false;
        Object.values(selectedSlotsByDay).forEach(slots => {
            if (slots && slots.length > 0) hasSlots = true;
        });

        if (hasSlots && startDateParam) {
            const weeks = parseInt($('#selDurationWeeks').val()) || 1;
            const ajaxUrl = (window.cosyCheckout && window.cosyCheckout.ajaxUrl) || window.ajaxurl || '/wp-admin/admin-ajax.php';
            const existingPending = JSON.parse(localStorage.getItem('cosy_pending_booking') || '{}');
            const targetProviderId = existingPending.providerId || providerIdParam || (window.cosyCheckout && window.cosyCheckout.providerId) || 0;

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cosy_validate_booking_slots',
                    provider_id: targetProviderId,
                    start_date: startDateParam,
                    num_weeks: weeks,
                    slots: JSON.stringify(selectedSlotsByDay)
                },
                success: function (res) {
                    if (res && res.success === false && res.data && res.data.message) {
                        if (typeof CosyAlert !== 'undefined') {
                            CosyAlert.error('Slot Unavailable', res.data.message);
                        } else {
                            alert(res.data.message);
                        }
                    }
                }
            });
        }
    });

    // Proceed from Call Schedule to Booking Summary
    $(document).on('click', '#btnProceedToSummary', function () {
        let totalSlots = 0;
        Object.values(selectedSlotsByDay).forEach(slots => {
            totalSlots += slots.length;
        });

        if (totalSlots === 0) {
            if (typeof CosyAlert !== 'undefined') {
                CosyAlert.warning('Select Time Slot', 'Please click "Select Time" and choose at least one 10-minute slot block before proceeding.');
            } else {
                alert('Please select at least one time slot block.');
            }
            return;
        }

        const unitPrice = getUnitPrice();
        const weeks = parseInt($('#selDurationWeeks').val()) || 1;
        const totalActiveSlots = calculateTotalActiveSlotsAcrossWeeks(weeks);
        const serviceCostVal = totalActiveSlots * unitPrice;
        const serviceCost = serviceCostVal.toFixed(2);

        const feeType = (window.cosyCheckout && window.cosyCheckout.feeType) || 'flat';
        const feeVal = (window.cosyCheckout && parseFloat(window.cosyCheckout.feeValue)) || 0.00;
        const serviceFeeVal = (feeType === 'percent') ? (serviceCostVal * (feeVal / 100)) : feeVal;
        const serviceFee = serviceFeeVal.toFixed(2);

        const totalPayable = (serviceCostVal + serviceFeeVal).toFixed(2);

        const existingPending = JSON.parse(localStorage.getItem('cosy_pending_booking') || '{}');
        const activeServiceId = localStorage.getItem('cosy_selected_service_id') || existingPending.serviceId || 1;

        // Compute End Date
        let computedEndDate = '';
        if (startDateParam) {
            try {
                let sObj;
                if (/^\d{2}-\d{2}-\d{4}$/.test(startDateParam)) {
                    const parts = startDateParam.split('-');
                    sObj = new Date(parseInt(parts[2]), parseInt(parts[1]) - 1, parseInt(parts[0]));
                } else {
                    sObj = new Date(startDateParam);
                }
                if (!isNaN(sObj.getTime())) {
                    sObj.setDate(sObj.getDate() + (weeks * 7) - 1);
                    const dd = String(sObj.getDate()).padStart(2, '0');
                    const mm = String(sObj.getMonth() + 1).padStart(2, '0');
                    const yyyy = sObj.getFullYear();
                    computedEndDate = `${dd}-${mm}-${yyyy}`;
                }
            } catch (e) { }
        }

        // Compute Week Days
        let computedWeekDaysArr = [];
        const daysMap = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        Object.keys(selectedSlotsByDay).forEach(dKey => {
            if (selectedSlotsByDay[dKey] && selectedSlotsByDay[dKey].length > 0) {
                let dObj;
                if (/^\d{2}-\d{2}-\d{4}$/.test(dKey)) {
                    const parts = dKey.split('-');
                    dObj = new Date(parseInt(parts[2]), parseInt(parts[1]) - 1, parseInt(parts[0]));
                } else {
                    dObj = new Date(dKey);
                }
                if (dObj && !isNaN(dObj.getTime())) {
                    const dName = daysMap[dObj.getDay()];
                    if (!computedWeekDaysArr.includes(dName)) computedWeekDaysArr.push(dName);
                } else {
                    if (!computedWeekDaysArr.includes(dKey)) computedWeekDaysArr.push(dKey);
                }
            }
        });
        const computedWeekDays = computedWeekDaysArr.join(', ');

        // Compute Selected Slots Timeline (showing Day Name + Slot Times breakdown e.g. Mon 08:00 AM, 08:10 AM)
        let computedTimelineArr = [];
        const shortDaysMap = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        Object.keys(selectedSlotsByDay).forEach(dKey => {
            const timesArr = selectedSlotsByDay[dKey];
            if (Array.isArray(timesArr) && timesArr.length > 0) {
                let dObj;
                if (/^\d{2}-\d{2}-\d{4}$/.test(dKey)) {
                    const parts = dKey.split('-');
                    dObj = new Date(parseInt(parts[2]), parseInt(parts[1]) - 1, parseInt(parts[0]));
                } else {
                    dObj = new Date(dKey);
                }

                let dayLabel = '';
                if (dObj && !isNaN(dObj.getTime())) {
                    dayLabel = shortDaysMap[dObj.getDay()];
                }

                const dayTimes = timesArr.map(normalizeTimeStr).join(', ');
                if (dayLabel) {
                    computedTimelineArr.push(`${dayLabel} ${dayTimes}`);
                } else {
                    computedTimelineArr.push(dayTimes);
                }
            }
        });
        const computedSlotsTimeline = computedTimelineArr.join(' | ');

        const bookingPayload = {
            serviceId: activeServiceId,
            service: activeServiceTitle,
            providerId: existingPending.providerId || providerIdParam || 0,
            providerName: providerNameParam,
            startDate: startDateParam || new Date().toISOString().split('T')[0],
            endDate: computedEndDate || existingPending.endDate || startDateParam || '',
            weeklyBooking: weeks + (weeks === 1 ? ' Week Duration' : ' Weeks Recurring'),
            numberOfWeeks: weeks,
            numberOfBookings: totalSlots,
            serviceCost: serviceCost,
            serviceFee: serviceFee,
            totalPayable: totalPayable,
            isGift: $('#chkBookAnother').is(':checked'),
            recipientName: $('#recipientName').val() || '',
            recipientEmail: $('#recipientEmail').val() || '',
            slots: selectedSlotsByDay,
            weekDays: computedWeekDays || existingPending.weekDays || '',
            slotsTimeline: computedSlotsTimeline || existingPending.slotsTimeline || ''
        };

        const $btn = $(this);
        const origBtnText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Verifying Slots...');

        const ajaxUrl = (window.cosyCheckout && window.cosyCheckout.ajaxUrl) || window.ajaxurl || '/wp-admin/admin-ajax.php';
        const targetProviderId = existingPending.providerId || providerIdParam || (window.cosyCheckout && window.cosyCheckout.providerId) || 0;

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'cosy_validate_booking_slots',
                provider_id: targetProviderId,
                start_date: startDateParam || '',
                num_weeks: weeks,
                slots: JSON.stringify(selectedSlotsByDay)
            },
            success: function (res) {
                $btn.prop('disabled', false).html(origBtnText);
                if (res && res.success === false && res.data && res.data.message) {
                    if (typeof CosyAlert !== 'undefined') {
                        CosyAlert.error('Slot Unavailable', res.data.message);
                    } else {
                        alert(res.data.message);
                    }
                    return;
                }

                localStorage.setItem('cosy_pending_booking', JSON.stringify(bookingPayload));
                setTimeout(() => {
                    renderSummaryScreen();
                }, 200);
            },
            error: function () {
                $btn.prop('disabled', false).html(origBtnText);
                localStorage.setItem('cosy_pending_booking', JSON.stringify(bookingPayload));
                renderSummaryScreen();
            }
        });
    });

    $(document).on('click', '#btnBackToSchedule', function () {
        renderCallScheduleScreen();
    });

    $(document).on('click', '#cosyCheckoutBackBtn', function (e) {
        e.preventDefault();
        localStorage.removeItem('cosy_pending_booking');
        
        // If coming from profile page, use history.back() to keep history stack clean
        if (document.referrer && (document.referrer.includes('/author/') || document.referrer.includes('provider-profile') || document.referrer.includes('/service-details/'))) {
            window.history.back();
            return;
        }

        const targetUrl = (window.providerProfileUrl && window.providerProfileUrl.length > 5)
            ? window.providerProfileUrl
            : (localStorage.getItem('cosy_selected_provider_url') || (window.cosyCheckout && window.cosyCheckout.providerUrl));
        
        if (targetUrl && targetUrl.length > 5) {
            window.location.href = targetUrl;
        } else {
            window.history.back();
        }
    });

    // Handle Pay Now button click (WorldPay Gateway)
    $(document).on('click', '#cosyPayNowBtn', function (e) {
        e.preventDefault();

        const $btn = $(this);
        const pendingBookingData = localStorage.getItem('cosy_pending_booking');
        if (!pendingBookingData) {
            if (typeof CosyAlert !== 'undefined') {
                CosyAlert.error('Booking Session Missing', 'No active booking session found. Please select your slot and try again.');
            } else {
                alert('No active booking session found.');
            }
            return;
        }

        const booking = JSON.parse(pendingBookingData);
        const ajaxAction = 'cosy_create_worldpay_session';
        const gwName = 'WorldPay';
        const defaultBtnText = 'Pay Now via WorldPay <i class="fas fa-lock ms-2"></i>';

        $btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Connecting to ${gwName}...`);

        const slotsVal = typeof booking.slots === 'string' ? booking.slots : JSON.stringify(booking.slots || []);

        const postData = {
            action: ajaxAction,
            nonce: (window.cosyCheckout && window.cosyCheckout.nonce) || '',
            serviceId: booking.serviceId || localStorage.getItem('cosy_selected_service_id') || 1,
            service: activeServiceTitle || booking.service || 'Parent Conversation',
            providerId: booking.providerId || providerIdParam || 0,
            providerName: booking.providerName || providerNameParam || 'Verified Parent',
            startDate: booking.startDate || '',
            endDate: booking.endDate || '',
            weeklyBooking: booking.weeklyBooking || '',
            numberOfWeeks: booking.numberOfWeeks || 1,
            numberOfBookings: booking.numberOfBookings || 1,
            serviceCost: booking.serviceCost || '0.00',
            serviceFee: booking.serviceFee || '0.00',
            totalPayable: booking.totalPayable || '0.00',
            slots: slotsVal,
            weekDays: booking.weekDays || '',
            slotsTimeline: booking.slotsTimeline || '',
            isGift: booking.isGift ? 1 : 0,
            recipientName: booking.recipientName || '',
            recipientEmail: booking.recipientEmail || ''
        };

        $.ajax({
            url: (window.cosyCheckout && window.cosyCheckout.ajaxUrl) || '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: postData,
            success: function (res) {
                if (res.success && res.data && res.data.url) {
                    window.location.href = res.data.url;
                } else {
                    const msg = (res.data && res.data.message) ? res.data.message : `Unable to create ${gwName} checkout session.`;
                    $btn.prop('disabled', false).html(defaultBtnText);
                    if (typeof CosyAlert !== 'undefined') {
                        CosyAlert.error('Payment Error', msg);
                    } else {
                        alert(msg);
                    }
                }
            },
            error: function (xhr, status, error) {
                $btn.prop('disabled', false).html(defaultBtnText);
                if (typeof CosyAlert !== 'undefined') {
                    CosyAlert.error('Server Error', 'Failed to communicate with payment server. Please try again.');
                } else {
                    alert('Server error: ' + error);
                }
            }
        });
    });
});
