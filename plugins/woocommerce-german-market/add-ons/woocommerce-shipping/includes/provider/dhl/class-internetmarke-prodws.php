<?php

namespace MarketPress\GermanMarket\Shipping\Provider\DHL;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use Exception;
use SoapClient;
use SoapFault;
use stdClass;

class Internetmarke_ProdWS
{
	/**
	 * ProdWS Wsdl Url.
	 *
	 * @var string
	 */
	const PRODWS_WSDL_URL = 'https://prodws.deutschepost.de/ProdWSProvider_1_1/prodws?wsdl';

	/**
	 * Product Catalog Credentials.
	 */
	const PRODWS_MANDANTID = 'MARKETPRESS';
	const PRODWS_USERNAME  = 'marketpress';
	const PRODWS_PASSWORD  = 'D&6%bk?db1';

	/**
	 * @var object|null
	 */
	private ?object $prod_ws = null;

	/**
	 * @private
	 *
	 * @var mixed
	 */
	private $internetmarke_products;

	/**
	 * @private
	 *
	 * @var mixed
	 */
	private $internetmarke_product_services;


	/**
	 * @var string
	 */
	private string $api_username;

	/**
	 * @var string
	 */
	private string $api_password;

	/**
	 * Instance placeholder.
	 *
	 * @static
	 *
	 * @var Internetmarke_ProdWS|null
	 */
	public static ?self $instance = null;

	/**
	 * Singleton.
	 *
	 * @static
	 *
	 * @return self
	 */
	public static function get_instance() : self
	{
		return ( null !== static::$instance ) ? self::$instance : self::$instance = new self();
	}

	/**
	 * Class Constructor.
	 *
	 * @private
	 *
	 * @throws SoapFault
	 */
	private function __construct()
	{
		$this->api_username = Shipping_Provider::$options->get_option( 'internetmarke_portokasse_email' );
		$this->api_password = Shipping_Provider::$options->get_option( 'internetmarke_portokasse_password' );

		// Check if API credentials are all set.

		if ( ( '' === $this->api_username ) || ( '' === $this->api_password ) ||
		     ( '' === Api::get_client_id() ) || ( '' === Api::get_client_secret() )
		) {
			return null;
		}

		// Check if COAP extension is available.

		if ( ! extension_loaded('soap' ) || ! class_exists('SoapClient' ) ) {
			return null;
		}

		if ( ( '' === self::PRODWS_MANDANTID ) || ( '' === self::PRODWS_USERNAME ) || ( '' === self::PRODWS_PASSWORD ) ) {
			// Unschedule cron if a credential is missing.
			self::unschedule_cron();
		} else {
			// Schedule cron if we have all credentials.
			self::schedule_cron();
		}

		self::maybe_create_tables();

		add_action( 'german_market_internetmarke_update_product_list', array( $this, 'update_products_services_cron' ) );

		if ( empty( $this->get_internetmarke_products() ) || empty( $this->get_internetmarke_product_services() ) ) {
			$this->load_products();
		}
	}

	/**
	 * @param $internetmarke_product_services
	 *
	 * @return void
	 */
	private function set_internetmarke_product_services( $internetmarke_product_services )
	{
		$this->internetmarke_product_services = $internetmarke_product_services;
	}

	/**
	 * @return mixed
	 */
	public function get_internetmarke_product_services()
	{
		return $this->internetmarke_product_services;
	}

	/**
	 * @private
	 *
	 * @param $internetmarke_products
	 *
	 * @return void
	 */
	private function set_internetmarke_products( $internetmarke_products )
	{
		$this->internetmarke_products = $internetmarke_products;
	}

	/**
	 * @return mixed
	 */
	public function get_internetmarke_products()
	{
		return $this->internetmarke_products;
	}

	/**
	 * Schedules the Internetmarke Cron job.
	 *
	 * @return void
	 */
	public static function schedule_cron()
	{
		$args = array( false );

		if ( ! wp_next_scheduled('german_market_internetmarke_update_product_list', $args ) ) {
			wp_schedule_event(
				time(),
				'daily',
				'german_market_internetmarke_update_product_list',
				$args
			);
		}
	}

	/**
	 * Unschedules the Internetmarke Cron job.
	 */
	public static function unschedule_cron()
	{
		$args = array( false );

		wp_unschedule_event(
			wp_next_scheduled('german_market_internetmarke_update_product_list', $args ),
			'german_market_internetmarke_update_product_list'
		);
	}

	/**
	 * Method for scheduled cron.
	 *
	 * @return void
	 */
	public function update_products_services_cron()
	{
		try {
			$this->update_products_services();
		} catch ( Exception $e ) {}
	}

	/**
	 * Update products and services from the API.
	 *
	 * @return void
	 * @throws SoapFault
	 */
	public function update_products_services()
	{
		global $wpdb;

		if ( null === $this->prod_ws ) {
			$this->init_WSS();
		}

		if ( $this->prod_ws ) {

			$product_list = $this->prod_ws->__soapCall( 'getProductList', array(
				'getProductListRequest' => array(
					'mandantID'         => self::PRODWS_MANDANTID,
					'dedicatedProducts' => true,
					'responseMode'      => 0,
				)
			) );

			// Clearing tables.
			$wpdb->query( "TRUNCATE TABLE " . self::get_products_table() );
			$wpdb->query( "TRUNCATE TABLE " . self::get_products_services_table() );

			$products = array(
				'sales'      => $product_list->Response->salesProductList->SalesProduct,
				'additional' => $product_list->Response->additionalProductList->AdditionalProduct,
				'basic'      => $product_list->Response->basicProductList->BasicProduct
			);

			$products_with_additional_service = array();

			foreach( $products as $product_type => $inner_products ) {

				foreach( $inner_products as $product ) {

					$extended_identifier = $product->extendedIdentifier;
					$extern_identifier   = property_exists( $extended_identifier, 'externIdentifier' ) ? $extended_identifier->externIdentifier : new stdClass();

					// skip product if it has no id
					if ( ! property_exists( $extern_identifier, 'id' ) || empty( $extern_identifier->id ) ) {
						continue;
					}

					$to_insert = array(
						'product_im_id'            => $extended_identifier->{'ProdWS-ID'},
						'product_code'             => property_exists( $extern_identifier, 'id' ) ? $extern_identifier->id : $extended_identifier->{'ProdWS-ID'},
						'product_name'             => property_exists( $extern_identifier, 'name' ) ? $extern_identifier->name : $extended_identifier->name,
						'product_category'         => '',
						'product_type'             => $product_type,
						'product_annotation'       => property_exists( $extended_identifier, 'annotation' ) ? $extended_identifier->annotation : '',
						'product_description'      => property_exists( $extended_identifier, 'description' ) ? $extended_identifier->description : '',
						'product_destination'      => $extended_identifier->destination,
						'product_price'            => property_exists( $product->priceDefinition, 'price' ) ? $this->euros_to_cents( $product->priceDefinition->price->calculatedGrossPrice->value ) : $this->euros_to_cents( $product->priceDefinition->grossPrice->value ),
						'product_information_text' => property_exists( $product, 'stampTypeList' ) ? $this->get_information_text( (array) $product->stampTypeList->stampType ) : '',
					);

					$product_slug     = $this->sanitize_product_slug( $to_insert[ 'product_name' ] );
					$product_category = $this->sanitize_product_category( $product_slug, $to_insert[ 'product_destination' ] );

					$to_insert[ 'product_slug' ]     = $product_slug;
					$to_insert[ 'product_category' ] = $product_category;

					if ( property_exists( $product, 'dimensionList' ) ) {

						$dimensions = $product->dimensionList;

						$to_insert = array_merge( $to_insert, $this->get_dimensions( $dimensions, 'width' ) );
						$to_insert = array_merge( $to_insert, $this->get_dimensions( $dimensions, 'height' ) );
						$to_insert = array_merge( $to_insert, $this->get_dimensions( $dimensions, 'length' ) );
					}

					if ( property_exists( $product, 'weight' ) ) {

						$to_insert = array_merge( $to_insert, $this->get_dimensions( $product, 'weight' ) );
					}

					$to_insert = array_map( 'wc_clean', $to_insert );

					/**
					 * Skip product if this is an additional service
					 */
					if ( $this->is_additional_service( $to_insert[ 'product_slug' ] ) ) {

						$products_with_additional_service[] = $to_insert;
						continue;
					}

					$wpdb->insert( self::get_products_table(), $to_insert );
				}
			}

			foreach ( $products_with_additional_service as $product_to_insert ) {

				$product_base_slug = $this->get_product_base_slug( $product_to_insert[ 'product_slug' ] );
				$parent_product    = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . self::get_products_table() . " WHERE product_slug = %s", $product_base_slug ) );
				$service_slugs     = $this->get_product_service_slugs( $product_to_insert[ 'product_slug' ] );

				if ( ! empty( $parent_product ) && ! empty( $service_slugs ) ) {
					$product_to_insert[ 'product_parent_id' ]     = $parent_product->product_id;
					$product_to_insert[ 'product_service_count' ] = sizeof( $service_slugs );
				}

				$wpdb->insert( self::get_products_table(), $product_to_insert );
				$product_id = $wpdb->insert_id;

				if ( ! empty( $parent_product ) && ! empty( $service_slugs ) ) {
					foreach( $service_slugs as $service_slug ) {
						$service_insert = array(
							'product_service_product_id'        => $product_id,
							'product_service_product_parent_id' => $parent_product->product_id,
							'product_service_slug'              => $service_slug,
						);

						$wpdb->insert( self::get_products_services_table(), $service_insert );
					}
				}
			}

			$this->set_internetmarke_products( $wpdb->get_results( "SELECT * FROM " . self::get_products_table() ) );
			$this->set_internetmarke_product_services( $wpdb->get_results( "SELECT * FROM " . self::get_products_services_table() ) );
		}
	}

	/**
	 * Create database tables for products and services.
	 *
	 * @return void
	 */
	private static function maybe_create_tables()
	{
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// $wpdb->query( "DROP TABLE " . self::get_products_table() );
		// $wpdb->query( "DROP TABLE " . self::get_products_services_table() );

		dbDelta("
CREATE TABLE " . self::get_products_table() . " (
    product_id bigint(20) NOT NULL AUTO_INCREMENT,
    product_im_id INT(16) NOT NULL,
    product_code int(16) NOT NULL,
    product_name varchar(255) NOT NULL,
    product_category varchar(255) NOT NULL,
    product_type varchar(255) NOT NULL,
    product_annotation varchar(1024) NOT NULL,
    product_description varchar(255),
    product_destination varchar(256) NOT NULL,
    product_price int(8) NOT NULL,
    product_information_text varchar(512) NOT NULL,
    product_width_min int(8) NULL,
    product_width_max int(8) NULL,
    product_width_unit varchar(8) NULL,
    product_height_min int(8) NULL,
    product_height_max int(8) NULL,
    product_height_unit varchar(8) NULL,
    product_length_min int(8) NULL,
    product_length_max int(8) NULL,
    product_length_unit varchar(8) NULL,
    product_weight_min int(8) NULL,
    product_weight_max int(8) NULL,
    product_weight_unit varchar(8) NULL,
    product_slug varchar(255) NOT NULL,
    product_parent_id int(16),
    product_service_count int(8),
    PRIMARY KEY (product_id),
    INDEX idx (product_code, product_slug)
) ENGINE=InnoDB $collate;
");

		dbDelta("
CREATE TABLE " . self::get_products_services_table() . " (
	product_service_id bigint(20) NOT NULL AUTO_INCREMENT,
    product_service_product_id int(8) NOT NULL,
    product_service_product_parent_id int(16) NOT NULL,
    product_service_slug varchar(255) NOT NULL,
    PRIMARY KEY (product_service_id)
) ENGINE=InnoDB $collate;
COMMIT;
");
	}

	/**
	 * Try to load products from the Database.
	 *
	 * @return void
	 * @throws SoapFault
	 */
	public function load_products()
	{
		global $wpdb;

		$this->set_internetmarke_products( $wpdb->get_results( "SELECT * FROM " . self::get_products_table() ) );
		$this->set_internetmarke_product_services( $wpdb->get_results( "SELECT * FROM " . self::get_products_services_table() ) );

		if ( empty( $this->get_internetmarke_products() ) || empty( $this->get_internetmarke_product_services() ) ) {
			$this->update_products_services();
		}
	}

	/**
	 * Returns the database table name for products.
	 *
	 * @static
	 *
	 * @return string
	 */
	private static function get_products_table() : string
	{
		global $wpdb;

		return $wpdb->prefix . 'wgm_dhl_internetmarke_products';
	}

	/**
	 * Returns the database table name for product services.
	 *
	 * @static
	 *
	 * @return string
	 */
	private static function get_products_services_table() : string
	{
		global $wpdb;

		return $wpdb->prefix . 'wgm_dhl_internetmarke_products_services';
	}

	/**
	 * Create a connection to the ProdWS service.
	 *
	 * @return void
	 */
	public function init_WSS() {

		$this->prod_ws = new SoapClient( self::PRODWS_WSDL_URL );
		$this->prod_ws->__setSoapHeaders( array(
			new Internetmarke_Wss_Header( self::PRODWS_USERNAME, self::PRODWS_PASSWORD )
		) );
	}

	/**
	 * Converting euro value to cents because Deutsche Post using values in cents.
	 *
	 * @access public
	 *
	 * @param float $amount
	 *
	 * @return float
	 */
	public function euros_to_cents( float $amount ) : float
	{
		return round( $amount * 100 );
	}

	/**
	 * Function to extract the information text from object.
	 *
	 * @param array $stamp_type
	 *
	 * @return mixed
	 */
	public function get_information_text( array $stamp_type )
	{
		$information_text = '';

		foreach ( $stamp_type as $stamp ) {
			if  ( isset( $stamp->name ) && 'Internetmarke' == $stamp->name ) {
				foreach ( $stamp->propertyList as $properties ) {
					foreach ( $properties as $property ) {
						if ( 'InformationText' == $property->name ) {
							$information_text = $property->propertyValue->alphanumericValue->fixValue;
						}
					}
				}
			}
		}

		return $information_text;
	}

	/**
	 * Gets the product slug.
	 *
	 * @param string $product_name
	 *
	 * @return array|string|string[]|null
	 */
	public function sanitize_product_slug( string $product_name )
	{
		$product_name = trim( mb_strtolower( $product_name ) );

		// Remove duplicate whitespaces
		return preg_replace( '/\s+/', ' ', $product_name );
	}

	/**
	 * Gets the product category.
	 *
	 * @param string $product_slug
	 * @param string $product_destination
	 *
	 * @return string
	 */
	public function sanitize_product_category( string $product_slug, string $product_destination ) : string
	{
		/*
		National
		-- Brief / Postkarte
		-- Presse
		International
		-- Brief / Postkarte
		-- Presse
		 */

		$category = '';

		// Presse
		if ( false !== strpos( $product_slug, 'presse' ) ||
		     str_starts_with( $product_slug, 'streifbandzeitung' ) ||
		     false !== strpos( $product_slug, 'warensendung' )
		) {
			$category = 'presse' . ( 'international' === $product_destination ? '-international' : '' );
		} else
		// Brief / Postkarte
		if ( false !== strpos( $product_slug, 'postkarte' ) ||
		     false !== strpos( $product_slug, 'standardbrief' ) ||
		     false !== strpos( $product_slug, 'kompaktbrief' ) ||
		     false !== strpos( $product_slug, 'großbrief' ) ||
		     false !== strpos( $product_slug, 'maxibrief' )
		) {
			$category = 'brief-postkarte' . ( 'international' === $product_destination ? '-international' : '' );
		} else
		// Brief Kilo-Tarif
		if ( false !== strpos( $product_slug, 'brief kilotarif' ) ) {
			$category = 'brief-kilotarif' . ( 'international' === $product_destination ? '-international' : '' );
		}

		return $category;
	}

	/**
	 * Getting dimensions from
	 *
	 * @param $dimensions
	 * @param string $type
	 *
	 * @return null[]
	 */
	public function get_dimensions( $dimensions, string $type = 'width' ) : array
	{
		$data = array(
			'product_' . $type . '_min'  => null,
			'product_' . $type . '_max'  => null,
			'product_' . $type . '_unit' => null,
		);

		if ( property_exists( $dimensions, $type ) ) {
			$d = $dimensions->{ $type };

			$data[ 'product_' . $type . '_min' ]  = property_exists( $d, 'minValue' ) ? $d->minValue : null;
			$data[ 'product_' . $type . '_max' ]  = property_exists( $d, 'maxValue' ) ? $d->maxValue : null;
			$data[ 'product_' . $type . '_unit' ] = property_exists( $d, 'unit' ) ? $d->unit : null;
		}

		return $data;
	}

	/**
	 * @acces public
	 *
	 * @param $slug
	 *
	 * @return bool
	 */
	public function is_additional_service( $slug ) : bool
	{
		$service_slug = $this->get_product_service_slugs( $slug );

		return ! empty( $service_slug );
	}

	/**
	 * @param $slug
	 *
	 * @return array|string|string[]|null
	 */
	public function get_product_base_slug( $slug )
	{
		$additional_services = $this->get_additional_service_identifiers();
		$slug                = str_replace( 'integral', '', $slug );

		foreach( array_keys( $additional_services ) as $identifier ) {
			$slug = str_replace( $identifier, ' ', $slug );
		}

		return $this->sanitize_product_slug( $slug );
	}

	/**
	 * @param $slug
	 *
	 * @return array
	 */
	protected function get_product_service_slugs( $slug ) : array {

		$service_slugs    = array();
		$has_einschreiben = false;

		foreach( $this->get_additional_service_identifiers() as $identifier => $service ) {
			if ( false !== strpos( $slug, $identifier ) ) {
				if ( false !== strpos( $identifier, 'einschreiben' ) ) {
					if ( ! $has_einschreiben ) {
						$has_einschreiben = true;
						$service_slugs[]  = $service;
					}
				} else
					if ( false !== strpos( $slug, $identifier ) ) {
						$service_slugs[] = $service;
					}
			}
		}

		return array_unique( $service_slugs );
	}

	/**
	 * Returns additional services identifiers.
	 *
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_additional_service_identifiers() : array
	{
		return array(
			'+ einschreiben einwurf'       => 'ESEW',
			'+ einschreiben + einwurf'     => 'ESEW',
			'+ einschreiben + eigenhändig' => 'ESEH',
			'+ einschreiben'               => 'ESCH',
			'+ zusatzentgelt mbf'          => 'ZMBF',
			'+ prio'                       => 'PRIO',
			'unterschrift'                 => 'USFT',
			'tracked'                      => 'TRCK',
		);
	}

}
