<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class German_Market_Lexoffice_API_Auth
 *
 * @author MarketPress
 */
class German_Market_Lexoffice_API_Auth {

	/**
	 * Get base url
	 * 
	 * @return String
	 */
	public static function get_base_url() {
		return apply_filters( 'german_market_lexoffice_get_base_url', 'https://api.lexware.io/' );
	}

	/**
	 * Get base app url
	 * 
	 * @return String
	 */
	public static function get_app_base_url() {
		return apply_filters( 'german_market_lexoffice_get_app_base_url', 'https://app.lexware.de/' );
	}

	/**
	 * Get client id and salt
	 * 
	 * @return String
	 */
	public static function get_client_id_and_salt() {
		return apply_filters( 'german_market_lexoffice_get_client_id_and_salt', 'ZGUxNmFkNzgtOWM4NC00ODc3LWJmMjUtMTQwMDVkODM3NDNhOjc3PVokQFlfW0d2d1UoUiE=' );
	}

	/**
	 * Get client id
	 * 
	 * @return String
	 */
	public static function get_client_id() {
		return apply_filters( 'german_market_lexoffice_get_client_id', 'de16ad78-9c84-4877-bf25-14005d83743a' );
	}

	/**
	 * Get sign up URL
	 * 
	 * @return String
	 */
	public static function get_sign_up_url() {
		
		if ( ! has_filter( 'german_market_lexoffice_get_app_base_url' ) ) {
			$signup_url = 'https://www.awin1.com/awclick.php?gid=508718&mid=13787&awinaffid=1909394&linkid=3827001&clickref=';
		} else {
			$signup_url = self::get_app_base_url() . 'signup?pid=1443';
		}

		return $signup_url;
	}

	/**
	 * Get authorize URL
	 * 
	 * @param String $interface_type
	 * @return String
	 */
	public static function get_auth_url( $interface_type = 'outbound' ) {
		
		$url = self::get_app_base_url() . 'oauth2/authorize?client_id=' . self::get_client_id() . '&redirect_uri=/oauth2/code&response_type=code';

		if ( 'inbound' === $interface_type ) {
			$scopes = '&scope=vouchers.write%20vouchers.read%20profile.read%20files.write%20transaction-assignment-hint.write%20files.read%20contacts.read%20contacts.write';

			$url .= $scopes;
		}

		return $url;
	}

	/**
	* API - get auth bearer, OAuth2 authorization
	* @return String
	*/
	public static function get_bearer() {

		German_Market_Lexoffice_Semaphore::init();
		German_Market_Lexoffice_Semaphore::sem_get();

		$bearer = '';

		if ( German_Market_Lexoffice_Semaphore::sem_acquire() ) {

			$bearer = get_option( 'lexoffice_woocommerce_barear', '' ); // access-token
			$code = get_option( 'woocommerce_de_lexoffice_authorization_code', '' );
			$last_used_code = get_option( 'lexoffice_woocommerce_last_auth_code', '' );
			$refresh_time = intval( get_option( 'lexoffice_woocommerce_refresh_time' ) );

			// reconnect
			if ( $code != $last_used_code ) {
				delete_option( 'lexoffice_woocommerce_barear' );
				delete_option( 'lexoffice_woocommerce_refresh_token' );
				delete_option( 'lexoffice_woocommerce_refresh_time' );
				delete_option( 'lexoffice_woocommerce_last_auth_code' );
			}

			///////////////////////////////////
			// if barear is empty => OAuth2
			///////////////////////////////////

			if ( $bearer == '' ) {

				// if code is empty => exit
				if ( empty( $code ) ) {
					if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
						echo __( '<b>ERROR:</b> There is not authorization code. Please go to the WooCommerce German Market settings and enter a valid authorization code.', 'woocommerce-german-market' );
						exit();
					} else {
						return '';
					}
				}

				// get bearer
				$curl = curl_init();

				$api_body = array(
					'grant_type' => 'authorization_code',
					'code'	=> $code,
					'redirect_uri' => '/oauth2/code'
				);

				curl_setopt_array($curl, array(
				  CURLOPT_URL => self::get_app_base_url() . "oauth2/token",
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 30,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "POST",
				  CURLOPT_HTTPHEADER => array(
				    "accept: application/json",
				    "authorization: Basic " . self::get_client_id_and_salt(),
				    "cache-control: no-cache",
				    "Content-Type: application/x-www-form-urlencoded",
				  ),
				  CURLOPT_POSTFIELDS => http_build_query( $api_body ),
				));

				$response = curl_exec($curl);
				$err = curl_error($curl);

				curl_close($curl);

				$response_array = json_decode( $response, true );

				if ( isset( $response_array[ 'access_token' ] ) ) {

					// update bearer
					$bearer = $response_array[ 'access_token' ];
					update_option( 'lexoffice_woocommerce_barear', $bearer );

					// set refresh token
					update_option( 'lexoffice_woocommerce_refresh_token', $response_array[ 'refresh_token' ] );

					// set refresh time
					$refresh_time = time() + intval( $response_array[ 'expires_in' ] );
					update_option( 'lexoffice_woocommerce_refresh_time', $refresh_time );

					// save used authorization code
					update_option( 'lexoffice_woocommerce_last_auth_code', $code );

				}

			}

			///////////////////////////////////
			// Do we need to refresh the bearer?
			///////////////////////////////////
			if ( $refresh_time > 0 ) {

				// we need a new one
				if ( $refresh_time - 100 - time() < 0 ) {

					$refresh_token = get_option( 'lexoffice_woocommerce_refresh_token' );

					$curl = curl_init();

					$api_body = array(
						'grant_type' => 'refresh_token',
						'refresh_token'	=> $refresh_token,
						'redirect_uri' => '/oauth2/code'
					);

					curl_setopt_array($curl, array(
					  CURLOPT_URL => self::get_app_base_url() . "oauth2/token",
					  CURLOPT_RETURNTRANSFER => true,
					  CURLOPT_ENCODING => "",
					  CURLOPT_MAXREDIRS => 10,
					  CURLOPT_TIMEOUT => 30,
					  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					  CURLOPT_CUSTOMREQUEST => "POST",
					  CURLOPT_HTTPHEADER => array(
					    "accept: application/json",
					    "authorization: Basic " . self::get_client_id_and_salt(),
					    "cache-control: no-cache",
					    "Content-Type: application/x-www-form-urlencoded",
					  ),
					  CURLOPT_POSTFIELDS => http_build_query( $api_body ),
					) );

					$response = curl_exec($curl);
					$err = curl_error($curl);

					curl_close($curl);

					$response_array = json_decode( $response, true );

					if ( isset( $response_array[ 'access_token' ] ) ) {

						// update bearer
						$bearer = $response_array[ 'access_token' ];
						update_option( 'lexoffice_woocommerce_barear', $bearer );

						// set refresh token
						update_option( 'lexoffice_woocommerce_refresh_token', $response_array[ 'refresh_token' ] );

						// set refresh time
						$refresh_time = time() + intval( $response_array[ 'expires_in' ] );
						update_option( 'lexoffice_woocommerce_refresh_time', $refresh_time );

					}
				}
			}

			// release semaphore
			German_Market_Lexoffice_Semaphore::sem_release(); 
		}

		return $bearer;
	}

	/**
	* Revoke Authorization
	*/
	public static function revoke_auth() {

		$curl = curl_init();

		$api_body = array(
			'token' => get_option( 'lexoffice_woocommerce_refresh_token' )
		);

		curl_setopt_array( $curl,

			array(
			  	CURLOPT_URL => self::get_app_base_url() . "oauth2/revoke",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_HTTPHEADER => array(
				    "accept: application/json",
				    "authorization: Basic " . self::get_client_id_and_salt(),
				    "cache-control: no-cache",
				    "Content-Type: application/x-www-form-urlencoded",
				  ),
				 CURLOPT_POSTFIELDS => http_build_query( $api_body ),
			)

		);

		$response = curl_exec( $curl );

		$response_array = json_decode( $response, true );
		curl_close( $curl );
	}
}
