<?php

namespace Cosy\Appointments\PostTypes;

// use Cosy\Appointments\Loader;

class ServiceCPT
{
    /**
     * Hooks into WordPress to register the Custom Post Type (CPT) for services.
     */
    public function register($loader): void
    {
        $loader->add_action('init', $this, 'register_service_cpt');
    }

    /**
     * Registers the "Services" Custom Post Type.
     * This allows admins to add, edit, and manage different services offered by providers.
     */
    public function register_service_cpt(): void
    {

        $labels = [
            'name' => __('Services', 'cosy-appointments'),
            'singular_name' => __('Service', 'cosy-appointments'),
            'add_new' => __('Add New Service', 'cosy-appointments'),
            'add_new_item' => __('Add New Service', 'cosy-appointments'),
            'edit_item' => __('Edit Service', 'cosy-appointments'),
            'new_item' => __('New Service', 'cosy-appointments'),
            'view_item' => __('View Service', 'cosy-appointments'),
            'search_items' => __('Search Services', 'cosy-appointments'),
            'not_found' => __('No Services found', 'cosy-appointments'),
            'not_found_in_trash' => __('No Services found in Trash', 'cosy-appointments'),
            'menu_name' => __('Services', 'cosy-appointments'),
        ];

        $args = [
            'labels' => $labels,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => false, // Admin menu me parent ke under show hoga
            'menu_icon' => 'dashicons-calendar-alt', // WP dashicon 
            'supports' => ['title', 'editor', 'thumbnail'],
            'has_archive' => true,
            'rewrite' => ['slug' => 'services'],
            'show_in_rest' => true, // Gutenberg + REST API support 
        ];

        register_post_type('cosy_service', $args);
    }
}
