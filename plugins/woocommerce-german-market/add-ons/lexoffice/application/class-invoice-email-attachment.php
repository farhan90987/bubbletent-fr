<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

class German_Market_Lexoffice_Invoice_Email_Attachment {

	public static function init() {

		if ( ! defined( 'GERMAN_MARKET_LEXOFFICE_CACHE' ) ) {
			define( 'GERMAN_MARKET_LEXOFFICE_CACHE', untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'german-market-lexoffice-invoices' . DIRECTORY_SEPARATOR );
		}

		if ( ! is_dir( GERMAN_MARKET_LEXOFFICE_CACHE ) ) {
			wp_mkdir_p( GERMAN_MARKET_LEXOFFICE_CACHE );
		}

		self::clear_cache();
	}


	/**
	* we cannot delete our pdf immediately because the generation of pdf and sending it via mail don't happen
	* simultaneously because we are just hooked into the mail sending process
	*
	* @access public
	* @return void
	*/
	public static function clear_cache() {

		$cache_dir 		= GERMAN_MARKET_LEXOFFICE_CACHE;
		if ( ! is_dir( $cache_dir ) ) {
			return;
		}
		$cache_dir_tree	= scandir( $cache_dir );
		foreach ( $cache_dir_tree as $dir ) {			
			$cache_dir	= explode( "-", $dir, 1 );
			$timestamp	= intval( $cache_dir[ 0 ] );
			if ( $timestamp > 0 ) {
				if ( ( time() - $timestamp ) < apply_filters( 'wp_wc_invoice_pdf_clear_cache_time', 10 ) ) {
					continue;
				}
				$clear_dir	= GERMAN_MARKET_LEXOFFICE_CACHE . $dir . DIRECTORY_SEPARATOR;
				$files = array_diff( scandir( $clear_dir ), array( '.', '..' ) );
				foreach ( $files as $file ) {
					unlink( $clear_dir . $file );
				}
				rmdir( $clear_dir );
			}
		}
	}

	/**
	* Ttrigger refunded order to save refund id as temporally post meta in order
	*
	* @hook woocommerce_order_fully_refunded_notification
	* @hook woocommerce_order_partially_refunded_notification
	* @param int $order_id
 	* @param int $refund_id
	* @return void
	*/
	public static function trigger_refund( $order_id, $refund_id ) {
		$order = wc_get_order( $order_id );
		$order->update_meta_data( '_lexoffice_email_attachment_refund_id', $refund_id );
		$order->save_meta_data();
	}

	/**
	 * Add credit note pdf to refund email
	 * 
	 * @param Array $attachments
	 * @param Integer $refund_id
	 * @param WC_Order $order
	 * @return Array
	 */
	public static function add_refund_attachment( $attachments, $refund_id, $order ) {

		if ( 'on' === get_option( 'woocommerce_de_lexoffice_credit_note_email_attachment', 'off' ) ) {

			$refund = wc_get_order( $refund_id );
			if ( is_object( $refund ) && method_exists( $refund, 'get_meta' ) ) {

				// get document
				$document_id 		= $refund->get_meta( '_lexoffice_woocomerce_invoice_document_id' );
				$send_to_lexoffice 	= false;

				if ( empty( $document_id ) ) {

					// there is no document id -> create one
					$success 			= German_Market_Lexoffice_Invoice_API_Credit_Note::send_refund( $refund, false );
					$send_to_lexoffice 	= true;
					$document_id 		= $refund->get_meta( '_lexoffice_woocomerce_invoice_document_id', true );
					
					// document was not created
					if ( empty( $document_id ) ) {
						return $attachments;
					}
				}

				$pdf_binary = German_Market_Lexoffice_Invoice_API_General::download_pdf_document( $document_id );

				if ( empty( $pdf_binary ) && ( ! $send_to_lexoffice ) ) {
					
					$success 		= German_Market_Lexoffice_Invoice_API_Credit_Note::send_refund( $refund, false );
					$document_id 	= $refund->get_meta( '_lexoffice_woocomerce_invoice_document_id', true );
					
					// document was not created
					if ( empty( $document_id ) ) {
						return $attachments;
					}

					$pdf_binary = German_Market_Lexoffice_Invoice_API_General::download_pdf_document( $document_id );
				}

				$validate = German_Market_Lexoffice_Invoice_API_General::validate_binary_pdf_cocument( $pdf_binary );

				if ( 'success' === $validate ) {
					if ( ! empty( $pdf_binary ) ) {

						$filename = German_Market_Lexoffice_Invoice_API_General::get_filename_and_replace_placeholders( $refund_id, $refund, 'frontend' );
					
						$file_extension = '.pdf';
						$directory_name	= time() . "_" . md5( 'lexoffice-invoice' . rand( 0, 99999 ) );
						
						wp_mkdir_p( GERMAN_MARKET_LEXOFFICE_CACHE . $directory_name );

						$file = GERMAN_MARKET_LEXOFFICE_CACHE . $directory_name . DIRECTORY_SEPARATOR . $filename . $file_extension;

						file_put_contents( $file, $pdf_binary );

						$attachments[] = $file; // don't use a key, it will be used as filename
					}
				}
			}
		}

		$order->delete_meta_data( '_lexoffice_email_attachment_refund_id' );
		$order->save_meta_data();

		return $attachments;
	}

	/**
	* Adds the pdf as an attachement to chosen e-mails
	*
	* @access public
	* @static
	* @hook woocommerce_email_attachments
	* @param Array $attachments
	* @param String $status
	* @param WC_Order $order
	* @return Array
	*/
	public static function add_attachment( $attachments, $status, $order ) {
		
		// clear cache
		self::init();

		if ( ! ( is_object( $order ) && method_exists( $order, 'get_meta' ) ) ) {
			return $attachments;
		}

		$refund_id = $order->get_meta( '_lexoffice_email_attachment_refund_id' );
		if ( ! empty( $refund_id ) ) {
			return self::add_refund_attachment( $attachments, $refund_id, $order );
		}

		// check if we need to add the invoice
		$selected_mails = get_option( 'woocommerce_de_lexoffice_invoice_email_attachment', array() );
		if ( ! in_array( $status, $selected_mails ) ) {
			return $attachments;
		}

		// get document
		$document_id 		= $order->get_meta( '_lexoffice_woocomerce_invoice_document_id' );
		$send_to_lexoffice 	= false;

		if ( empty( $document_id ) ) {

			// there is no document id -> create one
			$success 			= German_Market_Lexoffice_Invoice_API::send_order( $order, false );
			$send_to_lexoffice 	= true;
			$document_id 		= $order->get_meta( '_lexoffice_woocomerce_invoice_document_id', true );
			
			// document was not created
			if ( empty( $document_id ) ) {
				return $attachments;
			}
		}

		$pdf_binary = German_Market_Lexoffice_Invoice_API_General::download_pdf_document( $document_id );

		if ( empty( $pdf_binary ) && ( ! $send_to_lexoffice ) ) {
			
			$success 		= German_Market_Lexoffice_Invoice_API::send_order( $order, false );
			$document_id 	= $order->get_meta( '_lexoffice_woocomerce_invoice_document_id', true );
			
			// document was not created
			if ( empty( $document_id ) ) {
				return $attachments;
			}

			$pdf_binary = German_Market_Lexoffice_Invoice_API_General::download_pdf_document( $document_id );
		}

		$validate = German_Market_Lexoffice_Invoice_API_General::validate_binary_pdf_cocument( $pdf_binary );

		if ( 'success' === $validate ) {
			if ( ! empty( $pdf_binary ) ) {

				$filename = German_Market_Lexoffice_Invoice_API_General::get_filename_and_replace_placeholders( $order->get_id(), $order, 'frontend' );
			
				$file_extension = '.pdf';
				$directory_name	= time() . "_" . md5( 'lexoffice-invoice' . rand( 0, 99999 ) );
				
				wp_mkdir_p( GERMAN_MARKET_LEXOFFICE_CACHE . $directory_name );

				$file = GERMAN_MARKET_LEXOFFICE_CACHE . $directory_name . DIRECTORY_SEPARATOR . $filename . $file_extension;

				file_put_contents( $file, $pdf_binary );

				$attachments[] = $file; // don't use a key, it will be used as filename

			}
		}
		
		return $attachments;
	}
}
