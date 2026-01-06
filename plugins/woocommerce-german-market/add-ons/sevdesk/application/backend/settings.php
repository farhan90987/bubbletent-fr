<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

/**
* Backend Settings German Market 3.1
*
* wp-hook woocommerce_de_ui_left_menu_items
* @param Array $items
* @return Array
*/
function sevdesk_woocommerce_de_ui_left_menu_items( $items ) {

	$items[ 320 ] = array( 
				'title'		=> __( 'sevdesk', 'woocommerce-german-market' ),
				'slug'		=> 'sevdesk',
				'callback'	=>'sevdesk_woocommerce_de_ui_render_options',
				'options'	=> 'yes'
		);

	return $items;
}

/**
* Render Options for global
* 
* @return void
*/
function sevdesk_woocommerce_de_ui_render_options() {

	if ( isset ( $_REQUEST[ 'woocommerce_de_sevdesk_api_token' ] ) ) {
		wp_safe_redirect( get_admin_url() . 'admin.php?page=german-market&tab=sevdesk' );
	}

	$description = '';

	if ( ! function_exists( 'curl_init' ) ) {

		$description = '<span style="color: #f00;">' . __( 'The PHP cURL library seems not to be present on your server. Please contact your admin / webhoster.', 'woocommerce-german-market' ) . ' ' . __( 'The sevdesk Add-On will not work without the cURL library.', 'woocommerce-german-market' ) . '</span><br /><br />';

	}

	$description .= __( 'Please enter your API token in the field above. To retrieve your API token, log in to your <a href="https://my.sevdesk.de" target="_blank">sevdesk</a> account and go to <em>settings -> user</em>. Select your user account, under "edit user" you will find your API token at the bottom of the page.', 'woocommerce-german-market' ) . '<br /><br />' . sprintf ( __( "You can register <a href=\"%s\" target=\"_blank\">here</a> if you don't have a sevdesk account, yet.", 'woocommerce-german-market' ), 'https://sevdesk.de/register/?utm_source=integrations&utm_medium=referral&utm_campaign=marketpress' );
	
	$settings[] = array(
		'name' => __( 'Authorization', 'woocommerce-german-market' ),
		'type' => 'title',
		'id'   => 'sevdesk',
		'desc' => $description
	);

	$api_version = '';

	if ( get_option( 'woocommerce_de_sevdesk_api_token' ) != '' ) {
		$api_version = 'API Version: ' . German_Market_SevDesk_API_V2::get_bookkeeping_system_version();
	}

	$settings[] = array(
		'name'              => __( 'API Token', 'woocommerce-german-market' ),	
		'id'                => 'woocommerce_de_sevdesk_api_token',
		'type'              => 'text',
		'css'				=> 'min-width: 300px; max-width: 100%;',
		'desc'				=> $api_version,
	);

	$settings[] = array( 'type' => 'sectionend', 'id' => 'sevdesk' );

	$settings[] = array(
		'name' => __( 'Settings', 'woocommerce-german-market' ),
		'type' => 'title',
		'id'   => 'sevdesk_settings',
	);

	$description = __( 'Activate this option to send data of your WooCommerce customers to sevdesk to be used there as contacts (customers).', 'woocommerce-german-market' );

	$settings[] = array(
		'name'				=> __( 'Send Customer Data', 'woocommerce-german-market' ),
		'desc_tip'			=> $description,
		'id'				=> 'woocommerce_de_sevdesk_send_customer_data',
		'type'     			=> 'wgm_ui_checkbox',
		'default'			=> 'off',
	);

	if ( get_option( 'woocommerce_de_sevdesk_send_customer_data', 'off' ) == 'on' ) {

		$description = __( 'Prefix for your sevdesk customer numbers for persons, followed by the wordpress user id.', 'woocommerce-german-market' );

		$settings[] = array(
			'name'				=> __( 'Prefix - Person Customer Number', 'woocommerce-german-market' ),
			'desc_tip'			=> $description,
			'id'				=> 'woocommerce_de_sevdesk_customer_number_prefix',
			'type'              => 'text',
			'default'			=> '',
		);

		$description = __( 'If you activate this option, a company will be added as a contact to sevdesk if the user has an billing company in the user profile.', 'woocommerce-german-market' );

		$settings[] = array(
			'name'				=> __( 'Create Companies', 'woocommerce-german-market' ),
			'desc_tip'			=> $description,
			'id'				=> 'woocommerce_de_sevdesk_customer_add_company',
			'type'              => 'wgm_ui_checkbox',
		);

		$description = __( 'Prefix for your sevdesk customer numbers for companies, followed by the wordpress user id (which is the same as for the person). Only usesd if you activate the setting "Create Companies"', 'woocommerce-german-market' );

		$settings[] = array(
			'name'				=> __( 'Prefix - Company Customer Number', 'woocommerce-german-market' ),
			'desc_tip'			=> $description,
			'id'				=> 'woocommerce_de_sevdesk_customer_company_number_prefix',
			'type'              => 'text',
			'default'			=> '',
		);

		$settings[] = array(
			'name'		=> __( 'Guest Users', 'woocommerce-german-market' ),
			'id'				=> 'woocommerce_de_sevdesk_guest_users',
			'type'              => 'select',
			'options' 			=> array(
									'no'	=> __( 'Do not create a customer in sevdesk', 'woocommerce-german-market' ),
									'yes'	=> __( 'Create customer in sevdesk', 'woocommerce-german-market' ),
			),
			'default'			=> 'no',
			'css'				=> 'width: 400px;'
		);

		$description = __( 'The prefix for guest users is followed by the prefix for customer numbers or company number. After the guest prefix the order number is followed.', 'woocommerce-german-market' );

		$settings[] = array(
			'name'				=> __( 'Prefix - Guest Users', 'woocommerce-german-market' ),
			'desc_tip'			=> $description,
			'id'				=> 'woocommerce_de_sevdesk_customer_guest_prefix',
			'type'              => 'text',
			'default'			=> __( 'Guest-', 'woocommerce-german-market' ),
		);

	}

	if ( get_option( 'woocommerce_de_sevdesk_api_token' ) != '' ) {

		if ( function_exists( 'curl_init' ) ) {
			
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, sevdesk_woocommerce_api_get_base_url() . 'CheckAccount/?register=0' );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Authorization:' . get_option( 'woocommerce_de_sevdesk_api_token' ) ,'Content-Type:application/x-www-form-urlencoded' ) );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			$response = curl_exec( $ch );
			$result_array = json_decode( $response, true );
			curl_close( $ch );

			$check_accounts = array();
			
			if ( isset ( $result_array[ 'objects' ] ) ) {

				foreach ( $result_array[ 'objects' ] as $check_account ) {
					$check_accounts[ $check_account [ 'id' ] ] = $check_account[ 'name' ];
				}

				$description = __( 'Choose your check account to book your vouchers.', 'woocommerce-german-market' ) . ' ' . __( 'When using a check account that is not of type "offline", vouchers can not be marked as "paid" by this API. In that case vouchers will be automatically be marked as paid by sevdesk.', 'woocommerce-german-market' );

				$settings[] = array(
					'name'				=> __( 'Check Account', 'woocommerce-german-market' ),
					'desc_tip'			=> $description,
					'id'				=> 'woocommerce_de_sevdesk_check_account',
					'type'				=> 'select',
					'options'			=> $check_accounts

				);

				$settings[] = array(
					'name'				=> __( 'Individual Check Accounts for Payment Gateways', 'woocommerce-german-market' ),
					'type'				=> 'wgm_ui_checkbox',
					'id'				=> 'woocommerce_de_sevdesk_individual_gateway_check_accounts',
					'default'			=> 'off',
					'desc_tip'			=> __( 'If activated, you can set up an individual check account in each payment gateway in submenu "WooCommerce -> German Market -> General -> Payment settings". If no individual check account is selected for a payment gateway, the default check account will be used.', 'woocommerce-german-market' )

				);

				$settings[] = array(
					'name'				=> __( 'Synchronization of Voucher Status on Backend Order Page', 'woocommerce-german-market' ),
					'desc_tip'			=> __( 'If there are long loading times on the order overview page in the backend (WooCommerce -> Orders), you can deactivate this option. Each call checks whether the voucher still exists in the sevdesk account. If an order has to be sent again to sevdesk after the corresponding voucher has been deleted at sevdesk, this option must be activated to allow a resending.', 'woocommerce-german-market' ),
					'id'				=> 'woocommerce_de_sevdesk_backend_sync',
					'type'              => 'wgm_ui_checkbox',
					'default'			=> 'on'

				);

			}

		}

	}

	$settings[] = array(
		'name'				=> __( 'Payment Status', 'woocommerce-german-market' ),
		'id'				=> 'woocommerce_de_sevdesk_payment_status',
		'type'              => 'select',
		'default'			=> 'completed',
		'options'			=> array(
				'completed' => __( 'Mark sevdesk voucher as paid if WooCommerce order is paid (order status is completed or processing)', 'woocommerce-german-market' ),
				'never'		=> __( 'Never mark sevdesk vouchers as paid', 'woocommerce-german-market' )
			),
		'css'				=> 'width: 600px;',
		'desc_tip'			=> __( 'When using a check account that is not of type "offline", vouchers can not be marked as "paid" by this API. In that case vouchers will be automatically be marked as paid by sevdesk.', 'woocommerce-german-market' ),

	);

	$settings[] = array( 'type' => 'sectionend', 'id' => 'sevdesk_settings' );

	if ( get_option( 'woocommerce_de_sevdesk_api_token' ) != '' ) {

		if ( function_exists( 'curl_init' ) ) {

			$settings[] = array(
				'name' => __( 'Booking Accounts', 'woocommerce-german-market' ),
				'type' => 'title',
				'id'   => 'sevdesk_booking',
			);

			$booking_accounts = array();

			if ( '1.0' === German_Market_SevDesk_API_V2::get_bookkeeping_system_version() ) {
				
				$option_suffix = '';
				$filter_suffix = '';

				$default_order = 26;
				$default_refund = 27;

				$legacy_booking_accounts = German_Market_SevDesk_API_V2::get_legacy_booking_accounts();
				
				foreach ( $legacy_booking_accounts as $booking_account ) {
					
					$booking_account_value = '';
					
					if ( isset( $booking_account[ 'accountingSystemNumber' ][ 'number' ] ) ) {
						$booking_account_value .= $booking_account[ 'accountingSystemNumber' ][ 'number' ] . ' ';
					}

					$booking_account_value .= $booking_account[ 'name' ];
					
					$booking_accounts[ $booking_account[ 'id' ] ] = $booking_account_value;
				}

			} else {

				German_Market_SevDesk_API_V2::map_booking_account_options();

				$option_suffix = '_v2';
				$filter_suffix = '_v2';

				$default_order = 3631; // German_Market_SevDesk_API_V2::get_datev_id_from_legacy_booking_account_id( 26 )
				$default_refund = 3712; // German_Market_SevDesk_API_V2::get_datev_id_from_legacy_booking_account_id( 27 )

				$datev_accounts = German_Market_SevDesk_API_V2::get_datev_accounts();
				$now = intval( current_time( 'timestamp' ) );

				foreach ( $datev_accounts as $key => $booking_account ) {

					if ( isset( $booking_account[ 'validFrom' ] ) ) {
						if ( $now < intval( $booking_account[ 'validFrom' ] ) ) {
							continue;
						}
					}

					if ( isset( $booking_account[ 'validUntil' ] ) ) {
						if ( $now > intval( $booking_account[ 'validUntil' ] ) ) {
							continue;
						}
					}

					$new_key = isset( $booking_account[ 'accountDatevId' ] ) ? $booking_account[ 'accountDatevId' ] : $key;
					$name = '';
					if ( isset( $booking_account[ 'number' ] ) ) {
						$name = $booking_account[ 'number' ] . ' ';
					}

					if ( isset( $booking_account[ 'name' ] ) ) {
						$name .= $booking_account[ 'name' ];
					}

					if ( empty( $name ) ) {
						$name = $key;
					}

					$booking_accounts[ $new_key ] = $name;
				}
			}

			if ( has_filter( 'woocommerce_de_sevdesk_booking_accounts' . $filter_suffix ) ) {
				
				$settings = apply_filters( 'woocommerce_de_sevdesk_booking_accounts' . $filter_suffix, $settings, $booking_accounts );
			
			} else {
				
				$settings[] = array(
						'name'				=> __( 'Booking Account for Order Items', 'woocommerce-german-market' ),
						'id'				=> 'woocommerce_de_sevdesk_booking_account_order_items' . $option_suffix,
						'type'				=> 'select',
						'options'			=> $booking_accounts,
						'default'			=> $default_order,
						'class'				=> 'sevdesk_select_booking_account'
				);

				$settings[] = array(
						'name'				=> __( 'Booking Account for Order Shipping', 'woocommerce-german-market' ),
						'id'				=> 'woocommerce_de_sevdesk_booking_account_order_shipping' . $option_suffix,
						'type'				=> 'select',
						'options'			=> $booking_accounts,
						'default'			=> $default_order,
						'class'				=> 'sevdesk_select_booking_account'
				);

				$settings[] = array(
						'name'				=> __( 'Booking Account for Order Fees', 'woocommerce-german-market' ),
						'id'				=> 'woocommerce_de_sevdesk_booking_account_order_fees' . $option_suffix,
						'type'				=> 'select',
						'options'			=> $booking_accounts,
						'default'			=> $default_order,
						'class'				=> 'sevdesk_select_booking_account'
				);

				$settings[] = array(
						'name'				=> __( 'Booking Account for Refund Positions', 'woocommerce-german-market' ),
						'id'				=> 'woocommerce_de_sevdesk_booking_account_refunds' . $option_suffix,
						'type'				=> 'select',
						'options'			=> $booking_accounts,
						'default'			=> $default_refund,
						'class'				=> 'sevdesk_select_booking_account'
				);

				$settings[] = array(
						'name'				=> __( 'Individual booking accounts for products', 'woocommerce-german-market' ),
						'id'				=> 'woocommerce_de_sevdesk_individual_product_booking_accounts',
						'type'				=> 'wgm_ui_checkbox',
						'default'			=> 'off',
						'desc_tip'			=> __( 'If activated, you can set up individual booking accounts in every product. If no individual booking account is selected for a product, the default booking account will be used (booking account for order items or for refund positions).', 'woocommerce-german-market' )
				);
			
			}

			$settings 	= apply_filters( 'woocommerce_de_sevdesk_additional_booking_accounts' . $filter_suffix, $settings, $booking_accounts );
			$settings[] = array( 'type' => 'sectionend', 'id' => 'sevdesk_booking' );

		}

	}

	$settings[] = array( 
			'name'		 => __( 'Automatic Transmission', 'woocommerce-german-market' ),
			'type'		 => 'title',
			'id'  		 => 'sevdesk_automatic_transmission',
		);

	$settings[] = array(
			'name'		=> __( 'Completed Order', 'woocommerce-german-market' ),
			'id'		=> 'woocommerce_de_sevdesk_automatic_completed_order',
			'desc_tip'	=> __( 'If activated, the voucher will be send automatically to sevdesk if the order is marked as completed.', 'woocommerce-german-market' ),
			'type'     	=> 'wgm_ui_checkbox',
			'default'  	=> 'off',
		);

	$settings[] = array(
			'name'		=> __( 'Refunds', 'woocommerce-german-market' ),
			'id'		=> 'woocommerce_de_sevdesk_automatic_refund',
			'desc_tip'	=> __( 'If activated, the voucher will be send automatically to sevdesk if an refund is created.', 'woocommerce-german-market' ),
			'type'     	=> 'wgm_ui_checkbox',
			'default'  	=> 'off',
		);

	$settings[] = array(
			'type'		=> 'sectionend',
			'id' 		=> 'sevdesk_automatic_transmission' 
		);

	$settings[] = array(
			'name'		 => __( 'Voucher Number', 'woocommerce-german-market' ),
			'type'		 => 'title',
			'id'  		 => 'secdesk_voucher_number',
		);

	$settings[] = apply_filters( 'sevdesk_woocommerce_de_ui_render_option_sevdesk_voucher_description_order', array(
			'name'		 => __( 'Voucher Number for orders', 'woocommerce-german-market' ),
			'type'		 => 'text',
			'id'  		 => 'sevdesk_voucher_description_order',
			'default'	 => sevdesk_woocommerce_get_default_value( 'sevdesk_voucher_description_order' ),
			'desc'		 => __( 'You can use the following placeholder', 'woocommerce-german-market' ) . ': ' . __( 'Order Number - <code>{{order-number}}</code>', 'woocommerce-german-market' ),
		) );

	$settings[] = apply_filters( 'sevdesk_woocommerce_de_ui_render_option_sevdesk_voucher_description_refund', array(
			'name'		 => __( 'Voucher Number for refunds', 'woocommerce-german-market' ),
			'type'		 => 'text',
			'id'  		 => 'sevdesk_voucher_description_refund',
			'default'	 => sevdesk_woocommerce_get_default_value( 'sevdesk_voucher_description_refund' ),
			'desc'		 => __( 'You can use the following placeholders:', 'woocommerce-german-market' ) . ' ' . __( 'Refund ID - <code>{{refund-id}}</code>, Order Number - <code>{{order-number}}</code>', 'woocommerce-german-market' ),
		) );

	$settings = apply_filters( 'sevdesk_woocommerce_de_ui_settings_after_voucher_number', $settings );

	$settings[] = array(
			'type'		=> 'sectionend',
			'id' 		=> 'secdesk_voucher_number' 
		);

	$settings = apply_filters( 'sevdesk_woocommerce_de_ui_render_options', $settings );
	return( $settings );

}

/**
* Individual Accounts: Save Meta Data
*
* @since 3.8.2 
* @wp-hook woocommerce_process_product_meta
* @param Integer $post_id
* @param WP_Post $post
* @return void 
**/
function sevdesk_woocommerce_accounts_save_meta( $post_id, $post = NULL ) {

	if ( isset( $_POST[ '_sevdesk_field_order_account' ] ) ) {
		update_post_meta( $post_id, '_sevdesk_field_order_account', $_POST[ '_sevdesk_field_order_account' ] );
	}

	if ( isset( $_POST[ '_sevdesk_field_refund_account' ] ) ) {
		update_post_meta( $post_id, '_sevdesk_field_refund_account', $_POST[ '_sevdesk_field_refund_account' ] );
	}

	if ( isset( $_POST[ '_sevdesk_field_order_account_v2' ] ) ) {
		update_post_meta( $post_id, '_sevdesk_field_order_account_v2', $_POST[ '_sevdesk_field_order_account_v2' ] );
	}

	if ( isset( $_POST[ '_sevdesk_field_refund_account_v2' ] ) ) {
		update_post_meta( $post_id, '_sevdesk_field_refund_account_v2', $_POST[ '_sevdesk_field_refund_account_v2' ] );
	}

}

/**
* Individual Accounts: Add Product Tab
*
* @since 3.8.2 
* @wp-hook woocommerce_product_data_tabs
* @param Array $tabs
* @return Array 
**/
function sevdesk_woocommerce_accounts_product_tab( $tabs ) {

	$tabs[ 'german_market_sevdesk' ] = array(
			'label'  => 'sevdesk',
			'target' => 'sevdesk_accounts_product_panel_setting',
	);

	return $tabs;
}

/**
* Individual Accounts: Render Product Tab
*
* @since 3.8.2 
* @wp-hook woocommerce_product_data_panels
* @return void 
**/
function sevdesk_woocommerce_accounts_product_panel(){
	$product = wc_get_product( get_the_ID() );
	?>
	<div id="sevdesk_accounts_product_panel_setting" class="panel woocommerce_options_panel sevdesk" style="display: block; ">

		<?php
		$booking_accounts = array();		
		if ( get_option( 'woocommerce_de_sevdesk_api_token' ) != '' ) {

			if ( function_exists( 'curl_init' ) ) {

				$settings[] = array(
					'name' => __( 'Booking Accounts', 'woocommerce-german-market' ),
					'type' => 'title',
					'id'   => 'sevdesk_booking',
				);

				if ( '1.0' === German_Market_SevDesk_API_V2::get_bookkeeping_system_version() ) {

					$legacy_booking_accounts = German_Market_SevDesk_API_V2::get_legacy_booking_accounts();
					foreach ( $legacy_booking_accounts as $booking_account ) {
						$number = isset( $booking_account[ 'accountingSystemNumber' ][ 'number' ] ) ? $booking_account[ 'accountingSystemNumber' ][ 'number' ] : '';
						$booking_accounts[ $booking_account [ 'id' ] ] = trim( $number . ' ' . $booking_account[ 'name' ] );
					}

					$setting = get_post_meta( get_the_ID(), '_sevdesk_field_order_account', true );
					$setting_refund = get_post_meta( get_the_ID(), '_sevdesk_field_refund_account', true );
					$suffix = '';

				} else {

					$datev_accounts = German_Market_SevDesk_API_V2::get_datev_accounts();
					$now = intval( current_time( 'timestamp' ) );
					
					foreach ( $datev_accounts as $key => $booking_account ) {

						if ( isset( $booking_account[ 'validFrom' ] ) ) {
							if ( $now < intval( $booking_account[ 'validFrom' ] ) ) {
								continue;
							}
						}

						if ( isset( $booking_account[ 'validUntil' ] ) ) {
							if ( $now > intval( $booking_account[ 'validUntil' ] ) ) {
								continue;
							}
						}

						$new_key = isset( $booking_account[ 'accountDatevId' ] ) ? $booking_account[ 'accountDatevId' ] : $key;
						$name = '';
						if ( isset( $booking_account[ 'number' ] ) ) {
							$name = $booking_account[ 'number' ] . ' ';
						}

						if ( isset( $booking_account[ 'name' ] ) ) {
							$name .= $booking_account[ 'name' ];
						}

						if ( empty( $name ) ) {
							$name = $key;
						}

						$booking_accounts[ $new_key ] = $name;
					}

					$setting = German_Market_SevDesk_API_V2::get_product_datev_booking_account( $product );
					$setting_refund = German_Market_SevDesk_API_V2::get_product_datev_booking_account( $product, 'refund' );
					$suffix = '_v2';

				}
			}
		}

		?>

		<p class="form-field _sevdesk_fields">
			<label for="_sevdesk_field_order_account<?php echo $suffix; ?>" style="width: 300px;"><?php echo __( 'Booking account in orders:', 'woocommerce-german-market' ); ?></label>
			
			<select name="_sevdesk_field_order_account<?php echo $suffix; ?>" id="_sevdesk_field_order_account<?php echo $suffix; ?>" class="sevdesk_select_booking_account" style="width: 50%;">

				<option value="-1"><?php echo __( 'Default Booking Acount', 'woocommerce-german-market' ); ?></option>

				<?php foreach ( $booking_accounts as $key => $value ) { ?>

					<option value="<?php echo $key;?>" <?php echo ( intval( $setting ) == intval( $key ) ) ? 'selected="selected"' : ''; ?>><?php echo $value; ?></option>

				<?php } ?>
			
			</select>
		</p>

		<p class="form-field _sevdesk_fields">
			
			<label for="_sevdesk_field_refund_account<?php echo $suffix; ?>" style="width: 300px;"><?php echo __( 'Booking account in refunds:', 'woocommerce-german-market' ); ?></label>

			<select name="_sevdesk_field_refund_account<?php echo $suffix; ?>" id="_sevdesk_field_refund_account<?php echo $suffix; ?>" class="sevdesk_select_booking_account" style="width: 50%;">

				<option value="-1"><?php echo __( 'Default Booking Acount', 'woocommerce-german-market' ); ?></option>
				<?php foreach ( $booking_accounts as $key => $value ) { ?>

					<option value="<?php echo $key;?>" <?php echo ( intval( $setting_refund ) == intval( $key ) ) ? 'selected="selected"' : ''; ?>><?php echo $value; ?></option>

				<?php } ?>
			
			</select>

		</p>

		<?php do_action( 'woocommerce_de_sevdesk_after_product_panel', $product ); ?>

	</div>
	<?php


}

/**
* Field in Payment Gateways
*
* @since 3.8.2
* @param Array $settings
* @return Array
*/
function woocommerce_de_sevdesk_gateway_check_accounts_field( $settings, $gateway_id ) {

	$check_accounts = array();

	if ( isset( WGM_Helper::$run_time_cache[ 'sevdesk_check_account_options' ] ) ) {
		$result_array = WGM_Helper::$run_time_cache[ 'sevdesk_check_account_options' ];
	} else {

		if ( isset( $_REQUEST[ 'page' ] ) && 'german-market' === $_REQUEST[ 'page' ] && isset( $_REQUEST[ 'sub_tab' ] ) && 'payment_settings' === $_REQUEST[ 'sub_tab' ] ) {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, sevdesk_woocommerce_api_get_base_url() . 'CheckAccount/?register=0' );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Authorization:' . get_option( 'woocommerce_de_sevdesk_api_token' ) ,'Content-Type:application/x-www-form-urlencoded' ) );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			$response = curl_exec( $ch );
			$result_array = json_decode( $response, true );
			curl_close( $ch );
			WGM_Helper::$run_time_cache[ 'sevdesk_check_account_options' ] = $result_array;
		}
	}
	
	if ( isset ( $result_array[ 'objects' ] ) ) {

		$check_accounts[ 'default' ] = __( 'Default Check Account', 'woocommerce-german-market' );

		foreach ( $result_array[ 'objects' ] as $check_account ) {
			$check_accounts[ $check_account [ 'id' ] ] = $check_account[ 'name' ];
		}
	}

	$settings[ 'sevdesk_check_account' ] = array(
		'title'				=> __( 'sevdesk', 'woocommerce-german-market' ) .': ' . __( 'Check Account', 'woocommerce-german-market' ),
		'id'				=> 'woocommerce_de_sevdesk_check_account',
		'type'				=> 'select',
		'options'			=> $check_accounts

	);

	return $settings;

}
