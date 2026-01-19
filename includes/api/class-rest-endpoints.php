<?php
/**
 * REST Endpoints Class
 *
 * Handles WordPress REST API endpoints.
 *
 * @package WP_AdAgent
 * @since   1.0.0
 */

namespace WP_AdAgent\API;

use WP_AdAgent\Admin\Settings;
use WP_AdAgent\Admin\Prebid_Config;
use WP_AdAgent\Admin\Placements;
use WP_AdAgent\Admin\Supply_Chain;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * REST Endpoints Class
 *
 * @since 1.0.0
 */
class REST_Endpoints {

    /**
     * Register REST API routes.
     *
     * @since 1.0.0
     */
    public function register_routes() {
        // Get config endpoint
        register_rest_route(
            WP_ADAGENT_REST_NAMESPACE,
            '/config',
            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_config' ),
                'permission_callback' => '__return_true', // Public endpoint
            )
        );

        // Get placements endpoint
        register_rest_route(
            WP_ADAGENT_REST_NAMESPACE,
            '/placements',
            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_placements' ),
                'permission_callback' => '__return_true',
            )
        );

        // Test API connection endpoint
        register_rest_route(
            WP_ADAGENT_REST_NAMESPACE,
            '/test-api',
            array(
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'test_api' ),
                'permission_callback' => array( $this, 'check_admin_permission' ),
                'args'                => array(
                    'api_key' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'endpoint' => array(
                        'required'          => false,
                        'type'              => 'string',
                        'sanitize_callback' => 'esc_url_raw',
                    ),
                ),
            )
        );

        // Log impression endpoint (for analytics)
        register_rest_route(
            WP_ADAGENT_REST_NAMESPACE,
            '/impression',
            array(
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'log_impression' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'placement_id' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'winning_bid' => array(
                        'required'          => false,
                        'type'              => 'number',
                        'sanitize_callback' => 'floatval',
                    ),
                ),
            )
        );
    }

    /**
     * Get configuration endpoint.
     *
     * @since 1.0.0
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response
     */
    public function get_config( $request ) {
        // Try to get cached config
        $cached = get_transient( 'wp_adagent_config' );
        if ( false !== $cached ) {
            return rest_ensure_response( $cached );
        }

        $prebid_config = Prebid_Config::get_config();
        $supply_chain  = Supply_Chain::get_supply_chain();

        $config = array(
            'placements'  => Placements::get_placements(),
            'prebid'      => array(
                'version'     => $prebid_config['version'],
                'timeout'     => $prebid_config['timeout'],
                'priceFloors' => $prebid_config['priceFloors'],
                'bidders'     => $prebid_config['bidders'],
            ),
            'pubcontext'  => array(
                'enabled'     => Settings::is_semantic_enabled(),
                'endpoint'    => Settings::get_api_endpoint(),
                'supplyChain' => $supply_chain,
            ),
            'plugin'      => array(
                'version'     => WP_ADAGENT_VERSION,
                'installedAt' => get_option( 'wp_adagent_installed_at', '' ),
            ),
        );

        // Cache for 1 hour
        set_transient( 'wp_adagent_config', $config, WP_ADAGENT_CONFIG_CACHE_TTL );

        return rest_ensure_response( $config );
    }

    /**
     * Get placements endpoint.
     *
     * @since 1.0.0
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response
     */
    public function get_placements( $request ) {
        $include_inactive = $request->get_param( 'include_inactive' );

        $args = array();
        if ( $include_inactive && current_user_can( WP_ADAGENT_ADMIN_CAP ) ) {
            $args['meta_query'] = array();
        }

        $placements = Placements::get_placements( $args );

        return rest_ensure_response( $placements );
    }

    /**
     * Test API connection endpoint.
     *
     * @since 1.0.0
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response
     */
    public function test_api( $request ) {
        $api_key  = $request->get_param( 'api_key' );
        $endpoint = $request->get_param( 'endpoint' ) ?: Settings::get_api_endpoint();

        if ( empty( $api_key ) ) {
            return new \WP_REST_Response(
                array(
                    'success' => false,
                    'message' => __( 'API key is required.', 'wp-adagent' ),
                ),
                400
            );
        }

        // Create client and test connection
        $client = new Pubcontext_Client( $api_key, $endpoint );
        $result = $client->test_connection();

        if ( is_wp_error( $result ) ) {
            return new \WP_REST_Response(
                array(
                    'success' => false,
                    'message' => $result->get_error_message(),
                ),
                400
            );
        }

        return rest_ensure_response( array(
            'success' => true,
            'message' => __( 'Connection successful! API key is valid.', 'wp-adagent' ),
        ) );
    }

    /**
     * Log impression endpoint.
     *
     * @since 1.0.0
     * @param \WP_REST_Request $request Request object.
     * @return \WP_REST_Response
     */
    public function log_impression( $request ) {
        // This is a placeholder for Phase 2 analytics
        // For now, just acknowledge the impression

        $placement_id = $request->get_param( 'placement_id' );
        $winning_bid  = $request->get_param( 'winning_bid' );

        /**
         * Fires when an ad impression is logged.
         *
         * @since 1.0.0
         * @param string $placement_id The placement ID.
         * @param float  $winning_bid  The winning bid amount.
         * @param array  $request_data All request data.
         */
        do_action( 'wp_adagent_impression', $placement_id, $winning_bid, $request->get_params() );

        return rest_ensure_response( array(
            'success' => true,
        ) );
    }

    /**
     * Check admin permission.
     *
     * @since 1.0.0
     * @param \WP_REST_Request $request Request object.
     * @return bool
     */
    public function check_admin_permission( $request ) {
        return current_user_can( WP_ADAGENT_ADMIN_CAP );
    }
}
