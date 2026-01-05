<?php
/**
 * Plugin Name: Custom Tools
 * Plugin URI: https://yourwebsite.com
 * Description: A collection of custom tools for WooCommerce
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: custom-tools
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CUSTOM_TOOLS_VERSION', '1.0.0');
define('CUSTOM_TOOLS_PATH', plugin_dir_path(__FILE__));
define('CUSTOM_TOOLS_URL', plugin_dir_url(__FILE__));
define('CUSTOM_TOOLS_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once CUSTOM_TOOLS_PATH . 'includes/class-plugin-manager.php';
require_once CUSTOM_TOOLS_PATH . 'includes/class-tools-manager.php';

// Initialize the plugin
function custom_tools_init() {
    $plugin_manager = new Custom_Tools\Plugin_Manager();
    $plugin_manager->init();
}
add_action('plugins_loaded', 'custom_tools_init');

// Activation hook
register_activation_hook(__FILE__, function() {
    // Set default options
    add_option('custom_tools_settings', array(
        'double_booking_tool' => true
    ));
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    // Clean up if needed
});