<?php

if (!defined('ABSPATH')) exit;

class Listeo_Core_Chart
{


    public $post_types = array('listing');
    public $stats = array('visits', 'unique', 'booking_click');

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


        add_shortcode('listeo_stats', array($this, 'display_chart'));
        add_shortcode('listeo_stats_full', array($this, 'display_chart_full'));

        add_action('wp_ajax_listeo_chart_refresh', array($this, 'ajax_listeo_chart_refresh'));
    }





    public function get_labels()
    {

        $days = absint(get_option('listeo_stats_default_stat_days', 5)) +1;
        $dates = array();
        $date_from  = strtotime(date_i18n('Y-m-d', strtotime('-' . $days . 'days')));
        $date_to    = strtotime(date_i18n('Y-m-d'));

        while ($date_from <= $date_to) {
            $key = date_i18n('Y-m-d', $date_from);
            $dates[$key] = date_i18n(get_option('date_format'), $date_from);
            $date_from = strtotime('+1 day', $date_from);
        }

        return $dates;
    }




    /**
     * Chart Post IDs
     */
    public function post_ids()
    {
        $args = array(
            'post_type'       => array('listing'),
            'author'          => get_current_user_id(),
            'posts_per_page'  => 10,
        );
        $args = apply_filters('listeo_core_chart_loop_args', $args);
        $get_posts = get_posts($args);
        if (
            !$get_posts
        ) {
            return array();
        }
        $post_ids = array();
        foreach ($get_posts as $get_post) {
            $post_ids[] = $get_post->ID;
        }
        return $post_ids;
    }


    /** @return mixed  */

    function get_data()
    {

        $user_id = get_current_user_id();


        $days = absint(get_option('listeo_stats_default_stat_days', 5)) + 1;
        $args = array(
            'date_from'  => (date_i18n('Y-m-d', strtotime('-' . $days . 'days'))),
            'date_to'    => (date_i18n('Y-m-d')),
            'post_ids'       => $this->post_ids(),
        );

        $data = $this->get_raw_stats($args);
        return $data;
    }

    /**
     * Get Chart Datasets
     */
    public function get_posts_datasets($stats, $labels)
    {

        if (!is_array($stats)) {
            $stats    = $this->get_data();
        }
        if (empty($labels)) {
            $dates    = $this->get_labels();
        } else {
            $dates = $labels;
        }

        $datasets = array();

        /* Add post_id as key */
        $stat_datas = array();

        foreach ($stats as $stat) {
            $stat_datas[$stat->post_id][] = $stat;
        }


        /* Loop each post */
        foreach ($stat_datas as $post_id => $stats) {
            $title = get_the_title($post_id);

            if (!$title) {
                continue;
            }

            /* Add dataset */
            $datasets[$post_id] = array(
                'label' => "#{$post_id} {$title}",
                'data'  => array(),
            );

            /* Add each date to the dataset */
            foreach ($dates as $date => $date_label) {

                $datasets[$post_id]['data'][$date] = 0;
            }

            /* Fill in stats for existing dates */
            foreach ($stats as $stat) {

                if (isset($datasets[$post_id]['data'][$stat->stat_date])) {
                    $datasets[$post_id]['data'][$stat->stat_date] = $stat->stat_value;
                }
            }

            $datasets[$post_id]['data'] = array_values($datasets[$post_id]['data']);
        }


        return $datasets;
    }



    /**
     * Query Stats Data From Database in Simple Array
     */
    public function get_raw_stats($args)
    {
        global $wpdb;


        /* SQL */
        $where = array();
        if (isset($args['stat_id'])) {
            $where[] = "AND stat_id IN ( '" . $args['stat_id'] . "' )";
        }
        if (isset($args['post_ids'])) {
            $where[] = "AND post_id IN ( '" . implode("','", $args['post_ids']) . "' )";
        }
        if ($args['date_from'] && $args['date_to']) {
            $where[] = $wpdb->prepare('AND stat_date between %s and %s', $args['date_from'], $args['date_to']);
        }
        $where = implode(' ', $where);

        $data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}listeo_core_stats WHERE 1=1 {$where}");


        return apply_filters('listeo_stats_data_raw_stats', $data, $args);
    }


    /**
     * Nice Color Schemes
     */
    public function chart_colors()
    {
        $colors = array(
            '26, 188, 156',
            '46, 204, 113',
            '52, 152, 219',
            '155, 89, 182',
            '52, 73, 94',
            '241, 196, 15',
            '230, 126, 34',
            '231, 76, 60',
            '236, 240, 241',
            '149, 165, 166',
            '255, 204, 188',
            '206, 160, 228',
            '199, 44, 28',
            '255, 140, 200',
            '41, 197, 255',
            '255, 194, 155',
            '255, 124, 108',
            '94, 252, 161',
            '46, 204, 113',
            '140, 154, 169',
            '255, 207, 75',
            '255, 146, 107',
            '255, 108, 168',
            '18, 151, 224',
            '155, 89, 182',
            '80, 80, 80',
            '231, 76, 60',
        );
        return $colors;
    }


    function display_chart()
    {
        ob_start();
        wp_enqueue_script('listeo_core-chart-min'); // script
        // $id      = sanitize_html_class($this->get_id());

        // $name    = $this->get_name();
        $data    = $this->get_posts_datasets(false, false);
        $labels  = json_encode(array_values($this->get_labels())); //


?>
        <div class="content chart-box-content">
            <!-- Chart -->
            <div class="chart chart-container">
                <canvas id="chart" width="100" height="45"></canvas>
            </div>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                //Chart.defaults.global.defaultFontFamily = "Nunito";
                Chart.defaults.global.defaultFontColor = '#888';
                Chart.defaults.global.defaultFontSize = '14';
                var ctx = document.getElementById('chart').getContext('2d');

                window.chart = new Chart(ctx, {
                    type: 'line',
                    // The data for our dataset
                    data: {
                        labels: <?php echo $labels; ?>,
                        // Information about the dataset
                        datasets: [
                            <?php
                            $i = 0;
                            foreach ($data as $key => $dataset) {

                                /* Colors */
                                $colors = $this->chart_colors();
                                $i++;
                                $color = isset($colors[$i]) ? $colors[$i] : mt_rand(0, 255) . ',' . mt_rand(0, 255) . ',' . mt_rand(0, 255);

                            ?> {
                                    label: '<?php echo $dataset['label']; ?>',
                                    backgroundColor: 'rgba(<?php echo $color; ?>,0.08)',
                                    borderColor: 'rgba(<?php echo $color; ?>,1)',
                                    borderWidth: "3",
                                    data: <?php echo json_encode($dataset['data']); ?>,
                                    pointRadius: 5,
                                    pointHoverRadius: 5,
                                    pointHitRadius: 10,
                                    pointBackgroundColor: "#fff",
                                    pointHoverBackgroundColor: "#fff",
                                    pointBorderWidth: "2",
                                },

                            <?php } ?>
                        ],
                    },

                    // Configuration options
                    options: {

                        layout: {
                            padding: 10,
                        },

                        legend: {
                            display: false
                        },
                        title: {
                            display: false
                        },

                        scales: {
                            yAxes: [{
                                scaleLabel: {
                                    display: false
                                },
                                gridLines: {
                                    borderDash: [6, 10],
                                    color: "#d8d8d8",
                                    lineWidth: 1,
                                },
                                min: 0,
                                ticks: {
                                    suggestedMin: 0
                                }

                            }],
                            xAxes: [{
                                scaleLabel: {
                                    display: false
                                },
                                gridLines: {
                                    display: false
                                },
                                min: 0,
                                ticks: {
                                    suggestedMin: 0
                                }
                            }],

                        },

                        tooltips: {
                            backgroundColor: '#333',
                            titleFontSize: 13,
                            titleFontColor: '#fff',
                            bodyFontColor: '#fff',
                            bodyFontSize: 13,
                            displayColors: false,
                            xPadding: 10,
                            yPadding: 10,
                            intersect: false
                        }
                    },


                })


            });
        </script>


    <?php
        $html = ob_get_clean();
        return $html;
    }


    function get_listings_ids()
    {

        $current_user = wp_get_current_user();
        $post_status = array('publish', 'pending_payment', 'expired', 'draft', 'pending');
        $listings = new WP_Query(
            array(
                'author'            =>  $current_user->ID,
                'fields'              => 'ids', // Only get post IDs
                'posts_per_page'      => -1,
                'post_type'           => 'listing',
                'post_status'          => $post_status,
            )
        );
        return $listings;
    }
    function ajax_listeo_chart_refresh()
    {

        $date_start = $_POST['date_start'];
        $date_end = $_POST['date_end'];
        $type = $_POST['stat_type'];
        $listing = (isset($_POST['listing'])) ? $_POST['listing'] : false;

        if (!empty($listing)) {
            if ($listing == 'show_all') {
                $post_ids = $this->post_ids();
            } else {
                $post_ids = explode(" ", $listing);
            }
        } else {
            $post_ids = $this->post_ids();
        }

        global $wpdb;

        // setting dates to MySQL style
        $date_start = esc_sql(date("Y-m-d H:i:s", strtotime($wpdb->esc_like($date_start))));
        $date_end = esc_sql(date("Y-m-d H:i:s", strtotime($wpdb->esc_like($date_end))));

        $args = array(
            'date_from'  => $date_start,
            'date_to'    => $date_end,
            'post_ids'   => $post_ids,
            'stat_id'   => $type
        );

        $data = $this->get_raw_stats($args);



        $dates = array();
        $date_from  = strtotime($date_start);
        $date_to    = strtotime($date_end);

        while ($date_from <= $date_to) {
            $key = date_i18n('Y-m-d', $date_from);
            $dates[$key] = date_i18n(get_option('date_format'), $date_from);
            $date_from = strtotime('+1 day', $date_from);
        }


        $labels =  $dates;

        $postdata_raw = $this->get_posts_datasets($data, $labels);
        $postdata = array();
        $i = 0;
        foreach ($postdata_raw as $key => $dataset) {
            /* Colors */
            $colors = $this->chart_colors();
            $i++;
            $color = isset($colors[$i]) ? $colors[$i] : mt_rand(0, 255) . ',' . mt_rand(0, 255) . ',' . mt_rand(0, 255);
            $dataset['backgroundColor'] = 'rgba(' . $color . ',0.08)';
            $dataset['borderColor'] = 'rgba(' . $color . ',1)';
            $postdata[] = $dataset;
        }

        $result = array(
            'data' => $postdata,
            'labels' => $labels,
        );
        wp_send_json($result);
        die();
    }



    function display_chart_full()
    {
        ob_start();
        wp_enqueue_script('listeo_core-chart-min'); // script
        // $id      = sanitize_html_class($this->get_id());

        // $name    = $this->get_name();
        $data    = $this->get_posts_datasets(false, false);
        $labels  = json_encode(array_values($this->get_labels())); //


    ?>
        <div class="row">
            <!-- Listings -->
            <div class="col-lg-12 col-md-12">
                <div class="dashboard-list-box dashboard-chart-full margin-top-0">
                    <div class="headline-with-filters">


                        <div class=" chart-filters">
                            <div id="chart-date-range" style="display: none;">
                                <span></span>
                            </div>

                            <div class="sort-by">
                                <div class="sort-by-select">
                                    <select data-placeholder="Default order" class="select2-bookings" id="listing_id">
                                        <option value="show_all"><?php echo esc_html__('All listings', 'listeo_core') ?></option>
                                        <?php
                                        $listings = $this->get_listings_ids();
                                        foreach ($listings->posts as $listing_id) { ?>
                                            <option value="<?php echo $listing_id; ?>"><?php echo get_the_title($listing_id); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="sort-by-status chart-sort-by">
                                <div class="sort-by-select">
                                    <select data-placeholder="<?php esc_attr_e('Visits', 'listeo_core') ?>" class="select2-bookings-status" id="stat_type">
                                        <option value="visits"><?php echo esc_html__('All Visits', 'listeo_core') ?></option>
                                        <option value="unique"><?php echo esc_html__('Unique Visits', 'listeo_core') ?></option>
                                        <option value="booking_click"><?php echo esc_html__('Booking form clicks', 'listeo_core') ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <h4><?php esc_html_e('Listings Analytics', 'listeo_core')
                            ?> <i class="fa fa-circle-o-notch fa-spin booking-loading-icon"></i> </h4>
                    </div>
                    <div class="content chart-box-content">
                        <!-- Chart -->

                        <div class=" chart chart-container">
                            <canvas id="chart" width="100" height="45"></canvas>
                        </div>
                    </div>
                    <script type="text/javascript">
                        jQuery(document).ready(function($) {
                            //Chart.defaults.global.defaultFontFamily = "Nunito";
                            Chart.defaults.global.defaultFontColor = '#888';
                            Chart.defaults.global.defaultFontSize = '14';
                            var ctx = document.getElementById('chart').getContext('2d');

                            window.chart = new Chart(ctx, {
                                type: 'line',
                                // The data for our dataset
                                data: {
                                    labels: <?php echo $labels; ?>,
                                    // Information about the dataset
                                    datasets: [
                                        <?php
                                        $i = 0;
                                        foreach ($data as $key => $dataset) {

                                            /* Colors */
                                            $colors = $this->chart_colors();
                                            $i++;
                                            $color = isset($colors[$i]) ? $colors[$i] : mt_rand(0, 255) . ',' . mt_rand(0, 255) . ',' . mt_rand(0, 255);

                                        ?> {
                                                label: '<?php echo $dataset['label']; ?>',
                                                backgroundColor: 'rgba(<?php echo $color; ?>,0.08)',
                                                borderColor: 'rgba(<?php echo $color; ?>,1)',
                                                borderWidth: "3",
                                                data: <?php echo json_encode($dataset['data']); ?>,
                                                pointRadius: 5,
                                                pointHoverRadius: 5,
                                                pointHitRadius: 10,
                                                pointBackgroundColor: "#fff",
                                                pointHoverBackgroundColor: "#fff",
                                                pointBorderWidth: "2",
                                            },

                                        <?php } ?>
                                    ],
                                },

                                // Configuration options
                                options: {

                                    layout: {
                                        padding: 10,
                                    },

                                    legend: {
                                        display: false
                                    },
                                    title: {
                                        display: false
                                    },

                                    scales: {
                                        yAxes: [{
                                            scaleLabel: {
                                                display: false
                                            },
                                            gridLines: {
                                                borderDash: [6, 10],
                                                color: "#d8d8d8",
                                                lineWidth: 1,
                                            },
                                            min: 0,
                                            ticks: {

                                                suggestedMin: 0
                                            }

                                        }],
                                        xAxes: [{
                                            scaleLabel: {
                                                display: false
                                            },
                                            gridLines: {
                                                display: false
                                            },
                                            min: 0,
                                            ticks: {

                                                suggestedMin: 0
                                            }
                                        }],

                                    },

                                    tooltips: {
                                        backgroundColor: '#333',
                                        titleFontSize: 13,
                                        titleFontColor: '#fff',
                                        bodyFontColor: '#fff',
                                        bodyFontSize: 13,
                                        displayColors: false,
                                        xPadding: 10,
                                        yPadding: 10,
                                        intersect: false
                                    }
                                },


                            })


                        });
                    </script>
                </div>
            </div>
        </div>

<?php
        $html = ob_get_clean();
        return $html;
    }
}
