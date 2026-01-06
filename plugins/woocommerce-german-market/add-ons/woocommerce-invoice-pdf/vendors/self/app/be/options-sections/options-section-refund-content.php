<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//////////////////////////////////////////////////
// init
//////////////////////////////////////////////////

$file_name_placeholders = apply_filters( 'wp_wc_invoice_pdf_placeholders', 
											array( 
														'refund-id' => __( 'Refund ID', 'woocommerce-german-market' ), 
														'order-number' => __( 'Order number', 'woocommerce-german-market' ) 
											) 
							);

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

$refund_date_placholder = __( 'You can use this placeholder', 'woocommerce-german-market' ) . ': ' . __( 'Refund date:', 'woocommerce-german-market' ) . ' - <code>{{refund-date}}</code>';

// template files - init files and directories
$core_file_string		= 'woocommerce-invoice-pdf/templates/refund-content.php';
$theme_file_string		= 'yourtheme/woocommerce-invoice-pdf/refund-content.php';
$core_file				= untrailingslashit( plugin_dir_path( Woocommerce_Invoice_Pdf::$plugin_filename ) ) . DIRECTORY_SEPARATOR . 'vendors' . DIRECTORY_SEPARATOR . 'self' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'refund-content.php';
$theme_template_dir		= get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'woocommerce-invoice-pdf';
$theme_file				= $theme_template_dir  . DIRECTORY_SEPARATOR . 'refund-content.php';

// template file - move core file to template
if ( isset( $_GET[ 'gm_template_nonce' ] ) && wp_verify_nonce( $_GET[ 'gm_template_nonce' ], 'refund_content_template' ) ) {
	if ( isset( $_GET[ 'move_template' ] ) && ( 'refund-content' === $_GET[ 'move_template' ] ) ) {
		if (  wp_mkdir_p( dirname( get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'woocommerce-invoice-pdf' ) ) && ! file_exists( get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'woocommerce-invoice-pdf' . DIRECTORY_SEPARATOR . 'refund-content.php' ) ) {
			  $template_file	= $core_file;
			  // Copy template file
			  wp_mkdir_p( $theme_template_dir );
			  copy( $template_file, $theme_file );
			  echo '<div class="updated fade"><p>' . __( 'Template file copied to theme.', 'woocommerce-german-market' ) . '</p></div>';
		 }
					
	}

	// template file - delete theme file
	if ( isset( $_GET[ 'delete_template' ] ) && ( 'refund-content' === $_GET[ 'delete_template' ] ) ) {
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

		$href = add_query_arg( 'gm_template_nonce', wp_create_nonce( 'refund_content_template' ) );
		$href = add_query_arg( 'delete_template', 'refund-content', $href );
		$href = remove_query_arg( 'move_template', $href );

		$template_file_desc	 = '<a href="' . $href . '" class="delete_template button" style="float: right; margin-top: -4px; margin-left: 10px;">' . __( 'Delete template file', 'woocommerce-german-market' ) . '</a>' . $template_file_desc;
	}
} else {
	$template_file_desc		= __( 'To override and edit the template that contains the invoice content copy <code>[file_1]</code> to your theme folder: <code>[file_2]</code>.', 'woocommerce-german-market' );
	$template_file_desc		= str_replace( array( '[file_1]', '[file_2]' ), array( $core_file_string, $theme_file_string ), $template_file_desc );
	if ( ( is_dir( get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'woocommerce-infoice-pdf' . DIRECTORY_SEPARATOR ) && is_writable( get_stylesheet_directory()  . DIRECTORY_SEPARATOR . 'woocommerce-infoice-pdf' . DIRECTORY_SEPARATOR ) ) || is_writable( get_stylesheet_directory() ) ) { 
		
		$href = add_query_arg( 'gm_template_nonce', wp_create_nonce( 'refund_content_template' ) );
		$href = add_query_arg( 'move_template', 'refund-content', $href );
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
		
			array( 'title' 		=> __( 'General', 'woocommerce-german-market' ), 'type' => 'title','desc' => '', 'id' => 'wp_wc_refund_pdf_general' ),
						
				array(
						'name' 		=> __( 'File Name in Backend', 'woocommerce-german-market' ),
						'desc' 		=> '.pdf',
						'desc_tip'	=> __( 'Refund file name to use in backend.', 'woocommerce-german-market' ),
						'desc'		=> '<span class="desc">' . $placeholder_text . '</span>',
						'id'   		=> 'wp_wc_invoice_pdf_refund_file_name_backend',
						'type' 		=> 'text',
						'default'  	=> __( 'Refund-{{refund-id}}-for-order-{{order-number}}', 'woocommerce-german-market' ),
						'css'      	=> 'width: 500px;',
					),	
				
				array(
						'name' 		=> __( 'File Name in Frontend', 'woocommerce-german-market' ),
						'desc' 		=> '.pdf',
						'desc_tip'	=> __( 'Refund file name to use in frontend for your customer.', 'woocommerce-german-market' ),
						'desc'		=> '<span class="desc">' . $placeholder_text . '</span>',
						'id'   		=> 'wp_wc_invoice_pdf_refund_file_name_frontend',
						'type' 		=> 'text',
						'default'  	=>  get_bloginfo( 'name' ) . '-' . __( 'Refund-{{refund-id}}-for-order-{{order-number}}', 'woocommerce-german-market' ),
						'css'      	=> 'width: 500px;',
					),

			array( 'type' => 'sectionend', 'id' => 'wp_wc_refund_pdf_general' ),

			array( 'title' 		=> __( 'Refund Content', 'woocommerce-german-market' ), 'type' => 'title','desc' => '', 'id' => 'wp_wc_refund_pdf_content' ),

				array(
						'name' 		=> __( 'Subject line 1 (big)', 'woocommerce-german-market' ),
						'desc'		=> '<span class="desc">' . $placeholder_text . '</span>',
						'id'   		=> 'wp_wc_invoice_pdf_refund_start_subject_big',
						'type' 		=> 'text',
						'default'  	=> __( 'Refund {{refund-id}}', 'woocommerce-german-market' ),
						'css'      	=> 'width: 500px;',
				),

				array(
						'name' 		=> __( 'Subject line 2 (small)', 'woocommerce-german-market' ),
						'desc_tip' 	=> __( 'This line has the same font-size as the general text.', 'woocommerce-german-market' ),
						'desc'		=> '<span class="desc">' . $placeholder_text . '</span>',
						'id'   		=> 'wp_wc_invoice_pdf_refund_start_subject_small',
						'type' 		=> 'text',
						'default'  	=> __( 'For order {{order-number}}', 'woocommerce-german-market' ),
						'css'      	=> 'width: 500px;',
				),

				array(
						'name' 		=> __( 'Refund Date', 'woocommerce-german-market' ),
						'desc_tip' 	=> __( 'The refund date can be outputed right to the subject in a smaller font size, text aligned right.', 'woocommerce-german-market' ),
						'desc'		=> '<span class="desc">' . __( 'You can use the following HTML tag: <code>&lt;br /&gt;</code>', 'woocommerce-german-market' ) . '<br />' . $refund_date_placholder . '</span>',
						'id'   		=> 'wp_wc_invoice_pdf_refund_start_refund_date',
						'type' 		=> 'wp_wc_invoice_pdf_textarea',
						'default'  	=> __( 'Refund date<br />{{refund-date}}', 'woocommerce-german-market' ),
						'css'      	=> 'width: 500px; resize: none; height: 2em;',
				),

				// welcome text	
				array(
						'name' 		=> __( 'Welcome Text', 'woocommerce-german-market' ),
						'desc_tip' 	=> __( 'You can add an optional text.', 'woocommerce-german-market' ),
						'tip'  		=> __( 'You can add an optional text.', 'woocommerce-german-market' ),
						'desc'		=> '<span class="desc">' . __( 'You can use the following placeholders: Customer\'s first name - <code>{{first-name}}</code>, customer\'s last name - <code>{{last-name}}</code>, Order Number - <code>{{order-number}}</code>, Order Date - <code>{{order-date}}</code>, Order Total - <code>{{order-total}}</code>, e.g. "Hello {{first-name}} {{last-name}}! Thank you for shopping.." You can use HTML, following tags are allowed: <code>&lt;br/&gt;</code>, <code>&lt;p&gt;</code>, <code>&lt;h1&gt;</code>, <code>&lt;h2&gt;</code>, <code>&lt;h3&gt;</code>, <code>&lt;em&gt;</code>, <code>&lt;ul&gt;</code>, <code>&lt;li&gt;</code>, <code>&lt;strong&gt;</code>, <code>&lt;i&gt;</code>, <code>&lt;u&gt;</code>, <code>&lt;ol&gt;</code>, <code>&lt;span&gt;</code>', 'woocommerce-german-market' ) . '</span>',
						'id'   		=> 'wp_wc_invoice_pdf_refund_start_welcome_text',
						'css'  		=> 'min-width:500px; height: 100px;',
						'type' 		=> 'wp_wc_invoice_pdf_textarea',
						'default'  	=> ''
				),

				array(
					'name' 		=> __( 'Text after Invoice Content', 'woocommerce-german-market' ),
					'desc_tip' 	=> __( 'Here you can enter a optional text displayed after all content but before the fine print.', 'woocommerce-german-market' ),
					'desc'		=> '<span class="desc">' . __( 'You can use HTML, following tags are allowed: <code>&lt;br/&gt;</code>, <code>&lt;p&gt;</code>, <code>&lt;h1&gt;</code>, <code>&lt;h2&gt;</code>, <code>&lt;h3&gt;</code>, <code>&lt;em&gt;</code>, <code>&lt;ul&gt;</code>, <code>&lt;li&gt;</code>, <code>&lt;strong&gt;</code>, <code>&lt;i&gt;</code>, <code>&lt;u&gt;</code>, <code>&lt;ol&gt;</code>, <code>&lt;span&gt;</code>', 'woocommerce-german-market' ) . '</span>',
					'id'   		=> 'wp_wc_invoice_pdf_refund_text_after_content',
					'type' 		=> 'wp_wc_invoice_pdf_textarea',
					'css'  		=> 'min-width:500px; height: 100px;',
					'default'  	=> '',
				),

			array( 'type' => 'sectionend', 'id' => 'wp_wc_refund_pdf_content' ),

			array( 'title' => __( 'HTML Template', 'woocommerce-german-market' ), 'type' => 'title','desc' => $template_file_desc, 'id' => 'wp_wc_invoice_pdf_html_template' ),	
			
			array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_html_template' ),
		);
