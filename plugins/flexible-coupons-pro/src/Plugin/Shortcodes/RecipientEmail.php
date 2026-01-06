<?php
/**
 * Shortcode. Recipient email.
 *
 * @package WPDesk\FlexibleCouponsPro
 */

namespace WPDesk\FlexibleCouponsPro\Shortcodes;

use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\ShortcodeData;
use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\Shortcode;

/**
 * Recipient email shortcode declaration.
 *
 * @package WPDesk\FlexibleCouponsPro\Shortcodes
 */
class RecipientEmail implements Shortcode {

	const ID = 'recipient_email';

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
			'text'   => '[recipient_email]',
			'top'    => 340,
			'left'   => 20,
			'width'  => 200,
			'height' => 40,
		];
	}

	/**
	 * @param ShortcodeData $shortcode_data
	 *
	 * @return string
	 */
	public function get_value( ShortcodeData $shortcode_data ) {
		$item_data = $shortcode_data->get_product_fields_values();

		return isset( $item_data['flexible_coupon_recipient_email'] ) ? $item_data['flexible_coupon_recipient_email'] : '';
	}
}
