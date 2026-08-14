<?php

namespace Cosy\Appointments\Common;

if (!defined('ABSPATH')) {
    exit;
}

class Cron
{
    /**
     * EXECUTES DAILY ACTIVITY LOGS CLEANUP
     * 
     * USE CASE:
     * Triggered daily by WordPress cron event 'cosy_cleanup_activity_logs_cron' to prune old logs.
     * 
     * HOW TO USE:
     * (new Cron())->do_cleanup_activity_logs();
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Prepares DELETE SQL query for rows older than 30 days in wp_cosy_activity_logs table.
     * 2. Executes query via $wpdb->query().
     * 3. Logs count of pruned rows to LogManager if any logs were deleted.
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
