<?php
/**
 * Mutual exclusion between Free/Pro (and multiple installs of the same edition).
 *
 * Include this file at the top of both main plugin files, right after the ABSPATH guard
 * and before any defines/classes/includes/autoloaders.
 *
 * Behavior:
 *  - On activation: remember the just-activated plugin and deactivate all other editions.
 *  - On runtime (plugins_loaded, priority 0): if multiple are active:
 *      - Keep the one that was just activated (transient), if present (then delete the transient).
 *      - Otherwise: prefer Pro; if multiple of the same edition, keep the highest Version.
 *  - Multisite-aware (network-wide).
 *
 * Notes:
 *  - Avoid declaring global functions/classes/constants here (to prevent name collisions).
 *  - PHP 7.0+ compatible.
 */

// Determine the main plugin file that included this bootstrap.
// IMPORTANT: we rely on a local variable from the including file.
$this_file     = isset( $wpwoof_main_file ) ? $wpwoof_main_file : __FILE__;
$this_basename = function_exists( 'plugin_basename' ) ? plugin_basename( $this_file ) : basename( $this_file );

call_user_func( function () use ( $this_file, $this_basename ) {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	// Main plugin file names considered mutually exclusive (folder may vary)
	$target_filenames = array( 'product-catalog-feed.php', 'product-catalog-feed-pro.php' );

	// Collect candidates with metadata
	$all_plugins = get_plugins();
	$candidates  = array();
	foreach ( $all_plugins as $plugin_basename => $plugin_data ) {
		$fname = basename( $plugin_basename );
		if ( in_array( $fname, $target_filenames, true ) ) {
			$is_pro       = ( $fname === 'product-catalog-feed-pro.php' );
			$version      = isset( $plugin_data['Version'] ) ? (string) $plugin_data['Version'] : '0';
			$candidates[] = array(
				'basename' => $plugin_basename,
				'is_pro'   => $is_pro,
				'version'  => $version,
			);
		}
	}

	// On activation of this plugin: remember and deactivate all other editions
	register_activation_hook( $this_file, function () use ( $this_basename, $candidates ) {
		// Remember which plugin was just activated (short-lived marker is enough)
		set_transient( 'pcf_recently_activated', $this_basename, 120 );

		$to_deactivate = array();
		foreach ( $candidates as $c ) {
			if ( $c['basename'] !== $this_basename ) {
				$to_deactivate[] = $c['basename'];
			}
		}

		if ( ! empty( $to_deactivate ) ) {
			// Detect network-wide activation intent
			$network_wide = false;
			if ( is_multisite() ) {
				// WordPress sets 'networkwide' only in network admin. Safe fallback: use current screen context.
				$network_wide = is_network_admin() && isset( $_GET['networkwide'] ) && $_GET['networkwide'] == 1;
			}
			deactivate_plugins( $to_deactivate, true, $network_wide );
		}
	} );

	// Runtime sanity check: ensure only one edition stays active
	add_action( 'plugins_loaded', function () use ( $this_basename, $candidates ) {
		// Read current active plugins at runtime (do not rely on values captured at include-time)
		$active_plugins = (array) get_option( 'active_plugins', array() );

		// Find currently active candidates
		$active = array_values( array_filter( $candidates, function ( $c ) use ( $active_plugins ) {
			return in_array( $c['basename'], $active_plugins, true );
		} ) );

		if ( count( $active ) <= 1 ) {
			return; // nothing to fix
		}

		// If a plugin was just activated, prefer keeping that one
		$recent  = get_transient( 'pcf_recently_activated' );
		$to_keep = null;
		if ( $recent ) {
			foreach ( $active as $c ) {
				if ( $c['basename'] === $recent ) {
					$to_keep = $recent;
					break;
				}
			}
			// Clear the transient once read
			delete_transient( 'pcf_recently_activated' );
		}

		// Otherwise: Pro first, then by highest version
		if ( $to_keep === null ) {
			usort( $active, function ( $a, $b ) {
				if ( $a['is_pro'] !== $b['is_pro'] ) {
					return $a['is_pro'] ? - 1 : 1; // Pro has priority
				}
				// Highest version first
				$cmp = version_compare( $b['version'], $a['version'] );

				return $cmp !== 0 ? $cmp : 0;
			} );
			$to_keep = $active[0]['basename'];
		}

		// Deactivate all others
		$to_deactivate = array();
		foreach ( $active as $c ) {
			if ( $c['basename'] !== $to_keep ) {
				$to_deactivate[] = $c['basename'];
			}
		}

		if ( ! empty( $to_deactivate ) ) {
			$network_wide = false;
			if ( is_multisite() ) {
				foreach ( $to_deactivate as $basename ) {
					if ( is_plugin_active_for_network( $basename ) ) {
						$network_wide = true;
						break;
					}
				}
			}
			deactivate_plugins( $to_deactivate, true, $network_wide );

			// If this very file was deactivated, stop executing it any further in this request
			if ( in_array( $this_basename, $to_deactivate, true ) ) {
				return;
			}
		}
	}, 0 );
} );