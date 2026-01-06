<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
	use YayMail\Page\Source\CustomPostType;
	$postID             = CustomPostType::postIDByTemplate( $this->template );
	$emailTextLinkColor = get_post_meta( $postID, '_yaymail_email_textLinkColor_settings', true ) ? get_post_meta( $postID, '_yaymail_email_textLinkColor_settings', true ) : '#7f54b3';
	$borderColor        = isset( $atts['bordercolor'] ) && $atts['bordercolor'] ? 'border-color:' . html_entity_decode( $atts['bordercolor'], ENT_QUOTES, 'UTF-8' ) : 'border-color:inherit';
	$textColor          = isset( $atts['textcolor'] ) && $atts['textcolor'] ? 'color:' . html_entity_decode( $atts['textcolor'], ENT_QUOTES, 'UTF-8' ) : 'color:inherit';
	$fontFamily         = isset( $atts['fontfamily'] ) && $atts['fontfamily'] ? 'font-family:' . html_entity_decode( $atts['fontfamily'], ENT_QUOTES, 'UTF-8' ) : 'font-family:inherit';
	$titleColor         = isset( $atts['titlecolor'] ) && $atts['titlecolor'] ? 'color:' . html_entity_decode( $atts['titlecolor'], ENT_QUOTES, 'UTF-8' ) : 'color:inherit';
	$text_align         = is_rtl() ? 'right' : 'left';
?>
<?php if ( ! empty( $WooCommerceEventsOrderTickets ) ) : ?>
	<?php $x = 0; ?>
	<?php foreach ( $WooCommerceEventsOrderTickets as $event ) : ?>
		<?php foreach ( $event['tickets'] as $ticket ) : ?> 
			<?php
			$WooCommerceEventsEventDetailsNewOrder    = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsEventDetailsNewOrder', true );
			$WooCommerceEventsDisplayAttendeeNewOrder = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsDisplayAttendeeNewOrder', true );
			$WooCommerceEventsDisplayBookingsNewOrder = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsDisplayBookingsNewOrder', true );
			$WooCommerceEventsDisplaySeatingsNewOrder = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsDisplaySeatingsNewOrder', true );
			$WooCommerceEventsDisplayCustAttNewOrder  = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsDisplayCustAttNewOrder', true );
			?>
			<?php if ( 'on' == $WooCommerceEventsEventDetailsNewOrder ) : ?>
				<?php if ( ! empty( $event['WooCommerceEventsName'] ) ) : ?>
			<strong><a style="color: <?php echo esc_attr( $emailTextLinkColor ); ?>" href="<?php echo esc_url( $event['WooCommerceEventsURL'] ); ?>"><?php echo esc_html( $event['WooCommerceEventsName'] ); ?></a></strong><br />
			<?php endif; ?>
				<?php if ( 'single' == $event['WooCommerceEventsType'] ) : ?>
					<?php if ( ! empty( $event['WooCommerceEventsDate'] ) ) : ?>
					<strong><?php esc_html_e( 'Date', 'woocommerce-events' ); ?></strong>:<?php echo esc_html( $event['WooCommerceEventsDate'] ); ?><br />
				<?php endif; ?>
					<?php if ( ! empty( $event['WooCommerceEventsStartTime'] ) ) : ?>
					<strong><?php esc_html_e( 'Start time', 'woocommerce-events' ); ?></strong>: <?php echo esc_html( $event['WooCommerceEventsStartTime'] ); ?><br />
				<?php endif; ?>
					<?php if ( ! empty( $event['WooCommerceEventsEndTime'] ) ) : ?>
					<strong><?php esc_html_e( 'End time', 'woocommerce-events' ); ?></strong>: <?php echo esc_html( $event['WooCommerceEventsEndTime'] ); ?><br />
				<?php endif; ?>
			<?php endif; ?>
				<?php if ( 'sequential' == $event['WooCommerceEventsType'] ) : ?>
					<?php if ( ! empty( $event['WooCommerceEventsDate'] ) ) : ?>
					<strong><?php esc_html_e( 'Date', 'woocommerce-events' ); ?></strong>:<?php echo esc_html( $event['WooCommerceEventsDate'] ); ?><br />
				<?php endif; ?>
					<?php if ( ! empty( $event['WooCommerceEventsEndDate'] ) ) : ?>
					<strong><?php esc_html_e( 'End date', 'woocommerce-events' ); ?></strong>:<?php echo esc_html( $event['WooCommerceEventsEndDate'] ); ?><br />
				<?php endif; ?>    
					<?php if ( ! empty( $event['WooCommerceEventsStartTime'] ) ) : ?>
					<strong><?php esc_html_e( 'Start time', 'woocommerce-events' ); ?></strong>: <?php echo esc_html( $event['WooCommerceEventsStartTime'] ); ?><br />
				<?php endif; ?>
					<?php if ( ! empty( $event['WooCommerceEventsEndTime'] ) ) : ?>
					<strong><?php esc_html_e( 'End time', 'woocommerce-events' ); ?></strong>: <?php echo esc_html( $event['WooCommerceEventsEndTime'] ); ?><br />
				<?php endif; ?>
			<?php endif; ?>  
				<?php if ( 'select' == $event['WooCommerceEventsType'] ) : ?>
					<?php $y = 1; ?>    
					<?php if ( ! empty( $event['WooCommerceEventsSelectDate'] ) ) : ?>
						<?php foreach ( $event['WooCommerceEventsSelectDate'] as $date ) : ?>
					<strong><?php esc_html_e( 'Day ', 'woocommerce-events' ); ?><?php echo esc_html( $y ); ?></strong>: <?php echo esc_html( $date ); ?><br />
							<?php $y++; ?>
					<?php endforeach; ?>
						<?php if ( ! empty( $event['WooCommerceEventsStartTime'] ) ) : ?>
						<strong><?php esc_html_e( 'Start time', 'woocommerce-events' ); ?></strong>: <?php echo esc_html( $event['WooCommerceEventsStartTime'] ); ?><br />
					<?php endif; ?>
						<?php if ( ! empty( $event['WooCommerceEventsEndTime'] ) ) : ?>
						<strong><?php esc_html_e( 'End time', 'woocommerce-events' ); ?></strong>: <?php echo esc_html( $event['WooCommerceEventsEndTime'] ); ?><br />
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>        
		<?php endif; ?>
			<?php if ( 'on' == $WooCommerceEventsDisplayAttendeeNewOrder ) : ?>
			<strong><?php esc_html_e( 'Name', 'woocommerce-events' ); ?></strong>: <?php echo empty( $ticket['WooCommerceEventsAttendeeName'] ) ? esc_html( $ticket['WooCommerceEventsPurchaserFirstName'] ) . ' ' . esc_html( $ticket['WooCommerceEventsPurchaserLastName'] ) : esc_html( $ticket['WooCommerceEventsAttendeeName'] ) . ' ' . esc_html( $ticket['WooCommerceEventsAttendeeLastName'] ); ?><br />
			<strong><?php esc_html_e( 'Email', 'woocommerce-events' ); ?></strong>: <?php echo empty( $ticket['WooCommerceEventsAttendeeEmail'] ) ? esc_html( $ticket['WooCommerceEventsPurchaserEmail'] ) : esc_html( $ticket['WooCommerceEventsAttendeeEmail'] ); ?><br />
				<?php if ( ! empty( $ticket['WooCommerceEventsAttendeeTelephone'] ) ) : ?>
				<strong><?php esc_html_e( 'Telephone', 'woocommerce-events' ); ?></strong>: <?php echo esc_html( $ticket['WooCommerceEventsAttendeeTelephone'] ); ?><br />
			<?php endif; ?>
				<?php if ( ! empty( $ticket['WooCommerceEventsAttendeeCompany'] ) ) : ?>
				<strong><?php esc_html_e( 'Company', 'woocommerce-events' ); ?></strong>: <?php echo esc_html( $ticket['WooCommerceEventsAttendeeCompany'] ); ?><br />
			<?php endif; ?>
				<?php if ( ! empty( $ticket['WooCommerceEventsAttendeeDesignation'] ) ) : ?>
				<strong><?php esc_html_e( 'Designation', 'woocommerce-events' ); ?></strong>: <?php echo esc_html( $ticket['WooCommerceEventsAttendeeDesignation'] ); ?><br />
			<?php endif; ?>
		<?php endif; ?>    
			<?php if ( 'on' == $WooCommerceEventsDisplayBookingsNewOrder && ! empty( $ticket['WooCommerceEventsBookingOptions']['slot'] ) ) : ?>
				<strong>
				<?php
				/* translators:  booking options*/
					echo sprintf( esc_html__( 'Booking %s', 'woocommerce-events' ), esc_html( $ticket['WooCommerceEventsBookingOptions']['slot_term'] ) );
				?>
					</strong>: <?php echo esc_html( $ticket['WooCommerceEventsBookingOptions']['slot'] ); ?><br />
		<strong>
				<?php
				/* translators:  booking options*/
				echo sprintf( esc_html__( 'Booking %s', 'woocommerce-events' ), esc_html( $ticket['WooCommerceEventsBookingOptions']['date_term'] ) );
				?>
		</strong>: <?php echo esc_html( $ticket['WooCommerceEventsBookingOptions']['date'] ); ?><br />
		<?php endif; ?>
			<?php if ( 'on' == $WooCommerceEventsDisplaySeatingsNewOrder && ! empty( $ticket['WooCommerceEventsSeatingFields'] ) ) : ?>
				<?php $WooCommerceEventsSeatingFieldsKeys = array_keys( $ticket['WooCommerceEventsSeatingFields'] ); ?>
			<strong><?php esc_html_e( 'Row', 'woocommerce-events' ); ?></strong>: <?php echo esc_html( $ticket['WooCommerceEventsSeatingFields'][ $WooCommerceEventsSeatingFieldsKeys[0] ] ); ?> <br />
			<strong><?php esc_html_e( 'Seat', 'woocommerce-events' ); ?></strong>: <?php echo esc_html( $ticket['WooCommerceEventsSeatingFields'][ $WooCommerceEventsSeatingFieldsKeys[1] ] ); ?><br />
		<?php endif; ?>
			<?php if ( 'on' == $WooCommerceEventsDisplayCustAttNewOrder && ! empty( $ticket['WooCommerceEventsCustomAttendeeFields'] ) ) : ?>
				<?php foreach ( $ticket['WooCommerceEventsCustomAttendeeFields'] as $key => $field ) : ?>
				<strong><?php echo esc_html( $field['field'][ $key . '_label' ] ); ?>:</strong> <?php echo esc_html( $field['value'] ); ?><br />    
			<?php endforeach; ?>
		<?php endif; ?>    
		<br />
			<?php $x++; ?>   
	<?php endforeach; ?>
	<?php endforeach; ?>
<?php endif; ?>
