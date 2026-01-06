<?php

if (!defined('ABSPATH') || !is_admin()) {

    exit;	
	
}

$active_integrations = woo_j_integrations('integrations') ?? [];

?>

<div class="woo-upsellator-admin-page">
       
        <?php woo_j_render_admin_view('/partials/plugin_description', [ 'current_page' => sanitize_text_field( $_GET['page'] ) ])  ?>         
        
            <div class="flex-row-start wrap gx-2">                                   
                    <!-- WooCommerce Price Based on Country -->
                    <div class="wcj-card block">    
                        <div class="block-header flex-row-between">
                            <div class="title">WooCommerce Price Based on Country</div> 
                        </div>
                        <div class="block-content">
                            <a target="_blank" href="https://wordpress.org/plugins/woocommerce-product-price-based-on-countries/">
                                https://wordpress.org/plugins/woocommerce-product-price-based-on-countries/
                            </a><br><br>
                            <span class="author"><strong>by Oscar Gare</strong></span>
                        </div>
                        <div class="block-footer flex-row-between">
                            <span>activate</span>
                            <form action="wjufc_integrations" data-security="<?php echo wp_create_nonce( 'wjucf-ajax' ) ?>" method="POST" class="wjufc-ajax wjufc-auto-send">
                                <label class="wc-timeline-switch">
                                        <input name='active' <?php echo in_array( 'wcpboc', $active_integrations ) ? 'checked' : '' ?> type="checkbox">                                        
                                        <span class="slider"></span>     
                                        <input type="hidden" name="plugin" value="wcpboc">                              
                                </label>
                            </form>
                        </div>
                    </div>
                    <!-- WP Rocket -->
                    <div class="wcj-card block">    
                        <div class="block-header flex-row-between">
                            <div class="title">WP Rocket</div> 
                        </div>
                        <div class="block-content">
                            <a target="_blank" href="https://wp-rocket.me/it/">
                            https://wp-rocket.me/it/
                            </a><br><br>
                            <span class="author"><strong>by WP Media</strong></span>
                        </div>
                        <div class="block-footer flex-row-between">
                            <span>activate</span>
                            <form action="wjufc_integrations" data-security="<?php echo wp_create_nonce( 'wjucf-ajax' ) ?>" method="POST" class="wjufc-ajax wjufc-auto-send">
                                <label class="wc-timeline-switch">
                                        <input name='active' <?php echo in_array( 'wprocket', $active_integrations ) ? 'checked' : '' ?> type="checkbox">                                        
                                        <span class="slider"></span>     
                                        <input type="hidden" name="plugin" value="wprocket">                              
                                </label>
                            </form>
                        </div>
                    </div>
                     <!--FOX – Currency Switcher Professional for WooCommerce -->
                     <div class="wcj-card block">    
                        <div class="block-header flex-row-between">
                            <div class="title">FOX – Currency Switcher Professional for WooCommerce</div> 
                        </div>
                        <div class="block-content">
                            <a target="_blank" href="https://wordpress.org/plugins/woocommerce-currency-switcher/">
                            https://wordpress.org/plugins/woocommerce-currency-switcher/
                            </a><br><br>
                            <span class="author"><strong>by WP realmag777</strong></span>
                        </div>
                        <div class="block-footer flex-row-between">
                            <span>activate</span>
                            <form action="wjufc_integrations" data-security="<?php echo wp_create_nonce( 'wjucf-ajax' ) ?>" method="POST" class="wjufc-ajax wjufc-auto-send">
                                <label class="wc-timeline-switch">
                                        <input name='active' <?php echo in_array( 'woocs', $active_integrations ) ? 'checked' : '' ?> type="checkbox">                                        
                                        <span class="slider"></span>     
                                        <input type="hidden" name="plugin" value="woocs">                              
                                </label>
                            </form>
                        </div>
                    </div>
                    <!--CURCY – Multi Currency for WooCommerce -->
                    <div class="wcj-card block">    
                        <div class="block-header flex-row-between">
                            <div class="title">CURCY – Multi Currency for WooCommerce</div> 
                        </div>
                        <div class="block-content">
                            <a target="_blank" href="https://it.wordpress.org/plugins/woo-multi-currency/">
                            https://it.wordpress.org/plugins/woo-multi-currency/
                            </a><br><br>
                            <span class="author"><strong>by WP VillaTheme</strong></span>
                        </div>
                        <div class="block-footer flex-row-between">
                            <span>activate</span>
                            <form action="wjufc_integrations" data-security="<?php echo wp_create_nonce( 'wjucf-ajax' ) ?>" method="POST" class="wjufc-ajax wjufc-auto-send">
                                <label class="wc-timeline-switch">
                                        <input name='active' <?php echo in_array( 'wmc_curcy', $active_integrations ) ? 'checked' : '' ?> type="checkbox">                                        
                                        <span class="slider"></span>     
                                        <input type="hidden" name="plugin" value="wmc_curcy">                              
                                </label>
                            </form>
                        </div>
                    </div>
            </div>                        
</div>