/**
 * COSY APPOINTMENTS FRONTEND UI CONTROLLER
 * 
 * USE CASE:
 * Manages public-facing provider directory filters, registration popup, video modal, and customer reviews.
 * 
 * HOW TO USE:
 * Executed automatically on frontend page load across service-provider directory and profile templates.
 * 
 * WHAT IT DOES INTERNALLY:
 * 1. Syncs category dropdown with URL path slug.
 * 2. Fires AJAX filter queries for provider directory with smooth opacity transitions.
 * 3. Controls direct video modal player (`window.openVideo` / `window.closeVideo`).
 */
jQuery(document).ready(function ($) {

    // Sync category filter dropdown with URL path slug on page load
    let pathName = window.location.pathname.replace(/^\/|\/$/g, '');
    let segments = pathName.split('/');
    let lastSegment = segments[segments.length - 1];
    if (lastSegment && lastSegment !== 'service-provider' && $('#filter_category option[value="' + lastSegment + '"]').length) {
        $('#filter_category').val(lastSegment);
    }

    // ---------------- Register Popup ----------------
    $(document).on('click', '.openRegisterPopup', function (e) {
        e.preventDefault();
        $('#registerPopup').fadeIn(); // smoother than .show()
    });

    $(document).on('click', '#closePopup', function () {
        $('#registerPopup').fadeOut(); // smoother than .hide()
    });

    // ---------------- Slot Selection ----------------
    $(document).on('click', '.cosy-slot-btn', function (e) {
        e.preventDefault();
        $('.cosy-slot-btn').removeClass('selected');
        $(this).addClass('selected');
        $('#cosy-slot-input').val($(this).data('slot'));
    });

    /**
     * READ URL IMAGE PREVIEW
     * 
     * USE CASE: Previews local file selection for profile avatar uploads.
     * HOW TO USE: Triggered on change event of .file-upload input.
     * WHAT IT DOES INTERNALLY: Reads selected File object using FileReader and sets .profile-pic src.
     */
    var readURL = function (input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('.profile-pic').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    $(".file-upload").on('change', function () {
        readURL(this);
    });

    $(".upload-button").on('click', function () {
        $(".file-upload")[0].click(); // use native DOM click
    });


    //---------------- Tab Navigation with URL Hash ----------------
    $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function (e) {
        let target = $(e.target).data('bs-target'); // e.g. "#video"
        history.replaceState(null, null, target);   // Update URL hash anchor
    });

    // Check on page load if URL contains active tab hash anchor
    let hash = window.location.hash;
    if (hash) {
        let tabTrigger = document.querySelector('[data-bs-target="' + hash + '"]');
        if (tabTrigger) {
            let tab = new bootstrap.Tab(tabTrigger);
            tab.show();
        }
    }


    // ---------------- AJAX Provider Filtering & Pagination ----------------
    let filterTimeout;

    // Detect if page was loaded via BROWSER BACK / FORWARD button vs FRESH RELOAD / REFRESH
    const navEntries = (window.performance && window.performance.getEntriesByType) ? window.performance.getEntriesByType('navigation') : [];
    const isBackNav = (navEntries.length > 0 && navEntries[0].type === 'back_forward') || 
                      (window.performance && window.performance.navigation && window.performance.navigation.type === 2);

    if (isBackNav) {
        // Restore saved directory search state ONLY if returning via Back button
        try {
            const savedHtml = sessionStorage.getItem('cosy_dir_search_html');
            if (savedHtml && $('#cosyProvidersGridWrap').length) {
                const savedInput = sessionStorage.getItem('cosy_dir_search_input');
                const savedCat = sessionStorage.getItem('cosy_dir_search_category');
                const savedPrice = sessionStorage.getItem('cosy_dir_search_price');
                const savedGender = sessionStorage.getItem('cosy_dir_search_gender');
                const savedAge = sessionStorage.getItem('cosy_dir_search_age');
                const savedRating = sessionStorage.getItem('cosy_dir_search_rating');
                const savedPaged = sessionStorage.getItem('cosy_dir_search_paged');

                if (savedInput !== null && $('#filter_search_name').length) $('#filter_search_name').val(savedInput);
                if (savedCat !== null && savedCat !== '' && $('#filter_category').length) $('#filter_category').val(savedCat);
                if (savedPrice !== null && $('#filter_price').length) $('#filter_price').val(savedPrice);
                if (savedGender !== null && $('#filter_gender').length) $('#filter_gender').val(savedGender);
                if (savedAge !== null && $('#filter_age').length) $('#filter_age').val(savedAge);
                if (savedRating !== null && $('#filter_rating').length) $('#filter_rating').val(savedRating);
                if (savedPaged !== null && $('#filter_paged').length) $('#filter_paged').val(savedPaged);

                if (savedInput || savedCat || savedPrice || savedGender || savedAge || savedRating || (savedPaged && savedPaged !== '1')) {
                    $('#cosyProvidersGridWrap').html(savedHtml);
                }
            }
        } catch (e) {}
    } else {
        // Clear saved directory search state on fresh page load or manual refresh (F5)
        try {
            sessionStorage.removeItem('cosy_dir_search_html');
            sessionStorage.removeItem('cosy_dir_search_input');
            sessionStorage.removeItem('cosy_dir_search_category');
            sessionStorage.removeItem('cosy_dir_search_price');
            sessionStorage.removeItem('cosy_dir_search_gender');
            sessionStorage.removeItem('cosy_dir_search_age');
            sessionStorage.removeItem('cosy_dir_search_rating');
            sessionStorage.removeItem('cosy_dir_search_paged');
        } catch (e) {}
    }

    // Prevent form submission on enter press
    $('#cosyProvidersFilterForm').on('submit', function (e) {
        e.preventDefault();
    });

    // Select dropdown filters trigger AJAX query immediately on change
    $('#cosyProvidersFilterForm select').on('change', function () {
        $('#filter_paged').val('1');
        triggerFilter(false);
    });

    // Search input (#filter_search_name) triggers AJAX query with 300ms debounce
    $('#filter_search_name').on('input keyup', function (e) {
        $('#filter_paged').val('1');

        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(filterTimeout);
            triggerFilter(false);
            return;
        }

        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function () {
            triggerFilter(false);
        }, 300);
    });

    $(document).on('click', '.cosy-page-link', function (e) {
        e.preventDefault();
        const targetPage = $(this).data('page');
        if (!targetPage || $(this).prop('disabled') || $(this).parent().hasClass('disabled')) return;

        $('#filter_paged').val(targetPage);
        triggerFilter(true);
    });

    /**
     * TRIGGER PROVIDER FILTER AJAX
     * 
     * USE CASE: Executes AJAX search/filter query for provider directory.
     * HOW TO USE: Triggered on filter input change, keyup, or pagination button click.
     * WHAT IT DOES INTERNALLY: Serializes form data, posts to cosy_ajax.ajax_url, and updates grid HTML.
     */
    function triggerFilter(scrollToTop = false) {
        $('#cosyProvidersGridWrap').css('opacity', '0.5');
        $.ajax({
            url: cosy_ajax.ajax_url,
            type: 'POST',
            data: $('#cosyProvidersFilterForm').serialize(),
            success: function (response) {
                if (response.success) {
                    $('#cosyProvidersGridWrap').html(response.data.html);

                    // Save directory search state in sessionStorage for Back button navigation
                    try {
                        sessionStorage.setItem('cosy_dir_search_input', $('#filter_search_name').val() || '');
                        sessionStorage.setItem('cosy_dir_search_category', $('#filter_category').val() || '');
                        sessionStorage.setItem('cosy_dir_search_price', $('#filter_price').val() || '');
                        sessionStorage.setItem('cosy_dir_search_gender', $('#filter_gender').val() || '');
                        sessionStorage.setItem('cosy_dir_search_age', $('#filter_age').val() || '');
                        sessionStorage.setItem('cosy_dir_search_rating', $('#filter_rating').val() || '');
                        sessionStorage.setItem('cosy_dir_search_paged', $('#filter_paged').val() || '1');
                        sessionStorage.setItem('cosy_dir_search_html', response.data.html);
                    } catch (e) {}

                    if (scrollToTop) {
                        $('html, body').animate({
                            scrollTop: $('#cosyProvidersGridWrap').offset().top - 100
                        }, 300);
                    }
                }
                $('#cosyProvidersGridWrap').css('opacity', '1');
            },
            error: function () {
                $('#cosyProvidersGridWrap').css('opacity', '1');
            }
        });
    }

    // ---------------- Toggle Password Visibility ----------------
    $(document).on('click', '.cosy-toggle-password', function () {
        const passwordInput = $(this).siblings('input');
        const icon = $(this).find('i');

        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordInput.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // ---------------- Customer Order Details Modal ----------------
    $(document).on('click', '.btn-view-customer-order-details', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        var service = $(this).data('service');
        var provider = $(this).data('provider');
        var weekly = $(this).data('weekly');
        var total = $(this).data('total');
        var cost = $(this).data('cost');
        var fee = $(this).data('fee');
        var start = $(this).data('start');
        var end = $(this).data('end');
        var weeks = $(this).data('weeks');
        var slots = $(this).data('slots');
        var weekDays = $(this).data('week-days') || '';
        var slotsTimeline = $(this).data('slots-timeline') || '';
        var status = $(this).attr('data-status');

        $('#customerOrderDetailsModalLabel').text('Order Details - #' + id);
        $('#modalCustProviderName').text(provider);
        $('#modalCustStartDate').text(start);
        $('#modalCustWeeks').text(weeks + (parseInt(weeks) === 1 ? ' week' : ' weeks'));
        $('#modalCustSlotsTimeline').html(slotsTimeline);
        var sym = (typeof cosyAppointments !== 'undefined' && cosyAppointments.currencySymbol) ? cosyAppointments.currencySymbol : '£';
        $('#modalCustCost').text(sym + cost);
        $('#modalCustFee').text(sym + fee);
        $('#modalCustTotal').text(sym + total);

        var statusBg = '';
        var statusText = '';
        if (status === 'completed') {
            statusBg = 'rgba(34, 197, 94, 0.1)';
            statusText = 'Completed';
            $('#modalCustStatusText').css('color', '#22c55e');
        } else if (status === 'cancelled') {
            statusBg = 'rgba(239, 68, 68, 0.1)';
            statusText = 'Cancelled';
            $('#modalCustStatusText').css('color', '#ef4444');
        } else {
            statusBg = 'rgba(245, 158, 11, 0.1)';
            statusText = 'Pending';
            $('#modalCustStatusText').css('color', '#f59e0b');
        }
        $('#modalCustStatusBg').css('background-color', statusBg);
        $('#modalCustStatusText').text(statusText);
    });

    // ---------------- Provider Profile Reviews & Replies ----------------
    $(document).on('click', '#btnLoadMoreReviews', function () {
        var $btn = $(this);
        var $wrapper = $('.cosy-extra-reviews-wrapper');
        var total = $btn.data('total') || 1;

        if ($wrapper.is(':visible')) {
            $wrapper.slideUp(300, function () {
                $wrapper.attr('style', 'display: none !important;');
            });
            $btn.find('.btn-text').text('View Reviews (' + total + ')');
            $btn.find('.btn-icon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        } else {
            $wrapper.attr('style', 'display: flex !important;').hide().slideDown(300);
            $btn.find('.btn-text').text('Hide Reviews');
            $btn.find('.btn-icon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }
    });

    $(document).on('click', '.btn-toggle-cust-reply', function () {
        var revId = $(this).data('review-id');
        $('#cust-reply-form-' + revId).slideToggle(200);
    });

    $(document).on('click', '.btn-cancel-cust-reply', function () {
        var revId = $(this).data('review-id');
        $('#cust-reply-form-' + revId).slideUp(200);
    });

    $(document).on('submit', '.cosy-customer-reply-form', function (e) {
        e.preventDefault();
        var form = $(this);
        var revId = form.data('review-id');
        var replyText = form.find('.cust-reply-text').val().trim();
        var submitBtn = form.find('button[type="submit"]');

        if (!replyText) return;

        submitBtn.prop('disabled', true).css({ 'color': '#ffffff', 'opacity': '0.95' }).html('<i class="fas fa-spinner fa-spin me-1" style="color: #ffffff !important;"></i> <span style="color: #ffffff !important;">Posting...</span>');

        var ajaxUrl = (typeof cosy_ajax !== 'undefined' && cosy_ajax.ajax_url) ? cosy_ajax.ajax_url : (window.ajaxUrl || '/wp-admin/admin-ajax.php');

        $.post(ajaxUrl, {
            action: 'cosy_customer_reply_review',
            review_id: revId,
            reply_text: replyText
        }, function (res) {
            if (res.success) {
                location.reload();
            } else {
                alert(res.data.message || 'Error posting follow-up reply.');
                submitBtn.prop('disabled', false).html('Submit Follow-up');
            }
        }).fail(function () {
            alert('Server error posting follow-up reply. Please try again.');
            submitBtn.prop('disabled', false).html('Submit Follow-up');
        });
    });
});

/**
 * OPEN PROVIDER VIDEO POPUP
 * 
 * USE CASE: Plays provider introduction video in a direct modal popup.
 * HOW TO USE: Called via window.openVideo('video-url.mp4').
 * WHAT IT DOES INTERNALLY: Appends video modal to body, sets video src, and initiates playback.
 */
window.openVideo = function (url) {
    if (!url) return;

    let modal = document.getElementById('videoModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'videoModal';
        modal.className = 'modal';
        modal.onclick = function () { window.closeVideo(); };
        modal.innerHTML = `
            <div class="cosy-video-modal-content-v2" onclick="event.stopPropagation()">
                <span class="close-modal" onclick="window.closeVideo()">&times;</span>
                <video id="videoPlayer" controls width="100%" height="100%" src="" style="width:100%; height:100%; object-fit:contain; border-radius:20px; outline:none; background:#000;"></video>
            </div>
        `;
        document.body.appendChild(modal);
    } else if (modal.parentNode !== document.body) {
        document.body.appendChild(modal);
    }

    const videoPlayer = document.getElementById('videoPlayer');
    if (!videoPlayer) return;

    videoPlayer.src = url;
    videoPlayer.style.display = 'block';
    videoPlayer.play().catch(function(e) {
        console.log("Autoplay was prevented:", e);
    });

    modal.style.display = 'flex';
};

/**
 * CLOSE PROVIDER VIDEO POPUP
 * 
 * USE CASE: Closes video modal popup and stops video audio/video playback.
 * HOW TO USE: Called via window.closeVideo().
 * WHAT IT DOES INTERNALLY: Pauses video player element and resets src to empty string.
 */
window.closeVideo = function () {
    const modal = document.getElementById('videoModal');
    if (modal) {
        modal.style.display = 'none';
    }
    const videoPlayer = document.getElementById('videoPlayer');
    if (videoPlayer) {
        videoPlayer.pause();
        videoPlayer.src = '';
    }
};

