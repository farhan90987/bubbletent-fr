<?php
	use YayMail\Helper\Products;
	$table_width             = $general_attrs['tableWidth'];
	$background_color        = $attrs['backgroundColor'];
	$padding_top             = $attrs['paddingTop'];
	$padding_left            = $attrs['paddingLeft'];
	$padding_right           = $attrs['paddingRight'];
	$padding_bottom          = $attrs['paddingBottom'];
	$border_width            = $attrs['borderWidth'];
	$border_style            = $attrs['borderStyle'];
	$border_color            = $attrs['borderColor'];
	$showing_items           = isset( $attrs['showingItems'] ) ? $attrs['showingItems'] : array();
	$content                 = $attrs['content'];
	$text_color              = $attrs['textColor'];
	$button_text             = $attrs['buttonText'];
	$button_url              = $attrs['buttonUrl'];
	$button_text_color       = $attrs['buttonTextColor'];
	$button_background_color = $attrs['buttonBackgroundColor'];
	$family                  = str_replace( '"', '', $attrs['family'] );
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
			<div
				class="simple-offer-wrapper"
				<?php if ( in_array( 'border', $showing_items, true ) ) : ?>
					style='
					padding: 10px 15px;
					<?php echo esc_attr( "border : {$border_width}px {$border_style} {$border_color}" ); ?>
					'
				<?php endif; ?>
			>
				<div
				class="yaymail-inline-block"
				style='display: inline-block; width: 70%; vertical-align: middle;
				<?php echo esc_attr( "color: {$text_color}" ); ?>;
				<?php echo 'width: ' . in_array( 'button', $showing_items, true ) ? '70%' : '100%'; ?>;
				<?php echo 'margin-right: ' . in_array( 'button', $showing_items, true ) ? '10px' : '0'; ?>;
				'
				>
				<?php echo wp_kses_post( $content ); ?>
				</div>
				<?php if ( in_array( 'button', $showing_items, true ) ) : ?>
					<a
						href="<?php echo esc_url( $button_url ); ?>"
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
							<?php echo esc_attr( "color: {$button_text_color};" ); ?>
							<?php echo esc_attr( "background-color: {$button_background_color};" ); ?>
						'>
						<?php echo esc_html( $button_text ); ?>
					</a>
				<?php endif; ?>
			</div>
			</td>
		</tr>
	</tbody>
</table>
