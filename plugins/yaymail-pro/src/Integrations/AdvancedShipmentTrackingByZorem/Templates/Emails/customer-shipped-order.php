<?php

use YayMail\Integrations\AdvancedShipmentTrackingByZorem\Emails\CustomerShippedOrder;

defined( 'ABSPATH' ) || exit;

$template = CustomerShippedOrder::get_instance()->template;

if ( ! empty( $template ) ) {
    $content = $template->get_content( $args );
    // TODO: process args later.
    yaymail_kses_post_e( $content );
}
