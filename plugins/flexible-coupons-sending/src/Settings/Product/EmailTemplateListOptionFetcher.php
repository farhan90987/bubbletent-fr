<?php
namespace WPDesk\FCS\Settings\Product;

use FCSVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\FCS\Repository\EmailTemplateRepository;
use WP_Post;


class EmailTemplateListOptionFetcher implements Hookable {

	private EmailTemplateRepository $email_template_repository;

	public function __construct( EmailTemplateRepository $email_template_repository ) {
		$this->email_template_repository = $email_template_repository;
	}

	public function hooks(): void {
		add_filter( 'fc/field/email-template-list/options', [ $this, 'get_options' ] );
	}

	/**
	 * @return array<string, string>
	 */
	public function get_options(): array {
		$all_posts = $this->email_template_repository->get_raw_posts();

		return array_reduce(
			$all_posts,
			function ( array $carry, WP_Post $post ) {
				$carry[ $post->ID ] = $post->post_title;
				return $carry;
			},
			[ '' => __( '— Select —', 'flexible-coupons-sending' ) ]
		);
	}
}
