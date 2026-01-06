<?php

/**
 * Coupons. Assets.
 *
 * @package WPDesk\Library\WPCoupons\Integration
 */
namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration;

use RuntimeException;
use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\Shortcode;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Shortcodes\Shortcodes;
use FlexibleCouponsProVendor\WPDesk\PluginBuilder\Plugin\Hookable;
/**
 * Enqueue coupon scripts and styles.
 *
 * @package WPDesk\Library\WPCoupons\Integration
 */
class Assets implements Hookable
{
    /**
     * @var string
     */
    private $editor_post_type;
    /**
     * @var array
     */
    private $shortcodes;
    /**
     * @var string
     */
    private $scripts_version;
    private const COUPON_LISTING_PAGE = 'edit-wpdesk-coupons';
    private const COUPON_EDIT_PAGE = 'wpdesk-coupons';
    /**
     * @param string $editor_post_type Editor post type.
     * @param array  $shortcodes       Shortcodes container.
     */
    public function __construct(string $editor_post_type, array $shortcodes)
    {
        $this->editor_post_type = $editor_post_type;
        $this->shortcodes = $this->prepare_shortcodes_definition($shortcodes);
        $this->scripts_version = date('y.m.d H:i');
    }
    /**
     * Prepare shortcodes definition for react editor.
     *
     * @param array $shortcodes
     *
     * @return array
     */
    public function prepare_shortcodes_definition(array $shortcodes): array
    {
        $editor_shortcodes = [];
        foreach ($shortcodes as $shortcode) {
            if ($shortcode instanceof Shortcode) {
                $editor_shortcodes[] = $shortcode->definition();
            }
        }
        return $editor_shortcodes;
    }
    /**
     * Fires hooks
     */
    public function hooks()
    {
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'wp_enqueue_scripts']);
    }
    /**
     * @return string
     */
    private function get_assets_url(): string
    {
        return trailingslashit(plugin_dir_url(dirname(__DIR__, 2))) . 'assets/';
    }
    /**
     * Enqueue admin scripts.
     */
    public function admin_enqueue_scripts()
    {
        $screen = get_current_screen();
        $screen_id = $screen->id ?? '';
        $post_type = $screen->post_type ?? '';
        $screen_base = $screen->base ?? '';
        wp_enqueue_style('admin', $this->get_assets_url() . 'css/admin.css', [], $this->scripts_version);
        if ('product' === $post_type || 'shop_order' === $post_type || isset($_REQUEST['page']) && 'wc-orders' === $_REQUEST['page']) {
            wp_register_script('fc-coupons-admin', $this->get_assets_url() . 'js/admin.js', ['jquery'], $this->scripts_version, \true);
            wp_enqueue_script('fc-coupons-admin');
            wp_enqueue_script('wc-enhanced-select');
        }
        if (in_array($screen_id, ['product', 'edit-product'])) {
            wp_enqueue_script('fc-coupons-product-metaboxes', $this->get_assets_url() . 'js/metaboxes.js', ['jquery'], $this->scripts_version, \true);
        }
        if ('post' === $screen_base && $this->editor_post_type === $post_type) {
            wp_localize_script('wp-canva-admin', 'wpdesk_canva_editor_shortcodes', $this->shortcodes);
            $fonts = $this->get_editor_fonts();
            $this->enqueue_fonts_style($fonts);
            wp_localize_script('wp-canva-admin', 'wpdesk_canva_editor_fonts', $this->get_font_list($fonts));
        }
        $docs_url = $this->get_docs_url($screen_id);
        if ($docs_url) {
            wp_enqueue_script('fc-coupons-docs', $this->get_assets_url() . 'js/docs.js', ['jquery'], $this->scripts_version, \true);
            wp_localize_script('fc-coupons-docs', 'fc_coupons_docs', ['documentation_link' => sprintf(
                /* translators: %1$s: anchor opening tag, %2$s: anchor closing tag */
                esc_html__('Read the %1$splugin documentation →%2$s', 'flexible-coupons-pro'),
                '<a target="_blank" href="' . esc_url($docs_url) . '" class="docs-link">',
                '</a>'
            )]);
        }
    }
    private function get_docs_url(string $screen_id): string
    {
        switch ($screen_id) {
            case self::COUPON_LISTING_PAGE:
                $docs_url = 'https://wpdesk.net/docs/flexible-coupons-pro/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-docs-link&utm_content=edit-template-list#Coupon_templates_list';
                if (get_locale() === 'pl_PL') {
                    $docs_url = 'https://www.wpdesk.pl/docs/flexible-coupons-pro/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-docs-link&utm_content=edit-template-list#lista-szablonow-kuponow';
                }
                break;
            case self::COUPON_EDIT_PAGE:
                $docs_url = 'https://wpdesk.net/docs/flexible-coupons-pro/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-docs-link&utm_content=edit-template-add-new#New_coupon_template';
                if (get_locale() === 'pl_PL') {
                    $docs_url = 'https://www.wpdesk.pl/docs/flexible-coupons-pro/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-docs-link&utm_content=edit-template-add-new#tworzenie-szablonu-kuponu';
                }
                break;
            default:
                $docs_url = '';
        }
        return $docs_url;
    }
    /**
     * @param array $fonts
     */
    private function enqueue_fonts_style(array $fonts)
    {
        if (!empty($fonts)) {
            foreach ($fonts as $key => $font) {
                if ($font['url'] && wc_is_valid_url($font['url'])) {
                    wp_enqueue_style('canva-font-' . $key, $font['url'], \false);
                }
            }
        }
    }
    /**
     * Enqueue front scripts.
     */
    public function wp_enqueue_scripts()
    {
        global $post;
        if (isset($post->ID) && (is_product() || is_page())) {
            wp_enqueue_script('fc-front', $this->get_assets_url() . 'js/front.js', ['jquery'], $this->scripts_version, \true);
            wp_localize_script('fc-front', 'fc_front', ['security' => wp_create_nonce('fc-security-nonce'), 'ajax_url' => admin_url('admin-ajax.php'), 'product_id' => $post->ID]);
        }
    }
    /**
     * Define default fonts for visual editor.
     *
     * @return array
     */
    private function get_editor_fonts(): array
    {
        $fonts = ['lato' => ['name' => 'Lato', 'url' => \false], 'montserrat' => ['name' => 'Montserrat', 'url' => \false], 'open_sans' => ['name' => 'Open Sans', 'url' => \false], 'open_sans_condensed' => ['name' => 'Open Sans Condensed', 'url' => \false], 'nunito' => ['name' => 'Nunito', 'url' => \false], 'raleway' => ['name' => 'Raleway', 'url' => \false], 'roboto' => ['name' => 'Roboto', 'url' => \false], 'rubik' => ['name' => 'Rubik', 'url' => \false], 'quicksand' => ['name' => 'Quicksand', 'url' => \false], 'titillium_web' => ['name' => 'Titillium Web', 'url' => \false]];
        $fonts = apply_filters('fcpdf/core/editor/fonts', $fonts);
        if (!is_array($fonts)) {
            throw new RuntimeException('This is not an array of fonts.');
        }
        return $fonts;
    }
    /**
     * Get parsed font list for visual editor.
     *
     * @param array $fonts
     *
     * @return array
     */
    public function get_font_list(array $fonts): array
    {
        $fonts_data = [];
        if (!empty($fonts)) {
            foreach ($fonts as $font) {
                $fonts_data[] = $font['name'];
            }
        }
        return $fonts_data;
    }
}
