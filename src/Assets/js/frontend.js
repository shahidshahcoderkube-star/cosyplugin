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
        $(".file-upload")[0].click(); // ✅ use native DOM click
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


});

jQuery(document).ready(function ($) {
    // Calendar Element ko select karein
    var calendarEl = $('#calendar')[0];

    // FullCalendar Initialize karein
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: ''
        },
        // Events Data
        events: [
            {
                title: 'Available',
                start: '2026-01-30',
                className: 'event-available'
            },
            {
                title: 'Available',
                start: '2026-01-31',
                className: 'event-available'
            },
            {
                title: 'Booked',
                start: '2026-01-28',
                className: 'event-booked'
            }
        ],
        // Date Click Event logic
        dateClick: function (info) {
            // jQuery alert ya logic yahan likhein
            alert('Aapne select kiya hai: ' + info.dateStr);

            // Example: Selected date ka background badalna (jQuery way)
            $('.fc-day').css('background-color', ''); // Reset pehle wale
            $(info.dayEl).css('background-color', '#f0f7ff');
        }
    });

    // Calendar render karein
    calendar.render();
});