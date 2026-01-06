<?php

namespace WPDesk\FCS\Email;

use FlexibleCouponsProVendor\Psr\Log\LoggerInterface;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\PDF\Download;
use FlexibleCouponsProVendor\WPDesk\Persistence\PersistentContainer;
use WPDesk\FCS\Repository\EmailTemplateRepository;

/**
 * Email class responsible for sending coupon to buyer.
 */
class SendingBuyerEmail extends SendingEmail {

	public const SLUG = 'SendingBuyerEmail';


	public function __construct(
		string $template_path,
		bool $can_attach_pdf,
		LoggerInterface $logger,
		Download $download,
		PersistentContainer $settings,
		array $shortcodes,
		EmailTemplateRepository $email_template_repository
	) {
		parent::__construct(
			$template_path,
			$can_attach_pdf,
			$logger,
			$download,
			$settings,
			$shortcodes,
			$email_template_repository
		);
		$this->id            = 'coupon_sending_buyer_email';
		$this->title         = __( 'Coupon for buyer (Advanced Sending for Flexible Coupons Pro)', 'flexible-coupons-sending' );
		$this->description   = __( 'This message goes to the coupon buyer.', 'flexible-coupons-sending' );
		$this->heading       = __( 'Coupon', 'flexible-coupons-sending' );
		$this->template_html = 'emails/coupon-buyer.php';
	}

	/**
	 * Setup and get email recipients.
	 */
	public function get_recipient(): string {
		if ( ! $this->get_email_template()->is_enabled() ) {
			return '';
		}

		$this->recipient = $this->object->get_billing_email();

		// run it through the WC_Email filter.
		return parent::get_recipient();
	}

	/**
	 * Slug for woocommerce registration.
	 */
	public function get_slug(): string {
		return self::SLUG;
	}
}
