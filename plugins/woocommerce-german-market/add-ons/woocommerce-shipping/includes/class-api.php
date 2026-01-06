<?php

namespace MarketPress\GermanMarket\Shipping;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

class Api extends Cache {

	/**
	 * Shipping provider instance holder.
	 *
	 * @acces public
	 *
	 * @var object
	 */
	public object $provider;

	/**
	 * @acces public
	 *
	 * @var bool
	 */
	public bool $staging = false;

	/**
	 * @static
	 *
	 * @var string
	 */
	public static string $error_string = '';

	/**
	 * Class constructor.
	 *
	 * @acces protected
	 */
	protected function __construct() {

		parent::__construct();
	}

	/**
	 * Returns the SOAP path for 'live' or 'sandbox' mode
	 *
	 * @param string $path
	 *
	 * @return string dpd stating or live api url
	 */
	protected function get_soap_url( string $path ) : string {

		return ( true === $this->staging ? $this->provider->api_base_url_staging : $this->provider->api_base_url ) . $path;
	}

	/**
	 * Check if given domain / server is online.
	 *
	 * @acces protected
	 *
	 * @return bool
	 */
	protected function is_server_online()  : bool  {

		$host     = ( true === $this->staging ) ? $this->provider->api_base_url_staging : $this->provider->api_base_url;
		$curlInit = curl_init( $host );

		curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($curlInit,CURLOPT_HEADER,true);
		curl_setopt($curlInit,CURLOPT_NOBODY,true);
		curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);

		$response = curl_exec($curlInit);

		curl_close($curlInit);

		return (bool) $response;
	}

}
