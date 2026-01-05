<?php
/**
 * SideCart: Single Cart Item Template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cashier/sidecart/cart-item.php.
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
<div data-key="<?php echo esc_attr( $cart_item_key ); ?>" class="<?php echo esc_attr( $product_classes ); ?>">
	<?php do_action( 'cfw_sc_product_start', $_product, $cart_item_key ); ?>
	<?php if ( $show_pimage ) : ?>

		<div class="cfw-sc-img-col">
			<?php echo wp_kses( $thumbnail, $allowed_html ); ?>
		</div>

	<?php endif; ?>

	<div class="product-details">
		<?php if ( $show_pname ) : ?>
		<a href="<?php echo esc_url( $product_permalink ); ?>" class="cfw-sc-pname name">
			<?php echo wp_kses( $product_name, $allowed_html ); ?>
		</a>
		<?php endif; ?>

		<span class="cfw-rating">
			<?php
			echo wp_kses( wc_get_rating_html( $rating_count ), $allowed_html );
			?>
		</span>
		<div class="actions">
			<?php if ( $show_pprice ) : ?>
				<div class="cfw-sc-pprice price">
					<?php echo wp_kses( $product_price, $allowed_html ); ?>
				</div>
			<?php endif; ?>


			<?php if ( $_product->is_sold_individually() ) : ?>

				<div class="cfw-sc-qty-price">
					<span><?php echo esc_html__( 'Qty', 'side-cart-woocommerce' ) . ' : '; ?></span>
					<span><?php echo esc_html( $cart_item['quantity'] ); ?></span>
				</div>

			<?php else : ?>
				<?php
					$args = apply_filters(
						'cfw_sc_quantity_input_args',
						array(
							'sc_classes'  => 'cfw-sc-qty qty-input',
							'step'        => $step,
							'min_value'   => $min_value,
							'max_value'   => $max_value,
							'input_value' => $cart_item['quantity'],
							'placeholder' => $placeholder,
							'inputmode'   => $inputmode,
						),
						$_product,
						$cart_item,
						$cart_item_key
					);
				?>
				<?php wc_get_template( 'quantity-input.php', $args ); ?>

			<?php endif; ?>

			<?php if ( $show_premove ) : ?>
				<div class="cfw-remove-container">
					<a href="#" class="cfw-sc-remove">
						<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
								d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
							</path>
						</svg>
					</a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
