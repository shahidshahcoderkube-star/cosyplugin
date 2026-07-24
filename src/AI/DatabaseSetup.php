<?php

namespace Cosy\Appointments\AI;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * DatabaseSetup Class
 * Handles database table creation for AI Provider Embeddings and Search Cache.
 */
class DatabaseSetup
{
    /**
     * Create or update AI database tables.
     */
    public static function create_tables(): void
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // 1. Table: wp_provider_embeddings
        $table_embeddings = $wpdb->prefix . 'provider_embeddings';
        $sql_embeddings = "CREATE TABLE $table_embeddings (
            provider_id bigint(20) unsigned NOT NULL,
            embedding longtext NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (provider_id)
        ) $charset_collate;";
        dbDelta($sql_embeddings);

        // 2. Table: wp_cosychats_search_cache
        $table_cache = $wpdb->prefix . 'cosychats_search_cache';
        $sql_cache = "CREATE TABLE $table_cache (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            query_hash varchar(32) NOT NULL,
            query_text text NOT NULL,
            matching_provider_ids longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY query_hash (query_hash)
        ) $charset_collate;";
        dbDelta($sql_cache);
    }
}
