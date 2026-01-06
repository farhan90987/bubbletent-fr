<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//////////////////////////////////////////////////
// init
//////////////////////////////////////////////////

// fonts
$fonts		= WP_WC_Invoice_Pdf_Helper::get_fonts();
$fonts		= array_keys( $fonts );
$fonts		= array_combine( $fonts, $fonts );

//////////////////////////////////////////////////
// options
//////////////////////////////////////////////////

$options	= array(	

	array( 'title' => __( 'Legal Texts PDFs', 'woocommerce-german-market' ), 'type' => 'title', 'id' => 'wp_wc_invoice_pdf_additional_pdfs' ),

	// font-familiy
	array(
			'name' 		=> __( 'Font', 'woocommerce-german-market' ),
			'desc_tip' 	=> __( 'Choose the general font used in the additional PDFs', 'woocommerce-german-market' ),
			'tip'  		=> __( 'Choose the general font used in the additional PDFs', 'woocommerce-german-market' ),
			'id'   		=> 'wp_wc_invoice_pdf_additional_pdf_font',
			'type' 		=> 'select',
			'default'  	=> 'Helvetica',
			'css'      	=> 'width: 250px;',
			'options' 	=> $fonts
		),	
		
	// font size
	array(
			'name' 		=> __( 'Font Size', 'woocommerce-german-market' ),
			'desc_tip'	=> __( 'Choose the general font size used in the additional PDFs', 'woocommerce-german-market' ),
			'tip'  		=> __( 'Choose the general font size used in the invoice', 'woocommerce-german-market' ),
			'id'   		=> 'wp_wc_invoice_pdf_additional_pdf_font_size',
			'type' 		=> 'select',
			'default'  	=> 10,
			'css'      	=> 'width: 100px;',
			'options' 	=> array_combine( self::$font_sizes, self::$font_sizes )
		),

	// font color
	array(
			'name' 		=> __( 'Text Color', 'woocommerce-german-market' ),
			'desc_tip' 	=> __( 'Choose the general text color used used in the additional PDFs', 'woocommerce-german-market' ),
			'tip'  		=> __( 'Choose the general text color used used in the in the additional PDFs', 'woocommerce-german-market' ),
			'id'   		=> 'wp_wc_invoice_pdf_additional_pdf_text_color',
			'type' 		=> 'color',
			'default'  	=> '#000',
			'css'      	=> 'width: 100px;',
		),

	array(
			'name' 		=> __( 'Show Page Titles as Headlines', 'woocommerce-german-market' ),
			'desc_tip' 	=> __( 'If activated, the page titles of your WordPress pages will be shown as headlines in the PDF files', 'woocommerce-german-market' ),
			'tip'  		=> __( 'If activated, the page titles of your WordPress pages will be shown as headlines in the PDF files', 'woocommerce-german-market' ),
			'id'   		=> 'wp_wc_invoice_pdf_additional_pdf_show_headlines',
			'type' 		=> 'wgm_ui_checkbox',
			'default'  	=> 'on',
		),

	array(
			'name'     	=> __( 'Do not show background image in legal text PDFs', 'woocommerce-german-market' ),
			'id'       	=> 'wp_wc_invoice_pdf_background_image_not_in_legal_text_pdfs',
			'type'     	=> 'wgm_ui_checkbox',
			'default'  	=> 'off',
		),

	array(
			'name'     	=> __( 'Do not show header in legal text PDFs', 'woocommerce-german-market' ),
			'id'       	=> 'wp_wc_invoice_pdf_header_not_in_legal_text_pdfs',
			'type'     	=> 'wgm_ui_checkbox',
			'default'  	=> 'off',
		),

	array(
			'name'     	=> __( 'Do not show footer in legal text PDFs', 'woocommerce-german-market' ),
			'id'       	=> 'wp_wc_invoice_pdf_footer_not_in_legal_text_pdfs',
			'type'     	=> 'wgm_ui_checkbox',
			'default'  	=> 'off',
		),
				
	array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_additional_pdfs' ),

	array( 'title' => __( 'Terms and Conditions PDF', 'woocommerce-german-market' ), 'type' => 'title', 'desc' => __( 'Terms and Conditions PDF will not be attached to emails if no pages were added in the following settings', 'woocommerce-german-market' ), 'id' => 'wp_wc_invoice_pdf_additional_pdfs_terms_and_conditions' ),

	// legall information page
	array(
			'name' 		=> __( 'Legal Information Page', 'woocommerce-german-market' ),
			'desc' 		=> __( 'Add the Legal Information page to the Terms and Conditions PDF', 'woocommerce-german-market' ),
			'tip'  		=> __( 'Add the Legal Information page to the Terms and Conditions PDF', 'woocommerce-german-market' ),
			'id'   		=> 'wp_wc_invoice_pdf_additional_pdf_legal_information_page',
			'type' 		=> 'checkbox',
			'default'  	=> 'no'
		),

	// terms page
	array(
			'name' 		=> __( 'Terms and conditions', 'woocommerce-german-market' ),
			'desc' 		=> __( 'Add Terms and conditions page to the Terms and Conditions PDF', 'woocommerce-german-market' ),
			'tip'  		=> __( 'Add Terms and conditions page to the Terms and Conditions PDF', 'woocommerce-german-market' ),
			'id'   		=> 'wp_wc_invoice_pdf_additional_pdf_terms_page',
			'type' 		=> 'checkbox',
			'default'  	=> 'no'
		),
		
	// privacy
	array(
			'name' 		=> __( 'Privacy', 'woocommerce-german-market' ),
			'desc' 		=> __( 'Add Privacy page to the Terms and Conditions PDF', 'woocommerce-german-market' ),
			'tip'  		=> __( 'Add Privacy page to the Terms and Conditions PDF', 'woocommerce-german-market' ),
			'id'   		=> 'wp_wc_invoice_pdf_additional_pdf_privacy_page',
			'type' 		=> 'checkbox',
			'default'  	=> 'no'
		),

	// shipping and delivery
	array(
			'name' 		=> __( 'Shipping & Delivery', 'woocommerce-german-market' ),
			'desc' 		=> __( 'Add Shipping & Delivery page to the Terms and Conditions PDF', 'woocommerce-german-market' ),
			'tip'  		=> __( 'Add Shipping & Delivery page to the Terms and Conditions PDF', 'woocommerce-german-market' ),
			'id'   		=> 'wp_wc_invoice_pdf_additional_pdf_shipping_and_delivery_page',
			'type' 		=> 'checkbox',
			'default'  	=> 'no'
		),

	// payment methods
	array(
			'name' 		=> __( 'Payment Methods', 'woocommerce-german-market' ),
			'desc' 		=> __( 'Add Payment Methods page to the Terms and Conditions PDF', 'woocommerce-german-market' ),
			'tip'  		=> __( 'Add Payment Methods page to the Terms and Conditions PDF', 'woocommerce-german-market' ),
			'id'   		=> 'wp_wc_invoice_pdf_additional_pdf_payment_methods_page',
			'type' 		=> 'checkbox',
			'default'  	=> 'no'
		),

	array(
			'name' 		=> __( 'File Name', 'woocommerce-german-market' ),
			'id'   		=> 'wp_wc_invoice_pdf_additional_pdfs_file_name_terms',
			'type' 		=> 'text',
			'default' 	=> __( 'Terms and conditions', 'woocommerce-german-market' ),
		),

	array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_additional_pdfs_terms_and_conditions' ),

	array( 'title' => __( 'Revocation Policy PDF', 'woocommerce-german-market' ), 'type' => 'title', 'desc' => __( 'The Recovation Policy PDF will not be attached to emails if no pages were added in the following settings', 'woocommerce-german-market' ), 'id' => 'wp_wc_invoice_pdf_additional_pdfs_revocation_policy' ),

	// Revocation Policy Page
	array(
			'name' 		=> __( 'Revocation Policy Page', 'woocommerce-german-market' ),
			'desc' 		=> __( 'Add the Revocation Policy page to the Recovation Policy PDF', 'woocommerce-german-market' ),
			'tip'  		=> __( 'Add the Revocation Policy page to the Recovation Policy PDF', 'woocommerce-german-market' ),
			'id'   		=> 'wp_wc_invoice_pdf_additional_pdf_recovation_policy_page',
			'type' 		=> 'checkbox',
			'default'  	=> 'no'
	),

	// Revocation Policy for Digital Content Page
	array(
			'name' 		=> __( 'Revocation Policy for Digital Content Page', 'woocommerce-german-market' ),
			'desc' 		=> __( 'Add the Revocation Policy for Digital Content page to the Recovation Policy PDF', 'woocommerce-german-market' ),
			'tip'  		=> __( 'Add the Revocation Policy for Digital Content page to the Recovation Policy PDF', 'woocommerce-german-market' ),
			'id'   		=> 'wp_wc_invoice_pdf_additional_pdf_recovation_policy_digital_page',
			'type' 		=> 'checkbox',
			'default'  	=> 'no'
	),

	array(
			'name' 		=> __( 'File Name', 'woocommerce-german-market' ),
			'id'   		=> 'wp_wc_invoice_pdf_additional_pdfs_file_name_revocation',
			'type' 		=> 'text',
			'default' 	=> __( 'Revocation Policy', 'woocommerce-german-market' ),
		),

	array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_additional_pdfs_revocation_policy' ),
	
	
);			