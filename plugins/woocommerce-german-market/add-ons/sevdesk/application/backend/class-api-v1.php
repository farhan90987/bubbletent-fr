<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class German_Market_SevDesk_API_V1
 *
 * @author MarketPress
 */
class German_Market_SevDesk_API_V1 {

	/**
	* Get base_url
	* @return String
	*/
	public static function get_base_url() {
		return apply_filters( 'sevdesk_woocommerce_api_get_base_url', 'https://my.sevdesk.de/api/v1/' );
	}

	/**
	* Get User Agent for CUrl
	*
	* @since 3.16
	* @return String
	**/
	public static function get_user_agent() {
		return 'MarketPress German Market';
	}

	/**
	* Get api token
	* @return String
	*/
	public static function get_api_token( $show_errors = true ) {

		$api_token = apply_filters( 'sevdesk_woocomerce_api_get_api_token', get_option( 'woocommerce_de_sevdesk_api_token', '' ) );
	
		if ( $api_token == '' && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			
			$error_message = __( 'There is no API token. Please go to the WooCommerce German Market settings and enter a valid API token.', 'woocommerce-german-market' );

			if ( $show_errors ) {
				echo sevdesk_woocommerce_api_get_error_message( $error_message );
				exit();
			} else {
				error_log( 'German Market sevdesk Add-On: ' . $error_message );
			}
		}

		return $api_token;
	}

	/**
	* Check if we can use the order
	* @param WC_Order $order
	* @return WC_Order
	*/
	public static function check_order( $order ) {
		$error = '';

		$error = apply_filters( 'sevdesk_woocommerce_api_check_order', $error, $order );

		if ( $error != '' ) {
			echo sevdesk_woocommerce_api_get_error_message( $error );
			exit();
		}

		return $order;
	}

	/**
	* Markup for error message
	* @param String $message
	* @return String
	*/
	public static function get_error_message( $message = '', $order = null ) {
		
		if ( empty( $message ) ) {
			
			$message = __( 'Unknown error.', 'woocommerce-german-market' );
		
		} else {

			if ( '2.0' === German_Market_SevDesk_API_V2::get_bookkeeping_system_version() ) {
				$message = German_Market_SevDesk_API_V2::get_v2_error_message( $message, $order );
			}
		}

		return trim( __( '<b>ERROR:</b>', 'woocommerce-german-market' ) . ' ' . $message );
	}

	/**
	* Check if curl response is an error
	* @param String $response
	* @return void (exit if error)
	*/
	public static function curl_error_validaton( $response ) {
		$response_array = json_decode( $response, true );
		if ( isset( $response_array[ 'error' ] ) ) {
			
			if ( $response_array[ 'error' ][ 'message' ] == 'No CheckaccountTransaction for online checkaccount given' ) {
				return;	
			}

			echo sevdesk_woocommerce_api_get_error_message( $response_array[ 'error' ][ 'message' ] );

			exit();
		}
	}

	/**
	* Get default value for strings of options 'sevdesk_voucher_description_order' or 'sevdesk_voucher_description_reund'
	* depending on the former setting 'woocommerce_de_sevdesk_voucher_number'
	*
	* @since 3.9.2
	* @param String $option_key
	* @return String
	*/
	public static function get_default_value( $option_key ) {
		$default_value = '';

		if ( $option_key == 'sevdesk_voucher_description_order' ) {

			$default_value = __( 'Order #{{order-number}}', 'woocommerce-german-market' );
			if ( class_exists( 'Woocommerce_Running_Invoice_Number' ) && ( get_option( 'woocommerce_de_sevdesk_voucher_number', 'order_number' ) == 'invoice_number' ) ) {
				$default_value = __( 'Invoice {{invoice-number}}', 'woocommerce-german-market' );
			}

		} else if ( $option_key == 'sevdesk_voucher_description_refund' ) {

			$default_value = __( 'Refund #{{refund-id}} for Order #{{order-number}}', 'woocommerce-german-market' );
			if ( class_exists( 'Woocommerce_Running_Invoice_Number' ) && ( get_option( 'woocommerce_de_sevdesk_voucher_number', 'order_number' ) == 'invoice_number' ) ) {
				$default_value = __( 'Refund {{refund-number}} for Invoice {{invoice-number}}', 'woocommerce-german-market' );
			}

		}

		return $default_value;
	}

	/**
	* Get voucher status (exists or not)
	*
	* @param Integer $args
	* @return Boolean
	*/
	public static function get_vouchers_status( $voucher_id, $show_errors = true ) {
		$curl = curl_init();

		curl_setopt_array( $curl, array(
		  CURLOPT_URL => sevdesk_woocommerce_api_get_base_url() . 'Voucher/' . $voucher_id,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
		    'accept: application/json',
		    'authorization: ' . sevdesk_woocommerce_api_get_api_token( $show_errors ),
		    'cache-control: no-cache',
		  ),
		  CURLOPT_USERAGENT => sevdesk_woocommerce_get_user_agent(),
		) );

		$response = curl_exec( $curl );
		$response_array = json_decode( $response, true );

		if ( isset( $response_array[ 'error' ][ 'code' ] ) && $response_array[ 'error' ][ 'code' ] == 151 ) {
			return false;
		}

		return true;
	}

	/**
	* Get type of check account
	*
	* @since 3.11.1.4
	* @param Integer $checkaccount_id
	* @return String
	**/ 
	public static function get_type_of_check_account( $checkaccount_id ) {
			$type = 'offline';

		$check_accounts = get_transient( 'german_market_sevdesk_checkaccounts' );
		
		if ( ! is_array( $check_accounts ) ) {

			$check_accounts = array();

			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, sevdesk_woocommerce_api_get_base_url() . 'CheckAccount/?register=0' );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Authorization:' . get_option( 'woocommerce_de_sevdesk_api_token' ) ,'Content-Type:application/x-www-form-urlencoded' ) );
			curl_setopt( $ch, CURLOPT_USERAGENT, sevdesk_woocommerce_get_user_agent() );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			$response = curl_exec( $ch );
			$result_array = json_decode( $response, true );
			curl_close( $ch );

			if ( isset ( $result_array[ 'objects' ] ) ) {
				$check_accounts = $result_array[ 'objects' ];
				set_transient( 'german_market_sevdesk_checkaccounts', $check_accounts, MINUTE_IN_SECONDS );
			}

		}

		foreach ( $check_accounts as $check_account ) {
			if ( isset( $check_account[ 'id' ] ) ) {
				if ( intval( $checkaccount_id ) === intval( $check_account[ 'id' ] ) ) {
					if ( isset( $check_account[ 'type' ] ) ) {
						$type = $check_account[ 'type' ];
						break;
					}
				}
			}

		}

		return $type;
	}

}
