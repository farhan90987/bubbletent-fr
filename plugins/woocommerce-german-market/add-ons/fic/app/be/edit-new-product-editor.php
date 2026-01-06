<?php

class German_Market_FIC_Product_Editor {

	/**
	 * Add tab for FIC data in new product editor
	 * 
	 * @wp-hook woocommerce_block_template_area_product-form_after_add_block_general
	 * @param $general_group
	 * @return void
	 */
	public static function add_fic_data( $general_group ) {

		$parent = $general_group->get_parent();
	
	    if ( ! method_exists( $parent, 'add_group' ) ) {
	        return;
	    }

	    $is_variation = 'product-variation' === $general_group->get_parent()->get_id();

	    $tab = $parent->add_group([
	            'id'         => 'german-market-fic',
	            'order'      => $general_group->get_order() + 40,
	            'attributes' => [
	              'title' => __( 'Food Data', 'woocommerce-german-market' ),
	            ],
	        ]);

         
        if ( $is_variation ) {
	    	$notice = $tab->add_block(
				array(
					'id'         => 'german-market-ficvariation-notice',
					'blockName'  => 'woocommerce/product-single-variation-notice',
					'order'      => 5,
					'attributes' => array(
						'content'       => __( 'Values of empty fields in this variation will inherit their value from the parent product.', 'woocommerce-german-market' ),
						'type'          => 'info',
						'isDismissible' => false,
					),
				)
			);
	    }

	    $section = $tab->add_section([
            'id'         => 'german-market-fic-section',
            'order'      => 10,
            'attributes' => [
              'title' => __( 'Food Data', 'woocommerce-german-market' ), 
            ],
            
        ]);

	   
	    $columns  = $section->add_block(
			array(
				'id'        => 'german-market-fic-columns',
				'blockName' => 'core/columns',
				'order'     => 10,
			)
		);

		$column_1 = $columns->add_block(
			array(
				'id'         => 'german-market-fic-column-1',
				'blockName'  => 'core/column',
				'order'      => 10,
				'attributes' => array(
					'templateLock' => 'all',
				),
				
			)
		);

		$column_1->add_block([
            'id'         => '_nutritional_values_remark',
            'order'      => 1,
            'blockName'  => 'woocommerce/product-text-area-field', // this is beta in wc 8.7, check when released!
            'attributes' => [
                'property' 		=> 'meta_data._nutritional_values_remark',
                'label'    		=> __( 'Remark', 'woocommerce-german-market' ),
                'placeholder' 	=> get_option( 'gm_fic_ui_frontend_remark_nutritional_values', __( 'Nutritional values per 100g', 'woocommerce-german-market' ) ),
                'mode'			=> 'plain-text'
            ]
        ]);

		$column_1->add_block([
            'id'         => '_fic_ingredients',
            'order'      => 1,
            'blockName'  => 'woocommerce/product-text-area-field', // this is beta in wc 8.7, check when released!
            'attributes' => [
                'property'	=> 'meta_data._fic_ingredients',
                'label'   	=> get_option( 'gm_fic_ui_frontend_labels_ingredients', __( 'Ingredients', 'woocommerce-german-market' ) ),
                'help'		=> strip_tags( __( 'You can use <code>[h][/h]</code> to highlight special ingredients, for instance to highlight allergenes: <code>water, [h]milk[/h], sugar, salt</code>.', 'woocommerce-german-market' ) ),
                'mode'		=> 'plain-text'
            ]
        ]);

		$column_1->add_block([
            'id'         => '_allergens_info',
            'order'      => 1,
            'blockName'  => 'woocommerce/product-text-area-field', // this is beta in wc 8.7, check when released!
            'attributes' => [
                'property'	=> 'meta_data._allergens_info',
                'label'		=> get_option( 'gm_fic_ui_frontend_labels_allergens', __( 'Allergens', 'woocommerce-german-market' ) ),
                'mode'		=> 'plain-text'
            ]
        ]);

		$column_1->add_block([
            'id'         => '_alcohol_value',
            'order'      => 1,
            'blockName'  => 'woocommerce/product-text-area-field', // this is beta in wc 8.7, check when released!
            'attributes' => [
                'property' 	=> 'meta_data._alcohol_value',
                'label'    	=> __( 'Alcohol Content', 'woocommerce-german-market' ) . ' - ' . __( 'Value', 'woocommerce-german-market' ),
                'mode'		=> 'plain-text'
            ]
        ]);

		$column_1->add_block([
            'id'         => '_alcohol_unit',
            'order'      => 1,
            'blockName'  => 'woocommerce/product-text-area-field', // this is beta in wc 8.7, check when released!
            'attributes' => [
                'property' 		=> 'meta_data._alcohol_unit',
                'label'    		=> __( 'Alcohol Content', 'woocommerce-german-market' ) . ' - ' . __( 'Unit', 'woocommerce-german-market' ),
                'placeholder' 	=> get_option( 'gm_fic_ui_alocohol_default_unit', __( '% vol', 'woocommerce-german-market' ) ),
                'mode'			=> 'plain-text'
                // we need a default value here
            ]
        ]);

		$column_2 = $columns->add_block(
			array(
				'id'         => 'german-market-fic-column-2',
				'blockName'  => 'core/column',
				'order'      => 20,
				'attributes' => array(
					'templateLock' => 'all',
				),
				
			)
		);

	    $terms = get_terms( 'gm_fic_nutritional_values', array( 'orderby' => 'slug', 'hide_empty' => 0 ) );
		$default_nutritionals = gm_fic_get_default_nutritionals();
		$prefix = get_option( 'gm_fic_ui_frontend_prefix_nutritional_values', __( '- of which', 'woocommerce-german-market' ) );
		
		if ( $prefix != '' ) {
			$prefix = $prefix . ' ';
		}
		
		$counter = 0;
		foreach ( $terms as $term ) {

			$counter++;

			// meta data key
			$key = '_nutritional_values_' . $term->slug;
			
			// create label
			$required = false;
			if ( isset( $default_nutritionals[ $term->slug ] ) ) {
				$required = isset( $default_nutritionals[ $term->slug ][ 'required' ] ) && $default_nutritionals[ $term->slug ][ 'required' ];
			}

			$required_string = $required ? '*' : '';
			
			// prefix if term has parent
			$prefix_string = '';
			if ( isset( $default_nutritionals[ $term->slug ] ) ) {
				$has_parent = isset( $default_nutritionals[ $term->slug ][ 'parent' ] ) && $default_nutritionals[ $term->slug ][ 'parent' ];
				if ( $has_parent ) {
					$parent_slug = $default_nutritionals[ $term->slug ][ 'parent' ];
					$prefix_string = $default_nutritionals[ $parent_slug ][ 'label' ] . ' ' . $prefix;
				}
			} else {
				if ( isset( $term->parent ) && $term->parent > 0 ) {
					$prefix_string = $prefix;
				}
			}

			$label = $prefix_string . apply_filters( 'gm_fic_nutritional_values_term_name', $term->name, $term, true ) . $required_string;

			$column_2->add_block([
	            'id'         => $key,
	            'order'      => $counter,
	            'blockName'  => 'woocommerce/product-text-field',
	            'attributes' => [
	                'property' => 'meta_data.' . $key,
	                'name' => $key,
	                'label'    => $label,
	            ],
	        ]);
		}
	}
}
