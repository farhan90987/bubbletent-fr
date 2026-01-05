<?php
/*
Plugin Name: Excel to Database Importer
Description: This plugin allows admins to upload and import Excel files into a custom database table with correct column names and additional functionality.
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Add actions to initialize the plugin
add_action('admin_menu', 'add_excel_import_menu');
add_action('admin_enqueue_scripts', 'enqueue_excel_import_scripts');

// Include PHPSpreadsheet
require __DIR__ . '/vendor/autoload.php';  // Ensure you point this to where Composer installs PHPSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

// Function to add the menu item in the admin dashboard
function add_excel_import_menu() {
    add_menu_page(
        'Excel Import',         // Page Title
        'Excel Import',         // Menu Title
        'manage_options',       // Capability
        'excel-import',         // Menu Slug
        'render_excel_import_page', // Function to display content
        'dashicons-upload',     // Icon
        6                       // Position
    );
}

// Function to render the page content
function render_excel_import_page() {
    ?>
    <div class="wrap">
        <h1>Import Excel File</h1>
        <form id="excel-import-form" enctype="multipart/form-data" method="POST">
            <input type="file" name="excel_file" id="excel_file" accept=".xlsx">
            <input type="submit" name="import_excel" value="Import Excel" class="button button-primary">
        </form>
        <div id="import-message">
            <?php process_excel_import(); ?>
        </div>
    </div>
    <?php
}

// Modify custom table to add 'status' column if not exists
function modify_custom_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_excel_table';

    // Check if the 'status' column exists, and if not, add it
    $column = $wpdb->get_results("SHOW COLUMNS FROM `$table_name` LIKE 'status'");

    if (empty($column)) {
        // Add the 'status' column with default value of 0 (not used)
        $wpdb->query("ALTER TABLE $table_name ADD status TINYINT(1) DEFAULT 0");
    }
}
register_activation_hook(__FILE__, 'modify_custom_table');

// Enqueue scripts for the admin page if needed
function enqueue_excel_import_scripts($hook) {
    if ($hook != 'toplevel_page_excel-import') {
        return;
    }
    // You can enqueue scripts or styles here if needed
}

// Handle file upload and import Excel data
function process_excel_import() {
    if (isset($_POST['import_excel'])) {
        // Check if file is uploaded
        if (!empty($_FILES['excel_file']['tmp_name'])) {
            try {
                // Load the uploaded file
                $filePath = $_FILES['excel_file']['tmp_name'];
                $spreadsheet = IOFactory::load($filePath);
                $worksheet = $spreadsheet->getActiveSheet();

                // Get column headers (first row)
                $header = [];
                foreach ($worksheet->getRowIterator(1, 1) as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);

                    foreach ($cellIterator as $cell) {
                        $header[] = sanitize_title($cell->getValue()); // Use the first row for column names
                    }
                }

                // If there are not exactly two columns, return an error
                if (count($header) != 2) {
                    echo "<div class='error'><p>The Excel file must have exactly 2 columns.</p></div>";
                    return;
                }

                // Insert into the custom database table
                global $wpdb;
                $table_name = $wpdb->prefix . 'custom_excel_table'; // Ensure this matches the table created

                // Create the table with the correct column names if not exists
                create_custom_table($header[0], $header[1]);

                // Prepare to import the data (skip header row)
                $rows = [];
                foreach ($worksheet->getRowIterator(2) as $row) { // Start from row 2 to skip the header
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    $rowData = [];

                    foreach ($cellIterator as $cell) {
                        $rowData[] = $cell->getValue();
                    }

                    // Only insert rows that have values
                    if (!empty($rowData[0]) && !empty($rowData[1])) {
                        $wpdb->insert(
                            $table_name,
                            array(
                                $header[0] => $rowData[0],
                                $header[1] => $rowData[1],
                                'status' => 0 // Default status for newly inserted codes
                            )
                        );
                    }
                }

                echo "<div class='updated'><p>Data imported successfully.</p></div>";
            } catch (Exception $e) {
                echo "<div class='error'><p>There was an error processing the file: " . $e->getMessage() . "</p></div>";
            }
        } else {
            echo "<div class='error'><p>Please upload a valid Excel file.</p></div>";
        }
    }
}

// Create the database table if it doesn't exist
function create_custom_table($column1, $column2) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_excel_table'; // Adjust this to your needs

    $charset_collate = $wpdb->get_charset_collate();

    // Create the table with two columns matching the names in the Excel sheet
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        $column1 varchar(255) NOT NULL,
        $column2 varchar(255) NOT NULL,
        status TINYINT(1) DEFAULT 0, /* New status column to track used/not used */
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
