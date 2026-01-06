<?php

namespace YayMail\Integrations\Translations;

use YayMail\Abstracts\BaseTranslationsIntegration;
use YayMail\Utils\SingletonTrait;

/**
 * Weglot
 * * @method static Weglot get_instance()
 */
class Weglot extends BaseTranslationsIntegration {

    use SingletonTrait;

    public $id = 'weglot';

    public $title = 'Weglot';

    public function __construct() {
        if ( ! self::is_3rd_party_installed() ) {
            return;
        }
        add_action( 'yaymail_after_enqueue_settings_page_scripts', [ $this, 'enqueue_weglot_styles' ] );
        parent::__construct();
    }

    public static function is_3rd_party_installed() {
        return function_exists( 'weglot_get_service' );
    }

    public function get_available_languages() {
        $languages                              = [];
        $request_url_services                   = \weglot_get_service( 'Request_Url_Service_Weglot' );
            $language_services                  = \weglot_get_service( 'Language_Service_Weglot' );
            $original_and_destination_languages = $language_services->get_original_and_destination_languages( $request_url_services->is_allowed_private() );
            $languages                          = array_map(
                function( $lang ) {
                    return [
                        'code' => $lang->getInternalCode(),
                        'name' => $lang->getEnglishName(),
                        'flag' => '<label style="vertical-align: top;" class="weglot-flags flag-0 ' . $lang->getInternalCode() . '"><span class="wglanguage-name"></span></label>',
                    ];
                },
                $original_and_destination_languages
            );
        return $languages;
    }

    public function get_order_language( $order ) {
        $language = function_exists( 'weglot_get_current_language' ) ? \weglot_get_current_language() : '';
        if ( ! empty( $order ) ) {
            $order_language = get_post_meta( $order->get_id(), 'weglot_language', true );
            if ( $order_language ) {
                $language = $order_language;
            }
        }
        return 'en' !== $language ? $language : '';
    }

    public function enqueue_weglot_styles() {
        // Load css to display flag of Weglot plugin
        if ( defined( 'WEGLOT_DIRURL' ) && defined( 'WEGLOT_VERSION' ) && defined( 'WEGLOT_URL_DIST' ) ) {
            wp_enqueue_style( 'weglot-new-flag-css', WEGLOT_DIRURL . 'app/styles/new-flags.css', [], WEGLOT_VERSION );
            wp_enqueue_style( 'weglot-front-css', WEGLOT_URL_DIST . '/css/front-css.css', [], WEGLOT_VERSION );
        }
    }
}
