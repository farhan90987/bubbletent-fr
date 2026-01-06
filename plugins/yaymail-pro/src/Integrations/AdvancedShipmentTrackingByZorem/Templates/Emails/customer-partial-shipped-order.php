<?php

use YayMail\Integrations\AdvancedShipmentTrackingByZorem\Emails\CustomerPartialShippedOrder;

defined( 'ABSPATH' ) || exit;

$template = CustomerPartialShippedOrder::get_instance()->template;

if ( ! empty( $template ) ) {
    $content = $template->get_content( $args ); // TODO: process args later.
    yaymail_kses_post_e( $content );
}
