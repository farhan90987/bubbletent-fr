<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://www.webtoffee.com
 * @since      1.0.0
 *
 * @package    Wt_Smart_Coupon
 * @subpackage Wt_Smart_Coupon/admin/partials
 */

$wt_sc_admin_view_path = plugin_dir_path(WT_SMARTCOUPON_FILE_NAME).'admin/views/';
$ds_obj = Wbte\Sc\Ds\Wbte_Ds::get_instance( WEBTOFFEE_SMARTCOUPON_VERSION );
$admin_img_path = WT_SMARTCOUPON_MAIN_URL . 'admin/images/';

?>
<style>
    #wpbody-content{ margin-top: 130px; }
    #wpcontent{ background-color: #F1F8FE; }
</style>
<div class="wrap">
<?php 
    $header_arr = array(
        'wt-sc-help'   =>  __( 'Help guide', 'wt-smart-coupons-for-woocommerce' ),
        'wbte-sc-develop'        =>  __( 'Develop', 'wt-smart-coupons-for-woocommerce' )
    );
    $header_arr = apply_filters( 'wt_sc_plugin_settings_tabhead', $header_arr );
    if( isset( $_GET['debug'] ) )
    {
        $header_arr['wt-sc-debug']=__( 'Debug', 'wt-smart-coupons-for-woocommerce' );
    }
    $header_items = array();
    foreach( $header_arr as $key => $val )
    {		
        $_arr = array(
            'title' =>  is_array( $val ) ? $val[0] : $val,
            'href' => esc_attr( '#' . $key ),
        );
        if( 'wt-sc-coupon_style' === $key )
        {
            $_arr['class'] = 'active';
        }

        $header_items[] = $_arr;
    }

    echo $ds_obj->get_component(
        'header',
        array(
            'values' => array(
                'plugin_name'      => 'Smart coupon',
                'developed_by_txt' => esc_html__( 'Developed by', 'wt-smart-coupons-for-woocommerce' ),
                'plugin_logo' => esc_url( $admin_img_path . 'voucher_tag.svg' ),
                'menu' => $header_items,
            ),
        )
    );
    ?>
    <div class="wt-sc-tab-container">
        
        <?php
        //inside the settings form
        $setting_views_a = array(       
            'wt-sc-help'        => 'admin-help.php',    
            'wbte-sc-develop'   => '_admin-develop.php' 
        );

        //outside the settings form
        $setting_views_b=array(          
            'wt-sc-help'=>'admin-settings-help.php',           
        );
        if(isset($_GET['debug']))
        {
            $setting_views_b['wt-sc-debug']='admin-settings-debug.php';
        }
        ?>
        <form method="post" class="wt_sc_settings_form">
            <input type="hidden" value="main" class="wt_sc_settings_base" />
            <?php
            
            // Set nonce:
            if (function_exists('wp_nonce_field'))
            {
                wp_nonce_field(WT_SC_PLUGIN_NAME);
            }
            foreach ($setting_views_a as $target_id=>$value) 
            {
                $settings_view=$wt_sc_admin_view_path.$value;
                if(file_exists($settings_view))
                {
                    include $settings_view;
                }
            }

            //settings form fields for module
            do_action('wt_sc_plugin_settings_form');
            ?>           
        </form>
        <?php
        foreach($setting_views_b as $target_id=>$value) 
        {
            $settings_view=$wt_sc_admin_view_path.$value;
            if(file_exists($settings_view))
            {
                include $settings_view;
            }
        }
        ?>
        <?php 
        //modules to hook outside settings form
        do_action('wt_sc_plugin_out_settings_form');
        ?> 
    </div>

    <?php 
    Wt_Smart_Coupon_Admin::admin_right_sidebar();
    ?>
</div>
<?php
do_action('wt_sc_plugin_after_settings_tab');

echo $ds_obj->get_component(
    'help-widget',
    array(
        'values' => array(
            'items' => array(
            array( 'title' => __( 'Setup Guide', 'wt-smart-coupons-for-woocommerce' ), 'icon' => 'book', 'href' => esc_url( 'https://www.webtoffee.com/setup-smart-coupons-for-woocommerce/' ), 'target' => '_blank' ),
            array( 'title' => __( 'Contact support', 'wt-smart-coupons-for-woocommerce' ), 'icon' => 'headphone', 'target' => '_blank', 'href' => esc_url( 'https://www.webtoffee.com/support/' ) ),
            ),
            'hover_text' => esc_html__( 'Help', 'wt-smart-coupons-for-woocommerce' ),
        ),
        'class' => array( 'wbte_sc_admin_settings_help_widget' )
    )
);
?>