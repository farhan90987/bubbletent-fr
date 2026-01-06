<?php

namespace MarketPress\German_Market\E_Invoice;

class E_Invoice_Order_Conditions{

	/**
	 * Test if an order needs e-invoicing
	 * Decided by admins settings
	 * 
	 * @param WC_Order
	 * @return Boolean
	 */
	public function order_needs_e_invoice( $order ) {
		
		$order_needs_e_invoice = false;
		
		if ( 'test' !== $order && is_object( $order ) ) {
			
			if ( 'shop_order_refund' === $order->get_type() ) {
				$check_order = wc_get_order( $order->get_parent_id() );
			} else {
				$check_order = $order;
			}

			$option = get_option( 'german_market_einvoice_recipients', 'base_country_companies' );

			if ( 'all' === $option ) {
				$order_needs_e_invoice = true;
			} else {
				
				/**
				* Filter billing country that is used to determine whether to use ZUGFeRD
				* 
				* @since 1.0
				* @param Boolean
				* @param WC_Order $order
				*/
				$billing_country = apply_filters( 'german_market_zugferd_order_needs_invoice_billing_country', $check_order->get_billing_country(), $order );
				
				/**
				* Filter base country to compare with billing country
				* 
				* @since 1.0
				* @param String 'de'
				*/
				$compare_with_base_country = apply_filters( 'german_market_zugferd_order_needs_invoice_base_country', 'DE' );
				
				if ( $compare_with_base_country === $billing_country ) {
					if ( 'base_country' === $option ) {
						$order_needs_e_invoice = true;
					} else if ( 'base_country_companies' === $option ) {
						$order_needs_e_invoice = $this->order_is_company( $check_order );
					}
				}
			}
		}

		return $order_needs_e_invoice;
	}

	/**
	 * Returns if ZUGFeRD is activated by option
	 * 
	 * @param WC_Order $order
	 * @return Boolean
	 */
	public function merge_invcoice_and_xml( $order ) {
		
		/**
		* Filter if ZUGFeRD should be used
		* 
		* @since 1.0
		* @param Boolean
		* @param WC_Order $order
		*/
		return apply_filters( 'german_market_zugferd_activated', 'on' === get_option( 'german_market_einvoice_send_zugferd_invoices', 'off' ), $order );
	}

	/**
	 * Is this invoice for a company
	 * 
	 * @param WC_Order $order
	 * @return Boolean
	 */
	public function order_is_company( $order ) {

		$is_company = false;

		$billing_company = $order->get_billing_company();
		$vat_number = $order->get_meta( 'billing_vat' );

		if ( ( ! empty( $billing_company ) ) || ( ! empty( $vat_number ) ) ) {
			$is_company = true;
		}

		/**
		* Filter if order is for a company
		* 
		* @since 1.0
		* @param Boolean
		* @param WC_Order $order
		*/
		return apply_filters( 'german_market_zugferd_order_is_for_company', $is_company, $order );
	}
}
