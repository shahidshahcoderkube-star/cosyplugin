<?php

namespace Cosy\Appointments\Common;

if (!defined('ABSPATH')) {
    exit;
}

class Cron
{
    /**
     * Daily Cron Job to clean up activity logs older than 30 days.
     */
    public function do_cleanup_activity_logs(): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cosy_activity_logs';
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $table_name WHERE timestamp < DATE_SUB(NOW(), INTERVAL 30 DAY)"
            )
        );
        $deleted_rows = intval($wpdb->rows_affected);
        if ($deleted_rows > 0) {
            LogManager::log(
                'logs',
                'cron_cleanup',
                sprintf(__('Daily automatic cron cleanup deleted %d activity logs older than 30 days.', 'cosy-appointments'), $deleted_rows)
            );
        }
    }
}
