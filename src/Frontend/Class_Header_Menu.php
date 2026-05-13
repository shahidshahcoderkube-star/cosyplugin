<?php

namespace Cosy\Appointments\Frontend;

class Class_Header_Menu
{
    public function register($loader): void
    {
        // Filter to add menu items to the primary navigation
        $loader->add_filter('wp_nav_menu_items', $this, 'add_services_dropdown', 10, 2);

        // Action to inject CSS into the head
        $loader->add_action('wp_head', $this, 'inject_dropdown_styles');
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

        ob_start();
        ?>
        <!-- Separate Service Provider Link (Simple Link) -->
        <li class="menu-item cosy-header-simple-link">
            <a href="<?php echo home_url('/service-provider/'); ?>">
                Service Provider
            </a>
        </li>

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
        <?php
        $dropdown_html = ob_get_clean();

        return $items . $dropdown_html;
    }

    /**
     * Inject custom styles for the premium dropdown look
     */
    public function inject_dropdown_styles(): void
    {
        ?>
        <style>
            /* Premium Dropdown Styles */
            .cosy-header-dropdown-wrapper,
            .cosy-header-simple-link {
                position: relative;
                list-style: none !important;
                display: inline-block !important;
                margin-left: 20px !important;
                vertical-align: middle !important;
            }

            .cosy-header-simple-link a {
                color: #475569 !important;
                font-weight: 500 !important;
                text-decoration: none !important;
                font-family: 'Poppins', sans-serif !important;
                font-size: 0.95rem !important;
                transition: color 0.3s ease;
            }

            .cosy-header-simple-link a:hover {
                color: #a44390 !important;
            }

            .cosy-dropdown-toggle {
                display: inline-flex !important;
                align-items: center !important;
                gap: 8px !important;
                padding: 8px 24px !important;
                border: 1.5px solid #a44390 !important;
                border-radius: 50px !important;
                color: #a44390 !important;
                font-weight: 600 !important;
                font-family: 'Poppins', sans-serif !important;
                text-decoration: none !important;
                transition: all 0.3s ease !important;
                background: transparent !important;
                font-size: 0.95rem !important;
            }

            .cosy-dropdown-toggle:hover {
                background: rgba(164, 67, 144, 0.05) !important;
                box-shadow: 0 4px 12px rgba(164, 67, 144, 0.15) !important;
            }

            .cosy-arrow {
                font-size: 0.8rem;
                transition: transform 0.3s ease;
            }

            /* Submenu Styling */
            .cosy-custom-submenu {
                position: absolute !important;
                top: 120% !important;
                left: 50% !important;
                transform: translateX(-50%) translateY(10px) !important;
                background: #ffffff !important;
                min-width: 220px !important;
                border-radius: 16px !important;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12) !important;
                padding: 12px !important;
                opacity: 0 !important;
                visibility: hidden !important;
                transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55) !important;
                z-index: 99999 !important;
                list-style: none !important;
                border: 1px solid rgba(0, 0, 0, 0.05) !important;
            }

            .cosy-header-dropdown-wrapper:hover .cosy-custom-submenu {
                opacity: 1 !important;
                visibility: visible !important;
                transform: translateX(-50%) translateY(0) !important;
            }

            .cosy-header-dropdown-wrapper:hover .cosy-arrow {
                transform: rotate(180deg);
            }

            .cosy-custom-submenu li {
                margin: 0 !important;
                padding: 0 !important;
            }

            .cosy-custom-submenu li a {
                display: block !important;
                padding: 10px 16px !important;
                color: #475569 !important;
                font-weight: 500 !important;
                font-size: 0.9rem !important;
                text-decoration: none !important;
                border-radius: 10px !important;
                transition: all 0.2s ease !important;
                text-align: left !important;
            }

            .cosy-custom-submenu li a:hover {
                background: rgba(164, 67, 144, 0.08) !important;
                color: #a44390 !important;
                padding-left: 20px !important;
            }

            /* Fix for some themes menu wrapping */
            #wpadminbar {
                z-index: 99999 !important;
            }
        </style>
        <?php
    }
}
