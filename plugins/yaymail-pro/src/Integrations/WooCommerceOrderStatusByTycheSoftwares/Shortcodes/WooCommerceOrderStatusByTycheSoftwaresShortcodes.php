<?php

namespace YayMail\Integrations\WooCommerceOrderStatusByTycheSoftwares\Shortcodes;

use YayMail\Abstracts\BaseShortcode;
use YayMail\Integrations\WooCommerceOrderStatusByTycheSoftwares\Emails\EmailsHandler;
use YayMail\Utils\SingletonTrait;
use YayMail\Utils\Helpers;
/**
 * WooCommerceOrderStatusByTycheSoftwaresShortcodes
 */
class WooCommerceOrderStatusByTycheSoftwaresShortcodes extends BaseShortcode {
    use SingletonTrait;

    public function __construct() {
        $this->available_email_ids = EmailsHandler::get_instance()->get_list_id();
        parent::__construct();
    }

    public function get_shortcodes() {

        $shortcodes = [];

        $shortcodes[] = [
            'name'        => 'yaymail_order_status',
            'description' => __( 'WooCommerce Order Status', 'yaymail' ),
            'group'       => 'WooCommerce Order Status',
            'callback'    => [ $this, 'yaymail_order_status' ],
        ];

        $shortcodes[] = [
            'name'        => 'yaymail_order_status_from',
            'description' => __( 'WooCommerce Order Status From', 'yaymail' ),
            'group'       => 'WooCommerce Order Status',
            'callback'    => [ $this, 'yaymail_order_status_from' ],
        ];

        return $shortcodes;
    }

    public function yaymail_order_status( $args ) {
        $render_data = isset( $args['render_data'] ) ? $args['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            return __( 'Order Status', 'yaymail' );
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) ) {
            return __( 'No order found', 'yaymail' );
        }

        return strtolower( wc_get_order_status_name( $order->get_status() ) );
    }

    public function yaymail_order_status_from( $args ) {
        $render_data = isset( $args['render_data'] ) ? $args['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            return __( 'Order Status From', 'yaymail' );
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) ) {
            return __( 'No order found', 'yaymail' );
        }

        $status_from = $render_data['status_from'] ?? $order->get_status();

        return strtolower( wc_get_order_status_name( $status_from ) );
    }
}
