<?php

namespace YayMail\Integrations\Translations;

defined( 'ABSPATH' ) || exit;

class TranslatePressIntegration extends BaseIntegration {
	public static function get_integration_plugin() {
		return 'translatepress';
	}
	public static function before_initialize() {
	}
	public static function get_available_languages() {
		$preferred_user_language = new \TRP_Preferred_User_Language();
		$published_languages     = $preferred_user_language->get_published_languages();
		$languages               = array();
		foreach ( $published_languages as $code => $language_name ) {
			$flags_path     = apply_filters( 'trp_flags_path', TRP_PLUGIN_URL . 'assets/images/flags/', $code );
			$flag_file_name = apply_filters( 'trp_flag_file_name', "{$code}.png", $language_name );

			$languages[] = array(
				'code' => $code,
				'name' => $language_name,
				'flag' => $flags_path . $flag_file_name,
			);
		}
		return $languages;
	}
	public static function get_site_language( $order ) {
		global $TRP_LANGUAGE;
		$language = ! empty( $TRP_LANGUAGE ) ? $TRP_LANGUAGE : 'en';
		if ( null !== $order || isset( $GLOBALS['yaymail_set_order'] ) ) {
			$order_data = ( null !== $order ) ? $order : $GLOBALS['yaymail_set_order'];

			$order_language = '';

			if ( $order_data instanceof \WC_Order ) {
				$order_language = get_post_meta( $order_data->get_id(), 'trp_language', true );
			}

			if ( ! empty( $order_language ) ) {
				$language = $order_language;
			}
		}
		return $language;
	}
}
