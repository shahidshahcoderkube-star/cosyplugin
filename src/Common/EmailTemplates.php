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
            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 15px;'>Kind regards,<br><strong>The CosyChats Team</strong></p>
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
            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Kind regards,<br><strong>The CosyChats Team</strong></p>
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
        $subject = __('Your CosyChats Account is now Active', 'cosy-appointments');
        $heading = __('Account Active!', 'cosy-appointments');

        $html_content = "
            <p>Hello <strong>" . esc_html($name) . "</strong>,</p>
            <p>Congratulations! Your account has been reviewed and approved by the administrator. Your profile is now live and visible to parents.</p>
            <p style='text-align: center; margin: 30px 0;'>
                <a href='" . esc_url(home_url('/login')) . "' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>Login to Your Account</a>
            </p>
            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Kind regards,<br><strong>The CosyChats Team</strong></p>
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
            <p>Your parent account has been temporarily deactivated by a CosyChats administrator. While your account is inactive, your profile won't be visible to customers and you won't be able to receive new bookings.</p>
            <p>If you have any questions or think this has happened in error, please don't hesitate to get in touch with the CosyChats team at <a href='mailto:contact@cosychats.com' style='color: #a44390;'>contact@cosychats.com</a>.</p>
            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Kind regards,<br><strong>The CosyChats Team</strong></p>
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
        $subject = __('🌸 Booking Confirmed - Thank you for your payment!', 'cosy-appointments');
        $heading = __('Booking Confirmed!', 'cosy-appointments');

        $currency = $data['currency_symbol'] ?? '£';

        $html_content = "
            <p>Hello <strong>" . esc_html($data['customer_name']) . "</strong>,</p>
            <p>Thank you for choosing CosyChats. Your booking payment of <strong>" . esc_html($currency . number_format($data['total_payable'], 2)) . "</strong> has been successfully processed securely.</p>

            <h4 style='color: #6d2e67; margin-top: 20px; border-bottom: 2px solid #f1e4ef; padding-bottom: 5px;'>Booking Information Summary</h4>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px;'>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Order ID:</td><td style='padding: 8px 12px;'>#" . esc_html($data['appointment_id']) . "</td></tr>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Service / Conversation:</td><td style='padding: 8px 12px;'>" . esc_html($data['service_title']) . "</td></tr>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Service Provider (Parent):</td><td style='padding: 8px 12px;'>" . esc_html($data['provider_name']) . "</td></tr>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Start Date:</td><td style='padding: 8px 12px;'>" . esc_html($data['start_date']) . "</td></tr>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Schedule:</td><td style='padding: 8px 12px;'>" . esc_html($data['weekly_type']) . " (" . esc_html($data['num_weeks']) . " Weeks)</td></tr>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Selected Slots:</td><td style='padding: 8px 12px;'>" . esc_html($data['slots_timeline']) . "</td></tr>
            </table>

            <h4 style='color: #6d2e67; margin-top: 20px; border-bottom: 2px solid #f1e4ef; padding-bottom: 5px;'>Payment Breakdown</h4>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px;'>
                <tr><td style='padding: 8px 12px;'>Service Cost:</td><td style='padding: 8px 12px; font-weight: bold;'>" . esc_html($currency . number_format($data['service_cost'], 2)) . "</td></tr>
                <tr><td style='padding: 8px 12px;'>Service Fee:</td><td style='padding: 8px 12px; font-weight: bold;'>" . esc_html($currency . number_format($data['service_fee'], 2)) . "</td></tr>
                <tr style='border-top: 1px solid #e2e8f0;'><td style='padding: 10px 12px; font-weight: bold; color: #6d2e67;'>Total Paid:</td><td style='padding: 10px 12px; font-weight: bold; color: #6d2e67;'>" . esc_html($currency . number_format($data['total_payable'], 2)) . "</td></tr>
            </table>

            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Kind regards,<br><strong>The CosyChats Team</strong></p>
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
        $subject = sprintf(__('📅 New Booking Received - %s has booked your conversation!', 'cosy-appointments'), $data['customer_name']);
        $heading = __('New Appointment Notification', 'cosy-appointments');

        $currency = $data['currency_symbol'] ?? '£';

        $html_content = "
            <p>Hello <strong>" . esc_html($data['provider_name']) . "</strong>,</p>
            <p>Great news! A customer, <strong>" . esc_html($data['customer_name']) . "</strong> (" . esc_html($data['customer_email']) . "), has booked a session with you.</p>

            <h4 style='color: #6d2e67; margin-top: 20px; border-bottom: 2px solid #f1e4ef; padding-bottom: 5px;'>Booking Information Summary</h4>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px;'>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Order ID:</td><td style='padding: 8px 12px;'>#" . esc_html($data['appointment_id']) . "</td></tr>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Conversation:</td><td style='padding: 8px 12px;'>" . esc_html($data['service_title']) . "</td></tr>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Customer Name:</td><td style='padding: 8px 12px;'>" . esc_html($data['customer_name']) . "</td></tr>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Customer Email:</td><td style='padding: 8px 12px;'>" . esc_html($data['customer_email']) . "</td></tr>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Start Date:</td><td style='padding: 8px 12px;'>" . esc_html($data['start_date']) . "</td></tr>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Selected Slots:</td><td style='padding: 8px 12px;'>" . esc_html($data['slots_timeline']) . "</td></tr>
            </table>

            <p style='text-align: center; margin: 30px 0;'>
                <a href='" . esc_url(home_url('/dashboard')) . "' style='background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);'>View Dashboard Schedule</a>
            </p>

            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Kind regards,<br><strong>The CosyChats Team</strong></p>
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

        $html_content = "
            <p>Hello <strong>" . esc_html($data['recipient_name']) . "</strong>,</p>
            <p><strong>" . esc_html($data['sender_name']) . "</strong> (" . esc_html($data['sender_email']) . ") has gifted you a parent conversation session on CosyChats!</p>

            " . (!empty($data['gift_message']) ? "
            <div style='background: #fff0fa; border-left: 4px solid #a44390; padding: 15px; border-radius: 6px; margin: 20px 0;'>
                <p style='margin: 0; font-style: italic; color: #6d2e67;'>\"" . esc_html($data['gift_message']) . "\"</p>
            </div>
            " : "") . "

            <h4 style='color: #6d2e67; margin-top: 20px; border-bottom: 2px solid #f1e4ef; padding-bottom: 5px;'>Session Details</h4>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f8fafc; border-radius: 8px;'>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Service / Topic:</td><td style='padding: 8px 12px;'>" . esc_html($data['service_title']) . "</td></tr>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Service Provider (Parent):</td><td style='padding: 8px 12px;'>" . esc_html($data['provider_name']) . "</td></tr>
                <tr><td style='padding: 8px 12px; font-weight: bold;'>Start Date & Time:</td><td style='padding: 8px 12px;'>" . esc_html($data['start_date']) . " (" . esc_html($data['slots_timeline']) . ")</td></tr>
            </table>

            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Kind regards,<br><strong>The CosyChats Team</strong></p>
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
        $subject = __('Your CosyChats Introduction Video is Approved!', 'cosy-appointments');
        $heading = __('Video Approved!', 'cosy-appointments');

        $html_content = "
            <p>Hello <strong>" . esc_html($name) . "</strong>,</p>
            <p>Great news! Your introduction video has been reviewed and approved by the site administrator. It is now active on your public profile page.</p>
            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Kind regards,<br><strong>The CosyChats Team</strong></p>
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
        $subject = __('Action Required: Your Introduction Video Needs Revision', 'cosy-appointments');
        $heading = __('Video Update Required', 'cosy-appointments');

        $html_content = "
            <p>Hello <strong>" . esc_html($name) . "</strong>,</p>
            <p>Your introduction video was reviewed by our administrator and requires some updates before it can be published.</p>
            <div style='background: #fff5f5; border-left: 4px solid #ef4444; padding: 15px; margin: 20px 0; border-radius: 6px;'>
                <strong>Reason / Feedback:</strong>
                <p style='margin: 5px 0 0 0; color: #991b1b;'>" . esc_html($reason) . "</p>
            </div>
            <p>Please log in to your Provider Dashboard to re-upload an updated video.</p>
            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Kind regards,<br><strong>The CosyChats Team</strong></p>
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

        $admin_review_url = admin_url('admin.php?page=cosy-users-admin');

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
            <p style='font-size: 14px; line-height: 1.6; color: #64748b; margin-top: 25px;'>Kind regards,<br><strong>The CosyChats Team</strong></p>
        ";

        return [
            'subject' => $subject,
            'heading' => $heading,
            'content' => $html_content,
        ];
    }
}
