<?php
/**
 * Buy Now Starter file
 *
 * @package cashier/modules/buy-now/php/
 * @version 1.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$active_plugins = (array) get_option( 'active_plugins', array() );

if ( is_multisite() ) {
	$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
}

if ( ! ( in_array( 'woocommerce/woocommerce.php', $active_plugins, true ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins ) ) ) {
	return;
}

/**
 * Initialize Buy Now
 */
if ( ! defined( 'SA_BN_PLUGIN_FILE' ) ) {
	define( 'SA_BN_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'SA_BN_PLUGIN_DIRNAME' ) ) {
	define( 'SA_BN_PLUGIN_DIRNAME', dirname( plugin_basename( __FILE__ ) ) );
}
if ( ! defined( 'SA_BN_PLUGIN_DIRPATH' ) ) {
	define( 'SA_BN_PLUGIN_DIRPATH', dirname( __FILE__ ) );
}

require_once 'class-wc-buy-now.php';

$GLOBALS['wc_buy_now'] = WC_Buy_Now::get_instance();

require_once 'admin/class-sa-wc-buy-now-product-admin-fields.php';

if ( in_array( 'woocommerce-gateway-authorize-net-cim/woocommerce-gateway-authorize-net-cim.php', $active_plugins, true ) || array_key_exists( 'woocommerce-gateway-authorize-net-cim/woocommerce-gateway-authorize-net-cim.php', $active_plugins ) ) {
	require_once 'gateways/class-sa-buy-now-wc-authorize-net-cim.php';
}

if ( in_array( 'woocommerce-gateway-paypal-powered-by-braintree/woocommerce-gateway-paypal-powered-by-braintree.php', $active_plugins, true ) || array_key_exists( 'woocommerce-gateway-paypal-powered-by-braintree/woocommerce-gateway-paypal-powered-by-braintree.php', $active_plugins ) ) {
	require_once 'gateways/class-sa-buy-now-wc-braintree.php';
}

if ( in_array( 'woocommerce-gateway-stripe/woocommerce-gateway-stripe.php', $active_plugins, true ) || array_key_exists( 'woocommerce-gateway-stripe/woocommerce-gateway-stripe.php', $active_plugins ) ) {
	require_once 'gateways/class-sa-buy-now-wc-stripe.php';
}

require_once 'gateways/paypal-standard/class-sa-buy-now-wc-paypal-handler.php';
