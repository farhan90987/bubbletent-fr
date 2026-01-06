<?php
/**
 * Premium Upgrade Page Content
 *
 * @link
 * @since 1.4.4
 * @since 1.8.1 New Design
 *
 * @package  Wt_Smart_Coupon
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
$image_path = esc_url( WT_SMARTCOUPON_MAIN_URL . 'admin/modules/premium_upgrade/assets/images/' );
?>
<style type="text/css">

*{ box-sizing: border-box; }
#wpbody{ margin-left: -20px; }
.wbte_sc_upg_to_pro_flex_row, .wbte_sc_upg_to_pro_box_head_row, .wbte_sc_upg_to_pro_head_rating, .wbte_sc_upg_to_pro_yt_btn{ display: flex; }
.wbte_sc_upg_to_pro_flex_row{ padding: 45px 45px 0 45px; column-gap: 45px; }
.wbte_sc_upg_to_pro_flex_row_alternate{ flex-direction: row-reverse; }
.wbte_sc_upg_to_pro_flex_row_left{ width: 40%; }
.wbte_sc_upg_to_pro_flex_row_right{ width: 70%; }
.wbte_sc_upg_to_pro_small_box, .wbte_sc_upg_to_pro_large_box{ padding: 28px 30px; }
.wbte_sc_upg_to_pro_flex_row_left, .wbte_sc_upg_to_pro_flex_row_right{ border-radius: 20px; background-color: white; box-shadow: 0px 2px 10px 2px #55657D1A; position: relative; }
.wbte_sc_upg_to_box ul{ list-style-image: url( '../wp-content/plugins/wt-smart-coupons-for-woocommerce/admin/images/tick.svg' ); margin-left: 15px; }
.wbte_sc_upg_to_pro_head{ margin-left: 16px; }
.wbte_sc_upg_to_pro_head p{ margin: 0; }
.wbte_sc_upg_to_pro_head_title{ font-weight: 700; font-size: 15px; }
.wbte_sc_upg_to_pro_head_rating p{ font-size: 12px; font-weight: 500; }
.wbte_sc_upg_to_pro_icon{ width: 40px; }
.wbte_sc_upg_to_pro_subhead{ margin: 23px 0; font-size: 14px; }
.wbte_sc_upg_to_pro_features_hidden, .wbte_sc_upg_to_pro_features_view_less{ display: none; }
.wbte_sc_upg_to_pro_features_view_all, .wbte_sc_upg_to_pro_features_view_less{ color: #3171FB; font-weight: 500; font-size: 13px; text-align: center; cursor: pointer; text-decoration: underline; }
.wbte_sc_upg_to_pro_see_more_div{ margin-bottom: 70px; }
.wbte_sc_upg_to_pro_box_footer{ display: flex; gap: 26px; position: absolute; bottom: 33px; }
.wbte_sc_upg_to_pro_large_box .wbte_sc_upg_to_pro_box_footer{ gap: 28px; }
.wbte_sc_upg_to_pro_plugin_btn{ text-decoration: none; background-color: #3171FB; font-size: 13px; font-weight: 600; border-radius: 6px; color: white; padding: 12px 25px; }
.wbte_sc_upg_to_pro_plugin_btn:hover{ color: white; }
.wbte_sc_upg_to_pro_yt_btn p{ font-weight: 400; color: #3171FB; text-decoration: underline; margin: 0; }
.wbte_sc_upg_to_pro_yt_btn{ align-items: center; cursor: pointer; gap: 10px; justify-content: center; }
.wbte_sc_upg_to_pro_play_btn{ border-radius: 50px; background-color: #E5EDFF; width: 25px; height: 25px; display: flex; justify-content: center; align-items: center; color: #3171FB; }
.wbte_sc_upg_to_pro_play_btn .dashicons{ margin-left: 3px; }
.wbte_sc_upg_to_pro_large_box_content{ width: 60%; }
.wbte_sc_upg_to_pro_plugin_image{ position: absolute; bottom: 0; right: 0; border-bottom-right-radius: 20px; }
.wbte_sc_upg_to_pro_premium_crown_img{ top: -25px; right: -28px; position: absolute; }
.wbte_sc_upg_to_pro_premium_features{ padding: 50px; display: flex; flex-direction: column; position: relative; top: 50%; transform: translateY(-50%); }
.wbte_sc_upg_to_pro_premium_features div{ display: flex; gap: 20px; }
.wbte_sc_upg_to_pro_premium_features p{ font-size: 15px; font-weight: 500; }
.wbte_sc_upg_to_pro_special_tag{ background-color: #FFEFC5; border-radius: 50px; width: max-content; padding: 5px 12px; margin-bottom: 25px; }
.wbte_sc_upg_to_pro_special_tag p{ margin: 0; font-size: 10px; font-weight: 500; color: #936A00; }
.wbte_sc_upg_to_pro_yt_video_player { display:none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0, 0, 0, 0.4); align-items: center; justify-content: center; }

@media only screen and (max-width:1150px) {
	.wbte_sc_upg_to_pro_large_box_content{ width: 55%; }
}

@media only screen and (max-width:960px) {
	.wbte_sc_upg_to_pro_flex_row{ flex-direction: column; gap: 45px; }
	.wbte_sc_upg_to_pro_flex_row_left, .wbte_sc_upg_to_pro_flex_row_right{ width: 100%; }
	.wbte_sc_upg_to_pro_plugin_image{ position: static; }
	.wbte_sc_upg_to_pro_premium_features{ transform: none; }
	.wbte_sc_upg_to_pro_box_footer{ position: static; justify-content: center; }
	.wbte_sc_upg_to_pro_premium_features{ padding: 35px; }
}

@media only screen and (max-width:600px) {
	.wbte_sc_upg_to_pro_flex_row{ padding: 35px 35px 0 35px; gap: 35px; }
	.wbte_sc_upg_to_pro_flex_row_right{ background: white !important; }
	.wbte_sc_upg_to_pro_large_box_content{ width: 100%; }
	.wbte_sc_upg_to_pro_premium_features{ padding: 25px; }
}
</style>

<?php
	$cards_row = array(
		'sc_automation'                     => array(
			'small_box' => array(
				'title'           => __( 'Smart Coupons for WooCommerce', 'wt-smart-coupons-for-woocommerce' ),
				'subhead'         => __( 'Get advanced WooCommerce coupon features to make irresistible coupon campaigns and discounts', 'wt-smart-coupons-for-woocommerce' ),
				'features'        => array(
					__( 'Advanced Buy (X) Get (Y) BOGO offers', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Giveaways and free products', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Offer store credits and gift cards', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Create exclusive coupons based on purchase history', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Offer sign-up coupons to new users', 'wt-smart-coupons-for-woocommerce' ),
				),
				'hidded_features' => array(
					__( 'Save lost sales with cart abandonment coupons', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Create location-specific coupons', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Bulk generate coupons', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Display coupon banners and widgets', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Import coupons from CSV', 'wt-smart-coupons-for-woocommerce' ),
				),
				'plugin_url'      => 'https://www.webtoffee.com/product/smart-coupons-for-woocommerce/?utm_source=free_plugin_premium_upgrade_page&utm_medium=smart_coupons_basic&utm_campaign=smart_coupons&utm_content=',
				'video_url'       => 'https://www.youtube.com/embed/IY4cmdUBw4A?si=yHzKaXZSQirWLTyW',
				'icon_name'       => 'plugin_pro_icon.svg',
				'bg_color'        => '#FFFEFA',
				'border_color'    => '#FFB803',
			),
			'large_box' => array(
				'title'           => __( 'WebToffee WooCommerce Marketing Automation App', 'wt-smart-coupons-for-woocommerce' ),
				'subhead'         => __( 'Streamline your eCommerce marketing with automation. Create dynamic popups, web campaigns, and marketing emails all in one place.', 'wt-smart-coupons-for-woocommerce' ),
				'features'        => array(
					__( 'Dynamic Popups: Exit-intent, cart abandonment, and lead generation', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Email Marketing Automation: Welcome emails, cart recovery, win-back promotions, and thank-you emails', 'wt-smart-coupons-for-woocommerce' ),
				),
				'hidded_features' => array(
					__( 'Email Customizer for personalized messaging', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Pre-built Templates to save time', 'wt-smart-coupons-for-woocommerce' ),
					__( 'In-depth Analytics to track and optimize your performance', 'wt-smart-coupons-for-woocommerce' ),
				),
				'plugin_url'      => 'https://www.webtoffee.com/ecommerce-marketing-automation/?utm_source=free_plugin_premium_upgrade_page&utm_medium=smart_coupons_basic&utm_campaign=EMA&utm_content=',
				'video_url'       => '',
				'icon_name'       => 'marketing_automation_icon.svg',
				'image_name'      => 'marketing_automation_image.svg',
				'special_tag'     => __( 'INTRODUCING 🔥', 'wt-smart-coupons-for-woocommerce' ),
				'has_rating'      => false,
			),
		),
		'recomendation_premium_point' => array(
			'small_box' => array(
				'bg_color'     => '#F9FFF7',
				'border_color' => '#AAEFB9',
			),
			'large_box' => array(
				'title'           => __( 'WooCommerce Product Recommendations', 'wt-smart-coupons-for-woocommerce' ),
				'subhead'         => __( 'Create smart product recommendation campaigns on your WooCommerce store', 'wt-smart-coupons-for-woocommerce' ),
				'features'        => array(
					__( 'Built-in recommendation templates', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Bestsellers, New arrivals, FBT, Recently viewed & more', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Supports all types of WooCommerce products', 'wt-smart-coupons-for-woocommerce' ),
				),
				'hidded_features' => array(
					__( 'Advanced conditions for showing recommendations', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Recommend products on different store pages', 'wt-smart-coupons-for-woocommerce' ),
				),
				'plugin_url'      => 'https://www.webtoffee.com/product/woocommerce-product-recommendations/?utm_source=free_plugin_premium_upgrade_page&utm_medium=smart_coupons_basic&utm_campaign=Product_Recommendations&utm_content=',
				'video_url'       => 'https://www.youtube.com/embed/_-5SESD3Ez0?si=qYn7zu902DsFRmE2',
				'icon_name'       => 'recomendation_icon.svg',
				'image_name'      => 'recomendation_image.png',
			),
		),
		'generator_giftcard'          => array(
			'small_box' => array(
				'title'      => __( 'WooCommerce Coupon Generator', 'wt-smart-coupons-for-woocommerce' ),
				'subhead'    => __( 'Bulk generate hundreds or thousands of WooCommerce coupons with few clicks', 'wt-smart-coupons-for-woocommerce' ),
				'features'   => array(
					__( 'Create unique WooCommerce coupons', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Add prefixes & suffixes to custom coupon codes', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Email coupons to multiple recipients', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Advanced usage restrictions for coupons', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Export bulk generated coupons to CSV', 'wt-smart-coupons-for-woocommerce' ),
				),
				'plugin_url' => 'https://www.webtoffee.com/product/woocommerce-coupon-generator/?utm_source=free_plugin_premium_upgrade_page&utm_medium=smart_coupons_basic&utm_campaign=Coupon_Generator&utm_content=',
				'video_url'  => 'https://www.youtube.com/embed/AdAPmyiNXzw?si=11FHz3KgAIsT87ms',
				'icon_name'  => 'bulk_generate_icon.svg',
				'bg_color'   => '#FCF2FF',
			),
			'large_box' => array(
				'title'           => __( 'WooCommerce Gift Cards', 'wt-smart-coupons-for-woocommerce' ),
				'subhead'         => __( 'Create digital and physical gift cards and allow your customers to buy, redeem, and share', 'wt-smart-coupons-for-woocommerce' ),
				'features'        => array(
					__( 'Unlimited number of gift cards', 'wt-smart-coupons-for-woocommerce' ),
					__( '20+ free gift card templates', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Provide instant refunds to store credits', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Upload custom images for gift cards', 'wt-smart-coupons-for-woocommerce' ),
				),
				'hidded_features' => array(
					__( 'Gift this product option', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Send free gift cards to users', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Sell physical gift cards', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Schedule gift cards for auto-delivery', 'wt-smart-coupons-for-woocommerce' ),
				),
				'plugin_url'      => 'https://www.webtoffee.com/product/woocommerce-gift-cards/?utm_source=free_plugin_premium_upgrade_page&utm_medium=smart_coupons_basic&utm_campaign=WooCommerce_Gift_Cards&utm_content=',
				'video_url'       => 'https://www.youtube.com/embed/bKmGBG9U1uY?si=3pom110IDDAmiXew',
				'icon_name'       => 'gift_cards_icon.svg',
				'image_name'      => 'giftcards_image.svg',
			),
		),
		'feed_url'                    => array(
			'small_box' => array(
				'title'      => __( 'URL Coupons for WooCommerce', 'wt-smart-coupons-for-woocommerce' ),
				'subhead'    => __( 'Generate unique coupon URLs and QR code discounts for promotional offers', 'wt-smart-coupons-for-woocommerce' ),
				'features'   => array(
					__( 'Create unique coupon URLs', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Generate QR codes for coupons', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Click to apply coupons', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Automatically add products to cart', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Redirect users to specific pages', 'wt-smart-coupons-for-woocommerce' ),
				),
				'plugin_url' => 'https://www.webtoffee.com/product/url-coupons-for-woocommerce/?utm_source=free_plugin_premium_upgrade_page&utm_medium=smart_coupons_basic&utm_campaign=URL_Coupons&utm_content=',
				'video_url'  => 'https://www.youtube.com/embed/80JyXvalx6E?si=OCyEKY1GL3Rxt2DO',
				'icon_name'  => 'url_coupon_icon.svg',
				'bg_color'   => '#FFF8FE',
			),
			'large_box' => array(
				'title'      => __( 'WooCommerce Product Feed Plugin', 'wt-smart-coupons-for-woocommerce' ),
				'subhead'    => __( 'Get free listings of WooCommerce products on popular eCommerce sales channels', 'wt-smart-coupons-for-woocommerce' ),
				'features'   => array(
					__( 'Generate product feed for 20+ popular sales channels', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Supports Google Shopping programs', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Server cron for automated feed updates', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Advanced filters and conditions for product feed', 'wt-smart-coupons-for-woocommerce' ),
					__( 'Multilingual and Multicurrency Support', 'wt-smart-coupons-for-woocommerce' ),
				),
				'plugin_url' => 'https://www.webtoffee.com/product/woocommerce-product-feed/?utm_source=free_plugin_premium_upgrade_page&utm_medium=smart_coupons_basic&utm_campaign=WooCommerce_Product_Feed&utm_content=',
				'video_url'  => '',
				'icon_name'  => 'feed_icon.svg',
				'image_name' => 'feed_image.svg',
			),
		),
	);

	$cards_row_index = 1;
	foreach ( $cards_row as $cards_row_key => $cards_row_value ) {
		?>
			<div class="wbte_sc_upg_to_pro_flex_row <?php echo 0 === $cards_row_index % 2 ? 'wbte_sc_upg_to_pro_flex_row_alternate' : ''; ?>">
				<?php
					$style  = '';
					$style .= isset( $cards_row_value['small_box']['bg_color'] ) ? 'style = "background-color: ' . $cards_row_value['small_box']['bg_color'] . '; ' : '';
					$style .= isset( $cards_row_value['small_box']['border_color'] ) ? 'border: 1px solid ' . $cards_row_value['small_box']['border_color'] . '"' : '"';
				?>
				<div class="wbte_sc_upg_to_pro_flex_row_left" <?php echo wp_kses_post( $style ); ?>>
					<?php
					if ( isset( $cards_row_value['small_box']['title'] ) ) {
						?>
						<div class="wbte_sc_upg_to_pro_small_box wbte_sc_upg_to_box">
							<div class="wbte_sc_upg_to_pro_box_head_row">
								<img class="wbte_sc_upg_to_pro_icon" src="<?php echo esc_attr( esc_url( $image_path . $cards_row_value['small_box']['icon_name'] ) ); ?>" alt="<?php esc_attr_e( 'Plugin icon', 'wt-smart-coupons-for-woocommerce' ); ?>">
								<div class="wbte_sc_upg_to_pro_head">
									<p class="wbte_sc_upg_to_pro_head_title"><?php echo esc_html( $cards_row_value['small_box']['title'] ); ?></p>
									<div class="wbte_sc_upg_to_pro_head_rating">
										<img src="<?php echo esc_attr( esc_url( "{$image_path}5_star.svg" ) ); ?>" alt="<?php esc_attr_e( 'Star rating', 'wt-smart-coupons-for-woocommerce' ); ?>">
										<p>&nbsp;<?php esc_html_e( '4.8', 'wt-smart-coupons-for-woocommerce' ); ?></p>
									</div>
								</div>
							</div>
							<p class="wbte_sc_upg_to_pro_subhead"><?php echo esc_html( $cards_row_value['small_box']['subhead'] ); ?></p>
							<ul>
								<?php
								foreach ( $cards_row_value['small_box']['features'] as $feature ) {
									echo '<li>' . esc_html( $feature ) . '</li>';
								}
								if ( isset( $cards_row_value['small_box']['hidded_features'] ) ) {
									echo '<div class="wbte_sc_upg_to_pro_features_hidden">';
									foreach ( $cards_row_value['small_box']['hidded_features'] as $feature ) {
										echo '<li>' . esc_html( $feature ) . '</li>';
									}
									echo '</div>';
								}
								?>
							</ul>
							<div class="wbte_sc_upg_to_pro_see_more_div">
							<?php
							if ( isset( $cards_row_value['small_box']['hidded_features'] ) ) {
								?>
									<p class="wbte_sc_upg_to_pro_features_view_all"><?php esc_html_e( 'Show more', 'wt-smart-coupons-for-woocommerce' ); ?></p>
									<p class="wbte_sc_upg_to_pro_features_view_less"><?php esc_html_e( 'Show less', 'wt-smart-coupons-for-woocommerce' ); ?></p>
									<?php
							}
							?>
							</div>
							<div class="wbte_sc_upg_to_pro_box_footer">
								<a class="wbte_sc_upg_to_pro_plugin_btn" href="<?php echo esc_attr( esc_url( $cards_row_value['small_box']['plugin_url'] . WEBTOFFEE_SMARTCOUPON_VERSION ) ); ?>" target="_blank"><?php esc_html_e( 'Checkout plugin', 'wt-smart-coupons-for-woocommerce' ); ?></a>
								<?php
								if ( isset( $cards_row_value['small_box']['video_url'] ) && ! empty( $cards_row_value['small_box']['video_url'] ) ) {
									?>
									<div class="wbte_sc_upg_to_pro_yt_btn" data-yt-url="<?php echo esc_attr( esc_url( $cards_row_value['small_box']['video_url'] ) ); ?>">
										<div class="wbte_sc_upg_to_pro_play_btn">
											<span class="dashicons dashicons-controls-play"></span>
										</div>
										<p><?php esc_html_e( 'See, How this works', 'wt-smart-coupons-for-woocommerce' ); ?></p>
									</div>
									<?php
								}
								?>
							</div>
							<img class="wbte_sc_upg_to_pro_premium_crown_img" src="<?php echo esc_attr( esc_url( "{$image_path}premium_crown.svg" ) ); ?>" alt="<?php esc_attr_e( 'Premium crown', 'wt-smart-coupons-for-woocommerce' ); ?>">
						</div>
						<?php
					} else {
						?>
						<div class="wbte_sc_upg_to_pro_premium_features">
							<p style="margin-top: 0px;"><?php esc_html_e( 'Try with Confidence', 'wt-smart-coupons-for-woocommerce' ); ?></p>
							<div>
								<img src="<?php echo esc_attr( esc_url( "{$image_path}money_icon.svg" ) ); ?>" alt="<?php esc_attr_e( 'Money back', 'wt-smart-coupons-for-woocommerce' ); ?>">
								<p><?php esc_html_e( '30 Day Money Back Guarantee', 'wt-smart-coupons-for-woocommerce' ); ?></p>
							</div>
							<div>
								<img src="<?php echo esc_attr( esc_url( "{$image_path}support_icon.svg" ) ); ?>" alt="<?php esc_attr_e( 'Support', 'wt-smart-coupons-for-woocommerce' ); ?>">
								<p><?php esc_html_e( 'Fast and Priority Support', 'wt-smart-coupons-for-woocommerce' ); ?></p>
							</div>
							<div>
								<img src="<?php echo esc_attr( esc_url( "{$image_path}love_icon.svg" ) ); ?>" alt="<?php esc_attr_e( 'Satisfaction', 'wt-smart-coupons-for-woocommerce' ); ?>">
								<p><?php esc_html_e( '99% Satisfaction rating', 'wt-smart-coupons-for-woocommerce' ); ?></p>
							</div>
						</div>
						<?php
					}
					?>
					
				</div>
				<div class="wbte_sc_upg_to_pro_flex_row_right" style="background:url(<?php echo esc_attr( esc_url( $image_path . $cards_row_value['large_box']['image_name'] ) ); ?>), white; background-position: center right; background-repeat: no-repeat; background-size: 35% auto;">
					<div class="wbte_sc_upg_to_pro_large_box wbte_sc_upg_to_box">
						<?php
						if ( isset( $cards_row_value['large_box']['special_tag'] ) ) {
							?>
								<div class="wbte_sc_upg_to_pro_special_tag">
									<p><?php echo esc_html( $cards_row_value['large_box']['special_tag'] ); ?></p>
								</div>
								<?php
						}
						?>
						<div class="wbte_sc_upg_to_pro_large_box_content">
							<div class="wbte_sc_upg_to_pro_box_head_row">
								<img class="wbte_sc_upg_to_pro_icon" src="<?php echo esc_attr( esc_url( $image_path . $cards_row_value['large_box']['icon_name'] ) ); ?>" alt="<?php esc_attr_e( 'Plugin icon', 'wt-smart-coupons-for-woocommerce' ); ?>">
								<div class="wbte_sc_upg_to_pro_head">
									<p class="wbte_sc_upg_to_pro_head_title"><?php echo esc_html( $cards_row_value['large_box']['title'] ); ?></p>
									<?php if ( ! isset( $cards_row_value['large_box']['has_rating'] ) || $cards_row_value['large_box']['has_rating'] ) : 
										?>
										<div class="wbte_sc_upg_to_pro_head_rating">
											<img src="<?php echo esc_attr( esc_url( "{$image_path}5_star.svg" ) ); ?>" alt="<?php esc_attr_e( 'Star rating', 'wt-smart-coupons-for-woocommerce' ); ?>">
										<p>&nbsp;<?php esc_html_e( '4.8', 'wt-smart-coupons-for-woocommerce' ); ?></p>
									</div>
									<?php endif; ?>
								</div>
							</div>
							<p class="wbte_sc_upg_to_pro_subhead"><?php echo esc_html( $cards_row_value['large_box']['subhead'] ); ?></p>
							<ul>
							<?php
							foreach ( $cards_row_value['large_box']['features'] as $feature ) {
								echo '<li>' . esc_html( $feature ) . '</li>';
							}
							if ( isset( $cards_row_value['large_box']['hidded_features'] ) ) {
								echo '<div class="wbte_sc_upg_to_pro_features_hidden">';
								foreach ( $cards_row_value['large_box']['hidded_features'] as $feature ) {
									echo '<li>' . esc_html( $feature ) . '</li>';
								}
								echo '</div>';
							}
							?>
							</ul>
							<div class="wbte_sc_upg_to_pro_see_more_div">
							<?php
							if ( isset( $cards_row_value['large_box']['hidded_features'] ) ) {
								?>
								<p class="wbte_sc_upg_to_pro_features_view_all"><?php esc_html_e( 'Show more', 'wt-smart-coupons-for-woocommerce' ); ?></p>
								<p class="wbte_sc_upg_to_pro_features_view_less"><?php esc_html_e( 'Show less', 'wt-smart-coupons-for-woocommerce' ); ?></p>
								<?php
							}
							?>
							</div>
							<div class="wbte_sc_upg_to_pro_box_footer">
								<a class="wbte_sc_upg_to_pro_plugin_btn" href="<?php echo esc_attr( esc_url( $cards_row_value['large_box']['plugin_url'] . WEBTOFFEE_SMARTCOUPON_VERSION ) ); ?>" target="_blank"><?php esc_html_e( 'Checkout plugin', 'wt-smart-coupons-for-woocommerce' ); ?></a>
								<?php
								if ( isset( $cards_row_value['large_box']['video_url'] ) && ! empty( $cards_row_value['large_box']['video_url'] ) ) {
									?>
									<div class="wbte_sc_upg_to_pro_yt_btn" data-yt-url="<?php echo esc_attr( esc_url( $cards_row_value['large_box']['video_url'] ) ); ?>">
										<div class="wbte_sc_upg_to_pro_play_btn">
											<span class="dashicons dashicons-controls-play"></span>
										</div>
										<p><?php esc_html_e( 'See, How this works', 'wt-smart-coupons-for-woocommerce' ); ?></p>
									</div>
									<?php
								}
								?>
							</div>
						</div>
						<img class="wbte_sc_upg_to_pro_premium_crown_img" src="<?php echo esc_attr( esc_url( "{$image_path}premium_crown.svg" ) ); ?>" alt="<?php esc_attr_e( 'Premium crown', 'wt-smart-coupons-for-woocommerce' ); ?>">
					</div>
				</div>
			</div>
		<?php
		++$cards_row_index;
	}
	?>

<div class="wbte_sc_upg_to_pro_yt_video_player">
	<iframe width="560" height="315" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
</div>

<script>
	jQuery(document).ready(function( $ ){

		$('.wbte_sc_upg_to_pro_features_view_all').on( 'click', function(){
			$(this).closest('.wbte_sc_upg_to_box').find('.wbte_sc_upg_to_pro_features_hidden').show();
			$(this).closest('.wbte_sc_upg_to_box').find('.wbte_sc_upg_to_pro_features_view_all').hide();
			$(this).closest('.wbte_sc_upg_to_box').find('.wbte_sc_upg_to_pro_features_view_less').show();
		});

		$('.wbte_sc_upg_to_pro_features_view_less').on( 'click', function(){
			$(this).closest('.wbte_sc_upg_to_box').find('.wbte_sc_upg_to_pro_features_hidden').hide();
			$(this).closest('.wbte_sc_upg_to_box').find('.wbte_sc_upg_to_pro_features_view_all').show();
			$(this).closest('.wbte_sc_upg_to_box').find('.wbte_sc_upg_to_pro_features_view_less').hide();
		});

		$( '.wbte_sc_upg_to_pro_yt_btn' ).on( 'click', function (e) {
			e.preventDefault();
			const video_url = $( this ).attr( 'data-yt-url' );
			$( '.wbte_sc_upg_to_pro_yt_video_player iframe' ).attr( 'src', video_url );
			$( '.wbte_sc_upg_to_pro_yt_video_player' ).css('display','flex',);
		});

		$( window ).on( 'click', function(e) {
			if ( e.target.className.includes( 'wbte_sc_upg_to_pro_yt_video_player' ) ) {
				$( '.wbte_sc_upg_to_pro_yt_video_player' ).hide();
				$( '.wbte_sc_upg_to_pro_yt_video_player iframe' ).attr( 'src', '' );
			}
		});
	});
</script>