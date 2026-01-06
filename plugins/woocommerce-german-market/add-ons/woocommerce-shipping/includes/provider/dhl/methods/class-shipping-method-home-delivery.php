<?php

namespace MarketPress\GermanMarket\Shipping\Provider\DHL\Methods;

use MarketPress\GermanMarket\Shipping\Helper;
use MarketPress\GermanMarket\Shipping\Provider\DHL\Shipping_Provider;
use WC_Eval_Math;
use WC_Order;
use WC_Shipping_Method;
use Exception;
use WP_Error;
use DateInterval;
use DateTime;
use function DeepCopy\deep_copy;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

class Home_Delivery extends WC_Shipping_Method {

	/**
	 * Singleton.
	 *
	 * @acces public
	 * @static
	 *
	 * @var self
	 */
	public static $instance;

	/**
	 * Minimum amount for shipping method.
	 */
	public $minimum_amount = '';

	/**
	 * Min amount for free shipping.
	 */
	public $free_min_amount = '';

	/**
	 * Price calculation type.
	 */
	public $type = 'order';

	/**
	 * Price cost rates.
	 */
	public $cost_rates = '';

	/**
	 * Weight based calculation
	 */
	public $calc_weight_based = '';

	/**
	 * Maximum parcel weight limit.
	 */
	public float $parcels_delivery_limit_kg;

	/**
	 * Price.
	 *
	 * @acces public
	 *
	 * @var string
	 */
	public string $cost;

	/**
	 * @var
	 */
	private $fee_cost;

	/**
	 * Singleton getInstance.
	 *
	 * @static
	 *
	 * @return self
	 */
	public static function get_instance() : self {

		return ( null !== self::$instance ) ? self::$instance : self::$instance = new self();
	}

	/**
	 * Class Constructor.
	 *
	 * @param int $instance_id
	 */
	public function __construct( $instance_id = 0 ) {

		parent::__construct();

		$shop_address_notice = '';
		if ( false === Helper::check_shop_address_settings() ) {
			$shop_address_notice = '<br><small style="color: red;">' . __( 'This shipping method is currently not available in cart / checkout. You need to fill in your shop address details on <a target="_blank" href="' . admin_url( 'admin.php?page=german-market&tab=wgm-shipping-dhl&sub_tab=dhl-shop-address' ) . '">this setting page</a>.', 'woocommerce-german-market' ) . '</small>';
		}

		$this->id                 = 'dhl_home_delivery';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'DHL Standard', 'woocommerce-german-market' );
		$this->method_description = __( 'DHL Standard shipping method', 'woocommerce-german-market' ) . ' <small><em>' . __( 'Provided by German Market.', 'woocommerce-german-market' ) . '</em></small>' . $shop_address_notice;
		$this->supports           = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);

		$this->parcels_delivery_limit_kg = 31.9;

		$this->init();

		do_action( 'wgm_shipping_method_after_init', $this );
	}

	/**
	 * Init our settings
	 *
	 * @return void
	 */
	public function init() {

		$this->init_form_fields();

		$this->title             = $this->get_option( 'title', $this->method_title );
		$this->tax_status        = $this->get_option( 'tax_status' );
		$this->cost              = $this->get_option( 'cost' );
		$this->minimum_amount    = $this->get_option( 'minimum_amount', '' );
		$this->free_min_amount   = $this->get_option( 'free_min_amount', '' );
		$this->type              = $this->get_option( 'type', 'order' );
		$this->calc_weight_based = $this->get_option( 'calc_weight_based' );
		$this->cost_rates        = $this->get_option( 'cost_rates' );

		add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ] );
	}

	/**
	 * Add some WooCommerce actions and filters.
	 *
	 * @return void
	 */
	public function init_actions_and_filters() {

		if ( 'on' === Shipping_Provider::$options->get_option( 'service_preferred_day_enabled', 'off' ) ) {
			add_action( 'wp_enqueue_scripts',                      array( $this, 'enqueue_datepicker' ) );
			add_action( 'woocommerce_checkout_update_order_meta',  array( $this, 'checkout_save_dhl_preferred_day' ), 10, 2 );
			add_action( 'woocommerce_review_order_after_shipping', array( $this, 'review_order_after_shipping' ) );
			add_filter( 'woocommerce_get_order_item_totals', array( $this, 'add_delivery_date_to_order_received_page' ), 21 , 3 );
		}

		add_action( 'woocommerce_after_checkout_validation', array( $this, 'validate_checkout_house_no' ), 10, 2 );
		add_action( 'woocommerce_after_checkout_validation', array( $this, 'validate_preferred_delivery_day' ), 10, 2 );
	}

	/**
	 * Define settings field for this shipping
	 *
	 * @return void
	 */
	public function init_form_fields() {

		$cost_link = sprintf( '<span id="wc-shipping-advanced-costs-help-text">%s <a target="_blank" href="https://woo.com/document/flat-rate-shipping/#advanced-costs">%s</a>.</span>', __( 'Charge a flat rate per item, or enter a cost formula to charge a percentage based cost or a minimum fee. Learn more about', 'woocommerce' ), __( 'advanced costs', 'woocommerce' ) );
		$cost_desc = __( 'Enter a cost (excl. tax) or sum, e.g. <code>10.00 * [qty]</code>.', 'woocommerce' ) . '<br/><br/>' . __( 'Use <code>[qty]</code> for the number of items, <br/><code>[cost]</code> for the total cost of items, and <code>[fee percent="10" min_fee="20" max_fee=""]</code> for percentage based fees.', 'woocommerce' );

		$settings = array(
			'title'           => array(
				'title'       => __( 'Method title', 'woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
				'default'     => __( 'DHL Standard', 'woocommerce-german-market' ),
				'desc_tip'    => true,
			),
			'tax_status'      => array(
				'title'   => __( 'Tax status', 'woocommerce' ),
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'default' => 'taxable',
				'options' => array(
					'taxable' => __( 'Taxable', 'woocommerce' ),
					'none'    => _x( 'None', 'Tax status', 'woocommerce' ),
				),
			),
			'cost'            => array(
				'title'       => __( 'Cost', 'woocommerce' ),
				'type'        => 'text',
				'placeholder' => '',
				'description' => '',
				'default'     => '0',
				'desc_tip'    => true,
			),
			'minimum_amount'  => array(
				'title'       => __( 'Minimum order amount for activating shipping method', 'woocommerce-german-market' ),
				'type'        => 'price',
				'placeholder' => '',
				'description' => __( 'Users have to spend this amount to be able to use this shipping method.', 'woocommerce-german-market' ),
				'default'     => '0',
				'desc_tip'    => true,
			),
			'free_min_amount' => array(
				'title'       => __( 'Minimum order amount for free shipping', 'woocommerce-german-market' ),
				'type'        => 'price',
				'placeholder' => '',
				'description' => __( 'Users have to spend this amount to get free shipping.', 'woocommerce-german-market' ),
				'default'     => '',
				'desc_tip'    => true,
			),
		);

		// Get shipping classes.

		$shipping_classes = get_terms(
			'product_shipping_class',
			array(
				'hide_empty' => '0',
				'orderby'    => 'name',
			)
		);

		if ( is_wp_error( $shipping_classes ) ) {
			$shipping_classes = array();
		};

		if ( ! empty( $shipping_classes ) ) {

			$settings[ 'class_costs' ] = array(
				'title'       => __( 'Shipping class costs', 'woocommerce' ),
				'type'        => 'title',
				'default'     => '',
				/* translators: %s: URL for link */
				'description' => sprintf( __( 'These costs can optionally be added based on the <a target="_blank" href="%s">product shipping class</a>. Learn more about <a target="_blank" href="https://woo.com/document/flat-rate-shipping/#shipping-classes">setting shipping class costs</a>.', 'woocommerce' ), admin_url( 'admin.php?page=wc-settings&tab=shipping&section=classes' ) ),
			);

			foreach ( $shipping_classes as $shipping_class ) {

				if ( ! isset( $shipping_class->term_id ) ) {
					continue;
				}

				$settings[ 'class_cost_' . $shipping_class->term_id ] = array(
					/* translators: %s: shipping class name */
					'title'             => sprintf( __( '"%s" shipping class cost', 'woocommerce' ), esc_html( $shipping_class->name ) ),
					'type'              => 'text',
					'class'             => 'wc-shipping-modal-price',
					'placeholder'       => __( 'N/A', 'woocommerce' ),
					'description'       => $cost_desc,
					'default'           => $this->get_option( 'class_cost_' . $shipping_class->slug ), // Before 2.5.0, we used slug here which caused issues with long setting names.
					'desc_tip'          => true,
					'sanitize_callback' => array( $this, 'sanitize_cost' ),
				);
			}

			$settings[ 'no_class_cost' ] = array(
				'title'             => __( 'No shipping class cost', 'woocommerce' ),
				'type'              => 'text',
				'class'             => 'wc-shipping-modal-price',
				'placeholder'       => __( 'N/A', 'woocommerce' ),
				'description'       => $cost_desc,
				'default'           => '',
				'desc_tip'          => true,
				'sanitize_callback' => array( $this, 'sanitize_cost' ),
			);

			$settings[ 'type' ] = array(
				'title'       => __( 'Calculation type', 'woocommerce' ),
				'type'        => 'select',
				'class'       => 'wc-enhanced-select',
				'default'     => 'class',
				'options'     => array(
					'class' => __( 'Per class: Charge shipping for each shipping class individually', 'woocommerce' ),
					'order' => __( 'Per order: Charge shipping for the most expensive shipping class', 'woocommerce' ),
				),
				'description' => $cost_link,
			);

			// We do this at this point to check for selected calculation type 'weight' backwards.
			$this->instance_form_fields = $settings;

			$settings[ 'calc_weight_based' ] = array(
				'title'       => __( 'Calculation type', 'woocommerce' ) . ' - ' . __( 'DHL', 'woocommerce-german-market' ),
				'type'        => 'checkbox',
				'label'       => __( 'Weight based', 'woocommerce-german-market' ),
				'class'       => '',
				'default'     => ( 'weight' === $this->get_option( 'type' ) ? 'yes' : 'no' ),
				'description' => __( 'Activate this checkbox to calculate shipping costs based on product weight in cart.', 'woocommerce-german-market' ),
			);

			$settings[ 'cost_rates' ] = array(
				'title'       => __( 'Rates', 'woocommerce-german-market' ),
				'type'        => 'textarea',
				'placeholder' => '',
				'description' => __( 'Example: 5:10.00,7:12.00 | Description: price up to 5kg = standard cost of this shipping method, price from 5kg = 10,00€, price from 7kg = 12,00€', 'woocommerce-german-market' ),
				'default'     => '',
				'desc_tip'    => true,
			);

		} else {

			$settings[ 'type' ] = array(
				'title'   => __( 'Calculation type', 'woocommerce' ),
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'default' => 'order',
				'options' => array(
					'order'  => __( 'Per order', 'woocommerce-german-market' ),
					'weight' => __( 'Weight based', 'woocommerce-german-market' ),
				),
				'description' => __( 'For calculation type “Weight-based”: Enter fees here to calculate the shipping costs based on the product weight in the shopping cart. To do this, the product weight must be defined on the product under Shipping.', 'woocommerce-german-market' ),
			);

			$settings[ 'cost_rates' ] = array(
				'title'       => __( 'Rates', 'woocommerce-german-market' ),
				'type'        => 'textarea',
				'placeholder' => '',
				'description' => __( 'Example: 5:10.00,7:12.00 | Description: price up to 5kg = standard cost of this shipping method, price from 5kg = 10,00€, price from 7kg = 12,00€', 'woocommerce-german-market' ),
				'default'     => '',
				'desc_tip'    => true,
			);

		}

		$this->instance_form_fields = $settings;
	}

	/**
	 * Load date picker js in checkout.
	 *
	 * @Hook wp_enqueue_scripts
	 *
	 * @return void
	 */
	public function enqueue_datepicker() {

		if ( is_checkout() ) {
			// Load the datepicker script (pre-registered in WordPress).
			wp_enqueue_script( 'jquery-ui-datepicker' );
			// You need styling for the datepicker. For simplicity, I've linked to Google's hosted jQuery UI CSS.
			wp_register_style( 'jquery-ui', '//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css' );
			wp_enqueue_style( 'jquery-ui' );
		}
	}

	/**
	 * Display selected terminal information in order details.
	 *
	 * @Hook woocommerce_get_order_item_totals
	 *
	 * @param array        $total_rows
	 * @param WC_Order|int $order
	 * @param mixed        $tax_display
	 *
	 * @return array
	 */
	public function add_delivery_date_to_order_received_page( array $total_rows, $order, $tax_display ) {

		if ( is_int( $order) ) {
			$order = wc_get_order( $order );
		}

		$preferred_day = $order->get_meta( '_wgm_dhl_service_preferred_day' );

		if ( $order->has_shipping_method( $this->id ) && ( '' != $preferred_day ) ) {

			Helper::array_insert( $total_rows, 'shipping', array(
				'shipping_delivery_date' => array(
					'label' => __( 'Preferred delivery date', 'woocommerce-german-market' ) . ':',
					'value' => date( 'd.m.Y', strtotime( $preferred_day ) ),
				),
			) );
		}

		return $total_rows;
	}

	/**
	 * Save the given / posted DHL client number into order meta.
	 *
	 * @Hook woocommerce_checkout_update_order_meta
	 *
	 * @param int $order_id
	 * @param $data
	 *
	 * @return void
	 */
	public function checkout_save_dhl_preferred_day( $order_id, $data ) {

		if ( isset( $_POST[ 'wgm_dhl_service_preferred_day' ] ) && ( '' != $_POST[ 'wgm_dhl_service_preferred_day' ] ) ) {
			$order = wc_get_order( $order_id );
			$order->update_meta_data( '_wgm_dhl_service_preferred_day', sanitize_text_field( $_POST[ 'wgm_dhl_service_preferred_day' ] ) );
			$order->save();
		}
	}

	/**
	 * Checks if the Google Maps feature in enabled and load the Map template or shows a select box.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function review_order_after_shipping() {

		$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

		if ( ! empty( $chosen_shipping_methods ) && substr( $chosen_shipping_methods[ 0 ], 0, strlen( $this->id ) ) === $this->id ) {

			$preferred_day_enabled         = ( 'on' === Shipping_Provider::$options->get_option( 'service_preferred_day_enabled', 'off' ) && ( Shipping_Provider::$options->get_option( 'shipping_shop_address_country' ) == WC()->customer->get_shipping_country() ) ? 'on' : 'off' );
			$preferred_day                 = WC()->session->get( '_wgm_dhl_service_preferred_day' );
			$preferred_day_service_price   = Shipping_Provider::$options->get_option( 'service_preferred_day_fee', 1.2 );

			$preferred_day_service_price_string = '';

			if ( $preferred_day_service_price > 0 ) {
				$preferred_day_service_price_string = '<p class="wgm-shipping-dhl-preferred-day-price-string">' . sprintf( __( 'There is an additional charge of <span class="price">%s</span> (%s) for this service.', 'woocommerce-german-market' ), wc_price( $preferred_day_service_price ), __( 'incl VAT', 'woocommerce-german-market' ) ) . '</p>';
			}

			if ( 'on' !== $preferred_day_enabled ) {
				WC()->session->__unset( 'dhl_use_delivery_day' );
				WC()->session->__unset( '_wgm_dhl_service_preferred_day' );
			}

			$template_data = array(
				'preferred_day_enabled'              => $preferred_day_enabled,
				'preferred_day'                      => ! empty( $preferred_day ) ? $preferred_day : '',
				'preferred_day_service_price_string' => $preferred_day_service_price_string,
				'preferred_day_first_date'           => Shipping_Provider::calculate_first_preferred_delivery_day(),
			);

			do_action( 'dhl_home_before_preferred_day' );

			if ( 'on' === $preferred_day_enabled ) {
				wc_get_template( 'checkout/form-shipping-dhl-home-delivery.php', $template_data );
			}

			do_action( 'dhl_home_after_preferred_day' );
		}
	}

	/**
	 * Checks if the provided address contains a street name and house no.
	 * If not, we're adding an error message for the customer.
	 *
	 * @Hook woocommerce_after_checkout_validation
	 *
	 * @param array $data posted data
	 * @param object $errors
	 *
	 * @return void
	 */
	public function validate_checkout_house_no( $data, $errors ) {

		// checks if method is chosen
		if ( ! empty( $data[ 'shipping_method' ] ) && is_array( $data[ 'shipping_method' ] ) && preg_match( '/^(' . $this->id . ')([\:0-9]+)$/i', $data[ 'shipping_method' ][ 0 ] ) ) {
			// checks if billing_address_1 or shipping_address_1 contains any house number
			if ( ! preg_match('/^([^\d]*[^\d\s]) *(\d.*)$/', $data[ 'billing_address_1' ] ) || ( isset( $data[ 'shipping_address_1' ] ) && ! preg_match('/^([^\d]*[^\d\s]) *(\d.*)$/', $data[ 'shipping_address_1' ] ) ) ) {

				$errors->add( 'house_no', __( 'Sorry, but the address you provided does not contain a house number.', 'woocommerce-german-market' ) );
			}
		}
	}

	/**
	 * This function checks if a given terminal / filial is valid.
	 *
	 * @Hook woocommerce_after_checkout_validation
	 *
	 * @param array    $posted checkout fields
	 * @param WP_Error $errors errors object
	 *
	 * @return void
	 * @throws Exception
	 */
	public function validate_preferred_delivery_day( $posted, $errors ) {

		if ( empty( WC()->session->get( '_wgm_dhl_service_preferred_day' ) ) ) {
			return;
		}

		$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

		// Check for current shipping method.

		if ( ! empty( $chosen_shipping_methods ) && substr( $chosen_shipping_methods[ 0 ], 0, strlen( $this->id ) ) !== $this->id ) {
			return;
		}

		// We are using 'deep_copy' function to clone the DateTime object.

		$preferred_first_day           = Shipping_Provider::calculate_first_preferred_delivery_day( true );
		$preferred_last_day            = deep_copy( $preferred_first_day );
		$preferred_last_day            = $preferred_last_day->add( DateInterval::createFromDateString( '6 days' ) );
		$preferred_customer_day_string = WC()->session->get( '_wgm_dhl_service_preferred_day' );
		$preferred_customer_day        = new DateTime( $preferred_customer_day_string, wp_timezone() );

		// Check for valid date format e.g. 2023-12-31

		if ( ! preg_match( '/^([19|20]+\d\d)[-](0[1-9]|1[012])[-.](0[1-9]|[12][0-9]|3[01])$/', $preferred_customer_day_string ) ) {
			$errors->add( 'date_format', __( 'The preferred delivery date format is invalid.', 'woocommerce-german-market' ) );
			return;
		}

		// Check if we have a valid date.

		$check_date = date_parse( $preferred_customer_day_string );

		if ( ! empty( $check_date[ 'warnings' ] ) ) {
			$errors->add( 'invalid_date', __( 'The preferred delivery date is not a valid date.', 'woocommerce-german-market' ) );
			return;
		}

		// Check for Sundays

		$day_of_week = date('w', $preferred_customer_day->getTimestamp() );

		if ( 0 == $day_of_week ) {
			$errors->add( 'date_not_allowed', __( 'The chosen preferred delivery date is a sunday.', 'woocommerce-german-market' ) );
			return;
		}

		// Check if preferred date is between first and last possible date.

		if ( ( $preferred_customer_day < $preferred_first_day ) || ( $preferred_customer_day > $preferred_last_day ) ) {
			$errors->add( 'date_not_allowed', __( 'The preferred delivery date is not allowed.', 'woocommerce-german-market' ) );
			return;
		}

	}

	/**
	 * Get setting form fields for instances of this shipping method within zones.
	 *
	 * @return array
	 */
	public function get_instance_form_fields() {

		if ( is_admin() ) {
			wc_enqueue_js(
				"jQuery( function( $ ) {
					function wc" . $this->id . "ShowHideRatesField( el ) {
						var form = $( el ).closest( 'form' );
						var ratesField = $( '#woocommerce_" . $this->id . "_cost_rates', form ).closest( 'tr' );
						if ( 'weight' !== $( el ).val() || '' === $( el ).val() ) {
							ratesField.hide();
						} else {
							ratesField.show();
						}
					}

					$( document.body ).on( 'change', '#woocommerce_" . $this->id . "_type', function() {
						wc" . $this->id . "ShowHideRatesField( this );
					});

					// Change while load.
					$( '#woocommerce_" . $this->id . "_type' ).change();
					$( document.body ).on( 'wc_backbone_modal_loaded', function( evt, target ) {
						if ( 'wc-modal-shipping-method-settings' === target ) {
							wc" . $this->id . "ShowHideRatesField( $( '#wc-backbone-modal-dialog #woocommerce_" . $this->id . "_type', evt.currentTarget ) );
						}
					} );
				});"
			);
		}

		return parent::get_instance_form_fields();
	}

	/**
	 * This function is used to calculate the shipping cost.
	 * Within this function we can check for weights, dimensions and other parameters.
	 *
	 * @param mixed $package
	 *
	 * @return void
	 */
	public function calculate_shipping( $package = array() ) {

		$free_shipping = false;
		$cost          = floatval( $this->get_option( 'cost' ) );
		$weight_based  = $this->get_option( 'calc_weight_based' );
		$weight        = isset( WC()->cart ) ? WC()->cart->get_cart_contents_weight() : 0;

		if ( WC()->cart && ! empty( $this->free_min_amount ) && $this->free_min_amount > 0 ) {
			$total = WC()->cart->get_displayed_subtotal();

			if ( WC()->cart->display_prices_including_tax() ) {
				$total = round( $total - ( WC()->cart->get_discount_total() + WC()->cart->get_discount_tax() ), wc_get_price_decimals() );
			} else {
				$total = round( $total - WC()->cart->get_discount_total(), wc_get_price_decimals() );
			}

			if ( $total >= $this->free_min_amount ) {
				$free_shipping = true;
				$cost          = 0;
			}
		}

		if ( ! $free_shipping && ( ( 'weight' == $this->type ) || ( 'yes' === $weight_based ) ) ) {
			$rates = explode( ',', $this->cost_rates );

			foreach ( $rates as $rate ) {
				$data = explode( ':', $rate );

				if ( $weight >= $data[ 0 ] ) {
					if ( isset( $data[ 1 ] ) ) {
						$cost = floatval( str_replace( ',', '.', $data[ 1 ] ) );
					}
				}
			}

			if ( $cost < 0 ) {
				$cost = 0;
			}
		}

		/**
		 * @Hook woocommerce_dhl_home_delivery_shipping_rate_cost
		 * @param float              $cost
		 * @param bool               $free_shipping
		 * @param WC_Shipping_Method $this
		 */
		$cost = apply_filters( 'woocommerce_' . $this->id . '_shipping_rate_cost', $cost, $free_shipping, $this );

		$rate = array(
			'id'      => $this->get_rate_id(),
			'label'   => $this->title,
			'cost'    => ( ( $cost <= 0 ) || $free_shipping ) ? 0 : $cost,
			'package' => $package,
		);

		// Add shipping class costs.

		if ( ( 'no' === $weight_based ) && ! $free_shipping ) {

			$shipping_classes = get_terms(
				'product_shipping_class',
				array(
					'hide_empty' => '0',
					'orderby'    => 'name',
				)
			);

			if ( is_wp_error( $shipping_classes ) ) {
				$shipping_classes = array();
			};

			if ( ! empty( $shipping_classes ) ) {
				$found_shipping_classes = $this->find_shipping_classes( $package );
				$highest_class_cost     = 0;

				foreach ( $found_shipping_classes as $shipping_class => $products ) {
					// Also handles BW compatibility when slugs were used instead of ids.
					$shipping_class_term = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );
					$class_cost_string   = $shipping_class_term && $shipping_class_term->term_id ? $this->get_option( 'class_cost_' . $shipping_class_term->term_id, $this->get_option( 'class_cost_' . $shipping_class, '' ) ) : $this->get_option( 'no_class_cost', '' );

					if ( '' === $class_cost_string ) {
						continue;
					}

					$class_cost = $this->evaluate_cost(
						$class_cost_string,
						array(
							'qty'  => array_sum( wp_list_pluck( $products, 'quantity' ) ),
							'cost' => array_sum( wp_list_pluck( $products, 'line_total' ) ),
						)
					);

					if ( 'class' === $this->type ) {
						$rate[ 'cost' ] += $class_cost;
					} else {
						$highest_class_cost = max( $class_cost, $highest_class_cost );
					}
				}

				if ( 'order' === $this->type && $highest_class_cost ) {
					$rate[ 'cost' ] += floatval( $highest_class_cost );
				}
			}

		}

		$this->add_rate( $rate );

		do_action( 'woocommerce_' . $this->id . '_shipping_add_rate', $this, $rate );
	}

	/**
	 * Evaluate a cost from a sum/string.
	 *
	 * @param  string $sum Sum of shipping.
	 * @param  array  $args Args, must contain `cost` and `qty` keys. Having `array()` as default is for back compat reasons.
	 * @return string
	 */
	protected function evaluate_cost( $sum, $args = array() ) {

		// Add warning for subclasses.
		if ( ! is_array( $args ) || ! array_key_exists( 'qty', $args ) || ! array_key_exists( 'cost', $args ) ) {
			wc_doing_it_wrong( __FUNCTION__, '$args must contain `cost` and `qty` keys.', '4.0.1' );
		}

		include_once WC()->plugin_path() . '/includes/libraries/class-wc-eval-math.php';

		// Allow 3rd parties to process shipping cost arguments.
		$args           = apply_filters( 'woocommerce_evaluate_shipping_cost_args', $args, $sum, $this );
		$locale         = localeconv();
		$decimals       = array( wc_get_price_decimal_separator(), $locale[ 'decimal_point' ], $locale[ 'mon_decimal_point' ], ',' );
		$this->fee_cost = $args[ 'cost' ];

		// Expand shortcodes.
		add_shortcode( 'fee', array( $this, 'fee' ) );

		$sum = do_shortcode(
			str_replace(
				array(
					'[qty]',
					'[cost]',
				),
				array(
					$args['qty'],
					$args['cost'],
				),
				$sum
			)
		);

		remove_shortcode( 'fee', array( $this, 'fee' ) );

		// Remove whitespace from string.
		$sum = preg_replace( '/\s+/', '', $sum );

		// Remove locale from string.
		$sum = str_replace( $decimals, '.', $sum );

		// Trim invalid start/end characters.
		$sum = rtrim( ltrim( $sum, "\t\n\r\0\x0B+*/" ), "\t\n\r\0\x0B+-*/" );

		// Do the math.
		return $sum ? WC_Eval_Math::evaluate( $sum ) : 0;
	}

	/**
	 * Work out fee (shortcode).
	 *
	 * @param  array $atts Attributes.
	 * @return string
	 */
	public function fee( $atts ) {
		$atts = shortcode_atts(
			array(
				'percent' => '',
				'min_fee' => '',
				'max_fee' => '',
			),
			$atts,
			'fee'
		);

		$calculated_fee = 0;

		if ( $atts[ 'percent' ] ) {
			$calculated_fee = $this->fee_cost * ( floatval( $atts[ 'percent' ] ) / 100 );
		}

		if ( $atts[ 'min_fee' ] && $calculated_fee < $atts[ 'min_fee' ] ) {
			$calculated_fee = $atts[ 'min_fee' ];
		}

		if ( $atts[ 'max_fee' ] && $calculated_fee > $atts[ 'max_fee' ] ) {
			$calculated_fee = $atts[ 'max_fee' ];
		}

		return $calculated_fee;
	}

	/**
	 * Finds and returns shipping classes and the products with said class.
	 *
	 * @param mixed $package Package of items from cart.
	 * @return array
	 */
	public function find_shipping_classes( $package ) {
		$found_shipping_classes = array();

		foreach ( $package[ 'contents' ] as $item_id => $values ) {
			if ( $values[ 'data' ]->needs_shipping() ) {
				$found_class = $values[ 'data' ]->get_shipping_class();

				if ( ! isset( $found_shipping_classes[ $found_class ] ) ) {
					$found_shipping_classes[ $found_class ] = array();
				}

				$found_shipping_classes[ $found_class ][ $item_id ] = $values;
			}
		}

		return $found_shipping_classes;
	}

	/**
	 * Returns if method reaches free shipping.
	 *
	 * @return bool
	 */
	public function is_free_shipping_available() : bool {

		$is_available    = false;
		$cost            = floatval( $this->cost );
		$min_amount      = floatval( $this->minimum_amount );
		$free_min_amount = floatval( $this->free_min_amount );
		$cart_total       = WC()->cart === null ? 0 : preg_replace( '#[^\d]#', '', WC()->cart->get_cart_total() );

		if ( $cart_total > 0 ) {
			$cart_total = $cart_total / 100;
		}

		if ( ( $cost == 0 ) && ( $min_amount > 0 ) ) {
			$is_available = ( $min_amount <= $cart_total );
		} else
		if ( ( $cost > 0 ) && ( $free_min_amount > 0 ) ) {
			$is_available = ( $free_min_amount <= $cart_total );
		}

		return $is_available;
	}

	/**
	 * This function checks if the shipping method is available.
	 *
	 * @param array $package
	 *
	 * @return bool
	 */
	public function is_available( $package ) : bool {

		$available    = $this->is_enabled();
		$cart_total   = WC()->cart === null ? 0 : preg_replace( '#[^\d]#', '', WC()->cart->get_cart_total() );
		$cart_minimum = $this->minimum_amount === '' ? 0 : preg_replace( '#[^\d]#', '', number_format( $this->minimum_amount, 2 ) );

		if ( $cart_total < $cart_minimum ) {
			$available = false;
		}

		if ( false === Helper::check_shop_address_settings() ) {
			$available = false;
		}

		return $available;
	}

	/**
	 * Sanitize the cost field.
	 *
	 * @param string $value Unsanitized value.
	 *
	 * @return string
	 * @throws Exception Last error triggered.
	 */
	public function sanitize_cost( $value ) {

		$value = is_null( $value ) ? '' : $value;
		$value = wp_kses_post( trim( wp_unslash( $value ) ) );
		$value = str_replace( array( get_woocommerce_currency_symbol(), html_entity_decode( get_woocommerce_currency_symbol() ), wc_get_price_thousand_separator() ), '', $value );

		// Thrown an error on the front end if the evaluate_cost will fail.
		$dummy_cost = $this->evaluate_cost(
			$value,
			array(
				'cost' => 1,
				'qty'  => 1,
			)
		);

		if ( false === $dummy_cost ) {
			throw new Exception( WC_Eval_Math::$last_error );
		}

		return $value;
	}
}
