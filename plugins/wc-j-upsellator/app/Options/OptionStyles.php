<?php

namespace WcJUpsellator\Options;
  
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class OptionStyles extends BaseOption
{	
	
	const OPTION_NAME 		= 'woo_j_upsellator_styles';
	
	public $background_color;
	public $button_color;
	public $button_color_hover;
	public $button_font_color;
	public $button_font_color_hover;
	public $font_color;
	public $item_count_background;
	public $item_count_color;  
	public $gift_color;
	public $gift_text_color;
	public $upsell_color;
	public $upsell_text_color;
	public $modal_icon;	
	public $theme;
	public $gift_icon;
	public $base_font_size;
	public $modal_close_background;
	public $modal_close_text;	
	public $image_ratio;
	
	private $icons 			= ['wooj-icon-basket-alt','wooj-icon-basket-1', 'wooj-icon-basket', 'wooj-icon-opencart', 'wooj-icon-shopping-bag', 'wooj-icon-bag', 'wooj-icon-briefcase','wooj-icon-comment'];
	private $themes 		= ['default', 'small'];

	public function __construct()
	{				
			/*
			/* Nothing to do
			*/ 
	}

	public function getIcons()
	{
		return $this->icons;
	}

	public function getThemes()
	{
		return $this->themes;
	}

	public function setTheme( $type )
	{
			$this->theme = "standard";
			
			if( in_array ( $type, $this->themes ))
			{				
				$this->theme = $type;

			}
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
			$this->background_color 			= '#FFFFFF';
			$this->button_color 				= '#3E3E3E';
			$this->button_font_color			= '#FFFFFF';
			$this->button_color_hover			= '#FFFFFF';
			$this->button_font_color_hover		= '#3E3E3E';
			$this->image_ratio				    = '1.2';
			$this->font_color					= '#3E3E3E';
			$this->modal_close_background		= '#3E3E3E';
			$this->item_count_background 		= '#ffb000';
			$this->item_count_color 			= '#FFFFFF';			
			$this->gift_color 					= '#ffb000';
			$this->gift_text_color 				= '#FFFFFF';
			$this->upsell_color 				= '#ffb000';
			$this->upsell_text_color			= '#FFFFFF';
			$this->modal_close_text				= '#FFFFFF';
			$this->base_font_size				= '15';
			$this->modal_icon					= 'wooj-icon-basket-1';		
			$this->theme						= 'standard';					
		
			$this->save();	
	}

	public function save()
	{

			$settings 								= [];

			$settings['background_color'] 			= $this->background_color;
			$settings['button_color'] 				= $this->button_color;
			$settings['button_font_color_hover'] 	= $this->button_font_color_hover;
			$settings['button_color_hover'] 		= $this->button_color_hover;
			$settings['button_font_color'] 			= $this->button_font_color;
			$settings['font_color'] 				= $this->font_color;
			$settings['item_count_background'] 		= $this->item_count_background;
			$settings['item_count_color'] 			= $this->item_count_color;		
			$settings['modal_icon'] 				= $this->modal_icon;
			$settings['gift_color']					= $this->gift_color;
			$settings['gift_text_color']			= $this->gift_text_color; 
			$settings['upsell_color']				= $this->upsell_color; 
			$settings['image_ratio']				= $this->image_ratio; 
			$settings['upsell_text_color']			= $this->upsell_text_color;
			$settings['modal_close_background']		= $this->modal_close_background; 
			$settings['modal_close_text']			= $this->modal_close_text;
			$settings['base_font_size']				= $this->base_font_size;
			$settings['theme']						= $this->theme;
			  						
			
			if( get_option( self::OPTION_NAME ) ) update_option( self::OPTION_NAME,  $settings, 'no' );				
			else 							      add_option( self::OPTION_NAME,  $settings, '', 'no' );				
			
			$this->load( $settings );
			
			return $settings;

	}

}
