<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class German_Market_SevDesk_API_PDF
 *
 * @author MarketPress
 */
class German_Market_SevDesk_API_PDF {

	/**
	* build temp file of invoice pdf
	*
	* @param Array $args
	* @return String
	*/
	public static function build_temp_file( $args, $show_errors = true ) {

		$attachment = $args[ 'invoice_pdf' ];

		$cfile = new CURLFile( $attachment  );

		$post = array (
		    'file' => $cfile,
		);

		$curl = curl_init();

		curl_setopt_array( $curl, array(
		  CURLOPT_URL => $args[ 'base_url' ] . 'Voucher/Factory/uploadTempFile',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => $post,
		  CURLOPT_HTTPHEADER => array(
		    'accept: application/json',
		    'authorization: ' . $args[ 'api_token' ],
		    'cache-control: no-cache',
		    'content-type: multipart/form-data;',
		  ),
		  CURLOPT_USERAGENT => sevdesk_woocommerce_get_user_agent(),
		) );
	                                                                                                                                                                                                             
		$response = curl_exec( $curl );
		$error = curl_error( $curl );
		curl_close ( $curl );

		$response_array = json_decode( $response, true );

		// error handling
		if ( ! isset( $response_array[ 'objects' ][ 'filename' ] ) ) {

			if ( $error != '' ) {
				echo sevdesk_woocommerce_api_get_error_message( $error );
			} else {

				if ( isset( $response_array[ 'message' ] ) && ( 'Authentication required' === $response_array[ 'message' ] ) ) {
					$error_message = __( 'Authentication required. Please check the validity of the API token in the settings of the sevdesk add-on and check the validity of your sevdesk account.', 'woocommerce-german-market' );
				} else {
					$error_message = __( 'Failed to upload invoice pdf.', 'woocommerce-german-market' );
				}

				if ( $show_errors ) {
					echo sevdesk_woocommerce_api_get_error_message( $error_message );
					exit();
				} else {
					error_log( 'German Market sevdesk Add-On: ' . $error_message );
					return '';
				}
			}
		}

		return $response_array[ 'objects' ][ 'filename' ];
	}

	/**
	* Get invoice pdf, path to file
	* @param WC_Order $order
	* @return String
	*/
	public static function get_invoice_pdf( $order ) {

		if ( ! class_exists( 'WP_WC_Invoice_Pdf_Create_Pdf' ) ) {
			echo sevdesk_woocommerce_api_get_error_message( __( 'Modul Invoice PDF of WooCommerce German Market is not enabled.', 'woocommerce-german-market' ) );
			exit();
		}

		WGM_Compatibilities::wpml_invoice_pdf_switch_lang_for_online_booking( array( 'order' => $order, 'admin' => 'true' ) );

		$args = array( 
				'order'				=> $order,
				'output_format'		=> 'pdf',
				'output'			=> 'cache',
				'filename'			=> str_replace( '/', '-', apply_filters( 'wp_wc_invoice_pdf_frontend_filename', get_option( 'wp_wc_invoice_pdf_file_name_frontend', get_bloginfo( 'name' ) . '-' . __( 'Invoice-{{order-number}}', 'woocommerce-invoice-pdf' ) ), $order ) ),
				'admin'				=> 'true',
			);
			
		$invoice 	= new WP_WC_Invoice_Pdf_Create_Pdf( $args );
	  	$attachment = WP_WC_INVOICE_PDF_CACHE_DIR . $invoice->cache_dir . DIRECTORY_SEPARATOR . $invoice->filename;

	  	WGM_Compatibilities::wpml_invoice_pdf_reswitch_lang_for_online_booking();

	  	return $attachment;
	}

	/**
	* Get refund pdf, path to file
	* @param WC_Order $refund
	* @return String
	*/
	public static function get_refund_pdf( $refund ) {

		if ( ! class_exists( 'WP_WC_Invoice_Pdf_Create_Pdf' ) ) {
			echo sevdesk_woocommerce_api_get_error_message( __( 'Modul Invoice PDF of WooCommerce German Market is not enabled.', 'woocommerce-german-market' ) );
			exit();
		}

		// init
		$refund_id 	= $refund->get_id();
		$order_id 	= $refund->get_parent_id();
		$order 		= wc_get_order( $order_id );

		WGM_Compatibilities::wpml_invoice_pdf_switch_lang_for_online_booking( array( 'order' => $order, 'admin' => 'true' ) );

		do_action( 'wp_wc_invoice_pdf_before_refund_backend_download', $refund_id );

		add_filter( 'wp_wc_invoice_pdf_template_invoice_content', array( 'WP_WC_Invoice_Pdf_Backend_Download', 'load_storno_template' ) );

		// get filename
		$filename = get_option( 'wp_wc_invoice_pdf_refund_file_name_backend', 'Refund-{{refund-id}} for order {{order-number}}' );
		// replace {{refund-id}}, the other placeholders will be managed by the class WP_WC_Invoice_Pdf_Create_Pdf
		$filename = str_replace( '{{refund-id}}', $refund_id, $filename );
		$filename = apply_filters( 'wp_wc_invoice_pdf_refund_backend_filename', $filename, $refund );

		$args = array( 
					'order'				=> $order,
					'refund'			=> $refund,
					'output_format'		=> 'pdf',
					'output'			=> 'cache',
					'filename'			=> str_replace( '/', '-', $filename ),
					'admin'				=> 'true',
				);
		
		$refund = new WP_WC_Invoice_Pdf_Create_Pdf( $args );
		$attachment = WP_WC_INVOICE_PDF_CACHE_DIR . $refund->cache_dir . DIRECTORY_SEPARATOR . $refund->filename;

		remove_filter( 'wp_wc_invoice_pdf_template_invoice_content', array( 'WP_WC_Invoice_Pdf_Backend_Download', 'load_storno_template' ) );

	  	WGM_Compatibilities::wpml_invoice_pdf_reswitch_lang_for_online_booking();

		return $attachment;
	}

}
