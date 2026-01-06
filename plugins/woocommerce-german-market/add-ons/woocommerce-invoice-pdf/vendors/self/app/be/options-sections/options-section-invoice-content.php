<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//////////////////////////////////////////////////
// init
//////////////////////////////////////////////////

// placeholders for subject
$subject_placeholders = apply_filters( 'wp_wc_invoice_pdf_placeholders', array( 'order-number' => __( 'Order Number', 'woocommerce-german-market' ), 'order-date' => __( 'Order Date', 'woocommerce-german-market' ) ) );
$placeholder_array_string = array();
foreach ( $subject_placeholders as $key => $value ) {
	$placeholder_array_string[] = $value . ' - <code>{{' . $key . '}}</code>';
}
$placeholder_string = implode( ', ', $placeholder_array_string );
if ( count( $placeholder_array_string ) == 1 ) {
	$placeholder_text = __( 'You can use this placeholder', 'woocommerce-german-market' ) . ': ' . $placeholder_string;	
} else {
	$placeholder_text = __( 'You can use the following placeholders', 'woocommerce-german-market' ) . ': ' . $placeholder_string;	
}

// fonts
$fonts		= WP_WC_Invoice_Pdf_Helper::get_fonts();
$fonts		= array_keys( $fonts );
$fonts		= array_combine( $fonts, $fonts );

// some description texts
$billing_address_desc = __( 'After your optional header the invoice starts with the customers billing address. If you use windowed envelopes to send your invoices, you may want to position this field exactly. The default values refer to DIN 5008 Form A. If you layout a custom header you probably want to change the following values of the customers billing address field to position it exactly where you want it.', 'woocommerce-german-market' );

$invoice_start_desc = __( 'After the customer\'s billing address there is subject line and an optional text you can add', 'woocommerce-german-market' );

// template files - init files and directories
$core_file_string		= 'woocommerce-invoice-pdf/templates/invoice-content.php';
$theme_file_string		= 'yourtheme/woocommerce-invoice-pdf/invoice-content.php';
$core_file				= untrailingslashit( plugin_dir_path( Woocommerce_Invoice_Pdf::$plugin_filename ) ) . DIRECTORY_SEPARATOR . 'vendors' . DIRECTORY_SEPARATOR . 'self' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'invoice-content.php';
$theme_template_dir		= get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'woocommerce-invoice-pdf';
$theme_file				= $theme_template_dir  . DIRECTORY_SEPARATOR . 'invoice-content.php';

// template file - move core file to template
if ( isset( $_GET[ 'gm_template_nonce' ] ) && wp_verify_nonce( $_GET[ 'gm_template_nonce' ], 'invoice_content_template' ) ) {
	if ( isset( $_GET[ 'move_template' ] ) && ( 'invoice-content' === $_GET[ 'move_template' ] ) ) {
		if (  wp_mkdir_p( dirname( get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'woocommerce-invoice-pdf' ) ) && ! file_exists( get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'woocommerce-invoice-pdf' . DIRECTORY_SEPARATOR . 'invoice-content.php' ) ) {
			  $template_file	= $core_file;
			  // Copy template file
			  wp_mkdir_p( $theme_template_dir );
			  copy( $template_file, $theme_file );
			  echo '<div class="updated fade"><p>' . __( 'Template file copied to theme.', 'woocommerce-german-market' ) . '</p></div>';
		 }
					
	}

	// template file - delete theme file
	if ( isset( $_GET[ 'delete_template' ] ) && ( 'invoice-content' === $_GET[ 'delete_template' ] ) ) {
		if ( file_exists( $theme_file ) ) {
			unlink( $theme_file );
			echo '<div class="updated fade"><p>' . __( 'Template file deleted from theme.', 'woocommerce-german-market' ) . '</p></div>';
		}
	}
}

// template file - output buttons and texts
if ( file_exists( $theme_file ) ) {
	$template_file_desc = __( 'This template containing the invoice content has been overridden by your theme and can be found in:', 'woocommerce-german-market' ) . ' <code>' . $theme_file_string . '</code>';	
	if ( is_writable( $theme_file ) ) {

		$href = add_query_arg( 'gm_template_nonce', wp_create_nonce( 'invoice_content_template' ) );
		$href = add_query_arg( 'delete_template', 'invoice-content', $href );
		$href = remove_query_arg( 'move_template', $href );

		$template_file_desc	 = '<a href="' . $href . '" class="delete_template button" style="float: right; margin-top: -4px; margin-left: 10px;">' . __( 'Delete template file', 'woocommerce-german-market' ) . '</a>' . $template_file_desc;
	}
} else {
	$template_file_desc		= __( 'To override and edit the template that contains the invoice content copy <code>[file_1]</code> to your theme folder: <code>[file_2]</code>.', 'woocommerce-german-market' );
	$template_file_desc		= str_replace( array( '[file_1]', '[file_2]' ), array( $core_file_string, $theme_file_string ), $template_file_desc );
	if ( ( is_dir( get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'woocommerce-infoice-pdf' . DIRECTORY_SEPARATOR ) && is_writable( get_stylesheet_directory()  . DIRECTORY_SEPARATOR . 'woocommerce-infoice-pdf' . DIRECTORY_SEPARATOR ) ) || is_writable( get_stylesheet_directory() ) ) { 
		
		$href = add_query_arg( 'gm_template_nonce', wp_create_nonce( 'invoice_content_template' ) );
		$href = add_query_arg( 'move_template', 'invoice-content', $href );
		$href = remove_query_arg( 'delete_template', $href );

		$template_file_desc = '<a href="' . $href . '" class="button" style="float: right; margin-top: -4px; margin-left: 10px;">' . __( 'Copy file to theme', 'woocommerce-german-market' ) . '</a>' . $template_file_desc;
	}
}

// template file - js
wc_enqueue_js("
				jQuery('a.delete_template').click(function(){
					var answer = confirm('" . esc_js( __( 'Are you sure you want to delete this template file?', 'woocommerce-german-market' ) ) . "');
					if (answer)
						return true;
					return false;
				});
			");

//////////////////////////////////////////////////
// options
//////////////////////////////////////////////////

$options = array (
			array(	'name' 		=> __( 'Test Invoice', 'woocommerce-german-market' ), 'type' => 'wp_wc_invoice_pdf_test_download_button' ),	
			
			array( 'title' 		=> __( 'Text', 'woocommerce-german-market' ), 'type' => 'title','desc' => '', 'id' => 'wp_wc_invoice_pdf_content_text' ),

			// font family
			array(
					'name' 		=> __( 'Font', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Choose the general font used in the invoice', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose the general font used in the invoice', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_content_font',
					'type' 		=> 'select',
					'default'  	=> 'Helvetica',
					'css'      	=> 'width: 250px;',
					'options' 	=> $fonts
				),	
				
			// font size
			array(
					'name' 		=> __( 'Font Size', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Choose the general font size used in the invoice', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose the general font size used in the invoice', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_content_font_size',
					'type' 		=> 'select',
					'default'  	=> 10,
					'css'      	=> 'width: 100px;',
					'options' 	=> array_combine( self::$font_sizes, self::$font_sizes )
				),
			
			// font color
			array(
					'name' 		=> __( 'Text Color', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Choose the general text color used used in the invoice', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose the general text color used used in the invoice', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_body_color',
					'type' 		=> 'color',
					'default'  	=> '#000',
					'css'      	=> 'width: 100px;',
				),				
			
			array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_content_text' ),
			
			array( 'title' => __( 'Billing Address', 'woocommerce-german-market' ), 'type' => 'title','desc' => $billing_address_desc, 'id' => 'wp_wc_invoice_pdf_content_billing_address' ),
			
			// billing address width
			array(
					'name' 		=> __( 'Width', 'woocommerce-german-market' ),
					'desc' 		=> $user_unit,
					'desc_tip'	=> __( 'Width of the customer\'s billing address field including a margin of 0.5cm = 0.2in to each border', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Width of the customer\'s billing address field including a margin of 0.5cm = 0.2in to each border', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_billing_address_width',
					'type' 		=> 'text',
					'default'  	=> '8.5',
					'css'      	=> 'width: 100px;',
					'class'		=> 'german-market-unit',
				),
				
			// billing address height
			array(
					'name' 		=> __( 'Height', 'woocommerce-german-market' ),
					'desc' 		=> $user_unit,
					'desc_tip'	=> __( 'Height of the customer\'s billing address field including a margin of 0.5cm = 0.2in to each border, leave this field blank to use auto height', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Height of the customer\'s billing address field including a margin of 0.5cm = 0.2in to each border, leave this field blank to use auto height', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_billing_address_height',
					'type' 		=> 'text',
					'default'  	=> '4.5',
					'css'      	=> 'width: 100px;',
					'class'		=> 'german-market-unit',
				),	
			
			// billing address margin top
			array(
					'name' 		=> __( 'Margin Top', 'woocommerce-german-market' ),
					'desc' 		=> $user_unit,
					'desc_tip'	=> __( 'Space between the top page margin or the space between the bottom of the header (if a header is set) and the customer\'s billing address field', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Space between the top page margin or the space between the bottom of the header (if a header is set) and the customer\'s billing address field', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_billing_address_top_margin',
					'type' 		=> 'text',
					'default'  	=> '0.7',
					'css'      	=> 'width: 100px;',
					'class'		=> 'german-market-unit',
				),
			
			// billing address margin bottom	
			array(
					'name' 		=> __( 'Margin Bottom', 'woocommerce-german-market' ),
					'desc' 		=> $user_unit,
					'desc_tip'	=> __( 'Space between the bottom of the customer\'s billing address field and the following content', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Space between the bottom of the customer\'s billing address field and the following content', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_billing_address_bottom_margin',
					'type' 		=> 'text',
					'default'  	=> 1.5,
					'css'      	=> 'width: 100px;',
					'class'		=> 'german-market-unit',
				),	
			
			// billing address additional notation	
			array(
					'name' 		=> __( 'Additional Notation', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'This field has a height of 1.27cm = 0.5in (even if you enter nothing), e.g. you can enter your address in one line. The text is 4pt smaller then the general font size you have entered further above. If you don\'t want to use this field at all, please enter <code>{{blank}}</code>(probably you have to adjust the top margin and height in this case).', 'woocommerce-german-market' ),
					'desc'		=> '<span class="desc">' . __( 'You can use the follwing HTML tags: <code>&lt;strong&gt;</code>, <code>&lt;i&gt;</code>, <code>&lt;u&gt;</code>', 'woocommerce-german-market' ) . '</span>',
					'id'   		=> 'wp_wc_invoice_pdf_billing_address_additional_notation',
					'type' 		=> 'wp_wc_invoice_pdf_textarea',
					'default'  	=> get_bloginfo( 'name' ),
					'css'      	=> 'min-width: 500px; min-height: 50px;',
				),
				
			// billing address border color
			array(
					'name' 		=> __( 'Border Color', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Choose the border color of the table, leave blank if you don\'t want to use a border', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose the border color of billing address, leave blank if you don\'t want to use a border', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_billing_address_border_color',
					'type' 		=> 'color',
					'default'  	=> '#dddddd',
					'css'      	=> 'width: 100px;',
				),
			
			// billing address border width
			array(
					'name' 		=> __( 'Border Width', 'woocommerce-german-market' ),
					'desc' 		=> 'px',
					'desc_tip'	=> __( 'Choose the border width of the table', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose the border width of the table', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_billing_address_border_width',
					'type' 		=> 'text',
					'default'  	=> '1',
					'css'      	=> 'width: 100px;',
					'class'		=> 'german-market-unit',
				),
				
			// billing address border radius
			array(
					'name' 		=> __( 'Border Radius', 'woocommerce-german-market' ),
					'desc' 		=> $user_unit,
					'desc_tip'	=> __( 'Add rounded borders to the billing address field', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Add rounded borders to the billing address field', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_billing_address_border_radius',
					'type' 		=> 'text',
					'default'  	=> ( $user_unit == 'cm' ) ? '0.3' : '0.1',
					'css'      	=> 'width: 100px;',
					'class'		=> 'german-market-unit',
				),			
			
			
			
			array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_content_billing_address' ),
			
			array( 'title' => __( 'Invoice Start', 'woocommerce-german-market' ), 'type' => 'title','desc' => $invoice_start_desc, 'id' => 'wp_wc_invoice_pdf_invoice_start' ),
			
			// subject
			array(
					'name' 		=> __( 'Subject', 'woocommerce-german-market' ),
					'desc' 		=> '<span class="desc">' . $placeholder_text . '</span>',
					'id'   		=> 'wp_wc_invoice_pdf_invoice_start_subject',
					'type' 		=> 'text',
					'default'  	=> __( 'Invoice for order {{order-number}} ({{order-date}})', 'woocommerce-german-market' ),
					'css'      	=> 'width: 500px;',
				),
			
			// margin after subject
			array(
					'name' 		=> __( 'Margin After Subject', 'woocommerce-german-market' ),
					'desc' 		=> $user_unit,
					'desc_tip'	=> __( 'Margin between the subject and the following content', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Margin between the subject and the following content', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_invoice_start_margin_after_subject',
					'type' 		=> 'text',
					'default'  	=> 0.75,
					'css'      	=> 'width: 100px;',
					'class'		=> 'german-market-unit',
				),

			// welcome text	
			array(
					'name' 		=> __( 'Welcome Text', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'You can add an optional text.', 'woocommerce-german-market' ),
					'tip'  		=> __( 'You can add an optional text.', 'woocommerce-german-market' ),
					'desc'		=> '<span class="desc">' . __( 'You can use the following placeholders: Customer\'s first name - <code>{{first-name}}</code>, customer\'s last name - <code>{{last-name}}</code>, Order Number - <code>{{order-number}}</code>, Order Date - <code>{{order-date}}</code>, Order Total - <code>{{order-total}}</code>, e.g. "Hello {{first-name}} {{last-name}}! Thank you for shopping.." You can use HTML, following tags are allowed: <code>&lt;br/&gt;</code>, <code>&lt;p&gt;</code>, <code>&lt;h1&gt;</code>, <code>&lt;h2&gt;</code>, <code>&lt;h3&gt;</code>, <code>&lt;em&gt;</code>, <code>&lt;ul&gt;</code>, <code>&lt;li&gt;</code>, <code>&lt;strong&gt;</code>, <code>&lt;i&gt;</code>, <code>&lt;u&gt;</code>, <code>&lt;ol&gt;</code>, <code>&lt;span&gt;</code>', 'woocommerce-german-market' ) . '</span>',
					'id'   		=> 'wp_wc_invoice_pdf_invoice_start_welcome_text',
					'css'  		=> 'min-width:500px; height: 100px;',
					'type' 		=> 'wp_wc_invoice_pdf_textarea',
					'default'  	=> ''
			),

			// avoid payment instrucions
			array(
				'name'		=> __( 'Avoid Output of Payment Instructions', 'woocommerce-german-market' ),
				'desc_tip' 	=> __( 'Payment instructions are displayed depending on the order status as they would be displayed in a customer email. If you activate this option, the action "woocommerce_email_before_order_table" will not be executed in the pdf content. So neither payment instructions are displayed, nor other texts that are added by this hook.', 'woocommerce-german-market' ),
				'id'   		=> 'wp_wc_invoice_pdf_avoid_payment_instructions',
				'type' 		=> 'wgm_ui_checkbox',
				'default'  	=> 'off',
			),

			array(
				'name'		=> __( 'Custom Paypment Information', 'woocommerce-german-market' ),
				'desc_tip' 	=> __( 'If you use this setting to output custom payment information, you can define the text to be displayed in the “Invoice PDF -> Payment information” field in the submenu "WooCommerce -> German Market -> General -> Payment settings".', 'woocommerce-german-market' ),
				'id'   		=> 'wp_wc_invoice_pdf_custom_payment_information',
				'type' 		=> 'select',
				'class' 	=> 'wc-enhanced-select',
				'css'		=> 'width: 75%;',
				'default'  	=> 'off',
				'options'	=> array(
					'off'		=> __( 'Do not show custom payment information', 'woocommerce-german-market' ),
					'before'	=> __( 'Show custom payment information before order table', 'woocommerce-german-market' ),
					'after'		=> __( 'Show custom payment information after order table', 'woocommerce-german-market' ),
				),
			),
			
			array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_invoice_start' ),
			
			array( 'title' => __( 'Table', 'woocommerce-german-market' ), 'type' => 'title','desc' => '', 'id' => 'wp_wc_invoice_pdf_content_table' ),
			
			// border color
			array(
					'name' 		=> __( 'Border Color', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Choose the border color of the table', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose the border color of the table', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_table_border_color',
					'type' 		=> 'color',
					'default'  	=> '#dddddd',
					'css'      	=> 'width: 100px;',
				),
			
			// border width
			array(
					'name' 		=> __( 'Border Width', 'woocommerce-german-market' ),
					'desc' 		=> 'px',
					'desc_tip'	=> __( 'Choose the border width of the table', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose the border width of the table', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_table_border_width',
					'type' 		=> 'text',
					'default'  	=> '1',
					'css'      	=> 'width: 100px;',
					'class'		=> 'german-market-unit',
				),
			
			// thick border width	
			array(
					'name' 		=> __( 'Thick Border Width', 'woocommerce-german-market' ),
					'desc' 		=> 'px',
					'desc_tip'	=> __( 'After the items have been listed and the output of the totals starts, there can be a thicker border to separate these sections', 'woocommerce-german-market' ),
					'tip'  		=> __( 'After the items have been listed and the output of the totals starts, there can be a thicker border to separate these sections', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_table_thick_border',
					'type' 		=> 'text',
					'default'  	=> '3',
					'css'      	=> 'width: 100px;',
					'class'		=> 'german-market-unit',
				),
			
			// cell padding	
			array(
					'name' 		=> __( 'Cell Padding', 'woocommerce-german-market' ),
					'desc' 		=> 'px',
					'desc_tip'	=> __( 'Space between cell wall and cell content', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Space between cell wall and cell content', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_table_cell_padding',
					'type' 		=> 'text',
					'default'  	=> '5',
					'css'      	=> 'width: 100px;',
					'class'		=> 'german-market-unit',
				),

			// show short description
			array(
					'name' 		=> __( 'Short Description', 'woocommerce-german-market' ),
					'desc_tip'	=> __( 'Show product short description or not', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Show product short description or not', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_show_short_description_in_invoice',
					'type' 		=> 'select',
					'default'  	=> 0,
					'css'      	=> 'width: 250px;',
					'options' 	=> array(
										true	=> __( 'Show short description', 'woocommerce-german-market' ),
										false	=> __( 'Don\'t show short description', 'woocommerce-german-market' )
									)
				),		
			
			// show pos.
			array(
					'name' 		=> __( 'Position Column', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Choose whether there is a column for Position (Pos.) in the table or not', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose whether there is a column for Position (Pos.) in the table or not', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_show_pos_in_invoice',
					'type' 		=> 'select',
					'default'  	=> 0,
					'css'      	=> 'width: 250px;',
					'options' 	=> array(
										true	=> __( 'Show Position', 'woocommerce-german-market' ),
										false	=> __( ' Don\'t show Position', 'woocommerce-german-market' )
									)
				),

			// show usk
			array(
					'name' 		=> __( 'SKU', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Choose whether there is a row for SKUs in the table or not', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose whether there is a row for SKUs in the table or not', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_show_sku_in_invoice',
					'type' 		=> 'select',
					'default'  	=> 1,
					'css'      	=> 'width: 250px;',
					'options' 	=> array(
										true	=> __( 'Show SKU', 'woocommerce-german-market' ),
										false	=> __( ' Don\'t show SKU', 'woocommerce-german-market' )
									)
				),

			// product image
			array(
					'name' 		=> __( 'Product Image', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Choose whether there is a row for the product image in the table or not', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose whether there is a row for the product image in the table or not', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_show_product_image_in_invoice_pdf',
					'type' 		=> 'select',
					'default'  	=> 0,
					'css'      	=> 'width: 250px;',
					'options' 	=> array(
										true	=> __( 'Show Product Image', 'woocommerce-german-market' ),
										false	=> __( 'Don\'t show Product Image', 'woocommerce-german-market' )
									)
				),

			array(
					'name' 		=> __( 'Product Image Width', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'If the product image is shown, you can choose the width in pixel of the image. The height is adjusted proportionally.', 'woocommerce-german-market' ),
					'tip'  		=> __( 'If the product image is shown, you can choose the width in pixel of the image. The height is adjusted proportionally.', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_product_image_width',
					'type' 		=> 'number',
					'default'  	=> 75,
					'css'      	=> 'width: 100px;',
					'custom_attributes' => array(
						'step'	=> 1,
						'min'	=> 1,
					),
					'desc' 		=> 'px',
					'class'		=> 'german-market-unit',
				),

			// Product Weight
			array(
					'name' 		=> __( 'Product Weight', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Choose whether there is a row for Weight in the table or not', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose whether there is a row for Weight in the table or not', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_show_weight_in_invoice',
					'type' 		=> 'select',
					'default'  	=> 0,
					'css'      	=> 'width: 250px;',
					'options' 	=> array(
										true	=> __( 'Show Weight', 'woocommerce-german-market' ),
										false	=> __( ' Don\'t show Weight', 'woocommerce-german-market' )
									)
				),

			// Product Dimensions
			array(
					'name' 		=> __( 'Product Dimensions', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Choose whether there is a row for Dimensions in the table or not', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose whether there is a row for Dimensions in the table or not', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_show_dimensions_in_invoice',
					'type' 		=> 'select',
					'default'  	=> 0,
					'css'      	=> 'width: 250px;',
					'options' 	=> array(
										true	=> __( 'Show Dimensions', 'woocommerce-german-market' ),
										false	=> __( ' Don\'t show Dimensions', 'woocommerce-german-market' )
									)
				),
				
			// show purchase note
			array(
					'name' 		=> __( 'Purchase Note', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Choose whether a purchase note is displayed after the product name', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose whether a purchase note is displayed after the product name', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_show_purchase_note_in_invoice',
					'type' 		=> 'select',
					'default'  	=> 0,
					'css'      	=> 'width: 250px;',
					'options' 	=> array(
										true	=> __( 'Show purchase note', 'woocommerce-german-market' ),
										false	=> __( 'Don\'t show purchase note', 'woocommerce-german-market' )
									)
				),
			
			array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_content_table' ),

			// shipping address
			array( 'title' => __( 'Shipping Address', 'woocommerce-german-market' ), 'type' => 'title','desc' => '', 'id' => 'wp_wc_invoice_pdf_shipping_address' ),

			array(
					'name' 		=> __( 'Show Shipping Address', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_show_shipping_address',
					'type' 		=> 'select',
					'default'  	=> 0,
					'css'      	=> 'width: 250px;',
					'options' 	=> array(
										'show'		=> __( 'Show shipping address', 'woocommerce-german-market' ),
										'show_only'	=> __( 'Show shipping address only if not equal to billing address', 'woocommerce-german-market' ),
										'hide'		=> __( 'Don\'t show shipping address', 'woocommerce-german-market' )
									)
				),

			array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_shipping_address' ),

			// notes
			array( 'title' => __( 'Notes', 'woocommerce-german-market' ), 'type' => 'title','desc' => '', 'id' => 'wp_wc_invoice_pdf_notes' ),

			array(
				'name'		=> __( 'Customer note', 'woocommerce-german-market' ),
				'desc_tip' 	=> __( 'Show Customer\'s notes about the order. This note has been entered by the customer during the checkout process.', 'woocommerce-german-market' ),
				'id'   		=> 'wp_wc_invoice_pdf_show_customers_note',
				'type' 		=> 'wgm_ui_checkbox',
				'default'  	=> 'off',
			),

			array(
				'name'		=> __( 'Order notes', 'woocommerce-german-market' ),
				'desc_tip' 	=> __( 'Order notes to customer. These notes has been entered by a shop manager in the backend of the order.', 'woocommerce-german-market' ),
				'id'   		=> 'wp_wc_invoice_pdf_show_order_notes',
				'type' 		=> 'wgm_ui_checkbox',
				'default'  	=> 'off',
			),

			array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_notes' ),
			
			array( 'title' => __( 'Text after Invoice Content', 'woocommerce-german-market' ), 'type' => 'title','desc' => '', 'id' => 'wp_wc_invoice_pdf_text_after_content_section' ),	
			
			array(
				'name' 		=> __( 'For digital content - Text Repetition.', 'woocommerce-german-market' ),
				'desc_tip' 	=> __( 'Shpw the Reptition of the Text "For digital content" in invoice pdf', 'woocommerce-german-market' ),
				'id'   		=> 'wp_wc_invoice_pdf_show_for_digital_content',
				'type' 		=> 'wgm_ui_checkbox',
				'default'  	=> 'on',
			),

			// text after invoice content
			array(
					'name' 		=> __( 'Text', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Here you can enter a optional text displayed after all content but before the fine print.', 'woocommerce-german-market' ),
					'desc'		=> '<span class="desc">' . __( 'You can use HTML, following tags are allowed: <code>&lt;br/&gt;</code>, <code>&lt;p&gt;</code>, <code>&lt;h1&gt;</code>, <code>&lt;h2&gt;</code>, <code>&lt;h3&gt;</code>, <code>&lt;em&gt;</code>, <code>&lt;ul&gt;</code>, <code>&lt;li&gt;</code>, <code>&lt;strong&gt;</code>, <code>&lt;i&gt;</code>, <code>&lt;u&gt;</code>, <code>&lt;ol&gt;</code>, <code>&lt;span&gt;</code>', 'woocommerce-german-market' ) . '</span>',
					'id'   		=> 'wp_wc_invoice_pdf_text_after_content',
					'type' 		=> 'wp_wc_invoice_pdf_textarea',
					'css'  		=> 'min-width:500px; height: 100px;',
					'default'  	=> '',
				),
			
			array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_text_after_content_section' ),
			
			array( 'title' => __( 'Page Numbers', 'woocommerce-german-market' ), 'type' => 'title','desc' => '', 'id' => 'wp_wc_invoice_pdf_content_page_numbers' ),
			
			// // page numbers output
			array(
					'name' 		=> __( 'Output', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Choose whether the current page number is outputed and if so where it is displayed', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose whether the current page number is outputed and if so where it is displayed', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_page_numbers_output',
					'type' 		=> 'select',
					'default'  	=> 'footer_bottom_right',
					'css'      	=> 'width: 250px;',
					'options' 	=> array(
										'none'					=> __( 'No output', 'woocommerce-german-market' ),
										'custom'				=> __( 'Use custom position', 'woocommerce-german-market' ),
										'header_top_left'		=> __( 'Header, top left', 'woocommerce-german-market' ),
										'header_top_center'		=> __( 'Header, top center', 'woocommerce-german-market' ),
										'header_top_right'		=> __( 'Header, top right', 'woocommerce-german-market' ),
										'header_bottom_left'	=> __( 'Header, bottom left', 'woocommerce-german-market' ),
										'header_bottom_center'	=> __( 'Header, bottom center', 'woocommerce-german-market' ),
										'header_bottom_right'	=> __( 'Header, bottom right', 'woocommerce-german-market' ),
										'footer_top_left'		=> __( 'Footer, top left', 'woocommerce-german-market' ),
										'footer_top_center'		=> __( 'Footer, top center', 'woocommerce-german-market' ),
										'footer_top_right'		=> __( 'Footer, top right', 'woocommerce-german-market' ),
										'footer_bottom_left'	=> __( 'Footer, bottom left', 'woocommerce-german-market' ),
										'footer_bottom_center'	=> __( 'Footer, bottom center', 'woocommerce-german-market' ),
										'footer_bottom_right'	=> __( 'Footer, bottom right', 'woocommerce-german-market' ),										
									)
				),
			
			// page numbers custom x-position
			array(
					'name' 		=> __( 'Custom x-Position', 'woocommerce-german-market' ),
					'desc' 		=> $user_unit,
					'desc_tip'	=> __( 'x-coordinate where the output starts if you have chosen "Use custom position" in the setting above', 'woocommerce-german-market' ),
					'tip'  		=> __( 'x-coordinate where the output starts if you have chosen "Use custom position" in the setting above', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_page_numbers_custom_x',
					'type' 		=> 'text',
					'default'  	=> '',
					'css'      	=> 'width: 100px;',
					'class'		=> 'german-market-unit',
				),	
				
			// page numbers custom y-position
			array(
					'name' 		=> __( 'Custom y-Position', 'woocommerce-german-market' ),
					'desc' 		=> $user_unit,
					'desc_tip'	=> __( 'y-coordinate where the output starts if you have chosen "Use custom position" in the setting above', 'woocommerce-german-market' ),
					'tip'  		=> __( 'y-coordinate where the output starts if you have chosen "Use custom position" in the setting above', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_page_numbers_custom_y',
					'type' 		=> 'text',
					'default'  	=> '',
					'css'      	=> 'width: 100px;',
					'class'		=> 'german-market-unit',
				),	
			
			// page numbers font family
			array(
					'name' 		=> __( 'Font', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Choose the general font used in the invoice', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose the general font used in the invoice', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_page_numbers_font',
					'type' 		=> 'select',
					'default'  	=> 'Helvetica',
					'css'      	=> 'width: 250px;',
					'options' 	=> $fonts
				),	
				
			// page numbers font size
			array(
					'name' 		=> __( 'Font Size', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Choose the general font size used in the invoice', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose the general font size used in the invoice', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_page_numbers_font_size',
					'type' 		=> 'select',
					'default'  	=> 6,
					'css'      	=> 'width: 100px;',
					'options' 	=> array_combine( self::$font_sizes, self::$font_sizes )
				),
			
			// page numbers font color
			array(
					'name' 		=> __( 'Text Color', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Choose the general text color used used in the invoice', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose the general text color used used in the invoice', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_page_numbers_color',
					'type' 		=> 'color',
					'default'  	=> '#000000',
					'css'      	=> 'width: 100px;',
				),	
			
			// page numbers text	
			array(
					'name' 		=> __( 'Text', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Text for the page numbers.', 'woocommerce-german-market' ),
					'desc'		=> '<span class="desc">' . __( 'You can use the following placeholders: Current page number: <code>{{current_page_number}}</code>, total number of pages: <code>{{total_page_number}}</code>', 'woocommerce-german-market' ) . '</span>',
					'id'   		=> 'wp_wc_invoice_pdf_page_numbers_text',
					'type' 		=> 'text',
					'default'  	=> __( 'Page {{current_page_number}} of {{total_page_number}}', 'woocommerce-german-market' ),
					'css'      	=> 'width: 400px;',
				),
			
			array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_content_page_numbers' ),
			
			array( 'title' => __( 'Fine Print', 'woocommerce-german-market' ), 'type' => 'title','desc' => '', 'id' => 'wp_wc_invoice_pdf_content_fine_print' ),		
			
			// show fine print table
			array(
					'name' 		=> __( 'Show Fine Print', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Choose whether fine print text is shown or not. If fine text is shown you can decide whether to use your custom text or the default fine print', 'woocommerce-german-market' ) . '<br />' . __( 'The default fine print is the same content as generated in your email footers by WooCommerce and third party plugins', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose whether fine print text is shown or not. If fine text is shown you can decide whether to use your custom text or the default fine print', 'woocommerce-german-market' ) . '.' . __( 'The default fine print is the same content as generated in your email footers by WooCommerce and third party plugins', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_show_fine_print',
					'type' 		=> 'select',
					'default'  	=> 'no',
					'css'      	=> 'width: 250px;',
					'options' 	=> array(
										'default'	=> __( 'Show default fine print', 'woocommerce-german-market' ),
										'custom'	=> __( 'Show custom fine print', 'woocommerce-german-market' ),
										'no'		=> __( 'Don\'t show fine print', 'woocommerce-german-market' )
									)
				),
			
			// page break
			array(
					'name' 		=> __( 'Position', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Show fine print straight after the last content or start a new page', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Show fine print straight after the last content or start a new page', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_fine_print_new_page',
					'type' 		=> 'select',
					'default'  	=> 1,
					'css'      	=> 'width: 250px;',
					'options' 	=> array(
										true	=> __( 'New page', 'woocommerce-german-market' ),
										false	=> __( 'Straight after last content', 'woocommerce-german-market' )
									)
				),
				
			// font family
			array(
					'name' 		=> __( 'Font', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Choose the font for this column', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose the font for this column', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_fine_print_font',
					'type' 		=> 'select',
					'default'  	=> 'Helvetica',
					'css'      	=> 'width: 250px;',
					'options' 	=> $fonts
				),	
				
			// font size
			array(
					'name' 		=> __( 'Font Size', 'woocommerce-german-market' ),
					'desc_tip'	=> __( 'Choose the font size for this column', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose the font size for this column', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_fine_print_font_size',
					'type' 		=> 'select',
					'default'  	=> 6,
					'css'      	=> 'width: 100px;',
					'options' 	=> array_combine( self::$font_sizes, self::$font_sizes )
				),
			
			// font color
			array(
					'name' 		=> __( 'Text Color', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Choose the text color used in this column', 'woocommerce-german-market' ),
					'tip'  		=> __( 'Choose the text color used in this column', 'woocommerce-german-market' ),
					'id'   		=> 'wp_wc_invoice_pdf_fine_print_color',
					'type' 		=> 'color',
					'default'  	=> '#000',
					'css'      	=> 'width: 100px;',
				),
			
			// fine print text	
			array(
					'name' 		=> __( 'Fine Print Text', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Here you can enter your custom fine print.', 'woocommerce-german-market' ),
					'desc'		=> '<span class="desc">' . __( 'You can use HTML, following tags are allowed: <code>&lt;br/&gt;</code>, <code>&lt;p&gt;</code>, <code>&lt;h1&gt;</code>, <code>&lt;h2&gt;</code>, <code>&lt;h3&gt;</code>, <code>&lt;em&gt;</code>, <code>&lt;ul&gt;</code>, <code>&lt;li&gt;</code>, <code>&lt;strong&gt;</code>, <code>&lt;i&gt;</code>, <code>&lt;u&gt;</code>, <code>&lt;ol&gt;</code>, <code>&lt;span&gt;</code>', 'woocommerce-german-market' ) . '</span>',
					'id'   		=> 'wp_wc_invoice_pdf_fine_print_custom_content',
					'type' 		=> 'wp_wc_invoice_pdf_textarea',
					'css'  		=> 'min-width: 500px; height: 100px;',
					'default'  	=> '',
				),	
					
			array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_content_fine_print' ),

			// net prices
			array( 'title' => __( 'Net Prices', 'woocommerce-german-market' ), 'type' => 'title','desc' => WGM_Ui::get_video_layer( 'https://s3.eu-central-1.amazonaws.com/marketpress-videos/german-market/nettopreise.mp4' ), 'id' => 'wp_wc_invoice_pdf_html_template' ),

			array(
				'name'		=> __( 'Product', 'woocommerce-german-market' ),
				'desc_tip' 	=> __( 'Show product prices splitted into net price, tax and gross prices.', 'woocommerce-german-market' ),
				'id'   		=> 'wp_wc_invoice_pdf_net_prices_product',
				'type' 		=> 'wgm_ui_checkbox',
				'default'  	=> 'off',
			),

			array(
				'name'		=> __( 'Total', 'woocommerce-german-market' ),
				'desc_tip' 	=> __( 'Show extra line for net price total.', 'woocommerce-german-market' ),
				'id'   		=> 'wp_wc_invoice_pdf_net_prices_total',
				'type' 		=> 'wgm_ui_checkbox',
				'default'  	=> 'off',
			),

			array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_html_template' ),
			
			// template file
			array( 'title' => __( 'HTML Template', 'woocommerce-german-market' ), 'type' => 'title','desc' => $template_file_desc, 'id' => 'wp_wc_invoice_pdf_html_template' ),	
			
			array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_html_template' ),
		);
