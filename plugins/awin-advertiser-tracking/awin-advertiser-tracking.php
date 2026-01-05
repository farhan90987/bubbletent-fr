<?php

/**
 * Plugin Name: Awin - Advertiser Tracking
 * Plugin URI: https://wordpress.org/plugins/awin-advertiser-tracking
 * Description: The Awin Advertiser Tracking plugin allows for seamless integration of our core Advertiser Tracking Suite within WooCommerce.
 * Version: 2.0.2
 * Author: awinglobal
 * Author URI: https://profiles.wordpress.org/awinglobal/
 * Text Domain:  awin-advertiser-tracking
 * Domain Path: /languages
 *
 * Copyright: © 2019 AWIN LTD.
 * License: ModifiedBSD
 */

define('AWIN_ADVERTISER_TRACKING_VERSION', '2.0.2');
define('AWIN_SLUG', 'awin_advertiser_tracking');
define('AWIN_TEXT_DOMAIN', 'awin-advertiser-tracking');
define('AWIN_SETTINGS_KEY', 'awin_settings');
define('AWIN_SETTINGS_ADVERTISER_ID_KEY', 'awin_advertiser_id');
define('AWIN_SOURCE_COOKIE_NAME', 'source');
define('AWIN_AWC_COOKIE_NAME', 'adv_awc');

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) || (is_multisite() && isset(get_site_option('active_sitewide_plugins')['woocommerce/woocommerce.php']))) {

    add_action('admin_menu', 'awin_add_admin_menu');
    add_action('admin_init', 'awin_settings_init');
    add_action('wp_enqueue_scripts', 'awin_enqueue_journey_tag_script');
    add_action('init', 'awin_process_url_params');
    add_action('woocommerce_thankyou', 'awin_thank_you', 10);
    add_action("plugins_loaded", "awin_load_textdomain");

    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'awin_add_plugin_page_settings_link');

    add_action('init', 'schedule_product_feed_generation');
    add_filter('cron_schedules', 'custom_cron_intervals');
    add_action('generate_product_feed_event', 'generate_product_feed');

    add_action('init', 'schedule_approve_orders_cron');
    add_action('awin_approve_orders_cron_hook', 'approve_orders_daily');

    add_action('woocommerce_order_status_cancelled', 'trigger_awin_api_on_cancelled_or_refunded', 10, 1);
    add_action('woocommerce_order_status_refunded', 'trigger_awin_api_on_cancelled_or_refunded', 10, 1);
    add_action('woocommerce_order_partially_refunded', 'trigger_awin_api_on_partial_refund', 10, 2);

    add_action('admin_init', 'awin_handle_product_feed_generation');

    register_deactivation_hook(__FILE__, 'awin_deactivate_plugin');

    function awin_add_plugin_page_settings_link($links)
    {
        $links[] = '<a href="' . admin_url('options-general.php?page=' . AWIN_SLUG) . '">' . __('Settings') . '</a>';
        return $links;
    }

    function awin_load_textdomain()
    {
        load_plugin_textdomain(
            AWIN_TEXT_DOMAIN,
            false,
            basename(dirname(__FILE__)) . '/lang/'
        );
    }

    function awin_add_admin_menu()
    {
        add_options_page(__('Awin Advertiser Tracking', AWIN_TEXT_DOMAIN), __('Awin Advertiser Tracking', AWIN_TEXT_DOMAIN), 'manage_options', AWIN_SLUG, 'awin_render_options_page');
    }

    function awin_settings_init()
    {
        register_setting('awin-plugin-page', AWIN_SETTINGS_KEY);

        add_settings_section(
            'awin_plugin-page_section',
            __('Tracking settings', AWIN_TEXT_DOMAIN),
            'awin_settings_section_callback',
            'awin-plugin-page'
        );

        add_settings_field(
            'awin_advertiser_id',
            __('Advertiser ID', AWIN_TEXT_DOMAIN),
            'awin_advertiser_id_render',
            'awin-plugin-page',
            'awin_plugin-page_section'
        );

        // Bearer Token field
        add_settings_field(
            'awin_bearer_token',
            __('Authorization Bearer Token', AWIN_TEXT_DOMAIN),
            'awin_bearer_token_render',
            'awin-plugin-page',
            'awin_plugin-page_section'
        );

        // Add input field for Configurable days after which an order is approved
        add_settings_field(
            'awin_approval_days',
            __('Approval Delay (Days)', AWIN_TEXT_DOMAIN),
            'awin_approval_days_render',
            'awin-plugin-page',
            'awin_plugin-page_section'
        );
    }

    function awin_advertiser_id_render()
    {
        $options = get_option(AWIN_SETTINGS_KEY);
    ?>
<?php wp_nonce_field('awin-plugin-page-action', 'awin_settings[' . AWIN_SETTINGS_ADVERTISER_ID_KEY . '-check]'); ?>
<input type='number' name='awin_settings[<?php echo AWIN_SETTINGS_ADVERTISER_ID_KEY ?>]' value='<?php echo sanitize_text_field($options[AWIN_SETTINGS_ADVERTISER_ID_KEY]); ?>' required oninput='this.setCustomValidity("")' oninvalid="this.setCustomValidity('<?php echo __('You can\\\'t go to the next step until you enter your advertiser ID.', AWIN_TEXT_DOMAIN) ?>')">
<?php
    }

        function awin_bearer_token_render()
        {
            // Get the options array and retrieve the saved token
            $options      = get_option(AWIN_SETTINGS_KEY);
            $bearer_token = isset($options['awin_bearer_token']) ? sanitize_text_field($options['awin_bearer_token']) : '';
        ?>
<input type='text' name='awin_settings[awin_bearer_token]'
    value='<?php echo esc_attr($bearer_token); ?>'
    class="regular-text"
    placeholder='Enter your AWIN API Authorization Bearer token'>
<p class="description">Enter your AWIN API Authorization Bearer token. This token is required for API requests.</p>
<?php
    }

        function awin_approval_days_render()
        {
            $options       = get_option(AWIN_SETTINGS_KEY);
            $approval_days = isset($options['awin_approval_days']) ? sanitize_text_field($options['awin_approval_days']) : '30'; // Default to 30 days
        ?>
<input type="number" name="awin_settings[awin_approval_days]"
        value="<?php echo esc_attr($approval_days); ?>"
        min="1" placeholder="30">
<p class="description">Enter the number of days after which orders in completed status will be approved. Default is 30 days.</p>
<?php
    }

        function awin_settings_section_callback()
        {
            echo __('<p>By entering your advertiser ID and click <b>Save Changes</b>, your WooCommerce store will be automatically set up for Awin Tracking.</p><p>If you don\'t have an advertiser ID please sign up to the Awin network first to receive your advertiser ID, via <a href="https://www.awin.com" target="_blank">www.awin.com</a>.</p>', AWIN_TEXT_DOMAIN);
        }

        function awin_render_options_page()
        {
            if (current_user_can('manage_options')) {
                $isPost = ! empty($_POST);
                if ($isPost && (! isset($_POST['awin_settings[' . AWIN_SETTINGS_ADVERTISER_ID_KEY . '-check]']) || ! wp_verify_nonce($_POST['awin_settings[' . AWIN_SETTINGS_ADVERTISER_ID_KEY . '-check]'], 'awin-plugin-page-action'))
                    && ! isset($_POST['awin_generate_product_feed'])
                ) {
                    print 'Sorry, your nonce did not verify.';
                    exit;
                } else {
                ?>
        <div class="wrap">
            <h1><?php echo __('Awin Advertiser Tracking', AWIN_TEXT_DOMAIN) ?></h1>

            <form action='options.php' method='post'>
                <?php
                    settings_fields('awin-plugin-page');
                                    do_settings_sections('awin-plugin-page');
                                    submit_button(__('Save Changes', AWIN_TEXT_DOMAIN));
                                ?>

            </form>

            <!-- Separate form for generating product feed -->
            <h2><?php echo __('Generate Product Feed', AWIN_TEXT_DOMAIN) ?></h2>
            <form method="post" action="">
                <?php wp_nonce_field('awin_feed_nonce_action', 'awin_feed_nonce_field'); ?>
                <input type="hidden" name="action" value="awin_generate_product_feed">
                <?php submit_button(__('Generate Product Feed Now', AWIN_TEXT_DOMAIN), 'primary', 'awin_generate_product_feed'); ?>
            </form>
                <?php
                    $upload_dir = wp_upload_dir();
                                    $file_path  = $upload_dir['baseurl'] . '/product-feed.csv';
                                ?>
            <p>
                <p>After generating your product feed click the link below to download it:</p>
                <a href="<?php echo $file_path ?>"><?php echo $file_path ?></a>
            </p>
        </div>
<?php
    }
            }
        }

        function awin_handle_product_feed_generation()
        {
            if (
                isset($_POST['awin_generate_product_feed']) &&
                isset($_POST['awin_feed_nonce_field']) &&
                wp_verify_nonce($_POST['awin_feed_nonce_field'], 'awin_feed_nonce_action')
            ) {
                generate_product_feed();
                add_action('admin_notices', 'awin_feed_generation_notice');
            } elseif (isset($_POST['awin_generate_product_feed'])) {
                // Optional: Show error if CSRF check fails
                add_action('admin_notices', function () {
                    echo '<div class="notice notice-error is-dismissible"><p>' . __('Security check failed. Feed was not generated.', AWIN_TEXT_DOMAIN) . '</p></div>';
                });
            }
        }

        function awin_feed_generation_notice()
        {
        ?>
<div class="notice notice-success is-dismissible">
    <p><?php _e('Product feed has been generated successfully.', AWIN_TEXT_DOMAIN); ?></p>
</div>
<?php
    }

        function awin_enqueue_journey_tag_script()
        {
            if (! is_admin() && ! is_checkout()) {
                $advertiserId = awin_get_advertiser_id_from_settings();
                if ($advertiserId > 0) {
                    wp_enqueue_script('awin-journey-tag', 'https://www.dwin1.com/' . $advertiserId . '.js', [], AWIN_ADVERTISER_TRACKING_VERSION, true);
                }
            }
        }

        function awin_process_url_params()
        {
            $urlparts = parse_url(home_url());
            $domain   = $urlparts['host'];

            if (isset($_GET["awc"])) {
                // store awc from url if possible
                $sanitized_awc = sanitize_key($_GET["awc"]);
                if (strlen($sanitized_awc) > 0) {
                    setcookie(AWIN_AWC_COOKIE_NAME, $sanitized_awc, time() + (86400 * 30), COOKIEPATH, $domain, is_ssl(), true);
                }
            }

            if (isset($_GET[AWIN_SOURCE_COOKIE_NAME])) {
                // store source from url if possible
                $sanitized_cookie_name = sanitize_key($_GET[AWIN_SOURCE_COOKIE_NAME]);
                if (strlen($sanitized_cookie_name) > 0) {
                    setcookie(AWIN_SOURCE_COOKIE_NAME, $sanitized_cookie_name, time() + (86400 * 30), COOKIEPATH, $domain, is_ssl(), true);
                }
            }
        }

        function awin_get_advertiser_id_from_settings()
        {
            $options = get_option(AWIN_SETTINGS_KEY);
            return $options[AWIN_SETTINGS_ADVERTISER_ID_KEY];
        }

        function awin_thank_you($order_id)
        {
            $advertiserId = awin_get_advertiser_id_from_settings();

            if (strlen($order_id) > 0 && strlen($advertiserId) > 0) {
                // the order
                $order = wc_get_order($order_id);

                // check if the order has already been sent to Awin
                $sentToAwin = get_post_meta($order_id, '_awin_conversion', true);

                if ($sentToAwin) {
                    return;
                }

                // Get all orders of the customer
                $customer_id = $order->get_customer_id();

                // Determine customer acquisition status
                $customer_acquisition = get_customer_acquisition_status($customer_id);
                $voucher              = '';
                $coupons              = $order->get_coupon_codes();

                if (count($coupons) > 0) {
                    $voucher = $coupons[0];
                }

                // Getting an instance of the order object
                $order_number    = $order->get_order_number();
                $currency        = $order->get_currency();
                $NUMBER_DECIMALS = 2;
                $totalPrice      = number_format((float) $order->get_total() - $order->get_total_tax() - $order->get_total_shipping(), $NUMBER_DECIMALS, '.', '');

                $awc     = isset($_COOKIE[AWIN_AWC_COOKIE_NAME]) ? $_COOKIE[AWIN_AWC_COOKIE_NAME] : "";
                $channel = isset($_COOKIE[AWIN_SOURCE_COOKIE_NAME]) ? $_COOKIE[AWIN_SOURCE_COOKIE_NAME] : "aw";

                $imgUrl = 'https://www.awin1.com/sread.img?tt=ns&tv=2&merchant=' . $advertiserId . '&amount=' . $totalPrice . '&ch=' . $channel . '&cr=' . $currency . '&ref=' . $order_number . '&parts=DEFAULT:' . $totalPrice . '&customeracquisition=' . $customer_acquisition . '&p1=wooCommercePlugin_' . AWIN_ADVERTISER_TRACKING_VERSION;
                if (strlen($voucher) > 0) {
                    $imgUrl .= '&vc=' . $voucher;
                }

                // deepcode ignore XSS: safe
                echo '<img src="' . $imgUrl . '" loading="eager" border="0" height="0" width="0" style="display: none;">';
                echo '<form style="display: none;" name="aw_basket_form">' . "\n";
                echo '<textarea wrap="physical" id="aw_basket">' . "\n";

                foreach ($order->get_items() as $item_id => $item) {

                    $product = $item->get_product();

                    if ($product) {

                        $term_names = wp_get_post_terms($item->get_product_id(), 'product_cat', ['fields' => 'names']);

                        $categories_string = '';
                        if ($term_names != null && count($term_names) > 0) {
                            $categories_string = $term_names[count($term_names) - 1];
                        }

                        $singlePrice = number_format(((float) $item['total'] / (float) $item['quantity']), $NUMBER_DECIMALS, '.', '');

                        echo "\n" . "AW:P|{$advertiserId}|{$order->get_order_number()}|{$item['product_id']}|" . rawurlencode($item['name']) . "|{$singlePrice}|{$item['quantity']}|{$product->get_sku()}|DEFAULT|{$categories_string}";
                    }
                }
                echo "\n" . '</textarea>';
                echo "\n" . '</form>';

                $masterTag = '//<![CDATA[' . "\n";
                $masterTag .= 'var AWIN = {};' . "\n";
                $masterTag .= 'AWIN.Tracking = {};' . "\n";
                $masterTag .= 'AWIN.Tracking.Sale = {};' . "\n";
                $masterTag .= 'AWIN.Tracking.Sale.test = 0;' . "\n";
                $masterTag .= 'AWIN.Tracking.Sale.amount = "' . $totalPrice . '";' . "\n";
                $masterTag .= 'AWIN.Tracking.Sale.channel = "' . $channel . '";' . "\n";
                $masterTag .= 'AWIN.Tracking.Sale.currency = "' . $currency . '";' . "\n";
                $masterTag .= 'AWIN.Tracking.Sale.orderRef = "' . $order_number . '";' . "\n";
                $masterTag .= 'AWIN.Tracking.Sale.parts = "DEFAULT:' . $totalPrice . '";' . "\n";
                $masterTag .= 'AWIN.Tracking.Sale.voucher = "' . $voucher . '";' . "\n";
                $masterTag .= 'AWIN.Tracking.Sale.custom = ["wooCommercePlugin_' . AWIN_ADVERTISER_TRACKING_VERSION . '"];' . "\n";
                $masterTag .= 'AWIN.Tracking.Sale.customerAcquisition = "' . $customer_acquisition . '";' . "\n";
                $masterTag .= '//]]>' . "\n";

                // register and add variables
                wp_register_script('awin-mastertag-params', '');
                wp_enqueue_script('awin-mastertag-params');
                wp_add_inline_script('awin-mastertag-params', $masterTag);

                // add dwin1 script tag after variables
                wp_enqueue_script('awin-mastertag', 'https://www.dwin1.com/' . $advertiserId . '.js', ['awin-mastertag-params'], AWIN_ADVERTISER_TRACKING_VERSION, true);

                // s2s
                awin_perform_server_to_server_call($awc, $channel, $order, $advertiserId, $voucher, $customer_acquisition);

                // add sentToAwin flag to order in order to avoid duplicate calls
                update_post_meta($order_id, '_awin_conversion', true);
            }
        }

        function awin_deactivate_plugin()
        {
            delete_option('awin_settings');
        }

        function awin_perform_server_to_server_call($awc, $channel, $order, $advertiserId, $voucher, $customer_acquisition)
        {
            $totalPrice = number_format((float) $order->get_total() - $order->get_total_tax() - $order->get_total_shipping(), 2, '.', '');

            $query = [
                "tt"                  => "ss",
                "tv"                  => "2",
                "ch"                  => $channel,
                "cks"                 => $awc,
                "merchant"            => $advertiserId,
                "cr"                  => $order->get_currency(),
                "amount"              => $totalPrice,
                "parts"               => "DEFAULT:" . $totalPrice,
                "ref"                 => $order->get_order_number(),
                "customeracquisition" => $customer_acquisition,
                "p1"                  => "wooCommercePlugin_" . AWIN_ADVERTISER_TRACKING_VERSION,
            ];

            if (strlen($voucher) > 0) {
                $query["vc"] = $voucher;
            }

            wp_remote_get("https://www.awin1.com/sread.php?" . http_build_query($query));
        }

        // Function to execute daily task
        function approve_orders_daily()
        {
            $options       = get_option(AWIN_SETTINGS_KEY);
            $approval_days = isset($options['awin_approval_days']) ? (int) $options['awin_approval_days'] : 30;

            // Calculate the date threshold (current date minus the approval days)
            $threshold_date = date('Y-m-d', strtotime('-' . $approval_days . ' days'));

            $args = [
                'status'        => 'completed',
                'type'          => 'shop_order',
                'date_modified' => '>=' . $threshold_date,
                'limit'         => -1,
            ];

            $orders = wc_get_orders($args);

            foreach ($orders as $order) {
                $job_id              = get_post_meta($order->get_id(), '_awin_job_id', true);
                $awin_sent_completed = get_post_meta($order->get_id(), '_awin_sent_completed', true);

                if (! empty($awin_sent_completed)) {
                    continue; // Skip this iteration and move to the next order
                }

                $transaction_date = $order->get_date_completed()->date('Y-m-d\TH:i:s');
                $timezone         = wp_timezone_string();

                $request_body = json_encode([
                    [
                        'action'      => 'approve',
                        'transaction' => (object) [
                            'orderRef'        => $order->get_id(),
                            'transactionDate' => $transaction_date,
                            'timezone'        => $timezone,
                        ],
                    ],
                ]);

                $advertiserId = awin_get_advertiser_id_from_settings();
                $bearer_token = get_option('awin_settings')['awin_bearer_token'] ?? '';

                // Send API request
                $response = wp_remote_post("https://api.awin.com/advertisers/{$advertiserId}/transactions/batch", [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $bearer_token,
                        'Content-Type'  => 'application/json',
                    ],
                    'body'    => $request_body,
                    'timeout' => 45,
                ]);

                if (is_wp_error($response)) {
                    error_log('AWIN API: Failed to send approval request for order ' . $order->get_id());
                } else {
                    $response_body = json_decode(wp_remote_retrieve_body($response), true);

                    if (isset($response_body['jobId'])) {
                        update_post_meta($order->get_id(), '_awin_job_id', sanitize_text_field($response_body['jobId']));
                        update_post_meta($order->get_id(), '_awin_sent_completed', true);
                    }
                }
            }
        }

        // get customer acquisition status
        function get_customer_acquisition_status($customer_id)
        {
            if (! $customer_id) {
                return "NEW"; // Default to "NEW" if no customer ID is provided.
            }

            // Get the count of orders for the customer
            $order_count = count(wc_get_orders(['customer_id' => $customer_id]));

            // Return the acquisition status based on the order count
            return $order_count > 1 ? "RETURNING" : "NEW";
        }

        // set cron job interval time
        function custom_cron_intervals($schedules)
        {
            if (! isset($schedules["every_six_hours"])) {
                $schedules["every_six_hours"] = [
                    'interval' => 6 * HOUR_IN_SECONDS,
                    'display'  => __('Every Six Hours'),
                ];
            }
            return $schedules;
        }

        function schedule_product_feed_generation()
        {
            if (! wp_next_scheduled('generate_product_feed_event')) {
                wp_schedule_event(time(), 'every_six_hours', 'generate_product_feed_event');
            }
        }

        function schedule_approve_orders_cron()
        {
            if (! wp_next_scheduled('awin_approve_orders_cron_hook')) {
                wp_schedule_event(strtotime('00:00:00'), 'daily', 'awin_approve_orders_cron_hook');
            }
        }

        // generate product feed
        function generate_product_feed()
        {
            $upload_dir = wp_upload_dir();
            $file_path  = $upload_dir['basedir'] . '/product-feed.csv'; // Adjust file name and extension as needed

            // Check if the file already exists, and delete it if it does
            if (file_exists($file_path)) {
                unlink($file_path); // Delete the old file
            }

            $file = fopen($file_path, 'w');

            if ($file === false) {
                error_log('Could not open the product feed file for writing.');
                return;
            }

            // Define column headers for the feed
            $headers = [
                'deep_link',
                'basket_link', 'ean', 'isbn', 'product_GTIN', 'size_stock_amount', 'category_name', 'category_id',
                'image_url', 'product_id', 'product_name', 'price',
                'alternate_image', 'base_price', 'average_rating', 'base_price_amount', 'base_price_text',
                'brand_name', 'colour', 'commission_group', 'condition', 'currency', 'delivery_cost',
                'delivery_restrictions', 'delivery_time', 'delivery_weight', 'description', 'dimensions',
                'in_stock', 'is_for_sale', 'keywords', 'language', 'last_updated', 'large_image',
                'merchant_category', 'merchant_product_category_path', 'merchant_product_second_category',
                'merchant_product_third_category', 'model_number', 'parent_product_id',
                'product_model', 'product_price_old', 'product_short_description', 'product_type',
                'promotional_text', 'rating', 'reviews', 'rrp_price', 'saving', 'savings_percent',
                'size_stock_status', 'specifications', 'stock_quantity', 'stock_status', 'store_price',
                'upc', 'valid_from', 'valid_to', 'warranty', 'category', 'size',
                'deal_end', 'deal_start',
            ];

            fputcsv($file, $headers);

            // Query WooCommerce products
            $args = [
                'post_type'      => 'product',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            ];

            $products = get_posts($args);

            foreach ($products as $product_post) {
                $product          = wc_get_product($product_post->ID);
                $dimensions_array = $product->get_dimensions(false);
                $dimensions       = (! empty($dimensions_array['length']) || ! empty($dimensions_array['width']) || ! empty($dimensions_array['height']))
                ? wc_format_dimensions($dimensions_array)
                : '';
                $dimensions     = str_replace('&times;', '×', $dimensions);
                $category_paths = get_product_category_paths($product_post->ID);

                $attributes      = get_post_meta($product->get_id(), '_product_attributes', true);
                $color_formatted = '';
                if (isset($attributes['color'])) {
                    $colors          = explode('|', $attributes['color']['value']); // Split by the pipe
                    $color_formatted = implode(', ', array_map('trim', $colors));   // Format with commas and spaces
                }

                $size_formatted = '';
                // Check if the size attribute exists
                if (isset($attributes['size'])) {
                                                                            // Split the size values by pipe and trim them
                    $sizes = explode('|', $attributes['size']['value']); // Example: "S|M|L|XL"

                    // Create stock status for each size
                    $size_statuses = [];
                    foreach ($sizes as $size) {
                        // Assuming each size has an arbitrary stock status of '0' for this example; adjust as necessary
                        $size_statuses[] = trim($size);
                    }

                    // Format like 'S:0|M:0|L:0|XL:0'
                    $size_formatted = implode('|', $size_statuses);
                }

                $sizes_formatted = '';
                // Check if size attribute exists
                if (isset($attributes['size'])) {
                    // Split the size values using the pipe delimiter and format them with commas
                    $sizes           = explode('|', $attributes['size']['value']);
                    $sizes_formatted = implode("', '", array_map('trim', $sizes)); // Format with commas and spaces
                    $sizes_formatted = "'$sizes_formatted'";
                }

                $specifications = [];
                // Add weight and dimensions
                if ($product->get_weight()) {
                    $specifications[] = 'Weight: ' . $product->get_weight() . ' kg';
                }
                if ($dimensions) {
                    $specifications[] = 'Dimensions: ' . $dimensions;
                }

                // Add attributes
                $attributes = $product->get_attributes();
                if (! empty($attributes)) {
                    foreach ($attributes as $attribute) {
                        $specifications[] = $attribute->get_name() . ': ' . implode(', ', $attribute->get_options());
                    }
                }

                // Combine specifications into a single string with comma separation
                $specifications_string = ! empty($specifications) ? implode(', ', $specifications) : '';
                $cart_url              = wc_get_cart_url();
                // echo wc_get_cart_url();
                $deep_link = get_permalink($product->get_id());

                $row = [
                    'deep_link'                        => $deep_link,
                    'basket_link'                      => $cart_url,
                    'ean'                              => $product->get_global_unique_id() ?? '',
                    'isbn'                             => $product->get_global_unique_id() ?? '',
                    'product_GTIN'                     => $product->get_global_unique_id() ?? '',
                    'size_stock_amount'                => $size_formatted,
                    'category_name'                    => get_the_terms($product_post->ID, 'product_cat')[0]->name ?? '',
                    'category_id'                      => get_the_terms($product_post->ID, 'product_cat')[0]->term_id ?? '',
                    'image_url'                        => wp_get_attachment_url($product->get_image_id()),
                    'product_id'                       => $product->get_id(),
                    'product_name'                     => $product->get_name(),
                    'price'                            => $product->get_price(),
                    'alternate_image'                  => wp_get_attachment_url($product->get_gallery_image_ids()[0] ?? ''),
                    'base_price'                       => $product->get_regular_price(),
                    'average_rating'                   => $product->get_average_rating(),
                    'base_price_amount'                => $product->get_regular_price(),
                    'base_price_text'                  => $product->get_regular_price(),
                    'brand_name'                       => '', // Example custom field
                    'colour'                           => $color_formatted,
                    'commission_group'                 => get_the_terms($product_post->ID, 'product_cat')[0]->name ?? '',
                    'condition'                        => get_post_meta($product->get_id(), '_wc_pinterest_condition', true),
                    'currency'                         => get_woocommerce_currency(),
                    'delivery_cost'                    => get_post_meta($product->get_id(), '_delivery_cost', true),
                    'delivery_restrictions'            => 'None',
                    'delivery_time'                    => '5-7 days',
                    'delivery_weight'                  => $product->get_weight(),
                    'description'                      => $product->get_description(),
                    'dimensions'                       => $dimensions,
                    'in_stock'                         => $product->is_in_stock() ? '1' : '0',
                    'is_for_sale'                      => $product->is_on_sale() ? '1' : '0',
                    'keywords'                         => implode(',', wp_list_pluck(get_the_terms($product->get_id(), 'product_tag'), 'name')),
                    'language'                         => get_locale(),
                    'last_updated'                     => $product_post->post_modified,
                    'large_image'                      => wp_get_attachment_url($product->get_image_id()),
                    'merchant_category'                => get_the_terms($product_post->ID, 'product_cat')[0]->name ?? '',
                    'merchant_product_category_path'   => $category_paths[0] ?? '',
                    'merchant_product_second_category' => $category_paths[1] ?? '',
                    'merchant_product_third_category'  => $category_paths[2] ?? '',
                    'model_number'                     => get_post_meta($product->get_id(), '_model_number', true),
                    'parent_product_id'                => $product->get_parent_id() ?? '',
                    'product_model'                    => $product->get_sku(),
                    'product_price_old'                => $product->get_regular_price(),
                    'product_short_description'        => $product->get_short_description(),
                    'product_type'                     => $product->get_type(),
                    'promotional_text'                 => get_post_meta($product->get_id(), '_promotional_text', true),
                    'rating'                           => $product->get_average_rating(),
                    'reviews'                          => $product->get_review_count(),
                    'rrp_price'                        => $product->get_regular_price(),
                    'saving'                           => $product->get_sale_price() ? $product->get_regular_price() - $product->get_sale_price() : '0',
                    'savings_percent'                  => $product->get_sale_price() ? round((($product->get_regular_price() - $product->get_sale_price()) / $product->get_regular_price()) * 100) : '0',
                    'size_stock_status'                => $product->get_stock_status(),
                    'specifications'                   => $specifications_string,
                    'stock_quantity'                   => $product->get_stock_quantity(),
                    'stock_status'                     => $product->get_stock_status(),
                    'store_price'                      => $product->get_price(),
                    'upc'                              => $product->get_global_unique_id() ?? '',
                    'valid_from'                       => $product->get_date_on_sale_from() ?? '',
                    'valid_to'                         => $product->get_date_on_sale_to() ?? '',
                    'warranty'                         => get_post_meta($product->get_id(), '_warranty', true),
                    'category'                         => get_the_terms($product_post->ID, 'product_cat')[0]->name ?? '',
                    'size'                             => $sizes_formatted,
                    'deal_end'                         => $product->get_date_on_sale_to() ?? '',
                    'deal_start'                       => $product->get_date_on_sale_from() ?? '',
                ];

                fputcsv($file, $row);
            }

            fclose($file);
        }

        // get product category path
        function get_product_category_paths($product_id)
        {
            // Get the product categories
            $terms = get_the_terms($product_id, 'product_cat');

            // Check if terms were retrieved and handle any WP_Error
            if (is_wp_error($terms)) {
                return []; // Return empty array if there was an error
            }

            $category_paths = [];

            if ($terms && ! empty($terms)) {
                foreach ($terms as $term) {
                    // Check if we have already added three paths
                    if (count($category_paths) >= 3) {
                        break;
                    }

                    // Check if the term is a valid object
                    if (isset($term->term_id)) {
                        $parent_id = $term->parent;
                        $term_path = [$term->name];

                        while ($parent_id) {
                            $parent_term = get_term($parent_id, 'product_cat');

                            // Check if parent term retrieval was successful
                            if (! is_wp_error($parent_term) && $parent_term) {
                                array_unshift($term_path, $parent_term->name);
                                $parent_id = $parent_term->parent;
                            } else {
                                break; // Exit the loop if there's an error
                            }
                        }

                        // Combine the path for this category and add it to the array
                        $category_paths[] = implode(' > ', $term_path);
                    }
                }
            }

            return $category_paths; // Return array of up to 3 individual paths
        }

        function trigger_awin_api_on_cancelled_or_refunded($order_id)
        {
            $order = wc_get_order($order_id);

            $transaction_date = $order->get_date_created()->date('Y-m-d\TH:i:s');

            $timezone = wp_timezone_string();

            $decline_reason = 'Nothing Defined';

            // API request payload
            $request_body = json_encode([
                [
                    'action'      => 'decline',
                    'transaction' => (object) [
                        'orderRef'        => $order_id,
                        'transactionDate' => $transaction_date,
                        'timezone'        => $timezone,
                        'declineReason'   => $decline_reason,
                    ],
                ],
            ]);

            $advertiserId = awin_get_advertiser_id_from_settings();
            $bearer_token = get_option('awin_settings')['awin_bearer_token'] ?? '';

            // Send API request
            $response = wp_remote_post("https://api.awin.com/advertisers/{$advertiserId}/transactions/batch", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $bearer_token,
                    'Content-Type'  => 'application/json',
                ],
                'body'    => $request_body,
                'timeout' => 45, // Timeout option for request
            ]);

            // Handle API response
            if (is_wp_error($response)) {
                error_log('AWIN API: Failed to send request for order ' . $order_id);
            } else {
                $response_body = json_decode(wp_remote_retrieve_body($response), true);

                if (isset($response_body['jobId'])) {
                    update_post_meta($order_id, '_awin_job_id', sanitize_text_field($response_body['jobId']));
                } else {
                    error_log('AWIN API: Request successful but no Job ID returned for order ' . $order_id);
                }
            }
        }

        function trigger_awin_api_on_partial_refund($order_id, $refund_id)
        {
            // Get the order and refund objects
            $order              = wc_get_order($order_id);
            $refund             = wc_get_order($refund_id);
            $transaction_date   = $order->get_date_created()->date('Y-m-d\TH:i:s');
            $refund_reason_meta = $refund ? $refund->get_reason() ?: 'No reason defined': 'No reason defined';

            // Get the advertiser ID and Bearer token from settings
            $advertiserId = awin_get_advertiser_id_from_settings();
            $bearer_token = get_option('awin_settings')['awin_bearer_token'] ?? '';
            $timezone     = wp_timezone_string();

            if (empty($advertiserId) || empty($bearer_token)) {
                error_log('Advertiser ID or Bearer token is missing.');
                return;
            }

            $sale_amount  = $order->get_total() - $order->get_total_refunded();
            $request_body = json_encode([
                [
                    'action'      => 'amend',
                    'approve'     => false,
                    'transaction' => (object) [
                        'orderRef'         => $order->get_order_number(), // WooCommerce order number
                        'transactionDate'  => $transaction_date,          // Current timestamp
                        'timezone'         => $timezone,                  // Adjust timezone as needed
                        'amendReason'      => $refund_reason_meta,
                        'currency'         => $order->get_currency(),
                        'saleAmount'       => $sale_amount,
                        'transactionParts' => [
                            (object) [
                                'amount'              => $sale_amount, // Refund amount
                                'commissionGroupCode' => 'DEFAULT',
                            ],
                        ],
                    ],
                ],
            ]);

            $response = wp_remote_post("https://api.awin.com/advertisers/{$advertiserId}/transactions/batch", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $bearer_token,
                    'Content-Type'  => 'application/json',
                ],
                'body'    => $request_body,
                'timeout' => 45, // Timeout option for request
            ]);

            // Handle the response from the API (logging, error checking, etc.)
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
            } else {
                $response_body = json_decode(wp_remote_retrieve_body($response), true);

                if (isset($response_body['jobId'])) {
                    update_post_meta($order->get_id(), '_awin_job_id', sanitize_text_field($response_body['jobId']));
                }
            }
        }

}
