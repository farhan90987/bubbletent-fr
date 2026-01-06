<?php
use YayMail\Utils\TemplateHelpers;
use YayMail\Utils\Helpers;

if ( ! isset( $args['order'] )
    || ! ( Helpers::is_woocommerce_order( $args['order'] ) )
    || ( ! isset( $args['ast'] ) && empty( $args['ast'] ) )
    || ( ! isset( $args['email_id'] ) && empty( $args['email_id'] ) )
) {
    return;
}

$text_align  = yaymail_get_text_align();
$margin_side = is_rtl() ? 'left' : 'right';

$ast            = $args['ast'];
$order_instance = $args['order'];
$email_id       = $args['email_id'];

$order_id       = $ast->get_formated_order_id( $order_instance->get_id() );
$tracking_items = $ast->get_tracking_items( $order_id, true );
$ast_customizer = Ast_Customizer::get_instance();

$display_shippment_item_price = $ast->get_checkbox_option_value_from_array( 'woocommerce_customer_completed_order_settings', 'display_shippment_item_price', $ast_customizer->defaults['display_shippment_item_price'] );

$arr_email_id_display_item_shipment = [
    'customer_partial_shipped_order',
    'customer_completed_order',
];

$data = isset( $args['element']['data'] ) ? $args['element']['data'] : [];

$tracking_style = TemplateHelpers::get_style(
    [
        'color'       => isset( $data['text_color'] ) ? $data['text_color'] : 'inherit',
        'text-align'  => $text_align,
        'font-size'   => '14px',
        'font-family' => TemplateHelpers::get_font_family_value( isset( $data['font_family'] ) ? $data['font_family'] : 'inherit' ),
        'border'      => 'none',
    ]
);
?>
    <?php
        yaymail_kses_post_e( yaymail_get_content( 'src/Integrations/AdvancedShipmentTrackingByZorem/Templates/Shortcodes/tracking-information/main.php', $args ) );
    ?>
    <?php if ( in_array( $email_id, $arr_email_id_display_item_shipment, true ) ) : ?>
    <div style="margin-top: 10px;">
        <table class="td yaymail-tracking-items-shipment" cellspacing="0" cellpadding="6" border="0" style="<?php echo esc_attr( $tracking_style ); ?>">
            <tbody style="<?php echo esc_attr( $tracking_style ); ?>">
                <?php
                $num_items = count( $order_instance->get_items() );
                $i         = 0;
                foreach ( $order_instance->get_items() as $item_id => $item ) :
                    $product       = $item->get_product();
                    $sku           = '';
                    $purchase_note = '';
                    $image         = '';
                    $image_size    = [ 64, 64 ];

                    if ( is_object( $product ) ) {
                        $sku           = $product->get_sku();
                        $purchase_note = $product->get_purchase_note();
                        $image         = $product->get_image( $image_size );
                    } else {
                        $image = '<img src=' . esc_url( ast_pro()->plugin_dir_url() ) . 'assets/images/dummy-product-image.jpg>';
                    }

                    ?>
                    <tr style="<?php echo esc_attr( $tracking_style ); ?>">
                        <?php
                        if ( $image ) {
                            ?>
                            <td class="td image_id" style="<?php echo esc_attr( $tracking_style ); ?>">
                                <div style="margin:10px 10px 10px 0;">
                                    <?php echo wp_kses_post( apply_filters( 'woocommerce_order_item_thumbnail', $image, $item ) ); ?>
                                </div>   
                            </td>
                        <?php } ?>
                        <td class="td" style="<?php echo esc_attr( $tracking_style ); ?>">
                            <?php
                            // Product name.
                            echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $item->get_name(), $item, false ) );
                            echo ' x ';

                            $qty          = $item->get_quantity();
                            $refunded_qty = $order_instance->get_qty_refunded_for_item( $item_id );

                            if ( $refunded_qty ) {
                                $qty_display = '<del>' . esc_html( $qty ) . '</del> <ins>' . esc_html( $qty - ( $refunded_qty * -1 ) ) . '</ins>';
                            } else {
                                $qty_display = esc_html( $qty );
                            }
                            echo wp_kses_post( apply_filters( 'woocommerce_email_order_item_quantity', $qty_display, $item ) );

                            if ( $display_shippment_item_price ) {
                                echo ' - ';
                                echo wp_kses_post( $order_instance->get_formatted_line_subtotal( $item ) );
                            }

                            // allow other plugins to add additional product information here.

                            wc_display_item_meta(
                                $item,
                                [
                                    'label_before' => '<strong class="wc-item-meta-label" style="float: ' . esc_attr( $text_align ) . '; margin-' . esc_attr( $margin_side ) . ': .25em; clear: both">',
                                ]
                            );

                            // allow other plugins to add additional product information here.
                            ?>
                        </td>   
                    </tr>
                <?php endforeach; ?>
            </tbody>            
        </table>
    </div>
    <?php endif; ?>
<?php

