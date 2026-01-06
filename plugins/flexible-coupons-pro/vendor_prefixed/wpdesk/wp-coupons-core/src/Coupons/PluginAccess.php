<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons;

use FlexibleCouponsProVendor\Psr\Log\LoggerInterface;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\PostMeta;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\PDF\Download;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\PDF\PDF;
use FlexibleCouponsProVendor\WPDesk\Persistence\PersistentContainer;
use FlexibleCouponsProVendor\WPDesk\View\Renderer\Renderer;
use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\ProductFields;
class PluginAccess
{
    /**
     * @var Renderer
     */
    private $renderer;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var PDF
     */
    private $pdf;
    /**
     * @var Download
     */
    private $download;
    /**
     * @var array
     */
    private $shortcodes;
    /**
     * @var PersistentContainer
     */
    private $persistence;
    /**
     * @var PostMeta
     */
    private $post_meta;
    /**
     * @var ProductFields
     */
    private $product_fields;
    /**
     * @var string
     */
    private $plugin_version;
    public function __construct(Renderer $renderer, LoggerInterface $logger, PersistentContainer $persistence, PDF $pdf, Download $download, array $shortcodes, PostMeta $postmeta, ProductFields $product_fields, string $plugin_version)
    {
        $this->renderer = $renderer;
        $this->logger = $logger;
        $this->persistence = $persistence;
        $this->pdf = $pdf;
        $this->download = $download;
        $this->shortcodes = $shortcodes;
        $this->post_meta = $postmeta;
        $this->product_fields = $product_fields;
        $this->plugin_version = $plugin_version;
    }
    /**
     * @return Renderer
     */
    public function get_renderer(): Renderer
    {
        return $this->renderer;
    }
    /**
     * @return LoggerInterface
     */
    public function get_logger(): LoggerInterface
    {
        return $this->logger;
    }
    /**
     * @return PersistentContainer
     */
    public function get_persistence(): PersistentContainer
    {
        return $this->persistence;
    }
    /**
     * @return PDF
     */
    public function get_pdf(): PDF
    {
        return $this->pdf;
    }
    /**
     * @return Download
     */
    public function get_download(): Download
    {
        return $this->download;
    }
    /**
     * @return array
     */
    public function get_shortcodes(): array
    {
        return $this->shortcodes;
    }
    /**
     * @return PostMeta
     */
    public function get_post_meta(): PostMeta
    {
        return $this->post_meta;
    }
    /**
     * @return ProductFields
     */
    public function get_product_fields(): ProductFields
    {
        return $this->product_fields;
    }
    public function get_plugin_version(): string
    {
        return $this->plugin_version;
    }
}
