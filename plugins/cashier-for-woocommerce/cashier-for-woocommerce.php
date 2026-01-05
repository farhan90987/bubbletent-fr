<?php
/**
 * Plugin Name:          Cashier for WooCommerce
 * Plugin URI:           https://woocommerce.com/products/cashier/
 * Description:          Cashier for WooCommerce optimizes the checkout process
 *                       to nudge customers at every stage in the checkout
 *                       funnel - cart page, checkout page.
 * Version:              1.8.0
 * Author:               StoreApps
 * Author URI:           http://www.storeapps.org/
 * Developer:            StoreApps
 * Developer URI:        https://www.storeapps.org/
 * Requires at least:    4.9.0
 * Tested up to:         5.8.2
 * WC requires at least: 3.7.0
 * WC tested up to:      6.0.0
 * Text Domain:          cashier
 * Domain Path:          /languages/
 * Woo:                  6237396:027792cd460af1451333552190e1637e
 * License:              GNU General Public License v3.0
 * License URI:          http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright (c) 2020-2021 WooCommerce, StoreApps All rights reserved.
 *
 * @package cashier
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

register_activation_hook(
	__FILE__,
	function () {
		set_transient( 'sa_cfw_activated', array(), ( 5 * MINUTE_IN_SECONDS ) );
	}
);

/**
 * Initialize Cashier for WooCommerce
 */
function initialize_cashier_for_woocommerce() {

	$active_plugins = (array) get_option( 'active_plugins', array() );
	if ( is_multisite() ) {
		$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
	}

	if ( ! defined( 'SA_CFW_PLUGIN_FILE' ) ) {
		define( 'SA_CFW_PLUGIN_FILE', __FILE__ );
	}
	if ( ! defined( 'SA_CFW_PLUGIN_DIRNAME' ) ) {
		define( 'SA_CFW_PLUGIN_DIRNAME', dirname( plugin_basename( __FILE__ ) ) );
	}

	if ( ( in_array( 'woocommerce/woocommerce.php', $active_plugins, true ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins ) ) ) {
		include_once 'includes/class-sa-wc-cashier.php';
		$GLOBALS['sa_wc_cashier'] = SA_WC_Cashier::get_instance();
	} else {
		if ( is_admin() ) {
			?>
			<div class="notice notice-error">
				<p><?php echo esc_html__( 'Cashier for WooCommerce requires WooCommerce to be activated.', 'cashier' ); ?></p>
			</div>
			<?php
		}
	}

}
add_action( 'plugins_loaded', 'initialize_cashier_for_woocommerce' );
