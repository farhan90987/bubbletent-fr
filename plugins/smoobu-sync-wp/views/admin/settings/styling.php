<?php
/**
 * Styling settings view
 *
 * @package smoobu-calendar
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No skiddies please!' );
}
?>
<div class="wrap">
	<?php
	Smoobu_Main::load_template(
		'admin/settings/parts/notice',
		array(
			'success' => $success,
			'message' => $message,
		)
	);
	?>

	<h1><?php esc_html_e( 'Smoobu Calendar - Styling', 'smoobu-calendar' ); ?></h1>

	<form action="" method="POST">

		<h2 class="title"><?php esc_html_e( 'Settings', 'smoobu-calendar' ); ?></h2>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="smoobu_calendar_theme">
							<?php esc_html_e( 'Calendar Theme', 'smoobu-calendar' ); ?>
						</label>
					</th>
					<td>
						<select id="smoobu_calendar_theme" name="smoobu_calendar_theme">
							<?php Smoobu_Utility::available_themes_options( $current_theme ); ?>
						</select>
						<p class="description"><?php echo wp_kses_post( __( '<b>Warning!</b> Changing the theme will revoke all style customizations.', 'smoobu-calendar' ) ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Full Width Layout', 'smoobu-calendar' ); ?>
					</th>
					<td>
						<fieldset>
							<label for="smoobu_full_width">
								<input name="smoobu_full_width" type="checkbox" id="smoobu_full_width" value="1" <?php checked( get_option( 'smoobu_full_width' ) ); ?>>
								<?php esc_html_e( 'Stretch calendar to full width', 'smoobu-calendar' ); ?>
							</label>
						</fieldset>
						<p class="description">
							<?php esc_html_e( 'If checked, calendar will be always stretched to the width of the container.', 'smoobu-calendar' ); ?>
						</P>
					</td>
				</tr>
			</tbody>
		</table>

		<h2 class="title"><?php esc_html_e( 'Style Customization', 'smoobu-calendar' ); ?></h2>
		<div class="smoobu-styling-container">
			<div class="smoobu-styling-table">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<?php esc_html_e( 'Custom Styling', 'smoobu-calendar' ); ?>
							</th>
							<td>
								<fieldset>
									<label for="smoobu_custom_styling">
										<input name="smoobu_custom_styling" type="checkbox" id="smoobu_custom_styling" value="1" <?php checked( get_option( 'smoobu_custom_styling' ) ); ?>>
										<?php esc_html_e( 'Use custom styles', 'smoobu-calendar' ); ?>
									</label>
								</fieldset>
								<p class="description">
									<?php esc_html_e( 'If checked, settings below will be used instead of default theme settings.', 'smoobu-calendar' ); ?>
								</P>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<?php esc_html_e( 'Border Shadow', 'smoobu-calendar' ); ?>
							</th>
							<td>
								<fieldset>
									<label for="smoobu_custom_styling_border_shadow">
										<input name="smoobu_custom_styling_border_shadow" type="checkbox" id="smoobu_custom_styling_border_shadow" value="1" <?php checked( $styling_settings['border_shadow'] ); ?>>
										<?php esc_html_e( 'Show shadow around calendar borders', 'smoobu-calendar' ); ?>
									</label>
								</fieldset>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="smoobu_custom_styling_border_radius">
									<?php esc_html_e( 'Border Radius', 'smoobu-calendar' ); ?>
								</label>
							</th>
							<td>
								<input type="text" name="smoobu_custom_styling_border_radius" id="smoobu_custom_styling_border_radius" value="<?php echo esc_attr( $styling_settings['border_radius'] ); ?>" class="small-text">
								<?php esc_html_e( 'px', 'smoobu-calendar' ); ?>
							</td>
						</tr>
						<?php
						if ( ! empty( $styling_settings['colors'] ) ) {
							foreach ( $styling_settings['colors'] as $key => $color ) {
								?>
								<tr>
									<th scope="row">
										<label for="smoobu_custom_styling_color_<?php echo esc_attr( $key ); ?>">
											<?php echo esc_html( $translations[ $key ] ); ?>
										</label>
									</th>
									<td>
										<input type="text" name="smoobu_custom_styling_color_<?php echo esc_attr( $key ); ?>" id="smoobu_custom_styling_color_<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $color ); ?>" class="smoobu-color-picker" data-default-color="<?php echo esc_attr( $default_settings['colors'][ $key ] ); ?>">
									</td>
								</tr>
								<?php
							}
						}
						?>
					</tbody>
				</table>

				<p class="submit">
					<input type="submit" name="smoobu_styling_settings_save" class="button button-primary" value="<?php esc_html_e( 'Save Changes', 'smoobu-calendar' ); ?>">
					<input type="submit" name="smoobu_styling_settings_reset" class="button button-secondary" value="<?php esc_html_e( 'Reset Default Styling', 'smoobu-calendar' ); ?>" onclick="return confirm('<?php esc_html_e( 'Are you sure? This action will unretrievably reset all your custom styling!', 'smoobu-calendar' ); ?>');">
				</p>
				<input type="hidden" name="smoobu_settings_nonce" value="<?php echo esc_attr( $nonce ); ?>">
			</div>
			<div class="smoobu-styling-preview">
				<div class="smoobu-calendar"></div>
			</div>
	</form>
</div>
