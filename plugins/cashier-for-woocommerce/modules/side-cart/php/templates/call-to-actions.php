<?php
/**
 * Side Cart: Call to Action Template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cashier/sidecart/call-to-action.php.
 *
 * HOWEVER, on occasion we will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen.
 *
 * @version 1.1.0
 * @package Sidecart/Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<footer>
	<div class="cta-container">
		<?php
		$shop_url     = apply_filters( 'sa_cfw_sc_shop_url', wc_get_page_id( 'shop' ) !== -1 ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url() );
		$checkout_url = apply_filters( 'sa_cfw_checkout_url', wc_get_checkout_url() );
		?>
		<?php printf( '<a href="%1$s" class="continue-shopping">%2$s</a>', esc_url( $shop_url ), esc_html__( 'Continue shopping', 'cashier' ) ); ?>
		<?php printf( '<a href="%1$s" class="checkout">%2$s</a>', esc_url( $checkout_url ), esc_html__( 'Checkout', 'cashier' ) ); ?>
	</div>
</footer>
