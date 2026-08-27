/**
 * COSY APP FRONTEND FORM VALIDATION & DASHBOARD CONTROLLER
 * 
 * USE CASE:
 * Primary JavaScript module encapsulating all form validations, AJAX handlers, dynamic dashboard tabs, and alerts.
 * 
 * HOW TO USE:
 * Executed on page load via CosyApp.init().
 * 
 * WHAT IT DOES INTERNALLY:
 * 1. Binds jQuery validation rules to login/registration, profile, availability, and services forms.
 * 2. Manages dynamic AJAX tab rendering for Provider Dashboard.
 */
var CosyApp = (function ($) {

    /**
     * DYNAMIC ALERT BUILDER
     * 
     * USE CASE: Generates consistent alert HTML banners for AJAX success/error messages.
     * HOW TO USE: cosyAlert('success', 'Changes saved successfully');
     * WHAT IT DOES INTERNALLY: Constructs styled HTML alert box with font-awesome status icons.
     */
    function cosyAlert(type, message) {
        if (typeof message === "object" && message !== null) {
            message = message.message || JSON.stringify(message);
        }

        var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        var bg = type === 'success' ? 'rgba(34, 197, 94, 0.1)' : 'rgba(239, 68, 68, 0.1)';
        var color = type === 'success' ? '#22c55e' : '#ef4444';

        return `
            <div class="alert d-flex align-items-center border-0 p-3 mb-3" style="background: ${bg}; border-radius: 12px; color: ${color}; font-weight: 500; font-size: 0.9rem;" role="alert">
                <i class="fas ${icon} me-2" style="font-size: 1.1rem; color: ${color};"></i>
                <div>${message}</div>
            </div>
        `;
    }

    /**
     * SYNC PROFILE COMPLETENESS
     * 
     * USE CASE: Updates dashboard top profile completion warning banner in real-time.
     * HOW TO USE: Triggered after saving profile, availability, or services updates.
     * WHAT IT DOES INTERNALLY: Sends cosy_check_profile_completeness AJAX request and updates HTML container.
     */
    function syncProfileCompleteness() {
        const nonce = $("#cosy_dashboard_nonce_field").val();
        if (!nonce) return;

        $.ajax({
            url: cosy_ajax.ajax_url,
            type: "POST",
            data: {
                action: "cosy_check_profile_completeness",
                nonce: nonce
            },
            success(res) {
                if (res.success) {
                    const container = $("#cosy-completeness-alert-container");
                    if (container.length) {
                        container.html(res.data.html);
                    }
                }
            }
        });
    }

    /**
     * HANDLE MANUAL SUBMIT FALLBACK
     * 
     * USE CASE: Fallback AJAX submission handler for unvalidated forms.
     * HOW TO USE: handleManualSubmit(formElement, 'cosy_action', $button);
     * WHAT IT DOES INTERNALLY: Serializes form data and submits POST request to admin-ajax.php.
     */
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
            error() {
                let msgBox = $form.find(".cosy-message");
                msgBox.html(cosyAlert("danger", "Connection error. Please check your network and try again."));
            },
            complete() {
                $btn.prop("disabled", false).html("Submit");
            }
        });
    }

    /**
     * INIT AUTH FORMS
     * 
     * USE CASE: Manages Login and Customer/Provider Registration forms validation and AJAX submission.
     * HOW TO USE: Called on page load via CosyApp.init().
     * WHAT IT DOES INTERNALLY: Applies jQuery Validate rules and submits data via AJAX to WP backend.
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
                    terms: { required: true },
                    declaration_1: { required: true },
                    declaration_2: { required: true },
                    declaration_3: { required: true },
                    declaration_4: { required: true },
                    declaration_5: { required: true }
                },
                errorClass: "cosy-error",
                errorElement: "span",
                errorPlacement: function (error, element) {
                    if (element.parent('.cosy-password-wrapper').length) {
                        error.insertAfter(element.parent('.cosy-password-wrapper'));
                    } else if (element.closest('.cosy-declaration-wrapper').length) {
                        error.appendTo(element.closest('.cosy-declaration-wrapper'));
                    } else if (element.closest('.cosy-declaration-item').length) {
                        error.insertAfter(element.closest('.cosy-declaration-item'));
                    } else {
                        error.insertAfter(element);
                    }
                },

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

                        error() {
                            let msgBox = $form.find(".cosy-message");
                            msgBox.html(cosyAlert("danger", "Connection error. Please check your connection and try again."));
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

        $(document).on('click', '.upload-button', function () {
            $('.file-upload').trigger('click');
        });

        $(document).on('change', '.file-upload', function () {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('.profile-pic').attr('src', e.target.result);
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

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
                            syncProfileCompleteness();
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
            .off("click.cosyVideo", ".remove-video")
            .off("dragover.cosyVideo dragenter.cosyVideo", ".video-dropzone")
            .off("dragleave.cosyVideo dragend.cosyVideo", ".video-dropzone")
            .off("drop.cosyVideo", ".video-dropzone");

        // Open file picker
        $(document).on("click.cosyVideo", ".video-dropzone", function () {
            let input = $(this).closest("form").find(".video-upload")[0];
            if (input) input.click();
        });

        // Drag & Drop Support
        $(document).on("dragover.cosyVideo dragenter.cosyVideo", ".video-dropzone", function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass("drag-over");
        });

        $(document).on("dragleave.cosyVideo dragend.cosyVideo", ".video-dropzone", function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass("drag-over");
        });

        $(document).on("drop.cosyVideo", ".video-dropzone", function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass("drag-over");

            const dt = e.originalEvent.dataTransfer;
            if (dt && dt.files && dt.files.length > 0) {
                const $form = $(this).closest("form");
                const fileInput = $form.find(".video-upload")[0];
                if (fileInput) {
                    fileInput.files = dt.files;
                    $(fileInput).trigger("change.cosyVideo");
                }
            }
        });

        // Preview on file select 
        $(document).on("change.cosyVideo", ".video-upload", function () {
            const file = this.files[0];
            if (!file) return;

            if (!file.type.startsWith("video/")) {
                CosyAlert.warning('Invalid File', 'Please select a valid video file.');
                this.value = "";
                return;
            }

            const limitMb = (typeof cosy_ajax !== 'undefined' && cosy_ajax.max_video_size) ? cosy_ajax.max_video_size : 20;
            const maxSizeBytes = limitMb * 1024 * 1024;

            if (file.size > maxSizeBytes) {
                CosyAlert.warning('File Too Large', `Video size must not exceed ${limitMb} MB. Please compress your video and try again.`);
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

        // Submit form with video
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

        // Delete Video
        $(document).on("click.cosyVideo", ".remove-video", function () {
            const $btn = $(this);
            const userId = $btn.data("id");
            const $container = $btn.closest('[id^="existing-video-"]');

            CosyAlert.confirm(
                'Are you sure?',
                'Do you really want to delete this video?'
            ).then(() => {
                $.ajax({
                    url: cosy_ajax.ajax_url,
                    type: "POST",
                    data: {
                        action: "delete_video",
                        user_id: userId,
                        nonce: $("#cosy_dashboard_nonce_field").val()
                    },
                    beforeSend() {
                        $btn.prop("disabled", true).html(`<span class="spinner-border spinner-border-sm"></span>`);
                    },
                    success(res) {
                        if (res.success) {
                            $container.fadeOut(300, function () { $(this).remove(); });
                            CosyAlert.success('Deleted!', 'Video has been deleted.').then(() => {
                                location.reload();
                            });
                        } else {
                            CosyAlert.error('Error!', res.data || "Could not delete video.");
                        }
                    },
                    complete() {
                        $btn.prop("disabled", false).html('<i class="fas fa-trash"></i>');
                    }
                });
            }).catch(() => { });
        });
    }


    /**
     * INIT TABS
     * 
     * USE CASE: Handles dynamic AJAX loading of Provider Dashboard tabs (Profile, Services, Availability).
     * HOW TO USE: Triggered when user switches tabs on Provider Dashboard.
     * WHAT IT DOES INTERNALLY: Sends load_dashboard_tab AJAX call and injects response HTML into tab target pane.
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
                        `<div class="d-flex justify-content-center align-items-center" style="min-height: 600px;">
                            <span class="spinner-border" style="color: #a44390;" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </span>
                        </div>`
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
                        if (tabSlug === "nonworking") {
                            initNonWorkingDays(targetBox);
                        }
                    } else {
                        $(targetBox).html(cosyAlert("danger", res.data));
                    }
                }
            });
        });

        $(".cosy-tab.active").trigger("shown.bs.tab");
    }


    /**
     * BUILD SERVICE ROW
     * 
     * USE CASE: Constructs dynamic HTML table row element for a provider service item.
     * HOW TO USE: Called internally by serviceSelection() and serviceCheckbox().
     * WHAT IT DOES INTERNALLY: Generates <tr> template string with price input and action buttons.
     */
    function buildServiceRow(item, serviceId, slug, serviceTitle) {
        return `
        <tr id="row-${serviceId}">
            <td>
                <span class="service-title">${item?.service ?? serviceTitle}</span>
                <input type="hidden" name="service_duration[${serviceId}]" value="10">
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
     * SERVICE SELECTION
     * 
     * USE CASE: Fetches provider's active services via REST API and populates the Services table.
     * HOW TO USE: Triggered on Services tab initial load.
     * WHAT IT DOES INTERNALLY: Queries COSY_API.providerServices.get and updates table rows.
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


    /**
     * FORM VALIDATION
     * 
     * USE CASE: Configures jQuery Validate rules for services price inputs.
     * HOW TO USE: Called internally on Services tab load.
     * WHAT IT DOES INTERNALLY: Binds jQuery validate rules ensuring price is greater than 0.
     */
    function formValidation() {
        $(document).ready(function () {
            $("#servicesForm").validate({
                rules: {
                    "service_price[13]": { required: true, number: true, min: 1 },
                    "service_price[14]": { required: true, number: true, min: 1 },
                    "service_price[15]": { required: true, number: true, min: 1 }
                },
                messages: {
                    "service_price[13]": "Please enter a valid price greater than 0",
                    "service_price[14]": "Please enter a valid price greater than 0",
                    "service_price[15]": "Please enter a valid price greater than 0"
                },
                errorClass: "text-danger",   // error message styling
                errorElement: "span"         // wrap error in <span>
            });
        });


    }


    /**
     * updateServices
     * 
     * Handles dynamic update of individual service details (description, duration, price)
     * via fetch API when the provider clicks the green check icon on a service row.
     */
    function updateServices() {
        $(document).on("click", ".update-service", function () {
            const btn = $(this);
            const form = btn.closest("form");

            const msgBox = form.find(".cosy-message");
            const serviceId = btn.data("service-id");
            const row = $("#row-" + serviceId);

            const service = row.find(".service-title").text().trim().replace(/\s+/g, " ");
            const duration = row.find(`select[name="service_duration[${serviceId}]"]`).val();
            const price = row.find(`input[name="service_price[${serviceId}]"]`).val();

            const checkbox = $(`.service-checkbox[data-id='${serviceId}']`);
            const isChecked = checkbox.is(":checked");

            // Validate form fields before updating
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
                            syncProfileCompleteness();
                        } else {
                            msgBox.html(cosyAlert("danger", resp.message));
                        }
                    })
                    .catch(err => console.error("Update error:", err))
                    .finally(() => btn.prop("disabled", false));
            }
        });
    }


    /**
     * serviceCheckbox
     * 
     * Listens for changes on service selection checkboxes.
     * 1. Updates selection state in database immediately.
     * 2. Fetches details for newly selected services and adds a row to the table.
     * 3. Removes the corresponding row if unchecked.
     */
    function serviceCheckbox() {
        $(document).on("change", ".service-checkbox", function () {
            const checkbox = $(this);
            const serviceId = checkbox.data("id");
            const slug = checkbox.val();
            const serviceTitle = $("label[for='" + checkbox.attr("id") + "']").text();
            const tableBody = $("#servicesTable tbody");
            const isChecked = checkbox.is(":checked");

            if (isChecked) {
                if ($("#row-" + serviceId).length) return;

                // 1. Optimistic UI: Instantly add the row with placeholder fields
                const temporaryRow = buildServiceRow(null, serviceId, slug, serviceTitle);
                tableBody.append(temporaryRow);

                const $row = $("#row-" + serviceId);
                const $priceInput = $row.find(`input[name="service_price[${serviceId}]"]`);
                $priceInput.prop("placeholder", "...");

                // 2. Single network request to update and fetch values
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
                        checked: 'yes'
                    })
                })
                    .then(res => res.json())
                    .then(resp => {
                        const item = resp?.data ? resp.data : resp;
                        if (item && item.success !== false) {
                            if (item.price !== null && item.price !== undefined && item.price !== "") {
                                $priceInput.val(item.price);
                            }
                            if (item.duration) {
                                $row.find(`select[name="service_duration[${serviceId}]"]`).val(item.duration);
                            }
                            syncProfileCompleteness();
                        }
                        $priceInput.prop("placeholder", "");
                    })
                    .catch(err => {
                        console.error("Service checkbox error:", err);
                        checkbox.prop("checked", false); // rollback checkbox
                        $("#row-" + serviceId).remove(); // rollback row
                    });
            } else {
                // 1. Instantly remove row from UI
                $("#row-" + serviceId).remove();

                // 2. Send background update
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
                        checked: 'no'
                    })
                })
                    .then(res => {
                        syncProfileCompleteness();
                    })
                    .catch(err => {
                        console.error("Service checkbox error:", err);
                        checkbox.prop("checked", true); // rollback checkbox
                        tableBody.append(buildServiceRow(null, serviceId, slug, serviceTitle));
                    });
            }
        });
    }


    /**
     * removeService
     * 
     * Handles dynamic removal/deletion of a service when a provider clicks the trash icon.
     * Asks for confirmation using SweetAlert2 before executing the AJAX request.
     */
    function removeService() {
        $(document).on("click", ".remove-service", function () {
            const serviceId = $(this).data("service-id");
            const slug = $(this).data("slug");

            CosyAlert.confirm(
                'Are you sure?',
                'Do you really want to delete this service?'
            ).then(() => {
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
                            const checkbox = $(`.service-checkbox[data-id='${serviceId}']`);
                            if (checkbox.length) {
                                checkbox.prop("checked", false);
                            }
                            CosyAlert.success('Deleted!', resp.message);
                            syncProfileCompleteness();
                        } else {
                            CosyAlert.error('Error!', resp.message);
                        }
                    })
                    .catch(err => {
                        console.error("Remove service error:", err);
                        CosyAlert.error('Error!', 'Something went wrong while deleting.');
                    });
            }).catch(() => { /* Cancelled */ });
        });
    }


    /**
     * Helper: formatTimeForBadge
     * 
     * Converts a 24-hour time string (e.g., "14:30") to a user-friendly 12-hour
     * format with AM/PM (e.g., "02:30 PM"). This is used for rendering availability badges.
     * 
     * @param {string} timeStr - The 24-hour time string to format.
     * @returns {string} The formatted 12-hour time string.
     */
    function formatTimeForBadge(timeStr) {
        if (!timeStr) return '';
        const [hour, minute] = timeStr.split(':');
        const h = parseInt(hour);
        const ampm = h >= 12 ? 'PM' : 'AM';
        const h12 = h % 12 || 12;
        return `${h12}:${minute} ${ampm}`;
    }

    /**
     * Helper: renderAvailabilityBadges
     * 
     * Dynamically updates the 'Weekly Preview' container badges based on the global
     * `window.savedAvailability` schedule object. It clears previous badges and builds
     * new ones for days that have working hours set.
     */
    function renderAvailabilityBadges() {
        const container = jQuery('#weekly_preview_badges');
        if (!container.length) return;

        container.empty();
        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        days.forEach(day => {
            const avail = window.savedAvailability && window.savedAvailability[day];
            if (avail && avail.start_time && avail.end_time) {
                const s = formatTimeForBadge(avail.start_time);
                const e = formatTimeForBadge(avail.end_time);
                const shortDay = day.substring(0, 3);
                container.append(`<span class="badge availability-badge">${shortDay}: ${s} - ${e}</span>`);
            }
        });
    }

    /**
     * initAvailability
     * 
     * Manages the 'Availability' tab interactive features.
     * 1. Uses event delegation to listen for day selection dropdown changes to automatically
     *    auto-fill the inputs if there is already a saved schedule for that day.
     * 2. Handles the AJAX saving of provider availability with SweetAlert2 notifications,
     *    real-time preview badge updates, and form resetting upon successful save.
     * 
     * @param {HTMLElement|Document} container - The wrapper element (useful for dynamic/AJAX loaded tabs).
     */
    function initAvailability(container = document) {
        // Day Selection Handler (via event delegation to survive dynamic DOM load/reloads)
        $(container).off('change', '#availability_day').on('change', '#availability_day', function () {
            const day = $(this).val();
            if (!day) {
                $('#apply_days_container').hide();
                return;
            }

            // Show apply to days container
            $('#apply_days_container').fadeIn();

            // Reset and check the selected day
            $('.apply-day-checkbox').prop('checked', false).prop('disabled', false);
            $('#apply_day_' + day).prop('checked', true).prop('disabled', true);

            const data = window.savedAvailability && window.savedAvailability[day];
            if (data && typeof data === 'object') {
                $('#start_time').val(data.start_time || '');
                $('#end_time').val(data.end_time || '');
                $('#slot_duration').val(data.slot_duration || '10');
                $('#break_start_time').val(data.break_start || '');
                $('#break_end_time').val(data.break_end || '');
            } else {
                // Clear fields if no schedule exists for the selected day
                $('#start_time').val('');
                $('#end_time').val('');
                $('#slot_duration').val('10');
                $('#break_start_time').val('');
                $('#break_end_time').val('');
            }
            updateLiveTimeBadges();
        });

        /**
         * NORMALIZE PM TIME
         * 
         * USE CASE: Adjusts time values to PM when end or break times are logically after start times.
         * HOW TO USE: normalizePmTime('05:00', '09:00'); // Converts 05:00 to 17:00 PM
         * WHAT IT DOES INTERNALLY: Converts 12-hour ambiguous inputs to 24-hour PM formats relative to start time.
         */
        function normalizePmTime(timeStr, refStr) {
            if (!timeStr) return timeStr;
            const parts = timeStr.split(':');
            if (parts.length < 2) return timeStr;
            let h = parseInt(parts[0], 10);
            const m = parts[1];

            let refH = 0;
            if (refStr) {
                const refParts = refStr.split(':');
                refH = parseInt(refParts[0], 10);
            }

            if (h > 0 && h <= 11 && refH >= 1 && h <= refH) {
                h += 12;
            }
            return String(h).padStart(2, '0') + ':' + m;
        }

        /**
         * UPDATE LIVE TIME BADGES
         * 
         * USE CASE: Updates live 12-hour AM/PM badges beneath availability input fields.
         * HOW TO USE: Triggered on input change events across time pickers.
         * WHAT IT DOES INTERNALLY: Formats time strings and toggles preview badge visibility.
         */
        function updateLiveTimeBadges() {
            const format12 = (val, refVal) => {
                if (!val) return '';
                const normalized = normalizePmTime(val, refVal);
                const parts = normalized.split(':');
                let h = parseInt(parts[0], 10);
                const m = parts[1];
                const ampm = h >= 12 ? 'PM' : 'AM';
                const h12 = h % 12 || 12;
                const hStr = String(h12).padStart(2, '0');
                const wasAutoConv = (val !== normalized);
                return `<i class="far fa-clock me-1"></i> Selected: ${hStr}:${m} ${ampm} ${wasAutoConv ? '(PM)' : ''}`;
            };

            const startVal = $('#start_time').val();
            const endVal = $('#end_time').val();
            const breakStartVal = $('#break_start_time').val();
            const breakEndVal = $('#break_end_time').val();

            if (startVal) {
                $('#start_time_badge').html(format12(startVal, null)).show();
            } else {
                $('#start_time_badge').hide();
            }

            if (endVal) {
                $('#end_time_badge').html(format12(endVal, startVal)).show();
            } else {
                $('#end_time_badge').hide();
            }

            if (breakStartVal) {
                $('#break_start_time_badge').html(format12(breakStartVal, startVal)).show();
            } else {
                $('#break_start_time_badge').hide();
            }

            if (breakEndVal) {
                $('#break_end_time_badge').html(format12(breakEndVal, breakStartVal || startVal)).show();
            } else {
                $('#break_end_time_badge').hide();
            }
        }

        $(container).off('input change', '#start_time, #end_time, #break_start_time, #break_end_time').on('input change', '#start_time, #end_time, #break_start_time, #break_end_time', updateLiveTimeBadges);

        // Select All Weekdays helper
        $(container).off('click', '#select_all_weekdays').on('click', '#select_all_weekdays', function (e) {
            e.preventDefault();
            const weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            $('.apply-day-checkbox').each(function () {
                const val = $(this).val();
                $(this).prop('checked', weekdays.includes(val));
            });
            const mainDay = $('#availability_day').val();
            if (mainDay) {
                $('#apply_day_' + mainDay).prop('checked', true).prop('disabled', true);
            }
        });

        // Select All Days helper
        $(container).off('click', '#select_all_days').on('click', '#select_all_days', function (e) {
            e.preventDefault();
            $('.apply-day-checkbox').prop('checked', true);
            const mainDay = $('#availability_day').val();
            if (mainDay) {
                $('#apply_day_' + mainDay).prop('checked', true).prop('disabled', true);
            }
        });

        // Save Availability AJAX Handler (via event delegation)
        $(container).off("click", "#save_availability_btn").on("click", "#save_availability_btn", function (e) {
            e.preventDefault();
            const $btn = $(this);

            const days = [];
            $('.apply-day-checkbox:checked').each(function () {
                days.push($(this).val());
            });
            const mainDay = $('#availability_day').val();
            if (mainDay && !days.includes(mainDay)) {
                days.push(mainDay);
            }

            const rawStart = $('#start_time').val();
            const rawEnd = $('#end_time').val();
            const rawBreakStart = $('#break_start_time').val();
            const rawBreakEnd = $('#break_end_time').val();

            const startTime = rawStart;
            const endTime = normalizePmTime(rawEnd, startTime);
            const breakStart = normalizePmTime(rawBreakStart, startTime);
            const breakEnd = normalizePmTime(rawBreakEnd, breakStart || startTime);

            const data = {
                action: 'save_provider_availability',
                nonce: $('#cosy_dashboard_nonce_field').val(),
                day: mainDay,
                days: days,
                start_time: startTime,
                end_time: endTime,
                slot_duration: $('#slot_duration').val(),
                break_start: breakStart,
                break_end: breakEnd
            };

            // Basic frontend validation to verify day and times are selected
            if (!data.day || !data.start_time || !data.end_time) {
                CosyAlert.warning('Incomplete Information', 'Please fill all required fields before saving.');
                return;
            }

            $.ajax({
                url: cosy_ajax.ajax_url,
                type: "POST",
                data: data,
                beforeSend() {
                    // Disable button and show a clean loading spinner
                    $btn.prop("disabled", true).html(`<span class="spinner-border spinner-border-sm"></span> Saving...`);
                },
                success(res) {
                    if (res.success) {
                        // Update dynamic schedule dictionary in client memory for all selected days
                        if (!window.savedAvailability) window.savedAvailability = {};
                        days.forEach(dayName => {
                            window.savedAvailability[dayName] = {
                                start_time: data.start_time,
                                end_time: data.end_time,
                                slot_duration: data.slot_duration,
                                break_start: data.break_start,
                                break_end: data.break_end
                            };
                        });

                        // Trigger dynamic re-render of availability preview badges
                        if (typeof renderAvailabilityBadges === 'function') {
                            renderAvailabilityBadges();
                        }

                        // Reset form fields back to default state
                        $('#availability_day').val('');
                        $('#start_time').val('');
                        $('#end_time').val('');
                        $('#slot_duration').val('10');
                        $('#break_start_time').val('');
                        $('#break_end_time').val('');
                        $('#apply_days_container').hide();

                        // Show success alert
                        CosyAlert.success('Success!', 'Your availability has been saved.');
                        syncProfileCompleteness();
                    } else {
                        // Show error alert on request failure
                        CosyAlert.error('Error!', res.data || 'Something went wrong while saving.');
                    }
                },
                complete() {
                    // Restore save button state
                    $btn.prop("disabled", false).html("Save Availability");
                }
            });
        });

        // Remove Availability Day handler via event delegation
        $(container).off("click", ".remove-availability-day-btn").on("click", ".remove-availability-day-btn", function (e) {
            e.preventDefault();
            const dayName = $(this).data('day');
            if (!dayName) return;

            const $badge = $('#avail-badge-' + dayName);

            if (typeof CosyAlert !== 'undefined') {
                CosyAlert.confirm(
                    'Remove Availability?',
                    'Are you sure you want to remove working hours for ' + dayName + '?',
                    'Yes, Remove It'
                ).then(function () {
                    executeDeleteDay(dayName, $badge);
                });
            } else if (confirm('Remove availability for ' + dayName + '?')) {
                executeDeleteDay(dayName, $badge);
            }
        });

        /**
         * EXECUTE DELETE DAY
         * 
         * USE CASE: Deletes a specific day's working hours availability schedule from database.
         * HOW TO USE: Called after user confirms availability badge deletion.
         * WHAT IT DOES INTERNALLY: Posts delete_provider_availability_day AJAX call and removes badge element.
         */
        function executeDeleteDay(dayName, $badge) {
            $.ajax({
                url: cosy_ajax.ajax_url,
                type: "POST",
                data: {
                    action: 'delete_provider_availability_day',
                    nonce: $('#cosy_dashboard_nonce_field').val(),
                    day: dayName
                },
                success: function (res) {
                    if (res.success) {
                        $badge.fadeOut(300, function () { $(this).remove(); });
                        if (window.savedAvailability) {
                            delete window.savedAvailability[dayName];
                        }
                        if (typeof CosyAlert !== 'undefined') {
                            CosyAlert.success('Removed!', dayName + ' availability has been removed.');
                        }
                        syncProfileCompleteness();
                    } else {
                        if (typeof CosyAlert !== 'undefined') {
                            CosyAlert.error('Error!', res.data || 'Failed to remove availability.');
                        } else {
                            alert(res.data || 'Failed to remove availability.');
                        }
                    }
                }
            });
        }
    }

    /**
     * Helper: updateDaysCount
     * 
     * Recalculates and displays the number of non-working days marked
     * in the daysTable.
     */
    function updateDaysCount() {
        const count = jQuery('#daysTable tbody tr').length;
        const counter = jQuery('#daysCount');
        if (counter.length) {
            counter.html(count === 0 ? "No non-working days added yet." : `❌ ${count} non-working day(s) marked.`);
        }
    }

    /**
     * initNonWorkingDays
     * 
     * Handles the client-side interaction for marking non-working days.
     * 1. Listens for clicks on #add_non_working_day_btn to validate the date input,
     *    avoid duplicate additions, and dynamically inject a row.
     * 2. Listens for clicks on .remove-day-btn to dynamically clear marked days.
     * 
     * @param {HTMLElement|Document} container - Parent element context for event binding.
     */
    function initNonWorkingDays(container = document) {
        // Add Non-Working Day via event delegation
        $(container).off('click', '#add_non_working_day_btn').on('click', '#add_non_working_day_btn', function (e) {
            e.preventDefault();
            const date = $('#nonWorkingDate').val();
            const reason = $('#nonWorkingReason').val() || 'N/A';
            const tableBody = $('#daysTable tbody');

            if (!date) {
                CosyAlert.warning('Date Required', 'Please select a valid date first!');
                return;
            }

            // Prevent duplicate dates
            if ($('#day-' + date).length) {
                CosyAlert.warning('Date Already Marked', 'This date is already present in your non-working days list.');
                return;
            }

            const rowHtml = `
                <tr id="day-${date}">
                    <td>${date}</td>
                    <td>${reason}</td>
                    <td>
                        <button class="btn btn-success btn-sm update-day-btn" title="Update" style="margin-right: 4px;">
                            <i class="bi bi-check-circle"></i>
                        </button>
                        <button class="btn btn-danger btn-sm remove-day-btn" title="Remove" data-date="${date}">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </td>
                </tr>
            `;

            tableBody.append(rowHtml);
            $('#nonWorkingDate').val('');
            $('#nonWorkingReason').val('');
            updateDaysCount();
        });

        // Remove Non-Working Day via event delegation
        $(container).off('click', '.remove-day-btn').on('click', '.remove-day-btn', function (e) {
            e.preventDefault();
            const date = $(this).data('date');
            const row = $('#day-' + date);
            if (row.length) {
                row.remove();
            }
            updateDaysCount();
        });
    }

    /**
     * INIT CUSTOMER PROFILE
     * 
     * USE CASE: Handles AJAX profile updates and password changes on Customer My Profile page.
     * HOW TO USE: Triggered automatically on page load via CosyApp.init().
     * WHAT IT DOES INTERNALLY: Listens to #cosyCustomerProfileForm and #cosyCustomerPasswordForm submit events.
     */
    function initCustomerProfile(container = document) {
        $('#cosyCustomerProfileForm').on('submit', function (e) {
            e.preventDefault();
            var $form = $(this);
            var $btn = $form.find("button[type='submit']");
            var $msg = $form.find(".profile-msg");

            var formData = {
                action: 'cosy_customer_profile_update',
                cosy_profile_nonce: $('#cosy_profile_nonce_field').val(),
                first_name: $form.find("input[name='first_name']").val(),
                last_name: $form.find("input[name='last_name']").val(),
                email: $form.find("input[name='email']").val()
            };

            $.ajax({
                url: (typeof cosy_ajax !== 'undefined' ? cosy_ajax.ajax_url : ajaxurl),
                type: 'POST',
                data: formData,
                beforeSend: function () {
                    $btn.prop('disabled', true).text('Saving...');
                    $msg.html('');
                },
                success: function (response) {
                    if (response.success) {
                        $msg.html(cosyAlert('success', response.data));
                    } else {
                        $msg.html(cosyAlert('danger', response.data));
                    }
                },
                error: function () {
                    $msg.html(cosyAlert('danger', 'An unexpected error occurred. Please try again.'));
                },
                complete: function () {
                    $btn.prop('disabled', false).text('Save Changes');
                }
            });
        });

        $('#cosyCustomerPasswordForm').on('submit', function (e) {
            e.preventDefault();
            var $form = $(this);
            var $btn = $form.find("button[type='submit']");
            var $msg = $form.find(".password-msg");

            var formData = {
                action: 'cosy_customer_password_update',
                cosy_password_nonce: $('#cosy_password_nonce_field').val(),
                new_password: $form.find("input[name='new_password']").val(),
                confirm_password: $form.find("input[name='confirm_password']").val()
            };

            $.ajax({
                url: (typeof cosy_ajax !== 'undefined' ? cosy_ajax.ajax_url : ajaxurl),
                type: 'POST',
                data: formData,
                beforeSend: function () {
                    $btn.prop('disabled', true).text('Updating...');
                    $msg.html('');
                },
                success: function (response) {
                    if (response.success) {
                        $msg.html(cosyAlert('success', response.data));
                        $form[0].reset();
                    } else {
                        $msg.html(cosyAlert('danger', response.data));
                    }
                },
                error: function () {
                    $msg.html(cosyAlert('danger', 'An unexpected error occurred. Please try again.'));
                },
                complete: function () {
                    $btn.prop('disabled', false).text('Update Password');
                }
            });
        });
    }

    /**
     * INIT TOKEN REVIEW
     * 
     * USE CASE: Handles 1 to 10 score button selection and AJAX form submission for token-based customer reviews.
     * HOW TO USE: Triggered on Leave Review page when submitting feedback.
     * WHAT IT DOES INTERNALLY: Validates score range (1-10) and comment length, then posts to cosy_submit_token_review.
     */
    function initTokenReview() {
        $(document).on('click', '.rating-score-btn', function (e) {
            e.preventDefault();
            $('.rating-score-btn').removeClass('active btn-primary text-white').addClass('btn-outline-secondary').css({
                'background': '',
                'border-color': '',
                'color': ''
            });

            $(this).removeClass('btn-outline-secondary').addClass('active').css({
                'background': '#a44390',
                'border-color': '#a44390',
                'color': '#ffffff'
            });

            const score = $(this).data('score');
            $('#selectedScore').val(score);
            $('#ratingHelpText').html('<span class="fw-bold" style="color: #a44390;">Selected Rating: ' + score + ' / 10</span>');
        });

        $('#cosyTokenReviewForm').on('submit', function (e) {
            e.preventDefault();

            const score = parseInt($('#selectedScore').val()) || 0;
            const reviewText = $.trim($('#reviewCommentText').val());
            const $alert = $('#reviewResponseAlert');
            const $btn = $('#btnSubmitTokenReview');

            if (score < 1 || score > 10) {
                $alert.removeClass('d-none alert-success').addClass('alert-danger').html('<i class="fas fa-exclamation-circle me-2" style="font-size: 1.05rem;"></i> Please select a rating score between 1 and 10.').slideDown();
                return;
            }

            if (reviewText.length < 5) {
                $alert.removeClass('d-none alert-success').addClass('alert-danger').html('<i class="fas fa-exclamation-circle me-2" style="font-size: 1.05rem;"></i> Please write a brief review comment before submitting.').slideDown();
                return;
            }

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Submitting...');
            $alert.addClass('d-none');

            const formData = $(this).serialize();
            const ajaxUrl = (typeof cosy_ajax !== 'undefined' && cosy_ajax.ajax_url) ? cosy_ajax.ajax_url : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php');

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: formData,
                success: function (res) {
                    if (res.success) {
                        $alert.removeClass('d-none alert-danger').addClass('alert-success').html('<i class="fas fa-check-circle me-2" style="font-size: 1.05rem;"></i> ' + (res.data.message || 'Thank you! Your review has been submitted successfully.')).slideDown();
                        $('#cosyTokenReviewForm').find('input, textarea, button').prop('disabled', true);
                        setTimeout(function () {
                            window.location.href = res.data.redirect_url || '/';
                        }, 3000);
                    } else {
                        $alert.removeClass('d-none alert-success').addClass('alert-danger').html('<i class="fas fa-exclamation-circle me-2" style="font-size: 1.05rem;"></i> ' + (res.data.message || 'Failed to submit review. Please try again.')).slideDown();
                        $btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Submit Review');
                    }
                },
                error: function () {
                    $alert.removeClass('d-none alert-success').addClass('alert-danger').html('<i class="fas fa-exclamation-circle me-2" style="font-size: 1.05rem;"></i> An unexpected error occurred. Please try again.').slideDown();
                    $btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Submit Review');
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
            try { initCustomerProfile(); } catch (e) { console.error("CustomerProfile Error:", e); }
            try { initTokenReview(); } catch (e) { console.error("TokenReview Error:", e); }
            try { initVideoUpload(); } catch (e) { console.error("VideoUpload Error:", e); }
            try { initTabs(); } catch (e) { console.error("Tabs Error:", e); }
            try { serviceSelection(); } catch (e) { console.error("ServiceSelection Error:", e); }
            try { updateServices(); } catch (e) { console.error("UpdateServices Error:", e); }
            try { serviceCheckbox(); } catch (e) { console.error("ServiceCheckbox Error:", e); }
            try { removeService(); } catch (e) { console.error("RemoveService Error:", e); }
            try { formValidation(); } catch (e) { console.error("FormValidation Error:", e); }
            try { initAvailability(); } catch (e) { console.error("InitAvailability Error:", e); }
            try { initNonWorkingDays(); } catch (e) { console.error("InitNonWorkingDays Error:", e); }
            console.log("CosyApp: Initialization Complete.");
        }
    };


})(jQuery);


//-------- INITIALISATION --------//
jQuery(function () {
    CosyApp.init();
});
