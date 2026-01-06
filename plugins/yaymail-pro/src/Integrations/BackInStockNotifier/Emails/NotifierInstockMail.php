<?php

namespace YayMail\Integrations\BackInStockNotifier\Emails;

use YayMail\Abstracts\BaseEmail;
use YayMail\Elements\ElementsLoader;
use YayMail\Utils\SingletonTrait;
use YayMail\YayMailTemplate;

/**
 * NotifierInstockMail Class
 *
 * @method static NotifierInstockMail get_instance()
 */
class NotifierInstockMail extends BaseEmail {
    use SingletonTrait;

    public $email_types = [ YAYMAIL_NON_ORDER_EMAILS ];

    protected function __construct() {
        $this->id        = 'notifier_instock_mail';
        $this->title     = __( 'Notifier Instock Mail', 'yaymail' );
        $this->recipient = __( 'Customer', 'woocommerce' );
        $this->source    = [
            'plugin_id'   => 'back-in-stock-notifier',
            'plugin_name' => 'Back In Stock Notifier',
        ];

        add_filter( 'cwginstock_message', [ $this, 'cwginstock_message' ], 100, 2 );
        add_action( 'admin_action_cwginstock-sendmail', [ $this, 'remove_default_header_footer' ], 9 );
        add_action( 'cwginstock_notify_process', [ $this, 'remove_default_header_footer' ], 9 );
        add_action( 'cwginstocknotifier_handle_action_send_mail', [ $this, 'remove_default_header_footer' ], 9 );
    }

    public function get_default_elements() {
        $email_title  = __( 'Product [yaymail_notifier_product_name] is back in stock', 'woocommerce' );
        $email_hi     = __( 'Hello [yaymail_notifier_subscriber_name],', 'woocommerce' );
        $email_text_1 = __( 'Thanks for your patience and finally the wait is over! ', 'woocommerce' );
        $email_text_2 = __( 'Your Subscribed Product [yaymail_notifier_product_name] is now back in stock! ', 'woocommerce' );
        $email_text_3 = __( 'We only have a limited amount of stock, and this email is not a guarantee you\'ll get one, so hurry to be one of the lucky shoppers who do Add this product [yaymail_notifier_product_name] directly to your cart [yaymail_notifier_cart_link]', 'woocommerce' );

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
                        'rich_text' => '<p><span>' . $email_hi . '</span></p><p><span>' . $email_text_1 . '</span></p><p><span>' . $email_text_2 . '</span></p><p><span>' . $email_text_3 . '</span></p>',
                    ],
                ],
                [
                    'type' => 'Footer',
                ],
            ]
        );

        return $default_elements;
    }

    public function remove_default_header_footer() {
        $template = new YayMailTemplate( $this->id );

        if ( empty( $template ) ) {
            return;
        }

        if ( ! $template->is_enabled() ) {
            return;
        }
        $emails = \WC_Emails::instance();
        remove_action( 'woocommerce_email_header', [ $emails, 'email_header' ] );
        remove_action( 'woocommerce_email_footer', [ $emails, 'email_footer' ] );
    }

    public function cwginstock_message( $message, $subscriber_id ) {
        $template_path = $this->get_template_path();

        if ( ! file_exists( $template_path ) ) {
            return $message;
        }

        $template = new YayMailTemplate( $this->id );

        if ( empty( $template ) ) {
            return $message;
        }

        if ( ! $template->is_enabled() ) {
            return $message;
        }

        $render_data['subscriber_id'] = $subscriber_id;

        $html = $template->get_content( $render_data );

        return yaymail_kses_post_e( $html );
    }

    public function get_template_path() {
        return YAYMAIL_PLUGIN_PATH . 'src/Integrations/BackInStockNotifier/Templates/Emails/notifier-instock-mail.php';
    }
}
