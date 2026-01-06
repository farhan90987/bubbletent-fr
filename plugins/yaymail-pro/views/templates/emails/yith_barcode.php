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
<div id="ywbc_barcode_value">
	<?php
	if ( $barcode->exists() ) {
		$show_value = apply_filters( 'yith_ywbc_show_value_on_barcode', true, $obj );
		echo wp_kses_post( $this->render_barcode( $barcode, $show_value, '' ) );
	} elseif ( ! $hide_if_missing ) {
		?>
		<span class="ywbc-no-barcode-value"><?php esc_html_e( 'No barcode is available for this element.', 'yith-woocommerce-barcodes' ); ?></span>
		<?php
	}
	?>
</div>

