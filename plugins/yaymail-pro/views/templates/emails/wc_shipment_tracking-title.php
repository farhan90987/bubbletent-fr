<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use YayMail\Page\Source\CustomPostType;
$postID             = CustomPostType::postIDByTemplate( $this->template );
$emailTextLinkColor = get_post_meta( $postID, '_yaymail_email_textLinkColor_settings', true ) ? get_post_meta( $postID, '_yaymail_email_textLinkColor_settings', true ) : '#7f54b3';
$fontFamily         = isset( $atts['fontfamily'] ) && $atts['fontfamily'] ? 'font-family:' . html_entity_decode( $atts['fontfamily'], ENT_QUOTES, 'UTF-8' ) : 'font-family:inherit';
$titleColor         = isset( $atts['titlecolor'] ) && $atts['titlecolor'] ? 'color:' . html_entity_decode( $atts['titlecolor'], ENT_QUOTES, 'UTF-8' ) : 'color:inherit';
?>
<h2 class="shipment_tracking_header_text" style="margin:13px 0;text-align:left;<?php echo esc_attr( $fontFamily ); ?>;<?php echo esc_attr( $titleColor ); ?>;">
<?php esc_html_e( 'Tracking Information', 'yaymail' ); ?>
</h2>

