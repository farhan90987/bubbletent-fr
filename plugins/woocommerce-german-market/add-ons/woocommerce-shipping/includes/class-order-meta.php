<?php
/**
 * This class handles the order metadata.
 */

namespace MarketPress\GermanMarket\Shipping;

class Order_Meta {

	/**
	 * Singleton.
	 *
	 * @acces protected
	 * @static
	 *
	 * @var self
	 */
	protected static $instance;

	/**
	 * Singleton getInstance.
	 *
	 * @static
	 *
	 * @return self
	 */
	public static function get_instance() : self {

		return ( null !== self::$instance ) ? self::$instance : self::$instance = new self();
	}

	/**
	 * Class constructor.
	 */
	private function __construct() {

		// $this->drop_table();
		$this->maybe_create_table();
	}

	/**
	 * Creates database table.
	 *
	 * @return void
	 */
	public function create_table() {
		global $wpdb;

		$wpdb->hide_errors();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( $this->get_schema() );
	}

	/**
	 * Creates table if table does not exist.
	 *
	 * @return bool
	 */
	public function maybe_create_table() : bool {
		global $wpdb;

		$created_table = false;

		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}{$this->get_table_name()}'" );

		if ( ! $table_exists ) {
			$this->create_table();
			$created_table = true;
		}

		// Check if table column for export documents exists.

		if ( ! $this->table_column_exists( $wpdb->prefix . $this->get_table_name(), 'order_export_docs' ) ) {
			$wpdb->query( "ALTER TABLE " . $wpdb->prefix . $this->get_table_name() . " ADD order_export_docs MEDIUMBLOB" );
		}

		return $created_table;
	}

	/**
	 * Get database table name
	 *
	 * @return string
	 */
	private function get_table_name() : string {

		return 'gm_woocommerce_shipping_order_meta';
	}

	/**
	 * Get sql schema for database table.
	 *
	 * @return string
	 */
	public function get_schema() : string {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$table = "
CREATE TABLE {$wpdb->prefix}{$this->get_table_name()} (
    id BIGINT(20) unsigned auto_increment primary key,
    timestamp BIGINT(20) unsigned NOT NULL,
    order_id BIGINT(20) unsigned NOT NULL,
    shipment_number VARCHAR(255) NOT NULL,
    shipment_status VARCHAR(255),
    shipment_queue TEXT,
    order_label MEDIUMBLOB NOT NULL,
    order_label_retoure MEDIUMBLOB,
    order_export_docs MEDIUMBLOB,
    INDEX (order_id, shipment_number)
) ENGINE=InnoDB $collate;
	";

		return $table;
	}

	/**
	 * Drop table.
	 *
	 * @return void
	 */
	public function drop_table() {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$this->get_table_name()}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Returns if a given table column exists.
	 *
	 * @param string $table_name
	 * @param string $column_name
	 *
	 * @return bool
	 */
	public function table_column_exists( $table_name, $column_name ) : bool {
		global $wpdb;

		$column = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
			DB_NAME,
				$table_name,
				$column_name
		) );

		if ( ! empty( $column ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Store shipment label for order.
	 *
	 * @param int    $order_id order id
	 * @param string $shipment_number shipment number
	 * @param string $label_data binary label data
	 *
	 * @return int
	 */
	public function set_shipment_label( int $order_id, string $shipment_number, string $label_data ) {
		global $wpdb;

		$sql   = "INSERT INTO {$wpdb->prefix}{$this->get_table_name()} SET timestamp = %d, order_id = %d, shipment_number = %s, order_label = %s";
		$query = $wpdb->query(
			$wpdb->prepare(
				$sql,
				array(
					time(),
					$order_id,
					$shipment_number,
					$label_data,
				)
			)
		);

		// Store shipment numbers to order meta field.

		$order       = wc_get_order( $order_id );
		$shipment_id = $wpdb->insert_id; // Prevent getting 0 when using HPOS

		if ( is_object( $order ) ) {
			$stored_numbers = $order->get_meta( '_order_shipment_numbers', true );
			if ( ! empty( $stored_numbers ) ) {
				$stored_numbers_arr = explode( ',', $stored_numbers );
				if ( ! in_array( $shipment_number, $stored_numbers_arr ) ) {
					$stored_numbers_arr[] = $shipment_number;
				}
			} else {
				$stored_numbers_arr[] = $shipment_number;
			}
			$order->update_meta_data( '_order_shipment_numbers', implode( ',', $stored_numbers_arr ) );
			$order->save();
		}

		return $shipment_id;
	}

	/**
	 * Get the shipment label(s) for order id if exists.
	 *
	 * @param int $order_id order id
	 *
	 * @return array
	 */
	public function get_shipment_label( int $order_id ) : array {
		global $wpdb;

		$labels  = array();
		$sql     = "SELECT id, shipment_number, order_label, order_label_retoure FROM {$wpdb->prefix}{$this->get_table_name()} WHERE order_id = %d ORDER BY id";
		$results = $wpdb->get_results(
			$wpdb->prepare(
				$sql,
				array(
					$order_id,
				)
			), ARRAY_A
		);

		if ( ! empty( $results ) ) {
			foreach( $results as $row ) {
				$labels[] = $row;
			}
		}

		return $labels;
	}

	/**
	 * Store shipment retoure label for order.
	 *
	 * @param int    $shipment_id stored shipment id
	 * @param string $retoure_label_data binary label data
	 *
	 * @return int
	 */
	public function set_shipment_retoure_label( int $shipment_id, string $retoure_label_data ) : int {
		global $wpdb;

		$sql   = "UPDATE {$wpdb->prefix}{$this->get_table_name()} SET order_label_retoure = %s WHERE id = %d";
		$query = $wpdb->query(
			$wpdb->prepare(
				$sql,
				array(
					$retoure_label_data,
					$shipment_id,
				)
			)
		);

		return $wpdb->rows_affected;
	}

	/**
	 * Get the shipment retoure label for order id if exists.
	 *
	 * @param int $order_id order id
	 *
	 * @return array
	 */
	public function get_shipment_retoure_label( int $order_id ) : array {
		global $wpdb;

		$retoure_labels = array();
		$sql            = "SELECT id, shipment_number, order_label_retoure FROM {$wpdb->prefix}{$this->get_table_name()} WHERE order_id = %d AND order_label_retoure IS NOT NULL ORDER BY id";
		$results        = $wpdb->get_results(
			$wpdb->prepare(
				$sql,
				array(
					$order_id,
				)
			), ARRAY_A
		);

		if ( ! empty( $results ) ) {
			foreach( $results as $row ) {
				$retoure_labels[] = $row;
			}
		}

		return $retoure_labels;
	}

	/**
	 * Store export documents for order.
	 *
	 * @param int    $shipment_id stored shipment id
	 * @param string $export_documents_data binary documents data
	 *
	 * @return int
	 */
	public function set_shipment_export_documents( int $shipment_id, string $export_documents_data ) : int {
		global $wpdb;

		$sql   = "UPDATE {$wpdb->prefix}{$this->get_table_name()} SET order_export_docs = %s WHERE id = %d";
		$query = $wpdb->query(
			$wpdb->prepare(
				$sql,
				array(
					$export_documents_data,
					$shipment_id,
				)
			)
		);

		return $wpdb->rows_affected;
	}

	/**
	 * Get the export documents for order id if exists.
	 *
	 * @param int $order_id order id
	 *
	 * @return array
	 */
	public function get_shipment_export_documents( int $order_id ) : array {
		global $wpdb;

		$labels  = array();
		$sql     = "SELECT id, shipment_number, order_export_docs FROM {$wpdb->prefix}{$this->get_table_name()} WHERE order_id = %d ORDER BY id";
		$results = $wpdb->get_results(
			$wpdb->prepare(
				$sql,
				array(
					$order_id,
				)
			), ARRAY_A
		);

		if ( ! empty( $results ) ) {
			foreach( $results as $row ) {
				$labels[] = $row;
			}
		}

		return $labels;
	}

	/**
	 * Get all shipment numbers from order as array.
	 *
	 * @param int $order_id order id
	 *
	 * @return array
	 */
	public function get_shipment_numbers( int $order_id ) : array {
		global $wpdb;

		$shipment_numbers = array();
		$sql              = "SELECT shipment_number FROM {$wpdb->prefix}{$this->get_table_name()} WHERE order_id = %d ORDER BY id";
		$results          = $wpdb->get_results(
			$wpdb->prepare(
				$sql,
				array(
					$order_id,
				)
			), ARRAY_A
		);

		if ( ! empty( $results ) ) {
			foreach( $results as $row ) {
				$shipment_numbers[] = $row[ 'shipment_number' ];
			}
		}

		return $shipment_numbers;
	}

	/**
	 * Returns a boolean if we have any shipping label for order already.
	 *
	 * @param int $order_id order id
	 *
	 * @return bool
	 */
	public function has_shipping_label( int $order_id ) : bool {
		global $wpdb;

		$sql   = "SELECT COUNT(*) FROM {$wpdb->prefix}{$this->get_table_name()} WHERE order_id = %d AND order_label IS NOT NULL LIMIT 1";
		$count = $wpdb->get_var(
			$wpdb->prepare(
				$sql,
				array(
					$order_id,
				)
			)
		);

		return $count > 0;
	}

	/**
	 * Returns a boolean if we have any export documents for order already.
	 *
	 * @param int $order_id order id
	 *
	 * @return bool
	 */
	public function has_export_documents( int $order_id ) : bool {
		global $wpdb;

		$sql   = "SELECT COUNT(*) FROM {$wpdb->prefix}{$this->get_table_name()} WHERE order_id = %d AND order_export_docs IS NOT NULL LIMIT 1";
		$count = $wpdb->get_var(
			$wpdb->prepare(
				$sql,
				array(
					$order_id,
				)
			)
		);

		return $count > 0;
	}

	/**
	 * Set shipment status.
	 *
	 * @param int    $order_id order id
	 * @param string $status_data current shipment status
	 *
	 * @return void
	 */
	public function set_shipment_status( int $order_id, string $status_data ) {}

	/**
	 * Get shipment status.
	 *
	 * @param int    $order_id order id
	 * @param string $shipment_number shipment number
	 *
	 * @return string
	 */
	public function get_shipment_status( int $order_id, string $shipment_number ) {}

	/**
	 * Get the shipment queue.
	 *
	 * @param int    $order_id order id
	 * @param string $shipment_number shipment number
	 *
	 * @return array
	 */
	public function get_shipment_queue( int $order_id, string $shipment_number ) {}

	/**
	 * Delete all shipments for a given order id.
	 *
	 * @param int $order_id order id
	 *
	 * @return void
	 */
	public function delete_order_shipments( int $order_id ) {
		global $wpdb;

		$sql = "DELETE FROM {$wpdb->prefix}{$this->get_table_name()} WHERE order_id = %s";
		$wpdb->query(
			$wpdb->prepare(
				$sql,
				array(
					$order_id,
				)
			)
		);

		// Deleting all shipment numbers from order meta data.

		$order = wc_get_order( $order_id );

		if ( is_object( $order ) ) {
			$order->update_meta_data( '_order_shipment_numbers', '' );
			$order->save();
		}
	}

	/**
	 * Delete a shipment from a given order id and shipment number.
	 *
	 * @param int    $order_id
	 * @param string $shipment_number
	 *
	 * @return void
	 */
	public function delete_order_shipment( int $order_id, string $shipment_number ) {
		global $wpdb;

		$sql = "DELETE FROM {$wpdb->prefix}{$this->get_table_name()} WHERE order_id = %s AND shipment_number = %d";
		$wpdb->query(
			$wpdb->prepare(
				$sql,
				array(
					$order_id,
					$shipment_number,
				)
			)
		);
	}
}
