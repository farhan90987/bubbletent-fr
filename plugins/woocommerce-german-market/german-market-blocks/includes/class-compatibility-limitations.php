<?php

use Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils;

defined( 'ABSPATH' ) || exit;

/**
 * This class implements notices for limitations of German Market when the checkout block is used
 * and it adds some information in the German Market menu
 */
class German_Market_Blocks_Compatibility_Limitations extends German_Market_Blocks_Methods {
	
    public static $limitations = array();

    /**
     * Call actions and filters
     *
     * @return void
     */
    public function init() {
        self::$limitations = self::get_activated_limitations();

        if ( ! empty( self::$limitations ) ) {
            add_action( 'admin_notices', array( $this, 'notice_about_limitations_in_gm_menu' ) );
        }
    }

    /**
     * Get an array with limitation notices
     *
     * @return Array
     */
    public static function get_activated_limitations() {
        $limitations = array();
        return $limitations;
    }

    /**
     * Show notices about block compatibility limitations
     *
     * @return void
     */
    public function notice_about_limitations_in_gm_menu() {
    }
}
