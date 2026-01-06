<?php
// Display Christmas Box upsell on the Checkout page.



// Define the allowed user ID
//define('ALLOWED_USER_ID', 706); // Replace with your specific user ID

// Add an init check to ensure functions are available
//add_action('init', function() {
    //if (!is_user_logged_in() || get_current_user_id() !== ALLOWED_USER_ID) {
    //    return; // Exit early if the user is not authorized
 //   }


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

include_once plugin_dir_path(__FILE__) . 'helpers.php';

// Function to display Christmas Box upsell under the review order table on the checkout page
function display_christmas_box_upsell_in_checkout() {
    log_to_debug("Checking conditions to display Christmas Box upsell on the checkout page.");

    if (cart_contains_voucher_products()) {
        log_to_debug("The cart contains voucher products.");

        if (!christmas_box_in_cart()) {
            log_to_debug("The Christmas Box is not already in the cart.");

            $product_id = get_christmas_box_product_id();
            if ($product_id) {
                log_to_debug("Found Christmas Box product with ID: $product_id");

                $product = wc_get_product($product_id);
                if ($product) {
                    echo '<div id="christmas-box-upsell-checkout" class="christmas-box-upsell" style="margin-top: 20px; padding: 10px; border: 1px solid #ddd;">';
                    echo '<div class="product-image" style="float:left; margin-right: 15px;">' . $product->get_image('thumbnail') . '</div>';
                    echo '<h3>' . esc_html($product->get_title()) . '</h3>';
                    echo '<p><strong>' . wc_price($product->get_price()) . '</strong></p>';
                    echo '<p>' . wp_kses_post($product->get_description()) . '</p>';
                    echo '<p><a href="' . esc_url('?add-to-cart=' . $product_id) . '" class="button add_to_cart_button">' . __('Add to cart', 'woocommerce') . '</a></p>';
                    echo '<div style="clear: both;"></div>';
                    echo '</div>';
                } else {
                    log_to_debug("Could not retrieve the Christmas Box product.");
                }
            } else {
                log_to_debug("No valid Christmas Box product ID found.");
            }
        } else {
            log_to_debug("Christmas Box is already in the cart.");
        }
    } else {
        log_to_debug("Cart does not contain voucher products.");
    }
}

// Hook into WooCommerce to output the upsell HTML
add_action('woocommerce_checkout_after_order_review', 'display_christmas_box_upsell_in_checkout');

// Enqueue JavaScript to move the upsell content after the checkout review order table
function enqueue_christmas_box_upsell_script() {
    if (is_checkout()) {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            console.log('Running Christmas Box upsell JS');
            var upsell = $('#christmas-box-upsell-checkout');
            if (upsell.length) {
                upsell.insertAfter('.woocommerce-checkout-review-order-table').show();
                console.log('Christmas Box upsell moved and displayed');
            } else {
                console.log('No Christmas Box upsell found to display');
            }
        });
        </script>
        <?php
    }
}
add_action('wp_footer', 'enqueue_christmas_box_upsell_script');


//});