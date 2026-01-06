<?php

class WP_WC_Invoice_Pdf_Order_Meta {
	
	/**
	 * Order Object to handle saved contend
	 *
	 * @var WC_Order
	 */
	protected $order;

	/**
	 * Is db tabel installed
	 *
	 * @var Boolean
	 */
	public static $db_is_installed = null;

	/**
	 * Simple construct
	 * 
	 * @return void
	 */
	public function __construct( $order ) {
		$this->order = $order;
	}

	/**
	 * Creates database table
	 * 
	 * @return void
	 */
	public static function create_table() {

		global $wpdb;

		$wpdb->hide_errors();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( self::get_schema() );
	}	

	/**
	 * Creates table if table does not exists
	 * 
	 * @return Boolean
	 */
	public static function maybe_create_table() {

		global $wpdb;
		$created_table = false;

		if ( true !== self::$db_is_installed ) {
			
			$table_name = self::get_table_name();
			$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}{$table_name}'" );
			
			if ( ! $table_exists ) {
				self::create_table();
				$created_table = true;
			}

			$has_compression_column = false;
			foreach ( $wpdb->get_col( "DESC {$wpdb->prefix}{$table_name}", 0 ) as $column ) {
				if ( 'compression' === $column ) {
					$has_compression_column = true;
					break;
				}
			}

			if ( ! $has_compression_column ) {

				$wpdb->query( "ALTER TABLE {$wpdb->prefix}{$table_name} CHANGE `saved_content` `saved_content` LONGBLOB NOT NULL;" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}{$table_name} ADD `compression` tinyint(1) NOT NULL DEFAULT '0' AFTER `saved_content`;" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}{$table_name} ADD INDEX `order_id` (`order_id`);"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			}

			self::$db_is_installed = true;
		}

		return $created_table;
	}

	/**
	 * Get database table name
	 * 
	 * @return String
	 */
	private static function get_table_name() {
		return 'gm_invoice_pdf_order_meta';
	}

	/**
	 * Get sql schema for database table
	 * 
	 * @return String
	 */
	private static function get_schema() {

		global $wpdb;
		$table_name = self::get_table_name();
		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$table = "
CREATE TABLE {$wpdb->prefix}{$table_name} (
  id bigint(20) unsigned auto_increment,
  order_id bigint(20) unsigned NOT NULL,
  saved_content LONGBLOB NOT NULL,
  compression tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (id),
  KEY order_id (order_id)
) $collate;
		";

		return $table;
	}

	/**
	 * Drop table
	 *
	 * @return void
	 */
	public static function drop_table() {
		global $wpdb;
		$table_name = self::get_table_name();
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$table_name}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Add meta data to db table
	 *
	 * @param String $content
	 * @return int|bool
	 */
	public function add_meta( $content ) {

		global $wpdb;
		self::maybe_create_table();
		$table_name = self::get_table_name();

		$compression = 0;
		if ( function_exists( 'gzcompress' ) ) {
			if ( apply_filters( 'wp_wc_invoice_pdf_use_compression_for_saved_content', true ) ) {
				$compression = 1;
				$content = gzcompress( $content, 9 );
			}
		}

		$result = $wpdb->query(
			$wpdb->prepare( 
				"
				INSERT INTO {$wpdb->prefix}{$table_name} ( `order_id`, `saved_content`, `compression` )
				VALUES( %d,	%s, %d )
				",
				$this->order->get_id(),
				$content,
				$compression
			)
		);

		return $result;
	}

	/**
	 * Returns if meta data exists without getting it (much faster)
	 *
	 * @return Boolean
	 */
	public function has_meta() {

		global $wpdb;
		self::maybe_create_table();
		$table_name = self::get_table_name();
		$has_meta = false;

		$post_meta = get_post_meta( $this->order->get_id(), '_wp_wc_invoice_pdf_saved_html', true );
		
		if ( ! empty( $post_meta ) ) {

			$has_meta = true;

		} else {

			$count = $wpdb->get_var(
				
				$wpdb->prepare( 
					"SELECT id FROM {$wpdb->prefix}{$table_name} WHERE order_id = %d LIMIT 1;",
					 $this->order->get_id()
				),

			);

			$has_meta = ! is_null( $count );

		}

		return $has_meta;

	}

	/**
	 * Get meta from db table and make a migration from post meta to custom db table
	 *
	 * @return String
	 */
	public function get_meta() {

		global $wpdb;
		self::maybe_create_table();
		$table_name = self::get_table_name();
		$saved_content = '';

		$post_meta = get_post_meta( $this->order->get_id(), '_wp_wc_invoice_pdf_saved_html', true );
		
		if ( ! empty( $post_meta ) ) {
			
			$saved_content = $post_meta;
			delete_post_meta( $this->order->get_id(), '_wp_wc_invoice_pdf_saved_html' );
			$this->order->delete_meta_data( '_wp_wc_invoice_pdf_saved_html' );
			$this->order->save_meta_data();
			$this->add_meta( $saved_content );

		} else {

			$maybe_saved_content_object = $wpdb->get_results(
				
				$wpdb->prepare( 
					"SELECT saved_content, compression FROM {$wpdb->prefix}{$table_name} WHERE order_id = %d ORDER BY order_id DESC LIMIT 1;",
					 $this->order->get_id()
				),

				ARRAY_A

			);

			if ( isset( $maybe_saved_content_object[ 0 ] ) ) {
				$maybe_saved_content = $maybe_saved_content_object[ 0 ][ 'saved_content' ];
				$compression = intval( $maybe_saved_content_object[ 0 ][ 'compression' ] );

				if ( $maybe_saved_content && ! empty( $maybe_saved_content ) ) {
					if ( 0 === $compression ) {
						$saved_content = $maybe_saved_content;
					} else {
						$saved_content = gzuncompress( $maybe_saved_content );
					}
					
				}
			}

		}

		return $saved_content;
	}

	/**
	 * Delete meta of this order from db table
	 *
	 * @return int|bool
	 */
	public function delete_meta() {
		
		global $wpdb;
		self::maybe_create_table();
		$table_name = self::get_table_name();
		
		delete_post_meta( $this->order->get_id(), '_wp_wc_invoice_pdf_saved_html' );
		$this->order->delete_meta_data( '_wp_wc_invoice_pdf_saved_html' );
		$this->order->save_meta_data();
		
		$result = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}{$table_name} WHERE `order_id` = %d;",
				$this->order->get_id()
			)
		);

		return $result;
	}
}
