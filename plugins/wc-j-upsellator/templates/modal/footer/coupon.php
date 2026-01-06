<div class="footer-coupon-container">					
  
    <div class="flex-row-center footer-coupon">	
        <input placeholder="<?php _e('Type your coupon code', 'woo_j_cart' ) ?>" type="text" name="coupon_code">
        <div class="coupon_button has-loader wc-timeline-button flex-row-center">
                <span class="text"><?php _e('Apply', 'woo_j_cart' ) ?></span>
                <img alt="Loader" class="loader" src="<?php echo esc_attr( woo_j_env('img_path') ) ?>/loader.svg">	
        </div>
    </div>

    <?php if( !empty( $coupons ) ): ?>
        <div class="flex-row-start coupon-list">

            <?php foreach( $coupons as $coupon ): ?> 

                    <div class="coupon remove-coupon" data-code="<?php echo $coupon['code'] ?>">
                            <?php echo $coupon['code'] ?> (<?php echo $coupon['type'] ?>)
                    </div>

            <?php endforeach; ?>

        </div>
    <?php endif; ?>
        
</div>