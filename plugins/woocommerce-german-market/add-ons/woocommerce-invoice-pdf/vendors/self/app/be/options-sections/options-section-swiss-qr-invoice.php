<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//////////////////////////////////////////////////
// init
//////////////////////////////////////////////////
$options = array();
$options[] = array( 'name' => __( 'Test Invoice', 'woocommerce-german-market' ), 'type' => 'wp_wc_invoice_pdf_test_download_button' );	

//////////////////////////////////////////////////
// General Infos
//////////////////////////////////////////////////
$main_info = '';

if ( ! Woocommerce_Invoice_Pdf::has_php_7_4_for_qr_codes() ) {
	$main_info .= '<div class="german-market-requirement-error">' . __( 'Unfortunately, this function is not available.', 'woocommerce-german-market' ) . PHP_EOL;
	$main_info .= __( 'To use this function, at least PHP version 7.4 is required.', 'woocommerce-german-market' ) . PHP_EOL;
	$main_info .= sprintf( __( 'Only PHP version %s is active on your server.', 'woocommerce-german-market' ), PHP_VERSION ) . PHP_EOL;
	$main_info .= __( 'Therefore, no Swiss QR invoice can be output with this function in the invoice PDF.', 'woocommerce-german-market' ) . PHP_EOL;
	$main_info .= __( 'Please ask your hoster / server admin if and how you can update to a current PHP version.', 'woocommerce-german-market' ) . '</div>';
}

$main_info .= __( 'With these settings a Swiss QR invoice can be displayed on the PDF invoice from German Market.', 'woocommerce-german-market' );
$main_info .= PHP_EOL . PHP_EOL . __( 'The output takes place on the last page of the invoice content. If there is not enough space at the bottom, a new page will be added. The display takes place before output of the small print, if this is output in the PDF invoice.', 'woocommerce-german-market' );
$main_info .= PHP_EOL . PHP_EOL . __( 'In order for the QR Invoice to appear, the required data for the remittee must be entered and the payment methods must be selected for which the QR Invoice is to appear. The QR invoice supports only amounts in EUR or CHF and will be displayed if the order has a total value greater than 0 EUR.', 'woocommerce-german-market' );
$main_info .= PHP_EOL . PHP_EOL . __( 'If a test PDF is downloaded from this menu, the Swiss QR Invoice will always appear', 'woocommerce-german-market' );

$code_line = "<br><code>do_action( 'wp_wc_invoice_pdf_before_fine_print', &#36;order, &#36;args );</code>";
$main_info .= PHP_EOL . PHP_EOL . sprintf( __( 'If you have customized your invoice PDF template in your activated child theme, make sure that before outputting the small print, the line %s is executed so that the QR invoice can be printed.', 'woocommerce-german-market' ), $code_line );

$options[] = array( 'title' => __( 'Swiss QR Invoice', 'woocommerce-german-market' ), 'type' => 'title','desc' => $main_info, 'id' => 'wp_wc_invoice_pdf_swiss_qr_invoice_heading' );
$options[] = array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_swiss_qr_invoice_heading' );

//////////////////////////////////////////////////
// Remit Recipient
//////////////////////////////////////////////////
$remit_recipient_info = __( 'Please enter the data of the payee here. The required data is marked with an *. If not all required data is entered, the QR code cannot be output.', 'woocommerce-german-market' );

$options[] = array( 'title' => __( 'Remit Recipient', 'woocommerce-german-market' ), 'type' => 'title','desc' => $remit_recipient_info, 'id' => 'wp_wc_invoice_pdf_swiss_qr_invoice_remit_recipient' );

$options[] = array(
				'name' 		=> __( 'Name', 'woocommerce-german-market' ) . '*',
				'id'   		=> 'wp_wc_invoice_pdf_swiss_qr_invoice_remit_recipient_name',
				'type' 		=> 'text',
				'default'  	=> wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ),
				'css'      	=> 'width: 500px;',
			);

$options[] = array(
				'name' 		=> __( 'Address line', 'woocommerce-german-market' ) . '*',
				'id'   		=> 'wp_wc_invoice_pdf_swiss_qr_invoice_remit_recipient_address',
				'type' 		=> 'text',
				'default'  	=> get_option( 'woocommerce_store_address', '' ),
				'css'      	=> 'width: 500px;',
			);

$options[] = array(
				'name' 		=> __( 'Postcode / ZIP', 'woocommerce-german-market' ) . '*',
				'id'   		=> 'wp_wc_invoice_pdf_swiss_qr_invoice_remit_recipient_postcode',
				'type' 		=> 'text',
				'default'  	=> get_option( 'woocommerce_store_postcode', '' ),
				'css'      	=> 'width: 500px;',
			);

$options[] = array(
				'name' 		=> __( 'City', 'woocommerce-german-market' ) . '*',
				'id'   		=> 'wp_wc_invoice_pdf_swiss_qr_invoice_remit_recipient_city',
				'type' 		=> 'text',
				'default'  	=> get_option( 'woocommerce_store_city', '' ),
				'css'      	=> 'width: 500px;',
			);

$options[] = array(
				'name' 		=> __( 'Country', 'woocommerce-german-market' ) . '*',
				'id'   		=> 'wp_wc_invoice_pdf_swiss_qr_invoice_remit_recipient_country',
				'type' 		=> 'select',
				'css'      	=> 'width: 500px;',
				'options'	=> WC()->countries->get_countries(),
				'class'		=> 'wc-enhanced-select',
				'default'	=> WC()->countries->get_base_country(),
			);

$options[] = array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_swiss_qr_invoice_remit_recipient' );

//////////////////////////////////////////////////
// Payment Reference
//////////////////////////////////////////////////
$options[] = array( 'title' => __( 'Payment Reference and Additonal Information', 'woocommerce-german-market' ), 'type' => 'title','desc' => '', 'id' => 'wp_wc_invoice_pdf_swiss_qr_invoice_reference' );

$options[] = array(
				'name' 		=> __( 'Variant', 'woocommerce-german-market' ),
				'desc_tip'	=> __( 'Choose one of the different QR Invoice variants. Please check with your bank which variant you should prefer.', 'woocommerce-german-market' ),
				'id'   		=> 'wp_wc_invoice_pdf_swiss_qr_invoice_variant',
				'type' 		=> 'select',
				'css'      	=> 'width: 500px;',
				'class'		=> 'wc-enhanced-select',
				'default'	=> 'qr',
				'options'	=> array(
								'qr'		=> __( 'QR-IBAN and QR reference', 'woocommerce-german-market' ),
								'scor'		=> __( 'IBAN and creditor reference', 'woocommerce-german-market' ),
								'non'		=> __( 'IBAN without reference', 'woocommerce-german-market' ),
							),
			);

$options[] = array(
				'name' 		=> __( 'QR-IBAN', 'woocommerce-german-market' ) . '*',
				'id'   		=> 'wp_wc_invoice_pdf_swiss_qr_invoice_v1_qr_iban',
				'type' 		=> 'text',
				'css'      	=> 'width: 500px;',
				'class'		=> 'swiss_qr_invoice_v1',
				'default'	=> '',
			);

$options[] = array(
				'name' 		=> __( 'Customer Identification Number', 'woocommerce-german-market' ) . '*',
				'id'   		=> 'wp_wc_invoice_pdf_swiss_qr_invoice_v1_customer_id',
				'type' 		=> 'text',
				'css'      	=> 'width: 500px;',
				'default'	=> '',
				'class'		=> 'swiss_qr_invoice_v1',
				'desc_tip'	=> __( 'If the QR-IBAN is used, a QR reference must be specified. This consists of 26 digits and a check digit. To avoid incorrect bookings, it is recommended to use the customer identification number (BESR-ID) in the first six digits, which you can obtain from the bank just like the QR-IBAN.', 'woocommerce-german-market' ),
			);

$internal_reference_options = array(
	'order_number'	=> __( 'Order Number', 'woocommerce-german-market' ),
);
if ( class_exists( 'Woocommerce_Running_Invoice_Number' ) ) {
	$internal_reference_options[ 'invoice_number' ] = __( 'Invoice Number', 'woocommerce-german-market' );
}

$internal_reference_options = apply_filters( 'wp_wc_invoice_pdf_swiss_qr_invoice_internal_reference_options', $internal_reference_options );

$options[] = array(
				'name' 		=> __( 'Internal Reference Number', 'woocommerce-german-market' ) . '*',
				'id'   		=> 'wp_wc_invoice_pdf_swiss_qr_invoice_v1_internal',
				'type' 		=> 'select',
				'css'      	=> 'width: 500px;',
				'default'	=> 'order_number',
				'options'	=> $internal_reference_options,
				'class'		=> 'wc-enhanced-select swiss_qr_invoice_v1',
				'desc_tip'	=> __( 'For the remaining part of the QR reference, the order number is output as the internal reference. If you have activated the invoice number add-on from German Market, you can choose between invoice number and order number for the internal reference.', 'woocommerce-german-market' ),
			);

$options[] = array(
				'name' 		=> __( 'IBAN', 'woocommerce-german-market' ) . '*',
				'id'   		=> 'wp_wc_invoice_pdf_swiss_qr_invoice_v2_v3_iban',
				'type' 		=> 'text',
				'css'      	=> 'width: 500px;',
				'default'	=> '',
				'class'		=> 'swiss_qr_invoice_v2 swiss_qr_invoice_v3',
			);

$reference_placeholders = __( 'Order Number - <code>{{order-number}}</code>', 'woocommerce-german-market' );
if ( class_exists( 'Woocommerce_Running_Invoice_Number' ) ) {
	$reference_placeholders .= ', ' . __( 'Invoice Number - <code>{{invoice-number}}</code>', 'woocommerce-german-market' );
}

$options[] = array(
				'name' 		=> __( 'Creditor Reference', 'woocommerce-german-market' ) . '*',
				'id'   		=> 'wp_wc_invoice_pdf_swiss_qr_invoice_v2_creditor_reference',
				'type' 		=> 'text',
				'css'      	=> 'width: 500px;',
				'default'	=> '',
				'class'		=> 'swiss_qr_invoice_v2',
				'desc'		=> __( 'You can use the following placeholders:', 'woocommerce-german-market' ) . ' ' . $reference_placeholders,
				'desc_tip'	=> __( 'If the IBAN is used, a Creditor Reference can also be specified. Here, a maximum of 21 digits can be freely assigned. Permissible are digits as well as the letters from a-z or A-Z. Other characters as well as characters after the 21st digit are removed before output.', 'woocommerce-german-market' ),
			);

$placeholders = __( 'Customer\'s first name - <code>{{first-name}}</code>, customer\'s last name - <code>{{last-name}}</code>, Order Total - <code>{{order-total}}</code>, Order Number - <code>{{order-number}}</code>', 'woocommerce-german-market' );
if ( class_exists( 'Woocommerce_Running_Invoice_Number' ) ) {
	$placeholders .= ', ' . __( 'Invoice Number - <code>{{invoice-number}}</code>', 'woocommerce-german-market' );
}

$options[] = array(
				'name' 		=> __( 'Additonal Information', 'woocommerce-german-market' ),
				'id'   		=> 'wp_wc_invoice_pdf_swiss_qr_invoice_additional_information_text',
				'type' 		=> 'text',
				'default'  	=> __( 'Order {{order-number}}', 'woocommerce-german-market' ),
				'css'      	=> 'width: 500px;',
				'desc_tip'	=> __( 'Purpose of use that appears in the bank transfer.', 'woocommerce-german-market' ),
				'desc'		=> __( 'You can use the following placeholders:', 'woocommerce-german-market' ) . ' ' . $placeholders,
			);


$options[] = array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_swiss_qr_invoice_reference' );

//////////////////////////////////////////////////
// Payment methods output
//////////////////////////////////////////////////
$allowed_payment_methods = apply_filters( 'wp_wc_invoice_pdf_swiss_qr_invoice_supported_gateways', array(
	'german_market_purchase_on_account' => __( 'Purchase On Acccount', 'woocommerce-german-market' ),
	'bacs'								=> __( 'Direct bank transfer', 'woocommerce-german-market' ),
));

$payment_method_info = __( 'Here you can select the payment methods for which the Swiss QR code should appear in the invoice pdf.', 'woocommerce-german-market' );

$options[] = array( 'title' => __( 'Payment Methods', 'woocommerce-german-market' ), 'type' => 'title','desc' => $payment_method_info, 'id' => 'wp_wc_invoice_pdf_swiss_qr_invoice_payment_methods' );

foreach ( $allowed_payment_methods as $gateway_id => $gateway_name ) {

	$options[] = array(
					'name'		=> $gateway_name,
					'id'   		=> 'wp_wc_invoice_pdf_swiss_qr_invoice_gateway_' . $gateway_id,
					'type' 		=> 'wgm_ui_checkbox',
					'default'  	=> 'off',
				);
}

$options[] = array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_swiss_qr_invoice_payment_methods' );

//////////////////////////////////////////////////
// Billing Countries
//////////////////////////////////////////////////

$billing_countries_info = __( 'Here you can select for which billing countries the Swiss QR code should appear on the invoice pdf.', 'woocommerce-german-market' );

$options[] = array( 'title' => __( 'Billing Countries', 'woocommerce-german-market' ), 'type' => 'title','desc' => $billing_countries_info, 'id' => 'wp_wc_invoice_pdf_swiss_qr_invoice_billing_countries' );

$options[] = array(
				'name' 		=> __( 'Enable for Billing Countries', 'woocommerce-german-market' ),
				'id'   		=> 'wp_wc_invoice_pdf_swiss_qr_invoice_billing_countries_option',
				'type' 		=> 'select',
				'css'      	=> 'width: 500px;',
				'options'	=> array(
									'all' 			=> __( 'Enable for all billing countries', 'woocommerce-german-market' ),
									'all_except'	=> __( 'Enable for all billing countries, except for ...', 'woocommerce-german-market' ),
									'specific'		=> __( 'Enable for specific billing countries', 'woocommerce-german-market' ),
				),
				'class'		=> 'wc-enhanced-select',
				'default'	=> array(),
			);

$options[] = array(
				'name' 		=> __( 'Countries', 'woocommerce-german-market' ),
				'id'   		=> 'wp_wc_invoice_pdf_swiss_qr_invoice_billing_countries',
				'type' 		=> 'multiselect',
				'css'      	=> 'width: 500px;',
				'options'	=> WC()->countries->get_countries(),
				'class'		=> 'wc-enhanced-select',
				'default'	=> array(),
			);

$options[] = array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_swiss_qr_invoice_billing_countries' );

//////////////////////////////////////////////////
// Output in Invoice PDF
//////////////////////////////////////////////////
$options[] = array( 'title' => __( 'Output in Invoice PDF', 'woocommerce-german-market' ), 'type' => 'title','desc' => '', 'id' => 'wp_wc_invoice_pdf_swiss_qr_invoice_payment_methods' );

$options[] = array(
				'name'		=> __( 'Hide Page Numbers', 'woocommerce-german-market' ),
				'id'   		=> 'wp_wc_invoice_pdf_swiss_qr_invoice_hide_page_numbers',
				'type' 		=> 'wgm_ui_checkbox',
				'default'  	=> 'on',
				'desc_tip'	=> __( 'If you output page numbers on the invoice PDF, they should not appear in the QR Invoice part, which is always output at the bottom. Therefore, you should generally output the page numbers elsewhere or hide the page numbers on the page with the QR invoice. To do the latter, activate this setting.', 'woocommerce-german-market' ),
			);

$options[] = array( 'type' => 'sectionend', 'id' => 'wp_wc_invoice_pdf_swiss_qr_invoice_output' );
