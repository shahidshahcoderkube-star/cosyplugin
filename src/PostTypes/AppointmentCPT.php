<?php

namespace Cosy\Appointments\PostTypes;

use Cosy\Appointments\Loader;

class AppointmentCPT
{
    /**
     * REGISTERS APPOINTMENT CPT HOOKS
     * 
     * USE CASE:
     * Called during plugin initialization to register Custom Post Type and custom admin menus.
     * 
     * HOW TO USE:
     * (new AppointmentCPT())->register($loader);
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Attaches 'register_post_type' to WordPress 'init' hook.
     * 2. Attaches admin menu registration callbacks to WordPress 'admin_menu' hook.
     * 
     * @param Loader $loader Plugin loader instance.
     */
    public function register(Loader $loader): void
    {
        // CPT register karne ke liye init hook use karo
        $loader->add_action('init', $this, 'register_post_type');

        // Admin menu register karne ke liye hook
        $loader->add_action('admin_menu', $this, 'register_admin_menu');

        // Register video approve menu under CPT
        $loader->add_action('admin_menu', $this, 'register_video_menu');

        // Register admin menu under CPT
        $loader->add_action('admin_menu', $this, 'register_payment_token_and_key');
    }

    /**
     * REGISTERS COSY APPOINTMENT CUSTOM POST TYPE
     * 
     * USE CASE:
     * Registers 'cosy_appointment' CPT to store appointment orders in WordPress.
     * 
     * HOW TO USE:
     * Triggered automatically on WordPress 'init' hook.
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Sets labels, icon, custom capabilities, and supports title/editor/custom-fields.
     * 2. Registers post type with register_post_type().
     */
    public function register_post_type(): void
    {
        register_post_type('cosy_appointment', [
            'labels' => [
                'name'          => __('Appointments', 'cosy'),
                'singular_name' => __('Appointment', 'cosy'),
                'add_new_item' => __('Add Services', 'cosy'),
                'edit_item' => __('Edit Services', 'cosy'),
            ],
            'public'      => true,              // frontend pe visible
            'has_archive' => true,              // archive page banega
            'menu_icon'   => 'dashicons-clipboard', // dashboard icon
            'supports'    => ['title', 'editor', 'custom-fields'],
            'show_in_rest' => true,              // Gutenberg + REST API support
        ]);
    }

    /**
     * REGISTERS ORDERS SUBMENU
     * 
     * USE CASE:
     * Adds 'Orders' page under Appointments menu in WP Admin.
     * 
     * HOW TO USE:
     * Triggered automatically during 'admin_menu'.
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Calls add_submenu_page() with parent slug 'edit.php?post_type=cosy_appointment'.
     */
    public function register_admin_menu(): void
    {
        add_submenu_page(
            'edit.php?post_type=cosy_appointment', // parent slug = CPT menu
            __('Orders', 'cosy'), // page
            __('Orders', 'cosy'), // menu title
            'manage_cosy_appointments', // capability 
            'cosy-appointment-orders', // menu slug 
            [$this, 'render_orders_page'] // callback
        );
    }

    /**
     * REGISTERS VIDEO APPROVE SUBMENU
     * 
     * USE CASE:
     * Adds 'Video Approve' page under Appointments menu for video review.
     * 
     * HOW TO USE:
     * Triggered automatically during 'admin_menu'.
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Calls add_submenu_page() with capability 'approve_cosy_media'.
     */
    public function register_video_menu(): void
    {
        add_submenu_page(
            'edit.php?post_type=cosy_appointment', // parent slug = CPT menu
            __('Video Approve', 'cosy'), // page
            __('Video Approve', 'cosy'), // menu title
            'approve_cosy_media', // capability 
            'cosy-video-approve', // menu slug 
            [$this, 'render_video_approve_page'] // callback
        );
    }

    /**
     * REGISTERS PAYMENT TOKEN SUBMENU
     * 
     * USE CASE:
     * Adds 'Payment Token & Key' page under Appointments menu for gateway settings.
     * 
     * HOW TO USE:
     * Triggered automatically during 'admin_menu'.
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Calls add_submenu_page() with capability 'manage_cosy_appointments'.
     */
    public function register_payment_token_and_key(): void
    {
        add_submenu_page(
            'edit.php?post_type=cosy_appointment', // parent slug = CPT menu
            __('Payment Token & Key', 'cosy'), // page
            __('Payment Token & Key', 'cosy'), // menu title
            'manage_cosy_appointments', // capability 
            'cosy-payment-token-key', // menu slug 
            [$this, 'render_payment_token_and_key_page'] // callback
        );
    }

    /**
     * RENDERS ORDERS PAGE
     * 
     * USE CASE:
     * Callback renderer for Orders admin submenu page.
     * 
     * HOW TO USE:
     * Triggered when admin visits 'cosy-appointment-orders' page.
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Outputs HTML header wrapper.
     */
    public function render_orders_page(): void
    {
        echo '<div class="wrap"><h1>Orders</h1><p>Here you can manage appointment orders.</p></div>';
    }

    /**
     * RENDERS VIDEO APPROVAL PAGE
     * 
     * USE CASE:
     * Callback renderer for Video Approval admin submenu page.
     * 
     * HOW TO USE:
     * Triggered when admin visits 'cosy-video-approve' page.
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Outputs HTML header wrapper.
     */
    public function render_video_approve_page(): void
    {
        echo '<div class="wrap"><h1>Video Approve</h1><p>Here you can manage video approvals.</p></div>';
    }

    /**
     * RENDERS PAYMENT KEYS PAGE
     * 
     * USE CASE:
     * Callback renderer for Payment Token & Key admin submenu page.
     * 
     * HOW TO USE:
     * Triggered when admin visits 'cosy-payment-token-key' page.
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Outputs HTML header wrapper.
     */
    public function render_payment_token_and_key_page(): void
    {
        echo '<div class="wrap"><h1>Payment Token & Key</h1><p>Here you can manage payment tokens and keys.</p></div>';
    }
}
