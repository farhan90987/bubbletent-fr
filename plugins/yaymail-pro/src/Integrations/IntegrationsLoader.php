<?php

namespace YayMail\Integrations;

use YayMail\Utils\SingletonTrait;
use YayMail\Integrations\TranslationModule;
use YayMail\Integrations\AdvancedLocalPickupByZorem\AdvancedLocalPickup;
use YayMail\Integrations\AdvancedShipmentTrackingByZorem\AdvancedShipmentTracking;
use YayMail\Integrations\TrackingMoreOrderTrackingForWc\TrackingMoreOrderTrackingForWc;
use YayMail\Integrations\CustomOrderStatusForWcByNuggethon\CustomOrderStatusForWcByNuggethon;
use YayMail\Integrations\WooCommerceOrderStatusManagerBySkyVer\WooCommerceOrderStatusManagerBySkyVer;
use YayMail\Integrations\CustomOrderStatusManagerByBrightPlugins\CustomOrderStatusManagerByBrightPlugins;
use YayMail\Integrations\WoocommerceShipmentTrackingProByPluginHive\WoocommerceShipmentTrackingProByPluginHive;
use YayMail\Integrations\YITHWooCommerceOrderShipmentTracking\YITHWooCommerceOrderShipmentTracking;
use YayMail\Integrations\WcAdminCustomOrderFieldBySkyverge\WcAdminCustomOrderFieldBySkyverge;
use YayMail\Integrations\BackInStockNotifier\BackInStockNotifier;
use YayMail\Integrations\F4ShippingPhoneAndEmailForWooCommerce\F4ShippingPhoneAndEmailForWooCommerce;
use YayMail\Integrations\WooCommerceShippingTax\WooCommerceShippingTax;
use YayMail\Integrations\WooCommerceSoftwareAddon\WooCommerceSoftwareAddon;
use YayMail\Integrations\FooEventsforWooCommerce\FooEventsforWooCommerce;
use YayMail\Integrations\MakeCommerce\MakeCommerce;
use YayMail\Integrations\WooCommerceShipmentTracking\WooCommerceShipmentTracking;
use YayMail\Integrations\WooCommerceOrderStatusByTycheSoftwares\WooCommerceOrderStatusByTycheSoftwares;
use YayMail\Integrations\WooCommerceShipping\WooCommerceShipping;

/**
 * IntegrationsLoader
 * * @method static IntegrationsLoader get_instance()
 */
class IntegrationsLoader {
    use SingletonTrait;

    protected function __construct() {
        AdvancedShipmentTracking::get_instance();
        TrackingMoreOrderTrackingForWc::get_instance();
        WoocommerceShipmentTrackingProByPluginHive::get_instance();
        TranslationModule::get_instance();
        WcAdminCustomOrderFieldBySkyverge::get_instance();
        YITHWooCommerceOrderShipmentTracking::get_instance();
        CustomOrderStatusForWcByNuggethon::get_instance();
        WooCommerceOrderStatusManagerBySkyVer::get_instance();
        CustomOrderStatusManagerByBrightPlugins::get_instance();
        AdvancedLocalPickup::get_instance();
        BackInStockNotifier::get_instance();
        WooCommerceShippingTax::get_instance();
        WooCommerceSoftwareAddon::get_instance();
        FooEventsforWooCommerce::get_instance();
        WooCommerceShipmentTracking::get_instance();
        WooCommerceOrderStatusByTycheSoftwares::get_instance();
        MakeCommerce::get_instance();
        WooCommerceShipping::get_instance();
        RankMath::get_instance();
        F4ShippingPhoneAndEmailForWooCommerce::get_instance();
    }
}
