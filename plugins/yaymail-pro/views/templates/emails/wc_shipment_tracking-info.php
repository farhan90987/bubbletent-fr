<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use YayMail\Page\Source\CustomPostType;
$postID             = CustomPostType::postIDByTemplate( $this->template );
$emailTextLinkColor = get_post_meta( $postID, '_yaymail_email_textLinkColor_settings', true ) ? get_post_meta( $postID, '_yaymail_email_textLinkColor_settings', true ) : '#7f54b3';
$borderColor        = isset( $atts['bordercolor'] ) && $atts['bordercolor'] ? 'border-color:' . html_entity_decode( $atts['bordercolor'], ENT_QUOTES, 'UTF-8' ) : 'border-color:inherit';
$textColor          = isset( $atts['textcolor'] ) && $atts['textcolor'] ? 'color:' . html_entity_decode( $atts['textcolor'], ENT_QUOTES, 'UTF-8' ) : 'color:inherit';
$fontFamily         = isset( $atts['fontfamily'] ) && $atts['fontfamily'] ? 'font-family:' . html_entity_decode( $atts['fontfamily'], ENT_QUOTES, 'UTF-8' ) : 'font-family:inherit';

if ( $tracking_items ) : ?>
	<table class="yaymail_builder_table_items_border yaymail_builder_table_tracking_item" cellspacing="0" cellpadding="6" border="1" style="width: 100% !important;<?php echo esc_attr( $borderColor ); ?>" width="100%">

		<thead>
			<tr style="<?php echo esc_attr( $textColor ); ?>">
				<th class="td" colspan="<?php echo esc_html( 'WC_Shipment_Tracking_Actions' == $setClassAvtive ? 1 : 2 ); ?>" scope="col" class="td" style="text-align: left;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>"><?php esc_html_e( 'Provider', 'woocommerce-shipment-tracking' ); ?></th>
				<th class="td" scope="col" class="td" style="text-align: left;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>"><?php esc_html_e( 'Tracking Number', 'woocommerce-shipment-tracking' ); ?></th>
				<th class="td" scope="col" class="td" style="text-align: left;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>"><?php esc_html_e( 'Date', 'woocommerce-shipment-tracking' ); ?></th>
				<?php if ( isset( $tracking_items[0]['formatted_tracking_link'] ) ) { ?>
				<th class="td" scope="col" class="td" style="text-align: left;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>">&nbsp;</th>
				<?php } ?>
			</tr>
		</thead>

		<tbody style="flex-direction:inherit;">
		<?php
		foreach ( $tracking_items as $tracking_item ) {
			?>
				<tr class="tracking " style="flex-direction:inherit;<?php echo esc_attr( $textColor ); ?>">
					
					<?php if ( 'WC_Shipment_Tracking_Actions' == $setClassAvtive ) { ?>
						<td class="td" data-title="<?php esc_html_e( 'Provider', 'woocommerce-shipment-tracking' ); ?>" style="text-align: left; padding: 12px;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>">
							<?php echo esc_html( null != $tracking_item['custom_tracking_provider'] ? $tracking_item['custom_tracking_provider'] : $tracking_item['tracking_provider'] ); ?>
						</td>
						<?php
					} else {
						global $wpdb;

						$tracking_provider = isset( $tracking_item['tracking_provider'] ) ? $tracking_item['tracking_provider'] : $tracking_item['custom_tracking_provider'];
						if ( $tracking_provider ) {
							$tracking_provider = apply_filters( 'convert_provider_name_to_slug', $tracking_provider );

							$results = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woo_shippment_provider WHERE ts_slug = %s", $tracking_provider ) );

							$provider_name = apply_filters( 'get_ast_provider_name', $tracking_provider, $results );
						} else {
							$provider_name = $tracking_item['custom_tracking_provider'];
						}

						?>
						<td class="td" data-title="<?php esc_html_e( 'Provider', 'woocommerce-shipment-tracking' ); ?>" style="text-align: left; padding: 12px;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>">
							<img style="width: 50px;vertical-align: middle;max-width:unset;margin:0;" src="<?php echo esc_html( apply_filters( 'get_shipping_provdider_src', $results ) ); ?>">
						</td>
						<td class="td" data-title="<?php esc_html_e( 'Provider', 'woocommerce-shipment-tracking' ); ?>" style="text-align: left; padding: 12px;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>">
						<?php echo esc_html( apply_filters( 'ast_provider_title', esc_html( $provider_name ) ) ); ?>
						</td>
					<?php } ?> 

					<td class="td" data-title="<?php esc_html_e( 'Tracking Number', 'woocommerce-shipment-tracking' ); ?>" style="text-align: left; padding: 12px;;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>">
						<?php if ( isset( $tracking_item['tracking_number'] ) ) { ?>
							<?php echo esc_html( $tracking_item['tracking_number'] ); ?>
						<?php } ?>
					</td>
					<td class="td" data-title="<?php esc_html_e( 'Status', 'woocommerce-shipment-tracking' ); ?>" style="text-align: left; padding: 12px;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>">
						<?php if ( isset( $tracking_item['date_shipped'] ) ) { ?>
							<time datetime="<?php echo esc_attr( gmdate( 'Y-m-d', $tracking_item['date_shipped'] ) ); ?>" title="<?php echo esc_attr( gmdate( 'Y-m-d', $tracking_item['date_shipped'] ) ); ?>"><?php echo esc_html( date_i18n( get_option( 'date_format' ), $tracking_item['date_shipped'] ) ); ?></time>
						<?php } ?>
					</td>
					<?php if ( isset( $tracking_item['formatted_tracking_link'] ) ) { ?>
					<td class="td" style="text-align: center; padding: 12px;<?php echo esc_attr( $borderColor ); ?>;<?php echo esc_attr( $fontFamily ); ?>">
						<a style="color: <?php echo esc_attr( $emailTextLinkColor ); ?>" href="<?php echo esc_url( $tracking_item['formatted_tracking_link'] ); ?>" target="_blank"><?php esc_html_e( 'Track', 'woocommerce-shipment-tracking' ); ?></a>
					</td>
					<?php } ?>
				</tr>
				<?php
		}
		?>
		</tbody>
	</table>
	<?php
	endif;
