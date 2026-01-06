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

/* translators: %s: Customer name. */
printf( esc_html__( 'Hi %s,', 'flexible-coupons-pro' ), esc_html( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ) );

echo PHP_EOL . PHP_EOL;

/* translators: %1$s: Order number, %2$s: Order date. */
printf( esc_html__( 'In response to an order %1$s placed %2$s, we are sending you a coupon for shopping in our shop.', 'flexible-coupons-pro' ), esc_html( (string) $order_number ), esc_html( $date_order ) );

echo PHP_EOL . PHP_EOL;


echo PHP_EOL . PHP_EOL;

foreach ( $meta->get_coupons_array() as $coupon ) {
	echo esc_html__( 'Coupon information', 'flexible-coupons-pro' ) . PHP_EOL;
	if ( isset( $coupon['coupon_code'] ) ) {
		/* translators: %s: Coupon code. */
		printf( esc_html__( 'Coupon code: %s', 'flexible-coupons-pro' ), esc_html( $coupon['coupon_code'] ) );
		/* translators: %s: Coupon value. */
		printf( esc_html__( 'Coupon value: %s', 'flexible-coupons-pro' ), esc_html( $coupon['coupon_value'] ) );
		if ( ! empty( $coupon['coupon_expiring'] ) ) {
			/* translators: %s: Expiry date. */
			printf( esc_html__( 'Expiry date: %s', 'flexible-coupons-pro' ), esc_html( $coupon['coupon_expiring'] ) );
		}
	}
	/* translators: %s: Download link. */
	printf( esc_html__( 'Download PDF with the coupon from: %s', 'flexible-coupons-pro' ), esc_url( $coupon['coupon_url'] ) );


	echo PHP_EOL . PHP_EOL;
}


esc_html_e( 'Coupon fields:', 'flexible-coupons-pro' );
if ( ! empty( $meta->get_recipient_name() ) ) {
	/* translators: %s: Recipient name. */
	printf( esc_html__( 'Recipient name: %s', 'flexible-coupons-pro' ), esc_html( $meta->get_recipient_name() ) );
}
echo PHP_EOL;
if ( ! empty( $meta->get_recipient_email() ) ) {
	/* translators: %s: Recipient e-mail. */
	printf( esc_html__( 'Recipient e-mail: %s', 'flexible-coupons-pro' ), esc_html( $meta->get_recipient_email() ) );
}
echo PHP_EOL;
if ( ! empty( $meta->get_recipient_message() ) ) {
	/* translators: %s: Message for recipient. */
	printf( esc_html__( 'Message for recipient: %s', 'flexible-coupons-pro' ), esc_html( $meta->get_recipient_message() ) );
}

echo PHP_EOL . PHP_EOL;

esc_html_e( 'Thanks for reading!', 'flexible-coupons-pro' );

echo '****************************************************' . PHP_EOL . PHP_EOL;

echo esc_html( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
