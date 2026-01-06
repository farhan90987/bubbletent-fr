<?php
namespace WPDesk\FCS\PostType;

use FCSVendor\WPDesk\PluginBuilder\Plugin\Hookable;

class EmailTemplate implements Hookable {
	public const POST_TYPE = 'fc_email_template';

	public const SUBJECT_META_KEY    = '_fc_subject';
	public const RECIPIENT_META_KEY  = '_fc_recipients';
	public const ENABLED_META_KEY    = '_fc_enabled';
	public const IS_DEFAULT_META_KEY = '_fc_is_default';

	public function hooks() {
		add_action( 'init', [ $this, 'register_post_type' ] );
	}

	public function register_post_type(): void {
		$labels = [
			'name'               => __( 'Email Templates', 'flexible-coupons-sending' ),
			'singular_name'      => __( 'Email Template', 'flexible-coupons-sending' ),
			'menu_name'          => __( 'Email Templates', 'flexible-coupons-sending' ),
			'name_admin_bar'     => __( 'Email Template', 'flexible-coupons-sending' ),
			'add_new'            => __( 'Add New', 'flexible-coupons-sending' ),
			'add_new_item'       => __( 'Add New Email Template', 'flexible-coupons-sending' ),
			'new_item'           => __( 'New Email Template', 'flexible-coupons-sending' ),
			'edit_item'          => __( 'Edit Email Template', 'flexible-coupons-sending' ),
			'view_item'          => __( 'View Email Template', 'flexible-coupons-sending' ),
			'all_items'          => __( 'All Email Templates', 'flexible-coupons-sending' ),
			'search_items'       => __( 'Search Email Templates', 'flexible-coupons-sending' ),
			'not_found'          => __( 'No email templates found.', 'flexible-coupons-sending' ),
			'not_found_in_trash' => __( 'No email templates found in Trash.', 'flexible-coupons-sending' ),
		];

		$args = [
			'labels'              => $labels,
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => false,
			'show_in_menu'        => false,
			'query_var'           => false,
			'rewrite'             => false,
			'capability_type'     => 'post',
			'has_archive'         => false,
			'hierarchical'        => false,
			'supports'            => [ 'title' ],
			'show_in_rest'        => false,
		];

		register_post_type( self::POST_TYPE, $args );
	}
}
