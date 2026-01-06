<?php

namespace YayMail\Integrations\BackInStockNotifier\Shortcodes;

use YayMail\Abstracts\BaseShortcode;
use YayMail\Utils\SingletonTrait;

/**
 * BackInStockNotifierShortcodes
 * * @method static BackInStockNotifierShortcodes get_instance()
 */
class BackInStockNotifierShortcodes extends BaseShortcode {
    use SingletonTrait;

    protected function __construct() {
        $this->available_email_ids = [ 'notifier_instock_mail', 'notifier_subscribe_mail' ];
        parent::__construct();
    }

    public function get_shortcodes() {
        $shortcodes = [];

        $shortcodes[] = [
            'name'        => 'yaymail_notifier_product_id',
            'description' => __( 'Notifier Product ID', 'yaymail' ),
            'group'       => 'back_in_stock_notifier',
            'callback'    => [ $this, 'yaymail_notifier_product_id' ],
        ];
        $shortcodes[] = [
            'name'        => 'yaymail_notifier_product_name',
            'description' => __( 'Notifier Product Name', 'yaymail' ),
            'group'       => 'back_in_stock_notifier',
            'callback'    => [ $this, 'yaymail_notifier_product_name' ],
        ];
        $shortcodes[] = [
            'name'        => 'yaymail_notifier_product_sku',
            'description' => __( 'Notifier Product Sku', 'yaymail' ),
            'group'       => 'back_in_stock_notifier',
            'callback'    => [ $this, 'yaymail_notifier_product_sku' ],
        ];
        $shortcodes[] = [
            'name'        => 'yaymail_notifier_product_link',
            'description' => __( 'Notifier Product Link', 'yaymail' ),
            'group'       => 'back_in_stock_notifier',
            'callback'    => [ $this, 'yaymail_notifier_product_link' ],
        ];
        $shortcodes[] = [
            'name'        => 'yaymail_notifier_product_image',
            'description' => __( 'Notifier Product Image', 'yaymail' ),
            'group'       => 'back_in_stock_notifier',
            'callback'    => [ $this, 'yaymail_notifier_product_image' ],
        ];
        $shortcodes[] = [
            'name'        => 'yaymail_notifier_shopname',
            'description' => __( 'Notifier Shop Name', 'yaymail' ),
            'group'       => 'back_in_stock_notifier',
            'callback'    => [ $this, 'yaymail_notifier_shopname' ],
        ];
        $shortcodes[] = [
            'name'        => 'yaymail_notifier_cart_link',
            'description' => __( 'Notifier Cart Link', 'yaymail' ),
            'group'       => 'back_in_stock_notifier',
            'callback'    => [ $this, 'yaymail_notifier_cart_link' ],
        ];
        $shortcodes[] = [
            'name'        => 'yaymail_notifier_subscriber_email',
            'description' => __( 'Notifier Subscriber Email', 'yaymail' ),
            'group'       => 'back_in_stock_notifier',
            'callback'    => [ $this, 'yaymail_notifier_subscriber_email' ],
        ];
        $shortcodes[] = [
            'name'        => 'yaymail_notifier_subscriber_name',
            'description' => __( 'Notifier Subscriber Name', 'yaymail' ),
            'group'       => 'back_in_stock_notifier',
            'callback'    => [ $this, 'yaymail_notifier_subscriber_name' ],
        ];
        $shortcodes[] = [
            'name'        => 'yaymail_notifier_only_product_name',
            'description' => __( 'Notifier Only Product Name', 'yaymail' ),
            'group'       => 'back_in_stock_notifier',
            'callback'    => [ $this, 'yaymail_notifier_only_product_name' ],
        ];

        return $shortcodes;
    }

    public function yaymail_notifier_product_id( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return '1';
        }

        if ( empty( $render_data['subscriber_id'] ) ) {
            return '';
        }

        return get_post_meta( $render_data['subscriber_id'], 'cwginstock_pid', true );
    }

    public function yaymail_notifier_product_name( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return __( 'YayMail', 'yaymail' );
        }

        if ( empty( $render_data['subscriber_id'] ) ) {
            return '';
        }

        $cwg_instock_api = new \CWG_Instock_API();
        return $cwg_instock_api->display_product_name( $render_data['subscriber_id'] );
    }

    public function yaymail_notifier_product_sku( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return '1';
        }

        if ( empty( $render_data['subscriber_id'] ) ) {
            return '';
        }

        $cwg_instock_api = new \CWG_Instock_API();
        return $cwg_instock_api->get_product_sku( $render_data['subscriber_id'] );
    }

    public function yaymail_notifier_product_image( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return wc_placeholder_img();
        }

        if ( empty( $render_data['subscriber_id'] ) ) {
            return '';
        }

        $cwg_instock_api = new \CWG_Instock_API();
        return $cwg_instock_api->get_product_image( $render_data['subscriber_id'] );
    }

    public function yaymail_notifier_product_link( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return '<a href="' . esc_url( get_site_url() ) . '"> ' . esc_url( get_site_url() ) . ' </a>';
        }

        if ( empty( $render_data['subscriber_id'] ) ) {
            return '';
        }

        $cwg_instock_api = new \CWG_Instock_API();
        return $cwg_instock_api->display_product_link( $render_data['subscriber_id'] );
    }

    public function yaymail_notifier_shopname( $data ) {
        return esc_html( get_bloginfo( 'name' ) );
    }

    public function yaymail_notifier_cart_link( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return '<a href="' . esc_url( get_permalink( wc_get_page_id( 'cart' ) ) ) . '"> ' . esc_url( get_permalink( wc_get_page_id( 'cart' ) ) ) . ' </a>';
        }

        if ( empty( $render_data['subscriber_id'] ) ) {
            return '';
        }

        $product_id = get_post_meta( $render_data['subscriber_id'], 'cwginstock_pid', true );
        $cart_url   = esc_url_raw( add_query_arg( 'add-to-cart', $product_id, get_permalink( wc_get_page_id( 'cart' ) ) ) );

        return '<a href="' . esc_url( $cart_url ) . '"> ' . esc_url( $cart_url ) . ' </a>';
    }

    public function yaymail_notifier_subscriber_email( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return 'yaycommerce@sample.com';
        }

        if ( empty( $render_data['subscriber_id'] ) ) {
            return '';
        }

        return get_post_meta( $render_data['subscriber_id'], 'cwginstock_subscriber_email', true );
    }

    public function yaymail_notifier_subscriber_name( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return __( 'YayMail', 'yaymail' );
        }

        if ( empty( $render_data['subscriber_id'] ) ) {
            return '';
        }

        $cwg_instock_api = new \CWG_Instock_API();
        return $cwg_instock_api->get_subscriber_name( $render_data['subscriber_id'] );
    }

    public function yaymail_notifier_only_product_name( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return __( 'YayMail', 'yaymail' );
        }

        if ( empty( $render_data['subscriber_id'] ) ) {
            return '';
        }

        $cwg_instock_api = new \CWG_Instock_API();
        return $cwg_instock_api->display_only_product_name( $render_data['subscriber_id'] );
    }
}
