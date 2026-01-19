<?php
/**
 * Admin Class
 *
 * Handles admin menu and page registration.
 *
 * @package WP_AdAgent
 * @since   1.0.0
 */

namespace WP_AdAgent\Admin;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin Class
 *
 * @since 1.0.0
 */
class Admin {

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_notices', array( $this, 'display_welcome_notice' ) );
    }

    /**
     * Register admin menu.
     *
     * @since 1.0.0
     */
    public function register_menu() {
        // Main menu
        add_menu_page(
            __( 'Pubcontext', 'wp-adagent' ),
            __( 'Pubcontext', 'wp-adagent' ),
            WP_ADAGENT_ADMIN_CAP,
            'wp-adagent',
            array( $this, 'render_dashboard_page' ),
            'dashicons-megaphone',
            30
        );

        // Dashboard submenu (same as main)
        add_submenu_page(
            'wp-adagent',
            __( 'Dashboard', 'wp-adagent' ),
            __( 'Dashboard', 'wp-adagent' ),
            WP_ADAGENT_ADMIN_CAP,
            'wp-adagent',
            array( $this, 'render_dashboard_page' )
        );

        // Settings submenu
        add_submenu_page(
            'wp-adagent',
            __( 'Settings', 'wp-adagent' ),
            __( 'Settings', 'wp-adagent' ),
            WP_ADAGENT_ADMIN_CAP,
            'wp-adagent-settings',
            array( $this, 'render_settings_page' )
        );

        // Prebid Configuration submenu
        add_submenu_page(
            'wp-adagent',
            __( 'Prebid Configuration', 'wp-adagent' ),
            __( 'Prebid Config', 'wp-adagent' ),
            WP_ADAGENT_ADMIN_CAP,
            'wp-adagent-prebid',
            array( $this, 'render_prebid_page' )
        );

        // Placements submenu
        add_submenu_page(
            'wp-adagent',
            __( 'Ad Placements', 'wp-adagent' ),
            __( 'Placements', 'wp-adagent' ),
            WP_ADAGENT_ADMIN_CAP,
            'wp-adagent-placements',
            array( $this, 'render_placements_page' )
        );

        // Supply Chain submenu
        add_submenu_page(
            'wp-adagent',
            __( 'Supply Chain', 'wp-adagent' ),
            __( 'Supply Chain', 'wp-adagent' ),
            WP_ADAGENT_ADMIN_CAP,
            'wp-adagent-supply-chain',
            array( $this, 'render_supply_chain_page' )
        );

        // Analytics submenu
        add_submenu_page(
            'wp-adagent',
            __( 'Analytics', 'wp-adagent' ),
            __( 'Analytics', 'wp-adagent' ),
            WP_ADAGENT_ADMIN_CAP,
            'wp-adagent-analytics',
            array( $this, 'render_analytics_page' )
        );
    }

    /**
     * Enqueue admin scripts and styles.
     *
     * @since 1.0.0
     * @param string $hook Current admin page hook.
     */
    public function enqueue_scripts( $hook ) {
        // Only load on our plugin pages
        if ( strpos( $hook, 'wp-adagent' ) === false ) {
            return;
        }

        // Enqueue admin styles
        wp_enqueue_style(
            'wp-adagent-admin',
            WP_ADAGENT_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WP_ADAGENT_VERSION
        );

        // Enqueue admin scripts
        wp_enqueue_script(
            'wp-adagent-admin',
            WP_ADAGENT_PLUGIN_URL . 'assets/js/admin/admin.js',
            array( 'jquery' ),
            WP_ADAGENT_VERSION,
            true
        );

        // Localize script
        wp_localize_script(
            'wp-adagent-admin',
            'wpAdAgentAdmin',
            array(
                'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
                'restUrl'  => rest_url( WP_ADAGENT_REST_NAMESPACE ),
                'nonce'    => wp_create_nonce( 'wp_rest' ),
                'strings'  => array(
                    'saving'      => __( 'Saving...', 'wp-adagent' ),
                    'saved'       => __( 'Settings saved!', 'wp-adagent' ),
                    'error'       => __( 'An error occurred. Please try again.', 'wp-adagent' ),
                    'testing'     => __( 'Testing connection...', 'wp-adagent' ),
                    'testSuccess' => __( 'Connection successful!', 'wp-adagent' ),
                    'testFailed'  => __( 'Connection failed. Please check your API key.', 'wp-adagent' ),
                ),
            )
        );

        // Page-specific scripts
        if ( strpos( $hook, 'settings' ) !== false ) {
            wp_enqueue_script(
                'wp-adagent-settings',
                WP_ADAGENT_PLUGIN_URL . 'assets/js/admin/settings.js',
                array( 'wp-adagent-admin' ),
                WP_ADAGENT_VERSION,
                true
            );
        }

        if ( strpos( $hook, 'prebid' ) !== false ) {
            wp_enqueue_script(
                'wp-adagent-bidders',
                WP_ADAGENT_PLUGIN_URL . 'assets/js/admin/bidders.js',
                array( 'wp-adagent-admin' ),
                WP_ADAGENT_VERSION,
                true
            );

            wp_localize_script(
                'wp-adagent-bidders',
                'wpAdAgentBidders',
                array(
                    'availableBidders' => json_decode( WP_ADAGENT_AVAILABLE_BIDDERS, true ),
                )
            );
        }

        if ( strpos( $hook, 'placements' ) !== false ) {
            wp_enqueue_script(
                'wp-adagent-placements',
                WP_ADAGENT_PLUGIN_URL . 'assets/js/admin/placements.js',
                array( 'wp-adagent-admin' ),
                WP_ADAGENT_VERSION,
                true
            );
        }
    }

    /**
     * Display welcome notice on activation.
     *
     * @since 1.0.0
     */
    public function display_welcome_notice() {
        // Check if we should show the welcome notice
        if ( ! get_transient( 'wp_adagent_show_welcome' ) ) {
            return;
        }

        // Delete the transient
        delete_transient( 'wp_adagent_show_welcome' );

        ?>
        <div class="notice notice-success is-dismissible">
            <h3><?php esc_html_e( 'Welcome to WP-AdAgent!', 'wp-adagent' ); ?></h3>
            <p>
                <?php esc_html_e( 'Thank you for installing WP-AdAgent. Get started by configuring your Pubcontext API settings.', 'wp-adagent' ); ?>
            </p>
            <p>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-adagent-settings' ) ); ?>" class="button button-primary">
                    <?php esc_html_e( 'Configure Settings', 'wp-adagent' ); ?>
                </a>
                <a href="https://docs.pubcontext.com" target="_blank" class="button">
                    <?php esc_html_e( 'View Documentation', 'wp-adagent' ); ?>
                </a>
            </p>
        </div>
        <?php
    }

    /**
     * Render dashboard page.
     *
     * @since 1.0.0
     */
    public function render_dashboard_page() {
        include WP_ADAGENT_PLUGIN_DIR . 'templates/admin/dashboard-page.php';
    }

    /**
     * Render settings page.
     *
     * @since 1.0.0
     */
    public function render_settings_page() {
        include WP_ADAGENT_PLUGIN_DIR . 'templates/admin/settings-page.php';
    }

    /**
     * Render Prebid configuration page.
     *
     * @since 1.0.0
     */
    public function render_prebid_page() {
        include WP_ADAGENT_PLUGIN_DIR . 'templates/admin/prebid-config-page.php';
    }

    /**
     * Render placements page.
     *
     * @since 1.0.0
     */
    public function render_placements_page() {
        include WP_ADAGENT_PLUGIN_DIR . 'templates/admin/placements-page.php';
    }

    /**
     * Render supply chain page.
     *
     * @since 1.0.0
     */
    public function render_supply_chain_page() {
        include WP_ADAGENT_PLUGIN_DIR . 'templates/admin/supply-chain-page.php';
    }

    /**
     * Render analytics page.
     *
     * @since 1.0.0
     */
    public function render_analytics_page() {
        include WP_ADAGENT_PLUGIN_DIR . 'templates/admin/analytics-page.php';
    }
}
