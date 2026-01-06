<?php
defined( 'ABSPATH' ) || exit;

use YayMail\Utils\TemplateHelpers;
use YayMail\Integrations\AdvancedShipmentTrackingByZorem\Utils\Helpers;

if ( empty( $args['element'] ) ) {
    return;
}

$element = $args['element'];
$data    = $element['data'];

$tracking_info_settings = Helpers::get_tracking_info_settings();
$header                 = array_merge( $tracking_info_settings['header'], isset( $data['header'] ) ? $data['header'] : [] );
$style                  = array_merge( $tracking_info_settings['style'], isset( $data['style'] ) ? $data['style'] : [] );

$border_color = $style['table_border_color'];

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
        'margin-bottom' => '10px',
        'font-size'     => '18px',
        'font-weight'   => 'bold',
        'font-family'   => TemplateHelpers::get_font_family_value( isset( $data['font_family'] ) ? $data['font_family'] : 'inherit' ),
    ]
);

$tracking_border_style = TemplateHelpers::get_style(
    [
        'border' => 'solid 1px ' . $border_color,
    ]
);

$tracking_style = TemplateHelpers::get_style(
    [
        'color'       => isset( $data['text_color'] ) ? $data['text_color'] : 'inherit',
        'text-align'  => yaymail_get_text_align(),
        'font-size'   => '14px',
        'font-family' => TemplateHelpers::get_font_family_value( isset( $data['font_family'] ) ? $data['font_family'] : 'inherit' ),
    ]
);

$hide_tracking_header   = $header['hidden'];
$tracking_header_text   = $header['text'];
$additional_header_text = $header['additional_text'];

ob_start();
?>

<?php if ( ! $hide_tracking_header ) : ?>
    <h2 class="yaymail-order-tracking-information-by-zorem-title" style="<?php echo esc_attr( $title_style ); ?>" > <?php echo wp_kses_post( do_shortcode( $tracking_header_text ) ); ?> </h2>
<?php endif; ?>

<?php if ( ! empty( $additional_header_text ) ) : ?>
    <p class="yaymail-order-tracking-information-by-zorem-additional-text" style="<?php echo esc_attr( $tracking_style ); ?>" > <?php echo wp_kses_post( do_shortcode( $additional_header_text ) ); ?> </p>
<?php endif; ?>

<div class="yaymail-order-tracking-information-by-zorem" style="<?php echo esc_attr( $tracking_style ); ?>">
    <?php yaymail_kses_post_e( do_shortcode( isset( $data['rich_text'] ) ? $data['rich_text'] : '[yaymail_order_tracking_information_by_zorem]' ) ); ?>
</div>

<?php
$element_content = ob_get_clean();
TemplateHelpers::wrap_element_content( $element_content, $element, $wrapper_style );
