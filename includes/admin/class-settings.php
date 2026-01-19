<?php
/**
 * Settings Class
 *
 * Handles plugin settings registration and validation.
 *
 * @package WP_AdAgent
 * @since   1.0.0
 */

namespace WP_AdAgent\Admin;

use WP_AdAgent\Utils\Encryption;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Settings Class
 *
 * @since 1.0.0
 */
class Settings {

    /**
     * Option group name.
     *
     * @since 1.0.0
     * @var string
     */
    const OPTION_GROUP = 'wp_adagent_settings';

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    /**
     * Register plugin settings.
     *
     * @since 1.0.0
     */
    public function register_settings() {
        // API Settings Section
        add_settings_section(
            'wp_adagent_api_section',
            __( 'Pubcontext API Settings', 'wp-adagent' ),
            array( $this, 'render_api_section' ),
            self::OPTION_GROUP
        );

        // API Endpoint
        register_setting(
            self::OPTION_GROUP,
            'wp_adagent_api_endpoint',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'esc_url_raw',
                'default'           => WP_ADAGENT_API_ENDPOINT,
            )
        );

        add_settings_field(
            'wp_adagent_api_endpoint',
            __( 'API Endpoint', 'wp-adagent' ),
            array( $this, 'render_api_endpoint_field' ),
            self::OPTION_GROUP,
            'wp_adagent_api_section'
        );

        // API Key
        register_setting(
            self::OPTION_GROUP,
            'wp_adagent_api_key',
            array(
                'type'              => 'string',
                'sanitize_callback' => array( $this, 'sanitize_api_key' ),
                'default'           => '',
            )
        );

        add_settings_field(
            'wp_adagent_api_key',
            __( 'API Key', 'wp-adagent' ),
            array( $this, 'render_api_key_field' ),
            self::OPTION_GROUP,
            'wp_adagent_api_section'
        );

        // Semantic Matching
        register_setting(
            self::OPTION_GROUP,
            'wp_adagent_semantic_enabled',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default'           => true,
            )
        );

        add_settings_field(
            'wp_adagent_semantic_enabled',
            __( 'Semantic Matching', 'wp-adagent' ),
            array( $this, 'render_semantic_enabled_field' ),
            self::OPTION_GROUP,
            'wp_adagent_api_section'
        );
    }

    /**
     * Render API section description.
     *
     * @since 1.0.0
     */
    public function render_api_section() {
        echo '<p>' . esc_html__( 'Configure your Pubcontext API connection settings.', 'wp-adagent' ) . '</p>';
    }

    /**
     * Render API endpoint field.
     *
     * @since 1.0.0
     */
    public function render_api_endpoint_field() {
        $value = get_option( 'wp_adagent_api_endpoint', WP_ADAGENT_API_ENDPOINT );
        ?>
        <input
            type="url"
            id="wp_adagent_api_endpoint"
            name="wp_adagent_api_endpoint"
            value="<?php echo esc_attr( $value ); ?>"
            class="regular-text"
            placeholder="https://api.pubcontext.com/match"
        />
        <p class="description">
            <?php esc_html_e( 'The Pubcontext API endpoint URL for semantic matching.', 'wp-adagent' ); ?>
        </p>
        <?php
    }

    /**
     * Render API key field.
     *
     * @since 1.0.0
     */
    public function render_api_key_field() {
        $encrypted_key = get_option( 'wp_adagent_api_key', '' );
        $has_key       = ! empty( $encrypted_key );
        ?>
        <input
            type="password"
            id="wp_adagent_api_key"
            name="wp_adagent_api_key"
            value=""
            class="regular-text"
            placeholder="<?php echo $has_key ? '••••••••••••••••' : ''; ?>"
            autocomplete="new-password"
        />
        <button type="button" id="wp-adagent-test-api" class="button">
            <?php esc_html_e( 'Test Connection', 'wp-adagent' ); ?>
        </button>
        <span id="wp-adagent-test-result"></span>
        <p class="description">
            <?php
            if ( $has_key ) {
                esc_html_e( 'API key is saved. Enter a new key to update it.', 'wp-adagent' );
            } else {
                esc_html_e( 'Enter your Pubcontext API key. Get one from the Pubcontext Dashboard.', 'wp-adagent' );
            }
            ?>
        </p>
        <?php
    }

    /**
     * Render semantic enabled field.
     *
     * @since 1.0.0
     */
    public function render_semantic_enabled_field() {
        $value = get_option( 'wp_adagent_semantic_enabled', true );
        ?>
        <label>
            <input
                type="checkbox"
                id="wp_adagent_semantic_enabled"
                name="wp_adagent_semantic_enabled"
                value="1"
                <?php checked( $value, true ); ?>
            />
            <?php esc_html_e( 'Enable semantic content matching for dynamic floor optimization', 'wp-adagent' ); ?>
        </label>
        <p class="description">
            <?php esc_html_e( 'When enabled, page content will be analyzed to match with relevant advertisers and adjust floor prices accordingly.', 'wp-adagent' ); ?>
        </p>
        <?php
    }

    /**
     * Sanitize API key.
     *
     * @since 1.0.0
     * @param string $value The API key value.
     * @return string Encrypted API key or existing value if empty.
     */
    public function sanitize_api_key( $value ) {
        // If empty, keep existing value
        if ( empty( $value ) ) {
            return get_option( 'wp_adagent_api_key', '' );
        }

        // Encrypt the new key
        return Encryption::encrypt( sanitize_text_field( $value ) );
    }

    /**
     * Get decrypted API key.
     *
     * @since 1.0.0
     * @return string Decrypted API key.
     */
    public static function get_api_key() {
        $encrypted_key = get_option( 'wp_adagent_api_key', '' );
        if ( empty( $encrypted_key ) ) {
            return '';
        }
        return Encryption::decrypt( $encrypted_key );
    }

    /**
     * Get API endpoint.
     *
     * @since 1.0.0
     * @return string API endpoint URL.
     */
    public static function get_api_endpoint() {
        return get_option( 'wp_adagent_api_endpoint', WP_ADAGENT_API_ENDPOINT );
    }

    /**
     * Check if semantic matching is enabled.
     *
     * @since 1.0.0
     * @return bool True if enabled.
     */
    public static function is_semantic_enabled() {
        return (bool) get_option( 'wp_adagent_semantic_enabled', true );
    }
}

// Initialize settings
new Settings();
