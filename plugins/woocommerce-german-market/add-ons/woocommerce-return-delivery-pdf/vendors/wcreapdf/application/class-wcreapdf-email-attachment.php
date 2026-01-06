<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCREAPDF_Email_Attachment' ) ) {
	
	/**
	* Adds the pdf as an attachment to e-mails
	*
	* @class WCREAPDF_Email_Attachment
	* @version 1.0
	* @category	Class
	*/
	class WCREAPDF_Email_Attachment {
		
		/**
		* Adds the pdf as an attachement to chosen customer e-mails
		*
		* @since 0.0.1
		* @access public
		* @static
		* @hook woocommerce_email_attachments
		* @return array $attachments
		*/
		public static function add_attachment( $attachments, $status , $order ) {
			
			if ( WCREAPDF_Helper::check_if_needs_attachement( $order ) ) {

				$selected_mails = get_option( WCREAPDF_Helper::get_wcreapdf_optionname( 'return_note_emails' ), WCREAPDF_Backend_Options_WGM::get_return_note_email_attachment_default() );

				if ( in_array( $status, $selected_mails ) && apply_filters( 'wcreapdf_pdf_return_note_email_attachments_allowed_order', true, $status, $order ) ) {
					
					$directory_name = WCREAPDF_Pdf::create_pdf( $order );
					do_action( 'wcreapdf_pdf_before_output', 'retoure', $order, false );
					$attachments[] = WCREAPDF_TEMP_DIR . 'pdf' .  DIRECTORY_SEPARATOR . $directory_name . DIRECTORY_SEPARATOR . get_option( WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_file_name' ), __( 'Retoure', 'woocommerce-german-market' ) ) . '.pdf';

				}

				$selected_mails_delivery_note = get_option( WCREAPDF_Helper::get_wcreapdf_optionname( 'delivery_note_emails' ), array() );

				if ( in_array( $status, $selected_mails_delivery_note ) && apply_filters( 'wcreapdf_pdf_delivery_note_email_attachments_allowed_order', true, $status, $order ) ) {
					$directory_name = WCREAPDF_Pdf_Delivery::create_pdf( $order );
	            	do_action( 'wcreapdf_pdf_before_output', 'delivery', $order, false );
	            	$attachments[]     = WCREAPDF_TEMP_DIR . 'pdf' .  DIRECTORY_SEPARATOR . $directory_name . DIRECTORY_SEPARATOR . get_option( WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_file_name_delivery' ), __( 'Delivery-Note', 'woocommerce-german-market' ) ) . '.pdf';
	            }
			}

			return $attachments;
		}
	} // end class
	
} // end if
