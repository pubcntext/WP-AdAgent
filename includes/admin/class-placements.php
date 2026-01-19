<?php
/**
 * Placements Class
 *
 * Handles ad placement CRUD operations.
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
 * Placements Class
 *
 * @since 1.0.0
 */
class Placements {

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post_' . WP_ADAGENT_PLACEMENT_POST_TYPE, array( $this, 'save_meta' ), 10, 2 );
        add_filter( 'manage_' . WP_ADAGENT_PLACEMENT_POST_TYPE . '_posts_columns', array( $this, 'add_columns' ) );
        add_action( 'manage_' . WP_ADAGENT_PLACEMENT_POST_TYPE . '_posts_custom_column', array( $this, 'render_columns' ), 10, 2 );
    }

    /**
     * Add meta boxes.
     *
     * @since 1.0.0
     */
    public function add_meta_boxes() {
        add_meta_box(
            'wp_adagent_placement_details',
            __( 'Placement Configuration', 'wp-adagent' ),
            array( $this, 'render_meta_box' ),
            WP_ADAGENT_PLACEMENT_POST_TYPE,
            'normal',
            'high'
        );
    }

    /**
     * Render placement meta box.
     *
     * @since 1.0.0
     * @param \WP_Post $post The post object.
     */
    public function render_meta_box( $post ) {
        wp_nonce_field( 'wp_adagent_placement_meta', 'wp_adagent_placement_nonce' );

        $placement_id    = get_post_meta( $post->ID, '_wp_adagent_placement_id', true );
        $ad_unit_code    = get_post_meta( $post->ID, '_wp_adagent_ad_unit_code', true );
        $sizes           = get_post_meta( $post->ID, '_wp_adagent_sizes', true );
        $base_floor      = get_post_meta( $post->ID, '_wp_adagent_base_floor', true );
        $floor_cap       = get_post_meta( $post->ID, '_wp_adagent_floor_cap', true );
        $css_selector    = get_post_meta( $post->ID, '_wp_adagent_css_selector', true );
        $context_tags    = get_post_meta( $post->ID, '_wp_adagent_context_tags', true );
        $enable_semantic = get_post_meta( $post->ID, '_wp_adagent_enable_semantic', true );
        $active          = get_post_meta( $post->ID, '_wp_adagent_active', true );

        // Set defaults
        if ( '' === $base_floor ) {
            $base_floor = WP_ADAGENT_DEFAULT_FLOOR;
        }
        if ( '' === $floor_cap ) {
            $floor_cap = WP_ADAGENT_DEFAULT_FLOOR_CAP;
        }
        if ( '' === $enable_semantic ) {
            $enable_semantic = '1';
        }
        if ( '' === $active ) {
            $active = '1';
        }

        include WP_ADAGENT_PLUGIN_DIR . 'templates/admin/metabox-placement.php';
    }

    /**
     * Save placement meta.
     *
     * @since 1.0.0
     * @param int      $post_id The post ID.
     * @param \WP_Post $post    The post object.
     */
    public function save_meta( $post_id, $post ) {
        // Verify nonce
        if ( ! isset( $_POST['wp_adagent_placement_nonce'] ) ||
             ! wp_verify_nonce( $_POST['wp_adagent_placement_nonce'], 'wp_adagent_placement_meta' ) ) {
            return;
        }

        // Check autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Save meta fields
        $fields = array(
            '_wp_adagent_placement_id'    => 'sanitize_title',
            '_wp_adagent_ad_unit_code'    => 'sanitize_text_field',
            '_wp_adagent_sizes'           => array( $this, 'sanitize_sizes' ),
            '_wp_adagent_base_floor'      => 'floatval',
            '_wp_adagent_floor_cap'       => 'floatval',
            '_wp_adagent_css_selector'    => 'sanitize_text_field',
            '_wp_adagent_context_tags'    => 'sanitize_text_field',
            '_wp_adagent_enable_semantic' => 'absint',
            '_wp_adagent_active'          => 'absint',
        );

        foreach ( $fields as $key => $sanitize_callback ) {
            if ( isset( $_POST[ $key ] ) ) {
                $value = call_user_func( $sanitize_callback, $_POST[ $key ] );
                update_post_meta( $post_id, $key, $value );
            } else {
                // Handle checkboxes
                if ( in_array( $key, array( '_wp_adagent_enable_semantic', '_wp_adagent_active' ), true ) ) {
                    update_post_meta( $post_id, $key, 0 );
                }
            }
        }

        // Clear config cache
        delete_transient( 'wp_adagent_config' );
    }

    /**
     * Sanitize sizes field.
     *
     * @since 1.0.0
     * @param string $value The sizes value.
     * @return string Sanitized JSON array.
     */
    public function sanitize_sizes( $value ) {
        $value = sanitize_text_field( $value );

        // Try to parse as JSON
        $decoded = json_decode( $value, true );
        if ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded ) ) {
            return wp_json_encode( $decoded );
        }

        // Parse comma-separated values
        $sizes = array_map( 'trim', explode( ',', $value ) );
        $sizes = array_filter( $sizes );

        return wp_json_encode( array_values( $sizes ) );
    }

    /**
     * Add custom columns.
     *
     * @since 1.0.0
     * @param array $columns Existing columns.
     * @return array Modified columns.
     */
    public function add_columns( $columns ) {
        $new_columns = array();

        foreach ( $columns as $key => $value ) {
            $new_columns[ $key ] = $value;

            if ( 'title' === $key ) {
                $new_columns['placement_id'] = __( 'Placement ID', 'wp-adagent' );
                $new_columns['sizes']        = __( 'Sizes', 'wp-adagent' );
                $new_columns['floor']        = __( 'Base Floor', 'wp-adagent' );
                $new_columns['status']       = __( 'Status', 'wp-adagent' );
            }
        }

        return $new_columns;
    }

    /**
     * Render custom columns.
     *
     * @since 1.0.0
     * @param string $column  Column name.
     * @param int    $post_id Post ID.
     */
    public function render_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'placement_id':
                $value = get_post_meta( $post_id, '_wp_adagent_placement_id', true );
                echo '<code>' . esc_html( $value ?: '—' ) . '</code>';
                break;

            case 'sizes':
                $value = get_post_meta( $post_id, '_wp_adagent_sizes', true );
                $sizes = json_decode( $value, true );
                if ( is_array( $sizes ) ) {
                    echo esc_html( implode( ', ', $sizes ) );
                } else {
                    echo '—';
                }
                break;

            case 'floor':
                $value = get_post_meta( $post_id, '_wp_adagent_base_floor', true );
                if ( $value ) {
                    echo '$' . esc_html( number_format( (float) $value, 2 ) );
                } else {
                    echo '—';
                }
                break;

            case 'status':
                $active = get_post_meta( $post_id, '_wp_adagent_active', true );
                if ( $active ) {
                    echo '<span class="wp-adagent-status active">' . esc_html__( 'Active', 'wp-adagent' ) . '</span>';
                } else {
                    echo '<span class="wp-adagent-status inactive">' . esc_html__( 'Inactive', 'wp-adagent' ) . '</span>';
                }
                break;
        }
    }

    /**
     * Get all placements.
     *
     * @since 1.0.0
     * @param array $args Query arguments.
     * @return array Array of placement data.
     */
    public static function get_placements( $args = array() ) {
        $defaults = array(
            'post_type'      => WP_ADAGENT_PLACEMENT_POST_TYPE,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => array(
                array(
                    'key'     => '_wp_adagent_active',
                    'value'   => '1',
                    'compare' => '=',
                ),
            ),
        );

        $args  = wp_parse_args( $args, $defaults );
        $posts = get_posts( $args );

        $placements = array();
        foreach ( $posts as $post ) {
            $sizes = json_decode( get_post_meta( $post->ID, '_wp_adagent_sizes', true ), true ) ?: array();

            $placements[] = array(
                'id'             => $post->ID,
                'title'          => $post->post_title,
                'placementId'    => get_post_meta( $post->ID, '_wp_adagent_placement_id', true ),
                'adUnitCode'     => get_post_meta( $post->ID, '_wp_adagent_ad_unit_code', true ),
                'sizes'          => $sizes,
                'baseFloor'      => (float) get_post_meta( $post->ID, '_wp_adagent_base_floor', true ),
                'floorCap'       => (float) get_post_meta( $post->ID, '_wp_adagent_floor_cap', true ),
                'cssSelector'    => get_post_meta( $post->ID, '_wp_adagent_css_selector', true ),
                'contextTags'    => get_post_meta( $post->ID, '_wp_adagent_context_tags', true ),
                'enableSemantic' => (bool) get_post_meta( $post->ID, '_wp_adagent_enable_semantic', true ),
                'active'         => (bool) get_post_meta( $post->ID, '_wp_adagent_active', true ),
            );
        }

        return $placements;
    }
}

// Initialize
new Placements();
