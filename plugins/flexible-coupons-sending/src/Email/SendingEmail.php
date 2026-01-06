<?php

namespace WPDesk\FCS\Email;

use WC_Order;
use WC_Coupon;
use WC_Product;
use WC_Order_Item;
use WC_Order_Factory;
use FlexibleCouponsProVendor\Psr\Log\LoggerInterface;
use WPDesk\FlexibleCouponsPro\Email\BaseEmail;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Helpers\EmailStrings;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\PDF\Download;
use FlexibleCouponsProVendor\WPDesk\Persistence\PersistentContainer;
use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\Shortcode;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\ShortCodeReplacer;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Shortcodes\ShortcodeDataContainer;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Data\Email\EmailMeta;
use WPDesk\FCS\Repository\EmailTemplateRepository;
use WPDesk\FCS\Entity\EmailTemplateEntity;
use WPDesk\FCS\Product\ProductSettingsStorage;
use WPDesk\FCS\Exception\MetaException;

/**
 * Class responsible for sending coupons.
 */
abstract class SendingEmail extends BaseEmail {

	/**
	 * @var array
	 */
	protected $shortcodes;

	/**
	 * Settings container.
	 *
	 * @var PersistentContainer
	 */
	protected $settings_container;

	/**
	 * @var ShortCodeReplacer
	 */
	protected $replacer;

	protected EmailTemplateRepository $email_template_repository;

	protected ?EmailTemplateEntity $email_template_entity = null;


	public function __construct(
		string $template_path,
		bool $can_attach_pdf,
		LoggerInterface $logger,
		Download $download,
		PersistentContainer $settings,
		array $shortcodes,
		EmailTemplateRepository $email_template_repository
	) {
		parent::__construct( $template_path, $logger, $download );
		// default subject (can be overwrite in settings)
		$this->subject                   = EmailStrings::get_default_email_subject();
		$this->customer_email            = true;
		$this->can_attach_pdf            = $can_attach_pdf;
		$this->manual                    = false;
		$this->email_type                = 'html';
		$this->enabled                   = 'yes';
		$this->settings_container        = $settings;
		$this->shortcodes                = $shortcodes;
		$this->email_template_repository = $email_template_repository;
	}

	/**
	 * Slug for woocommerce registration.
	 */
	abstract public function get_slug(): string;

	/**
	 * Setting up placeholders.
	 * Parent format_string function uses placeholders to find and replace.
	 */
	protected function setup_placeholders(): void {

		$fcs_placeholders   = [
			'{site_description}'  => \get_option( 'blogdescription' ),
			'{admin_email}'       => \get_option( 'admin_email' ),
			'{current_date}'      => \current_time( 'mysql' ),
			'{recipient_name}'    => $this->meta->get_recipient_name(),
			'{recipient_message}' => $this->meta->get_recipient_message(),
			'{recipient_email}'   => $this->meta->get_recipient_email(),
			'{buyer_name}'        => $this->object->get_billing_first_name() . ' ' . $this->object->get_billing_last_name(),
			'{coupon_url}'        => implode( ', ' . PHP_EOL, $this->meta->get_coupon_urls() ) ?? '',
			'{coupon_code}'       => implode( ', ', $this->meta->get_coupon_codes() ) ?? '',
			'{coupon_value}'      => $this->meta->get_coupon_value(),
			'{coupon_expiry}'     => $this->meta->get_coupon_expiry(),
		];
		$this->placeholders = array_merge( $this->placeholders, $fcs_placeholders );

		parent::setup_placeholders();
	}

	/**
	 * Settings on woocommerce settings page.
	 */
	public function init_form_fields() {
		$this->form_fields = [
			'enabled' => [
				'title'   => __( 'Enable/Disable', 'flexible-coupons-sending' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'flexible-coupons-sending' ),
				'default' => 'yes',
			],
		];
	}

	/**
	 * Get HTML content.
	 *
	 * @return string
	 */
	public function get_content_html(): string {
		ob_start();

		\wc_get_template(
			$this->template_html,
			[
				'email_body'    => $this->get_body(),
				'email_heading' => $this->heading,
				'email'         => $this->customer_email,
			],
			'',
			$this->template_base
		);

		return ob_get_clean();
	}

	/**
	 * Setup and get email subject.
	 */
	public function get_subject(): string {
		$this->subject = $this->get_email_template()->get_subject() ?? $this->subject;

		// flexible coupons shortcodes
		$this->subject = $this->get_replacer()->replace_shortcodes( $this->subject );

		// replacing placeholders
		return $this->format_string( $this->subject );
	}

	/**
	 * Get email body.
	 */
	protected function get_body(): string {
		$body = $this->get_email_template()->get_content() ?? EmailStrings::get_default_email_body();

		// flexible coupons shortcodes
		$body = $this->get_replacer()->replace_shortcodes( $body );

		// replacing placeholders
		return $this->format_string( $body );
	}

	/**
	 * Replacer is an object for replacing shortcodes in a string.
	 */
	protected function get_replacer(): ShortCodeReplacer {
		if ( $this->replacer instanceof ShortCodeReplacer ) {
			return $this->replacer;
		}

		if ( ! $this->meta instanceof EmailMeta ) {
			throw new MetaException( 'Email meta is not set' );
		}

		if ( ! $this->object instanceof WC_Order ) {
			throw new MetaException( 'Order is not set' );
		}

		$items = $this->object->get_items();
		$item  = isset( $items[ $this->meta->get_item_id() ] ) ? $items[ $this->meta->get_item_id() ] : null;

		if ( ! $item instanceof WC_Order_Item ) {
			throw new MetaException( 'Order item is not set' );
		}

		$product = $item->get_product();
		$coupon  = new WC_Coupon( $this->meta->get_coupon_id() );

		$shortcodes_to_replace = $this->match_shortcode_values(
			$this->object,
			$item,
			$product,
			$coupon,
			$this->meta->get_meta(), //Todo: array here
			$coupon->get_code() //TODO: something here
		);
		$this->replacer        = new ShortCodeReplacer( $shortcodes_to_replace );

		return $this->replacer;
	}

	/**
	 * Returns all registered shortcodes with their values.
	 */
	private function match_shortcode_values(
		WC_Order $order,
		WC_Order_Item $item,
		WC_Product $product,
		WC_Coupon $coupon,
		array $product_fields_values,
		string $coupon_code
	): array {
		$shortcodes = [];
		foreach ( $this->shortcodes as $shortcode ) {
			if ( $shortcode instanceof Shortcode ) {
				$data_container = new ShortcodeDataContainer();
				$data_container->set_order( $order );
				$data_container->set_product( $product );
				$data_container->set_product_fields_values( $product_fields_values );
				$data_container->set_coupon_code( $coupon_code );
				$data_container->set_coupon( $coupon );
				$data_container->set_item( $item );
				$shortcodes[ $shortcode->get_id() ] = $shortcode->get_value( $data_container );
			}
		}

		return $shortcodes;
	}

	protected function get_email_template(): EmailTemplateEntity {
		if ( $this->email_template_entity instanceof EmailTemplateEntity ) {
			return $this->email_template_entity;
		}

		$product_id  = $this->meta->get_product_id();
		$template_id = (int) get_post_meta( $product_id, '_' . ProductSettingsStorage::EMAIL_TEMPLATE_ID, true );
		if ( $template_id !== 0 ) {
			return $this->email_template_repository->get_by_id( $template_id );
		}

		return $this->email_template_repository->get_default();
	}
}
