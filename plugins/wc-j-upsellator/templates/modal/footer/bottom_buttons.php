<div class="flex-row-center footer-buttons">					
    <?php if( woo_j_conf('cart_button') ): ?> 

        <a href='<?php echo wc_get_cart_url() ?>' rel='nofollow' title='Go to cart' class="wc-upsellator-footer-button cart">
                <div class='wc-timeline-button wc-timeline-checkout-button flex-row-center'>
                            <?php echo wjc__( wp_unslash( esc_html( woo_j_conf('text_cart_button') ) ) ) ?>
                </div>
        </a>

    <?php endif; ?> 

    <a href='<?php echo wc_get_checkout_url() ?>' rel='nofollow' title='Checkout' class="wc-upsellator-footer-button checkout">
            <div class='wc-timeline-button wc-timeline-checkout-button flex-row-center'>
                        <?php echo wjc__( wp_unslash( esc_html( woo_j_conf('text_checkout') ) ) ) ?>
            </div>
    </a>

    <?php do_action( 'wcjfw_after_checkout_button' ); ?>

</div>