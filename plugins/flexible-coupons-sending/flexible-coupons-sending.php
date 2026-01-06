<?php
/**
 * Plugin Name: Flexible PDF Coupons PRO - Advanced Sending
 * Plugin URI: https://wpde.sk/flexible-coupons-buy-advanced-sending
 * Description: Send coupons by email and schedule gift card delivery for any day of one's choice.
 * Version: 2.0.3
 * Author: WP Desk
 * Author URI: https://www.wpdesk.net/
 * Text Domain: flexible-coupons-sending
 * Domain Path: /lang/
 * Requires at least: 6.4
 * Tested up to: 6.8
 * WC requires at least: 9.8
 * WC tested up to: 10.2
 * Requires PHP: 7.4
 * Copyright 2022 WP Desk Ltd.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

use WPDesk\FCS\Plugin;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/vendor/autoload.php';

/* THESE TWO VARIABLES CAN BE CHANGED AUTOMATICALLY */
$plugin_version = '2.0.3';

$plugin_name        = 'Flexible PDF Coupons PRO - Advanced Sending';
$plugin_class_name  = Plugin::class;
$plugin_text_domain = 'flexible-coupons-sending';
$product_id         = 'Flexible PDF Coupons PRO - Advanced Sending';
$plugin_file        = __FILE__;
$plugin_dir         = __DIR__;

$plugin_shops = [
	'default' => 'https://www.wpdesk.net/',
];

$requirements = [
	'php'          => '7.4',
	'wp'           => '5.7',
	'repo_plugins' => [
		[
			'name'      => 'woocommerce/woocommerce.php',
			'nice_name' => 'WooCommerce',
			'version'   => '6.6',
		],
	],
	'plugins'      => [
		[
			'name'      => 'flexible-coupons-pro/flexible-coupons-pro.php',
			'nice_name' => 'Flexible Coupons PRO',
			'version'   => '2.3',
		],
	],
];
// to do switch to paid version.
require __DIR__ . '/vendor_prefixed/wpdesk/wp-plugin-flow-common/src/plugin-init-php52.php';
require __DIR__ . '/vendor/woocommerce/action-scheduler/action-scheduler.php';
