jQuery(document).ready(function($) {
    // Move the upsell content after the woocommerce-checkout-review-order-table on the checkout page
    var upsellCheckout = $('#christmas-box-upsell-checkout');
    if (upsellCheckout.length && $('.woocommerce-checkout-review-order-table').length) {
        upsellCheckout.insertAfter('.woocommerce-checkout-review-order-table').show();
    }

    // For the cart page, make sure the upsell is shown in the right place (if it exists)
    var upsellCart = $('.christmas-box-upsell');
    if (upsellCart.length && $('#cart').length) {
        upsellCart.show(); // Ensure it's visible on the cart page
    }
});
