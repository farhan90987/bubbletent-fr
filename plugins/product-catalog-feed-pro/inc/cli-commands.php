<?php

// Check if WP-CLI is available
if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

/**
 * Class WPWOOF_CLI_Commands
 *
 * Provides CLI commands for WPWOOF functionalities.
 */
class WPWOOF_CLI_Commands {

	/**
	 * Calls a feeds_update.
	 *
	 * ## EXAMPLES
	 *
	 *     Wp wpwoof_product_catalog_feed feeds_update
	 */
	public function feeds_update( $args, $assoc_args ) {
		global $woocommerce_wpwoof_common;

		$woocommerce_wpwoof_common->run_scheduled_feeds();

		// Print success message
		\WP_CLI::success( 'Method executed successfully!' );
	}
}

// Register the command.
// The first argument 'wpwoof_product_catalog_feed' is the command name, the second is the class with the methods to execute.
\WP_CLI::add_command( 'wpwoof_product_catalog_feed', 'WPWOOF_CLI_Commands' );