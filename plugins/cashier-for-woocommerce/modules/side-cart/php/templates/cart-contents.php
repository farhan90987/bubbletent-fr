<?php
/**
 * Side Cart Body (Avoid editing this template)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cashier/sidecart/cart-contents.php.
 *
 * HOWEVER, on occasion we will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen.
 *
 * @version 1.1.0
 * @package Sidecart/Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( empty( $cart_items ) ) {

	$empty_html = '';
	if ( $empty_cart_image ) {
		$empty_html .= sprintf( '<img src="%1$s" class="cfw-sc-emp-img" alt="%2$s">', $empty_cart_image, __( 'Empty Cart', 'cashier' ) );
	}

	if ( $empty_cart_text ) {
		$empty_html .= sprintf( '<span>%s</span>', $empty_cart_text );
	}

	printf( '<div class="cfw-sc-empty-cart">%s</div>', wp_kses( $empty_html, $allowed_html ) );

	return;

}

?>
<div class="cart-items"> 
<?php
foreach ( $cart_items as $cart_item_key => $cart_item ) {

	$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

	$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

	if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] < 0 || ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
		continue;
	}

	$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );

	$product_name = $_product->get_name();

	$product_name = apply_filters( 'woocommerce_cart_item_name', $product_name, $cart_item, $cart_item_key );
	$product_name = $product_permalink ? sprintf( '<a href="%s" class="cfw-sc-pname name">%s</a>', $product_permalink, $product_name ) : $product_name;

	$product_meta = wc_get_formatted_cart_item_data( $cart_item );

	$product_price = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );

	$product_subtotal = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );

	$product_img     = $_product->get_image_id();
	$product_img_url = $product_img ? wp_get_attachment_image_url( $product_img, 'full' ) : wc_placeholder_img_src();

	$thumbnail = sprintf( '<img src="%s" class="product-image"/>', $product_img_url );
	$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $thumbnail, $cart_item, $cart_item_key );
	$thumbnail = $product_permalink ? sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ) : $thumbnail;

	$args = apply_filters(
		'cfw_sc_product_args',
		array(
			'cart_item_key'     => $cart_item_key,
			'cart_item'         => $cart_item,
			'_product'          => $_product,
			'product_id'        => $product_id,
			'product_name'      => $product_name,
			'product_permalink' => $product_permalink,
			'product_meta'      => $product_meta,
			'product_price'     => $product_price,
			'product_subtotal'  => $product_subtotal,
			'thumbnail'         => $thumbnail,
			'product_classes'   => $product_classes,
			'show_pimage'       => $show_pimage,
			'show_pname'        => $show_pname,
			'show_pprice'       => $show_pprice,
			'show_premove'      => $show_premove,
			'step'              => $step,
			'min_value'         => $min_value,
			'max_value'         => $max_value,
			'input_value'       => $input_value,
			'placeholder'       => $placeholder,
			'inputmode'         => $inputmode,
			'rating_count'      => $_product->get_average_rating(),
			'allowed_html'      => $allowed_html,
		),
		$_product,
		$cart_item,
		$cart_item_key
	);
	wc_get_template( 'cart-item.php', $args );

}
?>
</div>
