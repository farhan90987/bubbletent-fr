<?php
namespace YayMail\Abstracts;

/**
 * BaseTranslationsIntegration Class
 */
abstract class BaseTranslationsIntegration {

    public $id = '';

    public $title = '';

    public $active_language = null;

    public $languages = [];

    protected function __construct() {

        $this->languages       = $this->get_available_languages();
        $this->active_language = $this->get_active_language();

        add_filter(
            'yaymail_translate_integrations',
            function( $integrations ) {
                $integrations[ $this->id ] = $this;
                return $integrations;
            }
        );
    }

    abstract public function get_available_languages();

    abstract public function get_order_language( $order );

    public function get_active_language() {
        $languages       = $this->languages;
        $cookie_language = ! empty( $_COOKIE['yaymail_active_language'] ) ? sanitize_text_field( $_COOKIE['yaymail_active_language'] ) : 'en';
        if ( empty( $languages ) ) {
            return 'en';
        }

        $language_codes = array_column( $languages, null, 'code' );

        if ( empty( $language_codes[ $cookie_language ] ) ) {
            return $languages[0]['code'];
        }

        return $language_codes[ $cookie_language ]['code'];
    }

    public function get_data() {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'languages'       => $this->languages,
            'active_language' => $this->active_language,
        ];
    }
}
