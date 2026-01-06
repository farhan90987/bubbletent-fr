<?php

namespace PixelYourSite;

use PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<div class="cards-wrapper cards-wrapper-style2 gap-24 settings-wrapper">
    <!-- General -->
    <div class="card card-style6 card-static">
        <div class="card-header card-header-style2 d-flex justify-content-between align-items-center">
            <h4 class="secondary_heading_type2"><?php _e('General', 'pys');?></h4>
        </div>
        <div class="card-body">
            <div class="gap-24">
                <div>
                    <div class="d-flex align-items-center">
                        <?php SuperPack()->render_switcher_input( 'enabled' ); ?>
                        <h4 class="switcher-label secondary_heading"><?php _e('Enable Super Pack', 'pys');?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Additional Pixel IDs -->
    <div class="card card-style6 card-static">
        <div class="card-header card-header-style2 d-flex justify-content-between align-items-center">
            <h4 class="secondary_heading_type2"><?php _e('Advanced Pixel Options', 'pys');?></h4>
        </div>
        <div class="card-body">
            <div class="gap-24">
                <div>
                    <p class="text-gray mb-24">
                        <?php _e('Add support for multiple pixels (Meta, Google, TikTok), enable pixel display conditions, and add extra controls for automated events for each pixel.', 'pys'); ?>
                    </p>
                    <div class="d-flex align-items-center">
                        <?php SuperPack()->render_switcher_input( 'additional_ids_enabled' ); ?>
                        <h4 class="switcher-label secondary_heading"><?php _e('Enable advanced pixel options', 'pys');?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Dynamic Parameters -->
    <div class="card card-style6 card-static">
        <div class="card-header card-header-style2 d-flex justify-content-between align-items-center">
            <h4 class="secondary_heading_type2"><?php _e('Dynamic Parameters for Events', 'pys');?></h4>
        </div>
        <div class="card-body">
            <div class="gap-24">
                <div>
                    <p class="text-gray mb-24">
                        <?php _e('Use page title, post ID, category or tags as your dynamic events parameters.', 'pys'); ?>
                    </p>
                    <div class="d-flex align-items-center">
                        <?php SuperPack()->render_switcher_input( 'dynamic_params_enabled' ); ?>
                        <h4 class="switcher-label secondary_heading"><?php _e('Enable dynamic params', 'pys');?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Custom Thank You Page -->
    <div class="card card-style6 card-static">
        <div class="card-header card-header-style2 d-flex justify-content-between align-items-center">
            <h4 class="secondary_heading_type2"><?php _e('Custom Thank You Pages', 'pys');?></h4>
        </div>
        <div class="card-body">
            <div class="gap-24">
                <div>
                    <p class="text-gray mb-24">
                        <?php _e('Define custom thank you pages (general or for a particular product) and fire the Meta Pixel on it.', 'pys'); ?>
                    </p>
                    <div class="d-flex align-items-center">
                        <?php SuperPack()->render_switcher_input( 'custom_thank_you_page_enabled' ); ?>
                        <h4 class="switcher-label secondary_heading"><?php _e('Enable Custom Thank You Pages', 'pys');?></h4>
                    </div>
                </div>
                <div class="<?php echo 'pys_' . SuperPack()->getSlug() . '_' . esc_attr( 'custom_thank_you_page_enabled' ) . '_panel gap-24'; ?>">
                    <?php if ( isWooCommerceActive() ) : ?>
                        <?php include 'html-ctp-woo.php'; ?>
                    <?php endif; ?>

                    <?php if ( isEddActive() ) : ?>
                        <?php include 'html-ctp-edd.php'; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- Hide this tag -->
    <div class="card card-style6 card-static">
        <div class="card-header card-header-style2 d-flex justify-content-between align-items-center">
            <h4 class="secondary_heading_type2"><?php _e('Hide tag', 'pys');?></h4>
        </div>
        <div class="card-body">
            <div class="gap-24">
                <div>
                    <div class="d-flex align-items-center">
                        <?php SuperPack()->render_switcher_input( 'enable_hide_this_tag_by_tags' ); ?>
                        <h4 class="switcher-label secondary_heading"><?php _e('Hide this tag if the landing URL includes these URL tags', 'pys');?></h4>
                    </div>
                </div>
                <div>
                    <div class="d-flex align-items-center">
                        <?php SuperPack()->render_switcher_input( 'enable_hide_this_tag_by_url' ); ?>
                        <h4 class="switcher-label secondary_heading"><?php _e('Hide this tag if the URL includes', 'pys');?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Remove Pixel -->
    <div class="card card-style6 card-static">
        <div class="card-header card-header-style2 d-flex justify-content-between align-items-center">
            <h4 class="secondary_heading_type2"><?php _e('Remove Pixel from Pages', 'pys');?></h4>
        </div>
        <div class="card-body">
            <div class="gap-24">
                <div>
                    <p class="text-gray mb-24"><?php _e('Remove Facebook, Google Analytics or Pinterest pixels from a particular page or post.', 'pys');?></p>
                    <div class="d-flex align-items-center">
                        <?php SuperPack()->render_switcher_input( 'remove_pixel_enabled' ); ?>
                        <h4 class="switcher-label secondary_heading"><?php _e('Enable remove pixel from pages', 'pys');?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- AMP -->
    <div class="card card-style6 card-static">
        <div class="card-header card-header-style2 d-flex justify-content-between align-items-center">
            <h4 class="secondary_heading_type2"><?php _e('AMP Support', 'pys');?></h4>
        </div>
        <div class="card-body">
            <div class="gap-24">
                <div>
                    <p class="text-gray mb-24"><?php _e('Fire Facebook, Google Analytics or Pinterest pixels on AMP pages.', 'pys');?></p>
                    <div class="d-flex align-items-center">
                        <?php SuperPack()->render_switcher_input( 'amp_enabled' ); ?>
                        <h4 class="switcher-label secondary_heading"><?php _e('Enable AMP integration', 'pys');?></h4>
                    </div>
                </div>
                <div>
                    <div class="d-flex align-items-center">
                        <?php if ( SuperPack\isAMPactivated() ) : ?>
                            <div class="indicator"><span>ON</span></div>
                        <?php else : ?>
                            <div class="indicator indicator-off"><span>OFF</span></div>
                        <?php endif; ?>
                        <h4 class="indicator-label">AMP by <a class="link" href="https://wordpress.org/plugins/amp/" target="_blank">WordPress.com VIP, XWP, Google, and contributors</a></h4>
                    </div>
                </div>
                <div>
                    <div class="d-flex align-items-center">
                        <?php if ( SuperPack\isAMPforWPactivated() ) : ?>
                            <div class="indicator"><span>ON</span></div>
                        <?php else : ?>
                            <div class="indicator indicator-off"><span>OFF</span></div>
                        <?php endif; ?>
                        <h4 class="indicator-label">Accelerated Mobile Pages by <a class="link" href="https://wordpress.org/plugins/accelerated-mobile-pages/" target="_blank">Ahmed Kaludi, Mohammed Kaludi</a></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
