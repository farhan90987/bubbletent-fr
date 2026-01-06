<?php

if (!defined('ABSPATH')) exit;

class Listeo_Core_Stats
{


    public $post_types = array('listing');
    public $stats = array('visits','unique','booking_click');
    public $cookie_name = 'listings_visited';
    /**
     * Cookie ID
     *
     * @var string $cookie_id Cookie ID.
     * @since 2.0.0
     */
    public $cookie_id = 'listeo_stats';

    /**
     * Returns the instance.
     *
     * @since 2.0.0
     */
    public static function get_instance()
    {
        static $instance = null;
        if (is_null($instance)) {
            $instance = new self;
        }
        return $instance;
    }


    /**
     * Constructor.
     *
     * @since 2.0.0
     */
    public function __construct()
    {
        
        $stats_type = get_option('listeo_stats_type',array( 'unique', 'booking_click'));
        
        if(empty($stats_type)){
            $stats_type = array('visits');
        } else {
            $stats_type[] = 'visits';
        }
         
        foreach ($this->stats as $stat_id) {
            if(in_array($stat_id,$stats_type)){
                
                add_action("wp_ajax_listeo_stat_{$stat_id}", array($this, 'update_stat_ajax'));
                add_action("wp_ajax_nopriv_listeo_stat_{$stat_id}", array($this, 'update_stat_ajax'));
            }
        }
        add_action('wp_enqueue_scripts', array($this, 'listeo_stats_scripts'));
      
    }




    /**
     * Stats Script
     *
     * Load Combined Stats JS if Debug is Disabled.
     *
     * @since 2.7.0
     */
    function listeo_stats_scripts()
    {
     
        // Only load in singular listing pages.
        if (is_singular($this->post_types)) {
             $stats_type = get_option('listeo_stats_type',array( 'unique', 'booking_click'));
            if(empty($stats_type)){
                $stats_type = array('visits');
            } else {
                $stats_type[] = 'visits';
            }
            // Single JS to track listings.
            wp_enqueue_script('listeo-stats', LISTEO_CORE_URL . 'assets/js/listeo.stats.min.js', array('wp-util', 'jquery'), 1.0, true);
            $data = array(
                'post_id' => intval(get_queried_object_id()),
                'stats'   => $stats_type,
            );
            wp_localize_script('listeo-stats', 'listeoStats', $data);
        }
    }


    /**
     * Check if tracking is needed for a post.
     */
    public function check($post_id, $log_author = false, $check_cookie = false)
    {
        // Get post, if not valid, bail.
        $post = get_post($post_id);
        if (!$post) {
            return false;
        }

        // Check post type.
        if (!in_array($post->post_type, $this->post_types, true)) {
            return false;
        }

        // Do not track listing author.
        if (!$log_author && is_user_logged_in() && $post->post_author && get_current_user_id() === $post->post_author) {
            return false;
        }

        // If log by cookie.
        if ($check_cookie) {

            // Bail, already logged.
            if (in_array($post_id, $this->get_cookie())) {
                return false;
            }
        }

        return true;
    }



    /**
     * AJAX Callback.
     *
     * @since 2.7.0
     */
    public function update_stat_ajax()
    {
        $request = stripslashes_deep($_POST);

        // Get Post ID.
        $post_id        = intval($request['post_id']);
        $stat           = $request['stat'];
        $is_ajax        = false;
        $check_cookie   = false;
        $log_author     = true;
        switch ($stat) {
            case 'visits':
                $stat_label   = __('Visits', 'wp-job-manager-stats');
                $is_ajax        = true;
                $check_cookie   = false;
                $log_author     = true;
                break;
            case 'unique':
                $stat_label   = __('Unique Visit', 'wp-job-manager-stats');
                $is_ajax        = true;
                $check_cookie   = true;
                $log_author     = true;
                break;

            default:
                # code...
                break;
        }
 
        // Check if tracking needed.
        if ($this->check($post_id,$log_author, $check_cookie)) {

            // Update stat.
            $updated = $this->update_stat_value($post_id, $stat, $check_cookie);
            if ($updated) {

                // Success.
                $data = array(
                    'stat'    => $stat,
                    'post_id' => $post_id,
                    'result'  => 'stat_updated',
                );
                if ($check_cookie) {
                    $data['cookie'] = $this->get_cookie();
                }
                wp_send_json_success($data);
            }
        }

        // Fail.
        $data = array(
            'stat'   => $stat,
            'post_id' => $post_id,
            'result' => 'stat_update_fail',
        );
        if ($check_cookie) {
            $data['cookie'] = $this->get_cookie();
        }
        wp_send_json_error($data);
    }


    public function update_stat_value($post_id, $stat, $check_cookie)
    {
        $updated = $this->listeo_update_stat_value($post_id, $stat);

        // Success.
        if ($updated) {

            // Update cookie if needed.
            if ($check_cookie) {
                $this->add_cookie($post_id,$stat);
            }

            // Update total.
            $this->update_post_stat_total($post_id, $stat);
        }

        return $updated;
    }

    /**
     * Update stat.
     *
     * Update a statistic in the database. This is based on the Listing post ID,
     * the date, and statistic ID. When that combination does not exist, a new value will
     * be created.
     *
     * @since 1.0.0
     *
     * @param   int    $post_id   ID of the listing post.
     * @param   string $stat_id   ID of the statistic. E.g 'views' or 'unique_views'.
     * @param   string $date      Date of the statistic.
     * @param   int    $value     Value of the statistic.
     * @return  mixed             The number of rows updated, or false on error.
     */
    public function listeo_update_stat_value($post_id, $stat_id, $date = false, $value = false)
    {
        global $wpdb;

        /* Check previous value */
        $old_value = $this->listeo_get_stat_value($post_id, $stat_id, $date);

        /* Previous value don't exist, add it. */
        if (
            !$old_value
        ) {
            return
                $this->listeo_add_stat_value($post_id, $stat_id, $date, $value);
        }

        /* Default */
        $date = (false === $date) ? date_i18n('Y-m-d') : $date;
        $value = (false === $value) ? $old_value + 1 : $value;

        /* Update database */
        $data = array(
            'stat_value' => intval($value),
        );
        $where = array(
            'post_id'    => absint($post_id),
            'stat_id'    => sanitize_title($stat_id),
            'stat_date'  => date_i18n('Y-m-d', strtotime($date)),
        );
        $result = $wpdb->update($wpdb->prefix . 'listeo_core_stats', $data, $where);
        return $result;
    }

    /**
     * Get Stat.
     *
     * Get a statistic from the database. This is based on the Listing post ID,
     * the date, and statistic ID. When that combination does not exist, null will
     * be returned.
     *
     * @since 1.0.0
     *
     * @param    int    $post_id   ID of the listing post.
     * @param    string $date      Date of the statistic.
     * @param    string $stat_id   ID of the statistic. E.g 'views' or 'unique_views'.
     * @param    int    $value     Value of the statistic.
     * @return   int                  Returns the stat value when exists, 0 when it doesn't exist.
     */
    function listeo_get_stat_value($post_id, $stat_id, $date = false)
    {
        global $wpdb;

        /* Default */
        $date = (false === $date) ? date_i18n('Y-m-d') : $date;

        /* Get row data */
        $row = $wpdb->get_row($wpdb->prepare("SELECT stat_value FROM {$wpdb->prefix}listeo_core_stats WHERE post_id = %s AND stat_date = %s AND stat_id = %s LIMIT 1", absint($post_id), date_i18n('Y-m-d', strtotime($date)), sanitize_title($stat_id)));

        if (
            is_object($row) && isset($row->stat_value)
        ) {
            return intval($row->stat_value);
        }
        return 0; // default
    }


    /**
     * Add stat.
     *
     * Add a statistic in the database. This is based on the Listing post ID,
     * the date, and statistic ID. When that combination does exist, the existing value
     * will be updated.
     *
     * @since 1.0.0
     *
     * @param   int    $post_id   ID of the listing post.
     * @param   string $date      Date of the statistic, recommended format YYYY-MM-DD.
     * @param   int    $stat_id   ID of the statistic. E.g 'views' or 'unique_views'.
     * @param   mixed  $value     Value of the statistic. False to auto increment from previous value.
     * @return  mixed               Returns row effected when successfully added, or false when failed.
     */
    public function listeo_add_stat_value($post_id, $stat_id, $date = false, $value = false)
    {
        global $wpdb;

        /* Check previous value */
        $old_value =
            $this->listeo_get_stat_value($post_id, $stat_id, $date);

        /* Previous value exist, use update function. */
        if (
            $old_value
        ) {
            return
                $this->listeo_update_stat_value($post_id, $stat_id, $date, $value);
        }

        /* Default */
        $date = (false === $date) ? date_i18n('Y-m-d') : $date;
        $value = (false === $value) ? 1 : $value;

        /* Insert database row */
        $result = $wpdb->query($wpdb->prepare("INSERT INTO `{$wpdb->prefix}listeo_core_stats` (`post_id`, `stat_date`, `stat_id`, `stat_value`) VALUES (%s, %s, %s, %s)", absint($post_id), date_i18n('Y-m-d', strtotime($date)), sanitize_title($stat_id), intval($value)));

        return $result;
    }


    /**
     * Update Post Stats Total Data.
     * This data is only updated on daily basis.
     * Data is useful for posts query based on stats data.
     *
     * @since 2.4.0
     *
     * @param int $post_id Post ID.
     */
    public function update_post_stat_total($post_id, $stat_id)
    {
        // Get today's date.
        $today = intval(date('Ymd')); // YYYYMMDD.

        // Last updated stat value.
        $last_updated = intval(get_post_meta($post_id, '_listeo_' . $stat_id . '_last_updated', true));

        // If not yet updated today, update it.
        if ($today !== $last_updated) {

            // Add updated day.
            update_post_meta($post_id, '_listeo_' . $stat_id . '_last_updated', intval($today));

            // Get stats total, and add it in post meta.
            $total = $this->listeo_get_stats_total($post_id, $stat_id);
            if ($total) {
                update_post_meta($post_id, '_listeo_' . $stat_id . '_total', intval($total));
            }
        }
    }


    /**
     * Get stats total of a stat in a post.
     *
     * @since 2.4.0
     *
     * @param int    $post_id Post ID.
     * @param string $stat_id Stat ID.
     * @return int
     */
    function listeo_get_stats_total($post_id, $stat_id)
    {
        global $wpdb;
        $total = $wpdb->get_results($wpdb->prepare("SELECT SUM(stat_value) stat_value FROM {$wpdb->prefix}listeo_core_stats WHERE post_id = %s AND stat_id = %s", absint($post_id), sanitize_title($stat_id)), 'ARRAY_A');
        if (
            isset($total[0]['stat_value'])
        ) {
            return intval($total[0]['stat_value']);
        }
        return 0;
    }



    /**
     * Get Cookie
     * this will return array of post ids of set cookie.
     *
     * @return array
     */
    public function get_cookie()
    {
        $cookie_id = $this->cookie_id;
        $cookie_name = $this->cookie_name ? $this->cookie_name : $this->stat_id;
        $cookie_value = array();
        if (isset($_COOKIE[$cookie_id]) && !empty($_COOKIE[$cookie_id])) {
            $stats_cookie_value = json_decode(stripslashes($_COOKIE[$cookie_id]), true);
            if (isset($stats_cookie_value[$cookie_name]) && is_array($stats_cookie_value[$cookie_name])) {
                $cookie_value = $stats_cookie_value[$cookie_name];
            }
        }
        return $cookie_value;
    }


    /**
     * Add Post ID in Stat Cookie.
     *
     * @since 2.0.0
     *
     * @param int $post_id Post ID.
     */
    public function add_cookie($post_id,$stat)
    {
        $post_id = intval($post_id);
        $expiration  = intval(apply_filters($stat . '_cookie_expiration', DAY_IN_SECONDS));
        $cookie_id = $this->cookie_id;
        $cookie_name = $this->cookie_name ? $this->cookie_name : $stat;
        $stats_cookie_value = array();
        if (isset($_COOKIE[$cookie_id]) && !empty($_COOKIE[$cookie_id])) {
            $stats_cookie_value = json_decode(stripslashes($_COOKIE[$cookie_id]), true);
        }
        $stats_cookie_value[$cookie_name][$post_id] = $post_id;
        setcookie($cookie_id, json_encode($stats_cookie_value), time() + $expiration);
    }


    

}
