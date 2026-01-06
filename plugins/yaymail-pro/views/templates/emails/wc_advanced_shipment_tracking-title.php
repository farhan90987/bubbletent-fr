<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use YayMail\Page\Source\CustomPostType;

$postID             = CustomPostType::postIDByTemplate( $this->template );
$emailTextLinkColor = get_post_meta( $postID, '_yaymail_email_textLinkColor_settings', true ) ? get_post_meta( $postID, '_yaymail_email_textLinkColor_settings', true ) : '#7f54b3';
$titleColor         = isset( $atts['titlecolor'] ) && $atts['titlecolor'] ? 'color:' . html_entity_decode( $atts['titlecolor'], ENT_QUOTES, 'UTF-8' ) : 'color:inherit';
$fontFamily         = isset( $atts['fontfamily'] ) && $atts['fontfamily'] ? 'font-family:' . html_entity_decode( $atts['fontfamily'], ENT_QUOTES, 'UTF-8' ) : 'font-family:inherit';
$ast                = new \WC_Advanced_Shipment_Tracking_Actions();
$ast_customizer     = \Ast_Customizer::get_instance();

$hide_trackig_header           = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'hide_trackig_header', '' );
$shipment_tracking_header      = $ast->get_option_value_from_array( 'tracking_info_settings', 'header_text_change', 'Tracking Information' );
$shipment_tracking_header_text = $ast->get_option_value_from_array( 'tracking_info_settings', 'additional_header_text', '' );
$fluid_table_layout            = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_table_layout', $ast_customizer->defaults['fluid_table_layout'] );
$border_color                  = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_table_border_color', $ast_customizer->defaults['fluid_table_border_color'] );
$border_radius                 = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_table_border_radius', $ast_customizer->defaults['fluid_table_border_radius'] );
$background_color              = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_table_background_color', $ast_customizer->defaults['fluid_table_background_color'] );
$table_padding                 = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_table_padding', $ast_customizer->defaults['fluid_table_padding'] );
$fluid_hide_provider_image     = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'fluid_hide_provider_image', $ast_customizer->defaults['fluid_hide_provider_image'] );
$fluid_hide_shipping_date      = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'fluid_hide_shipping_date', $ast_customizer->defaults['fluid_hide_shipping_date'] );
$button_background_color       = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_button_background_color', $ast_customizer->defaults['fluid_button_background_color'] );
$button_font_color             = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_button_font_color', $ast_customizer->defaults['fluid_button_font_color'] );

?>
<h2 class="shipment_tracking_header_text" style="margin:13px 0;text-align:left;<?php echo esc_attr( $fontFamily ); ?>;<?php echo esc_attr( $titleColor ); ?>;
<?php
if ( $hide_trackig_header ) {
	echo 'display:none;'; }
?>
">
		<?php echo esc_html( apply_filters( 'woocommerce_shipment_tracking_my_orders_title', __( $shipment_tracking_header, 'woo-advanced-shipment-tracking' ) ) ); ?>
</h2>
<?php if ( '' != $shipment_tracking_header_text ) { ?>
	<p class="addition_header"><?php echo esc_html( $shipment_tracking_header_text ); ?></p>
<?php } ?>
