<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}

$no_icon='<span class="dashicons dashicons-dismiss" style="color:#ea1515;"></span>&nbsp;';
$yes_icon='<span class="dashicons dashicons-yes-alt" style="color:#18c01d;"></span>&nbsp;';
$webtoffee_logo='<img src="'.WT_SEQUENCIAL_ORDNUMBER_URL.'assets/images/wt_logo.png" style="" />&nbsp;';
global $wp_version;
if(version_compare($wp_version, '5.2.0')<0)
{
 	$yes_icon='<img src="'.WT_SEQUENCIAL_ORDNUMBER_URL.'assets/images/tick_icon_green.png" style="float:left;" />&nbsp;';
}

/**
*	Array format
*	First 	: Feature
*	Second 	: Basic availability. Supports: Boolean, Array(Boolean and String values), String
*	Pro 	: Pro availability. Supports: Boolean, Array(Boolean and String values), String
*/
$comparison_data=array(

	array(
		esc_html__('Add order number prefix', 'wt-woocommerce-sequential-order-numbers'),
		true,
		true,
	),
	array(
		esc_html__('Set order number length', 'wt-woocommerce-sequential-order-numbers'),
		true,
		true,
	),
	array(
		esc_html__('Dynamic preview of order numbers', 'wt-woocommerce-sequential-order-numbers'),
		true,
		true,
	),
	array(
		esc_html__('Custom starting number for orders', 'wt-woocommerce-sequential-order-numbers'),
		true,
		true,
	),
	array(
		esc_html__('Keep existing order numbers', 'wt-woocommerce-sequential-order-numbers'),
		true,
		true,
	),
	array(
		esc_html__('Easy custom order number search', 'wt-woocommerce-sequential-order-numbers'),
		true,
		true,
	),
	array(
		esc_html__('Order tracking', 'wt-woocommerce-sequential-order-numbers'),
		true,
		true,
	),
	array(
		esc_html__('Custom suffix for order numbers', 'wt-woocommerce-sequential-order-numbers'),
		false,
		true,
	),
	array(
		esc_html__('Add order date as suffix', 'wt-woocommerce-sequential-order-numbers'),
		false,
		true,
	),
	array(
		esc_html__('Auto-reset order numbers', 'wt-woocommerce-sequential-order-numbers'),
		false,
		true,
	),
	array(
		esc_html__('Separate order number sequence for free orders', 'wt-woocommerce-sequential-order-numbers'),
		false,
		true,
	),
	array(
		esc_html__('Custom increment for order sequence', 'wt-woocommerce-sequential-order-numbers'),
		false,
		true,
	),
	array(
		esc_html__('Premium suppport', 'wt-woocommerce-sequential-order-numbers'),
		false,
		true,
	),
	array(
		esc_html__('Order number templates', 'wt-woocommerce-sequential-order-numbers'),
		esc_html__('Limited', 'wt-woocommerce-sequential-order-numbers'),
		true,
	),
);
function wt_seq_free_vs_pro_column_vl($vl, $yes_icon, $no_icon)
{
	if(is_array($vl))
	{
		foreach ($vl as $value)
		{
			if(is_bool($value))
			{
				echo wp_kses_post($value ? $yes_icon : $no_icon);
			}else
			{
				//string only
				echo esc_html($value);
			}
		}
	}else
	{
		if(is_bool($vl))
		{
			echo wp_kses_post($vl ? $yes_icon : $no_icon);
		}else
		{
			//string only
			echo esc_html($vl);
		}
	}
}
?>
<div class="wt_seq_free_vs_pro">
	<table class="wt_sequential_freevs_pro">
	<tr>
		<td><?php esc_html_e('FEATURES', 'wt-woocommerce-sequential-order-numbers'); ?></td>
		<td><?php esc_html_e('FREE', 'wt-woocommerce-sequential-order-numbers'); ?></td>
		<td><?php esc_html_e('PREMIUM', 'wt-woocommerce-sequential-order-numbers'); ?></td>
	</tr>
	<?php
	foreach ($comparison_data as $val_arr)
	{
		?>
		<tr>
			<td><?php echo esc_html($val_arr[0]);?></td>
			<td>
				<?php
				wt_seq_free_vs_pro_column_vl($val_arr[1], $yes_icon, $no_icon);
				?>
			</td>
			<td>
				<?php
				wt_seq_free_vs_pro_column_vl($val_arr[2], $yes_icon, $no_icon);
				?>
			</td>
		</tr>
		<?php
	}
	?>
</table>
</div>
<style type="text/css">
	.wt_sequential_freevs_pro{ width:100%; border-collapse:collapse; border-spacing:0px; background-color: #ffffff; }
	.wt_sequential_freevs_pro td{ border:solid 1px #e7eaef; text-align:center; vertical-align:middle; padding:15px 20px;}
	.wt_sequential_freevs_pro tr td:first-child{ background:#f8f9fa; text-align:left;}
	.wt_sequential_freevs_pro tr:first-child td{ font-weight:bold; }
</style>
<script type="text/javascript">
	//hide save settings button in license section
	jQuery(document).ready(function($){
 		$('p.submit').hide();
	});
</script>