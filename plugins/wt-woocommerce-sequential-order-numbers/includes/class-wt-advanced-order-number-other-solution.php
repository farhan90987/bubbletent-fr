<?php
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('WT_Sequentialordnum_other_solutions')) :

    /**
     * Class for other plugin's advertisement
     */

    class WT_Sequentialordnum_other_solutions
    {

        public function __construct()
        {
            add_action('wt_sequential_other_solution_section', array($this, 'wt_show_other_solutions'));
        }
        /**
        *   @since 1.4.9
        *   Other solutions section
        */
        public function wt_show_other_solutions()
        {
            require_once plugin_dir_path(dirname(__FILE__)) . 'admin/views/wt-other-solutions.php';
        }
    }
new WT_Sequentialordnum_other_solutions();
    
endif;