<?php
/**
 * Email: Buyer email template.
 *
 * @package WPDesk\FlexibleCouponsPro
 */

namespace WPDesk\FlexibleCouponsPro\Email;

use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\PDF\Download;
use FlexibleCouponsProVendor\Psr\Log\LoggerInterface;

/**
 * Register coupon pro template email.
 *
 * @package WPDesk\WooCommerceWFirma\Email
 */
class BuyerEmail extends BaseEmail {

	const SLUG = 'FlexibleCouponsEmail';

	/**
	 * @param string $template_path  Plugin template path.
	 * @param bool   $can_attach_pdf Can attach PDF to email.
	 */
	public function __construct( string $template_path, bool $can_attach_pdf, LoggerInterface $logger, Download $download ) {
		$this->id             = 'coupon_buyer_email';
		$this->title          = esc_html__( 'Coupon for buyer (Flexible Coupons Pro)', 'flexible-coupons-pro' );
		$this->description    = esc_html__( 'This message goes to the coupon buyer.', 'flexible-coupons-pro' );
		$this->heading        = esc_html__( 'Coupon', 'flexible-coupons-pro' );
		$this->subject        = esc_html__( '[{site_title}] You have received a coupon', 'flexible-coupons-pro' );
		$this->template_html  = 'emails/coupon.php';
		$this->template_plain = 'emails/plain/coupon.php';
		$this->can_attach_pdf = $can_attach_pdf;
		parent::__construct( $template_path, $logger, $download );
		$this->enabled = $this->get_option( 'enabled' );
		$this->manual  = false;
	}

	public function init_form_fields() {
		parent::init_form_fields();
		$fields['enabled'] = [
			'title'   => __( 'Enable/Disable', 'woocommerce' ),
			'type'    => 'checkbox',
			'label'   => __( 'Enable this email notification', 'woocommerce' ),
			'default' => 'yes',
		];

		$this->form_fields = array_merge( $fields, $this->form_fields );
	}

	public function get_recipient(): string {

		$this->recipient = $this->object->get_billing_email();

		// run it through the WC_Email filter.
		return parent::get_recipient();
	}
}
