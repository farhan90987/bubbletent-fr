<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $wpdb;
$table_name = $wpdb->prefix . 'dpd_orders_switzerland';
$order_id   = $order->get_id();
$parcels    = $wpdb->get_results( $wpdb->prepare( "SELECT parcel_number FROM {$wpdb->prefix}dpd_orders_switzerland WHERE order_id = %s AND status_label = 1", $order_id ) );
if ( count( $parcels ) ) {
	$parc_num                = $parcels[0]->parcel_number;
	$shipping_date           = $wpdb->get_row( $wpdb->prepare( "SELECT shipping_date FROM {$wpdb->prefix}dpd_orders_switzerland WHERE parcel_number = %s ORDER BY id DESC", $parc_num ) );
	$formatted_shiiping_date = gmdate( 'd.m.Y', absint( $shipping_date->shipping_date ) );
	// printf(
	// /* translators: %s: Formatted shipping date */
	// 	esc_html__( 'Die Bestellung wird am %s mit DPD verschickt. Hier der Link um die Sendung zu verfolgen: ', 'dpd-shipping-label-switzerland' ),
	// 	esc_html( $formatted_shiiping_date )
	// );
	foreach ( $parcels as $parcel ) {
		echo '<a href="https://www.dpdgroup.com/ch/mydpd/my-parcels/incoming?parcelNumber=' . esc_html( $parcel->parcel_number ) . '" target="_blank">' . esc_html( $parcel->parcel_number ) . '</a>';
		echo '<br /><br />';
	}
}
