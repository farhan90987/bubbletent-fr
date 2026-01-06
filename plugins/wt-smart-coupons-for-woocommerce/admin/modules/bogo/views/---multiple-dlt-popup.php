<?php
/**
 * Selected BOGO delete popup
 *
 * @since 2.0.0
 * @package    Wt_Smart_Coupon
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}
$ds_obj = Wbte\Sc\Ds\Wbte_Ds::get_instance( WEBTOFFEE_SMARTCOUPON_VERSION );

$values = isset( $this->variables ) && is_array( $this->variables ) ? $this->variables : array();
?>
<p><?php echo esc_html( $values['popup_content'] ); ?></p>
<div data-class="popup-footer" style="text-align: right;">
	<?php
	echo $ds_obj->get_component(
		'button text medium',
		array(
			'values' => array(
				'button_title' => esc_html__( 'Cancel', 'wt-smart-coupons-for-woocommerce' ),
			),
			'class'  => array( 'wbte_sc_delete_bogo_cancel' ),
		)
	);
	echo '&ensp;';
	echo $ds_obj->get_component(
		'button danger medium',
		array(
			'values' => array(
				'button_title' => esc_html__( 'Delete permanently', 'wt-smart-coupons-for-woocommerce' ),
			),
			'class'  => array( 'wbte_sc_delete_perm_bogo_multiple' ),
		)
	);
	?>
</div>
<br />