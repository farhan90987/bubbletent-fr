<?php

namespace MarketPress\German_Market\E_Invoice;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

class Backend_Menu {

	/**
	 * Add submenu for E-Inovice
	 * 
	 * @param Array $items
	 * @return Array
	 */
	public static function backend_menu( $submenu ) {

		$submenu[] = array(
			'title'		=> __( 'E-invoice', 'woocommerce-german-market' ),
			'slug'		=> 'einvoice',
			'callback'	=> array( __CLASS__, 'render_menu_einvoice_settings' ),
			'options'	=> 'yes'
		);

		return $submenu;
	}

	/**
	 * E-Invoice options in submenu
	 * 
	 * @return Array
	 */
	public static function render_menu_einvoice_settings() {

		$options = array();

		$options[] = array(
			'title'		=> __( 'Company data', 'woocommerce-german-market' ),
			'type'		=> 'title',
		);

		$options[] = array(
			'title'		=> __( 'Company name', 'woocommerce-german-market' ),
			'type'		=> 'text',
			'id'		=> 'german_market_einvoice_company_name',
			'default'	=> get_option( 'blogname' ),
		);

		$options[] = array(
			'title'		=> __( 'Managing director', 'woocommerce-german-market' ),
			'type'		=> 'text',
			'id'		=> 'german_market_einvoice_managing_director',
			'default'	=> '',
		);

		$options[] = array(
			'title'		=> __( 'Company registration number', 'woocommerce-german-market' ),
			'type'		=> 'text',
			'id'		=> 'german_market_einvoice_seller_global_id',
			'default'	=> '',
		);

		$options[] = array(
			'title'		=> __( 'IBAN', 'woocommerce-german-market' ),
			'type'		=> 'text',
			'id'		=> 'german_market_einvoice_iban',
			'default'	=> '',
			'desc_tip'	=> __( 'Is specified if BACS is the payment method.', 'woocommerce-german-market' ),
		);

		$options[] = array(
			'title'		=> __( 'Local tax number', 'woocommerce-german-market' ),
			'type'		=> 'text',
			'id'		=> 'german_market_einvoice_seller_tax_registration_fc',
			'desc_tip'	=> __( 'You get this from your local tax office, e.g. 123/456/78900', 'woocommerce-german-market' ), 
			'default'	=> '',
		);

		$options[] = array(
			'title'		=> __( 'EU VAT Identification Number (VATIN)', 'woocommerce-german-market' ),
			'type'		=> 'text',
			'id'		=> 'german_market_einvoice_seller_tax_registration_va',
			'desc_tip'	=> __( 'E.g. DE123456789', 'woocommerce-german-market' ),
			'default'	=> str_replace( '-', '', get_option( 'german_market_vat_requester_member_state' ) ) . get_option( 'german_market_vat_requester_vat_number' ),
		);

		$options[] = array(
			'title'		=> __( 'Contact - Name', 'woocommerce-german-market' ),
			'type'		=> 'text',
			'id'		=> 'german_market_einvoice_seller_contact_name',
			'default'	=> '',
			'desc_tip'	=> __( 'Please enter your first and last name.', 'woocommerce-german-market' ),
		);

		$options[] = array(
			'title'		=> __( 'Contact - Email', 'woocommerce-german-market' ),
			'type'		=> 'text',
			'id'		=> 'german_market_einvoice_seller_contact_email',
			'default'	=> get_option( 'admin_email' ),
		);

		$options[] = array(
			'title'		=> __( 'Contact - Phone', 'woocommerce-german-market' ),
			'type'		=> 'text',
			'id'		=> 'german_market_einvoice_seller_contact_phone',
			'default'	=> '',
		);

		$options[] = array(
			'title'		=> __( 'Contact - Fax', 'woocommerce-german-market' ),
			'type'		=> 'text',
			'id'		=> 'german_market_einvoice_seller_contact_fax',
			'default'	=> '',
		);

		$options[] = array( 'type' => 'sectionend' );

		$options[] = array(
			'title'		=> __( 'Sending of e-invoices', 'woocommerce-german-market' ),
			'type'		=> 'title',
		);

		$options[] = array(
			'title'		=> __( 'Send ZUGFeRD invoices', 'woocommerce-german-market' ),
			'type'		=> 'wgm_ui_checkbox',
			'id'		=> 'german_market_einvoice_send_zugferd_invoices',
			'default'	=> 'off',
			'desc'		=> __( 'If the setting is activated, the machine-readable XML is embedded in the human-readable PDF files - in accordance with Directive EU/2014/55 and the EN16931 standard.', 'woocommerce-german-market' )
		);

		$all_mails = WC()->mailer()->get_emails();
		$mail_options = array();

		foreach ( $all_mails as $email ) {
			$mail_options[ $email->id ] = $email->title;
		}

		$mail_options[ 'customer_order_confirmation' ] = __( 'Order Confirmation', 'woocommerce-german-market' );
		asort( $mail_options );

		$hide = array(
			'customer_new_account',
			'customer_reset_password',
			'customer_note',
		);
		
		foreach ( $hide as $email_key ) {
			if ( isset( $mail_options[ $email_key ] ) ) {
				unset( $mail_options[ $email_key ] );
			}
		}

		$options[] = array(
			'title'		=> __( 'Attach XML separately to emails', 'woocommerce-german-market' ),
			'type'		=> 'multiselect',
			'id'		=> 'german_market_einvoice_recipients_xml_emails',
			'default'	=> array(),
			'options'	=> $mail_options,
			'class' 	=> 'wc-enhanced-select',
			'desc'		=> __( 'If you don\'t want to send ZUGFerD invoices or PDF invoices at all, you can attach the e-invoice in XML format individually to selected WooCommerce emails.', 'woocommerce-german-market' ),
		);

		$options[] = array(
			'title'		=> __( 'E-invoices recipients', 'woocommerce-german-market' ),
			'type'		=> 'select',
			'id'		=> 'german_market_einvoice_recipients',
			'default'	=> 'base_country_companies',
			'options'	=> array(

				'all' => __( 'All invoice recipients', 'woocommerce-german-market' ),
				'base_country_companies' => __( 'Companies from Germany', 'woocommerce-german-market' ),
				'base_country' => __( 'Invoice recipients from Germany', 'woocommerce-german-market' ),

			),
			'class' 	=> 'wc-enhanced-select',
		);

		$options[] = array( 'type' => 'sectionend' );

		$options[] = array(
			'title'		=> __( 'Backend', 'woocommerce-german-market' ),
			'type'		=> 'title',
		);

		$options[] = array(
			'title'		=> __( 'XML Download', 'woocommerce-german-market' ),
			'type'		=> 'wgm_ui_checkbox',
			'id'		=> 'german_market_einvoice_backend_download_xml',
			'default'	=> 'off',
			'desc_tip'	=> __( 'If this setting is activated, you can download the separate XML file in the backend (individually for orders / refunds or as a bulk action)', 'woocommerce-german-market' ),
		);

		$options[] = array( 'type' => 'sectionend' );

		/**
		 * Filter options for e-invoice settings
		 * 
		 * @since 1.0
		 * @param Array $options
		 */
		return apply_filters( 'gm_invoice_pdf_einvoice_settings', $options );
	}
}
