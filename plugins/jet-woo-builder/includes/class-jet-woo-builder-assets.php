<?php
/**
 * JetWooBuilder Assets class.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Woo_Builder_Assets' ) ) {

	/**
	 * Define Jet_Woo_Builder_Assets class
	 */
	class Jet_Woo_Builder_Assets {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		public function init() {
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
			add_action( 'elementor/frontend/before_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
			add_action( 'elementor/frontend/after_enqueue_scripts', [ 'WC_Frontend_Scripts', 'localize_printed_scripts' ], 5 );
		}

		/**
		 * Enqueue styles.
		 *
		 * Enqueue public-facing stylesheets.
		 *
		 * @since  1.0.0
		 * @since  2.1.6 Refactored. Changed some scripts from `wp_enqueue_style` to `wp_register_style`.
		 * @access public
		 *
		 * @return void
		 */
		public function enqueue_styles() {

			wp_register_style(
				'jet-woo-builder',
				jet_woo_builder()->plugin_url( 'assets/css/frontend.css' ),
				apply_filters( 'jet-woo-builder/frontend/styles-dependencies', [] ),
				jet_woo_builder()->get_version()
			);

			wp_register_style(
				'jet-woo-builder-frontend-font',
				jet_woo_builder()->plugin_url( 'assets/css/lib/jetwoobuilder-frontend-font/css/jetwoobuilder-frontend-font.css' ),
				false,
				jet_woo_builder()->get_version()
			);

			$font_path = WC()->plugin_url() . '/assets/fonts/';

			wp_add_inline_style( 'jet-woo-builder', '@font-face {
				font-family: "WooCommerce";
				font-weight: normal;
				font-style: normal;
				src: url("' . $font_path . 'WooCommerce.eot");
				src: url("' . $font_path . 'WooCommerce.eot?#iefix") format("embedded-opentype"),
					 url("' . $font_path . 'WooCommerce.woff") format("woff"),
					 url("' . $font_path . 'WooCommerce.ttf") format("truetype"),
					 url("' . $font_path . 'WooCommerce.svg#WooCommerce") format("svg");
			}' );

		}

		/**
		 * Enqueue admin assets.
		 *
		 * @return void
		 */
		public function enqueue_admin_assets() {
			wp_register_script(
				'jet-woo-builder-tippy',
				jet_woo_builder()->plugin_url( 'assets/lib/tippy/tippy.all.min.js' ),
				[],
				'2.5.4',
				true
			);
		}

		/**
		 * Enqueue scripts.
		 *
		 * Enqueue plugin scripts only with elementor scripts.
		 *
		 * @since 2.1.11 Added `jet-plugins` script.
		 */
		public function enqueue_scripts() {

			wp_register_script(
				'jet-plugins',
				jet_woo_builder()->plugin_url( 'assets/lib/jet-plugins/jet-plugins.js' ),
				[ 'jquery' ],
				'1.0.0',
				true
			);

			wp_enqueue_script(
				'jet-woo-builder',
				jet_woo_builder()->plugin_url( 'assets/js/frontend' . $this->suffix() . '.js' ),
				apply_filters( 'jet-woo-builder/frontend/script-dependencies', [
					'jquery',
					'elementor-frontend',
					'jet-plugins',
				] ),
				jet_woo_builder()->get_version(),
				true
			);

			global $wp_query;

			wp_localize_script(
				'jet-woo-builder',
				'jetWooBuilderData',
				apply_filters( 'jet-woo-builder/frontend/localize-data', [
					'ajax_url'                => esc_url( admin_url( 'admin-ajax.php' ) ),
					'products'                => json_encode( $wp_query->query_vars ),
					'single_ajax_add_to_cart' => 'yes' === jet_woo_builder_shop_settings()->get( 'use_ajax_add_to_cart' ),
				] )
			);

		}

		/**
		 * Returns minified suffix for plugin scripts
		 *
		 * @return string
		 */
		public function suffix() {
			return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;

		}

	}

}

/**
 * Returns instance of Jet_Woo_Builder_Assets
 *
 * @return object
 */
function jet_woo_builder_assets() {
	return Jet_Woo_Builder_Assets::get_instance();
}
