<?php

namespace YayMail\Controllers;

use YayMail\Abstracts\BaseController;
use YayMail\Models\UserSavedPattern;
use YayMail\Utils\SingletonTrait;

/**
 * UserSavedPatterns Controller
 * * @method static UserSavedPatternsController get_instance()
 */
class UserSavedPatternsController extends BaseController {
    use SingletonTrait;

    /**
     * @var UserSavedPattern
     */
    private $model = null;

    protected function __construct() {
        $this->model = UserSavedPattern::get_instance();
        $this->init_hooks();
    }

    protected function init_hooks() {
        register_rest_route(
            YAYMAIL_REST_NAMESPACE,
            '/user-saved-patterns',
            [
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'exec_get_patterns' ],
                    'permission_callback' => [ $this, 'permission_callback' ],
                ],
                [
                    'methods'             => \WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'exec_add_pattern' ],
                    'permission_callback' => [ $this, 'permission_callback' ],
                ],

            ]
        );
        register_rest_route(
            YAYMAIL_REST_NAMESPACE,
            '/user-saved-patterns/(?P<pattern_id>[a-zA-Z0-9_-]+)',
            [
                [
                    'methods'             => \WP_REST_Server::DELETABLE,
                    'callback'            => [ $this, 'exec_delete_pattern' ],
                    'permission_callback' => [ $this, 'permission_callback' ],
                    'args'                => [
                        'pattern_id' => [
                            'type'     => 'string',
                            'required' => true,
                        ],
                    ],
                ],
            ]
        );
    }

    public function exec_get_patterns( \WP_REST_Request $request ) {
        return $this->exec( [ $this, 'get_patterns' ], $request );
    }
    public function get_patterns() {
        $patterns = $this->model->find_all();
        return $patterns;
    }

    public function exec_add_pattern( \WP_REST_Request $request ) {
        return $this->exec( [ $this, 'add_pattern' ], $request );
    }
    public function add_pattern( \WP_REST_Request $request ) {
        $pattern      = $request->get_param( 'pattern' );
        $updated_list = $this->model->save( $pattern );
        return [
            'success'  => true,
            'patterns' => $updated_list,
        ];
    }

    public function exec_delete_pattern( \WP_REST_Request $request ) {
        return $this->exec( [ $this, 'delete_pattern' ], $request );
    }
    public function delete_pattern( \WP_REST_Request $request ) {
        $id           = sanitize_text_field( $request->get_param( 'pattern_id' ) );
        $updated_list = $this->model->delete( $id );
        return [
            'success'  => true,
            'patterns' => $updated_list,
        ];
    }
}
