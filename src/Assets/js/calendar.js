let currentDate = new Date();
let selectedDate = null;
let selectedTimeSlotsByDay = {};
let selectedService = null;
let currentModalDate = '';

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

        // Format cellDate to YYYY-MM-DD for holiday check
        const cellYear = cellDate.getFullYear();
        const cellMonth = String(cellDate.getMonth() + 1).padStart(2, '0');
        const cellDayStr = String(cellDate.getDate()).padStart(2, '0');
        const dateString = `${cellYear}-${cellMonth}-${cellDayStr}`;
        const isHoliday = window.providerHolidays && window.providerHolidays.includes(dateString);

        const isUnavailable = isPast || isHoliday;

        let bg = '#f8fafc';
        let color = '#1e293b';
        let border = '1px solid transparent';
        let fontWeight = '600';

        if (isUnavailable) {
            bg = 'transparent';
            color = '#cbd5e1';
        } else if (isSelected) {
            bg = '#fff';
            color = '#a44390';
            border = '1.5px solid #a44390';
            fontWeight = '700';
        } else if (isToday) {
            bg = '#fdf2fb';
            color = '#a44390';
            border = '1.5px solid #a44390';
            fontWeight = '700';
        }

        container.innerHTML += `
            <div onclick="${isUnavailable ? '' : 'selectDay(this, ' + d + ')'}" 
                 data-day="${d}" data-month="${month}" data-year="${year}"
                 style="aspect-ratio:1; display:flex; align-items:center; justify-content:center;
                        font-size:0.85rem; font-weight:${fontWeight}; border-radius:12px;
                        background:${bg}; color:${color}; border:${border};
                        cursor:${isUnavailable ? 'not-allowed' : 'pointer'}; transition:all 0.2s;"
                 title="${isHoliday ? 'Holiday / Unavailable' : ''}">
                ${d}
            </div>`;
    }
}

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

    const bookingSection = document.getElementById('bookingTimeSlots');
    const displayDateText = document.getElementById('displaySelectedDate');
    const slotsList = document.getElementById('timeSlotsList');

    if (bookingSection) {
        bookingSection.style.display = 'block';
        displayDateText.textContent = selectedDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        slotsList.innerHTML = '';
        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        let addedCount = 0;
        let dayOffset = 0;

        while (addedCount < 6 && dayOffset < 14) {
            const nextDate = new Date(selectedDate);
            nextDate.setDate(selectedDate.getDate() + dayOffset);
            const dayIndex = nextDate.getDay();
            const dateStr = nextDate.toDateString();
            const dayName = dayNames[dayIndex];

            dayOffset++;

            // Skip if no availability set for this day
            const avail = window.providerAvailability ? window.providerAvailability[dayName] : null;
            if (!avail || !avail.start_time || !avail.end_time) continue;

            addedCount++;

            const slotDur = avail.slot_duration ? parseInt(avail.slot_duration) : 15;
            const duration = selectedTimeSlotsByDay[dateStr] ? selectedTimeSlotsByDay[dateStr].length * slotDur : 0;
            const formattedDate = nextDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });

            slotsList.innerHTML += `
                <div class="d-flex align-items-center justify-content-between p-3 mb-2 rounded-4 border bg-white cosy-slot-row-bg">
                    <div class="text-start">
                        <h6 class="fw-bold mb-1 cosy-slot-day-text">${dayNames[dayIndex]} (${formattedDate})</h6>
                        <p class="small text-muted mb-0 cosy-booking-for-another" id="duration-${dateStr}">${duration} minutes Call Duration</p>
                    </div>
                    <button onclick="openTimeSlotModal('${dateStr}')" class="btn btn-sm px-3 py-2 fw-bold text-white shadow-sm cosy-btn-select-time">
                        ${duration > 0 ? 'Edit Time' : 'Select Time'}
                    </button>
                </div>
            `;
        }
    }
}

function changeMonth(dir) {
    currentDate.setMonth(currentDate.getMonth() + dir);
    renderCalendar();
}

document.addEventListener('DOMContentLoaded', renderCalendar);

// ===== Modal Booking Logic =====
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
            date: dateStr
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
                    const isBooked = bookedSlots.includes(timeStr);

                    grid.innerHTML += `
                            <div class="time-block p-2 text-center small fw-bold ${isSelected ? 'selected' : ''} ${isBooked ? 'booked' : ''}" 
                                 onclick="${isBooked ? '' : `toggleTimeSlot('${timeStr}', this)`}">
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

// ===== Extra Utilities =====
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
            postReviewBtn.textContent = 'Posting...';

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
            const serviceCost = totalSlots * selectedService.price * weeks;
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