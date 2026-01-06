<?php

namespace MarketPress\German_Market\E_Invoice;

use MarketPress\German_Market\horstoeko\zugferd\ZugferdDocumentBuilder;
use MarketPress\German_Market\horstoeko\zugferd\ZugferdProfiles;
use MarketPress\German_Market\horstoeko\zugferd\codelists\ZugferdPaymentMeans;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

class E_Invoice_Order extends E_Invoice_General {

	/**
	* Construct
	* 
	* @return void
	*/
	public function __construct( $order, $is_frontend = false ) {
		parent::__construct( $order, $is_frontend );
	}

	/**
	 * Build zugferd document
	 * 
	 * @return void
	 */
	public function build_zugferd_document() {
		$this->set_document_information();
		$this->set_buyer();
		$this->set_seller();
		$this->set_positions();
		$this->set_doucment_tax();
		$this->set_document_summation();
		$this->set_notes();
		$this->set_shipping_to();
		$this->set_payment();
		$this->set_delivery_date();
	}

	/**
	 * Set notes to the document
	 * 
	 * @return void
	 */
	public function set_notes() {

		$notes = parent::get_notes();

		if ( 'order' === $this->order_type ) {
				
			// invoice title
			$subject = get_option( 'wp_wc_invoice_pdf_invoice_start_subject', __( 'Invoice for order {{order-number}} ({{order-date}})', 'woocommerce-german-market' ) );
			$subject_placeholders = apply_filters( 'wp_wc_invoice_pdf_placeholders', array( 'order-number' => __( 'Order Number', 'woocommerce-german-market' ), 'order-date' => __( 'Order Date', 'woocommerce-german-market' ) ) );
			$search = array();
			$replace = array();
			$order_number = $this->order->get_order_number();
			$order_date = $this->order->get_date_created()->format( 'Y-m-d' );
			$order_date_formated = date_i18n( wc_date_format(), strtotime( $order_date ) );

			foreach( $subject_placeholders as $placeholder_key => $placeholder_value ) {
				$search[] = '{{' . $placeholder_key . '}}';
				if ( $placeholder_key == 'order-number' ) {
					$replace[] = $order_number;
				} else if ( $placeholder_key == 'order-date' ) {
					$replace[] = $order_date_formated;
				} else {
					$replace[] = apply_filters( 'wp_wc_invoice_pdf_placeholder_' . $placeholder_key, $placeholder_value, $placeholder_key, $this->order );
				}
			}
			$subject = str_replace( $search, $replace , $subject );
			$notes[ 'title' ] = array(
				'text'			=> $subject,
				'subject_code'	=> 'AAI', // General Information
			);

			// customer note (from customer)
			$customer_note = $this->order->get_customer_note();
			if ( ! empty( $customer_note ) ) {
				$notes[ 'customer_note' ] = array(
					'text'			=> __( 'Customer provided note:', 'woocommerce-german-market' ) . PHP_EOL . $customer_note,
					'subject_code'	=> 'AAI', // General Information
				);
			}

			// customer order notes (from shop admin for customer)
			$customer_order_note_texts = array();
			foreach ( $this->order->get_customer_order_notes() as $customer_order_note ) {
				if ( isset( $customer_order_note->comment_content ) && ( ! empty( $customer_order_note->comment_content ) ) ) {
					$customer_order_note_texts[] = $customer_order_note->comment_content;
				}
			}

			if ( ! empty( $customer_order_note_texts ) ) {
				$customer_order_note_text = implode( PHP_EOL . PHP_EOL, $customer_order_note_texts );
				$notes[ 'customer_order_note' ] = array(
					'text'			=> $customer_order_note_text,
					'subject_code'	=> 'SUR', // Notes from the seller
				);
			}
		} else if ( 'refund' === $this->order_type ) {

			$subject_lines = array(
				'wp_wc_invoice_pdf_refund_start_subject_big' => __( 'Refund {{refund-id}}', 'woocommerce-german-market' ),
				'wp_wc_invoice_pdf_refund_start_subject_small' => __( 'For order {{order-number}}', 'woocommerce-german-market' )
			);


			$subject_placeholders = apply_filters( 'wp_wc_invoice_pdf_placeholders', array( 'refund-id' => __( 'Refund ID', 'woocommerce-german-market' ), 'order-number' => __( 'Order Number', 'woocommerce-german-market' ) ) );

			foreach ( $subject_lines as $subject_line_key => $subject_line ) {

				$subject_lines[ $subject_line_key ] = get_option( $subject_line_key, $subject_line );
				$search = array();
				$replace = array();
				foreach( $subject_placeholders as $placeholder_key => $placeholder_value ) {
					$search[] = '{{' . $placeholder_key . '}}';
					if ( $placeholder_key == 'order-number' ) {
						$parent = wc_get_order( $this->order->get_parent_id() );
						$replace[] = $parent->get_order_number();
					} else if ( $placeholder_key == 'refund-id' ) {
						$replace[] = $this->order->get_id();
					} else {
						$replace[] = apply_filters( 'wp_wc_invoice_pdf_placeholder_' . $placeholder_key, $placeholder_value, $placeholder_key, $order );
					}
				}

				$subject_lines[ $subject_line_key ] = str_replace( $search, $replace , $subject_lines[ $subject_line_key ] );
			}

			$notes[ 'title' ] = array(
				'text'			=> implode( ' ', $subject_lines ),
				'subject_code'	=> 'AAI', // General Information
			);

		}

		$notes = apply_filters( 'german_market_zugferd_order_notes', $notes, $this->order );

		foreach ( $notes as $note ) {
			if ( isset( $note[ 'text' ] ) && ( ! empty( $note[ 'text' ] ) ) ) {
				$this->zugferd_document->addDocumentNote( 
					esc_attr( strip_tags( $note[ 'text' ] ) ), 
					null, // contentCode
					isset( $note[ 'subject_code' ] ) ? $note[ 'subject_code' ] : null // subjectCode Codeliste UNTDID 4451
				);
			}
		}
	}
}
