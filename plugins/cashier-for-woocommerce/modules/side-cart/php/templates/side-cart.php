<?php
/**
 * Side Cart Main Template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cashier/sidecart/side-cart.php.
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
<div class="cfw-sc-modal">
	<div class="cfw-sc-container">
		<div class="cfw-sc-base">
			<div class="cfw-blocker"></div>
			<section aria-labelledby="slide-over-heading">
				<?php wc_get_template( 'cart-header.php' ); ?>
				<?php wc_get_template( 'cart-notice.php', $cart_notice ); ?>
				<?php wc_get_template( 'cart-contents.php', array_merge( $cart_items, array( 'allowed_html' => $allowed_html ) ) ); ?>

				<div class="cart-meta">
					<?php wc_get_template( 'cart-coupons.php', array_merge( $cart_coupons, array( 'allowed_html' => $allowed_html ) ) ); ?>
					<?php wc_get_template( 'cart-totals.php', array_merge( $cart_totals, array( 'allowed_html' => $allowed_html ) ) ); ?>
				</div>

				<?php wc_get_template( 'call-to-actions.php' ); ?>

				<div class="cfw-loader-container">
					<div class="cfw-sc-loader"></div>
				</div>
			</section>
		</div>
	</div>

	<?php wc_get_template( 'cart-bag.php', array( 'item_count' => $item_count ) ); ?>

</div>
