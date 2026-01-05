<?php
namespace Custom_Tools;

class Plugin_Manager {
    private $tools_manager;
    
    public function init() {
        $this->tools_manager = new Tools_Manager();
        
        // Load enabled tools
        add_action('init', array($this, 'load_tools'));
        
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Admin scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    public function load_tools() {
        $settings = get_option('custom_tools_settings', array());
        
        // Load Double Booking Tool if enabled
        if (isset($settings['double_booking_tool']) && $settings['double_booking_tool']) {
            if (file_exists(CUSTOM_TOOLS_PATH . 'tools/double-booking-tool/class-double-booking-tool.php')) {
                require_once CUSTOM_TOOLS_PATH . 'tools/double-booking-tool/class-double-booking-tool.php';
                
                // Initialize the tool
                $double_booking_tool = new \Custom_Tools\Double_Booking_Tool();
                $double_booking_tool->init();
            }
        }
        
        // Add more tools here as they're developed
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Custom Tools', 'custom-tools'),
            __('Custom Tools', 'custom-tools'),
            'manage_options',
            'custom-tools',
            array($this, 'render_admin_page'),
            'dashicons-admin-tools',
            56
        );
    }
    
    public function register_settings() {
        register_setting('custom_tools_settings', 'custom_tools_settings');
    }
    
    public function render_admin_page() {
        $settings = get_option('custom_tools_settings', array());
        ?>
        <div class="wrap custom-tools-admin">
            <h1><?php _e('Custom Tools Settings', 'custom-tools'); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('custom_tools_settings');
                do_settings_sections('custom_tools_settings');
                ?>
                
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e('Double Booking Tool', 'custom-tools'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="custom_tools_settings[double_booking_tool]" value="1" <?php checked(isset($settings['double_booking_tool']) && $settings['double_booking_tool']); ?> />
                                <?php _e('Enable Double Booking Tool', 'custom-tools'); ?>
                            </label>
                            <p class="description"><?php _e('Check this to enable the double booking detection tool.', 'custom-tools'); ?></p>
                        </td>
                    </tr>

                    <!-- Add more tools here as they're developed -->
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    public function enqueue_admin_scripts($hook) {
        if ($hook === 'toplevel_page_custom-tools') {
            wp_enqueue_style('custom-tools-admin', CUSTOM_TOOLS_URL . 'assets/css/admin.css', array(), CUSTOM_TOOLS_VERSION);
            wp_enqueue_script('custom-tools-admin', CUSTOM_TOOLS_URL . 'assets/js/admin.js', array('jquery'), CUSTOM_TOOLS_VERSION, true);
        }
    }
}