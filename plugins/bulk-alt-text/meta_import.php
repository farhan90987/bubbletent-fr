<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MetaImport {
    private $log_file;

    public function __construct() {
        add_action('wp_ajax_preview_meta_import', [$this, 'preview_meta_import']);
        add_action('wp_ajax_import_meta_data', [$this, 'import_meta_data']);
    }

    private function initialize_log_file($import_type) {
        $logs_dir = plugin_dir_path(__FILE__) . 'logs/';
        if (!is_dir($logs_dir)) {
            mkdir($logs_dir, 0755, true);
        }

        $this->log_file = $logs_dir . "import_{$import_type}_" . date('Y-m-d_H-i-s') . '.txt';
        file_put_contents($this->log_file, "Import started at " . date('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND);
    }

    private function log_message($message) {
        if (!empty($this->log_file)) {
            $timestamp = date('Y-m-d H:i:s');
            $log_entry = "[$timestamp] $message" . PHP_EOL;
            file_put_contents($this->log_file, $log_entry, FILE_APPEND);
        }
    }

    private function convert_to_utf8($file_path) {
        $uploads_dir = wp_upload_dir();
        $temp_file_path = $uploads_dir['basedir'] . '/temp_utf8_' . uniqid() . '.csv';
        $file_contents = file_get_contents($file_path);
    
        // Detect encoding and convert to UTF-8 if necessary
        $encoding = mb_detect_encoding($file_contents, 'UTF-8, ISO-8859-1, WINDOWS-1252', true);
        if ($encoding !== 'UTF-8') {
            $file_contents = mb_convert_encoding($file_contents, 'UTF-8', $encoding);
        }
    
        // Replace misinterpreted characters
        $file_contents = str_replace("", "–", $file_contents); // Replace misinterpreted en dash
        $file_contents = str_replace("", "'", $file_contents); // Replace single quotes (if needed)
    
        // Save the corrected content back to a temporary file
        file_put_contents($temp_file_path, $file_contents);
        return $temp_file_path;
    }
    
    

    private function save_uploaded_file_to_temp($uploaded_file) {
        $temp_dir = plugin_dir_path(__FILE__) . 'temp_files/';
        if (!is_dir($temp_dir)) {
            mkdir($temp_dir, 0755, true);
        }
    
        $temp_file_path = $temp_dir . uniqid('import_', true) . '.csv';
        if (move_uploaded_file($uploaded_file, $temp_file_path)) {
            return $temp_file_path;
        } else {
            wp_send_json_error(['message' => 'Failed to move uploaded file to temporary directory.']);
        }
    }
    
    
    

    public function render_import_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        echo '<div class="wrap">';
        echo '<h1>Meta Import</h1>';
        echo '<form id="meta-import-form" enctype="multipart/form-data">';
        echo '<label for="import-type">Select Type:</label>';
        echo '<select id="import-type" name="import_type">';
        echo '<option value="pages">Pages</option>';
        echo '<option value="posts">Posts</option>';
        echo '<option value="products">Products</option>';
        echo '<option value="listings">Listings</option>';
        echo '</select>';
        echo '<input type="file" id="import-file" name="import_file" accept=".csv" required>';
        echo '<button type="button" id="preview-button" class="button button-secondary">Preview Changes</button>';
        echo '<button type="button" id="import-button" class="button button-primary" style="display:none;">Import</button>';
        echo '</form>';
        echo '<div id="import-preview" style="margin-top: 20px;"></div>';
        echo '</div>';

// Add progress modal HTML
echo '<div id="progress-modal" style="display: none;">';
echo '<div class="progress-container">';
echo '<progress id="progress-bar" value="0" max="100"></progress>';
echo '<p id="progress-text"></p>';
echo '<button id="close-progress" class="button button-secondary">Close</button>';
echo '</div>';
echo '</div>';
    }

    public function preview_meta_import() {
        check_ajax_referer('bulk_meta_updater_nonce', 'security');
    
        if (!isset($_FILES['import_file']) || empty($_FILES['import_file']['tmp_name'])) {
            wp_send_json_error(['message' => 'No file uploaded.']);
        }
    
        // Get the selected import type from the dropdown
        $import_type = sanitize_text_field($_POST['import_type'] ?? '');
    
        // Ensure the import type is valid
        if (!in_array($import_type, ['pages', 'posts', 'products', 'listings'])) {
            wp_send_json_error(['message' => 'Invalid import type selected.']);
        }
    
        // Map dropdown values to file types
        $type_mapping = [
            'pages' => 'page',
            'posts' => 'post',
            'products' => 'product',
            'listings' => 'listing',
        ];
    
        // Convert the selected type to match the file's format
        $expected_file_type = $type_mapping[$import_type] ?? '';
    
        $file_path = $_FILES['import_file']['tmp_name'];
    
        // Ensure the file is in UTF-8 encoding
        $file_contents = file_get_contents($file_path);
        $encoding = mb_detect_encoding($file_contents, 'UTF-8, ISO-8859-1, WINDOWS-1252', true);
        if ($encoding !== 'UTF-8') {
            $file_contents = mb_convert_encoding($file_contents, 'UTF-8', $encoding);
        }
    
        // Handle UTF-8 BOM
        if (substr($file_contents, 0, 3) === "\xEF\xBB\xBF") {
            $file_contents = substr($file_contents, 3); // Remove BOM
        }
    
        // Replace misinterpreted special characters (if any)
        $file_contents = str_replace("", "–", $file_contents); // Replace misinterpreted en dash
        $file_contents = str_replace("", "'", $file_contents); // Replace misinterpreted single quotes
    
        // Save the normalized content back to the file
        file_put_contents($file_path, $file_contents);
    
        $file_handle = fopen($file_path, 'r');
        if (!$file_handle) {
            wp_send_json_error(['message' => 'Failed to open uploaded file.']);
        }
    
        // Read and normalize the header row
        $header = array_map('trim', fgetcsv($file_handle));
        $expected_header = ['ID', 'Title', 'Type', 'URL', 'SEO Title', 'Meta Description'];
    
        // Normalize case for comparison
        $header = array_map('strtolower', $header);
        $expected_header = array_map('strtolower', $expected_header);
    
        if ($header !== $expected_header) {
            fclose($file_handle);
            wp_send_json_error(['message' => 'Invalid file format. Expected headers: ' . implode(', ', $expected_header)]);
        }
    
        // Validate post types in the file
        $is_valid_type = true;
        while (($row = fgetcsv($file_handle)) !== false) {
            if (strtolower(trim($row[2])) !== $expected_file_type) {
                $is_valid_type = false;
                break;
            }
        }
    
        // If validation fails, stop processing and return an error
        if (!$is_valid_type) {
            fclose($file_handle);
            wp_send_json_error([
                'message' => "The uploaded file contains rows with a post type that does not match the selected type ({$import_type})."
            ]);
        }
    
        // Rewind file pointer to process rows for preview
        rewind($file_handle);
        fgetcsv($file_handle); // Skip the header row
    
        $preview_data = [];
        while (($row = fgetcsv($file_handle)) !== false) {
            $post_id = absint($row[0]);
            $old_seo_title = get_post_meta($post_id, '_yoast_wpseo_title', true);
            $old_meta_description = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
            $old_page_title = get_the_title($post_id);
    
            $preview_data[] = [
                'ID' => $row[0],
                'Title' => $row[1],
                'Type' => $row[2],
                'URL' => $row[3],
                'Old_Page_Title' => $old_page_title,
                'New_Page_Title' => $row[1],
                'Old_SEO_Title' => $old_seo_title,
                'New_SEO_Title' => $row[4],
                'Old_Meta_Description' => $old_meta_description,
                'New_Meta_Description' => $row[5],
            ];
        }
    
        fclose($file_handle);
    
        wp_send_json_success(['preview' => $preview_data]);
    }
    
    
    
    
    

    public function import_meta_data() {
        ob_start();
        check_ajax_referer('bulk_meta_updater_nonce', 'security');
    
        $import_type = sanitize_text_field($_POST['import_type'] ?? '');
        $processed = intval($_POST['processed'] ?? 0);
        $temp_file_path = sanitize_text_field($_POST['temp_file_path'] ?? '');
    
        if (!in_array($import_type, ['pages', 'posts', 'products', 'listings'])) {
            ob_end_clean();
            wp_send_json_error(['message' => 'Invalid import type.']);
        }
        
    
        $this->initialize_log_file($import_type);
    
        if (!$temp_file_path && !isset($_FILES['import_file']['tmp_name'])) {
            ob_end_clean();
            wp_send_json_error(['message' => 'No file uploaded or file path missing.']);
        }
    
        if (!$temp_file_path) {
            $temp_file_path = $this->convert_to_utf8($_FILES['import_file']['tmp_name']);
        }
    
        $total_records = count(file($temp_file_path)) - 1;
    
        $file_handle = fopen($temp_file_path, 'r');
        if (!$file_handle) {
            $this->log_message("Failed to open the file at {$temp_file_path}");
            ob_end_clean();
            wp_send_json_error(['message' => 'Failed to open the uploaded file.']);
        }
    
        if ($processed === 0) {
            fgetcsv($file_handle);
        } else {
            for ($i = 0; $i <= $processed; $i++) {
                fgetcsv($file_handle);
            }
        }
    
        $batch_size = 10;
        $updates = 0;
        $errors = [];
    
        while (($row = fgetcsv($file_handle)) !== false && $updates < $batch_size) {
            $post_id = absint($row[0]);
            if (!$post_id) continue;
    
            try {
                wp_update_post(['ID' => $post_id, 'post_title' => sanitize_text_field($row[1])]);
    
                if (!empty($row[4])) {
                    update_post_meta($post_id, '_yoast_wpseo_title', sanitize_text_field($row[4]));
                }
                if (!empty($row[5])) {
                    update_post_meta($post_id, '_yoast_wpseo_metadesc', sanitize_text_field($row[5]));
                }
    
                $updates++;
                $this->log_message("Successfully updated post ID: {$post_id}");
            } catch (Exception $e) {
                $errors[] = "Error updating post {$post_id}: " . $e->getMessage();
                $this->log_message("Error updating post {$post_id}: " . $e->getMessage());
            }
        }
    
        fclose($file_handle);
    
        $new_processed = $processed + $updates;
        $is_complete = $new_processed >= $total_records;
    
        if ($is_complete && file_exists($temp_file_path)) {
            unlink($temp_file_path);
            $this->log_message("Import completed. Total records processed: {$new_processed}");
        }
    
        ob_end_clean();
        wp_send_json_success([
            'progress' => $new_processed,
            'processed' => $new_processed,
            'total_records' => $total_records,
            'complete' => $is_complete,
            'temp_file_path' => $is_complete ? null : $temp_file_path,
            'errors' => $errors,
        ]);
    }
    

    
    
    
    
    
    
    
}

new MetaImport();