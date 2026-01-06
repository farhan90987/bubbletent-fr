<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class German_Market_Lexoffice_API_Contact
 *
 * @author MarketPress
 */
class German_Market_Lexoffice_API_Contact {

	/**
	* Get all contacts
	* @return Array
	*/
	public static function get_all_contacts() {
		
		$transient = get_transient( 'gm_lexoffice_all_contacts' );
		if ( is_array( $transient ) && ! empty( $transient ) ) {
			return $transient;
		}

		if ( get_option( 'woocommerce_de_lexoffice_too_many_contacts', 'no' ) == 'yes' ) {
			if ( apply_filters( 'woocommerce_de_lexoffice_too_many_contacts', true ) ) {
				return array();
			}
		}

		$token_bucket = new WGM_Token_Bucket( 'lexoffice-contacts', 2 );
		$token_bucket->consume();

		$curl = curl_init();

		curl_setopt_array( $curl,

			array(
			  	CURLOPT_URL => German_Market_Lexoffice_API_Auth::get_base_url() . "v1/contacts?size=100",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "GET",
				CURLOPT_HTTPHEADER => array(
				    "accept: application/json",
				    "authorization: Bearer " . German_Market_Lexoffice_API_Auth::get_bearer(),
				    "cache-control: no-cache",
				    "content-type: application/json",
				  ),
			)
		);

		$response 		= curl_exec( $curl );
		$response_array = json_decode( $response, true );

		curl_close( $curl );

		// simple error handling
		if ( ! isset( $response_array[ 'content' ] ) ) {
			return array();
		}

		$contacts 		= $response_array[ 'content' ];
		$total_pages 	= $response_array[ 'totalPages' ];

		if ( apply_filters( 'woocommerce_de_lexoffice_too_many_contacts', true ) ) {
			if ( $total_pages > 10 ) {
				update_option( 'woocommerce_de_lexoffice_too_many_contacts', 'yes' );
				return array();
			}
		}

		if ( $total_pages > 1 ) {

			for ( $i = 2; $i<= $total_pages; $i++ ) {

				$page = $i - 1;

				$token_bucket = new WGM_Token_Bucket( 'lexoffice-contacts', 2 );
				$token_bucket->consume();

				$curl = curl_init();

				curl_setopt_array( $curl,

					array(
					  	CURLOPT_URL => German_Market_Lexoffice_API_Auth::get_base_url() . "v1/contacts/?page=" . $page . "&size=100",
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_ENCODING => "",
						CURLOPT_MAXREDIRS => 10,
						CURLOPT_TIMEOUT => 30,
						CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						CURLOPT_CUSTOMREQUEST => "GET",
						CURLOPT_HTTPHEADER => array(
						    "accept: application/json",
						    "authorization: Bearer " . German_Market_Lexoffice_API_Auth::get_bearer(),
						    "cache-control: no-cache",
						    "content-type: application/json",
						  ),
					)
				);

				$response 		= curl_exec( $curl );
				$response_array = json_decode( $response, true );
				curl_close( $curl );

				$contacts = array_merge( $contacts, $response_array[ 'content' ] );
				set_transient( 'gm_lexoffice_all_contacts', $contacts , 180 );
			}
		}

		return $contacts;
	}

	/**
	* Create a new lexoffice user
	* @param WP_USer $wp_user
	* @param WC_Order $order
	* @return String (lexoffice contact id)
	*/
	public static function create_new_user( $wp_user, $order = null ) {

		$array = self::build_customer_array( $wp_user, $order );
		$json = json_encode( $array, JSON_PRETTY_PRINT );
		$curl = curl_init();

		$token_bucket = new WGM_Token_Bucket( 'lexoffice-contacts', 2 );
		$token_bucket->consume();
		
		curl_setopt_array( $curl,

			array(
			  	CURLOPT_URL => German_Market_Lexoffice_API_Auth::get_base_url() . "v1/contacts/",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => $json,
				CURLOPT_HTTPHEADER => array(
				    "accept: application/json",
				    "authorization: Bearer " . German_Market_Lexoffice_API_Auth::get_bearer(),
				    "cache-control: no-cache",
				    "content-type: application/json",
				  ),
			)

		);

		$response = curl_exec( $curl );
		$response_array = json_decode( $response, true );
		curl_close( $curl );

		if ( isset( $response_array[ 'id' ] ) ) {
			return $response_array[ 'id' ];
		} else {

			$error = '';
			if ( isset( $response_array[ 'IssueList' ] ) ) {
				$error = ': ' . json_encode( $response_array[ 'IssueList' ] );

				foreach ( $response_array[ 'IssueList' ] as $list_elem  ) {
					if ( isset( $list_elem[ 'i18nKey' ] ) && 'invalid_email' === $list_elem[ 'i18nKey' ] ) {
						$error = ': ' . __( 'Invalid email address: Please check the email address in the billing address of the order. The email address must not be empty.', 'woocommerce-german-market' ) . PHP_EOL;
					}
				}
				
			}
			echo __( 'ERROR: Could not create new Lexware Office user', 'woocommerce-german-market' ) . $error;
		}
	}

	/**
	* Build array for wp_user to be send to lexoffice
	* @param WP_User $wp_user
	* @param WP_Order $order
	* @return array
	*/
	public static function build_customer_array( $wp_user, $order = null, $lexoffice_user_data = null ) {

		$customer 			= array();
		$role_customer 		= new stdClass();
		$person 			= new stdClass();
		$company 			= new stdClass();
		$billing_address	= new stdClass();
		$shipping_address 	= new stdClass();
		$addresses 			= array();

		$is_company 		= false;
		$billing_address_is_empty = true;
		$shipping_address_is_empty = true;

		$address_meta_mapping = array(
			'address_1'		=> 'street',
			'address_2'		=> 'supplement',
			'postcode'		=> 'zip',
			'city'			=> 'city',
			'country'		=> 'countryCode',
		);

		$order_prefix = $order ? '_' : '';
		$addresses_pre = array(
			$order_prefix . 'billing_',
			$order_prefix . 'shipping_'
		);

		$salutation = apply_filters( 'lexoffice_woocommerce_create_new_user_default_salutation', '', $wp_user, $order );
		if ( ! empty( $salutation ) ) {
			$person->salutation = $salutation;
		}

		$order_get_address = $order;
		if ( $order_get_address && $order_get_address->get_type() == 'shop_order_refund' ) {
			$order_get_address = wc_get_order( $order->get_parent_id() );
		}

		if ( $order ) {
			$person->lastName 		= $order_get_address->get_billing_last_name();
			$email 					= $order_get_address->get_billing_email();
			$first_name 			= $order_get_address->get_billing_first_name();
			$company_name 			= $order_get_address->get_billing_company();
			$phone 					= $order_get_address->get_billing_phone();
		} else {
			$person->lastName 		= get_user_meta( $wp_user->ID, 'billing_last_name', true );
			$email 					= get_user_meta( $wp_user->ID, 'billing_email', true );
			$first_name 			= get_user_meta( $wp_user->ID, 'billing_first_name', true );
			$company_name 			= get_user_meta( $wp_user->ID, 'billing_company', true );
			$phone 					= get_user_meta( $wp_user->ID, 'billing_phone', true );
		}

		if ( $first_name != '' ) {
			$person->firstName = $first_name;
		}

		// init addresses
		foreach ( $addresses_pre as $pre ) {

			foreach ( $address_meta_mapping as $woocommerce_key => $lexoffice_key ) {

				if ( $order_get_address ) {

					$method_name = 'get' . $pre .  $woocommerce_key;
					if ( WGM_Helper::method_exists( $order_get_address, $method_name ) ) {
						$value = $order_get_address->$method_name();
					} else {
						$value = $order->get_meta( $order_get_address->get_id(), $pre . $woocommerce_key );
					}

				} else {
					$value = get_user_meta( $wp_user->ID, $pre . $woocommerce_key, true );
				}

				if ( $value != '' ) {
		
					if ( strlen( $value ) > 100 ) {
						$value = substr( $value, 0, 100 );
					}

					if ( $pre == 'billing_' || $pre == '_billing_' ) {
						$billing_address->$lexoffice_key = $value;
						$billing_address_is_empty = false;
					} else {
						$shipping_address->$lexoffice_key = $value;
						$shipping_address_is_empty = false;
					}

				}
			}

		}

		if ( apply_filters( 'lexoffice_woocommerce_use_tax_exempt_export_use_customer_as_company', false ) ) {

			if ( function_exists( 'wcvat_woocommerce_order_details_status' ) ) {
				if ( $order->get_type() == 'shop_order_refund' ) {
					$parent_order_of_refund = wc_get_order( $order->get_parent_id() );
					$tax_exempt_status = wcvat_woocommerce_order_details_status( $parent_order_of_refund );
				} else {
					$tax_exempt_status = wcvat_woocommerce_order_details_status( $order );
				}

				if ( $tax_exempt_status == 'tax_exempt_export_delivery' ) {
					$company_name = $person->lastName;
				}
			}

		}

		if ( empty( $company_name ) ) {
			if ( apply_filters( 'woocommerce_de_lexoffice_tax_free_intracommunity_delivery_empty_company', false ) ) {

				if ( $order->get_type() == 'shop_order_refund' ) {
					$parent_order_of_refund = wc_get_order( $order->get_parent_id() );
					$tax_exempt_status = wcvat_woocommerce_order_details_status( $parent_order_of_refund );
				} else {
					$tax_exempt_status = wcvat_woocommerce_order_details_status( $order );
				}

				if ( 'tax_free_intracommunity_delivery' === $tax_exempt_status ) {
					$company_name = $person->lastName;
				}
			}
		}

		if ( $company_name != '' ) {
			$is_company = true;
		}

		if ( ! $is_company ) {

			$customer = array(
				'version' 	=> 0,
				'roles' 	=> array(
					'customer' => $role_customer
				),
				'person' => $person,
				'emailAddresses' => array( 'private' => array( $email ) )
			);

		} else {

			$company = new stdClass();
			$company->name = $company_name;

			if ( isset( $person->lastName ) ) {
				if ( ! empty( $person->lastName ) ) {
					$company->contactPersons = array( $person );
				}
			}

			$billing_vat = $order->get_meta( 'billing_vat' );
			if ( ! empty( $billing_vat ) ) {
				$company->vatRegistrationId = str_replace( ' ', '', $billing_vat );
			}

			if ( ! empty( wcvat_woocommerce_order_details_status( $order ) ) ) {
				$company->allowTaxFreeInvoices = true;
			}

			$customer = array(
				'version' 	=> 0,
				'roles' 	=> array(
					'customer' => $role_customer
				),
				'company' => $company,
				'emailAddresses' => array( 'office' => array( $email ) )
			);
		}

		$billing_address 	= self::exceptions_for_addresses( $billing_address );
		$shipping_address 	= self::exceptions_for_addresses( $shipping_address );

		if ( ( ! $billing_address_is_empty ) ||  ( ! $shipping_address_is_empty ) ) {

			if ( ! $billing_address_is_empty ) {
				$addresses[ 'billing' ] = array( $billing_address );
			}

			if ( ! $shipping_address_is_empty ) {
				$addresses[ 'shipping' ] = array( $shipping_address );
			}

			$customer[ 'addresses' ] = $addresses;
		}

		if ( $phone != '' ) {
			$private_or_office = $is_company ? 'office' : 'private';
			$customer[ 'phoneNumbers' ] = array( $private_or_office => array( $phone ) );
		}

		if ( is_array( $lexoffice_user_data ) ) {
			if ( isset( $lexoffice_user_data[ 'note' ] ) ) {
				$customer[ 'note' ] = $lexoffice_user_data[ 'note' ];
			}
		}

		// filter
		return apply_filters( 'lexoffice_woocomerce_api_customer_array', $customer, $wp_user, $order );

	}
		
	/**
	* Use Collective Contact or lexoffice Users when sending the voucher
	*
	* @param Array $array
	* @param WP_User $user
	* @param WC_Order $order
	* @return Array
	**/
	public static function add_user_to_voucher( $array, $user, $order = null ) {

		$use_collective_contact = 'collective_contact' === get_option( 'woocommerce_de_lexoffice_contacts', 'collective_contact' );
		$use_collective_contact = apply_filters( 'woocommerce_de_lexoffice_use_collective_contact', $use_collective_contact, $order, $user );
		$is_collective_contact_allowed = German_Market_Lexoffice_API_General::is_collective_contact_allowed_for_order( $order );

		if ( $use_collective_contact && $is_collective_contact_allowed ) {

				$array[ 'useCollectiveContact' ] = true;

		} else {

			if ( $user && ( intval( $user->ID ) > 0 ) ) {

				// registered user
				$lexoffice_user_meta = get_user_meta( $user->ID, 'lexoffice_contact', true );
				if ( $lexoffice_user_meta == '' ) {
					$lexoffice_user_meta = '0';
				}

				if ( $lexoffice_user_meta != '0' ) {

					// a lexoffice user is already assigned to the woocommerce user
					// now test if the user still exists

					$still_exists = true;

					$token_bucket = new WGM_Token_Bucket( 'lexoffice-contacts', 2 );
					$token_bucket->consume();

					$curl = curl_init();
					curl_setopt_array( $curl,

						array(
						  	CURLOPT_URL => German_Market_Lexoffice_API_Auth::get_base_url() . "v1/contacts/" . $lexoffice_user_meta,
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_ENCODING => "",
							CURLOPT_MAXREDIRS => 10,
							CURLOPT_TIMEOUT => 30,
							CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
							CURLOPT_CUSTOMREQUEST => "GET",
							CURLOPT_HTTPHEADER => array(
							    "accept: application/json",
							    "authorization: Bearer " . German_Market_Lexoffice_API_Auth::get_bearer(),
							    "cache-control: no-cache",
							    "content-type: application/json",
							  ),
						)
					);

					$response 		= curl_exec( $curl );
					$response_array = json_decode( $response, true );

					curl_close( $curl );

					if ( ! isset( $response_array[ 'id' ] ) ) {
						$still_exists = false;
						update_user_meta( $user->ID, 'lexoffice_contact', '0' );
						$lexoffice_user_meta = '0';
					}

					if ( $still_exists ) {

						// user exists, so use this lexoffice user
						$array[ 'useCollectiveContact' ] = false;
						$array[ 'contactId' ] = $lexoffice_user_meta;

						if ( get_option( 'woocommerce_de_lexoffice_user_update', 'on' ) == 'on' ) {
							self::update_user( $user, $order, $response_array, $lexoffice_user_meta );
						}

					} else {

						// maybe create new user
						if ( 
							( 'on' === get_option( 'woocommerce_de_lexoffice_create_new_user', 'off' ) ) ||
							( ! $is_collective_contact_allowed )
						) {
							$lexoffice_user_meta = self::create_new_user( $user, $order );
							update_user_meta( $user->ID, 'lexoffice_contact', $lexoffice_user_meta );
							$array[ 'useCollectiveContact' ] = false;
							$array[ 'contactId' ] = $lexoffice_user_meta;
						} else {
							$array[ 'useCollectiveContact' ] = true;
						}

					}

				} else {

					// maybe create new user
					if ( 
						( 'on' === get_option( 'woocommerce_de_lexoffice_create_new_user', 'off' ) ) ||
						( ! $is_collective_contact_allowed )
					) {

						$found_user = false;
						$user_version = false;

						$search_response = self::search_user( $order );
						if ( isset( $search_response[ 'id' ] ) && isset( $search_response[ 'version' ] ) ) {
							$found_user = $search_response[ 'id' ];
							$user_version = $search_response[ 'version' ];
						}

						if ( $found_user ) {

							// use found user
							$array[ 'contactId' ] = $found_user;
							$array[ 'useCollectiveContact' ] = false;
							update_user_meta( $user->ID, 'lexoffice_contact', $found_user );

							// update user
							if ( get_option( 'woocommerce_de_lexoffice_user_update', 'on' ) == 'on' ) {
								self::update_user( $user, $order, array( 'version' => $user_version ), $found_user );
							}

						} else {

							$lexoffice_user_meta = self::create_new_user( $user, $order );
							update_user_meta( $user->ID, 'lexoffice_contact', $lexoffice_user_meta );
							$array[ 'useCollectiveContact' ] = false;
							$array[ 'contactId' ] = $lexoffice_user_meta;
						}

					} else {
						$array[ 'useCollectiveContact' ] = true;
					}
				}

			} else {

				// guest user handling
				$guest_handling = get_option( 'woocommerce_de_lexoffice_guest_user', 'collective_contact' );
				$guest_use_collective_contact = apply_filters( 'woocommerce_de_lexoffice_use_collective_contact', 'collective_contact' === $guest_handling, $order, null );

				if ( $guest_use_collective_contact && $is_collective_contact_allowed ) {
					$array[ 'useCollectiveContact' ] = true;
				} else if ( 'create_new_user' === $guest_handling || ( ! $is_collective_contact_allowed ) ) {

					$found_user = false;
					$user_version = false;

					$search_response = self::search_user( $order );
					if ( isset( $search_response[ 'id' ] ) && isset( $search_response[ 'version' ] ) ) {
						$found_user = $search_response[ 'id' ];
						$user_version = $search_response[ 'version' ];
					}

					if ( $found_user ) {

						// use found user
						$array[ 'contactId' ] = $found_user;

						// update user
						if ( get_option( 'woocommerce_de_lexoffice_user_update', 'on' ) == 'on' ) {
							self::update_user( $user, $order, array( 'version' => $user_version ), $found_user );
						}

					} else {

						// create new user
						$lexoffice_user_meta = self::create_new_user( $user, $order );
						$array[ 'contactId' ] = $lexoffice_user_meta;
					}

					$array[ 'useCollectiveContact' ] = false;

				} else {
					$array[ 'useCollectiveContact' ] = false;
					$array[ 'contactId' ] = $guest_handling;
				}
			}
		}

		/**
		 * Add contact name to vouchers that use collective contact
		 * @since 3.39
		 */
		if ( true === $array[ 'useCollectiveContact' ] ) {

			$order_get_address = $order;
			if ( 'shop_order_refund' === $order_get_address->get_type() ) {
				$order_get_address = wc_get_order( $order->get_parent_id() );
			}

			if ( is_object( $order_get_address ) && method_exists( $order_get_address, 'get_billing_company' ) ) {

				$contact_name = '';
				$connect_symbol = '';

				$billing_company = $order_get_address->get_billing_company();
				if ( ! empty( $billing_company ) ) {
					$contact_name = $billing_company;
					$connect_symbol = ', ';
				}

				$billing_name = trim( $order_get_address->get_billing_first_name() . ' ' . $order_get_address->get_billing_last_name() );
				$contact_name .= $connect_symbol . $billing_name;

				$contact_name = trim( apply_filters( 'woocommerce_de_lexoffice_voucher_contact_name_collective_contact', $contact_name, $order_get_address, $billing_company, $billing_name ) );
				
				if ( ! empty( $contact_name ) ) {
					$array[ 'contactName' ] = $contact_name;
				}
			}
		}

		return $array;
	}

	/**
	 * Search for an existing user
	 * 
	 * @param WC_Order $order
	 * @return Array
	 */
	public static function search_user( $order ) {

		$order_get_address = $order;
		if ( $order_get_address->get_type() == 'shop_order_refund' ) {
			$order_get_address = wc_get_order( $order->get_parent_id() );
		}

		$email = $order_get_address->get_billing_email();

		$found_user = array();

		if ( ! empty( $email ) ) {
			$curl = curl_init();

			$token_bucket = new WGM_Token_Bucket( 'lexoffice-contacts', 2 );
			$token_bucket->consume();

			curl_setopt_array( $curl,
				array(
				  	CURLOPT_URL => German_Market_Lexoffice_API_Auth::get_base_url() . "v1/contacts/?email=" . $email,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => "",
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 30,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => "GET",
					CURLOPT_HTTPHEADER => array(
					    "accept: application/json",
					    "authorization: Bearer " . German_Market_Lexoffice_API_Auth::get_bearer(),
					    "cache-control: no-cache",
					    "content-type: application/json",
					  ),
				)
			);

			$response = curl_exec( $curl );
			$response_array = json_decode( $response, true );
			curl_close( $curl );

			if ( isset( $response_array[ 'content' ] ) ) {
				foreach ( $response_array[ 'content' ] as $found_user_array ) {
					if ( isset( $found_user_array[ 'id' ] ) ) { // found user with this email
						$found_user[ 'id' ] = $found_user_array[ 'id' ];
						$found_user[ 'version' ] = $found_user_array[ 'version' ];
						break;
					}
				}
			}
		}

		return $found_user;
	}

	/**
	* Update existing lexoffice user
	*
	* @param WP_User $user
	* @param WC_Order $order
	* @param Array  $response_array
	* @param Integer $lexoffice_user_id
	*
	* @return void
	**/
	public static function update_user( $user, $order, $response_array, $lexoffice_user_id ) {

		$user_array = self::build_customer_array( $user, $order, $response_array );

		$user_array[ 'version' ] = $response_array[ 'version' ];

		$json = json_encode( $user_array, JSON_PRETTY_PRINT );

		$token_bucket = new WGM_Token_Bucket( 'lexoffice-contacts', 2 );
		$token_bucket->consume();
		
		$curl = curl_init();

		curl_setopt_array( $curl,

			array(
			  	CURLOPT_URL => German_Market_Lexoffice_API_Auth::get_base_url() . "v1/contacts/" . $lexoffice_user_id,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "PUT",
				CURLOPT_POSTFIELDS => $json,
				CURLOPT_HTTPHEADER => array(
				    "accept: application/json",
				    "authorization: Bearer " . German_Market_Lexoffice_API_Auth::get_bearer(),
				    "cache-control: no-cache",
				    "content-type: application/json",
				  ),
			)

		);

		$response = curl_exec( $curl );
		$response_array = json_decode( $response, true );

		curl_close( $curl );
	}

	/**
	* Manipulate addresses for exceptions (e.g. Northern Ireland)
	*
	* @param Object $address
	* @return Object
	**/
	public static function exceptions_for_addresses( $address ) {

		// Exception for Northern Ireland, in WC 'GB' is used as CountryCode, 'XI' in lexoffice
		if ( isset( $address->countryCode ) && 'GB' === $address->countryCode ) {

			if ( isset( $address->zip ) ) {
				$zip = strtolower( trim( $address->zip ) );
				if ( strpos( $zip, 'bt' ) === 0 ) {
					$address->countryCode = 'XI';
				}
			}
		}

		return $address;
	}
}
