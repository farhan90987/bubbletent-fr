<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Tabs;

use FlexibleCouponsProVendor\WPDesk\Forms\Field;
use FlexibleCouponsProVendor\WPDesk\Forms\Field\Header;
use FlexibleCouponsProVendor\WPDesk\Forms\Field\Paragraph;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Fields\AddonField;
use FlexibleCouponsProVendor\WPDesk\View\Renderer\Renderer;
use FlexibleCouponsProVendor\WPDesk\Forms\Field\NoOnceField;
use FlexibleCouponsProVendor\WPDesk\Forms\Field\SelectField;
use FlexibleCouponsProVendor\WPDesk\Forms\Field\SubmitField;
use FlexibleCouponsProVendor\WPDesk\Forms\Field\InputTextField;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Helpers\Links;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\SettingsForm;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Fields\LinkField;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Fields\DisableFieldProAdapter;
/**
 * Main Settings Tab Page.
 *
 * @package WPDesk\Library\WPCoupons\Settings\Tabs
 */
final class MainSettings extends FieldSettingsTab
{
    private $renderer;
    /** @var string field names */
    const FIELD_AUTOMATIC_SENDING = 'automatic_sending';
    const EXPIRY_DATE_FORMAT_FIELD = 'expiry_date_format';
    const PRODUCT_PAGE_POSITION_FIELD = 'coupon_product_position';
    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }
    /**
     * @return array|Field[]
     */
    protected function get_fields(): array
    {
        $is_pl = 'pl_PL' === get_locale();
        $precise_docs = $is_pl ? '&utm_content=main-settings#ustawienia-glowne' : '&utm_content=main-settings#Settings';
        $fields = [(new Header())->set_label(esc_html__('Automatic Coupon Generation', 'flexible-coupons-pro'))->set_description(sprintf(
            /* translators: %1$s: anchor opening tag, %2$s: anchor closing tag */
            __('Read more in the %1$splugin documentation →%2$s', 'flexible-coupons-pro'),
            sprintf('<a href="%s" target="_blank" class="docs-link">', esc_url(Links::get_doc_link() . $precise_docs)),
            '</a><br/>'
        ))->add_class('marketing-content'), (new SelectField())->set_options($this->get_wc_order_statuses())->set_name(self::FIELD_AUTOMATIC_SENDING)->set_label(\esc_html__('Automatically generate coupons', 'flexible-coupons-pro'))->set_description(\esc_html__('If you want the coupon to be generated automatically, select order status. Coupon will be generated and sent automatically when order status is changed to selected status.', 'flexible-coupons-pro'))->set_required()->add_class('form-table-field'), (new Paragraph())->set_name('php-allow')->set_label(\esc_html__('Allow URL Fopen', 'flexible-coupons-pro'))->set_description($this->get_php_settings_message()), (new Header())->set_label(esc_html__('Display Formating', 'flexible-coupons-pro')), (new DisableFieldProAdapter('', (new AddonField())->set_link(Links::get_pro_link())->set_is_addon(\false)->set_label('Upgrade to PRO')->set_description(__('Upgrade to PRO and enable options below', 'flexible-coupons-pro'))))->get_field(), (new DisableFieldProAdapter(self::EXPIRY_DATE_FORMAT_FIELD, (new InputTextField())->set_name('')->set_label(\esc_html__('Expiry date format', 'flexible-coupons-pro'))->set_description(sprintf(__('Define coupon expiry date format according to %1$sWordPress date formatting%2$s.', 'flexible-coupons-pro'), '<a href="https://wordpress.org/support/article/formatting-date-and-time/" target="_blank">', '</a>'))->set_default_value(get_option('date_format'))->add_class('form-table-field')))->get_field(), (new DisableFieldProAdapter(self::PRODUCT_PAGE_POSITION_FIELD, (new SelectField())->set_options(['below' => \esc_html__('Below Add to cart button', 'flexible-coupons-pro'), 'above' => \esc_html__('Above Add to cart button', 'flexible-coupons-pro')])->set_name('')->set_label(\esc_html__('Coupon fields position on the product page', 'flexible-coupons-pro'))->set_description(\esc_html__('Select where the coupon fields will be displayed on the product page.', 'flexible-coupons-pro'))->add_class('form-table-field')))->get_field(), (new NoOnceField(SettingsForm::NONCE_ACTION))->set_name(SettingsForm::NONCE_NAME), (new SubmitField())->set_name('save_settings')->set_label(\esc_html__('Save Changes', 'flexible-coupons-pro'))->add_class('button-primary')->set_attribute('id', 'save_settings')];
        return apply_filters('fcpdf/settings/general/fields', $fields, $this->get_tab_slug());
    }
    private function get_wc_order_statuses(): array
    {
        $statuses = wc_get_order_statuses();
        unset($statuses['wc-cancelled'], $statuses['wc-refunded'], $statuses['wc-failed'], $statuses['wc-checkout-draft']);
        return array_merge([\esc_html__('Do not generate', 'flexible-coupons-pro')], $statuses);
    }
    private function is_allow_url_fopen_active(): bool
    {
        return (bool) \ini_get('allow_url_fopen');
    }
    private function get_php_settings_message(): string
    {
        $is_active = $this->is_allow_url_fopen_active();
        return $this->renderer->render('allow-url-fopen-status', ['status' => $is_active ? \__('Enabled', 'flexible-coupons-pro') : \__('Disabled', 'flexible-coupons-pro'), 'color' => $is_active ? 'green' : 'red']);
    }
    /**
     * @return string
     */
    public static function get_tab_slug(): string
    {
        return 'general';
    }
    /**
     * @return string
     */
    public function get_tab_name(): string
    {
        return \esc_html__('Main settings', 'flexible-coupons-pro');
    }
}
