<?php
/*
 * Add-on Name:	WooCommerce Shipping
 * Description:	Provides additional shipping methods and connections to provider APIs
 * Version:		1.0.0
 * Author:		MarketPress
 * Author URI:	https://marketpress.de
 * Licence:		GPLv3
 * Text Domain: woocommerce-german-market
 */

namespace MarketPress\GermanMarket\Shipping;

use Woocommerce_German_Market;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

/**
 * Currently plugin version.
 */
define( 'WGM_SHIPPING_VERSION', Woocommerce_German_Market::$version );

/**
 * Looking for Script Debug Constant
 */
define( 'WGM_SHIPPING_MINIFY', ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min' );

/**
 * Main Path
 */
define( 'WGM_SHIPPING_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

/**
 * Main Url
 */
define( 'WGM_SHIPPING_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

/**
 * Load composer autoload.
 */
require plugin_dir_path( __FILE__ ) . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes' . DIRECTORY_SEPARATOR . 'class-woocommerce-shipping.php';

/**
 * Begins execution of the addon.
 */
Woocommerce_Shipping::get_instance();
