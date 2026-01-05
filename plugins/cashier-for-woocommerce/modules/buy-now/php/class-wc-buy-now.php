<?php
/**
 * Main class for WooCommerce Buy Now
 *
 * @package     WooCommerce Buy Now
 *  author      StoreApps
 * @version     1.1.1
 * @since       1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Buy_Now' ) ) {

	/**
	 * Main class for WooCommerce Buy Now
	 */
	class WC_Buy_Now {

		/**
		 * Variable to hold instance of Buy Now
		 *
		 * @var $instance
		 * @since 2.4.1
		 */
		private static $instance = null;

		/**
		 * WooCommerce Buy Now plugin data.
		 *
		 * @var array
		 * @since 2.4.1
		 */
		public $plugin_data = array();

		/**
		 * Get single instance of Buy Now.
		 *
		 * @return WC_Buy_Now Singleton object of WC_Buy_Now
		 * @since 2.4.1
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
		 * @since 2.4.1
		 */
		private function __clone() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'cashier' ), '3.3.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 2.4.1
		 */
		public function __wakeup() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'cashier' ), '3.3.0' );
		}

		/**
		 * Constructor
		 */
		public function __construct() {

			$this->includes();

			$this->plugin_data = self::get_bn_plugin_data();

			if ( get_option( 'wc_buy_now_set_for' ) === 'buy-now' ) {

				add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'checkout_redirect' ) );
				add_action( 'wp_enqueue_scripts', array( $this, 'remove_wc_add_to_cart_ajax_script' ), 100 );

				add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'woocommerce_product_change_add_to_cart_text' ), 10, 2 );     // Loop.
				add_filter( 'woocommerce_product_single_add_to_cart_text', array( $this, 'woocommerce_product_change_add_to_cart_text' ), 10, 2 );
			}

			add_action( 'init', array( $this, 'add_buy_now_pay_endpoint' ) );
			add_action( 'init', array( $this, 'process_quick_checkout' ) );
			add_action( 'template_redirect', array( $this, 'buy_now_pay_template_redirect' ) );
			add_action( 'wp_loaded', array( $this, 'process_products_and_coupons' ), 11 );
			add_action( 'wp_loaded', array( $this, 'process_buy_now_pay_action' ), 11 );
			add_action( 'wp_loaded', array( $this, 'add_buy_now_shortcode' ) );

			add_action( 'admin_menu', array( $this, 'wc_buy_now_admin_menu' ) );
			add_action( 'woocommerce_buy_now_checkout_redirect', array( $this, 'checkout_redirect' ) );
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'wc_buy_now_checkout_order_processed' ), 99, 2 );
			add_action( 'woocommerce_order_details_after_order_table', array( $this, 'restore_buy_now_persistent_cart' ), 999 );

			add_action( 'sa_wc_buy_now_enhanced_select_script_start', array( $this, 'enhanced_select_script_start' ) );
			add_action( 'sa_wc_buy_now_enhanced_select_script_end', array( $this, 'enhanced_select_script_end' ) );

			add_action( 'wp_ajax_woocommerce_json_search_coupons', array( $this, 'woocommerce_json_search_coupons' ) );
			add_action( 'wp_ajax_wc_buy_now_json_search_products_and_variations', array( $this, 'wc_buy_now_json_search_products_and_variations' ) );
			add_filter( 'woocommerce_json_search_found_products', array( $this, 'filter_found_products' ) );

			add_action( 'woocommerce_proceed_to_checkout', array( $this, 'add_express_checkout_for_buy_now' ) );

			add_action( 'woocommerce_after_checkout_validation', array( $this, 'wc_buy_now_after_checkout_validation' ) );

			add_filter( 'sa_buy_now_link', array( $this, 'sa_buy_now_link' ), 1, 2 );

			add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'add_buy_now_button' ), 10, 3 );
			add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'add_single_buy_now_button' ) );
			add_action( 'wc_ajax_sa_wc_get_buy_now_button', array( $this, 'get_ajax_buy_now_button' ) );
			add_action( 'wp_footer', array( $this, 'wc_buy_now_frontend_style_and_script' ) );

			// Actions/Filters for Quick Checkout.
			add_action( 'wp_enqueue_scripts', array( $this, 'register_quick_checkout_styles_and_scripts' ) );
			add_filter( 'woocommerce_payment_successful_result', array( $this, 'wc_buy_now_save_redirect_link_in_session' ), 10, 2 );
			add_filter( 'woocommerce_checkout_no_payment_needed_redirect', array( $this, 'wc_buy_now_save_redirect_link_in_session' ), 10, 2 );
			add_action( 'wp_footer', array( $this, 'wc_buy_now_redirect_after_quick_checkout' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'bn_load_admin_scripts_and_styles' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'register_buy_now_script' ) );
			add_action( 'admin_print_scripts', array( $this, 'wc_buy_now_styles_and_scripts' ) );

			// Filter to add a column to the Payment Gateway table to show whether the gateway supports Buy Now's one click checkout.
			add_filter( 'woocommerce_payment_gateways_setting_columns', array( $this, 'payment_gateways_support_column' ) );

			add_action( 'woocommerce_payment_gateways_setting_column_sa_bn_wc_one_click_checkout', array( $this, 'payment_gateways_support' ) );

			add_action( 'wp_ajax_nopriv_get_product_details', array( $this, 'get_product_details' ) );
			add_action( 'wp_ajax_get_product_details', array( $this, 'get_product_details' ) );

		}

		/**
		 * Function to handle WC compatibility related function call from appropriate class
		 *
		 * @param string $function_name Function to call.
		 * @param array  $arguments Array of arguments passed while calling $function_name.
		 * @return mixed Result of function call.
		 */
		public function __call( $function_name, $arguments = array() ) {

			if ( ! is_callable( 'SA_WC_Compatibility_4_1', $function_name ) ) {
				return;
			}

			if ( ! empty( $arguments ) ) {
				return call_user_func_array( 'SA_WC_Compatibility_4_1::' . $function_name, $arguments );
			} else {
				return call_user_func( 'SA_WC_Compatibility_4_1::' . $function_name );
			}

		}

		/**
		 * Includes
		 */
		public function includes() {
			include_once 'admin/class-sa-wc-buy-now-settings.php';
		}

		/**
		 * Function to load script that handle buy now actions on product edit page.
		 */
		public function bn_load_admin_scripts_and_styles() {
			global $pagenow, $post;

			if ( $post instanceof WP_Post && 'product' === $post->post_type && ( 'post-new.php' === $pagenow || 'post.php' === $pagenow ) ) {

				wp_register_script( 'wc-bn-admin-js', plugins_url( 'js/wc-bn-admin.js', SA_BN_PLUGIN_DIRPATH ), array( 'jquery' ), $this->plugin_data['Version'], false );

				$product_id            = $post->ID;
				$product               = wc_get_product( $product_id );
				$is_variable           = ( $product instanceof WC_Product_Variable ) ? 'yes' : 'no';
				$variations            = ( $product instanceof WC_Product_Variable ) ? $product->get_children() : array();
				$product_type          = $product->get_type();
				$exluded_product_types = $this->get_excluded_product_types();

				$preserve_cart = get_option( 'wc_buy_now_is_preserve_cart', 'yes' );
				$with_cart     = ( 'yes' === $preserve_cart ) ? 0 : 1;

				$params = array(
					'product_id'            => $product_id,
					'is_variable'           => $is_variable,
					'product_variations'    => $variations,
					'site_url'              => get_site_url(),
					'product_type'          => $product_type,
					'exluded_product_types' => $exluded_product_types,
					'with_cart'             => $with_cart,
				);

				wp_localize_script( 'wc-bn-admin-js', 'bn_admin_params', $params );

				if ( ! wp_script_is( 'wc-bn-admin-js' ) ) {
					wp_enqueue_script( 'wc-bn-admin-js' );
				}
			}
		}

		/**
		 * Get list of excluded product types
		 */
		public function get_excluded_product_types() {

			// $excluded_product_types = array( 'grouped', 'external' );
			$product_type_details = wc_get_product_types();
			if ( empty( $product_type_details ) ) {
				return array();
			}
			$product_types          = array_keys( $product_type_details );
			$included_product_types = array( 'listing_booking' );
			$excluded_product_types = array_diff( $product_types, $included_product_types );			
			 

			return apply_filters( 'wc_bn_excluded_product_types', $excluded_product_types );

		}

		/**
		 * JS to show checkout page in a popup
		 */
		public function wc_buy_now_show_checkout_in_popup() {
			$js = " var show_iframe_loading = function() {
					    var curLength = 0;
					    var interval = setInterval( function() {
					        if ( jQuery( 'iframe' ).length !== curLength ) {
					            curLength = jQuery( '.column-header' ).length;
					            jQuery( '.mfp-content' ).hide();
					            jQuery( '.mfp-preloader' ).show();

					        }
					    }, 50 );
					    this.content.find( 'iframe' ).on( 'load', function() {
					        clearInterval( interval );
					        jQuery( '.mfp-content' ).show();

					        jQuery( '.mfp-preloader' ).hide();
					    });

					    jQuery( '.woocommerce' ).find( '.woocommerce-error' ).hide();
					};

					var show_only_checkout_options = function() {
						this.content.find( 'iframe' ).on( 'load', function() {
							var checkout_body = jQuery( '.mfp-content .mfp-iframe' ).contents().find( 'body.woocommerce-checkout' );
							jQuery( checkout_body ).addClass( 'bn-qc-popup' );
							jQuery( checkout_body ).css( 'margin', '3em' );

							var checkout_content = jQuery( '.mfp-content .mfp-iframe' ).contents().find( 'form.checkout' ).parent();

							jQuery( '.mfp-content .mfp-iframe' ).contents().find( 'body.bn-qc-popup' ).prepend( checkout_content );
							jQuery( checkout_content ).siblings().hide();

							var errors = jQuery( '.woocommerce' ).find( '.woocommerce-error' ).show();
							jQuery( checkout_content ).prepend( errors );
						});
					}

					jQuery.magnificPopup.open({
		  				items:{
		  					src: '" . wc_get_checkout_url() . "'
		  				},
		  				type:'iframe',
		  				callbacks: {
	   						beforeAppend: show_iframe_loading,
	   						open: show_only_checkout_options
						}
					});";

			wc_enqueue_js( $js );
		}

		/**
		 * Register magnific popup script and style
		 */
		public function register_quick_checkout_styles_and_scripts() {
			$wc_buy_now_set_for = get_option( 'wc_buy_now_set_for' );

			if ( in_array( $wc_buy_now_set_for, array( 'buy-now', 'and-buy-now', 'standard' ), true ) && 'yes' === get_option( 'wc_buy_now_is_quick_checkout' ) ) {
				wp_register_script( 'bn-mfp-js', plugin_dir_url( SA_BN_PLUGIN_DIRPATH ) . 'js/magnific-popup.min.js', array( 'jquery' ), $this->plugin_data['Version'], false );
				wp_enqueue_script( 'bn-mfp-js' );

				wp_register_style( 'bn-magnific-popup-css', plugin_dir_url( SA_BN_PLUGIN_DIRPATH ) . 'css/magnific-popup.css', array(), $this->plugin_data['Version'] );
				wp_enqueue_style( 'bn-magnific-popup-css' );

				wp_register_style( 'bn-quick-checkout-css', plugin_dir_url( SA_BN_PLUGIN_DIRPATH ) . 'css/bn-quick-checkout.css', array(), $this->plugin_data['Version'] );
				wp_enqueue_style( 'bn-quick-checkout-css' );
			}
		}

		/**
		 * Function to set redirect url after checkout in session
		 *
		 * @param  array   $result Contains data.
		 * @param  integer $order_id Order ID.
		 * @return array   $result.
		 */
		public function wc_buy_now_save_redirect_link_in_session( $result, $order_id ) {
			$bn_set_for     = get_option( 'wc_buy_now_set_for' );
			$quick_checkout = get_option( 'wc_buy_now_is_quick_checkout' );
			$redirect_url   = ( is_array( $result ) && isset( $result['redirect'] ) ) ? $result['redirect'] : $result;

			if ( ( 'buy-now' === $bn_set_for || 'and-buy-now' === $bn_set_for || 'standard' === $bn_set_for ) && 'yes' === $quick_checkout ) {

				if ( strpos( $redirect_url, site_url() ) === 0 ) {
					WC()->session->set( 'wc_bn_redirect_url', $redirect_url );
				}
			} else {

				if ( is_object( WC()->session ) && is_callable( array( WC()->session, 'get' ) ) ) {
					$wc_bn_cart_items_added_via = WC()->session->get( 'wc_bn_cart_items_added_via' );

					// If cart items added via buy now then add query parameter to redirect url.
					if ( 'buy-now' === $wc_bn_cart_items_added_via ) {

						$redirect_url = add_query_arg( 'buy-now-redirect', 'yes', $redirect_url );
						if ( is_array( $result ) ) {
							$result['redirect'] = $redirect_url;
						} else {
							$result = $redirect_url;
						}
						WC()->session->__unset( 'wc_bn_cart_items_added_via' );
					}
				}
			}

			return $result;
		}

		/**
		 * Function to fetch redirect url and close the popup and do redirect
		 */
		public function wc_buy_now_redirect_after_quick_checkout() {

			$global_wc = WC();      // Have to add this code as PHPCS was passing. WC() was not working in empty function.

			if ( empty( $global_wc->session ) || ! is_object( WC()->session ) || ! is_callable( array( WC()->session, 'get' ) ) ) {
				return;
			}

			$redirect_url = WC()->session->get( 'wc_bn_redirect_url' );
			WC()->session->__unset( 'wc_bn_redirect_url' );

			if ( ! empty( $redirect_url ) ) {
				$redirect_url = add_query_arg( 'buy-now-redirect', 'yes', $redirect_url );
				$js           = "jQuery.magnificPopup.close();
						window.top.location.href = '" . $redirect_url . "'";

				wc_enqueue_js( $js );
			}
		}

		/**
		 * Add Express Checkout button on cart page for logged in customer
		 */
		public function add_express_checkout_for_buy_now() {

			if ( is_user_logged_in() && get_option( 'wc_buy_now_set_for' ) === 'express-checkout' ) {

				$express_checkout_button_text = ( get_option( 'wc_buy_now_express_checkout_button_text' ) === '' ) ? __( 'Express Checkout', 'cashier' ) : get_option( 'wc_buy_now_express_checkout_button_text' );

				$checkout_url         = wc_get_page_permalink( 'checkout' );
				$express_checkout_url = add_query_arg( 'wc_buy_now_express_checkout', 'true', $checkout_url );
				?>
				<a href="<?php echo esc_attr( $express_checkout_url ); ?>" name="express-checkout" class="checkout-button button alt"><?php echo esc_html( $express_checkout_button_text ); ?></a>
				<?php
			}
		}

		/**
		 * Function to call after checkout validation
		 *
		 * @param  array $posted Array containing posted data.
		 */
		public function wc_buy_now_after_checkout_validation( $posted ) {

			if ( did_action( 'buy_now_post_fields' ) < 1 ) {
				return;
			}

			$validation_failed = false;

			if ( wc_notice_count( 'error' ) > 1 ) {
				$validation_failed = true;
				wc_clear_notices();
			}

			if ( $validation_failed ) {
				$this->redirect_to_page( 'checkout' );
			}

		}

		/**
		 * Function to create buy now link
		 *
		 * @param  string $url  URL to which Buy Now link will be appended.
		 * @param  array  $args Additional data.
		 * @return string Buy Now link.
		 */
		public function sa_buy_now_link( $url = null, $args = array() ) {

			if ( empty( $url ) && empty( $args ) ) {
				return $url;
			}

			$preserve_cart = get_option( 'wc_buy_now_is_preserve_cart', 'yes' );

			$defaults = array(
				'product'   => '',
				'with-cart' => ( 'yes' === $preserve_cart ) ? 0 : 1,
			);

			$args = wp_parse_args( $args, $defaults );

			if ( empty( $args['product'] ) ) {
				return $url;
			}

			$query_args = array();

			$query_args['buy-now'] = ( is_array( $args['product'] ) ) ? implode( ',', $args['product'] ) : $args['product'];

			if ( empty( $args['qty'] ) ) {
				$product_ids = explode( ',', $query_args['buy-now'] );
				if ( count( $product_ids ) > 1 ) {
					$query_args['qty'] = '1' . str_repeat( ',1', count( $product_ids ) - 1 );
				} else {
					$query_args['qty'] = '1';
				}
			} else {
				$query_args['qty'] = $args['qty'];
			}

			if ( ! empty( $args['coupon'] ) ) {
				$query_args['coupon'] = ( is_array( $args['coupon'] ) ) ? implode( ',', $args['coupon'] ) : $args['coupon'];
			}

			if ( ! empty( $args['ship-via'] ) ) {
				$query_args['ship-via'] = $args['ship-via'];
			}

			if ( ! empty( $args['redirect-to'] ) ) {
				if ( is_string( $args['redirect-to'] ) ) {
					$page_id = wc_get_page_id( $args['redirect-to'] );
				} else {
					$page_id = $args['redirect-to'];
				}
				$query_args['page'] = $page_id;
			}

			$query_args['with-cart'] = ( ! empty( $args['with-cart'] ) ) ? $args['with-cart'] : 0;

			if ( empty( $url ) ) {
				$site_url = trailingslashit( site_url() );
				$url      = add_query_arg( $query_args, $site_url );
			} else {
				$is_url = wp_parse_url( $url );
				if ( false === $is_url ) {
					return $url;
				} else {
					$url = add_query_arg( $query_args, $url );
				}
			}

			return apply_filters( 'sa_buy_now_url', $url, $query_args );

		}

		/**
		 * Get Buy Now link for product
		 *
		 * @param  mixed $product Product object.
		 * @return string Buy Now link.
		 */
		public function get_buy_now_link_for_product( $product = null ) {

			if ( empty( $product ) ) {
				return '';
			}

			$product_id   = ( is_object( $product ) && is_callable( array( $product, 'get_id' ) ) ) ? $product->get_id() : 0;
			$product_type = ( is_object( $product ) && is_callable( array( $product, 'get_type' ) ) ) ? $product->get_type() : '';

			$parent_id = ( is_object( $product ) && is_callable( array( $product, 'get_parent_id' ) ) ) ? $product->get_parent_id() : 0;

			if ( 0 === $parent_id ) {
				$parent_id = $product_id;
			}

			$post_qty       = 1;
			$current_filter = current_filter();

			if ( isset( $_POST['qty'] ) && in_array( $current_filter, array( 'wc_ajax_sa_wc_get_buy_now_button' ), true ) ) {
				check_ajax_referer( 'sa-wc-get-buy-now-button', 'security' );
				$post_qty = ( ! empty( $_POST['qty'] ) ) ? sanitize_text_field( wp_unslash( $_POST['qty'] ) ) : '1'; // phpcs:ignore
			}

			$buy_now_product_meta      = $this->get_buy_now_product_meta( $parent_id );
			$buy_now_storewide_options = $this->get_buy_now_storewide_options();

			if ( 'buy-now' === $buy_now_storewide_options['set_for'] ) {
				return '';
			}

			$preserve_cart = get_option( 'wc_buy_now_is_preserve_cart', 'yes' );
			$with_cart     = ( 'yes' === $preserve_cart ) ? 0 : 1;

			if ( ! empty( $buy_now_product_meta['sa_wc_bn_action'] ) && ( 'bn_default' !== $buy_now_product_meta['sa_wc_bn_action'] ) ) {

				$bn_args            = array();
				$bn_args['product'] = $product_id;
				$bn_args['qty']     = $post_qty;

				if ( ! empty( $buy_now_product_meta['coupons'] ) ) {
					$bn_args['coupon'] = implode( ',', $buy_now_product_meta['coupons'] );
				}
				if ( ! empty( $buy_now_product_meta['shipping'] ) ) {
					$bn_args['ship-via'] = $buy_now_product_meta['shipping'];
				}
				if ( ! empty( $buy_now_product_meta['redirect'] ) ) {
					$bn_args['redirect-to'] = absint( $buy_now_product_meta['redirect'] );
				}

				$bn_args['with-cart'] = $with_cart;

			} else {

				$bn_args = array();

				if ( ! empty( $buy_now_storewide_options['set_for'] ) && 'and-buy-now' === $buy_now_storewide_options['set_for'] ) {

					$buy_now_product_categories = ( ! empty( $buy_now_storewide_options['product_categories'] ) ) ? $buy_now_storewide_options['product_categories'] : array();

					$product_category_ids = wp_get_object_terms( $parent_id, 'product_cat', array( 'fields' => 'ids' ) );
					$matched_category_ids = array_intersect( $buy_now_product_categories, $product_category_ids );

					if ( empty( $buy_now_product_categories ) || ! empty( $matched_category_ids ) ) {

						$bn_args['product'] = $product_id;
						$bn_args['qty']     = $post_qty;

						if ( ! empty( $buy_now_storewide_options['coupons'] ) ) {
							$bn_args['coupon'] = implode( ',', $buy_now_storewide_options['coupons'] );
						}
						if ( ! empty( $buy_now_storewide_options['shipping'] ) ) {
							$bn_args['ship-via'] = $buy_now_storewide_options['shipping'];
						}
						if ( ! empty( $buy_now_storewide_options['redirect'] ) ) {
							$bn_args['redirect-to'] = absint( $buy_now_storewide_options['redirect'] );
						}

						$bn_args['with-cart'] = $with_cart;

					}
				}
			}

			return apply_filters( 'sa_buy_now_link', '', $bn_args );

		}

		/**
		 * Add Buy Now button in loop
		 *
		 * @param string             $add_to_cart_button Existing HTML code for add to cart button.
		 * @param boolean|WC_Product $product            Product object.
		 * @param array              $args               Additional arguments.
		 * @return string            $add_to_cart_button Additional button if added.
		 */
		public function add_buy_now_button( $add_to_cart_button = '', $product = false, $args = array() ) {

			if ( ! empty( $product ) ) {

				$product_id               = ( is_object( $product ) && is_callable( array( $product, 'get_id' ) ) ) ? $product->get_id() : 0;
				$product_type             = ( is_object( $product ) && is_callable( array( $product, 'get_type' ) ) ) ? $product->get_type() : '';
				$product_types_to_exclude = $this->get_excluded_product_types();
				$is_variable              = ( $product instanceof WC_Product_Variable ) ? true : false;

				// check if this product type is excluded.
				if ( in_array( $product_type, $product_types_to_exclude, true ) ) {
					// return woocommerce default add to cart button.
					return $add_to_cart_button;
				}

				$parent_id = ( is_object( $product ) && is_callable( array( $product, 'get_parent_id' ) ) ) ? $product->get_parent_id() : 0;

				$is_variation = '';
				if ( 0 === $parent_id ) {

					$parent_id = $product_id;
				} else {
					$parent_product = wc_get_product( $parent_id );

					// Check if parent is a variable product then is a variation product.
					$is_variation = ( $parent_product instanceof WC_Product_Variable ) ? 'yes' : 'no';
				}

				if ( ! empty( $parent_id ) ) {

					$buy_now_product_meta      = $this->get_buy_now_product_meta( $parent_id );
					$buy_now_storewide_options = $this->get_buy_now_storewide_options();

					if ( 'buy-now' === $buy_now_storewide_options['set_for'] ) {
						return $add_to_cart_button;
					}

					if ( ! empty( $buy_now_product_meta['sa_wc_bn_action'] ) && 'bn_only' === $buy_now_product_meta['sa_wc_bn_action'] ) {

						$remove_add_to_cart_style = '<style type="text/css">a.add_to_cart_button[data-product_id="' . $product_id . '"], #product-' . $parent_id . ' button.single_add_to_cart_button { display: none; }</style>';

						// Check if it not variable product then we can remove add to cart button when bn_only is on.
						if ( true === $is_variable ) {
							$loop_name = wc_get_loop_prop( 'name', '' ); // Get loop name of current loop e.g. related, cross-sells, up-sells.

							// Remove add to cart button only if it a variable kind of product, not in well formed product loops e.g. single product page loop and not on shop page.
							if ( '' === $loop_name && ! is_shop() ) {
								$add_to_cart_button = $remove_add_to_cart_style;
							}
						} else {
							$add_to_cart_button = $remove_add_to_cart_style;
						}
					}

					// check if is variable prouct.
					if ( true === $is_variable ) {
						// return woocommerce default add to cart button.
						return $add_to_cart_button;
					}

					$additional_button_class           = '';
					$additional_button_container_class = '';
					if ( is_shop() ) {
						$additional_button_class           = 'sa_wc_buy_now_button_loop alt';
						$additional_button_container_class = 'sa_wc_buy_now_button_container_loop';
					} elseif ( is_product() ) {
						$additional_button_class           = 'sa_wc_buy_now_button_single';
						$additional_button_container_class = 'sa_wc_buy_now_button_container_single';
					}

					$wc_buy_now_button_text = get_option( 'wc_buy_now_button_text' );
					$button_text            = ( ! empty( $wc_buy_now_button_text ) ) ? $wc_buy_now_button_text : __( 'Buy Now', 'cashier' );

					$buy_now_link   = $this->get_buy_now_link_for_product( $product );
					$buy_now_button = '';

					if ( ! empty( $buy_now_link ) ) {

						$buy_now_button = apply_filters(
							'sa_wc_buy_now_button_html',
							sprintf(
								'<a href="%s" class="%s">%s</a>',
								esc_url( $buy_now_link ),
								esc_attr( 'button sa_wc_buy_now_button ' . $additional_button_class ),
								esc_html( $button_text )
							),
							array(
								'product_meta'      => $buy_now_product_meta,
								'storewide_options' => $buy_now_storewide_options,
							),
							$this
						);

					}

					// Following CSS will format Add to Cart & Buy Now button when both are present for a product - Shop, Single Product and Cart page.
					// Avoid moving to a different CSS file.
					if ( ! is_ajax() && ! is_admin() ) {
						?>
						<style type="text/css">
							.sa_wc_buy_now_button_container_loop,
							.related.products .sa_wc_buy_now_button_container,
							.up-sells .sa_wc_buy_now_button_container,
							.cross-sells .sa_wc_buy_now_button_container {
								display: block;
							}
						</style>
						<?php
					}
					$add_to_cart_button .= ( ! empty( $buy_now_button ) ) ? '<span class="sa_wc_buy_now_button_container ' . $additional_button_container_class . '">' . $buy_now_button . '</span>' : '';

				}
			}

			return $add_to_cart_button;
		}

		/**
		 * Add buy now button to single product page
		 */
		public function add_single_buy_now_button() {

			global $product;

			$buy_now_button = $this->add_buy_now_button( '', $product, array() );

			$allowed_html = wp_kses_allowed_html( 'post' );

			$allowed_html['style'] = array(
				'media' => true,
				'type'  => true,
			);

			echo wp_kses( $buy_now_button, $allowed_html ); // phpcs:ignore

		}

		/**
		 * Get buy now button via ajax
		 */
		public function get_ajax_buy_now_button() {

			check_ajax_referer( 'sa-wc-get-buy-now-button', 'security' );

			$response = array(
				'success' => 'no',
			);

			$post_product_id = ( ! empty( $_POST['product_id'] ) ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : 0; // phpcs:ignore

			if ( ! empty( $post_product_id ) ) {

				$product = wc_get_product( $post_product_id );

				$buy_now_button = $this->add_buy_now_button( '', $product, array() );

				if ( ! empty( $buy_now_button ) ) {
					$response = apply_filters(
						'wc_bn_ajax_buy_now_button',
						array(
							'success'  => 'yes',
							'data'     => $buy_now_button,
							'selector' => 'button.single_add_to_cart_button',
						)
					);
				}
			}

			wp_send_json( $response );

		}

		/**
		 * Buy Now additional styles & scripts for Buy Now plugin
		 */
		public function wc_buy_now_frontend_style_and_script() {

			if ( ! wp_script_is( 'jquery' ) ) {
				wp_enqueue_script( 'jquery' );
			}

			if ( is_product() ) {
				global $product;

				$product_type = ( is_object( $product ) && is_callable( array( $product, 'get_type' ) ) ) ? $product->get_type() : '';

				$product_types_to_exclude = $this->get_excluded_product_types();

				if ( ! in_array( $product_type, $product_types_to_exclude, true ) ) {

					$is_variable = ( $product instanceof WC_Product_Variable ) ? true : false;

					$product_id     = $product->get_id();
					$buy_now_action = get_post_meta( $product_id, 'sa_wc_bn_action', true );
					$global_buy_now = get_option( 'wc_buy_now_set_for' );

					if ( 'bn_only' === $buy_now_action || 'bn_both' === $buy_now_action || 'and-buy-now' === $global_buy_now ) {
						?>
						<script type="text/javascript">
							jQuery(function(){
								var reload_buy_now_button = function() {
									<?php if ( true === $is_variable ) { ?>
										var product_id = jQuery('div.woocommerce-variation-add-to-cart input.variation_id[name="variation_id"]').val();
									<?php } else { ?>
										var product_id = jQuery('[name="add-to-cart"]').val();
									<?php } ?>
									jQuery.ajax({
										url: '?wc-ajax=sa_wc_get_buy_now_button',
										type: 'POST',
										dataType: 'json',
										data: {
											product_id: product_id,
											qty: jQuery('div.quantity input[name="quantity"]').val(),
											security: '<?php echo esc_js( wp_create_nonce( 'sa-wc-get-buy-now-button' ) ); ?>'
										},
										success: function( response ) {
											if ( null !== response && typeof 'undefined' !== response.success && '' !== response.success && 'yes' === response.success ) {
												jQuery('form.cart span.sa_wc_buy_now_button_container_single').remove();
												jQuery(response.selector).after( ' ' + response.data );
											}
										}
									});
								};
								setTimeout(function() {
									reload_buy_now_button()
								}, 1000);
								jQuery(function(){
									jQuery('body').on( 'change', 'div.quantity input[name="quantity"]', function(){
										reload_buy_now_button();
									});
									jQuery('form.variations_form').on('woocommerce_variation_has_changed',function(){
										reload_buy_now_button();
									});
									jQuery('body form.cart').on('click', '.sa_wc_buy_now_button_single', function(e){
										e.preventDefault();
										let buy_now_link = jQuery(this).attr( 'href' );
										let url_vars = get_url_vars( buy_now_link );
										let buy_now_data_html = '';
										for (let url_var in url_vars) {
											if (url_vars.hasOwnProperty(url_var)) {
												let var_value = url_vars[url_var];
												buy_now_data_html += '<input type="hidden" name="' + url_var + '" value="' + var_value + '" />';
											}
										}
										let cart_form = jQuery(this).closest('form.cart');
										// Check if there are no invalid fields(required fields with empty data, fields with invalid data).
										if( 0 === jQuery(cart_form).find(':invalid').length ) {
											jQuery(cart_form).append(buy_now_data_html);
											jQuery(cart_form).submit();
										} else {
											// If form has invalid fields then trigger browser's form validation via submit button's click
											jQuery(cart_form).find('[type="submit"]').click();
										}
									});
								});

								function get_url_vars( url ) {
									let vars = [], hash;
									let hashes = url.slice(url.indexOf('?') + 1).split('&');
									for(let i = 0; i < hashes.length; i++) {
										hash = hashes[i].split('=');
										vars[hash[0]] = hash[1];
									}
									return vars;
								}
							});
						</script>
						<?php
					}
				}
			}

			?>
			<script type="text/javascript">
				(function(jQuery){
					jQuery('.related.products, .up-sells, .cross-sells').find('.sa_wc_buy_now_button_container .sa_wc_buy_now_button').addClass('alt');
					let wc_bn_get_query_variable = function( variable ) {
							let query = window.location.search.substring(1);
							let vars  = query.split("&");
							for( let i = 0; i < vars.length; i++ ) {
								let pair = vars[i].split("=");
								if(pair[0] == variable) {
									return pair[1];
								}
							}
							return false;
					}
					let buy_now_redirect = wc_bn_get_query_variable('buy-now-redirect');
					if( 'yes' === buy_now_redirect ) {
						setTimeout(function(){
							jQuery( document.body ).trigger( 'wc_fragment_refresh');
						}, 0);
					}
				})(jQuery);
			</script>
			<?php

		}

		/**
		 * Get buy now product meta for a product
		 *
		 * @param  integer $product_id The product id.
		 * @return array
		 */
		public function get_buy_now_product_meta( $product_id = 0 ) {

			if ( empty( $product_id ) ) {
				return array();
			}

			$bn_action = get_post_meta( $product_id, 'sa_wc_bn_action', true );

			$coupon          = get_post_meta( $product_id, 'sa_wc_buy_now_coupon', true );
			$shipping_method = get_post_meta( $product_id, 'sa_wc_buy_now_shipping_method', true );
			$redirect_page   = get_post_meta( $product_id, 'sa_wc_buy_now_redirect_page', true );

			$meta = array();

			$meta['sa_wc_bn_action'] = $bn_action;

			$coupons = ( ! empty( $coupon ) ) ? explode( ',', $coupon ) : array();

			$meta['coupons']  = $coupons;
			$meta['shipping'] = $shipping_method;
			$meta['redirect'] = $redirect_page;

			return apply_filters( 'sa_wc_buy_now_product_meta', $meta, $product_id, $this );
		}

		/**
		 * Get buy now storewide option
		 *
		 * @return array
		 */
		public function get_buy_now_storewide_options() {

			$storewide_options = array();

			$set_for            = get_option( 'wc_buy_now_set_for' );
			$product_categories = get_option( 'wc_buy_now_product_categories' );
			$coupons            = get_option( 'wc_buy_now_storewide_coupons' );
			$shipping_method    = get_option( 'wc_buy_now_storewide_shipping_method' );
			$redirect_to_page   = get_option( 'wc_buy_now_storewide_redirect_to_page' );

			$storewide_options['set_for']            = $set_for;
			$storewide_options['product_categories'] = ( ! empty( $product_categories ) ) ? $product_categories : array();
			$storewide_options['coupons']            = ( ! empty( $coupons ) ) ? $coupons : array();
			$storewide_options['shipping']           = $shipping_method;
			$storewide_options['redirect']           = $redirect_to_page;

			return apply_filters( 'sa_wc_buy_now_storewide_options', $storewide_options, $this );

		}

		/**
		 * Remove script of add to cart ajax call
		 */
		public function remove_wc_add_to_cart_ajax_script() {

			if ( get_option( 'woocommerce_enable_ajax_add_to_cart' ) === 'yes' ) {
				wp_dequeue_script( 'wc-add-to-cart' );
			}

		}

		/**
		 * Change Add to cart text for product
		 *
		 * @param  string     $button_text Current button text.
		 * @param  WC_Product $product Product object.
		 * @return string New button text.
		 */
		public function woocommerce_product_change_add_to_cart_text( $button_text, $product ) {

			$product_type = $product->get_type();

			if ( 'variable' === $product_type && is_shop() ) {
				return $button_text;
			}

			$wc_buy_now_button_text = get_option( 'wc_buy_now_button_text' );
			$button_text            = ( ! empty( $wc_buy_now_button_text ) ) ? $wc_buy_now_button_text : __( 'Buy Now', 'cashier' );
			return $button_text;

		}

		/**
		 * Add sub menu page under WooCommerce menu
		 */
		public function wc_buy_now_admin_menu() {

			$page = add_submenu_page(
				'woocommerce',
				__( 'Cashier', 'cashier' ),
				__( 'Cashier', 'cashier' ),
				'manage_woocommerce',
				'sa-wc-cashier',
				array( $this, 'wc_buy_now_admin_page' )
			);
			add_action( 'load-' . $page, array( $this, 'load_buy_now_admin_js' ) );
		}

		/**
		 * Loads script for buy now admin screen.
		 *
		 * @return void
		 */
		public function load_buy_now_admin_js() {
			wp_enqueue_script(
				'wc_buy_now_admin',
				plugin_dir_url( SA_BN_PLUGIN_DIRPATH ) . '/js/wc-buy-now-admin-menu.js',
				array( 'jquery' ),
				WC()->version,
				array( 'in_footer' => true )
			);
			$translation_array = array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( 'cashier_buy_now_nounce' ),
			);
			wp_localize_script( 'wc_buy_now_admin', 'cashier_buy_now_object', $translation_array );

		}

		/**
		 * Buy Now enhanced select script start
		 */
		public function enhanced_select_script_start() {

			$suffix      = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			$assets_path = str_replace( array( 'http:', 'https:' ), '', WC()->plugin_url() ) . '/assets/';

			// Register scripts.
			if ( ! wp_script_is( 'woocommerce_admin', 'registered' ) ) {
				wp_register_script( 'woocommerce_admin', WC()->plugin_url() . '/assets/js/admin/woocommerce_admin' . $suffix . '.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip' ), WC_VERSION, false );
			}
			if ( ! wp_script_is( 'select2', 'registered' ) ) {
				wp_register_script( 'select2', WC()->plugin_url() . '/assets/js/select2/select2' . $suffix . '.js', array( 'jquery', 'select2' ), WC()->version, false );
			}
			if ( ! wp_script_is( 'wc-enhanced-select', 'registered' ) ) {
				wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'select2' ), WC_VERSION, false );
			}
			$sa_buy_now_select_params = array(
				'i18n_matches_1'            => _x( 'One result is available, press enter to select it.', 'enhanced select', 'cashier' ),
				'i18n_matches_n'            => _x( '%qty% results are available, use up and down arrow keys to navigate.', 'enhanced select', 'cashier' ),
				'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'cashier' ),
				'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'cashier' ),
				'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'cashier' ),
				'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'cashier' ),
				'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'cashier' ),
				'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'cashier' ),
				'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'cashier' ),
				'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'cashier' ),
				'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'cashier' ),
				'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'cashier' ),
				'ajax_url'                  => admin_url( 'admin-ajax.php' ),
				'search_products_nonce'     => wp_create_nonce( 'search-products' ),
				'search_customers_nonce'    => wp_create_nonce( 'search-customers' ),
			);

			$params = array(
				'strings' => array(
					'import_products' => '',
					'export_products' => '',
				),
				'urls'    => array(
					'import_products' => '',
					'export_products' => '',
				),
			);

			wp_localize_script( 'woocommerce_admin', 'woocommerce_admin', $params );
			wp_localize_script( 'select2', 'wc_enhanced_select_params', $sa_buy_now_select_params );

			wp_enqueue_script( 'woocommerce_admin' );
			wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );

			wp_enqueue_script( 'select2' );
			wp_enqueue_script( 'wc-enhanced-select' );
			wp_enqueue_style( 'select2', $assets_path . 'css/select2.css', array(), WC_VERSION );

		}

		/**
		 * Buy Now enhanced select script end
		 */
		public function enhanced_select_script_end() {
			?>
			<script type="text/javascript">

				jQuery(function(){
					if ( typeof getEnhancedSelectFormatString == "undefined" ) {
						function getEnhancedSelectFormatString() {
							var formatString = {
								noResults: function() {
									return wc_enhanced_select_params.i18n_no_matches;
								},
								errorLoading: function() {
									return wc_enhanced_select_params.i18n_ajax_error;
								},
								inputTooShort: function( args ) {
									var remainingChars = args.minimum - args.input.length;

									if ( 1 === remainingChars ) {
										return wc_enhanced_select_params.i18n_input_too_short_1;
									}

									return wc_enhanced_select_params.i18n_input_too_short_n.replace( '%qty%', remainingChars );
								},
								inputTooLong: function( args ) {
									var overChars = args.input.length - args.maximum;

									if ( 1 === overChars ) {
										return wc_enhanced_select_params.i18n_input_too_long_1;
									}

									return wc_enhanced_select_params.i18n_input_too_long_n.replace( '%qty%', overChars );
								},
								maximumSelected: function( args ) {
									if ( args.maximum === 1 ) {
										return wc_enhanced_select_params.i18n_selection_too_long_1;
									}

									return wc_enhanced_select_params.i18n_selection_too_long_n.replace( '%qty%', args.maximum );
								},
								loadingMore: function() {
									return wc_enhanced_select_params.i18n_load_more;
								},
								searching: function() {
									return wc_enhanced_select_params.i18n_searching;
								}
							};

							var language = { 'language' : formatString };

							return language;
						}
					}

					jQuery( '[class= "select2_search_products_coupons"]' ).each( function() {

						var select2_args = {
							allowClear:  jQuery( this ).data( 'allow_clear' ) ? true : false,
							placeholder: jQuery( this ).data( 'placeholder' ),
							minimumInputLength: jQuery( this ).data( 'minimum_input_length' ) ? jQuery( this ).data( 'minimum_input_length' ) : '3',
							escapeMarkup: function( m ) {
								return m;
							},
							ajax: {
								url:         '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
								dataType:    'json',
								quietMillis: 250,
								data: function( params, page ) {
									return {
										term:     params.term,
										action:   jQuery( this ).data( 'action' ) || 'woocommerce_json_search_products_and_variations',
										security: jQuery( this ).data( 'security' )
									};
								},
								processResults: function( data, page ) {
									var terms = [];
									if ( data ) {
										jQuery.each( data, function( id, text ) {
											terms.push( { id: id, text: text } );
										});
									}
									return { results: terms };
								},
								cache: true
							}
						};

						select2_args = jQuery.extend( select2_args, getEnhancedSelectFormatString() );

						jQuery( this ).select2( select2_args );
					});
				});

			</script>
			<?php
		}

		/**
		 * Function to search coupons
		 */
		public function woocommerce_json_search_coupons() {

			global $wpdb;

			check_ajax_referer( 'search-coupons', 'security' );
			$term = ( ! empty( $_GET['term'] ) ) ? (string) urldecode( sanitize_text_field( wp_unslash( $_GET['term'] ) ) ) : ''; // phpcs:ignore

			if ( empty( $term ) ) {
				die();
			}

			$posts = wp_cache_get( 'sa_bn_search_coupons', 'woocommerce_buy_now' );

			if ( false === $posts ) {
				$posts = $wpdb->get_results( // phpcs:ignore
					$wpdb->prepare(
						"SELECT *
									FROM {$wpdb->prefix}posts
									WHERE post_type = %s
									AND post_title LIKE %s
									AND post_status = %s",
						'shop_coupon',
						$wpdb->esc_like( $term ) . '%',
						'publish'
					)
				);
				wp_cache_set( 'sa_bn_search_coupons', $posts, 'woocommerce_buy_now' );
			}

			$coupons_found                      = array();
			$all_discount_types                 = wc_get_coupon_types();
			$all_discount_types['smart_coupon'] = __( 'Store Credit / Gift Certificate', 'cashier' );

			if ( $posts ) {
				foreach ( $posts as $post ) {

					$discount_type = get_post_meta( $post->ID, 'discount_type', true );

					if ( ! empty( $all_discount_types[ $discount_type ] ) ) {
						$coupons_found[ get_the_title( $post->ID ) ] = get_the_title( $post->ID ) . ' (Type: ' . $all_discount_types[ $discount_type ] . ')';
					}
				}
			}

			wp_send_json( $coupons_found );

		}

		/**
		 * Buy Now search products & only variations
		 */
		public function wc_buy_now_json_search_products_and_variations() {

			if ( ! class_exists( 'WC_AJAX' ) ) {
				if ( ! defined( 'WC_PLUGIN_FILE' ) ) {
					wp_send_json(
						array(
							'success' => 'false',
							'message' => __( 'Could not locate WooCommerce', 'cashier' ),
						)
					);
				}
				include_once dirname( WC_PLUGIN_FILE ) . '/includes/class-wc-ajax.php';
			}

			$term = ( ! empty( $_GET['term'] ) ) ? (string) urldecode( sanitize_text_field( wp_unslash( $_GET['term'] ) ) ) : ''; // phpcs:ignore

			WC_AJAX::json_search_products( $term, true );

		}

		/**
		 * Remove variation parent and products with excluded product types from search result
		 *
		 * @param  array $products Array of product ids with product name.
		 * @return array $products Array of product ids with product name after excluded products
		 */
		public function filter_found_products( $products = null ) {

			if ( is_array( $products ) && ! empty( $products ) ) {

				$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ); // phpcs:ignore
				if ( 'wc_buy_now_json_search_products_and_variations' === $backtrace[5]['function'] ) {
					$product_ids = array_keys( $products );
					$parent_ids  = array_map( 'wp_get_post_parent_id', $product_ids );
					$parent_ids  = array_filter( array_unique( $parent_ids ) );
					foreach ( $parent_ids as $parent ) {
						unset( $products[ $parent ] );
					}
				}

				$product_types_to_exclude = $this->get_excluded_product_types();
				foreach ( $products as $product_id => $product_name ) {

					$product      = wc_get_product( $product_id );
					$product_type = $product->get_type();

					// Check if this product type is excluded.
					if ( in_array( $product_type, $product_types_to_exclude, true ) ) {
						// Remove this product from search results.
						unset( $products[ $product_id ] );
					}
				}
			}

			return $products;

		}

		/**
		 * Register Buy Now scripts
		 */
		public function register_buy_now_script() {
			$get_tab = ( ! empty( $_GET['tab'] ) ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore

			if ( ! empty( $get_tab ) && 'sa-cfw-settings' === $get_tab ) { // phpcs:ignore
				add_thickbox();
				wp_register_script( 'wc_buy_now', plugin_dir_url( SA_BN_PLUGIN_DIRPATH ) . 'js/wc-buy-now.js', array( 'jquery' ), WC()->version, false );
				$buy_now_translation = array(
					'preview_text'              => esc_html__( 'Preview', 'cashier' ),
					'standard_tip_text'         => esc_html__( 'This disables \'storewide\' Buy Now behavior. If a product does not have Buy Now settings, it will follow the standard WooCommerce purchase process.', 'cashier' ),
					'express_checkout_tip_text' => esc_html__( '\'Express Checkout\' speeds up the checkout process for logged in customers using payment method, billing address and shipping data from their recent order.', 'cashier' ),
					'buy_now_tip_text'          => esc_html__( 'Turn on quicker checkout with Buy Now for your entire store. You can override this default for individual products if you want.', 'cashier' ),
					'and_buy_now_tip_text'      => esc_html__( 'Keep the default \'Add to cart\' button, but also add \'Buy Now\' option. You can override this default for individual products if you want.', 'cashier' ),
				);
				wp_localize_script( 'wc_buy_now', 'buy_now_translation', $buy_now_translation );
				wp_enqueue_script( 'wc_buy_now' );
			}
		}

		/**
		 * Additiona style & script for Buy Now section
		 */
		public function wc_buy_now_styles_and_scripts() {
			$page = ( ! empty( $_GET['page'] ) ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore
			$tab = ( ! empty( $_GET['tab'] ) ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore
			if ( ( ! empty( $page ) && 'sa-wc-cashier' === $page ) || ( ! empty( $tab ) && 'sa-cfw-settings' === $tab ) ) { // phpcs:ignore
				?>
				<style type="text/css">
					th {
						width: 30% !important;
					}

					input[type=text] {
						width: 50%;
					}

					#TB_window img#TB_Image {
						border: none !important;
					}

					#wc_bn_storewide_settings table td.forminp-radio {
						border: 1px solid #ccc;
						background: #e5e5e5;
					}

					#wc_bn_storewide_settings tr.single_select_page {
						border-bottom: 1px solid #ccc;
					}
				</style>
				<?php
			}
		}


		/**
		 * Function to add a column to the Payment Gateway table to show whether the gateway supports Buy Now's one click checkout.
		 *
		 * @param array $header payment gateway columns.
		 * @return array $header_new payment gateway columns.
		 *
		 * @since 2.6.0
		 */
		public function payment_gateways_support_column( $header = array() ) {

			$header_new = array_slice( $header, 0, count( $header ) - 1, true ) + array( 'sa_bn_wc_one_click_checkout' => __( 'One-Click Checkout', 'cashier' ) ) +

			array_slice( $header, count( $header ) - 1, count( $header ) - ( count( $header ) - 1 ), true );

			return $header_new;
		}

		/**
		 * Check whether the payment gateway passed in supports Buy Now's one click checkout or not.
		 * Display in the Payment Gateway column.
		 *
		 * @param WC_Payment_Gateway $gateway payment gateway object.
		 *
		 * @since 2.6.0
		 */
		public static function payment_gateways_support( $gateway = object ) {

			echo '<td>';

			$supported_gateways = apply_filters( 'sa_wc_buy_now_supported_gateways', array( 'bacs', 'cheque', 'cod' ) );

			if ( isset( $gateway->id ) && is_array( $supported_gateways ) && in_array( $gateway->id, $supported_gateways, true ) ) {
				$status_html = '<span class="status-enabled tips" data-tip="' . esc_attr__( 'Supports Buy Now\'s One-Click Checkout.', 'cashier' ) . '">' . esc_html__( 'Yes', 'cashier' ) . '</span>';
			} else {
				$status_html = apply_filters( 'sa_wc_buy_now_unsupported_gateway_status_html' . ( isset( $gateway->id ) ? '_' . $gateway->id : '' ), '-' );
			}

			$allowed_html = wp_kses_allowed_html( 'post' );

			echo wp_kses( $status_html, $allowed_html );

			echo '</td>';
		}

		/**
		 * Page to generate buy now link
		 */
		public function wc_buy_now_admin_page() {

			global $thepostid;

			if ( empty( $thepostid ) ) {
				$thepostid = 1; // To prevent PHP Notice:  Trying to get property of non-object in wc-meta-box-functions.php.
			}

			$preserve_cart = get_option( 'wc_buy_now_is_preserve_cart', 'yes' );
			$with_cart     = ( 'yes' === $preserve_cart ) ? 0 : 1;

			do_action( 'sa_wc_buy_now_enhanced_select_script_start' );

			include_once 'admin/class-sa-wc-generate-buy-now-link.php';

			$bn_generate_link = SA_WC_Generate_Buy_Now_Link::get_instance();
			$bn_generate_link->render_bn_create_link_fields();

			do_action( 'sa_wc_buy_now_enhanced_select_script_end' );
			?>

			<script type="text/javascript">

				/*jQuery(function(){

					jQuery( '#buy_now_generate_link_product, #buy_now_generate_link_coupon, #buy_now_shipping_method, #buy_now_redirect_link' ).on( 'change', function() {

						var generated_url       = '<?php echo esc_url( get_site_url() ) . '/?'; ?>';
						var selected_products   = jQuery('#buy_now_generate_link_product').val();
						var selected_coupons    = jQuery('#buy_now_generate_link_coupon').val();
						var shipping_method     = jQuery('#buy_now_shipping_method').find('option:selected').val();
						var redirect_page       = jQuery('#buy_now_redirect_link').find('option:selected').val();

						jQuery('#buy_now_generated_link').text('');

						if( selected_products == null )
							return;

						generated_url += 'buy-now=' + selected_products;

						getProductDetails( );

						var quantity = JSON.stringify(selected_products).split(',');
						quantity.map(function(x, i, ar){
							ar[i] = 1;
						});

						generated_url += '&qty=' + quantity.join(',');

						if( selected_coupons != null ) {
							generated_url += '&coupon=' + selected_coupons;
						}

						if ( shipping_method != '' ) {
							generated_url += '&ship-via=' + shipping_method;
						}

						if ( redirect_page != '' ) {
							generated_url += '&page=' + redirect_page;
						}

						generated_url += '&with-cart=<?php echo esc_html( $with_cart ); ?>';

						let buy_now_button = '<p><a href="' + generated_url + '" class="button">Buy Now</a></p>';

						// jQuery('#buy_now_generated_link').text(generated_url);
						jQuery('#buy_now_generated_link').text(buy_now_button);

					});

					jQuery('#buy_now_shipping_method').css( 'width', '50%' );
					jQuery('#buy_now_redirect_link').css( 'width', '50%' );

				});

				jQuery(document).on( 'change', function () {
					if ( jQuery('#buy_now_generated_link').text().length > 0 ) {
						jQuery('#bn-click-to-copy-btn').prop( 'disabled', false );
					}
				});

				function copy_to_clipboard() {
					var copyText = document.getElementById("buy_now_generated_link");
					copyText.select();
					document.execCommand("copy");
					document.getElementById("bn-click-to-copy-btn").innerHTML = '<?php echo esc_html__( 'Copied!', 'cashier' ); ?>';
					setTimeout(function(){
						document.getElementById("bn-click-to-copy-btn").innerHTML = '<?php echo esc_html__( 'Click To Copy', 'cashier' ); ?>';
					}, 1000);
				}
				*/
			</script>
			<?php
		}

		/**
		 * Restore Buy Now persistent cart
		 *
		 * @param  WC_Order $order Order object.
		 */
		public function restore_buy_now_persistent_cart( $order ) {
			global $wp;

			$get_order_received = ( ! empty( $_GET['order-received'] ) ) ? absint( $_GET['order-received'] ) : 0; // phpcs:ignore
			$buy_now_redirect = isset( $_GET['buy-now-redirect'] ) ? wc_clean( wp_unslash( $_GET['buy-now-redirect'] ) ) : 'no'; // phpcs:ignore

			if ( 'yes' === $buy_now_redirect ) {
				if ( ! empty( $wp->query_vars['order-received'] ) ) {
					$order_received = $wp->query_vars['order-received'];
				} else {
					$order_received = $get_order_received;
				}
				if ( ! empty( $order_received ) && $order_received > 0 ) {
					if ( WC()->session->__isset( 'buy_now_persistent_cart' ) ) {
						$buy_now_persistent_cart = WC()->session->get( 'buy_now_persistent_cart' );
						$session_cart_updated    = $this->update_session_cart_from_persistent_cart( $buy_now_persistent_cart );
						if ( true === $session_cart_updated ) {
							WC()->session->__unset( 'buy_now_persistent_cart' );
						}
					} else {
						$current_user_id = get_current_user_id();
						if ( $current_user_id > 0 ) {
							if ( function_exists( 'get_user_attribute' ) ) {
								$buy_now_persistent_cart = get_user_attribute( $current_user_id, '_buy_now_persistent_cart' );
							} else {
								$buy_now_persistent_cart = get_user_meta( $current_user_id, '_buy_now_persistent_cart', true ); // phpcs:ignore
							}
							$session_cart_updated = $this->update_session_cart_from_persistent_cart( $buy_now_persistent_cart );
							if ( true === $session_cart_updated ) {
								if ( function_exists( 'delete_user_attribute' ) ) {

									delete_user_attribute( $current_user_id, '_buy_now_persistent_cart' );
								} else {
									delete_user_meta( $current_user_id, '_buy_now_persistent_cart' ); // phpcs:ignore
								}
							}
						}
					}
				}
			}

		}

		/**
		 * Update session cart from given persistent cart array
		 *
		 * @param  array $buy_now_persistent_cart buy now cart array.
		 * @return bool $bool True/false.
		 */
		public function update_session_cart_from_persistent_cart( $buy_now_persistent_cart = array() ) {

			if ( is_array( $buy_now_persistent_cart ) && ! empty( $buy_now_persistent_cart['cart'] ) ) {
				WC()->session->set( 'cart', $buy_now_persistent_cart['cart'] );
				WC()->session->set( 'applied_coupons', $buy_now_persistent_cart['applied_coupons'] );
				WC()->session->set( 'cart_totals', $buy_now_persistent_cart['cart_totals'] );
				WC()->session->set( 'coupon_discount_totals', $buy_now_persistent_cart['coupon_discount_totals'] );
				WC()->session->set( 'coupon_discount_tax_totals', $buy_now_persistent_cart['coupon_discount_tax_totals'] );
				WC()->session->set( 'removed_cart_contents', $buy_now_persistent_cart['removed_cart_contents'] );

				return true;
			}

			return false;
		}

		/**
		 * Add endpoint for Buy Now Pay page
		 */
		public function add_buy_now_pay_endpoint() {

			add_rewrite_endpoint( 'buy-now-pay', EP_ROOT | EP_PAGES );

		}

		/**
		 * Process quick checkout for Buy Now
		 */
		public function process_quick_checkout() {

			if ( defined( 'DOING_AJAX' ) ) {
				return;
			}

			$get_buy_now_action = ( ! empty( $_GET['buy-now-action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['buy-now-action'] ) ) : ''; // phpcs:ignore

			if ( empty( $get_buy_now_action ) || 'quick-checkout' !== $get_buy_now_action ) { // phpcs:ignore
				return;
			}

			$this->wc_buy_now_show_checkout_in_popup();

		}

		/**
		 * Function to redirect to Buy Now pay page
		 */
		public function buy_now_pay_template_redirect() {

			if ( defined( 'DOING_AJAX' ) ) {
				return;
			}

			global $wp_query;

			if ( ( isset( $_POST['_wpnonce'] ) ) && ! wp_verify_nonce( wc_clean( wp_unslash( $_POST['_wpnonce'] ) ), 'buy-now-pay-action' ) ) { // phpcs:ignore
				return;
			}

			if ( empty( $wp_query->query_vars['buy-now-pay'] ) || ! empty( $_POST['buy_now_complete_payment_button'] ) ) { // phpcs:ignore
				return;
			}

			$order = wc_get_order( $wp_query->query_vars['buy-now-pay'] );

			$this->buy_now_get_template( 'form-buy-now-pay.php', array( 'order' => $order ) );
			exit;

		}
		

		/**
		 * Add products and apply coupons to cart
		 */
		public function process_products_and_coupons() {
			

			$is_express_checkout = ( isset( $_GET['wc_buy_now_express_checkout'] ) ) ? sanitize_text_field( wp_unslash( $_GET['wc_buy_now_express_checkout'] ) ) : ''; // phpcs:ignore

			if ( 'true' === $is_express_checkout ) {
				$this->checkout_redirect();
			}

			$query_string          = '';
			$server_request_method = ( ! empty( $_SERVER['REQUEST_METHOD'] ) ) ? wc_clean( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : ''; // phpcs:ignore
			if ( 'GET' === $server_request_method ) {
				$query_string = ( isset( $_SERVER['QUERY_STRING'] ) ) ? wc_clean( wp_unslash( $_SERVER['QUERY_STRING'] ) ) : ''; // phpcs:ignore
			} elseif ( 'POST' === $server_request_method ) {
				if ( isset( $_POST['buy-now'] ) && ! isset( $_POST['sa-wc-buy-now-nonce'] ) ) { // phpcs:ignore
					$_POST['sa-wc-buy-now-nonce'] = wp_create_nonce( 'sa-wc-process-products-and-coupons' );
				}
				if ( ( isset( $_POST['sa-wc-buy-now-nonce'] ) ) && wp_verify_nonce( wc_clean( wp_unslash( $_POST['sa-wc-buy-now-nonce'] ) ), 'sa-wc-process-products-and-coupons' ) ) { // phpcs:ignore
					$query_string = http_build_query( $_POST ); // phpcs:ignore
					$query_string = sanitize_text_field( wp_unslash( $query_string ) );
				}
			}

			$url_args = array();

			if ( ! empty( $query_string ) ) {
				parse_str( $query_string, $url_args );
			}

			if ( ! isset( $url_args['buy-now'] ) || empty( $url_args['buy-now'] ) ) {
				return;
			}

			if ( empty( $url_args['with-cart'] ) || '1' !== $url_args['with-cart'] ) {
				$this->clear_cart_before_buy_now();
			}

			$product_to_add = explode( ',', $url_args['buy-now'] );

			if ( isset( $url_args['qty'] ) && ! empty( $url_args['qty'] ) ) {

				$related_quanity = explode( ',', $url_args['qty'] );

				if ( count( $related_quanity ) === 1 ) {

					$product_to_add = array_fill_keys( $product_to_add, $related_quanity[0] );

				} elseif ( count( $related_quanity ) !== count( $product_to_add ) ) {

					$this->wc_add_notice( sprintf( __( 'Can\'t procced for purchase. Buy Now link is invalid.', 'cashier' ) ), 'error' );
					$this->redirect_to_page( 'shop' );

				} elseif ( count( $related_quanity ) === count( $product_to_add ) ) {

					$product_to_add = array_combine( $product_to_add, $related_quanity );

				}
			} else {

				$product_to_add = array_fill_keys( $product_to_add, 1 );
			}

			foreach ( $product_to_add as $product_id => $quantity ) {

				$variation_id   = '';
				$variation_data = array();

				if ( wp_get_post_parent_id( $product_id ) > 0 ) {

					$_product = new WC_Product_Variation( $product_id );

					$variation_id   = $_product->get_id();
					$variation_data = wc_get_product_variation_attributes( $variation_id );
				} else {
					$_product = wc_get_product( $product_id );
				}

				$parent_id = $this->get_parent( $product_id );
				$cart_id   = WC()->cart->generate_cart_id( $parent_id, $variation_id, $variation_data );
				$key       = WC()->cart->find_product_in_cart( $cart_id );
				if ( empty( $key ) ) {
					$cart_item_key = WC()->cart->add_to_cart( $parent_id, $quantity, $variation_id, $variation_data );
					WC()->cart->set_quantity( $cart_item_key, $quantity );
				} else {
					if ( ! $_product->is_sold_individually() ) {
						WC()->cart->set_quantity( $key, WC()->cart->cart_contents[ $key ]['quantity'] + $quantity );
					}
				}
				

				if ( wc_notice_count( 'error' ) ) {
					$this->redirect_to_page( 'shop' );
				}
			}

			if ( is_object( WC()->session ) && is_callable( array( WC()->session, 'set' ) ) ) {
				// Flag to check whether cart items added via buy now after checkout redirect.
				WC()->session->set( 'wc_bn_cart_items_added_via', 'buy-now' );
			}

			if ( isset( $url_args['coupon'] ) && ! empty( $url_args['coupon'] ) ) {

				$coupon_to_apply = explode( ',', $url_args['coupon'] );

				foreach ( $coupon_to_apply as $coupon_code ) {
					if ( ! WC()->cart->has_discount( $coupon_code ) ) {
						WC()->cart->add_discount( $coupon_code );
					}
				}

				if ( wc_notice_count( 'error' ) ) {
					$this->redirect_to_page( 'cart' );
				}
			}
			if ( isset( $url_args['page'] ) && ! empty( $url_args['page'] ) ) {

				$quick_checkout     = get_option( 'wc_buy_now_is_quick_checkout' );
				$wc_buy_now_set_for = get_option( 'wc_buy_now_set_for' );

				$redirect_url = $this->get_redirect_url_after_buy_now_process( get_permalink( $url_args['page'] ) );

				if ( 'yes' === $quick_checkout && 'and-buy-now' === $wc_buy_now_set_for ) {
					$redirect_url = add_query_arg( array( 'buy-now-action' => 'quick-checkout' ), $redirect_url );
				}
				wp_safe_redirect( $redirect_url );
				exit;

			}

			$this->checkout_redirect();

		}

		/**
		 * Process Buy Now Pay action
		 */
		public function process_buy_now_pay_action() {

			$query_string = ( isset( $_SERVER['QUERY_STRING'] ) ) ? sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ) : ''; // phpcs:ignore

			$url_args = array();

			if ( ! empty( $query_string ) ) {
				parse_str( $query_string, $url_args );
			}

			if ( ! isset( $url_args['buy-now-pay'] ) || empty( $url_args['buy-now-pay'] ) ) {
				return;
			}

			if ( ! empty( $_POST['buy_now_complete_payment_button'] ) ) { // phpcs:ignore
				$nonce = ( ! empty( $_POST['_wpnonce'] ) ) ? wc_clean( wp_unslash( $_POST['_wpnonce'] ) ) : ''; // phpcs:ignore
				if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'buy-now-pay-action' ) ) {
					$message = __( 'We were unable to process payment, please try again.', 'cashier' );
					if ( ! wc_has_notice( $message, 'error' ) ) {
						$this->wc_add_notice( $message, 'error' );
					}
					unset( $_POST['buy_now_complete_payment_button'] ); // phpcs:ignore
					$redirect_url = add_query_arg( 'buy-now-pay', $url_args['buy-now-pay'], trailingslashit( home_url() ) );
					wp_safe_redirect( $redirect_url );
					exit;
				}

				WC()->session->order_awaiting_payment = $url_args['buy-now-pay'];

				$this->checkout_redirect();
			}

		}

		/**
		 * Add Buy Now shortcode
		 */
		public function add_buy_now_shortcode() {
			add_shortcode( 'sa_buy_now', array( $this, 'execute_buy_now_shortcode' ) );
		}

		/**
		 * Function to execute Buy Now shortcode
		 *
		 * @param  array $atts Shortcode attributes.
		 * @return string URL
		 */
		public function execute_buy_now_shortcode( $atts = null ) {

			$defaults = array(
				'product'   => '',
				'with-cart' => 0,
			);

			$args = wp_parse_args( $atts, $defaults );

			$url = apply_filters( 'sa_buy_now_link', '', $args );

			return esc_url( $url );

		}

		/**
		 * Redirect to specified WooCommerce page
		 *
		 * @param  string $page Page on which to redirect.
		 */
		public function redirect_to_page( $page = 'shop' ) {

			$redirect_url = get_permalink( wc_get_page_id( $page ) );
			if ( empty( $redirect_url ) ) {
				$redirect_url = ( ! empty( $_SERVER['HTTP_REFERER'] ) ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : home_url(); // phpcs:ignore
			}

			wp_safe_redirect( $this->get_redirect_url_after_buy_now_process( $redirect_url ) );
			exit;

		}

		/**
		 * Maintain the args in the url
		 *
		 * @param  string $url Existing URL.
		 * @return string $url
		 */
		public function get_redirect_url_after_buy_now_process( $url = '' ) {

			if ( empty( $url ) ) {
				return $url;
			}

			$query_string = ( isset( $_SERVER['QUERY_STRING'] ) ) ? sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ) : ''; // phpcs:ignore

			$url_args = array();

			if ( ! empty( $query_string ) ) {
				parse_str( $query_string, $url_args );
			}

			$bn_params  = array( 'add-to-cart', 'buy-now', 'qty', 'coupon', 'page', 'with-cart', 'ship-via' );
			$url_params = array_diff_key( $url_args, array_flip( $bn_params ) );

			$bn_url = remove_query_arg( 'wc_buy_now_express_checkout', add_query_arg( $url_params, $url ) );

			return apply_filters( 'redirect_url_after_buy_now', $bn_url );
		}

		/**
		 * Process automated checkout for logged in users
		 */
		public function checkout_redirect() {
			global $wpdb;

			$current_user = wp_get_current_user();
			$customer_id  = $current_user->ID;

			$woocommerce_checkout = WC()->checkout();

			$temp_buy_now_complete_payment_button = '';
			if ( ( ! empty( $_POST['_wpnonce'] ) ) && wp_verify_nonce( wc_clean( wp_unslash( $_POST['_wpnonce'] ) ), 'buy-now-pay-action' ) ) { // phpcs:ignore
				$temp_buy_now_complete_payment_button = ( ! empty( $_POST['buy_now_complete_payment_button'] ) ) ? sanitize_text_field( wp_unslash( $_POST['buy_now_complete_payment_button'] ) ) : ''; // phpcs:ignore
			}

			$last_order_id        = ( $customer_id > 0 ) ? $this->get_last_order_of_customer( $customer_id ) : '';
			$available_gateways   = ( ! empty( $last_order_id ) ) ? WC()->payment_gateways->get_available_payment_gateways() : array();
			$old_payment_method   = ( ! empty( $last_order_id ) ) ? get_post_meta( $last_order_id, '_payment_method', true ) : '';
			$bn_complete_checkout = ( ! empty( $available_gateways ) && array_key_exists( $old_payment_method, $available_gateways ) ) ? true : false;

			if ( $bn_complete_checkout ) {

				$last_order    = wc_get_order( $last_order_id );
				$customer_data = get_user_meta( $customer_id );

				$order_address_fields = array();
				$last_order_data      = array();
				$new_order_data       = array();

				$order_address_fields = array_merge(
					array_keys( $woocommerce_checkout->get_checkout_fields( 'billing' ) ),
					array_keys( $woocommerce_checkout->get_checkout_fields( 'shipping' ) )
				);

				$last_billing_data = $last_order->get_address( 'billing' ); // get biling data from last order.
				foreach ( $last_billing_data as $field_name => $field_value ) {
					$last_order_data[ 'billing_' . $field_name ] = $field_value;
				}

				$last_shipping_data = $last_order->get_address( 'shipping' ); // get shipping data from last order.
				foreach ( $last_shipping_data as $field_name => $field_value ) {
					$last_order_data[ 'shipping_' . $field_name ] = $field_value;
				}

				foreach ( $order_address_fields as $field_name ) {
					if ( isset( $last_order_data[ $field_name ] ) && ! empty( $last_order_data[ $field_name ] ) ) {
						// fetch data from last order.
						$new_order_data[ $field_name ] = $last_order_data[ $field_name ];
					} elseif ( isset( $customer_data[ $field_name ][0] ) && ! empty( $customer_data[ $field_name ][0] ) ) {
						// if data is not found in last order then get it from customer meta.
						$new_order_data[ $field_name ] = $customer_data[ $field_name ][0];
					}
				}

				$_POST = $new_order_data;

				if ( ! empty( $temp_buy_now_complete_payment_button ) ) {
					$_POST['buy_now_complete_payment_button'] = $temp_buy_now_complete_payment_button; // phpcs:ignore
				}

				if ( $this->is_wc_gte_34() ) {
					$_REQUEST['woocommerce-process-checkout-nonce'] = wp_create_nonce( 'woocommerce-process_checkout' ); // phpcs:ignore
				} else {
					$_POST['_wpnonce'] = wp_create_nonce( 'woocommerce-process_checkout' ); // phpcs:ignore
				}

				$_POST['payment_method'] = $old_payment_method; // phpcs:ignore

				/*
				 * This code processes checkout directly even if it's user first order
				if ( WC()->cart->needs_payment() && empty( $_POST['payment_method'] ) ) { // phpcs:ignore

					if ( count( $available_gateways ) ) {
						current( $available_gateways )->set_current();
					}

					foreach ( $available_gateways as $gateway ) {

						if ( $gateway->chosen ) {
							$_POST['payment_method'] = esc_attr( $gateway->id ); // phpcs:ignore
							break;
						}
					}
				}
				*/

				if ( WC()->cart->needs_shipping() ) {

					$_POST['ship_to_different_address'] = 1; // Flag to prevent woocommerce from copying shipping data from billing data.

					$available_methods = WC()->shipping->get_shipping_methods();

					$free_shipping_coupon = false;

					$coupon_code = ( ! empty( $_GET['coupon'] ) ) ? sanitize_text_field( wp_unslash( $_GET['coupon'] ) ) : ''; // phpcs:ignore
					$ship_via    = ( ! empty( $_GET['ship-via'] ) ) ? sanitize_text_field( wp_unslash( $_GET['ship-via'] ) ) : ''; // phpcs:ignore

					if ( ! empty( $coupon_code ) ) {
						$coupon = new WC_Coupon( $coupon_code );

						$coupon_free_shipping = $coupon->get_free_shipping();

						if ( $coupon instanceof WC_Coupon && ! empty( $coupon_free_shipping ) && 1 === $coupon_free_shipping ) {
							$free_shipping_coupon = true;
						}
					}

					if ( ! empty( $ship_via ) && ! empty( $available_methods ) && array_key_exists( $ship_via, $available_methods ) ) {

						$_POST['shipping_method'] = $ship_via; // phpcs:ignore

					} elseif ( $free_shipping_coupon ) {

						$_POST['shipping_method'] = 'free_shipping'; // phpcs:ignore

					} elseif ( ! empty( $last_order_id ) ) {

						// Needed to get exact shipping method name from last order.
						$last_order_shipping_method = get_post_meta( $last_order_id, '_shipping_method', true );
						$last_order_shipping_method = ( ! empty( $last_order_shipping_method[0] ) ) ? $last_order_shipping_method[0] : '';
						$last_order_shipping_method = explode( ':', $last_order_shipping_method );
						if ( ! empty( $last_order_shipping_method[1] ) ) {
							unset( $last_order_shipping_method[1] );
						}
						if ( empty( $last_order_shipping_method ) || ! is_array( $last_order_shipping_method ) ) {
							$last_order_shipping_method = array();
						}
						$old_shipping_method = implode( ' ', $last_order_shipping_method );
						$old_shipping_method = trim( $old_shipping_method );

						if ( ! empty( $old_shipping_method ) && ! empty( $available_methods ) && array_key_exists( $old_shipping_method, $available_methods ) ) {
							$_POST['shipping_method'] = $old_shipping_method; // phpcs:ignore
						}
					}

					$post_shipping_method = ( ! empty( $_POST['shipping_method'] ) ) ? sanitize_text_field( wp_unslash( $_POST['shipping_method'] ) ) : ''; // phpcs:ignore

					WC()->cart->calculate_shipping();

					if ( ! empty( $post_shipping_method ) ) {

						// Re-factoring needed.
						$packages                = WC()->shipping->get_packages();
						$shipping_rates          = ( ! empty( $packages[0]['rates'] ) ) ? $packages[0]['rates'] : array();
						$chosen_shipping_methods = array();
						$set_shipping_method     = false;

						if ( ! empty( $shipping_rates ) ) {
							foreach ( $shipping_rates as $method_id => $shipping_rate ) {
								$method = explode( ':', $method_id );
								if ( ! empty( $method ) && is_array( $method ) && in_array( $post_shipping_method, $method, true ) ) {
									$chosen_shipping_methods[] = $method_id;
									$set_shipping_method       = true;
								}
							}
						}
						if ( $set_shipping_method ) {
							if ( ! empty( $chosen_shipping_methods ) ) {
								WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
							} else {
								WC()->session->set( 'chosen_shipping_methods', $post_shipping_method );
							}
						}
					}
				}

				if ( wc_get_page_id( 'terms' ) > 0 ) {
					$_POST['terms'] = 'yes'; // phpcs:ignore
				}

				$_POST = apply_filters( 'wc_bn_order_post_data', $_POST, $last_order_id, $current_user ); // phpcs:ignore

				foreach ( $_POST as $post_key => $post_data ) { // phpcs:ignore
					$_POST[ $post_key ] = sanitize_text_field( wp_unslash( $post_data ) );
				}

				do_action( 'buy_now_post_fields', $last_order_id, $current_user );

				wc_clear_notices();

				if ( apply_filters( 'wc_bn_valid_for_process_checkout', true ) ) {

					$woocommerce_checkout->process_checkout();

				}
			}

			$quick_checkout     = get_option( 'wc_buy_now_is_quick_checkout' );
			$wc_buy_now_set_for = get_option( 'wc_buy_now_set_for' );

			if ( 'yes' === $quick_checkout && in_array( $wc_buy_now_set_for, array( 'buy-now', 'standard' ), true ) ) {
				$this->wc_buy_now_show_checkout_in_popup();
			} elseif ( 'yes' === $quick_checkout && 'and-buy-now' === $wc_buy_now_set_for ) {
				$redirect_url     = ( ! empty( $_SERVER['HTTP_REFERER'] ) ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : home_url(); // phpcs:ignore
				$new_redirect_url = $this->get_redirect_url_after_buy_now_process( $redirect_url );
				$new_redirect_url = add_query_arg( array( 'buy-now-action' => 'quick-checkout' ), $new_redirect_url );
				wp_safe_redirect( $new_redirect_url );
				exit;
			} else {
				$this->redirect_to_page( 'checkout' );
			}
		}

		/**
		 * Function to handle 2 click checkout process
		 *
		 * @param  integer $order_id Order ID.
		 * @param  array   $posted Posted data on checkout page.
		 */
		public function wc_buy_now_checkout_order_processed( $order_id = 0, $posted = null ) {

			if ( defined( 'DOING_AJAX' ) ) {
				return;
			}

			if ( empty( $order_id ) || empty( $posted ) ) {
				return;
			}

			if ( ( isset( $_POST['_wpnonce'] ) ) && ! wp_verify_nonce( wc_clean( wp_unslash( $_POST['_wpnonce'] ) ), 'buy-now-pay-action' ) ) { // phpcs:ignore
				return;
			}

			if ( ! empty( $_POST['buy_now_complete_payment_button'] ) ) { // phpcs:ignore
				return;
			}

			if ( WC()->cart->needs_payment() ) {

				$is_two_click = get_option( 'wc_buy_now_is_two_click', 'no' );

				if ( 'yes' === $is_two_click ) {
					$order           = wc_get_order( $order_id );
					$buy_now_pay_url = add_query_arg( 'buy-now-pay', $order_id, trailingslashit( home_url() ) );
					wp_safe_redirect( $buy_now_pay_url );
					exit;
				}
			}

		}

		/**
		 * Function to clear cart
		 */
		public function clear_cart_before_buy_now() {
			$current_user_id = get_current_user_id();
			$cart_data       = array(
				'cart'                       => WC()->session->get( 'cart' ),
				'applied_coupons'            => WC()->session->get( 'applied_coupons' ),
				'cart_totals'                => WC()->session->get( 'cart_totals' ),
				'coupon_discount_totals'     => WC()->session->get( 'coupon_discount_totals' ),
				'coupon_discount_tax_totals' => WC()->session->get( 'coupon_discount_tax_totals' ),
				'removed_cart_contents'      => WC()->session->get( 'removed_cart_contents' ),
			);
			if ( $current_user_id > 0 ) {
				if ( function_exists( 'update_user_attribute' ) ) {
					update_user_attribute(
						$current_user_id,
						'_buy_now_persistent_cart',
						$cart_data
					);
				} else {
					update_user_meta( // phpcs:ignore
						$current_user_id,
						'_buy_now_persistent_cart',
						$cart_data
					);
				}
			} else {
				WC()->session->set(
					'buy_now_persistent_cart',
					$cart_data
				);
			}
			WC()->cart->remove_coupons();
			$clear_persistent_cart = true;
			WC()->cart->empty_cart( $clear_persistent_cart );
		}

		/**
		 * Return last order of paricular customer email address
		 *
		 * @param  integer $customer_id Customer ID.
		 * @return integer Last Order ID.
		 */
		public function get_last_order_of_customer( $customer_id ) {

			global $wpdb;

			if ( empty( $customer_id ) ) {
				return false;
			}

			$customer_orders = wp_cache_get( 'sa_bn_customer_order_ids', 'woocommerce_buy_now' );

			if ( false === $customer_orders ) {
				$customer_orders = $wpdb->get_col( // phpcs:ignore
					$wpdb->prepare(
						"SELECT post_id
							FROM {$wpdb->prefix}postmeta
							WHERE meta_key = %s
							AND meta_value = %s",
						'_customer_user',
						$customer_id
					)
				);
				wp_cache_set( 'sa_bn_customer_order_ids', $customer_orders, 'woocommerce_buy_now' );
			}

			if ( empty( $customer_orders ) ) {
				return false;
			}

			if ( ! is_array( $customer_orders ) ) {
				$customer_orders = array( $customer_orders );
			}

			$customer_orders_list       = implode( ',', array_map( 'absint', $customer_orders ) );
			$option_customer_order_list = 'sa_bn_customer_order_list_' . $customer_id;

			update_option( $option_customer_order_list, $customer_orders_list, 'no' );

			$last_order_id = $wpdb->get_var( // phpcs:ignore
				$wpdb->prepare( // phpcs:ignore
					"SELECT posts.ID
						FROM {$wpdb->prefix}posts AS posts
							JOIN {$wpdb->prefix}postmeta  AS postmeta
							ON ( posts.ID = postmeta.post_id)
						WHERE FIND_IN_SET (posts.ID, (SELECT option_value
														FROM {$wpdb->prefix}options
														WHERE option_name = %s))
						AND posts.post_status IN ( %s, %s )
						AND posts.post_type = %s
						AND postmeta.meta_key = %s
						GROUP BY posts.ID
						ORDER BY posts.ID DESC
						LIMIT 1",
					$option_customer_order_list,
					'wc-completed',
					'wc-processing',
					'shop_order',
					'_payment_method'
				)
			);

			if ( empty( $last_order_id ) ) {
				return end( $customer_orders );
			}

			return $last_order_id;

		}


		/**
		 * Gets the details about the products whose ids has been sent.
		 *
		 * @return void
		 */
		public function get_product_details() {
			check_ajax_referer( 'cashier_buy_now_nounce', 'security' );
			if ( isset( $_POST['product_ids'] ) ) {
				$preserve_cart = get_option( 'wc_buy_now_is_preserve_cart', 'yes' );
				$with_cart     = ( 'yes' === $preserve_cart ) ? 0 : 1;

				$product_ids    = json_decode(
					sanitize_text_field(
						wp_unslash( $_POST['product_ids'] )
					),
					true
				);
				$product_prices = array();
				foreach ( $product_ids as $product_id ) {
					$product          = wc_get_product( $product_id );
					$product_prices[] = $product->get_price();
				}

				$data        = array(
					'data'           => __( 'Success.', 'subscription-updater' ),
					'with_cart'      => $with_cart,
					'product_prices' => $product_prices,
				);
				wp_send_json_success( $data, 200 );
				exit();
			} else {
				$data = array(
					'data' => __( 'Server did not receive the required data.', 'cashier' ),
				);
				wp_send_json_error( $data, 400 );
			}
			exit();
		}

		/**
		 * Function to return parent_id if parent_id is greater than 0 or product_id if parent_id is 0
		 *
		 * @param  integer $product_id Product ID.
		 * @return integer Processed product id.
		 */
		public function get_parent( $product_id ) {

			$parent_id = wp_get_post_parent_id( $product_id );
			if ( $parent_id > 0 ) {
				return $parent_id;
			}

			return $product_id;

		}

		/**
		 * Get template for Buy Now
		 *
		 * @param  string $template_name Template name.
		 * @param  array  $args Additional arguments.
		 * @param  string $template_path Current Template path.
		 * @param  string $default_path The default path.
		 */
		public function buy_now_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {

			$order = ( ! empty( $args['order'] ) && is_object( $args['order'] ) && $args['order'] instanceof WC_Order ) ? $args['order'] : null;

			if ( empty( $order ) ) {
				return;
			}

			$default_path = untrailingslashit( plugin_dir_path( SA_BN_PLUGIN_FILE ) ) . '/frontend/templates/';

			$plugin_base_dir = substr( plugin_basename( SA_BN_PLUGIN_FILE ), 0, strpos( plugin_basename( SA_BN_PLUGIN_FILE ), '/' ) + 1 );

			// Look within passed path within the theme - this is priority.
			$template = locate_template(
				array(
					'woocommerce/' . $plugin_base_dir . $template_name,
					$plugin_base_dir . $template_name,
					$template_name,
				)
			);

			// Get default template.
			if ( ! $template ) {
				$template = $default_path . $template_name;
			}

			do_action( 'woocommerce_before_template_part', $template_name, $template_path, $template, $args );

			include $template;

			do_action( 'woocommerce_after_template_part', $template_name, $template_path, $template, $args );
		}

		/**
		 * Function to fetch plugin's data
		 */
		public function get_bn_plugin_data() {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			return get_plugin_data( SA_CFW_PLUGIN_FILE );
		}

	} // End class
} // End class exists condition
