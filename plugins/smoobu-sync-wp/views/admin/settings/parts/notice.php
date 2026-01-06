<?php
/**
 * Success/error notice in settings
 *
 * @package smoobu-calendar
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No skiddies please!' );
}

if ( ! empty( $message ) ) {
	if ( true === $success) {
		$notice_class = 'updated';
	} else {
		$notice_class = 'error';
	}
	?>
	<div class="<?php echo esc_attr( $notice_class ); ?> settings-error notice is-dismissible"> 
		<p>
			<strong>
				<?php echo esc_html( $message ); ?>
			</strong>
		</p>
		<button type="button" class="notice-dismiss">
			<span class="screen-reader-text">
				<?php esc_html_e( 'Dismiss this notice.', 'smoobu-calendar' ); ?>
			</span>
		</button>
	</div>
	<?php
}
