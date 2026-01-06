<?php

namespace WcJUpsellator\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Api
{
    const API_NAMESPACE 		= 'wjufw/v';
    const API_VERSION 			= '1';

	const DATE_VALIDATION 					= "/^\d{4}\-\d{2}-\d{2}$/";
	const POSITION_VALIDATION 				= "/^(modal|cart|checkout|all)$/";
	const PRODUCT_AND_VARIATION_VALIDATION 	= "/^\d{1,}\-\d{1,}$/";

    public function sanitizeInput( $value, $request, $param )
	{
		$attributes = $request->get_attributes();
		
		if ( isset( $attributes['args'][ $param ] ) ) 
		{
			$argument = $attributes['args'][ $param ];
			
            if( !isset( $argument['regex'] ) ) return sanitize_text_field( $value );
			
            if ( preg_match( $argument['regex'] , $value ) ) return sanitize_text_field( $value );
			
            return new \WP_Error( 'rest_api_sad', esc_html__( 'Invalid input format.', 'woo_j_cart' ), array( 'status' => 500 ) );
			

		} else {
			
			return new \WP_Error( 'rest_invalid_param', sprintf( esc_html__( '%s was not registered as a request argument.', 'woo_j_cart' ), $param ), array( 'status' => 400 ) );
		}	 
		
		return new \WP_Error( 'rest_api_sad', esc_html__( 'Something went terribly wrong.', 'woo_j_cart' ), array( 'status' => 500 ) );
	}
}