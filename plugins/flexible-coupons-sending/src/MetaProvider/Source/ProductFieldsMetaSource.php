<?php

namespace WPDesk\FCS\MetaProvider\Source;

use WC_Order_Item;
use WPDesk\FlexibleCouponsPro\ProductFieldsDefinition;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\PostMeta;

/**
 * Gets product fields meta.
 */
class ProductFieldsMetaSource extends MetaSource {

	/**
	 * @var ProductFields
	 */
	private $product_fields;


	public function __construct( PostMeta $post_meta, ProductFieldsDefinition $product_fields ) {
		parent::__construct( $post_meta );
		$this->product_fields = $product_fields;
	}

	/**
	 * Gets product fields meta.
	 */
	public function get_meta( WC_Order_Item $item ): array {
		$meta = [];
		if ( ! $this->is_coupon_item( $item ) ) {
			return $meta;
		}

		$fields = $this->product_fields->get();
		if ( empty( $fields ) || ! is_array( $fields ) ) {
			return $meta;
		}

		foreach ( $fields as $field_id => $field ) {
			$value             = \wc_get_order_item_meta( $item->get_id(), $field_id, true );
			$meta[ $field_id ] = $value;
		}

		return $meta;
	}
}
