<?php
/**
 * Buyer email template.
 */

defined( 'ABSPATH' ) || exit;

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email );

/**
 * Settings defined email body.
 */
if ( $email_body ) {
	echo wp_kses_post( wpautop( wptexturize( $email_body ) ) );
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
