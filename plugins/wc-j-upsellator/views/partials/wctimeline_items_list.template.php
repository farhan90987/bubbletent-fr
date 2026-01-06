<div class="wc-items-container flex-column-between">

	<div class="wc-timeline-cart-products">

	<?php foreach( $products as $item ): ?>	
			
				<article class='wc-timeline-product flex-row-between <?php echo $item->isChildOfCompositeOrBundle() ? 'composite-child' : '' ?> <?php echo $item->isUpsell() ? 'upsell-product': '' ?> <?php echo $item->isGift()  ? 'free-gift-product':'' ?>' data-key='<?php echo ( $item->isGift() ) ? '' : esc_attr( $item->key ) ?>'>	
			
					<div class='wc-timeline-product-data flex-row-start start'>	
							
							<a title="<?php echo esc_attr( $item->name ) ?>" href="<?php echo esc_attr( $item->url ) ?>" class='image'>	
								<img alt="Product loader" class="loader" src="<?php echo esc_attr( woo_j_env('img_path') ) ?>loader.svg">								
									
								<div class='qty flex-row-center'>
									<?php echo esc_html( $item->quantity_on_cart ) ?>
								</div>								

								<?php echo $item->image ?>	
								<!-- Labels -->
								<?php if( !$item->isChildOfCompositeOrBundle() ): ?>
									<?php if( $item->isUpsell() && !empty( woo_j_conf('label_upsell') ) ): ?>	

											<?php if( $item->hasDiscount() || !$item->hasDiscount() && woo_j_conf('upsells_label_no_discount') ): ?>
												
													<div class="special <?= woo_j_conf('upsells_label_no_discount') ?> discounted">												
															<?php echo wjc__( esc_html( woo_j_conf('label_upsell'))) ?>
													</div>

											<?php endif; ?>					

									<?php elseif( $item->isGift() && !empty( woo_j_conf('label_gift') ) ): ?>	

												<div class="special discounted">
													<?php echo wjc__( esc_html( woo_j_conf('label_gift'))) ?>
												</div>

									<?php elseif( $item->onSale() && !empty( woo_j_conf('label_on_sale') ) ): ?>	

											<div class="discounted">
												<?php echo wjc__( esc_html( woo_j_conf('label_on_sale'))) ?>											
											</div>		
											
									<?php endif; ?>	
								<?php endif; ?>	
								<!-- /Labels -->
							</a>						
							
							<div class='heading flex-column-start'>
							
									<div style="position:relative;width:100%;">
									
											<div class='wc-timeline-product-category <?php echo $item->isUpsell() ? 'saved': '' ?>'>
												<?php echo $item->heading  ?>
											</div>
											
											<a title="<?php echo esc_attr( $item->name ) ?>" href='<?php echo esc_attr(  $item->url ) ?>' class='wc-timeline-product-title'>
												<?php echo  $item->name ?>												
											</a>
										
											<?php if( $item->isGift() ): ?> 

												<div class="upsell-text">
													<?php echo $item->description ?>
												</div>

											<?php else: ?>

												<?php if( isset( $item->has_custom_qty_max ) && $item->has_custom_qty_max ): ?>

													<div class="out-of-stock">
														<?php _e( 'Sorry, maximum allowed quantity is', 'woo_j_cart' ) ?>&nbsp;<?php echo esc_html( $item->has_custom_qty_max ) ?>														
													</div>

												<?php else: ?>

													<div class="out-of-stock">
														<?php _e( 'Sorry, we have just', 'woo_j_cart' ) ?>&nbsp;<?php echo esc_html( $item->max_quantity ) ?>&nbsp;
														<?php _e( 'items in stock', 'woo_j_cart' ) ?>
													</div>

												<?php endif; ?>		
												
												<div class="product-meta-data">
													<?php echo $item->meta_data ?> 
												</div>

											<?php endif; ?>										
									</div>

									<?php if( $item->allowsQuantityChange()  ): ?>
									
											<div class='prodotto-carrello-qta-container flex-row-start'>
													<div class='wc-timeline-action quantity-down wc-timeline-qty qty-change flex-row-center'>&#8722;</div>
													<input type="number" min="0" max="<?php echo esc_attr( $item->max_quantity ) ?>" data-sku="<?php echo esc_attr( $item->key ) ?>" readonly class='btn-qty' value="<?php echo esc_attr( $item->quantity_on_cart ) ?>">
													<div class='wc-timeline-action quantity-up wc-timeline-qty  qty-change flex-row-center'>&#43;</div>										
											</div>

									<?php endif; ?>								
								
							</div>
					</div>
					
					<div class='options flex-column-center'>
										
						<?php if( !$item->isGift() && !$item->isChildOfCompositeOrBundle() ): ?>	

								<div class='wc-timeline-remove wc-timeline-action wc-timeline-remove-product'>&#10005;</div>

						<?php else: ?>

								<div></div>
								
						<?php endif; ?>
							
							<?php if( $item->hasDisplayedPrice() ): ?>

								<div class="flex-row-end wc-timeline-price-container">								
									
									<?php if( !$item->isFree() && $item->actual_price > 0 ): ?>										

											<?php if( ( $item->onSale() || $item->hasDiscount() ) && !$item->isParentOfCompositeOrBundle()  ): 								
												
												woo_j_price( $item->base_price, ['striked'] );
											
											endif;										

											woo_j_price( $item->actual_price ); 

									else: ?>
										
										<div class='wc-timeline-product-price'>
											<?php echo wjc__( esc_html( woo_j_conf('text_free_product') )) ?>						
										</div>

									<?php endif; ?>
									
								</div>

							<?php endif; ?>
							
					</div>						
			</article>

	<?php endforeach ?>	

	<?php if( count( $triggerable_gifts ) ): ?> 

		<?php foreach( $triggerable_gifts as $gift ): ?>

				<?php woo_j_render_template('/modal/triggerable_gift', ['gift' => $gift ]); ?>
				
		<?php endforeach; ?>

	<?php endif; ?>

	</div>

	<?php $upsells->render('modal'); ?>	

</div>