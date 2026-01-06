<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

if ( ! class_exists( 'German_Market_Lexoffice_Invoice_API_General' ) ) {

	/**
	 * Class German_Market_Lexoffice_Invoice_API_General
	 */
	class German_Market_Lexoffice_Invoice_API_General {

		/**
		 * Try to get a nice error message
		 * 
		 * @param Array $error_response
		 * @return String
		 */
		public static function error_array_to_string( $error_response, $lexoffice_data = '' ) {

			$skip = array(
				'timestamp', 'status', 'requestId'
			);

			$message = '';
			foreach ( $error_response as $key => $value ) {
				if ( ! in_array( $key, $skip ) ) {

					$point = ' ';
					
					if ( 'message' === $key ) {
						$point .= ' ';
					}

					if ( is_array( $value ) ) {
						
						if ( 'details' === $key ) {
							$text = '';
							foreach ( $value as $detail_array ) {
								$text .= self::error_array_to_string( $detail_array );
							}

						} else {
							$text = self::error_array_to_string( $value );
						}

						$point = '';
			
					} else {
						$text = $value;	
					}

					if ( 'error' == $key ) {
						$message .= $text . $point;
					} else {
						$message .= ucfirst( $key ) . ': ' . $text . $point;
					}
					
				}
			}

			// nice messages
			if ( false !== strpos( $message, 'Not Acceptable Path: /v1/invoices' ) ) {
				$lexoffice_data = json_decode( $lexoffice_data, true );
				if ( isset( $lexoffice_data[ 'taxConditions' ] ) ) {
					if ( isset( $lexoffice_data[ 'taxConditions' ][ 'taxType' ] ) && 'intraCommunitySupply' === $lexoffice_data[ 'taxConditions' ][ 'taxType' ] ) {

						if ( false !== strpos( $message, 'Invalid combination of tax type intraCommunitySupply, contact id' ) ) {

							$message = __( 'The order could not be sent as a tax free intracommunity delivery. The contact has already been used for a voucher that includes taxes. Check whether a company name has been entered in the shipping or billing address in this order. Add this to the order and carry out the transfer again.', 'woocommerce-german-market' );

						} else {

							if ( false !== strpos( $message, 'A validation error occurred' ) ) {

								$message = __( 'The order could not be sent as a tax free intracommunity delivery. Please check in your Lexware Office account whether you have entered a correct value in the "USt-IdNr." field in the menu "Allgemeinen Einstellungen".', 'woocommerce-german-market' );
							}
						}
					}
				}
			} else if ( false !== strpos( $message, 'Forbidden Path: /v1/invoices' ) ) {

				$url  = admin_url() . 'admin.php?page=german-market&tab=lexoffice';
				$message = __( 'The order could not be sent to Lexware Office as an outbound document. Invoices or invoice corrections cannot be sent with your version of Lexware Office. To be able to use these functions, you need at least version M of Lexware Office.', 'woocommerce-german-market' );
			}

			return trim( $message );
		}

		/**
		* Get filename with replaced placeholders
		*
		* @access public
		* @static
		* @param String $string
		* @param Integer $order_id
		* @param WC_Order $order
		* @return String
		*/
		public static function get_filename_and_replace_placeholders( $order_id, $order = null, $frontend_or_backend = 'backend' ) {

			$used_order = null;
			if ( is_object( $order ) && method_exists( $order, 'get_meta' ) ) {
				$used_order = $order;
			} else {
				$used_order = wc_get_order( $order_id );
			}

			if ( 'shop_order_refund' === $used_order->get_type() ) {
				$filename = get_option( 'woocommerce_de_lexoffice_credit_note_api_pdf_' . $frontend_or_backend . '_filename', __( 'Invoice-Correction-{{lexwareoffice-correction-number}}', 'woocommerce-german-market' ) );
			} else {
				$filename = get_option( 'woocommerce_de_lexoffice_invoice_api_pdf_' . $frontend_or_backend . '_filename', __( 'Invoice-{{lexwareoffice-invoice-number}}', 'woocommerce-german-market' ) );
			}

			return self::replace_placeholders( $filename, $used_order->get_id(), $used_order );
		}

		/**
		* Replace placeholders of filename
		*
		* @access public
		* @static
		* @param String $string
		* @param Integer $order_id
		* @param WC_Order $order
		* @return String
		*/
		public static function replace_placeholders( $string, $order_id, $order = null ) {
			
			$used_order = null;
			if ( is_object( $order ) && method_exists( $order, 'get_meta' ) ) {
				$used_order = $order;
			} else {
				$used_order = wc_get_order( $order_id );
			}

			if ( 'shop_order_refund' === $used_order->get_type() ) {
				$search 	= array( '{{refund-id}}', '{{lexwareoffice-correction-number}}' );
				$replace 	= array( $used_order->get_id(), $used_order->get_meta( '_lexoffice_invoice_number' ) );
				$string 	= str_replace( $search, $replace, $string );

				$used_order = wc_get_order( $order->get_parent_id() );
			}		
			
			if ( is_object( $used_order ) && method_exists( $used_order, 'get_meta' ) ) {
				$search 	= array( '{{order-number}}', '{{lexwareoffice-invoice-number}}' );
				$replace 	= array( $used_order->get_order_number(), $used_order->get_meta( '_lexoffice_invoice_number' ) );
				$string 	= str_replace( $search, $replace, $string );
			}

			return $string;
		}

		/**
		 * Get possible order status for (auto)transmission options
		 * 
		 * @return Array
		 */
		public static function get_possible_order_status_for_invoices( $type = 'general' ) {

			$wc_status = wc_get_order_statuses();

			$not_allowed_status = array(
				'wc-refunded',
				'wc-failed',
				'wc-checkout-draft',
			);

			if ( 'autotransmission' === $type ) {
				$not_allowed_status[] = 'wc-cancelled';
			}

			foreach ( $not_allowed_status as $status_key ) {
				if ( isset( $wc_status[ $status_key ] ) ) {
					unset( $wc_status[ $status_key ] );
				}
			}

			$allowed_status = array();
			foreach ( $wc_status as $key => $value ) {
				$key = str_replace( 'wc-', '', $key );
				$allowed_status[ $key ] = $value;
			}

			if ( 'autotransmission' === $type ) {
				$allowed_status_for_transmission = get_option( 'woocommerce_de_lexoffice_invoice_allowed_status_for_transmission', 'completed' );

				if ( ( ! empty( $allowed_status_for_transmission ) ) && ( ! is_array( $allowed_status_for_transmission ) ) ) {
					$allowed_status_for_transmission = array( $allowed_status_for_transmission );
				}

				foreach ( $allowed_status as $key => $value ) {
					if ( ! in_array( $key, $allowed_status_for_transmission ) ) {
						unset( $allowed_status[ $key ] );
					}
				}
			}

			return apply_filters( 'german_market_lexoffice_invoice_api_possible_order_status', $allowed_status, $wc_status, $not_allowed_status );
		}

		/**
		 * Is an order allowed to be transmitted to lexoffice?
		 * 
		 * @param WC_Order $order
		 * @return Boolean
		 */
		public static function is_order_allowed_for_transmission( $order ) {

			$is_allowed = false;

			if ( is_object( $order ) && method_exists( $order, 'get_status' ) ) {
				$allowed_status = get_option( 'woocommerce_de_lexoffice_invoice_allowed_status_for_transmission', 'completed' );

				if ( ( ! empty( $allowed_status ) ) && ( ! is_array( $allowed_status ) ) ) {
					$allowed_status = array( $allowed_status );
				}

				if ( in_array( $order->get_status(), $allowed_status ) ) {
					$is_allowed = true;
				}
			}

			return apply_filters( 'german_market_lexoffice_invoice_api_is_order_allowed_for_transmission', $is_allowed, $order );
		}

		/**
		 * Get print layouts
		 * 
		 * @return Array
		 */
		public static function get_print_layouts() {

			$token_bucket = new WGM_Token_Bucket( 'print-layouts', 2 );
			$token_bucket->consume();

			$curl = curl_init();

			curl_setopt_array( $curl, 

				array(
					CURLOPT_URL => German_Market_Lexoffice_API_Auth::get_base_url() . "v1/print-layouts",
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

			return $response_array;
		}

		/**
		* Remove post meta data from WC Order
		* 
		* @param WC_Order $order
		* @return void
		*/ 
		public static function remove_lexoffice_invoice_meta_data( $order ) {
			

			if ( is_object( $order ) && method_exists( $order, 'delete_meta_data' ) ) {

				$meta_keys = array(
					'_lexoffice_woocomerce_has_transmission_invoice_api',
					'_lexoffice_woocomerce_invoice_document_id',
					'_lexoffice_invoice_number',
				);

				foreach ( $meta_keys as $meta_key ) {
					$order->delete_meta_data( $meta_key );
				}

				$order->save_meta_data();
			}
		}

		/**
		* Before a refund is transmitted to lexoffice => transmit order (if not done yet)
		* Invoice API
		* 
		* @since 3.22.1.1
		* @wp-hook woocommerce_de_lexoffice_api_before_send_refund
		* @param WC_Order $order
		* @param WC_Order_refund $refund
		* @return void
		*/
		public static function send_order_before_refund_invoice( $order, $refund ) {

			$order_lexoffice_status = $order->get_meta( '_lexoffice_woocomerce_has_transmission_invoice_api' );

			if ( empty( $order_lexoffice_status ) || apply_filters( 'woocommerce_de_lexoffice_force_transmit_order_before_refund', false, $refund, $order ) ) {
				add_filter( 'woocommerce_de_lexoffice_force_transmission_even_if_not_completed', '__return_true', 42 );
				German_Market_Lexoffice_Invoice_API::send_order( $order, false );
				remove_filter( 'woocommerce_de_lexoffice_force_transmission_even_if_not_completed', '__return_true', 42 );
			}
		}

		/**
		* Do stuff before send sth to lexoffice
		*
		* @param WC_Order $order
		* @return void
		*/
		public static function before_send( $order_or_refund ) {
			
			// don't show delviery time
			remove_filter( 'woocommerce_order_item_name', array( 'WGM_Template', 'add_delivery_time_to_product_title' ), 10, 2 );

			// product attributes in product name
			if ( get_option( 'german_market_attribute_in_product_name', 'off' ) == 'off' ) {
				//remove_filter( 'woocommerce_order_item_name', array( 'WGM_Template', 'attribute_in_product_name_order' ), 10, 3 );
			}

			// don't show short description in item meta start
			remove_action( 'woocommerce_order_item_meta_start', array( 'WGM_Template', 'woocommerce_order_item_meta_start_short_desc' ), 10, 4 );

			// dont's show product image in product name
			if ( 'on' === get_option( 'german_market_product_images_in_order', 'off' ) ) {
				remove_filter( 'woocommerce_order_item_name', array( 'WGM_Product', 'add_thumbnail_to_order' ), 100, 3 );
			}

			if ( 'shop_order_refund' === $order_or_refund->get_type() ) {
				$order = wc_get_order( $order_or_refund->get_parent_id() );
				do_action( 'woocommerce_de_lexoffice_api_before_send_refund', $order, $order_or_refund );
			} else {
				do_action( 'woocommerce_de_lexoffice_api_before_send', $order_or_refund );
			}
		}

		/**
		* Do stuff after send sth to lexoffice
		*
		* @param WC_Order $order
		* @return void
		*/
		public static function after_send( $order_or_refund ) {
			
			// don't show delviery time
			add_filter( 'woocommerce_order_item_name', array( 'WGM_Template', 'add_delivery_time_to_product_title' ), 10, 2 );
			
			// product attributes in product name
			if ( get_option( 'german_market_attribute_in_product_name', 'off' ) == 'off' ) {
				//add_filter( 'woocommerce_order_item_name', array( 'WGM_Template', 'attribute_in_product_name_order' ), 10, 3 );
			}

			// don't show short description in item meta start
			add_action( 'woocommerce_order_item_meta_start', array( 'WGM_Template', 'woocommerce_order_item_meta_start_short_desc' ), 10, 4 );

			// dont's show product image in product name
			if ( 'on' === get_option( 'german_market_product_images_in_order', 'off' ) ) {
				add_filter( 'woocommerce_order_item_name', array( 'WGM_Product', 'add_thumbnail_to_order' ), 100, 3 );
			}

			if ( 'shop_order_refund' === $order_or_refund->get_type() ) {
				$order = wc_get_order( $order_or_refund->get_parent_id() );
				do_action( 'woocommerce_de_lexoffice_api_after_send_refund', $order, $order_or_refund );
			} else {
				do_action( 'woocommerce_de_lexoffice_api_after_send', $order_or_refund );
			}
			
		}

		/**
		* Retrieve an invoice / credit-note to check if it exists
		* 
		* @param Integer $id
		* @param String $kind_of_return_value 'bool' | 'invoice'
		* @param String $endpoint 'invoices' | 'credit-notes'
		* @return Boolean
		*/ 
		public static function retrieve_invoice_api_document( $id, $kind_of_return_value = 'bool', $endpoint = 'invoices' ) {

			$allowed_endpoints = array( 'invoices', 'credit-notes' );

			if ( ! in_array( $endpoint, $allowed_endpoints ) ) {
				return array();
			}

			$token_bucket = new WGM_Token_Bucket( 'lexoffice-' . $endpoint, 2 );
			$token_bucket->consume();

			// Call API Action
			$curl = curl_init();

			curl_setopt_array( $curl, 

				array(
					CURLOPT_URL => German_Market_Lexoffice_API_Auth::get_base_url() . "v1/" . $endpoint . "/" . $id,
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

			$return_value = isset( $response_array[ 'id' ] );

			if ( 'invoice' === $kind_of_return_value ) {
				$return_value = $response_array;
			}

			return $return_value;
		}

		/**
		* Render invoice document and get its documentFileId
		* 
		* @param Integer $invoice_id
		* @param WC_Order $order
		* @return Boolean | Integer
		*/ 
		public static function render_document_and_get_document_id( $invoice_id, $order, $endpoint = 'invoices' ) {

			$allowed_endpoints = array( 'invoices', 'credit-notes' );

			if ( ! in_array( $endpoint, $allowed_endpoints ) ) {
				return false;
			}

			$token_bucket = new WGM_Token_Bucket( 'lexoffice-invoices', 2 );
			$token_bucket->consume();

			// init
			$document_id = false;

			// Call API Action
			$curl = curl_init();

			curl_setopt_array( $curl, 

				array(
					CURLOPT_URL => German_Market_Lexoffice_API_Auth::get_base_url() . "v1/" . $endpoint . "/" . $invoice_id . '/document',
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

			if ( isset( $response_array[ 'documentFileId' ] ) ) {
				$document_id = $response_array[ 'documentFileId' ];
				$order->update_meta_data( '_lexoffice_woocomerce_invoice_document_id', $document_id );
			} else if ( isset( $response_array[ 'status' ] ) && 406 === $response_array[ 'status' ] ) {
				$document_id = 'DRAFT';
				$order->delete_meta_data( '_lexoffice_woocomerce_invoice_document_id' );
			}

			$order->save_meta_data();

			return $document_id;
		}

		/**
		* Download invoice pdf from lexoffice
		* 
		* @param Integer $document_id
		* @return String
		*/ 
		public static function download_pdf_document( $document_id ) {

			$token_bucket = new WGM_Token_Bucket( 'lexoffice-files', 2 );
			$token_bucket->consume();

			// Call API Action
			$curl = curl_init();

			curl_setopt_array( $curl, 

				array(
					CURLOPT_URL => German_Market_Lexoffice_API_Auth::get_base_url() . "v1/files/" . $document_id,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => "",
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 30,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => "GET",
					CURLOPT_HTTPHEADER => array(
						"accept: application/pdf",
						"authorization: Bearer " . German_Market_Lexoffice_API_Auth::get_bearer(),
						"cache-control: no-cache",
						"content-type: application/json",
					),
				)

			);

			$response = curl_exec( $curl );
			
			return $response;
		}

		/**
		* Check pdf document
		* 
		* @param String $pdf_binary
		* @return String
		*/
		public static function validate_binary_pdf_cocument( $pdf_binary ) {

			$return_message = 'success';

			if ( '{"message":"Unauthorized"}' === $pdf_binary ) {
				$return_message = '01';
			}

			return $return_message;
		}
	}
}
