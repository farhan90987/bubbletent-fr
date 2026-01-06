<?php
/**
 * Plugin main class.
 *
 * @package WPDesk\PluginTemplate
 */

namespace WPDesk\FlexibleCouponsPro;

use FlexibleCouponsProVendor\WPDesk_Plugin_Info;
use FlexibleCouponsProVendor\Psr\Log\LoggerInterface;
use FlexibleCouponsProVendor\Psr\Log\LoggerAwareTrait;
use WPDesk\FlexibleCouponsPro\Marketing\SupportMenuPage;
use FlexibleCouponsProVendor\Psr\Log\LoggerAwareInterface;
use FlexibleCouponsProVendor\WPDesk\Logger\SimpleLoggerFactory;
use FlexibleCouponsProVendor\WPDesk\Dashboard\DashboardWidget;
use FlexibleCouponsProVendor\WPDesk\PluginBuilder\Plugin\Activateable;
use FlexibleCouponsProVendor\WPDesk\PluginBuilder\Plugin\AbstractPlugin;
use FlexibleCouponsProVendor\WPDesk\PluginBuilder\Plugin\HookableParent;
use FlexibleCouponsProVendor\WPDesk\PluginBuilder\Plugin\HookableCollection;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\SampleTemplates;
use FlexibleCouponsProVendor\WPDesk\Logger\Settings as LoggerSettings;

/**
 * Main plugin class. The most important flow decisions are made here.
 *
 * @package WPDesk\PluginTemplate
 */
class Plugin extends AbstractPlugin implements LoggerAwareInterface, HookableCollection, Activateable {
	use LoggerAwareTrait;
	use HookableParent;

	/**
	 * @var string
	 */
	private $plugin_path;

	/**
	 * @var string
	 */
	private $scripts_version = '1.0';

	public const EDITOR_POST_TYPE = 'wpdesk-coupons';

	private const LOGGER_CHANNEL = 'flexible-coupons-pro';

	/**
	 * Plugin constructor.
	 *
	 * @param WPDesk_Plugin_Info $plugin_info Plugin info.
	 */
	public function __construct( WPDesk_Plugin_Info $plugin_info ) {
		parent::__construct( $plugin_info );

		$this->plugin_info      = $plugin_info;
		$this->plugin_url       = $this->plugin_info->get_plugin_url();
		$this->plugin_path      = $this->plugin_info->get_plugin_dir();
		$this->plugin_namespace = $this->plugin_info->get_text_domain();

		$is_pl              = 'pl_PL' === get_locale();
		$this->settings_url = admin_url( 'edit.php?post_type=' . self::EDITOR_POST_TYPE . '&page=fc-settings' );
		$this->docs_url     = $is_pl ? 'https://www.wpdesk.pl/docs/flexible-coupons-pro/' : 'https://www.wpdesk.net/docs/flexible-coupons-pro/';
		$this->support_url  = $is_pl ? 'https://www.wpdesk.pl/support/' : 'https://www.wpdesk.net/support/';
		$this->setLogger(
			( new SimpleLoggerFactory( self::LOGGER_CHANNEL ) )->getLogger()
		);
	}

	/**
	 * Integrate with WordPress and with other plugins using action/filter system.
	 *
	 * @return void
	 */
	public function hooks() {
		parent::hooks();
		add_action(
			'woocommerce_init',
			function () {
				$editor      = new RegisterEditor();
				$integration = new Integration(
					$editor,
					$this->plugin_info,
					$this->logger
				);
				$this->add_hookable( $editor );
				$this->add_hookable( $integration );
				$this->add_hookable( new SupportMenuPage() );
				$this->hooks_on_hookable_objects();
			},
			10
		);

		( new DashboardWidget() )->hooks();
	}

	/**
	 * Enqueue admin scripts.
	 */
	public function admin_enqueue_scripts() {
		$screen = get_current_screen();
		$texts  = apply_filters( 'flexible_coupons_pro_editor_text', [] );
		if ( $screen && 'post' === $screen->base && self::EDITOR_POST_TYPE === $screen->post_type && ! empty( $texts ) ) {
			wp_localize_script(
				'wp-canva-admin',
				'wpdesk_canva_editor_texts',
				$texts
			);
		}
	}

	/**
	 * Plugin activation.
	 */
	public function activate() {
		$post = new SampleTemplates( self::EDITOR_POST_TYPE );
		$post->create();
	}

	/**
	 * Links filter.
	 *
	 * @param array $links Links.
	 *
	 * @return array
	 */
	public function links_filter( $links ) {
		$links = parent::links_filter( $links );

		$start_here_url  = admin_url( 'edit.php?post_type=wpdesk-coupons&page=wpdesk-fc-marketing' );
		$start_here_link = '<a style="font-weight:700; color: #007050" href="' . $start_here_url . '">' . __( 'Start Here', 'flexible-coupons-pro' ) . '</a>';
		// first link.
		array_unshift( $links, $start_here_link );

		$is_pl           = 'pl_PL' === get_locale();
		$buy_addons_url  = $is_pl ? 'https://www.wpdesk.pl/sklep/flexible-coupons-woocommerce/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-pro&utm_content=plugin-addons#dodatki' : 'https://wpdesk.net/products/flexible-coupons-woocommerce/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-pro&utm_content=plugin-addons#addons';
		$buy_addons_link = '<a style="font-weight:700; color: #FF9743" href="' . $buy_addons_url . '" target="_blank">' . __( 'Add-ons', 'flexible-coupons-pro' ) . ' →</a>';
		array_push( $links, $buy_addons_link );

		// move deactive link to the end.
		if ( array_key_exists( 'deactivate', $links ) ) {
			$deactivate_value = $links['deactivate'];
			unset( $links['deactivate'] );
			$links['deactivate'] = $deactivate_value;
		}

		return $links;
	}
}
