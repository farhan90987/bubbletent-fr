<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

class German_Market_Lexoffice_Invoice_My_Account {

	/**
	 * Check if download button can be shown
	 * 
	 * @param WC_Order
	 * @return Boolean
	 */
	public static function can_show_download_button( $order, $refund = null ) {

		if ( ! is_user_logged_in() ) {
			return false;
		}

		// manual order confirmation
		if ( 'yes' == $order->get_meta( '_gm_needs_conirmation' ) ) {
			return false;
		}

		// double-opt-in check
		if ( 'on' == get_option( 'wgm_double_opt_in_customer_registration' ) ) {

			$user              = wp_get_current_user();
			$activation_status = get_user_meta( $user->ID, '_wgm_double_opt_in_activation_status', true );

			if ( $activation_status == 'waiting' ) {
				return false;
			}
		}

		if ( ! current_user_can( 'view_order', $order->get_id() ) ) {
			return false;
		}

		$used_order = is_null( $refund ) ? $order : $refund;

		if ( is_object( $used_order ) && method_exists( $used_order, 'get_meta' ) ) {
			$document_id = $used_order->get_meta( '_lexoffice_woocomerce_invoice_document_id' );
			if ( ! empty( $document_id ) ) {
				return $document_id;
			}
		}

		return false;
	}

	/**
	 * Outout download button in my account, when one specific order is shown
	 * 
	 * @wp-hook woocommerce_order_details_after_order_table
	 * @param WC_Order $order
	 * @return void
	 */
	public static function make_download_button( $order ) {

		$document_id = self::can_show_download_button( $order );
		if ( false ===  $document_id ) {
			return;
		}

		$a_href       = wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_german_market_lexoffice_invoice_pdf&order_id=' . $order->get_id() ), 'german-market-lexoffice-download-view-order' );
		$a_target     = 'target="_blank"';
		$a_download   = ' download';
		$a_attributes = trim( $a_target . $a_download );

		$button_text = self::get_button_text( $order );

		if ( ! ( empty( $document_id ) ) ) {

			if ( apply_filters( 'german_market_lexoffice_invoice_api_download_button_my_account', true, $order ) ) {

				?>
				<p class="download-invoice-pdf">
				    <a href="<?php echo esc_url( $a_href ); ?>" class="button"<?php echo ( $a_attributes != '' ) ? ' ' . esc_attr( $a_attributes ) : ''; ?> style="<?php echo esc_attr( apply_filters( 'wp_wc_invoice_pdf_download_buttons_inline_style', 'margin: 0.15em 0;' ) ); ?>"><?php echo esc_attr( $button_text ); ?></a>
				</p>
				<?php
			}
		}
	}

	/**
	 * Outout download button in my account, when one specific order is shown
	 * 
	 * @wp-hook woocommerce_order_details_after_order_table
	 * @param WC_Order $order
	 * @return void
	 */
	public static function make_download_button_refunds( $order ) {

		if ( is_object( $order ) && method_exists( $order, 'get_refunds' ) ) {
			foreach ( $order->get_refunds() as $refund ) {

				$document_id = self::can_show_download_button( $order, $refund );

				if ( false ===  $document_id ) {
					continue;
				}

				$a_href       = wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_german_market_lexoffice_invoice_pdf&order_id=' . $order->get_id() . '&refund_id=' . $refund->get_id() ), 'german-market-lexoffice-download-view-order' );
				$a_target     = 'target="_blank"';
				$a_download   = ' download';
				$a_attributes = trim( $a_target . $a_download );

				$button_text = self::get_button_text( $refund );

				if ( ! ( empty( $document_id ) ) ) {

					if ( apply_filters( 'german_market_lexoffice_invoice_api_download_button_my_account_refund', true, $order ) ) {

						?>
						<p class="download-invoice-pdf download-invoice-correction-pdf">
						    <a href="<?php echo esc_url( $a_href ); ?>" class="button"<?php echo ( $a_attributes != '' ) ? ' ' . esc_attr( $a_attributes ) : ''; ?> style="<?php echo esc_attr( apply_filters( 'wp_wc_invoice_pdf_download_buttons_inline_style', 'margin: 0.15em 0;' ) ); ?>"><?php echo esc_attr( $button_text ); ?></a>
						</p>
						<?php
					}
				}
			}
		}
	}

	/**
	 * Download Invoice PDF
	 * 
	 * @wp-hook wp_ajax_woocommerce_german_market_lexoffice_invoice_pdf
	 * @return void
	 */
	public static function download_pdf() {

		if ( ! check_ajax_referer( 'german-market-lexoffice-download-view-order', 'security', false ) ) {
			wp_die( __( 'You have taken too long. Please go back and retry.', 'woocommerce-german-market' ), '', array( 'response' => 403 ) );
		}

		if ( isset( $_REQUEST[ 'order_id' ] ) ) {

			$order_id = intval( $_REQUEST[ 'order_id' ] );
			$order = wc_get_order( $order_id );

			if ( current_user_can( 'view_order', $order_id ) ) {

				if ( isset( $_REQUEST[ 'refund_id' ] ) ) {
					$order = wc_get_order( intval( $_REQUEST[ 'refund_id' ] ) );
				}

				if ( is_object( $order ) && method_exists( $order, 'get_meta' ) ) {
					
					$document_id = $order->get_meta( '_lexoffice_woocomerce_invoice_document_id' );
					$pdf_binary = German_Market_Lexoffice_Invoice_API_General::download_pdf_document( $document_id );
					$validate 	= German_Market_Lexoffice_Invoice_API_General::validate_binary_pdf_cocument( $pdf_binary );
					
					if ( 'success' === $validate ) {
						
						if ( ! empty( $pdf_binary ) ) {

							$filename = German_Market_Lexoffice_Invoice_API_General::get_filename_and_replace_placeholders( $order_id, $order, 'frontend' );
							header('Content-Description: File Transfer');
							header('Content-Type: application/pdf');
							header('Content-Disposition: attachment; filename=' . $filename . '.pdf');
							echo $pdf_binary;
							exit();

						} else {

							wp_die( __( 'The invoice was not found. Please contact the store owner.', 'woocommerce-german-market' ), '', array( 'response' => 403 ) );
						}
					}
				}
			}
		}

		wp_die( __( 'Unfortunately an error has occurred. Please try again later.', 'woocommerce-german-market' ), '', array( 'response' => 403 ) );
	}

	/**
	 * Outout download button in my account, "orders" submenu
	 * 
	 * @wp-hook woocommerce_my_account_my_orders_actions
	 * @param Array $actions
	 * @param WC_Order $order
	 * @return Array
	 */
	public static function make_download_button_in_order_actions( $actions, $order ) {

		$document_id = self::can_show_download_button( $order );
		
		if ( false !==  $document_id ) {

			if ( apply_filters( 'german_market_lexoffice_invoice_api_download_button_my_account', true, $order ) ) {
				
				$actions[ 'invoice' ] = array(
		            
		            'url'   => esc_url( wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_german_market_lexoffice_invoice_pdf&order_id=' . $order->get_id() ), 'german-market-lexoffice-download-view-order' ) ),
		            'name'  => self::get_button_text( $order )
	 
	        	);
	        }
		}

		return $actions;
	}

	/**
	 * Get download button text
	 * 
	 * @param WC_Order $order
	 * @return String
	 */
	public static function get_button_text( $order ) {

		if ( 'shop_order_refund' === $order->get_type() ) {
			$button_text = get_option( 'woocommerce_de_lexoffice_invoice_api_my_account_button_text_refund', __( 'Download invoice correction {{lexwareoffice-correction-number}}', 'woocommerce-german-market' ) );
		} else {
			$button_text = get_option( 'woocommerce_de_lexoffice_invoice_api_my_account_button_text', __( 'Download invoice {{lexwareoffice-invoice-number}}', 'woocommerce-german-market' ) );
		}
		
		$button_text = German_Market_Lexoffice_Invoice_API_General::replace_placeholders( $button_text, $order->get_id(), $order );

		return apply_filters( 'german_market_lexoffice_invoice_api_download_button_text', $button_text, $order );
	}
}
