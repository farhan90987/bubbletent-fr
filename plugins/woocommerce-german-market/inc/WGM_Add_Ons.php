<?php

/**
 * Class WGM_Add_Ons
 *
 * This class loads the Add Ons
 *
 * @author  ChriCo
 */
class WGM_Add_Ons {

	/**
	* Include all activated modules
	*
	* @static
	* @return Array
	*/
	public static function init() {
		
		// get activated add ons
		$activated_add_ons = self::get_activated_add_ons();

		foreach ( $activated_add_ons as $base_name => $activated_add_on_file ) {

			$include = true;

			if ( $base_name == 'woocommerce-eu-vat-checkout' ) {
				
				$plugin = 'woocommerce-eu-vat-checkout/woocommerce-eu-vat-checkout.php';
				
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
				if ( is_plugin_active_for_network( $plugin ) || is_plugin_active( $plugin ) ) {
					update_option( 'wgm_add_on_woocommerce_eu_vat_checkout_turn_off', '1' );
					$include = false;
				}

			}

			if ( $base_name == 'woocommerce-return-delivery-pdf' ) {

				$plugin = 'woocommerce-return-delivery-pdf/woocommerce-return-delivery-pdf.php';
				
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
				if ( is_plugin_active_for_network( $plugin ) || is_plugin_active( $plugin ) ) {
					update_option( 'wgm_add_on_woocommerce_return_delivery_pdf_turn_off', '1' );
					$include = false;
				}
			}

			if ( $include ) {
				require_once( $activated_add_on_file );
			}
			
		}

	}

	/**
	* Get array of all modules
	*
	* @static
	* @return Array
	*/
	public static function get_all_add_ons( $get_only_activated_add_ons = false ) {
		
		$add_ons = array();

		// set the add on files
		$add_on_files = array();

		// get the module path
		$add_on_dir = @opendir( WGM_ADD_ONS_PATH );

		if ( $add_on_dir ) {
			while ( ( $file = readdir( $add_on_dir ) ) !== FALSE ) {

				// Skip the folders
				if ( substr( $file, 0, 1 ) == '.' )
					continue;
					
				// We only acceppt folder structures
				$add_on_files[ $file ] = WGM_ADD_ONS_PATH . '/' . $file . '/' . $file . '.php';

			}
			closedir( $add_on_dir );
		}

		// we don't have modules
		if ( empty( $add_on_files ) )
			return;

		// walk the modules
		foreach ( $add_on_files as $add_on_id => $add_on ) {

			// get option key and option value
			$option = 'wgm_add_on_'  . str_replace( '-', '_', $add_on_id );
			$option_value = get_option( $option );

			// Activate 'Woocommerce Shipping' wrapper addon by default.
			if ( 'wgm_add_on_woocommerce_shipping' === $option ) {

				if ( empty( get_option( 'wgm_add_on_woocommerce_shipping' ) && ( true === apply_filters( 'wgm_add_on_activate_woocommerce_shipping_option', true ) ) ) ) {
					update_option( 'wgm_add_on_woocommerce_shipping', 'on' );
				}

				$option_value = 'on';
			}

			$if_condition = ( $get_only_activated_add_ons ) ? $option_value == 'on' : true;
			// if module is activated
			if ( $if_condition ) {

				// create file name
				$add_on_file = WGM_ADD_ONS_PATH . DIRECTORY_SEPARATOR . $add_on_id . DIRECTORY_SEPARATOR . $add_on_id .'.php';
				
				if ( file_exists( $add_on_file ) ) {
					
					// include add on file
					$add_ons[ $add_on_id ] = $add_on_file;
				}
				
			}

		}

		return $add_ons;
	}

	/**
	* activate or deactivate add-on
	*
	* @static
	* @param $string $add_on_option_key
	* @param $new_activation
	* @return void
	*/
	public static function activate_or_deactivate_new_add_on( $add_on_option_key, $new_activation ) {

		$class_name = str_replace( 'wgm_add_on_', '', $add_on_option_key );
		$class_name_parts = explode( '_', $class_name );
		$class_name_parts = array_map( 'ucfirst', $class_name_parts );
		$class_name = implode( '_', $class_name_parts );


		if ( $new_activation === 'off' ) {
			if ( class_exists( $class_name ) && method_exists( $class_name, 'deactivate' ) ) {
				call_user_func( array( $class_name, 'deactivate' ) );
			}
		}

		update_option( $add_on_option_key, $new_activation );
		self::init();

		if ( $new_activation === 'on' ) {
			if ( class_exists( $class_name ) && method_exists( $class_name, 'activate' ) ) {
				call_user_func( array( $class_name, 'activate' ) );
			}

		}
	}

	/**
	* Get array of all activated modules
	*
	* @static
	* @return Array
	*/
	public static function get_activated_add_ons() {
		return self::get_all_add_ons( true );
	}


	/**
	* Run uninstall.php of all add ons
	*
	* @static
	* @return void
	*/
	public static function uninstall() {

		$all_add_ons = self::get_all_add_ons();
		
		foreach ( $all_add_ons as $add_on_id => $add_on ) {
			
			// get uninstall.php
			$uninstall_file = WGM_ADD_ONS_PATH . DIRECTORY_SEPARATOR . $add_on_id . DIRECTORY_SEPARATOR . 'uninstall.php';
			
			// only if file exists
			if ( file_exists( $uninstall_file ) ) {
				include_once( $uninstall_file );
			}
		}

	}

	/**
	* Build class name of add-on from add-on key ( id )
	*
	* @static
	* @param String $key
	* @return String
	*/
	public static function get_class_name( $key ) {

		// init
		$name = $key;

		if ( trim( $key ) != '' ) {

			$name = '';
			$key_array = explode( '-', $key );
			$name_array = array();
			foreach ( $key_array as $key_element ) {
				$name_array[] = ucfirst( $key_element );
			}

			$name = implode( '_', $name_array );

		}

		return $name;

	}
}
