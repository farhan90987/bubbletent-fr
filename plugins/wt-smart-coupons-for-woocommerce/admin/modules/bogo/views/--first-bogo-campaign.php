<?php
/**
 * First BOGO setup page
 *
 * @package    Wt_Smart_Coupon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<h2><?php esc_html_e( 'Create your first BOGO offer', 'wt-smart-coupons-for-woocommerce' ); ?></h2>
	<div class="wbte_sc_new_campaign_default">
		<!-- Buy X Get X @ 50% -->
		<div>
			<div class="wbte_sc_new_campaign_box default" data-default-btn="default_1">
				<?php echo wp_kses_post( $discount_tag_img ); ?>
				<p><?php esc_html_e( 'Buy X Get X @ 50%', 'wt-smart-coupons-for-woocommerce' ); ?></p>
			</div>
			<p class="wbte_sc_new_campaign_box_default_tooltip"><?php esc_html_e( 'Buy 1 item, get 1 @ 50% off. Buy 2 items, get 2 @ 50% off — and so on! Buy more, Get more', 'wt-smart-coupons-for-woocommerce' ); ?></p>
		</div>
		<!-- Buy 2 Get a free gift -->
		<div>
			<div class="wbte_sc_new_campaign_box default" data-default-btn="default_2">
				<?php echo wp_kses_post( $discount_tag_img ); ?>
				<p><?php esc_html_e( 'Buy 2 Get a free gift', 'wt-smart-coupons-for-woocommerce' ); ?></p>
			</div>
			<p class="wbte_sc_new_campaign_box_default_tooltip"><?php esc_html_e( 'Buy two products, get a free gift.', 'wt-smart-coupons-for-woocommerce' ); ?></p>
		</div>
		<!-- Spend X Get a free gift -->
		<div>
			<div class="wbte_sc_new_campaign_box default" data-default-btn="default_3">
				<?php echo wp_kses_post( $discount_tag_img ); ?>
				<p>
					<?php
					// Translators: 1: Min amount, here 100.
					echo wp_kses_post( sprintf( __( 'Spend %s, Get a free gift', 'wt-smart-coupons-for-woocommerce' ), wc_price( 100, array( 'decimals' => 0 ) ) ) );
					?>
				</p>
			</div>
			<p class="wbte_sc_new_campaign_box_default_tooltip">
				<?php
				// Translators: 1: Min amount, here 100.
				echo wp_kses_post( sprintf( __( 'Spend %s or above, get a free gift', 'wt-smart-coupons-for-woocommerce' ), wc_price( 100, array( 'decimals' => 0 ) ) ) );
				?>
			</p>
		</div>
		<!-- Custom -->
		<div>
			<div class="wbte_sc_new_campaign_box wbte_sc_new_campaign_box_custom"   data-default-btn="custom">
				<p class="wbte_sc_new_campaign_box_custom_plus">+</p>
				<p><?php esc_html_e( 'Custom', 'wt-smart-coupons-for-woocommerce' ); ?></p>
			</div>
			<p class="wbte_sc_new_campaign_box_default_tooltip"><?php esc_html_e( 'Create Custom BOGO Offer', 'wt-smart-coupons-for-woocommerce' ); ?></p>
		</div>
	</div>
	<form class="wbte_sc_new_campaign_form" id="wbte_sc_new_bogo_coupon" method="POST">
		<div class="wbte_sc_new_campaign_form_contents">
			
			<div class="wbte_sc_bogo_campaign_custom_radio" hidden>
				<p class="wbte_sc_bogo_input_title"><?php esc_html_e( 'Select BOGO type', 'wt-smart-coupons-for-woocommerce' ); ?></p>
				<?php
				echo $ds_obj->get_component(
					'radio-group multi-line',
					array(
						'values' => array(
							'name'  => 'wbte_sc_bogo_type',
							'items' => array(
								array(
									'label'      => esc_html__( 'Buy product X, get product X/Y', 'wt-smart-coupons-for-woocommerce' ),
									'value'      => 'wbte_sc_bogo_bxgx',
									'is_checked' => true,
								),
								array(
									'label'      => esc_html__( 'Cheapest/Most Expensive', 'wt-smart-coupons-for-woocommerce' ) . wp_kses_post( '<img src="' . esc_url( $admin_img_path . 'prem_crown_2.svg' ) . '" alt="' . esc_attr__( 'premium', 'wt-smart-coupons-for-woocommerce' ) . '" />' ),
									'value'      => 'wbte_sc_bogo_cheap_expensive',
									'is_checked' => false,
								),
							),
						),
					)
				);
				?>
				<br>
			</div>
			
			<div class="wbte_sc_cheap_exp_promo_div">
				<img class="wbte_sc_cheap_exp_promo_img" src="<?php echo esc_url( $admin_img_path . 'cheap_exp_promo.svg' ); ?>" alt="<?php esc_attr_e( 'Cheapest/Most Expensive', 'wt-smart-coupons-for-woocommerce' ); ?>">
				<div class="wbte_sc_cheap_exp_promo_detail_div">
					<p>
						<?php esc_html_e( 'Upgrade to premium and offer discounts on the cheapest or most expensive items in the cart.', 'wt-smart-coupons-for-woocommerce' ); ?>
					</p>
				</div>
				<div><a href="<?php echo esc_url( 'https://www.webtoffee.com/product/smart-coupons-for-woocommerce/?utm_source=free_plugin_bogo_popup&utm_medium=smart_coupons_basic&utm_campaign=smart_coupons&utm_content=' . WEBTOFFEE_SMARTCOUPON_VERSION ) ?>" target="_blank" class="wbte_sc_button wbte_sc_button-filled wbte_sc_button-medium" style="text-decoration: none; width: 285px; display:flex; justify-content:center;"><?php esc_html_e( 'Get Premium Now', 'wt-smart-coupons-for-woocommerce' ); ?></a></div>
			</div>

			<div class="wbte_sc_new_campaign_form_fields">
				<label for="wbte_sc_bogo_coupon_name" class="wbte_sc_bogo_input_title"><?php esc_html_e( 'Offer name', 'wt-smart-coupons-for-woocommerce' ); ?></label><br>
				<input type="text" id="wbte_sc_bogo_coupon_name" name="wbte_sc_bogo_coupon_name" class="wbte_sc_bogo_text_input wbte_sc_bogo_restricted_input" placeholder="<?php esc_attr_e( 'Offer name', 'wt-smart-coupons-for-woocommerce' ); ?>" required><br><br>

				<label for="wbte_sc_bogo_campaign_description" class="wbte_sc_bogo_input_title"><?php esc_html_e( 'Description', 'wt-smart-coupons-for-woocommerce' ); ?></label><br>
				<textarea type="text" id="wbte_sc_bogo_campaign_description" name="wbte_sc_bogo_campaign_description" class="wbte_sc_bogo_text_input wbte_sc_bogo_restricted_input" placeholder="<?php esc_attr_e( 'Description', 'wt-smart-coupons-for-woocommerce' ); ?>" rows="5" ></textarea><br><br>

				<input type="hidden" id="wbte_sc_bogo_campaign_selected_default" name="wbte_sc_bogo_campaign_selected_default" value="">

				<input class="wbte_sc_bogo_campaign_submit" type="submit" value="<?php esc_html_e( 'Continue', 'wt-smart-coupons-for-woocommerce' ); ?>">
			</div>

		</div>
		
	</form>