<?php

namespace YayMail\Integrations\AdvancedShipmentTrackingByZorem\Shortcodes;

use YayMail\Utils\SingletonTrait;

/**
 * AstProTrackingInformation
 * @method static AstProTrackingInformation get_instance()
 */

class AstProTrackingInformation extends TrackingInformationShortcodes {
    use SingletonTrait;

    protected function get_ast_instance() {
        if ( class_exists( 'Ast_Pro' ) ) {
            return \AST_Pro_Actions::get_instance();
        }
        return null;
    }

    protected function get_content( $args ) {
        $tpi_order = ast_pro()->ast_tpi->check_if_tpi_order( $args['tracking_items'], $args['order'] );

        $path = $tpi_order ? 'tracking-information-pro-tpi/main.php' : 'tracking-information-pro/main.php';

        return yaymail_get_content( $this->get_path_to_shortcodes_template() . $path, $args );
    }
}
