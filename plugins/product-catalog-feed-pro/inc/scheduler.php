<?php

/**
 * Class WPWOOF_Scheduler
 * Product catalog scheduler
 *
 * Handles scheduling and execution of feed regeneration tasks for the WPWOOF plugin.
 */
class WPWOOF_Scheduler {

	function __construct() {
		if ( strpos( $_SERVER['REQUEST_URI'], '/feeds_update' ) !== false ) {
			return 0;
		}
		if ( isset( $GLOBALS['woocommerce_wpwoof_common'] ) ) {
			global $woocommerce_wpwoof_common;
			$global_data = $woocommerce_wpwoof_common->getGlobalData();
		} else {
			$global_data = get_option( 'wpwoof-global-data', array() );
		}

		if ( isset( $global_data['regeneration_method'] ) && $global_data['regeneration_method'] == 'scheduler' ) {
			add_action( 'wp_loaded', array( $this, 'check_scheduled_feeds' ), 20 );
		}
	}

	/**
	 * Checks if there are any scheduled feeds that need to be processed.
	 *
	 * This function determines whether there are past-due feed generation jobs that require action.
	 * If scheduled feed jobs exist, it triggers an asynchronous HTTP request to execute the feed update process.
	 *
	 * @return bool Returns true if the process is successfully triggered; otherwise, false.
	 */
	function check_scheduled_feeds(): bool {
		$last_run = get_transient( 'wpwoof_scheduler_last_run' );
		if ( $last_run && $last_run > time() - 60 ) {
			return false;
		}
		$need_run = false;
		if ( isset( $GLOBALS['woocommerce_wpwoof_common'] ) ) {
			global $woocommerce_wpwoof_common;
			$jobs = $woocommerce_wpwoof_common->get_scheduled_feeds( 'past' );
			if ( ! empty( $jobs ) ) {
				$need_run = true;
			}
		} else {
			$all_jobs = get_option( 'feed_gen_schedule', array() );
			foreach ( $all_jobs as $feed_id => $job_time ) {
				if ( $job_time <= time() ) {
					$need_run = true;
					break;
				}
			}

		}

		if ( $need_run ) {
			set_transient( 'wpwoof_scheduler_last_run', time() );
			$url  = add_query_arg( 'time', time(), site_url( 'wp-json/wpwoof/v1/feeds_update' ) );
			$args = array(
				'timeout'   => 0.01,
				'blocking'  => false,
				'sslverify' => false
			);

			if ( defined( 'WPWOOF_SCHEDULER_REQUEST_USERNAME' )
			     && defined( 'WPWOOF_SCHEDULER_REQUEST_PASSWORD' ) ) {
				$args['headers'] = array(
					'Authorization' => 'Basic ' . base64_encode( WPWOOF_SCHEDULER_REQUEST_USERNAME . ':' . WPWOOF_SCHEDULER_REQUEST_PASSWORD )
				);
			}

			$result = wp_remote_get( $url, $args );

			return ! is_wp_error( $result );

		}

		return false;
	}
}

$scheduler = new WPWOOF_Scheduler();


