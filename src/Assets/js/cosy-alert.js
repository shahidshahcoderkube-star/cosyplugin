/**
 * CosyAlert
 * 
 * A centralized utility for handling SweetAlert2 notifications across the Cosy Appointments plugin.
 * Implements DRY (Don't Repeat Yourself) principle by reducing boilerplate code.
 */
var CosyAlert = (function () {
    
    // Default styling matching the plugin theme
    const defaultOptions = {
        confirmButtonColor: '#a44390',
        cancelButtonColor: '#22c55e',
        showClass: { popup: '' },
        hideClass: { popup: '' }
    };

    return {
        /**
         * Show a Success alert
         */
        success: function (title, text, timer = 1500) {
            return Swal.fire({
                ...defaultOptions,
                icon: 'success',
                title: title,
                html: text,
                timer: timer,
                showConfirmButton: false
            });
        },

        /**
         * Show an Error alert
         */
        error: function (title, text) {
            return Swal.fire({
                ...defaultOptions,
                icon: 'error',
                title: title,
                html: text
            });
        },

        /**
         * Show a Warning alert
         */
        warning: function (title, text) {
            return Swal.fire({
                ...defaultOptions,
                icon: 'warning',
                title: title,
                html: text
            });
        },

        /**
         * Show a Confirmation dialog (Yes/No)
         * Returns a Promise that resolves if the user clicks "Confirm"
         */
        confirm: function (title, text, confirmBtnText = 'Yes, do it!', cancelBtnText = 'Cancel', confirmButtonColor = '#22c55e', cancelButtonColor = '#ef4444') {
            let opts = {};
            if (typeof title === 'object') {
                opts = title;
            } else {
                opts = {
                    title: title,
                    html: text,
                    confirmButtonText: confirmBtnText,
                    cancelButtonText: cancelBtnText,
                    confirmButtonColor: confirmButtonColor,
                    cancelButtonColor: cancelButtonColor
                };
            }

            return Swal.fire({
                ...defaultOptions,
                title: opts.title,
                html: opts.html || opts.text,
                icon: opts.icon || 'warning',
                showCancelButton: true,
                confirmButtonColor: opts.confirmButtonColor || '#22c55e',
                cancelButtonColor: opts.cancelButtonColor || '#ef4444',
                confirmButtonText: opts.confirmButtonText || opts.confirmText || 'Yes, proceed',
                cancelButtonText: opts.cancelButtonText || opts.cancelText || 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    return Promise.resolve(true);
                } else {
                    return Promise.reject(false);
                }
            });
        }
    };
})();

