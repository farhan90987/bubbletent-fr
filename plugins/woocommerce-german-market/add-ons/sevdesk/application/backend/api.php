<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
* API - send order
*
* @param WC_ORDER $order
* @return String ("SUCCESS" or "ERROR: {your error Message}")
*/
function sevdesk_woocomerce_api_send_order( $order, $show_errors = true ) {
	return German_Market_SevDesk_API_Order::send_order( $order, $show_errors );
}

/**
* API - send refund
*
* @param WC_ORDER $order
* @return String ("SUCCESS" or "ERROR: {your error Message}")
*/
function sevdesk_woocommerce_api_send_refund( $refund, $show_errors = true ) {
	return German_Market_SevDesk_API_Refund::send_refund( $refund, $show_errors );
}

/**
* send refund as voucher to sevdesk
*
* @param Array $args
* @return String
*/
function sevdesk_woocommerce_api_send_voucher_refund( $args, $show_errors = true ) {
	return German_Market_SevDesk_API_Refund::send_voucher_refund( $args, $show_errors );
}

/**
* send order as voucher to sevdesk
*
* @param Array $args
* @return String
*/
function sevdesk_woocommerce_api_send_voucher( $args, $show_errors = true ) {
	return German_Market_SevDesk_API_Order::send_voucher( $args, $show_errors );
}

/**
* create guest user data in sevdesk
*
* @param Array $args
* @return Integer
*/
function sevdesk_woocommerce_api_contact_guest_user( $args ) {
	return German_Market_SevDesk_API_Contact::guest_user( $args );
}

/**
* create or update user data in sevdesk
*
* @param Integer $wordpress_user_id
* @return Integer
*/
function sevdesk_woocommerce_api_contact( $wordpress_user_id, $args ) {
	return German_Market_SevDesk_API_Contact::contact( $wordpress_user_id, $args );
}

/**
* build company array from wordpress user_id
*
* @param Integer $wordpress_user_id
* @return Mixed: false (no company) / Array
*/
function sevdesk_woocommerce_api_contact_build_company_array( $wordpress_user_id, $order = null ) {
	return German_Market_SevDesk_API_Contact::build_company_array( $wordpress_user_id, $order );
}

/**
* build company array from order for guest users
*
* @param Integer $wordpress_user_id
* @return Mixed: false (no company) / Array
*/
function sevdesk_woocommerce_api_contact_build_company_array_guest( $order ) {
	return German_Market_SevDesk_API_Contact::build_company_array_guest( $order );
}

/**
* build customer array from order for guest user
*
* @param WC_Order
* @return Array
*/
function sevdesk_woocommerce_api_contact_build_customer_guest_array( $order ) {
	return German_Market_SevDesk_API_Contact::build_customer_guest_array( $order );
}

/**
* build customer array from wordpress user_id
*
* @param Integer $wordpress_user_id
* @return Array
*/
function sevdesk_woocommerce_api_contact_build_customer_array( $wordpress_user_id, $order = null ) {
	return German_Market_SevDesk_API_Contact::build_customer_array( $wordpress_user_id, $order );
}

/**
* get customer vat number by order or wordpress_user_id
*
* @param Integer $wordpress_user_id
* @return Array
*/
function sevdesk_get_vat_number_of_order_and_wordpress_user_id( $order = null, $wordpress_user_id = null ) {
	return German_Market_SevDesk_API_Contact::get_vat_number_of_order_and_wordpress_user_id( $order, $wordpress_user_id );
}

/**
* add additional customer data
*
* @param String $endpoint
* @param Integer $wordpress_user_id
* @param Integer $sevdesk_user_id
* @param Array $args
* @param Integer $address_category
* @return void
*/
function sevdesk_woocommerce_api_contact_add_data( $endpoint, $wordpress_user_id, $sevdesk_user_id, $args, $address_category = 47, $update = false, $addresses_and_communication_ways = array() ) {
	German_Market_SevDesk_API_Contact::contact_add_data( $endpoint, $wordpress_user_id, $sevdesk_user_id, $args, $address_category, $update, $addresses_and_communication_ways  );
}

/**
* add additional customer data for guest users
*
* @param String $endpoint
* @param Integer $wordpress_user_id
* @param Integer $sevdesk_user_id
* @param Array $args
* @param Integer $address_category
* @return void
*/
function sevdesk_woocommerce_api_contact_add_data_guest( $endpoint, $order, $sevdesk_user_id, $args, $address_category = 47, $update = false, $user_data = array(), $addresses_and_communication_ways = array() ) {
	German_Market_SevDesk_API_Contact::add_data_guest( $endpoint, $order, $sevdesk_user_id, $args, $address_category, $update, $user_data, $addresses_and_communication_ways );
}

/**
* this functions checks if the value of an endpoint (email, phone, address)
* already exists for a sevdesk user. It returns true if this data is new.
*
* @param String $sevdes_user_id
* @param Array $data
* @param String $endpoint
* @param Array $args
* @param Integer $address_category
* @return Boolean
*/
function sevdesk_woocommerce_update_sevdesk_user_data( $sevdesk_user_id, $data, $endpoint, $args, $address_category, $user_data ) {
	return German_Market_SevDesk_API_Contact::update_sevdesk_user_data( $sevdesk_user_id, $data, $endpoint, $args, $address_category, $user_data );
}

/**
* get sevdesk_user bei sevdesk_user_id
*
* @param Integer $sevdesk_user_id
* @return -1 OR Array
*/
function sevdesk_woocommerce_api_contact_get_by_customer_number( $sevdesk_customer_number, $args ) {
	return German_Market_SevDesk_API_Contact::get_by_customer_number( $sevdesk_customer_number, $args );
}

/**
* get all address data and communication ways (phone & email) of a sevdesk user
*
* @param String $sevdesk_user_id
* @param Array $args
* @return Array
*/
function sevdesk_woocommerce_get_contact_addresses_and_communication_ways( $sevdesk_user_id, $args ) {
	return German_Market_SevDesk_API_Contact::get_contact_addresses_and_communication_ways( $sevdesk_user_id, $args );
}

/**
* check if a sevdesk user with the same email exists
* and return the sevdesk user id
*
* @param String $email
* @param Array $args
* @return String
*/
function sevdesk_woocommerce_api_contact_get_by_email( $email, $args ) {
	return German_Market_SevDesk_API_Contact::get_by_email( $email, $args );	
}

/**
* build temp file of invoice pdf
*
* @param Array $args
* @return String
*/
function sevdesk_woocommerce_api_build_temp_file( $args, $show_errors = true ) {
	return German_Market_SevDesk_API_PDF::build_temp_file( $args, $show_errors );
} 

/**
* get voucher status (exists or not)
*
* @param Integer $args
* @return Boolean
*/
function sevdesk_woocommerce_api_get_vouchers_status( $voucher_id, $show_errors = true ) {
	return German_Market_SevDesk_API_V1::get_vouchers_status( $voucher_id, $show_errors );
}

/**
* Get api token
* @return String
*/
function sevdesk_woocommerce_api_get_api_token( $show_errors = true ) {
	return German_Market_SevDesk_API_V1::get_api_token( $show_errors );
}

/**
* Get invoice pdf, path to file
* @param WC_Order $order
* @return String
*/
function sevdesk_woocommerce_api_get_invoice_pdf( $order ) {
	return German_Market_SevDesk_API_PDF::get_invoice_pdf( $order );
} 

/**
* Get refund pdf, path to file
* @param WC_Order $refund
* @return String
*/
function sevdesk_woocommerce_api_get_refund_pdf( $refund ) {
	return German_Market_SevDesk_API_PDF::get_refund_pdf( $refund );
} 

/**
* check if we can use the order
* @param WC_Order $order
* @return WC_Order
*/
function sevdesk_woocommerce_api_check_order( $order ) {
	return German_Market_SevDesk_API_V1::check_order( $order );
}

/**
* Markup for error message
* @param String $message
* @return String
*/
function sevdesk_woocommerce_api_get_error_message( $message = '', $order = null ) {
	return German_Market_SevDesk_API_V1::get_error_message( $message, $order );
}

/**
* Check if curl response is an error
* @param String $response
* @return void (exit if error)
*/
function sevdesk_woocommerce_api_curl_error_validaton( $response ) {
	German_Market_SevDesk_API_V1::curl_error_validaton( $response );
}

/**
* get base_url
* @return String
*/
function sevdesk_woocommerce_api_get_base_url() {
	return German_Market_SevDesk_API_V1::get_base_url();
}

/**
* get default value for strings of options 'sevdesk_voucher_description_order' or 'sevdesk_voucher_description_reund'
* depending on the former setting 'woocommerce_de_sevdesk_voucher_number'
*
* @since 3.9.2
* @param String $option_key
* @return String
*/
function sevdesk_woocommerce_get_default_value( $option_key ) {
	return German_Market_SevDesk_API_V1::get_default_value( $option_key );
}

/**
* Get type of check account
*
* @since 3.11.1.4
* @param Integer $checkaccount_id
* @return String
**/ 
function sevdesk_woocommerce_get_type_of_check_account( $checkaccount_id ) {
	return German_Market_SevDesk_API_V1::get_type_of_check_account( $checkaccount_id );
}

/**
* Get User Agent for CUrl
*
* @since 3.16
* @return String
**/
function sevdesk_woocommerce_get_user_agent() {
	return German_Market_SevDesk_API_V1::get_user_agent();
}
