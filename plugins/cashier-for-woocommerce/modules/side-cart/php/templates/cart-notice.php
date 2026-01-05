<?php
/**
 * Sidecart: Cart Notice
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cashier/sidecart/cart-notice.php.
 *
 * HOWEVER, on occasion we will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen.
 *
 * @version 1.1.0
 * @package Sidecart/Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Buffer output.
ob_start();

foreach ( $notice_types as $notice_type ) {
	if ( wc_notice_count( $notice_type ) > 0 ) {
		$messages = '';

		foreach ( $notices[ $notice_type ] as $notice ) {
			$messages .= sprintf( '<div class="cfw-msg">%s</div>', isset( $notice['notice'] ) ? $notice['notice'] : $notice );
		}

		printf( '<div class="cfw-cart-notice cfw-%1$s"><span>%2$s</span></div>', esc_attr( $notice_type ), wp_kses( $messages, wp_kses_allowed_html( 'post' ) ) );
	}
}

wc_clear_notices();
