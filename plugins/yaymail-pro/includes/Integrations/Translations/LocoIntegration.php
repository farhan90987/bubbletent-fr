<?php

namespace YayMail\Integrations\Translations;

defined( 'ABSPATH' ) || exit;

class LocoIntegration extends BaseIntegration {
	public static function get_integration_plugin() {
		return 'loco';
	}
	public static function before_initialize() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_shared_script' ) );
	}
	public static function enqueue_shared_script() {
		if ( function_exists( 'loco_plugin_file' ) && function_exists( 'loco_plugin_version' ) ) {
			wp_enqueue_style( 'loco-admin-css', plugin_dir_url( loco_plugin_file() ) . '/pub/css/admin.css', array(), loco_plugin_version() );
		}
	}
	public static function get_available_languages() {
		$languages = array();
		$api       = new \Loco_api_WordPressTranslations();
		foreach ( $api->getInstalledCore() as $tag ) {
			$lang        = $api->getLocale( $tag );
			$is_valid =  \Loco_Locale::parse($tag)->isValid();
			if ($is_valid) {
				$languages[] = array(
					'code' => $tag,
					'lang' => null === $lang ? $tag : $lang->__get( 'lang' ),
					'name' => null === $lang ? 'English' : $lang->getName(),
					'icon' => null === $lang ? $tag : $lang->getIcon(),
				);
			}
		}

		return $languages;
	}
	public static function get_site_language( $order ) {
		$language = 'en';
		if ( function_exists( 'get_user_locale' ) ) {
			$language = \get_user_locale();
		}
		return $language;
	}
}
