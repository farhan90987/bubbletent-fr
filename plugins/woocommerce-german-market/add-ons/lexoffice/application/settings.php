<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

/**
* Backend Settings German Market 3.1
*
* wp-hook woocommerce_de_ui_options_global
* @param Array $items
* @return Array
*/
function lexoffice_woocommerce_de_ui_left_menu_items( $items ) {

	$prio = 310;

	$items[ $prio ] = array( 
		'title'		=> __( 'Lexware Office', 'woocommerce-german-market' ),
		'slug'		=> 'lexoffice',
	);

	$submenu = array(
				array(
					'title'		=> __( 'Authorization and general settings', 'woocommerce-german-market' ),
					'slug'		=> 'authorization_and_general_settings',
					'callback'	=> 'lexoffice_woocommerce_de_ui_render_options',
					'options'	=> 'yes'
				)
	);

	$voucher_or_invoice_api = get_option( 'woocommerce_de_lexoffice_voucher_or_invoice', 'voucher' );
	if ( isset( $_REQUEST[ 'woocommerce_de_lexoffice_voucher_or_invoice' ] ) && in_array( $_REQUEST[ 'woocommerce_de_lexoffice_voucher_or_invoice' ], array( 'voucher', 'invoice' ) ) ) {
		$voucher_or_invoice_api = esc_attr( $_REQUEST[ 'woocommerce_de_lexoffice_voucher_or_invoice' ] );
	}

	if ( 'voucher' === $voucher_or_invoice_api ) {

		$submenu[] = array(
			'title'		=> __( 'Inbound documents - interface settings', 'woocommerce-german-market' ),
			'slug'		=> 'voucher_settings',
			'callback'	=> 'lexoffice_woocommerce_de_ui_render_voucher_settings',
			'options'	=> 'yes'
		);

	} else if ( 'invoice' === $voucher_or_invoice_api ) {

		$submenu[] = array(
			'title'		=> __( 'General interface settings', 'woocommerce-german-market' ),
			'slug'		=> 'invoice_settings',
			'callback'	=> 'lexoffice_woocommerce_de_ui_render_invoice_settings',
			'options'	=> 'yes'
		);

		$submenu[] = array(
			'title'		=> __( 'Automatic transmission', 'woocommerce-german-market' ),
			'slug'		=> 'invoice_auto_transmission',
			'callback'	=> 'lexoffice_woocommerce_de_ui_render_invoice_auto_transmission',
			'options'	=> 'yes'
		);

		$submenu[] = array(
			'title'		=> __( 'Invoice document', 'woocommerce-german-market' ),
			'slug'		=> 'invoice_document_settings',
			'callback'	=> 'lexoffice_woocommerce_de_ui_render_invoice_document_settings',
			'options'	=> 'yes'
		);

		$submenu[] = array(
			'title'		=> __( 'Invoice correction document', 'woocommerce-german-market' ),
			'slug'		=> 'credit_note_document_settings',
			'callback'	=> 'lexoffice_woocommerce_de_ui_render_credit_note_document_settings',
			'options'	=> 'yes'
		);

		$not_draft_mode = 'off' === get_option( 'woocommerce_de_lexoffice_invoice_api_draft_mode', 'off' );
		if ( isset( $_REQUEST[ 'woocommerce_de_lexoffice_invoice_allowed_status_for_transmission' ] ) ) {
			$not_draft_mode = ! isset( $_REQUEST[ 'woocommerce_de_lexoffice_invoice_api_draft_mode' ] );
		}

		if ( $not_draft_mode ) {
			$submenu[] = array(
				'title'		=> __( 'PDF files', 'woocommerce-german-market' ),
				'slug'		=> 'invoice_pdf_settings',
				'callback'	=> 'lexoffice_woocommerce_de_ui_render_invoice_pdf_settings',
				'options'	=> 'yes'
			);
		}
	}

	$items[ $prio ][ 'submenu' ] = $submenu;

	return $items;
}

/**
* Render options for general lexoffice settings
* authorization for lexoffice and choice between voucher api and invoice api
*
* @return Array
*/
function lexoffice_woocommerce_de_ui_render_options() {

	$has_authorization = ! empty( get_option( 'woocommerce_de_lexoffice_authorization_code' ) );

	// revoke
	if ( isset( $_POST[ 'submit_save_wgm_options' ] ) ) { 
		if ( wp_verify_nonce( $_POST[ 'update_wgm_settings' ], 'woocommerce_de_update_wgm_settings' ) ) {
			if ( isset( $_REQUEST[ 'woocommerce_de_lexoffice_revoke' ] ) &&  $_REQUEST[ 'woocommerce_de_lexoffice_revoke' ] == 'on' ) {
				
				German_Market_Lexoffice_API_Auth::revoke_auth();
				$has_authorization = false;

				?>
				<div class="notice-wgm notice-success">
			        <p><?php echo __( 'The authorization has been revoked.', 'woocommerce-german-market' ); ?></p>
			    </div>
			    <?php
			} else {

				if ( isset( $_REQUEST[ 'woocommerce_de_lexoffice_authorization_code' ] ) && ! empty( $_REQUEST[ 'woocommerce_de_lexoffice_authorization_code' ] ) ) {
					$has_authorization = true;
				}
			}
		}
	}

	$description = '';

	if ( ! function_exists( 'curl_init' ) ) {

		$description = '<span style="color: #f00;">' . __( 'The PHP cURL library seems not to be present on your server. Please contact your admin / webhoster.', 'woocommerce-german-market' ) . ' ' . __( 'The Lexsare Office Add-On will not work without the cURL library.', 'woocommerce-german-market' ) . '</span><br /><br />';

	}

	if ( ! $has_authorization ) {
		
		$voucher_or_invoice_api = get_option( 'woocommerce_de_lexoffice_voucher_or_invoice', 'voucher' );
		if ( isset( $_REQUEST[ 'woocommerce_de_lexoffice_voucher_or_invoice' ] ) && in_array( $_REQUEST[ 'woocommerce_de_lexoffice_voucher_or_invoice' ], array( 'voucher', 'invoice' ) ) ) {
			$voucher_or_invoice_api = esc_attr( $_REQUEST[ 'woocommerce_de_lexoffice_voucher_or_invoice' ] );
		}

		$active_class = 'active-' . $voucher_or_invoice_api;

		$description .= '<span class="german-market-lexoffice-missing invoice ' . $active_class . '">' . sprintf( __( "Please visit <a href=\"%s\" target=\"_blank\">this page</a> and log in with your Lexware Office user account. Please accept all items of the next step. You will then receive an authorization code. Copy the code into the field \"Authorization Code\" below and save your settings.", 'woocommerce-german-market' ), 
			German_Market_Lexoffice_API_Auth::get_auth_url() ) . '</span>';

		$description .= '<span class="german-market-lexoffice-missing voucher ' . $active_class . '">' . sprintf( __( "Please visit <a href=\"%s\" target=\"_blank\">this page</a> and log in with your Lexware Office user account. Please accept all items of the next step. You will then receive an authorization code. Copy the code into the field \"Authorization Code\" below and save your settings.", 'woocommerce-german-market' ), 
			German_Market_Lexoffice_API_Auth::get_auth_url( 'inbound' ) ) . '</span>';
			
		$description .= '<br /><br /><span class="german-market-lexoffice-missing>' . sprintf ( __( "You can register <a href=\"%s\" target=\"_blank\">here</a> if you don't have a Lexware Office account, yet", 'woocommerce-german-market' ), German_Market_Lexoffice_API_Auth::get_sign_up_url() ) . '.</span>';

	} else {

		$description .= '<span class="german-market-lexoffice-connection-exists">' . esc_attr( __( 'There is already a connection to Lexware Office. If you have problems sending data to Lexware Office, revoke the authorization and then perform the authorization again.', 'woocommerce-german-market' ) ) . '</span>';

	}

	$options = array();

	$options[ 'interface_intro' ] = array(
		'name'		 => __( 'Interface', 'woocommerce-german-market' ),
		'type'		 => 'title',
		'id'  		 => 'lexoffice',
		'desc'		 => __( 'Before you authorize, you must decide whether you want to use the interface for inbound or outbound documents. Once you have decided, you can proceed with the authorization.', 'woocommerce-german-market' ) . PHP_EOL . PHP_EOL . __( 'You can find more information on the differences between the interfaces below.', 'woocommerce-german-market' )
	);

	if ( $has_authorization ) {
		if ( isset( $options[ 'interface_intro' ][ 'desc' ] ) ) {
			unset( $options[ 'interface_intro' ][ 'desc' ] );
		}
	}

	$options[ 'interface_option' ] = array(
		'name'		=>  __( 'Inbound or outbound documents', 'woocommerce-german-market' ),
		'id'		=> 'woocommerce_de_lexoffice_voucher_or_invoice',
		'type'     	=> 'select',
		'default'  	=> get_option( 'woocommerce_de_lexoffice_voucher_or_invoice', 'voucher' ),
		'options'	=> array(
				'voucher'  => __( 'Inbound documents (revenue and revenue deductions)', 'woocommerce-german-market' ),
				'invoice'  => __( 'Outbound documents (invoices and invoice corrections)', 'woocommerce-german-market' )
		),
		'class'     => 'wc-enhanced-select',
		'css'      	=> 'width: 500px;',
	);

	if ( $has_authorization ) {
		$options[ 'interface_option' ][ 'custom_attributes' ] = array( 'disabled' => 'disabled' );
		$options[ 'interface_option' ][ 'desc' ] = __( 'To be able to change this setting, there must be no active connection to Lexware Office. You must first revoke the authorization with the setting below. You can then change the setting and authorize again.', 'woocommerce-german-market' );
	}

	$options[] = array( 
		'type'		=> 'sectionend',
	);

	$options[] = array(
			'name'		 => __( 'Authorization', 'woocommerce-german-market' ),
			'type'		 => 'title',
			'id'  		 => 'lexoffice',
			'desc'		 => $description,
			'class'		 => 'test',
		);


	$options[ 'authorization_code' ] = array(
			'name'		=> __( 'Authorization Code', 'woocommerce-german-market' ),
			'id'		=> 'woocommerce_de_lexoffice_authorization_code',
			'type'		=> 'text'
		);

	$options[ 'revoke_auth' ] = array(
			'name'		=> __( 'Revoke Authorization', 'woocommerce-german-market' ),
			'id'		=> 'woocommerce_de_lexoffice_revoke',
			'desc_tip'	=> __( 'Enable this option to revoke the authorization, i.e. the connection between your Lexware Office account and your online shop will be removed.', 'woocommerce-german-market' ),
			'type'     	=> 'wgm_ui_checkbox',
			'default'  	=> 'off',
		);

	$options[] = array( 
		'type'		=> 'sectionend',
	);

	$description = __( 'You can decide whether you want to use the Lexware Office interface for inbound or outbound documents.', 'woocommerce-german-market' );
	$description .= '<br><br><strong>' . __( 'Inbound documents', 'woocommerce-german-market' ) . ':</strong><ul class="german-market-backend-list">';
	$description .= '<li>' . __( 'WooCommerce orders are transmitted to Lexware Office as revenue documents', 'woocommerce-german-market' ) . '</li>';
	$description .= '<li>' . __( 'Refunds from WooCommerce are sent to Lexware Office as revenue deductions', 'woocommerce-german-market' ) . '</li>';
	$description .= '<li>' . __( 'The invoice PDF from German Market ("Invoice PDF" add-on) is sent to Lexware Office as a voucher', 'woocommerce-german-market' ) . '</li>';
	$description .= '<li>' . __( 'The order number from WooCommerce (or the invoice number from the "Invoice Number" add-on from German Market) is sent to Lexware Office as the voucher number', 'woocommerce-german-market' ). '</li>';
	$description .= '<li>' . __( 'Can be used with version S of Lexware Office', 'woocommerce-german-market' ) . '</li>';
	$description .= '</ul>';
	$description .= '<br><strong>' . __( 'Outbound documents', 'woocommerce-german-market' ) . ':</strong><ul class="german-market-backend-list">';
	$description .= '<li>' . __( 'WooCommerce orders are transmitted to Lexware Office as invoices', 'woocommerce-german-market' ) . '</li>';
	$description .= '<li>' . __( 'Refunds from WooCommerce are sent to Lexware Office as invoice corrections', 'woocommerce-german-market' ) . '</li>';
	$description .= '<li>' . __( 'Lexware Office creates PDF files that you can attach to your WooCommerce emails', 'woocommerce-german-market' ) . '</li>';
	$description .= '<li>' . __( 'Lexware Office creates an invoice number, which you can also view in the WooCommerce store', 'woocommerce-german-market' ). '</li>';
	$description .= '<li>' . __( 'Can be used from version M of Lexware Office', 'woocommerce-german-market' ) . '</li>';
	$description .= '</ul>';
	

	$options[] = array(
		'name'		 => __( 'Inbound or outbound documents', 'woocommerce-german-market' ),
		'type'		 => 'title',
		'id'  		 => 'lexoffice_voucher_or_invoice',
		'desc'		 => $description,
		
	);

	$options[] = array( 
		'type'		=> 'sectionend',
		'id' 		=> 'lexoffice' 
	);
	$options = apply_filters( 'lexoffice_woocommerce_de_ui_render_options_general', $options );
	return $options;
}

/**
* Render Options for global
* 
* @return void
*/
function lexoffice_woocommerce_de_ui_render_voucher_settings() {

	$options = array();

	$options = lexoffice_woocommerce_de_ui_add_contact_options( $options );
	$options = lexoffice_woocommerce_de_ui_add_autotransmission_options( $options );

	$options = apply_filters( 'lexoffice_woocommerce_de_ui_render_options', $options );
	return( $options );

}

/**
 * Add options for contacts
 * Used both vouchaer api and invoice api
 * 
 * @param Array $options
 * @return $Array
 */
function lexoffice_woocommerce_de_ui_add_contact_options( $options ) {

	$lexoffice_contacts = German_Market_Lexoffice_API_Contact::get_all_contacts();
	$guest_options = array();
	$guest_options[ 'collective_contact' ] = __( 'Use "Collective Contact"', 'woocommerce-german-market' );
	$guest_options[ 'create_new_user' ] = __( 'Create a new Lexware Office User', 'woocommerce-german-market' );

	foreach ( $lexoffice_contacts as $lexoffice_user ) {
		
		$display_name = '';

		if ( isset( $lexoffice_user[ 'person' ] ) ) {
			$display_name = isset( $lexoffice_user[ 'person' ][ 'firstName' ] ) ? $lexoffice_user[ 'person' ][ 'lastName' ] . ', ' . $lexoffice_user[ 'person' ][ 'firstName' ] : $lexoffice_user[ 'person' ][ 'lastName' ];
		} else if ( isset( $lexoffice_user[ 'company' ] ) ) {
			$display_name = $lexoffice_user[ 'company' ][ 'name' ];
		}

		if ( $display_name != '' ) {
			$guest_options[ $lexoffice_user[ 'id' ] ] = sprintf( __( 'Use "%s"', 'woocommerce-german-market' ), $display_name );
		}
		
	}

	$options[] = array( 
			'name'		 => __( 'Contacts', 'woocommerce-german-market' ),
			'type'		 => 'title',
			'id'  		 => 'lexoffice_contacts',
			'desc'	   	 => __( 'A collective customer cannot be used for distance sales within the EU with non-German tax rates, tax free intracommunity deliveries and tax free export deliveries. In these cases, a contact is created in Lexware Office regardless of the following settings in order to ensure error-free transfer to Lexware Office.', 'woocommerce-german-market' ),
		);

	$options[] = array( 
			'name'		=> __( 'Lexware Office Contacts', 'woocommerce-german-market' ),
			'id'		=> 'woocommerce_de_lexoffice_contacts',
			'desc_tip'	=> __( 'You can choose whether to use only the "Collective Contact" for each WooCommerce user or to have the possibility to assing every WooCommerce user to one of your Lexware Office contacts.', 'woocommerce-german-market' ),
			'type'     	=> 'select',
			'default'  	=> 'collective_contact',
			'options'	=> array(
				'collective_contact'	=> __( 'Only use the Collective Contact', 'woocommerce-german-market' ),
				'lexoffice_contacts'	=> __( 'Use Lexware Office Contacts', 'woocommerce-german-market' ),
			),
			'class'    => 'wc-enhanced-select',
			'css'      => 'min-width:300px;',
		);

	$options[ 'woocommerce_de_lexoffice_create_new_user' ] = array(
			'name'		=>  __( 'Create new Lexware Office Users', 'woocommerce-german-market' ),
			'id'		=> 'woocommerce_de_lexoffice_create_new_user',
			'desc_tip'	=> __( 'If enabled, a new Lexware Office user is automatically created if you send an order to Lexware Office with an WooCommerce user that is not assigned to any Lexware Office user, yet.', 'woocommerce-german-market' ),
			'type'     	=> 'select',
			'default'  	=> 'on',
			'options'	=> array(
					'on'  => __( 'Create a new user', 'woocommerce-german-market' ),
					'off' => __( 'Use "Collective Contact"', 'woocommerce-german-market' )
			),
			'class'    => 'wc-enhanced-select',
			'css'      => 'min-width:300px;',
		);

	$options[ 'woocommerce_de_lexoffice_user_update' ] = array(
			'name'		=> __( 'Automatic User Update', 'woocommerce-german-market' ),
			'id'		=> 'woocommerce_de_lexoffice_user_update',
			'desc_tip'	=> __( 'Update the Lexware Office user data when a new order is send to Lexware Office.', 'woocommerce-german-market' ),
			'type'     	=> 'select',
			'default'  	=> 'on',
			'options'	=> array(
					'on'  => __( 'Update Lexware Office User', 'woocommerce-german-market' ),
					'off' => __( 'Don\'t update the Lexware Office User', 'woocommerce-german-market' )
			),
			'class'    => 'wc-enhanced-select',
			'css'      => 'min-width:300px;',
		);

	$options[ 'woocommerce_de_lexoffice_guest_user' ] = array(
			'name'		=> __( 'Guest Users', 'woocommerce-german-market' ),
			'id'		=> 'woocommerce_de_lexoffice_guest_user',
			'desc_tip'	=> __( 'If you have enabled guest checkout you can manage here how to connect a guest user with your Lexware Office contacts.', 'woocommerce-german-market' ),
			'type'     	=> 'select',
			'options'	=> $guest_options,
			'default'  	=> 'collective_contact',
			'class'    => 'wc-enhanced-select',
			'css'      => 'min-width:300px;',
		);

	$options[] = array( 
			'type'		=> 'sectionend',
			'id' 		=> 'lexoffice_contacts' 
		);

	return $options;
}

/**
 * Add options for auto transmission
 * Used both vouchaer api and invoice api
 * 
 * @param Array $options
 * @return Array
 */
function lexoffice_woocommerce_de_ui_add_autotransmission_options( $options ) {

	$options[] = array( 
		'name'		 => __( 'Automatic Transmission', 'woocommerce-german-market' ),
		'type'		 => 'title',
		'id'  		 => 'lexoffice_automatic_transmission',
	);

	$voucher_or_invoice_api = get_option( 'woocommerce_de_lexoffice_voucher_or_invoice', 'voucher' );

	if ( 'voucher' === $voucher_or_invoice_api ) {
		$options[] = array( 
				'name'		=> __( 'Completed Order', 'woocommerce-german-market' ),
				'id'		=> 'woocommerce_de_lexoffice_automatic_completed_order',
				'desc_tip'	=> __( 'If activated, the voucher will be send automatically to Lexware Office if the order is marked as completed.', 'woocommerce-german-market' ),
				'type'     	=> 'wgm_ui_checkbox',
				'default'  	=> 'off',
			);

	} else {

		$options[] = array(
			'name' 		=> __( 'Order status', 'woocommerce-german-market' ),
			'desc_tip'	=> __( 'The transmission of a WooCommerce order to Lexware Office as an invoice can take place automatically if the order changes to one of the status selected here and no transmission has yet taken place for this order. You can only select status here that you have defined in the "General interface settings" submenu under "Allowed status for transmission".', 'woocommerce-german-market' ),
			'id'   		=> 'woocommerce_de_lexoffice_invoice_autotransmission_status',
			'type' 		=> 'multiselect',
			'default'  	=> 'completed',
			'options'	=> German_Market_Lexoffice_Invoice_API_General::get_possible_order_status_for_invoices( 'autotransmission' ),
			'class'		=> 'wc-enhanced-select',
			'css'      	=> 'width: 400px;',
			'custom_attributes' => array(
					'data-placeholder' => __( 'Select status', 'woocommerce-german-market' ),
				),
		);
	}

	$options[] = array( 
			'name'		=> _x( 'Refunds', 'lexoffice setting', 'woocommerce-german-market' ),
			'id'		=> 'woocommerce_de_lexoffice_automatic_refund',
			'desc_tip'	=> __( 'If activated, the voucher will be send automatically to Lexware Office if an refund is created.', 'woocommerce-german-market' ),
			'type'     	=> 'wgm_ui_checkbox',
			'default'  	=> 'off',
		);

	$options[] = array( 
			'name'		=> __( 'Transmit order before transmitting refund', 'woocommerce-german-market' ),
			'id'		=> 'woocommerce_de_lexoffice_transmit_order_before_refund',
			'desc_tip'	=> __( 'If this setting is enabled, the order will be transmitted to Lexware Office before the refund is transmitted, if it has not been done before.', 'woocommerce-german-market' ) . ' ' . __( 'The transmission of the order takes place regardless of the status of the order.', 'woocommerce-german-market' ),
			'type'     	=> 'wgm_ui_checkbox',
			'default'  	=> 'on',
		);

	$options[] = array( 
			'type'		=> 'sectionend',
		);

	return $options;
}

/**
* Render options for invoice auto transmission settings
*
* @return Array
*/
function lexoffice_woocommerce_de_ui_render_invoice_auto_transmission() {

	$options = array();
	$options = lexoffice_woocommerce_de_ui_add_autotransmission_options( $options );

	return apply_filters( 'lexoffice_woocommerce_de_ui_render_options_invoice_autotransmission', $options );
}

/**
* Render options for pdf settings
*
* @return Array
*/
function lexoffice_woocommerce_de_ui_render_invoice_pdf_settings() {

	$wc_mails_instance = WC_Emails::instance();
	$wc_mails = $wc_mails_instance->get_emails();

	$select_mails = array();
	$exceptions = array( 'cancelled_order', 'failed_order', 'customer_refunded_order', 'customer_note', 'customer_reset_password', 'customer_new_account' );

	$select_mails[ 'customer_order_confirmation' ] = __( 'Order Confirmation', 'woocommerce-german-market' );
	foreach ( $wc_mails as $mail ) {
		if ( ! in_array( $mail->id, $exceptions ) ) {
			$select_mails[ $mail->id ] = $mail->title;
		}
	}

	$placholder_order = __( 'Order number - <code>{{order-number}}</code>, Lexware Office invoice number - <code>{{lexwareoffice-invoice-number}}</code>', 'woocommerce-german-market' );
	$options = array();

	$options[] = array(
		'name'		 => __( 'Invoice PDF', 'woocommerce-german-market' ),
		'type'		 => 'title',
		'id'  		 => 'lexoffice_invoice_pdf',
		'desc'		 => __( 'You can use the following placeholders:', 'woocommerce-german-market' ) . ' ' . $placholder_order,
	);

	$options[] = array(
		'name' 		=> __( 'File name in backend', 'woocommerce-german-market' ),
		'desc' 		=> '.pdf',
		'desc_tip'	=> __( 'Invoice file name to use in backend', 'woocommerce-german-market' ),
		'id'   		=> 'woocommerce_de_lexoffice_invoice_api_pdf_backend_filename',
		'type' 		=> 'text',
		'default'  	=> __( 'Invoice-{{lexwareoffice-invoice-number}}', 'woocommerce-german-market' ),
		'css'      	=> 'width: 400px;',
		'class'		=> 'german-market-unit',
	);
	
	$options[] = array(
		'name' 		=> __( 'File name in frontend', 'woocommerce-german-market' ),
		'desc' 		=> '.pdf',
		'desc_tip'	=> __( 'Invoice file name to use in frontend for your customer', 'woocommerce-german-market' ),
		'id'   		=> 'woocommerce_de_lexoffice_invoice_api_pdf_frontend_filename',
		'type' 		=> 'text',
		'default'  	=>  __( 'Invoice-{{lexwareoffice-invoice-number}}', 'woocommerce-german-market' ),
		'css'      	=> 'width: 400px;',
		'class'		=> 'german-market-unit',
	);

	$options[] = array(
		'name' 		=> __( 'Email attachement', 'woocommerce-german-market' ),
		'desc_tip'	=> __( 'You can add the Lexware Office invoice pdf as an attachment to your WooCommerce emails', 'woocommerce-german-market' ),
		'desc'		=> __( 'If the order has already been sent to Lexware Office and the PDF exists in Lexware Office, it will be attached to the selected WooCommerce email. If the PDF file does not exist in Lexware Office, the order is sent to Lexware Office and the PDF is attached. The order must be in a status for which the transmission to Lexware Office is allowed. If the order is not in a allowed status, no transmission will take place and the PDF will not be attached to the email. You can define the allowed status in the submenu "General interface settings" under "Allowed status for transmission".', 'woocommerce-german-market' ),
		'id'   		=> 'woocommerce_de_lexoffice_invoice_email_attachment',
		'type' 		=> 'multiselect',
		'default'  	=> '',
		'options'	=> $select_mails,
		'class'		=> 'wc-enhanced-select',
		'css'      	=> 'width: 400px;',
		'custom_attributes' => array(
				'data-placeholder' => __( 'Select emails', 'woocommerce-german-market' ),
			),
	);

	$options[] = array( 
		'type'		=> 'sectionend',
	);

	$placholder_refund = __( ', refund ID - <code>{{refund-id}}</code>, Lexware Office invoice correction number - <code>{{lexwareoffice-correction-number}}</code>', 'woocommerce-german-market' );

	$options[] = array(
		'name'		 => __( 'Invoice Correction PDF', 'woocommerce-german-market' ),
		'type'		 => 'title',
		'desc'		 => __( 'You can use the following placeholders:', 'woocommerce-german-market' ) . ' ' . $placholder_order . $placholder_refund,
	);

	$options[] = array(
		'name' 		=> __( 'File name in backend', 'woocommerce-german-market' ),
		'desc' 		=> '.pdf',
		'desc_tip'	=> __( 'Invoice file name to use in backend', 'woocommerce-german-market' ),
		'id'   		=> 'woocommerce_de_lexoffice_credit_note_api_pdf_backend_filename',
		'type' 		=> 'text',
		'default'  	=> __( 'Invoice-Correction-{{lexwareoffice-correction-number}}', 'woocommerce-german-market' ),
		'css'      	=> 'width: 400px;',
		'class'		=> 'german-market-unit',
	);
	
	$options[] = array(
		'name' 		=> __( 'File name in frontend', 'woocommerce-german-market' ),
		'desc' 		=> '.pdf',
		'desc_tip'	=> __( 'Invoice file name to use in frontend for your customer', 'woocommerce-german-market' ),
		'id'   		=> 'woocommerce_de_lexoffice_credit_note_api_pdf_frontend_filename',
		'type' 		=> 'text',
		'default'  	=>  __( 'Invoice-Correction-{{lexwareoffice-correction-number}}', 'woocommerce-german-market' ),
		'css'      	=> 'width: 400px;',
		'class'		=> 'german-market-unit',
	);

	$options[] = array(
		'name' 		=> __( 'Email attachement', 'woocommerce-german-market' ),
		'desc_tip'	=> __( 'If this setting is activated, the PDF of the invoice correction from Lexware Office is attached to the WooCommerce "Refunded order" email. If the refund has not yet been transmitted from WooCommerce to Lexware Office, this will be done.', 'woocommerce-german-market' ),
		'id'   		=> 'woocommerce_de_lexoffice_credit_note_email_attachment',
		'type' 		=> 'wgm_ui_checkbox',
		'default'  	=> 'off',
	);

	$options[] = array( 
		'type'		=> 'sectionend',
	);

	$options[] = array(
		'name'		 => __( 'My Account Page', 'woocommerce-german-market' ),
		'type'		 => 'title',
		'desc'		 => __( 'The download button is only displayed if the order has already been sent to Lexware Office.', 'woocommerce-german-market' )
	);

	$options[] = array(
		'name'		=> __( 'Show download button for an invoice in "Orders" menu', 'woocommerce-german-market' ),
		'id'		=> 'woocommerce_de_lexoffice_invoice_api_my_account_orders',
		'desc_tip'	=> __( 'The download button is displayed in the "Actions" column of this summary page.', 'woocommerce-german-market' ),
		'type'     	=> 'wgm_ui_checkbox',
		'default'  	=> 'off',
	);

	$options[] = array(
		'name'		=> __( 'Show download button for an invoice in order view', 'woocommerce-german-market' ),
		'id'		=> 'woocommerce_de_lexoffice_invoice_api_my_account_order_view',
		'type'     	=> 'wgm_ui_checkbox',
		'default'  	=> 'off',
	);

	$options[] = array(
		'name' 		=> __( 'Button text for invoices', 'woocommerce-german-market' ),
		'id'   		=> 'woocommerce_de_lexoffice_invoice_api_my_account_button_text',
		'type' 		=> 'text',
		'default'  	=> __( 'Download invoice {{lexwareoffice-invoice-number}}', 'woocommerce-german-market' ),
		'desc'		=> __( 'You can use the following placeholders:', 'woocommerce-german-market' ) . ' ' . __( 'Order number - <code>{{order-number}}</code>, Lexware Office invoice number - <code>{{lexwareoffice-invoice-number}}</code>', 'woocommerce-german-market' ),
		'css'      	=> 'width: 400px;',
	);

	$options[] = array(
		'name'		=> __( 'Show download button for an invoice correction in order view', 'woocommerce-german-market' ),
		'id'		=> 'woocommerce_de_lexoffice_invoice_api_my_account_order_view_refunds',
		'type'     	=> 'wgm_ui_checkbox',
		'default'  	=> 'off',
	);

	$options[] = array(
		'name' 		=> __( 'Button text for invoice corrections', 'woocommerce-german-market' ),
		'id'   		=> 'woocommerce_de_lexoffice_invoice_api_my_account_button_text_refund',
		'type' 		=> 'text',
		'default'  	=> __( 'Download invoice correction {{lexwareoffice-correction-number}}', 'woocommerce-german-market' ),
		'desc'		=> __( 'You can use the following placeholders:', 'woocommerce-german-market' ) . ' ' . __( 'Refund ID - <code>{{refund-id}}</code>, Lexware Office invoice correction number - <code>{{lexwareoffice-correction-number}}</code>', 'woocommerce-german-market' ),
		'css'      	=> 'width: 400px;',
	);

	$options[] = array( 
		'type'		=> 'sectionend',
	);

	return apply_filters( 'lexoffice_woocommerce_de_ui_render_options_invoice_pdf', $options );
}


/**
* Render options for invoice document settings
*
* @return Array
*/
function lexoffice_woocommerce_de_ui_render_invoice_document_settings( $is_credit_note = false ) {

	$suffix = $is_credit_note ? '_credit_note' : '';

	$placeholders = apply_filters( 'wp_wc_invoice_pdf_custom_payment_info_placeholders', array(
		__( 'Refund ID', 'woocommerce-german-market' ) => '{{refund-id}}',
		__( 'Lexware Office invoice number', 'woocommerce-german-market' ) => '{{lexwareoffice-invoice-number}}',
		__( 'Order Number', 'woocommerce-german-market' ) => '{{order-number}}',
		__( 'Order Date', 'woocommerce-german-market' ) => '{{order-date}}',
		__( 'Order Total', 'woocommerce-german-market' ) => '{{order-total}}',
		__( 'First name', 'woocommerce-german-market' ) => '{{first-name}}',
		__( 'Last name', 'woocommerce-german-market' ) => '{{last-name}}',
	) );

	if ( $is_credit_note ) {
		
		if ( isset( $placeholders[ __( 'Order Total', 'woocommerce-german-market' ) ] ) ) {
			unset( $placeholders[ __( 'Order Total', 'woocommerce-german-market' ) ] );
		}

		if ( isset( $placeholders[ __( 'Order Date', 'woocommerce-german-market' ) ] ) ) {
			unset( $placeholders[ __( 'Order Date', 'woocommerce-german-market' ) ] );
		}

		if ( 'off' === get_option( 'woocommerce_de_lexoffice_transmit_order_before_refund', 'on' ) ) {
			if ( isset( $placeholders[ __( 'Lexware Office invoice number', 'woocommerce-german-market' ) ] ) ) {
				unset( $placeholders[ __( 'Lexware Office invoice number', 'woocommerce-german-market' ) ] );
			}
		}

	} else {

		if ( isset( $placeholders[ __( 'Refund ID', 'woocommerce-german-market' ) ] ) ) {
			unset( $placeholders[ __( 'Refund ID', 'woocommerce-german-market' ) ] );
		}

		if ( isset( $placeholders[ __( 'Lexware Office invoice number', 'woocommerce-german-market' ) ] ) ) {
			unset( $placeholders[ __( 'Lexware Office invoice number', 'woocommerce-german-market' ) ] );
		}

	}

	$placeholders_string = '';
	foreach ( $placeholders as $label => $code ) {
		if ( ! empty( $placeholders_string ) ) {
			$placeholders_string .= ', ';
		}
		$placeholders_string .= $label . ' - ' . '<code>' . $code . '</code>';
	}

	$options = array();

	$options[] = array(
		'name'		 => __( 'Texts', 'woocommerce-german-market' ),
		'type'		 => 'title',
		'id'  		 => 'lexoffice_texts',
		'desc'		 => __( 'These texts appear on the documents in Lexware Office.', 'woocommerce-german-market' ),

	);

	$options[] = array(
		'name'		=> __( 'Document title', 'woocommerce-german-market' ),
		'id'		=> 'woocommerce_de_lexoffice_invoice_api_title' . $suffix,
		'type'     	=> 'text',
		'default'  	=> $is_credit_note ? __( 'Invoice Correction', 'woocommerce-german-market' ) : __( 'Invoice', 'woocommerce-german-market' ),
		'desc'		=> __( 'You can use the following placeholders:', 'woocommerce-german-market' ) . ' ' . $placeholders_string,
		'desc_tip'	=> __( 'After the placeholders have been replaced, the title can be a maximum of 25 characters long.', 'woocommerce-german-market' ),
	);

	$options[] = array(
		'name'		=> __( 'Introduction text', 'woocommerce-german-market' ),
		'id'		=> 'woocommerce_de_lexoffice_invoice_introduction'  . $suffix,
		'type'     	=> 'german_market_textarea',
		'default'  	=> $is_credit_note ? __( 'The following deliveries/services will be credited to your account.', 'woocommerce-german-market' ) : __( 'We invoice you for our services/deliveries as follows:', 'woocommerce-german-market' ),
		'css'		=> 'width: 400px; min-height: 75px;',
		'desc'		=> __( 'You can use the following placeholders:', 'woocommerce-german-market' ) . ' ' . $placeholders_string,
	);

	$options[] = array(
		'name'		=> __( 'Remark', 'woocommerce-german-market' ),
		'id'		=> 'woocommerce_de_lexoffice_invoice_remark' . $suffix,
		'type'     	=> 'german_market_textarea',
		'default'  	=> $is_credit_note ? __( 'Yours sincerely.', 'woocommerce-german-market' ) : __( 'Many thanks for the good cooperation.', 'woocommerce-german-market' ),
		'css'		=> 'width: 400px; min-height: 75px;',
		'desc'		=> __( 'You can use the following placeholders:', 'woocommerce-german-market' ) . ' ' . $placeholders_string,
	);

	if ( ! $is_credit_note ) {
		$options[] = array(
			'name'		=> __( 'Default payment conditions', 'woocommerce-german-market' ),
			'id'		=> 'woocommerce_de_lexoffice_invoice_default_payment_conditions'  . $suffix,
			'type'     	=> 'textarea',
			'default'  	=> __( 'Payable immediately.', 'woocommerce-german-market' ),
			'css'		=> 'width: 400px; min-height: 75px;',
			'desc_tip'	=> __( 'You can define payment conditions for each payment gateway in the “Payment condition text for Lexware Office invoice” field in the submenu "WooCommerce -> German Market -> General -> Payment settings". If the specific payment setting is empty, this default payment condition is used. If this field is also empty, no payment conditions are sent to Lexware Office and the default text from Lexware Office is displayed.', 'woocommerce-german-market' )
		);
	}

	$options[] = array(
		'name'		=> __( 'Headline shipping', 'woocommerce-german-market' ),
		'id'		=> 'woocommerce_de_lexoffice_invoice_headline_shipping' . $suffix,
		'type'     	=> 'text',
		'default'  	=> __( 'Shipping', 'woocommerce-german-market' ),
		'desc_tip'  => __( 'Leave the field empty if no heading is to be output.', 'woocommerce-german-market' ),
	);

	$options[] = array(
		'name'		=> __( 'Headline fees', 'woocommerce-german-market' ),
		'id'		=> 'woocommerce_de_lexoffice_invoice_headline_fees' . $suffix,
		'type'     	=> 'text',
		'default'  	=> __( 'Fees', 'woocommerce-german-market' ),
		'desc_tip'  => __( 'Leave the field empty if no heading is to be output.', 'woocommerce-german-market' ),
	);

	$options[] = array( 
		'type'		=> 'sectionend',
		'id' 		=> 'lexoffice_texts' 
	);

	$options[] = array(
		'name'		 => __( 'Document settings', 'woocommerce-german-market' ),
		'type'		 => 'title',
		'id'  		 => 'lexoffice_document_settings',
	);

	// print layout
	$print_layouts = German_Market_Lexoffice_Invoice_API_General::get_print_layouts();
	$print_layout_options = array();
	$print_layout_default = '';
	foreach ( $print_layouts as $print_layout ) {
		if ( isset( $print_layout[ 'id' ] ) && isset( $print_layout[ 'name' ] ) ) {
			$print_layout_options[ $print_layout[ 'id' ] ] = $print_layout[ 'name' ];
			if ( isset( $print_layout[ 'default' ] ) && true === $print_layout[ 'default' ] ) {
				$print_layout_default = $print_layout[ 'id' ];
			}
		}
	}

	if ( ! empty( $print_layout_options ) ) {
		$options[] = array(
			'name'		=> __( 'Print layout', 'woocommerce-german-market' ),
			'id'		=> 'woocommerce_de_lexoffice_invoice_api_print_layout' . $suffix,
			'type'     	=> 'select',
			'options'	=> $print_layout_options,
			'default'  	=> $print_layout_default,
			'class'    => 'wc-enhanced-select',
			'css'      => 'min-width: 400px;',
			'desc_tip'	=> __( 'Select one of the layouts created in Lexware Office here.', 'woocommerce-german-market' ),
		);
	}

	if ( ! $is_credit_note ) {
		$options[] = array(
			'name'		=> __( 'Show discounts', 'woocommerce-german-market' ),
			'desc_tip'	=> __( 'If this setting is deactivated, the item prices from WooCommerce orders are sent to Lexware Office after the discount has been applied. If the setting is activated, the prices are sent before the voucher is applied and the discount is also explicitly sent. The discount is always sent to Lexware Office as a percentage, Lexware Office then calculates the price itself after the discount has been applied.', 'woocommerce-german-market' ),
			'id'		=> 'woocommerce_de_lexoffice_invoice_show_discounts' . $suffix,
			'type'     	=> 'wgm_ui_checkbox',
			'default'  	=> 'off',
		);
	}

	$options[] = array(
		'name'		=> __( 'Show SKU', 'woocommerce-german-market' ),
		'id'		=> 'woocommerce_de_lexoffice_invoice_show_sku' . $suffix,
		'type'     	=> 'wgm_ui_checkbox',
		'default'  	=> 'on',
	);

	$options[ 'gtin' ] = array(
		'name'		=> __( 'Show GTIN', 'woocommerce-german-market' ),
		'id'		=> 'woocommerce_de_lexoffice_invoice_show_gtin' . $suffix,
		'type'     	=> 'wgm_ui_checkbox',
		'default'  	=> 'on',
	);

	$options[] = array(
		'name'		=> __( 'Show item meta', 'woocommerce-german-market' ),
		'desc_tip'	=> __( 'If activated, the document in Lexware Office will contain the item meta data of the order items. HTML tags are not supported and will be removed.', 'woocommerce-german-market' ),
		'id'		=> 'woocommerce_de_lexoffice_invoice_show_item_meta' . $suffix,
		'type'     	=> 'wgm_ui_checkbox',
		'default'  	=> 'off',
	);

	$options[] = array(
		'name'		=> __( 'Show short description of products', 'woocommerce-german-market' ),
		'id'		=> 'woocommerce_de_lexoffice_invoice_api_line_item_short_description' . $suffix,
		'desc_tip'	=> __( 'If activated, the document in Lexware Office will contain the short description of your WooCommerce products. HTML tags are not supported and will be removed.', 'woocommerce-german-market' ),
		'type'     	=> 'wgm_ui_checkbox',
		'default'  	=> 'off',
	);

	if ( ( 'off' === get_option( 'gm_gtin_activation', 'off' ) ) && isset( $options[ 'gtin' ] ) ) {
		unset( $options[ 'gtin' ] );
	}

	$options[] = array( 
		'type'		=> 'sectionend',
		'id' 		=> 'lexoffice_document_settings' 
	);

	return apply_filters( 'lexoffice_woocommerce_de_ui_render_options_invoice_document' . $suffix, $options );
}

/**
 * Render options for credit note document
 * 
 * @return Array
 */
function lexoffice_woocommerce_de_ui_render_credit_note_document_settings() {
	$is_credit_note = true;
	return lexoffice_woocommerce_de_ui_render_invoice_document_settings( $is_credit_note  );
}

/**
* Render options for invoice API
*
* @return Array
*/
function lexoffice_woocommerce_de_ui_render_invoice_settings() {

	$options = array();

	$options[] = array(
		'name'		 => __( 'Draft mode', 'woocommerce-german-market' ),
		'type'		 => 'title',
		'desc'		 => __( 'In draft mode, invoices and invoice corrections are created as "draft". If the setting is deactivated, the documents are created as "finalized". If the draft mode is active, WooCommerce cannot access the PDF files created by Lexware Office. It is then not possible to attach these PDFs from Lexware Office to WooCommerce emails or to offer them to the customer for download on the "My Account" page.', 'woocommerce-german-market' )
	);

	$options[] = array(
		'name'		=> __( 'Draft mode', 'woocommerce-german-market' ),
		'id'		=> 'woocommerce_de_lexoffice_invoice_api_draft_mode',
		'type'     	=> 'wgm_ui_checkbox',
		'default'  	=> 'off',
	);

	$options[] = array( 
		'type'		=> 'sectionend',
	);

	$options[] = array(
		'name'		 => __( 'Tax condition', 'woocommerce-german-market' ),
		'type'		 => 'title',
		'desc'		=> __( 'You can choose whether you send net or gross prices to Lexware Office. Lexware Office will calculate the taxes. It is recommended that this setting is consistent with the WooCommerce tax setting "Prices entered with tax" to eliminate rounding errors. If you select the "WooCommerce setting" option, the order to be transferred will be checked to see whether the prices have been saved with or without taxes (depending on the tax setting in WooCommerce at the time the order is received) and the tax condition for Lexware Office will be selected automatically. The use of this option is recommended.', 'woocommerce-german-market' ),
	);

	$options[] = array(
			'name'		=> __( 'Tax condition', 'woocommerce-german-market' ),
			'id'		=> 'woocommerce_de_lexoffice_invoice_api_tax_condition',
			'type'     	=> 'select',
			'default'  	=> 'gross',
			'options'	=> array(
				'wc'	=> __( 'WooCommerce setting', 'woocommerce-german-market' ),
				'gross'	=> __( 'Gross', 'woocommerce-german-market' ),
				'net'	=> __( 'Net', 'woocommerce-german-market' ),
			),
			'class'     => 'wc-enhanced-select',
			'css'      	=> 'width: 400px;'
		);

	$options[] = array( 
		'type'		=> 'sectionend',
	);

	$options[] = array(
		'name'		 => __( 'Allowed status for transmission', 'woocommerce-german-market' ),
		'type'		 => 'title',
		'desc'		=> __( 'A transfer of WooCommerce orders is only possible manually and via automatic transfer if the respective order is in one of the status selected here. Please note that once a WooCommerce order has been transmitted to Lexware Office, it cannot be transmitted to Lexware Office again, i.e. no updates are possible. Therefore, only select status in which you will no longer process the WooCommerce order.', 'woocommerce-german-market' ),
	);

	$options[] = array(
			'name' 		=> _x( 'Order status', 'plural', 'woocommerce-german-market' ),
			'id'   		=> 'woocommerce_de_lexoffice_invoice_allowed_status_for_transmission',
			'type' 		=> 'multiselect',
			'default'  	=> 'completed',
			'options'	=> German_Market_Lexoffice_Invoice_API_General::get_possible_order_status_for_invoices( 'general' ),
			'class'		=> 'wc-enhanced-select',
			'css'      	=> 'width: 400px;',
			'custom_attributes' => array(
					'data-placeholder' => __( 'Select status', 'woocommerce-german-market' ),
				),
		);

	$options[] = array( 
		'type'		=> 'sectionend',
	);

	$options[] = array(
		'name'		 => __( 'Retransmit', 'woocommerce-german-market' ),
		'type'		 => 'title',
		'desc'		 => __( 'Outbound documents (invoices and invoice corrections) cannot be updated via the interface. Therefore, an order/refund from WooCommerce can only be sent to Lexware Office once. If a manual attempt is made to resend an order/refund to Lexware Office, a message is displayed to check whether the corresponding invoice/invoice correction still exists in Lexware Office.', 'woocommerce-german-market' ) . PHP_EOL . PHP_EOL . __( 'If this setting is activated, the affiliation of the order/refund from WooCommerce to the document in Lexware Office is also removed if the document no longer exists in Lexware Office. A corresponding message is displayed. This makes it possible to send the order/refund to Lexware Office again afterwards. However, invoices and invoice corrections can only be deleted in Lexware Office if they are not finalized, i.e. if they are still in "draft" status.', 'woocommerce-german-market' )
	);
	
	$options[] = array(
		'name'		=> __( 'Allow retransmission', 'woocommerce-german-market' ),
		'id'		=> 'woocommerce_de_lexoffice_invoice_api_allow_retransmission',
		'type'     	=> 'wgm_ui_checkbox',
		'default'  	=> 'off',
	);

	$options[] = array( 
		'type'		=> 'sectionend',
	);

	$options = lexoffice_woocommerce_de_ui_add_contact_options( $options );

	return apply_filters( 'lexoffice_woocommerce_de_ui_render_options_invoice', $options );
}

/**
* Update bearer when saving options
* 
* @wp-hook woocommerce_de_ui_update_options
* @param Array $options
* @return void
*/
function lexoffice_woocommerce_de_ui_update_options( $options ) {
	
	if ( isset( $_POST[ 'submit_save_wgm_options' ] ) ) { 
		if ( wp_verify_nonce( $_POST[ 'update_wgm_settings' ], 'woocommerce_de_update_wgm_settings' ) ) {
			
			$last_used_code = get_option( 'lexoffice_woocommerce_last_auth_code', '' );

			if ( isset( $_REQUEST[ 'woocommerce_de_lexoffice_authorization_code' ] ) && ( $last_used_code != $_REQUEST[ 'woocommerce_de_lexoffice_authorization_code' ] ) ) {

				delete_option( 'lexoffice_woocommerce_barear' );
				delete_option( 'lexoffice_woocommerce_refresh_token' );
				delete_option( 'lexoffice_woocommerce_refresh_time' );
				delete_option( 'lexoffice_woocommerce_last_auth_code' );

				// Update Bearer
				return German_Market_Lexoffice_API_Auth::get_bearer();

			}

			// Revoke 
			if ( isset( $_REQUEST[ 'woocommerce_de_lexoffice_revoke' ] ) && $_REQUEST[ 'woocommerce_de_lexoffice_revoke' ] == 'on' ) {

				update_option( 'woocommerce_de_lexoffice_revoke', 'off' );

				delete_option( 'lexoffice_woocommerce_barear' );
				delete_option( 'lexoffice_woocommerce_refresh_token' );
				delete_option( 'lexoffice_woocommerce_refresh_time' );
				delete_option( 'lexoffice_woocommerce_last_auth_code' );
				delete_option( 'woocommerce_de_lexoffice_authorization_code' );

			}
		}
	}
}
