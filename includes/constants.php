<?php
/**
 * Plugin Constants
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
 * API Constants
 */
if ( ! defined( 'WP_ADAGENT_API_ENDPOINT' ) ) {
    define( 'WP_ADAGENT_API_ENDPOINT', 'https://api.pubcontext.com/match' );
}

if ( ! defined( 'WP_ADAGENT_API_TIMEOUT' ) ) {
    define( 'WP_ADAGENT_API_TIMEOUT', 2000 ); // 2 seconds
}

/**
 * Prebid Constants
 */
if ( ! defined( 'WP_ADAGENT_PREBID_CDN' ) ) {
    define( 'WP_ADAGENT_PREBID_CDN', 'https://cdn.jsdelivr.net/npm/prebid.js@latest/dist/not-for-prod/prebid.js' );
}

if ( ! defined( 'WP_ADAGENT_DEFAULT_TIMEOUT' ) ) {
    define( 'WP_ADAGENT_DEFAULT_TIMEOUT', 3000 ); // 3 seconds
}

if ( ! defined( 'WP_ADAGENT_DEFAULT_FLOOR' ) ) {
    define( 'WP_ADAGENT_DEFAULT_FLOOR', 0.50 ); // $0.50 CPM
}

if ( ! defined( 'WP_ADAGENT_DEFAULT_FLOOR_CAP' ) ) {
    define( 'WP_ADAGENT_DEFAULT_FLOOR_CAP', 10.00 ); // $10.00 CPM max
}

/**
 * Capability Constants
 */
if ( ! defined( 'WP_ADAGENT_ADMIN_CAP' ) ) {
    define( 'WP_ADAGENT_ADMIN_CAP', 'manage_options' );
}

if ( ! defined( 'WP_ADAGENT_EDIT_CAP' ) ) {
    define( 'WP_ADAGENT_EDIT_CAP', 'edit_posts' );
}

/**
 * Post Type Constants
 */
if ( ! defined( 'WP_ADAGENT_PLACEMENT_POST_TYPE' ) ) {
    define( 'WP_ADAGENT_PLACEMENT_POST_TYPE', 'wp_adagent_placement' );
}

/**
 * REST API Constants
 */
if ( ! defined( 'WP_ADAGENT_REST_NAMESPACE' ) ) {
    define( 'WP_ADAGENT_REST_NAMESPACE', 'pubcontext/v1' );
}

/**
 * Cache Constants
 */
if ( ! defined( 'WP_ADAGENT_CONFIG_CACHE_TTL' ) ) {
    define( 'WP_ADAGENT_CONFIG_CACHE_TTL', HOUR_IN_SECONDS ); // 1 hour
}

/**
 * Debug Constants
 */
if ( ! defined( 'WP_ADAGENT_DEBUG' ) ) {
    define( 'WP_ADAGENT_DEBUG', defined( 'WP_DEBUG' ) && WP_DEBUG );
}

/**
 * Semantic Matching Constants
 */
if ( ! defined( 'WP_ADAGENT_MIN_ALIGNMENT_SCORE' ) ) {
    define( 'WP_ADAGENT_MIN_ALIGNMENT_SCORE', 0.80 ); // Minimum score to bump floor
}

/**
 * Available Bidders Configuration
 */
if ( ! defined( 'WP_ADAGENT_AVAILABLE_BIDDERS' ) ) {
    define( 'WP_ADAGENT_AVAILABLE_BIDDERS', json_encode( array(
        'appnexus' => array(
            'label'  => 'AppNexus (Xandr)',
            'params' => array(
                'placementId' => array(
                    'type'     => 'string',
                    'label'    => 'Placement ID',
                    'required' => true,
                ),
            ),
        ),
        'rubicon' => array(
            'label'  => 'Rubicon Project',
            'params' => array(
                'accountId' => array(
                    'type'     => 'number',
                    'label'    => 'Account ID',
                    'required' => true,
                ),
                'siteId' => array(
                    'type'     => 'number',
                    'label'    => 'Site ID',
                    'required' => true,
                ),
                'zoneId' => array(
                    'type'     => 'number',
                    'label'    => 'Zone ID',
                    'required' => true,
                ),
            ),
        ),
        'criteo' => array(
            'label'  => 'Criteo',
            'params' => array(
                'networkId' => array(
                    'type'     => 'number',
                    'label'    => 'Network ID',
                    'required' => true,
                ),
            ),
        ),
        'pubmatic' => array(
            'label'  => 'PubMatic',
            'params' => array(
                'publisherId' => array(
                    'type'     => 'string',
                    'label'    => 'Publisher ID',
                    'required' => true,
                ),
                'adSlot' => array(
                    'type'     => 'string',
                    'label'    => 'Ad Slot',
                    'required' => true,
                ),
            ),
        ),
        'ix' => array(
            'label'  => 'Index Exchange',
            'params' => array(
                'siteId' => array(
                    'type'     => 'string',
                    'label'    => 'Site ID',
                    'required' => true,
                ),
            ),
        ),
        'openx' => array(
            'label'  => 'OpenX',
            'params' => array(
                'unit' => array(
                    'type'     => 'string',
                    'label'    => 'Ad Unit ID',
                    'required' => true,
                ),
                'delDomain' => array(
                    'type'     => 'string',
                    'label'    => 'Delivery Domain',
                    'required' => true,
                ),
            ),
        ),
        'sovrn' => array(
            'label'  => 'Sovrn',
            'params' => array(
                'tagid' => array(
                    'type'     => 'string',
                    'label'    => 'Tag ID',
                    'required' => true,
                ),
            ),
        ),
        'triplelift' => array(
            'label'  => 'TripleLift',
            'params' => array(
                'inventoryCode' => array(
                    'type'     => 'string',
                    'label'    => 'Inventory Code',
                    'required' => true,
                ),
            ),
        ),
    ) ) );
}
