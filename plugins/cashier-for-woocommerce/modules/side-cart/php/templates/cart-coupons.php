<?php
/**
 * Side Cart: Coupon Cart Template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cashier/sidecart/cart-coupons.php.
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

<div class="cfw-sc-ft-extras discount-container">
<?php if ( $show_coupon && ! WC()->cart->is_empty() ) : ?>
	<?php foreach ( $coupons as $code => $coupon ) : ?>
	<div class="cfw-sc-ftx-row cfw-sc-ftx-coupon">
		<div class="cfw-sc-ftx-coups">
			<div class="cfw-sc-remove-coupon" data-code="<?php echo esc_attr( $code ); ?>">
				<span>
					<?php printf( '%1$s: %2$s', esc_html__( 'Coupon', 'cashier' ), esc_html( $code ) ); ?>
					<span class="cfw-sc-icon-cross" data-coupon="<?php echo esc_attr( $code ); ?>">
						<?php esc_html_e( 'Remove', 'cashier' ); ?>
					</span>
				</span>
				<?php
				$amount         = WC()->cart->get_coupon_discount_amount( $coupon->get_code(), WC()->cart->display_cart_ex_tax );
				$dis_price_html = wc_price( $amount );
				if ( $coupon->get_free_shipping() && empty( $amount ) ) {
					$dis_price_html = __( 'Free shipping coupon', 'cashier' );
				}
				?>
				<span class="cfw-sc-right">
					<?php echo wp_kses( $dis_price_html, $allowed_html ); ?>
				</span>
			</div>

		</div>
	</div>
	<?php endforeach; ?>
	<div class="cfw-sc-ftx-row cfw-sc-ftx-coupon">
		<span class="cfw-sc-ftx-icon">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none">
				<path
					d="M9.3 7.5c0 .3.3.6.6.6h2.7c.3 0 .6-.3.6-.6s-.3-.6-.6-.6H9.9c-.4.1-.6.3-.6.6zm13-.5h-2.1c-.3 0-.6.3-.6.6s.3.6.6.6h2.1c.3 0 .6.2.6.6v11.9c0 .3-.2.6-.6.6H1.7c-.3 0-.6-.2-.6-.6v-5.2c0-.3-.3-.6-.6-.6s-.6.3-.6.6v5.2c0 .9.8 1.7 1.7 1.7h20.5c.9 0 1.7-.8 1.7-1.7v-12c.2-1-.6-1.7-1.5-1.7z">
				</path>
				<path
					d="M.3 12.2c.7 1.2 2.2 1.6 3.4.9 1.2-.7 1.6-2.2.9-3.4-.3-.4-.6-.8-1-1l.8-.4 5.3 3c.7.4 1.3-.6.6-1L5.6 7.5l4.7-2.7c.5-.3.3-1.1-.3-1.1-.1 0-.2 0-.3.1l-5.3 3-.7-.4c.4-.2.8-.5 1-1 .7-1.1.3-2.7-.9-3.4-.4-.2-.8-.3-1.2-.3C1.7 1.7.8 2.1.4 3c-.7 1.2-.3 2.8.9 3.4l2 1.2-2 1.2c-1.2.6-1.7 2.2-1 3.4zm1.5-6.8c-.6-.4-.9-1.2-.5-1.9.3-.4.7-.7 1.1-.7.3 0 .5 0 .8.2.7.4.9 1.2.5 1.9-.4.6-1.2.9-1.9.5zm0 4.3c.2-.1.5-.2.8-.2.4 0 .9.3 1.1.7.4.7.2 1.5-.5 1.9-.7.4-1.5.2-1.9-.5-.4-.7-.1-1.5.5-1.9zm11.1 8.2c.2.2.6.2.8 0l5.7-5.7c.4-.4.1-1-.4-1-.1 0-.3.1-.4.2l-5.7 5.7c-.2.2-.2.6 0 .8zm-.3-4.1c.8.8 2 .8 2.8 0 .8-.8.8-2 0-2.7-.8-.8-2-.8-2.8 0-.8.7-.8 2 0 2.7zm.8-1.9c.3-.3.8-.3 1.1 0 .3.3.3.8 0 1.1-.3.3-.8.3-1.1 0-.3-.3-.3-.8 0-1.1zm3.6 3.6c-.8.8-.8 2 0 2.7.8.8 2 .8 2.8 0 .8-.8.8-2 0-2.7-.8-.8-2.1-.8-2.8 0zm1.9 1.9c-.3.3-.8.3-1.1 0-.3-.3-.3-.8 0-1.1.3-.3.8-.3 1.1 0 .4.3.4.8 0 1.1zM15 8.1h2.7c.3 0 .6-.3.6-.6S18 7 17.7 7H15c-.3 0-.6.3-.6.6s.3.5.6.5z">
				</path>
			</svg>
		</span>

		<span class="cfw-sc-toggle-slider" data-slider="coupon">
			<?php esc_html_e( 'Have a coupon code?', 'cashier' ); ?>
		</span>
	</div>
	<div class="cfw-coupon-input">
		<form class="cfw-sc-sl-apply-coupon">
			<input type="text" name="cfw-sc-slcf-input"
			placeholder="<?php esc_html_e( 'Enter coupon code here', 'cashier' ); ?>">
			<button type="submit"><?php esc_html_e( 'Apply', 'cashier' ); ?></button>
		</form>
	</div>

<?php endif; ?>
</div>
