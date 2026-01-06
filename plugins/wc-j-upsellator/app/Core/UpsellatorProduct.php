<?php

namespace WcJUpsellator\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class UpsellatorProduct
{

    public $image;  
    public $url;
    public $on_sale;
    public $id;
    public $key;
    public $data;
    public $extra;
    public $quantity_on_cart;
    public $type;
    public $sold_individually;
    public $actual_price;
    public $base_price;
    public $saved_amount;
    public $max_quantity;
    public $name;
    public $description;
    public $heading;
    public $is_variable;
    public $main_id;
    public $categories_id = [];
    public $attributes    = [];
    /* For woocommerce composite products */
    public $composite_child             = false;
    public $composite_parent            = false;  
    public $is_composite_child          = false;
    public $is_composite_parent         = false;

    public $is_woocbundle_parent        = false;
    public $is_woocbundle_child         = false;

    public $is_woobundle_parent        = false;
    public $is_woobundle_child         = false;

    private $valid = false;

    public function __construct( $product )
    {

            $this->parse( $product );

    }

    private function parse( $product )
    {       
        
            $filtered_product                  = apply_filters( 'woocommerce_cart_item_product', $product['data'], $product, $product['key'] ?? null );
           
            if( $filtered_product->exists() && $product['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $product, $product['key'] ?? null ) ) 
            {
            
                    if( $this->isNotDeletedGift( $filtered_product, $product ) )
                    {
                            
                            $this->key                  = $product['key'] ?? null;
                            $this->id                   = $filtered_product->get_id(); 
                            $this->on_sale              = $filtered_product->is_on_sale();                         
                            $this->image                = $filtered_product->get_image( 'thumbnail' );         
                            $this->type                 = ( isset( $product['upsell'] ) && $product['upsell'] == 1 ) ? 'upsell' : 'standard';
                            $this->type                 = isset( $product['gift'] ) ? 'gift' : $this->type;                           
                            $this->data                 = $product;   
                            $this->is_variable          = $filtered_product->is_type( 'variation' ) ? true : false; 
                            $this->main_id              = $filtered_product->get_parent_id();
                            $this->meta_data         = wc_get_formatted_cart_item_data( $product );
                            /*
                            /* Composite woocommerce products
                            */
                            $this->is_composite_child   = $this->checkIfCompositeChild( $product );
                            $this->is_composite_parent  = $this->checkIfCompositeParent( $product ); 
                                
                            $this->is_woobundle_parent  = isset( $product['woosb_ids'] );
                            $this->is_woobundle_child   = isset( $product['woosb_parent_id'] );                              
                            /*
                            /* WooCommerce Product Bundles
                            */
                            $this->is_woocbundle_parent  = isset( $product['bundled_items'] );
                            $this->is_woocbundle_child   = isset( $product['bundled_by'] );                              

                            $this->quantity_on_cart     = $product['quantity'];
                            $this->sold_individually    = $filtered_product->is_sold_individually();
                            $this->max_quantity         = $filtered_product->get_max_purchase_quantity();
                           
                            $this->actual_price         = $this->calculatePrice( $product );
                            $this->base_price           = woo_j_get_price( $filtered_product, $filtered_product->get_regular_price() );
                            $this->saved_amount         = round( $product['quantity'] * ( (float)$this->base_price - (float)$this->actual_price ) , 1 );

                            $this->heading              = $this->prepareHeading();
                            
                            $this->description          = $this->prepareDescription() ?? substr( $filtered_product->get_description(), 0, 100 );
                            $this->attributes           = $this->getAttributes( $filtered_product );

                            $this->name                 = wp_kses_post( apply_filters( 'wjufw_cart_item_name', $filtered_product->get_name(), $product, $product['key'] ?? null ) . '&nbsp;' );; 
                            $this->url                  = ( $this->isGift() ) ? '#' : $filtered_product->get_permalink();       

                            $this->valid                = true;                       
                        
                    }
                
            }
    } 
    /*  
    /* Check if this product is not a gift no more active 
    */
    private function isNotDeletedGift(  $filtered_product,  $product )
    {

            if( !isset( $product['gift'] ) ) return true;        
            if( !empty( woo_j_gift('products') ) && array_key_exists( $filtered_product->get_id(), woo_j_gift('products') )  ) return true;
            
            WC()->cart->remove_cart_item( $product['key'] );
            
            return false;
    }
    /*
    /* Check if product is a composite child ( WooCommerce Composite Products )
    */
    private function checkIfCompositeChild( $product )
    {
            if( !function_exists( 'wc_cp_is_composited_cart_item' ) ) return false;
            return  wc_cp_is_composited_cart_item( $product );
    }
    /*
    /* Check if product is a composite parent ( WooCommerce Composite Products )
    */
    private function checkIfCompositeParent( $product )
    {           

            if( !function_exists( 'wc_cp_is_composite_container_cart_item' ) ) return false;
            return  wc_cp_is_composite_container_cart_item( $product );
    }
    /*
    /* Check if this specific product allows quantity change
    */
    public function allowsQuantityChange()
    {
            if( $this->isGift() || $this->isChildOfCompositeOrBundle() || $this->sold_individually  || woo_j_conf('qty_hidden') == 1 ) return false;

            if( $this->isUpsell() )
            {

                $allowsQuantity = woo_j_upsell('products')[ $this->id ][ 'quantity_change' ] ?? 0;
                
                if(  $allowsQuantity ) return true;
                return false;
            }

            return true;      
    }
    /*
    /* Check the heading of the product: 
    /* - if it's upsell we check if it has discount or not
    /* - if it's gift we take the value from options
    */
    private function prepareHeading()
    {
               
            if( $this->isUpsell() )
            {   
                $this->getCategory();

                if( isset( woo_j_upsell('products')[ $this->id ] ) )
                {   
                    if( woo_j_upsell('products')[ $this->id ]['discount_type'] == 2 )
                    {
                        return  wjc__( woo_j_upsell('products')[ $this->id ]['heading'] );
                    }  
                }
                
                return  __( 'you saved', 'woo_j_cart' )."&nbsp;<b>". $this->saved_amount ."</b>".woo_j_conf('currency');

            }

            if( $this->isGift() )
            {
            
                return wjc__( woo_j_gift('products')[ $this->id ]['heading'] );
            }

            return $this->getCategory();

    }    
    /*
    /* Prepare the product description for the modal cart
    */
    private function prepareDescription()
    {
            /*
            /* Is this an upsell with a set description?
            */
            if( $this->isUpsell() && isset(  woo_j_upsell('products')[ $this->id ] ) && isset( woo_j_upsell('products')[ $this->id ][ 'text' ] ) )
            {        
                    return  wjc__( woo_j_upsell('products')[ $this->id ][ 'text' ] );
            }
            /*
            /* Is this a gift with a set description?
            */
            if( $this->isGift() && isset( woo_j_gift('products')[ $this->id ] ) && isset( woo_j_gift('products')[ $this->id ][ 'text' ] ) )
            {        
                    return  wjc__( woo_j_gift('products')[ $this->id ][ 'text' ] );
            }

            return null;
    }
    /*
    /* Get a list of product attributes
    */
    private function getAttributes( $product )
    {        
      
            $attributes = [];

            if( $this->is_variable )
            {
                    $attributes = $this->getSingleVariationAttributes( $product );
                    /*
                    /* We marge the variable attributes with the basic product - NON USED IN VARIATIONS - attributes
                    */
                    $parent_product = wc_get_product( $this->main_id  );
                    $attributes     = array_merge( $attributes, $this->getProductAttributes( $parent_product, false ) );
    
            } else {

                    $attributes     = array_merge( $attributes, $this->getProductAttributes( $product, true ) );
            }       
        
            return $attributes;
    }
    /*
    /* Get specific Variation attributes 
    */
    private function getSingleVariationAttributes( $product )
    {
            $attributes = [];            

            foreach( $product->get_variation_attributes() as $taxonomy => $attribute ): 
                
                if (!is_object( $attribute ) ){

                        $basename       = explode('_', $taxonomy );						
                        $attr 		= $basename[ 1 ]."_";
                        if( isset( $basename[ 2 ] ) ) $attr .= $basename[ 2 ] ."_";

                        $attr           .= $attribute;                         
                        $attributes[]    = $attr;

                }                 

            endforeach;
            
            return $attributes;
    }
    /*
    /* Get the base produc attributes
    /* user_in_variations -> if the product is variable, we want to parse only
    /* attributes not used in variations 
    */
    private function getProductAttributes( $product, $used_in_variations )
    {
            $attributes = [];            
            
            if( $product )
            {
                foreach( $product->get_attributes() as $taxonomy => $attribute ): 				
                        
                        if ( is_object( $attribute ) ){

                                /*
                                /* If user_in_variations is false, we skip attributes used in variations
                                */
                                if( !$used_in_variations && $attribute['variation'] ) continue;
                                
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

            }
            
            return $attributes;
    }
    /*
    /* Get first category name ( we need to display it)
    /* and save all others, if they exist, into an array
    */
    private function getCategory()
    {
            $check_id       = ( $this->is_variable ) ? $this->main_id : $this->id;        
            $terms          = wp_get_post_terms( $check_id  , 'product_cat', array('fields' => 'all') );
            $category       = '';       
        
            if( !empty( $terms ) )
            {               

                    foreach( $terms as $term ):

                            array_push( $this->categories_id, $term->term_id );

                    endforeach;

                    $category = $term->name;

            }
            
            return $category;
            
    }

    private function calculatePrice( $product )
    {

        if( $this->isGift( )  ) return 0;
        if( $this->isCompositeParent() )
        {
               return $this->calcuateTotalPriceOfComposite( $product );
        }
        /*
        /* WOOSB bundle compatibility
        */
        if( $this->is_woobundle_parent )
        {
                return round( $product['woosb_price'], 2 ); 
        }
        /*
        /* Compatibility with YITH Gift card
        */       
        if ( $product['data'] instanceof \WC_Product_Gift_Card && isset( $product['ywgc_amount'] ) ) 
        {
                return round( $product['ywgc_amount'], 2 ); 
        }
        /*
        /* Compatibility with discount plugins
        */          
        if( !$this->isUpsell() )
        { 
                return woo_j_get_price( $product['data'], $product['data']->get_price() );                
        } 
        
        $subtotal_notax	 = $product['line_subtotal'];
        $subtotal_tax    = ( WC()->cart->display_prices_including_tax() ) ? $this->getAllTaxItems( $product['line_tax_data']['subtotal'] ?? [] ) : 0;
        $subtotal        = $subtotal_notax + $subtotal_tax;
        
        $price = ( $subtotal / $product['quantity'] );	
       
        return round( $price, 2 );      

    }
    /*
    /* Calculate all taxes
    */
    private function getAllTaxItems( $tax_arr )
    {
        if( empty( $tax_arr ) ) return 0;

        $total = 0;

        foreach( $tax_arr as $tax_val )
        {
                $total += $tax_val;
        }

        return $total;
    }
    /*
    /* Calculate total price of compsite product
    */
    private function calcuateTotalPriceOfComposite( $product )
    {
        $childs = wc_cp_get_composited_cart_items( $product );

        $subtotal_notax	 = $product['line_subtotal'];
        $subtotal_tax    = ( WC()->cart->display_prices_including_tax() ) ? current( $product['line_tax_data']['subtotal'] ) : 0;
        $subtotal        = $subtotal_notax + $subtotal_tax;
        
        $price = ( $subtotal / $product['quantity'] );

        if( !empty( $childs ) ): 

                foreach( $childs as $child ): 

                        $subtotal_notax	 = $child['line_subtotal'];
                        $subtotal_tax    = ( WC()->cart->display_prices_including_tax() ) ? current( $child['line_tax_data']['subtotal'] ) : 0;
                        $subtotal        = $subtotal_notax + $subtotal_tax;
                        
                        $price          += ( $subtotal / $child['quantity'] );

                endforeach;


        endif;

        return $price;
    }
    /*
    /* Check if it's upsell price is discounted
    */
    public function hasDiscount()
    {
            return $this->actual_price != $this->base_price;
    }

    public function isValid()
    {
            return $this->valid;
    }
    /*
    /* Check if it's on sale ( WooCommerce )
    */
    public function onSale()
    {
            return $this->on_sale;
    }
    /*
    /* Check if it's an upsell product
    */
    public function isUpsell()
    {        
            return $this->type === 'upsell';        
    }
    /*
    /* Check if it's a gift product
    */
    public function isGift()
    {
            return $this->type === 'gift'; 
    }
    /*
    /* Check if it's woo bundle child
    */
    public function isWooBundleChild()
    {
        return $this->is_woobundle_child;
    }
    /*
    /* Check if it's woo bundle parent
    */
    public function isWooBundleParent()
    {       
        return $this->is_woobundle_parent;
    }
    /*
    /* Check if it's component child
    */
    public function isCompositeChild()
    {
            return $this->is_composite_child; 
    }
     /*
    /* Check if it's component parent
    */
    public function isCompositeParent()
    {
            return $this->is_composite_parent; 
    }
    /*
    /* Check if it's free
    */
    public function isFree()
    {
            if( $this->actual_price > 0 ) return false;
            return true;
    }
    /*
    /* Check if it's woocommerce product bundles child
    */
    public function isWCBundleChild()
    {
            return $this->is_woocbundle_child; 
    }
    /*
    /*
    */
    public function isParentOfCompositeOrBundle()
    {
        return $this->isWooBundleParent() || $this->isCompositeParent();
    }
    /*
    /*
    */
    public function isChildOfCompositeOrBundle()
    {
        return $this->isWooBundleChild() || $this->isCompositeChild() || $this->isWCBundleChild();
    }
    /*
    /* 
    */
    public function hasDisplayedPrice()
    {
        return !$this->isWCBundleChild();
    }

}
