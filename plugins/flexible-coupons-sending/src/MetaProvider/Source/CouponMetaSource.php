<?php

namespace WPDesk\FCS\MetaProvider\Source;

use WC_Coupon;
use WC_Order_Item;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\Helper;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\PostMeta;
use FlexibleCouponsProVendor\WPDesk\Persistence\Adapter\WordPress\WordpressOptionsContainer;

/**
 * Gets coupon meta.
 */
class CouponMetaSource extends MetaSource {

	/**
	 * @var WordpressOptionsContainer
	 */
	private $settings;

	public function __construct( PostMeta $post_meta, WordpressOptionsContainer $settings ) {
		parent::__construct( $post_meta );
		$this->settings = $settings;
	}

	/**
	 * Gets coupon meta.
	 */
	public function get_meta( WC_Order_Item $item ): array {
		$meta = [];
		if ( ! $this->is_coupon_item( $item ) ) {
			return $meta;
		}

		$coupon_id = $this->get_coupon_id( $item );
		// return if coupon is not generated.
		if ( ! $coupon_id ) {
			return $meta;
		}

		$coupon = new WC_Coupon( $coupon_id );

		$meta['hash']          = \wp_hash( $coupon->get_code(), 'nonce' );
		$meta['order_id']      = $item->get_order_id();
		$meta['coupon_id']     = $coupon->get_id();
		$meta['coupon_code']   = $coupon->get_code();
		$meta['coupon_value']  = \wc_price(
			$coupon->get_amount(),
			[
				'currency' => $item->get_order()->get_currency(),
			]
		);
		$meta['product_id']    = $item->get_product_id();
		$meta['variation_id']  = $item->get_variation_id();
		$meta['item_id']       = $item->get_id();
		$meta['coupon_expiry'] = $this->get_coupon_expiry( $coupon );
		$meta['coupon_url']    = Helper::make_coupon_url( $meta );

		return $meta;
	}

	/**
	 * Gets coupon id if coupon is generated.
	 */
	private function get_coupon_id( WC_Order_Item $item ): int {
		$order     = $item->get_order();
		$coupon_id = (int) $order->get_meta( 'fcpdf_order_item_' . $item->get_id() . '_coupon_id' );
		if ( ! $coupon_id ) {
			$coupon_id = (int) $order->get_meta( '_fcpdf_order_item_' . $item->get_id() . '_coupon_id' );
		}
		return $coupon_id;
	}

	/**
	 * Gets coupon expiry date.
	 */
	private function get_coupon_expiry( WC_Coupon $coupon ): string {
		$coupon_expiry = '';
		if ( $coupon->get_date_expires() ) {
			$wp_default_date_format = \get_option( 'date_format', 'Y-m-d' );
			$expiry_date_format     = $this->settings->get_fallback( 'expiry_date_format', $wp_default_date_format );
			$coupon_expiry          = $coupon->get_date_expires()->date_i18n( $expiry_date_format );
		}
		return $coupon_expiry;
	}
}
