<?php

namespace WcJUpsellator\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Conf
{

    private static $_configuration = array();

    public static function set( $type, $key, $value )
    {
            self::$_configuration[ $type ][ $key ] = $value;
    }

    public static function get( $type, $key ){

            if( isset( self::$_configuration[ $type ][ $key ] ) ) return self::$_configuration[ $type ][ $key ];
            return null;

    }

    public static function clear( $type )
    {
            if( isset( self::$_configuration[ $type ] ) ) self::$_configuration[ $type ] = [];
    }


}
