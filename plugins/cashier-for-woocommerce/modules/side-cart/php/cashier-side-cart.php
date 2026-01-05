<?php
/**
 * Side Cart Starter file
 *
 * @package cashier/modules/side-cart/php/
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! defined( 'SA_SC_PLUGIN_FILE' ) ) {
	define( 'SA_SC_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'SA_SC_PLUGIN_DIRNAME' ) ) {
	define( 'SA_SC_PLUGIN_DIRNAME', dirname( plugin_basename( __FILE__ ) ) );
}

if ( ! defined( 'SA_SC_PLUGIN_DIRPATH' ) ) {
	define( 'SA_SC_PLUGIN_DIRPATH', dirname( __FILE__ ) );
}

require_once 'includes/class-sa-cfw-side-cart.php';
