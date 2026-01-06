<?php

namespace WcJUpsellator;
use WcJUpsellator\Traits\TraitAdmin;
use WcJUpsellator\Traits\TraitTestMode;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class CssSTyles
{
	use TraitAdmin;
	use TraitTestMode;

	public function __construct()
	{
		   /*
		   /* Nothing to do
		   */ 
	}

	public function loadAdmin()
	{
			if( is_admin() ) 
			{
				add_action( 'admin_enqueue_scripts', [ $this, 'adminCSS' ] );
				add_action( 'admin_head', [ $this, 'iconStyle' ] );
			}
	}

	public function loadFrontend()
	{
			if( $this->currentUserCan() ) 
			{
				add_action( 'wp_enqueue_scripts', [ $this, 'frontendCSS' ] );
			}			
	}

    public function adminCSS( $hook )
   {	
		if( $hook && strpos( $hook, $this->menu_name ) !== false )
		{	
			$upsell_color				= woo_j_styles('upsell_color');
			$upsell_text_color			= woo_j_styles('upsell_text_color');
			$item_bg_color 				= woo_j_styles('item_count_background');
			$item_color 				= woo_j_styles('item_count_color');
			$free_gift_color			= woo_j_styles('gift_color');
			$s_b_bar_background_empty	= woo_j_shipping('s_b_bar_background_empty') ?? '#fbfbfb';
			$gift_text_color			= woo_j_styles('gift_text_color');
			$s_b_bar_background			= woo_j_shipping('s_b_bar_background');			
			$custom_css = "               
			:root{
				--item-count-background:$item_bg_color;
				--item-count-color:$item_color;
				--upsell-color:$upsell_color;	
				--upsell-text-color:$upsell_text_color;
				--free-gift-color:$free_gift_color;	
				--gift-text-color:$gift_text_color;
				--shipping_bar_bar_background:$s_b_bar_background;
				--shipping_bar_bar_background_empty:$s_b_bar_background_empty;
			}";

			wp_register_style('wc_j_upsellator_admin_css', WC_J_UPSELLATOR_PLUGIN_URL.'assets/admin/css/upsellator-admin-style.css', false, WC_J_UPSELLATOR_VERSION );
			wp_enqueue_style('wc_j_upsellator_admin_css');
			wp_enqueue_style('sweetalert2-theme', WC_J_UPSELLATOR_PLUGIN_URL . 'assets/admin/vendor/sweetalert2/css/theme.css', false, WC_J_UPSELLATOR_VERSION);
			wp_add_inline_style( 'wc_j_upsellator_admin_css', $custom_css );

		}			
   }
   
   public function frontendCSS()
   {
	
		wp_register_style('wc_j_upsellator_css', WC_J_UPSELLATOR_PLUGIN_URL.'assets/frontend/css/upsellator-style.css', false, WC_J_UPSELLATOR_VERSION );
		wp_enqueue_style('wc_j_upsellator_css');		
		
		$font_color 			= woo_j_styles('font_color');
		$item_bg_color 			= woo_j_styles('item_count_background');
		$item_color 			= woo_j_styles('item_count_color');
		$modal_bg				= woo_j_styles('background_color');
		$button_color			= woo_j_styles('button_color');
		$button_font_color		= woo_j_styles('button_font_color');	
		$button_color_hover		= woo_j_styles('button_color_hover') ?? woo_j_styles('button_font_color');
		$button_font_color_hover = woo_j_styles('button_font_color_hover') ?? woo_j_styles('button_color');
		$s_b_bar_background		= woo_j_shipping('s_b_bar_background');
		$s_b_success_background	= woo_j_shipping('s_b_success_background');	
		$s_b_bar_background_empty	= woo_j_shipping('s_b_bar_background_empty') ?? '#fbfbfb';	
		$free_gift_color		= woo_j_styles('gift_color');
		$gift_text_color		= woo_j_styles('gift_text_color');
		$upsell_color			= woo_j_styles('upsell_color');
		$upsell_text_color		= woo_j_styles('upsell_text_color');
		$modal_close			= woo_j_styles('modal_close_background');
		$modal_close_text		= woo_j_styles('modal_close_text');
		$base_font_size			= woo_j_styles('base_font_size') ?? 15;
		$cart_image_ratio 		= woo_j_styles('image_ratio') ?? 1.22;
		
		$custom_css = "               
				:root{					
					--font-color:$font_color;
					--item-count-background:$item_bg_color;
					--item-count-color:$item_color;
					--modal-bg-color:$modal_bg;
					--button-color:$button_color;
					--font-button-color:$button_font_color;				
					--wcj-button-color-hover:$button_color_hover;
					--wcj-button-font-color-hover:$button_font_color_hover;		
					--shipping_bar_bar_background:$s_b_bar_background;
					--shipping_bar_bar_background_empty:$s_b_bar_background_empty;
					--shipping_bar_success_background:$s_b_success_background;	
					--free-gift-color:$free_gift_color;	
					--gift-text-color:$gift_text_color;
					--upsell-color:$upsell_color;	
					--upsell-text-color:$upsell_text_color;
					--upsell-modal-close:$modal_close;	
					--upsell-modal-close-text:$modal_close_text;
					--base_font_size:{$base_font_size}px;	
					--modal-cart-image-ratio:{$cart_image_ratio};				
				}";

		if( woo_j_conf('page_scroll') )	
		{
			$custom_css .= "body.woo-upsellator-modal-active{overflow-y:hidden;}";
		}
				
		wp_add_inline_style( 'wc_j_upsellator_css', $custom_css );
		
   }
   /* 
   /* We want to style the icon even if we do not load the plugin css file, so we print it directly in the head,
   /* please forgive us! 
   */
   public function iconStyle()
   {	
		?><style>
			#adminmenu li.toplevel_page_wc-j-upsellator .wp-menu-image:before
			{
				background-image: url(<?php echo WC_J_UPSELLATOR_PLUGIN_URL ?>assets/img/logo-small-25x25.png);
				background-size: 21px;
				background-repeat: no-repeat;
				background-position: Center center;
				content: '';
			}
			</style>	
		<?php

   }


}