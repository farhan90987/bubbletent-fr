<?php

namespace MarketPress\GermanMarket\Shipping;

use WC_Order;
use Exception;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

class Labels {

	public string $id;

	protected function __construct() {}

	/**
	 * Send Shipping Label PDF via Email.
	 *
	 * @param int $order_id
	 *
	 * @return void
	 * @throws Exception
	 */
	public function send_shipping_label_via_email( int $order_id ) {

		$order = wc_get_order( $order_id );

		if ( ! is_object( $order ) ) {
			return;
		}

		$provider           = Woocommerce_Shipping::get_instance()->get_provider_by_id( $this->id );
		$email_address      = apply_filters( 'wgm_' . $this->id . '_shipping_label_email_recipient', $provider::$options->get_option( 'label_email_address', '' ) );
		$label_already_sent = $order->get_meta( '_wgm_' . $this->id . '_shipping_label_email_sent' );

		if ( '' === $email_address ) {
			$email_address = get_option( 'admin_email' );
		}

		if ( '' === $label_already_sent ) {
			$label_data = Woocommerce_Shipping::$order_meta->get_shipment_label( $order_id );

			$pdf_merge          = new Pdf_Merger();
			$temp_file_handlers = array();

			foreach ( $label_data as $key => $label ) {
				// Save binary shipment label data to temp file.
				$tmp_file_label       = tmpfile();
				$tmp_file_path        = stream_get_meta_data( $tmp_file_label )[ 'uri' ];
				$temp_file_handlers[] = $tmp_file_label;
				@fwrite( $tmp_file_label, $label[ 'order_label' ] );
				$pdf_merge->addPDF( $tmp_file_path );
				if ( ! empty( $label[ 'order_label_retoure' ] ) ) {
					// Save binary shipment retoure label data to temp file.
					$tmp_file_retoure_label = tmpfile();
					$tmp_file_path          = stream_get_meta_data( $tmp_file_retoure_label )[ 'uri' ];
					$temp_file_handlers[]   = $tmp_file_retoure_label;
					@fwrite( $tmp_file_retoure_label, $label[ 'order_label_retoure' ] );
					$pdf_merge->addPDF( $tmp_file_path );
				}
			}

			$pdf = $pdf_merge->merge( 'string' );

			// Temp files will be automatically deleted from server when closing the file handler.
			foreach ( $temp_file_handlers as $file_handler ) {
				fclose( $file_handler );
			}

			$directory = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'woocommerce-shipping-' . $this->id . DIRECTORY_SEPARATOR;

			if ( ! is_dir( $directory ) ) {
				mkdir( $directory );
			}

			$filename = $directory . __('label_order_', 'woocommerce-german-market') . $order_id . '.pdf';
			file_put_contents( $filename, $pdf );

			$attachments = array(
				$filename,
			);

			// Check for export documents.

			if ( Woocommerce_Shipping::$order_meta->has_export_documents( $order_id ) ) {

				$export_documents   = Woocommerce_Shipping::$order_meta->get_shipment_export_documents( $order_id );
				$pdf_merge          = new Pdf_Merger();
				$temp_file_handlers = array();

				foreach ( $export_documents as $key => $document ) {
					// Save binary export document data to temp file.
					$tmp_file_label       = tmpfile();
					$tmp_file_path        = stream_get_meta_data( $tmp_file_label )[ 'uri' ];
					$temp_file_handlers[] = $tmp_file_label;
					@fwrite( $tmp_file_label, $document[ 'order_export_docs' ] );
					$pdf_merge->addPDF( $tmp_file_path );
				}

				$pdf = $pdf_merge->merge( 'string' );

				// Temp files will be automatically deleted from server when closing the file handler.
				foreach ( $temp_file_handlers as $file_handler ) {
					fclose( $file_handler );
				}

				$directory = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'woocommerce-shipping-' . $this->id . DIRECTORY_SEPARATOR;

				if ( ! is_dir( $directory ) ) {
					mkdir( $directory );
				}

				$documents_filename = $directory . 'export_document_order_' . $order_id . '.pdf';
				file_put_contents( $documents_filename, $pdf );

				$attachments[] = $documents_filename;
			}

			// Send email and attachment(s).

			$subject = apply_filters( 'wgm_' . $this->id . '_shipping_label_email_subject', sprintf( __( '%s-Shipping Label for order #%s', 'woocommerce-german-market' ), $provider->name, $order_id ) );
			$message = apply_filters( 'wgm_' . $this->id . '_shipping_label_email_text', sprintf( __( 'Hello Admin, here you can find the %s shipping label for order #%s', 'woocommerce-german-market' ), $provider->name, $order_id ) );
			$headers = array(
				'Content-Type: text/html; charset=UTF-8',
				'From: ' . apply_filters( 'wgm_' . $this->id . '_shipping_label_email_sender_name', get_option( 'blogname' ) ) . ' <' . apply_filters( 'wgm_' . $this->id . '_shipping_label_email_sender_email', get_option( 'admin_email' ) ) . '>',
			);

			wp_mail( $email_address, $subject, $message, $headers, $attachments );

			@unlink( $filename );

			if ( ! empty( $documents_filename ) ) {
				@unlink( $documents_filename );
			}

			$order->update_meta_data( '_wgm_' . $this->id . '_shipping_label_email_sent', current_time( 'timestamp' ) );
			$order->save();
		}
	}

	/**
	 * Automatic Shipping Label creation at pre-defined order status for new orders
	 *
	 * @Hook woocommerce_thankyou
	 *
	 * @param int $order_id
	 *
	 * @return void
	 */
	public function automatic_shipping_label_generator_new_order( int $order_id ) {

		$order = wc_get_order( $order_id );

		if ( ! is_object( $order ) ) {
			return;
		}

		$provider          = Woocommerce_Shipping::get_instance()->get_provider_by_id( $this->id );
		$generator_enabled = $provider::$options->get_option( 'label_auto_creation', 'off' );
		$generator_status  = $provider::$options->get_option( 'label_auto_creation_status', 'wc-processing' );
		$statuses          = array();

		if ( is_array( $generator_status ) ) {
			foreach ( $generator_status as $status ) {
				$statuses[] = substr( $status, 3 ); // cutting 'wc-'
			}
		} else {
			$statuses[] = substr( $generator_status, 3 );  // cutting 'wc-'
		}

		// Check if order shipping methods
		if ( Helper::check_order_for_shipping_provider_methods( $order, $this->id ) ) {
			// check if auto-creation is enabled && check given order status
			if ( ( 'on' === $generator_enabled ) && ( in_array( $order->get_status(), $statuses ) ) ) {
				$result = $this->order_shipment_creation( array( $order ) );
			}
		}
	}

	/**
	 * Automatic Shipping Label creation at pre-defined order status
	 *
	 * @Hook woocommerce_order_status_changed
	 *
	 * @param int      $order_id
	 * @param string   $status_old
	 * @param string   $status_new
	 * @param WC_Order $instance
	 *
	 * @return void
	 */
	public function automatic_shipping_label_generator( int $order_id, $status_old, string $status_new, $instance ) {

		$order             = wc_get_order( $order_id );
		$provider          = Woocommerce_Shipping::get_instance()->get_provider_by_id( $this->id );
		$generator_enabled = $provider::$options->get_option( 'label_auto_creation', 'off' );
		$generator_status  = $provider::$options->get_option( 'label_auto_creation_status', 'wc-processing' );
		$statuses          = array();

		if ( is_array( $generator_status ) ) {
			foreach ( $generator_status as $status ) {
				$statuses[] = substr( $status, 3 ); // cutting 'wc-'
			}
		} else {
			$statuses[] = substr( $generator_status, 3 );  // cutting 'wc-'
		}

		// Check if order shipping methods
		if ( Helper::check_order_for_shipping_provider_methods( $order, $this->id ) ) {
			// check if auto-creation is enabled && check given order status
			if ( ( 'on' === $generator_enabled ) && ( in_array( $status_new, $statuses ) ) ) {
				$result = $this->order_shipment_creation( array( $order ) );
			}
		}
	}

	/**
	 * Handle bulk actions.
	 *
	 * @Hook handle_bulk_actions-edit-shop_order
	 *
	 * @param string $redirect_to URL to redirect to.
	 * @param string $action Action name.
	 * @param array  $ids List of ids.
	 *
	 * @return string
	 * @throws Exception
	 */
	public function handle_orders_bulk_actions( $redirect_to, $action, $ids ) {

		$ids     = array_map( 'absint', $ids );
		$changed = 0;

		if ( 'wgm_' . $this->id . '_print_parcel_label' === $action ) {
			$report_action = $this->id . '_printed_parcel_label';
			$result        = $this->do_multiple_print_parcel_label( $ids );
			$changed       = ( $result == null ) ? - 1 : count( $ids );
		} else
		if ( 'wgm_' . $this->id . '_cancel_shipment' === $action ) {
			$report_action = $this->id . '_canceled_shipment';
			foreach ( $ids as $id ) {
				$order = wc_get_order( $id );
				if ( $order ) {
					$this->do_cancel_shipment( $order );
					$changed++;
				}
			}
		}

		if ( $changed ) {
			$redirect_to = add_query_arg( array(
				'post_type'   => Woocommerce_Shipping::$hpos_active ? 'shop-order' : 'shop_order',
				'bulk_action' => $report_action,
				'changed'     => $changed,
				'ids'         => join( ',', $ids ),
			), $redirect_to );
		}

		return esc_url_raw( $redirect_to );
	}

	/**
	 * Handle multiple parcel labels print.
	 *
	 * @param array $ids
	 *
	 * @return void
	 * @throws Exception
	 */
	public function do_multiple_print_parcel_label( array $ids ) {

		$tracking_labels    = array();
		$temp_file_handlers = array();

		foreach ( $ids as $id ) {
			$order = wc_get_order( $id );
			if ( $order ) {
				$shipment = $this->order_shipment_creation( array( $order ) );
				foreach ( $shipment as $order_id => $data ) {
					if ( 'err' === $data[ 'status' ] ) {
					} else
					if ( 'ok' === $data[ 'status' ] ) {

						$label_data = Woocommerce_Shipping::$order_meta->get_shipment_label( $order_id );

						foreach ( $label_data as $label ) {
							// Save binary shipment label data to temp file.
							$tmp_file_label       = tmpfile();
							$tmp_file_path        = stream_get_meta_data( $tmp_file_label )[ 'uri' ];
							$tracking_labels[]    = $tmp_file_path;
							$temp_file_handlers[] = $tmp_file_label;
							@fwrite( $tmp_file_label, $label[ 'order_label' ] );
							if ( ! empty( $label[ 'order_label_retoure' ] ) ) {
								// Save binary shipment retoure label data to temp file.
								$tmp_file_retoure_label = tmpfile();
								$tmp_file_path          = stream_get_meta_data( $tmp_file_retoure_label )[ 'uri' ];
								$tracking_labels[]      = $tmp_file_path;
								$temp_file_handlers[]   = $tmp_file_retoure_label;
								@fwrite( $tmp_file_retoure_label, $label[ 'order_label_retoure' ] );
							}
						}
					}
				}
			}
		}

		if ( is_array( $tracking_labels ) && count( $tracking_labels ) > 0 ) {

			$pdf = new Pdf_Merger();

			foreach ( $tracking_labels as $label ) {
				$pdf->addPDF( $label );
			}

			$pdf->merge( 'download', $this->id . '-shipping-label.pdf' );

			foreach ( $temp_file_handlers as $handler ) {
				@fclose( $handler );
			}

			die();
		}
	}

	/**
	 * Callback for order actions.
	 *
	 * @uses woocommerce_order_action_dhl_print_parcel_label
	 * @uses woocommerce_order_action_dpd_print_parcel_label
	 *
	 * @param WC_Order $order
	 *
	 * @return void
	 * @throws Exception
	 */
	public function do_print_parcel_label( WC_Order $order ) {

		$shipment  = $this->order_shipment_creation( array( $order ) );
		$has_error = false;

		foreach ( $shipment as $order_id => $data ) {
			if ( $data[ 'status' ] == 'ok' ) {
				$this->show_shipping_label( $order_id, $data[ 'barcodes' ] );
			} else {
				$has_error = true;
			}
		}

		if ( ! $has_error ) {
			die();
		}
	}

	/**
	 * Delete shipment.
	 *
	 * @uses wp_ajax_woocommerce_dpd_shipping_label_delete
	 * @uses wp_ajax_woocommerce_dhl_shipping_label_delete
	 *
	 * @return void, exit()
	 * @throws Exception
	 */
	public function delete_order_shipping_labels() {

		check_ajax_referer( 'wp-wc-label-delete', 'security' );

		$order_id = intval( $_REQUEST[ 'order_id' ] );
		$order    = wc_get_order( $order_id );

		if ( ! is_object( $order ) ) {
			return;
		}

		$label = Woocommerce_Shipping::$order_meta->get_shipment_label( $order_id );

		if ( ! empty( $label) ) {
			Woocommerce_Shipping::$order_meta->delete_order_shipments( $order_id );
		}

		wp_send_json_success();

		exit();
	}

	/**
	 * Download PDF via Link.
	 *
	 * @uses wp_ajax_woocommerce_dpd_shipping_label_download
	 * @uses wp_ajax_woocommerce_dhl_shipping_label_download
	 *
	 * @return void, exit()
	 * @throws Exception
	 */
	public function download_order_shipping_label() {

		check_ajax_referer( 'wp-wc-label-pdf-download', 'security' );

		$order_id = intval( $_REQUEST[ 'order_id' ] );
		$order    = wc_get_order( $order_id );

		if ( ! is_object( $order ) ) {
			return;
		}

		$label = Woocommerce_Shipping::$order_meta->get_shipment_label( $order_id );

		if ( ! empty( $label) ) {
			$this->show_shipping_label( $order_id, $label );
		}

		exit();
	}

	/**
	 * Ajax, manages what happen when the download button in public account order was clicked.
	 *
	 * @uses wp_ajax_woocommerce_wc_dpd_shipping_retoure_label_download
	 * @uses wp_ajax_woocommerce_wc_dhl_shipping_retoure_label_download
	 *
	 * @return void, exit()
	 * @throws Exception
	 */
	public function download_order_shipping_retoure_label() {

		if ( ! check_ajax_referer( 'wc-' . $this->id . '-retoure-shipping-label-download', 'security', false ) ) {
			wp_die( __( 'You have taken too long. Please go back and retry.', 'woocommerce-german-market' ), '', array( 'response' => 403 ) );
		}

		$order_id = intval( $_REQUEST[ 'order_id' ] );
		$order    = wc_get_order( $order_id );

		if ( ! is_object( $order ) ) {
			return;
		}

		$retoure_label = Woocommerce_Shipping::$order_meta->get_shipment_retoure_label( $order_id );

		if ( empty( $retoure_label ) ) {
			$result        = $this->create_retoure_shipping_label( $order );
			$retoure_label = Woocommerce_Shipping::$order_meta->get_shipment_retoure_label( $order_id );
		}

		if ( ! empty( $retoure_label ) ) {
			$this->download_shipping_label( $order_id, true );
		}

		exit();
	}

	/**
	 * Ajax, manages what happen when the download button on admin order page is clicked.
	 *
	 * @uses wp_ajax_woocommerce_dpd_ajax_shipping_label_download
	 * @uses wp_ajax_woocommerce_dhl_ajax_shipping_label_download
	 *
	 * @return void, exit()
	 * @throws Exception
	 */
	public function ajax_download_order_shipping_label() {

		if ( ! check_ajax_referer( 'wp-wc-label-pdf-download', 'security', false ) ) {
			wp_die( __( 'You have taken too long. Please go back and retry.', 'woocommerce-german-market' ), '', array( 'response' => 403 ) );
		}

		$order_id           = intval( $_REQUEST[ 'order_id' ] );
		$has_shipping_label = Woocommerce_Shipping::$order_meta->has_shipping_label( $order_id );

		if ( ! $has_shipping_label ) {

			$order = wc_get_order( $order_id );

			if ( ! is_object( $order ) ) {
				wp_send_json_success(
					array(
						'barcode' => array(),
						'error'   => sprintf( __( 'Order with ID not found.', 'woocommerce-german-market' ), $order_id ),
					)
				);
				die();
			}

			$result = $this->order_shipment_creation( array( $order ) );

			$barcodes = array();
			$error    = '';
			foreach ( $result as $order_id => $parcel ) {
				if ( 'ok' === $parcel[ 'status' ] ) {
					$barcodes[] = $parcel[ 'barcodes' ];
				} else
				if ( 'err' === $parcel[ 'status' ] ) {
					$error = $parcel[ 'errlog' ];
				}
			}
			wp_send_json_success(
				array(
					'barcode' => $barcodes,
					'error'   => $error,
				)
			);
			die();
		}

		$this->download_shipping_label( $order_id );
		exit();
	}

	/**
	 * Display shipping label in browser.
	 *
	 * @param int   $order_id order id
	 * @param array $label_data array with label data
	 *
	 * @return void, die()
	 * @throws Exception
	 */
	public function show_shipping_label( int $order_id, array $label_data ) {

		// Clear output buffering for already rendered WP html content at this point.
		ob_clean();

		$provider            = Woocommerce_Shipping::get_instance()->get_provider_by_id( $this->id );
		$print_retoure_label = ( 'on' === $provider::$options->get_option( 'label_retoure_enabled', 'off' ) );
		$pdf_merge           = new Pdf_Merger();
		$temp_file_handlers  = array();

		$order = wc_get_order( $order_id );

		// Check for DHL order meta option from meta box.
		if ( 'on' == $order->get_meta( '_wgm_dhl_service_return_label' ) ) {
			$print_retoure_label = true;
		}

		foreach ( $label_data as $key => $label ) {
			if ( empty( $label[ 'order_label' ] ) ) {
				$label_data = Woocommerce_Shipping::$order_meta->get_shipment_label( $order_id );
				break;
			}
		}

		foreach ( $label_data as $key => $label ) {
			// Save binary shipment label data to temp file.
			$tmp_file_label       = tmpfile();
			$tmp_file_path        = stream_get_meta_data( $tmp_file_label )[ 'uri' ];
			$temp_file_handlers[] = $tmp_file_label;
			@fwrite( $tmp_file_label, $label[ 'order_label' ] );
			$pdf_merge->addPDF( $tmp_file_path );
			if ( ! empty( $label[ 'order_label_retoure' ] ) && $print_retoure_label ) {
				// Save binary shipment retoure label data to temp file.
				$tmp_file_retoure_label = tmpfile();
				$tmp_file_path          = stream_get_meta_data( $tmp_file_retoure_label )[ 'uri' ];
				$temp_file_handlers[]   = $tmp_file_retoure_label;
				@fwrite( $tmp_file_retoure_label, $label[ 'order_label_retoure' ] );
				$pdf_merge->addPDF( $tmp_file_path );
			}
		}

		$pdf = $pdf_merge->merge( 'string' );

		// Temp files will be automatically deleted from server when closing the file handler.
		foreach ( $temp_file_handlers as $file_handler ) {
			fclose( $file_handler );
		}

		header( 'Content-Type: application/pdf' );
		header( 'Content-Disposition: attachment; filename="' . $this->id . __('_shipping_label_order_', 'woocommerce-german-market' ) . $order_id . '.pdf"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );

		die( $pdf );
	}

	/**
	 * Initiate PDF Download.
	 *
	 * @acces private
	 *
	 * @param int  $order_id
	 * @param bool $retoure
	 *
	 * @return void, die()
	 * @throws Exception
	 */
	private function download_shipping_label( int $order_id, bool $retoure = false ) {

		$filename = $this->id . __('_shipping_', 'woocommerce-german-market') . ( $retoure ? __('return_', 'woocommerce-german-market') : '' ) . __('label_order_', 'woocommerce-german-market') . $order_id;
		$filename = apply_filters( 'wgm_shipping_' . $this->id . '_filename_shipping_label', $filename, $order_id, $retoure );

		$provider            = Woocommerce_Shipping::get_instance()->get_provider_by_id( $this->id );
		$print_retoure_label = ( 'on' === $provider::$options->get_option( 'label_retoure_enabled', 'off' ) );
		$pdf_merge           = new Pdf_Merger();
		$temp_file_handlers  = array();

		if ( ! $retoure ) {
			$shipment_labels = Woocommerce_Shipping::$order_meta->get_shipment_label( $order_id );
			foreach ( $shipment_labels as $label ) {
				// Save binary shipment label data to temp file.
				$tmp_file_label       = tmpfile();
				$tmp_file_path        = stream_get_meta_data( $tmp_file_label )[ 'uri' ];
				$temp_file_handlers[] = $tmp_file_label;
				@fwrite( $tmp_file_label, $label[ 'order_label' ] );
				$pdf_merge->addPDF( $tmp_file_path );
				if ( ! empty( $label[ 'order_label_retoure' ] ) && $print_retoure_label ) {
					// Save binary shipment retoure label data to temp file.
					$tmp_file_retoure_label = tmpfile();
					$tmp_file_path          = stream_get_meta_data( $tmp_file_retoure_label )[ 'uri' ];
					$temp_file_handlers[]   = $tmp_file_retoure_label;
					@fwrite( $tmp_file_retoure_label, $label[ 'order_label_retoure' ] );
					$pdf_merge->addPDF( $tmp_file_path );
				}
			}
		} else {
			$shipment_labels = Woocommerce_Shipping::$order_meta->get_shipment_retoure_label( $order_id );
			foreach ( $shipment_labels as $label ) {
				if ( ! empty( $label[ 'order_label_retoure' ] ) ) {
					// Save binary shipment retoure label data to temp file.
					$tmp_file_retoure_label = tmpfile();
					$tmp_file_path          = stream_get_meta_data( $tmp_file_retoure_label )[ 'uri' ];
					$temp_file_handlers[]   = $tmp_file_retoure_label;
					@fwrite( $tmp_file_retoure_label, $label[ 'order_label_retoure' ] );
					$pdf_merge->addPDF( $tmp_file_path );
				}
			}
		}

		$pdf = $pdf_merge->merge( 'string' );

		// Temp files will be automatically deleted from server when closing the file handler.
		foreach ( $temp_file_handlers as $file_handler ) {
			fclose( $file_handler );
		}

		header( 'Content-Type: application/pdf' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '.pdf"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );

		die( $pdf );
	}

	/**
	 * Return a fake order note markup.
	 *
	 * @access public
	 * @static
	 *
	 * @param string $note
	 *
	 * @return string
	 */
	public static function get_order_note_markup( string $note ) : string {

		$time = current_datetime();

		$output  = '<li class="note system-note">';
		$output .= '	<div class="note_content">';
		$output .= '		' . wpautop( wptexturize( wp_kses_post( $note ) ) );
		$output .= '	</div>';
		$output .= '	<p class="meta">';
		$output .= '		<abbr class="exact-date">' . sprintf( __( '%1$s at %2$s', 'woocommerce' ), $time->format( wc_date_format() ), $time->format( wc_time_format() ) ) . '</abbr>';
		$output .= '	</p>';
		$output .= '</li>';

		return $output;
	}
}
