<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Tabs;

use FlexibleCouponsProVendor\WPDesk\Forms\Field;
use FlexibleCouponsProVendor\WPDesk\Forms\Field\Header;
use FlexibleCouponsProVendor\WPDesk\Forms\Field\NoOnceField;
use FlexibleCouponsProVendor\WPDesk\Forms\Field\SubmitField;
use FlexibleCouponsProVendor\WPDesk\Forms\Field\ToggleField;
use FlexibleCouponsProVendor\WPDesk\Forms\Field\InputTextField;
use FlexibleCouponsProVendor\WPDesk\Forms\Field\InputNumberField;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Helpers\Links;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Fields\AddonField;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\SettingsForm;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Fields\LinkField;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Fields\DisableFieldProAdapter;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Fields\CouponCodeList;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Fields\DisableFieldImportAddonAdapter;
/**
 * Main Settings Tab Page.
 *
 * @package WPDesk\Library\WPCoupons\Settings\Tabs
 */
final class CouponSettings extends FieldSettingsTab
{
    /** @var string field names */
    const COUPON_CODE_PREFIX_FIELD = 'coupon_code_prefix';
    const COUPON_CODE_SUFFIX_FIELD = 'coupon_code_suffix';
    const REGULAR_PRICE_FIELD = 'coupon_regular_price';
    const SHOW_TIPS_FIELD = 'coupon_tips';
    const SHOW_TEXTAREA_COUNTER_FIELD = 'coupon_textarea_counter';
    const COUPON_CODE_LENGTH_FIELD = 'coupon_code_random_length';
    const COUPON_CODE_LIST_FIELD = 'coupon_code_list_enabled';
    /**
     * @return array|Field[]
     */
    protected function get_fields(): array
    {
        $is_pl = 'pl_PL' === get_locale();
        $precise_docs = $is_pl ? '&utm_content=coupon-settings#kupon' : '&utm_content=coupon-settings#Coupon';
        $fields = [(new Header())->set_label(esc_html__('Coupon Code Format', 'flexible-coupons-pro'))->set_description(sprintf(
            /* translators: %1$s: anchor opening tag, %2$s: anchor closing tag */
            __('Read more in the %1$splugin documentation →%2$s', 'flexible-coupons-pro'),
            sprintf('<a href="%s" target="_blank" class="docs-link">', esc_url(Links::get_doc_link() . $precise_docs)),
            '</a><br/>'
        ))->add_class('marketing-content'), (new DisableFieldProAdapter('', (new AddonField())->set_link(Links::get_pro_link())->set_is_addon(\false)->set_label(esc_html__('Upgrade to PRO', 'flexible-coupons-pro'))->add_class('cupon-code-pill')->set_description(__('Upgrade to PRO and enable options below', 'flexible-coupons-pro'))))->get_field(), (new DisableFieldProAdapter(self::COUPON_CODE_PREFIX_FIELD, (new InputTextField())->set_name('')->set_label(esc_html__('Coupon code prefix', 'flexible-coupons-pro'))->set_description(__('Define the prefix which will be used as a beginning of your coupon code. Leave empty if you don’t want to use the prefix. Use <code>{order_id}</code> shortcode if you want to use the order number.', 'flexible-coupons-pro'))->add_class('form-table-field')))->get_field(), (new DisableFieldProAdapter(self::COUPON_CODE_SUFFIX_FIELD, (new InputTextField())->set_name('')->set_label(esc_html__('Coupon code suffix', 'flexible-coupons-pro'))->set_description(__('Define the suffix which will be used as a end of your coupon code. Leave empty if you don’t want to use the suffix. Use <code>{order_id}</code> shortcode if you want to use the order number.', 'flexible-coupons-pro'))->add_class('form-table-field')))->get_field(), (new DisableFieldProAdapter(self::COUPON_CODE_LENGTH_FIELD, (new InputNumberField())->set_name('')->set_label(esc_html__('Number of random characters', 'flexible-coupons-pro'))->add_class('form-table-field')->set_description(esc_html__('The number of random characters in the coupon code. Random characters will be used for generating unique coupon codes. Choose the number between 5 and 30.', 'flexible-coupons-pro'))->set_default_value('5')->set_attribute('min', '5')))->get_field(), (new Header())->set_label(esc_html__('UI Settings', 'flexible-coupons-pro')), (new DisableFieldProAdapter(self::REGULAR_PRICE_FIELD, (new ToggleField())->set_sublabel(esc_html__('Enable', 'flexible-coupons-pro'))->set_name('')->set_label(esc_html__('Coupon value', 'flexible-coupons-pro'))->set_description(esc_html__('Always use the regular price of the product for the coupon value.', 'flexible-coupons-pro'))))->get_field(), (new DisableFieldProAdapter(self::SHOW_TIPS_FIELD, (new ToggleField())->set_sublabel(esc_html__('Enable', 'flexible-coupons-pro'))->set_name('')->set_label(esc_html__('Show field tips', 'flexible-coupons-pro'))->set_description(esc_html__('Show tooltips for fields.', 'flexible-coupons-pro'))))->get_field(), (new DisableFieldProAdapter(self::SHOW_TEXTAREA_COUNTER_FIELD, (new ToggleField())->set_sublabel(esc_html__('Enable', 'flexible-coupons-pro'))->set_name('')->set_label(esc_html__('Show textarea counter', 'flexible-coupons-pro'))->set_description(esc_html__('Show character counter below textarea.', 'flexible-coupons-pro'))))->get_field(), (new Header())->set_label(esc_html__('Codes Import', 'flexible-coupons-pro')), (new DisableFieldImportAddonAdapter('', (new AddonField())->set_link(Links::get_fcci_buy_link())->set_is_addon(\true)->set_label(esc_html__('Coupon Codes Import', 'flexible-coupons-pro'))->set_description(__('Buy Flexible PDF Coupons PRO - Coupon Codes Import and enable options below', 'flexible-coupons-pro'))))->get_field(), (new DisableFieldProAdapter('', (new AddonField())->set_link(Links::get_bundle_link())->set_is_addon(\false)->set_label(esc_html__('Add-on Bundle', 'flexible-coupons-pro'))->set_description(__('Get Flexible Coupons PRO with add-ons in one Bundle and enable options below', 'flexible-coupons-pro'))))->get_field(), (new CouponCodeList())->set_name('')->set_label(esc_html__('Predefined coupon codes', 'flexible-coupons-pro')), (new NoOnceField(SettingsForm::NONCE_ACTION))->set_name(SettingsForm::NONCE_NAME), (new SubmitField())->set_name('save_settings')->set_label(esc_html__('Save Changes', 'flexible-coupons-pro'))->add_class('button-primary')->set_attribute('id', 'save_settings')];
        return \apply_filters('fcpdf/settings/general/fields', $fields, $this->get_tab_slug());
    }
    /**
     * @return string
     */
    public static function get_tab_slug(): string
    {
        return 'coupon';
    }
    /**
     * @return string
     */
    public function get_tab_name(): string
    {
        return \esc_html__('Coupon', 'flexible-coupons-pro');
    }
}
