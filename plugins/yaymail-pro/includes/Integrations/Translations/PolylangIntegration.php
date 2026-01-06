<?php

namespace YayMail\Integrations\Translations;

defined( 'ABSPATH' ) || exit;

class PolylangIntegration extends BaseIntegration {
	public static function get_integration_plugin() {
		return 'polylang';
	}
	public static function before_initialize() {
		self::turn_off_post_type_translation();
	}
	public static function turn_off_post_type_translation() {
		$polylang_options = get_option( 'polylang' );
		if ( isset( $polylang_options['post_types'] ) ) {
			$yaymail_template_position = array_search( 'yaymail_template', $polylang_options['post_types'] );
			if ( false !== $yaymail_template_position ) {
				array_splice( $polylang_options['post_types'], $yaymail_template_position, 1 );
				update_option( 'polylang', $polylang_options );
			}
		}
	}

	public static function get_available_languages() {
		$languages = array();
		if ( function_exists( 'icl_get_languages' ) ) {
			foreach ( icl_get_languages() as $key => $lang ) {
				$languages[] = array(
					'code' => $lang['language_code'],
					'name' => $lang['native_name'],
					'flag' => $lang['country_flag_url'],
				);
			}
		}
		return $languages;
	}
	public static function get_site_language( $order ) {
		$language = 'en';
		if ( ! empty( $order ) ) {
			$order_id       = $order->get_id();
			$order_language = pll_get_post_language( $order_id, 'slug' );
			if ( ! empty( $order_language ) && $order_language ) {
				$language = $order_language;
			}
		} else {
			if ( function_exists( 'icl_get_current_language' ) && function_exists( 'icl_get_default_language' ) ) {
				$language = false !== \icl_get_current_language() ? \icl_get_current_language() : \icl_get_default_language();
			}
		}

		return $language;
	}
}
