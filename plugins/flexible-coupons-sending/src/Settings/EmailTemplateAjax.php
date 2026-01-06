<?php
namespace WPDesk\FCS\Settings;

use WP_Error;
use FCSVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\FCS\Repository\EmailTemplateRepository;
use WPDesk\FCS\Entity\EmailTemplateEntity;
use WPDesk\FCS\Exception\RepositoryException;
use WPDesk\FCS\PostType\EmailTemplate;


class EmailTemplateAjax implements Hookable {
	const ACTION_PREFIX = 'fc_email_template_';

	private EmailTemplateRepository $repository;

	public function __construct( EmailTemplateRepository $repository ) {
		$this->repository = $repository;
	}

	public function hooks() {
		add_action( 'wp_ajax_' . self::ACTION_PREFIX . 'create', [ $this, 'handle_create' ] );
		add_action( 'wp_ajax_' . self::ACTION_PREFIX . 'update', [ $this, 'handle_update' ] );
		add_action( 'wp_ajax_' . self::ACTION_PREFIX . 'delete', [ $this, 'handle_delete' ] );
		add_action( 'wp_ajax_' . self::ACTION_PREFIX . 'get_all', [ $this, 'handle_get_all' ] );
	}

	public function handle_create() {
		$this->verify_request();

		$template_data = $this->get_template_data();

		try {
			if ( $template_data['meta_input'][ EmailTemplate::IS_DEFAULT_META_KEY ] ) {
				$this->repository->reset_default();
			}
			$result       = $this->repository->create( $template_data );
			$new_template = $this->repository->get_by_id( $result );

			wp_send_json_success( $new_template->get_formatted_data() );
		} catch ( RepositoryException $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	public function handle_update() {
		$this->verify_request();

		$template_id = absint( $_POST['id'] ?? 0 ); // @phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( $template_id === 0 ) {
			wp_send_json_error( __( 'Template ID is required for update.', 'flexible-coupons-sending' ), 400 );
		}

		$template_data = $this->get_template_data();

		try {
			$current_template = $this->repository->get_by_id( $template_id );

			// Prevent unsetting the last default template.
			if ( ! $template_data['meta_input'][ EmailTemplate::IS_DEFAULT_META_KEY ] && $current_template->is_default() ) {
				wp_send_json_error( __( 'Cannot unset the default template when it is the only one.', 'flexible-coupons-sending' ) );
			}

			if ( $template_data['meta_input'][ EmailTemplate::IS_DEFAULT_META_KEY ] ) {
				$this->repository->reset_default();
			}
			$result           = $this->repository->update( $template_id, $template_data );
			$updated_template = $this->repository->get_by_id( $template_id );

			wp_send_json_success( $updated_template->get_formatted_data() );
		} catch ( RepositoryException $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	public function handle_delete() {
		$this->verify_request();

		$template_id = absint( $_POST['id'] ?? 0 ); // @phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( $template_id === 0 ) {
			wp_send_json_error( __( 'Invalid template ID', 'flexible-coupons-sending' ), 400 );
		}

		try {
			$is_deleted_template_default = false;
			try {
				$default_template = $this->repository->get_default();
				if ( $default_template->get_id() === $template_id ) {
					$is_deleted_template_default = true;
				}
			} catch ( RepositoryException $e ) {
				// No default template found, or other error, proceed with deletion.
			}

			$result = $this->repository->delete( $template_id );
			if ( $is_deleted_template_default ) {
				$this->repository->ensure_default_exists();
			}
			wp_send_json_success( __( 'Template deleted', 'flexible-coupons-sending' ) );
		} catch ( RepositoryException $e ) {
			wp_send_json_error( $e->getMessage(), 500 );
		}
	}

	public function handle_get_all() {
		$this->verify_request();

		$templates = $this->repository->get_all();

		wp_send_json_success(
			array_map(
				function ( EmailTemplateEntity $template ) {
					return $template->get_formatted_data();
				},
				$templates
			)
		);
	}

	private function verify_request(): void {
		check_ajax_referer( 'fc-email-templates-nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized', 'flexible-coupons-sending' ), 403 );
		}
	}

	/**
	 * @return array<string, mixed>
	 */
	private function get_template_data(): array {
		// @phpcs:disable WordPress.Security.NonceVerification.Missing
		$template_data = [
			'post_title'   => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
			'post_content' => wp_kses_post( wp_unslash( $_POST['content'] ?? '' ) ),
			'meta_input'   => [
				EmailTemplate::SUBJECT_META_KEY    => sanitize_text_field( wp_unslash( $_POST['subject'] ?? '' ) ),
				EmailTemplate::RECIPIENT_META_KEY  => $this->get_recipients(),
				EmailTemplate::ENABLED_META_KEY    => isset( $_POST['enabled'] ) ? filter_var( wp_unslash( $_POST['enabled'] ), FILTER_VALIDATE_BOOLEAN ) : false,
				EmailTemplate::IS_DEFAULT_META_KEY => isset( $_POST['is_default'] ) ? filter_var( wp_unslash( $_POST['is_default'] ), FILTER_VALIDATE_BOOLEAN ) : false,
			],
		];
		// @phpcs:enable WordPress.Security.NonceVerification.Missing

		return $template_data;
	}

	/**
	 * @return array<int, string>
	 */
	private function get_recipients(): array {
		$recipients = sanitize_text_field( wp_unslash( $_POST['recipients'] ?? '' ) ); // @phpcs:ignore WordPress.Security.NonceVerification.Missing
		$recipients = explode( ',', $recipients );
		$recipients = array_map( 'trim', $recipients );

		return $recipients;
	}
}
