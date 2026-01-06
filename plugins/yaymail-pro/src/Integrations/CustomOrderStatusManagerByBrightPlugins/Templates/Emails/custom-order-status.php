<?php

defined( 'ABSPATH' ) || exit;

use YayMail\Integrations\CustomOrderStatusManagerByBrightPlugins\Emails\EmailsHandler;

$custom_status_order = isset( $args['order'] ) ? $args['order'] : null;

$order_data = method_exists( $custom_status_order, 'get_data' ) ? $custom_status_order->get_data() : null;

$custom_status = isset( $order_data['status'] ) ? $order_data['status'] : null;

$email_instance = EmailsHandler::get_instance()->get_email_by_status_slug( 'bvos_custom_' . $custom_status );

if ( ! empty( $email_instance ) ) {
    $template = property_exists( $email_instance, 'template' ) ? $email_instance->template : '';
}

if ( ! empty( $template ) ) {
    $content = $template->get_content( $args );
    yaymail_kses_post_e( $content );
}
