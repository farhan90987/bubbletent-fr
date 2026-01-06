<?php

namespace MarketPress\German_Market\E_Invoice;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

class E_Invoice_Email_Attachment {

	/**
	* Add XML as an attachement to chosen e-mails
	*
	* @hook woocommerce_email_attachments
	* @param Array $attachments
	* @param String $status
	* @param WC_Order $order
	* @return Array
	*/
	public static function add_attachment( $attachments, $status, $order ) {

		if ( ! ( is_object( $order ) && method_exists( $order, 'get_meta' ) ) ) {
			return $attachments;
		}

		$add_attachment = false;

		$refund_id = $order->get_meta( '_german_market_e_invoice_refund_id' );
		if ( ! empty( $refund_id ) ) {
			$used_order = wc_get_order( $refund_id );
			$add_attachment = true;
			$order->delete_meta_data( '_german_market_e_invoice_refund_id' );
			$order->save_meta_data();
		} else {
			$used_order = $order;
		}

		// check if we need to add the invoice
		$selected_mails = get_option( 'german_market_einvoice_recipients_xml_emails', array() );
		if ( in_array( $status, $selected_mails ) ) {
			$add_attachment = true;
		}

		if ( ! $add_attachment ) {
			return $attachments;
		}

		$e_invoice_order_conditions = new E_Invoice_Order_Conditions();
		if ( ! $e_invoice_order_conditions->order_needs_e_invoice( $order ) ) {
			return $attachments;	
		}

		$is_frontend = true;
		$e_invoice = new E_Invoice_Order( $used_order, $is_frontend );
		$attachments[] = $e_invoice->save_xml_temp_and_get_path();

		return $attachments;
	}

	/**
	* Ttrigger refunded order to save refund id as temporally post meta in order
	*
	* @hook woocommerce_order_fully_refunded_notification
	* @hook woocommerce_order_partially_refunded_notification
	* @param int $order_id
 	* @param int $refund_id
	* @return void
	*/
	public static function trigger_refund( $order_id, $refund_id ) {
		$order = wc_get_order( $order_id );
		$order->update_meta_data( '_german_market_e_invoice_refund_id', $refund_id );
		$order->save_meta_data();
	}
}
