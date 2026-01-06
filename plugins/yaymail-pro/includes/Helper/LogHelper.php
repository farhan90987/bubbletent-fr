<?php

namespace YayMail\Helper;

defined( 'ABSPATH' ) || exit;

class LogHelper {

	public static function writeLog( $message, $type_log = 'error', $name = 'log' ) {
		if ( ! is_string( $message ) ) {
			$message = print_r( $message, true );
		}

		$folder = YAYMAIL_PLUGIN_PATH . '/includes/Logs';
		if ( ! file_exists( $folder ) ) {

			global $wp_filesystem;
			if ( empty( $wp_filesystem ) ) {
				require_once ABSPATH . '/wp-admin/includes/file.php';
				WP_Filesystem();
			}
			$wp_filesystem->mkdir( $folder, 0755 );
			$wp_filesystem->chmod( $folder, 0755 );

		}

		$filename = $folder . DIRECTORY_SEPARATOR . $name . '.txt';

		clearstatcache(); // Remove filesize cache

		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}
		$wp_filesystem->put_contents( $filename, current_time( 'mysql' ) . ' [' . strtoupper( $type_log ) . '] ' . $message . PHP_EOL );
	}

	public static function getMessageException( $ex, $ajax = false ) {
		$message  = __( 'SYSTEM ERROR:', 'yaymail' ) . $ex->getCode() . ' : ' . $ex->getMessage();
		$message .= PHP_EOL . $ex->getFile() . '(' . $ex->getLine() . ')';
		$message .= PHP_EOL . $ex->getTraceAsString();
		self::writeLog( $message );
		if ( $ajax ) {
			wp_send_json_error( array( 'mess' => $message ) );
		}

	}

	// writeLog use show content when save email, save
	public static function writeLogContent( $content = '', $tailName = 'html' ) {
		$name     = __( 'log-', 'yaymail' ) . current_time( 'timestamp' );
		$folder   = YAYMAIL_PLUGIN_PATH . '/includes/Logs';
		$filename = $folder . DIRECTORY_SEPARATOR . $name . '.' . $tailName;
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}
		$wp_filesystem->put_contents( $filename, $content );
	}
}
