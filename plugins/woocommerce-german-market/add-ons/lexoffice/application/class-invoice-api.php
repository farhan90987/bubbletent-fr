<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

if ( ! class_exists( 'German_Market_Lexoffice_Invoice_API' ) ) {

	/**
	 * Class German_Market_Lexoffice_Invoice_API
	 */
	class German_Market_Lexoffice_Invoice_API {

		/**
		* Send order as invoice to lexoffice
		*
		* @param WC_Order $order
		* @param Boolean $show_errors
		* @return String | void
		*/		
		public static function send_order( $order, $show_errors = true ) {

			$before_transmission_start = self::before_transmission_start( $order );
			if ( ! empty( $before_transmission_start ) ) {
				if ( $show_errors ) {
					return $before_transmission_start;
				} else {
					return;
				}
			}

			// get meta to decide whether there is already a transmission to lexofice
			$order_lexoffice_status = $order->get_meta( '_lexoffice_woocomerce_has_transmission_invoice_api' );

			if ( ! empty( $order_lexoffice_status ) ) {
				
				// check if invoice still exists
				$status = self::retrieve_an_invoice( $order_lexoffice_status );
				
				if ( ! $status ) {

					$order_lexoffice_status = true;
					$return_message = 'NOTEX_A';
					$return_message .= __( 'The outbound document is no longer available in Lexware Office.', 'woocommerce-german-market' );

					if ( 'on' === get_option( 'woocommerce_de_lexoffice_invoice_api_allow_retransmission', 'off' ) ) {

						// invoice does not exists anymore in lexoffice, remove saved meta data for document ids
						German_Market_Lexoffice_Invoice_API_General::remove_lexoffice_invoice_meta_data( $order );

						$return_message = 'NOTEX_B';
						$return_message .= __( 'The outbound document is no longer available in Lexware Office. The link between the WooCommerce document and the Lexware Office document has been removed. It is now possible to execute the transfer to Lexware Office again.', 'woocommerce-german-market' );
					}

				} else {

					// invoice still exists in lexoffice
					$order_lexoffice_status = true;
					$return_message = 'EXISTS_';
					$return_message .= __( 'The order has already been transferred to Lexware Office. An update cannot take place.', 'woocommerce-german-market' );
				}

			} else {
				$order_lexoffice_status = false;
				$return_message = 'SUCCESS';
			}			

			if ( ! $order_lexoffice_status ) {

				$token_bucket = new WGM_Token_Bucket( 'lexoffice-invoices', 2 );
				$token_bucket->consume();

				German_Market_Lexoffice_Invoice_API_General::before_send( $order );

				$curl = curl_init();

				$lexoffice_data = self::create_invoice_array( $order, $show_errors );

				curl_setopt_array( $curl, 

					array(
						CURLOPT_URL => German_Market_Lexoffice_API_Auth::get_base_url() . "v1/invoices" . German_Market_Lexoffice_Invoice_API_Items::get_draft_mode_paramter(),
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_ENCODING => "",
						CURLOPT_MAXREDIRS => 10,
						CURLOPT_TIMEOUT => 30,
						CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						CURLOPT_CUSTOMREQUEST => "POST",
						CURLOPT_POSTFIELDS => $lexoffice_data,
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

				German_Market_Lexoffice_Invoice_API_General::after_send( $order );

				// evaluate response
				if ( ! isset ( $response_array[ 'id' ] ) ) {
					if ( $show_errors ) {
						return '<b>' . __( 'Error', 'woocommerce-german-market' ) . ':</b> ' . German_Market_Lexoffice_API_General::get_error_text( German_Market_Lexoffice_Invoice_API_General::error_array_to_string( $response_array, $lexoffice_data ), $order );
					} else {
						return;
					}
				}

				// save lexoffice id as post meta
				$order_lexoffice_status = $response_array[ 'id' ];
				$order->update_meta_data( '_lexoffice_woocomerce_has_transmission_invoice_api', $response_array[ 'id' ] );

				// save lexoffice invoice number as post meta
				$lexoffice_invoice = self::retrieve_an_invoice( $response_array[ 'id' ], 'invoice' );
				if ( isset( $lexoffice_invoice[ 'voucherNumber' ] ) ) {
					$order->update_meta_data( '_lexoffice_invoice_number', $lexoffice_invoice[ 'voucherNumber' ] );
				}
				
				$order->save_meta_data();

				// render document (if possible) in lexoffice
				if ( 'off' === get_option( 'woocommerce_de_lexoffice_invoice_api_draft_mode', 'off' ) ) {
					$document_id = German_Market_Lexoffice_Invoice_API_General::render_document_and_get_document_id( $order_lexoffice_status, $order );
					if ( $document_id && 'DRAFT' !== $document_id ) {
						$return_message .= $document_id;
					}
				}

				// transaction assignment
				$transaction = new German_Market_Lexoffice_API_Transaction_Assignment( $order, 'invoice' );
			}

			return $return_message;
		}

		/**
		* Check whether transmission can be executed
		*
		* @param WC_Order_Refund $refund
		* @return String
		*/
		public static function before_transmission_start( $order ) {

			if ( ! German_Market_Lexoffice_Invoice_API_General::is_order_allowed_for_transmission( $order ) ) {

				if ( ! apply_filters( 'woocommerce_de_lexoffice_force_transmission_even_if_not_completed', false, $order ) ) {
					return sprintf( __( '<b>ERROR:</b> According to the admin settings, it is not possible to send an order with the status "%s" to Lexware Office.', 'woocommerce-german-market' ), wc_get_order_status_name( $order->get_status() ) );
				}
			}

			return '';
		}

		/**
		* Create json to send order as invoice to lexoffice
		*
		* @param WC_Order $order
		* @param Boolean $show_erros
		* @return String
		*/
		public static function create_invoice_array( $order, $show_errors = true ) {

			// required fields
			$lexoffice_invoice = array(
				'voucherDate'			=> German_Market_Lexoffice_Invoice_API_Items::get_voucher_date( $order, $show_errors ),
				'lineItems'				=> German_Market_Lexoffice_Invoice_API_Items::get_line_items_of_order( $order, $show_errors ),
				'totalPrice'			=> German_Market_Lexoffice_Invoice_API_Items::get_total_price( $order, $show_errors ),
				'taxConditions'			=> German_Market_Lexoffice_Invoice_API_Items::get_tax_conditions( $order, $show_errors ),
				'shippingConditions'	=> German_Market_Lexoffice_Invoice_API_Items::get_shipping_conditions( $order, $show_errors ),
			);

			$lexoffice_invoice = German_Market_Lexoffice_Invoice_API_Items::add_contact( $lexoffice_invoice, $order, $show_errors );
			$lexoffice_invoice = self::add_payment_conditions( $lexoffice_invoice, $order, $show_errors );

			// extra data: title, introduction, remark
			$extra_data = array(

				'title'			=> array( 
									'option_key'	=> 'woocommerce_de_lexoffice_invoice_api_title',
									'default'		=> __( 'Invoice', 'woocommerce-german-market' ),
									'can_be_empty'	=> false,
								),

				'introduction'	=> array( 
									'option_key'	=> 'woocommerce_de_lexoffice_invoice_introduction',
									'default'		=> __( 'We invoice you for our services/deliveries as follows:', 'woocommerce-german-market' ),
									'can_be_empty'	=> true,
								),

				'remark'		=> array( 
									'option_key'	=> 'woocommerce_de_lexoffice_invoice_remark',
									'default'		=> __( 'Many thanks for the good cooperation.', 'woocommerce-german-market' ),
									'can_be_empty'	=> true,
								),


			);
		
			foreach ( $extra_data as $key => $settings ) {

				$option_value = get_option( $settings[ 'option_key' ], $settings[ 'default' ] );

				if ( ( ! empty( $option_value ) ) || ( empty( $option_value ) && $settings[ 'can_be_empty' ] ) ) {

					$placeholders = array(
						'{{order-number}}' 	=> $order->get_order_number(),
						'{{order-date}}' 	=> date_i18n( get_option( 'date_format' ), $order->get_date_created()->getTimestamp() ),
						'{{order-total}}' 	=> html_entity_decode( strip_tags( wc_price( $order->get_total(), array( 'currency' => $order->get_currency() ) ) ) ),
						'{{first-name}}'	=> $order->get_billing_first_name(),
						'{{last-name}}'		=> $order->get_billing_last_name(),
					);

					$placeholders = apply_filters( 'german_market_lexoffice_invoice_api_payment_placeholders', $placeholders, $order );

					$option_value = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $option_value );

					$lexoffice_invoice[ $key ] = $option_value;
				}
			}

			// print layout
			$print_layout = get_option( 'woocommerce_de_lexoffice_invoice_api_print_layout', '' );
			if ( ! empty( $print_layout ) ) {
				$lexoffice_invoice[ 'printLayoutId' ] = $print_layout;
			}

			$lexoffice_invoice = apply_filters( 'german_market_lexoffice_invoice_api_invoice_array', $lexoffice_invoice, $order );

			ini_set( 'serialize_precision', -1 );
			$json = json_encode( $lexoffice_invoice, JSON_PRETTY_PRINT );

			return $json;
		}

		/**
		 * Payment Conditions
		 * Set paymentTermDuration & paymentTermLabel
		 * 
		 * @param Array $lexoffice_invoice
		 * @param WC_Order $order
		 * @param Boolean $show_errors
		 * @return Array
		 */
		public static function add_payment_conditions( $lexoffice_invoice, $order, $show_errors = true ) {

			$payment_conditions = array();

			// paymentTermDuration
			if ( is_object( $order ) && method_exists( $order, 'get_meta' ) ) {

				$due_date_meta = $order->get_meta( '_wgm_due_date' );

				if ( ! empty( $due_date_meta ) ) {
					
					$due_date_object = new DateTimeImmutable( $due_date_meta );
					$now = current_datetime();
					$now = $now->setTime( 0, 0, 0, 0 );

					$interval = $now->diff( $due_date_object );
					$days = intval( $interval->format( '%R%a' ) );

					$payment_duration = apply_filters( 'german_market_lexoffice_invoice_api_payment_duration', $days < 0 ? 0 : $days, $days, $order );
					if ( $payment_duration >= 0 ) {
						$payment_conditions[ 'paymentTermDuration' ] = $payment_duration;
					}

					$payment_method_id	= $order->get_payment_method();

					if ( $payment_method_id != '' ) {
						$gateways = WC()->payment_gateways()->payment_gateways();
						if ( isset( $gateways[ $payment_method_id ] ) ) {
							$gateway = $gateways[ $payment_method_id ];
							
							$text = '';
							$payment_setting = WGM_Payment_Settings::get_option( 'lexoffice_due_date_invoice_notice', $gateway );

							if ( ! empty( $payment_setting ) ) {
								$text = $payment_setting;
							}

							if ( empty( $text ) ) {
								$text = get_option( 'woocommerce_de_lexoffice_invoice_default_payment_conditions', __( 'Payable immediately.', 'woocommerce-german-market' ) );
							}

							if ( ! empty( $text ) ) {

								$days_for_text = $days;
								if ( $days_for_text < 0 ) {
									$gateway_setting = WGM_Payment_Settings::get_option( 'lexoffice_due_date', $gateway );
									if ( ( ! empty( $gateway_setting ) ) && ( 0 <= intval( $gateway_setting ) ) ) {
										$days_for_text = intval( $gateway_setting );
									}
								}

								if ( $days_for_text >= 0 ) {

									$placeholders = array(
										'{{order-date}}' 			=> date_i18n( wc_date_format(), $order->get_date_created()->getTimestamp() ),
										'{{order-total}}' 			=> html_entity_decode( strip_tags( wc_price( $order->get_total(), array( 'currency' => $order->get_currency() ) ) ) ),
										'{{due-date}}'				=> date_i18n( wc_date_format(), $due_date_object->getTimestamp() ),
										'{{days}}'					=> $days_for_text,
									);

									$placeholders = apply_filters( 'german_market_lexoffice_invoice_api_payment_placeholders', $placeholders, $order );

									$text = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $text );

									$payment_conditions[ 'paymentTermLabel' ] = $text;
								}
							}
						}
					}
				}
			}

			// If "paymentTermLabel" is not set, unset paymentTermDuration. Default text by lefoffice will be used
			if ( isset( $payment_conditions[ 'paymentTermDuration' ] ) && ! isset( $payment_conditions[ 'paymentTermLabel' ] ) ) {
				$payment_conditions = array();
			}

			if ( ! empty( $payment_conditions ) ) {
				$lexoffice_invoice[ 'paymentConditions' ] = $payment_conditions;
			}

			return $lexoffice_invoice;
		}

		/**
		* Retrieve an invoice to check if it exists
		* 
		* @param Integer $invoice_id
		* @param String $kind_of_return_value 'bool' | 'invoice'
		* @return Boolean
		*/ 
		public static function retrieve_an_invoice( $invoice_id, $kind_of_return_value = 'bool' ) {
			return German_Market_Lexoffice_Invoice_API_General::retrieve_invoice_api_document( $invoice_id, $kind_of_return_value, 'invoices' );
		}

		/**
		* Get API Error Messages from error cpde
		* 
		* @param String $error_code
		* @return String
		*/
		public static function get_error_messages( $error_code ) {

			$messages = array(
				'01'	=>  __( 'You are not authorized to download the pdf document. Please renew your Lexware Office authorization.', 'woocommerce-german-market' ),
				'02'	=> __( 'Document not found.', 'woocommerce-german-market' ),
			);

			return isset( $messages[ $error_code ] ) ? $messages[ $error_code ] : __( 'Unknown error', 'woocommerce-german-market' );
		}
	}
}
