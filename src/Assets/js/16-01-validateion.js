//-------------------------------------------------------------//
//----------------- Registration & Login Forms ----------------//
//-------------------------------------------------------------//
jQuery(document).ready(function ($) {
    $(".cosy-form").each(function () {
        let form = $(this);
        let action = form.data("action");
        let $btn = form.find('button[type="submit"]');
        // console.log('action', action);

        // ✅ Validation + submit only
        form.validate({
            rules: {
                cust_name: { required: true, minlength: 3 },
                cust_email: { required: true, email: true },
                cust_pass: { required: true, minlength: 6 },
                prov_name: { required: true, minlength: 3 },
                prov_email: { required: true, email: true },
                prov_pass: { required: true, minlength: 6 },
                terms: { required: true }
            },
            messages: {
                cust_name: { required: "Please enter your name", minlength: "At least 3 characters" },
                cust_email: { required: "Please enter your email", email: "Enter a valid email" },
                cust_pass: { required: "Please provide a password", minlength: "At least 6 characters" },
                prov_name: { required: "Please enter provider name", minlength: "At least 3 characters" },
                prov_email: { required: "Please enter provider email", email: "Enter a valid email" },
                prov_pass: { required: "Please provide a password", minlength: "At least 6 characters" },
                terms: { required: "You must agree to Terms" }
            },
            errorClass: "cosy-error",
            errorElement: "span",
            highlight: function (element) { $(element).addClass("cosy-invalid"); },
            unhighlight: function (element) { $(element).removeClass("cosy-invalid"); },
            submitHandler: function (formEl) {
                // console.log('formEl', formEl);

                $.ajax({
                    url: cosy_ajax.ajax_url,
                    type: "POST",
                    data: $(formEl).serialize() + "&action=" + action,
                    // 🔄 AJAX START
                    beforeSend: function () {
                        $btn.prop("disabled", true);

                        // original text save
                        $btn.data("original-text", $btn.html());

                        // bootstrap spinner add
                        $btn.html(`<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Loading...`);
                    },

                    success: function (response) {
                        // console.log(response);

                        let msgBox = $(formEl).find(".cosy-message");

                        if (response.success) {
                            // login redirection and others
                            if (response.data.startsWith("http")) {
                                window.location.href = response.data;
                                return;
                            }
                            // else {
                            // 	msgBox.html('<div class="alert alert-success">' + response.data + '</div>');
                            // }

                            msgBox.html(`
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    ${response.data}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `);
                            formEl.reset();
                        } else {
                            msgBox.html(`
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    ${response.data}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `);
                        }
                    },
                    error: function () {
                        $(formEl).find(".cosy-message").html(`
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Something went wrong. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);
                    },
                    complete: function () {
                        $btn.prop("disabled", false);
                        $btn.html($btn.data("original-text"));
                    }
                });

            }
        });
    });
});


//------------------------------------------------------//
//----------------- Profile Update Form ----------------//
//------------------------------------------------------//
jQuery(document).ready(function ($) {

    let form = $(".cosy-form-update");
    if (!form.length) return;

    let action = form.data("action");

    form.validate({
        rules: {
            prov_name: { required: true, minlength: 3 },
            prov_email: { required: true, email: true }
        },
        messages: {
            prov_name: { required: "Please enter provider name", minlength: "At least 3 characters" },
            prov_email: { required: "Please enter provider email", email: "Enter a valid email" }
        },
        errorClass: "cosy-error",
        errorElement: "span",
        highlight: function (element) {
            $(element).addClass("cosy-invalid");
        },
        unhighlight: function (element) {
            $(element).removeClass("cosy-invalid");
        },

        submitHandler: function (formEl) {

            let $form = $(formEl);
            let $btn = $form.find('button[type="submit"]');

            // ✅ IMPORTANT: FormData
            let formData = new FormData(formEl);
            formData.append("action", action);

            $.ajax({
                url: cosy_ajax.ajax_url,
                type: "POST",
                data: formData,

                // ✅ REQUIRED for file upload
                processData: false,
                contentType: false,

                beforeSend: function () {
                    $btn.prop("disabled", true);
                    $btn.data("original-text", $btn.html());
                    $btn.html(`
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                        Loading...
                    `);
                },

                success: function (response) {
                    console.log("response_update", response);

                    let msgBox = $form.find(".cosy-message");

                    if (response.success) {

                        msgBox.html(`
                            <div class="alert alert-success alert-dismissible fade show">
                                ${response.data.message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `);

                        let d = response.data.data;

                        $('[name="prov_username"]').val(d.prov_username);
                        $('[name="prov_fname"]').val(d.prov_fname);
                        $('[name="prov_mname"]').val(d.prov_mname);
                        $('[name="prov_sname"]').val(d.prov_sname);
                        $('[name="prov_email"]').val(d.prov_email);
                        $('[name="prov_phone"]').val(d.prov_phone);
                        $('[name="prov_address"]').val(d.prov_address);
                        $('[name="dob"]').val(d.dob);
                        $('[name="postal_code"]').val(d.postal_code);
                        $('[name="bio"]').val(d.bio);
                        $('[name="gender"]').val(d.gender);
                        $('[name="age_group"]').val(d.age_group);

                        // ✅ Profile image update
                        if (d.profile_image) {
                            $(".profile-pic").attr("src", d.profile_image);
                        }

                    } else {
                        msgBox.html(`
                            <div class="alert alert-danger alert-dismissible fade show">
                                ${response.data.message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `);
                    }
                },

                error: function () {
                    $form.find(".cosy-message").html(`
                        <div class="alert alert-danger">
                            Something went wrong. Please try again.
                        </div>
                    `);
                },

                complete: function () {
                    $btn.prop("disabled", false);
                    $btn.html($btn.data("original-text"));
                }
            });
        }
    });

});
// --------------------------------end------------------//
//----------------------------------------------------//
//----------------- Video Upload Form ----------------//
//----------------------------------------------------//
// jQuery(document).ready(function ($) {
//     let form = $(".cosy_form_video"); // video upload form
//     form.validate({
//         submitHandler: function (formEl) {
//             let $form = $(formEl);
//             let action = $form.data("action");
//             let msgBox = $form.find(".cosy-message");
//             let $btn = $form.find('button[type="submit"]');

//             // ✅ IMPORTANT: FormData
//             let formData = new FormData(formEl);

//             formData.append("action", action);

//             jQuery.ajax({
//                 url: cosy_ajax.ajax_url,
//                 type: 'POST',
//                 data: formData,
//                 processData: false,
//                 contentType: false,
//                 beforeSend: function () {
//                     $btn.prop("disabled", true);
//                     $btn.data("original-text", $btn.html());
//                     $btn.html(`
//                         <span class="spinner-border spinner-border-sm me-2" role="status"></span>
//                         Loading...
//                     `);
//                 },
//                 success: function (response) {
//                     if (response.success) {
//                         msgBox.html(`
//                     <div class="alert alert-success alert-dismissible fade show">
//                         ${response.data.message}
//                         <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
//                     </div>
//                 `);
//                     } else {
//                         msgBox.html(`
//                     <div class="alert alert-danger alert-dismissible fade show">
//                         ${response.data.message}
//                         <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
//                     </div>
//                 `);
//                     }
//                 },

//                 error: function () {
//                     msgBox.html(`
//                 <div class="alert alert-danger alert-dismissible fade show">
//                     Something went wrong. Please try again.
//                     <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
//                 </div>
//             `);
//                 },
//                 complete: function () {
//                     $btn.prop("disabled", false);
//                     $btn.html($btn.data("original-text"));
//                 }
//             });

//         }
//     });
// });



//------------------------------------------------------//
//-----------------  Service Provider Tab Selection ----------------//
//------------------------------------------------------//


//--start--//
jQuery(document).ready(function ($) {

    // $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function (e) {
    $(document).on('shown.bs.tab', '.cosy-tab', function (e) {
        let tabSlug = $(e.target).data('tab');
        let targetBox = $(e.target).data('bs-target');

        $.ajax({
            url: cosy_ajax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'load_dashboard_tab',
                tab: tabSlug,
                // nonce: cosy_ajax.nonce
            },
            beforeSend: function () {
                $(targetBox).html(`
                    <div class="p-4 text-center cosy__loder">
                        <span class="spinner-border"></span>
                    </div>
                `);
            },
            success: function (res) {
                if (res.success) {
                    $(targetBox).html(res.data.html);
                } else {
                    $(targetBox).html(`<div class="alert alert-danger">${res.data}</div>`);
                }
            },
            error: function () {
                $(targetBox).html('<div class="alert alert-danger">AJAX error</div>');
            }
        });
    });

    // auto load first tab
    $('button[data-bs-toggle="pill"].active').trigger('shown.bs.tab');
});


jQuery(document).ready(function ($) {

    /* =========================================
       1️⃣ Dropzone → Open File Picker
    ========================================= */
    $(document).on("click", "#video-dropzone", function () {
        $("#video-upload")[0].click();
    });


    /* =========================================
       2️⃣ Preview on File Select
    ========================================= */
    $(document).on("change", "#video-upload", function () {

        const file = this.files[0];
        if (!file || !file.type.startsWith("video/")) {
            alert("Please upload a valid video file");
            this.value = "";
            return;
        }

        const videoURL = URL.createObjectURL(file);

        const $preview = $("#video-upload-preview");
        const $video = $preview.find("video");
        const $source = $video.find("source");

        $source.attr("src", videoURL);
        $video[0].load();

        $preview.fadeIn();
        $("#video-dropzone").hide();
    });


    /* =========================================
       3️⃣ Upload Video (AJAX)
    ========================================= */
    $(document).on("submit", "#video-upload-form", function (e) {
        e.preventDefault();

        const $form = $(this);
        const $btn = $form.find("button[type='submit']");
        const msgBox = $form.find(".cosy-message");

        const formData = new FormData(this);
        formData.append("action", $form.data("action"));

        $.ajax({
            url: cosy_ajax.ajax_url,
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,

            beforeSend: function () {
                $btn.prop("disabled", true)
                    .html(`<span class="spinner-border spinner-border-sm"></span> Uploading...`);
            },

            success: function (res) {
                // console.log(res);

                if (res.success) {
                    msgBox.html(` <div class="alert alert-success alert-dismissible fade show">${res.data.message} <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`);
                    // 🔁 simplest & safest (PHP state handle karega)
                    location.reload();

                } else {
                    msgBox.html(`<div class="alert alert-danger">${res.data.message}</div>`);
                }
            },

            complete: function () {
                $btn.prop("disabled", false).html("Save Video");
            }
        });
    });


    /* =========================================
       4️⃣ Delete Video (AJAX)
    ========================================= */
    $(document).on("click", "#remove-video", function () {

        if (!confirm("Do you really want to delete this video?")) return;

        const $btn = $(this);

        $.ajax({
            url: cosy_ajax.ajax_url,
            type: "POST",
            data: {
                action: $btn.data("action"),
                user_id: $btn.data("id")
            },

            beforeSend: function () {
                $btn.prop("disabled", true)
                    .html(`<span class="spinner-border spinner-border-sm"></span>`);
            },

            success: function (res) {
                if (res.success) {

                    // 🔁 reload so PHP shows upload form again
                    location.reload();

                } else {
                    alert(res.data.message);
                }
            }
        });
    });

});



