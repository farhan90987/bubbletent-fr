<?php

namespace WPDesk\FlexibleCouponsPro\Marketing;

use FlexibleCouponsProVendor\WPDesk\PluginBuilder\Plugin\Hookable;

/**
 * Deprecated Class - functionality moved to wp-coupons-core.
 *
 * @deprecated Deprecated since versio 1.11.4
 */
class SupportLinks implements Hookable {

	const COUPON_LISTING_PAGE = 'edit-wpdesk-coupons';
	const COUPON_EDIT_PAGE    = 'wpdesk-coupons';
	const COUPON_ADD_ACTION   = 'add';

	public function hooks() {
		add_action( 'admin_footer', [ $this, 'add_support_link_on_invoice_listing' ] );
		add_action( 'admin_footer', [ $this, 'add_support_link_on_invoice_add' ] );
		add_action( 'admin_footer', [ $this, 'add_support_link_on_invoice_edit' ] );
	}

	public function add_support_link_on_invoice_listing() {
		$screen = get_current_screen();
		if ( isset( $screen->id ) && $screen->id === self::COUPON_LISTING_PAGE ) {
			$url = 'https://wpdesk.net/docs/flexible-coupons-pro/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-docs-link&utm_content=edit-template-list#Coupon_templates_list';
			if ( get_locale() === 'pl_PL' ) {
				$url = 'https://www.wpdesk.pl/docs/flexible-coupons-pro/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-docs-link&utm_content=edit-template-list#lista-szablonow-kuponow';
			}
			?>
			<script>
				(function ($) {
					$('.wp-header-end').before('<div class="support-url-wrapper"><a target="_blank" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'See our documentation &rarr;', 'flexible-coupons-pro' ); ?></a></div>');
				})(jQuery);
			</script>
			<?php
		}
	}

	public function add_support_link_on_invoice_add() {
		$screen = get_current_screen();
		if ( ( isset( $screen->id ) && $screen->id === self::COUPON_EDIT_PAGE ) && ( isset( $screen->action ) && $screen->action === self::COUPON_ADD_ACTION ) ) {
			$url = 'https://wpdesk.net/docs/flexible-coupons-pro/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-docs-link&utm_content=edit-template-add-new#New_coupon_template';
			if ( get_locale() === 'pl_PL' ) {
				$url = 'https://www.wpdesk.pl/docs/flexible-coupons-pro/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-docs-link&utm_content=edit-template-add-new#tworzenie-szablonu-kuponu';
			}
			?>
			<script>
				(function ($) {
					$('.wp-header-end').before('<div class="support-url-wrapper"><a target="_blank" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'See our documentation &rarr;', 'flexible-coupons-pro' ); ?></a></div>');
				})(jQuery);
			</script>
			<?php
		}
	}

	public function add_support_link_on_invoice_edit() {
		$screen = get_current_screen();
		if ( isset( $screen->id ) && $screen->id === self::COUPON_EDIT_PAGE && empty( $screen->action ) ) {
			$url = 'https://wpdesk.net/docs/flexible-coupons-pro/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-docs-link&utm_content=edit-template-add-new#New_coupon_template';
			if ( get_locale() === 'pl_PL' ) {
				$url = 'https://www.wpdesk.pl/docs/flexible-coupons-pro/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-docs-link&utm_content=edit-template-add-new#tworzenie-szablonu-kuponu';
			}
			?>
			<script>
				(function ($) {
					$('.wp-header-end').before('<div class="support-url-wrapper"><a target="_blank" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'See our documentation &rarr;', 'flexible-coupons-pro' ); ?></a></div>');
				})(jQuery);
			</script>
			<?php
		}
	}
}
