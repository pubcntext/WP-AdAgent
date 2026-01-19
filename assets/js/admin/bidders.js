/**
 * WP-AdAgent Bidders Configuration JavaScript
 *
 * Handles bidder selection and parameter configuration.
 *
 * @package WP_AdAgent
 * @since   1.0.0
 */

(function($) {
    'use strict';

    const admin = window.wpAdAgentAdmin;
    if (!admin) return;

    /**
     * Initialize bidders page
     */
    function init() {
        initBidderToggles();
        initFormSubmission();
    }

    /**
     * Initialize bidder enable/disable toggles
     */
    function initBidderToggles() {
        $('.wp-adagent-bidder-enabled').on('change', function() {
            const $checkbox = $(this);
            const $card = $checkbox.closest('.wp-adagent-bidder-card');
            const $params = $card.find('.wp-adagent-bidder-params');

            if ($checkbox.is(':checked')) {
                $params.slideDown(200);
                $card.addClass('enabled');
            } else {
                $params.slideUp(200);
                $card.removeClass('enabled');
            }

            updateBiddersJson();
        });

        // Handle param changes
        $('.wp-adagent-bidder-params input').on('change keyup', function() {
            updateBiddersJson();
        });
    }

    /**
     * Update the hidden JSON field with current bidder configuration
     */
    function updateBiddersJson() {
        const bidders = {};

        $('.wp-adagent-bidder-card').each(function() {
            const $card = $(this);
            const bidderId = $card.data('bidder');
            const $enabled = $card.find('.wp-adagent-bidder-enabled');

            if (!$enabled.is(':checked')) {
                return;
            }

            const params = {};
            $card.find('.wp-adagent-bidder-params input').each(function() {
                const $input = $(this);
                const name = $input.attr('name');
                const value = $input.val();

                // Extract param name from name attribute
                const match = name.match(/\[([^\]]+)\]$/);
                if (match && value) {
                    params[match[1]] = value;
                }
            });

            if (Object.keys(params).length > 0) {
                bidders[bidderId] = params;
            }
        });

        $('#wp_adagent_prebid_bidders').val(JSON.stringify(bidders));
    }

    /**
     * Initialize form submission
     */
    function initFormSubmission() {
        $('form').on('submit', function() {
            updateBiddersJson();
        });
    }

    // Initialize on DOM ready
    $(document).ready(init);

})(jQuery);
