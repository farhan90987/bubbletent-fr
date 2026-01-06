<?php

namespace YayMail\Integrations;

use YayMail\Integrations\Translations\GTranslate;
use YayMail\Integrations\Translations\Loco;
use YayMail\Integrations\Translations\Polylang;
use YayMail\Integrations\Translations\TranslatePress;
use YayMail\Integrations\Translations\Weglot;
use YayMail\Integrations\Translations\WPML;
use YayMail\Utils\SingletonTrait;

/**
 * TranslationModule
 * * @method static TranslationModule get_instance()
 */
class TranslationModule {
    use SingletonTrait;

    public $integrations = [];

    public $available_integrations = [];

    public $current_integration = null;

    protected function __construct() {
        $this->load_classes();
        $this->available_integrations = apply_filters( 'yaymail_translate_integrations', [] );
        $this->current_integration    = $this->get_current_translate_integration();
    }

    public function load_classes() {
        WPML::get_instance();
        Loco::get_instance();
        Polylang::get_instance();
        TranslatePress::get_instance();
        GTranslate::get_instance();
        Weglot::get_instance();
    }

    public function get_current_translate_integration() {
        if ( count( $this->available_integrations ) > 0 ) {
            $priority_integration = get_option( 'yaymail_priority_translate', null );
            if ( null !== $priority_integration && in_array( $priority_integration, array_keys( $this->available_integrations ), true ) ) {
                return $this->available_integrations[ $priority_integration ];
            }
            return array_values( $this->available_integrations )[0];
        }
        return null;
    }

    public function get_active_language() {
        if ( ! is_null( $this->current_integration ) ) {
            return $this->current_integration->get_active_language();
        }
        return '';
    }

    public function get_order_language( $order ) {
        if ( ! is_null( $this->current_integration ) && count( $this->integrations ) > 0 ) {
            return $this->current_integration->get_order_language( $order );
        }
        return '';
    }

    public static function checked_language( $language ) {
        return 'en' === $language ? '' : $language;
    }

}
