<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_WC_Invoice_Pdf_Background_Image_Functions' ) ) {
	
	/**
	* Library of background image functions
	*
	* @WP_WC_Invoice_Pdf_Background_Image_Functions
	* @version 3.37
	* @category	Class
	*/
	class WP_WC_Invoice_Pdf_Background_Image_Functions {
		
		/**
		 * Change the default tamplate if background image should only be on first page
		 * 
		 * @since 3.37
		 * @wp-hook wp_wc_invoice_pdf_modify_template
		 * @param String $template
		 * @return template
		 */
		public static function background_only_first_page_modify_template( $template ) {
			$search = '<div class="background-color">[[background]]</div>';
			$replace = '<div class="background-color"></div><div class="background-image-container">[[background]]</div>';
			$template = str_replace( $search, $replace, $template );
			return $template;
		}

		/**
		 * Returns true if calling function is used in a legal text pdf
		 * 
		 * @return Boolean
		 */ 
		public static function is_legal_text_pdf() {

			$is_legal_text_pdf = false;
			$template = apply_filters( 'wp_wc_invoice_pdf_template_invoice_content', '' );
			
			$legal_text_templates = array(
				'terms-and-conditions.php',
				'revocation-policy.php'
			);

			if ( ! empty( $template ) ) {
				foreach ( $legal_text_templates as $legal_text_template ) {
					if ( false !== strpos( $template, $legal_text_template ) ) {
						$is_legal_text_pdf = true;
						break;
					}
				}
			}

			return $is_legal_text_pdf;
		}

		/**
		 * Remove background image in legal text pdfs
		 * 
		 * @since 3.37
		 * @wp-hook wp_wc_invoice_include_background_image_return_value
		 * @param String $markup
		 * @param String $img
		 * @param String $div
		 * @return String
		 */
		public static function background_not_in_legal_text_pdfs( $markup, $img, $div ) {

			if ( self::is_legal_text_pdf() ) {
				$markup = '';
			}
			
			return $markup;
		}

		/**
		 * Remove header content in legal text pdfs
		 * 
		 * @since 3.37
		 * @wp-hook wp_wc_invoice_pdf_header_footer_content_result
		 * @param String $content
		 * @param String $part
		 * @return String
		 */
		public static function no_header_in_legal_texts_content( $content, $part ) {

			if ( 'header' === $part && self::is_legal_text_pdf() ) {
				$content = '';
			}

			return $content;
		}

		/**
		 * Set header height to zero in legal text pdfs
		 * 
		 * @wp-hook wp_wc_invoice_pdf_get_option
		 * @param String $option_value
		 * @param String $option_name
		 * @param String $default
		 * @return String
		 */     
		public static function no_header_in_legal_texts_margin_top( $option_value, $option_name, $default ) {

			if ( self::is_legal_text_pdf() ) {
				if ( 'body_margin_top' === $option_name ) {
					$option_value = WP_WC_Invoice_Pdf_Create_Pdf::convert_to_css_numeric( get_option( 'wp_wc_invoice_pdf_body_margin_top' ) );
				} else if ( 
					'header_height' === $option_name ||
					'header_padding_top' === $option_name ||
					'header_padding_bottom' === $option_name
				) {
					$option_value = 0.0;
				}
			}

			return $option_value;
		}

		/**
		 * Remove footer content in legal text pdfs
		 * 
		 * @since 3.37
		 * @wp-hook wp_wc_invoice_pdf_header_footer_content_result
		 * @param String $content
		 * @param String $part
		 * @return String
		 */
		public static function no_footer_in_legal_texts_content( $content, $part ) {

			if ( 'footer' === $part && self::is_legal_text_pdf() ) {
				$content = '';
			}

			return $content;
		}

		/**
		 * Set footer height to zero in legal text pdfs
		 * 
		 * @wp-hook wp_wc_invoice_pdf_get_option
		 * @param String $option_value
		 * @param String $option_name
		 * @param String $default
		 * @return String
		 */     
		public static function no_footer_in_legal_texts_margin_bottom( $option_value, $option_name, $default ) {

			if ( self::is_legal_text_pdf() ) {
				if ( 'body_margin_bottom' === $option_name ) {
					$option_value = WP_WC_Invoice_Pdf_Create_Pdf::convert_to_css_numeric( get_option( 'wp_wc_invoice_pdf_body_margin_bottom' ) );
				} else if ( 
					'footer_height' === $option_name ||
					'footer_padding_top' === $option_name ||
					'footer_padding_bottom' === $option_name
				) {
					$option_value = 0.0;
				}
			}

			return $option_value;
		}

	} // end class
	
} // end if
