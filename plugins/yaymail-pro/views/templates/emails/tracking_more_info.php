<?php
$trackingmore_fields = array(
	'trackingmore_tracking_provider_name'       => array(
		'id'          => 'trackingmore_tracking_provider_name',
		'type'        => 'text',
		'label'       => '',
		'placeholder' => '',
		'description' => '',
		'class'       => 'hidden',
	),

	'trackingmore_tracking_required_fields'     => array(
		'id'          => 'trackingmore_tracking_required_fields',
		'type'        => 'text',
		'label'       => '',
		'placeholder' => '',
		'description' => '',
		'class'       => 'hidden',
	),

	'trackingmore_tracking_number'              => array(
		'id'          => 'trackingmore_tracking_number',
		'type'        => 'text',
		'label'       => __( 'Tracking number:', 'trackingmore' ),
		'placeholder' => '',
		'description' => '',
		'class'       => '',
	),

	'trackingmore_tracking_shipdate'            => array(
		'key'         => 'tracking_ship_date',
		'id'          => 'trackingmore_tracking_shipdate',
		'type'        => 'date',
		'label'       => 'Date shipped',
		'placeholder' => 'YYYY-MM-DD',
		'description' => '',
		'class'       => 'date-picker-field hidden-field',
	),

	'trackingmore_tracking_postal'              => array(
		'key'         => 'tracking_postal_code',
		'id'          => 'trackingmore_tracking_postal',
		'type'        => 'text',
		'label'       => 'Postal Code',
		'placeholder' => '',
		'description' => '',
		'class'       => 'hidden-field',
	),

	'trackingmore_tracking_account'             => array(
		'key'         => 'tracking_account_number',
		'id'          => 'trackingmore_tracking_account',
		'type'        => 'text',
		'label'       => 'Account name',
		'placeholder' => '',
		'description' => '',
		'class'       => 'hidden-field',
	),

	'trackingmore_tracking_key'                 => array(
		'key'         => 'tracking_key',
		'id'          => 'trackingmore_tracking_key',
		'type'        => 'text',
		'label'       => 'Tracking key',
		'placeholder' => '',
		'description' => '',
		'class'       => 'hidden-field',
	),

	'trackingmore_tracking_destination_country' => array(
		'key'         => 'tracking_destination_country',
		'id'          => 'trackingmore_tracking_destination_country',
		'type'        => 'text',
		'label'       => 'Destination Country',
		'placeholder' => '',
		'description' => '',
		'class'       => 'hidden-field',
	),
);
$values = array();
foreach ( $trackingmore_fields as $field ) {
	$values[ $field['id'] ] = get_post_meta( $order_id, '_' . $field['id'], true );
	if ( 'date' === $field['type'] && $values[ $field['id'] ] ) {
		$values[ $field['id'] ] = date_i18n( __( 'l jS F Y', 'wc_shipment_tracking' ), $values[ $field['id'] ] );
	}
}
$values['trackingmore_tracking_provider'] = get_post_meta( $order_id, '_trackingmore_tracking_provider', true );

if ( ! $values['trackingmore_tracking_provider'] ) {
	return;
}

if ( ! $values['trackingmore_tracking_number'] ) {
	return;
}


$options = get_option( 'trackingmore_option_name' );

if ( array_key_exists( 'track_message_1', $options ) && array_key_exists( 'track_message_2', $options ) ) {
	$track_message_1 = $options['track_message_1'];
	$track_message_2 = $options['track_message_2'];
} else {
	$track_message_1 = __( 'Your order was shipped via ', 'trackingmore' );
	$track_message_2 = __( 'Tracking number is ', 'trackingmore' );
}

$required_fields_values         = array();
$provider_required_fields       = explode( ',', $values['trackingmore_tracking_required_fields'] );
$count_provider_required_fields = count( $provider_required_fields );
for ( $i = 0; $i < $count_provider_required_fields; $i++ ) {
	$field = $provider_required_fields[ $i ];
	foreach ( $trackingmore_fields as $trackingmore_field ) {
		if ( array_key_exists( 'key', $trackingmore_field ) && $field == $trackingmore_field['key'] ) {
			array_unshift( $required_fields_values, $values[ $trackingmore_field['id'] ] );
		}
	}
}

if ( count( $required_fields_values ) ) {
	$required_fields_msg = ' (' . join( ', ', $required_fields_values ) . ')';
} else {
	$required_fields_msg = '';
}


echo wp_kses_post( '<span>' . $track_message_1 . $values['trackingmore_tracking_provider_name'] . '</span><br/><span>' . $track_message_2 . $values['trackingmore_tracking_number'] . '</span>' . $required_fields_msg );
