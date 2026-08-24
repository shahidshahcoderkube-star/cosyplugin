<?php

namespace Cosy\Appointments\Admin;

use Cosy\Appointments\Loader;

class Class_Provider_Verification
{
    use \Cosy\Appointments\Common\GlobalCommonFunctions;

    /**
     * REGISTERS PROVIDER VERIFICATION HOOKS & AJAX ENDPOINTS
     * 
     * USE CASE:
     * Called during plugin load sequence to hook user list table verification columns and status toggle handlers.
     * 
     * HOW TO USE:
     * (new Class_Provider_Verification())->register($loader);
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Attaches custom column hooks 'manage_users_columns' and 'manage_users_custom_column'.
     * 2. Attaches admin footer JS renderer for AJAX status toggling.
     * 3. Attaches 'wp_ajax_cosy_update_provider_status' to handle_status_update callback.
     * 
     * @param Loader $loader Plugin loader instance.
     */
    public function register(Loader $loader): void
    {
        $loader->add_filter('manage_users_columns', $this, 'add_verify_column');
        $loader->add_filter('manage_users_custom_column', $this, 'populate_verify_column', 10, 3);

        // AJAX handler for updating status
        $loader->add_action('wp_ajax_cosy_update_provider_status', $this, 'handle_status_update');
    }

    /**
     * ADDS VERIFY COLUMN TO WP USER TABLE
     * 
     * USE CASE:
     * Adds 'Verify' column header to WP Admin -> Users list table.
     * 
     * HOW TO USE:
     * Triggered automatically by WordPress filter 'manage_users_columns'.
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Appends 'cosy_verify' key to columns array.
     * 
     * @param array $columns WP User table columns array.
     * @return array        Modified columns array.
     */
    public function add_verify_column($columns)
    {
        $columns['cosy_verify'] = 'Verify';
        return $columns;
    }

    public function populate_verify_column($val, $column_name, $user_id)
    {
        if ($column_name !== 'cosy_verify') {
            return $val;
        }

        $user = get_userdata($user_id);
        if (!$user || !in_array('provider', (array) $user->roles)) {
            return 'N/A';
        }

        $status = get_user_meta($user_id, 'cosy_provider_status', true);

        // Empty means active for existing users before this feature
        if (empty($status)) {
            $status = 'active';
            // We can opportunistically save it to avoid future empty checks
            update_user_meta($user_id, 'cosy_provider_status', 'active');
        }

        $options = [
            'deactive' => 'Deactivate',
            'active'   => 'Activate'
        ];

        $html = sprintf('<select class="cosy-verify-dropdown" data-user-id="%d">', esc_attr($user_id));
        foreach ($options as $val_opt => $label) {
            $selected = selected($status, $val_opt, false);
            $html .= sprintf('<option value="%s" %s>%s</option>', esc_attr($val_opt), $selected, esc_html($label));
        }
        $html .= '</select>';
        $html .= '<span class="cosy-verify-spinner spinner" style="float: none; margin: 0 0 0 5px;"></span>';

        return $html;
    }

    /**
     * AJAX Handler: Update Provider Account Verification Status (Active/Deactive).
     * 
     * USE CASE:
     * Triggered when an administrator changes the dropdown status for a provider in WP Admin -> Users.
     * 
     * WHAT IT DOES:
     * 1. Verifies security via verify_admin_ajax_request('cosy_verify_nonce', 'edit_users').
     * 2. Updates 'cosy_provider_status' user meta key to 'active' or 'deactive'.
     * 3. Sends appropriate activation / reactivation / deactivation email to provider.
     * 4. Logs status transition activity to LogManager and flushes directory transient cache.
     */
    public function handle_status_update()
    {
        $this->verify_admin_ajax_request('cosy_admin_nonce', 'edit_users');

        $user_id = intval($_POST['user_id'] ?? 0);
        $status  = sanitize_text_field($_POST['status'] ?? '');

        if (!$user_id || !in_array($status, ['active', 'deactive'])) {
            wp_send_json_error('Invalid data.');
        }

        $old_status = get_user_meta($user_id, 'cosy_provider_status', true);
        if (empty($old_status)) {
            $old_status = 'active';
        }

        update_user_meta($user_id, 'cosy_provider_status', $status);
        $this->flush_provider_transients();

        $user = get_userdata($user_id);
        $user_display = $user ? $user->display_name . ' (' . $user->user_email . ')' : 'ID ' . $user_id;
        \Cosy\Appointments\Common\LogManager::log(
            'users',
            'status_updated',
            sprintf(__('Admin updated provider "%s" status from "%s" to "%s" via Users list page.', 'cosy-appointments'), $user_display, $old_status, $status)
        );

        // Send email if status transitions
        if ($old_status !== $status) {
            if ($user) {
                if ($status === 'active') {
                    $was_ever_activated = (bool) get_user_meta($user_id, 'cosy_was_ever_activated', true);
                    if ($was_ever_activated) {
                        // User was previously active, deactivated, and now re-activated
                        $tpl = \Cosy\Appointments\Common\EmailTemplates::get_provider_reactivated_template($user->display_name);
                    } else {
                        // First time activation by Admin
                        $tpl = \Cosy\Appointments\Common\EmailTemplates::get_provider_active_template($user->display_name);
                        update_user_meta($user_id, 'cosy_was_ever_activated', 1);
                    }
                    cosy_send_html_email($user->user_email, $tpl['subject'], $tpl['heading'], $tpl['content']);
                } elseif ($status === 'deactive') {
                    $tpl = \Cosy\Appointments\Common\EmailTemplates::get_provider_deactivated_template($user->display_name);
                    cosy_send_html_email($user->user_email, $tpl['subject'], $tpl['heading'], $tpl['content']);
                }
            }
        }

        $msg = ($status === 'active')
            ? __('Provider status set to Active.', 'cosy-appointments')
            : __('Provider status set to Deactive.', 'cosy-appointments');

        wp_send_json_success($msg);
    }
}
