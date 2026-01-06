<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class German_Market_SevDesk_API_V2
 *
 * @author MarketPress
 */
class German_Market_SevDesk_API_V2 {

	/**
	 * Get bookkkeeping system version
	 * 
	 * @since 3.32
	 * @return String
	 */
	public static function get_bookkeeping_system_version() {

		$bookkeeping_system_version = get_transient( 'woocommerce_sevdesk_bookkeeping_version' );

		if ( false === $bookkeeping_system_version ) {
			
			$version = '1.0';

			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, sevdesk_woocommerce_api_get_base_url() . 'Tools/bookkeepingSystemVersion' );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Authorization:' . get_option( 'woocommerce_de_sevdesk_api_token' ) ,'Content-Type:application/x-www-form-urlencoded' ) );
			curl_setopt( $ch, CURLOPT_USERAGENT, sevdesk_woocommerce_get_user_agent() );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			$response = curl_exec( $ch );
			$result_array = json_decode( $response, true );
			curl_close( $ch );

			if ( isset( $result_array[ 'objects' ] ) ) {
				if ( isset( $result_array[ 'objects' ][ 'version' ] ) ) {
					$version = $result_array[ 'objects' ][ 'version' ];
					set_transient( 'woocommerce_sevdesk_bookkeeping_version', $version, 5 );
				}
			}

		} else {
			$version = $bookkeeping_system_version;
		}

		if ( '2.0' === $version ) {
			$has_mapped_booking_account_options = apply_filters( 'woocommerce_sevdesk_has_mapped_booking_account_options', get_transient( 'woocommerce_sevdesk_has_mapped_booking_account_options' ) );
			if ( false === $has_mapped_booking_account_options ) {
				German_Market_SevDesk_API_V2::map_booking_account_options();
				set_transient( 'woocommerce_sevdesk_has_mapped_booking_account_options', true );
			}
		}

		return $version;
	}

	/**
	 * Get datev accounts
	 * 
	 * @since 3.32
	 * @return Array
	 */
	public static function get_datev_accounts() {
		$datev_accounts = get_transient( 'woocommerce_sevdesk_datev_accounts' );

		if ( ! is_array( $datev_accounts ) ) {

			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, sevdesk_woocommerce_api_get_base_url() . 'AccountDatev/Factory/getAccounts' );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Authorization:' . get_option( 'woocommerce_de_sevdesk_api_token' ) ,'Content-Type:application/x-www-form-urlencoded' ) );
			curl_setopt( $ch, CURLOPT_USERAGENT, sevdesk_woocommerce_get_user_agent() );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			$response = curl_exec( $ch );
			$result_array = json_decode( $response, true );
			curl_close( $ch );

			$accounts = array();

			if ( isset( $result_array[ 'objects' ] ) ) {
				$accounts = $result_array[ 'objects' ];
			}

			if ( ! empty( $accounts ) ) {
				set_transient( 'woocommerce_sevdesk_datev_accounts', $accounts, 5 );
			}

		} else {

			$accounts = $datev_accounts;
		}

		return $accounts;
	}

	/**
	 * Get legacy booking accounts
	 * 
	 * @since 3.32
	 * @return Array
	 */
	public static function get_legacy_booking_accounts() {
		$legacy_booking_accounts = get_transient( 'woocommerce_sevdesk_legacy_booking_accounts' );

		if ( ! is_array( $legacy_booking_accounts ) ) {

			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, sevdesk_woocommerce_api_get_base_url() . 'AccountingType?offset=0&useClientAccountingChart=true&embed=accountingSystemNumber&countAll=true&limit=-1&onlyOwn=false&emptyState=false' );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Authorization:' . get_option( 'woocommerce_de_sevdesk_api_token' ) ,'Content-Type:application/x-www-form-urlencoded' ) );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			$response = curl_exec( $ch );
			$result_array = json_decode( $response, true );
			curl_close( $ch );

			$accounts = array();

			if ( isset( $result_array[ 'objects' ] ) ) {
				$accounts = $result_array[ 'objects' ];
			}

			if ( ! empty( $accounts ) ) {
				set_transient( 'woocommerce_sevdesk_legacy_booking_accounts', $accounts, 5 );
			}

		} else {

			$accounts = $legacy_booking_accounts;

		}

		return $accounts;
	}

	/**
	 * Map legacy booking account id to datev id
	 * 
	 * @since 3.32
	 * @param Integer $booking_account_id
	 * @return Integer
	 */
	public static function get_datev_id_from_legacy_booking_account_id( $booking_account_id ) {
		$transient_info = intval( get_transient( 'woocommerce_sevdesk_get_datev_id_from_legacy_booking_account_id_' . $booking_account_id ) );

		if ( ! $transient_info > 0 ) {

			$datev_id = null;
			$booking_number = null;

			$legacy_booking_accounts = self::get_legacy_booking_accounts();

			// find booking number
			foreach ( $legacy_booking_accounts as $legacy_booking_account ) {
				if ( isset( $legacy_booking_account[ 'id' ] ) && intval( $booking_account_id ) === intval( $legacy_booking_account[ 'id' ] ) ) {
					if ( isset( $legacy_booking_account[ 'accountingSystemNumber' ] ) ) {
						if ( isset( $legacy_booking_account[ 'accountingSystemNumber' ][ 'number' ] ) ) {
							$booking_number = $legacy_booking_account[ 'accountingSystemNumber' ][ 'number' ];
							break;
						}	
					}
				}
			}

			// if we have a booking number
			if ( ! is_null( $booking_number ) ) {

				$datev_accounts = self::get_datev_accounts();
				foreach ( $datev_accounts as $datev_account ) {

					if ( isset( $datev_account[ 'number' ] ) && $booking_number === $datev_account[ 'number' ] ) {

						$now = intval( current_time( 'timestamp' ) );

						if ( isset( $datev_account[ 'validFrom' ] ) ) {
							if ( $now < intval( $datev_account[ 'validFrom' ] ) ) {
								continue;
							}
						}

						if ( isset( $datev_account[ 'validUntil' ] ) ) {
							if ( $now > intval( $datev_account[ 'validUntil' ] ) ) {
								continue;
							}
						}

						if ( isset( $datev_account[ 'accountDatevId' ] ) ) {
							$datev_id = intval( $datev_account[ 'accountDatevId' ] );
							set_transient( 'woocommerce_sevdesk_get_datev_id_from_legacy_booking_account_id_' . $booking_account_id, $datev_id, 10 );
							break;
						}
					}
				}
			}

		} else {
			$datev_id = $transient_info;
		}

		return $datev_id;
	}

	/**
	 * Map old booking account options to v2 datev booking accounts
	 * 
	 * @since 3.32
	 * @return void
	 */
	public static function map_booking_account_options() {
		$keys_and_v1_defaults = array(
			'woocommerce_de_sevdesk_booking_account_order_items',
			'woocommerce_de_sevdesk_booking_account_order_shipping',
			'woocommerce_de_sevdesk_booking_account_order_fees',
			'woocommerce_de_sevdesk_booking_account_refunds',
		);

		$option_suffix_v2 = '_v2';

		foreach ( $keys_and_v1_defaults as $key ) {

			$new_option_value = get_option( $key . $option_suffix_v2, '' );
			
			if ( '' === $new_option_value ) {

				$old_option_value = get_option( $key, '' );
				if ( ! empty( $old_option_value ) ) {

					$datev_id = self::get_datev_id_from_legacy_booking_account_id( $old_option_value );
					if ( ! is_null( $datev_id ) ) {
						update_option( $key . $option_suffix_v2, $datev_id );
					}
				}
			}
		}
	}

	/**
	 * Get individual datev booking account 
	 * if current meta value is empty and api v2 is used, map it from v1 meta value
	 * use this function only if api v2 is used
	 * 
	 * @param WC_Product $product
	 * @param String $order_or_refund
	 * @return Mixed
	 */
	public static function get_product_datev_booking_account( $product, $order_or_refund = 'order' ) {
		$datev_account = '';

		if ( is_object( $product ) && method_exists( $product, 'get_meta' ) ) {
			
			$default_key = 'order' === $order_or_refund ? '_sevdesk_field_order_account' : '_sevdesk_field_refund_account';
			$datev_key = $default_key . '_v2';
			$datev_account = $product->get_meta( $datev_key );
			
			if ( empty( $datev_account ) ) {

				$legacy_account = $product->get_meta( $default_key );
				if ( ( ! empty( $legacy_account ) ) && -1 != intval( $legacy_account ) ) {
					$mapped_account = self::get_datev_id_from_legacy_booking_account_id( $legacy_account );
					if ( ! is_null( $mapped_account ) ) {
						$datev_account = intval( $mapped_account );
						$product->update_meta_data( $datev_key, intval( $mapped_account ) );
						$product->save_meta_data();
					}
				}
			}
		}

		return $datev_account;
	}

	/**
	 * Get tax rules
	 * 
	 * @param Integer $country_id
	 * @return Array
	 */
	public static function get_tax_rules( $country_id = 1 ) {
		$transient_info = get_transient( 'woocommerce_sevdesk_tax_rules_' . $country_id );

		if ( ! is_array( $transient_info ) ) {

			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, sevdesk_woocommerce_api_get_base_url() . 'TaxRule/Factory/getTaxRulesForCountry?clientCountry[id]=' . $country_id . '&clientCountry[objectName]=StaticCountry' );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Authorization:' . get_option( 'woocommerce_de_sevdesk_api_token' ) ,'Content-Type:application/x-www-form-urlencoded' ) );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			$response = curl_exec( $ch );
			$result_array = json_decode( $response, true );
			curl_close( $ch );

			$tax_rules = array();

			if ( isset( $result_array[ 'objects' ] ) ) {
				$tax_rules_response = $result_array[ 'objects' ];

				foreach ( $tax_rules_response as $tax_rule ) {
					
					if ( isset( $tax_rule[ 'code' ] ) && isset( $tax_rule[ 'id' ] ) ) {
						$tax_rules[ $tax_rule[ 'code' ] ] = intval( $tax_rule[ 'id' ] );
					}

				}
			}

			if ( ! empty( $tax_rules ) ) {
				set_transient( 'woocommerce_sevdesk_tax_rules_' . $country_id, $tax_rules, 10 );
			}

		} else {

			$tax_rules = $transient_info;
		}

		return $tax_rules;
	}

	/**
	 * Get tax rule id by tax type
	 * 
	 * @param String $tax_type
	 * @param WC_Order $order (only used in apply_filters)
	 * @return Integer
	 */
	public static function get_tax_rule_id_by_tax_type( $tax_type, $order ) {
		$tax_rules = self::get_tax_rules();
		$tax_rule_id = 1;

		if ( 'default' === $tax_type ) {

			if ( isset( $tax_rules[ 'USTPFL_UMS_EINN' ] ) ) {
				$tax_rule_id = $tax_rules[ 'USTPFL_UMS_EINN' ];
			}

		} else if ( 'noteu' === $tax_type ) {

			if ( isset( $tax_rules[ 'AUSFUHREN' ] ) ) {
				$tax_rule_id = $tax_rules[ 'AUSFUHREN' ];
			}

		} else if ( 'eu' === $tax_type ) {

			if ( isset( $tax_rules[ 'INNERGEM_LIEF' ] ) ) {
				$tax_rule_id = $tax_rules[ 'INNERGEM_LIEF' ];
			}

		}

		return apply_filters( 'sevdesk_woocomerce_api_get_tax_rule_id_by_tax_type', $tax_rule_id, $tax_type, $tax_rules, $order );
	}


	/**
	 * Add some understandable error message
	 * 
	 * @param String $message
	 * @param WC_Order $order
	 * 
	 * @return String
	 */
	public static function get_v2_error_message( $message, $order ) {

		$v2_error_message = '';

		if ( is_object( $order ) && method_exists( $order, 'get_type' ) ) {
			
			if ( false !== strpos( $message, "Regular input tax rule 'AUSFUHREN' is not valid" ) ) {

				$v2_error_message = __( 'The sevdesk API version 2.0 does not support tax-exempt export delivery.', 'woocommerce-german-market' );

			} else if ( false !== strpos( $message, "Regular input tax rule 'INNERGEM_LIEF' is not valid " ) ) {

				$v2_error_message = __( 'The sevdesk API version 2.0 does not support tax free intracommunity delivery.', 'woocommerce-german-market' ); 
			
			} else {

				$allowed_taxes = array( 0.0, 7.0, 19.0 );
				$order_taxes = $order->get_taxes();

				foreach ( $order_taxes as $key => $order_tax ) {
					
					$check_tax = floatval( $order_tax->get_rate_percent() );
					
					if ( ! in_array( $check_tax, $allowed_taxes ) ) {
						$v2_error_message .= ' ' . sprintf( __( 'Unsupported tax rate: %s.', 'woocommerce-german-market' ), $check_tax . '%' );
					}

					if ( ! empty( $v2_error_message ) ) {
						$v2_error_message .= ' ' . __( 'The sevdesk API version 2.0 only supports German tax rates.', 'woocommerce-german-market' );
					}
				}
			}

			if ( ! empty( $v2_error_message ) ) {
				$message = trim( $v2_error_message ) . '<br><small>' . __( 'Direct error message from the sevdesk API:', 'woocommerce-german-market' ) . ' ' . $message . '</small>';
			}
		}

		return $message;
	}
}
