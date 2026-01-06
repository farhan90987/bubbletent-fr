<?php

namespace YayMail\Integrations\Translations;

use YayMail\Abstracts\BaseTranslationsIntegration;
use YayMail\Utils\SingletonTrait;

/**
 * WPML
 * * @method static WPML get_instance()
 */
class WPML extends BaseTranslationsIntegration {

    use SingletonTrait;

    public $id = 'wpml';

    public $title = 'WPML';

    public function __construct() {
        if ( ! self::is_3rd_party_installed() ) {
            return;
        }
        $this->before_initialize();
        parent::__construct();
    }

    public static function is_3rd_party_installed() {
        return class_exists( 'SitePress' );
    }

    public function get_available_languages() {
        global $sitepress;
        $languages        = [];
        $active_languages = apply_filters( 'wpml_active_languages', [], null );
        foreach ( $active_languages as $language ) {
            $name        = isset( $language['translated_name'] ) ? $language['translated_name'] : $language['display_name'];
            $languages[] = [
                'code' => $language['code'],
                'name' => $name,
                'flag' => '<img src="' . $sitepress->get_flag_url( $language['code'] ) . '" alt="' . $name . '" style="width: 20px; vertical-align: -0.125em; margin-inline-end: 3px;"  />',
            ];
        }
        return $languages;
    }

    public function get_order_language( $order ) {
        global $sitepress;
        $language = \defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : '';
        if ( null !== $order ) {
            if ( class_exists( 'WCML_Orders' ) ) {
                $order_language = \WCML_Orders::getLanguage( $order->get_id() );
            } elseif ( $order->meta_exists( 'wpml_language' ) ) {
                $order_language = $order->get_meta( 'wpml_language' );
            } else {
                $post_language_details = apply_filters( 'wpml_post_language_details', null, $order->get_id() );
                $order_language        = is_array( $post_language_details ) && isset( $post_language_details['language_code'] ) ? $post_language_details['language_code'] : '';
            }
            if ( ! empty( $order_language ) ) {
                $language = $order_language;
            }
        }
        $sitepress->switch_lang( $language );
        return 'en' !== $language ? $language : '';
    }

    public function before_initialize() {
        global $sitepress_settings, $sitepress;
        $custom_posts_sync                     = $sitepress_settings['custom_posts_sync_option'];
        $custom_posts_sync['yaymail_template'] = 0;
        $sitepress->set_setting( 'custom_posts_sync_option', $custom_posts_sync, true );
    }
}
