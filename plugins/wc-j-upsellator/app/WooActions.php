<?php

namespace WcJUpsellator;

use  WcJUpsellator\Render ;
use  WcJUpsellator\Core\UpsellatorPreLoader ;
use  WcJUpsellator\Traits\TraitUpsellatorProduct ;
use  WcJUpsellator\Traits\TraitWooCommerceHelper ;
use  WcJUpsellator\Traits\TraitTestMode ;
use  WcJUpsellator\WooCommerceActions ;

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}

class WooActions
{
    use  TraitUpsellatorProduct ;
    use  TraitTestMode ;
    use  TraitWooCommerceHelper ;
    private  $ajax_response ;
    public function __construct()
    {
        /*
        /* Nothing to do
        */
    }
    
    public static function register()
    {
        $page = new self();
        /*
        /* Are we on test mode?
        */
        
        if ( $page->currentUserCan() ) {
            /*
            /* If modal cart is active
            */
            
            if ( !woo_j_conf( 'only_background' ) ) {
                add_action( 'wp_ajax_wc_timeline_update_qty', array( $page, 'wc_timeline_update_qty' ) );
                add_action( 'wp_ajax_nopriv_wc_timeline_update_qty', array( $page, 'wc_timeline_update_qty' ) );
                add_action( 'wp_ajax_wc_timeline_remove_item', array( $page, 'wc_timeline_remove_item' ) );
                add_action( 'wp_ajax_nopriv_wc_timeline_remove_item', array( $page, 'wc_timeline_remove_item' ) );
            }
            
            /*
            /* If coupon code is active in the modal cart
            */
            if ( woo_j_conf( 'coupon_code' ) ) {
                WooCommerceActions\CouponsActions::register();
            }
            add_filter( 'woocommerce_add_to_cart_fragments', array( $page, 'wc_timeline_fragments' ) );
            add_filter(
                'woocommerce_add_cart_item_data',
                array( $page, 'markAsUpsell' ),
                99,
                3
            );
            /*
            /* Gifts check
            */
            add_action( 'woocommerce_after_calculate_totals', array( $page, 'recalculateGifts' ), 100 );
            
            if ( woo_j_shop( 'loop_labels' ) ) {
                $action_l = woo_j_shop( 'loop_label_hook' ) ?? "woocommerce_before_shop_loop_item_title";
                add_action( $action_l, array( $page, 'showUpsellatorLabel' ), 20 );
            }
            
            /*
            /* Get correct return price
            */
            if ( woo_j_shop( 'single_product' ) ) {
                add_action( 'woocommerce_before_single_product_summary', array( $page, 'showUpsellatorLabel' ), 9999 );
            }
            
            if ( woo_j_cartpage( 'display_on_cartpage' ) ) {
                $action_cp = woo_j_cartpage( 'cartpage_upsell_position' ) ?? "woocommerce_cart_totals_before_shipping";
                add_action(
                    $action_cp,
                    array( $page, 'cartPageUpsell' ),
                    1,
                    1
                );
            }
            
            
            if ( woo_j_shop( 'single_product_text' ) ) {
                $action_s = woo_j_shop( 'single_product_text_hook' ) ?? "woocommerce_before_add_to_cart_form";
                add_action( $action_s, array( $page, 'showBeforeSingleAddToCart' ) );
            }
            
            if ( woo_j_conf( 'prevent_upsell_discount' ) == 1 ) {
                add_filter(
                    'woocommerce_coupon_is_valid_for_product',
                    array( $page, 'preventCouponDiscountForUpsells' ),
                    9999,
                    4
                );
            }
        }
        
        WooCommerceActions\CartItemsActions::register();
        /*
        /* Add a meta to the upselled/gifted products and calculate total
        */
        add_action(
            'woocommerce_checkout_create_order_line_item',
            array( $page, 'markOrderProducts' ),
            10,
            4
        );
        add_action(
            'woocommerce_checkout_create_order',
            array( $page, 'calculateTotalUpsell' ),
            20,
            2
        );
        add_action(
            'woocommerce_before_calculate_totals',
            array( $page, 'changeItemPrice' ),
            5500,
            1
        );
    }
    
    /*
    /* @hook woocommerce_coupon_is_valid_for_product
    /*
    /* Prevent discount from being applied to upsells products
    */
    public function preventCouponDiscountForUpsells(
        $valid,
        $product,
        $coupon,
        $values
    )
    {
        return $valid && !isset( $values['upsell'] );
    }
    
    /*
    /* @hook woocommerce_before_add_to_cart_form
    /*
    /* Show custom text before add to cart form, in the single product page 
    */
    public function showBeforeSingleAddToCart( $description )
    {
        global  $product ;
        
        if ( is_product() ) {
            $gifts = ( !empty($this->found_gift) ? $this->found_gift : $this->getRelatedGift( $product ) );
            foreach ( $gifts as $gift ) {
                
                if ( isset( $gift['single_product_text'] ) ) {
                    /*
                    /* Hooks
                    */
                    do_action( 'wjufw_before_single_product_gift_text', $gift['product_id'] );
                    echo  "<div class='upsellator-before-short-description'>" . $gift['single_product_text'] . "</div>" ;
                    /*
                    /* Hooks
                    */
                    do_action( 'wjufw_after_single_product_gift_text', $gift['product_id'] );
                }
            
            }
        }
    
    }
    
    /*
    /* @hook woocommerce_after_calculate_totals
    /*
    /* Re-calculate gifts and upsells
    /* We need this hook for full-cart-page consistency 
    */
    public function recalculateGifts( $cart )
    {
        $preLoader = new UpsellatorPreLoader();
        $gifts = new Gift( $preLoader );
        $gifts->check();
        $upsell = new Upsell( $preLoader );
        $upsell->check();
    }
    
    /*
    /* @hook woocommerce_before_shop_loop_item_title
    /* @hook woocommerce_before_single_product_summary
    /*
    /* If the admin activated the option, we show a custom badge
    /* when the current item trigger or may trigger a gift
    */
    public function showUpsellatorLabel()
    {
        global  $product ;
        $gift_labels = $this->getRelatedGift( $product );
        
        if ( !empty($gift_labels[0]['text']) ) {
            ?> 

                    <div class="woo-j-upsellator-labels <?php 
            echo  esc_attr( woo_j_shop( 'style' ) ) ;
            ?>"> 

                        <?php 
            foreach ( $gift_labels as $label ) {
                ?>

                                <div class="woo-j-upsellator-label <?php 
                echo  esc_attr( $label["type"] ) ;
                ?>">
                                        <?php 
                echo  $label['text'] ;
                ?>
                                </div>

                        <?php 
            }
            ?>

                    </div> 
                    <?php 
        }
    
    }
    
    /*
    /* @hook woocommerce_before_calculate_totals
    /*
    /* if the item is a gift, we change it's price to 0
    /* if it's an upsell, we change it's price to what admin set
    */
    public function changeItemPrice( $cart )
    {
        if ( is_admin() && !defined( 'DOING_AJAX' ) ) {
            return;
        }
        /*
        /* This check is to prevent infinite loops but, since we can add multiple gifts ad the same time
        /* we need to set this limit decently high
        */
        if ( did_action( 'woocommerce_before_calculate_totals' ) >= 20 ) {
            return;
        }
        foreach ( $cart->get_cart() as $cart_item ) {
            
            if ( !empty($cart_item['upsell']) ) {
                $cart_item['data']->set_price( $cart_item['custom_price'] );
                $backend_upsell = woo_j_upsell( 'products' )[$cart_item['data']->get_id()] ?? null;
                if ( !$backend_upsell ) {
                    return;
                }
                /*
                /* If upsell doesn't allow qty change, we set it to the default value
                */
                
                if ( !isset( $backend_upsell['quantity_change'] ) || !$backend_upsell['quantity_change'] ) {
                    $qty = $backend_upsell['quantity'] ?? 1;
                    if ( $cart_item['quantity'] != $qty ) {
                        $cart->set_quantity( $cart_item['key'], $qty );
                    }
                } else {
                    if ( isset( $cart_item['custom_max_qty'] ) && $cart_item['custom_max_qty'] < $cart_item['quantity'] ) {
                        $cart->set_quantity( $cart_item['key'], $cart_item['custom_max_qty'] );
                    }
                }
            
            }
            
            if ( isset( $cart_item['gift'] ) ) {
                /*
                /* 0 may cause issues with some themes/plugins
                */
                $cart_item['data']->set_price( 1.0E-6 );
            }
        }
    }
    
    public function checkoutUpsellFragment( $fragments )
    {
        //Nothing to do
        return $fragments;
    }
    
    /*
    /* Inject div for fragments
    */
    public function cartPageUpsell()
    {
        ?> <div class="wc-timeline-cart-upsell"></div> <?php 
    }
    
    public function checkoutUpsell()
    {
        //Nothing to do
    }
    
    /*
    /* @hook woocommerce_add_cart_item_data
    /*
    /* before adding an item to cart
    /* We mark a specific product as sold via Upsell ( if needed )
    /* and calculate product price based on it's set discount    
    */
    public function markAsUpsell( $cart_item_data, $product_id, $variation_id )
    {
        $is_upsell = ( !empty($_POST['upsell']) ? 1 : 0 );
        $upsell_type = ( isset( $_POST['upsellType'] ) ? sanitize_text_field( $_POST['upsellType'] ) : '' );
        $id = ( $variation_id > 0 ? $variation_id : $product_id );
        
        if ( $is_upsell && empty($cart_item_data['gift']) && isset( woo_j_upsell( 'products' )[$id] ) ) {
            $backend_upsell = woo_j_upsell( 'products' )[$id];
            $cart_item_data['upsell'] = $is_upsell;
            $cart_item_data['upsell_type'] = $upsell_type;
            $cart_item_data['custom_price'] = apply_filters( 'wjufw_upsell_set_price', $this->calculateNewPrice( $id, $backend_upsell ) );
            if ( $backend_upsell['quantity_change'] && isset( $backend_upsell['quantity_change_max'] ) && $backend_upsell['quantity_change_max'] > 0 ) {
                $cart_item_data['custom_max_qty'] = (int) $backend_upsell['quantity_change_max'];
            }
        }
        
        /*
        /* Advanced product fields for woocommerce fix
        */
        
        if ( !empty($cart_item_data['gift']) || $is_upsell ) {
            unset( $cart_item_data['wapf'] );
            unset( $cart_item_data['wapf_key'] );
            unset( $cart_item_data['wapf_field_groups'] );
            unset( $cart_item_data['wapf_clone'] );
        }
        
        return $cart_item_data;
    }
    
    private function calculateNewPrice( $id, $upsell_product )
    {
        $product = wc_get_product( $id );
        $price = get_post_meta( $id, '_regular_price', true );
        if ( $upsell_product['discount_type'] == 1 ) {
            return round( $price - $price / 100 * $upsell_product['discount'], 2 );
        }
        if ( $upsell_product['discount_type'] == 0 ) {
            return $price - $upsell_product['discount'];
        }
        if ( $upsell_product['discount_type'] == 2 ) {
            return $product->get_price();
        }
    }
    
    /*  
    /* @hook woocommerce_checkout_create_order_line_item
    /*
    /* Before checkout, we add custom meta to the order items that are upsells or gifts  
    /* If they are upsell, we track them 
    */
    public function markOrderProducts(
        $item,
        $cart_item_key,
        $values,
        $order
    )
    {
        
        if ( !empty($values['upsell']) && $values['upsell'] == 1 ) {
            $item->add_meta_data( '_woo_j_upsellator_upsell', '<b>Upsell</b>' );
            $item->add_meta_data( '_woo_j_upsellator_upsell_type', $values['upsell_type'] );
        }
        
        if ( !empty($values['gift']) && $values['gift'] == 1 ) {
            $item->add_meta_data( '_woo_j_upsellator_gift', '<b>Gift</b>' );
        }
    }
    
    /*  
    /* @hook woocommerce_checkout_create_order
    /*
    /* After checkout, we calculate the total amount sold thanks to our upsells
    /* and add that to the order meta
    */
    public function calculateTotalUpsell( $order, $data )
    {
        $items = $order->get_items();
        $total_upsell = 0;
        foreach ( $items as $item ) {
            if ( $item->get_meta( '_woo_j_upsellator_upsell' ) ) {
                $total_upsell += $item->get_total();
            }
        }
        if ( $total_upsell ) {
            $order->update_meta_data( '_total_upsell', round( $total_upsell, 2 ) );
        }
    }
    
    public function wc_timeline_update_qty()
    {
        
        if ( isset( $_POST['key'], $_POST['qty'] ) ) {
            $qty = (double) $_POST['qty'];
            $key = sanitize_text_field( $_POST['key'] );
            
            if ( $qty > 0 ) {
                WC()->cart->set_quantity( $key, $qty );
            } else {
                WC()->cart->remove_cart_item( $key );
            }
            
            wp_send_json( true );
        }
    
    }
    
    public function wc_timeline_remove_item()
    {
        
        if ( isset( $_POST['key'] ) ) {
            $key = sanitize_text_field( $_POST['key'] );
            WC()->cart->remove_cart_item( $key );
            wp_send_json( true );
        }
    
    }
    
    /*  
    /* @hook woocommerce_add_to_cart_fragments
    /*
    /* We add our custom fragments to the woocommerce one  
    */
    public function wc_timeline_fragments( $fragments )
    {
        $total = WC()->cart->get_cart_contents_count();
        $current_page = (int) $_REQUEST['jcart_page_id'];
        $cart_page_id = wc_get_page_id( 'cart' );
        $cart_page_check = woo_j_cartpage( 'display_on_cartpage' ) && $cart_page_id == $current_page;
        if ( woo_j_conf( 'clear_fragments' ) ) {
            $fragments = [];
        }
        
        if ( woo_j_conf( 'upsells_shortcode' ) || $cart_page_check ) {
            ob_start();
            ( new Render\UpsellsBlock() )->render( 'cart' );
            $fragments['.wc-timeline-cart-upsell'] = ob_get_clean();
        }
        
        ob_start();
        ( new Render\ItemsList() )->render();
        $fragments['.wc-items-container'] = ob_get_clean();
        ob_start();
        ( new Render\TotalPlaceholder() )->render();
        $fragments['.wcjfw-total-placeholder'] = ob_get_clean();
        ob_start();
        ( new Render\ModalFooter() )->render();
        $fragments['.wc-timeline-footer'] = ob_get_clean();
        ob_start();
        ( new Render\CountButton() )->render();
        $fragments['.wc-timeline-button-show-cart'] = ob_get_clean();
        ob_start();
        woo_j_price( $this->getSubtotal(), [ 'wcj-shortcode-cart-total' ] );
        $fragments['.wcj-shortcode-cart-total'] = ob_get_clean();
        
        if ( $total > 0 ) {
            $fragments['.wc-j-upsellator-cart-count'] = "<div class=\"wc-j-upsellator-cart-count\">" . WC()->cart->get_cart_contents_count() . "</div>";
        } else {
            $fragments['.wc-j-upsellator-cart-count'] = "<div class=\"wc-j-upsellator-cart-count\"></div>";
        }
        
        return $fragments;
    }

}