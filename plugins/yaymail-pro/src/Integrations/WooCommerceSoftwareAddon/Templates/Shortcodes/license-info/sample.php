<?php
use YayMail\Utils\TemplateHelpers;

$data = isset( $args['element']['data'] ) ? $args['element']['data'] : [];

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

?>

<h3 style="<?php echo esc_attr( $text_style ); ?>"><?php echo esc_html( 'Sample product' ); ?> <?php
    // translators: Version.
    printf( esc_html__( 'Version %s', 'woocommerce-software-add-on' ), '1.0' );
?>
</h3>

<ul style="<?php echo esc_attr( $list_style ); ?>">
    <li><?php esc_html_e( 'License Email:', 'woocommerce-software-add-on' ); ?> <strong><?php echo esc_html( 'yaycommerce@sample.com' ); ?></strong></li>
    <li><?php esc_html_e( 'License Key:', 'woocommerce-software-add-on' ); ?> <strong><?php echo esc_html( '123456' ); ?></strong></li>
</ul>