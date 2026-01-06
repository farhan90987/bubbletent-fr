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

<?php if ( count( $keys ) > 0 ) : ?>

	<?php foreach ( $keys as $key ) : ?>

	<h3 style="<?php echo esc_attr( $textColor ); ?>"><?php echo esc_html( $key->software_product_id ); ?> <?php
	if ( $key->software_version ) {
		// translators: Version.
		printf( esc_html__( 'Version %s', 'woocommerce-software-add-on' ), esc_html( $key->software_version ) );}
	?>
	</h3>

	<ul style="<?php echo esc_attr( $textColor ); ?>;list-style: disc;">
		<li><?php esc_html_e( 'License Email:', 'woocommerce-software-add-on' ); ?> <strong><?php echo esc_html( $key->activation_email ); ?></strong></li>
		<li><?php esc_html_e( 'License Key:', 'woocommerce-software-add-on' ); ?> <strong><?php echo esc_html( $key->license_key ); ?></strong></li>
		<?php
		$remaining = $GLOBALS['wc_software']->activations_remaining( $key->key_id );
		if ( $remaining ) {
			// translators: activations remaining.
			echo '<li>' . sprintf( esc_html__( '%d activations remaining', 'woocommerce-software-add-on' ), esc_html( $remaining ) ) . '</li>';}
		?>
	</ul>

<?php endforeach; ?>

<?php endif; ?>
