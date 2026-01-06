<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

/**
* init actions and hooks needed for the edit-shop screen
*
* wp-hook current_screen
* @return void
*/
function lexoffice_woocommerce_edit_shop_order() {

	if ( function_exists( 'get_current_screen' ) ) {

		if ( WGM_Hpos::is_edit_shop_order_screen() || get_current_screen()->id == 'woocommerce_page_wgm-refunds' ) {

			// load styles and scripts, localizing script
			add_action( 'admin_enqueue_scripts', 'lexoffice_woocommerce_edit_shop_order_styles_and_scripts' );

			// add icon
			add_action( 'woocommerce_admin_order_actions_end', 'lexoffice_woocommerce_edit_shop_order_icon' );

			// add icon for refund;
			add_filter( 'wgm_refunds_actions', 'lexoffice_woocommerce_edit_refund_icon', 10, 2 );

			if ( 'invoice' === get_option( 'woocommerce_de_lexoffice_voucher_or_invoice', 'voucher' ) && 'off' === get_option( 'woocommerce_de_lexoffice_invoice_api_draft_mode', 'off' ) ) {
				add_action( 'woocommerce_admin_order_actions_end', array( 'German_Market_Lexoffice_Edit_Shop_Order', 'shop_order_icon_invoice_download' ) );
				add_action( 'woocommerce_order_actions_end', array( 'German_Market_Lexoffice_Edit_Shop_Order', 'oder_button_download' ) );
			}

			// add actions, filters or remove them
			do_action( 'lexoffice_woocommerce_edit_shop_order_after_init' );

		}
	}
}

/**
 * Legacy function
 * See erman_Market_Lexoffice_Edit_Shop_Order::styles_and_scripts()
 * 
 * @return void
 */
function lexoffice_woocommerce_edit_shop_order_styles_and_scripts() {
	German_Market_Lexoffice_Edit_Shop_Order::styles_and_scripts();
}

/**
 * Legacy function
 * See erman_Market_Lexoffice_Edit_Shop_Order::edit_shop_order_icon( $order) 
 * 
 * @param $order
 * @return void
 */
function lexoffice_woocommerce_edit_shop_order_icon( $order ) {
	German_Market_Lexoffice_Edit_Shop_Order::order_icon( $order );
}

/**
 * Legacy function
 * See erman_Market_Lexoffice_Edit_Shop_Order::edit_refund_icon( $actions, $refund) 
 * 
 * @param $Array
 * @param WC_Order_Refund $refund
 * @return Array
 */
function lexoffice_woocommerce_edit_refund_icon( $actions, $refund ) {
	return German_Market_Lexoffice_Edit_Shop_Order::refund_icon( $actions, $refund );
}
