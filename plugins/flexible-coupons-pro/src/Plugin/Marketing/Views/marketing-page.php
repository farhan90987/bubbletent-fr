<?php


use FlexibleCouponsProVendor\WPDesk\Library\Marketing\Boxes\MarketingBoxes;

/**
 * @var MarketingBoxes $boxes
 */
$boxes = $params['boxes'] ?? false;
if ( ! $boxes ) {
	return;
}

$support_url = 'pl_PL' === get_locale() ? 'https://www.wpdesk.pl/support/' : 'https://www.wpdesk.net/support/';
?>
<div class="wrap">
	<div id="marketing-page-wrapper">
		<?php echo $boxes->get_boxes()->get_all(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<div class="marketing-buttons">
			<a class="button button-primary button-support confirm" data-confirm="confirm-support" href="#"><?php esc_html_e( 'Get support', 'flexible-coupons-pro' ); ?></a>
		</div>

		<div class="wpdesk-tooltip-shadow"></div>
		<div id="confirm-support" class="wpdesk-tooltip wpdesk-tooltip-confirm">
			<span class="close-modal close-modal-button"><span class="dashicons dashicons-no-alt"></span></span>
			<h3><?php esc_html_e( 'Before sending a message please:', 'flexible-coupons-pro' ); ?></strong></h3>
			<ul>
				<li><?php esc_html_e( 'Prepare the information about the version of WordPress, WooCommerce, and Flexible Invoices (preferably your system status from WooCommerce->Status)', 'flexible-coupons-pro' ); ?></li>
				<li><?php esc_html_e( 'Describe the issue you have', 'flexible-coupons-pro' ); ?></li>
				<li><?php esc_html_e( 'Attach any log files & printscreens of the issue', 'flexible-coupons-pro' ); ?></li>
			</ul>
			<div class="confirm-buttons">
				<a target="_blank" href="<?php echo esc_url( $support_url ); ?>" class="confirm-url"><?php esc_html_e( 'Ok, take me to support', 'flexible-coupons-pro' ); ?></a>
				<a href="#" class="close-confirm close-modal"><?php esc_html_e( 'No, I\'ll wait', 'flexible-coupons-pro' ); ?></a>
			</div>
		</div>
	</div>
</div>
