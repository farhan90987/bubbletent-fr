<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

/**
 * Class German_Market_Lexoffice_Ajax_Handler
 */
class German_Market_Lexoffice_Ajax_Handler {
	
	/**
	 * Singleton instance
	 * 
	 * @var German_Market_Lexoffice_Ajax_Handler
	 */
	private static $instance = null;

	/**
	 * Voucher or invoice api
	 * 
	 * @var String
	 */
	public static $api_type = null; 

	/**
	* Singleton get_instance
	*
	* @static
	* @return German_Market_Lexoffice_Ajax_Handler
	*/
	public static function get_instance() {
		if ( self::$instance == NULL) {
			self::$instance = new German_Market_Lexoffice_Ajax_Handler();	
		}
		return self::$instance;
	}

	/**
	* Singletone constructor
	*
	* @access private
	*/
	private function __construct() {

		self::$api_type = get_option( 'woocommerce_de_lexoffice_voucher_or_invoice', 'voucher' );

		add_action( 'wp_ajax_lexoffice_woocommerce_edit_shop_order', 			array( __CLASS__, 'lexoffice_woocommerce_edit_shop_order_ajax' ) );
		add_action( 'wp_ajax_lexoffice_woocommerce_edit_shop_order_refund', 	array( __CLASS__, 'lexoffice_woocommerce_edit_shop_order_ajax_refund' ) );

		if ( 'invoice' === self::$api_type ) {
			add_action( 'wp_ajax_lexoffice_woocommerce_download_invoice', 		array( __CLASS__, 'lexoffice_woocommerce_download_lexoffice_invoice_pdf' ) );
			add_action( 'wp_ajax_lexoffice_woocommerce_refresh_invoice_number', array( __CLASS__, 'lexoffice_woocommerce_refresh_invoice_number' ) );
			add_action( 'admin_notices', 										array( __CLASS__, 'admin_notices' ) );
		}
	}

	/**
	* Show errors in backend
	*
	* wp-hook admin_notices
	* @static
	* @access public
	* @return boid
	*/
	public static function admin_notices() {

		$error_message = get_transient( 'german_market_lexoffice_error' );

		if ( $error_message ) {
			echo sprintf( '<div class="error"><p><strong>%s:</strong> %s</p></div>', __( 'ERROR', 'woocommerce-german-market' ), German_Market_Lexoffice_Invoice_API::get_error_messages( $error_message ) );
			delete_transient( 'german_market_lexoffice_error' ); 
		}
	}

	/**
	* Show lexoffice invoice inumber in "lexoffice invoice number column" after sending to lexoffice in backend
	*
	* wp-hook wp_ajax_$action (wp_ajax_lexoffice_woocommerce_refresh_invoice_number)
	* @static
	* @access public
	* @return exit();
	*/
	public static function lexoffice_woocommerce_refresh_invoice_number() {

		if ( class_exists( 'German_Market_Lexoffice_Backend_Invoice_Number_Column' ) ) {
			if ( check_ajax_referer( 'lexoffice_woocommerce_edit_shop_order_script', 'security', false ) ) {

				if ( isset( $_REQUEST[ 'order_id' ] ) ) {
					$order = wc_get_order( esc_attr( $_REQUEST[ 'order_id' ] ) );
					echo wp_kses_post( German_Market_Lexoffice_Backend_Invoice_Number_Column::get_column_markup( $order ) );
				}
				
			}  else {
				echo "<b>ERROR: </b>" . __( 'Ajax nonce check failed.', 'woocommerce-german-market' );
			}
		}

		exit();
	}

	/**
	* ajax handler to download invoice pdf from lexoffice
	*
	* wp-hook wp_ajax_$action (wp_ajax_lexoffice_woocommerce_download_invoice)
	* @static
	* @access public
	* @return exit();
	*/
	public static function lexoffice_woocommerce_download_lexoffice_invoice_pdf() {

		if ( check_ajax_referer( 'lexoffice_invoice_pdf_download', 'security', false ) ) {
			
			// get document it
			$document_id = esc_attr( $_REQUEST[ 'document_id' ] );
			
			$pdf_binary = German_Market_Lexoffice_Invoice_API_General::download_pdf_document( $document_id );
			$validate 	= German_Market_Lexoffice_Invoice_API_General::validate_binary_pdf_cocument( $pdf_binary );
			
			if ( 'success' === $validate ) {
				
				if ( ! empty( $pdf_binary ) ) {

					$filename 	= get_option( 'woocommerce_de_lexoffice_invoice_api_pdf_backend_filename',  __( 'Invoice-{{lexwareoffice-invoice-number}}', 'woocommerce-german-market' ) );
					$filename 	= German_Market_Lexoffice_Invoice_API_General::get_filename_and_replace_placeholders( intval( $_REQUEST[ 'order_id' ] ), null, 'backend' );
					header('Content-Description: File Transfer');
					header('Content-Type: application/pdf');
					header('Content-Disposition: attachment; filename=' . $filename . '.pdf');
					echo $pdf_binary;
					exit();

				} else {
					set_transient( 'german_market_lexoffice_error', esc_attr( '02' ) );
					wp_safe_redirect( wp_get_referer() );
				}

			} else {
				set_transient( 'german_market_lexoffice_error', esc_attr( $validate ) );
				wp_safe_redirect( wp_get_referer() );
			}

		} else {
			echo "ERROR: " . __( 'Ajax nonce check failed.', 'woocommerce-german-market' );
		}

		exit();
	}

	/**
	* ajax handler, click on button on edit_shop-order screen
	*
	* wp-hook wp_ajax_$action (wp_ajax_lexoffice_woocommerce_edit_shop_order)
	* @static
	* @access public
	* @return exit();
	*/
	public static function lexoffice_woocommerce_edit_shop_order_ajax() {

		if ( check_ajax_referer( 'lexoffice_woocommerce_edit_shop_order_script', 'security', false ) ) {
			
			// get order
			$order_id = $_REQUEST[ 'order_id' ];
			$order = wc_get_order( $order_id );

			// api
			$response = ( 'voucher' === self::$api_type ) ? lexoffice_woocomerce_api_send_voucher( $order ) : German_Market_Lexoffice_Invoice_API::send_order( $order );

			// echo response
			echo apply_filters( 'lexoffice_woocommerce_edit_shop_order_ajax_api', $response, $order_id );
		
		} else {
			echo "ERROR:" . __( 'Ajax nonce check failed.', 'woocommerce-german-market' );
		}

		exit();
	}

	/**
	* ajax handler, click on button on page=wgm-refunds screen
	*
	* wp-hook wp_ajax_$action (wp_ajax_lexoffice_woocommerce_edit_shop_order_refund)
	* @static
	* @access public
	* @return exit();
	*/
	public static function lexoffice_woocommerce_edit_shop_order_ajax_refund() {

		if ( check_ajax_referer( 'lexoffice_woocommerce_edit_shop_order_script', 'security', false ) ) {
			
			// get refund
			$refund_id = $_REQUEST[ 'refund_id' ];
			$refund = wc_get_order( $refund_id );

			// api
			$response = ( 'voucher' === self::$api_type ) ? lexoffice_woocommerce_api_send_refund( $refund ) : German_Market_Lexoffice_Invoice_API_Credit_Note::send_refund( $refund );

			// echo response
			echo apply_filters( 'lexoffice_woocommerce_edit_shop_order_ajax_api', $response, $refund_id );
		
		} else {
			echo "<b>ERROR: </b>" . __( 'Ajax nonce check failed.', 'woocommerce-german-market' );
		}

		exit();
	}
}
