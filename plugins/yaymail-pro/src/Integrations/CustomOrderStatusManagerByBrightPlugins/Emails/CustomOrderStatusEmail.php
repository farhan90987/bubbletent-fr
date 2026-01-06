<?php

namespace YayMail\Integrations\CustomOrderStatusManagerByBrightPlugins\Emails;

use YayMail\Abstracts\BaseEmail;
use YayMail\Elements\ElementsLoader;
use YayMail\YayMailTemplate;
use YayMail\Utils\SingletonTrait;

/**
 * CustomOrderStatusEmail Class
 *
 * @method static CustomOrderStatusEmail get_instance()
 */
class CustomOrderStatusEmail extends BaseEmail {

    public $template = null;

    const LIST_UNAVAILABLE_ELEMENTS = [
        'order_details_download',
    ];

    public function __construct( $email ) {
        $this->id         = $email->id;
        $this->title      = $email->get_title();
        $this->root_email = $email;
        $this->recipient  = function_exists( 'yaymail_get_email_recipient_zone' ) ? yaymail_get_email_recipient_zone( $email ) : '';

        $this->render_priority = apply_filters( 'yaymail_email_render_priority', 10, $this->id );
        add_filter( 'wc_get_template', [ $this, 'get_template_file' ], $this->render_priority ?? 10, 3 );
    }

    public function get_default_elements() {
        $heading          = __( 'Order status changed to {order_status}', 'bp-custom-order-status' );
        $heading          = str_replace( '{order_status}', $this->title, $heading );
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

    public function get_template_file( $located, $template_name, $args ) {
        if ( ! isset( $args['email'] ) ) {
            return $located;
        }
        $template_path = $this->get_template_path();
        if ( ! file_exists( $template_path ) ) {
            return $located;
        }

        $language = $this->get_language( isset( $args['order'] ) ? $args['order'] : null );

        $order = isset( $args['order'] ) ? $args['order'] : null;
        if ( empty( $order ) ) {
            return $located;
        }

        $data = method_exists( $order, 'get_data' ) ? $order->get_data() : null;
        if ( empty( $data ) ) {
            return $located;
        }

        $custom_order_status = $data['status'];
        if ( empty( $custom_order_status ) ) {
            return $located;
        }

        $instance = EmailsHandler::get_instance()->get_email_by_status_slug( 'bvos_custom_' . $custom_order_status );

        if ( empty( $instance ) || empty( $instance->id ) ) {
            return $located;
        }

        $this->template = new YayMailTemplate( $instance->id, $language );

        if ( ! $this->template->is_enabled() ) {
            return $located;
        }

        return $template_path;
    }

    public function get_template_path() {
        return YAYMAIL_PLUGIN_PATH . 'src/Integrations/CustomOrderStatusManagerByBrightPlugins/Templates/Emails/custom-order-status.php';
    }
}
