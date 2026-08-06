<?php

namespace Cosy\Appointments\Frontend;

class Class_Header_Menu
{
    public function register($loader): void
    {
        // Filter to add menu items to the primary navigation
        $loader->add_filter('wp_nav_menu_items', $this, 'add_services_dropdown', 10, 2);
    }

    /**
     * Add the dynamic services dropdown to the navigation menu
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
