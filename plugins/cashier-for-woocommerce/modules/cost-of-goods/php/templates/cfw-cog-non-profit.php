<?php
/**
 * COG Non Profit Email Content
 *
 * @version     1.0.0
 * @package     cashier/cost-of-goods/templates/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! isset( $email ) ) {
	$email = null;
}

if ( has_action( 'woocommerce_email_header' ) ) {
	do_action( 'woocommerce_email_header', $email_heading, $email );
} else {
	if ( function_exists( 'wc_get_template' ) ) {
		wc_get_template( 'emails/email-header.php', array( 'email_heading' => $email_heading ) );
	} else {
		woocommerce_get_template( 'emails/email-header.php', array( 'email_heading' => $email_heading ) );
	}
}

/* translators: 1. Order Id */
echo esc_html( apply_filters( 'sa_cfw_cog_loss_order_message', sprintf( __( 'You received an order(#%s) with less then your added cost of goods amount', 'cashier' ), $order_id ) ) );


if ( has_action( 'woocommerce_email_footer' ) ) {
	do_action( 'woocommerce_email_footer', $email );
} else {
	if ( function_exists( 'wc_get_template' ) ) {
		wc_get_template( 'emails/email-footer.php' );
	} else {
		woocommerce_get_template( 'emails/email-footer.php' );
	}
}
