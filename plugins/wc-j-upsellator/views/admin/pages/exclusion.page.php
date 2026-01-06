<?php

if (!defined('ABSPATH') || !is_admin()) {
    exit;		
}

use WcJUpsellator\Utility\Notice;
use WcJUpsellator\Options\OptionExclusion;

$config                 = new OptionExclusion(); 
$pages                  = $config->getValidPages();

if( isset( $_POST['edit_wc_timeline_exclusion'] ) &&  wp_verify_nonce( $_POST['edit_wc_timeline_exclusion'], 'edit_wc_timeline_exclusion' )  )
{ 

        $config->addPages( $_POST['excluded'] ?? null );
        $config->mode  = $_POST['exclusion_mode'] == 1  ? 1 : 0 ;

        $notice = new Notice();
        $notice->setText( __( 'Configuration saved', 'woo_j_cart' ) );
        $notice->success();
        

        $config->save();        
        $notice->show();
}
/*
/* Get current excluded pages
*/
$current_pages   = woo_j_exclusion('pages');

?>

<div class="woo-upsellator-admin-page">

        <?php woo_j_render_admin_view('/partials/plugin_description', [ 'current_page' => sanitize_text_field( $_GET['page'] )])  ?>  

        <?php woo_j_render_admin_view('/partials/nav_bar', [ 'current_page' => sanitize_text_field( $_GET['page'] ) ])  ?>

                <div class="woo-upsellator-admin-content">                                                   

                        <form method="post">

                                <?php wp_nonce_field( 'edit_wc_timeline_exclusion', 'edit_wc_timeline_exclusion' ); ?>

                                <p class="info">
                                        <?php echo _e( 'Exclude some pages from displaying the Upsellator modal cart.<br><br>
                                                  Choose a mode to decide if selected pages should or should not show the modal cart.<br> 
                                                  Activate a page clicking on the switch.<br><br>
                                                  <u><b>Checkout page</b> and <b>Cart page</b> are excluded by default</u>', 'woo_j_cart' ) ?>
                                </p>
                               
                                <div class="row-wrapper">  

                                        <div class="row">
                                                <div class="heading"><?php echo _e( 'Selection mode',  'woo_j_cart'  ) ?></div>
                                                <div class="option">                                                        
                                                        <select name="exclusion_mode">                                                                        
                                                                <option <?php echo  !woo_j_exclusion('mode') ? 'selected' : '' ?> value="0"><?php _e( 'Exclude selected pages', 'woo_j_cart' ) ?></option>
                                                                <option <?php echo  woo_j_exclusion('mode') ? 'selected' : '' ?> value="1"><?php _e( 'Allow selected pages', 'woo_j_cart' ) ?></option>                                                             
                                                        </select>
                                                </div>
                                                <div class="tips">
                                                        <?php _e( 'only allow selected pages or exclude selected pages', 'woo_j_cart' ) ?>
                                                </div>                                    
                                                                                        
                                        </div>                                  
                                </div>   

                                <div class="row-wrapper">  

                                        <div class="row">
                                                <div class="heading"><?php echo _e( 'Search a page',  'woo_j_cart'  ) ?></div>
                                                <div class="option">
                                                        <input type="search"  class="upsell-admin-text" id="search_pages">
                                                </div>
                                                <div class="tips">
                                                        <?php _e( 'search a page typing at least 3 characters', 'woo_j_cart' ) ?>
                                                </div>                                    
                                                                                        
                                        </div>                                  
                                </div>                            
                                
                                <?php if( count( $pages ) ): ?>
                                        <div class="row-wrapper">                                          

                                                <?php foreach( $pages as $page ): ?>

                                                        <div class="row page" data-page="<?php echo esc_attr( strtolower( $page['title'] ) ) ?>">
                                                                <div class="heading"><?php echo esc_html( $page['title'] ) ?></div>   
                                                                <div class="option">
                                                                        <?php _e( ucfirst( $page['status'] ) ) ?>,&nbsp;&nbsp; <small><?php echo esc_attr( $page['post_date'] ) ?></small>                               
                                                                </div>      
                                                                <div class="option">
                                                                        <label class="wc-timeline-switch">
                                                                                <input <?php echo  in_array( $page['id'], $current_pages ) ? 'checked' : ''; ?> name='excluded[]' value="<?php echo esc_attr( $page['id'] ) ?>" type="checkbox">
                                                                                <span class="slider"></span>
                                                                        </label>
                                                                </div>                                                                                                       
                                                        </div>  

                                                <?php endforeach; ?>
                                        </div>
                                <?php endif; ?>

                                <button type="submit" class="wc-timeline-button-standard"><?php _e( 'Save',  'woo_j_cart'  ) ?></button>
                        </form>
                        
                </div>

                
          

        </form>

</div>


