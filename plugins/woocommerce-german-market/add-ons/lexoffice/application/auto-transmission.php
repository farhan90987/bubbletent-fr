<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

$voucher_or_invoice_api = get_option( 'woocommerce_de_lexoffice_voucher_or_invoice', 'voucher' );

if ( 
	( 'voucher' === $voucher_or_invoice_api && 'on' === get_option( 'woocommerce_de_lexoffice_automatic_completed_order', 'off' ) ) ||
	( 'invoice' === $voucher_or_invoice_api )
) {
	add_action( 'woocommerce_order_status_changed', 'lexoffice_woocommerce_status_completed', 10, 3 );
}

if ( get_option( 'woocommerce_de_lexoffice_automatic_refund', 'off' ) == 'on' ) {
	add_action( 'woocommerce_create_refund', 'lexoffice_woocommerce_create_refund', 10, 2 );
}

/**
* Send Voucher to lexoffice if order is marked as completed
*
* @since 	GM 3.7.1
* @version 	3.22.1.3
* @wp-hook 	woocommerce_order_status_completed
* @param 	Integer $order_id
* @return 	void
*/
function lexoffice_woocommerce_status_completed( $order_id, $old_status, $new_status ) {

	$voucher_or_invoice_api = get_option( 'woocommerce_de_lexoffice_voucher_or_invoice', 'voucher' );
	$order = null;
	$do_transmission = false;

	if ( 'voucher' === $voucher_or_invoice_api ) {
		
		$do_transmission = 'completed' === $new_status;
	
	} else if ( 'invoice' === $voucher_or_invoice_api ) {

		$order = wc_get_order( $order_id );

		if ( is_object( $order ) && method_exists( $order, 'get_meta' ) ) {
			
			$has_transmission = $order->get_meta( '_lexoffice_woocomerce_has_transmission_invoice_api' );

			if ( empty( $has_transmission ) ) {

				$allowed_status = get_option( 'woocommerce_de_lexoffice_invoice_autotransmission_status', 'completed' );
					
				if ( ( ! empty( $allowed_status ) ) && ( ! is_array( $allowed_status ) ) ) {
					$allowed_status = array( $allowed_status );
				}

				if ( in_array( $new_status, $allowed_status ) ) {
					$do_transmission = true;
				}
			}
		}
	}

	if ( $do_transmission ) {

		if ( is_null( $order ) ) {
			$order = wc_get_order( $order_id );
		}
		
		if ( is_object( $order ) && method_exists( $order, 'get_meta' ) ) {

			// is bulk transmission scheduled?
			$is_scheduled = $order->get_meta( '_lexoffice_woocomerce_scheduled_for_transmission' );
			if ( ! empty( $is_scheduled ) ) {
				return;
			}
		}

		$api_type = get_option( 'woocommerce_de_lexoffice_voucher_or_invoice', 'voucher' );
		
		if ( 'voucher' === $api_type ) {
			$response = lexoffice_woocomerce_api_send_voucher( $order, false );
		} else if ( 'invoice' === $api_type ) {
			$return_value = German_Market_Lexoffice_Invoice_API::send_order( $order, false );
		}
	}
}

/**
* Send Voucher to lexoffice if refund is created
*
* @since 	GM 3.7.1
* @version 	3.22.1.3
* @wp-hook 	woocommerce_create_refund
* @param 	WC_Order_Refund $refund
* @param 	Array $args
* @return 	void
*/
function lexoffice_woocommerce_create_refund( $refund, $args ) {

	// is bulk transmission scheduled?
	if ( is_object( $refund ) && method_exists( $refund, 'get_meta' ) ) {
		$is_scheduled = $refund->get_meta( '_lexoffice_woocomerce_scheduled_for_transmission' );
		if ( ! empty( $is_scheduled ) ) {
			return;
		}
		
		$api_type = get_option( 'woocommerce_de_lexoffice_voucher_or_invoice', 'voucher' );

		if ( 'voucher' === $api_type ) {
			$return_value = lexoffice_woocommerce_api_send_refund( $refund, false );
		} else if ( 'invoice' === $api_type ) {
			$return_value = German_Market_Lexoffice_Invoice_API_Credit_Note::send_refund( $refund, false );
		}
	}
}

/**
* Before a refund is transmitted to lexoffice => transmit order (if not done yet)
*
* @since 3.22.1.1
* @wp-hook woocommerce_de_lexoffice_api_before_send_refund
* @param WC_Order $order
* @param WC_Order_refund $refund
* @return void
*/
function lexoffice_send_order_before_refund( $order, $refund ) {
	
	$api_type = get_option( 'woocommerce_de_lexoffice_voucher_or_invoice', 'voucher' );
	
	if ( 'voucher' === $api_type ) {
		German_Market_Lexoffice_API_General::send_order_before_refund( $order, $refund );
	} else if ( 'invoice' === $api_type ) {
		German_Market_Lexoffice_Invoice_API_General::send_order_before_refund_invoice( $order, $refund );
	}
}
