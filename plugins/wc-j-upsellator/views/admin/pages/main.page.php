<?php

if ( !defined( 'ABSPATH' ) || !is_admin() ) {
    exit;
}
use  WcJUpsellator\Options\OptionCheckout ;
use  WcJUpsellator\Options\OptionCartPage ;
use  WcJUpsellator\Utility\Notice ;
use  WcJUpsellator\Options\OptionConfig ;
wp_enqueue_media();
$config = new OptionConfig();
$cartpage = new OptionCartPage();
$cpHooks = $cartpage->getCartpageHooks();
$pages = $config->getValidPages();
$icons = $config->getIcons();

if ( isset( $_POST['edit_wc_timeline_config'] ) && wp_verify_nonce( $_POST['edit_wc_timeline_config'], 'edit_wc_timeline_config' ) ) {
    $config->preSet();
    $config->label_on_sale = sanitize_text_field( $_POST['label_on_sale'] );
    $config->label_gift = sanitize_text_field( $_POST['label_gift'] );
    $config->label_upsell = sanitize_text_field( $_POST['label_upsell'] );
    $config->text_free_product = wp_kses( wp_unslash( $_POST['text_free_product'] ), woo_j_string_filter() );
    $config->text_add_to_cart = sanitize_text_field( $_POST['text_add_to_cart'] );
    $config->text_go_to_product = sanitize_text_field( $_POST['text_go_to_product'] );
    $config->text_empty_text = wp_kses( wp_unslash( $_POST['text_empty_text'] ), woo_j_string_filter() );
    $config->text_empty_heading = wp_kses( wp_unslash( $_POST['text_empty_heading'] ), woo_j_string_filter() );
    $config->text_empty_button = wp_kses( wp_unslash( $_POST['text_empty_button'] ), woo_j_string_filter() );
    $config->text_header = wp_kses( wp_unslash( $_POST['text_header'] ), woo_j_string_filter() );
    $config->currency_position = ( isset( $_POST['currency_position'] ) ? 1 : 0 );
    $config->shipping_total = ( isset( $_POST['shipping_total'] ) ? 1 : 0 );
    $config->empty_cart_icon = sanitize_text_field( $_POST['empty_cart_icon'] );
    $config->text_checkout = wp_kses( wp_unslash( $_POST['text_checkout'] ), woo_j_string_filter() );
    $config->text_cart_button = wp_kses( wp_unslash( $_POST['text_cart_button'] ), woo_j_string_filter() );
    $config->clear_fragments = ( isset( $_POST['clear_fragments'] ) ? 1 : 0 );
    $config->cart_button = ( isset( $_POST['cart_button'] ) ? 1 : 0 );
    $config->footer_items_count = ( isset( $_POST['footer_items_count'] ) ? 1 : 0 );
    $config->subtotal_vat_excluded = ( isset( $_POST['subtotal_vat_excluded'] ) ? 1 : 0 );
    $config->only_background = ( isset( $_POST['only_background'] ) ? 1 : 0 );
    $config->force_recalculate_totals = ( isset( $_POST['force_recalculate_totals'] ) ? 1 : 0 );
    $config->prevent_upsell_discount = ( isset( $_POST['prevent_upsell_discount'] ) ? 1 : 0 );
    $config->open_on_add = ( isset( $_POST['open_on_add'] ) ? 1 : 0 );
    $config->modal_upsell_type = (int) $_POST['modal_upsell_type'];
    $config->modal_upsell_max_displayed = (int) $_POST['modal_upsell_max_displayed'];
    $config->upsells_random_order = ( isset( $_POST['upsells_random_order'] ) ? 1 : 0 );
    $config->upsells_label_no_discount = ( isset( $_POST['upsells_label_no_discount'] ) ? 1 : 0 );
    $config->single_page_ajax = ( isset( $_POST['single_page_ajax'] ) ? 1 : 0 );
    $config->page_scroll = ( isset( $_POST['page_scroll'] ) ? 1 : 0 );
    $config->modal_theme = sanitize_text_field( $_POST['modal_theme'] );
    $config->logo_attachment_id = ( isset( $_POST['wj_logo_id'] ) ? (int) $_POST['wj_logo_id'] : false );
    $config->checkout_upsell_position = 'woocommerce_review_order_before_cart_contents';
    $config->upsell_discount_subtotal = ( isset( $_POST['upsell_discount_subtotal'] ) ? 1 : 0 );
    $config->modal_cart_notices = ( isset( $_POST['modal_cart_notices'] ) ? 1 : 0 );
    $config->coupon_code = ( isset( $_POST['coupon_code'] ) ? 1 : 0 );
    $config->modal_cart_notices_timeout = (int) $_POST['modal_cart_notices_timeout'];
    /*
    /* Cart page Upsell Values
    */
    $cartpage->display_on_cartpage = ( isset( $_POST['display_on_cartpage'] ) ? 1 : 0 );
    $cartpage->cartpage_upsell_type = (int) $_POST['cartpage_upsell_type'];
    $cartpage->cartpage_upsell_max_displayed = (int) $_POST['cartpage_upsell_max_displayed'];
    $cartpage->setCartpageUpsellHook( sanitize_text_field( $_POST['cartpage_upsell_position'] ) );
    $cartpage->save();
    $config->shop_labels = ( isset( $_POST['shop_labels'] ) ? 1 : 0 );
    $config->position = sanitize_text_field( $_POST['position'] );
    $config->shop_url = ( isset( $_POST['shop_url'] ) ? (int) $_POST['shop_url'] : wc_get_page_id( 'shop' ) );
    $config->save();
    $notice = new Notice();
    $notice->setText( __( 'Settings updated', 'woo_j_cart' ) );
    $notice->success();
    $notice->show();
}

$logo = woo_j_env( 'img_path' ) . "no-logo.jpg";

if ( woo_j_conf( 'logo_attachment_id' ) ) {
    $images = wp_get_attachment_image_src( woo_j_conf( 'logo_attachment_id' ), 'medium' );
    $logo = $images[0] ?? '';
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

         <form method="post">

                <div class="woo-upsellator-admin-content">

                        <?php 
wp_nonce_field( 'edit_wc_timeline_config', 'edit_wc_timeline_config' );
?>
                        <!-- Base settings -->
                        <div class="wp-timeline-admin-box">

                                <div class="row main">
                                        <div class="heading"><?php 
_e( 'Base settings', 'woo_j_cart' );
?></div>
                                        <div class="option"><?php 
_e( 'J Cart Upsell base settings', 'woo_j_cart' );
?></div>
                                </div>

                                <p class="info">
                                        <?php 
_e( '<b>Modal cart trigger</b><br><br>If you want to trigger ( open/close ) the modal cart trough an element of your theme, just add to that element this class<br><b>wc-j-upsellator-show-cart</b>', 'woo_j_cart' );
?></p>
                                
                                <div class="row-wrapper">

                                        <div class="row">
                                                <div class="heading"><?php 
_e( 'Only background cart', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                <label class="wc-timeline-switch">
                                                                <input name='only_background' <?php 
echo  ( woo_j_conf( 'only_background' ) === 1 ? 'checked' : '' ) ;
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
_e( 'If you want to keep your theme modal cart, activate this option to use J Cart Upsell and cross-sell only for background logic', 'woo_j_cart' );
?> 
                                                </div>
                                        </div>

                                        <div class="row needs-modal <?php 
echo  ( woo_j_conf( 'only_background' ) ? 'transparent' : '' ) ;
?>">
                                                <div class="heading"><?php 
_e( 'Open cart position', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                        <select name="position" style="width: 100%;">
                                                                <option <?php 
echo  ( woo_j_conf( 'position' ) == 'right' ? 'selected' : '' ) ;
?> value="right"><?php 
_e( 'Right', 'woo_j_cart' );
?></option>
                                                                <option <?php 
echo  ( woo_j_conf( 'position' ) == 'left' ? 'selected' : '' ) ;
?> value="left"><?php 
_e( 'Left', 'woo_j_cart' );
?></option>
                                                                <option <?php 
echo  ( woo_j_conf( 'position' ) == 'hidden' ? 'selected' : '' ) ;
?> value="hidden"><?php 
_e( 'Hidden', 'woo_j_cart' );
?></option>
                                                        </select>  
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'The position of the modal cart button trigger: left, right or hidden', 'woo_j_cart' );
?> 
                                                </div>
                                        </div>

                                        <div class="row needs-modal <?php 
echo  ( woo_j_conf( 'only_background' ) ? 'transparent' : '' ) ;
?>">
                                                <div class="heading"><?php 
_e( 'Coupon code', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                <label class="wc-timeline-switch">
                                                                <input name='coupon_code' <?php 
echo  ( woo_j_conf( 'coupon_code' ) === 1 ? 'checked' : '' ) ;
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
_e( 'Display coupon code input in the modal cart', 'woo_j_cart' );
?> 
                                                </div>
                                        </div> 

                                        <div class="row needs-modal <?php 
echo  ( woo_j_conf( 'only_background' ) ? 'transparent' : '' ) ;
?>">
                                                <div class="heading"><?php 
_e( 'Open modal on product add', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                <label class="wc-timeline-switch">
                                                                <input name='open_on_add' <?php 
echo  ( woo_j_conf( 'open_on_add' ) === 1 ? 'checked' : '' ) ;
?> type="checkbox" class="has-text">
                                                                <span class="slider"></span>
                                                                <span class="text-content" data-yes="<?php 
_e( 'active', 'woo_j_cart' );
?>" data-no="<?php 
_e( 'inactive', 'woo_j_cart' );
?>"></span>
                                                        </label>
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'Automatically open the modal cart when the customer adds a product', 'woo_j_cart' );
?> 
                                                </div>
                                        </div>

                                        <div class="row needs-modal <?php 
echo  ( woo_j_conf( 'only_background' ) ? 'transparent' : '' ) ;
?>">
                                                <div class="heading"><?php 
_e( 'Single product AJAX', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                <label class="wc-timeline-switch">
                                                                <input name='single_page_ajax' <?php 
echo  ( woo_j_conf( 'single_page_ajax' ) === 1 ? 'checked' : '' ) ;
?> type="checkbox" class="has-text">
                                                                <span class="slider"></span>
                                                                <span class="text-content" data-yes="<?php 
_e( 'active', 'woo_j_cart' );
?>" data-no="<?php 
_e( 'inactive', 'woo_j_cart' );
?>"></span>
                                                        </label>
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'Use AJAX in the single page product', 'woo_j_cart' );
?> 
                                                </div>
                                        </div>
                                        
                                        <div class="row needs-modal <?php 
echo  ( woo_j_conf( 'only_background' ) ? 'transparent' : '' ) ;
?>">
                                                <div class="heading"><?php 
_e( 'Block page scroll', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                <label class="wc-timeline-switch">
                                                                <input name='page_scroll' <?php 
echo  ( woo_j_conf( 'page_scroll' ) === 1 ? 'checked' : '' ) ;
?> type="checkbox" class="has-text">
                                                                <span class="slider"></span>
                                                                <span class="text-content" data-yes="<?php 
_e( 'active', 'woo_j_cart' );
?>" data-no="<?php 
_e( 'inactive', 'woo_j_cart' );
?>"></span>
                                                        </label>
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'Disable or enable page scroll when modal cart is opened', 'woo_j_cart' );
?> 
                                                </div>
                                        </div>                                  

                                        <div class="row  <?php 
echo  ( !wju_fs()->can_use_premium_code__premium_only() ? 'needs-pro' : '' ) ;
?>">
                                                <div class="heading"><?php 
_e( 'Recap order table', 'woo_j_cart' );
?></div>
                                                <div class="option">

                                                        <?php 
?> 

                                                                <label class="wc-timeline-switch">
                                                                        <input type="checkbox" class="has-text upsellator-checkbox off">
                                                                        <span class="slider"></span>
                                                                        <span class="text-content" data-no="<?php 
_e( 'inactive', 'woo_j_cart' );
?>"></span>
                                                                </label> 

                                                        <?php 
?>                           
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'Display in the WooCommerce order table a column about your upsells', 'woo_j_cart' );
?>
                                                </div>
                                        </div>

                                        <div class="row">                                        
                                                <div class="heading"><?php 
_e( 'Currency position', 'woo_j_cart' );
?></div>
                                                <div class="option">                                                    
                                                                
                                                        <label class="wc-timeline-switch">
                                                                <input name='currency_position' <?php 
echo  ( woo_j_conf( 'currency_position' ) === 1 ? 'checked' : '' ) ;
?> type="checkbox" class="has-text upsellator-checkbox">
                                                                <span class="slider"></span>
                                                                <span class="text-content" data-yes="<?php 
_e( 'before price', 'woo_j_cart' );
?>" data-no="<?php 
_e( 'after price', 'woo_j_cart' );
?>"></span>
                                                        </label> 
                               
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'The position of the currency: before or after price', 'woo_j_cart' );
?> 
                                                </div>  
                                        </div>
                                        
                                        <div class="row is-beta">                                        
                                                <div class="heading"><?php 
_e( 'Shipping total', 'woo_j_cart' );
?></div>
                                                <div class="option">                                                    
                                                                
                                                        <label class="wc-timeline-switch">
                                                                <input name='shipping_total' <?php 
echo  ( woo_j_conf( 'shipping_total' ) === 1 ? 'checked' : '' ) ;
?> type="checkbox" class="has-text upsellator-checkbox">
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
_e( 'Display the shipping total in the modal cart', 'woo_j_cart' );
?> 
                                                </div>  
                                        </div> 

                                        <div class="row needs-modal <?php 
echo  ( woo_j_conf( 'only_background' ) ? 'transparent' : '' ) ;
?>">
                                                <div class="heading">Theme</div>
                                                <div class="option">
                                                        <select name="modal_theme" id="modal_theme" class="modal_icon_select">
                                                                <?php 
foreach ( $config->getStyles() as $style ) {
    ?>
                                                                        <option <?php 
    echo  ( $style == woo_j_conf( 'modal_theme' ) ? 'selected' : '' ) ;
    ?> value="<?php 
    echo  esc_attr( $style ) ;
    ?>">
                                                                                <?php 
    echo  esc_html( $style ) ;
    ?>
                                                                        </option>    
                                                                <?php 
}
?>            
                                                        </select>                                                        
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'Default: standard theme.<br>Logo: displays your website logo in the header of the modal cart', 'woo_j_cart' );
?>
                                                </div>
                                        </div>

                                        <div class="row needs-modal <?php 
echo  ( woo_j_conf( 'only_background' ) ? 'transparent' : '' ) ;
?> theme-option theme-logo-option" style="<?php 
echo  ( woo_j_conf( 'modal_theme' ) == 'logo' ? '' : 'display:none;' ) ;
?>">
                                                <div class="heading">Modal logo</div>
                                                <div class="option small" style="width:auto;">
                                                        <input type='hidden' name='wj_logo_id' id='wj_logo_id' value='<?php 
echo  woo_j_conf( 'logo_attachment_id' ) ?? false ;
?>'>                  
                                                        <div class='image-preview-wrapper'>
                                                                <img id='wj-logo-preview' src='<?php 
echo  $logo ;
?>' height='60' alt="no logo">
                                                        </div>                             
                                                </div>                                                
                                                <div class="tips">
                                                        <?php 
_e( 'Logo displayed on the top right of the modal cart.<br>Reccomendend size: 300px x 60px', 'woo_j_cart' );
?>
                                                </div>

                                                <div class="product-actions balanced">
                                                        <div id="upload-upsellator-logo" class="row-submit blue" style="margin-top:0px;">  
                                                                <?php 
_e( 'Change logo', 'woo_j_cart' );
?>
                                                        </div>
                                                </div>
                                        </div>

                                        <div class="row needs-modal <?php 
echo  ( woo_j_conf( 'only_background' ) ? 'transparent' : '' ) ;
?> theme-option theme-default-option" style="<?php 
echo  ( woo_j_conf( 'modal_theme' ) == 'default' ? '' : 'display:none;' ) ;
?>">
                                                <div class="heading"><?php 
_e( 'Header text', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                        <input type="text" name='text_header' value="<?php 
echo  esc_html( woo_j_conf( 'text_header' ) ) ;
?>">                                   
                                                </div>                                               
                                                <div class="tips">
                                                        <?php 
_e( 'Text displayed on the modal header, top right', 'woo_j_cart' );
?>
                                                </div>
                                        </div>

                                        
                       
                                </div>
                        </div>
                       
                        <!-- Text -->
                        <div class="wp-timeline-admin-box needs-modal <?php 
echo  ( woo_j_conf( 'only_background' ) ? 'transparent' : '' ) ;
?>">

                                <div class="row main">
                                        <div class="heading"><?php 
_e( 'Modal cart with items', 'woo_j_cart' );
?></div>
                                        <div class="option"><?php 
_e( 'Modal cart text when not empty', 'woo_j_cart' );
?></div>
                                </div>
                                <div class="row-wrapper">
                                        <div class="row">
                                                <div class="heading"><?php 
_e( 'Checkout order', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                <input type="text" name='text_checkout' value="<?php 
echo  esc_html( woo_j_conf( 'text_checkout' ) ) ;
?>">                                   
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'Modal cart checkout button text', 'woo_j_cart' );
?> 
                                                </div>
                                        </div>

                                        <div class="row">
                                                <div class="heading"><?php 
_e( 'Go to cart button', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                <label class="wc-timeline-switch">
                                                                <input name='cart_button' <?php 
echo  ( woo_j_conf( 'cart_button' ) === 1 ? 'checked' : '' ) ;
?> type="checkbox" class="has-text">
                                                                <span class="slider"></span>
                                                                <span class="text-content" data-yes="<?php 
_e( 'active', 'woo_j_cart' );
?>" data-no="<?php 
_e( 'no', 'woo_j_cart' );
?>"></span>
                                                        </label>
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'Display or not the go to cart button', 'woo_j_cart' );
?> 
                                                </div>
                                        </div> 

                                        <div class="row">
                                                <div class="heading"><?php 
_e( 'Go to cart text', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                <input type="text" name='text_cart_button' value="<?php 
echo  esc_html( woo_j_conf( 'text_cart_button' ) ) ;
?>">                                   
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'Go to cart text', 'woo_j_cart' );
?> 
                                                </div>
                                        </div>

                                        <div class="row">
                                                <div class="heading"><?php 
_e( 'Add to cart', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                <input type="text" name='text_add_to_cart' value="<?php 
echo  esc_html( woo_j_conf( 'text_add_to_cart' ) ) ;
?>">                                   
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'Upsell add to cart button text', 'woo_j_cart' );
?> 
                                                </div>
                                        </div> 
                                        
                                        <div class="row">
                                                <div class="heading"><?php 
_e( 'Go to product', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                <input type="text" name='text_go_to_product' value="<?php 
echo  esc_html( woo_j_conf( 'text_go_to_product' ) ) ;
?>">                                   
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'Upsell go to product button text', 'woo_j_cart' );
?> 
                                                </div>
                                        </div> 

                                        <div class="row">
                                                <div class="heading"><?php 
_e( 'Free product', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                <input type="text" name='text_free_product' value="<?php 
echo  esc_html( woo_j_conf( 'text_free_product' ) ) ;
?>">                                   
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'Free item text (instead of 0,0)', 'woo_j_cart' );
?> 
                                                </div>
                                        </div>

                                        <div class="row">
                                                <div class="heading"><?php 
_e( 'Cart items count', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                <label class="wc-timeline-switch">
                                                                <input name='footer_items_count' <?php 
echo  ( woo_j_conf( 'footer_items_count' ) === 1 ? 'checked' : '' ) ;
?> type="checkbox" class="has-text">
                                                                <span class="slider"></span>
                                                                <span class="text-content" data-yes="<?php 
_e( 'active', 'woo_j_cart' );
?>" data-no="<?php 
_e( 'no', 'woo_j_cart' );
?>"></span>
                                                        </label>
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'Display or not the go to cart button', 'woo_j_cart' );
?> 
                                                </div>
                                        </div> 
                                </div>
                        </div>
                        <!-- Text empty -->
                        <div class="wp-timeline-admin-box needs-modal <?php 
echo  ( woo_j_conf( 'only_background' ) ? 'transparent' : '' ) ;
?>">
                                <div class="row main">
                                        <div class="heading"><?php 
_e( 'Empty modal cart', 'woo_j_cart' );
?></div>
                                        <div class="option"><?php 
_e( 'Settings for modal cart when empty', 'woo_j_cart' );
?></div>
                                </div>
                                <div class="row-wrapper">
                                        <div class="row">
                                                <div class="heading"><?php 
_e( 'Heading', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                <input type="text" name='text_empty_heading' value="<?php 
echo  esc_html( woo_j_conf( 'text_empty_heading' ) ) ;
?>">                                   
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'Heading of empty modal cart', 'woo_j_cart' );
?> 
                                                </div>
                                        </div>

                                        <div class="row">
                                                <div class="heading"><?php 
_e( 'Description', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                <textarea name='text_empty_text'><?php 
echo  esc_html( woo_j_conf( 'text_empty_text' ) ) ;
?></textarea>                                 
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'Text of empty modal cart', 'woo_j_cart' );
?> 
                                                </div>
                                        </div>

                                        <div class="row">
                                                <div class="heading"><?php 
_e( 'Empty icon', 'woo_j_cart' );
?></div>
                                                <div class="option has-attribute">
                                                        <select name="empty_cart_icon" class="modal_icon_select">
                                                                <option value = ""><?php 
_e( 'No icon', 'woo_j_cart' );
?></option>
                                                                <?php 
foreach ( $icons as $icon ) {
    ?>
                                                                        <option <?php 
    echo  ( $icon == woo_j_conf( 'empty_cart_icon' ) ? 'selected' : '' ) ;
    ?> value="<?php 
    echo  esc_attr( $icon ) ;
    ?>">
                                                                                <?php 
    echo  $icon ;
    ?>
                                                                        </option>    
                                                                <?php 
}
?>            
                                                        </select>
                                                        <span class="attribute <?php 
echo  esc_attr( woo_j_conf( 'empty_cart_icon' ) ) ;
?>"></span>
                                                </div>
                                        </div>                                       

                                        <div class="row">
                                                <div class="heading"><?php 
_e( 'Go to shop button', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                <input type="text" name='text_empty_button' value="<?php 
echo  esc_html( woo_j_conf( 'text_empty_button' ) ) ;
?>">                                   
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'Text of the go to shop button', 'woo_j_cart' );
?> 
                                                </div>
                                        </div>

                                        <div class="row">
                                                <div class="heading"><?php 
_e( 'Go to shop url', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                        <select name="shop_url">
                                                                        <option value="" selected><?php 
_e( 'Choose a page', 'woo_j_cart' );
?></option>
                                                                        <?php 
foreach ( $pages as $page ) {
    ?>

                                                                               <option <?php 
    echo  ( woo_j_conf( 'shop_url' ) == $page['id'] ? 'selected' : '' ) ;
    ?> value="<?php 
    echo  esc_attr( $page['id'] ) ;
    ?>"><?php 
    echo  $page['title'] ;
    ?></option>
                                                                               

                                                                        <?php 
}
?>
                                                        </select>                                  
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'Target of the go to shop button', 'woo_j_cart' );
?> 
                                                </div>
                                        </div>
                                </div>
                        </div>
                        <!-- Upsell options -->
                        <div class="wp-timeline-admin-box">
                                <div class="row main">                                       
                                        <div class="heading">
                                                <span class="heading--text"><?php 
_e( 'Modal cart', 'woo_j_cart' );
?></span>
                                                <span class="heading--subtext">upsells</span>                                       
                                        </div>
                                        <div class="option"><?php 
_e( 'Upsells options', 'woo_j_cart' );
?></div>
                                </div>
                                <div class="row-wrapper">
                                        <div class="row  needs-modal <?php 
echo  ( woo_j_conf( 'only_background' ) ? 'transparent' : '' ) ;
?>">                                        
                                                <div class="heading"><?php 
_e( 'Label if no discount', 'woo_j_cart' );
?></div>
                                                <div class="option">                                                    
                                                                
                                                        <label class="wc-timeline-switch">
                                                                <input name='upsells_label_no_discount' <?php 
echo  ( woo_j_conf( 'upsells_label_no_discount' ) === 1 ? 'checked' : '' ) ;
?> type="checkbox" class="has-text upsellator-checkbox">
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
_e( 'Show the upsell label if set without any discount', 'woo_j_cart' );
?> 
                                                </div>  
                                        </div> 
                                        <div class="row is-new  needs-modal <?php 
echo  ( woo_j_conf( 'only_background' ) ? 'transparent' : '' ) ;
?>">                                        
                                                <div class="heading"><?php 
_e( 'Display mode', 'woo_j_cart' );
?></div>
                                                <div class="option"> 
                                                        
                                                        <select name="modal_upsell_type" class="upsell-condition" style="width: 100%;">
                                                                        <option <?php 
echo  ( woo_j_conf( 'modal_upsell_type' ) == 1 ? 'selected' : '' ) ;
?>  value="1"><?php 
_e( 'One at time', 'woo_j_cart' );
?></option>
                                                                        <option <?php 
echo  ( woo_j_conf( 'modal_upsell_type' ) == 2 ? 'selected' : '' ) ;
?> value="2"><?php 
_e( 'Carousel', 'woo_j_cart' );
?></option>
                                                                        <option <?php 
echo  ( woo_j_conf( 'modal_upsell_type' ) == 3 ? 'selected' : '' ) ;
?>  value="3"><?php 
_e( 'Stacked', 'woo_j_cart' );
?></option>                                                        
                                                        </select>  
                               
                                                </div>
                                                
                                                <div class="option mini upsell-displayed-number <?php 
echo  ( woo_j_conf( 'modal_upsell_type' ) == 3 ? '' : 'hidden' ) ;
?>">                                                         
                                                        <input min="1" name="modal_upsell_max_displayed" value="<?php 
echo  woo_j_conf( 'modal_upsell_max_displayed' ) ?? 1 ;
?>" type="number">                               
                                                </div>

                                                <div class="tips">
                                                        <?php 
_e( 'Upsells display: only 1, all with carousel, all stacked. If stacked selected, you can choose the max number of products displayed at time.', 'woo_j_cart' );
?> 
                                                </div>  
                                        </div>  

                                        <div class="row needs-modal <?php 
echo  ( woo_j_conf( 'only_background' ) ? 'transparent' : '' ) ;
?>">                                        
                                                <div class="heading"><?php 
_e( 'Random order', 'woo_j_cart' );
?></div>
                                                <div class="option">                                                    
                                                                
                                                        <label class="wc-timeline-switch">
                                                                <input name='upsells_random_order' <?php 
echo  ( woo_j_conf( 'upsells_random_order' ) === 1 ? 'checked' : '' ) ;
?> type="checkbox" class="has-text upsellator-checkbox">
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
_e( 'Upsells on random order', 'woo_j_cart' );
?> 
                                                </div>  
                                        </div> 
                                        
                                        <div class="row">                                        
                                                <div class="heading"><?php 
_e( 'Prevent from coupon discount', 'woo_j_cart' );
?></div>
                                                <div class="option">                                                    
                                                                
                                                        <label class="wc-timeline-switch">
                                                                <input name='prevent_upsell_discount' <?php 
echo  ( woo_j_conf( 'prevent_upsell_discount' ) === 1 ? 'checked' : '' ) ;
?> type="checkbox" class="has-text upsellator-checkbox">
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
_e( 'Prevent coupon from discounting upsells', 'woo_j_cart' );
?> 
                                                </div>  
                                        </div> 
                                </div>
                        </div>
                        <!-- Checkout -->
                        <div class="wp-timeline-admin-box">
                                <div class="row main">
                                        <div class="heading">
                                                <span class="heading--text">Checkout</span>
                                                <span class="heading--subtext">upsells</span>
                                        </div>
                                        <div class="option"><?php 
_e( 'Manage upsells on checkout page', 'woo_j_cart' );
?></div>
                                </div>
                                <div class="row-wrapper">
                                        <div class="row  <?php 
echo  ( !wju_fs()->can_use_premium_code__premium_only() ? 'needs-pro' : '' ) ;
?>">
                                                <div class="heading"><?php 
_e( 'Active', 'woo_j_cart' );
?></div>
                                                <div class="option">

                                                        <?php 
?> 

                                                                <label class="wc-timeline-switch">
                                                                        <input type="checkbox" class="has-text upsellator-checkbox off">
                                                                        <span class="slider"></span>
                                                                        <span class="text-content" data-no="<?php 
_e( 'inactive', 'woo_j_cart' );
?>"></span>
                                                                </label> 

                                                        <?php 
?>

                                                </div>                
                                                <div class="tips">
                                                        <?php 
_e( 'Propose your upsell in the checkout order recap', 'woo_j_cart' );
?>
                                                </div>
                                        </div>
                                        
                                        <?php 
?> 

                                        <?php 
?> 
                                </div>
                        </div>
                        <!-- Cart Page -->
                        <div class="wp-timeline-admin-box">
                                <div class="row main">
                                        <div class="heading">
                                                <span class="heading--text"><?php 
_e( 'Cart page', 'woo_j_cart' );
?></span>
                                                <span class="heading--subtext">upsells</span>                                       
                                        </div>
                                        <div class="option"><?php 
_e( 'Manage upsells on cart page page', 'woo_j_cart' );
?></div>
                                </div>
                                <div class="row-wrapper">
                                        <div class="row">
                                                <div class="heading"><?php 
_e( 'Active', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                                
                                                        <label class="wc-timeline-switch">
                                                                <input name='display_on_cartpage' <?php 
echo  ( woo_j_cartpage( 'display_on_cartpage' ) === 1 ? 'checked' : '' ) ;
?> type="checkbox" class="has-text upsellator-checkbox">
                                                                <span class="slider"></span>
                                                                <span class="text-content" data-yes="<?php 
_e( 'active', 'woo_j_cart' );
?>" data-no="<?php 
_e( 'inactive', 'woo_j_cart' );
?>"></span>
                                                        </label>                                                           

                                                </div>                
                                                <div class="tips">
                                                        <?php 
_e( 'Propose your upsell in the cart page', 'woo_j_cart' );
?>
                                                </div>
                                        </div>                                        
                                                
                                        <div class="row">                                        
                                                <div class="heading"><?php 
_e( 'Display mode', 'woo_j_cart' );
?></div>
                                                <div class="option"> 
                                                        
                                                        <select name="cartpage_upsell_type" class="upsell-condition" style="width: 100%;">
                                                                        <option <?php 
echo  ( woo_j_cartpage( 'cartpage_upsell_type' ) == 1 ? 'selected' : '' ) ;
?>  value="1"><?php 
_e( 'One at time', 'woo_j_cart' );
?></option>
                                                                        <option <?php 
echo  ( woo_j_cartpage( 'cartpage_upsell_type' ) == 2 ? 'selected' : '' ) ;
?> value="2"><?php 
_e( 'Carousel', 'woo_j_cart' );
?></option>
                                                                        <option <?php 
echo  ( woo_j_cartpage( 'cartpage_upsell_type' ) == 3 ? 'selected' : '' ) ;
?>  value="3"><?php 
_e( 'Stacked', 'woo_j_cart' );
?></option>                                                        
                                                        </select>  
                        
                                                </div>

                                                <div class="option mini upsell-displayed-number <?php 
echo  ( woo_j_cartpage( 'cartpage_upsell_type' ) == 3 ? '' : 'hidden' ) ;
?>">                                                         
                                                        <input min="1" name="cartpage_upsell_max_displayed" value="<?php 
echo  woo_j_cartpage( 'cartpage_upsell_max_displayed' ) ?? 1 ;
?>" type="number">                               
                                                </div>

                                                <div class="tips">
                                                        <?php 
_e( 'Upsells display: only 1, all with carousel, all stacked. If stacked selected, you can choose the max number of products displayed at time.', 'woo_j_cart' );
?> 
                                                </div>  
                                        </div>         
                                        
                                        <div class="row">                                        
                                                <div class="heading"><?php 
_e( 'Position', 'woo_j_cart' );
?></div>
                                                <div class="option"> 
                                                        
                                                        <select name="cartpage_upsell_position" style="width: 100%;">
                                                                <?php 
foreach ( $cpHooks as $hook => $text ) {
    ?>
                                                                        <option <?php 
    echo  ( woo_j_cartpage( 'cartpage_upsell_position' ) == $hook ? 'selected' : '' ) ;
    ?> value="<?php 
    echo  esc_attr( $hook ) ;
    ?>"><?php 
    echo  $text ;
    ?></option>                                                        
                                                                <?php 
}
?>
                                                        </select>   
                        
                                                </div>

                                                <div class="tips">
                                                        <?php 
_e( 'Position of upsells in cart page.', 'woo_j_cart' );
?> 
                                                </div>  
                                        </div>         
                                       
                                </div>
                        </div>
                        <!-- Labels text -->
                        <div class="wp-timeline-admin-box">
                                <div class="row main">
                                        <div class="heading"><?php 
_e( 'Labels', 'woo_j_cart' );
?></div>
                                        <div class="option"><?php 
_e( 'Labels of different type of products in the modal cart', 'woo_j_cart' );
?></div>
                                </div>
                                <div class="row-wrapper">
                                        <div class="row">
                                                <div class="heading"><?php 
_e( 'On sale', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                <input type="text" name='label_on_sale' value="<?php 
echo  esc_html( woo_j_conf( 'label_on_sale' ) ) ;
?>">                                   
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'Label text for products set on sale by WooCommerce', 'woo_j_cart' );
?> 
                                                </div>
                                        </div>
                         
                                        <div class="row">
                                                <div class="heading"><?php 
_e( 'Upsell', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                        <input type="text" name='label_upsell' value="<?php 
echo  esc_html( woo_j_conf( 'label_upsell' ) ) ;
?>">                                   
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'Label text for upsell items added to the cart', 'woo_j_cart' );
?> 
                                                </div>
                                        </div>
                                
                                        <div class="row">
                                                <div class="heading"><?php 
_e( 'Gift', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                        <input type="text" name='label_gift' value="<?php 
echo  esc_html( woo_j_conf( 'label_gift' ) ) ;
?>">                                   
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'Label text for gift items added to the cart', 'woo_j_cart' );
?> 
                                                </div>
                                        </div>
                                </div>
                        </div>

                        <div class="wp-timeline-admin-box">
                                <div class="row main">
                                        <div class="heading"><?php 
_e( 'Advanced', 'woo_j_cart' );
?></div>
                                        <div class="option"><?php 
_e( 'Advanced options', 'woo_j_cart' );
?></div>
                                </div>
                                <div class="row-wrapper">
                                         <div class="row is-advanced">       
                                                <div class="heading"><?php 
_e( 'Skip other fragments', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                        <label class="wc-timeline-switch">
                                                                <input name='clear_fragments' <?php 
echo  ( woo_j_conf( 'clear_fragments' ) === 1 ? 'checked' : '' ) ;
?> type="checkbox" class="has-text">
                                                                <span class="slider"></span>
                                                                <span class="text-content" data-yes="<?php 
_e( 'skip fragments', 'woo_j_cart' );
?>" data-no="<?php 
_e( 'use all', 'woo_j_cart' );
?>"></span>
                                                        </label>
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'Ignore or not cart fragments created by other plugins: activate for more performance, keep this deactivated for more compatibility', 'woo_j_cart' );
?> 
                                                </div>
                                        </div>                                              
                                
                                         <div class="row is-advanced">       
                                                <div class="heading"><?php 
_e( 'Force totals recalculations', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                        <label class="wc-timeline-switch">
                                                                <input name='force_recalculate_totals' <?php 
echo  ( woo_j_conf( 'force_recalculate_totals' ) === 1 ? 'checked' : '' ) ;
?> type="checkbox" class="has-text">
                                                                <span class="slider"></span>
                                                                <span class="text-content" data-yes="<?php 
_e( 'forced', 'woo_j_cart' );
?>" data-no="<?php 
_e( 'default', 'woo_j_cart' );
?>"></span>
                                                        </label>
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'Force cart totals recalculations if cache prevent the cart to update: this may lower performances', 'woo_j_cart' );
?> 
                                                </div>
                                        </div>                                              
                                
                                         <div class="row is-advanced needs-modal <?php 
echo  ( woo_j_conf( 'only_background' ) ? 'transparent' : '' ) ;
?>">       
                                                <div class="heading"><?php 
_e( 'Cart total without VAT', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                        <label class="wc-timeline-switch">
                                                                <input name='subtotal_vat_excluded' <?php 
echo  ( woo_j_conf( 'subtotal_vat_excluded' ) === 1 ? 'checked' : '' ) ;
?> type="checkbox" class="has-text">
                                                                <span class="slider"></span>
                                                                <span class="text-content" data-yes="<?php 
_e( 'excluded', 'woo_j_cart' );
?>" data-no="<?php 
_e( 'included', 'woo_j_cart' );
?>"></span>
                                                        </label>
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'Activate this options if you want to display cart subtotal without VAT. This option also change how upsells/gifts limit are evaluted.', 'woo_j_cart' );
?> 
                                                </div>
                                        </div> 
                              
                                         <div class="row <?php 
echo  ( !wju_fs()->can_use_premium_code__premium_only() ? 'needs-pro' : '' ) ;
?> needs-modal <?php 
echo  ( woo_j_conf( 'only_background' ) ? 'transparent' : '' ) ;
?>">       
                                                <div class="heading"><?php 
_e( 'Hide quantity buttons', 'woo_j_cart' );
?></div>
                                                <div class="option">

                                                        <?php 
?> 

                                                                <label class="wc-timeline-switch">
                                                                        <input type="checkbox" class="has-text upsellator-checkbox off">
                                                                        <span class="slider"></span>
                                                                        <span class="text-content" data-no="<?php 
_e( 'visible', 'woo_j_cart' );
?>"></span>
                                                                </label> 

                                                        <?php 
?>  
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'Hide quantity buttons on -- all -- modal cart products', 'woo_j_cart' );
?> 
                                                </div>
                                        </div>  
                                        
                                        <div class="row needs-modal <?php 
echo  ( woo_j_conf( 'only_background' ) ? 'transparent' : '' ) ;
?>">       
                                                <div class="heading"><?php 
_e( 'WC notices on modal cart', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                        <label class="wc-timeline-switch">
                                                                <input name='modal_cart_notices' <?php 
echo  ( woo_j_conf( 'modal_cart_notices' ) === 1 ? 'checked' : '' ) ;
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
                                                        <span class="attribute">sec.</span>                                                       
                                                        <input min="0" name="modal_cart_notices_timeout" value="<?php 
echo  woo_j_conf( 'modal_cart_notices_timeout' ) ?? 5 ;
?>" type="number">                               
                                                </div>
                                                <div class="tips">
                                                        <?php 
_e( 'Display WC notices directly on modal cart, without page reload.<br>Set the time in seconds after which the notices auto-disappear.', 'woo_j_cart' );
?> 
                                                </div>
                                        </div>
                                        
                                        <div class="row">       
                                                <div class="heading"><?php 
_e( 'Display upsell discount', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                        <label class="wc-timeline-switch">
                                                                <input name='upsell_discount_subtotal' <?php 
echo  ( woo_j_conf( 'upsell_discount_subtotal' ) === 1 ? 'checked' : '' ) ;
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
_e( 'Display the discount amount for each upsell product in the cart and checkout page', 'woo_j_cart' );
?> 
                                                </div>
                                        </div>
                                </div>
                        </div>                        

                </div>

                <button type="submit" class="wc-timeline-button-standard"><?php 
_e( 'Save', 'woo_j_cart' );
?></button>

        </form>

</div>

<script type='text/javascript'>
        jQuery( document ).ready( function( $ ) {
          
            let file_frame;
            let wp_media_post_id = wp.media.model.settings.post.id; 
            let set_to_post_id = $( '#wj_logo_id' ).val(); 

            $('#upload-upsellator-logo').on('click', function( event ){

                event.preventDefault();
               
                if ( file_frame ) {
                
                    file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
                    file_frame.open();
                    return;

                } 
                    
                wp.media.model.settings.post.id = set_to_post_id;
                
                file_frame = wp.media.frames.file_frame = wp.media({
                    title: 'Select a image to upload',
                    button: {
                        text: 'Use this image',
                    },
                    multiple: false 
                });
                
                file_frame.on( 'select', function() {
                    
                    attachment = file_frame.state().get('selection').first().toJSON();                    
                    $( '#wj-logo-preview' ).attr( 'src', attachment.url ).css( 'width', 'auto' );
                    $( '#wj_logo_id' ).val( attachment.id );                   
                    wp.media.model.settings.post.id = wp_media_post_id;
                });
                   
                file_frame.open();
            });

            $( 'a.add_media' ).on( 'click', function() {
                wp.media.model.settings.post.id = wp_media_post_id;
            });

        });
    </script>


