<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_WC_Running_Invoice_Number_Backend_Output_Shop_Order' ) ) {

	/**
	* output on shop_order
	*
	* @class WP_WC_Running_Invoice_Number_Backend_Output_Shop_Order
	* @version 1.0
	* @category	Class
	*/
	class WP_WC_Running_Invoice_Number_Backend_Output_Shop_Order {
		
		/**
		* add column 'Invoice Number' to table at screen shop_order
		*
		* @since 0.0.1
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
				$columns = $columns + array( 'invoice_number' => __( 'Invoice Number', 'woocommerce-german-market' ) ) + $rest_columns;
			}

			return $columns;
		}
		
		/**
		* make the column 'Invoice Number' sortable at screen shop_order
		*
		* @since 0.0.1
		* @access public
		* @arguments Array $columns
		* @hook manage_edit-shop_order_sortable_columns
		* @return Array
		*/
		public static function shop_order_sortable_columns( $columns ) {
			$custom = array(
				'invoice_number' => 'running_invoice_number',
			);
			return wp_parse_args( $custom, $columns );
		}
		
		/**
		* render the content of column 'Invoice Number' at screen shop_order
		*
		* @since 0.0.1
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
			
			$invoice_number = $order->get_meta( '_wp_wc_running_invoice_number' );

			if ( strlen( $invoice_number ) > 500 ) { // issue #1732
				$order->delete_meta_data( '_wp_wc_running_invoice_number' );
				$order->delete_meta_data( '_wp_wc_running_invoice_number_date' );
				$order->save_meta_data();
			}
			
			switch ( $column ) {
				case 'invoice_number' :
					?><div id="invoice_number_<?php echo $order->get_id(); ?>"><?php
					if ( $invoice_number == '' && apply_filters( 'wp_wc_running_invoice_number_backend_output_shop_order_render_shop_order_columns', true, $order->get_id() ) ) {
						
						?><a class="wp_wc_invoice_generate_orders_page generate" data-order-id="<?php echo $order->get_id();?>"><?php echo __( 'Generate Invoice Number', 'woocommerce-german-market' ); ?></a><?php

					} else {
						echo $invoice_number;
						$invoice_date = $order->get_meta( '_wp_wc_running_invoice_number_date' );
						if ( $invoice_date != '' ) {
							?><br /><?php
							echo date_i18n( get_option( 'date_format' ), intval( $invoice_date ) );
						}
					}
					?></div><?php
					break;
			}
		}
		
		/**
		* how to sort column invoice_number
		*
		* @since 0.0.1
		* @access public
		* @arguments $query
		* @hook pre_get_posts
		* @return void
		*/
		public static function shop_order_sort( $query ) {
			$orderby = $query->get( 'orderby');
			if ( $orderby == 'running_invoice_number' ) {
				$query->set( 'meta_key', '_wp_wc_running_invoice_number' );

				$orderby = apply_filters( 'german_market_invoice_number_order_orderby', 'meta_value' );
				$orderby = in_array( $orderby, array( 'meta_value', 'meta_value_num' ), true ) ? $orderby : 'meta_value';
				$query->set( 'orderby', $orderby );
			}
		}


		/**
		* how to sort column invoice_number using hpos
		*
		* @since 3.24
		* @param Array $order_query_args
		* @hook woocommerce_order_list_table_prepare_items_query_args
		* @return Array
		*/
		public static function shop_order_sort_hpos( $order_query_args ) {

			$field     = sanitize_text_field( wp_unslash( $_GET[ 'orderby' ] ?? '' ) );
			$direction = strtoupper( sanitize_text_field( wp_unslash( $_GET[ 'order' ] ?? '' ) ) );

			if ( 'running_invoice_number' === $field ) {
				$order_query_args[ 'orderby' ] = 'meta_value';
				$order_query_args[ 'meta_key' ] = '_wp_wc_running_invoice_number';
				$order_query_args[ 'order' ]= in_array( $direction, array( 'ASC', 'DESC' ), true ) ? $direction : 'ASC';
			}

			return $order_query_args;
		}
		
		/**
		* show invoice number in "WooCommerce -> Orders" after invoice pdf donwnload has been clicked
		*
		* @since 0.0.1
		* @access public
		* @hook wp_ajax_wp_wc_running_invoice_number_show_after_invoice_pdf_clicked
		* @return void
		*/
		public static function shop_order_ajax_after_invoice_pdf_clicked() {

			if ( check_ajax_referer( 'wp_wc_running_invoice_number_nonce', 'security' ) ) {

				$order_id = '';

				// if clicked on download button from pdf invoice
				if ( isset( $_REQUEST[ 'href' ] ) ) {
					
					// get order_id from href. we build the href, so we can be sure that works
					$href = $_REQUEST[ 'href' ];
					$check_url = parse_url( $href );
					$check_url = explode( '&', $check_url[ 'query' ] );
					foreach ( $check_url as $query ) {
						$check = explode( '=', $query );
						if ( $check[ 0 ] == 'order_id' ) {
							$order_id = 	$check[ 1 ];
							break;
						}
					}
				}

				if ( $order_id !== '' ) {

					$order = wc_get_order( $order_id );

					$order_number 	= $order->get_meta( '_wp_wc_running_invoice_number' );
					$order_date 	= $order->get_meta( '_wp_wc_running_invoice_number_date' );

					if ( ! empty( $order_number ) ) {
						echo $order_id . '[[SEPARATOR]]' . $order_number;
					}

					if ( ! empty( $order_date ) ) {
						echo '<br />' . date_i18n( get_option( 'date_format' ), ( intval( $order_date ) ) );
					}

				}
			}

			exit();
		}

		/**
		* update invoice number in shop_order if there was no invoice number before
		*
		* @since 0.0.1
		* @access public
		* @hook wp_ajax_wp_wc_running_invoice_number_ajax_backend_shop_order
		* @return void
		*/
		public static function shop_order_ajax() {
						
			if ( check_ajax_referer( 'wp_wc_running_invoice_number_nonce', 'security' ) ) {

				$order_id 				= absint( $_REQUEST[ 'order_id' ] );
				$order 					= wc_get_order( $_REQUEST[ 'order_id' ] );
				$running_invoice_number = new WP_WC_Running_Invoice_Number_Functions( $order );	

				echo $running_invoice_number->get_invoice_number();
				
				?><br /><?php
				echo date_i18n( get_option( 'date_format' ), intval( $running_invoice_number->get_invoice_timestamp() ) );

			} else {
				echo 'ERROR';
			}
			
			exit();
		}
		
		/**
		* Include invoice number to search query
		*
		* @since 0.0.1
		* @access public
		* @hook woocommerce_shop_order_search_fields
		* @hook woocommerce_order_table_search_query_meta_keys
		* @return void
		*/
		public static function search_query( $meta_keys ) {
			$meta_keys[] = '_wp_wc_running_invoice_number';			
			return $meta_keys;
		}

		/**
		* add coloumn for WP_List refunds
		*
		* @since WGM 3.0
		* @access public
		* @hook wgm_refunds_backend_columns
		* @param Array $columns
		* @return Array
		*/
		public static function refund_columns( $columns ) {

			$new_columns = array();

			foreach ( $columns as $key => $value ) {

				$new_columns[ $key ] = $value;
				
				if ( $key == 'refund' ) {
					$new_columns[ 'refund_number' ] = __( 'Refund Number', 'woocommerce-german-market' );
				}

			}

			return $new_columns;

		}

		/**
		* add coloumn content for refund_number in WP_List refunds
		*
		* @since WGM 3.0
		* @access public
		* @hook wgm_refunds_array
		* @param Array $item
		* @return Array
		*/
		public static function refund_item( $item ) {
			
			$refund_id = str_replace( '#', '', $item[ 'refund' ] );

			if ( WGM_Hpos::is_hpos_enabled() ) {
				$refund = wc_get_order( $refund_id );
				$refund_number = $refund->get_meta( '_wp_wc_running_invoice_number' );
			} else {
				$refund_number = get_post_meta( $refund_id, '_wp_wc_running_invoice_number', true );
			}
			
			$item[ 'refund_number' ] = '<span id="refund-number-' . $refund_id . '">' . $refund_number . '</span><a href="#" class="edit-refund-number" id="edit-refund-number-a-' . $refund_id . '" data-refund-id="' . $refund_id . '"></a><input type="text" style="width: 100%; display:none;" data-refund-id="' . $refund_id . '" id="edit-refund-number-text-field-' . $refund_id . '" value="' . $refund_number . '"/><input type="button" id="edit-refund-number-button-' . $refund_id . '" class="button-secondary refund-number-save-button" style="display: none;" data-refund-id="' . $refund_id . '" value="' . __( 'Save', 'woocommerce-german-market' ) . '" />';

			return $item;

		}

		/**
		* Show refund number in refund list after pdf has been downloaded
		*
		* @since WGM 3.0
		* @access public
		* @hook wp_ajax_wp_wc_running_invoice_number_ajax_backend_post
		* @return void (exit)
		*/
		public static function ajax_show_refund_number() {

			if ( ! check_ajax_referer( 'wp_wc_running_invoice_number_nonce', 'security', false ) ) {
				wp_die( __( 'You have taken too long. Please go back and retry.', 'woocommerce-invoice-pdf' ), '', array( 'response' => 403 ) );
			}

			$refund_id = intval( $_REQUEST[ 'refund_id' ] );

			if ( WGM_Hpos::is_hpos_enabled() ) {
				$refund = wc_get_order( $refund_id );
				$refund_number = $refund->get_meta( '_wp_wc_running_invoice_number' );
			} else {
				$refund_number = get_post_meta( $refund_id, '_wp_wc_running_invoice_number', true );
			}

			echo $refund_number;
			exit();
		}

		/**
		* Update refund number in refund list after save button has been pressed
		*
		* @since WGM 3.3.1
		* @access public
		* @hook wp_ajax_wp_wc_running_invoice_number_update_refund_number
		* @return void (exit)
		*/
		public static function ajax_update_refund_number() {

			if ( ! check_ajax_referer( 'wp_wc_running_invoice_number_nonce', 'security', false ) ) {
				wp_die( __( 'You have taken too long. Please go back and retry.', 'woocommerce-invoice-pdf' ), '', array( 'response' => 403 ) );
			}

			$refund_id = $_REQUEST[ 'refund_id' ];
			$refund_number = $_REQUEST[ 'new_refund_number' ];
			$refund = wc_get_order( $refund_id );

			$logging = apply_filters( 'german_market_invoice_number_logging', false );
			if ( $logging ) {
				$current_number = $refund->get_meta( '_wp_wc_running_invoice_number' ); 
				if ( $current_number != $refund_number ) {
					$new_log = sprintf( 'Refund: %s, Manual Saved Invoice Number Meta from %s to %s.', $refund_id, $current_number, $refund_number );
					$logger = wc_get_logger();
					$context = array( 'source' => 'german-market-invoice-number' );
					$logger->info( $new_log, $context );
				}
			}
			
			$refund->update_meta_data( '_wp_wc_running_invoice_number', $refund_number );
			$refund->save_meta_data();

			exit();
		}

		/**
		 * Add "Invoice Number" to "HPOS Full Text Search"
		 * Does not work if "all" is selected, yet
		 * 
		 * @since 3.39
		 * @wp-hook woocommerce_hpos_admin_search_filters
		 * @param Array $filters
		 * @return Array
		 */
		public static function add_hpos_full_text_search_option( $filters ) {
			
			$invoice_number_filter = array(
				'german-market-invoice-number' => __( 'Invoice Number', 'woocommerce-german-market' )
			);

			return $invoice_number_filter + $filters;
		}

		/**
		 * Add "Invoice Number" filter to "HPOS Full Text Search"
		 * 
		 * @since 3.39
		 * @wp-hook woocommerce_hpos_generate_where_for_search_filter
		 * @param String $where
		 * @param String $search_term
		 * @param String $filter
		 * @param WP_Query $query
		 * @return String
		 */
		public static function add_hpos_full_text_search_filter( $where, $search_term, $filter, $query ) {

			if ( 'german-market-invoice-number' === $filter ) {
				$order_table = $query->get_table_name( 'orders' );
				$meta_table = $query->get_table_name( 'meta' );
				$where .= 
					"$order_table.id in 
						(
							SELECT $meta_table.order_id 
							FROM $meta_table 
							WHERE $meta_table.meta_key = '_wp_wc_running_invoice_number' AND 
							$meta_table.meta_value LIKE '%" . esc_sql( $search_term ) . "%'
						)
					";
			}

			return $where;
		}
		
	} // end class
	
} // end if class exists
