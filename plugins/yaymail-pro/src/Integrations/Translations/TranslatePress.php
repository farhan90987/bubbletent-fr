<?php

namespace YayMail\Integrations\Translations;

use YayMail\Abstracts\BaseTranslationsIntegration;
use YayMail\Utils\SingletonTrait;

/**
 * TranslatePress
 * * @method static TranslatePress get_instance()
 */
class TranslatePress extends BaseTranslationsIntegration {

    use SingletonTrait;

    public $id = 'translatepress';

    public $title = 'TranslatePress';

    public function __construct() {
        if ( ! self::is_3rd_party_installed() ) {
            return;
        }
        parent::__construct();
    }

    public static function is_3rd_party_installed() {
        return class_exists( 'TRP_Translate_Press' );
    }

    public function get_available_languages() {
        $languages               = [];
        $preferred_user_language = new \TRP_Preferred_User_Language();
        $get_languages           = $preferred_user_language->get_published_languages();
        foreach ( $get_languages as $code => $name ) {
            $flags_path     = apply_filters( 'trp_flags_path', TRP_PLUGIN_URL . 'assets/images/flags/', $code );
            $flag_file_name = apply_filters( 'trp_flag_file_name', "{$code}.png", $name );
            $flag           = $flags_path . $flag_file_name;
            $languages[]    = [
                'code' => 'en_US' !== $code ? $code : 'en',
                'name' => $name,
                'flag' => '<img src="' . $flag . '" alt="' . $name . '" style="width: 20px; vertical-align: -0.125em; margin-right: 5px;"  />',
            ];
        }
        return $languages;
    }

    public function get_order_language( $order ) {
        global $TRP_LANGUAGE; // phpcs:ignore
        $language = ! empty( $TRP_LANGUAGE ) ? $TRP_LANGUAGE : ''; // phpcs:ignore
        if ( ! empty( $order ) ) {
            $order_language = get_post_meta( $order->get_id(), 'trp_language', true );
            if ( $order_language ) {
                $language = $order_language;
            }
        }
        return ( 'en' !== $language && 'en_US' !== $language ) ? $language : '';
    }
}
