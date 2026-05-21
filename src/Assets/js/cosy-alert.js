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
        confirm: function (title, text, confirmBtnText = 'Yes, do it!', cancelBtnText = 'Cancel') {
            return Swal.fire({
                ...defaultOptions,
                title: title,
                html: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444', // Red for destructive actions by default
                cancelButtonColor: '#22c55e',
                confirmButtonText: confirmBtnText,
                cancelButtonText: cancelBtnText
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
