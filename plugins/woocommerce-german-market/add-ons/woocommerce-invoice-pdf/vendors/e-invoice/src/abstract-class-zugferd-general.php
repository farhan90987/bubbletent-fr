<?php

namespace MarketPress\German_Market\E_Invoice;

use MarketPress\German_Market\horstoeko\zugferd\ZugferdDocumentBuilder;
use MarketPress\German_Market\horstoeko\zugferd\ZugferdProfiles;
use MarketPress\German_Market\horstoeko\zugferd\codelists\ZugferdPaymentMeans;
use MarketPress\German_Market\horstoeko\zugferd\codelists\ZugferdInvoiceType;
use MarketPress\German_Market\horstoeko\zugferd\ZugferdDocumentReader;
use MarketPress\German_Market\horstoeko\zugferd\codelists\ZugferdVATExemptionReasonCode;

abstract class E_Invoice_General extends E_Invoice_Manager {

	public $zugferd_document;
	public $order;
	public $order_to_get_buyer;
	public $order_type;
	public $position_counter = 0;
	public $net_total = 0;
	public $tax_net_parts = array();
	public $is_frontend;
	protected $has_saved_xml;
	protected $saved_meta_data = '';

	/**
	* Construct
	* 
	* @return void
	*/
	public function __construct( $order, $is_frontend = false ) {
		
		$this->order = $order;
		$this->order_type = 'shop_order_refund' === $this->order->get_type() ? 'refund' : 'order';
		$this->order_to_get_buyer = 'refund' === $this->order_type ? wc_get_order( $order->get_parent_id() ) : $order;
		$this->is_frontend = $is_frontend;

		if ( ! $this->has_saved_xml() ) {
			$this->zugferd_document = ZugferdDocumentBuilder::CreateNew(ZugferdProfiles::PROFILE_EXTENDED);
			$this->build_zugferd_document();
		}
	}

	/**
	 * Get filename for XML
	 * 
	 * @return String
	 */
	public function get_filename() {

		$filename = '';

		if ( 'order' === $this->order_type ) {

			if ( ! $this->is_frontend ) {
				$filename = \WP_WC_Invoice_Pdf_Email_Attachment::repair_filename( apply_filters( 'wp_wc_invoice_pdf_backend_filename', get_option( 'wp_wc_invoice_pdf_file_name_backend', __( 'Invoice-{{order-number}}', 'woocommerce-german-market' ) ), $this->order ) );
			} else {
				$filename = \WP_WC_Invoice_Pdf_Email_Attachment::repair_filename( apply_filters( 'wp_wc_invoice_pdf_frontend_filename', get_option( 'wp_wc_invoice_pdf_file_name_frontend', get_bloginfo( 'name' ) . '-' . __( 'Invoice-{{order-number}}', 'woocommerce-german-market' ) ), $this->order ) );
			}

			$file_name_placeholders = apply_filters( 'wp_wc_invoice_pdf_placeholders', array( 'order-number' => __( 'Order number', 'woocommerce-german-market' ) ) );
			foreach( $file_name_placeholders as $key => $value ) {
				$search[] 	= '{{' . $key . '}}';
				if ( $key == 'order-number' ) {
					$replace[] = $this->order->get_order_number();
				} else {
					// how to replace the custom placeholder
					$replace[] = apply_filters( 'wp_wc_invoice_pdf_placeholder_' . $key, $value, $key, $this->order );
				}
			}
			$filename = str_replace( $search, $replace, $filename );
		
		} else if ( 'refund' === $this->order_type ) {

			if ( ! $this->is_frontend ) {
				
				// get filename
				$filename = get_option( 'wp_wc_invoice_pdf_refund_file_name_backend', 'Refund-{{refund-id}} for order {{order-number}}' );
				// replace {{refund-id}}, the other placeholders will be managed by the class WP_WC_Invoice_Pdf_Create_Pdf
				$filename = str_replace( '{{refund-id}}', $this->order->get_id(), $filename );
				$filename = apply_filters( 'wp_wc_invoice_pdf_refund_backend_filename', $filename, $this->order );

			} else {
				
				$filename = get_option( 'wp_wc_invoice_pdf_refund_file_name_frontend', 'Refund-{{refund-id}} for order {{order-number}}' );
				// replace {{refund-id}}, the other placeholders will be managed by the class WP_WC_Invoice_Pdf_Create_Pdf
				$filename = str_replace( '{{refund-id}}', $this->order->get_id(), $filename );
				$filename = \WP_WC_Invoice_Pdf_Email_Attachment::repair_filename( apply_filters( 'wp_wc_invoice_pdf_refund_frontend_filename', $filename, $this->order ) );
			}

			$file_name_placeholders = apply_filters( 'wp_wc_invoice_pdf_placeholders', array( 'order-number' => __( 'Order number', 'woocommerce-german-market' ) ) );
			foreach( $file_name_placeholders as $key => $value ) {
				$search[] 	= '{{' . $key . '}}';
				if ( $key == 'order-number' ) {
					$replace[] = $this->order_to_get_buyer->get_order_number();
				} else {
					// how to replace the custom placeholder
					$replace[] = apply_filters( 'wp_wc_invoice_pdf_placeholder_' . $key, $value, $key, $this->order_to_get_buyer->order );
				}
			}
			
			$filename = str_replace( $search, $replace, $filename );
		}

		/**
		 * Prefix for xml filename
		 * 
		 * @param String $prefix
		 * @param WC_Order $order
		 */
		$prefix = apply_filters( 'german_market_zugferd_filename_prefix', '', $this->order );

		return $prefix . $filename;
	}

	/**
	 * Checks if order has savex xml and saves meta data in class object
	 * 
	 * @return Boolean
	 */
	public function has_saved_xml() {

		$this->has_saved_xml = false;
		
		$always_create_new_pdf_status = apply_filters( 'wp_wc_invoice_pdf_always_create_new_pdf_status', array( 'pending', 'processing', 'on-hold' ) );
		$create_new_but_dont_save = apply_filters( 'wp_wc_invoice_pdf_create_new_but_dont_save', false, $this->order, array() );

 		$meta_data = new E_Invoice_Meta_Data( $this->order );
		
		if ( 'order' === $this->order_type ) {
			
			if ( in_array( $this->order->get_status(), $always_create_new_pdf_status ) ) {
				$meta_data->delete_meta();
			} else {
				$saved_meta_data = $meta_data->get_meta();
				if ( '' !== trim( $saved_meta_data ) && ( ! $create_new_but_dont_save ) ) {
					$this->has_saved_xml = true;
					$this->saved_meta_data = $saved_meta_data;
				}
			}

		} else if ( 'refund' === $this->order_type ) {

			if ( '' !== trim( $this->saved_meta_data ) && ( ! $create_new_but_dont_save ) ) {
				$this->has_saved_xml = true;
				$this->saved_meta_data = $saved_meta_data;
			}

		}

		return $this->has_saved_xml;
	}

	/**
	 * Returns zugferd document
	 * 
	 * @return Object
	 */
	abstract function build_zugferd_document();

	/**
	 * Get XML
	 * From abstract class E_Invoice_Manager
	 * 
	 * @return String
	 */	
	public function get_xml() {
		
		$xml = null;

		if ( $this->has_saved_xml ) {
			$xml = $this->saved_meta_data;
		} else {
			$xml = $this->zugferd_document->getContent();

			// now save xml
			$create_new_but_dont_save = apply_filters( 'wp_wc_invoice_pdf_create_new_but_dont_save', false, $this->order, array() );
			$always_create_new_pdf_status = apply_filters( 'wp_wc_invoice_pdf_always_create_new_pdf_status', array( 'pending', 'processing', 'on-hold' ) );

			if ( 'order' === $this->order_type ) {
				
				if ( ! $create_new_but_dont_save ) {
					if ( ! in_array( $this->order->get_status(), $always_create_new_pdf_status ) ) {
						$meta_data = new E_Invoice_Meta_Data( $this->order );
						$meta_data->add_meta( $xml );
					}
				}

			} else if ( 'refund' === $this->order_type ) {

				if ( ! $create_new_but_dont_save ) {
					$meta_data = new E_Invoice_Meta_Data( $this->order );
					$meta_data->add_meta( $xml );
				}
			}
		}

		return $xml;
	}

	/**
	 * Save File
	 * From abstract class E_Invoice_Manager
	 * 
	 * @return String
	 */	
	public function save_file( $path = null ) {
		
		if ( ! is_null( $path ) ) {
			if ( $this->has_saved_xml ) {
				file_put_contents( $path, $this->saved_meta_data );
			} else {
				$this->zugferd_document->writeFile( $path );
			}	
		}
		
		return $path;
	}

	/**
	 * Return $zugferd_document
	 * 
	 * @return Object
	 */
	public function get_zugferd_document() {
		return $this->zugferd_document;
	}

	/**
	 * Set document information
	 * 
	 * @return void
	 */
	public function set_document_information() {

		$this->zugferd_document->setDocumentInformation(
			$this->get_documentno(),
			$this->get_documenttypecode(), 
			$this->get_documentdate(),
			$this->order->get_currency(),
		);
	}

	/**
	 * Get document date
	 * 
	 * @return String
	 */
	public function get_documentdate() {

		$document_date = $this->order->get_date_created();

		if ( class_exists( 'WP_WC_Running_Invoice_Number_Functions' ) ) {
			$running_invoice_number = new \WP_WC_Running_Invoice_Number_Functions( $this->order );	
			$document_date_string = date_i18n( 'Ymd', intval( $running_invoice_number->get_invoice_timestamp() ) );
			$document_date = new \DateTime( $document_date_string );
		}

		/**
		* Filter document date
		* 
		* @since 1.0
		* @param String $document_date
		* @param WC_Order $order
		*/
		return apply_filters( 'german_market_zugferd_document_date', $document_date, $this->order );
	}

	/**
	 * Get document number
	 * 
	 * @return String
	 */
	public function get_documentno() {

		$document_no = '';

		if ( 'order' === $this->order_type ) {
			$document_no = $this->order->get_order_number();
		} else {
			$document_no = $this->order->get_id();
		}

		if ( class_exists( 'WP_WC_Running_Invoice_Number_Functions' ) ) {
			$running_invoice_number = new \WP_WC_Running_Invoice_Number_Functions( $this->order );	
			$document_no = $running_invoice_number->get_invoice_number();
		}

		/**
		* Filter document number
		* 
		* @since 1.0
		* @param String $document_no
		* @param WC_Order $order
		*/
		return apply_filters( 'german_market_zugferd_document_no', $document_no, $this->order );
	}

	/**
	 * Get document type code
	 * See class class ZugferdInvoiceType
	 * 
	 * @return String
	 */
	public function get_documenttypecode() {
		$documenttypecode = 'order' === $this->order_type ? ZugferdInvoiceType::TAXINVOICE : ZugferdInvoiceType::CORRECTION;

		/**
		* Filter document type code
		* 
		* @since 1.0
		* @param String $documenttypecode
		* @param WC_Order $order
		*/
		return apply_filters( 'german_market_zugferd_documenttypecode', $documenttypecode, $this->order );
	}

	/**
	 * Set buyer information
	 * 
	 * @return void
	 */
	public function set_buyer() {

		$this->zugferd_document->setDocumentBuyer(
			$this->get_buyer_name(), // name
			$this->get_buyer_id(), // id
			$this->get_buyer_description() // description
		);
		
		$this->zugferd_document->setDocumentBuyerAddress( 
			$this->order_to_get_buyer->get_billing_address_1(), // lineone
			$this->order_to_get_buyer->get_billing_address_2(), // linetwo
			null, // linethree
			$this->order_to_get_buyer->get_billing_postcode(), // postcode
			$this->order_to_get_buyer->get_billing_city(), // city
			$this->order_to_get_buyer->get_billing_country(), // country
		);

		$this->zugferd_document->setDocumentBuyerContact( 
			trim( $this->order_to_get_buyer->get_billing_first_name() . ' ' . $this->order_to_get_buyer->get_billing_last_name() ), // contactpersonname
			
			/**
			* Filter buyer contactd department name
			* 
			* @since 1.0
			* @param String $department name
			* @param WC_Order $order
			*/
			apply_filters( 'german_market_zugferd_buyer_contactdepartmentname', __( 'Bookkeeping', 'woocommerce-german-market' ), $this->order_to_get_buyer ), // contactdepartmentname
			$this->order_to_get_buyer->get_billing_phone(), // contactphoneno

			/**
			* Filter buyer fax number
			* 
			* @since 1.0
			* @param String $faxnumber
			* @param WC_Order $order
			*/
			apply_filters( 'german_market_zugferd_buyer_faxno', '', $this->order_to_get_buyer ), // contactfaxno
			$this->order_to_get_buyer->get_billing_email(), // contactemailadd
		);

		$vat_number = $this->order_to_get_buyer->get_meta( 'billing_vat' );
		if ( ! empty( $vat_number ) ) {
			$this->zugferd_document->addDocumentBuyerTaxRegistration( 
				'VA', // taxregtype
				$vat_number // taxregid
			);
		}
	}

	/**
	 * Set shipping to information
	 * 
	 * @return void
	 */
	public function set_shipping_to() {

		if ( ! $this->order_to_get_buyer->needs_shipping_address() ) {
			return;
		}

		$this->zugferd_document->setDocumentShipTo(
			$this->get_shipping_to_name(), // name
			$this->get_shipping_to_id(), // id
			$this->get_shipping_to_description() // description
		);

		$this->zugferd_document->setDocumentShipToAddress( 
			$this->order_to_get_buyer->get_shipping_address_1(), // lineone
			$this->order_to_get_buyer->get_shipping_address_2(), // linetwo
			null, // linethree
			$this->order_to_get_buyer->get_shipping_postcode(), // postcode
			$this->order_to_get_buyer->get_shipping_city(), // city
			$this->order_to_get_buyer->get_shipping_country(), // country
		);

		$this->zugferd_document->setDocumentShipToContact( 
			trim( $this->order_to_get_buyer->get_shipping_first_name() . ' ' . $this->order_to_get_buyer->get_shipping_last_name() ), // contactpersonname
			
			/**
			* Filter shipping to contactd department name
			* 
			* @since 1.0
			* @param String $department name
			* @param WC_Order $order
			*/
			apply_filters( 'german_market_zugferd_shipping_to_contactdepartmentname', '', $this->order_to_get_buyer ), // contactdepartmentname

			$this->order_to_get_buyer->get_shipping_phone(), // contactphoneno
			
			/**
			* Filter shipping to fax number
			* 
			* @since 1.0
			* @param String $faxnumber
			* @param WC_Order $order
			*/
			apply_filters( 'german_market_zugferd_shipping_to_faxno', '', $this->order_to_get_buyer ), // contactfaxno
			
			/**
			* Filter shipping to email
			* 
			* @since 1.0
			* @param String $email
			* @param WC_Order $order
			*/
			apply_filters( 'german_market_zugferd_shipping_to_email', $this->order_to_get_buyer->get_billing_email(), $this->order_to_get_buyer ), // contactemailadd
		);

		$vat_number = $this->order_to_get_buyer->get_meta( 'billing_vat' );
		if ( ! empty( $vat_number ) ) {
			$this->zugferd_document->addDocumentShipToTaxRegistration( 
				'VA', // taxregtype
				$vat_number // taxregid
			);
		}
	}

	/**
	 * Set seller information
	 * 
	 * @return void
	 */ 
	public function set_seller() {

		$this->zugferd_document->setDocumentSeller( get_option( 'german_market_einvoice_company_name'), get_option( 'blogname' ) );

		$global_id = get_option( 'german_market_einvoice_seller_global_id', '' );
		if ( ! empty( $global_id ) ) {
			$this->zugferd_document->addDocumentSellerGlobalId( $global_id, '0088' );
		}

		$tax_registration_fc = get_option( 'german_market_einvoice_seller_tax_registration_fc', '' );
		if ( ! empty( $tax_registration_fc ) ) {
			$this->zugferd_document->addDocumentSellerTaxRegistration( 'FC', $tax_registration_fc );
		}

		$tax_registration_va = get_option( 'german_market_einvoice_seller_tax_registration_va', str_replace( '-', '', get_option( 'german_market_vat_requester_member_state' ) ) . get_option( 'german_market_vat_requester_vat_number' ) );
		if ( ! empty( $tax_registration_va ) ) {
			$this->zugferd_document->addDocumentSellerTaxRegistration( 'VA', $tax_registration_va );
		}
		    
		$this->zugferd_document->setDocumentSellerAddress( 
			WC()->countries->get_base_address(), //lineone
			WC()->countries->get_base_address_2(), //linetwo
			"", // linethree
			WC()->countries->get_base_postcode(), //postcode
			WC()->countries->get_base_city(), // city
			WC()->countries->get_base_country() // country
		);

		$this->zugferd_document->setDocumentSellerContact(
			get_option( 'german_market_einvoice_seller_contact_name', '' ), // contactpersonname
			apply_filters( 'german_market_zugferd_seller_contactdepartmentname', __( 'Bookkeeping', 'woocommerce-german-market' ) ), // contactdepartmentname
			get_option( 'german_market_einvoice_seller_contact_phone', '' ), // contactphoneno
			get_option( 'german_market_einvoice_seller_contact_fax', '' ), // contactfaxno
			get_option( 'german_market_einvoice_seller_contact_email', get_option( 'admin_email' ) ) // contactemailadd
		);

	}

	/**
	 * Get buyer name
	 * 
	 * @return String
	 */
	public function get_buyer_name() {

		$buyer_name = $this->order_to_get_buyer->get_billing_company();
		if ( empty( $buyer_name ) ) {
			$buyer_name = trim( $this->order_to_get_buyer->get_billing_first_name() . ' ' . $this->order_to_get_buyer->get_billing_last_name() );
		}

		/**
		* Filter buyer name
		* 
		* @since 1.0
		* @param String $buyer_name
		* @param WC_Order $order
		*/
		return apply_filters( 'german_market_zugferd_buyer_name', $buyer_name, $this->order_to_get_buyer );
	}

	/**
	 * Get buyer id
	 * 
	 * @return String
	 */
	public function get_buyer_id() {

		$order_user_id = $this->order_to_get_buyer->get_user_id();
		$buyer_id = $order_user_id > 0 ? $order_user_id : null;

		/**
		* Filter buyer id
		* 
		* @since 1.0
		* @param String $buyer_id
		* @param WC_Order $order
		*/
		return apply_filters( 'german_market_zugferd_buyer_id', $buyer_id, $this->order_to_get_buyer );
	}

	/**
	 * Get buyer description
	 * 
	 * @return String
	 */	
	public function get_buyer_description() {
		
		/**
		* Filter buyer description
		* 
		* @since 1.0
		* @param String $buyer_description
		* @param WC_Order $order
		*/
		return apply_filters( 'german_market_zugferd_buyer_description', null, $this->order_to_get_buyer );
	}

	/**
	 * Get shipping to name
	 * 
	 * @return String
	 */
	public function get_shipping_to_name() {

		$shipping_to_name = $this->order_to_get_buyer->get_shipping_company();
		if ( empty( $shipping_to_name ) ) {
			$shipping_to_name = trim( $this->order_to_get_buyer->get_shipping_first_name() . ' ' . $this->order_to_get_buyer->get_shipping_last_name() );
		}

		/**
		* Filter shipping to name
		* 
		* @since 1.0
		* @param String $shipping_to_name
		* @param WC_Order $order
		*/
		return apply_filters( 'german_market_zugferd_shipping_to_name', $shipping_to_name, $this->order_to_get_buyer );
	}

	/**
	 * Get shipping to id
	 * 
	 * @return String
	 */
	public function get_shipping_to_id() {

		$order_user_id = $this->order_to_get_buyer->get_user_id();
		$shipping_to_id = $order_user_id > 0 ? $order_user_id : null;

		/**
		* Filter shipping to id
		* 
		* @since 1.0
		* @param String $shipping_to_id
		* @param WC_Order $order
		*/
		return apply_filters( 'german_market_zugferd_shipping_to_id', $shipping_to_id, $this->order_to_get_buyer );
	}

	/**
	 * Get shipping to description
	 * 
	 * @return String
	 */	
	public function get_shipping_to_description() {
		
		/**
		* Filter shipping to description
		* 
		* @since 1.0
		* @param String $shipping_to_description
		* @param WC_Order $order
		*/
		return apply_filters( 'german_market_zugferd_shipping_to_description', null, $this->order_to_get_buyer );
	}

	/**
	 * Set document positions
	 * Order items, shipping and fees
	 * 
	 * @return void
	 */	
	public function set_positions() {
		$this->add_order_items();
		$this->add_shipping_or_fees( 'shipping' );
		$this->add_shipping_or_fees( 'fees' );
	}

	/**
	 * Add order items to the document
	 * 
	 * @return void
	 */
	public function add_order_items() {

		$this->before_get_items();

		foreach ( $this->order->get_items() as $key => $item ) {
			if ( method_exists( $item, 'get_quantity' ) ) {
				
				$this->position_counter++;

				$short_description = '';
				$sku = '';
				$global_id = '';

				$product = apply_filters( 'woocommerce_order_item_product', $item->get_product(), $item );
				if ( $product && method_exists( $product, 'get_short_description' ) ) {
					$short_description = strip_tags( $product->get_short_description() );
					if ( method_exists( $product, 'get_global_unique_id' ) ) {
						$global_id = $product->get_global_unique_id();
					}
					$sku = $product->get_sku();
				}

				$tax_rate_percent = \WGM_Tax::get_tax_rate_percent_by_item_and_order( $item, $this->order );

				$this->zugferd_document->addNewPosition( $this->position_counter );

			    $this->zugferd_document->setDocumentPositionProductDetails(
			    	strip_tags( apply_filters( 'woocommerce_order_item_name', $item->get_name(), $item, false ) ), // name
			    	$short_description, // description
			    	$sku, // sellerAssignedID
			    	null, // buyerAssignedID
			    	"0160", // globalIDType
			    	$global_id // globalID
			    );

			    // item meta data
			    if ( is_object( $item ) && method_exists( $item, 'get_formatted_meta_data' ) ) {
				    
			    	if ( method_exists( $item, 'get_all_formatted_meta_data' ) ) {
			    		 $meta_data = $item->get_all_formatted_meta_data();
			    	} else {
			    		 $meta_data = $item->get_formatted_meta_data();
			    	}
				   
				    foreach ( $meta_data as $item_meta_data ) {

				    	$display_key = isset( $item_meta_data->display_key) ? $item_meta_data->display_key : '';
				    	if ( empty ( $display_key ) ) {
				    		$display_key = isset( $item_meta_data->key ) ? $item_meta_data->key : '';
				    	}

				    	$display_value = isset( $item_meta_data->display_value) ? $item_meta_data->display_value : '';
				    	if ( empty ( $display_value ) ) {
				    		$display_value = isset( $item_meta_data->value ) ? $item_meta_data->value : '';
				    	}

				    	if ( ( ! empty( $display_key ) ) && ( ! empty( $display_value ) ) ) {
				    		$this->zugferd_document->addDocumentPositionProductCharacteristic( $display_key, preg_replace( "/\r|\n/", "", trim( strip_tags( $display_value ) ) ) );
				    	}
				    }
			    }

			    $this->zugferd_document->setDocumentPositionQuantity( 
			    	$this->prepare_value_for_amount( 
			    		$item->get_quantity()
			    	), 
			    	"H87" 
			    );
			    
			    /**
			     * This is not the gross price as we think
			     * 
			     * "Set the unit price excluding sales tax before deduction of the discount on the item price!""
			     *
			    /*
			    $this->zugferd_document->setDocumentPositionGrossPrice( 
			    	$this->prepare_value_for_amount( 
			    		$this->order->get_item_total( $item, false, false ) // maybe use subtotal
			    	)
			    );
			    */
			    
			    $this->zugferd_document->setDocumentPositionNetPrice( 
			    	$this->prepare_value_for_amount( 
			    		$this->order->get_item_total( $item, false, false ) 
			    	)
			    );

			    if ( ! isset( $this->tax_net_parts[ $tax_rate_percent ] ) ) {
			    	$this->tax_net_parts[ $tax_rate_percent ] = 0.0;
			    }

			    $this->tax_net_parts[ $tax_rate_percent ] += $this->prepare_value_for_amount( $this->order->get_line_total( $item, false, false ) );
			    $this->net_total += $this->prepare_value_for_amount( $this->order->get_line_total( $item, false, false ) );
			    $item_category_code = floatval( $tax_rate_percent ) > 0.0 ? 'S' : $this->get_tax_category_code( 'Z' );

			    /**
				* Filter tax category code for order item
				* 
				* @since 1.0
				* @param String $category_code
				* @param WC_Order_Item $item
				* @param WC_Order $order
				*/
			    $item_category_code = apply_filters( 'german_market_zugferd_get_item_category_code', $item_category_code, $item, $this->order );

			    if ( 'O' === $item_category_code ) {
			   		$tax_rate_percent = null;
			   	}
			    
			    $this->zugferd_document->addDocumentPositionTax( $item_category_code, 'VAT', $tax_rate_percent );
			 	
			   	$this->zugferd_document->setDocumentPositionLineSummation( 
			   		$this->prepare_value_for_amount(
			   			$this->order->get_line_total( $item, false, false ) 
			   		)
			   	);
			}
		}

		$this->after_get_items();
	}

	/**
	 * Add shipping or fees to the document
	 * 
	 * @return void
	 */
	public function add_shipping_or_fees( $type = 'shipping' ) {
		
		$order_shipping_or_fees = \WGM_Tax::get_shipping_or_fee_parts_by_order( $this->order, $type, true, 'net' );

		foreach ( $order_shipping_or_fees as $order_shipping_or_fee ) {
			foreach ( $order_shipping_or_fee as $shipping_or_fee_info ) {

				$this->position_counter++;

				$shipping_name = isset( $shipping_or_fee_info[ 'name' ] ) ? $shipping_or_fee_info[ 'name' ] : $headline_text;
				$shipping_total_net = $this->prepare_value_for_amount(
					isset( $shipping_or_fee_info[ 'net' ] ) ? floatval( $shipping_or_fee_info[ 'net' ] ) : 0.0
				);
				$shipping_total_gross = $this->prepare_value_for_amount(
					isset( $shipping_or_fee_info[ 'gross' ] ) ? floatval( $shipping_or_fee_info[ 'gross' ] ) : 0.0
				);
				$tax_rate_percent = isset( $shipping_or_fee_info[ 'rate_percent' ] ) ? floatval( $shipping_or_fee_info[ 'rate_percent' ] ) : 0.0;
				$category_code = $tax_rate_percent > 0.0 ? 'S' : $this->get_tax_category_code( 'Z' );

				$this->zugferd_document->addNewPosition( $this->position_counter );
			    $this->zugferd_document->setDocumentPositionProductDetails( $shipping_name );
			    $this->zugferd_document->setDocumentPositionQuantity( 1, "H87" );
			    //$this->zugferd_document->setDocumentPositionGrossPrice( $shipping_total_gross ); // this ist not gross as we think
			    $this->zugferd_document->setDocumentPositionNetPrice( $shipping_total_net );
			   	
			   	if ( 'O' === $category_code ) {
			   		$tax_rate_percent = null;
			   	}

			    $this->zugferd_document->addDocumentPositionTax( $category_code, 'VAT', $tax_rate_percent );
			   	$this->zugferd_document->setDocumentPositionLineSummation( $shipping_total_net );

			   	if ( ! isset( $this->tax_net_parts[ $tax_rate_percent ] ) ) {
			    	$this->tax_net_parts[ $tax_rate_percent ] = 0.0;
			    }

			    $this->tax_net_parts[ $tax_rate_percent ] += $shipping_total_net;
			    $this->net_total += $shipping_total_net;		
			}
		}
	}

	/**
	 * Set document tax
	 * 
	 * @return void
	 */
	public function set_doucment_tax() {

		$net_sum = 0.0;

		foreach ( $this->order->get_tax_totals() as $key => $tax_total ) {

			$exemption_reason = null;
			$exemption_reason_code = null;
			$rate_percent = floatval( \WGM_Tax::get_rate_percent_by_rate_id_and_order( $tax_total->rate_id, $this->order ) );
			$category_code = $rate_percent > 0.0 ? 'S' : $this->get_tax_category_code();
			$exemption = $this->get_exemption_reason_and_code_by_category_code( $category_code );

			$net_sum += $this->prepare_value_for_amount( isset( $this->tax_net_parts[ $rate_percent ] ) ? $this->tax_net_parts[ $rate_percent ] : 0.0 );

			$this->zugferd_document->addDocumentTax( 
				$category_code, // categoryCode
				'VAT', // typeCode
				$this->prepare_value_for_amount( isset( $this->tax_net_parts[ $rate_percent ] ) ? $this->tax_net_parts[ $rate_percent ] : 0.0 ), // basisAmount
				$this->prepare_value_for_amount( $tax_total->amount ), // calculatedAmount
				$rate_percent, // rateApplicablePercent
				isset( $exemption[ 'exemption_reason' ] ) ? $exemption[ 'exemption_reason' ] : null, // exemptionReason
				isset( $exemption[ 'exemption_reason_code' ] ) ? $exemption[ 'exemption_reason_code' ] : null // exemptionReasonCode
			);

		}

		$diff = $this->net_total - $net_sum;

		if ( $diff > 0.0 ) {

			$category_code = $this->get_tax_category_code( 'Z' );
			$exemption = $this->get_exemption_reason_and_code_by_category_code( $category_code );
			$this->zugferd_document->addDocumentTax( 
				$this->get_tax_category_code( 'Z' ), // categoryCode
				'VAT', // typeCode
				$diff , // basisAmount
				0.0, // calculatedAmount
				0.0, // rateApplicablePercent
				isset( $exemption[ 'exemption_reason' ] ) ? $exemption[ 'exemption_reason' ] : null, // exemptionReason
				isset( $exemption[ 'exemption_reason_code' ] ) ? $exemption[ 'exemption_reason_code' ] : null // exemptionReasonCode
			);
		}
	}

	/**
	 * Get tax category code (by $order)
	 * 
	 * @param String $init_code
	 * @return String
	 */
	public function get_tax_category_code( $init_code = 'S' ) {

		$category_code = $init_code;

		$order_vat_status = \WGM_Helper::wcvat_woocommerce_order_details_status( $this->order_to_get_buyer );

		if ( 'tax_free_intracommunity_delivery' === $order_vat_status ) {
			$category_code = 'K';
		} else if ( 'tax_exempt_export_delivery' === $order_vat_status ) {
			$category_code = 'G';
		} else if ( 'on' === get_option( 'woocommerce_de_kleinunternehmerregelung', 'off' ) ) {
			$category_code = 'O';
		} else if ( 0.0 === floatval( $this->order_to_get_buyer->get_total_tax() ) ) {
			$category_code = 'Z';
		}

		/**
		* Filter tax category code for order
		* 
		* @since 1.0
		* @param String $category_code
		* @param WC_Order $order
		*/
		return apply_filters( 'german_market_zugferd_get_category_code', $category_code, $this->order_to_get_buyer );
	}

	/**
	 * Get tax exemption reason and code by category code
	 * 
	 * @param String $category_code
	 * @return Array
	 */
	public function get_exemption_reason_and_code_by_category_code( $category_code ) {

		$exemption_reason = null;
		$exemption_reason_code = null;

		if ( 'K' === $category_code ) {
			
			$exemption_reason = apply_filters( 'wcvat_woocommerce_vat_notice_eu', get_option( 'vat_options_notice', __( 'Tax free intracommunity delivery', 'woocommerce-german-market' ) ), $this->order_to_get_buyer );
			$exemption_reason_code = ZugferdVATExemptionReasonCode::VATEX_EU_IC;
		
		} else if ( 'G' === $category_code ) {
			
			$exemption_reason = apply_filters( 'wcvat_woocommerce_vat_notice_not_eu', get_option( 'vat_options_non_eu_notice', __( 'Tax-exempt export delivery', 'woocommerce-german-market' ) ), $this->order_to_get_buyer  );
			$exemption_reason_code = ZugferdVATExemptionReasonCode::VATEX_EU_G;
		
		} else if ( 'O' === $category_code ) {
			
			if ( 'on' === get_option( 'woocommerce_de_kleinunternehmerregelung', 'off' ) ) {
				$exemption_reason = get_option( 'gm_small_trading_exemption_notice_extern_products', \WGM_Template::get_ste_string() );
			} else {
				$exemption_reason = __( 'Not subject to sales tax', 'woocommerce-german-market' );
			}

			$exemption_reason_code = ZugferdVATExemptionReasonCode::VATEX_EU_O;
		}

		$return_value = array(
			'exemption_reason' => $exemption_reason,
			'exemption_reason_code' => $exemption_reason_code,
		);



		/**
		* Filter exemption reason and exemption reason code
		* 
		* @since 1.0
		* @param Array $return_value (see lines above)
		* @param String $category_code
		* @param WC_Order $order
		*/
		return apply_filters( 'german_market_zugferd_get_exemption_reason_and_code', $return_value, $category_code, $this->order_to_get_buyer );
	}

	/**
	 * Set document summation
	 * 
	 * @return void
	 */
	public function set_document_summation() {
		
		$this->zugferd_document->setDocumentSummation(
			$this->prepare_value_for_amount( $this->order->get_total() ), // grandTotalAmount
			$this->prepare_value_for_amount( $this->order->get_total() ), // duePayableAmount
			$this->prepare_value_for_amount( $this->net_total ), // lineTotalAmount
			0.0, // chargeTotalAmount
			0.0, // allowanceTotalAmount
			$this->prepare_value_for_amount( $this->net_total ), // taxBasisTotalAmount
			$this->prepare_value_for_amount( $this->order->get_total_tax() ), // taxTotalAmount
			null, // roundingAmount
			0.0 // totalPrepaidAmount
		);
	}

	/**
	 * Preparea value for amount (money or quantity)
	 * Used for refunds to get positive floats
	 * 
	 * @param float $amount
	 * @return float
	 */
	public function prepare_value_for_amount( $amount ) {

		if ( 'refund' === $this->order_type ) {
			return abs( $amount );
		}

		return $amount;
	}

	/**
	 * Set notes to the document
	 * 
	 * @return void
	 */
	public function get_notes() {

		$managing_director = get_option( 'german_market_einvoice_managing_director', '' );
		if ( ! empty( $managing_director ) ) {
			$managing_director = __( 'Managing director', 'woocommerce-german-market' ) . ': ' . $managing_director;
		}

		$company_infos = array(
			'company_name' 		=> get_option( 'german_market_einvoice_company_name', get_option( 'blogname' ) ),
			'address1' 			=> WC()->countries->get_base_address(), //lineone
			'address2' 			=> WC()->countries->get_base_address_2(), //linetwo
			'postcode' 			=> WC()->countries->get_base_postcode(), //postcode
			'city' 				=> WC()->countries->get_base_city(), // city
			'country' 			=> WC()->countries->get_base_country(), // country
			'managing_director'	=> $managing_director,
			'fc' 				=> get_option( 'german_market_einvoice_seller_tax_registration_fc', '' ),
			'va' 				=> get_option( 'german_market_einvoice_seller_tax_registration_va', str_replace( '-', '', get_option( 'german_market_vat_requester_member_state' ) ) . get_option( 'german_market_vat_requester_vat_number' ) ),
		);

		foreach ( $company_infos as $key => $text ) {
			if ( empty( $text ) ) {
				unset( $company_infos[ $key ] );
			}
		}

		/**
		* Filter exemption reason and exemption reason code
		* 
		* @since 1.0
		* @param Array $return_value (see lines above)
		* @param String $category_code
		* @param WC_Order $order
		*/
		$company_info_text = apply_filters( 'german_market_zugferd_company_infos', implode( PHP_EOL, $company_infos ), $company_infos );

		return array(
			array(
				'text'			=> $company_info_text,
				'subject_code'	=> 'REG'
			),
		);
	}

	/**
	 * Set delivery date
	 * 
	 * @return void
	 */
	public function set_delivery_date() {

		$delivery_date = null;
		if ( method_exists( $this->order, 'get_date_completed' ) ) {
			$delivery_date = $this->order->get_date_completed();
		}

		if ( is_null( $delivery_date ) ) {
			$delivery_date = $this->order->get_date_created();
		}

		if ( is_null( $delivery_date ) ) {
			$delivery_date = new \DateTime();
		}

		/**
		* Filter delivery date
		* 
		* @since 1.0
		* @param DateTime $delivery_date
		* @param WC_Order $order
		*/
		$delivery_date = apply_filters( 'german_market_zugferd_delivery_date', $delivery_date, $this->order );

		$this->zugferd_document->setDocumentSupplyChainEvent( $delivery_date );

	}
	
	/**
	 * Do addDocumentPaymentMean and addDocumentPaymentTerm
	 * 
	 * @return void
	 */
	public function set_payment() {
		
		if ( 'order' === $this->order_type ) {

			$payment_method_title = $this->order->get_payment_method_title();
			$description = null;
			$due_date_meta = $this->order->get_meta( '_wgm_due_date' );
			$due_date_string = '';
			$direct_debit_mandate_id = null;
			$payment_means = ZugferdPaymentMeans::UNTDID_4461_1; // Instrument not defined
			$payment_method = $this->order->get_payment_method();
			$buyer_iban = null;
			$payee_iban = null;

			if ( 'german_market_sepa_direct_debit' === $payment_method ) {
				
				$direct_debit_mandate_id = apply_filters( 'german_market_zugferd_direct_debit_mandate_id', $direct_debit_mandate_id, $this->order );
				$payment_means = ZugferdPaymentMeans::UNTDID_4461_59; // SEPA direct debit
				
				$buyer_iban = apply_filters( 'german_market_zugferd_direct_debit_buyer_iban', '', $this->order );
				$sdd_settings = get_option( 'woocommerce_german_market_sepa_direct_debit_settings' );
				$creditor_reference_id = apply_filters( 'german_market_zugferd_direct_debit_creditor_reference_id', isset( $sdd_settings[ 'creditor_identifier' ] ) ? $sdd_settings[ 'creditor_identifier' ] : null , $this->order );

				$this->zugferd_document->addDocumentPaymentMeanToDirectDebit( $buyer_iban, $creditor_reference_id );

			} else if ( 'bacs' === $payment_method || 'german_market_purchase_on_account' === $payment_method ) {
				$payment_means = ZugferdPaymentMeans::UNTDID_4461_58; // SEPA credit transfer
				$payee_iban = get_option( 'german_market_einvoice_iban', '' );
			}

			if ( ! empty( $due_date_meta ) ) {
				$due_date = new \DateTime( $due_date_meta );
				$due_date_string = apply_filters( 'woocommerce_de_due_date_string', date_i18n( wc_date_format(), strtotime( $due_date_meta ) ), $due_date_meta, $this->order );
			} else {
				$due_date = null;
			}

			if ( $this->order->is_paid() ) {
				if ( empty( $payment_method_title ) ) {
					$description = __( 'Paid', 'woocommerce-german-market' );
				} else {
					$description = sprintf( __( 'Paid via %s', 'woocommerce-german-market' ), $payment_method_title );
				}
			} else {

				if ( empty( $due_date_string ) ) {
					if ( ! empty( $payment_method_title ) ) {
						$description = sprintf( __( 'Pay via %s', 'woocommerce-german-market' ), $payment_method_title );
					}
				} else {
					if ( ! empty( $payment_method_title ) ) {
						$description = sprintf( __( 'Pay via %s until %s', 'woocommerce-german-market' ), $payment_method_title, $due_date_string );
					} else {
						$description = sprintf( __( 'Pay until %s', 'woocommerce-german-market' ), $due_date_string );
					}
				}

				if ( 'off' !== get_option( 'wp_wc_invoice_pdf_custom_payment_information', 'off' ) ) {
					$payment_information_instance = \WP_WC_Invoice_Pdf_Payment_Information::get_instance();
					ob_start();
					$payment_information_instance->add_information( $this->order );
					$payment_description = ob_get_clean();
					$payment_description = str_replace( array( '<br>', '<br />' ), PHP_EOL, $payment_description );
					if ( ! empty( $payment_description ) ) {
						$description = $payment_description;
						$description = wp_strip_all_tags( html_entity_decode( $description ) );
					}
				}
			}

			$this->zugferd_document->addDocumentPaymentTerm( $description, $due_date, $direct_debit_mandate_id );
			$this->zugferd_document->addDocumentPaymentMean( 
				$payment_means,
				null, // information
				null, // cardType,
				null, // cardId
				null, // cardHolderName,
				$buyer_iban, // buyerIban
				$payee_iban // payeeIban
			);
		
		} else if ( 'refund' === $this->order_type ) {

			$due_date = $this->order->get_date_created();
			$this->zugferd_document->addDocumentPaymentTerm( null, $due_date );
		}
	}
}
