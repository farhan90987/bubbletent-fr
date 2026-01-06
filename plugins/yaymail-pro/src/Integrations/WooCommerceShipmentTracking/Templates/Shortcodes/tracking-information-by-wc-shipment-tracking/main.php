<?php
/**
 * Tracking Widget template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/fluid-tracking-info.php.
 */
use YayMail\Utils\TemplateHelpers;
use YayMail\Utils\Helpers;

if ( ! isset( $args['order'] ) || ! ( Helpers::is_woocommerce_order( $args['order'] ) ) ) {
    return;
}

$tracking_items = $args['tracking_items'];

if ( empty( $tracking_items ) ) {
    return;
}

$text_link_color = isset( $args['text_link_color'] ) ? $args['text_link_color'] : YAYMAIL_COLOR_WC_DEFAULT;
$is_placeholder  = isset( $args['is_placeholder'] ) ? $args['is_placeholder'] : false;
$element_data    = isset( $args['element']['data'] ) ? $args['element']['data'] : [];

$provider_title        = isset( $element_data['provider_title'] ) ? $element_data['provider_title'] : TemplateHelpers::get_content_as_placeholder( 'provider_title', esc_html__( 'Provider', 'woocommerce' ), $is_placeholder );
$tracking_number_title = isset( $element_data['tracking_number_title'] ) ? $element_data['tracking_number_title'] : TemplateHelpers::get_content_as_placeholder( 'tracking_number_title', esc_html__( 'Tracking Number', 'woocommerce' ), $is_placeholder );
$date_title            = isset( $element_data['date_title'] ) ? $element_data['date_title'] : TemplateHelpers::get_content_as_placeholder( 'date_title', esc_html__( 'Date', 'woocommerce' ), $is_placeholder );


$table_style = TemplateHelpers::get_style(
    [
        'border-color' => isset( $element_data['border_color'] ) ? $element_data['border_color'] : 'inherit',
        'width'        => '100%',
    ]
);

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
    <?php
    foreach ( $tracking_items as $tracking_item ) {
        $date_shipped = [
            'datetime_attr' => $tracking_item['formatted_date_shipped_ymd'],
            'title_attr'    => $tracking_item['formatted_date_shipped_wc'],
            'text'          => $tracking_item['formatted_date_shipped_i18n'],
        ];
        ?>
            <tr class="tracking">
                <td class="tracking-provider" data-title="<?php esc_html_e( 'Provider', 'woocommerce-shipment-tracking' ); ?>" style="<?php echo esc_attr( $table_td_style ); ?>">
                    <?php echo esc_html( $tracking_item['formatted_tracking_provider'] ); ?>
                </td>
                <td class="tracking-number" data-title="<?php esc_html_e( 'Tracking Number', 'woocommerce-shipment-tracking' ); ?>" style="<?php echo esc_attr( $table_td_style ); ?>">
                    <?php echo esc_html( $tracking_item['tracking_number'] ); ?>
                </td>
                <td class="date-shipped" data-title="<?php esc_html_e( 'Status', 'woocommerce-shipment-tracking' ); ?>" style="<?php echo esc_attr( $table_td_style ); ?>">
                    <time datetime="<?php echo esc_attr( $date_shipped ['datetime_attr'] ); ?>" title="<?php echo esc_attr( $date_shipped ['title_attr'] ); ?>"><?php echo esc_html( $date_shipped['text'] ); ?></time>
                </td>
                <td class="order-actions" style="<?php echo esc_attr( $table_td_style ); ?>">
                        <a style="color: <?php echo esc_attr( $text_link_color ); ?>" href="<?php echo esc_url( $tracking_item['formatted_tracking_link'] ); ?>" target="_blank"><?php esc_html_e( 'Track', 'woocommerce-shipment-tracking' ); ?></a>
                </td>
            </tr>
            <?php
    }//end foreach
    ?>
    </tbody>
</table>


