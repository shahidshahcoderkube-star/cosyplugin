// ==================================================
// Cosy App – Final Stable Version
// ==================================================

var CosyApp = (function ($) {

    //--------------- ALERTS ---------------//
    function cosyAlert(type, message) {
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


    //--------------- AUTH / REGISTER / LOGIN FORM VALIDATION ---------------//
    function initAuthForms(container = document) {

        $(container).find(".cosy-form").each(function () {

            let $form = $(this);
            if ($form.data("bound")) return;
            $form.data("bound", true);

            let action = $form.data("action");
            let $btn = $form.find('button[type="submit"]');

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
                        data: $(formEl).serialize() + "&action=" + action + "&nonce=" + cosy_ajax.cosy_nonce,

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


    //----------- PROFILE UPDATE ----------------//
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
                formData.append("nonce", cosy_ajax.cosy_nonce);

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


    //----------- VIDEO UPLOAD & DELETE (ONCE) ----------------//
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
                alert("Please upload a valid video file");
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
            formData.append("nonce", cosy_ajax.cosy_nonce);

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

            if (!confirm("Do you really want to delete this video?")) return;

            const $btn = $(this);

            $.ajax({
                url: cosy_ajax.ajax_url,
                type: "POST",
                data: {
                    action: $btn.data("action"),
                    user_id: $btn.data("id"),
                    nonce: cosy_ajax.cosy_nonce
                },

                beforeSend() {
                    $btn.prop("disabled", true)
                        .data("original-html", $btn.html())
                        .html(`<span class="spinner-border spinner-border-sm"></span>`);
                },

                success(res) {
                    if (res.success) {
                        location.reload();
                    } else {
                        alert(res.data.message);
                    }
                },

                complete() {
                    $btn.prop("disabled", false)
                        .html($btn.data("original-html"));
                }
            });
        });
    }


    //----------- DASHBOARD TABS (AJAX) ----------------//
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
                    nonce: cosy_ajax.cosy_nonce
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
                    <option value="30" ${item?.duration == 30 ? 'selected' : ''}>30 Minutes</option>
                    <option value="60" ${item?.duration == 60 ? 'selected' : ''}>1 Hour</option>
                </select>
            </td>
            <td><input type="number" class="form-control" name="service_price[${serviceId}]" value="${item?.price ?? ''}" required></td>
            <td>
                <button type="button" class="btn btn-success btn-sm update-service" data-service-id="${serviceId}">
                    <i class="bi bi-check-circle"></i>
                </button>
                <button type="button" class="btn btn-danger btn-sm remove-service" data-service-id="${serviceId}" data-slug="${slug}">
                    <i class="bi bi-x-circle"></i>
                </button>
            </td>
        </tr> `;
    }


    // -------- PAGE LOAD: GET SAVED SERVICES -------- //
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
            const msgBox = $(".cosy-message");

            if (!confirm("Are you sure you want to delete this service?")) return;

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
                        msgBox.html(cosyAlert("success", resp.message));
                        setTimeout(() => { location.reload(); }, 1000);
                    } else {
                        msgBox.html(cosyAlert("danger", resp.message));
                    }
                })
                .catch(err => {
                    console.error("Remove service error:", err);
                    msgBox.html(cosyAlert("danger", "Something went wrong while deleting."));
                });
        });
    }


    //-------- PUBLIC INITIALISATION --------//
    return {
        init() {
            initAuthForms();
            initProfileUpdate();
            initVideoUpload();
            initTabs();
            serviceSelection();
            updateServices();
            serviceCheckbox();
            removeService();
            formValidation();
        }
    };


})(jQuery);


//-------- INITIALISATION --------//
jQuery(function () {
    CosyApp.init();
});
