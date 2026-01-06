<?php

if (!defined('ABSPATH') || !is_admin()) {	

    exit;	
	
}

use WcJUpsellator\Utility\Notice;
use WcJUpsellator\Options\OptionStyles;

$styles                 = new OptionStyles();

if( isset( $_POST['edit_wc_timeline_styles'] ) &&  wp_verify_nonce( $_POST['edit_wc_timeline_styles'], 'edit_wc_timeline_styles' )  )
{    
       
        $styles->background_color               = sanitize_hex_color( $_POST['background_color'] );
        $styles->button_color                   = sanitize_hex_color( $_POST['button_color'] );
        $styles->button_font_color              = sanitize_hex_color( $_POST['button_font_color'] );
        $styles->button_color_hover             = sanitize_hex_color( $_POST['button_color_hover'] );
        $styles->button_font_color_hover        = sanitize_hex_color( $_POST['button_font_color_hover'] );
        $styles->font_color                     = sanitize_hex_color( $_POST['font_color'] );
        $styles->item_count_background          = sanitize_hex_color( $_POST['item_count_background'] );
        $styles->item_count_color               = sanitize_hex_color( $_POST['item_count_color'] ); 
        $styles->modal_icon                     = sanitize_text_field( $_POST['modal_icon'] );  
        
        $styles->setTheme( sanitize_text_field( $_POST['theme'] ) );       
       
        $styles->gift_color                     = sanitize_hex_color( $_POST['gift_color'] ); 
        $styles->base_font_size                 = (int)$_POST['base_font_size'];      
        $styles->image_ratio                    = (double)$_POST['image_ratio'];     
        $styles->gift_text_color                = sanitize_hex_color( $_POST['gift_text_color'] ); 
        $styles->upsell_color                   = sanitize_hex_color( $_POST['upsell_color'] ); 
        $styles->upsell_text_color              = sanitize_hex_color( $_POST['upsell_text_color'] );        
       
        $styles->modal_close_text               = sanitize_hex_color( $_POST['modal_close_text'] ); 
        $styles->modal_close_background         = sanitize_hex_color( $_POST['modal_close_background'] );

        $styles->save();

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

                <?php wp_nonce_field( 'edit_wc_timeline_styles', 'edit_wc_timeline_styles' ); ?>

                <!-- Modal cart  -->
                <section class="wp-timeline-admin-box needs-modal <?php echo woo_j_conf('only_background') ? 'transparent' : '' ?>">

                        <div class="row main">
                                <div class="heading">Modal Cart</div>
                                <div class="option"><?php _e( 'Modal cart global details', 'woo_j_cart' ) ?></div>
                        </div>

                        <div class="row-wrapper">

                                        <div class="row">
                                                <div class="heading"><?php _e( 'Theme', 'woo_j_cart' ) ?></div>
                                                <div class="option">
                                                        <select name="theme">
                                                                <?php foreach( $styles->getThemes() as $theme ): ?>
                                                                        <option <?php echo ( $theme == woo_j_styles('theme') ) ? 'selected':'' ?> value="<?php echo esc_attr( $theme ) ?>">
                                                                                <?php echo esc_html( $theme )  ?>
                                                                        </option>    
                                                                <?php endforeach; ?>            
                                                        </select>
                                                </div>
                                                <div class="tips">
                                                        <?php _e( 'Modal cart theme: default (large) or small',  'woo_j_cart'  ) ?> 
                                                </div> 
                                        </div>
                                        
                                        <div class="row">
                                                <div class="heading">Icon</div>
                                                <div class="option has-attribute">
                                                        <select name="modal_icon" class="modal_icon_select">
                                                                <?php foreach( $styles->getIcons() as $icon ): ?>
                                                                        <option <?php echo ( $icon == woo_j_styles('modal_icon') ) ? 'selected':'' ?> value="<?php echo esc_attr( $icon ) ?>">
                                                                                <?php echo esc_html( $icon )  ?>
                                                                        </option>    
                                                                <?php endforeach; ?>            
                                                        </select>
                                                        <span class="attribute <?php echo esc_attr( woo_j_styles('modal_icon') ) ?>"></span>
                                                </div>
                                        </div>

                                        <div class="row">
                                                <div class="heading">Fonts</div>
                                                <div class="option">
                                                <input class="color-picker" name="font_color" type="text" value="<?php echo esc_attr(  woo_j_styles('font_color') )  ?>" data-default-color="#ffffff">
                                                </div>
                                        </div>

                                        <div class="row">
                                                <div class="heading">Background</div>
                                                <div class="option">
                                                <input class="color-picker" name="background_color" type="text"  value="<?php echo esc_attr(  woo_j_styles('background_color') )  ?>" data-default-color="#ffffff">
                                                </div>
                                        </div>

                                        <div class="row">
                                                <div class="heading">Base font size</div>
                                                <div class="option has-attribute">
                                                        <span class="attribute font-size active">px</span> 
                                                        <input name="base_font_size" type="number"  value="<?php echo esc_attr( woo_j_styles('base_font_size') ) ?>">
                                                </div>
                                                <div class="tips">
                                                        <?php _e( 'Cart base font-size: changing this value changes the size of all strings', 'woo_j_cart' ) ?>
                                                </div>
                                        </div>

                                        <div class="row">
                                                <div class="heading">Product image ratio</div>
                                                <div class="option has-attribute">
                                                        <span class="attribute font-size active">%</span> 
                                                        <input name="image_ratio" step="0.01" type="number"  value="<?php echo esc_attr( woo_j_styles('image_ratio') ) ?>">
                                                </div>
                                                <div class="tips">
                                                        <?php _e( 'Ratio of product images on the modal cart: width / height. Keep 1 for squared images, > 1 for horizontal images, < 1 for vertical images.', 'woo_j_cart' ) ?>
                                                </div>
                                        </div>
                                     
                        </div>                
                </section>
                <!-- Buttons  -->
                <section class="wp-timeline-admin-box needs-modal <?php echo woo_j_conf('only_background') ? 'transparent' : '' ?>">   

                        <div class="row main">
                                <div class="heading"><?php _e( 'Buttons', 'woo_j_cart' ) ?></div>
                                <div class="option"><?php _e( 'Modal cart buttons colors', 'woo_j_cart' ) ?></div>
                        </div>

                        <div class="row-wrapper">
                                 
                                <div class="row">
                                        <div class="heading">Background</div>
                                        <div class="option middle">
                                                <input class="color-picker" name="button_color" type="text"  value="<?php echo esc_attr(  woo_j_styles('button_color') )  ?>" data-default-color="#3E3E3E">
                                        </div>
                                        <div class="option">
                                                <small>hover:</small>&nbsp;
                                                <input class="color-picker" name="button_color_hover" type="text"  value="<?php echo esc_attr(  woo_j_styles('button_color_hover') )  ?>" data-default-color="#FFFFFF">
                                        </div>
                                </div>                                               
                                <div class="row">
                                        <div class="heading"><?php _e( 'Fonts', 'woo_j_cart' ) ?></div>
                                        <div class="option middle">
                                                <input class="color-picker" name="button_font_color" type="text" value="<?php echo esc_attr(  woo_j_styles('button_font_color') )  ?>" data-default-color="#ffffff">
                                        </div>
                                        <div class="option">
                                                <small>hover:</small>&nbsp;
                                                <input class="color-picker" name="button_font_color_hover" type="text"  value="<?php echo esc_attr(  woo_j_styles('button_font_color_hover') )  ?>" data-default-color="#3E3E3E">
                                        </div>
                                </div>
                        </div>

                </section>  
                 <!-- Buttons  -->
                 <section class="wp-timeline-admin-box needs-modal <?php echo woo_j_conf('only_background') ? 'transparent' : '' ?>">   

                        <div class="row main">
                                <div class="heading"><?php _e( 'Modal Close', 'woo_j_cart' ) ?></div>
                                <div class="option"><?php _e( 'Modal cart close button', 'woo_j_cart' ) ?></div>
                        </div>

                        <div class="row-wrapper">
                                        <div class="row">
                                                <div class="heading">Background</div>
                                                <div class="option">
                                                <input class="color-picker" name="modal_close_background" type="text"  value="<?php echo esc_attr(  woo_j_styles('modal_close_background')  ) ?>" data-default-color="#3E3E3E">
                                                </div>
                                        </div>                                                         
                                        <div class="row">
                                                <div class="heading"><?php _e( 'Fonts', 'woo_j_cart' ) ?></div>
                                                <div class="option">
                                                <input class="color-picker" name="modal_close_text" type="text" value="<?php echo esc_attr(  woo_j_styles('modal_close_text') )  ?>" data-default-color="#ffffff">
                                                </div>
                                        </div>                                        
                        </div>

                </section>                            
                <p class="info" style="margin:30px 0px;">
                        <?php _e( 'J Cart Upsell divides the products into 3 different types: <b>upsells</b>, <b>gifts</b> and <b>standard</b>.<br>
                                You can set a different color for each of these type of products.', 'woo_j_cart' ) ?>
                </p>
                <!-- Upsells -->
                <section class="wp-timeline-admin-box">   

                        <div class="row main">
                                <div class="heading"><?php _e( 'Default', 'woo_j_cart' ) ?></div>
                                <div class="option"><?php _e( 'Default products label style', 'woo_j_cart' ) ?></div>
                        </div>    
                        
                        <div class="row-wrapper">        

                                        <div class="row">
                                                <div class="heading">Background</div>
                                                <div class="option">
                                                <input class="color-picker" name="item_count_background" type="text"  value="<?php echo esc_attr(  woo_j_styles('item_count_background') )  ?>" data-default-color="#e4853a">
                                                </div>
                                        </div>                                                                     
                                        <div class="row">
                                                <div class="heading"><?php _e( 'Fonts', 'woo_j_cart' ) ?></div>
                                                <div class="option">
                                                <input class="color-picker" name="item_count_color" type="text" value="<?php echo esc_attr(  woo_j_styles('item_count_color') )  ?>" data-default-color="#ffffff">
                                                </div>
                                        </div>                                                                               
                        </div>
                </section>
                <!-- Upsells -->
                <section class="wp-timeline-admin-box">   

                        <div class="row main">
                                <div class="heading"><?php _e( 'Upsells', 'woo_j_cart' ) ?></div>
                                <div class="option"><?php _e( 'Upsell products label style', 'woo_j_cart' ) ?></div>
                        </div>    
                        
                        <div class="row-wrapper">                                                                            
                                        <div class="row">
                                                <div class="heading"><?php _e( 'Background', 'woo_j_cart' ) ?></div>
                                                <div class="option">
                                                        <input class="color-picker" name="upsell_color" type="text"  value="<?php echo esc_attr(  woo_j_styles('upsell_color') )  ?>" data-default-color="#9dc192">
                                                </div>
                                        </div>
                                        
                                        <div class="row">
                                                <div class="heading"><?php _e( 'Fonts', 'woo_j_cart' ) ?></div>
                                                <div class="option">
                                                        <input class="color-picker" name="upsell_text_color" type="text"  value="<?php echo esc_attr(  woo_j_styles('upsell_text_color') )  ?>" data-default-color="#FFFFFF">
                                                </div>
                                        </div>  
                                        
                        </div>
                </section>
               <!-- Gifts -->
               <section class="wp-timeline-admin-box">   

                        <div class="row main">
                                <div class="heading"><?php _e( 'Gifts', 'woo_j_cart' ) ?></div>
                                <div class="option"><?php _e( 'Gift products label style', 'woo_j_cart' ) ?></div>
                        </div>    
                        
                        <div class="row-wrapper">                                                                               
                                <div class="row">
                                        <div class="heading"><?php _e( 'Background', 'woo_j_cart' ) ?></div>
                                        <div class="option">
                                                <input class="color-picker" name="gift_color" type="text"  value="<?php echo esc_attr(  woo_j_styles('gift_color') )  ?>" data-default-color="#9dc192">
                                        </div>
                                </div>
                                
                                <div class="row">
                                        <div class="heading"><?php _e( 'Fonts', 'woo_j_cart' ) ?></div>
                                        <div class="option">
                                                <input class="color-picker" name="gift_text_color" type="text"  value="<?php echo esc_attr(  woo_j_styles('gift_text_color') ) ?>" data-default-color="#FFFFFF">
                                        </div>
                                </div>  
                                        
                        </div>
                </section>                                                        
        </div>

        <button type="submit" class="wc-timeline-button-standard"><?php _e( 'Save', 'woo_j_cart' ) ?></button>

        </form>        

</div>


