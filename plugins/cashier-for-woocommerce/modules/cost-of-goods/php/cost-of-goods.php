<?php
/**
 * Cost of Goods Starter file
 *
 * @package cashier/modules/cost-of-goods/php/
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! defined( 'SA_COG_PLUGIN_FILE' ) ) {
	define( 'SA_COG_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'SA_COG_PLUGIN_DIRNAME' ) ) {
	define( 'SA_COG_PLUGIN_DIRNAME', dirname( plugin_basename( __FILE__ ) ) );
}

if ( ! defined( 'SA_COG_PLUGIN_DIRPATH' ) ) {
	define( 'SA_COG_PLUGIN_DIRPATH', dirname( __FILE__ ) );
}


// Include Cost Of Goods Main Class file.
require_once 'includes/class-sa-cfw-cost-of-goods.php';

if ( ! function_exists( 'sa_cfw_cog' ) ) {

	/**
	 * Function to get instance of SA_CFW_Cost_Of_Goods.
	 *
	 * @return SA_CFW_Cost_Of_Goods instance
	 */
	function sa_cfw_cog() {
		return SA_CFW_Cost_Of_Goods::get_instance();
	}
}
