<?php

class WP_WC_Invoice_Pdf_Order_Meta_Migrator {
	
	/**
	 * @var WP_WC_Invoice_Pdf_Order_Meta_Migrator
	 */
	private static $instance = null;

	/**
	* Singletone get_instance
	*
	* @static
	* @return WGM_Checkbox_Product_Depending
	*/
	public static function get_instance() {
		if ( self::$instance == NULL) {
			self::$instance = new WP_WC_Invoice_Pdf_Order_Meta_Migrator();
		}
		return self::$instance;
	}

	/**
	* Singletone constructor
	*
	* @access private
	*/
	private function __construct() {

		add_action( 'german_market_migration_invoice_pdf', array( $this, 'migrate_one_order' ), 10, 2 );
		add_action( 'german_market_migration_invoice_pdf_start', array( $this, 'start_migrator' ) );

		add_action( 'german_market_migration_invoice_pdf_compress_check', array( $this, 'daily_check_start_compressing' ) );
		add_action( 'german_market_migration_invoice_pdf_compress', array( $this, 'start_compressing' ) );
		
		add_action( 'init', function() {
			
			// custom db table migrator
			if ( get_option( 'german_market_migration_invoice_pdf_version', '' ) !== '1' ) {

				WP_WC_Invoice_Pdf_Order_Meta::maybe_create_table();

				WC()->queue()->schedule_single( time(), 'german_market_migration_invoice_pdf_start' );
				update_option( 'german_market_migration_invoice_pdf_version', '1' );
			}
			
			// compression migrator
			if ( get_option( 'german_market_migration_invoice_pdf_compress_version', '' ) !== '1' ) {

				WP_WC_Invoice_Pdf_Order_Meta::maybe_create_table();
				
				if ( function_exists( 'gzcompress' ) ) {
					if ( apply_filters( 'wp_wc_invoice_pdf_use_compression_for_saved_content', true ) ) {
						WC()->queue()->schedule_single( time() + DAY_IN_SECONDS, 'german_market_migration_invoice_pdf_compress_check' );
					}
				}

				update_option( 'german_market_migration_invoice_pdf_compress_version', '1' );
			}

		});
	}

	/**
	 * This method migrate all orders
	 * 
	 * @return void
	 */
	public function start_migrator() {

		global $wpdb;

		// Part 1
		$limit = 1000;
		$check_limit = 2;

		// Is very any date stored in postmeta table of wordpress?
		$results = $wpdb->get_results(
			$wpdb->prepare( 
				
				"
				SELECT post_id FROM {$wpdb->prefix}postmeta 
				WHERE meta_key = %s 
				ORDER BY post_id DESC 
				LIMIT %d;
				",

				'_wp_wc_invoice_pdf_saved_html',
				$check_limit
			),

			ARRAY_A
		);

		// If there are db entries
		if ( count( $results ) >= 1 ) {
			
			// Copy data from postmeta to our custom table
			$wpdb->query(
				
				$wpdb->prepare(
					
					"
					INSERT INTO {$wpdb->prefix}gm_invoice_pdf_order_meta ( order_id, saved_content ) 
					SELECT post_id AS order_id, meta_value AS saved_content FROM {$wpdb->prefix}postmeta 
					WHERE meta_key = %s
					ORDER BY post_id DESC
					LIMIT %d;
					",
					
					'_wp_wc_invoice_pdf_saved_html',
					$limit,
				)
				
			);

			// Delete postmeta
			$wpdb->query(
				
				$wpdb->prepare(
					
					"
					DELETE FROM {$wpdb->prefix}postmeta 
					WHERE meta_key = %s
					ORDER BY post_id DESC
					LIMIT %d;
					",

					'_wp_wc_invoice_pdf_saved_html',
					$limit
				)
				
			);

			// Is there any postmeta data left?
			$results = $wpdb->get_results(
				$wpdb->prepare( 
					
					"
					SELECT post_id FROM {$wpdb->prefix}postmeta 
					WHERE meta_key = %s 
					ORDER BY post_id DESC 
					LIMIT %d;
					",

					'_wp_wc_invoice_pdf_saved_html',
					$check_limit
				),

				ARRAY_A
			);

			// If so, restart in one minute
			if ( count( $results ) >= 1 ) {
				WC()->queue()->schedule_single( time() + MINUTE_IN_SECONDS, 'german_market_migration_invoice_pdf_start' );
				return;
			}
		}


		// part 2
		// nothing happens here if hpos migration has not happened yet
		// nothing happens if post meta and hpos meta is synched, because hpos meta was already deleted in step 1

		$limit = 50;

		$args = array(
			'meta_key'     	=> '_wp_wc_invoice_pdf_saved_html',
			'meta_compare' 	=> 'EXISTS',
			'limit'			=> $limit,
			'return'		=> 'ids',
		);

		$orders = wc_get_orders( $args );
			
		$counter = 0;
		$counter_limit = $limit - 5;

		foreach ( $orders as $order_id ) {

			$start_new = false;

			if ( $counter >= $counter_limit ) {
				$start_new = true;
			} 

			WC()->queue()->add( 'german_market_migration_invoice_pdf', array( 'order_id' => $order_id, 'start_new' => $start_new ), 'german_market_migration_invoice_pdf' );

			$counter++;

			if ( $counter >= $counter_limit ) {
				return;
			}
		}
	}

	/**
	 * This method migrates one order
	 * 
	 * @param Integer $order_id
	 * @param Boolean $start_new
	 * @return void
	 */
	public function migrate_one_order( $order_id, $start_new ) {
		
		$order_oject = wc_get_order( $order_id );
		if ( is_object( $order_oject ) && method_exists( $order_oject, 'get_meta' ) ) {
			$invoice_pdf_order_meta = new WP_WC_Invoice_Pdf_Order_Meta( $order_oject );
			$meta = $invoice_pdf_order_meta->get_meta(); // migration happens here
		}

		if ( $start_new ) {
			// Restart
			WC()->queue()->schedule_single( time() + 10, 'german_market_migration_invoice_pdf_start' );
		}
	}

	/**
	 * Check if there is still postmeta data, if not => start migration
	 * 
	 * @wp-hook german_market_migration_invoice_pdf_compress_check
	 * @return void
	 */
	public function daily_check_start_compressing() {

		global $wpdb;

		$check_limit = 2;

		// Is very any date stored in postmeta table of wordpress?
		$results = $wpdb->get_results(
			$wpdb->prepare( 
				
				"
				SELECT post_id FROM {$wpdb->prefix}postmeta 
				WHERE meta_key = %s 
				ORDER BY post_id DESC 
				LIMIT %d;
				",

				'_wp_wc_invoice_pdf_saved_html',
				$check_limit
			),

			ARRAY_A
		);

		if ( ! ( count( $results ) >= 1 ) ) {

			// If there are no db entries: migration to custom db table has been completed 
			WC()->queue()->schedule_single( time() + HOUR_IN_SECONDS, 'german_market_migration_invoice_pdf_compress' );

		} else {

			// migration to custom db is not completed, yet => check again tomorrow
			WC()->queue()->schedule_single( time() + DAY_IN_SECONDS, 'german_market_migration_invoice_pdf_compress_check' );
		}
	}

	/**
	 * Compress some saved content
	 * 
	 * @wp-hook german_market_migration_invoice_pdf_compress_check
	 * @return void
	 */
	public static function start_compressing() {

		global $wpdb;

		$limit = 150;
		$check_limit = 2;
		
		// get saved content with compression = 0 from custom db
		$results = $wpdb->get_results(
			$wpdb->prepare( 
				
				"
				SELECT id, saved_content FROM {$wpdb->prefix}gm_invoice_pdf_order_meta
				WHERE compression = '0' 
				LIMIT %d;
				",

				$limit
			),

			ARRAY_A
		);

		foreach ( $results as $saved_content_object ) {
			
			if ( isset( $saved_content_object[ 'saved_content' ] ) && isset( $saved_content_object[ 'id' ] ) ) {

				// compress saved_content
				$compressed_saved_content = gzcompress( $saved_content_object[ 'saved_content' ], 9 );

				// update row
				$updated = $wpdb->update(

					$wpdb->prefix . 'gm_invoice_pdf_order_meta',

					array(
						'saved_content' => $compressed_saved_content,
						'compression' => '1'
					),

					array(
						'id' => intval( $saved_content_object[ 'id' ] )
					)

				);
			}
		}

		// any data left => restart this scheduler
		$results = $wpdb->get_results(
			$wpdb->prepare( 
				
				"
				SELECT id, saved_content FROM {$wpdb->prefix}gm_invoice_pdf_order_meta
				WHERE compression = '0' 
				LIMIT %d;
				",

				$check_limit
			),

			ARRAY_A
		);

		if ( count( $results ) >= 1 ) {
			WC()->queue()->schedule_single( time() + MINUTE_IN_SECONDS * 5, 'german_market_migration_invoice_pdf_compress' );
		}
	}
}
