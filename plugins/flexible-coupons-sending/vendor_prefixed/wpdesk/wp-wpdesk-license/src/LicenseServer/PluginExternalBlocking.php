<?php

namespace FCSVendor\WPDesk\License\LicenseServer;

/**
 * Idea is to have a class that will be responsible for checking if external requests are blocked.
 * Can show a notice if external requests are blocked.
 */
class PluginExternalBlocking
{
    private PluginVersionInfo $plugin_info;
    private string $server;
    public function __construct(PluginVersionInfo $plugin_info, string $server)
    {
        $this->plugin_info = $plugin_info;
        $this->server = $server;
    }
    /**
     * Check for external blocking constants
     */
    public function display_info_when_external_blocking(): void
    {
        // show notice if external requests are blocked through the WP_HTTP_BLOCK_EXTERNAL constant
        if (defined('FCSVendor\WP_HTTP_BLOCK_EXTERNAL') && \FCSVendor\WP_HTTP_BLOCK_EXTERNAL === \true) {
            // check if our API endpoint is in the allowed hosts
            $host = parse_url($this->server, \PHP_URL_HOST);
            if (!defined('FCSVendor\WP_ACCESSIBLE_HOSTS') || stristr(\FCSVendor\WP_ACCESSIBLE_HOSTS, $host) === \false) {
                ?>
				<div class="error">
					<p>
					<?php 
                printf(wp_kses_post(__('<b>Warning!</b> You\'re blocking external requests which means you won\'t be able to get %1$s updates. Please add %2$s to %3$s.', 'flexible-coupons-sending')), esc_html($this->plugin_info->get_plugin_name()), wp_kses_post('<strong>' . $host . '</strong>'), wp_kses_post('<code>WP_ACCESSIBLE_HOSTS</code>'));
                ?>
							</p>
				</div>
				<?php 
            }
        }
    }
    public function hooks(): void
    {
        add_action('admin_notices', [$this, 'display_info_when_external_blocking']);
    }
}
