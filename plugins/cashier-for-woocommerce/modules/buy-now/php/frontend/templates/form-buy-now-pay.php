<?php
/**
 * Buy Now Pay Form
 *
 * @package     WooCommerceBuyNow/Templates
 *  author      StoreApps
 * @version     1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

global $wc_buy_now;

do_action( 'woocommerce_before_main_content' );

?>

<style type="text/css">
	.buy_now_pay_container {
		padding: 25px;
	}
	form#buy_now_pay {
		margin: 2em auto;
		width: 30em;
	}
	.buy_now_action {
		width: 100%;
		padding-top: 25px;
		display: inline-block;
	}
	.buy_now_go_back {
		line-height: 2.7em;
		padding-right: 2em;
	}
	.buy_now_complete_payment {
		padding-left: 2em;
	}
</style>

<div class="woocommerce">
	<div class="buy_now_pay_container">
		<form id="buy_now_pay" method="post">

			<center><h2><?php echo esc_html__( 'Processing your order&hellip;', 'cashier' ); ?></h2></center>
			<?php
				wc_print_notices();

				$order_payment_method = $order->get_payment_method();
			?>
			<input type="hidden" name="payment_method" value="<?php echo esc_attr( $order_payment_method ); ?>" />
			<?php
				wp_nonce_field( 'buy-now-pay-action' );
				do_action( 'buy_now_before_pay_action', $order );

				$redirect_url = get_permalink( wc_get_page_id( 'cart' ) );
			if ( empty( $redirect_url ) ) {
				$redirect_url = home_url();
			}
			?>
			<div class="buy_now_action">
				<center>
					<span class="buy_now_go_back">
						<?php
							$wp_get_referer = ( wp_get_referer() ) ? wp_get_referer() : $redirect_url; // phpcs:ignore
						?>
						<small><a href="<?php echo esc_url( $wp_get_referer ); ?>"><?php echo esc_html__( '&larr; Cancel & Return', 'cashier' ); ?></a></small>
					</span>
					<span class="buy_now_complete_payment">
						<input type="submit" class="button button-primary" name="buy_now_complete_payment_button" value="<?php echo esc_attr__( 'Complete Payment &rarr;', 'cashier' ); ?>">
					</span>
				</center>
			</div>

		</form>
	</div>
</div>

<?php do_action( 'woocommerce_after_main_content' ); ?>

<?php get_footer(); ?>
