<?php
/**
 * Email z kuponem
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
} // Exit if accessed directly ?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ?? '' ); ?>

<h1>
	<?php
	/* translators: %s: Customer name. */
	printf( esc_html__( 'Hi %s,', 'flexible-coupons-pro' ), esc_html( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ) );
	?>
</h1>

<p>
	<b>
	<?php
	printf(
		/* translators: %1$s: Order number, %2$s: Order date. */
		esc_html__( 'In response to an order %1$s placed %2$s, we are sending you a coupon for shopping in our shop.', 'flexible-coupons-pro' ),
		esc_html( (string) $order_number ),
		esc_html( $date_order ),
	);
	?>
	</b>
</p>

<?php foreach ( $meta->get_coupons_array() as $coupon ) : ?>

	<?php if ( isset( $coupon['coupon_code'] ) ) : ?>
		<p>
			<?php
			/* translators: %s: Coupon code. */
			printf( esc_html__( 'Coupon code: %s', 'flexible-coupons-pro' ), esc_html( $coupon['coupon_code'] ) );
			?>
		</p>
		<p>
			<?php
			/* translators: %s: Coupon value. */
			printf(
				esc_html__( 'Coupon value: %s', 'flexible-coupons-pro' ),
				wp_kses(
					$meta->get_coupon_value(),
					[
						'span' => [
							'class' => [],
						],
						'bdi'  => [],
					]
				)
			);
			?>
		</p>
		<?php if ( ! empty( $meta->get_coupon_expiry() ) ) : ?>
			<p>
				<?php
				/* translators: %s: Coupon expiry date. */
				printf( esc_html__( 'Expiry date: %s', 'flexible-coupons-pro' ), esc_html( $meta->get_coupon_expiry() ) );
				?>
			</p>
		<?php endif; ?>
	<?php endif; ?>

	<p>
		<a href="<?php echo esc_url( $coupon['coupon_url'] ); ?>">
			<?php
			esc_html_e( 'Download PDF with the coupon &raquo;', 'flexible-coupons-pro' );
			?>
		</a>
	</p>

<?php endforeach; ?>


<h2><?php esc_html_e( 'Coupon fields:', 'flexible-coupons-pro' ); ?></h2>
<?php
if ( ! empty( $meta->get_recipient_name() ) ) {
	echo '<p>' . sprintf( esc_html__( 'Recipient name: %s', 'flexible-coupons-pro' ), esc_html( $meta->get_recipient_name() ) ) . '</p>';
}
if ( ! empty( $meta->get_recipient_email() ) ) {
	echo '<p>' . sprintf( esc_html__( 'Recipient e-mail: %s', 'flexible-coupons-pro' ), esc_html( $meta->get_recipient_email() ) ) . '</p>';
}
if ( ! empty( $meta->get_recipient_message() ) ) {
	echo '<p>' . sprintf( esc_html__( 'Message for recipient: %s', 'flexible-coupons-pro' ), esc_html( $meta->get_recipient_message() ) ) . '</p>';
}
?>

<p><?php esc_html_e( 'Thanks for reading!', 'flexible-coupons-pro' ); ?></p>

<?php do_action( 'woocommerce_email_footer' ); ?>
