<?php

namespace YayMail\Integrations\Translations;

use YayMail\Abstracts\BaseTranslationsIntegration;
use YayMail\Utils\SingletonTrait;

/**
 * Loco
 * * @method static Loco get_instance()
 */
class Loco extends BaseTranslationsIntegration {

    use SingletonTrait;

    public $id = 'loco';

    public $title = 'Loco';

    public function __construct() {
        if ( ! self::is_3rd_party_installed() ) {
            return;
        }

        add_action( 'yaymail_after_enqueue_settings_page_scripts', [ $this, 'enqueue_loco_css' ] );
        parent::__construct();
    }

    public static function is_3rd_party_installed() {
        return class_exists( '\Loco_api_WordPressTranslations' );
    }

    public function get_available_languages() {
        $languages = [];
        $api       = new \Loco_api_WordPressTranslations();
        foreach ( $api->getInstalledCore() as $tag ) {
            $locale = \Loco_Locale::parse( $tag );
            if ( $locale->isValid() ) {
                $tag         = (string) $locale;
                $languages[] = [
                    'code' => 'en_US' !== $tag ? $tag : 'en',
                    'name' => $locale->ensureName( $api ),
                    'flag' => '<span class="' . $locale->getIcon() . '" lang="' . $locale->lang . '"><code>' . $tag . '</code></span>',
                ];
            }
        }
        return $languages;
    }

    public function get_order_language( $order ) {
        $language = '';
        if ( $order && $order->meta_exists( 'wcpdf_order_locale' ) ) {
            $order_language = $order->get_meta( 'wcpdf_order_locale', true );
            $language       = ! in_array( $order_language, [ 'en', 'en_US', 'en_AU' ], true ) ? $order_language : '';
        } elseif ( function_exists( 'get_user_locale' ) ) {
            $user_language = get_user_locale();
            $language      = ! in_array( $user_language, [ 'en', 'en_US', 'en_AU' ], true ) ? $user_language : '';
        }
        return $language;
    }

    public function enqueue_loco_css() {
        wp_enqueue_style( 'loco-admin-css', plugin_dir_url( loco_plugin_file() ) . 'pub/css/admin.css', [], loco_plugin_version() );
    }
}
