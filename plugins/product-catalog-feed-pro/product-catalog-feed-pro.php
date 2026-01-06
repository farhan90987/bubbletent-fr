<?php
/*
Plugin Name: Product Catalog Feed Pro by PixelYourSite
Description: WooCommerce Products Feed for Facebook Product Catalog. You can create XML feeds for Facebook Dynamic Product Ads.
Plugin URI: https://www.pixelyoursite.com/product-catalog-facebook
Author: PixelYourSite
Author URI: https://www.pixelyoursite.com
Version: 5.7.1
WC requires at least: 3.0.0
WC tested up to: 10.1
Requires PHP: 7.0
Requires Plugins: woocommerce
*/
/* Following are used for updating plugin */

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

$wpwoof_main_file = __FILE__;

// Mutual-exclusion bootstrap (shared between Free/Pro)
require_once __DIR__ . '/inc/mutex.php';

// File-scope guard: stop if another edition already defined the constant.
// Must be after the mutual-exclusion bootstrap and before any defines/classes/includes.
if ( defined( 'WPWOOF_VERSION' ) ) {
    return;
}

require_once plugin_dir_path( __FILE__ ) . 'inc/scheduler.php';

if ( ! is_admin() && ! wp_doing_cron() && ! class_exists( 'WP_CLI' ) && ! ( isset( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], '/wpwoof/' ) !== false ) ) {
    return;
}
//Plugin Version
define( 'WPWOOF_VERSION', '5.7.1' );
//NOTIFICATION VERSION
define( 'WPWOOF_VERSION_NOTICE', '0.0.0' );

//Plugin Update URL
define( 'WPWOOF_SL_STORE_URL', 'https://www.pixelyoursite.com' );
//Plugin Name
define( 'WPWOOF_SL_ITEM_NAME', 'Product Catalog Feed for WooCommerce' );
define( 'WPWOOF_SL_ITEM_SHNAME', 'Product Catalog' );

//Plugin Base
define( 'WPWOOF_BASE', plugin_basename( __FILE__ ) );
//Plugin PAtH
define( 'WPWOOF_PATH', plugin_dir_path( __FILE__ ) );
//Plugin URL
define( 'WPWOOF_URL', plugin_dir_url( __FILE__ ) );
//Plugin assets URL
define( 'WPWOOF_ASSETS_URL', WPWOOF_URL . 'assets/' );
//Plugin
define( 'WPWOOF_PLUGIN', 'wp-woocommerce-feed' );

//Plugin
define( 'WPWOOF_WOO', 'woocommerce/woocommerce.php' );
define( 'WPWOOF_YSEO', 'wordpress-seo/wp-seo.php' );
define( 'WPWOOF_SMART_OGR', 'smart-opengraph/catalog-plugin.php' );
//Brands plugins
// woocommerce brands */
define( 'WPWOOF_BRAND_YWBA', 'yith-woocommerce-brands-add-on/init.php' );
define( 'WPWOOF_BRAND_PEWB', 'perfect-woocommerce-brands/main.php' );
define( 'WPWOOF_BRAND_PRWB', 'premmerce-woocommerce-brands/premmerce-brands.php' );
define( 'WPWOOF_BRAND_PBFW', 'product-brands-for-woocommerce/product-brands-for-woocommerce.php' );
define( 'WPWOOF_CURCY', 'woo-multi-currency/woo-multi-currency.php' );
define( 'WPWOOF_CURCY_PRO', 'woocommerce-multi-currency/woocommerce-multi-currency.php' );
define( 'WPWOOF_CURRN_SWTCH', 'currency-switcher-woocommerce/currency-switcher-woocommerce.php' );
define( 'WPWOOF_CURRN_SWTPR', 'currency-switcher-woocommerce-pro/currency-switcher-woocommerce-pro.php' );
define( 'WPWOOF_WCPBC', 'woocommerce-product-price-based-on-countries/woocommerce-product-price-based-on-countries.php' );
define( 'WPWOOF_ALLIMPP', 'wp-all-import-pro/wp-all-import-pro.php' );
define( 'WPWOOF_ALLIMP', 'wp-all-import/plugin.php' );

// beta functionality - works for localized feeds only now
define( 'SWS_DEV_MODE', false );
if ( SWS_DEV_MODE ) {
    require_once plugin_dir_path( __FILE__ ) . 'inc/dev_mode.php';
}

//Plugin

require_once plugin_dir_path( __FILE__ ) . 'inc/helpers.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/generate-feed.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/admin.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/feed-list-table.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/admin_notices.php';


if ( isset( $_GET['page'] ) && $_GET['page'] == "wpwoof-settings" && isset( $_GET['WPWOOF_DEBUG'] ) ) {
    $is_debug = boolval( $_GET['WPWOOF_DEBUG'] );
    update_option( 'WPWOOF_DEBUG', $is_debug );
    if ( ! $is_debug ) {
        wpwoof_cleanup_debug_files();
    }
}
define( 'WPWOOF_DEBUG', get_option( 'WPWOOF_DEBUG' ) );

if ( WPWOOF_DEBUG ) {
    if ( ! function_exists( 'trace' ) ) {
        function trace( $obj, $onexit = 0 ) {
            echo "<pre>" . print_r( $obj, true ) . "</pre>";
            if ( $onexit ) {
                exit();
            }
        }
    }
    if ( ! function_exists( 'wpwoofStoreDebug' ) ) {
        function wpwoofStoreDebug( $data, $file = null ) {
//		trace( date( 'Y-m-d H:i:s' ) . "\t" . print_r( $data, true ) . "\n" );
            if ( empty( $file ) ) {
                global $woocommerce_wpwoof_common;
                $file = $woocommerce_wpwoof_common->getDebugFile();
            }
            if ( ! empty( $file ) ) {
                file_put_contents( $file, date( 'Y-m-d H:i:s' ) . "\t" . print_r( $data, true ) . "\n", FILE_APPEND );
            }
        }
    }
//    if (!defined('SAVEQUERIES')) {
//        define( 'SAVEQUERIES', true );
//    }
}

if ( ! function_exists( 'array_key_first' ) ) {
    function array_key_first( array $arr ) {
        foreach ( $arr as $key => $unused ) {
            return $key;
        }

        return null;
    }
}

class wpwoof_product_catalog {
	static $interval = '86400';
	static $schedule = array(
		'0'      => 'never',
		'3600'   => 'hourly',
		'43200'  => 'twicedaily',
		'86400'  => 'daily',
		'604800' => 'weekly'
	);
	static $aSMartTags = array( "recent-product"/*,"top-7-days"*/, "top-30-days", "on-sale" ); /* smart tags */
	static $WWC;
	static $fields_in_products = array(
		'wpfoof-exclude-product'        => array(
			"title" => 'Exclude this product from feeds',
			// "subscription"=>'Exclude the product from feed',
			//"main" => true,
			"type"  => 'trigger',
		),
		'feed_google_category'          => array(
			'title'    => 'Google Taxonomy:',
			'type'     => 'googleTaxonomy',
			"toImport" => 'text'
		),
        'wpfoof-product_type'      => array(
			'title' => 'product_type:',
			'subscription' => 'This value is taken only if the "Use custom value" for product_type option is selected in the feed settings.',
			'type'  => 'text'
		),
		'wpfoof-mpn-name'               => array(
			"title"    => 'MPN:',
			// "subscription"=>'Manufacturer part number',
			"type"     => 'text',
			"toImport" => 'text'
		),
		'wpfoof-gtin-name'              => array(
			"title"    => 'GTIN:',
			// "subscription"=>'Global Trade Item Number(GTINs may be 8, 12, 13 or 14 digits long)',
			"type"     => 'text',
			"toImport" => 'text'
		),
		'wpfoof-brand'                  => array(
			"title" => 'Brand:',
			"type"  => 'text',
		),
		'wpfoof-auto_pricing_min_price' => array(
			"title" => 'auto_pricing_min_price:',
			"type"  => 'text',
		),
		'wpfoof-identifier_exists'      => array(
			'title'   => 'identifier_exists:',
			'type'    => 'select',
			'options' => array(
				'true'   => 'select',
				'yes'    => 'Yes',
				'output' => 'No'
			)
		),
		'wpfoof-condition'              => array(
			'title'    => 'Condition:',
			'type'     => 'select',
			'topHr'    => true,
			'options'  => array(
				''            => 'select',
				'new'         => 'new',
				'refurbished' => 'refurbished',
				'used'        => 'used'
			),
			"toImport" => 'radio'
		),
		'wpfoof-custom-title'           => array(
			"title"    => 'Custom Title:',
			"type"     => 'text',
			'topHr'    => true,
			"toImport" => 'text'
		),
		'wpfoof-custom-descr'           => array(
			"title"    => 'Custom Description:',
			"type"     => 'textarea',
			"toImport" => 'textarea',
//             "needEditor" => true
		),
		'wpfoof-custom-url'             => array(
			"title"    => 'Custom URL:',
			"type"     => 'text',
			"toImport" => 'text'
		),
		'wpfoof-carusel-box-media-name' => array(
			"title" => 'Carousel ad:',
			// "subscription"=>'(1080X1080 recommended)',
			"size"  => "1080X1080",
			"type"  => "",
			'topHr' => true
		),
		'wpfoof-box-media-name'         => array(
			"title" => 'Single product ad:',
			// "subscription"=>'(1200X628 recommended)',
			"size"  => "1200X628",
			"type"  => ""

		),
//        'wpfoof-google' => array(
//            "title" => 'Extra Custom Fields',
//            "type" => 'trigger',
//            'topHr' => true,
//            'show' => 'google',
//            "toImport" => 'trigger'
//        ),
//        'wpfoof-adsensecustom' => array(
//            "title" => 'Extra Custom Fields for Google Ads Custom Feed',
//            "type" => 'trigger',
//            'topHr' => true,
//            'show' => 'adsensecustom',
//        )
	);
	static $category_field_names = array(

		'wpfoof-exclude-category'  => array(
			'title' => 'Exclude this category from feeds',
			'type'  => 'toggle'
		),
		'wpfoof-identifier_exists' => array(
			'title'   => 'identifier_exists:',
			'type'    => 'select',
			'options' => array(
				'true'   => '',
				'yes'    => 'Yes',
				'output' => 'No'
			)
		),
		'feed_google_category'     => array(
			'title' => 'Google Taxonomy:',
			'type'  => 'googleTaxonomy'
		),
        'wpfoof-product_type'      => array(
			'title' => 'product_type:',
			'type'  => 'text'
		),
		'wpfoof-adult'             => array(
			'title'   => 'Adult:',
			'type'    => 'select',
			'options' => array(
				'no'  => 'No',
				'yes' => 'Yes'
			)
		),
		'wpfoof-shipping-label'    => array(
			'title' => 'shipping_label:',
			'type'  => 'text'
		),
		'wpfoof-tax-category'      => array(
			'title' => 'tax_category:',
			'type'  => 'text'
		)
		,
		'wpfoof-auto_pricing_min_price'      => array(
			'title' => 'auto_pricing_min_price (percent):',
			'type'  => 'text'
		)
	);

	static $tag_field_names = array(

		'wpfoof-exclude-category' => array(
			'title' => 'Exclude products with this tag from feeds',
			'type'  => 'toggle'
		),

	);

    /**
 * @var WPWOOF_REST
 */private $rest;



	function __construct() {
		/*if( ! empty( $_GET['pcbpys_license_deactivate'] ) ) {
			$_POST['pcbpys_license_deactivate'] = true;
		}*/
		global $xml_has_some_error, $woocommerce_wpwoof_common;

        if ( class_exists( 'WP_CLI' ) ) {
            require_once plugin_dir_path( __FILE__ ) . 'inc/cli-commands.php';
        }
        if ( isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/wpwoof/') !== false ) {
            require_once plugin_dir_path( __FILE__ ) . 'inc/rest.php';
            $this->rest = new WPWOOF_REST();

        }

		self::$WWC          = $woocommerce_wpwoof_common;
		$xml_has_some_error = false;
		register_activation_hook( __FILE__, array( __CLASS__, 'activate' ) );
		register_deactivation_hook( __FILE__, array( __CLASS__, 'deactivate' ) );
		add_action( 'plugins_loaded', array( __CLASS__, 'check_plugin_version_and_migrate' ) );

		add_action( 'init', array( __CLASS__, 'init' ), 90 );
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ), 90 );
		//


		// extra fields on a category form
		add_action( 'product_cat_edit_form_fields', array( __CLASS__, 'edit_extra_fields_category' ), PHP_INT_MAX, 2 );
		add_action( 'product_cat_add_form_fields', array( __CLASS__, 'add_extra_fields_category' ), PHP_INT_MAX, 2 );

		add_action( 'edited_product_cat', array( __CLASS__, 'save_extra_fields_category' ), 10, 2 );
		add_action( 'create_product_cat', array( __CLASS__, 'save_extra_fields_category' ), 10, 2 );

		add_action( 'product_tag_edit_form_fields', array( __CLASS__, 'edit_extra_fields_tag' ), PHP_INT_MAX, 2 );
		add_action( 'product_tag_add_form_fields', array( __CLASS__, 'add_extra_fields_tag' ), PHP_INT_MAX, 2 );

		add_action( 'edited_product_tag', array( __CLASS__, 'save_extra_fields_category' ), 10, 2 );
		add_action( 'create_product_tag', array( __CLASS__, 'save_extra_fields_category' ), 10, 2 );


		// extra fields on product form
		//'woocommerce_product_options_general_product_data'
		add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'woo_woof_product_tab' ), 99, 1 );
		//add_action('woocommerce_product_options_woof_tab_product_data', array(__CLASS__, 'add_extra_fields'), 10);


		add_action( 'woocommerce_product_after_variable_attributes', array(
			__CLASS__,
			'add_extra_fields_variable'
		), PHP_INT_MAX, 3 );
		add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_extra_fields' ), 10, 2 );
		add_action( 'woocommerce_save_product_variation', array( __CLASS__, 'save_extra_fields' ), 10, 2 );


		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );

		add_filter( 'cron_schedules', array( __CLASS__, 'cron_schedules' ), 1000 );
		//////////////////////////////////////

		// Declaring extension compatibility with WooCommerce High-Performance Order Storage (HPOS)
		add_action( 'before_woocommerce_init', function () {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		} );

		add_filter( 'http_request_host_is_external', array( __CLASS__, 'http_request_host_is_external' ), 10, 3 );
		if ( ! class_exists( 'WPWOOF_Plugin_Updater' ) ) {
			include plugin_dir_path( __FILE__ ) . 'inc/plugin-updater.php';
		}
		$license_status = get_option( 'pcbpys_license_status' );
		if ( $license_status == 'valid' && function_exists( 'is_plugin_active' ) && ( is_plugin_active( WPWOOF_ALLIMPP ) || is_plugin_active( WPWOOF_ALLIMP ) ) ) {
            require_once plugin_dir_path( __FILE__ ) . 'inc/import-addon.php';
		}
	}


	static function woo_woof_product_tab( $default_tabs ) {
		$default_tabs['woof_tab'] = array(
			'label'    => __( WPWOOF_SL_ITEM_SHNAME, 'feedpro' ),
			'target'   => 'woof_add_extra_fields',
			'priority' => 90,
			//'class'    =>  array('panel', 'woocommerce_options_panel')
		);
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'woof_add_extra_fields' ) );

		return $default_tabs;
	}


	static function init() {
		self::$interval = self::$WWC->getInterval();
	}

	static function set_wpwoof_disable_status() {
		check_ajax_referer( 'wpwoof_settings' );
		if ( isset( $_POST['set_wpwoof_disable_status'] ) && ! empty( $_POST['feed_id'] ) ) {
			$fid   = (int) $_POST['feed_id'];
			$main_feed_config = wpwoof_get_feed( $fid );
            if (is_wp_error($main_feed_config)) {
                wp_send_json( array( "status" => "error", "message" => $main_feed_config->get_error_message() ) );
            }
            $status = empty( $_POST['set_wpwoof_disable_status'] ) ? 0 : 1;

			if ( ! empty( $main_feed_config['feed_name'] ) ) {
				$main_feed_config['noGenAuto'] = $status;
				wpwoof_update_feed( $main_feed_config, $fid, true );
				self::schedule_feed( $main_feed_config );
			}

            if ( ! empty( $main_feed_config['localized_feeds_ids'] ) ) {
                foreach ( $main_feed_config['localized_feeds_ids'] as $feed_id ) {
                    $localized_feed_config = wpwoof_get_feed( $feed_id );
                    if (is_wp_error($localized_feed_config)) {
                        continue;
                    }
                    $localized_feed_config['noGenAuto'] = $status;
                    wpwoof_update_feed( $localized_feed_config, $feed_id, true );
                    self::schedule_feed( $localized_feed_config );
                }
            }

			wp_send_json( array( "status" => "OK" ) );

		}
		wp_die();
	}

	static function set_wpwoof_image() {
		check_ajax_referer( 'wpwoof_settings' );
		if ( isset( $_POST['wpwoof_image'] ) ) {
			self::$WWC->setGlobalImg( $_POST['wpwoof_image'] );
			exit( "OK" );
		}
		wp_die();
	}

	static function set_wpwoof_category() {
		check_ajax_referer( 'wpwoof_settings' );
		$data = array();
		if ( isset( $_POST['wpwoof_feed_google_category'] ) ) {
			$data['name'] = $_POST['wpwoof_feed_google_category'];
			self::$WWC->setGlobalGoogleCategory( $data );

		}
		exit( 'OK' );
	}

	static function wpwoof_status() {
		check_ajax_referer( 'wpwoof_settings' );
		$result = array();
		if ( isset( $_POST['wpwoof_status'] ) && isset( $_POST['feedids'] ) && ! empty( $_POST['feedids'] ) ) {
			foreach ( $_POST['feedids'] as $feed_id ) {
				$feed_id            = (int) $feed_id;
				$status         = self::$WWC->get_feed_status( $feed_id );

				$feedConfig = wpwoof_get_feed( $feed_id );
                if (is_wp_error($feedConfig)) {
                    continue;
                }
                $result[ $feed_id ] = array();
				if ( isset( $feedConfig['generated_time'] ) || ! empty( $feedConfig['generated_time'] ) ) {
					$date = new DateTime();
					$date->setTimestamp( $feedConfig['generated_time'] );
					$date->setTimezone( new DateTimeZone( self::$WWC->getWpTimezone() ) );
					$result[ $feed_id ]['timestr'] = $date->format( 'd/m/Y H:i:s' );
					$nextRun = self::$WWC->get_feed_gen_schedule($feed_id);
					if ( ! empty( $nextRun ) ) {
						$date->setTimestamp( $nextRun );
						$result[ $feed_id ]['timestr'] .= '<br>Next update:<br>' . $date->format( 'd/m/Y H:i:s' );
					}
				}

				$result[ $feed_id ]['total']           = $status['total_products'];
				$result[ $feed_id ]['processed']       = $status['parsed_products'];
				$result[ $feed_id ]['show_loader']     = $status['show_loader'];
                $file_name = isset( $feedConfig['feed_file_name'] ) ? sanitize_text_field( $feedConfig['feed_file_name'] ) : strtolower( str_replace( ' ', '-', trim( $feedConfig['feed_name'] ) ) );
				$upload_dir                        = wpwoof_feed_dir( $file_name, $feedConfig['feed_type'] == "adsensecustom" ? 'csv' : 'xml' );
				$result[ $feed_id ]['hideFeedButtons'] = ! file_exists( $upload_dir['path'] );
				if ( ! empty( $feedConfig['status_feed'] ) && ! in_array( $feedConfig['status_feed'], array(
						'finished',
						'starting'
					) ) ) {
					$result[ $feed_id ]['error'] = $feedConfig['status_feed'];
				}
			}
		}
        wp_send_json( $result );
	}

	static function wpwoof_addfeed_submit() {
		check_ajax_referer( 'wpwoof_settings' );
		$values = $_POST;
		unset( $values['wpwoof_addfeed_submit'] );
		unset( $values['action'] );
		$values['added_time'] = time();
        if ( ! empty( $_POST['edit_feed'] ) ) {
            $old_feed_setting = wpwoof_get_feed( (int)$_POST['edit_feed'] );
            if (is_wp_error($old_feed_setting)) {
                wp_send_json( array( "status" => "error", "message" => $old_feed_setting->get_error_message() ) );
            }
            $existingNames = self::get_all_existing_feed_name_and_file_name();
            if ($old_feed_setting['feed_name'] != $values['feed_name']) {
                $values['feed_name'] = self::make_unique_name ($values['feed_name'] , $existingNames['feed_names'], true);
                wpwoof_update_feed( $values, (int) $_POST['edit_feed'], false, $values['feed_name'] );
            }
            if (!isset($old_feed_setting['feed_file_name'])) {
                $old_feed_setting['feed_file_name'] = strtolower( str_replace( ' ', '-', trim( $old_feed_setting['feed_name'] ) ) );
            }

            if ( (!empty($values['feed_file_name']) || $values['feed_file_name'] === 0) && $old_feed_setting['feed_file_name'] != $values['feed_file_name'] ) {
                $values['feed_file_name'] = self::make_unique_name ($values['feed_name'], $existingNames['file_names']);
                wpwoof_delete_feed_file( (int) $_POST['edit_feed'] );
            } elseif ( $values['feed_file_name'] == '' ) {
                $values['feed_file_name'] = $old_feed_setting['feed_file_name'];
            }
            $values = self::merge_feed_configs( $old_feed_setting, $values );
            wpwoof_create_feed( $values );
			update_option( 'wpwoof_message', 'Feed Updated Successfully.' );
        } else {
            $existingNames = self::get_all_existing_feed_name_and_file_name();
            if (in_array($values['feed_name'], $existingNames['feed_names'])) {
                $values['feed_name'] = self::make_unique_name ($values['feed_name'] , $existingNames['feed_names'], true);
            }
            if ( ( empty($values['feed_file_name']) && $values['feed_file_name'] !== 0 ) || in_array( $values['feed_file_name'],$existingNames['file_names']) ) {
                $values['feed_file_name'] = self::make_unique_name ($values['feed_file_name'], $existingNames['file_names']);
            }

			if ( update_option( 'wpwoof_feedlist_' . esc_sql($values['feed_name']), $values ) ) {
				global $wpdb;
				$sql    = "SELECT * FROM $wpdb->options WHERE option_name = 'wpwoof_feedlist_" . esc_sql( $values['feed_name'] ) . "' Limit 1";
				$result = $wpdb->get_results( $sql, 'ARRAY_A' );
				if ( count( $result ) == 1 ) {
					$values['edit_feed'] = $result[0]['option_id'];
					wpwoof_create_feed( $values );
				}
			}
		}
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
            self::$WWC->run_scheduled_feeds();
        }
        exit();
	}

	/**
 * Retrieves all existing feed names and file names from the database.
 *
 * @return array An associative array containing the feed names and file names.
 */static function get_all_existing_feed_name_and_file_name() {
		global $wpdb;

		$existing_names = array('feed_names'=> array(), 'file_names'=>array());
		$sql     = "SELECT option_value FROM $wpdb->options WHERE option_name LIKE 'wpwoof_feedlist_%'";
		$res     = $wpdb->get_results( $sql, 'ARRAY_A' );
		foreach ( $res as $val ) {
            $feed = unserialize($val['option_value']);
            if (empty($feed['feed_name'])) {continue;}
			$existing_names['feed_names'][] = sanitize_text_field($feed['feed_name']);
			$existing_names['file_names'][] = isset( $feed['feed_file_name'] ) ? sanitize_text_field( $feed['feed_file_name'] ) : strtolower( str_replace( ' ', '-', trim( $feed['feed_name'] ) ) );
		}
        return $existing_names;
	}


    /**
 * Function to create a unique name by adding or incrementing a suffix.
 *
 * @param string $name The initial name that needs to be made unique.
 * @param array $array The array with existing names to check the uniqueness against.
 * @param bool $case_sensitive Optional. Whether the comparison should be case-sensitive. Defaults to false.
 *
 * @return string Unique version of the initial name.
 */
    static function make_unique_name($name, $array, $case_sensitive = false) {

        // Checks if there is a number suffix and extracts it
        if (preg_match('/(.*?)-?(\d+)$/', $name, $matches)) {
            $base_name = $matches[1] === "" ? $matches[2] : $matches[1];
            $suffix = $matches[1] === "" ? 0 : (int)$matches[2];
        } else {
            $base_name = $name;
            $suffix = 0;
        }

        $new_name = $name;

        // Checks if the new name exists in the array, considering case sensitivity
        if (! $case_sensitive) {
            $array = array_map('strtolower', $array);
        }
        while (in_array($case_sensitive ? $new_name : strtolower($new_name), $array)) {
            $suffix++;
            $new_name = $base_name . (($suffix == 0) ? '' : '-' . $suffix);
        }

        return $new_name;
    }

    /**
     * Merges two feed configuration arrays by copying specific keys from the old configuration
     * to the new configuration if they are not already set in the new configuration.
     *
     * @param array $old_config The old configuration array.
     * @param array $new_config The new configuration array.
     *
     * @return array The merged configuration array.
     */
    static function merge_feed_configs(array $old_config, array $new_config):array {
        $accepted_keys = array('generated_time', 'added_time', 'total_products', 'localized_feeds_ids');
        foreach ($accepted_keys as $key) {
            if (isset($old_config[$key]) && !isset($new_config[$key])) {
                $new_config[$key] = $old_config[ $key ];
            }
        }
        return $new_config;
    }

	static function set_wpwoof_global_data() {
		check_ajax_referer( 'wpwoof_settings' );
        $old_global_settings = self::$WWC->getGlobalData();
		$data            = array(
			'extra'       => isset( $_POST['extra'] ) ? (array) $_POST['extra'] : array(),
			'brand'       => isset( $_POST['brand'] ) ? (array) $_POST['brand'] : array(),
			'tmp_storage' => isset( $_POST['tmp_storage'] ) ? sanitize_text_field( $_POST['tmp_storage'] ) : 'disk',
			'on_save_feed_action' => isset( $_POST['on_save_feed_action'] ) ? sanitize_text_field( $_POST['on_save_feed_action'] ) : 'save_and_regenerate_main',
			'regeneration_method' => isset( $_POST['regeneration_method'] ) ? sanitize_text_field( $_POST['regeneration_method'] ) : 'wp-cron',
			'add_no_cache_to_url' => isset( $_POST['add_no_cache_to_url'] ) ? (int) $_POST['add_no_cache_to_url'] : 0,
		);
		$deleteCacheFlag = false;
		if ( ! empty( $data['extra'] ) ) {
			foreach ( $data['extra'] as $key => $value ) {
				if ( isset( $value['feed_type']['mapping'] ) ) {
					$deleteCacheFlag = true;
				}
				if ( isset( $value['editor_value'] ) && $value['value'] == 'custom_value_editor' ) {
					$data['extra'][ $key ]['custom_value'] = stripslashes( $data['extra'][ $key ]['editor_value'] );
//                     echo $data['extra'][$key]['custom_value'].PHP_EOL;
					unset( $data['extra'][ $key ]['editor_value'] );
				}
			}
			if ( $deleteCacheFlag ) {
				delete_transient( 'wpwoof_custom_fields' );
			}
		}
		self::$WWC->setGlobalData( $data );

        if ( $old_global_settings['regeneration_method'] != $data['regeneration_method'] ) {
            if ($data['regeneration_method'] == 'wp-cron') {
                wp_unschedule_hook( 'wpwoof_feed_update' );
                wp_schedule_event( time(), 'every_minute', 'wpwoof_feed_update');
            } else {
                wp_unschedule_hook( 'wpwoof_feed_update' );
            }
        }

		exit( 'OK' );

	}

	static function set_wpwoof_schedule() {
		check_ajax_referer( 'wpwoof_settings' );
		if ( isset( $_POST['wpwoof_schedule'] ) ) {
			$option = $_POST['wpwoof_schedule'];

			if ( ! empty( self::$schedule[ $option ] ) ) {
				self::$interval = $option;
				self::$WWC->setInterval( self::$interval );
				if ( isset( $_POST['wpwoof_schedule_from'] ) ) {
					update_option( 'wpwoof_schedule_from', sanitize_text_field( $_POST['wpwoof_schedule_from'] ) );
				}

                self::reschedule_active_feeds();
				exit( 'OK' );
			}

		}
		wp_die();
	}

	static protected function _is_canRun() {
		if ( is_user_logged_in() ) {
			$roles_selected = get_option( 'wpwoof_permissions_role', array( 'administrator' ) );
			$user           = wp_get_current_user();
			if ( is_super_admin( $user->ID ) ) {
				return true;
			}

			$roles = ( array ) $user->roles;
			foreach ( $roles as $r ) {
				if ( in_array( $r, $roles_selected ) ) {
					return true;
				}
			}
		}

		return false;
	}

	static function admin_init() {
		// retrieve our license key from the DB
		$license_key = trim( get_option( 'pcbpys_license_key' ) );
		// setup the updater
		$edd_updater = new WPWOOF_Plugin_Updater( WPWOOF_SL_STORE_URL, __FILE__, array(
			'version'   => WPWOOF_VERSION,      // current version number
			'license'   => $license_key,        // license key (used get_option above to retrieve from DB)
			'item_name' => WPWOOF_SL_ITEM_NAME, // name of this plugin
			'author'    => 'PixelYourSite'      // author of this plugin
		) );
        $global_settings = self::$WWC->getGlobalData();
		global $wpdb, $wpwoof_values, $wpwoof_add_button, $wpwoof_add_tab, $wpwoof_message, $wpwoofeed_oldname;
		$wpwoof_values     = array();
		$wpwoof_add_button = isset($global_settings['on_save_feed_action']) && $global_settings['on_save_feed_action'] == 'save'?'Save':'Save & Generate';
		$wpwoof_add_tab    = 'Add New Feed';
		$wpwoof_message    = '';
		$wpwoofeed_oldname = '';


		if ( self::_is_canRun() ) {

			add_action( 'wp_ajax_set_wpwoof_disable_status', array( __CLASS__, 'set_wpwoof_disable_status' ) );
			add_action( 'wp_ajax_set_wpwoof_category', array( __CLASS__, 'set_wpwoof_category' ) );
			add_action( 'wp_ajax_set_wpwoof_image', array( __CLASS__, 'set_wpwoof_image' ) );
			add_action( 'wp_ajax_set_wpwoof_schedule', array( __CLASS__, 'set_wpwoof_schedule' ) );
			add_action( 'wp_ajax_set_wpwoof_global_data', array( __CLASS__, 'set_wpwoof_global_data' ) );
			add_action( 'wp_ajax_wpwoof_addfeed_submit', array( __CLASS__, 'wpwoof_addfeed_submit' ) );
			add_action( 'wp_ajax_wpwoof_status', array( __CLASS__, 'wpwoof_status' ) );
			add_action( 'wp_ajax_wpwoof_notice_action', array( __CLASS__, 'notice_action' ) );

			if ( $global_settings['regeneration_method'] == 'wp-cron' && ! self::$WWC->checkSchedulerStatus() ) {
				add_action( 'admin_notices', array( __CLASS__, 'showSchedulerError' ) );

			}
			$notice_actions = get_option( 'wpwoof_notice_actions', array() );

			// #3651 Notification 1 for CoG integration
			if ( ! empty( $notice_actions['cog_integrated'] ) && empty( $notice_actions['dismiss_cog_1'] )
			     && ( time() - $notice_actions['cog_integrated'] > 86400 ) && ! file_exists( WP_PLUGIN_DIR . '/pixel-cost-of-goods/pixel-cost-of-goods.php' ) ) {
				add_action( 'admin_notices', array( __CLASS__, 'show_COG_notification_1' ) );
			}

			// #3651 Notification 2 for CoG integration
			if ( ! empty( $notice_actions['dismiss_cog_1'] ) && empty( $notice_actions['dismiss_cog_2'] )
			     && ( time() - $notice_actions['dismiss_cog_1'] > 604800 ) && ! file_exists( WP_PLUGIN_DIR . '/pixel-cost-of-goods/pixel-cost-of-goods.php' ) ) {
				add_action( 'admin_notices', array( __CLASS__, 'show_COG_notification_2' ) );
			}


			if ( ! isset( $_REQUEST['page'] ) || $_REQUEST['page'] != 'wpwoof-settings' ) {
				return;
			}

			$nonce = isset( $_REQUEST['_wpnonce'] ) ? wp_verify_nonce( $_REQUEST['_wpnonce'], 'wooffeed-nonce' ) : false;


			if ( $nonce && ! empty( $_REQUEST['delete'] ) ) {
				$id      = (int) $_REQUEST['delete'];
				$deleted = wpwoof_delete_feed( $id );

                if ( ! is_wp_error( $deleted ) ) {
					wp_cache_flush();
					update_option( 'wpwoof_message', 'Feed deleted successfully.' );
					$wpwoof_message = 'success';
				} else {
					update_option( 'wpwoof_message', 'Failed to delete the feed: ' . $deleted->get_error_message() );
					$wpwoof_message = 'error';
				}
                $redirect_url =  add_query_arg( array(
                    'show_msg' => 'true',
                    'wpwoof_message' => $wpwoof_message,
                ), menu_page_url( 'wpwoof-settings', false ) );
                wp_redirect($redirect_url);
                exit();

			} else if ( $nonce && ! empty( $_REQUEST['feed_type'] ) ) {
                if ( in_array( $_REQUEST['feed_type'], array(
                    'fb_localize',
                    'fb_country',
                    'google_local_inventory'
                )))  {
                    $main_feed = wpwoof_get_feed( (int) $_REQUEST['main-feed'] );
                    if (is_wp_error($main_feed)) {
                        update_option( 'wpwoof_message', 'Failed to create localized the feed: ' . $main_feed->get_error_message() );
                        $redirect_url =  add_query_arg( array(
                            'show_msg' => 'true',
                            'wpwoof_message' => 'error',
                        ), menu_page_url( 'wpwoof-settings', false ) );
                        wp_redirect($redirect_url);
                        exit();
                    }
                    $wpwoof_values = array(
                      'feed_type' => $_REQUEST['feed_type'],
                      'main_feed_id' => (int) $_REQUEST['main-feed'],
                      'main_feed_name' => $main_feed['feed_name'],
                      'country_code' => !empty($_REQUEST['country_code'])?$_REQUEST['country_code']:''
                    );
                    $lang = !empty($_REQUEST['lang'])?esc_html($_REQUEST['lang']):"";
                    if (!empty($lang)) {
                        $wpwoof_values['feed_name'] = $main_feed['feed_name'] . ' - ' . $lang;
                        $wpwoof_values['feed_file_name'] = strtolower( str_replace( ' ', '-', trim( $main_feed['feed_name'] ) ). '-' . esc_html($_REQUEST['lang']) );
                        $wpwoof_values['feed_use_lang'] = $lang;
                    }
                    if (!empty($_REQUEST['currency'])) {
                        $wpwoof_values['feed_name'] .= ' - ' . esc_html($_REQUEST['currency']);
                        $wpwoof_values['feed_file_name'] .= '-' . strtolower(esc_html($_REQUEST['currency']));
                        $wpwoof_values['feed_use_currency'] = esc_html($_REQUEST['currency']);
                    }

                }
			} else if ( $nonce && ! empty( $_REQUEST['edit'] )  ) {
				$option_id                  = (int) $_REQUEST['edit'];
				$wpwoof_values              = wpwoof_get_feed( $option_id );
                if ( is_wp_error( $wpwoof_values ) ) {
					update_option( 'wpwoof_message', 'Failed to edit the feed: ' . $wpwoof_values->get_error_message() );
                    $redirect_url =  add_query_arg( array(
                        'show_msg' => 'true',
                        'wpwoof_message' => 'error',
                    ), menu_page_url( 'wpwoof-settings', false ) );
                    wp_redirect($redirect_url);
                    exit();
                }
				$wpwoof_values['edit_feed'] = $option_id;
				$wpwoofeed_oldname          = isset( $wpwoof_values['feed_name'] ) ? $wpwoof_values['feed_name'] : '';
				$wpwoof_add_tab             = 'Edit Feed : ' . $wpwoof_values['feed_name'];
                $wpwoof_add_button          = 'Update the Feed';
                if ( in_array( $wpwoof_values['feed_type'] , array(
                    'fb_localize',
                    'fb_country',
                    'google_local_inventory'
			    )))  {
                    $main_feed = wpwoof_get_feed( (int) $wpwoof_values['main_feed_id'] );
                    if ( ! is_wp_error( $main_feed ) ) {
                        $wpwoof_values['main_feed_name'] = $main_feed['feed_name'];
                    }
                }
			} else if ( $nonce && ! empty( $_REQUEST['update'] ) ) {
				$option_id     = (int) $_REQUEST['update'];
				$wpwoof_values = wpwoof_get_feed( $option_id );
                if ( is_wp_error( $wpwoof_values ) ) {
                    wp_send_json( array( "status" => "error", "message" => $wpwoof_values->get_error_message() ) );
                }

				$wpwoof_values['edit_feed'] = $option_id;
				self::schedule_feed( $wpwoof_values, time() );
                header('Content-Type: application/json');
                echo json_encode( array( "status" => "OK" ) );
                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                    self::$WWC->run_scheduled_feeds();
                }
                exit();
			} else if ( $nonce && ! empty( $_REQUEST['copy'] ) ) {
				$option_id     = $_REQUEST['copy'];
				$wpwoof_values = wpwoof_get_feed( $option_id );
                if ( is_wp_error( $wpwoof_values ) ) {
					update_option( 'wpwoof_message', 'Failed to duplicate the feed: ' . $wpwoof_values->get_error_message() );
                    $redirect_url =  add_query_arg( array(
                        'show_msg' => 'true',
                        'wpwoof_message' => 'error',
                    ), menu_page_url( 'wpwoof-settings', false ) );
                    wp_redirect($redirect_url);
                    exit();
                }
				unset( $wpwoof_values['edit_feed'] );
				unset( $wpwoof_values['localized_feeds_ids'] );
				unset( $wpwoof_values['feed_file_name'] );
				$aExists     = array();
				$copy_suffix = " - Copy ";
				$sql         = "SELECT option_name FROM $wpdb->options WHERE option_name LIKE 'wpwoof_feedlist_" . sanitize_text_field( $wpwoof_values['feed_name'] . $copy_suffix ) . "%'";
				$res         = $wpdb->get_results( $sql, 'ARRAY_A' );
				foreach ( $res as $val ) {
					$aExists[] = $val['option_name'];
				}
				$ind = 0;
				do {
					$ind ++;
					$feed_name = sanitize_text_field( 'wpwoof_feedlist_' . $wpwoof_values['feed_name'] . $copy_suffix . $ind );
				} while ( array_search( $feed_name, $aExists ) !== false );
				$wpwoof_values['feed_name']  = $wpwoof_values['old_feed_name'] = $wpwoof_values['feed_name'] . $copy_suffix . $ind;
				$wpwoof_values['noGenAuto']  = 1;
				$wpwoof_values['added_time'] = time();
//                trace($wpwoof_values);
				wpwoof_create_feed( $wpwoof_values );
                update_option( 'wpwoof_message', 'The feed is duplicated successfully.' );
                $redirect_url =  add_query_arg( array(
                    'show_msg' => 'true',
                    'wpwoof_message' => 'success',
                ), menu_page_url( 'wpwoof-settings', false ) );
                wp_redirect($redirect_url);
                exit();
			}

            // #3809, #3811 fix: feeds not generating after update to v.5.6
            if ( $global_settings['regeneration_method'] == 'wp-cron' ) {
                // Check if the hook is already scheduled
                $current_schedule = wp_get_schedule('wpwoof_feed_update');

                // Only reschedule if the hook is not set to run every minute
                if ($current_schedule !== 'every_minute') {
                    wp_unschedule_hook('wpwoof_feed_update');
                    wp_schedule_event(time(), 'every_minute', 'wpwoof_feed_update');
                    self::reschedule_active_feeds();
                }
            }
		} //current_user_can('administrator')
	}

	static function showSchedulerError() {
		echo '<div class="notice notice-error is-dismissible"> <p><b>' . WPWOOF_SL_ITEM_NAME . '</b>: Feeds won\'t be generated if your WordPress Cron is disabled, or if your website is password protected. </p></div>';
	}

	static function show_COG_notification_1() {
		echo '<div class="notice notice-info is-dismissible wpwoof-notice-active" data-name="dismiss_cog_1"> <p><b>' . WPWOOF_SL_ITEM_NAME . '</b>: Send [cost_of_goods_sold] to Google Merchant to get additional reporting on gross profit. Use this plugin to add the cost to your products: <a href="https://www.pixelyoursite.com/plugins/woocommerce-cost-of-goods?utm_source=feed-plugin&utm_medium=feed-plugin-notification1&utm_campaign=feed-plugin-notification1">WooCommerce Cost of Goods</a>.</p></div>';
	}

	static function show_COG_notification_2() {
		echo '<div class="notice notice-info is-dismissible wpwoof-notice-active" data-name="dismiss_cog_2"> <p><b>' . WPWOOF_SL_ITEM_NAME . '</b>: Add [cost_of_goods_sold] to your Google Merchant feed to get additional reporting on gross profit. Use this plugin to add the cost to your products: <a href="https://www.pixelyoursite.com/plugins/woocommerce-cost-of-goods?utm_source=feed-plugin&utm_medium=feed-plugin-notification2&utm_campaign=feed-plugin-notification2">WooCommerce Cost of Goods</a>.</p></div>';
	}


	static function notice_action() {
		check_ajax_referer( 'wpwoof_settings' );
		if ( ! empty( $_POST['element'] ) && in_array( $_POST['element'], array(
				'dismiss_cog_1',
				'dismiss_cog_2'
			) ) ) {
			$notice_actions                      = get_option( 'wpwoof_notice_actions', array() );
			$notice_actions[ $_POST['element'] ] = time();
			update_option( 'wpwoof_notice_actions', $notice_actions, 'no' );
			header( 'Content-Type: application/json' );
			exit( json_encode( array( "status" => "OK" ) ) );
		}
		wp_die();
	}

	static function admin_menu() {
		if ( ! self::_is_canRun() ) {
			return;
		}
		add_menu_page( 'Product Catalog', 'Product Catalog Pro', 'manage_feedpro', 'wpwoof-settings', array(
			__CLASS__,
			'menu_page_callback'
		), WPWOOF_URL . '/assets/img/favicon.png' );
	}

	static function menu_page_callback() {
        global $wpwoof_values;
        if ( ! is_wp_error( $wpwoof_values ) ) {
            require_once plugin_dir_path( __FILE__ ) . 'view/admin/settings.php';
        }
	}

	static function admin_enqueue_scripts() {
		wp_enqueue_style( WPWOOF_PLUGIN . '-fastselect', WPWOOF_ASSETS_URL . 'css/fastselect.min.css', array(), WPWOOF_VERSION, false );
		wp_enqueue_script( WPWOOF_PLUGIN . '-fastselect', WPWOOF_ASSETS_URL . 'js/fastselect.min.js', array( 'jquery' ), WPWOOF_VERSION, false );
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'wpwoof-settings' ) {
			//Admin Style

			wp_enqueue_style( WPWOOF_PLUGIN . '-style', WPWOOF_ASSETS_URL . 'css/admin-dashboard.css', array(), WPWOOF_VERSION, false );
            wp_enqueue_style (  'wp-jquery-ui-dialog');
			//Admin Javascript
			wp_enqueue_script( WPWOOF_PLUGIN . '-script', WPWOOF_ASSETS_URL . 'js/admin.js', array( 'jquery', 'jquery-ui-dialog' ), WPWOOF_VERSION, false );
			wp_enqueue_script( WPWOOF_PLUGIN . '-optionTree', WPWOOF_ASSETS_URL . 'js/jquery.optionTree.js', array( 'jquery' ), WPWOOF_VERSION, false );

			wp_enqueue_script( 'jquery.inputmask.bundle.min.js', WPWOOF_ASSETS_URL . 'js/jquery.inputmask.bundle.min.js', array( 'jquery' ), '4.0.9', false );

			wp_enqueue_media();
			wp_enqueue_script( WPWOOF_PLUGIN . '-media-script', WPWOOF_ASSETS_URL . 'js/media.js', array( 'jquery' ), WPWOOF_VERSION, false );


			wp_localize_script( WPWOOF_PLUGIN . '-script', 'WPWOOF', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'loading' => admin_url( 'images/loading.gif' ),
				'nonce'   => wp_create_nonce( 'wpwoof_settings' )
			) );
		}
		// products list page
		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'product' ) {
			wp_enqueue_style( WPWOOF_PLUGIN . '-admin-products-list', WPWOOF_ASSETS_URL . 'css/admin-products-lis.css', array(), WPWOOF_VERSION, false );
		}
	}

	static function cron_schedules( $schedules ) {
        if (! isset($schedules['every_minute'])) {
            $schedules['every_minute'] = array(
                'interval' => 60,
                'display'  => __( 'Every minute', 'woocommerce' ),
		    );
        }
		return $schedules;
	}


    /**
 * Reschedules all active feeds by querying the database for feed configurations
 * and re-scheduling them if applicable.
 *
 * This method removes existing feed generation schedules and retrieves feed configurations
 * whose automatic generation is allowed. It then re-schedules these active feeds for processing.
 *
 * @return void This method does not return any value.
 */
    static function reschedule_active_feeds() {
		global $wpdb;

        if ( WPWOOF_DEBUG ) {
			file_put_contents( self::$WWC->feedBaseDir . 'cron-wpfeed.log', date( "Y-m-d H:i:s" ) . "\tSTART reschedule_active_feeds\n", FILE_APPEND );
		}
        self::$WWC->remove_feed_gen_schedule(-1);

        $sql    = "SELECT option_id,option_value  FROM $wpdb->options WHERE option_name LIKE 'wpwoof_feedlist_%' and option_value not like '%noGenAuto\";i:1%'";
        $result = $wpdb->get_results( $sql, 'ARRAY_A' );
        if ( ! empty( $result ) ) {
            foreach ( $result as $value ) {
                self::schedule_feed( unserialize( $value['option_value'] ) );
            }
        }

	}


	static function activate() {

        wp_unschedule_hook( 'wpwoof_feed_update' );
        $global_settings = self::$WWC->getGlobalData();
        if ( !isset($global_settings['regeneration_method']) || $global_settings['regeneration_method'] == 'wp-cron') {
            wp_unschedule_hook( 'wpwoof_feed_update' );
            wp_schedule_event( time(), 'every_minute', 'wpwoof_feed_update');
        }


		$path_upload = wp_upload_dir();
		$path_upload = $path_upload['basedir'];


		$pathes = array(
			array( 'wpwoof-feed', 'xml' ),
			array( 'wpwoof-feed', 'csv' ),
		);
		foreach ( $pathes as $path ) {
			$path_folder = $path_upload;
			foreach ( $path as $folder ) {
				$path_created = false;
				if ( is_writable( $path_folder ) ) {
					$path_folder  = $path_folder . '/' . $folder;
					$path_created = is_dir( $path_folder );
					if ( ! $path_created ) {
						$path_created = mkdir( $path_folder, 0755 );
					}
				}
				if ( ! is_writable( $path_folder ) || ! $path_created ) {
					self::deactivate_generate_error( 'Cannot create folders in uploads folder', true, true );
					die( 'Cannot create folders in uploads folder' );
				}
			}
		}
		if ( ! file_exists( $path_folder . '/wpwoof-feed/xml/.htaccess' ) ) {
			file_put_contents( $path_upload . '/wpwoof-feed/xml/.htaccess', '<ifModule mod_rewrite.c>' . PHP_EOL . 'RewriteEngine Off' . PHP_EOL . '</IfModule>' );
		}
		if ( ! file_exists( $path_folder . '/wpwoof-feed/.htaccess' ) ) {
			file_put_contents( $path_upload . '/wpwoof-feed/.htaccess', '<ifModule mod_autoindex.c>' . PHP_EOL . 'Options -Indexes' . PHP_EOL . '</IfModule>' );
		}
		global $wp_roles;
		$roles_selected = get_option( 'wpwoof_permissions_role', array( 'administrator' ) );
		foreach ( $wp_roles->roles as $role => $options ) {
			if ( in_array( $role, $roles_selected ) ) {
				$wp_roles->add_cap( $role, 'manage_feedpro' );
			}
		}

		self::$WWC->saveFileFromUrl( "http://www.google.com/basepages/producttype/taxonomy.en-US.txt", $path_upload . "/wpwoof-feed/google-taxonomy.en.txt" );

		self::check_plugin_version_and_migrate( true );

        self::reschedule_active_feeds();
	}

	static function deactivate() {
		global $wp_roles;
		wp_unschedule_hook( 'wpwoof_feed_update' );
		wp_unschedule_hook( 'wpwoof_generate_feed' );

		$roles_selected = get_option( 'wpwoof_permissions_role', array( 'administrator' ) );
		foreach ( $wp_roles->roles as $role => $options ) {
			$wp_roles->remove_cap( $role, 'manage_feedpro' );
		}
	}

	static function deactivate_generate_error( $error_message, $deactivate = true, $echo_error = false ) {
		if ( $deactivate ) {
			deactivate_plugins( array( __FILE__ ) );
		}
		if ( $error_message ) {
			$message = "<div class='notice notice-error is-dismissible'>
            <p>" . $error_message . "</p></div>";
			if ( $echo_error ) {
				echo $message;
			} else {
				add_action( 'admin_notices', create_function( '', 'echo "' . $message . '";' ), 9999 );
			}
		}
	}



	static function check_plugin_version_and_migrate( $is_activate = false ) {
		global $wpdb;
        $is_pro = false;
		$db_version_original = get_option( 'WPWOOF_DB_VERSION' );
        if ( 'pro|' . WPWOOF_VERSION == $db_version_original ) {
			return true;
		}
        update_option( 'WPWOOF_DB_VERSION', 'pro|' . WPWOOF_VERSION, false );

        if (strpos($db_version_original, "pro|") === 0) {
            $is_pro = true;
            $db_version = substr($db_version_original, 3);
        } else {
            $db_version = $db_version_original;
        }

        if ( WPWOOF_DEBUG ) {
            file_put_contents( self::$WWC->feedBaseDir . 'cron-wpfeed.log', date( "Y-m-d H:i:s" ) . "\tdbMigration '". $db_version_original."' => 'pro|" . WPWOOF_VERSION."'\n", FILE_APPEND );
        }
        if ( empty( $db_version_original ) ) {  //@since 5.2.5
			$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key regexp '^[0-9]+\-wpfoof\-' OR meta_key regexp '^[0-9]+wpfoof\-'" );
		}
        if ( !$is_pro || ($is_pro && version_compare($db_version, '5.6', '<'))) {
            if ( WPWOOF_DEBUG ) {
                file_put_contents( self::$WWC->feedBaseDir . 'cron-wpfeed.log', date( "Y-m-d H:i:s" ) . "\tdbMigration pro<5.6\n", FILE_APPEND );
            }
            wp_unschedule_hook( 'wpwoof_feed_update' );
            wp_schedule_event( time(), 'every_minute', 'wpwoof_feed_update');
            wp_unschedule_hook( 'wpwoof_generate_feed' );
            if( ! $is_activate ) {
                self::reschedule_active_feeds();
            }
        }

		//  Cost of Goods (CoG) integration
		$notice_actions = get_option( 'wpwoof_notice_actions', array() );
		if ( ! isset( $notice_actions['cog_integrated'] ) ) {
			$notice_actions['cog_integrated'] = time();
		}

	}

	static function http_request_host_is_external( $allow, $host, $url ) {
		if ( $host == 'woocommerce-5661-12828-90857.cloudwaysapps.com' ) {
			$allow = true;
		}

		return $allow;
	}

	static function add_extra_fields_tag( $term ) {
		self::add_extra_fields_category( $term, "tag" );
	}

	static function edit_extra_fields_tag( $term ) {
		self::edit_extra_fields_category( $term, "tag" );
	}

	static function edit_extra_fields_category( $term, $isTag = false ) {
		$termData = get_term_meta( $term->term_id );
		//echo "TERMDATA:";
		//trace($termData);
		wp_enqueue_script( WPWOOF_PLUGIN . '-optionTree', WPWOOF_ASSETS_URL . 'js/jquery.optionTree.js', array( 'jquery' ), WPWOOF_VERSION, false );
		wp_enqueue_script( WPWOOF_PLUGIN . '-script', WPWOOF_ASSETS_URL . 'js/admin.js', array( 'jquery' ), WPWOOF_VERSION, false );
		wp_enqueue_style( WPWOOF_PLUGIN . '-style', WPWOOF_ASSETS_URL . 'css/admin.css', array(), WPWOOF_VERSION, false );
		?>
        <!-- /table><br><br><br -->
        <tr>
            <td colspan="2"><h1>Product Catalog Feed Pro Options:</h1></td>
        </tr>
        <!-- table class="form-table" -->
		<?php
		$fields = self::get_extra_fields_category($isTag == "tag" );
		foreach ( $fields as $fieldId => $field ) {
			switch ( $field['type'] ) {
				case 'toggle':
					?>
                    <tr class="form-field">
                        <th>
                            <input name="<?php echo $fieldId; ?>" type="hidden" value="0"/>
                            <input id="<?php echo $fieldId; ?>" name="<?php echo $fieldId; ?>" type="checkbox"
                                   class="ios-switch" <?php echo( isset( $termData[ $fieldId ][0] ) && $termData[ $fieldId ][0] ? 'checked="checked"' : '' ); ?> />
                            <div class="switch"></div>
                        </th>
                        <td><label for="<?php echo $fieldId; ?>"><?php echo $field['title']; ?></label></td>
                    </tr>
					<?php
					break;
				case 'text':
					?>
                    <tr class="form-field">
                        <th><?php echo $field['title']; ?></th>
                        <td>
                            <input type='text' name="<?php echo $fieldId; ?>"
                                   value="<?php echo( isset( $termData[ $fieldId ][0] ) && $termData[ $fieldId ][0] ? $termData[ $fieldId ][0] : '' ); ?>"/>
                            <?php if ( !empty($field['message']) ) { echo '<p>'.$field['message'].'</p>'; } ?>
                        </td>
                    </tr>
					<?php
					break;
                case 'hidden':
					?>
                    <tr class="form-field">
                        <th><?php echo $field['title']; ?></th>
                        <td>
                            <input type='hidden' name="<?php echo $fieldId; ?>"
                                   value="<?php echo( isset( $termData[ $fieldId ][0] ) && $termData[ $fieldId ][0] ? $termData[ $fieldId ][0] : '' ); ?>"/>
                            <?php echo $field['message'] ?? ''; ?>
                        </td>
                    </tr>
					<?php
					break;
				case 'select':
					?>
                    <tr class="form-field">
                        <th><?php echo $field['title']; ?></th>
                        <td>
                            <select name="<?php echo $fieldId; ?>">
								<?php
								if ( isset( $field['options'] ) && $field['options'] ) {
									foreach ( $field['options'] as $key => $text ) {
										echo '<option value="' . $key . '" ' . ( isset( $termData[ $fieldId ][0] ) && $termData[ $fieldId ][0] && $termData[ $fieldId ][0] == $key ? 'selected' : '' ) . '>' . $text . '</option>';
									}
								}
								?>
                            </select>
                        </td>
                    </tr>
					<?php
					break;
				case 'googleTaxonomy':
					$textCats = isset( $termData[ $fieldId ][0] ) ? $termData[ $fieldId ][0] : "";
					?>
                    <tr class="form-field">
                        <th>
							<?php echo $field['title']; ?>
                        </th>
                        <td class="addfeed-top-value">
                            <input class="wpwoof_google_category_cat_name" type="hidden" name="<?php echo $fieldId; ?>"
                                   value="<?php echo htmlspecialchars( $textCats, ENT_QUOTES ); ?>"/>
                            <input type="text" name="wpwoof_google_category_cat" class="wpwoof_google_category_cat"
                                   value="" style='display:none;'/>
                        </td>
                    </tr>
                    <script type="text/javascript">
                        jQuery(function ($) {
                            wpwoof_taxonomyPreLoad["<?= empty( $textCats ) ? 'root' : $textCats?>"] = <?=json_encode( wpwoof_getTaxonmyByPath( $textCats ) )?>;
                            loadTaxomomy(".wpwoof_google_category_cat");
                        });
                    </script>
					<?php
					break;

			}
		}
        ?>
        <tr class="form-field"><th colspan="2"><hr></th></tr>
        <?php
		/* adding custom fields */

	}

	static function add_extra_fields_category( $term, $isTag = false ) {
		$termData = ( ! isset( $term ) || ! isset( $term->term_id ) ) ? array() : get_term_meta( $term->term_id );


		wp_enqueue_script( WPWOOF_PLUGIN . '-optionTree', WPWOOF_ASSETS_URL . 'js/jquery.optionTree.js', array( 'jquery' ), WPWOOF_VERSION, false );
		wp_enqueue_script( WPWOOF_PLUGIN . '-script', WPWOOF_ASSETS_URL . 'js/admin.js', array( 'jquery' ), WPWOOF_VERSION, false );
		wp_enqueue_style( WPWOOF_PLUGIN . '-style', WPWOOF_ASSETS_URL . 'css/admin.css', array(), WPWOOF_VERSION, false );
		?>
        <!-- /table><br><br><br -->

        <tr>
            <td colspan="2"><h1>Product Catalog Feed Pro Options:</h1></td>
        </tr>
        <!-- table class="form-table" -->
		<?php
		$fields = self::get_extra_fields_category( $isTag == "tag" );
		foreach ( $fields as $fieldId => $field ) {
			switch ( $field['type'] ) {
				case 'toggle':
					?>
                    <div class="form-field">
                        <input name="<?php echo $fieldId; ?>" type="hidden" value="0"/>
                        <label for="<?php echo $fieldId; ?>"><?php echo $field['title']; ?></label>
                        <input id="<?php echo $fieldId; ?>" name="<?php echo $fieldId; ?>" type="checkbox"
                               class="ios-switch" <?php echo( isset( $termData[ $fieldId ][0] ) && $termData[ $fieldId ][0] ? 'checked="checked"' : '' ); ?> />
                        <div class="switch"></div>
                    </div>
					<?php
					break;
				case 'text':
					?>
                    <div class="form-field">
                        <label><?php echo $field['title']; ?></label>
                        <input type='text' name="<?php echo $fieldId; ?>"
                               value="<?php echo( isset( $termData[ $fieldId ][0] ) && $termData[ $fieldId ][0] ? $termData[ $fieldId ][0] : '' ); ?>"/>
                        <?php if(!empty($field['message'])) { echo '<p>'.$field['message'].'</p>'; } ?>
                    </div>
					<?php
					break;
                case 'hidden':
					?>
                    <div class="form-field">
                        <label><?php echo $field['title']; ?></label>
                        <input type='hidden' name="<?php echo $fieldId; ?>"
                               value="<?php echo( isset( $termData[ $fieldId ][0] ) && $termData[ $fieldId ][0] ? $termData[ $fieldId ][0] : '' ); ?>"/>
                        <?php echo $field['message'] ?? ''; ?>
                    </div>
					<?php
					break;
				case 'select':
					?>
                    <div class="form-field">
                        <label><?php echo $field['title']; ?></label>
                        <select name="<?php echo $fieldId; ?>">
							<?php
							if ( isset( $field['options'] ) && $field['options'] ) {
								foreach ( $field['options'] as $key => $text ) {
									echo '<option value="' . $key . '" ' . ( isset( $termData[ $fieldId ][0] ) && $termData[ $fieldId ][0] && $termData[ $fieldId ][0] == $key ? 'selected' : '' ) . '>' . $text . '</option>';
								}
							}
							?>
                        </select>
                    </div>
					<?php
					break;
				case 'googleTaxonomy':
					$textCats = isset( $termData[ $fieldId ][0] ) ? $termData[ $fieldId ][0] : "";
					?>
                    <div class="form-field">
                        <label>
							<?php echo $field['title']; ?>
                        </label>
                        <div>
                            <input class="wpwoof_google_category_cat_name" type="hidden" name="<?php echo $fieldId; ?>"
                                   value="<?php echo htmlspecialchars( $textCats, ENT_QUOTES ); ?>"/>
                            <input type="text" name="wpwoof_google_category" class="wpwoof_google_category_cat" value=""
                                   style='display:none;'/>
                        </div>
                    </div>
                    <script type="text/javascript">
                        jQuery(function ($) {
                            wpwoof_taxonomyPreLoad["<?= empty( $textCats ) ? 'root' : $textCats?>"] = <?=json_encode( wpwoof_getTaxonmyByPath( $textCats ) )?>;
                            loadTaxomomy(".wpwoof_google_category_cat");
                        });
                    </script>
					<?php
					break;

			}
		}
        ?>
        <div class="form-field"><hr></div>
        <?php
	}

	static function save_extra_fields_category( $term_id ) {

		$term   = get_term( $term_id );
		$fields = $term->taxonomy == "product_tag" ? self::$tag_field_names : self::$category_field_names;
		foreach ( $fields as $fieldId => $field ) {
			if ( isset( $_POST[ $fieldId . "_id" ] ) ) {
				update_term_meta( $term_id, $fieldId . "_id", $_POST[ $fieldId . "_id" ] );
			}
			if ( isset( $_POST[ $fieldId ] ) ) {
				update_term_meta( $term_id, $fieldId, $_POST[ $fieldId ] );
			}
		}
	}

	/**
     * Retrieves the extra fields for categories or tags, with specific field adjustments
     * based on the context (e.g., category or tag) and the status of the "Cost of Goods" (CoG) integration.
     *
     * @param bool $isTag Determines whether to retrieve fields for tags (true) or categories (false).
     *
     * @return array An array of fields tailored to either categories or tags,
     *               including adjustments based on CoG integration status.
     */
    static function get_extra_fields_category( bool $isTag ):array {

        $fields = $isTag? self::$tag_field_names : self::$category_field_names;

        //  #3816 Cost of Goods (CoG) integration. Hide fields if CoG is not activated and show a message
        $auto_pricing_min_price_field = self::$WWC::get_message_and_status_for_auto_pricing_min_price_field();
        $fields['wpfoof-auto_pricing_min_price']['type'] = $auto_pricing_min_price_field['type'];
        $fields['wpfoof-auto_pricing_min_price']['message'] = $auto_pricing_min_price_field['message'];

        return $fields;
	}

        static function get_fields_in_products( ):array {

        $fields = self::$fields_in_products;

        //  #3816 Cost of Goods (CoG) integration. Hide fields if CoG is not activated and show a message
        $auto_pricing_min_price_field = self::$WWC::get_message_and_status_for_auto_pricing_min_price_field();
        $fields['wpfoof-auto_pricing_min_price']['type'] = $auto_pricing_min_price_field['type'];
        $fields['wpfoof-auto_pricing_min_price']['message'] = $auto_pricing_min_price_field['message'];

        return $fields;
	}

	static function add_extra_fields_variable( $loop, $variation_data, $post ) {
		?>
        <div class="woocommerce_variable_attributes product-catalog-feed-pro">
        <br><strong class="woof-extra-title">Product Catalog Feed Options for Variable:</strong>
        <br><br> You must configure shipping from inside your Google Merchant account - <a target="_blank"
                                                                                           href="https://support.google.com/merchants/answer/6069284">help</a>
		<?php
		self::extra_fields_box_func( $post, $loop, $variation_data );
		?><hr></div><?php
	}

	static function woof_add_extra_fields() {
		global $post;
		?>
        <div id="woof_add_extra_fields" class="panel woocommerce_options_panel"
             style="display:none;"><?php /* class="woocommerce_options_panel" */ ?>
        <p><strong class="woof-extra-title">&nbsp;&nbsp;Product Catalog Feed Options:</strong></p>
        <p>You must configure shipping from inside your Google Merchant account - <a target="_blank"
                                                                                     href="https://support.google.com/merchants/answer/6069284">help</a>
        </p>
		<?php
		self::extra_fields_box_func( $post );
		//trace(self::$tabs);
		?></div><?php
		/*  add_meta_box( 'extra_fields', 'Product Catalog Feed Ads Images',array(__CLASS__, 'extra_fields_box_func'), 'product', 'normal', 'high'  );*/
	}

	static function save_extra_fields( $post_id, $post ) {
		$loop = is_int( $post ) ? $post : false;

		if ( ! isset( $_POST['wpwoof_nonce_name'] ) ) //make sure our custom value is being sent
		{
			return;
		}
		if ( ! wp_verify_nonce( $_POST['wpwoof_nonce_name'], 'wpwoof_nonce_action' ) ) //verify intent
		{
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) //no auto saving
		{
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) //verify permissions
		{
			return;
		}
//        exit(print_r($_POST,true));
		if ( ! isset( $_POST['wpfoof-box-media']['extra'] ) ) {
			delete_post_meta( $post_id, 'wpwoofextra' );
		}
		if ( ! isset( $_POST['wpfoof-box-media'] ) ) {
			return;
		}


		$new_value = $_POST['wpfoof-box-media']; //array_map( 'trim', $_POST['wpfoof-box-media'] ); //sanitize

//        if(isset( $_POST["feed_google_category"][$post_id."-feed_google_category"] )){
//            //kostyl for js optiontree
//
//            $new_value[$post_id."-feed_google_category"]    = $_POST["feed_google_category"][$post_id."-feed_google_category"];
//        }

		//trace($new_value,true);

		foreach ( $new_value as $k => $v ) {
//            $k = str_replace($post_id."-","",$k);
//            $k = str_replace("0-","",$k);
			$data = $loop === false ? $v : $v[ $loop ];
			if ( $k == 'extra' ) {
				update_post_meta( $post_id, 'wpwoofextra', $data );
				if ( ! empty( $data ) ) {
					foreach ( $data as $value ) {
						if ( isset( $value['feed_type']['mapping'] ) ) {
							delete_transient( 'wpwoof_custom_fields' );
							break;
						}
					}
				}

			} else {
				$old_val = get_post_meta( $post_id, $k, true );
				if ( is_string( $old_val ) ) {
					$old_val = trim( $old_val );
				}
				$data = trim( (string) $data );
				if ( $old_val != $data || ! empty( $data ) ) {
					update_post_meta( $post_id, $k, $data );
				} //save
			}
			//else { delete_post_meta( $post_id, $k); }
		}

	}

	static function extra_fields_box_func( $post, $loop = false, $variation_data = false ) {
		global $woocommerce_wpwoof_common;
		$editorsId4init = array();
		$isMain         = $loop === false;
		$loopStr        = $loop === false ? "" : "[" . $loop . "]";
		$post_id        = ( isset( $post->ID ) ) ? $post->ID : '0';
		$product        = wc_get_product( $post_id );
		wp_enqueue_media();
		wp_enqueue_script( WPWOOF_PLUGIN . '-media-script', WPWOOF_ASSETS_URL . 'js/media.js', array( 'jquery' ), WPWOOF_VERSION, false );
		wp_enqueue_script( WPWOOF_PLUGIN . '-optionTree', WPWOOF_ASSETS_URL . 'js/jquery.optionTree.js', array( 'jquery' ), WPWOOF_VERSION, false );
		wp_enqueue_script( WPWOOF_PLUGIN . '-script', WPWOOF_ASSETS_URL . 'js/admin.js', array( 'jquery' ), WPWOOF_VERSION, false );
		wp_enqueue_style( WPWOOF_PLUGIN . '-style', WPWOOF_ASSETS_URL . 'css/admin-product.css', array(), WPWOOF_VERSION, false );
		wp_enqueue_editor();
		wp_nonce_field( 'wpwoof_nonce_action', 'wpwoof_nonce_name', false );
        require_once plugin_dir_path( __FILE__ ) . '/inc/feedfbgooglepro.php';
		$all_fields = wpwoof_get_all_fields( $product );

		$gm             = get_post_meta( $post_id, 'wpwoofextra', true );

		//compatibility <= 4.1.4
		if ( empty( $gm ) ) {
			$gm = @array_merge( (array) get_post_meta( $post_id, 'wpwoofgoogle', true ), (array) get_post_meta( $post_id, 'wpwoofadsensecustom', true ) );
		}
		$oFeed         = new FeedFBGooglePro();
		$select_values = $helpLinks = array();
		foreach ( $all_fields['dashboardExtra'] as $key => $value ) {
			if ( ! empty( $value['custom'] ) ) {
				$select_values[ $key ] = $value['custom'];
			}
			$helpLinks[ $key ] = $oFeed->getHelpLinks( $value );
		}
		$link2mainFieldlist = array(
			'wpfoof-custom-descr'           => 'description',
			'wpfoof-custom-title'           => 'title',
			'wpfoof-mpn-name'               => 'mpn',
			'wpfoof-gtin-name'              => 'gtin',
			'wpfoof-brand'                  => 'brand',
			'wpfoof-identifier_exists'      => 'identifier_exists',
			'wpfoof-condition'              => 'condition',
			'wpfoof-auto_pricing_min_price' => 'auto_pricing_min_price',
		);

        $fields = self::get_fields_in_products();
		foreach ( $fields as $key => $val ) {
			if ( ! $isMain && empty( $val['main'] ) || $isMain ) {

				$value = $rawvalue = ( $post_id ) ? get_post_meta( $post_id, $key, true ) : '';
				$key   = esc_attr( $key );
				$value = esc_attr( $value );

				//compatibility <= 4.1.4
				if ( $key == 'wpfoof-identifier_exists' && $value === '' ) {
					if ( isset( $gm['identifier_exists']['value'] ) ) {
						$value = $gm['identifier_exists']['value'];
					}
				}


				if ( isset( $val['topHr'] ) && $val['topHr'] ) {
					echo '<hr>';
				}
				?><div>
                <p class="form-field custom_field_type"><?php
				if ( empty( $val['type'] ) ) {
					$s     = explode( "x", $val['size'] );
					$image = ! $rawvalue ? '' : wp_get_attachment_image( $rawvalue, 'full', false, array( 'style' => 'display:block; /*margin-left:auto;*/ margin-right:auto;max-width:30%;height:auto;' ) );
					?>
                    <span id='IDprev-<?php echo $post_id . "-" . $key; ?>'
                          class='image-preview'><?php echo ( $image ) ? ( $image . "<br/>" ) : "" ?></span>
                    <label for="<?php echo $post_id . "-" . $key; ?>-value"><?php echo $val['title']; ?></label>
                    <span class="wrap wpwoof-required-value">
                    <input type='hidden' id='_value-<?php echo $post_id . "-" . $key; ?>'
                           name='wpfoof-box-media[<?= $key . ']' . $loopStr ?>' value='<?php echo $value ?>'/>
                    <input type='button' id='<?php echo $post_id . "-" . $key; ?>'
                           onclick="jQuery.fn.clickWPfoofClickUpload(this);" class='button wpfoof-box-upload-button'
                           value='Upload'/>
                    <input type='button' id='<?php echo $post_id . "-" . $key; ?>-remove'
                           onclick="jQuery.fn.clickWPfoofClickRemove(this);"
                           <?php if ( empty( $image ) ) { ?>style="display:none;"<?php } ?> class='button wpfoof-box-upload-button-remove'
                           value='Remove'/>
                    </span>
                    <span class="unlock_pro_features" data-size='<?php echo esc_attr( $val['size'] ); ?>'
                          id='<?php echo $post_id . "-" . $key; ?>-alert'>
                    </span>
				<?php
				}//if(empty($val['type'])){

				else if ( $val['type'] == "checkbox" ){
				?>
                    <label for="<?php echo $post_id . "-" . $key; ?>-value"><?php echo $val['title']; ?></label>
                    <span class="wrap wpwoof-required-value">
                    <input type='hidden' id='value-<?php echo $post_id . "-" . $key; ?>'
                           name='wpfoof-box-media[<?= $key . ']' . $loopStr ?>' value='0'/>
                    <input type='checkbox' id='_value-<?php echo $post_id . "-" . $key; ?>'
                           name='wpfoof-box-media[<?= $key . ']' . $loopStr ?>' value='1'  <?php if ( $value ) {
	                    echo "checked='true'";
                    } ?> />
                    </span>
				<?php
				}   else if ( $val['type'] == "textarea" ){
				?>
                    <label for="<?php echo $post_id . "-" . $key; ?>-value"><?php echo $val['title']; ?></label>
                    <span class="wrap wpwoof-required-value">
                        <textarea class='short wc_input_<?php echo $key; ?>'
                                  id='value-<?php echo $post_id . "-" . $key; ?>'
                                  name='wpfoof-box-media[<?= $key . ']' . $loopStr ?>'><?php echo $value; ?></textarea>
                    </span>
				<?php
				if ( isset( $val['needEditor'] ) && $val['needEditor'] ) {
					$editorsId4init[] = 'value-' . $post_id . '-' . $key;
				}
				echo isset( $link2mainFieldlist[ $key ] ) ? '<span class="extra-link-2-wrapper">' . $oFeed->getHelpLinks( $woocommerce_wpwoof_common->product_fields[ $link2mainFieldlist[ $key ] ] ) . '</span>' : '';
				}  else if ( $val['type'] == "text" ){
				?>
                    <label for="<?php echo $post_id . "-" . $key; ?>-value"><?php echo $val['title']; ?></label>
                    <span class="wrap wpwoof-required-value">
                    <input type='text' id='_value-<?php echo $post_id . "-" . $key; ?>'
                           class='short wc_input_<?php echo $key; ?>'
                           name='wpfoof-box-media[<?= $key . ']' . $loopStr ?>' value='<?php echo $value; ?>'/>
                    </span>
				<?php
				echo isset( $link2mainFieldlist[ $key ] ) ? '<span class="extra-link-2-wrapper">' . $oFeed->getHelpLinks( $woocommerce_wpwoof_common->product_fields[ $link2mainFieldlist[ $key ] ] ) . '</span>' : '';
				 if(!empty($val['message'])) { echo '<br><br><span>'.$val['message'].'</span>'; }
                }  else if ( $val['type'] == "hidden" ){
                ?>
                    <label for="<?php echo $post_id . "-" . $key; ?>-value"><?php echo $val['title']; ?></label>
                    <span class="wrap wpwoof-required-value">
                    <input type='hidden' id='_value-<?php echo $post_id . "-" . $key; ?>'
                           class='short wc_input_<?php echo $key; ?>'
                           name='wpfoof-box-media[<?= $key . ']' . $loopStr ?>' value='<?php echo $value; ?>'/>
                           <?php if(!empty($val['message'])) { echo $val['message']; } ?>
                    </span>
				<?php
                }  else if ( $val['type'] == 'select' ) {
				?>
                    <label><?php echo $val['title']; ?></label>
                    <span class="wrap">
                        <select name='wpfoof-box-media[<?= $key . ']' . $loopStr ?>'
                                id='_value-<?php echo $post_id . "-" . $key; ?>' class="select short">
                            <?php
                            if ( isset( $val['options'] ) && $val['options'] ) {
	                            foreach ( $val['options'] as $key2 => $text ) {
		                            echo '<option value="' . $key2 . '" ' . ( isset( $value ) && $value && $value == $key2 ? 'selected' : '' ) . '>' . $text . '</option>';
	                            }
                            }
                            ?>
                        </select>
                    </span><?php
				echo isset( $link2mainFieldlist[ $key ] ) ? '<span class="extra-link-2-wrapper">' . $oFeed->getHelpLinks( $woocommerce_wpwoof_common->product_fields[ $link2mainFieldlist[ $key ] ] ) . '</span>' : '';
				} else if ( $val['type'] == 'googleTaxonomy' ) {
				?>
                    <label><?php echo $val['title']; ?></label>
                    <span class="catalog-pro-variations-google-taxonomy-container">
                        <input class="wpwoof_google_category<?= $post_id; ?>_name" type="hidden"
                               name="wpfoof-box-media[<?= $key . ']' . $loopStr ?>"
                               value="<?php echo htmlspecialchars( $value, ENT_QUOTES ); ?>"/>
                        <input type="text" class="wpwoof_google_category<?php echo $post_id; ?>"
                               id="wpwoof_google_category_<?php echo $post_id; ?>"
                               name="wpwoof_google_category_<?php echo $post_id; ?>" value="" style='display:none;'/>

                    </span>
				<?php

				$taxSrc = admin_url( 'admin-ajax.php' );
				$taxSrc = add_query_arg( array( 'action' => 'wpwoofgtaxonmy' ), $taxSrc );
				?>
                    <script>

						<?php if($isMain) { ?>
                        jQuery(function ($) {
                            wpwoof_taxonomyPreLoad["<?= empty( $value ) ? 'root' : htmlspecialchars_decode( $value )?>"] = <?=json_encode( wpwoof_getTaxonmyByPath( htmlspecialchars_decode( $value ) ) )?>;
                            loadTaxomomy("#wpwoof_google_category_<?php echo $post_id; ?>");
                        });
						<?php } else {
						?>
                        wpwoof_taxonomyPreLoad["<?= empty( $value ) ? 'root' : htmlspecialchars_decode( $value )?>"] = <?=json_encode( wpwoof_getTaxonmyByPath( htmlspecialchars_decode( $value ) ) )?>;
                        loadTaxomomy("#wpwoof_google_category_<?php echo $post_id;?>"); <?php
						}  ?>

                    </script>
					<?php

				}

				if ( isset( $val['subscription'] ) && $val['subscription'] ) {
					?><span class="woocommerce-help-tip"
                            data-tip="<?php echo esc_attr( $val['subscription'] ); ?>" ></span><?php
				}

				echo '</p></div>';
				if ( isset( $val['type'] ) && $val['type'] == 'trigger' ) {
					?>
                    <div class="trigger_div">
                        <input type='hidden' name='wpfoof-box-media[<?= $key . ']' . $loopStr ?>' value="0"/>
                        <input type='checkbox'
                               value="1" <?php if ( ! empty( $val['show'] ) ) { ?> onclick="jQuery.fn.wpwoofOpenCloseFieldList('<?php echo $post_id . $val['show']; ?>',this.checked);"<?php } ?>
                               class="ios-switch" id='_value-<?php
						echo $post_id . '-' . $key;
						?>'
                               name='wpfoof-box-media[<?= $key . ']' . $loopStr ?>' <?= ( $value ? "checked='true'" : "" ); ?> />
                        <div class="switch"></div>
						<?= ! empty( $va['subtitle'] ) ? $va['subtitle'] : ''; ?>
                        <label class="woof-switcher-title"
                               for="_value-<?php echo $post_id . '-' . $key; ?>"><?php echo $val['title']; ?></label>
                    </div>
					<?php
				}

				if ( ! empty( $val['show'] ) ) {
					$WpWoofTopSave = "";
					?>
                    <div id="id<?php echo $post_id . $val['show']; ?>Fields"
                         style="display:<?php echo ! empty( $value ) ? 'block' : 'none'; ?>;"><?php
					$oFeed->renderFieldsToTab( $all_fields['toedittab'], $val['show'], ( $post_id ) ? get_post_meta( $post_id, 'wpwoof' . $val['show'], true ) : array() );
					?></div><?php
				}


			}

		}
		?>
        <hr><p><strong class="woof-extra-title">Add extra fields:</strong></p>
		<?php
		if ( $gm && count( $gm ) ) {
			foreach ( $gm as $key => $value ) {
				if ( ! is_array( $value ) || ! isset( $value['value'] ) || $value['value'] === '' || $key == 'identifier_exists' || $key == 'installmentamount' ) {
					continue;
				}
                $needEditor  = isset( $value['type'] ) && $value['type'] == 'editor';
                if ( $needEditor ) {
                    $editorsId4init[] = ( $loop === false ? "" : $loop . "_" ) . $key;
                }
                $is_repeated = ! empty( $all_fields['dashboardExtra'][ $key ]['repeated'] );
                if ($is_repeated && $value['value']) {
                    foreach ($value['value'] as $value2) {
                        $value['value'] = $value2;
                        self::extra_field_rendering($key, $value, $isMain, $gm, $loop, $loopStr, $is_repeated);
                    }
                }
                else {
                    self::extra_field_rendering($key, $value, $isMain, $gm, $loop, $loopStr, $is_repeated);
                }
			}
		}
		if ( $isMain ) {
			echo '<script> let wpwoof_select_values = ' . json_encode( $select_values ) . '
                                let wpwoof_help_links = ' . json_encode( $helpLinks )
			     . ';let wpwoof_current_page = "editProduct";let wpwoof_editorsId4init = ' . json_encode( $editorsId4init ) . ';let wpwoof_editorsId4initVar = []</script>';
		} else {
			echo '<script>wpwoof_editorsId4initVar[' . $loop . '] = ' . json_encode( $editorsId4init ) . ';</script>';
		}
		?>

        <hr id="hr-befor-add-new-field">
        <div class="wpwoof-box add-new-field-wrapper">
            <p style="display: flex;">
				<?php
				$oFeed->renderFieldsForDropbox( $all_fields['dashboardExtra'] );
				?>
                <input type="button" id="add-extra-field-product-btn"
                       class="button" <?= $loop === false ? '' : 'data-loop=' . $loop ?> value="Add new field">
            </p>
        </div>

		<?php
	}

    private static function extra_field_rendering($key, $value, $isMain, $gm, $loop, $loopStr, $is_repeated) {
        $isCustomTag = isset( $value['custom_tag_name'] ) ? true : false;
        $repeated_str = $is_repeated ? '[]' : '';
        $needEditor  = ( isset( $value['type'] ) && $value['type'] == 'editor' ) ? true : false;
        echo '<div><div class="form-field custom_field_type add-extra-fields custom_extra_field"' . ( ! $isMain && $needEditor ? ' style="display: inline;">' : '>' );
        if ( $isCustomTag ) {
            ?>
            <input type="text" name="wpfoof-box-media[extra]<?= $loopStr ?>[<?= $key ?>][custom_tag_name]"
                   value="<?= $value['custom_tag_name'] ?>" style="margin-left: -150px;width: 140px;"
                   class="catalog-pro-custom-extra-field">
            <?php
        } else {
            echo ' <label>' . ( ! empty( self::$WWC->product_fields[$key]['header'] ) ? self::$WWC->product_fields[$key]['header'] : $key ) . ':</label>';
        }
        if ( isset( $select_values[ $key ] ) ) {
            echo '<select name="wpfoof-box-media[extra]' . $loopStr . '[' . $key . '][value]" class="select short">';
            foreach ( $select_values[ $key ] as $keySel => $valueSel ) {
                echo '<option value="' . $keySel . '" ' . selected( $valueSel, $value['value'] ) . '>' . $valueSel . '</option>';
            }
            echo '</select>';
        } else {
            if ( $needEditor ) {
                echo '<textarea placeholder="Custom value" class="short wc_input_' . $key . '" id="wpwoof-editor-' . ( $loop === false ? "" : $loop . "_" ) . $key . '">' . $value['value'] . '</textarea>';
                echo '<input type="hidden" name="wpfoof-box-media[extra]' . $loopStr . '[' . $key . '][type]"  value="editor">';
            }
            echo '<input type="' . ( $needEditor ? 'hidden' : 'text' ) . '" name="wpfoof-box-media[extra]' . $loopStr . '[' . $key . '][value]'.$repeated_str.'" placeholder="Custom value" value="' . esc_html( $value['value'] ) . '" class="short wc_input_' . $key . '">';
        }
        echo '<input type="button" class="button remove-extra-field-product-btn" value="remove">';
        if ( $isCustomTag ):
            ?>

            <div class="extra-link-wrapper">
                <div class="extra-link__item">
                    <input type="checkbox" id="wpfoof-box-media[extra][<?= $key ?>][feed_type][facebook]"
                           name="wpfoof-box-media[extra]<?= $loopStr ?>[<?= $key ?>][feed_type][facebook]" <?php checked( isset( $value['feed_type']['facebook'] ) ); ?>>
                    <label for="wpfoof-box-media[extra]<?= $loopStr ?>[<?= $key ?>][feed_type][facebook]">Facebook</label>&emsp;&emsp;
                </div>
                <div class="extra-link__item">
                    <input type="checkbox"
                           id="wpfoof-box-media[extra]<?= $loopStr ?>[<?= $key ?>][feed_type][google]"
                           name="wpfoof-box-media[extra]<?= $loopStr ?>[<?= $key ?>][feed_type][google]" <?php checked( isset( $value['feed_type']['google'] ) ); ?>>
                    <label for="wpfoof-box-media[extra]<?= $loopStr ?>[<?= $key ?>][feed_type][google]">Google
                        Merchant</label>&emsp;&emsp;
                </div>
                <div class="extra-link__item">
                    <input type="checkbox"
                           id="wpfoof-box-media[extra]<?= $loopStr ?>[<?= $key ?>][feed_type][adsensecustom]"
                           name="wpfoof-box-media[extra]<?= $loopStr ?>[<?= $key ?>][feed_type][adsensecustom]" <?php checked( isset( $value['feed_type']['adsensecustom'] ) ); ?>>
                    <label for="wpfoof-box-media[extra]<?= $loopStr ?>[<?= $key ?>][feed_type][adsensecustom]">Google
                        Custom Remarketing</label>
                </div>
                <div class="extra-link__item">
                    <input type="checkbox"
                           id="wpfoof-box-media[extra]<?= $loopStr ?>[<?= $key ?>][feed_type][pinterest]"
                           name="wpfoof-box-media[extra]<?= $loopStr ?>[<?= $key ?>][feed_type][pinterest]" <?php checked( isset( $value['feed_type']['pinterest'] ) ); ?>>
                    <label for="wpfoof-box-media[extra]<?= $loopStr ?>[<?= $key ?>][feed_type][pinterest]">Pinterest</label>
                </div>
                <div class="extra-link__item">
                    <input type="checkbox"
                           id="wpfoof-box-media[extra]<?= $loopStr ?>[<?= $key ?>][feed_type][tiktok]"
                           name="wpfoof-box-media[extra]<?= $loopStr ?>[<?= $key ?>][feed_type][tiktok]" <?php checked( isset( $value['feed_type']['tiktok'] ) ); ?>>
                    <label for="wpfoof-box-media[extra]<?= $loopStr ?>[<?= $key ?>][feed_type][tiktok]">TikTok</label>
                </div>
                <br>
                <div class="extra-link__item">

                    <input type="checkbox"
                           id="wpfoof-box-media[extra]<?= $loopStr ?>[<?= $key ?>][feed_type][mapping]"
                           name="wpfoof-box-media[extra]<?= $loopStr ?>[<?= $key ?>][feed_type][mapping]" <?php checked( isset( $value['feed_type']['mapping'] ) ); ?>>
                    <label for="wpfoof-box-media[extra]<?= $loopStr ?>[<?= $key ?>][feed_type][mapping]">Use for
                        mapping (limited to 100 chars if mapped to custom labels)</label>
                </div>
            </div>
        <?php
        else:
            if ( isset( $helpLinks[ $key ] ) ) {
                echo '<span class="extra-link-2-wrapper">' . $helpLinks[ $key ] . '</span>';
            }
        endif;
        if ( $key == 'installmentmonths' ) {
            ?>
            <p class="installmentamount-wrapper form-field custom_field_type add-extra-fields">
                <label>installmentamount:</label>
                <input type="text" name="wpfoof-box-media[extra]<?= $loopStr ?>[installmentamount][value]"
                       placeholder="Custom value" class="short wc_input_installmentamount"
                       value="<?= $gm['installmentamount']['value'] ?>">
            </p>
            <?php
        }
        echo "</div></div>";
    }

	static function schedule_feed( $feed_config, $regenerateTime = false ) {
        if (!empty($feed_config['edit_feed'])) {
            $feed_id = (int) $feed_config['edit_feed'];
        }
		$status = self::$WWC->get_feed_status( $feed_id );
		if ( ! empty( $status['products_left'] ) && ( time() - $status['time'] < 300 ) ) {
//            if(WPWOOF_DEBUG) file_put_contents(self::$WWC->feedBaseDir.'cron-wpfeed.log',date("Y-m-d H:i:s")."\t schedule_feed exclude:".$feed_config['edit_feed']." products_left:".count($status['products_left'])."\n",FILE_APPEND);
			return false;
		}
        if ( in_array( $feed_config['feed_type'] , array(
                    'fb_localize',
                    'fb_country',
                    'google_local_inventory'
			    )) && !empty($feed_config['main_feed_id'])) {
            $main_feed_config = wpwoof_get_feed( $feed_config['main_feed_id'] );
            if (!is_wp_error($main_feed_config)) {
                if ( isset($main_feed_config['noGenAuto']) ) {
                    $feed_config['noGenAuto'] = $main_feed_config['noGenAuto'];
                }
                if (isset($main_feed_config['feed_interval'])) {
                    $feed_config['feed_interval'] = $main_feed_config['feed_interval'];
                }
                if (isset($main_feed_config['generated_time'])) {
                    $feed_config['generated_time'] = $main_feed_config['generated_time'];
                }
                if (isset($main_feed_config['feed_schedule_from'])) {
                    $feed_config['feed_schedule_from'] = $main_feed_config['feed_schedule_from'];
                }
            }
        }
		if ( ! $regenerateTime && ( ! empty( $feed_config['noGenAuto'] ) || self::$interval * 1 == 0 ) ) {
            self::$WWC->remove_feed_gen_schedule( $feed_id );
		} else {
			$nextRun = $regenerateTime ? $regenerateTime : self::$WWC->calcNextRun( $feed_config );
//            if(WPWOOF_DEBUG) file_put_contents(self::$WWC->feedBaseDir.'cron-wpfeed.log',date("Y-m-d H:i:s")."\t schedule_feed nextRun:".$feed_id." time:".$nextRun."\n",FILE_APPEND);
			if ( $nextRun ) {
                self::$WWC->set_feed_gen_schedule( $feed_id, $nextRun);
			}
		}
	}

}

global $wpWoofProdCatalog;
$wpWoofProdCatalog = new wpwoof_product_catalog();
