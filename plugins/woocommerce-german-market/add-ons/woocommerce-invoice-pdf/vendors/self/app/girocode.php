<?php

use MarketPress\German_Market\SepaQr\Data;
use MarketPress\German_Market\chillerlan\QRCode\QRCode;
use MarketPress\German_Market\chillerlan\QRCode\QROptions;
use MarketPress\German_Market\chillerlan\QRCode\Common\EccLevel;
use MarketPress\German_Market\chillerlan\QRCode\Common\Version;
use MarketPress\German_Market\chillerlan\QRCode\Data\QRMatrix;
use MarketPress\German_Market\chillerlan\QRCode\Output\QROutputInterface;
use MarketPress\German_Market\chillerlan\QRCode\Output\QRImagick;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_WC_Invoice_Pdf_Girocode' ) ) {

	/**
	* girocode creation
	*
	* @WP_WC_Invoice_Pdf_Girocode
	* @version 1.0.0
	* @category	Class
	*/
	class WP_WC_Invoice_Pdf_Girocode {

		public $order;
		
		/**
		* construct
		* 
		* @access public
		* @param WC_Order | null $order
		* @return void
		*/
		public function __construct( $order = null ) {
			$this->order = $order;
		}

		/**
		* get payment data
		* 
		* @access public
		* @return Array / Object
		*/
		public function get_payment_data() {

			if ( is_object( $this->order ) && method_exists( $this->order, 'get_order_number' ) ) {
				$total = $this->order->get_total();
			} else {
				$total = 0.01;
			}

			try {
			
				$payment_data = Data::create()
					  ->setName( get_option( 'wp_wc_invoice_pdf_girocode_remit_recipient_name', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) ) )
					  ->setIban( get_option( 'wp_wc_invoice_pdf_girocode_remit_recipient_iban', '' ) )
					  ->setAmount( $total )
					  ->setRemittanceText( $this->replace_placeholders( get_option( 'wp_wc_invoice_pdf_girocode_remit_remittance_text', __( 'Order {{order-number}}', 'woocommerce-german-market' ) ), $this->order ) );

				$bic = get_option( 'wp_wc_invoice_pdf_girocode_remit_recipient_bic', '' );
				if ( ! empty( $bic ) ) {
					$payment_data->setBic( $bic );
				}

			} catch ( Exception $e ) {
				$this->log_error( $e );
				$payment_data = array();
			}

			return $payment_data;
		}

		/**
		* get qr-code matrix
		* 
		* @access public
		* @return Array
		*/
		public function get_matrix() {
			
			$matrix 		= array();
			$payment_data 	= $this->get_payment_data();

			if ( ! empty( $payment_data ) ) {
			
				try {
					
					$qr_options = new QROptions;
					$qr_options->outputType = QROutputInterface::MARKUP_HTML;
					$qr_options->eccLevel = $this->get_ecc_level();
					$qr_options->addQuietzone = false;
					$qr_code = new QRCode( $qr_options );
					$rendered_matrix = $qr_code->render( $payment_data );
					$matrix = $qr_code->getMatrix( $payment_data );
				
				} catch ( Exception $e ) {
					$this->log_error( $e );
				}
			}

			return $matrix;
		}

		/**
		* logs an error in wc log
		* 
		* @access public
		* @return void
		*/
		private function log_error( $e ) {
			$logger 	= wc_get_logger();
			$context 	= array( 'source' => 'german-market-girocode' );
			
			$message = '';

			if ( is_object( $this->order ) && method_exists( $this->order, 'get_id' ) ) {
				$message .= 'Order: ' . $this->order->get_id();
			}

			if ( is_object( $e ) && method_exists( $e, 'getMessage' ) ) {
				if ( ! empty( $message ) ) {
					$message .= ', ';
				}
				$message .= $e->getMessage();

			}

			$logger->info( $message, $context );
		}

		/**
		* get ecc level by setting
		* 
		* @access public
		* @return String
		*/
		public function get_ecc_level() {

			$level = EccLevel::L;
			$ecc_level = get_option( 'wp_wc_invoice_pdf_girocode_ecc_level', 'L' );
			
			if ( 'M' === $ecc_level ) {
				$level = EccLevel::M;
			} else if ( 'Q' === $ecc_level ) {
				$level = EccLevel::Q;
			} else if ( 'H' === $ecc_level ) {
				$level = EccLevel::H;
			}

			return $level;
		}

		/**
		* returns qr code as image
		* 
		* @access public
		* @param String $format
		* @return String
		*/
		public function get_qr_code_image( $format = 'svg' ) {

			$img 			= '';

			$complete_size 	= get_option( 'wp_wc_invoice_pdf_girocode_width', 2 );
			$unit 			= get_option( 'wp_wc_invoice_pdf_user_unit', 'cm' );
			$dark_option	= get_option( 'wp_wc_invoice_pdf_girocode_dark_color', '#000000' );
			$light_option	= get_option( 'wp_wc_invoice_pdf_girocode_bright_color', '#ffffff' );
			$payment_data 	= $this->get_payment_data();

			if ( ! empty( $payment_data ) ) {

				$qr_options = new QROptions;
				$qr_options->eccLevel = $this->get_ecc_level();
				$qr_options->addQuietzone = false;
				$qr_options->bgColor = self::hex_option_to_rgb_color( $light_option );
				$qr_options->outputBase64 = true;
				$qr_options->quality = 90;
				$qr_options->scale = 10;
				$qr_options->imageTransparent = false;
				$qr_options->drawLightModules    = true;
				$qr_options->drawCircularModules = false;

				$qr_options->keepAsSquare = array(
						QRMatrix::M_FINDER_DARK,
						QRMatrix::M_FINDER_DOT,
						QRMatrix::M_ALIGNMENT_DARK,
					);

				if ( 'jpg' === $format ) {
					
					$qr_options->outputType = QROutputInterface::GDIMAGE_JPG;
					$dark = self::hex_option_to_rgb_color( $dark_option );
					$light = self::hex_option_to_rgb_color( $light_option );

				} else {
					
					$qr_options->outputType = QROutputInterface::MARKUP_SVG;
					$dark = $dark_option;
					$light = $light_option;
				}

				$qr_options->moduleValues = array(
					// finder
					QRMatrix::M_FINDER_DARK    => $dark, // dark (true)
					QRMatrix::M_FINDER_DOT     => $dark, // finder dot, dark (true)
					QRMatrix::M_FINDER         => $light, // light (false)
					// alignment
					QRMatrix::M_ALIGNMENT_DARK => $dark,
					QRMatrix::M_ALIGNMENT      => $light,
					// timing
					QRMatrix::M_TIMING_DARK    => $dark,
					QRMatrix::M_TIMING         => $light,
					// format
					QRMatrix::M_FORMAT_DARK    => $dark,
					QRMatrix::M_FORMAT         => $light,
					// version
					QRMatrix::M_VERSION_DARK   => $dark,
					QRMatrix::M_VERSION        => $light,
					// data
					QRMatrix::M_DATA_DARK      => $dark,
					QRMatrix::M_DATA           => $light,
					// darkmodule
					QRMatrix::M_DARKMODULE     => $dark,
					// separator
					QRMatrix::M_SEPARATOR      => $light,
					// quietzone
					QRMatrix::M_QUIETZONE      => $light,
				);

				try {

					$qr_code 	= new QRCode( $qr_options );	
					$img = '<img style="width: ' . $complete_size . $unit .';" src="' . $qr_code->render( $payment_data ) . '" />';

				} catch ( Exception $e ) {
					$this->log_error( $e );
				}

			}

			return $img;
		}
		
		/**
		 * Convert hex color to rgb array
		 * 
		 * @param String $option
		 * @return String
		 */
		public function hex_option_to_rgb_color( $option ) {

			// convert color: hex to rgb
		   $hex = trim( str_replace( "#", "", $option ) );
		   if ( strlen ( $hex ) == 3 ) {
			  $r = hexdec( substr( $hex ,0 ,1 ) . substr( $hex ,0 ,1 ) );
			  $g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1 ,1 ) );
			  $b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
		   } else {
			  $r = hexdec( substr( $hex, 0, 2 ) );
			  $g = hexdec( substr( $hex, 2, 2 ) );
			  $b = hexdec( substr( $hex, 4, 2 ) );
		   }
		   $font_color_rgb = array( $r, $g , $b );

		   return $font_color_rgb;
		}

		/**
		* returns qr code as html markup
		* 
		* @access public
		* @param String $format
		* @return String
		*/
		public function get_matrix_markup() {

			$matrix 		= $this->get_matrix();
			$qr_code = '';

			if ( is_object( $matrix ) && method_exists( $matrix, 'size' ) ) {
				$matrix_size 	= $matrix->size();

				if ( $matrix_size > 0 ) {
					$complete_size 	= get_option( 'wp_wc_invoice_pdf_girocode_width', 2 );
					$unit 			= get_option( 'wp_wc_invoice_pdf_user_unit', 'cm' );
					$one_piece_size = round( $complete_size / $matrix_size, 2 );
					$complete_size  = $one_piece_size * $matrix_size;
					$dark_color 	= get_option( 'wp_wc_invoice_pdf_girocode_dark_color', '#000000' );
					$bright_color 	= get_option( 'wp_wc_invoice_pdf_girocode_bright_color', '#ffffff' );

					$div_row 		= '<div class="qr-code-row" style="width: ' . $complete_size . $unit .'; line-height: 0; font-size: 0;">';
					$dark_span  	= '<span style="background: ' . $dark_color . '; display: inline-block; width: ' . $one_piece_size  . $unit . '; font-size: inherit; height: ' . $one_piece_size . $unit . ';">&nbsp;</span>';
					$bright_span 	= '<span style="background: ' . $bright_color . '; display: inline-block; width: ' . $one_piece_size . $unit . '; font-size: inherit; height: ' . $one_piece_size . $unit . ';">&nbsp;</span>';
				}

				$i=0;
				foreach ( $matrix->matrix() as $y => $row ){
					$qr_code .= $div_row;
					foreach ( $row as $x => $module ){

						// get a module's value
						$value = $module;

						// or via the matrix's getter method
						$value = $matrix->get( $x, $y );
						$i++;
						// boolean check a module
						if ( $matrix->check( $x, $y ) ){ // if($module >> 8 > 0)
							$qr_code .= $dark_span;
						} else {
							// do other stuff, the module is light
							$qr_code .= $bright_span ;
						}

					}
					$qr_code .= '</div>';
				}
			}

			return $qr_code;
		}

		/**
		* get template file of girocode
		* 
		* @access public
		* @return String
		*/
		public function get_template_file() {
			
			$plugin_template_file = untrailingslashit( plugin_dir_path( Woocommerce_Invoice_Pdf::$plugin_filename ) ) . DIRECTORY_SEPARATOR . 'vendors' . DIRECTORY_SEPARATOR . 'self' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'girocode.php';
			$theme_template_file = get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'woocommerce-invoice-pdf' . DIRECTORY_SEPARATOR . 'girocode.php';
			
			$template_file = $plugin_template_file;

			if ( file_exists( $theme_template_file ) ) {
				$template_file = $theme_template_file;
			}

			return apply_filters( 'wp_wc_invoice_pdf_girocode_template_file', $template_file, $plugin_template_file, $theme_template_file );
		}

		/**
		* Replace Placholders
		* 
		* @access public
		* @param Strint $text
		* @param WC_Order $order
		* @return $text
		*/
		public function replace_placeholders( $text, $order ) {

			$can_use_order = is_object( $order ) && method_exists( $order, 'get_billing_first_name' );
			$placeholders = array(
				'{{first-name}}'	=> $can_use_order ? $order->get_billing_first_name() : __( 'John', 'woocommerce-german-market' ),
				'{{last-name}}'		=> $can_use_order ? $order->get_billing_last_name() : __( 'Doe', 'woocommerce-german-market' ),
				'{{order-number}}'	=> $can_use_order ? $order->get_order_number() : rand( 1000, 99999 ),
				'{{order-total}}'	=> $can_use_order ? strip_tags( wc_price( $order->get_total(), array( 'currency' => $order->get_currency() ) ) ) : wc_price( 0.01 ) 
			);

			$placeholders = apply_filters( 'wp_wc_invoice_pdf_girocode_placeholders', $placeholders, $order );

			$text = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $text );

			return apply_filters( 'wp_wc_invoice_pdf_girocode_placeholders_text', $text, $order );
		}

		/**
		* get markup and include template file
		* 
		* @access public
		* @param WC_Order $order
		* @return void
		*/
		public static function make_markup( $order = null ) {

			$qr_code 			= new self( $order );

			$text_raw 			= strip_tags( get_option( 'wp_wc_invoice_pdf_girocode_text', WGM_Helper::get_default_text_next_to_qr_code() ), '<small><strong><em><u><p><span><table><tr><td><th><br><h1><h2><h3><ul><li><ol>' );
			$text 				= nl2br( $qr_code->replace_placeholders( $text_raw, $order ) );

			$font 				= get_option( 'wp_wc_invoice_pdf_girocode_text_next_to_qr_code_font', 'Helvetica' );
			$font_size 			= get_option( 'wp_wc_invoice_pdf_girocode_text_next_to_qr_code_font_size', 10 );
			$font_color 		= get_option( 'wp_wc_invoice_pdf_girocode_text_next_to_qr_code_color', '#fff' );
			$text_align 		= get_option( 'wp_wc_invoice_pdf_girocode_text_next_to_qr_code_text_align', 'left' );
			$vertical_align 	= get_option( 'wp_wc_invoice_pdf_girocode_text_next_to_qr_code_text_vertical_align', 'top' );

			$girocode_alignment = get_option( 'wp_wc_invoice_pdf_girocode_alignment', 'left' );

			$cell_padding		= get_option( 'wp_wc_invoice_pdf_table_cell_padding', 5 );
			$padding_left 		= 'right' === $girocode_alignment ? '0' : $cell_padding . 'px';
			$padding_right 		= 'right' === $girocode_alignment ? $cell_padding . 'px' : '0';
			$style 				= sprintf( 'font-family: %s; font-size: %spt; color: %s; text-align: %s; vertical-align: %s; padding-left: %s; padding-right: %s; box-sizing: border-box;', $font, $font_size, $font_color, $text_align, $vertical_align, $padding_left, $padding_right );
			
			$text_td 			= '<td style="' . $style . '">' . $text . '</td>';
			$first_td 			= 'right' === $girocode_alignment ? $text_td : '';
			$last_td 			=  empty( $first_td ) ? $text_td : '';
			$unit 				= get_option( 'wp_wc_invoice_pdf_user_unit', 'cm' );
			$complete_size 		= get_option( 'wp_wc_invoice_pdf_girocode_width', 2 );
			$format 			= get_option( 'wp_wc_invoice_pdf_girocode_format', 'svg' );
			$margin 			= get_option( 'wp_wc_invoice_pdf_girocode_margin', 0.15 );
			$border_width		= get_option( 'wp_wc_invoice_pdf_girocode_border_width', 1 );
			$border_color 		= get_option( 'wp_wc_invoice_pdf_girocode_border_color', '#000' );

			$td_width			= floatval( $complete_size ) + 2 * floatval( $margin );

			$text_under_qr_code = get_option( 'wp_wc_invoice_pdf_girocode_text_under', __( 'Girocode', 'woocommerce-german-market' ) );
			$text_under_font 	= get_option( 'wp_wc_invoice_pdf_girocode_text_under_qr_code_font', 'Helvetica' );
			$text_under_size 	= get_option( 'wp_wc_invoice_pdf_girocode_text_under_qr_code_font_size', 8 );
			$text_under_align 	= get_option( 'wp_wc_invoice_pdf_girocode_text_under_qr_code_text_align', 'center' );
			$text_under_color 	= get_option( 'wp_wc_invoice_pdf_girocode_text_under_qr_code_color', '#000' );
			$text_under_style 	= sprintf( 'font-family: %s; font-size: %spt; color: %s; text-align: %s;', $text_under_font, $text_under_size, $text_under_color, $text_under_align, $td_width . $unit );
			
			$qr_code_markup	= '';
			
			if ( 'html' === $format ) {
				$qr_code_markup = $qr_code->get_matrix_markup();
			} else {
				$qr_code_markup = $qr_code->get_qr_code_image( $format );
			}

			if ( ! empty( $qr_code_markup ) ) {
				include( $qr_code->get_template_file() );
			}
		}

	} // end class

} // end if
