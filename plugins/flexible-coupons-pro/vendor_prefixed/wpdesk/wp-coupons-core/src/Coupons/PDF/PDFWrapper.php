<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\PDF;

use FlexibleCouponsProVendor\Mpdf\Mpdf;
use FlexibleCouponsProVendor\Mpdf\Output\Destination;
use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\EditorAreaProperties;
use FlexibleCouponsProVendor\Mpdf\Config\FontVariables;
/**
 * PDF library wrapper.
 *
 * @package WPDesk\Library\WPCoupons\PDF
 */
class PDFWrapper
{
    /**
     * @var EditorAreaProperties
     */
    private $editor_data;
    /**
     * @return array
     */
    private function get_fonts_data(): array
    {
        $default_font_config = (new FontVariables())->getDefaults();
        $default_font_data = $default_font_config['fontdata'];
        $fonts_data = new FontsData();
        $fonts_data->set_font('lato', 'Lato');
        $fonts_data->set_font('nunito', 'Nunito');
        $fonts_data->set_font('rubik', 'Rubik');
        $fonts_data->set_font('montserrat', 'Montserrat');
        $fonts_data->set_font('opensans', 'OpenSans');
        $fonts_data->set_font('quicksand', 'Quicksand');
        $fonts_data->set_font('roboto', 'Roboto');
        $fonts_data->set_font('raleway', 'Raleway');
        $fonts_data->set_font('titilliumweb', 'TitilliumWeb');
        $fonts_data->set_font('robotoslab', 'RobotoSlab');
        $fonts_data->set_font_without_italic('opensanscondensed', 'OpenSansCondensed');
        $fonts = $fonts_data->get();
        /**
         * Define own font data or add new fonts.
         *
         * Fonts should be added to the assets/fonts directory or own directory defined by flexible_coupons_font_dir filter.
         * Important. The key names must be lowercase.
         *
         * @param array     $font      Declaration of fonts used in the plugin.
         * @param string    $font_data Default fonts data from mPDF.
         * @param FontsData $font_data Class to create fonts data items. See: https://packagist.org/packages/wpdesk/wpdesk-mpdf
         *
         * @since 1.4.0
         */
        return (array) apply_filters('fcpdf/core/fonts/data', $fonts, $default_font_data, $fonts_data);
    }
    /**
     * @return array
     */
    private function get_fonts_dir(): array
    {
        $default_font_dir = [trailingslashit(dirname(__DIR__, 3)) . 'assets/fonts/'];
        /**
         * Define your own font directory or add to an existing one.
         *
         * @param array $fonts_dir Array of directories.
         *
         * @since 1.4.0
         */
        return (array) apply_filters('fcpdf/core/fonts/dir', $default_font_dir);
    }
    /**
     * @param EditorAreaProperties $editor_data
     */
    public function set_editor_data(EditorAreaProperties $editor_data)
    {
        $this->editor_data = $editor_data;
    }
    /**
     * @return string
     */
    private function get_temp_dir(): string
    {
        $upload_dir = wp_upload_dir();
        /**
         * Define own tmp dir for mpdf.
         *
         * @param string $dir Directory path.
         *
         * @since 1.5.0
         */
        $temp_dir = apply_filters('fcpdf/core/mpdf/tmp', trailingslashit($upload_dir['basedir']) . 'flexible-coupons/tmp/');
        wp_mkdir_p($temp_dir);
        return $temp_dir;
    }
    /**
     * Define MPDF config
     */
    private function get_config()
    {
        $config = new Config();
        $config->set_format($this->editor_data->get_format());
        $config->set_orientation($this->editor_data->get_orientation());
        $config->set_font_data($this->get_fonts_data());
        $config->set_font_dir($this->get_fonts_dir());
        $config->set_temp_dir($this->get_temp_dir());
        $config->set_mode('ja+aCJK');
        $config->set_auto_script_to_lang(\false);
        $config->set_auto_lang_to_font(\true);
        /**
         * Define own config for mpdf.
         *
         * @param array $config Config array.
         *
         * @since 1.5.0
         */
        return (array) apply_filters('fcpdf/core/mpdf/config', $config->get());
    }
    /**
     * @param string $html
     *
     * @return string
     * @throws \Mpdf\MpdfException
     */
    public function render($html)
    {
        $mpdf = new Mpdf($this->get_config());
        // sometimes clients has problem displaying images, becouse of ssl certificate (not trusted).
        $mpdf->curlAllowUnsafeSslRequests = \true;
        // phpcs:ignore
        $mpdf->pdf_version = '1.5';
        $mpdf->WriteHTML($html);
        return $mpdf->Output('', Destination::STRING_RETURN);
    }
}
