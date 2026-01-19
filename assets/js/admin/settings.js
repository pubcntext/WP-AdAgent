/**
 * WP-AdAgent Settings Page JavaScript
 *
 * Handles settings page interactions including API testing.
 *
 * @package WP_AdAgent
 * @since   1.0.0
 */

(function($) {
    'use strict';

    const admin = window.wpAdAgentAdmin;
    if (!admin) return;

    /**
     * Initialize settings page
     */
    function init() {
        initApiTest();
        initToggleVisibility();
    }

    /**
     * Initialize API test button
     */
    function initApiTest() {
        const $button = $('#wp-adagent-test-api');
        const $result = $('#wp-adagent-test-result');
        const $apiKeyInput = $('#wp_adagent_api_key');
        const $endpointInput = $('#wp_adagent_api_endpoint');

        if ($button.length === 0) return;

        $button.on('click', async function(e) {
            e.preventDefault();

            const apiKey = $apiKeyInput.val();
            const endpoint = $endpointInput.val();

            if (!apiKey) {
                $result.html(`<span class="error">${admin.strings.error}: API key is required</span>`);
                return;
            }

            // Show loading state
            $button.prop('disabled', true);
            $result.html(`<span class="loading">${admin.strings.testing}</span>`);

            try {
                const response = await admin.apiRequest('/test-api', 'POST', {
                    api_key: apiKey,
                    endpoint: endpoint
                });

                if (response.success) {
                    $result.html(`<span class="success">${admin.strings.testSuccess}</span>`);
                    admin.showNotice(admin.strings.testSuccess, 'success');
                } else {
                    $result.html(`<span class="error">${response.message || admin.strings.testFailed}</span>`);
                }
            } catch (error) {
                $result.html(`<span class="error">${admin.strings.testFailed}: ${error.message}</span>`);
            } finally {
                $button.prop('disabled', false);
            }
        });
    }

    /**
     * Initialize password visibility toggle
     */
    function initToggleVisibility() {
        $('input[type="password"]').each(function() {
            const $input = $(this);
            const $toggle = $('<button/>', {
                type: 'button',
                class: 'button button-secondary wp-adagent-toggle-visibility',
                text: 'Show',
                css: { marginLeft: '5px' }
            });

            $input.after($toggle);

            $toggle.on('click', function() {
                if ($input.attr('type') === 'password') {
                    $input.attr('type', 'text');
                    $toggle.text('Hide');
                } else {
                    $input.attr('type', 'password');
                    $toggle.text('Show');
                }
            });
        });
    }

    // Initialize on DOM ready
    $(document).ready(init);

})(jQuery);
