<?php

namespace YayMail\Integrations\CustomOrderStatusManagerByBrightPlugins\Emails;

use YayMail\Abstracts\BaseEmail;
use YayMail\Utils\SingletonTrait;

/**
 * EmailsHandler Class
 *
 * @method static EmailsHandler get_instance()
 */
class EmailsHandler {
    use SingletonTrait;

    private $emails = [];

    public function add_email( $status_slug, $email ) {
        if ( ! $email instanceof BaseEmail ) {
            return;
        }
        $this->emails[ $status_slug ] = $email;
    }

    public function get_emails() {
        return $this->emails;
    }

    public function get_list_id() {
        return array_map( function ( $email ) {
            return $email->get_id();
        }, array_values( $this->emails ) );
    }

    public function get_email_by_status_slug( $status_slug ) {
        return $this->emails[ $status_slug ] ?? null;
    }
}
