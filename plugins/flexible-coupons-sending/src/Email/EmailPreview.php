<?php

namespace WPDesk\FCS\Email;

use FCSVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\FCS\MetaProvider\MetaProvider;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Data\Email\EmailMeta;
use WC_Order;
use WC_Product;
use WC_Order_Item_Product;

class EmailPreview implements Hookable {

	public function hooks() {
		add_filter( 'woocommerce_prepare_email_for_preview', [ $this, 'prepare_email_for_preview' ] );
	}

	/**
	 * Prepare email for preview.
	 *
	 * @param \WC_Email $email Email object.
	 *
	 * @return \WC_Email
	 */
	public function prepare_email_for_preview( \WC_Email $email ): \WC_Email {
		if (
			! $email instanceof \WPDesk\FCS\Email\SendingBuyerEmail &&
			! $email instanceof \WPDesk\FCS\Email\SendingRecipientEmail
		) {
			return $email;
		}

		$meta = $this->get_dummy_meta( $email->object );
		$email->setup_preview( $meta );

		return $email;
	}

	private function get_dummy_meta( WC_Order $order ): EmailMeta {
		$items = $order->get_items();
		$item  = reset( $items );

		if ( ! $item instanceof WC_Order_Item_Product ) {
			// Let woocommerce handle the rest.
			throw new \Exception( 'Invalid order item.' );
		}

		$item_id       = $item->get_id();
		$product_id    = $item->get_product_id();
		$coupon_expiry = date( 'F j, Y', strtotime( '+1 month', current_time( 'timestamp' ) ) );
		$coupon_url    = admin_url( 'admin-ajax.php?action=download_coupon_p' );

		$coupon_meta_data = [
			'flexible_coupon_recipient_name'    => 'John Galt',
			'flexible_coupon_recipient_email'   => 'john.galt@mailinator.com',
			'flexible_coupon_recipient_message' => 'Happy Birthday! Hope you have a fantastic day!',
			'order_id'                          => $order->get_id(),
			'coupon_id'                         => 0,
			'coupon_code'                       => 'qwerty',
			'coupon_value'                      => '<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">&#36;</span>100.00</bdi></span>',
			'product_id'                        => $product_id,
			'variation_id'                      => 0,
			'item_id'                           => $item_id,
			'coupon_expiry'                     => $coupon_expiry,
			'coupon_url'                        => $coupon_url,
		];

		return new EmailMeta(
			array_merge(
				$coupon_meta_data,
				[
					'coupons' => [
						$coupon_meta_data,
					],
				]
			)
		);
	}
}
