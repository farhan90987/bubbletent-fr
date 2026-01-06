<?php
// auto-add-christmas-box.php

// Ensure WooCommerce is initialized before proceeding
add_action('woocommerce_init', 'initialize_christmas_box_adding');

function initialize_christmas_box_adding() {
    // Hook into WooCommerce's add to cart action when a product is added
    add_action('woocommerce_add_to_cart', 'add_christmas_box_when_product_added', 10, 2);
}

function add_christmas_box_when_product_added($cart_item_key, $product_id) {
    // Check if Christmas Box product ID is set and retrieve it from config
    $christmas_box_id = get_christmas_box_product_id();

    // Debug logs for validation
   log_to_debug("add_christmas_box_when_product_added called. Product ID added: $product_id");
   log_to_debug("Christmas Box Product ID: $christmas_box_id");

    // If Christmas Box is not set, exit early
    if (!$christmas_box_id) {
       log_to_debug("No Christmas Box ID found, exiting function.");
        return;
    }

    // Ensure Christmas Box is not already in the cart
    foreach (WC()->cart->get_cart() as $cart_item) {
        if ($cart_item['product_id'] == $christmas_box_id) {
            log_to_debug("Christmas Box is already in the cart. Exiting function.");
            return;
        }
    }

    // Check if the product added qualifies (downloadable or PDF coupon)
    $product = wc_get_product($product_id);
    if ($product->is_downloadable() || get_post_meta($product_id, '_wpdesk_pdf_coupons', true) === 'yes') {
        // Attempt to add the Christmas Box to the cart
        WC()->cart->add_to_cart($christmas_box_id);
      log_to_debug("Christmas Box added to cart.");
    } else {
        log_to_debug("Product added does not qualify, no Christmas Box added.");
    }
}

