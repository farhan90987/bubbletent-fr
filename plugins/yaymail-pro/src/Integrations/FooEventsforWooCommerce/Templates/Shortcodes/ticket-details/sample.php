<?php
use YayMail\Utils\TemplateHelpers;

$data            = isset( $args['element']['data'] ) ? $args['element']['data'] : [];
$template        = ! empty( $args['template'] ) ? $args['template'] : null;
$text_link_color = ! empty( $template ) ? $template->get_text_link_color() : YAYMAIL_COLOR_WC_DEFAULT;

$text_style = TemplateHelpers::get_style(
    [
        'font-size'   => '14px',
        'text-align'  => yaymail_get_text_align(),
        'font-family' => TemplateHelpers::get_font_family_value( isset( $data['font_family'] ) ? $data['font_family'] : 'inherit' ),
        'color'       => isset( $data['text_color'] ) ? $data['text_color'] : 'inherit',
        'line-height' => '150%',
    ]
);

$text_style_h3 = TemplateHelpers::get_style(
    [
        'text-align'  => yaymail_get_text_align(),
        'font-family' => TemplateHelpers::get_font_family_value( isset( $data['font_family'] ) ? $data['font_family'] : 'inherit' ),
        'color'       => $text_link_color,
        'font-size'   => '16px',
    ]
);

$text_style_a = TemplateHelpers::get_style(
    [
        'color'           => $text_link_color,
        'font-weight'     => 'normal',
        'text-decoration' => 'underline',
    ]
);
?>
<div class="yaymail-fooevents-order-tickets" style="<?php echo esc_attr( $text_style ); ?>">
    <h3 style="<?php echo esc_attr( $text_style_h3 ); ?>"><?php esc_html_e( 'Ticket Details', 'woocommerce-events' ); ?></h3>
    <strong><a style="<?php echo esc_attr( $text_style_a ); ?>" href="#"><?php echo esc_html__( 'Sample Event', 'yaymail' ); ?></a></strong><br />
    <strong><?php esc_html_e( 'Date', 'yaymail' ); ?></strong>: 2024-06-15<br />
    <strong><?php esc_html_e( 'Start time', 'yaymail' ); ?></strong>: 09:00 AM<br />
    <strong><?php esc_html_e( 'End time', 'yaymail' ); ?></strong>: 05:00 PM<br />

    <strong><?php esc_html_e( 'Name', 'yaymail' ); ?></strong>: John Doe Smith<br />
    <strong><?php esc_html_e( 'Email', 'yaymail' ); ?></strong>: john.doe@example.com<br />
    <strong><?php esc_html_e( 'Telephone', 'yaymail' ); ?></strong>: +1234567890<br />
    <strong><?php esc_html_e( 'Company', 'yaymail' ); ?></strong>: <?php esc_html_e( 'Sample Company', 'yaymail' ); ?><br />
    <strong><?php esc_html_e( 'Designation', 'yaymail' ); ?></strong>: <?php esc_html_e( 'Manager', 'yaymail' ); ?><br />
</div>