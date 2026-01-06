<?php
/**
 * Email z kuponem (plain text)
 *
 * @var WC_Order                                                                $order
 * @var string                                                                  $email_heading
 * @var \FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Data\Email\EmailMeta $meta
 * @var bool                                                                    $email
 * @var int                                                                     $order_number
 * @var string                                                                  $date_order
 * @var bool                                                                    $sent_to_admin
 * @var bool                                                                    $plain_text
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

echo \esc_html( $email_heading ) . "\n\n";

echo '****************************************************';

echo PHP_EOL . PHP_EOL;

/* translators: %s: Recipient name. */
printf( esc_html__( 'Hi %s,', 'flexible-coupons-pro' ), esc_html( $meta->get_recipient_name() ) );

echo PHP_EOL . PHP_EOL;

printf(
	/* translators: %1$s: Recipient name, %2$s: Site URL, %3$s: Site name, %4$s: Expiry date. */
	esc_html__( 'Thanks to %1$s you get a gift voucher for use in the %2$s (%3$s) store. The coupon is valid to %4$s.', 'flexible-coupons-pro' ),
	esc_html( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
	esc_url( get_site_url() ),
	esc_html( get_bloginfo( 'name' ) ),
	esc_html( $meta->get_coupon_expiry() )
);

echo PHP_EOL . PHP_EOL;

foreach ( $meta->get_coupons_array() as $coupon ) {
	/* translators: %s: Download link. */
	printf( esc_html__( 'Download PDF with the coupon from: %s', 'flexible-coupons-pro' ), esc_url( $coupon['coupon_url'] ) );

	echo PHP_EOL . PHP_EOL;

	echo esc_html__( 'Coupon information', 'flexible-coupons-pro' ) . PHP_EOL;
	if ( isset( $coupon['coupon_code'] ) ) {
		/* translators: %s: Coupon code. */
		printf( esc_html__( 'Coupon code: %s', 'flexible-coupons-pro' ), esc_html( $coupon['coupon_code'] ) );
		echo PHP_EOL;
		/* translators: %s: Coupon value. */
		printf( esc_html__( 'Coupon value: %s', 'flexible-coupons-pro' ), esc_html( $coupon['coupon_value'] ) );
		echo PHP_EOL;
		if ( ! empty( $coupon['coupon_expiry'] ) ) {
			/* translators: %s: Expiry date. */
			printf( esc_html__( 'Expiry date: %s', 'flexible-coupons-pro' ), esc_html( $coupon['coupon_expiry'] ) );
		}
	}
}
echo PHP_EOL . PHP_EOL;

if ( ! empty( $meta->get_recipient_message() ) ) {
	esc_html_e( 'A message from the buyer:', 'flexible-coupons-pro' );
	echo esc_html( $meta->get_recipient_message() );
}

echo PHP_EOL . PHP_EOL;

esc_html_e( 'Thanks for reading!', 'flexible-coupons-pro' );

echo '****************************************************' . PHP_EOL . PHP_EOL;

echo esc_html( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
