/**
 * WP-AdAgent Gutenberg Block
 *
 * Pubcontext Ad Placement block for the WordPress block editor.
 *
 * @package WP_AdAgent
 * @since   1.0.0
 */

(function(wp) {
    'use strict';

    const { registerBlockType } = wp.blocks;
    const { createElement: el, Fragment } = wp.element;
    const { InspectorControls, useBlockProps } = wp.blockEditor;
    const { PanelBody, SelectControl, TextControl, ToggleControl, Placeholder } = wp.components;
    const { __ } = wp.i18n;

    // Get placements from localized data
    const blockConfig = window.wpAdAgentBlock || { placements: [] };

    /**
     * Block icon
     */
    const blockIcon = el('svg', {
        width: 24,
        height: 24,
        viewBox: '0 0 24 24',
        fill: 'none',
        xmlns: 'http://www.w3.org/2000/svg'
    },
        el('rect', {
            x: 2,
            y: 4,
            width: 20,
            height: 16,
            rx: 2,
            stroke: 'currentColor',
            strokeWidth: 2,
            fill: 'none'
        }),
        el('path', {
            d: 'M8 10h8M8 14h5',
            stroke: 'currentColor',
            strokeWidth: 2,
            strokeLinecap: 'round'
        }),
        el('circle', {
            cx: 17,
            cy: 14,
            r: 2,
            fill: 'currentColor'
        })
    );

    /**
     * Edit component
     */
    function EditBlock({ attributes, setAttributes }) {
        const { placementId, overrideFloor, showAnalytics, align } = attributes;

        const blockProps = useBlockProps({
            className: `wp-block-pubcontext-ad-placement align${align || 'center'}`
        });

        // Build placement options
        const placementOptions = [
            { label: __('Select a placement...', 'wp-adagent'), value: '' },
            ...blockConfig.placements.map(p => ({
                label: `${p.title} (${p.placementId})`,
                value: p.placementId
            }))
        ];

        // Find selected placement
        const selectedPlacement = blockConfig.placements.find(p => p.placementId === placementId);

        // Get sizes display
        const sizesDisplay = selectedPlacement && selectedPlacement.sizes
            ? selectedPlacement.sizes.join(', ')
            : '300x250';

        return el(Fragment, {},
            // Inspector controls (sidebar)
            el(InspectorControls, {},
                el(PanelBody, {
                    title: __('Placement Settings', 'wp-adagent'),
                    initialOpen: true
                },
                    el(SelectControl, {
                        label: __('Ad Placement', 'wp-adagent'),
                        value: placementId,
                        options: placementOptions,
                        onChange: (value) => setAttributes({ placementId: value }),
                        help: __('Select the ad placement to display.', 'wp-adagent')
                    }),
                    el(TextControl, {
                        label: __('Override Floor Price ($)', 'wp-adagent'),
                        type: 'number',
                        value: overrideFloor || '',
                        onChange: (value) => setAttributes({ overrideFloor: parseFloat(value) || 0 }),
                        help: __('Override the base floor price for this placement. Leave empty to use the configured floor.', 'wp-adagent'),
                        step: 0.01,
                        min: 0
                    }),
                    el(ToggleControl, {
                        label: __('Show Analytics Overlay', 'wp-adagent'),
                        checked: showAnalytics,
                        onChange: (value) => setAttributes({ showAnalytics: value }),
                        help: __('Show real-time analytics overlay on the ad (visible to admins only).', 'wp-adagent')
                    })
                ),
                selectedPlacement && el(PanelBody, {
                    title: __('Placement Info', 'wp-adagent'),
                    initialOpen: false
                },
                    el('p', {}, el('strong', {}, __('ID: ', 'wp-adagent')), selectedPlacement.placementId),
                    el('p', {}, el('strong', {}, __('Sizes: ', 'wp-adagent')), sizesDisplay),
                    el('p', {}, el('strong', {}, __('Base Floor: ', 'wp-adagent')), `$${selectedPlacement.baseFloor || 0}`),
                    selectedPlacement.floorCap > 0 && el('p', {}, el('strong', {}, __('Floor Cap: ', 'wp-adagent')), `$${selectedPlacement.floorCap}`)
                )
            ),

            // Block content
            el('div', blockProps,
                !placementId
                    ? el(Placeholder, {
                        icon: blockIcon,
                        label: __('Pubcontext Ad Placement', 'wp-adagent'),
                        instructions: __('Select an ad placement from the dropdown to display an ad here.', 'wp-adagent')
                    },
                        el(SelectControl, {
                            value: placementId,
                            options: placementOptions,
                            onChange: (value) => setAttributes({ placementId: value })
                        })
                    )
                    : el('div', {
                        className: 'wp-adagent-block-preview',
                        style: {
                            border: '2px dashed #ccc',
                            padding: '20px',
                            textAlign: 'center',
                            backgroundColor: '#f9f9f9',
                            borderRadius: '4px'
                        }
                    },
                        el('div', {
                            className: 'wp-adagent-block-preview-icon',
                            style: { marginBottom: '10px' }
                        }, blockIcon),
                        el('strong', {}, selectedPlacement ? selectedPlacement.title : placementId),
                        el('div', {
                            style: { color: '#666', fontSize: '12px', marginTop: '5px' }
                        },
                            sizesDisplay,
                            overrideFloor > 0 && ` | Floor: $${overrideFloor}`
                        )
                    )
            )
        );
    }

    /**
     * Save component
     */
    function SaveBlock({ attributes }) {
        const { placementId, overrideFloor, showAnalytics, align } = attributes;

        if (!placementId) {
            return null;
        }

        const blockProps = useBlockProps.save({
            className: `wp-block-pubcontext-ad-placement wp-adagent-placement align${align || 'center'}`,
            'data-placement-id': placementId
        });

        if (overrideFloor > 0) {
            blockProps['data-override-floor'] = overrideFloor;
        }

        if (showAnalytics) {
            blockProps['data-show-analytics'] = 'true';
        }

        return el('div', blockProps);
    }

    /**
     * Register block
     */
    registerBlockType('pubcontext/ad-placement', {
        title: __('Ad Placement', 'wp-adagent'),
        description: __('Display a Pubcontext ad placement with semantic targeting.', 'wp-adagent'),
        category: 'widgets',
        icon: blockIcon,
        keywords: [
            __('ad', 'wp-adagent'),
            __('advertisement', 'wp-adagent'),
            __('prebid', 'wp-adagent'),
            __('pubcontext', 'wp-adagent'),
            __('banner', 'wp-adagent')
        ],
        attributes: {
            placementId: {
                type: 'string',
                default: ''
            },
            overrideFloor: {
                type: 'number',
                default: 0
            },
            showAnalytics: {
                type: 'boolean',
                default: false
            },
            align: {
                type: 'string',
                default: 'center'
            }
        },
        supports: {
            align: ['left', 'center', 'right', 'wide', 'full'],
            html: false
        },
        edit: EditBlock,
        save: SaveBlock
    });

})(window.wp);
