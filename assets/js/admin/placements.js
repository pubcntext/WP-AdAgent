/**
 * WP-AdAgent Placements Page JavaScript
 *
 * Handles placement management interactions.
 *
 * @package WP_AdAgent
 * @since   1.0.0
 */

(function($) {
    'use strict';

    const admin = window.wpAdAgentAdmin;
    if (!admin) return;

    /**
     * Initialize placements page
     */
    function init() {
        initDeleteConfirm();
        initBulkActions();
        initSizesParsing();
    }

    /**
     * Initialize delete confirmation
     */
    function initDeleteConfirm() {
        $('.row-actions .delete a, .row-actions .trash a').on('click', function(e) {
            if (!confirm('Are you sure you want to delete this placement? This action cannot be undone.')) {
                e.preventDefault();
                return false;
            }
        });
    }

    /**
     * Initialize bulk actions
     */
    function initBulkActions() {
        $('#doaction, #doaction2').on('click', function(e) {
            const $select = $(this).prev('select');
            const action = $select.val();

            if (action === 'delete' || action === 'trash') {
                const selected = $('input[name="post[]"]:checked').length;
                if (selected > 0 && !confirm(`Are you sure you want to delete ${selected} placement(s)?`)) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    }

    /**
     * Initialize sizes field parsing
     */
    function initSizesParsing() {
        const $sizesInput = $('input[name="_wp_adagent_sizes"]');
        if ($sizesInput.length === 0) return;

        // Format on blur
        $sizesInput.on('blur', function() {
            const value = $(this).val().trim();
            if (!value) return;

            // Try to parse and format
            try {
                // Check if it's already JSON
                const parsed = JSON.parse(value);
                if (Array.isArray(parsed)) {
                    $(this).val(JSON.stringify(parsed));
                    return;
                }
            } catch (e) {
                // Not JSON, try to parse comma-separated
                const sizes = value.split(',').map(s => s.trim()).filter(s => s);
                if (sizes.length > 0) {
                    $(this).val(JSON.stringify(sizes));
                }
            }
        });

        // Add help text
        $sizesInput.after(`
            <p class="description" style="margin-top: 5px;">
                Enter sizes as comma-separated values (e.g., "300x250, 728x90") or JSON array (e.g., ["300x250", "728x90"])
            </p>
        `);
    }

    // Initialize on DOM ready
    $(document).ready(init);

})(jQuery);
