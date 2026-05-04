<?php

namespace Cosy\Appointments\PostTypes;

use Cosy\Appointments\Loader;

class AppointmentCPT
{
    //--------------- Register Actions ----------------//
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

    //--------------- Register Custom Post Type ----------------//
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

    //--------------- Register Admin Menus ----------------//
    public function register_admin_menu(): void
    {
        add_submenu_page(
            'edit.php?post_type=cosy_appointment', // parent slug = CPT menu
            __('Orders', 'cosy'), // page
            __('Orders', 'cosy'), // menu title
            'manage_options', // capability 
            'cosy-appointment-orders', // menu slug 
            [$this, 'render_orders_page'] // callback
        );
    }

    //--------------- Register Video Menu ----------------//
    public function register_video_menu(): void
    {
        add_submenu_page(
            'edit.php?post_type=cosy_appointment', // parent slug = CPT menu
            __('Video Approve', 'cosy'), // page
            __('Video Approve', 'cosy'), // menu title
            'manage_options', // capability 
            'cosy-video-approve', // menu slug 
            [$this, 'render_video_approve_page'] // callback
        );
    }

    //--------------- Register Payment Token & Key Menu ----------------//
    public function register_payment_token_and_key(): void
    {
        add_submenu_page(
            'edit.php?post_type=cosy_appointment', // parent slug = CPT menu
            __('Payment Token & Key', 'cosy'), // page
            __('Payment Token & Key', 'cosy'), // menu title
            'manage_options', // capability 
            'cosy-payment-token-key', // menu slug 
            [$this, 'render_payment_token_and_key_page'] // callback
        );
    }

    //--------------- Render Callback Functions ----------------//
    public function render_orders_page(): void
    {
        echo '<div class="wrap"><h1>Orders</h1><p>Here you can manage appointment orders.</p></div>';
    }


    //--------------- Video Approve Page Render Function ----------------//
    public function render_video_approve_page(): void
    {
        echo '<div class="wrap"><h1>Video Approve</h1><p>Here you can manage video approvals.</p></div>';
    }

    //--------------- Payment Token & Key Page Render Function ----------------//
    public function render_payment_token_and_key_page(): void
    {
        echo '<div class="wrap"><h1>Payment Token & Key</h1><p>Here you can manage payment tokens and keys.</p></div>';
    }
}
