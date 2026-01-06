<?php 

add_shortcode('woo_j_cart_count','woo_j_show_cart_count');

function woo_j_show_cart_count( $atts )
{
    $attr = shortcode_atts( array(
		        'price'             => false,		        
                'noicon'            => false,
                'demo'              => false,
	), $atts );

    $total = ( $attr['demo'] || is_admin() ) ? 25 : esc_html( round( WC()->cart->get_subtotal(), wc_get_price_decimals() ) );
    $count = ( $attr['demo'] || is_admin() ) ? 5  : WC()->cart->get_cart_contents_count();
    
    ob_start();	

    ?>

        <div class="wc-j-upsellator-cart-count-container wc-j-upsellator-show-cart <?php echo !$attr['noicon'] ? '' : 'no-icon' ?>">
            <div class="flex-row-center">
                <?php 
                
                    if( $attr['price'] ):
                       
                         woo_j_price( $total, ['wcj-shortcode-cart-total']  );                     

                     endif; ?>

                <div class="icon-count-container">
                    <?php if( !$attr['noicon'] ): ?> 
                            <div class="wc-j-upsellator-cart-count-icon">
                                <i class="<?php echo esc_attr( woo_j_styles('modal_icon') ) ?>"></i>
                            </div>
                    <?php endif; ?>   

                    <div class="wc-j-upsellator-cart-count">
                            <?php echo  $count > 0 ?  $count : '' ?>
                    </div>

                </div>
                       
            </div>
        </div>

    <?php


    $result = ob_get_clean();
    return $result;

}


?>