<?php

namespace YayMail\Migrations\Versions;

use Exception;
use YayMail\Elements\ColumnLayout;
use YayMail\Migrations\AbstractMigration;
use YayMail\Utils\Helpers;
use YayMail\Utils\SingletonTrait;
use YayMail\Integrations\AdvancedShipmentTrackingByZorem\AdvancedShipmentTracking;
use YayMail\Integrations\TrackingMoreOrderTrackingForWc\TrackingMoreOrderTrackingForWc;
use YayMail\Integrations\WcAdminCustomOrderFieldBySkyverge\WcAdminCustomOrderFieldBySkyverge;
use YayMail\Integrations\WoocommerceShipmentTrackingProByPluginHive\WoocommerceShipmentTrackingProByPluginHive;
use YayMail\Integrations\WoocommerceShipmentTrackingProByPluginHive\Elements\TrackingInformationElement;
use YayMail\Integrations\YITHWooCommerceOrderShipmentTracking\YITHWooCommerceOrderShipmentTracking;
use YayMail\Integrations\YITHWooCommerceOrderShipmentTracking\Elements\YITHTrackingInformationElement;
use YayMail\Integrations\WooCommerceShippingTax\WooCommerceShippingTax;
use YayMail\Integrations\WooCommerceShippingTax\Elements\ShipmentTrackingElement;
use YayMail\Integrations\WooCommerceSoftwareAddon\WooCommerceSoftwareAddon;
use YayMail\Integrations\WooCommerceSoftwareAddon\Elements\SoftwareLicenseElement;
use YayMail\Integrations\AdvancedLocalPickupByZorem\AdvancedLocalPickup;
use YayMail\Integrations\AdvancedLocalPickupByZorem\Elements\AdvancedLocalPickupInstructionElement;
use YayMail\Integrations\WooCommerceShipmentTracking\WooCommerceShipmentTracking;
use YayMail\Models\SettingModel;
use YayMail\YayMailTemplate;

/**
 * Script to migrate from YayMail legacy (pre 4.0.0) to 4.0.0
 */
final class Ver_4_0_0 extends AbstractMigration {

    use SingletonTrait;

    private $current_template_id;
    private $element_types_map = [];

    private function __construct() {
        parent::__construct( '3.9.9', '4.0.0' );
    }

    protected function up() {
        $this->migrate_templates();
        $this->migrate_yaymail_settings();
    }

    /**
     * Private functions
     */
    private function migrate_templates() {
        $this->logger->log( 'Start migrating templates' );
        global $wpdb;

        // Make sure the backup existed
        if ( empty( $this->backup_option_name ) || empty( get_option( $this->backup_option_name ) ) ) {
            throw new Exception( 'Could not find backup option' );
        }

        $template_posts_query = "
            SELECT * 
            FROM {$wpdb->posts}
            WHERE post_type = 'yaymail_template'
        ";
        $template_posts       = $wpdb->get_results( $template_posts_query ); // phpcs:ignore
        if ( empty( $template_posts ) ) {
            $this->logger->log( 'There is no template to be migrated' );
            return;
        }

        foreach ( $template_posts as $template ) {
            if ( empty( $template->ID ) ) {
                continue;
            }
            /**
             * ==========================
             * Start Elements migrations
             */
            $this->current_template_id = $template->ID;

            $elements = get_post_meta( $this->current_template_id, \YayMail\YayMailTemplate::META_KEYS['elements'], true );

            if ( empty( $elements ) ) {
                continue;
            }

            $elements = array_map( [ $this, 'convert_element' ], $elements );

            update_post_meta( $this->current_template_id, \YayMail\YayMailTemplate::META_KEYS['elements'], $elements );
            /**
             * ==========================
             */

            /**
             * ==========================
             * Start Template settings migrations
             */
            // Template status
            $old_activation_status = get_post_meta( $this->current_template_id, \YayMail\YayMailTemplate::META_KEYS['status'], true );
            $new_activation_status = in_array( $old_activation_status, [ 'active', 'inactive' ], true ) ? $old_activation_status : ( '1' === $old_activation_status ? 'active' : 'inactive' );
            update_post_meta( $this->current_template_id, \YayMail\YayMailTemplate::META_KEYS['status'], $new_activation_status );

            // Template outer background color
            $old_outer_bg_color = get_post_meta( $this->current_template_id, '_email_backgroundColor_settings', true );
            update_post_meta( $this->current_template_id, \YayMail\YayMailTemplate::META_KEYS['background_color'], $old_outer_bg_color );

            // Template default language
            // On legacy core, when there is no multi-languages plugin installed, the default template does not have any language code
            // On new core, the default language is en_US
            // Check if there is a en_US template
            $language = get_post_meta( $this->current_template_id, \YayMail\YayMailTemplate::META_KEYS['language'], true );
            if ( empty( $language ) ) {
                // If not, set it as English US
                update_post_meta( $this->current_template_id, \YayMail\YayMailTemplate::META_KEYS['language'], 'en_US' );
            }

            $this->may_mark_template_as_v4_supported( $this->current_template_id );

            /**
             * Finish Template settings migrations
             * ==========================
             */

        }//end foreach
        $this->logger->log( 'Done migrating templates' );
    }

    private function migrate_yaymail_settings() {
        $this->logger->log( 'Start migrating YayMail settings' );
        $yaymail_settings = yaymail_settings();

        /**
         * ==============================
         * Start mapping attribute names
         */
        $attributes_map = [
            'payment'               => SettingModel::META_KEYS['payment_display_mode'],
            'product_image'         => SettingModel::META_KEYS['show_product_image'],
            'image_position'        => SettingModel::META_KEYS['product_image_position'],
            'image_width'           => SettingModel::META_KEYS['product_image_width'],
            'image_height'          => SettingModel::META_KEYS['product_image_height'],
            'product_sku'           => SettingModel::META_KEYS['show_product_sku'],
            'product_des'           => SettingModel::META_KEYS['show_product_description'],
            'product_regular_price' => SettingModel::META_KEYS['show_product_regular_price'],
            'product_hyper_links'   => SettingModel::META_KEYS['show_product_hyper_links'],
            'product_item_cost'     => SettingModel::META_KEYS['show_product_item_cost'],
            'direction_rtl'         => SettingModel::META_KEYS['direction'],
            'enable_css_custom'     => SettingModel::META_KEYS['enable_custom_css'],
        ];
        // Convert element data names
        foreach ( $attributes_map as $old_key => $new_key ) {
            $value = Helpers::get_object_value( $yaymail_settings, $old_key );

            if ( ! isset( $value ) ) {
                continue;
            }

            if ( in_array( $value, [ '0', 'no' ], true ) ) {
                $value = false;
            }

            if ( in_array( $value, [ '1', 'yes' ], true ) ) {
                $value = true;
            }

            Helpers::set_object_value( $yaymail_settings, $new_key, $value );
        }//end foreach

        /**
         * End mapping attribute names
         * ============================
         */

        /**
         * ==============================
         * Start mapping settings values
         */
        $payment_display_mode_map                 = [
            '0' => 'no',
            '1' => 'yes',
            '2' => 'customer',
        ];
        $yaymail_settings[SettingModel::META_KEYS['payment_display_mode']] = $payment_display_mode_map[ $yaymail_settings[SettingModel::META_KEYS['payment_display_mode']] ] ?? 'yes';

        $yaymail_settings[SettingModel::META_KEYS['product_image_position']] = strtolower( $yaymail_settings[SettingModel::META_KEYS['product_image_position']] ?? 'top' );
        $yaymail_settings[SettingModel::META_KEYS['product_image_width']]    = (int) str_replace( 'px', '', $yaymail_settings[SettingModel::META_KEYS['product_image_width']] ?? '30' );
        $yaymail_settings[SettingModel::META_KEYS['product_image_height']]   = (int) str_replace( 'px', '', $yaymail_settings[SettingModel::META_KEYS['product_image_height']] ?? '30' );
        $yaymail_settings[SettingModel::META_KEYS['container_width']]        = (int) str_replace( 'px', '', $yaymail_settings[SettingModel::META_KEYS['container_width']] ?? '605' );
        if ( empty( $yaymail_settings[SettingModel::META_KEYS['container_width']] ) ) {
            $yaymail_settings[SettingModel::META_KEYS['container_width']] = 605;
        }
        /**
         * Finish mapping settings values
         * ==============================
         */

        // Update yaymail settings to db
        $update_yaymail_setting_options_result = update_option( SettingModel::OPTION_NAME, $yaymail_settings );
        if ( ! $update_yaymail_setting_options_result ) {
            $this->logger->log( 'Failed to update new YayMail settings to db' );
        }
        $this->logger->log( 'Done migrating YayMail settings' );
    }

    private function convert_element( $element ) {
        $attributes_map = [
            'nameElement' => 'name',
            'settingRow'  => 'data',
        ];

        foreach ( $attributes_map as $old_key => $new_key ) {
            if ( isset( $element[ $old_key ] ) ) {
                $element[ $new_key ] = $element[ $old_key ];
            }
        }

        $this->convert_element_type( $element );

        $this->convert_element_data( $element );

        $element['available'] = true;

        return $element;
    }

    private function convert_element_type( &$element ) {
        $attributes_map =
            [
                'Logo'              => 'logo',
                'ElementText'       => 'text',
                'Images'            => 'image',
                'Button'            => 'button',
                'Title'             => 'title',
                'SocialIcon'        => 'social_icon',
                'Video'             => 'video',
                'ImageList'         => 'image_list',
                'ImageBox'          => 'image_box',
                'TextList'          => 'text_list',
                'HTMLCode'          => 'html',
                // Note: Old version uses ElementText for Heading and Footer

                'Space'             => 'space',
                'Divider'           => 'divider',
                'OneColumn'         => 'column_layout',
                'TwoColumns'        => 'column_layout',
                'ThreeColumns'      => 'column_layout',
                'FourColumns'       => 'column_layout',

                'ShippingAddress'   => 'shipping_address',
                'BillingAddress'    => 'billing_address',
                'OrderItem'         => 'order_details',
                'Hook'              => 'hook',
                'OrderItemDownload' => 'order_details_download',

                'FeaturedProducts'  => 'featured_products',
                'SimpleOffer'       => 'simple_offer',
                'SingleBanner'      => 'single_banner',

            ];
        // Integrated
        if ( AdvancedShipmentTracking::is_3rd_party_installed() ) {
            $attributes_map['TrackingItem'] = 'tracking_information_by_zorem';
        } elseif ( TrackingMoreOrderTrackingForWc::is_3rd_party_installed() ) {
            $attributes_map['TrackingItem'] = 'tracking_information_by_trackingmore';
        } elseif ( WoocommerceShipmentTrackingProByPluginHive::is_3rd_party_installed() ) {
            $attributes_map['TrackingItem'] = 'tracking_information_by_pluginhive';
        } elseif ( WooCommerceShipmentTracking::is_3rd_party_installed() ) {
            $attributes_map['TrackingItem'] = 'woocommerce_shipment_tracking';
        }
        if ( WcAdminCustomOrderFieldBySkyverge::is_3rd_party_installed() ) {
            $attributes_map['AdditionalOrderDetails'] = 'wc_admin_custom_order_fields_by_skyverge';
        }
        if ( YITHWooCommerceOrderShipmentTracking::is_3rd_party_installed() ) {
            $attributes_map['TrackingDetails'] = 'yith_tracking_information';
        }
        if ( AdvancedLocalPickup::is_3rd_party_installed() ) {
            $attributes_map['AdvancedLocalPickupInstruction'] = 'advanced_local_pickup_instruction';
        }
        if ( WooCommerceShippingTax::is_3rd_party_installed() ) {
            $attributes_map['ShippingTaxShipmentTracking'] = 'wc_shipping_tax_shipment_tracking';
        }
        if ( WooCommerceSoftwareAddon::is_3rd_party_installed() ) {
            $attributes_map['SoftwareAddOn'] = 'wc_software_addon_license_info';
        }
        $type = $element['type'];

        if ( $type === 'BillingAddress' ) {
            // Note: Old version uses BillingAddress for BillingShippingAddress
            if ( isset( $element['settingRow']['nameColumn'] ) && $element['settingRow']['nameColumn'] === 'BillingShippingAddress' ) {
                $element['type'] = 'billing_shipping_address';
                return;
            }
        }

        if ( empty( $attributes_map[ $type ] ) ) {
            $this->logger->log( 'Element type was not handled: ' . $type );
        }

        if ( isset( $attributes_map[ $type ] ) ) {
            $element['type'] = $attributes_map[ $type ];
        }
    }

    private function convert_element_data( &$element ) {
        $data =& $element['data'];
        if ( empty( $data ) ) {
            return;
        }

        $this->convert_element_data_attribute_names( $data, $element['type'] );
        $this->convert_element_data_attribute_values( $element );

        $this->add_new_attributes_to_element_data( $element );

        $this->migrate_shortcodes( $data, $element['type'] );
    }

    private function convert_element_data_attribute_names( &$data, $element_type ) {
        $attributes_map = $this->get_element_data_attributes_map( $element_type );

        // Convert element data names
        foreach ( $attributes_map as $old_key => $new_key ) {
            $value = Helpers::get_object_value( $data, $old_key );

            if ( ! isset( $value ) ) {
                continue;
            }

            Helpers::set_object_value( $data, $new_key, $value );
        }
    }

    private function get_element_data_attributes_map( $element_type ) {
        $common_map = [
            'backgroundColor'    => 'background_color',
            'borderColor'        => 'border_color',
            'borderRadiusBottom' => [ 'border_radius', 'bottom_right' ],
            'borderRadiusLeft'   => [ 'border_radius', 'bottom_left' ],
            'borderRadiusRight'  => [ 'border_radius', 'top_right' ],
            'borderRadiusTop'    => [ 'border_radius', 'top_left' ],
            'content'            => 'rich_text',
            'contentTitle'       => 'title',
            'titleColor'         => 'title_color',
            'family'             => 'font_family',
            'paddingBottom'      => [ 'padding', 'bottom' ],
            'paddingLeft'        => [ 'padding', 'left' ],
            'paddingRight'       => [ 'padding', 'right' ],
            'paddingTop'         => [ 'padding', 'top' ],
            'pathImg'            => 'src',
            'textColor'          => 'text_color',
            'fontSize'           => 'font_size',
        ];

        $element_specific_map = [];
        switch ( $element_type ) {
            case 'button':
                $element_specific_map = [
                    'buttonBackgroundColor' => 'button_background_color',
                    'buttonType'            => 'button_type',
                    'styleTheme'            => 'theme',
                    'heightButton'          => 'height',
                    'widthButton'           => 'width',
                    'pathUrl'               => 'url',
                ];
                break;
            case 'video':
                $element_specific_map = [
                    'videoUrl' => 'url',
                ];
                break;
            case 'title':
                $element_specific_map = [
                    'sizeSubTitle'  => 'subtitle_size',
                    'sizeTitle'     => 'title_size',
                    'subTitle'      => 'subtitle',
                    'titleBilling'  => 'billing_title',
                    'titleColor'    => 'title_color',
                    'titleShipping' => 'shipping_title',
                ];
                break;
            case 'social_icon':
                $element_specific_map = [
                    'styleTheme'      => 'theme',
                    'iconSocialsArr'  => 'icon_list',
                    'iconSpacing'     => 'spacing',
                    'widthSocialIcon' => 'width_icon',
                ];
                break;
            case 'image_list':
                $element_specific_map = [
                    'col1Align'         => [ 'image_list', 'column_1', 'align', 'value' ],
                    'col1PaddingBottom' => [ 'image_list', 'column_1', 'padding', 'value', 'bottom' ],
                    'col1PaddingLeft'   => [ 'image_list', 'column_1', 'padding', 'value', 'left' ],
                    'col1PaddingRight'  => [ 'image_list', 'column_1', 'padding', 'value', 'right' ],
                    'col1PaddingTop'    => [ 'image_list', 'column_1', 'padding', 'value', 'top' ],
                    'col1PathImg'       => [ 'image_list', 'column_1', 'image', 'value' ],
                    'col1Url'           => [ 'image_list', 'column_1', 'url', 'value' ],
                    'col1Width'         => [ 'image_list', 'column_1', 'width', 'value' ],
                    'col2Align'         => [ 'image_list', 'column_2', 'align', 'value' ],
                    'col2PaddingBottom' => [ 'image_list', 'column_2', 'padding', 'value', 'bottom' ],
                    'col2PaddingLeft'   => [ 'image_list', 'column_2', 'padding', 'value', 'left' ],
                    'col2PaddingRight'  => [ 'image_list', 'column_2', 'padding', 'value', 'right' ],
                    'col2PaddingTop'    => [ 'image_list', 'column_2', 'padding', 'value', 'top' ],
                    'col2PathImg'       => [ 'image_list', 'column_2', 'image', 'value' ],
                    'col2Url'           => [ 'image_list', 'column_2', 'url', 'value' ],
                    'col2Width'         => [ 'image_list', 'column_2', 'width', 'value' ],
                    'col3Align'         => [ 'image_list', 'column_3', 'align', 'value' ],
                    'col3PaddingBottom' => [ 'image_list', 'column_3', 'padding', 'value', 'bottom' ],
                    'col3PaddingLeft'   => [ 'image_list', 'column_3', 'padding', 'value', 'left' ],
                    'col3PaddingRight'  => [ 'image_list', 'column_3', 'padding', 'value', 'right' ],
                    'col3PaddingTop'    => [ 'image_list', 'column_3', 'padding', 'value', 'top' ],
                    'col3PathImg'       => [ 'image_list', 'column_3', 'image', 'value' ],
                    'col3Url'           => [ 'image_list', 'column_3', 'url', 'value' ],
                    'col3Width'         => [ 'image_list', 'column_3', 'width', 'value' ],
                    'numberCol'         => 'number_column',
                ];
                break;
            case 'image_box':
                $element_specific_map = [
                    'col1Align'         => [ 'image_box', 'column_1', 'align', 'value' ],
                    'col1PaddingBottom' => [ 'image_box', 'column_1', 'padding', 'value', 'bottom' ],
                    'col1PaddingLeft'   => [ 'image_box', 'column_1', 'padding', 'value', 'left' ],
                    'col1PaddingRight'  => [ 'image_box', 'column_1', 'padding', 'value', 'right' ],
                    'col1PaddingTop'    => [ 'image_box', 'column_1', 'padding', 'value', 'top' ],
                    'col1PathImg'       => [ 'image_box', 'column_1', 'image', 'value' ],
                    'col1Url'           => [ 'image_box', 'column_1', 'url', 'value' ],
                    'col1Width'         => [ 'image_box', 'column_1', 'width', 'value' ],
                    'col2Content'       => [ 'image_box', 'column_2', 'rich_text', 'value' ],
                    'col2Family'        => [ 'image_box', 'column_2', 'font_family', 'value' ],
                    'col2PaddingBottom' => [ 'image_box', 'column_2', 'padding', 'value', 'bottom' ],
                    'col2PaddingLeft'   => [ 'image_box', 'column_2', 'padding', 'value', 'left' ],
                    'col2PaddingRight'  => [ 'image_box', 'column_2', 'padding', 'value', 'right' ],
                    'col2PaddingTop'    => [ 'image_box', 'column_2', 'padding', 'value', 'top' ],
                    'numberCol'         => 'number_column',

                ];
                break;
            case 'text_list':
                $element_specific_map = [
                    'numberCol'           => 'number_column',
                    'buttonCol1'          => [ 'text_list', 'column_1', 'show_button', 'value' ],
                    'buttonCol2'          => [ 'text_list', 'column_2', 'show_button', 'value' ],
                    'buttonCol3'          => [ 'text_list', 'column_3', 'show_button', 'value' ],

                    'col1BtAlign'         => [ 'text_list', 'column_1', 'button_align', 'value' ],
                    'col1BtBgColor'       => [ 'text_list', 'column_1', 'button_background_color', 'value' ],
                    'col1BtButtonType'    => [ 'text_list', 'column_1', 'button_type', 'value' ],
                    'col1BtFamily'        => [ 'text_list', 'column_1', 'button_font_family', 'value' ],
                    'col1BtFontSize'      => [ 'text_list', 'column_1', 'button_font_size', 'value' ],
                    'col1BtPaddingBottom' => [ 'text_list', 'column_1', 'button_padding', 'value', 'bottom' ],
                    'col1BtPaddingLeft'   => [ 'text_list', 'column_1', 'button_padding', 'value', 'left' ],
                    'col1BtPaddingRight'  => [ 'text_list', 'column_1', 'button_padding', 'value', 'right' ],
                    'col1BtPaddingTop'    => [ 'text_list', 'column_1', 'button_padding', 'value', 'top' ],
                    'col1BtPathUrl'       => [ 'text_list', 'column_1', 'button_url', 'value' ],
                    'col1BtText'          => [ 'text_list', 'column_1', 'button_text', 'value' ],
                    'col1BtTextColor'     => [ 'text_list', 'column_1', 'button_text_color', 'value' ],
                    'col1BtWeight'        => [ 'text_list', 'column_1', 'button_weight', 'value' ],
                    'col1BtWidthButton'   => [ 'text_list', 'column_1', 'button_width', 'value' ],
                    'col1TtContent'       => [ 'text_list', 'column_1', 'rich_text', 'value' ],
                    'col1TtFamily'        => [ 'text_list', 'column_1', 'font_family', 'value' ],
                    'col1TtPaddingBottom' => [ 'text_list', 'column_1', 'padding', 'value', 'bottom' ],
                    'col1TtPaddingLeft'   => [ 'text_list', 'column_1', 'padding', 'value', 'left' ],
                    'col1TtPaddingRight'  => [ 'text_list', 'column_1', 'padding', 'value', 'right' ],
                    'col1TtPaddingTop'    => [ 'text_list', 'column_1', 'padding', 'value', 'top' ],

                    'col2BtAlign'         => [ 'text_list', 'column_2', 'button_align', 'value' ],
                    'col2BtBgColor'       => [ 'text_list', 'column_2', 'button_background_color', 'value' ],
                    'col2BtButtonType'    => [ 'text_list', 'column_2', 'button_type', 'value' ],
                    'col2BtFamily'        => [ 'text_list', 'column_2', 'button_font_family', 'value' ],
                    'col2BtFontSize'      => [ 'text_list', 'column_2', 'button_font_size', 'value' ],
                    'col2BtPaddingBottom' => [ 'text_list', 'column_2', 'button_padding', 'value', 'bottom' ],
                    'col2BtPaddingLeft'   => [ 'text_list', 'column_2', 'button_padding', 'value', 'left' ],
                    'col2BtPaddingRight'  => [ 'text_list', 'column_2', 'button_padding', 'value', 'right' ],
                    'col2BtPaddingTop'    => [ 'text_list', 'column_2', 'button_padding', 'value', 'top' ],
                    'col2BtPathUrl'       => [ 'text_list', 'column_2', 'button_url', 'value' ],
                    'col2BtText'          => [ 'text_list', 'column_2', 'button_text', 'value' ],
                    'col2BtTextColor'     => [ 'text_list', 'column_2', 'button_text_color', 'value' ],
                    'col2BtWeight'        => [ 'text_list', 'column_2', 'button_weight', 'value' ],
                    'col2BtWidthButton'   => [ 'text_list', 'column_2', 'button_width', 'value' ],
                    'col2TtContent'       => [ 'text_list', 'column_2', 'rich_text', 'value' ],
                    'col2TtFamily'        => [ 'text_list', 'column_2', 'font_family', 'value' ],
                    'col2TtPaddingBottom' => [ 'text_list', 'column_2', 'padding', 'value', 'bottom' ],
                    'col2TtPaddingLeft'   => [ 'text_list', 'column_2', 'padding', 'value', 'left' ],
                    'col2TtPaddingRight'  => [ 'text_list', 'column_2', 'padding', 'value', 'right' ],
                    'col2TtPaddingTop'    => [ 'text_list', 'column_2', 'padding', 'value', 'top' ],

                    'col3BtAlign'         => [ 'text_list', 'column_3', 'button_align', 'value' ],
                    'col3BtBgColor'       => [ 'text_list', 'column_3', 'button_background_color', 'value' ],
                    'col3BtButtonType'    => [ 'text_list', 'column_3', 'button_type', 'value' ],
                    'col3BtFamily'        => [ 'text_list', 'column_3', 'button_font_family', 'value' ],
                    'col3BtFontSize'      => [ 'text_list', 'column_3', 'button_font_size', 'value' ],
                    'col3BtPaddingBottom' => [ 'text_list', 'column_3', 'button_padding', 'value', 'bottom' ],
                    'col3BtPaddingLeft'   => [ 'text_list', 'column_3', 'button_padding', 'value', 'left' ],
                    'col3BtPaddingRight'  => [ 'text_list', 'column_3', 'button_padding', 'value', 'right' ],
                    'col3BtPaddingTop'    => [ 'text_list', 'column_3', 'button_padding', 'value', 'top' ],
                    'col3BtPathUrl'       => [ 'text_list', 'column_3', 'button_url', 'value' ],
                    'col3BtText'          => [ 'text_list', 'column_3', 'button_text', 'value' ],
                    'col3BtTextColor'     => [ 'text_list', 'column_3', 'button_text_color', 'value' ],
                    'col3BtWeight'        => [ 'text_list', 'column_3', 'button_weight', 'value' ],
                    'col3BtWidthButton'   => [ 'text_list', 'column_3', 'button_width', 'value' ],
                    'col3TtContent'       => [ 'text_list', 'column_3', 'rich_text', 'value' ],
                    'col3TtFamily'        => [ 'text_list', 'column_3', 'font_family', 'value' ],
                    'col3TtPaddingBottom' => [ 'text_list', 'column_3', 'padding', 'value', 'bottom' ],
                    'col3TtPaddingLeft'   => [ 'text_list', 'column_3', 'padding', 'value', 'left' ],
                    'col3TtPaddingRight'  => [ 'text_list', 'column_3', 'padding', 'value', 'right' ],
                    'col3TtPaddingTop'    => [ 'text_list', 'column_3', 'padding', 'value', 'top' ],
                ];
                break;
            case 'html':
                $element_specific_map = [
                    'HTMLContent' => 'rich_text',
                ];
                break;
            case 'divider':
                $element_specific_map = [
                    'dividerColor' => 'divider_color',
                    'dividerStyle' => 'divider_type',
                ];
                break;
            case 'featured_products':
                $element_specific_map = [
                    'buyBackgroundColor'        => 'buy_button_background_color',
                    'buyText'                   => 'buy_button_label',
                    'buyTextColor'              => 'buy_button_text_color',
                    'numberOfProducts'          => 'number_of_products',
                    'productOriginalPriceColor' => 'regular_price_color',
                    'productPriceColor'         => 'sale_price_color',
                    'productType'               => 'product_type',
                    'productsPerRow'            => 'products_per_row',
                    'showingItems'              => 'showing_items',
                    'sortedBy'                  => 'sorted_by',
                    'topContent'                => 'top_content',
                ];
                break;
            case 'simple_offer':
                $element_specific_map = [
                    'borderColor'           => 'border_color',
                    'borderStyle'           => 'border_style',
                    'borderWidth'           => 'border_width',
                    'buttonBackgroundColor' => 'button_background_color',
                    'buttonText'            => 'button_text',
                    'buttonTextColor'       => 'button_text_color',
                    'buttonUrl'             => 'button_url',
                    'showingItems'          => 'showing_items',
                ];
                break;
            case 'single_banner':
                $element_specific_map = [
                    'buttonAlign'           => 'button_align',
                    'buttonBackgroundColor' => 'button_background_color',
                    'buttonText'            => 'button_text',
                    'buttonTextColor'       => 'button_text_color',
                    'buttonUrl'             => 'button_url',
                    'contentAlign'          => 'content_align',
                    'contentWidth'          => 'content_width',
                    'backgroundImage'       => [ 'background_image', 'url' ],
                    'showingItems'          => 'showing_items',
                ];
                break;
            case 'billing_address':
                $element_specific_map = [
                    'titleBilling' => 'title',
                ];
                break;
            case 'column_layout':
                $element_specific_map = [
                    'backgroundImage'  => [ 'background_image', 'url' ],
                    'backgroundRepeat' => [ 'background_image', 'repeat' ],
                    'backgroundSize'   => [ 'background_image', 'size' ],
                ];
                break;
            case 'hook':
                $element_specific_map = [
                    'content' => 'hook_shortcode',
                ];
                break;

            default:
                break;
        }//end switch

        return array_merge( $common_map, $element_specific_map );
    }

    private function convert_element_data_attribute_values( &$element ) {
        // Map data for logo
        if ( $element['type'] === 'logo' ) {
            // $element['data']['src'] = str_replace( 'assets/dist/images', 'assets/images', $element['data']['src'] );
            if ( empty( $element['data']['src'] ) ) {
                $element['data']['src'] = YAYMAIL_PLUGIN_URL . 'assets/images/woocommerce-logo.png';

            }
        }
        // End logo logic

        // Map data for social_icon
        if ( $element['type'] === 'social_icon' ) {
            if ( ! empty( $element['data']['icon_list'] ) ) {
                $icon_list =& $element['data']['icon_list'];

                $icon_list = array_map(
                    function ( $item ) {
                        return [
                            'icon' => strtolower( $item['icon'] ?? 'facebook' ),
                            'url'  => $item['pathLink'] ?? '#',
                        ];
                    },
                    $icon_list
                );
            }
                return;
        }
        // End social_icon logic

        if ( $element['type'] === 'button' ) {
            // Increase button's width
            $data['width'] = min( $element['data']['width'] + 20, 100 );
        }

        if ( $element['type'] === 'video' ) {
            // Old core uses percent, new core uses px
            $data['width'] = (int) $element['data']['width'] * 600 / 100;
        }

        $number_column_value_map = [
            'one'   => 1,
            'two'   => 2,
            'three' => 3,
        ];

        // Map data for image_list and image_box
        if ( $element['type'] === 'image_list' ) {

            if ( ! empty( $element['data']['number_column'] ) ) {
                $element['data']['number_column'] = $number_column_value_map[ $element['data']['number_column'] ];
                return;
            }
            return;
        }
        // End image_list and image_box logic

        // Map data for text_list
        if ( $element['type'] === 'text_list' ) {
            if ( ! empty( $element['data']['number_column'] ) ) {
                $element['data']['number_column'] = $number_column_value_map[ $element['data']['number_column'] ];
            }

            foreach ( [ 'column_1', 'column_2', 'column_3' ] as $column ) {
                if ( isset( $element['data']['text_list'][ $column ]['show_button'] ) ) {
                    $element['data']['text_list'][ $column ]['show_button']['value'] = ( $element['data']['text_list'][ $column ]['show_button']['value'] === 'show' );
                }
            }
            return;
        }
        // End text_list logic

        // Map data for featured_products
        if ( $element['type'] === 'featured_products' ) {
            if ( ! empty( $element['data']['categories'] ) ) {
                $element['data']['categories'] = array_map(
                    function ( $category ) {

                        if ( ! isset( $category['key'] ) || ! isset( $category['label'] ) ) {
                            return $category;
                        }
                        return [
                            'id'   => $category['key'],
                            'name' => $category['label'],
                        ];
                    },
                    $element['data']['categories']
                );
            }
            if ( ! empty( $element['data']['products'] ) ) {
                $element['data']['products'] = array_map(
                    function ( $product ) {

                        if ( ! isset( $product['key'] ) || ! isset( $product['label'] ) ) {
                            return $product;
                        }
                        return [
                            'id'   => $product['key'],
                            'name' => $product['label'],
                        ];
                    },
                    $element['data']['products']
                );
            }
            if ( ! empty( $element['data']['tags'] ) ) {
                $element['data']['tags'] = array_map(
                    function ( $tag ) {

                        if ( ! isset( $tag['key'] ) || ! isset( $tag['label'] ) ) {
                            return $tag;
                        }
                        return [
                            'id'   => $tag['key'],
                            'name' => $tag['label'],
                        ];
                    },
                    $element['data']['tags']
                );
            }

            if ( ! empty( $element['data']['product_type'] ) ) {
                $featured_product_product_types_map = [
                    'onsale'     => 'on_sale',
                    'categories' => 'category_selections',
                    'products'   => 'product_selections',
                    'tags'       => 'tag_selections',
                ];
                $product_type                       =& $element['data']['product_type'];
                if ( ! empty( $featured_product_product_types_map[ $product_type ] ) ) {
                    $product_type = $featured_product_product_types_map[ $product_type ];
                }
            }

            if ( ! empty( $element['data']['sorted_by'] ) ) {
                $sorted_by_values_map = [
                    'ascName'   => 'name_a_z',
                    'descName'  => 'name_z_a',
                    'ascPrice'  => 'price_ascending',
                    'descPrice' => 'price_descending',
                ];
                $sorted_by            =& $element['data']['sorted_by'];
                if ( ! empty( $sorted_by_values_map[ $sorted_by ] ) ) {
                    $sorted_by = $sorted_by_values_map[ $sorted_by ];
                }
            }

            if ( ! empty( $element['data']['showing_items'] ) && is_array( $element['data']['showing_items'] ) ) {
                $showing_items_map = [
                    'topContent'           => 'top_content',
                    'productImage'         => 'product_image',
                    'productName'          => 'product_name',
                    'productPrice'         => 'product_price',
                    'productOriginalPrice' => 'product_original_price',
                    'butButton'            => 'buy_button',
                ];
                $showing_items     =& $element['data']['showing_items'];
                $showing_items     = array_map(
                    function ( $item ) use ( $showing_items_map ) {
                        if ( isset( $showing_items_map[ $item ] ) ) {
                            return $showing_items_map[ $item ];
                        }
                        return $item;
                    },
                    $showing_items
                );

            }//end if

            return;
        }//end if
        // End featured_products logic

        // Map data for single_banner
        if ( $element['type'] === 'single_banner' ) {
            if ( ! empty( $element['data']['showing_items'] ) && is_array( $element['data']['showing_items'] ) ) {
                $showing_item_values_map = [
                    'backgroundImage' => 'background_image',
                ];
                $showing_items           =& $element['data']['showing_items'];
                $showing_items           = array_map(
                    function ( $item ) use ( $showing_item_values_map ) {
                        if ( isset( $showing_item_values_map[ $item ] ) ) {
                            return $showing_item_values_map[ $item ];
                        }
                        return $item;
                    },
                    $showing_items
                );
            }
        }
        // End single_banner
    }

    private function add_new_attributes_to_element_data( &$element ) {
        $data =& $element['data'];

            // For element Order Details
        if ( $element['type'] === 'order_details' ) {
            // Add these fields to order_details element
            $data['payment_instructions'] = '[yaymail_payment_instructions]';

            // Headers of the table columns/rows
            $order_details_titles         = get_post_meta( $this->current_template_id, '_yaymail_email_order_item_title', true );
            $data['title']                = $order_details_titles['order_title'] ?? '<span style="font-size: 20px;">Order #[yaymail_order_number] <b>([yaymail_order_date])</b></span>';
            $data['product_title']        = $order_details_titles['product_title'] ?? __( 'Product', 'yaymail' );
            $data['cost_title']           = $order_details_titles['cost_title'] ?? __( 'Cost', 'yaymail' );
            $data['quantity_title']       = $order_details_titles['quantity_title'] ?? __( 'Quantity', 'yaymail' );
            $data['price_title']          = $order_details_titles['price_title'] ?? __( 'Price', 'yaymail' );
            $data['cart_subtotal_title']  = $order_details_titles['subtoltal_title'] ?? __( 'Subtotal:', 'yaymail' );
            $data['payment_method_title'] = $order_details_titles['payment_method_title'] ?? __( 'Payment method:', 'yaymail' );
            $data['order_total_title']    = $order_details_titles['total_title'] ?? __( 'Total:', 'yaymail' );
            $data['order_note_title']     = $order_details_titles['customer_note'] ?? __( 'Note:', 'yaymail' );
            $data['shipping_title']       = $order_details_titles['shipping_title'] ?? __( 'Shipping:', 'yaymail' );
            $data['discount_title']       = $order_details_titles['discount_title'] ?? __( 'Discount:', 'yaymail' );
            return;
        }

        if ( $element['type'] === 'billing_address' ) {
            $data['rich_text'] = '[yaymail_billing_address]';
            return;
        }

        if ( $element['type'] === 'shipping_address' ) {
            $data['rich_text'] = '[yaymail_shipping_address]';
            return;
        }

        if ( $element['type'] === 'billing_shipping_address' ) {
            $billing_title  = get_post_meta( $this->current_template_id, '_email_title_billing', true );
            $shipping_title = get_post_meta( $this->current_template_id, '_email_title_shipping', true );

            $data['billing_title']            = ! empty( $billing_title ) ? $billing_title : __( 'Billing Address', 'woocommerce' );
            $data['shipping_title']           = ! empty( $shipping_title ) ? $shipping_title : __( 'Shipping Address', 'woocommerce' );
            $data['shipping_address_content'] = '[yaymail_shipping_address]';
            $data['billing_address_content']  = '[yaymail_billing_address]';
            return;
        }

        if ( $element['type'] === 'order_details_download' ) {
            $data['title']          = __( 'Downloads', 'yaymail' );
            $data['product_title']  = __( 'Products', 'yaymail' );
            $data['expires_title']  = __( 'Expires', 'yaymail' );
            $data['download_title'] = __( 'Download', 'yaymail' );
            return;
        }

        if ( $element['type'] === 'column_layout' ) {
            $data['inner_border_radius']    = [
                'top_left'     => 0,
                'top_right'    => 0,
                'bottom_left'  => 0,
                'bottom_right' => 0,
            ];
            $data['inner_background_color'] = '#fff';

            if ( ! empty( $data['backgroundPosition'] ) ) {
                $position_map = [
                    'unset'         => 'default',
                    'center center' => 'center_center',
                    'center left'   => 'center_left',
                    'center right'  => 'center_right',
                    'top center'    => 'top_center',
                    'top left'      => 'top_left',
                    'top right'     => 'top_right',
                    'bottom center' => 'bottom_center',
                    'bottom left'   => 'bottom_left',
                    'bottom right'  => 'bottom_right',
                ];

                if ( isset( $position_map[ $data['backgroundPosition'] ] ) ) {
                    $data['backgroundPosition'] = $position_map[ $data['backgroundPosition'] ];
                }

                $position_list = array_values( $position_map );
                if ( in_array( $data['backgroundPosition'], $position_list, true ) ) {
                    $data['background_image']['position'] = $data['backgroundPosition'];
                } else {
                    $data['background_image']['position'] = 'custom';
                    $position                             = explode( ' ', str_replace( '%', '', $data['backgroundPosition'] ) );
                    $x                                    = $position[0];
                    $y                                    = $position[1];

                    $data['background_image']['x_position'] = $x;
                    $data['background_image']['y_position'] = $y;
                }
            }//end if

            $amount_of_columns         = 1;
            $data['amount_of_columns'] = $amount_of_columns;

            if ( isset( $data['column4'] ) ) {
                $amount_of_columns = 4;
            } elseif ( isset( $data['column3'] ) ) {
                $amount_of_columns = 3;
            } elseif ( isset( $data['column2'] ) ) {
                $amount_of_columns = 2;
            }

            $element['children'] = ColumnLayout::get_data( $amount_of_columns )['children'] ?? [];

            if ( empty( $element['children'] ) ) {
                return;
            }
            $children =& $element['children'];
            if ( $amount_of_columns >= 1 && isset( $children[0] ) && isset( $data['column1'] ) ) {
                $column_1                = array_map( [ $this, 'convert_element' ], $data['column1'] );
                $children[0]['children'] = $column_1;
            }
            if ( $amount_of_columns >= 2 && isset( $children[1] ) && isset( $data['column2'] ) ) {
                $column_2                = array_map( [ $this, 'convert_element' ], $data['column2'] );
                $children[1]['children'] = $column_2;
            }
            if ( $amount_of_columns >= 3 && isset( $children[2] ) && isset( $data['column3'] ) ) {
                $column_3                = array_map( [ $this, 'convert_element' ], $data['column3'] );
                $children[2]['children'] = $column_3;
            }
            if ( $amount_of_columns >= 4 && isset( $children[3] ) && isset( $data['column4'] ) ) {
                $column_4                = array_map( [ $this, 'convert_element' ], $data['column4'] );
                $children[3]['children'] = $column_4;
            }

            return;
        }//end if

        if ( $element['type'] === 'text_list' ) {
            $text_list           =& $data['text_list'];
            $border_radius_value = [
                'top_left'     => '5',
                'top_right'    => '5',
                'bottom_right' => '5',
                'bottom_left'  => '5',
            ];

            $columns = [ 'column_1', 'column_2', 'column_3' ];

            foreach ( $columns as $column ) {
                if ( ! empty( $text_list[ $column ] ) ) {
                    $text_list[ $column ]['button_border_radius'] = [
                        'value' => $border_radius_value,
                    ];
                    $text_list[ $column ]['button_height']        = [
                        'value' => '21',
                    ];
                }
            }
        }//end if

        if ( $element['type'] === 'single_banner' ) {
            $data['background_image']['position']   = 'center_center';
            $data['background_image']['x_position'] = 0;
            $data['background_image']['y_position'] = 0;
            $data['background_image']['repeat']     = 'default';
            $data['background_image']['size']       = 'cover';
        }

        // Integrated
        $integrated_elements = [ 'tracking_information_by_zorem', 'tracking_information_by_trackingmore', 'wc_admin_custom_order_fields_by_skyverge', 'tracking_information_by_pluginhive', 'yith_tracking_information', 'advanced_local_pickup_instruction', 'wc_software_addon_license_info', 'wc_shipping_tax_shipment_tracking', 'woocommerce_shipment_tracking' ];

        if ( in_array( $element['type'], $integrated_elements, true ) ) {
            $element['integration'] = '3rd';
            if ( 'tracking_information_by_zorem' === $element['type'] ) {
                $element['name']                = __( 'Tracking Information (By Zorem)', 'yaymail' );
                $element['group']               = 'Advanced Shipment Tracking for WooCommerce';
                $element['data']['rich_text']   = '[yaymail_order_tracking_information_by_zorem]';
                $element['data']['title']       = __( 'Tracking Information', 'yaymail' );
                $element['data']['button_text'] = __( 'Track Your Order', 'yaymail' );
            }
            if ( 'tracking_information_by_trackingmore' === $element['type'] ) {
                $element['name']                          = __( 'Tracking Information (By TrackingMore)', 'yaymail' );
                $element['data']['title']                 = __( 'Tracking Information', 'yaymail' );
                $element['data']['courier_title']         = __( 'Track Your Order', 'yaymail' );
                $element['data']['tracking_number_title'] = __( 'Tracking number', 'yaymail' );
            } elseif ( 'tracking_information_by_pluginhive' === $element['type'] ) {
                $element['name']              = __( 'Tracking Information (By PluginHive)', 'yaymail' );
                $element['data']['rich_text'] = TrackingInformationElement::get_data()['data']['rich_text']['default_value'];
                $element['data']['title']     = __( 'Tracking Information', 'yaymail' );
            } elseif ( 'yith_tracking_information' === $element['type'] ) {
                $element['name']              = __( 'Tracking Information (By YITH)', 'yaymail' );
                $element['data']['rich_text'] = YITHTrackingInformationElement::get_data()['data']['rich_text']['default_value'];
                $element['data']['title']     = __( 'Tracking Information', 'yaymail' );
            } elseif ( 'advanced_local_pickup_instruction' === $element['type'] ) {
                $element['data']['rich_text']            = AdvancedLocalPickupInstructionElement::get_data()['data']['rich_text']['default_value'];
                $element['data']['title']                = __( 'Pick up information', 'yaymail' );
                $element['data']['pickup_address_title'] = __( 'Pickup Address', 'yaymail' );
                $element['data']['pickup_hours_title']   = __( 'Pickup Hours', 'yaymail' );
            } elseif ( 'wc_shipping_tax_shipment_tracking' === $element['type'] ) {
                $element['data']['rich_text']             = ShipmentTrackingElement::get_data()['data']['rich_text']['default_value'];
                $element['data']['title']                 = __( 'Tracking', 'yaymail' );
                $element['data']['provider_title']        = __( 'Provider', 'yaymail' );
                $element['data']['tracking_number_title'] = __( 'Tracking number', 'yaymail' );
            } elseif ( 'wc_software_addon_license_info' === $element['type'] ) {
                $element['data']['rich_text'] = SoftwareLicenseElement::get_data()['data']['rich_text']['default_value'];
                $element['data']['title']     = __( 'License Keys', 'yaymail' );
            } elseif ( 'woocommerce_shipment_tracking' === $element['type'] ) {
                $default_data                             = \YayMail\Integrations\WooCommerceShipmentTracking\Elements\TrackingInformationElement::get_data();
                $element['name']                          = __( 'Tracking Information (By WooCommerce Shipment Tracking)', 'yaymail' );
                $element['data']['rich_text']             = $default_data['data']['rich_text']['default_value'];
                $element['data']['title']                 = $default_data['data']['title']['default_value'];
                $element['data']['provider_title']        = $default_data['data']['provider_title']['default_value'];
                $element['data']['tracking_number_title'] = $default_data['data']['tracking_number_title']['default_value'];
                $element['data']['date_title']            = $default_data['data']['date_title']['default_value'];
            }//end if
        }//end if
    }

    private function migrate_shortcodes( &$data, $element_type = '' ) {
        $shortcodes_map = [
            '[yaymail_items_border_content]'           => '[yaymail_order_details]',
            '[yaymail_items_border_title]'             => '[Order #[yaymail_order_number]] ([yaymail_order_date])',
            '[yaymail_items_downloadable_product]'     => '[yaymail_order_details_download_product]',
            '[yaymail_user_account_url_string]'        => '[yaymail_user_account_url]',
            '[yaymail_quantity_count]'                 => '[yaymail_order_product_item_count]',
            '[yaymail_orders_count]'                   => '[yaymail_order_product_line_item_count]',
            '[yaymail_orders_count_double]'            => '[yaymail_order_product_line_item_count_double]',
            '[yaymail_order_total_numbers]'            => '[yaymail_order_total_value]',
            '[yaymail_order_sub_total]'                => '[yaymail_order_subtotal]',
            '[yaymail_order_shipping]'                 => '[yaymail_shipping_total]',
            '[yaymail_payment_instruction]'            => '[yaymail_payment_instructions]',
            '[yaymail_payment_method]'                 => '[yaymail_order_payment_method]',
            '[yaymail_transaction_id]'                 => '[yaymail_payment_transaction_id]',
            '[yaymail_set_password_url_string]'        => '[yaymail_set_password_url]',
            '[yaymail_order_payment_url_string]'       => '[yaymail_order_payment_url]',

            // Turn the shortcode into a plain hook name
            '[woocommerce_email_before_order_table]'   => '[yaymail_custom_hook hook="woocommerce_email_before_order_table"]',
            '[woocommerce_email_after_order_table]'    => '[yaymail_custom_hook hook="woocommerce_email_after_order_table"]',

            // Integrated
            // Advanced shipment tracking for Woocommerce by zorem
            '[yaymail_shipment_tracking_title]'        => __( 'Tracking information', 'yaymail' ),
            '[yaymail_tracking_more_info]'             => '[yaymail_order_trackingmore_tracking_information]',
            // WC Shipping & Tax
            '[yaymail_shipping_tax_shipment_tracking]' => '[yaymail_wc_shipping_tax_shipment_tracking]',
            // WooCommerce Software Add-Ons by SkyVerge
            '[yaymail_software_add_on]'                => '[yaymail_wc_software_addon_license_info]',
            // Back In Stock Notifier
            '[yaymail_notifier_email_id]'              => '[yaymail_notifier_subscriber_email]',
            '[yaymail_notifier_only_product_sku]'      => '[yaymail_notifier_product_sku]',
            '[yaymail_notifier_only_product_image]'    => '[yaymail_notifier_product_image]',
        ];

        /**
         * This maybe cause a mistake when the 3rd party plugin is not installed, but in normal case without those plugins
         * the shortcode will be correctly replaced.
         */
        if ( AdvancedShipmentTracking::is_3rd_party_installed() ) {
            $shortcodes_map['[yaymail_order_meta:_wc_shipment_tracking_items]'] = '[yaymail_order_tracking_information_by_zorem]';
        }
        if ( WcAdminCustomOrderFieldBySkyverge::is_3rd_party_installed() ) {
            $shortcodes_map['[yaymail_order_meta:_wc_additional_order_details]'] = '[yaymail_order_skyverge_custom_order_fields]';
        }

        if ( $element_type === 'heading' || $element_type === 'text' ) {
            $shortcodes_map['[yaymail_order_id]']     = '[yaymail_order_id is_plain="true"]';
            $shortcodes_map['[yaymail_order_number]'] = '[yaymail_order_number is_plain="true"]';
        }

        foreach ( $data as $key => &$value ) {
            if ( is_array( $value ) ) {
                $this->migrate_shortcodes( $value, $element_type );
                continue;
            }

            foreach ( $shortcodes_map as $old => $new ) {

                $value = str_replace( $old, $new, $value );
            }
        }
    }
}
