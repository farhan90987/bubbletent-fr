<?php
/**
 * Coupon library.
 *
 * @package WPDesk\FlexibleCouponsPro
 */

namespace WPDesk\FlexibleCouponsPro;

use FlexibleCouponsProVendor\WPDesk_Plugin_Info;
use WPDesk\FlexibleCouponsPro\Coupons\AdminPage;
use WPDesk\FlexibleCouponsPro\Shortcodes\SiteUrl;
use WPDesk\FlexibleCouponsPro\Email\RegisterEmails;
use FlexibleCouponsProVendor\Psr\Log\LoggerInterface;
use WPDesk\FlexibleCouponsPro\Shortcodes\ProductName;
use FlexibleCouponsProVendor\Psr\Log\LoggerAwareTrait;
use WPDesk\FlexibleCouponsPro\Shortcodes\CustomerName;
use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\EditorIntegration;
use WPDesk\FlexibleCouponsPro\Shortcodes\RecipientName;
use WPDesk\FlexibleCouponsPro\Shortcodes\RecipientEmail;
use WPDesk\FlexibleCouponsPro\Shortcodes\CustomerAddress;
use WPDesk\FlexibleCouponsPro\Shortcodes\CouponExpiryDate;
use WPDesk\FlexibleCouponsPro\Shortcodes\RecipientMessage;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\PluginAccess;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\CouponsIntegration;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Shortcodes\CouponCode;


use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Shortcodes\CouponValue;

/**
 * Coupon library implementation.
 *
 * @package WPDesk\FlexibleCouponsPro
 */
class Integration extends CouponsIntegration {

	use LoggerAwareTrait;

	/**
	 * @var WPDesk_Plugin_Info
	 */
	private $plugin_info;

	/**
	 * @param EditorIntegration $editor Editor.
	 * @param WPDesk_Plugin_Info $plugin_info Plugin info.
	 * @param LoggerInterface $logger Logger.
	 */
	public function __construct(
		EditorIntegration $editor,
		WPDesk_Plugin_Info $plugin_info,
		LoggerInterface $logger
	) {
		$this->plugin_info = $plugin_info;
		parent::__construct(
			$editor,
			$plugin_info->get_version(),
			$logger
		);
		$this->set_product_fields( new ProductFieldsDefinition() );
		self::set_pro();
	}

	/**
	 * This method exists for transfers shortcodes declaration to visual editor and library.
	 * If defined shortcode has used in project, library will replace shortcode by declared value.
	 *
	 * @return array
	 */
	protected function shortcodes_definition(): array {
		return [
			new CouponValue(),
			new CouponCode(),
			new CouponExpiryDate(),
			new CustomerAddress(),
			new CustomerName(),
			new ProductName(),
			new RecipientMessage(),
			new RecipientEmail(),
			new RecipientName(),
			new SiteUrl(),
		];
	}

	public function hooks() {
		add_action(
			'fc/core/init',
			function ( PluginAccess $access ) {
				$this->add_hookable( new RegisterEmails( $this->plugin_info, $access->get_logger(), $access->get_persistence(), $access->get_download() ) );
			}
		);
		$this->add_hookable( new AdminPage() );
		parent::hooks();
		$this->hooks_on_hookable_objects();
	}
}
