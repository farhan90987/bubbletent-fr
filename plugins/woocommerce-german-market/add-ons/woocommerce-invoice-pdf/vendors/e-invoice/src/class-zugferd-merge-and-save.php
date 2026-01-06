<?php

namespace MarketPress\German_Market\E_Invoice;

use MarketPress\German_Market\horstoeko\zugferd\ZugferdDocumentPdfMerger;

class E_Invoice_Merge_And_Save extends E_Invoice_Order_Conditions {

	private $saved_temp = false;

	/**
	 * Construct, create merged pdf with e-invoice for different download behaviours
	 * 
	 * @param WC_Order $order
	 * @param String $pdf_stream,
	 * @param String $filename
	 * @param String $download_behaviour
	 * @param String|null $save_path
	 * 
	 * @return void
	 */ 
	public function __construct( $order, $pdf_stream, $filename, $download_behaviour, $save_path = null ) {

		if ( ! ( $this->merge_invcoice_and_xml( $order ) && $this->order_needs_e_invoice( $order ) ) ) {
			return;
		}

		$e_invoice = new E_Invoice_Order( $order );
		$documentPdfBuilder = new ZugferdDocumentPdfMerger( $e_invoice->get_xml(), $pdf_stream );
		$new_pdf_document = $documentPdfBuilder->generateDocument();

		if ( 'browser_download' === $download_behaviour || 'browser_inline' === $download_behaviour ) {

			$content_disposition = 'browser_download' === $download_behaviour ? 'attachment' : 'inline';

  			header( 'Content-Type: application/pdf');
			header( 'Content-disposition: ' . $content_disposition . '; filename="' . $filename . '"' );

  			echo $new_pdf_document->downloadString( $filename );
  			exit();

		} else if ( 'save_temp' === $download_behaviour ) {

			$new_pdf_document->saveDocument( $save_path . $filename );
			$this->saved_temp = true;
		}
	}

	/**
	 * Returns whether a file has been saved under $saved_path
	 * 
	 * @return Boolean
	 */
	public function get_is_saved_temp() {
		return $this->saved_temp;
	}
}
