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
        <input type="hidden" name="<?php echo esc_attr( $field_name ) ?>" id="<?php echo esc_attr( $field_id ) ?>" value="<?php echo $selected; ?>">
        <span id="dhl-selected-parcel"><?php echo $selected_name; ?></span><br>
        <a href="#" id="dhl-show-parcel-modal"><?php _e('Show DHL Parcel Shops', 'woocommerce-german-market'); ?></a>
        <div id="dhl-parcel-modal">
            <div class="modal-content">
                <span class="close" id="dhl-close-parcel-modal">&times;</span>

                <div class="form-inline">
                    <div class="form-group">
	                    <input name="dhl-modal-postcode" value="<?php echo WC()->customer->get_shipping_postcode(); ?>" type="text" class="form-control modal-postcode" placeholder="<?php echo esc_attr__( 'Zip Code', 'woocommerce-german-market' ); ?>">
	                    <input name="dhl-modal-city" value="<?php echo WC()->customer->get_shipping_city(); ?>" type="text" class="form-control modal-city" placeholder="<?php echo esc_attr__( 'City', 'woocommerce-german-market' ); ?>">
                        <input name="dhl-modal-address" value="<?php echo WC()->customer->get_shipping_address(); ?>" type="text" class="form-control modal-address" placeholder="<?php echo esc_attr__( 'Address', 'woocommerce-german-market' ); ?>">
	                    <input name="dhl-modal-country" value="<?php echo WC()->customer->get_shipping_country(); ?>" type="hidden">
                        <a href="#" class="button search-location"><?php echo esc_html__( 'Search', 'woocommerce-german-market' ); ?></a>
                    </div>
                </div>

                <div class="modal-map">
                    <!-- Map -->
                    <div id="dhl-parcel-modal-map"></div>

                    <!-- Info block -->
                    <div id="dhl-parcel-modal-info">
                        <div class="info-wrap">
                            <h3></h3>
                            <p>
                                <strong><?php echo __( 'Address', 'woocommerce-german-market' ); ?></strong>
                                <br>
                                <span class="info-address"></span>
                            </p>
                            <div class="working-hours-wrapper">
                                <p>
                                    <strong><?php echo __( 'Opening Hours', 'woocommerce-german-market' ); ?></strong>
                                </p>

                                <ul class="working-hours">
                                    <li class="mon"><span><?php echo __( 'Monday', 'woocommerce-german-market' ); ?>:</span> <span class="morning"></span> <span class="afternoon"></span></li>
                                    <li class="tue"><span><?php echo __( 'Tuesday', 'woocommerce-german-market' ); ?>:</span> <span class="morning"></span> <span class="afternoon"></span></li>
                                    <li class="wed"><span><?php echo __( 'Wednesday', 'woocommerce-german-market' ); ?>:</span> <span class="morning"></span> <span class="afternoon"></span></li>
                                    <li class="thu"><span><?php echo __( 'Thursday', 'woocommerce-german-market' ); ?>:</span> <span class="morning"></span> <span class="afternoon"></span></li>
                                    <li class="fri"><span><?php echo __( 'Friday', 'woocommerce-german-market' ); ?>:</span> <span class="morning"></span> <span class="afternoon"></span></li>
                                    <li class="sat"><span><?php echo __( 'Saturday', 'woocommerce-german-market' ); ?>:</span> <span class="morning"></span> <span class="afternoon"></span></li>
                                    <li class="sun"><span><?php echo __( 'Sunday', 'woocommerce-german-market' ); ?>:</span> <span class="morning"></span> <span class="afternoon"></span></li>
                                </ul>
                            </div>

                            <p style="display: none;">
                                <strong><?php echo __( 'Contact', 'woocommerce-german-market' ); ?></strong>
                                <br>
                                <span class="info-email"></span>
                                <br>
                                <span class="info-phone"></span>
                            </p>

                            <a href="#" class="button select-terminal" data-method="<?php echo esc_attr( $field_id ) ?>"><?php echo __( 'Select', 'woocommerce-german-market' ); ?></a>
                    </div>
                </div>
            </div>
        </div>
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
