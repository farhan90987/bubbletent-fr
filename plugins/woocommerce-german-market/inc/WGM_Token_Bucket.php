<?php
use bandwidthThrottle\tokenBucket\Rate;
use bandwidthThrottle\tokenBucket\TokenBucket;
use bandwidthThrottle\tokenBucket\BlockingConsumer;
use bandwidthThrottle\tokenBucket\storage\FileStorage;

/**
 * Implements a token bucket 
 * Multiple components of German Market can use it independently or interdependently
 */
final class WGM_Token_Bucket {

	/**
	* Package name
	*/
	public $package_name = null;

	/**
	* Packages
	*/
	private static $packages = array();

	/**
	 * Path for .bucket files
	 */
	public static $path = null;

	/**
	 * Create a token bucket and a blocking consumer
	 * 
	 * @param String $package_name
	 * @param Integer $limit_per_second
	 * @param Integer $bucket_capacity
	 * @param Integer $bootstrap
	 * @return void
	 */
	public function __construct( $package_name, $limit_per_second = 2, $bucket_capacity = 2, $bootstrap = 1 ) {

		if ( ! class_exists( 'bandwidthThrottle\tokenBucket\TokenBucket' ) ) {
			include_once( untrailingslashit( Woocommerce_German_Market::$plugin_path ) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'token-bucket' . DIRECTORY_SEPARATOR . 'autoload.php' );
		}

		$package_name = sanitize_file_name( $package_name );
		$this->package_name = $package_name;

		if ( ! isset( self::$packages[ $package_name ] ) ) {

			$path = $this->get_path();
			
			if ( false !== $path) {

				$file_name = $package_name . $limit_per_second . ".bucket";

				try {
					self::$packages[ $package_name ][ 'storage' ] = new FileStorage( $path . $file_name );
					self::$packages[ $package_name ][ 'rate' ] = new Rate( $limit_per_second , Rate::SECOND );
					self::$packages[ $package_name ][ 'bucket' ] = new TokenBucket( $bucket_capacity, self::$packages[ $package_name ][ 'rate' ], self::$packages[ $package_name ][ 'storage' ] );
					self::$packages[ $package_name ][ 'consumer' ] = new BlockingConsumer( self::$packages[ $package_name ][ 'bucket' ] );
					self::$packages[ $package_name ][ 'bucket' ]->bootstrap( $bootstrap );
				} catch ( Exception $e ) {

					self::$path = false;
					$logger = wc_get_logger();
					$context = array( 'source' => 'german-market-lexoffice' );
					$logger->info( 'Could not init token bucket: ' . $e->getMessage(), $context );

				}
			} else {
			
				$logger = wc_get_logger();
				$context = array( 'source' => 'german-market-lexoffice' );
				$logger->info( 'Could not init token bucket: ' . $package_name, $context );
			}
		}
	}

	/**
	 * Consume a token of the bucket (or more)
	 * 
	 * @pram $consume_number
	 */
	public function consume( $consume_number = 1 ) {
		
		if ( false !== $this->get_path() ) {
			self::$packages[ $this->package_name ][ 'consumer' ]->consume( $consume_number );
		}
	}

	/**
	 * Get path for .bucket file
	 * 
	 * @return String
	 */
	public function get_path() {

		if ( is_null( self::$path ) ) {
			
			self::$path = false;
			$wp_uploads = wp_upload_dir();

			$possible_paths = array(
				'cache' => untrailingslashit( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'woocommerce-german-market-token-bucket' . DIRECTORY_SEPARATOR,
				'dir' => __DIR__ . DIRECTORY_SEPARATOR,
				'uploads' => untrailingslashit( $wp_uploads[ 'basedir' ] ) . DIRECTORY_SEPARATOR . 'woocommerce-german-market-token-bucket' . DIRECTORY_SEPARATOR,
			);

			foreach ( $possible_paths as $maybe_path ) {
				if ( wp_mkdir_p( $maybe_path ) ) {
					$maybe_lock_file = $maybe_path . 'german-market.bucket';
					if ( touch( $maybe_lock_file ) ) {
						self::$path = $maybe_path;
						break;
					}
				}
			}
		}

		return apply_filters( 'german_market_token_bucket_path', self::$path, $this->package_name );
	}
}
