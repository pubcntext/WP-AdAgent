<?php
/**
 * Prebid Configuration Class
 *
 * Handles Prebid.js bidder configuration.
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
 * Prebid Config Class
 *
 * @since 1.0.0
 */
class Prebid_Config {

    /**
     * Option group name.
     *
     * @since 1.0.0
     * @var string
     */
    const OPTION_GROUP = 'wp_adagent_prebid';

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    /**
     * Register Prebid settings.
     *
     * @since 1.0.0
     */
    public function register_settings() {
        // Prebid Settings Section
        add_settings_section(
            'wp_adagent_prebid_section',
            __( 'Prebid.js Configuration', 'wp-adagent' ),
            array( $this, 'render_section' ),
            self::OPTION_GROUP
        );

        // Prebid Version
        register_setting(
            self::OPTION_GROUP,
            'wp_adagent_prebid_version',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '7.49.0',
            )
        );

        add_settings_field(
            'wp_adagent_prebid_version',
            __( 'Prebid.js Version', 'wp-adagent' ),
            array( $this, 'render_version_field' ),
            self::OPTION_GROUP,
            'wp_adagent_prebid_section'
        );

        // Auction Timeout
        register_setting(
            self::OPTION_GROUP,
            'wp_adagent_prebid_timeout',
            array(
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'default'           => WP_ADAGENT_DEFAULT_TIMEOUT,
            )
        );

        add_settings_field(
            'wp_adagent_prebid_timeout',
            __( 'Auction Timeout', 'wp-adagent' ),
            array( $this, 'render_timeout_field' ),
            self::OPTION_GROUP,
            'wp_adagent_prebid_section'
        );

        // Price Floors
        register_setting(
            self::OPTION_GROUP,
            'wp_adagent_prebid_price_floors',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default'           => true,
            )
        );

        add_settings_field(
            'wp_adagent_prebid_price_floors',
            __( 'Enable Price Floors', 'wp-adagent' ),
            array( $this, 'render_price_floors_field' ),
            self::OPTION_GROUP,
            'wp_adagent_prebid_section'
        );

        // Bidders Configuration
        register_setting(
            self::OPTION_GROUP,
            'wp_adagent_prebid_bidders',
            array(
                'type'              => 'string',
                'sanitize_callback' => array( $this, 'sanitize_bidders' ),
                'default'           => '{}',
            )
        );

        add_settings_field(
            'wp_adagent_prebid_bidders',
            __( 'Bidder Configuration', 'wp-adagent' ),
            array( $this, 'render_bidders_field' ),
            self::OPTION_GROUP,
            'wp_adagent_prebid_section'
        );
    }

    /**
     * Render section description.
     *
     * @since 1.0.0
     */
    public function render_section() {
        echo '<p>' . esc_html__( 'Configure Prebid.js header bidding settings and select your demand partners.', 'wp-adagent' ) . '</p>';
    }

    /**
     * Render version field.
     *
     * @since 1.0.0
     */
    public function render_version_field() {
        $value    = get_option( 'wp_adagent_prebid_version', '7.49.0' );
        $versions = array(
            '8.0.0'  => '8.0.0 (Latest)',
            '7.49.0' => '7.49.0 (Stable)',
            '7.48.0' => '7.48.0',
            '7.47.0' => '7.47.0',
        );
        ?>
        <select id="wp_adagent_prebid_version" name="wp_adagent_prebid_version">
            <?php foreach ( $versions as $ver => $label ) : ?>
                <option value="<?php echo esc_attr( $ver ); ?>" <?php selected( $value, $ver ); ?>>
                    <?php echo esc_html( $label ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">
            <?php esc_html_e( 'Select the Prebid.js version to use. Newer versions may have additional features but require testing.', 'wp-adagent' ); ?>
        </p>
        <?php
    }

    /**
     * Render timeout field.
     *
     * @since 1.0.0
     */
    public function render_timeout_field() {
        $value = get_option( 'wp_adagent_prebid_timeout', WP_ADAGENT_DEFAULT_TIMEOUT );
        ?>
        <input
            type="number"
            id="wp_adagent_prebid_timeout"
            name="wp_adagent_prebid_timeout"
            value="<?php echo esc_attr( $value ); ?>"
            min="1000"
            max="10000"
            step="100"
            class="small-text"
        />
        <span><?php esc_html_e( 'milliseconds', 'wp-adagent' ); ?></span>
        <p class="description">
            <?php esc_html_e( 'Maximum time to wait for bid responses. Recommended: 2000-3000ms.', 'wp-adagent' ); ?>
        </p>
        <?php
    }

    /**
     * Render price floors field.
     *
     * @since 1.0.0
     */
    public function render_price_floors_field() {
        $value = get_option( 'wp_adagent_prebid_price_floors', true );
        ?>
        <label>
            <input
                type="checkbox"
                id="wp_adagent_prebid_price_floors"
                name="wp_adagent_prebid_price_floors"
                value="1"
                <?php checked( $value, true ); ?>
            />
            <?php esc_html_e( 'Enable dynamic price floors based on semantic matching', 'wp-adagent' ); ?>
        </label>
        <p class="description">
            <?php esc_html_e( 'When enabled, floor prices will be adjusted based on content-advertiser alignment scores.', 'wp-adagent' ); ?>
        </p>
        <?php
    }

    /**
     * Render bidders field.
     *
     * @since 1.0.0
     */
    public function render_bidders_field() {
        $bidders           = json_decode( get_option( 'wp_adagent_prebid_bidders', '{}' ), true ) ?: array();
        $available_bidders = json_decode( WP_ADAGENT_AVAILABLE_BIDDERS, true );
        ?>
        <div id="wp-adagent-bidders-container">
            <p class="description" style="margin-bottom: 15px;">
                <?php esc_html_e( 'Select and configure the header bidding partners you want to enable.', 'wp-adagent' ); ?>
            </p>

            <?php foreach ( $available_bidders as $bidder_id => $bidder_config ) : ?>
                <?php
                $is_enabled = isset( $bidders[ $bidder_id ] );
                $params     = $is_enabled ? $bidders[ $bidder_id ] : array();
                ?>
                <div class="wp-adagent-bidder-card" data-bidder="<?php echo esc_attr( $bidder_id ); ?>">
                    <label class="wp-adagent-bidder-toggle">
                        <input
                            type="checkbox"
                            class="wp-adagent-bidder-enabled"
                            name="wp_adagent_bidders[<?php echo esc_attr( $bidder_id ); ?>][enabled]"
                            value="1"
                            <?php checked( $is_enabled ); ?>
                        />
                        <strong><?php echo esc_html( $bidder_config['label'] ); ?></strong>
                    </label>

                    <div class="wp-adagent-bidder-params" style="<?php echo $is_enabled ? '' : 'display: none;'; ?>">
                        <?php foreach ( $bidder_config['params'] as $param_id => $param_config ) : ?>
                            <div class="wp-adagent-param-field">
                                <label for="bidder_<?php echo esc_attr( $bidder_id . '_' . $param_id ); ?>">
                                    <?php echo esc_html( $param_config['label'] ); ?>
                                    <?php if ( $param_config['required'] ) : ?>
                                        <span class="required">*</span>
                                    <?php endif; ?>
                                </label>
                                <input
                                    type="<?php echo esc_attr( $param_config['type'] === 'number' ? 'number' : 'text' ); ?>"
                                    id="bidder_<?php echo esc_attr( $bidder_id . '_' . $param_id ); ?>"
                                    name="wp_adagent_bidders[<?php echo esc_attr( $bidder_id ); ?>][<?php echo esc_attr( $param_id ); ?>]"
                                    value="<?php echo esc_attr( isset( $params[ $param_id ] ) ? $params[ $param_id ] : '' ); ?>"
                                    class="regular-text"
                                    <?php echo $param_config['required'] ? 'required' : ''; ?>
                                />
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <input type="hidden" name="wp_adagent_prebid_bidders" id="wp_adagent_prebid_bidders" value="<?php echo esc_attr( wp_json_encode( $bidders ) ); ?>" />
        </div>
        <?php
    }

    /**
     * Sanitize bidders configuration.
     *
     * @since 1.0.0
     * @param string $value The bidders JSON string.
     * @return string Sanitized JSON string.
     */
    public function sanitize_bidders( $value ) {
        // Get the bidders from POST data
        if ( isset( $_POST['wp_adagent_bidders'] ) && is_array( $_POST['wp_adagent_bidders'] ) ) {
            $bidders           = array();
            $available_bidders = json_decode( WP_ADAGENT_AVAILABLE_BIDDERS, true );

            foreach ( $_POST['wp_adagent_bidders'] as $bidder_id => $bidder_data ) {
                if ( ! isset( $available_bidders[ $bidder_id ] ) ) {
                    continue;
                }

                if ( empty( $bidder_data['enabled'] ) ) {
                    continue;
                }

                $params = array();
                foreach ( $available_bidders[ $bidder_id ]['params'] as $param_id => $param_config ) {
                    if ( isset( $bidder_data[ $param_id ] ) ) {
                        $params[ $param_id ] = sanitize_text_field( $bidder_data[ $param_id ] );
                    }
                }

                $bidders[ $bidder_id ] = $params;
            }

            return wp_json_encode( $bidders );
        }

        return $value;
    }

    /**
     * Get Prebid configuration.
     *
     * @since 1.0.0
     * @return array Prebid configuration.
     */
    public static function get_config() {
        return array(
            'version'     => get_option( 'wp_adagent_prebid_version', '7.49.0' ),
            'timeout'     => absint( get_option( 'wp_adagent_prebid_timeout', WP_ADAGENT_DEFAULT_TIMEOUT ) ),
            'priceFloors' => (bool) get_option( 'wp_adagent_prebid_price_floors', true ),
            'bidders'     => json_decode( get_option( 'wp_adagent_prebid_bidders', '{}' ), true ) ?: array(),
        );
    }
}

// Initialize
new Prebid_Config();
