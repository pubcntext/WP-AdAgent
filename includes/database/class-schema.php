<?php
/**
 * Schema Class
 *
 * Handles database schema and custom post type registration.
 *
 * @package WP_AdAgent
 * @since   1.0.0
 */

namespace WP_AdAgent\Database;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Schema Class
 *
 * @since 1.0.0
 */
class Schema {

    /**
     * Register custom post types.
     *
     * @since 1.0.0
     */
    public static function register_post_types() {
        // Register placement post type
        register_post_type(
            WP_ADAGENT_PLACEMENT_POST_TYPE,
            array(
                'labels'              => array(
                    'name'               => __( 'Placements', 'wp-adagent' ),
                    'singular_name'      => __( 'Placement', 'wp-adagent' ),
                    'add_new'            => __( 'Add New', 'wp-adagent' ),
                    'add_new_item'       => __( 'Add New Placement', 'wp-adagent' ),
                    'edit_item'          => __( 'Edit Placement', 'wp-adagent' ),
                    'new_item'           => __( 'New Placement', 'wp-adagent' ),
                    'view_item'          => __( 'View Placement', 'wp-adagent' ),
                    'search_items'       => __( 'Search Placements', 'wp-adagent' ),
                    'not_found'          => __( 'No placements found', 'wp-adagent' ),
                    'not_found_in_trash' => __( 'No placements found in Trash', 'wp-adagent' ),
                    'all_items'          => __( 'All Placements', 'wp-adagent' ),
                    'menu_name'          => __( 'Placements', 'wp-adagent' ),
                ),
                'public'              => false,
                'show_ui'             => true,
                'show_in_menu'        => false, // We'll add it to our custom menu
                'show_in_rest'        => true,
                'rest_base'           => 'adagent-placements',
                'capability_type'     => 'post',
                'hierarchical'        => false,
                'supports'            => array( 'title' ),
                'has_archive'         => false,
                'rewrite'             => false,
                'query_var'           => false,
                'can_export'          => true,
            )
        );
    }

    /**
     * Create custom database tables.
     *
     * @since 1.0.0
     */
    public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Impressions table (for Phase 2 analytics)
        $table_name = $wpdb->prefix . 'adagent_impressions';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            placement_id VARCHAR(255) NOT NULL,
            post_id BIGINT(20) UNSIGNED DEFAULT NULL,
            page_url TEXT,
            timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            user_agent_hash VARCHAR(64) DEFAULT NULL,
            context_extracted LONGTEXT DEFAULT NULL,
            base_floor DECIMAL(10,4) DEFAULT NULL,
            semantic_score DECIMAL(5,4) DEFAULT NULL,
            final_floor DECIMAL(10,4) DEFAULT NULL,
            matched_creatives LONGTEXT DEFAULT NULL,
            bid_response LONGTEXT DEFAULT NULL,
            winning_bid DECIMAL(10,4) DEFAULT NULL,
            winning_bidder VARCHAR(255) DEFAULT NULL,
            publisher_revenue DECIMAL(10,4) DEFAULT NULL,
            pubcontext_fee DECIMAL(10,4) DEFAULT NULL,
            PRIMARY KEY (id),
            KEY placement_timestamp (placement_id, timestamp),
            KEY post_id (post_id),
            KEY timestamp (timestamp)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        // Store schema version
        update_option( 'wp_adagent_db_version', '1.0.0' );
    }

    /**
     * Drop custom database tables.
     *
     * @since 1.0.0
     */
    public static function drop_tables() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'adagent_impressions';
        $wpdb->query( "DROP TABLE IF EXISTS $table_name" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    }

    /**
     * Check if tables need upgrade.
     *
     * @since 1.0.0
     * @return bool True if upgrade needed.
     */
    public static function needs_upgrade() {
        $current_version = get_option( 'wp_adagent_db_version', '0' );
        return version_compare( $current_version, WP_ADAGENT_VERSION, '<' );
    }

    /**
     * Run database migrations.
     *
     * @since 1.0.0
     */
    public static function run_migrations() {
        $current_version = get_option( 'wp_adagent_db_version', '0' );

        // Run migrations based on version
        if ( version_compare( $current_version, '1.0.0', '<' ) ) {
            self::create_tables();
        }

        // Future migrations can be added here
        // if ( version_compare( $current_version, '1.1.0', '<' ) ) {
        //     self::migrate_to_1_1_0();
        // }
    }
}
