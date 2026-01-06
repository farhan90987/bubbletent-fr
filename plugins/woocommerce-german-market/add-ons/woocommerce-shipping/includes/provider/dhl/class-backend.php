<?php

namespace MarketPress\GermanMarket\Shipping\Provider\DHL;

use MarketPress\GermanMarket\Shipping\Backend as Shipping_Backend;
use MarketPress\GermanMarket\Shipping\Helper;
use MarketPress\GermanMarket\Shipping\Order_Meta;
use MarketPress\GermanMarket\Shipping\Package;
use MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Home_Delivery;
use MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Packstation;
use MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Parcels;
use MarketPress\GermanMarket\Shipping\Woocommerce_Shipping;
use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
use WC_AJAX;
use WC_Data_Exception;
use WC_Order;
use WP_Post;
use Exception;
use SoapFault;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

class Backend extends Shipping_Backend {

	/**
	 * Class constructor.
	 *
	 * @param string $id
	 */
	public function __construct( string $id ) {

		parent::__construct();

		$this->id = $id;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @Wp-hook admin_enqueue_scripts
	 *
	 * @return void
	 *
	 */
	public function enqueue_styles() {

		wp_enqueue_style( Shipping_Provider::get_instance()->handle, plugin_dir_url( __FILE__ ) . 'assets/css/backend' . WGM_SHIPPING_MINIFY . '.css', array( 'wp-components', 'wc-components' ), WGM_SHIPPING_VERSION, 'all' );
		wp_enqueue_style( 'thickbox' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @Wp-hook admin_enqueue_scripts
	 *
	 * @return void
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'thickbox' );
		wp_enqueue_script( Shipping_Provider::get_instance()->handle, plugin_dir_url( __FILE__ ) . 'assets/js/backend' . WGM_SHIPPING_MINIFY . '.js', array( 'jquery', 'jquery-ui-datepicker' ), WGM_SHIPPING_VERSION, true );

		wp_localize_script( Shipping_Provider::get_instance()->handle, Shipping_Provider::get_instance()->handle, array(
			'internetmarke_remove_from_cart' => __( 'Remove from cart', 'woocommerce-german-market' ),
			'ajax_internetmarke_url'         => admin_url( 'admin-ajax.php' ),
			'ajax_internetmarke_nonce'       => wp_create_nonce( 'internetmarke_checkout_nonce' ),
			'ajax_nonce'                     => wp_create_nonce( 'admin_order_create_label_nonce' ),
			'theme_url'                      => plugin_dir_url( __FILE__ ),
			'tracking_url'                   => Shipping_Provider::get_instance()::$tracking_link,
			'error_creating_label_heading'   => __( 'Error creating label', 'woocommerce-german-market' ),
		) );

		$shop_order_page = 'shop_order';

		// Check if German Market HPOS Helper class exists already.
		if ( Woocommerce_Shipping::$hpos_active ) {
			$shop_order_page = 'woocommerce_page_wc-orders';
		}

		if ( $shop_order_page == get_current_screen()->id ) {

			$provider           = Shipping_Provider::get_instance();
			$google_map_enabled = $provider::$options->get_option( 'google_map_enabled', 'off' );
			$google_map_key     = $provider::$options->get_option( 'google_map_key' );
			$dependencies       = array( 'jquery' );

			if ( ( 'on' === $google_map_enabled ) && ( '' !== $google_map_key ) ) {
				wp_enqueue_script( 'gmaps-markerclusterer', WGM_SHIPPING_URL . '/assets/js/gmaps-markerclusterer' . WGM_SHIPPING_MINIFY . '.js', $dependencies, WGM_SHIPPING_VERSION, true );
				wp_enqueue_script( 'google-maps-api', 'https://maps.googleapis.com/maps/api/js?key=' . $google_map_key, $dependencies, WGM_SHIPPING_VERSION, true );
				wp_localize_script( 'gmaps-markerclusterer', $this->id, array(
					'ajax_url'        => WC()->ajax_url(),
					'wc_ajax_url'     => WC_AJAX::get_endpoint( '%%endpoint%%' ),
					'ajax_nonce'      => wp_create_nonce( 'save-terminal' ),
					'theme_uri'       => plugin_dir_url( __FILE__ ) . 'assets/images/',
					'gmap_api_key'    => $google_map_key,
					'info_box_string' => __( 'WooCommerce has changed your selected payment method. Please check the selected payment method.', 'woocommerce-german-market' ),
				) );
				wp_enqueue_script( $this->id . 'parcel', plugin_dir_url( __FILE__ ) . 'assets/js/parcel' . WGM_SHIPPING_MINIFY . '.js', $dependencies, WGM_SHIPPING_VERSION, true );
			} else {
				wp_enqueue_script( 'parcel-select', WGM_SHIPPING_URL . '/assets/js/parcel-select' . WGM_SHIPPING_MINIFY . '.js', $dependencies, WGM_SHIPPING_VERSION, true );
				wp_localize_script( 'parcel-select', 'wgm_woocommerce_shipping', array(
					'ajax_url'        => WC()->ajax_url(),
					'wc_ajax_url'     => WC_AJAX::get_endpoint( '%%endpoint%%' ),
					'ajax_nonce'      => wp_create_nonce( 'save-terminal' ),
				) );
			}
		}
	}

	/**
	 * Add a checkbox to the product shipping tab.
	 *
	 * @Hook woocommerce_product_options_shipping_product_data
	 *
	 * @return void
	 */
	public function add_export_information_fields_to_product_data() {

		echo '<div class="options_group">';
		woocommerce_wp_text_input( array(
			'id'          => '_wgm_shipping_dhl_hscode',
			'value'       => get_post_meta( get_the_ID(), '_wgm_shipping_dhl_hscode', true ),
			'label'       => __( 'HS Code', 'woocommerce-german-market' ),
			'description' => __( 'This information is used for international exports in non european countries and printed on export documents when generating shipping labels. This field means the "Harmonized System Code" aka Customs tariff number of the product.', 'woocommerce-german-market' ),
			'desc_tip'    => true,
			'style'       => 'width: 350px;',
		) );
		echo '</div>';

		$countries = array_merge( array( 0 => __( '--- Unknown ---', 'woocommerce-german-market' ) ), WC()->countries->get_countries() );

		echo '<div class="options_group">';
		woocommerce_wp_select( array(
			'id'      => '_wgm_shipping_dhl_country_origin',
			'value'   => get_post_meta( get_the_ID(), '_wgm_shipping_dhl_country_origin', true ),
			'label'   => __( 'Country of origin', 'woocommerce-german-market' ),
			'class'   => 'wc-enhanced-select',
			'style'   => 'width: 350px;',
			'options' => $countries,
			'default' => 0,
		) );
		echo '</div>';
	}

	/**
	 * Save the product export information.
	 *
	 * @Hook woocommerce_process_product_meta
	 *
	 * @param int     $id
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	public function save_export_information_product_meta( int $id, WP_Post $post ) {

		if ( isset( $_POST[ '_wgm_shipping_dhl_hscode' ] ) ) {
			update_post_meta( $id, '_wgm_shipping_dhl_hscode', sanitize_text_field($_POST[ '_wgm_shipping_dhl_hscode' ] ) );
		} else {
			update_post_meta( $id, '_wgm_shipping_dhl_hscode', '' );
		}

		if ( isset( $_POST[ '_wgm_shipping_dhl_country_origin' ] ) && ( '0' != $_POST[ '_wgm_shipping_dhl_country_origin' ] ) ) {
			update_post_meta( $id, '_wgm_shipping_dhl_country_origin', sanitize_text_field($_POST[ '_wgm_shipping_dhl_country_origin' ] ) );
		} else {
			update_post_meta( $id, '_wgm_shipping_dhl_country_origin', '' );
		}
	}

	/**
	 * Add additional order actions.
	 *
	 * @Hook wgm_shipping_woocommerce_order_actions
	 *
	 * @param array  $actions array with order actions
	 * @param object $theorder current post or order object
	 *
	 * @return array
	 */
	public function add_additional_order_actions( $actions, $theorder ) : array {

		if ( Woocommerce_Shipping::$order_meta->has_shipping_label( $theorder->get_id() ) ) {
			$actions[ $this->id . '_cancel_shipment' ] = sprintf( __( 'Cancel %s label', 'woocommerce-german-market' ), Shipping_Provider::get_instance()->name );
		}

		return $actions;
	}

	/**
	 * Returns terminal information by given terminal id.
	 *
	 * @param mixed  $terminal_id
	 * @param string $type
	 *
	 * @return array
	 */
	public function get_terminal_by_id( $terminal_id, string $type = 'packstations' ) : array {

		$terminals = Shipping_Provider::$api->get_session_cache( $type );

		if ( ! empty( $terminals ) ) {
			$terminals = json_decode( $terminals[ 'terminals' ], true );
			foreach ( $terminals as $terminal ) {
				if ( $terminal_id == $terminal[ 'parcelshop_id' ] ) {
					return $terminal;
				}
			}
		}

		return array();
	}

	/**
	 * Adding a meta box to the order detail page.
	 *
	 * @Hook add_meta_boxes
	 *
	 * @global $post
	 * @global $woocommerce
	 *
	 * @return void
	 */
	public function add_shipping_services_meta_box() {
		global $post, $woocommerce, $theorder;

		$dhl_service_meta_box        = 'wgm_dhl_package_services';
		$dhl_services_meta_box_title = __( 'DHL - German Market', 'woocommerce-german-market' );

		if ( ! Woocommerce_Shipping::$hpos_active ) {

			$order = wc_get_order( $post->ID );

			if ( ! $order || ! method_exists( $order, 'get_status' ) ) {
				return;
			}

			if ( Helper::check_order_for_shipping_provider_methods( $order, $this->id ) ) {

				add_meta_box(
					$dhl_service_meta_box,
					$dhl_services_meta_box_title,
					array( $this, 'add_services_fields_for_packaging' ),
					'shop_order',
					'side',
					'core'
				);

				$user  = wp_get_current_user();
				$order = get_user_option( 'meta-box-order_shop_order', $user->ID );

				if ( strpos( $order[ 'side' ], $dhl_service_meta_box ) === false ) {
					$order[ 'side' ] = $dhl_service_meta_box . ',' . $order[ 'side' ];
					update_user_option( $user->ID, "meta-box-order_shop_order", $order, true );
				}
			}

		} else {

			if ( ! is_object( $theorder ) ) {
				return;
			}

			// Do not add a meta box if it's not a DHL shipment.
			if ( ! Helper::check_order_for_shipping_provider_methods( $theorder, $this->id ) ) {
				return;
			}

			$screen = wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
				? wc_get_page_screen_id( 'shop-order' )
				: 'shop_order';

			add_meta_box(
				$dhl_service_meta_box,
				$dhl_services_meta_box_title,
				array( $this, 'add_services_fields_for_packaging' ),
				$screen,
				'side',
				'core'
			);

		}
	}

	/**
	 * Adding the meta box content within the order detail page.
	 *
	 * @return void
	 * @throws SoapFault
	 * @throws Exception
	 */
	public function add_services_fields_for_packaging( $post_or_order_object ) {
		global $post, $woocommerce;

		$provider = Woocommerce_Shipping::get_instance()->get_provider_by_id( $this->id );
		$order    = ( $post_or_order_object instanceof WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;
		$order_id = $order->get_id();

		$has_shipping_label = Order_Meta::get_instance()->has_shipping_label( $order_id );

		$dhl_product_selected = $order->get_meta( '_wgm_dhl_paket_product' ) ? $order->get_meta( '_wgm_dhl_paket_product' ) : '';

		$shipping_country = $order->get_shipping_country();
		$shop_country     = $provider::$options->get_option( 'shipping_shop_address_country', 'DE' );
		$is_international = $shipping_country != $shop_country;

		$dhl_product_default_national      = $provider::$options->get_option( 'paket_default_product_national', WGM_SHIPPING_PRODUKT_DHL_PAKET );
		$dhl_product_default_international = $provider::$options->get_option( 'paket_default_product_international', WGM_SHIPPING_PRODUKT_DHL_PAKET_INTERNATIONAL );

		if ( $dhl_product_selected == '' ) {
			$dhl_product_selected = $is_international ? $dhl_product_default_international : $dhl_product_default_national;
		}

		/*
		 * Nonce
		 */
		?>
		<input type="hidden" name="add_services_fields_nonce" value="<?php echo wp_create_nonce(); ?>">
		<?php

		/*
		 * Product / Service
		 */
		?>
		<div class="wgm-meta-content-wrapper">
			<div class="inner border-bottom select">
				<label for="_wgm_dhl_paket_product" style="display: block; margin-bottom: 5px;"><?php echo __( 'Service selected', 'woocommerce-german-market' ) ?></label>
				<select id="_wgm_dhl_paket_product" name="_wgm_dhl_paket_product" class="wp-advanced-select" <?php echo ( $has_shipping_label ? 'disabled' : '' ) ?> >
					<?php
					if ( $is_international ) {
						?>
						<option
								data-service-ddp="off"
								data-service-shippingconditions="on"
								data-service-endorsement="off"
								value="<?php echo WGM_SHIPPING_PRODUKT_DHL_EURO_PAKET_B2B ?>"
							<?php echo ( $dhl_product_selected == WGM_SHIPPING_PRODUKT_DHL_EURO_PAKET_B2B ? "selected" : "" ) ?>
						><?php echo __( 'DHL Euro Parcel (B2B)', 'woocommerce-german-market' ) ?></option>
						<option
								data-service-ddp="on"
								data-service-shippingconditions="on"
								data-service-endorsement="on"
								value="<?php echo WGM_SHIPPING_PRODUKT_DHL_PAKET_INTERNATIONAL ?>"
							<?php echo ( $dhl_product_selected == WGM_SHIPPING_PRODUKT_DHL_PAKET_INTERNATIONAL ? "selected" : "" ) ?>
						><?php echo __( 'DHL Parcel International', 'woocommerce-german-market' ) ?></option>
						<option
								data-service-ddp="off"
								data-service-shippingconditions="on"
								data-service-endorsement="off"
								value="<?php echo WGM_SHIPPING_PRODUKT_DHL_WARENPOST_INTERNATIONAL ?>"
							<?php echo ( $dhl_product_selected == WGM_SHIPPING_PRODUKT_DHL_WARENPOST_INTERNATIONAL ? 'selected' : '' ) ?>
						><?php echo __( 'DHL Post International', 'woocommerce-german-market' ) ?></option>
						<?php

					} else {

						?>
						<option
								value="<?php echo WGM_SHIPPING_PRODUKT_DHL_PAKET ?>"
							<?php echo ( $dhl_product_selected == WGM_SHIPPING_PRODUKT_DHL_PAKET ? "selected" : "" ) ?>
						><?php echo __( 'DHL Parcel', 'woocommerce-german-market' ) ?></option>
						<option
								value="<?php echo WGM_SHIPPING_PRODUKT_DHL_KLEINPAKET ?>"
							<?php echo ( $dhl_product_selected == WGM_SHIPPING_PRODUKT_DHL_KLEINPAKET ? "selected" : "" ) ?>
						><?php echo __( 'DHL Kleinpaket', 'woocommerce-german-market' ) ?></option>
						<?php
					}
					?>
				</select>
			</div>
		</div>
		<?php

		/*
		 * Weight
		 */
		$items                 = $order->get_items();
		$shop_weight_unit      = get_option( 'woocommerce_weight_unit', 'kg' );
		$product_weight        = 0;
		$default_parcel_weight = str_replace( ',', '.', Shipping_Provider::$options->get_option( 'default_parcel_weight', 2 ) );
		$minimum_parcel_weight = str_replace( ',', '.', Shipping_Provider::$options->get_option( 'minimum_parcel_weight', 0.5 ) );

		foreach ( $items as $product ) {
			$product_data = null;
			if ( method_exists( $product, 'get_product' ) ) {
				$product_data = $product->get_product();
			}
			$product_data = apply_filters( 'wgm_shipping_dhl_item_product_data', $product_data, $product, $order );
			if ( is_object( $product_data ) && method_exists( $product_data, 'get_weight') ) {
				$product_weight += $product_data->get_weight() > 0 ? wc_get_weight( $product_data->get_weight(), 'kg', $shop_weight_unit ) * $product->get_quantity() : 0;
			} else {
				$product_weight += apply_filters( 'wgm_shipping_dhl_item_product_weight', 0, $product, $order );
			}
		}

		if ( $product_weight <= 0 ) {
			// Use default parcel weight if products weight cannot be calculated.
			$product_weight = $default_parcel_weight;
		}

		$parcel_distribution      = $provider::$options->get_option( 'parcel_distribution', 1 );
		$input_disabled_attribute = ( 1 != $parcel_distribution ) ? 'disabled' : '';
		$settings_url             = admin_url( 'admin.php?page=german-market&tab=wgm-shipping-dhl&sub_tab=parcels' );

		// If parcel has already a stored weight.
		if ( ( 1 == $parcel_distribution ) && ! empty( $order->get_meta( '_wgm_dhl_paket_weight' ) ) ) {
			$product_weight = $order->get_meta( '_wgm_dhl_paket_weight' );
		}

		$parcel_weight = $product_weight;

		// Check if parcel weight is lower than our minimum weight in settings.
		if ( $parcel_weight < $minimum_parcel_weight ) {
			$parcel_weight = $minimum_parcel_weight;
		}

		$additional_parcel_weight = $provider::$options->get_option( 'default_additional_parcel_weight', 0 );
		Package::$additional_parcel_weight = str_replace( ',', '.', $additional_parcel_weight );
		$total_weight = round( Package::maybe_add_additional_parcel_weight( floatval( $parcel_weight ) ), 3 );

		?>
		<div class="wgm-meta-content-wrapper">
			<div class="inner border-bottom select">
				<label for="_wgm_dhl_paket_weight" style="display: block; margin-bottom: 5px;"><?php echo __( 'Pre-calculated product weight (kg)', 'woocommerce-german-market' ); ?></label>
				<input
						type="number"
						id="_wgm_dhl_paket_weight"
						name="_wgm_dhl_paket_weight"
						value="<?php echo $product_weight ?>"
						min="0"
						step="0.001"
						style="padding-right: 0;" <?php echo ( $has_shipping_label ? 'disabled' : '' ) ?>
					<?php echo $input_disabled_attribute; ?>
				/>
				<label style="display: block; margin-top: 5px; margin-bottom: 5px;">
					<?php if ( 1 == $parcel_distribution ) : ?>
						<?php echo sprintf( __( 'Total weight of the parcel based on ordered products and <a href="%s" target="blank">Settings</a>.', 'woocommerce-german-market' ), $settings_url ); ?>: <div><span id="parcel_total_weight" style="font-weight: bold"><?php echo $total_weight; ?></span> <span style="font-weight: bold">kg</span></div>
					<?php else : ?>
						<?php echo sprintf( __( 'The individual input is only available if the option "Group all products in one delivery" is active under "<a href="%s" target="blank">Parcel Configuration</a> > <a href="%s" target="blank">Parcel Distribution</a>".', 'woocommerce-german-market' ), $settings_url, $settings_url . '#wgm_dhl_parcel_distribution' ); ?>
					<?php endif; ?>
				</label>
			</div>
		</div>
		<?php

		/*
		 * Preferred Day
		 */
		if ( ! $is_international ) {

			// If parcel has already a stored preferred delivery day.

			if ( ! empty( $order->get_meta( '_wgm_dhl_service_preferred_day' ) ) ) {
				$preferred_day = $order->get_meta( '_wgm_dhl_service_preferred_day' );
			} else {
				$preferred_day = '';
			}

			$preferred_first_day = Shipping_Provider::calculate_first_preferred_delivery_day();

			?>
			<div class="wgm-meta-content-wrapper">
				<div class="inner border-bottom">
					<label for="_wgm_dhl_service_preferred_day" style="display: block; margin-bottom: 5px;"><?php echo __( 'Preferred Day', 'woocommerce-german-market' ) ?></label>
					<input
							type="text"
							name="_wgm_dhl_service_preferred_day"
							id="_wgm_dhl_service_preferred_day"
							class="datepicker"
							value="<?php echo $preferred_day; ?>"
							placeholder="<?php echo __( 'Choose Date', 'woocommerce-german-market' ); ?>"
						<?php echo ( $has_shipping_label ? 'disabled' : '' ) ?>
					>
				</div>
				<script>
					jQuery(function( $ ) {
						$( "#_wgm_dhl_service_preferred_day.datepicker").datepicker( {
							beforeShowDay: function(date) {
								const day = date.getDay();
								return [(day !== 0)];
							},
							dateFormat : 'yy-mm-dd',
							minDate: <?php echo $preferred_first_day; ?>,
							maxDate: <?php echo $preferred_first_day + 6; ?>,
						} );
					} );
				</script>
			</div>
			<?php
		}

		/*
		 * Return Label
		 */
		$enabled = $order->get_meta( '_wgm_dhl_service_return_label' ) ? $order->get_meta( '_wgm_dhl_service_return_label' ) : get_option( 'wgm_dhl_label_retoure_enabled', 'off' );
		$ticked  = ( 'on' === $enabled ) ? ' checked="checked" ' : '';

		?>
		<div class="wgm-meta-content-wrapper">
			<div class="inner border-bottom">
				<input type="checkbox" id="_wgm_dhl_service_return_label" name="_wgm_dhl_service_return_label" <?php echo $ticked ?> style="margin-right: 10px;" <?php echo ( $has_shipping_label ? 'disabled' : '' ) ?> />
				<label for="_wgm_dhl_service_return_label"><?php echo __( 'Return Shipping Label', 'woocommerce-german-market' ) ?></label>
			</div>
		</div>
		<?php

		/*
		 * Transport Insurance
		 */
		$enabled = $order->get_meta( '_wgm_dhl_service_transport_insurance' ) ? $order->get_meta( '_wgm_dhl_service_transport_insurance' ) : get_option( 'wgm_dhl_service_transport_insurance_default', 'off' );
		$ticked  = ( 'on' === $enabled ) ? ' checked="checked" ' : '';

		?>
		<div class="wgm-meta-content-wrapper">
			<div class="inner border-bottom">
				<input type="checkbox" id="_wgm_dhl_service_transport_insurance" name="_wgm_dhl_service_transport_insurance" <?php echo $ticked ?> style="margin-right: 10px;" <?php echo ( $has_shipping_label ? 'disabled' : '' ) ?> />
				<label for="_wgm_dhl_service_transport_insurance"><?php echo __( 'Transport Insurance', 'woocommerce-german-market' ) ?></label>
			</div>
		</div>
		<?php

		/*
		 * No Neighbor Delivery
		 */
		if ( ! $is_international ) {

			$enabled = $order->get_meta( '_wgm_dhl_service_no_neighbor_delivery' ) ? $order->get_meta( '_wgm_dhl_service_no_neighbor_delivery' ) : get_option( 'wgm_dhl_service_no_neighbor_delivery_default', 'off' );
			$ticked  = ( 'on' === $enabled ) ? ' checked="checked" ' : '';

			?>
			<div class="wgm-meta-content-wrapper">
				<div class="inner border-bottom">
					<input type="checkbox" id="_wgm_dhl_service_no_neighbor_delivery" name="_wgm_dhl_service_no_neighbor_delivery" <?php echo $ticked ?> style="margin-right: 10px;" <?php echo ( $has_shipping_label ? 'disabled' : '' ) ?> />
					<label for="_wgm_dhl_service_no_neighbor_delivery"><?php echo __( 'No Neighbor Delivery', 'woocommerce-german-market' ) ?></label>
				</div>
			</div>
			<?php
		}

		/*
		 * Named Person Only
		 */
		if ( ! $is_international ) {

			$enabled = $order->get_meta( '_wgm_dhl_service_named_person' ) ? $order->get_meta( '_wgm_dhl_service_named_person' ) : get_option( 'wgm_dhl_service_named_person_default', 'off' );
			$ticked  = ( 'on' === $enabled ) ? ' checked="checked" ' : '';

			?>
			<div class="wgm-meta-content-wrapper">
				<div class="inner border-bottom">
					<input type="checkbox" id="_wgm_dhl_service_named_person" name="_wgm_dhl_service_named_person" <?php echo $ticked ?> style="margin-right: 10px;" <?php echo ( $has_shipping_label ? 'disabled' : '' ) ?> />
					<label for="_wgm_dhl_service_named_person"><?php echo __( 'Named Person Only', 'woocommerce-german-market' ) ?></label>
				</div>
			</div>
			<?php
		}

		/*
		 * Premium
		 */
		$enabled = $order->get_meta( '_wgm_dhl_service_premium' ) ? $order->get_meta( '_wgm_dhl_service_premium' ) : get_option( 'wgm_dhl_service_premium_default', 'off' );
		$ticked  = ( 'on' === $enabled ) ? ' checked="checked" ' : '';

		?>
		<div class="wgm-meta-content-wrapper">
			<div class="inner border-bottom">
				<input type="checkbox" id="_wgm_dhl_service_premium" name="_wgm_dhl_service_premium" <?php echo $ticked ?> style="margin-right: 10px;" <?php echo ( $has_shipping_label ? 'disabled' : '' ) ?> />
				<label for="_wgm_dhl_service_premium"><?php echo __( 'Premium', 'woocommerce-german-market' ) ?></label>
			</div>
		</div>
		<?php

		/*
		 * Bulky Goods
		 */
		$enabled = $order->get_meta( '_wgm_dhl_service_bulky_goods' ) ? $order->get_meta( '_wgm_dhl_service_bulky_goods' ) : get_option( 'wgm_dhl_service_bulky_goods_default', 'off' );
		$ticked  = ( 'on' === $enabled ) ? ' checked="checked" ' : '';

		?>
		<div class="wgm-meta-content-wrapper">
			<div class="inner border-bottom">
				<input type="checkbox" id="_wgm_dhl_service_bulky_goods" name="_wgm_dhl_service_bulky_goods" <?php echo $ticked ?> style="margin-right: 10px;" <?php echo ( $has_shipping_label ? 'disabled' : '' ) ?> />
				<label for="_wgm_dhl_service_bulky_goods" style=""><?php echo __( 'Bulky Goods', 'woocommerce-german-market' ) ?></label>
			</div>
		</div>
		<?php

		/*
		 * Only Codeable Address
		 */
		if ( ! $is_international ) {

			$enabled = $order->get_meta( '_wgm_dhl_service_codeable' ) ? $order->get_meta( '_wgm_dhl_service_codeable' ) : get_option( 'wgm_dhl_service_codeable_default', 'off' );
			$ticked  = ( 'on' === $enabled ) ? ' checked="checked" ' : '';

			?>
			<div class="wgm-meta-content-wrapper">
				<div class="inner border-bottom">
					<input type="checkbox" id="_wgm_dhl_service_codeable" name="_wgm_dhl_service_codeable" <?php echo $ticked ?> style="margin-right: 10px;" <?php echo ( $has_shipping_label ? 'disabled' : '' ) ?> />
					<label for="_wgm_dhl_service_codeable"><?php echo __( 'Only Codeable Addresses', 'woocommerce-german-market' ) ?></label>
				</div>
			</div>
			<?php
		}

		/*
		 * Parcel Outlet Routing
		 */
		if ( ! $is_international && $order->has_shipping_method( Home_Delivery::get_instance()->id ) ) {

			$enabled = $order->get_meta( '_wgm_dhl_service_outlet_routing' ) ? $order->get_meta( '_wgm_dhl_service_outlet_routing' ) : get_option( 'wgm_dhl_service_outlet_routing_default', 'off' );
			$ticked  = ( 'on' === $enabled ) ? ' checked="checked" ' : '';

			?>
			<div class="wgm-meta-content-wrapper">
				<div class="inner border-bottom">
					<input type="checkbox" id="_wgm_dhl_service_outlet_routing" name="_wgm_dhl_service_outlet_routing" <?php echo $ticked ?> style="margin-right: 10px;" <?php echo ( $has_shipping_label ? 'disabled' : '' ) ?> />
					<label for="_wgm_dhl_service_outlet_routing"><?php echo __( 'Parcel Outlet Routing', 'woocommerce-german-market' ) ?></label>
				</div>
			</div>
			<?php
		}

		/*
		 * Visual Age Check
		 */
		if ( ! $is_international && ! $order->has_shipping_method( Packstation::get_instance()->id ) ) {

			$value = $order->get_meta( '_wgm_dhl_service_age_visual' ) ? $order->get_meta( '_wgm_dhl_service_age_visual' ) : get_option( 'wgm_dhl_service_age_visual_default', '0' );

			?>
			<div class="wgm-meta-content-wrapper">
				<div class="inner border-bottom select">
					<label for="_wgm_dhl_service_age_visual" style="display: block; margin-bottom: 5px;"><?php echo __( 'Visual Age Check', 'woocommerce-german-market' ) ?></label>
					<select id="_wgm_dhl_service_age_visual" name="_wgm_dhl_service_age_visual" class="wp-advanced-select" <?php echo ( $has_shipping_label ? 'disabled' : '' ) ?>>
						<option
								value="0"
							<?php echo ( $value == '0' ? 'selected' : '' ) ?>
						><?php echo __( 'No Visual Age Check', 'woocommerce-german-market' ) ?></option>
						<option
								value="A16"
							<?php echo ( $value == 'A16' ? 'selected' : '' ) ?>
						><?php echo __( 'Minimum Age 16', 'woocommerce-german-market' ) ?></option>
						<option
								value="A18"
							<?php echo ( $value == 'A18' ? 'selected' : '' ) ?>
						><?php echo __( 'Minimum Age 18', 'woocommerce-german-market' ) ?></option>
					</select>
				</div>
			</div>
			<?php
		}

		/*
		 * Ident Check
		 */
		if ( ! $is_international && ! $order->has_shipping_method( Packstation::get_instance()->id ) ) {

			$value = $order->get_meta( '_wgm_dhl_service_ident_check' ) ? $order->get_meta( '_wgm_dhl_service_ident_check' ) : get_option( 'wgm_dhl_service_ident_check_default', '0' );
			$dob   = ! empty( $order->get_meta( 'billing_dob' ) ) ? $order->get_meta( 'billing_dob' ) : '';

			if ( $dob ) {
				$dateObj = strtotime( $dob );
				$dob     = date( 'd.m.Y', $dateObj );
			}

			?>
			<div class="wgm-meta-content-wrapper">
				<div class="inner border-bottom select">
					<label for="_wgm_dhl_service_ident_check" style="display: block; margin-bottom: 5px;"><?php echo __( 'Ident Check', 'woocommerce-german-market' ) ?></label>
					<select id="_wgm_dhl_service_ident_check" name="_wgm_dhl_service_ident_check" class="wp-advanced-select" <?php echo ( $has_shipping_label ? 'disabled' : '' ) ?>>
						<option
								value="0"
							<?php echo ( $value == '0' ? 'selected' : '' ) ?>
						><?php echo __( 'No Ident Check', 'woocommerce-german-market' ) ?></option>
						<option
								value="ALL"
							<?php echo ( $value == 'ALL' ? 'selected' : '' ) ?>
						><?php echo __( 'Complete Ident Check', 'woocommerce-german-market' ) ?></option>
						<option
								value="A16"
							<?php echo ( $value == 'A16' ? 'selected' : '' ) ?>
						><?php echo __( 'Minimum Age 16', 'woocommerce-german-market' ) ?></option>
						<option
								value="A18"
							<?php echo ( $value == 'A18' ? 'selected' : '' ) ?>
						><?php echo __( 'Minimum Age 18', 'woocommerce-german-market' ) ?></option>
					</select>
					<div class="billing-dob-wrapper" style="<?php echo ( $value == '0' ) ? 'display: none;' : ''; ?>">
						<label
								for="billing_dob"
								style="display: inline-block; margin-top: 1em;"
						><?php echo __( 'Date of Birth (d.m.Y)', 'woocommerce-german-market' ); ?></label>
						<input
								type="text"
								name="billing_dob"
								id="billing_dob"
								class=""
								value="<?php echo $dob; ?>"
								style="margin-top: .25em;"
						>
					</div>
				</div>
			</div>
			<?php
		}

		/*
		 * Terms of Trade
		 */
		if ( $is_international && ! Helper::is_eu_country( $order->get_shipping_country() ) ) {

			$value = $order->get_meta( '_wgm_dhl_service_terms_of_trade' ) ? $order->get_meta( '_wgm_dhl_service_terms_of_trade' ) : get_option( 'wgm_dhl_paket_default_terms_of_trade' );

			?>
			<div class="wgm-meta-content-wrapper">
				<div class="inner select">
					<label for="_wgm_dhl_service_terms_of_trade" style="display: block; margin-bottom: 5px;"><?php echo __( 'Shipping conditions', 'woocommerce-german-market' ) ?></label>
					<select id="_wgm_dhl_service_terms_of_trade" name="_wgm_dhl_service_terms_of_trade" class="wp-advanced-select" <?php echo ( $has_shipping_label ? 'disabled' : '' ) ?> >
						<option
								value="<?php echo WGM_SHIPPING_DELIVERY_DUTY_UNPAID ?>"
							<?php echo ( $value == WGM_SHIPPING_DELIVERY_DUTY_UNPAID ? 'selected' : '' ) ?>
						><?php echo __( 'Delivery Duty Unpaid', 'woocommerce-german-market' ) ?></option>
						<option
								value="<?php echo WGM_SHIPPING_DELIVERY_DUTY_PAID ?>"
							<?php echo ( $value == WGM_SHIPPING_DELIVERY_DUTY_PAID ? 'selected' : '' ) ?>
						><?php echo __( 'Delivery Duty Paid', 'woocommerce-german-market' ) ?></option>
						<option
								value="<?php echo WGM_SHIPPING_DELIVERY_DUTY_PAID_EXCL_VAT ?>"
							<?php echo ( $value == WGM_SHIPPING_DELIVERY_DUTY_PAID_EXCL_VAT ? 'selected' : '' ) ?>
						><?php echo sprintf( __( 'Delivered Duty Paid (%s)', 'woocommerce-german-market' ), __( 'excl. VAT', 'woocommerce-german-market' ) ) ?></option>
						<option
								value="<?php echo WGM_SHIPPING_DELIVERY_DUTY_PAID_EXCL_ALL ?>"
							<?php echo ( $value == WGM_SHIPPING_DELIVERY_DUTY_PAID_EXCL_ALL ? 'selected' : '' ) ?>
						><?php echo sprintf( __( 'Delivery Duty Paid (%s)', 'woocommerce-german-market' ), __( 'excl. customs duties, taxes and VAT', 'woocommerce-german-market' ) ) ?></option>
					</select>
				</div>
				<div class="inner border-bottom" style="padding-top: 0;">
					<input type="checkbox" id="_wgm_dhl_service_ddp" name="_wgm_dhl_service_ddp" <?php echo $ticked ?> style="margin-right: 10px;" disabled />
					<label for="_wgm_dhl_service_ddp"><?php echo __( 'Postal Duty Delivered Paid', 'woocommerce-german-market' ) ?></label>
				</div>
			</div>
			<?php
		}

		/*
		 * Endorsement Type
		 */
		if ( $is_international ) {

			$value = $order->get_meta( '_wgm_dhl_service_endorsement' ) ? $order->get_meta( '_wgm_dhl_service_endorsement' ) : get_option( 'wgm_dhl_service_endorsement_default' );

			if ( '0' === $value ) {
				$value = 'IMMEDIATE';
			}

			?>
			<div class="wgm-meta-content-wrapper">
				<div class="inner border-bottom select">
					<label for="_wgm_dhl_service_endorsement" style="display: block; margin-bottom: 5px;"><?php echo __( 'Endorsement Type', 'woocommerce-german-market' ) ?></label>
					<select id="_wgm_dhl_service_endorsement" name="_wgm_dhl_service_endorsement" class="wp-advanced-select" <?php echo ( $has_shipping_label ? 'disabled' : '' ) ?> >
						<option
								value="IMMEDIATE"
							<?php echo ( $value == 'IMMEDIATE' ? 'selected' : '' ) ?>
						><?php echo __( 'Sending back to sender', 'woocommerce-german-market' ) ?></option>
						<option
								value="ABANDONMENT"
							<?php echo ( $value == 'ABANDONMENT' ? 'selected' : '' ) ?>
						><?php echo __( 'Abandonment of parcel', 'woocommerce-german-market' ) ?></option>
					</select>
				</div>
			</div>
			<?php
		}

		/*
		 * Generate Shipping Label Button
		 */
		?>
		<div id="create_label_wrapper" class="wgm-meta-content-wrapper" style="<?php echo ( $has_shipping_label ? 'display: none;' : '' ) ?>">
			<div class="inner border-bottom">
				<input type="submit" id="_wgm_dhl_action_button" name="_wgm_dhl_action_button" value="<?php echo __( 'Create DHL shipping label', 'woocommerce-german-market' ) ?>" class="button button-primary button-save-form" />
			</div>
		</div>
		<?php

		/*
		 * Download Label Button if already created
		 */
		if ( ( 'auto-draft' != $order->get_status() ) && ! empty( Woocommerce_Shipping::$order_meta->has_shipping_label( $order_id ) ) ) {
			echo $this->get_label_download_button_markup( $order_id );
		}

		/*
		 * Download Button for Export Documents if exists
		 */
		if ( ( 'auto-draft' != $order->get_status() ) && ! empty( Woocommerce_Shipping::$order_meta->has_export_documents( $order_id ) ) ) {
			echo $this->get_export_documents_download_button_markup( $order_id );
		}

		/*
		 * Cancel Shipment Button
		 */
		if ( ( 'auto-draft' != $order->get_status() ) && ! empty( Woocommerce_Shipping::$order_meta->get_shipment_label( $order_id ) ) ) {
			echo $this->get_cancel_shipment_button_markup();
		}

		/*
		 * Internetmarke Wizard Button
		 */
		$internetmarke_username = $provider::$options->get_option( 'internetmarke_portokasse_email' );
		$internetmarke_password = $provider::$options->get_option( 'internetmarke_portokasse_password' );

		if (
			( '' !== $internetmarke_username ) && ( '' !== $internetmarke_password ) &&
			( '' !== Api::get_client_id() ) && ( '' !== Api::get_client_secret() ) &&
			$order->has_shipping_method( Home_Delivery::get_instance()->id )
		) {

			$download_available = ( '' != $order->get_meta( '_wgm_internetmarke_output_type' ) && ( '' != $order->get_meta( '_wgm_internetmarke_binary_label_data' ) ) );
			if ( false !== $download_available ) {
				$link = Shipping_Provider::$internetmarke->create_label_download_link( $order_id );
			}

			?>
			<div class="wgm-meta-content-wrapper">
				<div class="inner">
					<?php
					if ( 200 === Shipping_Provider::$internetmarke->test_connection() ) :
						?>
						<a class="button-primary internetmarke" href="#"><?php echo __( 'Internetmarke Wizard', 'woocommerce-german-market' ) ?></a>
					<?php
					endif;
					?>
					<?php echo ( $download_available ? '<a class="internetmarke dashicons dashicons-download" href="' . $link . '" target="_blank" title="' . __( 'Download Label', 'woocommerce-german-market') . '"><!-- --></a>' : '' ) ?>
				</div>
			</div>
			<?php
		}

		/**
		 * API Error Box
		 */
		?>
		<div id="wgm_dhl_error">
			<div class="inner">
				<!-- -->
			</div>
		</div>
		<?php
	}

	/**
	 * Returns the markup with label download button.
	 *
	 * @param int $order_id
	 *
	 * @return string
	 */
	public function get_label_download_button_markup( int $order_id ) : string {

		return '
				<div id="label_download_wrapper" class="wgm-meta-content-wrapper">
					<div class="inner border-bottom">
                        <a class="button-primary wp-wc-shipping-pdf" href="' . wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_' . $this->id . '_shipping_label_download&order_id=' . $order_id ), 'wp-wc-label-pdf-download' ) . '" target="_blank">' . __( 'Download Label', 'woocommerce-german-market' ) . '</a>
                    </div>
                </div>
            ';
	}

	/**
	 * Returns the markup with export documents download button.
	 *
	 * @param int $order_id
	 *
	 * @return string
	 */
	public function get_export_documents_download_button_markup( int $order_id ) : string {

		return '
				<div id="export_documents_wrapper" class="wgm-meta-content-wrapper">
					<div class="inner border-bottom">
                        <a class="button-primary wp-wc-shipping-pdf wp-wc-shipping-export-documents-pdf" href="' . wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_' . $this->id . '_export_documents_download&order_id=' . $order_id ), 'wp-wc-export-documents-pdf-download' ) . '" target="_blank">' . __( 'Download Export Document', 'woocommerce-german-market' ) . '</a>
	                </div>
                </div>
            ';
	}

	/**
	 * Returns the markup with cancel shipment button.
	 *
	 * @return string
	 */
	public function get_cancel_shipment_button_markup() : string {

		return '
				<div id="cancel_shipment_wrapper" class="wgm-meta-content-wrapper">
					<div class="inner border-bottom">
	                    <input type="submit" id="_wgm_dhl_cancel_button" name="_wgm_dhl_cancel_button" value="' . sprintf( __( 'Cancel %s Label', 'woocommerce-german-market' ), 'DHL' ) . '" class="button button-secondary button-save-form" />
                    </div>
                </div>
            ';
	}

	/**
	 * This function saves the meta box values into order meta after store owner clicked
	 * on the generate label button.
	 *
	 * @Hook woocommerce_process_shop_order_meta
	 *
	 * @param int      $order_id current order id
	 * @param WC_Order $order order object
	 *
	 * @return void
	 */
	public function save_services_fields_for_packaging_hpos( $order_id, $order ) {

		if ( ! isset( $_POST[ 'add_services_fields_nonce' ] ) ) {
			return;
		}

		$nonce = $_REQUEST[ 'add_services_fields_nonce' ];

		if ( ! wp_verify_nonce( $nonce ) ) {
			return;
		}

		// Make sure we edit are in an order.
		if ( ! is_object( $order ) ) {
			return;
		}

		// Check user permission.
		if ( ! current_user_can( 'manage_woocommerce', $order_id ) ) {
			return;
		}

		if ( isset( $_POST[ '_wgm_dhl_action_button' ] ) && $_POST[ '_wgm_dhl_action_button' ] != '' ) {

			$this->save_meta_fields( $order );

		} else
			if ( isset( $_POST[ '_wgm_dhl_cancel_button' ] ) && ( '' != $_POST[ '_wgm_dhl_cancel_button' ] ) ) {

				$provider = Woocommerce_Shipping::get_instance()->get_provider_by_id( $this->id );
				$provider::$labels->do_cancel_shipment( $order );

			}
	}

	/**
	 * This function saves the meta box values into order meta after store owner clicked
	 * on the generate label button.
	 *
	 * @Wp-hook Hook save_post
	 *
	 * @global $woocommerce
	 *
	 * @param int $post_id
	 *
	 * @return int|void
	 */
	public function save_services_fields_for_packaging( int $post_id ) {
		global $woocommerce;

		if ( ! isset( $_POST[ 'add_services_fields_nonce' ] ) ) {
			return $post_id;
		}

		$nonce = $_REQUEST[ 'add_services_fields_nonce' ];

		if ( ! wp_verify_nonce( $nonce ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		if ( 'page' == $_POST[ 'post_type' ] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}

		if ( isset( $_POST[ '_wgm_dhl_action_button' ] ) && $_POST[ '_wgm_dhl_action_button' ] != '' ) {
			$order = wc_get_order( $post_id );
			if ( $order ) {
				$this->save_meta_fields( $order );
			}
		} else
			if ( isset( $_POST[ '_wgm_dhl_cancel_button' ] ) && ( '' != $_POST[ '_wgm_dhl_cancel_button' ] ) ) {
				$order = wc_get_order( $post_id );
				if ( $order ) {
					$provider = Woocommerce_Shipping::get_instance()->get_provider_by_id( $this->id );
					$provider::$labels->do_cancel_shipment( $order );
				}

			}
	}

	/**
	 * Disabling "DHL Packstation / Paketshops" shipping method at "Cash on Delivery" payment method
	 *
	 * @uses german_market_gateway_cash_on_delviery_enable_for_shipping_methods
	 *
	 * @param bool   $found
	 * @param string $chosen_method
	 *
	 * @return bool
	 */
	public function gateway_cash_on_delivery_enable_for_shipping_methods( bool $found, string $chosen_method ) : bool {

		if ( false !== strpos( $chosen_method, Packstation::get_instance()->id ) || false !== strpos( $chosen_method, Parcels::get_instance()->id ) ) {
			$found = false;
		}

		return $found;
	}

	/**
	 * Store selected terminal data to order.
	 *
	 * @Hook woocommerce_checkout_update_order_meta
	 *
	 * @param int $order_id
	 *
	 * @return void
	 */
	public function process_checkout_save_terminal_to_order( int $order_id ) {

		$order = wc_get_order( $order_id );
		if ( $order ) {
			if ( $order->has_shipping_method( Parcels::get_instance()->id ) || $order->has_shipping_method( Packstation::get_instance()->id ) ) {

				$method        = ( $order->has_shipping_method( Parcels::get_instance()->id ) ) ? 'parcelshops' : 'packstations';
				$parcelshop_id = $order->get_meta( Parcels::get_instance()->field_id );
				$terminal      = $this->get_terminal_by_id( $parcelshop_id, $method );

				// Store terminal data into order.
				$order->update_meta_data( Woocommerce_Shipping::$terminal_data_field, $terminal );
				$order->save();
			}
		}
	}

	/**
	 * Check if parcelshop id has changed.
	 *
	 * @Wp-hook save_post
	 *
	 * @param int $post_id current post / order id
	 *
	 * @return int
	 * @throws WC_Data_Exception
	 */
	public function maybe_save_changed_terminal( int $post_id ) : int {

		// Make sure we edit are in an order.
		if ( isset( $_POST[ 'post_type' ] ) && ( 'shop_order' != $_POST[ 'post_type' ] ) ) {
			return $post_id;
		}

		// Do not save data during autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check user permission.
		if ( ! current_user_can( 'manage_woocommerce', $post_id ) ) {
			return $post_id;
		}

		// Check if we are saving a new parcelshop id.
		if ( isset( $_POST[ 'wgm_shipping_old_terminal_id' ] ) && isset( $_POST[ 'wgm_shipping_new_terminal_id' ] ) ) {
			$new_terminal_id = (int) wc_clean( $_POST[ 'wgm_shipping_new_terminal_id' ] );
			$terminal        = (array) $_POST[ 'wgm_shipping_new_terminal' ];
			$order           = wc_get_order( $post_id );
			if ( $order && ( $order->has_shipping_method( Parcels::get_instance()->id ) || $order->has_shipping_method( Packstation::get_instance()->id ) ) && ! empty( $terminal ) ) {
				// Delete old shipping labels and tracking number(s).
				Woocommerce_Shipping::$order_meta->delete_order_shipments( $post_id );
				// Save new parcelshop id into order meta.
				$order->update_meta_data( Parcels::get_instance()->field_id, $new_terminal_id );
				// Save new terminal into order meta.
				$order->update_meta_data( Woocommerce_Shipping::$terminal_data_field, $terminal );
				// Save new Company Name
				$order->set_shipping_company( $terminal[ 'company' ] );
				// Save new Street
				$order->set_shipping_address_1( $terminal[ 'street' ] );
				// Save new Postal Code
				$order->set_shipping_postcode( $terminal[ 'pcode' ] );
				// Save new City
				$order->set_shipping_city( $terminal[ 'city' ] );
				// Saving order.
				$order->save();
			}
		}

		return $post_id;
	}

	/**
	 * Check if parcelshop id has changed.
	 *
	 * @Hook woocommerce_process_shop_order_meta
	 *
	 * @param int $order_id current order id
	 * @param WC_Order $order order object
	 *
	 * @return void
	 * @throws WC_Data_Exception
	 */
	public function maybe_save_changed_terminal_hpos( int $order_id, WC_Order $order ) {

		// Make sure we edit are in an order.
		if ( ! is_object( $order ) ) {
			return;
		}

		// Check user permission.
		if ( ! current_user_can( 'manage_woocommerce', $order_id ) ) {
			return;
		}

		// Check if we are saving a new parcelshop id.
		if ( isset( $_POST[ 'wgm_shipping_old_terminal_id' ] ) && isset( $_POST[ 'wgm_shipping_new_terminal_id'] ) ) {
			$new_terminal_id = (int) wc_clean( $_POST[ 'wgm_shipping_new_terminal_id' ] );
			$terminal        = (array) $_POST[ 'wgm_shipping_new_terminal' ];

			if ( ( $order->has_shipping_method( Parcels::get_instance()->id ) || $order->has_shipping_method( Packstation::get_instance()->id ) ) && ! empty( $terminal ) ) {
				// Delete old shipping labels and tracking number(s).
				Woocommerce_Shipping::$order_meta->delete_order_shipments( $order_id );
				// Save new parcelshop id into order meta.
				$order->update_meta_data( Parcels::get_instance()->field_id, $new_terminal_id );
				// Save new terminal into order meta.
				$order->update_meta_data( Woocommerce_Shipping::$terminal_data_field, $terminal );
				// Save new Company Name
				$order->set_shipping_company( $terminal[ 'company' ] );
				// Save new Street
				$order->set_shipping_address_1( $terminal[ 'street' ] );
				// Save new Postal Code
				$order->set_shipping_postcode( $terminal[ 'pcode' ] );
				// Save new City
				$order->set_shipping_city( $terminal[ 'city' ] );
				// Saving order.
				$order->save();
			}
		}

	}

	/**
	 * Returns the formatted shipping address from terminal.
	 *
	 * @acces public
	 *
	 * @param WC_Order $order
	 * @param array    $terminal
	 * @param bool     $is_backend
	 *
	 * @return string
	 */
	public function get_formatted_shipping_address( WC_Order $order, array $terminal, bool $is_backend = false ) : string {

		$address  = '<p>';

		if ( $order->has_shipping_method( Parcels::get_instance()->id ) || $order->has_shipping_method( Packstation::get_instance()->id ) ) {
			if ( false === $is_backend ) {
				if ( ! empty( $order->get_shipping_company() ) ) {
					$address .= $order->get_shipping_company() . '<br/>';
				}
				$address .= $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . '<br/>';
				$address .= $order->get_shipping_address_1() . '<br/>';
				$address .= $order->get_shipping_postcode() . ' ' . $order->get_shipping_city();
			} else {
				$address .= htmlspecialchars( $terminal[ 'company' ] ) . '<br/>';
				$address .= $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . '<br/>';
				$address .= $terminal[ 'street' ] . '<br/>';
				$address .= $terminal[ 'pcode' ] . ' ' . $terminal[ 'city' ];
			}
		} else {
			if ( ! empty( $order->get_shipping_company() ) ) {
				$address .= $order->get_shipping_company() . '<br/>';
			}
			$address .= $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . '<br/>';
			$address .= $order->get_shipping_address_1() . '<br/>';
			$address .= $order->get_shipping_postcode() . ' ' . $order->get_shipping_city();
		}

		$address .= '</p>';

		if ( $order->has_shipping_method( Parcels::get_instance()->id ) ) {
			$address .= '<p>';
			$address .= '<strong>' . __( 'Delivery to parcelshop', 'woocommerce-german-market' ) . '</strong><br/>';
			$address .= '</p>';
		} else
			if ( $order->has_shipping_method( Packstation::get_instance()->id ) ) {
				$address .= '<p>';
				$address .= '<strong>' . __( 'Delivery to packstation', 'woocommerce-german-market' ) . '</strong><br/>';
				$address .= '</p>';
			}

		return $address;
	}

	/**
	 * Override shipping address for packstation and parcelshop in backend view.
	 *
	 * @Hook woocommerce_admin_order_data_after_shipping_address
	 *
	 * @acces public
	 *
	 * @param WC_Order $order
	 *
	 * @return void
	 */
	public function add_delivery_point_information_to_order_address( WC_Order $order ) {

		if ( ! $order->has_shipping_method( Parcels::get_instance()->id ) && ! $order->has_shipping_method( Packstation::get_instance()->id ) ) {
			return;
		}

		$address  = '<p class="terminal-information">';
		if ( $order->has_shipping_method( Parcels::get_instance()->id ) ) {
			$address .= '<strong>' . __( 'Delivery to parcelshop', 'woocommerce-german-market' ) . '</strong><br/><br/>';
		} else
			if ( $order->has_shipping_method( Packstation::get_instance()->id ) ) {
				$address .= '<strong>' . __( 'Delivery to packstation', 'woocommerce-german-market' ) . '</strong><br/><br/>';
			}
		$address .= '</p>';

		echo $address;
	}

	/**
	 * Save meta fields to order if changed.
	 *
	 * @acces private
	 *
	 * @param $order
	 *
	 * @return void
	 */
	private function save_meta_fields( $order ) : void {
		$settings = array();

		$settings[ '_wgm_dhl_paket_product' ]                = $_POST[ '_wgm_dhl_paket_product' ] ?? '';
		$settings[ '_wgm_dhl_service_preferred_day' ]        = $_POST[ '_wgm_dhl_service_preferred_day' ] ?? '';
		$settings[ '_wgm_dhl_paket_weight' ]                 = $_POST[ '_wgm_dhl_paket_weight' ] ?? get_option( 'wgm_dhl_default_parcel_weight', 0.5 );
		$settings[ '_wgm_dhl_service_return_label' ]         = $_POST[ '_wgm_dhl_service_return_label' ] ?? 'off';
		$settings[ '_wgm_dhl_service_transport_insurance' ]  = $_POST[ '_wgm_dhl_service_transport_insurance' ] ?? 'off';
		$settings[ '_wgm_dhl_service_no_neighbor_delivery' ] = $_POST[ '_wgm_dhl_service_no_neighbor_delivery' ] ?? 'off';
		$settings[ '_wgm_dhl_service_named_person' ]         = $_POST[ '_wgm_dhl_service_named_person' ] ?? 'off';
		$settings[ '_wgm_dhl_service_premium' ]              = $_POST[ '_wgm_dhl_service_premium' ] ?? 'off';
		$settings[ '_wgm_dhl_service_bulky_goods' ]          = $_POST[ '_wgm_dhl_service_bulky_goods' ] ?? 'off';
		$settings[ '_wgm_dhl_service_codeable' ]             = $_POST[ '_wgm_dhl_service_codeable' ] ?? 'off';
		$settings[ '_wgm_dhl_service_outlet_routing' ]       = $_POST[ '_wgm_dhl_service_outlet_routing' ] ?? 'off';
		$settings[ '_wgm_dhl_service_age_visual' ]           = $_POST[ '_wgm_dhl_service_age_visual' ] ?? '0';
		$settings[ '_wgm_dhl_service_ident_check' ]          = $_POST[ '_wgm_dhl_service_ident_check' ] ?? '0';
		$settings[ '_wgm_dhl_service_terms_of_trade' ]       = $_POST[ '_wgm_dhl_service_terms_of_trade' ] ?? get_option( 'wgm_dhl_paket_default_terms_of_trade', WGM_SHIPPING_DELIVERY_DUTY_PAID_EXCL_VAT );
		$settings[ '_wgm_dhl_service_ddp' ]                  = $_POST[ '_wgm_dhl_service_ddp' ] ?? 'off';
		$settings[ '_wgm_dhl_service_endorsement' ]          = $_POST[ '_wgm_dhl_service_endorsement' ] ?? '0';

		$refresh = false;

		foreach ( $settings as $setting => $value ) {
			$old_value = $order->get_meta( $setting );
			if ( $value != $old_value ) {
				$order->update_meta_data( $setting, $value );
				$refresh = true;
			}
		}

		if ( $refresh ) {
			$order->save();
		}

		$provider = Woocommerce_Shipping::get_instance()->get_provider_by_id( $this->id );
		$provider::$labels->order_shipment_creation( array( $order ), $refresh );
	}

}
