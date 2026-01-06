<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.webtoffee.com
 * @since      1.0.0
 *
 * @package    Wt_Smart_Coupon
 * @subpackage Wt_Smart_Coupon/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wt_Smart_Coupon
 * @subpackage Wt_Smart_Coupon/admin
 * @author     WebToffee <info@webtoffee.com>
 */
if( ! class_exists('Wt_Smart_Coupon_Admin') ) {
    class Wt_Smart_Coupon_Admin {

        private $plugin_name;
        private $version;

        /**
         * module list, Module folder and main file must be same as that of module name
         * Please check the `register_modules` method for more details
         * @since 1.3.5
         */
        public static $modules=array(
            'url_coupon',
            'limit_max_discount',
            'coupon_shortcode',
            'giveaway_product',
            'coupon_restriction',
            'freevspro',
            'auto_coupon',
            'premium_upgrade',
            'other_solutions',
            'checkout_options', /** @since 1.4.6 */
            'coupon_style', /** @since 1.4.7 */
            'exclude-product', /** @since 2.0.0 */
            'bogo', /** @since 2.0.0 */
            'giftcard-banner', /** @since 2.1.0 */
        );

        public static $existing_modules = array();
        public static $tooltip_arr = array();

        private static $instance = null;
    
        public function __construct($plugin_name, $version) {
    
            $this->plugin_name = $plugin_name;
            $this->version = $version;
        }

        /**
         * Get Instance
         * @since 1.4.1
         */
        public static function get_instance($plugin_name, $version)
        {
            if(self::$instance==null)
            {
                self::$instance=new Wt_Smart_Coupon_Admin($plugin_name, $version);
            }

            return self::$instance;
        }

        /**
         * Admin settings right sidebar
         * @since 1.4.0
         */
        public static function admin_right_sidebar()
        {
            include WT_SMARTCOUPON_MAIN_PATH.'/admin/views/_admin_right_sidebar.php';
        }

        /**
         *  Setup video
         *  @since 1.4.0
         */
        public static function setup_video_sidebar()
        {
            include WT_SMARTCOUPON_MAIN_PATH.'/admin/views/-setup-video-sidebar.php';
        }

        /**
         *  Premium features
         *  @since 1.4.0
         */
        public static function premium_features_sidebar()
        {
            $premium_url = esc_url( 'https://www.webtoffee.com/product/smart-coupons-for-woocommerce/?utm_source=free_plugin_sidebar&utm_medium=smart_coupons_basic&utm_campaign=smart_coupons' );
            include WT_SMARTCOUPON_MAIN_PATH.'/admin/views/_premium_features_sidebar.php';
        }

        /**
         * Help links metabox html
         * @since 1.3.5
         */
        public function help_links_meta_box_html()
        {
            include WT_SMARTCOUPON_MAIN_PATH.'/admin/views/_help_links_meta_box.php';
        }


        /**
         * Help links metabox
         * @since 1.3.5
         */
        public function help_links_meta_box()
        {
            add_meta_box("wt-sc-help-links", __("Quick links", 'wt-smart-coupons-for-woocommerce'), array($this, "help_links_meta_box_html"), "shop_coupon", "side", "default", null);
        }
    
        /**
         * Save Custom meata fields added in coupon 
         * @since 1.0.0
         */
        public function process_shop_coupon_meta($post_id, $post) {
            if ( !class_exists( 'Wt_Smart_Coupon_Security_Helper' ) || !method_exists( 'Wt_Smart_Coupon_Security_Helper', 'check_user_has_capability' ) || !Wt_Smart_Coupon_Security_Helper::check_user_has_capability() ) 
            {
                wp_die(__('You do not have sufficient permission to perform this operation', 'wt-smart-coupons-for-woocommerce'));
            }

            if( isset($_POST['_wt_valid_for_number']) ) {
                $wt_valid_for_number = Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST['_wt_valid_for_number']);
                if($wt_valid_for_number != '') {
                    update_post_meta($post_id, '_wt_valid_for_number', $wt_valid_for_number );
                }
                if ( isset( $_POST['_wt_valid_for_type'] ) && '' != $_POST['_wt_valid_for_type']  ) {
                    $wt_valid_for_type = Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST['_wt_valid_for_type']);
                } else {
                    $wt_valid_for_type = 'days';
                }
                update_post_meta($post_id, '_wt_valid_for_type', $wt_valid_for_type );
    
            }
    
            if(isset($_POST['_wc_make_coupon_available']) && $_POST['_wc_make_coupon_available']!='' )
            {              
                $_wc_make_coupon_available = Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST['_wc_make_coupon_available'], 'text_arr');
                update_post_meta($post_id, '_wc_make_coupon_available', implode(',', $_wc_make_coupon_available));
            }else
            {
                update_post_meta($post_id, '_wc_make_coupon_available',  '');
            }
    
        }
    
        /**
         * Enqueue Admin styles.
         * @since 1.0.0
         * @since 1.3.5 Styles limited to WC pages and Smart coupon settings pages
         */
        public function enqueue_styles()
        {
            $screen    = get_current_screen();
            $screen_id = $screen ? $screen->id : '';
            
            if ( 
                (function_exists('wc_get_screen_ids') && in_array( $screen_id, wc_get_screen_ids())) || 
                (isset($_GET['page']) && (WT_SC_PLUGIN_NAME === $_GET['page'] || 0 === strpos(sanitize_text_field($_GET['page']), WT_SC_PLUGIN_NAME)))
            ) 
            {
                wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wt-smart-coupon-admin.css', array(), $this->version, 'all');
                wp_enqueue_style( 'wp-color-picker' );
            }

             /**
             *  Enqueue style for code preview in hooks help section
             *  
             *  @since 1.5.2
             */
            if ( isset( $_GET['page'] ) && WT_SC_PLUGIN_NAME === $_GET['page'] ) {
                wp_enqueue_style( $this->plugin_name . '_highlightjs', esc_url( plugin_dir_url( __FILE__ ) ) . 'assets/libraries/highlight/styles/stackoverflow-light.min.css', array(), $this->version, 'all' );
            }
        }
        
        /**
         * Enqueue Admin Scripts.
         * @since 1.0.0
         * @since 1.3.5 Scripts limited to WC pages and Smart coupon settings pages
         */
        public function enqueue_scripts()
        {
            $screen    = get_current_screen();
            $screen_id = $screen ? $screen->id : '';
            
            if ( 
                (function_exists('wc_get_screen_ids') && in_array($screen_id, wc_get_screen_ids())) || 
                (isset($_GET['page']) && (WT_SC_PLUGIN_NAME === $_GET['page'] || 0 === strpos(sanitize_text_field($_GET['page']), WT_SC_PLUGIN_NAME)))
            ) 
            {
                wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wt-smart-coupon-admin.js', array('jquery', 'wp-color-picker'), $this->version, false);               
                
                $script_parameters=array(
                    'msgs'=>array(
                        'settings_error'=>sprintf(__('Unable to update settings due to an internal error. %s To troubleshoot please click %s here. %s', 'wt-smart-coupons-for-woocommerce'), '<br />', '<a href="https://www.webtoffee.com/how-to-fix-the-unable-to-save-settings-issue/" target="_blank">', '</a>'),
                        'is_required'=>__("is required", 'wt-smart-coupons-for-woocommerce'),
                        'copied'=>__("Copied!", 'wt-smart-coupons-for-woocommerce'),
                        'error'=>__("Error", 'wt-smart-coupons-for-woocommerce'),
                        'loading'=>__("Loading...", 'wt-smart-coupons-for-woocommerce'),
                        'please_wait'=>__("Please wait...", 'wt-smart-coupons-for-woocommerce'),
                        'are_you_sure'=>__("Are you sure?", 'wt-smart-coupons-for-woocommerce'),
                        'are_you_sure_to_delete'=>__("Are you sure you want to delete?", 'wt-smart-coupons-for-woocommerce'),
                        'old_bogo_disabled' => __( "Old BOGO module is disabled", 'wt-smart-coupons-for-woocommerce' ),
                        'switch_new_bogo' => __( "Switch to our new BOGO module for the latest features", 'wt-smart-coupons-for-woocommerce' ),
                        'update_now' => __( "Update now", 'wt-smart-coupons-for-woocommerce' ),
                        ),
                    'is_new_bogo_activated' => class_exists( 'Wbte_Smart_Coupon_Bogo_Common' ) 
                        && method_exists( 'Wbte_Smart_Coupon_Bogo_Common', 'is_new_bogo_activated' ) 
                        && Wbte_Smart_Coupon_Bogo_Common::is_new_bogo_activated(),
                );
                
                $script_parameters['ajaxurl'] = admin_url( 'admin-ajax.php' );
                $script_parameters['nonce'] = wp_create_nonce( 'wt_smart_coupons_admin_nonce' );


                wp_localize_script($this->plugin_name,'WTSmartCouponAdminOBJ', $script_parameters );
            }

            /**
             *  Enqueue script for code preview in hooks help section
             *  
             *  @since 1.5.2
             */
            if ( isset( $_GET['page'] ) && WT_SC_PLUGIN_NAME === $_GET['page'] ) {
                wp_enqueue_script( $this->plugin_name . '_highlightjs', plugin_dir_url( __FILE__ ) . 'assets/libraries/highlight/highlight.min.js', array(), $this->version, false );
            }
    
        }
            
    
        /**
         * Plugin action link.
         * @since 1.0.0
         * @since 1.3.9 Some links moved to plugin description section
         */
        public function add_plugin_links_wt_smartcoupon($links)
        {  
            $out=array(
                'settings' => '<a href="'.get_admin_url().'?page='.WT_SC_PLUGIN_NAME.'&tab=settings">'.esc_html__('Settings', 'wt-smart-coupons-for-woocommerce') .' </a>',
            );
            foreach($links as $link_key=>$link_html)
            {
                if($link_key==='deactivate')
                {
                    $out['deactivate'] = str_replace('<a', '<a class="smartcoupon-deactivate-link"', $link_html);
                }else
                {
                    $out[$link_key] = $link_html;
                }
            }
            $out['premium-upgrade'] = '<a target="_blank" href="https://www.webtoffee.com/product/smart-coupons-for-woocommerce/?utm_source=free_plugin_listing&utm_medium=smart_coupons_basic&utm_campaign=smart_coupons&utm_content='.WEBTOFFEE_SMARTCOUPON_VERSION.'" style="color: #3db634; font-weight: 500;">' . esc_html__('Premium Upgrade', 'wt-smart-coupons-for-woocommerce') . '</a>';
            return $out;
        }

        /**
         *  @since 1.3.9
         *  Links under plugin description section of plugins page
         */
        public function plugin_row_meta($links, $file)
        {
            if(WT_SMARTCOUPON_BASE_NAME !== $file)
            {
                return $links;
            }

            $links['documentation']='<a target="_blank" href="https://www.webtoffee.com/smart-coupons-for-woocommerce-userguide/">' . esc_html__('Docs', 'wt-smart-coupons-for-woocommerce') . '</a>';
            $links['support']='<a target="_blank" href="https://wordpress.org/support/plugin/wt-smart-coupons-for-woocommerce/">' . esc_html__('Support', 'wt-smart-coupons-for-woocommerce') . '</a>';
                     
            return $links;
        }


        /**
         * Add coupon visibility options in coupon general settings section
         * @since 1.3.7 Added option to show coupons in checkout page
         */
        function add_new_coupon_options( $coupon_id, $coupon )
        {
            $wc_make_coupon_available = get_post_meta($coupon_id , '_wc_make_coupon_available', true );
            $coupon_available_arr=($wc_make_coupon_available ? explode(',', $wc_make_coupon_available) : array()); 
            
            $coupon_availability_options = array(
                'my_account'    => __('My Account', 'wt-smart-coupons-for-woocommerce'),
                'checkout'      => __('Checkout', 'wt-smart-coupons-for-woocommerce'),
                'cart'          => __('Cart', 'wt-smart-coupons-for-woocommerce'),
            );
            ?>
            <p class="form-field">
                <label for="_wc_make_coupon_available"><?php _e('Display coupon in', 'wt-smart-coupons-for-woocommerce'); ?></label>
                <select id="_wc_make_coupon_available" name="_wc_make_coupon_available[]" style="width: 50%;"  class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php _e('Please select', 'wt-smart-coupons-for-woocommerce'); ?>">
                    <?php
                    foreach($coupon_availability_options as $section => $name)
                    {
                        $selected =(in_array($section, $coupon_available_arr) ? 'selected = selected' : '');                       
                        echo '<option value="'.esc_attr($section).'" '.$selected.'>'.esc_html($name).'</option>';
                    }                  
                    ?>
                </select> 
                <?php echo wc_help_tip(__('Display coupon in the selected pages', 'wt-smart-coupons-for-woocommerce')); ?>
            </p>

            <?php
            /**
             *  @since 1.4.7
             */
            include_once(ABSPATH.'wp-admin/includes/plugin.php');
            
            if(!is_plugin_active('wt-display-discounts-for-woocommerce/wt-display-discounts-for-woocommerce.php'))
            {
                
            ?>
                <table style="background:#EFFFE8; width:calc(100% - 30px); margin-left:10px; padding:10px 15px; box-sizing:border-box; border-left:solid 3px #299A42; color:#575757; margin-bottom:20px; border-spacing:0px; border-collapse:collapse;">
                    <tr>
                        <td style="padding:15px; background:#EFFFE8;">
                            <div style="width:100%; font-size:16px; font-weight:bold; color:#1E6200;"><span><img src="<?php echo esc_url(WT_SMARTCOUPON_MAIN_URL . 'admin/images/idea_bulb_green.svg');?>" style="width:16px;"></span>&nbsp;<?php esc_html_e('Did You Know?', 'wt-smart-coupons-for-woocommerce'); ?></div>
                            <div style="width:100%; font-size:14px; color:#555555;"><?php esc_html_e( 'You can create advanced Buy One Get One (BOGO) offers in WooCommerce.', 'wt-smart-coupons-for-woocommerce' ); ?></div>
                        </td>
                        <td style="width:100px; vertical-align:middle; padding:15px 15px 15px 5px; background:#EFFFE8;">
                            <a style="background:#299A42; color:#fff; border:none;" class="button button-secondary" href="<?php echo esc_attr( 'https://www.webtoffee.com/product/smart-coupons-for-woocommerce/?utm_source=free_plugin_marketing_bottom&utm_medium=smart_coupons_basic&utm_campaign=smart_coupons&utm_content=' . WEBTOFFEE_SMARTCOUPON_VERSION ); ?>" target="_blank"><?php esc_html_e('Check out this plugin', 'wt-smart-coupons-for-woocommerce'); ?> <span class="dashicons dashicons-arrow-right-alt" style="margin-top:8px;font-size:14px;"></span> </a>
                        </td>
                    </tr>
                </table>
            <?php
            }
        }

    
        /**
         * Ajax action function for checking product type
         * @since 1.0.0
         */
    
        function check_product_type() {

            if ( check_ajax_referer( 'wt_smart_coupons_nonce', 'security' ) && class_exists( 'Wt_Smart_Coupon_Security_Helper ') && method_exists( 'Wt_Smart_Coupon_Security_Helper', 'check_user_has_capability' ) && Wt_Smart_Coupon_Security_Helper::check_user_has_capability() ) {
                
                $product_id = isset( $_POST['product']) ? Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST['product'],'int') : '';
                if( '' == $product_id  )  {
                    return false;
                }
                $product = wc_get_product( $product_id );
                echo esc_html( $product->get_type() );
                die();
            }
        }
    
        /**
         * Get Smartcoupon Settings options
         * @deprecated 1.4.7 In favor of Wt_Smart_Coupon::get_settings.
         * 
         * @since 1.0.1
         */
        public static function get_options()
        {
            return Wt_Smart_Coupon::get_settings();
        }
    
        /**
         * helper function for getting formatted price
         * @since 1.2.9
         */
        public static function get_formatted_price( $amount ) {
            $currency = get_woocommerce_currency_symbol();
            $currentcy_positon = get_option('woocommerce_currency_pos');
    
            switch( $currentcy_positon ) {
                case 'left' : 
                    return $currency.$amount;
                case 'left_space' : 
                    return $currency.' '.$amount;
                case 'right_space' : 
                    return $amount.' '.$currency;
                default  : 
                    return $amount.$currency;
            }
    
            
        }
    

        /**
         *  Register modules    
         *  @since 1.3.5     
         */
        public function register_modules()
        { 
            Wt_Smart_Coupon::register_modules(self::$modules, 'wt_sc_admin_modules', plugin_dir_path( __FILE__ ), self::$existing_modules);  
        }

        /**
         *  Check module enabled    
         *  @since 1.3.5     
         */
        public static function module_exists($module)
        {
            return in_array($module, self::$existing_modules);
        }

        /**
         *  @since 1.4.1
         *  Saving new coupon count
         */
        public function save_created_coupon_count($post_id, $post, $update)
        {
            if(!$update && 'shop_coupon' === $post->post_type && 'auto-draft' === $post->post_status)
            {
                $auto_draft = get_option('wt_sc_auto_draft_coupons', array());
                $auto_draft[$post_id] = 1;

                update_option('wt_sc_auto_draft_coupons', $auto_draft);
            }


            if('shop_coupon' === $post->post_type && 'auto-draft' !== $post->post_status)
            {
                $auto_draft = get_option('wt_sc_auto_draft_coupons', array());

                $coupons_created = (int) get_option('wt_sc_coupons_created', 0);

                $is_update_needed = false;

                if($update && isset($auto_draft[$post_id])) //auto draft item saving as shop coupon
                {
                    $coupons_created++;
                    $is_update_needed = true;
                    
                    unset($auto_draft[$post_id]);
                    update_option('wt_sc_auto_draft_coupons', $auto_draft);
                }

                if(!$update)
                {
                    $coupons_created++;
                    $is_update_needed = true;
                }          

                if($is_update_needed)
                {
                    update_option('wt_sc_coupons_created', $coupons_created);
                }
            }
        }
        

        /**
         *  Alter WP coupon search section to handle `coupons by email` search. 
         *  Search format - email:{email@example.com}
         *  
         *  @since 1.4.4
         */
        public function search_coupon_using_email($wp)
        {
            global $pagenow, $wpdb;
            
            if('edit.php' !== $pagenow || !isset($wp->query_vars['s']) || 'shop_coupon' !== $wp->query_vars['post_type'])
            {
                return;
            }
            
            $wp->query_vars['s'] = trim($wp->query_vars['s']);
            
            if('email:' === strtolower(substr($wp->query_vars['s'], 0, 6)))
            {
                $email = trim(substr($wp->query_vars['s'], 6));
                
                if(!$email)
                {
                    return;
                }

                $post_ids = $wpdb->get_col($wpdb->prepare("SELECT pm.post_id FROM {$wpdb->postmeta} AS pm LEFT JOIN {$wpdb->posts} AS p ON (p.ID = pm.post_id AND p.post_type = 'shop_coupon') WHERE pm.meta_key = 'customer_email' AND pm.meta_value LIKE %s", '%' . $wpdb->esc_like($email) . '%')); // WPCS: db call ok.
                
                if(empty($post_ids))
                {
                    return;
                } 
                
                unset($wp->query_vars['s'], $_REQUEST['s']); //prevent WP default search

                $wp->query_vars['post__in'] = $post_ids;
                $wp->query_vars['email'] = $email;
            }
        }


        /**
         * Registers menu options
         * Hooked into admin_menu
         *
         * @since    1.4.4
         */
        public function admin_menu()
        {
            $menus=array(
                array(
                    'menu',
                    __('General settings', 'wt-smart-coupons-for-woocommerce'),
                    __('Smart Coupons', 'wt-smart-coupons-for-woocommerce'),
                    'manage_woocommerce',
                    WT_SC_PLUGIN_NAME,
                    array($this, 'admin_settings_page'),
                    'dashicons-tag',
                    59
                ),
               array(
                    'submenu',
                    WT_SC_PLUGIN_NAME,
                    __('All coupons','wt-smart-coupons-for-woocommerce'),
                    __('All coupons','wt-smart-coupons-for-woocommerce'),
                    'edit_shop_coupons',
                    'edit.php?post_type=shop_coupon',
                ),
                array(
                    'submenu',
                    WT_SC_PLUGIN_NAME,
                    __('Add coupon','wt-smart-coupons-for-woocommerce'),
                    __('Add coupon','wt-smart-coupons-for-woocommerce'),
                    'edit_shop_coupons',
                    'post-new.php?post_type=shop_coupon',
                ),
                array(
                    'submenu',
                    WT_SC_PLUGIN_NAME,
                    __('General settings','wt-smart-coupons-for-woocommerce'),
                    __('General settings','wt-smart-coupons-for-woocommerce'),
                    'manage_woocommerce',
                    WT_SC_PLUGIN_NAME,
                    array($this, 'admin_settings_page'),
                ),
            );
            
            $menus=apply_filters('wt_sc_admin_menu', $menus);

            if(is_array($menus))
            {
                foreach($menus as $menu)
                {
                    if('submenu' === $menu[0])
                    {
                        if(isset($menu[6]))
                        {
                            add_submenu_page($menu[1],$menu[2],$menu[3],$menu[4],$menu[5],$menu[6]);
                        }else{
                            add_submenu_page($menu[1],$menu[2],$menu[3],$menu[4],$menu[5]);
                        }
                        
                    }else
                    {
                        add_menu_page($menu[1],$menu[2],$menu[3],$menu[4],$menu[5],$menu[6],$menu[7]);  
                    }
                }
            }

            if(function_exists('remove_submenu_page')){
                remove_submenu_page(WT_SC_PLUGIN_NAME, WT_SC_PLUGIN_NAME);
            }
        }

        /**
         * Admin settings page
         *
         * @since    1.4.4
         */
        public function admin_settings_page()
        {
            include WT_SMARTCOUPON_MAIN_PATH.'admin/views/general_settings.php';
        }

        
        /**
         * Generate tab head for settings page.
         * 
         * @since     1.4.4
         */
        public static function generate_settings_tabhead($title_arr, $type="plugin")
        {   
            $out_arr = apply_filters("wt_sc_".$type."_settings_tabhead", $title_arr);
            
            foreach($out_arr as $k => $v)
            {           
                if(is_array($v))
                {
                    $v = (isset($v[2]) ? $v[2] : '').$v[0].' '.(isset($v[1]) ? $v[1] : '');
                }
            ?>
                <a class="nav-tab" href="#<?php echo esc_attr($k);?>"><?php echo wp_kses_post($v); ?></a>
            <?php
            }
        }

        
        /**
         *  Envelope settings tab content with tab div.
         *  Relative path is not acceptable for view file
         *  
         *  @since 1.4.4
         */
        public static function envelope_settings_tabcontent($target_id, $view_file="", $html="", $view_params=array(), $need_submit_btn=0)
        {
            ?>
                <div class="wt-sc-tab-content" data-id="<?php echo esc_attr($target_id);?>">
                    <?php
                    if("" !== $view_file && file_exists($view_file))
                    {
                        include_once $view_file;
                    }else
                    {
                        echo wp_kses_post($html);
                    }
                    ?>
                    <?php 
                    if(1 === $need_submit_btn)
                    {
                        self::add_settings_footer();
                    }
                    ?>
                </div>
            <?php
        }

        
        /**
         * Smart coupon settings button on coupons page
         * 
         *  @since 1.4.4
         *  @since 1.4.8    Added bulk generate info bar on coupons admin page
         */
        public function coupon_page_settings_button()
        {
            global $current_screen;
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

            if( 'shop_coupon' !== $current_screen->post_type )
            {
                return;
            }
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function($){
                    jQuery( '.page-title-action' ).after( '<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . WT_SC_PLUGIN_NAME ) );?>" class="page-title-action wt_sc_plugin_settings_btn"><?php esc_html_e( 'Smart coupon settings', 'wt-smart-coupons-for-woocommerce' ); ?></a>' );
                    
                    <?php  
                    $hidden_banners = get_option( 'wbte_sc_hidden_promotion_banners', array() );
                    if( ! in_array( 'ema_cpns_page', $hidden_banners ) )
                    {
                        $campaign_url = 'https://www.webtoffee.com/ecommerce-marketing-automation/?utm_source=free_plugin_add_coupon_menu&utm_medium=smart_coupon_basic&utm_campaign=EMA&utm_content=' . WEBTOFFEE_SMARTCOUPON_VERSION;

                        $bulk_plugin_text = sprintf( 
                            '<div data-wbte-sc-promotion-banner-id="ema_cpns_page" class="wbte_sc_promotion_banner_div"><span><img src="%s" style="width: 16px;" /></span>&nbsp;<span class="wbte_sc_promotion_banner_title">%s</span><div class="wbte_sc_promotion_banner_content"><p style="margin: 0; font-size: 14px;"> %s </p><div class="wbte_sc_promotion_banner_actions"> <a class="button button-secondary wbte_sc_promotion_banner_link_btn" href="%s" target="_blank"> %s <span class="dashicons dashicons-arrow-right-alt" style="font-size: 14px; line-height: 1.5;"></span> </a>&ensp;<button type="button" class="button button-secondary wbte_sc_promotion_banner_close wbte_sc_promotion_banner_later"> %s </button></div></div><span class="dashicons dashicons-no-alt wbte_sc_promotion_banner_close wbte_sc_promotion_banner_close_btn"></span></div>',
                            esc_url( WT_SMARTCOUPON_MAIN_URL . 'admin/images/idea_bulb_purple.svg' ),
                            esc_html__( 'Did you know?', 'wt-smart-coupons-for-woocommerce' ),
                            sprintf( 
                                __( 'With %s WooCommerce Marketing Automation %s app, you can build signup forms, create popups, and run automated email campaigns with exclusive coupons.', 'wt-smart-coupons-for-woocommerce' ), 
                                '<a href="' . esc_url( $campaign_url ) . '" target="_blank"><b>', '</b></a>' 
                            ),
                            esc_url( $campaign_url ),
                            esc_html__( "Sign Up for Free", "wt-smart-coupons-for-woocommerce" ),
                            esc_html__( "Maybe later", "wt-smart-coupons-for-woocommerce" )
                        );
                    ?>
                        jQuery( '.page-title-action.wt_sc_plugin_settings_btn' ).after( '<?php echo wp_kses_post( $bulk_plugin_text ); ?>' );
                    <?php  
                    }
                    ?>
                });
            </script>
            <?php
        }


        /**
        *   To save debug settings
        *   
        *   @since 1.4.5
        */
        protected function debug_save_sub($option_name)
        {
            $wt_sc_modules = get_option($option_name);
            
            if(false === $wt_sc_modules)
            {
                $wt_sc_modules = array();
            }

            if(isset($_POST[$option_name]))
            {
                $wt_sc_post = Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST[$option_name], 'text_arr');
                
                foreach($wt_sc_modules as $k => $v)
                {
                    if(isset($wt_sc_post[$k]) && 1 == $wt_sc_post[$k])
                    {
                        $wt_sc_modules[$k] = 1;
                    }else
                    {
                        $wt_sc_modules[$k] = 0;
                    }
                }
            }else
            {
                foreach($wt_sc_modules as $k => $v)
                {
                    $wt_sc_modules[$k] = 0;
                }
            }

            update_option($option_name, $wt_sc_modules);
        }

        
        /**
        *   Form action for debug settings tab
        *   
        *   @since 1.4.5
        */
        public function debug_save()
        {   
            if(isset($_POST['wt_sc_admin_modules_btn']))
            {
                if(Wt_Smart_Coupon_Security_Helper::check_write_access('smart_coupons', 'wt_smart_coupons_admin_nonce')) 
                {
                    return;
                }
                
                $this->debug_save_sub('wt_sc_public_modules');
                $this->debug_save_sub('wt_sc_common_modules');
                $this->debug_save_sub('wt_sc_admin_modules');
                
                wp_redirect($_SERVER['REQUEST_URI']); exit();
            }

            if(Wt_Smart_Coupon_Security_Helper::check_role_access('smart_coupons')) //Check access
            {
                //module debug settings saving hook
                do_action('wt_sc_module_save_debug_settings');
            }
        }

        /**
         *  Shows a progress message while migrating data from post table to lookup table
         *  
         *  @since 1.4.5
         */
        public function lookup_table_migration_message()
        {
            $migration_status = absint(get_option('wt_sc_coupon_lookup_updated', 0));
            $last_updated_id = absint(get_option('wt_sc_coupon_lookup_migration_last_id', 0));

            if(0 === $migration_status || 0 < $last_updated_id) //migration not started or in progress
            {
                ?>
                <div class="notice notice-info">
                    <p>
                        <h3><?php _e('Smart coupon database update in progress', 'wt-smart-coupons-for-woocommerce');?></h3>
                        <p><?php _e('The site may experience a slow response for few minutes.', 'wt-smart-coupons-for-woocommerce');?>
                        </p>
                        <p style="font-weight:bold;">
                            <?php
                            global $wpdb;
                            $row = $wpdb->get_row("SELECT COUNT(p.ID) AS total_records FROM {$wpdb->posts} AS p WHERE p.post_type = 'shop_coupon'", ARRAY_A);
                            $total = absint(!empty($row) && isset($row['total_records']) ? $row['total_records'] : 0);
                            $migrated = Wt_Smart_Coupon_Common::get_lookup_table_record_count();
                            echo sprintf(__('Progress: %d out of %d', 'wt-smart-coupons-for-woocommerce'), $migrated, $total); ?>
                        </p>
                    </p>
                </div>
                <?php
            }
        }


        /**
        *   Add setting tab footer
        *   
        *   @since 1.4.7
        */
        public static function add_settings_footer($settings_button_title='', $settings_footer_left='', $settings_footer_right='')
        {
            include WT_SMARTCOUPON_MAIN_PATH . "admin/views/admin-settings-save-button.php";
        }


        /**
         *  Hide promotion banner
         *
         *  @since 2.2.1
         */
        public static function hide_promotion_banner(){

            check_ajax_referer( 'wt_smart_coupons_admin_nonce', '_wpnonce' );

            if ( ! Wt_Smart_Coupon_Security_Helper::check_role_access( 'smart_coupons' ) ) {
                wp_send_json_error( __( 'Access denied', 'wt-smart-coupons-for-woocommerce' ) );
            }

            $hided_banners = get_option( 'wbte_sc_hidden_promotion_banners', array() );
            $hided_banners[] = isset( $_POST['banner_id'] ) ? sanitize_text_field( wp_unslash( $_POST['banner_id'] ) ) : '';
            update_option( 'wbte_sc_hidden_promotion_banners', $hided_banners );
            wp_send_json_success();
        }

        /**
         *  Screens to show promotional banner
         * 
         *  @since 1.5.2
         */
        public function wt_promotion_banner_screens( $screen_ids ) {

            $screen_ids[] = 'shop_coupon';
            $screen_ids[] = 'edit-shop_coupon';
            $screen_ids[] = 'toplevel_page_wt-smart-coupon-for-woo'; // Plugin settings page

            return $screen_ids;
        }


        /**
         *  Set tooltip for form fields 
         *   
         *   @since 1.3.5
         *   
         */
        public static function set_tooltip($key, $base_id = "", $custom_css = "") {
            $tooltip_text = self::get_tooltips( $key, $base_id );
            if ( "" !== $tooltip_text ) {
                $tooltip_text = "<span style='display:inline-block; color:#16a7c5; ".( "" !== $custom_css ? esc_attr( $custom_css ) : 'margin-top:0px; margin-left:2px; position:absolute;' )."' class='dashicons dashicons-editor-help wt-sc-tips' data-wt-sc-tip='" . esc_attr( $tooltip_text ) . "'></span>";
            }
            return $tooltip_text;
        }

        
        /**
         *   Get tooltip config data for non form field items
         * 
         *   @since 1.7.0
         *   
         *   @return array 'class': class name to enable tooltip, 'text': tooltip text including data attribute if not empty
         */
        public static function get_tooltip_configs( $key, $base_id = "" ) {
            $out    = array( 'class' => '', 'text' => '' );
            $text   = self::get_tooltips( $key, $base_id );
            if ( "" !== $text ) {
                $out['text']    = " data-wt-sc-tip='" . esc_attr( $text ) . "'";
                $out['class']   = ' wt-sc-tips';
            }   
            return $out;
        }

        
        /**
         *  This function will take tooltip data from modules 
         *  
         *  @since 1.7.0
         *
         */
        public function register_tooltips() {

            self::$tooltip_arr = array(
                'main' => array()
            );
            
            /**
             *  Hook for modules to register tooltip 
             * 
             *  @since 1.7.0
             *  @param array    Tooltip array
             */
            self::$tooltip_arr = apply_filters( 'wt_sc_alter_tooltip_data', self::$tooltip_arr );
        }

        
        /**
         *  Get tooltips
         *   
         *  @since  1.7.0
         *  @param  string  $key    Array key for tooltip item
         *  @param  string  $base   Module base id
         *  @return Tooltip content, empty string if not found
         */
        public static function get_tooltips( $key, $base_id = '' ) {
            $arr = ( "" !== $base_id && isset( self::$tooltip_arr[ $base_id ] ) ? self::$tooltip_arr[ $base_id ] : self::$tooltip_arr['main'] );
            return ( isset( $arr[ $key ] ) ? $arr[ $key ] : '' );
        }

        /**
         * 	Load the design system files and initiate it.
         * 	
         *  @since    2.0.0
         */
        public function include_design_system() {
            
            include_once plugin_dir_path( __FILE__ ) . 'wt-ds/class-wbte-ds.php';
            
            if( class_exists( 'Wbte\Sc\Ds\Wbte_Ds' ) ){

                // Just initiate it. This is to load the CSS and JS.
                Wbte\Sc\Ds\Wbte_Ds::get_instance( WEBTOFFEE_SMARTCOUPON_VERSION );
            }
        }

        /**
         * Get WC_DateTime object for a date
         * 
         * @since 2.0.0
         * 
         * @param mixed $value The date value to convert to WC_DateTime.
         * @return WC_DateTime The WC_DateTime object.
         */
        public static function wt_sc_get_date_prop( $value )
        {
            if ( is_int( $value ) ) {
                $timestamp = $value;
            } else {
                if ( 1 === preg_match( '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(Z|((-|\+)\d{2}:\d{2}))$/', $value, $date_bits ) ) {
                    $offset    = ! empty( $date_bits[7] ) ? iso8601_timezone_to_offset( $date_bits[7] ) : wc_timezone_offset();
                    $timestamp = gmmktime( $date_bits[4], $date_bits[5], $date_bits[6], $date_bits[2], $date_bits[3], $date_bits[1] ) - $offset;
                } else {
                    $timestamp = wc_string_to_timestamp( get_gmt_from_date( gmdate( 'Y-m-d H:i:s', wc_string_to_timestamp( $value ) ) ) );
                }
            }
            $datetime = new WC_DateTime( "@{$timestamp}", new DateTimeZone( 'UTC' ) );

			// Set local timezone or offset.
            if ( get_option( 'timezone_string' ) ) {
                $datetime->setTimezone( new DateTimeZone( wc_timezone_string() ) );
            } else {
                $datetime->set_utc_offset( wc_timezone_offset() );
            }
            
            return $datetime;
        }

        /**
         *  Trigger 'after_wt_smart_coupon_for_woocommerce_is_activated' hook by comparing version in option
         *  
         *  @since 2.0.0
         */
        public static function check_and_trigger_activation_action_hook(){
            
            if( !get_option( 'wbte_sc_basic_activation_hook_version' ) || version_compare( get_option( 'wbte_sc_basic_activation_hook_version' ), WEBTOFFEE_SMARTCOUPON_VERSION, '<' ) ){
                do_action( 'after_wt_smart_coupon_for_woocommerce_is_activated' );
                update_option( 'wbte_sc_basic_activation_hook_version', WEBTOFFEE_SMARTCOUPON_VERSION );		
            }
        }

        /**
         *  Delete non-existing coupons from the lookup table.
         * 
         *  @since 2.0.0
         */
        public function delete_coupon_from_lookup_table(){

            if( !get_option( 'wbte_sc_basic_removed_non_existing_coupons_lookup_tb' ) ){

                $lookup_tb = Wt_Smart_Coupon::get_lookup_table_name();

                if ( Wt_Smart_Coupon::is_table_exists( $lookup_tb ) ) {

                    global $wpdb;
                    
                    $lookup_table_coupon_ids = $wpdb->get_col( "SELECT coupon_id FROM $lookup_tb" );
            
                    if ( ! empty( $lookup_table_coupon_ids ) ) {
                        
                        $placeholders = implode( ',', array_fill( 0, count( $lookup_table_coupon_ids ), '%d' ) );
            
                        // Query to find existing coupon IDs in wp_posts.
                        $existing_coupon_ids = $wpdb->get_col(
                            $wpdb->prepare(
                                "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'shop_coupon' AND ID IN ($placeholders)",
                                $lookup_table_coupon_ids
                            )
                        );
            
                        // Calculate IDs to remove (in custom table but not in wp_posts).
                        $ids_to_remove = array_diff( $lookup_table_coupon_ids, $existing_coupon_ids );
            
                        // Remove invalid coupon IDs from the custom table.
                        if ( ! empty( $ids_to_remove ) ) {
                            $placeholders = implode( ',', array_fill( 0, count( $ids_to_remove ), '%d' ) );
            
                            $deleted_count = $wpdb->query(
                                $wpdb->prepare(
                                    "DELETE FROM $lookup_tb WHERE coupon_id IN ($placeholders)",
                                    $ids_to_remove
                                )
                            );

                            if( $deleted_count && $deleted_count === count( $ids_to_remove ) ){
                                update_option( 'wbte_sc_basic_removed_non_existing_coupons_lookup_tb', 1 );
                            }
                        }
                    }
                }
            }
           
        }

        /**
         * To change WordPress footer text to review request link in BOGO page.
         * If the current page is BOGO edit page, then return empty span, this span will be hidden using css.
         *
         * @since 2.1.0
         * @since 2.2.0 Moved from BOGO module to here.
         * @param  string $footer_text  Current footer text.
         * @return string               Modified footer text.
         */
        public function sc_review_request_footer( $footer_text ) {
            if ( isset( $_GET['page'] ) && 0 === strpos( sanitize_text_field( wp_unslash( $_GET['page'] ) ), WT_SC_PLUGIN_NAME ) && ! isset( $_GET['wbte_bogo_id'] ) ) {

                $review_url = 'https://wordpress.org/support/plugin/wt-smart-coupons-for-woocommerce/reviews?rate=5#new-post';
                
                $footer_text = wp_kses_post(
                    // Translators: 1: Opening italics tag, 2: Opening a tag, 3: Closing a tag, 4: Closing italics tag.
                    sprintf( __( '%s If you like Smart Coupons please leave us a %s ★★★★★ %s rating. A huge thanks in advance! %s', 'wt-smart-coupons-for-woocommerce' ), '<i class="wbte_sc_bogo_review_request">', '<a href="'. esc_url( $review_url ) .'" target="_blank">', '</a>', '</i>' )
                );
            }
            if ( isset( $_GET['page'] ) && isset( $_GET['wbte_bogo_id'] ) ) {
                $footer_text = '<span class="wbte_sc_bogo_edit_footer"></span>';
            }
            return $footer_text;
        }

        /**
         *  Change WordPress version text to show plugin version in Smart Coupons settings page.
         *
         *  @since 2.2.0
         *  @param string $footer_text Current footer text.
         *  @return string Modified footer text.
         */
        public function sc_version_footer( $footer_text ){
            if( isset( $_GET['page'] ) && 0 === strpos( sanitize_text_field( wp_unslash( $_GET['page'] ) ), WT_SC_PLUGIN_NAME ) ){
                $footer_text = sprintf( esc_html__( 'Version %s', 'wt-smart-coupons-for-woocommerce' ), WEBTOFFEE_SMARTCOUPON_VERSION );
            }
            return $footer_text;
        }
    }
}
