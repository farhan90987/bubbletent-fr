<?php

namespace YayMail\Integrations\WooCommerceOrderStatusManagerBySkyVer\Emails;

use YayMail\Abstracts\BaseEmail;
use YayMail\Elements\ElementsLoader;
use YayMail\Utils\SingletonTrait;

/**
 * WooCommerceOrderStatusManagerBySkyVer Class
 *
 * @method static WooCommerceOrderStatusManagerBySkyVer get_instance()
 */
class WooCommerceOrderStatusManagerBySkyVer extends BaseEmail {

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
        $heading = __( 'Order has been updated', 'woocommerce-order-status-manager' );

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
                        'rich_text' => '<p><span>' . __( 'Your order is now [yaymail_order_status]. Order details are as follows:', 'yaymail' ) . '</span></p>',
                    ],
                ],
                [
                    'type' => 'OrderDetails',
                ],
                [
                    'type'       => 'Title',
                    'attributes' => [
                        'title'      => __( 'Customer details', 'wc-vendors' ),
                        'align'      => 'left',
                        'text_color' => YAYMAIL_COLOR_WC_DEFAULT,
                        'subtitle'   => '',
                        'padding'    => [
                            'top'    => 15,
                            'bottom' => 0,
                            'left'   => 50,
                            'right'  => 50,
                        ],
                        'title_size' => '20px',
                    ],
                ],
                [
                    'type'       => 'Text',
                    'attributes' => [
                        'padding'   => [
                            'top'    => 15,
                            'bottom' => 0,
                            'left'   => 50,
                            'right'  => 50,
                        ],
                        'rich_text' => '<div style=\"margin: 0px;\"><span style=\"font-size: 14px;\"><b>' . __( 'Email', 'wc-vendors' ) . ':</b> [yaymail_billing_email]</div>',
                    ],
                ],
                [
                    'type'       => 'Text',
                    'attributes' => [
                        'padding'   => [
                            'top'    => 15,
                            'bottom' => 0,
                            'left'   => 50,
                            'right'  => 50,
                        ],
                        'rich_text' => '<div style=\"margin: 0px;\"><span style=\"font-size: 14px;\"><b>' . __( 'Tel', 'wc-vendors' ) . ':</b> [yaymail_billing_phone]</div>',
                    ],
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

    public function get_template_path() {
        return YAYMAIL_PLUGIN_PATH . 'src/Integrations/WooCommerceOrderStatusManagerBySkyVer/Templates/Emails/custom-order-status.php';
    }
}
