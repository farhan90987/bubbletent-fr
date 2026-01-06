<?php
defined( 'ABSPATH' ) || exit;
use YayMail\Utils\TemplateHelpers;
use YayMail\Models\ProductModel;

/**
 * $args includes
 * $element
 * $render_data
 * $is_nested
 */
if ( empty( $args['element'] ) ) {
    return;
}

$element     = $args['element'];
$settings    = $args['settings'];
$data        = $element['data'];
$render_data = $args['render_data'];

$showing_items = isset( $data['showing_items'] ) ? $data['showing_items'] : [];
$top_content   = isset( $data['top_content'] ) ? $data['top_content'] : '';

// Get Product Reviews from ProductModel
$params['max_reviews_per_product'] = isset( $data['max_reviews_per_product'] ) ? $data['max_reviews_per_product'] : 5;
$params['product_type']            = isset( $data['product_type'] ) ? $data['product_type'] : 'featured';
$params['sorted_by']               = isset( $data['sorted_by'] ) ? $data['sorted_by'] : 'none';
$params['review_filter']           = isset( $data['review_filter'] ) ? $data['review_filter'] : '';

// Handle product type
switch ( $params['product_type'] ) {
    case 'order_products':
        $params['order_id'] = isset( $args['render_data']['is_sample'] ) && $args['render_data']['is_sample']
            ? 'sample_order'
            : ( isset( $render_data['order'] ) ? $render_data['order']->get_id() : '' );
        break;
    case 'product_selections':
        $params['product_ids'] = isset( $data['products'] ) ? array_map(
            function( $entity ) {
                return $entity['id'];
            },
            $data['products']
        ) : [];
        break;
}

// Handle review filter
switch ( $params['review_filter'] ) {
    case 'review_filter_by_rating':
        $params['minimum_rating_to_show'] = isset( $data['minimum_rating_to_show'] ) ? $data['minimum_rating_to_show'] : 0;
        break;
    case 'review_filter_by_date':
        $params['from_date_to_date'] = isset( $data['from_date_to_date'] ) ? $data['from_date_to_date'] : null;
        break;
}

$product_model = ProductModel::get_instance();
$products      = $product_model->get_products_with_reviews( $params );

if ( empty( $products ) ) {
    return;
}

// Finish get Product Reviews

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

$reviews_per_row = isset( $data['reviews_per_row'] ) ? $data['reviews_per_row'] : 1;
$container_width = isset( $settings['container_width'] ) ? $settings['container_width'] : 605;

ob_start();
?>
    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
        <?php foreach ( $products as $product ) : ?>
            <tr>
                <td style="padding: 10px 0;">
                    <!-- Product Info -->
                    <table cellpadding="0" cellspacing="0" role="presentation">
                        <tbody>
                            <tr>
                                <td style="padding-right: 10px;">
                                    <a href="<?php echo esc_url( isset( $product['permalink'] ) ? $product['permalink'] : '#' ); ?>" target="_blank" rel="noreferrer">
                                        <img src="<?php echo esc_attr( isset( $product['thumbnail_src'] ) ? $product['thumbnail_src'] : '' ); ?>" 
                                            alt="<?php echo esc_html( isset( $product['name'] ) ? $product['name'] : '' ); ?>"
                                            style="width: 80px; height: auto; display: block;margin-right: 0;">
                                    </a>
                                </td>
                                <td style="vertical-align: middle; font-size: 14px; color: #000000;">
                                    <?php echo esc_html( isset( $product['name'] ) ? $product['name'] : '' ); ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Reviews -->
                    <?php if ( isset( $product['reviews'] ) && ! empty( $product['reviews'] ) ) : ?>
                    <table cellpadding="0" cellspacing="0" role="presentation" style="margin-top: 10px; width: 100%; text-align: <?php echo esc_attr( yaymail_get_text_align() ); ?>">
                        <tbody>
                            <tr>
                                <?php foreach ( $product['reviews'] as $review ) : ?>
                                <td style="width: calc(100% / <?php echo esc_attr( $reviews_per_row ); ?> - <?php echo esc_attr( $reviews_per_row > 1 ? 2 : 0 ); ?> * 10px); display: inline-block; padding: 10px; vertical-align: top;">
                                    <table cellpadding="0" cellspacing="0" role="presentation">
                                        <tbody>
                                            <tr>
                                                <?php if ( in_array( 'author_avatar', $showing_items, true ) ) : ?>
                                                <td style="padding-right: 10px;">
                                                    <img src="<?php echo esc_attr( isset( $review['author_avatar'] ) ? $review['author_avatar'] : '' ); ?>"
                                                        alt="<?php echo esc_html( isset( $review['author_name'] ) ? $review['author_name'] : '' ); ?>"
                                                        style="width: 50px; display: block;margin-right: 0;">
                                                </td>
                                                <?php endif; ?>
                                                <td style="vertical-align: top; font-size: 12px; color: #333; text-align: <?php echo esc_attr( yaymail_get_text_align() ); ?>">
                                                    <?php if ( in_array( 'author_name', $showing_items, true ) ) : ?>
                                                    <div style="line-height: 1.5;">
                                                        <strong><?php echo esc_html( isset( $review['author_name'] ) ? $review['author_name'] : '' ); ?></strong>
                                                    </div>
                                                    <?php endif; ?>
                                                    <?php if ( in_array( 'review_date', $showing_items, true ) ) : ?>
                                                    <div style="font-size: 10px; color: #777; line-height: 1.5;">
                                                        <?php echo esc_html( isset( $review['review_date'] ) ? $review['review_date'] : '' ); ?>
                                                    </div>
                                                    <?php endif; ?>
                                                    <?php if ( in_array( 'rating_stars', $showing_items, true ) ) : ?>
                                                    <div style="font-size: 10px; line-height: 1.5;">
                                                        <?php
                                                        $rating = isset( $review['rating_stars'] ) ? (int) $review['rating_stars'] : 0;
                                                        for ( $i = 1; $i <= 5; $i++ ) :
                                                            $color = $i <= $rating ? '#FFD700' : '#808080';
                                                            ?>
                                                            <span style="color: <?php echo esc_attr( $color ); ?>;">&#9733;</span>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <?php if ( in_array( 'review_message', $showing_items, true ) ) : ?>
                                    <div style="margin-top: 10px; font-size: 12px; color: #000000; line-height: 1.4; text-align: <?php echo esc_attr( yaymail_get_text_align() ); ?>">
                                        <?php echo esc_html( isset( $review['review_message'] ) ? $review['review_message'] : '' ); ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

<?php
$element_content = ob_get_clean();
TemplateHelpers::wrap_element_content( $element_content, $element, $wrapper_style );
