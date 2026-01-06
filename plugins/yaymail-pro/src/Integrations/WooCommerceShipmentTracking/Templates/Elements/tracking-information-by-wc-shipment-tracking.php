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

$title_style           = TemplateHelpers::get_style(
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
        'border' => 'solid 1px ' . $data['border_color'],
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

$is_sample = isset( $args['render_data']['is_sample'] ) ? $args['render_data']['is_sample'] : false;
$order     = isset( $args['render_data']['order'] ) ? $args['render_data']['order'] : null;

if ( ! empty( $order ) ) {
    if ( class_exists( '\WC_Shipment_Tracking_Actions' ) ) {
        $st             = \WC_Shipment_Tracking_Actions::get_instance();
        $tracking_items = $st->get_tracking_items( $order->get_id(), true );
    }
}

ob_start();
?>
<?php if ( ! empty( $tracking_items ) || $is_sample ) : ?>
<h2 class="yaymail-woocommerce-shipment-tracking-title" style="<?php echo esc_attr( $title_style ); ?>" > <?php yaymail_kses_post_e( do_shortcode( $data['title'] ) ); ?> </h2>

<div class="yaymail-woocommerce-shipment-tracking-content" style="<?php echo esc_attr( $tracking_style ); ?>">
    <?php yaymail_kses_post_e( do_shortcode( isset( $data['rich_text'] ) ? $data['rich_text'] : '[yaymail_order_tracking_information_by_wc_shipment_tracking]' ) ); ?>
</div>
<?php endif; ?>
<?php
$element_content = ob_get_clean();
TemplateHelpers::wrap_element_content( $element_content, $element, $wrapper_style );
