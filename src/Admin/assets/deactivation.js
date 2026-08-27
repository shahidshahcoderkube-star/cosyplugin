jQuery(document).ready(function($) {
    // 1. Inject Premium Custom Styling for a perfectly balanced & symmetrical layout
    const cosySwalStyle = `
        .cosy-swal-popup {
            font-family: 'Outfit', 'Poppins', 'Inter', system-ui, -apple-system, sans-serif !important;
            border-radius: 24px !important;
            padding: 2.25rem 2rem !important; /* Balanced padding all around */
            text-align: center !important;
        }
        /* Remove the massive default SweetAlert2 icon top margin to make top/bottom spaces equal */
        .cosy-swal-popup .swal2-icon {
            margin: 0 auto 1.25rem auto !important; 
        }
        .cosy-swal-title {
            color: #0f172a !important;
            font-size: 1.75rem !important;
            font-weight: 700 !important;
            margin-top: 0 !important; 
            margin-bottom: 0.75rem !important;
            text-align: center !important;
        }
        .cosy-swal-html {
            color: #475569 !important;
            font-size: 0.95rem !important;
            font-weight: 400 !important;
            line-height: 1.6 !important;
            margin-bottom: 1.75rem !important;
            text-align: center !important;
        }
        .cosy-swal-confirm-btn {
            background-color: #a44390 !important;
            color: #ffffff !important;
            font-size: 0.95rem !important;
            font-weight: 600 !important;
            padding: 12px 28px !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 6px -1px rgba(164, 67, 144, 0.2), 0 2px 4px -1px rgba(164, 67, 144, 0.1) !important;
            border: none !important;
            margin: 0 10px !important;
            transition: all 0.2s ease !important;
            outline: none !important;
        }
        .cosy-swal-confirm-btn:hover {
            background-color: #8f357b !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 8px -1px rgba(164, 67, 144, 0.24) !important;
        }
        .cosy-swal-cancel-btn {
            background-color: #22c55e !important;
            color: #ffffff !important;
            font-size: 0.95rem !important;
            font-weight: 600 !important;
            padding: 12px 28px !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 6px -1px rgba(34, 197, 94, 0.2), 0 2px 4px -1px rgba(34, 197, 94, 0.1) !important;
            border: none !important;
            margin: 0 10px !important;
            transition: all 0.2s ease !important;
            outline: none !important;
        }
        .cosy-swal-cancel-btn:hover {
            background-color: #16a34a !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 8px -1px rgba(34, 197, 94, 0.24) !important;
        }
        .cosy-swal-input {
            border-radius: 12px !important;
            border: 2px solid #cbd5e1 !important;
            font-size: 1.15rem !important;
            font-weight: 700 !important;
            letter-spacing: 2px !important;
            text-align: center !important;
            color: #0f172a !important;
            margin-top: 0.5rem !important;
            margin-bottom: 1.5rem !important;
            padding: 12px !important;
            width: 100% !important;
            max-width: 280px !important;
            display: block !important;
            margin-left: auto !important;
            margin-right: auto !important;
            transition: border-color 0.2s ease !important;
            box-shadow: none !important;
            outline: none !important;
        }
        .cosy-swal-input:focus {
            border-color: #a44390 !important;
            outline: none !important;
        }
        /* Symmetrical actions layout */
        .cosy-swal-popup .swal2-actions {
            margin-top: 0 !important;
            margin-bottom: 0.5rem !important;
            padding: 0 !important;
        }
    `;
    $('<style>').text(cosySwalStyle).appendTo('head');

    // 2. Locate deactivation link
    const deactivateLink = $('#deactivate-cosy-appointments, span.deactivate a[href*="plugin=cosy-appointments"]');
    
    if (deactivateLink.length === 0) {
        return;
    }

    deactivateLink.on('click', function(e) {
        e.preventDefault();
        const originalHref = $(this).attr('href');

        if (typeof Swal === 'undefined') {
            window.location.href = originalHref;
            return;
        }

        // First Popup (Send OTP)
        Swal.fire({
            title: 'Are you sure?',
            text: 'A secure OTP is required to deactivate the Cosy Appointments plugin. It will be sent to the site Administrator\'s email.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, send OTP!',
            cancelButtonText: 'Cancel',
            showLoaderOnConfirm: true,
            customClass: {
                popup: 'cosy-swal-popup',
                title: 'cosy-swal-title',
                htmlContainer: 'cosy-swal-html',
                confirmButton: 'cosy-swal-confirm-btn',
                cancelButton: 'cosy-swal-cancel-btn'
            },
            buttonsStyling: false,
            preConfirm: () => {
                return $.ajax({
                    url: cosyDeactivation.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'cosy_send_deactivation_otp',
                        nonce: cosyDeactivation.nonce
                    }
                }).then(response => {
                    if (!response.success) {
                        throw new Error(response.data.message || 'Failed to send OTP.');
                    }
                    return response.data;
                }).catch(error => {
                    Swal.showValidationMessage(`Error: ${error.message}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                // Second Popup (Enter OTP)
                Swal.fire({
                    title: 'Enter Security OTP',
                    text: result.value.message,
                    input: 'text',
                    inputAttributes: {
                        autocapitalize: 'off',
                        placeholder: 'Enter 6-digit OTP'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Verify & Deactivate',
                    cancelButtonText: 'Cancel',
                    showLoaderOnConfirm: true,
                    customClass: {
                        popup: 'cosy-swal-popup',
                        title: 'cosy-swal-title',
                        htmlContainer: 'cosy-swal-html',
                        input: 'cosy-swal-input',
                        confirmButton: 'cosy-swal-confirm-btn',
                        cancelButton: 'cosy-swal-cancel-btn'
                    },
                    buttonsStyling: false,
                    preConfirm: (otp) => {
                        if (!otp) {
                            Swal.showValidationMessage('Please enter the OTP.');
                            return false;
                        }
                        return $.ajax({
                            url: cosyDeactivation.ajaxUrl,
                            type: 'POST',
                            data: {
                                action: 'cosy_verify_deactivation_otp',
                                otp: otp,
                                nonce: cosyDeactivation.nonce
                            }
                        }).then(response => {
                            if (!response.success) {
                                throw new Error(response.data.message || 'Invalid OTP.');
                            }
                            return response.data;
                        }).catch(error => {
                            Swal.showValidationMessage(`Error: ${error.message}`);
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((verifyResult) => {
                    if (verifyResult.isConfirmed) {
                        Swal.fire({
                            title: 'Authorized! 🟢',
                            text: 'OTP verified. Deactivating plugin now...',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false,
                            customClass: {
                                popup: 'cosy-swal-popup'
                            }
                        });
                        
                        setTimeout(() => {
                            window.location.href = originalHref;
                        }, 1500);
                    }
                });
            }
        });
    });
});
