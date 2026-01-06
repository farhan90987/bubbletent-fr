<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//////////////////////////////////////////////////
// init
//////////////////////////////////////////////////

$options     = array();
$description = __( 'You can give logged-in customers the option to download their invoices on the "My Account" page under the "View Order" endpoint. You can decide for which order status the download button is available.', 'woocommerce-german-market' );

$options[]   = array(
	'title' => __( 'My Account Page', 'woocommerce-german-market' ),
	'type'  => 'title',
	'desc'  => $description,
	'id'    => 'wp_wc_invoice_pdf_my_account_page',
);

$options[] = array(
	'type' => 'sectionend',
	'id'   => 'wp_wc_invoice_pdf_my_account_page',
);

$options[]   = array(
	'title' => __( 'Invoice PDF', 'woocommerce-german-market' ),
	'type'  => 'title',
	'id'   => 'wp_wc_invoice_pdf_section_invoice_pdf',
);

$statusi   = wc_get_order_statuses();
$status_nr = count( $statusi );
$i         = 0;

//////////////////////////////////////////////////
// options
//////////////////////////////////////////////////

foreach ( $statusi as $status_key => $status_nice_name ) {

	$i++;
	if ( $i == 1 ) {
		$checkboxgroup = 'start';
	} else
	if ( $i == $status_nr ) {
		$checkboxgroup = 'ende';
	} else {
		$checkboxgroup = 'wp_wc_invoice_pdf_checkboxgroup_my_account_page';
	}

	$options[] = array(
		'title'         => __( 'Download Button', 'woocommerce-german-market' ),
		'desc'          => __( 'Enable download button for orders with status:', 'woocommerce-german-market' ) . ' "' . $status_nice_name . '"',
		'id'            => 'wp_wc_invoice_pdf_frontend_download_' . str_replace( 'wc-', '', $status_key ),
		'default'       => 'no',
		'type'          => 'checkbox',
		'checkboxgroup' => $checkboxgroup,
	);
}

$options[] = array(
	'name'     => __( 'Button Text', 'woocommerce-german-market' ),
	'desc_tip' => __( 'Enter a text that is shown on the download button', 'woocommerce-german-market' ),
	'tip'      => __( 'Enter a text that is shown on the download button', 'woocommerce-german-market' ),
	'id'       => 'wp_wc_invoice_pdf_view_order_button_text',
	'type'     => 'text',
	'default'  => __( 'Download Invoice Pdf', 'woocommerce-german-market' ),
	'css'      => 'min-width:250px;',
);

$options[] = array(
	'type' => 'sectionend',
	'id'   => 'wp_wc_invoice_pdf_section_invoice_pdf',
);

$options[]   = array(
	'title' => __( 'Refund PDF', 'woocommerce-german-market' ),
	'type'  => 'title',
	'id'    => 'wp_wc_invoice_pdf_section_refund_pdf',
);

$options[] = array(
	'name'     => __( 'Activate Download Button for Refund Pdf', 'woocommerce-german-market' ),
	'desc_tip' => __( 'Enable/Disable the Refund Pdf download button', 'woocommerce-german-market' ),
	'tip'      => __( 'Enable/Disable the Refund Pdf download button', 'woocommerce-german-market' ),
	'id'       => 'wp_wc_invoice_pdf_frontend_download_refund_pdf',
	'type'     => 'wgm_ui_checkbox',
	'default'  => 'off',
);

// Check if Invoice number add-on is activated?
if ( class_exists( 'Woocommerce_Running_Invoice_Number' ) ) {
	$placeholders = array(
		'<code>{{refund-id}}</code> (' . __( 'Refund ID', 'woocommerce-german-market' ) . ')',
		'<code>{{refund-number}}</code> (' . __( 'Refund Number', 'woocommerce-german-market' ) . ')',
		'<code>{{invoice-number}}</code> (' . __( 'Invoice Number', 'woocommerce-german-market' ) . ')',
	);
	$refund_button_text_desc = __( 'You can use the following placeholders:', 'woocommerce-german-market' ) . implode( ', ', $placeholders );
} else {
	$placeholders = array(
		'<code>{{refund-id}}</code> (' . __( 'Refund ID', 'woocommerce-german-market' ) . ')',
	);
	$refund_button_text_desc = __( 'You can use the following placeholders:', 'woocommerce-german-market' ) . implode( ', ', $placeholders );
}

$options[] = array(
	'name'     => __( 'Button Text (Refund)', 'woocommerce-german-market' ),
	'desc_tip' => __( 'Enter a text that is shown on the download button', 'woocommerce-german-market' ),
	'desc'     => $refund_button_text_desc,
	'tip'      => __( 'Enter a text that is shown on the download button', 'woocommerce-german-market' ),
	'id'       => 'wp_wc_invoice_pdf_view_order_refund_button_text',
	'type'     => 'text',
	'default'  => __( 'Download Refund #{{refund-id}} Pdf', 'woocommerce-german-market' ),
	'css'      => 'min-width:250px;',
);

$options[] = array(
	'type' => 'sectionend',
	'id'   => 'wp_wc_invoice_pdf_section_refund_pdf',
);

$options[]   = array(
	'title' => __( 'Link Behaviour', 'woocommerce-german-market' ),
	'type'  => 'title',
	'id'    => 'wp_wc_invoice_pdf_section_link_behaviour',
);

$options[] = array(
	'name'     => __( 'Link Behaviour', 'woocommerce-german-market' ),
	'desc_tip' => __( 'Open the invoice link in a new browser tab or not. In the first case the HTML <code>&lt;a&gt;</code> tag gets the attribute <code>target="blank"</code>', 'woocommerce-german-market' ),
	'tip'      => __( 'Open the invoice link in a new browser tab or not. In the first case the HTML <code>&lt;a&gt;</code> tag gets the attribute <code>target="blank"</code>', 'woocommerce-german-market' ),
	'id'       => 'wp_wc_invoice_pdf_view_order_link_behaviour',
	'type'     => 'select',
	'css'      => 'min-width:250px;',
	'default'  => 'new',
	'options'  => array(
		'new'     => __( 'New browser tab', 'woocommerce-german-market' ),
		'current' => __( 'Current browser tab', 'woocommerce-german-market' ),
	),
);

$options[] = array(
	'name'     => __( 'Download Behaviour', 'woocommerce-german-market' ),
	'desc_tip' => __( 'If "Download" is selected the browser forces a file download. The a-tag gets the attribute "download" (HTML5). If "Inline" is selected the file will be send inline to the browser, i.e. the browser will try to open the file in a tab using a browser plugin to display pdf files if available', 'woocommerce-german-market' ),
	'id'       => 'wp_wc_invoice_pdf_view_order_download_behaviour',
	'type'     => 'select',
	'css'      => 'min-width:250px;',
	'default'  => 'inline',
	'options'  => array(
		'inline'   => __( 'Inline', 'woocommerce-german-market' ),
		'download' => __( 'Download', 'woocommerce-german-market' ),
	),
);

$options[] = array(
	'type' => 'sectionend',
	'id'   => 'wp_wc_invoice_pdf_section_link_behaviour',
);
