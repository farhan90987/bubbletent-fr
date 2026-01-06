<?php

if ( !defined( 'ABSPATH' ) || !is_admin() ) {
    exit;
}
use  WcJUpsellator\Utility\Notice ;
use  WcJUpsellator\Options\OptionUpsell ;
$ups = new OptionUpsell();
if ( isset( $_POST['admin_upsellator_nonce'] ) && wp_verify_nonce( $_POST['admin_upsellator_nonce'], 'admin_upsellator_nonce' ) ) {
    
    if ( isset( $_POST['id'] ) && is_numeric( $_POST['id'] ) ) {
        $notice = new Notice();
        
        if ( $_POST['action'] == 'add' || $_POST['action'] == 'update' ) {
            $upsell = [];
            $upsell['id'] = (int) $_POST['id'];
            $upsell['heading'] = wp_kses( wp_unslash( $_POST['heading'] ), woo_j_string_filter() ) ?? '';
            $upsell['text'] = wp_kses( wp_unslash( $_POST['text'] ), woo_j_string_filter() ) ?? '';
            $upsell['discount_type'] = (int) $_POST['discount_type'];
            $upsell['discount'] = (double) $_POST['discount'];
            $upsell['cart_limit'] = (int) $_POST['cart_limit'] ?? 0;
            $upsell['only_registered'] = ( isset( $_POST['only_registered'] ) ? 1 : 0 );
            $upsell['sold_individually'] = ( isset( $_POST['sold_individually'] ) ? 1 : 0 );
            $upsell['quantity_change'] = ( isset( $_POST['quantity_change'] ) ? 1 : 0 );
            $upsell['quantity_change_max'] = ( isset( $_POST['quantity_change_max'] ) ? (int) $_POST['quantity_change_max'] : 0 );
            $upsell['hide_if_gifted'] = ( isset( $_POST['hide_if_gifted'] ) ? 1 : 0 );
            $upsell['hide_if_gifted_parent'] = ( isset( $_POST['hide_if_gifted_parent'] ) ? 1 : 0 );
            $upsell['button_action'] = sanitize_text_field( $_POST['button_action'] );
            $upsell['keep_in_cart'] = ( isset( $_POST['keep_in_cart'] ) ? 1 : 0 );
            $upsell['type'] = 'cart-limit';
            $upsell['products'] = [];
            $upsell['attributes'] = [];
            $upsell['categories'] = [];
            $upsell['condition-reversed'] = 0;
            $upsell['condition-reversed'] = ( $upsell['type'] == 'cart-limit' ? 0 : $upsell['condition-reversed'] );
            $upsell['quantity'] = (int) $_POST['quantity'];
            $added = $ups->editOrAdd( $upsell );
            
            if ( $added ) {
                $ups->save();
                $notice->setText( __( 'Upsell updated', 'woo_j_cart' ) );
                $notice->success();
            } else {
                $notice->setText( __( 'Something went wrong. Does the added product exists? If it\'s a variable one, you should add one of its variations.', 'woo_j_cart' ) );
                $notice->error();
            }
        
        }
        
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
_e( 'Upsell products will be displayed to the customer in their modal cart.<br>
                                                Upsells are evaluated one by one: if the first one met the set requirements, it will be displayed. If not, the next one will be evaluated. 
                                                If the customer already has that product added, the upsell is skipped.<br><br>
                                                <b>Upsell items can be added only once to cart by the customer and their quantity cannot be changed</b>', 'woo_j_cart' );
?></p>

                        <button class="row-submit new-upsell-button">
                                <i class="icon-list-add"></i>
                                <?php 
_e( 'Create a new upsell', 'woo_j_cart' );
?>
                        </button>

                        <div class="wp-timeline-admin-box  hidden new-upsell-wrapper"> 

                                <div class="row main">
                                        <div class="heading"><?php 
_e( 'New Upsell', 'woo_j_cart' );
?></div>
                                        <div class="option"><?php 
_e( 'Create a new upsell for your store', 'woo_j_cart' );
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
                                                                                                
                                                                <select name="id" data-parentvisible="0" id="main-product-search" required data-security="<?php 
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
_e( 'Choose the product you want to propose', 'woo_j_cart' );
?>
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
                                                                <input name="quantity" class="free upsell-qty-input main" type="number" value="1">                           
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
                                                                <select class="main upsell-discount-type" name="discount_type">
                                                                        <option value="1"><?php 
_e( 'Percentage', 'woo_j_cart' );
?></option>
                                                                        <option value="0"><?php 
_e( 'Flat amount', 'woo_j_cart' );
?></option>
                                                                        <option value="2"><?php 
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

                                                <div class="row discount-amount-row">
                                                        <div class="heading">
                                                                <?php 
_e( 'Discount amount', 'woo_j_cart' );
?>
                                                        </div>
                                                        <div class="option has-attribute">
                                                                <span class="discount-type attribute currency-value"><?php 
echo  esc_html( woo_j_conf( 'currency' ) ) ;
?></span>
                                                                <span class="discount-type attribute active percentage-value">%</span> 
                                                                <input class="discount-amount-input main" name="discount" min="1" step="0.1" type="number" value="1">
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
                                                                <input name="cart_limit" type="number" value="1">                           
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
                                                                                <input name='keep_in_cart' type="checkbox" class="has-text">
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
_e( 'Allow quantity change', 'woo_j_cart' );
?>
                                                        </div>
                                                        <div class="option has-attribute">
                                                                        <label class="wc-timeline-switch">
                                                                                <input name='quantity_change' type="checkbox" class="has-text">
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
                                                                <input min="0" name="quantity_change_max" value="0" type="number">                               
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
                                                                                <option selected value="add-to-cart"><?php 
_e( 'Add product to cart', 'woo_j_cart' );
?></option>
                                                                                <option value="product-page"><?php 
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
_e( 'Sold individually', 'woo_j_cart' );
?>
                                                        </div>
                                                        <div class="option has-attribute">
                                                                        <label class="wc-timeline-switch">
                                                                                <input name='sold_individually' type="checkbox" class="has-text">
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
                                                                                <input name='hide_if_gifted' type="checkbox" class="has-text">
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
                                                
                                                <div class="row hide-if-gifted hidden">
                                                        <div class="heading">
                                                                <?php 
_e( 'Check also for parent product', 'woo_j_cart' );
?>
                                                        </div>
                                                        <div class="option has-attribute">
                                                                        <label class="wc-timeline-switch">
                                                                                <input name='hide_if_gifted_parent' type="checkbox" class="has-text">
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
                                                        <div class="product-by-products-list option pre-product-condition product-by-category-attributes-list hidden" style="margin-right:-15px;">
                                                                <select name="condition-reversed">
                                                                                <option value="0"><?php 
_e( 'Cart must have', 'woo_j_cart' );
?></option>
                                                                                <option value="1"><?php 
_e( 'Cart must not have', 'woo_j_cart' );
?></option>
                                                               </select>
                                                        </div>
                                                        <div class="option" style="margin-left:0px;">
                                                                <select name="condition" class="product-condition" style="width: 100%;">
                                                                                <option value="cart-limit"><?php 
_e( 'No conditions', 'woo_j_cart' );
?></option>
                                                                                <option value="products-list"><?php 
_e( 'One of these products', 'woo_j_cart' );
?></option>
                                                                                <option value="category-attributes-list"><?php 
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
                                                                <input class="upsell-admin-heading" name="heading" type="text" value="">
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
                                                                <textarea class="upsell-admin-text" name="text"></textarea>
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

                                                                <article id="main-preview" class='wc-timeline-product preview flex-row-between'>
                                                                        <div class='flex-row-start'>                                                                                                
                                                                                <div class='image'>	
                                                                                	<img class="preview-img" src="<?php 
echo  WC_J_UPSELLATOR_PLUGIN_URL ;
?>assets/img/sample-product.jpg">	
                                                                                </div>						
                                                                                                
                                                                                <div class='heading_p flex-column-start'>
                                                                                                
                                                                                         <div style="position:relative;width:100%;">
                                                                                                <div class='upsell-heading'></div>
                                                                                                <div class='wc-timeline-product-title'>
                                                                                                        <span class="wc-timeline-product-qty"></span>
                                                                                                        <span class="wc-timeline-product-name">Sample Product</span>
                                                                                                </div>
                                                                                                <div class="upsell-text">                                                                                                      
                                                                                                </div>											
                                                                                        </div>                                                                                                        
                                                                                </div>
                                                                        </div>
                                                                        <div class='options flex-column-center'>                                                                                
                                                                                <div class="flex-row-end">

                                                                                        <div class='wc-timeline-product-price striked hidden'>
                                                                                                <span class="preview-striked-price"></span>
                                                                                                <span class="currency"><?php 
echo  esc_html( woo_j_conf( 'currency' ) ) ;
?></span>								
                                                                                        </div>	

                                                                                        <div class='wc-timeline-product-price'>
                                                                                                <span class="preview-price">15</span>
                                                                                                <span class="currency"><?php 
echo  esc_html( woo_j_conf( 'currency' ) ) ;
?></span>	
                                                                                        </div>
                                                                                        <input type="hidden" class="preview-base-price" value="15">
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

                                                <div class="product-actions">
                                                
                                                </div>

                                                <button class="row-submit blue" type="submit">
                                                        <i class="icon-list-add"></i>
                                                        <?php 
_e( 'Create upsell', 'woo_j_cart' );
?>
                                                </button>                               

                                        </form>
                                </div> 
                        </div>
                        <div id="woo-j-upsells">
                                <?php 
woo_j_render_admin_view( '/partials/upsell_items', [
    'option' => $ups,
] );
?>   
                        </div>                
                  
            </div>      

</div>


