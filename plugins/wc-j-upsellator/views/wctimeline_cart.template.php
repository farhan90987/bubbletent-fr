<!--- Wc J modal -->
<div class='wc-timeline-modal-cover'></div>

<div class='wc-timeline-modal-cover-container <?php echo woo_j_styles('theme') ?>
											  <?php echo woo_j_conf('modal_theme') ?? 'standard' ?> 											  	
											  <?php echo $dynamic_bar_visible ? 'has-free-shipping' : '' ?>'>

		<?php woo_j_render_template('/modal/header', ['logo' => $logo ]); ?>	
		<?php woo_j_render_template('/modal/notices'); ?>	
		
		<?php

			if( $dynamic_bar_visible )  
				woo_j_render_view('/shipping_bar/wctimeline_shipping_bar', 
							[
								'goals' => woo_j_shipping('goals'),
								'goals_count' => count( woo_j_shipping('goals') ) ?? 0
							] 
				);	

		?>

		<div data-notice-timeout="<?php echo woo_j_conf('modal_cart_notices_timeout') ?? 7 ?>" class='wc-timeline-inner-container flex-column-center'>
				<!--- Wc J items -->				
				<?php $items_list->render() ?>
				<!--- /Wc J items -->				
				<!--- Wc J footer -->
				<?php $footer->render() ?>
				<!--- /Wc J footer -->
		</div>
</div>		
<!--- /Wc J modal -->
<!--- Wc J item counter -->
<?php $count_button->render() ?>
<!--- /Wc J item counter -->