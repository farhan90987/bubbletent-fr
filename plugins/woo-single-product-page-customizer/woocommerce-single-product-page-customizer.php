<?php
/*
Plugin Name: Woocommerce Single Product Page Customizer
Description: By using this smart plugin, allows you to add text or HTML in wooocommerce Single product page , no need to edit theme and woocommerce plugin!
Author: Geek Code Lab
Version: 2.8.2
WC tested up to: 9.1.2
Author URI: https://geekcodelab.com/
Text Domain: woocommerce-single-product-page-customizer
Requires Plugins: woocommerce
*/

if( !defined( 'ABSPATH' ) ) exit;
define( "WSPPC_BUILD", "2.8.2");
require_once( plugin_dir_path (__FILE__) .'functions.php' );

/** All Hook List Array */
$hook_list=array(
'woocommerce_before_single_product',
'woocommerce_before_single_product_summary',
'woocommerce_single_product_summary',
'woocommerce_after_product_title',
'woocommerce_after_product_price',
'woocommerce_before_add_to_cart_form',
'woocommerce_before_variations_form',
'woocommerce_before_add_to_cart_button',
'woocommerce_before_single_variation',
'woocommerce_single_variation',
'woocommerce_after_single_variation',
'woocommerce_after_add_to_cart_button',
'woocommerce_after_variations_form',
'woocommerce_after_add_to_cart_form',
'woocommerce_product_meta_start',
'woocommerce_product_meta_end',
'woocommerce_share',
'woocommerce_product_thumbnails',
'woocommerce_after_single_product_summary',
'woocommerce_after_single_product',
'woocommerce_after_product_thumbnails'
);


/** Admin Menu Start */
add_action('admin_menu', 'wsppc_admin_menu_single_product_page_customizer' );
function wsppc_admin_menu_single_product_page_customizer(){
	add_submenu_page( 'woocommerce','Single Product Page Customizer', 'Single Product Page Customizer', 'manage_options', 'wsppc-woocommerce-single-product-page-customizer', 'wsppc_single_product_page_setting');
}
function wsppc_single_product_page_setting(){
	include( plugin_dir_path( __FILE__ ) . 'admin/options.php' );
}
/** Admin Menu End */


/**Avtivation Hook Start */
register_activation_hook( __FILE__, 'wsppc_plugin_active_single_product_page_customizert' );
function wsppc_plugin_active_single_product_page_customizert() {
	if (is_plugin_active( 'woo-single-product-page-customizer-pro/woocommerce-single-product-page-customizer-pro.php' ) ) {
		deactivate_plugins('woo-single-product-page-customizer-pro/woocommerce-single-product-page-customizer-pro.php');
   	} 
}

/**Avtivation Hook End */
require_once(plugin_dir_path( __FILE__ ) . 'front/index.php' );

/** Add settings And Support link Start */
$plugin = plugin_basename(__FILE__);
add_filter( "plugin_action_links_$plugin", 'wsppc_add_plugin_settings_link');
function wsppc_add_plugin_settings_link( $links ) {
	$support_link = '<a href="https://geekcodelab.com/contact/"  target="_blank" >' . __( 'Support', 'woocommerce-single-product-page-customizer' ) . '</a>'; 
	array_unshift( $links, $support_link );

	$pro_link = '<a href="https://geekcodelab.com/wordpress-plugins/woocommerce-single-product-page-customizer-pro/"  target="_blank" style="color:#46b450;font-weight: 600;">' . __( 'Premium Upgrade', 'woocommerce-single-product-page-customizer' ) . '</a>'; 
	array_unshift( $links, $pro_link );	

	$settings_link = '<a href="'. admin_url() .'admin.php?page=wsppc-woocommerce-single-product-page-customizer">' . __( 'Settings', 'woocommerce-single-product-page-customizer' ) . '</a>';
	array_unshift( $links, $settings_link );

	return $links;
}
/** Add settings And Support link End */

/** Admin Site Add Scriot Start */
add_action( 'admin_footer', 'wsppc_enqueue_styles_scripts' );
function wsppc_enqueue_styles_scripts()
{
    if( is_admin() ) {
        $css= plugins_url() . '/'.  basename(dirname(__FILE__)) . "/assets/css/style.css";               
        wp_enqueue_style( 'main-wsppc-woocommerce-single-page-css', $css, array(), WSPPC_BUILD );
    }
}
/** Admin Site Add Scriot End */


/** Admin Panel Edit Hook Form Start */
add_action("wp_ajax_wsppc_get_edit_form", "wsppc_edit_form");
function wsppc_edit_form()
{
	if($_POST['form_action']=='add_form')
	{
		check_ajax_referer( 'wsppc_ajax_add_nonce', 'security' );
	}
	if($_POST['form_action']=='edit_form')
	{
		check_ajax_referer( 'wsppc_ajax_edit_nonce', 'security' );
	}
	$hook="";
	$hook_value="";
	$all_hook="";
	if(isset($_POST['hook_name']))
	{
		$hook=sanitize_text_field($_POST['hook_name']);
		$hook_value=wsppc_get_hook_value($hook);
	}
	$all_hook=wsppc_get_hook();
	
	?>
	<form method="post" class="wsppc_form">
			<table class="form-table">
			<tbody>
				<tr valign="top">
				<?php if(empty($hook))
					{ ?>
					<th scope="row">
						<span><strong>Select Position</strong></span>
						<span>
							<select id="" name="hook">
								<?php 
								global $hook_list; 
								$i=1;							

								foreach($hook_list as $hooks){
									$disable_key="";
									
									if(isset($all_hook[$hooks])){ $disable_key = 'disabled="disabled"'; }	 ?>
									
										<option <?php echo $disable_key; ?> value="<?php echo $hooks; ?>"><?php echo  $i.". ".str_replace("_"," ",$hooks); ?></option>

										<?php $i++;
									} 
								?>
							</select>
							<p class="description"><?php echo esc_html__('Refere bellow position map.','woocommerce-single-product-page-customizer'); ?></p>
						</span>
					</th>
				<?php } else { ?>
				<input type="hidden" name="hook" value="<?php echo $hook; ?>">
				<input type="hidden" name="edit_form" value="1">
				<?php } ?>
				</tr>			
				<tr valign="top">				
					<td>
					<textarea name="content" id="content_<?php echo $hook;?>" rows="12" class="wsppc_content wp-editor"> <?php  echo wp_unslash($hook_value); ?></textarea>
					<p class="description"><?php echo esc_html__('This content will be show on single product page as per choosen position.','woocommerce-single-product-page-customizer'); ?></p>
					</td>				
				</tr>
			</tbody>
			</table>
			<input type="hidden" name="single_page_wpnonce" value="<?php echo $nonce= wp_create_nonce('wsppc_single_page_wpnonce'); ?>">
			<input type="submit" class="button button-primary " name="update_option" value="Update">
	</form>
	<?php
	die;
}
/** Admin Panel Edit Hook Form End */


/** Admin Panel Remove Hook Form Start */
add_action("wp_ajax_wsppc_remove_hook", "wsppc_removed_hook");
function wsppc_removed_hook()
{	
	check_ajax_referer( 'wsppc_ajax_remove_nonce', 'security' );
	$hook='';
	$security='';
	if(isset($_POST['hook_name']))
	{
		$hook=sanitize_text_field($_POST['hook_name']);
	}
	if(isset($_POST['security']))
	{
		$security=$_POST['security'];
	}
	$wsppc_hook=wsppc_get_hook();
	unset($wsppc_hook[$hook]);
	update_option('wsppc_hook',$wsppc_hook);
	echo true;
	die;
}
/** Admin Panel Remove Hook Form End */

/**
 * Added HPOS support for woocommerce
 */
add_action( 'before_woocommerce_init', 'wsppc_before_woocommerce_init' );
function wsppc_before_woocommerce_init() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
}
?>