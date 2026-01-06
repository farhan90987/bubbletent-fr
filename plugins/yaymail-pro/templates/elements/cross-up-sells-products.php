<?php
defined( 'ABSPATH' ) || exit;
use YayMail\Utils\TemplateHelpers;
use YayMail\Models\ProductModel;
use YayMail\Utils\Helpers;

/**
 * $args includes
 * $element
 * $render_data
 * $is_nested
 */
if ( empty( $args['element'] ) ) {
    return;
}

$element               = $args['element'];
$settings              = $args['settings'];
$data                  = $element['data'];
$render_data           = $args['render_data'];
$is_sample             = isset( $render_data['is_sample'] ) ? $render_data['is_sample'] : false;
$is_customized_preview = isset( $render_data['is_customized_preview'] ) ? $render_data['is_customized_preview'] : false;

$showing_items = isset( $data['showing_items'] ) ? $data['showing_items'] : [];
$top_content   = isset( $data['top_content'] ) ? $data['top_content'] : '';
$order_data    = Helpers::get_order_from_shortcode_data( $render_data );
// Get Cross/Up sells products from ProductModel
$params['max_products_displayed'] = isset( $data['max_products_displayed'] ) ? $data['max_products_displayed'] : 0;
$params['linked_products_type']   = isset( $data['linked_products_type'] ) ? $data['linked_products_type'] : 'cross_sells';
$params['order_id']               = ! empty( $order_data ) && Helpers::is_woocommerce_order( $order_data ) ? $order_data->get_id() : ( $is_sample || $is_customized_preview ? 'sample_order' : 0 );

$product_model = ProductModel::get_instance();
$products      = $product_model->get_cross_up_sells_products( $params );

if ( empty( $products ) ) {
    return;
}

$wrapper_style = TemplateHelpers::get_style(
    [
        'word-break'       => 'break-word',
        'background-color' => $data['background_color'],
        'font-family'      => isset( $data['font_family'] ) ? TemplateHelpers::get_font_family_value( $data['font_family'] ) : 'initial',
        'padding'          => TemplateHelpers::get_spacing_value( isset( $data['padding'] ) ? $data['padding'] : [] ),
    ]
);

$text_color = isset( $data['text_color'] ) ? $data['text_color'] : 'initial';

$top_content_styles = TemplateHelpers::get_style(
    [
        'color' => $text_color,
    ]
);

$items_container_styles = TemplateHelpers::get_style(
    [
        'width'      => '100%',
        'text-align' => 'center',
    ]
);

$buy_button_label = isset( $data['buy_button_label'] ) ? $data['buy_button_label'] : __( 'BUY NOW', 'yaymail' );
$products_per_row = isset( $data['products_per_row'] ) ? $data['products_per_row'] : 1;
$container_width  = isset( $settings['container_width'] ) ? $settings['container_width'] : 605;
$items_width      = ( ( $container_width - 100 ) / $products_per_row ) - 30;
$items_styles     = TemplateHelpers::get_style(
    [
        'width'          => "{$items_width}px",
        'padding'        => '10px',
        'text-align'     => 'center',
        'vertical-align' => 'top',
    ]
);

$item_image_styles = TemplateHelpers::get_style(
    [
        'width'      => '100%',
        'object-fit' => 'cover',
    ]
);

$product_name_styles = TemplateHelpers::get_style(
    [
        'margin-top'  => '5px',
        'font-weight' => 'bold',
        'color'       => $text_color,
    ]
);

$sale_price_styles = TemplateHelpers::get_style(
    [
        'color'       => isset( $data['sale_price_color'] ) ? $data['sale_price_color'] : 'initial',
        'font-weight' => 'bold',
    ]
);

$regular_price_styles = TemplateHelpers::get_style(
    [
        'color'       => isset( $data['regular_price_color'] ) ? $data['regular_price_color'] : 'initial',
        'font-weight' => 'bold',
        'margin-left' => '5px',
    ]
);

$buy_button_styles = TemplateHelpers::get_style(
    [
        'background-color' => isset( $data['buy_button_background_color'] ) ? $data['buy_button_background_color'] : 'initial',
        'color'            => isset( $data['buy_button_text_color'] ) ? $data['buy_button_text_color'] : 'initial',
        'line-height'      => '21px',
        'font-familt'      => 'inherit',
        'margin'           => 0,
        'padding'          => '10px 15px',
        'text-align'       => 'center',
        'text-decoration'  => 'none',
    ]
);
ob_start();
?>

    <!-- Top Content -->
    <?php if ( in_array( 'top_content', $showing_items, true ) ) : ?>
    <div style="<?php echo esc_attr( $top_content_styles ); ?>">
        <?php echo wp_kses_post( $top_content ); ?>
    </div>
    <?php endif; ?>
    <!-- End Top Content -->

    <table style="<?php echo esc_attr( $items_container_styles ); ?>">
        <tr>
            <td>
                <?php
                $product_count          = 0;
                $total_rows             = ceil( count( $products ) / $products_per_row );
                $last_row_product_count = count( $products ) % $products_per_row;
                $current_row            = 0;
                foreach ( $products as $index => $product ) :
                    if ( $product_count % $products_per_row === 0 ) {
                        if ( (int) $current_row === (int) $total_rows - 1 ) {
                            echo '<table width="' . esc_attr( $last_row_product_count * $items_width ) . 'px" align="center">';
                        } else {
                            echo '<table width="100%">';
                        }
                        echo '<tr>';
                        ++$current_row;
                    }
                    ?>
                    <td style="<?php echo esc_attr( $items_styles ); ?>">
                        <!-- Product Image -->
                        <?php if ( in_array( 'product_image', $showing_items, true ) ) : ?> 
                            <a href="<?php echo esc_url( isset( $product['permalink'] ) ? $product['permalink'] : '#' ); ?>" target="_blank" rel="noneferrer">
                                <img style="<?php echo esc_attr( $item_image_styles ); ?>" src="<?php echo esc_attr( isset( $product['thumbnail_src'] ) ? $product['thumbnail_src'] : '#' ); ?>" alt="<?php echo esc_html( isset( $product['name'] ) ? $product['name'] : '#' ); ?>"></img>
                            </a>
                        <?php endif; ?>
                        <!-- End Product Image -->

                        <!-- Product Name -->
                        <?php if ( in_array( 'product_name', $showing_items, true ) ) : ?> 
                            <span style="<?php echo esc_attr( $product_name_styles ); ?>"><?php echo esc_html( isset( $product['name'] ) ? $product['name'] : '' ); ?></span>
                        <?php endif; ?>
                        <!-- End Product Name -->

                        <!-- Prices -->
                        <div style="margin-bottom: 10px">
                            <!-- Product Price -->
                            <?php if ( in_array( 'product_price', $showing_items, true ) ) : ?> 
                                <span style="<?php echo esc_attr( $sale_price_styles ); ?>">
                                    <?php
                                    if ( ! empty( $product['sale_price_html'] ) ) {
                                        echo wp_kses_post( $product['sale_price_html'] );
                                    } else {
                                        echo wp_kses_post( $product['regular_price_html'] );
                                    }
                                    ?>
                                </span>
                            <?php endif; ?>
                            <!-- End Product Price -->

                            <!-- Product Original Price -->
                            <?php if ( in_array( 'product_original_price', $showing_items, true ) && ! empty( $product['sale_price_html'] ) ) : ?> 
                                <span style="<?php echo esc_attr( $regular_price_styles ); ?>">
                                    <?php echo wp_kses_post( $product['regular_price_html'] ); ?>
                                </span>
                            <?php endif; ?>
                            <!-- End Product Original Price -->
                        </div>
                        <!-- End Prices -->

                        <!-- Buy Button -->
                        <?php if ( in_array( 'buy_button', $showing_items, true ) ) : ?> 
                            <a style="<?php echo esc_attr( $buy_button_styles ); ?>" href="<?php echo esc_url( isset( $product['permalink'] ) ? $product['permalink'] : '#' ); ?>" target="_blank" rel="noneferrer">
                                <?php echo esc_html( $buy_button_label ); ?>
                            </a>
                        <?php endif; ?>
                        <!-- End Buy Button -->
                    </td>
                    <?php
                    ++$product_count;
                    if ( $product_count % $products_per_row === 0 ) {
                        echo '</tr>';
                        echo '</table>';
                    }
                endforeach;

                // Close the last row if it's not complete
                if ( $product_count % $products_per_row !== 0 ) {
                    echo '</tr>';
                    echo '</table>';
                }
                ?>
            </td>   
        </tr>
    </table>

<?php
$element_content = ob_get_clean();

TemplateHelpers::wrap_element_content( $element_content, $element, $wrapper_style );
