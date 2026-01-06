<div class='wc-timeline-footer flex-column-center' data-total="<?php echo round( $total, wc_get_price_decimals() ) ?>">			

	<?php if( $items > 0 ): ?>

			<?php if( woo_j_conf('coupon_code') ): ?>			
				<?php woo_j_render_template('/modal/footer/coupon', ['coupons' => $coupons ]); ?>	
			<?php endif; ?>

			<div class='total flex-row-between'>

						<div class="wc-timeline-footer-items-count flex-row-center">										
								<?php if( woo_j_conf('footer_items_count') ): ?>
									<strong><?php echo WC()->cart->get_cart_contents_count() ?></strong>
									<span class="items-count-text"><?php _e('items<br>in cart', 'woo_j_cart') ?></span>	
								<?php endif; ?>				
						</div>						
						<div>	
							<?php if( woo_j_conf('shipping_total') && $shipping ): ?>
								<div class="flex-row-end footer-shipping-row">
									<div class="wc-timeline-footer-subtotal-text">												
											<?php _e( 'Shipping', 'woo_j_cart' ) ?>:
									</div>										
									<div class="wc-footer-subtotal flex-row-end">
										<?php woo_j_price(  esc_html( round( $shipping['price'] , wc_get_price_decimals() ) ), ['wc-timeline-shipping-subtotal'] ) ?>	
									</div>
								</div>

							<?php endif; ?>
							<div class="flex-row-end">
									<div class="wc-timeline-footer-subtotal-text"><?php _e( 'Subtotal', 'woo_j_cart' ) ?>:</div>
									<div class="wc-footer-subtotal flex-row-end">
											<?php woo_j_price(  esc_html( round( $total, wc_get_price_decimals() ) ), ['wc-timeline-subtotal'] ) ?>									

											<?php if( $discount > 0 ): ?>

													<div class="discounted-total">
															( <?php woo_j_price( esc_html( $discount ) ) ?>&nbsp;<?php _e('discounted', 'woo_j_cart') ?>)
													</div>

											<?php endif; ?>
									</div>
							</div>	
						</div>
			</div>	

			<?php woo_j_render_template('/modal/footer/bottom_buttons'); ?>				
		
	<?php endif; ?> 

</div>
