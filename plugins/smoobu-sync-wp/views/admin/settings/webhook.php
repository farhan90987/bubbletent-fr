<?php
/**
 * Webhook settings view
 *
 * @package smoobu-calendar
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No skiddies please!' );
}
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Smoobu Calendar - Webhook', 'smoobu-calendar' ); ?></h1>
	<p>
		<?php esc_html_e( 'In order to receive automatic availability updates, paste the code below to Smoobu developers settings, webhookUrl field.', 'smoobu-calendar' ); ?>
	</P>
	<P>
		<?php
		// translators: link to smoobu developers settings.
		printf( wp_kses_post( 'To access developers settings, go to Smoobu Admin Panel -> Settings -> <a href="%s" target="_blank">For Developers</a>.', 'smoobu-calendar' ), esc_url( SMOOBU_DEVELOPERS_LINK ) );
		?>
	</p>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<label for="smoobu_webhook_url">
						<?php esc_html_e( 'Webhook Url', 'smoobu-calendar' ); ?>
					</label>
				</th>
				<td>
					<code><?php echo esc_url( get_rest_url( null, 'smoobu-calendar/v1/update' ) ); ?></code>
				</td>
			</tr>
		</tbody>
	</table>
</div>
