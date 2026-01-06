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

?>
<table class="yaymail_builder_table_items_border yaymail_builder_table_tracking_item" cellspacing="0" cellpadding="6" border="1" style="width: 100% !important;<?php echo esc_attr( $borderColor ); ?>" width="100%">

	<thead>
		<tr style="<?php echo esc_attr( $textColor ); ?>">
			<th class="td" colspan="<?php echo esc_html( 'WC_Shipment_Tracking_Actions' == $setClassAvtive ? 1 : 2 ); ?>" scope="col" class="td" style="text-align: left;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>"><?php esc_html_e( 'Provider', 'yaymail' ); ?></th>
			<th class="td" scope="col" class="td" style="text-align: left;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>"><?php esc_html_e( 'Tracking Number', 'yaymail' ); ?></th>
			<th class="td" scope="col" class="td" style="text-align: left;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>"><?php esc_html_e( 'Date', 'yaymail' ); ?></th>
			<th class="td" scope="col" class="td" style="text-align: left;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>">&nbsp;</th>
		</tr>
	</thead>

	<tbody style="">
			<tr class="tracking" style="<?php echo esc_attr( $textColor ); ?>">
				
				<?php if ( 'WC_Shipment_Tracking_Actions' == $setClassAvtive ) { ?>
					<td class="td" data-title="<?php esc_html_e( 'Provider', 'yaymail' ); ?>" style="text-align: left; padding: 12px;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>">
						<?php esc_html_e( 'yaymail', 'yaymail' ); ?>
					</td>
					<?php
				} else {
					?>
					<td class="td" data-title="<?php esc_html_e( 'Provider', 'yaymail' ); ?>" style="text-align: left; padding: 12px;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>">
						<img style="width: 50px;vertical-align: middle;max-width:unset;margin:0;" src="<?php echo esc_url( YAYMAIL_PLUGIN_URL . 'assets/images/icon-default.png' ); ?>">
					</td>
					<td class="td" data-title="<?php esc_html_e( 'Provider', 'yaymail' ); ?>" style="text-align: left; padding: 12px;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>">
						<?php esc_html_e( 'yaymail', 'yaymail' ); ?>
					</td>
				<?php } ?> 

				<td class="td" data-title="<?php esc_html_e( 'Tracking Number', 'yaymail' ); ?>" style="text-align: left; padding: 12px;;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>">
					<?php esc_html_e( '123', 'yaymail' ); ?>
				</td>
				<td class="td" data-title="<?php esc_html_e( 'Status', 'yaymail' ); ?>" style="text-align: left; padding: 12px;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>">
					<?php echo wp_kses_post( sprintf( '<time datetime="%s">%s</time>', new WC_DateTime(), wc_format_datetime( new WC_DateTime() ) ) ); ?>
				</td>
				<td class="td" style="text-align: center; padding: 12px;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>">
					<a style="color: <?php echo esc_attr( $emailTextLinkColor ); ?>" href="#" target="_blank"><?php esc_html_e( 'Track', 'yaymail' ); ?></a>
				</td>
			</tr>
	</tbody>
</table>
