<?php
/* 
 * Add-on Name:	Lexoffice
 * Description:	Lexoffice API for Woocommerce
 * Author:		MarketPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

if ( ! function_exists( 'lexoffice_woocommerce_init' ) ) {

	/**
	* init
	*
	* @return void
	*/
	function lexoffice_woocommerce_init() {

		// choose voucher or invoice api
		$voucher_or_invoice_api = get_option( 'woocommerce_de_lexoffice_voucher_or_invoice', 'voucher' );

		if ( ! in_array( $voucher_or_invoice_api, array( 'voucher', 'invoice' ) ) ) {
			return;
		}

		$app_dir = untrailingslashit( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'application';

		// auto-load classes on demand
		if ( function_exists( "__autoload" ) ) {
			spl_autoload_register( "__autoload" );
		}

		spl_autoload_register( 'german_market_lexoffice_autoload' );

		if ( is_admin() ) {

			// stuff that is only needed in the shop order table
			require_once( $app_dir . DIRECTORY_SEPARATOR . 'edit-shop-order.php' );
			add_action( 'current_screen', 'lexoffice_woocommerce_edit_shop_order' );

			// settings
			require_once( $app_dir . DIRECTORY_SEPARATOR . 'settings.php' );
			add_filter( 'woocommerce_de_ui_left_menu_items', 'lexoffice_woocommerce_de_ui_left_menu_items' );
			add_action( 'woocommerce_de_ui_update_options', 'lexoffice_woocommerce_de_ui_update_options' );

			// ajax handler
			if ( function_exists( 'curl_init' ) ) {
				
				$ajax_handler = German_Market_Lexoffice_Ajax_Handler::get_instance();

				// payment gateways option: due date
				require_once( $app_dir . DIRECTORY_SEPARATOR . 'due-date.php' );
				add_filter( 'german_market_gateway_settings_single_gateway', 'lexoffice_woocommerce_due_date_settings_field', 10, 2 );

				// user profile
				if ( 
					( 
						( 'voucher' === $voucher_or_invoice_api ) && 
						( 'collective_contact' !== get_option( 'woocommerce_de_lexoffice_contacts', 'collective_contact' ) ) 
					) || 
					( 'invoice' === $voucher_or_invoice_api ) 
				) {
					require_once( $app_dir . DIRECTORY_SEPARATOR . 'user-profile.php' );
					add_action( 'show_user_profile', 'lexoffice_woocommerce_profile_fields', 21 );
					add_action( 'edit_user_profile', 'lexoffice_woocommerce_profile_fields', 21 );

					add_action( 'personal_options_update', 'lexoffice_woocommerce_save_profile_fields' );
					add_action( 'edit_user_profile_update', 'lexoffice_woocommerce_save_profile_fields' );
				}
			}		
		}


		if ( 'voucher' === $voucher_or_invoice_api ) { // Load voucher api

			require_once( $app_dir . DIRECTORY_SEPARATOR . 'legacy-api-functions.php' );
		
		} else if ( 'invoice' === $voucher_or_invoice_api ) { // Load invoice api

			add_action( 'admin_init', function() {
				
				add_filter( WGM_Hpos::get_hook_for_manage_shop_order_posts_columns(), array( 'German_Market_Lexoffice_Backend_Invoice_Number_Column', 'shop_order_columns' ), 20 );
				add_filter( WGM_Hpos::get_hook_for_manage_edit_shop_order_sortable_columns(), array( 'German_Market_Lexoffice_Backend_Invoice_Number_Column', 'shop_order_sortable_columns' ) );
				add_filter( WGM_Hpos::get_hook_manage_shop_order_custom_column(), array( 'German_Market_Lexoffice_Backend_Invoice_Number_Column', 'render_shop_order_columns' ), 10, 2 );

				add_action( 'wgm_refunds_backend_columns', array( 'German_Market_Lexoffice_Backend_Invoice_Number_Column', 'refund_columns' ), 5 );
				add_filter( 'wgm_refunds_array', array( 'German_Market_Lexoffice_Backend_Invoice_Number_Column', 'refund_item' ) );

			});

			if ( WGM_Hpos::is_hpos_enabled() ) {
				add_filter( 'woocommerce_order_list_table_prepare_items_query_args', array( 'German_Market_Lexoffice_Backend_Invoice_Number_Column', 'shop_order_sort_hpos' ) );
			} else {
				add_action( 'pre_get_posts', array( 'German_Market_Lexoffice_Backend_Invoice_Number_Column', 'shop_order_sort' ) );
			}

			add_filter( 'woocommerce_shop_order_search_fields',	array( 'German_Market_Lexoffice_Backend_Invoice_Number_Column', 'search_query' ) );
			add_filter( 'woocommerce_order_table_search_query_meta_keys', array( 'German_Market_Lexoffice_Backend_Invoice_Number_Column', 'search_query' ) );

			// Email attachment
			if ( 'off' === get_option( 'woocommerce_de_lexoffice_invoice_api_draft_mode', 'off' ) ) {
				add_filter( 'woocommerce_email_attachments', array( 'German_Market_Lexoffice_Invoice_Email_Attachment', 'add_attachment' ), 10, 3 );

				if ( 'on' === get_option( 'woocommerce_de_lexoffice_credit_note_email_attachment', 'off' ) ) {
					add_filter( 'woocommerce_order_fully_refunded_notification', array( 'German_Market_Lexoffice_Invoice_Email_Attachment', 'trigger_refund' ), 10, 2 );
					add_filter( 'woocommerce_order_partially_refunded_notification', array( 'German_Market_Lexoffice_Invoice_Email_Attachment', 'trigger_refund' ), 10, 2 );
				}
			}

			// My Account
			if ( 'off' === get_option( 'woocommerce_de_lexoffice_invoice_api_draft_mode', 'off' ) ) {
				if ( 'on' === get_option( 'woocommerce_de_lexoffice_invoice_api_my_account_orders', 'off' ) ) {
					add_filter( 'woocommerce_my_account_my_orders_actions', array( 'German_Market_Lexoffice_Invoice_My_Account', 'make_download_button_in_order_actions' ), 10, 2 );
				}

				if ( 'on' === get_option( 'woocommerce_de_lexoffice_invoice_api_my_account_order_view', 'off' ) ) {
					add_action( 'woocommerce_order_details_after_order_table', array( 'German_Market_Lexoffice_Invoice_My_Account', 'make_download_button' ) );
				}

				if ( 'on' === get_option( 'woocommerce_de_lexoffice_invoice_api_my_account_order_view_refunds', 'off' ) ) {
					add_action( 'woocommerce_order_details_after_order_table', array( 'German_Market_Lexoffice_Invoice_My_Account', 'make_download_button_refunds' ) );
				}
				
				add_action( 'wp_ajax_woocommerce_german_market_lexoffice_invoice_pdf', array( 'German_Market_Lexoffice_Invoice_My_Account', 'download_pdf' ) );
			}

			add_filter( 'gm_split_tax_rounding_precision', function( $precision ) {
				return 4; // TO DO: Nötig?
			}, 99999999 );
		}

		// handling orders with refunds
		if ( get_option( 'woocommerce_de_lexoffice_transmit_order_before_refund', 'on' ) === 'on' ) {
			add_action( 'woocommerce_de_lexoffice_api_before_send_refund', 'lexoffice_send_order_before_refund', 10, 2 );
		}

		// allow to transmit orders with refunds
		add_filter( 'woocommerce_de_lexoffice_force_transmission_even_if_not_completed', function( $allow = false, $order = null ) {

			if ( ! $allow ) {
				if ( is_object( $order ) && method_exists( $order, 'get_refunds' ) ) {
					$count_refunds = count( $order->get_refunds() );
					if ( $count_refunds > 0 ) {
						$allow = true;
					}
				}
			}

			return $allow;
		}, 9, 2 );

		// automatic transmission
		require_once( untrailingslashit( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'auto-transmission.php' );

		// bulk transmission
		$lexoffice_bulk_transmission = new Bulk_Transmission_lexoffice();
	}
	
	lexoffice_woocommerce_init();
}

/**
 * Autoload
 * 
 * @since 3.35
 * @param $class
 * @return void
 */
function german_market_lexoffice_autoload( $class ) {

	$app_dir = untrailingslashit( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR;

	$classes_and_files = array(

		'German_Market_Lexoffice_Semaphore'						=> $app_dir . 'class-semaphore.php',
		'German_Market_Lexoffice_Ajax_Handler'					=> $app_dir . 'class-ajax-handler.php',
		'German_Market_Lexoffice_Edit_Shop_Order'				=> $app_dir . 'class-edit-shop-order-functions.php',
		'Bulk_Transmission_lexoffice'							=> $app_dir . 'class-bulk-transmission.php',
		
		'German_Market_Lexoffice_API_Order' 					=> $app_dir . 'class-api-order.php',
		'German_Market_Lexoffice_API_Refund' 					=> $app_dir . 'class-api-refund.php',
		'German_Market_Lexoffice_API_Contact' 					=> $app_dir . 'class-api-contact.php',
		'German_Market_Lexoffice_API_PDF' 						=> $app_dir . 'class-api-pdf.php',
		'German_Market_Lexoffice_API_Auth' 						=> $app_dir . 'class-api-auth.php',
		'German_Market_Lexoffice_API_General' 					=> $app_dir . 'class-api-general.php',
		'German_Market_Lexoffice_API_Transaction_Assignment'	=> $app_dir . 'class-api-transaction-assignment.php',
		
		'German_Market_Lexoffice_Invoice_API'					=> $app_dir . 'class-invoice-api.php',
		'German_Market_Lexoffice_Invoice_API_General'			=> $app_dir . 'class-invoice-api-general.php',
		'German_Market_Lexoffice_Invoice_API_Items'				=> $app_dir . 'class-invoice-api-items.php',
		'German_Market_Lexoffice_Invoice_API_Credit_Note'		=> $app_dir . 'class-invoice-api-credit-note.php',
		'German_Market_Lexoffice_Backend_Invoice_Number_Column'	=> $app_dir . 'class-invoice-number-column.php',
		'German_Market_Lexoffice_Invoice_Email_Attachment'		=> $app_dir . 'class-invoice-email-attachment.php',
		'German_Market_Lexoffice_Invoice_My_Account'			=> $app_dir . 'class-invoice-my-account.php',
	);

	if ( isset( $classes_and_files[ $class ] ) ) {
		require_once( $classes_and_files[ $class ] );
	}
}
