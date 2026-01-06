<?php
/**
 * BOGO free product choosing section
 *
 * @since 2.0.0
 *
 * @package  Wt_Smart_Coupon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wt_sc_giveaway_products_cart_page">
	<?php
	foreach ( $free_products as $coupon_code => $free_product_items ) {
		if ( empty( $free_product_items ) ) {
			continue;
		}

		$message      = self::get_general_settings_value( 'wbte_sc_bogo_general_apply_choose_product_title' );
		$coupon_id    = wc_get_coupon_id_by_code( $coupon_code );
		$coupon_title = self::get_coupon_meta_value( $coupon_id, 'wbte_sc_bogo_coupon_name' );
		$message      = str_replace( '{bogo_title}', $coupon_title, $message );
		$message_html = "<h4 class='giveaway-title'>$message</h4>";
		$message_html = apply_filters( 'wt_smartcoupon_give_away_message', $message_html, $coupon_code, $coupon_id );
		echo wp_kses_post( $message_html );
		?>
		<ul class="woocommcerce wbte_sc_bogo_products" coupon="<?php echo esc_attr( $coupon_id ); ?>">
			<?php
			$total_purchasable = 0;
			foreach ( $free_product_items as $product_id ) {
				$_product = wc_get_product( $product_id );
				if ( ! $_product || ( $_product->get_stock_quantity() && $_product->get_stock_quantity() < 1 ) ) {
					continue;
				}

				/* product image */
				$image = wp_get_attachment_image_src( $_product->get_image_id(), 'woocommerce_thumbnail' );
				if ( $_product->is_type( 'variable' ) ) {
					$default_attributes           = $_product->get_default_attributes();
					$default_variation_id         = 0;
					$default_variation_attributes = array();
					$_variation_product           = $_product;
					if ( ! empty( $default_attributes ) ) {

						$default_variation_attributes = array_combine(
							array_map(
								function ( $key ) {
									return 'attribute_' . $key; },
								array_keys( $default_attributes )
							),
							$default_attributes
						);

						$default_variation_id = self::find_matching_product_variation_id( $product_id, $default_variation_attributes );
						if ( $default_variation_id ) {
							$_variation_product = wc_get_product( $default_variation_id );
						}
					}
					$image = wp_get_attachment_image_src( $_variation_product->get_image_id(), 'woocommerce_thumbnail' );
				}
				if ( ! $image ) {
					$parent_product = wc_get_product( $_product->get_parent_id() );
					if ( $parent_product ) {
						$image = wp_get_attachment_image_src( $parent_product->get_image_id(), 'woocommerce_thumbnail' );
					}
				}

				if ( ! $image ) {
					$dimensions = wc_get_image_size( 'woocommerce_thumbnail' );
					$image      = array( wc_placeholder_img_src( 'woocommerce_thumbnail' ), $dimensions['width'], $dimensions['height'], false );
				}
				$variation_attributes = array(); /* this applicable only for variable products */
				$is_purchasable       = self::is_purchasable( $_product, $variation_attributes );
				if ( $is_purchasable ) {
					++$total_purchasable;
				}
				$temp_product_id              = $product_id;
				$variation_without_attributes = false;
				if ( $_product->is_type( 'variation' ) ) {
					foreach ( $_product->get_variation_attributes() as $attribute_name => $options ) {
						if ( '' === $options ) {
							$temp_product_id              = $_product->get_parent_id();
							$variation_without_attributes = true;
							break;
						}
					}
				}
				?>
				<li class="wbte_get_away_product" title="<?php echo esc_attr( $_product->get_name() ); ?>" data-is_purchasable="<?php echo esc_attr( $is_purchasable ? 1 : 0 ); ?>" product-id="<?php echo esc_attr( $temp_product_id ); ?>" data-free-qty="<?php echo esc_attr( $free_products_qty[ $coupon_code ][ $product_id ] ); ?>">
					<div class="wbte_product_image">
					<?php
					if ( $image && is_array( $image ) && isset( $image[0] ) ) {
						?>
						<img src="<?php echo esc_attr( $image[0] ); ?>" data-id="<?php echo esc_attr( $product_id ); ?>" />
						<?php
					} else {
						?>
						<div class="wt_sc_dummy_img"></div>
						<?php
					}
					?>
					</div>
					<div class="wbte_product_name">
						<?php echo esc_html( wp_trim_words( $_product->get_name(), 6 ) ); ?>
					</div>
					<?php
					if ( $is_purchasable ) {
						?>
					<div class="wbte_product_discount">
						<?php
						if ( ! $_product->is_type( 'variable' ) ) {
							?>
						<div>
							<?php
							$_discount = self::get_available_discount_for_giveaway_product( $coupon_id, $_product );
							$_price    = $_product->get_price();
							echo '<del><span>' . wp_kses_post( wc_price( $_price ) ) . '</span></del>&nbsp;<span>' . wp_kses_post( wc_price( $_price - $_discount ) ) . '</span>';
							?>
						</div>
							<?php
						}
						?>
					</div>
						<?php
						echo esc_html__( 'Quantity : ', 'wt-smart-coupons-for-woocommerce' );
						?>
							<div class="wbte_sc_bogo_quantity">
								<input type="number" name="wbte_sc_bogo_quantity" min="1" step="1" max="<?php echo esc_attr( $free_products_qty[ $coupon_code ][ $product_id ] ); ?>" value="<?php echo esc_attr( $free_products_qty[ $coupon_code ][ $product_id ] ); ?>" <?php echo $qty_alter_option[ $coupon_code ] ? '' : 'disabled'; ?>>
							</div>   
						<?php
					} else {
						?>
						<p class="wt_sc_product_out_of_stock stock out-of-stock"><?php esc_html_e( 'Sorry! this product is not available for giveaway.', 'wt-smart-coupons-for-woocommerce' ); ?></p>
						<?php
					}
					?>
					<?php
					if ( $_product->is_type( 'variable' ) ) {
						if ( $is_purchasable ) {
							?>
							<table class="variations wt_variations" cellspacing="0">
								<tbody>
								<?php
								foreach ( $_product->get_variation_attributes() as $attribute_name => $options ) {
									?>
									<tr>
										<td class="value">
											<label for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"><?php echo esc_html( wc_attribute_label( $attribute_name ) ); ?></label>
											<?php
											wc_dropdown_variation_attribute_options(
												array(
													'options'           => $options,
													'attribute'         => $attribute_name,
													'product'           => $_product,
													'class'             => 'wbte_give_away_product_attr',
													'show_option_none'  => false,
												)
											);
											?>
										</td>
									</tr>
									<?php
								}
								?>
								</tbody>
							</table>
							<input type="hidden" name="variation_id" value="<?php echo esc_attr( $default_variation_id ); ?>" />
							<input type="hidden" name="wt_product_id" value="<?php echo esc_attr( $product_id ); ?>" />
							<input type="hidden" name="wt_variation_options" value='<?php echo esc_attr( wp_json_encode( $default_variation_attributes ) ); ?>' />
							<?php
						}
					}
					if ( $variation_without_attributes && $is_purchasable ) {
						$variation_id = 0;
						?>
						<div class="wt_choose_button_box">
							<?php

							if ( $_product->is_type( 'variation' ) ) {

								$variation_attributes = isset( $product_data['attributes'] ) ? $product_data['attributes'] : array();
								?>
								<input type="hidden" name="variation_id" value="<?php echo esc_attr( $variation_id ); ?>">
								<input type="hidden" name="wt_product_id" value="<?php echo esc_attr( $product_id ); ?>" />
								<input type="hidden" name="wt_variation_options" value='<?php echo esc_attr( wp_json_encode( $variation_attributes ) ); ?>' />
								<?php
								$variation_id = $product_id;
								$product_id   = $_product->get_parent_id();
								if ( empty( $variation_attributes ) && $variation_without_attributes ) {
									$parent_product       = wc_get_product( $product_id );
									$variation_attributes = $_product->get_variation_attributes();
									foreach ( $variation_attributes as $attribute_name => $options ) {

										$variation_attributes[ $attribute_name ] = '' === $options
										? explode( ', ', $parent_product->get_attribute( str_replace( 'attribute_', '', $attribute_name ) ) )
										: array( $options );

									}
									?>
										<table class="variations wt_variations" cellspacing="0">
											<tbody>
											<?php
											foreach ( $variation_attributes as $attribute_name => $options ) {
												?>
												<tr>
													<td class="value">
														<label for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"><?php echo esc_html( wc_attribute_label( str_replace( 'attribute_', '', $attribute_name ) ) ); ?></label>
														<select id=<?php echo esc_attr( $attribute_name ); ?> class="wbte_give_away_product_attr" name=<?php echo esc_attr( $attribute_name ); ?> data-attribute_name=<?php echo esc_attr( $attribute_name ); ?> data-show_option_none="no">
														<option value=""><?php esc_html_e( 'Choose an option', 'wt-smart-coupons-for-woocommerce' ); ?></option>
														<?php
														foreach ( $options as $key => $value ) {
															?>
																	<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $value ); ?></option>
																<?php
														}

														?>
														</select>
													</td>
												</tr>
												<?php
											}
											?>
											</tbody>
										</table>
									<?php
								}
							}

							?>
						</div>
						<?php
					}
					?>
					<button class="wbte_choose_free_product" prod-id="<?php echo esc_attr( $product_id ); ?>" type="button"><?php esc_html_e( 'Choose product', 'wt-smart-coupons-for-woocommerce' ); ?></button>
				</li>
				<?php
			}
			?>
		</ul>
		<?php
	}
	?>
</div>