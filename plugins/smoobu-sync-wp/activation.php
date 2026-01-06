<?php
/**
 * SQL queries on plugin activation
 *
 * @package smoobu-calendar
 */

/**
 * SQL functions to be runned after plugin activation
 *
 * @return void
 */
function smoobu_activation() {
	global $wpdb;

	$table_name      = $wpdb->prefix . 'smoobu_calendar_availability';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		`property_id` int(11) NOT NULL,
		`busy_dates` LONGTEXT NOT NULL,
		`open_dates` LONGTEXT NOT NULL,
		PRIMARY KEY (property_id)
	) $charset_collate;";

	$property_details_table = $wpdb->prefix . 'smoobu_property_details';
	$sql_details_table      = "CREATE TABLE $property_details_table (
		`property_id` int(11) NOT NULL,
		`max_guests` int NOT NULL,
		`add_ons` LONGTEXT,
		`extra_cost` float(11),
		`extra_starts_at` int,
        PRIMARY KEY (property_id)
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	dbDelta( $sql );
	dbDelta( $sql_details_table );
}
