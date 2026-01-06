<?php
/**
 * Template for WC Shipping & Tax Shipment Tracking Information shortcode.
 */

use YayMail\Utils\TemplateHelpers;
use YayMail\Utils\Helpers;

$data = isset( $args['element']['data'] ) ? $args['element']['data'] : [];

$render_data = isset( $args['render_data'] ) ? $args['render_data'] : [];

$order_data = Helpers::get_order_from_shortcode_data( $render_data );

if ( empty( $order_data ) ) {
    return yaymail_kses_post_e( __( 'No order found', 'yaymail' ) );
}

$labels = $order_data->get_meta( 'wc_connect_labels', true );

if ( empty( $labels ) ) {
    return yaymail_kses_post_e( __( 'No tracking data', 'yaymail' ) );
}

$markup = '';

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

foreach ( $labels as $label ) {
    $carrier         = $label['carrier_id'];
    $carrier_service = YayMail\Integrations\WooCommerceShippingTax\Helpers\Helpers::get_service_schemas( $carrier );
    $carrier_label   = ( ! $carrier_service || empty( $carrier_service->carrier_name ) ) ? strtoupper( $carrier ) : $carrier_service->carrier_name;
    $tracking        = $label['tracking'];
    $error           = array_key_exists( 'error', $label );
    $refunded        = array_key_exists( 'refund', $label );

    // If the label has an error or is refunded, move to the next label.
    if ( $error || $refunded ) {
        continue;
    }
    $markup .= '<tr style="' . esc_attr( $table_td_style ) . '">';
    $markup .= '<td class="td" scope="col" style="' . esc_attr( $table_td_style ) . '">' . esc_html( $carrier_label ) . '</td>';

    switch ( $carrier ) {
        case 'fedex':
            $tracking_url = 'https://www.fedex.com/apps/fedextrack/?action=track&tracknumbers=' . $tracking;
            break;
        case 'usps':
            $tracking_url = 'https://tools.usps.com/go/TrackConfirmAction.action?tLabels=' . $tracking;
            break;
        case 'ups':
            $tracking_url = 'https://www.ups.com/track?tracknum=' . $tracking;
            break;
        case 'dhlexpress':
            $tracking_url = 'https://www.dhl.com/en/express/tracking.html?AWB=' . $tracking . '&brand=DHL';
            break;
    }

    $markup .= '<td class="td" scope="col" style="' . esc_attr( $table_td_style ) . '">';
    $markup .= '<a href="' . esc_url( $tracking_url ) . '" style="color: ' . esc_attr( $args['text_link_color'] ) . '">' . esc_html( $tracking ) . '</a>';
    $markup .= '</td>';
    $markup .= '</tr>';
}//end foreach

if ( empty( $markup ) ) {
    return yaymail_kses_post_e( __( 'No tracking data', 'yaymail' ) );
}

$provider_title        = isset( $data['provider_title'] ) ? $data['provider_title'] : '{{provider_title}}';
$tracking_number_title = isset( $data['tracking_number_title'] ) ? $data['tracking_number_title'] : '{{tracking_number_title}}';

?>

<thead style="<?php echo esc_attr( $table_td_style ); ?>">
    <tr style="<?php echo esc_attr( $table_td_style ); ?>">
        <th class="td" colspan="1" scope="col" style="<?php echo esc_attr( $table_td_style ); ?>"><?php yaymail_kses_post_e( $provider_title ); ?></th>
        <th class="td" colspan="1" scope="col" style="<?php echo esc_attr( $table_td_style ); ?>"><?php yaymail_kses_post_e( $tracking_number_title ); ?></th>
    </tr>
</thead>
<tbody style="<?php echo esc_attr( $table_td_style ); ?>">
    <?php yaymail_kses_post_e( $markup ); ?>
</tbody>
