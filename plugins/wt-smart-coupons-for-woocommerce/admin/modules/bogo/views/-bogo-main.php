<?php
/**
 * Content of new BOGO page
 *
 * @package    Wt_Smart_Coupon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$ds_obj = Wbte\Sc\Ds\Wbte_Ds::get_instance( WEBTOFFEE_SMARTCOUPON_VERSION );

if ( ! self::is_new_bogo_activated() ) {
	include_once plugin_dir_path( __FILE__ ) . '--new-bogo-switching.php';
	return;
}

if ( isset( $_GET['wbte_bogo_id'] ) ) {
	$coupon    = new WC_Coupon( absint( wp_unslash( $_GET['wbte_bogo_id'] ) ) );
	$coupon_id = $coupon->get_id();
	if ( self::$bogo_coupon_type_name !== $coupon->get_discount_type() ) {
		echo '<h1 style="display: flex; justify-content: center; align-items: center; height: 100vh;">' . esc_html__( 'Provided ID is not a BOGO coupon', 'wt-smart-coupons-for-woocommerce' ) . '</h1>';
		exit;
	}
	include_once plugin_dir_path( __FILE__ ) . '--bogo-edit-page.php';
	return;
}

// Include common BOGO header.
echo $ds_obj->get_component(
	'header',
	array(
		'values' => array(
			'plugin_name'      => 'Smart coupon',
			'developed_by_txt' => esc_html__( 'Developed by', 'wt-smart-coupons-for-woocommerce' ),
			'plugin_logo' => esc_url( $admin_img_path . 'voucher_tag.svg' ),
		),
	)
);


$discount_tag_img = '<img src="' . esc_url( $admin_img_path ) . 'bogo_discount_tag.svg" alt="' . esc_attr__( 'Discount tag', 'wt-smart-coupons-for-woocommerce' ) . '">';
require_once plugin_dir_path( __FILE__ ) . '--bogo-main-general.php';
$_all_bogo_coupon_count = self::get_total_bogo_counts();
?>
<div class="wbte_sc_bogo_body">
	<div class="wbte_sc_bogo_outer_box <?php echo ( 0 >= $_all_bogo_coupon_count ) ? '' : 'wbte_sc_bogo_outer_box_listing'; ?>">
		<div class="wbte_sc_bogo_general_settings_button">
			<img src="<?php echo esc_url( $admin_img_path ); ?>settings_gear.svg" alt="<?php esc_attr_e( 'Settings', 'wt-smart-coupons-for-woocommerce' ); ?>" title="<?php esc_attr_e( 'General settings', 'wt-smart-coupons-for-woocommerce' ); ?>">
		</div>
		<?php
		if ( 0 >= $_all_bogo_coupon_count ) {
			include_once plugin_dir_path( __FILE__ ) . '--first-bogo-campaign.php';
		} else {
			include_once plugin_dir_path( __FILE__ ) . '--bogo-listing.php';
		}
		?>
	</div>
	<?php 

	if( 0 < $_all_bogo_coupon_count ) {
		echo '<div class="wbte_sc_bogo_premium_features">';
		$premium_url = esc_url( 'https://www.webtoffee.com/product/smart-coupons-for-woocommerce/?utm_source=free_plugin_bogo_sidebar&utm_medium=smart_coupons_basic&utm_campaign=smart_coupons&utm_content=' . WEBTOFFEE_SMARTCOUPON_VERSION );
		include_once WT_SMARTCOUPON_MAIN_PATH . 'admin/views/_premium_features_sidebar.php'; 
		echo '</div>';
	}

	echo $ds_obj->get_component(
		'help-widget',
		array(
			'values' => array(
				'items' => array(
				array( 'title' => __( 'Setup Guide', 'wt-smart-coupons-for-woocommerce' ), 'icon' => 'book', 'href' => esc_url( 'https://www.webtoffee.com/woocommerce-bogo-discounts/' ), 'target' => '_blank' ),
				array( 'title' => __( 'Contact support', 'wt-smart-coupons-for-woocommerce' ), 'icon' => 'headphone', 'target' => '_blank', 'href' => esc_url( 'https://www.webtoffee.com/support/' ) ),
				),
				'hover_text' => esc_html__( 'Help', 'wt-smart-coupons-for-woocommerce' ),
			),
			'class' => array( 'wbte_sc_admin_settings_help_widget' )
		)
	);
	?>
</div>




