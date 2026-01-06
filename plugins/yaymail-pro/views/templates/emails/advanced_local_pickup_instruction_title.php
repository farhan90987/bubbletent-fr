<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use YayMail\Page\Source\CustomPostType;

	$postID     = CustomPostType::postIDByTemplate( $this->template );
	$textColor  = isset( $atts['textcolor'] ) && $atts['textcolor'] ? 'color:' . html_entity_decode( $atts['textcolor'], ENT_QUOTES, 'UTF-8' ) : 'color:inherit';
	$fontFamily = isset( $atts['fontfamily'] ) && $atts['fontfamily'] ? 'font-family:' . html_entity_decode( $atts['fontfamily'], ENT_QUOTES, 'UTF-8' ) : 'font-family:inherit';
	$titleColor = isset( $atts['titlecolor'] ) && $atts['titlecolor'] ? 'color:' . html_entity_decode( $atts['titlecolor'], ENT_QUOTES, 'UTF-8' ) : 'color:inherit';

	$alp      = wc_local_pickup()->admin;
	$settings = wc_local_pickup()->customizer->customize_setting_options_func( 'ready_pickup' );

	$hide_widget_header = $alp->get_option_value_from_array( 'pickup_instruction_customize_settings', 'hide_widget_header', $settings['hide_widget_header']['default'] );

	$widget_header_text = $alp->get_option_value_from_array( 'pickup_instruction_customize_settings', 'widget_header_text', $settings['widget_header_text']['default'] );

?>	
	<?php if ( '1' != $hide_widget_header ) { ?>
		<h2 class="local_pickup_email_title" style="<?php echo esc_attr( $titleColor ); ?>;"><?php esc_html_e( $widget_header_text, 'advanced-local-pickup-for-woocommerce' ); ?></h2>
	<?php } ?>
