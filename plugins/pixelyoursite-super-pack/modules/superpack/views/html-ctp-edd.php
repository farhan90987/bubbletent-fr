<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<div>
    <div class="mb-24">
        <label class="primary_heading"><?php _e('Easy Digital Downloads', 'pys');?></label>
    </div>
    <p class="text-gray"><?php _e('You can set up a global Easy Digital Downloads Thank You Page here. If you need to,
	        you can also define Custom Thank You Pages for each product (edit the product and you will find this
	        option in the right side menu).', 'pys');?></p>
</div>
<div class="offset-block">
    <div class="d-flex align-items-center">
        <?php SuperPack()->render_switcher_input( 'edd_custom_thank_you_page_global_enabled', true ); ?>
        <h4 class="switcher-label secondary_heading"><?php _e('Enable Easy Digital Downloads Global Thank You Page', 'pys');?></h4>
    </div>
    <div <?php renderCollapseTargetAttributes( 'edd_custom_thank_you_page_global_enabled', SuperPack() ); ?> class="pt-24">
        <div>
            <div>
                <h4 class="primary_heading mb-4"><?php _e( 'Global Custom Page URL:', 'pys' );?></h4>
                <?php SuperPack()->render_text_input( 'edd_custom_thank_you_page_global_url', __('Enter URL', 'pys') ); ?>
            </div>
        </div>
        <div>
            <div class="d-flex align-items-center mb-24">
                <h4 class="primary_heading"><?php echo esc_html( __('Order Details:', 'pys') ); ?></h4>
            </div>
            <div class="radio-inputs-wrap mb-16">
                <?php SuperPack()->render_radio_input( 'edd_custom_thank_you_page_global_cart', 'hidden', __('Hidden', 'pys')); ?>
                <?php SuperPack()->render_radio_input( 'edd_custom_thank_you_page_global_cart', 'after', __('After page content', 'pys')); ?>
                <?php SuperPack()->render_radio_input( 'edd_custom_thank_you_page_global_cart', 'before', __('Before page content', 'pys')); ?>
            </div>
        </div>
    </div>
</div>
