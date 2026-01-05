<?php
/**
 * COG Non Profit Email Content
 *
 * @version     1.0.0
 * @package     cashier/cost-of-goods/templates/plain/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/* translators: 1. Order Id */
echo esc_html( apply_filters( 'sa_cfw_cog_loss_order_message', sprintf( __( 'You received an order(#%s) with less then your added cost of goods amount', 'cashier' ), $order_id ) ) );

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
