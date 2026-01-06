<?php
/**
 *
 * Null plugin.
 *
 * @package WPDesk\FC\FreeDisabler
 */

namespace WPDesk\FC\FreeDisabler;

use FlexibleCouponsVendor\WPDesk\PluginBuilder\Plugin\SlimPlugin;

/**
 * Can be injected into CouponsVendor plugin builder to disable plugin.
 *
 * @package WPDesk\FreeDisabler
 */
final class NullPlugin extends SlimPlugin {
	/**
	 * Some null text-domain.
	 *
	 * @return string
	 */
	public function get_text_domain() {
		return 'null-text-domain';
	}

	/**
	 * Disabled init.
	 */
	public function init() {
		// do nothing.
	}
}
