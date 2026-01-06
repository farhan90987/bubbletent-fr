<?php

namespace WcJUpsellator\Options;

use  WcJUpsellator\Traits\TraitWordpress ;

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}

class OptionConfig extends BaseOption
{
    use  TraitWordpress ;
    const  OPTION_NAME = 'woo_j_upsellator_settings' ;
    public  $open_on_add ;
    public  $label_on_sale ;
    public  $single_page_ajax ;
    public  $text_add_to_cart ;
    public  $text_go_to_product ;
    public  $text_checkout ;
    public  $text_free_product ;
    public  $text_empty_text ;
    public  $text_empty_heading ;
    public  $text_empty_button ;
    public  $force_recalculate_totals ;
    public  $currency_position ;
    public  $shipping_total ;
    public  $page_scroll ;
    public  $labe_gift ;
    public  $label_upsell ;
    public  $display_on_admin_order ;
    public  $shop_labels ;
    public  $position ;
    public  $shop_url ;
    public  $empty_cart_icon ;
    public  $test_mode ;
    public  $cart_button ;
    public  $text_cart_button ;
    public  $text_header ;
    public  $clear_fragments ;
    public  $upsells_shortcode ;
    public  $dynamic_bar_shortcode ;
    public  $qty_hidden ;
    public  $label_gift ;
    public  $only_background ;
    public  $prevent_upsell_discount ;
    public  $modal_theme ;
    public  $logo_attachment_id ;
    public  $subtotal_vat_excluded ;
    public  $modal_upsell_type ;
    public  $modal_upsell_max_displayed ;
    public  $upsells_random_order ;
    public  $upsells_label_no_discount ;
    public  $footer_items_count ;
    public  $upsell_discount_subtotal ;
    public  $modal_cart_notices ;
    public  $modal_cart_notices_timeout ;
    public  $coupon_code ;
    private  $icons = array(
        'wooj-icon-empty-cart',
        'wooj-icon-basket-alt',
        'wooj-icon-basket-1',
        'wooj-icon-basket',
        'wooj-icon-opencart',
        'wooj-icon-shopping-bag',
        'wooj-icon-bag',
        'wooj-icon-briefcase',
        'wooj-icon-comment'
    ) ;
    private  $modal_styles = array( 'default', 'logo' ) ;
    protected  $translatable = array(
        'text_free_product',
        'text_go_to_product',
        'label_gift',
        'label_upsell',
        'label_on_sale',
        'text_header',
        'text_checkout',
        'text_cart_button',
        'text_empty_button',
        'text_add_to_cart',
        'text_empty_button',
        'text_empty_text',
        'text_empty_heading'
    ) ;
    public function __construct()
    {
    }
    
    public function getIcons()
    {
        return $this->icons;
    }
    
    public function getStyles()
    {
        return $this->modal_styles;
    }
    
    public function preSet()
    {
        $settings = get_option( static::OPTION_NAME );
        if ( !empty($settings) ) {
            foreach ( $settings as $key => $value ) {
                $this->{$key} = $value;
            }
        }
    }
    
    protected function load( $settings )
    {
        $this->loadSettings( $settings );
        woo_j_conf( 'currency', get_woocommerce_currency_symbol() );
    }
    
    protected function translate()
    {
        $this->loadTranslations();
    }
    
    protected function setDefaults()
    {
        $this->open_on_add = 1;
        $this->single_page_ajax = false;
        $this->page_scroll = false;
        $this->test_mode = false;
        $this->clear_fragments = false;
        $this->subtotal_vat_excluded = false;
        $this->qty_hidden = false;
        $this->cart_button = 1;
        $this->text_cart_button = "Your cart";
        $this->display_on_admin_order = false;
        $this->logo_attachment_id = false;
        $this->upsells_shortcode = false;
        $this->dynamic_bar_shortcode = false;
        $this->only_background = false;
        $this->force_recalculate_totals = false;
        $this->currency_position = false;
        $this->shipping_total = false;
        $this->coupon_code = false;
        $this->modal_upsell_type = 0;
        $this->modal_upsell_max_displayed = 5;
        $this->upsells_label_no_discount = true;
        $this->upsells_random_order = false;
        $this->shop_labels = false;
        $this->empty_cart_icon = 'wooj-icon-empty-cart';
        $this->label_on_sale = "on sale";
        $this->label_gift = "gift";
        $this->label_upsell = "special offer";
        $this->text_header = __( 'Shopping cart', 'woo_j_cart' );
        $this->text_add_to_cart = "add";
        $this->text_go_to_product = "view";
        $this->text_checkout = "Checkout order";
        $this->text_free_product = "Free";
        $this->text_empty_text = "Let's start shopping!";
        $this->text_empty_heading = "Your cart is empty";
        $this->text_empty_button = "Start shopping";
        $this->footer_items_count = 1;
        $this->modal_cart_notices = false;
        $this->modal_cart_notices_timeout = 7;
        $this->position = "right";
        $this->shop_url = wc_get_page_id( 'shop' );
        $this->modal_theme = 'default';
        $this->upsell_discount_subtotal = false;
        $this->save();
    }
    
    public function save()
    {
        $settings = [];
        $settings['open_on_add'] = $this->open_on_add;
        $settings['label_on_sale'] = $this->label_on_sale;
        $settings['label_gift'] = $this->label_gift;
        $settings['empty_cart_icon'] = $this->empty_cart_icon;
        $settings['label_upsell'] = $this->label_upsell;
        $settings['text_add_to_cart'] = $this->text_add_to_cart;
        $settings['text_go_to_product'] = $this->text_go_to_product;
        $settings['text_checkout'] = $this->text_checkout;
        $settings['single_page_ajax'] = $this->single_page_ajax;
        $settings['text_free_product'] = $this->text_free_product;
        $settings['text_empty_text'] = $this->text_empty_text;
        $settings['text_empty_heading'] = $this->text_empty_heading;
        $settings['text_empty_button'] = $this->text_empty_button;
        $settings['page_scroll'] = $this->page_scroll;
        $settings['test_mode'] = $this->test_mode;
        $settings['upsells_shortcode'] = $this->upsells_shortcode;
        $settings['dynamic_bar_shortcode'] = $this->dynamic_bar_shortcode;
        $settings['clear_fragments'] = $this->clear_fragments;
        $settings['cart_button'] = $this->cart_button;
        $settings['text_cart_button'] = $this->text_cart_button;
        $settings['logo_attachment_id'] = $this->logo_attachment_id;
        $settings['subtotal_vat_excluded'] = $this->subtotal_vat_excluded;
        $settings['prevent_upsell_discount'] = $this->prevent_upsell_discount;
        $settings['shipping_total'] = $this->shipping_total;
        $settings['modal_upsell_type'] = $this->modal_upsell_type;
        $settings['modal_upsell_max_displayed'] = ( $this->modal_upsell_max_displayed >= 0 ? $this->modal_upsell_max_displayed : 1 );
        $settings['upsells_random_order'] = $this->upsells_random_order;
        $settings['upsells_label_no_discount'] = $this->upsells_label_no_discount;
        $settings['coupon_code'] = $this->coupon_code;
        $settings['modal_theme'] = $this->modal_theme;
        $settings['text_header'] = $this->text_header;
        $settings['currency_position'] = $this->currency_position;
        $settings['force_recalculate_totals'] = $this->force_recalculate_totals;
        $settings['only_background'] = $this->only_background;
        $settings['footer_items_count'] = $this->footer_items_count;
        $settings['modal_cart_notices'] = $this->modal_cart_notices;
        $settings['modal_cart_notices_timeout'] = $this->modal_cart_notices_timeout;
        
        if ( $settings['only_background'] == 1 ) {
            $settings['page_scroll'] = 0;
            $settings['open_on_add'] = 0;
            $settings['single_page_ajax'] = 0;
            $settings['clear_fragments'] = 0;
        }
        
        $settings['shop_labels'] = $this->shop_labels;
        $settings['position'] = $this->position;
        $settings['shop_url'] = $this->shop_url;
        $settings['upsell_discount_subtotal'] = $this->upsell_discount_subtotal;
        
        if ( get_option( self::OPTION_NAME ) ) {
            update_option( self::OPTION_NAME, $settings, 'no' );
        } else {
            add_option(
                self::OPTION_NAME,
                $settings,
                '',
                'no'
            );
        }
        
        $this->load( $settings );
        return $settings;
    }

}