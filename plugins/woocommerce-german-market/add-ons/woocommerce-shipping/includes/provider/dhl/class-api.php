<?php

namespace MarketPress\GermanMarket\Shipping\Provider\DHL;

use League\ISO3166\ISO3166;
use MarketPress\GermanMarket\Shipping\Api as Shipping_Api;
use MarketPress\GermanMarket\Shipping\Helper;
use stdClass;
use WC_Order;

class Api extends Shipping_Api {

	/**
	 * @var string
	 */
	const BASE_DHL_AUTH = 'http://dhl.de/webservice/cisbase';

	/**
	 * Singleton.
	 *
	 * @acces protected
	 * @static
	 *
	 * @var self
	 */
	protected static $instance;

	/**
	 * @var string
	 */
	private string $locale_value = 'en-US';

	/**
	 * @acces public
	 *
	 * @var bool
	 */
	public bool $staging;

	/**
	 * @var string
	 */
	private string $api_username;

	/**
	 * @var string
	 */
	private string $api_password;

	/**
	 * Singleton getInstance.
	 *
	 * @static
	 *
	 * @param Shipping_Provider $provider
	 *
	 * @return self
	 */
	public static function get_instance( Shipping_Provider $provider ) : self {

		return ( null !== self::$instance ) ? self::$instance : self::$instance = new self( $provider );
	}

	/**
	 * Class constructor.
	 *
	 * @param Shipping_Provider $provider
	 */
	public function __construct( Shipping_Provider $provider ) {

		parent::__construct();

		$this->provider = $provider;

		$this->api_username = $provider::$options->get_option( 'global_username' );
		$this->api_password = $provider::$options->get_option( 'global_signature' );
		$this->staging      = ( 'on' === $provider::$options->get_option( 'test_mode' ) );

		if ( true === $this->staging ) {
			$this->api_username = 'user-valid';

			// DHL changed sandbox password after 12th of september
			if ( date( 'Ymd' ) < '20240912' ) {
				$this->api_password = 'pass';
			} else {
				$this->api_password = 'SandboxPasswort2023!';
			}
		}

		$this->provider_id = $provider->id;

		$locale = get_locale();

		if ( false !== strpos( $locale, 'de_' ) ) {
			$this->locale_value = 'de-DE';
		}
	}

	/**
	 * Returns API URL depending if staging mode or not.
	 *
	 * @acces public
	 *
	 * @param bool $location
	 *
	 * @return string
	 */
	public function get_base_url( bool $location = false ) : string {

		$url = 'https://api-sandbox.dhl.com';

		if ( ! $this->staging ) {
			$url = ( false === $location ) ? 'https://api-eu.dhl.com' : 'https://api.dhl.com';
		}

		return $url;
	}

	/**
	 * Returns the REST unified location finder path.
	 *
	 * @acces public
	 *
	 * @return string
	 */
	public function get_location_finder_service() : string {

		return $this->get_base_url( true ) . '/location-finder/v1';
	}

	/**
	 * Returns the parcel shipping REST API path.
	 *
	 * @acces public
	 *
	 * @return string
	 */
	public function get_parcel_shipping_service() : string {

		return $this->get_base_url() . '/parcel/de/shipping/v2';
	}

	/**
	 * Returns the location finder REST API route.
	 *
	 * @acces public
	 *
	 * @return string
	 */
	public function get_location_finder_route() : string {

		return '/find-by-address';
	}

	/**
	 * Returns the parcel shipping REST API order route.
	 *
	 * @acces public
	 *
	 * @return string
	 */
	public function get_order_route() : string {

		return '/orders';
	}

	/**
	 * Returns the REST return label path.
	 *
	 * @acces public
	 *
	 * @return string
	 */
	public function get_return_label_route() : string {

		return $this->get_base_url() . '/parcel/de/shipping/returns/v1/orders';
	}

	/**
	 * Return the DHL REST API Key.
	 *
	 * @acces public
	 *
	 * @return string
	 */
	public function get_api_key() : string {

		return defined( 'WGM_SHIPPING_DHL_REST_API_KEY' ) ? WGM_SHIPPING_DHL_REST_API_KEY : 'Xkkiq7JYoXCLad2PtWozOOGHKLw3QMn5';
	}

	/**
	 * Return the DHL REST API Client ID.
	 *
	 * @acces public
	 * @static
	 *
	 * @return string
	 */
	public static function get_client_id() : string {

		return defined( 'WGM_SHIPPING_DHL_REST_API_CLIENT_ID' ) ? WGM_SHIPPING_DHL_REST_API_CLIENT_ID : 'Xkkiq7JYoXCLad2PtWozOOGHKLw3QMn5';
	}

	/**
	 * Return the DHL REST API Client Secret.
	 *
	 * @acces public
	 * @static
	 *
	 * @return string
	 */
	public static function get_client_secret() : string {

		return defined( 'WGM_SHIPPING_DHL_REST_API_CLIENT_SECRET' ) ? WGM_SHIPPING_DHL_REST_API_CLIENT_SECRET : 'TFBXReO1O7YP717F';
	}

	/**
	 * Test REST API connection if credentials available.
	 *
	 * @return int
	 */
	public function test_connection() : int {

		$api_username = $this->provider::$options->get_option( 'global_username' );
		$api_password = $this->provider::$options->get_option( 'global_signature' );
		$staging      = ( 'on' === $this->provider::$options->get_option( 'test_mode' ) );

		$this->staging = $staging;

		if ( ( ! $staging ) && ( ( '' === $api_username ) || ( '' === $api_password ) ) ) {
			delete_transient( 'wgm_shipping_dhl_api_connection' );
			return 100;
		}

		if ( $staging ) {
			$api_username = 'user-valid';

			// DHL changed sandbox password after 12th of september
			if ( date( 'Ymd' ) < '20240912' ) {
				$api_password = 'pass';
			} else {
				$api_password = 'SandboxPasswort2023!';
			}
		}

		if ( ! empty( get_transient( 'wgm_shipping_dhl_api_connection' ) ) ) {
			$credentials = get_transient( 'wgm_shipping_dhl_api_connection' );
			if ( ( $credentials[ 'username' ] !== md5( $api_username ) ) || ( $credentials[ 'password' ] !== md5( $api_password ) ) ) {
				delete_transient( 'wgm_shipping_dhl_api_connection' );
			} else {
				return $credentials[ 'status' ];
			}
		}

		// Build API route.
		$url = $this->get_parcel_shipping_service() . $this->get_order_route();

		// Set validate param.
		$url = add_query_arg( 'validate', 'true', $url );

		// Test data.
		$data = array(
			'profile'   => 'STANDARD_GRUPPENPROFIL',
			'shipments' => array(
				array(
					'product'       => WGM_SHIPPING_PRODUKT_DHL_PAKET,
					'billingNumber' => '12345678901234',
					'refNo'         => 'Order No. 1234',
					'shipDate'      => date( 'Y-m-d' ),
					'shipper'       => array(
						'name1'         => 'Test',
						'addressStreet' => 'Steinbacher Straße 10',
						'postalCode'    => '01157',
						'city'          => 'Dresden',
						'country'       => 'DEU',
					),
					'consignee'     => array(
						'name1'         => 'Test',
						'addressStreet' => 'Hebbelstraße 10',
						'postalCode'    => '01157',
						'city'          => 'Dresden',
						'country'       => 'DEU',
					),
					'details'       => array(
						'weight' => array(
							'uom'   => 'kg',
							'value' => 5,
						),
					),
				),
			),
		);

		$response = wp_remote_post( $url, array(
			'body' => json_encode( $data ),
			'headers' => array(
				'Accept-Language' => $this->locale_value,
				'Authorization'   => 'Basic ' . base64_encode( $api_username . ':' . $api_password ),
				'dhl-api-key'     => $this->get_api_key(),
				'Content-Type'    => 'application/json',
			),
		) );

		if ( ! empty( $response[ 'body' ] ) ) {
			$response = json_decode( $response[ 'body' ], true );
		}

		if ( ! $staging ) {
			if ( isset( $response[ 'statusCode' ] ) && ( 401 === $response[ 'statusCode' ] ) ) {
				delete_transient( 'wgm_shipping_dhl_api_connection' );
				return 401;
			}
		}

		set_transient( 'wgm_shipping_dhl_api_connection', array(
			'status'   => 200,
			'username' => md5( $api_username ),
			'password' => md5( $api_password ),
		),
			HOUR_IN_SECONDS );

		return 200;
	}

	/**
	 * Perform the location finder REST API call.
	 *
	 * @acces public
	 *
	 * @param array $data array with location details
	 *     - streetAddress
	 *     - addressLocality
	 *     - postalCode
	 *     - countryCode
	 *     - locationType e.g. 'locker'
	 *
	 * @return array
	 */
	public function do_request_find_locations( array $data ) : array {

		$url  = $this->get_location_finder_service() . $this->get_location_finder_route();

		foreach ( $data as $key => $value ) {
			if ( ! is_array( $value ) ) {
				$url = add_query_arg( $key, $value, $url );
			} else {
				foreach ( $value as $sub_value ) {
					$url .= '&' . $key . '=' . $sub_value;
				}
			}
		}

		$response = wp_remote_request( $url, array(
			'method'  => 'GET',
			'headers' => array(
				'Accept-Language' => $this->locale_value,
				'dhl-api-key'     => $this->get_api_key(),
			),
		) );

		if ( is_wp_error( $response ) ) {
			return array(
				'status'  => 'error',
				'message' => $response->get_error_message(),
			);
		} else {
			return array(
				'status'    => 'success',
				'terminals' => json_decode( $response[ 'body' ] ),
			);
		}
	}

	/**
	 * Perform an API Call for available packstations for a given location.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function find_packstations( array $data ) : array {

		$shops     = array();
		$terminals = $this->get_session_cache( 'packstations' );

		if ( empty( $data[ 'country' ] ) || empty( $data[ 'city' ] ) || empty( $data[ 'street' ] ) || empty( $data[ 'zipCode' ] ) ) {
			return $shops;
		}

		if ( empty( $terminals ) || ! is_array( $terminals ) ) {
			$terminals = $this->get_cache( 'packstations', $data );
		}

		if ( empty( $terminals[ 'last_query' ] ) || ( $terminals[ 'last_query' ] !== json_encode( $data ) ) || empty( json_decode( $terminals[ 'terminals' ] ) ) ) {

			$listed_parcelshops = array();

			// Mapping old SOAP params to new REST params.
			$request_data = array(
				'streetAddress'   => $data[ 'street' ],
				'addressLocality' => $data[ 'city' ],
				'postalCode'      => $data[ 'zipCode' ],
				'countryCode'     => $data[ 'country' ],
				'locationType'    => 'locker',
			);

			$response = $this->do_request_find_locations( $request_data );

			if ( isset( $response[ 'status' ] ) && ( 'success' == $response[ 'status' ] ) && isset( $response[ 'terminals' ] ) ) {

				$response_terminals = $response[ 'terminals' ];

				if ( isset( $response_terminals->locations ) ) {
					foreach ( $response_terminals->locations as $packstation ) {

						$parcelshop_id = $packstation->location->keywordId;

						if ( empty( $parcelshop_id ) ) {
							$parcelshop_id = trim( preg_replace("/[^0-9]{3}/", '', $packstation->name ) );
						}

						if ( ! empty( $parcelshop_id ) && in_array( $parcelshop_id, $listed_parcelshops ) ) {
							continue;
						}

						$listed_parcelshops[] = $parcelshop_id;

						$shops[] = array(
							'parcelshop_id' => $parcelshop_id,
							'location_id'   => preg_replace( '/\/locations\/(.*)$/', '$1', $packstation->url ),
							'company'       => ( isset( $packstation->name ) ) ? $packstation->name : '',
							'country'       => WC()->countries->countries[ $packstation->place->address->countryCode ],
							'city'          => $packstation->place->address->addressLocality,
							'pcode'         => $packstation->place->address->postalCode,
							'street'        => $packstation->place->address->streetAddress,
							'email'         => '',
							'phone'         => '',
							'mon'           => '',
							'tue'           => '',
							'wed'           => '',
							'thu'           => '',
							'fri'           => '',
							'sat'           => '',
							'sun'           => '',
							'distance'      => $packstation->distance,
							'longitude'     => $packstation->place->geo->longitude,
							'latitude'      => $packstation->place->geo->latitude,
							'cod'           => 0,
						);
					}
				}

				$terminals[ 'terminals' ]  = json_encode( $shops );
				$terminals[ 'last_query' ] = json_encode( $data );
			}

			$this->set_cache( 'packstations', $terminals, $data );

		} else {
			$shops = json_decode( $terminals[ 'terminals' ], true );
		}

		return $shops;
	}

	/**
	 * Perform an API Call for available parcel shops for a given location.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function find_parcelshops( array $data ) : array {

		$shops     = array();
		$terminals = $this->get_session_cache( 'parcelshops' );

		if ( empty( $data[ 'country' ] ) || empty( $data[ 'city' ] ) || empty( $data[ 'street' ] ) || empty( $data[ 'zipCode' ] ) ) {
			return $shops;
		}

		if ( empty( $terminals ) || ! is_array( $terminals ) ) {
			$terminals = $this->get_cache( 'parcelshops', $data );
		}

		if ( empty( $terminals[ 'last_query' ] ) || ( $terminals[ 'last_query' ] !== json_encode( $data ) ) || empty( json_decode( $terminals[ 'terminals' ] ) ) ) {

			// Mapping old SOAP params to new REST params.
			$request_data = array(
				'streetAddress'   => $data[ 'street' ],
				'addressLocality' => $data[ 'city' ],
				'postalCode'      => $data[ 'zipCode' ],
				'countryCode'     => $data[ 'country' ],
				'locationType'    => array(
					'servicepoint',
					'postoffice',
				),
			);

			$response = $this->do_request_find_locations( $request_data );

			if ( ( 'success' == $response[ 'status' ] ) && isset( $response[ 'terminals' ] ) ) {
				$response_terminals = $response[ 'terminals' ];
				foreach ( $response_terminals->locations as $parcelshop ) {
					if ( empty( $parcelshop->location->keywordId ) ) {
						continue;
					}
					$shop_array = array(
						'parcelshop_id' => $parcelshop->location->keywordId,
						'company'       => ( isset( $parcelshop->name ) ) ? htmlspecialchars( $parcelshop->name ) : '',
						'country'       => WC()->countries->countries[ $parcelshop->place->address->countryCode ],
						'city'          => $parcelshop->place->address->addressLocality,
						'pcode'         => $parcelshop->place->address->postalCode,
						'street'        => $parcelshop->place->address->streetAddress,
						'email'         => '',
						'phone'         => '',
						'mon'           => 'closed',
						'tue'           => 'closed',
						'wed'           => 'closed',
						'thu'           => 'closed',
						'fri'           => 'closed',
						'sat'           => 'closed',
						'sun'           => 'closed',
						'distance'      => $parcelshop->distance,
						'longitude'     => $parcelshop->place->geo->longitude,
						'latitude'      => $parcelshop->place->geo->latitude,
						'cod'           => 0,
					);

					$timeinfo   = $parcelshop->openingHours;
					$timeformat = apply_filters( 'wgm_parcelshops_opening_times_timeformat', get_option( 'time_format' ) );

					foreach ( $timeinfo as $openingHours ) {
						if ( '' != $openingHours->opens ) {
							switch( $openingHours->dayOfWeek ) {
								case 'http://schema.org/Monday':
									$day_key = 'mon';
									break;
								case 'http://schema.org/Tuesday':
									$day_key = 'tue';
									break;
								case 'http://schema.org/Wednesday':
									$day_key = 'wed';
									break;
								case 'http://schema.org/Thursday':
									$day_key = 'thu';
									break;
								case 'http://schema.org/Friday':
									$day_key = 'fri';
									break;
								case 'http://schema.org/Saturday':
									$day_key = 'sat';
									break;
							}
							$shop_array[ $day_key ] = date($timeformat, strtotime($openingHours->opens ) ) . ' - ' . date($timeformat, strtotime($openingHours->closes ) );
						}
					}

					$shops[] = $shop_array;
				}

				$terminals[ 'terminals' ]  = json_encode( $shops );
				$terminals[ 'last_query' ] = json_encode( $data );

				$this->set_cache( 'parcelshops', $terminals, $data );
			}
		} else {
			$shops = json_decode( $terminals[ 'terminals' ], true );
		}

		return $shops;
	}

	/**
	 * Request a Return label.
	 *
	 * @param array $data
	 *
	 * @return mixed
	 */
	public function get_return_label( array $data ) {

		$auth_url = $this->get_base_url() . '/parcel/de/account/auth/ropc/v1/token';
		$response = wp_remote_post(
			$auth_url,
			array(
				'body' => array(
					'grant_type'    => 'password',
					'username'      => $this->api_username,
					'password'      => $this->api_password,
					'client_id'     => self::get_client_id(),
					'client_secret' => self::get_client_secret(),
				),
				'headers' => array(
					'dhl-api-key'     => $this->get_api_key(),
					'Content-Type'    => 'application/x-www-form-urlencoded',
				),
			)
		);

		$response = json_decode( $response[ 'body' ], true );

		if ( ! empty( $response[ 'access_token' ] ) ) {
			$bearer = $response[ 'access_token' ];

			// Build API route.
			$url = $this->get_return_label_route();

			// Request a PDF-Return-Label only. (possible values: SHIPMENT_LABEL, QR_LABEL, BOTH)
			$url = add_query_arg( 'labelType', 'SHIPMENT_LABEL', $url );

			$response = wp_remote_post(
				$url,
				array(
					'body' => json_encode( $data ),
					'headers' => array(
						'Accept-Language' => $this->locale_value,
						'Authorization'   => 'Bearer ' . $bearer,
						'Content-Type'    => 'application/json',
					),
				)
			);

			if ( is_wp_error( $response ) ) {
				Helper::add_flash_notice( 'cURL Error #: ' . $response->get_error_message(), 'error' );
			}
		}

		return $response;
	}

	/**
	 * This function sends our Order Request Array to the API.
	 * The API will give us an array with tracking number and label.
	 *
	 * FOR REST API:
	 * In additional to API Key header "dhl-api-key: ${KEY}", The Parcel DE Shipping API requires that requests send a Basic HTTP Authorization
	 * ie "Authorization: Basic 123456" header, where  "123456" is a base64 encoded username:password (or clientID: clientSecret).
	 *
	 * @param array    $data SOAP request array
	 * @param WC_Order $order order object
	 * @param bool     $validate 'true' by default before sending final request
	 *
	 * @return stdClass|array
	 */
	public function store_order( array $data, WC_Order $order, bool $validate = true ) {

		// Build API route.
		$url = $this->get_parcel_shipping_service() . $this->get_order_route();

		// Disable combined shipping and retoure label printing.
		$url = add_query_arg( 'combine', 'false', $url );

		// Shipment validation
		if ( ( true === $validate ) && ( true === apply_filters( 'wgm_shipping_dhl_store_order_shipment_validation_before_final_request', true ) ) ) {
			$url = add_query_arg( 'validate', 'true', $url );
		}

		// Set label size.
		$url = add_query_arg( 'printFormat', $this->provider::$options->get_option( 'label_size', '910-300-700' ), $url );

		// Uncomment to receive URI to pdf for testing purpose.
		// $url = add_query_arg( 'includeDocs', 'URL', $url );

		// Check for 'printOnlyIfCodable'.
		$must_encode = 'false';
		if ( ( ! empty( $order->get_meta( '_wgm_dhl_service_codeable' ) ) && ( 'on' === $order->get_meta( '_wgm_dhl_service_codeable' ) ) ) || ( empty( $order->get_meta( '_wgm_dhl_service_codeable' ) ) && ( 'on' === $this->provider::$options->get_option( 'service_codeable_default', 'off' ) ) ) ) {
			$must_encode = 'true';
		}
		$url = add_query_arg( 'mustEncode', $must_encode, $url );

		$response = wp_remote_post( $url, array(
			'body' => json_encode( $data ),
			'headers' => array(
				'Accept-Language' => $this->locale_value,
				'Authorization'   => 'Basic ' . base64_encode( $this->api_username . ':' . $this->api_password ),
				'dhl-api-key'     => $this->get_api_key(),
				'Content-Type'    => 'application/json',
			),
		) );

		if ( is_wp_error( $response ) ) {
			Helper::add_flash_notice( 'cURL Error #: ' . $response->get_error_message(), 'error' );
		}

		return $response;
	}

	/**
	 * This method performs an API call to delete an already
	 * stored Parcel within the DHL system.
	 *
	 * @param string|array $shipment_number parcel label number(s)
	 *
	 * @return mixed
	 */
	public function delete_order( $shipment_number ) {

		// Build API route.
		$url = $this->get_parcel_shipping_service() . $this->get_order_route();

		// Set custom profile.
		$url = add_query_arg( 'profile', apply_filters( 'wgm_dhl_parcel_shipment_rest_api_default_profile', 'STANDARD_GRUPPENPROFIL' ), $url );

		// Set shipments to delete.
		if ( is_array( $shipment_number ) ) {
			foreach ( $shipment_number as $shipment_no ) {
				if ( '' != $shipment_no ) {
					$url = add_query_arg( 'shipment', $shipment_no, $url );
				}
			}
		} else {
			$url = add_query_arg( 'shipment', $shipment_number, $url );
		}

		$response = wp_remote_request( $url, array(
			'method'  => 'DELETE',
			'headers' => array(
				'Accept-Language' => $this->locale_value,
				'Authorization'   => 'Basic ' . base64_encode( $this->api_username . ':' . $this->api_password ),
				'dhl-api-key'     => $this->get_api_key(),
				'Content-Type'    => 'application/json',
			),
		) );

		if ( is_wp_error( $response ) ) {
			Helper::add_flash_notice( 'cURL Error #: ' . $response->get_error_message(), 'error' );
		} else {
			$response = json_decode( $response[ 'body' ], true );
		}

		return $response;
	}

	/**
	 * Build the address array from a given order.
	 *
	 * @param WC_Order $order order object
	 *
	 * @return array
	 */
	public function build_address_from_order( WC_Order $order ) : array {

		$address = array();

		if ( ! is_object( $order ) ) {
			return $address;
		}

		$shop_country = Shipping_Provider::$options->get_option( 'shipping_shop_address_country', 'DE' );

		// Fixing params for DHL
		$consignee_name          = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
		$consignee_company       = Helper::custom_length( $order->get_shipping_company(), 40 ); // required 1, max length 40
		$consignee_street        = Helper::get_street_name( $order->get_shipping_address_1() );
		$consignee_house_no      = Helper::get_house_no( $order->get_shipping_address_1() );
		$consignee_city          = Helper::custom_length( $order->get_shipping_city(), 40 ); // required 1, max length 40
		$consignee_pcode         = $order->get_shipping_postcode();
		$consignee_country_code  = $order->get_shipping_country();
		$correct_phone           = Helper::separate_phone_number_from_country_code( $order->get_billing_phone(), $consignee_country_code );
		$consignee_phone         = ( ! empty( $correct_phone[ 'dial_code' ] ) && ! empty( $correct_phone[ 'phone_number' ] ) ) ? $correct_phone[ 'dial_code' ] . ' ' . $correct_phone[ 'phone_number' ] : '';
		$consignee_email         = $order->get_billing_email();

		$is_international = Helper::is_international_shipment( $shop_country, $consignee_country_code, $consignee_pcode );

		if ( $is_international ) {
			$consignee_street = $order->get_shipping_address_1();
		}

		// Converting consignee country code to 3-alpha-iso code.
		$country_data           = ( new ISO3166() )->alpha2( $consignee_country_code );
		$consignee_country_code = $country_data[ 'alpha3' ];

		// Set standard values.
		$address = array(
			'name1'         => $consignee_name,
			'addressStreet' => ( $is_international ? $consignee_street : $consignee_street . ' ' . $consignee_house_no ),
			'postalCode'    => $consignee_pcode,
			'city'          => $consignee_city,
			'country'       => $consignee_country_code,
			'email'         => $consignee_email,
		);

		// Add company name if set.
		if ( ( '' !== $consignee_name ) && ( '' !== $consignee_company ) ) {
			$address[ 'name1' ] = $consignee_company;
			$address[ 'name2' ] = $consignee_name;
		}

		// Add additional address information if set.
		if ( '' !== $order->get_shipping_address_2() ) {
			$address[ 'additionalAddressInformation1' ] = $order->get_shipping_address_2();
		}

		// Add phone number if not empty.
		if ( '' !== $consignee_phone ) {
			$address[ 'phone' ] = $consignee_phone;
		}

		return $address;
	}
}
