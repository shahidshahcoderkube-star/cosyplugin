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

        $html = sprintf('<select class="cosy-verify-dropdown" data-user-id="%d">', esc_attr($user_id));
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
                        security: <?php echo wp_json_encode($nonce); ?>,
                        user_id: userId,
                        status: status
                    }, function(response) {
                        select.prop('disabled', false);
                        spinner.removeClass('is-active');

                        if (response.success) {
                            var originalColor = select.css('border-color');
                            select.css('border-color', '#46b450');
                            setTimeout(function() {
                                select.css('border-color', originalColor);
                            }, 1500);
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
        if (empty($old_status)) {
            $old_status = 'active';
        }

        update_user_meta($user_id, 'cosy_provider_status', $status);

        // Send email if status transitions
        if ($old_status !== $status) {
            $user = get_userdata($user_id);
            if ($user) {
                if ($status === 'active') {
                    $subject = "Your Provider Account is Now Active!";
                    $html_content = "
                        <p>Hello <strong>" . esc_html($user->display_name) . "</strong>,</p>
                        <p>Congratulations! Your account has been reviewed and approved by the administrator. Your profile is now live and visible to parents.</p>
                        <p style='text-align: center; margin: 30px 0;'>
                            <a href='" . esc_url(home_url('/login')) . "' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>Login to Your Account</a>
                        </p>
                        <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Thank you,<br><strong>Cosy Appointments Team</strong></p>
                    ";
                    cosy_send_html_email($user->user_email, $subject, "Account Active!", $html_content);
                } elseif ($status === 'deactive') {
                    $subject = "Your Provider Account is Temporarily Deactivated";
                    $html_content = "
                        <p>Hello <strong>" . esc_html($user->display_name) . "</strong>,</p>
                        <p>Your provider account has been temporarily deactivated by the site administrator. During this time, your services will not be bookable and your profile will not be visible to customers.</p>
                        <p>If you believe this is a mistake or have questions, please reach out to our administration/support team.</p>
                        <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Thank you,<br><strong>Cosy Appointments Team</strong></p>
                    ";
                    cosy_send_html_email($user->user_email, $subject, "Account Deactivated", $html_content);
                }
            }
        }

        wp_send_json_success('Status updated successfully.');
    }
}
