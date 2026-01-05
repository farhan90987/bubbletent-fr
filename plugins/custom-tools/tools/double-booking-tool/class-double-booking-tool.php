<?php
namespace Custom_Tools;

if (!defined('ABSPATH')) {
    exit;
}

class Double_Booking_Tool {
    public function init() {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Add AJAX handler for popup data (ADDED THIS LINE)
        add_action('wp_ajax_get_smoobu_booking_details', array($this, 'ajax_get_smoobu_booking_details'));
    }
    
    public function add_admin_menu() {
        add_submenu_page(
            'custom-tools',
            __('Double Booking Checker', 'custom-tools'),
            __('Double Booking Checker', 'custom-tools'),
            'manage_options',
            'double-booking-tool',
            array($this, 'render_admin_page')
        );
    }
    
    public function render_admin_page() {
        // Check API key using $this (current instance)
        $has_api_key = $this->check_smoobu_api_key();

        if (!$has_api_key) {
            echo '<div class="notice notice-warning"><p>';
            _e('Smoobu API key not found. Smoobu status will not be available.', 'custom-tools');
            echo '</p></div>';
        }
        
        // Load the template
        include CUSTOM_TOOLS_PATH . 'tools/double-booking-tool/templates/admin-page.php';
    }
    
    public function enqueue_scripts($hook) {
        if ($hook === 'custom-tools_page_double-booking-tool') {
            wp_enqueue_style('double-booking-tool', CUSTOM_TOOLS_URL . 'tools/double-booking-tool/assets/css/style.css', array(), CUSTOM_TOOLS_VERSION);
            
            // Add modal CSS
            wp_enqueue_style('double-booking-modal', CUSTOM_TOOLS_URL . 'tools/double-booking-tool/assets/css/modal.css', array(), CUSTOM_TOOLS_VERSION);
            
            wp_enqueue_script('xlsx-js', 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js', array(), '0.18.5', true);
            wp_enqueue_script('double-booking-tool', CUSTOM_TOOLS_URL . 'tools/double-booking-tool/assets/js/script.js', array('jquery', 'xlsx-js'), CUSTOM_TOOLS_VERSION, true);
            
            // Localize script for AJAX
            wp_localize_script('double-booking-tool', 'smoobu_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('smoobu_booking_details')
            ));
        }
    }
    
public function fetch_orders($start_date, $end_date, $listing_id = '', $force_refresh = false) {
    global $wpdb;
    
    if (empty($start_date) || empty($end_date)) {
        return array();
    }
    
    $start_dt = date('Y-m-d H:i:s', strtotime($start_date));
    $end_dt   = date('Y-m-d H:i:s', strtotime($end_date . ' 23:59:59'));
    
    // Get product IDs based on listing selection
    $product_ids = array();
    if (!empty($listing_id)) {
        $product_ids = $this->get_listing_translated_products($listing_id);
    }
    
    // Include ALL order statuses
    $statuses = array(
        'wc-pending', 'wc-processing', 'wc-on-hold', 
        'wc-completed', 'wc-cancelled', 'wc-refunded', 
        'wc-failed', 'wc-checkout-draft'
    );
    
    if (empty($product_ids)) {
        // Get all listing_booking product IDs
        $listing_booking_ids = $wpdb->get_col("
            SELECT ID FROM {$wpdb->prefix}posts 
            WHERE post_type = 'product' 
            AND post_status = 'publish'
        ");
        
        // Filter by product type listing_booking
        $product_ids = array();
        foreach ($listing_booking_ids as $product_id) {
            $product = wc_get_product($product_id);
            if ($product && $product->get_type() === 'listing_booking') {
                $product_ids[] = $product_id;
            }
        }
        
        if (empty($product_ids)) {
            return array();
        }
    }
    
    // Convert listing IDs to product IDs
    $final_product_ids = array();
    foreach ($product_ids as $item_id) {
        // Check if it's a listing ID (has product_id meta) or product ID
        $product_id = get_post_meta($item_id, 'product_id', true);
        if (!empty($product_id)) {
            $final_product_ids[] = $product_id;
        } else {
            $final_product_ids[] = $item_id;
        }
    }
    
    $product_ids = array_unique($final_product_ids);
    
    // Get orders that contain specific product IDs and have check-in/check-out dates
    $placeholders = implode(',', array_fill(0, count($product_ids), '%d'));
    
    $query = $wpdb->prepare("
        SELECT DISTINCT o.ID
        FROM {$wpdb->prefix}posts o
        INNER JOIN {$wpdb->prefix}woocommerce_order_items oi ON o.ID = oi.order_id
        INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oi.order_item_id = oim.order_item_id
        INNER JOIN {$wpdb->prefix}postmeta pm1 ON o.ID = pm1.post_id
        INNER JOIN {$wpdb->prefix}postmeta pm2 ON o.ID = pm2.post_id
        WHERE o.post_type = 'shop_order'
        AND o.post_status IN ('" . implode("','", $statuses) . "')
        AND o.post_date BETWEEN %s AND %s
        AND oim.meta_key IN ('_product_id', '_variation_id')
        AND oim.meta_value IN ($placeholders)
        AND pm1.meta_key = 'smoobu_calendar_start'
        AND pm1.meta_value != ''
        AND pm2.meta_key = 'smoobu_calendar_end'
        AND pm2.meta_value != ''
        ORDER BY o.post_date DESC
    ", array_merge(array($start_dt, $end_dt), $product_ids));

    $order_ids = $wpdb->get_col($query);
    
    if (empty($order_ids)) {
        return array();
    }
    
    $orders = array();
    $checkin_groups = array();
    
    foreach ($order_ids as $order_id) {
        $order = wc_get_order($order_id);
        if (!$order) continue;
        
        $checkin_date  = trim($order->get_meta('smoobu_calendar_start'));
        $checkout_date = trim($order->get_meta('smoobu_calendar_end'));
        
        // Skip orders that don't have both dates
        if (empty($checkin_date) || empty($checkout_date)) {
            continue;
        }
        
        // Normalize check-in date for comparison
        $normalized_checkin = $this->normalize_date($checkin_date);
        $checkin_formatted = $normalized_checkin ? date('Y-m-d', $normalized_checkin) : '';
        
        $is_double = false;
        $double_group_id = 0;
        
        if ($checkin_formatted) {
            if (isset($checkin_groups[$checkin_formatted])) {
                $is_double = true;
                $double_group_id = $checkin_groups[$checkin_formatted]['group_id'];
                $checkin_groups[$checkin_formatted]['count']++;
            } else {
                $double_group_id = count($checkin_groups) + 1;
                $checkin_groups[$checkin_formatted] = array(
                    'group_id' => $double_group_id,
                    'count' => 1,
                    'checkin_date' => $checkin_formatted
                );
            }
        }
        
        $first_name = $order->get_billing_first_name();
        $last_name = $order->get_billing_last_name();
        $customer_name = $first_name . ' ' . $last_name;
        $status = str_replace('wc-', '', $order->get_status());
        $order_date = $order->get_date_created()->date('Y-m-d H:i:s');
        $email = $order->get_billing_email();
        
        // Get Smoobu booking ID - try different possible meta keys
        $smoobu_booking_id = $order->get_meta('_smoobu_booking_id');

        // If not found, try alternative meta keys
        if (empty($smoobu_booking_id)) {
            $smoobu_booking_id = $order->get_meta('smoobu_booking_id');
        }
        if (empty($smoobu_booking_id)) {
            $smoobu_booking_id = $order->get_meta('smoobu_reservation_id');
        }
        if (empty($smoobu_booking_id)) {
            $smoobu_booking_id = $order->get_meta('_smoobu_reservation_id');
        }

        // Debug output for testing
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Order {$order_id} - Smoobu ID: " . ($smoobu_booking_id ?: 'Not found'));
        }
        
        // Pass the force_refresh parameter to get fresh Smoobu status
        $smoobu_status = $this->get_smoobu_booking_status($smoobu_booking_id, $force_refresh);
        
        $orders[] = array(
            'order_id' => $order_id,
            'order_date' => $order_date,
            'customer_name' => $customer_name,
            'email' => $email,
            'status' => $status,
            'checkin' => $checkin_date,
            'checkout' => $checkout_date,
            'is_double' => $is_double,
            'double_group_id' => $double_group_id,
            'checkin_formatted' => $checkin_formatted,
            'smoobu_booking_id' => $smoobu_booking_id,
            'smoobu_status' => $smoobu_status
        );
    }
    
    return $orders;
}
    
    // Get all translated product IDs from original listing ID
    private function get_listing_translated_products($original_listing_id) {
        // First get the product ID from the listing
        $product_id = get_post_meta($original_listing_id, 'product_id', true);
        
        if (empty($product_id)) {
            return array();
        }
        
        // Get translations of the product
        $translations = apply_filters('wpml_get_element_translations', null, apply_filters('wpml_element_trid', null, $product_id, 'post_product'), 'post_product');

        $ids = [];
        if (!empty($translations) && is_array($translations)) {
            foreach ($translations as $t) {
                if (!empty($t->element_id)) {
                    $ids[] = $t->element_id;
                }
            }
        }

        // Always include the original product ID
        if (!in_array($product_id, $ids)) {
            $ids[] = $product_id;
        }

        return $ids;
    }
    
    // Get all listings for dropdown
    public function get_all_listings() {
        $args = array(
            'post_type'      => 'listing',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'suppress_filters' => false,
        );
        
        $listings = get_posts($args);
        $listing_options = array();
        
        foreach ($listings as $listing) {
            // Only process original listing
            $original_listing_id = apply_filters('wpml_object_id', $listing->ID, 'listing', false, wpml_get_default_language());
            if ($original_listing_id !== $listing->ID) {
                continue; // Skip duplicates (translations)
            }
            
            $listing_options[$original_listing_id] = get_the_title($listing->ID);
        }
        
        return $listing_options;
    }
    
    private function check_smoobu_api_key() {
        $api_key = get_option('smoobu_api_key');
        return !empty($api_key);
    }
    
private function get_smoobu_booking_status($booking_id, $force_refresh = false) {
    if (empty($booking_id)) {
        return 'No Smoobu ID';
    }
    
    $transient_key = 'smoobu_status_' . $booking_id;
    
    // Skip cache if force refresh is requested
    if (!$force_refresh) {
        $cached_status = get_transient($transient_key);
        if ($cached_status !== false) {
            // Debug: Log cache hit
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("Smoobu Status Cache HIT for booking {$booking_id}: {$cached_status}");
            }
            return $cached_status;
        }
    }
    
    // Debug: Log API call
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("Smoobu Status API CALL for booking {$booking_id}. Force refresh: " . ($force_refresh ? 'Yes' : 'No'));
    }
    
    $api_key = get_option('smoobu_api_key');
    
    if (empty($api_key)) {
        return 'API Key Missing';
    }
    
    // Use the correct endpoint from documentation
    $api_url = 'https://login.smoobu.com/api/reservations/' . $booking_id;
    
    $response = wp_remote_get($api_url, array(
        'headers' => array(
            'Api-Key' => $api_key,
            'Accept' => 'application/json',
            'Cache-Control' => 'no-cache'
        ),
        'timeout' => 15,
        'sslverify' => false
    ));
    
    if (is_wp_error($response)) {
        return 'API Error';
    }
    
    $status_code = wp_remote_retrieve_response_code($response);
    $status = ''; // Initialize status variable
    
    if ($status_code === 200) {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        $status = $this->extract_smoobu_status($data);
        
    } elseif ($status_code === 404) {
        $status = 'Not Found In Smoobu';
        
    } elseif ($status_code === 401) {
        $status = 'API Authentication Failed';
        
    } else {
        $status = 'API Error (' . $status_code . ')';
    }
    
    // Debug: Log API response
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("Smoobu Status API RESPONSE for booking {$booking_id}: {$status} (Code: {$status_code})");
    }
    
    // Cache the new result
    set_transient($transient_key, $status, HOUR_IN_SECONDS);
    return $status;
}
private function extract_smoobu_status($data) {
    // If it's a 404 response or empty data
    if (empty($data)) {
        return 'Not Found In Smoobu';
    }
    
    // If we got an error response
    if (isset($data['status']) && $data['status'] === 401) {
        return 'API Error';
    }
    
    // Check if it's a valid reservation object with type field
    if (isset($data['id']) && isset($data['type'])) {
        $type = strtolower($data['type']);
        
        // Map Smoobu types to status labels
        $type_map = array(
            'reservation' => 'Reservation',
            'modification of booking' => 'Modified',
            'cancellation' => 'Cancelled'
        );
        
        return isset($type_map[$type]) ? $type_map[$type] : ucfirst($type);
    }
    
    // If it's an array of reservations (from list endpoint)
    if (is_array($data) && isset($data[0]['id'])) {
        foreach ($data as $reservation) {
            if (isset($reservation['id']) && isset($reservation['type'])) {
                $type = strtolower($reservation['type']);
                
                $type_map = array(
                    'reservation' => 'Reservation',
                    'modification of booking' => 'Modified', 
                    'cancellation' => 'Cancelled'
                );
                
                return isset($type_map[$type]) ? $type_map[$type] : ucfirst($type);
            }
        }
    }
    
    return 'Status Unknown';
}

    private function get_smoobu_status_from_list($api_key, $booking_id) {
        // Try getting status from the reservations list
        $list_endpoints = [
            'https://login.smoobu.com/api/reservations',
            'https://api.smoobu.com/reservations',
            'https://login.smoobu.com/api/bookings',
        ];
        
        foreach ($list_endpoints as $api_url) {
            $response = wp_remote_get($api_url, array(
                'headers' => array(
                    'API-KEY' => $api_key,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ),
                'timeout' => 20, // Longer timeout for list requests
                'sslverify' => false
            ));
            
            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $body = wp_remote_retrieve_body($response);
                $reservations = json_decode($body, true);
                
                if (is_array($reservations)) {
                    foreach ($reservations as $reservation) {
                        // Check different ID fields that might match our booking ID
                        $id_fields = ['id', 'reservationId', 'bookingId', 'number'];
                        
                        foreach ($id_fields as $id_field) {
                            if (isset($reservation[$id_field]) && $reservation[$id_field] == $booking_id) {
                                $status = $this->extract_smoobu_status($reservation);
                                if ($status) {
                                    $transient_key = 'smoobu_status_' . $booking_id;
                                    set_transient($transient_key, $status, HOUR_IN_SECONDS);
                                    return $status;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return 'Not Found';
    }
    
    private function normalize_date($date_str) {
        if (empty($date_str)) return false;
        
        $date_clean = str_replace(['/', '.'], '-', trim($date_str));
        $parts = explode('-', $date_clean);
        
        if (count($parts) === 3) {
            if (strlen($parts[0]) === 4) {
                return strtotime($date_clean);
            } else {
                return strtotime($parts[2] . '-' . $parts[1] . '-' . $parts[0]);
            }
        } else {
            return strtotime($date_str);
        }
    }
private function get_smoobu_status_class($status) {
    $status_map = array(
        'reservation' => 'confirmed',
        'modified' => 'pending',
        'cancelled' => 'cancelled',
        'not found in smoobu' => 'not-found',
        'api key missing' => 'api-key-missing',
        'api error' => 'api-error',
        'api authentication failed' => 'api-error',
        'no smoobu id' => 'no-smoobu-id',
        'status unknown' => 'status-unknown'
    );
    
    $status_key = strtolower($status);
    return isset($status_map[$status_key]) ? $status_map[$status_key] : 'status-unknown';
}


// Add this new method for AJAX popup data
public function ajax_get_smoobu_booking_details() {
    // Check nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'smoobu_booking_details')) {
        wp_die('Security check failed');
    }
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    
    if (empty($booking_id)) {
        wp_send_json_error('Booking ID is required');
    }
    
    $api_key = get_option('smoobu_api_key');
    
    if (empty($api_key)) {
        wp_send_json_error('API Key Missing');
    }
    
    // Fetch fresh data from Smoobu API (bypass cache)
    $api_url = 'https://login.smoobu.com/api/reservations/' . $booking_id;
    
    $response = wp_remote_get($api_url, array(
        'headers' => array(
            'Api-Key' => $api_key,
            'Accept' => 'application/json',
            'Cache-Control' => 'no-cache'
        ),
        'timeout' => 15,
        'sslverify' => false
    ));
    
    if (is_wp_error($response)) {
        wp_send_json_error('API Error: ' . $response->get_error_message());
    }
    
    $status_code = wp_remote_retrieve_response_code($response);
    
    if ($status_code === 200) {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        // Format the data for display
        $formatted_data = $this->format_booking_details($data);
        wp_send_json_success($formatted_data);
        
    } elseif ($status_code === 404) {
        wp_send_json_error('Booking not found in Smoobu');
        
    } else {
        wp_send_json_error('API Error (Status: ' . $status_code . ')');
    }
}

// Add this method to format the booking details
private function format_booking_details($data) {
    if (empty($data)) {
        return array('error' => 'No data received from Smoobu');
    }
    
    // Map the API response to a clean format
    $formatted = array(
        'basic_info' => array(
            'Booking ID' => isset($data['id']) ? $data['id'] : 'N/A',
            'Type' => isset($data['type']) ? ucfirst($data['type']) : 'N/A',
            'Arrival' => isset($data['arrival']) ? date('M j, Y', strtotime($data['arrival'])) : 'N/A',
            'Departure' => isset($data['departure']) ? date('M j, Y', strtotime($data['departure'])) : 'N/A',
            'Created' => isset($data['created-at']) ? date('M j, Y g:i A', strtotime($data['created-at'])) : 'N/A',
        ),
        
        'accommodation' => array(
            'Apartment' => isset($data['apartment']['name']) ? $data['apartment']['name'] : 'N/A',
            'Channel' => isset($data['channel']['name']) ? $data['channel']['name'] : 'N/A',
        ),
        
        'guest_info' => array(
            'Guest Name' => isset($data['guest-name']) ? $data['guest-name'] : 'N/A',
            'Email' => isset($data['email']) ? $data['email'] : 'N/A',
            'Phone' => isset($data['phone']) ? $data['phone'] : 'N/A',
            'Adults' => isset($data['adults']) ? $data['adults'] : '0',
            'Children' => isset($data['children']) ? $data['children'] : '0',
            'Language' => isset($data['language']) ? strtoupper($data['language']) : 'N/A',
        ),
        
        'check_times' => array(
            'Check-in' => isset($data['check-in']) ? $data['check-in'] : 'N/A',
            'Check-out' => isset($data['check-out']) ? $data['check-out'] : 'N/A',
        ),
        
        'financial' => array(
            'Price' => isset($data['price']) ? '€' . number_format($data['price'], 2) : 'N/A',
            'Price Paid' => isset($data['price-paid']) ? ucfirst($data['price-paid']) : 'N/A',
            'Prepayment' => isset($data['prepayment']) ? '€' . number_format($data['prepayment'], 2) : 'N/A',
            'Prepayment Paid' => isset($data['prepayment-paid']) ? ucfirst($data['prepayment-paid']) : 'N/A',
            'Deposit' => isset($data['deposit']) ? '€' . number_format($data['deposit'], 2) : 'N/A',
            'Deposit Paid' => isset($data['deposit-paid']) ? ucfirst($data['deposit-paid']) : 'N/A',
        ),
        
        'additional' => array(
            'Notice' => isset($data['notice']) && !empty($data['notice']) ? $data['notice'] : 'No special notes',
            'Blocked Booking' => isset($data['is-blocked-booking']) ? ($data['is-blocked-booking'] ? 'Yes' : 'No') : 'N/A',
            'Guest ID' => isset($data['guestId']) ? $data['guestId'] : 'N/A',
            'Guest App URL' => isset($data['guest-app-url']) ? $data['guest-app-url'] : 'N/A',
        )
    );
    
    return $formatted;
}
}
