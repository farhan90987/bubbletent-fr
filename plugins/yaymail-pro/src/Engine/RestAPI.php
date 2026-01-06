<?php
namespace YayMail\Engine;

use YayMail\Controllers\MigrationController;
use YayMail\Controllers\RevisionController;
use YayMail\Utils\SingletonTrait;
use YayMail\Controllers\SettingController;
use YayMail\Controllers\TemplateController;
use YayMail\Controllers\ProductController;
use YayMail\Controllers\UserSavedPatternsController;
use YayMail\Controllers\AddonController;
use YayMail\Controllers\AttachmentController;

/**
 * YayMail Rest API
 */
class RestAPI {
    use SingletonTrait;

    /**
     * Hooks Initialization
     *
     * @return void
     */
    protected function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_yaymail_endpoints' ] );
    }

    /**
     * Add YayMail Endpoints
     */
    public function add_yaymail_endpoints() {
        TemplateController::get_instance();
        SettingController::get_instance();
        RevisionController::get_instance();
        ProductController::get_instance();
        UserSavedPatternsController::get_instance();
        MigrationController::get_instance();
        AddonController::get_instance();
        AttachmentController::get_instance();
        do_action( 'yaymail_init_rest_controllers' );
    }
}
