<?php

namespace YayMail\Integrations\WcAdminCustomOrderFieldBySkyverge\Shortcodes;

use YayMail\Utils\Helpers;
use YayMail\Utils\SingletonTrait;
use YayMail\Abstracts\BaseShortcode;

/**
 * AdminCustomOrderFieldsShortcodes
 * * @method static AdminCustomOrderFieldsShortcodes get_instance()
 */
class AdminCustomOrderFieldsShortcodes extends BaseShortcode {
    use SingletonTrait;

    protected $third_party_instance = null;

    protected function __construct() {
        if ( class_exists( 'WC_Admin_Custom_Order_Fields' ) ) {
            $this->third_party_instance = \WC_Admin_Custom_Order_Fields::instance();
        }
        parent::__construct();
    }

    public function get_shortcodes() {

        if ( empty( $this->third_party_instance ) ) {
            return [];
        }

        $shortcodes = [];

        /**
         * @var \WC_Custom_Order_Field[] all the custom order fields that have been created
         */
        $custom_order_fields = $this->third_party_instance->get_order_fields();

        // Define shortcodes for all custom order fields
        foreach ( $custom_order_fields as $order_field ) {
            $label = method_exists( $order_field, '__get' ) ? $order_field->__get( 'label' ) : '';
            if ( empty( $label ) ) {
                continue;
            }
            $name        = Helpers::to_snake_case( $label );
            $description = $order_field->__get( 'description' );
            if ( empty( $description ) ) {
                $description = __( "SkyVerge's WoocCommerce Admin Custom Order Field: ", 'yaymail' ) . $label;
            }

            $shortcodes[] = [
                'name'          => "yaymail_order_skyverge_custom_order_field_{$name}",
                'description'   => $description,
                'group'         => 'admin_custom_order_fields',
                'callback'      => [ $this, 'get_admin_custom_order_field' ],
                'callback_args' => [
                    'custom_order_field_instance' => $order_field,
                ],
            ];
        }

        // Define a general shortcode to display all the custom fields
        $shortcodes[] = [
            'name'        => 'yaymail_order_skyverge_custom_order_fields',
            'description' => __( 'WooCommerce Admin Custom Order Field by SkyVerge', 'yaymail' ),
            'group'       => 'admin_custom_order_fields',
            'callback'    => [ $this, 'get_all_admin_custom_order_fields' ],
        ];

        return $shortcodes;
    }

    public function get_admin_custom_order_field( $data ) {
        /**
         * @var \WC_Custom_Order_Field the instance of third party custom order field (does not contain order data)
         */
        $custom_order_field_instance = isset( $data['custom_order_field_instance'] ) ? $data['custom_order_field_instance'] : '';
        $default_value               = $this->get_formatted_default_value( $custom_order_field_instance );

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is Sample Order
             */
            return $default_value;
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) ) {
            return $default_value;
        }

        $order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : '';

        /**
         * @var \WC_Custom_Order_Field[] array of all custom order fields belong to this current order
         */
        $with_order_data_fields = $this->third_party_instance->get_order_fields( $order_id );

        $field_id = method_exists( $custom_order_field_instance, 'get_id' ) ? $custom_order_field_instance->get_id() : '';

        $with_order_data_field = isset( $with_order_data_fields[ $field_id ] ) ? $with_order_data_fields[ $field_id ] : '';

        $result = method_exists( $with_order_data_field, 'get_value_formatted' ) ? $with_order_data_field->get_value_formatted() : '';

        $result = ! empty( $result ) ? $result : $default_value;

        return $result;
    }

    public function get_all_admin_custom_order_fields( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        $element = isset( $data['element'] ) ? $data['element'] : [];

        $is_placeholder = isset( $data['is_placeholder'] ) ? $data['is_placeholder'] : false;

        $template = ! empty( $data['template'] ) ? $data['template'] : null;

        $text_link_color = ! empty( $template ) ? $template->get_text_link_color() : YAYMAIL_COLOR_WC_DEFAULT;

        $path_to_shortcodes_template = 'src/Integrations/WcAdminCustomOrderFieldBySkyverge/Templates/Shortcodes/admin-custom-order-fields';

        $custom_order_fields = [];

        $args = [
            'text_link_color' => $text_link_color,
            'element'         => $element,
            'is_placeholder'  => $is_placeholder,
        ];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is Sample Order
             */

            $admin_custom_order_field_instances = $this->third_party_instance->get_order_fields();

            foreach ( $admin_custom_order_field_instances as $admin_custom_order_field_instance ) {
                $custom_order_fields[ $admin_custom_order_field_instance->__get( 'label' ) ] = $this->get_formatted_default_value( $admin_custom_order_field_instance );
            }

            $args['custom_order_fields'] = $custom_order_fields;

            $html = yaymail_get_content( $path_to_shortcodes_template . '/main.php', $args );
            return $html;
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) ) {
            /**
             * Not having order/order_id
             */
            return __( 'No order found', 'yaymail' );
        }

        $order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : '';

        $admin_custom_order_field_instances = $this->third_party_instance->get_order_fields( $order_id );

        foreach ( $admin_custom_order_field_instances as $admin_custom_order_field_instance ) {
            $value = $admin_custom_order_field_instance->get_value_formatted();

            if ( ! empty( $value ) ) {
                $custom_order_fields[ $admin_custom_order_field_instance->__get( 'label' ) ] = $value;
            }
        }

        $args['custom_order_fields'] = $custom_order_fields;

        $html = yaymail_get_content( $path_to_shortcodes_template . '/main.php', $args );

        return $html;
    }

    /**
     * Get the formatted default value
     *
     * Reference in 3rd-party plugin: WC_Custom_Order_Field get_value_formatted()
     *
     * @param \WC_Custom_Order_Field $custom_field_instance the instance of the custom field
     *
     * @return string the formatted default value
     *
     */
    private function get_formatted_default_value( $custom_field_instance ) {
        if ( ! ( isset( $custom_field_instance ) && $custom_field_instance instanceof \WC_Custom_Order_Field ) ) {
            return 'No data';
        }

        $type  = $custom_field_instance->get_type();
        $value = $custom_field_instance->get_default_value();

        switch ( $type ) {

            case 'date':
                $value_formatted = $value ? date_i18n( wc_date_format(), $value ) : '';
                break;

            case 'text':
            case 'textarea':
                $value_formatted = stripslashes( $value );
                break;

            case 'select':
            case 'multiselect':
            case 'checkbox':
            case 'radio':
                $options = $custom_field_instance->get_options();

                $value = [];

                foreach ( $options as $option ) {

                    if ( $option['selected'] ) {

                        $value[] = $option['label'];
                    }
                }

                $value_formatted = implode( ', ', $value );

                break;

            default:
                $value_formatted = $value;
                break;
        }

        return $value_formatted;
    }
}
