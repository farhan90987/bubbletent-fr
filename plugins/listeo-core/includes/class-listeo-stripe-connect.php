<?php

if (! defined('ABSPATH'))
    exit;

require __DIR__ . '/../vendor/autoload.php';


use Stripe\OAuth;

class ListeoStripeConnect
{
    /**
     * Is test mode active?
     *
     * @var bool
     */
    public $testmode;

    /**
     * The secret to use when verifying webhooks.
     *
     * @var string
     */
    protected $secret;
    protected $client_id;
    protected $webhook_secret;
  

    function __construct()
    {
        
        $stripe_connect_activation = get_option('listeo_stripe_connect_activation');



        if ($stripe_connect_activation && function_exists('is_plugin_active')) {
            $plugin_dependency_active = is_plugin_active('woocommerce-gateway-stripe/woocommerce-gateway-stripe.php');
            if (!$plugin_dependency_active) {

                add_action('admin_notices', array($this, 'missing_stripe_notice'));

            } else {
                
                $stripe_settings      = get_option('woocommerce_stripe_settings', []);
                $this->testmode       = (!empty($stripe_settings['testmode']) && 'yes' === $stripe_settings['testmode']) ? true : false;
                $secret_key           = ($this->testmode ? 'test_' : '') . 'secret_key';
                $this->secret         = !empty($stripe_settings[$secret_key]) ? $stripe_settings[$secret_key] : false;
            
                $webhook_secret_key           = ($this->testmode ? 'test_' : '') . 'webhook_secret';
                $this->webhook_secret         = !empty($stripe_settings[$webhook_secret_key]) ? $stripe_settings[$webhook_secret_key] : false;
                
                if($this->testmode) {
                    $this->client_id = get_option('listeo_stripe_connect_test_client_id');
                } else {
                    $this->client_id = get_option('listeo_stripe_connect_live_client_id');
                }
                
                add_action('woocommerce_api_wc_stripe', array($this, 'check_for_webhook'), 9);
                add_action('wp_ajax_listeo_disconnect_stripe', array($this, 'ajax_listeo_disconnect_stripe'));


                add_action('wp_enqueue_scripts', array($this, 'listeo_stripe_scripts'));
            }
        }
       

    }

    function listeo_stripe_scripts()
    {
        $wallet_page = get_option('listeo_wallet_page');
        global $post;
        // Single JS to track listings.
        if (isset($post) && $post->ID == $wallet_page) {
            
            wp_enqueue_script('listeo-core-stripe', LISTEO_CORE_URL . 'assets/js/listeo.stripe.js', array('jquery'), 1.0, true);
            
        }
    }

    function ajax_listeo_disconnect_stripe(){
        
        $secret = $this->secret;
        if($secret){
            $user_id = get_current_user_id();
            \Stripe\Stripe::setApiKey($secret);

            try {
                \Stripe\OAuth::deauthorize([
                    'client_id' => $this->client_id,
                    'stripe_user_id' => get_user_meta($user_id, 'stripe_user_id', true),
                ]);
              
                delete_user_meta($user_id, 'vendor_connected');
                delete_user_meta($user_id, 'access_token');
                delete_user_meta($user_id, 'refresh_token');
                delete_user_meta($user_id, 'stripe_publishable_key');
                delete_user_meta($user_id, 'stripe_user_id');
                wp_send_json_success();
            } catch (Exception $e) {
                
               
                delete_user_meta($user_id, 'vendor_connected');
                delete_user_meta($user_id, 'access_token');
                delete_user_meta($user_id, 'refresh_token');
                delete_user_meta($user_id, 'stripe_publishable_key');
                delete_user_meta($user_id, 'stripe_user_id');
                wp_send_json_error();
            }
        }
       
    }

    function missing_stripe_notice()
    {
        $class = 'notice notice-error';
        $message = __('Listeo Stripe Connect Split Payment option requires WooCommerce Stripe Payment Gateway enabled.', 'listeo_core');
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
    }

    public function check_for_webhook()
    {
        if (
            !isset($_SERVER['REQUEST_METHOD'])
            || ('POST' !== $_SERVER['REQUEST_METHOD'])
            || !isset($_GET['wc-api'])
            || ('wc_stripe' !== $_GET['wc-api'])
        ) {
            return;
        }
        $wc_stripe_webhook_handler = new \WC_Stripe_Webhook_Handler();
       
        $request_body = file_get_contents('php://input');
        $request_headers = array_change_key_case($wc_stripe_webhook_handler->get_request_headers(), CASE_UPPER);
        // New function call:

        if (!$this->webhook_for_this_site($request_body)) {
            WC_Stripe_Logger::log('Listeo Stripe Connect: Incoming webhook is not for this site: ' . print_r($request_body, true));
            status_header(200);
            exit;
        }

        // Validate it to make sure it is legit.

        if ($this->is_valid_request($request_headers, $request_body)) {
          
            $wc_stripe_webhook_handler->process_webhook($request_body);
            WC_Stripe_Logger::log('Listeo Stripe Connect: webhook is processed: ' . print_r($request_body, true));
            $this->split_payment($request_body);
            status_header(200);
            exit;
        } else {
          
            \WC_Stripe_Logger::log('Incoming webhook failed validation: ' . print_r($request_body, true));
            status_header(400);
            exit;
        }
    }


    /**
     * Verify the incoming webhook notification to make sure it is legit.
     *
     * @since 4.0.0
     * WooCommerce @version 4.0.0
     * @param string $request_headers The request headers from Stripe.
     * @param string $request_body The request body from Stripe.
     * @return bool
     *
     * @version 1.0.0
     */
    public function is_valid_request($request_headers = null, $request_body = null)
    {
       
        if (null === $request_headers || null === $request_body) {
            return false;
        }
        if (!empty($request_headers['USER-AGENT']) && !preg_match('/Stripe/', $request_headers['USER-AGENT'])) {
            return false;
        }

        if (!empty($this->webhook_secret)) {
            // Check for a valid signature.
            $signature_format = '/^t=(?P<timestamp>\d+)(?P<signatures>(,v\d+=[a-z0-9]+){1,2})$/';
            if (empty($request_headers['STRIPE-SIGNATURE']) || !preg_match($signature_format, $request_headers['STRIPE-SIGNATURE'], $matches)) {
		    return false;
            }
            // Verify the timestamp.
            $timestamp = intval($matches['timestamp']);

            if (abs($timestamp - time()) > 5 * MINUTE_IN_SECONDS) {
                return WC_Stripe_Webhook_State::VALIDATION_FAILED_TIMESTAMP_MISMATCH;
                
            }
            // Generate the expected signature.
            $signed_payload     = $timestamp . '.' . $request_body;
            $expected_signature = hash_hmac('sha256', $signed_payload, $this->webhook_secret);

            // Check if the expected signature is present.
            if (
                !preg_match('/,v\d+=' . preg_quote($expected_signature, '/') . '/', $matches['signatures'])
            ) {
                return WC_Stripe_Webhook_State::VALIDATION_FAILED_SIGNATURE_MISMATCH;
            }
        }

        return true;
    }
        

    /**
     * Check if the webhook is for this site
     */
    private function webhook_for_this_site($request_body)
    {
        // Look for source id
        $source_id = $this->get_source_id($request_body);
        // Check with Woo to see if it's a valid order
        $order = \WC_Stripe_Helper::get_order_by_source_id($source_id);
        // If yes return true
        if ($order) {
            return true;
        }
        // If no return false
        return false;
    }

    /**
     * Gets the Stripe source ID
     */
    private function get_source_id($request_body)
    {
        $event_type = $this->get_event_type($request_body);
        $source_id = NULL;
       
        if (!$event_type) {
            return NULL;
        }

        $request_body = json_decode($request_body);
        switch ($event_type) {
            case 'charge.succeeded':
                $source_id = $request_body->data->object->source->id;
                break;
            case 'payment_intent.succeeded':
                $source_id = $request_body->data->object->source;
                break;
            case 'source.chargeable':
                $source_id = $request_body->data->object->id;
                break;
        }
        return $source_id;
    }

    /**
     * Gets the Stripe event type
     */
    private function get_event_type($request_body)
    {
        $request_body = json_decode($request_body);
        $event_type = ($request_body->type ? strtolower($request_body->type) : NULL);
      
        /* Should return a value for the following event type:
                   source.chargeable
                   charge.succeeded
                   payment_intent.succeeded
               */
        return $event_type;
    }



    /**
     * Determine whether or not to initiate a transfer
     */
    private function split_payment($request_body)
    {
        $event_type = $this->get_event_type($request_body);

        if ($event_type == 'payment_intent.succeeded' ) {
            WC_Stripe_Logger::log('Listeo Stripe Connect: payment_intent.succeeded: ' . print_r($request_body, true));
            list($success, $result_message, $transfer) = $this->transfer_to_owner($request_body);
           
            if ($success) {
                WC_Stripe_Logger::log('Listeo Stripe Connect: Trasnfer to owner success: ' . print_r($transfer, true));
            } else {
                WC_Stripe_Logger::log('Listeo Stripe Connect: Payment error: ' . print_r($result_message, true));
                error_log("Listeo Split Payment error {$result_message}");
            }
        }
    }
    /**
     * Get event meta data (payment_intent.succeeded)
     */
    private function get_event_meta($request_body)
    {
        $data = NULL;
        $event = json_decode($request_body);
        
        try {

            if (isset($event->data->object->charges)) {
                $charge_created = $event->data->object->charges->data[0]->created;
                $charge_created = date('Y-m-d H:i:s', $charge_created);
            } else if ($event->data->object->created) {
                $charge_created = $event->data->object->created;
                $charge_created = date('Y-m-d H:i:s', $charge_created);
            } else {
                error_log("Stripe created not found");
                return NULL;
            }

            if (isset($event->data->object->charges)) {
                $order_amount = $event->data->object->charges->data[0]->amount;
                $order_amount = $order_amount / 100;
            } else if ($event->data->object->amount) {
                $order_amount = $event->data->object->amount;
                $order_amount = $order_amount / 100;
            } else {
                error_log("Stripe order amount not found");
                return NULL;
            }


            if ($event->livemode && $event->livemode == 'true') {
                $stripe_mode = 'live';
            } else {
                $stripe_mode = 'test';
            }

            $data = array(
                'charge_amount'      => $order_amount,
                'charge_created'     => $charge_created,
                'charge_description' => $event->data->object->description,
                'total_amount'       => $event->data->object->amount,
               
                'source_transaction' => $event->data->object->source,
                'stripe_mode'        => $stripe_mode,
                'wc_order_id'        => $event->data->object->metadata->order_id,
            );
            if(isset($event->data->object->latest_charge)){
                $data['source_charge_id']   = $event->data->object->latest_charge;
            }
    
        } catch (Exception $e) {
            WC_Stripe_Logger::log("Stripe Exception in get_event_meta: {$e}");
        }
        return $data;
    }


    /**
     * Transfer to connected account
     */
    private function transfer_to_owner($request_body)
    {
        $result_message = NULL;
        $success = false;
        $transfer = NULL;
        $data_keys = array(
            'secret'    => $this->secret,
            'stripeTestMode' => $this->testmode
        );

        if (!$data_keys) {
            $result_message = 'Missing WooCommerce Stripe settings';
            return array($success, $result_message, $transfer);
        }

        $event_data = $this->get_event_meta($request_body);

        if ($event_data) {
            try {
                $stripe_test_mode = $data_keys['stripeTestMode'] == 'yes';
                $listeo_commission_rate = esc_attr(get_option('listeo_commission_rate'));
                $commission_rate = apply_filters('listeo_commission_rate', $listeo_commission_rate);

                $default_percentage = $commission_rate / 100;
                // Order details

                $order_id = $event_data['wc_order_id'];
                if(isset($event_data['source_charge_id'])){
                    $source_transaction = $event_data['source_charge_id'];
                }
    
                $total_amount = $event_data['charge_amount'];
                
                if (!$order_id ) {
                    $result_message = 'Missing order data.';
                    return array($success, $result_message, $transfer);
                }



                $order = wc_get_order($order_id);
                $order_data = $order->get_data();

                $line_items = $order_data['line_items'];
                /**
                 * @var $item WC_Order_Item_Product
                 */
                foreach ($line_items as $item) {
                    $product_data[] = $item->get_data();
                }

                $p_data = $product_data[0];
                $post = get_post($p_data['product_id']);

                $_product = wc_get_product($p_data['product_id']);
                
                if (!$_product->is_type('listing_booking')) {
                    $result_message = 'Product is not Booking type.';
                    return array($success, $result_message, $transfer);
                }

                $listing_id =  $order->get_meta('listing_id');
                $owner_id = get_post_field('post_author', $listing_id);
                
                
                $stripe_user_id = get_user_meta($owner_id, 'stripe_user_id', true);
                
                
                if (empty($stripe_user_id)){
                    $result_message = esc_html_e('User is not connected to Stripe Connect.', 'listeo_core');
                    return array($success, $result_message, $transfer);
                }

                $total_price = $p_data['total'];
           
                $transfer_amount = $total_price - ($default_percentage * $total_price);
                $transfer_amount = $transfer_amount * 100;
                if (!$transfer_amount) {
                    $result_message = esc_html_e('Error calculating split payment transfer amount.','listeo_core');
                    return array($success, $result_message, $transfer);
                }

                
             
                \Stripe\Stripe::setApiKey($data_keys['secret']);

                $woo_currency = get_woocommerce_currency();
                $currency = ($woo_currency ? $woo_currency : 'usd');

                
                $transfer_data = array(
                    'amount'             => $transfer_amount,
                    'currency'           => $currency,
                    //   'source_transaction' => $source_transaction,
                    'destination'        => $stripe_user_id,
                    'description'        => "Split Booking Payment Order ID:" . $order_id,
                );
                if(isset($source_transaction)){
                    $transfer_data['source_transaction'] = $source_transaction;
                }
                $transfer = \Stripe\Transfer::create($transfer_data);
                
                $success = true;
                
                
                $order->update_meta_data('listeo_stripe_connect_processed', 'yes');
                    

                $order->save_meta_data();

                $pp_data = array(
                    'status' => 'paid',
                    'commission_type' => 'stripe'
                );
                    
                
                global $wpdb;
                $commission_table = $wpdb->prefix . 'listeo_core_commissions';

                $is_updated = $wpdb->update(
                    $commission_table,
                    $pp_data,
                    [
                        'order_id' => $order_id,
                     
                    ]
                );
                
                $result_message = esc_html_e('Transfer successful','listeo_core');
                return array($success, $result_message, $transfer);
            } catch (\InvalidRequestException $e) {
                // Invalid request
                $result_message = "Invalid request: {$e}";
                return array($success, $result_message, $transfer);
            } catch (Exception $e) {

                if (stristr($e, 'no such destination')) {
                    $account_number = substr($e, strpos($e, 'acct_'));
                    $account_number = explode(' ', trim($account_number));
                    $account_number = $account_number[0];
                    $result_message = "Ignored; cannot find account number: {$account_number}. Stripe replied 'No such destination.'";
                    return array($success, $result_message, $transfer);
                } else {
                    $result_message = "Exception: {$e}";
                    return array($success, $result_message, $transfer);
                }
            }
        } else {
            $result_message = __('Could not retreive order meta data.','listeo_core');
            return array($success, $result_message, $transfer);
        }
    }


}