<?php
// Display Christmas Box upsell on Cart and Checkout Pages.


// Define the allowed user ID
//define('ALLOWED_USER_ID', 706); // Replace with your specific user ID

// Add an init check to ensure functions are available
//add_action('init', function() {
  //  if (!is_user_logged_in() || get_current_user_id() !== ALLOWED_USER_ID) {
    //    return; // Exit early if the user is not authorized
   // }


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

include_once plugin_dir_path(__FILE__) . 'helpers.php';

// Function to check if the cart contains either downloadable or PDF coupon products
function cart_contains_voucher_products() {
    foreach (WC()->cart->get_cart() as $cart_item) {
        $product = wc_get_product($cart_item['product_id']);

        // Exclude 'listing_booking' products
        if ($product->is_type('listing_booking')) {
            return false;
        }

        // Check if the product is downloadable or has a PDF voucher meta value of "yes"
        if ($product->is_downloadable() || get_post_meta($product->get_id(), '_wpdesk_pdf_coupons', true) === 'yes') {
            return true;
        }
    }
    return false;
}

// Function to check if the Christmas Box product is already in the cart
function christmas_box_in_cart() {
    $product_id = get_christmas_box_product_id();
    foreach (WC()->cart->get_cart() as $cart_item) {
        if ($cart_item['product_id'] == $product_id) {
            return true;
        }
    }
    return false;
}

// Display the Christmas Box upsell under the cart form contents
function display_christmas_box_upsell_in_cart() {
    if (cart_contains_voucher_products() && !christmas_box_in_cart()) {
        $product_id = get_christmas_box_product_id();

        if ($product_id) {
            $product = wc_get_product($product_id);

            if ($product) {
                echo '<div class="christmas-box-upsell" style="margin-top: 20px; padding: 10px; border: 1px solid #ddd;">';
                echo '<div class="product-image" style="float:left; margin-right: 15px;">' . $product->get_image('thumbnail') . '</div>';
                echo '<h3>' . esc_html($product->get_title()) . '</h3>';
                echo '<p><strong>' . wc_price($product->get_price()) . '</strong></p>';
                echo '<p>' . wp_kses_post($product->get_description()) . '</p>';
                echo '<p><a href="' . esc_url('?add-to-cart=' . $product_id) . '" class="button add_to_cart_button">' . __('Add to cart', 'woocommerce') . '</a></p>';
                echo '<div style="clear: both;"></div>';
                echo '</div>';
            }
        }
    }
}

// Hook to display Christmas Box upsell in cart
add_action('woocommerce_after_cart_table', 'display_christmas_box_upsell_in_cart');


//});