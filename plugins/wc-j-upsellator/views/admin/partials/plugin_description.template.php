<section class="woo-upsellator-admin-page-heading">

    <div class="heading">

        <div class="main-heading">
                <h1>J Cart Upsell and cross-sell </h1> <span class="upsellator-small">version</span><strong><?php echo WC_J_UPSELLATOR_VERSION; ?></strong>
                
        </div>

        <div class="info-heading">
            <a href="https://www.linkedin.com/in/giacomo-zoffoli-9bb33bb0/" target="_blank" rel="noopener">
                        <i class="wooj-icon-linkedin-squared"></i>
            <span class="upsellator-small">by</span><strong>Zoffoli Giacomo</strong></a>
        </div>
       
        <form action="wjufc_set_test_mode" data-security="<?php echo wp_create_nonce( 'wjucf-ajax' ) ?>" method="POST" class="wjufc-ajax wjufc-auto-send test-mode-switch">
                
                <label class="wc-timeline-switch blue">
                        <input name='test_mode' <?php echo ( woo_j_conf('test_mode') == 1 ) ? 'checked' : '' ?> type="checkbox" class="has-text">
                        <span class="slider"></span>
                        <span class="text-content" data-yes="<?php _e( 'You are on test mode, switch to Live!',  'woo_j_cart'  ) ?>" data-no="<?php _e( 'Switch to Test Mode',  'woo_j_cart'  ) ?>"></span>
                </label>
                
        </form>
        
    </div>  

    <div class="actions">

        <?php            
            
            if ( wju_fs()->is_not_paying() ) : ?>
    
            <div class="woo-upsellator-premium">
                <a href="<?php echo wju_fs()->get_upgrade_url() ?>" class="woo-upsellator-admin-button woo-nav-button">
                    <?php _e( 'Go Premium',  'woo_j_cart'  ) ?>
                </a>
            </div>

        <?php endif; ?>  

        <div class="woo-upsellator-contacts">
                <a href="https://www.buymeacoffee.com/Jakjako" target="_blank" class="woo-upsellator-admin-button woo-nav-button coffee yellow">
                    <img src="<?php echo WC_J_UPSELLATOR_PLUGIN_URL ?>assets/buy-me-a-coffee.png">
                    <?php _e( 'Buy me a coffee',  'woo_j_cart'  ) ?>
                </a>
        </div>

    </div>

</section>

