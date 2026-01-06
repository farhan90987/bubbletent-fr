<?php
/*
Plugin Name: Voucher Product Christmas Box with Email Printing
Description: Sends an email to a printer with order details after creating a coupon.
Version: 3.0
Author: Mathesconsulting
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include necessary files
include_once plugin_dir_path(__FILE__) . 'includes/config.php'; // Configuration
include_once plugin_dir_path(__FILE__) . 'includes/helpers.php'; // Helpers file
include_once plugin_dir_path(__FILE__) . 'includes/coupon-handler.php'; // Coupon handler
include_once plugin_dir_path(__FILE__) . 'includes/email-handler.php'; // Email handler
include_once plugin_dir_path(__FILE__) . 'includes/cart-upsell.php';

// Enqueue assets for the pop-up
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script(
        'christmas-box-popup-js',
        plugin_dir_url(__FILE__) . 'assets/js/christmas-box-popup.js',
        ['jquery'],
        '1.0',
        true
    );

    wp_enqueue_style(
        'christmas-box-popup-css',
        plugin_dir_url(__FILE__) . 'assets/css/christmas-box-popup.css',
        [],
        '1.0'
    );

    wp_localize_script('christmas-box-popup-js', 'christmasBoxPopup', [
        'ajax_url' => admin_url('admin-ajax.php'),
    ]);
});

// Hook to track products added to the cart
add_action('woocommerce_add_to_cart', function ($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
    $product = wc_get_product($product_id);

    // Check if the product is eligible (downloadable or has PDF voucher meta)
    if ($product->is_downloadable() || get_post_meta($product_id, '_wpdesk_pdf_coupons', true) === 'yes') {
        WC()->session->set('show_christmas_box_popup', true);
        log_to_debug("Eligible product added to cart. Popup will trigger.");
    } else {
        log_to_debug("Non-eligible product added to cart. Popup will not trigger.");
    }
}, 10, 6);

// Render the pop-up container for dynamic content
add_action('wp_footer', function () {
    echo '<div id="christmas-box-popup" style="display: none;"></div>';
});


// Initialization hook if needed
function voucher_christmas_box_init() {
    log_to_debug("Voucher Christmas Box Plugin Initialized.");
}
add_action('init', 'voucher_christmas_box_init');
