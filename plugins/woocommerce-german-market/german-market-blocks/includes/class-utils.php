<?php

defined( 'ABSPATH' ) || exit;

class German_Market_Blocks_Utils extends German_Market_Blocks_Methods {
	
	public function init() {

	}

	/**
	 * Does the checkout page uses at least one of the German Market blocks
	 * 
	 * @return bool
	 */
	public static function has_checkout_page_a_german_market_block() {
		return self::has_checkout_page_german_market_checkboxes_block();
	}

	/**
	 * Does the checkout page uses the "EU VAT ID" block
	 * 
	 * @return bool
	 */
	public static function has_checkout_page_eu_vat_id_block() {

		return self::has_checkout_page_german_market_inner_block(

			'german-market/eu-vat-id',

			array(
				'woocommerce/checkout-fields-block',
				'woocommerce/checkout-totals-block'
			)
		);
	}

	/**
	 * Does the checkout page uses the "EU VAT ID" block
	 *
	 * @return bool
	 */
	public static function has_checkout_page_shipping_block() {

		return self::has_checkout_page_german_market_inner_block(

			'german-market/woocommerce-shipping',

			array(
				'woocommerce/checkout-fields-block',
			)
		);
	}

	/**
	 * Does the checkout page uses the "German Market Checkboxes" block
	 * 
	 * @return bool
	 */
	public static function has_checkout_page_german_market_checkboxes_block() {
		
		return self::has_checkout_page_german_market_inner_block(

			'german-market/checkout-checkboxes',

			array(
				'woocommerce/checkout-fields-block',
				'woocommerce/checkout-totals-block'
			)

		);
	}

	/**
	 * Does the checkout page uses a special German Market block within allowd innerBlocks
	 * 
	 * Todo: use a elegant recoursive function
	 * 
	 * @param String $german_market_block
	 * @param Array $allowed_inner_blocks
	 * 
	 * @return bool
	 */
	public static function has_checkout_page_german_market_inner_block( $german_market_block, $allowed_inner_blocks = array() ) {

		$has_gm_block = false;
		$page_id = wc_get_page_id( 'checkout' );

		if ( $page_id > 0 ) {
			$page_to_check = get_post( $page_id );
			if ( is_object( $page_to_check ) && isset( $page_to_check->post_content ) ) {
				$blocks = parse_blocks( $page_to_check->post_content );

				foreach ( $blocks as $block ) {
					if ( isset( $block[ 'blockName' ] ) && 'woocommerce/checkout' === $block[ 'blockName' ] ) {
						if ( isset( $block[ 'innerBlocks' ] ) ) {
							foreach ( $block[ 'innerBlocks' ] as $innerBlock ) {
								if ( isset( $innerBlock[ 'blockName' ] ) && in_array( $innerBlock[ 'blockName' ], $allowed_inner_blocks ) ) {	
									if ( isset( $innerBlock[ 'innerBlocks' ] ) ) {
										foreach ( $innerBlock[ 'innerBlocks' ] as $innerBlock2 ) {
											if ( $german_market_block === $innerBlock2[ 'blockName' ] ) {
												return true;
											}
										}
									}
								}	
							}
						}
					}
				}
			}
		}

		return $has_gm_block;
	}
}
