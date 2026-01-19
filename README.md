# WP-AdAgent (Pubcontext Semantic DSP)

<p align="center">
  <img src="assets/images/logo-banner.png" alt="WP-AdAgent Logo" width="600">
</p>

<p align="center">
  <strong>Prebid.js Header Bidding Plugin for WordPress with Semantic Audience Matching</strong>
</p>

<p align="center">
  <a href="https://wordpress.org/plugins/wp-adagent/"><img src="https://img.shields.io/badge/WordPress-6.0%2B-blue?logo=wordpress" alt="WordPress 6.0+"></a>
  <a href="https://www.php.net/"><img src="https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white" alt="PHP 7.4+"></a>
  <a href="https://prebid.org/"><img src="https://img.shields.io/badge/Prebid.js-7.x-orange?logo=javascript" alt="Prebid.js 7.x"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/License-GPL%20v2-green" alt="License GPL v2"></a>
  <img src="https://img.shields.io/badge/Version-1.0.0-brightgreen" alt="Version 1.0.0">
</p>

<p align="center">
  <a href="#features">Features</a> •
  <a href="#installation">Installation</a> •
  <a href="#quick-start">Quick Start</a> •
  <a href="#configuration">Configuration</a> •
  <a href="#documentation">Documentation</a>
</p>

---

## Overview

**WP-AdAgent** enables WordPress publishers to implement **semantic-aware programmatic advertising** without any technical integration. The plugin bridges [Prebid.js](https://prebid.org/) header bidding with Pubcontext's 4D semantic matching engine to optimize publisher revenue through intelligent creative matching and dynamic floor price adjustment.

### How It Works

```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│   WordPress     │────▶│   WP-AdAgent     │────▶│  Pubcontext API │
│   Page Load     │     │  Context Extract │     │  Semantic Match │
└─────────────────┘     └──────────────────┘     └─────────────────┘
                                 │                        │
                                 ▼                        ▼
                        ┌──────────────────┐     ┌─────────────────┐
                        │   Prebid.js      │◀────│  Dynamic Floor  │
                        │   Auction        │     │  Adjustment     │
                        └──────────────────┘     └─────────────────┘
                                 │
                                 ▼
                        ┌──────────────────┐
                        │   Ad Rendered    │
                        │   Higher CPM! 💰 │
                        └──────────────────┘
```

---

## Admin UI Screenshots

<p align="center">
  <img src="assets/images/screenshots/admin-dashboard.png" alt="Admin Dashboard" width="800">
  <br>
  <em>Main Dashboard - Overview of ad performance and revenue metrics</em>
</p>

<p align="center">
  <img src="assets/images/screenshots/settings-page.png" alt="Settings Page" width="800">
  <br>
  <em>Settings Page - Configure Pubcontext API and semantic matching</em>
</p>

<p align="center">
  <img src="assets/images/screenshots/prebid-config.png" alt="Prebid Configuration" width="800">
  <br>
  <em>Prebid Configuration - Select and configure header bidding partners</em>
</p>

<p align="center">
  <img src="assets/images/screenshots/placements-manager.png" alt="Placements Manager" width="800">
  <br>
  <em>Placements Manager - Create and manage ad placements visually</em>
</p>

<p align="center">
  <img src="assets/images/screenshots/gutenberg-block.png" alt="Gutenberg Block" width="800">
  <br>
  <em>Gutenberg Block - Insert ad placements directly in the editor</em>
</p>

---

## Features

### For Publishers

| Feature | Description |
|---------|-------------|
| **One-Click Setup** | No coding required - configure everything from WordPress admin |
| **Semantic Matching** | AI-powered content analysis for better ad relevance |
| **Dynamic Floor Pricing** | Automatic floor adjustment based on content-ad alignment |
| **Visual Placement Manager** | Create and manage ad slots with an intuitive UI |
| **Gutenberg Block** | Insert ad placements directly in the block editor |
| **Real-Time Analytics** | Track impressions, revenue, and optimization metrics |
| **Supply Chain Transparency** | Full ads.txt and supply chain configuration |

### For Advertisers (via Pubcontext DSP)

- **Lower Wastage** - Target only high-intent placements
- **Better ROI** - Audience precision matching
- **Semantic Transparency** - Know exactly where your ads appear

### Revenue Impact

```
┌────────────────────────────────────────────────────────────┐
│                    Expected Revenue Lift                    │
├────────────────────────────────────────────────────────────┤
│  Traditional Programmatic  │████████░░░░░░░░░│  $2.00 CPM  │
│  With WP-AdAgent           │████████████████░│  $3.50 CPM  │
│                            │                  │             │
│  Revenue Lift: 60-80% on matched inventory                 │
└────────────────────────────────────────────────────────────┘
```

---

## Installation

### From WordPress Admin

1. Go to **Plugins > Add New**
2. Search for "WP-AdAgent" or "Pubcontext"
3. Click **Install Now** then **Activate**

### Manual Installation

```bash
# Download the plugin
wget https://github.com/pubcntext/WP-AdAgent/releases/latest/download/wp-adagent.zip

# Extract to WordPress plugins directory
unzip wp-adagent.zip -d /path/to/wordpress/wp-content/plugins/

# Or clone for development
git clone https://github.com/pubcntext/WP-AdAgent.git wp-content/plugins/wp-adagent
```

### Requirements

| Requirement | Version |
|-------------|---------|
| WordPress | 6.0 or higher |
| PHP | 7.4 or higher |
| MySQL | 5.7 or higher |

---

## Quick Start

### Step 1: Get Your API Key

1. Sign up at [Pubcontext Dashboard](https://dashboard.pubcontext.com)
2. Create a new property for your WordPress site
3. Copy your API key

### Step 2: Configure the Plugin

```
WordPress Admin > Pubcontext > Settings
```

1. Paste your **API Key**
2. Click **Test Connection** to verify
3. Enable **Semantic Matching**
4. Save changes

### Step 3: Configure Prebid Bidders

```
WordPress Admin > Pubcontext > Prebid Configuration
```

1. Select your header bidding partners (AppNexus, Rubicon, etc.)
2. Enter your bidder-specific parameters
3. Set auction timeout (default: 3000ms)
4. Save configuration

### Step 4: Create Ad Placements

```
WordPress Admin > Pubcontext > Placements > Add New
```

| Field | Example Value |
|-------|---------------|
| Placement ID | `sidebar-300x250` |
| Ad Unit Code | `/1234567/homepage/sidebar` |
| Sizes | `300x250, 300x600` |
| Base Floor | `$2.50` |
| Floor Cap | `$8.00` |

### Step 5: Insert Placements in Content

**Using Gutenberg Block:**
1. Edit a page or post
2. Add block: **Pubcontext Ad Placement**
3. Select your placement from dropdown
4. Publish!

**Using Shortcode:**
```
[pubcontext_placement id="sidebar-300x250"]
```

**Using PHP Template Tag:**
```php
<?php pubcontext_placement('sidebar-300x250'); ?>
```

---

## Configuration

### Plugin Settings

| Setting | Description | Default |
|---------|-------------|---------|
| `API Endpoint` | Pubcontext API URL | `https://api.pubcontext.com/match` |
| `API Key` | Your Pubcontext API key | - |
| `Semantic Matching` | Enable/disable semantic optimization | Enabled |

### Prebid Settings

| Setting | Description | Default |
|---------|-------------|---------|
| `Prebid Version` | Prebid.js library version | `7.49.0` |
| `Auction Timeout` | Max time for bids (ms) | `3000` |
| `Price Floors` | Enable dynamic floor pricing | Enabled |

### Placement Settings

| Field | Description | Required |
|-------|-------------|----------|
| `Placement ID` | Unique identifier (slug format) | Yes |
| `Ad Unit Code` | Prebid ad unit path | Yes |
| `Sizes` | Supported ad sizes (JSON array) | Yes |
| `Base Floor` | Minimum CPM price | Yes |
| `Floor Cap` | Maximum floor limit | No |
| `Page Rules` | Target specific post types/categories | No |
| `Context Tags` | Manual semantic tags | No |

---

## Architecture

```
wp-adagent/
├── wp-adagent.php                    # Main plugin file
├── README.md                         # This file
├── readme.txt                        # WordPress.org readme
├── composer.json                     # PHP dependencies
├── package.json                      # JS dependencies
│
├── includes/
│   ├── plugin.php                    # Plugin initialization
│   ├── constants.php                 # Global constants
│   │
│   ├── admin/
│   │   ├── class-admin.php           # Admin menu & pages
│   │   ├── class-settings.php        # API settings
│   │   ├── class-prebid-config.php   # Prebid configuration
│   │   ├── class-placements.php      # Placement CRUD
│   │   └── class-supply-chain.php    # Supply chain config
│   │
│   ├── frontend/
│   │   ├── class-scripts.php         # Script enqueuing
│   │   └── class-blocks.php          # Gutenberg blocks
│   │
│   ├── api/
│   │   ├── class-rest-endpoints.php  # WordPress REST API
│   │   └── class-pubcontext-client.php # Pubcontext API client
│   │
│   └── utils/
│       ├── class-encryption.php      # API key encryption
│       └── class-logger.php          # Debug logging
│
├── assets/
│   ├── js/
│   │   ├── pubcontext-init.js        # Frontend initialization
│   │   ├── context-extractor.js      # Semantic context extraction
│   │   └── admin/                    # Admin JS files
│   │
│   ├── css/
│   │   ├── admin.css                 # Admin styles
│   │   └── blocks.css                # Block editor styles
│   │
│   └── images/
│       └── screenshots/              # Admin UI screenshots
│
├── templates/
│   ├── admin/                        # Admin page templates
│   └── blocks/                       # Block templates
│
└── languages/
    └── wp-adagent.pot                # Translation template
```

---

## REST API

### Get Configuration

```http
POST /wp-json/pubcontext/v1/config
```

**Response:**
```json
{
  "placements": [...],
  "prebid": {
    "version": "7.49.0",
    "bidders": [...],
    "timeout": 3000
  },
  "pubcontext": {
    "enabled": true,
    "endpoint": "https://api.pubcontext.com/match"
  }
}
```

### Test API Connection

```http
POST /wp-json/pubcontext/v1/test-api
```

**Payload:**
```json
{
  "api_key": "your-api-key",
  "endpoint": "https://api.pubcontext.com/match"
}
```

---

## Hooks & Filters

### Actions

```php
// Fired before Prebid auction starts
do_action('pubcontext_before_auction', $placements);

// Fired after semantic matching completes
do_action('pubcontext_after_match', $match_result);

// Fired when a placement is rendered
do_action('pubcontext_render_placement', $placement_id);
```

### Filters

```php
// Modify context before API call
add_filter('pubcontext_context_data', function($context) {
    $context['custom_field'] = 'value';
    return $context;
});

// Modify floor price calculation
add_filter('pubcontext_floor_price', function($floor, $placement_id, $match_score) {
    return $floor * 1.1; // 10% increase
}, 10, 3);

// Add custom bidders
add_filter('pubcontext_bidder_config', function($bidders) {
    $bidders['custom_bidder'] = ['param1' => 'value'];
    return $bidders;
});
```

---

## Troubleshooting

### Common Issues

| Issue | Solution |
|-------|----------|
| "API Connection Failed" | Check your API key and endpoint URL |
| "Prebid not loading" | Ensure no ad blockers are active, check console for errors |
| "Floors not bumping" | Verify semantic matching is enabled and API is responding |
| "Block not appearing" | Clear WordPress cache, check Gutenberg is active |

### Debug Mode

Enable debug logging in `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('PUBCONTEXT_DEBUG', true);
```

Logs are written to `wp-content/debug.log`

---

## Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

```bash
# Clone the repository
git clone https://github.com/pubcntext/WP-AdAgent.git

# Install dependencies
composer install
npm install

# Run tests
composer test
npm test
```

---

## Support

- **Documentation:** [docs.pubcontext.com](https://docs.pubcontext.com)
- **Issues:** [GitHub Issues](https://github.com/pubcntext/WP-AdAgent/issues)
- **Email:** support@pubcontext.com

---

## License

This plugin is licensed under the [GPL v2 or later](LICENSE).

```
WP-AdAgent - Prebid.js Header Bidding for WordPress
Copyright (C) 2026 Pubcontext

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

---

<p align="center">
  Made with ❤️ by <a href="https://pubcontext.com">Pubcontext</a>
</p>

<p align="center">
  <a href="https://pubcontext.com">Website</a> •
  <a href="https://twitter.com/pubcontext">Twitter</a> •
  <a href="https://linkedin.com/company/pubcontext">LinkedIn</a>
</p>
