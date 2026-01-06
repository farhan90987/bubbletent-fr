<?php

namespace WPDesk\FCS\Email;

use FlexibleCouponsProVendor\Psr\Log\LoggerInterface;
use FCSVendor\WPDesk_Plugin_Info;
use WPDesk\FCS\Email\SendingRecipientEmail;
use WPDesk\FlexibleCouponsPro\Email\BuyerEmail;
use FCSVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\FlexibleCouponsPro\Email\RecipientEmail;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\PDF\Download;
use FlexibleCouponsProVendor\WPDesk\Persistence\PersistentContainer;
use WPDesk\FCS\Repository\EmailTemplateRepository;

/**
 * Register our custom emails as Woocommerce emails.
 */
class RegisterEmails implements Hookable {

	private const HOOK_PRIORITY = 20;

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
	 * @var array
	 */
	private $shortcodes;

	private EmailTemplateRepository $email_template_repository;

	public function __construct(
		WPDesk_Plugin_Info $plugin,
		LoggerInterface $logger,
		PersistentContainer $settings,
		Download $download,
		array $shortcodes,
		EmailTemplateRepository $email_template_repository
	) {
		$this->plugin_info               = $plugin;
		$this->logger                    = $logger;
		$this->settings                  = $settings;
		$this->download                  = $download;
		$this->shortcodes                = $shortcodes;
		$this->email_template_repository = $email_template_repository;
	}

	/**
	 * Hooks
	 */
	public function hooks() {
		add_filter( 'woocommerce_email_classes', [ $this, 'register_emails' ], self::HOOK_PRIORITY );
	}

	private function get_template_path(): string {
		return $this->plugin_info->get_plugin_dir() . '/src/Views/';
	}

	private function can_attach_pdf(): bool {
		return 'yes' === $this->settings->get_fallback( 'attach_coupon', 'no' );
	}

	/**
	 * Register emails in WooCommerce.
	 *
	 * @param array $emails WC_Email.
	 */
	public function register_emails( array $emails ): array {
		if ( isset( $emails[ RecipientEmail::SLUG ] ) ) {
			unset( $emails[ RecipientEmail::SLUG ] );
		}
		if ( isset( $emails[ BuyerEmail::SLUG ] ) ) {
			unset( $emails[ BuyerEmail::SLUG ] );
		}

		$emails[ SendingRecipientEmail::SLUG ] = new SendingRecipientEmail(
			$this->get_template_path(),
			$this->can_attach_pdf(),
			$this->logger,
			$this->download,
			$this->settings,
			$this->shortcodes,
			$this->email_template_repository
		);

		$emails[ SendingBuyerEmail::SLUG ] = new SendingBuyerEmail(
			$this->get_template_path(),
			$this->can_attach_pdf(),
			$this->logger,
			$this->download,
			$this->settings,
			$this->shortcodes,
			$this->email_template_repository
		);

		return $emails;
	}
}
