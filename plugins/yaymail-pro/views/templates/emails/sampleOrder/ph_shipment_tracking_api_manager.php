<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
	use YayMail\Page\Source\CustomPostType;
	$postID             = CustomPostType::postIDByTemplate( $this->template );
	$emailTextLinkColor = get_post_meta( $postID, '_yaymail_email_textLinkColor_settings', true ) ? get_post_meta( $postID, '_yaymail_email_textLinkColor_settings', true ) : '#7f54b3';
	$borderColor        = isset( $atts['bordercolor'] ) && $atts['bordercolor'] ? 'border-color:' . html_entity_decode( $atts['bordercolor'], ENT_QUOTES, 'UTF-8' ) : 'border-color:inherit';
	$textColor          = isset( $atts['textcolor'] ) && $atts['textcolor'] ? 'color:' . html_entity_decode( $atts['textcolor'], ENT_QUOTES, 'UTF-8' ) : 'color:inherit';
	$fontFamily         = isset( $atts['fontfamily'] ) && $atts['fontfamily'] ? 'font-family:' . html_entity_decode( $atts['fontfamily'], ENT_QUOTES, 'UTF-8' ) : 'font-family:inherit';
	$titleColor         = isset( $atts['titlecolor'] ) && $atts['titlecolor'] ? 'color:' . html_entity_decode( $atts['titlecolor'], ENT_QUOTES, 'UTF-8' ) : 'color:inherit';
	$text_align         = is_rtl() ? 'right' : 'left';
?>

<p style="margin: 0;margin-bottom: 10px;margin-top: 10px;" class="addition_header"><?php echo wp_kses_post( 'To track shipment, please follow the link of shipment ID(s)' ); ?></p>

<table class="yaymail_builder_table_items_border yaymail_builder_table_tracking_item" cellspacing="0" cellpadding="6" border="1" style="width: 100% !important;<?php echo esc_attr( $borderColor ); ?>" width="100%">

	<thead>
		<tr style="<?php echo esc_attr( $textColor ); ?>">
			<th class="td" scope="col" class="td" style="text-align: left;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>"><?php esc_html_e( 'Tracking No.', 'yaymail' ); ?></th>
			<th class="td" scope="col" class="td" style="text-align: left;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>"><?php esc_html_e( 'Description', 'yaymail' ); ?></th>
		</tr>
	</thead>

	<tbody style="">
			<tr class="tracking" style="<?php echo esc_attr( $textColor ); ?>">
				<td class="td" data-title="<?php esc_html_e( 'Tracking Number', 'yaymail' ); ?>" style="text-align: left; padding: 12px;;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>">
					<?php echo wp_kses_post( '1' ); ?>
				</td>
				<td class="td" data-title="<?php esc_html_e( 'Status', 'yaymail' ); ?>" style="text-align: left; padding: 12px;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>">
					<?php echo wp_kses_post( 'Tracking descriptions' ); ?> 
				</td>
			</tr>
	</tbody>
</table>

