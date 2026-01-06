<?php

namespace PixelYourSite\SuperPack;

use PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Check if WPML plugin installed and activated.
 *
 * @return bool
 */
function isWPMLActive() {

    if ( ! function_exists( 'is_plugin_active' ) ) {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    if ( ! file_exists( WP_PLUGIN_DIR . '/sitepress-multilingual-cms/sitepress.php' ) ) {
        return false;
    }
    return is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' );
}

function isPysProActive() {

	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
    if ( ! file_exists( WP_PLUGIN_DIR . '/pixelyoursite-pro/pixelyoursite-pro.php' ) ) {
        return false;
    }
	return is_plugin_active( 'pixelyoursite-pro/pixelyoursite-pro.php' );
	
}

function pysProVersionIsCompatible() {
 
	if ( ! function_exists( 'get_plugin_data' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	
	$data = get_plugin_data( WP_PLUGIN_DIR   . '/pixelyoursite-pro/pixelyoursite-pro.php', false, false );

	return version_compare( $data['Version'], PYS_SUPER_PACK_PRO_MIN_VERSION, '>=' );
	
}


function printLangList($activeLang,$languageCodes,$pixelSlag = '') {

?>
<div class="wpml_lags">
    <h4 class="primary_heading mb-4"><strong>WPML Detected.</strong> Fire this pixel for the following languages:</h4>

    <?php if($pixelSlag != '') : ?>
        <input class="pixel_lang" hidden name="pys[<?=$pixelSlag?>][pixel_lang][]"  value="<?=implode('_',$activeLang) ?>"/>
    <?php endif; ?>

    <?php foreach ($languageCodes as $code) :

        $id = $code . '_' . rand(1, 1000000);
        ?>

        <div class="small-checkbox pixel_lang_check_box">
            <input type="checkbox" name="wpml_active_lang[]" value="<?=$code?>"
                   id="<?php echo esc_attr( $id ); ?>"
                   class="small-control-input" <?=in_array($code,$activeLang) ? "checked":""?>>
            <label class="small-control small-checkbox-label" for="<?php echo esc_attr( $id ); ?>">
                <span class="small-control-indicator"><i class="icon-check"></i></span>
                <span class="small-control-description"><?=$code?></span>
            </label>
        </div>

	<?php endforeach; ?>
</div>
<?php
}


function get_public_post_types($args = []) {
    $post_type_args = [
        // Default is the value $public.
        'show_in_nav_menus' => true,
    ];


    $post_type_args = wp_parse_args( $post_type_args, $args );

    $_post_types = get_post_types( $post_type_args, 'objects' );

    $post_types = [];

    foreach ( $_post_types as $post_type => $object ) {
        $post_types[ $post_type ] = $object->label;
    }

    return $post_types;
}

function isMembershipActive() {

	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

	return is_plugin_active( 'memberpress/memberpress.php' );

}