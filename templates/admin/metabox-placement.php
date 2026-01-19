<?php
/**
 * Placement Metabox Template
 *
 * @package WP_AdAgent
 * @since   1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Variables are passed from the render_meta_box method
?>

<table class="form-table">
    <tr>
        <th scope="row">
            <label for="_wp_adagent_placement_id"><?php esc_html_e( 'Placement ID', 'wp-adagent' ); ?></label>
        </th>
        <td>
            <input
                type="text"
                id="_wp_adagent_placement_id"
                name="_wp_adagent_placement_id"
                value="<?php echo esc_attr( $placement_id ); ?>"
                class="regular-text"
                placeholder="sidebar-300x250"
                required
            />
            <p class="description">
                <?php esc_html_e( 'Unique identifier for this placement (slug format, e.g., "sidebar-300x250").', 'wp-adagent' ); ?>
            </p>
        </td>
    </tr>

    <tr>
        <th scope="row">
            <label for="_wp_adagent_ad_unit_code"><?php esc_html_e( 'Ad Unit Code', 'wp-adagent' ); ?></label>
        </th>
        <td>
            <input
                type="text"
                id="_wp_adagent_ad_unit_code"
                name="_wp_adagent_ad_unit_code"
                value="<?php echo esc_attr( $ad_unit_code ); ?>"
                class="regular-text"
                placeholder="/1234567/homepage/sidebar"
            />
            <p class="description">
                <?php esc_html_e( 'Prebid ad unit code (e.g., "/1234567/homepage/sidebar"). Leave empty to use placement ID.', 'wp-adagent' ); ?>
            </p>
        </td>
    </tr>

    <tr>
        <th scope="row">
            <label for="_wp_adagent_sizes"><?php esc_html_e( 'Ad Sizes', 'wp-adagent' ); ?></label>
        </th>
        <td>
            <input
                type="text"
                id="_wp_adagent_sizes"
                name="_wp_adagent_sizes"
                value="<?php echo esc_attr( $sizes ); ?>"
                class="regular-text"
                placeholder='["300x250", "300x600"]'
                required
            />
            <p class="description">
                <?php esc_html_e( 'Supported ad sizes. Enter as JSON array: ["300x250", "728x90"] or comma-separated: 300x250, 728x90', 'wp-adagent' ); ?>
            </p>
        </td>
    </tr>

    <tr>
        <th scope="row">
            <label for="_wp_adagent_base_floor"><?php esc_html_e( 'Base Floor Price ($)', 'wp-adagent' ); ?></label>
        </th>
        <td>
            <input
                type="number"
                id="_wp_adagent_base_floor"
                name="_wp_adagent_base_floor"
                value="<?php echo esc_attr( $base_floor ); ?>"
                class="small-text"
                step="0.01"
                min="0"
                placeholder="0.50"
            />
            <p class="description">
                <?php esc_html_e( 'Minimum CPM price for this placement. Semantic matching may increase this.', 'wp-adagent' ); ?>
            </p>
        </td>
    </tr>

    <tr>
        <th scope="row">
            <label for="_wp_adagent_floor_cap"><?php esc_html_e( 'Floor Cap ($)', 'wp-adagent' ); ?></label>
        </th>
        <td>
            <input
                type="number"
                id="_wp_adagent_floor_cap"
                name="_wp_adagent_floor_cap"
                value="<?php echo esc_attr( $floor_cap ); ?>"
                class="small-text"
                step="0.01"
                min="0"
                placeholder="10.00"
            />
            <p class="description">
                <?php esc_html_e( 'Maximum floor price (safety limit). Semantic matching will not exceed this value.', 'wp-adagent' ); ?>
            </p>
        </td>
    </tr>

    <tr>
        <th scope="row">
            <label for="_wp_adagent_css_selector"><?php esc_html_e( 'CSS Selector', 'wp-adagent' ); ?></label>
        </th>
        <td>
            <input
                type="text"
                id="_wp_adagent_css_selector"
                name="_wp_adagent_css_selector"
                value="<?php echo esc_attr( $css_selector ); ?>"
                class="regular-text"
                placeholder="#ad-sidebar"
            />
            <p class="description">
                <?php esc_html_e( 'Optional CSS selector if you want to target a specific element (advanced use).', 'wp-adagent' ); ?>
            </p>
        </td>
    </tr>

    <tr>
        <th scope="row">
            <label for="_wp_adagent_context_tags"><?php esc_html_e( 'Context Tags', 'wp-adagent' ); ?></label>
        </th>
        <td>
            <input
                type="text"
                id="_wp_adagent_context_tags"
                name="_wp_adagent_context_tags"
                value="<?php echo esc_attr( $context_tags ); ?>"
                class="regular-text"
                placeholder="finance, commodities, investing"
            />
            <p class="description">
                <?php esc_html_e( 'Comma-separated tags to help with semantic matching (e.g., "finance, commodities").', 'wp-adagent' ); ?>
            </p>
        </td>
    </tr>

    <tr>
        <th scope="row"><?php esc_html_e( 'Options', 'wp-adagent' ); ?></th>
        <td>
            <fieldset>
                <label>
                    <input
                        type="checkbox"
                        id="_wp_adagent_enable_semantic"
                        name="_wp_adagent_enable_semantic"
                        value="1"
                        <?php checked( $enable_semantic, '1' ); ?>
                    />
                    <?php esc_html_e( 'Enable semantic matching for this placement', 'wp-adagent' ); ?>
                </label>
                <br><br>
                <label>
                    <input
                        type="checkbox"
                        id="_wp_adagent_active"
                        name="_wp_adagent_active"
                        value="1"
                        <?php checked( $active, '1' ); ?>
                    />
                    <?php esc_html_e( 'Active (enable this placement)', 'wp-adagent' ); ?>
                </label>
            </fieldset>
        </td>
    </tr>
</table>

<style>
    .form-table input.small-text {
        width: 80px;
    }
</style>
