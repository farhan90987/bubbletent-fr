<table class="form-table wpwoof-addfeed-top">
    <tr class="addfeed-top-field">
        <th class="addfeed-top-label addfeed-bigger">Feed's Name:</th>
        <td class="addfeed-top-value">
			<?php
			$data_for_feed_name = '';
			if ( ! empty( $wpwoof_values['feed_type'] ) && in_array( $wpwoof_values['feed_type'], array(
				'fb_localize',
				'fb_country',
				'google_local_inventory'
			) )  ) { //trace($wpwoof_values);
			?>
            <input type="hidden" name="main_feed_id"
                   value="<?php echo $wpwoof_values['main_feed_id']; ?>"
                   style="display:none"/>
			<?php
			if ( ! empty( $wpwoof_values['main_feed_name'] ) ) {

				$data_for_feed_name .= ' data-main-feed-name="' . esc_attr( $wpwoof_values['main_feed_name'] ) . '"';

				$main_feed_edit_link = add_query_arg( array(
					'tab'      => 1,
					'edit'     => $wpwoof_values['main_feed_id'],
					'_wpnonce' => wp_create_nonce( 'wooffeed-nonce' )
				), menu_page_url( 'wpwoof-settings', false ) );

			}
			}
			?>
            <input type="text" id="idFeedName" name="feed_name"
                   value="<?php echo isset( $wpwoof_values['feed_name'] ) ? esc_html( $wpwoof_values['feed_name'] ) : '';
			       echo '"' . $data_for_feed_name ?>/>
		    <?php
			       if ( ! empty( $wpwoofeed_oldname ) ) { ?>
                <input type=" hidden" name="old_feed_name" value="<?php echo $wpwoofeed_oldname; ?>"
            style="display:none"/>
			<?php } ?>
        </td>
    </tr>
    <tr class="addfeed-top-field">
        <th class="addfeed-top-label addfeed-bigger">Feed's Type:</th>
        <td class="addfeed-top-value">
			<?php if ( isset( $_GET['edit'] ) || ! empty( $_GET['feed_type'] ) ) {
				global $woocommerce_wpwoof_common;

				$feed_type_id = 'facebook';
				if ( ! empty( $wpwoof_values['feed_type'] ) ) {
					$feed_type_id = $wpwoof_values['feed_type'];
				} elseif ( ! empty( $_GET['feed_type'] ) ) {
					$feed_type_id = esc_html( $_GET['feed_type'] );
				}

				echo isset( $woocommerce_wpwoof_common->feed_type_name[ $feed_type_id ] ) ? esc_html( $woocommerce_wpwoof_common->feed_type_name[ $feed_type_id ] ) : "Facebook Product Catalog";

				echo '<input id="ID-feed_type" type="hidden" name="feed_type" value="' . esc_attr( $feed_type_id ) . '" style="display:none" />';
			} else { ?>
                <select id="ID-feed_type" name="feed_type" onchange="jQuery.fn.toggleFeedField(this.value);">
                    <option <?php if ( isset( $wpwoof_values['feed_type'] ) ) {
						selected( "facebook", $wpwoof_values['feed_type'], true );
					} ?> value="facebook">Facebook Product Catalog
                    </option>
                    <option <?php if ( isset( $wpwoof_values['feed_type'] ) ) {
						selected( "google", $wpwoof_values['feed_type'], true );
					} ?> value="google">Google Merchant
                    </option>
                    <option <?php if ( isset( $wpwoof_values['feed_type'] ) ) {
						selected( "adsensecustom", $wpwoof_values['feed_type'], true );
					} ?> value="adsensecustom">Google Adwords Remarketing Custom
                    </option>
                    <option <?php if ( isset( $wpwoof_values['feed_type'] ) ) {
						selected( "pinterest", $wpwoof_values['feed_type'], true );
					} ?> value="pinterest">Pinterest
                    </option>
                    <option <?php if ( isset( $wpwoof_values['feed_type'] ) ) {
						selected( "tiktok", $wpwoof_values['feed_type'], true );
					} ?> value="tiktok">TikTok
                    </option>
                    <option <?php if ( isset( $wpwoof_values['feed_type'] ) ) {
						selected( "googleReviews", $wpwoof_values['feed_type'], true );
					} ?> value="googleReviews">Reviews for Google Merchant
                    </option>
                </select>
			<?php } ?>
        </td>
    </tr>
	<?php
	if ( isset( $wpwoof_values['feed_file_name'] ) ) {
		$file_name = $wpwoof_values['feed_file_name'];
	} elseif ( ! empty( $wpwoof_values['feed_name'] ) ) {
		$file_name = strtolower( str_replace( ' ', '-', trim( $wpwoof_values['feed_name'] ) ) );
	} else {
		$file_name = '';
	}
	?>
    <tr class="addfeed-top-field">
        <th class="addfeed-top-label addfeed-bigger">File name (without extension):</th>
        <td class="addfeed-top-value">
            <input type="text" id="feed_file_name" name="feed_file_name"
                   value="<?php echo esc_html( $file_name ); ?>"/>
        </td>
    </tr>
	<?php
	if ( ! empty( $wpwoof_values['feed_type'] ) && $wpwoof_values['feed_type'] == 'fb_country' ) {
		?>
        <tr class="addfeed-top-field">
            <th class="addfeed-top-label addfeed-bigger">Country:</th>
            <td class="addfeed-top-value">
                <select name="country">
					<?php
					$wpwoof_values['country'] = $wpwoof_values['country'] ?? '';
					foreach ( WC()->countries->get_allowed_countries() as $country_code => $country_name ) {

						echo '<option value="' . $country_code . '" ' . selected( $wpwoof_values['country'], $country_code ) . '>' . $country_name . '</option>';
					}
					?>
                </select>
            </td>
        </tr>
        <tr class="addfeed-top-field">
            <th class="addfeed-top-label addfeed-bigger" colspan="2">
                <label style="display: inline;">
                    <input type="checkbox" class="wpwoof-mapping"
                           value="1" name="fb_country_exclude_fields"
					<?php checked( $wpwoof_values['fb_country_exclude_fields'] ?? 0 ) ?>"/>
                    Exclude fields which can be present in the Language feed</label>
            </th>
        </tr>
		<?php
	}
	if ( empty( $wpwoof_values['feed_type'] ) || ! in_array( $wpwoof_values['feed_type'], array(
			'fb_localize',
			'fb_country',
			'google_local_inventory'
		) ) ) :
		?>
        <tr class="addfeed-top-field">
            <th class="addfeed-top-label addfeed-bigger">Regenerate Feed:</th>
            <td class="addfeed-top-value">
                <select name="feed_interval">
					<?php
					$current_interval = isset( $wpwoof_values['feed_interval'] ) ? $wpwoof_values['feed_interval'] : 0;
					$intervals        = array(
						'0'      => 'Global settings',
						'3600'   => 'Hourly',
						'86400'  => 'Daily',
						'43200'  => 'Twice daily',
						'604800' => 'Weekly'
					);
					foreach ( $intervals as $interval => $interval_name ) {
						echo '<option ' . selected( $interval, $current_interval, false ) . ' value="' . $interval . '">' . $interval_name . '</option>';
					}
					?>
                </select>
            </td>
        </tr>
        <tr class="addfeed-top-field">
            <th class="addfeed-top-label addfeed-bigger">Start regeneration from:</th>
            <td class="addfeed-top-value">
                <input type="time" name="feed_schedule_from"
                       value="<?= isset( $wpwoof_values['feed_schedule_from'] ) ? $wpwoof_values['feed_schedule_from'] : ''; ?>">
            </td>
        </tr>
	<?php
	else:
		$override_notice = array(
			'fb_country'  => 'The default country code is taken from your Woocommerce settings. Override the country code ONLY if it does not correspond to <a target="_blank" href="https://www.facebook.com/business/help/2144286692311411?id=725943027795860">Meta requirements</a>.',
			'fb_localize' => 'The default locale is taken from your WMPL language settings. Override the language code ONLY if it does not correspond to <a target="_blank" href="https://www.facebook.com/business/help/2144286692311411?id=725943027795860">Meta requirements</a>.',
		)
		?>
		<?php if ( $wpwoof_values['feed_type'] == 'fb_localize' ) : ?>
        <tr class="addfeed-top-field">
            <th class="addfeed-top-label addfeed-bigger">Override:</th>
            <td class="addfeed-top-value">
                <input type="text" id="custom_override" name="custom_override"
                       value="<?= $wpwoof_values['custom_override'] ?? ''; ?>"/>
            </td>
        </tr>
	<?php elseif ( $wpwoof_values['feed_type'] == 'google_local_inventory' ): ?>
        <tr class="addfeed-top-field">
            <th class="addfeed-top-label addfeed-bigger">Store code:</th>
            <td class="addfeed-top-value">
                <input type="text" id="store_code" name="store_code"
                       value="<?= $wpwoof_values['store_code'] ?? ''; ?>"/>
            </td>
        </tr>
	<?php endif;

		if ( ! empty( $override_notice[ $wpwoof_values['feed_type'] ] ) ):
			?>
            <tr>
                <td colspan="2">
					<?= $override_notice[ $wpwoof_values['feed_type'] ] ?>
                </td>
            </tr>
		<?php
		endif;
		?>

        <tr class="addfeed-top-field">
            <th class="addfeed-top-label addfeed-bigger">Parent feed:</th>
            <td class="addfeed-top-value">
				<?php echo empty( $wpwoof_values['main_feed_name'] ) ? _( 'Feed not found' ) : "<a href='" . $main_feed_edit_link . "'>" . esc_html( $wpwoof_values['main_feed_name'] ) . "</a>"; ?>
            </td>
        </tr>
	<?php
	endif;
	?>
</table>