<?php
/**
 * General settings view
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

	<h1><?php esc_html_e( 'Smoobu Calendar - General Settings', 'smoobu-calendar' ); ?></h1>

	<p>
		<?php esc_html_e( 'You can find your API key in Smoobu developers settings, API key field.', 'smoobu-calendar' ); ?>
	</P>
	<P>
		<?php
		// translators: link to smoobu developers settings.
		printf( wp_kses_post( 'To access developers settings, go to Smoobu Admin Panel -> Settings -> <a href="%s" target="_blank">For Developers</a>.', 'smoobu-calendar' ), esc_url( SMOOBU_DEVELOPERS_LINK ) );
		?>
	</p>

	<form action="" method="POST">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="smoobu_api_key">
							<?php esc_html_e( 'API Key', 'smoobu-calendar' ); ?>
						</label>
					</th>
					<td>
						<input type="text" name="smoobu_api_key" id="smoobu_api_key" value="<?php echo esc_attr( get_option( 'smoobu_api_key' ) ); ?>" class="regular-text">
						<p class="smoobu-connection-message"></p>
						<p>
							<input type="button" id="smoobu_api_check_connection" name="smoobu_api_check_connection" class="button button-secondary" value="<?php esc_html_e( 'Check Connection', 'smoobu-calendar' ); ?>">
						</p>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" name="smoobu_general_settings_save" class="button button-primary" value="<?php esc_html_e( 'Save Changes', 'smoobu-calendar' ); ?>">
		</p>
		<input type="hidden" name="smoobu_settings_nonce" value="<?php echo esc_attr( $nonce ); ?>">
	</form>
</div>
