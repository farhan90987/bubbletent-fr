<?php

namespace MarketPress\GermanMarket\Shipping;

use WC_Order;
use WP_Post;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

class Backend {

	/**
	 * @acces public
	 *
	 * @var string
	 */
	public string $id;

	/**
	 * Class constructor.
	 *
	 * @acces protected
	 */
	protected function __construct() {}

	/**
	 * Adds a 'Print Label' callback to order actions menu.
	 *
	 * @Hook woocommerce_order_actions
	 *
	 * @param array $actions
	 *
	 * @return array
	 */
	public function add_order_actions( array $actions ) : array {
		global $theorder;

		if ( ! $theorder ) {
			return $actions;
		}

		if ( ! Helper::check_order_for_shipping_provider_methods( $theorder, $this->id ) ) {
			return $actions;
		}

		if ( $this->id === 'dhl' ) {
			return $actions;
		}

		$instance = Woocommerce_Shipping::get_instance()->get_provider_by_id( $this->id );
		if ( $instance ) {
			$actions[ $this->id . '_print_parcel_label' ] = sprintf( __( 'Print / Download %s Label', 'woocommerce-german-market' ), $instance->name );
		}

		return apply_filters( 'wgm_shipping_woocommerce_order_actions', $actions, $theorder );
	}

	/**
	 * adds a small download button to the admin page for orders
	 *
	 * @hook woocommerce_admin_order_actions
	 *
	 * @param array    $actions
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public function backend_icon_download( array $actions, WC_Order $order ) : array {

		if ( apply_filters( 'wgm_' . $this->id . '_shipping_backend_admin_icon_download_return', false, $order ) ) {
			return $actions;
		}

		if ( ! Helper::check_order_for_shipping_provider_methods( $order, $this->id ) ) {
			return $actions;
		}

		$has_shipping_label = ! empty( Woocommerce_Shipping::$order_meta->get_shipment_label( $order->get_id() ) );
		$instance           = Woocommerce_Shipping::get_instance()->get_provider_by_id( $this->id );

		if ( $instance ) {
			$create_pdf = array(
				'url'    => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_' . $this->id . '_ajax_shipping_label_download&order_id=' . $order->get_id() ), 'wp-wc-label-pdf-download' ),
				'name'   => sprintf( __( 'Download %s Shipping Label', 'woocommerce-german-market' ), $instance->name ),
				'action' => $this->id . '_shipping_label_' . ( $has_shipping_label ? 'download' : 'create' ) . ' ' . $this->id . '_order_' . $order->get_id(),
			);
			$actions[ $this->id . '_shipping_label' ] = $create_pdf;
		}

		return $actions;
	}

	/**
	 * Adds a 'Print Label' callback to the order bulk actions' menu.
	 *
	 * @Hook bulk_actions-edit-shop_order
	 *
	 * @param array $actions
	 *
	 * @return array
	 */
	public function define_orders_bulk_actions( array $actions ) : array {

		$provider = Woocommerce_Shipping::get_instance()->get_provider_by_id( $this->id );
		if ( $provider ) {
			$actions[ $provider::$options->build_option_key( 'print_parcel_label' ) ] = sprintf( __( 'Print / Download %s Label', 'woocommerce-german-market' ), $provider->name );
			if ( 'dhl' == $this->id ) {
				$actions[ $provider::$options->build_option_key( 'cancel_shipment' ) ] = sprintf( __( 'Cancel %s Shipments', 'woocommerce-german-market' ), $provider->name );
			}
		}

		return $actions;
	}

	/**
	 * Display Bulk Admin notices.
	 *
	 * @Wp-hook admin_notices
	 *
	 * @global $post_type
	 * @global $pagenow
	 *
	 * @return void
	 */
	public function bulk_admin_notices() {
		global $post_type, $pagenow;

		// Bail out if not on shop order list page.
		if ( 'edit.php' !== $pagenow || 'shop_order' !== $post_type || ! isset( $_REQUEST[ 'bulk_action' ] ) ) {
			return;
		}

		$number       = isset( $_REQUEST[ 'changed' ] ) ? intval( $_REQUEST[ 'changed' ] ) : 0;
		$bulk_action  = wc_clean( wp_unslash( $_REQUEST[ 'bulk_action' ] ) );
		$message      = '';
		$notice_class = '';

		if ( $this->id . '_printed_parcel_label' === $bulk_action ) {
			if ( $number == - 1 ) {
				$message      = __( 'ERROR: Cannot print labels for these orders, because some of orders parcel is not found.', 'woocommerce-german-market' );
				$notice_class = 'error';
			} else {
				$message      = sprintf( _n( 'Label printed for %d order.', 'Labels printed for %d orders.', $number, 'woocommerce-german-market' ), number_format_i18n( $number ) );
				$notice_class = 'success';
			}
		}

		if ( ! empty( $message ) ) {
			Helper::add_flash_notice( $message, $notice_class );
		}
	}

	/**
	 * Adds 'Tracking' column content to 'Orders' page immediately after 'Status' column.
	 *
	 * @hook manage_shop_order_posts_custom_column
	 *
	 * @param string     $column name of column being displayed
	 * @param int|object $post_id_or_order_object current post id or order object
	 *
	 * @return void
	 */
	public function add_order_tracking_column_content( string $column, $post_id_or_order_object ) {

		if ( ! ( is_object( $post_id_or_order_object ) && method_exists( $post_id_or_order_object, 'get_meta' ) ) ) {
			$order = wc_get_order( $post_id_or_order_object );
		} else {
			$order = $post_id_or_order_object;
		}

		if ( ! Helper::check_order_for_shipping_provider_methods( $order, $this->id ) ) {
			return;
		}

		if ( $column === 'order_tracking' ) {

			$provider         = Woocommerce_Shipping::get_instance()->get_provider_by_id( $this->id );
			$icon             = plugin_dir_url( __FILE__ ) . 'provider/' . $this->id . '/assets/images/icon.png';
			$tracking_numbers = Woocommerce_Shipping::$order_meta->get_shipment_numbers( $order->get_id() );

			if ( ! empty( $tracking_numbers ) ) {
				foreach( $tracking_numbers as $tracking_number ) {
					$tracking_url = $provider::$tracking_link;
					$tracking_url = str_replace( '{tracking_number}', $tracking_number, $tracking_url );
					echo "<a href='$tracking_url' target='_blank' style='font-weight: 600; background: url($icon) left center no-repeat; background-size: auto 20px; padding-left: 52px;' class='icon icon-" . $this->id . "'>" . $tracking_number . "</a><br>";
				}
			}
		}
	}

	/**
	 * Add Tracking Link to Costumer Email.
	 *
	 * @Hook woocommerce_email_order_meta
	 *
	 * @param WC_Order $order
	 * @param mixed    $sent_to_admin
	 * @param string   $plain_text
	 * @param string   $email
	 *
	 * @return void
	 */
	public function add_order_tracking_link_email( WC_Order $order, bool $sent_to_admin, $plain_text, $email = null ) {

		$order_id          = $order->get_id();
		$tracking_numbers  = Woocommerce_Shipping::$order_meta->get_shipment_numbers( $order_id );
		$provider          = Woocommerce_Shipping::get_instance()->get_provider_by_id( $this->id );
		$order_status      = $order->get_status();
		$test_mode         = $provider::$options->get_option( 'test_mode', 'off' );
		$generator_enabled = $provider::$options->get_option( 'label_auto_creation', 'off' );
		$generator_status  = $provider::$options->get_option( 'label_auto_creation_status', 'wc-processing' );
		$statuses          = array();

		if ( true === $sent_to_admin ) {
			return;
		}

		if ( false === apply_filters( 'wgm_shipping_add_order_tracking_link_email', true, $order, $sent_to_admin, $plain_text, $email, $this->id ) ) {
			return;
		}

		if ( empty( $tracking_numbers ) ) {
			if ( is_array( $generator_status ) ) {
				foreach ( $generator_status as $status ) {
					$statuses[] = substr( $status, 3 ); // cutting 'wc-'
				}
			} else {
				$statuses[] = substr( $generator_status, 3 );  // cutting 'wc-'
			}

			// Check shipping methods
			if ( Helper::check_order_for_shipping_provider_methods( $order, $this->id ) ) {
				// check if auto-creation is enabled && check given order status
				if ( ( 'on' === $generator_enabled ) && ( in_array( $order_status, $statuses ) ) ) {
					$result           = $provider::$labels->order_shipment_creation( array( $order ) );
					$tracking_numbers = Woocommerce_Shipping::$order_meta->get_shipment_numbers( $order_id );
				}
			}
		}

		if ( ! empty( $tracking_numbers ) ) {
			$email_text = $provider::$options->get_option( 'parcel_tracking_email_text', '{' . $this->id . '_tracking_link}' );
			$email_link = '';
			if ( 'off' === $test_mode ) {
				foreach ( $tracking_numbers as $tracking_number ) {
					$tracking_url = $provider::$tracking_link;
					$tracking_url = str_replace( '{tracking_number}', $tracking_number, $tracking_url );
					$email_link = '<a href="' . $tracking_url . '" target="_blank">' . $tracking_number . '</a><br>';
				}
			} else {
				$email_link = '{DEMO_LINK_SANDBOX_MODE}';
			}

			echo "<p>" . str_replace( '{' . $this->id . '_tracking_link}', $email_link, $email_text ) . "</p>";
		}
	}

	/**
	 * Saves a note with the tracking number into the order
	 *
	 * @param int      $order_id
	 * @param string   $barcode
	 * @param WC_Order $order
	 *
	 * @return void
	 */
	public function add_order_note_with_tracking_number( int $order_id, string $barcode, WC_Order $order ) {

		if ( $order_id && $barcode ) {
			$provider = Woocommerce_Shipping::get_instance()->get_provider_by_id( $this->id );
			$message  = sprintf( __( '%s Tracking number: %s', 'woocommerce-german-market' ), $provider->name, $barcode );
			$order->add_order_note( $message, false, false );
		}
	}

	/**
	 * Register the JavaScript for the backend-facing side of the site.
	 *
	 * @Wp-hook admin_enqueue_scripts
	 *
	 * @return void
	 */
	public function global_enqueue_scripts() {

		$dependencies = array( 'jquery' );

		wp_enqueue_script( 'jquery-repeater',      WGM_SHIPPING_URL . '/assets/js/repeater' . WGM_SHIPPING_MINIFY . '.js', $dependencies, WGM_SHIPPING_VERSION, true );
		wp_enqueue_script( 'wgm-shipping-backend', WGM_SHIPPING_URL . '/assets/js/backend' . WGM_SHIPPING_MINIFY . '.js', $dependencies, WGM_SHIPPING_VERSION, true );
		wp_enqueue_script( 'jquery-blockui',       WGM_SHIPPING_URL . '/assets/js/jquery.blockUI' . WGM_SHIPPING_MINIFY . '.js', $dependencies, WGM_SHIPPING_VERSION, true );
	}

	/**
	 * Save the product keepflat checkbox.
	 *
	 * @Hook woocommerce_process_product_meta
	 *
	 * @static
	 *
	 * @param int     $id
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	public static function save_keepflat_product_meta( int $id, WP_Post $post ) {

		if ( isset( $_POST[ '_wgm_shipping_keepflat' ] ) ) {
			update_post_meta( $id, '_wgm_shipping_keepflat', $_POST[ '_wgm_shipping_keepflat' ] );
		} else {
			update_post_meta( $id, '_wgm_shipping_keepflat', 'no' );
		}
	}

	/**
	 * Add a checkbox to the product shipping tab.
	 *
	 * @Hook woocommerce_product_options_shipping_product_data
	 *
	 * @static
	 *
	 * @return void
	 */
	public static function add_keepflat_checkbox_to_product_data() {

		echo '<div class="options_group">';
		woocommerce_wp_checkbox( array(
			'id'          => '_wgm_shipping_keepflat',
			'value'       => get_post_meta( get_the_ID(), '_wgm_shipping_keepflat', true ),
			'label'       => __( 'Box Packaging', 'woocommerce-german-market' ),
			'description' => __( 'Product has to keep flat in package', 'woocommerce-german-market' ),
		) );
		echo '</div>';
	}

}
