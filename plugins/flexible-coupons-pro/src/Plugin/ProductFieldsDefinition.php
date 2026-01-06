<?php
/**
 * Product fields.
 *
 * @package WPDesk\FlexibleCouponsPro
 */

namespace WPDesk\FlexibleCouponsPro;

use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\ProductFields;

/**
 * Define pro fields for coupon product.
 *
 * @package WPDesk\FlexibleCouponsPro
 */
class ProductFieldsDefinition implements ProductFields {

	/**
	 * @return array
	 */
	public function get() {
		$fields = [
			'flexible_coupon_recipient_name'    => [
				'id'                => 'flexible_coupon_recipient_name',
				'type'              => 'text',
				'title'             => esc_html__( 'Recipient name', 'flexible-coupons-pro' ),
				'value'             => '',
				'required'          => false,
				'validation'        => [
					'minlength' => 2,
					'maxlength' => 100,
				],
				'can_disable'       => true,
				// translator: number of chars.
				'custom_attributes' => [
					'data-description' => esc_html__( 'The minimum characters number in this field is %d', 'flexible-coupons-pro' ),
				],
			],
			'flexible_coupon_recipient_email'   => [
				'id'                => 'flexible_coupon_recipient_email',
				'type'              => 'text',
				'title'             => esc_html__( 'Recipient email', 'flexible-coupons-pro' ),
				'value'             => '',
				'required'          => false,
				'validation'        => [
					'email'     => true,
					'minlength' => 5,
					'maxlength' => 120,
				],
				'can_disable'       => true,
				'custom_attributes' => [
					'data-description' => esc_html__( 'Make sure you use @ sign and domain extention like .com', 'flexible-coupons-pro' ),
				],
			],
			'flexible_coupon_recipient_message' => [
				'id'                => 'flexible_coupon_recipient_message',
				'type'              => 'textarea',
				'title'             => esc_html__( 'Recipient message', 'flexible-coupons-pro' ),
				'value'             => '',
				'required'          => false,
				'validation'        => [
					'minlength' => 5,
					'maxlength' => 250,
				],
				'can_disable'       => true,
				// translator: number of chars.
				'custom_attributes' => [
					'data-description' => esc_html__( 'The minimum characters number in this field is %d', 'flexible-coupons-pro' ),
				],
			],
		];

		/**
		 * Adds custom product fields.
		 *
		 * @param array $fields List of defined fields.
		 *
		 * @since 1.1.3
		 */
		return apply_filters( 'fc/pro/product/fields', $fields );
	}

	public function is_premium() {
		return true;
	}
}
