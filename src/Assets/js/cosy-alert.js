/**
 * COSY ALERT NOTIFICATION CONTROLLER
 * 
 * USE CASE:
 * Centralized SweetAlert2 wrapper for success, error, warning, and confirmation dialogs across frontend.
 * 
 * HOW TO USE:
 * CosyAlert.success('Success', 'Profile saved');
 * CosyAlert.confirm({ title: 'Delete?', text: 'Are you sure?' }).then(...);
 * 
 * WHAT IT DOES INTERNALLY:
 * 1. Pre-configures SweetAlert2 brand colors and animation parameters.
 * 2. Wraps Swal.fire calls into clean JavaScript Promises.
 */
var CosyAlert = (function () {
    
    // Default styling matching the plugin theme
    const defaultOptions = {
        confirmButtonColor: '#a44390',
        cancelButtonColor: '#22c55e',
        showClass: { popup: '' },
        hideClass: { popup: '' }
    };

    /**
     * HELPER: Safely invoke Swal or fallback to browser alert
     */
    function safeSwalFire(options, fallbackMessage) {
        if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
            return Swal.fire(options);
        } else {
            alert(fallbackMessage || options.title || 'Notice');
            return Promise.resolve({ isConfirmed: true });
        }
    }

    return {
        /**
         * SHOW SUCCESS ALERT
         * 
         * USE CASE: Displays quick non-blocking green success toast or popup.
         * HOW TO USE: CosyAlert.success('Saved!', 'Details updated successfully');
         * WHAT IT DOES INTERNALLY: Triggers Swal.fire with icon='success' and auto-dismiss timer. Fallbacks to browser alert if Swal is unavailable.
         */
        success: function (title, text, timer = 1500) {
            const cleanText = (text || '').replace(/<[^>]*>?/gm, '');
            if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                return Swal.fire({
                    ...defaultOptions,
                    icon: 'success',
                    title: title,
                    html: text,
                    timer: timer,
                    showConfirmButton: false
                });
            } else {
                alert((title ? title + '\n' : '') + cleanText);
                return Promise.resolve({ isConfirmed: true });
            }
        },

        /**
         * SHOW ERROR ALERT
         * 
         * USE CASE: Displays red error alert dialog for failed AJAX calls or validation issues.
         * HOW TO USE: CosyAlert.error('Failed', 'Could not save changes');
         * WHAT IT DOES INTERNALLY: Triggers Swal.fire with icon='error' and confirm button. Fallbacks to browser alert if Swal is unavailable.
         */
        error: function (title, text) {
            const cleanText = (text || '').replace(/<[^>]*>?/gm, '');
            if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                return Swal.fire({
                    ...defaultOptions,
                    icon: 'error',
                    title: title,
                    html: text
                });
            } else {
                alert((title ? title + '\n' : '') + cleanText);
                return Promise.resolve({ isConfirmed: true });
            }
        },

        /**
         * SHOW WARNING ALERT
         * 
         * USE CASE: Displays yellow warning alert for incomplete forms or caution notices.
         * HOW TO USE: CosyAlert.warning('Notice', 'Please complete required fields');
         * WHAT IT DOES INTERNALLY: Triggers Swal.fire with icon='warning'. Fallbacks to browser alert if Swal is unavailable.
         */
        warning: function (title, text) {
            const cleanText = (text || '').replace(/<[^>]*>?/gm, '');
            if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                return Swal.fire({
                    ...defaultOptions,
                    icon: 'warning',
                    title: title,
                    html: text
                });
            } else {
                alert((title ? title + '\n' : '') + cleanText);
                return Promise.resolve({ isConfirmed: true });
            }
        },

        /**
         * SHOW CONFIRMATION DIALOG (YES / NO)
         * 
         * USE CASE: Asks user to confirm destructive actions like deleting items or cancelling orders.
         * HOW TO USE: CosyAlert.confirm('Delete Item?', 'This action cannot be undone').then(...);
         * WHAT IT DOES INTERNALLY: Triggers Swal.fire with cancel button and returns a resolving Promise on confirmation. Fallbacks to browser confirm if Swal is unavailable.
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

            if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
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
            } else {
                const cleanText = (opts.html || opts.text || '').replace(/<[^>]*>?/gm, '');
                const userConfirmed = window.confirm((opts.title ? opts.title + '\n' : '') + cleanText);
                return userConfirmed ? Promise.resolve(true) : Promise.reject(false);
            }
        }
    };
})();

