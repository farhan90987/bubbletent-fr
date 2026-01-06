<?php

namespace MarketPress\GermanMarket\Shipping;

use WC_Order;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

class Helper {

	/**
	 * @var array
	 */
	private static $eu_country_codes = array(
		'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'EL', 'ES',
		'FI', 'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV',
		'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK'
	);

	/**
	 * @var array
	 */
	public static array $countryCodes = array(
		'IL' => '972',
		'AF' => '93',
		'AL' => '355',
		'DZ' => '213',
		'AS' => '1684',
		'AD' => '376',
		'AO' => '244',
		'AI' => '1264',
		'AG' => '1268',
		'AR' => '54',
		'AM' => '374',
		'AW' => '297',
		'AU' => '61',
		'AT' => '43',
		'AZ' => '994',
		'BS' => '1242',
		'BH' => '973',
		'BD' => '880',
		'BB' => '1246',
		'BY' => '375',
		'BE' => '32',
		'BZ' => '501',
		'BJ' => '229',
		'BM' => '1441',
		'BT' => '975',
		'BA' => '387',
		'BW' => '267',
		'BR' => '55',
		'IO' => '246',
		'BG' => '359',
		'BF' => '226',
		'BI' => '257',
		'KH' => '855',
		'CM' => '237',
		'CA' => '1',
		'CV' => '238',
		'KY' => '345',
		'CF' => '236',
		'TD' => '235',
		'CL' => '56',
		'CN' => '86',
		'CX' => '61',
		'CO' => '57',
		'KM' => '269',
		'CG' => '242',
		'CK' => '682',
		'CR' => '506',
		'HR' => '385',
		'CU' => '53',
		'CY' => '537',
		'CZ' => '420',
		'DK' => '45',
		'DJ' => '253',
		'DM' => '1767',
		'DO' => '1849',
		'EC' => '593',
		'EG' => '20',
		'SV' => '503',
		'GQ' => '240',
		'ER' => '291',
		'EE' => '372',
		'ET' => '251',
		'FO' => '298',
		'FJ' => '679',
		'FI' => '358',
		'FR' => '33',
		'GF' => '594',
		'PF' => '689',
		'GA' => '241',
		'GM' => '220',
		'GE' => '995',
		'DE' => '49',
		'GH' => '233',
		'GI' => '350',
		'GR' => '30',
		'GL' => '299',
		'GD' => '1473',
		'GP' => '590',
		'GU' => '1671',
		'GT' => '502',
		'GN' => '224',
		'GW' => '245',
		'GY' => '595',
		'HT' => '509',
		'HN' => '504',
		'HU' => '36',
		'IS' => '354',
		'IN' => '91',
		'ID' => '62',
		'IQ' => '964',
		'IE' => '353',
		'IT' => '39',
		'JM' => '1876',
		'JP' => '81',
		'JO' => '962',
		'KZ' => '77',
		'KE' => '254',
		'KI' => '686',
		'KW' => '965',
		'KG' => '996',
		'LV' => '371',
		'LB' => '961',
		'LS' => '266',
		'LR' => '231',
		'LI' => '423',
		'LT' => '370',
		'LU' => '352',
		'MG' => '261',
		'MW' => '265',
		'MY' => '60',
		'MV' => '960',
		'ML' => '223',
		'MT' => '356',
		'MH' => '692',
		'MQ' => '596',
		'MR' => '222',
		'MU' => '230',
		'YT' => '262',
		'MX' => '52',
		'MC' => '377',
		'MN' => '976',
		'ME' => '382',
		'MS' => '1664',
		'MA' => '212',
		'MM' => '95',
		'NA' => '264',
		'NR' => '674',
		'NP' => '977',
		'NL' => '31',
		'AN' => '599',
		'NC' => '687',
		'NZ' => '64',
		'NI' => '505',
		'NE' => '227',
		'NG' => '234',
		'NU' => '683',
		'NF' => '672',
		'MP' => '1670',
		'NO' => '47',
		'OM' => '968',
		'PK' => '92',
		'PW' => '680',
		'PA' => '507',
		'PG' => '675',
		'PY' => '595',
		'PE' => '51',
		'PH' => '63',
		'PL' => '48',
		'PT' => '351',
		'PR' => '1939',
		'QA' => '974',
		'RO' => '40',
		'RW' => '250',
		'WS' => '685',
		'SM' => '378',
		'SA' => '966',
		'SN' => '221',
		'RS' => '381',
		'SC' => '248',
		'SL' => '232',
		'SG' => '65',
		'SK' => '421',
		'SI' => '386',
		'SB' => '677',
		'ZA' => '27',
		'GS' => '500',
		'ES' => '34',
		'LK' => '94',
		'SD' => '249',
		'SR' => '597',
		'SZ' => '268',
		'SE' => '46',
		'CH' => '41',
		'TJ' => '992',
		'TH' => '66',
		'TG' => '228',
		'TK' => '690',
		'TO' => '676',
		'TT' => '1868',
		'TN' => '216',
		'TR' => '90',
		'TM' => '993',
		'TC' => '1649',
		'TV' => '688',
		'UG' => '256',
		'UA' => '380',
		'AE' => '971',
		'GB' => '44',
		'US' => '1',
		'UY' => '598',
		'UZ' => '998',
		'VU' => '678',
		'WF' => '681',
		'YE' => '967',
		'ZM' => '260',
		'ZW' => '263',
		'BO' => '591',
		'BN' => '673',
		'CC' => '61',
		'CD' => '243',
		'CI' => '225',
		'FK' => '500',
		'GG' => '44',
		'VA' => '379',
		'HK' => '852',
		'IR' => '98',
		'IM' => '44',
		'JE' => '44',
		'KP' => '850',
		'KR' => '82',
		'LA' => '856',
		'LY' => '218',
		'MO' => '853',
		'MK' => '389',
		'FM' => '691',
		'MD' => '373',
		'MZ' => '258',
		'PS' => '970',
		'PN' => '872',
		'RE' => '262',
		'RU' => '7',
		'BL' => '590',
		'SH' => '290',
		'KN' => '1869',
		'LC' => '1758',
		'MF' => '590',
		'PM' => '508',
		'VC' => '1784',
		'ST' => '239',
		'SO' => '252',
		'SJ' => '47',
		'SY' => '963',
		'TW' => '886',
		'TZ' => '255',
		'TL' => '670',
		'VE' => '58',
		'VN' => '84',
		'VG' => '1284',
		'VI' => '1340',
	);

	/**
	 * Add a flash notice to {prefix}options table until a full page refresh is done
	 *
	 * @static
	 *
	 * @param string $notice our notice message
	 * @param string $type This can be "info", "warning", "error" or "success", "warning" as default
	 * @param bool   $dismissible set this to TRUE to add is-dismissible functionality to your notice
	 *
	 * @return void
	 */
	public static function add_flash_notice( string $notice = '', string $type = 'warning', bool $dismissible = true ) {

		// Here we return the notices saved on our option, if there are not notices, then an empty array is returned
		$notices          = get_option( 'wgm_shipping_flash_notices', array() );
		$dismissible_text = ( $dismissible ) ? 'is-dismissible' : '';

		// We add our new notice.
		$notices[] = array(
			'notice'      => $notice,
			'type'        => $type,
			'dismissible' => $dismissible_text,
		);

		// Then we update the option with our notices array
		update_option( 'wgm_shipping_flash_notices', $notices );
	}

	/**
	 * Function executed when the 'admin_notices' action is called, here we check if there are notices on
	 * our database and display them, after that, we remove the option to prevent notices being displayed forever.
	 *
	 * @static
	 *
	 * @return void
	 */
	public static function display_flash_notices() {

		$notices = get_option( 'wgm_shipping_flash_notices', array() );

		// Iterate through our notices to be displayed and print them.
		foreach ( $notices as $notice ) {
			printf( '<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
				$notice[ 'type' ],
				$notice[ 'dismissible' ],
				$notice[ 'notice' ]
			);
		}

		// Now we reset our options to prevent notices being displayed forever.
		if ( ! empty( $notices ) ) {
			delete_option( 'wgm_shipping_flash_notices' );
		}
	}

	/**
	 * Helper function to get the street name without house no.
	 *
	 * @static
	 *
	 * @param string $data
	 *
	 * @return string
	 */
	public static function get_street_name( string $data ) : string {

		return self::split_street( $data, 'street' );
	}

	/**
	 * Helper function to get the house no.
	 *
	 * @static
	 *
	 * @param string $data
	 *
	 * @return string
	 */
	public static function get_house_no( string $data ) : string {

		return self::split_street( $data, 'house_no' );
	}

	/**
	 * Helper function to split street in name + house no.
	 *
	 * @static
	 *
	 * @param string $data
	 * @param string $what
	 *
	 * @return string house no or street name
	 */
	public static function split_street( string $data, string $what = 'street' ) : string {

		if ( ! empty( $data ) ) {
			preg_match( "/^([^\d]*[^\d\s]) *(\d.*)$/", $data, $street_array );
			if ( count( $street_array ) > 0 ) {
				if ( $what == 'street' ) {
					return trim( $street_array[ 1 ] );
				} else
				if ( $what == 'house_no' ) {
					return str_replace( ' ', '', trim( $street_array[ 2 ] ) );
				}
			}
		}

		return '';
	}

	/**
	 * Split name into firstname and lastname.
	 *
	 * @static
	 *
	 * @param string $fullname the customers fullname
	 * @param string $part the part what should be returned. ('firstname' or 'lastname')
	 *
	 * @return string|void name part or fullname if cannot split.
	 */
	public static function get_person_name_part( string $fullname, string $part = 'lastname' ) {

		if ( '' != $fullname ) {
			$results = array();
			preg_match('#^(\w+\.)?\s*([\'\’\w]+)\s+([\'\’\w]+)\s*(\w+\.?)?$#', $fullname, $results );
			switch ( $part ) {
				case 'firstname':
					if ( ! empty( $results[ 2 ] ) ) {
						return $results[ 2 ];
					} else {
						return $fullname;
					}
					break;
				case 'lastname':
				default:
					if ( ! empty( $results[ 3 ] ) ) {
						if ( ! empty( $results[ 4 ] ) ) {
							return $results[ 3 ] . ' ' . $results[ 4 ];
						}
						return $results[ 3 ];
					} else {
						return $fullname;
					}
					break;
			}
		}
	}

	/**
	 * Check if Shop address is set up right
	 * Otherwise we are disabling Shipping Methods
	 *
	 * @static
	 *
	 * @param string $addon 'dhl' or 'dpd'
	 *
	 * @return bool
	 */
	public static function check_shop_address_settings( string $addon = 'dhl' ) : bool {

		$available = true;

		if ( ( 'dhl' != strtolower( $addon ) ) && ( 'dpd' != strtolower( $addon ) ) ) {
			return false;
		} else {
			$addon = strtolower( $addon );
		}

		if ( ( 'dhl' === $addon ) && ( '' != get_option( 'wgm_dhl_shipping_gkp_shipper_reference' ) ) ) {
			return true;
		}

		if ( '' == get_option( 'wgm_' . $addon . '_shipping_shop_address_name', '' ) ) {
			$available = false;
		} else
		if ( '' == get_option( 'wgm_' . $addon . '_shipping_shop_address_street', '' ) ) {
			$available = false;
		} else
		if ( '' == get_option( 'wgm_' . $addon . '_shipping_shop_address_house_no', '' ) ) {
			$available = false;
		} else
		if ( '' == get_option( 'wgm_' . $addon . '_shipping_shop_address_zip_code', '' ) ) {
			$available = false;
		} else
		if ( '' == get_option( 'wgm_' . $addon . '_shipping_shop_address_city', '' ) ) {
			$available = false;
		} else
		if ( '' == get_option( 'wgm_' . $addon . '_shipping_shop_address_country', '' ) ) {
			$available = false;
		}

		return $available;
	}

	/**
	 * Returns the file size of the specific log file.
	 * We have to request the $name dynamically because our DHL add-on will use the same functions.
	 *
	 * @static
	 *
	 * @param string $name name of the log file
	 *
	 * @return string
	 */
	public static function log_filesize( string $name ) {

		if ( '' == trim( $name ) ) {
			$file_size = '';
		} else {
			$file_size = ( file_exists( wc_get_log_file_path( $name ) ) ? self::calculate_file_size( filesize( wc_get_log_file_path( $name ) ) ) : '' );
		}

		return $file_size;
	}

	/**
	 * Calculate file size of given bytes.
	 *
	 * @static
	 *
	 * @param $bytes
	 *
	 * @return int|string user-friendly formatted file size
	 */
	public static function calculate_file_size( $bytes ) {

		$result  = 0;
		$bytes   = floatval( $bytes );
		$arBytes = array(
			0 => array(
				"UNIT"  => "TB",
				"VALUE" => pow( 1024, 4 ),
			),
			1 => array(
				"UNIT"  => "GB",
				"VALUE" => pow( 1024, 3 ),
			),
			2 => array(
				"UNIT"  => "MB",
				"VALUE" => pow( 1024, 2 ),
			),
			3 => array(
				"UNIT"  => "KB",
				"VALUE" => 1024,
			),
			4 => array(
				"UNIT"  => "B",
				"VALUE" => 1,
			),
		);

		foreach ( $arBytes as $arItem ) {
			if ( $bytes >= $arItem[ 'VALUE' ] ) {
				$result = $bytes / $arItem[ 'VALUE' ];
				$result = str_replace( '.', '.', strval( round( $result, 2 ) ) ) . ' ' . $arItem[ 'UNIT' ];
				break;
			}
		}

		return $result;
	}

	/**
	 * Initialize log for debug-mode.
	 *
	 * @static
	 *
	 * @param string $version
	 *
	 * @return string
	 */
	public static function debug_init_log( string $version ) : string {

		$content = "----- German Market Shipping Log File ( " . $version . " ) -----\n";

		return $content;
	}

	/**
	 * Remove personal information from a given array for privacy policy.
	 *
	 * @static
	 *
	 * @param mixed $data
	 *
	 * @return mixed
	 */
	public static function debug_remove_data( $data ) {

		// TODO
		// We habe to remove some personal information from data.

		return $data;
	}

	/**
	 * Write data into log file.
	 * We have to request the $name dynamically because our DHL add-on will use the same functions.
	 *
	 * @static
	 *
	 * @param string $name log file name
	 * @param mixed  $mg data to save into log file
	 * @param string $title a given string
	 *
	 * @return void
	 */
	public static function debug_log_update( string $name, $mg, string $title ) {

		$msg  = self::debug_remove_data( $mg );
		$log  = wc_get_logger();
		$head = "----- German Market Shipping ( " . $title . " ) -----\n";

		if ( is_string( $msg ) ) {
			$log_text = $head . $msg;
		} else if ( is_array( $msg ) || is_object( $msg ) ) {
			$log_text = $head . print_r( (object) $msg, true );
		}

		$context = array( 'source' => $name );
		$log->log( "debug", $log_text, $context );
	}

	/**
	 * Filtering WooCommerce order statuses
	 *
	 * @public static
	 * @static
	 *
	 * @param array $statuses
	 *
	 * @return array
	 */
	public static function filter_get_order_statuses( array $statuses ) : array {

		if ( is_array( $statuses ) ) {
			foreach ( $statuses as $index => $value ) {
				if ( in_array( $index, array( 'wc-cancelled', 'wc-refunded', 'wc-failed' ) ) ) {
					unset( $statuses[ $index ] );
				}
			}
		}

		return $statuses;
	}

	/**
	 * Gets array of all phone country codes, array key is country ISO-3166 code and value is numeric dial code without '+'
	 *
	 * @static
	 *
	 * @return array
	 */
	public static function get_country_codes() : array {

		return self::$countryCodes;
	}

	/**
	 * Gets numeric dialcode without '+' sign for specified country code
	 *
	 * @static
	 *
	 * @param string $countryId ISO-3166 country code
	 *
	 * @return string
	 */
	public static function get_country_code_from_country_id( string $countryId ) : string {

		if ( isset( self::$countryCodes[ $countryId ] ) ) {
			return self::$countryCodes[ $countryId ];
		}

		return '';
	}

	/**
	 * Attempts to separate country code from phone number by supplied default country.
	 * If Phone number is missing country code, then it is applied by supplied country ISO-3166 code.
	 * Returns array with following format:
	 * array(
	 *     'dial_code' => country dial code with + prefix
	 *     'phone_number' => phone number without country code
	 * );
	 *
	 * @static
	 *
	 * @param string $phonenumber phone number that may contain country code
	 * @param string $countryId ISO-3166 country code
	 *
	 * @return array
	 */
	public static function separate_phone_number_from_country_code( string $phonenumber, string $countryId ) : array {

		$result          = array(
			'dial_code'    => '',
			'phone_number' => '',
		);
		$defaultDialCode = self::get_country_code_from_country_id( $countryId );

		// Remove all whitespace
		$phonenumber = str_replace( ' ', '', $phonenumber );

		// When country code is supplied, then it can:
		// Start with country code
		// Start with + sign
		// Start with 00 (double zero)
		$containsCountryCode = strpos( $phonenumber, $defaultDialCode ) === 0 || strpos( $phonenumber, '+' ) === 0 || strpos( $phonenumber, '00' ) === 0;

		// When country code is not supplied, then it can
		// Start with single zero
		// Start with any number
		if ( ! $containsCountryCode ) {
			$result[ 'dial_code' ] = '+' . $defaultDialCode;
			if ( strpos( $phonenumber, '0' ) === 0 ) {
				$result[ 'phone_number' ] = substr( $phonenumber, 1 );
			} else if ( is_array( $phonenumber ) && isset( $phonenumber[ 0 ] ) && ( $phonenumber[ 0 ] === '8' ) ) {
				$result[ 'phone_number' ] = substr( $phonenumber, 1 );
			} else {
				$result[ 'phone_number' ] = $phonenumber;
			}
		} else {
			// Phone number contains country code
			// We need to know what country code phone number contains
			// Remove 00 or + sign
			$phonenumber              = ltrim( $phonenumber, '+0' );
			$dialCode                 = self::_get_country_code_from_phonenumber( $phonenumber, $defaultDialCode );
			$result[ 'dial_code' ]    = '+' . $dialCode;
			$result[ 'phone_number' ] = substr( $phonenumber, strlen( $dialCode ) );
		}

		return $result;
	}

	/**
	 * Attempts to find country code from phone number, when it is known that phone number most certainly contains country code.
	 * Method assumes that longest found dial code is matcing dial code for this phone number.
	 * If now match is found then default code is used.
	 *
	 * @static
	 *
	 * @param string $phonenumber phone number that is known to contain country code
	 * @param string $defaultCode dial code to be used if no country code is found
	 *
	 * @return string resulting dial code without + sign
	 */
	public static function _get_country_code_from_phonenumber( string $phonenumber, string $defaultCode ) : string {

		$matchingCountryCode = '';
		foreach ( self::get_country_codes() as $countryIso => $dialCode ) {
			if ( strpos( $phonenumber, $dialCode ) === 0 ) {
				if ( strlen( $dialCode ) > $matchingCountryCode ) {
					$matchingCountryCode = $dialCode;
				}
			}
		}
		//as last resort apply default dial code
		if ( $matchingCountryCode === '' ) {
			return $defaultCode;
		}

		return $matchingCountryCode;
	}

	/**
	 * Returns the shipping provider from class namespace.
	 *
	 * @static
	 *
	 * @param string $namespace
	 *
	 * @return string
	 */
	public static function get_provider_from_namespace( string $namespace ) : string {

		$namespace = str_replace( '\\', '_', $namespace );
		preg_match( '/_Provider_([^_|$]*)/', $namespace, $namespace_array );

		return $namespace_array[ 1 ];
	}

	/**
	 * Returns a string with custom length.
	 *
	 * @static
	 *
	 * @param string $string
	 * @param int    $length
	 *
	 * @return string
	 */
	public static function custom_length( string $string, int $length ): string {

		if ( strlen( $string ) <= $length ) {
			return $string;
		} else {
			return substr( $string, 0, $length );
		}
	}

	/**
	 * Check if order has a shipping method from given shipping provider.
	 *
	 * @static
	 *
	 * @param WC_Order $order order object
	 * @param string   $shipping_provider provider
	 *
	 * @return bool
	 */
	public static function check_order_for_shipping_provider_methods( WC_Order $order, string $shipping_provider ) : bool {

		if ( ! is_object( $order ) || empty( $shipping_provider ) ) {
			return false;
		}

		$found_shipping_method = false;
		$provider              = strtolower( $shipping_provider );
		$shipping              = Woocommerce_Shipping::get_instance();
		$shipping_methods      = $shipping->providers[ $provider ][ 'methods' ];

		if ( ! empty( $shipping_methods ) ) {
			foreach( $shipping_methods as $method_id => $method ) {
				if ( $order->has_shipping_method( $method_id ) ) {
					$found_shipping_method = true;
					break;
				}
			}
		}

		return $found_shipping_method;
	}

	/**
	 * This array saves an array into another array at specific position.
	 *
	 * @static
	 *
	 * @param array $array
	 * @param mixed $position
	 * @param array $insert
	 *
	 * @return void
	 */
	public static function array_insert( array &$array, $position, array $insert ) {

		if ( is_int( $position ) ) {
			array_splice( $array, $position, 0, $insert );
		} else {
			$pos   = array_search( $position, array_keys( $array ) );
			$array = array_merge(
				array_slice( $array, 0, $pos + 1 ),
				$insert,
				array_slice( $array, $pos + 1 )
			);
		}
	}

	/**
	 * Returns a name string of the given terminal data.
	 *
	 * @static
	 *
	 * @param array $terminal
	 *
	 * @return string
	 */
	public static function get_formatted_terminal_name( array $terminal ) : string {

		return ( '' == $terminal[ 'company' ] || '' == $terminal[ 'street' ] ) ? '' : $terminal[ 'company' ] . ', ' . $terminal[ 'street' ];
	}

	/**
	 * Returns if shipment country is shop base country.
	 *
	 * @static
	 *
	 * @param string $shop_country shipping provider instance
	 * @param string $shipping_country shipping country from order
	 *
	 * @return bool
	 */
	public static function is_domestic_shipment( string $shop_country, string $shipping_country ) : bool {

		if ( empty( $shipping_country ) || empty( $shop_country ) ) {
			return false;
		}

		$base_country    = wc_get_base_location()[ 'country' ];

		if ( ( $base_country !== $shop_country ) && ( ! empty( $shipper_country ) ) ) {
			$base_country = $shipper_country;
		}

		return ( $shipping_country === $base_country );
	}

	/**
	 * Returns if we have an international shipment or not.
	 *
	 * @static
	 *
	 * @param string $shop_country
	 * @param string $shipping_country
	 * @param string $shipping_postcode
	 *
	 * @return bool
	 */
	public static function is_international_shipment( string $shop_country, string $shipping_country, string $shipping_postcode ) : bool {

		$is_international = false;
		$shop_country     = strtoupper( $shop_country );
		$shipping_country = strtoupper( $shipping_country );

		// Check for domestic shipment.
		if ( $shop_country != $shipping_country ) {
			if ( ! self::is_eu_country( $shop_country ) || ! self::is_eu_country( $shipping_country ) ) {
				// Is international shipment if one country is not a european country.
				$is_international = true;
			} else
			if ( ! self::is_eu_country( $shop_country ) && ! self::is_eu_country( $shipping_country ) ) {
				// Is international shipment if both counties are not european countries.
				$is_international = true;
			}
			// Check for Northern Ireland and postcode starts with 'BT' as part of EU.
			if ( self::is_eu_country( $shop_country ) && strpos( $shipping_postcode, 'BT' ) ) {
				$is_international = false;
			}
		}

		return $is_international;
	}

	/**
	 * Returns if we have an european shipment or not.
	 *
	 * @static
	 *
	 * @param string $shop_country
	 * @param string $shipping_country
	 * @param string $shipping_postcode
	 *
	 * @return bool
	 */
	public static function is_european_shipment( string $shop_country, string $shipping_country, string $shipping_postcode ) : bool {

		$is_european      = false;
		$shop_country     = strtoupper( $shop_country );
		$shipping_country = strtoupper( $shipping_country );

		// Check for domestic shipment.
		if ( $shop_country != $shipping_country ) {
			if ( self::is_eu_country( $shop_country ) && self::is_eu_country( $shipping_country ) ) {
				// Is european shipment if both counties are european countries.
				$is_european = true;
			}
			// Check for Northern Ireland and postcode starts with 'BT' as part of EU.
			if ( self::is_eu_country( $shop_country ) && strpos( $shipping_postcode, 'BT' ) ) {
				$is_european = true;
			}
		}

		return $is_european;
	}

	/**
	 * Returns if country is in europe.
	 *
	 * @static
	 *
	 * @param string $country_code
	 *
	 * @return bool
	 */
	public static function is_eu_country( string $country_code ) : bool {

		return in_array(strtoupper( $country_code ), self::$eu_country_codes );
	}

	/**
	 * Returns EU countries as array.
	 *
	 * @static
	 *
	 * @return array
	 */
	public static function get_eu_countries() : array {

		return self::$eu_country_codes;
	}

	/**
	 * Returns a country code from country name.
	 *
	 * @param string $country country name
	 *
	 * @return string
	 */
	public static function get_country_code_from_country_name( string $country ) : string {

		if ( strlen( $country ) == 2 ) {
			return $country;
		}

		$country_list = WC()->countries->get_countries();

		foreach ( $country_list as $country_code => $country_name ) {
			if ( $country_name === $country ) {

				return $country_code;
			}
		}

		return $country;
	}
}
