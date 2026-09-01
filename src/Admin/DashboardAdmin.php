<?php

namespace Cosy\Appointments\Admin;

class DashboardAdmin
{
    /**
     * RENDERS ADMIN ANALYTICS DASHBOARD & METRICS
     * 
     * USE CASE:
     * Callback renderer for the main 'CC Booking' admin dashboard landing page.
     * 
     * HOW TO USE:
     * (new DashboardAdmin())->render_booking_dashboard();
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Queries booking statistics (confirmed, pending, cancelled, total revenue).
     * 2. Queries provider and customer registration totals via WP_User_Query.
     * 3. Calculates monthly revenue trend metrics.
     * 4. Renders responsive dashboard layout with analytics charts and recent appointment lists.
     */
    public function render_booking_dashboard(): void
    {
        global $wpdb;

        // ── Real Stats from DB via WP_Query (more reliable than wp_count_posts) ──
        $confirmed = (int) (new \WP_Query([
            'post_type'   => 'cosy_appointment',
            'post_status' => 'publish',
            'fields'      => 'ids',
            'nopaging'    => true,
        ]))->found_posts;

        $pending = (int) (new \WP_Query([
            'post_type'   => 'cosy_appointment',
            'post_status' => 'draft',
            'fields'      => 'ids',
            'nopaging'    => true,
        ]))->found_posts;

        $cancelled = (int) (new \WP_Query([
            'post_type'   => 'cosy_appointment',
            'post_status' => 'trash',
            'fields'      => 'ids',
            'nopaging'    => true,
        ]))->found_posts;

        $total_bookings = $confirmed + $pending + $cancelled;

        $total_providers = (int) (new \WP_User_Query([
            'role'    => 'provider',
            'fields'  => 'ID',
            'number'  => -1,
        ]))->get_total();

        $total_customers = (int) (new \WP_User_Query([
            'role'    => 'customer',
            'fields'  => 'ID',
            'number'  => -1,
        ]))->get_total();

        // Total Revenue from paid appointments
        $revenue_rows = $wpdb->get_col(
            "SELECT pm.meta_value
             FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             WHERE pm.meta_key = 'cosy_total_payable'
             AND p.post_type = 'cosy_appointment'
             AND p.post_status = 'publish'"
        );
        $total_revenue = array_sum(array_map('floatval', $revenue_rows));
        $currency_symbol = cosy_get_currency_symbol();

        // ── Recent 8 Bookings ───────────────────────────────────────────
        $recent_bookings = get_posts([
            'post_type'      => 'cosy_appointment',
            'post_status'    => ['publish', 'draft', 'pending', 'trash'],
            'posts_per_page' => 8,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        // ── Monthly bookings for chart (last 6 months) ──────────────────
        $monthly_labels = [];
        $monthly_data   = [];
        for ($i = 5; $i >= 0; $i--) {
            $month_ts    = strtotime("-{$i} months");
            $month_label = date('M Y', $month_ts);
            $month_start = date('Y-m-01', $month_ts);
            $month_end   = date('Y-m-t', $month_ts);

            $count = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->posts}
                 WHERE post_type = 'cosy_appointment'
                 AND post_status IN ('publish','draft','pending')
                 AND post_date >= %s AND post_date <= %s",
                $month_start . ' 00:00:00',
                $month_end . ' 23:59:59'
            ));

            $monthly_labels[] = $month_label;
            $monthly_data[]   = $count;
        }

        wp_localize_script('cosy-dashboard-admin-script', 'cosyDashboardData', [
            'labels' => $monthly_labels,
            'data'   => $monthly_data,
        ]);
        ?>

        <div class="cdb-wrap">

            <!-- Page Header -->
            <div class="cdb-page-header">
                <h1><i class="fas fa-chart-bar cdb-header-icon"></i>Booking Dashboard</h1>
                <p>A real-time overview of your platform's appointments, providers, customers, and revenue.</p>
            </div>

            <!-- Stat Cards -->
            <div class="cdb-stats-grid">
                <div class="cdb-stat-card purple">
                    <div class="cdb-stat-icon"><i class="fas fa-calendar-alt"></i></div>
                    <div class="cdb-stat-value"><?php echo number_format($total_bookings); ?></div>
                    <div class="cdb-stat-label">Total Bookings</div>
                </div>
                <div class="cdb-stat-card emerald">
                    <div class="cdb-stat-icon"><i class="fas fa-wallet"></i></div>
                    <div class="cdb-stat-value"><?php echo $currency_symbol . number_format($total_revenue, 2); ?></div>
                    <div class="cdb-stat-label">Total Revenue</div>
                </div>
                <div class="cdb-stat-card green">
                    <div class="cdb-stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="cdb-stat-value"><?php echo number_format($confirmed); ?></div>
                    <div class="cdb-stat-label">Confirmed</div>
                </div>
                <div class="cdb-stat-card amber">
                    <div class="cdb-stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="cdb-stat-value"><?php echo number_format($pending); ?></div>
                    <div class="cdb-stat-label">Pending</div>
                </div>
                <div class="cdb-stat-card red">
                    <div class="cdb-stat-icon"><i class="fas fa-times-circle"></i></div>
                    <div class="cdb-stat-value"><?php echo number_format($cancelled); ?></div>
                    <div class="cdb-stat-label">Cancelled</div>
                </div>
                <div class="cdb-stat-card blue">
                    <div class="cdb-stat-icon"><i class="fas fa-user-tie"></i></div>
                    <div class="cdb-stat-value"><?php echo number_format($total_providers); ?></div>
                    <div class="cdb-stat-label">Providers</div>
                </div>
                <div class="cdb-stat-card teal">
                    <div class="cdb-stat-icon"><i class="fas fa-users"></i></div>
                    <div class="cdb-stat-value"><?php echo number_format($total_customers); ?></div>
                    <div class="cdb-stat-label">Customers</div>
                </div>
            </div>

            <!-- Bottom: Chart + Recent Bookings -->
            <div class="cdb-bottom-grid">

                <!-- Monthly Chart -->
                <div class="cdb-panel">
                    <div class="cdb-panel-header">
                        <i class="fas fa-chart-line"></i>
                        <h3>Monthly Bookings (Last 6 Months)</h3>
                    </div>
                    <div class="cdb-panel-body">
                        <div class="cdb-chart-wrap">
                            <canvas id="cosyMonthlyChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Bookings -->
                <div class="cdb-panel">
                    <div class="cdb-panel-header">
                        <i class="fas fa-list-alt"></i>
                        <h3>Recent Bookings</h3>
                    </div>
                    <?php if (empty($recent_bookings)): ?>
                        <div class="cdb-empty">
                            <i class="fas fa-calendar-times"></i>
                            No bookings found yet.
                        </div>
                    <?php else: ?>
                        <table class="cdb-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Customer</th>
                                    <th>Provider</th>
                                    <th>Service</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_bookings as $booking):
                                    $customer_id  = get_post_meta($booking->ID, 'cosy_customer_id', true);
                                    $provider_id  = get_post_meta($booking->ID, 'cosy_provider_id', true);
                                    $service_name = get_post_meta($booking->ID, 'cosy_service_name', true);
                                    $appt_date    = get_post_meta($booking->ID, 'cosy_start_date', true);
                                    $customer     = $customer_id ? get_userdata($customer_id) : null;
                                    $provider     = $provider_id ? get_userdata($provider_id) : null;
                                    $status       = $booking->post_status;
                                ?>
                                <tr>
                                    <td class="cdb-col-id">#<?php echo $booking->ID; ?></td>
                                    <td><?php echo $customer ? esc_html($customer->display_name) : '—'; ?></td>
                                    <td><?php echo $provider ? esc_html($provider->display_name) : '—'; ?></td>
                                    <td><?php echo $service_name ? esc_html($service_name) : '—'; ?></td>
                                    <td class="cdb-col-date"><?php echo $appt_date ? esc_html(cosy_format_date($appt_date)) : esc_html(cosy_format_date($booking->post_date)); ?></td>
                                    <td>
                                        <span class="cdb-badge <?php echo esc_attr($status); ?>">
                                            <?php echo $status === 'publish' ? 'Confirmed' : ucfirst($status); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

            </div><!-- .cdb-bottom-grid -->

        </div><!-- .cdb-wrap -->
        <?php
    }
}
