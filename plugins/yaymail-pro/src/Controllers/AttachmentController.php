<?php

namespace YayMail\Controllers;

use YayMail\Abstracts\BaseController;
use YayMail\Integrations\TranslationModule;
use YayMail\Models\TemplateModel;
use YayMail\Utils\SingletonTrait;

/**
 * Attachment Controller
 *
 * @method static AttachmentController get_instance()
 */
class AttachmentController extends BaseController {
    use SingletonTrait;

    private $model = null;

    protected function __construct() {
        $this->model = TemplateModel::get_instance();
        $this->init_hooks();
    }

    protected function init_hooks() {
        register_rest_route(
            YAYMAIL_REST_NAMESPACE,
            '/attachments',
            [
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'exec_get_all_attachments' ],
                    'permission_callback' => [ $this, 'permission_callback' ],
                    'args'                => [
                        'language' => [
                            'type'     => 'string',
                            'required' => false,
                        ],
                    ],
                ],
                [
                    'methods'             => \WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'exec_update_attachments' ],
                    'permission_callback' => [ $this, 'permission_callback' ],
                    'args'                => [
                        'template_id' => [
                            'type'     => 'string | int',
                            'required' => true,
                        ],
                        'attachments' => [
                            'type'     => 'array',
                            'required' => true,
                        ],
                        'language'    => [
                            'type'     => 'string',
                            'required' => false,
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Get all attachments for all templates and general attachments
     *
     * @param \WP_REST_Request $request The request object.
     * @return \WP_REST_Response
     */
    public function exec_get_all_attachments( \WP_REST_Request $request ) {
        return $this->exec( [ $this, 'get_all_attachments' ], $request );
    }

    /**
     * Get all attachments for all templates and general attachments
     *
     * @param \WP_REST_Request $request The request object.
     * @return array
     */
    public function get_all_attachments( \WP_REST_Request $request ) {
        $language = TranslationModule::checked_language( $request->get_param( 'language' ) ?? TranslationModule::get_instance()->get_active_language() );

        $templates = TemplateModel::find_all( $language );

        $general_attachment = get_option(
            "yaymail_general_attachment_$language",
            [
                'template_title' => __( 'General Attachment', 'yaymail' ),
                'name'           => 'general_attachment',
                'status'         => 'inactive',
                'attachments'    => [],
            ]
        );

        $all_attachments = [
            'general'   => $general_attachment,
            'templates' => [],
        ];

        foreach ( $templates as $template ) {
            $all_attachments['templates'][] = [
                'id'             => $template['id'] ?? '',
                'name'           => $template['name'] ?? '',
                'template_title' => $template['template_title'] ?? '',
                'status'         => $template['status'] ?? 'inactive',
                'attachments'    => $template['attachments'] ?? [],
            ];
        }

        return $all_attachments;
    }

    /**
     * Update attachments for a specific template
     *
     * @param \WP_REST_Request $request The request object.
     * @return \WP_REST_Response
     */
    public function exec_update_attachments( \WP_REST_Request $request ) {
        return $this->exec( [ $this, 'update_attachments' ], $request );
    }

    /**
     * Update attachments for a specific template
     *
     * @param \WP_REST_Request $request The request object.
     * @return array
     */
    public function update_attachments( \WP_REST_Request $request ) {
        $template_id = $request->get_param( 'template_id' );
        $attachments = $request->get_param( 'attachments' );
        $language    = TranslationModule::checked_language( $request->get_param( 'language' ) ?? TranslationModule::get_instance()->get_active_language() );

        if ( $template_id === 'general_attachment' ) {
            $general_attachment                = get_option(
                "yaymail_general_attachment_$language",
                [
                    'template_title' => __( 'General Attachment', 'yaymail' ),
                    'name'           => 'general_attachment',
                    'attachments'    => [],
                ]
            );
            $general_attachment['attachments'] = $attachments;
            update_option( "yaymail_general_attachment_$language", $general_attachment, false );
        } else {
            $updated_data = $this->model::update( $template_id, [ 'attachments' => $attachments ], false );
        }

        return [
            'success'     => true,
            'attachments' => $template_id === 'general_attachment' ? $attachments : $updated_data['attachments'],
            'message'     => __( 'Attachments updated successfully', 'yaymail' ),
        ];
    }
}
