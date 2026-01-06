<?php
defined( 'ABSPATH' ) || exit;

use YayMail\Utils\TemplateHelpers;

if ( empty( $args['element'] ) ) {
    return;
}

$element = $args['element'];
$data    = $element['data'];

$text_align = yaymail_get_text_align();

$font_family   = TemplateHelpers::get_font_family_value( $data['font_family'] );
$wrapper_style = TemplateHelpers::get_style(
    [
        'word-break'       => 'break-word',
        'background-color' => $data['background_color'],
        'padding'          => TemplateHelpers::get_spacing_value( isset( $data['padding'] ) ? $data['padding'] : [] ),
        'font-family'      => $font_family,
        'text-align'       => $text_align,
    ]
);

$title_style = TemplateHelpers::get_style(
    [
        'color'         => isset( $data['title_color'] ) ? $data['title_color'] : '#333333',
        'margin-top'    => '0',
        'margin-bottom' => '10px',
        'font-size'     => '18px',
        'font-weight'   => 'bold',
        'font-family'   => $font_family,

    ]
);

$text_styles = TemplateHelpers::get_style(
    [
        'font-family' => $font_family,
        'color'       => isset( $data['text_color'] ) ? $data['text_color'] : '#333333',
    ]
);

$shortcoded_title   = isset( $data['title'] ) ? do_shortcode( $data['title'] ) : '';
$shortcoded_content = isset( $data['rich_text'] ) ? do_shortcode( $data['rich_text'] ) : '';

ob_start();
?>

<h2 class="yaymail-tracking-information-by-pluginhive-title" style="<?php echo esc_attr( $title_style ); ?>" > <?php yaymail_kses_post_e( $shortcoded_title ); ?> </h2>

<div class="yaymail-tracking-information-by-pluginhive-content" style="<?php echo esc_attr( $text_styles ); ?>">
    <?php yaymail_kses_post_e( $shortcoded_content ); ?>
</div>

<?php
$element_content = ob_get_clean();
TemplateHelpers::wrap_element_content( $element_content, $element, $wrapper_style );
