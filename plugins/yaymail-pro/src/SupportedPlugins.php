<?php
namespace YayMail;

use YayMail\Models\AddonModel;
use YayMail\Utils\SingletonTrait;
use YayMail\Utils\Logger;
/**
 * YayMail SupportedPlugins
 *
 * @method static SupportedPlugins get_instance()
 */
class SupportedPlugins {

    use SingletonTrait;

    private $logger;

    private $wc_emails               = [];
    private $addon_supported_plugins = [];

    private function __construct() {
        $this->logger = new Logger();

        $this->addon_supported_plugins = AddonModel::get_3rd_party_addons();
    }


    public function get_template_ids_from_core() {
        return AddonModel::get_template_ids(
            [
                'WC_Email_Cancelled_Order',
                'WC_Email_Customer_Cancelled_Order',
                'WC_Email_Customer_Completed_Order',
                'WC_Email_Customer_Invoice',
                'WC_Email_Customer_New_Account',
                'WC_Email_Customer_Note',
                'WC_Email_Customer_On_Hold_Order',
                'WC_Email_Customer_Processing_Order',
                'WC_Email_Customer_Refunded_Order',
                'WC_Email_Customer_Reset_Password',
                'WC_Email_Failed_Order',
                'WC_Email_Customer_Failed_Order',
                'WC_Email_New_Order',
            ]
        );
    }


    /**
     * Determines the source of support for a given template ID.
     *
     * This method checks if the template ID is supported by the core, an addon, or neither.
     *
     * @param string $template_id The template ID to check.
     * @return string Returns 'already_supported', 'addon_needed' if supported by an addon, 'pro_needed' if supported by pro, or 'not_supported'.
     */
    private function get_support_status( string $template_id ): string {
        // If the YayMail template data exists, it means the template is supported and ready to be edited
        if ( ! empty( $this->get_yaymail_template_data( $template_id ) ) ) {
            return 'already_supported';
        }

        /**
         * Check addons
         */
        $template_ids_from_addons = [];
        foreach ( $this->get_addon_supported_plugins() as $third_party ) {
            if ( ! empty( $third_party['template_ids'] ) && ! empty( $third_party['is_3rd_party_installed'] ) ) {
                $template_ids_from_addons = array_merge( $template_ids_from_addons, $third_party['template_ids'] );
            }
        }
        if ( in_array( $template_id, $template_ids_from_addons, true ) ) {
            return 'addon_needed';
        }

        return 'not_supported';
    }

    /**
     * Get the plugin name based on a specific template ID.
     *
     * @param array  $addons       The array of addons, each containing plugin_name and template_ids.
     * @param string $template_id The template ID to search for.
     * @return string|null        The plugin name if the template ID is found, or null if not found.
     */
    private function get_addon_info( string $template_id ): ?array {

        foreach ( $this->addon_supported_plugins as $addon ) {
            // Check if 'template_ids' exists and contains the specified template ID
            if ( isset( $addon['template_ids'] ) && in_array( $template_id, $addon['template_ids'], true ) ) {
                return $addon;
            }
        }

        return null;
    }

    /**
     * Retrieves support information for a given template.
     *
     * @param string $template_id Template id
     *
     * @return array An associative array containing:
     *               - 'support_status' (string): 'already_supported', 'addon_needed' if supported by an addon, 'pro_needed' if supported by pro, or 'not_supported'.
     *               - 'addon_info' (array|null): array (object) that has 3 fields: {plugin_name: string, template_ids: array of strings, link_upgrade: string}
     */
    public function get_support_info( string $template_id ): array {
        $support_status = $this->get_support_status( $template_id );
        $addon_info     = $this->get_addon_info( $template_id );

        return [
            'status' => $support_status,
            'addon'  => $addon_info,
        ];
    }

    public function get_yaymail_template_data( $template_id ) {
        $yaymail_emails = \yaymail_get_emails();
        return current( array_filter( $yaymail_emails, fn( $email ) => $email->get_id() === $template_id ) );
    }

    public function get_addon_supported_plugins() {
        return $this->addon_supported_plugins;
    }

    public function get_all_addon_supported_template_ids() {
        $template_ids = [];
        foreach ( $this->addon_supported_plugins as $addon_namespace => $addon ) {
            $template_ids = array_merge( $template_ids, $this->get_addon_supported_template_ids( $addon_namespace ) );
        }
        return $template_ids;
    }

    public function get_addon_supported_template_ids( string $addon_namespace ): array {
        return $this->addon_supported_plugins[ $addon_namespace ]['template_ids'] ?? [];
    }

    public function get_slug_name_supported_plugins(): array {
        return array_map(
            function( $addon ) {
                return [
                    'plugin_name' => $addon['plugin_name'] ?? '',
                    'slug_name'   => $addon['slug_name'] ?? '',
                ];
            },
            $this->addon_supported_plugins
        );
    }
}
