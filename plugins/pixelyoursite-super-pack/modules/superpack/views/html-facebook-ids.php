<?php

namespace PixelYourSite\SuperPack;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use PixelYourSite;

?>

    <?php
$isWpmlActive = isWPMLActive();
if ($isWpmlActive) {
    $languageCodes = array_keys(apply_filters('wpml_active_languages', null, null));
}
$pixelsInfo = PixelYourSite\SuperPack()->getFbAdditionalPixel();

foreach ( $pixelsInfo as $index => $pixelInfo ) : ?>
    <div class="plate pixel_info pixel_info_multipixel mb-24">
		<?php PixelYourSite\SuperPack()->render_text_input_array_item( 'fb_ext_pixel_id', "", $index, true ); ?>

        <div class="row align-items-center mb-24">
            <div class="col-12 d-flex align-items-center pixel-switcher-enabled">
                <div class="secondary-switch">
                    <input type="checkbox" value="1" <?php checked( $pixelInfo->isEnable, true ); ?>
                           id="pixel_facebook_is_enable_<?= $index ?>" class="custom-switch-input is_enable">
                    <label class="custom-switch-btn" for="pixel_facebook_is_enable_<?= $index ?>">
                    </label>
                </div>
                <h4 class="switcher-label secondary_heading">Enable Pixel</h4>
            </div>
        </div>

		<?php include PYS_SUPER_PACK_PATH . '/modules/superpack/views/UI/button-remove-pixel.php'; ?>

        <div class="pixel-data-wrap">
            <div>
                <h4 class="primary_heading mb-4">Meta Pixel ID:</h4>
                <input type="text" value="<?php echo esc_attr( $pixelInfo->pixel ); ?>"
                       placeholder="Meta Pixel ID"
                       class='form-control pixel_id input-standard'/>

                <div class="form-text mt-4">
                    <a href="https://www.pixelyoursite.com/pixelyoursite-free-version/add-your-facebook-pixel"
                       target="_blank" class="link link-small">How to get it?</a>
                </div>
            </div>

            <div>
                <h4 class="primary_heading mb-4">Conversion API:</h4>
                <textarea type="text"
                          placeholder="Api token"
                          class="form-control pixel_ext textarea-standard"
                          data-ext="api_token"><?= !empty( $pixelInfo->extensions[ 'api_token' ] ) ? $pixelInfo->extensions[ 'api_token' ] : "" ?></textarea>
            </div>

            <div>
                <p class="text-gray">
                    Send events directly from your web server to Facebook through the Conversion API. This
                    can help you capture more events. An access token is required to use the server-side
                    API.
                    <a href='https://www.pixelyoursite.com/facebook-conversion-api-capi' target='_blank' class="link">Learn
                        how to generate the token and how to test Conversion API</a>
                </p>
            </div>

            <div>
                <h4 class="primary_heading mb-4">Test Event Code:</h4>
                <input type="text" data-ext="api_code"
                       value="<?= !empty( $pixelInfo->extensions[ 'api_code' ] ) ? $pixelInfo->extensions[ 'api_code' ] : "" ?>"
                       placeholder="Code" class='form-control pixel_ext input-standard'>
                <div class="mt-6">
                    <p class="form-text text-small">
                        Use this if you need to test the server-side event. <strong>Remove it after
                            testing.</strong> The code will auto-delete itself after 24 hours.
                    </p>
                </div>
            </div>

            <div class="gap-16">
                <div>
                    <div class="small-checkbox">
                        <input type="checkbox" value="1"
                               id="facebook_is_fire_signal_<?php echo esc_attr( $index ); ?>"
                               class="small-control-input is_fire_signal" <?php checked( $pixelInfo->isFireForSignal, true ); ?>>
                        <label class="small-control small-checkbox-label"
                               for="facebook_is_fire_signal_<?php echo esc_attr( $index ); ?>">
                            <span class="small-control-indicator"><i class="icon-check"></i></span>
                            <span class="small-control-description">Fire the active automated events for this pixel</span>
                        </label>
                    </div>
                </div>

				<?php if ( PixelYourSite\isWooCommerceActive() ) : ?>
                    <div>
                        <div class="small-checkbox">
                            <input type="checkbox"
                                   id="facebook_is_fire_woo_<?php echo esc_attr( $index ); ?>"
                                   class="small-control-input is_fire_woo" <?php checked( $pixelInfo->isFireForWoo, true ); ?>>
                            <label class="small-control small-checkbox-label"
                                   for="facebook_is_fire_woo_<?php echo esc_attr( $index ); ?>">
                                <span class="small-control-indicator"><i class="icon-check"></i></span>
                                <span class="small-control-description">Fire the WooCommerce events for this pixel</span>
                            </label>
                        </div>
                    </div>
				<?php endif; ?>

				<?php if ( PixelYourSite\isEddActive() ) : ?>
                    <div>
                        <div class="small-checkbox">
                            <input type="checkbox"
                                   id="facebook_is_fire_edd_<?php echo esc_attr( $index ); ?>"
                                   class="small-control-input is_fire_edd" <?php checked( $pixelInfo->isFireForEdd, true ); ?>>
                            <label class="small-control small-checkbox-label"
                                   for="facebook_is_fire_edd_<?php echo esc_attr( $index ); ?>">
                                <span class="small-control-indicator"><i class="icon-check"></i></span>
                                <span class="small-control-description">Fire the Easy Digital Downloads events for this pixel</span>
                            </label>
                        </div>
                    </div>
				<?php endif; ?>
            </div>

            <div>
                <h4 class="primary_heading mb-8">Display conditions:</h4>
                <div class="conditions-logic-track">
                    <label>Logic: </label>
                    <div class="select-standard-wrap">
                        <select class="select-standard" id="facebook_logic_conditional_track_<?=$index?>">
                            <option value="" disabled selected>Please, select...</option>
                            <?php
                            $track_options = array(
                                'track' => 'Track',
                                'dont_track' => 'Don\'t track',
                            );
                            foreach ( $track_options as $option_key => $option_value ) : ?>
                                <option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $option_key, $pixelInfo->logicConditionalTrack ); ?> ><?php echo esc_attr( $option_value ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php SpPixelCondition()->renderHtml( $pixelInfo->displayConditions ) ?>
            </div>

			<?php if ( PixelYourSite\SuperPack()->getOption( 'enable_hide_this_tag_by_url' ) ) : ?>
                <div class="line-dark"></div>

                <div class="d-flex align-items-center">
                    <div class="secondary-switch">
                        <input type="checkbox" value="1" <?php checked( $pixelInfo->isHideByUrl, true ); ?>
                               id="pixel_facebook_is_hide_url_<?= $index ?>"
                               class="custom-switch-input is-hide-url">

                        <label class="custom-switch-btn" for="pixel_facebook_is_hide_url_<?= $index ?>">
                        </label>
                    </div>

                    <h4 class="switcher-label secondary_heading">Hide this tag if the URL includes</h4>
                </div>

                <div>
                    <h4 class="primary_heading mb-4">Hide this tag if the page URL any of these values. The tag will
                        not
                        fire on the specific page only.</h4>

                    <select class="form-control pys-condition-pysselect2 hide-conditions-url"
                            id="pixel_facebook_hide_url_conditions_<?= $index ?>" style="width: 100%;"
                            multiple>
						<?php foreach ( $pixelInfo->hideConditionByUrl as $tag ) : ?>
                            <option value="<?php echo esc_attr( $tag ); ?>" selected locked="locked">
								<?php echo esc_attr( $tag ); ?>
                            </option>
						<?php endforeach; ?>
                    </select>
                </div>
			<?php endif; ?>

			<?php if ( PixelYourSite\SuperPack()->getOption( 'enable_hide_this_tag_by_tags' ) ) : ?>
                <div class="line-dark"></div>

                <div class="d-flex align-items-center">
                    <div class="secondary-switch">
                        <input type="checkbox" value="1" <?php checked( $pixelInfo->isHide, true ); ?>
                               id="pixel_facebook_is_hide_<?= $index ?>" class="custom-switch-input is-hide">

                        <label class="custom-switch-btn" for="pixel_facebook_is_hide_<?= $index ?>">
                        </label>
                    </div>

                    <h4 class="switcher-label secondary_heading">Hide this tag if the landing URL includes
                        any of
                        these values</h4>
                </div>

                <div>
                    <h4 class="primary_heading mb-4">Hide this tag if the <b>landing page URL</b> includes any of
                        these
                        URL parameters values. The tag will not fire on any pages. </h4>

                    <select class="form-control pys-condition-pysselect2 hide-conditions"
                            id="pixel_facebook_hide_conditions_<?= $index ?>" style="width: 100%;"
                            multiple>
						<?php foreach ( $pixelInfo->hideCondition as $tag ) : ?>
                            <option value="<?php echo esc_attr( $tag ); ?>" selected locked="locked">
								<?php echo esc_attr( $tag ); ?>
                            </option>
						<?php endforeach; ?>
                    </select>
                    <div class="pt-4">
                        <p class="form-text">
                            Use this format: param_name=value or param_name<br>
                            Example: brand=Apple, brand.
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    <h4 class="primary_heading mr-16">Hide for:</h4>

                    <div class="input-number-wrapper">
                        <button class="decrease"><i class="icon-minus"></i></button>
                        <input type="number"
                               value="<?php echo isset( $pixelInfo->hideTime ) && !empty( $pixelInfo->hideTime ) ? $pixelInfo->hideTime : 24 ?>"
                               min="0" class="form-control hide-time"
                               max="720"
                               step="0.01"
                        >
                        <button class="increase"><i class="icon-plus"></i></button>
                    </div>

                    <span class="ml-16">Hours</span>
                </div>
			<?php endif; ?>

			<?php
			if ( $isWpmlActive && !empty( $languageCodes ) ) { ?>
                <div class="line-dark"></div>
				<?php
				$active = $pixelInfo->wpmlActiveLang;
				if ( $active == null && !is_array( $active ) ) {
					$active = $languageCodes;
				}
				printLangList( $active, $languageCodes );
			}
			?>
        </div>
    </div>
<?php endforeach; ?>

    <div class="plate pixel_info pixel_info_multipixel mb-24" id="pys_superpack_facebook_pixel_id"
         style="display: none;">
        <input type="hidden" name="pys[superpack][fb_ext_pixel_id][]" value="" placeholder="0" class="form-control">

        <div class="row align-items-center mb-24">
            <div class="col-12 d-flex align-items-center pixel-switcher-enabled">
                <div class="secondary-switch">
                    <input type="checkbox" value="1" checked
                           id="pixel_facebook_is_enable" class="custom-switch-input is_enable">
                    <label class="custom-switch-btn" for="pixel_facebook_is_enable">
                    </label>
                </div>
                <h4 class="switcher-label secondary_heading">Enable Pixel</h4>
            </div>
        </div>

		<?php include PYS_SUPER_PACK_PATH . '/modules/superpack/views/UI/button-remove-pixel.php'; ?>

        <div class="pixel-data-wrap">
            <div>
                <h4 class="primary_heading mb-4">Meta Pixel ID:</h4>
                <input type="text"
                       placeholder="Meta Pixel ID"
                       class='form-control pixel_id input-standard'/>
                <div class="form-text mt-4">
                    <a href="https://www.pixelyoursite.com/pixelyoursite-free-version/add-your-facebook-pixel"
                       target="_blank" class="link link-small">How to get it?</a>
                </div>
            </div>

            <div>
                <h4 class="primary_heading mb-4">Conversion API:</h4>
                <textarea type="text"
                          placeholder="Api token"
                          class="form-control pixel_ext textarea-standard"
                          data-ext="api_token"></textarea>
            </div>

            <div>
                <p class="text-gray">
                    Send events directly from your web server to Facebook through the Conversion API. This
                    can help you capture more events. An access token is required to use the server-side
                    API.
                    <a href='https://www.pixelyoursite.com/facebook-conversion-api-capi' target='_blank' class="link">Learn
                        how to generate the token and how to test Conversion API</a>
                </p>
            </div>

            <div>
                <h4 class="primary_heading mb-4">Test Event Code:</h4>
                <input type="text" data-ext="api_code"
                       placeholder="Code" class='form-control pixel_ext input-standard'>
                <div class="mt-6">
                    <p class="form-text text-small">
                        Use this if you need to test the server-side event. <strong>Remove it after
                            testing.</strong> The code will auto-delete itself after 24 hours.
                    </p>
                </div>
            </div>

            <div class="gap-16">
                <div>
                    <div class="small-checkbox">
                        <input type="checkbox" value="1"
                               id="facebook_is_fire_signal" checked
                               class="small-control-input is_fire_signal">
                        <label class="small-control small-checkbox-label" for="facebook_is_fire_signal">
                            <span class="small-control-indicator"><i class="icon-check"></i></span>
                            <span class="small-control-description">Fire the active automated events for this pixel</span>
                        </label>
                    </div>
                </div>

				<?php if ( PixelYourSite\isWooCommerceActive() ) : ?>
                    <div>
                        <div class="small-checkbox">
                            <input type="checkbox"
                                   id="facebook_is_fire_woo"
                                   class="small-control-input is_fire_woo" checked>
                            <label class="small-control small-checkbox-label" for="facebook_is_fire_woo">
                                <span class="small-control-indicator"><i class="icon-check"></i></span>
                                <span class="small-control-description">Fire the WooCommerce events for this pixel</span>
                            </label>
                        </div>
                    </div>
				<?php endif; ?>

				<?php if ( PixelYourSite\isEddActive() ) : ?>
                    <div>
                        <div class="small-checkbox">
                            <input type="checkbox"
                                   id="facebook_is_fire_edd"
                                   class="small-control-input is_fire_edd" checked>
                            <label class="small-control small-checkbox-label" for="facebook_is_fire_edd">
                                <span class="small-control-indicator"><i class="icon-check"></i></span>
                                <span class="small-control-description">Fire the Easy Digital Downloads events for this pixel</span>
                            </label>
                        </div>
                    </div>
				<?php endif; ?>
            </div>

            <div>
                <h4 class="primary_heading mb-8">Display conditions:</h4>
                <div class="conditions-logic-track">
                    <label>Logic: </label>
                    <div class="select-standard-wrap">
                        <select class="select-standard" id="facebook_logic_conditional_track">
                            <option value="" disabled selected>Please, select...</option>
                            <?php
                            $track_options = array(
                                'track' => 'Track',
                                'dont_track' => 'Don\'t track',
                            );
                            foreach ( $track_options as $option_key => $option_value ) : ?>
                                <option value="<?php echo esc_attr( $option_key ); ?>"><?php echo esc_attr( $option_value ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php SpPixelCondition()->renderHtml() ?>
            </div>

			<?php if ( PixelYourSite\SuperPack()->getOption( 'enable_hide_this_tag_by_url' ) ) : ?>
                <div class="line-dark"></div>

                <div class="d-flex align-items-center">
                    <div class="secondary-switch">
                        <input type="checkbox" value="1"
                               id="pixel_facebook_is_hide_url" class="custom-switch-input is-hide-url">

                        <label class="custom-switch-btn" for="pixel_facebook_is_hide_url">
                        </label>
                    </div>

                    <h4 class="switcher-label secondary_heading">Hide this tag if the URL includes</h4>
                </div>

                <div class="col-12">
                    <h4 class="primary_heading mb-4">Hide this tag if the page URL any of these values. The tag will
                        not
                        fire on the specific page only.</h4>

                    <select class="form-control pys-condition-pysselect2 hide-conditions-url"
                            id="pixel_facebook_hide_url_conditions" style="width: 100%;"
                            multiple>
                    </select>
                </div>
			<?php endif; ?>

			<?php if ( PixelYourSite\SuperPack()->getOption( 'enable_hide_this_tag_by_tags' ) ) : ?>
                <div class="line-dark"></div>

                <div class="d-flex align-items-center">
                    <div class="secondary-switch">
                        <input type="checkbox" value="1"
                               id="pixel_facebook_is_hide" class="custom-switch-input is-hide">

                        <label class="custom-switch-btn" for="pixel_facebook_is_hide">
                        </label>
                    </div>

                    <h4 class="switcher-label secondary_heading">Hide this tag if the landing URL includes
                        any of
                        these values</h4>
                </div>

                <div>
                    <h4 class="primary_heading mb-4">Hide this tag if the <b>landing page URL</b> includes any of
                        these
                        URL parameters values. The tag will not fire on any pages. </h4>

                    <select class="form-control pys-condition-pysselect2 hide-conditions"
                            id="pixel_facebook_hide_conditions" style="width: 100%;"
                            multiple>
                    </select>

                    <div class="pt-4">
                        <p class="form-text">
                            Use this format: param_name=value or param_name<br>
                            Example: brand=Apple, brand.
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    <h4 class="primary_heading mr-16">Hide for:</h4>

                    <div class="input-number-wrapper">
                        <button class="decrease"><i class="icon-minus"></i></button>
                        <input type="number"
                               value="24"
                               min="0" class="form-control hide-time"
                               max="720"
                               step="0.01"
                        >
                        <button class="increase"><i class="icon-plus"></i></button>
                    </div>

                    <span class="ml-16">Hours</span>
                </div>
			<?php endif; ?>

			<?php if ( $isWpmlActive && !empty( $languageCodes ) ) { ?>
                <div class="line-dark"></div>
				<?php printLangList( $languageCodes, $languageCodes );
			}
			?>
        </div>
    </div>

    <div class="mb-20">
        <button class="btn btn-sm btn-primary btn-primary-type2" type="button"
                id="pys_superpack_add_facebook_pixel_id">
            Add Extra Meta Pixel ID
        </button>
    </div>

<?php
