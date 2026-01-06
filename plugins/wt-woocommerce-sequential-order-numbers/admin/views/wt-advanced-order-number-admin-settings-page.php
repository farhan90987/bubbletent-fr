<?php 
$date_frmt_tooltip= esc_html__('Click to select the date format','wt-woocommerce-sequential-order-numbers');?>
<div class="wt_seq_num_frmt_hlp wt_seq_num_popup">
	<div class="wt_seq_num_popup_hd">
		<span style="line-height:40px;" class="dashicons dashicons-calendar-alt"></span> <?php esc_html_e('Date formats','wt-woocommerce-sequential-order-numbers');?>
		<div class="wt_seq_num_popup_close">X</div>
	</div>
	<div class="wt_seq_num_popup_body">
		
		<p style="text-align:left; margin-bottom:10px; margin-top: -10px;">
			<?php esc_html_e('Pick one or more date formats from predefined formats listed below:','wt-woocommerce-sequential-order-numbers');?>
		</p>
		<table class="wp-list-table widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e('Format','wt-woocommerce-sequential-order-numbers');?></th><th><?php esc_html_e('Output','wt-woocommerce-sequential-order-numbers');?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><a class="wt_seq_num_frmt_append_btn" title="<?php echo esc_attr($date_frmt_tooltip); ?>">[F]</a></td>
					<td><?php
					 // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					 echo esc_html(date('F')); 
					 ?></td>
				</tr>
				<tr>
					<td><a class="wt_seq_num_frmt_append_btn" title="<?php echo esc_attr($date_frmt_tooltip); ?>">[dS]</a></td>
					<td><?php
					 // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					 echo esc_html(date('dS')); 
					 ?></td>
				</tr>
				<tr>
					<td><a class="wt_seq_num_frmt_append_btn" title="<?php echo esc_attr($date_frmt_tooltip); ?>">[M]</a></td>
					<td><?php
					 // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					 echo esc_html(date('M')); 
					 ?></td>
				</tr>
				<tr>
					<td><a class="wt_seq_num_frmt_append_btn" title="<?php echo esc_attr($date_frmt_tooltip); ?>">[m]</a></td>
					<td><?php
					 // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					 echo esc_html(date('m')); 
					 ?></td>
				</tr>
				<tr>
					<td><a class="wt_seq_num_frmt_append_btn" title="<?php echo esc_attr($date_frmt_tooltip); ?>">[d]</a></td>
					<td><?php
					 // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					 echo esc_html(date('d')); 
					 ?></td>
				</tr>
				<tr>
					<td><a class="wt_seq_num_frmt_append_btn" title="<?php echo esc_attr($date_frmt_tooltip); ?>">[D]</a></td>
					<td><?php
					 // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					 echo esc_html(date('D')); 
					 ?></td>
				</tr>
				<tr>
					<td><a class="wt_seq_num_frmt_append_btn" title="<?php echo esc_attr($date_frmt_tooltip); ?>">[y]</a></td>
					<td><?php
					 // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					 echo esc_html(date('y')); 
					 ?></td>
				</tr>
				<tr>
					<td><a class="wt_seq_num_frmt_append_btn" title="<?php echo esc_attr($date_frmt_tooltip); ?>">[Y]</a></td>
					<td><?php
					 // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					 echo esc_html(date('Y')); 
					 ?></td>
				</tr>
				<tr>
					<td><a class="wt_seq_num_frmt_append_btn" title="<?php echo esc_attr($date_frmt_tooltip); ?>">[d/m/y]</a></td>
					<td><?php
					 // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					 echo esc_html(date('d/m/y')); 
					 ?></td>
				</tr>
				<tr>
					<td><a class="wt_seq_num_frmt_append_btn" title="<?php echo esc_attr($date_frmt_tooltip); ?>">[d-m-Y]</a></td>
					<td><?php
					 // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					 echo esc_html(date('d-m-Y')); 
					 ?></td>
				</tr>
			</tbody>
		</table>
		<p style="text-align:left; margin-bottom:2px;">
			<?php esc_html_e('For e.g, Input [y]/[m]/[d] to get the order date format as ','wt-woocommerce-sequential-order-numbers');
			 // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			echo esc_html(date('y/m/d'));
			?>
		</p>
	</div>
</div>
<style type="text/css">
/* popup */
.wt_seq_overlay{ position:fixed; z-index:100000000; width:100%; height:100%; background-color:rgba(0,0,0,.5); left:0px; top:0px; display:none;}
.wt_seq_num_popup{position:fixed; z-index:100000001; background:#fff; border:solid 1px #eee; text-align:center; box-shadow:0px 2px 5px #333; left:50%; display:none;}
.wt_seq_num_popup_hd{display:inline-block; width:100%; box-sizing:border-box; font-weight:bold; background-color:#f3f3f3; height:40px; text-align:left; line-height:40px; padding:0px 20px;}
.wt_seq_num_popup_close{float:right; width:40px; height:40px; text-align:right; line-height:40px; cursor:pointer;}
.wt_seq_num_popup_footer{width:100%; text-align:right; margin-top:10px;}
.wt_seq_num_frmt_hlp_btn{ cursor:pointer; }
.wt_seq_num_frmt_hlp table thead th{ font-weight:bold; text-align:left; }
.wt_seq_num_frmt_hlp table tbody td{ text-align:left; }
.wt_seq_num_frmt_hlp .wt_seq_num_popup_body{min-width:300px; padding:20px;}
.wt_seq_num_frmt_append_btn{ cursor:pointer; }
</style>