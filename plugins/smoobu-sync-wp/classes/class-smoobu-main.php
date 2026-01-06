<?php
/**
 * Main plugin class
 *
 * @package smoobu-calendar
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No skiddies please!' );
}

/**
 * Main class
 */
final class Smoobu_Main {
	/**
	 * Class instance
	 *
	 * @var Smoobu_Main
	 */
	protected static $instance = null;

	/**
	 * Main instance
	 *
	 * @return Smoobu_Main
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class constructor, loads main actions, methods etc.
	 */
	public function __construct() {

		// localization.
		add_action( 'plugins_loaded', array( $this, 'text_domain' ) );

		// register styling and scripts.
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );

		// register admin styling and scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		// admin settings.
		add_action( 'init', array( 'Smoobu_Settings', 'instance' ) );

		// shortcodes.
		add_action( 'init', array( 'Smoobu_Calendar_Shortcode', 'instance' ) );

		// widgets.
		add_action( 'widgets_init', array( $this, 'widgets' ) );

		// gutenberg blocks.
		add_action( 'plugins_loaded', array( 'Smoobu_Blocks', 'instance' ) );

		// webhooks.
		add_action( 'init', array( 'Smoobu_Webhook', 'instance' ) );

		// AJAX actions.
		add_action( 'init', array( 'Smoobu_Ajax', 'instance' ) );

		// API Bookings.
		add_action( 'init', array( 'Smoobu_Api_Booking', 'instance' ) );

		// WooComemrce settings.
		include_once SMOOBU_PATH . 'classes/woocomerce/class-smoobu-booking-from-woo.php';
		add_action( 'plugins_loaded', array( 'Smoobu_Booking_From_Woo', 'instance' ) );
	}

	/**
	 * Localization
	 *
	 * @return void
	 */
	public function text_domain() {
		load_plugin_textdomain( SMOOBU_NAME, false, SMOOBU_PATH . 'languages' );
	}

	/**
	 * Rewrite plugin template with theme template if exists under folder /theme-name/smoobu-calendar/
	 *
	 * @param string $template template name.
	 * @param array  $args arguments to pass to template.
	 * @return void
	 */
	public static function load_template( $template, $args = array() ) {
		// transfer required arguments.
		foreach ( $args as $key => $arg ) {
			${$key} = $arg;
		}

		// load template below.
		if ( file_exists( get_template_directory() . SMOOBU_NAME . '/' . $template . '.php' ) ) {
			// if overriden in theme.
			$load = get_template_directory() . SMOOBU_NAME . '/' . $template . '.php';
		} elseif ( file_exists( SMOOBU_PATH . 'views/' . $template . '.php' ) ) {
			// if exists at all.
			$load = SMOOBU_PATH . 'views/' . $template . '.php';
		}

		if ( ! empty( $load ) ) {
			include $load;
		} else {
			esc_html_e( 'Template not found', 'smoobu-calendar' );
		}
	}

	/**
	 * Register native styling and scripts
	 *
	 * @return void
	 */
	public function scripts() {
		// styles.
		wp_register_style( 'smoobu-calendar-css-main', SMOOBU_URI . 'assets/css/main.min.css', array(), SMOOBU_VERSION );

		// Experiment with easepick.
		wp_register_style( 'smoobu-calendar-css-easepick', SMOOBU_URI . 'assets/css/index.css', array(), SMOOBU_VERSION );
		wp_register_style( 'smoobu-calendar-easepick-hotel', SMOOBU_URI . 'assets/css/hotel-example.css', array(), SMOOBU_VERSION );

		// all calendar themes.
		wp_register_style( 'smoobu-calendar-css-theme-default', SMOOBU_URI . 'assets/css/default/theme.css', array(), SMOOBU_VERSION );
		wp_register_style( 'smoobu-calendar-css-theme-dark', SMOOBU_URI . 'assets/css/dark/theme.css', array(), SMOOBU_VERSION );

		wp_register_script(
			'smoobu-calendar-easepick-js',
			SMOOBU_URI . 'assets/js/index.umd.js',
			array(),
			SMOOBU_VERSION,
			true
		);

		// Scripts.
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		if (
			function_exists( 'is_plugin_active' ) &&
			is_plugin_active( 'woocommerce/woocommerce.php' ) &&
			is_checkout()
		) {
			$is_in_cart = false;

			foreach ( WC()->cart->get_cart() as $cart_item ) {
				$product = wc_get_product( $cart_item['data']->get_id() );
				if ( $product->is_type( 'listing_booking' ) ) {
					$is_in_cart = true;
					break;
				}
			}

			if ( $is_in_cart ) {
				wp_enqueue_script(
					'smoobu-calendar-checkout-js',
					SMOOBU_URI .
					'assets/js/main-checkout.min.js',
					array( 'jquery', 'smoobu-calendar-easepick-js', 'wp-i18n' ),
					SMOOBU_VERSION,
					true
				);
			}
		} else {
			wp_register_script(
				'smoobu-calendar-js',
				SMOOBU_URI . 'assets/js/main.min.js',
				array( 'jquery', 'smoobu-calendar-easepick-js', 'wp-i18n' ),
				SMOOBU_VERSION,
				true
			);

		}

	}

	/**
	 * Register admin styling and scripts
	 *
	 * @return void
	 */
	public function admin_scripts() {
		global $post;
		$current_screen = get_current_screen();
		if (
			in_array(
				$current_screen->base,
				array(
					'toplevel_page_smoobu-calendar-settings',
					'smoobu-calendar_page_smoobu-calendar-settings-properties',
					'smoobu-calendar_page_smoobu-calendar-settings-renewal',
					'smoobu-calendar_page_smoobu-calendar-settings-webhook',
					'smoobu-calendar_page_smoobu-calendar-settings-styling',
					'smoobu-calendar_page_smoobu-calendar-settings-faq',
				),
				true
			) ||
			(
				( strcmp( $current_screen->base, 'post' ) === 0 ) &&
				( strcmp( $current_screen->id, 'product' ) === 0 || strcmp( $current_screen->id, 'page' ) === 0 )
			) ||
			(
				( strcmp( $current_screen->base, 'widgets' ) === 0 )
			)
		) {
			// styles.
			wp_enqueue_style( 'smoobu-calendar-admin-css', SMOOBU_URI . 'assets/css/admin/main.min.css', array(), SMOOBU_VERSION );

			// scripts.
			wp_enqueue_script( 'jquery-ui-accordion' );
			wp_enqueue_script( 'smoobu-calendar-admin-js', SMOOBU_URI . 'assets/js/admin/main.min.js', array( 'wp-color-picker', 'jquery-ui-datepicker', 'jquery', 'wp-i18n' ), SMOOBU_VERSION, true );

			wp_localize_script(
				'smoobu-calendar-admin-js',
				'smoobu_calendar_lists',
				array(
					'properties' => Smoobu_Utility::get_available_properties(),
					'layouts'    => Smoobu_Utility::get_available_layouts(),
				)
			);

			wp_localize_script(
				'smoobu-calendar-admin-js',
				'smoobu_calendar_ajax',
				array(
					'ajaxurl'                => admin_url( 'admin-ajax.php' ),
					'theme'                  => Smoobu_Utility::get_current_theme(),
					'connection_check_nonce' => wp_create_nonce( 'connection_check_nonce' ),
					'styling_nonce'          => wp_create_nonce( 'styling_nonce' ),
					'connection_success'     => __( 'Connection successful. Do not forget to save your settings.', 'smoobu-calendar' ),
					'connection_error'       => __( 'Connection failed. Error returned: ', 'smoobu-calendar' ),
				)
			);

			// front-end related scripts to show calendar preview in settings.
			// styles.
			wp_register_style( 'smoobu-calendar-css-main', SMOOBU_URI . 'assets/css/main.min.css', array(), SMOOBU_VERSION );

			// all calendar themes.
			wp_register_style( 'smoobu-calendar-css-theme-default', SMOOBU_URI . 'assets/css/default/theme.css', array(), SMOOBU_VERSION );
			wp_register_style( 'smoobu-calendar-css-theme-dark', SMOOBU_URI . 'assets/css/dark/theme.css', array(), SMOOBU_VERSION );
		}
	}


	/**
	 * Register widgets
	 *
	 * @return void
	 */
	public function widgets() {
		register_widget( 'Smoobu_Calendar_Widget' );
	}


}
