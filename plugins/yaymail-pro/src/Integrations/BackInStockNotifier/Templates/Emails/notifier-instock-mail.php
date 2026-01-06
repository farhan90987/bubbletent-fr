<?php

use YayMail\Integrations\BackInStockNotifier\Emails\NotifierInstockMail;

defined( 'ABSPATH' ) || exit;

$template = NotifierInstockMail::get_instance()->template;

if ( ! empty( $template ) ) {
    $content = $template->get_content( $args );
    // TODO: process args later.
    yaymail_kses_post_e( $content );
}
