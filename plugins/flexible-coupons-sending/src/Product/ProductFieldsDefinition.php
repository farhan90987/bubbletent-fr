<?php

namespace WPDesk\FCS\Product;

use FCSVendor\WPDesk\PluginBuilder\Plugin\Hookable;

/**
 * Adds customer delay date field to product fields definition.
 */
class ProductFieldsDefinition implements Hookable {

	public const CUSTOMER_DELAY_DATE_FIELD = 'fc_sending_customer_delay_date';

	/**
	 * Hooks.
	 */
	public function hooks() {
		add_filter( 'fc/pro/product/fields', [ $this, 'add_sending_fields' ] );
	}

	public function add_sending_fields( array $fields ): array {

		$fields[ self::CUSTOMER_DELAY_DATE_FIELD ] = [
			'id'                => self::CUSTOMER_DELAY_DATE_FIELD,
			'type'              => 'datetime-local',
			'title'             => esc_html__( 'Send coupon to recipient on', 'flexible-coupons-sending' ),
			'value'             => '',
			'required'          => false,
			'validation'        => [
				'date'      => true,
				'past_date' => true,
			],
			'can_disable'       => false,
		];

		return $fields;
	}
}
