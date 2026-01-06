<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use YayMail\Page\Source\CustomPostType;
	$wcast_customizer_settings = \Ast_Customizer::get_instance();

	$postID             = CustomPostType::postIDByTemplate( $this->template );
	$emailTextLinkColor = get_post_meta( $postID, '_yaymail_email_textLinkColor_settings', true ) ? get_post_meta( $postID, '_yaymail_email_textLinkColor_settings', true ) : '#7f54b3';
	$borderColor        = isset( $atts['bordercolor'] ) && $atts['bordercolor'] ? 'border-color:' . html_entity_decode( $atts['bordercolor'], ENT_QUOTES, 'UTF-8' ) : 'border-color:inherit';
	$textColor          = isset( $atts['textcolor'] ) && $atts['textcolor'] ? 'color:' . html_entity_decode( $atts['textcolor'], ENT_QUOTES, 'UTF-8' ) : 'color:inherit';
	$fontFamily         = isset( $atts['fontfamily'] ) && $atts['fontfamily'] ? 'font-family:' . html_entity_decode( $atts['fontfamily'], ENT_QUOTES, 'UTF-8' ) : 'font-family:inherit';


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


	$ast_preview  = ( isset( $_REQUEST['wcast-tracking-preview'] ) && '1' === $_REQUEST['wcast-tracking-preview'] ) ? true : false;
	$text_align   = is_rtl() ? 'right' : 'left';
	$order_status = 'completed';

$borderColor = isset( $atts['bordercolor'] ) && $atts['bordercolor'] ? 'border-color:' . html_entity_decode( $atts['bordercolor'], ENT_QUOTES, 'UTF-8' ) : 'border-color:inherit';
?>	
	<?php if ( 2 == $fluid_table_layout ) { ?>
	<div class="fluid_container" style="border-color: inherit;">
		<table class="fluid_table fluid_table_2cl" style="border-collapse: separate;width: 100%;margin: 10px 0;border: 1px solid <?php echo esc_html( $border_color ); ?>;border-radius: <?php echo esc_html( $border_radius ); ?>px;<?php echo esc_attr( $borderColor ); ?>;">
		<?php



			$ast_provider_title = apply_filters( 'ast_provider_title', esc_html( 'tracking_provider' ) );



		?>
				<tr class="fluid_2cl_tr" style="background: <?php echo esc_html( $background_color ); ?>">
					<td class="fluid_2cl_td_image" style="padding:12px;">
						<div class="fluid_provider">
							<?php if ( ! $fluid_hide_provider_image ) { ?>
								<div class="fluid_provider_img" style="vertical-align: top;display: inline-block;margin-right: 5px;<?php 1 == $fluid_table_layout ? 'width: 17%;' : ''; ?>">
									<img style="width:100%;max-width: 40px;border-radius: 5px;width:40px;height:40px;" src="<?php echo esc_url( wp_kses_post( wc_placeholder_img_src() ) ); ?>"></img>
								</div>
							<?php } ?>
							<div class="provider_name" style="display: inline-block; vertical-align: top;<?php 1 == $fluid_table_layout ? 'width: 75%;' : ''; ?>">
								<div>
									<span class="tracking_provider" style="word-break: break-word;margin-right: 5px;font-size: 14px;display: block;<?php $fluid_hide_provider_image && 2 == $fluid_table_layout && ! $fluid_hide_shipping_date ? 'display: inline-block;' : ''; ?>"><?php echo esc_html( $ast_provider_title ); ?></span>
									<a class="tracking_number" style="text-decoration: none;font-size: 14px;line-height: 19px;display: block;margin-top: 4px;<?php 1 == $fluid_table_layout ? 'overflow: hidden;text-overflow: ellipsis;' : ''; ?><?php $fluid_hide_provider_image && 2 == $fluid_table_layout && ! $fluid_hide_shipping_date ? 'display: inline-block;' : ''; ?>" href=""><?php echo esc_html( '123' ); ?></a>
								</div>								
							</div>		
							<?php if ( ! $fluid_hide_shipping_date ) { ?>
								<div class="order_status <?php echo esc_html( $order_status ); ?>" style="font-size: 12px; margin: 8px 0 0;font-style: italic;">
									<?php
									esc_html_e( 'Shipped on:', 'ast-pro' );
									echo '<span> ' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( gmdate( 'm-d-y' ) ) ) ) . '</span>';
									?>
								</div>	
							<?php } ?>							
						
						</div>		
					</td>
					<td class="fluid_2cl_td_action" style="text-align: right;padding:12px">
						<a href="" class="button track-button" style="border-color: white;background: <?php echo esc_html( $button_background_color ); ?>;color: <?php echo esc_html( $button_font_color ); ?>;padding: <?php echo esc_html( $button_padding ); ?>;text-decoration: none;display: inline-block;border-radius: <?php echo esc_html( $button_radius ); ?>px;margin-top: 0;font-size: <?php echo esc_html( $button_font_size ); ?>px;text-align: center;min-height: 10px;white-space: nowrap;"><?php echo esc_html( $fluid_button_text ); ?></a>
					</td>
				</tr>							
					</table>
	</div>
	<?php } else { ?>
	<div class="fluid_container" style="margin-top:10px;">
	
		<?php

		$ast_provider_title = apply_filters( 'ast_provider_title', esc_html( 'tracking_provider' ) );

		?>
		<table class="fluid_table fluid_table_1cl" style="border-collapse: separate;border: 1px solid <?php echo esc_html( $border_color ); ?>;border-radius: <?php echo esc_html( $border_radius ); ?>px;background: <?php echo esc_html( $background_color ); ?>margin-bottom: 10px;width: 48%;margin: 0 10px 0 0;float: left;">	
			<tr>
				<td style="padding: <?php echo esc_html( $table_padding ); ?>px;">
					<div class="fluid_provider">
						<?php if ( ! $fluid_hide_provider_image ) { ?>
							<div class="fluid_provider_img" style="vertical-align: top;display: inline-block;margin-right: 5px;<?php 1 == $fluid_table_layout ? 'width: 17%;' : ''; ?>">
								<img src="<?php echo esc_html( wp_kses_post( wc_placeholder_img_src() ) ); ?>" style="width:40px;height:40px"></img>
							</div>
						<?php } ?>
						<div class="provider_name" style="display: inline-block; vertical-align: top;<?php 1 == $fluid_table_layout ? 'width: 75%;' : ''; ?>">
							<div>
								<span class="tracking_provider" style="word-break: break-word;margin-right: 5px;font-size: 14px;display: block;<?php $fluid_hide_provider_image && 2 == $fluid_table_layout && ! $fluid_hide_shipping_date ? 'display: inline-block;' : ''; ?>"><?php echo esc_html( $ast_provider_title ); ?></span>
								<a class="tracking_number" style="text-decoration: none;font-size: 14px;line-height: 19px;display: block;margin-top: 4px;<?php 1 == $fluid_table_layout ? 'overflow: hidden;text-overflow: ellipsis;' : ''; ?><?php $fluid_hide_provider_image && 2 == $fluid_table_layout && ! $fluid_hide_shipping_date ? 'display: inline-block;' : ''; ?>" href=""><?php echo esc_html( '123' ); ?></a>
							</div>							
						</div>
						<?php if ( ! $fluid_hide_shipping_date ) { ?>
							<div class="order_status <?php echo esc_html( $order_status ); ?>">
								<?php
								esc_html_e( 'Shipped on:', 'ast-pro' );
								echo '<span> ' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( gmdate( 'm-d-y' ) ) ) ) . '</span>';
								?>
							</div>	
						<?php } ?>
					
					</div>		
				</td>				
			</tr>
			<tr>
				<td class="fluid_td_1_cl_action" style="padding: <?php echo esc_html( $table_padding ); ?>px;padding-top: 0">
					<a href="" class="button track-button" style="<?php $button_expand && 1 == $fluid_table_layout ? 'display: block;' : ''; ?>"><?php echo esc_html( $fluid_button_text ); ?></a>
				</td>
			</tr>	
		</table>	
	
	</div>
	<?php } ?>

<div class="clearfix" style="display: block;content: '';clear: both;"></div>


	<?php

