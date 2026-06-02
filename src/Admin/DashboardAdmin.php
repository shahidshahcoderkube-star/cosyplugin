<?php

namespace Cosy\Appointments\Admin;

class DashboardAdmin
{
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

        $chart_labels = json_encode($monthly_labels);
        $chart_data   = json_encode($monthly_data);
        ?>

        <style>
            @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;700;800&display=swap');

            .cdb-wrap * { box-sizing: border-box; }
            .cdb-wrap {
                font-family: 'Plus Jakarta Sans', sans-serif;
                color: #1e293b;
                margin: 20px 20px 40px 0;
            }

            /* Page header */
            .cdb-page-header {
                background: linear-gradient(135deg, #9b4593 0%, #6d2e67 100%);
                border-radius: 18px;
                padding: 32px 40px;
                color: #fff;
                margin-bottom: 28px;
                position: relative;
                overflow: hidden;
            }
            .cdb-page-header::after {
                content: '';
                position: absolute;
                top: -50px; right: -50px;
                width: 200px; height: 200px;
                border-radius: 50%;
                background: rgba(255,255,255,0.06);
            }
            .cdb-page-header h1 {
                font-family: 'Outfit', sans-serif;
                font-size: 1.9rem;
                font-weight: 800;
                color: #fff;
                margin: 0 0 6px 0;
                letter-spacing: -0.02em;
            }
            .cdb-page-header p {
                font-size: 0.95rem;
                opacity: 0.85;
                margin: 0;
                font-weight: 300;
            }

            /* Stat Cards */
            .cdb-stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 16px;
                margin-bottom: 28px;
            }
            .cdb-stat-card {
                background: #fff;
                border: 1px solid #eedced;
                border-radius: 14px;
                padding: 22px 20px;
                transition: all 0.25s ease;
                position: relative;
                overflow: hidden;
            }
            .cdb-stat-card::before {
                content: '';
                position: absolute;
                top: 0; left: 0;
                width: 4px; height: 100%;
                border-radius: 4px 0 0 4px;
            }
            .cdb-stat-card.purple::before  { background: #9b4593; }
            .cdb-stat-card.emerald::before { background: #10b981; }
            .cdb-stat-card.green::before   { background: #22c55e; }
            .cdb-stat-card.amber::before   { background: #f59e0b; }
            .cdb-stat-card.red::before     { background: #ef4444; }
            .cdb-stat-card.blue::before    { background: #3b82f6; }
            .cdb-stat-card.teal::before    { background: #14b8a6; }
            .cdb-stat-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 10px 25px rgba(155,69,147,0.1);
            }
            .cdb-stat-icon {
                width: 40px; height: 40px;
                border-radius: 10px;
                display: flex; align-items: center; justify-content: center;
                font-size: 1.15rem;
                margin-bottom: 14px;
            }
            .cdb-stat-card.purple  .cdb-stat-icon { background: #f1e4ef; color: #9b4593; }
            .cdb-stat-card.emerald .cdb-stat-icon { background: #d1fae5; color: #059669; }
            .cdb-stat-card.green   .cdb-stat-icon { background: #dcfce7; color: #16a34a; }
            .cdb-stat-card.amber   .cdb-stat-icon { background: #fef3c7; color: #d97706; }
            .cdb-stat-card.red     .cdb-stat-icon { background: #fee2e2; color: #dc2626; }
            .cdb-stat-card.blue    .cdb-stat-icon { background: #dbeafe; color: #2563eb; }
            .cdb-stat-card.teal    .cdb-stat-icon { background: #ccfbf1; color: #0d9488; }
            .cdb-stat-value {
                font-family: 'Outfit', sans-serif;
                font-size: 1.8rem;
                font-weight: 800;
                color: #0f172a;
                line-height: 1;
                margin-bottom: 4px;
            }
            .cdb-stat-label {
                font-size: 0.8rem;
                color: #64748b;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }

            /* Bottom grid: chart + table */
            .cdb-bottom-grid {
                display: grid;
                grid-template-columns: 1fr 1.6fr;
                gap: 20px;
            }

            /* Panels */
            .cdb-panel {
                background: #fff;
                border: 1px solid #eedced;
                border-radius: 14px;
                overflow: hidden;
            }
            .cdb-panel-header {
                padding: 20px 24px;
                border-bottom: 1px solid #f5eaf4;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .cdb-panel-header i { color: #9b4593; font-size: 1.1rem; }
            .cdb-panel-header h3 {
                font-family: 'Outfit', sans-serif;
                font-size: 1.05rem;
                font-weight: 700;
                color: #0f172a;
                margin: 0;
            }
            .cdb-panel-body { padding: 24px; }

            /* Chart */
            .cdb-chart-wrap { position: relative; height: 260px; }

            /* Table */
            .cdb-table { width: 100%; border-collapse: collapse; }
            .cdb-table th {
                background: #faf5fb;
                padding: 11px 16px;
                font-size: 0.78rem;
                font-weight: 700;
                color: #6d2e67;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                border-bottom: 1px solid #eedced;
                text-align: left;
                white-space: nowrap;
            }
            .cdb-table td {
                padding: 12px 16px;
                border-bottom: 1px solid #f5eaf4;
                font-size: 0.88rem;
                color: #334155;
                vertical-align: middle;
            }
            .cdb-table tr:last-child td { border-bottom: none; }
            .cdb-table tr:hover td { background: #fdf8fe; }
            .cdb-badge {
                display: inline-block;
                padding: 3px 10px;
                border-radius: 20px;
                font-size: 0.76rem;
                font-weight: 700;
            }
            .cdb-badge.paid     { background: #dcfce7; color: #16a34a; }
            .cdb-badge.pending  { background: #fef3c7; color: #b45309; }
            .cdb-badge.draft    { background: #fef3c7; color: #b45309; }
            .cdb-badge.cancelled{ background: #fee2e2; color: #dc2626; }
            .cdb-badge.publish  { background: #dcfce7; color: #16a34a; }
            .cdb-badge.trash    { background: #fee2e2; color: #dc2626; }

            .cdb-empty {
                text-align: center;
                padding: 40px 20px;
                color: #94a3b8;
                font-size: 0.9rem;
            }
            .cdb-empty i { font-size: 2rem; margin-bottom: 10px; display: block; }
        </style>

        <div class="cdb-wrap">

            <!-- Page Header -->
            <div class="cdb-page-header">
                <h1><i class="fas fa-chart-bar" style="margin-right:10px;"></i>Booking Dashboard</h1>
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
                                    <td style="color:#94a3b8;font-size:0.8rem;">#<?php echo $booking->ID; ?></td>
                                    <td><?php echo $customer ? esc_html($customer->display_name) : '—'; ?></td>
                                    <td><?php echo $provider ? esc_html($provider->display_name) : '—'; ?></td>
                                    <td><?php echo $service_name ? esc_html($service_name) : '—'; ?></td>
                                    <td style="white-space:nowrap;"><?php echo $appt_date ? esc_html($appt_date) : esc_html(date('M d, Y', strtotime($booking->post_date))); ?></td>
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

        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        (function() {
            const ctx = document.getElementById('cosyMonthlyChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo $chart_labels; ?>,
                    datasets: [{
                        label: 'Bookings',
                        data: <?php echo $chart_data; ?>,
                        borderColor: '#9b4593',
                        backgroundColor: 'rgba(155,69,147,0.08)',
                        borderWidth: 2.5,
                        pointBackgroundColor: '#9b4593',
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        tension: 0.4,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: '#94a3b8', font: { size: 12 } },
                            grid: { color: '#f5eaf4' }
                        },
                        x: {
                            ticks: { color: '#94a3b8', font: { size: 12 } },
                            grid: { display: false }
                        }
                    }
                }
            });
        })();
        </script>
        <?php
    }
}
