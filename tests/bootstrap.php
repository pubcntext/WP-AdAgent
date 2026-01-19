<?php
/**
 * PHPUnit bootstrap file for WP-AdAgent tests.
 *
 * @package WP_AdAgent
 */

// Composer autoloader.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Load Brain\Monkey for WordPress function mocking.
require_once dirname( __DIR__ ) . '/vendor/brain/monkey/inc/patchwork-loader.php';

use Brain\Monkey;
use Yoast\PHPUnitPolyfills\Autoload;

/**
 * Load Yoast PHPUnit Polyfills.
 */
if ( ! class_exists( Autoload::class ) ) {
	require_once dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';
}

/**
 * Bootstrap Brain\Monkey.
 */
Monkey\setUp();

/**
 * Define WordPress constants for testing.
 */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/wordpress/' );
}

if ( ! defined( 'WP_ADAGENT_PLUGIN_DIR' ) ) {
	define( 'WP_ADAGENT_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
}

if ( ! defined( 'WP_ADAGENT_PLUGIN_URL' ) ) {
	define( 'WP_ADAGENT_PLUGIN_URL', 'http://example.com/wp-content/plugins/wp-adagent/' );
}

if ( ! defined( 'WP_ADAGENT_VERSION' ) ) {
	define( 'WP_ADAGENT_VERSION', '1.0.0' );
}

if ( ! defined( 'WP_ADAGENT_TESTING' ) ) {
	define( 'WP_ADAGENT_TESTING', true );
}
