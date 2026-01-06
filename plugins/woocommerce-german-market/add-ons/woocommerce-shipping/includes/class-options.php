<?php

namespace MarketPress\GermanMarket\Shipping;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

class Options {

	/**
	 * @acces private
	 *
	 * @var object
	 */
	private object $provider;

	/**
	 * @acces private
	 *
	 * @var string
	 */
	private string $prefix = 'wgm_';

	/**
	 * Class constructor.
	 *
	 * @param object $provider
	 */
	public function __construct( object $provider ) {

		$this->provider = $provider;
	}

	/**
	 * Returns option value from database.
	 *
	 * @acces public
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function get_option( string $key, $default = '' ) {

		return get_option( $this->build_option_key( $key ), $default );
	}

	/**
	 * Update option in database.
	 *
	 * @acces public
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function update_option( string $key, $value ) {

		update_option( $this->build_option_key( $key ), $value );
	}

	/**
	 * Returns the option key.
	 *
	 * @acces public
	 *
	 * @param string $option
	 *
	 * @return string
	 */
	public function build_option_key( string $option ) : string {

		return $this->prefix . $this->provider->id . '_' . $option;
	}
}
