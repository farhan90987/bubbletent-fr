<?php
/**
 * Template for TrackingMore Tracking Information shortcode.
 */

use YayMail\Utils\TemplateHelpers;

$custom_order_fields = $args['custom_order_fields'];

if ( empty( $custom_order_fields ) ) {
    return;
}

$data = isset( $args['element']['data'] ) ? $args['element']['data'] : [];

$table_td_style = TemplateHelpers::get_style(
    [
        'font-size'   => '14px',
        'padding'     => '12px',
        'text-align'  => yaymail_get_text_align(),
        'font-family' => TemplateHelpers::get_font_family_value( isset( $data['font_family'] ) ? $data['font_family'] : 'inherit' ),
        'color'       => isset( $data['text_color'] ) ? $data['text_color'] : 'inherit',
        'border'      => isset( $data['border_color'] ) ? '1px solid ' . $data['border_color'] : 'inherit',
    ]
);
?>

<tbody style="<?php echo esc_attr( $table_td_style ); ?>">

    <?php
    foreach ( $custom_order_fields as $label => $value ) :
        ?>
        <?php if ( ! empty( $label ) && ! empty( $value ) ) : ?>
            <tr style="<?php echo esc_attr( $table_td_style ); ?>">
                <th class="td" colspan="1" scope="col" style="<?php echo esc_attr( $table_td_style ); ?>"><?php yaymail_kses_post_e( $label ); ?></th>
                <td class="td" colspan="1" scope="col" style="<?php echo esc_attr( $table_td_style ); ?>"><?php yaymail_kses_post_e( $value ); ?></td>
            </tr>
        <?php endif; ?>
    <?php endforeach; ?>
</tbody>




