<?php

namespace WPDesk\FCS\Schedule;

use FCSVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use FlexibleCouponsProVendor\Psr\Log\LoggerInterface;
use WC_Order_Factory;
use WC_Order_Item;
use WPDesk\FCS\MetaProvider\MetaProvider;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Exception\EmailException;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Data\Email\EmailMeta;

/**
 * Runs scheduled tasks.
 */
class ScheduleRunner implements Hookable {

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var MetaProvider
	 */
	private $meta_provider;


	public function __construct(
		LoggerInterface $logger,
		MetaProvider $meta_provider
	) {
		$this->logger        = $logger;
		$this->meta_provider = $meta_provider;
	}

	/**
	 * Hooks.
	 */
	public function hooks() {
		add_action( 'fc_sending_coupon_email', [ $this, 'send_email' ], 10, 3 );
	}

	/**
	 * Sends queued email.
	 *
	 * @param string $email_slug
	 * @param string $order_item_id
	 * @param array  $queue_meta those meta are accurate to the time when order was created.
	 */
	public function send_email( string $email_slug, int $order_item_id, array $queue_meta = [] ): void {

		try {
			$item = WC_Order_Factory::get_order_item( $order_item_id );
			if ( ! $item instanceof WC_Order_Item ) {
				throw new EmailException( 'Invalid order item.' );
			}

			// get most actual meta from database.
			$db_meta = $this->meta_provider->get_all_meta( $item );

			// make sure meta entry from the queue (if present) are used.
			$meta = \wp_parse_args(
				$queue_meta,
				$db_meta
			);

			$wc_registered_emails = \WC()->mailer()->get_emails();

			if ( ! isset( $wc_registered_emails[ $email_slug ] ) ) {
				throw new EmailException( 'Email class not found: ' . $email_slug );
			}

			$meta['coupons'][] = $this->create_coupon_meta( $meta );

			$email_meta = new EmailMeta( $meta );

			$email = $wc_registered_emails[ $email_slug ];
			$email->send_mail( $item->get_order_id(), $email_meta );

		} catch ( EmailException $e ) {
			$this->logger->warning(
				sprintf( 'Failed to send email. Reason: %s', $e->getMessage() ),
				[ 'meta' => $meta ]
			);

		}
	}

	private function create_coupon_meta( array $meta ): array {
		$coupon_meta['hash']          = $meta['hash'];
		$coupon_meta['coupon_id']     = $meta['coupon_id'];
		$coupon_meta['order_id']      = $meta['order_id'];
		$coupon_meta['coupon_code']   = $meta['coupon_code'];
		$coupon_meta['coupon_value']  = $meta['coupon_value'];
		$coupon_meta['coupon_expiry'] = $meta['coupon_expiry'];
		$coupon_meta['product_id']    = $meta['product_id'];
		$coupon_meta['variation_id']  = $meta['variation_id'];
		$coupon_meta['item_id']       = $meta['item_id'];
		$coupon_meta['coupon_url']    = $meta['coupon_url'];

		return $coupon_meta;
	}
}
