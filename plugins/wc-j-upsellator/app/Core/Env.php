<?php

namespace WcJUpsellator\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Env
{

    private static $_configuration = array();

    public static function set( $key, $value )
    {
            self::$_configuration[ $key ] = $value;
    }

    public static function get( $key ){

            if( isset( self::$_configuration[ $key ] ) ) return self::$_configuration[ $key ];
            return null;

    }

    public static function clear()
    {
            self::$_configuration = [];
    }
 

}
