<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//////////////////////////////////////////////////
// init
//////////////////////////////////////////////////

$allow_url_fopen	= ini_get( 'allow_url_fopen' );
$short_description = __( 'To use other fonts in your invoice, you have the possibility to add Google Fonts here. You have to copy the <code>link</code> tag for each font you want to use into the field below. You find this code on the "Quick Use" page of each Google Font.', 'woocommerce-german-market' );
					
$description	= $short_description . '<br /><br />' . __( 'You have to choose the Google Fonts for <b>bold</b>, <i>italic</i>, and <b><i>bold and italic</i></b> if you want to use these font styles. Unfortunately, DOMPDF cannot render the font weight for bold texts correctly, i.e. the bold text will not be rendered in the chosen font. Nevertheless, if you want to use this font you can set the default font weight for bold text to "normal" at the bottom of this option page. In that case, you could add some other custom CSS styles (e.g. color or font-size) to the elements that cannot be shown in bold (these CSS elements are: <code>strong</code>, <code>b</code>, <code>th</code>, <code>table.subject tr td</code>, <code>h1</code>, <code>h2</code>, <code>h3</code>, <code>h4</code>, <code>h5</code>, <code>h6</code>).', 'woocommerce-german-market' );

$description .= '<br /><br />' . __( 'When Google Fonts are included, DOMPDF automatically allows remote sources to be loaded. For security reasons this is otherwise disabled by default in DOMPDF. DOMPDF is the PHP library which renders HTML to PDF.', 'woocommerce-german-market' );

$example		= 	"<link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>\r\n<link href='http://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>";

//////////////////////////////////////////////////
// options
//////////////////////////////////////////////////

if ( ! $allow_url_fopen ) {
	$error = __( 'The option "allow_url_fopen" is not activated on your server, but it is necessary to use this feature. Please ask your admin to enable this option.', 'woocommerce-german-market' );
	$options	= array(	
					array( 'title' => __( 'Custom Fonts', 'woocommerce-german-market' ), 'type' => 'title','desc' => $short_description . '<br /><div class="error" style="padding: 10px 12px; font-weight: bold;">' . $error . '</div>', 'id' => 'wp_wc_invoice_pdf_custom_fonts' ),
					array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_custom_fonts' )
				);
} else {

	$options	= array(	

					array(	'name' 	=> __( 'Test invoice', 'woocommerce-german-market' ), 'type' => 'wp_wc_invoice_pdf_test_download_button' ),	

					array( 'title'	=> __( 'Custom Fonts', 'woocommerce-german-market' ), 'type' => 'title','desc' => $description, 'id' => 'wp_wc_invoice_pdf_custom_fonts_section' ),			
					
					array(
						'name' 		=> __( 'Google Fonts', 'woocommerce-german-market' ),
						'desc_tip'	=> __( 'Insert your code here, use a new line for each <code>link</code> tag! (see example below)', 'woocommerce-german-market' ),
						'tip'  		=> __( 'Insert your code here, use a new line for each <code>link</code> tag! (see example below)', 'woocommerce-german-market' ),
						'id'   		=> 'wp_wc_invoice_pdf_custom_fonts',
						'type' 		=> 'wp_wc_invoice_pdf_textarea',
						'css'  		=> 'width: 600px; max-width: 100%; min-height: 200px;',
						'default'  	=> '',
						),
					
					array(
						'name' 		=> __( 'Example', 'woocommerce-german-market' ),
						'desc_tip' 	=> __( 'Example code to add the fonts "Open Sans" and "Roboto"', 'woocommerce-german-market' ),
						'tip'  		=> __( 'Example code to add the fonts "Open Sans" and "Roboto"', 'woocommerce-german-market' ),
						'type' 		=> 'wp_wc_invoice_pdf_textarea',
						'css'  		=> 'min-width: 500px; height: 75px;',
						'custom_attributes' => array( 'readonly' => 'readonly', 'return_html' => $example )
						),
						
					array(
						'name' 		=> __( 'Default Font Weight for Bold Text', 'woocommerce-german-market' ),
						'desc_tip' 	=> __( 'If the bold text of your Google Font is not rendered correctly, set this option to "normal"', 'woocommerce-german-market' ),
						'tip'  		=> __( 'If the bold text of your Google Font is not rendered correctly, set this option to "normal"', 'woocommerce-german-market' ),
						'id'   		=> 'wp_wc_invoice_pdf_default_font_weight_bold',
						'type' 		=> 'select',
						'default'  	=> 'bold',
						'css'      	=> 'width: 100px;',
						'options' 	=> array(
										'bold'		=> __( 'Bold', 'woocommerce-german-market' ),
										'normal' 	=> __( 'Normal', 'woocommerce-german-market' ),
									)
							),		
					
					array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_custom_fonts_section' )
				
		);	
}
