<?php
/**
 * Plugin main class.
 */

namespace WPDesk\FCS;

use WPDesk\FCS\Product;
use FCSVendor\WPDesk_Plugin_Info;
use FCSVendor\WPDesk\Notice\Notice;
use WPDesk\FCS\Email\RegisterEmails;
use WPDesk\FCS\Schedule\EmailScheduler;
use WPDesk\FCS\Schedule\ScheduleRunner;
use WPDesk\FCS\Schedule\DelayCalculator;
use WPDesk\FCS\MetaProvider\MetaProvider;
use WPDesk\FCS\Settings\Tabs\EmailSettings;
use WPDesk\FCS\Settings\SettingsIntegration;
use WPDesk\FCS\Product\ProductFieldsDefinition;
use WPDesk\FCS\MetaProvider\Source\CouponMetaSource;
use FCSVendor\WPDesk\PluginBuilder\Plugin\AbstractPlugin;
use FCSVendor\WPDesk\PluginBuilder\Plugin\HookableParent;
use WPDesk\FCS\MetaProvider\Source\ProductFieldsMetaSource;
use FCSVendor\WPDesk\PluginBuilder\Plugin\HookableCollection;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\PluginAccess;
use WPDesk\FCS\Settings\Product\EmailTemplateListOptionFetcher;
use WPDesk\FCS\Settings\EmailTemplateAjax;
use WPDesk\FCS\Repository\EmailTemplateRepository;
use FCSVendor\WPDesk\Migrations\WpdbMigrator;

/**
 * Main plugin class. The most important flow decisions are made here.
 *
 * @codeCoverageIgnore
 */
class Plugin extends AbstractPlugin implements HookableCollection {

	use HookableParent;

	/**
	 * Plugin constructor.
	 *
	 * @param WPDesk_Plugin_Info $plugin_info Plugin info.
	 */

	const EDITOR_POST_TYPE = 'wpdesk-coupons';

	public function __construct( WPDesk_Plugin_Info $plugin_info ) {
		parent::__construct( $plugin_info );

		$is_pl              = 'pl_PL' === \get_locale();
		$this->docs_url     = $is_pl ? 'https://www.wpdesk.pl/docs/docs-kupony-woocommerce-pro-zaawansowana-wysylka/' : 'https://flexiblecoupons.net/documentation/advanced-sending/docs-flexible-coupons-advanced-sending/';
		$this->settings_url = \admin_url( 'edit.php?post_type=' . self::EDITOR_POST_TYPE . '&page=fc-settings&tab=emails' );

		$this->plugin_info      = $plugin_info;
		$this->plugin_url       = $this->plugin_info->get_plugin_url();
		$this->plugin_namespace = $this->plugin_info->get_text_domain();
	}

	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public function hooks(): void {
		parent::hooks();

		\add_action(
			'fc/core/init',
			function ( PluginAccess $access ) {
				$coupons_pro_version = $access->get_plugin_version();
				if ( version_compare( $coupons_pro_version, '3', '>=' ) ) {
					new Notice(
						sprintf(
							// translators: %s Flexible Coupons Pro version.
							__(
								'This version of Flexible PDF Coupons PRO - Advanced Sending plugin is not compatible with Flexible Coupons Pro %s. Please upgrade Flexible PDF Coupons PRO - Advanced Sending to the newest version.',
								'flexible-coupons-sending'
							),
							$coupons_pro_version
						)
					);

					return;
				}

				$post_meta                 = $access->get_post_meta();
				$logger                    = $access->get_logger();
				$meta_provider             = new MetaProvider(
					new ProductFieldsMetaSource( $post_meta, $access->get_product_fields() ),
					new CouponMetaSource( $post_meta, $access->get_persistence() )
				);
				$email_template_repository = new EmailTemplateRepository();

				$this->add_hookable(
					new RegisterEmails(
						$this->plugin_info,
						$logger,
						$access->get_persistence(),
						$access->get_download(),
						$access->get_shortcodes(),
						$email_template_repository
					)
				);
				$this->add_hookable(
					new ScheduleRunner(
						$logger,
						$meta_provider
					)
				);
				$this->add_hookable(
					new EmailScheduler(
						new DelayCalculator(),
						new Product\ProductSettingsStorage( $post_meta ),
						$logger
					)
				);
				$this->add_hookable( new Product\SimpleProductSettingsStorage( $post_meta ) );
				$this->add_hookable( new Product\VariableProductSettingsStorage( $post_meta ) );
				$this->add_hookable( new ProductFieldsDefinition() );
				$this->add_hookable( new PostType\EmailTemplate() );
				$this->add_hookable( new EmailTemplateListOptionFetcher( $email_template_repository ) );
				$this->add_hookable( new EmailTemplateAjax( $email_template_repository ) );
				$this->add_hookable( new Email\EmailPreview() );

				$this->hooks_on_hookable_objects();
			}
		);

		add_action(
			'plugins_loaded',
			function () {
				$migrator = $this->get_migrator();
				$migrator->migrate();
			}
		);
	}

	private function get_migrator(): WpdbMigrator {
		return WpdbMigrator::from_classes(
			[
				Migrations\Version200::class,
			],
			'fcs_migration_db'
		);
	}

	public function links_filter( $links ) {
		$links = parent::links_filter( $links );

		$start_here_url  = admin_url( 'edit.php?post_type=wpdesk-coupons&page=wpdesk-fc-marketing' );
		$start_here_link = '<a style="font-weight:700; color: #007050" href="' . $start_here_url . '">' . __( 'Start Here', 'flexible-coupons-sending' ) . '</a>';
		// first link.
		array_unshift( $links, $start_here_link );

		// move deactive link to the end.
		if ( array_key_exists( 'deactivate', $links ) ) {
			$deactivate_value = $links['deactivate'];
			unset( $links['deactivate'] );
			$links['deactivate'] = $deactivate_value;
		}

		return $links;
	}
}
