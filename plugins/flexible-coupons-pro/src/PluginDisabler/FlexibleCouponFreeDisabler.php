<?php
/**
 *
 * Coupons Free plugin disabler.
 *
 * @package WPDesk\FreeDisabler
 */

namespace WPDesk\FC\FreeDisabler;

use FlexibleCouponsProVendor\WPDesk\Notice\Notice;

/**
 * Can disable free plugin.
 *
 * @package WPDesk\FreeDisabler
 */
class FlexibleCouponFreeDisabler {

	/**
	 * Disable Flexible Coupons free.
	 */
	public static function disable_free() {
		add_action(
			'wp_builder_plugin_class',
			static function ( $class ) {
				if ( is_a( $class, \WPDesk\FlexibleCoupons\Plugin::class, true )
				) {
					require_once __DIR__ . '/NullPlugin.php';
					self::show_notice();

					return NullPlugin::class;
				}

				return $class;
			}
		);
	}

	/**
	 * Ensure notice that Free is disabled.
	 */
	public static function show_notice() {
		add_action(
			'init',
			static function () {
				if ( class_exists( Notice::class ) ) {
					$action = 'deactivate';
					$plugin = 'flexible-coupons/flexible-coupons.php';
					$url    = sprintf( admin_url( 'plugins.php?action=' . $action . '&plugin=%s&plugin_status=all&paged=1&s' ), $plugin );
					$url    = wp_nonce_url( $url, $action . '-plugin_' . $plugin );
					new Notice(
						sprintf(
						// Translators: link.
							__( '"Flexible Coupons PDF for WooCommerce" plugin can be removed now since the PRO version took over its functionalities.%1$s%2$sClick here%3$s to deactivate "Flexible Coupons PDF for WooCommerce" plugin.', 'flexible-coupons-pro' ),
							'<br/>',
							'<a href="' . $url . '">',
							'</a>'
						)
					);
				}
			}
		);
	}
}
