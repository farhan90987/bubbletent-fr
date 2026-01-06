<?php
/**
 * Template for WC Shipping & Tax Shipment Tracking Information shortcode.
 */

use YayMail\Utils\TemplateHelpers;

$data = isset( $args['element']['data'] ) ? $args['element']['data'] : [];

$provider_title        = isset( $data['provider_title'] ) ? $data['provider_title'] : '{{provider_title}}';
$tracking_number_title = isset( $data['tracking_number_title'] ) ? $data['tracking_number_title'] : '{{tracking_number_title}}';

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

<thead style="<?php echo esc_attr( $table_td_style ); ?>">
    <tr style="<?php echo esc_attr( $table_td_style ); ?>">
        <th class="td" colspan="1" scope="col" style="<?php echo esc_attr( $table_td_style ); ?>"><?php yaymail_kses_post_e( $provider_title ); ?></th>
        <th class="td" colspan="1" scope="col" style="<?php echo esc_attr( $table_td_style ); ?>"><?php yaymail_kses_post_e( $tracking_number_title ); ?></th>
    </tr>
</thead>
<tbody style="<?php echo esc_attr( $table_td_style ); ?>">
    <tr style="<?php echo esc_attr( $table_td_style ); ?>">
        <td class="td" colspan="1" scope="col" style="<?php echo esc_attr( $table_td_style ); ?>"><?php yaymail_kses_post_e( 'YayMail' ); ?></td>
        <td class="td" colspan="1" scope="col" style="<?php echo esc_attr( $table_td_style ); ?>"><?php yaymail_kses_post_e( '1111' ); ?></td>
    </tr>
</tbody>




