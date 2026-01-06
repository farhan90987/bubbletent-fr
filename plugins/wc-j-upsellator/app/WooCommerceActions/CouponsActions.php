<?php 

namespace WcJUpsellator\WooCommerceActions;

use WcJUpsellator\Core\AjaxNotification;

class CouponsActions
{
    private $state = 'error';

    public static function register()
    {
            $page = new self();	
            /*
            /* If coupon code is active in the modal cart
            */   
            add_action( 'wp_ajax_nopriv_wc_timeline_apply_coupon', array( $page, 'apply_coupon' ) ); 
            add_action( 'wp_ajax_wc_timeline_apply_coupon', array( $page, 'apply_coupon' ) ); 
            add_action( 'wp_ajax_nopriv_wc_timeline_remove_coupon', array( $page, 'remove_coupon' ) ); 
            add_action( 'wp_ajax_wc_timeline_remove_coupon', array( $page, 'remove_coupon' ) );             
    }
    /*
    /* Apply coupon code to cart
    */
    public function apply_coupon()
    {
      
        $coupon_code = ( isset( $_POST['coupon'] ) ? wc_format_coupon_code( wp_unslash( $_POST['coupon'] ) ) : false );         

        if ( $coupon_code ) {
            
            if ( !WC()->cart->has_discount( $coupon_code ) ) {
                
                if ( WC()->cart->apply_coupon( $coupon_code ) ) 
                {                        
                        $message        = __( 'Coupon code applied successfully.', 'woocommerce' );
                        $this->state    = 'success';                       

                } else {

                    $coupon     = new \WC_Coupon( $coupon_code );
                    $discounts  = new \WC_Discounts( WC()->cart );
                    $valid      = $discounts->is_coupon_valid( $coupon );
                    
                    if ( is_wp_error( $valid ) ) 
                    {                       
                        $message = $valid->get_error_message();                                               
                    }
                    
                    elseif ( $valid ) 
                    {  
                        $message = sprintf( __( 'Sorry, coupon "%s" has already been applied and cannot be used in conjunction with other coupons.', 'woocommerce' ), esc_html( $coupon_code ) );                    

                    } else 
                    {  
                        $message = __( 'Invalid coupon code', 'woocommerce' );                      
                    }
                
                }
            
            } else {               
               
                $message = __( 'Coupon code already applied!', 'woocommerce' );          
            }
        
        } else {           
               
                $message = __( 'Invalid coupon code', 'woocommerce' ); 
                
        } 
        
        ( new AjaxNotification() )->throw( $message, $this->state );        
        
    }  
    /*
    /* Remove coupon code from cart
    */
    public function remove_coupon()
    {
        $coupon      = ( isset( $_POST['coupon'] ) ? wc_format_coupon_code( wp_unslash( $_POST['coupon'] ) ) : false );        

        if ( empty($coupon) ) 
        {
                $message = __( 'There was a problem removing this coupon.', 'woo-j-cart' );

        } else {
                WC()->cart->remove_coupon( $coupon );
                $this->state    = 'success';    
                $message        = __( 'Coupon has been removed.', 'woocommerce' );
        }
            
        ( new AjaxNotification() )->throw( $message, $this->state );  
    }
   
}