<?php

if ( !defined( 'ABSPATH' ) || !is_admin() ) {
    exit;
}
use  WcJUpsellator\Utility\Notice ;
use  WcJUpsellator\Options\OptionGift ;
$ups = new OptionGift();
if ( isset( $_POST['admin_upsellator_nonce'] ) && wp_verify_nonce( $_POST['admin_upsellator_nonce'], 'admin_upsellator_nonce' ) ) {
    
    if ( isset( $_POST['id'] ) && is_numeric( $_POST['id'] ) ) {
        $notice = new Notice();
        
        if ( $_POST['action'] == 'add' || $_POST['action'] == 'update' ) {
            $p_gift = [];
            $p_gift['id'] = (int) $_POST['id'];
            $p_gift['heading'] = wp_kses( wp_unslash( $_POST['heading'] ), woo_j_string_filter() ) ?? '';
            $p_gift['text'] = wp_kses( wp_unslash( $_POST['text'] ), woo_j_string_filter() ) ?? '';
            $p_gift['shop_label'] = ( isset( $_POST['shop_label'] ) ? sanitize_text_field( $_POST['shop_label'] ) : '' );
            $p_gift['single_product_text'] = ( isset( $_POST['single_product_text'] ) ? wp_kses( wp_unslash( $_POST['single_product_text'] ), woo_j_string_filter() ) : '' );
            $p_gift['cart_limit'] = (int) $_POST['cart_limit'] ?? 0;
            $p_gift['cart_limit_to'] = (int) $_POST['cart_limit_to'] ?? 0;
            $p_gift['exclude_virtual_products'] = ( isset( $_POST['exclude_virtual_products'] ) ? 1 : 0 );
            $p_gift['only_registered'] = ( isset( $_POST['only_registered'] ) ? 1 : 0 );
            $p_gift['gifted_individually'] = ( isset( $_POST['gifted_individually'] ) ? 1 : 0 );
            $p_gift['gifted_if_not_upsell'] = ( isset( $_POST['gifted_if_not_upsell'] ) ? 1 : 0 );
            $p_gift['product_quantity'] = (int) $_POST['product_quantity'] ?? 1;
            $p_gift['banner'] = ( isset( $_POST['banner'] ) ? 1 : 0 );
            $p_gift['banner_text'] = wp_kses( wp_unslash( $_POST['banner_text'] ), woo_j_string_filter() ) ?? '';
            
            if ( $p_gift['cart_limit'] > 0 && $p_gift['cart_limit_to'] > 0 && $p_gift['cart_limit_to'] < $p_gift['cart_limit'] ) {
                $t = $p_gift['cart_limit'];
                $p_gift['cart_limit'] = $p_gift['cart_limit_to'];
                $p_gift['cart_limit_to'] = $t;
            }
            
            $p_gift['type'] = 'cart-limit';
            $p_gift['products'] = [];
            $p_gift['categories'] = [];
            $p_gift['attributes'] = [];
            $p_gift['once_per_order'] = 1;
            $p_gift['coupon'] = "";
            $p_gift['product_quantity'] = 1;
            $p_gift['quantity'] = (int) $_POST['quantity'];
            $added = $ups->editOrAdd( $p_gift );
            
            if ( $added ) {
                $ups->save();
                $notice->setText( __( 'Gift updated', 'woo_j_cart' ) );
                $notice->success();
            } else {
                $notice->setText( __( 'Something went wrong. Does this product exists?', 'woo_j_cart' ) );
                $notice->error();
            }
        
        }
        
        $notice->success();
        $notice->show();
    }

}
?>

<div class="woo-upsellator-admin-page">
       
        <?php 
woo_j_render_admin_view( '/partials/plugin_description', [
    'current_page' => sanitize_text_field( $_GET['page'] ),
] );
?>  

        <?php 
woo_j_render_admin_view( '/partials/nav_bar', [
    'current_page' => sanitize_text_field( $_GET['page'] ),
] );
?>       

            <div class="woo-upsellator-admin-content">                    

                    <p class="info">
                        <?php 
_e( 'Gifts are products automatically added to the customer cart when met some conditions. If they remove something from cart and this condition is no longer met, the product is automatically removed from cart.<br><br>
                                        <b>Do you want to hide the gift products from your store?</b><br>Set them as password protected: you can use them as gift but your cutomers will not find them in the store. 
                                        ', 'woo_j_cart' );
?>
                    </p>

                    <button class="row-submit new-gift-button">
                        <i class="icon-list-add"></i>
                        <?php 
_e( 'Create a new gift', 'woo_j_cart' );
?>
                    </button>

                    <div class="wp-timeline-admin-box  hidden new-gift-wrapper"> 
                            
                                <div class="row main">
                                        <div class="heading"><?php 
_e( 'New gift', 'woo_j_cart' );
?></div>
                                        <div class="option"><?php 
_e( 'Create a new gift for your store', 'woo_j_cart' );
?></div>
                                </div>

                                <div class="row-wrapper">                               

                                        <form method="post">

                                                <?php 
wp_nonce_field( 'admin_upsellator_nonce', 'admin_upsellator_nonce' );
?>
                                                <input type="hidden" value="add" name="action">               

                                                <div class="row">
                                                        <div class="heading"><?php 
_e( 'Product', 'woo_j_cart' );
?></div>  
                                                        <div class="option">                            
                                                                                                
                                                                <select data-parentvisible="0" id="main-product-search" name="id" required data-security="<?php 
echo  wp_create_nonce( 'search-products' ) ;
?>" class="wc-upsellator-product-search select2-hidden-accessible" style="width: 100%;" data-placeholder="<?php 
esc_attr_e( 'Search for a product&hellip;', 'woocommerce' );
?>" data-action="woocommerce_json_search_products_and_variations">
                                                                        <option value="" selected><?php 
_e( 'Nothing selected', 'woo_j_cart' );
?></option>
                                                                </select>
                                                                
                                                        </div> 
                                                        <div class="tips">
                                                                <?php 
_e( 'Choose the product you want to gift', 'woo_j_cart' );
?>
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
                                                                <input name="quantity" class="free" type="number" value="1">                           
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
                                                                <input name="cart_limit" type="number" value="0">                           
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
                                                                <input name="cart_limit_to" type="number" value="0">                           
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
                                                                                <input name='exclude_virtual_products' type="checkbox" class="has-text">
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
                                                                                <input name='only_registered' type="checkbox" class="has-text">
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
                                                                                <input name='gifted_individually' type="checkbox" class="has-text">
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
                                                                        <input name='gifted_if_not_upsell' yype="checkbox" class="has-text">
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
                                                                                <input name='banner' type="checkbox" class="has-text">
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

                                                <div class="row banner-text hidden">
                                                        <div class="heading">
                                                                <?php 
_e( 'Banner text', 'woo_j_cart' );
?>
                                                        </div>
                                                        <div class="option">
                                                                <input name="banner_text" type="text" value="">
                                                        </div>
                                                        <div class="tips">
                                                                <?php 
_e( 'Banner text, ex: reach 25$ to get this special gift', 'woo_j_cart' );
?>
                                                        </div>
                                                </div>

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
                                                                                <option value="cart-limit"><?php 
_e( 'No conditions', 'woo_j_cart' );
?></option>   
                                                                                <option value="products-list"><?php 
_e( 'One of these products', 'woo_j_cart' );
?></option>
                                                                                <option value="category-attributes-list"><?php 
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
                                                                <input  class="upsell-admin-heading" name="heading" type="text" value="">
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
                                                                <textarea  class="upsell-admin-text" name="text"></textarea>
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
                                                                        <input name="shop_label" type="text" value="">
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
                                                                        <textarea name="single_product_text"></textarea>
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

                                                                <article id="main-preview" class='wc-timeline-product preview free-gift flex-row-between'>
                                                                        <div class='flex-row-start'>                                                                                                
                                                                                <div class='image'>	
                                                                                        <div class='qty flex-row-center'>
                                                                                                1
                                                                                        </div>	
                                                                                        <img class="preview-img" src="<?php 
echo  WC_J_UPSELLATOR_PLUGIN_URL ;
?>assets/img/sample-product.jpg">
                                                                                        
                                                                                        <div class="special discounted">
                                                                                                <?php 
echo  esc_html( woo_j_conf( 'label_gift' ) ) ;
?>
                                                                                        </div>
                                                                                
                                                                                </div>						
                                                                                                
                                                                                <div class='heading_p flex-column-start'>
                                                                                                
                                                                                                <div style="position:relative;width:100%;">
                                                                                                <div class='upsell-heading'></div>
                                                                                                <div class='wc-timeline-product-title'>
                                                                                                        Sample Product
                                                                                                </div>
                                                                                                <div class="upsell-text"></div>											
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

                                                <div class="product-actions">

                                                </div>

                                                 <button class="row-submit blue" type="submit">
                                                        <i class="icon-list-add"></i>
                                                        <?php 
_e( 'Create gift', 'woo_j_cart' );
?>
                                                </button>                               
                                               
                                        </form>
                                </div>                               
                   
                    </div>

                    <?php 
woo_j_render_admin_view( '/partials/gift_items', [
    'option'  => $ups,
    'coupons' => $coupons,
] );
?> 
                    
            </div>

</div>


