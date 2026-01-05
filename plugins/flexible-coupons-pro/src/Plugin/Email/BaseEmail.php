<?php
/**
 * Email: Buyer email template.
 *
 * @package WPDesk\FlexibleCouponsPro
 */

namespace WPDesk\FlexibleCouponsPro\Email;

use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Email\FlexibleCouponsBaseEmail;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\PDF\Download;
use FlexibleCouponsProVendor\Psr\Log\LoggerInterface;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Coupon\CouponsProcessing;

/**
 * Register coupon pro template email.
 *
 * @package WPDesk\WooCommerceWFirma\Email
 */
class BaseEmail extends FlexibleCouponsBaseEmail {

	/**
	 * @var bool
	 */
	public $enabled = true;

	/**
	 * @var LoggerInterface
	 */
	protected $logger;

	/**
	 * @var Download
	 */
	private $download;

	/**
	 * @param string          $template_path
	 * @param LoggerInterface $logger
	 */
	public function __construct( string $template_path, LoggerInterface $logger, Download $download ) {
		parent::__construct( $template_path );
		$this->logger   = $logger;
		$this->download = $download;
	}

	protected function get_string_attachments(): array {
		if ( ! $this->can_attach_pdf ) {
			return [];
		}

		$attachements = [];

		foreach ( $this->meta->get_coupons_array() as $coupon ) {
			$name      = $coupon['coupon_code'] . '.pdf';
			$prefix    = \esc_html__( 'Coupon', 'flexible-coupons-pro' );
			$full_name = $prefix . '-' . $name;

			/**
			 * Define name for PDF attached to email.
			 *
			 * @param string $full_name Name with prefix & code & pdf extension.
			 * @param string $name      Name with code & pdf extension.
			 * @param array  $meta      Coupon meta data.
			 *
			 * @since 1.4.0
			 */
			$filename         = \apply_filters( 'fcpdf/core/pdf/filename', $full_name, $name, $coupon );
			$coupon['return'] = 'string';
			$pdf_content      = $this->download->get_pdf_content( $coupon );

			if ( empty( $pdf_content ) ) {
				continue;
			}

			$attachements[] = [
				'pdf' => [
					'fileName' => $filename,
					'content'  => $pdf_content,
				],
			];
		}

		return $attachements;
	}
}
