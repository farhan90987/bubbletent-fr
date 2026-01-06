<?php

if (!defined('ABSPATH') || !is_admin()) {

    exit;	
	
}

use WcJUpsellator\Utility\Notice;
use WcJUpsellator\Options\OptionShippingBar;

$config        = new OptionShippingBar();  
$icons         = $config->getShippingIcons(); 


if( isset( $_POST['edit_wc_timeline_config'] ) &&  wp_verify_nonce( $_POST['edit_wc_timeline_config'], 'edit_wc_timeline_config' )  )
{           
             
        $config->shipping_timeline              = isset( $_POST['shipping_timeline'] ) ? 1 : 0 ;
        
        $config->success_text                   = wp_kses( wp_unslash( $_POST['success_text']  ), woo_j_string_filter() );
       
        $config->saveGoals( $_POST['goals'] ?? null );    
        
        $config->s_b_bar_background             = sanitize_hex_color( $_POST['s_b_bar_background'] );
        $config->s_b_success_background         = sanitize_hex_color( $_POST['s_b_success_background'] );
        $config->s_b_bar_background_empty       = sanitize_hex_color( $_POST['s_b_bar_background_empty'] );
        $config->shipping_bar_type              = sanitize_text_field( $_POST['shipping_bar_type'] );
        
        $config->save();
        
        $notice = new Notice();
        $notice->setText( __( 'Settings updated', 'woo_j_cart' ) );
        $notice->success();
        
        $notice->show();
}

$goals                  = woo_j_shipping('goals');

?>

<div class="hidden new-goal-template">
        <div class="row">
                <div class="heading"><?php _e( 'Goal',  'woo_j_cart'  ) ?></div>
                <div class="option medium has-attribute">
                        <span class="attribute"><?php echo esc_html( woo_j_conf('currency') ) ?></span>     
                        <input type="number" min="0" step=".01" name='goals[limit][]' value="0">                                   
                </div>
                <div class="option">
                        <textarea placeholder="<?php _e( 'text displayed during goal',  'woo_j_cart'  ) ?>" class="shipping-before-price" name="goals[text][]"></textarea>                                                                     
                </div>
                <div class="option has-attribute middle">
                        <select name="shipping_icon" style="font-size:10px;" class="modal_icon_select select-shipping-icon">
                                <?php foreach( $icons as $icon_s => $text ): ?>
                                        <option value="<?php echo esc_attr( $icon_s ) ?>">
                                        <?php echo $text  ?>
                                        </option>    
                                <?php endforeach; ?>            
                        </select>
                        <span class="attribute wooj-icon-gift"></span>
                </div>
                <div class="product-actions">  

                        <button data-title = "<?php _e( 'Delete goal', 'woo_j_cart' ) ?>"                                                                                              
                                data-text = "<?php _e( 'Do you want to remove the selected goal?', 'woo_j_cart' ) ?>"                                                
                                class="woo-j-action-round  red delete-goal">
                                <i class="wooj-icon-trash"></i>
                        </button> 
                </div> 
        </div>   
</div>

<div class="woo-upsellator-admin-page">

        <?php woo_j_render_admin_view('/partials/plugin_description', [ 'current_page' => sanitize_text_field( $_GET['page'] ) ])  ?>  

        <?php woo_j_render_admin_view('/partials/nav_bar', [ 'current_page' => sanitize_text_field( $_GET['page'] ) ])  ?>

         <form method="post">

                <div class="woo-upsellator-admin-content">

                        <?php wp_nonce_field( 'edit_wc_timeline_config', 'edit_wc_timeline_config' ); ?> 

                                <div class="row-wrapper">

                                        <div class="row">
                                                <div class="heading"><?php _e( 'Active',  'woo_j_cart'  ) ?></div>
                                                <div class="option">
                                                        <label class="wc-timeline-switch">
                                                                <input name='shipping_timeline' <?php echo ( woo_j_shipping('shipping_timeline') === 1 ) ? 'checked' : '' ?>  type="checkbox" class="has-text">
                                                                <span class="slider"></span>
                                                                <span class="text-content" data-yes="<?php _e( 'active',  'woo_j_cart'  ) ?>" data-no="<?php _e( 'inactive',  'woo_j_cart'  ) ?>"></span>
                                                        </label>
                                                </div>
                                                <div class="tips">
                                                        <?php _e( 'Activate/Deactivate dynamic bar',  'woo_j_cart'  ) ?> 
                                                </div>
                                        </div>                                                                      
                                     
                                        <div class="dynamic-bar-goals">                                                

                                                <p class="mini-info">
                                                        <?php _e( '<b>Set your dynamic bar goals.</b><br>To dynamically display the missing amount, use the {price_left} placeholder.<br><br>
                                                                   Common use 1: create a xx gift triggered at 50€, set a bar goal at 50€ and put as text <b>Add another {price_left} to get the xx gift</b><br>
                                                                   Common use 2: set a goal at the same amount of your free shipping limit and put as text <b>Add another {price_left} to get the free shipping</b>', 'woo_j_cart' ) ?>
                                                </p>

                                                <?php if( $goals && count( $goals ) ): 

                                                        foreach( $goals as $goal ): ?>

                                                                <div class="row">
                                                                        <div class="heading"><?php _e( 'Goal',  'woo_j_cart'  ) ?></div>
                                                                        <div class="option medium has-attribute">
                                                                                <span class="attribute"><?php echo esc_html( woo_j_conf('currency') ) ?></span>     
                                                                                <input min="0" type="number" step=".01" name='goals[limit][]' value="<?php echo esc_attr( $goal['limit'] ?? 0 ) ?>">                                   
                                                                        </div>
                                                                        <div class="option">
                                                                                <textarea class="shipping-before-price" name="goals[text][]"><?php echo wp_unslash( esc_html( $goal['text'] ?? '' ) ) ?></textarea>                                                                     
                                                                        </div>
                                                                        <div class="option has-attribute middle">
                                                                                <select name="goals[icon][]" style="font-size:10px;" class="modal_icon_select select-shipping-icon">
                                                                                        <?php foreach( $icons as $icon_s => $text ): ?>
                                                                                                <option <?php echo ( $icon_s == $goal['icon'] ) ? 'selected':'' ?> value="<?php echo esc_attr( $icon_s ) ?>">
                                                                                                <?php echo $text ?>
                                                                                                </option>    
                                                                                        <?php endforeach; ?>            
                                                                                </select>
                                                                                <span class="attribute <?php echo  esc_attr( $goal['icon'] ?? 0 ) ?>"></span>
                                                                        </div>
                                                                       
                                                                        <div class="product-actions">  
                                                
                                                                                <button data-title = "<?php _e( 'Delete goal', 'woo_j_cart' ) ?>"                                                                                              
                                                                                        data-text = "<?php _e( 'Do you want to remove the selected goal?', 'woo_j_cart' ) ?>"                                                
                                                                                        class="woo-j-action-round  red delete-goal">
                                                                                        <i class="wooj-icon-trash"></i>
                                                                                </button> 
                                                                        </div> 
                                                                </div>

                                                        <?php endforeach;
                                                else: ?>

                                                        <div class="no-goals wcj-banner">
                                                                <strong><?php _e( 'You have no goal set.',  'woo_j_cart'  ) ?></strong><br>
                                                                <?php _e( 'To display the dynamic bar, add your first goal.',  'woo_j_cart'  ) ?>
                                                          </div>


                                                <?php endif; ?>
                                        </div>

                                        <div class="row-submit new-goal-button" style="margin-bottom:20px;">
                                                                 <i class="icon-list-add"></i>
                                                                 <?php _e( 'Add new goal',  'woo_j_cart'  ) ?>                       
                                        </div>
                                        
                                        <div class="row">
                                                <div class="heading"><?php _e( 'Success text',  'woo_j_cart'  ) ?></div>
                                                <div class="option">
                                                <textarea name="success_text"><?php echo wp_unslash( esc_html( woo_j_shipping('success_text')) ) ?></textarea>                                                                     
                                                </div>
                                                <div class="tips">
                                                        <?php _e( 'Text displayed when all goals are completed',  'woo_j_cart'  ) ?> 
                                                </div>
                                        </div>
                                </div>
                                
                                <section class="wp-timeline-admin-box">
                                                                                        
                                        <div class="row main">
                                                <div class="heading"><?php _e( 'Style', 'woo_j_cart' ) ?></div>
                                                <div class="option"><?php _e( 'Dynamic bar colors', 'woo_j_cart' ) ?></div>
                                        </div>    
                                        
                                        <div class="row-wrapper">

                                                        <div class="row">
                                                                <div class="heading"><?php _e( 'Bar type', 'woo_j_cart' ) ?></div>
                                                                <div class="option">
                                                                        <select name="shipping_bar_type" class="modal_icon_select select-shipping-bar-type">                                                                                
                                                                                <option <?php echo ( woo_j_shipping('shipping_bar_type') == 'line2' ) ? 'selected':'' ?> value="line2"><?php _e( 'Small line 2', 'woo_j_cart' ) ?></option>  
                                                                                <option <?php echo ( woo_j_shipping('shipping_bar_type') == 'line' ) ? 'selected':'' ?> value="line"><?php _e( 'Big line', 'woo_j_cart' ) ?></option>                                                                                             
                                                                        </select>                                                                        
                                                                </div>
                                                        </div>                                                          
                
                                                        <div class="row">
                                                                <div class="heading"><?php _e( 'Default color', 'woo_j_cart' ) ?></div>
                                                                <div class="option">
                                                                <input class="color-picker" data-variable-change="shipping_bar_bar_background" name="s_b_bar_background" type="text"  value="<?php echo esc_attr( woo_j_shipping('s_b_bar_background') ) ?>" data-default-color="#0a0a0a">
                                                                </div>
                                                        </div>

                                                        <div class="row">
                                                                <div class="heading"><?php _e( 'Default color ( empty )', 'woo_j_cart' ) ?></div>
                                                                <div class="option">
                                                                <input class="color-picker" data-variable-change="shipping_bar_bar_background_empty" name="s_b_bar_background_empty" type="text"  value="<?php echo esc_attr( woo_j_shipping('s_b_bar_background_empty') ?? '#fbfbfb' ) ?>" data-default-color="#fbfbfb">
                                                                </div>
                                                        </div>
                
                                                        <div class="row">
                                                                <div class="heading"><?php _e( 'Success color', 'woo_j_cart' ) ?></div>
                                                                <div class="option">
                                                                <input class="color-picker"  name="s_b_success_background" type="text"  value="<?php echo esc_attr( woo_j_shipping('s_b_success_background') ) ?>" data-default-color="#9dc192">
                                                                </div>
                                                        </div>  

                                                        <div class="row">
                                                                <div class="heading">
                                                                        <?php _e( 'Preview', 'woo_j_cart' ) ?>
                                                                </div>
                                                                <div class="option big">

                                                                         <div class="shipping-bar-preview-container <?php echo esc_attr( woo_j_shipping('shipping_bar_type') ) ?> flex-column-center">
                                                                                <div class="shipping-bar-preview">			
                                                                                        <div class="shipping-progress-bar"></div>
                                                                                        <div class="<?php echo esc_attr( $goals[ 0 ]['icon'] ?? 'wooj-gift' ) ?> flex-row-center shipping-icon"></div>
                                                                                </div>
                                                                                
                                                                                <div class="shipping-bar-text">	
                                                                                        <?php _e('Keep shopping, you need another', 'woo_j_cart'  ) ?>
                                                                                        <b>xx<?php echo esc_html( woo_j_conf('currency') ) ?></b> 
                                                                                        <span class="shipping-post-price"> <?php _e('to reach the goal', 'woo_j_cart'  ) ?></span>
                                                                                </div>			
                                                                        </div>       
                                                                </div>
                                                        </div>                                                        
                                        </div>                
                                </section>                          
                </div>

                <button type="submit" class="wc-timeline-button-standard"><?php _e( 'Save',  'woo_j_cart'  ) ?></button>

        </form>

</div>


