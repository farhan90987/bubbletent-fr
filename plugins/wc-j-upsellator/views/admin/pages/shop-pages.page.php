<?php

if (!defined('ABSPATH') || !is_admin()) {	

    exit;	
	
}

use WcJUpsellator\Utility\Notice;
use WcJUpsellator\Options\OptionShopPages;

$shop_page                 = new OptionShopPages();

if( isset( $_POST['edit_wc_timeline_shop'] ) &&  wp_verify_nonce( $_POST['edit_wc_timeline_shop'], 'edit_wc_timeline_shop' )  )
{    
       
        $shop_page->loop_labels                 = isset( $_POST['loop_labels'] ) ? 1 : 0 ;
        $shop_page->single_product              = isset( $_POST['single_product'] ) ? 1 : 0 ;
        $shop_page->single_product_text         = isset( $_POST['single_product_text'] ) ? 1 : 0 ;
        $shop_page->style                       = sanitize_text_field( $_POST['style'] );
        $shop_page->setSinglePageTextHook( sanitize_text_field( $_POST['single_text_hook'] ) );
        $shop_page->setLoopLabelHook( sanitize_text_field( $_POST['loop_label_hook'] ) );
        $shop_page->save();

        $notice = new Notice();
        $notice->setText( __( 'Settings updated', 'woo_j_cart' ) );
        $notice->success();
        
        $notice->show();
}

?>

<div class="woo-upsellator-admin-page">

        <?php woo_j_render_admin_view('/partials/plugin_description', [ 'current_page' => sanitize_text_field( $_GET['page'] ) ])  ?>  

        <?php woo_j_render_admin_view('/partials/nav_bar', [ 'current_page' => sanitize_text_field( $_GET['page'] ) ])  ?>

        <form method="post">

                <div class="woo-upsellator-admin-content">

                        <?php wp_nonce_field( 'edit_wc_timeline_shop', 'edit_wc_timeline_shop' ); ?>

                        <p class="info">
                                        <?php _e( 'Sometimes you may want to show directly in the shop pages of your store if a product triggers a free gift. 
                                                 Activating this option, a label will be displayed on top of the product.<br><br>
                                                 <b>Every gift has its own text: you can change the text directly in the gift page.</b>', 'woo_j_cart' ) ?>
                        </p>     

                        <p class="info">
                                        <?php _e( '<b>Every website has its own style and it\'s impossible to make something perfect for every theme. You proably need to custom edit the CSS in order 
                                                   to make these badges fit your design.</b>', 'woo_j_cart' ) ?>
                        </p>                   
                                        
                        <div class="row-wrapper">
                                <div class="row">
                                        <div class="heading"><?php _e( 'Loop labels', 'woo_j_cart' ) ?></div>
                                        <div class="option small">
                                                <label class="wc-timeline-switch">
                                                        <input name='loop_labels' <?php echo ( woo_j_shop('loop_labels') === 1 ) ? 'checked' : '' ?> 
                                                                data-text="<?php _e( 'A new option named <b>shop label</b> has been activated for gifts. Edit that to decide what kind of text
                                                                                    display when a product can trigger a gift.<br><br>Example: <b>Buy this to get a free shirt!</b>', 'woo_j_cart' ) ?>"
                                                                type="checkbox" class="has-text has-popup">

                                                        <span class="slider"></span>
                                                        <span class="text-content" data-yes="<?php _e( 'active', 'woo_j_cart' ) ?>" data-no="<?php _e( 'inactive', 'woo_j_cart' ) ?>"></span>
                                                </label>                                
                                        </div>
                                        <div class="option">
                                                <select name="loop_label_hook" style="width: 100%;">
                                                                <?php foreach( $shop_page->getLoopLabelsHooks() as $hook => $text ): ?>
                                                                       <option <?php echo ( woo_j_shop('loop_label_hook') == $hook ) ? 'selected' : '' ?> value="<?php echo esc_attr($hook) ?>"><?php echo $text ?></option>                                                        
                                                                <?php endforeach; ?>
                                                </select>                                                    
                                        </div>
                                        <div class="tips">
                                                <?php _e( 'Display labels in the standard shop pages of WooCommerce', 'woo_j_cart' ) ?>
                                        </div>
                                </div> 

                                <div class="row">
                                        <div class="heading"><?php _e( 'Single product labels', 'woo_j_cart' ) ?></div>
                                        <div class="option small">
                                                <label class="wc-timeline-switch">
                                                        <input name='single_product' <?php echo ( woo_j_shop('single_product') === 1 ) ? 'checked' : '' ?> 
                                                                data-text="<?php _e( 'A new option named <b>shop label</b> has been activated for gifts. Edit that to decide what kind of text
                                                                                    display when a product can trigger a gift.<br><br>Example: <b>Buy this to get a free shirt!</b>', 'woo_j_cart' ) ?>"
                                                                type="checkbox" class="has-text has-popup">

                                                        <span class="slider"></span>
                                                        <span class="text-content" data-yes="<?php _e( 'active', 'woo_j_cart' ) ?>" data-no="<?php _e( 'inactive', 'woo_j_cart' ) ?>"></span>
                                                </label>                                
                                        </div>
                                        <div class="tips">
                                                <?php _e( 'Display labels in the single product page', 'woo_j_cart' ) ?>
                                        </div>
                                </div>

                                <div class="row">
                                        <div class="heading"><?php _e( 'Single product text', 'woo_j_cart' ) ?></div>
                                        <div class="option small">
                                                <label class="wc-timeline-switch">
                                                        <input name='single_product_text' <?php echo ( woo_j_shop('single_product_text') === 1 ) ? 'checked' : '' ?> 
                                                                data-text="<?php _e( 'A new option named <b>single product text</b> has been activated for gifts. Edit that to display a custom text
                                                                                    in the single product page, before the add to cart form.<br><br>Example: <b>Buy this to get a free shirt!</b>', 'woo_j_cart' ) ?>"
                                                                type="checkbox" class="has-text  has-popup">
                                                        <span class="slider"></span>
                                                        <span class="text-content" data-yes="<?php _e( 'active', 'woo_j_cart' ) ?>" data-no="<?php _e( 'inactive', 'woo_j_cart' ) ?>"></span>
                                                </label>                                
                                        </div>
                                        <div class="option">
                                                <select name="single_text_hook" style="width: 100%;">
                                                                <?php foreach( $shop_page->getSingleTextHooks() as $hook => $text ): ?>
                                                                       <option <?php echo ( woo_j_shop('single_product_text_hook') == $hook ) ? 'selected' : '' ?> value="<?php echo esc_attr( $hook ) ?>"><?php echo $text ?></option>                                                        
                                                                <?php endforeach; ?>
                                                </select>                                                    
                                        </div>
                                        <div class="tips">
                                                <?php _e( 'Gift have a field called single product text. Activating this you can display that text in the single product page, before the add to cart form.', 'woo_j_cart' ) ?>
                                        </div>
                                </div>

                                <div class="row is-beta">
                                        <div class="heading"><?php _e( 'Label style', 'woo_j_cart' ) ?></div>
                                        <div class="option">
                                                <select name="style" style="width: 100%;">
                                                                <option <?php echo ( woo_j_shop('style') == 'rotated' ) ? 'selected' : ''  ?> value="rotated">rotated</option>
                                                                <option <?php echo ( woo_j_shop('style') == 'inline' ) ? 'selected' : ''  ?> value="inline">inline</option>
                                                </select>                              
                                        </div>
                                        <div class="tips">
                                                <?php _e( 'The style of labels', 'woo_j_cart' ) ?>
                                        </div>
                                </div> 
                        </div>
                                                                                    
                </div>

                <button type="submit" class="wc-timeline-button-standard"><?php _e( 'Save', 'woo_j_cart' ) ?></button>

        </form>        

</div>


