<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class Wt_Advanced_Order_Number_Admin {

    private $plugin_name;
    private $version;
    /**
    *   To store the RTL needed or not status
    *   @since 1.4.9
    */
    public static $is_enable_rtl=null;

    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        //wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wt-advanced-order-number-admin.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if(isset($_GET['page']) && $_GET['page']=='wc-settings' && isset($_GET['tab']) && $_GET['tab']=='wts_settings' && ((isset($_GET['section']) && $_GET['section'] =='') || !isset($_GET['section'])))
        {
            // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'views/wt-settings-screen.js', array('jquery'), $this->version);
            
        }
        // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wt-advanced-order-number-admin.js', array('jquery'), $this->version);
        $params=array(
            'msgs'=>array(
                'prev'=>__('Preview : ','wt-woocommerce-sequential-order-numbers'),
                'pro_text'=>'<span class="wt_pro_text" style="color:#39b54a;font-size:11px;">'.__(' (Pro) ','wt-woocommerce-sequential-order-numbers').'</span>',
            ),
            'serch_by_order_number' => get_option( 'wt_custom_order_number_search', 'yes' ),
        );
        wp_localize_script($this->plugin_name, 'wt_seq_settings', $params); 
    }

    public function add_settings_page_popup() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if(isset($_GET['page']) && $_GET['page']=='wc-settings' && isset($_GET['tab']) && $_GET['tab']=='wts_settings')
        {
            require_once plugin_dir_path(dirname(__FILE__)) . 'admin/views/wt-advanced-order-number-admin-settings-page.php';
        }
    }

    public function add_plugin_links_wt_wtsequentialordnum($links) {


        $plugin_links = array(
            '<a href="' . admin_url('admin.php?page=wc-settings&tab=wts_settings') . '">' . __('Settings', 'wt-woocommerce-sequential-order-numbers') . '</a>',
            '<a target="_blank" href="https://wordpress.org/support/plugin/wt-woocommerce-sequential-order-numbers/#new-topic-0">' . __('Support', 'wt-woocommerce-sequential-order-numbers') . '</a>',
            '<a target="_blank" href="https://wordpress.org/support/plugin/wt-woocommerce-sequential-order-numbers/reviews/#new-post">' . __('Review', 'wt-woocommerce-sequential-order-numbers') . '</a>',
            '<a href="https://woocommerce.com/products/webtoffee-sequential-order-numbers/" target="_blank" style="color: #3db634;">'.__('Premium Upgrade','wt-woocommerce-sequential-order-numbers').'</a>',
        );
        if (array_key_exists('deactivate', $links)) {
            $links['deactivate'] = str_replace('<a', '<a class="wtsequentialordnum-deactivate-link"', $links['deactivate']);
        }
        return array_merge($plugin_links, $links);
    }

    public function custom_ordernumber_search_field($search_fields) {
        array_push($search_fields, '_order_number');
        return $search_fields;
    }
    /**
    *   @since 1.4.9 Get list of RTL languages
    *   @return array an associative array of RTL languages with locale name, native name, locale code, WP locale code
    */
    public static function wt_get_rtl_languages()
    {
        $rtl_lang_keys=array('ar', 'dv', 'he_IL', 'ps', 'fa_IR', 'ur');

        /**
        *   Alter RTL language list.
        *   @param array RTL language locale codes (WP specific locale codes)
        */
        $rtl_lang_keys=apply_filters('wt_seq_alter_rtl_language_list', $rtl_lang_keys);

        $lang_list=self::wt_get_language_list(); //taking full language list       
        
        $rtl_lang_keys=array_flip($rtl_lang_keys);
        return array_intersect_key($lang_list, $rtl_lang_keys);
    }

    /**
    *   @since 1.4.9 Checks user enabled RTL and current language needs RTL support.
    *   @return boolean 
    */
    public static function wt_is_enable_rtl_support()
    {
        if(!is_null(self::$is_enable_rtl)) /* already checked then return the stored result */
        {
            return self::$is_enable_rtl;
        }
        $rtl_languages=self::wt_get_rtl_languages();
        $current_lang=get_locale();
        
        self::$is_enable_rtl=isset($rtl_languages[$current_lang]); 
        return self::$is_enable_rtl;
    }

    /**
    *   @since 1.4.9
    *   List of all languages with locale name and native name
    *   @return array An associative array of languages.
    */
    public static function wt_get_language_list()
    {
        include plugin_dir_path(__FILE__).'data/data.language-list.php';
        
        /**
        *   Alter language list.
        *   @param array An associative array of languages.
        */
        $wt_seq_language_list=apply_filters('wt_seq_alter_language_list', $wt_seq_language_list);

        return $wt_seq_language_list;
    }

}