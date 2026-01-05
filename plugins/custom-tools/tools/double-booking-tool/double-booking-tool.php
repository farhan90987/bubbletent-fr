<?php
if (!defined('ABSPATH')) {
    exit;
}

class Double_Booking_Tool {
    public function init() {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
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
        // Load the template
        include CUSTOM_TOOLS_PATH . 'tools/double-booking-tool/templates/admin-page.php';
    }
    
    public function enqueue_scripts($hook) {
        if ($hook === 'custom-tools_page_double-booking-tool') {
            wp_enqueue_style('double-booking-tool', CUSTOM_TOOLS_URL . 'tools/double-booking-tool/assets/css/style.css', array(), CUSTOM_TOOLS_VERSION);
            wp_enqueue_script('xlsx-js', 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js', array(), '0.18.5', true);
            wp_enqueue_script('double-booking-tool', CUSTOM_TOOLS_URL . 'tools/double-booking-tool/assets/js/script.js', array('jquery', 'xlsx-js'), CUSTOM_TOOLS_VERSION, true);
        }
    }
    
public function fetch_orders($start_date, $end_date) {
    global $wpdb;
    
    if (empty($start_date) || empty($end_date)) {
        return array();
    }
    
    $start_dt = date('Y-m-d H:i:s', strtotime($start_date));
    $end_dt   = date('Y-m-d H:i:s', strtotime($end_date . ' 23:59:59'));
    
    // Get all orders in the date range
    $statuses = array('wc-processing', 'wc-on-hold', 'wc-completed', 'wc-pending', 'wc-cancelled', 'wc-refunded', 'wc-failed');
    
    $query = $wpdb->prepare("
        SELECT ID
        FROM {$wpdb->prefix}posts
        WHERE post_type = 'shop_order'
        AND post_status IN ('" . implode("','", $statuses) . "')
        AND post_date BETWEEN %s AND %s
        ORDER BY post_date DESC
    ", $start_dt, $end_dt);

    $order_ids = $wpdb->get_col($query);
    
    if (empty($order_ids)) {
        return array();
    }
    
    $orders = array();
    $checkin_dates = array(); // Track check-in dates to find duplicates
    
    foreach ($order_ids as $order_id) {
        $order = wc_get_order($order_id);
        if (!$order) continue;
        
        $checkin_date  = trim($order->get_meta('smoobu_calendar_start'));
        $checkout_date = trim($order->get_meta('smoobu_calendar_end'));
        
        // Normalize check-in date for comparison
        $normalized_checkin = $this->normalize_date($checkin_date);
        $checkin_formatted = $normalized_checkin ? date('Y-m-d', $normalized_checkin) : '';
        
        $is_double = false;
        if ($checkin_formatted) {
            // Check if this check-in date already exists
            if (isset($checkin_dates[$checkin_formatted])) {
                $is_double = true;
                // Also mark the previous order with this check-in date as double
                foreach ($orders as &$existing_order) {
                    $existing_checkin_normalized = $this->normalize_date($existing_order['checkin']);
                    $existing_checkin_formatted = $existing_checkin_normalized ? date('Y-m-d', $existing_checkin_normalized) : '';
                    if ($existing_checkin_formatted === $checkin_formatted) {
                        $existing_order['is_double'] = true;
                    }
                }
            }
            $checkin_dates[$checkin_formatted] = true;
        }
        
        $first_name = $order->get_billing_first_name();
        $last_name = $order->get_billing_last_name();
        $customer_name = $first_name . ' ' . $last_name;
        $status = str_replace('wc-', '', $order->get_status());
        $order_date = $order->get_date_created()->date('Y-m-d H:i:s');
        $email = $order->get_billing_email();
        
        // Get Smoobu booking ID - try different possible meta keys
        $smoobu_booking_id = $order->get_meta('smoobu_booking_id');
        
        // If not found, try alternative meta keys that might be used
        if (empty($smoobu_booking_id)) {
            $smoobu_booking_id = $order->get_meta('_smoobu_booking_id');
        }
        if (empty($smoobu_booking_id)) {
            $smoobu_booking_id = $order->get_meta('smoobu_reservation_id');
        }
        
        $smoobu_status = $this->get_smoobu_booking_status($smoobu_booking_id);
        
        $orders[] = array(
            'order_id' => $order_id,
            'order_date' => $order_date,
            'customer_name' => $customer_name,
            'email' => $email,
            'status' => $status,
            'checkin' => $checkin_date,
            'checkout' => $checkout_date,
            'is_double' => $is_double,
            'smoobu_booking_id' => $smoobu_booking_id,
            'smoobu_status' => $smoobu_status
        );
    }
    
    return $orders;
}
    
private function get_smoobu_booking_status($booking_id) {
    if (empty($booking_id)) {
        return 'No Smoobu ID';
    }
    
    // Check if we have a cached status (to avoid API calls on every page load)
    $transient_key = 'smoobu_status_' . $booking_id;
    $cached_status = get_transient($transient_key);
    
    if ($cached_status !== false) {
        return $cached_status;
    }
    
    // Get API key from existing plugin's option
    $api_key = get_option('smoobu_api_key');
    
    if (empty($api_key)) {
        return 'API Key Missing';
    }
    
    // Make API call to Smoobu
    $api_url = 'https://login.smoobu.com/api/reservations/' . $booking_id;
    
    $response = wp_remote_get($api_url, array(
        'headers' => array(
            'API-KEY' => $api_key,
            'Content-Type' => 'application/json'
        ),
        'timeout' => 15
    ));
    
    if (is_wp_error($response)) {
        return 'API Error';
    }
    
    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if ($status_code === 200 && isset($data['status'])) {
        $status = $data['status'];
        // Cache the status for 1 hour
        set_transient($transient_key, $status, HOUR_IN_SECONDS);
        return $status;
    }
    
    return 'Not Found';
}
    private function normalize_date($date_str) {
        if (empty($date_str)) return false;
        
        $date_clean = str_replace(['/', '.'], '-', trim($date_str));
        $parts = explode('-', $date_clean);
        
        if (count($parts) === 3) {
            // Guess format
            if (strlen($parts[0]) === 4) {
                // Y-m-d
                return strtotime($date_clean);
            } else {
                // d-m-Y
                return strtotime($parts[2] . '-' . $parts[1] . '-' . $parts[0]);
            }
        } else {
            return strtotime($date_str); // fallback
        }
    }
}