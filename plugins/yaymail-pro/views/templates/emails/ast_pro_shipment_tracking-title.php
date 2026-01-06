<?php
	$wcast_customizer_settings     = \Ast_Customizer::get_instance();
	$titleColor                    = isset( $atts['titlecolor'] ) && $atts['titlecolor'] ? 'color:' . html_entity_decode( $atts['titlecolor'], ENT_QUOTES, 'UTF-8' ) : 'color:inherit';
	$hide_trackig_header           = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'hide_trackig_header', '' );
	$shipment_tracking_header      = $ast->get_option_value_from_array( 'tracking_info_settings', 'header_text_change', 'Tracking Information' );
	$shipment_tracking_header_text = $ast->get_option_value_from_array( 'tracking_info_settings', 'additional_header_text', '' );
	$fluid_table_layout            = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_table_layout', $wcast_customizer_settings->defaults['fluid_table_layout'] );
	$border_color                  = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_table_border_color', $wcast_customizer_settings->defaults['fluid_table_border_color'] );
	$border_radius                 = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_table_border_radius', $wcast_customizer_settings->defaults['fluid_table_border_radius'] );
	$background_color              = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_table_background_color', $wcast_customizer_settings->defaults['fluid_table_background_color'] );
	$table_padding                 = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_table_padding', $wcast_customizer_settings->defaults['fluid_table_padding'] );
	$fluid_hide_provider_image     = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'fluid_hide_provider_image', $wcast_customizer_settings->defaults['fluid_hide_provider_image'] );
	$fluid_hide_shipping_date      = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'fluid_hide_shipping_date', $wcast_customizer_settings->defaults['fluid_hide_shipping_date'] );
	$button_background_color       = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_button_background_color', $wcast_customizer_settings->defaults['fluid_button_background_color'] );
	$button_font_color             = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_button_font_color', $wcast_customizer_settings->defaults['fluid_button_font_color'] );

	$button_radius     = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_button_radius', $wcast_customizer_settings->defaults['fluid_button_radius'] );
	$button_expand     = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'fluid_button_expand', $wcast_customizer_settings->defaults['fluid_button_expand'] );
	$fluid_button_text = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_button_text', $wcast_customizer_settings->defaults['fluid_button_text'] );

	$fluid_button_size = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'fluid_button_size', $wcast_customizer_settings->defaults['fluid_button_size'] );
	$button_font_size  = ( 'large' == $fluid_button_size ) ? 16 : 14;
	$button_padding    = ( 'large' == $fluid_button_size ) ? '12px 25px' : '10px 15px';

	$ast_preview = ( isset( $_REQUEST['wcast-tracking-preview'] ) && '1' === $_REQUEST['wcast-tracking-preview'] ) ? true : false;
	$text_align  = is_rtl() ? 'right' : 'left';
	$hide_header = ( $hide_trackig_header ) ? 'display:none' : '';
?>
	<h2 class="header_text" style="margin: 0;text-align:<?php echo esc_html( $text_align ); ?>;<?php echo esc_html( $hide_header ); ?>;<?php echo esc_attr( $titleColor ); ?>">
		<?php echo esc_html( apply_filters( 'woocommerce_shipment_tracking_my_orders_title', esc_html_e( $shipment_tracking_header, 'ast-pro' ) ) ); ?>
	</h2>

	<p style="margin: 0;" class="addition_header"><?php echo wp_kses_post( $shipment_tracking_header_text ); ?></p>
