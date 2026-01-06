<?php

namespace MarketPress\GermanMarket\Shipping;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

class Cache {

	/**
	 * @acces public
	 *
	 * @var string
	 */
	public string $provider_id;

	/**
	 * @acces private
	 *
	 * @var array
	 */
	private static array $runtime_cache;

	/**
	 * @acces private
	 *
	 * @var string
	 */
	private static string $runtime_cache_key;

	/**
	 * Class constructor.
	 *
	 * @acces protected
	 */
	protected function __construct() {

		if ( ! isset( WC()->session ) ) {
			$this->init_woocommerce_session_handler();
		}
	}

	/**
	 * Initialize the WooCommerce Session Handler.
	 *
	 * @acces protected
	 *
	 * @return void
	 */
	protected function init_woocommerce_session_handler() {

		// WC()->frontend_includes();

		// Is session running?
		if ( isset ( WC()->session ) && ! WC()->session->has_session() ) {
			WC()->session->set_customer_session_cookie( true );
			WC()->session->init();
		}
	}

	/**
	 * Store data into WooCommerce session.
	 *
	 * @acces protected
	 *
	 * @param string $type
	 * @param array  $data
	 *
	 * @return void
	 */
	protected function set_session_cache( string $type, array $data ) {

		set_transient( $this->provider_id . '_' . $type, $data, DAY_IN_SECONDS );
	}

	/**
	 * Return data from WooCommerce session.
	 *
	 * @acces public
	 *
	 * @param string $type
	 *
	 * @return array|string
	 */
	public function get_session_cache( string $type ) {

		return get_transient( $this->provider_id . '_' . $type );
	}

	/**
	 * Store data into runtime cache, session cache and transient.
	 *
	 * @param string $type
	 * @param array  $data
	 * @param array  $address request address data needed if we don't have a runtime cache key
	 *
	 * @return void
	 */
	public function set_cache( string $type, array $data, array $address ) {

		if ( empty( self::$runtime_cache_key ) ) {
			self::$runtime_cache_key = $this->build_cache_key( $type, $address );
		}

		self::$runtime_cache[ self::$runtime_cache_key ] = $data;

		$this->set_session_cache( $type, $data );

		set_transient( self::$runtime_cache_key, $data, 86400 * 3 );
	}

	/**
	 * Try to return stored value from runtime cache or transient.
	 *
	 * @acces public
	 *
	 * @param string $type
	 * @param array  $address
	 *
	 * @return array
	 */
	public function get_cache( string $type, array $address ) : array {

		self::$runtime_cache_key = $this->build_cache_key( $type, $address );

		if ( ! empty( self::$runtime_cache[ self::$runtime_cache_key ] ) ) {
			$terminals = self::$runtime_cache[ self::$runtime_cache_key ];
		} else {
			$terminals = get_transient( self::$runtime_cache_key );
			if ( empty( $terminals ) ) {
				$terminals = array(
					'terminals'  => json_encode( array() ),
					'last_query' => '',
				);
			}
			self::$runtime_cache[ self::$runtime_cache_key ] = $terminals;
			$this->set_session_cache( $type, $terminals );
		}

		return $terminals;
	}

	/**
	 * Returns a sanitized unique cache key based on type and requested data.
	 * Return format: dpd_terminals_20_de_karcherallee12_01277_dresden
	 *
	 * @acces protected
	 *
	 * @param string $type e.g. terminals, parcelshops, packstations
	 * @param array  $request_data api request data e.g. limit, country, street, zip code, city
	 *
	 * @return string
	 */
	protected function build_cache_key( string $type, array $request_data ) : string {

		$cache_key = sanitize_key( $this->provider_id ) . '_' . sanitize_key( $type );
		foreach ( $request_data as $key => $value ) {
			$cache_key .= '_' . sanitize_key( $value );
		}

		return $cache_key;
	}
}
