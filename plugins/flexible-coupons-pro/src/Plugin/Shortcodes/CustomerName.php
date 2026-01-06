<?php
/**
 * Shortcode. Customer name.
 *
 * @package WPDesk\FlexibleCouponsPro
 */

namespace WPDesk\FlexibleCouponsPro\Shortcodes;

use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\ShortcodeData;
use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\Shortcode;
use WC_Order;

/**
 * Customer name shortcode declaration.
 *
 * @package WPDesk\FlexibleCouponsPro\Shortcodes
 */
class CustomerName implements Shortcode {

	const ID = 'customer_name';

	/**
	 * @return string
	 */
	public function get_id() {
		return self::ID;
	}

	/**
	 * @return array
	 */
	public function definition() {
		return [
			'text'   => '[customer_name]',
			'top'    => 240,
			'left'   => 300,
			'width'  => 200,
			'height' => 30,
		];
	}

	/**
	 * @param ShortcodeData $shortcode_data
	 *
	 * @return string
	 */
	public function get_value( ShortcodeData $shortcode_data ) {
		$order         = $shortcode_data->get_order();
		$customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

		/**
		 * Allow change customer name.
		 *
		 * @param string   $customer_name Customer name.
		 * @param WC_Order $order         Order object.
		 *
		 * @since 1.1.0
		 */
		return apply_filters( 'fcpdf_shortcode_customer_name', $customer_name, $order );
	}
}
