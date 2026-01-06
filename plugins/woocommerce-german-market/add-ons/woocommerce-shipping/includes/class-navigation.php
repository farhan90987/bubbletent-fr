<?php

namespace MarketPress\GermanMarket\Shipping;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

class Navigation {

	/**
	 * @acces public
	 *
	 * @var string
	 */
	public string $id;

	/**
	 * Class constructor.
	 */
	protected function __construct() {

		// Boxes repeatable.
		add_action( 'woocommerce_admin_field_boxes_repeatable', array( $this, 'boxes_repeatable' ) );
	}

	/**
	 * Update Package Boxes when saving options
	 *
	 * @wp-hook admin_init
	 *
	 * @param array $options
	 *
	 * @return void
	 */
	public function update_package_boxes_options( $options ) {

		$provider = Woocommerce_Shipping::get_instance()->get_provider_by_id( $this->id );

		if ( isset( $_POST[ 'submit_save_wgm_options' ] ) ) {
			if ( wp_verify_nonce( $_POST[ 'update_wgm_settings' ], 'woocommerce_de_update_wgm_settings' ) ) {
				if ( isset( $_POST[ 'wgm_' . $this->id . '_packaging_boxes_settings' ] ) ) {
					if ( isset( $_POST[ 'boxes' ] ) ) {
						$provider::$options->update_option( 'package_boxes', $_POST[ 'boxes' ] );
					} else {
						$provider::$options->update_option( 'package_boxes', array() );
					}
				}
			}
		}
	}

	/**
	 * Output type package boxes repeater fields.
	 *
	 * @Hook woocommerce_admin_field_boxes_repeatable
	 *
	 * @return void
	 */
	public function boxes_repeatable() {

		$url = parse_url( $_SERVER[ 'REQUEST_URI' ] );
		parse_str($url[ 'query' ], $url_params );

		if ( $url_params[ 'tab' ] != 'wgm-shipping-' . $this->id ) {
			return;
		}

		$provider = Woocommerce_Shipping::get_instance()->get_provider_by_id( $this->id );
		$boxes    = $provider::$options->get_option( 'package_boxes' );

		?>
		<div class="boxes-repeater package-boxes-<?php echo $this->id; ?>" style="margin-top: 1rem;">
			<input type="hidden" name="wgm_<?php echo $this->id; ?>_packaging_boxes_settings" value="1" />
			<div data-repeater-list="boxes">
				<?php if ( ! empty( $boxes ) ) : ?>
					<?php foreach( $boxes as $key => $box ) : ?>
						<div data-repeater-item style="margin: .25em 0;">
							<input type="text" name="name" placeholder="<?php esc_attr_e( 'Box Name / Reference', 'woocommerce-german-market' ); ?>" class="form-control" value="<?php echo $box[ 'name' ]; ?>" style="width: 20%;" required>
							<input type="text" name="outer_width" placeholder="<?php esc_attr_e( 'Outer Width in mm', 'woocommerce-german-market' ); ?>" class="form-control" value="<?php echo $box[ 'outer_width' ]; ?>" style="width: 10%;" required>
							<input type="text" name="outer_length" placeholder="<?php esc_attr_e( 'Outer Length in mm', 'woocommerce-german-market' ); ?>" class="form-control" value="<?php echo $box[ 'outer_length' ]; ?>" style="width: 10%;" required>
							<input type="text" name="outer_depth" placeholder="<?php esc_attr_e( 'Outer Depth in mm', 'woocommerce-german-market' ); ?>" class="form-control" value="<?php echo $box[ 'outer_depth' ]; ?>" style="width: 10%;" required>
							<input type="text" name="empty_weight" placeholder="<?php esc_attr_e( 'Empty Weight in g', 'woocommerce-german-market' ); ?>" class="form-control" value="<?php echo $box[ 'empty_weight' ]; ?>" style="width: 10%;" required>
							<input type="text" name="max_weight" placeholder="<?php esc_attr_e( 'Max Weight in g', 'woocommerce-german-market' ); ?>" class="form-control" value="<?php echo $box[ 'max_weight' ]; ?>" style="width: 10%;">
							<button data-repeater-delete type="button" data-optionkey="<?php echo $key; ?>" class="save-wgm-options" style="margin-left: 1em;"><?php _e( 'Delete', 'woocommerce-german-market' ); ?></button>
						</div>
					<?php endforeach; ?>
				<?php else : ?>
					<div data-repeater-item style="margin: .25em 0;">
						<input type="text" name="name" placeholder="<?php esc_attr_e( 'Box Name / Reference', 'woocommerce-german-market' ); ?>" class="form-control" style="width: 20%;" required>
						<input type="text" name="outer_width" placeholder="<?php esc_attr_e( 'Outer Width in mm', 'woocommerce-german-market' ); ?>" class="form-control" style="width: 10%;" required>
						<input type="text" name="outer_length" placeholder="<?php esc_attr_e( 'Outer Length in mm', 'woocommerce-german-market' ); ?>" class="form-control" style="width: 10%;" required>
						<input type="text" name="outer_depth" placeholder="<?php esc_attr_e( 'Outer Depth in mm', 'woocommerce-german-market' ); ?>" class="form-control" style="width: 10%;" required>
						<input type="text" name="empty_weight" placeholder="<?php esc_attr_e( 'Empty Weight in g', 'woocommerce-german-market' ); ?>" class="form-control" style="width: 10%;" required>
						<input type="text" name="max_weight" placeholder="<?php esc_attr_e( 'Max Weight in g', 'woocommerce-german-market' ); ?>" class="form-control" style="width: 10%;">
						<input data-repeater-delete type="button" value="<?php esc_attr_e( 'Delete', 'woocommerce-german-market' ); ?>" class="save-wgm-options" style="margin-left: 1em;">
					</div>
				<?php endif; ?>
			</div>
			<input data-repeater-create type="button" value="<?php esc_attr_e( 'Add', 'woocommerce-german-market' ); ?>" class="save-wgm-options" style="margin-top: 1em;">
		</div>
		<?php
	}

}
