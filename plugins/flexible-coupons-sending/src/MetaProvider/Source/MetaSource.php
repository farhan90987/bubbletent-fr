<?php

namespace WPDesk\FCS\MetaProvider\Source;

use WC_Order_Item;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Product\ProductEditPage;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\PostMeta;

/**
 * Abstract class for meta source.
 */
abstract class MetaSource {

	/**
	 * @var PostMeta
	 */
	protected $post_meta;


	public function __construct( PostMeta $post_meta ) {
		$this->post_meta = $post_meta;
	}

	/**
	 * Gets meta.
	 */
	abstract public function get_meta( WC_Order_Item $item ): array;


	/**
	 * Checks if order item is a coupon product.
	 */
	protected function is_coupon_item( WC_Order_Item $item ): bool {
		return 'yes' === $this->post_meta->get_private( $item->get_product_id(), ProductEditPage::PRODUCT_COUPON_SLUG );
	}
}
