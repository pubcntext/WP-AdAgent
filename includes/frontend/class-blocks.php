<?php
/**
 * Blocks Class
 *
 * Handles Gutenberg block registration.
 *
 * @package WP_AdAgent
 * @since   1.0.0
 */

namespace WP_AdAgent\Frontend;

use WP_AdAgent\Admin\Placements;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Blocks Class
 *
 * @since 1.0.0
 */
class Blocks {

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_blocks' ) );
        add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
        add_shortcode( 'pubcontext_placement', array( $this, 'render_shortcode' ) );
    }

    /**
     * Register Gutenberg blocks.
     *
     * @since 1.0.0
     */
    public function register_blocks() {
        // Check if Gutenberg is available
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        register_block_type(
            'pubcontext/ad-placement',
            array(
                'editor_script'   => 'wp-adagent-block-editor',
                'editor_style'    => 'wp-adagent-block-editor-style',
                'render_callback' => array( $this, 'render_block' ),
                'attributes'      => array(
                    'placementId' => array(
                        'type'    => 'string',
                        'default' => '',
                    ),
                    'overrideFloor' => array(
                        'type'    => 'number',
                        'default' => 0,
                    ),
                    'showAnalytics' => array(
                        'type'    => 'boolean',
                        'default' => false,
                    ),
                    'align' => array(
                        'type'    => 'string',
                        'default' => 'center',
                    ),
                ),
            )
        );
    }

    /**
     * Enqueue block editor assets.
     *
     * @since 1.0.0
     */
    public function enqueue_editor_assets() {
        // Editor script
        wp_enqueue_script(
            'wp-adagent-block-editor',
            WP_ADAGENT_PLUGIN_URL . 'assets/js/blocks/pubcontext-placement.js',
            array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-api-fetch' ),
            WP_ADAGENT_VERSION,
            true
        );

        // Editor styles
        wp_enqueue_style(
            'wp-adagent-block-editor-style',
            WP_ADAGENT_PLUGIN_URL . 'assets/css/blocks.css',
            array( 'wp-edit-blocks' ),
            WP_ADAGENT_VERSION
        );

        // Localize with placements data
        wp_localize_script(
            'wp-adagent-block-editor',
            'wpAdAgentBlock',
            array(
                'placements' => Placements::get_placements( array(
                    'meta_query' => array(), // Get all placements for editor
                ) ),
                'restUrl'    => rest_url( WP_ADAGENT_REST_NAMESPACE ),
                'nonce'      => wp_create_nonce( 'wp_rest' ),
            )
        );
    }

    /**
     * Render block on frontend.
     *
     * @since 1.0.0
     * @param array $attributes Block attributes.
     * @return string Block HTML.
     */
    public function render_block( $attributes ) {
        $placement_id  = isset( $attributes['placementId'] ) ? sanitize_text_field( $attributes['placementId'] ) : '';
        $override_floor = isset( $attributes['overrideFloor'] ) ? floatval( $attributes['overrideFloor'] ) : 0;
        $show_analytics = isset( $attributes['showAnalytics'] ) ? (bool) $attributes['showAnalytics'] : false;
        $align          = isset( $attributes['align'] ) ? sanitize_text_field( $attributes['align'] ) : 'center';

        if ( empty( $placement_id ) ) {
            if ( current_user_can( 'edit_posts' ) ) {
                return '<div class="wp-adagent-error">' . esc_html__( 'Please select a placement for this ad block.', 'wp-adagent' ) . '</div>';
            }
            return '';
        }

        $classes = array(
            'wp-block-pubcontext-ad-placement',
            'wp-adagent-placement',
        );

        if ( $align ) {
            $classes[] = 'align' . $align;
        }

        ob_start();
        ?>
        <div
            class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
            data-placement-id="<?php echo esc_attr( $placement_id ); ?>"
            <?php if ( $override_floor > 0 ) : ?>
                data-override-floor="<?php echo esc_attr( $override_floor ); ?>"
            <?php endif; ?>
            <?php if ( $show_analytics ) : ?>
                data-show-analytics="true"
            <?php endif; ?>
        >
            <!-- Ad will be rendered here by JavaScript -->
        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Render shortcode.
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes.
     * @return string Shortcode HTML.
     */
    public function render_shortcode( $atts ) {
        $atts = shortcode_atts(
            array(
                'id'        => '',
                'floor'     => 0,
                'analytics' => 0,
                'align'     => 'center',
            ),
            $atts,
            'pubcontext_placement'
        );

        return $this->render_block( array(
            'placementId'   => $atts['id'],
            'overrideFloor' => floatval( $atts['floor'] ),
            'showAnalytics' => (bool) $atts['analytics'],
            'align'         => $atts['align'],
        ) );
    }
}

/**
 * Template tag for theme developers.
 *
 * @since 1.0.0
 * @param string $placement_id The placement ID.
 * @param array  $args         Optional arguments.
 */
function pubcontext_placement( $placement_id, $args = array() ) {
    $defaults = array(
        'floor'     => 0,
        'analytics' => false,
        'align'     => 'center',
        'echo'      => true,
    );

    $args = wp_parse_args( $args, $defaults );

    $blocks = new Blocks();
    $output = $blocks->render_block( array(
        'placementId'   => $placement_id,
        'overrideFloor' => $args['floor'],
        'showAnalytics' => $args['analytics'],
        'align'         => $args['align'],
    ) );

    if ( $args['echo'] ) {
        echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    return $output;
}
