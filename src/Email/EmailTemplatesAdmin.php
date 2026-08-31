<?php

namespace Cosy\Appointments\Email;

use Cosy\Appointments\Loader;
use Cosy\Appointments\Common\GlobalCommonFunctions;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EmailTemplatesAdmin
 * 
 * Manages WP Admin Email Templates Settings Page, Real-Time Previews & Live Test Mail.
 * Allows administrators to customize Email Subjects, Headings, Intro Copy, and Outro Copy
 * while keeping dynamic system data tables bulletproof and leaving official signatures untouched.
 */
class EmailTemplatesAdmin
{
    use GlobalCommonFunctions;

    /**
     * REGISTERS EMAIL TEMPLATES ADMIN HOOKS & AJAX ENDPOINTS
     */
    public function register(Loader $loader): void
    {
        $loader->add_action('wp_ajax_cosy_admin_save_email_template', $this, 'handle_ajax_save_email_template');
        $loader->add_action('wp_ajax_cosy_admin_reset_email_template', $this, 'handle_ajax_reset_email_template');
        $loader->add_action('wp_ajax_cosy_admin_preview_email_template', $this, 'handle_ajax_preview_email_template');
        $loader->add_action('wp_ajax_cosy_admin_send_test_email', $this, 'handle_ajax_send_test_email');
    }

    /**
     * Master registry of customizable email templates.
     */
    public static function get_default_email_templates(): array
    {
        $site_url = home_url();

        return [
            'customer_booking' => [
                'title'      => __('Customer Booking Confirmation', 'cosy-appointments'),
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
                'outro_text' => "<p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Warm regards,</p>",
                'has_table'  => true,
                'table_type' => 'booking_customer',
            ],
            'provider_booking' => [
                'title'      => __('Provider Booking Notification', 'cosy-appointments'),
                'subject'    => __('📅 New Booking Received - {customer_name} has booked a conversation.', 'cosy-appointments'),
                'heading'    => __('New Booking Notification', 'cosy-appointments'),
                'body_text'  => "<p>Hello <strong>{provider_name}</strong>,</p>
<p>Great news! A new customer, <strong>{customer_name}</strong>, has booked a conversation with you.</p>
<p>Please find the booking details below.</p>
<p>You can also log in to your account at any time to view your bookings, update your availability, or manage your profile.</p>
<p>Before the scheduled time, please arrange your conversation with the customer using their chosen communication method, such as setting up a virtual meeting or another agreed way to connect. At the scheduled time, simply begin your conversation.</p>
<p>If you have any questions or need any assistance before your booking, please don't hesitate to contact us at <a href='mailto:contact@cosychats.com' style='color: #a44390; font-weight: 600;'>contact@cosychats.com</a>.</p>
<p>Thank you for being part of CosyChats. We hope you enjoy your upcoming conversation, and we appreciate you helping make conversations based on shared experiences available to more parents.</p>",
                'outro_text' => "<p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Warm regards,</p>",
                'has_table'  => true,
                'table_type' => 'booking_provider',
            ],
            'booking_status' => [
                'title'      => __('Order Status Update', 'cosy-appointments'),
                'subject'    => __('✅ Update: Your CosyChats Appointment Status is now {status} (#{order_id})', 'cosy-appointments'),
                'heading'    => __('Appointment {status}', 'cosy-appointments'),
                'body_text'  => "<p>Hello <strong>{customer_name}</strong>,</p>
<p>Great news! Your booking status with <strong>{provider_name}</strong> has been updated to <strong>{status}</strong>.</p>",
                'outro_text' => "<p>You can view your booking details at any time in your account dashboard.</p>
<p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Kind regards,</p>",
                'has_table'  => true,
                'table_type' => 'booking_status',
            ],
            'booking_cancelled' => [
                'title'      => __('Booking Cancellation Alert', 'cosy-appointments'),
                'subject'    => __('❌ Important Update: Your CosyChats Appointment Has Been Cancelled (#{order_id})', 'cosy-appointments'),
                'heading'    => __('Appointment Cancelled', 'cosy-appointments'),
                'body_text'  => "<p>Hello <strong>{customer_name}</strong>,</p>
<p>We are writing to inform you that your upcoming conversation session with <strong>{provider_name}</strong> has been cancelled by the parent provider.</p>",
                'outro_text' => "<p>We apologize for any inconvenience this may cause. If you have any questions regarding refunds or re-booking another parent, please contact our support team at <a href='mailto:contact@cosychats.com' style='color: #a44390; font-weight: 600;'>contact@cosychats.com</a>.</p>
<p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Kind regards,</p>",
                'has_table'  => true,
                'table_type' => 'booking_cancelled',
            ],
            'review_invite' => [
                'title'      => __('Customer Review Invitation', 'cosy-appointments'),
                'subject'    => __('⭐ How was your session with {provider_name}? Leave a Review', 'cosy-appointments'),
                'heading'    => __('We\'d Love Your Feedback', 'cosy-appointments'),
                'body_text'  => "<p>Hello <strong>{customer_name}</strong>,</p>
<p>Thank you for your recent conversation with <strong>{provider_name}</strong> on CosyChats.</p>
<p>We hope you found the session helpful and meaningful. Your feedback helps other parents discover the right person to talk to, and it means a great deal to the parents who share their experiences on CosyChats.</p>
<p>If you'd like to share your experience, simply click the button below. Your review will be submitted for approval before appearing on the parent's profile.</p>",
                'outro_text' => "<p style='font-size: 13px; color: #64748b; margin-top: 25px;'>If you're having trouble clicking the button, copy and paste the link below into your web browser:</p>
<p style='font-size: 13px; word-break: break-all; color: #a44390;'><a href='{review_url}' style='color: #a44390; text-decoration: none;'>{review_url}</a></p>
<p style='font-size: 13px; color: #94a3b8; margin-top: 20px; font-style: italic;'>This is a one-time link. Once you've submitted your review, the link will no longer be active.</p>
<p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Kind regards,</p>",
                'has_table'  => false,
                'table_type' => 'review_button',
            ],
            'customer_verification' => [
                'title'      => __('Customer Email Verification', 'cosy-appointments'),
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
                'has_table'  => false,
                'table_type' => 'verification_button',
            ],
            'provider_verification' => [
                'title'      => __('Provider Email Verification', 'cosy-appointments'),
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
                'has_table'  => false,
                'table_type' => 'verification_button',
            ],
            'password_reset' => [
                'title'      => __('Password Reset Request', 'cosy-appointments'),
                'subject'    => __('Password Reset Request', 'cosy-appointments'),
                'heading'    => __('Password Reset Request', 'cosy-appointments'),
                'body_text'  => "<p>Hello <strong>{customer_name}</strong>,</p>
<p>You requested a password reset for your account. Please click the button below to set a new password:</p>",
                'outro_text' => "<p>If you did not request this reset, you can safely ignore this email. Your password will remain unchanged.</p>
<p style='word-break: break-all;'><a href='{reset_url}' style='color: #a44390; text-decoration: none;'>{reset_url}</a></p>
<p>Kind regards,</p>",
                'has_table'  => false,
                'table_type' => 'password_reset_button',
            ],
            'gifted_booking' => [
                'title'      => __('Gift Recipient Booking', 'cosy-appointments'),
                'subject'    => __('🎁 A Special Gift For You! You have received a CosyChats conversation', 'cosy-appointments'),
                'heading'    => __('🎁 A Special Gift For You!', 'cosy-appointments'),
                'body_text'  => "<p>Hello <strong>{recipient_name}</strong>,</p>\n<p><strong>{sender_name}</strong> has gifted you a parent conversation session on CosyChats!</p>",
                'outro_text' => "<p>We look forward to welcoming you to your conversation. If you have any questions, contact us at <a href='mailto:contact@CosyChats.com' style='color: #a44390; font-weight: 600;'>contact@CosyChats.com</a>.</p>",
                'has_table'  => true,
                'table_type' => 'gifted_booking',
            ],
            'admin_payment' => [
                'title'      => __('Admin Payment & Booking Alert', 'cosy-appointments'),
                'subject'    => __('🔔 New Secure Payment Received - Order #{order_id}', 'cosy-appointments'),
                'heading'    => __('New Secure Payment Received', 'cosy-appointments'),
                'body_text'  => "<p>Hello Administrator,</p>\n<p>A new payment transaction has been processed and authorized successfully.</p>",
                'outro_text' => "<p>Log in to the WordPress admin panel to manage or inspect this booking.</p>",
                'has_table'  => true,
                'table_type' => 'admin_payment',
            ],
            'admin_provider_setup' => [
                'title'      => __('Admin Provider Setup Alert', 'cosy-appointments'),
                'subject'    => __('New Provider Profile Ready for Review', 'cosy-appointments'),
                'heading'    => __('New Provider Profile', 'cosy-appointments'),
                'body_text'  => "<p style='margin-bottom: 15px;'>Hello Administrator,</p>
<p style='margin-bottom: 15px;'>A Service Provider (Parent) has completed/updated their profile details and is ready for your review and activation.</p>",
                'outro_text' => "<p style='margin-bottom: 20px;'>Please log in to your WP Admin dashboard to review their details and activate their provider profile.</p>
<p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Kind regards,</p>",
                'has_table'  => false,
                'table_type' => 'simple',
            ],
            'provider_review_approved' => [
                'title'      => __('Review Approved Notification', 'cosy-appointments'),
                'subject'    => __('New Parent Review Approved on Your Profile!', 'cosy-appointments'),
                'heading'    => __('Parent Review Approved', 'cosy-appointments'),
                'body_text'  => "<p style='margin-bottom: 15px;'>Hello <strong>{provider_name}</strong>,</p>
<p style='margin-bottom: 15px;'>A new parent review from <strong>{customer_name}</strong> (Rating: <strong>{rating}/10</strong>) has been approved by the Administrator and is now live on your profile page.</p>",
                'outro_text' => "<p style='margin-bottom: 0;'>You can view and post a public response to this review from your <strong>Provider Dashboard &rarr; Parent Reviews</strong> tab.</p>",
                'has_table'  => true,
                'table_type' => 'review_box',
            ],
            'admin_new_review' => [
                'title'      => __('Admin New Review Alert', 'cosy-appointments'),
                'subject'    => __('New Customer Review Submitted for Moderation', 'cosy-appointments'),
                'heading'    => __('New Customer Review', 'cosy-appointments'),
                'body_text'  => "<p style='margin-bottom: 15px;'>Hello Administrator,</p>
<p style='margin-bottom: 15px;'>A new customer review has been submitted for <strong>{provider_name}</strong> and is currently <strong>Pending Approval</strong>.</p>",
                'outro_text' => "<p style='margin-bottom: 0;'>Log in to WP Admin &rarr; <strong>CC Booking &rarr; Reviews</strong> to approve or reject this review.</p>",
                'has_table'  => true,
                'table_type' => 'review_box',
            ],
            'customer_review_reply' => [
                'title'      => __('Review Reply Notification', 'cosy-appointments'),
                'subject'    => __('New Response from {provider_name} on Your Review!', 'cosy-appointments'),
                'heading'    => __('Provider Response Received', 'cosy-appointments'),
                'body_text'  => "<p style='margin-bottom: 15px;'>Hello <strong>{customer_name}</strong>,</p>
<p style='margin-bottom: 15px;'><strong>{provider_name}</strong> has posted a response to your review on CosyChats.</p>",
                'outro_text' => "<p style='margin-bottom: 0;'>You can view the response thread anytime by visiting the parent's profile on CosyChats.</p>",
                'has_table'  => true,
                'table_type' => 'reply_box',
            ],
            'provider_review_followup' => [
                'title'      => __('Review Follow-up Notification', 'cosy-appointments'),
                'subject'    => __('New Follow-up Response from {customer_name} on Review Thread', 'cosy-appointments'),
                'heading'    => __('Customer Follow-up Response', 'cosy-appointments'),
                'body_text'  => "<p style='margin-bottom: 15px;'>Hello <strong>{provider_name}</strong>,</p>
<p style='margin-bottom: 15px;'><strong>{customer_name}</strong> has posted a follow-up response in your review thread on CosyChats.</p>",
                'outro_text' => "<p style='margin-bottom: 0;'>You can log in to your Provider Dashboard or visit your public profile to view and post a final closing response to this review thread.</p>",
                'has_table'  => true,
                'table_type' => 'reply_box',
            ],
            'customer_review_closing' => [
                'title'      => __('Review Closing Response', 'cosy-appointments'),
                'subject'    => __('Review Thread Completed - Final Response from {provider_name}', 'cosy-appointments'),
                'heading'    => __('Review Thread Completed', 'cosy-appointments'),
                'body_text'  => "<p style='margin-bottom: 15px;'>Hello <strong>{customer_name}</strong>,</p>
<p style='margin-bottom: 15px;'><strong>{provider_name}</strong> has posted the final closing response to your review thread on CosyChats. Below is the complete conversation transcript:</p>",
                'outro_text' => "<p style='margin-bottom: 0;'>Thank you for your feedback and active participation on CosyChats!</p>",
                'has_table'  => true,
                'table_type' => 'reply_box',
            ]
        ];
    }

    /**
     * Gets stored option or fallback default template values.
     */
    public static function get_template_settings(string $template_key): array
    {
        $defaults = self::get_default_email_templates();
        $default_data = $defaults[$template_key] ?? [
            'title'      => 'Email Template',
            'subject'    => '',
            'heading'    => '',
            'body_text'  => '',
            'outro_text' => '',
        ];

        $option = get_option('cosy_email_template_' . $template_key, []);

        return [
            'title'      => $default_data['title'],
            'subject'    => !empty($option['subject']) ? $option['subject'] : $default_data['subject'],
            'heading'    => !empty($option['heading']) ? $option['heading'] : $default_data['heading'],
            'body_text'  => isset($option['body_text']) && $option['body_text'] !== '' ? $option['body_text'] : $default_data['body_text'],
            'outro_text' => isset($option['outro_text']) && $option['outro_text'] !== '' ? $option['outro_text'] : $default_data['outro_text'],
        ];
    }

    /**
     * Renders WP Admin Email Templates Settings Page.
     */
    public function render_email_templates_page(): void
    {
        if (!current_user_can('manage_options') && !current_user_can('manage_cosy_appointments')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'cosy-appointments'));
        }

        $controller = $this;
        include COSY_APPT_PATH . 'src/Email/Views/email-templates-page.php';
    }

    /**
     * AJAX handler: Saves email template settings.
     */
    public function handle_ajax_save_email_template(): void
    {
        check_ajax_referer('cosy_admin_email_tpl_nonce', 'security');

        if (!current_user_can('manage_options') && !current_user_can('manage_cosy_appointments')) {
            wp_send_json_error(__('Permission denied.', 'cosy-appointments'));
        }

        $template_key = sanitize_text_field(wp_unslash($_POST['template_key'] ?? ''));
        $subject      = sanitize_text_field(wp_unslash($_POST['subject'] ?? ''));
        $heading      = sanitize_text_field(wp_unslash($_POST['heading'] ?? ''));
        $body_text    = wp_kses_post(wp_unslash($_POST['body_text'] ?? ''));
        $outro_text   = wp_kses_post(wp_unslash($_POST['outro_text'] ?? ''));

        $valid_keys = array_keys(self::get_default_email_templates());
        if (empty($template_key) || !in_array($template_key, $valid_keys, true)) {
            wp_send_json_error(__('Invalid template key.', 'cosy-appointments'));
        }

        $data = [
            'subject'    => $subject,
            'heading'    => $heading,
            'body_text'  => $body_text,
            'outro_text' => $outro_text,
            'updated_at' => current_time('mysql'),
        ];

        update_option('cosy_email_template_' . $template_key, $data);

        if (class_exists('\\Cosy\\Appointments\\Common\\LogManager')) {
            \Cosy\Appointments\Common\LogManager::log(
                'email',
                'email_template_updated',
                sprintf(__('Admin updated email template settings for "%s".', 'cosy-appointments'), $template_key)
            );
        }

        wp_send_json_success(__('Email template saved successfully!', 'cosy-appointments'));
    }

    /**
     * AJAX handler: Resets email template to default values.
     */
    public function handle_ajax_reset_email_template(): void
    {
        check_ajax_referer('cosy_admin_email_tpl_nonce', 'security');

        if (!current_user_can('manage_options') && !current_user_can('manage_cosy_appointments')) {
            wp_send_json_error(__('Permission denied.', 'cosy-appointments'));
        }

        $template_key = sanitize_text_field($_POST['template_key'] ?? '');
        $valid_keys = array_keys(self::get_default_email_templates());

        if (empty($template_key) || !in_array($template_key, $valid_keys, true)) {
            wp_send_json_error(__('Invalid template key.', 'cosy-appointments'));
        }

        delete_option('cosy_email_template_' . $template_key);

        if (class_exists('\\Cosy\\Appointments\\Common\\LogManager')) {
            \Cosy\Appointments\Common\LogManager::log(
                'email',
                'email_template_reset',
                sprintf(__('Admin reset email template settings for "%s" to defaults.', 'cosy-appointments'), $template_key)
            );
        }

        $defaults = self::get_template_settings($template_key);
        wp_send_json_success([
            'message'  => __('Template reset to default values.', 'cosy-appointments'),
            'template' => $defaults
        ]);
    }

    /**
     * Builds realistic sample dynamic tables and elements for live previews and test emails.
     */
    public static function build_sample_element_html(string $template_key): string
    {
        switch ($template_key) {
            case 'customer_booking':
                return "
                    <h4 style='color: #6d2e67; margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1e4ef; padding-bottom: 6px;'>Booking Information Summary:</h4>
                    <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px;'>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; width: 40%; vertical-align: top;'>Order ID:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>#10482</td></tr>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Service Provider:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>Amy Taylor</td></tr>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Customer Name:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>Sarah Jenkins</td></tr>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Customer Email:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>sarah.jenkins@example.com</td></tr>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Start Date:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>08 Sep 2026</td></tr>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Number of Weeks:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>1</td></tr>
                        <tr><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Booking Days:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top; line-height: 1.6;'>Tuesday, 08 Sep 2026: 09:10 AM - 09:40 AM</td></tr>
                    </table>

                    <h4 style='color: #6d2e67; margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1e4ef; padding-bottom: 6px;'>Payment Details:</h4>
                    <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px;'>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; width: 40%; vertical-align: top;'>Experience Cost:</td><td style='padding: 10px 14px; font-weight: bold; text-align: right; color: #1e293b;'>£45.00</td></tr>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Service Fee*:</td><td style='padding: 10px 14px; font-weight: bold; text-align: right; color: #1e293b;'>£2.50</td></tr>
                        <tr style='background-color: #fdf2fb;'><td style='padding: 12px 14px; font-weight: bold; color: #a44390; font-size: 15px;'>Total Paid:</td><td style='padding: 12px 14px; font-weight: bold; text-align: right; color: #a44390; font-size: 15px;'>£47.50</td></tr>
                    </table>
                    <p style='font-size: 11px; color: #64748b; margin-top: 6px; font-style: italic;'>*A small non-refundable fee to help us run our platform safely &amp; smoothly.</p>
                ";

            case 'provider_booking':
                return "
                    <h4 style='color: #6d2e67; margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1e4ef; padding-bottom: 6px;'>Booking Information:</h4>
                    <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px;'>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; width: 40%; vertical-align: top;'>Order ID:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>#10482</td></tr>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Customer Name:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>Sarah Jenkins</td></tr>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Customer Email:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>sarah.jenkins@example.com</td></tr>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Start Date:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>08 Sep 2026</td></tr>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Number of Weeks:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>1</td></tr>
                        <tr><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Booking Days:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top; line-height: 1.6;'>Tuesday, 08 Sep 2026: 09:10 AM - 09:40 AM</td></tr>
                    </table>

                    <h4 style='color: #6d2e67; margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1e4ef; padding-bottom: 6px;'>Payment Details:</h4>
                    <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px;'>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; width: 40%; vertical-align: top;'>Experience Cost:</td><td style='padding: 10px 14px; font-weight: bold; text-align: right; color: #1e293b;'>£45.00</td></tr>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Service Fee*:</td><td style='padding: 10px 14px; font-weight: bold; text-align: right; color: #1e293b;'>£2.50</td></tr>
                        <tr style='background-color: #fdf2fb;'><td style='padding: 12px 14px; font-weight: bold; color: #a44390; font-size: 15px;'>Total Paid:</td><td style='padding: 12px 14px; font-weight: bold; text-align: right; color: #a44390; font-size: 15px;'>£47.50</td></tr>
                    </table>
                    <p style='font-size: 11px; color: #64748b; margin-top: 6px; font-style: italic;'>*A small non-refundable fee to help us run our platform safely &amp; smoothly.</p>
                ";

            case 'booking_status':
                return "
                    <h4 style='color: #6d2e67; margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1e4ef; padding-bottom: 6px;'>Booking Summary:</h4>
                    <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px;'>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; width: 40%; vertical-align: top;'>Order ID:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>#10482</td></tr>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Provider:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>Amy Taylor</td></tr>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Start Date:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>08 Sep 2026</td></tr>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Booking Days:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top; line-height: 1.6;'>Tuesday, 08 Sep 2026: 09:10 AM - 09:40 AM</td></tr>
                        <tr><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Current Status:</td><td style='padding: 10px 14px; color: #a44390; font-weight: bold; vertical-align: top;'>Confirmed</td></tr>
                    </table>
                ";

            case 'booking_cancelled':
                return "
                    <h4 style='color: #6d2e67; margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1e4ef; padding-bottom: 6px;'>Cancelled Booking Details:</h4>
                    <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px;'>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; width: 40%; vertical-align: top;'>Order ID:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>#10482</td></tr>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Provider:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>Amy Taylor</td></tr>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Start Date:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top;'>08 Sep 2026</td></tr>
                        <tr><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; vertical-align: top;'>Booking Days:</td><td style='padding: 10px 14px; color: #334155; vertical-align: top; line-height: 1.6;'>Tuesday, 08 Sep 2026: 09:10 AM - 09:40 AM</td></tr>
                    </table>
                ";

            case 'review_invite':
                $sample_review_url = home_url('/leave-review?token=rev_sample_token_8899');
                return "
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='" . esc_url($sample_review_url) . "' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>Leave Your Review</a>
                    </p>
                ";

            case 'customer_verification':
            case 'provider_verification':
                $sample_verify_url = home_url('/verify-email?action=cosy_verify_customer&uid=123&token=sample_token_abc123');
                return "
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='" . esc_url($sample_verify_url) . "' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>Verify &amp; Activate Account</a>
                    </p>
                ";

            case 'password_reset':
                $sample_reset_url = home_url('/reset-password?key=sample_key_123&login=sarah');
                return "
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='" . esc_url($sample_reset_url) . "' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>Reset Password</a>
                    </p>
                ";

            case 'gifted_booking':
                return "
                    <h4 style='color: #6d2e67; margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1e4ef; padding-bottom: 6px;'>Session Details:</h4>
                    <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px;'>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; width: 40%;'>Service Provider:</td><td style='padding: 10px 14px; color: #334155;'>Amy Taylor</td></tr>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b;'>Start Date:</td><td style='padding: 10px 14px; color: #334155;'>08 Sep 2026</td></tr>
                        <tr><td style='padding: 10px 14px; font-weight: bold; color: #1e293b;'>Booking Days:</td><td style='padding: 10px 14px; color: #334155;'>Tuesday, 08 Sep 2026: 09:10 AM - 09:40 AM</td></tr>
                    </table>
                ";

            case 'admin_payment':
                return "
                    <h4 style='color: #6d2e67; margin-top: 25px; margin-bottom: 12px; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1e4ef; padding-bottom: 6px;'>Order &amp; Payment Summary:</h4>
                    <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px;'>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b; width: 40%;'>Order ID:</td><td style='padding: 10px 14px; color: #334155;'>#10482</td></tr>
                        <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 10px 14px; font-weight: bold; color: #1e293b;'>Total Paid:</td><td style='padding: 10px 14px; color: #a44390; font-weight: bold;'>£47.50</td></tr>
                        <tr><td style='padding: 10px 14px; font-weight: bold; color: #1e293b;'>Gateway:</td><td style='padding: 10px 14px; color: #334155;'>WorldPay HPP</td></tr>
                    </table>
                ";

            case 'provider_review_approved':
            case 'admin_new_review':
                return "
                    <div style='background: #f8fafc; border-left: 4px solid #a44390; padding: 14px; border-radius: 6px; margin: 15px 0;'>
                        <div style='font-size: 16px; margin-bottom: 6px;'>⭐⭐⭐⭐⭐</div>
                        <div style='font-style: italic; color: #334155;'>\"Amy was so thoughtful and supportive! The conversation provided exactly the guidance we were looking for. Highly recommend!\"</div>
                    </div>
                ";

            case 'customer_review_reply':
            case 'provider_review_followup':
            case 'customer_review_closing':
                return "
                    <div style='background: #f8fafc; border-left: 4px solid #a44390; padding: 12px; margin: 15px 0; font-style: italic; color: #334155;'>
                        \"Thank you so much for the lovely feedback! It was a pleasure speaking with you and supporting your family's journey.\"
                    </div>
                ";

            default:
                return "";
        }
    }

    /**
     * Builds the exact full HTML email body including the official global signature
     * from Settings > Email Signature. Untouched and consistent with cosy_send_html_email().
     */
    public static function build_complete_preview_html(string $heading, string $content_html): string
    {
        $year = date('Y');

        // Build Official Email Signature (Exactly matching Helpers.php cosy_send_html_email)
        $sig_html = '';
        if (get_option('cosy_sig_enabled', 1)) {
            $sig_logo = get_option('cosy_sig_logo_url', '');
            if (empty($sig_logo)) {
                $custom_logo_id = get_theme_mod('custom_logo');
                if ($custom_logo_id) {
                    $sig_logo = wp_get_attachment_image_url($custom_logo_id, 'full');
                } else {
                    $sig_logo = get_site_icon_url();
                }
            }

            $sig_name    = get_option('cosy_sig_name', 'The CosyChats Team');
            $sig_title   = get_option('cosy_sig_title', 'Customer Support');
            $sig_phone   = get_option('cosy_sig_phone', '');
            $sig_email   = get_option('cosy_sig_email', '');
            $sig_website = get_option('cosy_sig_website', '');
            $sig_address = get_option('cosy_sig_address', '');
            $sig_li      = get_option('cosy_sig_linkedin', '');
            $sig_fb      = get_option('cosy_sig_facebook', '');
            $sig_ig      = get_option('cosy_sig_instagram', '');
            $sig_tw      = get_option('cosy_sig_twitter', '');
            $sig_tk      = get_option('cosy_sig_tiktok', '');
            $sig_yt      = get_option('cosy_sig_youtube', '');

            $logo_col = '';
            if (!empty($sig_logo)) {
                $logo_col = "
                    <td style=\"width:110px; vertical-align:middle; padding-right:16px;\">
                        <img src=\"" . esc_url($sig_logo) . "\" alt=\"Logo\" width=\"100\" style=\"max-width:100px; height:auto; display:block; border:0;\">
                    </td>
                    <td style=\"width:2px; vertical-align:middle; padding:0 16px 0 0;\">
                        <div style=\"width:2px; height:90px; background:linear-gradient(180deg,#a44390,#6d2e67);\"></div>
                    </td>";
            }

            $contact_rows = '';
            if (!empty($sig_email))   $contact_rows .= "<p style=\"margin:0 0 4px 0; font-size:12px; color:#334155;\">&#9993; <a href=\"mailto:" . esc_attr($sig_email) . "\" style=\"color:#a44390; text-decoration:none;\">" . esc_html($sig_email) . "</a></p>";
            if (!empty($sig_website)) $contact_rows .= "<p style=\"margin:0 0 4px 0; font-size:12px; color:#334155;\">&#127760; <a href=\"" . esc_url($sig_website) . "\" style=\"color:#a44390; text-decoration:none;\">" . esc_html($sig_website) . "</a></p>";
            if (!empty($sig_address)) $contact_rows .= "<p style=\"margin:0 0 8px 0; font-size:12px; color:#334155;\">&#128205; " . esc_html($sig_address) . "</p>";

            $social_badges = '';
            if (!empty($sig_fb))  $social_badges .= "<a href=\"" . esc_url($sig_fb) . "\" target=\"_blank\" style=\"display:inline-block; margin-right:8px; text-decoration:none;\"><img src=\"https://wppremiumplugins.com/cosychats/wp-content/uploads/2026/08/facebook.png\" width=\"26\" height=\"26\" alt=\"Facebook\" style=\"display:inline-block; vertical-align:middle; width:26px; height:26px; border:0;\"></a>";
            if (!empty($sig_tw))  $social_badges .= "<a href=\"" . esc_url($sig_tw) . "\" target=\"_blank\" style=\"display:inline-block; margin-right:8px; text-decoration:none;\"><img src=\"https://wppremiumplugins.com/cosychats/wp-content/uploads/2026/08/X.png\" width=\"26\" height=\"26\" alt=\"X\" style=\"display:inline-block; vertical-align:middle; width:26px; height:26px; border:0;\"></a>";
            if (!empty($sig_ig))  $social_badges .= "<a href=\"" . esc_url($sig_ig) . "\" target=\"_blank\" style=\"display:inline-block; margin-right:8px; text-decoration:none;\"><img src=\"https://wppremiumplugins.com/cosychats/wp-content/uploads/2026/08/instagram.png\" width=\"26\" height=\"26\" alt=\"Instagram\" style=\"display:inline-block; vertical-align:middle; width:26px; height:26px; border:0;\"></a>";
            if (!empty($sig_tk))  $social_badges .= "<a href=\"" . esc_url($sig_tk) . "\" target=\"_blank\" style=\"display:inline-block; margin-right:8px; text-decoration:none;\"><img src=\"https://wppremiumplugins.com/cosychats/wp-content/uploads/2026/08/tiktok.png\" width=\"26\" height=\"26\" alt=\"TikTok\" style=\"display:inline-block; vertical-align:middle; width:26px; height:26px; border:0;\"></a>";
            if (!empty($sig_yt))  $social_badges .= "<a href=\"" . esc_url($sig_yt) . "\" target=\"_blank\" style=\"display:inline-block; margin-right:8px; text-decoration:none;\"><img src=\"https://wppremiumplugins.com/cosychats/wp-content/uploads/2026/08/youtube.png\" width=\"26\" height=\"26\" alt=\"YouTube\" style=\"display:inline-block; vertical-align:middle; width:26px; height:26px; border:0;\"></a>";
            if (!empty($sig_li))  $social_badges .= "<a href=\"" . esc_url($sig_li) . "\" target=\"_blank\" style=\"display:inline-block; margin-right:8px; text-decoration:none;\"><img src=\"https://wppremiumplugins.com/cosychats/wp-content/uploads/2026/08/Linkedin.png\" width=\"26\" height=\"26\" alt=\"LinkedIn\" style=\"display:inline-block; vertical-align:middle; width:26px; height:26px; border:0;\"></a>";

            $sig_html = "
                <div style='margin: 24px 0 0 0; padding: 18px 20px; background: #fdf6fc; border: 1px solid #f1e4ef; border-radius: 10px;'>
                    <table role='presentation' cellpadding='0' cellspacing='0' border='0' width='100%'>
                        <tr>
                            {$logo_col}
                            <td style='vertical-align: middle;'>
                                " . (!empty($sig_name) ? "<p style='margin:0 0 2px 0; font-size:15px; font-weight:700; color:#a44390;'>" . esc_html($sig_name) . "</p>" : '') . "
                                " . (!empty($sig_title) ? "<p style='margin:0 0 8px 0; font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;'>" . esc_html($sig_title) . "</p>" : '') . "
                                {$contact_rows}
                                " . (!empty($social_badges) ? "<p style='margin:4px 0 0 0;'>{$social_badges}</p>" : '') . "
                            </td>
                        </tr>
                    </table>
                </div>";
        }

        $site_name = get_bloginfo('name');
        if (empty($site_name) || strtolower($site_name) === 'cosyplugin' || strtolower($site_name) === 'wordpress') {
            $site_name = 'CosyChats';
        }

        return "
            <div style='background-color: #faf6f9; padding: 30px 15px; font-family: \"Outfit\", -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; color: #1e293b; line-height: 1.6;'>
                <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; border: 1px solid #f1e4ef; box-shadow: 0 10px 25px rgba(109, 46, 103, 0.05); overflow: hidden;'>
                    
                    <!-- Header -->
                    <div style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); padding: 30px 20px; text-align: center; color: #ffffff;'>
                        <h1 style='margin: 0; font-size: 22px; font-weight: 700; color: #ffffff; letter-spacing: -0.3px;'>" . esc_html($heading) . "</h1>
                    </div>

                    <!-- Body -->
                    <div style='padding: 35px 25px; font-size: 15px; line-height: 1.6;'>
                        " . $content_html . "
                        " . $sig_html . "
                    </div>

                    <!-- Footer -->
                    <div style='background-color: #fdf2fb; padding: 20px; text-align: center; font-size: 12px; color: #8a7a88; border-top: 1px solid #f1e4ef;'>
                        &copy; {$year} " . esc_html($site_name) . ". All rights reserved.
                    </div>

                </div>
            </div>
        ";
    }

    /**
     * AJAX handler: Generates live real-time HTML email preview.
     */
    public function handle_ajax_preview_email_template(): void
    {
        check_ajax_referer('cosy_admin_email_tpl_nonce', 'security');

        if (!current_user_can('manage_options') && !current_user_can('manage_cosy_appointments')) {
            wp_send_json_error(__('Permission denied.', 'cosy-appointments'));
        }

        $template_key = sanitize_text_field(wp_unslash($_POST['template_key'] ?? 'customer_booking'));
        $subject      = sanitize_text_field(wp_unslash($_POST['subject'] ?? ''));
        $heading      = sanitize_text_field(wp_unslash($_POST['heading'] ?? ''));
        $body_text    = wp_kses_post(wp_unslash($_POST['body_text'] ?? ''));
        $outro_text   = wp_kses_post(wp_unslash($_POST['outro_text'] ?? ''));

        // Sample replacement data
        $replacements = [
            '{customer_name}'  => 'Sarah Jenkins',
            '{provider_name}'  => 'Amy Taylor',
            '{service_name}'   => 'Family & Parenting Experience',
            '{start_date}'     => '08 Sep 2026',
            '{total_payable}'  => '£47.50',
            '{order_id}'       => '10482',
            '{status}'         => 'Confirmed',
            '{site_url}'       => esc_url(home_url()),
            '{site_name}'      => get_bloginfo('name') ?: 'CosyChats',
            '{support_email}'  => get_option('admin_email') ?: 'contact@cosychats.com',
            '{recipient_name}' => 'Emily Watson',
            '{sender_name}'    => 'Sarah Jenkins',
            '{verify_url}'     => esc_url(home_url('/verify-email?action=cosy_verify_customer&uid=123&token=sample_token_abc123')),
            '{reset_url}'      => esc_url(home_url('/reset-password?key=sample_key_123&login=sarah')),
            '{review_url}'     => esc_url(home_url('/leave-review?token=rev_sample_token_8899')),
        ];

        $render_tags = function ($str) use ($replacements) {
            return str_replace(array_keys($replacements), array_values($replacements), $str);
        };

        $preview_subject = $render_tags($subject);
        $preview_heading = $render_tags($heading);
        $preview_body    = $render_tags($body_text);
        $preview_outro   = $render_tags($outro_text);

        // Protected system tables or action buttons
        $element_html = self::build_sample_element_html($template_key);

        $inner_content = $preview_body . "\n" . $element_html . "\n" . $preview_outro;
        $full_email_html = self::build_complete_preview_html($preview_heading, $inner_content);

        wp_send_json_success([
            'subject' => $preview_subject,
            'html'    => $full_email_html,
        ]);
    }

    /**
     * AJAX handler: Sends real test email to specified recipient address.
     */
    public function handle_ajax_send_test_email(): void
    {
        check_ajax_referer('cosy_admin_email_tpl_nonce', 'security');

        if (!current_user_can('manage_options') && !current_user_can('manage_cosy_appointments')) {
            wp_send_json_error(__('Permission denied.', 'cosy-appointments'));
        }

        $test_email   = sanitize_email(wp_unslash($_POST['test_email'] ?? ''));
        $template_key = sanitize_text_field(wp_unslash($_POST['template_key'] ?? 'customer_booking'));
        $subject      = sanitize_text_field(wp_unslash($_POST['subject'] ?? ''));
        $heading      = sanitize_text_field(wp_unslash($_POST['heading'] ?? ''));
        $body_text    = wp_kses_post(wp_unslash($_POST['body_text'] ?? ''));
        $outro_text   = wp_kses_post(wp_unslash($_POST['outro_text'] ?? ''));

        if (empty($test_email) || !is_email($test_email)) {
            wp_send_json_error(__('Please enter a valid email address to send the test email.', 'cosy-appointments'));
        }

        if (!function_exists('cosy_send_html_email')) {
            wp_send_json_error(__('Mail function cosy_send_html_email is not available.', 'cosy-appointments'));
        }

        // Realistic sample replacement data for testing
        $replacements = [
            '{customer_name}'  => 'Sarah Jenkins',
            '{provider_name}'  => 'Amy Taylor',
            '{service_name}'   => 'Family & Parenting Experience',
            '{start_date}'     => '08 Sep 2026',
            '{total_payable}'  => '£47.50',
            '{order_id}'       => '10482',
            '{status}'         => 'Confirmed',
            '{site_url}'       => esc_url(home_url()),
            '{site_name}'      => get_bloginfo('name') ?: 'CosyChats',
            '{support_email}'  => get_option('admin_email') ?: 'contact@cosychats.com',
            '{recipient_name}' => 'Emily Watson',
            '{sender_name}'    => 'Sarah Jenkins',
            '{verify_url}'     => esc_url(home_url('/verify-email?action=cosy_verify_customer&uid=123&token=sample_token_abc123')),
            '{reset_url}'      => esc_url(home_url('/reset-password?key=sample_key_123&login=sarah')),
            '{review_url}'     => esc_url(home_url('/leave-review?token=rev_sample_token_8899')),
        ];

        $render_tags = function ($str) use ($replacements) {
            return str_replace(array_keys($replacements), array_values($replacements), $str);
        };

        $test_subject = '[TEST] ' . $render_tags($subject);
        $test_heading = $render_tags($heading);
        $test_body    = $render_tags($body_text);
        $test_outro   = $render_tags($outro_text);

        // Protected system tables or action buttons
        $element_html = self::build_sample_element_html($template_key);
        $inner_content = $test_body . "\n" . $element_html . "\n" . $test_outro;

        // Dispatches via cosy_send_html_email() which automatically appends the official signature
        $sent = cosy_send_html_email($test_email, $test_subject, $test_heading, $inner_content, true);

        if ($sent) {
            if (class_exists('\\Cosy\\Appointments\\Common\\LogManager')) {
                \Cosy\Appointments\Common\LogManager::log(
                    'email',
                    'test_email_sent',
                    sprintf(__('Admin sent test email for template "%s" to %s.', 'cosy-appointments'), $template_key, $test_email)
                );
            }
            wp_send_json_success(sprintf(__('Test email dispatched successfully to %s! Please check your inbox or spam folder.', 'cosy-appointments'), esc_html($test_email)));
        } else {
            wp_send_json_error(__('Failed to send test email. Please check your mail server configuration / SMTP settings in Settings.', 'cosy-appointments'));
        }
    }
}
