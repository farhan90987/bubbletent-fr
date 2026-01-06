<?php
// helpers.php

// Function to log to WordPress debug.log
function log_to_debug($message) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("[Christmas Box Plugin]: " . $message);
    }
}

// Function to retrieve the appropriate Christmas Box product ID based on the current language
function get_christmas_box_product_id() {
    global $christmas_box_products; // Ensure this global is available from config.php

    // Detect the current language using WPML or fallback to English if language isn't detected
    $current_language = apply_filters('wpml_current_language', NULL);

    // Return the product ID for the current language, defaulting to English if not available
    if (isset($christmas_box_products[$current_language])) {
        return $christmas_box_products[$current_language];
    } else {
        return $christmas_box_products['en']; // Default to English
    }
}

// Function to check if the order contains a Christmas Box product
function order_contains_christmas_box($order) {
    global $christmas_box_products; // Load the product IDs from config.php

    foreach ($order->get_items() as $item_id => $item) {
        $product_id = $item->get_product_id();
        if (in_array($product_id, $christmas_box_products)) {
            log_to_debug("Christmas Box product found in order #" . $order->get_id() . " with product ID: $product_id");
            return true;
        }
    }
    return false;
}

// Function to check if the order contains voucher-eligible products (downloadable or PDF coupon)
function order_contains_voucher_products($order) {
    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        if ($product->is_downloadable() || get_post_meta($product->get_id(), '_wpdesk_pdf_coupons', true)) {
            log_to_debug("Voucher-eligible product found in order #" . $order->get_id());
            return true;
        }
    }
    return false;
}

// Function to retrieve available voucher code from the database
function get_available_voucher_code() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_excel_table';
    $voucher_code = $wpdb->get_var("SELECT vouchercode FROM $table_name WHERE status = 0 LIMIT 1");
    if ($voucher_code) {
        $wpdb->update($table_name, ['status' => 1], ['vouchercode' => $voucher_code]);
        log_to_debug("Voucher code $voucher_code retrieved from the database and marked as used.");
    } else {
        log_to_debug("No unused voucher code found in the database.");
    }
    return $voucher_code;
}

// Display the voucher code in the admin order view under shipping details
add_action('woocommerce_admin_order_data_after_shipping_address', 'display_voucher_code_in_order_shipping_section');

function display_voucher_code_in_order_shipping_section($order) {
    // Get the voucher code from the order meta
    $voucher_code = get_post_meta($order->get_id(), '_voucher_code', true);

    // Only display the voucher code if it exists
    if (!empty($voucher_code)) {
        echo '<div style="background-color: #f7f7f7; padding: 15px; border: 1px solid #dcdcdc; border-radius: 5px; margin-top: 20px;">';
        echo '<h3 style="margin: 0 0 10px 0;">' . __('Voucher Code', 'woocommerce') . '</h3>';
        echo '<p style="font-size: 16px; font-weight: bold;">' . esc_html($voucher_code) . '</p>';
        echo '</div>';
    }
}




// Function to check if the cart contains eligible products
function cart_contains_eligible_products() {
    foreach (WC()->cart->get_cart() as $cart_item) {
        $product = wc_get_product($cart_item['product_id']);

        // Check for downloadable products or PDF coupons
        if ($product->is_downloadable() || get_post_meta($product->get_id(), '_wpdesk_pdf_coupons', true) === 'yes') {
            return true;
        }
    }
    return false;
}

// AJAX handler for fetching the Christmas Box popup
add_action('wp_ajax_get_christmas_box_popup', 'get_christmas_box_popup');
add_action('wp_ajax_nopriv_get_christmas_box_popup', 'get_christmas_box_popup');

function get_christmas_box_popup() {
    // Check if the session flag is set to trigger the popup
    if (!WC()->session->get('show_christmas_box_popup')) {
        log_to_debug("Popup trigger flag not set in session. Exiting popup handler.");
        wp_send_json_error(['message' => 'Popup not triggered.']);
    }

    $product_id = get_christmas_box_product_id();
    $product = wc_get_product($product_id);

    if (!$product) {
        log_to_debug("Christmas Box product not found.");
        wp_send_json_error(['message' => 'Christmas Box product not found.']);
    }

    log_to_debug("Preparing Christmas Box popup content.");

    $popup_html = '
    <div id="christmas-box-popup">
        <div class="popup-container">
            <button id="close-popup-btn" class="popup-close">×</button>
            <div class="popup-content">
                <div class="popup-image">
                    ' . $product->get_image('medium') . '
                </div>
                <div class="popup-text">
                    <h2 class="popup-heading">' . __('Donne une touche spéciale à ton cadeau !', 'vcb') . '</h2>
                    <p class="popup-description">' . __("<b>Tu souhaites offrir ton bon cadeau dans une boîte cadeau élégante ?</b><br> Choisis maintenant ou lors du paiement notre option exclusive de boîte cadeau et reçois ton bon cadeau prêt à être offert. Pas besoin d'imprimer le bon cadeau !", 'vcb') . '</p>
                    <p class="popup-description">' . __('Ce que tu reçois', 'vcb') . '</p>
                    <ul class="popup-list">
                        <li>' . __('Une boîte cadeau de qualité avec un bon et un livret', 'vcb') . '</li>
                        <li>' . __("Une douce surprise à l'intérieur", 'vcb') . '</li>
                    </ul>
                    <a href="' . esc_url(wc_get_cart_url() . '?add-to-cart=' . $product_id) . '" id="add-christmas-box-btn" class="popup-button">
                        ' . __('Ajouter une boîte cadeau', 'vcb') . '
                    </a>
                </div>
            </div>
            <p class="popup-footer">
                ' . __("", 'vcb') . '
            </p>
        </div>
    </div>';

    // Clear the session flag after displaying the popup
    WC()->session->__unset('show_christmas_box_popup');

    wp_send_json_success(['popup_html' => $popup_html]);
}
