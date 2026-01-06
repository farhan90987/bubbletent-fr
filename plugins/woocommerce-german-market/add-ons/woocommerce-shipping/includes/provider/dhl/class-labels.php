<?php

namespace MarketPress\GermanMarket\Shipping\Provider\DHL;

use MarketPress\GermanMarket\Shipping\Helper;
use MarketPress\GermanMarket\Shipping\Labels as Shipping_Labels;
use MarketPress\GermanMarket\Shipping\Package;
use MarketPress\GermanMarket\Shipping\Pdf_Merger;
use MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Home_Delivery;
use MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Packstation;
use MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Parcels;
use MarketPress\GermanMarket\Shipping\Woocommerce_Shipping;
use Olifolkerd\Convertor\Convertor;
use League\ISO3166\ISO3166;
use DVDoug\BoxPacker\NoBoxesAvailableException;
use WC_Order;
use Exception;
use DateInterval;
use DateTime;
use function DeepCopy\deep_copy;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

class Labels extends Shipping_Labels {

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
	 * Callback for order action.
	 *
	 * @uses woocommerce_order_action_dhl_print_parcel_label
	 *
	 * @acces public
	 *
	 * @param WC_Order $order
	 *
	 * @return void
	 * @throws Exception
	 */
	public function do_print_parcel_label( WC_Order $order ) {

		$provider = Woocommerce_Shipping::get_instance()->get_provider_by_id( $this->id );
		$shipment = $this->order_shipment_creation( array( $order ) );

		foreach ( $shipment as $order_id => $data ) {
			if ( 'ok' == $data[ 'status' ] ) {
				$result = $this->show_shipping_label( $order_id, $data );
				if ( null == $result ) {
					$order->add_order_note( sprintf( __( 'Cannot print %s label: Parcel not found', 'woocommerce-german-market' ), $provider->name ), false );
				}
			}
		}
	}

	/**
	 * Handle the Ajax shipment cancellation from admin order.
	 *
	 * @return void
	 */
	public function ajax_do_cancel_shipment() {

		// Check Nonce.
		check_ajax_referer( 'admin_order_create_label_nonce', 'nonce' );

		// Walking through the variables.

		$order_id = intval( $_POST[ 'order_id' ] );
		$order    = wc_get_order( $order_id );

		if ( ! is_object( $order ) || ! Helper::check_order_for_shipping_provider_methods( $order, $this->id ) ) {
			wp_send_json_error( array(
				'success' => false,
				'message' => sprintf( __( 'Order with ID %d not found.', 'woocommerce-german-market' ), $order_id ),
			));
		}

		$this->do_cancel_shipment( $order );

		// Send response.
		wp_send_json_success( array(
			'success' => true,
			'note'    => array(
				self::get_order_note_markup( sprintf( __( 'The %s shipment was cancelled for this order.', 'woocommerce-german-market' ), 'DHL' ) ),
			),
		) );
	}

	/**
	 * Cancelling a shipment request if there is already any parcel tracking number.
	 *
	 * @uses woocommerce_order_action_dhl_cancel_shipment
	 *
	 * @param WC_Order $order
	 *
	 * @return void
	 */
	public function do_cancel_shipment( WC_Order $order ) {

		$provider             = Woocommerce_Shipping::get_instance()->get_provider_by_id( $this->id );
		$order_id             = $order->get_id();
		$parcel_label_numbers = Woocommerce_Shipping::$order_meta->get_shipment_numbers( $order_id );

		if ( $parcel_label_numbers ) {
			// Delete shipment labels from database.
			$provider::$api->delete_order( $parcel_label_numbers );

			// Add order note.
			$order->add_order_note( __( 'The DHL shipment was cancelled for this order', 'woocommerce-german-market' ), false, false );

			// Remove Meta.
			$order->delete_meta_data( '_wgm_' . $this->id . '_shipping_label_email_sent' );
			$order->save();

			// Delete shipment labels from database.
			Woocommerce_Shipping::$order_meta->delete_order_shipments( $order_id );
		}
	}

	/**
	 * Creates the DHL 14 Digits account number.
	 *
	 * @param string $dhl_product dhl product number
	 * @param bool   $print_retoure_label optional
	 *
	 * @return string
	 */
	public function create_dhl_account_number( string $dhl_product, bool $print_retoure_label = false ) : string {

		$provider          = Woocommerce_Shipping::get_instance()->get_provider_by_id( $this->id );
		$dhl_ekp           = $provider::$options->get_option( 'global_ekp' );
		$dhl_participation = $provider::$options->get_option( 'paket_dhl_participation_' . strtoupper( $dhl_product ), '' );
		$is_sandbox        = 'on' === $provider::$options->get_option( 'test_mode', 'off' );
		$has_gogreen_plus  = 'on' === $provider::$options->get_option( 'service_gogreenplus_default', 'off' );

		if ( $print_retoure_label ) {
			$dhl_participation = $provider::$options->get_option( 'paket_dhl_participation_return', '' );
		}

		// Backward compatibility.
		if ( WGM_SHIPPING_PRODUKT_DHL_KLEINPAKET == $dhl_product ) {
			$dhl_participation = $provider::$options->get_option( 'paket_dhl_participation_' . WGM_SHIPPING_PRODUKT_DHL_WARENPOST, '' );
		}

		if ( $is_sandbox ) {
			$dhl_ekp = '3333333333';
			if ( $has_gogreen_plus ) {
				$dhl_participation = '02';
			}
			if ( WGM_SHIPPING_PRODUKT_DHL_PAKET === $dhl_product ) {
				if ( $has_gogreen_plus ) {
					if ( $print_retoure_label ) {
						$dhl_participation = '03';
					} else {
						$dhl_participation = '04';
					}
				}
			} else if ( ( WGM_SHIPPING_PRODUKT_DHL_WARENPOST_INTERNATIONAL === $dhl_product ) && $has_gogreen_plus ) {
				$dhl_participation = '04';
			}
		}

		preg_match( '!\d+!', $dhl_product, $matches );

		$dhl_product_number = $matches[ 0 ];

		// Retoure

		if ( true === $print_retoure_label ) {
			$dhl_product_number = '07';
		}

		// Return BillingNumber

		if ( $dhl_product_number ) {
			return $dhl_ekp . $dhl_product_number . $dhl_participation;
		}

		return '';
	}

	/**
	 * Handle the Ajax label creation from admin order.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function ajax_order_shipment_creation() {

		// Check Nonce.
		check_ajax_referer( 'admin_order_create_label_nonce', 'nonce' );

		// Walking through the variables.

		$order_id = intval( $_POST[ 'order_id' ] );
		$order    = wc_get_order( $order_id );
		$updated  = false;

		if ( ! is_object( $order ) || ! Helper::check_order_for_shipping_provider_methods( $order, $this->id ) ) {
			wp_send_json_error( array(
				'success' => false,
				'message' => sprintf( __( 'Order with ID %d not found.', 'woocommerce-german-market' ), $order_id ),
			));
		}

		$options = array(
			'_wgm_dhl_paket_product',
			'_wgm_dhl_service_preferred_day',
			'_wgm_dhl_paket_weight',
			'_wgm_dhl_service_return_label',
			'_wgm_dhl_service_transport_insurance',
			'_wgm_dhl_service_no_neighbor_delivery',
			'_wgm_dhl_service_named_person',
			'_wgm_dhl_service_premium',
			'_wgm_dhl_service_bulky_goods',
			'_wgm_dhl_service_codeable',
			'_wgm_dhl_service_outlet_routing',
			'_wgm_dhl_service_age_visual',
			'_wgm_dhl_service_ident_check',
			'_wgm_dhl_service_terms_of_trade',
			'_wgm_dhl_service_ddp',
			'_wgm_dhl_service_endorsement',
			'billing_dob',
		);

		foreach ( $options as $option_key ) {
			$option_value = $order->get_meta( $option_key );
			switch ( $option_key ) {
				case '_wgm_dhl_paket_product':
				case '_wgm_dhl_service_preferred_day':
					$option_new_value = $_POST[ $option_key ] ?? '';
					break;
				case '_wgm_dhl_paket_weight':
					$option_new_value = $_POST[ $option_key ] ?? get_option( 'wgm_dhl_default_parcel_weight', 2 );
					break;
				case '_wgm_dhl_service_age_visual':
				case '_wgm_dhl_service_ident_check':
				case '_wgm_dhl_service_terms_of_trade':
				case '_wgm_dhl_service_endorsement':
					$option_new_value = $_POST[ $option_key ] ?? '0';
					break;
				case 'billing_dob':
					if ( ! empty( $_POST[ $option_key ] ) ) {
						$date             = strtotime( $_POST[ $option_key ] );
						$option_new_value = date( 'Y-m-d', $date );
					} else {
						$option_new_value = '';
					}
					break;
				default:
					$option_new_value = $_POST[ $option_key ] ?? 'off';
					break;
			}
			if ( ! empty( $option_value ) ) {
				if ( $option_value !== $option_new_value ) {
					$order->update_meta_data( $option_key, $option_new_value );
					$updated = true;
				}
			} else {
				$order->update_meta_data( $option_key, $option_new_value );
				$updated = true;
			}
		}

		// Save order when meta data being updated.
		if ( $updated ) {
			$order->save();
		}

		// Label Creation.
		$result = $this->order_shipment_creation( array( $order ), $updated );

		// Creating Response.
		$response = array(
			'success' => true,
			'updated' => $updated,
		);

		foreach ( $result as $order_id => $line ) {
			if ( 'err' === $line[ 'status' ] ) {
				$response[ 'success' ] = false;
				$response[ 'error' ]   = '<p>' . __( 'Error occurred', 'woocommerce-german-market' ) . '</p>';
				$response[ 'error' ]  .= $line[ 'errlog' ];
			} else {
				$response[ 'buttons' ] = $line[ 'buttons' ];
				$response[ 'note' ]    = $line[ 'note' ];
			}
		}

		// Send response.
		wp_send_json_success( $response );
	}

	/**
	 * Calculate parcels total weight after changing input field in backend.
	 *
	 * @return void
	 */
	public function ajax_order_calculate_parcel_total_weight() {

		// Check Nonce.
		check_ajax_referer( 'admin_order_create_label_nonce', 'nonce' );

		$input_weight          = floatval( $_POST[ 'weight' ] );
		$order_id              = intval( $_POST[ 'order_id' ] );
		$order                 = wc_get_order( $order_id );
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

		// Given weight cannot be lower than product weight in parcel. Doesn´t make sense.
		/*
		if ( $input_weight < $product_weight ) {
			$input_weight = $product_weight;
		} else {
			$product_weight = $input_weight;
		}
		*/

		// We do not check if weight from input field is less that product weight anymore.
		$product_weight = $input_weight;

		$parcel_weight = $product_weight;

		if ( $parcel_weight < $minimum_parcel_weight ) {
			// Check if product weight is lower than our minimum weight in settings.
			$parcel_weight = $minimum_parcel_weight;
		}

		$additional_parcel_weight          = Shipping_Provider::$options->get_option( 'default_additional_parcel_weight', 0 );
		Package::$additional_parcel_weight = str_replace( ',', '.', $additional_parcel_weight );
		$total_weight                      = round( Package::maybe_add_additional_parcel_weight( $parcel_weight ), 3 );

		$response = array(
			'success'      => true,
			'input_weight' => $input_weight,
			'total_weight' => $total_weight,
		);

		// Send response.
		wp_send_json_success( $response );
	}

	/**
	 * Let's create our shipping data / collection and send it to the API.
	 *
	 * @param WC_Order[] $orders
	 * @param bool       $refresh
	 *
	 * @return array
	 * @throws Exception
	 */
	public function order_shipment_creation( $orders = array(), bool $refresh = false ) : array {

		$provider          = Shipping_Provider::get_instance();
		$tracking_barcodes = array();
		$shop_country      = $provider::$options->get_option( 'shipping_shop_address_country', 'DE' );

		// Building shipper address.

		if ( '' !== $provider::$options->get_option( 'shipping_gkp_shipper_reference' ) ) {

			$shipper[ 'shipperRef' ] = $provider::$options->get_option( 'shipping_gkp_shipper_reference' );

		} else {

			$country_data         = ( new ISO3166() )->alpha2( $shop_country );
			$shipper_country_code = $country_data[ 'alpha3' ];
			$correct_phone        = Helper::separate_phone_number_from_country_code( $provider::$options->get_option( 'shipping_shop_address_phone' ), $shop_country );
			$shipper_phone        = ( ! empty( $correct_phone[ 'dial_code' ] ) && ! empty( $correct_phone[ 'phone_number' ] ) ) ? $correct_phone[ 'dial_code' ] . ' ' . $correct_phone[ 'phone_number' ] : '';

			$shipper = array(
				'name1'         => $provider::$options->get_option( 'shipping_shop_address_name' ),
				'addressStreet' => $provider::$options->get_option( 'shipping_shop_address_street' ) . ' ' . str_replace( ' ', '', $provider::$options->get_option( 'shipping_shop_address_house_no' ) ),
				'postalCode'    => $provider::$options->get_option( 'shipping_shop_address_zip_code' ),
				'city'          => $provider::$options->get_option( 'shipping_shop_address_city' ),
				'country'       => $shipper_country_code,
				'email'         => $provider::$options->get_option( 'shipping_shop_address_email' ),
			);

			if ( '' !== $provider::$options->get_option( 'shipping_shop_address_company' ) ) {
				$shipper[ 'name1' ] = $provider::$options->get_option( 'shipping_shop_address_company' );
				$shipper[ 'name2' ] = $provider::$options->get_option( 'shipping_shop_address_name' );
			}

			if ( '' !== $shipper_phone ) {
				$shipper[ 'phone' ] = $shipper_phone;
			}

		}

		// Building retoure address.

		$country_data         = ( new ISO3166() )->alpha2( $provider::$options->get_option( 'shipping_shop_retour_address_country', 'DE') );
		$retoure_country_code = $country_data[ 'alpha3' ];
		$correct_phone        = Helper::separate_phone_number_from_country_code( $provider::$options->get_option( 'shipping_shop_retour_address_phone' ), $shop_country );
		$retoure_phone        = ( ! empty( $correct_phone[ 'dial_code' ] ) && ! empty( $correct_phone[ 'phone_number' ] ) ) ? $correct_phone[ 'dial_code' ] . ' ' . $correct_phone[ 'phone_number' ] : '';

		$retoure_address = array(
			'name1'         => $provider::$options->get_option( 'shipping_shop_retour_address_name' ),
			'addressStreet' => $provider::$options->get_option( 'shipping_shop_retour_address_street' ) . ' ' . str_replace( ' ', '', $provider::$options->get_option( 'shipping_shop_retour_address_house_no' ) ),
			'postalCode'    => $provider::$options->get_option( 'shipping_shop_retour_address_zip_code' ),
			'city'          => $provider::$options->get_option( 'shipping_shop_retour_address_city' ),
			'country'       => $retoure_country_code,
			'email'         => $provider::$options->get_option( 'shipping_shop_retour_address_email' ),
		);

		if ( '' !== $provider::$options->get_option( 'shipping_shop_retour_address_company' ) ) {
			$retoure_address[ 'name1' ] = $provider::$options->get_option( 'shipping_shop_retour_address_company' );
			$retoure_address[ 'name2' ] = $provider::$options->get_option( 'shipping_shop_retour_address_name' );
		}

		if ( ! empty( $shipper_phone ) ) {
			$retoure_address[ 'phone' ] = $retoure_phone;
		}

		// Walking through the orders.

		foreach ( $orders as $order ) {

			$order_id               = $order->get_id();
			$consignee_country_code = $order->get_shipping_country();
			$consignee_pcode        = $order->get_shipping_postcode();
			$is_international       = Helper::is_international_shipment( $shop_country, $consignee_country_code, $consignee_pcode );
			$is_european            = Helper::is_european_shipment( $shop_country, $consignee_country_code, $consignee_pcode );

			$dhl_product = $order->get_meta( '_wgm_dhl_paket_product' );

			if ( ! $dhl_product ) {
				if ( $consignee_country_code == $provider::$options->get_option( 'shipping_shop_address_country', 'DE' ) ) {
					// National shipment.
					$dhl_product = $provider::$options->get_option( 'paket_default_product_national', WGM_SHIPPING_PRODUKT_DHL_PAKET );
				} else {
					// International shipment.
					$dhl_product = $provider::$options->get_option( 'paket_default_product_international', WGM_SHIPPING_PRODUKT_DHL_PAKET_INTERNATIONAL );
				}
			}

			if ( WGM_SHIPPING_PRODUKT_DHL_WARENPOST === $dhl_product ) {
				$dhl_product = WGM_SHIPPING_PRODUKT_DHL_KLEINPAKET;
			}

			if ( Helper::check_order_for_shipping_provider_methods( $order, $this->id ) ) {

				$parcel_label_number = Woocommerce_Shipping::$order_meta->get_shipment_numbers( $order_id );

				if ( empty( $parcel_label_number ) || ( true === $refresh ) || ( true === apply_filters( 'wgm_shipping_dhl_always_create_new_label', false ) ) ) {

					// Delete stored labels from Database before regenerating.

					if ( true === $refresh ) {
						Woocommerce_Shipping::$order_meta->delete_order_shipments( $order_id );
					}

					if ( ! empty( $parcel_label_number ) && ( true === $refresh ) ) {
						$this->do_cancel_shipment( $order );
					}

					// Parcel Calculation.

					$maximum_box_weight = 0;

					if ( $order->has_shipping_method( Parcels::get_instance()->id ) ) {
						$maximum_box_weight = Parcels::get_instance()->parcels_delivery_limit_kg;
					} else
						if ( $order->has_shipping_method( Home_Delivery::get_instance()->id ) ) {
							$maximum_box_weight = Home_Delivery::get_instance()->parcels_delivery_limit_kg;
						} else
							if ( ( WGM_SHIPPING_PRODUKT_DHL_WARENPOST == $dhl_product ) || ( WGM_SHIPPING_PRODUKT_DHL_WARENPOST_INTERNATIONAL == $dhl_product ) || ( WGM_SHIPPING_PRODUKT_DHL_KLEINPAKET == $dhl_product ) ) {
								$maximum_box_weight = 1;
							}

					$products                 = $order->get_items();
					$boxes                    = $provider::$options->get_option( 'package_boxes', array() );
					$parcel_distribution      = $provider::$options->get_option( 'parcel_distribution', 1 );
					$group_variants           = $provider::$options->get_option( 'group_variable_products', 'off' );
					$additional_parcel_weight = $provider::$options->get_option( 'default_additional_parcel_weight', 0 );
					$precalculated_weight     = ! empty( $order->get_meta( '_wgm_dhl_paket_weight' ) ) ? $order->get_meta( '_wgm_dhl_paket_weight' ) : 0;

					// Set minimum and addition parcel weight from settings.

					Package::$default_parcel_weight    = floatval( $provider::$options->get_option( 'default_parcel_weight', 2.0 ) );
					Package::$minimum_parcel_weight    = floatval( $provider::$options->get_option( 'minimum_parcel_weight', 0.5 ) );
					Package::$additional_parcel_weight = str_replace( ',', '.', $additional_parcel_weight );

					// Start packaging parcels.

					try {

						$parcels = Package::calculate_box_packaging( $products, $boxes, $parcel_distribution, $group_variants, $maximum_box_weight, $precalculated_weight );

					} catch ( NoBoxesAvailableException $e ) {

						$problem_item        = $e->getItem();
						$shop_dimension_unit = get_option( 'woocommerce_dimension_unit', 'cm' );
						$width_converter     = new Convertor( $problem_item->getWidth(), 'mm' );
						$length_converter    = new Convertor( $problem_item->getLength(), 'mm' );
						$height_converter    = new Convertor( $problem_item->getDepth(), 'mm' );

						Helper::add_flash_notice( sprintf( __( 'Too large item in order #%s found. For the following item we could not find any available package box: %s (%s)', 'woocommerce-german-market' ), $order->get_id(), $problem_item->getDescription(), wc_format_dimensions( array( $width_converter->to( $shop_dimension_unit ), $length_converter->to( $shop_dimension_unit ), $height_converter->to( $shop_dimension_unit ) ) ) ) );

						return $tracking_barcodes;
					}

					if ( empty( $parcels ) ) {

						$error_message = sprintf( __( 'We could not calculate any package boxes for %s order #%s', 'woocommerce-german-market' ), $provider->name, $order->get_id() );

						if ( ! defined( 'DOING_AJAX' ) ) {
							Helper::add_flash_notice( $error_message, 'error' );
							return array();
						} else {
							$tracking_barcodes[ $order_id ][ 'status' ] = 'err';
							$tracking_barcodes[ $order_id ][ 'errlog' ] = '<ul class="wgm-dhl-error"><li>' . $error_message . '</li></ul>';
						}

						return $tracking_barcodes;
					}

					// Building up label request array.

					$order_shipment = array(
						'profile'   => apply_filters( 'wgm_shipping_dhl_parcel_shipment_rest_api_default_profile', 'STANDARD_GRUPPENPROFIL' ),
						'shipments' => array(),
					);

					// Walking through the parcels.

					foreach ( $parcels as $parcel_no => $parcel ) {

						// Check weight for DHL Warenpost / DHL Warenpost International / DHL Kleinpaket product. Limit is 1 kg.

						if ( ( ( WGM_SHIPPING_PRODUKT_DHL_WARENPOST == $dhl_product ) || ( WGM_SHIPPING_PRODUKT_DHL_WARENPOST_INTERNATIONAL == $dhl_product ) || ( WGM_SHIPPING_PRODUKT_DHL_KLEINPAKET == $dhl_product ) ) && ( floatval( $parcel[ 'weight' ] ) > 1 ) ) {
							$error_message = sprintf( __( 'Total weight of parcel #%d is %s kilograms. The weight limit for DHL Post Product is 1 kg.', 'woocommerce-german-market' ), $parcel_no + 1, round( $parcel[ 'weight' ], 2 ) );
							if ( ! defined( 'DOING_AJAX' ) ) {
								Helper::add_flash_notice( $error_message, 'error' );
								return array();
							} else {
								$tracking_barcodes[ $order_id ][ 'status' ] = 'err';
								$tracking_barcodes[ $order_id ][ 'errlog' ] = '<ul class="wgm-dhl-error"><li>' . $error_message . '</li></ul>';

								return $tracking_barcodes;
							}
						}

						// Check for DHL parcel weight limit if we have an additional weight.

						if ( ( $parcel[ 'weight' ] > 31.5 ) && ! empty( $provider::$options->get_option( 'default_additional_parcel_weight' ) ) ) {
							$error_message = sprintf( __( 'Total weight of parcel #%s with total weight of %s kilograms is greater than 31.5 kilograms, based on product weights and additional parcel weight settings', 'woocommerce-german-market' ), $parcel_no + 1, round( $parcel[ 'weight' ], 2 ) );
							if ( ! defined( 'DOING_AJAX' ) ) {
								Helper::add_flash_notice( $error_message, 'error' );
								return array();
							} else {
								$tracking_barcodes[ $order_id ][ 'status' ] = 'err';
								$tracking_barcodes[ $order_id ][ 'errlog' ] = '<ul class="wgm-dhl-error"><li>' . $error_message . '</li></ul>';

								return $tracking_barcodes;
							}
						}

						// Check for zero parcel weight.

						if ( 0 == $parcel[ 'weight' ] ) {
							$parcel[ 'weight' ] = Package::maybe_add_additional_parcel_weight( Package::$default_parcel_weight );
							if ( 0 == $precalculated_weight ) {
								$order->update_meta_data( '_wgm_dhl_paket_weight', Package::$default_parcel_weight );
								$order->save();
							}
						}

						if ( $parcel[ 'weight' ] < Package::$minimum_parcel_weight ) {
							$parcel[ 'weight' ] = Package::maybe_add_additional_parcel_weight( Package::$minimum_parcel_weight );
							if ( 0 == $precalculated_weight ) {
								$order->update_meta_data( '_wgm_dhl_paket_weight', Package::$minimum_parcel_weight );
								$order->save();
							}
						}

						$parcels[ $parcel_no ][ 'weight' ] = $parcel[ 'weight' ];

						// Set basic shipment data.

						$shipment = array(
							'product'       => $dhl_product,
							'billingNumber' => $this->create_dhl_account_number( $dhl_product ),
							'refNo'         => apply_filters( 'wgm_shipping_dhl_parcel_shipment_reference', sprintf( __( 'Order No. %d', 'woocommerce-german-market' ), $order_id ), $order_id ),
							'shipper'       => $shipper,
							'consignee'     => Shipping_Provider::$api->build_address_from_order( $order ),
							'details'       => array(
								'weight' => array(
									'uom'   => 'kg',
									'value' => round( $parcel[ 'weight' ], 3 ),
								),
							),
						);

						// Preferred Day.

						if ( ! empty( $order->get_meta( '_wgm_dhl_service_preferred_day' ) ) ) {

							// We are using 'deep_copy' function to clone the DateTime object.

							$preferred_first_day           = Shipping_Provider::calculate_first_preferred_delivery_day( true );
							$preferred_last_day            = deep_copy( $preferred_first_day );
							$preferred_last_day            = $preferred_last_day->add( DateInterval::createFromDateString( '6 days' ) );
							$preferred_customer_day_string = $order->get_meta( '_wgm_dhl_service_preferred_day' );
							$preferred_customer_day        = new DateTime( $preferred_customer_day_string, wp_timezone() );

							// Check for valid date format e.g. 2023-12-31

							if ( ! preg_match( '/^([19|20]+\d\d)[-](0[1-9]|1[012])[-.](0[1-9]|[12][0-9]|3[01])$/', $preferred_customer_day_string ) ) {
								$error_message = __( 'The preferred delivery date format is invalid.', 'woocommerce-german-market' );
								if ( ! defined( 'DOING_AJAX' ) ) {
									Helper::add_flash_notice( $error_message, 'error' );
									return array();
								} else {
									$tracking_barcodes[ $order_id ][ 'status' ] = 'err';
									$tracking_barcodes[ $order_id ][ 'errlog' ] = '<ul class="wgm-dhl-error"><li>' . $error_message . '</li></ul>';

									return $tracking_barcodes;
								}
							}

							// Check if we have a valid date.

							$check_date = date_parse( $preferred_customer_day_string );

							if ( ! empty( $check_date[ 'warnings' ] ) ) {
								$error_message = __( 'The preferred delivery date is not a valid date.', 'woocommerce-german-market' );
								if ( ! defined( 'DOING_AJAX' ) ) {
									Helper::add_flash_notice( $error_message, 'error' );
									return array();
								} else {
									$tracking_barcodes[ $order_id ][ 'status' ] = 'err';
									$tracking_barcodes[ $order_id ][ 'errlog' ] = '<ul class="wgm-dhl-error"><li>' . $error_message . '</li></ul>';

									return $tracking_barcodes;
								}
							}

							// Check if preferred date is between first and last possible date.

							if ( ( $preferred_customer_day < $preferred_first_day ) || ( $preferred_customer_day > $preferred_last_day ) ) {
								$error_message = __( 'The preferred delivery date is not allowed.', 'woocommerce-german-market' );
								if ( ! defined( 'DOING_AJAX' ) ) {
									Helper::add_flash_notice( $error_message, 'error' );
									return array();
								} else {
									$tracking_barcodes[ $order_id ][ 'status' ] = 'err';
									$tracking_barcodes[ $order_id ][ 'errlog' ] = '<ul class="wgm-dhl-error"><li>' . $error_message . '</li></ul>';

									return $tracking_barcodes;
								}
							}

							// Check for Sundays

							$day_of_week = date('w', $preferred_customer_day->getTimestamp() );

							if ( 0 == $day_of_week ) {
								$error_message = __( 'The chosen preferred delivery date is a sunday.', 'woocommerce-german-market' );
								if ( ! defined( 'DOING_AJAX' ) ) {
									Helper::add_flash_notice( $error_message, 'error' );
									return array();
								} else {
									$tracking_barcodes[ $order_id ][ 'status' ] = 'err';
									$tracking_barcodes[ $order_id ][ 'errlog' ] = '<ul class="wgm-dhl-error"><li>' . $error_message . '</li></ul>';

									return $tracking_barcodes;
								}
							}

							$shipment[ 'services' ][ 'preferredDay' ] = $preferred_customer_day_string;
						}

						// GoGreenService.
						// We should send a value to the API, even when not selected to allow the merchant to turn this option off.
						// This service is only available for domestic shipments.

						if ( Helper::is_domestic_shipment( $shop_country, $consignee_country_code,  ) ) {
							$shipment[ 'services' ][ 'goGreenPlus' ] = ( 'on' === $provider::$options->get_option( 'service_gogreenplus_default', 'off' ) );
						}

						// Retoure.

						if ( ( ! empty( $order->get_meta( '_wgm_dhl_service_return_label' ) ) && ( 'on' === $order->get_meta( '_wgm_dhl_service_return_label' ) ) ) || ( empty( $order->get_meta( '_wgm_dhl_service_return_label' ) ) && ( 'on' === $provider::$options->get_option( 'label_retoure_enabled', 'off' ) ) ) ) {

							$country_data         = ( new ISO3166() )->alpha2( $provider::$options->get_option( 'shipping_shop_retour_address_country', 'DE' ) );
							$retoure_country_code = $country_data[ 'alpha3' ];
							$correct_phone        = Helper::separate_phone_number_from_country_code( $provider::$options->get_option( 'shipping_shop_retour_address_phone' ), $shop_country );
							$retoure_phone        = ( ! empty( $correct_phone[ 'dial_code' ] ) && ! empty( $correct_phone[ 'phone_number' ] ) ) ? $correct_phone[ 'dial_code' ] . ' ' . $correct_phone[ 'phone_number' ] : '';

							$shipment[ 'services' ][ 'dhlRetoure' ] = array(
								'billingNumber' => $this->create_dhl_account_number( $dhl_product, true ),
								'refNo'         => apply_filters( 'wgm_shipping_dhl_parcel_shipment_retoure_reference', sprintf( __( 'Retoure Order No. %d', 'woocommerce-german-market' ), $order_id ), $order_id ),
								'returnAddress' => array(
									'name1'         => $provider::$options->get_option( 'shipping_shop_retour_address_name' ),
									'addressStreet' => $provider::$options->get_option( 'shipping_shop_retour_address_street' ),
									"addressHouse"  => str_replace( ' ', '', $provider::$options->get_option( 'shipping_shop_retour_address_house_no' ) ),
									'postalCode'    => $provider::$options->get_option( 'shipping_shop_retour_address_zip_code' ),
									'city'          => $provider::$options->get_option( 'shipping_shop_retour_address_city' ),
									'country'       => $retoure_country_code,
									'email'         => $provider::$options->get_option( 'shipping_shop_retour_address_email' ),
								),
							);

							if ( '' !== $provider::$options->get_option( 'shipping_shop_retour_address_company' ) ) {
								$shipment[ 'services' ][ 'dhlRetoure' ][ 'returnAddress' ][ 'name1' ] = $provider::$options->get_option( 'shipping_shop_retour_address_company' );
								$shipment[ 'services' ][ 'dhlRetoure' ][ 'returnAddress' ][ 'name2' ] = $provider::$options->get_option( 'shipping_shop_retour_address_name' );
							}

							if ( ! empty( $retoure_phone ) ) {
								$shipment[ 'services' ][ 'dhlRetoure' ][ 'returnAddress' ][ 'phone' ] = $retoure_phone;
							}

							// GoGreenService.
							// We should send a value to the API, even when not selected to allow the merchant to turn this option off.
							// This service is only available for domestic shipments.

							if ( Helper::is_domestic_shipment( $shop_country, $consignee_country_code, ) ) {
								$shipment[ 'services' ][ 'dhlRetoure' ][ 'goGreenPlus' ] = ( 'on' === $provider::$options->get_option( 'service_gogreenplus_default', 'off' ) );
							}
						}

						// Outlet Routing service.

						if ( $order->has_shipping_method( Home_Delivery::get_instance()->id ) ) {
							if ( ( ! empty( $order->get_meta( '_wgm_dhl_service_outlet_routing' ) ) && ( 'on' === $order->get_meta( '_wgm_dhl_service_outlet_routing' ) ) ) || ( empty( $order->get_meta( '_wgm_dhl_service_outlet_routing' ) ) && ( 'on' === $provider::$options->get_option( 'service_outlet_routing_default', 'off' ) ) ) ) {
								$shipment[ 'services' ][ 'parcelOutletRouting' ] = $order->get_billing_email();
							}
						}

						// Transport insurance.

						if ( ( ! empty( $order->get_meta( '_wgm_dhl_service_transport_insurance' ) ) && ( 'on' === $order->get_meta( '_wgm_dhl_service_transport_insurance' ) ) ) || ( empty( $order->get_meta( '_wgm_dhl_service_transport_insurance' ) ) && ( 'on' === $provider::$options->get_option( 'service_transport_insurance_default', 'off' ) ) ) ) {
							$insurance_value = 0;
							$order_items     = $parcel[ 'items_quantity' ];
							foreach ( $order_items as $product_id => $order_item ) {
								$insurance_value += $order_item[ 'item_price' ] * $order_item[ 'item_quantity' ];
							}
							$shipment[ 'services' ][ 'additionalInsurance' ] = array(
								'currency' => get_woocommerce_currency(),
								'value'    => floatval( apply_filters( 'wgm_shipping_dhl_parcel_shipment_insurance_value', $insurance_value, $parcel, $order ) ),
							);
						}

						// Personal Delivery - No neighbor delivery.

						if ( ( ! empty( $order->get_meta( '_wgm_dhl_service_no_neighbor_delivery' ) ) && ( 'on' === $order->get_meta( '_wgm_dhl_service_no_neighbor_delivery' ) ) ) || ( empty( $order->get_meta( '_wgm_dhl_service_no_neighbor_delivery' ) ) && ( 'on' === $provider::$options->get_option( 'service_no_neighbor_delivery_default', 'off' ) ) ) ) {
							$shipment[ 'services' ][ 'noNeighbourDelivery' ] = true;
						}

						// Named Person Only.

						if ( ( ! empty( $order->get_meta( '_wgm_dhl_service_named_person' ) ) && ( 'on' === $order->get_meta( '_wgm_dhl_service_named_person' ) ) ) || ( empty( $order->get_meta( '_wgm_dhl_service_named_person' ) ) && ( 'on' === $provider::$options->get_option( 'service_named_person_default', 'off' ) ) ) ) {
							if ( ! $order->has_shipping_method( Packstation::get_instance()->id ) ) {
								$shipment[ 'services' ][ 'namedPersonOnly' ] = true;
							}
						}

						// Premium.

						if ( ( ! empty( $order->get_meta( '_wgm_dhl_service_premium' ) ) && ( 'on' === $order->get_meta( '_wgm_dhl_service_premium' ) ) ) || ( empty( $order->get_meta( '_wgm_dhl_service_premium' ) ) && ( 'on' === $provider::$options->get_option( 'service_premium_default', 'off' ) ) ) ) {
							$shipment[ 'services' ][ 'premium' ] = true;
						}

						// Bulky Goods.

						if ( ( ! empty( $order->get_meta( '_wgm_dhl_service_bulky_goods' ) ) && ( 'on' === $order->get_meta( '_wgm_dhl_service_bulky_goods' ) ) ) || ( empty( $order->get_meta( '_wgm_dhl_service_bulky_goods' ) ) && ( 'on' === $provider::$options->get_option( 'service_bulky_goods_default', 'off' ) ) ) ) {
							$shipment[ 'services' ][ 'bulkyGoods' ] = true;
						}

						// Endorsement Type.

						if ( ( $is_international || $is_european ) && ( WGM_SHIPPING_PRODUKT_DHL_PAKET_INTERNATIONAL === $dhl_product ) ) {
							$endorsement_type = false;
							if ( ( ! empty( $order->get_meta( '_wgm_dhl_service_endorsement' ) ) && ( '0' !== $order->get_meta( '_wgm_dhl_service_endorsement' ) ) ) || ( empty( $order->get_meta( '_wgm_dhl_service_endorsement' ) ) && ( '0' !== $provider::$options->get_option( 'service_endorsement_default', '0' ) ) ) ) {
								if ( ! empty( $order->get_meta( '_wgm_dhl_service_endorsement' ) ) && ( '0' !== $order->get_meta( '_wgm_dhl_service_endorsement' ) ) ) {
									$endorsement_type = $order->get_meta( '_wgm_dhl_service_endorsement' );
								} else {
									$endorsement_type = $provider::$options->get_option( 'service_endorsement_default', '0' );
								}
								switch ( $endorsement_type ) {
									case 'IMMEDIATE':
										$endorsement_type = 'RETURN';
										break;
									case 'ABANDONMENT':
										$endorsement_type = 'ABANDON';
										break;
								}
							}
							if ( false === $endorsement_type ) {
								$endorsement_type = 'RETURN';
							}
							$shipment[ 'services' ][ 'endorsement' ] = $endorsement_type;
						}

						// Ident Check.

						if ( ( ! empty( $order->get_meta( '_wgm_dhl_service_ident_check' ) ) && ( '0' !== $order->get_meta( '_wgm_dhl_service_ident_check' ) ) ) || ( empty( $order->get_meta( '_wgm_dhl_service_ident_check' ) ) && ( '0' !== $provider::$options->get_option( 'service_ident_check_default', '0' ) ) ) ) {
							if ( ! $order->has_shipping_method( Packstation::get_instance()->id ) ) {
								if ( ! empty( $order->get_meta( '_wgm_dhl_service_ident_check' ) ) ) {
									$ident_check = $order->get_meta( '_wgm_dhl_service_ident_check' );
								} else {
									$ident_check = $provider::$options->get_option( 'service_ident_check_default', '0' );
								}
								$dob = $order->get_meta( 'billing_dob' );
								switch ( $ident_check ) {
									case 'ALL':
										$shipment[ 'services' ][ 'identCheck' ] = array(
											'firstName'   => $order->get_shipping_first_name(),
											'lastName'    => $order->get_shipping_last_name(),
											'dateOfBirth' => $dob,
											'minimumAge'  => apply_filters( 'wgm_shipping_dhl_parcel_shipment_ident_check_complete_minimum_age', 'A16', $order ),
										);
										break;
									case 'A16':
									case 'A18':
										$shipment[ 'services' ][ 'identCheck' ] = array(
											'firstName'  => $order->get_shipping_first_name(),
											'lastName'   => $order->get_shipping_last_name(),
											'minimumAge' => $ident_check,
										);
										break;
									case 'DOB':
										$shipment[ 'services' ][ 'identCheck' ] = array(
											'firstName'   => $order->get_shipping_first_name(),
											'lastName'    => $order->get_shipping_last_name(),
											'dateOfBirth' => $dob,
										);
										break;
								}
							}
						}

						// Visual Age Check.

						if ( ( ! empty( $order->get_meta( '_wgm_dhl_service_age_visual' ) ) && ( '0' !== $order->get_meta( '_wgm_dhl_service_age_visual' ) ) ) || ( empty( $order->get_meta( '_wgm_dhl_service_age_visual' ) ) && ( '0' !== $provider::$options->get_option( 'service_age_visual_default', '0' ) ) ) ) {
							if ( ! $order->has_shipping_method( Packstation::get_instance()->id ) ) {
								if ( ! empty( $order->get_meta( '_wgm_dhl_service_age_visual' ) ) ) {
									$age_visual_check = $order->get_meta( '_wgm_dhl_service_age_visual' );
								} else {
									$age_visual_check = $provider::$options->get_option( 'service_age_visual_default', '0' );
								}
								$shipment[ 'services' ][ 'visualCheckOfAge' ] = $age_visual_check;
							}
						}

						// Cash on Delivery.

						if ( 'cash_on_delivery' == $order->get_payment_method() ) {

							$reference_1 = $provider::$options->get_option( 'shipping_shop_cod_reference', __( 'Order', 'woocommerce-german-market' ) . ' ' . '{order_id}' );
							$reference_2 = $provider::$options->get_option( 'shipping_shop_cod_reference_2', '{email}' );

							$shortcodes = array(
								'{order_id}',
								'{invoice_number}',
								'{email}'
							);

							$replacements = array(
								$order_id,
								$order->get_order_number(),
								$order->get_billing_email(),
							);

							$reference_1 = str_replace( $shortcodes, $replacements, $reference_1 );
							$reference_2 = str_replace( $shortcodes, $replacements, $reference_2 );

							$shipment[ 'services' ][ 'cashOnDelivery' ] = array(
								'amount' => array(
									'currency' => get_woocommerce_currency(),
									'value'    => $order->get_total(),
								),
								'bankAccount' => array(
									'accountHolder' => $provider::$options->get_option( 'shipping_shop_cod_bank_account_holder' ),
									'bankName'      => $provider::$options->get_option( 'shipping_shop_cod_bank_name' ),
									'iban'          => $provider::$options->get_option( 'shipping_shop_cod_iban' ),
									'bic'           => $provider::$options->get_option( 'shipping_shop_cod_bic' ),
								),
								'transferNote1' => apply_filters( 'wgm_shipping_dhl_parcel_shipment_cod_transfernote_1', $reference_1, $order_id, $order ),
								'transferNote2' => apply_filters( 'wgm_shipping_dhl_parcel_shipment_cod_transfernote_1', $reference_2, $order_id, $order ),
							);
						}

						// Postal Delivery Duty Paid

						if ( $is_international || $is_european ) {
							if ( 'on' === $order->get_meta( '_wgm_dhl_service_ddp' ) ) {
								$shipment[ 'services' ][ 'postalDeliveryDutyPaid' ] = true;
							}
						}

						// Packstation / Locker.

						if ( $order->has_shipping_method( Packstation::get_instance()->id ) ) {

							$packstation_id = $order->get_meta( Packstation::get_instance()->field_id );
							$terminal       = $order->get_meta( Woocommerce_Shipping::$terminal_data_field );

							if ( ( $packstation_id != $terminal[ 'parcelshop_id' ] ) ) {
								$packstation_instance = Packstation::get_instance();
								$terminal   = $packstation_instance->get_selected_terminal( $order->get_shipping_country(), $order->get_shipping_city(), $order->get_shipping_address_1(), $order->get_shipping_postcode(), $packstation_id, 'array' );
								if ( ! empty( $terminal ) ) {
									$order->update_meta_data( Woocommerce_Shipping::$terminal_data_field, $terminal );
								}
							}

							$receiver_company = $shipment[ 'consignee' ][ 'name1' ];
							$receiver_name    = $shipment[ 'consignee' ][ 'name2' ] ?? '';

							// We have to convert name key in array. WTH :-(
							unset( $shipment[ 'consignee' ][ 'name1' ] );

							if ( ! empty( $receiver_name ) ) {
								$shipment[ 'consignee' ][ 'name' ]  = $receiver_name;
								$shipment[ 'consignee' ][ 'name2' ] = $receiver_company;
							} else {
								$shipment[ 'consignee' ][ 'name' ] = $receiver_company;
							}

							$shipment[ 'consignee' ][ 'lockerID' ]   = $packstation_id;
							$shipment[ 'consignee' ][ 'postNumber' ] = $order->get_meta( 'wc_shipping_dhl_client_number' );
							$shipment[ 'consignee' ][ 'postalCode' ] = $terminal[ 'pcode' ];
							$shipment[ 'consignee' ][ 'city' ]       = $terminal[ 'city' ];

							// Converting packstation country code to 3-alpha-iso code.
							$country_data                         = ( new ISO3166() )->alpha2( $order->get_shipping_country() );
							$shipment[ 'consignee' ][ 'country' ] = $country_data[ 'alpha3' ];

						} else

							// Parcelshop.

							if ( $order->has_shipping_method( Parcels::get_instance()->id ) ) {

								$parcelshop_id = $order->get_meta( Parcels::get_instance()->field_id );
								$terminal      = $order->get_meta( Woocommerce_Shipping::$terminal_data_field );

								if ( ! isset( $terminal[ 'parcelshop_id' ] ) || ( $parcelshop_id != $terminal[ 'parcelshop_id' ] ) ) {
									$parcels_instance = Parcels::get_instance();
									$terminal         = $parcels_instance->get_selected_terminal( $order->get_shipping_country(), $order->get_shipping_city(), $order->get_shipping_address_1(), $order->get_shipping_postcode(), $parcelshop_id, 'array' );
									if ( ! empty( $terminal ) ) {
										$order->update_meta_data( Woocommerce_Shipping::$terminal_data_field, $terminal );
										$order->save();
									}
								}

								$receiver_company = $shipment[ 'consignee' ][ 'name1' ];
								$receiver_name    = $shipment[ 'consignee' ][ 'name2' ] ?? '';

								// We have to convert name key in array. WTH :-(
								unset( $shipment[ 'consignee' ][ 'name1' ] );

								if ( ! empty( $receiver_name ) ) {
									$shipment[ 'consignee' ][ 'name' ]  = $receiver_name;
									$shipment[ 'consignee' ][ 'name2' ] = $receiver_company;
								} else {
									$shipment[ 'consignee' ][ 'name' ] = $receiver_company;
								}

								$shipment[ 'consignee' ][ 'retailID' ]   = $parcelshop_id;
								$shipment[ 'consignee' ][ 'postNumber' ] = $order->get_meta( 'wc_shipping_dhl_client_number', true );
								$shipment[ 'consignee' ][ 'email' ]      = $order->get_billing_email();
								$shipment[ 'consignee' ][ 'city' ]       = $terminal[ 'city' ];
								$shipment[ 'consignee' ][ 'postalCode' ] = $terminal[ 'pcode' ];

								// Converting postfilial country code to 3-alpha-iso code.
								$country_data                         = ( new ISO3166() )->alpha2( $order->get_shipping_country() );
								$shipment[ 'consignee' ][ 'country' ] = $country_data[ 'alpha3' ];

							}

						// Add export information for International Shipments.

						if ( true === $is_international || true === $is_european ) {

							$customs = array(
								'exportType'                      => $this->get_export_type( $order ),
								'exportDescription'               => apply_filters( 'wgm_shipping_dhl_parcel_shipment_international_export_description', $order->get_meta( '_wgm_export_description' ), $order_id, $order ),
								'hasElectronicExportNotification' => apply_filters( 'wgm_shipping_dhl_parcel_shipment_international_electronic_export_notification', false, $order_id, $order ),
								'postalCharges'                   => array(
									'currency' => get_woocommerce_currency(),
									'value'    => $order->get_shipping_total(),
								),
								'permitNo'                        => apply_filters( 'wgm_shipping_dhl_parcel_shipment_international_permit_number', '', $order_id, $order ),
								'attestationNo'                   => apply_filters( 'wgm_shipping_dhl_parcel_shipment_international_attestation_number', '', $order_id, $order ),
								'invoiceNo'                       => apply_filters( 'wgm_shipping_dhl_parcel_shipment_international_invoice_number', $order->get_order_number(), $order_id, $order ),
								'shipperCustomsRef'               => apply_filters( 'wgm_shipping_dhl_parcel_shipment_international_shipper_customs_reference', '', $order_id, $order ),
								'consigneeCustomsRef'             => apply_filters( 'wgm_shipping_dhl_parcel_shipment_international_consignee_customs_reference', '', $order_id, $order ),
								'officeOfOrigin'                  => apply_filters( 'wgm_shipping_dhl_parcel_shipment_international_office_origin', '', $order_id, $order ),
								'items'                           => array(),
							);

							// Terms of Trade

							if ( ( $is_international || $is_european ) && ( ( empty( $order->get_meta( '_wgm_dhl_service_ddp' ) ) || 'off' === $order->get_meta( '_wgm_dhl_service_ddp' ) ) && ( WGM_SHIPPING_PRODUKT_DHL_EURO_PAKET_B2B === $dhl_product ) ) ) {
								$customs[ 'shippingConditions' ] = ( '' != $order->get_meta( '_wgm_dhl_service_terms_of_trade' ) ) ? $order->get_meta( '_wgm_dhl_service_terms_of_trade' ) : $provider::$options->get_option( 'paket_default_terms_of_trade', WGM_SHIPPING_DELIVERY_DUTY_PAID_EXCL_VAT );
							}

							// Walking through the item data.

							foreach ( $parcel[ 'items_quantity' ] as $item_product_id => $item ) {

								// Do not list free items
								if ( floatval( $item[ 'item_price' ] ) <= 0 ) {
									continue;
								}

								// Try to handle Country Of Origin.

								$item[ 'item_hscode' ] = apply_filters( 'wgm_shipping_dhl_parcel_shipment_international_item_hscode', $item[ 'item_hscode' ], $item_product_id, $item, $order );
								$item[ 'item_coo' ]    = apply_filters( 'wgm_shipping_dhl_parcel_shipment_international_item_origin_country', $item[ 'item_coo' ], $item_product_id, $item, $order );

								if ( ! empty( $item[ 'item_coo' ] ) ) {
									try {
										$item_country_data  = ( new ISO3166() )->alpha2( $item[ 'item_coo' ] );
										$item[ 'item_coo' ] = $item_country_data[ 'alpha3' ];
									} catch( Exception $e ) {

										$error_message = sprintf( __( 'The <strong>Country Of Origin "%s"</strong> cannot be found. Please supply correct 2-Alpha-Country-Codes e.g. "DE" for Germany.', 'woocommerce-german-market' ), $item[ 'item_coo' ] );

										if ( ! defined( 'DOING_AJAX' ) ) {
											Helper::add_flash_notice( $error_message, 'error' );
											return array();
										} else {
											$tracking_barcodes[ $order_id ][ 'status' ] = 'err';
											$tracking_barcodes[ $order_id ][ 'errlog' ] = $error_message;
										}
									}
								} else
									if ( true === apply_filters( 'wgm_shipping_dhl_parcel_shipment_international_item_set_origin_country_to_shop_country', false ) ) {
										$item_country_data  = ( new ISO3166() )->alpha2( $provider::$options->get_option( 'shipping_shop_address_country', 'DE' ) );
										$item[ 'item_coo' ] = $item_country_data[ 'alpha3' ];
									}

								// Fill the item data.

								$item_arr = array(
									'itemDescription'  => apply_filters( 'wgm_shipping_dhl_parcel_shipment_international_item_description', substr( htmlentities( mb_convert_encoding( $item[ 'item_description' ], 'UTF-8', mb_detect_encoding( $item[ 'item_description' ] ) ) ), 0, 50 ), $item, $item_product_id, $order_id, $order ),
									'packagedQuantity' => $item[ 'item_quantity' ],
									'itemValue'        => array(
										'currency' => get_woocommerce_currency(),
										'value'    => floatval( $item[ 'item_price' ] ),
									),
									'itemWeight'       => array(
										'uom'   => 'kg',
										'value' => floatval( $item[ 'item_weight' ] ),
									),
								);

								if ( ! empty( $item[ 'item_hscode' ] ) ) {
									$item_arr[ 'hsCode' ] = $item[ 'item_hscode' ];
								}

								if ( ! empty( $item[ 'item_coo' ] ) ) {
									$item_arr[ 'countryOfOrigin' ] = $item[ 'item_coo' ];
								}

								$customs[ 'items' ][] = $item_arr;
							}

							$shipment[ 'customs' ] = $customs;
						}

						// Add shipment to request array.

						$order_shipment[ 'shipments' ][] = $shipment;
					}

					// Allow to hook into shipment array.

					/**
					 * @param array    $order_shipment
					 * @param int      $order_id
					 * @param WC_Order $order
					 */
					$order_shipment = apply_filters( 'wgm_shipping_dhl_order_shipment_before_api_request', $order_shipment, $order_id, $order );

					// Send Order to DHL REST API.

					$response = $provider::$api->store_order( $order_shipment, $order );

					if ( is_array( $response ) ) {
						$response = json_decode( $response[ 'body' ], true );
					}

					// Send final API request if no validation errors occurred.

					if ( true === apply_filters( 'wgm_shipping_dhl_store_order_shipment_validation_before_final_request', true ) ) {
						if ( isset( $response[ 'status' ][ 'statusCode' ] ) && in_array( $response[ 'status' ][ 'statusCode' ], array( 200, 207 ) ) ) {
							$response = $provider::$api->store_order( $order_shipment, $order, false );
							$response = json_decode( $response[ 'body' ], true );
						}
					}

					// Allow to hook into after we got a response object.

					/**
					 * @param object|array $response
					 * @param array        $order_shipment
					 * @param int          $order_id
					 * @param WC_Order     $order
					 */
					do_action( 'wgm_shipping_dhl_order_shipment_after_api_request', $response, $order_shipment, $order_id, $order );

					// Checking the API result for success
					// Code '200' means success for single element
					// Code '207' means success for multiple elements

					$has_export_documents = false;

					if ( isset( $response[ 'status' ][ 'statusCode' ] ) && in_array( $response[ 'status' ][ 'statusCode' ], array( 200, 207 ) ) ) {

						// Walking through the response items

						foreach ( $response[ 'items' ] as $parcel_number => $response_item ) {

							// Check for line item success

							if ( isset( $response_item[ 'sstatus' ] ) && ( 200 == $response_item[ 'sstatus' ][ 'statusCode' ] ) ) {

								// Our parcel tracking number.

								$parcel_label_number = $response_item[ 'shipmentNo' ];

								// Fetch shipping label.

								$shipment_id = Woocommerce_Shipping::$order_meta->set_shipment_label( $order_id, $parcel_label_number, base64_decode( $response_item[ 'label' ][ 'b64' ] ) );

								if ( isset( $response_item[ 'returnLabel' ] ) ) {
									Woocommerce_Shipping::$order_meta->set_shipment_retoure_label( $shipment_id, base64_decode( $response_item[ 'returnLabel' ][ 'b64' ] ) );
								}

								// Fetch and store export documents

								if ( isset( $response_item[ 'customsDoc' ] ) ) {
									Woocommerce_Shipping::$order_meta->set_shipment_export_documents( $shipment_id, base64_decode( $response_item[ 'customsDoc' ][ 'b64' ] ) );
									$has_export_documents = true;
								}

								// Save Tracking Number and parcel information to order notices

								if ( ! empty( $parcel_label_number ) ) {

									$parcel = $parcels[ $parcel_number ];

									$note  = '<strong>' . sprintf( __( 'Tracking number: %s', 'woocommerce-german-market' ), $parcel_label_number ) . '</strong><br><br>';

									// Check if we used box packer

									if ( isset( $parcel[ 'reference' ] ) ) {
										$note .= '<strong>' . sprintf( __( 'Package Box %s ( %s )', 'woocommerce-german-market' ), ( $parcel_number + 1 ), $parcel[ 'reference' ] ) . '</strong><br>';
										$note .= __( 'Dimensions', 'woocommerce-german-market' ) . ': ' . $parcel[ 'dimensions' ] . '<br>';
									} else {
										$note .= '<strong>' . sprintf( __( 'Package Box %s', 'woocommerce-german-market' ), ( $parcel_number + 1 ) ) . '</strong><br>';
									}

									$note .= __( 'Weight', 'woocommerce-german-market' ) . ': ' . $parcel[ 'weight' ] . ' ' . get_option( 'woocommerce_weight_unit', 'kg' ) . '<br><br>';
									$note .= '<strong>' . __( 'Items in this Box', 'woocommerce-german-market' ) . '</strong><br><br>';

									$items = explode( ', ', $parcel[ 'items_text' ] );

									foreach ( $items as $item ) {
										$note .= $item . '<br>';
									}

									$order->add_order_note( $note, false, false );

									$tracking_barcodes[ $order_id ][ 'status' ]     = 'ok';
									$tracking_barcodes[ $order_id ][ 'barcodes' ][] = $parcel_label_number;
									$tracking_barcodes[ $order_id ][ 'note' ][]     = self::get_order_note_markup( $note );
								}

							} else {

								// We have a bad request.

								$tracking_barcodes[ $order_id ][ 'status' ] = 'err';
								$tracking_barcodes[ $order_id ][ 'errlog' ] = ( new Api_Error() )->get_response_error_message( $response );

								// Add error flash notice if it's not an Ajax Request.

								if ( ! defined( 'DOING_AJAX' ) ) {
									Helper::add_flash_notice( sprintf( __( 'Error occurred while request shipping label for order #%s: %s', 'woocommerce-german-market' ), $order_id, $tracking_barcodes[ $order_id ][ 'errlog' ] ), 'error' );
								}

							}

						}

					} else {

						// We have a bad request.

						$tracking_barcodes[ $order_id ][ 'status' ] = 'err';
						$tracking_barcodes[ $order_id ][ 'errlog' ] = ( new Api_Error() )->get_response_error_message( $response );

						// Add error flash notice if it's not an Ajax Request.

						if ( ! defined( 'DOING_AJAX' ) ) {
							Helper::add_flash_notice( sprintf( __( 'Error occurred while request shipping label for order #%s: %s', 'woocommerce-german-market' ), $order_id, $tracking_barcodes[ $order_id ][ 'errlog' ] ), 'error' );
						}

					}

					// Customer response in backend.

					if ( ! empty( $tracking_barcodes[ $order_id ][ 'barcodes' ] ) ) {

						if ( ! defined( 'DOING_AJAX' ) ) {
							if ( ! $has_export_documents ) {
								$success_message = __( 'The Shipping Label(s) was created successfully. You can download / print it from the DHL Meta Box on the right side.', 'woocommerce-german-market' );
							} else {
								$success_message = __( 'The Shipping Label(s) and export document(s) were created successfully. You can download / print it from the DHL Meta Box on the right side.', 'woocommerce-german-market' );
							}

							Helper::add_flash_notice( $success_message, 'success' );
						} else {
							$tracking_barcodes[ $order_id ][ 'buttons' ] = array(
								'markup_download_label'            => Shipping_Provider::$backend->get_label_download_button_markup( $order_id ),
								'markup_cancel_shipment'           => Shipping_Provider::$backend->get_cancel_shipment_button_markup(),
								'markup_download_export_documents' => ( $has_export_documents ? Shipping_Provider::$backend->get_export_documents_download_button_markup( $order_id ) : '' ),
							);
						}

						$tracking_barcodes[ $order_id ][ 'status' ] = 'ok';
					}

					// Check if we have a label and if we should send it per email

					$email_enabled = $provider::$options->get_option( 'label_email_enabled', 'off' );

					if ( ( 'ok' == $tracking_barcodes[ $order_id ][ 'status' ] ) && ( 'on' === $email_enabled ) ) {

						$this->send_shipping_label_via_email( $order_id );
					}

				} else {
					$tracking_barcodes[ $order_id ][ 'status' ]   = 'ok';
					$tracking_barcodes[ $order_id ][ 'barcodes' ] = $parcel_label_number;
				}

			} else {
				$tracking_barcodes[ $order_id ][ 'status' ] = 'err';
				$tracking_barcodes[ $order_id ][ 'errlog' ] = sprintf( __( 'Shipping method is not %s', 'woocommerce-german-market' ), $provider->name );
			}
		}

		return $tracking_barcodes;
	}

	/**
	 * Returns the export type for international shipments.
	 *
	 * @acces protected
	 *
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	protected function get_export_type( WC_Order $order ) : string {

		$export_reason = ( ! empty( $order->get_meta( 'export_reason' ) ) ) ? $order->get_meta( 'export_reason' ) : 'sale';

		switch ( strtolower( $export_reason ) ) {
			case 'gift':
				$export_type = 'PRESENT';
				break;
			case 'sample':
				$export_type = 'COMMERCIAL_SAMPLE';
				break;
			case 'repair':
				$export_type = 'RETURN_OF_GOODS';
				break;
			case 'sale':
				$export_type = 'COMMERCIAL_GOODS';
				break;
			case 'document':
				$export_type = 'DOCUMENT';
				break;
			default:
				$export_type = 'OTHER';
				break;
		}

		return apply_filters( 'wgm_shipping_dhl_international_shipping_api_export_type', strtoupper( $export_type ), $order );
	}

	/**
	 * Ajax, manages what happen when the download button in public account order was clicked.
	 *
	 * @uses wp_ajax_woocommerce_wc_dhl_shipping_export_documents_download
	 *
	 * @return void, exit()
	 * @throws Exception
	 */
	public function download_order_shipping_export_documents() {

		if ( ! check_ajax_referer( 'wp-wc-export-documents-pdf-download', 'security', false ) ) {
			wp_die( __( 'You have taken too long. Please go back and retry.', 'woocommerce-german-market' ), '', array( 'response' => 403 ) );
		}

		$order_id = intval( $_REQUEST[ 'order_id' ] );
		$order    = wc_get_order( $order_id );

		if ( ! is_object( $order ) ) {
			return;
		}

		$export_documents = Woocommerce_Shipping::$order_meta->get_shipment_export_documents( $order_id );

		if ( ! empty( $export_documents ) ) {
			$this->download_export_documents( $order_id, $export_documents );
		}

		exit();
	}

	/**
	 * Initiate PDF Download.
	 *
	 * @acces private
	 *
	 * @param int   $order_id
	 * @param array $export_documents
	 *
	 * @return void, die()
	 * @throws Exception
	 */
	private function download_export_documents( int $order_id, array $export_documents ) {

		$filename = $this->id . '_shipping_export_documents_order_' . $order_id;
		$filename = apply_filters( 'wgm_shipping_' . $this->id . '_filename_shipping_export_documents', $filename, $order_id );

		$pdf_merge          = Pdf_Merger::get_instance();
		$temp_file_handlers = array();

		foreach ( $export_documents as $document ) {
			// Save binary shipment label data to temp file.
			$tmp_file_label       = tmpfile();
			$tmp_file_path        = stream_get_meta_data( $tmp_file_label )[ 'uri' ];
			$temp_file_handlers[] = $tmp_file_label;
			@fwrite( $tmp_file_label, $document[ 'order_export_docs' ] );
			$pdf_merge->addPDF( $tmp_file_path );
		}

		$pdf = $pdf_merge->merge( 'string' );

		// Temp files will be automatically deleted from server when closing the file handler.
		foreach ( $temp_file_handlers as $file_handler ) {
			fclose( $file_handler );
		}

		header( 'Content-Type: application/pdf' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '.pdf"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );

		die( $pdf );
	}

}
