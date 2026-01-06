<?php

use Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Purchase on account Gateway
 *
 * Provides a Cash on Delivery Payment Gateway.
 *
 * @class 		WGM_Gateway_Purchase_On_Account
 * @extends		WC_Payment_Gateway
 * @version		1.1
 */
class WGM_Gateway_Purchase_On_Account extends WC_Payment_Gateway {

	public static $instances = 0;

	public $instructions;
	public $enable_for_methods;
	public $enable_for_virtual;
	public $user_availability;
	public $order_status;
	public $deactivate_ship_to_different_address;
	public $cart_limit_min;
	public $cart_limit_max;
	public $cart_limit_shipping;
	public $cart_limit_fee;
	public $cart_limit_tax;
	public $allowed_countries;
	public static $order_count_cache = array();
	
    /**
     * Init Payment Gateway
     */
    function __construct() {
		
    	self::$instances++;

		$this->id           						= 'german_market_purchase_on_account';
		$this->method_title 						= __( 'Purchase On Acccount', 'woocommerce-german-market' );
		$this->has_fields   						= false;
		$this->method_description 					= __( 'Let your customers pay by "Purchase on Acccount".', 'woocommerce-german-market' ) . ' <small><em>' . __( 'Provided by German Market.', 'woocommerce-german-market' ) . '</em></small>';

		// support
		$this->supports = array(
			'products'
		);

		if ( class_exists( 'WCS_Autoloader' ) && 'yes' === $this->get_option( 'supports_subscriptions', 'no' ) ) {
			$this->supports = array(
				'products', 
				'subscriptions',
				'subscription_cancellation', 
				'subscription_suspension', 
				'subscription_reactivation',
				'subscription_amount_changes',
				'subscription_date_changes',
				'subscription_payment_method_change',
				'subscription_payment_method_change_customer',
				'subscription_payment_method_change_admin',
				'multiple_subscriptions',
			);

			add_filter( 'woocommerce_payment_gateways_settings', function( $settings ) {
				add_filter( 'german_market_purchase_on_account_is_available', '__return_true' );
				return $settings;
			}, 1 );
		}

		// Load the settings
		$this->init_form_fields();
		$this->init_settings();

		// Get settings
		$this->enabled 			  					= $this->get_option( 'enabled', 'no' );
		$this->title              					= $this->get_option( 'title' );
		$this->description        					= $this->get_option( 'description' );
		$this->instructions       					= $this->get_option( 'instructions' );
		$this->enable_for_methods 					= $this->get_option( 'enable_for_methods', array() );
	    $this->enable_for_virtual 					= $this->get_option( 'enable_for_virtual', 'yes' ) === 'yes' ? true : false;
	    $this->user_availability  					= apply_filters( 'wgm_gateway_purchase_on_account_option_user_availability', $this->get_option( 'user_availability' ) );
	    $this->order_status 	  					= $this->get_option( 'order_status', 'processing' );
	    $this->deactivate_ship_to_different_address = $this->get_option( 'deactivate_ship_to_different_address', 'no' );
	    $this->cart_limit_min						= $this->get_option( 'cart_limit_min' );
		$this->cart_limit_max						= $this->get_option( 'cart_limit_max' );
		$this->cart_limit_shipping					= $this->get_option( 'cart_limit_shipping' );
		$this->cart_limit_fee						= $this->get_option( 'cart_limit_fee' );
		$this->cart_limit_tax						= $this->get_option( 'cart_limit_tax' );
		$this->allowed_countries 					= $this->get_option( 'enable_for_biling_countries', array() );

		if ( $this->deactivate_ship_to_different_address == 'yes' ) {
			add_action( 'woocommerce_after_checkout_billing_form', array( $this, 'hidden_field_for_deactivate_ship_to_different_address' ) );
		}
		
	    // Customer Emails
	    if ( self::$instances == 1 ) {
	   		add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 9, 4 );
	   		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_thankyou_german_market_purchase_on_account', array( $this, 'thankyou' ) );
	   	}

	    if ( is_admin() ) {
	    	$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : 'min.';
			wp_enqueue_style( 'woocommerce_de_admin', plugins_url( '/css/backend.' . $min . 'css', Woocommerce_German_Market::$plugin_base_name ), array( 'wp-components', 'wc-experimental' ), Woocommerce_German_Market::$version );
	    }
	    
		do_action( 'wgm_gateway_purchase_on_account_after_counstruct', $this );

    }

    /**
	 * Hidden field for JS to get option "deactivate_ship_to_different_address"
	 *
	 * @since 3.6.2
	 * @wp-hook woocommerce_after_checkout_billing_form
	 * @return void
	 */
    function hidden_field_for_deactivate_ship_to_different_address( $checkout ) {
		?><input type="hidden" name="deactivate_ship_to_different_address_if_purchase_on_account" id="deactivate_ship_to_different_address_if_purchase_on_account" value="<?php echo $this->deactivate_ship_to_different_address; ?>"><?php
    }

	/**
	 * Admin Panel Options
	 * - Options for bits like 'title' and availability on a country-by-country basis
	 *
	 * @access public
	 * @return void
	 */
	function admin_options() {
		
		// German Market styles
		?>
		<h3><?php echo esc_attr( __( 'Purchase on Account', 'woocommerce-german-market' ) ); ?></h3>
		<p><?php echo esc_attr( __( 'Your customers can pay per invoice after they received the order.', 'woocommerce-german-market' ) ); 
		echo wp_kses_post( WGM_Ui::get_video_layer( 'https://s3.eu-central-1.amazonaws.com/videogm/kauf-auf-rechnung.mp4' ) ); ?></p>
		<table class="form-table">
			<?php $this->generate_settings_html(); ?>
		</table> <?php
	}


	/**
	 * Initialise Gateway Settings Form Fields
	 *
	 * @access public
	 * @return void
	 */
	function init_form_fields() {

		if ( is_admin() ) {

			// Init
			$possible_statuses = apply_filters( 'wgm_purchase_on_account_possible_order_statuses', array(
				'pending'		=> __( 'Pending Payment', 'woocommerce-german-market' ), // Zahlung ausstehend
				'processing'	=> __( 'Processing', 'woocommerce-german-market' ), // In Bearbeitung
				'on-hold'		=> __( 'On Hold', 'woocommerce-german-market' ), // Wartestellung
				'completed'		=> __( 'Completed', 'woocommerce-german-market' ), // Fertiggestellt
			) );

			$shipping_methods = array();

			$has_local_pickup_pickup_location = false;
			
			$wc_shipping = WC()->shipping();
			if ( is_object( $wc_shipping ) && method_exists( $wc_shipping, 'get_shipping_methods' ) ) {
				foreach ( $wc_shipping->get_shipping_methods() as $method ) {
					if ( 'pickup_location' === $method->id || 'local_pickup' === $method->id ) {
						if ( $has_local_pickup_pickup_location ) {
							continue;
						} else {
							$has_local_pickup_pickup_location = true;
						}
					}
					$shipping_methods[ $method->id ] = $method->get_method_title();
				}
			}

			$shipping_methods [ 'no_shipping_needed' ] = __( 'No shipping needed (for virtual orders)', 'woocommerce-german-market' );

			$sentence_min_orders_1_1 = __( 'Only for registered users with at least 1 completed order', 'woocommerce-german-market' );
			$sentence_min_orders_1_2 = __( 'Only for registered users with at least 2 completed orders', 'woocommerce-german-market' );
			
			$min_orders_for_setting = apply_filters( 'wgm_purchase_on_account_min_orders', 3 );

			$sentence_min_orders_1_3 = sprintf( __( 'Only for registered users with at least %s completed orders', 'woocommerce-german-market' ), $min_orders_for_setting );
			
			$sentence_min_orders_2 = sprintf( __( 'Choose whether "Puchase on Account" is available for all users, only registered users or only registered users that have at least 1, 2 or %s completed order.', 'woocommerce-german-market' ), $min_orders_for_setting );

			$pdf_notice = '';
			if ( class_exists( 'Woocommerce_Invoice_Pdf' ) ) {
				// If the Invoice PDF Add-On is active
				// German Translation: Wenn du die Rechnungs-PDF in die E-Mail einfügen willst, die der Kunde erhält, stelle sicher, dass du die Rechnungs-PDF $%here%s in der E-Mail für den gewählten Bestellstatus aktiviert hast. 
				$page_url = get_admin_url() . 'admin.php?page=german-market&tab=invoice-pdf&sub_tab=emails';
				$pdf_notice = '<br />' . sprintf( __( 'If you want to attache the invoice pdf into the email that is send to the customer, make sure that you have attached the invoice pdf for your chosen order status in the settings %shere%s.', 'woocommerce-german-market' ), '<a href="' . $page_url . '">', '</a>' );
			}

			if ( get_option( 'gm_gross_shipping_costs_and_fees', 'off' ) == 'off' ) {
				$fee_notice = sprintf( __( 'Collect an extra service fee for "Pay on Purchase" payments. Enter amount in %s excluding tax.', 'woocommerce-german-market' ), esc_attr( get_option( 'woocommerce_currency' ) ) );
			} else {
				$fee_notice = sprintf( __( 'Collect an extra service fee for "Pay on Purchase" payments. Enter amount in %s including tax.', 'woocommerce-german-market' ), esc_attr( get_option( 'woocommerce_currency' ) ) );
			}

			// allowed countries
			$allowed_countries = array();
			$show_enable_for_biling_countries = false;

			if ( isset( WC()->countries ) && WGM_Helper::method_exists( WC()->countries, 'get_allowed_countries' ) ) {

				if ( isset( $_REQUEST[ 'section' ] ) && 'german_market_purchase_on_account' === $_REQUEST[ 'section' ] ) {
					$allowed_countries = WC()->countries->get_allowed_countries();
					$show_enable_for_biling_countries = true;
				}
			}
			
			// Set form fields
			$this->form_fields = array(
				'enabled' => array(
					'title' => __( 'Enable Purchase on Account', 'woocommerce-german-market' ),
					'label' => __( 'Enable Purchase on Account', 'woocommerce-german-market' ),
					'type' => 'checkbox',
					'description' => '',
					'default' => 'no'
				),

				'supports_subscriptions' => array(
					'title'             => __( 'Subscriptions', 'woocommerce-german-market' ),
					'label'             => __( 'Use the payment method "Purchase on Account" for recurring payments of WooCommerce Subscriptions.', 'woocommerce-german-market' ),
					'type'              => 'checkbox',
					'default'           => 'no'
				),

				'title' => array(
					'title' => __( 'Title', 'woocommerce-german-market' ),
					'type' => 'text',
					'description' => __( 'Payment method description that the customer will see on your checkout.', 'woocommerce-german-market' ),
					'default' => __( 'Purchase on Account', 'woocommerce-german-market' ),
					'desc_tip'      => true,
				),
				'description' => array(
					'title' => __( 'Description', 'woocommerce-german-market' ),
					'type' => 'textarea',
					'description' => __( 'Payment method description that the customer will see on your website in order.', 'woocommerce-german-market' ),
					'default' => __( 'Purchase on Account.', 'woocommerce-german-market' ),
				),
				'instructions' => array(
					'title' => __( 'Instructions', 'woocommerce-german-market' ),
					'type' => 'textarea',
					'description' => __( 'Payment method description that the customer will see on your website and in the order emails.', 'woocommerce-german-market' ),
					'default' => __( 'Pay per invoice after you received your order. The goods remain our property until complete payment has been made.', 'woocommerce-german-market' )
				),

				'order_status' => array(
					'title'			=> __( 'Order Status', 'woocommerce-german-market' ),
					'type'			=> 'select',
					'default'		=> 'processing',
					'options'		=> $possible_statuses,
					'description'	=> __( 'Choose the order status of the customer\'s order after the customer finished the order process. We recommend to the set the option "Processing".', 'woocommerce-german-market' ) . $pdf_notice,
				),

				'user_availability' => array(
					'title'			=> __( 'User Availability', 'woocommerce-german-market' ),
					'type'			=> 'select',
					'default'		=> 'all_users',
					'options'		=> array(
							'all_users'					=> __( 'All Users', 'woocommerce-german-market' ),
							'registered_users'			=> __( 'Only for registered users', 'woocommerce-german-market' ),
							'completed_order_users'		=> $sentence_min_orders_1_1,
							'completed_order_users_2'	=> $sentence_min_orders_1_2,
							'completed_order_users_3'	=> $sentence_min_orders_1_3
					),
					'description'	=> $sentence_min_orders_2
				),

				'woocommerce_german_market_purchase_on_account_fee' => array(
					'title' 		=> __( 'Service Fee', 'woocommerce-german-market' ),
					'type' 			=> 'text',
					'css'  			=> 'width:50px;',
					/* translators: %s = default currency, e.g. EUR */
					'desc_tip' 		=> $fee_notice,
					'default' 		=> '',
					'description' 	=> __( '<span style="color: #f00;">Attention!</span> Please inform yourself about the legalities regarding the charging of fees for payments:<br><a href="https://www.it-recht-kanzlei.de/verbot-extra-kosten-kartenzahlungen.html" target="_blank">https://www.it-recht-kanzlei.de/verbot-extra-kosten-kartenzahlungen.html</a>', 'woocommerce-german-market' ),
				),
				'enable_for_methods' => array(
					'title' 		=> __( 'Enable for shipping methods', 'woocommerce-german-market' ),
					'type' 			=> 'multiselect',
					'class'			=> 'chosen_select',
					'css'			=> 'width: 450px;',
					'default' 		=> '',
					'description' 	=> __( 'If "Purchase on Account" is only available for certain methods, set it up here. Leave blank to enable for all methods.', 'woocommerce-german-market' ),
					'options'		=> $shipping_methods,
					'desc_tip'      => true,
				),

				'enable_for_biling_countries' => array(
					'title' 		=> __( 'Enable for Billing Countries', 'woocommerce-german-market' ),
					'type' 			=> 'multiselect',
					'class'			=> 'chosen_select',
					'css'			=> 'width: 450px;',
					'default' 		=> '',
					'description' 	=> __( 'If "Purchase on Account" is only available for certain billing countries, set it up here. Leave blank to enable for all countries. You can choose between all allowed countries depending on your general woocommerce settings.', 'woocommerce-german-market' ),
					'options'		=> $allowed_countries,
					'desc_tip'      => true,
				),

				'enable_for_virtual' => array(
					'title'             => __( 'Accept for virtual orders', 'woocommerce-german-market' ),
					'label'             => __( 'Accept "Purchase on Account" if the order is virtual', 'woocommerce-german-market' ),
					'type'              => 'checkbox',
					'default'           => 'yes'
				),

				'deactivate_ship_to_different_address' => array(
					'title'             => __( 'Deactivate "Ship to different address"', 'woocommerce-german-market' ),
					'label'             => __( 'Deactivate "Ship to different address" if customer choose "Purchase on Account" as payment.', 'woocommerce-german-market' ),
					'type'              => 'checkbox',
					'default'           => 'no'
				),	

				'cart_total_limitation' => array(
					'title'			=> __( 'Availability by Cart Total', 'woocommerce-german-market' ),
					'type'			=> 'title',
					'description'	=> WGM_Ui::get_video_layer( 'https://s3.eu-central-1.amazonaws.com/videogm/kauf-auf-rechnung-warenkorbwert.mp4' ),
				),

				'cart_limit_min'	=> array(
					'title'			=> sprintf( __( 'Cart Total Minimum in %s', 'woocommerce-german-market' ), get_woocommerce_currency_symbol() ),
					'type'			=> 'text',
					'class'			=> 'wc_input_price',
					'default'		=> '',
					'desc_tip'		=> __( 'If an amount is entered, cart total has to be equal or greater than this amount to enable "Purchase on Account". Leave empty for no minimum limit.', 'woocommerce-german-market' ),
				),

				'cart_limit_max'	=> array(
					'title'			=> sprintf( __( 'Cart Total Maximum in %s', 'woocommerce-german-market' ), get_woocommerce_currency_symbol() ),
					'type'			=> 'text',
					'class'			=> 'wc_input_price',
					'default'		=> '',
					'desc_tip'		=> __( 'If an amount is entered, cart total has to be equal or less than this amount to enable "Purchase on Account". Leave empty for no maximum limit.', 'woocommerce-german-market' ),
				),

				'cart_limit_shipping'	=> array(
					'title'				=> __( 'Including or Excluding Shipping', 'woocommerce-german-market' ),
					'type'				=> 'select',
					'options'			=> array(
											'incl' => __( 'Including Shipping', 'woocommerce-german-market' ),
											'excl' => __( 'Excluding Shipping', 'woocommerce-german-market' ),
										),
					'class'				=> 'wc_input_price',
					'default'			=> 'excl',
					'desc_tip'			=> __( 'The amounts of the options "Cart Total Minimum" and "Cart Total Maximum" can be applied including or excluding shipping.', 'woocommerce-german-market' ),
				),

				'cart_limit_fee'	=> array(
					'title'				=> __( 'Including or Excluding Fee', 'woocommerce-german-market' ),
					'type'				=> 'select',
					'options'			=> array(
											'incl' => __( 'Including Fee', 'woocommerce-german-market' ),
											'excl' => __( 'Excluding Fee', 'woocommerce-german-market' ),
										),
					'class'				=> 'wc_input_price',
					'default'			=> 'excl',
					'desc_tip'			=> __( 'The amounts of the options "Cart Total Minimum" and "Cart Total Maximum" can be applied including or excluding fee.', 'woocommerce-german-market' ),
				),

				'cart_limit_tax'	=> array(
					'title'			=> __( 'Including or Excluding Tax', 'woocommerce-german-market' ),
					'type'			=> 'select',
					'options'		=> array(
											'incl' => __( 'Including Tax', 'woocommerce-german-market' ),
											'excl' => __( 'Excluding Tax', 'woocommerce-german-market' ),
										),
					'class'			=> 'wc_input_price',
					'default'		=> 'incl',
					'desc_tip'		=> __( 'The amounts of the options "Cart Total Minimum" and "Cart Total Maximum" can be applied including or excluding tax.', 'woocommerce-german-market' ),
				),

		   );

			if ( ! $show_enable_for_biling_countries ) {
				unset( $this->form_fields[ 'enable_for_biling_countries' ] );
			}

			if ( ! class_exists( 'WCS_Autoloader' ) ) {
				unset( $this->form_fields[ 'supports_subscriptions' ] );
			}
		}
	}

	/**
	 * Check availability of gateway
	 */
	public function check_availability() {

		// If not enabled for virtual products
		if ( ! $this->enable_for_virtual ) {
			if ( WC()->cart && ! WC()->cart->needs_shipping() ) {
				return false;
			}

			if ( is_page( wc_get_page_id( 'checkout' ) ) && 0 < get_query_var( 'order-pay' ) ) {
				$order_id = absint( get_query_var( 'order-pay' ) );
				$order    = wc_get_order( $order_id );

				// Test if order needs shipping.
				$needs_shipping = false;

				if ( 0 < sizeof( $order->get_items() ) ) {
					foreach ( $order->get_items() as $item ) {
						
						if ( WGM_Helper::method_exists( $item, 'get_product' ) ) {
							$_product = $item->get_product();
						} else {
							$_product = $order->get_product_from_item( $item );
						}

						if ( $_product->needs_shipping() ) {
							$needs_shipping = true;
							break;
						}
					}
				}

				$needs_shipping = apply_filters( 'woocommerce_cart_needs_shipping', $needs_shipping );

				if ( $needs_shipping ) {
					return false;
				}
			}

			if ( apply_filters( 'german_market_gateway_enable_for_virtuell_off_return_false', false ) ) {
				return false;
			}
		}

		if ( ! empty( $this->enable_for_methods ) ) {

			// order pay page
			if ( is_wc_endpoint_url( get_option( 'woocommerce_checkout_pay_endpoint' ) ) ) {

                $order_id = absint( get_query_var( 'order-pay' ) );
				$order = wc_get_order( $order_id );

				if ( ! $order->get_shipping_method() )
					return $this->available_if_no_shipping_needed();

				$chosen_method = $order->get_shipping_method();

			// cart
			} else {
				
				// get chosen method from WC session
				$chosen_method = '';
				if ( ! is_null( WC()->session ) ) {
					$chosen_method = WC()->session->chosen_shipping_methods;
				}
				
				// if cart does not need any shipping, set chosen method to empty string
				if ( WC()->cart && ! WC()->cart->needs_shipping() ) {
					$chosen_method = '';
				}
			}

			if ( is_array( $chosen_method ) && count( $chosen_method ) == 1 ) {
				$chosen_method = $chosen_method[ 0 ];
			}

			$chosen_method = str_replace( 'pickup_location', 'local_pickup', $chosen_method );

			if ( empty( $chosen_method ) ) {
				
				// there is no chosen method, return if gateway is not available if no shipping needed
				// but otherwise proceed with next checks
				if ( ! $this->available_if_no_shipping_needed() ) {
					return false;
				}

			} else {

				// there is a chosen method, check if gateway is enabled for this method
				$found = false;

				foreach ( $this->enable_for_methods as $method_id ) {
					if ( ( is_string( $chosen_method ) && strpos( $chosen_method, $method_id ) === 0 ) ||
						 ( is_array( $chosen_method ) && in_array( $method_id, $chosen_method ) ) ) {
						$found = true;
						break;
					}
				}

				if ( ! $found ) {
					return false;					
				}
			}
		}

		$user_availability = $this->user_availability;
		$user_id = get_current_user_id();

		if ( $user_availability == 'registered_users' ) {
			
			if ( ! $user_id > 0 ) {
				return false;
			}

		} else if ( $user_availability == 'completed_order_users' || $user_availability == 'completed_order_users_2' || $user_availability == 'completed_order_users_3' ) {

			if ( ! $user_id > 0 ) {
				return false;
			}

			$max_check = 1;

			if ( $user_availability == 'completed_order_users_2' ) {
				$max_check = 2;
			} else if ( $user_availability == 'completed_order_users_3' ) {
				$max_check = apply_filters( 'wgm_purchase_on_account_min_orders', 3 );
			}

			if ( isset( self::$order_count_cache[ $user_id ] ) ) {

				$orders_count = self::$order_count_cache[ $user_id ];

			} else {

				$orders = wc_get_orders( array(
					'limit' 		=> $max_check + 2,
					'customer_id' 	=> $user_id,
					'status' 		=> 'completed',	
					'return'		=> 'ids',
					'type' 			=> 'shop_order'
				) );

				$orders_count = count( $orders );
				self::$order_count_cache[ $user_id ] = $orders_count;
			}
			

			if ( $orders_count < $max_check ) {
				return false;
			}

		}

		// Check min and max amount
		$cart_totals = false;

		// Min Limit
		if ( ! empty( $this->get_option( 'cart_limit_min' ) ) ) {

			$cart_totals = $this->get_cart_total_for_limit_availability();

			$limit_min = floatval( str_replace( ',', '.', $this->get_option( 'cart_limit_min' ) ) );

			if ( $cart_totals < $limit_min ) {
				return false;
			}

		}

		// Max Limit
		if ( ! empty( $this->get_option( 'cart_limit_max' ) ) ) {

			if ( ! $cart_totals ) {
				$cart_totals = $this->get_cart_total_for_limit_availability();
			}

			$limit_max = floatval( str_replace( ',', '.', $this->get_option( 'cart_limit_max' ) ) );

			if ( $cart_totals > $limit_max ) {
				return false;
			}

		}

		// Allowed Countries
		if ( ! empty( $this->allowed_countries ) ) {

			if ( is_wc_endpoint_url( get_option( 'woocommerce_checkout_pay_endpoint' ) ) ) {

                $order_id 			= absint( get_query_var( 'order-pay' ) );
				$order 				= wc_get_order( $order_id );
				$billing_country 	= $order->get_billing_country();

			} else {

				if ( ! $this->is_checkout_block_default() ) {
					
					// shortcode checkout
					if ( isset( WC()->session->customer[ 'country' ] ) ) {
						$billing_country 	= WC()->session->customer[ 'country' ];
					} else {
						$billing_country 	= false;
					}
				} else {

					// block checkout
					if ( ! empty( WC()->checkout->get_value( 'billing_country' ) ) ) {
						$billing_country = WC()->checkout->get_value( 'billing_country' );
						if ( empty( $billing_country ) ) {
							if ( ! empty( WC()->checkout->get_value( 'shipping_country' ) ) ) {
								$billing_country = WC()->checkout->get_value( 'shipping_country' );
							}
						}
					}
				}
			
			}

			if ( $billing_country && ( ! in_array( $billing_country, $this->allowed_countries ) ) ) {
				return false;
			}

		}

		return parent::is_available();
	}

	/**
	 * Check If The Gateway Is Available For Use
	 *
	 * @access public
	 * @return bool
	 */
	public function is_available() {
		
		if ( ! $this->is_checkout_block_default() ) {
			return apply_filters( 'german_market_purchase_on_account_is_available', $this->check_availability() );
		}

		return apply_filters( 'german_market_purchase_on_account_is_available', parent::is_available() );
	}

	/**
	 * Calls Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils::is_checkout_block_default()
	 * If the class is not available it returns false
	 *
	 * @access public
	 * @return bool
	 */
	public function is_checkout_block_default() {

		$is_checkout_block_default = false;

		if ( class_exists( 'Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils' ) ) {
			$is_checkout_block_default = CartCheckoutUtils::is_checkout_block_default();
		}

		return $is_checkout_block_default;
	}

	/**
	 * Get Cart Total regarding tax and shipping options for limit availability
	 *
	 * @access private
	 * @since 3.7
	 * @return Float
	 */
	private function get_cart_total_for_limit_availability() {

		if ( ! WC()->cart ) {
			return '';
		}

		$cart_totals 	= WC()->cart->get_cart_contents_total();
		$tax_option 	= $this->get_option( 'cart_limit_tax' );
		
		if (  $tax_option == 'incl' ) {
			$cart_totals +=  WC()->cart->get_cart_contents_tax();
		}

		if ( $this->get_option( 'cart_limit_shipping' ) == 'incl' ) {

			$cart_totals +=  WC()->cart->get_shipping_total();

			if (  $tax_option == 'incl' ) {
				$cart_totals +=  WC()->cart->get_shipping_tax();
			}

		}

		if ( $this->get_option( 'cart_limit_fee' ) == 'incl' ) {

			$cart_totals +=  WC()->cart->get_fee_total();

			if (  $tax_option == 'incl' ) {
				$cart_totals +=  WC()->cart->get_fee_tax();
			}

		}

		return $cart_totals;
	}

	/**
	 * Returns true if and only if 
	 * The order does not need shipping (is virtual), no shipping methods are available, the user selected "no shipping needed" in "enable for methods"
	 *
	 * @access private
	 * @since 3.5.7
	 * @return Boolean
	 */
	private function available_if_no_shipping_needed() {

		if ( in_array( 'no_shipping_needed', $this->enable_for_methods ) && $this->enabled == 'yes' ) {
			return true;
		}

		return false;
	}

	/**
	 * Process the payment and return the result
	 *
	 * @access public
	 * @param int $order_id
	 * @return array
	 */
	function process_payment ($order_id) {

		$order = wc_get_order( $order_id );

		if ( true === apply_filters( 'wgm_gateway_purchase_on_account_process_payment_update_status', true, $order ) ) {

			// Mark as on-hold (we're awaiting the cheque)
			$order->update_status( $this->order_status, __( 'Purchase on account.', 'woocommerce-german-market' ));

			// Reduce stock levels
			if ( apply_filters( 'wgm_gateway_purchase_on_account_process_payment_reduce_stock_levels', true ) ) {
				wc_reduce_stock_levels( $order->get_id() );
			}
			
			// Remove cart
			WC()->cart->empty_cart();

		}

		// Return thankyou redirect
		return array(
			'result' 	=> 'success',
			'redirect'	=> $order->get_checkout_order_received_url()
		);
	}

	/**
	 * Output for the order received page.
	 *
	 * @access public
	 * @return void
	 */
	function thankyou() {
		echo $this->instructions != '' ? wpautop( $this->instructions ) : '';
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @access public
	 * @param WC_Order $order
	 * @param bool $sent_to_admin
	 * @param bool $plain_text
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false, $email = false ) {
		
		if ( $email && is_a( $email, 'WC_Email_Customer_Refunded_Order' ) ) {
			return;
		}
		
		if ( apply_filters( 'german_market_purchase_on_account_email_instructions_return', false, $order, $email ) ) {
			return;
		}
		
		if ( $this->instructions && ! $sent_to_admin && 'german_market_purchase_on_account' === $order->get_payment_method() ) {
			
			remove_action( 'woocommerce_email_before_order_table', array( 'WGM_Manual_Order_Confirmation', 'add_payment_instructions_processing' ), 10, 3 );
			remove_action( 'woocommerce_email_before_order_table', array( 'WGM_Manual_Order_Confirmation', 'add_payment_instructions' ), 10, 3 );

			echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
		}
	}
}
