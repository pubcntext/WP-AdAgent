/**
 * WP-AdAgent Admin JavaScript
 *
 * Common admin functionality.
 *
 * @package WP_AdAgent
 * @since   1.0.0
 */

(function($) {
    'use strict';

    // Ensure config is available
    if (typeof wpAdAgentAdmin === 'undefined') {
        console.warn('[WP-AdAgent Admin] Configuration not found.');
        return;
    }

    const config = wpAdAgentAdmin;

    /**
     * Show admin notice
     */
    function showNotice(message, type = 'success') {
        const notice = $('<div/>', {
            class: `notice notice-${type} is-dismissible`,
            html: `<p>${message}</p>`
        });

        $('.wrap h1').first().after(notice);

        // Make dismissible
        notice.find('.notice-dismiss').on('click', function() {
            notice.fadeOut(300, function() {
                $(this).remove();
            });
        });

        // Auto-dismiss success notices
        if (type === 'success') {
            setTimeout(function() {
                notice.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }
    }

    /**
     * API request helper
     */
    async function apiRequest(endpoint, method = 'GET', data = null) {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': config.nonce
            }
        };

        if (data) {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(config.restUrl + endpoint, options);

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Request failed');
        }

        return await response.json();
    }

    /**
     * Initialize common admin features
     */
    function init() {
        // Add loading state to buttons
        $('form').on('submit', function() {
            const $button = $(this).find('input[type="submit"], button[type="submit"]');
            $button.prop('disabled', true).addClass('updating-message');
        });

        // Confirm dangerous actions
        $('[data-confirm]').on('click', function(e) {
            const message = $(this).data('confirm');
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });

        // Initialize tooltips
        initTooltips();

        // Initialize tabs if present
        initTabs();
    }

    /**
     * Initialize tooltips
     */
    function initTooltips() {
        $('[data-tooltip]').each(function() {
            const $el = $(this);
            const text = $el.data('tooltip');

            $el.on('mouseenter', function() {
                const tooltip = $('<div/>', {
                    class: 'wp-adagent-tooltip',
                    text: text
                }).appendTo('body');

                const offset = $el.offset();
                tooltip.css({
                    top: offset.top - tooltip.outerHeight() - 5,
                    left: offset.left + ($el.outerWidth() / 2) - (tooltip.outerWidth() / 2)
                });
            }).on('mouseleave', function() {
                $('.wp-adagent-tooltip').remove();
            });
        });
    }

    /**
     * Initialize tab navigation
     */
    function initTabs() {
        const $tabs = $('.wp-adagent-tabs');
        if ($tabs.length === 0) return;

        $tabs.find('.nav-tab').on('click', function(e) {
            e.preventDefault();

            const $tab = $(this);
            const target = $tab.attr('href');

            // Update active tab
            $tabs.find('.nav-tab').removeClass('nav-tab-active');
            $tab.addClass('nav-tab-active');

            // Show target panel
            $tabs.find('.tab-panel').hide();
            $(target).show();

            // Update URL hash
            if (history.pushState) {
                history.pushState(null, null, target);
            }
        });

        // Activate tab from URL hash
        if (window.location.hash) {
            $tabs.find(`a[href="${window.location.hash}"]`).trigger('click');
        }
    }

    // Expose utilities globally
    window.wpAdAgentAdmin = {
        ...config,
        showNotice: showNotice,
        apiRequest: apiRequest
    };

    // Initialize on DOM ready
    $(document).ready(init);

})(jQuery);
