<?php
/**
 * Pubcontext Client Class
 *
 * HTTP client for Pubcontext API integration.
 *
 * @package WP_AdAgent
 * @since   1.0.0
 */

namespace WP_AdAgent\API;

use WP_AdAgent\Utils\Logger;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Pubcontext Client Class
 *
 * @since 1.0.0
 */
class Pubcontext_Client {

    /**
     * API key.
     *
     * @since 1.0.0
     * @var string
     */
    private $api_key;

    /**
     * API endpoint.
     *
     * @since 1.0.0
     * @var string
     */
    private $endpoint;

    /**
     * Request timeout in seconds.
     *
     * @since 1.0.0
     * @var int
     */
    private $timeout;

    /**
     * Constructor.
     *
     * @since 1.0.0
     * @param string $api_key  API key.
     * @param string $endpoint API endpoint URL.
     * @param int    $timeout  Request timeout in milliseconds.
     */
    public function __construct( $api_key, $endpoint = null, $timeout = null ) {
        $this->api_key  = $api_key;
        $this->endpoint = $endpoint ?: WP_ADAGENT_API_ENDPOINT;
        $this->timeout  = ( $timeout ?: WP_ADAGENT_API_TIMEOUT ) / 1000; // Convert to seconds
    }

    /**
     * Call the match API.
     *
     * @since 1.0.0
     * @param string $placement_id Placement ID.
     * @param array  $context      Page context data.
     * @return array|\WP_Error Match response or error.
     */
    public function call_match_api( $placement_id, $context ) {
        $payload = array(
            'placement_id' => $placement_id,
            'page_url'     => isset( $context['url'] ) ? $context['url'] : '',
            'referrer'     => isset( $context['referrer'] ) ? $context['referrer'] : '',
            'context'      => array(
                'page_title'    => isset( $context['title'] ) ? $context['title'] : '',
                'page_content'  => isset( $context['content'] ) ? $context['content'] : '',
                'keywords'      => isset( $context['keywords'] ) ? $context['keywords'] : array(),
                'sentiment'     => isset( $context['sentiment'] ) ? $context['sentiment'] : 'neutral',
                'user_device'   => isset( $context['device'] ) ? $context['device'] : 'desktop',
                'time_on_page'  => isset( $context['timeOnPage'] ) ? $context['timeOnPage'] : 0,
                'scroll_depth'  => isset( $context['scrollDepth'] ) ? $context['scrollDepth'] : 0,
            ),
        );

        /**
         * Filter the match API payload before sending.
         *
         * @since 1.0.0
         * @param array  $payload      The API payload.
         * @param string $placement_id The placement ID.
         * @param array  $context      The original context data.
         */
        $payload = apply_filters( 'wp_adagent_match_payload', $payload, $placement_id, $context );

        $response = wp_remote_post(
            $this->endpoint,
            array(
                'timeout'     => $this->timeout,
                'headers'     => array(
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'User-Agent'    => 'WP-AdAgent/' . WP_ADAGENT_VERSION,
                ),
                'body'        => wp_json_encode( $payload ),
                'data_format' => 'body',
            )
        );

        // Handle WP error
        if ( is_wp_error( $response ) ) {
            Logger::error( 'Pubcontext API error: ' . $response->get_error_message() );
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body        = wp_remote_retrieve_body( $response );
        $data        = json_decode( $body, true );

        // Log API call
        Logger::api_call( $this->endpoint, 'POST', $status_code );

        // Handle non-200 responses
        if ( 200 !== $status_code ) {
            $error_message = isset( $data['error'] ) ? $data['error'] : 'Unknown error';
            Logger::error( sprintf( 'Pubcontext API returned %d: %s', $status_code, $error_message ) );

            return new \WP_Error(
                'api_error',
                $error_message,
                array( 'status' => $status_code )
            );
        }

        // Handle malformed response
        if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $data ) ) {
            Logger::error( 'Pubcontext API returned malformed response' );
            return new \WP_Error( 'malformed_response', 'Invalid JSON response from API' );
        }

        /**
         * Filter the match API response.
         *
         * @since 1.0.0
         * @param array  $data         The API response data.
         * @param string $placement_id The placement ID.
         */
        return apply_filters( 'wp_adagent_match_response', $data, $placement_id );
    }

    /**
     * Test API connection.
     *
     * @since 1.0.0
     * @return true|\WP_Error True on success or error.
     */
    public function test_connection() {
        // Use a simple ping endpoint or minimal match request
        $response = wp_remote_post(
            rtrim( $this->endpoint, '/' ) . '/ping',
            array(
                'timeout'     => 10,
                'headers'     => array(
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'User-Agent'    => 'WP-AdAgent/' . WP_ADAGENT_VERSION,
                ),
                'body'        => wp_json_encode( array( 'test' => true ) ),
                'data_format' => 'body',
            )
        );

        if ( is_wp_error( $response ) ) {
            return new \WP_Error(
                'connection_failed',
                sprintf(
                    /* translators: %s: Error message */
                    __( 'Connection failed: %s', 'wp-adagent' ),
                    $response->get_error_message()
                )
            );
        }

        $status_code = wp_remote_retrieve_response_code( $response );

        // Accept 200, 201, or even 404 (endpoint might not exist but connection works)
        if ( $status_code >= 200 && $status_code < 500 ) {
            // Check for auth errors
            if ( 401 === $status_code || 403 === $status_code ) {
                return new \WP_Error(
                    'auth_failed',
                    __( 'Authentication failed. Please check your API key.', 'wp-adagent' )
                );
            }

            return true;
        }

        return new \WP_Error(
            'server_error',
            sprintf(
                /* translators: %d: HTTP status code */
                __( 'Server returned error code: %d', 'wp-adagent' ),
                $status_code
            )
        );
    }

    /**
     * Calculate suggested floor based on match response.
     *
     * @since 1.0.0
     * @param array $match_response The match API response.
     * @param float $base_floor     The base floor price.
     * @param float $floor_cap      The maximum floor price.
     * @return float Suggested floor price.
     */
    public static function calculate_floor( $match_response, $base_floor, $floor_cap ) {
        if ( empty( $match_response['matched_creatives'] ) ) {
            return $base_floor;
        }

        $highest_floor = $base_floor;

        foreach ( $match_response['matched_creatives'] as $creative ) {
            // Check alignment score threshold
            $score = isset( $creative['context_alignment_score'] ) ? $creative['context_alignment_score'] : 0;

            if ( $score < WP_ADAGENT_MIN_ALIGNMENT_SCORE ) {
                continue;
            }

            // Get suggested floor
            $suggested = isset( $creative['suggested_bid_floor'] ) ? $creative['suggested_bid_floor'] : 0;

            if ( $suggested > $highest_floor ) {
                $highest_floor = $suggested;
            }
        }

        // Apply floor cap
        if ( $floor_cap > 0 && $highest_floor > $floor_cap ) {
            return $floor_cap;
        }

        return $highest_floor;
    }
}
