<?php
/**
 * General settings.
 *
 * @package WPDesk\FlexibleCouponsPro
 */

namespace WPDesk\FlexibleCouponsPro\Settings;

use FlexibleCouponsProVendor\WPDesk\Forms\Field\ToggleField;
use FlexibleCouponsProVendor\WPDesk\Forms\Field\Header;
use FlexibleCouponsProVendor\WPDesk\Forms\Field\InputNumberField;
use FlexibleCouponsProVendor\WPDesk\Forms\Field\InputTextField;
use FlexibleCouponsProVendor\WPDesk\Forms\Field\Paragraph;
use FlexibleCouponsProVendor\WPDesk\Forms\Field\SelectField;
use FlexibleCouponsProVendor\WPDesk\PluginBuilder\Plugin\Hookable;

/**
 * Define pro fields for general settings.
 *
 * @package WPDesk\FlexibleCouponsPro
 */
class GeneralSettings implements Hookable {

	const EXPIRY_DATE_FORMAT_FIELD    = 'expiry_date_format';
	const ATTACH_COUPON_FIELD         = 'attach_coupon';
	const REGULAR_PRICE_FIELD         = 'coupon_regular_price';
	const SHOW_TIPS_FIELD             = 'coupon_tips';
	const SHOW_TEXTAREA_COUNTER_FIELD = 'coupon_textarea_counter';
	const PRODUCT_PAGE_POSITION_FIELD = 'coupon_product_position';
	const COUPON_CODE_PREFIX_FIELD    = 'coupon_code_prefix';
	const COUPON_CODE_SUFFIX_FIELD    = 'coupon_code_suffix';
	const COUPON_CODE_LENGTH_FIELD    = 'coupon_code_random_length';

	public function hooks() {
		add_filter( 'fcpdf/settings/general/fields', [ $this, 'add_pro_fields' ] );
	}

	/**
	 * @param array $fields
	 *
	 * @return array
	 */
	public function add_pro_fields( array $fields ): array {
		$submit_field = array_pop( $fields );
		$pro_fields   = [
			( new InputTextField() )
				->set_name( self::EXPIRY_DATE_FORMAT_FIELD )
				->set_label( esc_html__( 'Expiry date format', 'flexible-coupons-pro' ) )
				->set_description( sprintf( __( 'Define coupon expiry date format according to %1$sWordPress date formatting%2$s.', 'flexible-coupons-pro' ), '<a href="https://wordpress.org/support/article/formatting-date-and-time/" target="_blank">', '</a>' ) )
				->set_default_value( get_option( 'date_format' ) ),
			( new ToggleField() )
				->set_sublabel( esc_html__( 'Enable', 'flexible-coupons-pro' ) )
				->set_name( self::ATTACH_COUPON_FIELD )
				->set_label( esc_html__( 'PDF as attachment', 'flexible-coupons-pro' ) )
				->set_description( esc_html__( 'Enable to add PDF coupons as email attachments. If this option is disabled, recipients will only be able to download the coupon via a link in the email.', 'flexible-coupons-pro' ) ),
			( new SelectField() )
				->set_options(
					[
						'below' => esc_html__( 'Below Add to cart button', 'flexible-coupons-pro' ),
						'above' => esc_html__( 'Above Add to cart button', 'flexible-coupons-pro' ),
					]
				)
				->set_name( self::PRODUCT_PAGE_POSITION_FIELD )
				->set_label( esc_html__( 'Coupon fields position on the product page', 'flexible-coupons-pro' ) )
				->set_description(
					esc_html__(
						'Select where the coupon fields will be displayed on the product page.',
						'flexible-coupons-pro'
					)
				),
			( new Header() )
				->set_label( esc_html__( 'Coupon code', 'flexible-coupons-pro' ) )
				->set_description(
				// translators: %1$s start url, %2$s close url.
					sprintf( esc_html__( 'In this section you can define your own settings for coupon code.', 'flexible-coupons-pro' ) )
				),
			( new InputTextField() )
				->set_name( self::COUPON_CODE_PREFIX_FIELD )
				->set_label( esc_html__( 'Coupon code prefix', 'flexible-coupons-pro' ) )
				->set_description( __( 'Define the prefix which will be used as a beginning of your coupon code. Leave empty if you don’t want to use the prefix. Use <code>{order_id}</code> shortcode if you want to use the order number.', 'flexible-coupons-pro' ) ),
			( new InputNumberField() ) //@phpstan-ignore-line
				->set_attribute( 'min', '5' )
				->set_attribute( 'max', '30' )
				->set_default_value( 5 )
				->set_name( self::COUPON_CODE_LENGTH_FIELD )
				->set_label( esc_html__( 'Number of random characters', 'flexible-coupons-pro' ) )
				->set_description(
					esc_html__(
						'The number of random characters in the coupon code. Random characters will be used for generating unique coupon codes. Choose the number between 5 and 30.',
						'flexible-coupons-pro'
					)
				),
			( new InputTextField() )
				->set_name( self::COUPON_CODE_SUFFIX_FIELD )
				->set_label( esc_html__( 'Coupon code suffix', 'flexible-coupons-pro' ) )
				->set_description(
					__(
						'Define the suffix which will be used as a end of your coupon code. Leave empty if you don’t want to use the suffix. Use <code>{order_id}</code> shortcode if you want to use the order number.',
						'flexible-coupons-pro'
					)
				),
			( new ToggleField() )
				->set_sublabel( esc_html__( 'Enable', 'flexible-coupons-pro' ) )
				->set_name( self::REGULAR_PRICE_FIELD )
				->set_label( esc_html__( 'Coupon value', 'flexible-coupons-pro' ) )
				->set_description(
					esc_html__(
						'Always use the regular price of the product for the coupon value.',
						'flexible-coupons-pro'
					)
				),
			( new ToggleField() )
				->set_sublabel( esc_html__( 'Enable', 'flexible-coupons-pro' ) )
				->set_name( self::SHOW_TIPS_FIELD )
				->set_label( esc_html__( 'Show field tips', 'flexible-coupons-pro' ) )
				->set_description( esc_html__( 'Show tooltips for fields.', 'flexible-coupons-pro' ) ),
			( new ToggleField() )
				->set_sublabel( esc_html__( 'Enable', 'flexible-coupons-pro' ) )
				->set_name( self::SHOW_TEXTAREA_COUNTER_FIELD )
				->set_label( esc_html__( 'Show textarea counter', 'flexible-coupons-pro' ) )
				->set_description( esc_html__( 'Show character counter below textarea.', 'flexible-coupons-pro' ) ),
			( new Header() )
				->set_name( 'php-settings' )
				->set_label( esc_html__( 'PHP Settings', 'flexible-coupons-pro' ) ),
			( new Paragraph() )
				->set_name( 'php-alow' )
				->set_label( esc_html__( 'Allow URL Fopen', 'flexible-coupons-pro' ) )
				->set_description( $this->get_php_settings_message( 'allow_url_fopen', true ) ),
		];

		return array_merge( $fields, $pro_fields, [ $submit_field ] );
	}

	/**
	 * @param string $option_name
	 *
	 * @return string|bool
	 */
	private function get_php_option( string $option_name ) {
		return ini_get( $option_name );
	}

	private function get_php_settings_message( string $option_key, $expected_value ) {
		if ( (bool) $this->get_php_option( $option_key ) !== $expected_value ) {
			return '<span style="color: red; font-weight: 700;">' . esc_html__( 'Disabled', 'flexible-coupons-pro' ) . '</span>';
		}

		return '<span style="color: green; font-weight: 700;">' . esc_html__( 'Enabled', 'flexible-coupons-pro' ) . '</span>';
	}
}
