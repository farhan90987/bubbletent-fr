<?php
/*
 * Plugin Name: Address correction autocomplete for Woo
 * Description: This plug-in for Woocommerce enables an auto address correction to be implemented in the checkout process using the Google API.
 * Author: SAHU MEDIA ®
 * Author URI: https://sahu.media
 * Version: 1.2
 * Domain Path: /languages
 *
 * @package sahu_woo_com
 * @copyright Copyright (c) 2022, SAHU MEDIA®
 *
 */
 
// Lade übersetzungen

add_action( 'init', 'sahu_woo_com_load_textdomain' );
function sahu_woo_com_load_textdomain() {
  load_plugin_textdomain( 'sahu_woo_com', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

// Lade Plugin - sahu_woo_com

function sahu_woo_com_auto_complete_init () {
  if (class_exists( 'WooCommerce' )) {
    if( get_option( 'api_key' ) ) {
      add_action('wp_footer', 'sahu_woo_com_auto_complete_scripts');
    }else{
      add_action( 'admin_notices', 'sahu_woo_com_auto_complete_missing_key_notice' );
    }
  }else{
    add_action( 'admin_notices', 'sahu_woo_com_auto_complete_missing_wc_notice' );
  }
}
add_action( 'init', 'sahu_woo_com_auto_complete_init' );

// Admin Error Messages

function sahu_woo_com_auto_complete_missing_key_notice() {
  ?>
  <div class="update-nag notice">
      <p><?php _e( 'For Enable WooCommerce Adress Autocomplete for WooCommerce Please <a href="admin.php?page=sahu_woo_com_auto_complete_page">enter your Google Maps API Key</a>', 'sahu_woo_com' ); ?></p>
  </div>
  <?php
}

function sahu_woo_com_auto_complete_missing_wc_notice() {
  ?>
  <div class="error notice">
      <p><?php _e( 'You need to install and activate WooCommerce in order to use the Autocomplete function for WooCommerce!', 'sahu_woo_com' ); ?></p>
  </div>
  <?php
}


// Admin Settings Menu

function sahu_woo_com_auto_complete_menu(){
  add_submenu_page( 'woocommerce', 'Autocomplete Checkout Address', 'Autocomplete Checkout Address', 'manage_options', 'sahu_woo_com_auto_complete_page', 'sahu_woo_com_auto_complete_page' ); 
  add_action( 'admin_init', 'sahu_woo_com_update_auto_complete' );
}
add_action( 'admin_menu', 'sahu_woo_com_auto_complete_menu' , 99);

// Admin Settings Page

function sahu_woo_com_auto_complete_page(){
?>
<div class="wrap">
  <h1><?php _e( 'Autocomplete Address for WooCommerce checkout fields', 'sahu_woo_com');?></h1>
  <p><?php _e( 'For enable autocomplete address on checkout page paste your API key below and click "Save Changes".', 'sahu_woo_com'); ?></p>
 
 <!--  <p>Make sure to check the "Places" box.  If you don't already have a billing account on the Google Cloud Platform, you will need to set one up in order to get your API key.  The above link will guide you through it.</p> -->
  <form method="post" action="options.php">
    <?php settings_fields( 'auto-complete-settings' ); ?>
    <?php do_settings_sections( 'auto-complete-settings' ); ?>
    <table class="form-table">
      <tr valign="top">
      <th scope="row"><?php _e( 'Google Maps API Key:','sahu_woo_com'); ?></th>
      <td><input type="text" name="api_key" style="width: 25em; padding: 3px 5px;" placeholder="Please enter your Goole map API key" value="<?php echo get_option( 'api_key' ); ?>"/>&nbsp;<a href="https://cloud.google.com/maps-platform/#get-started" target="_blank">Click here to get your "Places" API Key</a></td>
      </tr>
      <tr valign="top">
      <th scope="row"><?php _e( 'Force Enqueue Google Maps JS:', 'sahu_woo_com'); ?></th>
      <td><input type="checkbox" name="enqueue_map_js" value="true" <?php if(get_option('enqueue_map_js')==true)echo 'checked'; ?>/></td>
      </tr>
    </table>
    <?php submit_button(); ?>
  </form>
</div>
<?php
}

// Save Plugin Settings (API Key)

add_filter( 'woocommerce_get_settings_checkout', 'sahu_woo_com_checkout_settings', 10, 2 );
function sahu_woo_com_checkout_settings( $settings) {
 
  $updated_settings = array();
    foreach ($settings as $section) {    
      if (isset($section['id']) && 'woocommerce_enable_coupons' == $section['id']) 
      {
        $updated_settings[] = array(
                'name' => __('Gift Wrap Charges', 'Gift Wrap Charges'),
                'id' => 'giftproduct_charge',
                'type' => 'text',
                'css' => 'min-width:300px;',
                'desc' => '',
            );
      }
      $updated_settings[] = $section;
    }
  return $updated_settings;
}

function sahu_woo_com_update_auto_complete() {
  register_setting( 'auto-complete-settings', 'api_key' );
  register_setting( 'auto-complete-settings', 'enqueue_map_js' );
}

// Load Frontend Javascripts

function sahu_woo_com_auto_complete_scripts() {
    if(is_checkout() || is_account_page()){

        ?>
       
        <?php
        if(get_option('enqueue_map_js')==true){
          wp_enqueue_script('google-autocomplete', 'https://maps.googleapis.com/maps/api/js?libraries=places&key='.get_option( 'api_key' ));
          wp_enqueue_script('auto-complete', plugin_dir_url( __FILE__ ) . 'autocomplete.js');
        }else{
          sahu_woo_com_auto_complete_google_maps_script_loader();
        }
    }
  }


function sahu_woo_com_auto_complete_google_maps_script_loader() {
    global $wp_scripts; $gmapsenqueued = false;
    foreach ($wp_scripts->queue as $key) {
      if(array_key_exists($key, $wp_scripts->registered)) {
        $script = $wp_scripts->registered[$key];
        if (preg_match('#maps\.google(?:\w+)?\.com/maps/api/js#', $script->src)) {
          $gmapsenqueued = true;
        }
      }
    }

    if (!$gmapsenqueued) {
       ?>
       

        <?php
        wp_enqueue_script('google-autocomplete', 'https://maps.googleapis.com/maps/api/js?v=3&libraries=places&key='.get_option( 'api_key' ));
    }
    wp_enqueue_script('auto-complete', plugin_dir_url( __FILE__ ) . 'autocomplete.js');
}

?>