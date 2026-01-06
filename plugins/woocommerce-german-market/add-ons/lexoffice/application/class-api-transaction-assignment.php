<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class German_Market_Lexoffice_API_Transaction_Assignment
 *
 * @author MarketPress
 */
class German_Market_Lexoffice_API_Transaction_Assignment {

	/**
	 * Error message, empty in case of success
	 * 
	 * @var String
	 */
	private $error_message = '';

	/**
	 * Status mesasge
	 * 
	 * @var String
	 */
	private $status = '';

	/**
	 * Success or not
	 * 
	 * @var boolean
	 */
	private $success = false;

	/**
	 * Construct
	 * 
	 * @param WC_Order $order_object
	 * @return void
	 */
	public function __construct( $order_object, $type = 'voucher' ) {

		if ( is_object( $order_object ) && method_exists( $order_object, 'get_transaction_id' ) ) {
			
			$has_transaction_assignment = ! empty( $order_object->get_meta( '_lexoffice_woocommerce_has_transaction_assignment' ) );
			
			if ( 'voucher' === $type ) {
				$voucher_id = $order_object->get_meta( '_lexoffice_woocomerce_has_transmission' );
			} else if ( 'invoice' === $type ) {
				$voucher_id = $order_object->get_meta( '_lexoffice_woocomerce_has_transmission_invoice_api' );
			} else {
				$voucher_id = '';
			}
			
			$transaction_id = $order_object->get_transaction_id();

			if ( 
				( ! $has_transaction_assignment ) &&
				( ! empty( $voucher_id ) ) && 
				( ! empty( $transaction_id ) ) &&
				/**
				 * Let user decide if the transaction assignment should be executed
				 * 
				 * @since 3.39
				 * @param Boolean
				 * @param WC_Order $order_object
				 */
				apply_filters( 'woocommerce_de_lexoffice_send_transaction_assignment_for_order', true, $order_object ) 
			) {
				
				$this->transaction_assignment( $voucher_id, $transaction_id );
				
				if ( $this->success ) {
					$order_object->update_meta_data( '_lexoffice_woocommerce_has_transaction_assignment', $transaction_id );
					$order_object->save_meta_data();
				}

			} else {
				
				if ( $has_transaction_assignment ) {
					$this->status = 'Assignment already exists';
				} else {
					$this->status = 'No assignment transferred. Transaction ID: "' . $transaction_id . '", Voucher ID: "' . $voucher_id . '"';
				}
			}
		}
	}

	/**
	 * Send transaction assignment to lexoffice
	 * 
	 * @param String $voucher_id
	 * @param String $transaction_id
	 * @return void
	 */
	public function transaction_assignment( $voucher_id, $transaction_id ) {

		$data = array(
			'voucherId'			=> $voucher_id,
			'externalReference'	=> $transaction_id
		);

		ini_set( 'serialize_precision', -1 );
		$json = json_encode( $data, JSON_PRETTY_PRINT );

		$token_bucket = new WGM_Token_Bucket( 'lexoffice-transactionassignmenthint', 2 );
		$token_bucket->consume();

		$curl = curl_init();

		curl_setopt_array( $curl,
			array(
			  	CURLOPT_URL => German_Market_Lexoffice_API_Auth::get_base_url() . "v1/transaction-assignment-hint",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => $json,
				CURLOPT_HTTPHEADER => array(
				    "accept: application/json",
				    "authorization: Bearer " . German_Market_Lexoffice_API_Auth::get_bearer(),
				    "cache-control: no-cache",
				    "content-type: application/json",
				  ),
			)
		);

		$response_post 	= curl_exec( $curl );
		$response_array = json_decode( $response_post, true );

		if ( isset( $response_array[ 'error' ] ) ) {
			
			$this->error_message = $response_array[ 'error' ];
			if ( isset( $response_array[ 'message' ] ) ) {
				$this->error_message .= ' ' . $response_array[ 'message' ];
			}
		
		} else if ( isset( $response_array[ 'voucherId' ] ) ) {
			$this->success = true;
		}
	}

	/**
	 * Get status
	 * 
	 * @return String
	 */
	public function get_status() {
		
		if ( $this->success ) {
			$this->status = 'success';
		} else {
			if ( empty( $this->status ) ) {
				if ( empty( $this->error_message ) ) {
					$this->status = 'unknown';
				} else {
					$this->status = $this->error_message;
				}
			} else {
				if ( ! empty( $this->error_message ) ) {
					$this->status .= ' ' . $this->error_message;
				}
			}
		}

		return $this->status;
	}
}
