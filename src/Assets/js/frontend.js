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

    // ---------------- Profile Image Upload ----------------
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
        history.replaceState(null, null, target);   // URL me slug set karega
    });

    // Page load hone par check karo ki koi hash hai
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
    $('#cosyProvidersFilterForm input, #cosyProvidersFilterForm select').on('change keyup', function (e) {
        if ($(this).attr('id') === 'filter_paged') return;

        // Reset page to 1 whenever any filter input/select is modified
        $('#filter_paged').val('1');

        if (e.type === 'keyup' && e.key !== 'Enter') {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function () {
                triggerFilter(false);
            }, 500);
            return;
        }
        triggerFilter(false);
    });

    $(document).on('click', '.cosy-page-link', function (e) {
        e.preventDefault();
        const targetPage = $(this).data('page');
        if (!targetPage || $(this).prop('disabled') || $(this).parent().hasClass('disabled')) return;

        $('#filter_paged').val(targetPage);
        triggerFilter(true);
    });

    function triggerFilter(scrollToTop = false) {
        $('#cosyProvidersGridWrap').css('opacity', '0.5');
        $.ajax({
            url: cosy_ajax.ajax_url,
            type: 'POST',
            data: $('#cosyProvidersFilterForm').serialize(),
            success: function (response) {
                if (response.success) {
                    $('#cosyProvidersGridWrap').html(response.data.html);
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
        $('#modalCustServiceName').text(service);
        $('#modalCustWeeklyBooking').text(weekly);
        $('#modalCustDurationInfo').text(start + ' to ' + end);
        $('#modalCustWeeks').text(weeks + ' week(s) (' + slots + ' slots)');
        $('#modalCustWeekDays').text(weekDays);
        $('#modalCustSlotsTimeline').text(slotsTimeline);
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

// ============================================================
// GLOBAL DIRECT VIDEO MODAL CONTROLLER FOR PROVIDER INTRO VIDEOS
// ============================================================
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

