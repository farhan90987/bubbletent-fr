<?php

namespace YayMail\Integrations\AdvancedShipmentTrackingByZorem\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Helpers Classes
 */
class Helpers {
    public static function get_tracking_info_settings() {
        $tracking_info_settings = get_option( 'tracking_info_settings', [] );

        return [
            'header' => [
                'hidden'          => ! empty( $tracking_info_settings['hide_trackig_header'] ) ? true : false,
                'text'            => ! empty( $tracking_info_settings['header_text_change'] ) ? $tracking_info_settings['header_text_change'] : __( 'Tracking information', 'yaymail' ),
                'additional_text' => ! empty( $tracking_info_settings['additional_header_text'] ) ? $tracking_info_settings['additional_header_text'] : '',
            ],
            'style'  => [
                'display_shipped_header' => ( ( isset( $tracking_info_settings['fluid_display_shipped_header'] ) ) && ( empty( $tracking_info_settings['fluid_display_shipped_header'] ) || 0 === $tracking_info_settings['fluid_display_shipped_header'] ) ) ? false : true,
                'tracker_type'           => ! empty( $tracking_info_settings['tracker_type'] ) ? $tracking_info_settings['tracker_type'] : 'progress_bar',
                'table_background_color' => ! empty( $tracking_info_settings['fluid_table_background_color'] ) ? $tracking_info_settings['fluid_table_background_color'] : '#fafafa',
                'table_border_color'     => ! empty( $tracking_info_settings['fluid_table_border_color'] ) ? $tracking_info_settings['fluid_table_border_color'] : '#e0e0e0',
                'table_border_radius'    => ! empty( $tracking_info_settings['fluid_table_border_radius'] ) ? $tracking_info_settings['fluid_table_border_radius'] : '3',
                'hide_provider_image'    => ! empty( $tracking_info_settings['fluid_hide_provider_image'] ) ? true : false,
            ],
            'button' => [
                'text'             => ! empty( $tracking_info_settings['fluid_button_text'] ) ? $tracking_info_settings['fluid_button_text'] : __( 'Track Your Order', 'yaymail' ),
                'size'             => ! empty( $tracking_info_settings['fluid_button_size'] ) ? $tracking_info_settings['fluid_button_size'] : 'normal',
                'background_color' => ! empty( $tracking_info_settings['fluid_button_background_color'] ) ? $tracking_info_settings['fluid_button_background_color'] : '#005b9a',
                'font_color'       => ! empty( $tracking_info_settings['fluid_button_font_color'] ) ? $tracking_info_settings['fluid_button_font_color'] : '#fff',
                'radius'           => ! empty( $tracking_info_settings['fluid_button_radius'] ) ? $tracking_info_settings['fluid_button_radius'] : '3',
            ],
        ];
    }
}
