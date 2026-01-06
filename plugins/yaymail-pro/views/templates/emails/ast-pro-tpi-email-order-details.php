<?php
/**
 * Order details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/ast-pro-tpi-email-order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates/Emails
 * @version 5.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$text_align  = is_rtl() ? 'right' : 'left';
$margin_side = is_rtl() ? 'left' : 'right';


$ast_preview = ( isset( $_REQUEST['action'] ) && 'email_preview' === $_REQUEST['action'] ) ? true : false;

$table_font_size = '';
$kt_woomail      = get_option( 'kt_woomail' );

if ( ! empty( $kt_woomail ) && isset( $kt_woomail['font_size'] ) ) {
	$table_font_size = 'font-size:' . $kt_woomail['font_size'] . 'px';
}

$ast_customizer = Ast_Customizer::get_instance();
$ast            = new AST_Pro_Actions();

$button_background_color = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_button_background_color', $ast_customizer->defaults['fluid_button_background_color'] );
$button_font_color       = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_button_font_color', $ast_customizer->defaults['fluid_button_font_color'] );
$button_radius           = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_button_radius', $ast_customizer->defaults['fluid_button_radius'] );
$fluid_button_text       = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_button_text', $ast_customizer->defaults['fluid_button_text'] );
?>
<style>
a.button.track-button {
	background: <?php echo esc_html( $button_background_color ); ?>;
	color: <?php echo esc_html( $button_font_color ); ?> !important;
	padding: 8px 15px;
	text-decoration: none;
	border-radius: <?php echo esc_html( $button_radius ); ?>px;
	margin-top: 0;
	font-size: 90% !important;
	position: absolute !important;
	top: 6px !important;
	right: 0 important;
}
.tpi_order_details_table tr td{
	vertical-align: middle; 
	font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; 
	word-wrap:break-word;
	border-left:0;
	border:0 !important;
	border-bottom:1px solid #e0e0e0;
	padding: 12px 6px 12px 6px !important;	
	position: relative;
}
.tpi_order_details_table tr td.last_td{
	border-bottom:0 !important;
}
.tpi_order_details_table tr td.image_id{
	padding-left: 0 !important;
}
.shipment_heading{	
	margin: 0 0 5px !important;	
}
.shipment_heading.heading_border{
	border-bottom:1px solid #e0e0e0;	
}
.shipping_items_heading{
	margin: 10px 0 0 !important;
	border-bottom: 1px solid #e0e0e0;
	padding-bottom: 3px;
}
.section_divider{
	margin: 10px 0px 15px;
	border: 0;
	border-top: 1px solid #e0e0e0;
}
</style>
	<?php
	$total_trackings = count( $tracking_items );
	$layout          = 'tpi_layout_2';
	foreach ( $tracking_items as $tracking_item ) {
		if ( isset( $tracking_item['products_list'] ) && ! empty( $tracking_item['products_list'] ) && count( $tracking_item['products_list'] ) > 1 ) {
			$layout = 'tpi_layout_2';
			continue;
		}
	}
	// echo $layout;exit;
	if ( 'tpi_layout_2' == $layout ) {

		$num = 1;
		foreach ( $tracking_items as $tracking_item ) {
			$heading_class = ( isset( $tracking_item['products_list'] ) && ! empty( $tracking_item['products_list'] ) && count( $tracking_item['products_list'] ) == 1 ) ? 'heading_border' : '';

			if ( $total_trackings > 1 ) {
					/* translators: %1$s: search number, %2$s: search total trackings */
					echo '<p class="shipment_heading"><strong><i>' . sprintf( esc_html__( 'Shipment %1$s (out of %2$s):', 'ast-pro' ), esc_html( $num ), esc_html( $total_trackings ) ) . '</i></strong></p>';
			}

			$tpi_item   = array();
			$tpi_item[] = $tracking_item;

			$numItems = 0;
			if ( is_array( $tracking_item['products_list'] ) ) {
				?>
			<div style="margin:0;">
				<table class="td tpi_order_details_table" cellspacing="0" cellpadding="6" style="background-color: transparent;width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;border:0;<?php echo esc_html( $table_font_size ); ?>" border="0">
					<tbody>
						<?php
						$numItems = count( $tracking_item['products_list'] );

						$i = 0;
						foreach ( (array) $tracking_item['products_list'] as $products_list ) {
							$product = wc_get_product( $products_list->product );

							if ( ! is_object( $product ) ) {
								continue;
							}

							$image_size    = array( 64, 64 );
							$product_id    = $product->get_id();
							$sku           = $product->get_sku();
							$purchase_note = $product->get_purchase_note();
							$image         = $product->get_image( $image_size );


							foreach ( $order->get_items() as $item_id => $item ) {
								$item_product = $item->get_product();

								if ( ! is_object( $item_product ) ) {
									continue;
								}

								$item_product_id = $item_product->get_id();
								if ( $item_product_id == $product_id ) {
									$order_item = $item;
								}
							}

							$last_child_class = '';
							if ( ++$i === $numItems ) {
								$last_child_class = 'last_td';
							}
							?>
							<tr>
								<?php
								if ( $ast_preview ) {
									$display_image_class = ( ! $display_product_images ) ? 'hide' : '';
									?>
									<td class="td image_id <?php echo esc_attr( $last_child_class ); ?> <?php echo esc_attr( $display_image_class ); ?>" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;border-left:0;border:0;border-bottom:1px solid #e0e0e0;padding: 12px 5px;width: 70px;">
										<?php echo wp_kses_post( apply_filters( 'woocommerce_order_item_thumbnail', $image, $item ) ); ?>
									</td>
									<?php
								} elseif ( $display_product_images ) {
									?>
									<td class="td image_id <?php echo esc_attr( $last_child_class ); ?>" style="text-align:<?php echo esc_attr( $text_align ); ?>;width: 70px;">
									<?php echo wp_kses_post( apply_filters( 'woocommerce_order_item_thumbnail', $image, $item ) ); ?>
									</td>
								<?php } ?>
								<td class="td <?php echo esc_attr( $last_child_class ); ?>" style="text-align:<?php echo esc_attr( $text_align ); ?>;">
									<?php
									// Product name.
									echo '<div>';
									echo wp_kses_post( $product->get_name() );
									echo ' x ';
									echo esc_html( $products_list->qty );

									if ( $ast_preview ) {
										$shippment_item_price_class = ( ! $display_shippment_item_price ) ? 'hide' : '';
										echo '<span class="shippment_item_price ' . esc_attr( $shippment_item_price_class ) . '">';
										echo ' - ';
										echo wp_kses_post( $order->get_formatted_line_subtotal( $order_item ) );
										echo '</span>';
									} elseif ( $display_shippment_item_price ) {
										echo ' - ';
										echo wp_kses_post( $order->get_formatted_line_subtotal( $order_item ) );
									}
									echo '</div>';
									?>
								</td>	
							</tr>	
							<?php
						}
						?>
					</tbody>
				</table>
			</div>
			<hr class="section_divider">
				<?php
			}
			$num++;
		}
	} else {
		?>
		<?php
		$numItems = count( $tracking_items );
		$i        = 0;
		foreach ( $tracking_items as $tracking_item ) {
			$last_child_class = '';
			if ( ++$i === $numItems ) {
				$last_child_class = 'last_td';
			}
			?>
		<div style="margin:0;">			
			<table class="td tpi_order_details_table" cellspacing="0" cellpadding="6" style="background-color: transparent;width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;border:0;<?php echo esc_html( $table_font_size ); ?>" border="0">
				<tbody>
					<?php

					foreach ( $tracking_item['products_list'] as $products_list ) {
						$product       = wc_get_product( $products_list->product );
						$product_id    = $product->get_id();
						$sku           = '';
						$purchase_note = '';
						$image         = '';
						$image_size    = array( 64, 64 );

						if ( is_object( $product ) ) {
							$sku           = $product->get_sku();
							$purchase_note = $product->get_purchase_note();
							$image         = $product->get_image( $image_size );
						}

						foreach ( $order->get_items() as $item_id => $item ) {
							$item_product    = $item->get_product();
							$item_product_id = $item_product->get_id();
							if ( $item_product_id == $product_id ) {
								$order_item = $item;
							}
						}

						?>
						<tr>
						<?php
						if ( $ast_preview ) {
							$display_image_class = ( ! $display_product_images ) ? 'hide' : '';
							?>
								<td class="td image_id <?php echo esc_attr( $last_child_class ); ?> <?php echo esc_attr( $display_image_class ); ?>" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;border-left:0;border:0;border-bottom:1px solid #e0e0e0;padding: 12px 5px;width: 70px;">
								<?php echo wp_kses_post( apply_filters( 'woocommerce_order_item_thumbnail', $image, $item ) ); ?>
								</td>
								<?php
						} elseif ( $display_product_images ) {
							?>
								<td class="td image_id <?php echo esc_attr( $last_child_class ); ?>" style="text-align:<?php echo esc_attr( $text_align ); ?>;width: 70px;">
								<?php echo wp_kses_post( apply_filters( 'woocommerce_order_item_thumbnail', $image, $item ) ); ?>
								</td>
							<?php } ?>
							<td class="td <?php echo esc_attr( $last_child_class ); ?>" style="text-align:<?php echo esc_attr( $text_align ); ?>;">
								<?php
								// Product name.
								echo wp_kses_post( $product->get_name() );
								echo ' x ';
								echo esc_html( $products_list->qty );

								if ( $ast_preview ) {
									$shippment_item_price_class = ( ! $display_shippment_item_price ) ? 'hide' : '';
									echo '<span class="shippment_item_price ' . esc_attr( $shippment_item_price_class ) . '">';
									echo ' - ';
									echo wp_kses_post( $order->get_formatted_line_subtotal( $order_item ) );
									echo '</span>';
								} elseif ( $display_shippment_item_price ) {
									echo ' - ';
									echo wp_kses_post( $order->get_formatted_line_subtotal( $order_item ) );
								}

								echo '<div style="margin-top:10px;"><span style="font-size: 90%;">' . esc_html( $tracking_item['formatted_tracking_provider'] ) . '<a style="font-size: 90%;margin: 0 10px 0 5px;text-decoration: none;" href=' . esc_url( $tracking_item['ast_tracking_link'] ) . '><span>' . esc_html( $tracking_item['tracking_number'] ) . '</span></a></span> </div>';
								?>
							</td>
							<td class="td <?php echo esc_attr( $last_child_class ); ?>" style="text-align:right;">
								<?php echo '<a class="button track-button" href=' . esc_url( $tracking_item['ast_tracking_link'] ) . '><span>' . esc_html( $fluid_button_text ) . '</span></a>'; ?>
							</td>	
						</tr>	
						<?php
					}
					?>
				</tbody>
			</table>
		</div>
		<hr class="section_divider">		
			<?php
		}
	}
	?>
</div>
<?php

