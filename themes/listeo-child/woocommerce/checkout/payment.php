<?php
/**
 * Checkout Payment Section
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/payment.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.1.0
 */

defined( 'ABSPATH' ) || exit;

// if ( ! wp_doing_ajax() ) {
//	do_action( 'woocommerce_review_order_before_payment' );
//}

if ( has_action( 'woocommerce_review_order_before_payment' ) ) {
  do_action( 'woocommerce_review_order_before_payment' );
}

if ( ! function_exists( 'has_valid_product_type' ) ) {
	/**
	 * Checks if the valid product type is there in the cart.
	 *
	 * @param string $type type of the product that should be present.
	 * @return boolean
	 */
	function has_valid_product_type( $type = 'listing_booking' ) {

		$has_product_type = false;

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$product = wc_get_product( $cart_item['data']->get_id() );
			if ( $product->is_type( $type ) ) {
				$has_product_type = true;
				break;
			}
		}

		return $has_product_type;
	}
}

?>
<div id="payment" class="woocommerce-checkout-payment">
	<?php if ( WC()->cart->needs_payment() ) : ?>
		<?php if ( has_valid_product_type() ) : ?>
			<?php if ( ! empty( $available_gateways ) ) : ?>
				<div class="smoobu-payments-wrapper">
					<label for="smoobu-payments-container" class="smoobu-payments-label" >
						<?php esc_html_e( 'Payment Instructions', 'woocomerce' ); ?>
					</label>
					<select id="smoobu-payments-container" name="smoobu-payments-container" class="smoobu-payments-select" >
						<?php foreach ( $available_gateways as $gateway ) : ?>
								<option id="option_payment_method_<?php echo esc_attr( $gateway->id ); ?>" value="<?php echo esc_attr( $gateway->id ); ?>" <?php echo esc_attr( $gateway->chosen ? 'selected' : '' ); ?> >
									<?php echo $gateway->get_title(); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?> <?php echo $gateway->get_icon(); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?>
								</option>
						<?php endforeach; ?>
					</select>
				</div>
				<?php foreach ( $available_gateways as $gateway ) : ?>
					<?php if ( $gateway->has_fields() || $gateway->get_description() ) : ?>
						<div class="payment_box payment_method_<?php echo esc_attr( $gateway->id ); ?>" <?php if ( ! $gateway->chosen ) : /* phpcs:ignore Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace */ ?>style="display:none;"<?php endif; /* phpcs:ignore Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace */ ?>>
							<?php $gateway->payment_fields(); ?>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		<?php endif; ?>
		<ul class="wc_payment_methods payment_methods methods" style="<?php echo esc_attr( has_valid_product_type() ? 'display: none' : '' ); ?>">
			<?php
			if ( ! empty( $available_gateways ) ) {
				foreach ( $available_gateways as $gateway ) {
					wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
				}
			} else {
				echo '<li>';
				wc_print_notice( apply_filters( 'woocommerce_no_available_payment_methods_message', WC()->customer->get_billing_country() ? esc_html__( 'Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.', 'listeo_core' ) : esc_html__( 'Please fill in your details above to see available payment methods.', 'listeo_core'  ) ), 'notice' ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
				echo '</li>';
			}
			?>
		</ul>
	<?php endif; ?>
	<div class="form-row place-order">
		<noscript>
			<?php
			/* translators: $1 and $2 opening and closing emphasis tags respectively */
			printf( esc_html__( 'Since your browser does not support JavaScript, or it is disabled, please ensure you click the %1$sUpdate Totals%2$s button before placing your order. You may be charged more than the amount stated above if you fail to do so.', 'listeo_core'  ), '<em>', '</em>' );
			?>
			<br/><button type="submit" class="button alt<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'Update totals', 'listeo_core'  ); ?>"><?php esc_html_e( 'Update totals', 'listeo_core'  ); ?></button>
		</noscript>

		<?php wc_get_template( 'checkout/terms.php' ); ?>

		<?php do_action( 'woocommerce_review_order_before_submit' ); ?>

		<?php echo apply_filters( 'woocommerce_order_button_html', '<button type="submit" class="button alt' . esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ) . '" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>' ); // @codingStandardsIgnoreLine ?>

		<?php do_action( 'woocommerce_review_order_after_submit' ); ?>

		<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
	</div>
</div>
<?php
if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_after_payment' );
}
