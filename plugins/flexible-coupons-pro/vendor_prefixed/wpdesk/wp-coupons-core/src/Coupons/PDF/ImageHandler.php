<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\PDF;

use Exception;
class ImageHandler
{
    // @phpstan-ignore-next-line
    public static function get_simple_image_html(array $object): string
    {
        $styles = '
		transform: rotate(' . $object['rotateAngle'] . 'deg);
		position: absolute;
		top: ' . $object['top'] . 'px;
		left: ' . $object['left'] . 'px;
		width: ' . $object['width'] . 'px;
		height: ' . $object['height'] . 'px;
		background: transparent url(' . $object['url'] . ') 0 0 no-repeat;
		background-size: ' . $object['width'] . 'px ' . $object['height'] . 'px;
		';
        return '<div ' . Items::rtl_dir() . ' class="image" style="' . $styles . '"></div>';
    }
    /**
     * Pre-renders a rotated image using the GD library and returns a simple <img> tag.
     *
     * @param array $object
     *
     * @return string
     * @throws Exception
     */
    // @phpstan-ignore-next-line
    public static function get_prerotated_image_html(array $object): string
    {
        $source_url = $object['url'];
        $angle = (float) $object['rotateAngle'] * -1;
        $target_w = (int) $object['width'];
        $target_h = (int) $object['height'];
        $supersample_factor = 2;
        $high_res_w = $target_w * $supersample_factor;
        $high_res_h = $target_h * $supersample_factor;
        $image_data = @file_get_contents($source_url);
        if ($image_data === \false) {
            throw new Exception('Could not read image file.');
        }
        $source_image = @imagecreatefromstring($image_data);
        if (!$source_image) {
            throw new Exception('Could not create image from source.');
        }
        $source_w = imagesx($source_image);
        $source_h = imagesy($source_image);
        $high_res_image = imagecreatetruecolor($high_res_w, $high_res_h);
        imagealphablending($high_res_image, \false);
        imagesavealpha($high_res_image, \true);
        $transparent_background = imagecolorallocatealpha($high_res_image, 255, 255, 255, 127);
        imagefill($high_res_image, 0, 0, $transparent_background);
        imagecopyresampled($high_res_image, $source_image, 0, 0, 0, 0, $high_res_w, $high_res_h, $source_w, $source_h);
        imagedestroy($source_image);
        $rotated_image = imagerotate($high_res_image, $angle, $transparent_background);
        if (!$rotated_image) {
            imagedestroy($high_res_image);
            throw new Exception('Image rotation failed.');
        }
        imagedestroy($high_res_image);
        imagesavealpha($rotated_image, \true);
        $final_w = imagesx($rotated_image);
        $final_h = imagesy($rotated_image);
        $upload_dir = wp_upload_dir();
        $temp_dir = trailingslashit($upload_dir['basedir']) . 'flexible-coupons/tmp/';
        wp_mkdir_p($temp_dir);
        $temp_filename = 'rotated_hq_final_' . md5($source_url . $angle . $target_w . $target_h) . '.png';
        $temp_filepath = $temp_dir . $temp_filename;
        imagepng($rotated_image, $temp_filepath, 9);
        imagedestroy($rotated_image);
        $temp_url = trailingslashit($upload_dir['baseurl']) . 'flexible-coupons/tmp/' . $temp_filename;
        $new_left = $object['left'] + ($target_w - $final_w / $supersample_factor) / 2;
        $new_top = $object['top'] + ($target_h - $final_h / $supersample_factor) / 2;
        $container_w = $final_w / $supersample_factor;
        $container_h = $final_h / $supersample_factor;
        $styles = 'position: absolute; left: ' . $new_left . 'px; top: ' . $new_top . 'px; width: ' . $container_w . 'px; height: ' . $container_h . 'px;';
        return '<div style="' . $styles . '"><img src="' . $temp_url . '" style="width: 100%; height: 100%;" /></div>';
    }
}
