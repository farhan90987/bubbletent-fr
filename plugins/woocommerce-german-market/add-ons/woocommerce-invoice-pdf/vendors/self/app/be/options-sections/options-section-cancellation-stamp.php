<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$options = array();

$options[] = array(	
	'name' 	=> __( 'Test Invoice', 'woocommerce-german-market' ), 
	'type' => 'wp_wc_invoice_pdf_test_download_button' 
);	

$options[] = array( 
	'title'	=> __( 'Cancellation Stamp', 'woocommerce-german-market' ), 
	'type' => 'title',
	'desc' => sprintf( 
		__( 'For orders with the status "%s", a cancellation stamp can be displayed on the invoice PDF. If the output of the stamp is activated, it is output on every page of the PDF.', 'woocommerce-german-market' ),
		wc_get_order_status_name( 'cancelled' )
	)
);

$options[] = 	array(
	'name'     => __( 'Activation', 'woocommerce-german-market' ),
	'id'       => 'wp_wc_invoice_pdf_cancel_stamp',
	'type'     => 'wgm_ui_checkbox',
	'default'  => 'off',
);

$options[] = array(
	'type' => 'sectionend',
);

$options[] = array( 
	'title'	=> __( 'Design', 'woocommerce-german-market' ), 
	'type' => 'title',
	'desc' => ''
);

$options[] = array(
	'name'     				=> __( 'Text on the stamp', 'woocommerce-german-market' ),
	'id'       				=> 'wp_wc_invoice_pdf_cancel_stamp_text',
	'type'     				=> 'text',
	'default'				=> _x( 'CANCELLED', 'stamp text in cancelled invoice', 'woocommerce-german-market' ),		
);

$unit = get_option( 'wp_wc_invoice_pdf_user_unit', 'cm' );

// fonts
$fonts		= WP_WC_Invoice_Pdf_Helper::get_fonts();
$fonts		= array_keys( $fonts );
$fonts		= array_combine( $fonts, $fonts );
$fonts		= apply_filters( 'wp_wc_invoice_pdf_custom_fonts', $fonts );

$options[] = array(
	'name'    	=> __( 'Stamp font', 'woocommerce-german-market' ),
	'id'      	=> 'wp_wc_invoice_pdf_cancel_stamp_font',
	'type' 		=> 'select',
	'default'  	=> 'Helvetica',
	'options' 	=> $fonts,
	'class'	   	=> 'wc-enhanced-select',
);

$options[] = array(
	'name'		=> __( 'Font weight', 'woocommerce-german-market' ),
	'id'       	=> 'wp_wc_invoice_pdf_cancel_stamp_font_weight',
	'type'     	=> 'select',
	'default'	=> 'normal',
	'class'	   	=> 'wc-enhanced-select',
	'options'	=> array(
		'normal'				=> __( 'Normal', 'woocommerce-german-market' ),
		'bold' 					=> __( 'Bold', 'woocommerce-german-market' ),
	),
);

$options[] = array(
	'name'     				=> __( 'Font size', 'woocommerce-german-market' ),
	'id'       				=> 'wp_wc_invoice_pdf_cancel_stamp_font_size',
	'type'     				=> 'number',
	'default'				=> 1.5,
	'class'					=> 'german-market-unit',
	'desc'					=> $unit,
	'custom_attributes'	 	=> array( 'step' => '0.1', 'min' => 0, 'max' => 4 ),
	'css'					=> 'width: 100px;',	
);

$options[] = array(
	'name'     				=> __( 'Border size', 'woocommerce-german-market' ),
	'id'       				=> 'wp_wc_invoice_pdf_cancel_stamp_border_size',
	'type'     				=> 'number',
	'default'				=> 0.15,
	'class'					=> 'german-market-unit',
	'desc'					=> $unit,
	'custom_attributes'	 	=> array( 'step' => '0.01', 'min' => 0, 'max' => 1 ),
	'css'					=> 'width: 100px;',	
);

$options[] = array(
	'name'     		=> __( 'Border Style', 'woocommerce-german-market' ),
	'id'       		=> 'wp_wc_invoice_pdf_cancel_stamp_border_style',
	'type'     		=> 'select',
	'class'	   		=> 'wc-enhanced-select',
	'default'		=> 'double',
	'options'		=> array(
		'solid'		=> _x( 'Solid', 'border style', 'woocommerce-german-market' ),
		'double' 	=> __( 'Double', 'woocommerce-german-market' ),
		'dashed' 	=> __( 'Dashed', 'woocommerce-german-market' ),
		'dotted' 	=> __( 'Dotted', 'woocommerce-german-market' ),
	),
);

$options[] = array(
	'name'     				=> __( 'Border radius', 'woocommerce-german-market' ),
	'id'       				=> 'wp_wc_invoice_pdf_cancel_stamp_border_radius',
	'type'     				=> 'number',
	'default'				=> 1.5,
	'class'					=> 'german-market-unit',
	'desc'					=> $unit,
	'custom_attributes'	 	=> array( 'step' => '0.1', 'min' => 0, 'max' => 2 ),
	'css'					=> 'width: 100px;',	
	'desc_tip'				=> __( 'This setting can be used to round off the corners of the stamp.', 'woocommerce-german-market' ),
);

$options[] = array(
	'name'     				=> __( 'Stamp color', 'woocommerce-german-market' ),
	'id'       				=> 'wp_wc_invoice_pdf_cancel_stamp_color',
	'type'     				=> 'color',
	'default'				=> '#ff0000',
	'css'					=> 'width: 100px;',	
);

$options[] = array(
	'name'     				=> __( 'Rotation', 'woocommerce-german-market' ),
	'id'       				=> 'wp_wc_invoice_pdf_cancel_stamp_rotation',
	'type'     				=> 'number',
	'default'				=> -10,
	'class'					=> 'german-market-unit',
	'desc'					=> '°',
	'custom_attributes'	 	=> array( 'step' => '1', 'min' => -45, 'max' => 45 ),
	'css'					=> 'width: 100px;',
	'desc_tip'				=> __( 'Entering a negative value turns the stamp to the left, a positive value turns it to the right.', 'woocommerce-german-market' ),
);

$options[] = array(
	'type' => 'sectionend',
);

$options[] = array( 
	'title'	=> __( 'Positioning', 'woocommerce-german-market' ), 
	'type' => 'title',
	'desc' => __( 'The stamp is automatically printed at the top of each page. You can change the position. If you enter a positive value for the horizontal shift, the stamp is moved to the right, if you enter a negative value, it is moved to the left. If you enter a positive value for the vertical shift, the stamp is moved downwards, if you enter a negative value, it is moved upwards.', 'woocommerce-german-market' )
);

$options[] = array(
	'name'     				=> __( 'Horizontal shift', 'woocommerce-german-market' ),
	'id'       				=> 'wp_wc_invoice_pdf_cancel_stamp_horizontal_shift',
	'type'     				=> 'number',
	'default'				=> 0,
	'class'					=> 'german-market-unit',
	'desc'					=> $unit,
	'custom_attributes'	 	=> array( 'step' => '0.1', 'min' => -20, 'max' => 20 ),
	'css'					=> 'width: 100px;',
);

$options[] = array(
	'name'     				=> __( 'Vertical shift', 'woocommerce-german-market' ),
	'id'       				=> 'wp_wc_invoice_pdf_cancel_stamp_vertical_shift',
	'type'     				=> 'number',
	'default'				=> 0,
	'class'					=> 'german-market-unit',
	'desc'					=> $unit,
	'custom_attributes'	 	=> array( 'step' => '0.1', 'min' => -20, 'max' => 20 ),
	'css'					=> 'width: 100px;',		
);

$options[] = array(
	'type' => 'sectionend',
);
