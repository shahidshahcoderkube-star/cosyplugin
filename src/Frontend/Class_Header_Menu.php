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

        if (empty($services)) {
            return $items;
        }

        $current_user = wp_get_current_user();
        $roles = (array) $current_user->roles;

        if (in_array('provider', $roles)) {
            $items = str_replace('Service Provide', '', $items);
        }

        ob_start();
        ?>
        <!-- Services Dropdown (Premium Button) -->
        <li class="menu-item menu-item-has-children cosy-header-dropdown-wrapper">
            <a href="#" class="cosy-dropdown-toggle">
                Parents <span class="cosy-arrow">▾</span>
            </a>
            <ul class="sub-menu cosy-custom-submenu">
                <?php foreach ($services as $service): ?>
                    <li class="menu-item">
                        <a href="<?php echo home_url('/service-provider/' . $service->post_name . '/'); ?>">
                            <?php echo esc_html($service->post_title); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </li>
        
        <!-- Auth Buttons -->
        <?php if (!is_user_logged_in()) : ?>
            <li class="menu-item login-btn-item" style="display: inline-flex; align-items: center;">
                <a href="#" class="btn btn-filled openRegisterPopup" style="margin-left: 10px;">Register</a>
            </li>
            <li class="menu-item login-btn-item" style="display: inline-flex; align-items: center;">
                <a href="<?php echo site_url('login/'); ?>" class="btn btn-filled" style="margin-left: 10px;">Login</a>
            </li>
        <?php else : ?>
            <?php if (in_array('customer', $roles)) : ?>
                <li class="menu-item login-btn-item" style="display: inline-flex; align-items: center;">
                    <a href="<?php echo site_url('customer-profile'); ?>" class="btn btn-filled" style="margin-left: 10px;">Dashboard</a>
                </li>
                <li class="menu-item login-btn-item" style="display: inline-flex; align-items: center;">
                    <a href="<?php echo site_url('customer-order'); ?>" class="btn btn-filled" style="margin-left: 10px;">Order</a>
                </li>
                <li class="menu-item login-btn-item" style="display: inline-flex; align-items: center;">
                    <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-filled" style="margin-left: 10px;">Logout</a>
                </li>
            <?php elseif (in_array('provider', $roles)) : ?>
                <li class="menu-item login-btn-item" style="display: inline-flex; align-items: center;">
                    <a href="<?php echo site_url('provider-dashboard'); ?>" class="btn btn-filled" style="margin-left: 10px;">Dashboard</a>
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
