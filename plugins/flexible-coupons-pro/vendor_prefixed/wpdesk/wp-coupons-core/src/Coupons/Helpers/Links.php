<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Helpers;

/**
 * All plugin related links
 */
class Links
{
    /**
     * @return string
     */
    public static function get_doc_link(): string
    {
        $docs_link = 'https://wpdesk.net/docs/flexible-coupons-pro/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-docs-link';
        if (get_locale() === 'pl_PL') {
            $docs_link = 'https://www.wpdesk.pl/docs/flexible-coupons-pro/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-docs-link';
        }
        return $docs_link;
    }
    /**
     * @return string
     */
    public static function get_pro_link(): string
    {
        $pro_link = 'https://wpdesk.net/products/flexible-coupons-woocommerce/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-pro';
        if (get_locale() === 'pl_PL') {
            $pro_link = 'https://www.wpdesk.pl/sklep/flexible-coupons-woocommerce/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-pro';
        }
        return $pro_link;
    }
    /**
     * @return string
     */
    public static function get_fcs_link(): string
    {
        $sending_link = 'https://wpdesk.net/products/flexible-coupons-pro-advanced-sending/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-advanced-sending';
        if (get_locale() === 'pl_PL') {
            $sending_link = 'https://www.wpdesk.pl/sklep/kupony-woocommerce-pro-zaawansowana-wysylka/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-advanced-sending';
        }
        return $sending_link;
    }
    public static function get_fcmpdf_link(): string
    {
        $sending_link = 'https://flexiblecoupons.net/products/flexible-pdf-coupons-pro-multiple-pdfs/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-multiple-pdfs';
        if (get_locale() === 'pl_PL') {
            $sending_link = 'https://www.wpdesk.pl/sklep/kupony-pdf-woocommerce-multi-kupony/?utmsource=wp-admin-plugins&utmmedium=link&utm_campaign=flexible-coupons-multiple-pdfs';
        }
        return $sending_link;
    }
    /**
     * @return string
     */
    public static function get_bundle_link(): string
    {
        $bundle_link = 'https://wpdesk.net/products/all-plugins-bundle-1-site/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-bundle';
        if (get_locale() === 'pl_PL') {
            $bundle_link = 'https://www.wpdesk.pl/sklep/pakiet-kupony/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-bundle';
        }
        return $bundle_link;
    }
    /**
     * @return string
     */
    public static function get_fcs_doc_delay_type_link(): string
    {
        $docs_link = 'https://wpdesk.link/as-advanced-sending-docs-product-settings';
        if (get_locale() === 'pl_PL') {
            $docs_link = 'https://wpdesk.link/as-advanced-sending-docs-product-settings-pl';
        }
        return $docs_link;
    }
    /**
     * @return string
     */
    public static function get_fcs_doc_link(): string
    {
        $docs_link = 'https://wpdesk.link/as-advanced-sending-docs';
        if (get_locale() === 'pl_PL') {
            $docs_link = 'https://wpdesk.link/as-advanced-sending-docs-pl';
        }
        return $docs_link;
    }
    public static function get_fcci_buy_link(): string
    {
        $sending_link = 'https://flexiblecoupons.net/products/flexible-pdf-coupons-pro-coupon-codes-import/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-code-import';
        if (get_locale() === 'pl_PL') {
            $sending_link = 'https://www.wpdesk.pl/sklep/kupony-pdf-woocommerce-pro-import-kodow/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-code-import';
        }
        return $sending_link;
    }
}
