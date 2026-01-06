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

	<h1><?php esc_html_e( 'Smoobu Calendar - Data Renewal', 'smoobu-calendar' ); ?></h1>
	<p>
		<?php echo esc_html( 'To update properties and availability manually, click buttons below.', 'smoobu-calendar' ); ?>
		<?php
		// translators:data renewal page link.
		printf( wp_kses_post( 'To get availability updated automatically, please configure <a href="%s">webhook</a> settings.', 'smoobu-calendar' ), esc_url( menu_page_url( 'smoobu-calendar-settings-webhook', false ) ) );
		?>
	</p>

	<form action="" method="POST">
		<p class="submit">
			<input type="submit" name="smoobu_renewal_settings_properties" class="button button-secondary" value="<?php esc_html_e( 'Update Properties List', 'smoobu-calendar' ); ?>">
			<input type="submit" name="smoobu_renewal_settings_availability" class="button button-secondary" value="<?php esc_html_e( 'Update Properties Availability', 'smoobu-calendar' ); ?>">
		</p>
		<input type="hidden" name="smoobu_settings_nonce" value="<?php echo esc_attr( $nonce ); ?>">
	</form>
</div>
