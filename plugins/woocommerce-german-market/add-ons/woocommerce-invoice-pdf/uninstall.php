<?php
/**
 * WooCommerce Invoice PDF - Uninstall
 *
 * Uninstalling Options
 */

if ( ! ( defined( 'WGM_UNINSTALL_ADD_ONS' ) || defined( 'WP_UNINSTALL_PLUGIN' ) ) ) {
	exit;
}

$all_wordpress_options = wp_load_alloptions();

foreach ( $all_wordpress_options as $option_key => $option_value ) {
	
	if ( substr( $option_key, 0, 18 ) == 'wp_wc_invoice_pdf_' ) {
		delete_option( $option_key );
	}

}

require_once( 'vendors' . DIRECTORY_SEPARATOR . 'self' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'order-meta.php' );

if ( method_exists( 'WP_WC_Invoice_Pdf_Order_Meta', 'drop_table' ) ) {
	WP_WC_Invoice_Pdf_Order_Meta::drop_table();
}
