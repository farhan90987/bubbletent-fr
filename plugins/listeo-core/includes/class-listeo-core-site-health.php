<?php

if (!defined('ABSPATH')) exit;

class Listeo_Core_Site_Health
{

    /**
     * Returns the instance.
     *
     * @since 2.0.0
     */
    public static function get_instance()
    {
        static $instance = null;
        if (is_null($instance)) {
            $instance = new self;
        }
        return $instance;
    }


    /**
     * Constructor.
     *
     * @since 2.0.0
     */
    public function __construct()
    {
        add_filter('site_health_navigation_tabs',array($this,'listeo_site_health_navigation_tabs'));
        add_action('site_health_tab_content', array($this, 'listeo_site_health_tab_content'));
        add_action('admin_enqueue_scripts', array($this, 'listeo_site_health_enqueue_admin_scripts'));

        add_action('wp_ajax_listeo_recreate_page', array($this, 'listeo_recreate_page'));
        
    }

    
    function listeo_site_health_enqueue_admin_scripts( $hook ) {
        
        if ('site-health.php' == $hook ) {
            
            wp_enqueue_script('listeo_site_health_script', LISTEO_CORE_URL . 'assets/js/listeo.sitehealth.js', array('wp-util', 'jquery'), 1.0, true);
            
        }
        
    }

    function listeo_site_health_navigation_tabs($tabs)
    {
        // translators: Tab heading for Site Health navigation.
        $tabs['listeo-site-health-tab'] = esc_html_x('Listeo', 'Site Health', 'listeo_core');

        return $tabs;
    }

    function listeo_site_health_tab_content( $tab ) {
        // Do nothing if this is not our tab.
        if ('listeo-site-health-tab' !== $tab ) {
            return;
        }
    
        // Include the interface, kept in a separate file just to differentiate code from views.
        include trailingslashit( plugin_dir_path( __FILE__ ) ) . '/views/site-health-tab.php';
    }


    function listeo_recreate_page(){
        $pages = listeo_core_get_dashboard_pages_list();
        
        if(!empty($_POST['page'])){
            $page = $pages[$_POST['page']];
            $title = $page['title'];
            $content = $page['content'];
            delete_option($page['option']);
            $page_args = array(
                'comment_status' => 'close',
                'ping_status'    => 'close',
                'post_author'    => 1,
                'post_title'     => $title,
                'post_name'      => strtolower(str_replace(' ', '-', trim($title))),
                'post_status'    => 'publish',
                'post_content'   => $content,
                'post_type'      => 'page',
                'page_template'  => 'template-dashboard.php'
            );
            if(in_array($_POST['page'],array('listeo_lost_password_page', 'listeo_reset_password_page'))){
               unset($page_args['page_template']);
            }
            $page_id = wp_insert_post(
                $page_args
            );
            
            if($page_id){
                update_option($page['option'],$page_id);
                wp_send_json_success();
            }
        } else {
            wp_send_json_error();
        }
    
        
    }

}


