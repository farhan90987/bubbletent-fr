<?php

defined( 'ABSPATH' ) || exit;

abstract class German_Market_Blocks_Methods {
	
	private static $instances = array();

	/**
	* Singletone get_instance
	*
	* @final
	* @static
	* @return static() (child class)
	*/
	final public static function get_instance() {

		if ( ! isset( self::$instances[ static::class ] ) ) {
			self::$instances[ static::class ] = new static();	
		}

		return self::$instances[ static::class ];
	}

    /**
     * Construct
     *
     * @return void
     */
    final function __construct() {
        $this->init();
    }

    /**
     * Call your hooks and filters here
     *
     * @return void
     */
    abstract function init();

}
