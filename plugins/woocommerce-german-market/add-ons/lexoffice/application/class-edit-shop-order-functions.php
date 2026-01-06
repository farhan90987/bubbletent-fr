<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

class German_Market_Lexoffice_Edit_Shop_Order{

	/**
	* Output Icon
	*
	* wp-hook woocommerce_admin_order_actions_end
	* @param WC_Order $order
	* @return void
	*/
	public static function order_icon( $order ) {
		
		if ( apply_filters( 'lexoffice_woocommerce_edit_shop_order_icon_return', false, $order, 'orders-icon' ) ) {
			return ;
		}

		// is bulk transmission scheduled?
		$is_scheduled = $order->get_meta( '_lexoffice_woocomerce_scheduled_for_transmission' );
		if ( ! empty( $is_scheduled ) ) {
			return;
		}

		// manual order confirmation
		if ( get_option( 'woocommerce_de_manual_order_confirmation' ) == 'on' ) {
			if ( $order->get_meta( '_gm_needs_conirmation' ) === 'yes' ) {
				return;
			}
		}

		if ( 'invoice' === get_option( 'woocommerce_de_lexoffice_voucher_or_invoice', 'voucher' ) ) {
			$lexoffice_voucher_id = $order->get_meta( '_lexoffice_woocomerce_has_transmission_invoice_api' );
		} else {
			$lexoffice_voucher_id = $order->get_meta( '_lexoffice_woocomerce_has_transmission' );
		}

		// has transmission?
		$has_transmission = $lexoffice_voucher_id != '';

		// is voucher still available?
		$is_valid = true;

		// order status
		if ( ! $has_transmission ) {
			
			$voucher_or_invoice_api = get_option( 'woocommerce_de_lexoffice_voucher_or_invoice', 'voucher' );

			if ( 'voucher' === $voucher_or_invoice_api ) {
				$completed_class = ( 'completed' !== $order->get_status() ) ? ' lexoffice-not-completed' : '';
			} else if ( 'invoice' === $voucher_or_invoice_api ) {
				$completed_class = ( ! German_Market_Lexoffice_Invoice_API_General::is_order_allowed_for_transmission( $order ) ) ? ' lexoffice-not-completed' : '';
			}
			
			if ( apply_filters( 'woocommerce_de_lexoffice_force_transmission_even_if_not_completed', false, $order ) ) {
				$completed_class = '';
			}
			
		}
		
		// load correct icon ()
		$classes = ( $has_transmission ) ? 'lexoffice-woocommerce-yes dashicons dashicons-yes' : 'lexoffice-woocommerce-x' . $completed_class;
		$classes = apply_filters( 'lexoffice_woocommerce_edit_shop_order_icon_classes', $classes, $has_transmission, $order );

		// markup
		?><a class="button lexoffice-woocomerce-default <?php echo $classes; ?>" data-order-id="<?php echo $order->get_id(); ?>" title="<?php echo __( 'Lexware Office', 'woocommerce-german-market' ); ?>"></a><?php
	}

	/**
	* adds a small download button to the admin page for refunds
	*
	* @since WGM 3.0
	* @access public
	* @static 
	* @hook wgm_refunds_actions
	* @param String $string
	* @param shop_order_refund $refund
	* @return String
	*/
	public static function refund_icon( $actions, $refund ) {

		// is bulk transmission scheduled?
		$is_scheduled = $refund->get_meta( '_lexoffice_woocomerce_scheduled_for_transmission' );
		if ( ! empty( $is_scheduled ) ) {
			return $actions;
		}

		if ( isset( $_REQUEST[ 'transmit-to-lexoffice' ] ) && isset( $_REQUEST[ 'refunds' ] ) ) {
			if ( is_array( $_REQUEST[ 'refunds' ] ) ) {
				if ( in_array( $refund->get_id(), $_REQUEST[ 'refunds' ] ) ) {
					return $actions;
				}
			}
		}
		
		if ( 'invoice' === get_option( 'woocommerce_de_lexoffice_voucher_or_invoice', 'voucher' ) ) {
			$lexoffice_voucher_id = $refund->get_meta( '_lexoffice_woocomerce_has_transmission_invoice_api' );
		} else {
			$lexoffice_voucher_id = $refund->get_meta( '_lexoffice_woocomerce_has_transmission' );
		}

		// has transmission?
		$has_transmission = $lexoffice_voucher_id != '';

		// is voucher still available?
		$is_valid = true;

		// load correct icon ()
		$classes = ( $has_transmission ) ? 'lexoffice-woocommerce-yes dashicons dashicons-yes' : 'lexoffice-woocommerce-x';
		$classes = apply_filters( 'lexoffice_woocommerce_edit_shop_order_icon_classes_refund', $classes, $has_transmission, $refund );
		$name = ( $has_transmission ) ? '' : __( 'Send refund data to Lexware Office', 'woocommerce-german-market' );

		$actions[ 'lexoffice' ] = array(
			'class' => 'lexoffice-woocomerce-default lexoffice-refund ' . $classes,
			'data'	=> array(
							'order-id' => $refund->get_parent_id(),
							'refund-id'=> $refund->get_id()
						),
			'name'	=> $name
		);

		if ( 'invoice' === get_option( 'woocommerce_de_lexoffice_voucher_or_invoice', 'voucher' ) ) {
			$document_id = $refund->get_meta( '_lexoffice_woocomerce_invoice_document_id' );
			$classes = empty( $document_id ) ? 'no-document' : 'has-document';
			$name = empty( $document_id ) ? '' : __( 'Download invoice correction', 'woocommerce-german-market' );
			$href = wp_nonce_url( admin_url( 'admin-ajax.php?action=lexoffice_woocommerce_download_invoice&document_id=' . esc_attr( $document_id ) . '&order_id=' . esc_attr( $refund->get_id() ) ), 'lexoffice_invoice_pdf_download' );

			$actions[ 'lexoffice-credit-note' ] = array(
				'url'	=> $href,
				'class' => 'lexoffice-woocomerce-default lexoffice-invoice-pdf lexoffice-refund ' . $classes,
				'data'	=> array(
								'data-document-id' => $document_id,
								'refund-id'=> $refund->get_id()
							),
				'name'	=> $name
			);
		}

		return $actions;
	}

	/**
	* Enqueue scripts and styles
	*
	* wp-hook admin_enqueue_scripts
	* @return void
	*/
	public static function styles_and_scripts() {

		// set directories
		$assets_dir 	= untrailingslashit( plugin_dir_url( __FILE__ ) ) . '/assets';
		$styles_dir 	= $assets_dir . '/styles';
		$scripts_dir	= $assets_dir . '/scripts';

		// script debug
		$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : 'min.';

		// set api type (voucher or invoice api)
		$api_type = get_option( 'woocommerce_de_lexoffice_voucher_or_invoice', 'voucher' );

		// enqueue style
		wp_enqueue_style( 'lexoffice_woocommerce_edit_shop_order_style', $styles_dir . '/edit-shop-order.' . $min . 'css', array(), '0.1' );

		// enqueue script
		wp_enqueue_script( 'lexoffice_woocommerce_edit_shop_order_script', $scripts_dir . '/edit-shop-order-' . $api_type . '.' . $min . 'js', array( 'jquery' ), '0.1' );

		// localize script for ajax
		wp_localize_script( 'lexoffice_woocommerce_edit_shop_order_script', 'lexoffice_ajax', 
			array(
				'url' 	=> admin_url( 'admin-ajax.php' ),
				'nonce'	=> wp_create_nonce( 'lexoffice_woocommerce_edit_shop_order_script' )
			)
		);
	}

		/**
	* Icon for invoice pdf download
	*
	* wp-hook woocommerce_admin_order_actions_end
	* @param WC_Order $order
	* @return void
	*/
	public static function shop_order_icon_invoice_download( $order ) {

		if ( apply_filters( 'lexoffice_woocommerce_edit_shop_order_icon_return', false, $order, 'orders-invoice-download' ) ) {
			return ;
		}

		// manual order confirmation
		if ( 'on' === get_option( 'woocommerce_de_manual_order_confirmation' ) ) {
			if ( 'yes' === $order->get_meta( '_gm_needs_conirmation' ) ) {
				return;
			}
		}

		$document_id = $order->get_meta( '_lexoffice_woocomerce_invoice_document_id' );

		$style = '';
		if ( empty( $document_id ) ) {
			$style = ' style="display: none;"';
		}
			// markup
		?><a class="button lexoffice-invoice-pdf" data-document-id="<?php echo esc_attr( $document_id ); ?>" title="<?php echo esc_attr( __( 'Download Lexware Office invoice pdf', 'woocommerce-german-market' ) ); ?>" href="<?php echo wp_nonce_url( admin_url( 'admin-ajax.php?action=lexoffice_woocommerce_download_invoice&document_id=' . $document_id . '&order_id=' . $order->get_id() ), 'lexoffice_invoice_pdf_download' ); ?>" <?php echo wp_kses_post( $style );?>>test</a><?php
		
	}

	/**
	* Download button in backend order
	*
	* wp-hook woocommerce_order_actions_end
	* @param Integer $order_id
	* @return void
	*/
	public static function oder_button_download( $order_id ) {

		$order = wc_get_order( $order_id );

		if ( apply_filters( 'lexoffice_woocommerce_edit_shop_order_icon_return', false, $order, 'order-invoice-download' ) ) {
			return ;
		}

		// manual order confirmation
		if ( 'on' === get_option( 'woocommerce_de_manual_order_confirmation' ) ) {
			if ( 'yes' === $order->get_meta( '_gm_needs_conirmation' ) ) {
				return;
			}
		}

		$order = wc_get_order( $order_id );

		if ( is_object( $order ) && method_exists( $order, 'get_meta' ) ) {
			$document_id = $order->get_meta( '_lexoffice_woocomerce_invoice_document_id' );

			if ( ! empty( $document_id ) ) {

				$invoice_number = $order->get_meta( '_lexoffice_invoice_number' );

				echo wp_kses_post( '<li class="wide"><p><a class="button-primary lexoffice-invoice-pdf" href="' . wp_nonce_url( admin_url( 'admin-ajax.php?action=lexoffice_woocommerce_download_invoice&document_id=' . esc_attr( $document_id ) . '&order_id=' . esc_attr( $order_id ) ), 'lexoffice_invoice_pdf_download' ) . '">' . esc_attr( sprintf( __( 'Download Lexware Office %s', 'woocommerce-german-market' ), $invoice_number ) ) . '</a></p></li>' );
			}
		}
	}
}
