// //----------------- AJAX Helper Function ----------------//
// function cosyAjax(action, userId, $btn, onSuccess, onError) {
//     jQuery.ajax({
//         url: ajaxurl,
//         type: "POST",
//         data: { action: action, user_id: userId },
//         beforeSend: function () {
//             if ($btn && $btn.length) {
//                 $btn.prop("disabled", true);
//                 $btn.data("original-text", $btn.html());
//                 $btn.html(`
//                     <span class="spinner-border spinner-border-sm me-2" role="status"></span>
//                     Loading...
//                 `);
//             }
//         },
//         success: function (res) {
//             if (res.success) {
//                 if (typeof onSuccess === "function") onSuccess(res);
//             } else {
//                 if (typeof onError === "function") onError(res);
//             }
//         },
//         complete: function () {
//             if ($btn && $btn.length) {
//                 $btn.prop("disabled", false);
//                 $btn.html($btn.data("original-text"));
//             }
//         }
//     });
// }

// //----------- Approve/Reject Media Handlers ----------------//
// jQuery(document).ready(function ($) {
//     // Approve
//     $(document).on('click', '.approve-media', function () {
//         // alert('clicked');
//         let row = $(this).closest('tr');
//         let $btn = $(this);
//         let userId = $btn.data('id');

//         cosyAjax("video_approve", userId, $btn, function (res) {
//             row.find('.status-badge')
//                 .removeClass('bg-warning text-dark')
//                 .addClass('bg-success')
//                 .text('Approved');

//             // Hide approve button
//             row.find('.approve-media').remove();
//         });
//     });

//     // Reject
//     $(document).on('click', '.reject-media', function () {
//         let row = $(this).closest('tr');
//         let $btn = $(this);
//         let userId = $btn.data('id');

//         cosyAjax("video_reject", userId, $btn, function (res) {
//             row.fadeOut(500, function () { $(this).remove(); });
//         });
//     });
// });


// jQuery(document).ready(function ($) {

//     // Approve
//     $(document).on('click', '.approve-media', function () {
//         let row = $(this).closest('tr');
//         let $btn = $(this);
//         let userId = $btn.data('id');

//         $.ajax({
//             url: ajaxurl,
//             type: "POST",
//             dataType: "json",
//             data: {
//                 action: "video_approve",
//                 user_id: userId
//             },
//             beforeSend: function () {
//                 $btn.prop("disabled", true).text("Approving...");
//             },
//             success: function (res) {
//                 if (res.success) {
//                     // ✅ Update status instantly
//                     row.find('td:nth-child(6)')
//                         .html('<span class="badge bg-success status-badge">Approved</span>');

//                     // ✅ Remove approve button
//                     row.find('.approve-media').remove();
//                 } else {
//                     alert(res.data.message || "Error approving");
//                 }
//             },
//             complete: function () {
//                 $btn.prop("disabled", false).text("Approve");
//             }
//         });
//     });

//     // Reject
//     $(document).on('click', '.reject-media', function () {
//         let row = $(this).closest('tr');
//         let $btn = $(this);
//         let userId = $btn.data('id');

//         $.ajax({
//             url: ajaxurl,
//             type: "POST",
//             dataType: "json",
//             data: {
//                 action: "video_reject",
//                 user_id: userId
//             },
//             beforeSend: function () {
//                 $btn.prop("disabled", true).text("Rejecting...");
//             },
//             success: function (res) {
//                 if (res.success) {
//                     // ✅ Option 1: Update status badge
//                     // row.find('td:nth-child(6)')
//                     //     .html('<span class="badge bg-danger status-badge">Rejected</span>');

//                     // ✅ Option 2: Remove row completely
//                     row.fadeOut(500, function () { $(this).remove(); });
//                 } else {
//                     alert(res.data.message || "Error rejecting");
//                 }
//             },
//             complete: function () {
//                 $btn.prop("disabled", false).text("Reject");
//             }
//         });
//     });

// });

jQuery(document).ready(function ($) {

    // Approve
    $(document).on('click', '.approve-media', function () {
        let row = $(this).closest('tr');
        let $btn = $(this);
        let userId = $btn.data('id');

        $.ajax({
            url: ajaxurl,
            type: "POST",
            dataType: "json",
            data: {
                action: "video_approve",
                user_id: userId,
                nonce: $('#cosy_media_nonce_field').val()
            },
            beforeSend: function () {
                $btn.prop("disabled", true).text("Approving...");
            },
            success: function (res) {
                if (res.success) {
                    row.find('td:nth-child(6)')
                        .html('<span class="badge bg-success status-badge">Approved</span>');
                    row.find('.approve-media').remove();

                    // ✅ Direct success message
                    $(".admin-succes").html(`
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            ${res.data.message || "Video approved successfully!"}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `);
                } else {
                    $(".admin-succes").html(`
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            ${res.data.message || "Error approving"}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `);
                }
            },
            complete: function () {
                $btn.prop("disabled", false).text("Approve");
            }
        });
    });

    // Reject
    $(document).on('click', '.reject-media', function () {
        let row = $(this).closest('tr');
        let $btn = $(this);
        let userId = $btn.data('id');

        $.ajax({
            url: ajaxurl,
            type: "POST",
            dataType: "json",
            data: {
                action: "video_reject",
                user_id: userId,
                nonce: $('#cosy_media_nonce_field').val()
            },
            beforeSend: function () {
                $btn.prop("disabled", true).text("Rejecting...");
            },
            success: function (res) {
                if (res.success) {
                    row.fadeOut(500, function () { $(this).remove(); });

                    // ✅ Direct success message
                    $(".admin-succes").html(`
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            ${res.data.message || "Video rejected successfully!"}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `);
                } else {
                    $(".admin-succes").html(`
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            ${res.data.message || "Error rejecting"}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `);
                }
            },
            complete: function () {
                $btn.prop("disabled", false).text("Reject");
            }
        });
    });

});
