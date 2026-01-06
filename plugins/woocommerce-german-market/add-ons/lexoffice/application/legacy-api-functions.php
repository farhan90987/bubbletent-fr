<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
* API - send voucher
*
* @param WC_ORDER $order
* @return String ("SUCCESS" or "ERROR: {your error Message}")
*/
function lexoffice_woocomerce_api_send_voucher( $order, $show_errors = true ) {
	return German_Market_Lexoffice_API_Order::send_voucher( $order, $show_errors );
}

/**
* API - create voucher, post method
*
* @param WC_ORDER $order
* @return String
*/
function lexoffice_woocomerce_api_send_voucher_post( $order, $show_errors = true ) {
	return German_Market_Lexoffice_API_Order::send_voucher_post( $order, $show_errors );
}

/**
* API - update voucher, put method
*
* @param WC_ORDER $order || Refund
* @return String
*/
function lexoffice_woocomerce_api_send_voucher_put( $order, $show_errors = true ) {
	return German_Market_Lexoffice_API_Order::send_voucher_put( $order, $show_errors );
}

/**
* API - send refund
*
* @param WC_ORDER $order
* @return String ("SUCCESS" or "ERROR: {your error Message}")
*/
function lexoffice_woocommerce_api_send_refund( $refund, $show_errors = true ) {
	return German_Market_Lexoffice_API_Refund::send_refund( $refund, $show_errors );
}

/**
* API - create refund voucher, post method
*
* @param WC_ORDER $order
* @return String
*/
function lexoffice_woocomerce_api_send_refund_post( $refund, $show_errors = true ) {
	return German_Market_Lexoffice_API_Refund::send_refund_post( $refund, $show_errors );
}

/**
* API - update refund, put method
*
* @param WC_ORDER $order || Refund
* @return String
*/
function lexoffice_woocomerce_api_send_refund_put( $refund, $show_errors = true ) {
	return German_Market_Lexoffice_API_Refund::send_refund_put( $refund, $show_errors );
}

/**
* Create Curlopt Postfields from a refund
*
* @param WC_Order_Refund $refund
* @param String $file
* @return String (JSON formated)
*/
function lexoffice_woocomerce_api_refund_to_curlopt_postfields( $refund, $file = null, $show_errors = true ) {
	return German_Market_Lexoffice_API_Refund::refund_to_curlopt_postfields( $refund, $file, $show_errors );
}

/**
* Create Curlopt Postfields
*
* @param WC_ORDER $order
* @param String $file
* @return String (JSON formated)
*/
function lexoffice_woocomerce_api_order_to_curlopt_postfields( $order, $file = null, $show_errors = true ) {
	return German_Market_Lexoffice_API_Order::order_to_curlopt_postfields( $order, $file, $show_errors );
}

/**
* API - send invoice pdf
*
* @param WC_ORDER $order
* @return String json response
*/
function lexoffice_woocomerce_api_upload_invoice_pdf( $voucher_id, $order, $is_refund = false, $show_errors = true ) {
	return German_Market_Lexoffice_API_PDF::upload_invoice_pdf( $voucher_id, $order, $is_refund, $show_errors );
}

/**
* Get voucher status
*
* @param String $voucher_id
* @param $return_bool
* @return Boolean (true if voucher exists) | Array if $return_bool is set to false
*/
function lexoffice_woocommerce_api_get_vouchers_status( $voucher_id, $return_bool = true) {
	return German_Market_Lexoffice_API_General::get_vouchers_status( $voucher_id, $return_bool );
}

/**
* API - get auth bearer, OAuth2 authorization
* @return String
*/
function lexoffice_woocomerce_api_get_bearer() {
	return German_Market_Lexoffice_API_Auth::get_bearer();
}

/**
* Revoke Authorization
*/
function lexoffice_woocomerce_api_revoke_auth() {
	German_Market_Lexoffice_API_Auth::revoke_auth();
}

/**
* Get beauty error text from json string if possible
* @param String
* @return String
*/
function lexoffice_woocomerce_get_error_text( $json, $order = null ) {
	return German_Market_Lexoffice_API_General::get_error_text( $json, $order );
}

/**
* Get all contacts
* @return Array
*/
function lexoffice_woocommerce_get_all_contacts() {
	return German_Market_Lexoffice_API_Contact::get_all_contacts();
}

/**
* Create a new lexoffice user
* @param WP_USer $wp_user
* @param WC_Order $order
* @return String (lexoffice contact id)
*/
function lexoffice_woocommerce_create_new_user( $wp_user, $order = null ) {
	return German_Market_Lexoffice_API_Contact::create_new_user( $wp_user, $order );
}

/**
* Build array for wp_user to be send to lexoffice
* @param WP_User $wp_user
* @param WP_Order $order
* @return array
*/
function lexoffice_woocommerce_build_customer_array( $wp_user, $order = null, $lexoffice_user_data = null ) {
	return German_Market_Lexoffice_API_Contact::build_customer_array( $wp_user, $order, $lexoffice_user_data );
}

/**
* Manipulate addresses for exceptions (e.g. Northern Ireland)
*
* @param Object $address
* @return Object
**/
function lexoffice_woocommerce_api_exceptions_for_addresses( $address ) {
	return German_Market_Lexoffice_API_Contact::exceptions_for_addresses( $address );
}

/**
* Use Collective Contact or lexoffice Users when sending the voucher
*
* @param Array $array
* @param WP_User $user
* @param WC_Order $order
* @return Array
**/
function lexoffice_woocommerce_api_add_user_to_voucher( $array, $user, $order = null ) {
	return German_Market_Lexoffice_API_Contact::add_user_to_voucher( $array, $user, $order );
}

/**
* Get all tax rates used in the shop
*
* @return Array
**/
function lexoffice_woocommerce_api_get_all_rates_in_shop() {
	return German_Market_Lexoffice_API_General::get_all_rates_in_shop();
}

/**
* Get lexoffice oss setting info
*
* @return String
**/
function lexoffice_woocommerce_api_get_oss_info() {
	return German_Market_Lexoffice_API_General::get_oss_info();
}

/**
* Update existing lexoffice user
*
* @param WP_User $user
* @param WC_Order $order
* @param Array  $response_array
* @param Integer $lexoffice_user_id
*
* @return void
**/
function lexoffice_woocommerce_api_update_user( $user, $order, $response_array, $lexoffice_user_id ) {
	German_Market_Lexoffice_API_Contact::update_user( $user, $order, $response_array, $lexoffice_user_id );
}
