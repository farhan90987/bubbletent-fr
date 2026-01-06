<?php

namespace WPDesk\FCS\Product;

use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\PostMeta;

/**
 * Abstract class for saving delay settings.
 */
class ProductSettingsStorage {

	public const DELAY_TYPE        = 'fc_sending_delay_type';
	public const DELAY_VALUE       = 'fc_sending_delay_value';
	public const DELAY_INTERVAL    = 'fc_sending_delay_interval';
	public const DELAY_FIXED_DATE  = 'fc_sending_delay_fixed_date';
	public const EMAIL_TEMPLATE_ID = 'fc_product_email_template_id';

	/**
	 * @var PostMeta
	 */
	protected $post_meta;


	public function __construct( PostMeta $post_meta ) {
		$this->post_meta = $post_meta;
	}

	/**
	 * @param int $product_id (product_id or variation id)
	 */
	public function save_product_settings( int $product_id, string $delay_type, string $delay_value, string $delay_interval, string $delay_fixed_date, string $email_template_id ): void {

		if ( 'customer_date_delay' === $delay_type ) {
			$this->post_meta->update_private( $product_id, ProductFieldsDefinition::CUSTOMER_DELAY_DATE_FIELD, 'yes' );
		} else {
			$this->post_meta->update_private( $product_id, ProductFieldsDefinition::CUSTOMER_DELAY_DATE_FIELD, 'no' );
		}

		$this->post_meta->update_private( $product_id, self::DELAY_TYPE, $delay_type );
		$this->post_meta->update_private( $product_id, self::DELAY_VALUE, $delay_value );
		$this->post_meta->update_private( $product_id, self::DELAY_INTERVAL, $delay_interval );
		$this->post_meta->update_private( $product_id, self::DELAY_FIXED_DATE, $delay_fixed_date );
		$this->post_meta->update_private( $product_id, self::EMAIL_TEMPLATE_ID, $email_template_id );
	}

	/**
	 * @param int $product_id
	 * @param int $variation_id
	 *
	 * @return array<string, string>
	 */
	public function get_product_delay_settings( int $product_id, int $variation_id ): array {
		if ( $variation_id !== 0 && $this->has_delay_settings( $variation_id ) ) {
			$product_id = $variation_id;
		}

		$delay_type       = $this->post_meta->get_private( $product_id, self::DELAY_TYPE, 'disabled' );
		$delay_value      = $this->post_meta->get_private( $product_id, self::DELAY_VALUE, '' );
		$delay_interval   = $this->post_meta->get_private( $product_id, self::DELAY_INTERVAL, '' );
		$delay_fixed_date = $this->post_meta->get_private( $product_id, self::DELAY_FIXED_DATE, '' );

		return [
			self::DELAY_TYPE       => $delay_type,
			self::DELAY_VALUE      => $delay_value,
			self::DELAY_INTERVAL   => $delay_interval,
			self::DELAY_FIXED_DATE => $delay_fixed_date,
		];
	}

	private function has_delay_settings( int $product_id ): bool {
		$delay_type = $this->post_meta->get_private( $product_id, self::DELAY_TYPE, '' );
		return ! in_array( $delay_type, [ '', '1' ], true );
	}
}
