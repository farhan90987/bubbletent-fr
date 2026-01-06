<?php

namespace YayMail\Page;

use stdClass;
use YayMail\Ajax;
use YayMail\Page\Source\CustomPostType;
use YayMail\Page\Source\DefaultElement;
use YayMail\Templates\Templates;
use YayMail\I18n;
use YayMail\Helper\Products;
use YayMail\Helper\Helper;
use YayMail\Helper\PluginSupported;

defined( 'ABSPATH' ) || exit;
/**
 * Settings Page
 */
class Settings {

	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
			self::$instance->doHooks();
		}

		return self::$instance;
	}

	private $email_customizer_hook_surfix = null;
	private $pageId                       = null;
	private $templateAccount;
	private $emails = null;
	public function doHooks() {
		$this->templateAccount = array( 'customer_new_account', 'customer_new_account_activation', 'customer_reset_password' );

		// Register Custom Post Type use Email Builder
		add_action( 'init', array( $this, 'registerCustomPostType' ) );

		// Register Menu
		add_action( 'admin_menu', array( $this, 'settingsMenu' ), YAYMAIL_MENU_PRIORITY );

		// Register Style & Script use for Menu Backend
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAdminScripts' ) );

		add_filter( 'plugin_action_links_' . YAYMAIL_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );

		add_filter( 'plugin_row_meta', array( $this, 'add_support_and_docs_links' ), 10, 2 );
		// Notice

		$optionNotice = get_option( 'yaymail_notice' );
		if ( time() >= (int) $optionNotice ) {
			add_action( 'admin_notices', array( $this, 'renderNotice' ) );
		}

		// Ajax display notive
		add_action( 'wp_ajax_yaymail_notice', array( $this, 'yaymailNotice' ) );

		// Add Woocommerce email setting columns
		add_filter( 'woocommerce_email_setting_columns', array( $this, 'yaymail_email_setting_columns' ) );
		add_action( 'woocommerce_email_setting_column_template', array( $this, 'column_template' ) );

		//Alow text cid: in img-> src
		add_filter(
			'kses_allowed_protocols',
			function ( $protocols ) {
				$protocols[] = 'cid';
				return $protocols;
			}
		);

		// Excute Ajax
		Ajax::getInstance();
	}
	public function __construct() {}

	public function renderNotice() {

		include YAYMAIL_PLUGIN_PATH . '/includes/Page/Source/DisplayAddonNotice.php';
	}

	public function yaymailNotice() {
		if ( isset( $_POST ) ) {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : null;

			if ( ! wp_verify_nonce( $nonce, 'yaymail_nonce' ) ) {
				wp_send_json_error( array( 'status' => 'Wrong nonce validate!' ) );
				exit();
			}
			update_option( 'yaymail_notice', time() + 60 * 60 * 24 * 60 ); // After 60 days show
			wp_send_json_success();
		}
		wp_send_json_error( array( 'message' => 'Update fail!' ) );
	}

	public function yaymail_email_setting_columns( $array ) {
		if ( isset( $array['actions'] ) ) {
			unset( $array['actions'] );
			return array_merge(
				$array,
				array(
					'template' => '',
					'actions'  => '',
				)
			);
		}
		return $array;
	}
	public function column_template( $email ) {
		$email_id = $email->id;
		if ( 'yith-coupon-email-system' === $email->id ) {
			if ( class_exists( 'YayMailYITHWooCouponEmailSystem\templateDefault\DefaultCouponEmailSystem' ) ) {
				$email_id = 'YWCES_register';
			}
		}

		echo '<td class="wc-email-settings-table-template">
				<a class="button alignright" target="_blank" href="' . esc_attr( admin_url( 'admin.php?page=yaymail-settings' ) ) . '&template=' . esc_attr( $email_id ) . '">' . esc_html( __( 'Customize with YayMail', 'yaymail' ) ) . '</a></td>';
	}

	public function plugin_action_links( $links ) {
		$action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=yaymail-settings' ) . '" aria-label="' . esc_attr__( 'View WooCommerce Email Builder', 'yaymail' ) . '">' . esc_html__( 'Start Customizing', 'yaymail' ) . '</a>',
		);
		return array_merge( $action_links, $links );
	}

	public function add_support_and_docs_links( $plugin_meta, $plugin_file ) {
		if ( YAYMAIL_PLUGIN_BASENAME === $plugin_file ) {
			$plugin_meta[] = '<a target="_blank" href="https://docs.yaycommerce.com/yaymail/getting-started/introduction">Docs</a>';
			$plugin_meta[] = '<a target="_blank" href="https://yaycommerce.com/support/">Support</a>';
		}
		return $plugin_meta;
	}

	public function registerCustomPostType() {
		$labels       = array(
			'name'               => __( 'Email Template', 'yaymail' ),
			'singular_name'      => __( 'Email Template', 'yaymail' ),
			'add_new'            => __( 'Add New Email Template', 'yaymail' ),
			'add_new_item'       => __( 'Add a new Email Template', 'yaymail' ),
			'edit_item'          => __( 'Edit Email Template', 'yaymail' ),
			'new_item'           => __( 'New Email Template', 'yaymail' ),
			'view_item'          => __( 'View Email Template', 'yaymail' ),
			'search_items'       => __( 'Search Email Template', 'yaymail' ),
			'not_found'          => __( 'No Email Template found', 'yaymail' ),
			'not_found_in_trash' => __( 'No Email Template currently trashed', 'yaymail' ),
			'parent_item_colon'  => '',
		);
		$capabilities = array();
		$args         = array(
			'labels'              => $labels,
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => false,
			'query_var'           => true,
			'rewrite'             => true,
			'capability_type'     => 'yaymail_template',
			'capabilities'        => $capabilities,
			'hierarchical'        => false,
			'menu_position'       => null,
			'exclude_from_search' => true,
			'supports'            => array( 'title', 'author', 'thumbnail', 'revisions' ),
		);
		register_post_type( 'yaymail_template', $args );
	}
	public function settingsMenu() {
		$this->email_customizer_hook_surfix = add_submenu_page( 'yaycommerce', __( 'Email Builder Settings', 'yaymail' ), __( 'YayMail', 'yaymail' ), 'manage_woocommerce', $this->getPageId(), array( $this, 'settingsPage' ), 0 );
	}


	public function nitWebPluginRegisterButtons( $buttons ) {
		$buttons[] = 'table';
		$buttons[] = 'searchreplace';
		$buttons[] = 'visualblocks';
		$buttons[] = 'code';
		$buttons[] = 'insertdatetime';
		$buttons[] = 'autolink';
		$buttons[] = 'contextmenu';
		$buttons[] = 'advlist';
		return $buttons;
	}

	public function njtWebPluginRegisterPlugin( $plugin_array ) {
		$plugin_array['table']          = YAYMAIL_PLUGIN_URL . 'assets/tinymce/table/plugin.min.js';
		$plugin_array['searchreplace']  = YAYMAIL_PLUGIN_URL . 'assets/tinymce/searchreplace/plugin.min.js';
		$plugin_array['visualblocks']   = YAYMAIL_PLUGIN_URL . 'assets/tinymce/visualblocks/plugin.min.js';
		$plugin_array['code']           = YAYMAIL_PLUGIN_URL . 'assets/tinymce/code/plugin.min.js';
		$plugin_array['insertdatetime'] = YAYMAIL_PLUGIN_URL . 'assets/tinymce/insertdatetime/plugin.min.js';
		$plugin_array['autolink']       = YAYMAIL_PLUGIN_URL . 'assets/tinymce/autolink/plugin.min.js';
		$plugin_array['contextmenu']    = YAYMAIL_PLUGIN_URL . 'assets/tinymce/contextmenu/plugin.min.js';
		$plugin_array['advlist']        = YAYMAIL_PLUGIN_URL . 'assets/tinymce/advlist/plugin.min.js';
		return $plugin_array;
	}

	public function settingsPage() {
		// When load this page will not show adminbar
		?>
		<style type="text/css">
			#wpcontent, #footer {opacity: 0}
			#adminmenuback, #adminmenuwrap { display: none !important; }
		</style>
		<script type="text/javascript" id="yaymail-onload">
			jQuery(document).ready( function() {
				// jQuery('#adminmenuback, #adminmenuwrap').remove();
			});
		</script>
		<?php
		// add new buttons
		add_filter( 'mce_buttons', array( $this, 'nitWebPluginRegisterButtons' ) );

		// Load the TinyMCE plugin
		add_filter( 'mce_external_plugins', array( $this, 'njtWebPluginRegisterPlugin' ) );
		$viewPath = YAYMAIL_PLUGIN_PATH . 'views/pages/html-settings.php';
		include_once $viewPath;
	}

	public function enqueueAdminScripts( $screenId ) {
		global $wpdb;

		\YayMail\Integrations\Core::create();

		if ( strpos( $screenId, 'yaymail-settings' ) !== false && class_exists( 'WC_Emails' ) ) {
			// Filter to active tinymce
			add_filter( 'user_can_richedit', '__return_true', PHP_INT_MAX );
			// Get list template from Woo
			$this->emails = Helper::getListTemplateFromWoo();
			// Check active language

			$list_languages               = array();
			$active_language              = 'en';
			$translate_integeration       = \YayMail\Integrations\Translations\Initialize::get_integration();
			$translate_integration_plugin = null;
			if ( null !== $translate_integeration ) {
				$list_languages               = $translate_integeration::get_available_languages();
				$active_language              = $translate_integeration::get_dashboard_active_language();
				$translate_integration_plugin = $translate_integeration::get_integration_plugin();
			}

			/*
			@@@@ Enable Disable
			@@@@ note: Note the default value section is required when displaying in vue
			 */

			$settingDefaultEnableDisable = array(
				'new_order'                 => 1,
				'cancelled_order'           => 1,
				'failed_order'              => 1,
				'customer_on_hold_order'    => 1,
				'customer_processing_order' => 1,
				'customer_completed_order'  => 1,
				'customer_refunded_order'   => 1,
				'customer_invoice'          => 0,
				'customer_note'             => 0,
				'customer_reset_password'   => 0,
				'customer_new_account'      => 0,
			);

			$settingEnableDisables = ( CustomPostType::templateEnableDisable( false ) ) ? CustomPostType::templateEnableDisable( false ) : $settingDefaultEnableDisable;

			foreach ( $this->emails as $key => $value ) {
				if ( 'ORDDD_Email_Delivery_Reminder' == $key ) {
					$value->id = 'orddd_delivery_reminder_customer';
				}
				if ( 'WCVendors_Admin_Notify_Approved' == $key ) {
					$value->id = 'admin_notify_approved';
				}
				if ( isset( $value->id ) ) {
					if ( ! array_key_exists( $value->id, $settingEnableDisables ) ) {
						$settingEnableDisables[ $value->id ] = '0';
					}
				} else {
					if ( ! array_key_exists( $value['id'], $settingEnableDisables ) ) {
						$settingEnableDisables[ $value['id'] ] = '0';
					}
				}
			}

			$this->emails          = apply_filters( 'YaymailCreateFollowUpTemplates', $this->emails );
			$settingEnableDisables = apply_filters( 'YaymailCreateSelectFollowUpTemplates', $settingEnableDisables );

			$this->emails          = apply_filters( 'YaymailCreateAutomateWooTemplates', $this->emails );
			$settingEnableDisables = apply_filters( 'YaymailCreateSelectAutomateWooTemplates', $settingEnableDisables );

			$this->emails          = apply_filters( 'YaymailCreateShopMagicTemplates', $this->emails );
			$settingEnableDisables = apply_filters( 'YaymailCreateSelectShopMagicTemplates', $settingEnableDisables );

			$this->emails          = apply_filters( 'YaymailCreateTrackShipWooTemplates', $this->emails );
			$settingEnableDisables = apply_filters( 'YaymailCreateSelectTrackShipWooTemplates', $settingEnableDisables );

			$this->emails          = apply_filters( 'YaymailCreateWCFMWooFMTemplates', $this->emails );
			$settingEnableDisables = apply_filters( 'YaymailCreateSelectWCFMWooFMTemplates', $settingEnableDisables );

			$this->emails          = apply_filters( 'YaymailCreateListYWCESTemplates', $this->emails );
			$settingEnableDisables = apply_filters( 'YaymailCreateSelectYWCESTemplates', $settingEnableDisables );

			$this->emails          = apply_filters( 'YaymailCreateWcCartAbandonmentRecoveryTemplates', $this->emails );
			$settingEnableDisables = apply_filters( 'YaymailCreateSelectWcCartAbandonmentRecoveryTemplates', $settingEnableDisables );

			$this->emails          = apply_filters( 'YaymailCreateB2BMarketTemplates', $this->emails );
			$settingEnableDisables = apply_filters( 'YaymailCreateSelectB2BMarketTemplates', $settingEnableDisables );

			$settingEnableDisables = apply_filters( 'YaymailCreateSelectGermanMarketTemplates', $settingEnableDisables );

			$settingDefaultGenerals = array(
				'payment'                      => 2,
				'product_image'                => 0,
				'image_size'                   => 'thumbnail',
				'image_width'                  => '30px',
				'image_height'                 => '30px',
				'product_sku'                  => 1,
				'product_des'                  => 0,
				'product_hyper_links'          => 0,
				'product_regular_price'        => 0,
				'product_item_cost'            => 0,
				'background_color_table_items' => '#e5e5e5',
				'content_items_color'          => '#636363',
				'title_items_color'            => '#7f54b3',
				'container_width'              => '605px',
				'order_url'                    => '',
				'custom_css'                   => '',
				'enable_css_custom'            => 'no',
				'image_position'               => 'Top',
			);
			$settingGenerals        = get_option( 'yaymail_settings' ) ? get_option( 'yaymail_settings' ) : $settingDefaultGenerals;
			foreach ( $settingDefaultEnableDisable as $keyDefaultEnableDisable => $settingDefaultEnableDisable ) {
				if ( ! array_key_exists( $keyDefaultEnableDisable, $settingEnableDisables ) ) {
					$settingEnableDisables[ $keyDefaultEnableDisable ] = $settingDefaultEnableDisable;
				};
			}
			$settings['enableDisable'] = $settingEnableDisables;

			/*
			@@@@ General
			@@@@ note: Note the default value section is required when displaying in vue
			 */

			$settingGenerals = get_option( 'yaymail_settings' ) ? get_option( 'yaymail_settings' ) : $settingDefaultGenerals;
			foreach ( $settingDefaultGenerals as $keyDefaultGeneral => $settingGeneral ) {
				if ( ! array_key_exists( $keyDefaultGeneral, $settingGenerals ) ) {
					$settingGenerals[ $keyDefaultGeneral ] = $settingDefaultGenerals[ $keyDefaultGeneral ];
				};
			}

			$settingGenerals['direction_rtl'] = get_option( 'yaymail_direction' ) ? get_option( 'yaymail_direction' ) : 'ltr';
			$settings['general']              = $settingGenerals;

			$scriptId = $this->getPageId();
			$order    = CustomPostType::getListOrders();
			wp_deregister_script( 'vue' );
			wp_deregister_script( 'vuex' );
			wp_enqueue_script( 'vue', YAYMAIL_PLUGIN_URL . ( YAYMAIL_DEBUG ? 'assets/libs/vue.js' : 'assets/libs/vue.min.js' ), '', YAYMAIL_VERSION, true );
			wp_enqueue_script( 'vuex', YAYMAIL_PLUGIN_URL . 'assets/libs/vuex.js', '', YAYMAIL_VERSION, true );

			wp_enqueue_script( $scriptId, YAYMAIL_PLUGIN_URL . 'assets/dist/js/main.js', array( 'jquery' ), YAYMAIL_VERSION, true );
			wp_enqueue_style( $scriptId, YAYMAIL_PLUGIN_URL . 'assets/dist/css/main.css', array(), YAYMAIL_VERSION );

			wp_enqueue_script( $scriptId . '-script', YAYMAIL_PLUGIN_URL . 'assets/admin/js/script.js', '', YAYMAIL_VERSION, true );
			wp_enqueue_script( $scriptId . '-plugin-install', YAYMAIL_PLUGIN_URL . 'assets/admin/js/plugin-install.js', '', YAYMAIL_VERSION, true );
			$yaymailSettings = get_option( 'yaymail_settings' );

			// Load ACE Editor -Start
			if ( isset( $yaymailSettings['enable_css_custom'] ) && 'yes' == $yaymailSettings['enable_css_custom'] ) {
				wp_enqueue_script( $scriptId . 'ace-script', YAYMAIL_PLUGIN_URL . 'assets/aceeditor/ace.js', '', YAYMAIL_VERSION, true );
				wp_enqueue_script( $scriptId . 'ace1-script', YAYMAIL_PLUGIN_URL . 'assets/aceeditor/ext-language_tools.js', '', YAYMAIL_VERSION, true );
				wp_enqueue_script( $scriptId . 'ace2-script', YAYMAIL_PLUGIN_URL . 'assets/aceeditor/mode-css.js', '', YAYMAIL_VERSION, true );
				wp_enqueue_script( $scriptId . 'ace3-script', YAYMAIL_PLUGIN_URL . 'assets/aceeditor/theme-merbivore_soft.js', '', YAYMAIL_VERSION, true );
				wp_enqueue_script( $scriptId . 'ace4-script', YAYMAIL_PLUGIN_URL . 'assets/aceeditor/worker-css.js', '', YAYMAIL_VERSION, true );
				wp_enqueue_script( $scriptId . 'ace5-script', YAYMAIL_PLUGIN_URL . 'assets/aceeditor/snippets/css.js', '', YAYMAIL_VERSION, true );
			} else {
				wp_dequeue_script( $scriptId . 'ace-script' );
				wp_dequeue_script( $scriptId . 'ace1-script' );
				wp_dequeue_script( $scriptId . 'ace2-script' );
				wp_dequeue_script( $scriptId . 'ace3-script' );
				wp_dequeue_script( $scriptId . 'ace4-script' );
				wp_dequeue_script( $scriptId . 'ace5-script' );
			}
			// Load ACE Editor -End
			// Css for page admin of WordPress.
			wp_enqueue_style( $scriptId . '-css', YAYMAIL_PLUGIN_URL . 'assets/admin/css/css.css', array(), YAYMAIL_VERSION );
			$current_user       = wp_get_current_user();
			$default_email_test = false != get_user_meta( get_current_user_id(), 'yaymail_default_email_test', true ) ? get_user_meta( get_current_user_id(), 'yaymail_default_email_test', true ) : $current_user->user_email;
			$element            = new DefaultElement();

			$yaymailSettingsDefaultLogo   = get_option( 'yaymail_settings_default_logo_' . $active_language );
			$setDefaultLogo               = false != $yaymailSettingsDefaultLogo ? $yaymailSettingsDefaultLogo['set_default'] : '0';
			$yaymailSettingsDefaultFooter = get_option( 'yaymail_settings_default_footer_' . $active_language );
			$setDefaultFooter             = false != $yaymailSettingsDefaultFooter ? $yaymailSettingsDefaultFooter['set_default'] : '0';
			if ( isset( $_GET['template'] ) || ! empty( $_GET['template'] ) ) {
				$req_template['id'] = sanitize_text_field( $_GET['template'] );
			} else {
				$req_template['id'] = 'new_order';
			}
			foreach ( $this->emails as $value ) {
				$template_id = is_object( $value ) ? $value->id : $value['id'];
				if ( $template_id == $req_template['id'] ) {
					$req_template['title'] = is_object( $value ) ? $value->title : $value['title'];
				}
			}

			// List email supported
			$list_email_supported = PluginSupported::listAddonSupported();
			$list_plugin_for_pro  = PluginSupported::listPluginForProSupported();

			$orderby        = 'name';
			$order_category = 'asc';
			$hide_empty     = false;

			$product_categories = get_terms(
				array(
					'taxonomy'   => 'product_cat',
					'orderby'    => $orderby,
					'order'      => $order_category,
					'hide_empty' => $hide_empty,
				)
			);

			$billing_country     = WC()->countries->countries;
			$arr_payment_methods = array();
			$payment_methods     = WC()->payment_gateways->payment_gateways;
			foreach ( $payment_methods as $key => $item ) {
				if ( 'yes' == $item->enabled ) {
					$arr_payment_methods[] = array(
						'id'           => $item->id,
						'method_title' => ! empty( $item->method_title ) ? $item->method_title : $item->title,
					);
				}
			}

			$get_shipping_methods = WC()->shipping->get_shipping_methods();
			$data                 = array();
			foreach ( $get_shipping_methods as $shipping_method ) {
				$item = array(
					'id'           => $shipping_method->id,
					'method_title' => $shipping_method->method_title,
				);

				$data[] = $item;
			}

			$shipping_methods = $data;

			$data_coupon_codes = array();
			$data_products     = array();
			$data_skus         = array();
			if ( is_plugin_active( 'yaymail-addon-conditional-logic/yaymail-conditional-logic.php' ) || is_plugin_active( 'yaymail-conditional-logic/yaymail-conditional-logic.php' ) ) {
				global $wpdb;
				$data_coupon_codes = $wpdb->get_col( "SELECT post_name FROM $wpdb->posts WHERE post_type = 'shop_coupon' AND post_status = 'publish' ORDER BY post_name ASC LIMIT 20" );

				$get_products = $wpdb->get_results( "SELECT ID as id, post_title as name FROM $wpdb->posts WHERE post_type = 'product' AND post_status = 'publish' ORDER BY name ASC LIMIT 20" );
				foreach ( $get_products as $product ) {
					$data_products[] = $product;
				}

				$get_product_skus = $wpdb->get_results(
					// "SELECT CONCAT(p.post_title, ' (#', pm.meta_value, ')') AS label,
					"SELECT 
						DISTINCT(pm.meta_value) as sku,
						p.post_title AS name,
						p.ID AS id 
					FROM {$wpdb->prefix}postmeta AS pm 
						INNER JOIN {$wpdb->prefix}posts AS p
						ON pm.post_id = p.ID
					WHERE pm.meta_key = '_sku'
						AND p.post_type IN ('product', 'product_variation')
						AND p.post_status = 'publish'
					ORDER BY pm.meta_value ASC LIMIT 20"
				);
				foreach ( $get_product_skus as $skus ) {
					$data_skus[] = $skus;
				}
			}
			$coupon_codes = $data_coupon_codes;
			$products     = $data_products;
			$product_skus = $data_skus;

			$arrayShortCode = array();
			if ( class_exists( 'YITH_Barcode' ) ) {
				$arrayShortCode[] = array(
					'plugin'    => 'YITH Barcode',
					'shortcode' => array(
						array( '[yaymail_yith_barcode]', 'Show Barcode' ),
					),
				);
			}

			$yaymail_informations = Helper::inforShortcode( '', '', array() );
			$shortcodeCustom      = array_keys( apply_filters( 'yaymail_customs_shortcode', array(), $yaymail_informations, '' ) );
			if ( ! empty( $shortcodeCustom ) ) {
				$arrItemShortcodeCus = array();
				foreach ( $shortcodeCustom as $item ) {
					$name = __( 'Custom Shortcode For ', 'yaymail' ) . ucfirst( str_replace( array( '[', ']', 'yaymail_custom_shortcode_' ), '', $item ) );
					array_push( $arrItemShortcodeCus, array( $item, $name ) );
				}
				$arrayShortCode[] = array(
					'plugin'    => __( 'Custom Shortcode', 'yaymail' ),
					'shortcode' => $arrItemShortcodeCus,
				);
			}

			$listShortCodeAddon = apply_filters( 'yaymail_list_shortcodes', $arrayShortCode );

			// WooCommerce for LatePoint
			if ( class_exists( 'TechXelaLatePointPaymentsWooCommerce' ) ) {
				$listShortCodeAddon[] = array(
					'plugin'    => 'WooCommerce for LatePoint',
					'shortcode' => array(
						array( '[yaymail_woo_latepoint_booking_detail]', 'WooCommerce Latepoint Booking Detail' ),

						array( '[yaymail_woo_latepoint_caption]', 'WooCommerce Latepoint caption' ),
						array( '[yaymail_woo_latepoint_bg_color]', 'WooCommerce Latepoint Bg Color' ),
						array( '[yaymail_woo_latepoint_text_color]', 'WooCommerce Latepoint Text Color' ),
						array( '[yaymail_woo_latepoint_font_size]', 'WooCommerce Latepoint Font Size' ),
						array( '[yaymail_woo_latepoint_border]', 'WooCommerce Latepoint Border' ),
						array( '[yaymail_woo_latepoint_border_radius]', 'WooCommerce Latepoint Border Radius' ),
						array( '[yaymail_woo_latepoint_margin]', 'WooCommerce Latepoint Margin' ),
						array( '[yaymail_woo_latepoint_padding]', 'WooCommerce Latepoint Padding' ),
						array( '[yaymail_woo_latepoint_css]', 'WooCommerce Latepoint css' ),
						array( '[yaymail_woo_latepoint_show_locations]', 'WooCommerce Latepoint Show Locations' ),
						array( '[yaymail_woo_latepoint_show_agents]', 'WooCommerce Latepoint Show Agents' ),
						array( '[yaymail_woo_latepoint_show_services]', 'WooCommerce Latepoint Show Services' ),
						array( '[yaymail_woo_latepoint_show_service_categories]', 'WooCommerce Latepoint Show Service Sategories' ),
						array( '[yaymail_woo_latepoint_selected_location]', 'WooCommerce Latepoint Selected Location' ),
						array( '[yaymail_woo_latepoint_selected_agent]', 'WooCommerce Latepoint Selected Agent' ),
						array( '[yaymail_woo_latepoint_selected_service]', 'WooCommerce Latepoint Selected Service' ),
						array( '[yaymail_woo_latepoint_selected_service_category]', 'WooCommerce Latepoint Selected Service Category' ),
						array( '[yaymail_woo_latepoint_selected_duration]', 'WooCommerce Latepoint Selected Duration' ),
						array( '[yaymail_woo_latepoint_selected_total_attendees]', 'WooCommerce Latepoint Selected Total Attendees' ),
						array( '[yaymail_woo_latepoint_hide_side_panel]', 'WooCommerce Latepoint Hide Side Panel' ),
						array( '[yaymail_woo_latepoint_hide_summary]', 'WooCommerce Latepoint Hide Summary' ),
						array( '[yaymail_woo_latepoint_calendar_start_date]', 'WooCommerce Latepoint Calendar Start Date' ),

					),
				);
			}

			// support Custom Order Statuses for WooCommerce by nuggethon
			$woocos_custom_order_statuses = array();
			if ( class_exists( 'WOOCOS_Email_Manager' ) ) {
				$custom_order_statuses = json_decode( get_option( 'woocos_custom_order_statuses' ) );
				if ( $custom_order_statuses ) {
					foreach ( $custom_order_statuses as $order_status ) {
						array_push( $woocos_custom_order_statuses, $order_status->slug );
					}
				}
			}

			// support Custom Order Statuses for WooCommerce by nuggethon
			$bvcos_custom_order_statuses = array();
			if ( class_exists( 'Bright_Plugins_COSW' ) ) {
				$arg            = array(
					'numberposts' => -1,
					'post_type'   => 'order_status',
				);
				$postStatusList = get_posts( $arg );

				if ( $postStatusList ) {
					foreach ( $postStatusList as $post ) {
						$statusSlug = get_post_meta( $post->ID, 'status_slug', true );
						array_push( $bvcos_custom_order_statuses, $statusSlug );
					}
				}
			}
			$initial_categories = array_map(
				function( $cat_item ) {
					return array(
						'label' => $cat_item->name,
						'value' => $cat_item->term_id,
					);
				},
				$product_categories
			);
			// $initial_tags = Products::get_all_tags();

				wp_localize_script(
					$scriptId,
					'yaymail_data',
					array(
						'orders'                           => $order,
						'imgUrl'                           => YAYMAIL_PLUGIN_URL . 'assets/dist/images',
						'nonce'                            => wp_create_nonce( 'email-nonce' ),
						'defaultDataElement'               => $element->defaultDataElement,
						'home_url'                         => home_url(),
						'wc_setting_email_url'             => admin_url( 'admin.php?page=wc-settings&tab=email' ),
						'settings'                         => $settings,
						'admin_url'                        => get_admin_url(),
						'yaymail_plugin_url'               => YAYMAIL_PLUGIN_URL,
						'wc_emails'                        => $this->emails,
						'default_email_test'               => $default_email_test,
						'template'                         => $req_template,
						'set_default_logo'                 => $setDefaultLogo,
						'set_default_footer'               => $setDefaultFooter,
						'list_plugin_for_pro'              => $list_plugin_for_pro,
						'plugins'                          => apply_filters( 'yaymail_plugins', array() ),
						'list_email_supported'             => $list_email_supported,
						'list_languages'                   => $list_languages,
						'active_language'                  => $active_language,
						'translate_integration_plugin'     => $translate_integration_plugin,
						'available_translate_integrations' => \YayMail\Integrations\Translations\Initialize::get_available_integrations(),
						'priority_translate_integration'   => \YayMail\Integrations\Translations\Initialize::get_priority_integration(),
						'product_categories'               => $product_categories,
						'billing_country'                  => $billing_country,
						'payment_methods'                  => $arr_payment_methods,
						'custom_statuses'                  => function_exists( 'alg_get_custom_order_statuses_from_cpt' ) ? alg_get_custom_order_statuses_from_cpt() : array(), // Support Custom Order Status for WooCommerce
						'link_detail_smtp'                 => self_admin_url( 'plugin-install.php?tab=plugin-information&plugin=yaysmtp&section=description&TB_iframe=true&width=600&height=800' ),
						'yaymail_automatewoo_active'       => ( is_plugin_active( 'yaymail-addon-for-automatewoo/yaymail-automatewoo.php' ) || is_plugin_active( 'email-customizer-automatewoo/yaymail-automatewoo.php' ) ) ? true : false,
						'yaymail_dokan_active'             => is_plugin_active( 'yaymail-addon-for-dokan/yaymail-premium-addon-dokan.php' ) ? true : false,
						'yaysmtp_active'                   => $this->check_plugin_installed( 'yaysmtp/yay-smtp.php' ) || $this->check_plugin_installed( 'yaysmtp-pro/yay-smtp.php' ) ? true : false,
						'yaysmtp_setting'                  => admin_url( 'admin.php?page=yaysmtp' ),
						'list_shortcode_addon'             => $listShortCodeAddon,
						'shipping_methods'                 => $shipping_methods,
						'coupon_codes'                     => $coupon_codes,
						'products'                         => $products,
						'product_skus'                     => $product_skus,
						'i18n'                             => I18n::jsTranslate(),
						'is_hiden_direction'               => $this->is_hiden_direction(),
						'woocos_custom_statuses'           => $woocos_custom_order_statuses,
						'bvos_custom_statuses'             => $bvcos_custom_order_statuses,
						'product_placeholder_image'        => \wc_placeholder_img_src(),
						'categories'                       => $initial_categories,
						// 'initial_tags'                     => array_slice( $initial_tags, 0, 1 ),
						'condition_values_page_size'       => 20,
					)
				);
				do_action( 'yaymail_enqueue_script_conditional_logic' );
				do_action( 'yaymail_before_enqueue_dependence' );
		}
		wp_enqueue_script( 'yaymail-notice', YAYMAIL_PLUGIN_URL . 'assets/admin/js/notice.js', array( 'jquery' ), YAYMAIL_VERSION, false );
		wp_localize_script(
			'yaymail-notice',
			'yaymail_notice',
			array(
				'admin_ajax' => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'yaymail_nonce' ),
			)
		);
	}
	public function getPageId() {
		if ( null == $this->pageId ) {
			$this->pageId = YAYMAIL_PREFIX . '-settings';
		}

		return $this->pageId;
	}
	public function check_plugin_installed( $plugin_slug ) {
		$installed_plugins = get_plugins();
		return array_key_exists( $plugin_slug, $installed_plugins ) || in_array( $plugin_slug, $installed_plugins, true );
	}

	public function is_hiden_direction() {
		if ( class_exists( 'Polylang' ) || class_exists( 'SitePress' ) || class_exists( 'TRP_Translate_Press' ) || class_exists( 'Loco_Locale' ) ) {
			return true;
		}
		return false;
	}
}
