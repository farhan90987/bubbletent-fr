<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<tr class="wc_shipping_dhl_postnumber">
    <th><?php echo __( 'DHL client number', 'woocommerce-german-market' ) ?> <abbr class="required" title="<?php echo __( 'Required', 'woocommerce-german-market' ); ?>">*</abbr></th>
	<?php do_action('woocommerce_wgm_shipping_checkout_table_column' ); ?>
	<td colspan="<?php echo apply_filters( 'woocommerce_wgm_shipping_checkout_table_colspan', 1 ); ?>">
        <input type="text" name="wc_shipping_dhl_client_number" id="wc_shipping_dhl_client_number" value="<?php echo $dhl_client_id; ?>" placeholder="<?php echo __( 'DHL Client Number (6-10 Digits)', 'woocommerce-german-market' ); ?>">
    </td>
</tr>
<tr class="wc_shipping_dhl_terminals">
	<th><?php esc_html_e( 'Choose a pickup point', 'woocommerce-german-market' ) ?> <abbr class="required" title="required">*</abbr></th>
	<?php do_action('woocommerce_wgm_shipping_checkout_table_column' ); ?>
	<td colspan="<?php echo apply_filters( 'woocommerce_wgm_shipping_checkout_table_colspan', 1 ); ?>">
		<select name="<?php echo esc_attr( $field_name ) ?>" id="<?php echo esc_attr( $field_id ) ?>" class="select shipping-terminal-select">
			<option value="" <?php selected( $selected, '' ); ?>><?php echo esc_html__( 'Choose a pickup point', 'woocommerce-german-market' ) ?></option>
			<?php foreach( $terminals as $group_name => $locations ) : ?>
				<?php if ( count( $terminals ) > 1 ) : ?>
					<optgroup label="<?php echo $group_name ?>">
				<?php endif; ?>
					<?php foreach( $locations as $location ) : ?>
						<option
							data-cod="<?php echo $location[ 'cod' ]; ?>"
							data-terminalCompany="<?php echo $location[ 'company' ]; ?>"
							data-terminalStreet="<?php echo $location[ 'street' ]; ?>"
							data-terminalPostcode="<?php echo $location[ 'pcode' ]; ?>"
							data-terminalCity="<?php echo $location[ 'city' ]; ?>"
							value="<?php echo esc_html( $location[ 'parcelshop_id' ] ) ?>"
							<?php selected( $selected, $location[ 'parcelshop_id' ] ); ?>
						><?php echo esc_html( $location[ 'company' ] ) ?>, <?php echo esc_html( $location[ 'street' ] ) ?></option>
					<?php endforeach; ?>
				<?php if ( count( $terminals ) > 1 ) : ?>
					</optgroup>
				<?php endif; ?>
			<?php endforeach; ?>
		</select>
	</td>
</tr>
<?php

if ( 'on' === $preferred_day_enabled ) {

?>
<tr class="wc_shipping_dhl_preferred_day">
	<th><?php echo __( 'Preferred delivery date', 'woocommerce-german-market' ) ?></th>
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
				var day = date.getDay();
				return [(day != 0)];
			},
			dateFormat : 'yy-mm-dd',
			minDate: <?php echo $preferred_day_first_date; ?>,
			maxDate: <?php echo $preferred_day_first_date + 6; ?>,
		} );
	} );
</script>
<?php

}
