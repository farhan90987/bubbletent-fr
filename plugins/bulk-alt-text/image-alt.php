<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class ImageAltUpdater {
    public function __construct() {
        add_action('wp_ajax_process_image_alt_file', [$this, 'process_image_alt_file']);
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        echo '<div class="wrap">';
        echo '<h1>Image Alt Text Processor</h1>';
        echo '<form id="alt-text-upload-form" enctype="multipart/form-data">';
        echo '<input type="file" id="alt_text_file" name="alt_text_file" accept=".xlsx,.xls" required>';
        echo '<button type="button" id="process-file-button" class="button button-primary">Process File</button>';
        echo '</form>';
        echo '<div id="progress-container" style="margin-top: 20px; display: none;">';
        echo '<progress id="progress-bar" value="0" max="100"></progress>';
        echo '<p id="progress-text"></p>';
        echo '</div>';
        echo '</div>';
        echo '<button id="export-images-button" class="button button-secondary" style="margin-top: 20px;">Export Images</button>';
    }

    public function process_image_alt_file() {
        check_ajax_referer('bulk_meta_updater_nonce', 'security');
    
        if (!isset($_FILES['file']) && empty($_POST['temp_file_path'])) {
            wp_send_json_error(['message' => 'No file uploaded or file path missing.']);
        }
    
        try {
            $file_path = $_POST['temp_file_path'] ?? null;
            if (!$file_path) {
                $file_path = $_FILES['file']['tmp_name'];
                $uploads_dir = wp_upload_dir();
                $temp_dir = $uploads_dir['basedir'] . '/temp_files/';
                if (!is_dir($temp_dir)) {
                    mkdir($temp_dir, 0755, true);
                }
                $file_path = $temp_dir . uniqid('alt_text_import_', true) . '.xlsx';
                move_uploaded_file($_FILES['file']['tmp_name'], $file_path);
            }
    
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
    
            $total_rows = count($rows) - 1;
            $processed = intval($_POST['processed'] ?? 0);
    
            $batch_size = 10;
            $start = $processed;
            $end = min($start + $batch_size, $total_rows);
    
            for ($i = $start + 1; $i <= $end; $i++) {
                $row = $rows[$i];
                $image_id = intval($row[0]);
                if ($image_id) {
                    update_post_meta($image_id, '_wp_attachment_image_alt', sanitize_text_field($row[2]));
                }
            }
    
            $processed += $batch_size;
    
            if ($processed < $total_rows) {
                wp_send_json_success(['progress' => $processed, 'total' => $total_rows, 'complete' => false, 'temp_file_path' => $file_path]);
            } else {
                unlink($file_path);
                wp_send_json_success(['progress' => $total_rows, 'total' => $total_rows, 'complete' => true]);
            }
        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Error processing file: ' . $e->getMessage()]);
        }
    }

    
    
}

new ImageAltUpdater();
