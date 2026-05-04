// For Register Popup
jQuery(document).ready(function ($) {
    // Menu link click → popup open
    $('.openRegisterPopup').on('click', function (e) {
        e.preventDefault();
        $('#registerPopup').show();
    });

    // Close button → popup hide
    $('#closePopup').on('click', function () {
        $('#registerPopup').hide();
    });
});

// Frontend JavaScript for Cosy Appointments Plugin
// (function ($) {
//     // Fetch slots from REST API
//     // console.log('cosyAppointments Nonce', cosyAppointments.nonce);

//     // $.getJSON(cosyAppointments.restUrl + 'slots', function (slots) {
//     //     var html = '<ul class="cosy-slot-list">';
//     //     $.each(slots, function (i, slot) {
//     //         html += '<li><button class="cosy-slot-btn" data-slot="' + slot.start + '-' + slot.end + '">' + slot.start + ' - ' + slot.end + '</button></li>';
//     //     });
//     //     html += '</ul>';
//     //     $('#cosy-slots').html(html);
//     // });

//     // Handle booking form
//     $('#cosy-book-form').on('submit', function (e) {
//         e.preventDefault();
//         var data = {
//             slot: $('#cosy-slot-input').val(),
//             name: $('input[name="fname"]').val()
//         };

//         $.ajax({
//             url: cosyAppointments.restUrl + 'book',
//             method: 'POST',
//             data: data,
//             beforeSend: function (xhr) { xhr.setRequestHeader('X-WP-Nonce', cosyAppointments.nonce); },
//             success: function (res) {
//                 console.log('Booked successfully! ID: ' + res.id);

//                 alert('Booked successfully! ID: ' + res.id);
//             },
//             error: function (err) {
//                 alert('Error booking slot');
//             }
//         });
//     });
// })(jQuery);

// Separate handler for slot selection to avoid conflicts
jQuery(document).on('click', '.cosy-slot-btn', function (e) {
    e.preventDefault();
    jQuery('.cosy-slot-btn').removeClass('selected');
    jQuery(this).addClass('selected');
    jQuery('#cosy-slot-input').val(jQuery(this).data('slot'));
});

// profile image upload preview
jQuery(document).ready(function ($) {

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
        $(".file-upload").click();
    });
});

// video upload with preview

jQuery(document).ready(function ($) {

    $('#video-dropzone').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation(); // 🔥 important
        $('#video-upload')[0].click(); // native click
    });

    $('#video-upload').on('change', function (e) {
        e.stopPropagation();

        const file = this.files[0];
        if (!file) return;

        const videoURL = URL.createObjectURL(file);
        $('#video-preview video').attr('src', videoURL);
        $('#video-preview').show();
    });

    $('#remove-video').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        $('#video-preview video').attr('src', '');
        $('#video-upload').val('');
        $('#video-preview').hide();
    });

});





// Service selection and dynamic table update
jQuery(document).ready(function ($) {
    // Checkbox change event
    $(".service-checkbox").on("change", function () {
        const value = $(this).val();
        const text = $(this).next("label").text();
        const tableBody = $("#servicesTable tbody");

        if ($(this).is(":checked")) {
            // Add row if checked
            const row = `
        <tr id="row-${value}">
          <td>${text}</td>
          <td><textarea class="form-control" name="service_desc[${value}]" rows="2"></textarea></td>
          <td>
            <select class="form-select" name="service_duration[${value}]">
              <option value="30">30 Minutes</option>
              <option value="60">1 Hour</option>
              <option value="90">1.5 Hours</option>
            </select>
          </td>
          <td><input type="number" class="form-control" name="service_price[${value}]" value=""></td>
          <td>
            <button type="button" class="btn btn-success btn-sm" title="Update">
              <i class="bi bi-check-circle"></i>
            </button>
            <button type="button" class="btn btn-danger btn-sm remove-service" data-service="${value}" title="Remove">
              <i class="bi bi-x-circle"></i>
            </button>
          </td>
        </tr>
      `;
            tableBody.append(row);
        } else {
            // Remove row if unchecked
            $("#row-" + value).remove();
        }

        updateSelectedCount();
    });

    // Remove button click event
    $(document).on("click", ".remove-service", function () {
        const service = $(this).data("service");
        $("#row-" + service).remove();
        $("input.service-checkbox[value='" + service + "']").prop("checked", false);
        updateSelectedCount();
    });

    // Update counter
    function updateSelectedCount() {
        const count = $("#servicesTable tbody tr").length;
        $("#selectedCount").html(count === 0 ? "No services selected yet." : `✅ ${count} service(s) selected.`);
    }

});
