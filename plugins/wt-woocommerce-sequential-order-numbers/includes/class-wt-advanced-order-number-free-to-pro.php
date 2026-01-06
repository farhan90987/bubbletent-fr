<?php
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('WT_Sequentialordnum_free_to_pro')) :

    /**
     * Class for pro advertisement
     */

    class WT_Sequentialordnum_free_to_pro
    {

        public function __construct()
        {
            /* 
            *Goto Pro sidebar section 
            */
            add_action('wt_sequential_goto_pro_section_sidebar', array($this, 'wt_goto_pro_sidebar'));
        }
        /**
        *   @since 1.4.9
        *   Goto Pro section in sidebar
        */
        public function wt_goto_pro_sidebar()
        {
            require_once plugin_dir_path(dirname(__FILE__)) . 'admin/views/wt-goto-pro.php';
        }
    }
new WT_Sequentialordnum_free_to_pro();
    
endif;