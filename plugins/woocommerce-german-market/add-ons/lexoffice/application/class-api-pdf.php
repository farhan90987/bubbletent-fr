<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class German_Market_Lexoffice_API_PDF
 *
 * @author MarketPress
 */
class German_Market_Lexoffice_API_PDF {

	/**
	* API - send invoice pdf
	*
	* @param WC_ORDER $order
	* @return String json response
	*/
	public static function upload_invoice_pdf( $voucher_id, $order, $is_refund = false, $show_errors = true ) {

		if ( ! class_exists( 'WP_WC_Invoice_Pdf_Create_Pdf' ) ) {
			if ( $show_errors ) {
				echo __( '<b>ERROR:</b> Modul Invoice PDF of WooCommerce German Market is not enabled.', 'woocommerce-german-market' );
				exit();
			} else {
				return;
			}
		}

		if ( $is_refund ) {

			$refund 	= $order;
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

		} else {

			WGM_Compatibilities::wpml_invoice_pdf_switch_lang_for_online_booking( array( 'order' => $order, 'admin' => 'true' ) );

			$args = array(
				'order'				=> $order,
				'output_format'		=> 'pdf',
				'output'			=> 'cache',
				'filename'			=> str_replace( '/', '-', apply_filters( 'wp_wc_invoice_pdf_frontend_filename', get_option( 'wp_wc_invoice_pdf_file_name_frontend', get_bloginfo( 'name' ) . '-' . __( 'Invoice-{{order-number}}', 'woocommerce-invoice-pdf' ) ), $order ) ),
				'admin'				=> 'true',
			);

		}


		$invoice 	= new WP_WC_Invoice_Pdf_Create_Pdf( $args );
	  	$attachment = WP_WC_INVOICE_PDF_CACHE_DIR . $invoice->cache_dir . DIRECTORY_SEPARATOR . $invoice->filename;

	  	WGM_Compatibilities::wpml_invoice_pdf_reswitch_lang_for_online_booking();

	  	if ( $is_refund ) {
	  		remove_filter( 'wp_wc_invoice_pdf_template_invoice_content', array( 'WP_WC_Invoice_Pdf_Backend_Download', 'load_storno_template' ) );
	  	}

	  	///////////////////////////////////
		// 1st step: upload post
		///////////////////////////////////

	  	// create CURLFile
		$cfile = new CURLFile( $attachment );

		$post = array (
		    'file' => $cfile,
		    'type' => 'voucher'
		);

		$token_bucket = new WGM_Token_Bucket( 'lexoffice-vouchers', 2 );
		$token_bucket->consume();

		$curl = curl_init();

		curl_setopt_array( $curl, array(
		  CURLOPT_URL => German_Market_Lexoffice_API_Auth::get_base_url() . "v1/vouchers/" . $voucher_id . "/files",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => $post,
		  CURLOPT_HTTPHEADER => array(
		    "accept: application/json",
		    "authorization: Bearer " . lexoffice_woocomerce_api_get_bearer(),
		    "cache-control: no-cache",
		  ),
		) );


		$response_post = curl_exec( $curl );
		curl_close( $curl );

		// evaluate response
		$response_array = json_decode( $response_post, true );
		if ( ! isset( $response_array[ 'id' ] ) ) {
			if ( $show_errors ) {
				echo '<b>' . __( 'ERROR', 'woocommerce-german-market' ) . ':</b> ' . lexoffice_woocomerce_get_error_text( $response_post );
				exit();
			} else {
				return;
			}

		}

		return $response_post;
	}
}
