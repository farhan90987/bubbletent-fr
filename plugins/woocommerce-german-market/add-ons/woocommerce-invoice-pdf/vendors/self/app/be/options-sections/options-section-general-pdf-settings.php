<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//////////////////////////////////////////////////
// init
//////////////////////////////////////////////////

$file_name_placeholders = apply_filters( 'wp_wc_invoice_pdf_placeholders', array( 'order-number' => __( 'Order number', 'woocommerce-german-market' ) ) );
$placeholder_array_string = array();
foreach ( $file_name_placeholders as $key => $value ) {
	$placeholder_array_string[] = $value . ' - <code>{{' . $key . '}}</code>';
}
$placeholder_string = implode( ', ', $placeholder_array_string );
if ( count( $placeholder_array_string ) == 1 ) {
	$placeholder_text = __( 'You can use this placeholder', 'woocommerce-german-market' ) . ': ' . $placeholder_string;	
} else {
	$placeholder_text = __( 'You can use the following placeholders', 'woocommerce-german-market' ) . ': ' . $placeholder_string;	
}

//////////////////////////////////////////////////
// options
//////////////////////////////////////////////////

$options	= array (
				array(	'name' 		=> __( 'Test Invoice', 'woocommerce-german-market' ), 'type' => 'wp_wc_invoice_pdf_test_download_button' ),	
				
				array( 'title' => __( 'File Settings' , 'woocommerce-german-market' ), 'type' => 'title','desc' => '', 'id' => 'wp_wc_invoice_pdf_general_pdf_settings_file' ),
				
				array(
						'name' 		=> __( 'Document Title', 'woocommerce-german-market' ),
						'desc_tip'	=> __( 'Title of the invoice (there is no output in the content of the pdf file)', 'woocommerce-german-market' ),
						'tip'  		=> __( 'Title of the invoice (there is no output in the content of the pdf file)', 'woocommerce-german-market' ),
						'id'   		=> 'wp_wc_invoice_pdf_document_title',
						'type' 		=> 'text',
						'default'  	=> __( 'Invoice', 'woocommerce-german-market' ) .' - ' . get_bloginfo( 'name' ),
						'css'     	=> 'width: 400px;',
					),
					
				array(
						'name' 		=> __( 'File Name in Backend', 'woocommerce-german-market' ),
						'desc' 		=> '.pdf',
						'desc_tip'	=> __( 'Invoice file name to use in backend', 'woocommerce-german-market' ),
						'tip'  		=> __( 'Invoice file name to use in backend', 'woocommerce-german-market' ),
						'desc'		=> '<span class="desc">' . $placeholder_text . '</span>',
						'id'   		=> 'wp_wc_invoice_pdf_file_name_backend',
						'type' 		=> 'text',
						'default'  	=> __( 'Invoice-{{order-number}}', 'woocommerce-german-market' ),
						'css'      	=> 'width: 400px;',
					),	
				
				array(
						'name' 		=> __( 'File Name in Frontend', 'woocommerce-german-market' ),
						'desc' 		=> '.pdf',
						'desc_tip'	=> __( 'Invoice file name to use in frontend for your customer', 'woocommerce-german-market' ),
						'tip'  		=> __( 'Invoice file name to use in frontend for your customer', 'woocommerce-german-market' ),
						'desc'		=> '<span class="desc">' . $placeholder_text . '</span>',
						'id'   		=> 'wp_wc_invoice_pdf_file_name_frontend',
						'type' 		=> 'text',
						'default'  	=>  get_bloginfo( 'name' ) . '-' . __( 'Invoice-{{order-number}}', 'woocommerce-german-market' ),
						'css'      	=> 'width: 400px;',
					),
				
				array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_general_pdf_settings_file' ),
				
				array( 'title' => __( 'Paper Size', 'woocommerce-german-market' ), 'type' => 'title','desc' => '', 'id' => 'wp_wc_invoice_pdf_general_pdf_settings_sizes' ),
				
				array(
						'name' 		=> __( 'Paper Size', 'woocommerce-german-market' ),
						'desc_tip' 	=> __( 'Paper size of your invoice, A4 has a width of 21cm = 8.267in and a height of 29.7cm = 11.692in, letter format has a width of 8.5in = 21.59cm and a height of 11in = 27.94cm', 'woocommerce-german-market' ),
						'tip'  		=> __( 'Paper size of your invoice, A4 has a width of 21cm = 8.267in and a height of 29.7cm = 11.692in, letter format has a width of 8.5in = 21.59cm and a height of 11in = 27.94cm', 'woocommerce-german-market' ),
						'id'   		=> 'wp_wc_invoice_pdf_paper_size',
						'type' 		=> 'select',
						'default'  	=> 'A4',
						'css'      	=> 'width: 150px;',
						'options' 	=> array(
										'A4'		=> 'A4',
										'letter'	=> 'Letter'
									)
					),

				array(
						'name' 		=> __( 'Paper Orientation', 'woocommerce-german-market' ),
						'desc_tip' 	=> __( 'Choose portrait or landscape as orientation.', 'woocommerce-german-market' ),
						'id'   		=> 'wp_wc_invoice_pdf_paper_orientation',
						'type' 		=> 'select',
						'default'  	=> 'portrait',
						'css'      	=> 'width: 150px;',
						'options' 	=> array(
										'portrait'		=> __( 'Portrait', 'woocommerce-german-market' ),
										'landscape'		=> __( 'Landscape', 'woocommerce-german-market' )
									)
					),
				
				array(
						'name' 		=> __( 'Unit of Length', 'woocommerce-german-market' ),
						'desc_tip' 	=> __( 'Unit of length that is used for other settings', 'woocommerce-german-market' ),
						'tip'  		=> __( 'Unit of length that is used for other settings', 'woocommerce-german-market' ),
						'id'   		=> 'wp_wc_invoice_pdf_user_unit',
						'type' 		=> 'select',
						'default'  	=> 'cm',
						'css'      	=> 'width: 150px;',
						'options' 	=> array(
										'cm'		=> 'Centimeter (cm)',
										'in'		=> 'Inch (in)'
									)
					),
				
				array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_general_pdf_settings_sizes' ),
				
				array( 'title' => __( 'Page Margins', 'woocommerce-german-market' ), 'type' => 'title','desc' => '', 'id' => 'wp_wc_invoice_pdf_general_pdf_settings_margins' ),
				
				array(
						'name' 		=> __( 'Margin Top', 'woocommerce-german-market' ),
						'desc'		=> $user_unit,
						'desc_tip'	=> __( 'Space between the top page margin or the space between the bottom of the header (if a header is set) and the content of the invoice', 'woocommerce-german-market' ),
						'tip'  		=> __( 'Space between the top page margin or the space between the bottom of the header (if a header is set) and the content of the invoice', 'woocommerce-german-market' ),
						'id'   		=> 'wp_wc_invoice_pdf_body_margin_top',
						'type' 		=> 'text',
						'default'  	=>  1,
						'css'      	=> 'width: 100px;',
						'class'		=> 'german-market-unit',
					),
					
				array(
						'name' 		=> __( 'Margin Right', 'woocommerce-german-market' ),
						'desc'		=> $user_unit,
						'desc_tip'	=> __( 'Space between the right page margin and the content of the invoice', 'woocommerce-german-market' ),
						'tip'  		=> __( 'Space between the right page margin and the content of the invoice', 'woocommerce-german-market' ),
						'id'   		=> 'wp_wc_invoice_pdf_body_margin_right',
						'type' 		=> 'text',
						'default'  	=>  1,
						'css'      	=> 'width: 100px;',
						'class'		=> 'german-market-unit',
					),
					
				array(
						'name' 		=> __( 'Margin Bottom', 'woocommerce-german-market' ),
						'desc' 		=> $user_unit,
						'desc_tip'	=> __( 'Space between the bottom page margin or the space between the top of the footer (if a footer is set) and the content of the invoice', 'woocommerce-german-market' ),
						'tip'  		=> __( 'Space between the bottom page margin or the space between the top of the footer (if a footer is set) and the content of the invoice', 'woocommerce-german-market' ),
						'id'   		=> 'wp_wc_invoice_pdf_body_margin_bottom',
						'type' 		=> 'text',
						'default'  	=>  1,
						'css'      	=> 'width: 100px;',
						'class'		=> 'german-market-unit',
					),
					
				array(
						'name' 		=> __( 'Margin Left', 'woocommerce-german-market' ),
						'desc'		=> $user_unit,
						'desc_tip'	=> __( 'Space between the left page margin and the content of the invoice', 'woocommerce-german-market' ),
						'tip' 		=> __( 'Space between the left page margin and the content of the invoice', 'woocommerce-german-market' ),
						'id'   		=> 'wp_wc_invoice_pdf_body_margin_left',
						'type' 		=> 'text',
						'default'	=>  1,
						'css'      	=> 'width: 100px;',
						'class'		=> 'german-market-unit',
					),			
				
				array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_general_pdf_settings_margins' ),
				
				array( 'title' => __( 'Paper Color', 'woocommerce-german-market' ), 'type' => 'title','desc' => '', 'id' => 'wp_wc_invoice_pdf_general_pdf_paper_color' ),
				
				array(
						'name' 		=> __( 'Color', 'woocommerce-german-market' ),
						'desc_tip' 	=> __( 'Background color of the invoice, leave blank to use no background color (white)', 'woocommerce-german-market' ),
						'tip'  		=> __( 'Background color of the invoice, leave blank to use no background color (white)', 'woocommerce-german-market' ),
						'id'   		=> 'wp_wc_invoice_pdf_background_color_background',
						'type'		=> 'color',
						'default'  	=>  '',
						'css'      	=> 'width: 100px;',
					),
				
				array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_general_pdf_paper_color' ),

				array( 'title' => __( 'Backend download', 'woocommerce-german-market' ), 'type' => 'title','desc' => '', 'id' => 'wp_wc_invoice_pdf_general_pdf_debug' ),

				array(
					'name' 		=> __( 'Backend download of invoice PDFs', 'woocommerce-german-market' ),
					'desc'		=> sprintf(

						__( 'If this setting is disabled, there is no way for admins to download invoice PDFs in the backend. This is useful, for example, if you only want to use the "Legal Texts PDFs" functions of the add-on. If you only want to use the "Legal Texts PDFs", also make sure that you have disabled all settings in the menus "<a href="%s">Emails Invoice PDF</a>" and "<a href="%s">My Account Page</a>".', 'woocommerce-german-market' ),

						admin_url() . 'admin.php?page=german-market&tab=invoice-pdf&sub_tab=emails',
						admin_url() . 'admin.php?page=german-market&tab=invoice-pdf&sub_tab=my_account_page'

					),

					'id'   		=> 'wp_wc_invpice_pdf_backend_download',
					'type' 		=> 'wgm_ui_checkbox',
					'default'  	=> 'on'
					),

				array( 'type' => 'sectionend' ),

				array( 'title' => __( 'Debug', 'woocommerce-german-market' ), 'type' => 'title','desc' => '', 'id' => 'wp_wc_invoice_pdf_general_pdf_debug' ),

				array(
					'name' 		=> __( 'Debug in PDF', 'woocommerce-german-market' ),
					'desc_tip'	=> __( 'Even though WP_DEBUG is set to true PHP Notices, Warnings and Erros are suppresses to avoid unwanted in messages in the PDF file. Activate this option to see PHP debug notices.', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_debug',
					'type' 		=> 'wgm_ui_checkbox',
					'default'  	=> 'off'
					),

				array( 'type' => 'sectionend' )
			);
