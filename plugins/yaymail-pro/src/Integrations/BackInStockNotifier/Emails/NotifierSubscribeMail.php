<?php

namespace YayMail\Integrations\BackInStockNotifier\Emails;

use YayMail\Abstracts\BaseEmail;
use YayMail\Elements\ElementsLoader;
use YayMail\Utils\SingletonTrait;
use YayMail\YayMailTemplate;

/**
 * NotifierSubscribeMail Class
 *
 * @method static NotifierSubscribeMail get_instance()
 */
class NotifierSubscribeMail extends BaseEmail {
    use SingletonTrait;

    public $email_types = [ YAYMAIL_NON_ORDER_EMAILS ];

    protected function __construct() {
        $this->id        = 'notifier_subscribe_mail';
        $this->title     = __( 'Notifier Subscribe Mail', 'yaymail' );
        $this->recipient = __( 'Customer', 'woocommerce' );
        $this->source    = [
            'plugin_id'   => 'back-in-stock-notifier',
            'plugin_name' => 'Back In Stock Notifier',
        ];

        add_filter( 'cwgsubscribe_message', [ $this, 'cwgsubscribe_message' ], 100, 2 );
        add_action( 'cwginstock_after_insert_subscriber', [ $this, 'remove_default_header_footer' ], 9 );
    }

    public function get_default_elements() {
        $email_title = __( 'You subscribed to [yaymail_notifier_product_name] at [yaymail_notifier_shopname]', 'woocommerce' );
        $email_hi    = __( 'Dear [yaymail_notifier_subscriber_name] ,', 'woocommerce' );
        $email_text  = __( 'Thank you for subscribing to the #[yaymail_notifier_product_name]. We will email you once product back in stock', 'woocommerce' );

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
                        'rich_text' => '<p><span>' . $email_hi . '</span></p><p><span>' . $email_text . '</span></p>',
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

    public function cwgsubscribe_message( $message, $subscriber_id ) {
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
        return YAYMAIL_PLUGIN_PATH . 'src/Integrations/BackInStockNotifier/Templates/Emails/notifier-subscribe-mail.php';
    }
}
