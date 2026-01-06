<?php
defined( 'ABSPATH' ) || exit;

use YayMail\Utils\TemplateHelpers;

if ( empty( $args['element'] ) ) {
    return;
}

$element = $args['element'];
$data    = $element['data'];

$shipping_address_html = wp_kses_post( do_shortcode( isset( $data['rich_text'] ) ? $data['rich_text'] : '[yaymail_shipping_address]' ) );

if ( empty( $shipping_address_html ) ) :
    return '';
endif;

$wrapper_style = TemplateHelpers::get_style(
    [
        'word-break'       => 'break-word',
        'background-color' => $data['background_color'],
        'padding'          => TemplateHelpers::get_spacing_value( isset( $data['padding'] ) ? $data['padding'] : [] ),
    ]
);

$shipping_border_style = TemplateHelpers::get_style(
    [
        'border' => 'solid 1px ' . $data['border_color'],
    ]
);

$shipping_wrapper_style = TemplateHelpers::get_style(
    [
        'color'       => isset( $data['text_color'] ) ? $data['text_color'] : 'inherit',
        'padding'     => '12px',
        'text-align'  => yaymail_get_text_align(),
        'font-size'   => '14px',
        'font-family' => TemplateHelpers::get_font_family_value( isset( $data['font_family'] ) ? $data['font_family'] : 'inherit' ),
        'border'      => 'solid 1px ' . $data['border_color'],
    ]
);

$title_style = TemplateHelpers::get_style(
    [
        'text-align'  => yaymail_get_text_align(),
        'color'       => isset( $data['title_color'] ) ? $data['title_color'] : 'inherit',
        'margin'      => '0 0 7px 0',
        'font-size'   => '20px',
        'font-weight' => '600',
        'font-family' => TemplateHelpers::get_font_family_value( isset( $data['font_family'] ) ? $data['font_family'] : 'inherit' ),
    ]
);

$is_layout_type_modern = isset( $data['layout_type'] ) && 'modern' === $data['layout_type'];

ob_start();
?>
<style>
    /* Modern layout */
    <?php if ( $is_layout_type_modern ) { ?>
    [data-yaymail-element-id="<?php echo esc_attr( $element['id'] ); ?>"] .yaymail-shipping-address-wrap {
        border: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
        <?php
    }//end if
    ?>
</style>
<div class="yaymail-shipping-title" style="<?php echo esc_attr( $title_style ); ?>" > <?php echo wp_kses_post( do_shortcode( $data['title'] ) ); ?> </div>
<div class="yaymail-shipping-address-wrap" style="<?php echo esc_attr( $shipping_wrapper_style ); ?>">
    <?php echo wp_kses_post( do_shortcode( isset( $data['rich_text'] ) ? $data['rich_text'] : '[yaymail_shipping_address]' ) ); ?>
</div>


           
<?php
$element_content = ob_get_clean();
TemplateHelpers::wrap_element_content( $element_content, $element, $wrapper_style );

