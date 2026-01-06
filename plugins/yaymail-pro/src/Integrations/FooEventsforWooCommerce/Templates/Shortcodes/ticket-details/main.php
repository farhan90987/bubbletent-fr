<?php
/**
 * Template for FooEvents for WooCommerce.
 */

use YayMail\Utils\TemplateHelpers;
use YayMail\Utils\Helpers;

global $wpdb;

$data                    = isset( $args['element']['data'] ) ? $args['element']['data'] : [];
$template                = ! empty( $args['template'] ) ? $args['template'] : null;
$text_link_color         = ! empty( $template ) ? $template->get_text_link_color() : YAYMAIL_COLOR_WC_DEFAULT;
$render_data             = isset( $args['render_data'] ) ? $args['render_data'] : [];
$fooevents_order_tickets = isset( $args['fooevents_order_tickets'] ) ? $args['fooevents_order_tickets'] : [];
$order_data              = Helpers::get_order_from_shortcode_data( $render_data );

if ( empty( $order_data ) || empty( $fooevents_order_tickets ) ) {
    return null;
}

    $text_style = TemplateHelpers::get_style(
        [
            'font-size'   => '14px',
            'text-align'  => yaymail_get_text_align(),
            'font-family' => TemplateHelpers::get_font_family_value( isset( $data['font_family'] ) ? $data['font_family'] : 'inherit' ),
            'color'       => isset( $data['text_color'] ) ? $data['text_color'] : 'inherit',
            'line-height' => '150%',
        ]
    );

    $text_style_h3 = TemplateHelpers::get_style(
        [
            'text-align'  => yaymail_get_text_align(),
            'font-family' => TemplateHelpers::get_font_family_value( isset( $data['font_family'] ) ? $data['font_family'] : 'inherit' ),
            'color'       => $text_link_color,
            'font-size'   => '16px',
        ]
    );

    $text_style_a = TemplateHelpers::get_style(
        [
            'color'           => $text_link_color,
            'font-weight'     => 'normal',
            'text-decoration' => 'underline',
        ]
    );
    ?>
<div class="yaymail-fooevents-order-tickets" style="<?php echo esc_attr( $text_style ); ?>">
    <h3 style="<?php echo esc_attr( $text_style_h3 ); ?>"><?php esc_html_e( 'Ticket Details', 'woocommerce-events' ); ?></h3>
    <?php if ( ! empty( $fooevents_order_tickets ) ) : ?>
        <?php
        $x = 0;
        foreach ( $fooevents_order_tickets as $event ) :
            foreach ( $event['tickets'] as $ticket ) :
                $woo_events_event_details_new_order    = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsEventDetailsNewOrder', true );
                $woo_events_display_attendee_new_order = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsDisplayAttendeeNewOrder', true );
                $woo_events_display_bookings_new_order = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsDisplayBookingsNewOrder', true );
                $woo_events_display_seatings_new_order = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsDisplaySeatingsNewOrder', true );
                $woo_events_display_cust_att_new_order = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsDisplayCustAttNewOrder', true );
                ?>
                <?php if ( 'on' === $woo_events_event_details_new_order ) : ?>
                    <?php if ( ! empty( $event['WooCommerceEventsName'] ) ) : ?>
            <strong><a style="<?php echo esc_attr( $text_style_a ); ?>" href="<?php echo esc_url( $event['WooCommerceEventsURL'] ); ?>"><?php echo esc_html( $event['WooCommerceEventsName'] ); ?></a></strong><br />
            <?php endif; ?>
                    <?php if ( 'single' === $event['WooCommerceEventsType'] ) : ?>
                        <?php if ( ! empty( $event['WooCommerceEventsDate'] ) ) : ?>
                    <strong><?php esc_html_e( 'Date', 'woocommerce-events' ); ?></strong>: <?php echo esc_html( $event['WooCommerceEventsDate'] ); ?><br />
                <?php endif; ?>
                        <?php if ( ! empty( $event['WooCommerceEventsStartTime'] ) ) : ?>
                    <strong><?php esc_html_e( 'Start time', 'woocommerce-events' ); ?></strong>: <?php echo esc_html( $event['WooCommerceEventsStartTime'] ); ?><br />
                <?php endif; ?>
                        <?php if ( ! empty( $event['WooCommerceEventsEndTime'] ) ) : ?>
                    <strong><?php esc_html_e( 'End time', 'woocommerce-events' ); ?></strong>: <?php echo esc_html( $event['WooCommerceEventsEndTime'] ); ?><br />
                <?php endif; ?>
            <?php endif; ?>
                    <?php if ( 'sequential' === $event['WooCommerceEventsType'] ) : ?>
                        <?php if ( ! empty( $event['WooCommerceEventsDate'] ) ) : ?>
                    <strong><?php esc_html_e( 'Date', 'woocommerce-events' ); ?></strong>: <?php echo esc_html( $event['WooCommerceEventsDate'] ); ?><br />
                <?php endif; ?>
                        <?php if ( ! empty( $event['WooCommerceEventsEndDate'] ) ) : ?>
                    <strong><?php esc_html_e( 'End date', 'woocommerce-events' ); ?></strong>: <?php echo esc_html( $event['WooCommerceEventsEndDate'] ); ?><br />
                <?php endif; ?>    
                        <?php if ( ! empty( $event['WooCommerceEventsStartTime'] ) ) : ?>
                    <strong><?php esc_html_e( 'Start time', 'woocommerce-events' ); ?></strong>: <?php echo esc_html( $event['WooCommerceEventsStartTime'] ); ?><br />
                <?php endif; ?>
                        <?php if ( ! empty( $event['WooCommerceEventsEndTime'] ) ) : ?>
                    <strong><?php esc_html_e( 'End time', 'woocommerce-events' ); ?></strong>: <?php echo esc_html( $event['WooCommerceEventsEndTime'] ); ?><br />
                <?php endif; ?>
            <?php endif; ?>  
                    <?php if ( 'select' === $event['WooCommerceEventsType'] ) : ?>
                        <?php $y = 1; ?>    
                        <?php if ( ! empty( $event['WooCommerceEventsSelectDate'] ) ) : ?>
                            <?php foreach ( $event['WooCommerceEventsSelectDate'] as $date ) : ?>
                    <strong><?php esc_html_e( 'Day ', 'woocommerce-events' ); ?><?php echo esc_html( $y ); ?></strong>: <?php echo esc_html( $date ); ?><br />
                                <?php ++$y; ?>
                    <?php endforeach; ?>
                            <?php if ( ! empty( $event['WooCommerceEventsStartTime'] ) ) : ?>
                        <strong><?php esc_html_e( 'Start time', 'woocommerce-events' ); ?></strong>: <?php echo esc_html( $event['WooCommerceEventsStartTime'] ); ?><br />
                    <?php endif; ?>
                            <?php if ( ! empty( $event['WooCommerceEventsEndTime'] ) ) : ?>
                        <strong><?php esc_html_e( 'End time', 'woocommerce-events' ); ?></strong>: <?php echo esc_html( $event['WooCommerceEventsEndTime'] ); ?><br />
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>        
        <?php endif; ?>
                <?php if ( 'on' === $woo_events_display_attendee_new_order ) : ?>
            <strong><?php esc_html_e( 'Name', 'woocommerce-events' ); ?></strong>: <?php echo empty( $ticket['WooCommerceEventsAttendeeName'] ) ? esc_html( $ticket['WooCommerceEventsPurchaserFirstName'] ) . ' ' . esc_html( $ticket['WooCommerceEventsPurchaserLastName'] ) : esc_html( $ticket['WooCommerceEventsAttendeeName'] ) . ' ' . esc_html( $ticket['WooCommerceEventsAttendeeLastName'] ); ?><br />
            <strong><?php esc_html_e( 'Email', 'woocommerce-events' ); ?></strong>: <?php echo empty( $ticket['WooCommerceEventsAttendeeEmail'] ) ? esc_html( $ticket['WooCommerceEventsPurchaserEmail'] ) : esc_html( $ticket['WooCommerceEventsAttendeeEmail'] ); ?><br />
                    <?php if ( ! empty( $ticket['WooCommerceEventsAttendeeTelephone'] ) ) : ?>
                <strong><?php esc_html_e( 'Telephone', 'woocommerce-events' ); ?></strong>: <?php echo esc_html( $ticket['WooCommerceEventsAttendeeTelephone'] ); ?><br />
            <?php endif; ?>
                    <?php if ( ! empty( $ticket['WooCommerceEventsAttendeeCompany'] ) ) : ?>
                <strong><?php esc_html_e( 'Company', 'woocommerce-events' ); ?></strong>: <?php echo esc_html( $ticket['WooCommerceEventsAttendeeCompany'] ); ?><br />
            <?php endif; ?>
                    <?php if ( ! empty( $ticket['WooCommerceEventsAttendeeDesignation'] ) ) : ?>
                <strong><?php esc_html_e( 'Designation', 'woocommerce-events' ); ?></strong>: <?php echo esc_html( $ticket['WooCommerceEventsAttendeeDesignation'] ); ?><br />
            <?php endif; ?>
        <?php endif; ?>    
                <?php if ( 'on' === $woo_events_display_bookings_new_order && ! empty( $ticket['WooCommerceEventsBookingOptions']['slot'] ) ) : ?>
                <strong>
                    <?php
                    /* translators:  booking options*/
                    printf( esc_html__( 'Booking %s', 'woocommerce-events' ), esc_html( $ticket['WooCommerceEventsBookingOptions']['slot_term'] ) );
                    ?>
                    </strong>: <?php echo esc_html( $ticket['WooCommerceEventsBookingOptions']['slot'] ); ?><br />
        <strong>
                    <?php
                    /* translators:  booking options*/
                    printf( esc_html__( 'Booking %s', 'woocommerce-events' ), esc_html( $ticket['WooCommerceEventsBookingOptions']['date_term'] ) );
                    ?>
        </strong>: <?php echo esc_html( $ticket['WooCommerceEventsBookingOptions']['date'] ); ?><br />
        <?php endif; ?>
                <?php if ( 'on' === $woo_events_display_seatings_new_order && ! empty( $ticket['WooCommerceEventsSeatingFields'] ) ) : ?>
                    <?php $woo_events_seating_fields_keys = array_keys( $ticket['WooCommerceEventsSeatingFields'] ); ?>
            <strong><?php esc_html_e( 'Row', 'woocommerce-events' ); ?></strong>: <?php echo esc_html( $ticket['WooCommerceEventsSeatingFields'][ $woo_events_seating_fields_keys[0] ] ); ?> <br />
            <strong><?php esc_html_e( 'Seat', 'woocommerce-events' ); ?></strong>: <?php echo esc_html( $ticket['WooCommerceEventsSeatingFields'][ $woo_events_seating_fields_keys[1] ] ); ?><br />
        <?php endif; ?>
                <?php if ( 'on' === $woo_events_display_cust_att_new_order && ! empty( $ticket['WooCommerceEventsCustomAttendeeFields'] ) ) : ?>
                    <?php foreach ( $ticket['WooCommerceEventsCustomAttendeeFields'] as $key => $field ) : ?>
                <strong><?php echo esc_html( $field['field'][ $key . '_label' ] ); ?>:</strong> <?php echo esc_html( $field['value'] ); ?><br />    
            <?php endforeach; ?>
        <?php endif; ?>    
        <br />
                <?php ++$x; ?>   
            <?php endforeach; ?>
            <?php endforeach; ?>
    <?php endif; ?>
</div>
