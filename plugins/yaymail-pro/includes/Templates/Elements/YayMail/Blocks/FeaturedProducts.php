<?php
	use YayMail\Helper\Products;
	$table_width                  = $general_attrs['tableWidth'];
	$background_color             = $attrs['backgroundColor'];
	$padding_top                  = $attrs['paddingTop'];
	$padding_left                 = $attrs['paddingLeft'];
	$padding_right                = $attrs['paddingRight'];
	$padding_bottom               = $attrs['paddingBottom'];
	$showing_items                = isset( $attrs['showingItems'] ) ? $attrs['showingItems'] : array();
	$top_content                  = $attrs['topContent'];
	$text_color                   = $attrs['textColor'];
	$products_per_row             = isset( $attrs['productsPerRow'] ) ? $attrs['productsPerRow'] : 3;
	$categories                   = array_map(
		function( $item ) {
			return $item['key'];
		},
		isset( $attrs['categories'] ) ? $attrs['categories'] : array()
	);
	$tags                         = array_map(
		function( $item ) {
			return $item['key'];
		},
		isset( $attrs['tags'] ) ? $attrs['tags'] : array()
	);
	$product_ids                  = array_map(
		function( $item ) {
			return $item['key'];
		},
		isset( $attrs['products'] ) ? $attrs['products'] : array()
	);
	$products                     = Products::get_products_by_filter(
		array(
			'type'        => $attrs['productType'],
			'sorted_by'   => $attrs['sortedBy'],
			'limit'       => $attrs['numberOfProducts'],
			'categories'  => $categories,
			'tags'        => $tags,
			'product_ids' => $product_ids,
		)
	);
	$buy_text                     = $attrs['buyText'];
	$buy_text_color               = $attrs['buyTextColor'];
	$buy_background_color         = $attrs['buyBackgroundColor'];
	$product_price_color          = $attrs['productPriceColor'];
	$product_original_price_color = $attrs['productOriginalPriceColor'];
	$family                       = str_replace( '"', '', $attrs['family'] );

	$width = ( 100 / $products_per_row - 10.5 ) . '%';
	?>
<table
	id="<?php echo esc_attr( $id ); ?>"
	cellspacing="0"
	cellpadding="0"
	border="0"
	align="center"
	width="<?php echo esc_attr( $table_width ); ?>"
	style='display: table; <?php echo esc_attr( "background-color: {$background_color};" ); ?>'
	class="web-main-row"
  >
	<tbody>
		<tr>
			<td
			id="<?php echo esc_attr( $id ); ?>"
			style='
			<?php echo esc_attr( "padding-top: {$padding_top}px; padding-left: {$padding_left}px; padding-right: {$padding_right}px; padding-bottom: {$padding_bottom}px;" ); ?>
			font-family: <?php echo wp_kses_post( $family ); ?>;
			'
			>
			<div class="element-text-content">
				<?php if ( in_array( 'topContent', $showing_items, true ) ) : ?>
					<div class="element-featured-product-top-content" style='<?php echo esc_attr( "color: {$text_color};" ); ?>'>
						<?php echo wp_kses_post( $top_content ); ?>
					</div>
				<?php endif; ?>
				<div class="yaymail_list_featured_products" style="text-align:center; margin-top:10px">
				<?php
				foreach ( $products as $product ) :
					$sale_price     = ! empty( $product['sale_price'] ) ? $product['sale_price'] : ( empty( $product['regular_price'] ) ? $product['price'] : $product['regular_price'] );
					$original_price = ! empty( $product['sale_price'] ) ? $product['regular_price'] : '';
					?>
					<div class="yaymail-inline-block" style='width: <?php echo esc_attr( $width ); ?>; padding: 10px; margin-top:10px; text-align:center; display:inline-block;'>
					<?php if ( in_array( 'productImage', $showing_items, true ) ) : ?>
						<a href="<?php echo esc_url( $product['permalink'] ); ?>">
							<img
							style="object-fit: cover; width: 100%"
							class="web-img"
							border="0"
							tabindex="0"
							src="<?php echo esc_url( $product['image'] ); ?>"
							height="auto"
							/>
						</a>
					<?php endif; ?>
					<?php if ( in_array( 'productName', $showing_items, true ) ) : ?>
						<p style='margin-top: 5px; font-weight:bold; <?php echo esc_attr( "color: {$text_color}" ); ?>'> <?php echo esc_html( $product['name'] ); ?></p>
					<?php endif; ?>
					<?php if ( in_array( 'productPrice', $showing_items, true ) ) : ?>
						<p style='margin-top: 5px; font-weight: bold; <?php echo esc_attr( "color: {$product_price_color}" ); ?>'>
							<span>
								<?php echo wp_kses_post( $sale_price ); ?>
							</span>
							<?php if ( in_array( 'productOriginalPrice', $showing_items, true ) && ! empty( $original_price ) ) : ?>
								<span style='text-decoration: line-through; margin-left: 4px; <?php echo esc_attr( "color: {$product_original_price_color}" ); ?>'>
									<?php echo wp_kses_post( $original_price ); ?>
								</span>
							<?php endif; ?>
						</p>
					<?php endif; ?>
					<?php if ( in_array( 'buyButton', $showing_items, true ) ) : ?>
						<a
							href="<?php echo esc_url( $product['permalink'] ); ?>"
							class="yaymail-inline-block"
							style='
								font-weight: bold;
								line-height: 21px;
								text-align: center;
								text-decoration: none;
								display: block;
								margin: 0px;
								margin-top: 5px;
								font-family: <?php echo wp_kses_post( $family ); ?>;
								font-size: 13px;
								font-weight: normal;
								padding: 10px 15px;
								<?php echo esc_attr( "color: {$buy_text_color};" ); ?>
								<?php echo esc_attr( "background-color: {$buy_background_color};" ); ?>
							'>
							<?php echo esc_html( $buy_text ); ?>
						</a>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>
				</div>
			</div>
			</td>
		</tr>
	</tbody>
</table>
