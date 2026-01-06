<?php

namespace WPDesk\FCS\Product;

use FCSVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Product\SaveProductVariationData;

/**
 * Saves delay settings.
 */
class VariableProductSettingsStorage extends ProductSettingsStorage implements Hookable {

	public function hooks() {
		add_action( 'fc/core/product/variation/save', [ $this, 'variation_save' ], 10, 3 );
	}

	public function variation_save( int $variation_id, int $order_id, SaveProductVariationData $product_variation_data ): void {

		$delay_type        = $product_variation_data->post_data( self::DELAY_TYPE . '_variation', $order_id, 'disabled' );
		$delay_value       = $product_variation_data->post_data( self::DELAY_VALUE . '_variation', $order_id, '' );
		$delay_interval    = $product_variation_data->post_data( self::DELAY_INTERVAL . '_variation', $order_id, '' );
		$delay_fixed_date  = $product_variation_data->post_data( self::DELAY_FIXED_DATE . '_variation', $order_id, '' );
		$email_template_id = $product_variation_data->post_data( self::EMAIL_TEMPLATE_ID . '_variation', $order_id, '' );

		$this->save_product_settings( $variation_id, $delay_type, $delay_value, $delay_interval, $delay_fixed_date, $email_template_id );
	}
}
