<?php

namespace YayMail\Controllers;

use YayMail\Abstracts\BaseController;
use YayMail\Models\SettingModel;
use YayMail\Utils\SingletonTrait;

/**
 * Settings Controller
 * * @method static SettingController get_instance()
 */
class SettingController extends BaseController {
    use SingletonTrait;

    private $model = null;

    protected function __construct() {
        $this->model = SettingModel::get_instance();
        $this->init_hooks();
    }

    protected function init_hooks() {
        register_rest_route(
            YAYMAIL_REST_NAMESPACE,
            '/settings',
            [
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'exec_get_settings' ],
                    'permission_callback' => [ $this, 'permission_callback' ],
                ],
                [
                    'methods'             => \WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'exec_update_settings' ],
                    'permission_callback' => [ $this, 'permission_callback' ],
                ],
            ]
        );
    }

    public function exec_get_settings( \WP_REST_Request $request ) {
        return $this->exec( [ $this, 'get_settings' ], $request );
    }

    public function get_settings() {
        $settings = $this->model::find_all();
        return $settings;
    }

    public function exec_update_settings( \WP_REST_Request $request ) {
        return $this->exec( [ $this, 'update_settings' ], $request );
    }

    public function update_settings( \WP_REST_Request $request ) {
        $settings               = is_array( $request->get_param( 'settings' ) ) ? array_map( 'sanitize_text_field', wp_unslash( $request->get_param( 'settings' ) ) ) : [];
        $settings['custom_css'] = wp_strip_all_tags( isset( $request->get_param( 'settings' )['custom_css'] ) ? $request->get_param( 'settings' )['custom_css'] : '' );
        $this->model::update( $settings );
        return [
            'success' => true,
            'data'    => $settings,
        ];
    }
}
