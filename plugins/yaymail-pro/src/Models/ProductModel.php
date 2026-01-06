<?php

namespace YayMail\Models;

use YayMail\Utils\SingletonTrait;

/**
 * Revision Model
 *
 * @method static ProductModel get_instance()
 */
class ProductModel {

    use SingletonTrait;

    const DEFAULT_LIMIT = 5;

    const COMMON_WP_QUERY_ARGUMENTS = [
        'post_type'   => 'product',
        'post_status' => 'publish',
    ];

    /**
     * Retrieves a list of terms(categories | tags | products) based on the provided parameters.
     *
     * This function fetches a list of terms(categories | tags | products) based on the specified search criteria and pagination options.
     *
     * @param array $params An associative array of parameters for the term retrieval.
     *   - 'search_string' (string): The search string to filter terms. Default is an empty string.
     *   - 'page_num' (number): The page number for paginating results. Default is "1".
     *   - 'page_size' (number): The number of terms to retrieve per page. Default is "20".
     *   - 'term_type' (string): The type of terms to retrieve. Could be "product_cat" | "product_tag" | null | ''. (null | '' is for Product).
     * @param array $field_mapping An associative array of field mapping for the term retrieval.
     *   - 'id' (string): The field name for the term ID. Default is "id".
     *   - 'name' (string): The field name for the term name. Default is "name".
     *
     * @return array An associative array containing the retrieved terms.
     *   - 'list' (array): An array of term data, each with 'id' and 'name' fields.
     *   - 'next_page' (number|false): The token for the next page of results, if available.
     */
    public function get_terms( $params, $field_mapping = [
        'id'   => 'id',
        'name' => 'name',
    ] ) {
        $page_data = $this->get_terms_page( isset( $params['term_type'] ) ? $params['term_type'] : '', $params['search_string'] ?? '', $params['page_num'] ?? 1, $params['page_size'] ?? 20 );

        $result = [
            'list'      => array_map(
                function( $item ) use ( $field_mapping ) {
                    $id_field   = $field_mapping['id'] ?? 'id';
                    $name_field = $field_mapping['name'] ?? 'name';
                    return [
                        'id'   => strval( isset( $item->{$id_field} ) ? $item->{$id_field} : $item->id ),
                        'name' => isset( $item->{$name_field} ) ? $item->{$name_field} : $item->name,
                    ];
                },
                $page_data['list']
            ),
            'next_page' => $page_data['next_page'],
        ];

        return $result;
    }

    public function get_products_with_reviews( $params ) {
        $product_type = isset( $params['product_type'] ) ? $params['product_type'] : 'featured';
        unset( $params['product_type'] );

        switch ( $product_type ) {
            case 'featured':
                $products = $this->get_product_type_featured_products( $params );
                break;
            case 'product_selections':
                $products = $this->get_by_product_ids( $params );
                break;
            case 'order_products':
                $products = $this->get_order_products( $params );
                break;
            default:
                $products = [];
                break;
        }//end switch

        $products_response = array_map(
            function( $product ) use ( $params ) {
                return $this->map_reviews( $product, $params );
            },
            $products
        );

        $result = [];

        foreach ( $products_response as $product_response ) {
            if ( ! empty( $product_response ) ) {
                $result[] = $product_response;
            }
        }

        return $result;
    }

    /**
     * Retrieves featured products based on the provided parameters and product type.
     *
     * This function allows you to retrieve featured products based on different product types or specific criteria. It delegates the retrieval of products to various specialized methods depending on the product type.

     * @param array $params An associative array of parameters for retrieving featured products.
     *   - 'product_type' (string): The type of featured products to retrieve (e.g., 'newest', 'on_sale', 'featured', 'category_selections', 'tag_selections', 'product_selections'). Default is 'newest'.
     *   - 'number_of_products' (string): The number of featured products to retrieve. Default is "5".
     *   - 'sorted_by' (string): The sorting criteria for the featured products. Default is "none".
     *   - 'category_ids' (null or array): An array of category IDs to filter products by, or null if not used.
     *   - 'tag_ids' (null or array): An array of tag IDs to filter products by, or null if not used.
     *   - 'product_ids' (null or array): An array of product IDs to retrieve, or null if not used.
     *
     * @return array An array of featured products with details in the following format:
     *   - 'id' (int): The product's ID.
     *   - 'name' (string): The product's name.
     *   - 'sale_price_html' (string): The HTML representation of the sale price.
     *   - 'regular_price_html' (string): The HTML representation of the regular price.
     *   - 'thumbnail_src' (string): The URL of the product's thumbnail image.
     *   - 'permalink' (string): The URL to the product's page.
     */
    public function get_featured_products( $params ) {
        $product_type = isset( $params['product_type'] ) ? $params['product_type'] : 'newest';
        unset( $params['product_type'] );

        switch ( $product_type ) {
            case 'newest':
                $products = $this->get_newest_products( $params );
                break;
            case 'on_sale':
                $products = $this->get_on_sale_products( $params );
                break;
            case 'featured':
                $products = $this->get_product_type_featured_products( $params );
                break;
            case 'category_selections':
                $products = $this->get_by_categories( $params );
                break;
            case 'tag_selections':
                $products = $this->get_by_tags( $params );
                break;
            case 'product_selections':
                $products = $this->get_by_product_ids( $params );
                break;
            default:
                $products = [];
                break;
        }//end switch

        $products_response = array_map( [ $this, 'get_product_response' ], $products );

        $result = [];

        foreach ( $products_response as $product_response ) {
            if ( ! empty( $product_response ) ) {
                $result[] = $product_response;
            }
        }

        if ( isset( $params['sorted_by'] ) && 'price_ascending' === $params['sorted_by'] ) {
            usort(
                $result,
                function( $a, $b ) {
                    return (float) $a['price'] - (float) $b['price'];
                }
            );
        }
        if ( isset( $params['sorted_by'] ) && 'price_descending' === $params['sorted_by'] ) {
            usort(
                $result,
                function( $a, $b ) {
                    return (float) $b['price'] - (float) $a['price'];
                }
            );
        }
        return $result;
    }

    public function get_cross_up_sells_products( $params ) {
        $order_id               = isset( $params['order_id'] ) ? $params['order_id'] : 0;
        $linked_products_type   = isset( $params['linked_products_type'] ) ? $params['linked_products_type'] : 'cross_sells';
        $max_products_displayed = isset( $params['max_products_displayed'] ) ? $params['max_products_displayed'] : 0;
        if ( 0 === $order_id ) {
            return [];
        }

        if ( 'sample_order' === $order_id ) {
            $products          = wc_get_products( [] );
            $products_response = array_map( [ $this, 'get_product_response' ], $products );
            $result            = [];
            foreach ( $products_response as $product_response ) {
                if ( count( $result ) >= $max_products_displayed ) {
                    break;
                }
                if ( ! empty( $product_response ) ) {
                    $result[] = $product_response;
                }
            }
            return $result;
        }

        $order       = wc_get_order( $order_id );
        $items       = $order->get_items();
        $product_ids = [];
        $products    = [];
        foreach ( $items as $item ) {
            if ( 'cross_sells' === $linked_products_type ) {
                $product_ids = array_merge( $item->get_product()->get_cross_sell_ids(), $product_ids );
            } else {
                $product_ids = array_merge( $item->get_product()->get_upsell_ids(), $product_ids );
            }
        }
        $product_ids = array_unique( $product_ids );

        if ( empty( $product_ids ) ) {
            return [];
        }

        foreach ( $product_ids as $product_id ) {
            if ( count( $products ) < $max_products_displayed ) {
                $products[] = wc_get_product( $product_id );
            }
        }

        $products_response = array_map( [ $this, 'get_product_response' ], $products );
        $result            = [];
        foreach ( $products_response as $product_response ) {
            if ( ! empty( $product_response ) ) {
                $result[] = $product_response;
            }
        }

        return $result;
    }

    /**
     * Retrieves the newest products based on the provided criteria.
     *
     * This function retrieves the newest products from the WooCommerce store based on the specified criteria, including the number of products to retrieve and the sorting order.
     *
     * @param array $criteria An associative array of criteria for retrieving the newest products.
     *   - 'number_of_products' (string): The number of newest products to retrieve. Default is "5".
     *   - 'sorted_by' (string): The sorting criteria for the newest products. Default is "none".
     *   - 'category_ids' (null or array): An array of category IDs to filter products by, or null if not used.
     *   - 'tag_ids' (null or array): An array of tag IDs to filter products by, or null if not used.
     *   - 'product_ids' (null or array): An array of specific product IDs to retrieve, or null if not used.
     *
     * @param array $optional_args An associative array of optional arguments for the query.
     *
     * @return WC_Product_Simple[]|WC_Product_Variable[] An array of WooCommerce simple and variable product objects representing the newest products.
     */
    private function get_newest_products( $criteria, $optional_args = [] ) {
        $args = [
            'limit'     => isset( $criteria['number_of_products'] ) ? $criteria['number_of_products'] : self::DEFAULT_LIMIT,

            'orderby'   => 'date',
            'order'     => 'DESC',
            'status'    => 'publish',
            'tax_query' => [
                [
                    'taxonomy' => 'product_type',
                    'field'    => 'slug',
                    'terms'    => 'grouped',
                    'operator' => 'NOT IN',
                ],
            ],
        ];
        if ( isset( $criteria['sorted_by'] ) && 'random' === $criteria['sorted_by'] ) {
            $args['orderby'] = 'rand';
        }

        if ( ! empty( $optional_args ) ) {
            $args = wp_parse_args( $args, $optional_args );
        }

        $query    = new \WC_Product_Query( $args );
        $products = $query->get_products();

        return $products;
    }

    /**
     * Retrieves products on sale based on the provided criteria.
     *
     * This function retrieves products on sale from the WooCommerce store based on the specified criteria, including the number of products to retrieve and the sorting order.
     *
     * @param array $criteria An associative array of criteria for retrieving products on sale.
     *   - 'number_of_products' (string): The number of products on sale to retrieve. Default is "5".
     *   - 'sorted_by' (string): The sorting criteria for products on sale. Default is "none".
     *   - 'category_ids' (null or array): An array of category IDs to filter products by, or null if not used.
     *   - 'tag_ids' (null or array): An array of tag IDs to filter products by, or null if not used.
     *   - 'product_ids' (null or array): An array of specific product IDs to retrieve, or null if not used.
     * @param array $optional_args An associative array of optional arguments for the query.
     *
     * @return WC_Product_Simple[]|WC_Product_Variable[] An array of WooCommerce simple and variable product objects representing products on sale.
     */
    private function get_on_sale_products( $criteria, $optional_args = [] ) {
        $args = [
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key'     => '_sale_price',
                    'value'   => 0,
                    'compare' => '>',
                    'type'    => 'numeric',
                ],
                [
                    'key'     => '_min_variation_sale_price',
                    'value'   => 0,
                    'compare' => '>',
                    'type'    => 'numeric',
                ],

            ],
            'status'     => 'publish',
            'tax_query'  => [
                [
                    'taxonomy' => 'product_type',
                    'field'    => 'slug',
                    'terms'    => 'grouped',
                    'operator' => 'NOT IN',
                ],
            ],
        ];

        $args = array_merge(
            self::COMMON_WP_QUERY_ARGUMENTS,
            [
                'posts_per_page' => isset( $criteria['number_of_products'] ) ? $criteria['number_of_products'] : self::DEFAULT_LIMIT,
                'fields'         => 'ids',
            ],
            $args
        );

        if ( isset( $criteria['sorted_by'] ) && 'random' === $criteria['sorted_by'] ) {
            $args['orderby'] = 'rand';
        }

        if ( ! empty( $optional_args ) ) {
            $args = wp_parse_args( $args, $optional_args );
        }

        $query       = new \WP_QUERY( $args );
        $product_ids = $query->posts;

        $products = wc_get_products( [ 'include' => $product_ids ] );
        return $products;
    }

    /**
     * Retrieves featured products based on the provided criteria.
     *
     * This function retrieves products on sale from the WooCommerce store based on the specified criteria, including the number of products to retrieve and the sorting order.
     *
     * @param array $criteria An associative array of criteria for retrieving products on sale.
     *   - 'number_of_products' (string): The number of products on sale to retrieve. Default is "5".
     *   - 'sorted_by' (string): The sorting criteria for products on sale. Default is "none".
     *   - 'category_ids' (null or array): An array of category IDs to filter products by, or null if not used.
     *   - 'tag_ids' (null or array): An array of tag IDs to filter products by, or null if not used.
     *   - 'product_ids' (null or array): An array of specific product IDs to retrieve, or null if not used.
     * @param array $optional_args An associative array of optional arguments for the query.
     *
     * @return WC_Product_Simple[]|WC_Product_Variable[] An array of WooCommerce simple and variable product objects representing products on sale.
     */
    private function get_product_type_featured_products( $criteria, $optional_args = [] ) {
        $tax_query[] = [
            'taxonomy' => 'product_visibility',
            'field'    => 'name',
            'terms'    => 'featured',
            'operator' => 'IN',
        ];
        $tax_query[] = [
            'taxonomy' => 'product_type',
            'field'    => 'slug',
            'terms'    => 'grouped',
            'operator' => 'NOT IN',
        ];

        $args = array_merge(
            self::COMMON_WP_QUERY_ARGUMENTS,
            [
                'posts_per_page' => isset( $criteria['number_of_products'] ) ? $criteria['number_of_products'] : self::DEFAULT_LIMIT,
                'fields'         => 'ids',
                'status'         => 'publish',
                'tax_query'      => $tax_query,
            ]
        );

        if ( isset( $criteria['sorted_by'] ) && 'random' === $criteria['sorted_by'] ) {
            $args['orderby'] = 'rand';
        }

        if ( ! empty( $optional_args ) ) {
            $args = wp_parse_args( $args, $optional_args );
        }

        $query = new \WP_QUERY( $args );
        if ( $query->have_posts() ) {
            $product_ids = $query->posts;
            $products    = wc_get_products( [ 'include' => $product_ids ] );
            return $products;
        } else {
            return [];
        }
    }

    /**
     * Retrieves products by category IDs based on the provided criteria.
     *
     * This function retrieves products from the WooCommerce store based on specified category IDs and criteria, including the number of products to retrieve and the sorting order.
     *
     * @param array $criteria An associative array of criteria for retrieving products by category.
     *   - 'number_of_products' (string): The number of products to retrieve by category. Default is "5".
     *   - 'sorted_by' (string): The sorting criteria for products by category. Default is "none".
     *   - 'category_ids' (array): An array of category IDs to filter products by. If empty, an empty array is returned.
     * @param array $optional_args An associative array of optional arguments for the query.
     *
     * @return WC_Product_Simple[]|WC_Product_Variable[] An array of WooCommerce simple and variable product objects representing products by category.
     */
    private function get_by_categories( $criteria, $optional_args = [] ) {
        if ( empty( $criteria['category_ids'] ) ) {
            return [];
        }

        $args = [
            'limit'               => isset( $criteria['number_of_products'] ) ? $criteria['number_of_products'] : self::DEFAULT_LIMIT,
            'product_category_id' => $criteria['category_ids'],
            'status'              => 'publish',
        ];

        if ( isset( $criteria['sorted_by'] ) && 'random' === $criteria['sorted_by'] ) {
            $args['orderby'] = 'rand';
        }

        if ( ! empty( $optional_args ) ) {
            $args = wp_parse_args( $args, $optional_args );
        }

        $query    = new \WC_Product_Query( $args );
        $products = $query->get_products();
        return $products;
    }

    /**
     * Retrieves products by tag IDs based on the provided criteria.
     *
     * This function retrieves products from the WooCommerce store based on specified tag IDs and criteria, including the number of products to retrieve and the sorting order.
     *
     * @param array $criteria An associative array of criteria for retrieving products by tag.
     *   - 'number_of_products' (string): The number of products to retrieve by tag. Default is "5".
     *   - 'sorted_by' (string): The sorting criteria for products by tag. Default is "none".
     *   - 'tag_ids' (array): An array of category IDs to filter products by. If empty, an empty array is returned.
     * @param array $optional_args An associative array of optional arguments for the query.
     *
     * @return WC_Product_Simple[]|WC_Product_Variable[] An array of WooCommerce simple and variable product objects representing products by category.
     */
    private function get_by_tags( $criteria, $optional_args = [] ) {
        if ( empty( $criteria['tag_ids'] ) ) {
            return [];
        }

        $args = [
            'limit'          => isset( $criteria['number_of_products'] ) ? $criteria['number_of_products'] : self::DEFAULT_LIMIT,
            'product_tag_id' => $criteria['tag_ids'],
            'status'         => 'publish',
        ];

        if ( isset( $criteria['sorted_by'] ) && 'random' === $criteria['sorted_by'] ) {
            $args['orderby'] = 'rand';
        }

        if ( ! empty( $optional_args ) ) {
            $args = wp_parse_args( $args, $optional_args );
        }

        $query    = new \WC_Product_Query( $args );
        $products = $query->get_products();
        return $products;
    }

    /**
     * Retrieves products by specific product IDs based on the provided criteria.
     *
     * This function retrieves products from the WooCommerce store based on specified product IDs and criteria, including the number of products to retrieve and the sorting order.
     *
     * @param array $criteria An associative array of criteria for retrieving products by specific product IDs.
     *   - 'number_of_products' (string): The number of products to retrieve by specific product IDs. Default is "5".
     *   - 'sorted_by' (string): The sorting criteria for products by specific product IDs. Default is "none".
     *   - 'product_ids' (array): An array of specific product IDs to retrieve. If empty, an empty array is returned.
     * @param array $optional_args An associative array of optional arguments for the query.
     *
     * @return WC_Product_Simple[]|WC_Product_Variable[] An array of WooCommerce simple and variable product objects representing products by specific product IDs.
     */
    private function get_by_product_ids( $criteria, $optional_args = [] ) {
        if ( empty( $criteria['product_ids'] ) ) {
            return [];
        }

        $args = [
            'limit'     => -1,
            'include'   => $criteria['product_ids'],
            'status'    => 'publish',
            'tax_query' => [
                [
                    'taxonomy' => 'product_type',
                    'field'    => 'slug',
                    'terms'    => 'grouped',
                    'operator' => 'NOT IN',
                ],
            ],
        ];

        if ( isset( $criteria['sorted_by'] ) && 'random' === $criteria['sorted_by'] ) {
            $args['orderby'] = 'rand';
        }

        if ( ! empty( $optional_args ) ) {
            $args = wp_parse_args( $args, $optional_args );
        }

        $query    = new \WC_Product_Query( $args );
        $products = $query->get_products();
        return $products;
    }

    private function get_order_products( $criteria ) {
        $order_id = $criteria['order_id'];
        if ( $order_id === 'sample_order' ) {
            $product = new \WC_Product_Simple();
            $product->set_name( 'Happy YayCommerce' );
            $product->set_regular_price( '20.00' );
            $product->set_sale_price( '18.00' );

            // Add sample reviews
            $reviews = [
                [
                    'author'  => 'John Doe',
                    'email'   => 'john@example.com',
                    'content' => __( 'Great product! Very satisfied with the quality.', 'yaymail' ),
                    'rating'  => 5,
                ],
                [
                    'author'  => 'Jane Smith',
                    'email'   => 'jane@example.com',
                    'content' => __( 'Good value for money. Would recommend!', 'yaymail' ),
                    'rating'  => 4,
                ],
            ];

            foreach ( $reviews as $review ) {
                $comment_id = wp_insert_comment(
                    [
                        'comment_post_ID'      => $product->get_id(),
                        'comment_author'       => $review['author'],
                        'comment_author_email' => $review['email'],
                        'comment_content'      => $review['content'],
                        'comment_type'         => 'review',
                        'comment_approved'     => 1,
                        'user_id'              => 0,
                    ]
                );
                update_comment_meta( $comment_id, 'rating', $review['rating'] );
            }

            return [ $product ];
        }//end if
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return [];
        }

        $items = $order->get_items();

        $products = [];
        foreach ( $items as $item ) {
            $product_id = $item->get_data()['product_id'];
            $products[] = wc_get_product( $product_id );
        }
        return $products;
    }

    private function map_reviews( $wc_product, $params ) {
        $limit                  = isset( $params['max_reviews_per_product'] ) ? $params['max_reviews_per_product'] : 2;
        $review_filter          = isset( $params['review_filter'] ) ? $params['review_filter'] : 'review_filter_by_rating';
        $minimum_rating_to_show = isset( $params['minimum_rating_to_show'] ) ? $params['minimum_rating_to_show'] : 1;
        $sorted_by              = isset( $params['sorted_by'] ) ? $params['sorted_by'] : 'asc_of_rating';

        $result_comments = [];
        if ( ! $wc_product || 'grouped' === $wc_product->get_type() ) {
            return [];
        }

        $args = [
            'post_id' => $wc_product->get_id(),
            'status'  => 'approve',
            'type'    => 'review',
        ];

        if ( $sorted_by === 'asc_of_review_date' || $sorted_by === 'des_of_review_date' ) {
            $args['orderby'] = 'comment_date';
            $args['order']   = $sorted_by === 'asc_of_review_date' ? 'ASC' : 'DESC';
        }

        $comments = get_comments( $args );

        if ( empty( $comments ) ) {
            return [];
        }

        // sort comment by rating
        if ( $sorted_by === 'asc_of_rating' || $sorted_by === 'des_of_rating' ) {
            usort(
                $comments,
                function( $a, $b ) use ( $sorted_by ) {
                    $rating_a = (int) get_comment_meta( $a->comment_ID, 'rating', true );
                    $rating_b = (int) get_comment_meta( $b->comment_ID, 'rating', true );
                    if ( $sorted_by === 'asc_of_rating' ) {
                        return $rating_a - $rating_b;
                    } else {
                        return $rating_b - $rating_a;
                    }
                }
            );
        }

        foreach ( $comments as $comment ) {
            $rating_stars = get_comment_meta( $comment->comment_ID, 'rating', true );
            $comment_date = $comment->comment_date;

            if ( $review_filter === 'review_filter_by_rating' ) {
                if ( $rating_stars < $minimum_rating_to_show ) {
                    continue;
                }
            }
            if ( $review_filter === 'review_filter_by_date' && null != $params['from_date_to_date'] ) {
                if ( strtotime( $comment_date ) < strtotime( $params['from_date_to_date'][0] ) || strtotime( $comment_date ) > strtotime( $params['from_date_to_date'][1] ) ) {
                    continue;
                }
            }
            if ( count( $result_comments ) >= $limit ) {
                break;
            }
            $result_comments[] = [
                'author_name'    => $comment->comment_author,
                'author_avatar'  => get_avatar_url( $comment->comment_author_email ),
                'review_date'    => gmdate( get_option( 'date_format' ), strtotime( $comment_date ) ),
                'rating_stars'   => $rating_stars,
                'review_message' => $comment->comment_content,
            ];
        }//end foreach

        if ( empty( $result_comments ) ) {
            return [];
        }

        $result_product = [
            'id'            => $wc_product->get_id(),
            'name'          => $wc_product->get_title(),
            'thumbnail_src' => $wc_product->get_image_id() ? current( wp_get_attachment_image_src( $wc_product->get_image_id(), 'single-post-thumbnail' ) ) : wc_placeholder_img_src(),
            'permalink'     => $wc_product->get_permalink(),
            'reviews'       => $result_comments,
        ];
        return $result_product;
    }


        /**
         * Retrieves product response data based on a WooCommerce product.
         *
         * This function generates and returns an array of data representing a WooCommerce product, including its ID, name, sale price, regular price, thumbnail source, and permalink.
         *
         * @param \WC_Product $wc_product A WooCommerce product object to generate response data for.
         *
         * @return array An associative array containing the product response data.
         *   - 'id' (int): The product's ID.
         *   - 'name' (string): The product's name.
         *   - 'sale_price_html' (string): The HTML representation of the sale price.
         *   - 'regular_price_html' (string): The HTML representation of the regular price.
         *   - 'thumbnail_src' (string): The URL of the product's thumbnail image.
         *   - 'permalink' (string): The URL to the product's page.
         *   - 'price' (number): The price which is used for sorting purpose.
         */
    private function get_product_response( \WC_Product $wc_product ) {
        $result = [];
        if ( ! $wc_product || 'grouped' === $wc_product->get_type() ) {
            return [];
        }
        if ( $wc_product instanceof \WC_Product_Variable ) {
            $min_sale_price    = $wc_product->get_variation_sale_price( 'min', true );
            $max_sale_price    = $wc_product->get_variation_sale_price( 'max', true );
            $min_regular_price = $wc_product->get_variation_regular_price( 'min', true );
            $max_regular_price = $wc_product->get_variation_regular_price( 'max', true );

            $show_min_regular_price = $min_sale_price !== $min_regular_price;
            $show_max_regular_price = $min_regular_price !== $max_regular_price && $max_regular_price !== $max_sale_price;
            $show_max_sale_price    = $min_sale_price !== $max_sale_price;
            $sale_price_html        = wc_price( $min_sale_price ) . ( $show_max_sale_price ? ' - ' . wc_price( $max_sale_price ) : '' );
            $regular_price_html     = ( $show_min_regular_price ? wc_price( $min_regular_price ) : '' ) . ( $show_min_regular_price && $show_max_regular_price ? ' - ' : '' ) . ( $show_max_regular_price ? wc_price( $max_regular_price ) : '' );
            $price_html             = $sale_price_html . ( '' !== $regular_price_html ? '<span style="text-decoration: line-through; margin-left: 5px;">' . $regular_price_html . '</span>' : '' );
            $price                  = ! empty( $min_sale_price ) ? $min_sale_price : ( ! empty( $min_regular_price ) ? $min_regular_price : 0 );
        } else {
            $sale_price      = $wc_product->get_sale_price();
            $sale_price_html = ! empty( $sale_price ) ? wc_price( $sale_price ) : '';

            $regular_price      = $wc_product->get_regular_price();
            $regular_price_html = ! empty( $regular_price ) ? wc_price( $regular_price ) : '';
            $price              = ! empty( $sale_price ) ? $sale_price : ( ! empty( $regular_price ) ? $regular_price : 0 );
        }//end if

        if ( ! empty( $sale_price_html ) ) {
            $regular_price_html = "<span style=\"text-decoration: line-through\">$regular_price_html</span>";
        }

        $result = [
            'id'                 => $wc_product->get_id(),
            'name'               => $wc_product->get_title(),
            'sale_price_html'    => $sale_price_html,
            'regular_price_html' => $regular_price_html,
            'thumbnail_src'      => $wc_product->get_image_id() ? current( wp_get_attachment_image_src( $wc_product->get_image_id(), 'single-post-thumbnail' ) ) : wc_placeholder_img_src(),
            'permalink'          => $wc_product->get_permalink(),
            'price'              => (float) $price,
        ];
        return $result;
    }

    /**
     * Retrieves a page of terms (categories, tags, or products) based on the provided parameters.
     *
     * This function retrieves a page of terms, which can be categories, tags, or products, based on the specified taxonomy, search string, page number, and page size.
     *
     * @param string $taxonomy The taxonomy to filter terms (categories or tags) or an empty string for products.
     * @param string $search_string The search string to filter terms or products by name. Default is an empty string.
     * @param int    $page_num The page number for paginating results. Default is 1.
     * @param int    $page_size The number of terms to retrieve per page. Default is 10.
     * @param array  $optional_args Optional WP_Query arguments to merge with default arguments.
     *
     * @return array An associative array containing the retrieved terms or products.
     *   - 'list' (array): An array of term or product data, each with 'id' and 'name' fields.
     *   - 'next_page' (int|false): The page number for the next page of results, or false if no more pages are available.
     */
    private function get_terms_page( $taxonomy, $search_string = '', $page_num = 1, $page_size = 10, $optional_args = [] ) {

        // if ( class_exists( 'SitePress' ) ) {
        // do_action( 'wpml_switch_language', $active_language );
        // }
        $limit = $page_size + 1;
        // +1 in order to check for next_page
        $offset = ( $page_num - 1 ) * $page_size;

        if ( empty( $taxonomy ) ) {
            /**
             * Get products
             */
            global $wpdb;
            $query = $wpdb->prepare(
                "SELECT id, post_title AS name
                FROM {$wpdb->prefix}posts
                WHERE {$wpdb->prefix}posts.post_type = 'product'
                AND {$wpdb->prefix}posts.post_title LIKE %s 
                ORDER BY post_title ASC
                LIMIT %d OFFSET %d",
                "%{$search_string}%",
                $limit,
                $offset
            );
            $list  = $wpdb->get_results( $query ); //phpcs:ignore

        } else {
            /**
             * Get categories or tags
             */
            $orderby      = 'name';
            $show_count   = 0;
            $pad_counts   = 0;
            $hierarchical = 1;
            $empty        = 0;

            $args = [
                'taxonomy'     => $taxonomy,
                'orderby'      => $orderby,
                'show_count'   => $show_count,
                'pad_counts'   => $pad_counts,
                'hierarchical' => $hierarchical,
                'hide_empty'   => $empty,
                'number'       => $limit,
                'offset'       => $offset,
            ];

            if ( ! empty( $search_string ) ) {
                $args['name__like'] = $search_string;
            }

            $list = array_values( \get_categories( $args ) );
        }//end if

        $next_page = count( $list ) > $page_size ? $page_num + 1 : false;

        if ( $next_page ) {
            array_pop( $list );
        }

        $result = [
            'list'      => $list,
            'next_page' => $next_page,
        ];

        return $result;
    }
}
