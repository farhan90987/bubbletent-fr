<?php
/**
 * FAQ page question loop
 *
 * @package smoobu-calendar
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No skiddies please!' );
}
?>
<div class="accordion-body">
	<h2 class="title">
		<button type="button" aria-expanded="false" class="">
			<span aria-hidden="true">
				<svg class="arrow-down" width="24px" height="24px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true" focusable="false">
					<g><path fill="none" d="M0,0h24v24H0V0z"></path></g>
					<g><path d="M7.41,8.59L12,13.17l4.59-4.58L18,10l-6,6l-6-6L7.41,8.59z"></path></g>
				</svg>
				<svg class="arrow-up" width="24px" height="24px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true" focusable="false">
					<g><path fill="none" d="M0,0h24v24H0V0z"></path></g>
					<g><path d="M12,8l-6,6l1.41,1.41L12,10.83l4.59,4.58L18,14L12,8z"></path></g>
				</svg>
			</span>
			<?php echo esc_html( $question ); ?>
		</button>
	</h2>
	<div class="content">
		<?php echo wp_kses_post( $answer ); ?>
	</div>
</div>
