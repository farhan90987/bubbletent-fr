<?php
/**
 * Information page while switching from Old BOGO to new
 *
 * @package    Wt_Smart_Coupon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $wpdb;

$lookup_table = Wt_Smart_Coupon::get_lookup_table_name();

$old_bogo_count_sql = "SELECT COUNT(*) FROM $lookup_table WHERE discount_type = '%s' AND post_status = '%s'";

$old_bogo_coupon_count = $wpdb->get_var(
	$wpdb->prepare( $old_bogo_count_sql, 'wt_sc_bogo', 'publish' )
);
?>

<div class="wbte_sc_bogo_switching">
	<div class="wbte_sc_bogo_switching_content">
		<h3><?php esc_html_e( 'Discover our enhanced BOGO module', 'wt-smart-coupons-for-woocommerce' ); ?></h3>
		<p><?php esc_html_e( "We've separated BOGO from other coupon types to unlock endless possibilities  based on your feedback and market trends.", 'wt-smart-coupons-for-woocommerce' ); ?></p>
		<span>
			<p><?php esc_html_e( 'Why Switch', 'wt-smart-coupons-for-woocommerce' ); ?></p>
			<img src="<?php echo esc_url( $admin_img_path ); ?>bogo_switch_help.svg" alt="<?php esc_html_e( 'Why switch to new bogo', 'wt-smart-coupons-for-woocommerce' ); ?>">
		</span>
		<ul>
			<li><p><?php esc_html_e( 'Expanded Features: Unlock new possibilities with our focused BOGO module.', 'wt-smart-coupons-for-woocommerce' ); ?></p></li>
			<li><p><?php esc_html_e( 'Improved Experience: Simplified and more efficient coupon management.', 'wt-smart-coupons-for-woocommerce' ); ?></p></li>
		</ul>
	</div>
	<?php if ( 0 < $old_bogo_coupon_count ) { ?>
	<div class="wbte_sc_bogo_switching_warning">
		<span style="height: 24px;"><?php echo wp_kses_post( $ds_obj->render_html( array( 'html' => '{{wbte-ds-icon-exclamation-mark-1}}' ) ) ); ?></span>
		<p>
			<?php
			echo wp_kses_post( sprintf( __( 'Switching to the new BOGO module will disable %d BOGO coupons made using the old version.', 'wt-smart-coupons-for-woocommerce' ), $old_bogo_coupon_count ) );
			?>
		</p>
	</div>
	<?php } ?>
	<div class="wbte_sc_bogo_switching_btn_div">
		<?php
		echo $ds_obj->get_component(
			'button filled medium',
			array(
				'values' => array(
					'button_title' => esc_html__( 'Update now', 'wt-smart-coupons-for-woocommerce' ),
				),
				'class'  => array( 'wbte_sc_bogo_switching_btn' ),
				'attr'   => array(
					'data-old-bogo-count' => $old_bogo_coupon_count,
				),
			)
		);
		?>
	</div>
</div>