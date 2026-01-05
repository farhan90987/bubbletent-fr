<?php
/**
 * General settings.
 *
 * @package WPDesk\FlexibleCouponsPro
 */

namespace WPDesk\FlexibleCouponsPro\Coupons;

use FlexibleCouponsProVendor\WPDesk\PluginBuilder\Plugin\Hookable;

/**
 * Define pro fields for general settings.
 *
 * @package WPDesk\FlexibleCouponsPro
 */
class AdminPage implements Hookable {

	private const COUPON_ORDER_COLUMN   = 'fcpdf';
	private const COUPON_PRODUCT_COLUMN = 'fcproduct';


	public function hooks() {
		add_filter( 'woocommerce_coupon_discount_types', [ $this, 'coupon_discount_types' ] );
		add_filter( 'manage_shop_coupon_posts_columns', [ $this, 'set_custom_shop_coupon_columns' ], 999 );
		add_action( 'manage_shop_coupon_posts_custom_column', [ $this, 'custom_shop_coupon_columns' ], 999, 2 );
		add_action( 'pre_get_posts', [ $this, 'filter_coupons' ], 999, 2 );
		add_filter( 'post_row_actions', [ $this, 'post_row_actions' ], 100, 2 );
		add_filter( 'init', [ $this, 'clone_template' ], 999 );
	}

	private function types(): array {
		$types['only_fcpdf']    = __( 'Show only Flexible Coupons', 'flexible-coupons-pro' );
		$types['without_fcpdf'] = __( 'Show only WooCommerce Coupons', 'flexible-coupons-pro' );

		return $types;
	}

	/**
	 * @param array $types
	 *
	 * @return array
	 */
	public function coupon_discount_types( array $types ): array {
		global $current_screen;

		if ( $current_screen && $current_screen->id === 'edit-shop_coupon' ) {
			return array_merge( $types, $this->types() );
		}

		return $types;
	}

	/**
	 * @param array $columns
	 *
	 * @return array
	 */
	public function set_custom_shop_coupon_columns( array $columns ): array {
		$columns[ self::COUPON_ORDER_COLUMN ]   = __( 'Flexible Coupon', 'flexible-coupons-pro' );
		$columns[ self::COUPON_PRODUCT_COLUMN ] = __( 'Product creating coupon', 'flexible-coupons-pro' );

		return $columns;
	}

	/**
	 * @param string $column
	 * @param int    $post_id
	 *
	 * @return void
	 */
	public function custom_shop_coupon_columns( string $column, int $post_id ) {
		$data = get_post_meta( $post_id, '_fcpdf_coupon_data', true );
		if ( ! is_array( $data ) || empty( $data ) ) {
			return;
		}

		if ( $column === self::COUPON_ORDER_COLUMN ) {
			$this->create_coupon_order_column( $data );
		}

		if ( $column === self::COUPON_PRODUCT_COLUMN ) {
			$this->create_coupon_product_column( $data );
		}
	}

	//@phpstan-ignore-next-line
	private function create_coupon_order_column( array $data ): void {
		$order_id = $data['order_id'] ?? 0;
		$order    = wc_get_order( $order_id );
		if ( $order && $order->get_id() ) {
			echo '<a href="' . \esc_url( \admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' ) ) . '">' .
				sprintf(
				/* translators: %s - order number */
					esc_html__( 'Order ID: %s', 'flexible-coupons-pro' ),
					esc_html( $order->get_order_number() )
				) .
				'</a>';
		}
	}

	//@phpstan-ignore-next-line
	private function create_coupon_product_column( array $data ): void {
		$product_id = $data['product_id'];
		$product    = wc_get_product( $product_id );

		if ( ! $product ) {
			return;
		}

		if ( $product->is_type( 'variation' ) ) {
			$product_id = $product->get_parent_id();
		}

		$product_url = admin_url( 'post.php?post=' . $product_id . '&action=edit' );

		echo '<a href="' . esc_url( $product_url ) . '">' . esc_html( $product->get_title() ) . '</a>';
	}

	public function filter_coupons( \WP_Query $query ) {
		if ( $query->is_main_query() && $query->is_admin ) {
			$type  = isset( $_GET['coupon_type'] ) ? \sanitize_text_field( \wp_unslash( $_GET['coupon_type'] ) ) : '';
			$types = $this->types();
			if ( isset( $types[ $type ] ) ) {
				$query->set( 'meta_key', '' );
				$query->set( 'meta_value', '' );

				switch ( $type ) {
					case 'only_fcpdf':
						$meta_query = [
							[
								'key'     => '_fcpdf_coupon_data',
								'compare' => 'EXISTS',
							],
						];
						$query->set( 'meta_query', $meta_query );
						break;

					case 'without_fcpdf':
						$meta_query = [
							[
								'key'     => '_fcpdf_coupon_data',
								'compare' => 'NOT EXISTS',
							],
						];
						$query->set( 'meta_query', $meta_query );
						break;

					case 'used_fcpdf':
						$query->set( 'meta_key', '_fcpdf_coupon_data' );
						break;

					case 'unused_fcpdf':
						$query->set( 'meta_key', '_fcpdf_coupon_data' );
						break;
				}
			}
		}
	}

	/**
	 * @param array    $actions
	 * @param \WP_Post $post
	 *
	 * @return array
	 */
	public function post_row_actions( $actions, $post ): array {
		if ( ! $post instanceof \WP_Post ) {
			return $actions;
		}

		if ( $post->post_type !== 'wpdesk-coupons' ) {
			return $actions;
		}

		$new_actions = [];
		$copy_url    = wp_nonce_url( admin_url( 'admin.php?copy=yes&post_id=' . $post->ID . '' ), 'fc-clone-template-' . $post->ID );
		$copy_link   = '<a href="' . $copy_url . '">' . esc_html__( 'Copy', 'flexible-coupons-pro' ) . '</a>';
		foreach ( $actions as $action_id => $action ) {
			$new_actions[ $action_id ] = $action;
			if ( $action_id === 'edit' ) {
				$new_actions['fc_copy'] = $copy_link;
			}
		}

		return $new_actions;
	}

	public function clone_template() {
		$request     = wp_unslash( $_REQUEST );
		$copy        = $request['copy'] ?? '';
		$post_id     = $request['post_id'] ?? '';
		$nonce_value = $request['_wpnonce'] ?? '';
		if ( $copy === 'yes' && wp_verify_nonce( $nonce_value, 'fc-clone-template-' . $post_id ) ) {
			$copied_post = get_post( $post_id );
			if ( ! $copied_post ) {
				wp_safe_redirect( admin_url( 'edit.php?post_type=wpdesk-coupons' ) );
				exit;
			}

			$post        = [
				'post_title'  => sprintf( esc_html__( 'Copy of %s', 'flexible-coupons-pro' ), get_the_title( $post_id ) ),
				'post_status' => 'draft',
				'post_type'   => $copied_post->post_type,
				'post_author' => $copied_post->post_author,
			];
			$new_post_id = wp_insert_post( $post );

			global $wpdb;
			$metas = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->postmeta}` WHERE `post_id` =  %s", $post_id ) );
			foreach ( $metas as $meta ) {
				if ( $meta->meta_key === '_edit_lock' || $meta->meta_key === '_wp_old_slug' ) {
					continue;
				}
				$wpdb->insert(
					$wpdb->postmeta,
					[
						'post_id'    => $new_post_id,
						'meta_key'   => $meta->meta_key,
						'meta_value' => $meta->meta_value,
					],
					[ '%d', '%s', '%s' ]
				);
			}

			wp_safe_redirect( admin_url( 'post.php?post=' . $new_post_id . '&action=edit' ) );
			exit;
		}
	}
}
