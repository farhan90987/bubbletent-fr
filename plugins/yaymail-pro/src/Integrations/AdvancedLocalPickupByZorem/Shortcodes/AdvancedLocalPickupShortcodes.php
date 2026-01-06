<?php

namespace YayMail\Integrations\AdvancedLocalPickupByZorem\Shortcodes;

use YayMail\Abstracts\BaseShortcode;
use YayMail\Utils\Helpers;
use YayMail\Utils\SingletonTrait;

/**
 * AdvancedLocalPickupShortcodes
 * * @method static AdvancedLocalPickupShortcodes get_instance()
 */
class AdvancedLocalPickupShortcodes extends BaseShortcode {
    use SingletonTrait;

    private $local_pickup_admin_instance = null;

    public function __construct() {
        if ( class_exists( 'WC_Local_Pickup_Admin' ) ) {
            $this->local_pickup_admin_instance = \WC_Local_Pickup_Admin::get_instance();
        }
        parent::__construct();
    }

    public function get_shortcodes() {

        if ( empty( $this->local_pickup_admin_instance ) ) {
            return [];
        }

        $shortcodes = [];

        $shortcodes[] = [
            'name'        => 'yaymail_advanced_local_pickup_instruction',
            'description' => __( 'Advanced Local Pickup Instruction', 'yaymail' ),
            'group'       => 'advanced_local_pickup',
            'callback'    => [ $this, 'yaymail_advanced_local_pickup_instruction' ],
        ];

        return $shortcodes;
    }

    public function yaymail_advanced_local_pickup_instruction( $data ) {
        $local_pickup_admin = $this->local_pickup_admin_instance;
        $pick_up_data       = $local_pickup_admin->get_data();
        $location_id        = get_option( 'location_defualt', min( $pick_up_data )->id );
        $location           = $local_pickup_admin->get_data_byid( $location_id );
        $country_code       = isset( $location ) ? $location->store_country : get_option( 'woocommerce_default_country' );

        $split_country = explode( ':', $country_code );
        $store_country = isset( $split_country[0] ) ? $split_country[0] : '';
        $store_state   = isset( $split_country[1] ) ? $split_country[1] : '';

        $store_days = isset( $location->store_days ) ? maybe_unserialize( $location->store_days ) : [];
        $all_days   = [
            'sunday'    => esc_html__( 'Sunday', 'advanced-local-pickup-for-woocommerce' ),
            'monday'    => esc_html__( 'Monday', 'advanced-local-pickup-for-woocommerce' ),
            'tuesday'   => esc_html__( 'Tuesday', 'advanced-local-pickup-for-woocommerce' ),
            'wednesday' => esc_html__( 'Wednesday', 'advanced-local-pickup-for-woocommerce' ),
            'thursday'  => esc_html__( 'Thursday', 'advanced-local-pickup-for-woocommerce' ),
            'friday'    => esc_html__( 'Friday', 'advanced-local-pickup-for-woocommerce' ),
            'saturday'  => esc_html__( 'Saturday', 'advanced-local-pickup-for-woocommerce' ),
        ];

        $w_day = array_slice( $all_days, get_option( 'start_of_week' ) );

        foreach ( $all_days as $key => $val ) {
            $w_day[ $key ] = $val;
        }
        foreach ( $store_days as $key => $val ) {
            if ( $w_day[ $key ] ) {
                $w_day[ $key ] = $val;
            }
        }

        $wclp_default_time_format = isset( $location ) ? $location->store_time_format : '24';
        if ( '12' === $wclp_default_time_format ) {
            foreach ( $w_day as $key => $val ) {
                if ( isset( $val['wclp_store_hour'] ) ) {
                    $last_digit = explode( ':', $val['wclp_store_hour'] );
                    if ( '00' == end( $last_digit ) ) {
                        $val['wclp_store_hour'] = gmdate( 'g:ia', strtotime( $val['wclp_store_hour'] ) );
                    } else {
                        $val['wclp_store_hour'] = gmdate( 'g:ia', strtotime( $val['wclp_store_hour'] ) );
                    }
                }
                if ( isset( $val['wclp_store_hour_end'] ) ) {
                    $last_digit = explode( ':', $val['wclp_store_hour_end'] );
                    if ( '00' == end( $last_digit ) ) {
                        $val['wclp_store_hour_end'] = gmdate( 'g:ia', strtotime( $val['wclp_store_hour_end'] ) );
                    } else {
                        $val['wclp_store_hour_end'] = gmdate( 'g:ia', strtotime( $val['wclp_store_hour_end'] ) );
                    }
                }
                $w_day[ $key ] = $val;
            }
        }//end if

        $element = isset( $data['element'] ) ? $data['element'] : [];

        $text_link_color = ! empty( $template ) ? $template->get_text_link_color() : YAYMAIL_COLOR_WC_DEFAULT;

        $is_placeholder = isset( $data['is_placeholder'] ) ? $data['is_placeholder'] : false;

        $args = [
            'element'         => $element,
            'text_link_color' => $text_link_color,
            'is_placeholder'  => $is_placeholder,
            'w_day'           => $w_day,
            'location'        => $location,
            'store_country'   => $store_country,
            'store_state'     => $store_state,
        ];

        $path_to_shortcodes = 'src/Integrations/AdvancedLocalPickupByZorem/Templates/Shortcodes/advanced-local-pickup-instruction/main.php';

        $html = yaymail_get_content( $path_to_shortcodes, $args );

        return $html;
    }
}
