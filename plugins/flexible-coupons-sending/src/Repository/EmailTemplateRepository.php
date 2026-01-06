<?php
namespace WPDesk\FCS\Repository;

use WP_Error;
use WPDesk\Library\WPCoupons\Product;
use WPDesk\FCS\Exception\RepositoryException;
use WPDesk\FCS\PostType\EmailTemplate;
use WPDesk\FCS\Entity\EmailTemplateEntity;
use WP_Post;


class EmailTemplateRepository {

	/**
	 * Creates a new email template.
	 *
	 * @param array $data Template data.
	 * @return int Post ID on success.
	 * @throws RepositoryException On failure.
	 */
	public function create( array $data ): int {
		$data['post_type'] = EmailTemplate::POST_TYPE;
		$result            = wp_insert_post( $data, true );

		if ( is_wp_error( $result ) ) {
			throw new RepositoryException( 'Failed to create email template: ' . $result->get_error_message() );
		}

		return $result;
	}

	/**
	 * Updates an existing email template.
	 *
	 * @param int   $id   Post ID.
	 * @param array $data Template data.
	 * @return int Post ID on success.
	 * @throws RepositoryException On failure.
	 */
	public function update( int $id, array $data ): int {
		$data['ID'] = $id;
		$result     = wp_update_post( $data, true );

		if ( is_wp_error( $result ) ) {
			throw new RepositoryException( 'Failed to update email template: ' . $result->get_error_message() );
		}

		return $result;
	}

	/**
	 * Deletes an email template.
	 *
	 * @param int $template_id The ID of the template to delete.
	 * @return bool True on success.
	 * @throws RepositoryException On failure.
	 */
	public function delete( int $template_id ): bool {
		$result = wp_delete_post( $template_id, true );
		if ( is_wp_error( $result ) || ! $result ) {
			throw new RepositoryException( 'Failed to delete template: ' . ( is_wp_error( $result ) ? $result->get_error_message() : 'Unknown error' ) );
		}
		return true;
	}

	/**
	 * Retrieves a single email template by its ID.
	 *
	 * @param int $template_id The ID of the template.
	 * @return EmailTemplateEntity Template data.
	 * @throws RepositoryException If the template is not found or an error occurs.
	 */
	public function get_by_id( int $template_id ): EmailTemplateEntity {
		$post = get_post( $template_id );

		if ( is_wp_error( $post ) ) {
			throw new RepositoryException( 'Error retrieving email template: ' . $post->get_error_message() );
		}

		if ( ! $post || $post->post_type !== EmailTemplate::POST_TYPE ) {
			throw new RepositoryException( __( 'Email template not found.', 'flexible-coupons-sending' ), 404 );
		}

		$additional_fields = $this->get_additional_fields( $post->ID );

		return new EmailTemplateEntity(
			$post->ID,
			$post->post_title,
			$additional_fields['subject'],
			$post->post_content,
			$additional_fields['recipients'],
			$additional_fields['enabled'],
			$additional_fields['is_default']
		);
	}

	/**
	 * @return array<int, WP_Post>
	 */
	public function get_raw_posts(): array {
		$posts = get_posts(
			[
				'post_type'   => EmailTemplate::POST_TYPE,
				'post_status' => 'any',
				'numberposts' => -1,
			]
		);

		return $posts;
	}

	/**
	 * @return array<int, EmailTemplateEntity>
	 */
	public function get_all(): array {
		$posts = $this->get_raw_posts();

		return array_reduce(
			$posts,
			function ( array $carry, WP_Post $post ) {
				$additional_fields = $this->get_additional_fields( $post->ID );
				$carry[]           = new EmailTemplateEntity(
					$post->ID,
					$post->post_title,
					$additional_fields['subject'],
					$post->post_content,
					$additional_fields['recipients'],
					$additional_fields['enabled'],
					$additional_fields['is_default']
				);
				return $carry;
			},
			[]
		);
	}

	public function reset_default(): void {
		try {
			$email_template = $this->get_default();
		} catch ( RepositoryException $e ) {
			return;
		}

		update_post_meta( $email_template->get_id(), EmailTemplate::IS_DEFAULT_META_KEY, 0 );
	}

	public function get_default(): EmailTemplateEntity {
		$posts = get_posts(
			[
				'post_type'   => EmailTemplate::POST_TYPE,
				'post_status' => 'any',
				'numberposts' => -1,
				'fields'      => 'ids',
				'meta_key'    => EmailTemplate::IS_DEFAULT_META_KEY,
				'meta_value'  => '1',
			]
		);

		if ( ! is_array( $posts ) ) {
			throw new RepositoryException( 'Failed to get default email template' );
		}

		if ( count( $posts ) === 0 ) {
			throw new RepositoryException( 'No default email template found.' );
		}

		return $this->get_by_id( reset( $posts ) );
	}

	/**
	 * Checks if there is only one email template.
	 *
	 * @return bool True if only one template exists, false otherwise.
	 */
	public function has_only_one_template(): bool {
		$posts = $this->get_raw_posts();
		return count( $posts ) === 1;
	}

	/**
	 * Ensures that a default email template exists. If no default is found,
	 * the first available template (sorted by ID) is set as default.
	 */
	public function ensure_default_exists(): void {
		try {
			$this->get_default();
			return; // A default template already exists.
		} catch ( RepositoryException $e ) {
			// No default template found, proceed to set one.
		}

		$posts = $this->get_raw_posts();
		if ( empty( $posts ) ) {
			return; // No templates to set as default.
		}

		// Sort posts by ID to ensure consistent selection of the "first" template.
		usort(
			$posts,
			function ( $a, $b ) {
				return $a->ID - $b->ID;
			}
		);

		$first_template_id = $posts[0]->ID;
		update_post_meta( $first_template_id, EmailTemplate::IS_DEFAULT_META_KEY, 1 );
	}

	/**
	 * @param int $template_id
	 *
	 * @return array{subject:string, recipients:array<int, string>, enabled:bool, is_default:bool}
	 */
	private function get_additional_fields( int $template_id ): array {
		return [
			'subject'    => (string) get_post_meta( $template_id, EmailTemplate::SUBJECT_META_KEY, true ),
			'recipients' => (array) get_post_meta( $template_id, EmailTemplate::RECIPIENT_META_KEY, true ),
			'enabled'    => (bool) get_post_meta( $template_id, EmailTemplate::ENABLED_META_KEY, true ),
			'is_default' => (bool) get_post_meta( $template_id, EmailTemplate::IS_DEFAULT_META_KEY, true ),
		];
	}
}
