<?php

/**
 * Download PDF.
 *
 * @package WPDesk\Library\WPCoupons\PDF
 */
namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\PDF;

use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\PostMeta;
use FlexibleCouponsProVendor\WPDesk\PluginBuilder\Plugin\Hookable;
/**
 * Download PDF.
 */
class Download implements Hookable
{
    /**
     * @var PostMeta
     */
    private $postmeta;
    /**
     * @var PDF
     */
    private $pdf;
    /**
     * @param PDF      $pdf      PDF.
     * @param PostMeta $postmeta PostMeta.
     */
    public function __construct(PDF $pdf, PostMeta $postmeta)
    {
        $this->pdf = $pdf;
        $this->postmeta = $postmeta;
    }
    public function hooks()
    {
        add_action('wp_ajax_download_coupon_pdf', [$this, 'wp_ajax_download_pdf'], 40, 2);
        add_action('wp_ajax_nopriv_download_coupon_pdf', [$this, 'wp_ajax_download_pdf'], 40, 2);
    }
    public function wp_ajax_download_pdf()
    {
        $this->get_pdf_content();
    }
    public function get_pdf_content($request = [])
    {
        if (empty($request)) {
            $request = wp_unslash($_GET);
            //phpcs:ignore
        }
        if (!isset($request['coupon_id'], $request['hash'])) {
            exit;
        }
        $coupon_data = $this->postmeta->get_private($request['coupon_id'], 'fcpdf_coupon_data');
        if (!isset($coupon_data['hash'])) {
            wp_die(esc_html__('This coupon not exits or expired!', 'flexible-coupons-pro'));
        }
        if ($coupon_data['hash'] !== $request['hash']) {
            exit;
        }
        try {
            $pdf = $this->pdf->string_output($coupon_data['order_id'], $coupon_data['item_id']);
            if (isset($request['return']) && $request['return'] === 'string') {
                return $pdf;
            }
            header('Content-type: application/pdf');
            if (!isset($request['view'])) {
                $coupon_id = $coupon_data['coupon_id'] ?: null;
                $coupon = new \WC_Coupon($coupon_id);
                $code = $coupon->get_code();
                $name = $code . '.pdf';
                $prefix = esc_html__('Coupon', 'flexible-coupons-pro');
                $full_name = $prefix . '-' . $name;
                /**
                 * Define name for PDF downloaded from URL.
                 *
                 * @param string $full_name   Name with prefix & code & pdf extension.
                 * @param string $name        Name with code & pdf extension.
                 * @param array  $coupon_data Coupon meta data.
                 *
                 * @since 1.4.0
                 */
                $name = apply_filters('fcpdf/core/pdf/filename', $full_name, $name, $coupon_data);
                header('Content-Disposition: attachment; filename="' . $name . '"');
                /**
                 * Fire before download.
                 *
                 * @param array $coupon_data Coupon meta data.
                 *
                 * @since 1.4.0
                 */
                do_action('fcpdf/core/pdf/download/headers', $coupon_data);
            }
            echo $pdf;
            //phpcs:ignore
        } catch (\Exception $e) {
            wp_die(esc_html($e->getMessage()));
        }
        exit;
    }
}
