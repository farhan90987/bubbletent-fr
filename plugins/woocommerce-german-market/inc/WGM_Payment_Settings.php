<?php

/**
 * Class WGM_Payment_Settings
 *
 */
class WGM_Payment_Settings {

	/**
	 * @var WGM_Payment_Settings
	 * @since v3.50
	 */
	private static $instance = null;
	
	public $settings = null;
	/**
	* Singletone get_instance
	*
	* @static
	* @return WGM_Payment_Settings
	*/
	public static function get_instance() {
		if ( self::$instance == NULL) {
			self::$instance = new WGM_Payment_Settings();	
		}
		return self::$instance;
	}

	/**
	 * Add hooks
	 * 
	 * @return void
	 */
	public function __construct() {
		add_filter( 'german_market_admin_submenu', array( $this, 'add_submenu' ) );
	}

	/**
	 * Get submenu
	 * 
	 * @wp-hook german_market_admin_submenu
	 * @param Array $submenu
	 * @return Array
	 */
	public function add_submenu( $submenu ) {

		if ( ! empty( $this->get_settings() ) ) {

			$submenu[ 'payment_settings' ] = array(
				'title'		=> __( 'Payment settings', 'woocommerce-german-market' ),
				'slug'		=> 'payment_settings',
				'callback'	=> array( $this, 'get_settings' ),
				'options'	=> 'yes'
			);

		}

		return $submenu;
	}

	/**
	 * Get setting for each payment gateway
	 * 
	 * @return Array
	 */
	public function get_settings() {

		$settings = array();

		if ( is_null( $this->settings ) ) {

			foreach ( WC()->payment_gateways()->payment_gateways() as $gateway_id => $gateway ) {

				$gateway_settings = apply_filters( 'german_market_gateway_settings_single_gateway', array(), $gateway_id );

				if ( ! empty( $gateway_settings ) ) {

					if ( is_object( $gateway ) && method_exists( $gateway, 'get_title' ) ) {
						$title = $gateway->get_title();
					} else if ( is_object( $gateway ) && isset( $gateway->title ) ) {
						$title = $gateway->title;
					} else {
						$title = $gateway_id;
					}
					
					if ( str_replace( 'stripe', '', $gateway_id ) !== $gateway_id ) {
						$title .= ' (Stripe)';
					} else if ( 
						( str_replace( 'ppcp', '', $gateway_id ) !== $gateway_id ) && 
						'PayPal' !== $title
					){
						$title .= ' (PayPal Payments)';	
					} else if ( str_replace( 'woocommerce_payments', '', $gateway_id ) !== $gateway_id ) {
						$title .= ' (WooPayments)';
					} else if ( str_replace( 'german_market', '', $gateway_id ) !== $gateway_id ) {
						$title .= ' (German Market)';
					}

					$settings[] = array(
						'name' => $title,
						'type' => 'title',
					);

					foreach ( $gateway_settings as $key => $setting ) {

						// don't output any titles again
						if ( isset( $setting[ 'type' ] ) && 'title' === $setting[ 'type' ] ) {
							continue;
						}

						$id = 'german_market_' . $gateway_id . '_' . $key;
						$settings[ $id ] = $setting;
						$settings[ $id ][ 'id' ] = $id;

						// if option is not saved yet, try to get legacy payment setting
						if ( empty( get_option( $id ) ) ) {
							if ( isset( $gateway->settings ) && isset( $gateway->settings[ $key ] ) ) {
								$settings[ $id ][ 'default' ] = $gateway->settings[ $key ];
							}
						}

						// make UI looks good
						if ( isset( $setting[ 'type' ] ) && 'textarea' === $setting[ 'type' ] ) {
							$settings[ $id ][ 'css' ] = 'min-width: 500px; min-height: 75px;';
							$settings[ $id ][ 'type' ] = 'german_market_textarea';
						}

						// rebuild descriptons from legacy payment gateway settings
						if ( isset( $settings[ $id ][ 'description' ] ) ) {
							$settings[ $id ][ 'desc' ] = $settings[ $id ][ 'description' ];
							unset( $settings[ $id ][ 'description' ] );
						}

					}

					$settings[] = array( 'type' => 'sectionend' );
				}
			}

			$this->settings = $settings;
		}


		return $this->settings;
	}

	/**
	 * Get payment setting by gateway and key
	 * Try to get legacy payment setting if option is empty
	 * 
	 * @param WC_Payment_Gateway $gateway
	 * @param String $key
	 * @param String $default
	 * 
	 * @return Mixed
	 */
	public static function get_option( $key, $gateway, $default = '' ) {

		$option_key = 'german_market_' . $gateway->id . '_' . $key;
		$option_value = get_option( $option_key );

		if ( empty( $option_value ) ) {
			if ( isset( $gateway->settings ) && isset( $gateway->settings[ $key ] ) ) {
				$option_value = $gateway->settings[ $key ];
			}
		}

		if ( empty( $option_value ) && ( ! empty( $default ) ) ) {
			$option_value = $default;
		}

		return $option_value;
	}
}
