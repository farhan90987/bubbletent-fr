<?php
/**
 * Plugin Name: YayMail Pro - WooCommerce Email Customizer
 * Plugin URI: https://yaycommerce.com/yaymail-woocommerce-email-customizer/
 * Description: Create awesome transactional emails with a drag and drop email builder
 * Version: 4.2.0
 * Author: YayCommerce
 * Author URI: https://yaycommerce.com
 * Text Domain: yaymail
 * WC requires at least: 3.0.0
 * WC tested up to: 10.0
 * Domain Path: /i18n/languages/
 *
 * @package YayMail
 */

namespace YayMail;

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'YAYMAIL_PREFIX' ) ) {
    define( 'YAYMAIL_PREFIX', 'yaymail' );
}

if ( ! defined( 'YAYMAIL_DEBUG' ) ) {
    define( 'YAYMAIL_DEBUG', false );
}

if ( ! defined( 'YAYMAIL_VERSION' ) ) {
    define( 'YAYMAIL_VERSION', '4.2.0' );
}

if ( ! defined( 'YAYMAIL_PLUGIN_URL' ) ) {
    define( 'YAYMAIL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'YAYMAIL_PLUGIN_PATH' ) ) {
    define( 'YAYMAIL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'YAYMAIL_PLUGIN_BASENAME' ) ) {
    define( 'YAYMAIL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YAYMAIL_IS_DEVELOPMENT' ) ) {
    define( 'YAYMAIL_IS_DEVELOPMENT', false );
}

if ( ! defined( 'YAYMAIL_REST_NAMESPACE' ) ) {
    define( 'YAYMAIL_REST_NAMESPACE', 'yaymail/v1' );
}

$yaymail_has_required_deps = true;
if ( function_exists( 'YayMail\\init' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'templates/fallbacks/fallback-exists.php';
    $yaymail_has_required_deps = false;
}

if ( version_compare( PHP_VERSION, '7.2', '<' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'templates/fallbacks/fallback-minimum-php.php';
    $yaymail_has_required_deps = false;
}

if ( version_compare( $GLOBALS['wp_version'], '5.2', '<' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'templates/fallbacks/fallback-minimum-wp.php';
    $yaymail_has_required_deps = false;
}

if ( ! $yaymail_has_required_deps ) {
    add_action(
        'admin_init',
        function() {
            deactivate_plugins( plugin_basename( __FILE__ ) );
        }
    );

    // Return early to prevent loading the plugin.
    return;
}

require_once YAYMAIL_PLUGIN_PATH . 'vendor/autoload.php';

/**
 * Initialize constants
 */
Constants\ConstantsHandler::get_instance();

if ( ! function_exists( 'install_yaymail_admin_notice' ) ) {
    function install_yaymail_admin_notice() {
        ?>
            <div class="error">          
                <p>
                <?php
                // translators: %s: search WooCommerce plugin link
                printf( 'YayMail ' . esc_html__( 'is enabled but not effective. It requires %1$sWooCommerce%2$s in order to work.', 'yaymail' ), '<a href="' . esc_url( admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) ) . '">', '</a>' );
                ?>
                </p>
            </div>
        <?php
    }
}

if ( ! function_exists( 'YayMail\\init' ) ) {
    function init() {
        \YayMail\YayCommerceMenu\RegisterMenu::get_instance();
        \YayMail\License\LicenseHandler::get_instance();
        if ( ! function_exists( 'WC' ) ) {
            add_action( 'admin_notices', 'YayMail\\install_yaymail_admin_notice' );
        } else {
            add_action( 'before_woocommerce_init', 'YayMail\\yaymail_enable_compatible_hpos' );
            do_action( 'yaymail_before_init' );

            \YayMail\Initialize::get_instance();
        }//end if
    }
}//end if

if ( ! function_exists( 'yaymail_enable_compatible_hpos' ) ) {
    function yaymail_enable_compatible_hpos() {
        if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );

            // Set compatible for addon
            $plugins = get_plugins();
            foreach ( array_keys( $plugins ) as $key ) {
                $is_yaymail_addon = strpos( $key, 'yaymail-addon' ) !== false || strpos( $key, 'email-customizer' ) !== false;
                if ( $is_yaymail_addon ) {
                    \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $key, true );
                }
            }
        }
    }
}

if ( ! wp_installing() ) {
    add_action( 'plugins_loaded', 'YayMail\\init' );
}

register_activation_hook( __FILE__, [ \YayMail\Engine\ActDeact::class, 'activate' ] );
register_deactivation_hook( __FILE__, [ \YayMail\Engine\ActDeact::class, 'deactivate' ] );
