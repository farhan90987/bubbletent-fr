<div class="shipping-bar-plugin flex-column-center <?php echo woo_j_conf('currency_position') ? 'currency-before' : '' ?>">
		
		<div data-current=""
			<?php if( $goals ): 
				$count = 1;
				
				foreach( $goals as $goal ): ?>

					data-goal-limit-<?php echo $count ?>="<?php echo apply_filters( 'wjufw_shipping_bar_limit', $goal['limit'], $goal['limit'] ); ?>"
					data-goal-limit-real-<?php echo $count ?>="<?php echo  $goal['limit'] ?>"

				<?php $count++; 
					
				endforeach;
				
			endif; ?>
			
			class="wcjfw-shipping-bar <?php echo esc_attr( woo_j_shipping('shipping_bar_type') ) ?>">			
			<div class="shipping-progress-bar transition" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
			<div class="flex-row-center magic shipping-icon"><i class="<?php echo esc_attr( woo_j_shipping('shipping_icon') ) ?>"></i></div>
		</div>
		
		<div class="shipping-bar-text">	
		
		</div>			
</div>
