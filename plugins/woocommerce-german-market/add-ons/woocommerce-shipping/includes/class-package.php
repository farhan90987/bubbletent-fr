<?php

namespace MarketPress\GermanMarket\Shipping;

use DVDoug\BoxPacker\Packer;
use Olifolkerd\Convertor\Convertor;
use Olifolkerd\Convertor\Exceptions\ConvertorDifferentTypeException;
use Olifolkerd\Convertor\Exceptions\ConvertorException;
use Olifolkerd\Convertor\Exceptions\ConvertorInvalidUnitException;
use WC_Order_Item;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

class Package {

	/**
	 * @var float
	 */
	public static float $default_parcel_weight = 0;

	/**
	 * @var float
	 */
	public static float $minimum_parcel_weight = 0;

	/**
	 * @var string
	 */
	public static string $additional_parcel_weight;

	/**
	 * Native calculation of parcel weights based on 'parcel distribution' setting.
	 *
	 * @static
	 *
	 * @param int             $parcel_distribution 'Parcel Distribution' setting
	 * @param string          $group_variants_setting 'Group Variants' setting
	 * @param WC_Order_Item[] $products WooCommerce order items
	 * @param string          $pre_calculated_weight (optional) pre-calculated weight
	 *
	 * @return array
	 */
	protected static function native_box_packaging_calculator( int $parcel_distribution, string $group_variants_setting, array $products, $pre_calculated_weight = 0 ) : array {

		// How many labels print
		$labels_setting   = absint( $parcel_distribution );
		$shop_weight_unit = get_option( 'woocommerce_weight_unit' );
		$parcels          = array();
		$parcel_weight    = ( 0 != $pre_calculated_weight ? $pre_calculated_weight : 0 );
		$items_quantity   = array();

		foreach ( $products as $product ) {
			$product_data = $product->get_product();

			if ( ! is_object( $product_data ) ) {
				continue;
			}

			// If product is virtual, skip it
			if ( method_exists( $product_data, 'needs_shipping' ) ) {
				if ( ! $product_data->needs_shipping() ) {
					continue;
				}
			}

			// Check if we need to calculate parcel weight if weight was not pre-calculated.
			if ( 0 == $pre_calculated_weight ) {
				$parcel_weight += $product_data->get_weight() > 0 ? wc_get_weight( $product_data->get_weight(), 'kg', $shop_weight_unit ) * $product->get_quantity() : 0;
			}

			$items_quantity[ $product_data->get_id() ] = array(
				'item_description' => $product_data->get_name(),
				'item_price'       => number_format( $product->get_data()[ 'subtotal' ] / $product->get_quantity(), 2 ),
				'item_quantity'    => $product->get_quantity(),
				'item_weight'      => $product_data->get_weight(),
				'item_hscode'      => apply_filters( 'wgm_shipping_dhl_product_hscode', $product_data->get_meta( '_wgm_shipping_dhl_hscode' ), $product ),
				'item_coo'         => apply_filters( 'wgm_shipping_dhl_product_country_origin', $product_data->get_meta( '_wgm_shipping_dhl_country_origin' ), $product ),
			);
		}

		if ( 0 == $parcel_weight ) {
			$parcel_weight = self::$default_parcel_weight;
		}

		if ( $parcel_weight < self::$minimum_parcel_weight ) {
			$parcel_weight = self::$minimum_parcel_weight;
		}

		switch ( $labels_setting ) {
			default:
			case 1:
				/**
				 * Group all products in one delivery.
				 */
				$parcels[] = array(
					'weight'         => self::maybe_add_additional_parcel_weight( $parcel_weight ),
					'items'          => $products,
					'items_text'     => self::box_contains( $items_quantity ),
					'items_quantity' => $items_quantity,
				);
				break;
			case 2:
				/**
				 * Group same products in one delivery-
				 */
				$group_variants   = ( 'on' === $group_variants_setting ) ? true : false;
				$grouped_products = array();
				foreach ( $products as $product ) {
					$product_id   = $product->get_product_id();
					$product_data = $product->get_product();

					// If product is virtual, skip it
					if ( is_object( $product_data) && method_exists( $product_data, 'needs_shipping' ) ) {
						if ( ! $product_data->needs_shipping() ) {
							continue;
						}
					}

					if ( true === $group_variants ) {
						if ( true === apply_filters( 'wgm_shipping_skip_grouping_variants_for_product', false, $product_id, $product ) ) {
							$grouped_products[] = array( $product );
						} else {
							$grouped_products[ $product_id ][] = $product;
						}
					} else {
						if ( true === apply_filters( 'wgm_shipping_group_variants_for_product', false, $product_id, $product ) ) {
							$grouped_products[ $product_id ][] = $product;
						} else {
							$grouped_products[] = array( $product );
						}
					}

				}
				foreach ( $grouped_products as $products ) {
					$grouped_product_quantity_weight = 0;
					$items_quantity                  = array();
					foreach ( $products as $product ) {
						$product_data                     = $product->get_product();
						$grouped_product_quantity_weight += $product_data->get_weight() > 0 ? wc_get_weight( $product_data->get_weight(), 'kg', $shop_weight_unit ) * $product->get_quantity() : 0;

						if ( 0 == $grouped_product_quantity_weight ) {
							$grouped_product_quantity_weight = self::$default_parcel_weight;
						}

						if ( $grouped_product_quantity_weight < self::$minimum_parcel_weight ) {
							$grouped_product_quantity_weight = self::$minimum_parcel_weight;
						}

						$items_quantity[ $product_data->get_id() ] = array(
							'item_description' => $product_data->get_name(),
							'item_price'       => number_format( $product->get_data()[ 'subtotal' ] / $product->get_quantity(), 2 ),
							'item_quantity'    => $product->get_quantity(),
							'item_weight'      => $product_data->get_weight(),
							'item_hscode'      => apply_filters( 'wgm_shipping_dhl_product_hscode', $product_data->get_meta( '_wgm_shipping_dhl_hscode' ), $product ),
							'item_coo'         => apply_filters( 'wgm_shipping_dhl_product_country_origin', $product_data->get_meta( '_wgm_shipping_dhl_country_origin' ), $product ),
						);
					}
					$parcels[] = array(
						'weight'         => self::maybe_add_additional_parcel_weight( $grouped_product_quantity_weight ),
						'items'          => $grouped_products,
						'items_text'     => self::box_contains( $items_quantity ),
						'items_quantity' => $items_quantity,
					);
				}
				break;
			case 3:
				/**
				 * For each individual product a separate delivery.
				 */
				foreach ( $products as $product ) {
					$product_data = $product->get_product();

					// If product is virtual, skip it
					if ( is_object( $product_data) && method_exists( $product_data, 'needs_shipping' ) ) {
						if ( ! $product_data->needs_shipping() ) {
							continue;
						}
					}

					$product_item_weight   = $product_data->get_weight() > 0 ? wc_get_weight( $product_data->get_weight(), 'kg', $shop_weight_unit ) : 0;
					$product_item_quantity = $product->get_quantity();

					if ( 0 == $product_item_weight ) {
						$product_item_weight = self::$default_parcel_weight;
					}

					if ( $product_item_weight < self::$minimum_parcel_weight ) {
						$product_item_weight = self::$minimum_parcel_weight;
					}

					for ( $j = 0; $j < $product_item_quantity; $j++ ) {
						$parcels[] = array(
							'weight'         => self::maybe_add_additional_parcel_weight( $product_item_weight ),
							'items'          => $product,
							'items_text'     => self::box_contains( array(
								$product->get_id() => array(
									'item_description' => $product_data->get_name(),
									'item_quantity'    => 1,
								)
							) ),
							'items_quantity' => array(
								$product_data->get_id() => array(
									'item_description' => $product_data->get_name(),
									'item_price'       => number_format( $product->get_data()[ 'subtotal' ] / 1, 2 ),
									'item_quantity'    => 1,
									'item_weight'      => $product_data->get_weight(),
									'item_hscode'      => apply_filters( 'wgm_shipping_dhl_product_hscode', $product_data->get_meta( '_wgm_shipping_dhl_hscode' ), $product ),
									'item_coo'         => apply_filters( 'wgm_shipping_dhl_product_country_origin', $product_data->get_meta( '_wgm_shipping_dhl_country_origin' ), $product ),
								),
							),
						);
					}
				}
				break;
		}

		return apply_filters( 'wgm_shipping_native_calculated_parcels', $parcels, $parcel_distribution, $group_variants_setting, $products, $pre_calculated_weight );
	}

	/**
	 * Automatic box packaging calculation.
	 *
	 * @acces public
	 * @static
	 *
	 * @param WC_Order_Item[] $products WooCommerce order items
	 * @param array           $boxes available packaging boxes
	 * @param int             $parcel_distribution 'Parcel Distribution' setting
	 * @param string          $group_variants_setting 'Group Variants' setting
	 * @param float           $maximum_box_weight Maximum box/parcel weight
	 * @param string          $pre_calculated_weight (optional) pre-calculated weight
	 *
	 * @return array
	 * @throws ConvertorException
	 * @throws ConvertorDifferentTypeException
	 * @throws ConvertorInvalidUnitException
	 */
	public static function calculate_box_packaging( array $products, array $boxes, int $parcel_distribution, string $group_variants_setting, float $maximum_box_weight, $pre_calculated_weight = 0 ) : array {

		$parcel_distribution  = absint( $parcel_distribution );
		$final_boxes          = array();
		$box_carton_thickness = apply_filters( 'wgm_shipping_box_carton_thickness', 9 );
		$shop_weight_unit     = get_option( 'woocommerce_weight_unit', 'kg' );
		$shop_dimension_unit  = get_option( 'woocommerce_dimension_unit', 'cm' );

		/**
		 * Use native package box calculation if boxes are empty.
		 */
		if ( ( '4' != $parcel_distribution ) || empty( $boxes ) ) {
			return self::native_box_packaging_calculator( $parcel_distribution, $group_variants_setting, $products, $pre_calculated_weight );
		}

		/**
		 * Packing boxes depending on user settings.
		 */
		switch ( $parcel_distribution ) {
			default:
			case 1:
				/**
				 * Group all products in one delivery.
				 *
				 * Initialize Box Packer.
				 */
				$packer = new Packer();

				/**
				 * Adding packaging boxes.
				 */
				$weight_converter = new Convertor( $maximum_box_weight, 'kg' );

				foreach ( $boxes as $box ) {
					$packer->addBox(
						new Package_Box(
							$box[ 'name' ],
							$box[ 'outer_width' ],
							$box[ 'outer_length' ],
							$box[ 'outer_depth' ],
							$box[ 'empty_weight' ],
							$box[ 'outer_width' ] - ( 2 * $box_carton_thickness ),
							$box[ 'outer_length' ] - ( 2 * $box_carton_thickness ),
							$box[ 'outer_depth' ] - ( 2 * $box_carton_thickness ),
							( ( $box[ 'max_weight' ] > $weight_converter->to( 'g' ) ) && ( $weight_converter->to( 'g' ) > 0 ) ) ? $weight_converter->to( 'g' ) : $box[ 'max_weight' ],
						)
					);
				}

				/**
				 * Adding products / items.
				 */
				foreach ( $products as $product ) {

					$product_data = $product->get_product();

					// If product is virtual, skip it
					if ( is_object( $product_data) && method_exists( $product_data, 'needs_shipping' ) ) {
						if ( ! $product_data->needs_shipping() ) {
							continue;
						}
					}

					$product_width  = ! empty( $product_data->get_width() )  ? $product_data->get_width()  : 0;
					$product_length = ! empty( $product_data->get_length() ) ? $product_data->get_length() : 0;
					$product_height = ! empty( $product_data->get_height() ) ? $product_data->get_height() : 0;
					$product_weight = $product_data->get_weight() > 0 ? wc_get_weight( $product_data->get_weight(), 'g', $shop_weight_unit ) : 0;

					$width_converter  = new Convertor( $product_width, $shop_dimension_unit );
					$length_converter = new Convertor( $product_length, $shop_dimension_unit );
					$height_converter = new Convertor( $product_height, $shop_dimension_unit );

					$keepflat_option = get_post_meta( $product->get_id(), '_wgm_shipping_keepflat', true );
					$keepflat        = false;

					if ( ! empty( $keepflat_option ) ) {
						$keepflat = ( $keepflat_option === 'yes' );
					}

					$packer->addItem(
						new Package_Item(
							$product->get_name(),
							$width_converter->to( 'mm' ),
							$length_converter->to( 'mm' ),
							$height_converter->to( 'mm' ),
							$product_weight,
							$keepflat
						),
						$product->get_quantity()
					);
				}

				/**
				 * Calculating box packaging.
				 */
				$packed_boxes = $packer->pack();

				/**
				 * Fill final boxes array.
				 */
				foreach ( $packed_boxes as $packed_box ) {
					$box            = $packed_box->getBox();
					$packed_items   = $packed_box->getItems();
					$items_quantity = array();
					foreach ( $packed_items as $item ) {
						$product_name = $item->getItem()->getDescription();
						foreach ( $products as $product ) {
							if ( $product->get_name() == $product_name ) {
								$product_data   = $product->get_product();
								$product_id     = $product->get_id();
								$product_price  = $product_data->get_price();
								$product_weight = $product_data->get_weight();
								$product_hscode = apply_filters( 'wgm_shipping_dhl_product_hscode', $product_data->get_meta( '_wgm_shipping_dhl_hscode' ), $product );
								$product_coo    = apply_filters( 'wgm_shipping_dhl_product_country_origin', $product_data->get_meta( '_wgm_shipping_dhl_country_origin' ), $product );
								break;
							}
						}
						if ( empty( $items_quantity[ $product_id ] ) ) {
							$items_quantity[ $product_id ] = array(
								'item_description' => $product_name,
								'item_price'       => $product_price,
								'item_weight'      => $product_weight,
								'item_hscode'      => $product_hscode,
								'item_coo'         => $product_coo,
								'item_quantity'    => 1,
							);
						} else {
							$items_quantity[ $product_id ][ 'item_quantity' ] += 1;
						}
					}

					$width_converter  = new Convertor( $box->getOuterWidth(), 'mm' );
					$length_converter = new Convertor( $box->getOuterLength(), 'mm' );
					$height_converter = new Convertor( $box->getOuterDepth(), 'mm' );

					$final_boxes[] = array(
						'reference'      => $box->getReference(),
						'dimensions'     => wc_format_dimensions( array( $width_converter->to( $shop_dimension_unit ), $length_converter->to( $shop_dimension_unit ), $height_converter->to( $shop_dimension_unit ) ) ),
						'weight'         => self::maybe_add_additional_parcel_weight( wc_get_weight( $packed_box->getWeight(), 'kg', 'g' ) ),
						'items_text'     => self::box_contains( $items_quantity ),
						'items'          => $products,
						'items_quantity' => $items_quantity,
					);
				}

				break;
			case 2:
				/**
				 * Group same products in one delivery.
				 */
				$group_variants   = ( 'on' === $group_variants_setting );
				$grouped_products = array();
				foreach ( $products as $product ) {
					$product_id   = $product->get_product_id();
					$product_data = $product->get_product();

					// If product is virtual, skip it
					if ( is_object( $product_data) && method_exists( $product_data, 'needs_shipping' ) ) {
						if ( ! $product_data->needs_shipping() ) {
							continue;
						}
					}

					if ( true === $group_variants ) {
						if ( true === apply_filters( 'wgm_shipping_skip_grouping_variants_for_product', false, $product_id, $product ) ) {
							$grouped_products[] = array( $product );
						} else {
							$grouped_products[ $product_id ][] = $product;
						}
					} else {
						if ( true === apply_filters( 'wgm_shipping_group_variants_for_product', false, $product_id, $product ) ) {
							$grouped_products[ $product_id ][] = $product;
						} else {
							$grouped_products[] = array( $product );
						}
					}

				}
				foreach ( $grouped_products as $products ) {
					/**
					 * Initialize Box Packer.
					 */
					$packer = new Packer();

					/**
					 * Adding packaging boxes.
					 */
					$weight_converter = new Convertor( $maximum_box_weight, 'kg' );

					foreach ( $boxes as $box ) {
						$packer->addBox(
							new Package_Box(
								$box[ 'name' ],
								$box[ 'outer_width' ],
								$box[ 'outer_length' ],
								$box[ 'outer_depth' ],
								$box[ 'empty_weight' ],
								$box[ 'outer_width' ] - ( 2 * $box_carton_thickness ),
								$box[ 'outer_length' ] - ( 2 * $box_carton_thickness ),
								$box[ 'outer_depth' ] - ( 2 * $box_carton_thickness ),
								( ( $box[ 'max_weight' ] > $weight_converter->to( 'g' ) ) && ( $weight_converter->to( 'g' ) > 0 ) ) ? $weight_converter->to( 'g' ) : $box[ 'max_weight' ],
							)
						);
					}

					/**
					 * Adding products.
					 */
					foreach ( $products as $product ) {
						$product_data     = $product->get_product();
						$width_converter  = new Convertor( $product_data->get_width(), $shop_dimension_unit );
						$length_converter = new Convertor( $product_data->get_length(), $shop_dimension_unit );
						$height_converter = new Convertor( $product_data->get_height(), $shop_dimension_unit );

						$keepflat_option = get_post_meta( $product->get_id(), '_wgm_shipping_keepflat', true );
						$keepflat        = false;

						if ( ! empty( $keepflat_option ) ) {
							$keepflat = ( $keepflat_option === 'yes' );
						}

						$packer->addItem(
							new Package_Item(
								$product_data->get_title(),
								$width_converter->to( 'mm' ),
								$length_converter->to( 'mm' ),
								$height_converter->to( 'mm' ),
								$product_data->get_weight() > 0 ? wc_get_weight( $product_data->get_weight(), 'g', $shop_weight_unit ) : 0,
								$keepflat
							),
							$product->get_quantity()
						);
					}

					/**
					 * Calculating box packaging.
					 */
					$packed_boxes = $packer->pack();

					/**
					 * Fill final boxes array.
					 */
					foreach ( $packed_boxes as $packed_box ) {
						$box            = $packed_box->getBox();
						$packed_items   = $packed_box->getItems();
						$items_quantity = array();
						foreach ( $packed_items as $item ) {
							$product_name = $item->getItem()->getDescription();
							foreach ( $products as $product ) {
								if ( $product->get_name() == $product_name ) {
									$product_data   = $product->get_product();
									$product_id     = $product->get_id();
									$product_price  = $product_data->get_price();
									$product_weight = $product_data->get_weight();
									$product_hscode = apply_filters( 'wgm_shipping_dhl_product_hscode', $product_data->get_meta( '_wgm_shipping_dhl_hscode' ), $product );
									$product_coo    = apply_filters( 'wgm_shipping_dhl_product_country_origin', $product_data->get_meta( '_wgm_shipping_dhl_country_origin' ), $product );
									break;
								}
							}
							if ( empty( $items_quantity[ $product_id ] ) ) {
								$items_quantity[ $product_id ] = array(
									'item_description' => $product_name,
									'item_price'       => $product_price,
									'item_weight'      => $product_weight,
									'item_hscode'      => $product_hscode,
									'item_coo'         => $product_coo,
									'item_quantity'    => 1,
								);
							} else {
								$items_quantity[ $product_id ][ 'item_quantity' ] += 1;
							}
						}

						$width_converter  = new Convertor( $box->getOuterWidth(), 'mm' );
						$length_converter = new Convertor( $box->getOuterLength(), 'mm' );
						$height_converter = new Convertor( $box->getOuterDepth(), 'mm' );

						$final_boxes[] = array(
							'reference'      => $box->getReference(),
							'dimensions'     => wc_format_dimensions( array( $width_converter->to( $shop_dimension_unit ), $length_converter->to( $shop_dimension_unit ), $height_converter->to( $shop_dimension_unit ) ) ),
							'weight'         => self::maybe_add_additional_parcel_weight( wc_get_weight( $packed_box->getWeight(), 'kg', 'g' ) ),
							'items_text'     => self::box_contains( $items_quantity ),
							'items'          => $packed_items,
							'items_quantity' => $items_quantity,
						);
					}
				}

				break;
			case 3:
				/**
				 * For each individual product a separate delivery.
				 */

				foreach ( $products as $product ) {
					$product_data = $product->get_product();

					// If product is virtual, skip it
					if ( is_object( $product_data) && method_exists( $product_data, 'needs_shipping' ) ) {
						if ( ! $product_data->needs_shipping() ) {
							continue;
						}
					}

					$product_item_quantity = $product->get_quantity();

					for ( $j = 0; $j < $product_item_quantity; $j++ ) {
						/**
						 * Initialize Box Packer.
						 */
						$packer = new Packer();

						/**
						 * Adding packaging boxes.
						 */
						$weight_converter = new Convertor( $maximum_box_weight, 'kg' );

						foreach ( $boxes as $box ) {
							$packer->addBox(
								new Package_Box(
									$box[ 'name' ],
									$box[ 'outer_width' ],
									$box[ 'outer_length' ],
									$box[ 'outer_depth' ],
									$box[ 'empty_weight' ],
									$box[ 'outer_width' ] - ( 2 * $box_carton_thickness ),
									$box[ 'outer_length' ] - ( 2 * $box_carton_thickness ),
									$box[ 'outer_depth' ] - ( 2 * $box_carton_thickness ),
									( ( $box[ 'max_weight' ] > $weight_converter->to( 'g' ) ) && ( $weight_converter->to( 'g' ) > 0 ) ) ? $weight_converter->to( 'g' ) : $box[ 'max_weight' ],
								)
							);
						}

						$width_converter  = new Convertor( $product_data->get_width(), $shop_dimension_unit );
						$length_converter = new Convertor( $product_data->get_length(), $shop_dimension_unit );
						$height_converter = new Convertor( $product_data->get_height(), $shop_dimension_unit );

						$keepflat_option = get_post_meta( $product->get_id(), '_wgm_shipping_keepflat', true );
						$keepflat        = false;

						if ( ! empty( $keepflat_option ) ) {
							$keepflat = ( $keepflat_option === 'yes' );
						}

						$packer->addItem(
							new Package_Item(
								$product_data->get_title(),
								$width_converter->to( 'mm' ),
								$length_converter->to( 'mm' ),
								$height_converter->to( 'mm' ),
								$product_data->get_weight() > 0 ? wc_get_weight( $product_data->get_weight(), 'g', $shop_weight_unit ) : 0,
								$keepflat
							)
						);

						/**
						 * Calculating box packaging.
						 */
						$packed_boxes = $packer->pack();

						/**
						 * Fill final boxes array.
						 */
						foreach ( $packed_boxes as $packed_box ) {
							$box          = $packed_box->getBox();
							$packed_items = $packed_box->getItems();
							$items_quantity = array();
							foreach ( $packed_items as $item ) {
								$product_name = $item->getItem()->getDescription();
								foreach ( $products as $product ) {
									if ( $product->get_name() == $product_name ) {
										$product_data   = $product->get_product();
										$product_id     = $product->get_id();
										$product_price  = $product_data->get_price();
										$product_weight = $product_data->get_weight();
										$product_hscode = apply_filters( 'wgm_shipping_dhl_product_hscode', $product_data->get_meta( '_wgm_shipping_dhl_hscode' ), $product );
										$product_coo    = apply_filters( 'wgm_shipping_dhl_product_country_origin', $product_data->get_meta( '_wgm_shipping_dhl_country_origin' ), $product );
										break;
									}
								}
								if ( empty( $items_quantity[ $product_id ] ) ) {
									$items_quantity[ $product_id ] = array(
										'item_description' => $product_name,
										'item_price'       => $product_price,
										'item_weight'      => $product_weight,
										'item_hscode'      => $product_hscode,
										'item_coo'         => $product_coo,
										'item_quantity'    => 1,
									);
								} else {
									$items_quantity[ $product_id ][ 'item_quantity' ] += 1;
								}
							}

							$width_converter  = new Convertor( $box->getOuterWidth(), 'mm' );
							$length_converter = new Convertor( $box->getOuterLength(), 'mm' );
							$height_converter = new Convertor( $box->getOuterDepth(), 'mm' );

							$final_boxes[] = array(
								'reference'      => $box->getReference(),
								'dimensions'     => wc_format_dimensions( array( $width_converter->to( $shop_dimension_unit ), $length_converter->to( $shop_dimension_unit ), $height_converter->to( $shop_dimension_unit ) ) ),
								'weight'         => self::maybe_add_additional_parcel_weight( wc_get_weight( $packed_box->getWeight(), 'kg', 'g' ) ),
								'items_text'     => self::box_contains( $items_quantity ),
								'items'          => $packed_items,
								'items_quantity' => $items_quantity,
							);
						}
					}
				}

				break;
		}

		return apply_filters( 'wgm_shipping_calculated_parcels', $final_boxes, $products, $boxes, $parcel_distribution, $group_variants_setting, $maximum_box_weight, $pre_calculated_weight );
	}

	/**
	 * Check if cart contains too heavyweight products for shipping.
	 *
	 * @static
	 *
	 * @param int   $parcel_distribution option from settings page
	 * @param float $limit parcel weight limit in kg
	 *
	 * @return bool
	 */
	public static function check_cart_for_parcel_distribution( int $parcel_distribution, $limit = 30 ) : bool {

		$available        = true;
		$shop_weight_unit = get_option( 'woocommerce_weight_unit', 'kg' );

		switch( $parcel_distribution ) {
			default:
			case 1:
				// all products in one parcel
				$cart_weight    = WC()->cart === null ? 0 : WC()->cart->get_cart_contents_weight();
				$cart_weight_kg = wc_get_weight( $cart_weight, 'kg', $shop_weight_unit );
				if ( $cart_weight_kg > $limit ) {
					$available = false;
				}
				break;
			case 2:
			case 3:
				// checking if cart contains a heavyweight product
				if ( null !== WC()->cart ) {
					$cart_items = WC()->cart->get_cart();
					foreach ( $cart_items as $cart_item_key => $cart_item ) {
						$product_data = $cart_item[ 'data' ];
						$product_weight = $product_data->get_weight() > 0 ? wc_get_weight( $product_data->get_weight(), 'kg', $shop_weight_unit ) : 0;
						if ( $product_weight > $limit ) {
							$available = false;
						}
					}
				}
				break;
		}

		return $available;
	}

	/**
	 * Maybe add additional weight to parcel weight.
	 *
	 * @param float $parcel_weight
	 *
	 * @return float
	 */
	public static function maybe_add_additional_parcel_weight( float $parcel_weight ) : float {

		if ( ! empty( self::$additional_parcel_weight ) && ( '0' != self::$additional_parcel_weight ) ) {
			if ( strpos( self::$additional_parcel_weight, '%' ) ) {
				$percent = substr( self::$additional_parcel_weight, 0, strpos( self::$additional_parcel_weight, '%' ) );
				$percent = trim( $percent );
				if ( floatval( $percent ) > 0 ) {
					$parcel_weight += ( $parcel_weight * floatval( $percent ) / 100 );
				}
			} else {
				$parcel_weight += floatval( self::$additional_parcel_weight );
			}
		}

		return $parcel_weight;
	}

	/**
	 * Returns items in parcel box.
	 *
	 * @param array $items
	 *
	 * @return string
	 */
	public static function box_contains( array $items ) : string {

		$box_contains = array();
		foreach ( $items as $product_id => $item ) {
			$box_contains[] = $item[ 'item_quantity' ] . ' x ' . $item[ 'item_description' ];
		}

		return implode( ', ', $box_contains );
	}
}
