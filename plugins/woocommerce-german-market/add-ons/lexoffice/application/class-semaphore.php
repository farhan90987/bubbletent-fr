<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'German_Market_Lexoffice_Semaphore' ) ) {
	
	class German_Market_Lexoffice_Semaphore {

		/**
		* @var null | String
		* @access public
		* path to lock file
		*/
		public static $lock_file 	= null;

		/**
		* @var Ressource
		* @access public
		* ressource semaphore returned by by fopen
		*/
		public static $ressource = null;

		/**
		* Flock init
		*
		* @access public
		* @static
		* @return void
		*/
		public static function init() {

			$try_path_cache = untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'woocommerce-german-market-lexoffice' . DIRECTORY_SEPARATOR;
			$wp_uploads = wp_upload_dir();
			$wp_uploads_dir = untrailingslashit( $wp_uploads[ 'basedir' ] ) . DIRECTORY_SEPARATOR . 'woocommerce-german-market-lexoffice' . DIRECTORY_SEPARATOR;

			if ( wp_mkdir_p( $try_path_cache ) ) {
				$maybe_lock_file = $try_path_cache . 'lockfile.lock';
				if ( touch( $maybe_lock_file ) ) {
					self::$lock_file = $maybe_lock_file;
				}
			}

			if ( is_null( self::$lock_file ) ) {
				if ( wp_mkdir_p( $wp_uploads_dir ) ) {
					$maybe_lock_file = $wp_uploads_dir . 'lockfile.lock';
					if ( touch( $maybe_lock_file ) ) {
						self::$lock_file = $maybe_lock_file;
					}
				}
			}
		}

		/**
		* Semaphore get (like sem_get)
		*
		* @access public
		* @static
		* @return Ressource | String
		*/
		public static function sem_get() {

			if ( is_null( self::$ressource ) ) {
				self::$ressource = fopen( self::$lock_file, 'a' ); 
			}

			return self::$ressource;
		}

		/**
		* Semaphore aquire (like sem_acquire)
		*
		* @access public
		* @static
		* @return Boolean
		*/
		public static function sem_acquire() {
			return flock( self::$ressource, LOCK_EX );
		}

		/**
		* Semaphore release (like sem_release)
		*
		* @access public
		* @static
		* @return Boolean
		*/
		public static function sem_release() {
			return flock( self::$ressource, LOCK_UN );
		}

	}
}
