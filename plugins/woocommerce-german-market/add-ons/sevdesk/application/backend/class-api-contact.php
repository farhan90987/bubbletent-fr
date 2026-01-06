<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class German_Market_SevDesk_API_Contact
 *
 * @author MarketPress
 */
class German_Market_SevDesk_API_Contact {

	/**
	* create guest user data in sevdesk
	*
	* @param Array $args
	* @return Integer
	*/
	public static function guest_user( $args ) {
		$order = $args[ 'order' ];

		// is guest already created?
		$guest_customer_number = $order->get_meta( '_sevdesk_customer_number_guest' );
		$create_customer = empty( $guest_customer_number );

		if ( ( ! $create_customer ) && apply_filters( 'sevdesk_woocomerce_create_guest_user', true ) ) {

			$sevdesk_user = sevdesk_woocommerce_api_contact_get_by_customer_number( $guest_customer_number, $args );
			
			if ( ! is_array( $sevdesk_user ) ) {
				$create_customer = true;
				$order->delete_meta_data( '_sevdesk_customer_number_guest' );
				$order->delete_meta_data( '_sevdesk_customer_id_guest' );
				$order->save_meta_data();
			}

		}

		// Check if a sevdesk user with same email address already exists in sevdesk
		// If so, use this sevdesk user
		$email = $order->get_billing_email();
		$sevdesk_user_id_by_email = sevdesk_woocommerce_api_contact_get_by_email( $email, $args );

		if ( $sevdesk_user_id_by_email ) {

			$customer = sevdesk_woocommerce_api_contact_build_customer_guest_array( $order );

			if ( isset( $customer[ 'customerNumber' ] ) ) {
				unset( $customer[ 'customerNumber' ] );
			}

			$data_customer = http_build_query( $customer, '', '&', PHP_QUERY_RFC1738 );

			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . 'Contact/' . $sevdesk_user_id_by_email );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
			curl_setopt( $ch, CURLOPT_HEADER, FALSE );
			curl_setopt( $ch, CURLOPT_POST, TRUE );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $data_customer );
			curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "PUT" );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
			  'Authorization:' . $args[ 'api_token' ],
			  'Content-Type: application/x-www-form-urlencoded'
			));
			curl_setopt( $ch, CURLOPT_USERAGENT, sevdesk_woocommerce_get_user_agent() );
			$response = curl_exec( $ch );

			curl_close( $ch );
			sevdesk_woocommerce_api_curl_error_validaton( $response );

			$addresses_and_communication_ways = sevdesk_woocommerce_get_contact_addresses_and_communication_ways( $sevdesk_user_id_by_email, $args );

			sevdesk_woocommerce_api_contact_add_data_guest( 'addEmail', $order, $sevdesk_user_id_by_email, $args, null, true, array(), $addresses_and_communication_ways );
			sevdesk_woocommerce_api_contact_add_data_guest( 'addPhone', $order, $sevdesk_user_id_by_email, $args, null, true, array(), $addresses_and_communication_ways );
			sevdesk_woocommerce_api_contact_add_data_guest( 'addAddress', $order, $sevdesk_user_id_by_email, $args, 47, true, array(), $addresses_and_communication_ways ); // billing address
			sevdesk_woocommerce_api_contact_add_data_guest( 'addAddress', $order, $sevdesk_user_id_by_email, $args, 48, true, array(), $addresses_and_communication_ways); // delivery address

			$response_array 	= json_decode( $response, true );
			$sevdesk_customer 	= $response_array[ 'objects' ];

			$customer_info = sevdesk_woocommerce_api_contact_get_by_customer_number( $sevdesk_customer[ 'customerNumber' ], $args );

			return $customer_info;
		}

		if ( $create_customer && apply_filters( 'sevdesk_woocomerce_create_guest_user', true ) ) {

			// build customer array
			$customer = sevdesk_woocommerce_api_contact_build_customer_guest_array( $order );

			// do we have to create a company first?
			$add_company = apply_filters( 'sevdesk_woocomerce_api_add_company_guest', ( get_option( 'woocommerce_de_sevdesk_customer_add_company', 'on') == 'on' ), $order );

			if ( $add_company ) {
				
				$company = sevdesk_woocommerce_api_contact_build_company_array_guest( $order );
				
				// add company
				if ( is_array( $company ) ) {

					$data = http_build_query( $company, '', '&', PHP_QUERY_RFC1738 );
					$ch = curl_init();
					curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . 'Contact/' );
					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
					curl_setopt( $ch, CURLOPT_HEADER, FALSE );
					curl_setopt( $ch, CURLOPT_POST, TRUE );
					curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
					curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
					  'Authorization:' . $args[ 'api_token' ],
					  'Content-Type: application/x-www-form-urlencoded'
					));
					curl_setopt( $ch, CURLOPT_USERAGENT, sevdesk_woocommerce_get_user_agent() );
					$response = curl_exec( $ch );
					curl_close( $ch );
					sevdesk_woocommerce_api_curl_error_validaton( $response );

					$response_array = json_decode( $response, true );
					if ( isset( $response_array[ 'objects' ] ) ) {
						$sevdesk_commpany = $response_array[ 'objects' ];

						// add company to customer array
						$customer[ 'parent' ] = array(
							'id' 			=> $sevdesk_commpany[ 'id' ],
							'objectName'	=> 'Contact'
						);
					}
				}
			}

			$data_customer = http_build_query( $customer, '', '&', PHP_QUERY_RFC1738 );

			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . 'Contact/' );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
			curl_setopt( $ch, CURLOPT_HEADER, FALSE );
			curl_setopt( $ch, CURLOPT_POST, TRUE );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $data_customer );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
			  'Authorization:' . $args[ 'api_token' ],
			  'Content-Type: application/x-www-form-urlencoded'
			));
			curl_setopt( $ch, CURLOPT_USERAGENT, sevdesk_woocommerce_get_user_agent() );
			$response = curl_exec( $ch );
			curl_close( $ch );
			sevdesk_woocommerce_api_curl_error_validaton( $response );

			// save new sevdesk to order meta
			$response_array 	= json_decode( $response, true );
			$sevdesk_customer 	= $response_array[ 'objects' ];
			$return 			= $sevdesk_customer[ 'customerNumber' ];
			$sevdesk_user_id 	= $sevdesk_customer[ 'id' ];

			$order->add_meta_data( '_sevdesk_customer_number_guest', $sevdesk_customer[ 'customerNumber' ] );
			$order->add_meta_data( '_sevdesk_customer_id_guest',  $sevdesk_user_id );
			$order->save_meta_data();

			// add additional data
			sevdesk_woocommerce_api_contact_add_data_guest( 'addEmail', $order, $sevdesk_user_id, $args );
			sevdesk_woocommerce_api_contact_add_data_guest( 'addPhone', $order, $sevdesk_user_id, $args );
			sevdesk_woocommerce_api_contact_add_data_guest( 'addAddress', $order, $sevdesk_user_id, $args, 47 ); // billing address
			sevdesk_woocommerce_api_contact_add_data_guest( 'addAddress', $order, $sevdesk_user_id, $args, 48 ); // delivery address

			$return = sevdesk_woocommerce_api_contact_get_by_customer_number( $sevdesk_customer[ 'customerNumber' ], $args );

		} else {

			$return = array(
					'id' => apply_filters( 'woocommerce_de_sevdesk_user_id_for_guest_users', $order->get_meta( '_sevdesk_customer_id_guest' ), $order ),
					'objectName' => 'Contact'
				);

		}

		return $return;
	}

	/**
	* create or update user data in sevdesk
	*
	* @param Integer $wordpress_user_id
	* @return Integer
	*/
	public static function contact( $wordpress_user_id, $args ) {

		$return = null;

		// only if option is activated
		if ( get_option( 'woocommerce_de_sevdesk_send_customer_data', 'off' ) == 'on' ) {	
			
			// check if guest
			if ( $wordpress_user_id == 0 ) {
				
				if ( get_option( 'woocommerce_de_sevdesk_guest_users', 'no' ) == 'yes' ) {
					return sevdesk_woocommerce_api_contact_guest_user( $args );
				} else {
					return apply_filters( 'woocommerce_de_sevdesk_send_customer_guest', null, $args );
				}
			}

			// get sevdesk user
			$sevdesk_user = array();
			$sevdesk_user_customer_number = get_user_meta( $wordpress_user_id, '_sevdesk_customer_number', true );

			// 1st try if user still exists
			if ( $sevdesk_user_customer_number != '' ) {
				$sevdesk_user = sevdesk_woocommerce_api_contact_get_by_customer_number( $sevdesk_user_customer_number, $args );
				if ( ! is_array( $sevdesk_user ) ) {
					delete_user_meta( $wordpress_user_id, '_sevdesk_customer_number' );
					delete_user_meta( $wordpress_user_id, '_sevdesk_user_id' );
					delete_user_meta( $wordpress_user_id, '_sevdesk_customer_company_number' );
					delete_user_meta( $wordpress_user_id, '_sevdesk_company_id' );
					delete_user_meta( $wordpress_user_id, '_sevdesk_customer__Email' );
					delete_user_meta( $wordpress_user_id, '_sevdesk_customer__Phone' );
					delete_user_meta( $wordpress_user_id, '_sevdesk_customer_billing_Address' );
					delete_user_meta( $wordpress_user_id, '_sevdesk_customer_shipping_Address' );
					$sevdesk_user_customer_number = '';
				} else {

					// re-save id (it may have change when switchen through sevdesk accounts)
					if ( isset( $sevdesk_user[ 'id' ] ) ) {
						$old_user_id = get_user_meta( $wordpress_user_id, '_sevdesk_user_id', true );
						if ( $old_user_id != $sevdesk_user[ 'id' ] ) {
							delete_user_meta( $wordpress_user_id, '_sevdesk_customer_number' );
							delete_user_meta( $wordpress_user_id, '_sevdesk_user_id' );
							delete_user_meta( $wordpress_user_id, '_sevdesk_customer_company_number' );
							delete_user_meta( $wordpress_user_id, '_sevdesk_company_id' );
							delete_user_meta( $wordpress_user_id, '_sevdesk_customer__Email' );
							delete_user_meta( $wordpress_user_id, '_sevdesk_customer__Phone' );
							delete_user_meta( $wordpress_user_id, '_sevdesk_customer_billing_Address' );
							delete_user_meta( $wordpress_user_id, '_sevdesk_customer_shipping_Address' );
							update_user_meta( $wordpress_user_id, '_sevdesk_user_id', $sevdesk_user[ 'id' ] );	
						}
						
					}
				}

			}

			if ( $sevdesk_user_customer_number == '' ) {

				// Check if a sevdesk user with same email address already exists in sevdesk
				// If so, use this sevdesk user
				if ( isset( $args[ 'order' ] ) ) {
					if ( is_object( $args[ 'order' ] ) && method_exists( $args[ 'order' ], 'get_billing_email' ) ) {
						$email = $args[ 'order' ]->get_billing_email();
						$sevdesk_user_id_by_email = sevdesk_woocommerce_api_contact_get_by_email( $email, $args );
						if ( $sevdesk_user_id_by_email ) {
							$sevdesk_user_customer_number = 'not-wc-user-in-sevdesk'; // just to get in the following -else statement
							update_user_meta( $wordpress_user_id, '_sevdesk_user_id', $sevdesk_user_id_by_email );
						}
					}
				}
			}

			// create a new user
			if ( $sevdesk_user_customer_number == '' ) {

				// build customer array
				$customer = sevdesk_woocommerce_api_contact_build_customer_array( $wordpress_user_id, $args[ 'order' ] );

				// do we have to create a company first?
				$add_company = apply_filters( 'sevdesk_woocomerce_api_add_company', ( get_option( 'woocommerce_de_sevdesk_customer_add_company', 'on') == 'on' ), $wordpress_user_id );

				if ( $add_company ) {
					$company = sevdesk_woocommerce_api_contact_build_company_array( $wordpress_user_id, $args[ 'order' ] );
					
					// add company
					if ( is_array( $company ) ) {

						$data = http_build_query( $company, '', '&', PHP_QUERY_RFC1738 );
						$ch = curl_init();
						curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . 'Contact/' );
						curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
						curl_setopt( $ch, CURLOPT_HEADER, FALSE );
						curl_setopt( $ch, CURLOPT_POST, TRUE );
						curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
						curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
						  'Authorization:' . $args[ 'api_token' ],
						  'Content-Type: application/x-www-form-urlencoded'
						));
						curl_setopt( $ch, CURLOPT_USERAGENT, sevdesk_woocommerce_get_user_agent() );
						$response = curl_exec( $ch );
						curl_close( $ch );
						sevdesk_woocommerce_api_curl_error_validaton( $response );

						$response_array = json_decode( $response, true );
						$sevdesk_commpany = $response_array[ 'objects' ];

						// save new sevdesk company data
						update_user_meta( $wordpress_user_id, '_sevdesk_customer_company_number', $sevdesk_commpany[ 'customerNumber' ] );
						update_user_meta( $wordpress_user_id, '_sevdesk_company_id', $sevdesk_commpany[ 'id' ] );

						if ( apply_filters( 'sevdesk_woocommerce_api_add_company_address', false ) ) {
							sevdesk_woocommerce_api_contact_add_data( 'addAddress', $wordpress_user_id, $sevdesk_commpany[ 'id' ], $args, 47 );
							sevdesk_woocommerce_api_contact_add_data( 'addAddress', $wordpress_user_id, $sevdesk_commpany[ 'id' ], $args, 48 );
						}

						do_action( 'sevdesk_woocommerce_api_after_company_build', $sevdesk_commpany, $wordpress_user_id, $args );

						// add company to customer array
						$customer[ 'parent' ] = array(
							'id' 			=> $sevdesk_commpany[ 'id' ],
							'objectName'	=> 'Contact'
						);

					}

				}

				$data_customer = http_build_query( $customer, '', '&', PHP_QUERY_RFC1738 );

				$ch = curl_init();
				curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . 'Contact/' );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
				curl_setopt( $ch, CURLOPT_HEADER, FALSE );
				curl_setopt( $ch, CURLOPT_POST, TRUE );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $data_customer );
				curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
				  'Authorization:' . $args[ 'api_token' ],
				  'Content-Type: application/x-www-form-urlencoded'
				));
				curl_setopt( $ch, CURLOPT_USERAGENT, sevdesk_woocommerce_get_user_agent() );
				$response = curl_exec( $ch );
				curl_close( $ch );
				sevdesk_woocommerce_api_curl_error_validaton( $response );

				// save new sevdesk user data
				$response_array = json_decode( $response, true );
				$sevdesk_customer = $response_array[ 'objects' ];
				update_user_meta( $wordpress_user_id, '_sevdesk_customer_number', $sevdesk_customer[ 'customerNumber' ] );
				update_user_meta( $wordpress_user_id, '_sevdesk_user_id', $sevdesk_customer[ 'id' ] );
				
				$return = $sevdesk_customer[ 'customerNumber' ];
				$sevdesk_user_id = $sevdesk_customer[ 'id' ];

				// add additional data
				sevdesk_woocommerce_api_contact_add_data( 'addEmail', $wordpress_user_id, $sevdesk_user_id, $args );
				sevdesk_woocommerce_api_contact_add_data( 'addPhone', $wordpress_user_id, $sevdesk_user_id, $args );
				sevdesk_woocommerce_api_contact_add_data( 'addAddress', $wordpress_user_id, $sevdesk_user_id, $args, 47 ); // billing address
				sevdesk_woocommerce_api_contact_add_data( 'addAddress', $wordpress_user_id, $sevdesk_user_id, $args, 48 ); // delivery address

				do_action( 'sevdesk_woocommerce_api_after_customer_build', $sevdesk_customer, $wordpress_user_id, $args );

				$return = sevdesk_woocommerce_api_contact_get_by_customer_number( $sevdesk_customer[ 'customerNumber' ], $args );

			} else {
				
				// user exists update all data
				$customer = sevdesk_woocommerce_api_contact_build_customer_array( $wordpress_user_id );

				// never update customer number, sevder user may exists in sevdesk - identified by email
				if ( isset( $customer[ 'customerNumber' ] ) ) {
					unset( $customer[ 'customerNumber' ] );
				}

				$data = http_build_query( $customer, '', '&', PHP_QUERY_RFC1738 );

				$ch = curl_init();
				$sevdesk_user_id = get_user_meta( $wordpress_user_id, '_sevdesk_user_id', true );
				curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . 'Contact/' . $sevdesk_user_id );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
				curl_setopt( $ch, CURLOPT_HEADER, FALSE );
				curl_setopt( $ch, CURLOPT_POST, TRUE );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
				curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "PUT" );
				curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
				  'Authorization:' . $args[ 'api_token' ],
				  'Content-Type: application/x-www-form-urlencoded'
				));
				curl_setopt( $ch, CURLOPT_USERAGENT, sevdesk_woocommerce_get_user_agent() );
				$response = curl_exec( $ch );
				curl_close( $ch );
				sevdesk_woocommerce_api_curl_error_validaton( $response );

				$addresses_and_communication_ways = sevdesk_woocommerce_get_contact_addresses_and_communication_ways( $sevdesk_user_id, $args );
				
				sevdesk_woocommerce_api_contact_add_data( 'addEmail', $wordpress_user_id, $sevdesk_user_id, $args, null, true, $addresses_and_communication_ways );
				sevdesk_woocommerce_api_contact_add_data( 'addPhone', $wordpress_user_id, $sevdesk_user_id, $args, null, true, $addresses_and_communication_ways );
				sevdesk_woocommerce_api_contact_add_data( 'addAddress', $wordpress_user_id, $sevdesk_user_id, $args, 47, true, $addresses_and_communication_ways ); // billing address
				sevdesk_woocommerce_api_contact_add_data( 'addAddress', $wordpress_user_id, $sevdesk_user_id, $args, 48, true, $addresses_and_communication_ways ); // delivery address

				$return = array(
					'id' => $sevdesk_user_id,
					'objectName' => 'Contact'
				);

			}

		}

		return $return;
	}

	/**
	* build company array from wordpress user_id
	*
	* @param Integer $wordpress_user_id
	* @return Mixed: false (no company) / Array
	*/
	public static function build_company_array( $wordpress_user_id, $order = null ) {
		// init
		$company = false;
		$company_name = get_user_meta( $wordpress_user_id, 'billing_company', true );

		// if there is a company
		if ( trim( $company_name ) != '' ) {

			$company = array(
				'name'				=> $company_name,
				'customerNumber'	=> get_option( 'woocommerce_de_sevdesk_customer_company_number_prefix', '' ) . $wordpress_user_id,
				'category'			=> array( 
										'id' 			=> 3, // customer
										'objectName'	=> 'Category'
									),
				'name2'				=> '',
				'description'		=> '',
				'vatNumber'			=> apply_filters( 'sevdesk_woocomerce_api_customer_vat_number', sevdesk_get_vat_number_of_order_and_wordpress_user_id( $order, $wordpress_user_id ) ),
				'bankAccount'		=> '',
				'bankNumber'		=> ''
			);

			$company = apply_filters( 'sevdesk_woocomerce_api_customer_company_array', $company, $wordpress_user_id );

		}

		return $company;
	}

	/**
	* build company array from order for guest users
	*
	* @param Integer $wordpress_user_id
	* @return Mixed: false (no company) / Array
	*/
	public static function build_company_array_guest( $order ) {
		// init
		$company = false;
		$company_name = $order->get_billing_company();

		// if there is a company
		if ( trim( $company_name ) != '' ) {

			$company = array(
				'name'				=> $company_name,
				'customerNumber'	=> get_option( 'woocommerce_de_sevdesk_customer_company_number_prefix', '' ) . get_option( 'woocommerce_de_sevdesk_customer_guest_prefix', __( 'Guest-', 'woocommerce-german-market' ) ) . $order->get_order_number(),
				'category'			=> array( 
										'id' 			=> 3, // customer
										'objectName'	=> 'Category'
									),
				'name2'				=> '',
				'description'		=> '',
				'vatNumber'			=> apply_filters( 'sevdesk_woocomerce_api_customer_vat_number', sevdesk_get_vat_number_of_order_and_wordpress_user_id( $order ) ),
				'bankAccount'		=> '',
				'bankNumber'		=> ''
			);

			$company = apply_filters( 'sevdesk_woocomerce_api_customer_company_array_guest', $company, $order );

		}

		return $company;
	}

	/**
	* build customer array from order for guest user
	*
	* @param WC_Order
	* @return Array
	*/
	public static function build_customer_guest_array( $order ) {

		// because some admins did not saved first and last name
		$last_name = $order->get_billing_last_name();
		$first_name = $order->get_billing_first_name();

		$customer =  array(
			'familyname'		=> $last_name,
			'surename'			=> $first_name,
			'customerNumber'	=> get_option( 'woocommerce_de_sevdesk_customer_number_prefix', '' ) . get_option( 'woocommerce_de_sevdesk_customer_guest_prefix', __( 'Guest-', 'woocommerce-german-market' ) ) . $order->get_order_number(),
			'category'			=> array( 
										'id' 			=> 3, // customer
										'objectName'	=> 'Category'
									), 
			'birthday'			=> null,
			'title'				=> null,
			'academicTitle' 	=> null,
			'gender'			=> null,
			'name2'				=> null,
			'description'		=> null,
			'vatNumber'			=> apply_filters( 'sevdesk_woocomerce_api_customer_vat_number', sevdesk_get_vat_number_of_order_and_wordpress_user_id( $order ) ),
			'bankAccount'		=> null,
			'bankNumber'		=> null,
		);

		return apply_filters( 'sevdesk_woocomerce_api_customer_guest_array', $customer, $order );
	}

	/**
	* build customer array from wordpress user_id
	*
	* @param Integer $wordpress_user_id
	* @return Array
	*/
	public static function build_customer_array( $wordpress_user_id, $order = null ) {
		$user_data = get_userdata( $wordpress_user_id );
	
		// because some admins did not saved first and last name
		$last_name = $user_data->last_name != '' ? $user_data->last_name : get_user_meta( $wordpress_user_id, 'billing_last_name', true );
		$first_name = $user_data->first_name != '' ? $user_data->first_name : get_user_meta( $wordpress_user_id, 'billing_first_name', true );

		$customer =  array(
			'familyname'		=> $last_name,
			'surename'			=> $first_name,
			'customerNumber'	=> get_option( 'woocommerce_de_sevdesk_customer_number_prefix', '' ) . $wordpress_user_id,
			'category'			=> array( 
										'id' 			=> 3, // customer
										'objectName'	=> 'Category'
									), 
			'birthday'			=> null,
			'title'				=> null,
			'academicTitle' 	=> null,
			'gender'			=> null,
			'name2'				=> null,
			'description'		=> null,
			'vatNumber'			=> apply_filters( 'sevdesk_woocomerce_api_customer_vat_number', sevdesk_get_vat_number_of_order_and_wordpress_user_id( $order, $wordpress_user_id ) ),
			'bankAccount'		=> null,
			'bankNumber'		=> null,
		);

		return apply_filters( 'sevdesk_woocomerce_api_customer_array', $customer, $wordpress_user_id );
	}

	/**
	* get customer vat number by order or wordpress_user_id
	*
	* @param Integer $wordpress_user_id
	* @return Array
	*/
	public static function get_vat_number_of_order_and_wordpress_user_id( $order = null, $wordpress_user_id = null ) {
		
		$vat_number = null;
		$maybe_vat_number = '';

		if ( is_object( $order ) && method_exists( $order, 'get_meta' ) ) {
			$maybe_vat_number = $order->get_meta( 'billing_vat' );
		}

		if ( empty( $maybe_vat_number ) && ( ! is_null( $wordpress_user_id ) ) ) {
			$maybe_vat_number = get_user_meta( $wordpress_user_id, 'billing_vat', true );
		}

		if ( ! empty( $maybe_vat_number ) ) {
			$vat_number = $maybe_vat_number;
		}

		return $vat_number;
	}

	/**
	* add additional customer data
	*
	* @param String $endpoint
	* @param Integer $wordpress_user_id
	* @param Integer $sevdesk_user_id
	* @param Array $args
	* @param Integer $address_category
	* @return void
	*/
	public static function contact_add_data( $endpoint, $wordpress_user_id, $sevdesk_user_id, $args, $address_category = 47, $update = false, $addresses_and_communication_ways = array() ) {
		$user_data = get_userdata( $wordpress_user_id );
		$post_meta_prefix = '';

		if ( $endpoint == 'addEmail' ) {

			$data = array(
				'key'	=> 2, // work
				'value'	=> $user_data->user_email,
				'type'	=> 2
			);

		} else if ( $endpoint == 'addPhone' ) {

			$data = array(
				'key'	=> 2, // work
				'value'	=> get_user_meta( $wordpress_user_id, 'billing_phone', true ),
				'type'	=> 2
			);

		} else if ( $endpoint == 'addAddress' ) {

			$post_meta_prefix = $address_category == 48 ? 'shipping' : 'billing';

			// get country
			$user_country = strtolower( get_user_meta( $wordpress_user_id, $post_meta_prefix . '_country', true ) );

			// get all country codes to get the id of the country
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . 'StaticCountry/?limit=999' );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
			curl_setopt( $ch, CURLOPT_HEADER, FALSE );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
				'Authorization:' . $args[ 'api_token' ],
				'Content-Type: application/x-www-form-urlencoded'
			));
			curl_setopt( $ch, CURLOPT_USERAGENT, sevdesk_woocommerce_get_user_agent() );
			$response = curl_exec( $ch );
			curl_close( $ch );
			sevdesk_woocommerce_api_curl_error_validaton( $response );
			$response_array = json_decode( $response, true );
			$countries = $response_array[ 'objects' ];

			$data = array(
				'street'	=> trim( get_user_meta( $wordpress_user_id, $post_meta_prefix . '_address_1', true ) . ' ' . get_user_meta( $wordpress_user_id, $post_meta_prefix . '_address_2', true ) ),
				'zip'		=> get_user_meta( $wordpress_user_id, $post_meta_prefix . '_postcode', true ),
				'city'		=> get_user_meta( $wordpress_user_id, $post_meta_prefix . '_city', true ),
				'category'	=> $address_category,
				'type'		=> $address_category,
			);

			$data[ 'contact' ] = array(
				'id' => $sevdesk_user_id,
				'objectName' => 'Contact'
			);

			// get country
			$not_add_country = empty( trim( $data[ 'street' ] ) ) && empty( trim( $data[ 'zip' ] ) ) && empty( trim( $data[ 'city' ] ) );
			
			// don't add adress if no adress is set at all
			if ( $not_add_country ) {
				return;
			}

			// pretend to be from Germany if we will not find the correct country
			$data[ 'country' ] = 1;

			foreach ( $countries as $country ) {
				// attention: a WooCommerce country code always consists of 2 letters (even if it should be 3)
				if ( strtolower( substr( $country[ 'code' ], 0, 2 ) ) == strtolower( $user_country ) ) {
					$data[ 'country' ] = $country[ 'id' ];
					break;
				}
			}

		}

		$post_meta_key = str_replace( 'add', '_sevdesk_customer_' . $post_meta_prefix . '_', $endpoint );

		$data = apply_filters( 'sevdesk_woocomerce_api_customer_data_before_send', $data, $endpoint, $wordpress_user_id, $sevdesk_user_id, $args, $address_category, $update );

		if ( $update ) {
			$new_data = sevdesk_woocommerce_update_sevdesk_user_data( $sevdesk_user_id, $data, $endpoint, $args, $address_category, $addresses_and_communication_ways );
			if ( $new_data ) {
				$update = false;
			}
		}

		if ( ! $update ) {

			// add data
			$data = http_build_query( $data, '', '&', PHP_QUERY_RFC1738 );
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . 'Contact/' . $sevdesk_user_id . '/' . $endpoint );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
			curl_setopt( $ch, CURLOPT_HEADER, FALSE );
			curl_setopt( $ch, CURLOPT_POST, TRUE );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
				'Authorization:' . $args[ 'api_token' ],
				'Content-Type: application/x-www-form-urlencoded'
			));
			curl_setopt( $ch, CURLOPT_USERAGENT, sevdesk_woocommerce_get_user_agent() );
			$response = curl_exec( $ch );
			curl_close( $ch );

			sevdesk_woocommerce_api_curl_error_validaton( $response );

			// Save id of CommunicationWay to update this data later
			$response_array = json_decode( $response, true );
			$id = $response_array[ 'objects' ][ 'id' ];
			update_user_meta( $wordpress_user_id, $post_meta_key, $id );

		}
	}

	/**
	* add additional customer data for guest users
	*
	* @param String $endpoint
	* @param Integer $wordpress_user_id
	* @param Integer $sevdesk_user_id
	* @param Array $args
	* @param Integer $address_category
	* @return void
	*/
	public static function add_data_guest( $endpoint, $order, $sevdesk_user_id, $args, $address_category = 47, $update = false, $user_data = array(), $addresses_and_communication_ways = array() ) {

		$post_meta_prefix = '';

		if ( $endpoint == 'addEmail' ) {

			$data = array(
				'key'	=> 2, // work
				'value'	=> $order->get_billing_email(),
				'type'	=> 2
			);

		} else if ( $endpoint == 'addPhone' ) {

			$data = array(
				'key'	=> 2, // work
				'value'	=> $order->get_billing_phone(),
				'type'	=> 2
			);

		} else if ( $endpoint == 'addAddress' ) {

			$post_meta_prefix = $address_category == 48 ? 'shipping' : 'billing';

			// get country
			if ( $post_meta_prefix == 'shipping' ) {
				$user_country = $order->get_shipping_country();
			} else {
				$user_country = $order->get_billing_country();
			}

			// get all country codes to get the id of the country
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . 'StaticCountry/?limit=999' );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
			curl_setopt( $ch, CURLOPT_HEADER, FALSE );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
				'Authorization:' . $args[ 'api_token' ],
				'Content-Type: application/x-www-form-urlencoded'
			));
			curl_setopt( $ch, CURLOPT_USERAGENT, sevdesk_woocommerce_get_user_agent() );
			$response = curl_exec( $ch );
			curl_close( $ch );
			sevdesk_woocommerce_api_curl_error_validaton( $response );
			$response_array = json_decode( $response, true );
			$countries = $response_array[ 'objects' ];

			if ( $post_meta_prefix == 'shipping' ) {
				
				$data = array(
					'street'	=> trim( $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2() ),
					'zip'		=> $order->get_shipping_postcode(),
					'city'		=> $order->get_shipping_city(),
					'category'	=> $address_category,
					'type'		=> $address_category,
				);

			} else {

				$data = array(
					'street'	=> trim( $order->get_billing_address_1() . ' ' . $order->get_billing_address_2() ),
					'zip'		=> $order->get_billing_postcode(),
					'city'		=> $order->get_billing_city(),
					'category'	=> $address_category,
					'type'		=> $address_category,
				);

			}

			$data[ 'contact' ] = array(
				'id' => $sevdesk_user_id,
				'objectName' => 'Contact'
			);

			// get country
			$not_add_country = empty( trim( $data[ 'street' ] ) ) && empty( trim( $data[ 'zip' ] ) ) && empty( trim( $data[ 'city' ] ) );
			
			// don't add adress if no adress is set at all
			if ( $not_add_country ) {
				return;
			}

			// pretend to be from Germany if we will not find the correct country
			$data[ 'country' ] = 1;

			foreach ( $countries as $country ) {
				// attention: a WooCommerce country code always consists of 2 letters (even if it should be 3)
				if ( strtolower( substr( $country[ 'code' ], 0, 2 ) ) == strtolower( $user_country ) ) {
					$data[ 'country' ] = $country[ 'id' ];
					break;
				}
			}

		}

		$post_meta_key = str_replace( 'add', '_sevdesk_customer_' . $post_meta_prefix . '_', $endpoint );
		
		$data = apply_filters( 'sevdesk_woocomerce_api_customer_data_before_send_guest', $data, $endpoint, $order, $sevdesk_user_id, $args, $address_category, $update );

		if ( $update ) {
			$new_data = sevdesk_woocommerce_update_sevdesk_user_data( $sevdesk_user_id, $data, $endpoint, $args, $address_category, $addresses_and_communication_ways );
			if ( $new_data ) {
				$update = false;
			}
		}

		if ( ! $update ) {

			// add data
			$data = http_build_query( $data, '', '&', PHP_QUERY_RFC1738 );
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . 'Contact/' . $sevdesk_user_id . '/' . $endpoint );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
			curl_setopt( $ch, CURLOPT_HEADER, FALSE );
			curl_setopt( $ch, CURLOPT_POST, TRUE );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
				'Authorization:' . $args[ 'api_token' ],
				'Content-Type: application/x-www-form-urlencoded'
			));
			curl_setopt( $ch, CURLOPT_USERAGENT, sevdesk_woocommerce_get_user_agent() );
			$response = curl_exec( $ch );
			curl_close( $ch );

			sevdesk_woocommerce_api_curl_error_validaton( $response );

			// Save id of CommunicationWay to update this data later
			$response_array = json_decode( $response, true );
			$id = $response_array[ 'objects' ][ 'id' ];
		}
	}

	/**
	* this functions checks if the value of an endpoint (email, phone, address)
	* already exists for a sevdesk user. It returns true if this data is new.
	*
	* @param String $sevdes_user_id
	* @param Array $data
	* @param String $endpoint
	* @param Array $args
	* @param Integer $address_category
	* @return Boolean
	*/
	public static function update_sevdesk_user_data( $sevdesk_user_id, $data, $endpoint, $args, $address_category, $user_data ) {
		$new_data = false;

		if ( 'addEmail' === $endpoint ) {

			if ( isset( $user_data[ 'email' ] ) ) {
				$email = $data[ 'value' ];
				if ( ! in_array( $email, $user_data[ 'email' ] ) ) {
					$new_data = true;
				}
			}

		} else if ( 'addPhone' === $endpoint ) {

			if ( isset( $user_data[ 'phone' ] ) ) {
				$phone = $data[ 'value' ];
				if ( ! in_array( $phone, $user_data[ 'phone' ] ) ) {
					$new_data = true;
				}
			}

		} else if ( 'addAddress' === $endpoint ) {
			
			$key = false;

			if ( 47 === $address_category ) {
				$key = 'billing_address';
			} else if ( 48 === $address_category ) {
				$key = 'shipping_address';
			}

			if ( isset( $user_data[ $key ] ) ) {
				
				if ( isset( $data[ 'street' ] ) && isset( $data[ 'zip' ] ) && isset( $data[ 'city' ] ) ) {

					$found_address = false;

					foreach ( $user_data[ $key ] as $address ) {
						
						if (	
								isset( $address[ 'street' ] ) && isset( $address[ 'zip' ] ) && isset( $address[ 'city' ] ) &&
								( 
									trim( $address[ 'street' ] ) == trim( $data[ 'street' ] ) &&
									trim( $address[ 'zip' ] ) == trim( $data[ 'zip' ] ) &&
									trim( $address[ 'city' ] ) == trim( $data[ 'city' ] )
								)
						) {

							$found_address = true;
							break;
							
						}
					}

					if ( ! $found_address ) {
						$new_data = true;
					}
				}
			}

		}

		return $new_data;
	}

	/**
	* get sevdesk_user bei sevdesk_user_id
	*
	* @param Integer $sevdesk_user_id
	* @return -1 OR Array
	*/
	public static function get_by_customer_number( $sevdesk_customer_number, $args ) {

		$return = -1;

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . 'Contact/?customerNumber=' . $sevdesk_customer_number . '&depth=true' );
		curl_setopt( $ch, CURLOPT_POST, 0 );
		curl_setopt( $ch,CURLOPT_HTTPHEADER,array( 'Authorization:' . $args[ 'api_token' ] ) );
		curl_setopt( $ch, CURLOPT_USERAGENT, sevdesk_woocommerce_get_user_agent() );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		$response = curl_exec( $ch );
		curl_close( $ch );
		$result_array = json_decode( $response, true );

		if ( isset( $result_array[ 'objects' ][ 0 ][ 'id' ] ) ) {
			$return = $result_array[ 'objects' ][ 0 ];
		}

		return $return;
	}

	/**
	* get all address data and communication ways (phone & email) of a sevdesk user
	*
	* @param String $sevdesk_user_id
	* @param Array $args
	* @return Array
	*/
	public static function get_contact_addresses_and_communication_ways( $sevdesk_user_id, $args ) {
		$data = array(
			'phone' => array(),
			'email' => array(),
			'billing_address' => array(),
			'shipping_address' => array(),
		);

		// communication ways
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . 'CommunicationWay/?contact[objectName]=Contact&contact[id]=' . $sevdesk_user_id );
		curl_setopt( $ch, CURLOPT_POST, 0 );
		curl_setopt( $ch,CURLOPT_HTTPHEADER,array( 'Authorization:' . $args[ 'api_token' ] ) );
		curl_setopt( $ch, CURLOPT_USERAGENT, sevdesk_woocommerce_get_user_agent() );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		$response = curl_exec( $ch );
		curl_close( $ch );
		
		$result_array = json_decode( $response, true );
		if ( isset( $result_array[ 'objects' ] ) ) {
			foreach ( $result_array[ 'objects' ] as $communication_way ) {
				if ( isset( $communication_way[ 'type' ] ) && 'PHONE' === $communication_way[ 'type' ] ) {
					$data[ 'phone' ][] = $communication_way[ 'value' ];
				} else if ( isset( $communication_way[ 'type' ] ) && 'EMAIL' === $communication_way[ 'type' ] ) {
					$data[ 'email' ][] = $communication_way[ 'value' ];
				}
			}
		}

		// contact addresses
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . 'ContactAddress/?contact[objectName]=Contact&contact[id]=' . $sevdesk_user_id );
		curl_setopt( $ch, CURLOPT_POST, 0 );
		curl_setopt( $ch,CURLOPT_HTTPHEADER,array( 'Authorization:' . $args[ 'api_token' ] ) );
		curl_setopt( $ch, CURLOPT_USERAGENT, sevdesk_woocommerce_get_user_agent() );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		$response = curl_exec( $ch );
		$result_array = json_decode( $response, true );
		curl_close( $ch );

		if ( isset( $result_array[ 'objects' ] ) ) {
			foreach ( $result_array[ 'objects' ] as $address ) {
				if ( isset( $address[ 'category' ][ 'id' ] ) ) {
					
					$key = false;

					if ( 47 === intval( $address[ 'category' ][ 'id' ] ) ) {
						$key = 'billing_address';
					} else if ( 48 === intval( $address[ 'category' ][ 'id' ] ) ) {
						$key = 'shipping_address';
					}

					if ( $key ) {

						$data[ $key ][] = array(
							'street'	=> $address[ 'street' ],
							'zip'		=> $address[ 'zip' ],
							'city'		=> $address[ 'city' ],
							'category'	=> $address[ 'category' ][ 'id' ],
						);

					}

						
				}
			}
		}

		return $data;
	}

	/**
	* check if a sevdesk user with the same email exists
	* and return the sevdesk user id
	*
	* @param String $email
	* @param Array $args
	* @return String
	*/
	public static function get_by_email( $email, $args ) {

		$sevdesk_user_id = false;

		if ( apply_filters( 'sevdesk_woocomerce_get_sevdesk_contact_by_email_before_creating_new_contact', true ) ) {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . 'CommunicationWay/?value=' . $email . '&depth=true' );
			curl_setopt( $ch, CURLOPT_POST, 0 );
			curl_setopt( $ch,CURLOPT_HTTPHEADER,array( 'Authorization:' . $args[ 'api_token' ] ) );
			curl_setopt( $ch, CURLOPT_USERAGENT, sevdesk_woocommerce_get_user_agent() );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			$response = curl_exec( $ch );
			curl_close( $ch );
			$result_array = json_decode( $response, true );

			if ( isset( $result_array[ 'objects' ] ) && is_array( $result_array[ 'objects' ] ) ) {
				foreach ( $result_array[ 'objects' ] as $result_element ) {
					if ( isset( $result_element[ 'contact' ][ 'id' ] ) ) {
						$sevdesk_user_id = $result_element[ 'contact' ][ 'id' ];
						break;
					}
				}
			}			
		}

		return $sevdesk_user_id;
	}

}
