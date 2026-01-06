<div class="wc-timeline-empty-modal wc-items-container flex-column-center">

		<?php if( !empty( woo_j_conf('text_empty_heading') ) ): ?>
			<div class="heading">
					<?php echo wjc__( woo_j_conf('text_empty_heading') ) ?>
			</div>	
		<?php endif; ?>

		<?php if( woo_j_conf('empty_cart_icon') ): ?>
			<i class="<?php echo esc_attr( woo_j_conf('empty_cart_icon') ) ?> empty-icon"></i>
		<?php endif; ?>
		
		<div class="sub-heading"><?php echo wjc__( woo_j_conf('text_empty_text') ) ?></div>
		
		<a href='<?php echo wjc_getpage( woo_j_conf('shop_url') ) ?>' title='shop' style='width:100%;'>
				<div class='wc-timeline-button flex-row-center'>
					<?php echo wjc__( woo_j_conf('text_empty_button') ) ?>
				</div>
		</a>

</div>