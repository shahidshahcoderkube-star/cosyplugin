<?php

namespace Cosy\Appointments\Common;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class EmailTemplates
 * 
 * Centralized registry and provider for ALL email templates used across the CosyChats plugin.
 * Modify subjects, headings, and HTML body contents in this single file.
 */
class EmailTemplates
{
    /**
     * 1. Customer Registration Verification Email
     */
    public static function get_customer_verification_template(string $name, string $verify_url): array
    {
        $subject = __('Welcome to CosyChats – Please verify your email', 'cosy-appointments');
        $heading = __('Confirm Your Account', 'cosy-appointments');

        $html_content = "
            <p>Hello <strong>" . esc_html($name) . "</strong>,</p>
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
            <p style='margin-top: 20px;'>Please click the button below to verify your email address and activate your account:</p>
            <p style='text-align: center; margin: 30px 0;'>
                <a href='" . esc_url($verify_url) . "' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>Verify &amp; Activate Account</a>
            </p>
            <p style='font-size: 13px; color: #64748b; margin-top: 25px;'>If you're having trouble clicking the button, copy and paste the link below into your web browser:</p>
            <p style='font-size: 13px; word-break: break-all; color: #a44390;'><a href='" . esc_url($verify_url) . "' style='color: #a44390; text-decoration: none;'>" . esc_html($verify_url) . "</a></p>
            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Kind regards,</p>
        ";

        return [
            'subject' => $subject,
            'heading' => $heading,
            'content' => $html_content,
        ];
    }

    /**
     * 2. Provider Registration Verification Email
     */
    public static function get_provider_verification_template(string $name, string $verify_url): array
    {
        $subject = __('Welcome to CosyChats – Please verify your email', 'cosy-appointments');
        $heading = __('Confirm Your Account', 'cosy-appointments');

        $html_content = "
            <p>Hello <strong>" . esc_html($name) . "</strong>,</p>
            <p>Welcome to CosyChats, and thank you for registering.</p>
            <p>To complete your registration, please click the verification link below. Once your email address has been verified, you'll be able to sign in and continue setting up your parent account.</p>
            <p>We're delighted you've chosen to be part of CosyChats.</p>
            <p>Our aim is simple: to make it easier for parents to find someone they can have a genuine conversation with, based on shared life experiences. The more people who know about CosyChats, the more parents have the opportunity to discover the platform when they're looking for someone to talk to.</p>
            <p>If you believe in what we're building, we'd love your help in spreading the word. Whether it's mentioning CosyChats to friends and family, sharing it on social media, or simply telling someone who might benefit from a conversation, every introduction helps more people discover us.</p>
            <p>If you have any questions or need any assistance getting started, we're always happy to help. Contact the CosyChats team at <a href='mailto:contact@CosyChats.com' style='color: #a44390; font-weight: 600;'>contact@CosyChats.com</a>.</p>
            <p style='margin-top: 20px;'>Please click the button below to verify your email address and activate your account:</p>
            <p style='text-align: center; margin: 30px 0;'>
                <a href='" . esc_url($verify_url) . "' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>Verify &amp; Activate Account</a>
            </p>
            <p style='font-size: 13px; color: #64748b; margin-top: 25px;'>If you're having trouble clicking the button, copy and paste the link below into your web browser:</p>
            <p style='font-size: 13px; word-break: break-all; color: #a44390;'><a href='" . esc_url($verify_url) . "' style='color: #a44390; text-decoration: none;'>" . esc_html($verify_url) . "</a></p>
            <p style='margin-top: 25px;'>Thank you for helping us make conversations based on shared experiences easier to find.</p>
            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 15px;'>Kind regards,</p>
        ";

        return [
            'subject' => $subject,
            'heading' => $heading,
            'content' => $html_content,
        ];
    }

    /**
     * 3. Forgot Password / Password Reset Email
     */
    public static function get_password_reset_template(string $name, string $reset_url): array
    {
        $subject = __('Password Reset Request', 'cosy-appointments');
        $heading = __('Password Reset Request', 'cosy-appointments');

        $html_content = "
            <p>Hello <strong>" . esc_html($name) . "</strong>,</p>
            <p>You requested a password reset for your account. Please click the button below to set a new password:</p>
            <p style='text-align: center; margin: 30px 0;'>
                <a href='" . esc_url($reset_url) . "' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>Reset Password</a>
            </p>
            <p style='font-size: 13px; color: #64748b; margin-top: 25px;'>If you did not request this reset, you can safely ignore this email. Your password will remain unchanged.</p>
            <p style='font-size: 13px; word-break: break-all; color: #a44390;'><a href='" . esc_url($reset_url) . "' style='color: #a44390; text-decoration: none;'>" . esc_html($reset_url) . "</a></p>
            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Kind regards,</p>
        ";

        return [
            'subject' => $subject,
            'heading' => $heading,
            'content' => $html_content,
        ];
    }

    /**
     * 4. Provider Account Approved & Activated Email
     */
    public static function get_provider_active_template(string $name): array
    {
        $subject = __('Welcome to CosyChats – Your Parent Account Is Now Approved', 'cosy-appointments');
        $heading = __('Account Approved!', 'cosy-appointments');

        $html_content = "
            <p>Hello <strong>" . esc_html($name) . "</strong>,</p>
            <p>We're delighted to let you know that your CosyChats parent account has now been reviewed and approved.</p>
            <p>Thank you for taking the time to join us. We're so pleased to welcome you to the CosyChats community.</p>
            <p>You can now get ready to start having conversations with other parents based on shared experiences.</p>
            <p>If you believe in what we're building, we'd love your help in spreading the word. Friends, family, and the people who know you best are often the first to tell others about something they genuinely believe in. By mentioning CosyChats or sharing it with people in your own network, you'll be helping more parents discover that these conversations are available when they need them.</p>
            <p>If you have any questions or need any assistance getting started, we're always happy to help. Contact the CosyChats team at <a href='mailto:contact@CosyChats.com' style='color: #a44390; font-weight: 600;'>contact@CosyChats.com</a>.</p>
            <p>Thank you for being part of CosyChats. We're excited to have you with us.</p>
            <p style='text-align: center; margin: 30px 0;'>
                <a href='" . esc_url(home_url('/login')) . "' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>Login to Your Account</a>
            </p>
            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Kind regards,</p>
        ";

        return [
            'subject' => $subject,
            'heading' => $heading,
            'content' => $html_content,
        ];
    }

    /**
     * 5. Provider Account Deactivated Email
     */
    public static function get_provider_deactivated_template(string $name): array
    {
        $subject = __('Your CosyChats Account is Temporarily Deactivated', 'cosy-appointments');
        $heading = __('Account Deactivated', 'cosy-appointments');

        $html_content = "
            <p>Hello <strong>" . esc_html($name) . "</strong>,</p>
            <p>Your parent account has been temporarily deactivated by a CosyChats administrator.</p>
            <p>While your account is inactive, your profile won't be visible to customers and you won't be able to receive new bookings.</p>
            <p>If you have any questions or think this has happened in error, please don't hesitate to get in touch with the CosyChats team. We'll be happy to help.</p>
            <p>Contact the CosyChats team at <a href='mailto:contact@CosyChats.com' style='color: #a44390; font-weight: 600;'>contact@CosyChats.com</a>.</p>
            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Kind regards,</p>
        ";

        return [
            'subject' => $subject,
            'heading' => $heading,
            'content' => $html_content,
        ];
    }

    /**
     * 6. Provider Account Re-Activated Email
     */
    public static function get_provider_reactivated_template(string $name): array
    {
        $subject = __('Your CosyChats Account has been Re-activated', 'cosy-appointments');
        $heading = __('Account Re-activated!', 'cosy-appointments');

        $html_content = "
            <p>Hello <strong>" . esc_html($name) . "</strong>,</p>
            <p>We're pleased to let you know that your CosyChats parent account has now been reactivated.</p>
            <p>You can now sign in as normal, update your availability, manage your profile, and accept new bookings from customers.</p>
            <p>We're sorry for any inconvenience caused while your account was unavailable, and we really appreciate your patience and understanding.</p>
            <p>If you have any questions or need any assistance, simply reply to this email or get in touch with the CosyChats team—we're always happy to help.</p>
            <p>Thank you for being part of CosyChats. We're delighted to have you back.</p>
            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Warm wishes,</p>
        ";

        return [
            'subject' => $subject,
            'heading' => $heading,
            'content' => $html_content,
        ];
    }

    /**
     * 6. Booking Confirmation Email (Customer)
     */
    public static function get_booking_customer_template(array $data): array
    {
        $subject = __('🌸 Thank You for Your Booking with CosyChats', 'cosy-appointments');
        $heading = __('Booking Confirmation', 'cosy-appointments');

        $currency = $data['currency_symbol'] ?? '£';
        $gift_row = !empty($data['is_gift'])
            ? "<tr style='border-bottom: 1px solid #e2e8f0; background-color: #fcf4fa;'><td style='padding: 8px 12px; font-weight: bold; color: #a44390;'>Gifted To 🎁</td><td style='padding: 8px 12px; color: #a44390; font-weight: 600;'>" . esc_html($data['recipient_name'] ?? '') . " (" . esc_html($data['recipient_email'] ?? '') . ")</td></tr>"
            : "";

        $order_id = $data['order_id'] ?? $data['appointment_id'] ?? '';

        $html_content = "
            <p>Hello <strong>" . esc_html($data['customer_name']) . "</strong>,</p>
            <p>Thank you for booking a conversation through CosyChats.</p>
            <p>We're delighted you've chosen CosyChats, and we hope you enjoy your upcoming conversation.</p>
            <p>Please find your booking confirmation below.</p>
            <p>Before your conversation, you can log in to your account at any time to view your booking details. At the scheduled time, your chosen parent will contact you to begin your conversation.</p>
            <p>If you have any questions before your conversation, or if there's anything we can help with, please contact us at <a href='mailto:contact@cosychats.com' style='color: #a44390; font-weight: 600;'>contact@cosychats.com</a>.</p>
            <p>After your conversation, we'd love to hear your feedback.</p>
            <p>If you enjoy your CosyChats experience, we'd be grateful if you could tell your friends and family about us. Every recommendation helps more parents discover CosyChats and the conversations available.</p>
            <p>Thank you again for choosing CosyChats. We really appreciate your support and look forward to welcoming you back.</p>

            <h4 style='color: #6d2e67; margin-top: 25px; border-bottom: 2px solid #f1e4ef; padding-bottom: 6px;'>Booking Information Summary:</h4>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;'>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 12px; font-weight: bold; width: 40%;'>Order ID:</td><td style='padding: 8px 12px;'>#" . esc_html($order_id) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 12px; font-weight: bold;'>Experience Booked:</td><td style='padding: 8px 12px;'>" . esc_html($data['service_title']) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 12px; font-weight: bold;'>Service Provider:</td><td style='padding: 8px 12px;'>" . esc_html($data['provider_name']) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 12px; font-weight: bold;'>Customer Name:</td><td style='padding: 8px 12px;'>" . esc_html($data['customer_name']) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 12px; font-weight: bold;'>Customer Email:</td><td style='padding: 8px 12px;'>" . esc_html($data['customer_email']) . "</td></tr>
                {$gift_row}
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 12px; font-weight: bold;'>Start Date:</td><td style='padding: 8px 12px;'>" . esc_html($data['start_date']) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 12px; font-weight: bold;'>End Date:</td><td style='padding: 8px 12px;'>" . esc_html($data['end_date']) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 12px; font-weight: bold;'>Weekly Schedule:</td><td style='padding: 8px 12px;'>" . esc_html($data['weekly_type']) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 12px; font-weight: bold;'>Number of Weeks:</td><td style='padding: 8px 12px;'>" . esc_html($data['num_weeks']) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 12px; font-weight: bold;'>Week Days:</td><td style='padding: 8px 12px;'>" . esc_html($data['week_days']) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 12px; font-weight: bold;'>Total Booked Slots:</td><td style='padding: 8px 12px;'>" . esc_html($data['num_bookings']) . " slots</td></tr>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Selected Slots:</td><td style='padding: 8px 12px;'>" . esc_html($data['slots_timeline']) . "</td></tr>
            </table>

            <h4 style='color: #6d2e67; margin-top: 25px; border-bottom: 2px solid #f1e4ef; padding-bottom: 6px;'>Payment Details:</h4>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;'>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 12px;'>Experience Cost:</td><td style='padding: 8px 12px; font-weight: bold; text-align: right;'>" . esc_html($currency . number_format($data['service_cost'], 2)) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 12px;'>Service Fee*:</td><td style='padding: 8px 12px; font-weight: bold; text-align: right;'>" . esc_html($currency . number_format($data['service_fee'], 2)) . "</td></tr>
                <tr style='background-color: #fdf2fb;'><td style='padding: 10px 12px; font-weight: bold; color: #a44390;'>Total Paid:</td><td style='padding: 10px 12px; font-weight: bold; text-align: right; color: #a44390;'>" . esc_html($currency . number_format($data['total_payable'], 2)) . "</td></tr>
            </table>
            <p style='font-size: 11px; color: #64748b; margin-top: 6px; font-style: italic;'>*A small non-refundable fee to help us run our platform safely &amp; smoothly.</p>

            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Warm regards,</p>
        ";

        return [
            'subject' => $subject,
            'heading' => $heading,
            'content' => $html_content,
        ];
    }

    /**
     * 7. New Booking Notification Email (Provider)
     */
    public static function get_booking_provider_template(array $data): array
    {
        $subject = sprintf(__('📅 New Booking Received - %s has booked a conversation.', 'cosy-appointments'), $data['customer_name']);
        $heading = __('New Booking Notification', 'cosy-appointments');

        $currency = $data['currency_symbol'] ?? '£';
        $order_id = $data['order_id'] ?? $data['appointment_id'] ?? '';

        $html_content = "
            <p>Hello <strong>" . esc_html($data['provider_name']) . "</strong>,</p>
            <p>Great news! A new customer, <strong>" . esc_html($data['customer_name']) . "</strong>, has booked a conversation with you.</p>
            <p>Please find the booking details below.</p>
            <p>You can also log in to your account at any time to view your bookings, update your availability, or manage your profile.</p>
            <p>Before the scheduled time, please arrange your conversation with the customer using their chosen communication method, such as setting up a virtual meeting or another agreed way to connect. At the scheduled time, simply begin your conversation.</p>
            <p>If you have any questions or need any assistance before your booking, please don't hesitate to contact us at <a href='mailto:contact@cosychats.com' style='color: #a44390; font-weight: 600;'>contact@cosychats.com</a>.</p>
            <p>Thank you for being part of CosyChats. We hope you enjoy your upcoming conversation, and we appreciate you helping make conversations based on shared experiences available to more parents.</p>

            <h4 style='color: #6d2e67; margin-top: 25px; border-bottom: 2px solid #f1e4ef; padding-bottom: 6px;'>Booking Information:</h4>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;'>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 12px; font-weight: bold; width: 40%;'>Order ID:</td><td style='padding: 8px 12px;'>#" . esc_html($order_id) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 12px; font-weight: bold;'>Experience Booked:</td><td style='padding: 8px 12px;'>" . esc_html($data['service_title']) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 12px; font-weight: bold;'>Customer Name:</td><td style='padding: 8px 12px;'>" . esc_html($data['customer_name']) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 12px; font-weight: bold;'>Customer Email:</td><td style='padding: 8px 12px;'>" . esc_html($data['customer_email']) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 12px; font-weight: bold;'>Start Date:</td><td style='padding: 8px 12px;'>" . esc_html($data['start_date']) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 12px; font-weight: bold;'>End Date:</td><td style='padding: 8px 12px;'>" . esc_html($data['end_date']) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 12px; font-weight: bold;'>Weekly Schedule:</td><td style='padding: 8px 12px;'>" . esc_html($data['weekly_type']) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 12px; font-weight: bold;'>Number of Weeks:</td><td style='padding: 8px 12px;'>" . esc_html($data['num_weeks']) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 12px; font-weight: bold;'>Week Days:</td><td style='padding: 8px 12px;'>" . esc_html($data['week_days']) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 12px; font-weight: bold;'>Total Booked Slots:</td><td style='padding: 8px 12px;'>" . esc_html($data['num_bookings']) . " slots</td></tr>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Selected Slots:</td><td style='padding: 8px 12px;'>" . esc_html($data['slots_timeline']) . "</td></tr>
            </table>

            <h4 style='color: #6d2e67; margin-top: 25px; border-bottom: 2px solid #f1e4ef; padding-bottom: 6px;'>Payment Details:</h4>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;'>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 12px;'>Experience Cost:</td><td style='padding: 8px 12px; font-weight: bold; text-align: right;'>" . esc_html($currency . number_format($data['service_cost'], 2)) . "</td></tr>
                <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 12px;'>Service Fee*:</td><td style='padding: 8px 12px; font-weight: bold; text-align: right;'>" . esc_html($currency . number_format($data['service_fee'], 2)) . "</td></tr>
                <tr style='background-color: #fdf2fb;'><td style='padding: 10px 12px; font-weight: bold; color: #a44390;'>Total Paid:</td><td style='padding: 10px 12px; font-weight: bold; text-align: right; color: #a44390;'>" . esc_html($currency . number_format($data['total_payable'], 2)) . "</td></tr>
            </table>
            <p style='font-size: 11px; color: #64748b; margin-top: 6px; font-style: italic;'>*A small non-refundable fee to help us run our platform safely &amp; smoothly.</p>

            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Warm regards,</p>
        ";

        return [
            'subject' => $subject,
            'heading' => $heading,
            'content' => $html_content,
        ];
    }

    /**
     * 8. Gifted Booking Email (Recipient)
     */
    public static function get_gifted_booking_template(array $data): array
    {
        $subject = __('🎁 A Special Gift For You! You have received a CosyChats conversation', 'cosy-appointments');
        $heading = __('🎁 A Special Gift For You!', 'cosy-appointments');

        $recipient_name = $data['recipient_name'] ?? 'Friend';
        $sender_name    = $data['sender_name'] ?? $data['customer_name'] ?? 'A Friend';
        $sender_email   = $data['sender_email'] ?? $data['customer_email'] ?? '';
        $gift_message   = $data['gift_message'] ?? '';
        $service_title  = $data['service_title'] ?? '';
        $provider_name  = $data['provider_name'] ?? '';
        $start_date     = $data['start_date'] ?? '';
        $slots_timeline = $data['slots_timeline'] ?? '';

        $html_content = "
            <p>Hello <strong>" . esc_html($recipient_name) . "</strong>,</p>
            <p><strong>" . esc_html($sender_name) . "</strong> " . (!empty($sender_email) ? "(" . esc_html($sender_email) . ")" : "") . " has gifted you a parent conversation session on CosyChats!</p>

            " . (!empty($gift_message) ? "
            <div style='background: #fff0fa; border-left: 4px solid #a44390; padding: 15px; border-radius: 6px; margin: 20px 0;'>
                <p style='margin: 0; font-style: italic; color: #6d2e67;'>\"" . esc_html($gift_message) . "\"</p>
            </div>
            " : "") . "

            <h4 style='color: #6d2e67; margin-top: 20px; border-bottom: 2px solid #f1e4ef; padding-bottom: 5px;'>Session Details</h4>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px;'>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Service / Topic:</td><td style='padding: 8px 12px;'>" . esc_html($service_title) . "</td></tr>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Service Provider (Parent):</td><td style='padding: 8px 12px;'>" . esc_html($provider_name) . "</td></tr>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Start Date & Time:</td><td style='padding: 8px 12px;'>" . esc_html($start_date) . " (" . esc_html($slots_timeline) . ")</td></tr>
            </table>

            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Kind regards,</p>
        ";

        return [
            'subject' => $subject,
            'heading' => $heading,
            'content' => $html_content,
        ];
    }

    /**
     * 9. Video Approval Email
     */
    public static function get_video_approved_template(string $name): array
    {
        $subject = __('Your Introduction Video Is Now Live', 'cosy-appointments');
        $heading = __('Video Approved!', 'cosy-appointments');

        $dashboard_url = home_url('/provider-dashboard/#profile');

        $html_content = "
            <p>Hello <strong>" . esc_html($name) . "</strong>,</p>
            <p>Great news! Your introduction video is now live on your CosyChats profile.</p>
            <p>Parents visiting your profile can now watch your introduction, helping them get to know you and your experiences before booking a conversation.</p>
            <p style='text-align: center; margin: 30px 0;'>
                <a href='" . esc_url($dashboard_url) . "' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>Go to Dashboard</a>
            </p>
            <p>Thank you for taking the time to create your video. It adds a personal touch to your profile and helps bring your story to life.</p>
            <p>If you have any questions or need any help, please don't hesitate to get in touch with us at <a href='mailto:contact@cosychats.com' style='color: #a44390; font-weight: 600;'>contact@cosychats.com</a>—we're always happy to help.</p>
            <p>Thank you for being part of CosyChats.</p>
            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Warm wishes,</p>
        ";

        return [
            'subject' => $subject,
            'heading' => $heading,
            'content' => $html_content,
        ];
    }

    /**
     * 10. Video Rejection / Update Required Email
     */
    public static function get_video_rejected_template(string $name, string $reason): array
    {
        $subject = __('Your Introduction Video Needs Updating', 'cosy-appointments');
        $heading = __('Video Update Required', 'cosy-appointments');

        $dashboard_url = home_url('/dashboard');
        $reason_block  = !empty($reason)
            ? "<div style='background: #fff5f5; border-left: 4px solid #ef4444; padding: 15px; margin: 20px 0; border-radius: 6px;'><strong style='color: #991b1b;'>Reason / Feedback:</strong><p style='margin: 5px 0 0 0; color: #991b1b;'>" . esc_html($reason) . "</p></div>"
            : "";

        $html_content = "
            <p>Hello <strong>" . esc_html($name) . "</strong>,</p>
            <p>Thank you for uploading your introduction video.</p>
            <p>We've reviewed your video and, unfortunately, it isn't quite ready to be published on your CosyChats profile. This could be due to the video quality, format, file size, or because it doesn't meet our video guidelines.</p>
            {$reason_block}
            <p>Please log in to your dashboard to upload a new version.</p>
            <p style='text-align: center; margin: 30px 0;'>
                <a href='" . esc_url($dashboard_url) . "' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>Upload New Video</a>
            </p>
            <p>If you're unsure what needs changing or would like any help creating your video, please don't hesitate to get in touch with us at <a href='mailto:contact@cosychats.com' style='color: #a44390; font-weight: 600;'>contact@cosychats.com</a>. We'll be happy to help you get your video ready to go live.</p>
            <p>Thank you for taking the time to update it—we're looking forward to seeing your new introduction.</p>
            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Warm wishes,</p>
        ";

        return [
            'subject' => $subject,
            'heading' => $heading,
            'content' => $html_content,
        ];
    }

    /**
     * 11. Admin Notification - New Provider Profile Ready for Review
     */
    public static function get_admin_provider_setup_template(string $provider_name, string $username, string $email, string $status): array
    {
        $subject = __('New Provider Profile Ready for Review', 'cosy-appointments');
        $heading = __('New Provider Profile Alert', 'cosy-appointments');

        $admin_review_url = admin_url('admin.php?page=cosy-users');

        $html_content = "
            <p style='margin-bottom: 15px;'>Hello Administrator,</p>
            <p style='margin-bottom: 15px;'>A Service Provider (Parent) has completed/updated their profile details and is ready for your review and activation.</p>
            <table style='width: 100%; background: #f8fafc; border: 1px solid #e2e8f0; border-collapse: collapse; border-radius: 8px; margin-bottom: 20px;'>
                <tr>
                    <td style='padding: 10px 15px; font-weight: bold; border-bottom: 1px solid #e2e8f0;'>Provider Name:</td>
                    <td style='padding: 10px 15px; border-bottom: 1px solid #e2e8f0;'>" . esc_html($provider_name) . "</td>
                </tr>
                <tr>
                    <td style='padding: 10px 15px; font-weight: bold; border-bottom: 1px solid #e2e8f0;'>Username:</td>
                    <td style='padding: 10px 15px; border-bottom: 1px solid #e2e8f0;'>" . esc_html($username) . "</td>
                </tr>
                <tr>
                    <td style='padding: 10px 15px; font-weight: bold; border-bottom: 1px solid #e2e8f0;'>Email Address:</td>
                    <td style='padding: 10px 15px; border-bottom: 1px solid #e2e8f0;'>" . esc_html($email) . "</td>
                </tr>
                <tr>
                    <td style='padding: 10px 15px; font-weight: bold;'>Account Status:</td>
                    <td style='padding: 10px 15px;'>" . esc_html(ucwords($status)) . "</td>
                </tr>
            </table>
            <p style='margin-bottom: 20px;'>Please log in to your WP Admin dashboard to review their details and activate their provider profile.</p>
            <p style='text-align: center; margin: 30px 0;'>
                <a href='" . esc_url($admin_review_url) . "' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>Review Provider in WP Admin</a>
            </p>
            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Kind regards,</p>
        ";

        return [
            'subject' => $subject,
            'heading' => $heading,
            'content' => $html_content,
        ];
    }

    /**
     * 12. Admin Payment Alert Email
     */
    public static function get_admin_payment_template(array $data): array
    {
        $order_id = $data['order_id'] ?? $data['appointment_id'] ?? '';
        $subject = sprintf(__('🔔 New Secure Payment Received - Order #%s', 'cosy-appointments'), $order_id);
        $heading = __('Admin Payment Alert', 'cosy-appointments');

        $currency = $data['currency_symbol'] ?? '£';
        $gift_row = !empty($data['is_gift'])
            ? "<tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Gift Recipient</td><td style='padding: 10px 0; color: #a44390; font-weight: 600;'>" . esc_html($data['recipient_name']) . " (" . esc_html($data['recipient_email']) . ")</td></tr>"
            : "";

        $html_content = "
            <p>Hello Administrator,</p>
            <p>A new payment transaction has been processed and authorized successfully.</p>
            
            <h3 style='color: #6d2e67; border-bottom: 2px solid #f1e4ef; padding-bottom: 8px; margin-top: 25px;'>Order Information Summary:</h3>
            <table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>
                <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600; width: 40%;'>Order ID</td><td style='padding: 10px 0;'>#" . esc_html($order_id) . "</td></tr>
                <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Experience</td><td style='padding: 10px 0;'>" . esc_html($data['service_title']) . "</td></tr>
                <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Customer Name</td><td style='padding: 10px 0;'>" . esc_html($data['customer_name']) . " (" . esc_html($data['customer_email']) . ")</td></tr>
                <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Parent Provider</td><td style='padding: 10px 0;'>" . esc_html($data['provider_name']) . "</td></tr>
                {$gift_row}
                <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Start Date</td><td style='padding: 10px 0;'>" . esc_html($data['start_date']) . "</td></tr>
                <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>End Date</td><td style='padding: 10px 0;'>" . esc_html($data['end_date']) . "</td></tr>
                <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Weekly Schedule</td><td style='padding: 10px 0;'>" . esc_html($data['weekly_type']) . "</td></tr>
                <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Week Days Available</td><td style='padding: 10px 0;'>" . esc_html($data['week_days']) . "</td></tr>
                <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Weeks &amp; Slots</td><td style='padding: 10px 0;'>" . esc_html($data['num_bookings']) . " slots over " . esc_html($data['num_weeks']) . " week(s)</td></tr>
                <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0; font-weight: 600;'>Selected Slots</td><td style='padding: 10px 0;'>" . esc_html($data['slots_timeline']) . "</td></tr>
            </table>

            <h3 style='color: #6d2e67; border-bottom: 2px solid #f1e4ef; padding-bottom: 8px; margin-top: 25px;'>Financial Details:</h3>
            <table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>
                <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0;'>Provider Revenue Share</td><td style='padding: 10px 0; text-align: right;'>{$currency}" . esc_html($data['service_cost']) . "</td></tr>
                <tr style='border-bottom: 1px solid #fdf2fb;'><td style='padding: 10px 0;'>Service Fee* (Net)</td><td style='padding: 10px 0; text-align: right;'>{$currency}" . esc_html($data['service_fee']) . "</td></tr>
                <tr style='background-color: #fdf2fb;'><td style='padding: 12px 10px; font-weight: 700; color: #a44390;'>Total Paid</td><td style='padding: 12px 10px; font-weight: 700; text-align: right; color: #a44390;'>{$currency}" . esc_html($data['total_payable']) . "</td></tr>
            </table>
            <p style='font-size: 11px; color: #64748b; margin-top: 6px; font-style: italic;'>*A small non-refundable fee to help us run our platform safely &amp; smoothly.</p>
        ";

        return [
            'subject' => $subject,
            'heading' => $heading,
            'content' => $html_content,
        ];
    }

    /**
     * 13. Provider Review Approved Notification Email
     */
    public static function get_provider_review_approved_template(string $provider_name, string $customer_name, int $rating, string $review_text): array
    {
        $subject = __('New Parent Review Approved on Your Profile!', 'cosy-appointments');
        $heading = __('Parent Review Approved', 'cosy-appointments');

        $html_content = sprintf(
            '<p style="margin-bottom: 15px;">Hello <strong>%s</strong>,</p>
            <p style="margin-bottom: 15px;">A new parent review from <strong>%s</strong> (<strong>%d Stars</strong>) has been approved by the Administrator and is now live on your profile page.</p>
            <blockquote style="background: #fdf5fc; border-left: 4px solid #a44390; padding: 12px 16px; margin: 15px 0; font-style: italic;">"%s"</blockquote>
            <p style="margin-bottom: 0;">You can view and post a public response to this review from your <strong>Provider Dashboard &rarr; Parent Reviews</strong> tab.</p>',
            esc_html($provider_name),
            esc_html($customer_name),
            $rating,
            esc_html($review_text)
        );

        return [
            'subject' => $subject,
            'heading' => $heading,
            'content' => $html_content,
        ];
    }

    /**
     * 14. Admin New Review Submitted Alert Email
     */
    public static function get_admin_new_review_template(string $provider_name, string $customer_name, int $rating, string $review_text): array
    {
        $subject = __('New Customer Review Submitted for Moderation', 'cosy-appointments');
        $heading = __('New Customer Review', 'cosy-appointments');

        $html_content = sprintf(
            '<p style="margin-bottom: 15px;">Hello Administrator,</p>
            <p style="margin-bottom: 15px;">A new customer review has been submitted for <strong>%s</strong> and is currently <strong>Pending Approval</strong>.</p>
            <ul style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px 25px; border-radius: 8px; margin-bottom: 20px;">
                <li><strong>Customer:</strong> %s</li>
                <li><strong>Rating:</strong> %d Stars</li>
                <li><strong>Review:</strong> "%s"</li>
            </ul>
            <p style="margin-bottom: 0;">Log in to WP Admin &rarr; <strong>CC Booking &rarr; Reviews</strong> to approve or reject this review.</p>',
            esc_html($provider_name),
            esc_html($customer_name),
            $rating,
            esc_html($review_text)
        );

        return [
            'subject' => $subject,
            'heading' => $heading,
            'content' => $html_content,
        ];
    }

    /**
     * 15. Customer Review Reply Email
     */
    public static function get_customer_review_reply_template(string $customer_name, string $provider_name, string $reply_text, string $original_review): array
    {
        $subject = sprintf(__('New Response from %s on Your Review!', 'cosy-appointments'), $provider_name);
        $heading = __('Provider Response Received', 'cosy-appointments');

        $html_content = sprintf(
            '<p style="margin-bottom: 15px;">Hello <strong>%s</strong>,</p>
            <p style="margin-bottom: 15px;"><strong>%s</strong> has posted a response to your review on CosyChats.</p>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin: 15px 0;">
                <p style="margin: 0 0 8px 0; font-size: 13px; color: #64748b;"><strong>Your Original Review:</strong></p>
                <p style="margin: 0 0 15px 0; font-style: italic; color: #334155;">"%s"</p>
                <div style="border-top: 1px dashed #cbd5e1; padding-top: 12px;">
                    <p style="margin: 0 0 6px 0; font-size: 13px; color: #a44390;"><strong>%s\'s Response:</strong></p>
                    <p style="margin: 0; color: #1e293b; font-weight: 500;">"%s"</p>
                </div>
            </div>
            <p style="margin-bottom: 0;">You can view the response thread anytime by visiting the parent\'s profile on CosyChats.</p>',
            esc_html($customer_name),
            esc_html($provider_name),
            esc_html($original_review),
            esc_html($provider_name),
            esc_html($reply_text)
        );

        return [
            'subject' => $subject,
            'heading' => $heading,
            'content' => $html_content,
        ];
    }

    /**
     * 11. Booking Cancellation Email (Customer)
     */
    public static function get_booking_cancelled_customer_template(array $data): array
    {
        $order_id = $data['order_id'] ?? '';
        $subject  = sprintf(__('❌ Important Update: Your CosyChats Appointment Has Been Cancelled (#%s)', 'cosy-appointments'), $order_id);
        $heading  = __('Appointment Cancelled', 'cosy-appointments');

        $customer_name  = $data['customer_name'] ?? 'Customer';
        $provider_name  = $data['provider_name'] ?? 'Provider';
        $service_title  = $data['service_title'] ?? '';
        $start_date     = $data['start_date'] ?? '';
        $slots_timeline = $data['slots_timeline'] ?? '';

        $html_content = "
            <p>Hello <strong>" . esc_html($customer_name) . "</strong>,</p>
            <p>We are writing to inform you that your upcoming conversation session with <strong>" . esc_html($provider_name) . "</strong> has been cancelled by the parent provider.</p>

            <h4 style='color: #6d2e67; margin-top: 20px; border-bottom: 2px solid #f1e4ef; padding-bottom: 5px;'>Cancelled Booking Details</h4>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px;'>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Order ID:</td><td style='padding: 8px 12px;'>#" . esc_html($order_id) . "</td></tr>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Experience / Topic:</td><td style='padding: 8px 12px;'>" . esc_html($service_title) . "</td></tr>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Provider:</td><td style='padding: 8px 12px;'>" . esc_html($provider_name) . "</td></tr>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Scheduled Date & Time:</td><td style='padding: 8px 12px;'>" . esc_html($start_date) . " (" . esc_html($slots_timeline) . ")</td></tr>
            </table>

            <p>We apologize for any inconvenience this may cause. If you have any questions regarding refunds or re-booking another parent, please contact our support team at <a href='mailto:contact@cosychats.com' style='color: #a44390; font-weight: 600;'>contact@cosychats.com</a>.</p>
            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Kind regards,</p>
        ";

        return [
            'subject' => $subject,
            'heading' => $heading,
            'content' => $html_content,
        ];
    }

    /**
     * 12. Booking Status Update Email (Customer)
     */
    public static function get_booking_status_update_customer_template(array $data): array
    {
        $order_id     = $data['order_id'] ?? '';
        $status_label = ucfirst($data['status'] ?? 'Updated');
        $subject      = sprintf(__('✅ Update: Your CosyChats Appointment Status is now %s (#%s)', 'cosy-appointments'), $status_label, $order_id);
        $heading      = sprintf(__('Appointment %s', 'cosy-appointments'), $status_label);

        $customer_name  = $data['customer_name'] ?? 'Customer';
        $provider_name  = $data['provider_name'] ?? 'Provider';
        $service_title  = $data['service_title'] ?? '';
        $start_date     = $data['start_date'] ?? '';
        $slots_timeline = $data['slots_timeline'] ?? '';

        $html_content = "
            <p>Hello <strong>" . esc_html($customer_name) . "</strong>,</p>
            <p>Great news! Your booking status with <strong>" . esc_html($provider_name) . "</strong> has been updated to <strong>" . esc_html($status_label) . "</strong>.</p>

            <h4 style='color: #6d2e67; margin-top: 20px; border-bottom: 2px solid #f1e4ef; padding-bottom: 5px;'>Booking Summary</h4>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px;'>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Order ID:</td><td style='padding: 8px 12px;'>#" . esc_html($order_id) . "</td></tr>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Experience / Topic:</td><td style='padding: 8px 12px;'>" . esc_html($service_title) . "</td></tr>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Provider:</td><td style='padding: 8px 12px;'>" . esc_html($provider_name) . "</td></tr>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Scheduled Date & Time:</td><td style='padding: 8px 12px;'>" . esc_html($start_date) . " (" . esc_html($slots_timeline) . ")</td></tr>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Current Status:</td><td style='padding: 8px 12px; color: #a44390; font-weight: bold;'>" . esc_html($status_label) . "</td></tr>
            </table>

            <p>You can view your booking details at any time in your account dashboard.</p>
            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Kind regards,</p>
        ";

        return [
            'subject' => $subject,
            'heading' => $heading,
            'content' => $html_content,
        ];
    }

    /**
     * Review Invite Email (Sent to customer when booking is completed)
     *
     * @param array $data Contains: customer_name, provider_name, service_title, review_url
     * @return array ['subject', 'heading', 'content']
     */
    public static function get_review_invite_template(array $data): array
    {
        $customer_name = esc_html($data['customer_name'] ?? 'Customer');
        $provider_name = esc_html($data['provider_name'] ?? 'Parent');
        $service_title = esc_html($data['service_title'] ?? 'Parent Conversation');
        $review_url    = esc_url($data['review_url'] ?? '#');

        $subject = sprintf(__('⭐ How was your session with %s? Leave a Review', 'cosy-appointments'), $data['provider_name'] ?? 'Parent');
        $heading = __('We\'d Love Your Feedback', 'cosy-appointments');

        $html_content = "
            <p>Hello <strong>{$customer_name}</strong>,</p>
            <p>Thank you for your recent conversation with <strong>{$provider_name}</strong> on CosyChats.</p>
            <p>We hope you found the session helpful and meaningful. Your feedback helps other parents discover the right person to talk to, and it means a great deal to the parents who share their experiences on CosyChats.</p>

            <div style='background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px; margin: 20px 0;'>
                <p style='margin: 0 0 8px 0; font-weight: 600; color: #1d2327;'>Session Details:</p>
                <p style='margin: 0 0 4px 0; color: #475569;'><strong>Parent:</strong> {$provider_name}</p>
                <p style='margin: 0; color: #475569;'><strong>Experience:</strong> {$service_title}</p>
            </div>

            <p>If you'd like to share your experience, simply click the button below. Your review will be submitted for approval before appearing on the parent's profile.</p>

            <p style='text-align: center; margin: 30px 0;'>
                <a href='{$review_url}' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>Leave Your Review</a>
            </p>

            <p style='font-size: 13px; color: #64748b; margin-top: 25px;'>If you're having trouble clicking the button, copy and paste the link below into your web browser:</p>
            <p style='font-size: 13px; word-break: break-all; color: #a44390;'><a href='{$review_url}' style='color: #a44390; text-decoration: none;'>{$review_url}</a></p>

            <p style='font-size: 13px; color: #94a3b8; margin-top: 20px; font-style: italic;'>This is a one-time link. Once you've submitted your review, the link will no longer be active.</p>
            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Kind regards,</p>
        ";

        return [
            'subject' => $subject,
            'heading' => $heading,
            'content' => $html_content,
        ];
    }
}
