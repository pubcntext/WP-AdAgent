<?php
/**
 * Supply Chain Class
 *
 * Handles supply chain transparency configuration.
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
 * Supply Chain Class
 *
 * @since 1.0.0
 */
class Supply_Chain {

    /**
     * Option group name.
     *
     * @since 1.0.0
     * @var string
     */
    const OPTION_GROUP = 'wp_adagent_supply_chain';

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    /**
     * Register supply chain settings.
     *
     * @since 1.0.0
     */
    public function register_settings() {
        // Supply Chain Section
        add_settings_section(
            'wp_adagent_supply_chain_section',
            __( 'Supply Chain Configuration', 'wp-adagent' ),
            array( $this, 'render_section' ),
            self::OPTION_GROUP
        );

        // Supply Chain JSON
        register_setting(
            self::OPTION_GROUP,
            'wp_adagent_supply_chain',
            array(
                'type'              => 'string',
                'sanitize_callback' => array( $this, 'sanitize_supply_chain' ),
                'default'           => '',
            )
        );

        add_settings_field(
            'wp_adagent_supply_chain',
            __( 'Supply Chain Object (schain)', 'wp-adagent' ),
            array( $this, 'render_supply_chain_field' ),
            self::OPTION_GROUP,
            'wp_adagent_supply_chain_section'
        );
    }

    /**
     * Render section description.
     *
     * @since 1.0.0
     */
    public function render_section() {
        ?>
        <p>
            <?php esc_html_e( 'Configure your supply chain transparency information. This helps maintain trust in the programmatic advertising ecosystem.', 'wp-adagent' ); ?>
        </p>
        <p>
            <a href="https://iabtechlab.com/sellers-json/" target="_blank" rel="noopener">
                <?php esc_html_e( 'Learn more about sellers.json and supply chain', 'wp-adagent' ); ?>
            </a>
        </p>
        <?php
    }

    /**
     * Render supply chain field.
     *
     * @since 1.0.0
     */
    public function render_supply_chain_field() {
        $value = get_option( 'wp_adagent_supply_chain', '' );

        // Format JSON for display
        if ( ! empty( $value ) ) {
            $decoded = json_decode( $value, true );
            if ( json_last_error() === JSON_ERROR_NONE ) {
                $value = wp_json_encode( $decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
            }
        }
        ?>
        <textarea
            id="wp_adagent_supply_chain"
            name="wp_adagent_supply_chain"
            rows="15"
            cols="80"
            class="large-text code"
            placeholder='{
  "ver": "1.0",
  "complete": 1,
  "nodes": [
    {
      "asi": "yourdomain.com",
      "sid": "pub-123456789",
      "hp": 1
    }
  ]
}'
        ><?php echo esc_textarea( $value ); ?></textarea>

        <p class="description">
            <?php esc_html_e( 'Enter your supply chain configuration as a JSON object. This will be included in all Prebid bid requests.', 'wp-adagent' ); ?>
        </p>

        <div id="wp-adagent-schain-validation" class="notice inline" style="display: none;"></div>

        <h4><?php esc_html_e( 'Supply Chain Fields Reference', 'wp-adagent' ); ?></h4>
        <table class="widefat" style="max-width: 600px;">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Field', 'wp-adagent' ); ?></th>
                    <th><?php esc_html_e( 'Description', 'wp-adagent' ); ?></th>
                    <th><?php esc_html_e( 'Required', 'wp-adagent' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>ver</code></td>
                    <td><?php esc_html_e( 'Version of the supply chain spec (use "1.0")', 'wp-adagent' ); ?></td>
                    <td><?php esc_html_e( 'Yes', 'wp-adagent' ); ?></td>
                </tr>
                <tr>
                    <td><code>complete</code></td>
                    <td><?php esc_html_e( '1 if all nodes are included, 0 otherwise', 'wp-adagent' ); ?></td>
                    <td><?php esc_html_e( 'Yes', 'wp-adagent' ); ?></td>
                </tr>
                <tr>
                    <td><code>nodes</code></td>
                    <td><?php esc_html_e( 'Array of supply chain nodes', 'wp-adagent' ); ?></td>
                    <td><?php esc_html_e( 'Yes', 'wp-adagent' ); ?></td>
                </tr>
                <tr>
                    <td><code>asi</code></td>
                    <td><?php esc_html_e( 'Canonical domain of the SSP/exchange', 'wp-adagent' ); ?></td>
                    <td><?php esc_html_e( 'Yes', 'wp-adagent' ); ?></td>
                </tr>
                <tr>
                    <td><code>sid</code></td>
                    <td><?php esc_html_e( 'Seller ID in the sellers.json file', 'wp-adagent' ); ?></td>
                    <td><?php esc_html_e( 'Yes', 'wp-adagent' ); ?></td>
                </tr>
                <tr>
                    <td><code>hp</code></td>
                    <td><?php esc_html_e( '1 if payment flows through this node', 'wp-adagent' ); ?></td>
                    <td><?php esc_html_e( 'Yes', 'wp-adagent' ); ?></td>
                </tr>
                <tr>
                    <td><code>rid</code></td>
                    <td><?php esc_html_e( 'Request ID for tracking', 'wp-adagent' ); ?></td>
                    <td><?php esc_html_e( 'No', 'wp-adagent' ); ?></td>
                </tr>
                <tr>
                    <td><code>name</code></td>
                    <td><?php esc_html_e( 'Name of the company', 'wp-adagent' ); ?></td>
                    <td><?php esc_html_e( 'No', 'wp-adagent' ); ?></td>
                </tr>
                <tr>
                    <td><code>domain</code></td>
                    <td><?php esc_html_e( 'Business domain of the node', 'wp-adagent' ); ?></td>
                    <td><?php esc_html_e( 'No', 'wp-adagent' ); ?></td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * Sanitize supply chain JSON.
     *
     * @since 1.0.0
     * @param string $value The supply chain JSON.
     * @return string Sanitized JSON or empty string.
     */
    public function sanitize_supply_chain( $value ) {
        if ( empty( $value ) ) {
            return '';
        }

        // Try to parse JSON
        $decoded = json_decode( $value, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            add_settings_error(
                'wp_adagent_supply_chain',
                'invalid_json',
                __( 'Invalid JSON format. Please check your supply chain configuration.', 'wp-adagent' ),
                'error'
            );
            return get_option( 'wp_adagent_supply_chain', '' );
        }

        // Validate required fields
        if ( ! isset( $decoded['ver'] ) || ! isset( $decoded['complete'] ) || ! isset( $decoded['nodes'] ) ) {
            add_settings_error(
                'wp_adagent_supply_chain',
                'missing_fields',
                __( 'Supply chain must include "ver", "complete", and "nodes" fields.', 'wp-adagent' ),
                'error'
            );
            return get_option( 'wp_adagent_supply_chain', '' );
        }

        // Validate nodes
        if ( ! is_array( $decoded['nodes'] ) ) {
            add_settings_error(
                'wp_adagent_supply_chain',
                'invalid_nodes',
                __( '"nodes" must be an array.', 'wp-adagent' ),
                'error'
            );
            return get_option( 'wp_adagent_supply_chain', '' );
        }

        foreach ( $decoded['nodes'] as $index => $node ) {
            if ( ! isset( $node['asi'] ) || ! isset( $node['sid'] ) || ! isset( $node['hp'] ) ) {
                add_settings_error(
                    'wp_adagent_supply_chain',
                    'invalid_node',
                    sprintf(
                        /* translators: %d: Node index number */
                        __( 'Node %d must include "asi", "sid", and "hp" fields.', 'wp-adagent' ),
                        $index + 1
                    ),
                    'error'
                );
                return get_option( 'wp_adagent_supply_chain', '' );
            }
        }

        // Clear config cache
        delete_transient( 'wp_adagent_config' );

        // Return minified JSON
        return wp_json_encode( $decoded );
    }

    /**
     * Get supply chain configuration.
     *
     * @since 1.0.0
     * @return array|null Supply chain object or null if not set.
     */
    public static function get_supply_chain() {
        $value = get_option( 'wp_adagent_supply_chain', '' );

        if ( empty( $value ) ) {
            return null;
        }

        return json_decode( $value, true );
    }
}

// Initialize
new Supply_Chain();
