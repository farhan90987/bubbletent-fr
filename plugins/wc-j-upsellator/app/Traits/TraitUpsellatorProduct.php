<?php

namespace WcJUpsellator\Traits;

trait TraitUpsellatorProduct
{		

    public $found_gift = [];
    /* 
    /* Get the attributes of a given product.  
    */
	private function getProductAttributes( $product )
	{

			$attributes = [];		
			
			foreach( $product->get_attributes() as $taxonomy => $attribute ): 				
				
				if ( is_object( $attribute ) ){

						$slugs = $attribute->get_slugs();
						
                        if( !empty( $slugs ) )
                        {
                            foreach( $slugs as $slug )
                            {
                                $attributes[] = $taxonomy."_".$slug;
                            }

                        }
											
				}   

			endforeach;
            
			return $attributes;
		
    }    
    /*
    /* Calculate the discount percentage of a given product 
    */
    private function getProductAndVariationsIDS( $product )
	{

            $ids = [ $product->get_id() ];

            if( !$product->is_type( 'variable' ) ) return $ids;

            $variations     = $product->get_available_variations();
            $variations_id  = wp_list_pluck( $variations, 'variation_id' );

            return array_merge( $ids, $variations_id );
		
    }
    /* 
    /* Check if the given product triggers a gift 
    */
    public function getRelatedGift( $product )
    {
        
        $data               = [];
        
        if( !empty( woo_j_gift('products') ))
        {

                $cart_product_attributes = $this->getProductAttributes( $product ); 

                foreach( woo_j_gift('products') as $gift )
                {      
                        /*
                        /* If the label text is empty or gift not active, we skip the check
                        */
                        if( !isset( $gift['active'] )  || $gift['active'] == 1  ): 
                              
                                switch ( $gift['type'] ) 
                                {

                                    case 'products-list':                                        
                                       
                                        $test_passed        = ( array_intersect( $this->getProductAndVariationsIDS($product), $gift['products'] )  ) ? true : false;
                                        break;
                                    
                                    case 'category-attributes-list': 
                                        
                                        
                                        if( empty( $gift['categories'] ) && empty( $gift['attributes'] ) && empty( $gift['attributes_2'] ) )
                                        {

                                            $test_passed = false;     


                                        }else{

                                            $test_passed_cat    = true;
                                            $test_passed_attr   = true;
                                            $test_passed_attr_2 = true;

                                            if( !empty( $gift['categories'] ) )
                                            {
                                                $test_passed_cat    = ( count( array_intersect( $product->get_category_ids(), $gift['categories'] ) ) ) ? true : false;
                                            }

                                            if( !empty( $gift['attributes'] ) )
                                            {
                                                $test_passed_attr   = ( count( array_intersect( $cart_product_attributes, $gift['attributes'] ) ) )  ? true : false;
                                            }

                                            if( !empty( $gift['attributes_2'] ) )
                                            {
                                                $test_passed_attr_2   = ( count( array_intersect( $cart_product_attributes, $gift['attributes_2'] ) ) )  ? true : false;
                                            }

                                            $test_passed     = $test_passed_attr * $test_passed_cat * $test_passed_attr_2;

                                        }  
                                       
                                        break;


                                    default:

                                        $test_passed = false;

                                }
                                
                                if( $test_passed ){

                                        $data[] = $this->found_gift[] = array('product_id' => $gift['id'],'text' => $gift['shop_label'], 'type' => "label-gift", 'single_product_text' => $gift['single_product_text'] );                                          

                                } 
                            
                        endif;

                }

        }  

        return $data;

    }
  
}