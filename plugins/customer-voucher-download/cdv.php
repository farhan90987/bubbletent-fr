<?php
/**
 * Plugin Name: Custom Voucher Download
 * Description: Adds voucher download functionality to WooCommerce "Thank You" pages and order completion emails.
 * Version: 1.0.3
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit;

// Add the admin menu and settings page
add_action('admin_menu', 'custom_voucher_admin_menu');
function custom_voucher_admin_menu() {
    add_menu_page(
        'Voucher Settings', 
        'Voucher Settings', 
        'manage_options', 
        'custom-voucher-settings', 
        'custom_voucher_settings_page', 
        'dashicons-download', 
        100
    );
}

// Define the settings page
function custom_voucher_settings_page() {
    ?>
    <div class="wrap">
        <h1>Voucher Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('custom_voucher_settings_group');
            do_settings_sections('custom-voucher-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register plugin settings
add_action('admin_init', 'custom_voucher_register_settings');
function custom_voucher_register_settings() {
    register_setting('custom_voucher_settings_group', 'custom_voucher_validity_start');
    register_setting('custom_voucher_settings_group', 'custom_voucher_validity_end');
    register_setting('custom_voucher_settings_group', 'custom_voucher_enable');
    register_setting('custom_voucher_settings_group', 'custom_voucher_logging');
    register_setting('custom_voucher_settings_group', 'custom_voucher_files', 'custom_voucher_sanitize_files');

    add_settings_section('custom_voucher_main', 'Main Settings', null, 'custom-voucher-settings');

    add_settings_field('custom_voucher_validity_start', 'Start Date/Time', 'custom_voucher_validity_start_callback', 'custom-voucher-settings', 'custom_voucher_main');
    add_settings_field('custom_voucher_validity_end', 'End Date/Time', 'custom_voucher_validity_end_callback', 'custom-voucher-settings', 'custom_voucher_main');
    add_settings_field('custom_voucher_enable', 'Enable Plugin', 'custom_voucher_enable_callback', 'custom-voucher-settings', 'custom_voucher_main');
    add_settings_field('custom_voucher_logging', 'Enable Logging', 'custom_voucher_logging_callback', 'custom-voucher-settings', 'custom_voucher_main');
    add_settings_field('custom_voucher_files', 'Voucher Files', 'custom_voucher_files_callback', 'custom-voucher-settings', 'custom_voucher_main');
}

// Sanitize file data
function custom_voucher_sanitize_files($input) {
    if (!is_array($input)) return [];
    foreach ($input as $key => $file) {
        $input[$key]['title'] = sanitize_text_field($file['title']);
        $input[$key]['file'] = esc_url_raw($file['file']);
    }
    return $input;
}

// Callbacks for the settings fields
function custom_voucher_validity_start_callback() {
    $value = get_option('custom_voucher_validity_start', '');
    echo '<input type="datetime-local" name="custom_voucher_validity_start" value="' . esc_attr($value) . '">';
}

function custom_voucher_validity_end_callback() {
    $value = get_option('custom_voucher_validity_end', '');
    echo '<input type="datetime-local" name="custom_voucher_validity_end" value="' . esc_attr($value) . '">';
}

function custom_voucher_enable_callback() {
    $checked = get_option('custom_voucher_enable', 0) ? 'checked' : '';
    echo '<input type="checkbox" name="custom_voucher_enable" value="1" ' . $checked . '>';
}

function custom_voucher_logging_callback() {
    $checked = get_option('custom_voucher_logging', 0) ? 'checked' : '';
    echo '<input type="checkbox" name="custom_voucher_logging" value="1" ' . $checked . '>';
}

// Render the repeater field for files
function custom_voucher_files_callback() {
    $files = get_option('custom_voucher_files', []);
    ?>
    <div id="custom-voucher-repeater">
        <?php if (!empty($files)) : ?>
            <?php foreach ($files as $index => $file) : ?>
                <div class="custom-voucher-item">
                    <input type="text" name="custom_voucher_files[<?php echo $index; ?>][title]" value="<?php echo esc_attr($file['title']); ?>" placeholder="PDF Title">
                    <input type="hidden" name="custom_voucher_files[<?php echo $index; ?>][file]" value="<?php echo esc_url($file['file']); ?>" class="file-url">
                    <button type="button" class="upload-file-button">Select File</button>
                    <button type="button" class="remove-voucher">Remove</button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button type="button" id="add-voucher">Add Voucher</button>
    <script>
        (function($) {
            $(document).ready(function() {
                let repeater = $('#custom-voucher-repeater');
                let addButton = $('#add-voucher');

                addButton.on('click', function(e) {
                    e.preventDefault();
                    let index = repeater.find('.custom-voucher-item').length;
                    let newItem = `
                        <div class="custom-voucher-item">
                            <input type="text" name="custom_voucher_files[${index}][title]" placeholder="PDF Title">
                            <input type="hidden" name="custom_voucher_files[${index}][file]" class="file-url">
                            <button type="button" class="upload-file-button">Select File</button>
                            <button type="button" class="remove-voucher">Remove</button>
                        </div>`;
                    repeater.append(newItem);
                });

                repeater.on('click', '.remove-voucher', function(e) {
                    e.preventDefault();
                    $(this).closest('.custom-voucher-item').remove();
                });

                repeater.on('click', '.upload-file-button', function(e) {
                    e.preventDefault();
                    let fileFrame;
                    let input = $(this).siblings('.file-url');
                    if (fileFrame) {
                        fileFrame.open();
                        return;
                    }
                    fileFrame = wp.media({
                        title: 'Select or Upload PDF',
                        button: {
                            text: 'Use this file',
                        },
                        multiple: false
                    });
                    fileFrame.on('select', function() {
                        const attachment = fileFrame.state().get('selection').first().toJSON();
                        input.val(attachment.url);
                    });
                    fileFrame.open();
                });
            });
        })(jQuery);
    </script>
    <style>
        #custom-voucher-repeater {
            margin-bottom: 20px;
        }
        .custom-voucher-item {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }
        .custom-voucher-item input {
            flex: 1;
        }
        .upload-file-button,
        .remove-voucher {
            background: #007cba;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
        .remove-voucher {
            background: #dc3545;
        }
        #add-voucher {
            background: #007cba;
            color: white;
            border: none;
            padding: 5px 15px;
            cursor: pointer;
        }
    </style>
    <?php
}

// Add voucher dropdown to WooCommerce "Thank You" page
add_action('woocommerce_thankyou', 'custom_voucher_thankyou_page');
function custom_voucher_thankyou_page($order_id) {
    if (!get_option('custom_voucher_enable')) return;

    $order = wc_get_order($order_id);
    $items = $order->get_items();
    $valid = false;

    foreach ($items as $item) {
        $product = wc_get_product($item->get_product_id());
        if ($product->is_downloadable() || get_post_meta($product->get_id(), '_wpdesk_pdf_coupons', true) === 'yes') {
            $valid = true;
            break;
        }
    }

    if (!$valid) return;

    $files = get_option('custom_voucher_files', []);
    if (empty($files)) return;

    echo '<h3>' . __('Tu peux télécharger et imprimer ton bon directement ici.:', 'ccdv') . '</h3>';
    echo '<p>' . __('Veuillez saisir le numéro de commande comme numéro de bon dans le champ en bas à gauche.', 'ccdv') . '</p>';
    echo '<div class="voucher-dropdown-wrapper">';
    echo '<select id="voucher-select">';
    foreach ($files as $file) {
        echo '<option value="' . esc_url($file['file']) . '">' . esc_html($file['title']) . '</option>';
    }
    echo '</select>';
    echo '<button id="voucher-download" class="voucher-download-button">' . __('Téléchargement', 'ccdv') . '</button>'; // Button
    echo '</div>';
    ?>
    <script>
        document.getElementById('voucher-download').addEventListener('click', function() {
            const url = document.getElementById('voucher-select').value;
            window.open(url, '_blank');
        });
    </script>
    <style>
        /* Wrapper styles for alignment */
        .voucher-dropdown-wrapper {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-top: 10px;
        }

        /* Dropdown styling */
        #voucher-select {
            width: auto;
            padding: 8px 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #f9f9f9;
        }

        /* Button styling */
        .voucher-download-button {
            background-color: #54775e;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 50px;
            cursor: pointer;
        }

        .voucher-download-button:hover {
            background-color: #54775e;
        }

        /* Responsive layout for mobile */
        @media screen and (max-width: 768px) {
            .voucher-dropdown-wrapper {
                display: block; /* Stack elements vertically */
                gap: 0; /* Remove gap between elements */
            }

            .voucher-download-button {
                margin-top: 10px; /* Add space above the button */
                width: 100%; /* Optional: Make the button full-width */
            }
        }
    </style>
    <?php
}


add_action('woocommerce_email_after_order_table', 'custom_voucher_email_content_for_confirmation', 10, 4);
function custom_voucher_email_content_for_confirmation($order, $sent_to_admin, $plain_text, $email) {
    // Check email type
    if (!in_array($email->id, ['customer_completed_order', 'customer_processing_order'])) {
        return;
    }

    if (!get_option('custom_voucher_enable')) return;

    $items = $order->get_items();
    $valid = false;

    // Check for specific products in the order
    foreach ($items as $item) {
        $product = wc_get_product($item->get_product_id());
        if ($product->is_downloadable() || get_post_meta($product->get_id(), '_wpdesk_pdf_coupons', true) === 'yes') {
            $valid = true;
            break;
        }
    }

    if (!$valid) return;

    $files = get_option('custom_voucher_files', []);
    if (empty($files)) return;

    // Display voucher links in the email
    echo '<h3>' . __('Tu peux télécharger et imprimer ton bon directement ici:', 'ccdv') . '</h3>';
    echo '<p>' . __('Veuillez saisir le numéro de commande comme numéro de bon dans le champ en bas à gauche.', 'ccdv') . '</p>';
    echo '<ul>';
    foreach ($files as $file) {
        echo '<li><a href="' . esc_url($file['file']) . '" target="_blank" style="background-color: #54775e; color: white; padding: 8px 12px; border-radius: 50px; text-decoration: none; font-size: 16px; display: inline-block; margin-bottom: 5px;">' . esc_html($file['title']) . '</a></li>';
    }
    echo '</ul>';
}
