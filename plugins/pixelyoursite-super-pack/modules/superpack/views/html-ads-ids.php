<?php

namespace PixelYourSite\SuperPack;

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use PixelYourSite;

?>

<?php
$isWpmlActive = isWPMLActive();
if ( $isWpmlActive ) {
	$languageCodes = array_keys( apply_filters( 'wpml_active_languages', null, null ) );
}

$pixelsInfo = PixelYourSite\SuperPack()->getAdsAdditionalPixel();

foreach ( $pixelsInfo as $index => $pixelInfo ) : ?>

    <div class="plate pixel_info pixel_info_multipixel mb-24">
		<?php PixelYourSite\SuperPack()->render_text_input_array_item( 'ads_ext_pixel_id', "", $index, true ); ?>

        <div class="d-flex align-items-center pixel-switcher-enabled mb-24">
            <div class="secondary-switch">
                <input type="checkbox" value="1" <?php checked( $pixelInfo->isEnable, true ); ?>
                       id="pixel_ads_is_enable_<?= $index ?>" class="custom-switch-input is_enable">
                <label class="custom-switch-btn" for="pixel_ads_is_enable_<?= $index ?>">
                </label>
            </div>
            <h4 class="switcher-label secondary_heading">Enable Pixel</h4>
        </div>

		<?php include PYS_SUPER_PACK_PATH . '/modules/superpack/views/UI/button-remove-pixel.php'; ?>

        <div class="pixel-data-wrap">
            <div>
                <h4 class="primary_heading mb-4">Google Ads Tag:</h4>
                <input type="text" value="<?= $pixelInfo->pixel ?>"
                       placeholder="AW-123456789" class='form-control pixel_id input-standard'/>

                <div class="form-text mt-4">
                    <a href="https://www.pixelyoursite.com/documentation/google-ads-tag"
                       target="_blank" class="link link-small">How to get it?</a>
                </div>
            </div>

            <div class="d-flex align-items-center">
				<?php PixelYourSite\Ads()->render_switcher_input_array( "enhanced_conversions_manual_enabled", ( $index + 1 ) ); ?>

                <h4 class="switcher-label secondary_heading">Enable enhanced conversions</h4>
            </div>

            <div>
                <p class="text-gray pb-8">
                    Enhanced conversion data is sent when you add a conversion label to your events.
                    You need to select <b>Manual setup > Edit code" when creating the conversion inside your Google
                        Ads account</b>.
                    The enhanced conversion data is sent for all WooCommerce and Easy Digital Downloads
                    purchase-related conversions.
                    For the other events, we send it for logged-in users only, or when we can detect if from forms
                    using Advanced user-data detection.
                </p>
            </div>

            <div class="line-dark"></div>

            <div>
                <p class="text-gray pb-8">
                    How to enable Google Consent Mode V2:
                    <a href=https://www.pixelyoursite.com/google-consent-mode-v2-wordpress?utm_source=plugin&utm_medium=pro&utm_campaign=google-consent"
                       target="_blank" class="link">click here</a>
                </p>
                <p class="text-gray pb-8">
                    Learn how to get the Google Analytics 4 tag ID and how to test it:
                    <a href="https://www.youtube.com/watch?v=KkiGbfl1q48" target="_blank" class="link">watch
                        video</a>
                </p>
                <p class="text-gray pb-8">
                    Install the old Google Analytics UA property and the new GA4 at the same time:
                    <a href="https://www.youtube.com/watch?v=JUuss5sewxg" target="_blank" class="link">watch
                        video</a>
                </p>
                <p class="text-gray">
                    Learn how to get your Measurement Protocol API secret:
                    <a href="https://www.youtube.com/watch?v=cURMzxY3JSg" target="_blank" class="link">watch
                        video</a>
                </p>
            </div>

            <div class="gap-16">

                <div>
                    <div class="small-checkbox">
                        <input type="checkbox" value="1"
                               id="ads_is_fire_signal_<?php echo esc_attr( $index ); ?>"
                               class="small-control-input is_fire_signal" <?php checked( $pixelInfo->isFireForSignal, true ); ?>>
                        <label class="small-control small-checkbox-label"
                               for="ads_is_fire_signal_<?php echo esc_attr( $index ); ?>">
                            <span class="small-control-indicator"><i class="icon-check"></i></span>
                            <span class="small-control-description">Fire the active automated events for this pixel</span>
                        </label>
                    </div>
                </div>

				<?php if ( PixelYourSite\isWooCommerceActive() ) : ?>
                    <div>
                        <div class="small-checkbox">
                            <input type="checkbox"
                                   id="ads_is_fire_woo_<?php echo esc_attr( $index ); ?>"
                                   class="small-control-input is_fire_woo" <?php checked( $pixelInfo->isFireForWoo, true ); ?>>
                            <label class="small-control small-checkbox-label"
                                   for="ads_is_fire_woo_<?php echo esc_attr( $index ); ?>">
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
                                   id="ads_is_fire_edd_<?php echo esc_attr( $index ); ?>"
                                   class="small-control-input is_fire_edd" <?php checked( $pixelInfo->isFireForEdd, true ); ?>>
                            <label class="small-control small-checkbox-label"
                                   for="ads_is_fire_edd_<?php echo esc_attr( $index ); ?>">
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
                        <select class="select-standard" id="ads_logic_conditional_track_<?=$index?>">
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
                               id="pixel_ads_is_hide_url_<?= $index ?>"
                               class="custom-switch-input is-hide-url">

                        <label class="custom-switch-btn" for="pixel_ads_is_hide_url_<?= $index ?>">
                        </label>
                    </div>

                    <h4 class="switcher-label secondary_heading">Hide this tag if the URL includes</h4>
                </div>

                <div>
                    <h4 class="primary_heading mb-4">Hide this tag if the page URL any of these values. The tag will
                        not
                        fire on the specific page only.</h4>

                    <select class="form-control pys-condition-pysselect2 hide-conditions-url"
                            id="pixel_ads_hide_url_conditions_<?= $index ?>" style="width: 100%;"
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
                               id="pixel_ads_is_hide_<?= $index ?>" class="custom-switch-input is-hide">

                        <label class="custom-switch-btn" for="pixel_ads_is_hide_<?= $index ?>">
                        </label>
                    </div>

                    <h4 class="switcher-label secondary_heading">Hide this tag if the landing URL includes any of
                        these values</h4>
                </div>

                <div>
                    <h4 class="primary_heading mb-4">Hide this tag if the <b>landing page URL</b> includes any of
                        these URL parameters values. The tag will not fire on any pages. </h4>

                    <select class="form-control pys-condition-pysselect2 hide-conditions"
                            id="pixel_ads_hide_conditions_<?= $index ?>" style="width: 100%;"
                            multiple>
						<?php foreach ( $pixelInfo->hideCondition as $tag ) : ?>
                            <option value="<?php echo esc_attr( $tag ); ?>" selected locked="locked">
								<?php echo esc_attr( $tag ); ?>
                            </option>
						<?php endforeach; ?>
                    </select>
                    <div class="form-text pt-4">
                        <p class="form-text">Use the parameter and value (param_name=value), or just the parameter's
                            name (param_name).</p>
                        <p class="form-text">Example: Use brand=Apple, or brand, to hide the tag when the landing page
                            URL contains
                            brand=Apple</p>
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    <h4 class="primary_heading mr-16">Hide for:</h4>

                    <div class="input-number-wrapper">
                        <button class="decrease"><i class="icon-minus"></i></button>
                        <input type="number"
                               value="<?php echo !empty( $pixelInfo->hideTime ) ? $pixelInfo->hideTime : 24 ?>"
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

<div class="plate pixel_info pixel_info_multipixel mb-24" id="pys_superpack_google_ads_id"
     style="display: none;">
    <input type="hidden" name="pys[superpack][ads_ext_pixel_id][]" value="" placeholder="0" class="form-control">

    <div class="d-flex align-items-center pixel-switcher-enabled mb-24">
        <div class="secondary-switch">
            <input type="checkbox" value="1" checked
                   id="pixel_ads_is_enable" class="custom-switch-input is_enable">
            <label class="custom-switch-btn" for="pixel_ads_is_enable">
            </label>
        </div>
        <h4 class="switcher-label secondary_heading">Enable Pixel</h4>
    </div>

	<?php include PYS_SUPER_PACK_PATH . '/modules/superpack/views/UI/button-remove-pixel.php'; ?>

    <div class="pixel-data-wrap">
        <div>
            <h4 class="primary_heading mb-4">Google Ads Tag:</h4>
            <input type="text" value="" placeholder="AW-123456789" class='form-control pixel_id input-standard'/>

            <div class="form-text mt-4">
                <a href="https://www.pixelyoursite.com/documentation/google-ads-tag"
                   target="_blank" class="link link-small">How to get it?</a>
            </div>
        </div>

        <div class="d-flex align-items-center">
            <div class="secondary-switch">
                <input type="checkbox"
                       name="enhanced_conversions_manual_enabled"
                       value="1" checked
                       id="enhanced_conversions_manual_enabled"
                       class="custom-switch-input enhanced_conversions_manual_enabled">
                <label class="custom-switch-btn" for="enhanced_conversions_manual_enabled"></label>
            </div>

            <h4 class="switcher-label secondary_heading">Enable enhanced conversions</h4>
        </div>

        <div>
            <p class="text-gray pb-8">
                Enhanced conversion data is sent when you add a conversion label to your events.
                You need to select <b>Manual setup > Edit code" when creating the conversion inside your Google Ads
                    account</b>.
                The enhanced conversion data is sent for all WooCommerce and Easy Digital Downloads purchase-related
                conversions.
                For the other events, we send it for logged-in users only, or when we can detect if from forms using
                Advanced user-data detection.
            </p>
        </div>

        <div class="line-dark"></div>

        <div>
            <p class="text-gray pb-8">
                How to enable Google Consent Mode V2:
                <a href=https://www.pixelyoursite.com/google-consent-mode-v2-wordpress?utm_source=plugin&utm_medium=pro&utm_campaign=google-consent"
                   target="_blank" class="link">click here</a>
            </p>
            <p class="text-gray pb-8">
                How to install the Google the Google Ads Tag:
                <a href="https://www.youtube.com/watch?v=dft-TRigkj0" target="_blank" class="link">watch video</a>
            </p>
            <p class="text-gray pb-8">
                How to configure Google Ads Conversions:
                <a href="https://www.youtube.com/watch?v=5kb-jQe-Psg" target="_blank" class="link">watch video</a>
            </p>
            <p class="text-gray">
                Lear how to use Enhanced Conversions:
                <a href="https://www.youtube.com/watch?v=-bN5D_HJyuA" target="_blank" class="link">watch video</a>
            </p>
        </div>

        <div class="gap-16">
            <div class="small-checkbox">
                <input type="checkbox" value="1"
                       id="ads_is_fire_signal" checked
                       class="small-control-input is_fire_signal">
                <label class="small-control small-checkbox-label" for="ads_is_fire_signal">
                    <span class="small-control-indicator"><i class="icon-check"></i></span>
                    <span class="small-control-description">Fire the active automated events for this pixel</span>
                </label>
            </div>

			<?php if ( PixelYourSite\isWooCommerceActive() ) : ?>
                <div>
                    <div class="small-checkbox">
                        <input type="checkbox"
                               id="ads_is_fire_woo"
                               class="small-control-input is_fire_woo" checked>
                        <label class="small-control small-checkbox-label" for="ads_is_fire_woo">
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
                               id="ads_is_fire_edd"
                               class="small-control-input is_fire_edd" checked>
                        <label class="small-control small-checkbox-label" for="ads_is_fire_edd">
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
                    <select class="select-standard" id="ads_logic_conditional_track">
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
                           id="pixel_ads_is_hide_url" class="custom-switch-input is-hide-url">

                    <label class="custom-switch-btn" for="pixel_ads_is_hide_url">
                    </label>
                </div>

                <h4 class="switcher-label secondary_heading">Hide this tag if the URL includes</h4>
            </div>

            <div>
                <h4 class="primary_heading mb-4">Hide this tag if the page URL any of these values. The tag will
                    not fire on the specific page only.</h4>

                <select class="form-control pys-condition-pysselect2 hide-conditions-url"
                        id="pixel_ads_hide_url_conditions" style="width: 100%;"
                        multiple>
                </select>
            </div>
		<?php endif; ?>

		<?php if ( PixelYourSite\SuperPack()->getOption( 'enable_hide_this_tag_by_tags' ) ) : ?>
            <div class="line-dark"></div>

            <div class="d-flex align-items-center">
                <div class="secondary-switch">
                    <input type="checkbox" value="1"
                           id="pixel_ads_is_hide" class="custom-switch-input is-hide">

                    <label class="custom-switch-btn" for="pixel_ads_is_hide">
                    </label>
                </div>

                <h4 class="switcher-label secondary_heading">Hide this tag if the landing URL includes
                    any of these values</h4>
            </div>

            <div>
                <h4 class="primary_heading mb-4">Hide this tag if the <b>landing page URL</b> includes any of
                    these URL parameters values. The tag will not fire on any pages. </h4>

                <select class="form-control pys-condition-pysselect2 hide-conditions"
                        id="pixel_ads_hide_conditions" style="width: 100%;"
                        multiple>
                </select>

                <div class="form-text pt-4">
                    <p class="form-text">Use the parameter and value (param_name=value), or just the parameter's
                        name (param_name).</p>
                    <p class="form-text">Example: Use brand=Apple, or brand, to hide the tag when the landing page
                        URL contains
                        brand=Apple</p>
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
            id="pys_superpack_add_google_ads_id">
        Add Extra Google Ads Tag
    </button>
</div>


