let currentDate = new Date();
let selectedDate = null;
let selectedTimeSlotsByDay = {};
let selectedService = null;
let currentModalDate = '';

/**
 * SELECT SERVICE ITEM
 * 
 * USE CASE: Handles click event on service option rows on the provider profile page.
 * HOW TO USE: Triggered via onclick="selectServiceItem(this, id, title, price, duration)".
 * WHAT IT DOES INTERNALLY: Updates active UI highlight and sets global selectedService object.
 */
function selectServiceItem(el, id, title, price, duration) {
    // Reset all services
    const allRows = document.querySelectorAll('.service-item-row');
    allRows.forEach(row => {
        row.classList.remove('selected');
        const icon = row.querySelector('.service-check-icon');
        if (icon) icon.style.color = '#cbd5e1';
    });

    // Highlight selected service
    el.classList.add('selected');
    const activeIcon = el.querySelector('.service-check-icon');
    if (activeIcon) activeIcon.style.color = '#a44390';

    selectedService = {
        id: parseInt(id),
        title: title,
        price: parseFloat(price),
        duration: parseInt(duration)
    };


    // Update final price if a date is already selected
    if (selectedDate && document.getElementById('weeklyPricingSection').style.display === 'block') {
        updateFinalPrice();
    }
}

/**
 * RENDER CALENDAR
 * 
 * USE CASE: Generates interactive monthly date grid for the current month.
 * HOW TO USE: Called on page load and when navigating month pagination controls.
 * WHAT IT DOES INTERNALLY: 
 * 1. Checks provider availability and holiday dates array.
 * 2. Renders disabled styles for past dates, holidays, and non-working days.
 * 3. Highlights active selected date cell.
 */
function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    document.getElementById('currentMonthYear').textContent = monthNames[month] + ' ' + year;

    const firstDay = new Date(year, month, 1).getDay(); // 0=Sun
    const offset = (firstDay === 0) ? 6 : firstDay - 1;
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const today = new Date();

    const container = document.getElementById('calendarDays');
    container.innerHTML = '';

    for (let i = 0; i < offset; i++) {
        container.innerHTML += `<div></div>`;
    }

    for (let d = 1; d <= daysInMonth; d++) {
        const cellDate = new Date(year, month, d);
        const isPast = cellDate < new Date(today.getFullYear(), today.getMonth(), today.getDate());
        const isToday = cellDate.toDateString() === today.toDateString();
        const isSelected = selectedDate && cellDate.toDateString() === selectedDate.toDateString();

        // Deterministic YYYY-MM-DD formatting to prevent timezone shift issues
        const cellYear = year;
        const cellMonth = String(month + 1).padStart(2, '0');
        const cellDayStr = String(d).padStart(2, '0');
        const dateString = `${cellYear}-${cellMonth}-${cellDayStr}`;
        const isHoliday = Array.isArray(window.providerHolidays) && window.providerHolidays.includes(dateString);
        const holidayReason = (window.providerHolidayReasons && window.providerHolidayReasons[dateString]) ? window.providerHolidayReasons[dateString] : 'Holiday';

        // Check if provider has configured working hours for this day of the week
        const dayNamesMap = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const dayName = dayNamesMap[cellDate.getDay()];
        let isDayOff = false;
        if (window.providerAvailability && typeof window.providerAvailability === 'object') {
            const dayConfig = window.providerAvailability[dayName];
            if (!dayConfig || (!dayConfig.start_time && !dayConfig.end_time)) {
                isDayOff = true;
            }
        }

        // Today, past days, holidays, and non-working days are unavailable
        const isUnavailable = isPast || isToday || isHoliday || isDayOff;

        let bg = '#ffffff';
        let color = '#a44390';
        let border = '1.5px solid #a44390';
        let fontWeight = '600';
        let boxShadow = 'none';
        let textDecoration = 'none';
        let titleAttr = '';

        if (isHoliday) {
            bg = '#f1f5f9';
            color = '#94a3b8';
            border = '1px dashed #cbd5e1';
            boxShadow = 'none';
            textDecoration = 'line-through';
            titleAttr = `Unavailable (${holidayReason})`;
        } else if (isDayOff) {
            bg = '#f8fafc';
            color = '#cbd5e1';
            border = '1px solid transparent';
            boxShadow = 'none';
            titleAttr = 'Non-working day';
        } else if (isPast || isToday) {
            bg = 'transparent';
            color = '#cbd5e1';
            border = '1px solid transparent';
            boxShadow = 'none';
            titleAttr = isPast ? 'Past Date' : 'Today';
        } else if (isSelected) {
            bg = 'linear-gradient(135deg, #a44390 0%, #6d2e67 100%)';
            color = '#ffffff';
            border = '1.5px solid #a44390';
            fontWeight = '700';
            boxShadow = '0 4px 12px rgba(164, 67, 144, 0.35)';
        } else {
            bg = '#fdf5fc';
            color = '#a44390';
            border = '1.5px solid #a44390';
            fontWeight = '600';
            boxShadow = 'none';
        }

        container.innerHTML += `
            <div onclick="${isUnavailable ? '' : 'selectDay(this, ' + d + ')'}" 
                 data-day="${d}" data-month="${month}" data-year="${year}"
                 style="aspect-ratio:1; display:flex; align-items:center; justify-content:center;
                        font-size:0.85rem; font-weight:${fontWeight}; border-radius:12px;
                        background:${bg}; color:${color}; border:${border}; box-shadow:${boxShadow};
                        text-decoration:${textDecoration};
                        cursor:${isUnavailable ? 'not-allowed' : 'pointer'}; transition:all 0.2s;"
                 ${isUnavailable ? '' : `onmouseover="if(!this.classList.contains('selected-cell')){this.style.background='#a44390';this.style.color='#ffffff';}" onmouseout="if(!this.classList.contains('selected-cell')){this.style.background='${bg}';this.style.color='${color}';}"`}
                 class="${isSelected ? 'selected-cell' : ''}"
                 title="${titleAttr}">
                ${d}
            </div>`;
    }
}

/**
 * SELECT DAY
 * 
 * USE CASE: Handles user click on a calendar date cell.
 * HOW TO USE: Triggered via onclick="selectDay(this, d)".
 * WHAT IT DOES INTERNALLY: 
 * 1. Stores selected start_date in localStorage.
 * 2. Immediately redirects user to checkout page with start_date & provider_id parameters.
 */
function selectDay(el, day) {
    if (!selectedService) {
        if (window.cosyDefaultService) {
            selectedService = window.cosyDefaultService;
        } else if (typeof cosyCalendar !== 'undefined' && cosyCalendar.defaultService) {
            selectedService = cosyCalendar.defaultService;
        } else {
            selectedService = { id: 0, title: 'Parent Conversation', price: 0, duration: 10 };
        }
    }

    const year = parseInt(el.dataset.year);
    const month = parseInt(el.dataset.month);
    selectedDate = new Date(year, month, day);
    renderCalendar();

    // Format start date as DD-MM-YYYY
    const monthStr = String(month + 1).padStart(2, '0');
    const dayStr = String(day).padStart(2, '0');
    const formattedDate = `${dayStr}-${monthStr}-${year}`;

    // Read service parameter from current profile URL bar to maintain exact selected category
    const urlParams = new URLSearchParams(window.location.search);
    const urlServiceName = urlParams.get('service_name') || urlParams.get('service_category');

    let serviceTitle = '';
    let serviceSlug = '';

    // Prioritize URL service parameter over defaults to avoid resetting to default service
    if (urlServiceName) {
        serviceSlug = urlServiceName;
        serviceTitle = urlServiceName.replace(/[-_]+/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    } else if (selectedService && selectedService.title) {
        serviceTitle = selectedService.title;
        serviceSlug = selectedService.slug || serviceTitle.toLowerCase().replace(/\s+/g, '-');
    } else if (window.cosyDefaultService && window.cosyDefaultService.title) {
        serviceTitle = window.cosyDefaultService.title;
        serviceSlug = window.cosyDefaultService.slug || serviceTitle.toLowerCase().replace(/\s+/g, '-');
    } else {
        serviceTitle = 'Parent Conversation';
        serviceSlug = 'parent-conversation';
    }

    const serviceId = (selectedService && selectedService.id) ? selectedService.id : (window.cosyDefaultService ? window.cosyDefaultService.id : 1);
    const servicePrice = (selectedService && selectedService.price) ? selectedService.price : (window.cosyDefaultService ? window.cosyDefaultService.price : 0);

    // Save selection details to localStorage for smooth transition
    localStorage.setItem('cosy_selected_start_date', formattedDate);
    localStorage.setItem('cosy_selected_provider_id', window.providerId || 0);
    localStorage.setItem('cosy_selected_provider_name', window.providerName || '');
    localStorage.setItem('cosy_selected_provider_url', window.providerProfileUrl || window.location.href);
    localStorage.setItem('cosy_selected_service_name', serviceTitle);
    localStorage.setItem('cosy_selected_service_id', serviceId);
    if (servicePrice) {
        localStorage.setItem('cosy_selected_service_price', servicePrice);
    }

    // Initialize fresh pending booking session to eliminate stale cache from previous sessions
    try {
        const freshBooking = {
            service: serviceTitle,
            serviceId: serviceId,
            unitPrice: servicePrice || 0,
            providerId: window.providerId || 0,
            providerName: window.providerName || '',
            startDate: formattedDate,
            slots: {},
            slotsTimeline: ''
        };
        localStorage.setItem('cosy_pending_booking', JSON.stringify(freshBooking));
        localStorage.setItem('cosy_selected_provider_url', window.location.href);
    } catch (e) { }

    // Redirect to the dedicated Call Schedule / Checkout page with parameters
    const fallbackPath = (window.location.pathname.indexOf('/cosyplugin') !== -1 ? '/cosyplugin' : '') + '/cosy-checkout/';
    const baseUrl = window.checkoutUrl || (window.location.origin + fallbackPath);
    const separator = baseUrl.includes('?') ? '&' : '?';
    window.location.href = baseUrl + separator + 'step=schedule&provider_id=' + (window.providerId || 0) + '&start_date=' + formattedDate + '&service_name=' + encodeURIComponent(serviceSlug);
}


/**
 * CHANGE MONTH
 * 
 * USE CASE: Navigates calendar view backward or forward by 1 month.
 * HOW TO USE: Triggered via prev/next arrow buttons on calendar header.
 * WHAT IT DOES INTERNALLY: Increments or decrements currentDate month index and calls renderCalendar().
 */
function changeMonth(dir) {
    currentDate.setMonth(currentDate.getMonth() + dir);
    renderCalendar();
}

document.addEventListener('DOMContentLoaded', renderCalendar);

/**
 * OPEN TIME SLOT MODAL
 * 
 * USE CASE: Opens slot picker modal for a specific calendar date and fetches available/booked time slots.
 * HOW TO USE: Triggered when user clicks a date cell on calendar.
 * WHAT IT DOES INTERNALLY:
 * 1. Shows Bootstrap timeSlotModal with spinner.
 * 2. Fires AJAX request cosy_get_booked_slots to retrieve existing bookings.
 * 3. Generates slot buttons based on provider working hours and break times.
 */
function openTimeSlotModal(dateStr) {
    currentModalDate = dateStr;
    const modal = new bootstrap.Modal(document.getElementById('timeSlotModal'));
    const grid = document.getElementById('timeGrid');

    // Show spinner / loading state
    grid.innerHTML = `
            <div class="col-12 py-5 d-flex flex-column align-items-center justify-content-center w-100" style="grid-column: 1 / -1;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted small mt-2 mb-0 text-center">Checking slot availability...</p>
            </div>
        `;
    modal.show();

    const dateObj = new Date(dateStr);
    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const dayName = dayNames[dateObj.getDay()];
    const avail = window.providerAvailability ? window.providerAvailability[dayName] : null;

    if (!avail) {
        grid.innerHTML = '<div class="col-12 text-center py-4 text-muted w-100" style="grid-column: 1 / -1;">No availability set for this day.</div>';
        return;
    }

    // Fetch booked slots via AJAX
    jQuery.ajax({
        url: window.ajaxUrl,
        type: 'POST',
        data: {
            action: 'cosy_get_booked_slots',
            nonce: window.nonce,
            provider_id: window.providerId,
            date: dateStr,
            num_weeks: parseInt(jQuery('#selDurationWeeks').val()) || 1
        },
        success: function (response) {
            grid.innerHTML = '';
            let bookedSlots = [];
            if (response.success && Array.isArray(response.data)) {
                bookedSlots = response.data;
            }

            const startStr = avail.start_time; // e.g. "09:00"
            const endStr = avail.end_time; // e.g. "17:00"
            const slotDuration = parseInt(avail.slot_duration) || (selectedService ? selectedService.duration : 30);

            const baseDateStr = '1970-01-01T';
            let startTime = new Date(`${baseDateStr}${startStr}:00`);
            let endTime = new Date(`${baseDateStr}${endStr}:00`);

            // Handle overnight shifts
            if (endTime <= startTime) {
                endTime.setDate(endTime.getDate() + 1);
            }

            let breakStart = null;
            let breakEnd = null;

            if (avail.break_start && avail.break_end) {
                breakStart = new Date(`${baseDateStr}${avail.break_start}:00`);
                breakEnd = new Date(`${baseDateStr}${avail.break_end}:00`);

                // Adjust break times for overnight shifts
                if (breakStart < startTime) {
                    breakStart.setDate(breakStart.getDate() + 1);
                }
                if (breakEnd < breakStart) {
                    breakEnd.setDate(breakEnd.getDate() + 1);
                }
            }

            let currentTime = new Date(startTime);
            let slotsCount = 0;

            while (currentTime < endTime) {
                const timeStr = currentTime.toTimeString().substring(0, 5); // "HH:MM"
                const displayTime = currentTime.toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                // Calculate the end of THIS slot
                const currentSlotEnd = new Date(currentTime);
                currentSlotEnd.setMinutes(currentSlotEnd.getMinutes() + slotDuration);

                // Don't create the slot if it exceeds the shift end time
                if (currentSlotEnd > endTime) {
                    break;
                }

                // Check if this slot falls within a break (overlap logic)
                let isInBreak = false;
                if (breakStart && breakEnd) {
                    if (currentTime < breakEnd && currentSlotEnd > breakStart) {
                        isInBreak = true;
                    }
                }

                if (!isInBreak) {
                    slotsCount++;
                    const isSelected = selectedTimeSlotsByDay[dateStr] && selectedTimeSlotsByDay[dateStr].includes(timeStr);
                    const normDisplayTime = displayTime.trim().toUpperCase().replace(/^0+/, '');
                    const isBooked = bookedSlots.some(b => {
                        const normB = String(b).trim().toUpperCase().replace(/^0+/, '');
                        return normB === normDisplayTime || b === timeStr || b === displayTime;
                    });

                    grid.innerHTML += `
                            <div class="time-block p-2 text-center small fw-bold ${isSelected ? 'selected' : ''} ${isBooked ? 'booked' : ''}" 
                                 style="${isBooked ? 'background:#e2e8f0; color:#94a3b8; cursor:not-allowed;' : ''}"
                                 onclick="${isBooked ? '' : `toggleTimeSlot('${timeStr}', this)`}"
                                 title="${isBooked ? 'Already Booked' : ''}">
                                ${displayTime}
                            </div>
                        `;
                }

                // Move to next slot
                currentTime = new Date(currentSlotEnd);
            }

            if (slotsCount === 0) {
                grid.innerHTML = '<div class="col-12 text-center py-4 text-muted">No slots available for the selected range.</div>';
            }

            updateModalDuration();
        },
        error: function () {
            grid.innerHTML = '<div class="col-12 text-center py-4 text-danger">Failed to check slot availability. Please try again.</div>';
        }
    });
}

/**
 * TOGGLE TIME SLOT
 * 
 * USE CASE: Selects or deselects an available time slot inside the slot picker modal.
 * HOW TO USE: Triggered via onclick on individual time slot blocks.
 * WHAT IT DOES INTERNALLY: Updates selectedTimeSlotsByDay array and calls updateModalDuration().
 */
function toggleTimeSlot(time, el) {
    if (!selectedTimeSlotsByDay[currentModalDate]) selectedTimeSlotsByDay[currentModalDate] = [];
    const index = selectedTimeSlotsByDay[currentModalDate].indexOf(time);
    if (index > -1) {
        selectedTimeSlotsByDay[currentModalDate].splice(index, 1);
        el.classList.remove('selected');
    } else {
        selectedTimeSlotsByDay[currentModalDate].push(time);
        el.classList.add('selected');
    }
    updateModalDuration();
}

/**
 * UPDATE MODAL DURATION
 * 
 * USE CASE: Calculates and updates the total duration display in the slot picker modal header.
 * HOW TO USE: Called internally whenever a slot selection is toggled.
 * WHAT IT DOES INTERNALLY: Multiplies selected slot count by slot duration in minutes.
 */
function updateModalDuration() {
    const count = selectedTimeSlotsByDay[currentModalDate] ? selectedTimeSlotsByDay[currentModalDate].length : 0;
    const durationEl = document.getElementById('modalTotalDuration');

    // Get slot duration for this day to calculate total correctly
    const dateObj = new Date(currentModalDate);
    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const dayName = dayNames[dateObj.getDay()];
    const avail = window.providerAvailability ? window.providerAvailability[dayName] : null;
    const slotDur = avail ? parseInt(avail.slot_duration) : 15;

    if (durationEl) durationEl.textContent = `${count * slotDur} minutes`;
}

/**
 * CONFIRM TIME SLOTS
 * 
 * USE CASE: Saves selected time slots from modal and updates sidebar summary and pricing.
 * HOW TO USE: Triggered by clicking "Confirm Slots" button in time slot modal.
 * WHAT IT DOES INTERNALLY: Hides modal, updates sidebar duration label, and calls updateFinalPrice().
 */
function confirmTimeSlots() {
    const modalEl = document.getElementById('timeSlotModal');
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) modalInstance.hide();

    // Update Sidebar Row
    const selectedSlots = selectedTimeSlotsByDay[currentModalDate] || [];

    const dateObj = new Date(currentModalDate);
    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const dayName = dayNames[dateObj.getDay()];
    const avail = window.providerAvailability ? window.providerAvailability[dayName] : null;
    const slotDur = avail ? parseInt(avail.slot_duration) : 15;

    const duration = selectedSlots.length * slotDur;
    const durationTextEl = document.getElementById(`duration-${currentModalDate}`);
    if (durationTextEl) {
        durationTextEl.textContent = `${duration} minutes Call Duration`;
        const btn = durationTextEl.closest('.d-flex').querySelector('button');
        if (btn) btn.textContent = duration > 0 ? 'Edit Time' : 'Select Time';
    }

    // Pricing visibility
    let grandTotalMin = 0;
    for (const d in selectedTimeSlotsByDay) {
        const dObj = new Date(d);
        const dName = dayNames[dObj.getDay()];
        const dAvail = window.providerAvailability ? window.providerAvailability[dName] : null;
        const dSlotDur = dAvail ? parseInt(dAvail.slot_duration) : 15;
        grandTotalMin += selectedTimeSlotsByDay[d].length * dSlotDur;
    }

    const pricingSection = document.getElementById('weeklyPricingSection');
    if (pricingSection) {
        if (grandTotalMin > 0) {
            pricingSection.style.display = 'block';
            updateFinalPrice();
        } else {
            pricingSection.style.display = 'none';
        }
    }
}

/**
 * UPDATE FINAL PRICE
 * 
 * USE CASE: Recalculates total cost based on selected slots, service unit price, and booking weeks.
 * HOW TO USE: Called internally whenever slots or week duration input change.
 * WHAT IT DOES INTERNALLY: Updates final price element with formatted currency symbol.
 */
function updateFinalPrice() {
    let totalSlots = 0;
    for (const d in selectedTimeSlotsByDay) {
        totalSlots += selectedTimeSlotsByDay[d].length;
    }

    const weeks = parseInt(document.getElementById('totalBookingWeeks').value) || 1;
    const servicePrice = selectedService ? selectedService.price : 0;

    const totalPrice = totalSlots * servicePrice * weeks;

    const amountText = document.getElementById('finalTotalAmountText');
    const currencySymbol = (typeof cosyCalendar !== 'undefined' && cosyCalendar.currencySymbol) ? cosyCalendar.currencySymbol : '£';
    if (amountText) amountText.textContent = `${currencySymbol}${totalPrice.toFixed(2)}`;
}

/**
 * OPEN VIDEO POPUP
 * 
 * USE CASE: Displays introduction video popup modal on provider profile page.
 * HOW TO USE: Triggered when user clicks video thumbnail on provider profile.
 * WHAT IT DOES INTERNALLY: Converts YouTube/Vimeo URL to embed iframe URL and shows modal.
 */
function openVideoPopup(url) {
    const modal = new bootstrap.Modal(document.getElementById('videoModal'));
    const iframe = document.getElementById('videoIframe');
    let embedUrl = url;
    if (url.includes('youtube.com/watch?v=')) embedUrl = url.replace('watch?v=', 'embed/');
    else if (url.includes('youtu.be/')) embedUrl = url.replace('youtu.be/', 'youtube.com/embed/');
    iframe.src = embedUrl;
    modal.show();
}

document.addEventListener('DOMContentLoaded', () => {
    // Clear old pending booking data from previous sessions when landing on profile page
    localStorage.removeItem('cosy_pending_booking');
    selectedTimeSlotsByDay = {};

    const stars = document.querySelectorAll('.rating-star');
    const ratingInput = document.getElementById('selectedRating');
    const addReviewBtn = document.getElementById('addReviewBtn');
    const postReviewBtn = document.getElementById('postReviewBtn');
    const reviewText = document.getElementById('reviewText');
    const reviewFormEl = document.getElementById('reviewForm');

    if (addReviewBtn) {
        addReviewBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (!window.currentUser || !window.currentUser.isLoggedIn) {
                CosyAlert.warning('Customer Login Required', 'Please log in to a Customer account to post a review.');
                return;
            }

            if (window.currentUser.role !== 'customer') {
                CosyAlert.error('Access Restricted', 'Only registered customers are allowed to post reviews.');
                return;
            }

            const bsCollapse = bootstrap.Collapse.getOrCreateInstance(reviewFormEl);
            bsCollapse.toggle();
        });
    }

    if (postReviewBtn) {
        postReviewBtn.addEventListener('click', function (e) {
            e.preventDefault();
            const ratingVal = ratingInput ? parseInt(ratingInput.value) : 0;
            const reviewVal = reviewText ? reviewText.value.trim() : '';

            if (ratingVal < 1 || ratingVal > 5) {
                CosyAlert.warning('Rating Required', 'Please select a rating by clicking on the stars.');
                return;
            }

            if (reviewVal === '') {
                CosyAlert.warning('Review Required', 'Please write a brief comment describing your experience.');
                return;
            }

            postReviewBtn.disabled = true;
            postReviewBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1" style="color: #ffffff !important;"></i> <span style="color: #ffffff !important;">Posting...</span>';
            postReviewBtn.style.setProperty('color', '#ffffff', 'important');

            // Send AJAX request
            jQuery.ajax({
                url: window.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cosy_add_provider_review',
                    nonce: window.nonce,
                    rating: ratingVal,
                    review: reviewVal,
                    provider_id: window.providerId
                },
                success: function (response) {
                    postReviewBtn.disabled = false;
                    postReviewBtn.textContent = 'Post Review';

                    if (response.success) {
                        CosyAlert.success('Thank You!', response.data.message);

                        // Clear inputs & hide form
                        if (ratingInput) ratingInput.value = '0';
                        if (reviewText) reviewText.value = '';
                        highlightStars(0);
                        const bsCollapse = bootstrap.Collapse.getInstance(reviewFormEl);
                        if (bsCollapse) bsCollapse.hide();
                    } else {
                        CosyAlert.error('Submission Failed', response.data.message || 'Something went wrong.');
                    }
                },
                error: function () {
                    postReviewBtn.disabled = false;
                    postReviewBtn.textContent = 'Post Review';
                    CosyAlert.error('Error', 'Failed to communicate with server. Please try again.');
                }
            });
        });
    }

    if (stars.length) {
        stars.forEach(star => {
            star.addEventListener('mouseover', function () {
                highlightStars(this.dataset.rating);
            });
            star.addEventListener('mouseout', function () {
                highlightStars(ratingInput.value);
            });
            star.addEventListener('click', function () {
                ratingInput.value = this.dataset.rating;
                highlightStars(ratingInput.value);
            });
        });
    }

    function highlightStars(val) {
        stars.forEach(s => {
            if (s.dataset.rating <= val) {
                s.classList.replace('far', 'fas');
                s.style.color = '#ffb800';
            } else {
                s.classList.replace('fas', 'far');
                s.style.color = '#cbd5e1';
            }
        });
    }

    const bookServiceBtn = document.getElementById('bookServiceBtn');
    if (bookServiceBtn) {
        bookServiceBtn.addEventListener('click', (e) => {
            e.preventDefault();

            // 1. Ensure service is assigned
            if (!selectedService) {
                if (window.cosyDefaultService) {
                    selectedService = window.cosyDefaultService;
                } else if (typeof cosyCalendar !== 'undefined' && cosyCalendar.defaultService) {
                    selectedService = cosyCalendar.defaultService;
                } else {
                    selectedService = { id: 0, title: 'Parent Conversation', price: 0, duration: 10 };
                }
            }

            // 2. Check if at least one slot is selected
            let totalSlots = 0;
            let bookingSlotsList = [];
            for (const dateStr in selectedTimeSlotsByDay) {
                const slots = selectedTimeSlotsByDay[dateStr];
                if (slots && slots.length > 0) {
                    totalSlots += slots.length;
                    slots.forEach(time => {
                        bookingSlotsList.push({
                            date: dateStr, // e.g. "Wed May 20 2026"
                            time: time // e.g. "09:00"
                        });
                    });
                }
            }

            if (totalSlots === 0) {
                CosyAlert.warning('Select Time Slot', 'Please click on the calendar date and select at least one starting time slot.');
                return;
            }

            // 3. Get total weeks and calculations
            const weeks = parseInt(document.getElementById('totalBookingWeeks').value) || 1;
            const dynamicHourlyRate = (selectedService && selectedService.price) ? parseFloat(selectedService.price) : ((window.cosyDefaultService && window.cosyDefaultService.price) ? parseFloat(window.cosyDefaultService.price) : 0);
            const slotUnitPrice = dynamicHourlyRate / 6.0;
            const serviceCost = totalSlots * slotUnitPrice * weeks;
            const feeType = window.serviceFeeType || 'flat';
            const feeVal = parseFloat(window.serviceFeeValue) || 0.00;
            const serviceFee = (feeType === 'percent') ? (serviceCost * (feeVal / 100)) : feeVal;
            const totalPayable = serviceCost + serviceFee;

            // Sort bookingSlotsList by date chronologically
            bookingSlotsList.sort((a, b) => new Date(a.date) - new Date(b.date));

            // Start date is the selected calendar date or fallback to first selected slot date
            const startDateObj = selectedDate ? new Date(selectedDate) : new Date(bookingSlotsList[0].date);
            const startDateStr = startDateObj.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            // Calculate end date based on selected weeks duration (e.g. 1 week = 7 days duration range)
            const endDateObj = new Date(startDateObj);
            endDateObj.setDate(startDateObj.getDate() + (weeks * 7) - 1);

            const endDateStr = endDateObj.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            // Map day names to slot time for display
            // e.g. "Wednesday 9:00 AM 10 Minutes"
            const firstSlot = bookingSlotsList[0];
            const firstSlotDate = new Date(firstSlot.date);
            const dayName = firstSlotDate.toLocaleDateString('en-US', { weekday: 'long' });

            // Format the slot time nicely (e.g. "09:00" to "9:00 AM")
            const [hourStr, minStr] = firstSlot.time.split(':');
            let hour = parseInt(hourStr);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            hour = hour % 12;
            hour = hour ? hour : 12; // the hour '0' should be '12'
            const timeFormatted = `${hour}:${minStr} ${ampm}`;

            const weeklyBookingStr = `${dayName} ${timeFormatted} ${selectedService.duration} Minutes`;

            // Get provider info
            const providerName = window.providerName;
            const providerId = window.providerId;

            // Generate Available Week days from provider availability
            const dayNamesList = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const availableDays = [];
            dayNamesList.forEach(day => {
                const avail = window.providerAvailability ? window.providerAvailability[day] : null;
                if (avail && avail.start_time && avail.end_time) {
                    availableDays.push(day);
                }
            });
            const weekDaysStr = availableDays.join(', ');

            // Generate slots timeline string (e.g. "09:00 AM, 09:10 AM...")
            const slotTimes = bookingSlotsList.map(slot => {
                const [hStr, mStr] = slot.time.split(':');
                let h = parseInt(hStr);
                const ap = h >= 12 ? 'PM' : 'AM';
                h = h % 12;
                h = h ? h : 12;
                const hFormatted = String(h).padStart(2, '0');
                return `${hFormatted}:${mStr} ${ap}`;
            });
            // Get unique slot times
            const uniqueSlotTimes = [...new Set(slotTimes)];
            const slotsTimelineStr = uniqueSlotTimes.join(', ');

            // Save details to localStorage
            const pendingBooking = {
                serviceId: selectedService.id,
                service: selectedService.title,
                serviceDuration: selectedService.duration,
                unitPrice: selectedService.price,
                providerName: providerName,
                providerId: providerId,
                startDate: startDateStr,
                endDate: endDateStr,
                weeklyBooking: weeklyBookingStr,
                numberOfWeeks: weeks,
                numberOfBookings: totalSlots * weeks,
                serviceCost: serviceCost.toFixed(2),
                serviceFee: serviceFee.toFixed(2),
                totalPayable: totalPayable.toFixed(2),
                slots: bookingSlotsList,
                weekDays: weekDaysStr,
                slotsTimeline: slotsTimelineStr
            };

            localStorage.setItem('cosy_pending_booking', JSON.stringify(pendingBooking));

            // 4. Disable button and show processing spinner, then redirect to Checkout page after 1.5 seconds
            bookServiceBtn.disabled = true;
            bookServiceBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing Checkout...`;

            setTimeout(() => {
                window.location.href = window.checkoutUrl;
            }, 1500);
        });
    }
});