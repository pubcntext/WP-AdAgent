/**
 * WP-AdAgent Initialization Script
 *
 * Main frontend script that initializes Prebid.js and handles
 * semantic matching with the Pubcontext API.
 *
 * @package WP_AdAgent
 * @since   1.0.0
 */

(function() {
    'use strict';

    // Ensure config is available
    if (typeof wpAdAgentConfig === 'undefined') {
        console.warn('[WP-AdAgent] Configuration not found. Plugin may not be properly configured.');
        return;
    }

    const config = wpAdAgentConfig;
    const DEBUG = config.debug || false;

    /**
     * Log debug messages
     */
    function log(...args) {
        if (DEBUG) {
            console.log('[WP-AdAgent]', ...args);
        }
    }

    /**
     * Log errors
     */
    function logError(...args) {
        console.error('[WP-AdAgent]', ...args);
    }

    /**
     * Main AdAgent class
     */
    class AdAgent {
        constructor() {
            this.placements = [];
            this.adUnits = [];
            this.contextData = null;
            this.matchData = null;
            this.isInitialized = false;
        }

        /**
         * Initialize the ad agent
         */
        async init() {
            log('Initializing...');

            try {
                // Fetch configuration from REST API
                const configData = await this.fetchConfig();

                if (!configData || !configData.placements || configData.placements.length === 0) {
                    log('No placements configured, skipping initialization');
                    return;
                }

                this.placements = configData.placements;

                // Find placements on the page
                const pagePlacements = this.findPagePlacements();

                if (pagePlacements.length === 0) {
                    log('No placement elements found on page');
                    return;
                }

                log('Found', pagePlacements.length, 'placements on page');

                // Extract page context
                if (config.semanticEnabled && typeof wpAdAgentContext !== 'undefined') {
                    this.contextData = wpAdAgentContext.extractPageContext();
                    log('Context extracted:', this.contextData);
                }

                // Build Prebid ad units
                this.adUnits = this.buildAdUnits(pagePlacements, configData);

                // Initialize Prebid
                await this.initPrebid(configData);

                // Call semantic matching API if enabled
                if (config.semanticEnabled) {
                    await this.callSemanticMatching(pagePlacements, configData);
                }

                // Request bids
                this.requestBids();

                this.isInitialized = true;
                log('Initialization complete');

            } catch (error) {
                logError('Initialization failed:', error);
            }
        }

        /**
         * Fetch configuration from REST API
         */
        async fetchConfig() {
            try {
                const response = await fetch(config.restUrl + '/config', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': config.nonce
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to fetch config: ' + response.status);
                }

                return await response.json();
            } catch (error) {
                logError('Config fetch error:', error);
                return null;
            }
        }

        /**
         * Find placement elements on the page
         */
        findPagePlacements() {
            const elements = document.querySelectorAll('.wp-adagent-placement[data-placement-id]');
            const found = [];

            elements.forEach(element => {
                const placementId = element.dataset.placementId;
                const placement = this.placements.find(p => p.placementId === placementId);

                if (placement) {
                    found.push({
                        element: element,
                        config: placement,
                        overrideFloor: parseFloat(element.dataset.overrideFloor) || 0
                    });
                }
            });

            return found;
        }

        /**
         * Build Prebid ad units from placements
         */
        buildAdUnits(pagePlacements, configData) {
            return pagePlacements.map(({ element, config: placement, overrideFloor }) => {
                // Parse sizes
                const sizes = this.parseSizes(placement.sizes);

                // Build bidders
                const bids = this.buildBids(placement, configData.prebid.bidders);

                // Create ad unit
                const adUnit = {
                    code: placement.adUnitCode || placement.placementId,
                    mediaTypes: {
                        banner: {
                            sizes: sizes
                        }
                    },
                    bids: bids
                };

                // Add floor if price floors enabled
                if (configData.prebid.priceFloors) {
                    const floor = overrideFloor || placement.baseFloor || 0;
                    adUnit.floors = {
                        default: floor
                    };
                }

                // Store reference to element
                adUnit._element = element;
                adUnit._placement = placement;

                return adUnit;
            });
        }

        /**
         * Parse sizes from various formats
         */
        parseSizes(sizes) {
            if (!sizes) return [[300, 250]];

            if (Array.isArray(sizes)) {
                return sizes.map(size => {
                    if (Array.isArray(size)) return size;
                    if (typeof size === 'string') {
                        const [w, h] = size.split('x').map(Number);
                        return [w, h];
                    }
                    return [300, 250];
                });
            }

            return [[300, 250]];
        }

        /**
         * Build bids from enabled bidders
         */
        buildBids(placement, bidders) {
            const bids = [];

            for (const [bidderId, params] of Object.entries(bidders || {})) {
                bids.push({
                    bidder: bidderId,
                    params: { ...params }
                });
            }

            return bids;
        }

        /**
         * Initialize Prebid.js
         */
        async initPrebid(configData) {
            return new Promise((resolve) => {
                window.pbjs = window.pbjs || {};
                window.pbjs.que = window.pbjs.que || [];

                window.pbjs.que.push(() => {
                    // Set configuration
                    window.pbjs.setConfig({
                        debug: DEBUG,
                        bidderTimeout: configData.prebid.timeout || 3000,
                        enableSendAllBids: true,
                        useBidCache: true,
                        priceGranularity: 'dense'
                    });

                    // Add supply chain if configured
                    if (configData.pubcontext.supplyChain) {
                        window.pbjs.setConfig({
                            schain: configData.pubcontext.supplyChain
                        });
                    }

                    // Add ad units
                    window.pbjs.addAdUnits(this.adUnits);

                    log('Prebid initialized with', this.adUnits.length, 'ad units');
                    resolve();
                });
            });
        }

        /**
         * Call semantic matching API
         */
        async callSemanticMatching(pagePlacements, configData) {
            if (!configData.pubcontext.enabled || !configData.pubcontext.endpoint) {
                log('Semantic matching disabled or no endpoint configured');
                return;
            }

            try {
                // Build context payload
                const context = {
                    url: window.location.href,
                    referrer: document.referrer,
                    title: document.title,
                    ...this.contextData,
                    ...(config.pageData || {})
                };

                // Make API call for each placement (could be optimized to batch)
                const promises = pagePlacements.map(async ({ config: placement }) => {
                    try {
                        const response = await this.callMatchAPI(
                            placement.placementId,
                            context,
                            configData.pubcontext.endpoint
                        );

                        if (response && response.matched_creatives) {
                            this.applySemanticFloors(placement, response);
                        }

                        return response;
                    } catch (error) {
                        log('Semantic match failed for placement:', placement.placementId, error);
                        return null;
                    }
                });

                // Wait for all with timeout
                const timeout = config.apiTimeout || 2000;
                await Promise.race([
                    Promise.all(promises),
                    new Promise(resolve => setTimeout(resolve, timeout))
                ]);

            } catch (error) {
                logError('Semantic matching error:', error);
            }
        }

        /**
         * Call the Pubcontext match API
         */
        async callMatchAPI(placementId, context, endpoint) {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    placement_id: placementId,
                    page_url: context.url,
                    referrer: context.referrer,
                    context: context
                })
            });

            if (!response.ok) {
                throw new Error('Match API returned ' + response.status);
            }

            return await response.json();
        }

        /**
         * Apply semantic floor adjustments
         */
        applySemanticFloors(placement, matchResponse) {
            if (!matchResponse.matched_creatives || matchResponse.matched_creatives.length === 0) {
                return;
            }

            const minScore = config.minAlignScore || 0.8;
            let highestFloor = placement.baseFloor || 0;

            for (const creative of matchResponse.matched_creatives) {
                if (creative.context_alignment_score >= minScore) {
                    const suggestedFloor = creative.suggested_bid_floor || 0;
                    if (suggestedFloor > highestFloor) {
                        highestFloor = suggestedFloor;
                    }
                }
            }

            // Apply floor cap
            if (placement.floorCap > 0 && highestFloor > placement.floorCap) {
                highestFloor = placement.floorCap;
            }

            // Update ad unit floor
            const adUnit = this.adUnits.find(u =>
                u._placement && u._placement.placementId === placement.placementId
            );

            if (adUnit) {
                adUnit.floors = { default: highestFloor };
                log('Updated floor for', placement.placementId, 'to', highestFloor);
            }
        }

        /**
         * Request bids from Prebid
         */
        requestBids() {
            window.pbjs.que.push(() => {
                window.pbjs.requestBids({
                    bidsBackHandler: (bids) => {
                        this.handleBidsBack(bids);
                    },
                    timeout: config.prebid.timeout || 3000
                });
            });
        }

        /**
         * Handle bid responses
         */
        handleBidsBack(bids) {
            log('Bids received:', bids);

            this.adUnits.forEach(adUnit => {
                const adUnitBids = bids[adUnit.code];

                if (adUnitBids && adUnitBids.bids && adUnitBids.bids.length > 0) {
                    // Get winning bid
                    const winningBid = adUnitBids.bids.reduce((prev, current) =>
                        (prev.cpm > current.cpm) ? prev : current
                    );

                    log('Winning bid for', adUnit.code, ':', winningBid.cpm, winningBid.bidder);

                    // Render the ad
                    this.renderAd(adUnit, winningBid);

                    // Log impression
                    this.logImpression(adUnit, winningBid);
                } else {
                    log('No bids for', adUnit.code);
                    // Optionally show fallback
                    this.showFallback(adUnit);
                }
            });
        }

        /**
         * Render winning ad
         */
        renderAd(adUnit, winningBid) {
            const element = adUnit._element;
            if (!element) return;

            // Create iframe for ad
            const iframe = document.createElement('iframe');
            iframe.id = 'ad-frame-' + adUnit.code;
            iframe.width = winningBid.width || 300;
            iframe.height = winningBid.height || 250;
            iframe.frameBorder = '0';
            iframe.scrolling = 'no';
            iframe.style.border = 'none';

            element.innerHTML = '';
            element.appendChild(iframe);

            // Write ad content to iframe
            const iframeDoc = iframe.contentWindow.document;
            iframeDoc.open();
            iframeDoc.write(winningBid.ad);
            iframeDoc.close();

            element.classList.add('ad-loaded');
        }

        /**
         * Show fallback content
         */
        showFallback(adUnit) {
            const element = adUnit._element;
            if (!element) return;

            element.classList.add('no-bids');
            // Could show fallback ad or hide element
        }

        /**
         * Log impression to analytics
         */
        logImpression(adUnit, winningBid) {
            fetch(config.restUrl + '/impression', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': config.nonce
                },
                body: JSON.stringify({
                    placement_id: adUnit._placement.placementId,
                    winning_bid: winningBid.cpm,
                    winning_bidder: winningBid.bidder
                })
            }).catch(error => {
                log('Failed to log impression:', error);
            });
        }
    }

    // Initialize on DOM ready
    function onReady(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
        } else {
            callback();
        }
    }

    // Create and start the ad agent
    onReady(() => {
        window.wpAdAgent = new AdAgent();
        window.wpAdAgent.init();
    });

})();
