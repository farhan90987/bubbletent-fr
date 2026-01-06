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
	<th><?php esc_html_e( 'Choose a DHL packstation', 'woocommerce-german-market' ) ?> <abbr class="required" title="<?php echo __('Required', 'woocommerce-german-market'); ?>">*</abbr></th>
	<?php do_action('woocommerce_wgm_shipping_checkout_table_column' ); ?>
	<td colspan="<?php echo apply_filters( 'woocommerce_wgm_shipping_checkout_table_colspan', 1 ); ?>">
        <input type="hidden" name="<?php echo esc_attr( $field_name ) ?>" id="<?php echo esc_attr( $field_id ) ?>" value="<?php echo $selected; ?>">
        <span id="dhl-selected-parcel"><?php echo $selected_name; ?></span><br>
        <a href="#" id="dhl-show-parcel-modal"><?php _e('Show Packstations', 'woocommerce-german-market'); ?></a>
        <style>
            #dhl-parcel-modal {
                display: none;
                position: fixed;
                z-index: 9999;
                padding-top: 100px;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgb(0,0,0);
                background-color: rgba(0,0,0,0.4);
                font-size: 14px;
            }

            #dhl-parcel-modal h3 {
                font-size: 18px;
                margin: 1rem auto 2rem;
            }

            #dhl-parcel-modal .modal-content {
                background-color: #fefefe;
                margin: auto;
                padding: 20px;
                border: 1px solid #888;
                width: 80%;
                max-width: 900px;
            }

            #dhl-parcel-modal .dhl-city-label {
                padding-right: 10px;
                padding-left: 5px;
                text-transform: capitalize;
            }

            #dhl-parcel-modal .close {
                color: #aaaaaa;
                float: right;
                font-size: 28px;
                font-weight: bold;
            }

            #dhl-parcel-modal .close:hover,
            #dhl-parcel-modal .close:focus {
                color: #000;
                text-decoration: none;
                cursor: pointer;
            }

            #dhl-parcel-modal .modal-map{
                height: 400px;
                margin-top: 20px;
                position: relative;
            }

            #dhl-parcel-modal-map {
                height: 100%;
            }

            #dhl-parcel-modal-info {
                position: absolute;
                top: 10px;
                bottom: 10px;
                width: 300px;
                right: 10px;
                background-color: #ffffff;
                display: none;
                height: 60%;
            }

            #dhl-parcel-modal-info .working-hours {
                padding: 0;
                margin: 0;
                list-style: none inside;
                font-size: 11px;
            }
            #dhl-parcel-modal-info .working-hours span {
                width: 80px;
                margin-right: 5px;
                display: inline-block;
            }

            #dhl-parcel-modal-info .working-hours li {
                line-height: 1;
                margin: 0 0 0 2rem;
            }

            #dhl-parcel-modal-info .info-wrap {
                position: relative;
                padding: 10px;
                height: 100%;
            }

            #dhl-parcel-modal-info .select-terminal {
                position: absolute;
                bottom: 10px;
                left: 10px;
                right: 10px;
                text-align: center;
            }
        </style>

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
                            <p><?php echo __('DHL Packstation', 'woocommerce-german-market')?> #<span class="packstation_id"></span></p>
                            <h3></h3>
                            <p>
                                <strong><?php echo __( 'Address', 'woocommerce-german-market' ); ?></strong>
                                <br>
                                <span class="info-address"></span>
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
	<th><?php echo __( 'Preferred Delivery Date', 'woocommerce-german-market' ) ?></th>
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
