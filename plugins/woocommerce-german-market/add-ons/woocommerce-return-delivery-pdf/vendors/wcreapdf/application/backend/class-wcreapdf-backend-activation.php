<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCREAPDF_Backend_Activation' ) ) {
	
	/**
	* When plugin is activated
	*
	* @class WCREAPDF_Backend_Activation
	* @version 1.0
	* @category	Class
	*/
	class WCREAPDF_Backend_Activation {
		
		/**
		* when activated
		*
		* @since 0.0.1
		* @access public
		* @static
		* @return void
		*/	
		public static function activation() {
			self::create_temp_directories();
		}		
		
		/**
		* create cache folders for pdfs and images
		*
		* @since 0.0.1
		* @access public
		* @static
		* @return void
		*/
		public static function create_temp_directories() {
			if ( ! file_exists( untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' ) ) {
				mkdir( untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' );
			}
			
			if ( ! file_exists( untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'woocommerce-return-delivery-pdf' ) ) {
				mkdir( untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'woocommerce-return-delivery-pdf' );
				mkdir( untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'woocommerce-return-delivery-pdf' . DIRECTORY_SEPARATOR . 'pdf' );
				mkdir( untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'woocommerce-return-delivery-pdf' . DIRECTORY_SEPARATOR . 'fonts' );	
			}
		}	
		
	} // end class
} // end if
