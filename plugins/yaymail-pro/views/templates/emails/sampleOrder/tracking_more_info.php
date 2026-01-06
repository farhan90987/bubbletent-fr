<?php

$options = get_option( 'trackingmore_option_name' );

if ( array_key_exists( 'track_message_1', $options ) && array_key_exists( 'track_message_2', $options ) ) {
	$track_message_1 = $options['track_message_1'];
	$track_message_2 = $options['track_message_2'];
} else {
	$track_message_1 = __( 'Your order was shipped via ', 'trackingmore' );
	$track_message_2 = __( 'Tracking number is ', 'trackingmore' );
}


echo wp_kses_post( '<span>' . $track_message_1 . 'yaymail</span><br/><span>' . $track_message_2 . 'yaymail</span>' );
