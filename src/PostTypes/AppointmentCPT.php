<?php

namespace Cosy\Appointments\PostTypes;

use Cosy\Appointments\Loader;

class AppointmentCPT
{
    /**
     * Hooks into WordPress to register the Custom Post Type (CPT) and its admin menus.
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
     * Registers the "Appointments" Custom Post Type.
     * This creates a new section in the WP Admin menu where all bookings are stored.
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
     * Adds the "Orders" submenu page under the Appointments menu in WP Admin.
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
     * Adds the "Video Approve" submenu page under the Appointments menu.
     * Used by admins to review and approve provider introductory videos.
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
     * Adds the "Payment Token & Key" submenu page.
     * Used to configure payment credentials.
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
     * Renders the HTML layout for the Orders management page in the backend.
     */
    public function render_orders_page(): void
    {
        echo '<div class="wrap"><h1>Orders</h1><p>Here you can manage appointment orders.</p></div>';
    }


    /**
     * Renders the HTML layout for the Video Approval page in the backend.
     */
    public function render_video_approve_page(): void
    {
        echo '<div class="wrap"><h1>Video Approve</h1><p>Here you can manage video approvals.</p></div>';
    }

    /**
     * Renders the HTML layout for the Settings (Payment Keys) page.
     */
    public function render_payment_token_and_key_page(): void
    {
        echo '<div class="wrap"><h1>Payment Token & Key</h1><p>Here you can manage payment tokens and keys.</p></div>';
    }
}
