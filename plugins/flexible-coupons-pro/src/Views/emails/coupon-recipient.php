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

<h2>
	<?php
	/* translators: %s: Recipient name. */
	printf( esc_html__( 'Hi %s,', 'flexible-coupons-pro' ), esc_html( $meta->get_recipient_name() ) );
	?>
</h2>

<p>
	<b>
		<?php
		printf(
			wp_kses(
				/* translators: %1$s: Recipient name, %2$s: Site URL, %3$s: Site name, %4$s: Expiry date. */
				__( 'Thanks to %1$s you get a gift voucher for use in the <a href="%2$s">%3$s</a> store. The coupon is valid to %4$s.', 'flexible-coupons-pro' ),
				[
					'a' => [
						'href' => [],
					],
				]
			),
			esc_html( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
			esc_url( get_site_url() ),
			esc_html( get_bloginfo( 'name' ) ),
			esc_html( $meta->get_coupon_expiry() )
		);
		?>
	</b>
</p>


<h2><?php esc_html_e( 'Coupon information', 'flexible-coupons-pro' ); ?></h2>


<?php foreach ( $meta->get_coupons_array() as $coupon ) : ?>

	<?php if ( isset( $coupon['coupon_code'] ) ) : ?>
		<p>
			<?php
			/* translators: %s: Coupon code. */
			printf( esc_html__( 'Coupon code: %s', 'flexible-coupons-pro' ), esc_html( $coupon['coupon_code'] ) );
			?>
		</p>
	<?php endif; ?>

	<?php if ( ! empty( $meta->get_coupon_value() ) ) : ?>
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
	<?php endif; ?>

	<?php if ( ! empty( $meta->get_coupon_expiry() ) ) : ?>
		<p>
			<?php
			/* translators: %s: Coupon expiry date. */
			printf( esc_html__( 'Expiry date: %s', 'flexible-coupons-pro' ), esc_html( $meta->get_coupon_expiry() ) );
			?>
		</p>
	<?php endif; ?>
	<p>
		<a href="<?php echo esc_url( $coupon['coupon_url'] ); ?>">
			<?php esc_html_e( 'Download PDF with the coupon &raquo;', 'flexible-coupons-pro' ); ?>
		</a>
	</p>

<?php endforeach; ?>


<?php if ( ! empty( $meta->get_recipient_message() ) ) : ?>
	<p><?php esc_html_e( 'A message from the buyer:', 'flexible-coupons-pro' ); ?></p>
	<p><?php echo esc_html( $meta->get_recipient_message() ); ?></p>
<?php endif; ?>

<p><?php esc_html_e( 'Thanks for reading!', 'flexible-coupons-pro' ); ?></p>

<?php do_action( 'woocommerce_email_footer' ); ?>
