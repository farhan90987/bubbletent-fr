<?php

namespace MarketPress\GermanMarket\Shipping\Provider\DHL;

use MarketPress\GermanMarket\Shipping\Frontend as Shipping_Frontend;
use MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Home_Delivery;
use MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Packstation;
use MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Parcels;
use MarketPress\GermanMarket\Shipping\Woocommerce_Shipping;
use WC_AJAX;
use WGM_Age_Rating;
use WP_Error;
use DateInterval;
use DateTime;
use Exception;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

class Frontend extends Shipping_Frontend {

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
	 * Singleton getInstance.
	 *
	 * @static
	 *
	 * @param string $id
	 *
	 * @return self
	 */
	public static function get_instance( string $id ) : self {

		return ( null !== self::$instance ) ? self::$instance : self::$instance = new self( $id );
	}

	/**
	 * Class constructor.
	 *
	 * @param string $id
	 */
	public function __construct( string $id ) {

		parent::__construct( $id );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @return void
	 */
	public function enqueue_styles() {

		wp_enqueue_style( Shipping_Provider::get_instance()->handle, plugin_dir_url( __FILE__ ) . 'assets/css/frontend' . WGM_SHIPPING_MINIFY . '.css', array(), WGM_SHIPPING_VERSION );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {

		$dependencies = array();

		if ( is_cart() || is_checkout() ) {

			$select_woo_active = $this->version_check( '3.2' );
			$dependencies      = array( 'jquery' );
			if ( $select_woo_active ) {
				$dependencies[] = 'selectWoo';
			}
		}

		wp_enqueue_script( Shipping_Provider::get_instance()->handle, plugin_dir_url( __FILE__ ) . 'assets/js/frontend' . WGM_SHIPPING_MINIFY . '.js', $dependencies, WGM_SHIPPING_VERSION );

		$google_map_enabled = Shipping_Provider::$options->get_option( 'google_map_enabled', 'off' );
		$google_map_key     = Shipping_Provider::$options->get_option( 'google_map_key' );

		if ( ( 'on' == $google_map_enabled ) && ( '' != $google_map_key ) ) {

			wp_enqueue_script( 'gmaps-markerclusterer', plugin_dir_url( __FILE__ ) . '../../../assets/js/gmaps-markerclusterer' . WGM_SHIPPING_MINIFY . '.js', array( 'jquery' ), WGM_SHIPPING_VERSION, true );
			wp_enqueue_script( 'parcel', plugin_dir_url( __FILE__ ) . 'assets/js/parcel' . WGM_SHIPPING_MINIFY . '.js', array( 'jquery' ), WGM_SHIPPING_VERSION, true );

			wp_localize_script( 'gmaps-markerclusterer', $this->id, array(
				'ajax_url'        => WC()->ajax_url(),
				'wc_ajax_url'     => WC_AJAX::get_endpoint( '%%endpoint%%' ),
				'ajax_nonce'      => wp_create_nonce( 'save-terminal' ),
				'theme_uri'       => plugin_dir_url( __FILE__ ) . 'assets/images/',
				'gmap_api_key'    => $google_map_key,
				'info_box_string' => __( 'WooCommerce has changed your selected payment method. Please check the selected payment method.', 'woocommerce-german-market' ),
			) );

		}
	}

	/**
	 * Checks if customer provided their DHL specific customer numbers.
	 * This number is needed for 'DHL Packsatationen' and 'DHL Filiale'.
	 *
	 * @Hook woocommerce_checkout_process
	 *
	 * @return void
	 */
	public function checkout_client_number_process() {

		if ( isset( WC()->session ) ) {
			$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
			if ( ! empty( $chosen_shipping_methods ) ) {
				if ( substr( $chosen_shipping_methods[ 0 ], 0, strlen( Packstation::get_instance()->id ) ) == Packstation::get_instance()->id ||
				     substr( $chosen_shipping_methods[ 0 ], 0, strlen( Parcels::get_instance()->id ) ) == Parcels::get_instance()->id
				) {
					if ( ! $_POST[ 'wc_shipping_dhl_client_number' ] ) {
						wc_add_notice( __( 'Your DHL client number is mandatory for using packstations/parcelshops.', 'woocommerce-german-market' ), 'error' );
					} else {
						if ( ! is_numeric( $_POST[ 'wc_shipping_dhl_client_number' ] ) ) {
							wc_add_notice( __( 'Your DHL client number is wrong. It contains 6-10 Digits.', 'woocommerce-german-market' ), 'error' );
						} else if ( strlen( $_POST[ 'wc_shipping_dhl_client_number' ] ) < 6 || strlen( $_POST[ 'wc_shipping_dhl_client_number' ] ) > 10 ) {
							wc_add_notice( __( 'Your DHL client number is wrong. It contains 6-10 Digits only.', 'woocommerce-german-market' ), 'error' );
						} else {
							if ( is_user_logged_in() ) {
								// Store DHL Client ID in User meta data
								$user_id = get_current_user_id();
								update_user_meta( $user_id, 'wc_shipping_dhl_client_number', $_POST[ 'wc_shipping_dhl_client_number' ] );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Check available payment gateways and disable 'Cash on Delivery'
	 * when 'DHL Packstation' is used.
	 *
	 * @Hook woocommerce_available_payment_gateways
	 *
	 * @param array $available_gateways
	 *
	 * @return array
	 */
	public function available_payment_gateways( array $available_gateways ) : array {

		if ( isset( $available_gateways[ 'cod' ] ) ) {
			if ( isset( WC()->session ) ) {
				$cod                     = WC()->session->get( 'cod_for_parcel' );
				$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
				if ( '0' === $cod ) {
					unset( $available_gateways[ 'cod' ] );

					return $available_gateways;
				} else
				if ( ! empty( $chosen_shipping_methods ) ) {
					$selected_terminal = false;

					if ( substr( $chosen_shipping_methods[ 0 ], 0, strlen ( Parcels::get_instance()->id ) ) === Parcels::get_instance()->id ) {
						$selected_terminal = WC()->session->get( Parcels::get_instance()->field_id );
					} else
					if ( substr( $chosen_shipping_methods[ 0 ], 0, strlen( Packstation::get_instance()->id ) ) === Packstation::get_instance()->id ) {
						$selected_terminal = WC()->session->get( Packstation::get_instance()->field_id );
					}

					if ( false !== $selected_terminal ) {
						$provider = Woocommerce_Shipping::get_instance()->get_provider_by_id( $this->id );
						$terminal = $provider::$backend->get_terminal_by_id( $selected_terminal );
						if ( 0 < count( $terminal ) ) {
							if ( 0 == $terminal[ 'cod' ] ) {
								unset( $available_gateways[ 'cash_on_delivery' ] );
								return $available_gateways;
							}
						}
					}
				}
			} else {
				unset( $available_gateways[ 'cash_on_delivery' ] );
				return $available_gateways;
			}

			if ( isset( WC()->cart ) && isset( WC()->customer ) ) {
				$total = WC()->cart->get_displayed_subtotal();

				if ( WC()->cart->display_prices_including_tax() ) {
					$total = round( $total - ( WC()->cart->get_discount_total() + WC()->cart->get_discount_tax() ), wc_get_price_decimals() );
				} else {
					$total = round( $total - WC()->cart->get_discount_total(), wc_get_price_decimals() );
				}

				$max_total = 3500; // Maximal 3500 Euro

				if ( $total > $max_total ) {
					unset( $available_gateways[ 'cash_on_delivery' ] );
					return $available_gateways;
				}
			}
		}

		return $available_gateways;
	}

	/**
	 * Checks if we're in checkout and need to add date of birth input fields if a DHL shipping method is chosen.
	 * We're using the German Market function 'get_age_rating_of_cart_or_order' for that.
	 *
	 * @Wp-hook wp
	 * @Hook woocommerce_checkout_update_order_review
	 *
	 * @param array $post_data
	 *
	 * @return void
	 */
	public function maybe_checkout_add_dob_field( $post_data = array() ) {

		if ( is_checkout() && isset( WC()->session ) && method_exists( WC()->session, 'get' ) ) {
			
			$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

			if ( is_array( $chosen_shipping_methods ) && isset( $chosen_shipping_methods[ 0 ] ) ) {
				
				$chosen_shipping_method = strtolower( $chosen_shipping_methods[ 0 ] );
				if ( '' !== $chosen_shipping_method && ( false !== strpos( $chosen_shipping_method, Parcels::get_instance()->id ) || false !== strpos( $chosen_shipping_method, Home_Delivery::get_instance()->id ) ) ) {

					$ar_instance = WGM_Age_Rating::get_instance();
					$age_rating  = $ar_instance::get_age_rating_of_cart_or_order();
					$default     = Shipping_Provider::$options->get_option( 'service_ident_check_default', 0 );

					if (
						( 'on' === get_option( 'german_market_age_rating', 'off' ) && ( 0 !== $age_rating ) && ( 0 != $default ) ) ||
						( 'off' === get_option( 'german_market_age_rating', 'off' ) && ( 0 != $default ) )
					) {
						add_filter( 'woocommerce_billing_fields', array( $this, 'checkout_add_dob_field' ), 10, 1 );
					}
				}
			}
		}
	}

	/**
	 * Adds the Date of Birth field to the checkout.
	 *
	 * @Hook woocommerce_billing_fields
	 *
	 * @access public
	 *
	 * @param array $fields checkout billing fields
	 *
	 * @return array
	 * @throws Exception
	 */
	public function checkout_add_dob_field( array $fields ) : array {

		$today           = current_time('Y-m-d' );
		$today_object    = new DateTime( $today );
		$date_min_object = clone $today_object;
		$date_min_object->sub( new DateInterval( 'P' . apply_filters( 'wgm_shipping_maximal_customer_age_in_years', 100 ) . 'Y' ) );
		$date_max_object = clone $today_object;
		$date_max_object->sub( new DateInterval( 'P' . apply_filters( 'wgm_shipping_minimal_customer_age_in_years', 14 ) . 'Y' ) );

		$fields[ 'billing_dob' ] = array(
			'type'              => 'date',
			'class'             => array( 'shipping-dhl-dob form-row-wide' ),
			'id'                => 'billing_dob',
			'required'          => true,
			'label'             => __( 'Date of birth', 'woocommerce-german-market' ),
			'placeholder'       => __( 'Date of birth', 'woocommerce-german-market' ),
			'custom_attributes' => array(
				'min' => $date_min_object->format( 'Y-m-d' ),
				'max' => $date_max_object->format( 'Y-m-d' ),
			)
		);

		return $fields;
	}

	/**
	 * Save personal id field into order meta.
	 *
	 * @Hook woocommerce_checkout_update_order_meta
	 *
	 * @param int $order_id
	 *
	 * @return void
	 */
	public function process_checkout_dob_field( int $order_id ) {

		$order = wc_get_order( $order_id );

		if ( ! isset( WC()->session ) ) {
			return;
		}

		$dob = WC()->session->get( 'wc_shipping_dhl_dob', '' );

		if ( is_object( $order ) && ( '' !== $dob ) ) {
			$order->update_meta_data( 'billing_dob', $dob );
			$order->save();
		}
	}

	/**
	 * Validates the date of birth field if DHL shipping is chosen.
	 *
	 * @Hook woocommerce_after_checkout_validation
	 *
	 * @param array    $fields posted data
	 * @param WP_Error $errors
	 *
	 * @return void
	 */
	public function checkout_validate_dob_field( array $fields, WP_Error $errors ) {

		$ar_instance            = WGM_Age_Rating::get_instance();
		$age_rating             = $ar_instance::get_age_rating_of_cart_or_order();
		$default                = Shipping_Provider::$options->get_option( 'service_ident_check_default', 0 );
		$chosen_shipping_method = ! empty( WC()->session->get( 'chosen_shipping_methods' )[ 0 ] ) ? strtolower( WC()->session->get( 'chosen_shipping_methods' )[ 0 ] ) : '';

		if ( '' !== $chosen_shipping_method && ( false !== strpos( $chosen_shipping_method, Parcels::get_instance()->id ) || false !== strpos( $chosen_shipping_method, Home_Delivery::get_instance()->id ) ) ) {

			if (
				( 'on' === get_option( 'german_market_age_rating', 'off' ) && ( 0 !== $age_rating ) && ( 0 != $default ) ) ||
				( 'off' === get_option( 'german_market_age_rating', 'off' ) && ( 0 != $default ) )
			) {
				if ( ! isset( $_POST[ 'billing_dob' ] ) || empty( trim( $_POST[ 'billing_dob' ] ) ) ) {
					$errors->add( 'dob', __( 'This shipping needs an ID check through DHL. Please provide your date of birth.', 'woocommerce-german-market' ) );
				} else {
					WC()->session->set( 'wc_shipping_dhl_dob', sanitize_text_field( $_POST[ 'billing_dob' ] ) );
				}
			}
		}
	}

}
