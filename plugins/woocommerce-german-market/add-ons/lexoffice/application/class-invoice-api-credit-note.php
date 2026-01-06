<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

if ( ! class_exists( 'German_Market_Lexoffice_Invoice_API_Credit_Note' ) ) {

	/**
	 * Class German_Market_Lexoffice_Invoice_API
	 */
	class German_Market_Lexoffice_Invoice_API_Credit_Note {

		/**
		* Send refund as credit-notes to lexoffice
		*
		* @param WC_Order_Refund $refund
		* @param Boolean $show_errors
		* @return String | void
		*/	
		public static function send_refund( $refund, $show_errors = true ) {

			// get meta to decide whether there is already a transmission to lexofice
			$order_lexoffice_status = $refund->get_meta( '_lexoffice_woocomerce_has_transmission_invoice_api' );
			
			if ( ! empty( $order_lexoffice_status ) ) {
				
				// check if invoice still exists
				$status = self::retrieve_a_credit_note( $order_lexoffice_status );
				
				if ( ! $status ) {

					$order_lexoffice_status = true;
					$return_message = 'NOTEX_A';
					$return_message .= __( 'The outbound document is no longer available in Lexware Office.', 'woocommerce-german-market' );
					
					if ( 'on' === get_option( 'woocommerce_de_lexoffice_invoice_api_allow_retransmission', 'off' ) ) {

						// invoice does not exists anymore in lexoffice, remove saved meta data for document ids
						German_Market_Lexoffice_Invoice_API_General::remove_lexoffice_invoice_meta_data( $refund );

						$return_message = 'NOTEX_B';
						$return_message .= __( 'The outbound document is no longer available in Lexware Office. The link between the WooCommerce document and the Lexware Office document has been removed. It is now possible to execute the transfer to Lexware Office again.', 'woocommerce-german-market' );
					}

				} else {

					// invoice still exists in lexoffice
					$order_lexoffice_status = true;
					$return_message = 'EXISTS_';
					$return_message .= __( 'The refund has already been transferred to Lexware Office. An update cannot take place.', 'woocommerce-german-market' );
				}

			} else {
				$order_lexoffice_status = false;
				$return_message = 'SUCCESS';
			}

			if ( ! $order_lexoffice_status ) {

				German_Market_Lexoffice_Invoice_API_General::before_send( $refund );

				$token_bucket = new WGM_Token_Bucket( 'lexoffice-credit-notes', 2 );
				$token_bucket->consume();

				$curl = curl_init();

				$lexoffice_data = self::create_refund_array( $refund, $show_errors );

				curl_setopt_array( $curl, 

					array(
						CURLOPT_URL => German_Market_Lexoffice_API_Auth::get_base_url() . "v1/credit-notes" . German_Market_Lexoffice_Invoice_API_Items::get_draft_mode_paramter(),
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

				German_Market_Lexoffice_Invoice_API_General::after_send( $refund );

				// evaluate response
				if ( ! isset ( $response_array[ 'id' ] ) ) {
					if ( $show_errors ) {
						return '<b>' . __( 'Error', 'woocommerce-german-market' ) . ':</b> ' . German_Market_Lexoffice_API_General::get_error_text( German_Market_Lexoffice_Invoice_API_General::error_array_to_string( $response_array, $lexoffice_data ), $refund );
					} else {
						return;
					}
				}

				// save lexoffice id as post meta
				$refund_lexoffice_status = $response_array[ 'id' ];
				$refund->update_meta_data( '_lexoffice_woocomerce_has_transmission_invoice_api', $response_array[ 'id' ] );

				// save lexoffice invoice number as post meta
				$lexoffice_invoice = self::retrieve_a_credit_note ( $response_array[ 'id' ], 'invoice' );
				if ( isset( $lexoffice_invoice[ 'voucherNumber' ] ) ) {
					$refund->update_meta_data( '_lexoffice_invoice_number', $lexoffice_invoice[ 'voucherNumber' ] );
				}
				
				$refund->save_meta_data();

				// render document (if possible) in lexoffice
				if ( 'off' === get_option( 'woocommerce_de_lexoffice_invoice_api_draft_mode', 'off' ) ) {
					$document_id = German_Market_Lexoffice_Invoice_API_General::render_document_and_get_document_id( $refund_lexoffice_status, $refund, 'credit-notes' );
					if ( $document_id && 'DRAFT' !== $document_id ) {
						$return_message .= $document_id;
					}
				}

				// transaction assignment
				$transaction = new German_Market_Lexoffice_API_Transaction_Assignment( $refund );
			}

			return $return_message;
		}

		/**
		 * Create array for credit note from WC_Order_refund
		 * 
		 * @param WC_Order_Refund $refund
		 * @param Boolean $show_errors
		 * @return Array
		 */
		public static function create_refund_array( $refund, $show_errors = true ) {

			$is_refund = true;

			// required fields
			$lexoffice_credit_note = array(
				'voucherDate'			=> German_Market_Lexoffice_Invoice_API_Items::get_voucher_date( $refund, $show_errors ),
				'lineItems'				=> German_Market_Lexoffice_Invoice_API_Items::get_line_items_of_order( $refund, $show_errors, $is_refund ),
				'totalPrice'			=> German_Market_Lexoffice_Invoice_API_Items::get_total_price( $refund, $show_errors ),
				'taxConditions'			=> German_Market_Lexoffice_Invoice_API_Items::get_tax_conditions( $refund, $show_errors ),
				'shippingConditions'	=> German_Market_Lexoffice_Invoice_API_Items::get_shipping_conditions( $refund, $show_errors ),
			);

			$lexoffice_credit_note = German_Market_Lexoffice_Invoice_API_Items::add_contact( $lexoffice_credit_note, $refund, $show_errors );

			// extra data: title, introduction, remark
			$extra_data = array(

				'title'			=> array( 
									'option_key'	=> 'woocommerce_de_lexoffice_invoice_api_title_credit_note',
									'default'		=> __( 'Invoice Correction', 'woocommerce-german-market' ),
									'can_be_empty'	=> false,
								),

				'introduction'	=> array( 
									'option_key'	=> 'woocommerce_de_lexoffice_invoice_introduction_credit_note',
									'default'		=> __( 'The following deliveries/services will be credited to your account.', 'woocommerce-german-market' ),
									'can_be_empty'	=> true,
								),

				'remark'		=> array( 
									'option_key'	=> 'woocommerce_de_lexoffice_invoice_remark_credit_note',
									'default'		=> __( 'Yours sincerely.', 'woocommerce-german-market' ),
									'can_be_empty'	=> true,
								),
			);
		
			foreach ( $extra_data as $key => $settings ) {

				$option_value = get_option( $settings[ 'option_key' ], $settings[ 'default' ] );

				if ( ( ! empty( $option_value ) ) || ( empty( $option_value ) && $settings[ 'can_be_empty' ] ) ) {

					$order = wc_get_order( $refund->get_parent_id() );
				
					$placeholders = array(
						'{{refund-id}}' 	=> $refund->get_id(),
						'{{order-number}}' 	=> $order->get_order_number(),
						'{{order-date}}' 	=> date_i18n( get_option( 'date_format' ), $order->get_date_created()->getTimestamp() ),
						'{{order-total}}' 	=> html_entity_decode( strip_tags( wc_price( $order->get_total(), array( 'currency' => $order->get_currency() ) ) ) ),
						'{{first-name}}'	=> $order->get_billing_first_name(),
						'{{last-name}}'		=> $order->get_billing_last_name(),
						'{{lexwareoffice-invoice-number}}' => $order->get_meta( '_lexoffice_invoice_number' ),
					);
					
					$placeholders = apply_filters( 'german_market_lexoffice_invoice_api_payment_placeholders', $placeholders, $order );

					$option_value = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $option_value );

					$lexoffice_credit_note[ $key ] = $option_value;
				}
			}

			// print layout
			$print_layout = get_option( 'woocommerce_de_lexoffice_invoice_api_print_layout_credit_note', '' );
			if ( ! empty( $print_layout ) ) {
				$lexoffice_credit_note[ 'printLayoutId' ] = $print_layout;
			}

			$lexoffice_credit_note = apply_filters( 'german_market_lexoffice_invoice_api_credit_note_array', $lexoffice_credit_note, $refund );

			ini_set( 'serialize_precision', -1 );
			$json = json_encode( $lexoffice_credit_note, JSON_PRETTY_PRINT );

			return $json;
		}

		/**
		* Retrieve an credit note to check if it exists
		* 
		* @param Integer $invoice_id
		* @param String $kind_of_return_value 'bool' | 'invoice'
		* @return Boolean
		*/ 
		public static function retrieve_a_credit_note( $invoice_id, $kind_of_return_value = 'bool' ) {
			return German_Market_Lexoffice_Invoice_API_General::retrieve_invoice_api_document( $invoice_id, $kind_of_return_value, 'credit-notes' );
		}
	}
}
