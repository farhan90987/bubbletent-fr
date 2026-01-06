<?php
use YayMail\Utils\TemplateHelpers;

defined( 'ABSPATH' ) || exit;

$render_data    = isset( $args['render_data'] ) ? $args['render_data'] : [];
$element_data   = isset( $args['element']['data'] ) ? $args['element']['data'] : [];
$is_placeholder = isset( $args['is_placeholder'] ) ? $args['is_placeholder'] : false;

$style = TemplateHelpers::get_style(
    [
        'font-size'   => '14px',
        'text-align'  => yaymail_get_text_align(),
        'font-family' => TemplateHelpers::get_font_family_value( isset( $element_data['font_family'] ) ? $element_data['font_family'] : 'inherit' ),
        'color'       => isset( $element_data['text_color'] ) ? $element_data['text_color'] : 'inherit',
    ]
);

$image        = '<img width="70px" src="' . wc_placeholder_img_src() . '" alt="product image"/>';
$border_color = $is_placeholder ? TemplateHelpers::get_content_as_placeholder( 'border_color', '', $is_placeholder ) : ( $element_data['border_color'] ?? ' #e0e0e0' );

?>

<table class="yaymail-ast-order-details-table" cellspacing="0" cellpadding="0" width="100%" >
    <tbody>
        <tr>
            <td class="td image_id" style="<?php echo esc_attr( $style ); ?> ;width: 70px; border: 0 ;border-bottom:1px solid <?php echo esc_attr( $border_color ); ?>; border-top:1px solid <?php echo esc_attr( $border_color ); ?>; vertical-align: middle; padding: 12px 5px;">
                <?php echo wp_kses_post( $image ); ?>
            </td>
         
            <td class="td image_id" style="<?php echo esc_attr( $style ); ?> ;text-align: left; border: 0 ;border-bottom:1px solid <?php echo esc_attr( $border_color ); ?>; border-top:1px solid <?php echo esc_attr( $border_color ); ?>">
                <?php echo esc_html__( 'Happy YayCommerce', 'yaymail' ); ?>
            </td>   
        </tr>
    </tbody>            
</table>
