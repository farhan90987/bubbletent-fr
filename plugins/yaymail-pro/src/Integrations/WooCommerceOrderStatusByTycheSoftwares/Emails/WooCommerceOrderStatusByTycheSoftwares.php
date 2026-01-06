<?php

namespace YayMail\Integrations\WooCommerceOrderStatusByTycheSoftwares\Emails;

use YayMail\Abstracts\BaseEmail;
use YayMail\Elements\ElementsLoader;
use YayMail\Utils\SingletonTrait;
use YayMail\YayMailTemplate;

/**
 * WooCommerceOrderStatusByTycheSoftwares Class
 *
 * @method static WooCommerceOrderStatusByTycheSoftwares get_instance()
 */
class WooCommerceOrderStatusByTycheSoftwares extends BaseEmail {

    public $template = null;

    const LIST_UNAVAILABLE_ELEMENTS = [
        'order_details_download',
    ];

    public function __construct( $email ) {
        $this->id         = $email->id;
        $this->title      = $email->title;
        $this->root_email = $email;
        $this->recipient  = function_exists( 'yaymail_get_email_recipient_zone' ) ? yaymail_get_email_recipient_zone( $email ) : '';

        // TODO: check this filter. Does the email send twice?
        add_filter( 'woocommerce_order_status_changed', [ $this, 'send_email_on_order_status' ], 10, 4 );
    }

    public function get_default_elements() {
        $heading = sprintf( __( '%1$s: #%2$s', 'yaymail' ), $this->title, '[yaymail_order_id is_plain="true"]' );

        $default_elements = ElementsLoader::load_elements(
            [
                [
                    'type' => 'Logo',
                ],
                [
                    'type'       => 'Heading',
                    'attributes' => [
                        'rich_text' => '<h1 style="font-size: 30px; font-weight: 300; line-height: normal; margin: 0px; color: inherit; text-align: left;">' . $heading . '</h1>',
                    ],
                ],
                [
                    'type'       => 'Text',
                    'attributes' => [
                        'rich_text' => '<p><span>' . __( 'Status changed from [yaymail_order_status_from] to [yaymail_order_status]', 'yaymail' ) . '</span></p>',
                    ],
                ],
                [
                    'type' => 'OrderDetails',
                ],
                [
                    'type' => 'BillingShippingAddress',
                ],
                [
                    'type' => 'Footer',
                ],
            ]
        );

        return $default_elements;
    }


    public function send_email_on_order_status( $order_id, $status_from, $status_to, $order ) {
        $alg_orders_custom_statuses_array = alg_get_custom_order_statuses_from_cpt();

        $emails_statuses = get_option( 'alg_orders_custom_statuses_emails_statuses', [] );

        $is_status_changed = in_array( 'wc-' . $status_to, $emails_statuses, true ) || ( empty( $emails_statuses ) && in_array( 'wc-' . $status_to, array_keys( $alg_orders_custom_statuses_array ), true ) );

        if ( ! $is_status_changed || 'wc-' . $status_to !== $this->id ) {
            return;
        }

        $args = [
            'order'       => $order,
            'status_from' => $status_from,
            'status_to'   => $status_to,
        ];

        $order = apply_filters( 'yaymail_order_for_language', isset( $order ) ? $order : null, $args );

        $language = $this->get_language( $order );

        $this->template = new YayMailTemplate( $this->id, $language );

        if ( ! $this->template->is_enabled() ) {
            return;
        }

        global $wp_filter;
        $action = isset( $wp_filter['woocommerce_order_status_changed'] ) ? $wp_filter['woocommerce_order_status_changed']->callbacks : [];
        if ( ! empty( $action ) ) {

            foreach ( $action as $key => $value ) {
                foreach ( $value as $key1 => $value1 ) {
                    // TODO: if hell
                    if ( is_array( $value1['function'] ) && isset( $value1['function']['1'] ) ) {
                        if ( 'send_email_on_order_status_changed' === $value1['function']['1'] ) {
                            remove_action( 'woocommerce_order_status_changed', $key1, PHP_INT_MAX, 4 );
                        }
                    }
                }
            }
        }

        $alg_orders_custom_statuses_array          = alg_get_custom_order_statuses_from_cpt();
        $alg_orders_custom_statuses_with_id_array  = alg_get_custom_order_statuses_from_cpt( true, true );
        $email_address                             = '';
        $bcc_email_address                         = '';
        $email_subject                             = '';
        $email_heading                             = '';
        $emails_statuses                           = get_option( 'alg_orders_custom_statuses_emails_statuses', [] );
        $is_global_emails_enabled                  = get_option( 'alg_orders_custom_statuses_emails_enabled', 'no' );
        $alg_send_emails                           = false;
        $alg_orders_custom_statuses_emails_enabled = '';

        if ( 'yes' === $is_global_emails_enabled ) {
            if ( in_array( 'wc-' . $status_to, $emails_statuses, true ) || ( in_array( 'wc-' . $status_to, array_keys( $alg_orders_custom_statuses_array ), true ) ) ) {
                $alg_send_emails = true;
                // Options.
                $email_address     = get_option( 'alg_orders_custom_statuses_emails_address', '' );
                $bcc_email_address = get_option( 'alg_orders_custom_statuses_bcc_emails_address', '' );
                $email_subject     = get_option(
                    'alg_orders_custom_statuses_emails_subject',
                    // translators: WC Order Number, New Status & Date on which the order was placed.
                    sprintf( __( '[%1$s] Order #%2$s status changed to %3$s - %4$s', 'custom-order-statuses-woocommerce' ), '{site_title}', '{order_number}', '{status_to}', '{order_date}' )
                );
                $email_heading = get_option(
                    'alg_orders_custom_statuses_emails_heading',
                    /* translators: $s: status to */
                    sprintf( __( 'Order status changed to %s', 'custom-order-statuses-woocommerce' ), '{status_to}' )
                );
            }
        }
        // For the emails set at custom status level(Individual level).
        // If hell
        if ( ! empty( $alg_orders_custom_statuses_with_id_array ) ) {
            // Get custom meta box values of custom post status.
            if ( isset( $alg_orders_custom_statuses_with_id_array[ $status_to ] ) ) {
                $status_post_id                            = $alg_orders_custom_statuses_with_id_array[ $status_to ];
                $alg_orders_custom_statuses_emails_enabled = get_post_meta( $status_post_id, 'alg_orders_custom_statuses_emails_enabled', true );
                if ( $status_post_id > 0 && 'yes' === $alg_orders_custom_statuses_emails_enabled ) {
                    $alg_send_emails = true;
                    if ( ! empty( get_post_meta( $status_post_id, 'alg_orders_custom_statuses_emails_address', true ) ) ) {
                        $email_address = get_post_meta( $status_post_id, 'alg_orders_custom_statuses_emails_address', true );
                    }
                    if ( ! empty( get_post_meta( $status_post_id, 'alg_orders_custom_statuses_bcc_emails_address', true ) ) ) {
                        $bcc_email_address = get_post_meta( $status_post_id, 'alg_orders_custom_statuses_bcc_emails_address', true );
                    }
                    if ( ! empty( get_post_meta( $status_post_id, 'alg_orders_custom_statuses_emails_subject', true ) ) ) {
                        $email_subject = get_post_meta( $status_post_id, 'alg_orders_custom_statuses_emails_subject', true );
                    }
                    if ( ! empty( get_post_meta( $status_post_id, 'alg_orders_custom_statuses_emails_heading', true ) ) ) {
                        $email_heading = get_post_meta( $status_post_id, 'alg_orders_custom_statuses_emails_heading', true );
                    }
                }
            }
        }//end if

        if ( 'yes' !== $is_global_emails_enabled && 'yes' !== $alg_orders_custom_statuses_emails_enabled ) {
            return;
        }

        $woo_statuses        = wc_get_order_statuses();
        $replace_status_from = isset( $alg_orders_custom_statuses_array[ 'wc-' . $status_from ] ) ? $alg_orders_custom_statuses_array[ 'wc-' . $status_from ] : $woo_statuses[ 'wc-' . $status_from ];
        $replace_status_to   = isset( $alg_orders_custom_statuses_array[ 'wc-' . $status_to ] ) ? $alg_orders_custom_statuses_array[ 'wc-' . $status_to ] : $woo_statuses[ 'wc-' . $status_to ];

        // Replaced values.
        $replaced_values = [
            '{order_id}'         => $order_id,
            '{order_number}'     => $order->get_order_number(),
            '{order_date}'       => gmdate( get_option( 'date_format' ), strtotime( $order->get_date_created() ) ),
            '{site_title}'       => wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ),
            '{status_from}'      => $replace_status_from,
            '{status_to}'        => $replace_status_to,
            '{first_name}'       => $order->get_billing_first_name(),
            '{last_name}'        => $order->get_billing_last_name(),
            '{billing_address}'  => $order->get_formatted_billing_address(),
            '{shipping_address}' => $order->get_formatted_shipping_address(),
        ];

        $email_replaced_values = [
            '{customer_email}' => $order->get_billing_email(),
            '{admin_email}'    => get_option( 'admin_email' ),
        ];

        // Final processing.
        $email_address = ( '' === $email_address ? get_option( 'admin_email' ) : str_replace( array_keys( $email_replaced_values ), $email_replaced_values, $email_address ) );
        $email_subject = do_shortcode( str_replace( array_keys( $replaced_values ), $replaced_values, $email_subject ) );
        $email_heading = do_shortcode( str_replace( array_keys( $replaced_values ), $replaced_values, $email_heading ) );
        $headers       = [];
        $headers[]     = 'Content-Type: text/html; charset=UTF-8';
        $headers[]     = 'From: Owner <owner@owner.com>';
        $bcc_to        = [];
        if ( ! empty( $bcc_email_address ) ) {
            $bcc_to = explode( ', ', $bcc_email_address );
            if ( ! empty( $bcc_to ) ) {
                foreach ( $bcc_to as $email ) {
                    $headers[] = 'Bcc: ' . $email;
                }
            }
        }
        // Content
        $args = [
            'email_heading'       => $email_heading,
            'custom_order_status' => true,
            'order'               => $order,
            'sent_to_admin'       => false,
            'status_from'         => $status_from,
            'email_id'            => $this->id,
        ];

        $template_path = $this->get_template_path();

        if ( ! file_exists( $template_path ) ) {
            return;
        }

        ob_start();
        include $template_path;
        $html = ob_get_contents();
        ob_end_clean();

        // Send mail.
        if ( $alg_send_emails ) {
            wc_mail( $email_address, strval( $email_subject ), $html, $headers );
        }
    }

    public function get_template_path() {
        return YAYMAIL_PLUGIN_PATH . 'src/Integrations/WooCommerceOrderStatusByTycheSoftwares/Templates/Emails/custom-order-status.php';
    }
}
