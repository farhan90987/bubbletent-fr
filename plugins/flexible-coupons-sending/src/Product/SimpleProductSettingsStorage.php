<?php

namespace WPDesk\FCS\Product;

use FCSVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Product\SaveProductSimpleData;

/**
 * Saves delay settings.
 */
class SimpleProductSettingsStorage extends ProductSettingsStorage implements Hookable {

	public function hooks() {
		add_action( 'fc/core/product/simple/save', [ $this, 'simple_save' ], 10, 2 );
	}

	public function simple_save( int $product_id, SaveProductSimpleData $simple_product_data ): void {

		$delay_type        = $simple_product_data->post_data( self::DELAY_TYPE, 'disabled' );
		$delay_value       = $simple_product_data->post_data( self::DELAY_VALUE, '' );
		$delay_interval    = $simple_product_data->post_data( self::DELAY_INTERVAL, '' );
		$delay_fixed_date  = $simple_product_data->post_data( self::DELAY_FIXED_DATE, '' );
		$email_template_id = $simple_product_data->post_data( self::EMAIL_TEMPLATE_ID, '' );

		$this->save_product_settings( $product_id, $delay_type, $delay_value, $delay_interval, $delay_fixed_date, $email_template_id );
	}
}
