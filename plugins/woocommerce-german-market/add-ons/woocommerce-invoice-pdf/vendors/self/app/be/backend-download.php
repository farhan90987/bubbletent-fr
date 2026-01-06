<?php

use MarketPress\German_Market\E_Invoice\E_Invoice_Meta_Data;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_WC_Invoice_Pdf_Backend_Download' ) ) {
	
	/**
	* enables download buttons in backend
	*
	* @class WP_WC_Invoice_Pdf_Backend_Download
	* @version 1.0
	* @category	Class
	*/
	class WP_WC_Invoice_Pdf_Backend_Download {
		
		/**
		* adds 'download invoice pdf' to order
		*
		* @since 3.9.2
		* @access public
		* @static
		* @hook woocommerce_order_actions_end
		* @arguments $order_id
		* @return void
		*/	
		public static function order_download( $order_id ) {

			if ( 'on' === get_option( 'wp_wc_invpice_pdf_backend_download', 'on' ) ) {
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
						echo '<li class="wide"><p><a class="button-primary wp-wc-invoice-pdf" href="' . wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_wp_wc_invoice_pdf_invoice_download&order_id=' . $order_id ), 'wp-wc-invoice-pdf-download' ) . '">' . __( 'Download invoice pdf', 'woocommerce-german-market' ) . '</a></p></li>';
					}
				}
			}

			do_action( 'wp_wc_invoice_pdf_after_invoice_download_button_order', $order_id );
		}
		
		/**
		* create the invoice pdf to shop user when choosing this option and force download
		*
		* @since 0.0.1
		* @access public
		* @static
		* @hook woocommerce_order_action_wp_wc_invoice_pdf_invoice
		* @arguments $order
		* @return void
		*/		
		public static function order_action( $order ) {
			// don't download if user is saving very first time
			if ( isset( $_REQUEST[ '_wp_http_referer' ] ) && str_replace( 'post-new.php', '', $_REQUEST[ '_wp_http_referer' ] ) != $_REQUEST[ '_wp_http_referer' ] ) {
				 update_option( 'wp_wc_invoice_pdf_new_post_message', true );
			} else {		

				do_action( 'wp_wc_invoice_pdf_before_backend_download', $order );
				do_action( 'wp_wc_invoice_pdf_before_backend_download_switch', array( 'order' => $order, 'admin' => true ) );				

				$args = array( 
							'order'				=> $order,
							'output_format'		=> 'pdf',
							'output'			=> '',
							'filename'			=> WP_WC_Invoice_Pdf_Email_Attachment::repair_filename( apply_filters( 'wp_wc_invoice_pdf_backend_filename', get_option( 'wp_wc_invoice_pdf_file_name_backend', __( 'Invoice-{{order-number}}', 'woocommerce-german-market' ) ), $order ) ),
							'admin'				=> true
						);
				$invoice = new WP_WC_Invoice_Pdf_Create_Pdf( $args );
				exit();
			}
		}
		
		/**
		* adds a small download button to the admin page for orders
		*
		* @since 0.0.1
		* @access public
		* @static 
		* @hook woocommerce_admin_order_actions
		* @arguments $actions, $theOrder
		* @return $actions
		*/	
		public static function admin_icon_download( $actions, $order ) {
			
			if ( apply_filters( 'wp_wc_invoice_pdf_backend_download_admin_icon_download_return', false, $order ) ) {
				return $actions;
			}
			
			// init css classes for js
			$invoice_pdf_order_meta = new WP_WC_Invoice_Pdf_Order_Meta( $order );
			$has_saved_content = $invoice_pdf_order_meta->has_meta();

			if ( ! $has_saved_content ) {
				$e_invoice_meta = new E_Invoice_Meta_Data( $order );
				$has_saved_content = $e_invoice_meta->has_meta();
			}

			$saved_content_class = $has_saved_content ? '' : ' hidden';
			$always_create_new = false;

			$always_create_new_pdf_status = apply_filters( 'wp_wc_invoice_pdf_always_create_new_pdf_status', array( 'pending', 'processing', 'on-hold' ) );
			if ( in_array( $order->get_status(), $always_create_new_pdf_status ) ) {
				$saved_content_class = ' hidden';
				$always_create_new = true;
			}

			// create pdf button
			$create_pdf = array( 
				'url' 		=>	wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_wp_wc_invoice_pdf_invoice_download&order_id=' . $order->get_id() ), 'wp-wc-invoice-pdf-download' ), 
				// would be nice do add html5 attribute download
				// so you get in chrome: Resource interpreted as Document but transferred with MIME type application
				'name' 		=> __( 'Download invoice pdf', 'woocommerce-german-market' ),
				'action' 	=> 'invoice_pdf' . ( $always_create_new ? ' always_create_new' : '' ),
			);

			if ( 'on' === get_option( 'wp_wc_invpice_pdf_backend_download', 'on' ) ) {
				$actions[ 'invoice_pdf' ] = $create_pdf;
			}

			$actions = apply_filters( 'wp_wc_invoice_pdf_order_admin_icon_download', $actions, $order );

			// delete pdf content button
			$delete_pdf_content = array( 
				'url' 		=>	wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_wp_wc_invoice_pdf_invoice_delete_content&order_id=' . $order->get_id() ), 'wp-wc-invoice-pdf-delete-content' ), 
				'name' 		=> __( 'Delete saved invoice content to allow regeneration of the invoice content', 'woocommerce-german-market' ),
				'action' 	=> 'invoice_pdf_delete_content' . $saved_content_class,
			);
		
			$actions[ 'invoice_pdf_delete_content' ] = $delete_pdf_content;

			return $actions;
		}

		/**
		* adds a small download button to the admin page for refunds
		*
		* @since WGM 3.0
		* @access public
		* @static 
		* @hook wgm_refunds_actions
		* @param String $string
		* @param shop_order_refund $refund
		* @return String
		*/
		public static function admin_refund_icon_download( $actions, $refund ) {
			
			if ( empty( $refund->get_parent_id() ) ) {
				return $actions;
			}

			if ( 'on' === get_option( 'wp_wc_invpice_pdf_backend_download', 'on' ) ) {
				$actions[ 'refund_pdf' ] = array(
					'url' 	=> wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_wp_wc_invoice_pdf_refund_download&refund_id=' . $refund->get_id() ), 'wp-wc-refund-pdf-download' ),
					'name' 	=> __( 'Download refund pdf', 'woocommerce-german-market' ),
					'class' => 'invoice_pdf',
					'data'	=> array(
									'refund-id' => $refund->get_id(),
									'order-id'	=> $refund->get_parent_id(),
								)
				);
			}

			$actions = apply_filters( 'wp_wc_invoice_pdf_refund_admin_icon_download', $actions, $refund );

			// Button for: Delete saved refund PDF content to allow regeneration of the refund PDF content
			$invoice_pdf_order_meta = new WP_WC_Invoice_Pdf_Order_Meta( $refund );
			$has_saved_content = $invoice_pdf_order_meta->has_meta();

			if ( ! $has_saved_content ) {
				$e_invoice_meta = new E_Invoice_Meta_Data( $refund );
				$has_saved_content = $e_invoice_meta->has_meta();
			}

			$style = $has_saved_content ? '' : 'display: none;';
			
			$actions[ 'refund_delete_saved_content' ] = array(
				'url' 	=> wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_wp_wc_invoice_pdf_refund_delete_saved_content&refund_id=' . $refund->get_id() ), 'wp-wc-refund-delete-saved-content' ),
				'name' 	=> __( 'Delete saved invoice content to allow regeneration of the invoice content', 'woocommerce-german-market' ),
				'class' => 'delete-refund-pdf-content',
				'style' => $style,
				'data'	=> array(
								'refund-id' => $refund->get_id(),
								'order-id'	=> $refund->get_parent_id(),
							) 
			);

			return $actions;
		}
		
		/**
		* ajax, manages what happen when the downloadbutton on admin order page is clicked
		*
		* @since WGM 3.0
		* @access public
		* @static 
		* @hook wp_ajax_woocommerce_wcreapdf_download
		* @arguments $_REQUEST[ 'order_id' ]
		* @return void, exit()
		*/	
		public static function admin_ajax_download_pdf() {
			
			if ( ! check_ajax_referer( 'wp-wc-invoice-pdf-download', 'security', false ) ) {
				wp_die( __( 'You have taken too long. Please go back and retry.', 'woocommerce-german-market' ), '', array( 'response' => 403 ) );
			}
						
			$order_id	= intval( $_REQUEST[ 'order_id' ] );
			$order 		= new WC_Order( $order_id );
			self::order_action( $order );
			exit();
		}

		/**
		* ajax, manages what happen when the download button for a refund is clicked
		*
		* @since WGM 3.0
		* @access public
		* @static 
		* @arguments $_REQUEST[ 'order_id' ]
		* @return void, exit()
		*/	
		public static function admin_ajax_download_refund_pdf() {

			if ( ! check_ajax_referer( 'wp-wc-refund-pdf-download', 'security', false ) ) {
				wp_die( __( 'You have taken too long. Please go back and retry.', 'woocommerce-german-market' ), '', array( 'response' => 403 ) );
			}

			// init
			$refund_id 	= $_REQUEST[ 'refund_id' ];
			$refund 	= wc_get_order( $refund_id );#
			$order_id 	= $refund->get_parent_id();
			$order 		= wc_get_order( $order_id );

			do_action( 'wp_wc_invoice_pdf_before_refund_backend_download', $refund_id );
			do_action( 'wp_wc_invoice_pdf_before_backend_download_switch', array( 'order' => $order, 'admin' => true ) );	

			add_filter( 'wp_wc_invoice_pdf_template_invoice_content', array( __CLASS__, 'load_storno_template' ) );

			// get filename
			$filename = get_option( 'wp_wc_invoice_pdf_refund_file_name_backend', 'Refund-{{refund-id}} for order {{order-number}}' );
			// replace {{refund-id}}, the other placeholders will be managed by the class WP_WC_Invoice_Pdf_Create_Pdf
			$filename = str_replace( '{{refund-id}}', $refund_id, $filename );
			$filename = apply_filters( 'wp_wc_invoice_pdf_refund_backend_filename', $filename, $refund );

			$args = array( 
				'order'				=> $order,
				'refund'			=> $refund,
				'output_format'		=> 'pdf',
				'output'			=> '',
				'filename'			=> WP_WC_Invoice_Pdf_Email_Attachment::repair_filename( $filename ),
				'admin'				=> true,
			);
			
			$refund = new WP_WC_Invoice_Pdf_Create_Pdf( $args );

			remove_filter( 'wp_wc_invoice_pdf_template_invoice_content', array( __CLASS__, 'load_storno_template' ) );

			exit();
		}

		/**
		* ajax, delete saved refund PDF content to allow regeneration of the refund PDF content
		*
		* @since WGM 3.0
		* @access public
		* @static 
		* @hook wp_ajax_woocommerce_wcreapdf_download
		* @arguments $_REQUEST[ 'order_id' ]
		* @return void, exit()
		*/
		public static function admin_ajax_refund_delete_saved_content() {

			if ( ! check_ajax_referer( 'wp-wc-refund-delete-saved-content', 'security', false ) ) {
				wp_die( __( 'You have taken too long. Please go back and retry.', 'woocommerce-german-market' ), '', array( 'response' => 403 ) );
			}

			$refund_id = intval( $_REQUEST[ 'refund_id' ] );

			// delete post meta
			$refund = wc_get_order( $refund_id );
			$invoice_pdf_order_meta = new WP_WC_Invoice_Pdf_Order_Meta( $refund );
			$invoice_pdf_order_meta->delete_meta();

			// make a notice
			$notice = sprintf( __( 'The saved invoice content of the refund <i>#%s</i> has been deleted, i.e. the content will be regenerated the next time when the invoice is generated.', 'woocommerce-german-market' ), $refund_id );

			do_action( 'wp_wc_invoice_pdf_after_delete_refund_saved_content', $refund );
			
			// redirect to referer with notice
			wp_safe_redirect( wp_get_referer() . '&notice=' . urlencode( $notice ) );
			
			exit();

		}

		/**
		* ajax, delete saved invoice PDF content to allow regeneration of the invoice PDF content
		*
		* @since WGM 3.1
		* @access public
		* @static 
		* @hook woocommerce_order_action_wp_wc_invoice_pdf_delete_content
		* @arguments $_REQUEST[ 'order_id' ]
		* @return void, exit()
		*/
		public static function invoice_pdf_delete_saved_content() {

			if ( ! check_ajax_referer( 'wp-wc-invoice-pdf-delete-content', 'security', false ) ) {
				wp_die( __( 'You have taken too long. Please go back and retry.', 'wp-wc-invoice-delete-saved-content' ), '', array( 'response' => 403 ) );
			}

			$order_id = intval( $_REQUEST[ 'order_id' ] );
			$order = wc_get_order( $order_id );
			$invoice_pdf_order_meta = new WP_WC_Invoice_Pdf_Order_Meta( $order );
			$invoice_pdf_order_meta->delete_meta();
			
			do_action( 'wp_wc_invoice_pdf_after_delete_saved_content', $order );

			// redirect to referer with notice with query var to show admin notice
			wp_safe_redirect( wp_get_referer() . '&gm_delete_pdf_content_notice=' . $order_id . '&gm_notice_time=' . time() );

			exit();

		}
		
		/**
		* load storno template instead of general template
		*
		* @param String $invoice_template_path
		* @hook wp_wc_invoice_pdf_template_invoice_content
		* @return String
		*/	
		public static function load_storno_template( $template_path ) {

			$theme_template_file = get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'woocommerce-invoice-pdf' . DIRECTORY_SEPARATOR . 'refund-content.php';
			if ( file_exists( $theme_template_file ) ) {
				$template_path = $theme_template_file;
			} else {
				$template_path = untrailingslashit( plugin_dir_path( Woocommerce_Invoice_Pdf::$plugin_filename ) ) . DIRECTORY_SEPARATOR . 'vendors' . DIRECTORY_SEPARATOR . 'self' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'refund-content.php';
			}

			return $template_path;

		}

		/**
		* ajax, manages what happen when the test download button is clicked
		*
		* @since 0.0.1
		* @access public
		* @static 
		* @hook wp_ajax_woocommerce_wp_wc_invoice_pdf_test_invoice
		* @return void, exit()
		*/	
		public static function admin_ajax_test_invoice() {

			if ( ! check_ajax_referer( 'wp-wc-invoice-pdf-test-invoice', 'security' ) ) {
				wp_die( __( 'You have taken too long. Please go back and retry.', 'woocommerce-german-market' ), '', array( 'response' => 403 ) );
			}

			$subtab = isset( $_REQUEST[ 'subtab' ] ) ? esc_attr( $_REQUEST[ 'subtab' ] ) : 'x';
			
			$args = array( 
						'order'				=> 'test',
						'output_format'		=> 'pdf',
						'output'			=> '',
						'filename'			=> WP_WC_Invoice_Pdf_Email_Attachment::repair_filename( apply_filters( 'wp_wc_invoice_pdf_test_filename', 'test-invoice' ) ),
						'subtab'			=> $subtab,
					);
			$invoice = new WP_WC_Invoice_Pdf_Create_Pdf( $args );
			exit();
		}
		
		/**
		* download not possible - admin notice
		*
		* @since 0.0.1
		* @access public
		* @static
		* @hook admin_notices
		* @return void
		*/	
		public static function output_notices() {
			echo '<div class="error"><p>' . __( 'Sorry, the invoice could not be downloaded because you saved this order for the very first time. Please, click again "Download Invoice" to get your invoice.', 'woocommerce-german-market' ) . '</p></div>'; 
			update_option( 'wp_wc_invoice_pdf_new_post_message', false );
		}

		/**
		* show notice if invoice pdf content has been deleted
		*
		* @since 3.1
		* @access public
		* @static 
		* @hook admin_notices
		* @return void
		*/
		public static function admin_notices() {

			if ( isset( $_GET[ 'gm_delete_pdf_content_notice' ] ) ) {
				
				$notice_time = isset( $_GET[ 'gm_notice_time' ] ) ? $_GET[ 'gm_notice_time' ] : time();

				if ( $notice_time + 2 > time() ) {

					$notice = sprintf( __( 'The saved invoice content of the order <i>#%s</i> has been deleted, i.e. the content will be regenerated the next time when the invoice is generated.', 'woocommerce-german-market' ), $_GET[ 'gm_delete_pdf_content_notice' ] );

					?>
						<div class="updated">
					      <p><?php echo $notice; ?></p>
					   </div>
					<?php
				}
			}
		}

		/**
		* add bulk action
		*
		* @access public
		* @static 
		* @hook WGM_Hpos::get_hook_for_order_bulk_actions()
		* @param Array $actions
		* @return Array
		*/
		public static function add_bulk_actions( $actions ) {
			$actions[ 'gm_download_invoices_zip' ] = __( 'Downloads Invoice PDFs', 'woocommerce-german-market' );
			return $actions;
		}

		/**
		* do bulk action download zip with invoice pdfs
		*
		* @since 3.1
		* @access public
		* @static 
		* @hook load-edit.php
		* @return void
		*/
		public static function bulk_action( $redirect_to, $action, $order_ids ) {

			// clear cache
			self::clear_zip_cache();

			if ( empty( $order_ids ) ) {
				return $redirect_to;
			}

			if ( 'gm_download_invoices_zip' !== $action ) {
				return $redirect_to;
			}

			do_action( 'german_market_before_bulk_for_pdfs' );

			$created_one_pdf = false;

			foreach ( $order_ids as $order_id ) {

				// create pdf
				$order = wc_get_order( $order_id );

				if ( apply_filters( 'wp_wc_invoice_pdf_backend_download_order_not_in_bulk_zip', false, $order ) ) {
					continue;
				}

				do_action( 'wp_wc_invoice_pdf_before_backend_download_switch', array( 'order' => $order, 'admin' => true ) );

				$args = array( 
					'order'				=> $order,
					'output_format'		=> 'pdf',
					'output'			=> 'cache-zip',
					'filename'			=> WP_WC_Invoice_Pdf_Email_Attachment::repair_filename( apply_filters( 'wp_wc_invoice_pdf_backend_filename', get_option( 'wp_wc_invoice_pdf_file_name_backend', get_bloginfo( 'name' ) . '-' . __( 'Invoice-{{order-number}}', 'woocommerce-german-market' ) ), $order ) ),
					'admin'				=> true,
				);

				$invoice = new WP_WC_Invoice_Pdf_Create_Pdf( $args );

				$created_one_pdf = true;

			}

			// create zip file
			if ( $created_one_pdf ) {
				$zip_dir  = untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'woocommerce-invoice-pdf-zip' . DIRECTORY_SEPARATOR;
				wp_mkdir_p( $zip_dir );
				$zip_file = $zip_dir . time() . "_" . rand( 1, 999999 ) . '_' . md5( rand( 1, 999999 ) . 'wp_wc_invoice_pdf' ) . md5( 'woocommerce-invoice-pdf' . rand( 0, 999999 ) ) . '.zip';
				$files = array_diff( scandir( $zip_dir ), array( '.', '..' ) );

				if ( class_exists( 'ZipArchive' ) ) {

					$zip = new ZipArchive();
					
					if ( $zip->open( $zip_file, ZipArchive::CREATE ) ) {
				    
						foreach ( $files as $file ) {
							$zip->addFile( $zip_dir . $file, $file );
						}

						$zip->close();
					}

				} else {

					// use PclZip of WordPress
					$pclizip_class = ABSPATH . 'wp-admin/includes/class-pclzip.php';
					if ( file_exists( $pclizip_class ) ) {
						require_once $pclizip_class;
						$zip = new PclZip( $zip_file );

						foreach ( $files as $file ) {
							$zip->add( $zip_dir . $file, PCLZIP_OPT_REMOVE_ALL_PATH );
						}
					}
				}
				
				// clear pdf cache
				self::clear_zip_cache( true );

				// download zip file
				header( 'Content-Type: application/zip');
				header( 'Content-disposition: attachment; filename=' . apply_filters( 'wp_wc_invoice_pdf_zip_filename', date( 'Y-m-d-H-i' ) . '-' . __( 'invoices', 'woocommerce-german-market' ) . '.zip' ) );
				header( 'Content-Length: ' . filesize( $zip_file ) );
				readfile( $zip_file );

				exit();
			}

			return $redirect_to;
		}

		/**
		* clear zip cache
		*
		* @since 3.1
		* @access private
		* @static 
		* @param Boolean $zip
		* @return void
		*/
		public static function clear_zip_cache( $zip = false, $deactivate = false ) {
	
			$cache_dir = untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'woocommerce-invoice-pdf-zip' . DIRECTORY_SEPARATOR;

			if ( ! is_dir( $cache_dir ) ) {
				return;
			}

			$files = array_diff( scandir( $cache_dir ), array( '.', '..' ) );
			
			foreach ( $files as $file ) {
				
				if ( $zip ) {
					
					if ( str_replace( '.zip', '', $file ) != $file ) {
						continue;
					}
				}
				
				unlink( $cache_dir . DIRECTORY_SEPARATOR . $file );

			}

			if ( $deactivate ) {
				rmdir( $cache_dir );
			}
	
		}

		/**
		* submit button for refunds
		*
		* @since 3.1
		* @access public
		* @static 
		* @hook woocommerc_de_refund_after_list, woocommerc_de_refund_before_list
		* @return void
		*/
		public static function submit_button() {

			self::clear_zip_cache();
			?><input class="button-primary" type="submit" name="download-refund-zip" value="<?php echo __( 'Download Refund PDFs in ZIP', 'woocommerce-german-market' ); ?>"/><?php
		}

		/**
		* bulk download for refunds
		*
		* @since 3.1
		* @access public
		* @static 
		* @hook admin_init
		* @return void
		*/
		public static function bulk_action_refunds() {
			
			if ( isset( $_REQUEST[ 'download-refund-zip' ] ) ) {
				
				// clear cache
				self::clear_zip_cache();

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

					$order 		= wc_get_order( $order_id );

					if ( apply_filters( 'wp_wc_invoice_pdf_backend_download_refund_not_in_bulk_zip', false, $refund, $order ) ) {
						continue;
					}

					do_action( 'german_market_before_bulk_for_pdfs' );
					do_action( 'wp_wc_invoice_pdf_before_backend_download_switch', array( 'order' => $order, 'admin' => true ) );	

					add_filter( 'wp_wc_invoice_pdf_template_invoice_content', array( __CLASS__, 'load_storno_template' ) );

					// get filename
					$filename = get_option( 'wp_wc_invoice_pdf_refund_file_name_backend', 'Refund-{{refund-id}} for order {{order-number}}' );
					// replace {{refund-id}}, the other placeholders will be managed by the class WP_WC_Invoice_Pdf_Create_Pdf
					$filename = str_replace( '{{refund-id}}', $refund_id, $filename );
					$filename = apply_filters( 'wp_wc_invoice_pdf_refund_backend_filename', $filename, $refund );

					$args = array( 
								'order'				=> $order,
								'refund'			=> $refund,
								'output_format'		=> 'pdf',
								'output'			=> 'cache-zip',
								'filename'			=> WP_WC_Invoice_Pdf_Email_Attachment::repair_filename( $filename ),
								'admin'				=> true,
							);

					$invoice = new WP_WC_Invoice_Pdf_Create_Pdf( $args );

					remove_filter( 'wp_wc_invoice_pdf_template_invoice_content', array( __CLASS__, 'load_storno_template' ) );

				}
				
				// create zip file
				$zip_dir = untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'woocommerce-invoice-pdf-zip' . DIRECTORY_SEPARATOR;
				wp_mkdir_p( $zip_dir );
				$zip_file = $zip_dir . time() . "_" . rand( 1, 999999 ) . '_' . md5( rand( 1, 999999 ) . 'wp_wc_invoice_pdf' ) . md5( 'woocommerce-invoice-pdf' . rand( 0, 999999 ) ) . '.zip';
				$files = array_diff( scandir( $zip_dir ), array( '.', '..' ) );

				if ( class_exists( 'ZipArchive' ) ) {
					
					$zip = new ZipArchive();
					
					if ( $zip->open( $zip_file, ZipArchive::CREATE ) ) {
				 
						foreach ( $files as $file ) {
							$zip->addFile( $zip_dir . $file, $file );
						}

						$zip->close();
					}

				} else {

					// use PclZip of WordPress
					$pclizip_class = ABSPATH . 'wp-admin/includes/class-pclzip.php';
					if ( file_exists( $pclizip_class ) ) {
						require_once $pclizip_class;
						$zip = new PclZip( $zip_file );

						foreach ( $files as $file ) {
							$zip->add( $zip_dir . $file, PCLZIP_OPT_REMOVE_ALL_PATH );
						}
					}
				}
				
				// clear pdf cache
				self::clear_zip_cache( true );

				// download zip file
				header( 'Content-Type: application/zip');
				header( 'Content-disposition: attachment; filename=' . apply_filters( 'wp_wc_invoice_pdf_zip_filename', date( 'Y-m-d-H-i' ) . '-' . __( 'refunds', 'woocommerce-german-market' ) . '.zip' ) );
				header( 'Content-Length: ' . filesize( $zip_file ) );
				readfile( $zip_file );

				exit();
			}
		}
	} // end class
} // end if
