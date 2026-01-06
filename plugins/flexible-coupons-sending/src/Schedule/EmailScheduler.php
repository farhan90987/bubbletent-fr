<?php

namespace WPDesk\FCS\Schedule;

use FlexibleCouponsProVendor\Psr\Log\LoggerInterface;
use WPDesk\FCS\Email\SendingEmail;
use WPDesk\FCS\Exception\MetaException;
use WPDesk\FCS\Exception\DelayException;
use FCSVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Exception\EmailException;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Email\FlexibleCouponsBaseEmail;
use WPDesk\FCS\Product\ProductSettingsStorage;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Data\Email\EmailMeta;
use WC_DateTime;

/**
 * Schedules email sending.
 */
class EmailScheduler implements Hookable {

	/**
	 * Action Scheduler can group actions by name, lets use it.
	 */
	const ACTION_SCHEDULE_GROUP = 'coupon-sending';

	/**
	 * @var DelayCalculator
	 */
	private $delay_calculator;

	/**
	 * @var ProductSettingsStorage
	 */
	private $product_settings_storage;

	/**
	 * @var LoggerInterface
	 */
	private $logger;


	public function __construct( DelayCalculator $delay_calculator, ProductSettingsStorage $product_settings_storage, LoggerInterface $logger ) {
		$this->delay_calculator         = $delay_calculator;
		$this->product_settings_storage = $product_settings_storage;
		$this->logger                   = $logger;
	}

	/**
	 * Hooks.
	 */
	public function hooks() {
		add_filter( 'fc/core/email/should_send_email', [ $this, 'schedule_email_sending' ], 10, 5 );
	}

	/**
	 * Adds email to queue if delay date is set.
	 *
	 * @return bool False means, we are not going to send email now.
	 */
	public function schedule_email_sending( bool $should_send_email, FlexibleCouponsBaseEmail $email, int $order_id, array $meta, EmailMeta $email_meta ): bool {

		if ( ! $email instanceof SendingEmail ) {
			return $should_send_email;
		}

		try {
			$delay_settings      = $this->product_settings_storage->get_product_delay_settings(
				$email_meta->get_product_id(),
				$email_meta->get_variation_id()
			);
			$customer_delay_date = $email_meta->get_delay_date();
			$order_date_created  = $this->get_order_date_created( $order_id, $delay_settings );
			$delay_timestamp     = $this->delay_calculator->get_delay_timestamp( $delay_settings, $customer_delay_date, $order_date_created );
			if ( ! $delay_timestamp ) {
				return $should_send_email;
			}

			$args = [
				'email'   => $email->get_slug(),
				'item_id' => $email_meta->get_item_id(),
			];

			$action_id = \as_schedule_single_action( $delay_timestamp, 'fc_sending_coupon_email', $args, self::ACTION_SCHEDULE_GROUP );
			if ( ! $action_id ) {
				throw new DelayException( 'Action Scheduler fail to schedule.' );
			}
		} catch ( DelayException $e ) {
			$this->logger->error(
				sprintf(
					'Could not schedule email. Reason: %s',
					$e->getMessage()
				)
			);

			return $should_send_email;

		} catch ( MetaException $e ) {
			throw new EmailException( 'Invalid meta: ' . $e->getMessage() );
		}

		return false;
	}

	private function get_order_date_created( int $order_id, array $delay_settings ): WC_DateTime {
		$order = \wc_get_order( $order_id );
		if ( ! $order instanceof \WC_Order ) {
			throw new EmailException( 'Order does not exist ' . $order_id );
		}

		$date_created = $order->get_date_created();
		if (
			! $date_created instanceof WC_DateTime &&
			'simple_delay' === $delay_settings[ ProductSettingsStorage::DELAY_TYPE ]
		) {
			throw new DelayException( 'Order date created does not exist ' . $order_id );
		}

		return $date_created;
	}
}
