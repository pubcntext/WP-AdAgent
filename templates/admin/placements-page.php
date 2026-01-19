<?php
/**
 * Placements Page Template
 *
 * @package WP_AdAgent
 * @since   1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Redirect to the custom post type list
wp_redirect( admin_url( 'edit.php?post_type=' . WP_ADAGENT_PLACEMENT_POST_TYPE ) );
exit;
