<?php
/**
 * Shortcode. Coupon expiry date.
 *
 * @package WPDesk\FlexibleCouponsPro
 */

namespace WPDesk\FlexibleCouponsPro\Shortcodes;

use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\ShortcodeData;
use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\Shortcode;

/**
 * Coupon expiry date shortcode declaration.
 *
 * @package WPDesk\FlexibleCouponsPro\Shortcodes
 */
class CouponExpiryDate implements Shortcode {

	const ID = 'coupon_expiry_date';

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
			'text'   => '[coupon_expiry_date]',
			'top'    => 380,
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
		$coupon             = $shortcode_data->get_coupon();
		$expiry_date_format = get_option( 'flexible_coupons_expiry_date_format', get_option( 'date_format' ) );
		if ( empty( $expiry_date_format ) ) {
			$expiry_date_format = get_option( 'date_format', 'Y-m-d' );
		}

		/**
		 * Allow change date format for coupon.
		 *
		 * @param string $date_format Date format.
		 *
		 * @since 1.1.0
		 */
		if ( $coupon->get_date_expires() ) {
			return date_i18n( apply_filters( 'fcpdf_shortcode_coupon_expiry_date_format', $expiry_date_format, $coupon ), $coupon->get_date_expires()->getOffsetTimestamp() );
		}

		return '';
	}
}
