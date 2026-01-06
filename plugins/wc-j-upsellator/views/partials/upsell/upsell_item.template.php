<?php 

    $price 		= woo_j_get_price( $product['woo_product'],  get_post_meta( $product['id'], '_regular_price', true) );	
	$qty 		= $product['quantity'] ?? 1;
	//Check if we have a discount or the upsell has full price
	if( $product['discount'] )
	{
		if( $product['discount_type'] == 1 ) $price = round( $price - ( ( $price / 100 ) * $product['discount'] ) , 2);
		if( $product['discount_type'] == 0 ) $price = $price - $product['discount'];
		
	}else{

		$price 		= woo_j_get_price( $product['woo_product'],  get_post_meta( $product['id'], '_price', true ) );	
	}

	$price *= $qty;
	/*
	/* Check if the upsell should be added to cart or not
	*/
	$is_add_to_cart = !isset( $product['button_action']) || $product['button_action'] == 'add-to-cart' ? true : false;
	$product_action = $is_add_to_cart ? '?add-to-cart='.esc_attr( $product['id'] ) : get_permalink( $product['id'] );
	$class			= $is_add_to_cart ? 'add_to_cart_button ajax_add_to_cart' : '';
?>

<article class='wc-timeline-product upsell flex-row-between'>

            <div class='wc-timeline-product-data flex-row-start'>	
						
						<div class='image'>	
                            <img alt="Product loader" class="loader" src="<?php echo esc_attr( woo_j_env('img_path') ) ?>/loader.svg">							
							<?php echo $product['woo_product']->get_image( 'thumbnail' ) ?>	
							
							<?php if( $product['discount'] ): ?>	
										<?php if( $product['discount_type'] == 1 ): ?> 

											<div class="special discounted">
												<?php echo $product['discount'] ?>% <?php _e( 'off', 'woo_j_cart' ) ?>
											</div>

										<?php else: ?> 

											<div class="special discounted">
												<?php _e( 'save', 'woo_j_cart' ) ?>  <?php echo $qty * $product['discount'] ?> <?php echo  esc_html( woo_j_conf('currency') ) ?>
											</div>

										<?php endif; ?>
										

							<?php elseif( $product['woo_product']->is_on_sale() ): ?>	
											<div class="discounted">
												<?php echo wjc__( esc_html( woo_j_conf('label_on_sale') ) ) ?>
											</div>						
							<?php endif; ?>	

						</div>						
						
						<div class='heading flex-column-start'>
								
								<div style="position:relative;width:100%;">
										<div class='upsell-heading'><?php echo wjc__( $product['heading'] ) ?></div>
										<a title="<?php echo esc_attr( $product['woo_product']->get_title() ) ?>" href='<?php echo esc_attr( $product['woo_product']->get_permalink() ) ?>' class='wc-timeline-product-title'>
												<?php if( $qty > 1 ): ?>
														<span class="upsell-qty"><?php echo $qty ?>* </span>
												<?php endif; ?>
												<?php echo  wp_kses_post( $product['woo_product']->get_name() ) ?>
                                        </a>
                                        <div class="upsell-text">
                                            <?php echo wjc__( $product['text'] ) ?>
                                        </div>											
								</div>
							
						</div>
            </div>
            
            <div class='options flex-column-center'>
				
						<div class="flex-row-end">
						
							<?php if( $product['woo_product']->is_on_sale() || $product['discount'] ): 

									woo_j_price( woo_j_get_price( $product['woo_product'],  $qty * $product['woo_product']->get_regular_price() ), ['striked'] );								
							
								  endif;
								
								  woo_j_price( apply_filters('wjufw_upsell_displayed_price', $price ) ); 

							?>						
							
                        </div> 

                        <div class="wc-timeline-product-add">

                                <a <?php echo do_action( 'wjufw_add_to_cart_parameters', $product['woo_product'] ); ?> title="Add to cart" href="<?php echo $product_action ?>" data-upsell-type="<?php echo $upsell_type ?>" data-upsell="true" data-quantity="<?php echo $qty ?>" class="button product_type_simple <?php echo $class	?>" data-product_id="<?php echo esc_attr( $product['id'] ) ?>" 
                                            data-product_sku="<?php echo $product['woo_product']->get_sku() ?>" rel="nofollow">

											<?php if( $is_add_to_cart ): ?> 
												<?php echo wjc__( esc_html( woo_j_conf('text_add_to_cart') ) ) ?>
											<?php else: ?>
												<?php echo wjc__( esc_html( woo_j_conf('text_go_to_product') ?? 'view' ) ) ?>
											<?php endif; ?>
                                        
                                </a>
                        </div>
        </div> 
</article>