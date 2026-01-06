<?php

namespace WPDesk\FCS\Email;

use FlexibleCouponsProVendor\Psr\Log\LoggerInterface;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\PDF\Download;
use FlexibleCouponsProVendor\WPDesk\Persistence\PersistentContainer;
use WPDesk\FCS\Repository\EmailTemplateRepository;

/**
 * Email class responsible for sending coupon to defined recipients.
 */
class SendingRecipientEmail extends SendingEmail {

	public const SLUG = 'SendingRecipientEmail';

	/**
	 * @var array
	 */
	private $bcc_recipients = [];


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
		$this->id            = 'coupon_sending_recipient_email';
		$this->title         = __( 'Coupon for recipient (Advanced Sending for Flexible Coupons Pro)', 'flexible-coupons-sending' );
		$this->description   = __( 'This message goes to the coupon recipient.', 'flexible-coupons-sending' );
		$this->heading       = __( 'Coupon', 'flexible-coupons-sending' );
		$this->template_html = 'emails/coupon-recipient.php';
	}

	/**
	 * Setup and get email recipients.
	 */
	public function get_recipient(): string {
		$recipients = [];
		if ( \is_email( $this->meta->get_recipient_email() ) ) {
			$recipients[] = $this->meta->get_recipient_email();
		}

		$additional_recipients = $this->get_email_template()->get_recipients() ?? [];

		if ( count( $additional_recipients ) > 0 ) {
			$additional_recipients = \array_map( 'trim', $additional_recipients );
			$additional_recipients = \array_map( [ $this->get_replacer(), 'replace_shortcodes' ], $additional_recipients );
			$additional_recipients = \array_map( [ $this, 'format_string' ], $additional_recipients );
			$recipients            = \array_merge( $recipients, $additional_recipients );
		}

		$this->recipient = $recipients[0] ?? '';

		// other recipients have to be set as BCC headers.
		if ( is_array( $recipients ) && count( $recipients ) > 1 ) {
			$this->bcc_recipients = \array_slice( $recipients, 1 );
		}

		// run it through the WC_Email filter.
		return parent::get_recipient();
	}

	/**
	 * Slug for woocommerce registration.
	 */
	public function get_slug(): string {
		return self::SLUG;
	}

	/**
	 * Add BCC headers to default WC Email headers.
	 */
	public function get_headers(): string {
		// WC headers
		$headers = parent::get_headers();

		if ( ! empty( $this->bcc_recipients ) ) {
			foreach ( $this->bcc_recipients as $bcc_recipient ) {
				$headers .= 'Bcc: ' . $bcc_recipient . "\r\n";
			}
		}

		return $headers;
	}
}
