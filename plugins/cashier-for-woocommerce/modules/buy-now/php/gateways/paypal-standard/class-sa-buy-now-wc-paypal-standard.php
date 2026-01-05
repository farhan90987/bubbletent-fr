<?php
/**
 * PayPal Subscription Class.
 *
 * Filters necessary functions in the WC_Paypal class to allow for subscriptions, either via PayPal Standard (default)
 * or PayPal Express Checkout using Reference Transactions (preferred)
 *
 * @package     WooCommerce Buy Now
 *  author      StoreApps
 * @since       1.1.0
 *
 * Credit: Prospress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly..
}

/**
 * Main class for Handling Paypal Reference Transaction
 */
class SA_Buy_Now_WC_Paypal_Standard {

	/**
	 * For communicating with PayPal
	 *
	 * @var WCS_PayPal_Express_API $api
	 */
	protected static $api;

	/**
	 * Single instance of this class
	 *
	 * @var SA_Buy_Now_WC_Paypal_Standard $instance
	 */
	protected static $instance;

	/**
	 * Cache of PayPal IPN Handler
	 *
	 * @var array $ipn_handlers
	 */
	protected static $ipn_handlers;

	/**
	 * Cache of PayPal Standard settings in WooCommerce
	 *
	 * @var array $paypal_settings
	 */
	protected static $paypal_settings;

	/**
	 * An internal cache of order IDs with a specific PayPal Standard Profile ID or Reference Transaction Billing Agreement.
	 *
	 * @var int[][] $orders_by_paypal_id
	 */
	protected static $orders_by_paypal_id = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', __CLASS__ . '::init', 5 ); // run before default priority 10 in case the site is using ALTERNATE_WP_CRON to avoid https://core.trac.wordpress.org/ticket/24160.

		add_action( 'admin_notices', __CLASS__ . '::show_admin_notices' );
	}

	/**
	 * Main PayPal Instance, ensures only one instance is/can be loaded
	 *
	 * @see wc_paypal_express()
	 * @return WCS_PayPal
	 * @since 2.0
	 */
	public static function instance() {

		// Check if instance is already exists.
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Handle call to functions which is not available in this class
	 *
	 * @param string $function_name Function name.
	 * @param array  $arguments Array of arguments passed.
	 * @return mixed result of function call
	 */
	public static function __callStatic( $function_name, $arguments = array() ) {

		global $wc_buy_now;

		if ( ! is_callable( array( $wc_buy_now, $function_name ) ) ) {
			return;
		}

		if ( ! empty( $arguments ) ) {
			return call_user_func_array( array( $wc_buy_now, $function_name ), $arguments );
		} else {
			return call_user_func( array( $wc_buy_now, $function_name ) );
		}

	}

	/**
	 * Bootstraps the class and hooks required actions & filters.
	 *
	 * @since 2.0
	 */
	public static function init() {

		self::$paypal_settings = self::get_options();

		// wc-api handler for express checkout transactions.
		if ( ! has_action( 'woocommerce_api_sa_bn_wc_paypal' ) ) {
			add_action( 'woocommerce_api_sa_bn_wc_paypal', __CLASS__ . '::handle_wc_api' );
		}

		// When necessary, set the PayPal args to be for a subscription instead of shopping cart.
		add_action( 'woocommerce_update_options_payment_gateways_paypal', __CLASS__ . '::reload_options', 100 );

		// When necessary, set the PayPal args to be for a subscription instead of shopping cart.
		add_action( 'woocommerce_update_options_payment_gateways_paypal', __CLASS__ . '::are_reference_transactions_enabled', 100 );

		// When necessary, set the PayPal args to be for a subscription instead of shopping cart.
		add_filter( 'woocommerce_paypal_args', __CLASS__ . '::get_paypal_args', 10, 2 );

		add_action( 'buy_now_post_fields', __CLASS__ . '::add_billing_agreement_id', 10, 2 );

		// Check a valid PayPal IPN request to see if it's a subscription *before* WCS_Gateway_Paypal::successful_request().
		add_action( 'valid-paypal-standard-ipn-request', __CLASS__ . '::process_ipn_request', 0 );

		// Triggered by WCS_SV_API_Base::broadcast_request() whenever an API request is made.
		add_action( 'wc_paypal_api_request_performed', __CLASS__ . '::log_api_requests', 10, 2 );

		// Run the IPN failure handler attach and detach functions before and after processing to catch and log any unexpected shutdowns.
		add_action( 'valid-paypal-standard-ipn-request', 'SA_Buy_Now_WC_Paypal_Standard_IPN_Failure_Handler::attach', -1, 1 );
		add_action( 'valid-paypal-standard-ipn-request', 'SA_Buy_Now_WC_Paypal_Standard_IPN_Failure_Handler::detach', 1, 1 );

		// Filter for Buy Now's one click checkout.
		add_filter( 'sa_wc_buy_now_supported_gateways', __CLASS__ . '::add_gateway_support' );

		// Filter for status HTML for unsupported gateway in Buy Now's one click checkout.
		add_filter( 'sa_wc_buy_now_unsupported_gateway_status_html_' . self::instance()->get_id(), __CLASS__ . '::add_unsupported_gateway_status_html' );

		add_action( 'admin_footer', __CLASS__ . '::styles_and_scripts' );
	}

	/**
	 * Get a WooCommerce setting value for the PayPal Standard Gateway
	 *
	 * @param string $setting_key setting key.
	 * @since 2.0
	 */
	public static function get_option( $setting_key = '' ) {

		// From WC 3.4 onwards, PayPal's sandbox and live API credentials are stored separately. When requesting the API keys make sure we return the active keys - live or sandbox depending on the mode.
		if ( self::is_wc_gte_34() && in_array( $setting_key, array( 'api_username', 'api_password', 'api_signature' ), true ) && 'yes' === self::get_option( 'testmode' ) ) {
			$setting_key = 'sandbox_' . $setting_key;
		}

		return ( isset( self::$paypal_settings[ $setting_key ] ) ) ? self::$paypal_settings[ $setting_key ] : '';
	}

	/**
	 * Checks if the PayPal API credentials are set.
	 *
	 * @since 2.0
	 */
	public static function are_credentials_set() {

		$credentials_are_set = false;

		if ( '' !== self::get_option( 'api_username' ) && '' !== self::get_option( 'api_password' ) && '' !== self::get_option( 'api_signature' ) ) {
			$credentials_are_set = true;
		}

		return apply_filters( 'wooocommerce_paypal_credentials_are_set', $credentials_are_set );
	}

	/**
	 * Checks if the PayPal account has reference transactions setup
	 *
	 * Subscriptions keeps a record of all accounts where reference transactions were found to be enabled just in case the
	 * store manager switches to and from accounts. This record is stored as a JSON encoded array in the options table.
	 *
	 * @param string $bypass_cache flag to bypass cache.
	 * @since 2.0
	 */
	public static function are_reference_transactions_enabled( $bypass_cache = '' ) {

		$api_username                   = self::get_option( 'api_username' );
		$transient_key                  = 'sa_bn_wc_paypal_rt_enabled';
		$reference_transactions_enabled = false;

		if ( self::are_credentials_set() ) {

			$accounts_with_reference_transactions_enabled = json_decode( get_option( 'sa_bn_wc_paypal_rt_enabled_accounts', wp_json_encode( array() ) ) );

			if ( in_array( $api_username, $accounts_with_reference_transactions_enabled, true ) ) {

				$reference_transactions_enabled = true;

			} elseif ( 'bypass_cache' === $bypass_cache || get_transient( $transient_key ) !== $api_username ) {

				if ( self::get_api()->are_reference_transactions_enabled() ) {
					$accounts_with_reference_transactions_enabled[] = $api_username;
					update_option( 'sa_bn_wc_paypal_rt_enabled_accounts', wp_json_encode( $accounts_with_reference_transactions_enabled ), 'no' );
					$reference_transactions_enabled = true;
				} else {
					set_transient( $transient_key, $api_username, WEEK_IN_SECONDS );
				}
			}
		}

		return apply_filters( 'wooocommerce_buy_now_paypal_reference_transactions_enabled', $reference_transactions_enabled );
	}

	/**
	 * Handle WC API requests where we need to run a reference transaction API operation
	 *
	 * @throws Exception Fatal errors during execution.
	 * @since 2.0
	 */
	public static function handle_wc_api() {

		if ( ! isset( $_GET['action'] ) ) { // phpcs:ignore 
			return;
		}

		switch ( $_GET['action'] ) { // phpcs:ignore 

			// called when the customer is returned from PayPal after authorizing their payment, used for retrieving the customer's checkout details.
			case 'create_billing_agreement':
				// bail if no token.
				if ( ! isset( $_GET['token'] ) ) { // phpcs:ignore 
					return;
				}

				// get token to retrieve checkout details with.
				$token = wc_clean( wp_unslash( $_GET['token'] ) ); // phpcs:ignore 

				try {

					$express_checkout_details_response = self::get_api()->get_express_checkout_details( $token );

					$billing_agreement_status = (int) $express_checkout_details_response->get_billing_agreement_status();

					// Make sure the billing agreement was accepted.
					if ( 1 === $billing_agreement_status ) {

						$order = $express_checkout_details_response->get_order();

						if ( is_null( $order ) ) {
							throw new Exception( __( 'Unable to find order for PayPal billing agreement.', 'cashier' ) );
						}

						// we need to process an initial payment.
						if ( $order->get_total() > 0 ) {
							$billing_agreement_response = self::get_api()->do_express_checkout(
								$token,
								$order,
								array(
									'payment_action' => 'Sale',
									'payer_id'       => $express_checkout_details_response->get_payer_id(),
								)
							);
						} else {
							$billing_agreement_response = self::get_api()->create_billing_agreement( $token );
						}

						if ( $billing_agreement_response->has_api_error() ) {
							throw new Exception( $billing_agreement_response->get_api_error_message(), $billing_agreement_response->get_api_error_code() );
						}

						// Make sure PayPal is set as the payment method on the order and subscription.
						$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
						$payment_method     = isset( $available_gateways[ self::instance()->get_id() ] ) ? $available_gateways[ self::instance()->get_id() ] : false;
						$order->set_payment_method( $payment_method );

						// Store the billing agreement ID on the order and subscriptions.
						sa_bn_wc_set_paypal_id( $order, $billing_agreement_response->get_billing_agreement_id() );

						if ( true === sa_bn_wc_is_order( $order ) ) {

							if ( 0 === $order->get_total() ) {
								$order->payment_complete();
							} else {
								self::process_subscription_payment_response( $order, $billing_agreement_response );
							}

							$redirect_url = add_query_arg( 'utm_nooverride', '1', $order->get_checkout_order_received_url() );
						}

						// redirect customer to order received page.
						wp_safe_redirect( esc_url_raw( $redirect_url ) );

					} else {

						wp_safe_redirect( wc_get_cart_url() );

					}
				} catch ( Exception $e ) {

					wc_add_notice( __( 'An error occurred, please try again or try an alternate form of payment.', 'cashier' ), 'error' );

					wp_safe_redirect( wc_get_cart_url() );
				}

				exit;

			case 'reference_transaction_account_check':
				exit;
		}
	}

	/**
	 * Override the default PayPal standard args in WooCommerce for subscription purchases when
	 * automatic payments are enabled and when the recurring order totals is over $0.00 (because
	 * PayPal doesn't support subscriptions with a $0 recurring total, we need to circumvent it and
	 * manage it entirely ourselves.)
	 *
	 * @param array    $paypal_args Paypal arguments.
	 * @param WC_Order $order WooCommerce order object.
	 * @return array $paypal_args Paypal arguments.
	 * @since 2.0
	 */
	public static function get_paypal_args( $paypal_args = array(), $order = object ) {

		// Return defalut arguement if not an order.
		if ( false === sa_bn_wc_is_order( $order ) ) {
			return $paypal_args;
		}

		// Proceed only when valid.
		if ( true === self::should_modify_paypal_args() ) {
			if ( ( isset( $_REQUEST['woocommerce-process-checkout-nonce'] ) ) && ! wp_verify_nonce( wc_clean( wp_unslash( $_REQUEST['woocommerce-process-checkout-nonce'] ) ), 'woocommerce-process_checkout' ) ) { //phpcs:ignore
				return $paypal_args;
			} elseif ( ( isset( $_POST['_wpnonce'] ) ) && ! wp_verify_nonce( wc_clean( wp_unslash( $_POST['_wpnonce'] ) ), 'woocommerce-process_checkout' ) ) { //phpcs:ignore
				return $paypal_args;
			}
			if ( did_action( 'buy_now_post_fields' ) && isset( $_POST['sa-wc-bn-paypal-billing-agreement-id'] ) ) { // phpcs:ignore 

				$billing_agreement_id = wc_clean( wp_unslash( $_POST['sa-wc-bn-paypal-billing-agreement-id'] ) ); // phpcs:ignore 

				if ( self::is_wc_gte_30() ) {
					$order_id = $order->get_id();
				} else {
					$order_id = ( ! empty( $order->id ) ) ? $order->id : 0;
				}

				// We first need to add billing agreement id to current order which will be used while making do reference transaction request.
				update_post_meta( $order_id, '_paypal_subscription_id', $billing_agreement_id );

				self::process_subscription_payment( $order->get_total(), $order );

				if ( ! function_exists( 'is_plugin_active' ) ) {
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
				}

				// Compatibility for subscription plugin.
				if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
					if ( function_exists( 'wcs_order_contains_subscription' ) && wcs_order_contains_subscription( $order, array( 'parent' ) ) ) {
						self::add_billing_agreement_id_to_subscriptions( $order, $billing_agreement_id );
					}
				}

				$redirect_url = add_query_arg(
					array(
						'utm_nooverride'   => '1',
						'buy-now-redirect' => 'yes',
					),
					$order->get_checkout_order_received_url()
				);

				// redirect customer to order received page.
				wp_safe_redirect( esc_url_raw( $redirect_url ) );
				exit;
			} else {
				$paypal_args = self::get_api()->get_paypal_args( $paypal_args, $order );
			}
		}

		return $paypal_args;
	}

	/**
	 * Function to get billing agreement id from last order
	 *
	 * @param  integer $last_order_id Order ID.
	 * @param  WP_User $current_user User object.
	 */
	public static function add_billing_agreement_id( $last_order_id = 0, $current_user = null ) {

		$order   = wc_get_order( $last_order_id );
		$user_id = $current_user->ID;

		$last_payment_method = ( ! empty( $order ) ) ? $order->get_payment_method() : '';

		if ( 'paypal' === $last_payment_method ) {

			$billing_agreement_id = get_post_meta( $last_order_id, '_paypal_subscription_id', true );

			// If billing agreement id not found in last order fetch it from user meta.
			if ( empty( $billing_agreement_id ) ) {
				$billing_agreement_id = get_user_meta( $user_id, '_paypal_subscription_id', true );
			}

			if ( ! empty( $billing_agreement_id ) ) {
				$_POST['sa-wc-bn-paypal-billing-agreement-id'] = $billing_agreement_id;
			}
		}
	}

	/**
	 * Function to check whether we should modify paypal arguments
	 *
	 * @param  WC_Order $order Order Object.
	 * @return bool Flag to decide paypal argument modification
	 */
	public static function should_modify_paypal_args( $order = object ) {

		// Check if reference transactions are enabled in merchant account.
		if ( false === self::are_reference_transactions_enabled() ) {
			return false;
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		/** If subscription plugin is active and order contains a subscription or itself is subscription then don't modify paypal args to avoid conflict with Subscription plugin since subscription plugin itself modify paypal args. */
		if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
			// If cart contains subscription product not added through Buy Now, then allow subscription plugin to take control.
			if ( class_exists( 'WC_Subscriptions_Cart' ) && is_callable( array( 'WC_Subscriptions_Cart', 'cart_contains_subscription' ) ) && WC_Subscriptions_Cart::cart_contains_subscription() && ! did_action( 'buy_now_post_fields' ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Function to add billing agreement id to related subscriptions
	 *
	 * @param WC_Order $order WooCommere Order.
	 * @param string   $billing_agreement_id paypal billing agreement id.
	 */
	public static function add_billing_agreement_id_to_subscriptions( $order = object, $billing_agreement_id = '' ) {

		if ( false === sa_bn_wc_is_order( $order ) || empty( $billing_agreement_id ) ) {
			return;
		}

		// Make sure PayPal is set as the payment method on the order and subscription.
		$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
		$payment_method     = isset( $available_gateways[ self::instance()->get_id() ] ) ? $available_gateways[ self::instance()->get_id() ] : false;
		$order->set_payment_method( $payment_method );

		foreach ( wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'any' ) ) as $subscription ) {
			$subscription->set_payment_method( $payment_method );
			sa_bn_wc_set_paypal_id( $subscription, $billing_agreement_id ); // Also saves the subscription.
		}

	}

	/**
	 * When a PayPal IPN messaged is received for a subscription transaction,
	 * check the transaction details and
	 *
	 * @link https://developer.paypal.com/docs/classic/ipn/integration-guide/IPNandPDTVariables/
	 *
	 * @param array $transaction_details transaction details array.
	 * @since 2.0
	 */
	public static function process_ipn_request( $transaction_details = array() ) {

		try {
			if ( ! isset( $transaction_details['txn_type'] ) || ! in_array( $transaction_details['txn_type'], array_merge( self::get_ipn_handler( 'standard' )->get_transaction_types(), self::get_ipn_handler( 'reference' )->get_transaction_types() ), true ) ) {
				return;
			}

			WC_Gateway_Paypal::log( 'Subscription Transaction Type: ' . $transaction_details['txn_type'] );
			WC_Gateway_Paypal::log( 'Subscription Transaction Details: ' . print_r( $transaction_details, true ) ); // phpcs:ignore 

			if ( in_array( $transaction_details['txn_type'], self::get_ipn_handler( 'standard' )->get_transaction_types(), true ) ) {
				self::get_ipn_handler( 'standard' )->valid_response( $transaction_details );
			} elseif ( in_array( $transaction_details['txn_type'], self::get_ipn_handler( 'reference' )->get_transaction_types(), true ) ) {
				self::get_ipn_handler( 'reference' )->valid_response( $transaction_details );
			}
		} catch ( Exception $e ) {
			SA_Buy_Now_WC_Paypal_Standard_IPN_Failure_Handler::log_unexpected_exception( $e );
		}
	}

	/**
	 * Check whether a given subscription is using reference transactions and if so process the payment.
	 *
	 * @param float    $amount order total.
	 * @param WC_Order $order order to be processed in Buy Now's one click checkout.
	 * @since 2.0
	 */
	public static function process_subscription_payment( $amount = 0, $order = object ) {

		// If the subscription is using reference transactions, we can process the payment ourselves.
		$paypal_profile_id = sa_bn_wc_get_paypal_id( sa_bn_wc_get_objects_property( $order, 'id' ) );

		if ( sa_bn_wc_is_paypal_profile_a( $paypal_profile_id, 'billing_agreement' ) ) {

			if ( 0 === $amount ) {
				$order->payment_complete();
				return;
			}

			$response = self::get_api()->do_reference_transaction(
				$paypal_profile_id,
				$order,
				array(
					'amount'         => $amount,
					'invoice_number' => self::get_option( 'invoice_prefix' ) . sa_bn_wc_str_to_ascii( ltrim( $order->get_order_number(), _x( '#', 'hash before the order number. Used as a character to remove from the actual order number', 'cashier' ) ) ),
				)
			);

			self::process_subscription_payment_response( $order, $response );
		}
	}

	/**
	 * Process a payment based on a response
	 *
	 * @param WC_Order                                                $order order which is processed in Buy Now's one click checkout.
	 * @param SA_Buy_Now_WC_PayPal_Reference_Transaction_API_Response $response response from Paypal after order processed.
	 * @since 2.6.0
	 */
	public static function process_subscription_payment_response( $order = object, $response = object ) {

		if ( $response->has_api_error() ) {

			$error_message = $response->get_api_error_message();

			// Some PayPal error messages end with a fullstop, others do not, we prefer our punctuation consistent, so add one if we don't already have one.
			if ( '.' !== substr( $error_message, -1 ) ) {
				$error_message .= '.';
			}

			// translators: placeholders are PayPal API error code and PayPal API error message.
			$order->update_status( 'failed', sprintf( __( 'PayPal API error: (%1$d) %2$s', 'cashier' ), $response->get_api_error_code(), $error_message ) );

		} elseif ( $response->transaction_held() ) {

			// translators: placeholder is PayPal transaction status message.
			$order_note   = sprintf( __( 'PayPal Transaction Held: %s', 'cashier' ), $response->get_status_message() );
			$order_status = apply_filters( 'sa_bn_wc_paypal_held_payment_order_status', 'on-hold', $order, $response );

			// mark order as held.
			if ( ! $order->has_status( $order_status ) ) {
				$order->update_status( $order_status, $order_note );
			} else {
				$order->add_order_note( $order_note );
			}
		} elseif ( ! $response->transaction_approved() ) {

			// translators: placeholder is PayPal transaction status message.
			$order->update_status( 'failed', sprintf( __( 'PayPal payment declined: %s', 'cashier' ), $response->get_status_message() ) );

		} elseif ( $response->transaction_approved() ) {

			// translators: placeholder is PayPal transaction id.
			$order->add_order_note( sprintf( __( 'PayPal payment approved (ID: %s)', 'cashier' ), $response->get_transaction_id() ) );

			$order->payment_complete( $response->get_transaction_id() );
		}
	}

	/** Getters ******************************************************/

	/**
	 * Get the API object
	 *
	 * @see SV_WC_Payment_Gateway::get_api()
	 * @param string $ipn_type Paypal IPN request type.
	 * @return WC_PayPal_Express_API API instance
	 * @since 2.0
	 */
	protected static function get_ipn_handler( $ipn_type = 'standard' ) {

		$use_sandbox = ( 'yes' === self::get_option( 'testmode' ) );

		if ( 'reference' === $ipn_type ) {

			if ( ! isset( self::$ipn_handlers['reference'] ) ) {
				self::$ipn_handlers['reference'] = new SA_Buy_Now_WC_Paypal_Reference_Transaction_IPN_Handler( $use_sandbox, self::get_option( 'receiver_email' ) );
			}

			$ipn_handler = self::$ipn_handlers['reference'];

		} else {

			if ( ! isset( self::$ipn_handlers['standard'] ) ) {
				self::$ipn_handlers['standard'] = new SA_Buy_Now_WC_Paypal_Standard_IPN_Handler( $use_sandbox, self::get_option( 'receiver_email' ) );
			}

			$ipn_handler = self::$ipn_handlers['standard'];

		}

		return $ipn_handler;
	}

	/**
	 * Get the API object
	 *
	 * @return WCS_PayPal_Express_API API instance
	 * @since 2.0
	 */
	public static function get_api() {

		if ( is_object( self::$api ) ) {
			return self::$api;
		}

		if ( ! class_exists( 'WC_Gateway_Paypal_Response' ) ) {
			require_once WC()->plugin_path() . '/includes/gateways/paypal/includes/class-wc-gateway-paypal-response.php';
		}

		$environment = ( 'yes' === self::get_option( 'testmode' ) ) ? 'sandbox' : 'production';

		self::$api = new SA_Buy_Now_WC_PayPal_Reference_Transaction_API( 'paypal', $environment, self::get_option( 'api_username' ), self::get_option( 'api_password' ), self::get_option( 'api_signature' ) );

		return self::$api;
	}

	/**
	 * Return the default WC PayPal gateway's settings.
	 *
	 * @since 2.0
	 */
	public static function reload_options() {
		self::get_options();
	}

	/**
	 * Return the default WC PayPal gateway's settings.
	 *
	 * @since 2.0
	 */
	protected static function get_options() {

		self::$paypal_settings = get_option( 'woocommerce_paypal_settings' );

		return self::$paypal_settings;
	}

	/** Logging **/

	/**
	 * Log API request/response data
	 *
	 * @since 2.0
	 * @param array $request_data request data parameters array.
	 * @param array $response_data response data parameters array.
	 */
	public static function log_api_requests( $request_data = array(), $response_data = array() ) {
		WC_Gateway_Paypal::log( 'Subscription Request Parameters: ' . print_r( $request_data, true ) ); //phpcs:ignore
		WC_Gateway_Paypal::log( 'Subscription Request Response: ' . print_r( $response_data, true ) ); //phpcs:ignore
	}

	/**
	 * Method required by WCS_SV_API_Base, which normally requires an instance of SV_WC_Plugin *
	 */
	public function get_plugin_name() {
		return _x( 'WooCommerce Buy Now', 'used in User Agent data sent to PayPal to help identify where a payment came from', 'cashier' );
	}

	/**
	 * Function to get version of Buy Now plugin
	 *
	 * @return string $version Buy Now plugin's version.
	 */
	public function get_version() {
		$plugin_data = self::get_bn_plugin_data();
		$version     = '';
		if ( is_array( $plugin_data ) && isset( $plugin_data['Version'] ) ) {
			$version = $plugin_data['Version'];
		}
		return $version;
	}

	/**
	 * Function to get id of Buy Now plugin
	 *
	 * @return string $id Buy Now plugin's id.
	 */
	public function get_id() {
		return 'paypal';
	}

	/**
	 * Gets orders with a given paypal subscription id.
	 *
	 * @since 2.5.4
	 * @param string $paypal_id The PayPal Standard Profile ID or PayPal Reference Transactions Billing Agreement.
	 * @param string $return    Optional. The type to return. Can be 'ids' to return subscription IDs or 'objects' to return WC_Subscription objects. Default 'ids'.
	 * @return WC_Subscription[]|int[] Subscriptions (objects or IDs) with the PayPal Profile ID or Billing Agreement stored in meta.
	 */
	public static function get_orders_by_paypal_id( $paypal_id, $return = 'ids' ) {

		if ( ! isset( self::$orders_by_paypal_id[ $paypal_id ] ) ) {
			$subscription_ids = get_posts(
				array(
					'posts_per_page' => -1,
					'post_type'      => 'shop_order',
					'post_status'    => 'any',
					'fields'         => 'ids',
					'meta_query'     => array( // phpcs:ignore
						array(
							'key'     => '_paypal_subscription_id',
							'compare' => '=',
							'value'   => $paypal_id,
						),
					),
				)
			);

			self::$orders_by_paypal_id[ $paypal_id ] = array_combine( $subscription_ids, $subscription_ids );
		}

		if ( 'objects' === $return ) {
			$orders = array_filter( array_map( 'sa_bn_wc_get_order', self::$orders_by_paypal_id[ $paypal_id ] ) );
		} else {
			$orders = self::$orders_by_paypal_id[ $paypal_id ];
		}

		return $orders;
	}

	/**
	 * Function to add this gateway to list of supported gateways for Buy Now's one click checkout.
	 *
	 * @param array $supported_gateways list of supported gateways.
	 * @return array $supported_gateways list of supported gateways.
	 */
	public static function add_gateway_support( $supported_gateways = array() ) {

		if ( ! is_array( $supported_gateways ) ) {
			$supported_gateways = array();
		}

		if ( true === self::are_reference_transactions_enabled() ) {
			$supported_gateways[] = 'paypal';
		}

		return $supported_gateways;
	}

	/**
	 * Function to add status html when this gateway is not supported
	 *
	 * @param string $status_html status html.
	 * @return string $status_html new status html.
	 */
	public static function add_unsupported_gateway_status_html( $status_html = '' ) {

		$status_html = '<span class="sa-bn-status-warning tips" data-tip="' . esc_attr__( 'PayPal Reference Transactions is mandatory to enable One-Click Checkout.', 'cashier' ) . '">' . esc_html__( 'No', 'cashier' ) . '</span>';

		return $status_html;
	}

	/**
	 * Function to add styles and scripts
	 */
	public static function styles_and_scripts() {
		global $pagenow;
		$get_page = ( ! empty( $_GET['page'] ) ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore
		$get_tab  = ( ! empty( $_GET['tab'] ) ) ? wc_clean( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore
		if ( empty( $pagenow ) || 'admin.php' !== $pagenow || empty( $get_page ) || 'wc-settings' !== $get_page || empty( $get_tab ) || 'checkout' !== $get_tab ) {
			return;
		}
		?>
		<style type="text/css" media="screen">
			.sa-bn-status-warning {
				font-size: 1.4em;
				display: block;
				text-indent: -9999px;
				position: relative;
				height: 1em;
				width: 1em;
			}
			.sa-bn-status-warning:before {
				content: "\e016" !important;
				font-family: WooCommerce;
				speak: none;
				font-weight: 400;
				font-variant: normal;
				text-transform: none;
				line-height: 1;
				margin: 0;
				text-indent: 0;
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				text-align: center;
				color: #ccc;
			}
		</style>
		<?php
	}

	/**
	 * Function to show admin notices related to Paypal
	 */
	public static function show_admin_notices() {
		global $pagenow;

		if ( empty( $pagenow ) || ! in_array( $pagenow, array( 'admin.php', 'edit.php' ), true ) ) {
			return;
		}

		if ( 'admin.php' === $pagenow ) {
			$get_page = isset( $_GET['page'] ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore

			if ( empty( $get_page ) || ! in_array( $get_page, array( 'wc-settings', 'sa-wc-cashier' ), true ) ) {
				return;
			}

			$get_tab  = isset( $_GET['tab'] ) ? wc_clean( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore
			if ( ! empty( $get_tab ) ) {
				// Do not show notices on pages other than WooCommerce Payments tab and Buy Now's settings tabs.
				if ( ! in_array( $get_tab, array( 'checkout', 'bn_create_link', 'bn_storewide_settings' ), true ) ) {
					return;
				}
			} elseif ( 'wc-settings' === $get_page ) {
				// Do not show notices on WooCommerce Main Settings page.
				return;
			}

			$get_section  = isset( $_GET['section'] ) ? wc_clean( wp_unslash( $_GET['section'] ) ) : ''; // phpcs:ignore
			if ( ! empty( $get_section ) ) {
				// Do not show notices on gateway edit page if it is not Paypal.
				if ( self::instance()->get_id() !== $get_section ) {
					return;
				}
			}
		} elseif ( 'edit.php' === $pagenow ) {
			$post_type = isset( $_GET['post_type'] ) ? wc_clean( wp_unslash( $_GET['post_type'] ) ) : ''; // phpcs:ignore
			if ( ! in_array( $post_type, array( 'product', 'shop_order' ), true ) ) {
				return;
			}
		}

		$valid_paypal_currency = in_array(
			get_woocommerce_currency(),
			apply_filters(
				'woocommerce_paypal_supported_currencies',
				array(
					'AUD',
					'BRL',
					'CAD',
					'MXN',
					'NZD',
					'HKD',
					'SGD',
					'USD',
					'EUR',
					'JPY',
					'TRY',
					'NOK',
					'CZK',
					'DKK',
					'HUF',
					'ILS',
					'MYR',
					'PHP',
					'PLN',
					'SEK',
					'CHF',
					'TWD',
					'THB',
					'GBP',
					'RMB',
				)
			),
			true
		);

		if ( false === $valid_paypal_currency || false === current_user_can( 'manage_options' ) || 'no' === get_option( 'sa_buy_now_wc_show_paypal_admin_notices', 'yes' ) ) {
			return;
		}

		if ( false === self::are_reference_transactions_enabled() ) {
			if ( 'admin.php' === $pagenow ) {
				?>
			<div class="notice notice-warning">
				<p>
				<?php
					echo '<strong>' . esc_html__( 'Important', 'cashier' ) . ': </strong>' . esc_html__( 'You cannot use Buy Now\'s One-Click checkout with PayPal since PayPal Reference Transactions are not enabled on your PayPal account.', 'cashier' ) . ' <a href="' . esc_url( 'https://www.storeapps.org/docs/bn-paypal-reference-transactions/?utm_source=bn&utm_medium=in_app&utm_campaign=paypal_rt_disabled' ) . '" target="_blank" >' . esc_html__( 'Click here', 'cashier' ) . '</a>' . esc_html__( ' to know steps to enable PayPal Reference Transactions.', 'cashier' ) . '<br>';
				?>
				</p>
			</div>
				<?php
			}
		}
	}

}

SA_Buy_Now_WC_Paypal_Standard::instance();
