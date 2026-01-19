<?php
/**
 * Supply Chain Page Template
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
    <h1><?php esc_html_e( 'Supply Chain Configuration', 'wp-adagent' ); ?></h1>

    <form method="post" action="options.php" class="wp-adagent-form">
        <?php
        settings_fields( \WP_AdAgent\Admin\Supply_Chain::OPTION_GROUP );
        do_settings_sections( \WP_AdAgent\Admin\Supply_Chain::OPTION_GROUP );
        submit_button();
        ?>
    </form>
</div>
