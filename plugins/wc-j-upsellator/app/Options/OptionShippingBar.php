<?php

namespace WcJUpsellator\Options;

use WcJUpsellator\Core\Conf;
  
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class OptionShippingBar extends BaseOption
{	
	
	const OPTION_NAME 		  = 'woo_j_upsellator_shipping_bar';	
		
	public $shipping_timeline;	
	public $success_text;
	public $s_b_bar_background;
	public $s_b_success_background;
	public $s_b_bar_background_empty;
	public $shipping_bar_type;

	public $icons 			  = [];
	public $goals 			  = [];	
	protected $translatable   = ['success_text'];
	

	public function __construct()
	{				
		/*
		/* Nothing to do
		*/ 		
	} 
	/*
	/* Icons
	*/
	private function loadIcons()
	{
		$this->icons = [
				'wooj-icon-gift' 		=> __('gift', 'woo_j_cart'), 
				'wooj-icon-flight' 		=> __('flight', 'woo_j_cart'), 
				'wooj-icon-truck' 		=> __('truck', 'woo_j_cart'),  
				'wooj-icon-bicycle'		=> __('bicycle', 'woo_j_cart'), 
				'wooj-icon-camera' 		=> __('camera', 'woo_j_cart'), 
				'wooj-icon-fast-food' 	=> __('hamburger', 'woo_j_cart'), 
				'wooj-icon-videocam' 	=> __('videocamera', 'woo_j_cart'), 
				'wooj-icon-tablet' 		=> __('tablet', 'woo_j_cart'), 
				'wooj-icon-thumbs-up' 	=> __('thumbs up', 'woo_j_cart'), 
				'wooj-icon-heart' 		=> __('heart', 'woo_j_cart'), 
		];
	}

	protected function setDefaults()
	{	
			$this->shipping_timeline			= false;	
			$this->success_text					= "You have reached <b>free shipping!</b>";

			$this->s_b_bar_background			= '#0a0a0a';
			$this->s_b_success_background		= '#9dc192';			
			$this->shipping_bar_type			= 'line2';
			$this->s_b_bar_background_empty		= '#fbfbfb';

			$this->save();
	}

	public function getShippingIcons()
	{
		$this->loadIcons();

		return $this->icons;
	}

	protected function load( $settings )
	{		
		$this->loadSettings( $settings );
	}

	protected function translate()
	{
		$this->loadTranslations();

		$goals = Conf::get( str_replace('woo_j_upsellator_', '', $this::OPTION_NAME ), 'goals' );
			
		if( empty( $goals ) ) return;

		$counter = 1;

		foreach( $goals as $goal )
		{
			wjc__addStringTranslation( 'shipping_bar_goal_' .$counter , $goal['text'] );				
			$counter++;
		}
	}
	/*
	/* Save goals array
	*/
	public function saveGoals( $array )
	{
		$count = 0;

		if( empty( $array ) ) return;

		$this->loadIcons();

		foreach( $array['limit'] as $limit )
		{	
			
			$newGoal 			= [];
			$newGoal['limit'] 	= is_numeric( $limit ) ? (float)$limit  : 0;
			$newGoal['text'] 	= isset( $array['text'][ $count ] ) ? wp_kses( wp_unslash( $array['text'][ $count ]  ), woo_j_string_filter() ) : ""; 
			$newGoal['icon']	= isset( $array['icon'][ $count ] ) && array_key_exists( $array['icon'][ $count ], $this->icons ) ? $array['icon'][ $count ] : 'wooj-icon-gift' ;
			$count++;

			$this->goals[] 	= $newGoal;
			 
		}
		/*
		/* Order by goal limit
		*/
		usort( $this->goals, function ($item1, $item2) {
				return $item1['limit'] <=> $item2['limit'];
		});		
	}

	public function save()
	{

			$settings 						  		= [];
			$settings['shipping_timeline'] 	  		= $this->shipping_timeline;				
			$settings['success_text']		  		= $this->success_text;

			$settings['goals']	  					= $this->goals;			
			
			$settings['s_b_bar_background'] 		= $this->s_b_bar_background;
			$settings['s_b_bar_background_empty'] 	= $this->s_b_bar_background_empty;
			$settings['s_b_success_background'] 	= $this->s_b_success_background;
			
			$settings['shipping_bar_type']			= $this->shipping_bar_type;
			
			if( get_option( self::OPTION_NAME ) ) update_option( self::OPTION_NAME,  $settings, 'no' );				
			else 							      add_option( self::OPTION_NAME,  $settings, '', 'no' );				
			
			$this->load( $settings );
			
			return $settings;

	}	
	
}
