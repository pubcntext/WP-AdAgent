<?php
/**
 * Scripts Class
 *
 * Handles frontend script and style enqueuing.
 *
 * @package WP_AdAgent
 * @since   1.0.0
 */

namespace WP_AdAgent\Frontend;

use WP_AdAgent\Admin\Settings;
use WP_AdAgent\Admin\Prebid_Config;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Scripts Class
 *
 * @since 1.0.0
 */
class Scripts {

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'wp_head', array( $this, 'output_config' ), 1 );
    }

    /**
     * Enqueue frontend scripts.
     *
     * @since 1.0.0
     */
    public function enqueue_scripts() {
        // Don't load in admin
        if ( is_admin() ) {
            return;
        }

        // Check if plugin is configured
        if ( ! $this->is_configured() ) {
            return;
        }

        $prebid_config = Prebid_Config::get_config();

        // Enqueue Prebid.js
        wp_enqueue_script(
            'prebid-js',
            $this->get_prebid_url( $prebid_config['version'] ),
            array(),
            $prebid_config['version'],
            false // Load in head for performance
        );

        // Enqueue context extractor
        wp_enqueue_script(
            'wp-adagent-context',
            WP_ADAGENT_PLUGIN_URL . 'assets/js/context-extractor.js',
            array(),
            WP_ADAGENT_VERSION,
            true
        );

        // Enqueue main init script
        wp_enqueue_script(
            'wp-adagent-init',
            WP_ADAGENT_PLUGIN_URL . 'assets/js/pubcontext-init.js',
            array( 'prebid-js', 'wp-adagent-context' ),
            WP_ADAGENT_VERSION,
            true
        );

        // Localize script with config
        wp_localize_script(
            'wp-adagent-init',
            'wpAdAgentConfig',
            $this->get_frontend_config()
        );

        // Enqueue placement styles
        wp_enqueue_style(
            'wp-adagent-placement',
            WP_ADAGENT_PLUGIN_URL . 'assets/css/placement.css',
            array(),
            WP_ADAGENT_VERSION
        );
    }

    /**
     * Output inline config in head.
     *
     * @since 1.0.0
     */
    public function output_config() {
        if ( is_admin() || ! $this->is_configured() ) {
            return;
        }

        // Output early config for Prebid
        ?>
        <script>
            window.pbjs = window.pbjs || {};
            window.pbjs.que = window.pbjs.que || [];
        </script>
        <?php
    }

    /**
     * Check if plugin is configured.
     *
     * @since 1.0.0
     * @return bool
     */
    private function is_configured() {
        $api_key = Settings::get_api_key();
        return ! empty( $api_key );
    }

    /**
     * Get Prebid.js CDN URL.
     *
     * @since 1.0.0
     * @param string $version Prebid version.
     * @return string CDN URL.
     */
    private function get_prebid_url( $version ) {
        // Use jsdelivr CDN for Prebid.js
        return sprintf(
            'https://cdn.jsdelivr.net/npm/prebid.js@%s/dist/not-for-prod/prebid.js',
            $version
        );
    }

    /**
     * Get frontend configuration.
     *
     * @since 1.0.0
     * @return array Configuration for JavaScript.
     */
    private function get_frontend_config() {
        $prebid_config = Prebid_Config::get_config();

        return array(
            'restUrl'         => rest_url( WP_ADAGENT_REST_NAMESPACE ),
            'nonce'           => wp_create_nonce( 'wp_rest' ),
            'debug'           => WP_ADAGENT_DEBUG,
            'semanticEnabled' => Settings::is_semantic_enabled(),
            'prebid'          => array(
                'version'     => $prebid_config['version'],
                'timeout'     => $prebid_config['timeout'],
                'priceFloors' => $prebid_config['priceFloors'],
            ),
            'apiTimeout'      => WP_ADAGENT_API_TIMEOUT,
            'minAlignScore'   => WP_ADAGENT_MIN_ALIGNMENT_SCORE,
            'pageData'        => array(
                'postId'     => get_the_ID(),
                'postType'   => get_post_type(),
                'url'        => get_permalink(),
                'title'      => get_the_title(),
                'categories' => $this->get_post_categories(),
                'tags'       => $this->get_post_tags(),
            ),
        );
    }

    /**
     * Get post categories.
     *
     * @since 1.0.0
     * @return array Category names.
     */
    private function get_post_categories() {
        $categories = get_the_category();
        if ( empty( $categories ) ) {
            return array();
        }

        return array_map(
            function ( $cat ) {
                return $cat->name;
            },
            $categories
        );
    }

    /**
     * Get post tags.
     *
     * @since 1.0.0
     * @return array Tag names.
     */
    private function get_post_tags() {
        $tags = get_the_tags();
        if ( empty( $tags ) ) {
            return array();
        }

        return array_map(
            function ( $tag ) {
                return $tag->name;
            },
            $tags
        );
    }
}
