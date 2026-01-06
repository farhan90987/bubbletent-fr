<?php

if( !function_exists( 'icl_get_languages' ) ) {
    function icl_get_languages( $params ) {
        return [
            'en' => [
                'id'               => 1,
                'language_code'    => 'en',
                'native_name'      => 'English',
                'translated_name'  => 'English',
                'url'              => '/en/',
                'active'           => false,
                'country_flag_url' => '/wp-content/plugins/sitepress-multilingual-cms/res/flags/en.png',
            ],
            'fr' => [
                'id'               => 2,
                'language_code'    => 'fr',
                'native_name'      => 'Français',
                'translated_name'  => 'French',
                'url'              => '/fr/',
                'active'           => false,
                'country_flag_url' => '/wp-content/plugins/sitepress-multilingual-cms/res/flags/fr.png',
            ],
            'uk' => [
                'id'               => 3,
                'language_code'    => 'uk',
                'native_name'      => 'Українська',
                'translated_name'  => 'Ukrainian',
                'url'              => '/uk/',
                'active'           => true,
                'country_flag_url' => '/wp-content/plugins/sitepress-multilingual-cms/res/flags/uk.png',
            ],
        ];
    }
}

if( !defined( 'ICL_LANGUAGE_CODE' ) ) {
    define( 'ICL_LANGUAGE_CODE', 'en' );
}