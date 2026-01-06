<?php if( !empty( $upsell_items ) ): 			
			
			if( count( $upsell_items ) > 1 )
			{
					?>
						<div class="wc-j-items-carousel">
							<div class="wc-j-items-carousel-inner <?php echo $type == 3  ? 'stacked' : '' ?>">
							<?php 

								foreach( $upsell_items as $product ): 
										
										woo_j_render_view('/partials/upsell/upsell_item', ['product' => $product,  'upsell_type' => $mode ] );

								endforeach;

							?>
							</div>
						</div>

						<?php if( $type == 2 ): ?>
							<div class="wc-j-items-carousel-nav flex-row-between">
								<div class="wc-nav-prev">&lsaquo;</div>
								<div class="wc-nav-bullets flex-row-center">
									<?php for( $bullet = 0; $bullet < count( $upsell_items ); $bullet++ ): ?>

											<div data-index="<?php echo $bullet ?>" class="wc-j-bullet <?php echo ( $bullet  == 0 ) ? 'active' : '' ?>"></div>

									<?php endfor; ?>
								</div>	
								<div class="wc-nav-next">&rsaquo;</div>
							</div>
						<?php endif; ?>
					<?php 
			}else{
			
				woo_j_render_view('/partials/upsell/upsell_item', ['product' => current( $upsell_items ), 'upsell_type' => $mode ] );
			}
    
endif; 