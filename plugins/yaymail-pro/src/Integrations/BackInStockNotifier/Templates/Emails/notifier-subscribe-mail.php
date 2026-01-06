<?php

use YayMail\Integrations\BackInStockNotifier\Emails\NotifierSubscribeMail;

defined( 'ABSPATH' ) || exit;

$template = NotifierSubscribeMail::get_instance()->template;

if ( ! empty( $template ) ) {
    $content = $template->get_content( $args );
    // TODO: process args later.
    yaymail_kses_post_e( $content );
}
