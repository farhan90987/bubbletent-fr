<?php

namespace MarketPress\German_Market\E_Invoice;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

class Backend_Download {

	/**
	 * Backend download in oder
	 * 
	 * @wp-hook wp_wc_invoice_pdf_after_invoice_download_button_order
	 * @param Integer $order_id
	 * @return void 
	 */
	public static function order_download( $order_id ) {

		$order = wc_get_order( $order_id );
		$new_status = $order->get_status( 'edit' );

		$is_new = isset( $_GET[ 'action' ] ) ? 'new' === strval( $_GET[ 'action' ] ) : false;

		if ( ! $is_new ) {
			if ( 'auto-draft' === $new_status ) {
				$is_new = true;
			}
		}

		if ( apply_filters( 'german_market_backend_show_pdf_download_button', true, 'invoice', $order_id ) ) {
			if ( ! $is_new  ) {
				echo '<li class="wide"><p><a class="button-primary wp-wc-invoice-pdf" href="' . wp_nonce_url( admin_url( 'admin-ajax.php?action=german_market_e_invoice&order_id=' . $order_id ), 'german-market-e-invoice' ) . '">' . __( 'Download E-Invoice XML', 'woocommerce-german-market' ) . '</a></p></li>';
			}
		}
	}

	/**
	 * Backend download in oder menu
	 * 
	 * @wp-hook wp_wc_invoice_pdf_order_admin_icon_download
	 * @param Array $actions
	 * @param WC_Order $order
	 * @return Array 
	 */
	public static function admin_icon_download( $actions, $order ) {

		if ( apply_filters( 'wp_wc_invoice_pdf_backend_download_admin_icon_download_return', false, $order ) ) {
			return $actions;
		}

		$always_create_new = false;
		$always_create_new_pdf_status = apply_filters( 'wp_wc_invoice_pdf_always_create_new_pdf_status', array( 'pending', 'processing', 'on-hold' ) );
		if ( in_array( $order->get_status(), $always_create_new_pdf_status ) ) {
			$always_create_new = true;
		}

		$e_invoice = array( 
			'url' 		=>	wp_nonce_url( admin_url( 'admin-ajax.php?action=german_market_e_invoice&order_id=' . $order->get_id() ), 'german-market-e-invoice' ), 
			'name' 		=> __( 'Download E-Invoice XML', 'woocommerce-german-market' ),
			'action' 	=> 'e_invoice_xml' . ( $always_create_new ? ' always_create_new' : '' ),
		);

		$actions[ 'e_invoice' ] = $e_invoice;

		return $actions;
	}

	/**
	 * Ajax for downloading XML (order or refund)
	 * 
	 * @wp-hook wp_ajax_german_market_e_invoice
	 * @return void
	 */
	public static function admin_ajax_download_xml() {

		if ( ! check_ajax_referer( 'german-market-e-invoice', 'security', false ) ) {
			wp_die( __( 'You have taken too long. Please go back and retry.', 'woocommerce-german-market' ), '', array( 'response' => 403 ) );
		}

		$order_id = intval( $_REQUEST[ 'order_id' ] );
		$order = wc_get_order( $order_id );
		$e_invoice = new E_Invoice_Order( $order );
		$e_invoice->download_xml_file();
	}

	/**
	 * Backend download in refund menu
	 * 
	 * @wp-hook wp_wc_invoice_pdf_refund_admin_icon_download
	 * @param Array $actions
	 * @param WC_Order $order
	 * @return Array 
	 */
	public static function admin_refund_icon_download( $actions, $refund ) {

		$e_invoice = array( 
			'url' 		=>	wp_nonce_url( admin_url( 'admin-ajax.php?action=german_market_e_invoice&order_id=' . $refund->get_id() ), 'german-market-e-invoice' ), 
			'name' 		=> __( 'Download E-Invoice XML', 'woocommerce-german-market' ),
			'action' 	=> 'e_invoice_xml',
			'data'	=> array(
				'refund-id' => $refund->get_id(),
				'order-id'	=> $refund->get_parent_id(),
			)
		);

		$actions[ 'e_invoice' ] = $e_invoice;

		return $actions;
	}

	/**
	 * Delete saved content after button was pressed
	 * 
	 * @wp-hook wp_wc_invoice_pdf_after_delete_saved_content
	 * @wp-hook wp_wc_invoice_pdf_after_delete_refund_saved_content
	 * @param WC_Order
	 * @return void
	 */
	public static function delete_saved_content( $order ) {
		$meta_data = new E_Invoice_Meta_Data( $order );
		$meta_data->delete_meta();
	}

	/**
	* Add bulk action
	*
	* @hook WGM_Hpos::get_hook_for_order_bulk_actions()
	* @param Array $actions
	* @return Array
	*/
	public static function add_bulk_actions( $actions ) {
		$actions[ 'gm_download_e_invoice_xml' ] = __( 'Downloads e-invoice XML files', 'woocommerce-german-market' );
		return $actions;
	}

	/**
	* Do bulk action download zip with XML files
	*
	* @hook WGM_Hpos::get_hook_for_order_handle_bulk_actions()
	* @param String $redirect_to
	* @param String $actions
	* @param Array $order_ids
	* @return String
	*/
	public static function bulk_action( $redirect_to, $action, $order_ids ) {

		\WP_WC_Invoice_Pdf_Backend_Download::clear_zip_cache();
		\WP_WC_Invoice_Pdf_Create_Pdf::clear_cache();

		if ( empty( $order_ids ) ) {
			return $redirect_to;
		}

		if ( 'gm_download_e_invoice_xml' !== $action ) {
			return $redirect_to;
		}

		do_action( 'german_market_before_bulk_for_e_invoice_xml' );

		$created_one_pdf = false;
		$files = array();

		foreach ( $order_ids as $order_id ) {

			// create xml
			$order = wc_get_order( $order_id );

			if ( apply_filters( 'wp_wc_invoice_pdf_backend_download_order_not_in_bulk_zip', false, $order ) ) {
				continue;
			}

			do_action( 'wp_wc_invoice_pdf_before_backend_download_switch', array( 'order' => $order, 'admin' => true ) );

			$e_invoice = new E_Invoice_Order( $order );
			$xml_files[] = $e_invoice->save_xml_temp_and_get_path();
			$created_one_pdf = true;

		}

		// create zip file
		if ( $created_one_pdf ) {
			$zip_dir  = untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'woocommerce-invoice-pdf-zip' . DIRECTORY_SEPARATOR;
			wp_mkdir_p( $zip_dir );
			$zip_file = $zip_dir . time() . "_" . rand( 1, 999999 ) . '_' . md5( rand( 1, 999999 ) . 'wp_wc_invoice_pdf' ) . md5( 'woocommerce-invoice-pdf' . rand( 0, 999999 ) ) . '.zip';
			$files = array_diff( scandir( $zip_dir ), array( '.', '..' ) );

			if ( class_exists( 'ZipArchive' ) ) {

				$zip = new \ZipArchive();
				if ( $zip->open( $zip_file, \ZipArchive::CREATE ) ) {
					foreach ( $xml_files as $file ) {
						$zip->addFile( $file, basename( $file ) );
					}
					$zip->close();
				}

			} else {

				// use PclZip of WordPress
				$pclizip_class = ABSPATH . 'wp-admin/includes/class-pclzip.php';
				if ( file_exists( $pclizip_class ) ) {
					require_once $pclizip_class;
					$zip = new \PclZip( $zip_file );
					foreach ( $xml_files as $file ) {
						$zip->add( $file, PCLZIP_OPT_REMOVE_ALL_PATH );
					}
				}
			}
			
			// clear pdf cache
			\WP_WC_Invoice_Pdf_Backend_Download::clear_zip_cache( true );
			\WP_WC_Invoice_Pdf_Create_Pdf::clear_cache();

			// download zip file
			header( 'Content-Type: application/zip');
			header( 'Content-disposition: attachment; filename=' . apply_filters( 'wp_wc_invoice_xml_e_invoices_zip_filename', date( 'Y-m-d-H-i' ) . '-' . __( 'xml-e-invoices', 'woocommerce-german-market' ) . '.zip' ) );
			header( 'Content-Length: ' . filesize( $zip_file ) );
			readfile( $zip_file );

			exit();
		}

		return $redirect_to;
	}

	/**
	* Submit button for bulk download refunds
	*
	* @since 3.1
	* @access public
	* @static 
	* @hook woocommerc_de_refund_after_list, woocommerc_de_refund_before_list
	* @return void
	*/
	public static function refund_button() {

		\WP_WC_Invoice_Pdf_Backend_Download::clear_zip_cache( true );
		\WP_WC_Invoice_Pdf_Create_Pdf::clear_cache();
		
		?><input class="button-primary" type="submit" name="download-refund-e-invoice-xml" value="<?php echo esc_attr( __( 'Downloads e-invoice XML files', 'woocommerce-german-market' ) ); ?>"/><?php
	}

	/**
	* Bulk download for refunds
	*
	* @since 3.1
	* @access public
	* @static 
	* @hook admin_init
	* @return void
	*/
	public static function bulk_action_refunds() {
		
		if ( isset( $_REQUEST[ 'download-refund-e-invoice-xml' ] ) ) {
			
			// clear cache
			\WP_WC_Invoice_Pdf_Backend_Download::clear_zip_cache( true );
			\WP_WC_Invoice_Pdf_Create_Pdf::clear_cache();

			// check nonce
			if ( ! isset( $_REQUEST[ 'wgm_refund_list_nonce' ] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( $_POST[ 'wgm_refund_list_nonce' ], 'wgm_refund_list' ) ) {
				?><div id="message" class="error notice" style="display: block;"><p><?php echo __( 'Sorry, something went wrong while downloading your refunds. Please, try again.', 'woocommerce-german-market' ); ?></p></div><?php
				return;
			} 

			// init refunds
			if ( ! isset( $_REQUEST[ 'refunds' ] ) ) {
				return;
			}

			$refunds = $_REQUEST[ 'refunds' ];

			// return if no order is checked
			if ( empty( $refunds ) ) {
				return;
			}

			foreach ( $refunds as $refund_id ) {

				// refund
				$refund 	= wc_get_order( $refund_id );
				$order_id 	= $refund->get_parent_id();

				if ( ! ( $order_id > 0 ) ) {
					continue;
				}

				$order = wc_get_order( $order_id );

				if ( apply_filters( 'wp_wc_invoice_pdf_backend_download_refund_not_in_bulk_zip', false, $refund, $order ) ) {
					continue;
				}

				do_action( 'german_market_before_bulk_for_pdfs' );
				do_action( 'wp_wc_invoice_pdf_before_backend_download_switch', array( 'order' => $order, 'admin' => true ) );	

				$e_invoice = new E_Invoice_Order( $refund );
				$xml_files[] = $e_invoice->save_xml_temp_and_get_path();
				$created_one_pdf = true;

			}
			
			// create zip file
			$zip_dir = untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'woocommerce-invoice-pdf-zip' . DIRECTORY_SEPARATOR;
			wp_mkdir_p( $zip_dir );
			$zip_file = $zip_dir . time() . "_" . rand( 1, 999999 ) . '_' . md5( rand( 1, 999999 ) . 'wp_wc_invoice_pdf' ) . md5( 'woocommerce-invoice-pdf' . rand( 0, 999999 ) ) . '.zip';
			$files = array_diff( scandir( $zip_dir ), array( '.', '..' ) );

			if ( class_exists( 'ZipArchive' ) ) {
				
				$zip = new \ZipArchive();
				
				if ( $zip->open( $zip_file, \ZipArchive::CREATE ) ) {
			 
					foreach ( $xml_files as $file ) {
						$zip->addFile( $file, basename( $file ) );
					}

					$zip->close();
				}

			} else {

				// use PclZip of WordPress
				$pclizip_class = ABSPATH . 'wp-admin/includes/class-pclzip.php';
				if ( file_exists( $pclizip_class ) ) {
					require_once $pclizip_class;
					$zip = new \PclZip( $zip_file );

					foreach ( $xml_files as $file ) {
						$zip->add( $file, PCLZIP_OPT_REMOVE_ALL_PATH );
					}
				}
			}
			
			// clear pdf cache
			\WP_WC_Invoice_Pdf_Backend_Download::clear_zip_cache( true );
			\WP_WC_Invoice_Pdf_Create_Pdf::clear_cache();

			// download zip file
			header( 'Content-Type: application/zip');
			header( 'Content-disposition: attachment; filename=' . apply_filters( 'wp_wc_invoice_pdf_zip_filename', date( 'Y-m-d-H-i' ) . '-' . __( 'xml-e-invoices-refunds', 'woocommerce-german-market' )  . '.zip' ) );
			header( 'Content-Length: ' . filesize( $zip_file ) );
			readfile( $zip_file );

			exit();
		}
	}
}
