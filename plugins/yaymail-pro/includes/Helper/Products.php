<?php

namespace YayMail\Helper;

class Products {

	const TYPE      = 'newest';
	const SORTED_BY = 'none';
	const LIMIT     = '5';

	public static function get_products_by_filter( $filter ) {
		$type        = isset( $filter['type'] ) ? $filter['type'] : TYPE;
		$sorted_by   = isset( $filter['sorted_by'] ) ? $filter['sorted_by'] : SORTED_BY;
		$limit       = isset( $filter['limit'] ) ? $filter['limit'] : LIMIT;
		$categories  = isset( $filter['categories'] ) ? $filter['categories'] : array();
		$tags        = isset( $filter['tags'] ) ? $filter['tags'] : array();
		$product_ids = isset( $filter['product_ids'] ) ? $filter['product_ids'] : array();
		switch ( $type ) {
			case 'newest':
				$products = self::get_newest_products( $sorted_by );
				break;
			case 'onsale':
				$products = self::get_on_sale_products( $sorted_by );
				break;
			case 'featured':
				$products = self::get_featured_products( $sorted_by );
				break;
			case 'categories':
				$products = self::get_products_by_categories( $sorted_by, $categories );
				break;
			case 'tags':
				$products = self::get_products_by_tags( $sorted_by, $tags );
				break;
			case 'products':
				$products = self::get_products_by_list_id( $sorted_by, $product_ids );
				break;
			default:
				$products = array();
				break;
		}
		if ( 'random' === $sorted_by ) {
			$products = self::randomize_products( $products, $limit );
		}
		$products = self::get_limit_products( $products, $limit );
		return $products;
	}
	public static function get_newest_products( $sorted_by = SORTED_BY ) {
		$query    = new \WC_Product_Query(
			array(
				'limit'   => '-1',
				'orderby' => 'date',
				'order'   => 'ASC',
				'return'  => 'ids',
			)
		);
		$products = $query->get_products();
		$products = self::get_products_information( $products );
		if ( 'none' !== $sorted_by ) {
			$products = self::sort_products( $products, $sorted_by );
		}
		return $products;
	}

	public static function get_products_by_list_id( $sorted_by = SORTED_BY, $list_id = array() ) {
		$products = self::get_products_information( $list_id );
		if ( 'none' !== $sorted_by ) {
			$products = self::sort_products( $products, $sorted_by );
		}
		return $products;
	}

	public static function get_on_sale_products( $sorted_by = SORTED_BY ) {
		$args            = self::get_base_query_args();
		$meta_query_args = array(
			'meta_query' => array(
				'relation' => 'OR',
				array( // Simple products type
					'key'     => '_sale_price',
					'value'   => 0,
					'compare' => '>',
					'type'    => 'numeric',
				),
				array( // Variable products type
					'key'     => '_min_variation_sale_price',
					'value'   => 0,
					'compare' => '>',
					'type'    => 'numeric',
				),
			),
		);

		$args = array_merge( $args, $meta_query_args, self::get_order_by_args( $sorted_by ) );

		$query    = new \WP_QUERY( $args );
		$products = array();
		if ( $query->have_posts() ) {
			$products = $query->posts;
		}
		$products = self::get_products_information( $products );
		return $products;
	}

	public static function get_featured_products( $sorted_by = SORTED_BY ) {
		$args           = self::get_base_query_args();
		$tax_query_args = array(
			'tax_query' => array(
				array(
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => 'featured',
					'operator' => 'IN',
				),
			),
		);
		$args           = array_merge( $args, $tax_query_args, self::get_order_by_args( $sorted_by ) );

		$query    = new \WP_QUERY( $args );
		$products = array();
		if ( $query->have_posts() ) {
			$products = $query->posts;
		}
		$products = self::get_products_information( $products );
		return $products;
	}

	public static function get_products_by_categories( $sorted_by = SORTED_BY, $list_id = array() ) {
		$args           = self::get_base_query_args();
		$tax_query_args = array(
			'tax_query' => array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => $list_id,
					'operator' => 'IN',
				),
			),
		);
		$args           = array_merge( $args, $tax_query_args, self::get_order_by_args( $sorted_by ) );

		$query    = new \WP_QUERY( $args );
		$products = array();
		if ( $query->have_posts() ) {
			$products = $query->posts;
		}
		$products = self::get_products_information( $products );
		return $products;
	}

	public static function get_products_by_tags( $sorted_by = SORTED_BY, $list_id = array() ) {
		$args           = self::get_base_query_args();
		$tax_query_args = array(
			'tax_query' => array(
				array(
					'taxonomy' => 'product_tag',
					'field'    => 'term_id',
					'terms'    => $list_id,
					'operator' => 'IN',
				),
			),
		);
		$args           = array_merge( $args, $tax_query_args, self::get_order_by_args( $sorted_by ) );

		$query    = new \WP_QUERY( $args );
		$products = array();
		if ( $query->have_posts() ) {
			$products = $query->posts;
		}
		$products = self::get_products_information( $products );
		return $products;
	}

	public static function sort_products( $products, $sorted_by ) {
		switch ( $sorted_by ) {
			case 'descName':
				self::yaymail_usort( $products, 'name', false );
				break;
			case 'ascPrice':
				self::yaymail_usort( $products, 'compare_price' );
				break;
			case 'descPrice':
				self::yaymail_usort( $products, 'compare_price', false );
				break;
			default:
				self::yaymail_usort( $products, 'name' );
				break;
		}
		return $products;
	}

	public static function yaymail_usort( &$items, $key, $is_asc = true ) {
		\usort(
			$items,
			function( $item_a, $item_b ) use ( $key, $is_asc ) {
				if ( $item_a[ $key ] == $item_b[ $key ] ) {
					return 0;
				}
				if ( $is_asc ) {
					return ( $item_a[ $key ] < $item_b[ $key ] ) ? -1 : 1;
				}
				return ( $item_a[ $key ] > $item_b[ $key ] ) ? -1 : 1;
			}
		);
	}

	public static function get_order_by_args( $sorted_by ) {
		$order_by_args = array();
		if ( 'ascName' === $sorted_by || 'descName' === $sorted_by ) {
			$order_by_args['orderby'] = 'name';
			$order_by_args['order']   = str_replace( 'Name', '', $sorted_by );
		}
		if ( 'ascPrice' === $sorted_by || 'descPrice' === $sorted_by ) {
			$order_by_args['orderby']  = 'meta_value_num';
			$order_by_args['meta_key'] = '_price';
			$order_by_args['order']    = str_replace( 'Price', '', $sorted_by );
		}
		if ( 'random' === $sorted_by ) {
			$order_by_args = array();
		}
		return $order_by_args;
	}

	public static function get_base_query_args() {
		return array(
			'post_type'   => 'product',
			'post_status' => 'publish',
			'limit'       => '-1',
		);
	}

	public static function get_products_information( $list_of_ids ) {
		$result = array();
		foreach ( $list_of_ids as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( ! $product || 'grouped' === $product->get_type() || 'outofstock' === $product->get_stock_status() ) {
				continue;
			}
			$sale_price    = null;
			$regular_price = null;
			if ( ! $product->is_type( 'variable' ) ) {
				$sale_price         = $product->get_sale_price();
				$regular_price      = $product->get_regular_price();
				$price              = $product->get_price_html();
				$sale_price_html    = ! empty( $sale_price ) ? \wc_price( $sale_price ) : '';
				$regular_price_html = ! empty( $regular_price ) ? \wc_price( $regular_price ) : '';
			} else {
				$min_sale_price         = $product->get_variation_sale_price( 'min', true );
				$max_sale_price         = $product->get_variation_sale_price( 'max', true );
				$min_regular_price      = $product->get_variation_regular_price( 'min', true );
				$max_regular_price      = $product->get_variation_regular_price( 'max', true );
				$show_min_regular_price = $min_sale_price !== $min_regular_price;
				$show_max_regular_price = $min_regular_price !== $max_regular_price && $max_regular_price !== $max_sale_price;
				$show_max_sale_price    = $min_sale_price !== $max_sale_price;
				$sale_price_html        = \wc_price( $min_sale_price ) . ( $show_max_sale_price ? ' - ' . \wc_price( $max_sale_price ) : '' );
				$regular_price_html     = ( $show_min_regular_price ? \wc_price( $min_regular_price ) : '' ) . ( $show_min_regular_price && $show_max_regular_price ? ' - ' : '' ) . ( $show_max_regular_price ? \wc_price( $max_regular_price ) : '' );
				$price                  = $sale_price_html . ( '' !== $regular_price_html ? '<span style="text-decoration: line-through; margin-left: 5px;">' . $regular_price_html . '</span>' : '' );
			}

			$result[] = array(
				'id'            => $product_id,
				'name'          => $product->get_title(),
				'compare_price' => $sale_price ? $sale_price : ( $regular_price ? $regular_price : 0 ),
				'price'         => $price,
				'sale_price'    => $sale_price_html,
				'regular_price' => $regular_price_html,
				'image'         => $product->get_image_id() ? current( wp_get_attachment_image_src( $product->get_image_id(), 'single-post-thumbnail' ) ) : wc_placeholder_img_src(),
				'permalink'     => $product->get_permalink(),
			);
		}
		return $result;
	}

	public static function randomize_products( $products, $limit ) {
		shuffle( $products );
		$random_key = array_rand( $products, $limit > count( $products ) ? count( $products ) : $limit );
		$products   = array_map(
			function( $index ) use ( $products ) {
				return $products[ $index ];
			},
			$random_key
		);
		return $products;
	}

	public static function get_limit_products( $products, $limit ) {
		array_splice( $products, $limit );
		return $products;
	}

	public static function get_custom_terms( $taxonomy, $search_string = '', $all = false ) {
		if ( '' === $search_string && false === $all ) {
			return array();
		}
		$orderby      = 'name';
		$show_count   = 0;
		$pad_counts   = 0;
		$hierarchical = 1;
		$empty        = 0;

		$args      = array(
			'taxonomy'     => $taxonomy,
			'orderby'      => $orderby,
			'show_count'   => $show_count,
			'pad_counts'   => $pad_counts,
			'hierarchical' => $hierarchical,
			'name__like'   => $search_string,
			'hide_empty'   => $empty,
		);
		$all_terms = \get_categories( $args );
		return $all_terms;
	}

	public static function get_all_categories() {
		$categories = self::get_custom_terms( 'product_cat', '', true );
		$result     = array_map(
			function( $item ) {
				return array(
					'label' => $item->name,
					'key'   => $item->term_id,
				);
			},
			$categories
		);
		return $result;
	}

	public static function get_all_tags() {
		$tags   = self::get_custom_terms( 'product_tag', '', true );
		$result = array_map(
			function( $item ) {
				return array(
					'label' => $item->name,
					'key'   => $item->term_id,
				);
			},
			$tags
		);
		return $result;
	}


	public static function get_categories( $search_string ) {
		if ( '' === $search_string ) {
			return array();
		}
		$categories = self::get_custom_terms( 'product_cat', $search_string );
		$result     = array_map(
			function( $item ) {
				return array(
					'label' => $item->name,
					'key'   => $item->term_id,
				);
			},
			$categories
		);
		return $result;
	}

	public static function get_tags( $search_string ) {
		if ( '' === $search_string ) {
			return array();
		}
		$tags   = self::get_custom_terms( 'product_tag', $search_string );
		$result = array_map(
			function( $item ) {
				return array(
					'label' => $item->name,
					'key'   => $item->term_id,
				);
			},
			$tags
		);
		return $result;
	}

	public static function search_products( $search_string ) {
		global $wpdb;
		if ( '' === $search_string ) {
			return array();
		}
		$products = $wpdb->get_results( $wpdb->prepare( "SELECT id, post_title from {$wpdb->prefix}posts WHERE {$wpdb->prefix}posts.post_type = 'product' AND {$wpdb->prefix}posts.post_title LIKE %s order by post_title ASC ", "%{$search_string}%" ) );
		$result   = array_map(
			function( $item ) {
				return array(
					'key'   => $item->id,
					'label' => $item->post_title,
				);
			},
			$products
		);
		return $result;
	}

}
