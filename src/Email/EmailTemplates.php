<?php

namespace Cosy\Appointments\Email;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EmailTemplates
 * 
 * Centralized registry and provider for ALL dynamic email templates across the plugin.
 * Fully preserves 100% of the original email text, paragraphs, and complete dynamic tables,
 * while allowing client customization through the WordPress Admin dashboard.
 */
class EmailTemplates
{
    /**
     * REPLACE {PLACEHOLDER} TAGS WITH ACTUAL DATA VALUES
     *
     * USE CASE:
     * Core utility used by all template methods to swap dynamic {tags}
     * (e.g. {customer_name}, {order_id}) with real data before sending.
     *
     * HOW TO USE:
     * $content = EmailTemplates::replace_tags($template, ['{customer_name}' => 'Sarah']);
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Performs a bulk str_replace() swapping all tag keys with their values.
     * 2. Returns the final processed string.
     *
     * @param string $content      Template string containing {tag} placeholders.
     * @param array  $replacements Associative array of '{tag}' => 'value' pairs.
     * @return string Processed string with all tags replaced.
     */
    public static function replace_tags(string $content, array $replacements): string
    {
        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * LOAD ADMIN SETTINGS OR FALLBACK TO DEFAULTS, THEN REPLACE PLACEHOLDERS
     *
     * USE CASE:
     * Core internal helper used by every public template method.
     * Merges admin-customized content with plugin defaults and injects data values.
     *
     * HOW TO USE:
     * Internal only — called inside each get_*_template() method.
     * $parts = self::get_rendered_template_parts('customer_booking', $defaults, $replacements);
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Calls EmailTemplatesAdmin::get_template_settings() to load saved admin overrides.
     * 2. Falls back to $defaults if admin has not customized that field.
     * 3. Auto-injects {site_url}, {site_name}, {support_email} tags if not already in $replacements.
     * 4. Calls replace_tags() on each field and returns processed subject, heading, body, outro.
     *
     * @param string $template_key  Unique key for this template (e.g. 'customer_booking').
     * @param array  $defaults      Default subject, heading, body_text, outro_text values.
     * @param array  $replacements  Dynamic {tag} => value pairs to inject into template.
     * @return array Processed array with keys: subject, heading, body_text, outro_text.
     */
    protected static function get_rendered_template_parts(string $template_key, array $defaults, array $replacements): array
    {
        $settings = EmailTemplatesAdmin::get_template_settings($template_key);

        $subject    = !empty($settings['subject']) ? $settings['subject'] : ($defaults['subject'] ?? '');
        $heading    = !empty($settings['heading']) ? $settings['heading'] : ($defaults['heading'] ?? '');
        $body_text  = isset($settings['body_text']) && $settings['body_text'] !== '' ? $settings['body_text'] : ($defaults['body_text'] ?? '');
        $outro_text = isset($settings['outro_text']) && $settings['outro_text'] !== '' ? $settings['outro_text'] : ($defaults['outro_text'] ?? '');

        if (!isset($replacements['{site_url}'])) {
            $replacements['{site_url}'] = esc_url(home_url());
        }
        if (!isset($replacements['{site_name}'])) {
            $site_title = get_bloginfo('name');
            $replacements['{site_name}'] = !empty($site_title) ? esc_html($site_title) : 'CosyChats';
        }
        if (!isset($replacements['{support_email}'])) {
            $admin_mail = get_option('admin_email');
            $replacements['{support_email}'] = !empty($admin_mail) ? esc_html($admin_mail) : 'contact@CosyChats.com';
        }

        return [
            'subject'    => self::replace_tags($subject, $replacements),
            'heading'    => self::replace_tags($heading, $replacements),
            'body_text'  => self::replace_tags($body_text, $replacements),
            'outro_text' => self::replace_tags($outro_text, $replacements),
        ];
    }

    /**
     * 1. CUSTOMER REGISTRATION EMAIL VERIFICATION TEMPLATE
     *
     * USE CASE:
     * Sent automatically when a new customer registers on the site.
     * Contains a secure email verification link to activate their account.
     *
     * HOW TO USE:
     * $tpl = EmailTemplates::get_customer_verification_template($name, $verify_url);
     * cosy_send_html_email($email, $tpl['subject'], $tpl['heading'], $tpl['content']);
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Builds replacements for {customer_name} and {verify_url}.
     * 2. Loads admin-customized or default body text via get_rendered_template_parts().
     * 3. Appends a branded 'Verify & Activate Account' CTA button.
     * 4. Returns array with keys: subject, heading, content.
     *
     * @param string $name       Customer's display name.
     * @param string $verify_url Full URL for email verification.
     * @return array Email parts: subject, heading, content.
     */
    public static function get_customer_verification_template(string $name, string $verify_url): array
    {
        $replacements = [
            '{customer_name}' => esc_html($name),
            '{verify_url}'    => esc_url($verify_url),
        ];

        $defaults = [
            'subject'    => __('Welcome to CosyChats – Please verify your email', 'cosy-appointments'),
            'heading'    => __('Confirm Your Account', 'cosy-appointments'),
            'body_text'  => "<p>Hello <strong>{customer_name}</strong>,</p>
<p>Welcome to CosyChats, and thank you for creating your customer account.</p>
<p>To complete your registration, please click the verification link below. Once your email address has been verified, you'll be able to sign in and start exploring the parents on CosyChats.</p>
<p>Every parent on CosyChats shares their own personal experiences of family life, making it easy to find someone whose journey feels relevant to your own.</p>
<p><strong>Once you're ready, simply:</strong></p>
<ul style='background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px 35px; border-radius: 8px; margin: 10px 0 10px 0; list-style-type: disc;'>
    <li style='margin-bottom: 8px;'>Browse parent profiles.</li>
    <li style='margin-bottom: 8px;'>Choose the parent you'd like to talk to.</li>
    <li>Book a virtual conversation at a time that suits you.</li>
</ul>
<p>If you have any questions or need any assistance getting started, we're always happy to help. Contact the CosyChats team at <a href='mailto:contact@CosyChats.com' style='color: #a44390; font-weight: 600;'>contact@CosyChats.com</a>.</p>
<p>We look forward to welcoming you to your first conversation.</p>
<p style='margin-top: 20px;'>Please click the button below to verify your email address and activate your account:</p>",
            'outro_text' => "<p>If you're having trouble clicking the button, copy and paste the link below into your web browser:</p>
<p style='word-break: break-all;'><a href='{verify_url}' style='color: #a44390; text-decoration: none;'>{verify_url}</a></p>
<p>Kind regards,</p>",
        ];

        $parts = self::get_rendered_template_parts('customer_verification', $defaults, $replacements);

        $action_button_html = "
            <p style='text-align: center; margin: 30px 0;'>
                <a href='" . esc_url($verify_url) . "' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>Verify &amp; Activate Account</a>
            </p>
        ";

        return [
            'subject' => $parts['subject'],
            'heading' => $parts['heading'],
            'content' => $parts['body_text'] . "\n" . $action_button_html . "\n" . $parts['outro_text'],
        ];
    }

    /**
     * 2. PROVIDER REGISTRATION EMAIL VERIFICATION TEMPLATE
     *
     * USE CASE:
     * Sent automatically when a new provider registers on the site.
     * Contains a secure verification link for the provider to activate their account.
     *
     * HOW TO USE:
     * $tpl = EmailTemplates::get_provider_verification_template($name, $verify_url);
     * cosy_send_html_email($email, $tpl['subject'], $tpl['heading'], $tpl['content']);
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Builds replacements for {provider_name} and {verify_url}.
     * 2. Loads admin-customized or default content via get_rendered_template_parts().
     * 3. Appends a branded CTA button.
     * 4. Returns array with keys: subject, heading, content.
     *
     * @param string $name       Provider's display name.
     * @param string $verify_url Full URL for email verification.
     * @return array Email parts: subject, heading, content.
     */
    public static function get_provider_verification_template(string $name, string $verify_url): array
    {
        $replacements = [
            '{provider_name}' => esc_html($name),
            '{customer_name}' => esc_html($name),
            '{verify_url}'    => esc_url($verify_url),
        ];

        $defaults = [
            'subject'    => __('Welcome to CosyChats – Please verify your email', 'cosy-appointments'),
            'heading'    => __('Confirm Your Account', 'cosy-appointments'),
            'body_text'  => "<p>Hello <strong>{provider_name}</strong>,</p>
<p>Welcome to CosyChats, and thank you for registering.</p>
<p>To complete your registration, please click the verification link below. Once your email address has been verified, you'll be able to sign in and continue setting up your parent account.</p>
<p>We're delighted you've chosen to be part of CosyChats.</p>
<p>Our aim is simple: to make it easier for parents to find someone they can have a genuine conversation with, based on shared life experiences. The more people who know about CosyChats, the more parents have the opportunity to discover the platform when they're looking for someone to talk to.</p>
<p>If you believe in what we're building, we'd love your help in spreading the word. Whether it's mentioning CosyChats to friends and family, sharing it on social media, or simply telling someone who might benefit from a conversation, every introduction helps more people discover us.</p>
<p>If you have any questions or need any assistance getting started, we're always happy to help. Contact the CosyChats team at <a href='mailto:contact@CosyChats.com' style='color: #a44390; font-weight: 600;'>contact@CosyChats.com</a>.</p>
<p style='margin-top: 20px;'>Please click the button below to verify your email address and activate your account:</p>",
            'outro_text' => "<p>If you're having trouble clicking the button, copy and paste the link below into your web browser:</p>
<p style='word-break: break-all;'><a href='{verify_url}' style='color: #a44390; text-decoration: none;'>{verify_url}</a></p>
<p>Thank you for helping us make conversations based on shared experiences easier to find.</p>
<p>Kind regards,</p>",
        ];

        $parts = self::get_rendered_template_parts('provider_verification', $defaults, $replacements);

        $action_button_html = "
            <p style='text-align: center; margin: 30px 0;'>
                <a href='" . esc_url($verify_url) . "' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>Verify &amp; Activate Account</a>
            </p>
        ";

        return [
            'subject' => $parts['subject'],
            'heading' => $parts['heading'],
            'content' => $parts['body_text'] . "\n" . $action_button_html . "\n" . $parts['outro_text'],
        ];
    }

    /**
     * 3. FORGOT PASSWORD / PASSWORD RESET EMAIL TEMPLATE
     *
     * USE CASE:
     * Sent when a customer or provider requests a password reset from the login page.
     * Contains a secure one-time WordPress reset link.
     *
     * HOW TO USE:
     * $tpl = EmailTemplates::get_password_reset_template($name, $reset_url);
     * cosy_send_html_email($email, $tpl['subject'], $tpl['heading'], $tpl['content']);
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Builds replacements for {customer_name} and {reset_url}.
     * 2. Loads content via get_rendered_template_parts().
     * 3. Appends a branded 'Reset Password' CTA button.
     * 4. Returns array with keys: subject, heading, content.
     *
     * @param string $name      Recipient's display name.
     * @param string $reset_url Full WordPress password reset URL.
     * @return array Email parts: subject, heading, content.
     */
    public static function get_password_reset_template(string $name, string $reset_url): array
    {
        $replacements = [
            '{customer_name}' => esc_html($name),
            '{reset_url}'     => esc_url($reset_url),
        ];

        $defaults = [
            'subject'    => __('Password Reset Request', 'cosy-appointments'),
            'heading'    => __('Password Reset Request', 'cosy-appointments'),
            'body_text'  => "<p>Hello <strong>{customer_name}</strong>,</p>
<p>You requested a password reset for your account. Please click the button below to set a new password:</p>",
            'outro_text' => "<p>If you did not request this reset, you can safely ignore this email. Your password will remain unchanged.</p>
<p style='word-break: break-all;'><a href='{reset_url}' style='color: #a44390; text-decoration: none;'>{reset_url}</a></p>
<p>Kind regards,</p>",
        ];

        $parts = self::get_rendered_template_parts('password_reset', $defaults, $replacements);

        $action_button_html = "
            <p style='text-align: center; margin: 30px 0;'>
                <a href='" . esc_url($reset_url) . "' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>Reset Password</a>
            </p>
        ";

        return [
            'subject' => $parts['subject'],
            'heading' => $parts['heading'],
            'content' => $parts['body_text'] . "\n" . $action_button_html . "\n" . $parts['outro_text'],
        ];
    }

    /**
     * 4. PROVIDER ACCOUNT APPROVED & ACTIVATED EMAIL TEMPLATE
     *
     * USE CASE:
     * Sent when an admin approves and activates a provider's account for the first time.
     * Welcomes the provider and tells them their profile is now live.
     *
     * HOW TO USE:
     * $tpl = EmailTemplates::get_provider_active_template($name);
     * cosy_send_html_email($email, $tpl['subject'], $tpl['heading'], $tpl['content']);
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Builds replacement for {provider_name}.
     * 2. Loads content via get_rendered_template_parts().
     * 3. Returns array with keys: subject, heading, content.
     *
     * @param string $name Provider's display name.
     * @return array Email parts: subject, heading, content.
     */
    public static function get_provider_active_template(string $name): array
    {
        $replacements = [
            '{provider_name}' => esc_html($name),
            '{login_url}'     => esc_url(home_url('/login')),
        ];

        $defaults = [
            'subject'    => __('Welcome to CosyChats – Your Parent Account Is Now Approved', 'cosy-appointments'),
            'heading'    => __('Account Approved!', 'cosy-appointments'),
            'body_text'  => "<p>Hello <strong>{provider_name}</strong>,</p>
<p>We're delighted to let you know that your CosyChats parent account has now been reviewed and approved.</p>
<p>Thank you for taking the time to join us. We're so pleased to welcome you to the CosyChats community.</p>
<p>You can now get ready to start having conversations with other parents based on shared experiences.</p>
<p>If you believe in what we're building, we'd love your help in spreading the word. Friends, family, and the people who know you best are often the first to tell others about something they genuinely believe in. By mentioning CosyChats or sharing it with people in your own network, you'll be helping more parents discover that these conversations are available when they need them.</p>
<p>If you have any questions or need any assistance getting started, we're always happy to help. Contact the CosyChats team at <a href='mailto:contact@CosyChats.com' style='color: #a44390; font-weight: 600;'>contact@CosyChats.com</a>.</p>
<p>Thank you for being part of CosyChats. We're excited to have you with us.</p>",
            'outro_text' => "<p>Kind regards,</p>",
        ];

        $parts = self::get_rendered_template_parts('provider_active', $defaults, $replacements);

        $action_btn = "
            <p style='text-align: center; margin: 30px 0;'>
                <a href='" . esc_url(home_url('/login')) . "' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>Login to Your Account</a>
            </p>
        ";

        return [
            'subject' => $parts['subject'],
            'heading' => $parts['heading'],
            'content' => $parts['body_text'] . "\n" . $action_btn . "\n" . $parts['outro_text'],
        ];
    }

    /**
     * 5. PROVIDER ACCOUNT DEACTIVATED EMAIL TEMPLATE
     *
     * USE CASE:
     * Sent when an admin deactivates a provider's account.
     * Notifies the provider that their account has been suspended.
     *
     * HOW TO USE:
     * $tpl = EmailTemplates::get_provider_deactivated_template($name);
     * cosy_send_html_email($email, $tpl['subject'], $tpl['heading'], $tpl['content']);
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Builds replacement for {provider_name}.
     * 2. Loads content via get_rendered_template_parts().
     * 3. Returns array with keys: subject, heading, content.
     *
     * @param string $name Provider's display name.
     * @return array Email parts: subject, heading, content.
     */
    public static function get_provider_deactivated_template(string $name): array
    {
        $replacements = ['{provider_name}' => esc_html($name)];

        $defaults = [
            'subject'    => __('Your CosyChats Account is Temporarily Deactivated', 'cosy-appointments'),
            'heading'    => __('Account Deactivated', 'cosy-appointments'),
            'body_text'  => "<p>Hello <strong>{provider_name}</strong>,</p>
<p>Your parent account has been temporarily deactivated by a CosyChats administrator.</p>
<p>While your account is inactive, your profile won't be visible to customers and you won't be able to receive new bookings.</p>
<p>If you have any questions or think this has happened in error, please don't hesitate to get in touch with the CosyChats team. We'll be happy to help.</p>
<p>Contact the CosyChats team at <a href='mailto:contact@CosyChats.com' style='color: #a44390; font-weight: 600;'>contact@CosyChats.com</a>.</p>",
            'outro_text' => "<p>Kind regards,</p>",
        ];

        $parts = self::get_rendered_template_parts('provider_deactivated', $defaults, $replacements);

        return [
            'subject' => $parts['subject'],
            'heading' => $parts['heading'],
            'content' => $parts['body_text'] . "\n" . $parts['outro_text'],
        ];
    }

    /**
     * 6. PROVIDER ACCOUNT RE-ACTIVATED EMAIL TEMPLATE
     *
     * USE CASE:
     * Sent when an admin re-activates a provider who was previously deactivated.
     * Distinguishes from first-time activation by using 'welcome back' messaging.
     *
     * HOW TO USE:
     * $tpl = EmailTemplates::get_provider_reactivated_template($name);
     * cosy_send_html_email($email, $tpl['subject'], $tpl['heading'], $tpl['content']);
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Builds replacement for {provider_name}.
     * 2. Loads content via get_rendered_template_parts().
     * 3. Returns array with keys: subject, heading, content.
     *
     * @param string $name Provider's display name.
     * @return array Email parts: subject, heading, content.
     */
    public static function get_provider_reactivated_template(string $name): array
    {
        $replacements = ['{provider_name}' => esc_html($name)];

        $defaults = [
            'subject'    => __('Your CosyChats Account has been Re-activated', 'cosy-appointments'),
            'heading'    => __('Account Re-activated!', 'cosy-appointments'),
            'body_text'  => "<p>Hello <strong>{provider_name}</strong>,</p>
<p>We're pleased to let you know that your CosyChats parent account has now been reactivated.</p>
<p>You can now sign in as normal, update your availability, manage your profile, and accept new bookings from customers.</p>
<p>We're sorry for any inconvenience caused while your account was unavailable, and we really appreciate your patience and understanding.</p>
<p>If you have any questions or need any assistance, simply reply to this email or get in touch with the CosyChats team—we're always happy to help.</p>
<p>Thank you for being part of CosyChats. We're delighted to have you back.</p>",
            'outro_text' => "<p>Warm wishes,</p>",
        ];

        $parts = self::get_rendered_template_parts('provider_reactivated', $defaults, $replacements);

        return [
            'subject' => $parts['subject'],
            'heading' => $parts['heading'],
            'content' => $parts['body_text'] . "\n" . $parts['outro_text'],
        ];
    }

    /**
     * 7. CUSTOMER BOOKING CONFIRMATION EMAIL TEMPLATE
     *
     * USE CASE:
     * Sent to the customer immediately after a successful booking and payment.
     * Confirms booking details and sets expectations for the upcoming conversation.
     *
     * HOW TO USE:
     * $tpl = EmailTemplates::get_booking_customer_template($booking_data);
     * cosy_send_html_email($email, $tpl['subject'], $tpl['heading'], $tpl['content']);
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Builds replacements from $data array (customer_name, order_id, provider, slots, etc.).
     * 2. Loads content via get_rendered_template_parts().
     * 3. Builds and appends the live booking summary + payment table HTML.
     * 4. Returns array with keys: subject, heading, content.
     *
     * @param array $data Booking data: customer_name, order_id, provider_name, slots, etc.
     * @return array Email parts: subject, heading, content.
     */
    public static function get_booking_customer_template(array $data): array
    {
        $currency = $data['currency_symbol'] ?? '£';
        $order_id = $data['order_id'] ?? $data['appointment_id'] ?? '';
        $service_title = $data['service_title'] ?? $data['service_name'] ?? 'Parent Consultation';
        $formatted_start_date = cosy_format_date($data['start_date'] ?? '');

        $replacements = [
            '{customer_name}'  => esc_html($data['customer_name'] ?? 'Customer'),
            '{provider_name}'  => esc_html($data['provider_name'] ?? 'Provider'),
            '{service_name}'   => esc_html($service_title),
            '{order_id}'       => esc_html($order_id),
            '{start_date}'     => esc_html($formatted_start_date),
            '{total_payable}'  => esc_html($currency . number_format((float)($data['total_payable'] ?? 0), 2)),
        ];

        $defaults = [
            'subject'    => __('🌸 Thank You for Your Booking with CosyChats', 'cosy-appointments'),
            'heading'    => __('Booking Confirmation', 'cosy-appointments'),
            'body_text'  => "<p>Hello <strong>{customer_name}</strong>,</p>
<p>Thank you for booking a conversation through CosyChats.</p>
<p>We're delighted you've chosen CosyChats, and we hope you enjoy your upcoming conversation.</p>
<p>Please find your booking confirmation below.</p>
<p>Before your conversation, you can log in to your account at any time to view your booking details. At the scheduled time, your chosen parent will contact you to begin your conversation.</p>
<p>If you have any questions before your conversation, or if there's anything we can help with, please contact us at <a href='mailto:contact@cosychats.com' style='color: #a44390; font-weight: 600;'>contact@cosychats.com</a>.</p>
<p>After your conversation, we'd love to hear your feedback.</p>
<p>If you enjoy your CosyChats experience, we'd be grateful if you could tell your friends and family about us. Every recommendation helps more parents discover CosyChats and the conversations available.</p>
<p>Thank you again for choosing CosyChats. We really appreciate your support and look forward to welcoming you back.</p>",
            'outro_text' => "<p>Warm regards,</p>",
        ];

        $parts = self::get_rendered_template_parts('customer_booking', $defaults, $replacements);

        $slots_timeline = function_exists('cosy_clean_slots_timeline')
            ? cosy_clean_slots_timeline($data['slots_timeline'] ?? '', $data['start_date'] ?? '', $data['week_days'] ?? '')
            : esc_html($data['slots_timeline'] ?? '');

        $gift_row = !empty($data['is_gift'])
            ? "<tr style='border-bottom: 1px solid #e2e8f0; background-color: #fcf4fa;'><td style='padding: 8px 12px; font-weight: bold; color: #a44390;'>Gifted To 🎁</td><td style='padding: 8px 12px; color: #a44390; font-weight: 600;'>" . esc_html($data['recipient_name'] ?? '') . " (" . esc_html($data['recipient_email'] ?? '') . ")</td></tr>"
            : "";

        $protected_system_tables = "
            <h4 style='color: #6d2e67; margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1e4ef; padding-bottom: 6px;'>Booking Information Summary:</h4>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px;'>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; width: 40%; vertical-align: top;'>Order ID:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>#" . esc_html($order_id) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Service Provider:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($data['provider_name'] ?? '') . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Customer Name:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($data['customer_name'] ?? '') . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Customer Email:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($data['customer_email'] ?? '') . "</td></tr>
                {$gift_row}
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Start Date:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($formatted_start_date) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Number of Weeks:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($data['num_weeks'] ?? '1') . "</td></tr>
                <tr><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Booking Days:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top; line-height: 1.6;'>" . $slots_timeline . "</td></tr>
            </table>

            <h4 style='color: #6d2e67; margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1e4ef; padding-bottom: 6px;'>Payment Details:</h4>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px;'>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; width: 40%; vertical-align: top;'>Experience Cost:</td><td style='padding: 10px 14px; font-weight: bold; text-align: right; color: #1e293b;'>{$currency}" . number_format((float)($data['service_cost'] ?? 0), 2) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Service Fee*:</td><td style='padding: 10px 14px; font-weight: bold; text-align: right; color: #1e293b;'>{$currency}" . number_format((float)($data['service_fee'] ?? 0), 2) . "</td></tr>
                <tr style='background-color: #fdf2fb;'><td style='padding: 12px 14px; font-weight: bold; color: #a44390; font-size: 15px;'>Total Paid:</td><td style='padding: 12px 14px; font-weight: bold; text-align: right; color: #a44390; font-size: 15px;'>{$currency}" . number_format((float)($data['total_payable'] ?? 0), 2) . "</td></tr>
            </table>
            <p style='font-size: 11px; color: #64748b; margin-top: 6px; font-style: italic;'>*A small non-refundable fee to help us run our platform safely &amp; smoothly.</p>
        ";

        return [
            'subject' => $parts['subject'],
            'heading' => $parts['heading'],
            'content' => $parts['body_text'] . "\n" . $protected_system_tables . "\n" . $parts['outro_text'],
        ];
    }

    /**
     * 8. PROVIDER BOOKING NOTIFICATION EMAIL TEMPLATE
     *
     * USE CASE:
     * Sent to the provider immediately after a customer completes a booking.
     * Notifies the provider of the new conversation request with full booking details.
     *
     * HOW TO USE:
     * $tpl = EmailTemplates::get_booking_provider_template($booking_data);
     * cosy_send_html_email($email, $tpl['subject'], $tpl['heading'], $tpl['content']);
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Builds replacements from $data array (provider_name, customer_name, slots, etc.).
     * 2. Loads content via get_rendered_template_parts().
     * 3. Builds and appends the booking details table HTML.
     * 4. Returns array with keys: subject, heading, content.
     *
     * @param array $data Booking data: provider_name, customer_name, order_id, slots, etc.
     * @return array Email parts: subject, heading, content.
     */
    public static function get_booking_provider_template(array $data): array
    {
        $currency = $data['currency_symbol'] ?? '£';
        $order_id = $data['order_id'] ?? $data['appointment_id'] ?? '';
        $customer_name = $data['customer_name'] ?? 'Customer';
        $formatted_start_date = cosy_format_date($data['start_date'] ?? '');

        $replacements = [
            '{customer_name}'  => esc_html($customer_name),
            '{provider_name}'  => esc_html($data['provider_name'] ?? 'Provider'),
            '{service_name}'   => esc_html($data['service_title'] ?? $data['service_name'] ?? 'Parent Consultation'),
            '{order_id}'       => esc_html($order_id),
            '{start_date}'     => esc_html($formatted_start_date),
            '{total_payable}'  => esc_html($currency . number_format((float)($data['total_payable'] ?? 0), 2)),
        ];

        $defaults = [
            'subject'    => sprintf(__('📅 New Booking Received - %s has booked a conversation.', 'cosy-appointments'), $customer_name),
            'heading'    => __('New Booking Notification', 'cosy-appointments'),
            'body_text'  => "<p>Hello <strong>{provider_name}</strong>,</p>
<p>Great news! A new customer, <strong>{customer_name}</strong>, has booked a conversation with you.</p>
<p>Please find the booking details below.</p>
<p>You can also log in to your account at any time to view your bookings, update your availability, or manage your profile.</p>
<p>Before the scheduled time, please arrange your conversation with the customer using their chosen communication method, such as setting up a virtual meeting or another agreed way to connect. At the scheduled time, simply begin your conversation.</p>
<p>If you have any questions or need any assistance before your booking, please don't hesitate to contact us at <a href='mailto:contact@cosychats.com' style='color: #a44390; font-weight: 600;'>contact@cosychats.com</a>.</p>
<p>Thank you for being part of CosyChats. We hope you enjoy your upcoming conversation, and we appreciate you helping make conversations based on shared experiences available to more parents.</p>",
            'outro_text' => "<p>Warm regards,</p>",
        ];

        $parts = self::get_rendered_template_parts('provider_booking', $defaults, $replacements);

        $slots_timeline = function_exists('cosy_clean_slots_timeline')
            ? cosy_clean_slots_timeline($data['slots_timeline'] ?? '', $data['start_date'] ?? '', $data['week_days'] ?? '')
            : esc_html($data['slots_timeline'] ?? '');

        // Check if this booking was purchased as a gift for another person
        $gift_row = !empty($data['is_gift'])
            ? "<tr style='border-bottom: 1px solid #e2e8f0; background-color: #fcf4fa;'><td style='padding: 10px 14px; font-weight: bold; color: #a44390; vertical-align: top;'>Gifted To 🎁:</td><td style='padding: 10px 14px; color: #a44390; font-weight: 600; vertical-align: top;'>" . esc_html($data['recipient_name'] ?? '') . " (" . esc_html($data['recipient_email'] ?? '') . ")</td></tr>"
            : "";

        $protected_system_tables = "
            <h4 style='color: #6d2e67; margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1e4ef; padding-bottom: 6px;'>Booking Information:</h4>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px;'>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; width: 40%; vertical-align: top;'>Order ID:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>#" . esc_html($order_id) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Customer Name:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($data['customer_name'] ?? '') . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Customer Email:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($data['customer_email'] ?? '') . "</td></tr>
                {$gift_row}
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Start Date:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($formatted_start_date) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Number of Weeks:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($data['num_weeks'] ?? '1') . "</td></tr>
                <tr><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Booking Days:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top; line-height: 1.6;'>" . $slots_timeline . "</td></tr>
            </table>

            <h4 style='color: #6d2e67; margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1e4ef; padding-bottom: 6px;'>Payment Details:</h4>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px;'>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; width: 40%; vertical-align: top;'>Experience Cost:</td><td style='padding: 10px 14px; font-weight: bold; text-align: right; color: #1e293b;'>{$currency}" . number_format((float)($data['service_cost'] ?? 0), 2) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Service Fee*:</td><td style='padding: 10px 14px; font-weight: bold; text-align: right; color: #1e293b;'>{$currency}" . number_format((float)($data['service_fee'] ?? 0), 2) . "</td></tr>
                <tr style='background-color: #fdf2fb;'><td style='padding: 12px 14px; font-weight: bold; color: #a44390; font-size: 15px;'>Total Paid:</td><td style='padding: 12px 14px; font-weight: bold; text-align: right; color: #a44390; font-size: 15px;'>{$currency}" . number_format((float)($data['total_payable'] ?? 0), 2) . "</td></tr>
            </table>
            <p style='font-size: 11px; color: #64748b; margin-top: 6px; font-style: italic;'>*A small non-refundable fee to help us run our platform safely &amp; smoothly.</p>
        ";

        return [
            'subject' => $parts['subject'],
            'heading' => $parts['heading'],
            'content' => $parts['body_text'] . "\n" . $protected_system_tables . "\n" . $parts['outro_text'],
        ];
    }

    /**
     * 9. GIFTED BOOKING CONFIRMATION EMAIL TEMPLATE (RECIPIENT)
     *
     * USE CASE:
     * Sent to the gift recipient (friend/family member) when a booking is made as a gift.
     * Informs them of their upcoming conversation booked by the gifter.
     *
     * HOW TO USE:
     * $tpl = EmailTemplates::get_gifted_booking_template($booking_data);
     * cosy_send_html_email($email, $tpl['subject'], $tpl['heading'], $tpl['content']);
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Builds replacements from $data (recipient_name, gifter_name, provider, slots, etc.).
     * 2. Loads content via get_rendered_template_parts().
     * 3. Builds and appends a session summary table.
     * 4. Returns array with keys: subject, heading, content.
     *
     * @param array $data Booking data: recipient_name, gifter_name, provider_name, slots, etc.
     * @return array Email parts: subject, heading, content.
     */
    public static function get_gifted_booking_template(array $data): array
    {
        $recipient_name = $data['recipient_name'] ?? 'Friend';
        $sender_name    = $data['sender_name'] ?? $data['customer_name'] ?? 'A Friend';
        $sender_email   = $data['sender_email'] ?? $data['customer_email'] ?? '';
        $provider_name  = $data['provider_name'] ?? 'Parent Provider';
        $start_date     = $data['start_date'] ?? '';
        $gift_message   = $data['gift_message'] ?? '';

        // Point 2: Use clean sender name without bracketed email
        $sender_disp = esc_html($sender_name);

        // Point 4: Format start date nicely (e.g. 08 Sep 2026)
        $formatted_start_date = $start_date;
        if (!empty($start_date)) {
            $ts = strtotime(str_replace('/', '-', $start_date));
            if ($ts) {
                $formatted_start_date = date('d M Y', $ts);
            }
        }

        $replacements = [
            '{recipient_name}' => esc_html($recipient_name),
            '{sender_name}'    => $sender_disp,
            '{provider_name}'  => esc_html($provider_name),
            '{start_date}'     => esc_html($formatted_start_date),
        ];

        $defaults = [
            'subject'    => __('🎁 A Special Gift For You! You have received a CosyChats conversation', 'cosy-appointments'),
            'heading'    => __('🎁 A Special Gift For You!', 'cosy-appointments'),
            'body_text'  => "<p>Hello <strong>{recipient_name}</strong>,</p>
<p><strong>{sender_name}</strong> has gifted you a parent conversation session on CosyChats!</p>",
            'outro_text' => "<p>Kind regards,</p>",
        ];

        $parts = self::get_rendered_template_parts('gifted_booking', $defaults, $replacements);

        $slots_timeline = function_exists('cosy_clean_slots_timeline')
            ? cosy_clean_slots_timeline($data['slots_timeline'] ?? '', $data['start_date'] ?? '', $data['week_days'] ?? '')
            : esc_html($data['slots_timeline'] ?? '');

        $msg_block = !empty($gift_message)
            ? "<div style='background: #fff0fa; border-left: 4px solid #a44390; padding: 15px; border-radius: 6px; margin: 20px 0;'><p style='margin: 0; font-style: italic; color: #6d2e67;'>\"" . esc_html($gift_message) . "\"</p></div>"
            : "";

        $protected_system_table = $msg_block . "
            <h4 style='color: #6d2e67; margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1e4ef; padding-bottom: 6px;'>Session Details:</h4>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px;'>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; width: 40%; vertical-align: top;'>Service Provider:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($provider_name) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Start Date:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($formatted_start_date) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Number of Weeks:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($data['num_weeks'] ?? $data['number_of_weeks'] ?? '1') . "</td></tr>
                <tr><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Booking Days:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top; line-height: 1.6;'>" . $slots_timeline . "</td></tr>
            </table>
        ";

        return [
            'subject' => $parts['subject'],
            'heading' => $parts['heading'],
            'content' => $parts['body_text'] . "\n" . $protected_system_table . "\n" . $parts['outro_text'],
        ];
    }

    /**
     * 10. VIDEO APPROVAL EMAIL TEMPLATE
     *
     * USE CASE:
     * Sent to a provider when the admin approves their introduction video.
     * Congratulates them and confirms their video is now visible on their profile.
     *
     * HOW TO USE:
     * $tpl = EmailTemplates::get_video_approved_template($name);
     * cosy_send_html_email($email, $tpl['subject'], $tpl['heading'], $tpl['content']);
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Builds replacement for {provider_name}.
     * 2. Loads content via get_rendered_template_parts().
     * 3. Returns array with keys: subject, heading, content.
     *
     * @param string $name Provider's display name.
     * @return array Email parts: subject, heading, content.
     */
    public static function get_video_approved_template(string $name): array
    {
        $dashboard_url = home_url('/provider-dashboard/#profile');
        $replacements = [
            '{provider_name}' => esc_html($name),
            '{dashboard_url}' => esc_url($dashboard_url),
        ];

        $defaults = [
            'subject'    => __('Your Introduction Video Is Now Live', 'cosy-appointments'),
            'heading'    => __('Video Approved!', 'cosy-appointments'),
            'body_text'  => "<p>Hello <strong>{provider_name}</strong>,</p>
<p>Great news! Your introduction video is now live on your CosyChats profile.</p>
<p>Parents visiting your profile can now watch your introduction, helping them get to know you and your experiences before booking a conversation.</p>",
            'outro_text' => "<p>Thank you for taking the time to create your video. It adds a personal touch to your profile and helps bring your story to life.</p>
<p>If you have any questions or need any help, please don't hesitate to get in touch with us at <a href='mailto:contact@cosychats.com' style='color: #a44390; font-weight: 600;'>contact@cosychats.com</a>—we're always happy to help.</p>
<p>Thank you for being part of CosyChats.</p>
<p>Warm wishes,</p>",
        ];

        $parts = self::get_rendered_template_parts('video_approved', $defaults, $replacements);

        $btn = "
            <p style='text-align: center; margin: 30px 0;'>
                <a href='" . esc_url($dashboard_url) . "' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>Go to Dashboard</a>
            </p>
        ";

        return [
            'subject' => $parts['subject'],
            'heading' => $parts['heading'],
            'content' => $parts['body_text'] . "\n" . $btn . "\n" . $parts['outro_text'],
        ];
    }

    /**
     * 11. VIDEO REJECTION / UPDATE REQUIRED EMAIL TEMPLATE
     *
     * USE CASE:
     * Sent to a provider when the admin rejects or requests an update to their introduction video.
     * Includes the rejection reason and guidelines for re-submission.
     *
     * HOW TO USE:
     * $tpl = EmailTemplates::get_video_rejected_template($name, $reason);
     * cosy_send_html_email($email, $tpl['subject'], $tpl['heading'], $tpl['content']);
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Builds replacements for {provider_name} and {rejection_reason}.
     * 2. Loads content via get_rendered_template_parts().
     * 3. Returns array with keys: subject, heading, content.
     *
     * @param string $name   Provider's display name.
     * @param string $reason Admin-provided rejection reason message.
     * @return array Email parts: subject, heading, content.
     */
    public static function get_video_rejected_template(string $name, string $reason): array
    {
        $dashboard_url = home_url('/dashboard');
        $replacements = [
            '{provider_name}' => esc_html($name),
            '{dashboard_url}' => esc_url($dashboard_url),
        ];

        $defaults = [
            'subject'    => __('Your Introduction Video Needs Updating', 'cosy-appointments'),
            'heading'    => __('Video Update Required', 'cosy-appointments'),
            'body_text'  => "<p>Hello <strong>{provider_name}</strong>,</p>
<p>Thank you for uploading your introduction video.</p>
<p>We've reviewed your video and, unfortunately, it isn't quite ready to be published on your CosyChats profile. This could be due to the video quality, format, file size, or because it doesn't meet our video guidelines.</p>",
            'outro_text' => "<p>Please log in to your dashboard to upload a new version.</p>
<p style='text-align: center; margin: 30px 0;'>
    <a href='" . esc_url($dashboard_url) . "' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>Upload New Video</a>
</p>
<p>If you're unsure what needs changing or would like any help creating your video, please don't hesitate to get in touch with us at <a href='mailto:contact@cosychats.com' style='color: #a44390; font-weight: 600;'>contact@cosychats.com</a>. We'll be happy to help you get your video ready to go live.</p>
<p>Thank you for taking the time to update it—we're looking forward to seeing your new introduction.</p>
<p>Warm wishes,</p>",
        ];

        $parts = self::get_rendered_template_parts('video_rejected', $defaults, $replacements);

        $reason_block = !empty($reason)
            ? "<div style='background: #fff5f5; border-left: 4px solid #ef4444; padding: 15px; margin: 20px 0; border-radius: 6px;'><strong style='color: #991b1b;'>Reason / Feedback:</strong><p style='margin: 5px 0 0 0; color: #991b1b;'>" . esc_html($reason) . "</p></div>"
            : "";

        return [
            'subject' => $parts['subject'],
            'heading' => $parts['heading'],
            'content' => $parts['body_text'] . "\n" . $reason_block . "\n" . $parts['outro_text'],
        ];
    }

    /**
     * 12. ADMIN NOTIFICATION - NEW PROVIDER PROFILE READY FOR REVIEW
     *
     * USE CASE:
     * Sent to the site admin when a provider completes their profile setup.
     * Prompts the admin to review the profile and activate the provider's account.
     *
     * HOW TO USE:
     * $tpl = EmailTemplates::get_admin_provider_setup_template($provider_name, $username, $email, $status);
     * cosy_send_html_email($admin_email, $tpl['subject'], $tpl['heading'], $tpl['content']);
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Builds replacements for {provider_name}, {username}, {email}, {status}.
     * 2. Loads content via get_rendered_template_parts().
     * 3. Returns array with keys: subject, heading, content.
     *
     * @param string $provider_name Display name of the newly registered provider.
     * @param string $username      WordPress username of the provider.
     * @param string $email         Provider's email address.
     * @param string $status        Current account status (e.g. 'pending review').
     * @return array Email parts: subject, heading, content.
     */
    public static function get_admin_provider_setup_template(string $provider_name, string $username, string $email, string $status): array
    {
        $admin_review_url = admin_url('admin.php?page=cosy-users');
        $replacements = [
            '{provider_name}' => esc_html($provider_name),
            '{username}'      => esc_html($username),
            '{email}'         => esc_html($email),
            '{status}'        => esc_html(ucwords($status)),
        ];

        $defaults = [
            'subject'    => __('New Provider Profile Ready for Review', 'cosy-appointments'),
            'heading'    => __('New Provider Profile', 'cosy-appointments'),
            'body_text'  => "<p style='margin-bottom: 15px;'>Hello Administrator,</p>
<p style='margin-bottom: 15px;'>A Service Provider (Parent) has completed/updated their profile details and is ready for your review and activation.</p>",
            'outro_text' => "<p style='margin-bottom: 20px;'>Please log in to your WP Admin dashboard to review their details and activate their provider profile.</p>
<p style='text-align: center; margin: 30px 0;'>
    <a href='" . esc_url($admin_review_url) . "' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>Review Provider in WP Admin</a>
</p>
<p>Kind regards,</p>",
        ];

        $parts = self::get_rendered_template_parts('admin_provider_setup', $defaults, $replacements);

        $table = "
            <h4 style='color: #6d2e67; margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1e4ef; padding-bottom: 6px;'>Provider Details Summary:</h4>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px;'>
                <tr style='border-bottom: 1px solid #e2e8f0;'>
                    <td style='padding: 10px 14px; font-weight: bold; color: #1e293b; width: 40%; vertical-align: top;'>Provider Name:</td>
                    <td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($provider_name) . "</td>
                </tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'>
                    <td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Username:</td>
                    <td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($username) . "</td>
                </tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'>
                    <td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Email Address:</td>
                    <td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($email) . "</td>
                </tr>
                <tr>
                    <td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Account Status:</td>
                    <td style='padding: 10px 14px; color: #a44390; font-weight: bold; vertical-align: top;'>" . esc_html(ucwords($status)) . "</td>
                </tr>
            </table>
        ";

        return [
            'subject' => $parts['subject'],
            'heading' => $parts['heading'],
            'content' => $parts['body_text'] . "\n" . $table . "\n" . $parts['outro_text'],
        ];
    }

    /**
     * 13. ADMIN PAYMENT ALERT EMAIL TEMPLATE
     *
     * USE CASE:
     * Sent to the site admin after every successful booking payment.
     * Provides a full order, customer, provider, and payment summary.
     *
     * HOW TO USE:
     * $tpl = EmailTemplates::get_admin_payment_template($booking_data);
     * cosy_send_html_email($admin_email, $tpl['subject'], $tpl['heading'], $tpl['content']);
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Builds replacements from $data (order_id, customer, provider, amount, gateway, etc.).
     * 2. Loads content via get_rendered_template_parts().
     * 3. Builds and appends a full order + payment summary table.
     * 4. Returns array with keys: subject, heading, content.
     *
     * @param array $data Booking data including order_id, customer_name, amount, gateway, etc.
     * @return array Email parts: subject, heading, content.
     */
    public static function get_admin_payment_template(array $data): array
    {
        $order_id = $data['order_id'] ?? $data['appointment_id'] ?? '';
        $currency = $data['currency_symbol'] ?? '£';

        $replacements = [
            '{order_id}'       => esc_html($order_id),
            '{customer_name}'  => esc_html($data['customer_name'] ?? 'Customer'),
            '{provider_name}'  => esc_html($data['provider_name'] ?? 'Provider'),
            '{total_payable}'  => esc_html($currency . number_format((float)($data['total_payable'] ?? 0), 2)),
        ];

        $defaults = [
            'subject'    => sprintf(__('🔔 New Secure Payment Received - Order #%s', 'cosy-appointments'), $order_id),
            'heading'    => __('New Secure Payment Received', 'cosy-appointments'),
            'body_text'  => "<p>Hello Administrator,</p>
<p>A new payment transaction has been processed and authorized successfully.</p>",
            'outro_text' => "",
        ];

        $parts = self::get_rendered_template_parts('admin_payment', $defaults, $replacements);

        $gift_row = !empty($data['is_gift'])
            ? "<tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding:10px 14px;font-weight:bold;color:#1e293b;vertical-align:top;'>Gift Recipient</td><td style='padding: 10px 0; color: #a44390; font-weight: 600;'>" . esc_html($data['recipient_name'] ?? '') . " (" . esc_html($data['recipient_email'] ?? '') . ")</td></tr>"
            : "";

        $card_brand_str   = !empty($data['card_brand']) ? strtoupper($data['card_brand']) : '';
        $card_last4_str   = !empty($data['card_last4']) ? '**** ' . $data['card_last4'] : '';
        $card_display_str = trim($card_brand_str . ' ' . $card_last4_str);
        $card_display_str = !empty($card_display_str) ? esc_html($card_display_str) : 'N/A';

        $auth_code_str  = !empty($data['auth_code']) ? $data['auth_code'] : '';
        $last_event_str = !empty($data['last_event']) ? strtoupper($data['last_event']) : '';
        $auth_parts = [];
        if (!empty($auth_code_str)) {
            $auth_parts[] = $auth_code_str;
        }
        if (!empty($last_event_str)) {
            $auth_parts[] = "({$last_event_str})";
        }
        $auth_display_str = !empty($auth_parts) ? esc_html(implode(' ', $auth_parts)) : 'N/A';

        $slots_timeline = function_exists('cosy_clean_slots_timeline')
            ? cosy_clean_slots_timeline($data['slots_timeline'] ?? '', $data['start_date'] ?? '', $data['week_days'] ?? '')
            : esc_html($data['slots_timeline'] ?? '');

        $formatted_start_date = cosy_format_date($data['start_date'] ?? '');
        $formatted_end_date   = !empty($data['end_date']) && $data['end_date'] !== 'N/A' ? cosy_format_date($data['end_date']) : 'N/A';

        $protected_system_tables = "
            <h4 style='color: #6d2e67; margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1e4ef; padding-bottom: 6px;'>Order Information Summary:</h4>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px;'>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; width: 40%; vertical-align: top;'>Order ID:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>#" . esc_html($order_id) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Experience:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($data['service_title'] ?? 'Parent Experience') . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Customer Name:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($data['customer_name'] ?? '') . " (" . esc_html($data['customer_email'] ?? '') . ")</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Parent Provider:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($data['provider_name'] ?? '') . "</td></tr>
                {$gift_row}
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Start Date:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($formatted_start_date) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>End Date:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($formatted_end_date) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Weekly Schedule:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($data['weekly_type'] ?? 'Standard') . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Week Days Available:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($data['week_days'] ?? '') . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Weeks &amp; Slots:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($data['num_bookings'] ?? '1') . " slots over " . esc_html($data['num_weeks'] ?? '1') . " week(s)</td></tr>
                <tr><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Selected Slots:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . $slots_timeline . "</td></tr>
            </table>

            <h4 style='color: #6d2e67; margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1e4ef; padding-bottom: 6px;'>Financial Details:</h4>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px;'>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; color: #475569; font-weight: 500;'>Provider Revenue Share:</td><td style='padding: 10px 14px; font-weight: bold; text-align: right; color: #1e293b;'>{$currency}" . esc_html($data['service_cost'] ?? '0.00') . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; color: #475569; font-weight: 500;'>Service Fee* (Net):</td><td style='padding: 10px 14px; font-weight: bold; text-align: right; color: #1e293b;'>{$currency}" . esc_html($data['service_fee'] ?? '0.00') . "</td></tr>
                <tr style='background-color: #fdf2fb;'><td style='padding: 12px 14px; font-weight: bold; color: #a44390; font-size: 15px;'>Total Paid:</td><td style='padding: 12px 14px; font-weight: bold; text-align: right; color: #a44390; font-size: 15px;'>{$currency}" . esc_html($data['total_payable'] ?? '0.00') . "</td></tr>
            </table>
            <p style='font-size: 11px; color: #64748b; margin-top: 6px; font-style: italic;'>*A small non-refundable fee to help us run our platform safely &amp; smoothly.</p>

            <h4 style='color: #6d2e67; margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1e4ef; padding-bottom: 6px;'>💳 WorldPay Payment Gateway Details (Admin Only):</h4>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px;'>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; width: 40%; vertical-align: top;'>Payment Gateway:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($data['gateway'] ?? 'WorldPay HPP') . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Transaction Ref ID:</td><td style='padding: 10px 14px; font-family: monospace; font-weight: bold; color: #a44390; vertical-align: top;'>" . esc_html($data['transaction_ref_id'] ?? 'N/A') . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>WorldPay Payment ID:</td><td style='padding: 10px 14px; font-family: monospace; color: #334155; vertical-align: top;'>" . esc_html($data['payment_id'] ?? 'N/A') . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Card Used:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . $card_display_str . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Auth Code / Status:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . $auth_display_str . "</td></tr>
                <tr><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Payment Date &amp; Time:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($data['payment_date'] ?? date('Y-m-d H:i:s')) . "</td></tr>
            </table>
        ";

        return [
            'subject' => $parts['subject'],
            'heading' => $parts['heading'],
            'content' => $parts['body_text'] . "\n" . $protected_system_tables . "\n" . $parts['outro_text'],
        ];
    }

    /**
     * 14. BOOKING CANCELLATION EMAIL TEMPLATE (CUSTOMER)
     *
     * USE CASE:
     * Sent to the customer when a provider cancels their booking.
     * Informs the customer of the cancellation with full booking details.
     *
     * HOW TO USE:
     * $tpl = EmailTemplates::get_booking_cancelled_customer_template($data);
     * cosy_send_html_email($email, $tpl['subject'], $tpl['heading'], $tpl['content']);
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Builds replacements from $data (customer_name, provider_name, order_id, slots, etc.).
     * 2. Loads content via get_rendered_template_parts().
     * 3. Builds and appends the cancelled booking summary table.
     * 4. Returns array with keys: subject, heading, content.
     *
     * @param array $data Booking data: customer_name, provider_name, order_id, slots, etc.
     * @return array Email parts: subject, heading, content.
     */
    public static function get_booking_cancelled_customer_template(array $data): array
    {
        $order_id       = $data['order_id'] ?? '';
        $customer_name  = $data['customer_name'] ?? 'Customer';
        $provider_name  = $data['provider_name'] ?? 'Provider';
        $start_date     = $data['start_date'] ?? '';
        $formatted_start_date = cosy_format_date($start_date);
        $slots_timeline = function_exists('cosy_clean_slots_timeline')
            ? cosy_clean_slots_timeline($data['slots_timeline'] ?? '', $data['start_date'] ?? '', $data['week_days'] ?? '')
            : esc_html($data['slots_timeline'] ?? '');

        $replacements = [
            '{order_id}'       => esc_html($order_id),
            '{customer_name}'  => esc_html($customer_name),
            '{provider_name}'  => esc_html($provider_name),
            '{service_name}'   => esc_html($data['service_title'] ?? 'Parent Conversation'),
            '{start_date}'     => esc_html($formatted_start_date),
        ];

        $defaults = [
            'subject'    => sprintf(__('❌ Important Update: Your CosyChats Appointment Has Been Cancelled (#%s)', 'cosy-appointments'), $order_id),
            'heading'    => __('Appointment Cancelled', 'cosy-appointments'),
            'body_text'  => "<p>Hello <strong>{customer_name}</strong>,</p>
<p>We are writing to inform you that your upcoming conversation session with <strong>{provider_name}</strong> has been cancelled by the parent provider.</p>",
            'outro_text' => "<p>We apologize for any inconvenience this may cause. If you have any questions regarding refunds or re-booking another parent, please contact our support team at <a href='mailto:contact@cosychats.com' style='color: #a44390; font-weight: 600;'>contact@cosychats.com</a>.</p>
<p>Kind regards,</p>",
        ];

        $parts = self::get_rendered_template_parts('booking_cancelled', $defaults, $replacements);

        $table = "
            <h4 style='color: #6d2e67; margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1e4ef; padding-bottom: 6px;'>Cancelled Booking Details:</h4>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px;'>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; width: 40%; vertical-align: top;'>Order ID:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>#" . esc_html($order_id) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Provider:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($provider_name) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Start Date:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($formatted_start_date) . "</td></tr>
                <tr><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Booking Days:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top; line-height: 1.6;'>" . $slots_timeline . "</td></tr>
            </table>
        ";

        return [
            'subject' => $parts['subject'],
            'heading' => $parts['heading'],
            'content' => $parts['body_text'] . "\n" . $table . "\n" . $parts['outro_text'],
        ];
    }

    /**
     * 15. BOOKING STATUS UPDATE EMAIL TEMPLATE (CUSTOMER)
     *
     * USE CASE:
     * Sent to the customer whenever a provider updates the status of their booking
     * (e.g. from 'pending' to 'confirmed', or 'confirmed' to 'completed').
     *
     * HOW TO USE:
     * $tpl = EmailTemplates::get_booking_status_update_customer_template($data);
     * cosy_send_html_email($email, $tpl['subject'], $tpl['heading'], $tpl['content']);
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Builds replacements from $data (customer_name, provider_name, new_status, slots, etc.).
     * 2. Loads content via get_rendered_template_parts().
     * 3. Builds and appends a booking status summary table.
     * 4. Returns array with keys: subject, heading, content.
     *
     * @param array $data Booking data: customer_name, provider_name, status, order_id, etc.
     * @return array Email parts: subject, heading, content.
     */
    public static function get_booking_status_update_customer_template(array $data): array
    {
        $order_id       = $data['order_id'] ?? '';
        $status_label   = ucfirst($data['status'] ?? 'Updated');
        $customer_name  = $data['customer_name'] ?? 'Customer';
        $provider_name  = $data['provider_name'] ?? 'Provider';
        $start_date     = $data['start_date'] ?? '';
        $formatted_start_date = cosy_format_date($start_date);
        $slots_timeline = function_exists('cosy_clean_slots_timeline')
            ? cosy_clean_slots_timeline($data['slots_timeline'] ?? '', $data['start_date'] ?? '', $data['week_days'] ?? '')
            : esc_html($data['slots_timeline'] ?? '');

        $replacements = [
            '{order_id}'       => esc_html($order_id),
            '{status}'         => esc_html($status_label),
            '{customer_name}'  => esc_html($customer_name),
            '{provider_name}'  => esc_html($provider_name),
            '{start_date}'     => esc_html($formatted_start_date),
        ];

        $defaults = [
            'subject'    => sprintf(__('✅ Update: Your CosyChats Appointment Status is now %s (#%s)', 'cosy-appointments'), $status_label, $order_id),
            'heading'    => sprintf(__('Appointment %s', 'cosy-appointments'), $status_label),
            'body_text'  => "<p>Hello <strong>{customer_name}</strong>,</p>
<p>Great news! Your booking status with <strong>{provider_name}</strong> has been updated to <strong>{status}</strong>.</p>",
            'outro_text' => "<p>You can view your booking details at any time in your account dashboard.</p>
<p>Kind regards,</p>",
        ];

        $parts = self::get_rendered_template_parts('booking_status', $defaults, $replacements);

        $table = "
            <h4 style='color: #6d2e67; margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1e4ef; padding-bottom: 6px;'>Booking Summary:</h4>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px;'>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; width: 40%; vertical-align: top;'>Order ID:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>#" . esc_html($order_id) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Provider:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($provider_name) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Start Date:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($formatted_start_date) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Booking Days:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top; line-height: 1.6;'>" . $slots_timeline . "</td></tr>
                <tr><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Current Status:</td><td style='padding: 10px 14px; color: #a44390; font-weight: bold; vertical-align: top;'>" . esc_html($status_label) . "</td></tr>
            </table>
        ";

        return [
            'subject' => $parts['subject'],
            'heading' => $parts['heading'],
            'content' => $parts['body_text'] . "\n" . $table . "\n" . $parts['outro_text'],
        ];
    }

    /**
     * 16. REVIEW INVITE EMAIL TEMPLATE
     *
     * USE CASE:
     * Sent to the customer after their booking is marked as 'completed'.
     * Invites them to leave a review for the provider via a secure one-time token link.
     *
     * HOW TO USE:
     * $tpl = EmailTemplates::get_review_invite_template($data);
     * cosy_send_html_email($email, $tpl['subject'], $tpl['heading'], $tpl['content']);
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Builds replacements from $data (customer_name, provider_name, service_title, review_url).
     * 2. Loads content via get_rendered_template_parts().
     * 3. Appends a branded 'Leave Your Review' CTA button.
     * 4. Returns array with keys: subject, heading, content.
     *
     * @param array $data Data: customer_name, provider_name, service_title, review_url.
     * @return array Email parts: subject, heading, content.
     */
    public static function get_review_invite_template(array $data): array
    {
        $customer_name = esc_html($data['customer_name'] ?? 'Customer');
        $provider_name = esc_html($data['provider_name'] ?? 'Parent');
        $review_url    = esc_url($data['review_url'] ?? '#');

        $replacements = [
            '{customer_name}' => $customer_name,
            '{provider_name}' => $provider_name,
            '{review_url}'    => $review_url,
        ];

        $defaults = [
            'subject'    => sprintf(__('⭐ How was your session with %s? Leave a Review', 'cosy-appointments'), $data['provider_name'] ?? 'Parent'),
            'heading'    => __('We\'d Love Your Feedback', 'cosy-appointments'),
            'body_text'  => "<p>Hello <strong>{customer_name}</strong>,</p>
<p>Thank you for your recent conversation with <strong>{provider_name}</strong> on CosyChats.</p>
<p>We hope you found the session helpful and meaningful. Your feedback helps other parents discover the right person to talk to, and it means a great deal to the parents who share their experiences on CosyChats.</p>
<p>If you'd like to share your experience, simply click the button below. Your review will be submitted for approval before appearing on the parent's profile.</p>",
            'outro_text' => "<p>If you're having trouble clicking the button, copy and paste the link below into your web browser:</p>
<p style='word-break: break-all;'><a href='{review_url}' style='color: #a44390; text-decoration: none;'>{review_url}</a></p>
<p style='font-style: italic;'>This is a one-time link. Once you've submitted your review, the link will no longer be active.</p>
<p>Kind regards,</p>",
        ];

        $parts = self::get_rendered_template_parts('review_invite', $defaults, $replacements);

        $btn = "
            <p style='text-align: center; margin: 30px 0;'>
                <a href='{$review_url}' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>Leave Your Review</a>
            </p>
        ";

        return [
            'subject' => $parts['subject'],
            'heading' => $parts['heading'],
            'content' => $parts['body_text'] . "\n" . $btn . "\n" . $parts['outro_text'],
        ];
    }

    /**
     * 17. PROVIDER REVIEW APPROVED NOTIFICATION EMAIL TEMPLATE
     *
     * USE CASE:
     * Sent to the provider when an admin approves a customer review on their profile.
     * Notifies them of the star rating and the positive feedback received.
     *
     * HOW TO USE:
     * $tpl = EmailTemplates::get_provider_review_approved_template($provider_name, $customer_name, $rating, $review);
     * cosy_send_html_email($email, $tpl['subject'], $tpl['heading'], $tpl['content']);
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Builds replacements for provider_name, customer_name, rating, and review text.
     * 2. Loads content via get_rendered_template_parts().
     * 3. Returns array with keys: subject, heading, content.
     *
     * @param string $provider_name Provider's display name.
     * @param string $customer_name Customer's display name who left the review.
     * @param int    $rating        Star rating (1-5).
     * @param string $review        Review text content.
     * @return array Email parts: subject, heading, content.
     */
    public static function get_provider_review_approved_template(string $provider_name, string $customer_name, int $rating, string $review): array
    {
        $replacements = [
            '{provider_name}' => esc_html($provider_name),
            '{customer_name}' => esc_html($customer_name),
            '{rating}'        => (string)$rating,
            '{review_text}'   => esc_html($review),
        ];

        $defaults = [
            'subject'    => __('New Parent Review Approved on Your Profile!', 'cosy-appointments'),
            'heading'    => __('Parent Review Approved', 'cosy-appointments'),
            'body_text'  => "<p style='margin-bottom: 15px;'>Hello <strong>{provider_name}</strong>,</p>
<p style='margin-bottom: 15px;'>A new parent review from <strong>{customer_name}</strong> (Rating: <strong>{rating}/10</strong>) has been approved by the Administrator and is now live on your profile page.</p>",
            'outro_text' => "<p style='margin-bottom: 0;'>You can view and post a public response to this review from your <strong>Provider Dashboard &rarr; Parent Reviews</strong> tab.</p>",
        ];

        $parts = self::get_rendered_template_parts('provider_review_approved', $defaults, $replacements);

        $blockquote = "
            <blockquote style='background: #fdf5fc; border-left: 4px solid #a44390; padding: 12px 16px; margin: 15px 0; font-style: italic;'>\"" . esc_html($review) . "\"</blockquote>
        ";

        return [
            'subject' => $parts['subject'],
            'heading' => $parts['heading'],
            'content' => $parts['body_text'] . "\n" . $blockquote . "\n" . $parts['outro_text'],
        ];
    }

    /**
     * 18. ADMIN NEW REVIEW SUBMITTED ALERT EMAIL TEMPLATE
     *
     * USE CASE:
     * Sent to the admin when a customer submits a new review.
     * Prompts the admin to approve or reject the review from the Admin > Reviews page.
     *
     * HOW TO USE:
     * $tpl = EmailTemplates::get_admin_new_review_template($provider_name, $customer_name, $rating, $review);
     * cosy_send_html_email($admin_email, $tpl['subject'], $tpl['heading'], $tpl['content']);
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Builds replacements for provider_name, customer_name, rating, and review text.
     * 2. Loads content via get_rendered_template_parts().
     * 3. Returns array with keys: subject, heading, content.
     *
     * @param string $provider_name Provider being reviewed.
     * @param string $customer_name Reviewer's name.
     * @param int    $rating        Star rating submitted (1-5).
     * @param string $review        Review text content.
     * @return array Email parts: subject, heading, content.
     */
    public static function get_admin_new_review_template(string $provider_name, string $customer_name, int $rating, string $review): array
    {
        $replacements = [
            '{provider_name}' => esc_html($provider_name),
            '{customer_name}' => esc_html($customer_name),
            '{rating}'        => (string)$rating,
            '{review_text}'   => esc_html($review),
        ];

        $defaults = [
            'subject'    => __('New Customer Review Submitted for Moderation', 'cosy-appointments'),
            'heading'    => __('New Customer Review', 'cosy-appointments'),
            'body_text'  => "<p style='margin-bottom: 15px;'>Hello Administrator,</p>
<p style='margin-bottom: 15px;'>A new customer review has been submitted for <strong>{provider_name}</strong> and is currently <strong>Pending Approval</strong>.</p>",
            'outro_text' => "<p style='margin-bottom: 0;'>Log in to WP Admin &rarr; <strong>CC Booking &rarr; Reviews</strong> to approve or reject this review.</p>",
        ];

        $parts = self::get_rendered_template_parts('admin_new_review', $defaults, $replacements);

        $table = "
            <h4 style='color: #6d2e67; margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1e4ef; padding-bottom: 6px;'>Review Details Summary:</h4>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px;'>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; width: 40%; vertical-align: top;'>Parent Provider:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($provider_name) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Customer:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>" . esc_html($customer_name) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Rating:</td><td style='padding: 10px 14px; color: #a44390; font-weight: bold; vertical-align: top;'>" . (int)$rating . "/10</td></tr>
                <tr><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Review Text:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top; font-style: italic;'>\"" . esc_html($review) . "\"</td></tr>
            </table>
        ";

        return [
            'subject' => $parts['subject'],
            'heading' => $parts['heading'],
            'content' => $parts['body_text'] . "\n" . $table . "\n" . $parts['outro_text'],
        ];
    }

    /**
     * 19. CUSTOMER REVIEW REPLY EMAIL TEMPLATE
     *
     * USE CASE:
     * Sent to the customer when a provider responds to their review for the first time.
     * Notifies the customer that their review has received a reply.
     *
     * HOW TO USE:
     * $tpl = EmailTemplates::get_customer_review_reply_template($customer_name, $provider_name, $reply, $original_review);
     * cosy_send_html_email($email, $tpl['subject'], $tpl['heading'], $tpl['content']);
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Builds replacements for customer_name, provider_name, reply_text, and original review.
     * 2. Loads content via get_rendered_template_parts().
     * 3. Returns array with keys: subject, heading, content.
     *
     * @param string $customer_name   Customer's display name.
     * @param string $provider_name   Provider's display name.
     * @param string $reply_text      Provider's reply text.
     * @param string $original_review The customer's original review text.
     * @return array Email parts: subject, heading, content.
     */
    public static function get_customer_review_reply_template(string $customer_name, string $provider_name, string $reply_text, string $original_review): array
    {
        $replacements = [
            '{customer_name}' => esc_html($customer_name),
            '{provider_name}' => esc_html($provider_name),
            '{reply_text}'    => esc_html($reply_text),
            '{original_review}' => esc_html($original_review),
        ];

        $defaults = [
            'subject'    => sprintf(__('New Response from %s on Your Review!', 'cosy-appointments'), $provider_name),
            'heading'    => __('Provider Response Received', 'cosy-appointments'),
            'body_text'  => "<p style='margin-bottom: 15px;'>Hello <strong>{customer_name}</strong>,</p>
<p style='margin-bottom: 15px;'><strong>{provider_name}</strong> has posted a response to your review on CosyChats.</p>",
            'outro_text' => "<p style='margin-bottom: 0;'>You can view the response thread anytime by visiting the parent's profile on CosyChats.</p>",
        ];

        $parts = self::get_rendered_template_parts('customer_review_reply', $defaults, $replacements);

        $thread_box = "
            <div style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin: 15px 0;'>
                <p style='margin: 0 0 8px 0; font-size: 13px; color: #64748b;'><strong>Your Original Review:</strong></p>
                <p style='margin: 0 0 15px 0; font-style: italic; color: #334155;'>\"" . esc_html($original_review) . "\"</p>
                <div style='border-top: 1px dashed #cbd5e1; padding-top: 12px;'>
                    <p style='margin: 0 0 6px 0; font-size: 13px; color: #a44390;'><strong>" . esc_html($provider_name) . "'s Response:</strong></p>
                    <p style='margin: 0; color: #1e293b; font-weight: 500;'>\"" . esc_html($reply_text) . "\"</p>
                </div>
            </div>
        ";

        return [
            'subject' => $parts['subject'],
            'heading' => $parts['heading'],
            'content' => $parts['body_text'] . "\n" . $thread_box . "\n" . $parts['outro_text'],
        ];
    }

    /**
     * 20. CUSTOMER FOLLOW-UP RESPONSE EMAIL TEMPLATE (TO PROVIDER)
     *
     * USE CASE:
     * Sent to the provider when a customer adds a follow-up reply to the review thread.
     * Notifies the provider that the conversation is continuing.
     *
     * HOW TO USE:
     * $tpl = EmailTemplates::get_provider_review_followup_template($provider_name, $customer_name, $reply, $original_review);
     * cosy_send_html_email($email, $tpl['subject'], $tpl['heading'], $tpl['content']);
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Builds replacements for provider_name, customer_name, reply_text, and original review.
     * 2. Loads content via get_rendered_template_parts().
     * 3. Returns array with keys: subject, heading, content.
     *
     * @param string $provider_name   Provider's display name.
     * @param string $customer_name   Customer's display name.
     * @param string $reply_text      Customer's follow-up reply text.
     * @param string $original_review The original review text.
     * @return array Email parts: subject, heading, content.
     */
    public static function get_provider_review_followup_template(string $provider_name, string $customer_name, string $reply_text, string $original_review): array
    {
        $replacements = [
            '{provider_name}' => esc_html($provider_name),
            '{customer_name}' => esc_html($customer_name),
            '{followup_text}' => esc_html($reply_text),
            '{original_review}' => esc_html($original_review),
        ];

        $defaults = [
            'subject'    => sprintf(__('New Follow-up Response from %s on Review Thread', 'cosy-appointments'), $customer_name),
            'heading'    => __('Customer Follow-up Response', 'cosy-appointments'),
            'body_text'  => "<p style='margin-bottom: 15px;'>Hello <strong>{provider_name}</strong>,</p>
<p style='margin-bottom: 15px;'><strong>{customer_name}</strong> has posted a follow-up response in your review thread on CosyChats.</p>",
            'outro_text' => "<p style='margin-bottom: 0;'>You can log in to your Provider Dashboard or visit your public profile to view and post a final closing response to this review thread.</p>",
        ];

        $parts = self::get_rendered_template_parts('provider_review_followup', $defaults, $replacements);

        $box = "
            <div style='background: #fdf5fc; border: 1px solid rgba(164, 67, 144, 0.2); border-radius: 8px; padding: 15px; margin: 15px 0;'>
                <p style='margin: 0 0 8px 0; font-size: 13px; color: #64748b;'><strong>Original Review Thread:</strong></p>
                <p style='margin: 0 0 15px 0; font-style: italic; color: #334155;'>\"" . esc_html($original_review) . "\"</p>
                <div style='border-top: 1px dashed #cbd5e1; padding-top: 12px;'>
                    <p style='margin: 0 0 6px 0; font-size: 13px; color: #a44390;'><strong>" . esc_html($customer_name) . "'s Follow-up Response:</strong></p>
                    <p style='margin: 0; color: #1e293b; font-weight: 500;'>\"" . esc_html($reply_text) . "\"</p>
                </div>
            </div>
        ";

        return [
            'subject' => $parts['subject'],
            'heading' => $parts['heading'],
            'content' => $parts['body_text'] . "\n" . $box . "\n" . $parts['outro_text'],
        ];
    }

    /**
     * 21. PROVIDER FINAL CLOSING RESPONSE EMAIL TEMPLATE (TO CUSTOMER)
     *
     * USE CASE:
     * Sent to the customer when a provider sends a final closing reply to a review thread.
     * Includes the full conversation history (original review, provider reply, customer follow-up, closing reply).
     *
     * HOW TO USE:
     * $tpl = EmailTemplates::get_customer_review_closing_template($customer_name, $provider_name, $review, $l1_reply, $l2_reply, $closing);
     * cosy_send_html_email($email, $tpl['subject'], $tpl['heading'], $tpl['content']);
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Builds replacements including full conversation thread text.
     * 2. Loads content via get_rendered_template_parts().
     * 3. Returns array with keys: subject, heading, content.
     *
     * @param string $customer_name   Customer's display name.
     * @param string $provider_name   Provider's display name.
     * @param string $original_review Customer's original review text.
     * @param string $l1_reply        Provider's first reply.
     * @param string $l2_reply        Customer's follow-up response.
     * @param string $closing_reply   Provider's final closing reply.
     * @return array Email parts: subject, heading, content.
     */
    public static function get_customer_review_closing_template(string $customer_name, string $provider_name, string $original_review, string $l1_reply, string $l2_reply, string $closing_reply): array
    {
        $replacements = [
            '{customer_name}' => esc_html($customer_name),
            '{provider_name}' => esc_html($provider_name),
        ];

        $defaults = [
            'subject'    => sprintf(__('Review Thread Completed - Final Response from %s', 'cosy-appointments'), $provider_name),
            'heading'    => __('Review Thread Completed', 'cosy-appointments'),
            'body_text'  => "<p style='margin-bottom: 15px;'>Hello <strong>{customer_name}</strong>,</p>
<p style='margin-bottom: 15px;'><strong>{provider_name}</strong> has posted the final closing response to your review thread on CosyChats. Below is the complete conversation transcript:</p>",
            'outro_text' => "<p style='margin-bottom: 0;'>Thank you for your feedback and active participation on CosyChats!</p>",
        ];

        $parts = self::get_rendered_template_parts('customer_review_closing', $defaults, $replacements);

        $box = "
            <div style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 20px 0;'>
                <div style='margin-bottom: 15px; padding-bottom: 12px; border-bottom: 1px dashed #cbd5e1;'>
                    <p style='margin: 0 0 4px 0; font-size: 12px; color: #64748b; font-weight: 700; text-transform: uppercase;'>1. Your Original Review (" . esc_html($customer_name) . "):</p>
                    <p style='margin: 0; color: #334155; font-style: italic;'>\"" . esc_html($review_text) . "\"</p>
                </div>
                <div style='margin-bottom: 15px; padding-bottom: 12px; border-bottom: 1px dashed #cbd5e1;'>
                    <p style='margin: 0 0 4px 0; font-size: 12px; color: #a44390; font-weight: 700; text-transform: uppercase;'>2. Provider Response (" . esc_html($provider_name) . "):</p>
                    <p style='margin: 0; color: #1e293b; font-weight: 500;'>\"" . esc_html($l1_text) . "\"</p>
                </div>
                " . (!empty($l2_text) ? "
                <div style='margin-bottom: 15px; padding-bottom: 12px; border-bottom: 1px dashed #cbd5e1;'>
                    <p style='margin: 0 0 4px 0; font-size: 12px; color: #475569; font-weight: 700; text-transform: uppercase;'>3. Your Follow-up (" . esc_html($customer_name) . "):</p>
                    <p style='margin: 0; color: #334155; font-style: italic;'>\"" . esc_html($l2_text) . "\"</p>
                </div>" : "") . "
                <div>
                    <p style='margin: 0 0 4px 0; font-size: 12px; color: #16a34a; font-weight: 700; text-transform: uppercase;'>4. Final Closing Response (" . esc_html($provider_name) . "):</p>
                    <p style='margin: 0; color: #1e293b; font-weight: 500;'>\"" . esc_html($l3_text) . "\"</p>
                </div>
            </div>
        ";

        return [
            'subject' => $parts['subject'],
            'heading' => $parts['heading'],
            'content' => $parts['body_text'] . "\n" . $box . "\n" . $parts['outro_text'],
        ];
    }

    /**
     * CENTRALIZED SINGLE-LINE EMAIL DISPATCHER HELPER
     *
     * USE CASE:
     * Convenient single-line method to build and send any registered template email
     * without manually calling the get_*_template() method and cosy_send_html_email() separately.
     *
     * HOW TO USE:
     * $sent = EmailTemplates::send('customer_booking', $email, $booking_data);
     *
     * WHAT IT DOES INTERNALLY:
     * 1. Validates that $to_email is not empty and cosy_send_html_email() function exists.
     * 2. Dynamically constructs the correct get_*_template() method name from $template_name.
     * 3. Calls that method with $data to build the email payload.
     * 4. Dispatches the email via cosy_send_html_email() and returns true/false result.
     *
     * @param string $template_name Registered template key (e.g. 'customer_booking').
     * @param string $to_email      Recipient email address.
     * @param array  $data          Data payload passed to the template method.
     * @return bool True if email sent successfully, false otherwise.
     */
    public static function send(string $template_name, string $to_email, array $data = []): bool
    {
        if (empty($to_email) || !function_exists('cosy_send_html_email')) {
            return false;
        }

        $template = null;

        switch ($template_name) {
            case 'customer_verification':
                $template = self::get_customer_verification_template($data['name'] ?? '', $data['verify_url'] ?? '');
                break;
            case 'provider_verification':
                $template = self::get_provider_verification_template($data['name'] ?? '', $data['verify_url'] ?? '');
                break;
            case 'password_reset':
                $template = self::get_password_reset_template($data['name'] ?? '', $data['reset_url'] ?? '');
                break;
            case 'provider_active':
                $template = self::get_provider_active_template($data['name'] ?? '');
                break;
            case 'provider_deactivated':
                $template = self::get_provider_deactivated_template($data['name'] ?? '');
                break;
            case 'provider_reactivated':
                $template = self::get_provider_reactivated_template($data['name'] ?? '');
                break;
            case 'customer_booking_confirmation':
            case 'customer_booking':
                $template = self::get_booking_customer_template($data);
                break;
            case 'provider_booking_notification':
            case 'provider_booking':
                $template = self::get_booking_provider_template($data);
                break;
            case 'gifted_booking_recipient':
            case 'gifted_booking':
                $template = self::get_gifted_booking_template($data);
                break;
            case 'video_approved':
                $template = self::get_video_approved_template($data['name'] ?? '');
                break;
            case 'video_rejected':
                $template = self::get_video_rejected_template($data['name'] ?? '', $data['reason'] ?? '');
                break;
            case 'admin_provider_setup':
                $template = self::get_admin_provider_setup_template(
                    $data['provider_name'] ?? '',
                    $data['username'] ?? '',
                    $data['email'] ?? '',
                    $data['status'] ?? 'pending'
                );
                break;
            case 'admin_payment':
                $template = self::get_admin_payment_template($data);
                break;
            case 'booking_cancelled':
                $template = self::get_booking_cancelled_customer_template($data);
                break;
            case 'booking_status':
                $template = self::get_booking_status_update_customer_template($data);
                break;
            case 'review_invite':
                $template = self::get_review_invite_template($data);
                break;
            case 'provider_review_approved':
                $template = self::get_provider_review_approved_template(
                    $data['provider_name'] ?? '',
                    $data['customer_name'] ?? '',
                    (int)($data['rating'] ?? 5),
                    $data['review_text'] ?? ''
                );
                break;
            case 'admin_new_review':
                $template = self::get_admin_new_review_template(
                    $data['provider_name'] ?? '',
                    $data['customer_name'] ?? '',
                    (int)($data['rating'] ?? 5),
                    $data['review_text'] ?? ''
                );
                break;
            case 'customer_review_reply':
                $template = self::get_customer_review_reply_template(
                    $data['customer_name'] ?? '',
                    $data['provider_name'] ?? '',
                    $data['reply_text'] ?? '',
                    $data['original_review'] ?? ''
                );
                break;
            case 'provider_review_followup':
                $template = self::get_provider_review_followup_template(
                    $data['provider_name'] ?? '',
                    $data['customer_name'] ?? '',
                    $data['followup_text'] ?? '',
                    $data['original_review'] ?? ''
                );
                break;
            case 'customer_review_closing':
                $template = self::get_customer_review_closing_template(
                    $data['customer_name'] ?? '',
                    $data['provider_name'] ?? '',
                    $data['review_text'] ?? '',
                    $data['l1_text'] ?? '',
                    $data['l2_text'] ?? '',
                    $data['l3_text'] ?? ''
                );
                break;
        }

        if (!$template || empty($template['subject']) || empty($template['content'])) {
            return false;
        }

        $heading = $template['heading'] ?? get_bloginfo('name');
        return (bool) cosy_send_html_email(
            $to_email,
            $template['subject'],
            $heading,
            $template['content'],
            true
        );
    }
}
