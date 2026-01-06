<?php

namespace MarketPress\German_Market\E_Invoice;

use MarketPress\German_Market\horstoeko\zugferd\ZugferdSettings;

abstract class E_Invoice_Manager {

	/**
	 * Get XML
	 * 
	 * @return string
	 */
	public abstract function get_xml();

	/**
	 * Save XML file temporarily
	 * Have to return path as string
	 * 
	 * @return String
	 */
	public abstract function save_file( $path = null );

	/**
	 * Get filename for XML
	 * 
	 * @return String
	 */
	public abstract function get_filename();

	/**
	 * Download XML browser
	 * 
	 * @return void
	 */
	public function download_xml_file() {
		header( 'Content-type: text/xml' );
		header( 'Content-Disposition: attachment; filename="' . $this->get_filename() . '.xml"' );
  
		echo $this->get_xml();
		exit();
	}

	/**
	 * Save XML file temporarily and return path on server
	 * 
	 * @return String
	 */
	public function save_xml_temp_and_get_path() {
		
		$directory_name	= time() . "_" . rand( 1, 99999 ) . '_' . md5( rand( 1, 99999 ) . 'gm_e_invoice' ) . md5( 'gm_e_invoice' . rand( 0, 99999 ) );

		wp_mkdir_p( WP_WC_INVOICE_PDF_CACHE_DIR . $directory_name );

		$file_name = $this->get_filename() . '.xml';

		$path = WP_WC_INVOICE_PDF_CACHE_DIR . $directory_name . DIRECTORY_SEPARATOR . $file_name;
		$path = $this->save_file( $path );
		return $path;
	}

	/**
	 * Change Hooks before getting order items
	 * 
	 * @return void
	 */
	public function before_get_items() {
		
		$amount_decimals = 6;

		ZugferdSettings::setUnitAmountDecimals( $amount_decimals );
		remove_filter( 'woocommerce_order_item_name', array( 'WGM_Template', 'add_delivery_time_to_product_title' ), 10, 2 );

		// dont's show product image in product name
		if ( 'on' === get_option( 'german_market_product_images_in_order', 'off' ) ) {
			remove_filter( 'woocommerce_order_item_name', array( 'WGM_Product', 'add_thumbnail_to_order' ), 100, 3 );
		}
	}

	/**
	 * Undo what happened in method "before_get_items"
	 * 
	 * @return void
	 */
	public function after_get_items() {

		add_filter( 'woocommerce_order_item_name', array( 'WGM_Template', 'add_delivery_time_to_product_title' ), 10, 2 );

		// dont's show product image in product name
		if ( 'on' === get_option( 'german_market_product_images_in_order', 'off' ) ) {
			add_filter( 'woocommerce_order_item_name', array( 'WGM_Product', 'add_thumbnail_to_order' ), 100, 3 );
		}
	}

}
