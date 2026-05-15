/**
 * CosyApp JavaScript Module
 * 
 * This file handles all frontend logic for the Cosy Appointments plugin.
 * It includes form validation, AJAX submissions, real-time UI updates,
 * and SweetAlert2 notifications.
 */
var CosyApp = (function ($) {

    //--------------- ALERTS ---------------//
    function cosyAlert(type, message) {
        // If message is an object, try to get .message property or stringify it
        if (typeof message === "object" && message !== null) {
            message = message.message || JSON.stringify(message);
        }
        return `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close"></button>
            </div>
        `;
    }


    //--------------- MANUAL SUBMIT FALLBACK ---------------//
    function handleManualSubmit(formEl, action, $btn) {
        let $form = $(formEl);
        $.ajax({
            url: cosy_ajax.ajax_url,
            type: "POST",
            data: $form.serialize() + "&action=" + action,
            beforeSend() {
                $btn.prop("disabled", true).html("Loading...");
            },
            success(response) {
                let msgBox = $form.find(".cosy-message");
                if (response.success) {
                    if (typeof response.data === "string" && response.data.startsWith("http")) {
                        window.location.href = response.data;
                    } else {
                        msgBox.html(cosyAlert("success", response.data));
                        formEl.reset();
                    }
                } else {
                    msgBox.html(cosyAlert("danger", response.data));
                }
            },
            complete() {
                $btn.prop("disabled", false).html("Submit");
            }
        });
    }


    /**
     * initAuthForms
     * 
     * Handles validation and AJAX submission for Login and Registration forms.
     * It uses JQuery Validate to check fields before sending data to the server.
     */
    function initAuthForms(container = document) {

        $(container).find(".cosy-form").each(function () {

            let $form = $(this);
            if ($form.data("bound")) return;
            $form.data("bound", true);

            let action = $form.data("action");
            let $btn = $form.find('button[type="submit"]');

            if (typeof $.fn.validate === "undefined") {
                console.warn("JQuery Validate not loaded. Using fallback submit.");
                $form.on("submit", function (e) {
                    e.preventDefault();
                    // Call a manual submit if validate is missing
                    handleManualSubmit(this, action, $btn);
                });
                return;
            }

            $form.validate({
                rules: {
                    cust_name: { required: true, minlength: 3 },
                    cust_email: { required: true, email: true },
                    cust_pass: { required: true, minlength: 6 },
                    prov_name: { required: true, minlength: 3 },
                    prov_email: { required: true, email: true },
                    prov_pass: { required: true, minlength: 6 },
                    terms: { required: true }
                },
                errorClass: "cosy-error",
                errorElement: "span",

                submitHandler: function (formEl) {

                    $.ajax({
                        url: cosy_ajax.ajax_url,
                        type: "POST",
                        data: $(formEl).serialize() + "&action=" + action,

                        beforeSend() {
                            $btn.prop("disabled", true);
                            $btn.data("original-html", $btn.html());
                            $btn.html(`<span class="spinner-border spinner-border-sm me-2"></span> Loading...`);
                        },

                        success(response) {

                            let msgBox = $form.find(".cosy-message");

                            if (response.success) {

                                if (typeof response.data === "string" && response.data.startsWith("http")) {
                                    window.location.href = response.data;
                                    return;
                                }

                                msgBox.html(cosyAlert("success", response.data));
                                formEl.reset();

                            } else {
                                msgBox.html(cosyAlert("danger", response.data));
                            }
                        },

                        complete() {
                            $btn.prop("disabled", false)
                                .html($btn.data("original-html"));
                        }
                    });
                }
            });
        });
    }


    /**
     * initProfileUpdate
     * 
     * Handles the AJAX submission for the Provider Profile Information form.
     * It sends text data and the profile image to the backend update handler.
     */
    function initProfileUpdate(container = document) {

        let $form = $(container).find(".cosy-form-update");
        if (!$form.length || $form.data("bound")) return;
        $form.data("bound", true);

        let action = $form.data("action");

        $form.validate({
            rules: {
                prov_name: { required: true, minlength: 3 },
                prov_email: { required: true, email: true }
            },

            submitHandler: function (formEl) {

                let $btn = $form.find("button[type='submit']");
                let formData = new FormData(formEl);
                formData.append("action", action);
                formData.append("nonce", $("#cosy_dashboard_nonce_field").val());

                $.ajax({
                    url: cosy_ajax.ajax_url,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,

                    beforeSend() {
                        $btn.prop("disabled", true);
                        $btn.data("original-html", $btn.html());
                        $btn.html(`<span class="spinner-border spinner-border-sm me-2"></span> Loading...`);
                    },

                    success(response) {

                        let msgBox = $form.find(".cosy-message");

                        if (response.success) {
                            msgBox.html(cosyAlert("success", response.data.message));
                        } else {
                            msgBox.html(cosyAlert("danger", response.data.message));
                        }
                    },

                    complete() {
                        $btn.prop("disabled", false)
                            .html($btn.data("original-html"));
                    }
                });
            }
        });
    }


    /**
     * initVideoUpload
     * 
     * Handles introduction video uploads and deletions.
     * Features:
     * 1. Real-time video preview before uploading.
     * 2. AJAX upload with progress feedback.
     * 3. SweetAlert2 confirmation for deletions.
     */
    function initVideoUpload() {

        $(document)
            .off("click.cosyVideo", ".video-dropzone")
            .off("change.cosyVideo", ".video-upload")
            .off("submit.cosyVideo", ".video-upload-form")
            .off("click.cosyVideo", ".remove-video");

        // Open file picker
        $(document).on("click.cosyVideo", ".video-dropzone", function () {
            let input = $(this).closest("form").find(".video-upload")[0];
            if (input) input.click();
        });

        // Preview on file select 
        $(document).on("change.cosyVideo", ".video-upload", function () {

            const file = this.files[0];
            if (!file || !file.type.startsWith("video/")) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid File',
                    text: 'Please upload a valid video file.',
                    confirmButtonColor: '#a44390',
                    showClass: { popup: '' },
                    hideClass: { popup: '' }
                });
                this.value = "";
                return;
            }

            const url = URL.createObjectURL(file);
            const $form = $(this).closest("form");
            const $preview = $form.find(".video-upload-preview");

            $preview.find("source").attr("src", url);
            $preview.find("video")[0].load();
            $preview.fadeIn();
            $form.find(".video-dropzone").hide();
        });

        //------------- Submit form with video --------------//
        $(document).on("submit.cosyVideo", ".video-upload-form", function (e) {
            e.preventDefault();

            const $form = $(this);
            const $btn = $form.find("button[type='submit']");
            const msgBox = $form.find(".cosy-message");

            const formData = new FormData(this);
            formData.append("action", $form.data("action"));
            formData.append("nonce", $("#cosy_dashboard_nonce_field").val());

            $.ajax({
                url: cosy_ajax.ajax_url,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,

                beforeSend() {
                    $btn.prop("disabled", true)
                        .html(`<span class="spinner-border spinner-border-sm"></span> Uploading...`);
                },

                success(res) {
                    if (res.success) {
                        msgBox.html(cosyAlert("success", res.data.message));
                        location.reload();
                    } else {
                        msgBox.html(cosyAlert("danger", res.data.message));
                    }
                },

                complete() {
                    $btn.prop("disabled", false).html("Save Video");
                }
            });
        });

        // Delete
        $(document).on("click.cosyVideo", ".remove-video", function () {
            const $btn = $(this);
            const videoId = $btn.data("id");
            const $container = $btn.closest(".video-item, .video-preview-card");

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you really want to delete this video?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#22c55e',
                confirmButtonText: 'Yes, delete it!',
                showClass: { popup: '' },
                hideClass: { popup: '' }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: cosy_ajax.ajax_url,
                        type: "POST",
                        data: {
                            action: "cosy_delete_provider_video",
                            video_id: videoId,
                            nonce: $("#cosy_dashboard_nonce_field").val()
                        },
                        beforeSend() {
                            $btn.prop("disabled", true).html(`<span class="spinner-border spinner-border-sm"></span>`);
                        },
                        success(res) {
                            if (res.success) {
                                $container.fadeOut(300, function () { $(this).remove(); });
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: 'Video has been deleted.',
                                    timer: 1500,
                                    showConfirmButton: false,
                                    showClass: { popup: '' },
                                    hideClass: { popup: '' }
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: res.data || "Could not delete video.",
                                    confirmButtonColor: '#a44390',
                                    showClass: { popup: '' },
                                    hideClass: { popup: '' }
                                });
                            }
                        },
                        complete() {
                            $btn.prop("disabled", false).html('<i class="fas fa-trash"></i>');
                        }
                    });
                }
            });
        });
    }


    /**
     * initTabs
     * 
     * Handles dynamic loading of Dashboard tabs via AJAX.
     * When a user clicks a tab (Profile, Services, etc.), the content is fetched from the server
     * and injected into the page without a full reload.
     */
    function initTabs() {

        $("#cosyDashboardTabs").on("shown.bs.tab", ".cosy-tab", function (e) {

            let tabSlug = $(e.target).data("tab");
            let targetBox = $(e.target).data("bs-target");

            $.ajax({
                url: cosy_ajax.ajax_url,
                type: "POST",
                dataType: "json",
                data: {
                    action: "load_dashboard_tab",
                    tab: tabSlug,
                    nonce: $("#cosy_dashboard_nonce_field").val()
                },

                beforeSend() {
                    $(targetBox).html(
                        `<div class="p-4 text-center"><span class="spinner-border"></span></div>`
                    );
                },

                success(res) {
                    if (res.success) {
                        $(targetBox).html(res.data.html);
                        initAuthForms(targetBox);
                        initProfileUpdate(targetBox);
                        if (tabSlug === "services") {
                            serviceSelection();
                        }
                    } else {
                        $(targetBox).html(cosyAlert("danger", res.data));
                    }
                }
            });
        });

        $(".cosy-tab.active").trigger("shown.bs.tab");
    }


    // -------- Helper: Build Service Row -------- //
    function buildServiceRow(item, serviceId, slug, serviceTitle) {
        return `
        <tr id="row-${serviceId}">
            <td><span class="service-title">${item?.service ?? serviceTitle}</span></td>
            <td><textarea class="form-control" name="service_desc[${serviceId}]">${item?.description ?? ''}</textarea></td>
            <td>
                <select class="form-select" name="service_duration[${serviceId}]">
                    <option value="10" ${item?.duration == 10 ? 'selected' : ''}>10 Minutes</option>
                    <option value="20" ${item?.duration == 20 ? 'selected' : ''}>20 Minutes</option>
                    <option value="30" ${item?.duration == 30 ? 'selected' : ''}>30 Minutes</option>
                    <option value="40" ${item?.duration == 40 ? 'selected' : ''}>40 Minutes</option>
                    <option value="50" ${item?.duration == 50 ? 'selected' : ''}>50 Minutes</option>
                    <option value="60" ${item?.duration == 60 ? 'selected' : ''}>60 Minutes</option>
                </select>
            </td>
            <td><input type="number" class="form-control" name="service_price[${serviceId}]" value="${item?.price ?? ''}" required></td>
            <td>
                <button type="button" class="btn btn-success btn-sm update-service" data-service-id="${serviceId}">
                    <i class="fas fa-check"></i>
                </button>
                <button type="button" class="btn btn-danger btn-sm remove-service" data-service-id="${serviceId}" data-slug="${slug}">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        </tr> `;
    }


    /**
     * serviceSelection
     * 
     * Fetches the provider's saved services from the database on page load.
     * It populates the 'Services' table with descriptions, durations, and prices.
     */
    function serviceSelection() {
        if ($("#servicesTable").length) {
            fetch(COSY_API.base + COSY_API.providerServices.get, {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'X-WP-Nonce': cosy_ajax.nonce }
            })
                .then(res => {
                    if (!res.ok) throw new Error("Network error");
                    return res.json();
                })
                .then(data => {
                    if (Array.isArray(data)) {
                        const tableBody = $("#servicesTable tbody");
                        let rowsHtml = "";

                        data.forEach(item => {
                            const checkbox = $(`.service-checkbox[data-id='${item.service_id}']`);
                            if (checkbox.length) {
                                checkbox.prop("checked", item.checkbox_status === 'yes');
                            }
                            if (item.checkbox_status === 'yes') {
                                rowsHtml += buildServiceRow(item, item.service_id, item.service, item.service);
                            }
                        });

                        if (rowsHtml !== "") {
                            tableBody.html(rowsHtml);
                        }
                    }
                })
                .catch(err => console.error("Service load error:", err));
        }
    }


    // -------- FORM VALIDATION -------- //
    function formValidation() {
        $(document).ready(function () {
            $("#servicesForm").validate({
                rules: {
                    "service_desc[13]": { required: true, minlength: 5 },
                    "service_price[13]": { required: true, number: true, min: 1 },

                    "service_desc[14]": { required: true, minlength: 5 },
                    "service_price[14]": { required: true, number: true, min: 1 },

                    "service_desc[15]": { required: true, minlength: 5 },
                    "service_price[15]": { required: true, number: true, min: 1 }
                },
                messages: {
                    "service_desc[13]": "Description must be at least 5 characters",
                    "service_price[13]": "Please enter a valid price greater than 0",

                    "service_desc[14]": "Description must be at least 5 characters",
                    "service_price[14]": "Please enter a valid price greater than 0",

                    "service_desc[15]": "Description must be at least 5 characters",
                    "service_price[15]": "Please enter a valid price greater than 0"
                },
                errorClass: "text-danger",   // error message styling
                errorElement: "span"         // wrap error in <span>
            });
        });


    }


    // -------- SERVICE UPDATE -------- //
    function updateServices() {
        $(document).on("click", ".update-service", function () {
            const btn = $(this);
            const form = btn.closest("form");
            console.log(form);

            const msgBox = form.find(".cosy-message");
            const serviceId = btn.data("service-id");
            const row = $("#row-" + serviceId);

            const service = row.find(".service-title").text().trim().replace(/\s+/g, " ");
            const description = row.find(`textarea[name="service_desc[${serviceId}]"]`).val();
            const duration = row.find(`select[name="service_duration[${serviceId}]"]`).val();
            const price = row.find(`input[name="service_price[${serviceId}]"]`).val();
            console.log(price);

            const checkbox = $(`.service-checkbox[data-id='${serviceId}']`);
            const isChecked = checkbox.is(":checked");

            // Validation
            if (form.valid()) {
                btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm"></span>');
                fetch(COSY_API.base + COSY_API.providerServices.update, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': cosy_ajax.nonce
                    },
                    body: JSON.stringify({
                        service_id: serviceId,
                        serviceTitle: service,
                        description: description,
                        duration: duration,
                        price: price,
                        checked: isChecked ? 'yes' : 'no'
                    })
                })
                    .then(res => res.json())
                    .then(resp => {
                        if (resp.success) {
                            msgBox.html(cosyAlert("success", resp.message));
                            btn.html('<i class="bi bi-check2-circle"></i>');
                        } else {
                            msgBox.html(cosyAlert("danger", resp.message));
                        }
                    })
                    .catch(err => console.error("Update error:", err))
                    .finally(() => btn.prop("disabled", false));
            }
        });
    }


    // -------- SERVICE CHECKBOX -------- //
    function serviceCheckbox() {
        $(document).on("change", ".service-checkbox", function () {
            const checkbox = $(this);
            const serviceId = checkbox.data("id");
            const slug = checkbox.val();
            const serviceTitle = $("label[for='" + checkbox.attr("id") + "']").text();
            const tableBody = $("#servicesTable tbody");
            const isChecked = checkbox.is(":checked");

            if (isChecked && $("#row-" + serviceId).length) return;

            fetch(COSY_API.base + COSY_API.providerServices.update, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': cosy_ajax.nonce
                },
                body: JSON.stringify({
                    service_id: serviceId,
                    serviceTitle: serviceTitle,
                    checked: isChecked ? 'yes' : 'no'
                })
            })
                .then(res => res.json())
                .then(() => {
                    if (isChecked) {
                        fetch(COSY_API.base + COSY_API.providerServices.getOne + "?service_id=" + serviceId, {
                            method: 'GET',
                            credentials: 'same-origin',
                            headers: { 'X-WP-Nonce': cosy_ajax.nonce }
                        })
                            .then(res => res.json())
                            .then(resp => {
                                const item = resp?.data ? resp.data : resp;
                                const hasData = item && (
                                    (item.description && item.description.trim() !== '') ||
                                    (item.price && Number(item.price) > 0) ||
                                    (item.duration && Number(item.duration) > 0)
                                );

                                $("#row-" + serviceId).remove();
                                tableBody.append(
                                    hasData
                                        ? buildServiceRow(item, serviceId, slug, serviceTitle)
                                        : buildServiceRow(null, serviceId, slug, serviceTitle)
                                );
                            });
                    } else {
                        $("#row-" + serviceId).remove();
                    }
                })
                .catch(err => {
                    console.error("Service checkbox error:", err);
                    checkbox.prop("checked", !isChecked); // rollback
                });
        });
    }


    // -------- REMOVE SERVICE -------- //
    function removeService() {
        $(document).on("click", ".remove-service", function () {
            const serviceId = $(this).data("service-id");
            const slug = $(this).data("slug");

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you really want to delete this service?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#22c55e',
                confirmButtonText: 'Yes, delete it!',
                showClass: { popup: '' },
                hideClass: { popup: '' }
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(COSY_API.base + COSY_API.providerServices.delete, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': cosy_ajax.nonce
                        },
                        body: JSON.stringify({ service_id: serviceId })
                    })
                    .then(res => res.json())
                    .then(resp => {
                        if (resp.success) {
                            $("#row-" + serviceId).remove();
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: resp.message,
                                timer: 1500,
                                showConfirmButton: false,
                                showClass: { popup: '' },
                                hideClass: { popup: '' }
                            });
                            setTimeout(() => { location.reload(); }, 1600);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: resp.message,
                                confirmButtonColor: '#a44390',
                                showClass: { popup: '' },
                                hideClass: { popup: '' }
                            });
                        }
                    })
                    .catch(err => {
                        console.error("Remove service error:", err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: "Something went wrong while deleting.",
                            confirmButtonColor: '#a44390',
                            showClass: { popup: '' },
                            hideClass: { popup: '' }
                        });
                    });
                }
            });
        });
    }


    /**
     * initAvailability
     * 
     * Handles the 'Availability' management system.
     * 1. Validates required fields (Day, Start/End time).
     * 2. Saves data to backend via AJAX.
     * 3. Updates the 'Weekly Preview' badges in real-time on success.
     * 4. Resets form fields after a successful save.
     */
    function initAvailability(container = document) {
        $(container).off("click", "#save_availability_btn").on("click", "#save_availability_btn", function (e) {
            e.preventDefault();
            const $btn = $(this);
            const data = {
                action: 'save_provider_availability',
                nonce: $('#cosy_dashboard_nonce_field').val(),
                day: $('#availability_day').val(),
                start_time: $('#start_time').val(),
                end_time: $('#end_time').val(),
                slot_duration: $('#slot_duration').val(),
                break_start: $('#break_start_time').val(),
                break_end: $('#break_end_time').val()
            };

            if (!data.day || !data.start_time || !data.end_time) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Incomplete Information',
                    text: 'Please fill all required fields before saving.',
                    confirmButtonColor: '#a44390',
                    showClass: { popup: '' }, // No bounce animation
                    hideClass: { popup: '' }  // No bounce animation
                });
                return;
            }

            $.ajax({
                url: cosy_ajax.ajax_url,
                type: "POST",
                data: data,
                beforeSend() {
                    $btn.prop("disabled", true).html(`<span class="spinner-border spinner-border-sm"></span> Saving...`);
                },
                success(res) {
                    if (res.success) {
                        // Update local data for real-time preview using the 'data' object from outer scope
                        const dayName = data.day;
                        if (!window.savedAvailability) window.savedAvailability = {};
                        window.savedAvailability[dayName] = {
                            start_time: data.start_time,
                            end_time: data.end_time,
                            slot_duration: data.slot_duration,
                            break_start: data.break_start,
                            break_end: data.break_end
                        };

                        // Trigger re-render of badges
                        if (typeof renderAvailabilityBadges === 'function') {
                            renderAvailabilityBadges();
                        }

                        // Clear fields after success
                        $('#availability_day').val('');
                        $('#start_time').val('');
                        $('#end_time').val('');
                        $('#slot_duration').val('10');
                        $('#break_start_time').val('');
                        $('#break_end_time').val('');

                        // Success Notification
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Your availability has been saved.',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                    } else {
                        // Error Notification
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: res.data || 'Something went wrong while saving.',
                            confirmButtonColor: '#a44390'
                        });
                    }
                },
                complete() {
                    $btn.prop("disabled", false).html("Save Availability");
                }
            });
        });
    }


    //-------- PUBLIC INITIALISATION --------//
    return {
        init() {
            console.log("CosyApp: Initializing...");
            try { initAuthForms(); } catch (e) { console.error("AuthForms Error:", e); }
            try { initProfileUpdate(); } catch (e) { console.error("ProfileUpdate Error:", e); }
            try { initVideoUpload(); } catch (e) { console.error("VideoUpload Error:", e); }
            try { initTabs(); } catch (e) { console.error("Tabs Error:", e); }
            try { serviceSelection(); } catch (e) { console.error("ServiceSelection Error:", e); }
            try { updateServices(); } catch (e) { console.error("UpdateServices Error:", e); }
            try { serviceCheckbox(); } catch (e) { console.error("ServiceCheckbox Error:", e); }
            try { removeService(); } catch (e) { console.error("RemoveService Error:", e); }
            try { formValidation(); } catch (e) { console.error("FormValidation Error:", e); }
            try { initAvailability(); } catch (e) { console.error("InitAvailability Error:", e); }
            console.log("CosyApp: Initialization Complete.");
        }
    };


})(jQuery);


//-------- INITIALISATION --------//
jQuery(function () {
    CosyApp.init();
});
