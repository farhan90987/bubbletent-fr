<?php
use YayMail\Utils\TemplateHelpers;

$is_placeholder = isset( $args['is_placeholder'] ) ? $args['is_placeholder'] : false;
$element_data   = isset( $args['element']['data'] ) ? $args['element']['data'] : [];

$provider_title        = isset( $element_data['provider_title'] ) ? $element_data['provider_title'] : TemplateHelpers::get_content_as_placeholder( 'provider_title', esc_html__( 'Provider', 'woocommerce' ), $is_placeholder );
$tracking_number_title = isset( $element_data['tracking_number_title'] ) ? $element_data['tracking_number_title'] : TemplateHelpers::get_content_as_placeholder( 'tracking_number_title', esc_html__( 'Tracking Number', 'woocommerce' ), $is_placeholder );
$date_title            = isset( $element_data['date_title'] ) ? $element_data['date_title'] : TemplateHelpers::get_content_as_placeholder( 'date_title', esc_html__( 'Date', 'woocommerce' ), $is_placeholder );


$table_td_style = TemplateHelpers::get_style(
    [
        'font-size'    => '14px',
        'padding'      => '15px',
        'text-align'   => yaymail_get_text_align(),
        'font-family'  => TemplateHelpers::get_font_family_value( isset( $element_data['font_family'] ) ? $element_data['font_family'] : 'inherit' ),
        'color'        => isset( $element_data['text_color'] ) ? $element_data['text_color'] : 'inherit',
        'border-width' => '1px',
        'border-style' => 'solid',
        'border-color' => isset( $element_data['border_color'] ) ? $element_data['border_color'] : 'inherit',
    ]
);

$table_style = TemplateHelpers::get_style(
    [
        'border-color' => isset( $element_data['border_color'] ) ? $element_data['border_color'] : 'inherit',
        'width'        => '100%',
    ]
);

?>
<table class="td" cellspacing="0" cellpadding="6" style="<?php echo esc_attr( $table_style ); ?>">
    <thead>
        <tr>
            <th class="tracking-provider" scope="col" class="td" style="<?php echo esc_attr( $table_td_style ); ?>"><?php echo esc_html( $provider_title ); ?></th>
            <th class="tracking-number" scope="col" class="td" style="<?php echo esc_attr( $table_td_style ); ?>"><?php echo esc_html( $tracking_number_title ); ?></th>
            <th class="date-shipped" scope="col" class="td" style="<?php echo esc_attr( $table_td_style ); ?>"><?php echo esc_html( $date_title ); ?></th>
            <th class="order-actions" scope="col" class="td" style="<?php echo esc_attr( $table_td_style ); ?>">&nbsp;</th>
        </tr>
    </thead>

    <tbody>
        <tr class="tracking">
            <td class="tracking-provider" data-title="<?php esc_html_e( 'Provider', 'woocommerce-shipment-tracking' ); ?>" style="<?php echo esc_attr( $table_td_style ); ?>">
                <?php echo esc_html__( 'Provider', 'woocommerce-shipment-tracking' ); ?>
            </td>
            <td class="tracking-number" data-title="<?php esc_html_e( 'Tracking Number', 'woocommerce-shipment-tracking' ); ?>" style="<?php echo esc_attr( $table_td_style ); ?>">
                <?php echo esc_html( '123' ); ?>
            </td>
            <td class="date-shipped" data-title="<?php esc_html_e( 'Status', 'woocommerce-shipment-tracking' ); ?>" style="<?php echo esc_attr( $table_td_style ); ?>">
                <?php echo esc_html__( 'Status', 'woocommerce-shipment-tracking' ); ?>
            </td>
            <td class="order-actions" style="<?php echo esc_attr( $table_td_style ); ?>">
                <?php echo esc_html__( 'Track', 'woocommerce-shipment-tracking' ); ?>
            </td>
        </tr>
    </tbody>
</table>


