<?php

defined( 'ABSPATH' ) || exit;

use YayMail\Integrations\WooCommerceOrderStatusByTycheSoftwares\Emails\EmailsHandler;

$custom_status_order = isset( $args['order'] ) ? $args['order'] : null;

$email_id = isset( $args['email_id'] ) ? $args['email_id'] : null;

$email_instance = EmailsHandler::get_instance()->get_email_by_id( $email_id );

if ( ! empty( $email_instance ) ) {
    $template = property_exists( $email_instance, 'template' ) ? $email_instance->template : '';
}

if ( ! empty( $template ) ) {
    $content = $template->get_content( $args );
    yaymail_kses_post_e( $content );
}
