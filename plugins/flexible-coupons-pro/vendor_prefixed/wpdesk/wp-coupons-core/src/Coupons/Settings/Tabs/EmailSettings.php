<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Tabs;

use FlexibleCouponsProVendor\WPDesk\Forms\Field;
use FlexibleCouponsProVendor\WPDesk\Forms\Field\Header;
use FlexibleCouponsProVendor\WPDesk\Forms\Field\NoOnceField;
use FlexibleCouponsProVendor\WPDesk\Forms\Field\SubmitField;
use FlexibleCouponsProVendor\WPDesk\Forms\Field\ToggleField;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Helpers\Links;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Fields\AddonField;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\SettingsForm;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Fields\LinkField;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Fields\DisableFieldProAdapter;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Fields\DisableFieldSendingAddonAdapter;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Fields\CouponEmailTemplatesList;
/**
 * Main Settings Tab Page.
 *
 * @package WPDesk\Library\WPCoupons\Settings\Tabs
 */
class EmailSettings extends FieldSettingsTab
{
    /** @var string field names */
    const ATTACH_COUPON_FIELD = 'attach_coupon';
    /**
     * @return array|Field[]
     */
    protected function get_fields(): array
    {
        $is_pl = 'pl_PL' === get_locale();
        $precise_docs = $is_pl ? '&utm_content=emails-settings#emaile' : '&utm_content=emails-settings#Emails';
        $fields = [(new Header())->set_name('')->set_label(\esc_html__('Email Settings', 'flexible-coupons-pro'))->set_description(sprintf(
            /* translators: %1$s: first sentence, %2$s: anchor opening tag, %3$s: anchor closing tag */
            '<p>%1$s</p><p>' . __('Read more in the %2$splugin documentation →%3$s', 'flexible-coupons-pro') . '</p>',
            __('For more specific email delay settings, visit the product edit page.', 'flexible-coupons-pro'),
            sprintf('<a href="%s" target="_blank" class="docs-link">', esc_url(Links::get_doc_link() . $precise_docs)),
            '</a>'
        ))->add_class('marketing-content'), (new DisableFieldProAdapter('', (new AddonField())->set_link(Links::get_pro_link())->set_is_addon(\false)->set_label(esc_html__('Upgrade to PRO', 'flexible-coupons-pro'))->set_description(__('Upgrade to PRO and enable options below', 'flexible-coupons-pro'))->add_class('email-pill')))->get_field(), (new DisableFieldProAdapter(self::ATTACH_COUPON_FIELD, (new ToggleField())->set_sublabel(\esc_html__('Attach PDF file to coupon email', 'flexible-coupons-pro'))->set_name('')->set_label(\esc_html__('Attachments in the e-mail', 'flexible-coupons-pro'))))->get_field(), (new NoOnceField(SettingsForm::NONCE_ACTION))->set_name(SettingsForm::NONCE_NAME), (new SubmitField())->set_name('save_settings')->set_label(\esc_html__('Save Changes', 'flexible-coupons-pro'))->add_class('button-primary')->set_attribute('id', 'save_settings'), (new Header())->set_label(esc_html__('Email Templates', 'flexible-coupons-pro')), (new DisableFieldSendingAddonAdapter('', (new AddonField())->set_link(Links::get_fcs_link())->set_is_addon(\true)->set_label(esc_html__('Advanced Sending', 'flexible-coupons-pro'))->add_class('sending-pill')->set_description(__('Buy Flexible PDF Coupons PRO - Advanced Sending and enable options below', 'flexible-coupons-pro'))))->get_field(), (new DisableFieldProAdapter('', (new AddonField())->set_link(Links::get_bundle_link())->set_is_addon(\false)->set_label(esc_html__('Add-on Bundle', 'flexible-coupons-pro'))->add_class('sending-pill')->set_description(__('Get Flexible Coupons PRO with add-ons in one Bundle and enable options below', 'flexible-coupons-pro'))))->get_field(), (new DisableFieldSendingAddonAdapter('', (new CouponEmailTemplatesList())->set_name('')->set_label(esc_html__('Email templates', 'flexible-coupons-pro'))))->get_field()];
        return \apply_filters('fcpdf/settings/general/fields', $fields, $this->get_tab_slug());
    }
    /**
     * @return string
     */
    public static function get_tab_slug(): string
    {
        return 'emails';
    }
    /**
     * @return string
     */
    public function get_tab_name(): string
    {
        return __('Email', 'flexible-coupons-pro');
    }
}
