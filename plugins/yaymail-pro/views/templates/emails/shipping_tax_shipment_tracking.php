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
if ( $markup ) :
	?>
	<table class="yaymail_builder_table_items_border yaymail_builder_table_tracking_item" cellspacing="0" cellpadding="6" border="1" style="width: 100% !important;<?php echo esc_attr( $borderColor ); ?>" width="100%">
		<thead>
			<tr style="<?php echo esc_attr( $textColor ); ?>">
				<th class="td" scope="col" style="text-align: <?php echo esc_html( $text_align ); ?>;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>"><?php echo esc_html__( 'Provider', 'woocommerce-services' ); ?></th>
				<th class="td" scope="col" style="text-align: <?php echo esc_html( $text_align ); ?>;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>"><?php echo esc_html__( 'Tracking number', 'woocommerce-services' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php echo wp_kses_post( $markup ); ?>
		</tbody>
	</table>
<br/>
	<?php
endif;



