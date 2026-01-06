<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

class German_Market_Lexoffice_Backend_Invoice_Number_Column {

	/**
	* Add column 'lexoffice Invoice Number' to table at screen shop_order
	*
	* @access public
	* @arguments Array $columns
	* @hook manage_edit-shop_order_columns
	* @return Array
	*/
	public static function shop_order_columns( $columns ) {

		if ( array_key_exists( 'order_number', $columns ) ) {
			// find 'order_title'
			$new_coloumns = array();
			$i = 0;
			foreach ( $columns as $column_key => $column_value ) {
				$i++;
				if ( $column_key == 'order_number' ) {
					break;	
				}
			}
			$rest_columns = array_splice( $columns, $i ) ;
			$columns = $columns + array( 'lexoffice_invoice_number' => __( 'Lexware office Invoice Number', 'woocommerce-german-market' ) ) + $rest_columns;
		}

		return $columns;
	}
	
	/**
	* Make the column 'lexoffice Invoice Number' sortable at screen shop_order
	*
	* @access public
	* @arguments Array $columns
	* @hook manage_edit-shop_order_sortable_columns
	* @return Array
	*/
	public static function shop_order_sortable_columns( $columns ) {
		$custom = array(
			'lexoffice_invoice_number' => 'lexoffice_invoice_number',
		);
		return wp_parse_args( $custom, $columns );
	}
	
	/**
	* Render the content of column 'lexoffice Invoice Number' at screen shop_order
	*
	* @access public
	* @arguments Array $columns
	* @hook manage_shop_order_posts_custom_column
	* @return Array
	*/
	public static function render_shop_order_columns( $column, $post_id_or_order_object ) {
		
		if ( ! ( is_object( $post_id_or_order_object ) && method_exists( $post_id_or_order_object, 'get_meta' ) ) ) {
			$order = wc_get_order( $post_id_or_order_object );
		} else {
			$order = $post_id_or_order_object;
		}
		
		switch ( $column ) {
			case 'lexoffice_invoice_number' :
				?><div id="lexoffice-invoice-number-<?php echo esc_attr( $order->get_id() ); ?>"><?php echo wp_kses_post( self::get_column_markup( $order ) ); ?></div><?php
				break;
		}
	}

	/**
	* Get markup to render invoice number in column
	*
	* @access public
	* @param Integer $order_id
	* @hook manage_shop_order_posts_custom_column
	* @return Array
	*/
	public static function get_column_markup( $order ) {

		$string 					= '';
		$lexoffice_invoice_number 	= $order->get_meta( '_lexoffice_invoice_number', true );
		$lexoffice_invoice_id 		= $order->get_meta( '_lexoffice_woocomerce_has_transmission_invoice_api' );
		$show_link 					= apply_filters( 'woocommerce_de_lexoffice_show_deeplink_in_shop_order_column', true );

		if ( ! empty( $lexoffice_invoice_number ) ) {
			if ( $show_link && ( ! empty( $lexoffice_invoice_id ) ) ) {
				$string = sprintf( '<a href="%s" target="_blank">%s</a>', German_Market_Lexoffice_API_Auth::get_app_base_url() . 'vouchers#!/VoucherView/Invoice/' . $lexoffice_invoice_id, $lexoffice_invoice_number );
			} else {
				$string = $lexoffice_invoice_number;
			}
		}
		return $string;
	}
	
	/**
	* How to sort column lexoffice invoice_number
	*
	* @access public
	* @arguments $query
	* @hook pre_get_posts
	* @return void
	*/
	public static function shop_order_sort( $query ) {
		$orderby = $query->get( 'orderby' );
		if ( $orderby == 'lexoffice_invoice_number' ) {
			$query->set( 'meta_key', '_lexoffice_invoice_number' );
			$query->set( 'orderby', 'meta_value' );
		}
	}

	/**
	* How to sort column invoice_number using hpos
	*
	* @param Array $order_query_args
	* @hook woocommerce_order_list_table_prepare_items_query_args
	* @return Array
	*/
	public static function shop_order_sort_hpos( $order_query_args ) {

		$field     = sanitize_text_field( wp_unslash( $_GET[ 'orderby' ] ?? '' ) );
		$direction = strtoupper( sanitize_text_field( wp_unslash( $_GET[ 'order' ] ?? '' ) ) );

		if ( 'lexoffice_invoice_number' === $field ) {
			$order_query_args[ 'orderby' ] = 'meta_value';
			$order_query_args[ 'meta_key' ] = '_lexoffice_invoice_number';
			$order_query_args[ 'order' ]= in_array( $direction, array( 'ASC', 'DESC' ), true ) ? $direction : 'ASC';
		}

		return $order_query_args;
	}
	
	/**
	* include invoice number to search query
	*
	* @access public
	* @hook woocommerce_shop_order_search_fields
	* @return void
	*/
	public static function search_query( $fields ) {
		$fields[] = '_lexoffice_invoice_number';			
		return $fields;
	}

	/**
	* Add coloumn for WP_List refunds
	*
	* @hook wgm_refunds_backend_columns
	* @param Array $columns
	* @return Array
	*/
	public static function refund_columns( $columns ) {

		$new_columns = array();

		foreach ( $columns as $key => $value ) {

			$new_columns[ $key ] = $value;
			
			if ( $key == 'refund' ) {
				$new_columns[ 'lexoffice_correction_number' ] = __( 'Lexware Office invoice correction', 'woocommerce-german-market' );
			}

		}

		return $new_columns;
	}

	/**
	* Add coloumn content for lexoffice_correction_number in WP_List refunds
	*
	* @hook wgm_refunds_array
	* @param Array $item
	* @return Array
	*/
	public static function refund_item( $item ) {
		
		$refund_id = str_replace( '#', '', $item[ 'refund' ] );
		$refund = wc_get_order( $refund_id );
		
		$string 					= '';
		$lexoffice_invoice_number 	= $refund->get_meta( '_lexoffice_invoice_number', true );
		$lexoffice_invoice_id 		= $refund->get_meta( '_lexoffice_woocomerce_has_transmission_invoice_api' );
		$show_link 					= apply_filters( 'woocommerce_de_lexoffice_show_deeplink_in_shop_order_column', true );

		if ( ! empty( $lexoffice_invoice_number ) ) {
			if ( $show_link && ( ! empty( $lexoffice_invoice_id ) ) ) {
				$string = sprintf( '<a href="%s" target="_blank">%s</a>', German_Market_Lexoffice_API_Auth::get_app_base_url() . 'vouchers#!/VoucherView/Invoice/' . $lexoffice_invoice_id, $lexoffice_invoice_number );
			} else {
				$string = $lexoffice_invoice_number;
			}
		}

		$item[ 'lexoffice_correction_number' ] = '<div id="lexoffice-invoice-number-' . $refund->get_id() . '">' . $string . '</div>';

		return $item;

	}
}
