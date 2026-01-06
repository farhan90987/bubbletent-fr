<div class="woo-upsellator-admin-nav">
            
            <div class="nav-tab-wrapper">
                
                <a href="<?php 
echo  admin_url( 'admin.php?page=wc-j-upsellator-stats' ) ;
?>"
                        class="<?php 
echo  ( $current_page === 'wc-j-upsellator-stats' && $tab == '' ? 'nav-tab active' : 'nav-tab ' ) ;
?>">
                        <?php 
esc_html_e( 'Stats', 'woo_j_cart' );
?>
                </a>  

                 <?php 
?>

                    <div class="nav-tab needs-pro">
                                <?php 
esc_html_e( 'Advanced Stats', 'woo_j_cart' );
?>
                    </div>

                 <?php 
?>
               
            </div>

</div>
