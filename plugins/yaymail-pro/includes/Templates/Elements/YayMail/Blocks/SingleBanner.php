<?php
	use YayMail\Helper\Products;
	$table_width             = $general_attrs['tableWidth'];
	$background_color        = $attrs['backgroundColor'];
	$padding_top             = $attrs['paddingTop'];
	$padding_left            = $attrs['paddingLeft'];
	$padding_right           = $attrs['paddingRight'];
	$padding_bottom          = $attrs['paddingBottom'];
	$showing_items           = isset( $attrs['showingItems'] ) ? $attrs['showingItems'] : array();
	$background_image        = $attrs['backgroundImage'];
	$content                 = $attrs['content'];
	$content_width           = $attrs['contentWidth'];
	$content_align           = $attrs['contentAlign'];
	$text_color              = $attrs['textColor'];
	$button_text             = $attrs['buttonText'];
	$button_url              = $attrs['buttonUrl'];
	$button_text_color       = $attrs['buttonTextColor'];
	$button_background_color = $attrs['buttonBackgroundColor'];
	$button_align            = $attrs['buttonAlign'];
	$family                  = str_replace( '"', '', $attrs['family'] );
?>
<table
	id="<?php echo esc_attr( $id ); ?>"
	cellspacing="0"
	cellpadding="0"
	border="0"
	align="center"
	width="<?php echo esc_attr( $table_width ); ?>"
	style='
		display: table;
		<?php echo esc_attr( "background-color: {$background_color};" ); ?>
		background-position: center;
		background-repeat: no-repeat;
		background-size: cover;
		background-image: url(<?php echo in_array( 'backgroundImage', $showing_items, true ) && ! empty( $background_image ) ? esc_url( $background_image ) : ''; ?>);
		'
	class="web-main-row"
  >
	<tbody>
		<tr>
			<td
			id="<?php echo esc_attr( $id ); ?>"
			style='
			<?php echo esc_attr( "padding-top: {$padding_top}px; padding-left: {$padding_left}px; padding-right: {$padding_right}px;" ); ?>
			font-family: <?php echo wp_kses_post( $family ); ?>;
			'
			>
				<table
					<?php echo esc_attr( "align= {$content_align}" ); ?>
					cellspacing="0"
					cellpadding="0"
					border="0"
					<?php echo esc_attr( "width= {$content_width}%" ); ?>
				>
					<tbody>
					<tr>
						<td>
							<div class="simple-offer-content">
								<?php echo wp_kses_post( $content ); ?>
							</div>
						</td>
					</tr>
					</tbody>
				</table>
			</td>
		</tr>
		<tr>
			<td
			id="<?php echo esc_attr( $id ); ?>"
			style='
			<?php echo esc_attr( "padding-left: {$padding_left}px; padding-right: {$padding_right}px; padding-bottom: {$padding_bottom}px; " ); ?>
			font-family: <?php echo wp_kses_post( $family ); ?>;
			'
			>
			<?php if ( in_array( 'button', $showing_items, true ) ) : ?>
				<table
				<?php echo esc_attr( "align= {$button_align}" ); ?>
				cellspacing="0"
				cellpadding="0"
				border="0"
				style="margin-top: 10px"
			>
				<tbody>
					<tr>
						<td style='font-family: <?php echo wp_kses_post( $family ); ?>;'>
							<a
								class="yaymail-inline-block"
								href="<?php echo esc_url( $button_url ); ?>"
								style='
									display: inline-block;
									font-weight: bold;
									vertical-align: middle;
									line-height: 21px;
									text-align: center;
									text-decoration: none;
									margin-top: 5px;
									font-size: 13px;
									font-weight: normal;
									padding: 10px 15px;
									<?php echo esc_attr( "background-color: {$button_background_color};" ); ?>
									<?php echo esc_attr( "color: {$button_text_color};" ); ?>
									font-family: <?php echo wp_kses_post( $family ); ?>
								'
								><?php echo esc_html( $button_text ); ?>
							</a>
						</td>
					</tr>
				</tbody>
			</table>
			<?php endif; ?>
			</td>
		</tr>
	</tbody>
</table>
