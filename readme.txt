=== WP-AdAgent - Prebid.js Header Bidding with Semantic Matching ===
Contributors: pubcontext
Tags: advertising, header bidding, prebid, programmatic, semantic
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Prebid.js header bidding plugin for WordPress with semantic audience matching. Optimize publisher revenue through intelligent creative matching.

== Description ==

**WP-AdAgent** enables WordPress publishers to implement semantic-aware programmatic advertising without any technical integration. The plugin bridges Prebid.js header bidding with Pubcontext's 4D semantic matching engine to optimize publisher revenue through intelligent creative matching and dynamic floor price adjustment.

= Key Features =

* **One-Click Setup** - No coding required, configure everything from WordPress admin
* **Semantic Matching** - AI-powered content analysis for better ad relevance
* **Dynamic Floor Pricing** - Automatic floor adjustment based on content-ad alignment
* **Visual Placement Manager** - Create and manage ad slots with an intuitive UI
* **Gutenberg Block** - Insert ad placements directly in the block editor
* **Real-Time Analytics** - Track impressions, revenue, and optimization metrics
* **Supply Chain Transparency** - Full ads.txt and supply chain configuration

= How It Works =

1. Extract semantic context from your WordPress content
2. Send context to Pubcontext API for matching
3. Adjust floor prices based on semantic alignment
4. Run Prebid.js auction with optimized floors
5. Render highest-performing ads

= Revenue Impact =

Publishers using WP-AdAgent typically see a 60-80% revenue lift on matched inventory compared to traditional programmatic advertising.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wp-adagent` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to Pubcontext > Settings to configure your API key.
4. Set up your Prebid bidders in Pubcontext > Prebid Configuration.
5. Create ad placements in Pubcontext > Placements.
6. Insert placements using the Gutenberg block or shortcode.

== Frequently Asked Questions ==

= Do I need a Pubcontext account? =

Yes, you need to sign up at [Pubcontext Dashboard](https://dashboard.pubcontext.com) to get your API key. A free tier is available for testing.

= Which header bidding partners are supported? =

WP-AdAgent supports all major Prebid.js bidders including AppNexus, Rubicon, OpenX, Index Exchange, and many more.

= Will this slow down my site? =

No. The plugin is optimized for performance with asynchronous loading and configurable auction timeouts.

= Is this compatible with my theme? =

Yes, WP-AdAgent is theme-agnostic and works with any properly-coded WordPress theme.

= Can I use this with Google Ad Manager? =

Yes, WP-AdAgent integrates seamlessly with GAM for publishers using a hybrid Prebid + GAM setup.

== Screenshots ==

1. Main Dashboard - Overview of ad performance and revenue metrics
2. Settings Page - Configure Pubcontext API and semantic matching
3. Prebid Configuration - Select and configure header bidding partners
4. Placements Manager - Create and manage ad placements visually
5. Gutenberg Block - Insert ad placements directly in the editor

== Changelog ==

= 1.0.0 =
* Initial release
* Prebid.js integration with configurable bidders
* Semantic context extraction from WordPress content
* Pubcontext API integration for semantic matching
* Dynamic floor price adjustment
* Visual placement manager with CRUD operations
* Gutenberg block for ad placement insertion
* Shortcode support for classic editor
* Admin dashboard with analytics overview
* Supply chain configuration for ads.txt

== Upgrade Notice ==

= 1.0.0 =
Initial release of WP-AdAgent.

== Additional Info ==

For documentation and support, visit [docs.pubcontext.com](https://docs.pubcontext.com).

For bug reports and feature requests, use our [GitHub repository](https://github.com/pubcontext/WP-AdAgent/issues).
