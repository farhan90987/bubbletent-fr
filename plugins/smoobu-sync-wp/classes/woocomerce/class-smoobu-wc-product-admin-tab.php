<?php
/**
 * Class for WooCommerce Buy Now Product Admin fields
 *
 * @package     smoobu-calendar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Smoobu_WC_Product_Admin_Tab' ) ) {

	/**
	 * Class for WooCommerce Buy Now Product Admin Fields
	 */
	class Smoobu_WC_Product_Admin_Tab {

		/**
		 * Variable to hold instance of Buy Now Product Admin Fields
		 *
		 * @var $instance
		 * @since 2.4.1
		 */
		private static $instance = null;

		/**
		 * Constructor
		 */
		public function __construct() {

			add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_smoobu_property_details' ) );

			add_action( 'woocommerce_product_data_panels', array( $this, 'property_product_data_fields' ) );

			add_action( 'woocommerce_process_product_meta', array( $this, 'save_property_details_product_data' ), 10, 2 );

			// Add custom fields to WooCommerce settings.
			// add_filter( 'woocommerce_general_settings', array( $this, 'add_payment_instructions_cancellation_policy' ) );

			include_once SMOOBU_PATH . 'classes/class-smoobu-utility.php';

		}

		/**
		 * Get single instance of Buy Now Product Admin Fields
		 *
		 * @return Smoobu_WC_Product_Admin_Tab Singleton object of Smoobu_WC_Product_Admin_Tab
		 * @since 2.4.1
		 */
		public static function get_instance() {

			// Check if instance is already exists.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Handle call to functions which is not available in this class
		 *
		 * @param string $function_name Function name.
		 * @param array  $arguments Array of arguments passed.
		 * @return mixed result of function call
		 */
		public function __call( $function_name, $arguments = array() ) {

			global $wc_buy_now;

			if ( ! is_callable( array( $wc_buy_now, $function_name ) ) ) {
				return;
			}

			if ( ! empty( $arguments ) ) {
				return call_user_func_array( array( $wc_buy_now, $function_name ), $arguments );
			} else {
				return call_user_func( array( $wc_buy_now, $function_name ) );
			}

		}

		/**
		 * Function to add tab for Buy Now fields under product data
		 *
		 * @param array $product_data_tabs Product data tabs.
		 * @return array $product_data_tabs Product data tabs including Buy Now tabs.
		 */
		public function add_smoobu_property_details( $product_data_tabs = array() ) {

			$excluded_product_types = $this->get_excluded_product_types();
			$class                  = array();

			if ( count( $excluded_product_types ) > 0 ) {
				foreach ( $excluded_product_types as $product_type ) {
					$class[] = 'hide_if_' . $product_type; // hide buy now product tab if this product type is excluded.
				}
			}

			$product_data_tabs['smoobu-property-details'] = array(
				'label'    => __( 'Property Details', 'smoobu-calendar' ),
				'target'   => 'property_details_product_data',
				'class'    => $class,
				'priority' => 20,
			);

			return $product_data_tabs;
		}


		/**
		 * Display the custom fields in admin section of product creation page.
		 *
		 * @return void
		 */
		public function property_product_data_fields() {
			global $thepostid, $post, $woocommerce;
			
			$product = wc_get_product( $post->ID );

			if ( $product && $product->get_type() !== 'listing_booking' ) {
				return;
			}			

			// Add a nonce field.
			wp_nonce_field( 'save_custom_fields', 'custom_fields_nonce' );
			?>
			<div id="property_details_product_data" class="panel woocommerce_options_panel">
				<div class="options_group">
					<?php
					$args_property_id = array(
						'id'          => 'custom_property_id_field',
						'label'       => __( 'Mapped Property Id', 'smoobu-calendar' ),
						'class'       => 'st-custom-field',
						'desc_tip'    => 'true',
						'description' => __( 'property id from smoobu_property_details table to which this product is to be mapped.', 'smoobu-calendar' ),
					);
					woocommerce_wp_text_input( $args_property_id );

					$args_is_owned = array(
						'id'          => 'is_property_owned',
						'label'       => __( 'Owned Property', 'smoobu-calendar' ),
						'class'       => 'st-custom-field',
						'desc_tip'    => 'true',
						'description' => __( 'Check whether property is owned.', 'smoobu-calendar' ),
					);
					woocommerce_wp_checkbox( $args_is_owned );

					// Custom Product Field to store maximum number of adults allowed.
					$max_adults      = get_post_meta( $post->ID, 'max_adults', true );
					$max_adults      = ! empty( $max_adults ) ? $max_adults : 2;
					$args_max_adults = array(
						'id'                => 'max_adults',
						'placeholder'       => __( 'Maximum Adults', 'smoobu-calendar' ),
						'label'             => __( 'Maximum Number of Adults Allowed', 'smoobu-calendar' ),
						'type'              => 'number',
						'value'             => $max_adults,
						'custom_attributes' => array(
							'step' => 'any',
							'min'  => '0',
						),
					);
					woocommerce_wp_text_input( $args_max_adults );

					// Custom Product Field to store maximum number of kids allowed.
					$max_kids      = get_post_meta( $post->ID, 'max_kids', true );
					$max_kids      = ! empty( $max_kids ) ? $max_kids : 2;
					$args_max_kids = array(
						'id'                => 'max_kids',
						'placeholder'       => __( 'Maximum Kids', 'smoobu-calendar' ),
						'label'             => __( 'Maximum Number of Kids Allowed', 'smoobu-calendar' ),
						'type'              => 'number',
						'value'             => $max_kids,
						'custom_attributes' => array(
							'step' => 'any',
							'min'  => '0',
						),
					);
					woocommerce_wp_text_input( $args_max_kids );

					$is_property_owned = get_post_meta( $post->ID, 'is_property_owned', true );

					if ( 'yes' === $is_property_owned ) {
						$tax_on_room     = get_post_meta( $post->ID, 'tax_on_room', true );
						$tax_on_upgrades = get_post_meta( $post->ID, 'tax_on_upgrades', true );
						$tax_on_room     = ! isset( $tax_on_room ) ? $tax_on_room : 7;
						$tax_on_upgrades = ! isset( $tax_on_upgrades ) ? $tax_on_upgrades : 19;
					} else {
						$tax_on_room     = get_post_meta( $post->ID, 'tax_on_room', true );
						$tax_on_upgrades = get_post_meta( $post->ID, 'tax_on_upgrades', true );
						$tax_on_room     = ! isset( $tax_on_room ) ? $tax_on_room : 0;
						$tax_on_upgrades = ! isset( $tax_on_upgrades ) ? $tax_on_upgrades : 0;
					}

					$args_tax_on_room = array(
						'id'          => 'tax_on_room',
						'label'       => __( 'Tax On Room Tarrif ( % )', 'smoobu-calendar' ),
						'class'       => 'st-custom-field',
						'value'       => ( ! empty( $tax_on_room ) ? $tax_on_room : 7 ),
						'desc_tip'    => 'true',
						'description' => __( 'Please modify this value if the tax has changed to other than 7%.', 'smoobu-calendar' ),
					);
					woocommerce_wp_text_input( $args_tax_on_room );

					$args_tax_on_room = array(
						'id'          => 'tax_on_upgrades',
						'label'       => __( 'Tax On Upgrades ( % )', 'smoobu-calendar' ),
						'class'       => 'st-custom-field',
						'value'       => ( ! empty( $tax_on_upgrades ) ? $tax_on_upgrades : 19 ),
						'desc_tip'    => 'true',
						'description' => __( 'Please modify this value if the tax has changed to other than 19%.', 'smoobu-calendar' ),
					);
					woocommerce_wp_text_input( $args_tax_on_room );

					// Custom Product Field to store minimum days between booking.
					$args_min_days_to_book = array(
						'id'                => 'min_lead_time',
						'placeholder'       => __( 'Minimum Lead Time', 'smoobu-calendar' ),
						'label'             => __( 'Min. days between Booking and Arrival', 'smoobu-calendar' ),
						'type'              => 'number',
						'custom_attributes' => array(
							'step' => 'any',
							'min'  => '0',
						),
					);
					woocommerce_wp_text_input( $args_min_days_to_book );

					// Custom Product Field to capture extra charge per guest.
					$args_extra_charges = array(
						'id'                => 'extra_charges_per_guest',
						'placeholder'       => __( 'Extra guests / night', 'smoobu-calendar' ),
						'label'             => __( 'Extra guests / night', 'smoobu-calendar' ),
						'type'              => 'number',
						'custom_attributes' => array(
							'step' => 'any',
							'min'  => '0',
						),
					);
					woocommerce_wp_text_input( $args_extra_charges );

					// Custom Product Field to capture extra charge starting at.
					$args_extra_charges = array(
						'id'                => 'extra_charges_starting_at',
						'placeholder'       => __( 'Extra charges to start from', 'smoobu-calendar' ),
						'label'             => __( 'Extra charges to start from ( xth guest)', 'smoobu-calendar' ),
						'type'              => 'number',
						'custom_attributes' => array(
							'step' => 'any',
							'min'  => '0',
						),
					);
					woocommerce_wp_text_input( $args_extra_charges );

					// Custom Product Field to store maximum number of kids allowed.
					$checkin_starts_at      = get_post_meta( $post->ID, 'checkin_starts_at', true );
					$checkin_starts_at      = ! empty( $checkin_starts_at ) ? $checkin_starts_at : '15:00';
					$args_checkin_starts_at = array(
						'id'    => 'checkin_starts_at',
						'label' => __( 'Check in Starts At', 'smoobu-calendar' ),
						'type'  => 'text',
						'value' => $checkin_starts_at,
					);
					woocommerce_wp_text_input( $args_checkin_starts_at );

					// Cancellation Policy Instruction.
					$cancellation_policy      = get_post_meta( $post->ID, 'cancellation_policy', true );
					$cancellation_policy      = ! empty( $cancellation_policy ) ? $cancellation_policy : '';
					$args_cancellation_policy = array(
						'id'          => 'cancellation_policy',
						'placeholder' => __( 'Cancellation Policy', 'smoobu-calendar' ),
						'label'       => __( 'Cancellation Policy', 'smoobu-calendar' ),
						'type'        => 'textarea',
						'value'       => $cancellation_policy,
					);
					woocommerce_wp_textarea_input( $args_cancellation_policy );

					// Payment Instructions.
					$payment_instructions      = get_post_meta( $post->ID, 'payment_instructions', true );
					$payment_instructions      = ! empty( $payment_instructions ) ? $payment_instructions : '';
					$args_payment_instructions = array(
						'id'          => 'payment_instructions',
						'placeholder' => __( 'Payment Instructions', 'smoobu-calendar' ),
						'label'       => __( 'Payment Instructions', 'smoobu-calendar' ),
						'type'        => 'textarea',
						'value'       => $payment_instructions,
					);
					woocommerce_wp_textarea_input( $args_payment_instructions );

					// Add New Add On Button.
					$add_ons = get_post_meta( $post->ID, 'add_ons', true );
					?>
					<button type="button" id="add_new_addon_button">
						<?php esc_html_e( 'Add New Add On', 'smoobu-calendar' ); ?>
					</button>
					<div id="addon_container">
						<?php if ( ! empty( $add_ons ) ) : ?>
							<?php foreach ( $add_ons as $key => $add_on ) : ?>
								<?php $this->create_addon_display_block( $add_on, $key ); ?>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<?php

		}


		/**
		 * Create a block from the add on data passed to it.
		 *
		 * @param array $add_on Add on data.
		 * @param int   $key    index of the data.
		 * @return void
		 */
		private function create_addon_display_block( $add_on, $key ) {
			?>
			<fieldset id="addon_fieldset_<?php echo esc_attr( $key + 1 ); ?>" class="addon_fieldset">
				<div class="addon-input-container">
					<label for="addon_name_<?php echo esc_attr( $key + 1 ); ?>">
						<?php esc_html_e( 'Add On Name*', 'smoobu-calendar' ); ?>
					</label>
					<input
						type="text"
						id="addon_name_<?php echo esc_attr( $key + 1 ); ?>"
						name="addon_name_<?php echo esc_attr( $key + 1 ); ?>"
						value="<?php echo esc_attr( $add_on['name'] ); ?>"
						placeholder=<?php esc_html_e( 'Add On Name*', 'smoobu-calendar' ); ?>
					/>
				</div>
				<div class="addon-input-container">
					<label for="addon_description_<?php echo esc_attr( $key + 1 ); ?>">
						<?php esc_html_e( 'Add On Description', 'smoobu-calendar' ); ?>
					</label>
					<textarea
						id="addon_description_<?php echo esc_attr( $key + 1 ); ?>"
						name="addon_description_<?php echo esc_attr( $key + 1 ); ?>"
					><?php echo ( ! empty( $add_on['description'] ) ? esc_attr( $add_on['description'] ) : '' ); ?></textarea>
				</div>
				<div class="addon-input-container">
					<label for="addon_charges_<?php echo esc_attr( $key + 1 ); ?>">
						<?php esc_html_e( 'Add On Charges', 'smoobu-calendar' ); ?>
					</label>
					<input
						type="number"
						id="addon_charges_<?php echo esc_attr( $key + 1 ); ?>"
						name="addon_charges_<?php echo esc_attr( $key + 1 ); ?>"
						min="0"
						step=".01"
						value="<?php echo ( ! empty( $add_on['charges'] ) ? esc_attr( $add_on['charges'] ) : 0 ); ?>"
						placeholder=<?php esc_html_e( 'Add On Charges', 'smoobu-calendar' ); ?>
					/>
				</div>
				<div class="addon-image-container">
					<input
						type="hidden"
						name="addon_image_<?php echo esc_attr( $key + 1 ); ?>"
						<?php echo ( ! empty( $add_on['image'] ) ? 'value=' . esc_url_raw( $add_on['image'] ) : '' ); ?>
						class="addon_image_input">
					<button type="button" class="upload_image_button">
						<?php esc_html_e( 'Add-on Image', 'smoobu-calendar' ); ?>
					</button>
					<img
						class="addon_image_preview"
						src=<?php echo ( ! empty( $add_on['image'] ) ? esc_url_raw( $add_on['image'] ) : '' ); ?>
					>
				</div>
				<div class="addon-checkbox-container">
					<label for="per_person_<?php echo esc_attr( $key + 1 ); ?>">
						<?php esc_html_e( 'Per Person', 'smoobu-calendar' ); ?>
					</label>
					<input
						type="checkbox"
						id="per_person_<?php echo esc_attr( $key + 1 ); ?>"
						name="per_person_<?php echo esc_attr( $key + 1 ); ?>"
						<?php echo esc_attr( $add_on['is_per_person'] ? 'checked' : '' ); ?>
						id="per_person_<?php echo esc_attr( $key + 1 ); ?>"
					/>
				</div>
				<div class="addon-checkbox-container">
					<label for="include_per_kid_<?php echo esc_attr( $key + 1 ); ?>">
						<?php esc_html_e( 'Include Per Kid', 'smoobu-calendar' ); ?>
					</label>
					<input
						type="checkbox"
						id="include_per_kid_<?php echo esc_attr( $key + 1 ); ?>"
						name="include_per_kid_<?php echo esc_attr( $key + 1 ); ?>"
						<?php echo esc_attr( $add_on['is_per_person'] ? '' : 'disabled=true' ); ?>
						<?php echo esc_attr( $add_on['include_kids'] ? 'checked' : '' ); ?>
						id="include_per_kid_<?php echo esc_attr( $key + 1 ); ?>"
					/>
				</div>
				<div class="addon-checkbox-container">
					<label for="per_night_<?php echo esc_attr( $key + 1 ); ?>">
						<?php esc_html_e( 'Per Night', 'smoobu-calendar' ); ?>
					</label>
					<input
						type="checkbox"
						id="per_night_<?php echo esc_attr( $key + 1 ); ?>"
						name="per_night_<?php echo esc_attr( $key + 1 ); ?>"
						<?php echo esc_attr( $add_on['is_per_night'] ? 'checked' : '' ); ?>
						id="per_night_<?php echo esc_attr( $key + 1 ); ?>"
					/>
				</div>
				<button type="button" class="remove_addon_button">
					<?php esc_html_e( 'Remove', 'smoobu-calendar' ); ?>
				</button>
				<hr class="add-on-divider">
			</fieldset>
			<?php
		}

		/**
		 * Function to save buy now data in product
		 *
		 * @param  integer $post_id The post id.
		 * @param  WP_Post $post    Post object.
		 */
		public function save_property_details_product_data( $post_id = 0, $post = array() ) {

			// Verify nonce.
			if (
				! isset( $_POST['custom_fields_nonce'] ) ||
				! wp_verify_nonce( wc_clean( wp_unslash( $_POST['custom_fields_nonce'] ) ), 'save_custom_fields' ) //phpcs:ignore
			) {
				return;
			}

			$product = wc_get_product( $post_id );

			if ( $product && $product->get_type() !== 'listing_booking' ) {
				$product->save();
				return;
			}			

			// Save maximum number of adults and kids allowed.
			$max_adults = isset( $_POST['max_adults'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['max_adults'] ) ) ) : 2;
			$max_kids   = isset( $_POST['max_kids'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['max_kids'] ) ) ) : 2;

			$property_id_data = isset( $_POST['custom_property_id_field'] ) ?
					sanitize_text_field( wp_unslash( $_POST['custom_property_id_field'] ) ) :
					'';
			$is_owned_data    = isset( $_POST['is_property_owned'] ) ? 'yes' : 'no';
			if ( 'yes' === $is_owned_data ) {
				$tax_on_room_data     = isset( $_POST['tax_on_room'] ) ?
					sanitize_text_field( wp_unslash( $_POST['tax_on_room'] ) ) :
					7;
				$tax_on_upgrades_data = isset( $_POST['tax_on_upgrades'] ) ?
					sanitize_text_field( wp_unslash( $_POST['tax_on_upgrades'] ) ) :
					19;
			} else {
				$tax_on_room_data     = 0;
				$tax_on_upgrades_data = 0;
			}

			$min_lead_time = isset( $_POST['min_lead_time'] ) ?
				intval( sanitize_text_field( wp_unslash( $_POST['min_lead_time'] ) ) ) :
				0;

			$extra_charges_per_guest = isset( $_POST['extra_charges_per_guest'] ) ?
				intval( sanitize_text_field( wp_unslash( $_POST['extra_charges_per_guest'] ) ) ) :
				0;

			$charges_starts_at = isset( $_POST['extra_charges_starting_at'] ) ?
				intval( sanitize_text_field( wp_unslash( $_POST['extra_charges_starting_at'] ) ) ) :
				0;

			$checkin_starts_at = ( isset( $_POST['checkin_starts_at'] ) && $this->validate_time_format( wp_unslash( $_POST['checkin_starts_at'] ) ) )  ? //phpcs:ignore
				sanitize_text_field( wp_unslash( $_POST['checkin_starts_at'] ) ) :
				'00:00';

			$cancellation_policy = isset( $_POST['cancellation_policy'] ) ?
				sanitize_textarea_field( wp_unslash( $_POST['cancellation_policy'] ) ) :
				'';

			$payment_instructions = isset( $_POST['payment_instructions'] ) ?
				sanitize_textarea_field( wp_unslash( $_POST['payment_instructions'] ) ) :
				'';

			$product->update_meta_data( 'max_adults', $max_adults );
			$product->update_meta_data( 'max_kids', $max_kids );
			$product->update_meta_data( 'custom_property_id_field', $property_id_data );
			$product->update_meta_data( 'is_property_owned', $is_owned_data );
			$product->update_meta_data( 'tax_on_room', $tax_on_room_data );
			$product->update_meta_data( 'tax_on_upgrades', $tax_on_upgrades_data );
			$product->update_meta_data( 'min_lead_time', $min_lead_time );
			$product->update_meta_data( 'extra_charges_per_guest', $extra_charges_per_guest );
			$product->update_meta_data( 'extra_charges_starting_at', $charges_starts_at );
			$product->update_meta_data( 'checkin_starts_at', $checkin_starts_at );
			$product->update_meta_data( 'cancellation_policy', $cancellation_policy );
			$product->update_meta_data( 'payment_instructions', $payment_instructions );

			// Save add-on data.
			$add_ons = array();
			// Use a foreach loop to iterate through all available addon fields.
			foreach ( $_POST as $key => $value ) {
				if ( strpos( $key, 'addon_name_' ) === 0 ) {
					// Extract the unique identifier from the field name.
					$fieldset_id = str_replace( 'addon_name_', '', $key );

					// Handle new add-on fields.
					$addon_name        = isset( $_POST[ "addon_name_{$fieldset_id}" ] ) ?
						sanitize_text_field( wp_unslash( $_POST[ "addon_name_{$fieldset_id}" ] ) ) :
						array();
					$addon_description = isset( $_POST[ "addon_description_{$fieldset_id}" ] ) ?
						sanitize_textarea_field( wp_unslash( $_POST[ "addon_description_{$fieldset_id}" ] ) ) :
						array();
					$addon_charge      = isset( $_POST[ "addon_charges_{$fieldset_id}" ] ) ?
						floatval( $_POST[ "addon_charges_{$fieldset_id}" ] ) :
						0;
					$addon_image       = isset( $_POST[ "addon_image_{$fieldset_id}" ] ) ?
						esc_url_raw( wp_unslash( $_POST[ "addon_image_{$fieldset_id}" ] ) ) :
						'';
					$is_per_person     = isset( $_POST[ "per_person_{$fieldset_id}" ] ) ?
						filter_var( wp_unslash( $_POST[ "per_person_{$fieldset_id}" ] ), FILTER_VALIDATE_BOOLEAN ) :
						false;
					$include_kids      = isset( $_POST[ "include_per_kid_{$fieldset_id}" ] ) && $is_per_person ?
						filter_var( wp_unslash( $_POST[ "include_per_kid_{$fieldset_id}" ] ), FILTER_VALIDATE_BOOLEAN ) :
						false;
					$is_per_night      = isset( $_POST[ "per_night_{$fieldset_id}" ] ) ?
						filter_var( wp_unslash( $_POST[ "per_night_{$fieldset_id}" ] ), FILTER_VALIDATE_BOOLEAN ) :
						false;

					if ( ! $is_per_person && ! $include_kids && ! $is_per_night ) {
						$calculation_type = 0;
					} elseif ( $is_per_person && ! $include_kids && ! $is_per_night ) {
						$calculation_type = 1;
					} elseif ( ! $is_per_person && ! $include_kids && $is_per_night ) {
						$calculation_type = 2;
					} elseif ( $is_per_person && ! $include_kids && $is_per_night ) {
						$calculation_type = 3;
					} elseif ( $is_per_person && $include_kids && ! $is_per_night ) {
						$calculation_type = 4;
					} elseif ( $is_per_person && $include_kids && $is_per_night ) {
						$calculation_type = 5;
					}

					$add_on          = array(
						'name'             => $addon_name,
						'description'      => $addon_description,
						'charges'          => $addon_charge,
						'image'            => $addon_image,
						'is_per_person'    => $is_per_person,
						'include_kids'     => $include_kids,
						'is_per_night'     => $is_per_night,
						'calculation_type' => $calculation_type,
					);
					$add_ons[ $key ] = $add_on;
				}
			}

			// Arrange the $add_ons array in ascending order of keys.
			ksort( $add_ons );

			// Reindex the array to start from 0.
			$add_ons = array_values( $add_ons );

			$product->update_meta_data( 'add_ons', $add_ons );

			$product->save();

		}


		/**
		 * Checks whether the entered time is in HH:mm format.
		 *
		 * @param string $value the string to validate.
		 * @return bool
		 */
		private function validate_time_format( $value ) {
			return (bool) preg_match( '/^\d{2}:\d{2}$/', $value );
		}


		/**
		 * Adds Payment Policy and Custom Checkout Instruction.
		 *
		 * @param array $settings Settings instructions for WooCommerce.
		 * @return array
		 */
		public function add_payment_instructions_cancellation_policy( $settings ) {
			$settings[] = array(
				'title' => __( 'Checkout Page', 'smoobu-calendar' ),
				'desc'  => __( 'Customize information displayed on the checkout page.', 'smoobu-calendar' ),
				'type'  => 'title',
				'id'    => 'checkout_page_options',
			);

			$settings[] = array(
				'title'    => __( 'Cancellation Policy', 'smoobu-calendar' ),
				'desc'     => __( 'Enter the cancellation policy to display on the checkout page.', 'smoobu-calendar' ),
				'id'       => 'cancellation_policy',
				'type'     => 'textarea',
				'default'  => '',
				'desc_tip' => true,
			);

			$settings[] = array(
				'title'    => __( 'Payment Instructions', 'smoobu-calendar' ),
				'desc'     => __( 'Enter payment instructions to display on the checkout page.', 'smoobu-calendar' ),
				'id'       => 'payment_instructions',
				'type'     => 'textarea',
				'default'  => '',
				'desc_tip' => true,
			);

			$settings[] = array(
				'type' => 'sectionend',
				'id'   => 'checkout_page_options',
			);

			return $settings;
		}


		/**
		 * Get list of excluded product types.
		 *
		 * @return array
		 */
		public function get_excluded_product_types() {
			$product_type_details = wc_get_product_types();
			if ( empty( $product_type_details ) ) {
				return array();
			}
			$product_types          = array_keys( $product_type_details );
			$included_product_types = array( 'listing_booking' );
			$excluded_product_types = array_diff( $product_types, $included_product_types );

			return apply_filters( 'wc_bn_excluded_product_types', $excluded_product_types );
		}

		/**
		 * Checks if the valid product type is there in the cart.
		 *
		 * @return boolean
		 */
		/*private function has_required_product_type() {

			// Verify nonce.
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ), 'edit-product_' . get_the_ID() ) ) { // phpcs:ignore
				return false;
			}

			$product_type = isset( $_GET['product_type'] ) ?
				sanitize_text_field( wp_unslash( $_GET['product_type'] ) ) :
				'listing_booking';

			// Check if the product type is 'listing_booking', otherwise return the original $product_data_tabs.
			if ( 'listing_booking' !== $product_type ) {
				return true;
			} else {
				return false;
			}
		}*/

	}

}

Smoobu_WC_Product_Admin_Tab::get_instance();
