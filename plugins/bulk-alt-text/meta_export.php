<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MetaExport {
    public function __construct() {
        add_action('wp_ajax_export_meta_data', [$this, 'export_meta_data']);
    }

    public function render_export_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        echo '<div class="wrap">';
        echo '<h1>Meta Export</h1>';
        echo '<form id="meta-export-form" method="post">';
        echo '<label for="export-type">Select Type:</label>';
        echo '<select id="export-type" name="export_type">';
        echo '<option value="pages">Pages</option>';
        echo '<option value="posts">Posts</option>';
        echo '<option value="products">Products</option>';
        echo '<option value="listings">Listings</option>';
        echo '</select>';
        echo '<button type="button" id="export-button" class="button button-primary">Export</button>';
        echo '</form>';
        echo '<div id="export-result"></div>';
        echo '</div>';
    }

    public function export_meta_data() {
        check_ajax_referer('bulk_meta_updater_nonce', 'security');
    
        $export_type = isset($_POST['export_type']) ? sanitize_text_field($_POST['export_type']) : '';
    
        if (!in_array($export_type, ['pages', 'posts', 'products', 'listings'])) {
            wp_send_json_error(['message' => 'Invalid export type.']);
        }
        
    
        global $wpdb;
    
        $post_type = '';
        switch ($export_type) {
            case 'pages':
                $post_type = 'page';
                break;
            case 'posts':
                $post_type = 'post';
                break;
            case 'products':
                $post_type = 'product';
                break;
                case 'listings':
                    $post_type = 'listing';
                    break;
        }
    
        // Get the default language using WPML
        $default_language = apply_filters('wpml_default_language', NULL);
        if (!$default_language) {
            wp_send_json_error(['message' => 'Default language not found.']);
        }
    
        // Fetch posts in the default language
        $query = $wpdb->prepare(
            "SELECT DISTINCT p.ID, p.post_title, p.post_type, p.guid, p.post_status,
                (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = '_yoast_wpseo_title') AS seo_title,
                (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = '_yoast_wpseo_metadesc') AS meta_description
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->prefix}icl_translations t ON p.ID = t.element_id
            WHERE p.post_type = %s
              AND p.post_status IN ('publish', 'private', 'draft')
              AND t.language_code = %s
              AND (t.source_language_code IS NULL OR t.source_language_code = '')",
            $post_type,
            $default_language
        );
    
        $results = $wpdb->get_results($query);
    
        if (empty($results)) {
            wp_send_json_error(['message' => 'No data found for the selected type.']);
        }
    
        // Ensure the exports directory exists
        $exports_dir = plugin_dir_path(__FILE__) . 'exports';
        if (!is_dir($exports_dir)) {
            if (!mkdir($exports_dir, 0755, true) && !is_dir($exports_dir)) {
                wp_send_json_error(['message' => 'Failed to create exports directory.']);
            }
        }
    
        // Prepare CSV
        $file_name = 'meta_export_' . $export_type . '_' . date('Y-m-d_H-i-s') . '.csv';
        $file_path = $exports_dir . '/' . $file_name;
    
        $file = fopen($file_path, 'w');
        if (!$file) {
            wp_send_json_error(['message' => 'Failed to create the export file.']);
        }
    
        // Ensure UTF-8 BOM for special characters
        fwrite($file, chr(239) . chr(187) . chr(191));
    
        // Write CSV headers (aligned with import format)
        fputcsv($file, ['ID', 'Title', 'Type', 'URL', 'SEO Title', 'Meta Description']);
    
        // Write data rows
        foreach ($results as $row) {
            fputcsv($file, [
                $row->ID,
                $row->post_title,
                $row->post_type,
                get_permalink($row->ID), // Ensure correct URL
                $row->seo_title ?: 'N/A',
                $row->meta_description ?: 'N/A',
            ]);
        }
    
        fclose($file);
    
        // Return file URL
        $file_url = plugins_url('exports/' . $file_name, __FILE__);
        wp_send_json_success(['file_url' => $file_url]);
    }
}

new MetaExport();
