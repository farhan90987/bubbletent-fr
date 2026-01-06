<?php

namespace YayMail\Integrations\Translations;

defined( 'ABSPATH' ) || exit;

class WeglotIntegration extends BaseIntegration {
	public static function get_integration_plugin() {
		return 'weglot';
	}
	public static function before_initialize() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_shared_scripts' ) );
	}
	public static function enqueue_shared_scripts() {
		if ( defined( 'WEGLOT_DIRURL' ) && defined( 'WEGLOT_VERSION' ) && defined( 'WEGLOT_URL_DIST' ) ) {
			wp_enqueue_style( 'weglot-new-flag-css', WEGLOT_DIRURL . 'app/styles/new-flags.css', array(), WEGLOT_VERSION );
			wp_enqueue_style( 'weglot-front-css', WEGLOT_URL_DIST . '/css/front-css.css', array(), WEGLOT_VERSION );
		}
	}
	public static function get_available_languages() {
		$languages = array();
		if ( function_exists( 'weglot_get_service' ) ) {
			$request_url_services               = \weglot_get_service( 'Request_Url_Service_Weglot' );
			$language_services                  = \weglot_get_service( 'Language_Service_Weglot' );
			$original_and_destination_languages = $language_services->get_original_and_destination_languages( $request_url_services->is_allowed_private() );
			$languages                          = array_map(
				function( $lang ) {
					return array(
						'code' => $lang->getInternalCode(),
						'name' => $lang->getEnglishName(),
					);
				},
				$original_and_destination_languages
			);
		}

		return $languages;
	}
	public static function get_site_language( $order ) {
		$language = 'en';
		if ( function_exists( 'weglot_get_current_language' ) ) {
			$language = \weglot_get_current_language();
		}
		if ( null !== $order ) {
			$order_language = get_post_meta( $order->get_id(), 'weglot_language', true );
			if ( $order_language ) {
				$language = $order_language;
			}
		}
		return $language;
	}
}
