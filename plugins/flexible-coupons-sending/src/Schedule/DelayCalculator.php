<?php

namespace WPDesk\FCS\Schedule;

use DateTime;
use WC_DateTime;
use DateTimeZone;
use WPDesk\FCS\Exception\DelayException;
use WPDesk\FCS\Product\ProductSettingsStorage;

/**
 * Schedules email sending.
 */
class DelayCalculator {

	const INTERVALS = [
		'minutes',
		'hours',
		'days',
		'weeks',
		'months',
		'years',
	];

	const INTERVAL_VALUES = [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 ];

	/**
	 * Get exact time (timestamp) for sending this email
	 *
	 * @return integer|false
	 */
	public function get_delay_timestamp( array $product_delay_settings, string $customer_delay_date, WC_DateTime $order_date_created ) {

		switch ( $product_delay_settings[ ProductSettingsStorage::DELAY_TYPE ] ) {
			case 'disabled':
				return false;
			case 'fixed_date_delay':
				return $this->get_fixed_date_timestamp( $product_delay_settings[ ProductSettingsStorage::DELAY_FIXED_DATE ] );
			case 'simple_delay':
				return $this->get_simple_delay_timestamp(
					$order_date_created,
					$product_delay_settings[ ProductSettingsStorage::DELAY_INTERVAL ],
					$product_delay_settings[ ProductSettingsStorage::DELAY_VALUE ]
				);
			case 'customer_date_delay':
				return $this->get_customer_delay_timestamp( $customer_delay_date );
			default:
				return false;
		}
	}

	private function get_fixed_date_timestamp( string $fixed_date ): int {
		$fixed_date = $this->get_utc_timestamp( $fixed_date );
		$this->validate_timestamp( $fixed_date );

		return $fixed_date;
	}

	private function get_simple_delay_timestamp( WC_DateTime $order_date_created, string $delay_interval, int $delay_value ): int {
		$simple_delay = $this->get_timestamp_from_interval( $order_date_created, $delay_interval, $delay_value );
		$this->validate_timestamp( $simple_delay );

		return $simple_delay;
	}

	private function get_customer_delay_timestamp( string $customer_delay_date ): int {
		$customer_delay_date = $this->get_utc_timestamp( $customer_delay_date );
		$this->validate_timestamp( $customer_delay_date );

		return $customer_delay_date;
	}


	private function get_timestamp( string $date ): int {
		$timestamp = \strtotime( $date );
		if ( ! $timestamp ) {
			throw new DelayException( 'Invalid date: ' . $date );
		}

		return $timestamp;
	}

	private function get_utc_timestamp( string $date ): int {
		$datetime = new DateTime( $date, \wp_timezone() );
		if ( ! $datetime->getTimestamp() ) {
			throw new DelayException( 'Invalid date: ' . $date );
		}
		// Convert to GMT timezone.
		$gmt_timezone = new DateTimeZone( '+0000' );
		$datetime->setTimezone( $gmt_timezone );

		return $datetime->getTimestamp();
	}


	private function validate_timestamp( int $timestamp ): void {
		$current_date_time = new DateTime( 'now', \wp_timezone() );
		// is it date from the future.
		if ( $timestamp < $current_date_time->getTimestamp() ) {
			throw new DelayException( 'Date is in the past: ' . $timestamp . ' (timestamp)' );
		}
	}


	private function get_timestamp_from_interval( WC_DateTime $order_date_created, string $delay_interval, int $delay_value ): int {

		if ( ! \in_array( $delay_interval, self::INTERVALS, true ) ) {
			throw new DelayException( 'Invalid delay interval: ' . $delay_interval );
		}

		if ( ! \in_array( $delay_value, self::INTERVAL_VALUES, true ) ) {
			throw new DelayException( 'Invalid delay value: ' . $delay_value );
		}

		$delay_in_seconds = $this->get_delay_in_seconds( $delay_interval, $delay_value );
		if ( $delay_in_seconds < 1 ) {
			throw new DelayException( 'Invalid delay in seconds: ' . $delay_in_seconds );
		}

		return $order_date_created->getTimestamp() + $delay_in_seconds;
	}


	private function get_delay_in_seconds( string $delay_interval, int $delay_value ): int {
		switch ( $delay_interval ) {
			case 'minutes':
				return $delay_value * MINUTE_IN_SECONDS;
			case 'hours':
				return $delay_value * HOUR_IN_SECONDS;
			case 'days':
				return $delay_value * DAY_IN_SECONDS;
			case 'weeks':
				return $delay_value * WEEK_IN_SECONDS;
			case 'months':
				return $delay_value * MONTH_IN_SECONDS;
			case 'years':
				return $delay_value * YEAR_IN_SECONDS;
			default:
				return 0;
		}
	}
}
