<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_WC_Invoice_Pdf_Backend_Activation' ) ) {
	
	/**
	* When plugin is activated
	*
	* @class WP_WC_Invoice_Pdf_Backend_Activation
	* @version 1.0
	* @category	Class
	*/
	class WP_WC_Invoice_Pdf_Backend_Activation {
		
		/**
		* when activated
		*
		* @since 0.0.1
		* @access public
		* @static
		* @return void
		*/	
		public static function activation() {
			update_option( 'wp_wc_invoice_pdf_just_activated', true );
		}
		
		/**
		* set default values for some options that should be avaiable even if the user never visit the setting pages before
		* otherwise the class Ebs_Pdf_Wordpress would use default values set in abstract class Ebs_Pdf
        * try to check whether user would use A4 or letter, in or cm
		*
		* @since 0.0.1
		* @access public
		* @static
		* @hook admin_init
		* @return void
		*/	
		public static function load_defaults() {
			if ( get_option( 'wp_wc_invoice_pdf_document_title', false ) === false ) {			
				update_option( 'wp_wc_invoice_pdf_document_title', 		__( 'Invoice', 'woocommerce-german-market' ) . ' - ' . get_bloginfo( 'name' ) );								// title of pdf file
				update_option( 'wp_wc_invoice_pdf_file_name_backend', 	get_bloginfo( 'name' ) . '-' . __( 'Invoice-{{order-number}}', 'woocommerce-german-market' ) );			// pdf file name in backend
				update_option( 'wp_wc_invoice_pdf_file_name_frontend', 	__( 'Invoice-{{order-number}}', 'woocommerce-german-market' ) );											// pdf file name in backend			

				// now we set options that depends on whether in or cm will be used
				$locale = get_locale();
				$letter_size = array ( 'en_US', 'haw_US', 'en_CA', 'es_CO', 'es_VE', 'es_CL' );	// locales using letter size
				if ( in_array ( $locale, $letter_size ) ) {
					update_option( 'wp_wc_invoice_pdf_paper_size', 	'letter' );	
				} else {
					update_option( 'wp_wc_invoice_pdf_paper_size', 	'A4' );	
				}
				$inches = array ( 'en_US', 'haw_US', 'my_MM' );
				$default_margins = 2; // centimeter
				if ( in_array ( $locale, $inches ) ) {
					update_option( 'wp_wc_invoice_pdf_user_unit', 	'in' );
					$default_margins = 1; // inches
				} else {
					update_option( 'wp_wc_invoice_pdf_user_unit', 	'cm' );	
				}
				
				// page margins as defaults
				update_option( 'wp_wc_invoice_pdf_body_margin_top', 						$default_margins );
				update_option( 'wp_wc_invoice_pdf_body_margin_right', 						$default_margins );
				update_option( 'wp_wc_invoice_pdf_body_margin_bottom', 						$default_margins * 0.5 ); // half of the value, cause we use a default footer height		
				update_option( 'wp_wc_invoice_pdf_body_margin_left',						$default_margins );	
				update_option( 'wp_wc_invoice_pdf_footer_padding_left',						$default_margins );	
				update_option( 'wp_wc_invoice_pdf_footer_padding_right',						$default_margins );	
				update_option( 'wp_wc_invoice_pdf_header_padding_left',						$default_margins );	
				update_option( 'wp_wc_invoice_pdf_header_padding_right',						$default_margins );	
				
				// customer's billing address field
				update_option( 'wp_wc_invoice_pdf_billing_address_width', 					( $default_margins == 2 ) ? 8.5 : 3.346 );
				update_option( 'wp_wc_invoice_pdf_billing_address_height', 					( $default_margins == 2 ) ? 4.5 : 1.772 );
				update_option( 'wp_wc_invoice_pdf_billing_address_top_margin', 				( $default_margins == 2 ) ? 0.7 : 0.28 );
				update_option( 'wp_wc_invoice_pdf_billing_address_bottom_margin', 			( $default_margins == 2 ) ? 1.5 : 0.59 );		
				
				// margins after subject
				update_option( 'wp_wc_invoice_pdf_invoice_start_margin_after_subject',		( $default_margins == 2 ) ? 0.75 : 0.3 );
				
				// set footer margins for default page number
				update_option( 'wp_wc_invoice_pdf_footer_height',		 					( $default_margins == 2 ) ? 1 : 0.39 );
				update_option( 'wp_wc_invoice_pdf_footer_padding_bottom', 					( $default_margins == 2 ) ? 0.5 : 0.19685 );
			}
		}
		
		/**
		* create cache folders for pdfs
		*
		* @since 0.0.1
		* @access public
		* @static
		* @return void
		*/
		public static function setup_cache_dir() {
			if ( ! file_exists( untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' ) ) {
				wp_mkdir_p( untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' );
			}
			if ( ! file_exists( untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'woocommerce-invoice-pdf' ) ) {
				wp_mkdir_p( untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'woocommerce-invoice-pdf' );
			}
		}	
		
	} // end class
} // end if
