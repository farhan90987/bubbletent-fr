<?php
/**
 * Contains class Smoobu_Booking_From_Woo
 *
 * @package smoobu-calendar
 */

if (!defined('ABSPATH')) {
	die('No skiddies please!');
}

/**
 * Handles all functionalities required to customize woocomerce to create
 * single page booking, add additional fields in woocomerce checkout page,
 * and to finally make a booking in Smoobu.
 */
class Smoobu_Booking_From_Woo
{


	/**
	 * Class instance
	 *
	 * @var Smoobu_Booking_From_Woo
	 */
	protected static $instance = null;

	/**
	 * Main instance
	 *
	 * @return Smoobu_Booking_From_Woo
	 */
	public static function instance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class constructor
	 */
	public function __construct()
	{

		// Includes files required to add and manage fields in product admin tab.
		if (is_admin()) {
			include_once SMOOBU_PATH . 'classes/woocomerce/class-smoobu-wc-product-admin-tab.php';
		}

		include_once SMOOBU_PATH . 'classes/class-smoobu-utility.php';
		add_action('plugins_loaded', array($this, 'include_admin_files'));

		// Ajax call to remove cart item on cart abandonment.
		add_action('wp_ajax_nopriv_remove_product_on_checkout_abandonment', array($this, 'remove_product_on_checkout_abandonment'));
		add_action('wp_ajax_remove_product_on_checkout_abandonment', array($this, 'remove_product_on_checkout_abandonment'));

		// Adds the checkout page header.
		add_action('woocommerce_checkout_before_customer_details', array($this, 'display_checkout_page_header'));
		add_action('woocommerce_checkout_before_customer_details', array($this, 'add_calendar_and_guest_count_field'));

		// Customise existing billing fields and add new fields in the section.
		add_filter('woocommerce_checkout_fields', array($this, 'customize_billing_fields_and_section'));

		add_action('woocommerce_checkout_after_customer_details', array($this, 'add_additional_checkin_information'));

		// Adds the featured image of the page from which this request has been made.
		add_action('woocommerce_checkout_before_order_review', array($this, 'add_listing_page_featured_image'));

		// Filters out additional cart item date and its variations from order review section.
		add_filter('woocommerce_get_item_data', array($this, 'filter_cart_item_data'), 10, 2);

		// Change cart_item section presentation.
		add_filter('woocommerce_cart_item_name', array($this, 'modify_product_name_and_price_display'), 10, 3);
		add_filter('woocommerce_checkout_cart_item_quantity', array($this, 'add_item_quantity_hidden_field'), 9999, 3);

		add_action('woocommerce_review_order_after_cart_contents', array($this, 'add_addons_on_checkout_page'), 10);

		// Just hide default woocommerce coupon field.
		add_action('woocommerce_before_checkout_form', array($this, 'hide_checkout_coupon_form'), 5);

		// Add a custom coupon field before checkout payment section.
		add_action('woocommerce_review_order_before_order_total', array($this, 'add_custom_coupon_field'));

		// Remove Order Notes - WooCommerce Checkout.
		add_filter('woocommerce_enable_order_notes_field', '__return_false', 9999);

		add_action('woocommerce_after_checkout_form', array($this, 'append_smoobu_calendar_shortcode_output'), 10);

		// Removes Optional word from optional fields.
		add_filter('woocommerce_form_field', array($this, 'remove_optional_text_from_checkout_fields'), 10, 4);

		add_action('woocommerce_checkout_create_order', array($this, 'save_custom_fields_data'));

		add_filter('woocommerce_add_cart_item_data', array($this, 'save_get_data_in_cart_object'), 30, 3);

		add_action('woocommerce_checkout_update_order_review', array($this, 'update_checkout_quantity'));

		add_action('woocommerce_cart_calculate_fees', array($this, 'include_addon_service_fees'));
		// Can be removed as this functionality has been tackled in custom-checkout.scss.
		add_action('woocommerce_review_order_before_payment', array($this, 'remove_checkout_subtotal_display'));

		add_action('woocommerce_before_calculate_totals', array($this, 'reflect_cart_items_new_average_price'), 99, 1);

		// Add "Custom sub-menu" in menu item render output if menu item has class "wpml-ls-flag".
		add_filter('walker_nav_menu_start_el', array($this, 'append_get_parameters_menu_item'), 10, 4);

		add_action('woocommerce_checkout_process', array($this, 'validate_fields_and_smoobu_availability'));
		add_action('woocommerce_thankyou', array($this, 'register_the_order_in_smoobu'), 10, 1);

		// Change text of order received to replace product name with product price.
		// add_filter( 'woocommerce_order_item_name', array( $this, 'modify_item_name_on_thank_you_page' ), 10, 3 );

		add_filter('smoobu_set_calendar_busy_days', array($this, 'reflect_lead_time_for_booking_in_busy_days'));

		add_action('woocommerce_admin_order_items_after_line_items', array($this, 'display_custom_field_in_order_details'), 10, 1);
		add_action('woocommerce_email_after_order_table', array($this, 'display_custom_field_in_order_email'), 10, 4);
		add_action('woocommerce_order_status_changed', array($this, 'cancel_booking_on_wc_order_cancel'), 10, 3);

	}


	/**
	 * Removes cart content on abandonment for listing_booking
	 * product type
	 *
	 * @return void
	 */
	public function remove_product_on_checkout_abandonment()
	{
		// Replace 'your_product_category' with the actual product category slug.
		$product_category_to_remove = 'listing_booking';

		$cart = WC()->cart;
		$items = $cart->get_cart();

		foreach ($items as $key => $cart_item) {
			$product = $cart_item['data'];
			if ($product->is_type($product_category_to_remove)) {
				$cart->remove_cart_item($key);
				break;
			}
		}

		$cart->set_cart_contents($items); // Update the cart with removed items.
		wp_die(); // Important for proper AJAX response.
	}


	/**
	 * Displays product's meta on the checkout page.
	 *
	 * @return void
	 */
	public function display_checkout_page_header()
	{

		if (!is_checkout() || !$this->has_required_product_type()) {
			return;
		}

		$page_id = !empty($_GET['page-id']) ? // phpcs:ignore
			sanitize_text_field(wp_unslash($_GET['page-id'])) : // phpcs:ignore
			'#';
		$url = get_permalink($page_id);
		?>
		<div class="custom-product">
			<h1>
				<a href="<?php echo esc_url($url); ?>">
					<?php esc_html_e('< To the location page', 'smoobu-calendar'); ?>
				</a>
			</h1>
		</div>
		<?php
	}


	/**
	 * Add custom field to WooCommerce checkout page.
	 *
	 * @return void
	 */
	public function add_calendar_and_guest_count_field()
	{

		if (!is_checkout() || !$this->has_required_product_type()) {
			return;
		}

		$default_max_adults = 2; // Default value for max adults.
		$default_max_kids = 2; // Default value for max kids.
		$max_guests = 0;
		$extra_starts_at = 0;
		$max_adults = $default_max_adults;
		$max_kids = $default_max_kids;

		foreach (WC()->cart->get_cart() as $cart_item) {
			$product = $cart_item['data'];
			if ($product && $product->is_type('listing_booking')) {
				$value = intval($product->get_meta('custom_property_id_field'));
				$extra_starts_at = intval($product->get_meta('extra_charges_starting_at')) ?? 0;
				$max_adults = intval($product->get_meta('max_adults')) ?? $default_max_adults;
				$max_kids = intval($product->get_meta('max_kids')) ?? $default_max_kids;
				break;
			}
		}

		$max_guests = $this->get_max_guests_from_database($value);

		?>

		<div class="smoobu-custom-info-container">
			<h3 class="travel-date"><?php esc_html_e('Travel Data', 'smoobu-calendar'); ?></h3>
			<div class="smoobu-dates-selection-box">
				<div class="smoobu-date-entry-box">
					<label class="smoobu-date-entry-label" for="smoobu_calendar_start">
						<?php esc_html_e('Check-In', 'smoobu-calendar'); ?>
					</label>
					<input class="smoobu-calendar" type="text" id="smoobu_calendar_start" name="smoobu_calendar_start"
						placeholder="<?php esc_html_e('Check-In', 'smoobu-calendar'); ?>"
						value="<?php echo esc_attr(isset($_GET['start-date']) ? sanitize_text_field(wp_unslash($_GET['start-date'])) : ''); //phpcs:ignore ?>" />
				</div>
				<div class="smoobu-date-entry-box">
					<label class="smoobu-date-entry-label" for="smoobu_calendar_start">
						<?php esc_html_e('Check-Out', 'smoobu-calendar'); ?>
					</label>
					<input class="smoobu-calendar" type="text" id="smoobu_calendar_end" name="smoobu_calendar_end"
						placeholder="<?php esc_html_e('Check-Out', 'smoobu-calendar'); ?>"
						value="<?php echo esc_attr(isset($_GET['end-date']) ? sanitize_text_field(wp_unslash($_GET['end-date'])) : ''); //phpcs:ignore ?>" />
				</div>
			</div>
			<hr class="smoobu-section-divider" />
			<div id="guests-count-container" class="guests-count-container">
				<div class="guest-instruction-container">
					<span class="guest-number-text">
						<?php esc_html_e('Number of guests:', 'smoobu-calendar'); ?>
					</span>
					<!-- <span class="guest-instructions">
						<?php //esc_html_e( 'Maximum 2 adults + 2 children (up to 14 years)', 'smoobu-calendar' ); ?>
					</span> -->
				</div>
				<div class="guests-count-box">
					<div data-max-guest="<?php echo esc_attr($max_guests); ?>"
						data-free-till="<?php echo esc_attr($extra_starts_at); ?>" class="smoobu-guest-entry-box">
						<label for="_number_of_adults">
							<?php esc_html_e('Adults', 'smoobu-calendar'); ?>
						</label>
						<select id="_number_of_adults" name="_number_of_adults" class="smoobu-calendar-guests"
							data-max-adults="<?php echo esc_attr($max_adults); ?>">
							<?php 
							// old code
							//for ($x = 0; $x <= 2; $x++):
								// new code to allowed max adults defined in the backend 
							for ($x = 0; $x <= $max_adults; $x++): 
							?>
								<option value="<?php echo (!empty($x) ? esc_attr($x) : ''); ?>">
									<?php if (0 === $x): ?>
										<?php esc_html_e('Select', 'smoobu-calendar'); ?>
									<?php else: ?>
										<?php echo esc_html($x); ?>
									<?php endif; ?>
								</option>
							<?php endfor; ?>
							<!-- Add more options as needed -->
						</select>
					</div>
					<div class="smoobu-guest-entry-box">
						<label for="_number_of_kids">
							<?php esc_html_e('Kids', 'smoobu-calendar'); ?>
						</label>
						<select id="_number_of_kids" name="_number_of_kids" class="smoobu-calendar-guests"
							data-max-kids="<?php echo esc_attr($max_kids); ?>" disabled="true">
							<!-- Options for the second select field will be dynamically added using JavaScript -->
							<?php for ($x = 0; $x <= $max_kids; $x++): ?>
								<option value="<?php echo (!empty($x) ? esc_attr($x) : ''); ?>">
									<?php if (0 === $x): ?>
										<?php esc_html_e('Select', 'smoobu-calendar'); ?>
									<?php else: ?>
										<?php echo esc_html($x); ?>
									<?php endif; ?>
								</option>
							<?php endfor; ?>

						</select>
					</div>
				</div>
			</div>
		</div>
		<hr class="smoobu-section-divider" />
		<?php

	}

	/**
	 * Get max guests from database.
	 *
	 * @param int $value Property ID.
	 * @return int Max guests.
	 */
	private function get_max_guests_from_database($value)
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'smoobu_property_details';
		// phpcs:ignore
		$result = $wpdb->get_var(
			$wpdb->prepare('SELECT max_guests FROM %i WHERE property_id = %d', $table_name, $value) // phpcs:ignore
		);

		$return_value = isset($result[0]['max_guests']) ? intval($result[0]['max_guests']) : 0;
		return $return_value;
	}


	/**
	 * Modifies fields of checkout page for a given product type.
	 * Also, create a placeholder for property id.
	 *
	 * @param array $fields fields of the checkout field.
	 * @return array
	 */
	public function customize_billing_fields_and_section($fields)
	{

		if (!$this->has_required_product_type()) {
			return $fields;
		}

		// Hide fields that are not needed for the listing_booking product.
		unset($fields['billing']['billing_address_2']);
		unset($fields['billing']['billing_state']);
		unset($fields['billing']['billing_company']);

		// Modify field parameters to assist users of listing_booking product.
		$fields['order']['order_comments']['placeholder'] = __('Message to the host', 'smoobu-calendar');
		$fields['order']['order_comments']['label'] = __('Message to the host', 'smoobu-calendar');
		$fields['billing']['billing_address_1']['placeholder'] = __('Street/Number', 'smoobu-calendar');
		$fields['billing']['billing_country']['label'] = __('Country', 'smoobu-calendar');
		$fields['billing']['billing_email']['placeholder'] = __('Email', 'smoobu-calendar');
		$fields['billing']['billing_email']['label'] = __('Email', 'smoobu-calendar');
		$fields['billing']['billing_city']['placeholder'] = __('Town/City', 'smoobu-calendar');
		$fields['billing']['billing_postcode']['placeholder'] = __('Postal code', 'smoobu-calendar');
		$fields['billing']['billing_postcode']['label'] = __('Postal code', 'smoobu-calendar');
		$fields['billing']['billing_email']['priority'] = 35;
		$fields['billing']['billing_phone']['priority'] = 36;
		$fields['billing']['billing_city']['priority'] = 91;
		$fields['billing']['billing_country']['priority'] = 50;
		$fields['billing']['billing_address_1']['priority'] = 92;

		// Adds property_id custom field.
		$fields['billing']['billing_property_id'] = array(
			'label' => __('Property ID', 'smoobu-calendar'), // Add custom field label.
			'placeholder' => __('1881955', 'smoobu-calendar'), // Add custom field placeholder.
			'required' => true, // if field is required or not.
			'clear' => false, // add clear or not.
			'type' => 'text', // add field type.
			'class' => array('property-id-container'),   // add class name.
			'priority' => 9, // Priority sorting option.
		);

		return $fields;
	}



	/**
	 * Adds input fields for additional checkin information like message to the
	 * host and checkin time to the checkout page.
	 *
	 * @return void
	 */
	public function add_additional_checkin_information()
	{

		if (!$this->has_required_product_type()) {
			return;
		}

		$cancellation_policy = '';
		$payment_instructions = '';

		// Loop through cart items and get meta data.
		foreach (WC()->cart->get_cart() as $cart_item) {
			$product_id = $cart_item['data']->get_id(); // Get product ID from cart item.
			$product = wc_get_product($product_id); // Get product object.

			// Retrieve meta values from product.
			$checkin_starts_at = $product->get_meta('checkin_starts_at');
			$checkin_starts_at = !empty($checkin_starts_at) && preg_match('/^\d{2}:\d{2}$/', $checkin_starts_at) ?
				$checkin_starts_at :
				'15:00';
			$cancellation_policy .= esc_html($product->get_meta('cancellation_policy'));
			$payment_instructions .= esc_html($product->get_meta('payment_instructions'));
		}

		?>
		<div class="smoobu-addinfo-container">
			<p class="form-row notes" id="message_to_landlord_container" data-priority="">
				<label for="message_to_landlord" class="">
					<?php esc_html_e('Des précisions sur votre séjour ?', 'smoobu-calendar'); ?>
				</label>
				<span class="woocommerce-input-wrapper">
					<textarea name="message_to_landlord" class="input-text " id="message_to_landlord"
						placeholder="<?php echo esc_attr(__('Des précisions sur votre séjour ?', 'smoobu-calendar')); ?>" rows="2"
						cols="5"></textarea>
				</span>
			</p>
			<p class="form-row validate-required" id="smoobu_checkin_time_container" data-priority="100">
				<label for="smoobu_checkin_time" class="">
					<?php esc_html_e('Check-in (hh:mm): 15:00 hours or later', 'smoobu-calendar'); ?>
				</label>
				<span class="woocommerce-input-wrapper">
					<input type="text" class="input-text timepicker" name="smoobu_checkin_time" id="smoobu_checkin_time"
						placeholder="Check-in (hh:mm)" value="<?php echo esc_attr($checkin_starts_at); ?>"
						data-min-time="<?php echo esc_attr($checkin_starts_at); ?>"
						min="<?php echo esc_attr($checkin_starts_at); ?>" />
				</span>
			</p>
		</div>
		<hr class="smoobu-section-divider" />
		<div class="instructions-container">
			<h3><?php esc_html_e('Legal', 'smoobu-calendar'); ?></h3>
			<p class="cancellation-info-box">
				<span>
					<?php esc_html_e('Cancellation Information', 'smoobu-calendar'); ?>
				</span>
				<span><?php echo esc_html($cancellation_policy); ?></span>
			</p>
			<p class="payments-instruction-box">
				<span>
					<?php esc_html_e('Payment instructions', 'smoobu-calendar'); ?>
				</span>
				<span><?php echo esc_html($payment_instructions); ?></span>
			</p>
		</div>
		<?php

	}


	/**
	 * Displays the thumbnail of the page from which the request has
	 * been made just before order review..
	 *
	 * @return void
	 */
	public function add_listing_page_featured_image()
	{
		if (!is_checkout() || !$this->has_required_product_type() || empty($_GET['page-id'])) { //phpcs:ignore
			return;
		}

		//phpcs:ignore
		$page_id = intval(sanitize_text_field(wp_unslash($_GET['page-id'])));
		if (has_post_thumbnail($page_id)) {
			$url = get_the_post_thumbnail_url($page_id, 'medium');
			$title = get_the_title($page_id);
		}

		if (!empty($url) && !empty($title)):
			?>
			<div class="featured-product-image">
				<h3><?php echo esc_html($title); ?></h3>
				<img src="<?php echo esc_url($url); ?>" alt="" />
			</div>
			<?php
		endif;

	}


	/**
	 * Removes the additional items and variations from the display.
	 *
	 * @param array $cart_data Item data + variations for display on the frontend.
	 * @param array $cart_item Cart item object.
	 * @return array
	 */
	public function filter_cart_item_data($cart_data, $cart_item)
	{
		if (!$this->has_required_product_type()) {
			return $cart_data;
		}

		// Loop through cart item additional displayed data.
		foreach ($cart_data as $key => $values) {
			unset($cart_data[$key]);  // Remove attribute from the array.
		}

		return $cart_data;
	}


	/**
	 * Changes the name of the product.
	 *
	 * @param string $item_name      name of the product.
	 * @param array  $cart_item      cart product item details.
	 * @param string $cart_item_key  internal product identification keys for the cart items.
	 * @return string
	 */
	public function modify_product_name_and_price_display($item_name, $cart_item, $cart_item_key)
	{
		$product = $cart_item['data'];
		if (!$product->is_type('listing_booking')) {
			return $item_name;
		}

		$product = $cart_item['data'];
		$price = floatval($cart_item['custom_data']['prices']);

		$currency_symbol = get_woocommerce_currency_symbol();

		$unit_text = intval($cart_item['quantity']) <= 1 ? __('Night', 'smoobu-calendar') : __('Nights', 'smoobu-calendar');
		$item_name = sprintf(
			'%1$s%2$s x %3$d %4$s',
			$price,
			$currency_symbol,
			intval($cart_item['quantity']),
			$unit_text
		);
		return $item_name;
	}

	/**
	 * Adds quantity input beside product name and makes it hidden.
	 *
	 * @param integer $product_quantity current product quantity in the cart.
	 * @param array   $cart_item        cart product item details.
	 * @param string  $cart_item_key    internal product identification keys for the cart items.
	 * @return string
	 */
	public function add_item_quantity_hidden_field($product_quantity, $cart_item, $cart_item_key)
	{

		$product = $cart_item['data'];
		if (!$product->is_type('listing_booking')) {
			return $product_quantity;
		}

		$product = $cart_item['data'];
		$product_id = $cart_item['product_id'];

		$product_quantity = "<input
			name='shipping_method_qty_$product_id'
			value='{$cart_item['quantity']}'
			id='quantity_$cart_item_key'
			class='input-text qty text'
			style='display:none'
			/>";

		$product_quantity .= '<input type="hidden" name="product_key_' . $product_id . '" value="' . $cart_item_key . '">';
		return $product_quantity;
	}



	/**
	 * Display a checkbox along with name and price for each add-on.
	 *
	 * @return void
	 */
	public function add_addons_on_checkout_page()
	{

		if (!is_checkout() || !$this->has_required_product_type()) {
			return;
		}

		$add_on_list = $this->get_add_on_list();
		if (empty($add_on_list) || !is_array($add_on_list)) {
			return;
		}

		if (!empty($_POST['post_data'])) { // phpcs:ignore
			parse_str(
				sanitize_post(wp_unslash($_POST['post_data'])), // phpcs:ignore
				$post_data
			);
		}

		$currency = get_woocommerce_currency_symbol();
		?>
		<tr class="custom-content-row custom-content-row-header">
			<th class="optional-text" colspan="3">
				<?php esc_html_e('Optional', 'smoobu-calendar'); ?>
			</th>
		</tr>
		<?php foreach ($add_on_list as $key => $add_on): ?>
			<tr class="custom-content-row">
				<th colspan="2" class="custom-product-row">
					<?php
					$checkbox_value = (!empty($post_data) && isset($post_data['_custom_checkbox_' . $key])) ?
						$post_data['_custom_checkbox_' . $key] :
						0;
					woocommerce_form_field(
						'_custom_checkbox_' . $key,
						array(
							'type' => 'checkbox',
							'class' => array('form-row-wide'),
							'input_class' => array('add-on-checkbox'),
							'label' => $add_on['name'],
							'description' => $add_on['description'],
							'required' => false,
						),
						$checkbox_value
					);
					?>
					<!-- Displays the associated price field. -->
					<p><?php echo esc_html('+' . $add_on['charges'] . $currency); ?></p>
				</th>
				<?php if ($add_on['include_kids']): ?>
					<td class="addon-include-kids-container">
						<?php
						$include_kids = (!empty($post_data) && isset($post_data['_include_kids_checkbox_' . $key])) ?
							$post_data['_include_kids_checkbox_' . $key] :
							0;
						woocommerce_form_field(
							'_include_kids_checkbox_' . $key,
							array(
								'type' => 'checkbox',
								'class' => array('form-row-wide'),
								'input_class' => array('add-on-checkbox'),
								'label' => __('Include Kids', 'smoobu-calendar'),
								'description' => __('Include Kids', 'smoobu-calendar'),
								'required' => false,
							),
							$include_kids
						);
						?>
					</td>
				<?php endif; ?>
				<td class="addon-entry-container">
					<div class="addon-description">
						<?php echo esc_html($add_on['description']); ?>
					</div>
					<?php if (!empty($add_on['image'])): ?>
						<div class="addon-image-container">
							<img src="<?php echo esc_url_raw($add_on['image']); ?>" />
						</div>
					<?php endif; ?>
					<input type='hidden' name="_custom_addon_price_<?php echo esc_attr($key); ?>"
						value="<?php echo esc_attr($add_on['charges']); ?>" />
					<input type='hidden' name="_custom_addon_name_<?php echo esc_attr($key); ?>"
						value="<?php echo esc_attr($add_on['name']); ?>" />
					<input type='hidden' name="_custom_addon_type_<?php echo esc_attr($key); ?>"
						value="<?php echo esc_attr($add_on['calculation_type']); ?>" />
				</td>
			</tr>
		<?php endforeach; ?>
	<?php
	}

	/**
	 * Get the list of add-ons.
	 *
	 * @return array
	 */
	private function get_add_on_list()
	{
		foreach (WC()->cart->get_cart() as $cart_item) {
			$product = $cart_item['data'];
			if ($product->is_type('listing_booking')) {
				return $product->get_meta('add_ons');
			}
		}
		return array();
	}


	/**
	 * Just hide default woocommerce coupon field
	 *
	 * @return void
	 */
	public function hide_checkout_coupon_form()
	{
		if (!$this->has_required_product_type()) {
			return;
		}
		echo '<style>.woocommerce-form-coupon-toggle {display:none;}</style>';
	}


	/**
	 * Adds the coupoun field just above the total.
	 *
	 * @return void
	 */
	public function add_custom_coupon_field()
	{
		if (!$this->has_required_product_type()) {
			return;
		}
		?>
		<tr class="coupon-container">
			<th class="coupon-head-block">
				<div class="checkout-coupon-toggle">
					<div class="woocommerce-info">
						<?php esc_html_e('Have a coupon?', 'smoobu-calendar'); ?>
						<a href='#' class='show-coupon'>
							<?php esc_html_e('Click here to enter your code', 'smoobu-calendar'); ?>
						</a>
					</div>
				</div>
			</th>
			<td>
				<div class="smoobu-coupon-form" style="margin-bottom:20px; display: none">
					<p>
						<?php esc_html_e('If you have a coupon code, please apply it below.', 'smoobu-calendar'); ?>
					</p>
					<p class="form-row form-row-first woocommerce-validated">
						<input type="text" name="coupon_code1" class="input-text"
							placeholder="<?php esc_html_e('Coupon code', 'smoobu-calendar'); ?>" id="coupon_code1" value="">
					</p>
					<p class="form-row form-row-last">
						<button type="button" class="button" name="apply_coupon"
							value="<?php esc_html_e('Apply coupon', 'smoobu-calendar'); ?>">
							<?php esc_html_e('Apply coupon', 'smoobu-calendar'); ?>
						</button>
					</p>
					<div class="clear"></div>
				</div>
			</td>
		</tr>
		<?php
		$this->display_add_ons_and_charges();
	}


	/**
	 * Displays all the add-ons selected by the user.
	 *
	 * @return void
	 */
	public function display_add_ons_and_charges()
	{
		$add_on_info = array();
		$extra_charges = array();

		// Loop through cart items.
		foreach (WC()->cart->get_cart() as $cart_item) {
			// Check if item has add-ons (modify as needed based on your add-on structure).
			if (isset($cart_item['add_ons'])) {
				foreach ($cart_item['add_ons'] as $add_on) {
					$add_on_info[] = $add_on['name'];
				}
			}
		}

		// Loop through cart fees (optional, modify based on your extra charge structure).
		foreach (WC()->cart->get_fees() as $key => $fee) {
			if ('Shipping' !== $fee->name) { // Exclude shipping fee (modify as needed).
				$seperator_position = strrpos($fee->name, '__');
				$key_name = $seperator_position ?
					substr($fee->name, 0, $seperator_position) :
					substr($fee->name, 0);
				$extra_charges[] = array(
					'name' => $key_name, // ( strcmp( 'Extra Guest Charges', $fee->name ) === 0 ) ? __( 'Extra Guest Charges', 'smoobu-calendar' ) : substr( $fee->name, 0, strrpos( $fee->name, '__' ) ),
					'amount' => $fee->amount,
				);
			}
		}

		// Display add-on information and extra charges (modify formatting as needed).
		if (!empty($add_on_info)):
			?>
			<h3><?php esc_html_e('Add-Ons Selected:', 'smoobu-calendar'); ?></h3>
			<ul>
				<?php foreach ($add_on_info as $info): ?>
					<li><?php echo esc_html($info); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if (!empty($extra_charges)): ?>

			<?php foreach ($extra_charges as $extra): ?>
				<tr class="extra-details">
					<th><?php echo esc_html($extra['name']); ?></th>
					<td><?php echo esc_html($extra['amount'] . get_woocommerce_currency_symbol()); ?></td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
	<?php
	}


	/**
	 * Add privacy policy tick box at checkout
	 *
	 * @return void
	 */
	public function add_privacy_policy()
	{
		if (!$this->has_required_product_type()) {
			return;
		}
		$label_string = __(
			'Ich habe die
			<a href="https://book-a-bubble.de/allgemeine_geschaeftsbedingungen/">Allgemeinen Geschäftsbedingungen</a>, die
			<a href="https://book-a-bubble.de/datenschutz/">Datenschutzbestimmung</a> und die
			<a href="https://book-a-bubble.de/widerruf/">Widerrufsbelehrung</a> gelesen und akzeptiert.',
			'smoobu-calendar'
		);
		woocommerce_form_field(
			'privacy_policy',
			array(
				'type' => 'checkbox',
				'class' => array('form-row privacy'),
				'label_class' => array('woocommerce-form__label woocommerce-form__label-for-checkbox checkbox'),
				'input_class' => array('woocommerce-form__input woocommerce-form__input-checkbox input-checkbox'),
				'required' => true,
				'label' => $label_string,
			)
		);
	}


	/**
	 * Displays shortcode output on checkout page.
	 *
	 * @return void
	 */
	public function append_smoobu_calendar_shortcode_output()
	{
		if (!$this->has_required_product_type()) {
			return;
		}

		$custom_data = array_column(WC()->cart->get_cart(), 'custom_data');
		echo do_shortcode("[smoobu_calendar property_id='{$custom_data[0]['property_id']}' layout='1x1']");
	}



	/**
	 * Remove optional label from WooCommerce checkout page.
	 *
	 * @param string $field Field string.
	 * @param string $key   Field name and ID.
	 * @param array  $args  Array of field parameters.
	 * @param string $value Field value by default.
	 * @return string
	 */
	public function remove_optional_text_from_checkout_fields($field, $key, $args, $value)
	{
		if (!$this->has_required_product_type()) {
			return $field;
		}

		if (is_checkout() && !is_wc_endpoint_url()) {
			$optional = '&nbsp;<span class="optional">(' . esc_html__('optional', 'smoobu-calendar') . ')</span>';
			$field = str_replace($optional, '', $field);
		}
		return $field;
	}



	/**
	 * Save the custom field value to the order.
	 *
	 * @param object $order order object.
	 * @return void
	 */
	public function save_custom_fields_data($order)
	{

		if (!$this->has_required_product_type()) {
			error_log("Invalid product type in save_custom_fields_data");
			return;
		}

		$fields = array(
			'_number_of_adults' => '_number_of_adults',
			'_number_of_kids' => '_number_of_kids',
			'smoobu_calendar_start' => 'smoobu_calendar_start',
			'smoobu_calendar_end' => 'smoobu_calendar_end',
			'message_to_landlord' => 'message_to_landlord',
			'smoobu_checkin_time' => 'smoobu_checkin_time',
		);

		foreach ($fields as $post_key => $meta_key) {
			$alternative_value = in_array($post_key, array('_number_of_adults', '_number_of_kids'), true) ? 0 : '';
			$value = !empty($_POST[$post_key]) ? sanitize_text_field(wp_unslash($_POST[$post_key])) : $alternative_value;
			$order->update_meta_data($meta_key, $value);
		}

		if (!empty($_POST)) {
			foreach ($_POST as $key => $value) {
				if (
					strpos($key, '_custom_checkbox_') === 0 ||
					strpos($key, '_custom_addon_name_') === 0 ||
					strpos($key, '_custom_addon_price_') === 0 ||
					strpos($key, '_custom_addon_type_') === 0 ||
					strpos($key, '_include_kids_checkbox_') === 0
				) {
					$order->update_meta_data($key, sanitize_text_field(wp_unslash($value)));
				}
			}
		}

		// Copy billing address to shipping address.
		$order->set_shipping_first_name(method_exists($order, 'get_billing_first_name') ? $order->get_billing_first_name() : $order->billing_first_name);
		$order->set_shipping_last_name(method_exists($order, 'get_billing_last_name') ? $order->get_billing_last_name() : $order->billing_last_name);
		$order->set_shipping_address_1(method_exists($order, 'get_billing_address_1') ? $order->get_billing_address_1() : $order->billing_address_1);
		$order->set_shipping_address_2(method_exists($order, 'get_billing_address_2') ? $order->get_billing_address_2() : $order->billing_address_2);
		$order->set_shipping_city(method_exists($order, 'get_billing_city') ? $order->get_billing_city() : $order->billing_city);
		$order->set_shipping_state(method_exists($order, 'get_billing_state') ? $order->get_billing_state() : $order->billing_state);
		$order->set_shipping_postcode(method_exists($order, 'get_billing_postcode') ? $order->get_billing_postcode() : $order->billing_postcode);
		$order->set_shipping_country(method_exists($order, 'get_billing_country') ? $order->get_billing_country() : $order->billing_country);
	}


	/**
	 * Transfers additional data as part of custom cart data in the cart item.
	 *
	 * @param array   $cart_item_data Array of other cart item data.
	 * @param integer $product_id     ID of the product added to the cart.
	 * @param integer $variation_id   Variation ID of the product added to the cart.
	 *
	 * @return array
	 */
	public function save_get_data_in_cart_object($cart_item_data, $product_id, $variation_id)
	{

		$product = wc_get_product($product_id);
		if (!$product->is_type('listing_booking')) {
			return $cart_item_data;
		}

		// All the relevant data being send from the listing page.
		$get_params = array('prices', 'start-date', 'end-date', 'property_id');

		// Get the data from the GET request.
		foreach ($get_params as $param) {
			// phpcs:ignore
			$value = isset($_GET[$param]) ? sanitize_text_field(wp_unslash($_GET[$param])) : '';
			$cart_item_data['custom_data'][$param] = $value;
		}


		return $cart_item_data;
	}

	/**
	 * Detect Quantity Change and Recalculate Totals.
	 *
	 * @param string $post_data the changed data.
	 * @return void
	 */
	public function update_checkout_quantity($post_data)
	{
		if (!$this->has_required_product_type()) {
			return;
		}

		parse_str($post_data, $post_data_array);
		$updated_qty = false;
		foreach ($post_data_array as $key => $value) {
			if (strpos($key, 'shipping_method_qty_') === 0) {
				$id = substr($key, 20);
				WC()->cart->set_quantity($post_data_array['product_key_' . $id], $value, false);
				$updated_qty = true;
			}
		}

		if ($updated_qty) {
			WC()->cart->calculate_totals();
		}

	}

	/**
	 * Add fees for add on services.
	 *
	 * @param object $cart cart object for the particular order.
	 * @return void
	 */
	public function include_addon_service_fees($cart)
	{

		// Exit if not checkout, missing nonce, admin/ajax, or wrong product type.
		if (
			$this->is_admin_or_ajax() ||
			!$this->has_required_product_type()
		) {
			return;
		}

		$post_data = $this->calculate_post_data();

		if (empty($post_data) || !is_array($post_data)) {
			return;
		}

		foreach ($cart->get_cart() as $cart_item) {
			$product_id = $cart_item['data']->get_id();

			// Check if the product has the 'is_property_owned' meta field.
			$is_property_owned = get_post_meta($product_id, 'is_property_owned', true);
			$product = wc_get_product($product_id);
			if ($product->is_type('listing_booking')) {
				$value = intval($product->get_meta('custom_property_id_field'));
				$extra_charges_per_guest = intval($product->get_meta('extra_charges_per_guest'));
				$extra_starts_at = intval($product->get_meta('extra_charges_starting_at'));
				$quantity = $cart_item['quantity'];
				break;
			}
		}

		// Get discount amount based on coupon.

		/*
					A.
					$discount_percentage = 0;
					$coupons             = $cart->get_coupons();
					foreach ( $coupons as $code => $coupon ) {
						// Check if coupon is percentage discount.
						if ( $coupon->is_type( 'percent' ) ) {
							$discount_percentage = ( $coupon->get_amount() / 100 );
							break;
						}
					}
					*/

		// Select the addons that have been checked by the user.
		$selected_add_ons = array_filter(
			$post_data,
			function ($key) {
				return strpos($key, '_custom_checkbox_') === 0;
			},
			ARRAY_FILTER_USE_KEY
		);

		$adults = isset($post_data['_number_of_adults']) ?
			intval($post_data['_number_of_adults']) :
			0;
		$total_guests = $adults + (isset($post_data['_number_of_kids']) ? intval($post_data['_number_of_kids']) : 0);
		//new code don't need to this condition.
		// Check if the number of adults is more than 2.
		// if ($adults > 2) {
		// 	wc_add_notice(
		// 		__('Number of adults cannot be more than 2.', 'smoobu-calendar'),
		// 		'error'
		// 	);
		// 	return;
		// }

		// Checks whether the prices include taxes or is exclusive of taxes.
		$prices_include_tax = get_option('woocommerce_prices_include_tax');
		$taxable = ('yes' === $prices_include_tax) ? false : true;

		// Adds the extra guest fees based on the set value in the meta field ( extra_charges_starting_at )
		// not in Smoobu as we were having troule getting this extra guest charges from Smoobu.
		if (
			!empty($extra_charges_per_guest) &&
			!empty($extra_starts_at) &&
			$total_guests >= $extra_starts_at
		) {
			// $guest_fee = ( $total_guests - $extra_starts_at + 1 ) * $extra_charges_per_guest * ( 1 - $discount_percentage );
			$guest_fee = ($total_guests - $extra_starts_at + 1) * $extra_charges_per_guest * $quantity;
			$tax_class = ('no' === $is_property_owned) ? 'Zero rate' : 'Standard';
			$guest_key = __('Extra Guest Charges', 'smoobu-calendar');
			$cart->add_fee($guest_key, floatval($guest_fee), $taxable, $tax_class);
			// $cart->add_fee( 'Extra Guest Charges', floatval( $guest_fee ), $taxable, $tax_class );
		}

		if (!empty($selected_add_ons)) {
			foreach ($selected_add_ons as $key => $value) {
				$addon_key = substr(strrchr($key, '_'), 1);
				$add_on_price = floatval(sanitize_text_field($post_data['_custom_addon_price_' . $addon_key]));
				$add_on_name = sanitize_text_field($post_data['_custom_addon_name_' . $addon_key]);
				$add_on_type = sanitize_text_field($post_data['_custom_addon_type_' . $addon_key]);
				$addon_tax_class = ('no' === $is_property_owned) ? 'Zero rate' : 'Addon';

				switch (intval($add_on_type)) {
					case 0:
						$total_fee = floatval($add_on_price);
						$multiplier = 1;
						break;
					case 1:
						$total_fee = intval($adults) * floatval($add_on_price);
						$multiplier = intval($adults);
						break;
					case 2:
						$total_fee = floatval($quantity) * floatval($add_on_price);
						$multiplier = floatval($quantity);
						break;
					case 3:
						$total_fee = intval($adults) * floatval($quantity) * floatval($add_on_price);
						$multiplier = intval($adults) * floatval($quantity);
						break;
					case 4:
						$guest_count = isset($post_data['_include_kids_checkbox_' . $addon_key]) && $post_data['_include_kids_checkbox_' . $addon_key] ?
							intval($total_guests) :
							intval($adults);
						$total_fee = $guest_count * floatval($add_on_price);
						$multiplier = $guest_count;
						break;
					case 5:
						$guest_count = isset($post_data['_include_kids_checkbox_' . $addon_key]) && $post_data['_include_kids_checkbox_' . $addon_key] ?
							intval($total_guests) :
							intval($adults);
						$total_fee = $guest_count * floatval($quantity) * floatval($add_on_price);
						$multiplier = $guest_count * floatval($quantity);
						break;
				}

				// $total_fee *= ( 1 - $discount_percentage );

				// Add the fee to the cart with the appropriate tax class.
				//$cart->add_fee( $multiplier . ' x ' . $add_on_name . '__' . $addon_key, floatval( $total_fee ), ! $taxable, $addon_tax_class );
				$cart->add_fee($multiplier . ' x ' . $add_on_name, floatval($total_fee), !$taxable, $addon_tax_class);
			}

		}
	}


	/**
	 * Remove subtotal display on the WooCommerce checkout page.
	 *
	 * @return void
	 */
	public function remove_checkout_subtotal_display()
	{
		if (!$this->has_required_product_type()) {
			return;
		}
		// Output custom CSS to hide the subtotal element.
		echo '<style>
				.cart-subtotal { display: none !important; }
				.fee { display: none !important; }
				.tax-rate { display: none !important; }
			</style>';
	}


	/**
	 * Implement Dynamic pricing based on a selection.
	 *
	 * @param object $cart current cart item.
	 * @return void
	 */
	public function reflect_cart_items_new_average_price($cart)
	{

		if (
			$this->is_admin_or_ajax() ||
			empty($_POST) || 		//phpcs:ignore
			!$this->has_required_product_type() ||
			$this->has_called_calculate_totals_twice()
		) {
			return;
		}

		$this->update_tax_class($cart);
		$post_data = $this->calculate_post_data(); // phpcs:ignore
		$this->update_cart_item_prices_based_on_smoobu_calendar($cart, $post_data);
		$applied_coupons = WC()->cart->get_applied_coupons();
		if ($applied_coupons) {
			$this->update_fixed_cart_discount($cart, $applied_coupons);
		}

	}

	/**
	 * Check if the checkout nonce is invalid.
	 *
	 * @return bool
	 */
	private function is_checkout_nonce_invalid()
	{
		return (
			isset($_REQUEST['woocommerce-process-checkout-nonce']) &&
			!wp_verify_nonce(wc_clean(wp_unslash($_REQUEST['woocommerce-process-checkout-nonce'])), 'woocommerce-process_checkout') //phpcs:ignore
		) ||
			(
				isset($_POST['_wpnonce']) &&
				!wp_verify_nonce(wc_clean(wp_unslash($_POST['_wpnonce'])), 'woocommerce-process_checkout') //phpcs:ignore
			);
	}

	/**
	 * Check whether the particular action has been called more than twice.
	 *
	 * @return bool
	 */
	private function has_called_calculate_totals_twice()
	{
		return (did_action('woocommerce_before_calculate_totals') >= 2);
	}

	/**
	 * Update tax class for each cart item based on 'is_property_owned' meta field.
	 *
	 * @param object $cart current cart.
	 * @return void
	 */
	private function update_tax_class($cart)
	{
		foreach ($cart->get_cart() as $cart_item) {
			$product_id = $cart_item['product_id'];
			$is_property_owned = get_post_meta($product_id, 'is_property_owned', true);
			$cart_item['data']->set_tax_class('no' === $is_property_owned ? 'zero-rate' : '');
		}
	}

	/**
	 * Forms and returns the post_data object.
	 *
	 * @return array
	 */
	private function calculate_post_data()
	{
		$post_data = array();
		if (!empty($_POST['post_data'])) { // phpcs:ignore
			parse_str(
				sanitize_post(wp_unslash($_POST['post_data'])), // phpcs:ignore
				$post_data
			);
		} elseif (!empty($_POST)) { // phpcs:ignore
			$post_data = sanitize_post(wp_unslash($_POST)); // phpcs:ignore
		}
		return $post_data;
	}

	/**
	 * Update cart item prices based on Smoobu calendar data.
	 *
	 * @param object $cart      current cart.
	 * @param array  $post_data Additional information from checkout page.
	 * @return void
	 */
	private function update_cart_item_prices_based_on_smoobu_calendar($cart, $post_data)
	{
		// phpcs:ignore
		if (empty($post_data['smoobu_calendar_start']) || empty($post_data['smoobu_calendar_end']) || empty($post_data['billing_property_id'])) {
			return;
		}

		$start_day_string = sanitize_text_field(wp_unslash($post_data['smoobu_calendar_start'])); // phpcs:ignore
		$end_day_string = sanitize_text_field(wp_unslash($post_data['smoobu_calendar_end'])); // phpcs:ignore

		$end_day_string = strcmp($start_day_string, $end_day_string) === 0 ? $start_day_string . ' +1 day' : $end_day_string;

		$start_date = gmdate('Y-m-d', strtotime($start_day_string));
		$end_date = gmdate('Y-m-d', strtotime($end_day_string));
		$max_date = gmdate('Y-m-d', strtotime('+2 year'));
		$property_id = intval(sanitize_text_field(wp_unslash($post_data['billing_property_id']))); // phpcs:ignore

		if ($end_date > $max_date) {
			wc_add_notice(
				__('Bookings beyond a year from now are currently unavailable.', 'smoobu-calendar'),
				'error'
			);
		}

		foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
			$current_price = Smoobu_Utility::fetch_average_price($start_date, $end_date, $property_id);
			$cart->cart_contents[$cart_item_key]['data']->set_price($current_price);
			$cart->cart_contents[$cart_item_key]['custom_data']['prices'] = $current_price;
		}
	}

	/**
	 * Adds additional discount fees for fixed-cart coupon with value exceeding
	 * cart subtotal value.
	 *
	 * @param WC_Cart $cart            cart object for this session.
	 * @param array   $applied_coupons Array of applied coupons.
	 * @return void
	 */
	private function update_fixed_cart_discount($cart, $applied_coupons)
	{
		$discount_amount = 0;

		foreach ($applied_coupons as $coupon_code) {
			$coupon = new WC_Coupon($coupon_code);

			// Apply discount only for fixed cart coupons.
			if ($coupon->get_discount_type() === 'fixed_cart') {
				$discount_amount += $coupon->get_amount();
			}
		}

		if ($discount_amount <= 0) {
			return;
		}

		// Apply discount to the grand total, including taxes and fees.
		// Check if subtotal is less than discount.
		$subtotal = WC()->cart->get_subtotal();

		$subtotal_content = 0;
		foreach ($cart->cart_contents as $cart_item) {
			$subtotal_content += $cart_item['data']->get_price() * $cart_item['quantity'];
		}

		if (floatval($subtotal_content) < floatval($discount_amount)) {
			// Cap discount to subtotal and set discount as a negative fee.
			$discount_amount = ($subtotal_content - $discount_amount);
			$cart->add_fee(__('Additional Discount', 'smoobu-calendar'), ($discount_amount), false);
			$cart->set_discount_total($subtotal_content);
		} else {
			$cart->set_discount_total($discount_amount);
		}
	}


	/**
	 * Undocumented function
	 *
	 * @param string   $item_output The menu items starting HTML output.
	 * @param WP_Post  $item        Menu item data object.
	 * @param int      $depth       Depth of menu item. Used for padding.
	 * @param stdClass $args        An object of wp_nav_menu() arguments.
	 * @return string
	 */
	public function append_get_parameters_menu_item($item_output, $item, $depth, $args)
	{

		// Check if we are on the checkout page.
		if (
			is_checkout() &&
			$this->has_required_product_type() &&
			str_contains($item_output, 'class="wpml-ls-flag"')
		) {
			// Get the current URL with the existing GET parameters.
			$current_url = add_query_arg($_GET);

			// Get the URL of the menu item.
			$href_pos = strpos($item_output, 'href="') + 6;
			$href_end_pos = strpos($item_output, '"', $href_pos);
			$href = substr($item_output, $href_pos, $href_end_pos - $href_pos);

			// Append the current GET parameters to the menu item URL.
			$new_href = add_query_arg($_GET, $href);

			// Replace the original URL with the modified one.
			$item_output = str_replace('href="' . $href, 'href="' . esc_url($new_href), $item_output);
		}

		return $item_output;
	}


	/**
	 * Show notice if customer does not tick
	 *
	 * @return void
	 */
	public function validate_fields_and_smoobu_availability()
	{
		if (!$this->has_required_product_type()) {
			return;
		}

		// Loop through cart items and get meta data.
		foreach (WC()->cart->get_cart() as $cart_item) {
			$product_id = $cart_item['data']->get_id(); // Get product ID from cart item.
			$product = wc_get_product($product_id); // Get product object.

			// Retrieve meta values from product.
			$checkin_starts_at = $product->get_meta('checkin_starts_at');
			$checkin_starts_at = !empty($checkin_starts_at) && preg_match('/^\d{2}:\d{2}$/', $checkin_starts_at) ?
				$checkin_starts_at :
				'15:00';
		}

		$this->validate_and_add_notice(
			'_number_of_adults',
			__('Please enter number of guests.', 'smoobu-calendar')
		);

		// phpcs:ignore
		$start_date = isset($_POST['smoobu_calendar_start']) ?
			sanitize_text_field(wp_unslash($_POST['smoobu_calendar_start'])) : // phpcs:ignore
			'';
		// phpcs:ignore
		$end_date = isset($_POST['smoobu_calendar_end']) ?
			sanitize_text_field(wp_unslash($_POST['smoobu_calendar_end'])) : // phpcs:ignore
			'';

		// Validate the checkin checkout days.
		if (empty($start_date) || empty($end_date)) {
			wc_add_notice(
				__('Check-in/Checkout fields cannot be empty.', 'smoobu-calendar'),
				'error'
			);
		} elseif (strtotime($end_date) < strtotime($start_date)) {
			wc_add_notice(
				__('Checkin date cannot be after Checkout date.', 'smoobu-calendar'),
				'error'
			);
		} else {
			// phpcs:ignore
			$property_id = isset($_POST['billing_property_id']) ?
				sanitize_text_field(wp_unslash($_POST['billing_property_id'])) : // phpcs:ignore
				'';
			if (empty($property_id)) {
				wc_add_notice(
					__('Property Id is not set.', 'smoobu-calendar'),
					'error'
				);
			} else {
				$start_date = gmdate('Y-m-d', strtotime($start_date));
				$end_date = gmdate('Y-m-d', strtotime($end_date));
				$max_date = gmdate('Y-m-d', strtotime('+2 year'));
				$api_check_availability = new Smoobu_Api_Check_Availability();

				if ($end_date > $max_date) {
					wc_add_notice(
						__('Bookings beyond a year from now are currently unavailable.', 'smoobu-calendar'),
						'error'
					);
				}

				if (!$api_check_availability->check_availability($start_date, $end_date, $property_id)) {
					$calendar = new Smoobu_Calendar($property_id, '1x1');
					$calendar->run();
					wc_add_notice(
						__('There is a problem with the selected dates. Please reselect another date.', 'smoobu-calendar'),
						'error'
					);
				}
			}
		}

		// Validate check-in time (added logic).
		$checkin_time = isset($_POST['smoobu_checkin_time']) ? sanitize_text_field(wp_unslash($_POST['smoobu_checkin_time'])) : ''; // phpcs:ignore
		if (
			!empty($checkin_time) &&
			(preg_match('/^\d{2}:\d{2}$/', $checkin_time) === 1) &&
			strtotime($checkin_time) < strtotime($checkin_starts_at)
		) {
			wc_add_notice(
				// translators: %s is a placeholder for minimum checkin time.
				sprintf(__('Check-in time cannot be before %s hrs.', 'smoobu-calendar'), $checkin_starts_at),
				'error'
			);
		}

	}


	/**
	 * Checks absence of a custom field and adds the $message in notice to be
	 * displayed to the user.
	 *
	 * @param string $field_name Name of the custom field.
	 * @param string $message    Message to be displayed to the user.
	 * @return void
	 */
	private function validate_and_add_notice($field_name, $message)
	{
		// phpcs:ignore
		if (!isset($_POST[$field_name]) || empty($_POST[$field_name])) {
			wc_add_notice(
				$message,
				'error'
			);
		}
	}


	/**
	 * Make a reservation in Smoobu.
	 *
	 * @param integer $order_id id of the order just booked.
	 * @return void
	 */
	public function register_the_order_in_smoobu($order_id)
	{

		if (!$order_id || get_post_meta($order_id, '_thankyou_action_done', true)) {
			return;
		}

		$order = wc_get_order($order_id);
		$items = $order->get_items();

		$is_in_cart = false;
		$quantity = 0;
		$add_on_list = array();

		foreach ($items as $item) {
			// Get the product object.
			$product = $item->get_product();
			if ($product->is_type('listing_booking')) {
				$is_in_cart = true;
				$quantity = $item->get_quantity();
				$add_on_list = $product->get_meta('add_ons');
				break;
			}
		}

		if (!$is_in_cart) {
			return;
		}

		$this->make_booking_in_smoobu($order, $quantity, $add_on_list);
	}


	/**
	 * Captures the submitted field values. Would be used to book on Smoobu.
	 *
	 * @param WC_Order $order       $order booked order object.
	 * @param int      $quantity    quantity of items in the cart.
	 * @param array    $add_on_list list of addons configured for this product.
	 * @return void
	 */
	private function make_booking_in_smoobu($order, $quantity, $add_on_list)
	{

		$property_id = $order->get_meta('_billing_property_id');
		$departure_date = gmdate('Y-m-d', strtotime($order->get_meta('smoobu_calendar_end')));
		$arrival_date = gmdate('Y-m-d', strtotime($order->get_meta('smoobu_calendar_start')));

		$order_data = $order->get_data();
		$user_information = $order_data['billing'];

		// Constructing a string for Add on details to be added in the notes.


		$addon_details = '';
		$has_additional_services = false;

		foreach ($order->get_meta_data() as $meta) {
			if (strpos($meta->key, '_custom_checkbox_') === 0) {
				$has_additional_services = true;
				break;
			}
		}

		if ($has_additional_services) {

			$addon_details = '<br />Additional Services';
			foreach ($order->get_meta_data() as $meta) {
				if (strpos($meta->key, '_custom_checkbox_') === 0) {
					$add_on_id = intval(str_replace('_custom_checkbox_', '', $meta->key));
					$addon_name = $add_on_list[$add_on_id]['name'];
					$addon_details .= '<br />' . $addon_name;

					$addon_type = $order->get_meta('_custom_addon_type_' . $add_on_id);
					$number_of_night = $quantity;
					$should_include_kids = $order->get_meta('_include_kids_checkbox_' . $add_on_id);
					$number_of_persons = $should_include_kids ?
						intval($order->get_meta('_number_of_adults')) + intval($order->get_meta('_number_of_kids')) :
						intval($order->get_meta('_number_of_adults'));

					if (isset($addon_type)) {
						switch (intval($addon_type)) {
							case 0:
								$addon_details .= __(' is required.', 'smoobu-calendar');
								break;
							case 1:
							case 4:
								// Translators: Addon description.
								$addon_details .= sprintf(__(' is required for %s persons.', 'smoobu-calendar'), $number_of_persons);
								break;
							case 2:
								// Translators: Addon description with number of nights.
								$addon_details .= sprintf(__(' is required for %s nights.', 'smoobu-calendar'), $number_of_night);
								break;
							case 3:
							case 5:
							default:
								// Translators: Addon description with number of persons and nights.
								$addon_details .= sprintf(__(' is required for %1$s persons for %2$s nights.', 'smoobu-calendar'), $number_of_persons, $number_of_night);
								break;
						}
					} else {
						if (!empty($number_of_persons)) {
							// Translators: Addon description with number of persons.
							$addon_details .= '<br />' . sprintf(__(' is required for %s persons.', 'smoobu-calendar'), $number_of_persons);
						}
						if (!empty($number_of_night)) {
							// Translators: Addon description with number of nights.
							$addon_details .= '<br />' . sprintf(__(' is required for %s nights.', 'smoobu-calendar'), $number_of_night);
						}
					}
				}
			}
		}

		/* Adding coupon code to be added in the notes. */

		$coupons = $order->get_coupon_codes();

		$coupon_details = "";
		if ($coupons) {
			$coupon_details .= '<br />Bon de réduction: ';
			$coupon_details .= implode(', ', $coupons);
		}

		$data = array(
			'arrivalDate' => $arrival_date,
			'departureDate' => $departure_date,
			'channelId' => 70,
			'apartmentId' => intval($property_id),
			'arrivalTime' => $order->get_meta('smoobu_checkin_time'),
			'firstName' => $user_information['first_name'],
			'lastName' => $user_information['last_name'],
			'email' => $user_information['email'],
			'phone' => $user_information['phone'],
			'address' => array(
				'street' => $user_information['address_1'],
				'postalCode' => $user_information['postcode'],
				'location' => $user_information['city'],
			),
			'country' => $user_information['country'],
			'notice' => $order->get_meta('message_to_landlord') . $addon_details . $coupon_details,
			'adults' => $order->get_meta('_number_of_adults'),
			'children' => $order->get_meta('_number_of_kids'),
			'price' => $order_data['total'],
			'priceStatus' => $order->has_status('completed') ? 1 : 0,
		);

		// phpcs:ignore
		if (class_exists('Smoobu_Api_Booking')) {
			try {
				$response = Smoobu_Api_Booking::make_booking($data);

				if (isset($response['id'])) {
					$booking_id = $response['id'];
					update_post_meta($order->get_id(), '_smoobu_booking_id', $booking_id);
				} else {
					$error_message = Smoobu_Api_Booking::instance()->get_error();
					error_log('Smoobu Error details: ' . $error_message);
					wc_add_notice($error_message, 'error');
					error_log('Smoobu Data Request: ');
					error_log(print_r($data, true));
				}
			} catch (Exception $e) {
				error_log('Exception occurred while making booking: ' . $e->getMessage());
			}

		}
	}

	/**
	 * Cancel a reservation in Smoobu when WC order is cancelled.
	 *
	 * @return void
	 */

	public function cancel_booking_on_wc_order_cancel($order_id, $old_status, $new_status)
	{
		if ($new_status === 'cancelled') {
			$smoobu_booking_id = get_post_meta($order_id, '_smoobu_booking_id', true);
			if (!empty($smoobu_booking_id)) {
				if (class_exists('Smoobu_Api_Booking')) {
					$response = Smoobu_Api_Booking::cancel_booking($smoobu_booking_id);
				}
			}
		}
	}

	/**
	 * Converts product name and link to custom product name.
	 *
	 * @param string $name name of the string to be displayed on the order received page.
	 * @param object $item WC_Order_Item_Product object.
	 * @return string
	 */
	public function modify_item_name_on_thank_you_page($name, $item)
	{

		$product = $item->get_product();
		if (!$product->is_type('listing_booking')) {
			return $name;
		}

		// $name = ( intval( $item->get_subtotal() ) / intval( $item->get_quantity() ) ) . ' ' . get_woocommerce_currency_symbol() . ' ';

		return $name;
	}


	/**
	 * Adds the lead time days to the existing list of busy days.
	 *
	 * @param array $days days that the hotel is booked.
	 * @return array
	 */
	public function reflect_lead_time_for_booking_in_busy_days($days)
	{

		if (!is_plugin_active('woocommerce/woocommerce.php') || empty(WC()->cart)) {
			return $days;
		}

		foreach (WC()->cart->get_cart() as $cart_item) {
			$product = $cart_item['data'];
			if ($product->is_type('listing_booking')) {
				$lead_time = intval($product->get_meta('min_lead_time'));
				if ($lead_time > 0) {
					$lead_time_dates = array_map(
						function ($x) {
							return gmdate('Y-m-d', strtotime("+$x days"));
						},
						range(0, $lead_time)
					);

					$days = array_unique(array_merge($lead_time_dates, $days));
					sort($days);
					break; // No need to continue iteration once lead time is found.
				}
			}
		}

		return $days;
	}

	/**
	 * Creates an Entry in Order details table for the custom fields.
	 *
	 * @param int $order_id id of the current order.
	 * @return void
	 */
	public function display_custom_field_in_order_details($order_id)
	{
		$order = wc_get_order($order_id);

		$has_simple_product = false;

		foreach ($order->get_items() as $item) {
			$product_id = $item->get_product_id();
			$product = wc_get_product($product_id);

			if ($product && $product->is_type('listing_booking')) {
				$has_simple_product = true;
				break; // Exit the loop once a simple product is found.
			}
		}

		if (!$has_simple_product) {
			return;
		}

		$meta_keys = array(
			'smoobu_calendar_start' => __('Check-in Date', 'smoobu-calendar'),
			'smoobu_calendar_end' => __('Check-out Date', 'smoobu-calendar'),
			'smoobu_checkin_time' => __('Check-in time', 'smoobu-calendar'),
			'_number_of_adults' => __('Number of Adults', 'smoobu-calendar'),
			'_number_of_kids' => __('Number of Kids', 'smoobu-calendar'),
			'message_to_landlord' => __('Message To Landlord', 'smoobu-calendar'),
		);
		?>

		<?php foreach ($meta_keys as $key => $value): ?>
			<?php if ($order->get_meta($key)): ?>
				<tr class="item " data-order_item_id="141">
					<td class="thumb">
					</td>
					<td class="name" colspan="2">
						<?php echo $value; //phpcs:ignore ?>
					</td>
					<td class="item_cost" colspan="4">
						<?php echo esc_attr($order->get_meta($key)); ?>
					</td>
				</tr>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php
	}


	/**
	 * Adds additional fields to the new order email.
	 *
	 * @param WC_Order $order         Order instance.
	 * @param bool     $sent_to_admin If should sent to admin.
	 * @param bool     $plain_text    Set true to send as plain text. Default to false.
	 * @param string   $email         Email address.
	 * @return void
	 */
	public function display_custom_field_in_order_email($order, $sent_to_admin, $plain_text, $email)
	{

		$has_simple_product = false;

		foreach ($order->get_items() as $item) {
			$product_id = $item->get_product_id();
			$product = wc_get_product($product_id);

			if ($product && $product->is_type('listing_booking')) {
				$has_simple_product = true;
				break; // Exit the loop once a simple product is found.
			}
		}

		if (!$has_simple_product) {
			return;
		}

		$meta_keys = array(
			'smoobu_calendar_start' => __('Check-in Date', 'smoobu-calendar'),
			'smoobu_calendar_end' => __('Check-out Date', 'smoobu-calendar'),
			'_number_of_adults' => __('Number of Adults', 'smoobu-calendar'),
			'_number_of_kids' => __('Number of Kids', 'smoobu-calendar'),
		);
		?>
		<div>
			<h2 class="title-billing"
				style="color: inherit; display: block; font-size: 18px; font-weight: bold; line-height: 130%; margin: 0px 10px 18px 0px; font-family: inherit;">
				<?php esc_html_e('Additional Check-in Information', 'smoobu-calendar'); ?>
			</h2>
			<table class="yaymail-items-border-content" style="color: inherit; border-color: #E5E5E5; width: 100%;">
				<thead class="yaymail_element_head_order_item">
				</thead>
				<tbody class="yaymail_element_body_order_item">
				</tbody>
				<tfoot class="yaymail_element_foot_order_item">
					<?php foreach ($meta_keys as $key => $value): ?>
						<?php if ($order->get_meta($key)): ?>
							<tr class="yaymail_item_subtoltal_title_row">
								<th scope="row" colspan="2" class="td yaymail_item_subtoltal_title"
									style="text-align: left; font-weight: bold; vertical-align: middle; padding: 12px; font-size: 14px; border-style: solid; border-color: inherit; color: inherit;">
									<span>
										<font style="vertical-align: inherit;">
											<font style="vertical-align: inherit;">
												<?php echo $value; //phpcs:ignore ?>
											</font>
										</font>
									</span>
								</th>
								<th scope="row" colspan="1" class="td yaymail_item_subtoltal_content"
									style="font-weight: normal; text-align: left; vertical-align: middle; padding: 12px; font-size: 14px; border-style: solid; border-color: inherit; color: inherit;">
									<span class="woocommerce-Price-amount amount">
										<font style="vertical-align: inherit;">
											<font style="vertical-align: inherit;">
												<?php echo esc_attr($order->get_meta($key)); ?>
											</font>
										</font>
									</span>
								</th>
							</tr>
						<?php endif; ?>
					<?php endforeach; ?>
				</tfoot>
			</table>
		</div>
		<?php
	}


	/**
	 * Check if the current action is related to admin or ajax.
	 *
	 * @return bool
	 */
	private function is_admin_or_ajax()
	{
		return (is_admin() && !defined('DOING_AJAX'));
	}

	/**
	 * Checks if the valid product type is there in the cart.
	 *
	 * @param string $type type of the product that should be present.
	 * @return boolean
	 */
	private function has_required_product_type($type = 'listing_booking')
	{

		// Check if the cart exists.
		if (!WC()->cart) {
			return false;
		}

		// Get the cart contents.
		$cart_contents = WC()->cart->get_cart();

		// Check if the cart contents are valid.
		if (empty($cart_contents) || !is_array($cart_contents)) {
			return false;
		}

		$has_product_type = false;

		foreach ($cart_contents as $cart_item) {
			// Check if 'data' exists in the cart item and is a valid product.
			if (isset($cart_item['data']) && is_object($cart_item['data'])) {
				$product = $cart_item['data'];
				if (method_exists($product, 'is_type') && $product->is_type($type)) {
					$has_product_type = true;
					break;
				}
			}
		}

		return $has_product_type;
	}


	/**
	 * Solves the loading order issue which occurs when is_admin() starts to return true at a point after plugin
	 * load.
	 *
	 * @since 1.0.0
	 */
	public function include_admin_files()
	{

		if (is_admin() && !class_exists('Smoobu_WC_Product_Admin_Tab')) {

			include_once SMOOBU_PATH . 'classes/woocomerce/class-smoobu-wc-product-admin-tab.php';
		}
	}

}