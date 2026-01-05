<?php

namespace WPDesk\FlexibleCouponsPro\Email;

class StringAttachments {

	/**
	 * @var array
	 */
	private $attachments;

	/**
	 * @param array $attachments
	 */
	public function __construct( array $attachments ) {
		$this->attachments = $attachments;
	}

	/**
	 * Add WordPress action.
	 */
	public function add_action() {
		add_action( 'phpmailer_init', [ $this, 'add_string_attachments' ] );
	}

	/**
	 * Remove WordPress action.
	 */
	public function remove_action() {
		remove_action( 'phpmailer_init', [ $this, 'add_string_attachments' ] );
	}

	/**
	 * Add attachments to mail from string.
	 *
	 * @param \PHPMailer $phpmailer
	 */
	public function add_string_attachments( $phpmailer ) {
		foreach ( $this->attachments as $attachment ) {
			$phpmailer->addStringAttachment( $attachment['content'], $attachment['fileName'] );
		}
	}
}
