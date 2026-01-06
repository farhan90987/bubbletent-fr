<?php

defined( 'ABSPATH' ) || exit;
$sent_to_admin       = ( isset( $sent_to_admin ) ? true : false );
$email               = ( isset( $email ) ? $email : '' );
$plain_text          = ( isset( $plain_text ) ? $plain_text : '' );
$woocontracts1a      = get_option( 'woocontracts_1a' );
$woocontracts2a      = get_option( 'woocontracts_2a' );
$woocontracts3a      = get_option( 'woocontracts_3a' );
$woocontractsbaslik  = ( ! empty( get_option( 'woocontracts_baslik' ) ) ? stripslashes( get_option( 'woocontracts_baslik' ) ) : 'Sözleşmeler' );
$woocontracts1baslik = ( ! empty( get_option( 'woocontracts_1_baslik' ) ) ? stripslashes( get_option( 'woocontracts_1_baslik' ) ) : 'Sözleşme 1' );
$woocontracts2baslik = ( ! empty( get_option( 'woocontracts_2_baslik' ) ) ? stripslashes( get_option( 'woocontracts_2_baslik' ) ) : 'Sözleşme 2' );
$woocontracts3baslik = ( ! empty( get_option( 'woocontracts_3_baslik' ) ) ? stripslashes( get_option( 'woocontracts_3_baslik' ) ) : 'Sözleşme 3' );
$woocontracts1       = ( ! empty( get_option( 'woocontracts_1_yaz' ) ) ? stripslashes( get_option( 'woocontracts_1_yaz' ) ) : 'Bu alanları Sözleşmeler kısmından düzenleyebilirsiniz.' );
$woocontracts2       = ( ! empty( get_option( 'woocontracts_2_yaz' ) ) ? stripslashes( get_option( 'woocontracts_2_yaz' ) ) : 'Bu alanları Sözleşmeler kısmından düzenleyebilirsiniz.' );
$woocontracts3       = ( ! empty( get_option( 'woocontracts_3_yaz' ) ) ? stripslashes( get_option( 'woocontracts_3_yaz' ) ) : 'Bu alanları Sözleşmeler kısmından düzenleyebilirsiniz.' );
if ( null !== $order ) {
	$searchSC      = array( '[fatura-isim]', '[fatura-firma]', '[fatura-adres]', '[tc-kimlik-no]', '[vergi-dairesi]', '[vergi-numarasi]', '[kargo-isim]', '[kargo-firma]', '[kargo-adres]', '[telefon]', '[eposta]', '[tarih]', '[urun-listesi]', '[toplam-tutar]', '[kargo-tutar]', '[vergi-tutar]', '[sepet-tutar]', '[odeme-yontemi]' );
	$fisim         = $order->get_formatted_billing_full_name();
	$kargisim      = $order->get_formatted_shipping_full_name();
	$tckimlikno    = get_post_meta( $order->get_id(), '_billing_tc', true );
	$vergidairesi  = get_post_meta( $order->get_id(), '_billing_vergi_dairesi', true );
	$verginumarasi = get_post_meta( $order->get_id(), '_billing_vergi_no', true );
	$tarih         = $order->get_date_created()->format( 'd-m-Y' );
	$fil           = WC()->countries->states[ $order->get_billing_country() ][ $order->get_billing_state() ];
	$fadres        = $order->get_billing_address_1() . ' ' . $order->get_billing_address_2() . ' ' . $order->get_billing_postcode() . ' ' . $order->get_billing_city() . ' ' . $fil;
	if ( ! empty( $order->get_shipping_country() ) ) {
		$kargil    = WC()->countries->states[ $order->get_shipping_country() ][ $order->get_shipping_state() ];
		$kargadres = $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2() . ' ' . $order->get_shipping_postcode() . ' ' . $order->get_shipping_city() . ' ' . $kargil;
	} else {
		$kargil    = $fil;
		$kargadres = $fadres;
	}
	$replace          = array( $fisim, $order->get_billing_company(), $fadres, $tckimlikno, $vergidairesi, $verginumarasi, $kargisim, $order->get_shipping_company(), $kargadres, $order->get_billing_phone(), $order->get_billing_email(), $tarih, $urunListeVar, $order->get_formatted_order_total(), $order->get_shipping_to_display(), $order->get_total_tax(), $order->get_subtotal_to_display(), $order->get_payment_method_title() );
	$woocontracts1yaz = nl2br( str_replace( $searchSC, $replace, $woocontracts1 ) );
	$woocontracts2yaz = nl2br( str_replace( $searchSC, $replace, $woocontracts2 ) );
	$woocontracts3yaz = nl2br( str_replace( $searchSC, $replace, $woocontracts3 ) );
} else {
	$woocontracts1yaz = nl2br( $woocontracts1 );
	$woocontracts2yaz = nl2br( $woocontracts2 );
	$woocontracts3yaz = nl2br( $woocontracts3 );
}
$textColor  = isset( $atts['textcolor'] ) && $atts['textcolor'] ? 'color:' . html_entity_decode( $atts['textcolor'], ENT_QUOTES, 'UTF-8' ) : 'color:inherit';
$fontFamily = isset( $atts['fontfamily'] ) && $atts['fontfamily'] ? 'font-family:' . html_entity_decode( $atts['fontfamily'], ENT_QUOTES, 'UTF-8' ) : 'font-family:inherit';
?>
<div id="sozlesmeler">
	<h2 style='color: #7f54b3;<?php echo esc_attr( $fontFamily ); ?>'><?php echo esc_html( $woocontractsbaslik ); ?></h2>
	<?php if ( get_option( 'woocontracts_3a' ) == 1 ) : ?>
	<div>
		<h4 style="margin: 10px 0;<?php echo esc_attr( $textColor ); ?>;"><?php echo esc_html( $woocontracts1baslik ); ?></h4>
		<div id="woocontracts1" style="<?php echo esc_attr( $textColor ); ?>;padding: 6px 40px 10px 8px;height: 110px;background-color:#F4F4F4;overflow:auto;font-size:small;"><?php echo wp_kses_post( $woocontracts1yaz ); ?></div>
	</div>
	<?php endif; ?>
	<?php if ( get_option( 'woocontracts_2a' ) == 1 ) : ?>
	<div>
		<h4 style="margin: 10px 0;<?php echo esc_attr( $textColor ); ?>;"><?php echo esc_html( $woocontracts2baslik ); ?></h4>
		<div id="woocontracts2" style="<?php echo esc_attr( $textColor ); ?>;padding: 6px 40px 10px 8px;height: 110px;background-color:#F4F4F4;overflow:auto;font-size:small;"><?php echo wp_kses_post( $woocontracts2yaz ); ?></div>
	</div>
	<?php endif; ?>
	<?php if ( get_option( 'woocontracts_3a' ) == 1 ) : ?>
	<div>
		<h4 style="margin: 10px 0;<?php echo esc_attr( $textColor ); ?>;"><?php echo esc_html( $woocontracts3baslik ); ?></h4>
		<div id="woocontracts3" style="<?php echo esc_attr( $textColor ); ?>;padding: 6px 40px 10px 8px;height: 110px;background-color:#F4F4F4;overflow:auto;font-size:small;"><?php echo wp_kses_post( $woocontracts3yaz ); ?></div>
	</div>
	<?php endif; ?>
</div>
