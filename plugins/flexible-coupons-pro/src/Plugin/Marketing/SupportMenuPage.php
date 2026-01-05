<?php

namespace WPDesk\FlexibleCouponsPro\Marketing;

use FlexibleCouponsProVendor\WPDesk\Library\Marketing\Boxes\Assets;
use FlexibleCouponsProVendor\WPDesk\Library\Marketing\Boxes\MarketingBoxes;
use FlexibleCouponsProVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use FlexibleCouponsProVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use FlexibleCouponsProVendor\WPDesk\View\Resolver\ChainResolver;
use FlexibleCouponsProVendor\WPDesk\View\Resolver\DirResolver;
use FlexibleCouponsProVendor\WPDesk\View\Renderer\Renderer;

class SupportMenuPage implements Hookable {

	const SCRIPTS_VERSION = 2;
	const PLUGIN_SLUG     = 'flexible-coupons-pro';

	/**
	 * @var Renderer
	 */
	private $renderer;

	public function __construct() {
		$this->init_renderer();
	}

	public function hooks() {
		add_action(
			'admin_menu',
			function () {
				add_submenu_page(
					'edit.php?post_type=wpdesk-coupons',
					esc_html__( 'Start Here', 'flexible-coupons-pro' ),
					'<span style="color: #00FFC2">' . esc_html__( 'Start Here', 'flexible-coupons-pro' ) . '</span>',
					'manage_options',
					'wpdesk-fc-marketing',
					[ $this, 'render_page_action' ],
					11
				);
			},
			9999
		);

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

		Assets::enqueue_assets();
		Assets::enqueue_owl_assets();
	}

	/**
	 * Init renderer.
	 */
	private function init_renderer() {
		$resolver = new ChainResolver();
		$resolver->appendResolver( new DirResolver( __DIR__ . '/Views/' ) );
		$this->renderer = new SimplePhpRenderer( $resolver );
	}

	public function render_page_action() {
		$local = get_locale();
		if ( $local === 'en_US' ) {
			$local = 'en';
		}
		$boxes = new MarketingBoxes( self::PLUGIN_SLUG, $local );
		echo $this->renderer->render( 'marketing-page', [ 'boxes' => $boxes ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @param string $screen_id
	 */
	public function admin_enqueue_scripts( $screen_id ) {
		if ( in_array( $screen_id, [ 'wpdesk-coupons_page_wpdesk-marketing' ], true ) ) {
			wp_enqueue_style( 'wpdesk-marketing', plugin_dir_url( __FILE__ ) . 'assets/css/marketing.css', [], self::SCRIPTS_VERSION );
			wp_enqueue_style( 'wpdesk-modal-marketing', plugin_dir_url( __FILE__ ) . 'assets/css/modal.css', [], self::SCRIPTS_VERSION );
			wp_enqueue_script( 'wpdesk-marketing', plugin_dir_url( __FILE__ ) . 'assets/js/modal.js', [ 'jquery' ], self::SCRIPTS_VERSION, true );
		}
	}
}
