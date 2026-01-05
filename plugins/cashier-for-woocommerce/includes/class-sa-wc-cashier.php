<?php
/**
 * Main class for Cashier
 *
 * @package     cashier/includes/
 *  author      StoreApps
 * @since       1.0.0
 * @version     1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SA_WC_Cashier' ) ) {

	/**
	 *  Main Cashier Class.
	 *
	 * @return object of SA_WC_Cashier having all functionality of Cashier
	 */
	class SA_WC_Cashier {

		/**
		 * Variable to hold instance of Cashier
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Get single instance of Cashier.
		 *
		 * @return SA_WC_Cashier Singleton object of SA_WC_Cashier
		 */
		public static function get_instance() {

			// Check if instance is already exists.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Cloning is forbidden.
		 *
		 * @since 1.0.0
		 */
		private function __clone() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'cashier' ), '1.0.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 1.0.0
		 */
		public function __wakeup() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'cashier' ), '1.0.0' );
		}

		/**
		 * Constructor
		 */
		private function __construct() {

			$this->includes();

			add_action( 'admin_notices', array( $this, 'needs_wc37' ) );
			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'rearrange_cashier_settings_tab' ), 100 );

		}

		/**
		 * Function to handle WC compatibility related function call from appropriate class
		 *
		 * @param string $function_name Function to call.
		 * @param array  $arguments Array of arguments passed while calling $function_name.
		 * @return mixed Result of function call.
		 */
		public function __call( $function_name, $arguments = array() ) {

			if ( ! is_callable( array( 'SA_WC_Compatibility_4_1', $function_name ) ) ) {
				return;
			}

			if ( ! empty( $arguments ) ) {
				return call_user_func_array( 'SA_WC_Compatibility_4_1::' . $function_name, $arguments );
			} else {
				return call_user_func( 'SA_WC_Compatibility_4_1::' . $function_name );
			}

		}

		/**
		 * Include files
		 */
		public function includes() {

			include_once WP_PLUGIN_DIR . '/' . SA_CFW_PLUGIN_DIRNAME . '/includes/compat/class-sa-wc-compatibility-3-0.php';
			include_once WP_PLUGIN_DIR . '/' . SA_CFW_PLUGIN_DIRNAME . '/includes/compat/class-sa-wc-compatibility-3-1.php';
			include_once WP_PLUGIN_DIR . '/' . SA_CFW_PLUGIN_DIRNAME . '/includes/compat/class-sa-wc-compatibility-3-2.php';
			include_once WP_PLUGIN_DIR . '/' . SA_CFW_PLUGIN_DIRNAME . '/includes/compat/class-sa-wc-compatibility-3-3.php';
			include_once WP_PLUGIN_DIR . '/' . SA_CFW_PLUGIN_DIRNAME . '/includes/compat/class-sa-wc-compatibility-3-4.php';
			include_once WP_PLUGIN_DIR . '/' . SA_CFW_PLUGIN_DIRNAME . '/includes/compat/class-sa-wc-compatibility-3-5.php';
			include_once WP_PLUGIN_DIR . '/' . SA_CFW_PLUGIN_DIRNAME . '/includes/compat/class-sa-wc-compatibility-3-6.php';
			include_once WP_PLUGIN_DIR . '/' . SA_CFW_PLUGIN_DIRNAME . '/includes/compat/class-sa-wc-compatibility-3-7.php';
			include_once WP_PLUGIN_DIR . '/' . SA_CFW_PLUGIN_DIRNAME . '/includes/compat/class-sa-wc-compatibility-3-8.php';
			include_once WP_PLUGIN_DIR . '/' . SA_CFW_PLUGIN_DIRNAME . '/includes/compat/class-sa-wc-compatibility-3-9.php';
			include_once WP_PLUGIN_DIR . '/' . SA_CFW_PLUGIN_DIRNAME . '/includes/compat/class-sa-wc-compatibility-4-0.php';
			include_once WP_PLUGIN_DIR . '/' . SA_CFW_PLUGIN_DIRNAME . '/includes/compat/class-sa-wc-compatibility-4-1.php';

			include_once 'admin/class-sa-cfw-settings.php';

			$this->include_modules();

		}

		/**
		 * Include modules
		 */
		public function include_modules() {
			$available_modules = $this->get_available_modules();
			$enabled_modules   = $this->get_enabled_modules();
			if ( ! empty( $enabled_modules ) && is_array( $enabled_modules ) && ! empty( $available_modules ) && is_array( $available_modules ) ) {
				$intersect_modules = array_intersect( $available_modules, $enabled_modules );
				if ( ! empty( $intersect_modules ) ) {
					foreach ( $intersect_modules as $module ) {
						$is_enable = $this->is_enable_module( $module );
						if ( true === $is_enable ) {
							$module_loader = WP_PLUGIN_DIR . '/' . SA_CFW_PLUGIN_DIRNAME . '/modules/' . $module . '/php/index.php';
							if ( file_exists( $module_loader ) ) {
								include_once $module_loader;
							}
						}
					}
				}
			}
		}

		/**
		 * Available modules
		 *
		 * @return array
		 */
		public function get_available_modules() {
			$available_modules = array();
			$directories       = new DirectoryIterator( WP_PLUGIN_DIR . '/' . SA_CFW_PLUGIN_DIRNAME . '/modules' );

			if ( ! empty( $directories ) ) {
				foreach ( $directories as $directory ) {
					if ( $directory->isDir() && ! $directory->isDot() ) {
						$available_modules[] = $directory->getFilename();
					}
				}
			}
			return $available_modules;
		}

		/**
		 * Get enabled modules
		 *
		 * @return array
		 */
		public function get_enabled_modules() {
			$enabled_modules = get_option( 'sa_cfw_enabled_modules', $this->get_available_modules() );
			return $enabled_modules;
		}

		/**
		 * Re-arrange Cashier settings tab under WooCommetrce > Settings
		 *
		 * @param array $tabs Existing tabs.
		 * @return array
		 */
		public function rearrange_cashier_settings_tab( $tabs = array() ) {
			$key = 'sa-cfw-settings';
			if ( isset( $tabs[ $key ] ) ) {
				$cashier_tab = array( $key => $tabs[ $key ] );
				unset( $tabs[ $key ] );
				$tabs = array_merge( $tabs, $cashier_tab );
			}
			return $tabs;
		}

		/**
		 * Check whether to enable module or not
		 *
		 * @param boolean $module The module name.
		 * @return boolean
		 */
		public function is_enable_module( $module = '' ) {
			$is_enable = true;
			if ( ! empty( $module ) ) {
				switch ( $module ) {
					case 'buy-now':
						$active_plugins = (array) get_option( 'active_plugins', array() );
						if ( is_multisite() ) {
							$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
						}
						if ( in_array( 'woocommerce-buy-now/woocommerce-buy-now.php', $active_plugins, true ) ) {
							add_action( 'admin_notices', array( $this, 'deactivate_buy_now' ) );
							$is_enable = false;
						}
						break;
				}
			} else {
				$is_enable = false;
			}
			return apply_filters(
				'sa_cfw_is_enable_module',
				$is_enable,
				array(
					'source' => $this,
					'module' => $module,
				)
			);
		}

		/**
		 * Notify about deactivating the Buy Now plugin
		 */
		public function deactivate_buy_now() {
			?>
			<div class="updated error">
				<p>
				<?php
					echo '<strong>' . esc_html__( 'Cashier', 'cashier' ) . ': </strong>' . sprintf(
						/* translators: 1. Link for the plugin to deactivate */
						esc_html__( 'Buy Now feature of Cashier is disabled. To enable, deactivate %s.', 'cashier' ),
						'<a href="' . esc_url(
							add_query_arg(
								array(
									's'             => 'woocommerce-buy-now',
									'plugin_status' => 'active',
								),
								admin_url( 'plugins.php' )
							)
						) . '" target="_blank">' . esc_html__(
							'WooCommerce Buy Now',
							'cashier'
						) . '</a>'
					);
				?>
				</p>
			</div>
			<?php
		}

		/**
		 * Function to show admin notice that Cashier works with WC 4.0+
		 */
		public function needs_wc37() {
			if ( $this->is_wc_gte_37() ) {
				return;
			}
			?>
			<div class="updated error">
				<p>
				<?php
					echo '<strong>' . esc_html__( 'Important', 'cashier' ) . ': </strong>' . esc_html__( 'Cashier for WooCommerce is active but it will only work with WooCommerce 3.7+.', 'cashier' ) . ' <a href="' . esc_url( admin_url( 'plugins.php?plugin_status=upgrade' ) ) . '" target="_blank" >' . esc_html__( 'Please update WooCommerce to the latest version', 'cashier' ) . '</a>';
				?>
				</p>
			</div>
			<?php
		}

		/**
		 * Function to log messages generated by Cashier plugin
		 *
		 * @param  string $level   Message type. Valid values: debug, info, notice, warning, error, critical, alert, emergency.
		 * @param  string $message The message to log.
		 */
		public function log( $level = 'notice', $message = '' ) {

			if ( empty( $message ) ) {
				return;
			}

			if ( function_exists( 'wc_get_logger' ) ) {
				$logger  = wc_get_logger();
				$context = array( 'source' => 'cashier' );
				$logger->log( $level, $message, $context );
			} else {
				include_once plugin_dir_path( WC_PLUGIN_FILE ) . 'includes/class-wc-logger.php';
				$logger = new WC_Logger();
				$logger->add( 'cashier', $message );
			}

		}

		/**
		 * Function to fetch plugin's data
		 */
		public function get_plugin_data() {
			return get_plugin_data( SA_CFW_PLUGIN_FILE );
		}

	}

}
