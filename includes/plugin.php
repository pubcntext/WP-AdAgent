<?php
/**
 * Main Plugin Class
 *
 * @package WP_AdAgent
 * @since   1.0.0
 */

namespace WP_AdAgent;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main Plugin Class
 *
 * Implements singleton pattern for the main plugin instance.
 *
 * @since 1.0.0
 */
class Plugin {

    /**
     * Plugin instance.
     *
     * @since 1.0.0
     * @var Plugin|null
     */
    private static $instance = null;

    /**
     * Admin instance.
     *
     * @since 1.0.0
     * @var Admin\Admin|null
     */
    public $admin = null;

    /**
     * Frontend instance.
     *
     * @since 1.0.0
     * @var Frontend\Scripts|null
     */
    public $frontend = null;

    /**
     * REST API instance.
     *
     * @since 1.0.0
     * @var API\REST_Endpoints|null
     */
    public $api = null;

    /**
     * Get plugin instance.
     *
     * @since 1.0.0
     * @return Plugin
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load required dependencies.
     *
     * @since 1.0.0
     */
    private function load_dependencies() {
        // Utils
        require_once WP_ADAGENT_PLUGIN_DIR . 'includes/utils/class-encryption.php';
        require_once WP_ADAGENT_PLUGIN_DIR . 'includes/utils/class-logger.php';

        // Database
        require_once WP_ADAGENT_PLUGIN_DIR . 'includes/database/class-schema.php';

        // API
        require_once WP_ADAGENT_PLUGIN_DIR . 'includes/api/class-rest-endpoints.php';
        require_once WP_ADAGENT_PLUGIN_DIR . 'includes/api/class-pubcontext-client.php';

        // Admin (only in admin context)
        if ( is_admin() ) {
            require_once WP_ADAGENT_PLUGIN_DIR . 'includes/admin/class-admin.php';
            require_once WP_ADAGENT_PLUGIN_DIR . 'includes/admin/class-settings.php';
            require_once WP_ADAGENT_PLUGIN_DIR . 'includes/admin/class-prebid-config.php';
            require_once WP_ADAGENT_PLUGIN_DIR . 'includes/admin/class-placements.php';
            require_once WP_ADAGENT_PLUGIN_DIR . 'includes/admin/class-supply-chain.php';
        }

        // Frontend
        require_once WP_ADAGENT_PLUGIN_DIR . 'includes/frontend/class-scripts.php';
        require_once WP_ADAGENT_PLUGIN_DIR . 'includes/frontend/class-blocks.php';
    }

    /**
     * Initialize hooks.
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        // Initialize components on 'init' hook
        add_action( 'init', array( $this, 'init' ) );

        // Load text domain
        add_action( 'init', array( $this, 'load_textdomain' ) );

        // Register REST API routes
        add_action( 'rest_api_init', array( $this, 'init_rest_api' ) );

        // Check WordPress version
        add_action( 'admin_notices', array( $this, 'check_requirements' ) );
    }

    /**
     * Initialize plugin components.
     *
     * @since 1.0.0
     */
    public function init() {
        // Register custom post type
        Database\Schema::register_post_types();

        // Initialize admin
        if ( is_admin() ) {
            $this->admin = new Admin\Admin();
        }

        // Initialize frontend
        $this->frontend = new Frontend\Scripts();

        // Initialize blocks
        new Frontend\Blocks();

        /**
         * Fires after the plugin is fully initialized.
         *
         * @since 1.0.0
         * @param Plugin $plugin The plugin instance.
         */
        do_action( 'wp_adagent_init', $this );
    }

    /**
     * Initialize REST API.
     *
     * @since 1.0.0
     */
    public function init_rest_api() {
        $this->api = new API\REST_Endpoints();
        $this->api->register_routes();
    }

    /**
     * Load plugin text domain.
     *
     * @since 1.0.0
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'wp-adagent',
            false,
            dirname( WP_ADAGENT_PLUGIN_BASENAME ) . '/languages'
        );
    }

    /**
     * Check plugin requirements.
     *
     * @since 1.0.0
     */
    public function check_requirements() {
        // Check WordPress version
        if ( version_compare( get_bloginfo( 'version' ), '6.0', '<' ) ) {
            ?>
            <div class="notice notice-error">
                <p>
                    <?php
                    printf(
                        /* translators: %s: Required WordPress version */
                        esc_html__( 'WP-AdAgent requires WordPress %s or higher. Please update WordPress to use this plugin.', 'wp-adagent' ),
                        '6.0'
                    );
                    ?>
                </p>
            </div>
            <?php
        }

        // Check PHP version
        if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
            ?>
            <div class="notice notice-error">
                <p>
                    <?php
                    printf(
                        /* translators: %s: Required PHP version */
                        esc_html__( 'WP-AdAgent requires PHP %s or higher. Please upgrade PHP to use this plugin.', 'wp-adagent' ),
                        '7.4'
                    );
                    ?>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Get plugin version.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_version() {
        return WP_ADAGENT_VERSION;
    }

    /**
     * Get plugin directory path.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_plugin_dir() {
        return WP_ADAGENT_PLUGIN_DIR;
    }

    /**
     * Get plugin URL.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_plugin_url() {
        return WP_ADAGENT_PLUGIN_URL;
    }

    /**
     * Prevent cloning.
     *
     * @since 1.0.0
     */
    private function __clone() {}

    /**
     * Prevent unserializing.
     *
     * @since 1.0.0
     */
    public function __wakeup() {
        throw new \Exception( 'Cannot unserialize singleton' );
    }
}
