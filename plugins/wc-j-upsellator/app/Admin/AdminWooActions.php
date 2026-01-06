<?php

namespace WcJUpsellator\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class AdminWooActions
{   

        public function __construct()
        {
                /*
                /* Nothing to do
                */ 
        }

        public static function register()
        {
                
                $page = new self();
                
                if( is_admin() )
                {
                        add_action( 'wp_ajax_wjufc_search_products', array( $page, 'wjufc_search_products' ) ); 
                        add_action( 'wp_ajax_wjufc_search_categories', array( $page, 'wjufc_search_categories' ) ); 
                        add_action( 'wp_ajax_wjufc_search_attributes', array( $page, 'wjufc_search_attributes' ) ); 
                }  
                        
        }     
        /*  
        /* @hook wp_ajax_wjufc_search_products
        /*
        /* Edited from WooCommerce json_search ( we need something more )  
        */
        public static function wjufc_search_products() 
        {
                check_ajax_referer( 'search-products', 'security' );       

                if ( empty( $_GET['term'] ) ) {
                        wp_die();
                }

                if ( !current_user_can( 'edit_products' ) ) {
			wp_die( -1 );
		}
        
                $parent_visibile = isset( $_GET['parent_vis'] ) ? false  : true;
                $term            = (string) wc_clean( wp_unslash( $_GET['term'] ) );
                $limit           = 20;      

                $include_ids = ! empty( $_GET['include'] ) ? array_map( 'absint', (array) wp_unslash( $_GET['include'] ) ) : array();
                $exclude_ids = ! empty( $_GET['exclude'] ) ? array_map( 'absint', (array) wp_unslash( $_GET['exclude'] ) ) : array();

                $data_store = \WC_Data_Store::load( 'product' );
                $ids        = $data_store->search_products( $term, '', true, false, $limit, $include_ids, $exclude_ids );

                $products = array();
                
                foreach ( $ids as $id ) 
                {
                        
                        $product = wc_get_product( $id );

                        if ( ! wc_products_array_filter_readable( $product ) ) {
                                continue;
                        }         
                        /*
                        /* We can't upsell parent variable product
                        */
                        if ( !$parent_visibile && $product->has_child() ) 
                        {
                        
                        }else{
                        
                                $base_id                      = ( $product->get_parent_id() ) ? $product->get_parent_id() : $product->get_id();

                                $current_product              = [];
                                $current_product['name']      = rawurldecode( $product->get_formatted_name() );
                                $current_product['img']       = get_the_post_thumbnail_url( $base_id  );
                                $current_product['price']     = $product->get_regular_price();
                                $current_product['base_name'] = $product->get_name();

                                $products[ $product->get_id() ] = $current_product;
                        }
                        
                }

                wp_send_json( $products );
        }

        /*  
        /* @hook wp_ajax_wjufc_search_categories
        /*
	/* Search for categories and return json.
	*/
        public static function wjufc_search_categories() 
        {		

		check_ajax_referer( 'search-categories', 'security' );

		if ( !current_user_can( 'edit_products' ) || empty( $_GET['term'] ) ) {
			wp_die();
		}

                $search_text         = wc_clean( wp_unslash( $_GET['term'] ) );
                $categories          = array();
                
		$args = array(
			'taxonomy'   => array( 'product_cat' ),
			'orderby'    => 'id',
			'order'      => 'ASC',
			'hide_empty' => false,
			'fields'     => 'all',
			'name__like' => $search_text,
		);

		$terms = get_terms( $args );

		if ( $terms ) {
			foreach ( $terms as $term ) {
				$term->formatted_name = '';

				if ( $term->parent ) {
					$ancestors = array_reverse( get_ancestors( $term->term_id, 'product_cat' ) );
					foreach ( $ancestors as $ancestor ) {
						$ancestor_term = get_term( $ancestor, 'product_cat' );
						if ( $ancestor_term ) {
							$term->formatted_name .= $ancestor_term->name . ' > ';
						}
					}
				}

				$term->formatted_name        .= $term->name . ' (' . $term->count . ')';
				$categories[ $term->term_id ] = $term;
			}
		}

		wp_send_json( $categories );
	}

        /*  
        /* @hook wp_ajax_wjufc_search_attributes
        /*
	/* Search for attributes and return json.
	*/
        public static function wjufc_search_attributes() 
        {		

		check_ajax_referer( 'search-attributes', 'security' );

		if ( !current_user_can( 'edit_products' ) || empty( $_GET['term'] ) ) {
			wp_die();
		}

                global $wpdb;
                $search_text              = wc_clean( wp_unslash( $_GET['term'] ) );	
		$taxonomy_terms 	  = array();
                $results 		  = $wpdb->get_results( $wpdb->prepare( 
                                                        "SELECT attribute_name 
                                                        FROM {$wpdb->prefix}woocommerce_attribute_taxonomies 
                                                        WHERE attribute_name LIKE %s", '%' . $search_text . '%' )
                                                 );

		if( !empty( $results ) ): 			
			
			foreach ($results as $tax):

				if (taxonomy_exists( wc_attribute_taxonomy_name($tax->attribute_name))):	
					
					$terms = get_terms( wc_attribute_taxonomy_name( $tax->attribute_name ), 'orderby=name&hide_empty=0' );
					
					foreach( $terms as $term ): 
                                                        
							$product_attribute 			= [];
							$product_attribute['id'] 		= $term->term_id;
							$product_attribute['slug'] 		= $term->slug;
							$product_attribute['attribute'] 	= $tax->attribute_name;
							$product_attribute['count'] 		= $term->count; 
							$product_attribute['value']		= "pa_". $tax->attribute_name ."_". $term->slug;

							$taxonomy_terms[ $product_attribute['value'] ] 	= $product_attribute;
							
					endforeach;

				endif;

			endforeach;


		endif;

		wp_send_json( $taxonomy_terms );
	}
	

}