<?php
/**
 * Shortcode. Customer address.
 *
 * @package WPDesk\FlexibleCouponsPro
 */

namespace WPDesk\FlexibleCouponsPro\Shortcodes;

use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\ShortcodeData;
use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\Shortcode;
use WC_Order;

/**
 * Customer address shortcode declaration.
 *
 * @package WPDesk\FlexibleCouponsPro\Shortcodes
 */
class CustomerAddress implements Shortcode {

	const ID = 'customer_address';

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
			'text'   => '[customer_address]',
			'top'    => 20,
			'left'   => 300,
			'width'  => 200,
			'height' => 200,
		];
	}

	/**
	 * @param ShortcodeData $shortcode_data
	 *
	 * @return string
	 */
	public function get_value( ShortcodeData $shortcode_data ) {
		$order            = $shortcode_data->get_order();
		$customer_address = $order->get_billing_address_1() . ' ' . $order->get_billing_address_2() . ', ' . $order->get_billing_city();

		/**
		 * Allow change customer address.
		 *
		 * @param string   $customer_address Customer address.
		 * @param WC_Order $order            Order object.
		 *
		 * @since 1.1.0
		 */
		return apply_filters( 'fcpdf_shortcode_customer_address', $customer_address, $order );
	}
}
