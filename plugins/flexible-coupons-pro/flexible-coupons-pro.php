<?php
/**
 * Plugin Name: Flexible PDF Coupons PRO for WooCommerce
 * Plugin URI: https://www.wpdesk.net/products/flexible-coupons-woocommerce/
 * Description: Flexible PDF Coupons for WooCommerce is a WooCommerce plugin with which you can create your gift cards, vouchers, or coupons in PDF format. Use it for your future marketing campaigns.
 * Product: Flexible PDF Coupons PRO for WooCommerce
 * Version: 2.4.2
 * Author: WP Desk
 * Author URI: https://www.wpdesk.net/
 * Text Domain: flexible-coupons-pro
 * Domain Path: /lang/
 * Requires at least: 6.4
 * Tested up to: 6.8
 * WC requires at least: 9.8
 * WC tested up to: 10.2
 * Requires PHP: 7.4
 *
 * @package \WPDesk\FlexibleCouponsPro
 *
 * Copyright 2016 WP Desk Ltd.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/* Plugins version */
$plugin_version = '2.4.2';
/* Plugin release */
$plugin_release_timestamp = '2023-06-21 15:44';

$plugin_name        = 'Flexible PDF Coupons Pro for WooCommerce';
$product_id         = 'Flexible PDF Coupons Pro for WooCommerce';
$plugin_class_name  = '\WPDesk\FlexibleCouponsPro\Plugin';
$plugin_text_domain = 'flexible-coupons-pro';
$plugin_file        = __FILE__;
$plugin_dir         = __DIR__;

$plugin_shops = [
	'pl_PL'   => 'https://www.wpdesk.pl/',
	'default' => 'https://www.wpdesk.net/',
];

$requirements = [
	'php'     => '7.4',
	'wp'      => '5.0',
	'plugins' => [
		[
			'name'      => 'woocommerce/woocommerce.php',
			'nice_name' => 'WooCommerce',
			'version'   => '4.0',
		],
	],
];

require __DIR__ . '/vendor_prefixed/wpdesk/wp-plugin-flow-common/src/plugin-init-php52.php';

// Disable free version.
if ( \PHP_VERSION_ID > 50300 ) {
	require_once __DIR__ . '/src/PluginDisabler/FlexibleCouponFreeDisabler.php';
	\WPDesk\FC\FreeDisabler\FlexibleCouponFreeDisabler::disable_free();
}
