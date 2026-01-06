<?php
/**
 * Ajax actions
 *
 * @package smoobu-calendar
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No skiddies please!' );
}

/**
 * Ajax class
 */
final class Smoobu_Ajax {
	/**
	 * Class instance
	 *
	 * @var Smoobu_Ajax
	 */
	protected static $instance = null;

	/**
	 * Main instance
	 *
	 * @return Smoobu_Ajax
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class constructor, loads main actions, methods etc.
	 */
	public function __construct() {
		// resources & learning news.
		add_action( 'wp_ajax_check_api_connection', array( $this, 'check_api_connection' ) );
		add_action( 'wp_ajax_get_calendar_styling', array( $this, 'get_calendar_styling' ) );

		// Gets Average of the price from the given period.
		add_action( 'wp_ajax_get_average_price', array( $this, 'get_average_price' ) );
		add_action( 'wp_ajax_nopriv_get_average_price', array( $this, 'get_average_price' ) );
	}

	/**
	 * Check API connection
	 *
	 * @return void
	 */
	public static function check_api_connection() {
		check_ajax_referer( 'connection_check_nonce', 'security' );

		if ( isset( $_POST['api_key'] ) ) {
			$api_key = sanitize_text_field( wp_unslash( $_POST['api_key'] ) );
		}

		// get response.
		$api          = new Smoobu_Api();
		$check_result = $api->get_api_check( $api_key, SMOOBU_API_USER_ENDPOINT );

		if ( false === $check_result ) {
			$response = array(
				'status' => 'OK',
			);
		} else {
			$response = array(
				'status'  => 'ERROR',
				'message' => $check_result,
			);
		}

		echo wp_json_encode( $response );

		exit;
	}

	/**
	 * Get calendar custom styling settings
	 *
	 * @return void
	 */
	public static function get_calendar_styling() {
		check_ajax_referer( 'styling_nonce', 'security' );

		$styling = Smoobu_Utility::get_custom_theme_styling();

		// border related values.
		if ( isset( $_POST['smoobu_custom_styling_border_shadow'] ) ) {
			$styling['border_shadow'] = sanitize_text_field( wp_unslash( $_POST['smoobu_custom_styling_border_shadow'] ) );
		}

		if ( isset( $_POST['smoobu_custom_styling_border_radius'] ) ) {
			$styling['border_radius'] = sanitize_text_field( wp_unslash( $_POST['smoobu_custom_styling_border_radius'] ) );
		}

		// color related values.
		foreach ( $styling['colors'] as $key => $empty ) {
			if ( isset( $_POST[ 'smoobu_custom_styling_color_' . $key ] ) ) {
				$styling['colors'][ $key ] = sanitize_text_field( wp_unslash( $_POST[ 'smoobu_custom_styling_color_' . $key ] ) );
			}
		}

		$css = Smoobu_Utility::get_custom_css( $styling );

		$response = array(
			'status' => 'OK',
			'css'    => $css,
		);

		echo wp_json_encode( $response );

		exit;
	}


	/**
	 * Gets the average room rent from the given dates.
	 *
	 * @return void
	 */
	public function get_average_price() {
		check_ajax_referer( 'pricing_nonce', 'security' );

		// border related values.
		if ( isset( $_POST['star_date'] ) && isset( $_POST['end_date'] ) && isset( $_POST['property_id'] ) ) {
			$start_date  = sanitize_text_field( wp_unslash( $_POST['star_date'] ) );
			$end_date    = sanitize_text_field( wp_unslash( $_POST['end_date'] ) );
			$property_id = sanitize_text_field( wp_unslash( $_POST['property_id'] ) );

			$average_price = $this->fetch_average_price(
				$start_date,
				$end_date,
				$property_id
			);

			/*
				$average_price = Smoobu_Utility::fetch_average_price(
				$start_date,
				$end_date,
				$property_id
			);
			*/

			if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				$currency_symbol = get_woocommerce_currency_symbol();
			} else {
				$currency_symbol = '€';
			}

			$data = array(
				'data'           => __( 'Got the data.', 'smoobu-calendar' ),
				'averagePrice'   => $average_price,
				'currencySymbol' => $currency_symbol,
			);
			wp_send_json_success( $data, 200 );
		} else {
			$data = array(
				'data' => __( 'Insufficient Data.', 'smoobu-calendar' ),
			);
			wp_send_json_error( $data, 500 );
		}
		exit();
	}


	/**
	 * Gets the average price of the property between the mentioned dates.
	 *
	 * @param string  $start_date  checkin date.
	 * @param string  $end_date    checkout date.
	 * @param integer $property_id id of the property.
	 * @return integer
	 */
	public function fetch_average_price( $start_date, $end_date, $property_id ) {
		global $wpdb;

		$result = json_decode(
			$wpdb->get_col( //phpcs:ignore
				$wpdb->prepare(
					"SELECT open_dates FROM {$wpdb->prefix}smoobu_calendar_availability WHERE property_id = %d",
					$property_id
				)
			)[0],
			true
		);

		// 2. Handle empty results early
		if ( empty( $result ) ) {
			return 0;
		}

		// 3. Filter dates and extract prices in-memory
		$prices = array();
		foreach ( $result as $date => $data ) {
			if ( $date >= $start_date && $date < $end_date ) {
				$prices[] = $data['price'];
			}
		}

		// 4. Calculate average price
		if ( empty( $prices ) ) {
			return 0; // Handle cases where no prices match the date range.
		}

		$average_price = array_sum( $prices ) / count( $prices );

		return round( $average_price, 2 );

	}

	/* public function fetch_average_price( $start_date, $end_date, $property_id ) {
		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT
				JSON_UNQUOTE(JSON_EXTRACT(open_dates, CONCAT('$.', date.key, '.price'))) AS price,
				date.key AS date
			FROM {$wpdb->prefix}smoobu_calendar_availability,
			JSON_TABLE(JSON_KEYS(open_dates), '$[*]' COLUMNS (
				`key` VARCHAR(10) PATH '$'
			)) AS date
			WHERE property_id = %d
			AND DATE(date.key) BETWEEN %s AND %s",
			$property_id,
			$start_date,
			$end_date
		);

		$results = $wpdb->get_results( $query, ARRAY_A );

		$total_price = 0;
		$count = 0;
		foreach ( $results as $result ) {
			if ( $result['price'] !== null ) {
				$total_price += $result['price'];
				$count++;
			}
		}

		if ( $count > 0 ) {
			$average_price = $total_price / $count;
			return round( $average_price, 2 );
		} else {
			return 0;
		}
	} */
}
