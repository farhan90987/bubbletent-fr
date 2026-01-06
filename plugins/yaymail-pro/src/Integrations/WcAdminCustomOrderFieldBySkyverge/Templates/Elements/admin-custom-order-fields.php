<?php
defined( 'ABSPATH' ) || exit;

use YayMail\Utils\TemplateHelpers;

if ( empty( $args['element'] ) ) {
    return;
}

$element = $args['element'];
$data    = $element['data'];

$wrapper_style = TemplateHelpers::get_style(
    [
        'word-break'       => 'break-word',
        'background-color' => $data['background_color'],
        'padding'          => TemplateHelpers::get_spacing_value( isset( $data['padding'] ) ? $data['padding'] : [] ),
    ]
);

$title_style = TemplateHelpers::get_style(
    [
        'text-align'    => yaymail_get_text_align(),
        'color'         => isset( $data['title_color'] ) ? $data['title_color'] : 'inherit',
        'margin-top'    => '0',
        'margin-bottom' => '10px',
        'font-size'     => '18px',
        'font-weight'   => 'bold',
        'font-family'   => TemplateHelpers::get_font_family_value( isset( $data['font_family'] ) ? $data['font_family'] : 'inherit' ),
    ]
);

$table_style = TemplateHelpers::get_style(
    [
        'font-size'   => '14px',
        'text-align'  => yaymail_get_text_align(),
        'border'      => isset( $data['border_color'] ) ? '1px solid ' . $data['border_color'] : 'inherit',
        'font-family' => TemplateHelpers::get_font_family_value( isset( $data['font_family'] ) ? $data['font_family'] : 'inherit' ),
        'color'       => isset( $data['text_color'] ) ? $data['text_color'] : 'inherit',
    ]
);

$shortcoded_title   = isset( $data['title'] ) ? do_shortcode( $data['title'] ) : '';
$shortcoded_content = isset( $data['rich_text'] ) ? do_shortcode( $data['rich_text'] ) : '';
ob_start();
?>

<h2 class="yaymail-admin-custom-order-fields-by-skyverge-title" style="<?php echo esc_attr( $title_style ); ?>" > <?php yaymail_kses_post_e( $shortcoded_title ); ?></h2>

<table class="td yaymail-admin-custom-order-fields-by-skyverge-table" style="<?php echo esc_attr( $table_style ); ?>" border="0" cellpadding="6" cellspacing="0" width="100%">
    <?php yaymail_kses_post_e( $shortcoded_content ); ?>
</table>

<?php
$element_content = ob_get_clean();
TemplateHelpers::wrap_element_content( $element_content, $element, $wrapper_style );
