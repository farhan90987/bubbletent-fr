<?php
/**
 * BOGO general fields in bulk generate page
 *
 * @since 2.2.0
 * @package    Wt_Smart_Coupon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$ds_obj = Wbte\Sc\Ds\Wbte_Ds::get_instance( WEBTOFFEE_SMARTCOUPON_VERSION );

$today_date      = gmdate( 'Y-m-d' );
?>
<style>
	/* Bulk BOGO */
	#wbte_sc_bulk_bogo_container { width: 95%; background: #fff; border-radius: 8px; box-shadow: 0px 0px 10px 1px #545f6f2e; display: none; }
	.wbte_sc_bulk_bogo_field { margin-bottom: 20px; }
	.wbte_sc_bulk_bogo_field label { display: block; margin-bottom: 8px; font-weight: 500; }
	.wbte_sc_bulk_bogo_select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 15px; }
	.wbte_sc_bulk_bogo_input { width: 100px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
	.wbte_sc_bulk_bogo_radio_group { margin: 10px 0; }
	.wbte_sc_bulk_bogo_radio_label { display: flex; align-items: center; margin: 8px 0; cursor: pointer; }
	.wbte_sc_bulk_bogo_radio_label input { margin-right: 8px; }

	.wbte_sc_bogo_edit_step_content{ margin: 0; }
	.wbte_sc_bulk_bogo_table{ width: 100%; }
	.wbte_sc_bogo_edit_table tr th:first-child, .wbte_sc_bulk_bogo_table tr th:first-child{ width: 30%; text-align: start; }
	.wbte_sc_bogo_customer_buys_table, .wbte_sc_bogo_additional_fields_table{ width: 60%; }
	.wbte_sc_bulk_bogo_display_checkbox:not(:first-child) { margin-top: 12px; }
	.wbte_sc_bulk_bogo_tooltip_div_td{ display: flex; align-items: center; gap: 10px; }
	.wbte_sc_bogo_edit_code_cond_radio{ gap: 0; }
	#wbte_sc_bogo_schedule_content th{ padding-left: 30px; }
	.wbte_sc_bulk_bogo_table{ border-spacing: 0px 20px; }

	.wbte_sc_coupon_format_container{ display: flex; align-items: center; border: 1.5px solid #BDC1C6; border-radius: 6px; background: #fff; min-width: 320px; max-width: 500px; height: 40px; justify-content: space-between; }
	.wbte_sc_bulk_bogo_pre_suf{ border: none !important; background: transparent !important; padding: 8px !important; width: 100px !important; outline: none !important; box-shadow: none !important; }
	.wbte_sc_bulk_bogo_pre_suf::placeholder{ color: #9DA3AA; }
	.wbte_sc_coupon_code_display{ padding: 4px 8px; background: #EBF1FD; border-radius: 6px; color: #666; font-size: 14px; }

	#wbte_sc_bogo_coupon_name{ width: 300px; text-align: unset; }
</style>
<table class="wbte_sc_bulk_bogo_table">
	<tr>
		<th scope="row">
			<label for="wbte_sc_bulk_bogo_discount_type"><?php esc_html_e( 'Discount type', 'wt-smart-coupons-for-woocommerce' ); ?></label>
		</th>
		<td>
			<div class="wbte_sc_bulk_bogo_tooltip_div_td">
				<?php echo wp_kses_post( wc_help_tip( __( 'Select the type of discount you want to apply to the coupon', 'wt-smart-coupons-for-woocommerce' ) ) ); ?>
				<div class="wbte_sc_bogo_edit_custom_drop_down_head wbte_sc_bulk_bogo_discount_type_dropdown">
					<div class="wbte_sc_bogo_edit_custom_drop_down_btn" style="min-width: 300px; z-index: 5;">
						<p><?php esc_html_e( 'BOGO', 'wt-smart-coupons-for-woocommerce' ); ?></p>
						<span class="dashicons dashicons-arrow-down-alt2"></span>
					</div>
					<div class="wbte_sc_bogo_edit_custom_drop_down" style="z-index: 4;">
						<?php
						$discount_types = wc_get_coupon_types();
						foreach ( $discount_types as $discount_type => $label ) {
							printf(
								'<p data-val="%s" class="wbte_sc_bogo_edit_custom_drop_down_sub_btn">%s</p>',
								esc_attr( $discount_type ),
								esc_html( $label )
							);
						}
						?>
					</div>
					<input type="hidden" name="discount_type" id="discount_type" value="wbte_sc_bogo">
				</div>
			</div>
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="wbte_sc_bulk_bogo_discount_type"><?php esc_html_e( 'Offer title', 'wt-smart-coupons-for-woocommerce' ); ?></label>
		</th>
		<td>
			<div class="wbte_sc_bulk_bogo_tooltip_div_td">
				<?php echo wp_kses_post( wc_help_tip( __( 'The offer title is used to identify a BOGO campaign within the plugin and the store', 'wt-smart-coupons-for-woocommerce' ) ) ); ?>
				<input type="text" id="wbte_sc_bogo_coupon_name" name="wbte_sc_bogo_coupon_name" class="wbte_sc_bogo_text_input" placeholder="<?php esc_attr_e( 'Campaign title', 'wt-smart-coupons-for-woocommerce' ); ?>" value="">
			</div>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<label><?php esc_html_e( 'Trigger offer', 'wt-smart-coupons-for-woocommerce' ); ?></label>
		</th>
		<td>
			<div class="wbte_sc_bulk_bogo_tooltip_div_td" style="align-items: flex-start;">
				<?php echo wp_kses_post( wc_help_tip( __( 'Choose how the offer should be triggered: automatically applied at checkout or through a coupon code', 'wt-smart-coupons-for-woocommerce' ) ) ); ?>
				<div class="wbte_sc_bulk_bogo_apply_radio_group_div">
					<?php
					echo $ds_obj->get_component(
						'radio-group multi-line',
						array(
							'values' => array(
								'name'  => 'wbte_sc_bogo_code_condition',
								'items' => array(
									array(
										'label'      => sprintf(
											// Translators: 1: tooltip text.
											esc_html__( 'Automatically %s', 'wt-smart-coupons-for-woocommerce' ),
											wp_kses_post( '<span class="wbte_sc_bogo_help_text wbte_sc_bogo_code_cond_help_txt">' . esc_html__( 'Offer name will be displayed in the cart summary when offer is applied', 'wt-smart-coupons-for-woocommerce' ) . '</span>' )
										),
										'value'      => 'wbte_sc_bogo_code_auto',
										'is_checked' => true,
									),
									array(
										'label' => esc_html__( 'Through coupon code', 'wt-smart-coupons-for-woocommerce' ),
										'value' => 'wbte_sc_bogo_code_manual',
									),
								),
							),
							'class'  => array( 'wbte_sc_bogo_edit_code_cond_radio' ),
						)
					);
					?>
				</div>
			</div>
		</td>
	</tr>

	<tr>
		<th>
			<label><?php esc_html_e( 'Offer Code Format', 'wt-smart-coupons-for-woocommerce' ); ?></label>
		</th>
		<td>
			<div class="wbte_sc_bulk_bogo_tooltip_div_td">
				<?php echo wp_kses_post( wc_help_tip( __( 'Customize how your coupon codes are generated by adding a prefix or suffix. Example: "BOGO-VJASISVY-2024"', 'wt-smart-coupons-for-woocommerce' ) ) ); ?>
				<div>
					<div class="wbte_sc_coupon_format_container">
						<input type="text" id="_wt_scg_coupon_prefix" class="wbte_sc_bulk_bogo_pre_suf" name="_wt_scg_coupon_prefix" placeholder="<?php esc_attr_e( 'Prefix', 'wt-smart-coupons-for-woocommerce' ); ?>" value="">
						<div class="wbte_sc_coupon_code_display"><?php esc_html_e( 'Offer code', 'wt-smart-coupons-for-woocommerce' ); ?></div>
						<input type="text" id="_wt_scg_coupon_suffix" class="wbte_sc_bulk_bogo_pre_suf" name="_wt_scg_coupon_suffix" placeholder="<?php esc_attr_e( 'Suffix', 'wt-smart-coupons-for-woocommerce' ); ?>" value="">
					</div>
					<span class="wbte_sc_bogo_help_text"><?php esc_html_e( 'Type in prefix/ suffix if required', 'wt-smart-coupons-for-woocommerce' ); ?></span>
				</div>
			</div>
		</td>
	</tr>

	<tr>
		<th>
			<label><?php esc_html_e( 'Offer Code Length', 'wt-smart-coupons-for-woocommerce' ); ?></label>
		</th>
		<td>
			<div class="wbte_sc_bulk_bogo_tooltip_div_td">
				<?php echo wp_kses_post( wc_help_tip( __( 'Set how many characters the auto-generated code should include', 'wt-smart-coupons-for-woocommerce' ) ) ); ?>
				<div>
					<input type="text" id="_wt_scg_coupon_length" name="_wt_scg_coupon_length" class="wbte_sc_admin_number_input wbte_sc_admin_input_only_number" value="">
				</div>
			</div>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<label><?php esc_html_e( 'Display Offer On', 'wt-smart-coupons-for-woocommerce' ); ?></label>
		</th>
		<td>
			<div class="wbte_sc_bulk_bogo_tooltip_div_td" style="align-items: flex-start;">
				<?php
				echo '<span style="margin-top: -1px;">' . wp_kses_post( wc_help_tip( __( 'Choose where shoppers will see the coupon offer', 'wt-smart-coupons-for-woocommerce' ) ) ) . '</span>';

				$make_coupon_available = array(
					'my_account' => esc_html__( 'My Account', 'wt-smart-coupons-for-woocommerce' ),
					'checkout'   => esc_html__( 'Checkout', 'wt-smart-coupons-for-woocommerce' ),
					'cart'       => esc_html__( 'Cart', 'wt-smart-coupons-for-woocommerce' ),
				);

				echo '<div class="wbte_sc_bulk_bogo_display_checkbox_div">';
				foreach ( $make_coupon_available as $display_slug => $display_title ) {

					echo $ds_obj->get_component(
						'checkbox normal',
						array(
							'values' => array(
								'name'  => '_wc_make_coupon_available[]',
								'id'    => esc_attr( $display_slug ),
								'value' => esc_attr( $display_slug ),
								'label' => esc_attr( $display_title ),
							),
							'class'  => array( 'wbte_sc_bulk_bogo_display_checkbox' ),
						)
					);
					echo '<br>';
				}
				echo '</div>';
				?>
			</div>
		</td>
	</tr>

	<!-- For coupon generator plugin -->
	<tr valign="top" style="display: none;" class="form-field _wt_scg_coupon_option_email_restriction_field">
		<th>
			<label><?php esc_html_e( 'Coupon(s) can be used by', 'wt-smart-coupons-for-woocommerce' ); ?></label>
		</th>
		<td>
			<div class="wbte_sc_bulk_bogo_tooltip_div_td">
				<span style="width: 16px;"></span>
				<div>
					<?php
					echo $ds_obj->get_component(
						'radio-group multi-line',
						array(
							'values' => array(
								'name'  => '_wt_scg_coupon_option_email_restriction',
								'items' => array(
									array(
										'label'      => esc_html__( 'Email recipients', 'wt-smart-coupons-for-woocommerce' ),
										'value'      => 'email_recipients',
										'is_checked' => true,
									),
									array(
										'label' => esc_html__( 'Anyone', 'wt-smart-coupons-for-woocommerce' ),
										'value' => 'anyone',
									),
								),
							),
						)
					);
					?>
				</div>
			</div>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<label><?php esc_html_e( 'Schedule offer', 'wt-smart-coupons-for-woocommerce' ); ?></label>
		</th>
		<td>
			<div class="wbte_sc_bulk_bogo_tooltip_div_td">
				<?php echo wp_kses_post( wc_help_tip( __( 'Enable this option to run the BOGO offer during a specific time period', 'wt-smart-coupons-for-woocommerce' ) ) ); ?>
				<?php
				echo $ds_obj->get_component(
					'checkbox normal',
					array(
						'values' => array(
							'name'  => 'wbte_sc_bogo_schedule[]',
							'id'    => 'wbte_sc_bogo_schedule',
							'value' => 'yes',
						),
					)
				);
				?>
			</div>
		</td>
	</tr>
</table>


	<table class="wbte_sc_bulk_bogo_table" id="wbte_sc_bogo_schedule_content" style="display: none;">
		<tr>
			<th><label><?php esc_html_e( 'Starts on', 'wt-smart-coupons-for-woocommerce' ); ?></label></th>
			<td>
				<div class="wbte_sc_bulk_bogo_tooltip_div_td">
					<?php echo wp_kses_post( wc_help_tip( __( 'Choose when the BOGO offer will become active on your store', 'wt-smart-coupons-for-woocommerce' ) ) ); ?>
					<div class="wbte_sc_schedule_field_row">
						<input type="date" class="wbte_sc_bogo_date_picker" id="_wt_coupon_start_date" name="_wt_coupon_start_date" value="" min="<?php echo esc_attr( $today_date ); ?>">
					</div>
				</div>
			</td>
		</tr>

		<tr class="wbte_sc_schedule_expiry_div">
			<th><label><?php esc_html_e( 'Ends in', 'wt-smart-coupons-for-woocommerce' ); ?></label></th>
			<td>
				<div class="wbte_sc_bulk_bogo_tooltip_div_td">
					<?php echo wp_kses_post( wc_help_tip( __( 'Set a specific end date and time for the BOGO offer', 'wt-smart-coupons-for-woocommerce' ) ) ); ?>
					<div class="wbte_sc_schedule_field_row wbte_sc_schedule_expiry_field_row">
						<input type="date" class="wbte_sc_bogo_date_picker" id="expiry_date" name="expiry_date"  value="" min="<?php echo esc_attr( $today_date ); ?>">
					</div>
				</div>
			</td>
		</tr>
	</table>


<script>
	jQuery(document).ready(function($) {
		let storedBogoContainer = $('#wbte_sc_bulk_bogo_container').detach();
		let storedCouponMetaBox = null;
		const is_wt_scg = $('.wt_scg_container').length > 0;
		
		$( document ).on( 'change', '#discount_type', function() {
			const isBogoType = 'wbte_sc_bogo' === $(this).val();
			const $bogoContainer = $('#wbte_sc_bulk_bogo_container');
			let $couponMetaBox = $('#wt-coupon-meta-box');
			let $appendArea = $('.wbte_sc_bulk_generate_coupon_button');
			
			if( is_wt_scg ){
				$couponMetaBox = $('#wt_scg_coupon_meta_box');
				$appendArea = $('.wt_scg_coupon_settings_hd');
			}
			
			let $dropDown = $('.wbte_sc_bulk_bogo_discount_type_dropdown');
			
			if (isBogoType) {
				if ($couponMetaBox.length) {
					storedCouponMetaBox = $couponMetaBox.detach();
				}
				
				if ( ! $bogoContainer.length && storedBogoContainer ) {
					if (is_wt_scg) {
						$appendArea.after(storedBogoContainer);
						$( 'tr[data-row="wbte_sc_bogo_email_row"], p[data-row="wbte_sc_bogo_email_row"]' ).remove();
						if( $( '#wt_scg_use_coupons_to__email' ).is(':checked') ){
							$( '.form-field._wt_scg_coupon_option_email_restriction_field' ).show();
						}
					} else {
						$appendArea.before(storedBogoContainer);
					}
					$( '#wbte_sc_bulk_bogo_container' ).show();
				}

				let $dropDown = $('.wbte_sc_bulk_bogo_discount_type_dropdown');
				
				const $dropDownMenu = $dropDown.find('.wbte_sc_bogo_edit_custom_drop_down');
				const $bogoOption = $dropDownMenu.find('p[data-val="wbte_sc_bogo"]');
				
				$dropDownMenu.find('p')
					.removeClass('wbte_sc_disabled')
					.find('.wbte_sc_dropdown_selected_icon')
					.remove();
								
				$dropDown.find('.wbte_sc_bogo_edit_custom_drop_down_btn p')
					.html(wbte_sc_bogo_params.text.bogo);
				
				$bogoOption.addClass('wbte_sc_disabled')
					.append(`<img class="wbte_sc_dropdown_selected_icon" src="${wbte_sc_bogo_params.urls.image_path}selected_grey.svg">`);
				
			} else {
				$dropDown.find('.wbte_sc_bogo_edit_custom_drop_down').hide();
				
				if ($bogoContainer.length) {
					storedBogoContainer = $bogoContainer.detach();
				}
				
				if (!$couponMetaBox.length && storedCouponMetaBox) {
					if (is_wt_scg) {
						$appendArea.after(storedCouponMetaBox);
					} else {
						$appendArea.before(storedCouponMetaBox);
					}
				}
			}
			
			$('#discount_type').val($(this).val());
		});
	});

</script>