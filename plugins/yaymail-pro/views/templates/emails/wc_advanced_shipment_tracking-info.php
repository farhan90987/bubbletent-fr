<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use YayMail\Page\Source\CustomPostType;

if ( $tracking_items ) :
	$ast_customizer = \Ast_Customizer::get_instance();

	$postID             = CustomPostType::postIDByTemplate( $this->template );
	$emailTextLinkColor = get_post_meta( $postID, '_yaymail_email_textLinkColor_settings', true ) ? get_post_meta( $postID, '_yaymail_email_textLinkColor_settings', true ) : '#7f54b3';
	$borderColor        = isset( $atts['bordercolor'] ) && $atts['bordercolor'] ? 'border-color:' . html_entity_decode( $atts['bordercolor'], ENT_QUOTES, 'UTF-8' ) : 'border-color:inherit';
	$textColor          = isset( $atts['textcolor'] ) && $atts['textcolor'] ? 'color:' . html_entity_decode( $atts['textcolor'], ENT_QUOTES, 'UTF-8' ) : 'color:inherit';
	$fontFamily         = isset( $atts['fontfamily'] ) && $atts['fontfamily'] ? 'font-family:' . html_entity_decode( $atts['fontfamily'], ENT_QUOTES, 'UTF-8' ) : 'font-family:inherit';

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

	$button_radius     = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_button_radius', $ast_customizer->defaults['fluid_button_radius'] );
	$button_expand     = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'fluid_button_expand', $ast_customizer->defaults['fluid_button_expand'] );
	$fluid_button_text = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_button_text', $ast_customizer->defaults['fluid_button_text'] );

	$fluid_button_size  = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'fluid_button_size', $ast_customizer->defaults['fluid_button_size'] );
	$fluid_tracker_type = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_tracker_type', $ast_customizer->defaults['fluid_tracker_type'] );
	$button_font_size   = ( 'large' == $fluid_button_size ) ? 16 : 14;
	$button_padding     = ( 'large' == $fluid_button_size ) ? '12px 25px' : '10px 15px';

	$order_details = wc_get_order( $order_id );

	$ast_preview = ( isset( $_REQUEST['action'] ) && 'email_preview' === $_REQUEST['action'] ) ? true : false;
	$text_align  = is_rtl() ? 'right' : 'left';

	$shipment_status = get_post_meta( $order_id, 'shipment_status', true );

	if ( ! empty( $order_details ) ) {
		$order_status = $order_details->get_status();
	} else {
		$order_status = 'completed';
	}

	?>

<div class="fluid_container">
	
	<?php
	foreach ( $tracking_items as $key => $tracking_item ) {

		if ( '' != $tracking_item['formatted_tracking_provider'] ) {
			$ast_provider_title = apply_filters( 'ast_provider_title', esc_html( $tracking_item['formatted_tracking_provider'] ) );
		} else {
			$ast_provider_title = apply_filters( 'ast_provider_title', esc_html( $tracking_item['tracking_provider'] ) );
		}
		?>
		<table class="fluid_table fluid_table_2cl" style="<?php echo esc_attr( $textColor ); ?>">
			<tbody class="fluid_tbody_2cl">
				<tr>
					<td>
						<div class="order_status <?php echo esc_attr( $order_status ); ?>">
							<h2 class="shipped_label"><?php esc_html_e( 'Shipped', 'woo-advanced-shipment-tracking' ); ?></h2>
							<?php
							if ( $ast_preview ) {
								$hide_shipping_date_class = ( $fluid_hide_shipping_date ) ? 'hide' : '';
								echo '<p style="margin: 0;"><span class="shipped_on ' . esc_html( $hide_shipping_date_class ) . '">';
								esc_html_e( 'Shipped on', 'woo-advanced-shipment-tracking' );
								echo ': <b>';
								echo esc_html( date_i18n( get_option( 'date_format' ), $tracking_item['date_shipped'] ) );
								echo '</b>';
								echo '</span></p>';
							} elseif ( ! $fluid_hide_shipping_date ) {
								echo '<p style="margin: 0;"><span class="shipped_on">';
								esc_html_e( 'Shipped on', 'woo-advanced-shipment-tracking' );
								echo ': <b>';
								echo esc_html( date_i18n( get_option( 'date_format' ), $tracking_item['date_shipped'] ) );
								echo '</b>';
								echo '</span></p>';
							}
							?>
						</div>		
					</td>						
				</tr>

				<?php
				if ( $ast_preview ) {
					$fluid_tracker_class = ( 'hide' == $fluid_tracker_type ) ? 'hide' : '';
					?>
					<tr class="tracker_tr <?php echo esc_attr( $fluid_tracker_class ); ?>">
						<td class="fluid_2cl_td_image" style="padding-top:5px !important;">
							<img class="tracker_image " style="width:100%;" src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>assets/images/<?php echo esc_attr( $fluid_tracker_type ); ?>.png"></img>
						</td>	
					</tr>	
					<?php
				} else {
					if ( 'hide' != $fluid_tracker_type ) {
						?>
						<tr class="tracker_tr">
							<td class="fluid_2cl_td_image <?php echo esc_attr( $fluid_tracker_type ); ?>" style="padding-top:5px !important;">
								<img class="tracker_image" style="width:100%;" src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>assets/images/<?php echo esc_attr( $fluid_tracker_type ); ?>.png"></img>
							</td>					
						</tr>						
						<?php
					}
				}

				?>
				<tr class="fluid_2cl_tr">
					<td class="fluid_2cl_td_image" style="vertical-align: middle;">
						<div class="fluid_provider">
							<?php
							if ( $ast_preview ) {
								$fluid_provider_img_class = ( $fluid_hide_provider_image ) ? 'hide' : '';
								?>
								<div class="fluid_provider_img <?php echo esc_attr( $fluid_provider_img_class ); ?>">
									<img src="<?php echo esc_url( $tracking_item['tracking_provider_image'] ); ?>" style="width:40px;height:40px"></img>
								</div>									
								<?php
							} elseif ( ! $fluid_hide_provider_image ) {
								?>
								<div class="fluid_provider_img">
									<img src="<?php echo esc_url( $tracking_item['tracking_provider_image'] ); ?>" style="width:40px;height:40px"></img>
								</div>
								<?php
							}
							?>
							<div class="provider_name">
								<div>
									<span class="tracking_provider"><?php echo esc_html( $ast_provider_title ); ?></span>
									<a class="tracking_number" href="<?php echo esc_url( $tracking_item['ast_tracking_link'] ); ?>"><?php echo esc_html( $tracking_item['tracking_number'] ); ?></a>
								</div>								
							</div>		

							<div class="track-button-div">
								<a href="<?php echo esc_url( $tracking_item['ast_tracking_link'] ); ?>" class="track-button"><?php echo esc_html_e( $fluid_button_text, 'woo-advanced-shipment-tracking' ); ?></a>
							</div>
							<div class="clearfix"></div>
							<?php do_action( 'ast_fluid_left_cl_end', $tracking_item, $order_id ); ?>
						</div>								
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}
	?>
		
</div>

<div class="clearfix"></div>

<style>
.clearfix{
	display: block;
	content: '';
	clear: both;
}
.fluid_container{
	width: 100%;
	display: block;
}
.fluid_table_2cl{
	width: 100%;	
	margin: 10px 0 !important;
	border: 1px solid <?php echo esc_html( $border_color ); ?> !important;
	border-radius: <?php echo esc_html( $border_radius ); ?>px !important;    
	background: <?php echo esc_html( $background_color ); ?> !important;	
	border-spacing: 0 !important;	
}
.tracker_tr td{	
	border-bottom: 1px solid <?php echo esc_html( $border_color ); ?>;
}
.fluid_table_2cl .fluid_2cl_tr td.fluid_2cl_td_action{	
	text-align: right;
	vertical-align: middle !important;
}

.fluid_table td{
	padding: <?php echo esc_html( $table_padding ); ?>px !important;
}

.fluid_provider_img {    
	display: inline-block;
	vertical-align: middle;
}
.fluid_provider_img img{
	max-width: 40px;
	border-radius: 5px;
	margin-right: 10px !important;
}
.provider_name{
	display: inline-block;
	vertical-align: middle;
}
.tracking_provider{
	word-break: break-word;
	margin-right: 5px;	
	font-size: 14px;
	display: block;
}
.tracking_number{
	color: #03a9f4;
	text-decoration: none;    
	font-size: 14px;
	line-height: 19px;
	display: block;
	margin-top: 4px;
}
.order_status{
	font-size: 12px;    
	margin: 0;	
}
.shipped_label{
	font-size: 24px !important;
	margin: 0 0 10px !important;	
	display: inline-block;
	color: #333;
	vertical-align: middle;
	font-weight:500;
	line-height: 100%;
}
span.shipped_on{
	margin-top: 5px;
	display: inline-block;
	font-size: 14px;
}
.order_status span{
	vertical-align: middle;
}
a.track-button {
	background: <?php echo esc_html( $button_background_color ); ?>;
	color: <?php echo esc_html( $button_font_color ); ?> !important;
	padding: <?php echo esc_html( $button_padding ); ?>;
	text-decoration: none;
	display: inline-block;
	border-radius: <?php echo esc_html( $button_radius ); ?>px;
	margin-top: 0;
	font-size: <?php echo esc_html( $button_font_size ); ?>px !important;
	text-align: center;
	min-height: 10px;
	white-space: nowrap;	
}
.track-button-div{
	float: right;
}

@media screen and (max-width: 460px) {
	.track-button-div{
		float: none !important;
		margin-top: 15px !important;
	}
}

</style>

	<?php
endif;
