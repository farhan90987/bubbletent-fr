<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_WC_Invoice_Pdf_Payment_Information' ) ) {
	
	/**
	* Adding custom payment information in each gateway to invoice pdf
	*
	* @WP_WC_Invoice_Pdf_Payment_Information
	* @version 3.37
	* @category	Class
	*/
	class WP_WC_Invoice_Pdf_Payment_Information {
		
		/**
		 * @var WP_WC_Invoice_Pdf_Payment_Information
		 * @since v3.37
		 */
		private static $instance = null;
		
		/**
		* Singletone get_instance
		*
		* @static
		* @return WGM_Due_date
		*/
		public static function get_instance() {
			if ( self::$instance == NULL) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		* Singletone constructor
		*
		* @access private
		*/
		private function __construct() {
			
			add_filter( 'german_market_gateway_settings_single_gateway', array( $this, 'settings_field' ), 80, 2 );

			$position = get_option( 'wp_wc_invoice_pdf_custom_payment_information', 'off' );
			
			if ( 'before' === $position ) {
				add_action( 'wp_wc_invoice_pdf_custom_before_order_table', array( $this, 'add_information' ), 10, 1 );
			} else if ( 'after' === $position ) {
				add_action( 'wp_wc_invoice_pdf_start_template', array( $this, 'add_hook_for_position_after' ) );
				add_action( 'wp_wc_invoice_pdf_end_template', array( $this, 'remove_hook_for_position_after' ) );
			}

			add_action( 'admin_init', array( $this, 'make_options_translatable' ) );
		}
		
		/**
		 * Make payment options translatable for WPML and Polylang
		 * 
		 * @wp-hook admin_init
		 * @return void
		 */
		public function make_options_translatable() {

			if ( ! is_admin() ) {
				return;
			}

			if ( ( isset( $_REQUEST[ 'page' ] ) && ( $_REQUEST[ 'page' ] == 'wc-settings' || $_REQUEST[ 'page' ] == 'german-market' ) ) ) {
				return;
			}

			$gateways = WC()->payment_gateways()->payment_gateways();


			foreach ( $gateways as $payment_method_id => $gateway ) {

				$gateway_setting = WGM_Payment_Settings::get_option( 'german_market_invoice_text', $gateway );
				if ( ! empty( $gateway_setting ) ) {
					$payment_info_text = $gateway_setting;
				} else {
					$payment_info_text = '';
				}

				if ( ! empty( $payment_info_text ) ) {

					if ( function_exists( 'icl_register_string' ) && function_exists( 'icl_t' ) && function_exists( 'icl_st_is_registered_string' ) ) {

						if ( ! ( icl_st_is_registered_string( 'German Market: Custom Payment Info in Invoice PDF', $payment_info_text ) ) ) {
							icl_register_string( 'German Market: Custom Payment Info in Invoice PDF', $payment_info_text, $payment_info_text );

						}

					} else if ( function_exists( 'pll_register_string' ) && function_exists( 'pll__' ) ) {

							pll_register_string( $payment_info_text, $payment_info_text, 'German Market: Custom Payment Info in Invoice PDF', true );

					}
				}
			}
		}

		/**
		 * Add hook when invoice pdf (not refund) starts
		 * Used if output is after order table
		 * 
		 * @since 3.37
		 * @wp-hook wp_wc_invoice_pdf_start_template
		 * @param Array $args
		 * @return void
		 */
		public function add_hook_for_position_after( $args ) {
			if ( ! isset( $args[ 'refund' ] ) ) {
				add_action( 'woocommerce_email_after_order_table', array( $this, 'add_information' ), 1, 1 );
			}
		}

		/**
		 * Remove hook when invoice pdf ends
		 * Used if output is after order table
		 * 
		 * @since 3.37
		 * @wp-hook wp_wc_invoice_pdf_start_template
		 * @param Array $args
		 * @return void
		 */
		public function remove_hook_for_position_after( $args ) {
			remove_action( 'woocommerce_email_after_order_table', array( $this, 'add_information' ), 1, 1 );
		}

		/**
		* Add option to gateway settings
		*
		* wp-hook german_market_gateway_settings_single_gateway
		* @param Array $settings
		* @param String $gateway_id
		* @return Array
		*/
		public function settings_field( $settings, $gateway_id ) {

			$placeholders = apply_filters( 'wp_wc_invoice_pdf_custom_payment_info_placeholders', array(
				__( 'Order Number', 'woocommerce-german-market' ) => '{{order-number}}',
				__( 'Order Date', 'woocommerce-german-market' ) => '{{order-date}}',
				__( 'Order Total', 'woocommerce-german-market' ) => '{{order-total}}',
				__( 'First name', 'woocommerce-german-market' ) => '{{first-name}}',
				__( 'Last name', 'woocommerce-german-market' ) => '{{last-name}}',
			) );

			if ( 'on' === get_option( 'woocommerce_de_due_date', 'off' ) ) {
				$placeholders[ __( 'Due Date', 'woocommerce-german-market' ) ] = '{{due-date}}';
			}

			if ( class_exists( 'WP_WC_Running_Invoice_Number_Functions' ) ) {
				$placeholders[ __( 'Invoice Number', 'woocommerce-german-market' ) ] = '{{invoice-number}}';
			}

			$placeholders_string = '';
			foreach ( $placeholders as $label => $code ) {
				if ( ! empty( $placeholders_string ) ) {
					$placeholders_string .= ', ';
				}
				$placeholders_string .= $label . ' - ' . '<code>' . $code . '</code>';
			}

			$settings[ 'german_market_invoice_text' ] = array(
				'type'			=> 'textarea',
				'title'			=>  __( 'Invoice PDF', 'woocommerce-german-market' ) . ': ' . __( 'Payment information', 'woocommerce-german-market' ),
				'description'	=> __( 'You can use the following placeholders:', 'woocommerce-german-market' ) . ' ' . $placeholders_string . '<br>' . __( 'You can use HTML.', 'woocommerce-german-market' ),
			);	

			return $settings;
		}

		/**
		 * Add payment information to invoice pdf
		 * 
		 * @wp-hook wp_wc_invoice_pdf_custom_before_order_table
		 * @param WC_Order $order
		 * @param Array $args
		 */
		public static function add_information( $order ) {

			if ( is_object( $order ) && method_exists( $order, 'get_payment_method' ) ) {
				$payment_method_id	= $order->get_payment_method();

				if ( $payment_method_id != '' ) {
					$gateways = WC()->payment_gateways()->payment_gateways();
					if ( isset( $gateways[ $payment_method_id ] ) ) {
						$gateway = $gateways[ $payment_method_id ];
						$gateway_setting = WGM_Payment_Settings::get_option( 'german_market_invoice_text', $gateway );
						if ( ! empty( $gateway_setting ) ) {
							
							$text = $gateway_setting;
							
							// WPML and Polylang Support
							if ( function_exists( 'icl_register_string' ) && function_exists( 'icl_t' ) && function_exists( 'icl_st_is_registered_string' ) ) {
								$text = icl_t( 'German Market: Custom Payment Info in Invoice PDF', $text, $text );	
							} else if ( function_exists( 'pll__' ) ) {
								$text = pll__( $text );
							}

							if ( ! empty( $text ) ) {

								$placeholders = array(
									'{{order-number}}' 	=> $order->get_order_number(),
									'{{order-date}}' 	=> date_i18n( get_option( 'date_format' ), $order->get_date_created()->getTimestamp() ),
									'{{order-total}}' 	=> strip_tags( wc_price( $order->get_total(), array( 'currency' => $order->get_currency() ) ) ),
									'{{first-name}}'	=> $order->get_billing_first_name(),
									'{{last-name}}'		=> $order->get_billing_last_name(),
								);

								$due_date = $order->get_meta( '_wgm_due_date' );

								if ( $due_date != '' ) {
									$due_date = apply_filters( 'woocommerce_de_due_date_string', date_i18n( wc_date_format(), strtotime( $due_date ) ), $due_date, $order );
								}

								$placeholders[ '{{due-date}}' ] = $due_date;

								if ( class_exists( 'WP_WC_Running_Invoice_Number_Functions' ) ) {
									$running_invoice_number = new WP_WC_Running_Invoice_Number_Functions( $order );
									$placeholders[ '{{invoice-number}}' ] = $running_invoice_number->get_invoice_number();
								}

								$placeholders = apply_filters( 'wp_wc_invoice_pdf_custom_payment_info_placeholders_replace', $placeholders, $order );

								$text = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $text );

								echo wp_kses_post( nl2br( $text ) );
							}
						}
					}
				}
			}
		}
	}
}
