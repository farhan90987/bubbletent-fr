<?php
/**
 * BOGO general settings
 *
 * @package    Wt_Smart_Coupon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$placeholders = array(
	'wbte_sc_bogo_general_discount_apply_message'     => array(
		'{bogo_title}',
	),
	'wbte_sc_bogo_general_product_added_message'      => array(
		'{bogo_title}',
	),
	'wbte_sc_bogo_general_discount_under_product_msg' => array(
		'{bogo_title}',
	),
	'wbte_sc_bogo_general_apply_choose_product_title' => array(
		'{bogo_title}',
	),
);
?>

<div id="wbte_sc_bogo_general_settings" class="wbte_sc_bogo_general_settings">     
	
	<div class="wbte_sc_bogo_general_settings_head">
		<h3><?php esc_html_e( 'General settings', 'wt-smart-coupons-for-woocommerce' ); ?></h3>
		<p class="wbte_sc_bogo_general_settings_close">&times;</p>
	</div>
	<div class="wbte_sc_bogo_general_settings_body">
		<form id="wbte_sc_bogo_general_settings_form" action="POST">

			<p class="wbte_sc_bogo_input_title" style="margin:30px 0 10px 0;"><?php esc_html_e( 'Auto add products for Buy X Get X/Y giveaways', 'wt-smart-coupons-for-woocommerce' ); ?></p>
			<?php
			echo $ds_obj->get_component(
				'radio-group multi-line',
				array(
					'values' => array(
						'name'  => 'wbte_sc_bogo_auto_add_giveaway',
						'items' => array(
							array(
								'label'      => esc_html__( 'Add only free products to cart', 'wt-smart-coupons-for-woocommerce' ),
								'value'      => 'wbte_sc_bogo_auto_add_full_giveaway',
								'is_checked' => esc_attr( 'wbte_sc_bogo_auto_add_full_giveaway' === self::get_general_settings_value( 'wbte_sc_bogo_auto_add_giveaway' ) ),
							),
							array(
								'label'      => esc_html__( 'Add all discounted products to cart', 'wt-smart-coupons-for-woocommerce' ),
								'value'      => 'wbte_sc_bogo_auto_add_all_giveaway',
								'is_checked' => esc_attr( 'wbte_sc_bogo_auto_add_all_giveaway' === self::get_general_settings_value( 'wbte_sc_bogo_auto_add_giveaway' ) ),
							),
						),
					),
				)
			);
			?>

			<p class="wbte_sc_bogo_input_title" style="margin:30px 0 10px 0;"><?php esc_html_e( 'Apply tax on', 'wt-smart-coupons-for-woocommerce' ); ?></p>
			<?php
			echo $ds_obj->get_component(
				'radio-group multi-line',
				array(
					'values' => array(
						'items' => array(
							array(
								'label'      => sprintf(
									// Translators: 1: Tooltip.
									esc_html__( 'Discounted price %s', 'wt-smart-coupons-for-woocommerce' ),
									wp_kses_post( wc_help_tip( __( 'Tax is calculated based on the price after the offer is applied', 'wt-smart-coupons-for-woocommerce' ) ) )
								),
								'is_checked' => true,
							),
							array(
								'label'       => sprintf(
									// Translators: 1: Premium icon, 2: Tooltip.
									esc_html__( 'Original price %s %s', 'wt-smart-coupons-for-woocommerce' ),
									wp_kses_post( '<img class="wbte_sc_bogo_prem_crown_disabled" src="' . esc_url( $admin_img_path . 'prem_crown_2.svg' ) . '" alt="' . esc_attr__( 'premium', 'wt-smart-coupons-for-woocommerce' ) . '" />' ),
									wp_kses_post( wc_help_tip( __( 'Tax is calculated on the original price before the offer is applied.', 'wt-smart-coupons-for-woocommerce' ) ) )
								),
								'is_checked'  => false,
								'is_disabled' => true,
							),
						),
					),
				)
			);
			?>

			<label for="wbte_sc_bogo_general_discount_apply_message" class="wbte_sc_bogo_input_title wbte_sc_bogo_general_label" style="margin-top:30px;"><?php esc_html_e( 'Offer applied message', 'wt-smart-coupons-for-woocommerce' ); ?></label>
			<input type="text" id="wbte_sc_bogo_general_discount_apply_message" name="wbte_sc_bogo_general_discount_apply_message" class="wbte_sc_bogo_text_input" placeholder="<?php esc_attr_e( 'Apply discount', 'wt-smart-coupons-for-woocommerce' ); ?>" value="<?php echo esc_attr( self::get_general_settings_value( 'wbte_sc_bogo_general_discount_apply_message' ) ); ?>">
			<div class="wbte_sc_bogo_help_text">
				<?php
				esc_html_e(
					'Available placeholders: ',
					'wt-smart-coupons-for-woocommerce'
				);
				foreach ( $placeholders['wbte_sc_bogo_general_discount_apply_message'] as $placeholder ) {
					echo "<span class='wbte_sc_bogo_placeholder' id='" . esc_attr( $placeholder ) . "' data-parent-input='wbte_sc_bogo_general_discount_apply_message'>" . esc_html( $placeholder ) . '</span>&nbsp;';
				}
				?>
			</div>

			<label for="wbte_sc_bogo_general_product_added_message" class="wbte_sc_bogo_input_title wbte_sc_bogo_general_label"><?php esc_html_e( 'Product added message', 'wt-smart-coupons-for-woocommerce' ); ?></label>
			<input type="text" id="wbte_sc_bogo_general_product_added_message" name="wbte_sc_bogo_general_product_added_message" class="wbte_sc_bogo_text_input" placeholder="<?php esc_attr_e( 'Product added...', 'wt-smart-coupons-for-woocommerce' ); ?>" value="<?php echo esc_attr( self::get_general_settings_value( 'wbte_sc_bogo_general_product_added_message' ) ); ?>">
			<div class="wbte_sc_bogo_help_text">
				<?php
				esc_html_e(
					'Available placeholders: ',
					'wt-smart-coupons-for-woocommerce'
				);
				foreach ( $placeholders['wbte_sc_bogo_general_product_added_message'] as $placeholder ) {
					echo "<span class='wbte_sc_bogo_placeholder' id='" . esc_attr( $placeholder ) . "' data-parent-input='wbte_sc_bogo_general_product_added_message'>" . esc_html( $placeholder ) . '</span>&nbsp;';
				}
				?>
			</div>

			<label for="wbte_sc_bogo_general_discount_under_product_msg" class="wbte_sc_bogo_input_title wbte_sc_bogo_general_label"><?php esc_html_e( 'Discount info under each item in cart', 'wt-smart-coupons-for-woocommerce' ); ?></label>
			<input type="text" id="wbte_sc_bogo_general_discount_under_product_msg" name="wbte_sc_bogo_general_discount_under_product_msg" class="wbte_sc_bogo_text_input" placeholder="<?php esc_attr_e( 'Discount...', 'wt-smart-coupons-for-woocommerce' ); ?>" value="<?php echo esc_attr( self::get_general_settings_value( 'wbte_sc_bogo_general_discount_under_product_msg' ) ); ?>">
			<div class="wbte_sc_bogo_help_text">
				<?php
				esc_html_e(
					'Available placeholders: ',
					'wt-smart-coupons-for-woocommerce'
				);
				foreach ( $placeholders['wbte_sc_bogo_general_discount_under_product_msg'] as $placeholder ) {
					echo "<span class='wbte_sc_bogo_placeholder' id='" . esc_attr( $placeholder ) . "' data-parent-input='wbte_sc_bogo_general_discount_under_product_msg'>" . esc_html( $placeholder ) . '</span>&nbsp;';
				}
				?>
			</div>

			<label for="wbte_sc_bogo_general_apply_choose_product_title" class="wbte_sc_bogo_input_title wbte_sc_bogo_general_label"><?php esc_html_e( '“Choose product” title', 'wt-smart-coupons-for-woocommerce' ); ?></label>
			<input type="text" id="wbte_sc_bogo_general_apply_choose_product_title" name="wbte_sc_bogo_general_apply_choose_product_title" class="wbte_sc_bogo_text_input" placeholder="<?php esc_attr_e( 'Choose product', 'wt-smart-coupons-for-woocommerce' ); ?>" value="<?php echo esc_attr( self::get_general_settings_value( 'wbte_sc_bogo_general_apply_choose_product_title' ) ); ?>">
			<div class="wbte_sc_bogo_help_text">
				<?php
				esc_html_e(
					'Available placeholders: ',
					'wt-smart-coupons-for-woocommerce'
				);
				foreach ( $placeholders['wbte_sc_bogo_general_apply_choose_product_title'] as $placeholder ) {
					echo "<span class='wbte_sc_bogo_placeholder' id='" . esc_attr( $placeholder ) . "' data-parent-input='wbte_sc_bogo_general_apply_choose_product_title'>" . esc_html( $placeholder ) . '</span>&nbsp;';
				}
				?>
			</div>

			<div class="wbte_sc_bogo_general_settings_btn_div">
				<?php
				echo $ds_obj->get_component(
					'button filled medium',
					array(
						'values' => array(
							'button_title' => esc_html__( 'Update settings', 'wt-smart-coupons-for-woocommerce' ),
						),
						'class'  => array( 'wbte_sc_bogo_update_general_settings' ),
					)
				);
				?>
			</div>
		</form>
	</div>
</div>