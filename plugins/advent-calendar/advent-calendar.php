<?php
/**
 * Plugin Name: Advent Calendar Date-Based Winner Selection
 * Description: Advent Calendar entries with date-based winner selection via ACF, WooCommerce coupon generation, email templates.
 * Version: 1.0
 * Author: Mathes
 */

if (!defined('ABSPATH')) exit;

class Advent_Calendar_Manager {

    const CPT = 'advent_calendar';
    const CRON_HOOK = 'ac_daily_1200_event';
    private $shortcode_rendered = false;

    public function __construct() {
        // core
        add_action('init', [$this, 'register_cpt']);
        add_action('add_meta_boxes', [$this, 'register_meta_boxes']);
        add_action('save_post', [$this, 'save_meta_fields']);

        // frontend shortcode + assets + ajax
        add_shortcode('advent_calendar', [$this, 'render_advent_calendar']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_submit_advent_entry', [$this, 'ajax_submit_entry']);
        add_action('wp_ajax_nopriv_submit_advent_entry', [$this, 'ajax_submit_entry']);

        // email verification
        add_action('init', [$this, 'handle_email_verification']);
        add_action('wp_ajax_resend_verification_email', [$this, 'ajax_resend_verification_email']);
        add_action('wp_ajax_nopriv_resend_verification_email', [$this, 'ajax_resend_verification_email']);

        // admin columns
        add_filter('manage_' . self::CPT . '_posts_columns', [$this, 'add_admin_columns']);
        add_action('manage_' . self::CPT . '_posts_custom_column', [$this, 'render_admin_columns'], 10, 2);
        add_filter('manage_edit-' . self::CPT . '_sortable_columns', [$this, 'add_sortable_columns']);

        // cron
        add_action(self::CRON_HOOK, [$this, 'process_daily_selection']);
        register_activation_hook(__FILE__, [$this, 'on_activation']);
        register_deactivation_hook(__FILE__, [$this, 'on_deactivation']);

        // ACF options
        add_action('acf/init', [$this, 'register_acf_fields']);
    }

    /**************************************************************************
     * CPT & Meta
     **************************************************************************/
    public function register_cpt() {
        $labels = [
            'name' => 'Advent Calendar',
            'singular_name' => 'Advent Entry',
            'menu_name' => 'Advent Calendar',
        ];
        $args = [
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'menu_icon' => 'dashicons-calendar',
            'supports' => ['title'],
        ];
        register_post_type(self::CPT, $args);
    }

    public function register_meta_boxes() {
        add_meta_box('ac_meta_box', 'Advent Entry Meta', [$this, 'render_meta_box'], self::CPT, 'normal', 'default');
    }

    public function render_meta_box($post) {
        $email     = get_post_meta($post->ID, '_ac_email', true);
        $first_name = get_post_meta($post->ID, '_ac_first_name', true);
        $last_name  = get_post_meta($post->ID, '_ac_last_name', true);
        $day       = get_post_meta($post->ID, '_ac_day', true);
        $coupon    = get_post_meta($post->ID, '_ac_coupon', true);
        $is_winner = get_post_meta($post->ID, '_ac_is_winner', true);
        $verified  = get_post_meta($post->ID, '_ac_email_verified', true);

        wp_nonce_field('ac_save_meta', 'ac_meta_nonce');
        ?>
        <p><label><strong>Day:</strong></label><br><input type="text" readonly value="<?php echo esc_attr($day); ?>" style="width:100%"></p>
        <p><label><strong>First Name:</strong></label><br><input type="text" name="ac_first_name" value="<?php echo esc_attr($first_name); ?>" style="width:100%"></p>
        <p><label><strong>Last Name:</strong></label><br><input type="text" name="ac_last_name" value="<?php echo esc_attr($last_name); ?>" style="width:100%"></p>
        <p><label><strong>Email:</strong></label><br><input type="email" name="ac_email" value="<?php echo esc_attr($email); ?>" style="width:100%"></p>
        <p><label><strong>Email Verified:</strong></label><br>
            <?php echo $verified ? '<span style="color:green;">✅ Verified</span>' : '<span style="color:red;">❌ Not Verified</span>'; ?>
        </p>
        <p><label><strong>Coupon (if generated):</strong></label><br><input type="text" readonly value="<?php echo esc_attr($coupon); ?>" style="width:100%"></p>
        <p><strong>Winner?</strong> <?php echo $is_winner ? 'Yes' : 'No'; ?></p>
        <?php
    }

    public function save_meta_fields($post_id) {
        if (!isset($_POST['ac_meta_nonce']) || !wp_verify_nonce($_POST['ac_meta_nonce'], 'ac_save_meta')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        if (isset($_POST['ac_email'])) update_post_meta($post_id, '_ac_email', sanitize_email($_POST['ac_email']));
        if (isset($_POST['ac_first_name'])) update_post_meta($post_id, '_ac_first_name', sanitize_text_field($_POST['ac_first_name']));
        if (isset($_POST['ac_last_name'])) update_post_meta($post_id, '_ac_last_name', sanitize_text_field($_POST['ac_last_name']));
    }

    /**************************************************************************
     * Admin Columns
     **************************************************************************/
    public function add_admin_columns($columns) {
        $new_columns = [
            'cb' => $columns['cb'],
            'title' => $columns['title'],
            'ac_first_name' => 'First Name',
            'ac_last_name' => 'Last Name',
            'ac_email' => 'Email',
            'ac_email_verified' => 'Verified',
            'ac_day' => 'Day',
            'ac_winner_status' => 'Winner Status',
            'ac_coupon' => 'Coupon',
            'date' => $columns['date'],
        ];
        return $new_columns;
    }

    public function render_admin_columns($column, $post_id) {
        switch ($column) {
            case 'ac_first_name':
                echo esc_html(get_post_meta($post_id, '_ac_first_name', true));
                break;
            case 'ac_last_name':
                echo esc_html(get_post_meta($post_id, '_ac_last_name', true));
                break;
            case 'ac_email':
                echo esc_html(get_post_meta($post_id, '_ac_email', true));
                break;
            case 'ac_email_verified':
                $verified = get_post_meta($post_id, '_ac_email_verified', true);
                if ($verified) {
                    echo '<span style="color:green;">✅ Verified</span>';
                } else {
                    echo '<span style="color:red;">❌ Not Verified</span>';
                }
                break;
            case 'ac_day':
                echo esc_html(get_post_meta($post_id, '_ac_day', true));
                break;
            case 'ac_winner_status':
                $is_winner = get_post_meta($post_id, '_ac_is_winner', true);
                if ($is_winner) {
                    echo '<span style="color:green;font-weight:bold;">🎉 WINNER</span>';
                } else {
                    echo '<span style="color:#ccc;">Participant</span>';
                }
                break;
            case 'ac_coupon':
                $coupon = get_post_meta($post_id, '_ac_coupon', true);
                if ($coupon) {
                    echo esc_html($coupon);
                } else {
                    echo '<span style="color:#ccc;">—</span>';
                }
                break;
        }
    }

    public function add_sortable_columns($columns) {
        $columns['ac_day'] = 'ac_day';
        $columns['ac_winner_status'] = 'ac_winner_status';
        $columns['ac_email_verified'] = 'ac_email_verified';
        return $columns;
    }

    /**************************************************************************
     * Email Verification System
     **************************************************************************/
    
    /**
     * Generate verification token
     */
    private function generate_verification_token() {
        return wp_generate_password(32, false);
    }

    /**
     * Send verification email
     */
    private function send_verification_email($post_id) {
        $email = get_post_meta($post_id, '_ac_email', true);
        $first_name = get_post_meta($post_id, '_ac_first_name', true);
        $last_name = get_post_meta($post_id, '_ac_last_name', true);
        $name = $first_name . ' ' . $last_name;
        $token = get_post_meta($post_id, '_ac_verification_token', true);
        
        if (!$token) {
            $token = $this->generate_verification_token();
            update_post_meta($post_id, '_ac_verification_token', $token);
        }

        $verification_url = add_query_arg([
            'ac_verify' => $token,
            'entry_id' => $post_id
        ], home_url('/advent-calendar'));

        $subject = __("Vérifie ton inscription au calendrier de l'Avent", 'advent-calendar');
        $body = "
        <p>" . sprintf(__('Bonjour %s', 'advent-calendar'), esc_html($name)) . ",</p>
        <p>" . __("Merci d'avoir soumis ta contribution à notre calendrier de l'Avent !", 'advent-calendar') . "</p>
        <p>" . __("Veuille à vérifier ton adresse e-mail en cliquant sur le lien ci-dessous :", 'advent-calendar') . "</p>
        <p><a href='" . esc_url($verification_url) . "' style='background:#007cba; color:white; padding:12px 24px; text-decoration:none; border-radius:4px; display:inline-block;'>" . __('Vérifie ton adresse e-mail', 'advent-calendar') . "</a></p>
        <p>" . __('Ou copie et colle cette URL dans ton navigateur :', 'advent-calendar') . "<br><code>" . esc_url($verification_url) . "</code></p>
        <p>" . __("Si tu n'as pas soumis cette entrée, ignore cet e-mail.", 'advent-calendar') . "</p>
        <p>— " . __('© 2025 Book a Bubble', 'advent-calendar') . "</p>
        ";

        $headers = ['Content-Type: text/html; charset=UTF-8'];
        
        if (wp_mail($email, $subject, $body, $headers)) {
            error_log('Advent Calendar: Verification email sent to ' . $email);
            return true;
        } else {
            error_log('Advent Calendar: Failed to send verification email to ' . $email);
            return false;
        }
    }

    /**
     * Handle email verification via URL
     */
    public function handle_email_verification() {
        if (!isset($_GET['ac_verify']) || !isset($_GET['entry_id'])) {
            return;
        }

        $token = sanitize_text_field($_GET['ac_verify']);
        $entry_id = intval($_GET['entry_id']);

        // Verify the token
        $stored_token = get_post_meta($entry_id, '_ac_verification_token', true);
        $is_verified = get_post_meta($entry_id, '_ac_email_verified', true);

        if ($stored_token === $token) {
            if (!$is_verified) {
                update_post_meta($entry_id, '_ac_email_verified', 1);
                delete_post_meta($entry_id, '_ac_verification_token');
                
                // Set success message
                set_transient('ac_verification_success', __("Votre adresse e-mail a été vérifiée avec succès ! Vous êtes désormais inscrit au calendrier de l'Avent.", 'advent-calendar'), 60);
            } else {
                set_transient('ac_verification_info', __('Votre adresse e-mail a déjà été vérifiée.', 'advent-calendar'), 60);
            }
        } else {
            set_transient('ac_verification_error', __('Lien de vérification invalide. Veuillez réessayer ou demander un nouvel e-mail de vérification.', 'advent-calendar'), 60);
        }
    }

    /**
     * AJAX handler to resend verification email
     */
    public function ajax_resend_verification_email() {
        check_ajax_referer('advent_nonce', 'nonce');

        $entry_id = intval($_POST['entry_id'] ?? 0);
        
        if (!$entry_id) {
            wp_send_json_error(['message' => __("ID d'entrée non valide.", 'advent-calendar')]);
            wp_die();
        }

        $email = get_post_meta($entry_id, '_ac_email', true);
        if (!$email) {
            wp_send_json_error(['message' => __('Entrée introuvable.', 'advent-calendar')]);
            wp_die();
        }

        // Generate new token and send email
        $token = $this->generate_verification_token();
        update_post_meta($entry_id, '_ac_verification_token', $token);
        
        if ($this->send_verification_email($entry_id)) {
            wp_send_json_success(['message' => __('E-mail de vérification envoyé ! Veuillez vérifier votre boîte de réception.', 'advent-calendar')]);
        } else {
            wp_send_json_error(['message' => __("Échec de l'envoi de l'e-mail de vérification. Veuillez réessayer.", 'advent-calendar')]);
        }
        
        wp_die();
    }

    /**************************************************************************
     * ACF Fields Registration
     **************************************************************************/
    public function register_acf_fields() {
        if (!function_exists('acf_add_local_field_group')) return;

        // Create fields for each day (1-24)
        $fields = [];
        
        for ($i = 1; $i <= 24; $i++) {
            $fields[] = [
                'key' => "field_day_{$i}_group",
                'label' => "Day {$i} Settings",
                'name' => "day_{$i}",
                'type' => 'group',
                'layout' => 'block',
                'sub_fields' => [
                    [
                        'key' => "field_day_{$i}_enabled",
                        'label' => 'Enabled',
                        'name' => 'day_enabled',
                        'type' => 'true_false',
                        'default_value' => 0,
                        'ui' => 1,
                    ],
                    [
                        'key' => "field_day_{$i}_date",
                        'label' => 'Selection Date',
                        'name' => 'day_date',
                        'type' => 'date_picker',
                        'display_format' => 'd/m/Y',
                        'return_format' => 'Y-m-d',
                        'first_day' => 1,
                        'conditional_logic' => [
                            [
                                [
                                    'field' => "field_day_{$i}_enabled",
                                    'operator' => '==',
                                    'value' => '1'
                                ]
                            ]
                        ],
                    ],
                    [
                        'key' => "field_day_{$i}_popup_text",
                        'label' => 'Popup Text',
                        'name' => 'day_popup_text',
                        'type' => 'textarea',
                        'placeholder' => 'Enter custom text to display in the popup form for this day',
                        'conditional_logic' => [
                            [
                                [
                                    'field' => "field_day_{$i}_enabled",
                                    'operator' => '==',
                                    'value' => '1'
                                ]
                            ]
                        ],
                    ],
                    [
                        'key' => "field_day_{$i}_choose_winner",
                        'label' => 'Choose Winner on this Date',
                        'name' => 'day_choose_winner',
                        'type' => 'true_false',
                        'default_value' => 0,
                        'ui' => 1,
                        'conditional_logic' => [
                            [
                                [
                                    'field' => "field_day_{$i}_enabled",
                                    'operator' => '==',
                                    'value' => '1'
                                ]
                            ]
                        ],
                    ],
                    [
                        'key' => "field_day_{$i}_winner_voucher",
                        'label' => 'Winner Voucher Code',
                        'name' => 'day_winner_voucher',
                        'type' => 'text',
                        'placeholder' => 'WINNER25',
                        'conditional_logic' => [
                            [
                                [
                                    'field' => "field_day_{$i}_enabled",
                                    'operator' => '==',
                                    'value' => '1'
                                ],
                                [
                                    'field' => "field_day_{$i}_choose_winner",
                                    'operator' => '==',
                                    'value' => '1'
                                ]
                            ]
                        ],
                    ],
                    [
                        'key' => "field_day_{$i}_loser_voucher",
                        'label' => 'Loser Voucher Code',
                        'name' => 'day_loser_voucher',
                        'type' => 'text',
                        'placeholder' => 'LOSER5',
                        'conditional_logic' => [
                            [
                                [
                                    'field' => "field_day_{$i}_enabled",
                                    'operator' => '==',
                                    'value' => '1'
                                ]
                            ]
                        ],
                    ],
                ],
            ];
        }

        acf_add_local_field_group([
            'key' => 'group_advent_calendar_settings',
            'title' => 'Advent Calendar Settings',
            'fields' => $fields,
            'location' => [
                [
                    [
                        'param' => 'options_page',
                        'operator' => '==',
                        'value' => 'advent-settings',
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => true,
            'description' => '',
        ]);

        // Add options page
        if (function_exists('acf_add_options_page')) {
            acf_add_options_page([
                'page_title' => 'Advent Calendar Settings',
                'menu_title' => 'Advent Settings',
                'menu_slug' => 'advent-settings',
                'parent_slug' => 'edit.php?post_type=advent_calendar',
                'capability' => 'manage_options',
                'redirect'   => false,
            ]);
        }
    }

    /**
     * Shortcode: [advent_calendar]
     * Renders 1..24 boxes and popup markup
     */
    public function render_advent_calendar() {
        $this->shortcode_rendered = true; // Set flag for conditional enqueue
        
        ob_start();
        
        // Display verification messages
        $this->display_verification_messages();
        
        // Build days array with active status from ACF options
        $days = [];
        for ($i = 1; $i <= 24; $i++) {
            $is_active = $this->is_day_active($i);
            $popup_text = $this->get_day_popup_text($i);
            $days[$i] = [
                'active' => $is_active,
                'popup_text' => $popup_text,
            ];
        }

        // Render grid
        echo '<div class="ac-main">';
        echo "<audio id='clickSound' src='".plugin_dir_url(__FILE__)."assets/audio/Magic.mp3'></audio>";
        echo "<img class='snow-img' src='".plugin_dir_url(__FILE__)."assets/img/snow.png' alt='snow-img' />";
        echo '<div class="container">';
        echo '<h1>';
        esc_html_e('Tente ta chance et gagne une nuit magique dans une tente en bulle', 'advent-calendar');
        echo '</h1>';
        echo '<div class="ac-grid" id="ac-grid">';
        foreach ($days as $n => $meta) {
            $cls = 'ac-box' . ($meta['active'] ? ' enabled heartbeat' : ' disabled');
            
            echo "<div class='{$cls} ac-box{$n}' data-day='{$n}' data-popup-text='" . esc_attr($meta['popup_text']) . "'><div class='door-telt'>";
            echo "<img class='star-icon' src='".plugin_dir_url(__FILE__)."assets/img/icon-star.png' alt='day-icon' />";
            echo "<strong>{$n}</strong>";
            echo "<img class='day-icon' src='".plugin_dir_url(__FILE__)."assets/img/icon-{$n}.png' alt='day-icon' />";
            echo "</div></div>";
        }
        echo '</div></div></div>';

        // Popup HTML + overlay
        ?>
        <div class="ac-overlay" id="ac-overlay"></div>
        <div class="loader-main"><span class="loader"></span></div>
        <div class="ac-popup" id="ac-popup" role="dialog" aria-hidden="true">
            <img class="popup-bgimg" src="<?php echo plugin_dir_url(__FILE__); ?>assets/img/popup-bg.png" alt="">
            <div class="ac-popup-content">
                <a id="ac-cancel" href="#">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M128 128C92.7 128 64 156.7 64 192L64 448C64 483.3 92.7 512 128 512L512 512C547.3 512 576 483.3 576 448L576 192C576 156.7 547.3 128 512 128L128 128zM231 231C240.4 221.6 255.6 221.6 264.9 231L319.9 286L374.9 231C384.3 221.6 399.5 221.6 408.8 231C418.1 240.4 418.2 255.6 408.8 264.9L353.8 319.9L408.8 374.9C418.2 384.3 418.2 399.5 408.8 408.8C399.4 418.1 384.2 418.2 374.9 408.8L319.9 353.8L264.9 408.8C255.5 418.2 240.3 418.2 231 408.8C221.7 399.4 221.6 384.2 231 374.9L286 319.9L231 264.9C221.6 255.5 221.6 240.3 231 231z"/></svg>
                </a>
                <div class="ac-popup-container">
                    <p><?php esc_html_e("Ta porte est ouverte aujourd'hui.", 'advent-calendar'); ?></p>
                    <h3><?php esc_html_e('Tente  ta chance et gagne une nuit magique dans une tente en bulle.', 'advent-calendar'); ?></h3>
                    <img class="tentform-img" src="<?php echo plugin_dir_url(__FILE__); ?>assets/img/tent-form.png" alt="">
                    <p><?php esc_html_e("Voici ce qu’il y a aujourd’hui dans le calendrier de l’Avent:",'advent-calendar') ?></p>
                    <p id="ac-day-specific-text" class="ac-day-specific-text"></p>
                    <h3><?php esc_html_e('Encore un petit pas :', 'advent-calendar'); ?></h3>
                    <p><?php esc_html_e('Entre tes données et participe au tirage au sort. Demain, nous te dirons si la chance était de ton côté.', 'advent-calendar'); ?></p>
                    <form id="ac-entry-form">
                        <input type="hidden" name="day" id="ac-day">
                        <p class="input-col">
                            <input type="text" name="first_name" placeholder="<?php esc_attr_e('Prénom', 'advent-calendar'); ?>" required>
                            <input type="text" name="last_name" placeholder="<?php esc_attr_e('Nom de famille', 'advent-calendar'); ?>" required>
                        </p>
                        <p>
                            <input type="email" name="email" placeholder="<?php esc_attr_e('E-Mail', 'advent-calendar'); ?>" required>
                        </p>
                        <p>
                            <button type="submit"><?php esc_html_e('SOUMETTRE', 'advent-calendar'); ?></button>
                        </p>
                    </form>
                </div>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }

    /**************************************************************************
     * Frontend rendering + enqueue
     **************************************************************************/
    public function enqueue_assets() {
        $url = plugin_dir_url(__FILE__) . 'assets/js/advent-calendar.js';
        wp_enqueue_script('advent-calendar-js', $url, ['jquery'], '1.0', true);

        wp_localize_script('advent-calendar-js', 'AdventAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('advent_nonce'),
        ]);

        wp_enqueue_style( 'advant-style', plugin_dir_url(__FILE__) . 'assets/css/style.css' , array(), '1.0', 'all' );
    }

    /**
     * Display verification messages
     */
    public function display_verification_messages() {
        $success = get_transient('ac_verification_success');
        $error   = get_transient('ac_verification_error');
        $info    = get_transient('ac_verification_info');

        if ($success) {
            echo '<div class="ac-verification-notice ac-verification-success">' . esc_html($success) . '</div>';
            delete_transient('ac_verification_success');
        }elseif ($error) {
            echo '<div class="ac-verification-notice ac-verification-error">' . esc_html($error) . '</div>';
            delete_transient('ac_verification_error');
        }elseif ($info) {
            echo '<div class="ac-verification-notice ac-verification-info">' . esc_html($info) . '</div>';
            delete_transient('ac_verification_info');
        }
    }

    /**
     * Get popup text for a specific day
     */
    private function get_day_popup_text($day_number) {
        if (!function_exists('get_field')) {
            return '';
        }

        $group = get_field("day_{$day_number}", 'option');
        return is_array($group) ? ($group['day_popup_text'] ?? '') : '';
    }

    /**
     * Check if a day should be active based on ACF settings
     */
    private function is_day_active($day_number) {
        if (!function_exists('get_field')) {
            return false;
        }

        $group = get_field("day_{$day_number}", 'option');
        
        if (!is_array($group)) {
            return false;
        }

        $enabled = !empty($group['day_enabled']);
        $selection_date = $group['day_date'] ?? '';
        
        // Check if the day is enabled AND the selection date is today
        if ($enabled && $selection_date) {
            $today = current_time('Y-m-d');
            return $selection_date === $today;
        }

        return false;
    }


    /**************************************************************************
     * AJAX handler for front-end submissions
     **************************************************************************/
    public function ajax_submit_entry() {
        check_ajax_referer('advent_nonce', 'nonce');

        $day = intval($_POST['day'] ?? 0);
        $first_name = sanitize_text_field($_POST['first_name'] ?? '');
        $last_name = sanitize_text_field($_POST['last_name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');

        if (!$day || $day < 1 || $day > 24) {
            wp_send_json_error(['message' => __('Jour sélectionné non valide.', 'advent-calendar')]);
            wp_die();
        }
        if (!$first_name || !$last_name || !$email) {
            wp_send_json_error(['message' => __('Veuillez remplir tous les champs obligatoires.', 'advent-calendar')]);
            wp_die();
        }

        // Check if day is active (enabled + date is today)
        if (!$this->is_day_active($day)) {
            wp_send_json_error(['message' => sprintf(__("Le jour %d n'est pas actif aujourd'hui.", 'advent-calendar'), $day)]);
            wp_die();
        }

        // Check for ANY entry (verified or unverified) from same email for same day
        $existing = get_posts([
            'post_type' => self::CPT,
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => '_ac_day',
                    'value' => $day,
                    'compare' => '='
                ],
                [
                    'key' => '_ac_email',
                    'value' => $email,
                    'compare' => '='
                ]
            ],
            'posts_per_page' => 1
        ]);
        
        if ($existing) {
            $existing_id = $existing[0]->ID;
            $is_verified = get_post_meta($existing_id, '_ac_email_verified', true);
            
            if ($is_verified) {
                wp_send_json_error(['message' => __('Vous avez déjà soumis une entrée vérifiée pour ce jour.', 'advent-calendar')]);
            } else {
                wp_send_json_error(['message' => __('Vous avez déjà soumis une participation pour cette journée. Veuillez vérifier votre boîte mail pour confirmer votre participation.', 'advent-calendar')]);
            }
            wp_die();
        }

        // Create the post
        $title = sprintf(__('%s %s - Day %d', 'advent-calendar'), $first_name, $last_name, $day);

        $post_id = wp_insert_post([
            'post_title' => $title,
            'post_type' => self::CPT,
            'post_status' => 'publish'
        ]);

        if (!$post_id) {
            wp_send_json_error(['message' => __("Erreur lors de la création de l'entrée.", 'advent-calendar')]);
            wp_die();
        }

        update_post_meta($post_id, '_ac_first_name', $first_name);
        update_post_meta($post_id, '_ac_last_name', $last_name);
        update_post_meta($post_id, '_ac_email', $email);
        update_post_meta($post_id, '_ac_day', $day);
        update_post_meta($post_id, '_ac_created_date', current_time('mysql'));
        update_post_meta($post_id, '_ac_is_winner', 0); // Default to not winner
        update_post_meta($post_id, '_ac_email_verified', 0); // Not verified yet
        $lang = apply_filters( 'wpml_current_language', NULL );
        update_post_meta($post_id, '_submission_language', $lang);

        // Send verification email
        $email_sent = $this->send_verification_email($post_id);

        if ($email_sent) {
            wp_send_json_success([
                'message' => __('Ta participation a été enregistrée ! Veuille à vérifier tes e-mails pour confirmer ton adresse. Tu dois confirmer ton adresse e-mail pour pouvoir prétendre au prix.', 'advent-calendar'),
                'entry_id' => $post_id
            ]);
        } else {
            wp_send_json_error(['message' => __("Votre inscription a été créée, mais nous n'avons pas pu envoyer l'e-mail de confirmation. Veuillez contacter le service d'assistance.", 'advent-calendar')]);
        }
        
        wp_die();
    }

    /**************************************************************************
     * Activation / Deactivation / Cron scheduling
     **************************************************************************/
    public function on_activation() {
        $this->schedule_next_1200();
    }

    public function on_deactivation() {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        if ($timestamp) wp_unschedule_event($timestamp, self::CRON_HOOK);
    }

    private function schedule_next_1200() {
        if (function_exists('wp_timezone')) {
            $tz = wp_timezone();
            $now = new DateTime('now', $tz);
        } else {
            $now = new DateTime('now');
        }
        $next = clone $now;
        $next->setTime(12, 00, 0); // Changed to 12:00 PM
        if ($next <= $now) $next->modify('+1 day');
        $ts = $next->getTimestamp();
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_single_event($ts, self::CRON_HOOK);
        }
    }

    /**************************************************************************
     * Date-based winner selection
     **************************************************************************/
    public function process_daily_selection() {
        // Reschedule next
        $this->schedule_next_1200();

        // Get yesterday's date to process previous day's entries
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        // $yesterday = date('Y-m-d');
        
        // Check if any day is configured for processing yesterday
        $day_to_process = null;
        $choose_winner = false;
        
        for ($i = 1; $i <= 24; $i++) {
            if (function_exists('get_field')) {
                $group = get_field("day_{$i}", 'option');
                if (is_array($group)) {
                    $selection_date = $group['day_date'] ?? '';
                    $enabled = !empty($group['day_enabled']);
                    
                    if ($enabled && $selection_date === $yesterday) {
                        $day_to_process = $i;
                        $choose_winner = !empty($group['day_choose_winner']);
                        break;
                    }
                }
            }
        }

        if (!$day_to_process) {
            error_log('Advent Calendar: No day scheduled for processing yesterday ' . $yesterday);
            return;
        }

        error_log('Advent Calendar: Processing day ' . $day_to_process . ' for date ' . $yesterday . ' - Winner selection: ' . ($choose_winner ? 'YES' : 'NO'));

        // Get VERIFIED entries for this day
        $args = [
            'post_type' => self::CPT,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => '_ac_day',
                    'value' => $day_to_process,
                    'compare' => '=',
                ],
                [
                    'key' => '_ac_email_verified',
                    'value' => '1',
                    'compare' => '=',
                ]
            ],
        ];
        $q = new WP_Query($args);
        $entries = $q->posts;
        
        if (empty($entries)) {
            error_log('Advent Calendar: No verified entries found for day ' . $day_to_process);
            return;
        }

        error_log('Advent Calendar: Found ' . count($entries) . ' verified entries for day ' . $day_to_process);

        // Get voucher codes for this day
        $vouchers = $this->get_day_vouchers($day_to_process);
        $winner_voucher = $vouchers['winner'];
        $loser_voucher = $vouchers['loser'];

        error_log('Advent Calendar: Using winner voucher: ' . $winner_voucher . ', loser voucher: ' . $loser_voucher);

        if ($choose_winner && $winner_voucher) {
            // Pick random winner
            $winner_post = $entries[array_rand($entries)];
            $winner_id = (int) $winner_post->ID;

            foreach ($entries as $entry) {
                $entry_id = (int) $entry->ID;
                if ($entry_id === $winner_id) {
                    update_post_meta($entry_id, '_ac_is_winner', 1);
                    update_post_meta($entry_id, '_ac_coupon', $winner_voucher);
                    $this->send_winner_email($entry_id, $winner_voucher);
                    error_log('Advent Calendar: Winner selected for day ' . $day_to_process . ' - Entry ID: ' . $winner_id . ' - Voucher: ' . $winner_voucher);
                } else {
                    update_post_meta($entry_id, '_ac_is_winner', 0);
                    update_post_meta($entry_id, '_ac_coupon', $loser_voucher);
                    $this->send_loser_email($entry_id, $loser_voucher);
                    error_log('Advent Calendar: Loser coupon sent for day ' . $day_to_process . ' - Entry ID: ' . $entry_id . ' - Voucher: ' . $loser_voucher);
                }
            }
        } else {
            // Only send loser coupons (no winner selection)
            foreach ($entries as $entry) {
                $entry_id = (int) $entry->ID;
                update_post_meta($entry_id, '_ac_coupon', $loser_voucher);
                $this->send_participant_email($entry_id, $loser_voucher);
                error_log('Advent Calendar: Participant coupon sent for day ' . $day_to_process . ' - Entry ID: ' . $entry_id . ' - Voucher: ' . $loser_voucher);
            }
        }

        wp_reset_postdata();
    }

    /**************************************************************************
     * Helper Methods
     **************************************************************************/
    
    /**
     * Get voucher codes for a specific day
     */
    private function get_day_vouchers($day_number) {
        if (!function_exists('get_field')) {
            return ['winner' => '', 'loser' => ''];
        }

        $group = get_field("day_{$day_number}", 'option');
        return [
            'winner' => is_array($group) ? ($group['day_winner_voucher'] ?? '') : '',
            'loser' => is_array($group) ? ($group['day_loser_voucher'] ?? '') : ''
        ];
    }

    /**************************************************************************
     * Email sending
     **************************************************************************/
    private function send_winner_email($entry_id, $coupon_code) {
        $email = get_post_meta($entry_id, '_ac_email', true);
        $first_name = get_post_meta($entry_id, '_ac_first_name', true);
        $last_name = get_post_meta($entry_id, '_ac_last_name', true);
        $name = $first_name . ' ' . $last_name;
        $subject = $this->get_winner_subject($entry_id);
        $body = $this->get_winner_email_body($entry_id, $coupon_code, $name);
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        if ($email) {
            wp_mail($email, $subject, $body, $headers);
            error_log('Advent Calendar: Winner email sent to ' . $email);
        }
    }

    private function send_loser_email($entry_id, $coupon_code) {
        $email = get_post_meta($entry_id, '_ac_email', true);
        $first_name = get_post_meta($entry_id, '_ac_first_name', true);
        $last_name = get_post_meta($entry_id, '_ac_last_name', true);
        $name = $first_name . ' ' . $last_name;
        $subject = $this->get_loser_subject($entry_id);
        $body = $this->get_loser_email_body($entry_id, $coupon_code, $name);
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        if ($email) wp_mail($email, $subject, $body, $headers);
    }

    private function send_participant_email($entry_id, $coupon_code) {
        $email = get_post_meta($entry_id, '_ac_email', true);
        $first_name = get_post_meta($entry_id, '_ac_first_name', true);
        $last_name = get_post_meta($entry_id, '_ac_last_name', true);
        $name = $first_name . ' ' . $last_name;
        $subject = $this->get_participant_subject($entry_id);
        $body = $this->get_participant_email_body($entry_id, $coupon_code, $name);
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        if ($email) wp_mail($email, $subject, $body, $headers);
    }

    private function get_winner_subject($entry_id) { 
        $language = get_post_meta($entry_id, '_submission_language', true);

        $subject = '🎉 Vous êtes le gagnant du jour !';
        
        return apply_filters('ac_winner_email_subject', $subject); 
    }
    
    private function get_loser_subject($entry_id) { 
        $language = get_post_meta($entry_id, '_submission_language', true);
        
        $subject = "Merci d'avoir participé à notre calendrier de l'Avent !";


        
        return apply_filters('ac_loser_email_subject', $subject); 
    }

    private function get_participant_subject($entry_id) { 
        $language = get_post_meta($entry_id, '_submission_language', true);
        
        $subject = "Merci d'avoir participé à notre calendrier de l'Avent !";


        
        return apply_filters('ac_participant_email_subject', $subject); 
    }

    private function get_winner_email_body($entry_id, $coupon_code, $name = '') {

        $first_name = get_post_meta($entry_id, '_ac_first_name', true);
        $language = get_post_meta($entry_id, '_submission_language', true);

        $expiry = date('d.m.Y', strtotime('+30 days'));
        $date_raw = get_post_meta($entry_id, '_ac_created_date', true);
        $ac_day = get_post_meta($entry_id, '_ac_day', true);
        $date = date_create($date_raw);
        $formatted_date = date_format($date, 'j F Y');
        
        $prize_description = $this->get_day_popup_text($ac_day);
        

        ob_start();

        // Path of template
        $template = __DIR__ . '/email/winner-email-fr.php';

        if (file_exists($template)) {
            include $template;
        } else {
            echo 'Email template not found!';
        }

        $body = ob_get_clean();

        return apply_filters('ac_winner_email_body', $body, $entry_id, $coupon_code);
    }

    private function get_loser_email_body($entry_id, $coupon_code, $name = '') {
        $first_name = get_post_meta($entry_id, '_ac_first_name', true);
        $language = get_post_meta($entry_id, '_submission_language', true);

        $expiry = date('d.m.Y', strtotime('+30 days'));
        $date_raw = get_post_meta($entry_id, '_ac_created_date', true);
        $date = date_create($date_raw);
        $formatted_date = date_format($date, 'j F Y');

        ob_start();

        $template = __DIR__ . '/email/loser-email-fr.php';


        if (file_exists($template)) {
            include $template;
        } else {
            echo 'Email template not found!';
        }

        $body = ob_get_clean();

        return apply_filters('ac_loser_email_body', $body, $entry_id, $coupon_code);
    }

    private function get_participant_email_body($entry_id, $coupon_code, $name = '') {
        $first_name = get_post_meta($entry_id, '_ac_first_name', true);
        $language = get_post_meta($entry_id, '_submission_language', true);

        $expiry = date('d.m.Y', strtotime('+30 days'));
        $date_raw = get_post_meta($entry_id, '_ac_created_date', true);
        $date = date_create($date_raw);
        $formatted_date = date_format($date, 'j F Y');

        ob_start();

        // Path of template
        $template = __DIR__ . '/email/loser-email-fr.php';


        if (file_exists($template)) {
            include $template;
        } else {
            echo 'Email template not found!';
        }

        $body = ob_get_clean();

        return apply_filters('ac_participant_email_body', $body, $entry_id, $coupon_code);
    }
}

// instantiate
new Advent_Calendar_Manager();