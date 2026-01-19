<?php
/**
 * Analytics Page Template
 *
 * @package WP_AdAgent
 * @since   1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap wp-adagent-wrap">
    <h1><?php esc_html_e( 'Analytics', 'wp-adagent' ); ?></h1>

    <div class="wp-adagent-card">
        <div class="wp-adagent-card-header">
            <h2><?php esc_html_e( 'Coming Soon', 'wp-adagent' ); ?></h2>
        </div>
        <div class="wp-adagent-card-content">
            <p><?php esc_html_e( 'Analytics dashboard is coming in a future update. You will be able to:', 'wp-adagent' ); ?></p>
            <ul>
                <li><?php esc_html_e( 'Track impressions and revenue by placement', 'wp-adagent' ); ?></li>
                <li><?php esc_html_e( 'Monitor semantic matching performance', 'wp-adagent' ); ?></li>
                <li><?php esc_html_e( 'View floor optimization impact', 'wp-adagent' ); ?></li>
                <li><?php esc_html_e( 'Compare bidder performance', 'wp-adagent' ); ?></li>
                <li><?php esc_html_e( 'Export reports', 'wp-adagent' ); ?></li>
            </ul>

            <p>
                <strong><?php esc_html_e( 'In the meantime:', 'wp-adagent' ); ?></strong>
                <?php esc_html_e( 'You can use your browser developer tools to monitor Prebid.js auction results and view the pbjs object.', 'wp-adagent' ); ?>
            </p>
        </div>
    </div>
</div>
