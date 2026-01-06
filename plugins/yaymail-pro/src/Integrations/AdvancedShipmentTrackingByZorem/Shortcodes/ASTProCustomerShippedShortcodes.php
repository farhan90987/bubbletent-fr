<?php

namespace YayMail\Integrations\AdvancedShipmentTrackingByZorem\Shortcodes;

use YayMail\Abstracts\BaseShortcode;
use YayMail\Utils\Helpers;
use YayMail\Utils\SingletonTrait;

/**
 * ASTProCustomerShippedShortcodes
 */
class ASTProCustomerShippedShortcodes extends BaseShortcode {

    protected $path_to_shortcodes_content = 'src/Integrations/AdvancedShipmentTrackingByZorem/Templates/Shortcodes/';

    use SingletonTrait;

    protected function get_ast_instance() {
        if ( class_exists( 'Ast_Pro' ) ) {
            return \AST_Pro_Actions::get_instance();
        }
        return null;
    }

    public function get_shortcodes() {
        $shortcodes   = [];
        $shortcodes[] = [
            'name'        => 'yaymail_ast_order_details',
            'description' => __( 'AST Order Details', 'yaymail' ),
            'group'       => 'order_details',
            'callback'    => [ $this, 'yaymail_ast_order_details' ],
        ];

        return $shortcodes;
    }

    public function yaymail_ast_order_details( $data ) {
        $ast = $this->get_ast_instance();

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        $element = isset( $data['element'] ) ? $data['element'] : [];

        $is_placeholder = isset( $data['is_placeholder'] ) ? $data['is_placeholder'] : false;

        $args = [
            'element'        => $element,
            'is_placeholder' => $is_placeholder,
            'ast'            => $ast,
        ];

        if ( ! empty( $render_data['is_sample'] ) ) {
            $html = yaymail_get_content( $this->path_to_shortcodes_content . 'ast-pro-order-details/sample.php', $args );
            return $html;
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) ) {
            return __( 'No order found', 'yaymail' );
        }

        $args['order'] = $order;

        $html = yaymail_get_content( $this->path_to_shortcodes_content . 'ast-pro-order-details/main.php', $args );

        return $html;
    }
}
