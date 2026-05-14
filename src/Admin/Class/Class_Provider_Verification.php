<?php

namespace Cosy\Appointments\Admin;

use Cosy\Appointments\Loader;

class Class_Provider_Verification
{
    public function register(Loader $loader): void
    {
        $loader->add_filter('manage_users_columns', $this, 'add_verify_column');
        $loader->add_filter('manage_users_custom_column', $this, 'populate_verify_column', 10, 3);
        $loader->add_action('admin_footer', $this, 'add_verify_script');
        
        // AJAX handler for updating status
        $loader->add_action('wp_ajax_cosy_update_provider_status', $this, 'handle_status_update');
    }

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

        $html = sprintf('<select class="cosy-verify-dropdown" data-user-id="%d" style="border-radius: 4px; padding: 4px 24px 4px 8px; min-width: 100px;">', esc_attr($user_id));
        foreach ($options as $val_opt => $label) {
            $selected = selected($status, $val_opt, false);
            $html .= sprintf('<option value="%s" %s>%s</option>', esc_attr($val_opt), $selected, esc_html($label));
        }
        $html .= '</select>';
        $html .= '<span class="cosy-verify-spinner spinner" style="float: none; margin: 0 0 0 5px;"></span>';

        return $html;
    }

    public function add_verify_script()
    {
        $screen = get_current_screen();
        if (!$screen || $screen->id !== 'users') {
            return;
        }

        $nonce = wp_create_nonce('cosy_verify_nonce');
        ?>
        <script>
        jQuery(document).ready(function($) {
            $('.cosy-verify-dropdown').on('change', function() {
                var select = $(this);
                var userId = select.data('user-id');
                var status = select.val();
                var spinner = select.next('.spinner');

                select.prop('disabled', true);
                spinner.addClass('is-active');

                $.post(ajaxurl, {
                    action: 'cosy_update_provider_status',
                    security: '<?php echo $nonce; ?>',
                    user_id: userId,
                    status: status
                }, function(response) {
                    select.prop('disabled', false);
                    spinner.removeClass('is-active');

                    if (response.success) {
                        var originalColor = select.css('border-color');
                        select.css('border-color', '#46b450');
                        setTimeout(function() { select.css('border-color', originalColor); }, 1500);
                    } else {
                        alert(response.data || 'Failed to update status.');
                    }
                });
            });
        });
        </script>
        <?php
    }

    public function handle_status_update()
    {
        check_ajax_referer('cosy_verify_nonce', 'security');

        if (!current_user_can('edit_users')) {
            wp_send_json_error('Permission denied.');
        }

        $user_id = intval($_POST['user_id'] ?? 0);
        $status  = sanitize_text_field($_POST['status'] ?? '');

        if (!$user_id || !in_array($status, ['active', 'deactive'])) {
            wp_send_json_error('Invalid data.');
        }

        $old_status = get_user_meta($user_id, 'cosy_provider_status', true);
        
        update_user_meta($user_id, 'cosy_provider_status', $status);

        // Send email if it transitioned to active
        if ($old_status !== 'active' && $status === 'active') {
            $user = get_userdata($user_id);
            if ($user) {
                $subject = "Your Provider Account is Now Active!";
                $message = "Hello {$user->display_name},\n\nCongratulations! Your account has been reviewed and approved by the administrator. Your profile is now live and visible to parents.\n\nThank you,\nCosy Appointments Team";
                wp_mail($user->user_email, $subject, $message);
            }
        }

        wp_send_json_success('Status updated successfully.');
    }
}
