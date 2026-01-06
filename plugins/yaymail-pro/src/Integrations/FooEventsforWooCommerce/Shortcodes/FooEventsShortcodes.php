<?php

namespace YayMail\Integrations\FooEventsforWooCommerce\Shortcodes;

use YayMail\Abstracts\BaseShortcode;
use YayMail\Utils\SingletonTrait;

/**
 * FooEventsShortcodes
 * * @method static FooEventsShortcodes get_instance()
 */
class FooEventsShortcodes extends BaseShortcode {
    use SingletonTrait;

    public function get_shortcodes() {
        $shortcodes = [];

        $shortcodes[] = [
            'name'        => 'yaymail_fooevents_ticket_details',
            'description' => __( 'FooEvents Ticket Details', 'yaymail' ),
            'group'       => 'fooevents',
            'callback'    => [ $this, 'yaymail_fooevents_ticket_details' ],
        ];

        return $shortcodes;
    }

    public function yaymail_fooevents_ticket_details( $args ) {
        $render_data             = isset( $args['render_data'] ) ? $args['render_data'] : [];
        $is_sample               = isset( $render_data['is_sample'] ) ? $render_data['is_sample'] : false;
        $is_customized_preview   = isset( $render_data['is_customized_preview'] ) ? $render_data['is_customized_preview'] : false;
        $template                = ! empty( $args['template'] ) ? $args['template'] : null;
        $args['text_link_color'] = ! empty( $template ) ? $template->get_text_link_color() : YAYMAIL_COLOR_WC_DEFAULT;

        $path_to_shortcodes_template = 'src/Integrations/FooEventsforWooCommerce/Templates/Shortcodes/ticket-details';

        if ( $is_sample ) {
            /**
             * Is sample data
             */
            $html = yaymail_get_content( $path_to_shortcodes_template . '/sample.php', $args );
            return $html;
        }

        $order = '';

        if ( isset( $render_data['order'] ) ) {
            $order = $render_data['order'];
        }

        if ( empty( $order ) && $is_customized_preview ) {
            return '';
        }

        $fooevents_order_tickets = get_post_meta( $order->get_id(), 'WooCommerceEventsOrderTickets', true );
        $fooevents_order_tickets = $this->process_order_tickets_for_display( $fooevents_order_tickets );

        $args['fooevents_order_tickets'] = $fooevents_order_tickets;

        $html = yaymail_get_content( $path_to_shortcodes_template . '/main.php', $args );
        return $html;
    }

    /**
     * Process order tickets for display
     *
     * @param array $woocommerce_events_order_tickets
     * @return array
     */
    public function process_order_tickets_for_display( $fooevents_order_tickets ) {

        if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
            require_once ABSPATH . '/wp-admin/includes/plugin.php';
        }

        $config = new \FooEvents_Config();
        require_once $config->class_path . 'class-fooevents-zoom-api-helper.php';
        $zoom_api_helper = new \FooEvents_Zoom_API_Helper( $config );

        $processed_event_tickets = [];

        foreach ( $fooevents_order_tickets as $event_tickets ) {

            $x = 0;
            foreach ( $event_tickets as $ticket ) {

                $event = get_post( $ticket['WooCommerceEventsProductID'] );

                if ( empty( $processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ] ) ) {

                    $processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ] = [];

                    $processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsProductID'] = $ticket['WooCommerceEventsProductID'];
                    $processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsName']      = $event->post_title;
                    $processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsURL']       = get_permalink( $event->ID );
                    $processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsType']      = get_post_meta( $event->ID, 'WooCommerceEventsType', true );

                    $processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsDate']      = get_post_meta( $event->ID, 'WooCommerceEventsDate', true );
                    $processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsStartTime'] = get_post_meta( $event->ID, 'WooCommerceEventsHour', true ) . ':' . get_post_meta( $event->ID, 'WooCommerceEventsMinutes', true ) . ' ' . get_post_meta( $event->ID, 'WooCommerceEventsPeriod', true );
                    $processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsEndTime']   = get_post_meta( $event->ID, 'WooCommerceEventsHourEnd', true ) . ':' . get_post_meta( $event->ID, 'WooCommerceEventsMinutesEnd', true ) . ' ' . get_post_meta( $event->ID, 'WooCommerceEventsEndPeriod', true );

                    $processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsLocation']       = get_post_meta( $event->ID, 'WooCommerceEventsLocation', true );
                    $processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsGPS']            = get_post_meta( $event->ID, 'WooCommerceEventsGPS', true );
                    $processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsSupportContact'] = get_post_meta( $event->ID, 'WooCommerceEventsSupportContact', true );
                    $processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsEmail']          = get_post_meta( $event->ID, 'WooCommerceEventsEmail', true );

                    if ( empty( $ticket['WooCommerceEventsBookingOptions'] ) ) {

                        $processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsZoomText'] = $zoom_api_helper->get_ticket_text( [ 'WooCommerceEventsProductID' => $event->ID ], 'admin' );

                    }
                }//end if

                if ( ! empty( $ticket['WooCommerceEventsVariations'] ) ) {

                    $ticket_vars = [];
                    foreach ( $ticket['WooCommerceEventsVariations'] as $variation_name => $variation_value ) {

                        $variation_name_output = str_replace( 'attribute_', '', $variation_name );
                        $variation_name_output = str_replace( 'pa_', '', $variation_name_output );
                        $variation_name_output = str_replace( '_', ' ', $variation_name_output );
                        $variation_name_output = str_replace( '-', ' ', $variation_name_output );
                        $variation_name_output = str_replace( 'Pa_', '', $variation_name_output );
                        $variation_name_output = ucwords( $variation_name_output );

                        $variation_value_output = str_replace( '_', ' ', $variation_value );
                        $variation_value_output = str_replace( '-', ' ', $variation_value_output );
                        $variation_value_output = ucwords( $variation_value_output );

                        $ticket_vars[ $variation_name_output ] = $variation_value_output;

                    }

                    $ticket['WooCommerceEventsVariations'] = $ticket_vars;

                }//end if

                if ( ! empty( $ticket['WooCommerceEventsCustomAttendeeFields'] ) ) {

                    if ( is_plugin_active( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) || is_plugin_active_for_network( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) ) {

                        $fooevents_custom_attendee_fields = new \Fooevents_Custom_Attendee_Fields();
                        $ticket_cust                      = $fooevents_custom_attendee_fields->fetch_attendee_details_for_order( $ticket['WooCommerceEventsProductID'], $ticket['WooCommerceEventsCustomAttendeeFields'] );

                    }

                    $ticket['WooCommerceEventsCustomAttendeeFields'] = $ticket_cust;

                }

                $processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['tickets'][ $x ] = $ticket;

                if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

                        $fooevents_bookings = new \Fooevents_Bookings();

                    if ( ! empty( $ticket['WooCommerceEventsBookingOptions'] ) ) {

                        $woocommerce_events_booking_fields = $fooevents_bookings->process_capture_booking( $ticket['WooCommerceEventsProductID'], $ticket['WooCommerceEventsBookingOptions'], '' );

                    }

                    $bookings_date_term = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsBookingsDateOverride', true );
                    $bookings_slot_term = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsBookingsSlotOverride', true );

                    $slot_label = '';
                    if ( empty( $bookings_slot_term ) ) {

                        $slot_label = __( 'Slot', 'fooevents-bookings' );

                    } else {

                        $slot_label = $bookings_slot_term;

                    }

                    $date_label = '';
                    if ( empty( $bookings_date_term ) ) {

                        $date_label = __( 'Date', 'fooevents-bookings' );

                    } else {

                        $date_label = $bookings_date_term;

                    }

                    if ( ! empty( $woocommerce_events_booking_fields ) ) {

                        $processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['tickets'][ $x ]['WooCommerceEventsBookingOptions']['slot']      = $woocommerce_events_booking_fields['slot'];
                        $processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['tickets'][ $x ]['WooCommerceEventsBookingOptions']['slot_term'] = $slot_label;
                        $processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['tickets'][ $x ]['WooCommerceEventsBookingOptions']['date']      = $woocommerce_events_booking_fields['date'];
                        $processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['tickets'][ $x ]['WooCommerceEventsBookingOptions']['date_term'] = $date_label;

                        $ticket_text_options = array_merge( [ 'WooCommerceEventsProductID' => $event->ID ], $woocommerce_events_booking_fields );

                        $processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['tickets'][ $x ]['WooCommerceEventsBookingOptions']['WooCommerceEventsZoomText'] = $zoom_api_helper->get_ticket_text( $ticket_text_options, 'admin' );

                    }
                }//end if

                if ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) {

                    $processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsEndDate']    = get_post_meta( $event->ID, 'WooCommerceEventsEndDate', true );
                    $processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsSelectDate'] = get_post_meta( $event->ID, 'WooCommerceEventsSelectDate', true );

                }

                ++$x;

            }//end foreach
        }//end foreach

        return $processed_event_tickets;
    }
}
