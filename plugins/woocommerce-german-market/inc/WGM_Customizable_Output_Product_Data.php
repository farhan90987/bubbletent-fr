<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WGM_Customizable_Output_Product_Data
 *
 * @author MarketPress
 */
class WGM_Customizable_Output_Product_Data {

	/**
	 * @var WGM_Checkbox_Product_Depending
	 */
	private static $instance = null;

	public $default_includes = '';
	public $default_excludes = '';
	public $includes_option = '';
	public $excludes_option = '';
	public $options = array();
	public $sorted_schema = array();

	/**
	* Singletone get_instance
	*
	* @static
	* @return WGM_Checkbox_Product_Depending
	*/
	public static function get_instance() {
		if ( self::$instance == NULL) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	* Singletone constructor
	*
	* @access private
	*/
	private function __construct() {

		// Change tax prefix
		$this->default_includes = __( 'Includes', 'woocommerce-german-market' );
		$this->default_excludes = __( 'Plus', 'woocommerce-german-market' );
		$this->includes_option = esc_attr( trim( get_option( 'german_market_customizable_product_data_prefix_incl', $this->default_includes ) ) );
		$this->excludes_option = esc_attr( trim( get_option( 'german_market_customizable_product_data_prefix_excl', $this->default_excludes ) ) );

		if ( 
			( $this->default_includes !== $this->includes_option ) ||
			( $this->default_excludes !== $this->excludes_option )
		) {

			add_filter( 'wgm_checkout_add_tax_to_product_item', array( $this, 'change_tax_output_wording' ), 99 );
			add_filter( 'wgm_checkout_add_tax_to_order_product_item', array( $this, 'change_tax_output_wording' ), 99 );
			add_filter( 'wgm_get_totals_tax_string', array( $this, 'change_tax_output_wording' ), 99 );
			add_filter( 'wgm_get_excl_incl_tax_string', array( $this, 'change_tax_output_wording' ), 99 );
			add_filter( 'wgm_get_tax_line', array( $this, 'change_tax_output_wording' ), 99 );
			add_filter( 'gm_cart_column_heading_tax_incl', array( $this, 'change_tax_output_wording' ), 99 );
			add_filter( 'german_market_mini_cart_price_tax', array( $this, 'change_tax_output_wording' ), 99 );
		}

		// Disable automatic output of product data (except variations)
		if ( 'on' === get_option( 'german_market_customizable_product_data_disable_automatic_output', 'off' ) ) {
			add_action( 'german_market_after_init', array( $this, 'disable_automatic_output' ), 9999 );
		}

		// Disable automatic output of product data for variations
		if ( 'on' === get_option( 'german_market_customizable_variations_data_disable_automatic_output', 'off' ) ) {
			add_action( 'german_market_after_init', array( $this, 'disable_automatic_output_variation' ), 9999 );
		}

		// Elementor
		if ( 
			defined( 'ELEMENTOR_VERSION' ) && 
			'on' === get_option( 'german_market_customizable_product_data_disable_automatic_output_elementor', 'off' ) 
		) {
			add_filter( 'german_market_compatibility_elementor_price_data', '__return_false', 9999 );
		}

		// Bakery
		if ( 
			class_exists( 'Vc_Manager' ) && 
			'on' === get_option( 'german_market_customizable_product_data_disable_automatic_output_bakery', 'off' ) 
		) {
			add_filter( 'german_market_wp_bakery_price_html_exception' , '__return_true', 9999 );
		}

		// Change product data
		add_filter( 'wgm_product_summary_parts_after', array( $this, 'change_product_data_output' ), 999, 3 );

		// German Market Submenu
		add_filter( 'german_market_admin_submenu', array( $this, 'add_submenu_entry' ) );

		// Body class for settings 
		add_filter( 'body_class', array( $this, 'add_body_class' ) );

		// Settings: from json to wp_options
		add_action( 'woocommerce_de_ui_update_options', array( $this, 'update_options' ) );

		// update options
		if ( 'no' === get_option( 'german_market_update_3_26', 'no' ) ) {
			$this->update_options_without_separation_loop_single();
			update_option( 'german_market_update_3_26', 'yes' );
		}

		// Special Tax Class
		$number = intval( get_option( 'german_market_special_tax_output_number', 0 ) );
		if ( $number > 0 ) {
			
			$special_tax_objects = array();

			for ( $i = 1; $i <= $number; $i++ ) {

				$tax_class = get_option( 'german_market_special_tax_output_tax_class_' . $i, 'off' );

				if ( 'off' !== $tax_class ) {
					$special_tax_objects[] = new WGM_Special_Tax_Information_For_Tax_Class( $tax_class, $i );
				}
			}
		}
	}

	/**
	 * Wrapper for get_option to avoid calling same get_option too often
	 * 
	 * @param String $key
	 * @param String $default
	 * 
	 * @return mixed
	 */
	public function get_option( $key, $default = '' ) {

		$option_value = '';

		if ( isset( $this->options[ $key ] ) ) {
			$option_value = $this->options[ $key ];
		} else {
			$option_value = get_option( $key, $default );
			$this->options[ $key ] = $option_value;
		}

		return $option_value;
	}

	/**
	 * Runs option update
	 * 
	 * woocommerce_de_show_price_per_unit => woocommerce_de_show_price_per_unit_loop & woocommerce_de_show_price_per_unit_single
	 * woocommerce_de_show_extra_cost_hint_eu => woocommerce_de_show_extra_cost_hint_eu_loop & woocommerce_de_show_extra_cost_hint_eu_single
	 * @return void
	 */
	public function update_options_without_separation_loop_single() {

		$options = array(
			'woocommerce_de_show_price_per_unit',
			'woocommerce_de_show_extra_cost_hint_eu',
		);

		foreach ( $options as $option_key ) {

			$option_value_without_default = get_option( $option_key );
			
			if ( ! empty( $option_value_without_default ) ) {
				update_option( $option_key . '_loop', $option_value_without_default );
				update_option( $option_key . '_single' , $option_value_without_default );
				delete_option( $option_key );
			}

		}
	}

	/**
	 * Body class to determine wheich options are activated
	 * 
	 * @wp-hook body_class
	 * @param Array $classes
	 * @return Array
	 */
	public function add_body_class( $classes ) {

		/**
		 * Filter if body classes should be added
		 * 
		 * @since 3.36
		 * @param Boolean
		 */
		if ( apply_filters( 'german_market_customizable_product_data_add_body_classes', true ) ) {
			
			$theme_compatibility_object = WGM_Theme_Compatibilities::get_instance();

			if ( 
				$theme_compatibility_object->theme_has_compatibility_adjustment() &&
				'on' === get_option( 'german_market_customizable_product_data_disable_theme_compatibility', 'off' )
			) {
				$classes[] = 'german-market-theme-compatibility-off';
			}

			if ( 'on' === get_option( 'german_market_customizable_product_data_disable_automatic_output', 'off' ) ) {
				$classes[] = 'german-market-automatic-product-data-output-off';
			}

			if ( 'on' === get_option( 'german_market_customizable_variations_data_disable_automatic_output', 'off' ) ) {
				$classes[] = 'german-market-automatic-variation-data-output-off';
			}

			if ( 
				defined( 'ELEMENTOR_VERSION' ) && 
				'on' === get_option( 'german_market_customizable_product_data_disable_automatic_output_elementor', 'off' ) 
			) {
				$classes[] = 'german-market-automatic-price-data-elementor-off';
			}

			if ( 
				class_exists( 'Vc_Manager' ) && 
				'on' === get_option( 'german_market_customizable_product_data_disable_automatic_output_bakery', 'off' ) 
			) {
				$classes[] = 'german-market-automatic-price-data-bakery-off';
			}
		}

		return $classes;
	}

	/**
	 * Deactivate automatic output for variations
	 * 
	 * @wp-hook after_setup_theme
	 */
	public function disable_automatic_output_variation() {
		remove_filter( 'woocommerce_available_variation', array( 'WGM_Helper', 'prepare_variation_data' ), 10, 3 );
	}

	/**
	 * Deactivate automatic output
	 * 
	 * @wp-hook after_setup_theme
	 */
	public function disable_automatic_output() {
		remove_action( 'woocommerce_single_product_summary', 			array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_single' ), 7 );
		remove_action( 'woocommerce_after_shop_loop_item_title',		array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		remove_filter( 'woocommerce_blocks_product_grid_item_html',		array( 'WGM_Template', 'german_market_woocommerce_blocks_price' ), 10, 3 );
		remove_action( 'woocommerce_widget_product_item_end', 			array( 'WGM_Template', 'widget_product_item_end' ) );
	}

	/**
	 * Add submenu in German Market backend menu
	 * 
	 * @wp-hook german_market_admin_submenu
	 * @param Array $submenu
	 * @return Array
	 */
	public function add_submenu_entry( $submenu ) {

		if ( isset( $submenu[ 'products' ] ) ) {
			
			$pos = intval( array_search( 'products', array_keys( $submenu ) ) ) + 1;

			$submenu_entry = array(
				'title'		=> __( 'Product Data', 'woocommerce-german-market' ),
				'slug'		=> 'product-data',
				'callback'	=> array( $this, 'submenu_callback' ),
				'options'	=> 'yes'
			);

			$submenu = 	array_slice( $submenu, 0, $pos, true) +
						array( 'product_data' => $submenu_entry ) +
						array_slice( $submenu, $pos, count( $submenu ) - $pos, true);

		}
		
		return $submenu;
	}

	/**
	 * Callback to render submenu options
	 * 
	 * @return Array
	 */
	public function submenu_callback() {

		$options = array(

			array(
				'name' => __( 'Product Data', 'woocommerce-german-market' ),
				'id' => 'product-data',
				'type' => 'title',
				'desc' => __( 'With these settings, you can influence, deactivate and rearrange the output of the data that German Market outputs after a product price. If you do this, you should check with a lawyer whether the resulting output is legally correct.', 'woocommerce-german-market' ),
			),

			array( 'type' => 'sectionend' )
		);

		// Note about the "Generalized Tax Output" option of the "EU VAT Checkout" add-on, only if used
		$general_tax_option_used = '';
		if ( 
			class_exists( 'WGM_General_Tax_Output' ) &&
			'on' === get_option( 'wcevc_general_tax_output_activation', 'off' )
		) {

			$general_tax_option_used = sprintf(
				__( 'Note: The following settings may be overwritten by the <a href="%s">"Generalized Tax Output"</a> setting of the "EU VAT Checkout" add-on.', 'woocommerce-german-market' ),
				admin_url() . 'admin.php?page=german-market&tab=eu_vat_checkout&sub_tab=generalized_tax_output'
			);

		}

		$options[] = array(
			'name' => __( 'Tax Output', 'woocommerce-german-market' ),
			'id' => 'tax-output',
			'type' => 'title',
			'desc' => $general_tax_option_used
		);

		$options[] = array(
			'name'			=> __( 'Prefix tax output gross', 'woocommerce-german-market' ),
			'id'			=> 'german_market_customizable_product_data_prefix_incl',
			'type'			=> 'text',
			'placeholder' 	=> $this->default_includes,
			'css'			=> 'width: 200px;',
			'class'			=> 'german-market-unit',
			'desc' 			=> __( 'x% tax name', 'woocommerce-german-market' ),
			'desc_tip'		=> sprintf( 
				__( 'If this setting is left blank, the default value "%s" is used.', 'woocommerce-german-market' ),
				$this->default_includes
			),
		);

		$options[] = array(
			'name'			=> __( 'Prefix tax output net', 'woocommerce-german-market' ),
			'id'			=> 'german_market_customizable_product_data_prefix_excl',
			'type'			=> 'text',
			'placeholder' 	=> $this->default_excludes,
			'css'			=> 'width: 200px;',
			'class'			=> 'german-market-unit',
			'desc' 			=> __( 'x% tax name', 'woocommerce-german-market' ),
			'desc_tip'		=> sprintf( 
				__( 'If this setting is left blank, the default value "%s" is used.', 'woocommerce-german-market' ),
				$this->default_excludes
			),
			
		);

		$options[] = array( 'type' => 'sectionend' );

		$options[] = array(
			'name' => __( 'Advanced Settings', 'woocommerce-german-market' ),
			'type' => 'title',
			'id' => 'advanced-settings',
		);
	
		$options[] = array(
			'name'     => __( 'Disable automatic output of product data', 'woocommerce-german-market' ),
			'id'       => 'german_market_customizable_product_data_disable_automatic_output',
			'type'     => 'wgm_ui_checkbox',
			'default'  => 'off',
			'desc_tip' => __( 'By default, German Market adds further legally required data after a price has been displayed. If you do not want this data to be output because you add it yourself using blocks or shortcode, you can activate this setting. Variations of variable products are excluded in this setting.', 'woocommerce-german-market' ) . ' ' . __( 'If you activate this setting, only the option "Do not change WooCommerce behaviour" is available at "WooCommerce -> German Market -> General -> Products -> When a variation of a variable product has been selected".', 'woocommerce-german-market' )
		);

		$options[] = array(
			'name'     => __( 'Disable automatic output of product data for variations', 'woocommerce-german-market' ),
			'id'       => 'german_market_customizable_variations_data_disable_automatic_output',
			'type'     => 'wgm_ui_checkbox',
			'default'  => 'off',
			'desc_tip' => __( 'By default, German Market adds further legally required data after a price of a variation has been displayed. If you do not want this data to be output, you can activate this setting.', 'woocommerce-german-market' ),
		);

		$theme_compatibility_object = WGM_Theme_Compatibilities::get_instance();

		if ( $theme_compatibility_object->theme_has_compatibility_adjustment() ) {

			$options[] = array(
				'name'     => __( 'Disable customization for theme compatibility', 'woocommerce-german-market' ),
				'id'       => 'german_market_customizable_product_data_disable_theme_compatibility',
				'type'     => 'wgm_ui_checkbox',
				'default'  => 'off',
				'desc_tip' => __( 'German Market contains special code for some themes so that the price output is correct and German Market is compatible with the respective theme. This is the case with your activated theme. If you do not want to use the special customization, deactivate this setting.', 'woocommerce-german-market' ),
			);
		}

		// Elementor
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			$options[] = array(
				'name'     => __( 'Disable automatic output of product data after "Elementor Page Builder" price', 'woocommerce-german-market' ),
				'id'       => 'german_market_customizable_product_data_disable_automatic_output_elementor',
				'type'     => 'wgm_ui_checkbox',
				'default'  => 'off',
			);
		}

		// Bakery
		if ( class_exists( 'Vc_Manager' ) ) {
			$options[] = array(
				'name'     => __( 'Disable automatic output of product data after "Bakery Page Builder" price', 'woocommerce-german-market' ),
				'id'       => 'german_market_customizable_product_data_disable_automatic_output_bakery',
				'type'     => 'wgm_ui_checkbox',
				'default'  => 'off',
			);
		}

		$options[] = array(
			'name' => __( 'Change automatically displayed product data and their order', 'woocommerce-german-market' ),
			'id' 		=> 'german_market_customizable_product_data_customized',
			'type' 		=> 'wgm_ui_checkbox',
			'default'  	=> 'off',
			'desc'		=> '<span style="color: #f00;">' . __( 'If you activate this setting, you can reorder and deactivate the elements that German Market displays after a price.', 'woocommerce-german-market' ) . '<br>' . __( 'If you do this, you should clarify whether the resulting output is legally correct.', 'woocommerce-german-market' ) . '</span>'
		);

		$options[] = array( 'type' => 'sectionend' );

		// Loop
		$sortable = $this->get_sortable_settings( 'loop' );		

		$options[] = array(
			'name' => __( 'Sortable Product Data in Shop', 'woocommerce-german-market' ),
			'id' => 'sortable-data-loop',
			'type' => 'title',
			'desc' => $sortable
		);

		$options[] = array(
			'name' => 'Sortable Data in Loop - SAVE FIELD (hidden)',
			'id' => 'german_market_customizable_product_data_loop',
			'type' => 'text',
		);

		$options[] = array( 'type' => 'sectionend' );

		// Single
		$sortable = $this->get_sortable_settings( 'single' );		

		$options[] = array(
			'name' => __( 'Sortable Product Data on Single Product Pages', 'woocommerce-german-market' ),
			'type' => 'title',
			'id' => 'sortable-data-single',
			'desc' => $sortable
		);

		$options[] = array(
			'name' => 'Sortable Data in Single - SAVE FIELD (hidden)',
			'id' => 'german_market_customizable_product_data_single',
			'type' => 'text',
		);

		$options[] = array( 'type' => 'sectionend' );

		$options[] = array(
			'name' => __( 'Tax information for products with a special tax class', 'woocommerce-german-market' ),
			'type' => 'title',
			'desc' => __( 'For products with a special tax class, you can use the following options to display special tax information. This is useful, for example, if you sell products that are tax-free due to a special legal paragraph. The "Tax status" of the product must be assigned the value "Taxable".', 'woocommerce-german-market' ),
		);

		$options[] = array(
			'name'    => __( 'Number of tax classes with special tax output', 'woocommerce-german-market' ),
			'id'      => 'german_market_special_tax_output_number',
			'type'    => 'number',
			'custom_attributes' => array(
				'min'	=> 0,
				'max'	=> apply_filters( 'german_market_special_tax_output_number', 5 )
			),
			'default' => 0,
		);

		$tax_class_options = array(
			'off' 		=> __( 'Deactivated', 'woocommerce-german-market' ),
			'standard'	=> __( 'Standard', 'woocommerce-german-market' )
		);

		$tax_classes = WC_Tax::get_tax_rate_classes();

		foreach ( $tax_classes  as $tax_class ) {
			$tax_class_options[ $tax_class->slug ] = $tax_class->name;
		}

		for ( $i = 1; $i <= apply_filters( 'german_market_special_tax_output_number', 5 ); $i++ ) {

			$options[] = array(
				'name'     => sprintf( __( '%s - Tax class', 'woocommerce-german-market' ), $i ),
				'id'       => 'german_market_special_tax_output_tax_class_' . $i,
				'type'     => 'select',
				'default'  => 'off',
				'options'  => $tax_class_options,
				'class'	   => 'german-market-special-tax-output german-market-special-tax-output-first',
			);

			$options[] = array(
				'name'     => sprintf( __( '%s - Tax information', 'woocommerce-german-market' ), $i ),
				'id'       => 'german_market_special_tax_output_tax_information_' . $i,
				'type'     => 'text',
				'default'  => '',
				'class'	   => 'german-market-special-tax-output german-market-special-tax-output-last'
			);

		}

		$options[] = array( 'type' => 'sectionend' );

		/**
		 * Filter backend options
		 * 
		 * @since 3.36
		 * @param Array $options
		 */
		$options = apply_filters( 'woocommerce_de_ui_options_product_data', $options );
		return $options;
	}

	/**
	 * Make sortable UI (backend) for product data
	 * 
	 * @param String $loop_or_single
	 * @return String
	 */
	public function get_sortable_settings( $loop_or_single ) {

		ob_start(); ?>
		<ul class="german-market-sortable-product-data" id="german-market-sortable-product-data-<?php echo esc_attr( $loop_or_single ); ?>">

			<?php 
			foreach ( $this->get_sorted_schema( $loop_or_single ) as $key => $product_data ) {
				
				$label = isset( $product_data[ 'label' ] ) ? $product_data[ 'label' ] : $key;
				$price_class = $key === 'price' ? 'price' : 'not-price';
				$price_class = apply_filters( 'german_market_sortable_product_data_class', $price_class, $loop_or_single, $key );
				$dashicon = $price_class === 'price' ? 'lock' : 'sort';

				?><li class="sort-class <?php echo esc_attr( $price_class ); ?>" data-element-key="<?php echo esc_attr( $key ); ?>">
					<span class="german-market-sortable-item">
						<span class="info">
							<span class="dashicons dashicons-<?php echo esc_attr( $dashicon ); ?>"></span>
							
							<span class="text">
								<span class="label"><?php echo esc_attr( $label ); ?></span>
								<?php if ( isset( $product_data[ 'more-settings' ] ) && false !== $product_data[ 'more-settings' ] ) { ?>	
									<span class="more-settings">
										<a href="<?php echo esc_url( admin_url( 'admin.php' . $product_data[ 'more-settings' ] ) ); ?>" target="_blank"><?php echo esc_attr( __( 'More settings', 'woocommerce-german-market' ) ); ?></a>
									</span>
								<?php } ?>
								
								<?php if ( isset( $product_data[ 'desc' ] ) && false !== $product_data[ 'desc' ] ) { ?>	
									<span class="desc"><?php echo esc_attr( $product_data[ 'desc' ] ); ?></span>
								<?php } ?>
							
							</span>

						</span>
						
						<?php 
							$switch_key = $key . '_activation_' . $loop_or_single;
							$option_value = ( isset( $product_data[ 'activation' ] ) && 'on' === $product_data[ 'activation' ] ) ? 'on' : 'off';
							$off_active = $option_value == 'off' ? 'active' : 'clickable';
							$on_active  = $option_value == 'on' ? 'active' : 'clickable';
							$slider_class =  $option_value == 'on' ? 'on' : '';
						?>
						<span class="switcher">
							<span class="switch <?php echo esc_attr( $switch_key ) ?>"><div class="slider round gm-slider <?php echo esc_attr( $slider_class ) . ' ' . esc_attr( $loop_or_single ); ?>"></div></span>
							
							<p class="screen-reader-buttons">
								<span class="gm-ui-checkbox switcher-german-market off <?php echo esc_attr( $off_active ); ?>"><?php echo esc_attr( __( 'Off', 'woocommerce-german-market' ) ); ?></span>
								<span class="gm-ui-checkbox delimter">|</span>
								<span class="gm-ui-checkbox switcher-german-market on <?php echo esc_attr( $on_active ); ?>"><?php echo esc_attr( __( 'On', 'woocommerce-german-market' ) ); ?></span>
							</p>
						</span>
					</span>
				</li>
				<?php
			}

			?>

		</ul>

		<?php 
		$sortable = ob_get_clean();
		$sortable = str_replace( array( "\r" , "\n"  ), '', $sortable );
		return $sortable;
	}

	/**
	 * Change product data output (frontend)
	 * 
	 * @wp-hook wgm_product_summary_parts_after
	 * @param Array $output_parts
	 * @param WC_Product $product
	 * @param String $hook
	 * @return Array
	 */
	public function change_product_data_output( $output_parts, $product, $hook ) {
		
		if ( 'on' === get_option( 'german_market_customizable_product_data_customized', 'off' ) ) {

			$sorted_data = $this->get_sorted_schema( $hook );

			$new_output_parts = array();

			if ( isset( $output_parts[ 'shipping' ] ) ) {
				
				// Value of 'shipping' contains shipping info and delviery time here
				// Now we use two separate callback functions
				unset( $output_parts[ 'shipping' ] );
			}

			foreach ( $sorted_data as $key => $data ) {

				if ( isset( $data[ 'activation' ] ) && 'on' === $data[ 'activation' ] ) { // If output is activated for this hook
					
					if ( isset( $data[ 'callback' ] ) && false !== $data[ 'callback' ] ) {
						
						// If we have a callable callback function => call it with param array( 'product_object' => $product )
						if ( is_callable( $data[ 'callback' ] ) ) {
							$value = call_user_func( $data[ 'callback' ], array( 'product_object' => $product ) );
						} else {
							// If the callback is not callable, just make the value as output
							$value = strval( $data[ 'callback' ] );
						}

						// Add the value if it's not empty
						if ( ! empty( $value ) ) {
							$new_output_parts[ $key ] = $value;
						}
					
					} else if ( isset( $data[ 'filter_callback' ] ) && false !== $data[ 'filter_callback' ] ) {
						
						$value = apply_filters( $data[ 'filter_callback' ], '', $product, $hook );

						// Add the value if it's not empty
						if ( ! empty( $value ) ) {
							$new_output_parts[ $key ] = $value;
						}

					} else {

						// If no callback is defined, copy it from original value
						if ( isset( $output_parts[ $key ] ) ){
							$new_output_parts[ $key ] = $output_parts[ $key ];
						}
					}
				}

				// Remove from original array
				if ( isset( $output_parts[ $key ] ) ) {
					unset( $output_parts[ $key ] );
				}
			}

			// Add remaining parts (maybe added by 3rd party)
			foreach ( $output_parts as $key => $value ) {
				$new_output_parts[ $key ] = $value;
			}

			$output_parts = $new_output_parts;
		}

		/**
		 * Filter output
		 * 
		 * @since 3.36
		 * @param Array $output_parts
		 * @param WC_Product $product
		 * @param String $hook  
		 */
		return apply_filters( 'german_market_product_summary_parts_after_customization', $output_parts, $product, $hook );
	}

	/**
	 * Save wp_options after json-data is saved
	 * 
	 * @wp-hook woocommerce_de_ui_update_options
	 * @param Array $options
	 * @return void
	 */
	public function update_options( $options ) {

		if ( isset( $_POST[ 'submit_save_wgm_options' ] ) ) { 
			if ( wp_verify_nonce( $_POST[ 'update_wgm_settings' ], 'woocommerce_de_update_wgm_settings' ) ) {

				if ( isset( $_REQUEST[ 'german_market_customizable_product_data_single' ] ) ) {

					$activation = get_option( 'german_market_customizable_product_data_customized', 'off' );
					$single_data = json_decode( get_option( 'german_market_customizable_product_data_single' ), true );
					$loop_data = json_decode( get_option( 'german_market_customizable_product_data_loop' ), true );

					if ( 'on' === $activation ) {
						foreach( $this->get_all_possible_data() as $key => $data ) {

							if ( isset( $data[ 'activation_single_key' ] ) && isset( $single_data[ $key ] ) ) {
								update_option( $data[ 'activation_single_key' ], $single_data[ $key ] );
							}

							if ( isset( $data[ 'activation_loop_key' ] ) && isset( $loop_data[ $key ] ) ) {
								update_option( $data[ 'activation_loop_key' ], $loop_data[ $key ] );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Get sorted data
	 * 
	 * @param String $loop_or_single
	 * @return Array
	 */
	public function get_sorted_schema( $loop_or_single = 'loop' ) {

		if ( isset( $this->sorted_schema[ $loop_or_single ] ) ) {
			
			$data = $this->sorted_schema[ $loop_or_single ];

		} else {

			$all_possible_data = $this->get_all_possible_data( $loop_or_single );
			$saved_data = '';

			$data = $all_possible_data;

			$option_key = 'german_market_customizable_product_data_' . $loop_or_single;
			$just_saved = false;

			if ( isset( $_REQUEST[ $option_key ] ) && ( wp_verify_nonce( $_POST[ 'update_wgm_settings' ], 'woocommerce_de_update_wgm_settings' ) ) ) {
				$saved_data = stripslashes( sanitize_text_field( $_REQUEST[ $option_key ] ) );
				$just_saved = true;
			} else{
				$saved_data = get_option( $option_key, '' );
			}

			if ( ! empty( $saved_data ) ) {
				
				$saved_data = json_decode( $saved_data, true );
				$data = array();
				
				foreach ( $saved_data as $key => $activation ) {

					if ( ! isset( $all_possible_data[ $key ] ) ) {
						continue;
					}

					if ( ! $just_saved ) {
						$activation = $this->get_activation( $activation, $loop_or_single, isset( $all_possible_data[ $key ] ) ? $all_possible_data[ $key ] : array() );
					}

					$data[ $key ] = array(
						'label' 			=> isset( $all_possible_data[ $key ][ 'label' ] ) ? $all_possible_data[ $key ][ 'label' ] : $key,
						'activation'		=> $activation,
						'desc'				=> isset( $all_possible_data[ $key ][ 'desc' ] ) ? $all_possible_data[ $key ][ 'desc' ] : '',
						'callback'			=> isset( $all_possible_data[ $key ][ 'callback' ] ) ? $all_possible_data[ $key ][ 'callback' ] : false,
						'filter_callback'	=> isset( $all_possible_data[ $key ][ 'filter_callback' ] ) ? $all_possible_data[ $key ][ 'filter_callback' ] : false,
						'more-settings'		=> isset( $all_possible_data[ $key ][ 'more-settings' ] ) ? $all_possible_data[ $key ][ 'more-settings' ] : false,
					);

					if ( isset( $all_possible_data[ $key ] ) ){
						unset( $all_possible_data[ $key ] );
					}

				}

				foreach ( $all_possible_data as $key => $new_default_data ) {
					$data[ $key ] = $new_default_data;
				}

			}

			// Remove alc. if fic add-on is not activated
			if ( ! function_exists( 'german_market_fic_init' ) ) {
				if ( isset( $data[ 'alc' ] ) ) {
					unset( $data[ 'alc' ] );
				}
			}

			$this->sorted_schema[ $loop_or_single ] = $data;
		}

		return $data;
	}

	/**
	 * Get activation status
	 * 
	 * @param String $activation
	 * @param String $loop_or_single
	 * @param Array $default_data
	 * 
	 * @return String ('on' or 'off')
	 */
	public function get_activation( $activation, $loop_or_single, $default_data ) {

		if ( 'single' === $loop_or_single && isset( $default_data[ 'activation_single' ] ) ) {
			$activation = $default_data[ 'activation_single' ] ? 'on' : 'off';
		} else if ( 'loop' === $loop_or_single && isset( $default_data[ 'activation_loop' ] ) ) {
			$activation = $default_data[ 'activation_loop' ] ? 'on' : 'off';
		}

		return $activation;
	}

	/**
	 * Get all possible German Market product data
	 * 
	 * @param String $loop_or_single 
	 * @return Array
	 */
	public function get_all_possible_data( $loop_or_single = 'loop' ) {

		/**
		 * Filter all possible German Market product data
		 * 
		 * @since 1.0
		 * @param Array
		 */
		return apply_filters( 'german_market_get_possible_product_data', array(

			'price' => array(
				'label' 				=> __( 'Price', 'woocommerce-german-market' ),
				'activation'			=> 'on',
			),
			
			'tax' => array(
				'label' 				=> __( 'Tax', 'woocommerce-german-market' ),
				'activation'			=> 'on',
			),
			
			'tax_and_shipping' => array(
				'label' 				=> __( 'Tax & Shipping', 'woocommerce-german-market' ),
				'activation'			=> 'off',
				'desc' 					=> __( 'This element shows tax and shipping information in one row. If enabled, "Tax" and "Shipping Information" should be disabled.', 'woocommerce-german-market' ),
				'callback'				=> array( __CLASS__ , 'tax_and_shipping' )
			),

			'ppu' => array(
				'label' 				=> __( 'Price per unit', 'woocommerce-german-market' ),
				'activation_loop' 		=> 'on' === $this->get_option( 'woocommerce_de_show_price_per_unit_loop', 'on' ),
				'activation_loop_key' 	=> 'woocommerce_de_show_price_per_unit_loop',
				'activation_single'		=> 'on' === $this->get_option( 'woocommerce_de_show_price_per_unit_single', 'on' ),
				'activation_single_key' => 'woocommerce_de_show_price_per_unit_single',
				'more-settings'			=> '?page=german-market&tab=general&sub_tab=products#gm_price_per_unit-description',
			),

			'shipping' => array(
				'label' 				=> __( 'Shipping Information', 'woocommerce-german-market' ),
				'activation'			=> 'on',
				'callback'				=> array( 'WGM_Shortcodes', 'shipping_info_callback' )
			),
			
			'delivery_time' => array(
				'label' 				=> __( 'Delivery Time', 'woocommerce-german-market' ),
				'activation_loop' 		=> 'on' === $this->get_option( 'woocommerce_de_show_delivery_time_overview', 'off' ),
				'activation_loop_key' 	=> 'woocommerce_de_show_delivery_time_overview',
				'activation_single'		=> 'on' === $this->get_option( 'woocommerce_de_show_delivery_time_product_page', 'on' ),
				'activation_single_key' => 'woocommerce_de_show_delivery_time_product_page',
				'callback'				=> array( 'WGM_Shortcodes', 'delivery_time_callback' ),
				'more-settings'			=> '?page=german-market&tab=general&sub_tab=delivery-times',
			),
			
			'extra_costs_non_eu' => array(
				'label' 				=> __( 'Shipping Note Non-EU Countries', 'woocommerce-german-market' ),
				'activation_loop' 		=> 'on' === $this->get_option( 'woocommerce_de_show_extra_cost_hint_eu_loop', 'off' ),
				'activation_loop_key' 	=> 'woocommerce_de_show_extra_cost_hint_eu_loop',
				'activation_single'		=> 'on' === $this->get_option( 'woocommerce_de_show_extra_cost_hint_eu_single', 'on' ),
				'activation_single_key' => 'woocommerce_de_show_extra_cost_hint_eu_single',
				'more-settings'			=> '?page=german-market&tab=general&sub_tab=products#de_products-description',
			),

			'alc' => array(
				'label' 				=> __( 'Alcohol', 'woocommerce-german-market' ),
				'activation_loop' 		=> 'on' === $this->get_option( 'gm_fic_ui_alocohol_loop', 'on' ),
				'activation_loop_key' 	=> 'gm_fic_ui_alocohol_loop',
				'activation_single'		=> 'on' === $this->get_option( 'gm_fic_ui_alocohol_product_page', 'on' ),
				'activation_single_key' => 'gm_fic_ui_alocohol_product_page',
				'more-settings'			=> '?page=german-market&tab=fic#gm_fic_ui_alocohol_default_unit',
			),

			'gtin' => array(
				'label' 				=> __( 'GTIN', 'woocommerce-german-market' ),
				'activation_loop' 		=> 'on' === $this->get_option( 'gm_gtin_loop', 'off' ),
				'activation_loop_key' 	=> 'gm_gtin_loop',
				'activation_single'		=> 'on' === $this->get_option( 'gm_gtin_product_pages', 'off' ),
				'activation_single_key'	=> 'gm_gtin_product_pages',
				'more-settings' 		=> '?page=german-market&tab=general&sub_tab=products#german_market_gtin-description',
			),

			'charging_device' => array(
				'label' 				=> __( 'Charging Device', 'woocommerce-german-market' ),
				'activation_loop' 		=> 'on' === $this->get_option( 'german_market_charging_device_activation_loop', 'on' ),
				'activation_loop_key' 	=> 'german_market_charging_device_activation_loop',
				'activation_single'		=> 'on' === $this->get_option( 'german_market_charging_device_activation_single', 'on' ),
				'activation_single_key'	=> 'german_market_charging_device_activation_single',
				'more-settings' 		=> '?page=german-market&tab=general&sub_tab=products#german_market_charging_devices-description',
			)

		) );

	}

	/**
	 * It's a clone from German_Market_Product_Block_Tax_And_Shipping::render_content
	 * 
	 * @param Array $attributes
	 * @return $string
	 */
	public static function tax_and_shipping( $attributes ) {

		$tax_and_shipping_info = '';
		$product = null;
		
		if ( isset( $attributes[ 'product_object' ] ) ) {
			$product = $attributes[ 'product_object' ];
		}

		if ( WGM_Helper::method_exists( $product, 'get_id' ) ) {
            
            // 1. delimiter
            $delimiter = isset( $attributes[ 'delimiter' ] ) ? $attributes[ 'delimiter' ] : ', ';

            // 2. shipping part
            $shipping_info = WGM_Shipping::get_shipping_page_link( $product );

            // Check for free shipping advertising option
		    $free_shipping = get_option( 'woocommerce_de_show_free_shipping' ) === 'on';

            $info = sprintf(
                '<div class="wgm-info woocommerce_de_versandkosten">%s</div>',
                $shipping_info
            );

            //TODO Deprecate 2nd parameter (used to $stopped_by_option, which has always been false since there was a return before this filter was able to run)
            $info = apply_filters( 'wgm_product_shipping_info', $info, FALSE, $free_shipping, $product );
            $info = trim( strip_tags( $info, '<a>' ) );

            // 3. tax part
            $is_cart = false;
            $tax_info = WGM_Tax::text_including_tax( $product, $is_cart, false );
            $tax_info = trim( strip_tags( $tax_info ) );

            // 4. together
            if ( empty( $info ) || empty( $tax_info ) ) {
                $delimiter = '';
            }

            $tax_and_shipping_info = $tax_info . $delimiter . $info;
            $tax_and_shipping_info = '<span class="wgm-info tax-and-shipping-customized">' . $tax_and_shipping_info . '</span>';

        }

        return $tax_and_shipping_info;
	}

	/**
	 * Change prefix for tax (includes / plus)
	 * 
	 * @param String $tax_output
	 * @return String
	 */
	public function change_tax_output_wording( $tax_output ) {

		if ( 
			( ! empty( $this->includes_option ) ) &&
			( $this->default_includes !== $this->includes_option ) 
		) {

			$search  = array( 
				ucfirst( $this->default_includes ),
				lcfirst( $this->default_includes )
			);

			$replace = $this->includes_option;

			$tax_output = str_replace( $search, $replace, $tax_output );

		}

		if ( 
			( ! empty( trim( $this->excludes_option ) ) ) &&
			( $this->default_excludes !== $this->excludes_option ) 
		) {

			$search  = array( 
				ucfirst( $this->default_excludes ),
				lcfirst( $this->default_excludes )
			);

			$replace = trim( $this->excludes_option );

			$tax_output = str_replace( $search, $replace, $tax_output );

		}

		return $tax_output;
	}

	/**
	 * Get option key for "When a variation of a variable product has been selected"
	 * 
	 * @since 3.50
	 * @return String
	 */
	public static function get_price_presentation_variable_products() {

		$option = get_option( 'german_market_price_presentation_variable_products', 'gm_default' );
		if ( 'on' === get_option( 'german_market_customizable_product_data_disable_automatic_output', 'off' ) ) {
			$option = 'woocommerce';
		}

		return $option;
	}
}
