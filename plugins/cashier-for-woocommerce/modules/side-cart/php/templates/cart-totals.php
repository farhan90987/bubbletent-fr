<?php
/**
 * Sidecart: Cart Totals Templates
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cashier/sidecart/cart-totals.php
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

<div class="cfw-sc-ft-totals">
	<table class="cart-totals">
	<?php foreach ( $cart_totals as $key => $data ) : ?>
		<?php if ( $data['value'] ) : ?>
		<tr class="cfw-sc-ft-amt">
			<td class="cfw-sc-ft-amt-label"><?php echo wp_kses( $data['label'], $allowed_html ); ?></td>
			<td class="cfw-sc-ft-amt-value"><?php echo wp_kses( $data['value'], $allowed_html ); ?></td>
		</tr>
		<?php endif; ?>
	<?php endforeach; ?>
	</table>
</div>
