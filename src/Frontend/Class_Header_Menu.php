<?php

namespace Cosy\Appointments\Frontend;

class Class_Header_Menu
{
    /**
     * REGISTERS NAVIGATION MENU HOOKS
     * 
     * USE CASE:
     * Called during plugin initialization sequence to hook header navigation dropdown items.
     * 
     * HOW TO USE:
     * (new Class_Header_Menu())->register($loader);
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Attaches 'add_services_dropdown' callback to WordPress filter 'wp_nav_menu_items'.
     * 
     * @param Loader $loader Plugin loader instance.
     */
    public function register($loader): void
    {
        // Filter to add menu items to the primary navigation
        $loader->add_filter('wp_nav_menu_items', $this, 'add_services_dropdown', 10, 2);
    }

    /**
     * ADDS DYNAMIC SERVICES & ACCOUNT BUTTONS TO NAVIGATION MENU
     * 
     * USE CASE:
     * Triggered when WordPress outputs primary theme navigation menu.
     * 
     * HOW TO USE:
     * Automatically invoked by WordPress 'wp_nav_menu_items' filter.
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Queries published 'cosy_service' CPT items.
     * 2. Checks current user login status and roles (customer/provider/admin).
     * 3. Renders login, register popup trigger, dashboard links, or logout dropdown menu items.
     * 4. Returns appended menu items HTML string.
     * 
     * @param string $items Raw HTML menu items string.
     * @param object $args  Nav menu arguments object.
     * @return string       Modified HTML menu items string.
     */
    public function add_services_dropdown(string $items, $args): string
    {
        // You can target a specific menu location if needed, e.g., 'primary'
        // if ($args->theme_location !== 'primary') return $items;

        $services = get_posts([
            'post_type' => 'cosy_service',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ]);

        $current_user = wp_get_current_user();
        $roles = (array) $current_user->roles;

        if (in_array('provider', $roles)) {
            $items = str_replace('Service Provide', '', $items);
        }

        ob_start();
        ?>
        <!-- Auth Buttons -->
        <?php if (!is_user_logged_in()) : ?>
            <li class="menu-item login-btn-item" style="display: inline-flex; align-items: center;">
                <a href="javascript:void(0)" class="btn btn-filled openRegisterPopup" style="margin-left: 10px;">Create Account</a>
            </li>
            <li class="menu-item login-btn-item" style="display: inline-flex; align-items: center;">
                <a href="<?php echo esc_url(cosy_get_page_url('login')); ?>" class="btn btn-filled" style="margin-left: 10px;">Login</a>
            </li>
        <?php else : ?>
            <?php if (in_array('customer', $roles)) : ?>
                <li class="menu-item login-btn-item" style="display: inline-flex; align-items: center;">
                    <a href="<?php echo esc_url(cosy_get_page_url('customer-profile')); ?>" class="btn btn-filled" style="margin-left: 10px;">Dashboard</a>
                </li>
                <li class="menu-item login-btn-item" style="display: inline-flex; align-items: center;">
                    <a href="<?php echo esc_url(cosy_get_page_url('customer-order')); ?>" class="btn btn-filled" style="margin-left: 10px;">Order</a>
                </li>
                <li class="menu-item login-btn-item" style="display: inline-flex; align-items: center;">
                    <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-filled" style="margin-left: 10px;">Logout</a>
                </li>
            <?php elseif (in_array('provider', $roles)) : ?>
                <li class="menu-item login-btn-item" style="display: inline-flex; align-items: center;">
                    <a href="<?php echo esc_url(cosy_get_page_url('provider-dashboard')); ?>" class="btn btn-filled" style="margin-left: 10px;">Dashboard</a>
                </li>
                <li class="menu-item login-btn-item" style="display: inline-flex; align-items: center;">
                    <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-filled" style="margin-left: 10px;">Logout</a>
                </li>
            <?php endif; ?>
        <?php endif; ?>

<?php
        $dropdown_html = ob_get_clean();

        return $items . $dropdown_html;
    }
}
