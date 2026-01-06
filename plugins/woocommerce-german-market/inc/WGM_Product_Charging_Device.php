<?php

class WGM_Product_Charging_Device {

	/**
	 * @var Array $runtime_cache
	 * @since v3.45
	 */
	public static $runtime_cache = array();

	/**
	 * @var WGM_Product_Charging_Device
	 * @since v3.45
	 */
	private static $instance = null;
	
	/**
	* Singletone get_instance
	*
	* @static
	* @return WGM_Product_Charging_Device
	*/
	public static function get_instance() {
		if ( self::$instance == NULL) {
			self::$instance = new WGM_Product_Charging_Device();	
		}
		return self::$instance;
	}

	/**
	 * Construct
	 */
	private function __construct() {

		add_filter( 'wgm_product_summary_parts_after', array( $this, 'add_charging_device' ), 99, 3 );

		// add product options
		add_action( 'woocommerce_product_data_tabs', array( $this, 'backend_product_tab' ), 11 );
		add_action( 'woocommerce_product_data_panels', array( $this, 'backend_product_tab_write_panel' ) );
		add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'product_options_variation' ), 200, 3 );

		// save product options
		add_filter( 'german_market_add_process_product_meta_meta_keys', array( $this, 'save_product_options' ) );

		do_action( 'german_market_product_charging_device_after_construct', $this );
	}

	/**
	 * Add charging device to German Market price data
	 * 
	 * @wp-hook wgm_product_summary_parts_after
	 * @param Array $output_parts
	 * @param WC_Product $product
	 * @param String $hook
	 * @return Array
	 */
	public function add_charging_device( $output_parts, $product, $hook ) {

		$option_key = 'german_market_charging_device_activation_' . $hook;
		if ( 'on' === get_option( $option_key ) ) {
			$output_parts[ 'charging_device' ] = $this->get_markup_by_product( $product );
		}

		return $output_parts;
	}

	/**
	 * Get string from CSS from an array
	 * 
	 * @param Array
	 * @return String
	 */
	public function make_css_string_from_array( $css_array = array() ) : String {
		$css = '';

		if ( ! empty( $css_array ) ) {
			foreach( $css_array as $key => $value ) {
				$css .= $key . ': ' . $value .';';
			}
		}

		return $css;
	}

	/**
	 * Get inline css for "device graphic"
	 * 
	 * @return String
	 */
	public function get_device_graphic_inline_css() {
		
		$a = $this->get_value_of_variable_a();
		$unit = $this->get_unit_for_variable_a();

		$device_graphic_width = round( $a * 4 / 3, 4 ) . $unit;
		$device_graphic_height = round( $a * 11 / 6, 4 ) . $unit;

		return $this->make_css_string_from_array(
			array(
				'width'			=> $device_graphic_width,
				'height' 		=> $device_graphic_height
			)
		);
	}

	/**
	 * Get inline css for "device second part"
	 * 
	 * @return String
	 */
	public function get_device_second_part_inline_css() {

		$a = $this->get_value_of_variable_a();
		$unit = $this->get_unit_for_variable_a();

		return $this->make_css_string_from_array(
			array(
				'bottom' 		=> round( $a / 6, 4 ) . $unit,
				'margin-left'	=> round( $a * -1/2 , 4 ) . $unit,
				'width'			=> round( $a, 4 ) . $unit,
				'height'		=> round( $a, 4 ) . $unit,
				'padding-top'	=> round( $a / 10, 4 ) . $unit,
				'font-size'		=> round( $a / 4.5, 4 ) . $unit,
				'line-height'	=> round( $a / 4, 4 ) . $unit,
			)
		);
	}

	/**
	 * Get inline css for "device first part"
	 * 
	 * @return String
	 */
	public function get_device_first_part_inline_css() {

		$a = $this->get_value_of_variable_a();
		$unit = $this->get_unit_for_variable_a();

		return $this->make_css_string_from_array(
			array(
				'bottom' 		=> round( $a / 6 + $a, 4 ) . $unit,
				'margin-left'	=> round( 2 * $a * -1 / 6, 4 ) . $unit,
				'width'			=> round( 2 * $a / 3, 4 ) . $unit,
				'height'		=> round( $a / 4, 4 ) . $unit,
			)
		);
	}

	/**
	 * Get inline css for "pin"
	 * 
	 * @param Integer $nr (left or right pin)
	 * @return String
	 */
	public function get_device_pin_css( $nr = 1 ) {

		$a = $this->get_value_of_variable_a();
		$unit = $this->get_unit_for_variable_a();
		$position = 1 === $nr ? 'left' : 'right';

		return $this->make_css_string_from_array(
			array(
				'bottom' 		=> round( $a / 6 + $a + $a / 4, 4 ) . $unit,
				$position		=> round( $a / 8 + $a / 3, 4 ) . $unit,
				'width'			=> round( $a / 12, 4 ) . $unit,
				'height'		=> round( $a / 4, 4 ) . $unit,
			)
		);
	}

	/**
	 * Get inline css for "USB PD"
	 * 
	 * @return String
	 */
	public function get_device_usb_pd_css() {
		
		$a = $this->get_value_of_variable_a();
		$unit = $this->get_unit_for_variable_a();
		
		return $this->make_css_string_from_array(
			array(
				'font-size'		=> round( $a / 6, 4 ) . $unit,
			)
		);
	}

	/**
	 * Get variable of variable a
	 * 
	 * @return Float
	 */
	public function get_value_of_variable_a() {

		if ( isset( $_REQUEST[ 'submit_save_wgm_options' ] ) && isset( $_REQUEST[ 'german_market_charging_device_size' ] ) ) {
			if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'update_wgm_settings' ] ) ), 'woocommerce_de_update_wgm_settings' ) ) {
				return intval( $_REQUEST[ 'german_market_charging_device_size' ] );
			}
		}

		return intval( get_option( 'german_market_charging_device_size', 40 ) );
	}

	/**
	 * Get unit of variable a
	 * 
	 * @return String
	 */
	public function get_unit_for_variable_a() {

		if ( isset( $_REQUEST[ 'submit_save_wgm_options' ] ) && isset( $_REQUEST[ 'german_market_charging_device_unit' ] ) ) {
			if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'update_wgm_settings' ] ) ), 'woocommerce_de_update_wgm_settings' ) ) {
				return esc_attr( $_REQUEST[ 'german_market_charging_device_unit' ] );
			}
		}

		return get_option( 'german_market_charging_device_unit', 'px' );
	}

	/**
	 * Get data for the charging device by product
	 * 
	 * @param WC_Product $product
	 * @return Array
	 */
	public function get_product_data_by_product( $product ) {

		if ( isset( self::$runtime_cache[ $product->get_id() ] ) ) {
			return self::$runtime_cache[ $product->get_id() ];
		}

		$data = array();
		$pictogram =  $product->get_meta( '_german_market_charging_device_pictogram' ); 
		$show_markup = ( 'no' !== $pictogram ) &&  ( '' !== $pictogram );

		$data = array(
			'show_markup' => $show_markup,
		);

		if ( $show_markup ) {
			$data[ 'device_included' ] = 'included' === $product->get_meta( '_german_market_charging_device_pictogram' ) ? 'yes' : 'no';
			$data[ 'power_min' ] = $product->get_meta( '_german_market_charging_device_minimum_power' );
			$data[ 'power_max' ] = $product->get_meta( '_german_market_charging_device_maximum_power' );
			$data[ 'usb_pd' ] = 'yes' === $product->get_meta( '_german_market_charging_device_usb_pd' );
		}

		self::$runtime_cache[ $product->get_id() ] = $data;
		return $data;
	}	

	/**
	 * Get data for the charging device by product ID (fast)
	 * 
	 * @param Integer $product_id 
	 * @return Array
	 */
	public function get_product_data_by_product_id( $product_id ) {

		if ( isset( self::$runtime_cache[ $product_id ] ) ) {
			return self::$runtime_cache[ $product_id ];
		}

		$data = array();

		$pictogram =  get_post_meta( $product_id, '_german_market_charging_device_pictogram', true ); 
		$show_markup = ( 'no' !== $pictogram ) &&  ( '' !== $pictogram );

		$data = array(
			'show_markup' => $show_markup,
		);

		if ( $show_markup ) {
			$data[ 'device_included' ] = 'included' === get_post_meta( $product_id, '_german_market_charging_device_pictogram', true ) ? 'yes' : 'no';
			$data[ 'power_min' ] = get_post_meta( $product_id, '_german_market_charging_device_minimum_power', true );
			$data[ 'power_max' ] = get_post_meta( $product_id, '_german_market_charging_device_maximum_power', true );
			$data[ 'usb_pd' ] = 'yes' === get_post_meta( $product_id, '_german_market_charging_device_usb_pd', true );
		}

		self::$runtime_cache[ $product_id ] = $data;
		return $data;
	}

	/**
	 * Get product data
	 * 
	 * @param WC_Product $product
	 * @param Integer $variation_id
	 * @param Array $parent_data 
	 * @return Array
	 */
	public function get_product_data( $product, $variation_id = null, $parent_data = array() ) {

		$data = array();
		
		if ( is_object( $product ) && method_exists( $product, 'get_type' ) ) {

			if ( false === strpos( $product->get_type(), 'variable' ) ) { // It's not a variable product
				
				if ( false !== strpos( $product->get_type(), 'variation' ) ) { // It's a variation of a variable product
					
					$variation_setting = $product->get_meta( '_german_market_charging_device_variation_setting' );
					
					if ( 1 === intval( $variation_setting ) ) {
						
						// Get data from this variation
						$data = $this->get_product_data_by_product( $product );
					} else {

						// Get data from parent product (variable)
						$data = $this->get_product_data_by_product_id( $product->get_parent_id() );
					}

				} else { // It's a simple product

					// Get data from simple product
					$data = $this->get_product_data_by_product( $product );
				}

			} else { // It's a variable product

				// variable product
				$parent_data = $this->get_product_data_by_product( $product );
				$unique_data = null;
				$all_variations_have_the_same_data = true;

				foreach ( $product->get_children() as $variation_id ) {
					$child_data = $this->get_product_data( null, $variation_id, $parent_data );

					if ( is_null( $unique_data ) ) {
						$unique_data = $child_data;
					} else {
						if ( $unique_data !== $child_data ) {
							$all_variations_have_the_same_data = false;
							break;
						}
					}
				}

				if ( $all_variations_have_the_same_data ) {
					$data = $unique_data;
				}
				
			}

		} else { // variation quick

			$variation_setting = get_post_meta( $variation_id, '_german_market_charging_device_variation_setting', true );
			
			if ( 1 === intval( $variation_setting ) ) {
				$data = $this->get_product_data_by_product_id( $variation_id );
			} else {
				$data = $parent_data;
			}
		}

		return $data;

	}

	/**
	* Get markup by product
	* 
	* @param WC_Product $product
	* @param Integer $variation_id
	* @return String
	*/
	public function get_markup_by_product( $product, $variation_id = null ) {

		if ( 'test' === $product ) {
			
			$data = array(
				'show_markup' => true,
				'device_included' => 'no',
				'power_min' => 10,
				'power_max' => 20,
				'usb_pd' => true,
			);

		} else {
			$data = $this->get_product_data( $product, $variation_id );
		}

		$show_markup = isset( $data[ 'show_markup' ] ) ? $data[ 'show_markup' ] : false;
		$markup = '';
		
		if ( $show_markup ) {

			$device_included = isset( $data[ 'device_included' ] ) ? $data[ 'device_included' ] : 'no';
			$device_included_class = 'no' === $device_included ? 'german-market-charging-device-not-included' : 'german-market-charging-device-included';
			$power_min = isset( $data[ 'power_min' ] ) ? $data[ 'power_min' ] : '';
			$power_max = isset( $data[ 'power_max' ] ) ? $data[ 'power_max' ] : '';
			$show_w = ( ! empty( $power_min ) ) && ( ! empty( $power_max ) );
			$show_usb_pd = isset( $data[ 'usb_pd' ] ) ? $data[ 'usb_pd' ] : false;
			$aria_label_included_or_not = 'no' === $device_included ? __( 'Charging device not included', 'woocommerce-german-market' ) : __( 'Charging device included', 'woocommerce-german-market' );
			
			/**
			 * Decimal separator for watt value
			 * 
			 * @since 3.45
			 * @param String wc_get_price_decimal_separator()
			 */
			$decimal_watt_separator = apply_filters( 'german_market_charging_device_decimal_watt_separator', wc_get_price_decimal_separator() );
			
			if ( $decimal_watt_separator !== wc_get_price_decimal_separator() ) {
				$power_max = str_replace( wc_get_price_decimal_separator(), $decimal_watt_separator, $power_max );
				$power_min = str_replace( wc_get_price_decimal_separator(), $decimal_watt_separator, $power_min );
			}

			$str_len_power = strlen( str_replace( $decimal_watt_separator, '', $power_min . $power_max ) );
			$power_separator = $str_len_power < 6 ? ' - ' : '-';

			/**
			 * Min/Max W separator
			 * 
			 * @since 3.45
			 * @param String $power_separator
			 * @param String $power_min
			 * @param String $power_max
			 */
			$power_separator = apply_filters( 'german_market_charging_device_min_max_separator', $power_separator, $power_min, $power_max );

			$aria_label_specs = '';
			if ( $show_w ) {
				$aria_label_specs .= $power_min . $power_separator . $power_max . ' ' . apply_filters( 'german_market_charging_device_symbol_for_w', 'W' );
			}

			if ( $show_usb_pd ) {
				if ( ! empty( $aria_label_specs ) ) {
					$aria_label_specs .= ', ';
				}
				$aria_label_specs .= apply_filters( 'german_market_charging_device_symbol_for_usb_pd', 'USB PD' );
			}

			if ( ! empty( $aria_label_specs ) ) {
				$aria_label_specs = __( 'Specification of the charging device:', 'woocommerce-german-market' ) . ' ' . $aria_label_specs;
			}

			ob_start();
			?>
			<div class="german-market-charging-device">
			    
			    <div role="img" aria-label="<?php esc_attr_e( $aria_label_included_or_not ); ?>" class="german-market-charging-device-graphic <?php esc_attr_e( $device_included_class ); ?>" style="<?php esc_attr_e( $this->get_device_graphic_inline_css() ); ?>">
			        <div class="german-market-charging-device-pin1" style="<?php esc_attr_e( $this->get_device_pin_css( 1 ) ); ?>"></div>
			        <div class="german-market-charging-device-pin2" style="<?php esc_attr_e( $this->get_device_pin_css( 2 ) ); ?>"></div>
			        <div class="german-market-charging-device-first-part" style="<?php esc_attr_e( $this->get_device_first_part_inline_css() ); ?>"></div>
			        <div class="german-market-charging-device-second-part" style="<?php esc_attr_e( $this->get_device_second_part_inline_css() ); ?>"></div>
			    </div>

			    <div role="img" aria-label="<?php esc_attr_e( $aria_label_specs ); ?>" class="german-market-charging-device-graphic german-market-charging-device-no-border" style="<?php esc_attr_e( $this->get_device_graphic_inline_css() ); ?>">
			        
			        <div class="german-market-charging-device-pin1" style="<?php esc_attr_e( $this->get_device_pin_css( 1 ) ); ?>"></div>
			        <div class="german-market-charging-device-pin2" style="<?php esc_attr_e( $this->get_device_pin_css( 2 ) ); ?>"></div>
			        <div class="german-market-charging-device-first-part" style="<?php esc_attr_e( $this->get_device_first_part_inline_css() ); ?>"></div>
			        <div class="german-market-charging-device-second-part" style="<?php esc_attr_e( $this->get_device_second_part_inline_css() ); ?>">
			        	
			        	<?php if ( $show_w ) { ?>
				        	<span class="german-market-charging-device-power german-market-charging-device-power-elem">
				        		<span class="german-market-charging-device-power-min"><?php esc_attr_e( $power_min ); ?></span><span class="german-market-charging-device-power-separator"><?php esc_attr_e( $power_separator ); ?></span><span class="german-market-charging-device-power-max"><?php esc_attr_e( $power_max ); ?></span>
				        	</span>
				        	
				        	<span class="german-market-charging-device-w german-market-charging-device-power-elem"><?php esc_attr_e( apply_filters( 'german_market_charging_device_symbol_for_w', 'W' ) ); ?></span>
				        <?php } ?>

			        	<?php if ( $show_usb_pd ) { ?>
			        		<span class="german-market-charging-device-usb-pd german-market-charging-device-power-elem" style="<?php esc_attr_e( $this->get_device_usb_pd_css() ); ?>"><?php esc_attr_e( apply_filters( 'german_market_charging_device_symbol_for_usb_pd', 'USB PD' ) ); ?></span>
			        	<?php } ?>
			        </div>
			    </div>
			</div>
			<?php
			$markup = ob_get_clean();
		}

		return $markup;
	}

	/**
	* Add Product Options in Backend
	*
	* @access public
	* @wp-hook woocommerce_product_after_variable_attributes
	* @param $integer $loop
	* @param Array $variation_data
	* @param Array $variation
	* @return void
	*/
	public function product_options_variation( $loop = NULL, $variation_data = NULL, $variation = NULL ) {

		$name_suffix = '_variable[' . $loop . ']';
		$id = $variation->ID;
		?>

		<div class="german-market-charging-device-settings" style="border: 1px solid #eee; padding: 10px; box-sizing: border-box; margin-top: 10px;">

			<strong><?php echo __( 'Charging Device', 'woocommerce-german-market' ); ?>:</strong>

			<?php
			
			$variation_setting = intval( 
				 apply_filters( 
				 	'german_market_get_post_meta_value_translatable',
				 	get_post_meta( $id, '_german_market_charging_device_variation_setting', true ),
				 	$id,
				 	'_german_market_charging_device_variation_setting'
				 )
			);
			$variation_setting = 0 === $variation_setting ? -1 : $variation_setting;

			woocommerce_wp_select( array(
				'id'      => '_german_market_charging_device_variation_setting' . $name_suffix,
				'value'   => $variation_setting,
				'label'   => __( 'Used Setting', 'woocommerce-german-market' ) . ':',
				'class'   => 'german-market-variable-charging-device german-market-variation-input-not-translatable',
				'style'   => 'width: 350px;',
				'options' => array(
					-1 => __( 'Same as parent', 'woocommerce-german-market' ),
					1 => __( 'Following Special Variation Setting', 'woocommerce-german-market' )
				),
				'default' => -1,
			) );

			$style = -1 === $variation_setting ? 'display: none;' : '';

			?>
			<div class="german-market-charging-device-special-variation-settings" style="<?php echo $style; ?>">
				<?php $this->get_backend_fields( $id, $name_suffix, 'german-market-variation-input-not-translatable' ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Output backend fields, used by simple products and variations
	 * 
	 * @param Integer $product_id
	 * @param String $name_suffix
	 */
	public function get_backend_fields( $product_id, $name_suffix = '', $extra_class = '' ) {

		woocommerce_wp_select( 
			array(
				'id'     			=> '_german_market_charging_device_pictogram' . $name_suffix,
				'value'   			=> apply_filters( 'german_market_get_post_meta_value_translatable', get_post_meta( $product_id, '_german_market_charging_device_pictogram', true ), $product_id, '_german_market_charging_device_pictogram' ),
				'label'   			=> __( 'Pictogram', 'woocommerce-german-market' ) . ':',
				'style'   			=> 'width: 350px;',
				'options' => array(
					'no' => __( 'No pictogram', 'woocommerce-german-market' ),
					'not-included' => __( 'No charging device is included', 'woocommerce-german-market' ),
					'included' => __( 'Charging device is included', 'woocommerce-german-market' ),
				),
				'default' 			=> 'no',
				'desc_tip' 			=> true,
				'description'  		=> __( 'Specify whether a pictogram should be displayed for a charging device included / not included in the scope of delivery. If a charging device is included, a charging plug that is not crossed-out is displayed. If no charging device is included, a crossed-out charging plug is shown.', 'woocommerce-german-market' ),
				'class'		 		=> $extra_class,
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => '_german_market_charging_device_minimum_power' . $name_suffix,
				'label'       => __( 'Minimum power (W):', 'woocommerce-german-market' ),
				'data_type'   => 'decimal',
				'value'       => apply_filters( 'german_market_get_post_meta_value_translatable', get_post_meta( $product_id, '_german_market_charging_device_minimum_power', true ), $product_id, '_german_market_charging_device_minimum_power' ),
				'desc_tip' 	  => true,
				'description' => __( 'Specify he numerical value of the minimum power required to charge the device, which a charging device must supply to charge the device.', 'woocommerce-german-market' ),
				'class'		  => $extra_class,
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => '_german_market_charging_device_maximum_power' . $name_suffix,
				'label'       => __( 'Maximum power (W):', 'woocommerce-german-market' ),
				'data_type'   => 'decimal',
				'value'       => apply_filters( 'german_market_get_post_meta_value_translatable', get_post_meta( $product_id, '_german_market_charging_device_maximum_power', true ), $product_id, '_german_market_charging_device_maximum_power' ),
				'desc_tip' 	  => true,
				'description' => __( 'Specify the maximum power required by the device to achieve maximum charging speed, which determines the power that a charging device needs to supply at least to achieve that maximum charging speed.', 'woocommerce-german-market' ),
				'class'		  => $extra_class,
			)
		);

		$checkbox_meta = apply_filters( 'german_market_get_post_meta_value_translatable', get_post_meta( $product_id, '_german_market_charging_device_usb_pd', true ), $product_id, '_german_market_charging_device_usb_pd' );
		woocommerce_wp_checkbox(
			array(
				'id'            => '_german_market_charging_device_usb_pd' . $name_suffix,
				'label'       	=> __( 'Show "USB PD"', 'woocommerce-german-market' ),
				'value'         => $checkbox_meta  ? 'yes' : 'no',
				'wrapper_class' => '',
				'description'   => __( 'The abbreviation USB PD (USB Power Delivery) shall be displayed if the device supports that charging communication protocol.', 'woocommerce-german-market' ),
				'desc_tip' 	  	=> true,
				'default'		=> 'no',
				'class'		  	=> $extra_class,
			)
		);
	}

	/**
	* Backend product tab for charging device
	*
	* @access public
	* @wp-hook woocommerce_product_data_tabs
	* @param Array $meta_keys
	* @return Array
	*/
	public function backend_product_tab( $tabs ) {

		$tabs[ 'german_market_charging_device' ] = array(
				'label'  => __( 'Charging Device', 'woocommerce-german-market' ),
				'target' => 'german_market_charging_device',
		);

		return $tabs;
	}

	/**
	* Render backend product tab for GPSR
	*
	* @wp-hook woocommerce_product_data_panels
	* @return void
	*/
	public function backend_product_tab_write_panel() {
		?>
		<div id="german_market_charging_device" class="panel woocommerce_options_panel age-rating" style="display: block; ">
			<div class="options_group">
				<?php $this->get_backend_fields( get_the_ID() ); ?>
			</div>
		</div>
		<?php
	}

	/**
	* Save Meta Data in Product
	*
	* @access public
	* @wp-hook german_market_add_process_product_meta_meta_keys
	* @param Array $meta_keys
	* @return Array
	*/
	public function save_product_options( $meta_keys ) {
		$meta_keys[ '_german_market_charging_device_variation_setting' ] = -1;
		$meta_keys[ '_german_market_charging_device_pictogram' ] = 'no';
		$meta_keys[ '_german_market_charging_device_minimum_power' ] = '';
		$meta_keys[ '_german_market_charging_device_maximum_power' ] = '';
		$meta_keys[ '_german_market_charging_device_usb_pd' ] = 'no';
		return $meta_keys;
	}
}
