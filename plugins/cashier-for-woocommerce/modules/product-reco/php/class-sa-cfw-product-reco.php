<?php
/**
 * Main class for Product Reco
 *
 * @package     cashier/includes/
 *  author      StoreApps
 * @since       1.0.0
 * @version     1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SA_CFW_Product_Reco' ) ) {

	/**
	 *  Main Product Reco Class.
	 *
	 * @return object of SA_CFW_Product_Reco having all functionality of Product Reco
	 */
	class SA_CFW_Product_Reco {

		/**
		 * Variable to hold instance of Product Reco
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Get single instance of Product Reco.
		 *
		 * @return SA_CFW_Product_Reco Singleton object of SA_CFW_Product_Reco
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
			add_action( 'wp_loaded', array( $this, 'create_tables' ) );

			add_filter( 'woocommerce_related_products', array( $this, 'related_products' ), 99, 3 );
			add_filter( 'woocommerce_product_get_upsell_ids', array( $this, 'upsells' ), 99, 2 );
			add_filter( 'woocommerce_product_get_cross_sell_ids', array( $this, 'cross_sells' ), 99, 2 );
			add_filter( 'woocommerce_cart_crosssell_ids', array( $this, 'cart_cross_sells' ), 99, 2 );
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
		 * Recommend related products
		 *
		 * @param mixed   $related_product_ids Related product ids.
		 * @param integer $product_id The product id.
		 * @param array   $args Additional arguments.
		 * @return mixed
		 */
		public function related_products( $related_product_ids = null, $product_id = 0, $args = array() ) {
			$recommended = $this->get_recommended_segment( 'related' );
			if ( ! empty( $recommended[ $product_id ] ) ) {
				return $recommended[ $product_id ];
			}
			return $related_product_ids;
		}

		/**
		 * Recommend upsells
		 *
		 * @param mixed   $upsells The upsells.
		 * @param integer $product The product object.
		 * @return mixed
		 */
		public function upsells( $upsells = array(), $product = null ) {
			$product_id  = ( is_object( $product ) && is_callable( array( $product, 'get_id' ) ) ) ? $product->get_id() : 0;
			$recommended = $this->get_recommended_segment( 'upsells' );
			if ( ! empty( $recommended[ $product_id ] ) ) {
				return $recommended[ $product_id ];
			}
			return $upsells;
		}

		/**
		 * Recommend cross sells
		 *
		 * @param mixed   $cross_sells The cross sells.
		 * @param integer $product The product object.
		 * @return mixed
		 */
		public function cross_sells( $cross_sells = array(), $product = null ) {
			$product_id  = ( is_object( $product ) && is_callable( array( $product, 'get_id' ) ) ) ? $product->get_id() : 0;
			$recommended = $this->get_recommended_segment( 'cross_sells' );
			if ( ! empty( $recommended[ $product_id ] ) ) {
				return $recommended[ $product_id ];
			}
			return $cross_sells;
		}

		/**
		 * Recommend cart cross sells
		 *
		 * @param mixed   $cross_sells The cross sells.
		 * @param integer $cart The cart.
		 * @return mixed
		 */
		public function cart_cross_sells( $cross_sells = array(), $cart = null ) {
			$recommended = $this->get_recommended_segment( 'cross_sells' );
			if ( ! empty( $recommended ) ) {
				foreach ( $recommended as $recos ) {
					if ( ! empty( $recos ) ) {
						return $recos;
					}
				}
			}
			return $cross_sells;
		}

		/**
		 * Get product recommendations from transient
		 *
		 * @param integer $id The product id.
		 * @return array
		 */
		public function get_transient( $id = 0 ) {
			if ( empty( $id ) ) {
				return array();
			}
			$transient_prefix = 'sa_cfw_product_recommentations_';
			$transient_name   = $transient_prefix . $id;
			$transient        = get_transient( $transient_name );
			return $transient;
		}

		/**
		 * Set product recommendations in transient
		 *
		 * @param integer $id The product id.
		 * @param array   $transient The transient value to be set.
		 */
		public function set_transient( $id = 0, $transient = array() ) {
			if ( empty( $id ) ) {
				return;
			}
			$transient_prefix = 'sa_cfw_product_recommentations_';
			$transient_name   = $transient_prefix . $id;
			$transient_expiry = apply_filters( 'sa_cfw_product_recommendations_transient_expiry', WEEK_IN_SECONDS, array( 'source' => $this ) );
			set_transient( $transient_name, $transient, $transient_expiry );
		}

		/**
		 * Get available slots
		 *
		 * @param string $for The segment.
		 * @return integer
		 */
		public function get_available_slots( $for = '' ) {
			if ( ! empty( $for ) ) {
				$available_slots = 0;
				$columns         = wc_get_loop_prop( 'columns' );
				switch ( $for ) {
					case 'upsells':
						$available_slots = apply_filters( 'woocommerce_upsells_columns', $columns );
						break;
					case 'cross_sells':
						$available_slots = apply_filters( 'woocommerce_cross_sells_columns', $columns );
						break;
					case 'related':
						$available_slots = apply_filters( 'woocommerce_related_products_columns', $columns );
						break;
				}
				return absint( $available_slots );
			}
			return 0;
		}

		/**
		 * Get recommended segment
		 *
		 * @param string $segment The segment id.
		 * @return array
		 */
		public function get_recommended_segment( $segment = '' ) {
			if ( ! empty( $segment ) && in_array( $segment, array( 'upsells', 'cross_sells', 'related', 'fbt' ), true ) ) {
				$source_product_ids          = $this->get_recommendation_source();
				$available_slots             = $this->get_available_slots( $segment );
				$is_generate_recommendations = true;
				$is_force_fbt                = apply_filters( 'sa_cfw_is_force_fbt', wc_string_to_bool( get_option( 'sa_cfw_is_force_fbt', 'yes' ) ), array( 'source' => $this ) );
				if ( ! empty( $source_product_ids ) ) {
					$generate_recommendations_for = array();
					$found_products               = array();
					foreach ( $source_product_ids as $id ) {
						$recommendations = $this->get_transient( $id );
						if ( false === $recommendations ) {
							$generate_recommendations_for[] = $id;
							continue;
						}
						if ( empty( $found_products[ $id ] ) || ! is_array( $found_products[ $id ] ) ) {
							$found_products[ $id ] = array();
						}
						$found_products[ $id ] = ( ! empty( $recommendations[ $segment ] ) ) ? array_merge( $found_products[ $id ], $recommendations[ $segment ] ) : $found_products[ $id ];
						if ( $available_slots <= count( $found_products[ $id ] ) ) {
							$is_generate_recommendations = false;
							break;
						}
						if ( in_array( $segment, array( 'upsells', 'related' ), true ) && true === $is_force_fbt && ! empty( $recommendations['fbt'] ) ) {
							$found_products[ $id ] = array_merge( $found_products[ $id ], $recommendations['fbt'] );
							if ( $available_slots <= count( $found_products[ $id ] ) ) {
								$is_generate_recommendations = false;
								break;
							}
						}
					}
					if ( $is_generate_recommendations && ! empty( $generate_recommendations_for ) ) {
						$generated_recommendations = $this->generate_recommendations( $generate_recommendations_for );
						foreach ( $generated_recommendations as $id => $recommendations ) {
							if ( empty( $found_products[ $id ] ) || ! is_array( $found_products[ $id ] ) ) {
								$found_products[ $id ] = array();
							}
							$found_products[ $id ] = ( ! empty( $recommendations[ $segment ] ) ) ? array_merge( $found_products[ $id ], $recommendations[ $segment ] ) : $found_products[ $id ];
							if ( $available_slots <= count( $found_products[ $id ] ) ) {
								break;
							}
							if ( in_array( $segment, array( 'upsells', 'related' ), true ) && true === $is_force_fbt && ! empty( $recommendations['fbt'] ) ) {
								$found_products[ $id ] = array_merge( $found_products[ $id ], $recommendations['fbt'] );
								if ( $available_slots <= count( $found_products[ $id ] ) ) {
									break;
								}
							}
						}
					}
					if ( ! empty( $found_products ) ) {
						foreach ( $found_products as $id => $products ) {
							if ( $available_slots < count( $products ) ) {
								$found_products[ $id ] = array_slice( $products, 0, $available_slots );
							}
						}
						if ( ! empty( $found_products ) ) {
							return $found_products;
						}
					}
				}
			}
			return array();
		}

		/**
		 * Generate recommendations
		 *
		 * @param array $ids The products ids.
		 * @return array
		 */
		public function generate_recommendations( $ids = array() ) {
			if ( empty( $ids ) ) {
				return;
			}
			$recommendations           = $this->get_product_recommendations( $ids );
			$generated_recommendations = array();
			if ( ! empty( $recommendations ) ) {
				foreach ( $ids as $id ) {
					$transient                        = array(
						'upsells'     => ( ! empty( $recommendations['upsells'][ $id ] ) ) ? $recommendations['upsells'][ $id ] : array(),
						'cross_sells' => ( ! empty( $recommendations['cross_sells'][ $id ] ) ) ? $recommendations['cross_sells'][ $id ] : array(),
						'related'     => ( ! empty( $recommendations['related'][ $id ] ) ) ? $recommendations['related'][ $id ] : array(),
						'fbt'         => ( ! empty( $recommendations['fbt'][ $id ] ) ) ? $recommendations['fbt'][ $id ] : array(),
					);
					$generated_recommendations[ $id ] = $transient;
					$this->set_transient( $id, $transient );
				}
			}
			return $generated_recommendations;
		}

		/**
		 * Get Product Recommendations
		 *
		 * @param array $source_product_ids The source product ids for which recommendations to be found.
		 * @return array
		 */
		public function get_product_recommendations( $source_product_ids = array() ) {
			global $wpdb;

			if ( empty( $source_product_ids ) ) {
				return array();
			}

			$unique_id = $this->get_unique_id();

			$option_source_product_ids = 'sa_cfw_reco_source_product_ids_' . $unique_id;

			update_option( $option_source_product_ids, implode( ',', $source_product_ids ), 'no' );

			$current_timestamp = microtime( true ) * 10000;
			$order_count       = apply_filters( 'sa_cfw_max_orders_to_scan', get_option( 'sa_cfw_max_orders_to_scan', 200 ), array( 'source' => $this ) );

			$wpdb->query( // phpcs:ignore
				$wpdb->prepare( // phpcs:ignore
					"INSERT INTO {$wpdb->prefix}sa_cfw_fbt_temp( order_id, timestamp )
						SELECT DISTINCT(oi.order_id) as order_id,
							%d AS timestamp
						FROM {$wpdb->prefix}woocommerce_order_itemmeta as oim1
							JOIN {$wpdb->prefix}woocommerce_order_items as oi
								ON(oi.order_item_id = oim1.order_item_id
									AND oi.order_item_type = %s
									AND oim1.meta_key = %s
									AND FIND_IN_SET (oim1.meta_value, (SELECT option_value
																			FROM {$wpdb->prefix}options
																			WHERE option_name = %s)))
						ORDER BY oi.order_id DESC
						LIMIT %d",
					$current_timestamp,
					'line_item',
					'_product_id',
					$option_source_product_ids,
					$order_count
				)
			);

			$results = $wpdb->get_results( // phpcs:ignore
				$wpdb->prepare( // phpcs:ignore
					"SELECT product_id,
							fbt_product_id,
							count(combinations) as freq
						FROM (
								SELECT oim1.meta_value as product_id,
										oim2.meta_value as fbt_product_id,
										concat(oim1.meta_value,'_',oim2.meta_value) as combinations
									FROM {$wpdb->prefix}woocommerce_order_itemmeta as oim1
									JOIN (
											SELECT oi1.order_item_id as item_id,
													oi2.order_item_id as fbt_item_id,
													concat(oi1.order_item_id,'_',oi2.order_item_id) as combinations
											FROM {$wpdb->prefix}woocommerce_order_items as oi1 
											JOIN {$wpdb->prefix}woocommerce_order_items as oi2
												ON (oi2.order_id = oi1.order_id
													AND oi2.order_item_type = oi1.order_item_type
													AND oi2.order_item_id != oi1.order_item_id
													and oi1.order_item_type = %s)	
											WHERE oi1.order_id IN ( SELECT order_id
																	FROM {$wpdb->prefix}sa_cfw_fbt_temp 
																	WHERE timestamp = %d )
										) as ordermeta
										ON( (oim1.order_item_id = ordermeta.item_id)
											AND oim1.meta_key = %s)
									JOIN {$wpdb->prefix}woocommerce_order_itemmeta as oim2
										ON( (oim2.order_item_id = ordermeta.fbt_item_id)
											AND oim2.meta_key = %s)
							) as temp
						WHERE FIND_IN_SET (product_id, (SELECT option_value
															FROM {$wpdb->prefix}options
															WHERE option_name = %s))
						GROUP BY product_id, combinations
						HAVING freq > %d
						ORDER BY freq DESC",
					'line_item',
					$current_timestamp,
					'_product_id',
					'_product_id',
					$option_source_product_ids,
					1
				),
				ARRAY_A
			);

			$frequently_together = array();

			if ( ! empty( $results ) ) {
				foreach ( $results as $result ) {
					$product_id = ( ! empty( $result['product_id'] ) ) ? absint( $result['product_id'] ) : 0;
					$fbt_id     = ( ! empty( $result['fbt_product_id'] ) ) ? absint( $result['fbt_product_id'] ) : 0;
					if ( empty( $product_id ) || empty( $fbt_id ) ) {
						continue;
					}
					if ( empty( $frequently_together[ $product_id ] ) || ! is_array( $frequently_together[ $product_id ] ) ) {
						$frequently_together[ $product_id ] = array();
					}
					$frequently_together[ $product_id ][] = $fbt_id;
				}
			}

			$wpdb->query( // phpcs:ignore
				$wpdb->prepare( // phpcs:ignore
					"DELETE FROM {$wpdb->prefix}sa_cfw_fbt_temp 
						WHERE timestamp = %d",
					$current_timestamp
				)
			);

			$related_products = $wpdb->get_results( // phpcs:ignore
				$wpdb->prepare( // phpcs:ignore
					"SELECT tr1.object_id AS source,
							tr2.object_id AS related
						FROM {$wpdb->prefix}term_relationships AS tr1
							JOIN {$wpdb->prefix}term_relationships AS tr2
								ON (tr1.term_taxonomy_id = tr2.term_taxonomy_id)
						WHERE tr2.term_taxonomy_id IN (
														SELECT tr.term_taxonomy_id AS ttid
																	FROM {$wpdb->prefix}term_relationships AS tr
																	JOIN {$wpdb->prefix}term_taxonomy AS tt
																		ON (tt.term_taxonomy_id = tr.term_taxonomy_id 
																			AND tt.taxonomy = %s
																			AND FIND_IN_SET (tr.object_id, (SELECT option_value
																												FROM {$wpdb->prefix}options
																												WHERE option_name = %s)))
														)
							AND FIND_IN_SET (tr1.object_id, (SELECT option_value
																FROM {$wpdb->prefix}options
																WHERE option_name = %s))
							AND NOT FIND_IN_SET (tr2.object_id, (SELECT option_value
																FROM {$wpdb->prefix}options
																WHERE option_name = %s))",
					'product_cat',
					$option_source_product_ids,
					$option_source_product_ids,
					$option_source_product_ids
				),
				ARRAY_A
			);

			$related = array();

			if ( ! empty( $related_products ) ) {
				foreach ( $related_products as $related_product ) {
					$product_id = ( ! empty( $related_product['source'] ) ) ? absint( $related_product['source'] ) : 0;
					$fbt_id     = ( ! empty( $related_product['related'] ) ) ? absint( $related_product['related'] ) : 0;
					if ( empty( $product_id ) || empty( $fbt_id ) ) {
						continue;
					}
					if ( empty( $related[ $product_id ] ) || ! is_array( $related[ $product_id ] ) ) {
						$related[ $product_id ] = array();
					}
					$related[ $product_id ][] = $fbt_id;
				}
			}

			$results = $wpdb->get_results( // phpcs:ignore
				$wpdb->prepare( // phpcs:ignore
					"SELECT post_id,
							meta_key,
							meta_value
						FROM {$wpdb->postmeta}
						WHERE FIND_IN_SET (post_id, (SELECT option_value
														FROM {$wpdb->prefix}options
														WHERE option_name = %s))
							AND meta_key IN (%s,%s)
							AND meta_value IS NOT NULL
							AND meta_value != %s
							AND meta_value != %s",
					$option_source_product_ids,
					'_upsell_ids',
					'_crosssell_ids',
					'',
					'a:0:{}'
				),
				ARRAY_A
			);

			$upsell     = array();
			$cross_sell = array();

			if ( ! empty( $results ) ) {
				foreach ( $results as $result ) {
					$product_id = ( ! empty( $result['post_id'] ) ) ? absint( $result['post_id'] ) : 0;
					$meta_key   = ( ! empty( $result['meta_key'] ) ) ? $result['meta_key'] : '';
					$meta_value = ( ! empty( $result['meta_value'] ) ) ? $result['meta_value'] : '';
					switch ( $meta_key ) {
						case '_upsell_ids':
							if ( empty( $upsell[ $product_id ] ) || ! is_array( $upsell[ $product_id ] ) ) {
								$upsell[ $product_id ] = array();
							}
							$upsell[ $product_id ] = ( ! empty( $meta_value ) && is_serialized( $meta_value ) ) ? maybe_unserialize( $meta_value ) : array();
							break;
						case '_crosssell_ids':
							if ( empty( $cross_sell[ $product_id ] ) || ! is_array( $cross_sell[ $product_id ] ) ) {
								$cross_sell[ $product_id ] = array();
							}
							$cross_sell[ $product_id ] = ( ! empty( $meta_value ) && is_serialized( $meta_value ) ) ? maybe_unserialize( $meta_value ) : array();
							break;
					}
				}
			}

			$recommendations = array(
				'upsells'     => $upsell,
				'cross_sells' => $cross_sell,
				'related'     => $related,
				'fbt'         => $frequently_together,
			);

			delete_option( $option_source_product_ids );

			return $recommendations;

		}

		/**
		 * Get products and/or categories for which the recommendation will be fetched
		 *
		 * @return array
		 */
		public function get_recommendation_source() {
			$source_product_ids = array();
			if ( is_product() ) {
				$source_product_ids[] = get_the_ID();
			}
			$cart = ( is_object( WC() ) && isset( WC()->cart ) ) ? WC()->cart : null;
			if ( is_object( $cart ) && is_callable( array( $cart, 'is_empty' ) ) && ! $cart->is_empty() ) {
				$cart_contents      = ( is_callable( array( $cart, 'get_cart' ) ) ) ? $cart->get_cart() : array();
				$cart_product_ids   = wp_list_pluck( $cart_contents, 'product_id' );
				$source_product_ids = array_unique( array_merge( $source_product_ids, array_values( $cart_product_ids ) ) );
			}
			return $source_product_ids;
		}

		/**
		 * Function to create tables
		 */
		public function create_tables() {
			global $wpdb;

			$is_activated   = get_transient( 'sa_cfw_activated' );
			$current_module = strtolower( __CLASS__ );

			if ( is_array( $is_activated ) && ! in_array( $current_module, $is_activated, true ) ) {
				$collate = '';

				if ( $wpdb->has_cap( 'collation' ) ) {
					if ( ! empty( $wpdb->charset ) ) {
						$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
					}
					if ( ! empty( $wpdb->collate ) ) {
						$collate .= " COLLATE $wpdb->collate";
					}
				}

				if ( ! function_exists( 'maybe_create_table' ) ) {
					include_once ABSPATH . 'wp-admin/includes/upgrade.php';
				}

				$table_name = $wpdb->prefix . 'sa_cfw_fbt_temp';

				$create_table_query = "
										CREATE TABLE {$table_name} (
											order_id bigint(20) NOT NULL,
											timestamp bigint(20) NOT NULL
										) $collate;
										";
				$is_created         = maybe_create_table( $table_name, $create_table_query );

				if ( true === $is_created ) {
					$is_activated[] = $current_module;
					set_transient( 'sa_cfw_activated', $is_activated );
				}
			}

		}

		/**
		 * Generate & get an unique id
		 *
		 * @return string
		 */
		public function get_unique_id() {
			$user_id = get_current_user_id();
			if ( empty( $user_id ) ) {
				$user_id = ( ! empty( $_COOKIE['PHPSESSID'] ) ) ? wc_clean( wp_unslash( $_COOKIE['PHPSESSID'] ) ) : ''; // phpcs:ignore
				if ( ! empty( $user_id ) && 100 < strlen( $user_id ) ) {
					$user_id = substr( $user_id, 0, 100 );
				}
			}
			$unique_id = uniqid( $user_id, true );
			return $unique_id;
		}

		/**
		 * Function to log messages generated by Product Reco plugin
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

SA_CFW_Product_Reco::get_instance();
