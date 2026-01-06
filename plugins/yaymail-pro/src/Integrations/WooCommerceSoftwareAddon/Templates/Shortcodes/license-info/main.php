<?php
/**
 * Template for WC Shipping & Tax Shipment Tracking Information shortcode.
 */

use YayMail\Utils\TemplateHelpers;
use YayMail\Utils\Helpers;

global $wpdb;

$data = isset( $args['element']['data'] ) ? $args['element']['data'] : [];

$render_data = isset( $args['render_data'] ) ? $args['render_data'] : [];

$order_data = Helpers::get_order_from_shortcode_data( $render_data );

if ( empty( $order_data ) ) {
    return yaymail_kses_post_e( __( 'No order found', 'yaymail' ) );
}

$order_id = version_compare( WC_VERSION, '3.0', '<' ) ? $order_data->id : $order_data->get_id();
$keys     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->wc_software_licenses} WHERE order_id = %s", $order_id ) );

if ( empty( $keys ) ) {
    return yaymail_kses_post_e( __( 'No license data', 'yaymail' ) );
}

$text_style = TemplateHelpers::get_style(
    [
        'font-size'   => '14px',
        'text-align'  => yaymail_get_text_align(),
        'font-family' => TemplateHelpers::get_font_family_value( isset( $data['font_family'] ) ? $data['font_family'] : 'inherit' ),
        'color'       => isset( $data['text_color'] ) ? $data['text_color'] : 'inherit',
    ]
);

$list_style = TemplateHelpers::get_style(
    [
        'font-size'   => '14px',
        'text-align'  => yaymail_get_text_align(),
        'font-family' => TemplateHelpers::get_font_family_value( isset( $data['font_family'] ) ? $data['font_family'] : 'inherit' ),
        'color'       => isset( $data['text_color'] ) ? $data['text_color'] : 'inherit',
        'list-style'  => 'disc',
    ]
);


foreach ( $keys as $key ) : ?>

    <h3 style="<?php echo esc_attr( $text_style ); ?>"><?php echo esc_html( $key->software_product_id ); ?> <?php
    if ( $key->software_version ) {
        // translators: Version.
        printf( esc_html__( 'Version %s', 'woocommerce-software-add-on' ), esc_html( $key->software_version ) );}
    ?>
    </h3>
    
    <ul style="<?php echo esc_attr( $list_style ); ?>">
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


<?php if ( count( $keys ) > 0 ) : ?>



<?php endif; ?>