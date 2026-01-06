<?php

namespace YayMail\Integrations\Translations;

use YayMail\Abstracts\BaseTranslationsIntegration;
use YayMail\Utils\SingletonTrait;

/**
 * Polylang
 * * @method static Polylang get_instance()
 */
class Polylang extends BaseTranslationsIntegration {

    use SingletonTrait;

    public $id = 'polylang';

    public $title = 'Polylang';

    public function __construct() {
        if ( ! self::is_3rd_party_installed() ) {
            return;
        }
        $this->before_initialize();
        parent::__construct();
    }

    public static function is_3rd_party_installed() {
        return class_exists( 'Polylang' );
    }

    public function get_available_languages() {
        $languages     = [];
        $get_languages = apply_filters( 'wpml_active_languages', [], null );
        foreach ( $get_languages as $language ) {
            $languages[] = [
                'code' => $language['language_code'],
                'name' => $language['native_name'],
                'flag' => '<img src="' . $language['country_flag_url'] . '" alt="' . $language['native_name'] . '" style="width: 20px; vertical-align: -0.125em; margin-right: 5px;"  />',
            ];
        }
        return $languages;
    }

    public function get_order_language( $order ) {
        $language = '';
        if ( ! empty( $order ) ) {
            $order_id       = $order->get_id();
            $order_language = pll_get_post_language( $order_id, 'slug' );
            if ( ! empty( $order_language ) ) {
                $language = $order_language;
            } else {
                $language = function_exists( 'pll_default_language' ) ? pll_default_language() : '';
            }
        } elseif ( function_exists( 'pll_current_language' ) && function_exists( 'pll_default_language' ) ) {
                $language = false !== pll_current_language() ? pll_current_language() : pll_default_language();
        }
        return 'en' !== $language ? $language : '';
    }

    public function before_initialize() {
        $polylang_options = get_option( 'polylang' );
        if ( isset( $polylang_options['post_types'] ) ) {
            $yaymail_template_position = array_search( 'yaymail_template', $polylang_options['post_types'] );
            if ( false !== $yaymail_template_position ) {
                array_splice( $polylang_options['post_types'], $yaymail_template_position, 1 );
                update_option( 'polylang', $polylang_options );
            }
        }
    }
}
