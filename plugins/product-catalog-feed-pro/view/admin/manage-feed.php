<?php
global $woocommerce_wpwoof_common;
include( 'feed-manage-list.php' );
require_once dirname( __FILE__ ) . '/../../inc/feedfbgooglepro.php';

$myListTable = new Wpwoof_Feed_Manage_list();
$myListTable->prepare_items();
$wpwoof_values = $woocommerce_wpwoof_common->getGlobalData();

if ( ! isset( $wpwoof_values['tmp_storage'] ) ) {
	$wpwoof_values['tmp_storage'] = 'disk';
}
$attributes = wpwoof_get_all_attributes();
$all_fields = wpwoof_get_all_fields();
$oFeed      = new FeedFBGooglePro();

$field_auto_pricing_min_price         = $woocommerce_wpwoof_common::get_message_and_status_for_auto_pricing_min_price_field();
$field_auto_pricing_min_price['type'] = $field_auto_pricing_min_price['status'] == 'hidden' ? 'hidden' : 'number';


?>
    <script>
        function storeWpWoofdata() {
            var data = jQuery('#iDwpwoofGLS').serialize() + "&action=set_wpwoof_global_data";
            jQuery.fn.saveWPWoofParam(data, function () {
                /*$('#idWpWoofGCats').html($('#feed_google_category').val());*/
            });
        }

        jQuery(function ($) {
            $('#IDextraGlobal input').change(storeWpWoofdata);
            $('#IDextraGlobal select').change(storeWpWoofdata);
        });
    </script>
    <div class="wpwoof-content-top wpwoof-box headerManagePage">
        <div a>
            <a class="wpwoof-button wpwoof-button-orange1" id="idWpWoofAddNewFeed" href="#">Create New Feed</a>
        </div>
        <div b>
            <vr></vr>
        </div>
        <div c>
            <a target="_blank" href="https://www.pixelyoursite.com/product-catalog-for-woocommerce-video-tutorials">VIDEO:
                Watch these short video tutorials for tips about the plugin.</a>
            <a target="_blank" href="https://www.pixelyoursite.com/woocommerce-product-catalog-feed-help">Learn how to
                use the plugin</a>
            <a target="_blank" href="https://www.pixelyoursite.com/facebook-product-catalog-feed">Learn how to create a
                Facebook Product Catalog</a>
        </div>
    </div>

    <form id="contact-filter" method="post">
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>"/>
		<?php //$myListTable->search_box('search', 'search_id'); ?>
        <!-- Now we can render the completed list table -->
		<?php $myListTable->display() ?>
    </form>

    <div id="feed-all-categories-dialog-content" style="display:none;" class="dialog-content"></div>
    <div class="wpwoof-box wpoof-settings-accordion">
        <form method="post" action="#" id="iDwpwoofGLS">
            <div class="wpoof-settings-accordion-wrapper">
                <h3>Global Settings:</h3>
                <svg class="wpoof-settings-accordion-btn" xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 512 512">
                    <!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                    <path
                            d="M496 384H160v-16c0-8.8-7.2-16-16-16h-32c-8.8 0-16 7.2-16 16v16H16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h80v16c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16v-16h336c8.8 0 16-7.2 16-16v-32c0-8.8-7.2-16-16-16zm0-160h-80v-16c0-8.8-7.2-16-16-16h-32c-8.8 0-16 7.2-16 16v16H16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h336v16c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16v-16h80c8.8 0 16-7.2 16-16v-32c0-8.8-7.2-16-16-16zm0-160H288V48c0-8.8-7.2-16-16-16h-32c-8.8 0-16 7.2-16 16v16H16C7.2 64 0 71.2 0 80v32c0 8.8 7.2 16 16 16h208v16c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16v-16h208c8.8 0 16-7.2 16-16V80c0-8.8-7.2-16-16-16z"/>
                </svg>
            </div>
            <table class="form-table manage_global_settings_block wpoof-settings-accordion-content">
                <tr>
                    <th>Regenerate active feeds:</th>
                    <td>
						<?php $current_interval = $woocommerce_wpwoof_common->getInterval(); ?>
                        <select name="wpwoof_schedule" id="wpwoof_schedule"
                                onchange="jQuery.fn.saveWPWoofParam({'action':'set_wpwoof_schedule'});">
							<?php

							$intervals = array(
								/*
								'604800'    => '1 Week',
								'86400'     => '24 Hours',
								'43200'     => '12 Hours',
								'21600'     => '6 Hours',
								'3600'      => '1 Hour',
								'900'       => '15 Minutes',
								'300'       => '5 Minutes',
								*/
								'0'      => 'Never',
								'3600'   => 'Hourly',
								'86400'  => 'Daily',
								'43200'  => 'Twice daily',
								'604800' => 'Weekly'
							);
							foreach ( $intervals as $interval => $interval_name ) {
								?>
                                <option <?php
								if ( $interval == $current_interval or ! $current_interval and ! $interval ) {
									echo " selected ";
								} ?> value="<?php
								echo $interval;
								?>"><?php echo $interval_name;
								?></option><?php
							}
							?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Start regeneration from:</th>
                    <td>
                        <input type="time" name="wpwoof_schedule_from"
                               value="<?= get_option( 'wpwoof_schedule_from', "" ) ?>"
                               onchange="jQuery.fn.saveWPWoofParam({'action':'set_wpwoof_schedule'});">
                    </td>
                </tr>
                <tr>
                    <th>"On Save/Update" feeds action:</th>
                    <td>
                        <select name="on_save_feed_action" id="wpwoof_on_save_feed_action"
                                onchange="storeWpWoofdata();">
                            <option value="save" <?php selected( $wpwoof_values['on_save_feed_action'], 'save' ) ?>>Save
                                only
                            </option>
                            <option value="save_and_regenerate_main" <?php selected( $wpwoof_values['on_save_feed_action'], 'save_and_regenerate_main' ) ?>>
                                Save and regenerate
                            </option>
                            <option value="save_and_regenerate_all" <?php selected( $wpwoof_values['on_save_feed_action'], 'save_and_regenerate_all' ) ?>>
                                Save and regenerate with localized feeds (if exist)
                            </option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Global Google Taxonomy:</th>
                    <td>
						<?php
						$data = $woocommerce_wpwoof_common->getGlobalGoogleCategory();
						?>
                        <input class="wpwoof_google_category_g_name" type="hidden" name="feed_google_category"
                               value="<?php echo $data['name']; ?>"/>
                        <input type="text" name="wpwoof_google_category" onchange="storeTaxonomyParams(this);"
                               class="wpwoof_google_category_g" value="" style='display:none;'/>
						<?php
						$taxSrc = admin_url( 'admin-ajax.php' );
						$taxSrc = add_query_arg( array( 'action' => 'wpwoofgtaxonmy' ), $taxSrc );
						?>
                        <script>
                            var WPWOOFpreselect = '<?php echo $data['name'] ?>';
                            jQuery(function ($) {
                                wpwoof_taxonomyPreLoad["<?= empty( $data['name'] ) ? 'root' : $data['name']?>"] = <?=json_encode( wpwoof_getTaxonmyByPath( $data['name'] ) )?>;
                                loadTaxomomy(".wpwoof_google_category_g", function () {
                                    var sNames = jQuery('.wpwoof_google_category_g_name').val();
                                    if (WPWOOFpreselect != sNames) {
                                        jQuery.fn.saveWPWoofParam({
                                            'action': 'set_wpwoof_category',
                                            'wpwoof_feed_google_category': sNames,
                                        }, function () {
                                            WPWOOFpreselect = sNames;
                                        });
                                    }
                                });
                            });
                        </script>
                    </td>
                </tr>
                <tr>
                    <th>Global Image:</th>
                    <td>
                        <!-- input type="button" class="button wpfoof-box-upload-button" value="Upload" / -->
						<?php
						$value = $woocommerce_wpwoof_common->getGlobalImg();
						$image = empty( $value ) ? '' : wp_get_attachment_image( $value, 'full', false, array( 'style' => 'display:block;/*margin-left:auto*/;margin-right:auto;max-width:30%;height:auto;' ) );
						?>
                        <span class="wrap wpwoof-required-value">
                            <input type='hidden' id='_value-Maine-Img' name='wpfoof-box-media[Maine-Img]'
                                   value='<?php echo $value ?>'/>
                            <input type='button' id='Maine-Img' onclick="jQuery.fn.clickWPfoofClickUpload(this);"
                                   class='button wpfoof-box-upload-button' value='Upload'/>
                            <input type='button' id='Maine-Img-remove' onclick="jQuery.fn.clickWPfoofClickRemove(this);"
                                   <?php if ( empty( $image ) ) { ?>style="display:none;"<?php } ?> class='button wpfoof-box-upload-button-remove'
                                   value='Remove'/>
                         </span>
                        <span id='IDprev-Maine-Img'
                              class='image-preview'><?php echo ( $image ) ? ( "<br/><br/>" . $image . "<br/>" ) : "" ?></span>
                        <span data-size='1200X628' id='Maine-Img-alert'></span>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <hr class="wpwoof-break"/>
                    </td>
                </tr>
                <tr>
                    <th>Brand:</th>
                    <td><b>The plugin will fill brand in this order:</b></td>
                </tr>

                <tr>
                    <td></td>
                    <td>
                        <label>
                            <input name="brand[custom]" type="hidden" value="0"/>
                            <input onchange="storeWpWoofdata();" name="brand[custom]" value="1"
                                   type="checkbox" <?php
							if ( ! empty( $wpwoof_values['brand']['custom'] ) || ! isset( $wpwoof_values['brand']['custom'] ) ) {
								echo ' checked';
							}
							?> /> Custom Brand. The plugin adds a dedicated Brand field to every product.
                            <br><br>
                        </label>
						<?php
						if ( $woocommerce_wpwoof_common->is_woocomerce_brand_active() ) {
							?>  <label>
                                <input name="brand[woo_brand]" type="hidden" value="0"/>
                                <input onchange="storeWpWoofdata();" name="brand[woo_brand]" value="1"
                                       type="checkbox" <?php
								if ( ! empty( $wpwoof_values['brand']['woo_brand'] ) || ! isset( $wpwoof_values['brand']['woo_brand'] ) ) {
									echo ' checked';
								}
								?> /> WooCommerce brands
                                <br><br></label>
							<?php
						}
						if ( is_plugin_active( WPWOOF_BRAND_YWBA ) ) {
							?>  <label>
                                <input name="brand[WPWOOF_BRAND_YWBA]" type="hidden" value="0"/>
                                <input onchange="storeWpWoofdata();" name="brand[WPWOOF_BRAND_YWBA]" value="1"
                                       type="checkbox" <?php
								if ( ! empty( $wpwoof_values['brand']['WPWOOF_BRAND_YWBA'] ) || ! isset( $wpwoof_values['brand']['WPWOOF_BRAND_YWBA'] ) ) {
									echo ' checked';
								}
								?> /> YITH WooCommerce Brands Add-on plugin detected, use it when possible
                                <br><br></label>
							<?php
						}
						if ( is_plugin_active( WPWOOF_BRAND_PEWB ) ) {
							?> <label>
                                <input name="brand[WPWOOF_BRAND_PEWB]" type="hidden" value="0"/>
                                <input onchange="storeWpWoofdata();" type="checkbox" name="brand[WPWOOF_BRAND_PEWB]"
                                       value="1" <?php
								if ( ! empty( $wpwoof_values['brand']['WPWOOF_BRAND_PEWB'] ) || ! isset( $wpwoof_values['brand']['WPWOOF_BRAND_PEWB'] ) ) {
									echo ' checked';
								}
								?> /> Perfect WooCommerce Brands. Use it when possible
                                <br><br></label>
							<?php
						}
						if ( $woocommerce_wpwoof_common->is_PRWB_active() ) {
							?> <label>
                                <input name="brand[WPWOOF_BRAND_PRWB]" type="hidden" value="0"/>
                                <input onchange="storeWpWoofdata();" type="checkbox" name="brand[WPWOOF_BRAND_PRWB]"
                                       value="1" <?php
								if ( ! empty( $wpwoof_values['brand']['WPWOOF_BRAND_PRWB'] ) || ! isset( $wpwoof_values['brand']['WPWOOF_BRAND_PRWB'] ) ) {
									echo ' checked';
								}
								?> /> Premmerce WooCommerce Brands. Use it when possible
                                <br><br></label>
							<?php
						}
						if ( is_plugin_active( WPWOOF_BRAND_PBFW ) ) {
							?> <label>
                                <input name="brand[WPWOOF_BRAND_PBFW]" type="hidden" value="0"/>
                                <input onchange="storeWpWoofdata();" type="checkbox" name="brand[WPWOOF_BRAND_PBFW]"
                                       value="1" <?php
								if ( ! empty( $wpwoof_values['brand']['WPWOOF_BRAND_PBFW'] ) || ! isset( $wpwoof_values['brand']['WPWOOF_BRAND_PBFW'] ) ) {
									echo ' checked';
								}
								?> /> Product Brands For WooCommerce. Use it when possible
                                <br><br></label>
							<?php
						}
						?>
                        <label class="p_inline_block" style="display: inline-block;">
                            <input name="brand[linked]" type="hidden" value="0"/>
                            <input onchange="storeWpWoofdata();" name="brand[linked]" value="1"
                                   type="checkbox" <?php echo checked( ! empty( $wpwoof_values['brand']['linked'] ) || ! isset( $wpwoof_values['brand']['linked'] ) )
							?>>This value: </label>
                        <select onchange="storeWpWoofdata();" name="brand[value]"
                                class="wpwoof_mapping wpwoof_mapping_option"
                                style="display: inline-block;"><?php
							$html = '';
							$html .= '<optgroup label="">';
							$html .= '<option value="">select</option>';
							$html .= '</optgroup>';


							$html .= '<optgroup label="Global Product Attributes">';
							foreach ( $attributes['global'] as $key => $value ) {
								if ( $key == 'product_visibility' ) {
									continue;
								}
								$html .= '<option value="wpwoofattr_' . $key . '" ' . ( isset( $wpwoof_values['brand']['value'] ) ? selected( 'wpwoofattr_' . $key, $wpwoof_values['brand']['value'], false ) : '' ) . ' >' . $value . '</option>';
							}
							$html .= '</optgroup>';
							if ( isset( $attributes['pa'] ) and count( $attributes['pa'] ) ) {
								$html .= '<optgroup label="Product Attributes">';
								foreach ( $attributes['pa'] as $key => $value ) {
									$html .= '<option value="wpwoofattr_' . $key . '" ' . ( isset( $wpwoof_values['brand']['value'] ) ? selected( 'wpwoofattr_' . $key, $wpwoof_values['brand']['value'], false ) : '' ) . ' >' . $value . '</option>';
								}
								$html .= '</optgroup>';
							}
							if ( isset( $attributes['meta'] ) and count( $attributes['meta'] ) ) {
								$html .= '<optgroup label="Custom Fields">';
								foreach ( $attributes['meta'] as $key => $value ) {
									if ( ! empty( $value ) ) {
										$html .= '<option value="wpwoofattr_' . $value . '" ' . ( isset( $wpwoof_values['brand']['value'] ) ? selected( 'wpwoofattr_' . $value, $wpwoof_values['brand']['value'], false ) : '' ) . ' >' . $value . '</option>';
									}
								}
								$html .= '</optgroup>';
							}
							if ( isset( $attributes['integrated'] ) and count( $attributes['integrated'] ) ) {
								foreach ( $attributes['integrated'] as $name => $fields ) {
									if ( ! empty( $fields ) ) {
										$html .= '<optgroup label="' . $name . '">';
										foreach ( $fields as $key => $value ) {
											$html .= '<option value="wpwoofattr_' . $key . '" ' . ( isset( $aValues['value'] ) ? selected( 'wpwoofattr_' . $key, $aValues['value'], false ) : '' ) . ' >' . $value . '</option>';
										}
										$html .= '</optgroup>';
									}

								}
							}
							echo $html;
							?></select><br><br>
                        <script>jQuery("select[name='brand[value]']").fastselect();</script>
                        <label>
                            <input name="brand[autodetect]" type="hidden" value="0"/>
                            <input onclick="storeWpWoofdata();" name="brand[autodetect]" type="checkbox" value="1" <?php
							if ( ! empty( $wpwoof_values['brand']['autodetect'] ) || ! isset( $wpwoof_values['brand']['autodetect'] ) ) {
								echo ' checked';
							}
							?>/> Possible "brand" field autodetected. Use it when possible
                            <br><br></label>

                        <p class="p_inline_block">Use this when brand is missing: </p>
                        <input onchange="storeWpWoofdata();" name="brand[define]"
                               type="text" value="<?php
						echo ! empty( $wpwoof_values['brand']['define'] ) ? $wpwoof_values['brand']['define'] : get_bloginfo( 'name' ); ?>"/>
						<?php ////////////////////////////////////////////////////////////////// END Brand BLOCk  ////////////////////////////////////////////////////////////////////////////////////// ?>
                    </td>
                    <td style="padding-bottom: 100px;"><?= isset( $all_fields['notoutput']['brand'] ) ? $oFeed->getHelpLinks( $all_fields['notoutput']['brand'] ) : '' ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <hr class="wpwoof-break"/>
                    </td>
                </tr>
                <tr>
                    <th>GTIN:</th>
                    <td><b>The plugin will fill GTIN in this order:</b></td>
                </tr>
                <tr>
                    <th></th>
                    <td>
                        <p>Custom GTIN. The plugin adds a dedicated GTIN field (If value exists; overrides feed
                            setting).
                        </p>
                        <p>Feed settings (If value is set; overrides global setting).</p><br>
                        <p style="display: inline-block">This value:</p>
						<?php $value = isset( $wpwoof_values['extra']['gtin']['value'] ) ? $wpwoof_values['extra']['gtin'] : array(
							'value'        => '',
							'custom_value' => ''
						);
						echo $oFeed->renderExtraFieldsForMapping( 'gtin', $value );
						?>
                        (Optional).
                    </td>
                    <td style="vertical-align: bottom;padding-bottom: 23px;width: 100px;"><?= isset( $all_fields['dashboardRequired']['gtin'] ) ? $oFeed->getHelpLinks( $all_fields['dashboardRequired']['gtin'] ) : '' ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <hr class="wpwoof-break"/>
                    </td>
                </tr>
                <tr>
                    <th>MPN:</th>
                    <td>
                        <b>The plugin will fill MPN in this order:</b>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <p>Custom MPN. The plugin adds a dedicated MPN field (if value exists; overrides feed
                            setting).</p>
                        <p>Feed settings (Іf value is set; overrides global setting).</p><br>
                        <p style="display: inline-block">This value:</p>
						<?php $value = isset( $wpwoof_values['extra']['mpn']['value'] ) ? $wpwoof_values['extra']['mpn'] : array(
							'value'        => '',
							'custom_value' => ''
						);
						echo $oFeed->renderExtraFieldsForMapping( 'mpn', $value ); ?>
                        (Required. Not applicable for Reviews for Google Merchant feed).
                    </td>
                    <td style="vertical-align: bottom;padding-bottom: 23px;"><?= isset( $all_fields['dashboardRequired']['mpn'] ) ? $oFeed->getHelpLinks( $all_fields['dashboardRequired']['mpn'] ) : '' ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <hr class="wpwoof-break"/>
                    </td>
                </tr>
                <tr>
					<?php $val = isset( $wpwoof_values['extra']['identifier_exists']['custom_value'] ) ? $wpwoof_values['extra']['identifier_exists']['custom_value'] : ''; ?>
                    <th>Identifier exists:</th>
                    <td><p style="display: inline-block">This value:</p>
                        <select name="extra[identifier_exists][custom_value]"
                                class="wpwoof_mapping wpwoof_mapping_option">
                            <option value="true">select</option>
                            <option <?php selected( $val, 'yes' ) ?> value="yes"> Yes</option>
                            <option <?php selected( $val, 'no' ) ?> value="no">No</option>
                        </select>
                        <input type="hidden" name="extra[identifier_exists][value]" value="custom_value">
                    </td>
                    <td><?= isset( $all_fields['dashboardRequired']['identifier_exists'] ) ? $oFeed->getHelpLinks( $all_fields['dashboardRequired']['identifier_exists'] ) : '' ?></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <hr class="wpwoof-break"/>
                    </td>
                </tr>
                <tr>
                    <th>Google Automated Discounts:</th>
                    <td><p style="display: inline-block">auto_pricing_min_price:</p>
                        <input onchange="storeWpWoofdata();" name="extra[auto_pricing_min_price][value]"
                               type="<?php echo $field_auto_pricing_min_price['type'] ?>" min="1" max="99" step="1"
                               value="<?php
						       echo ! empty( $wpwoof_values['auto_pricing_min_price']['value'] ) ? $wpwoof_values['auto_pricing_min_price']['value'] : '' ?>"/>
                        <br><br><?php echo $field_auto_pricing_min_price['message'] ?>
                    </td>
                    <td><?= isset( $all_fields['dashboardRequired']['auto_pricing_min_price'] ) ? $oFeed->getHelpLinks( $all_fields['dashboardRequired']['auto_pricing_min_price'] ) : '' ?></td>
                </tr>
            </table>
            <table class="form-table product-catalog-feed-pro__settings wpoof-settings-accordion-content">
                <tr>
                    <td colspan="4">
						<?php

						$select_values = $helpLinks = array();
						foreach ( $all_fields['dashboardExtra'] as $key => $value ) {
							if ( isset( $value['custom'] ) && ! empty( $value['custom'] ) ) {
								$select_values[ $key ] = $value['custom'];
							}
							$helpLinks[ $key ] = $oFeed->getHelpLinks( $value );
						}
						?>
                        <hr class="wpwoof-break"/>
                        <h3>Map extra fields:</h3>
                        <p>Add extra fields and map them to product attributes or custom fields. You can also edit
                            products or variations and add additional fields.</p>
                    </td>
                </tr>
				<?php
				$editorsId4init   = array();
				foreach ( $wpwoof_values['extra'] as $key => $value ):
					$is_repeated = false;
					$original_key = $key;
					$field_title  = empty( $all_fields['dashboardExtra'][ $key ]['header'] ) ? $key : $all_fields['dashboardExtra'][ $key ]['header'];
					if ( preg_match( '/^(.+)-\d+$/', $key, $matches ) ) {
						$original_key = $matches[1];
						if ( ! empty( $all_fields['dashboardExtra'][ $original_key ]['repeated'] ) ) {
							$is_repeated = true;
							$field_title = empty( $all_fields['dashboardExtra'][ $original_key ]['header'] ) ? $original_key : $all_fields['dashboardExtra'][ $original_key ]['header'];

						}
					}
					if ( $value['value'] === '' || in_array( $key, array(
							'identifier_exists',
							'mpn',
							'gtin',
							'auto_pricing_min_price'
						) ) ) {
						continue;
					}
					$isCustomTag = isset( $value['custom_tag_name'] );
					?>

                    <tr>
                        <td style="width:250px;">
							<?php if ( $isCustomTag ) { ?>
                                <input type="text" name="extra[<?= $key ?>][custom_tag_name]"
                                       value="<?= $value['custom_tag_name'] ?>" style="width: 100%;">
							<?php } else {
								echo '<b>' . $field_title . ':</b>';
							} ?>

                        </td>
                        <td class="input-cell" style="min-width: 550px;">
							<?php
							echo $oFeed->renderExtraFieldsForMapping( $key, $value );
							if ( isset( $select_values[ $key ] ) ) {
								echo '<select name="extra[' . $key . '][custom_value]" class="catalog_pro_dashboard_select" style="display: ' . ( $value['value'] == 'custom_value' ? 'inline' : "none" ) . ';" >';

								foreach ( $select_values[ $key ] as $keySel => $valueSel ) {
									echo '<option value="' . $keySel . '" ' . selected( $keySel, $value['custom_value'], false ) . '>' . $valueSel . '</option>';
								}

								echo '</select>';
							} else {
								if ( $value['value'] == 'custom_value_editor' ) {
									echo '<textarea name="extra[' . $key . '][editor_value]" id="wpwoof-editor-' . $key . '" cols="110">' . stripslashes( $value['custom_value'] ) . '</textarea>';
									$editorsId4init[] = $key;
								} else {
									echo '<textarea id="wpwoof-editor-' . $key . '" cols="110" style="display: none"></textarea>';
								} ?>

                                <input type="text" name="extra[<?= $key ?>][custom_value]" placeholder="Custom value"
                                       value="<?= esc_html( $value['custom_value'] ) ?>"
                                       class="catalog_pro_dashboard_input"
                                       style="display: <?= $value['value'] == 'custom_value' ? 'inline' : "none" ?>;">
							<?php }
							if ( $isCustomTag ):?>
                                <br><br>
                                <div class="extra-input__item">
                                    <input type="checkbox" name="extra[<?= $key ?>][feed_type][facebook]"
                                           id="extra[<?= $key ?>][feed_type][facebook]" <?php checked( isset( $value['feed_type']['facebook'] ) ); ?>>
                                    <label for="extra[<?= $key ?>][feed_type][facebook]">Facebook</label>&emsp;&emsp;
                                </div>
                                <div class="extra-input__item">
                                    <input type="checkbox" name="extra[<?= $key ?>][feed_type][google]"
                                           id="extra[<?= $key ?>][feed_type][google]" <?php checked( isset( $value['feed_type']['google'] ) ); ?>>
                                    <label for="extra[<?= $key ?>][feed_type][google]">Google Merchant</label>&emsp;&emsp;
                                </div>
                                <div class="extra-input__item">
                                    <input type="checkbox" name="extra[<?= $key ?>][feed_type][adsensecustom]"
                                           id="extra[<?= $key ?>][feed_type][adsensecustom]" <?php checked( isset( $value['feed_type']['adsensecustom'] ) ); ?>>
                                    <label for="extra[<?= $key ?>][feed_type][adsensecustom]">Google Custom
                                        Remarketing</label>&emsp;&emsp;
                                </div>
                                <div class="extra-input__item">
                                    <input type="checkbox" name="extra[<?= $key ?>][feed_type][pinterest]"
                                           id="extra[<?= $key ?>][feed_type][pinterest]" <?php checked( isset( $value['feed_type']['pinterest'] ) ); ?>>
                                    <label for="extra[<?= $key ?>][feed_type][pinterest]">Pinterest</label>
                                </div>
                                <div class="extra-input__item">
                                    <input type="checkbox" name="extra[<?= $key ?>][feed_type][tiktok]"
                                           id="extra[<?= $key ?>][feed_type][tiktok]" <?php checked( isset( $value['feed_type']['tiktok'] ) ); ?>>
                                    <label for="extra[<?= $key ?>][feed_type][tiktok]">TikTok</label>
                                </div>
                                <div class="extra-input__item">
                                    <br><br>
                                    <input type="checkbox" name="extra[<?= $key ?>][feed_type][mapping]"
                                           id="extra[<?= $key ?>][feed_type][mapping]" <?php checked( isset( $value['feed_type']['mapping'] ) ); ?>>
                                    <label for="extra[<?= $key ?>][feed_type][mapping]">Use for mapping (limited to 100
                                        chars if mapped to custom labels)</label>
                                </div>
							<?php endif ?>
                        </td>
                        <td>
                            <div style="display: inline-block; line-height: 30px;float:right;width: 100px;">
								<?= isset( $all_fields['dashboardExtra'][ $original_key ] ) ? $oFeed->getHelpLinks( $all_fields['dashboardExtra'][ $original_key ] ) : '' ?>
                            </div>
                            <input type="button" onclick="" class="button remove-extra-field-btn" value="remove"
                                   style="float:left;">
                        </td>
                    </tr>
				<?php endforeach; ?>
                <tr id="tr-befor-add-new-field">
                    <td colspan="4">
                        <hr class="wpwoof-break"/>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <div class="catalog_pro_dashboard_extra_field_container">
							<?php
							$oFeed->renderFieldsForDropbox( $all_fields['dashboardExtra'] );
							?>
                        </div>
                        <input type="button" id="add-extra-field-btn" class="button" value="Add new field"
                               style="height: 31px;">
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
						<?php
						if ( isset( $all_fields['toedittab']['shipping']['desc'] ) ) {
							echo '* ' . $all_fields['toedittab']['shipping']['desc'];
						}
						?>
                    </td>
                </tr>
                <tr>
                    <th>Cloudflare:</th>
                    <td>
                        <label class="p_inline_block" style="display: inline-block;">
                            <input name="add_no_cache_to_url" type="hidden" value="0"/>
                            <input onchange="storeWpWoofdata();" name="add_no_cache_to_url" value="1"
                                   type="checkbox" <?php echo checked( ! empty( $wpwoof_values['add_no_cache_to_url'] ) )
							?>>Add "no cache" paramener to feed URLs</label>
                        <p>Set this option to ensure using non-cached feeds. Primarily works for Cloudflare, but may
                            also work with other caching systems. </p>
                    </td>
                </tr>
                <tr>
                    <th>Store temporary data to:</th>
                    <td>
                        <select onchange="storeWpWoofdata();" name="tmp_storage" id="tmp_storage">
                            <option <?php selected( $wpwoof_values['tmp_storage'], 'disk' ) ?> value="disk">Disk
                            </option>
                            <option <?php selected( $wpwoof_values['tmp_storage'], 'db' ) ?> value="db">Database
                            </option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Regeneration method:</th>
                    <td>
                        <select onchange="storeWpWoofdata();" name="regeneration_method" id="regeneration_method">
                            <option <?php selected( $wpwoof_values['regeneration_method'], 'wp-cron' ) ?>
                                    value="wp-cron">
                                WP-Cron
                            </option>
                            <option <?php selected( $wpwoof_values['regeneration_method'], 'scheduler' ) ?>
                                    value="scheduler">Product catalog scheduler
                            </option>
                            <option <?php selected( $wpwoof_values['regeneration_method'], 'external' ) ?>
                                    value="external">
                                External call
                            </option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        Regeneration method should be set to ensure that the feeds are continuously regenerated and stay
                        updated.
                        <br>
                        <br>
                        <b>WP-Cron</b> - the feed is regenerated by WP-Cron.
                        <br>
                        <b>Product catalog scheduler</b> - the feed is regenerated by the product catalog scheduler.
                        Recommended to use when WP-Cron is not working or disabled.
                        <br>
                        <b>External</b> - the feed is regenerated by external call. Recommended to use when the web site
                        does not have visitors or has a cached pages or protected by https authentication.
                        <br><br>
                        <b>External calls example</b>:
                        <br><br>
                        <b>REST API</b>:
                        <br>
                        <pre><code>GET <?php echo get_site_url() ?>/wp-json/wpwoof/v1/feeds_update</code></pre>
                        <b>WP-CLI</b>:
                        <br>
                        <pre><code>wp wpwoof_product_catalog_feed feeds_update</code></pre>
                        External calls can be customized in addition to WP-Cron or product catalog scheduler and work
                        simultaneously.
                    </td>
                </tr>
            </table>
        </form>
        <table id="wpwoof-def-extra-row" style="display: none">
            <tr>
                <td style="width:250px;">

                    <input type="text" name="wpwoof-def[custom_tag_name]" placeholder="Custom field"
                           style="width: 100%;">
                    <input type="hidden" name="wpwoof-def[input_type]" value="text">
                    <b id="wpwoof-def-title"></b>

                </td>
                <td class="input-cell" style="min-width: 550px;">
					<?php
					echo $oFeed->renderExtraFieldsForMapping( 'wpwoof-def', array() );

					?><input type="text" name="wpwoof-def[custom_value]" placeholder="Custom value" value=""
                             class="catalog_pro_dashboard_input" style="display: none;">
                    <select name="wpwoof-def[custom_value]" class="catalog_pro_dashboard_select"
                            style="display: none;"></select>
                    <textarea id="wpwoof-editor-def" cols="110" style="display: none"></textarea>
                    <br><br>

                    <div class="extra-input__item">
                        <input type="checkbox" name="wpwoof-def[feed_type][facebook]" checked>
                        <label for="wpwoof-def[feed_type][facebook]">Facebook</label>&emsp;&emsp;
                    </div>
                    <div class="extra-input__item">
                        <input type="checkbox" name="wpwoof-def[feed_type][google]" checked>
                        <label for="wpwoof-def[feed_type][google]">Google Merchant</label>&emsp;&emsp;
                    </div>
                    <div class="extra-input__item">
                        <input type="checkbox" name="wpwoof-def[feed_type][adsensecustom]" checked>
                        <label for="wpwoof-def[feed_type][adsensecustom]">Google Custom Remarketing</label>
                    </div>
                    <div class="extra-input__item">
                        <input type="checkbox" name="wpwoof-def[feed_type][pinterest]" checked>
                        <label for="wpwoof-def[feed_type][pinterest]">Pinterest</label>
                    </div>
                    <div class="extra-input__item">
                        <input type="checkbox" name="wpwoof-def[feed_type][tiktok]" checked>
                        <label for="wpwoof-def[feed_type][tiktok]">TikTok</label>
                    </div>
                    <div class="extra-input__item">
                        <br><br>
                        <input type="checkbox" name="wpwoof-def[feed_type][mapping]" checked>
                        <label for="wpwoof-def[feed_type][mapping]">Use for mapping (limited to 100 chars if mapped to
                            custom labels)</label>
                    </div>
                </td>
                <td>
                    <div class="extra-link-2-wrapper-dashboard"
                         style="display: inline-block; line-height: 30px;float:right;width: 100px;">
                        &nbsp;&nbsp;FB | G
                    </div>
                    <input type="button" onclick="" class="button remove-extra-field-btn" value="remove"
                           style="float:left">
                </td>
            </tr>
        </table>
        <div class="wpoof-settings-accordion-content">
			<?php include( 'info-settings-permissions.php' ); ?>
        </div>
    </div>
    <script>
        jQuery("select[name*='extra['][name$='[value]']").fastselect();
        jQuery("select[name*='extra['][name*='[identifier_exists]']").fastselect();
        let wpwoof_select_values = <?= json_encode( $select_values )?>;
        let wpwoof_help_links = <?= json_encode( $helpLinks ) ?>;
        let wpwoof_editorsId4init = <?= json_encode( $editorsId4init ) ?>;
        let wpwoof_current_page = 'dashboard'; </script>

<?php
wp_enqueue_editor();
include( 'info-settings.php' );