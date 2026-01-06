<?php
/**
 * BOGO listing page
 *
 * @since 2.0.0
 * @package    Wt_Smart_Coupon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$is_trash_segment = isset( $_GET['listing_status'] ) && 'trash' === $_GET['listing_status'];
$bogo_coupon_ids  = $is_trash_segment ? self::get_bogo_coupons_list( array( 'is_trash' => true ) ) : self::get_bogo_coupons_list();
$trash_count      = self::get_total_bogo_counts( array( 'is_trash' => true ) );
$all_bogo_count   = self::get_total_bogo_counts( array( 'is_trash' => false ) );

?>

<div class="wbte_sc_bogo_listings_head">
	<div class="wbte_sc_bogo_listings_head_left">
		<h2><?php esc_html_e( 'BOGO offers', 'wt-smart-coupons-for-woocommerce' ); ?></h2>
		<?php
		echo $ds_obj->get_component(
			'button filled medium',
			array(
				'values' => array(
					'button_title' => esc_html__( 'Add new', 'wt-smart-coupons-for-woocommerce' ),
					'icon_left'    => 'plus',
				),
				'class'  => array( 'wbte_sc_add_new_bogo' ),
			)
		);
		?>
		<div class="wbte_sc_bogo_add_new_popup">
			<form id="wbte_sc_new_bogo_coupon" method="POST">
				<h3><?php esc_html_e( 'Create new BOGO offer', 'wt-smart-coupons-for-woocommerce' ); ?></h3>
				<div class="wbte_sc_bogo_add_new_popup_predefined">
					<!-- Buy X Get X @ 50% -->
					<p data-default-btn="default_1" data-desc="<?php esc_html_e( 'Buy 1 item, get 1 @ 50% off. Buy 2 items, get 2 @ 50% off — and so on! Buy more, Get more', 'wt-smart-coupons-for-woocommerce' ); ?>"><?php esc_html_e( 'Buy X Get X @ 50%', 'wt-smart-coupons-for-woocommerce' ); ?></p>

					<!-- Buy 2 Get a free gift -->
					<p data-default-btn="default_2" data-desc="<?php esc_html_e( 'Buy two products, get a free gift.', 'wt-smart-coupons-for-woocommerce' ); ?>"><?php esc_html_e( 'Buy 2 Get a free gift', 'wt-smart-coupons-for-woocommerce' ); ?></p>

					<!-- Spend X Get a free gift -->
					<?php
						// Translators: 1: Min amount, here 100.
						$_default_3_btn_data = sprintf( __( 'Spend %s or above, get a free gift', 'wt-smart-coupons-for-woocommerce' ), wp_strip_all_tags( wc_price( 100, array( 'decimals' => 0 ) ) ) );
					?>
					<p data-default-btn="default_3" data-desc="<?php echo esc_attr( $_default_3_btn_data ); ?>">
						<?php
						// Translators: 1: Min amount, here 100.
						echo wp_kses_post( sprintf( __( 'Spend %s, Get a free gift', 'wt-smart-coupons-for-woocommerce' ), wc_price( 100, array( 'decimals' => 0 ) ) ) );
						?>
					</p>

					<!-- Custom -->
					<p class="custom"  data-default-btn="custom" data-desc="<?php esc_html_e( 'Create Custom BOGO Offer', 'wt-smart-coupons-for-woocommerce' ); ?>"><?php esc_html_e( '+ Custom', 'wt-smart-coupons-for-woocommerce' ); ?></p>
				</div>
				<div class="wbte_sc_bogo_campaign_custom_radio" hidden>
					<?php
					echo $ds_obj->get_component(
						'radio-group multi-line',
						array(
							'values' => array(
								'name'  => 'wbte_sc_bogo_type',
								'items' => array(
									array(
										'label'      => esc_html__( 'Buy product X, get product X/Y', 'wt-smart-coupons-for-woocommerce' ),
										'value'      => 'wbte_sc_bogo_bxgx',
										'is_checked' => true,
									),
									array(
										'label'      => esc_html__( 'Cheapest/Most Expensive', 'wt-smart-coupons-for-woocommerce' ) . wp_kses_post( '<img src="' . esc_url( $admin_img_path . 'prem_crown_2.svg' ) . '" alt="' . esc_attr__( 'premium', 'wt-smart-coupons-for-woocommerce' ) . '" />' ),
										'value'      => 'wbte_sc_bogo_cheap_expensive',
										'is_checked' => false,
									),
								),
							),
						)
					);
					?>
					<br>
					<img class="wbte_sc_bogo_custom_bogo_img" src="<?php echo esc_url( $admin_img_path ); ?>custom_bxgx.svg" alt="<?php esc_attr_e( 'Buy product X, get product X/Y', 'wt-smart-coupons-for-woocommerce' ); ?>">
				</div>

				<div class="wbte_sc_cheap_exp_promo_div">
					<img class="wbte_sc_cheap_exp_promo_img" src="<?php echo esc_url( $admin_img_path . 'cheap_exp_promo.svg' ); ?>" alt="<?php esc_attr_e( 'Cheapest/Most Expensive', 'wt-smart-coupons-for-woocommerce' ); ?>">
					<div class="wbte_sc_cheap_exp_promo_detail_div">
						<p>
							<?php esc_html_e( 'Upgrade to premium and offer discounts on the cheapest or most expensive items in the cart.', 'wt-smart-coupons-for-woocommerce' ); ?>
						</p>
					</div>

					<div class="wbte_sc_bogo_add_new_popup_buttons">
						<?php
						echo $ds_obj->get_component(
							'button text medium',
							array(
								'values' => array(
									'button_title' => esc_html__( 'Cancel', 'wt-smart-coupons-for-woocommerce' ),
								),
								'class'  => array( 'wbte_sc_bogo_add_new_cancel' ),
							)
						);

						?>
						<a href="<?php echo esc_url( 'https://www.webtoffee.com/product/smart-coupons-for-woocommerce/?utm_source=free_plugin_bogo_popup&utm_medium=smart_coupons_basic&utm_campaign=smart_coupons&utm_content=' . WEBTOFFEE_SMARTCOUPON_VERSION ) ?>" target="_blank" class="wbte_sc_button wbte_sc_button-filled wbte_sc_button-medium" style="text-decoration: none;"><?php esc_html_e( 'Get Premium Now', 'wt-smart-coupons-for-woocommerce' ); ?></a>
					</div>
				</div>

				<div class="wbte_sc_bogo_add_new_popup_form">
					<input type="text" id="wbte_sc_bogo_coupon_name" name="wbte_sc_bogo_coupon_name" class="wbte_sc_bogo_text_input" placeholder="<?php esc_attr_e( 'Offer title', 'wt-smart-coupons-for-woocommerce' ); ?>" required><br>

					<textarea type="text" id="wbte_sc_bogo_campaign_description" name="wbte_sc_bogo_campaign_description" class="wbte_sc_bogo_text_input" placeholder="<?php esc_attr_e( 'Description', 'wt-smart-coupons-for-woocommerce' ); ?>" rows="5" ></textarea>

					<input type="hidden" id="wbte_sc_bogo_campaign_selected_default" name="wbte_sc_bogo_campaign_selected_default" value="">
					<div class="wbte_sc_bogo_add_new_popup_buttons">
						<?php
						echo $ds_obj->get_component(
							'button text medium',
							array(
								'values' => array(
									'button_title' => esc_html__( 'Cancel', 'wt-smart-coupons-for-woocommerce' ),
								),
								'class'  => array( 'wbte_sc_bogo_add_new_cancel' ),
							)
						);

						echo $ds_obj->get_component(
							'button filled medium',
							array(
								'values' => array(
									'button_title' => esc_html__( 'Continue', 'wt-smart-coupons-for-woocommerce' ),
								),
								'class'  => array( 'wbte_sc_bogo_add_new_continue' ),
							)
						);
						?>
					</div>
				
				</div>
			</form>
		</div>
	</div>
	<div class="wbte_sc_bogo_listings_head_right">
		<div class="wbte_sc_bogo_listings_head_search">
			<input type="text" name="wbte_bogo_search" id="wbte_bogo_search" placeholder="<?php esc_attr_e( 'Search', 'wt-smart-coupons-for-woocommerce' ); ?>" value="<?php echo isset( $_GET['search'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['search'] ) ) ) : ''; ?>">
		
			<span class="wbte_bogo_search_icon" style="height: 14px;" title="<?php esc_attr_e( 'Search BOGO', 'wt-smart-coupons-for-woocommerce' ); ?>"><?php echo wp_kses_post( $ds_obj->render_html( array( 'html' => '{{wbte-ds-icon-search}}' ) ) ); ?></span>
		</div>
	</div>
</div>

<div class="wbte_sc_bogo_listing_sub_head">

	<div class="wbte_sc_segments wbte_sc_bogo_listing_status_segments">
		<a data-target-id="wbte_sc_bogo_listing_status_all" class="wbte_sc_segment <?php echo $is_trash_segment ? '' : 'active'; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::$bogo_page_name ) ); ?>"> 
			<span class="wbte_sc_segment_text">
				<?php
				// Translators: 1: all coupon count.
				printf( esc_html__( 'All ( %d )', 'wt-smart-coupons-for-woocommerce' ), esc_html( $all_bogo_count ) );
				?>
			</span>
		</a>
		<a data-target-id="wbte_sc_bogo_listing_status_trash" class="wbte_sc_segment 
		<?php
		echo $is_trash_segment ? 'active' : '';
		echo 1 > $trash_count ? ' disabled' : '';
		?>
		" href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::$bogo_page_name . '&listing_status=trash' ) ); ?>"> 
			<span class="wbte_sc_segment_text">
				<?php
				// Translators: 1: trash coupon count.
				printf( esc_html__( 'Trash ( %d )', 'wt-smart-coupons-for-woocommerce' ), esc_html( $trash_count ) );
				?>
			</span>
		</a>
	</div>

	<div class="wbte_sc_bogo_listing_selected_div">
		<p class="wbte_sc_bogo_listing_selected_div_select_count"><?php esc_html_e( 'selected', 'wt-smart-coupons-for-woocommerce' ); ?></p>
		<?php if ( ! $is_trash_segment ) { ?>
			<?php
			echo $ds_obj->get_component(
				'button tonal medium',
				array(
					'values' => array(
						'button_title' => esc_html__( 'Enable all', 'wt-smart-coupons-for-woocommerce' ),
					),
					'class'  => array( 'wbte_sc_bogo_listing_selected_enable' ),
				)
			);

			echo $ds_obj->get_component(
				'button tonal medium',
				array(
					'values' => array(
						'button_title' => esc_html__( 'Disable all', 'wt-smart-coupons-for-woocommerce' ),
					),
					'class'  => array( 'wbte_sc_bogo_listing_selected_disable' ),
				)
			);
			?>
			<span style="height: 24px;"  class="wbte_sc_bogo_multiple_trash"><?php echo wp_kses_post( $ds_obj->render_html( array( 'html' => '{{wbte-ds-icon-trash}}' ) ) ); ?></span>
			<?php
		} else {
			echo $ds_obj->get_component(
				'button tonal medium',
				array(
					'values' => array(
						'button_title' => esc_html__( 'Restore', 'wt-smart-coupons-for-woocommerce' ),
					),
					'class'  => array( 'wbte_sc_bogo_listing_selected_restore' ),
				)
			);

			echo $ds_obj->get_component(
				'button tonal medium',
				array(
					'values' => array(
						'button_title' => esc_html__( 'Delete permanently', 'wt-smart-coupons-for-woocommerce' ),
					),
					'class'  => array( 'wbte_sc_bogo_listing_selected_perm_delete' ),
					'attr'   => array(
						'data-wbte_sc_popup' => 'wbte_sc_bogo_delete_popup_multiple',
					),
				)
			);

		}
		?>
	</div>

</div>

<table class="wbte_sc_bogo_listing_table">
	<thead>
		<tr class="wbte_sc_bogo_listing_table_head">
			<td class="wbte_sc_bogo_listing_table_checkbox">
				<?php
				echo $ds_obj->get_component(
					'checkbox master',
					array(
						'values' => array(
							'name'     => 'wbte_sc_bogo_listing_check_all',
							'id'       => 'wbte_sc_bogo_listing_check_all',
							'group_id' => 'wbte_sc_bogo_listing_checkbox',
						),
					)
				);
				?>
			</td>
			<td style="width:13%;"><?php esc_html_e( 'Bogo name', 'wt-smart-coupons-for-woocommerce' ); ?></td>
			<td class="wbte_sc_bogo_hide_md" style="width:12%;"><?php esc_html_e( 'Description', 'wt-smart-coupons-for-woocommerce' ); ?></td>
			<td>
				<?php
				esc_html_e( 'Status', 'wt-smart-coupons-for-woocommerce' );
				if ( ! $is_trash_segment ) {
					echo '<span class="wbte_sc_bogo_status_filtering">';
					echo $ds_obj->render_html( array( 'html' => '{{wbte-ds-icon-equalizer-alt}}' ) );
					echo '</span>';
					echo '<div class="wbte_sc_bogo_edit_custom_drop_down wbte_sc_bogo_listing_status_filter_dropdown">';

					$listing_filters_selected = Wt_Smart_Coupon_Security_Helper::sanitize_item( isset( $_GET['listing_filters'] ) ? wp_unslash( $_GET['listing_filters'] ) : array( 'publish', 'draft', 'expired' ), 'text_arr' );
					$listing_filter_checkbox  = array(
						'publish' => __( 'Active', 'wt-smart-coupons-for-woocommerce' ),
						'draft'   => __( 'Inactive', 'wt-smart-coupons-for-woocommerce' ),
						'expired' => __( 'Expired', 'wt-smart-coupons-for-woocommerce' ),
					);

					foreach ( $listing_filter_checkbox as $filter_value => $filter_label ) {
						echo $ds_obj->get_component(
							'checkbox normal',
							array(
								'values' => array(
									'name'       => 'wbte_sc_bogo_listing_filters[]',
									'id'         => esc_attr( "wbte_sc_bogo_{$filter_value}" ),
									'value'      => esc_attr( $filter_value ),
									'is_checked' => esc_attr( in_array( $filter_value, $listing_filters_selected, true ) ),
									'label'      => esc_attr( $filter_label ),
								),
							)
						);
					}

					echo '</div>';
				}
				?>
			</td>
			<td class="wbte_sc_bogo_hide_md"><?php esc_html_e( 'Conversion', 'wt-smart-coupons-for-woocommerce' ); ?></td>
			<td class="wbte_sc_bogo_hide_sm"><?php esc_html_e( 'Schedule', 'wt-smart-coupons-for-woocommerce' ); ?></td>
			<td class="wbte_sc_bogo_listing_table_actions"><?php esc_html_e( 'Actions', 'wt-smart-coupons-for-woocommerce' ); ?></td>
		</tr>
		<tr></tr>
		<tr></tr>
	</thead>
	<tbody>
		<?php
		if ( empty( $bogo_coupon_ids ) ) {
			?>
				<tr>
					<td colspan="7" style="text-align: center; border-right: none;"><?php esc_html_e( 'No BOGO offers found', 'wt-smart-coupons-for-woocommerce' ); ?></td>
				</tr>
				<?php
		}
			$status_class_label = array(
				'success' => __( 'Active', 'wt-smart-coupons-for-woocommerce' ),
				'warning' => __( 'Inactive', 'wt-smart-coupons-for-woocommerce' ),
				'failed'  => __( 'Expired', 'wt-smart-coupons-for-woocommerce' ),
			);
			foreach ( $bogo_coupon_ids as $key => $coupon_id ) {
				$coupon = new WC_Coupon( $coupon_id );

				$status_class = 'publish' === $coupon->get_status() ? 'success' : 'warning';
				if ( $coupon->get_date_expires() && time() > $coupon->get_date_expires()->getTimestamp() ) {
					$status_class = 'failed';
				}

				$start_date  = get_post_meta( $coupon_id, '_wt_coupon_start_date', true );
				$expire_date = ! is_null( $coupon->get_date_expires() ) ? $coupon->get_date_expires()->date( 'Y-m-d' ) : '';

				$schedule_text = '—';
				if ( ! empty( $start_date ) && ! empty( $expire_date ) ) {
					$schedule_text = sprintf( wp_kses_post( '%s to %s' ), esc_html( $start_date ) . '<span>', '</span>' . esc_html( $expire_date ) );
				} elseif ( ! empty( $start_date ) ) {
					$schedule_text = sprintf( wp_kses_post( '%s to %s &infin;' ), esc_html( $start_date ) . '<span>', '</span>' );
				} elseif ( ! empty( $expire_date ) ) {
					$schedule_text = sprintf( wp_kses_post( '%s Till %s' ), '<span>', '</span>' . esc_html( $expire_date ) );
				}
				?>
				<tr data-coupon_id="<?php echo esc_attr( $coupon_id ); ?>" data-edit-url='<?php echo esc_url( admin_url( 'admin.php?page=' . self::$bogo_page_name . '&wbte_bogo_id=' . $coupon_id ) ); ?>' >
					<td class="wbte_sc_bogo_listing_checkbox_td">
						<?php
						echo $ds_obj->get_component(
							'checkbox normal',
							array(
								'values' => array(
									'name'     => 'wbte_sc_bogo_listing_check_ind',
									'id'       => esc_attr( "wbte_sc_checkbox$key" ),
									'group_id' => 'wbte_sc_bogo_listing_checkbox',
								),
							)
						);
						?>
					</td>
					<td class="wbte_sc_bogo_listing_title_td">
						<div class="wbte_sc_bogo_listing_table_title">
							<h3><?php echo esc_html( get_post_meta( $coupon_id, 'wbte_sc_bogo_coupon_name', true ) ); ?></h3>
							<p><?php esc_html_e( 'Buy X, get X/Y', 'wt-smart-coupons-for-woocommerce' ); ?></p>
						</div>
					</td>
					<td class="wbte_sc_bogo_listing_desc_td wbte_sc_bogo_hide_md">
						<?php $_coupon_desc = $coupon->get_description(); ?>
						<div class="wbte_sc_bogo_listing_description_div"><?php echo esc_html( ! empty( $_coupon_desc ) ? $_coupon_desc : '—' ); ?>
						</div>
					</td>
					<td class="wbte_sc_bogo_listing_table_status">
						<div class="wbte_sc_bogo_listing_table_status_div">
						<?php
						if ( ! $is_trash_segment ) {
							echo $ds_obj->get_component(
								"label {$status_class}",
								array(
									'values' => array(
										'label_text' => esc_html( $status_class_label[ $status_class ] ),
									),
								)
							);
						} elseif ( 'failed' === $status_class ) {
								echo $ds_obj->get_component(
									esc_attr( "label {$status_class}" ),
									array(
										'values' => array(
											'label_text' => esc_html( $status_class_label[ $status_class ] ),
										),
									)
								);
						}
						?>
						</div>
					</td>
					<td class="wbte_sc_bogo_hide_md">
						<?php
						$usage_count = $coupon->get_usage_count();
						$usage_limit = $coupon->get_usage_limit();
						printf(
							/* translators: 1: count 2: limit */
							esc_html__( '%s / %s', 'wt-smart-coupons-for-woocommerce' ),
							esc_html( $usage_count ),
							$usage_limit ? esc_html( $usage_limit ) : '&infin;'
						);
						?>
					</td>
					<td class="wbte_sc_bogo_listing_schedule wbte_sc_bogo_hide_sm"><?php echo wp_kses_post( $schedule_text ); ?></td>
					<td>
						<?php
						if ( ! $is_trash_segment ) {
							?>
								<div class="wbte_sc_bogo_listing_actions_content">
									<span style="height:24px;" class="wbte_sc_custom_title" onclick="window.location.href = '<?php echo esc_url( admin_url( 'admin.php?page=' . self::$bogo_page_name . '&wbte_bogo_id=' . $coupon_id ) ); ?>'" data-title="<?php esc_attr_e( 'Edit', 'wt-smart-coupons-for-woocommerce' ); ?>"><?php echo wp_kses_post( $ds_obj->render_html( array( 'html' => '{{wbte-ds-icon-edit}}' ) ) ); ?></span>

									<span class="wbte_sc_bogo_listing_single_duplicate wbte_sc_custom_title" style="height:24px;" data-title="<?php esc_attr_e( 'Duplicate', 'wt-smart-coupons-for-woocommerce' ); ?>"><?php echo wp_kses_post( $ds_obj->render_html( array( 'html' => '{{wbte-ds-icon-clone}}' ) ) ); ?></span>

									<span class="wbte_sc_bogo_listing_single_delete wbte_sc_custom_title wbte_sc_bogo_hide_xs" style="height:24px;" data-title="<?php esc_attr_e( 'Delete', 'wt-smart-coupons-for-woocommerce' ); ?>"><?php echo wp_kses_post( $ds_obj->render_html( array( 'html' => '{{wbte-ds-icon-trash}}' ) ) ); ?></span>

								<?php
								if ( 'failed' !== $status_class ) {
									echo $ds_obj->get_component(
										'toggle small',
										array(
											'values' => array(
												'name'  => 'wbte_sc_bogo_listing_actions_toggle',
												'id'    => esc_attr( "wbte_sc_bogo_listing_actions_toggle{$key}" ),
												'value' => 1,
												'is_checked' => esc_attr( 'success' === $status_class ),
											),
											'class'  => array( 'wbte_sc_bogo_listing_actions_toggle_label' ),
										)
									);
								} else {
									?>
										<div style="width:32px"></div>
									<?php } ?>
								</div>
								<?php
						} else {
							?>
								<div class="wbte_sc_bogo_listing_trash_actions_content">
									<span class="wbte_sc_bogo_listing_single_restore" style="height:24px;" ><?php echo wp_kses_post( $ds_obj->render_html( array( 'html' => '{{wbte-ds-icon-reload}}' ) ) ); ?></span>
									<span class="wbte_sc_bogo_single_perm_dlt_listing" data-wbte_sc_popup = "wbte_sc_bogo_delete_popup_single" style="height:24px;"><?php echo wp_kses_post( $ds_obj->render_html( array( 'html' => '{{wbte-ds-icon-trash}}' ) ) ); ?></span>
								</div>
								<?php
						}
						?>
						</div>
					</td>
				</tr>
				<?php
			}
			?>
		
	</tbody>
</table>
<?php

// Pagination.
$_bogo_coupon_ids       = $is_trash_segment ? self::get_bogo_coupons_list(
	array(
		'is_trash' => true,
		'no_limit' => true,
	)
) : self::get_bogo_coupons_list( array( 'no_limit' => true ) );
$pagination_bogo_counts = count( $_bogo_coupon_ids );
if ( $pagination_bogo_counts > 20 ) {
	$admin_url = admin_url( 'admin.php?page=' . self::$bogo_page_name );
	if ( isset( $_GET['listing_filters'] ) && is_array( $_GET['listing_filters'] ) ) {
		// Loop through the listing_filters array.
		$_listing_filters = Wt_Smart_Coupon_Security_Helper::sanitize_item( wp_unslash( $_GET['listing_filters'] ), 'text_arr' );
		foreach ( $_listing_filters as $filter ) {
			// Append each filter to the URL.
			$admin_url = add_query_arg( 'listing_filters[]', $filter, $admin_url );
		}
	}
	if ( isset( $_GET['search'] ) ) {
		$admin_url = add_query_arg( 'search', sanitize_text_field( wp_unslash( $_GET['search'] ) ), $admin_url );
	}
	if ( isset( $_GET['listing_status'] ) ) {
		$admin_url = add_query_arg( 'listing_status', sanitize_text_field( wp_unslash( $_GET['listing_status'] ) ), $admin_url );
	}
	echo $ds_obj->get_component(
		'pagination',
		array(
			'values' => array(
				'total'        => esc_attr( $pagination_bogo_counts ),
				'current_page' => isset( $_GET['pagenum'] ) ? max( absint( wp_unslash( $_GET['pagenum'] ) ), 1 ) : 1, // Current page.
				'limit'        => 20, // Limit of items per page.
				'url'          => esc_url( "{$admin_url}&pagenum=" ), // URL for pagination.
			),
		)
	);
}

// Multiple delete popup.
echo $ds_obj->get_component(
	'popup medium',
	array(
		'values' => array(
			'data_id'       => 'wbte_sc_bogo_delete_popup_multiple', // Unique popup id.
			'data_overlay'  => '1', // To enable overlay.
			'popup_title'   => esc_html__( 'Delete BOGO offers', 'wt-smart-coupons-for-woocommerce' ),
			'popup_content' => esc_html__( 'Are you sure you want to delete selected BOGO offers?', 'wt-smart-coupons-for-woocommerce' ),
			'templates'     => array( 'popup_content' => esc_html( plugin_dir_path( __FILE__ ) . '---multiple-dlt-popup.php' ) ),
		),
	)
);

// Single delete popup.
echo $ds_obj->get_component(
	'popup medium',
	array(
		'values' => array(
			'data_id'        => 'wbte_sc_bogo_delete_popup_single', // Unique popup id.
			'data_overlay'   => '1', // To enable overlay.
			'popup_title'    => esc_html__( 'Delete BOGO offer', 'wt-smart-coupons-for-woocommerce' ),
			// Translators: %s: BOGO title.
			'popup_content'  => sprintf( wp_kses_post( __( 'Are you sure you want to delete "%s"', 'wt-smart-coupons-for-woocommerce' ) ), wp_kses_post( '<span class="wbte_sc_bogo_single_dlt_title"></span>' ) ),
			'data-coupon_id' => '', // To store coupon id.
			'templates'      => array( 'popup_content' => esc_html( plugin_dir_path( __FILE__ ) . '---single-dlt-popup.php' ) ),
		),
	)
);
?>
