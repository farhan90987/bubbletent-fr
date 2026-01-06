<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;
/**
 * Listeo_Core_Listing class
 */
class Listeo_Core_Calendar_View
{

    /**
     * The single instance of the class.
     *
     * @var self
     * @since  1.26
     */
    private static $_instance = null;

    /**
     * Allows for accessing single instance of class. Class should only be constructed once per call.
     *
     * @since  1.26
     * @static
     * @return self Main instance.
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    /**
     * Constructor.
     *
     * @since 2.0.0
     */
    public function __construct()
    {

        add_action('wp_enqueue_scripts', array($this, 'listeo_calendar_view_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'listeo_calendar_view_style'));
        add_shortcode('listeo_calendar_view', array($this, 'calendar_view'));
        add_action("wp_ajax_listeo_get_calendar_view_events", array($this, 'ajax_get_events'));
        add_action("wp_ajax_listeo_get_calendar_view_single_events", array($this, 'ajax_get_single_events'));
        add_action("wp_ajax_listeo_get_calendar_view_event_details", array($this, 'ajax_get_event_details'));
    }



    /**
     * Stats Script
     *
     * Load Combined Stats JS if Debug is Disabled.
     *
     * @since 2.7.0
     */
    function listeo_calendar_view_scripts()
    {
        $bookings_calendar_page = get_option('listeo_bookings_calendar_page');
        global $post;
        // Single JS to track listings.
         if (isset($post) && $post->ID == $bookings_calendar_page || is_singular('listing')) {
            $language = get_option('listeo_calendar_view_lang','en');
            wp_enqueue_script('listeo-core-fullcalendar', LISTEO_CORE_URL . 'assets/js/fullcalendar.min.js', array('jquery'), 1.0, true);
            if($language != 'en'){
                wp_enqueue_script('listeo-core-fullcalendar-lang', LISTEO_CORE_URL . 'assets/js/locales/' . $language . '.js', array('jquery'), 1.0, true);
            }
            $data = array(
                'language'   => $language,
            );
            if($post->ID == $bookings_calendar_page){
                wp_enqueue_script('listeo-core-fullcalendar-view', LISTEO_CORE_URL . 'assets/js/listeo.fullcalendar.js', array('jquery'), 1.0, true);
                wp_localize_script('listeo-core-fullcalendar-view', 'listeoCal', $data); 
            } else {
                wp_enqueue_script('listeo-core-fullcalendar-single-view', LISTEO_CORE_URL . 'assets/js/listeo.fullcalendar.single.js', array('jquery'), 1.0, true);
                wp_localize_script('listeo-core-fullcalendar-single-view', 'listeoCal', $data); 
            }
           
        }
       

    }

    function listeo_calendar_view_style()
    {

        wp_register_style('listeo-core-fullcalendar', LISTEO_CORE_URL . 'assets/css/fullcalendar.min.css', array(), '1.0');
        wp_enqueue_style('listeo-core-fullcalendar');
        // Single JS to track listings.


    }

    function calendar_view()
    {
        ob_start();
        $users = new Listeo_Core_Users;
        $listings = $users->get_agent_listings('', 0, -1);
        $template_loader = new Listeo_Core_Template_Loader;
        $template_loader->set_template_data(
            array(
                'message' => '',
                'listings' => $listings->posts,
            )
        )->get_template_part('account/calendar-view');
        $html = ob_get_clean();
        return $html;
    }

    function ajax_get_events()
    {
        $users = new Listeo_Core_Users;

        $listings = $users->get_agent_listings('', 0, -1);
        $args = array(
            'owner_id' => get_current_user_id(),
            'type' => 'reservation',

        );

        $dates_args = $_POST['dates'];
        $date_start = $dates_args['startStr'];
        $date_end = $dates_args['endStr'];
        if (isset($_POST['listing_id']) &&  $_POST['listing_id'] != 'show_all') $args['listing_id'] = $_POST['listing_id'];
        if (isset($_POST['listing_status']) && $_POST['listing_status'] != 'show_all') $args['status'] = $_POST['listing_status'];
        if (isset($_POST['booking_author']) && $_POST['booking_author'] != 'show_all') $args['bookings_author'] = $_POST['booking_author'];



        if (isset($_GET['status'])) {

            $args['status'] = $_GET['status'];
        }
        $bookings = new Listeo_Core_Bookings_Calendar;
        $data = $bookings->get_bookings(
            $date_start,
            $date_end,
            $args,
            'booking_date',
            $limit = ''
        );
        // return 

        $events = array();
        if ($data) {

            //parse booking for fullcalendar
            foreach ($data as $key => $booking) {

                $details = json_decode($booking['comment']);
                // title start
                $title = array();
                if (isset($details->first_name)) $title[] = esc_html(stripslashes($details->first_name));
                if (isset($details->last_name)) $title[] = esc_html(stripslashes($details->last_name));
                $title[] = ' - ';
                $title[] = get_the_title($booking['listing_id']);
              //  $title[] = ' ('.$booking['status'].')';

                $event_title = implode(' ', $title);
                // title end

                //status color

                $booking_status = $booking['status'];
                if (
                    $booking_status != 'paid' && isset($booking['order_id']) && !empty($booking['order_id']) && $booking_status == 'confirmed'
                ) {
                    $order = wc_get_order($booking['order_id']);
                    if ($order) {
                        $payment_url = $order->get_checkout_payment_url();

                        $order_data = $order->get_data();

                        $order_status = $order_data['status'];
                    }
                    if (new DateTime() > new DateTime($booking['expiring'])) {
                        $booking_status = 'expired';
                    }
                }
                switch ($booking_status) {
                    case 'paid':
                        $bgcolor = '#64bc36';
                        break;
                    case 'pay_to_confirm':
                    case 'confirmed':
                        $bgcolor = '#ECBE1F';
                        break;
                    case 'waiting':
                        $bgcolor = '#61b2db';
                        break;

                    case 'expired':
                        $bgcolor = '#ee3535';
                        break;

                    default:
                        $bgcolor = '#aaa';
                        break;
                }

                $args = array(
                    'id'        => $booking['ID'],
                    'title'     => $event_title,
                    'start'     => $booking['date_start'],
                    'end'       => $booking['date_end'],
                    'description'       => $booking['price'],
                    'backgroundColor' => $bgcolor,
                    'borderColor' => $bgcolor,

                );
                if ($booking_status == 'owner_reservations') {
                    $args["allDay"] = true;
                    $args["display"] = 'background';
                }
                $events[] = $args;
            }
        }
        // $data[] = array(
        //     'id'   => 1,
        //     'title'   => 'test',
        //     'start'   => '2022-08-08',
        //     'end'   => '2022-08-18',
        // );

        echo json_encode($events, JSON_UNESCAPED_UNICODE);
        wp_die();
    }

    function ajax_get_single_events(){
        $users = new Listeo_Core_Users;

        global $post;
        
      
        $args = array(
            
            'type' => 'reservation',
        );
        $args['listing_id'] = $_POST['listing_id'];
        $args['owner_id'] =get_post_field('post_author', $_POST['listing_id']);

        $dates_args = $_POST['dates'];
        $date_start = $dates_args['startStr'];
      
        $date_end = $dates_args['endStr'];
        
        $type = get_option('listeo_show_calendar_single_type','owner');
        $bookings = new Listeo_Core_Bookings_Calendar;
        $data = $bookings->get_bookings(
            $date_start,
            $date_end,
            $args,
            'booking_date',
            $limit = '',
            $offset = '',
            $type
        );
        // return 

        $events = array();
        if ($data) {

            //parse booking for fullcalendar
            foreach ($data as $key => $booking) {

                $details = json_decode($booking['comment']);
                // title start
                $title = array();
               // $title[] = esc_html__('Booked','listeo_core');
                
                //  $title[] = ' ('.$booking['status'].')';

                $event_title = implode(' ', $title);
                // title end

                //status color

                $booking_status = $booking['status'];
                // if($booking_status == 'owner_reservations'){
                //     $event_title = "Closed day";
                // }
                if (
                    $booking_status != 'paid' && isset($booking['order_id']) && !empty($booking['order_id']) && $booking_status == 'confirmed'
                ) {
                    $order = wc_get_order($booking['order_id']);
                    if ($order) {
                        $payment_url = $order->get_checkout_payment_url();

                        $order_data = $order->get_data();

                        $order_status = $order_data['status'];
                    }
                    if (new DateTime() > new DateTime($booking['expiring'])) {
                        $booking_status = 'expired';
                    }
                }
                switch ($booking_status) {
                    case 'paid':
                        $bgcolor = '#64bc36';
                        break;
                    case 'pay_to_confirm':
                    case 'confirmed':
                        $bgcolor = '#ECBE1F';
                        break;
                    case 'waiting':
                        $bgcolor = '#61b2db';
                        break;
                    case 'owner_reservations':
                    case 'expired':
                        $bgcolor = '#ee3535';
                        break;

                    default:
                        $bgcolor = '#aaa';
                        break;
                }

                $args = array(
                    'id'        => $booking['ID'],
                    'title'     => $event_title,                   
                    'start'     => $booking['date_start'],
                    'end'       => $booking['date_end'],
                    'description'       => $booking['price'],
                    'backgroundColor' => $bgcolor,
                    'borderColor' => $bgcolor,
                );
                if ($booking_status == 'owner_reservations') {
                   $args["allDay"] = true;
                   $args["display"] = 'background';
                }
               
                $events[] = $args;
                 
            }
        }
        // $events[] = array(
        //     'id'   => 1,
        //     'title'   => 'test',
        //     'start'   => '2023-01-30',
        //     'end'   => '2023-01-32',
        // );

        echo json_encode($events, JSON_UNESCAPED_UNICODE);
        wp_die();
    }

    function ajax_get_event_details()
    {
        $booking_id = $_POST['id'];

        $template_loader = new Listeo_Core_Template_Loader;
        $bookings = new Listeo_Core_Bookings_Calendar;
        $booking_data = $bookings->get_booking($booking_id);
        ob_start();
        $template_loader->set_template_data($booking_data)->get_template_part('booking/content-booking-calendar');
        $result['html'] = ob_get_clean();
        wp_send_json_success($result);
    }
}
