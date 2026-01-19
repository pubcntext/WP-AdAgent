<?php
/**
 * Plugin Installation
 *
 * Handles plugin activation routines.
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
 * Run installation routines.
 *
 * @since 1.0.0
 */
function install() {
    // Create database tables
    Schema::create_tables();

    // Set default options
    $defaults = array(
        'wp_adagent_version'              => WP_ADAGENT_VERSION,
        'wp_adagent_installed_at'         => current_time( 'mysql' ),
        'wp_adagent_api_endpoint'         => WP_ADAGENT_API_ENDPOINT,
        'wp_adagent_semantic_enabled'     => true,
        'wp_adagent_prebid_version'       => '7.49.0',
        'wp_adagent_prebid_timeout'       => WP_ADAGENT_DEFAULT_TIMEOUT,
        'wp_adagent_prebid_price_floors'  => true,
        'wp_adagent_prebid_bidders'       => '{}',
        'wp_adagent_supply_chain'         => '',
    );

    foreach ( $defaults as $option => $value ) {
        if ( false === get_option( $option ) ) {
            add_option( $option, $value );
        }
    }

    // Set transient to show welcome notice
    set_transient( 'wp_adagent_show_welcome', true, 60 );

    // Clear any cached data
    delete_transient( 'wp_adagent_config' );

    // Log installation
    if ( WP_ADAGENT_DEBUG ) {
        error_log( '[WP-AdAgent] Plugin installed/activated - Version ' . WP_ADAGENT_VERSION );
    }
}

/**
 * Run upgrade routines.
 *
 * @since 1.0.0
 */
function upgrade() {
    $current_version = get_option( 'wp_adagent_version', '0' );

    if ( version_compare( $current_version, WP_ADAGENT_VERSION, '<' ) ) {
        // Run database migrations
        Schema::run_migrations();

        // Update version
        update_option( 'wp_adagent_version', WP_ADAGENT_VERSION );

        // Clear cached data
        delete_transient( 'wp_adagent_config' );

        // Log upgrade
        if ( WP_ADAGENT_DEBUG ) {
            error_log( sprintf(
                '[WP-AdAgent] Plugin upgraded from %s to %s',
                $current_version,
                WP_ADAGENT_VERSION
            ) );
        }
    }
}

/**
 * Run uninstall routines.
 *
 * @since 1.0.0
 */
function uninstall() {
    // Only run if user has permission
    if ( ! current_user_can( 'activate_plugins' ) ) {
        return;
    }

    // Remove all plugin options
    $options = array(
        'wp_adagent_version',
        'wp_adagent_installed_at',
        'wp_adagent_db_version',
        'wp_adagent_api_key',
        'wp_adagent_api_endpoint',
        'wp_adagent_semantic_enabled',
        'wp_adagent_prebid_version',
        'wp_adagent_prebid_timeout',
        'wp_adagent_prebid_price_floors',
        'wp_adagent_prebid_bidders',
        'wp_adagent_supply_chain',
    );

    foreach ( $options as $option ) {
        delete_option( $option );
    }

    // Remove transients
    delete_transient( 'wp_adagent_config' );
    delete_transient( 'wp_adagent_show_welcome' );

    // Remove custom post type data
    $placements = get_posts( array(
        'post_type'   => WP_ADAGENT_PLACEMENT_POST_TYPE,
        'numberposts' => -1,
        'post_status' => 'any',
    ) );

    foreach ( $placements as $placement ) {
        wp_delete_post( $placement->ID, true );
    }

    // Drop custom tables (optional - uncomment if you want to remove data on uninstall)
    // Schema::drop_tables();

    // Log uninstall
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( '[WP-AdAgent] Plugin uninstalled - All data removed' );
    }
}
