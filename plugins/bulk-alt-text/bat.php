<?php
/**
 * Plugin Name: Bulk Meta Updater
 * Description: Bulk update image alt text and Page, Post, and Woocommerce product meta title and description
 * Version: 3.1
 * Author: Mathesconsulting
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once(plugin_dir_path(__FILE__) . 'lib/vendor/autoload.php');

// Include MetaExport and MetaImport classes
if (!class_exists('MetaExport')) {
    require_once(plugin_dir_path(__FILE__) . 'meta_export.php');
}
if (!class_exists('MetaImport')) {
    require_once(plugin_dir_path(__FILE__) . 'meta_import.php');
}
if (!class_exists('ImageAltUpdater')) {
    require_once(plugin_dir_path(__FILE__) . 'image-alt.php');
}

use PhpOffice\PhpSpreadsheet\IOFactory;

class BulkMetaUpdater {

    private $log_file;
    private $meta_export;
    private $meta_import;

    public function __construct() {
        $this->log_file = '';
        $this->meta_export = new MetaExport(); // Initialize MetaExport class
        $this->meta_import = new MetaImport(); // Initialize MetaImport class
        $this->image_alt_updater = new ImageAltUpdater();

        add_action('admin_menu', [$this, 'create_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_preview_alt_updates', [$this, 'preview_alt_updates']);
        add_action('wp_ajax_process_alt_updates', [$this, 'process_alt_updates']);
        add_action('wp_ajax_update_batch', [$this, 'update_batch']);
        add_action('wp_ajax_view_log', [$this, 'view_log']);
        add_action('wp_ajax_delete_log', [$this, 'delete_log']);
        add_action('wp_ajax_export_image_alt_texts', [$this, 'export_image_alt_texts']);
    }

    private function initialize_log_file() {
        if (empty($this->log_file)) {
            $logs_dir = plugin_dir_path(__FILE__) . 'logs/';
            if (!is_dir($logs_dir)) {
                mkdir($logs_dir, 0755, true);
            }
            $this->log_file = $logs_dir . 'log_' . date('Y-m-d_H-i-s') . '.txt';
            file_put_contents($this->log_file, "Processing started at " . date('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND);
        }
    }


    public function create_admin_menu() {
        add_menu_page(
            'Bulk Meta Updater',
            'Bulk Meta Updater',
            'manage_options',
            'bulk-meta-updater',
            [$this, 'render_dashboard_page'],
            'dashicons-admin-tools',
            20
        );



        add_submenu_page(
            'bulk-meta-updater',
            'Meta Export',
            'Meta Export',
            'manage_options',
            'meta-export',
            [$this->meta_export, 'render_export_page'] // Use the MetaExport class method
        );

        add_submenu_page(
            'bulk-meta-updater',
            'Meta Import',
            'Meta Import',
            'manage_options',
            'meta-import',
            [$this->meta_import, 'render_import_page'] // Use the MetaImport class method
        );


        add_submenu_page(
            'bulk-meta-updater',
            'Image Alt Processor',
            'Image Alt Processor',
            'manage_options',
            'image-alt-processor',
            [$this->image_alt_updater, 'render_page']
        );
        
        add_submenu_page(
            'bulk-meta-updater',
            'Logs',
            'Logs',
            'manage_options',
            'bulk-meta-updater-logs',
            [$this, 'render_logs_page']
        );


    }

    

    public function enqueue_scripts($hook) {
        $valid_hooks = [
            'toplevel_page_bulk-meta-updater',
            'bulk-meta-updater_page_image-alt-updater',
            'bulk-meta-updater_page_meta-export',
            'bulk-meta-updater_page_meta-import',
            'bulk-meta-updater_page_bulk-meta-updater-logs',
            'bulk-meta-updater_page_image-alt-processor', // Ensure this is added
        ];

        if (in_array($hook, $valid_hooks)) {
            wp_enqueue_script('bulk-alt-updater', plugins_url('/js/script.js', __FILE__), ['jquery'], '1.0', true);
            wp_localize_script('bulk-alt-updater', 'BulkAltUpdater', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('bulk_meta_updater_nonce'),
            ]);
            wp_enqueue_style('bulk-meta-updater', plugins_url('/css/style.css', __FILE__));

            if ($hook === 'bulk-meta-updater_page_meta-export') {
                wp_enqueue_script('meta-export', plugins_url('/js/meta_export.js', __FILE__), ['jquery'], '1.0', true);
                wp_enqueue_style('meta-export', plugins_url('/css/meta_export.css', __FILE__));
            }

            if ($hook === 'bulk-meta-updater_page_meta-import') {
                wp_enqueue_script('meta-import', plugins_url('/js/meta_import.js', __FILE__), ['jquery'], '1.0', true);
                wp_enqueue_style('meta-import', plugins_url('/css/meta_import.css', __FILE__));
            }
            if ($hook === 'bulk-meta-updater_page_image-alt-processor') {
                wp_enqueue_script('image-alt-processor', plugins_url('/js/image-alt.js', __FILE__), ['jquery'], '1.0', true);
                
            }
        }
    }

    public function render_dashboard_page() {
        echo '<div class="wrap"><h1>Bulk Meta Updater</h1><p>Select a tool from the submenu.</p></div>';
    }

    // Image Alt Text Functions Preserved from Original Code
    public function render_image_alt_page() {
        if (!current_user_can('manage_options')) return;

        echo '<div class="wrap bulk-alt-updater-container">';
        echo '<h1 class="title">Bulk Image Alt Text Updater</h1>';
        echo '<form id="upload-form" method="post" enctype="multipart/form-data" class="upload-form">';
        echo '<input type="file" name="alt_text_file" id="alt_text_file" accept=".xlsx,.xls" class="file-input" required>';
        echo '<button type="submit" class="button button-primary upload-button">Preview Changes</button>';
        echo '</form>';
        echo '<button id="export-images-button" class="button button-secondary" style="margin-top: 20px;">Export Images</button>';
        echo '<div id="preview-container" class="preview-container" style="display:none;">';
        echo '<h2 class="subtitle">Preview</h2>';
        echo '<table id="preview-table" class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Image URL</th><th>Current Alt Text</th><th>New Alt Text</th></tr></thead>';
        echo '<tbody></tbody></table>';
        echo '<div id="pagination-controls" style="margin-top: 10px;"></div>';
        echo '<button id="start-processing" class="button button-primary start-processing-button" style="display:none;">Start Processing</button>';
        echo '</div>';
        echo '<div id="progress-container" class="progress-container" style="display:none;">';
        echo '<p id="progress-text" class="progress-text">Starting...</p>';
        echo '<progress id="progress-bar" class="progress-bar" max="100" value="0"></progress>';
        echo '</div>';
        echo '</div>';
    }


    public function export_image_alt_texts() {
        check_ajax_referer('bulk_meta_updater_nonce', 'security');
    
        global $wpdb;
    
        // Prepare the base directory for exports within the plugin directory
        $exports_dir = plugin_dir_path(__FILE__) . 'exports/';
        if (!is_dir($exports_dir)) {
            if (!mkdir($exports_dir, 0755, true) && !is_dir($exports_dir)) {
                wp_send_json_error(['message' => 'Failed to create exports directory.']);
            }
        }
    
        $query = "
            SELECT DISTINCT p.ID AS image_id, 
                   p.guid AS url,
                   MAX(CASE WHEN pm.meta_key = '_wp_attachment_image_alt' THEN pm.meta_value ELSE NULL END) AS alt_text,
                   p.post_name AS filename,
                   p.post_mime_type
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'attachment'
              AND p.post_mime_type LIKE 'image/%'
              AND p.post_status = 'inherit'
              AND NOT EXISTS (
                  SELECT 1 
                  FROM {$wpdb->postmeta} pm_subsize
                  WHERE pm_subsize.post_id = p.ID
                  AND pm_subsize.meta_key = '_wp_attachment_is_subsize'
              )
            GROUP BY p.ID
        ";
    
        $results = $wpdb->get_results($query);
    
        if (empty($results)) {
            wp_send_json_error(['message' => 'No images found in the media library.']);
        }
    
        // Create the spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        // Set headers
        $sheet->setCellValue('A1', 'Image ID');
        $sheet->setCellValue('B1', 'URL');
        $sheet->setCellValue('C1', 'Alt Text');
    
        $row = 2;
        $unique_urls = [];
        foreach ($results as $image) {
            $parsed_url = wp_parse_url($image->url);
    
            $correct_url = trailingslashit($parsed_url['scheme'] . '://' . $parsed_url['host']) . basename($parsed_url['path']);
            if (in_array($correct_url, $unique_urls)) {
                continue;
            }
            $unique_urls[] = $correct_url;
    
            $sheet->setCellValue('A' . $row, $image->image_id);
            $sheet->setCellValue('B' . $row, $correct_url);
            $sheet->setCellValue('C' . $row, $image->alt_text ?: 'N/A');
            $row++;
        }
    
        // Save the file to the exports directory
        $file_name = 'media_library_image_alt_export_' . date('Y-m-d_H-i-s') . '.xlsx';
        $file_path = $exports_dir . $file_name;
    
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        try {
            $writer->save($file_path);
        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Error saving export file: ' . $e->getMessage()]);
        }
    
        // Construct the file URL using plugins_url
        $file_url = plugins_url('exports/' . $file_name, __FILE__);
        wp_send_json_success(['file_url' => $file_url]);
    }
    
    

    

    
    // Logs Page Rendering Function
    public function render_logs_page() {
        if (!current_user_can('manage_options')) return;

        $logs_dir = plugin_dir_path(__FILE__) . 'logs/';
        $log_files = glob($logs_dir . '*.txt');

        echo '<div class="wrap bulk-alt-updater-logs-container">';
        echo '<h1 class="title">Logs</h1>';

        if (empty($log_files)) {
            echo '<p>No log files found.</p>';
        } else {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>Log File</th><th>Actions</th></tr></thead>';
            echo '<tbody>';

            foreach ($log_files as $log_file) {
                $file_name = basename($log_file);
                echo '<tr>';
                echo '<td>' . esc_html($file_name) . '</td>';
                echo '<td>';
                echo '<button class="button view-log" data-log="' . esc_attr($file_name) . '">View</button> ';
                echo '<button class="button delete-log" data-log="' . esc_attr($file_name) . '">Delete</button>';
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody></table>';
        }

        echo '<div id="log-content-modal" style="display:none;">';
        echo '<h2>Log Content</h2>';
        echo '<pre id="log-content" style="background: #f1f1f1; padding: 10px; border: 1px solid #ddd; max-height: 400px; overflow: auto;"></pre>';
        echo '<button id="close-log-modal" class="button">Close</button>';
        echo '</div>';
        echo '</div>';
    }


    public function preview_alt_updates() {
        check_ajax_referer('bulk_meta_updater_nonce', 'security');

        if (!isset($_FILES['alt_text_file']) || empty($_FILES['alt_text_file']['tmp_name'])) {
            wp_send_json_error(['message' => 'No file uploaded.']);
        }

        try {
            $file_path = $_FILES['alt_text_file']['tmp_name'];
            $spreadsheet = IOFactory::load($file_path);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $headers = array_map('strtolower', $rows[0]);
            if (!in_array('url', $headers) || !in_array('alt text', $headers)) {
                wp_send_json_error(['message' => 'Missing required columns: "url" and "alt text".']);
            }

            $url_index = array_search('url', $headers);
            $alt_text_index = array_search('alt text', $headers);

            $page = isset($_POST['page']) ? (int) $_POST['page'] : 1;
            $per_page = 50;
            $offset = ($page - 1) * $per_page;

            $preview_data = [];
            foreach (array_slice($rows, $offset + 1, $per_page) as $row) {
                $url = trim($row[$url_index]);
                $new_alt_text = trim($row[$alt_text_index]);

                if (filter_var($url, FILTER_VALIDATE_URL) && !empty($new_alt_text)) {
                    $attachment_id = attachment_url_to_postid($url);
                    $current_alt_text = $attachment_id ? get_post_meta($attachment_id, '_wp_attachment_image_alt', true) : 'N/A';

                    $preview_data[] = [
                        'url' => $url,
                        'current_alt_text' => $current_alt_text,
                        'new_alt_text' => $new_alt_text,
                    ];
                }
            }

            $total_rows = count($rows) - 1;
            $total_pages = ceil($total_rows / $per_page);

            wp_send_json_success([
                'preview' => $preview_data,
                'total_pages' => $total_pages,
                'current_page' => $page,
            ]);
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function process_alt_updates() {
        check_ajax_referer('bulk_alt_updater_nonce', 'security');

        if (!isset($_POST['preview_data']) || empty($_POST['preview_data'])) {
            wp_send_json_error(['message' => 'No preview data provided.']);
        }

        $this->initialize_log_file();
        $preview_data = json_decode(stripslashes($_POST['preview_data']), true);
        $batch_size = 10;
        $batches = array_chunk($preview_data, $batch_size);

        wp_send_json_success(['batches' => $batches]);
    }

    public function update_batch() {
        check_ajax_referer('bulk_meta_updater_nonce', 'security');
    
        $batch = isset($_POST['batch']) ? $_POST['batch'] : [];
        if (empty($batch) || !is_array($batch)) {
            wp_send_json_error(['message' => 'Invalid batch data.']);
        }
    
        $rollback_data = [];
        $errors = [];
        try {
            foreach ($batch as $item) {
                $url = $item['url'];
                $new_alt_text = $item['new_alt_text'];
    
                $attachment_id = attachment_url_to_postid($url);
    
                if ($attachment_id) {
                    $current_alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
                    $rollback_data[] = [
                        'attachment_id' => $attachment_id,
                        'old_alt_text' => $current_alt_text,
                    ];
                    update_post_meta($attachment_id, '_wp_attachment_image_alt', sanitize_text_field($new_alt_text));
                    $this->log_message("Updated: $url | Previous: $current_alt_text | Updated: $new_alt_text");
                } else {
                    $errors[] = "Image not found: $url";
                    $this->log_message("Error: Image not found for URL $url");
                }
            }
    
            if (!empty($errors)) {
                wp_send_json_success([
                    'message' => 'Batch processed with some images skipped.',
                    'errors' => $errors,
                ]);
            }
    
            wp_send_json_success(['message' => 'Batch processed successfully.']);
        } catch (Exception $e) {
            $this->rollback_changes($rollback_data);
            $this->log_message("Critical Error: " . $e->getMessage());
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    

    private function rollback_changes($rollback_data) {
        foreach ($rollback_data as $data) {
            $attachment_id = $data['attachment_id'];
            $old_alt_text = $data['old_alt_text'];

            update_post_meta($attachment_id, '_wp_attachment_image_alt', $old_alt_text);
            $this->log_message("Rolled back: Attachment ID $attachment_id to Alt Text: $old_alt_text");
        }
    }

    private function log_message($message) {
        if (empty($this->log_file)) {
            $this->initialize_log_file();
        }

        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] $message" . PHP_EOL;
        file_put_contents($this->log_file, $log_entry, FILE_APPEND);
    }

    public function view_log() {
        check_ajax_referer('bulk_meta_updater_nonce', 'security');
    
        if (!isset($_POST['log_file']) || empty($_POST['log_file'])) {
            wp_send_json_error(['message' => 'Log file not specified.']);
        }
    
        $log_file = sanitize_file_name($_POST['log_file']);
        $logs_dir = plugin_dir_path(__FILE__) . 'logs/';
        $log_path = $logs_dir . $log_file;
    
        if (!file_exists($log_path)) {
            wp_send_json_error(['message' => 'Log file does not exist.']);
        }
    
        $content = file_get_contents($log_path);
        wp_send_json_success(['content' => esc_textarea($content)]);
    }
    

    public function delete_log() {
        check_ajax_referer('bulk_meta_updater_nonce', 'security');
    
        if (!isset($_POST['log_file']) || empty($_POST['log_file'])) {
            wp_send_json_error(['message' => 'Log file not specified.']);
        }
    
        $log_file = sanitize_file_name($_POST['log_file']);
        $logs_dir = plugin_dir_path(__FILE__) . 'logs/';
        $log_path = $logs_dir . $log_file;
    
        if (!file_exists($log_path)) {
            wp_send_json_error(['message' => 'Log file does not exist.']);
        }
    
        if (unlink($log_path)) {
            wp_send_json_success(['message' => 'Log file deleted successfully.']);
        } else {
            wp_send_json_error(['message' => 'Failed to delete log file.']);
        }
    }
    
}

new BulkMetaUpdater();