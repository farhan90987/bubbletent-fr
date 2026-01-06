<?php

defined( 'ABSPATH' ) || exit;

use YayMail\Utils\TemplateHelpers;
use YayMail\Utils\Helpers;

if ( ! isset( $args['order'] ) || ! ( Helpers::is_woocommerce_order( $args['order'] ) ) || ( ! isset( $args['ast'] ) && empty( $args['ast'] ) ) ) {
    return;
}

$margin_side  = is_rtl() ? 'left' : 'right';
$element_data = isset( $args['element']['data'] ) ? $args['element']['data'] : [];
$ast          = isset( $args['ast'] ) ? $args['ast'] : '';
$order        = isset( $args['order'] ) ? $args['order'] : '';

$is_placeholder = isset( $args['is_placeholder'] ) ? $args['is_placeholder'] : false;
$plain_text     = isset( $data['plain_text'] ) ? $data['plain_text'] : false;
$sent_to_admin  = isset( $data['sent_to_admin'] ) ? $data['sent_to_admin'] : false;
$email          = isset( $data['email'] ) ? $data['email'] : false;

if ( $order && $email ) {
    do_action( 'wcast_email_before_order_table', $order, $sent_to_admin, $plain_text, $email );
    do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email );
}

$emails                       = \WC_Emails::instance()->get_emails();
$ast_customizer               = Ast_Customizer::get_instance();
$border_color                 = $is_placeholder ? TemplateHelpers::get_content_as_placeholder( 'border_color', '', $is_placeholder ) : ( $element_data['border_color'] ?? ' #e0e0e0' );
$display_product_images       = $ast->get_checkbox_option_value_from_array( 'woocommerce_customer_shipped_order_settings', 'display_product_images', $ast_customizer->defaults['display_product_images'] );
$display_shippment_item_price = $ast->get_checkbox_option_value_from_array( 'woocommerce_customer_shipped_order_settings', 'display_shippment_item_price', $ast_customizer->defaults['display_shippment_item_price'] );

$style = TemplateHelpers::get_style(
    [
        'font-size'   => '14px',
        'font-family' => TemplateHelpers::get_font_family_value( isset( $element_data['font_family'] ) ? $element_data['font_family'] : 'inherit' ),
        'color'       => isset( $element_data['text_color'] ) ? $element_data['text_color'] : 'inherit',
        'text-align'  => yaymail_get_text_align(),
    ]
);

?>  

<table class="yaymail-ast-order-details-table" cellspacing="0" cellpadding="0" width="100%" >
    <tbody>
        <?php
        $num_items = count( $order->get_items() );
        $i         = 0;

        foreach ( $order->get_items() as $item_id => $item ) :
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

            $last_td = false;
            if ( ++$i === $num_items ) {
                $last_td = true;
            }

            ?>
            <tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'order_item', $item, $order ) ); ?>">
                <?php
                if ( $display_product_images && $image ) {
                    ?>
                    <td class="td image_id" style="<?php echo esc_attr( $style ); ?> ;width: 70px; border: 0 ;border-top:1px solid <?php echo esc_attr( $border_color ); ?>; vertical-align: middle; padding: 12px 5px; <?php echo $last_td ? 'border-bottom:1px solid ' . esc_attr( $border_color ) : ''; ?>">
                        <?php echo wp_kses_post( apply_filters( 'woocommerce_order_item_thumbnail', $image, $item ) ); ?>
                    </td>
                <?php } ?>
                <td class="td" style="<?php echo esc_attr( $style ); ?> ;text-align: left; border: 0 ;border-top:1px solid <?php echo esc_attr( $border_color ); ?>; <?php echo $last_td ? 'border-bottom:1px solid ' . esc_attr( $border_color ) : ''; ?>">
                    <?php
                    echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $item->get_name(), $item, false ) );
                    echo ' x ';

                    $qty          = $item->get_quantity();
                    $refunded_qty = $order->get_qty_refunded_for_item( $item_id );

                    if ( $refunded_qty ) {
                        $qty_display = '<del>' . esc_html( $qty ) . '</del> <ins>' . esc_html( $qty - ( $refunded_qty * -1 ) ) . '</ins>';
                    } else {
                        $qty_display = esc_html( $qty );
                    }
                    echo wp_kses_post( apply_filters( 'woocommerce_email_order_item_quantity', $qty_display, $item ) );

                    if ( $display_shippment_item_price ) {
                        echo ' - ';
                        echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) );
                    }

                    if ( 'new:line_items0' !== $item_id && 'new:line_items1' != $item_id ) {
                        // allow other plugins to add additional product information here.
                        do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, $plain_text );
                    }

                    wc_display_item_meta(
                        $item,
                        [
                            'label_before' => '<strong class="wc-item-meta-label" style="float: ' . esc_attr( yaymail_get_text_align() ) . '; margin-' . esc_attr( $margin_side ) . ': .25em; clear: both">',
                        ]
                    );

                    if ( 'new:line_items0' != $item_id || 'new:line_items1' != $item_id ) {
                        // allow other plugins to add additional product information here.
                        do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, $plain_text );
                    }
                    ?>
                </td>   
            </tr>
        <?php endforeach; ?>
    </tbody>            
</table>

<?php

if ( $order && $email ) {
    do_action( 'wcast_email_after_order_table', $order, $sent_to_admin, $plain_text, $email );
    do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email );
}
