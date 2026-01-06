<div class="woo-upsellator-admin-nav">

            <div class="nav-tab-wrapper">

                <a href="<?php echo admin_url( 'admin.php?page=wc-j-upsellator' ); ?>"
                        class="<?php echo ( $current_page === 'wc-j-upsellator' ) ? 'nav-tab active' : 'nav-tab '; ?>">
                        <?php esc_html_e( 'Settings', 'woo_j_cart'  ); ?>
                </a>   
                
                <a href="<?php echo admin_url( 'admin.php?page=wc-j-upsellator-styles' ); ?>"                  
                    class="<?php echo ( $current_page === 'wc-j-upsellator-styles' ) ? 'nav-tab active' : 'nav-tab '; ?>">
                        <?php esc_html_e( 'Style', 'woo_j_cart' ); ?>
                 </a>

                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-j-upsellator-upsells' ) ); ?>"
                class="<?php echo ( $current_page === 'wc-j-upsellator-upsells' ) ? 'nav-tab active' : 'nav-tab '; ?>">
                        <?php esc_html_e( 'Upsells', 'woo_j_cart' ); ?>
                </a> 
                
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-j-upsellator-gifts' ) ); ?>"
                class="<?php echo ( $current_page === 'wc-j-upsellator-gifts' ) ? 'nav-tab active' : 'nav-tab '; ?>">
                        <?php esc_html_e( 'Gifts', 'woo_j_cart' ); ?>
                </a> 

                 <a href="<?php echo admin_url( 'admin.php?page=wc-j-upsellator-dynamic-bar' ); ?>"                  
                    class=" needs-modal <?php echo woo_j_conf('only_background') && !woo_j_conf('dynamic_bar_shortcode') ? 'transparent' : '' ?> <?php echo ( $current_page === 'wc-j-upsellator-dynamic-bar' ) ? 'nav-tab active' : 'nav-tab '; ?>">
                        <?php esc_html_e( 'Dynamic bar', 'woo_j_cart' ); ?>
                 </a>

                 <a href="<?php echo admin_url( 'admin.php?page=wc-j-upsellator-shop-pages' ); ?>"                  
                    class="<?php echo ( $current_page === 'wc-j-upsellator-shop-pages' ) ? 'nav-tab active' : 'nav-tab '; ?>">
                        <?php esc_html_e( 'Shop pages', 'woo_j_cart' ); ?>
                 </a>

                 <a href="<?php echo admin_url( 'admin.php?page=wc-j-upsellator-exclusion' ); ?>"                  
                    class="needs-modal <?php echo woo_j_conf('only_background') ? 'transparent' : '' ?> <?php echo ( $current_page === 'wc-j-upsellator-exclusion' ) ? 'nav-tab active' : 'nav-tab '; ?>">
                        <?php esc_html_e( 'Excluded pages', 'woo_j_cart' ); ?>
                 </a>

                 <a href="<?php echo admin_url( 'admin.php?page=wc-j-upsellator-shortcodes' ); ?>"                  
                    class="needs-modal <?php echo woo_j_conf('only_background') ? 'transparent' : '' ?> <?php echo ( $current_page === 'wc-j-upsellator-shortcodes' ) ? 'nav-tab active' : 'nav-tab '; ?>">
                        <?php esc_html_e( 'Shortcodes', 'woo_j_cart' ); ?>
                 </a>
            </div>

</div>
