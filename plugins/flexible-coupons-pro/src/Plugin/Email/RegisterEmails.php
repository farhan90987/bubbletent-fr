<?php
/**
 * Email: Register email templates.
 *
 * @package WPDesk\FlexibleCouponsPro
 */

namespace WPDesk\FlexibleCouponsPro\Email;

use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\PDF\Download;
use FlexibleCouponsProVendor\WPDesk\Persistence\PersistentContainer;
use FlexibleCouponsProVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use FlexibleCouponsProVendor\WPDesk_Plugin_Info;
use FlexibleCouponsProVendor\Psr\Log\LoggerInterface;

/**
 * Register email templates and pass them to the WooCommerce filter.
 *
 * @package WPDesk\WooCommerceWFirma\Email
 */
class RegisterEmails implements Hookable {

	const HOOK_PRIORITY = 11;

	/**
	 * @var WPDesk_Plugin_Info
	 */
	private $plugin_info;

	/**
	 * @var PersistentContainer
	 */
	private $settings;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var Download
	 */
	private $download;

	/**
	 * @param WPDesk_Plugin_Info  $plugin
	 * @param LoggerInterface     $logger
	 * @param PersistentContainer $settings
	 * @param Download            $download
	 */
	public function __construct( WPDesk_Plugin_Info $plugin, LoggerInterface $logger, PersistentContainer $settings, Download $download ) {
		$this->plugin_info = $plugin;
		$this->logger      = $logger;
		$this->settings    = $settings;
		$this->download    = $download;
	}

	/**
	 * Hooks
	 */
	public function hooks() {
		add_filter( 'woocommerce_email_classes', [ $this, 'register_emails' ], self::HOOK_PRIORITY );
	}

	/**
	 * @return string
	 */
	private function get_template_path(): string {
		return $this->plugin_info->get_plugin_dir() . '/src/Views/';
	}

	/**
	 * Register emails in WooCommerce.
	 *
	 * @param array $emails Emails.
	 *
	 * @return array
	 */
	public function register_emails( array $emails ): array {
		$can_attach_pdf_to_email        = 'yes' === $this->settings->get_fallback( 'attach_coupon', 'no' );
		$emails[ BuyerEmail::SLUG ]     = new BuyerEmail( $this->get_template_path(), $can_attach_pdf_to_email, $this->logger, $this->download );
		$emails[ RecipientEmail::SLUG ] = new RecipientEmail( $this->get_template_path(), $can_attach_pdf_to_email, $this->logger, $this->download );

		return $emails;
	}
}
