jQuery(document).ready(function ($) {

    // ---------------- Register Popup ----------------
    $('.openRegisterPopup').on('click', function (e) {
        e.preventDefault();
        $('#registerPopup').fadeIn(); // smoother than .show()
    });

    $('#closePopup').on('click', function () {
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


    // ---------------- AJAX Provider Filtering ----------------
    let filterTimeout;
    $('#cosyProvidersFilterForm input, #cosyProvidersFilterForm select').on('change keyup', function(e) {
        if (e.type === 'keyup' && e.key !== 'Enter') {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(triggerFilter, 500);
            return;
        }
        triggerFilter();
    });

    function triggerFilter() {
        $('#cosyProvidersGridWrap').css('opacity', '0.5');
        $.ajax({
            url: cosy_ajax.ajax_url,
            type: 'POST',
            data: $('#cosyProvidersFilterForm').serialize(),
            success: function(response) {
                if (response.success) {
                    $('#cosyProvidersGridWrap').html(response.data.html);
                }
                $('#cosyProvidersGridWrap').css('opacity', '1');
            },
            error: function() {
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
        $('#modalCustEndDate').text(end);
        $('#modalCustWeeklyBooking').text(weekly);
        $('#modalCustWeeks').text(weeks + ' week(s)');
        $('#modalCustWeekDays').text(weekDays);
        $('#modalCustSlots').text(slots + ' slot(s)');
        $('#modalCustSlotsTimeline').text(slotsTimeline);
        $('#modalCustCost').text('£' + cost);
        $('#modalCustFee').text('£' + fee);
        $('#modalCustTotal').text('£' + total);

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
});
