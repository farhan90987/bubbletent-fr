<?php

use FlexibleCouponsProVendor\WPDesk\Library\Marketing\RatePlugin\RateBox;

/**
 * @var RateBox $boxes
 */
$rate_box = $params['rate_box'] ?? false;
if ( ! $rate_box ) {
	return;
}
?>
<script id="fi_rate_box" type="text/template">
	<?php
	$wpdesk_url  = get_locale() === 'pl_PL' ? 'https://wpdesk.pl' : 'https://wpdesk.net';
	$review_link = 'https://wpdesk.link/fi-footer-review-link';
	echo $rate_box->render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$review_link,
		sprintf(
			// translator: %1$s icon,  %2$s open url tag, %3$s close url tag.
			__( 'Created with %1$s by Sailors from %2$sWP Desk%3$s - if you like Flexible Coupons PDF Coupons Pro rate us &rarr;', 'flexible-coupons-pro' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'<span class="love"><span class="dashicons dashicons-heart"></span></span>',
			'<a target="_blank" href="' . \esc_url( $wpdesk_url ) . '">',
			'</a>'
		)
	);
	?>
</script>
<script>
	(function ($) {
		let body_wrapper = $('#marketing-page-wrapper, #fiw-settings-footer');
		if (body_wrapper.length) {
			body_wrapper.append($('#fi_rate_box').html())
		}
	})(jQuery);
</script>
