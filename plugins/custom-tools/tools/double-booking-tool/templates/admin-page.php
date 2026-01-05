<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get all listings for dropdown
$listings = $this->get_all_listings();

// Process form submission
$orders = array();
$selected_listing = '';
$force_refresh = false;

// FIX: Check for EITHER submit button
if ((isset($_POST['check_double_bookings']) || isset($_POST['force_refresh'])) && check_admin_referer('double_booking_action', 'double_booking_nonce')) {
    $start = sanitize_text_field($_POST['start_date']);
    $end   = sanitize_text_field($_POST['end_date']);
    $selected_listing = sanitize_text_field($_POST['listing_id']);
    
    // FIX: Set force_refresh based on which button was clicked
    $force_refresh = isset($_POST['force_refresh']);
    
    $orders = $this->fetch_orders($start, $end, $selected_listing, $force_refresh);
}

// Group orders by check-in date for display
$grouped_orders = array();
foreach ($orders as $order) {
    $checkin_key = $order['checkin_formatted'] ?: 'no-date';
    $grouped_orders[$checkin_key][] = $order;
}
?>

<div class="wrap double-booking-tool">
    <h1><?php _e('Double Booking Checker', 'custom-tools'); ?></h1>
    <p><?php _e('This tool helps identify booking orders with the same check-in dates, which may indicate double booking issues.', 'custom-tools'); ?></p>
    
   <form method="post" id="double-booking-form">
    <?php wp_nonce_field('double_booking_action', 'double_booking_nonce'); ?>
    
    <?php
    // Debug information (remove in production)
    if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('manage_options')) {
        $api_key = get_option('smoobu_api_key');
        echo '<div class="notice notice-info"><p>';
        echo '<strong>Debug Info:</strong> API Key: ' . ($api_key ? 'Set (' . substr($api_key, 0, 10) . '...)' : 'Not set');
        if (!empty($orders)) {
            $smoobu_ids = array_filter(array_column($orders, 'smoobu_booking_id'));
            echo ' | Found ' . count($smoobu_ids) . ' orders with Smoobu IDs';
            if (!empty($smoobu_ids)) {
                echo ' | Sample IDs: ' . implode(', ', array_slice($smoobu_ids, 0, 3));
            }
        }
        echo ' | Force Refresh: ' . ($force_refresh ? 'Yes' : 'No');
        echo '</p></div>';
    }
    ?>
    
    <div class="date-filters">
        <label for="listing_select"><?php _e('Select Location:', 'custom-tools'); ?></label>
        <select name="listing_id" id="listing_select">
            <option value=""><?php _e('All Locations', 'custom-tools'); ?></option>
            <?php foreach ($listings as $listing_id => $listing_title) : ?>
                <option value="<?php echo esc_attr($listing_id); ?>" <?php selected($selected_listing, $listing_id); ?>>
                    <?php echo esc_html($listing_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <label for="start_date"><?php _e('Start Date:', 'custom-tools'); ?></label>
        <input type="date" name="start_date" id="start_date" required value="<?php echo isset($_POST['start_date']) ? esc_attr($_POST['start_date']) : ''; ?>">
        
        <label for="end_date"><?php _e('End Date:', 'custom-tools'); ?></label>
        <input type="date" name="end_date" id="end_date" required value="<?php echo isset($_POST['end_date']) ? esc_attr($_POST['end_date']) : ''; ?>">
        
        <input type="submit" name="check_double_bookings" class="button button-primary" value="<?php _e('Fetch Orders', 'custom-tools'); ?>">
        <input type="submit" name="force_refresh" class="button button-secondary" value="<?php _e('Force Refresh Smoobu', 'custom-tools'); ?>">
    </div>

    <!-- Rest of your code remains the same -->

    <!-- Rest of your code remains the same -->

        
        <?php if (!empty($orders)) : ?>
            <div class="booking-summary">
                <p><strong><?php echo count($orders); ?></strong> booking orders found.</p>
                <?php
                $double_booking_count = 0;
                foreach ($grouped_orders as $checkin_date => $group_orders) {
                    if (count($group_orders) > 1) {
                        $double_booking_count += count($group_orders);
                    }
                }
                if ($double_booking_count > 0) {
                    echo '<p class="double-booking-alert"><strong>' . $double_booking_count . '</strong> orders have potential double bookings.</p>';
                }
                ?>
            </div>
            
           <div class="table-filters">
    <h3><?php _e('Filters', 'custom-tools'); ?></h3>
    <div class="filter-row">
        <label for="status_filter"><?php _e('Order Status:', 'custom-tools'); ?></label>
        <select id="status_filter">
            <option value=""><?php _e('All Statuses', 'custom-tools'); ?></option>
            <option value="pending"><?php _e('Pending', 'custom-tools'); ?></option>
            <option value="processing"><?php _e('Processing', 'custom-tools'); ?></option>
            <option value="on-hold"><?php _e('On Hold', 'custom-tools'); ?></option>
            <option value="completed"><?php _e('Completed', 'custom-tools'); ?></option>
            <option value="cancelled"><?php _e('Cancelled', 'custom-tools'); ?></option>
            <option value="refunded"><?php _e('Refunded', 'custom-tools'); ?></option>
            <option value="failed"><?php _e('Failed', 'custom-tools'); ?></option>
            <option value="checkout-draft"><?php _e('Checkout Draft', 'custom-tools'); ?></option>
        </select>
        
        <label for="search_filter"><?php _e('Search:', 'custom-tools'); ?></label>
        <input type="text" id="search_filter" placeholder="<?php _e('Order ID, Name, Email', 'custom-tools'); ?>">
        
        <label for="double_booking_filter">
            <input type="checkbox" id="double_booking_filter"> <?php _e('Show Only Double Bookings', 'custom-tools'); ?>
        </label>
        
        <button type="button" id="export_btn" class="button button-secondary"><?php _e('Export to XLSX', 'custom-tools'); ?></button>
    </div>
</div>
            
           <table id="orders-table" class="widefat striped">
    <thead>
        <tr>
            <th><?php _e('Order ID', 'custom-tools'); ?></th>
            <th><?php _e('Order Date', 'custom-tools'); ?></th>
            <th><?php _e('Customer Name', 'custom-tools'); ?></th>
            <th style="display: none;"><?php _e('Phone', 'custom-tools'); ?></th> <!-- Hidden for display -->
            <th style="display: none;"><?php _e('Email', 'custom-tools'); ?></th> <!-- Hidden for display -->
            <th><?php _e('Order Status', 'custom-tools'); ?></th>
            <th><?php _e('Check-in Date', 'custom-tools'); ?></th>
            <th><?php _e('Check-out Date', 'custom-tools'); ?></th>
            <th><?php _e('Smoobu Status', 'custom-tools'); ?></th>
            <th><?php _e('Double Booking', 'custom-tools'); ?></th>
            <th style="display: none;"><?php _e('Order Link', 'custom-tools'); ?></th> <!-- Hidden for display -->
            <th><?php _e('Actions', 'custom-tools'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($grouped_orders as $checkin_date => $group_orders) : 
              $is_double_group = count($group_orders) > 1;
              $group_class = $is_double_group ? 'double-booking-group' : '';
        ?>
            <?php foreach ($group_orders as $index => $order) : 
                  $row_class = $is_double_group ? 'double-booking-row group-' . $order['double_group_id'] : '';
                  $first_in_group = $index === 0;
                  $order_link = get_edit_post_link($order['order_id']);
            ?>
                <tr class="<?php echo $row_class; ?>" 
                    data-status="<?php echo esc_attr($order['status']); ?>" 
                    data-double="<?php echo $order['is_double'] ? '1' : '0'; ?>"
                    data-group="<?php echo $order['double_group_id']; ?>"
                    data-checkin="<?php echo esc_attr($order['checkin_formatted']); ?>"
                    data-smoobu-booking-id="<?php echo esc_attr($order['smoobu_booking_id']); ?>">
                    <td><a href="<?php echo $order_link; ?>" target="_blank">#<?php echo $order['order_id']; ?></a></td>
                    <td><?php echo $order['order_date']; ?></td>
                    <td><?php echo esc_html($order['customer_name']); ?></td>
                    <td style="display: none;"><?php echo esc_html($order['phone']); ?></td> <!-- Hidden phone -->
                    <td style="display: none;"><?php echo esc_html($order['email']); ?></td> <!-- Hidden email -->
                    <td><span class="order-status status-<?php echo esc_attr($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span></td>
                    <td>
                        <?php if ($first_in_group && $is_double_group) : ?>
                            <strong class="double-booking-date"><?php echo esc_html($order['checkin']); ?></strong>
                        <?php else : ?>
                            <?php echo esc_html($order['checkin']); ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html($order['checkout']); ?></td>
                    <td>
                        <span class="smoobu-status <?php echo esc_attr($this->get_smoobu_status_class($order['smoobu_status'])); ?>" 
                            style="cursor: pointer; text-decoration: underline;" 
                            title="Click for detailed Smoobu information">
                            <?php echo esc_html($order['smoobu_status']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($is_double_group) : ?>
                            <span class="double-booking-badge">Yes (<?php echo count($group_orders); ?>)</span>
                        <?php else : ?>
                            No
                        <?php endif; ?>
                    </td>
                    <td style="display: none;"><?php echo $order_link; ?></td> <!-- Hidden order link -->
                    <td>
                        <a href="<?php echo $order_link; ?>" class="button" target="_blank"><?php _e('View Order', 'custom-tools'); ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </tbody>
</table>
        <?php elseif (isset($_POST['check_double_bookings'])) : ?>
            <div class="notice notice-warning">
                <p><?php _e('No booking orders found in the selected date range.', 'custom-tools'); ?></p>
            </div>
        <?php endif; ?>
    </form>
</div>