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
	<h1><?php esc_html_e( 'Smoobu Calendar - My Properties', 'smoobu-calendar' ); ?></h1>
	<p>
		<?php
		// translators:data renewal page link.
		printf( wp_kses_post( 'If you don\'t see your properties, update properties list in <a href="%s">data renewal</a> page.', 'smoobu-calendar' ), esc_url( menu_page_url( 'smoobu-calendar-settings-renewal', false ) ) );
		?>
	</p>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<label for="smoobu-calendar-layout">
						<?php esc_html_e( 'Layout style', 'smoobu-calendar' ); ?>
					</label>
				</th>
				<td>
					<select id="smoobu-calendar-layout">
						<?php Smoobu_Utility::available_layouts_options(); ?>
					</select>
				</td>
			</tr>
		</tbody>
	</table>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Property Name', 'smoobu-calendar' ); ?></th>
				<th><?php esc_html_e( 'Shortcode', 'smoobu-calendar' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			if ( ! empty( $properties ) ) {
				foreach ( $properties as $property ) {
					?>
					<tr>
						<td>
							<?php echo esc_html( $property->name ); ?>
						</td>
						<td>
							<code class="smoobu-layout-code" data-property-id="<?php echo esc_attr( $property->id ); ?>">
								[smoobu_calendar property_id="<?php echo esc_attr( $property->id ); ?>" layout="1x3"]
							</code>
						</td>
					</tr>
					<?php
				}
			}
			?>
		</tbody>
	</table>
</div>
