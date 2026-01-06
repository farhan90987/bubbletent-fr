<?php

class WGM_Product_GPSR {

	public static $options = array();

	public static function init() {

		add_action( 'woocommerce_product_data_tabs', array( __CLASS__, 'general_product_safety_regulation_tab' ), 11 );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'general_product_safety_regulation_write_panel' ) );
		add_filter( 'german_market_add_process_product_meta_meta_keys', array( __CLASS__, 'general_product_safety_regulation_save_meta' ) );

		// GPSR automatic output product summary
		self::$options[ 'german_market_gpsr_automatic_output_manufacturer_and_responsible_person' ] = get_option( 'german_market_gpsr_automatic_output_manufacturer_and_responsible_person', 'summary' );
		self::$options[ 'german_market_gpsr_automatic_output_warnings_and_safety_information' ] = get_option( 'german_market_gpsr_automatic_output_warnings_and_safety_information', 'tab' );

		if ( 'summary' === self::$options[ 'german_market_gpsr_automatic_output_manufacturer_and_responsible_person' ] ) {
			$manufacturer_and_responsible_person_automatic_output_prio = get_option( 'german_market_gpsr_automatic_output_manufacturer_and_responsible_person_prio', 45 );
			add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'general_product_safety_regulation_manufacturer_and_responsible_person' ), $manufacturer_and_responsible_person_automatic_output_prio ); 
		}

		if ( 'summary' === self::$options[ 'german_market_gpsr_automatic_output_warnings_and_safety_information' ] ) {
			$warnings_automatic_output_prio = get_option( 'german_market_gpsr_automatic_output_warnings_and_safety_information_prio', 45 );
			add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'general_product_safety_regulation_warnings' ), $warnings_automatic_output_prio ); 
		}

		add_action( 'woocommerce_product_tabs', array( __CLASS__, 'general_product_safety_regulation_add_product_tab' ) );
		
		add_action( 'woocommerce_product_bulk_edit_end', array( __CLASS__, 'product_bulk_edit' ) );
		add_action( 'woocommerce_product_bulk_edit_save', array( __CLASS__, 'product_bulk_save' ) );
	}

	/**
	 * Save bulk
	 * 
	 * @wp-hook woocommerce_product_bulk_edit_save
	 * @param WC_Product $product
	 * @return void
	 */
	public static function product_bulk_save( $product ) {

		if ( ! ( is_object( $product ) && method_exists( $product, 'update_meta_data' ) ) ) {
			return;
		}

		$keys = array(
			'manufacturer',
			'responsible_person',
			'warnings_and_safety_information',
		);

		$did_update = false;

		foreach ( $keys as $key ) {

			$change_meta_key = 'change_german_market_gpsr_' . $key;
			$needs_update = isset( $_REQUEST[ $change_meta_key ] ) ? absint( $_REQUEST[ $change_meta_key ] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( 1 === $needs_update ) {
				$meta_key = '_german_market_gpsr_' . $key;
				$new_value = isset( $_REQUEST[ $meta_key ] ) ? wp_kses_post( $_REQUEST[ $meta_key ] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					
				$product->update_meta_data( $meta_key, $new_value );
				$did_update = true;
			}
		}

		if ( $did_update ) {
			$product->save_meta_data();
		}
	}

	/**
	 * Product bulk edit
	 * 
	 * @wp-hook woocommerce_product_bulk_edit_end
	 * @return void
	 */ 
	public static function product_bulk_edit() {

		$text_area_change_options = array(
			''  => __( '— No change —', 'woocommerce-german-market' ),
			'1' => __( 'Change to:', 'woocommerce-german-market' ),
		);

		$labels = array(
			'manufacturer' 							=> get_option( 'german_market_gpsr_label_manufacturer', __( 'Manufacturer', 'woocommerce-german-market' ) ),
			'responsible_person' 				=> get_option( 'german_market_gpsr_label_responsible_person', __( 'Responsible person', 'woocommerce-german-market' ) ),
			'warnings_and_safety_information' 	=> get_option( 'german_market_gpsr_label_warnings_and_safety_information', __( 'Warnings and safety information', 'woocommerce-german-market' ) ),
		);

		foreach ( $labels as $meta_key => $nicename ) {
			?>
			<div class="inline-edit-group manufacturer">
				
				<label class="alignleft">
					<span class="title"><?php echo esc_attr( $nicename ); ?></span>
					<span class="input-text-wrap">
							<select class="change_german_market_gpsr_<?php echo esc_attr( $meta_key ); ?> change_to" name="change_german_market_gpsr_<?php echo esc_attr( $meta_key ); ?>">
								<?php
								foreach ( $text_area_change_options as $option_key => $value ) {
									echo '<option value="' . esc_attr( $option_key ) . '">' . esc_html( $value ) . '</option>';
								}
								?>
							</select>
						</span>
				</label>
				
				<label class="change-input">
					<textarea class="short" name="_german_market_gpsr_<?php echo esc_attr( $meta_key ); ?>" placeholder="" rows="4" cols="20"></textarea>
				</label>

			</div>
		<?php
		}
	}

	/**
	 * Add product tab for GPSR
	 * The tab is available if there is any output in the tab, depending on the settings
	 * 
	 * @wp-hook woocommerce_product_tabs
	 * @return $tabs
	 */
	public static function general_product_safety_regulation_add_product_tab ( $tabs ) {

		if ( is_admin() ) {

			// Compatibility for WooCommerce Tab Manager
			if ( function_exists( 'init_woocommerce_tab_manager' ) ) {

				$tabs[ 'gm_rpsr' ] = array(
					'title' 	=> get_option( 'german_market_gpsr_tab_name', __( 'Product safety', 'woocommerce-german-market' ) ),
					'priority' 	=> 28,
					'callback' 	=> array( __CLASS__, 'general_product_safety_regulation_render_product_tab' )
				);

			}

			return $tabs;
		}

		global $product;

		if ( ! $product ) {
			return $tabs;
		}

		$manufacturer_and_responsible_person_in_tab = 'tab' === self::$options[ 'german_market_gpsr_automatic_output_manufacturer_and_responsible_person' ];
		$warnings_in_tab = 'tab' === self::$options[ 'german_market_gpsr_automatic_output_warnings_and_safety_information' ];
		$manufacturer_and_responsible_person_is_empty = empty( self::get_general_product_safety_regulation( $product, array( 'manufacturer', 'responsible_person' ) ) );
		$warnings_is_empty = empty( self::get_general_product_safety_regulation( $product, 'warnings_and_safety_information' ) );

		if ( 
			( $manufacturer_and_responsible_person_in_tab && ( ! $manufacturer_and_responsible_person_is_empty ) ) ||
			( $warnings_in_tab && ( ! $warnings_is_empty ) )
		) {

			$tabs[ 'gm_rpsr' ] = array(
				'title' 	=> get_option( 'german_market_gpsr_tab_name', __( 'Product safety', 'woocommerce-german-market' ) ),
				'priority' 	=> 28,
				'callback' 	=> array( __CLASS__, 'general_product_safety_regulation_render_product_tab' )
			);
		}

		return $tabs;
	}	

	/**
	 * Render tab content for GPSR
	 * 
	 * @return $void
	 */
	public static function general_product_safety_regulation_render_product_tab() {

		global $product;
		?><span class="german-market-tab-gpsr"><?php

			if ( 'tab' === self::$options[ 'german_market_gpsr_automatic_output_manufacturer_and_responsible_person' ] ) {
				echo wp_kses_post( self::get_general_product_safety_regulation( $product, array( 'manufacturer', 'responsible_person' ), true, 'tab' ) );
			}

			if ( 'tab' === self::$options[ 'german_market_gpsr_automatic_output_warnings_and_safety_information' ] ) {
				echo wp_kses_post( self::get_general_product_safety_regulation( $product, 'warnings_and_safety_information' ), true, 'tab' );
			}

		?></span><?php

	}

	/**
	 * Output GPSR data for manufacturer and responsible person in woocommerce_single_product_summary
	 * 
	 * @wp-hook woocommerce_single_product_summary
	 * @return void
	 */
	public static function general_product_safety_regulation_manufacturer_and_responsible_person() {
		global $product;
		?><span class="german-market-summary-gpsr"><?php
			echo wp_kses_post( self::get_general_product_safety_regulation( $product, array( 'manufacturer', 'responsible_person' ), true, 'summary' ) );
		?></span><?php
	}

	/**
	 * Output GPSR data for warnings and safety information in woocommerce_single_product_summary
	 * 
	 * @wp-hook woocommerce_single_product_summary
	 * @return void
	 */
	public static function general_product_safety_regulation_warnings() {
		global $product;
		?><span class="german-market-summary-gpsr"><?php
			echo wp_kses_post( self::get_general_product_safety_regulation( $product, 'warnings_and_safety_information' ), true, 'summary' );
		?></span><?php
	}

	/**
	* Save mata data for GPSR
	*
	* @access public
	* @wp-hook german_market_add_process_product_meta_meta_keys
	* @param Array $meta_keys
	* @return Array
	*/
	public static function general_product_safety_regulation_save_meta( $meta_keys ) {
		$meta_keys[ '_german_market_gpsr_manufacturer' ] = '';
		$meta_keys[ '_german_market_gpsr_responsible_person' ] = '';
		$meta_keys[ '_german_market_gpsr_warnings_and_safety_information' ] = '';
		$meta_keys[ '_german_market_gpsr_ignore_defaults' ] = '';
		return $meta_keys;
	}

	/**
	* Backend product tab for GPSR
	*
	* @access public
	* @wp-hook german_market_add_process_product_meta_meta_keys
	* @param Array $meta_keys
	* @return Array
	*/
	public static function general_product_safety_regulation_tab( $tabs ) {

		$tabs[ 'german_market_gpsr' ] = array(
				'label'  => __( 'General Product Safety Regulation', 'woocommerce-german-market' ),
				'target' => 'german_market_general_product_safety_regulation',
		);

		return $tabs;
	}

	/**
	* Render backend product tab for GPSR
	*
	* @wp-hook woocommerce_product_data_panels
	* @return void
	*/
	public static function general_product_safety_regulation_write_panel() {

		$product = wc_get_product( get_the_ID() );?>
		
		<div id="german_market_general_product_safety_regulation" class="panel woocommerce_options_panel age-rating" style="display: block; ">

			<div class="options_group">
				<?php
				
				$new_product = ( ! isset( $_REQUEST[ 'post' ] ) );

				$default_manufacturer = $product->get_meta( '_german_market_gpsr_manufacturer' );
				$default_responsible_person = $product->get_meta( '_german_market_gpsr_responsible_person' );
				$default_warnings_and_safety_information = $product->get_meta( '_german_market_gpsr_warnings_and_safety_information' );

				if ( 
					( 'on' === get_option( 'german_market_gpsr_pre_fill_only_new_producs', 'on' ) && $new_product ) ||
					( 'off' === get_option( 'german_market_gpsr_pre_fill_only_new_producs', 'on' ) )
				) {

					$default_manufacturer = empty( $default_manufacturer ) ? get_option( 'german_market_gpsr_default_manufacturer', '' ) : $default_manufacturer;
					$default_responsible_person = empty( $default_responsible_person ) ? get_option( 'german_market_gpsr_default_responsible_person', '' ) : $default_responsible_person;
					$default_warnings_and_safety_information = empty( $default_warnings_and_safety_information ) ? get_option( 'german_market_gpsr_default_warnings_and_safety_information', '' ) : $default_warnings_and_safety_information;
				}

				woocommerce_wp_checkbox(
					array(
						'id'            => '_german_market_gpsr_ignore_defaults',
						'value'         => apply_filters( 'german_market_get_post_meta_value_translatable', $product->get_meta( '_german_market_gpsr_ignore_defaults' ), $product->get_id(), '_german_market_gpsr_ignore_defaults' ) ? 'yes' : 'no',
						'wrapper_class' => '',
						'label'         => __( 'Never use default values in frontend if a field is empty', 'woocommerce-german-market' ),
						'description'   => '',
					)
				);

				woocommerce_wp_textarea_input(
					array(
						'id'          => '_german_market_gpsr_manufacturer',
						'value'       => apply_filters( 'german_market_get_post_meta_value_translatable', $default_manufacturer, $product->get_id(), '_german_market_gpsr_manufacturer' ),
						'label'       => get_option( 'german_market_gpsr_label_manufacturer', __( 'Manufacturer', 'woocommerce-german-market' ) ),
						'desc_tip'    => true,
						'description' => __( 'Name, trademark, address and electronic address (url of the website or e-mail address)', 'woocommerce-german-market' ),
					)
				);

				woocommerce_wp_textarea_input(
					array(
						'id'          => '_german_market_gpsr_responsible_person',
						'value'       => apply_filters( 'german_market_get_post_meta_value_translatable', $default_responsible_person, $product->get_id(), '_german_market_gpsr_responsible_person' ),
						'label'       => get_option( 'german_market_gpsr_label_responsible_person', __( 'Responsible person', 'woocommerce-german-market' ) ),
						'desc_tip'    => true,
						'description' => __( 'If the manufacturer is not established in the EU: name, address and electronic address (url of the website or e-mail address) of an economic operator established in the EU who is responsible for the manufacturer\'s obligations.', 'woocommerce-german-market' ),
					)
				);


				?>

				<div class="german_market_gpsr_warnings_and_safety_informatio-label">
					<span><?php echo esc_attr( get_option( 'german_market_gpsr_label_warnings_and_safety_information', __( 'Warnings and safety information', 'woocommerce-german-market' ) ) ); ?></span>
				</div>
				<div class="german_market_gpsr_warnings_and_safety_informatio-editor">

					<?php
					wp_editor( 
						htmlspecialchars_decode( $default_warnings_and_safety_information, ENT_QUOTES ),
						'_german_market_gpsr_warnings_and_safety_information',
						array(
							'wpautop'       => true,
						    'media_buttons' => true,
						    'textarea_name' => '_german_market_gpsr_warnings_and_safety_information',
						    'editor_height' => '400',
						)

					);
					?>
				</div>
			</div>

		</div>
		<?php
	}

	/**
	 * Get GPSR data, formatted
	 * 
	 * @param WC_Product $prouct
	 * @param String|Array $type
	 * @param Boolean $show_labels
	 */
	public static function get_general_product_safety_regulation( $product, $type = 'all', $show_labels = true, $content = '' ) {

		$data = '';

		$meta_keys = array(
			'manufacturer' => '_german_market_gpsr_manufacturer',
			'responsible_person' => '_german_market_gpsr_responsible_person',
			'warnings_and_safety_information' => '_german_market_gpsr_warnings_and_safety_information'
		);

		$used_meta_keys = array();

		if ( 'all' === $type ) {
			$used_keys = $meta_keys;
		} else if ( is_string( $type ) && isset( $meta_keys[ $type ] ) ) {
			$used_meta_keys[ $type ] = $meta_keys[ $type ];
		} else if ( is_array( $type ) ) {
			foreach ( $type as $value ) {
				if ( isset( $meta_keys[ $value ] ) ) {
					$used_meta_keys[ $value ] = $meta_keys[ $value ];
				}
			}
		}

		$labels = array(
			'manufacturer' 							=> get_option( 'german_market_gpsr_label_manufacturer', __( 'Manufacturer', 'woocommerce-german-market' ) ),
			'responsible_person' 				=> get_option( 'german_market_gpsr_label_responsible_person', __( 'Responsible person', 'woocommerce-german-market' ) ),
			'warnings_and_safety_information' 	=> get_option( 'german_market_gpsr_label_warnings_and_safety_information', __( 'Warnings and safety information', 'woocommerce-german-market' ) ),
		);

		foreach ( $used_meta_keys as $key => $value ) {

			if ( is_object( $product ) && method_exists( $product, 'get_meta' ) ) {

				$never_use_default_values = $product->get_meta( '_german_market_gpsr_ignore_defaults' );

				if ( isset( $used_meta_keys[ $key ] ) ) {
					$meta_value = $product->get_meta( $used_meta_keys[ $key ] );
					if ( empty( $meta_value ) ) {
						if ( 'yes' !== $never_use_default_values ) {
							if ( 'on' === get_option( 'german_market_gpsr_use_default_in_frontend_if_empty_value', 'off' ) ) {
								$meta_value = get_option( 'german_market_gpsr_default_' . $key, '' );
							}
						}
					}

					$value = $meta_value;

					/**
					 * Filter value (GPSR data) that will be outputed
					 * 
					 * Here is an example if you want to be able to use shortcodes:
					 * 
					 * add_filter( 'german_market_gpsr_meta_value', function( $value, $product, $key ) {
					 *		return do_shortcode( $value );
					 * }, 10, 3 );
					 *
					 * @since 1.0
					 * @param String $value
					 * @param WC_Product $product
					 * @param String $key
					 */
					$value = apply_filters( 'german_market_gpsr_meta_value', $value, $product, $key );
					
					if ( ! empty( $value ) ) {
						
						$data .= '<span class="german-market-gpsr german-market-gpsr-' . esc_attr( $key ) . '">';
						
						/**
						 * Should label be shown
						 * 
						 * @since 1.0
						 * @param Boolean $show_labels
						 * @param String $key
						 * @param String $content ('tab', 'summary', 'product-block', 'shortcode')
						 * @param WC_Product $product
						 */
						$show_labels = apply_filters( 'german_market_gpsr_show_labels', $show_labels, $key, $content, $product );
						
						if ( $show_labels ) {
							$data .= '<span class="german-market-gpsr-label german-market-gpsr-label-' . esc_attr( $key ) .'">';
							$data .= esc_attr( isset( $labels[ $key ] ) ? $labels[ $key ] : '' );

							/**
							 * Filter separator
							 * 
							 * @since 1.0
							 * @param String $separator
							 */
							$data .= apply_filters( 'german_market_gpsr_label_saparator', ': ' );
							$data .= '</span>';
						}
						
						if ( 'warnings_and_safety_information' === $key ) {
							$data .= '<span class="german-market-gpsr-content german-market-gpsr-content-' . esc_attr( $key ) .'">' . wpautop( wp_kses_post( do_shortcode( $value ) ) ) . '</span>';
						} else {
							$data .= '<span class="german-market-gpsr-content german-market-gpsr-content-' . esc_attr( $key ) .'">' . wp_kses_post( nl2br( $value ) ) . '</span>';
						}
						
						$data .= '</span>';
						
					}
				}
			}
		}

		return $data;
	}
}
