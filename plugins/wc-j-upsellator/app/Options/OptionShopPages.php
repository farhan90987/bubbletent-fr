<?php

namespace WcJUpsellator\Options;
  
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class OptionShopPages extends BaseOption
{	
	
	const OPTION_NAME 		= 'woo_j_upsellator_shop_pages';
	
	public $single_product;
	public $loop_labels;
	public $single_product_text;
	public $single_product_text_hook;
	public $loop_label_hook;
	public $style;

	private $single_page_text_hooks;
	private $loop_label_hooks;
	
	public function __construct()
	{				
			$this->single_page_text_hooks =  ["woocommerce_before_add_to_cart_quantity" => __( 'Before add to cart qty', 'woo_j_cart' ),
											  "woocommerce_before_add_to_cart_form" => __( 'Before add to cart form', 'woo_j_cart' ),
											  "woocommerce_after_add_to_cart_form" => __( 'After add to cart form', 'woo_j_cart' ),
											  "woocommerce_product_meta_end" => __( 'Product meta list', 'woo_j_cart' )
											 ];

			$this->loop_label_hooks		  =  ["woocommerce_before_shop_loop_item_title" => __( 'Before loop title', 'woo_j_cart' ),
											  "woocommerce_after_shop_loop_item_title" => __( 'After loop title', 'woo_j_cart' ),
											  "woocommerce_after_shop_loop_item" => __( 'After loop item', 'woo_j_cart' ),
											  "woocommerce_before_shop_loop_item" => __( 'Before loop item', 'woo_j_cart' ),
											];								
	}

	public function getSingleTextHooks()
	{
			return $this->single_page_text_hooks;
	}

	public function getLoopLabelsHooks()
	{
			return $this->loop_label_hooks;
	}

	protected function load( $settings )
	{
			$this->loadSettings( $settings );
	}

	protected function translate()
	{
			//Nothing
	}

	protected function setDefaults()
	{			
			$this->loop_labels 					= false;
			$this->single_product 				= false;
			$this->single_product_text_hook 	= 'woocommerce_before_add_to_cart_form';
			$this->loop_label_hook 				= 'woocommerce_before_shop_loop_item_title';
			$this->style						= 'rotated';
		
			$this->save();	
	}

	public function setSinglePageTextHook( $hook )
	{

			$this->single_product_text_hook = "woocommerce_before_add_to_cart_form";

			if( array_key_exists ( $hook, $this->single_page_text_hooks ))
			{

				$this->single_product_text_hook = $hook;

			}
	}

	public function setLoopLabelHook( $hook )
	{

			$this->loop_label_hook = "woocommerce_before_shop_loop_item_title";
			
			if( array_key_exists ( $hook, $this->loop_label_hooks ))
			{

				$this->loop_label_hook = $hook;

			}
	}

	public function save()
	{

			$settings 								= [];
			$settings['single_product'] 			= $this->single_product;
			$settings['loop_labels'] 				= $this->loop_labels;
			$settings['loop_label_hook'] 			= $this->loop_label_hook;
			$settings['single_product_text'] 		= $this->single_product_text;
			$settings['single_product_text_hook']	= $this->single_product_text_hook;
			$settings['style']						= $this->style;						
			
			if( get_option( self::OPTION_NAME ) ) update_option( self::OPTION_NAME,  $settings, 'no' );				
			else 							      add_option( self::OPTION_NAME,  $settings, '', 'no' );				
			
			$this->load( $settings );
			
			return $settings;

	}

}
