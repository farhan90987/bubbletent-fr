<?php

namespace WcJUpsellator;

use WcJUpsellator\Traits\TraitExclusion;
use WcJUpsellator\Traits\TraitAdmin;
use WcJUpsellator\Traits\TraitTestMode;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class JsScripts
{

	use TraitExclusion;
	use TraitAdmin;
	use TraitTestMode;

	public function __construct()
	{
		
	}

	public function loadFrontend()
	{
			add_action( 'wp', array( $this, 'frontendJS' ) );
	}

	public function loadAdmin()
    {	
			if( is_admin() )
			{	
					add_action( 'admin_enqueue_scripts', [ $this, 'adminScripts' ] );	
					
			} 
	}
	
	public function frontendJS()
	{
			if( !$this->pageExcluded() && $this->currentUserCan() )            
			{				
				if( !woo_j_conf('only_background') )
				{						
						add_action( 'wp_enqueue_scripts', [ $this, 'singlePageAjax' ] );
				}
				
				add_action( 'wp_enqueue_scripts', [ $this, 'lineProgressBar' ] );
				add_action( 'wp_enqueue_scripts', [ $this, 'customJs' ] );				
				

			}else{

				add_action( 'wp_enqueue_scripts', [ $this, 'inlineRedirectToCart' ] );

			}
	}

	public function inlineRedirectToCart()
	{
			wp_register_script('wc_j_upsellator_redirect_js', WC_J_UPSELLATOR_PLUGIN_URL.'assets/frontend/js/upsellator.redirect.min.js', array( 'jquery' ), '5.8', true );
			wp_localize_script('wc_j_upsellator_redirect_js', 'wc_timeline', array('cart_url' => wc_get_cart_url()  ) );
			wp_enqueue_script('wc_j_upsellator_redirect_js');
	}
	
	public function customJs()
	{		
			wp_register_script('wc_j_upsellator_js', WC_J_UPSELLATOR_PLUGIN_URL.'assets/frontend/js/upsellator.js', array( 'jquery' ), WC_J_UPSELLATOR_VERSION, true );
			wp_localize_script('wc_j_upsellator_js', 'wc_timeline', array( 'url' => admin_url( 'admin-ajax.php' ), 
																	       'open_on_add' => woo_j_conf('open_on_add'), 
																		   'cart_url' => wc_get_cart_url(),
																		   'is_cart_page' => is_cart() && woo_j_cartpage('display_on_cartpage'),
																		   'goals_count' =>  woo_j_shipping('goals') ? count( woo_j_shipping('goals') ) : 0,																		 
																		   'has_carousel' => woo_j_checkout('checkout_upsell_type') == 2 || woo_j_conf('modal_upsell_type') == 2 || woo_j_cartpage('cartpage_upsell_type') == 2,																			   
																		));
			wp_enqueue_script('wc_j_upsellator_js');
			wp_enqueue_script( 'wc-cart-fragments' );
			
	}

	public function singlePageAjax()
	{
			if ( is_singular('product') && woo_j_conf('single_page_ajax') ) 
			{				
				wp_enqueue_script('wc_j_upsellator_ajax_single_page', WC_J_UPSELLATOR_PLUGIN_URL.'assets/frontend/js/single-page-ajax.min.js', array( 'jquery' ), WC_J_UPSELLATOR_VERSION, true );
			}			
	}
	
	public function lineProgressBar()
	{		
		if( !woo_j_shipping('goals') || ( !woo_j_shipping('shipping_timeline') && !woo_j_conf('dynamic_bar_shortcode') ) ) return;
			
			wp_register_script('wc_j_upsellator_progress_bar', WC_J_UPSELLATOR_PLUGIN_URL.'assets/frontend/js/upsellator-bar.js', array( 'jquery' ), WC_J_UPSELLATOR_VERSION, true );
			wp_localize_script('wc_j_upsellator_progress_bar', 'wc_shipping_bar', array( 'currency' => woo_j_conf('currency'), 
																						  'decimals' => wc_get_price_decimals(), 	 
																				  		  'goals' => wjc__loadSubArrTranslations( woo_j_shipping('goals'), ['text'] ), 	
																						  'success_text' =>  wjc__( woo_j_shipping('success_text') )
																					   ));
			wp_enqueue_script('wc_j_upsellator_progress_bar');
			

	}

	public function adminScripts( $hook )
	{		
		$tab = sanitize_text_field( $_GET['tab'] ?? '' );
		
		if( $hook && strpos( $hook, $this->menu_name ) !== false )
		{
			if( strpos( $hook,'wc-j-upsellator-stats') !== false && $tab == '' )
			{
				wp_enqueue_style('jquery-ui-css', WC_J_UPSELLATOR_PLUGIN_URL.'assets/admin/css/jquery.ui.theme.css');

				wp_register_script('wc_j_chart_js', WC_J_UPSELLATOR_PLUGIN_URL.'assets/admin/js/Chart.bundle.min.js', array( 'jquery'), WC_J_UPSELLATOR_VERSION , true);
				wp_register_script('wc_j_chart_class_js', WC_J_UPSELLATOR_PLUGIN_URL.'assets/admin/js/Chart.class.js', array( 'jquery'), WC_J_UPSELLATOR_VERSION , true);
				wp_register_script('wc_j_chart_chart_label', WC_J_UPSELLATOR_PLUGIN_URL.'assets/admin/js/ChartLabels.min.js', [], WC_J_UPSELLATOR_VERSION, true);
				wp_register_script('wc_j_stats_loader', WC_J_UPSELLATOR_PLUGIN_URL.'assets/admin/js/stats.page.js', array('jquery'), WC_J_UPSELLATOR_VERSION, true);
				
				wp_enqueue_script('wc_j_chart_js');
				wp_enqueue_script('wc_j_chart_class_js');
				wp_enqueue_script('wc_j_chart_chart_label');
				wp_enqueue_script('wc_j_stats_loader');

				wp_localize_script('wc_j_stats_loader', 'wc_j_stats', array( 'currency' => woo_j_conf('currency'), 	 
																	   	     'decimals' => wc_get_price_decimals() 																						 
																	 )
				); 

				wp_enqueue_script('jquery-ui-datepicker');
				
			}

			if( strpos( $hook,'wc-j-upsellator-stats') !== false  && $tab == 'advanced' )
			{
				
				wp_register_script('wc_j_upsells_stats_loader', WC_J_UPSELLATOR_PLUGIN_URL.'assets/admin/js/upsells_stats.page.js', array('jquery'), WC_J_UPSELLATOR_VERSION, true);
				

				wp_enqueue_style('jquery-ui-css', WC_J_UPSELLATOR_PLUGIN_URL.'assets/admin/css/jquery.ui.theme.css');
				wp_enqueue_script('wc_j_upsells_stats_loader');

				wp_localize_script('wc_j_upsells_stats_loader', 'wc_j_stats', array( 'currency' => woo_j_conf('currency'), 	 
																	   	     'decimals' => wc_get_price_decimals() 																						 
																	 )
				); 

				wp_enqueue_script('jquery-ui-datepicker');
			}
			
			wp_enqueue_style( 'wp-color-picker' );			
			wp_enqueue_script('sweetalert2-js', WC_J_UPSELLATOR_PLUGIN_URL . 'assets/admin/vendor/sweetalert2/js/sweetalert2.min.js', array('jquery'), WC_J_UPSELLATOR_VERSION, true);
			
			wp_register_script('wc_j_upsellator_admin_js', WC_J_UPSELLATOR_PLUGIN_URL.'assets/admin/js/upsellator-admin.js', array( 'jquery', 'wp-i18n', 'select2','wp-color-picker', 'jquery-ui-sortable' ), WC_J_UPSELLATOR_VERSION );
			wp_localize_script('wc_j_upsellator_admin_js', 'wc_j', array( 'img_path' => woo_j_env('img_path') ) );
			wp_enqueue_script('wc_j_upsellator_admin_js' );
			
			wp_set_script_translations('wc_j_upsellator_admin_js', 'woo_j_cart', WC_J_UPSELLATOR_PLUGIN_DIR . 'languages');
		}	
	}
}