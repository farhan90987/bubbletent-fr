<?php


$exclude_regions = array(284, 274);

$region_id = $args['id'];
$region_name = $args['name'];
$region_url	= $args['url'];
$region_img = $args['image'];

//if (!in_array($region_id, $exclude_regions)) { ?>

<!-- 	<div class="region-item" style="background-image:url(<?php //echo $region_img; ?>);">
		<a href="<?php //echo esc_url($region_url); ?>">
			<div><?php //esc_html_e('Bubble Tent', 'listeo_core'); ?></div>
			<h4><?php //esc_html_e($region_name, 'listeo_core'); ?></h4>
		</a>
	</div> -->

	<div class="region-item" style="background-image:url(<?php echo $region_img; ?>);">
		<a href="<?php echo esc_url($region_url); ?>">
			<div class="region-item-icon"><svg width="35" height="35" viewBox="0 0 35 35" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="0.5" y="0.5" width="34" height="34" rx="17" stroke="white"/><path d="M13.125 21.875L21.875 13.125M21.875 13.125H13.125M21.875 13.125V21.875" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
			<div><?php esc_html_e('Bubble Tent', 'listeo_core'); ?></div>
			<h4><?php esc_html_e($region_name, 'listeo_core'); ?></h4>
		</a>
	</div>

<?php //}