<?php
namespace WPDesk\FCS\Migrations;

use WPDesk\FCS\Repository\EmailTemplateRepository;
use WPDesk\FCS\Entity\EmailTemplateEntity;
use WPDesk\FCS\Exception\RepositoryException;
use FCSVendor\WPDesk\Migrations\AbstractMigration;


class Version200 extends AbstractMigration {

	public function up(): bool {
		if (
			false === get_option( 'flexible_coupons_sending_email_subject' ) &&
			false === get_option( 'flexible_coupons_sending_email_body' ) &&
			false === get_option( 'flexible_coupons_sending_additional_recipients' )
		) {
			return true;
		}

		$subject    = get_option( 'flexible_coupons_sending_email_subject', '' );
		$body       = get_option( 'flexible_coupons_sending_email_body', '' );
		$recipients = get_option( 'flexible_coupons_sending_additional_recipients', '' );

		$template_data = [
			'post_title'   => __( 'Default Imported Template', 'flexible-coupons-sending' ),
			'post_content' => $body,
			'meta_input'   => [
				'subject'    => $subject,
				'recipient'  => $recipients,
				'enabled'    => true,
				'is_default' => true,
			],
		];

		try {
			$repository = new EmailTemplateRepository();
			$repository->create( $template_data );

			return true;

		} catch ( RepositoryException $e ) {
			$this->logger->error( 'Email template migration (up) failed (RepositoryException): ' . $e->getMessage() );
			return false;
		}
	}
}
