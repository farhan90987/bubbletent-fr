<?php
/**
 * Plugin Name: SmoobuSyncWP
 * Description: Effortlessly sync Smoobu property availability with your WordPress site. Seamlessly register bookings via WooCommerce's single-page checkout.
 * Version:     1.2.2
 * Author:      Sagacitas Technologies Private Limited
 * Text Domain: smoobu-calendar
 * Domain Path: /languages
 *
 * @package smoobu-calendar
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No skiddies please!' );
}

// constants.
require_once plugin_dir_path( __FILE__ ) . 'constants.php';

// activation hooks.
require_once SMOOBU_PATH . 'activation.php';
register_activation_hook( __FILE__, 'smoobu_activation' );

// main class.
require_once SMOOBU_PATH . 'classes/class-smoobu-main.php';

// API related.
require_once SMOOBU_PATH . 'classes/api/class-smoobu-api.php';
require_once SMOOBU_PATH . 'classes/api/class-smoobu-api-properties.php';
require_once SMOOBU_PATH . 'classes/api/class-smoobu-api-availability.php';
require_once SMOOBU_PATH . 'classes/api/class-smoobu-api-user.php';
require_once SMOOBU_PATH . 'classes/api/class-smoobu-api-check-availability.php';
require_once SMOOBU_PATH . 'classes/api/class-smoobu-webhook.php';
require_once SMOOBU_PATH . 'classes/api/class-smoobu-api-booking.php';

// shortcodes.
require_once SMOOBU_PATH . 'classes/shortcodes/class-smoobu-calendar-shortcode.php';

// widgets.
require_once SMOOBU_PATH . 'classes/widgets/class-smoobu-calendar-widget.php';

// blocks.
require_once SMOOBU_PATH . 'classes/class-smoobu-blocks.php';

// everything else.
require_once SMOOBU_PATH . 'classes/class-smoobu-calendar.php';
require_once SMOOBU_PATH . 'classes/class-smoobu-settings.php';
require_once SMOOBU_PATH . 'classes/class-smoobu-utility.php';
require_once SMOOBU_PATH . 'classes/class-smoobu-ajax.php';

/**
 * Initialize main plugin class
 *
 * @return Smoobu_Main
 */
function smoobu_main_init() {
	if ( ! class_exists( 'Smoobu_Main' ) ) {
		return;
	}

	return Smoobu_Main::instance();
}

smoobu_main_init();
