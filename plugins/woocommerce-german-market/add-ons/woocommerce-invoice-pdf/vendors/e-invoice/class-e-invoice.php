<?php

namespace MarketPress\German_Market\E_Invoice;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

class E_Invoice {

	/**
	 * Singleton object
	 * @var object
	 */
	static $instance = null;

	/**
	 * Singleton getInstance
	 *
	 * @access public
	 * @return MarketPress\German_Market\E_Invoice\E_Invoice
	 */
	public static function get_instance() {

		if ( self::$instance == NULL) {
			self::$instance = new E_Invoice();
		}

		return self::$instance;
	}

	/**
	* Construct
	* 
	* @return void
	*/
	public function __construct() {
		
		spl_autoload_register( array( $this, 'autoload' ) );

		if ( 'on' === get_option( 'german_market_einvoice_backend_download_xml', 'off' ) ) {
			
			add_filter( 'wp_wc_invoice_pdf_order_admin_icon_download', array( 'MarketPress\\German_Market\\E_Invoice\\Backend_Download', 'admin_icon_download' ), 10, 2 );
			add_action( 'wp_wc_invoice_pdf_refund_admin_icon_download', array( 'MarketPress\\German_Market\\E_Invoice\\Backend_Download', 'admin_refund_icon_download' ), 10, 2 );
			add_action( 'wp_ajax_german_market_e_invoice', array( 'MarketPress\\German_Market\\E_Invoice\\Backend_Download', 'admin_ajax_download_xml' ) );
			add_action( 'wp_wc_invoice_pdf_after_invoice_download_button_order', array( 'MarketPress\\German_Market\\E_Invoice\\Backend_Download', 'order_download' ) );

			add_action( 'admin_init', function() {
				add_action( \WGM_Hpos::get_hook_for_order_bulk_actions(), array( 'MarketPress\\German_Market\\E_Invoice\\Backend_Download' , 'add_bulk_actions' ), 10 );
				add_action( \WGM_Hpos::get_hook_for_order_handle_bulk_actions(), array( 'MarketPress\\German_Market\\E_Invoice\\Backend_Download', 'bulk_action' ), 10, 3 );
			});

			add_action( 'woocommerc_de_refund_before_list', array( 'MarketPress\\German_Market\\E_Invoice\\Backend_Download', 'refund_button' ), 20 );
			add_action( 'woocommerc_de_refund_after_list', array( 'MarketPress\\German_Market\\E_Invoice\\Backend_Download', 'refund_button' ), 20 );
			add_action( 'admin_init', array( 'MarketPress\\German_Market\\E_Invoice\\Backend_Download', 'bulk_action_refunds' ) );
		}

		add_action( 'wp_wc_invoice_pdf_after_delete_saved_content', array( 'MarketPress\\German_Market\\E_Invoice\\Backend_Download', 'delete_saved_content' ) );
		add_action( 'wp_wc_invoice_pdf_after_delete_refund_saved_content', array( 'MarketPress\\German_Market\\E_Invoice\\Backend_Download', 'delete_saved_content' ) );

		// Backend Menu
		add_filter( 'german_market_invoice_pdf_submenu', array( 'MarketPress\\German_Market\\E_Invoice\\Backend_Menu', 'backend_menu' ) );

		// Email Attachment XML
		$selected_mails = get_option( 'german_market_einvoice_recipients_xml_emails', array() );
		if ( ! empty( $selected_mails ) ) {

			add_filter( 'woocommerce_email_attachments', array( 'MarketPress\\German_Market\\E_Invoice\\E_Invoice_Email_Attachment', 'add_attachment' ), 10, 3 );
			
			if ( in_array( 'customer_refunded_order', $selected_mails ) ) {
				add_filter( 'woocommerce_order_fully_refunded_notification', array( 'MarketPress\\German_Market\\E_Invoice\\E_Invoice_Email_Attachment', 'trigger_refund' ), 10, 2 );
				add_filter( 'woocommerce_order_partially_refunded_notification', array( 'MarketPress\\German_Market\\E_Invoice\\E_Invoice_Email_Attachment', 'trigger_refund' ), 10, 2 );
			}
		}
	}

	/**
	 * Autoload
	 *
	 * @return void
	 */
	public function autoload( $classname ) {

		$src_dir = untrailingslashit( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
		$namespace = 'MarketPress\\German_Market\\E_Invoice\\';

		$xml_type = 'zugferd';

		$class_map = array(
			
			$namespace . 'Backend_Download' 			=> $src_dir . 'class-backend-download.php',
			$namespace . 'Backend_Menu' 				=> $src_dir . 'class-backend-menu.php',
			$namespace . 'E_Invoice_Manager' 			=> $src_dir . 'abstract-class-manager.php',
			$namespace . 'E_Invoice_General' 			=> $src_dir . 'abstract-class-' . $xml_type . '-general.php',
			$namespace . 'E_Invoice_Order' 				=> $src_dir . 'class-' . $xml_type . '-order.php',
			$namespace . 'E_Invoice_Merge_And_Save'		=> $src_dir . 'class-' . $xml_type . '-merge-and-save.php',
			$namespace . 'E_Invoice_Order_Conditions'	=> $src_dir . 'class-order-conditions.php',
			$namespace . 'E_Invoice_Email_Attachment'	=> $src_dir . 'class-email-attachment.php',
			$namespace . 'E_Invoice_Meta_Data'			=> $src_dir . 'class-meta-data.php',

		);

		if ( isset( $class_map[ $classname ] ) ) {
			require_once $class_map[ $classname ];
		}
	}
}
