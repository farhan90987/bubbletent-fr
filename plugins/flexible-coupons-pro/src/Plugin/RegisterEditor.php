<?php
/**
 * Integration. Coupon editor implementation.
 *
 * @package WPDesk\FlexibleCouponsPro
 */

namespace WPDesk\FlexibleCouponsPro;

use FlexibleCouponsProVendor\WPDesk\Library\WPCanvaEditor\EditorImplementation;

/**
 * @package WPDesk\FlexibleCouponsPro
 */
class RegisterEditor extends EditorImplementation {

	const EDITOR_POST_TYPE = 'wpdesk-coupons';

	public function __construct() {
		parent::__construct( self::EDITOR_POST_TYPE );
	}

	/**
	 * Define arguments for editor post type.
	 *
	 * @return array
	 */
	public function post_type_args_definition() {
		$labels = [
			'name'               => __( 'PDF Coupons', 'flexible-coupons-pro' ),
			'singular_name'      => __( 'PDF Coupons', 'flexible-coupons-pro' ),
			'menu_name'          => __( 'PDF Coupons', 'flexible-coupons-pro' ),
			'name_admin_bar'     => __( 'PDF Coupons', 'flexible-coupons-pro' ),
			'add_new'            => __( 'Add New', 'flexible-coupons-pro' ),
			'add_new_item'       => __( 'Add New', 'flexible-coupons-pro' ),
			'new_item'           => __( 'New', 'flexible-coupons-pro' ),
			'edit_item'          => __( 'Edit', 'flexible-coupons-pro' ),
			'view_item'          => __( 'Views', 'flexible-coupons-pro' ),
			'all_items'          => __( 'Templates', 'flexible-coupons-pro' ),
			'search_items'       => __( 'Search', 'flexible-coupons-pro' ),
			'parent_item_colon'  => __( 'Parent:', 'flexible-coupons-pro' ),
			'not_found'          => __( 'No found.', 'flexible-coupons-pro' ),
			'not_found_in_trash' => __( 'No found in Trash.', 'flexible-coupons-pro' ),
		];
		$args   = [
			'labels'             => $labels,
			'description'        => __( 'Manage coupons templates.', 'flexible-coupons-pro' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'query_var'          => true,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'show_in_menu'       => true,
			'supports'           => [ 'title' ],
			'show_in_rest'       => false,
			'menu_icon'          => 'dashicons-tickets-alt',
		];

		return $args;
	}
}
