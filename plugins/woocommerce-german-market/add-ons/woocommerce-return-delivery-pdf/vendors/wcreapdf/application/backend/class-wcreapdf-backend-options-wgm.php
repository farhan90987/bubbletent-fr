<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCREAPDF_Backend_Options_WGM' ) ) {

	/**
	* admin setting page in backend wgm 3.1
	*
	* @class WP_WC_Invoice_Pdf_Backend_Options_WGM
	* @version 1.0
	* @category	Class
	*/
	class WCREAPDF_Backend_Options_WGM {

		/**
		* Backend Settings German Market 3.1
		*
		* wp-hook woocommerce_de_ui_options_global
		* @param Array $items
		* @return Array
		*/
		public static function menu( $items ) {

			$items[ 250 ] = array( 
				'title'		=> __( 'Return / Delivery Note', 'woocommerce-german-market' ),
				'slug'		=> 'preferences-wcreapdf',
				
				'submenu'	=> array(

					array(
						'title'		=> __( 'Return Note - Pdf settings', 'woocommerce-german-market' ),
						'slug'		=> 'pdf_settings',
						'callback'	=> array( __CLASS__, 'render_menu_pdf_settings' ),
						'options'	=> 'yes'
					),

					array(
						'title'		=> __( 'Delivery Note - Pdf settings', 'woocommerce-german-market' ),
						'slug'		=> 'pdf_settings_delivery_note',
						'callback'	=> array( __CLASS__, 'render_menu_pdf_settings_delivery_note' ),
						'options'	=> 'yes'
					),

					array(
						'title'		=> __( 'Email attachments', 'woocommerce-german-market' ),
						'slug'		=> 'email_settings',
						'callback'	=> array( __CLASS__, 'render_menu_email_settings' ),
						'options'	=> 'yes'
					),

					array(
						'title'		=> __( 'Return Note - My Account Page', 'woocommerce-german-market' ),
						'slug'		=> 'my_account_page',
						'callback'	=> array( __CLASS__, 'render_menu_my_account_page' ),
						'options'	=> 'yes'
					),

					array(
						'title'		=> __( 'Test pdf', 'woocommerce-german-market' ),
						'slug'		=> 'test_pdf',
						'callback'	=> array( __CLASS__, 'render_menu_test_pdf' ),
						'options'	=> 'yes'
					),

				)
			);

			return $items;

		}

		/**
		* Render Options for pdf settings - Return note
		* 
		* @access public
		* @return void
		*/
		public static function render_menu_pdf_settings() {

			$fonts		= array(
							'Helvetica'	=> __( 'Helvetica', 'woocommerce-german-market' ),
							'Courier'  	=> __( 'Courier', 'woocommerce-german-market' ),
							'Times' 		=> __( 'Times', 'woocommerce-german-market' ),
							);
			$fonts		= apply_filters( 'wcreapdf_fonts', $fonts );	
			
			$image_upload_button = '<button type="button" class="button-secondary" id="' . WCREAPDF_Helper::get_wcreapdf_optionname( 'image_upload_button' ) . '" style="margin: 3px 0;">' . __( 'Image upload', 'woocommerce-german-market' ) . '</button>';
			$image_remove_button = '<button type="button" class="button-secondary" id="' . WCREAPDF_Helper::get_wcreapdf_optionname( 'image_remove_button' ) . '" style="margin: 3px 3px;">' . __( 'Remove image', 'woocommerce-german-market' ) . '</button>';

			$placeholders = __( 'Order Number: <code>{{order-number}}</code>, Order Date: <code>{{order-date}}</code>', 'woocommerce-german-market' );
			$placeholders = apply_filters( 'wcreapdf_pdf_placeholders_backend_string', $placeholders );

			$placeholders_remark = __( 'Customer\'s first name - <code>{{first-name}}</code>, customer\'s last name - <code>{{last-name}}</code>', 'woocommerce-german-market' ) . ', ' . __( 'Order Number: <code>{{order-number}}</code>, Order Date: <code>{{order-date}}</code>', 'woocommerce-german-market' );
			$placeholders_remark = apply_filters( 'wcreapdf_pdf_placeholders_backend_string', $placeholders_remark );
			
			$options	= array(				
							'section_title' => array(
								'name' 	=> __( 'Return Note - Pdf settings', 'woocommerce-german-market' ),
								'type' 	=> 'title',
								'id' 	=> 'woocomerce_wcreapdf_wgm_pdf'
							),

							array(
								'name' => __( 'Backend Download', 'woocommerce-german-market' ),
								'desc_tip' => __( 'Activate or deactivate the Download Option to download the return delivery note.', 'woocommerce-german-market' ),
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_backend_download' ),
								'type' => 'wgm_ui_checkbox',
								'default'  => 'on'
							),
							
							array(
								'name' => __( 'File name', 'woocommerce-german-market' ),
								'desc_tip' => __( 'Choose a file name for the attached return delivery note without file extension (.pdf)', 'woocommerce-german-market' ),
								'tip'  => __( 'Choose a file name for the attached return delivery note without file extension (.pdf)', 'woocommerce-german-market' ),
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_file_name' ),
								'type' => 'text',
								'default'  => __( 'Retoure', 'woocommerce-german-market' ),
								'css'      => 'min-width:300px;',
							),									
								
							array(
								'name' => __( 'Author', 'woocommerce-german-market' ),
								'desc_tip' =>  __( 'Choose an author for the attached return delivery note', 'woocommerce-german-market' ),
								'tip'  => __( 'Choose an author for the attached return delivery note', 'woocommerce-german-market' ),
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_author' ),
								'type' => 'text',
								'default'  => get_bloginfo( 'name' ),
								'css'      => 'min-width:300px;',
							),		
							
							array(
								'name' => __( 'Title', 'woocommerce-german-market' ),
								'desc_tip' =>  __( 'Choose a title for the attached return delivery note', 'woocommerce-german-market' ),
								'tip'  => __( 'Choose a title for the attached return delivery note', 'woocommerce-german-market' ),
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_title' ),
								'type' => 'text',
								'default'  => __( 'Retoure', 'woocommerce-german-market' ) . ' - ' . get_bloginfo( 'name' ),
								'css'      => 'min-width:300px;',
							),
							
							array(
								'name' => __( 'Font', 'woocommerce-german-market' ),
								'desc_tip' =>  __( 'Choose the font used in the attached return delivery note', 'woocommerce-german-market' ),
								'tip'  => __( 'Choose the font used in the attached return delivery note', 'woocommerce-german-market' ),
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_font' ),
								'type' => 'select',
								'default'  => 'Times',
								'options' => $fonts
							),
							
							array(
								'name' => __( 'Shop name', 'woocommerce-german-market' ),
								'desc_tip' =>  __( 'Choose your shop name displayed at the beginning of the return delivery note', 'woocommerce-german-market' ),
								'tip'  => __( 'Choose your shop name displayed at the beginning of the return delivery note', 'woocommerce-german-market' ),
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_shop_name' ),
								'type' => 'text',
								'default'  => get_bloginfo( 'name' ),
								'css'      => 'min-width:300px;',
							),
													
							array(
								'name' => __( 'Header image', 'woocommerce-german-market' ),
								'desc' =>  $image_upload_button . $image_remove_button . '<br /><br />' . __( 'Choose an image that will be displayed in the upper left corner of the pdf instead of the shop name. Click the upload button to use the media uploader. The image will have a height of 2.0 cm and the width is automatically calculated. Supported file formats are JPEG, PNG and GIF. The GD extension is required for GIF. Transparency is supported. Interlacing is not supported. Please notice that your image will be embedded in your return delivery note. An image with a large file size will cause an return delivery note with a large file size and creating the pdf will take longer. So it is recommended to choose an image with a small file size.', 'woocommerce-german-market' ),
								'tip'  => __( 'Choose an image that will be displayed in the upper left corner of the pdf instead of the shop name. Click the upload button to use the media uploader. The image will have a height of 2.0 cm and the width is automatically calculated. Supported file formats are JPEG, PNG and GIF. The GD extension is required for GIF. Transparency is supported. Interlacing is not supported. Please notice that your image will be embedded in your return delivery note. An image with a large file size will cause an return delivery note with a large file size and creating the pdf will take longer. So it is recommended to choose an image with a small file size.', 'woocommerce-german-market' ),
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_logo_url' ),
								'type' => 'text',
								'default'  => '',
								'css'      => 'min-width:500px;',
								'custom_attributes' => array( 'readonly' => 'readonly' )
							),
							
							array(
								'name' => __( 'Shop address', 'woocommerce-german-market' ),
								'desc_tip' => __( 'Set the address of your shop that will be used to ship the return delivery to', 'woocommerce-german-market' ),
								'tip'  => __( 'Set the address of your shop that will be used to ship the return delivery to', 'woocommerce-german-market' ),
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_address' ),
								'css'  => 'min-width:500px; height: 100px;',
								'type' => 'wcreapdf_textarea',
								'default'  => ''
							),

							array(
								'name' => __( 'Small Headline', 'woocommerce-german-market' ),
								'desc_tip' =>  __( 'Set the text of the small headline after the big headline "Return Note:"', 'woocommerce-german-market' ),
								'desc'  => __( 'You can use the following placeholders:', 'woocommerce-german-market' ) . ' ' . $placeholders,
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_shop_small_headline' ),
								'type' => 'text',
								'default'  => __( 'Order: {{order-number}} ({{order-date}})', 'woocommerce-german-market' ),
								'css'      => 'min-width:500px;',
							),

							// Quantity: Exclude or include refund quantitites
							array(
									'name' 		=> __( 'Item Quantity', 'woocommerce-german-market' ),
									'id'   		=> WCREAPDF_Helper::get_wcreapdf_optionname( 'quantity_refund' ),
									'type' 		=> 'select',
									'default'  	=> 'exclude',
									'css'      	=> 'width: 300px;',
									'options' 	=> array(
														'exclude'	=> __( 'Exclude refunded quantities', 'woocommerce-german-market' ),
														'inlcude'	=> __( 'Include refunded quantities', 'woocommerce-german-market' )
													)
							),

							// show usk
							array(
									'name' 		=> __( 'SKU', 'woocommerce-german-market' ),
									'desc_tip' 	=> __( 'Choose whether there is a row for SKUs in the table or not', 'woocommerce-german-market' ),
									'tip'  		=> __( 'Choose whether there is a row for SKUs in the table or not', 'woocommerce-german-market' ),
									'id'   		=> WCREAPDF_Helper::get_wcreapdf_optionname( 'show_sku' ),
									'type' 		=> 'select',
									'default'  	=> 1,
									'css'      	=> 'width: 300px;',
									'options' 	=> array(
														true	=> __( 'Show SKU', 'woocommerce-german-market' ),
														false	=> __( ' Don\'t show SKU', 'woocommerce-german-market' )
													)
							),

							// show short description
							array(
								'name' 		=> __( 'Short Description', 'woocommerce-german-market' ),
								'desc_tip'	=> __( 'Show product short description or not', 'woocommerce-german-market' ),
								'tip'  		=> __( 'Show product short description or not', 'woocommerce-german-market' ),
								'id'   		=> WCREAPDF_Helper::get_wcreapdf_optionname( 'show_short_description' ),
								'type' 		=> 'select',
								'default'  	=> 0,
								'css'      	=> 'width: 300px;',
								'options' 	=> array(
													true	=> __( 'Show short description', 'woocommerce-german-market' ),
													false	=> __( 'Don\'t show short description', 'woocommerce-german-market' )
												)
							),

							array(
								'name' => __( 'Remark', 'woocommerce-german-market' ),
								'desc_tip' => __( 'Enter a remark displayed in the return delivery note', 'woocommerce-german-market' ),
								'tip'  => __( 'Enter a remark displayed in the return delivery note', 'woocommerce-german-market' ),
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_remark' ),
								'css'  => 'min-width:500px; height: 100px;',
								'type' => 'wcreapdf_textarea',
								'default'  => '',
								'desc'  => __( 'You can use the following placeholders:', 'woocommerce-german-market' ) . ' ' . $placeholders_remark,
							),
							
							array(
								'name' => __( 'Return delivery reasons', 'woocommerce-german-market' ),
								'desc_tip' => __( 'Enter possible return delivery reasons, enter them semicolon separated', 'woocommerce-german-market' ),
								'tip'  => __( 'Enter possible return delivery reasons, enter them semicolon separated', 'woocommerce-german-market' ),
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_reasons' ),
								'css'  => 'min-width:500px; height: 100px;',
								'type' => 'wcreapdf_textarea',
								'default'  => ''
							),
							
							array(
								'name' => __( 'Pdf footer', 'woocommerce-german-market' ),
								'desc_tip' => __( 'Enter your footer for the return delivery note', 'woocommerce-german-market' ),
								'tip'  => __( 'Enter your footer for the return delivery note', 'woocommerce-german-market' ),
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_footer' ),
								'css'  => 'min-width:500px; height: 100px;',
								'type' => 'wcreapdf_textarea',
								'default'  => ''
							),
							
							array(
								'name' => __( 'Footer text alignment', 'woocommerce-german-market' ),
								'desc_tip' =>  __( 'Choose alignment of your footer text in the return delivery note', 'woocommerce-german-market' ),
								'tip'  => __( 'Choose alignment of your footer text in the return delivery note', 'woocommerce-german-market' ),
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_footer_alignment' ),
								'type' => 'select',
								'default'	=> 'C',
								'options'	=> array(
													'L'		=> __( 'Left', 'woocommerce-german-market' ),
													'C' 	=> __( 'Center', 'woocommerce-german-market' ),
													'R' 	=> __( 'Right', 'woocommerce-german-market' )
												)
							)
						);	

			$extra_pdf_options		= apply_filters( 'wcreapdf_pdf_options', array() );			// add your own pdf options
			$options				= array_merge( $options, $extra_pdf_options );
			$options[] 				= array( 'type' => 'sectionend', 'id' => 'woocomerce_wcreapdf_wgm_pdf' );
			return( $options );
			
		}

		/**
		* Render Options for pdf settings - Delivery note
		* 
		* @access public
		* @return void
		*/
		public static function render_menu_pdf_settings_delivery_note() {

			$fonts		= array(
							'Helvetica'	=> __( 'Helvetica', 'woocommerce-german-market' ),
							'Courier'  	=> __( 'Courier', 'woocommerce-german-market' ),
							'Times' 		=> __( 'Times', 'woocommerce-german-market' ),
							);
			$fonts		= apply_filters( 'wcreapdf_fonts', $fonts );	
			
			$image_upload_button = '<button type="button" class="button-secondary" id="' . WCREAPDF_Helper::get_wcreapdf_optionname( 'image_upload_button' ) . '" style="margin: 3px 0;">' . __( 'Image upload', 'woocommerce-german-market' ) . '</button>';
			$image_remove_button = '<button type="button" class="button-secondary" id="' . WCREAPDF_Helper::get_wcreapdf_optionname( 'image_remove_button' ) . '" style="margin: 3px 3px;">' . __( 'Remove image', 'woocommerce-german-market' ) . '</button>';

			$placeholders = __( 'Order Number: <code>{{order-number}}</code>, Order Date: <code>{{order-date}}</code>', 'woocommerce-german-market' );
			$placeholders = apply_filters( 'wcreapdf_pdf_placeholders_backend_string', $placeholders );

			$placeholders_remark = __( 'Customer\'s first name - <code>{{first-name}}</code>, customer\'s last name - <code>{{last-name}}</code>', 'woocommerce-german-market' )  . ', ' . __( 'Cusomter\'s phone number - <code>{{phone}}</code>, Cusomter\'s email - <code>{{email}}</code>, Total weight of order: <code>{{total-weight}}</code>', 'woocommerce-german-market' );;
			$placeholders_remark = apply_filters( 'wcreapdf_pdf_placeholders_backend_string', $placeholders_remark );
			
			$options	= array(				
							'section_title' => array(
								'name' 	=> __( 'Delivery Note - Pdf settings', 'woocommerce-german-market' ),
								'type' 	=> 'title',
								'id' 	=> 'woocomerce_wcreapdf_wgm_pdf_delivery'
							),
							
							array(
								'name' => __( 'Backend Download', 'woocommerce-german-market' ),
								'desc_tip' => __( 'Activate or deactivate the Download Option to download the Delivery Note.', 'woocommerce-german-market' ),
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_delivery_backend_download' ),
								'type' => 'wgm_ui_checkbox',
								'default'  => 'on'
							),

							array(
								'name' => __( 'File name', 'woocommerce-german-market' ),
								'desc_tip' => __( 'Choose a file name for the attached delivery note without file extension (.pdf)', 'woocommerce-german-market' ),
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_file_name_delivery' ),
								'type' => 'text',
								'default'  => __( 'Delivery-Note', 'woocommerce-german-market' ),
								'css'      => 'min-width:300px;',
							),									
								
							array(
								'name' => __( 'Author', 'woocommerce-german-market' ),
								'desc_tip' =>  __( 'Choose an author for the attached delivery note', 'woocommerce-german-market' ),
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_author_delivery' ),
								'type' => 'text',
								'default'  => get_bloginfo( 'name' ),
								'css'      => 'min-width:300px;',
							),		
							
							array(
								'name' => __( 'Title', 'woocommerce-german-market' ),
								'desc_tip' =>  __( 'Choose a title for the attached delivery note', 'woocommerce-german-market' ),
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_title_delivery' ),
								'type' => 'text',
								'default'  => __( 'Delivery Note', 'woocommerce-german-market' ) . ' - ' . get_bloginfo( 'name' ),
								'css'      => 'min-width:300px;',
							),
							
							array(
								'name' => __( 'Font', 'woocommerce-german-market' ),
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_font_delivery' ),
								'type' => 'select',
								'default'  => 'Times',
								'options' => $fonts
							),
							
							array(
								'name' => __( 'Shop name', 'woocommerce-german-market' ),
								'desc_tip' =>  __( 'Choose your shop name displayed at the beginning of the delivery note', 'woocommerce-german-market' ),
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_shop_name_delivery' ),
								'type' => 'text',
								'default'  => get_bloginfo( 'name' ),
								'css'      => 'min-width:300px;',
							),
													
							array(
								'name' => __( 'Header image', 'woocommerce-german-market' ),
								'desc' =>  $image_upload_button . $image_remove_button . '<br /><br />' . __( 'Choose an image that will be displayed in the upper left corner of the pdf instead of the shop name. Click the upload button to use the media uploader. The image will have a height of 2.0 cm and the width is automatically calculated. Supported file formats are JPEG, PNG and GIF. The GD extension is required for GIF. Transparency is supported. Interlacing is not supported. Please notice that your image will be embedded in your delivery note. An image with a large file size will cause an delivery note with a large file size and creating the pdf will take longer. So it is recommended to choose an image with a small file size.', 'woocommerce-german-market' ),
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_logo_url_delivery' ),
								'type' => 'text',
								'default'  => '',
								'css'      => 'min-width:500px;',
								'custom_attributes' => array( 'readonly' => 'readonly' )
							),
							
							array(
								'name' => __( 'Shop address', 'woocommerce-german-market' ),
								'desc_tip' => __( 'Set the address of your shop that will be used to ship the delivery to', 'woocommerce-german-market' ),
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_address_delivery' ),
								'css'  => 'min-width:500px; height: 100px;',
								'type' => 'wcreapdf_textarea',
								'default'  => ''
							),

							array(
								'name'		=> __( 'Shop Adress (Consignor) Position', 'woocommerce-german-market' ),
								'desc_tip'	=> __( 'By default, the shop adress (the consignor) will be displayed to the right of the recipient. If you set this option to "Above the recipient", the shop adress will be displayed in one line above the shop adress so you can use the delivery note in an envelope with window.', 'woocommerce-german-market' ),
								'type' 		=> 'select',
								'default'	=> 'right',
								'options'	=> array(
													'right'		=> __( 'To the right of the Recipient', 'woocommerce-german-market' ),
													'above' 	=> __( 'Above the recipient', 'woocommerce-german-market' ),
												),
								'id'   		=> WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_shop_adress_position_delivery' ),
								'css'		=> 'width: 300px;',
							),

							array(
								'name' => __( 'Small Headline', 'woocommerce-german-market' ),
								'desc_tip' =>  __( 'Set the text of the small headline after the big headline "Delivery Note:"', 'woocommerce-german-market' ),
								'desc'  => __( 'You can use the following placeholders:', 'woocommerce-german-market' ) . ' ' . $placeholders,
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_shop_small_headline_delivery' ),
								'type' => 'text',
								'default'  => __( 'Order: {{order-number}} ({{order-date}})', 'woocommerce-german-market' ),
								'css'      => 'min-width:500px;',
							),

							// Quantity: Exclude or include refund quantitites
							array(
									'name' 		=> __( 'Item Quantity', 'woocommerce-german-market' ),
									'id'   		=> WCREAPDF_Helper::get_wcreapdf_optionname( 'quantity_refund_delivery' ),
									'type' 		=> 'select',
									'default'  	=> 'exclude',
									'css'      	=> 'width: 300px;',
									'options' 	=> array(
														'exclude'	=> __( 'Exclude refunded quantities', 'woocommerce-german-market' ),
														'inlcude'	=> __( 'Include refunded quantities', 'woocommerce-german-market' )
													)
							),

							// show usk
							array(
									'name' 		=> __( 'SKU', 'woocommerce-german-market' ),
									'desc_tip' 	=> __( 'Choose whether there is a row for SKUs in the table or not', 'woocommerce-german-market' ),
									'tip'  		=> __( 'Choose whether there is a row for SKUs in the table or not', 'woocommerce-german-market' ),
									'id'   		=> WCREAPDF_Helper::get_wcreapdf_optionname( 'sku_delivery' ),
									'type' 		=> 'select',
									'default'  	=> 1,
									'css'      	=> 'width: 300px;',
									'options' 	=> array(
														true	=> __( 'Show SKU', 'woocommerce-german-market' ),
														false	=> __( ' Don\'t show SKU', 'woocommerce-german-market' )
													)
								),

							array(
									'name' 		=> __( 'Product Weight', 'woocommerce-german-market' ),
									'desc_tip' 	=> __( 'Choose whether there is a row for Weight in the table or not', 'woocommerce-german-market' ),
									'tip'  		=> __( 'Choose whether there is a row for Weight in the table or not', 'woocommerce-german-market' ),
									'id'   		=> WCREAPDF_Helper::get_wcreapdf_optionname( 'weight_delivery' ),
									'type' 		=> 'select',
									'default'  	=> 1,
									'css'      	=> 'width: 300px;',
									'options' 	=> array(
														true	=> __( 'Show Weight', 'woocommerce-german-market' ),
														false	=> __( ' Don\'t show Weight', 'woocommerce-german-market' )
													)
								),
							
							array(
								'name' 		=> __( 'Short Description', 'woocommerce-german-market' ),
								'desc_tip'	=> __( 'Show product short description or not', 'woocommerce-german-market' ),
								'tip'  		=> __( 'Show product short description or not', 'woocommerce-german-market' ),
								'id'   		=> WCREAPDF_Helper::get_wcreapdf_optionname( 'show_short_description_delivery' ),
								'type' 		=> 'select',
								'default'  	=> 0,
								'css'      	=> 'width: 300px;',
								'options' 	=> array(
													true	=> __( 'Show short description', 'woocommerce-german-market' ),
													false	=> __( 'Don\'t show short description', 'woocommerce-german-market' )
												)
							),

							// delivery date
							array(
								'name' => __( 'Show Delivey Date', 'woocommerce-german-market' ),
								'desc_tip'  => __( 'If activated, the delivery date will be shown after the products table. The delivery date can only be shown if the order is marked as completed. The date when the order has been completed will be uses as the delivery date.', 'woocommerce-german-market' ),
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'show_delivery_date' ),
								'type' 		=> 'select',
								'default'  	=> 0,
								'css'      	=> 'width: 300px;',
								'options' 	=> array(
													true	=> __( 'Show delivery date', 'woocommerce-german-market' ),
													false	=> __( 'Don\'t show delivery date', 'woocommerce-german-market' )
												)
							),

							array(
								'name' => __( 'Remark', 'woocommerce-german-market' ),
								'desc_tip' => __( 'Enter a remark displayed in the delivery note', 'woocommerce-german-market' ),
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_remark_delivery' ),
								'css'  => 'min-width:500px; height: 100px;',
								'type' => 'wcreapdf_textarea',
								'default'  => '',
								'desc'  => __( 'You can use the following placeholders:', 'woocommerce-german-market' ) . ' ' . $placeholders_remark,
							),

							array(
								'name'		=> __( 'Customer note', 'woocommerce-german-market' ),
								'desc_tip' 	=> __( 'Show Customer\'s notes about the order. This note has been entered by the customer during the checkout process.', 'woocommerce-german-market' ),
								'id'   		=> WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_show_customers_note' ),
								'type' 		=> 'wgm_ui_checkbox',
								'default'  	=> 'off',
							),

							array(
								'name' => __( 'Pdf footer', 'woocommerce-german-market' ),
								'desc_tip' => __( 'Enter your footer for the delivery note', 'woocommerce-german-market' ),
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_footer_delivery' ),
								'css'  => 'min-width:500px; height: 100px;',
								'type' => 'wcreapdf_textarea',
								'default'  => ''
							),
							
							array(
								'name' => __( 'Footer text alignment', 'woocommerce-german-market' ),
								'desc_tip' =>  __( 'Choose alignment of your footer text in the delivery note', 'woocommerce-german-market' ),
								'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_footer_alignment_delivery' ),
								'type' => 'select',
								'default'	=> 'C',
								'options'	=> array(
													'L'		=> __( 'Left', 'woocommerce-german-market' ),
													'C' 	=> __( 'Center', 'woocommerce-german-market' ),
													'R' 	=> __( 'Right', 'woocommerce-german-market' )
												)
							)
						);	

			$extra_pdf_options		= apply_filters( 'wcreapdf_pdf_options', array() );			// add your own pdf options
			$options				= array_merge( $options, $extra_pdf_options );
			$options[] 				= array( 'type' => 'sectionend', 'id' => 'woocomerce_wcreapdf_wgm_pdf_delivery' );
			return( $options );

		}

		/**
		 * Get default values for legacy option, merged to new option
		 * 
		 * @return Array
		 */
		public static function get_return_note_email_attachment_default() {

			$default = array();

			$legacy_options = array(
				'customer_invoice',
				'customer_order_confirmation',
				'new_order',
				'customer_on_hold_order',
				'customer_processing_order',
				'customer_completed_order',
			);

			foreach ( WGM_Compatibilities::get_subscriptions_emails() as $key => $a_mail ) {
				$legacy_options[] = $a_mail->id;
			}

			foreach ( $legacy_options as $legacy_option ) {
				if ( 'on' === get_option( WCREAPDF_Helper::get_wcreapdf_optionname( $legacy_option ), 'off' ) ) {
					$default[] = $legacy_option;
				}
			}

			return $default;
		}

		/**
		* Render Options for e_mail
		* 
		* @access public
		* @return void
		*/
		public static function render_menu_email_settings() {

			$all_mails = WC()->mailer()->get_emails();
			$mail_options = array();

			foreach ( $all_mails as $email ) {
				$mail_options[ $email->id ] = $email->title;
			}

			$mail_options[ 'customer_order_confirmation' ] = __( 'Order Confirmation', 'woocommerce-german-market' );
			asort( $mail_options );

			$hide = array(
				'failed_order',
				'customer_new_account',
				'customer_reset_password',
				'customer_refunded_order',
				'cancelled_order',
				'customer_note',
			);
			
			foreach ( $hide as $email_key ) {
				if ( isset( $mail_options[ $email_key ] ) ) {
					unset( $mail_options[ $email_key ] );
				}
			}

			$options 	= array(
				'section_title' => array(
					'name'     => __( 'Email attachments', 'woocommerce-german-market' ),
					'type'     => 'title',
					'desc'     => '',
					'id'       => 'wcreapdf_email'
				),

				array(
					'title'		=> __( 'Return note', 'woocommerce-german-market' ),
					'type'		=> 'multiselect',
					'id'		=>  WCREAPDF_Helper::get_wcreapdf_optionname( 'return_note_emails' ),
					'default'	=> self::get_return_note_email_attachment_default(),
					'options'	=> $mail_options,
					'class' 	=> 'wc-enhanced-select',
					'desc_tip'  => __( 'Select the WooCommerce emails to which you want to attach the return note.', 'woocommerce-german-market' ),
					'css'      => 'min-width: 500px;',
				),

				array(
					'title'		=> __( 'Delivery note', 'woocommerce-german-market' ),
					'type'		=> 'multiselect',
					'id'		=>  WCREAPDF_Helper::get_wcreapdf_optionname( 'delivery_note_emails' ),
					'default'	=> array(),
					'options'	=> $mail_options,
					'class' 	=> 'wc-enhanced-select',
					'desc_tip'  => __( 'Select the WooCommerce emails to which you want to attach the delivery note.', 'woocommerce-german-market' ),
					'css'      => 'min-width: 500px;',
				),
			);
									
			$options[] = array( 'type' => 'sectionend', 'id' => 'wcreapdf_email' );
			$options = apply_filters( 'wcreapdf_email_options_after_sectioned_enhanced', $options );
			return( $options );
		}

		/**
		* Render Options for test_pdf
		* 
		* @access public
		* @return void
		*/
		public static function render_menu_test_pdf() {

			$link_url		= esc_url( wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_wcreapdf_download_test_pdf' ), 'woocommerce-wcreapdf-download-test-pdf' ) );
			$link_url_delivery = esc_url( wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_wcreapdf_download_test_pdf_delivery' ), 'woocommerce-wcreapdf-download-test-pdf' ) );
			
			$options = array(							
								'section_title' => array(
									'name' 	=> __( 'Test pdf', 'woocommerce-german-market' ),
									'type' 	=> 'title',
									'desc' 	=>  '<a class="woocommerce-wcreapdf-download-test-pdf" href="' . $link_url . '" download>' . __( 'Download Test pdf - Return Note', 'woocommerce-german-market' ) . '</a> <a class="woocommerce-wcreapdf-download-test-pdf" href="' . $link_url_delivery . '" download>' . __( 'Download Test pdf - Delivery Note', 'woocommerce-german-market' ) . '</a>',
									'id' 	=> 'woocomerce_wcreapdf_wgm_test_pdf'
								),
							
								array(
									'name' => __( 'Email', 'woocommerce-german-market' ),
									'desc_tip' =>  __( 'The test return note will be send to this email address', 'woocommerce-german-market' ),
									'tip'  => __( 'The test return delivery note will be send to this email address', 'woocommerce-german-market' ),
									'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'test_email' ),
									'type' => 'text',
									'default'  => get_option( 'woocommerce_email_from_address' ),
									'css'      => 'min-width:300px;',
								),

								array(
									'name' => __( 'Send test Return Note pdf', 'woocommerce-german-market' ),
									'type' => 'wgm_ui_checkbox',
									'desc_tip' => __( 'Enable this checkbox and click the save button. Your test return delivery note will be send to the email address entered above', 'woocommerce-german-market' ),
									'tip'  => __( 'Enable this checkbox and click the save button. Your test return delivery note will be send to the email address entered above', 'woocommerce-german-market' ),
									'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'send_test_email_check_box' ),
									'default' => 'no'
								),

								array( 'type' => 'sectionend', 'id' => 'woocomerce_wcreapdf_wgm_test_pdf' )

							);
	
			$extra_test_pdf_options	= apply_filters( 'wcreapdf_pdf_test_options', array() );			// add your own pdf options
			$options				= array_merge( $options, $extra_test_pdf_options );
			return( $options );

		}


		/**
		* Render Options for my_account_page
		* 
		* @access public
		* @return void
		*/
		public static function render_menu_my_account_page() {

			$options	= array(				
				'section_title' => array(
					'name' 	=> __( 'Return Note - My Account Page', 'woocommerce-german-market' ),
					'type' 	=> 'title',
					'desc' 	=> '',
					'id' 	=> 'wcreapdf_my_account_page'
				),
				
				array(
						'name' => __( 'Download button', 'woocommerce-german-market' ),
						'desc_tip' =>  __( 'Enable or disable the "Download return delivery note" on', 'woocommerce-german-market' ) . ' "' . trim( __( 'Endpoint for the My Account &rarr; View Order page', 'woocommerce-german-market' ) ) . '"',
						'tip'  => __( 'Enable or disable the "Download return delivery note" on', 'woocommerce-german-market' ) . ' "' . trim( __( 'Endpoint for the My Account &rarr; View Order page', 'woocommerce-german-market' ) ) . '"',
						'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'view-order-button' ),
						'type' => 'wgm_ui_checkbox',
						'css'  => 'min-width:250px;',
						'default'  => 'off',
					),
					
				array(
					'name' => __( 'Button text', 'woocommerce-german-market' ),
					'desc_tip' =>  __( 'Enter a text that is shown on the download button', 'woocommerce-german-market' ),
					'tip'  => __( 'Enter a text that is shown on the download button', 'woocommerce-german-market' ),
					'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'view-order-button-text' ),
					'type' => 'text',
					'default'  => __( 'Download Return Delivery Pdf', 'woocommerce-german-market' ),
					'css'      => 'min-width:250px;',
				),
					
				array(
						'name' => __( 'Link behaviour', 'woocommerce-german-market' ),
						'desc_tip' =>  __( 'Open the return delivery note link in a new browser tab or not. In the first case the HTML <code>&lt;a&gt;</code> tag gets the attribute <code>target="blank"</code>', 'woocommerce-german-market' ),
						'tip'  => __( 'Open the return delivery note link in a new browser tab or not. In the first case the HTML <code>&lt;a&gt;</code> tag gets the attribute <code>target="blank"</code>', 'woocommerce-german-market' ),
						'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'view-order-link-behaviour' ),
						'type' => 'select',
						'css'  => 'min-width:250px;',
						'default'  => 'new',
						'options' => array(
							'new'  => __( 'New browser tab', 'woocommerce-german-market' ),
							'current' => __( 'Current browser tab', 'woocommerce-german-market' ),
							)
					),
					
				array(
						'name' => __( 'Download behaviour', 'woocommerce-german-market' ),
						'desc_tip' =>  __( 'If "Download" is selected the browser forces a file download. The HTML <code>&lt;a&gt;</code> tag gets the attribute <code>download</code> (HTML5). If "Inline" is selected the file will be send inline to the browser, i.e. the browser will try to open the file in a tab using a browser plugin to display pdf files if available', 'woocommerce-german-market' ),
						'tip'  => __( 'If "Download" is selected the browser forces a file download. The HTML <code>&lt;a&gt;</code> tag gets the attribute <code>download</code> (HTML5). If "Inline" is selected the file will be send inline to the browser, i.e. the browser will try to open the file in a tab using a browser plugin to display pdf files if available', 'woocommerce-german-market' ),
						'id'   => WCREAPDF_Helper::get_wcreapdf_optionname( 'view-order-download-behaviour' ),
						'type' => 'select',
						'css'  => 'min-width:250px;',
						'default'  => 'inline',
						'options' => array(
							'inline'  => __( 'Inline', 'woocommerce-german-market' ),
							'download' => __( 'Download', 'woocommerce-german-market' ),
							)
					),

				array( 'type' => 'sectionend', 'id' => 'wcreapdf_my_account_page' )
			);

			$extra_my_account_options	= apply_filters( 'wcreapdf_pdf_my_account_options', array() ); // add your own my account options	
			$options					= array_merge( $options, $extra_my_account_options );
			return( $options );

		}

		/**
		* Output type wcreapdf_textarea
		*
		* @since 0.0.1
		* @static
		* @access public
		* @hook woocommerce_admin_field_wcreapdf_textarea
		* @return void
		*/
		public static function output_textarea( $value ) {

			// Description handling
			$field_description = WC_Admin_Settings::get_field_description( $value );
			extract( $field_description );

			$option_value = WC_Admin_Settings::get_option( $value['id'], $value['default'] );
			?><tr valign="top">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label><?php echo $tooltip_html; ?>
				</th>
				<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
					<textarea
						name="<?php echo esc_attr( $value['id'] ); ?>"
						id="<?php echo esc_attr( $value['id'] ); ?>"
						style="<?php echo esc_attr( $value['css'] ); ?>"
						class="<?php echo esc_attr( $value['class'] ); ?>"
						><?php echo esc_textarea( $option_value );  ?></textarea>
						<br /><span class="description"><?php echo $value['desc']; ?></span>
				</td>
			</tr><?php
		}

		/**
		* Save type wcreapdf_textarea
		*
		* @since 1.0.6
		* @static
		* @access public
		* @hook woocommerce_admin_settings_sanitize_option
		* @return void
		*/
		public static function save_wcreapdf_textarea( $value, $option, $raw_value ) {
			 
			if ( isset( $option[ 'type'] ) && $option[ 'type' ] == 'wcreapdf_textarea' ) {
				return html_entity_decode( wp_kses_post( trim( $raw_value ) ) );
			}

			return $value;
		}

		/**
		* send the test pdf
		*
		* @since 0.0.1
		* @access private
		* @return void
		*/
		private static function send_test_pdf() {
			$to 	= $_REQUEST[ 'woocomerce_wcreapdf_wgm_test_email' ];
			$pdf_path 	= WCREAPDF_Pdf::create_pdf( NULL, true );
			$attachment 	= WCREAPDF_TEMP_DIR . 'pdf'  . DIRECTORY_SEPARATOR . $pdf_path . DIRECTORY_SEPARATOR . get_option( WCREAPDF_Helper::get_wcreapdf_optionname( 'pdf_file_name' ), __( 'Retoure', 'woocommerce-german-market' ) ) . '.pdf';
			$subject_and_message = __( 'Example Retoure pdf', 'woocommerce-german-market' );
			wc_mail( $to, $subject_and_message, $subject_and_message, 'Content-Type: text/html\r\n', $attachment );
		}

		/**
		* Validation before saving
		*
		* @since 0.0.1
		* @access public
		* @return void
		*/
		public static function save( $value, $option, $raw_value ) {
			
			// send test e-mail if checkbox is checked
			if ( $option[ 'id' ] == WCREAPDF_Helper::get_wcreapdf_optionname( 'send_test_email_check_box' ) ) {
				if ( $value == 'on' ) {
					$value = 'off';
					self::send_test_pdf();
				}	
			}
			
			return $value;
		}

	}	

}
