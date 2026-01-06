<?php
	global $wpdb;
	$list_event_id = $wpdb->get_results( "SELECT ID, post_title FROM wp_posts WHERE post_type = 'ajde_events' AND post_status = 'publish'" );
	$list_event    = array();
foreach ( $list_event_id as $key => $event_id ) {
	$event          = get_post_meta( $event_id->ID );
	$event['title'] = $event_id->post_title;
	$list_event[]   = $event;
}
?>
<table id="shrief-table">
   <?php
	foreach ( $list_event as $key => $event ) :
		$start_time = eventon_get_formatted_time( $event['evcal_srow'][0] );
		$end_time   = eventon_get_formatted_time( $event['evcal_erow'][0] );
		?>
	<tr>
	   <td>
		  <p><span style="font-weight: bold; font-size: 18px;"><?php echo esc_html( $start_time['d'] ); ?></span> - <span><?php echo esc_html( $end_time['d'] ); ?></span></p>
		  <p><?php echo esc_html( strtoupper( $start_time['M'] ) ); ?></p>
	   </td>
	   <td><?php echo esc_html( $event['title'] ); ?></td>
	</tr>
	 <?php endforeach; ?>
</table>
