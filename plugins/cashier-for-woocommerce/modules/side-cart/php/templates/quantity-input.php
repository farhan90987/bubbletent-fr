<?php
/**
 * Sidecart: Quantity Input Template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cashier/sidecart/quantity-input.php.
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

<div class="cfw-sc-qty-box">
	<?php do_action( 'cfw_sc_before_quantity_input_field' ); ?> 
	<input
		type="number"
		class="<?php echo esc_attr( join( ' ', (array) $sc_classes ) ); ?>"
		step="<?php echo esc_attr( $step ); ?>"
		min="<?php echo esc_attr( $min_value ); ?>"
		max="<?php echo esc_attr( 0 < $max_value ? $max_value : '' ); ?>"
		value="<?php echo esc_attr( $input_value ); ?>"
		placeholder="<?php echo esc_attr( $placeholder ); ?>"
		inputmode="<?php echo esc_attr( $inputmode ); ?>" />

	<?php do_action( 'cfw_sc_after_quantity_input_field' ); ?> 
</div>
