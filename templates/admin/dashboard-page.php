<?php
/**
 * Dashboard Page Template
 *
 * @package WP_AdAgent
 * @since   1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get stats
$placements_count = wp_count_posts( WP_ADAGENT_PLACEMENT_POST_TYPE );
$active_placements = isset( $placements_count->publish ) ? $placements_count->publish : 0;

$api_configured = ! empty( get_option( 'wp_adagent_api_key' ) );
$bidders = json_decode( get_option( 'wp_adagent_prebid_bidders', '{}' ), true );
$bidders_count = count( $bidders );
?>

<div class="wrap wp-adagent-wrap">
    <div class="wp-adagent-header">
        <h1><?php esc_html_e( 'Pubcontext Dashboard', 'wp-adagent' ); ?></h1>
    </div>

    <?php if ( ! $api_configured ) : ?>
        <div class="wp-adagent-quick-start">
            <h2><?php esc_html_e( 'Welcome to WP-AdAgent!', 'wp-adagent' ); ?></h2>
            <p><?php esc_html_e( 'Get started by configuring your Pubcontext API key to enable semantic ad matching.', 'wp-adagent' ); ?></p>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-adagent-settings' ) ); ?>" class="button button-large">
                <?php esc_html_e( 'Configure API Settings', 'wp-adagent' ); ?>
            </a>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="wp-adagent-dashboard-grid">
        <div class="wp-adagent-stat-card">
            <div class="stat-value"><?php echo esc_html( $active_placements ); ?></div>
            <div class="stat-label"><?php esc_html_e( 'Active Placements', 'wp-adagent' ); ?></div>
        </div>

        <div class="wp-adagent-stat-card">
            <div class="stat-value"><?php echo esc_html( $bidders_count ); ?></div>
            <div class="stat-label"><?php esc_html_e( 'Configured Bidders', 'wp-adagent' ); ?></div>
        </div>

        <div class="wp-adagent-stat-card">
            <div class="stat-value">
                <?php if ( $api_configured ) : ?>
                    <span style="color: #00a32a;">✓</span>
                <?php else : ?>
                    <span style="color: #d63638;">✗</span>
                <?php endif; ?>
            </div>
            <div class="stat-label"><?php esc_html_e( 'API Status', 'wp-adagent' ); ?></div>
        </div>

        <div class="wp-adagent-stat-card">
            <div class="stat-value"><?php echo esc_html( get_option( 'wp_adagent_semantic_enabled' ) ? '✓' : '✗' ); ?></div>
            <div class="stat-label"><?php esc_html_e( 'Semantic Matching', 'wp-adagent' ); ?></div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="wp-adagent-card">
        <div class="wp-adagent-card-header">
            <h2><?php esc_html_e( 'Quick Actions', 'wp-adagent' ); ?></h2>
        </div>
        <div class="wp-adagent-card-content">
            <p>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-adagent-placements' ) ); ?>" class="button">
                    <?php esc_html_e( 'Manage Placements', 'wp-adagent' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-adagent-prebid' ) ); ?>" class="button">
                    <?php esc_html_e( 'Configure Bidders', 'wp-adagent' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . WP_ADAGENT_PLACEMENT_POST_TYPE ) ); ?>" class="button button-primary">
                    <?php esc_html_e( 'Add New Placement', 'wp-adagent' ); ?>
                </a>
            </p>
        </div>
    </div>

    <!-- Setup Checklist -->
    <div class="wp-adagent-card">
        <div class="wp-adagent-card-header">
            <h2><?php esc_html_e( 'Setup Checklist', 'wp-adagent' ); ?></h2>
        </div>
        <div class="wp-adagent-card-content">
            <ol class="wp-adagent-steps">
                <li class="<?php echo $api_configured ? 'completed' : ''; ?>">
                    <strong><?php esc_html_e( 'Configure API Settings', 'wp-adagent' ); ?></strong>
                    <p><?php esc_html_e( 'Enter your Pubcontext API key to enable semantic matching.', 'wp-adagent' ); ?></p>
                    <?php if ( ! $api_configured ) : ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-adagent-settings' ) ); ?>"><?php esc_html_e( 'Configure now →', 'wp-adagent' ); ?></a>
                    <?php endif; ?>
                </li>
                <li class="<?php echo $bidders_count > 0 ? 'completed' : ''; ?>">
                    <strong><?php esc_html_e( 'Set Up Bidders', 'wp-adagent' ); ?></strong>
                    <p><?php esc_html_e( 'Configure your Prebid.js header bidding partners.', 'wp-adagent' ); ?></p>
                    <?php if ( $bidders_count === 0 ) : ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-adagent-prebid' ) ); ?>"><?php esc_html_e( 'Configure now →', 'wp-adagent' ); ?></a>
                    <?php endif; ?>
                </li>
                <li class="<?php echo $active_placements > 0 ? 'completed' : ''; ?>">
                    <strong><?php esc_html_e( 'Create Placements', 'wp-adagent' ); ?></strong>
                    <p><?php esc_html_e( 'Define your ad slots with sizes and floor prices.', 'wp-adagent' ); ?></p>
                    <?php if ( $active_placements === 0 ) : ?>
                        <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . WP_ADAGENT_PLACEMENT_POST_TYPE ) ); ?>"><?php esc_html_e( 'Create now →', 'wp-adagent' ); ?></a>
                    <?php endif; ?>
                </li>
                <li>
                    <strong><?php esc_html_e( 'Add Placements to Content', 'wp-adagent' ); ?></strong>
                    <p><?php esc_html_e( 'Use the Gutenberg block or shortcode to insert ads in your pages.', 'wp-adagent' ); ?></p>
                </li>
            </ol>
        </div>
    </div>

    <!-- Documentation -->
    <div class="wp-adagent-card">
        <div class="wp-adagent-card-header">
            <h2><?php esc_html_e( 'Resources', 'wp-adagent' ); ?></h2>
        </div>
        <div class="wp-adagent-card-content">
            <ul>
                <li><a href="https://docs.pubcontext.com" target="_blank"><?php esc_html_e( 'Documentation', 'wp-adagent' ); ?> ↗</a></li>
                <li><a href="https://github.com/pubcntext/WP-AdAgent" target="_blank"><?php esc_html_e( 'GitHub Repository', 'wp-adagent' ); ?> ↗</a></li>
                <li><a href="https://pubcontext.com/support" target="_blank"><?php esc_html_e( 'Get Support', 'wp-adagent' ); ?> ↗</a></li>
            </ul>
        </div>
    </div>
</div>
