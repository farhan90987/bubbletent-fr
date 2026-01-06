<?php 

namespace WcJUpsellator\WooCommerceActions;

class CartItemsActions
{    

    public static function register()
    {
            $page = new self();	
           /*
           /* Add labels and prevent quantity change on checkout if gifts or upsells
           */            
           add_filter( 'woocommerce_cart_item_quantity', array( $page,'editCartPageQuantity' ), 99, 3 );
           add_filter( 'woocommerce_cart_item_name', array( $page,'editCartPageName' ), 99, 2 );           
           add_filter( 'woocommerce_cart_item_class', array( $page,'editCartPageItemClasses' ), 99, 3 ); 
           add_filter( 'woocommerce_cart_item_price', array( $page,'editCartPageItemPrice' ), 9999, 3 );
           add_filter( 'woocommerce_cart_item_subtotal',  array( $page,'editCartPageItemPrice' ), 9999, 3 ); 

           if( woo_j_conf('upsell_discount_subtotal') == 1 )
           {
                add_filter( 'woocommerce_cart_item_subtotal' , array( $page,'showDiscountFromUpsell' ), 99, 3 );
           }  
    }
    /*
    /* @hook woocommerce_cart_item_name
    /*
    /* if the item is upselled or gifted, 
    /* we add a string before the name
    */
    public function editcartPageName( $item_name,  $cart_item )
    {
        if( !is_cart() ) return $item_name;

        if( !empty( $cart_item['gift'] ) )
        {     
                $item_name = "<span class='woo-j-cart-name gift'>".wjc__( esc_html( woo_j_conf('label_gift') ) )."</span><br>". $item_name;       
        }

        if( !empty( $cart_item['upsell'] ) )
        {
                $item_name = "<span class='woo-j-cart-name upsell'>". wjc__( esc_html( woo_j_conf('label_upsell') ) ). "</span><br>". $item_name; 
        }

        return $item_name;
    }
     /*
    /* @hook woocommerce_cart_item_class
    /*
    /* if the item is upselled or gifted, 
    /* we add a class to help working on them
    */
    public function editCartPageItemClasses( $class, $cart_item, $cart_item_key )
    {
        if( !empty( $cart_item['gift'] ) )
        {     
                $class .= " woo-j-gift";       
        }

        if( !empty( $cart_item['upsell'] ) )
        {
                $class .= " woo-j-upsell";  
        }
        
        return $class;
    }
     /*
    /* @hook woocommerce_cart_item_quantity
    /*
    /* if the item is upselled or gifted, 
    /* we do not allow to change quantities in the cart page
    */
    public function editCartPageQuantity( $product_quantity, $cart_item_key,  $cart_item )
    {        

        if( empty( $cart_item['gift'] ) && empty( $cart_item['upsell'] ) )  return $product_quantity;
       
        if( !empty( $cart_item['upsell'] ) )
        {                
                $id      = $cart_item['variation_id'] > 0 ? $cart_item['variation_id'] : $cart_item['product_id'];
                $upsell  = woo_j_upsell('products')[ $id ];

                if( !$upsell ) return $product_quantity;                 
                
                if( $upsell['quantity_change'] )
                {
                        return $product_quantity;
                } 
        }
        
        return "<div class='quantity'>".esc_html( $cart_item['quantity'] ) ."<input type='hidden' value='". esc_attr( $cart_item['quantity'] ) ."' /></div>"; 
        
    } 
     /*
    /* @hook woocommerce_cart_item_subtotal
    /*
    /* If the product is an upsell, we display the discount if exists
    */
    public function showDiscountFromUpsell( $line_total, $cart_item, $cart_item_key )
    {       
        /*
        /* Currently not working with taxes
        */
        if( !isset( $cart_item['upsell'] ))                                                            return $line_total;
        if( !wc_prices_include_tax() || ! WC()->cart->display_prices_including_tax())                  return $line_total;
        
        $prod_price = $cart_item['data']->is_on_sale() ? $cart_item['data']->get_sale_price() : $cart_item['data']->get_regular_price() ;
        $real_total = $prod_price  * $cart_item['quantity'];
        $cur_price  = $cart_item['data']->get_price() * $cart_item['quantity'];             
        
        if( $real_total <= $cur_price ) return $line_total;  
               
        return "<div class='j-upsellator-subotal-better'>". wc_price( $cur_price )."<br>
        <span class='j-upsellator-discounted'>".wc_price( $real_total )."</span></div>";        
         
    } 
     /*
    /* @hook woocommerce_cart_item_price
    /*
    /* If the product is an upsell, we display the discount if exists
    */
    public function editCartPageItemPrice( $value, $cart_item, $cart_item_key )
    {
        if( empty( $cart_item['gift'] ) ) return $value;

        return wc_price( 0 );
        
    }
}