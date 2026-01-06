<?php

namespace YayMail\Integrations\Translations;

class Initialize {
	public function __construct() {
		$this->load();
	}

	public static function get_integration() {
		$namespace              = '\YayMail\Integrations\Translations';
		$priority_integration   = self::get_priority_integration();
		$supported_integrations = array(
			'wpml'           => "{$namespace}\WPMLIntegration",
			'translatepress' => "{$namespace}\TranslatePressIntegration",
			'gtranslate'     => "{$namespace}\GTranslateIntegration",
			'weglot'         => "{$namespace}\WeglotIntegration",
			'loco'           => "{$namespace}\LocoIntegration",
			'polylang'       => "{$namespace}\PolylangIntegration",
		);
		$available_integrations = self::get_available_integrations();

		if ( empty( $available_integrations ) ) {
			return null;
		}

		if ( \in_array( $priority_integration, $available_integrations ) ) {
			return $supported_integrations[ $priority_integration ];
		}

		return $supported_integrations[ $available_integrations[0] ];

	}

	public static function get_available_integrations() {
		$available_integrations = array();
		if ( defined( 'WEGLOT_NAME' ) ) {
			$available_integrations[] = 'weglot';
		}
		if ( class_exists( 'GTranslate' ) ) {
			$available_integrations[] = 'gtranslate';
		}
		if ( class_exists( 'SitePress' ) ) {
			$available_integrations[] = 'wpml';
		}
		if ( class_exists( 'TRP_Translate_Press' ) ) {
			$available_integrations[] = 'translatepress';
		}
		if ( class_exists( 'Loco_Locale' ) ) {
			$available_integrations[] = 'loco';
		}
		if ( class_exists( 'Polylang' ) ) {
			$available_integrations[] = 'polylang';
		}
		return $available_integrations;
	}

	public static function get_priority_integration() {
		return get_option( 'yaymail_priority_translate_integration', null );
	}

	public function load() {
		$integration = self::get_integration();
		if ( $integration ) {
			$integration::initialize();
		}
	}
}

new Initialize();
