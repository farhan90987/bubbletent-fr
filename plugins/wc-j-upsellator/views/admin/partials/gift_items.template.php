<div class="wp-timeline-admin-box" style="margin-top:15px;">

                <div class="row main">
                        <div class="heading"><?php 
_e( 'Active gifts', 'woo_j_cart' );
?></div>
                        <div class="option"><?php 
_e( 'Your active gifts', 'woo_j_cart' );
?></div>
                </div>

                <p class="info">
                                <?php 
_e( 'To remove a gift, click <b>delete</b>.<br>To edit an existing one, click <b>edit</b>. 
                                        ', 'woo_j_cart' );
?>
                </p>

                <section class="gifts-container">
                <?php 
$active_gifts = woo_j_gift( 'products' );

if ( !empty($active_gifts) ) {
    foreach ( $active_gifts as $gift ) {
        $product = wc_get_product( $gift['id'] );
        /* If the product has been deleted, we remove it from the gifts */
        
        if ( !is_object( $product ) ) {
            $option->removeGift( $gift['id'] );
            $option->save();
            continue;
        }
        
        $qty = $gift['quantity'] ?? 1;
        $product_qty = $gift['product_quantity'] ?? 1;
        ?>
                
                        <div class="row-wrapper product-wrapper closed gift-wrapper" data-gift="<?php 
        echo  $gift['id'] ;
        ?>">

                                <form style="display:none;" data-event="wooj:gift:deleted" action="wjufc_delete_gift" class="wjufc-ajax delete-upsell-form" data-security="<?php 
        echo  wp_create_nonce( 'wjucf-ajax' ) ;
        ?>" method="POST">
                                        <input type="hidden" value="<?php 
        echo  esc_attr( $gift['id'] ) ;
        ?>" name="id"> 
                                </form>

                                <form action="wjufc_update_gift" class="wjufc-ajax" data-security="<?php 
        echo  wp_create_nonce( 'wjucf-ajax' ) ;
        ?>" method="POST">                       

                                        <input type="hidden" value="<?php 
        echo  esc_attr( $gift['id'] ) ;
        ?>" name="id">   

                                        <div class="row">

                                                <div class="heading">
                                                                <span class="gift--heading"><?php 
        _e( 'Gift product', 'woo_j_cart' );
        ?></span>
                                                                <div class="sub-heading type">                                                        
                                                                        <?php 
        echo  str_replace( "-", " ", $gift['type'] ) ;
        ?>                                                           
                                                                </div>

                                                                <?php 
        
        if ( $gift['cart_limit'] > 0 ) {
            ?>     

                                                                        <div class="sub-heading">                                                        
                                                                                <?php 
            _e( 'subtotal', 'woo-j-cart' );
            ?>                                                    
                                                                                > &nbsp;<span class="strong-heading"><?php 
            echo  $gift['cart_limit'] . esc_html( woo_j_conf( 'currency' ) ) ;
            ?></span>                                                        
                                                                                
                                                                        </div>

                                                                <?php 
        }
        
        ?>
                                                                                                                
                                                                <?php 
        
        if ( isset( $gift['cart_limit_to'] ) && $gift['cart_limit_to'] > 0 ) {
            ?>  

                                                                        <div class="sub-heading">                                                        
                                                                                <?php 
            _e( 'subtotal', 'woo-j-cart' );
            ?>                                                    
                                                                                < &nbsp;<span class="strong-heading"><?php 
            echo  $gift['cart_limit_to'] . esc_html( woo_j_conf( 'currency' ) ) ;
            ?></span>                                                        
                                                                                
                                                                        </div>

                                                                <?php 
        }
        
        ?>
                                                                
                                                                <?php 
        ?>
                                                        
                                                </div> 

                                                <div class="option">   
                                                        <input class="selected_product" data-id="<?php 
        echo  esc_attr( $gift['id'] ) ;
        ?>" type="text" name='product_name' value="<?php 
        echo  explode( '<span', wp_kses_post( $product->get_formatted_name() ) )[0] ;
        ?>" readonly> 
                                                </div> 

                                                <div class="image">
                                                        <?php 
        echo  $product->get_image( 'thumbnail' ) ;
        ?>
                                                </div>
                                                <!-- Qty check -->
                                                <?php 
        
        if ( intval( $qty ) ) {
            ?>

                                                        <div class="alert qty">
                                                                x<?php 
            echo  $qty ;
            ?>
                                                        </div>

                                                <?php 
        }
        
        ?>
                                                <!-- Status check -->
                                                <?php 
        
        if ( $product->get_status() != 'publish' ) {
            ?>

                                                        <div class="alert">
                                                                <i class="icon-attention"></i><?php 
            _e( 'This product not set to publish', 'woo_j_cart' );
            ?>
                                                        </div>

                                                <?php 
        } elseif ( $product->managing_stock() && $product->get_stock_quantity() < $qty ) {
            ?>

                                                        <div class="alert">
                                                                <i class="icon-attention"></i>
                                                                <?php 
            _e( 'Non enough quantity in stock,<br>current stock is', 'woo_j_cart' );
            ?>
                                                                <?php 
            echo  $product->get_stock_quantity() ;
            ?>
                                                        </div>
                                                <?php 
        } elseif ( !$product->is_in_stock() ) {
            ?>
                                                        <div class="alert">
                                                                <i class="icon-attention"></i>
                                                                <?php 
            _e( 'Out of stock', 'woo_j_cart' );
            ?>                                            
                                                        </div> 
                                                <?php 
        }
        
        ?>
                                                <!-- /Status check -->                        
                                                <div class="product-actions">
                                                        
                                                        <div data-id="<?php 
        echo  esc_attr( $gift['id'] ) ;
        ?>" 
                                                                data-security="<?php 
        echo  wp_create_nonce( 'wjucf-ajax' ) ;
        ?>" 
                                                                title="<?php 
        _e( 'Pause/Active', 'woo_j_cart' );
        ?>" 
                                                                data-type="gift"
                                                                class="flex-row-center woo-j-action-round <?php 
        echo  ( isset( $gift['active'] ) && $gift['active'] == 0 ? 'pause' : '' ) ;
        ?> switch-product-status">
                                                                        <i class="<?php 
        echo  ( isset( $gift['active'] ) && $gift['active'] == 0 ? ' wooj-icon-play' : ' wooj-icon-pause' ) ;
        ?>"></i>
                                                        </div>

                                                        <div data-open="<?php 
        _e( 'Reduce', 'woo_j_cart' );
        ?>" 
                                                                data-closed="<?php 
        _e( 'Edit', 'woo_j_cart' );
        ?>" 
                                                                class="product-toggle row-submit blue">
                                                        </div>

                                                        
                                                        <button data-title="<?php 
        _e( 'Delete Gift', 'woo_j_cart' );
        ?>" 
                                                                title="<?php 
        _e( 'Delete Gift', 'woo_j_cart' );
        ?>" 
                                                                data-text="<?php 
        _e( 'Do you want to remove the selected gift?', 'woo_j_cart' );
        ?>"                                                
                                                                class="woo-j-action-round  red delete-upsell">
                                                                <i class="wooj-icon-trash"></i>
                                                        </button> 

                                                </div>   

                                        </div> 

                                        <?php 
        ?>

                                                <div class="row needs-pro">
                                                        <div class="heading"><?php 
        _e( 'One per order', 'woo_j_cart' );
        ?></div>
                                                        <div class="option">
                                                                <label class="wc-timeline-switch">
                                                                        <input type="checkbox" checked class="has-text upsellator-checkbox off">
                                                                        <span class="slider"></span>
                                                                        <span class="text-content" data-yes="<?php 
        _e( 'active', 'woo_j_cart' );
        ?>"></span>
                                                                </label>                                 
                                                        </div>
                                                        <div class="tips">
                                                                <?php 
        _e( 'Uncheck if you want to give this gift based on products quantity and not only once per order', 'woo_j_cart' );
        ?> 
                                                        </div>
                                                </div>

                                        <?php 
        ?>       
                                        
                                        <div class="row">
                                                <div class="heading">
                                                        <?php 
        _e( 'Gift quantity', 'woo_j_cart' );
        ?>
                                                </div>
                                                <div class="option has-attribute">
                                                        <span class="attribute">n.</span>
                                                        <input name="quantity" class="free gift-qty-input" type="number" value="<?php 
        echo  intval( $qty ) ;
        ?>">                           
                                                </div>
                                                <div class="tips">
                                                        <?php 
        _e( 'How many of this product should be gifted when triggered', 'woo_j_cart' );
        ?>
                                                </div>
                                        </div>

                                        <div class="row">
                                                <div class="heading">
                                                        <?php 
        _e( 'Active from', 'woo_j_cart' );
        ?>
                                                </div>
                                                <div class="option has-attribute">
                                                        <span class="attribute"><?php 
        echo  esc_html( woo_j_conf( 'currency' ) ) ;
        ?></span>
                                                        <input name="cart_limit" class="free" type="number" value="<?php 
        echo  esc_attr( $gift['cart_limit'] ) ;
        ?>">                           
                                                </div>
                                                <div class="tips">
                                                        <?php 
        _e( 'Subtotal needed to trigger the product', 'woo_j_cart' );
        ?>
                                                </div>
                                        </div>

                                        <div class="row">
                                                <div class="heading">
                                                        <?php 
        _e( 'Active to', 'woo_j_cart' );
        ?>
                                                </div>
                                                <div class="option has-attribute">
                                                        <span class="attribute"><?php 
        echo  esc_html( woo_j_conf( 'currency' ) ) ;
        ?></span>
                                                        <input name="cart_limit_to" type="number" value="<?php 
        echo  esc_attr( $gift['cart_limit_to'] ) ;
        ?>">                           
                                                </div>
                                                <div class="tips">
                                                        <?php 
        _e( 'Subtotal limit to trigger the product. Leave 0 for no limit.', 'woo_j_cart' );
        ?>
                                                </div>
                                        </div>

                                        <?php 
        ?>

                                                <div class="row needs-pro">
                                                        <div class="heading">
                                                                <?php 
        _e( 'Needs an applied coupon', 'woo_j_cart' );
        ?>
                                                        </div>
                                                        <div class="option">   
                                                                <select name="coupon" style="width: 100%;">
                                                                        <option value=""><?php 
        _e( 'No coupon needed', 'woo_j_cart' );
        ?></option>                                                        
                                                                </select> 
                                                        </div>                
                                                        <div class="tips">
                                                                <?php 
        _e( 'Product is gifted only if the customer has this coupon applied', 'woo_j_cart' );
        ?>
                                                        </div>
                                                </div> 

                                        <?php 
        ?>

                                

                                        <div class="row">
                                                <div class="heading">
                                                        <?php 
        _e( 'Exclude virtual products', 'woo_j_cart' );
        ?>
                                                </div>
                                                <div class="option has-attribute">
                                                                <label class="wc-timeline-switch">
                                                                        <input name='exclude_virtual_products' <?php 
        echo  ( isset( $gift['exclude_virtual_products'] ) && $gift['exclude_virtual_products'] === 1 ? 'checked' : '' ) ;
        ?> type="checkbox" class="has-text">
                                                                        <span class="slider"></span>
                                                                        <span class="text-content" data-yes="<?php 
        _e( 'exclude', 'woo_j_cart' );
        ?>" data-no="<?php 
        _e( 'don\'t exclude', 'woo_j_cart' );
        ?>"></span>
                                                                </label>                       
                                                </div>
                                                <div class="tips">
                                                        <?php 
        _e( 'Exclude virtual products from the cart total limit (option above)', 'woo_j_cart' );
        ?>
                                                </div>
                                        </div>

                                        <div class="row">
                                                <div class="heading">
                                                        <?php 
        _e( 'Only for logged users', 'woo_j_cart' );
        ?>
                                                </div>
                                                <div class="option has-attribute">
                                                                <label class="wc-timeline-switch">
                                                                        <input name='only_registered' <?php 
        echo  ( isset( $gift['only_registered'] ) && $gift['only_registered'] === 1 ? 'checked' : '' ) ;
        ?> type="checkbox" class="has-text">
                                                                        <span class="slider"></span>
                                                                        <span class="text-content" data-yes="<?php 
        _e( 'yes', 'woo_j_cart' );
        ?>" data-no="<?php 
        _e( 'no', 'woo_j_cart' );
        ?>"></span>
                                                                </label>                       
                                                </div>
                                                <div class="tips">
                                                        <?php 
        _e( 'Gift valid only for logged users', 'woo_j_cart' );
        ?>
                                                </div>
                                        </div>

                                        <div class="row">
                                                <div class="heading">
                                                        <?php 
        _e( 'Gifted individually', 'woo_j_cart' );
        ?>
                                                </div>
                                                <div class="option has-attribute">
                                                                <label class="wc-timeline-switch">
                                                                        <input name='gifted_individually' <?php 
        echo  ( isset( $gift['gifted_individually'] ) && $gift['gifted_individually'] === 1 ? 'checked' : '' ) ;
        ?> type="checkbox" class="has-text">
                                                                        <span class="slider"></span>
                                                                        <span class="text-content" data-yes="<?php 
        _e( 'yes', 'woo_j_cart' );
        ?>" data-no="<?php 
        _e( 'no', 'woo_j_cart' );
        ?>"></span>
                                                                </label>                       
                                                </div>
                                                <div class="tips">
                                                        <?php 
        _e( 'Gift triggered only if the same product (standard) is not already in cart.  If the gift is already in cart, it will be removed after the upsell add.', 'woo_j_cart' );
        ?>
                                                </div>
                                        </div>

                                        <div class="row">
                                                <div class="heading">
                                                        <?php 
        _e( 'Dont trigger if upselled', 'woo_j_cart' );
        ?>
                                                </div>
                                                <div class="option has-attribute">
                                                                <label class="wc-timeline-switch">
                                                                        <input name='gifted_if_not_upsell' <?php 
        echo  ( isset( $gift['gifted_if_not_upsell'] ) && $gift['gifted_if_not_upsell'] === 1 ? 'checked' : '' ) ;
        ?> type="checkbox" class="has-text">
                                                                        <span class="slider"></span>
                                                                        <span class="text-content" data-yes="<?php 
        _e( 'yes', 'woo_j_cart' );
        ?>" data-no="<?php 
        _e( 'no', 'woo_j_cart' );
        ?>"></span>
                                                                </label>                       
                                                </div>
                                                <div class="tips">
                                                        <?php 
        _e( 'Do not trigger this gift if the same product is already in cart as upsell. If the gift is already in cart, it will be removed after the upsell add.', 'woo_j_cart' );
        ?>
                                                </div>
                                        </div>

                                        <div class="row">
                                                <div class="heading">
                                                        <?php 
        _e( 'Display banner', 'woo_j_cart' );
        ?>
                                                </div>
                                                <div class="option has-attribute">
                                                                <label class="wc-timeline-switch">
                                                                        <input name='banner' <?php 
        echo  ( isset( $gift['banner'] ) && $gift['banner'] === 1 ? 'checked' : '' ) ;
        ?> type="checkbox" class="has-text">
                                                                        <span class="slider"></span>
                                                                        <span class="text-content" data-yes="<?php 
        _e( 'yes', 'woo_j_cart' );
        ?>" data-no="<?php 
        _e( 'no', 'woo_j_cart' );
        ?>"></span>
                                                                </label>                       
                                                </div>
                                                <div class="tips">
                                                        <?php 
        _e( 'Display as banner on the modal cart if not already triggered', 'woo_j_cart' );
        ?>
                                                </div>
                                        </div>

                                        <div class="row banner-text  <?php 
        echo  ( isset( $gift['banner'] ) && $gift['banner'] === 1 ? '' : 'hidden' ) ;
        ?>">
                                                <div class="heading">
                                                        <?php 
        _e( 'Banner text', 'woo_j_cart' );
        ?>
                                                </div>
                                                <div class="option">
                                                        <input name="banner_text" type="text" value="<?php 
        echo  esc_html( $gift['banner_text'] ?? '' ) ;
        ?>">
                                                </div>
                                                <div class="tips">
                                                        <?php 
        _e( 'Banner text, ex: reach 25$ to get this special gift', 'woo_j_cart' );
        ?>
                                                </div>
                                        </div>
                                        
                                        <!-- condition -->
                                        <div class="row <?php 
        echo  ( !wju_fs()->can_use_premium_code__premium_only() ? 'needs-pro' : '' ) ;
        ?>">
                                                <div class="heading">
                                                        <?php 
        _e( 'Cart must have', 'woo_j_cart' );
        ?>
                                                </div>
                                                <div class="option">
                                                        <select name="condition" class="product-condition" style="width: 100%;">
                                                                        <option <?php 
        echo  ( $gift['type'] == 'cart-limit' ? 'selected' : '' ) ;
        ?> value="cart-limit"><?php 
        _e( 'No conditions', 'woo_j_cart' );
        ?></option>
                                                                        <option <?php 
        echo  ( $gift['type'] == 'products-list' ? 'selected' : '' ) ;
        ?> value="products-list"><?php 
        _e( 'One of this/these products', 'woo_j_cart' );
        ?></option>
                                                                        <option <?php 
        echo  ( $gift['type'] == 'category-attributes-list' ? 'selected' : '' ) ;
        ?> value="category-attributes-list"><?php 
        _e( 'At least a product with category/attribute', 'woo_j_cart' );
        ?></option>
                                                        </select>                                                    
                                                </div>  

                                                <?php 
        ?>
                                        
                                        </div>                        

                                        <?php 
        ?>                       

                                        <div class="row">
                                                <div class="heading darker">
                                                        <?php 
        _e( 'Heading', 'woo_j_cart' );
        ?>
                                                </div>
                                                <div class="option">
                                                        <input class="upsell-admin-heading"  name="heading" type="text" value="<?php 
        echo  esc_html( $gift['heading'] ) ;
        ?>">
                                                </div>
                                                <div class="tips">
                                                        <?php 
        _e( 'Short text to define your gift', 'woo_j_cart' );
        ?>
                                                </div>
                                        </div>                        

                                        <div class="row">
                                                <div class="heading darker">
                                                        <?php 
        _e( 'Description', 'woo_j_cart' );
        ?>
                                                </div>
                                                <div class="option">
                                                <textarea class="upsell-admin-text"  name="text"><?php 
        echo  esc_html( $gift['text'] ) ;
        ?></textarea>
                                                </div>
                                                <div class="tips">
                                                        <?php 
        _e( 'Long text to define your gift', 'woo_j_cart' );
        ?>
                                                </div>
                                        </div>

                                        <?php 
        
        if ( !empty(woo_j_shop( 'single_product' )) || !empty(woo_j_shop( 'loop_labels' )) ) {
            ?>

                                                <div class="row">
                                                        <div class="heading">
                                                                <?php 
            _e( 'Shop label', 'woo_j_cart' );
            ?>
                                                        </div>
                                                        <div class="option">
                                                                <input name="shop_label" type="text" value="<?php 
            echo  esc_html( $gift['shop_label'] ) ;
            ?>">
                                                        </div>
                                                        <div class="tips">
                                                                <?php 
            _e( 'If shop label option is enabled, this text is displayed as a label in the product loop pages', 'woo_j_cart' );
            ?>
                                                        </div>
                                                </div>

                                        <?php 
        }
        
        ?>

                                        <?php 
        
        if ( !empty(woo_j_shop( 'single_product_text' )) ) {
            ?>

                                                <div class="row">
                                                        <div class="heading">
                                                                <?php 
            _e( 'Single product text', 'woo_j_cart' );
            ?>
                                                        </div>
                                                        <div class="option">
                                                                <textarea name="single_product_text"><?php 
            echo  esc_html( $gift['single_product_text'] ) ;
            ?></textarea>
                                                        </div>
                                                        <div class="tips">
                                                                <?php 
            _e( 'If single product text option is enabled, this text is displayed before the short description', 'woo_j_cart' );
            ?>
                                                        </div>
                                                </div>

                                        <?php 
        }
        
        ?>   

                                        <div class="row">
                                                <div class="heading darker">
                                                        <?php 
        _e( 'Preview', 'woo_j_cart' );
        ?>
                                                </div>
                                                <div class="option big">

                                                        <article class='wc-timeline-product preview free-gift flex-row-between'>
                                                                <div class='flex-row-start'>                                                                                                
                                                                        <div class='image'>	
                                                                                <div class='qty flex-row-center'>
                                                                                        <?php 
        echo  intval( $qty ) ;
        ?>
                                                                                </div>	
                                                                                <?php 
        echo  $product->get_image( 'thumbnail' ) ;
        ?>
                                                                                
                                                                                <div class="special discounted">
                                                                                        <?php 
        echo  esc_html( woo_j_conf( 'label_gift' ) ) ;
        ?>
                                                                                </div>
                                                                        
                                                                        </div>						
                                                                                        
                                                                        <div class='heading_p flex-column-start'>
                                                                                        
                                                                                        <div style="position:relative;width:100%;">
                                                                                        <div class='upsell-heading'><?php 
        echo  wp_unslash( esc_html( $gift['heading'] ) ) ;
        ?></div>
                                                                                        <div class='wc-timeline-product-title'>
                                                                                                <?php 
        echo  explode( '(', wp_kses_post( $product->get_formatted_name() ) )[0] ;
        ?>
                                                                                        </div>
                                                                                        <div class="upsell-text">  
                                                                                                <?php 
        echo  esc_html( $gift['text'] ) ;
        ?>                                                                                                           
                                                                                        </div>											
                                                                                </div>                                                                                                        
                                                                        </div>
                                                                </div>
                                                                <div class='options flex-column-center'> 
                                                                        <div></div>                                                                               
                                                                        <div class="flex-row-end">                                                            						
                                                                                
                                                                                <div class='wc-timeline-product-price'>
                                                                                        <?php 
        echo  esc_html( woo_j_conf( 'text_free_product' ) ) ;
        ?>							
                                                                                </div>
                                                                                
                                                                        </div> 
                                                                
                                                                </div> 

                                                                                                                                
                                                        </article>                                         
                                                </div>
                                        </div>
                                        
                                        <button style="float:right;" class="row-submit blue" type="submit">
                                                <i class="icon-edit"></i> <?php 
        _e( 'Update gift', 'woo_j_cart' );
        ?>
                                        </button> 
                                                                                
                                </form>
                        </div>     
                      
                <?php 
    }
    ?>
        <?php 
}

?>
        </section>
</div>
