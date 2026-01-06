<?php
/**
 * Order details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates/Emails
 * @version 3.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$text_align  = is_rtl() ? 'right' : 'left';
$margin_side = is_rtl() ? 'left' : 'right';

$table_font_size = '';
$kt_woomail      = get_option( 'kt_woomail' );
if ( ! empty( $kt_woomail ) && isset( $kt_woomail['font_size'] ) ) {
	$table_font_size = 'font-size:' . $kt_woomail['font_size'] . 'px';
}
$borderColor = isset( $atts['bordercolor'] ) && $atts['bordercolor'] ? 'border-color:' . html_entity_decode( $atts['bordercolor'], ENT_QUOTES, 'UTF-8' ) : 'border-color:inherit';
?>

<div style="border-color: inherit;">
		<table class="td order_details_table" cellspacing="0" cellpadding="6" style="background-color: transparent;width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;border:0;<?php echo esc_html( $table_font_size ); ?>;border-color: inherit;" border="0">
			<tbody style="border-color: inherit;">
					<tr class="order_item" style="border-color: inherit;">
						<?php
						if ( $display_product_images ) {
							?>
							<td class="td image_id" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align: middle; word-wrap:break-word;border-left:0;border:0;border-bottom:1px solid #e0e0e0;padding: 12px 5px;width: 70px;<?php echo esc_attr( $borderColor ); ?>;">
								<img style="width:100%;max-width: 40px;border-radius: 5px;" src="<?php echo esc_url( wp_kses_post( wc_placeholder_img_src() ) ); ?>"></img>
							</td>
						<?php } ?>
						<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align: middle; word-wrap:break-word;border-left:0;border:0;border-bottom:1px solid #e0e0e0;padding: 12px 5px;<?php echo esc_attr( $borderColor ); ?>;">
							<?php
							// Product name.
							echo wp_kses_post( 'YayMail' );
							echo ' x ';
							echo esc_html( '1' );
							?>
						</td>	
					</tr>
				
			</tbody>			
		</table>
	</div>
<?php
