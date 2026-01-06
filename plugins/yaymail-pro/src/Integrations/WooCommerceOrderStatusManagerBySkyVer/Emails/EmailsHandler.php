<?php

namespace YayMail\Integrations\WooCommerceOrderStatusManagerBySkyVer\Emails;

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

    public function add_email( $email ) {
        if ( ! $email instanceof BaseEmail ) {
            return;
        }
        $this->emails[] = $email;
    }

    public function get_emails() {
        return $this->emails;
    }

    public function get_list_id() {
        return array_map( function ( $email ) {
            return $email->get_id();
        }, $this->emails );
    }

    public function get_email_by_id( $id ) {
        foreach ( $this->emails as $email ) {
            if ( $email->get_id() === $id ) {
                return $email;
            }
        }
        return null;
    }
}
