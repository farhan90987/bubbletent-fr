<section class="wp-timeline-admin-box" style="margin-top:15px;">

        <div class="row main">
                <div class="heading"><?php 
_e( 'Active Upsells', 'woo_j_cart' );
?></div>
                <div class="option"><?php 
_e( 'Your active upsells', 'woo_j_cart' );
?></div>
        </div>

        <p class="info">
                <?php 
_e( 'To remove an upsell, click <b>delete</b>.<br>To edit an existing one, click <b>edit</b>.', 'woo_j_cart' );
?>
                <br><?php 
_e( 'To change order, drag and drop by clicking on the <strong>sorting icon</strong>.', 'woo_j_cart' );
?>
        </p>
                
        <div class="upsells-container">
        <?php 
$backend_upsells = woo_j_upsell( 'products' );

if ( !empty($backend_upsells) ) {
    foreach ( $backend_upsells as $active_upsell ) {
        $product = wc_get_product( $active_upsell['id'] );
        /* 
        /* If the product has been deleted, we remove it from the upsells 
        */
        
        if ( !is_object( $product ) || $product->is_type( 'variable' ) && !$product->get_parent_id() ) {
            $option->removeUpsell( $active_upsell['id'] );
            $option->save();
            continue;
        }
        
        $reg_price = 0;
        
        if ( $product->is_purchasable() ) {
            $qty = $active_upsell['quantity'] ?? 1;
            $reg_price = $product->get_regular_price();
            if ( $active_upsell['discount_type'] != 2 && isset( $active_upsell['discount'] ) ) {
                
                if ( $active_upsell['discount_type'] == 1 ) {
                    $reg_price -= $reg_price / 100 * $active_upsell['discount'];
                } else {
                    $reg_price -= $active_upsell['discount'];
                }
            
            }
            $reg_price *= $qty;
        }
        
        ?>
                <div class="upsell-wrapper--container">
                        <div class="sorting-wrapper">&#10021</div>
                        <div class="row-wrapper product-wrapper closed upsell-wrapper" data-upsell="<?php 
        echo  $active_upsell['id'] ;
        ?>"> 
                                
                                <form style="display:none;" data-event="wooj:upsell:deleted" action="wjufc_delete_upsell" class="wjufc-ajax delete-upsell-form" data-security="<?php 
        echo  wp_create_nonce( 'wjucf-ajax' ) ;
        ?>" method="POST">
                                        <input type="hidden" value="<?php 
        echo  esc_attr( $active_upsell['id'] ) ;
        ?>" name="id"> 
                                </form>
                                
                                <form action="wjufc_update_upsell" class="wjufc-ajax" data-security="<?php 
        echo  wp_create_nonce( 'wjucf-ajax' ) ;
        ?>" method="POST">
                                        
                                        <input type="hidden" value="<?php 
        echo  esc_attr( $active_upsell['id'] ) ;
        ?>" name="id">                         

                                        <div class="row">
                                                <div class="heading">
                                                                <span class="upsell--heading"><?php 
        _e( 'Upsell', 'woo_j_cart' );
        ?></span>
                                                                <div class="sub-heading type">     
                                                                        <?php 
        if ( isset( $active_upsell['condition-reversed'] ) && $active_upsell['condition-reversed'] == 1 ) {
            ?> 
                                                                                <span class="negated-condition">!</span>
                                                                        <?php 
        }
        ?>
                                                                        <?php 
        echo  str_replace( "-", " ", $active_upsell['type'] ) ;
        ?>
                                                                </div>

                                                                <?php 
        
        if ( $active_upsell['cart_limit'] > 0 ) {
            ?>    

                                                                        <div class="sub-heading">                                                        
                                                                                <?php 
            _e( 'subtotal', 'woo-j-cart' );
            ?>                                                    
                                                                                > &nbsp;<span class="strong-heading"><?php 
            echo  $active_upsell['cart_limit'] . esc_html( woo_j_conf( 'currency' ) ) ;
            ?></span>                                     
                                                                        </div>

                                                                <?php 
        }
        
        ?>

                                                                <?php 
        if ( isset( $active_upsell['keep_in_cart'] ) && $active_upsell['keep_in_cart'] == 1 ) {
            ?>   
                                                                        <div class="sub-heading-icon">
                                                                                <i title="Keep in cart" class="wooj-icon-list-add"></i>
                                                                        </div> 
                                                                <?php 
        }
        ?> 
                                                                
                                                </div>
                                                                                        
                                                <div class="option">   
                                                        <input type="text" class="selected_product" data-id="<?php 
        echo  esc_attr( $active_upsell['id'] ) ;
        ?>" name='product_name' value="<?php 
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
        
        if ( $qty > 1 ) {
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
        }
        
        ?>
                                                <!-- /Status check -->  

                                                <?php 
        
        if ( !empty($product->get_post_password()) ) {
            ?>
                                                        <div class="alert">
                                                                <i class="icon-attention"></i>
                                                                <?php 
            _e( 'This product is password protected and can\'t be  be upselled', 'woo_j_cart' );
            ?>
                                                        </div>
                                                <?php 
        }
        
        ?>   
                                                <!-- Purchasable check -->
                                                <?php 
        
        if ( !$product->is_purchasable() ) {
            ?>
                                                        <div class="alert">
                                                                <i class="icon-attention"></i>
                                                                <?php 
            _e( 'This product not purchasable', 'woo_j_cart' );
            ?>
                                                        </div>
                                                <?php 
        }
        
        ?> 

                                                <div class="product-actions">

                                                        <div data-id = "<?php 
        echo  esc_attr( $active_upsell['id'] ) ;
        ?>" 
                                                        data-security = "<?php 
        echo  wp_create_nonce( 'wjucf-ajax' ) ;
        ?>" 
                                                        data-title = "<?php 
        _e( 'Pause/Active', 'woo_j_cart' );
        ?>"   
                                                        data-type = "upsell"                                           
                                                        class="flex-row-center woo-j-action-round <?php 
        echo  ( isset( $active_upsell['active'] ) && $active_upsell['active'] == 0 ? 'pause' : '' ) ;
        ?> switch-product-status">
                                                                <i class="<?php 
        echo  ( isset( $active_upsell['active'] ) && $active_upsell['active'] == 0 ? ' wooj-icon-play' : ' wooj-icon-pause' ) ;
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

                                                        
                                                        <button data-title = "<?php 
        _e( 'Delete Upsell', 'woo_j_cart' );
        ?>"                                                                                              
                                                                data-text = "<?php 
        _e( 'Do you want to remove the selected upsell?', 'woo_j_cart' );
        ?>"                                                
                                                                class="woo-j-action-round  red delete-upsell">
                                                                <i class="wooj-icon-trash"></i>
                                                        </button> 
                                                </div> 
                                        
                                        </div>     
                                        
                                        <div class="row">
                                                <div class="heading">
                                                        <?php 
        _e( 'Upsell quantity', 'woo_j_cart' );
        ?>
                                                </div>
                                                <div class="option has-attribute">
                                                        <span class="attribute">n.</span>
                                                        <input name="quantity" class="free upsell-qty-input" type="number" value="<?php 
        echo  intval( $active_upsell['quantity'] ?? 1 ) ;
        ?>">                           
                                                </div>
                                                <div class="tips">
                                                        <?php 
        _e( 'How many of this product should be upselled', 'woo_j_cart' );
        ?>
                                                </div>
                                        </div>

                                        <div class="row">
                                                <div class="heading">
                                                        <?php 
        _e( 'Discount type', 'woo_j_cart' );
        ?>
                                                </div>
                                                <div class="option">
                                                        <select class="upsell-discount-type" name="discount_type">
                                                                <option value="1" <?php 
        echo  ( $active_upsell['discount_type'] == 1 ? 'selected' : '' ) ;
        ?>><?php 
        _e( 'Percentage', 'woo_j_cart' );
        ?></option>
                                                                <option value="0" <?php 
        echo  ( $active_upsell['discount_type'] == 0 ? 'selected' : '' ) ;
        ?>><?php 
        _e( 'Flat amount', 'woo_j_cart' );
        ?></option>
                                                                <option value="2" <?php 
        echo  ( $active_upsell['discount_type'] == 2 ? 'selected' : '' ) ;
        ?>><?php 
        _e( 'None', 'woo_j_cart' );
        ?></option>                 
                                                        </select>
                                                </div>
                                                <div class="tips">
                                                        <?php 
        _e( 'Type of discount: flat amount,percentage or none', 'woo_j_cart' );
        ?>
                                                </div>
                                        </div>

                                        <div class="row  discount-amount-row <?php 
        echo  ( $active_upsell['discount_type'] != 2 ? '' : 'hidden' ) ;
        ?>">
                                                <div class="heading">
                                                        <?php 
        _e( 'Discount amount', 'woo_j_cart' );
        ?>
                                                </div>
                                                <div class="option has-attribute">
                                                        <span class="discount-type <?php 
        echo  ( $active_upsell['discount_type'] == 0 ? 'active' : '' ) ;
        ?> attribute currency-value"><?php 
        echo  esc_html( woo_j_conf( 'currency' ) ) ;
        ?></span>
                                                        <span class="discount-type <?php 
        echo  ( $active_upsell['discount_type'] == 1 ? 'active' : '' ) ;
        ?> attribute percentage-value">%</span>                    
                                                        <input name="discount" step="0.1" class="discount-amount-input" type="number" min="0" value="<?php 
        echo  esc_attr( $active_upsell['discount'] ) ;
        ?>">
                                                </div>
                                                <div class="tips">
                                                        <?php 
        _e( 'Amount of the discount, based on previous choice. If upsell quantity is greater than 1, this discount refers to a single unit.', 'woo_j_cart' );
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
                                                        <input name="cart_limit" type="number" value="<?php 
        echo  esc_attr( $active_upsell['cart_limit'] ) ;
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
        _e( 'Keep in cart', 'woo_j_cart' );
        ?>
                                                </div>
                                                <div class="option has-attribute">
                                                                <label class="wc-timeline-switch">
                                                                        <input name='keep_in_cart' <?php 
        echo  ( isset( $active_upsell['keep_in_cart'] ) && $active_upsell['keep_in_cart'] === 1 ? 'checked' : '' ) ;
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
        _e( 'If added to cart, keep it even if trigger conditions are not met anymore', 'woo_j_cart' );
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
        echo  ( isset( $active_upsell['only_registered'] ) && $active_upsell['only_registered'] === 1 ? 'checked' : '' ) ;
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
        _e( 'Upsell valid only for logged users', 'woo_j_cart' );
        ?>
                                                </div>
                                        </div>

                                        <div class="row">
                                                <div class="heading">
                                                        <?php 
        _e( 'Button action', 'woo_j_cart' );
        ?>
                                                </div>
                                                <div class="option">
                                                        <select name="button_action" style="width: 100%;">
                                                                        <option <?php 
        echo  ( isset( $active_upsell['button_action'] ) && $active_upsell['button_action'] == 'add-to-cart' ? 'selected' : '' ) ;
        ?> value="add-to-cart"><?php 
        _e( 'Add product to cart', 'woo_j_cart' );
        ?></option>
                                                                        <option <?php 
        echo  ( isset( $active_upsell['button_action'] ) && $active_upsell['button_action'] == 'product-page' ? 'selected' : '' ) ;
        ?> value="product-page"><?php 
        _e( 'Go to product page', 'woo_j_cart' );
        ?></option>
                                                        </select>                                                    
                                                </div>   
                                                <div class="tips">
                                                        <?php 
        _e( 'What happens when customer clicks the button: add the product to cart or go to the product page', 'woo_j_cart' );
        ?>
                                                </div>                            
                                        </div>

                                        <div class="row">
                                                <div class="heading">
                                                        <?php 
        _e( 'Allow quantity change', 'woo_j_cart' );
        ?>
                                                </div>
                                                <div class="option has-attribute">
                                                                <label class="wc-timeline-switch">
                                                                        <input name='quantity_change' <?php 
        echo  ( isset( $active_upsell['quantity_change'] ) && $active_upsell['quantity_change'] === 1 ? 'checked' : '' ) ;
        ?> type="checkbox" class="has-text">
                                                                        <span class="slider"></span>
                                                                        <span class="text-content" data-yes="<?php 
        _e( 'yes', 'woo_j_cart' );
        ?>" data-no="<?php 
        _e( 'no', 'woo_j_cart' );
        ?>"></span>
                                                                </label>                       
                                                </div>
                                                <div class="option has-attribute medium">  
                                                                        <span class="attribute">max.</span>                                                       
                                                                        <input min="0" name="quantity_change_max" value="<?php 
        echo  ( isset( $active_upsell['quantity_change_max'] ) ? (int) $active_upsell['quantity_change_max'] : 0 ) ;
        ?>" type="number">                               
                                                                </div>
                                                <div class="tips">
                                                        <?php 
        _e( 'Customer can change upsell item quantity after adding it to cart. Set the max allowed quantity, leave zero for no limit.', 'woo_j_cart' );
        ?>
                                                </div>
                                        </div>

                                        <div class="row">
                                                <div class="heading">
                                                        <?php 
        _e( 'Sold individually', 'woo_j_cart' );
        ?>
                                                </div>
                                                <div class="option has-attribute">
                                                                <label class="wc-timeline-switch">
                                                                        <input name='sold_individually' <?php 
        echo  ( isset( $active_upsell['sold_individually'] ) && $active_upsell['sold_individually'] === 1 ? 'checked' : '' ) ;
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
        _e( 'Upsell triggered only if the same product is not already in cart', 'woo_j_cart' );
        ?>
                                                </div>
                                        </div>

                                        <div class="row">
                                                <div class="heading">
                                                        <?php 
        _e( 'Hide if already gifted', 'woo_j_cart' );
        ?>
                                                </div>
                                                <div class="option has-attribute">
                                                                <label class="wc-timeline-switch">
                                                                        <input name='hide_if_gifted' <?php 
        echo  ( isset( $active_upsell['hide_if_gifted'] ) && $active_upsell['hide_if_gifted'] === 1 ? 'checked' : '' ) ;
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
        _e( 'Do not show this upsell if this product is already in cart as gift', 'woo_j_cart' );
        ?>
                                                </div>
                                        </div>

                                        <div class="row hide-if-gifted <?php 
        echo  ( $active_upsell['hide_if_gifted'] == true ? '' : 'hidden' ) ;
        ?>">
                                                <div class="heading">
                                                        <?php 
        _e( 'Check also for parent product', 'woo_j_cart' );
        ?>
                                                </div>
                                                <div class="option has-attribute">
                                                                <label class="wc-timeline-switch">
                                                                        <input name='hide_if_gifted_parent' <?php 
        echo  ( isset( $active_upsell['hide_if_gifted_parent'] ) && $active_upsell['hide_if_gifted_parent'] === 1 ? 'checked' : '' ) ;
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
        _e( 'Check also if parent product matches', 'woo_j_cart' );
        ?>
                                                </div>
                                        </div>

                                        <div class="row <?php 
        echo  ( !wju_fs()->can_use_premium_code__premium_only() ? 'needs-pro' : '' ) ;
        ?>">
                                                <div class="heading">
                                                        <?php 
        _e( 'Cart items', 'woo_j_cart' );
        ?>
                                                </div>
                                                <div class="product-by-products-list option pre-product-condition product-by-category-attributes-list <?php 
        echo  ( $active_upsell['type'] != 'cart-limit' ? '' : 'hidden' ) ;
        ?>" style="margin-right:-15px;">
                                                        <select name="condition-reversed">
                                                                        <option <?php 
        echo  ( isset( $active_upsell['condition-reversed'] ) && $active_upsell['condition-reversed'] === 0 ? 'selected' : '' ) ;
        ?> value="0"><?php 
        _e( 'Cart must have', 'woo_j_cart' );
        ?></option>
                                                                        <option <?php 
        echo  ( isset( $active_upsell['condition-reversed'] ) && $active_upsell['condition-reversed'] === 1 ? 'selected' : '' ) ;
        ?> value="1"><?php 
        _e( 'Cart must not have', 'woo_j_cart' );
        ?></option>
                                                        </select>     
                                                </div>
                                                <div class="option" style="margin-left:0px;">
                                                        <select name="condition" class="product-condition" style="width: 100%;">
                                                                        <option <?php 
        echo  ( $active_upsell['type'] == 'cart-limit' ? 'selected' : '' ) ;
        ?> value="cart-limit"><?php 
        _e( 'No conditions', 'woo_j_cart' );
        ?></option>
                                                                        <option <?php 
        echo  ( $active_upsell['type'] == 'products-list' ? 'selected' : '' ) ;
        ?> value="products-list"><?php 
        _e( 'One of this/these products', 'woo_j_cart' );
        ?></option>
                                                                        <option <?php 
        echo  ( $active_upsell['type'] == 'category-attributes-list' ? 'selected' : '' ) ;
        ?> value="category-attributes-list"><?php 
        _e( 'A product with category/attribute', 'woo_j_cart' );
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
                                                        <input name="heading"  class="upsell-admin-heading" type="text" value="<?php 
        echo  esc_html( $active_upsell['heading'] ) ;
        ?>">
                                                </div>
                                                <div class="tips">
                                                        <?php 
        _e( 'Short text to define your upsell', 'woo_j_cart' );
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
                                                        <textarea  class="upsell-admin-text" name="text"><?php 
        echo  esc_html( $active_upsell['text'] ) ;
        ?></textarea>
                                                </div>
                                                <div class="tips">
                                                        <?php 
        _e( 'Long text to define your upsell', 'woo_j_cart' );
        ?>
                                                </div>
                                        </div>

                                        <div class="row">
                                                <div class="heading darker">
                                                        <?php 
        _e( 'Preview', 'woo_j_cart' );
        ?>
                                                </div>
                                                <div class="option big">

                                                        <article class='wc-timeline-product preview flex-row-between'>
                                                                <div class='flex-row-start'>                                                                                                
                                                                        <div class='image'>	
                                                                        <?php 
        echo  $product->get_image( 'thumbnail' ) ;
        ?>
                                                                                
                                                                                <?php 
        
        if ( $product->is_on_sale() ) {
            ?>

                                                                                        <div class="discounted">
                                                                                        <?php 
            echo  esc_html( woo_j_conf( 'label_on_sale' ) ) ;
            ?>
                                                                                        </div>	
                                                                                                                                
                                                                                <?php 
        }
        
        ?>	
                                                                        
                                                                        </div>						
                                                                                        
                                                                        <div class='heading_p flex-column-start'>
                                                                                        
                                                                                        <div style="position:relative;width:100%;">
                                                                                        <div class='upsell-heading'><?php 
        echo  esc_html( $active_upsell['heading'] ) ;
        ?></div>
                                                                                        <div class='wc-timeline-product-title'>
                                                                                                <span class="wc-timeline-product-qty">
                                                                                                        <?php 
        echo  ( isset( $active_upsell['quantity'] ) && $active_upsell['quantity'] > 1 ? $active_upsell['quantity'] . '* ' : '' ) ;
        ?>
                                                                                                </span>
                                                                                                <span class="wc-timeline-product-name">
                                                                                                        <?php 
        echo  explode( '(', wp_kses_post( $product->get_formatted_name() ) )[0] ;
        ?>
                                                                                                </span>
                                                                                        </div>
                                                                                        <div class="upsell-text">  
                                                                                                <?php 
        echo  esc_html( $active_upsell['text'] ) ;
        ?>                                                                                                           
                                                                                        </div>											
                                                                                </div>                                                                                                        
                                                                        </div>
                                                                </div>
                                                                <div class='options flex-column-center'>                                                                                
                                                                        <div class="flex-row-end">
                                                                                <input type="hidden" class="preview-base-price" value="<?php 
        echo  esc_attr( $reg_price ) ;
        ?>">  

                                                                                <div class='wc-timeline-product-price striked <?php 
        echo  ( $active_upsell['discount_type'] != 2 ? '' : 'hidden' ) ;
        ?>'>
                                                                                                <span class="preview-striked-price"><?php 
        echo  $qty * $reg_price ;
        ?></span>
                                                                                                <span class="currency"><?php 
        echo  esc_html( woo_j_conf( 'currency' ) ) ;
        ?></span>								
                                                                                </div>	

                                                                                <div class='wc-timeline-product-price'>
                                                                                                <span class="preview-price"><?php 
        echo  round( $reg_price, 2 ) ;
        ?></span>
                                                                                                <span class="currency"><?php 
        echo  esc_html( woo_j_conf( 'currency' ) ) ;
        ?></span>	
                                                                                </div>                                                               
                                                                                
                                                                        </div>  
                                                                        
                                                                        <div class="wc-timeline-product-add">
                                                                                <span class="product_type_simple add_to_cart_button ajax_add_to_cart">
                                                                                        <?php 
        echo  esc_html( woo_j_conf( 'text_add_to_cart' ) ) ;
        ?>                                                                                        
                                                                                </span>
                                                                        </div> 
                                                                </div> 

                                                                                                                                
                                                        </article>                                         
                                                </div>
                                                
                                        </div>

                                        <button style="float:right;" class="row-submit blue" type="submit">
                                                <?php 
        _e( 'Update upsell', 'woo_j_cart' );
        ?>
                                        </button> 

                                </form>
                        </div>         
                </div>                                                                
                <?php 
    }
    ?>
        <?php 
}

?>

        <form data-event="wooj:upsells:reordered" action="wjufc_reorder_upsells" class="wjufc-ajax reorder-upsells-form" data-security="<?php 
echo  wp_create_nonce( 'wjucf-ajax' ) ;
?>" method="POST">
                <input type="hidden" name="order" value="ids">  
                <button type="submit" class="wc-timeline-button-standard"><?php 
_e( 'Save order', 'woo_j_cart' );
?></button>                                                                  
        </form>

        </div>
       
</section>