<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( 'on' === $preferred_day_enabled ) {

?>
<tr class="wc_shipping_dhl_preferred_day">
	<th>
		<?php echo __( 'Preferred delivery date', 'woocommerce-german-market' ) ?>
	</th>
	<?php do_action('woocommerce_wgm_shipping_checkout_table_column' ); ?>
	<td colspan="<?php echo apply_filters( 'woocommerce_wgm_shipping_checkout_table_colspan', 1 ); ?>">
		<input type="text" name="wgm_dhl_service_preferred_day" id="wgm_dhl_service_preferred_day" class="datepicker" value="<?php echo $preferred_day; ?>" placeholder="<?php echo __( 'Choose Date', 'woocommerce-german-market' ); ?>">
		<?php echo $preferred_day_service_price_string; ?>
	</td>
</tr>
<script>
	jQuery(function( $ ) {
		$( ".wc_shipping_dhl_preferred_day .datepicker").datepicker( {
			beforeShowDay: function(date) {
				const day = date.getDay();
				return [(day !== 0)];
			},
			dateFormat : 'yy-mm-dd',
			minDate: <?php echo $preferred_day_first_date; ?>,
			maxDate: <?php echo $preferred_day_first_date + 6; ?>,
		} );
	} );
</script>
<?php

}
