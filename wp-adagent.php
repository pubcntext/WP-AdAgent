<?php
/**
 * Plugin Name:       WP-AdAgent (Pubcontext Semantic DSP)
 * Plugin URI:        https://github.com/pubcntext/WP-AdAgent
 * Description:       Prebid.js header bidding plugin for WordPress with semantic audience matching. Optimize publisher revenue through intelligent creative matching and dynamic floor price adjustment.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Pubcontext
 * Author URI:        https://pubcontext.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-adagent
 * Domain Path:       /languages
 *
 * @package           WP_AdAgent
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'WP_ADAGENT_VERSION', '1.0.0' );
define( 'WP_ADAGENT_PLUGIN_FILE', __FILE__ );
define( 'WP_ADAGENT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_ADAGENT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_ADAGENT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Load constants
require_once WP_ADAGENT_PLUGIN_DIR . 'includes/constants.php';

// Load the main plugin class
require_once WP_ADAGENT_PLUGIN_DIR . 'includes/plugin.php';

/**
 * Initialize the plugin.
 *
 * @since 1.0.0
 * @return WP_AdAgent\Plugin
 */
function wp_adagent() {
    return WP_AdAgent\Plugin::get_instance();
}

// Start the plugin
add_action( 'plugins_loaded', 'wp_adagent' );

/**
 * Plugin activation hook.
 *
 * @since 1.0.0
 */
function wp_adagent_activate() {
    require_once WP_ADAGENT_PLUGIN_DIR . 'includes/database/install.php';
    WP_AdAgent\Database\install();

    // Set default options
    add_option( 'wp_adagent_version', WP_ADAGENT_VERSION );
    add_option( 'wp_adagent_installed_at', current_time( 'mysql' ) );
    add_option( 'wp_adagent_semantic_enabled', true );
    add_option( 'wp_adagent_prebid_timeout', 3000 );
    add_option( 'wp_adagent_prebid_version', '7.49.0' );

    // Flush rewrite rules for custom post type
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wp_adagent_activate' );

/**
 * Plugin deactivation hook.
 *
 * @since 1.0.0
 */
function wp_adagent_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'wp_adagent_deactivate' );

/**
 * Plugin uninstall hook.
 * Note: This should ideally be in uninstall.php for better performance.
 *
 * @since 1.0.0
 */
function wp_adagent_uninstall() {
    // Remove all plugin options
    delete_option( 'wp_adagent_version' );
    delete_option( 'wp_adagent_installed_at' );
    delete_option( 'wp_adagent_api_key' );
    delete_option( 'wp_adagent_api_endpoint' );
    delete_option( 'wp_adagent_semantic_enabled' );
    delete_option( 'wp_adagent_prebid_version' );
    delete_option( 'wp_adagent_prebid_timeout' );
    delete_option( 'wp_adagent_prebid_bidders' );
    delete_option( 'wp_adagent_prebid_price_floors' );
    delete_option( 'wp_adagent_supply_chain' );

    // Remove custom post type data
    $placements = get_posts( array(
        'post_type'   => 'wp_adagent_placement',
        'numberposts' => -1,
        'post_status' => 'any',
    ) );

    foreach ( $placements as $placement ) {
        wp_delete_post( $placement->ID, true );
    }
}
