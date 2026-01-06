<?php

namespace MarketPress\GermanMarket\Shipping\Provider\DHL;

use SoapHeader;
use SoapVar;
use stdClass;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

class Internetmarke_Wss_Header extends SoapHeader {

	private $wss_ns = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
	private $wsp_ns = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText';

	/**
	 * Dhl_Shipping_Internetmarke_Wss_Header constructor.
	 *
	 * @param string $username Internetmarke ProdWS username
	 * @param string $password Internetmarke ProdWS password
	 */
	function __construct( $username, $password ) {

		$encodedPassword = htmlspecialchars( $password );

		$auth = new stdClass();
		$auth->Username = new SoapVar( $username, XSD_STRING, NULL, $this->wss_ns, NULL, $this->wss_ns );
		$auth->Password = new SoapVar('<ns2:Password Type="' . $this->wsp_ns . '">' . $encodedPassword .'</ns2:Password>', XSD_ANYXML );

		$username_token = new stdClass();
		$username_token->UsernameToken = new SoapVar( $auth, SOAP_ENC_OBJECT, NULL, $this->wss_ns, 'UsernameToken', $this->wss_ns );

		$security_sv = new SoapVar(
			new SoapVar( $username_token, SOAP_ENC_OBJECT, NULL, $this->wss_ns, 'UsernameToken', $this->wss_ns ),
			SOAP_ENC_OBJECT, NULL, $this->wss_ns, 'Security', $this->wss_ns
		);

		parent::__construct( $this->wss_ns, 'Security', $security_sv, true );
	}

}
