<?php
/**
 * Order details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/ast-pro-tpi-email-order-details.php.
 *
 */

use YayMail\Utils\TemplateHelpers;
use YayMail\Utils\Helpers;

if ( ! isset( $args['order'] ) || ! ( Helpers::is_woocommerce_order( $args['order'] ) ) || ( ! isset( $args['ast'] ) && empty( $args['ast'] ) ) ) {
    return;
}

$ast            = $args['ast'];
$order_instance = $args['order'];
$order_id       = $ast->get_formated_order_id( $order_instance->get_id() );
$tracking_items = $ast->get_tracking_items( $order_id, true );

$text_align  = yaymail_get_text_align();
$margin_side = is_rtl() ? 'left' : 'right';

$table_font_size = '';
$kt_woomail      = get_option( 'kt_woomail' );

if ( ! empty( $kt_woomail ) && isset( $kt_woomail['font_size'] ) ) {
    $table_font_size = 'font-size:' . $kt_woomail['font_size'] . 'px';
}

$ast_customizer = Ast_Customizer::get_instance();

$button_background_color      = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_button_background_color', $ast_customizer->defaults['fluid_button_background_color'] );
$button_font_color            = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_button_font_color', $ast_customizer->defaults['fluid_button_font_color'] );
$button_radius                = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_button_radius', $ast_customizer->defaults['fluid_button_radius'] );
$fluid_button_text            = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_button_text', $ast_customizer->defaults['fluid_button_text'] );
$display_shippment_item_price = $ast->get_checkbox_option_value_from_array( 'woocommerce_customer_completed_order_settings', 'display_shippment_item_price', $ast_customizer->defaults['display_shippment_item_price'] );

$tracking_style = TemplateHelpers::get_style(
    [
        'color'       => isset( $data['text_color'] ) ? $data['text_color'] : 'inherit',
        'text-align'  => $text_align,
        'font-size'   => '14px',
        'font-family' => TemplateHelpers::get_font_family_value( isset( $data['font_family'] ) ? $data['font_family'] : 'inherit' ),
        'border'      => 'none',
    ]
);

$table_style = TemplateHelpers::get_style(
    [
        'width' => '100%',
    ]
);
?>

    <?php
    $total_trackings = count( $tracking_items );
    $layout          = 'tpi_layout_2';
    foreach ( $tracking_items as $tracking_item ) {
        if ( isset( $tracking_item['products_list'] ) && ! empty( $tracking_item['products_list'] ) && count( $tracking_item['products_list'] ) > 1 ) {
            $layout = 'tpi_layout_2';
            continue;
        }
    }

    if ( 'tpi_layout_2' === $layout ) {

        $num = 1;
        foreach ( $tracking_items as $tracking_item ) {
            $heading_class = ( isset( $tracking_item['products_list'] ) && ! empty( $tracking_item['products_list'] ) && count( $tracking_item['products_list'] ) === 1 ) ? 'heading_border' : '';

            if ( $total_trackings > 1 ) {
                    /* translators: %1$s: search number, %2$s: search total trackings */
                    echo '<p class="shipment_heading"><strong><i>' . sprintf( esc_html__( 'Shipment %1$s (out of %2$s):', 'ast-pro' ), esc_html( $num ), esc_html( $total_trackings ) ) . '</i></strong></p>';
            }

            $tpi_item   = [];
            $tpi_item[] = $tracking_item;

            $args ['tracking_items'] = $tpi_item;

            yaymail_kses_post_e( yaymail_get_content( 'src/Integrations/AdvancedShipmentTrackingByZorem/Templates/Shortcodes/tracking-information/main.php', $args ) );

            $num_items = 0;
            if ( is_array( $tracking_item['products_list'] ) ) {
                ?>
            <div style="margin-top: 15px;">
                <table class="td tpi_order_details_table" cellspacing="0" cellpadding="6" border="0" style="<?php echo esc_attr( $tracking_style ); ?>">
                    <tbody>
                        <?php
                        $num_items = count( $tracking_item['products_list'] );

                        $i = 0;
                        foreach ( (array) $tracking_item['products_list'] as $products_list ) {
                            $product = wc_get_product( $products_list->product );

                            if ( ! is_object( $product ) ) {
                                continue;
                            }

                            $image_size    = [ 64, 64 ];
                            $product_id    = $product->get_id();
                            $sku           = $product->get_sku();
                            $purchase_note = $product->get_purchase_note();
                            $image         = $product->get_image( $image_size );


                            foreach ( $order_instance->get_items() as $item_id => $item ) {
                                $item_product = $item->get_product();

                                if ( ! is_object( $item_product ) ) {
                                    continue;
                                }

                                $item_product_id = $item_product->get_id();
                                if ( $item_product_id === $product_id ) {
                                    $order_item = $item;
                                }
                            }

                            ?>
                            <tr style="<?php echo esc_attr( $tracking_style ); ?>">
                                <?php
                                if ( $image ) {
                                    ?>
                                    <td class="td image_id" style="<?php echo esc_attr( $tracking_style ); ?>">
                                        <?php echo wp_kses_post( apply_filters( 'woocommerce_order_item_thumbnail', $image, $item ) ); ?>
                                    </td>
                                <?php } ?>
                                <td class="td" style="<?php echo esc_attr( $tracking_style ); ?>">
                                    <?php
                                    // Product name.
                                    echo '<div>';
                                    echo wp_kses_post( $product->get_name() );
                                    echo ' x ';
                                    echo esc_html( $products_list->qty );

                                    if ( $display_shippment_item_price ) {
                                        echo ' - ';
                                        echo wp_kses_post( $order_instance->get_formatted_line_subtotal( $order_item ) );
                                    }
                                    echo '</div>';
                                    ?>
                                </td>   
                            </tr>   
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
                <?php
            }
            $num++;
        }
    } else {
        $num_items = count( $tracking_items );
        $i         = 0;
        foreach ( $tracking_items as $tracking_item ) {
            ?>
        <div style="margin-top: 15px;">         
            <table class="td tpi_order_details_table" cellspacing="0" cellpadding="6" border="0" style="<?php echo esc_attr( $tracking_style . $table_style ); ?>">
                <tbody>
                    <?php

                    foreach ( $tracking_item['products_list'] as $products_list ) {
                        $product       = wc_get_product( $products_list->product );
                        $product_id    = $product->get_id();
                        $sku           = '';
                        $purchase_note = '';
                        $image         = '';
                        $image_size    = [ 64, 64 ];

                        if ( is_object( $product ) ) {
                            $sku           = $product->get_sku();
                            $purchase_note = $product->get_purchase_note();
                            $image         = $product->get_image( $image_size );
                        }

                        foreach ( $order_instance->get_items() as $item_id => $item ) {
                            $item_product    = $item->get_product();
                            $item_product_id = $item_product->get_id();
                            if ( $item_product_id === $product_id ) {
                                $order_item = $item;
                            }
                        }

                        ?>
                        <tr style="<?php echo esc_attr( $tracking_style ); ?>">
                        <?php
                        if ( $image ) {
                            ?>
                                <td class="td image_id" style="<?php echo esc_attr( $tracking_style ); ?>">
                                <?php echo wp_kses_post( apply_filters( 'woocommerce_order_item_thumbnail', $image, $item ) ); ?>
                                </td>
                            <?php } ?>
                            <td class="td" style="<?php echo esc_attr( $tracking_style ); ?>">
                                <?php
                                // Product name.
                                echo wp_kses_post( $product->get_name() );
                                echo ' x ';
                                echo esc_html( $products_list->qty );

                                if ( $display_shippment_item_price ) {
                                    echo ' - ';
                                    echo wp_kses_post( $order_instance->get_formatted_line_subtotal( $order_item ) );
                                }

                                echo '<div style="margin-top:10px;"><span style="font-size: 90%;">' . esc_html( $tracking_item['formatted_tracking_provider'] ) . '<a style="font-size: 90%;margin: 0 10px 0 5px;text-decoration: none;" href=' . esc_url( $tracking_item['ast_tracking_link'] ) . '><span>' . esc_html( $tracking_item['tracking_number'] ) . '</span></a></span> </div>';
                                ?>
                            </td>
                            <td class="td" style="<?php echo esc_attr( $tracking_style ); ?>;">
                                <?php echo '<a class="button track-button" href=' . esc_url( $tracking_item['ast_tracking_link'] ) . '><span>' . esc_html( $fluid_button_text ) . '</span></a>'; ?>
                            </td>   
                        </tr>   
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
            <?php
        }
    }
    ?>
</div>
<style>
    a.button.track-button {
    background: <?php echo esc_html( $button_background_color ); ?>;
    color: <?php echo esc_html( $button_font_color ); ?> !important;
    padding: 8px 15px;
    text-decoration: none;
    border-radius: <?php echo esc_html( $button_radius ); ?>px;
    margin-top: 0;
    font-size: 90% !important;
}
</style>
<?php

