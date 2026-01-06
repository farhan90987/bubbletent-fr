<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_WC_Invoice_Pdf_Cancellation_Stamp' ) ) {
	
	/**
	* Library of background image functions
	*
	* @WP_WC_Invoice_Pdf_Cancellation_Stamp
	* @version 3.40
	* @category	Class
	*/
	class WP_WC_Invoice_Pdf_Cancellation_Stamp {

		public static function add_cancellation_stamp( $html, $args ) {

			if ( 
				
				(
					isset( $args[ 'order' ] ) && 
					( ! isset( $args[ 'refund' ] ) ) && 
					is_object( $args[ 'order' ] ) && 
					method_exists( $args[ 'order' ], 'get_status' ) && 
					( 'cancelled' === $args[ 'order' ]->get_status() )
				) ||
				
				(
					isset( $args[ 'order' ] ) && 
					'test' === $args[ 'order' ] &&
					isset( $args[ 'subtab' ] ) && 
					'cencellation_stamp_content' === $args[ 'subtab' ]
				)

			 ) {
						
				$stamp_text = get_option( 'wp_wc_invoice_pdf_cancel_stamp_text', _x( 'CANCELLED', 'stamp text in cancelled invoice', 'woocommerce-german-market' ),	 );
				$horizontal_shift = floatval( get_option( 'wp_wc_invoice_pdf_cancel_stamp_horizontal_shift', 0 ) );
				$vertical_shift = floatval( get_option( 'wp_wc_invoice_pdf_cancel_stamp_vertical_shift', 0 ) );
				$position_top = WP_WC_Invoice_Pdf_Create_Pdf::convert_to_css_numeric( get_option( 'wp_wc_invoice_pdf_body_margin_top' ) ) + 1 + $vertical_shift;
				$position_left = WP_WC_Invoice_Pdf_Create_Pdf::convert_to_css_numeric( get_option( 'wp_wc_invoice_pdf_body_margin_left' ) ) + 5 + $horizontal_shift;
				$font_size = floatval( get_option( 'wp_wc_invoice_pdf_cancel_stamp_font_size', 1.5 ) );
				$font_family = get_option( 'wp_wc_invoice_pdf_cancel_stamp_font', 'Helvetica' );
				$unit = get_option( 'wp_wc_invoice_pdf_user_unit', 'cm' );
				$color = get_option( 'wp_wc_invoice_pdf_cancel_stamp_color', '#ff0000' );
				$layout = get_option( 'wp_wc_invoice_pdf_cancel_stamp_layout', 'rectangle' );
				$border_size = get_option( 'wp_wc_invoice_pdf_cancel_stamp_border_size', 0.1 );
				$padding = $font_size / 3.0;
				$border_type = get_option( 'wp_wc_invoice_pdf_cancel_stamp_border_style', 'double' );
				$border_radius = get_option( 'wp_wc_invoice_pdf_cancel_stamp_border_radius', 1.5 ) . $unit;
				
				$cancelled = sprintf(
					
					'<div
						class ="cancelled" 
						style = "
							position: fixed;
							z-index: 999999;
							font-family: %8$s;
							font-weight: %11$s;
							color: %7$s;
							font-size: %4$s;
							border: %5$s %12$s %7$s;
							padding: %6$s;
							transform: rotate(%11$sdeg); 
							top: %1$s;
							left: %2$s;
							text-align: center;
							vertical-align: middle;
							border-radius: %9$s;
						">
						%3$s
					</div>',
					
					$position_top . $unit, // 1
					$position_left . $unit, // 2
					$stamp_text, // 3
					$font_size . $unit, // 4
					$border_size . $unit, // 5
					$padding . $unit, // 6
					$color, // 7
					$font_family, // 8
					$border_radius, // 9
					get_option( 'wp_wc_invoice_pdf_cancel_stamp_font_weight', 'normal' ), // 10
					intval( get_option( 'wp_wc_invoice_pdf_cancel_stamp_rotation', -10 ) ), // 11
					$border_type // 12
				);

				$cancelled = apply_filters( 'wp_wc_invoice_pdf_cancel_stamp_markup', $cancelled );

				$html = str_replace( '</header>', '</header>'.  $cancelled, $html );
			}

			return $html;
		}
	}
}
