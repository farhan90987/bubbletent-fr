<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//////////////////////////////////////////////////
// init
//////////////////////////////////////////////////

$user_unit = get_option( 'wp_wc_invoice_pdf_user_unit', 'cm' );
			
if ( $part == 'footer' ) {
	if ( $i == 1 && get_option( 'wp_wc_invoice_pdf_footer_number_of_columns', 1 ) == 1 ) {
		$title = __( 'Footer Text', 'woocommerce-german-market' );
	} else {
		$title = __( 'Footer Column', 'woocommerce-german-market' ) .' '. $i;
	}
} else if ( $part == 'header' ) {
	if ( $i == 1 && get_option( 'wp_wc_invoice_pdf_header_number_of_columns', 1 ) == 1 ) {
		$title = __( 'Header Text', 'woocommerce-german-market' );
	} else {
		$title = __( 'Header Column', 'woocommerce-german-market' ) .' '. $i;
	}
} // else - should never happens

// description text ist different for the last column
$desc_width = __( 'Choose the width of this column', 'woocommerce-german-market' );
$nextOptionToLoad = ( $part == 'footer' ) ? get_option( 'wp_wc_invoice_pdf_footer_number_of_columns', 1 ) : get_option( 'wp_wc_invoice_pdf_header_number_of_columns', 1 );
if ( $i == $nextOptionToLoad ) {	
	if ( $part == 'footer' ) {
		$desc_width .= __( ', if you leave this field empty or if you enter 0 the column will end at the right border of the footer', 'woocommerce-german-market' );
	} else {
		$desc_width .= __( ', if you leave this field empty or if you enter 0 the column will end at the right border of the header', 'woocommerce-german-market' );
	}
}

// fonts
$fonts		= WP_WC_Invoice_Pdf_Helper::get_fonts();
$fonts		= array_keys( $fonts );
$fonts		= array_combine( $fonts, $fonts );
$fonts		= apply_filters( 'wp_wc_invoice_pdf_custom_fonts', $fonts );

//////////////////////////////////////////////////
// foreach column output a section:
//////////////////////////////////////////////////

$column =  array(				
			array( 'title' => $title, 'type' => 'title','desc' => '', 'id' => 'wp_wc_invoice_pdf_' . $part . '_column_' . $i . '_settings' ),
			
			// column width
			array(
					'name' 		=> __( 'Column Width', 'woocommerce-german-market' ),
					'desc' 		=> $user_unit,
					'desc_tip'	=> $desc_width,
					'tip'  		=> $desc_width,
					'id'   		=> 'wp_wc_invoice_pdf_' . $part .'_column_' . $i . '_width',
					'type' 		=> 'text',
					'default'  	=> 0,
					'css'      	=> 'width: 100px;',
					'class'		=> 'german-market-unit',
				),
				
			// font family
			array(
					'name' 		=> __( 'Font', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Choose the font for this column', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose the font for this column', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_' . $part .'_column_' . $i . '_font',
					'type' 		=> 'select',
					'default'  	=> 'Helvetica',
					'css'      	=> 'width: 200px;',
					'options' 	=> $fonts
				),	
			
			// font size
			array(
					'name' 		=> __( 'Font Size', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Choose the font size for this column', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose the font size for this column', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_' . $part .'_column_' . $i . '_font_size',
					'type' 		=> 'select',
					'default'  	=> 10,
					'css'      	=> 'width: 100px;',
					'options' 	=> array_combine( self::$font_sizes, self::$font_sizes )
				),
			
			// text color
			array(
					'name' 		=> __( 'Text Color', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Choose the text color used in this column', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose the text color used in this column', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_' . $part .'_column_' . $i . '_color',
					'type' 		=> 'color',
					'default'  	=> '#000',
					'css'      	=> 'width: 100px;',
				),
			
			// bold, italic, underline
			array(
					'name' 		=> __( 'Font Style', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Choose whether your text shall be bold, italic or underlined', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose whether your text shall be bold, italic or underlined', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_' . $part .'_column_' . $i . '_font_style',
					'type' 		=> 'select',
					'default'  	=> 'normal',
					'css'      	=> 'width: 200px;',
					'options' 	=> array(
									'normal'				=> __( 'Normal', 'woocommerce-german-market' ),
									'bold' 					=> __( 'Bold', 'woocommerce-german-market' ),
									'italic'				=> __( 'Italic', 'woocommerce-german-market' ),
									'bold_italic'			=> __( 'Bold, italic', 'woocommerce-german-market' ),
									'bold_underline'		=> __( 'Bold, underline', 'woocommerce-german-market' ),
									'bold_underline_italic'	=> __( 'Bold, underline, italic', 'woocommerce-german-market' ),
									'italic_underline'		=> __( 'Italic, underline', 'woocommerce-german-market' )
								)
				),
			
			// horizontal alignment
			array(
					'name' 		=> __( 'Horizontal Text Alignment', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Choose horizontal text alignment for this column', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose horizontal text alignment for this column', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_' . $part .'_column_' . $i . '_horizontal_text_alignment',
					'type' 		=> 'select',
					'default'  	=> 'left',
					'css'      	=> 'width: 200px;',
					'options' 	=> array(
									'left'				=> __( 'Left', 'woocommerce-german-market' ),
									'center'			=> __( 'Center', 'woocommerce-german-market' ),
									'right'				=> __( 'Right', 'woocommerce-german-market' ),
									'justify'			=> __( 'Justify', 'woocommerce-german-market' )
								)
				),
			
			// vertical alignment	
			array(
					'name' 		=> __( 'Vertical Text Alignment', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Choose vertical text alignment for this column', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose vertical text alignment for this column', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_' . $part .'_column_' . $i . '_vertical_text_alignment',
					'type' 		=> 'select',
					'default'  	=> 'top',
					'css'      	=> 'width: 200px;',
					'options' 	=> array(
									'top'				=> __( 'Top', 'woocommerce-german-market' ),
									'middle'			=> __( 'Middle', 'woocommerce-german-market' ),
									'bottom'			=> __( 'Bottom', 'woocommerce-german-market' ),
								)
				),
			
			// text	
			array(
					'name' 		=> __( 'Text', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Enter the text for this footer column, HTML is allowed', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Enter the text for this footer column, HTML is allowed', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_' . $part .'_column_' . $i . '_text',
					'css'  		=> 'min-width:500px; height: 100px;',
					'type' 		=> 'wp_wc_invoice_pdf_textarea',
					'default'  	=> ''
			),	
						
			array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_' . $part . '_column_' . $i . '_settings' )
		);
?>