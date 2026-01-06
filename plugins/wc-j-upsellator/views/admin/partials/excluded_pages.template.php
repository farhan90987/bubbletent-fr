<?php  
$excluded_pages = woo_j_exclusion('pages');

if( !empty($excluded_pages) ): ?>

        <div class="row-wrapper"> 

       <?php foreach( $excluded_pages as $excluded_page ): ?>                 

                <form method="post">
                        <input type="hidden" name="add" value="0">
                        <?php wp_nonce_field( 'edit_wc_timeline_exclusion', 'edit_wc_timeline_exclusion' ); ?>

                        <div class="row">
                                <div class="heading"><?php echo esc_html( $excluded_page['name'] ) ?></div>
                                <div class="option  has-attribute">           
                                        <input name='page_name' type="text" value="<?php echo esc_attr( $excluded_page['slug'] ) ?>" readonly>                                             
                                        <span class="attribute wooj-icon-newspaper"></span>
                                </div>

                                <input type="hidden" name="page_id" value="<?php echo esc_attr( $excluded_page['id'] ) ?>">

                                <button class="row-submit red" type="submit"><?php _e( 'Remove from list',  'woo_j_cart'  ) ?></button>                                      
                                                                                
                        </div>  
                        
                </form>                

        <?php endforeach; ?>

        </div>

 <?php endif; ?>