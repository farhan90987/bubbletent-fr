<?php

namespace YayMail\Integrations\AdvancedShipmentTrackingByZorem\Emails;

use YayMail\Abstracts\BaseEmail;
use YayMail\Elements\ElementsLoader;
use YayMail\Utils\SingletonTrait;

/**
 * CustomerPartialShippedOrder Class
 *
 * @method static CustomerPartialShippedOrder get_instance()
 */
class CustomerPartialShippedOrder extends BaseEmail {
    use SingletonTrait;

    public $template = null;

    protected function __construct() {
        $emails = \WC_Emails::instance()->get_emails();

        if ( empty( $emails['WC_Email_Customer_Partial_Shipped_Order'] ) ) {
            return;
        }
        $email            = $emails['WC_Email_Customer_Partial_Shipped_Order'];
        $this->id         = $email->id;
        $this->title      = $email->get_title();
        $this->root_email = $email;
        $this->recipient  = function_exists( 'yaymail_get_email_recipient_zone' ) ? yaymail_get_email_recipient_zone( $email ) : '';

        $this->render_priority = apply_filters( 'yaymail_email_render_priority', 10, $this->id );
        add_filter( 'wc_get_template', [ $this, 'get_template_file' ], $this->render_priority ?? 10, 3 );
    }

    public function get_default_elements() {
        $email_title = __( 'Your Order is Partially Shipped', 'woocommerce' );
        // translators: customer name.
        $email_hi        = sprintf( esc_html__( 'Hi %s,', 'woocommerce' ), '[yaymail_billing_first_name]' );
        $email_text      = esc_html__( 'Hi there. we thought you\'d like to know that your recent order from ', 'woocommerce' ) . esc_html( do_shortcode( '[yaymail_site_name]' ) ) . esc_html__( ' has been partially shipped.', 'woocommerce' );
        $additional_text = __( 'We look forward to fulfilling your order soon.', 'woocommerce' );

        $default_elements = ElementsLoader::load_elements(
            [
                [
                    'type' => 'Logo',
                ],
                [
                    'type'       => 'Heading',
                    'attributes' => [
                        'rich_text' => $email_title,
                    ],
                ],
                [
                    'type'       => 'Text',
                    'attributes' => [
                        'rich_text' => '<p><span>' . $email_hi . '<br /><br /></span></p><p><span>' . $email_text . '</span></p>',
                    ],
                ],
                [
                    'type'        => 'AdvancedShipmentTrackingByZorem\Elements\TrackingInformationElement',
                    'integration' => '3rd',
                ],
                [
                    'type' => 'OrderDetails',
                ],
                [
                    'type' => 'BillingShippingAddress',
                ],
                [
                    'type'       => 'Text',
                    'attributes' => [
                        'rich_text' => '<p><span>' . $additional_text . '</span></p>',
                        'padding'   => [
                            'top'    => '0',
                            'right'  => '50',
                            'bottom' => '38',
                            'left'   => '50',
                        ],
                    ],
                ],
                [
                    'type' => 'Footer',
                ],
            ]
        );

        return $default_elements;
    }

    public function get_template_path() {
        return YAYMAIL_PLUGIN_PATH . 'src/Integrations/AdvancedShipmentTrackingByZorem/Templates/Emails/customer-partial-shipped-order.php';
    }
}
