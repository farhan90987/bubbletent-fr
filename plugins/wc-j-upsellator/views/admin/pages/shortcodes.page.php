<?php

if ( !defined( 'ABSPATH' ) || !is_admin() ) {
    exit;
}
use  WcJUpsellator\Options\OptionConfig ;
use  WcJUpsellator\Utility\Notice ;
$config = new OptionConfig();
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
                <?php 
wp_nonce_field( 'edit_wc_timeline_config', 'edit_wc_timeline_config' );
?>
                <div class="woo-upsellator-admin-content">             

                <!-- Cart count  -->
                <section class="wp-timeline-admin-box">
                     
                        <div class="row main">
                                <div class="heading">Cart Count</div>
                                <div class="option"><?php 
_e( 'Counter shortcode', 'woo_j_cart' );
?></div>
                        </div>

                        <div class="row-wrapper">
                        <p class="info">
                                        <?php 
_e( 'Icon type and color are based on what you have selected on <b>style</b> tab', 'woo_j_cart' );
?></p> 
                                        <div class="row">
                                                <div class="heading"><?php 
_e( 'Default', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                        <?php 
echo  do_shortcode( '[woo_j_cart_count demo="true"] ' ) ;
?>
                                                </div>
                                                <div class="option">
                                                        [woo_j_cart_count]                                            
                                                </div>
                                        </div>
                                        
                                        <div class="row">
                                                <div class="heading"><?php 
_e( 'With price', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                        <?php 
echo  do_shortcode( '[woo_j_cart_count demo="true" price="true"] ' ) ;
?>
                                                </div>
                                                <div class="option">
                                                        [woo_j_cart_count price="true"]                                            
                                                </div>
                                        </div>

                                        <div class="row">
                                                <div class="heading"><?php 
_e( 'Without icon', 'woo_j_cart' );
?></div>
                                                <div class="option">
                                                        <?php 
echo  do_shortcode( '[woo_j_cart_count noicon="true" demo="true" price="true"] ' ) ;
?>
                                                </div>
                                                <div class="option">                                                  
                                                        [woo_j_cart_count noicon="true" price="true"]                                            
                                                </div>
                                        </div>
                                     
                        </div>                
                </section>
                <!-- Shipping Bar -->
                <section class="wp-timeline-admin-box">
                     
                        <div class="row main">
                                <div class="heading"><?php 
esc_html_e( 'Dynamic bar', 'woo_j_cart' );
?></div>
                                <div class="option"> <?php 
esc_html_e( 'Dynamic bar', 'woo_j_cart' );
?> Shortcode</div>
                        </div>

                        <div class="row-wrapper">
                                <p class="info">
                                        <?php 
_e( 'Display the dynamic bar via shortcode</b>', 'woo_j_cart' );
?>
                                </p> 
                                <div class="row">
                                 <div class="heading"><?php 
esc_html_e( 'Dynamic bar', 'woo_j_cart' );
?></div>
                                        <div class="option medium">
                                        <label class="wc-timeline-switch">
                                                                <input name='dynamic_bar_shortcode' <?php 
echo  ( woo_j_conf( 'dynamic_bar_shortcode' ) === 1 ? 'checked' : '' ) ;
?> type="checkbox" class="has-text upsellator-checkbox">
                                                                <span class="slider"></span>
                                                                <span class="text-content" data-yes="<?php 
_e( 'active', 'woo_j_cart' );
?>" data-no="<?php 
_e( 'inactive', 'woo_j_cart' );
?>"></span>
                                                        </label>  
                                        </div>
                                        <div class="option">
                                                <div class="shipping-bar-preview-container <?php 
echo  esc_attr( woo_j_shipping( 'shipping_bar_type' ) ) ;
?> flex-column-center">
                                                                                        <div class="shipping-bar-preview">			
                                                                                                <div class="shipping-progress-bar"></div>
                                                                                                <div class="<?php 
echo  esc_attr( 'wooj-icon-truck' ) ;
?> flex-row-center shipping-icon"></div>
                                                                                        </div>                                                                               		
                                                                                </div>
                                                </div>
                                        <div class="option">                                                  
                                                [wjcfw_dynamic_bar]                                           
                                        </div>
                                </div>                                        
                                     
                        </div>                
                </section>
                <!-- Upsells -->
                <section class="wp-timeline-admin-box">
                     
                        <div class="row main">
                                <div class="heading">Upsells</div>
                                <div class="option"><?php 
_e( 'Upsells shortcode', 'woo_j_cart' );
?></div>
                        </div>

                        <div class="row-wrapper">
                                <p class="info">
                                        <?php 
_e( 'Display upsells via shortcode. Settings are taken from <b>cart page upsells</b>', 'woo_j_cart' );
?>
                                </p> 
                                <div class="row <?php 
echo  ( !wju_fs()->can_use_premium_code__premium_only() ? 'needs-pro' : '' ) ;
?>">
                                        <div class="heading"><?php 
_e( 'Upsells Shortcode', 'woo_j_cart' );
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
                                        <div class="option">
                                                [wjcfw_upsells]                                            
                                        </div>
                                </div>                                         
                                     
                        </div>                
                </section>

                <button type="submit" class="wc-timeline-button-standard"><?php 
_e( 'Save', 'woo_j_cart' );
?></button>
                </div>    
        </form>      

</div>


