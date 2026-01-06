<?php

/**
 * Integration. Shortcode replaces.
 *
 * @package WPDesk\Library\WPCoupons
 */
namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration;

/**
 * Replace short codes defined in content.
 *
 * To use this class You should pass in the constructor array of shortcodes
 * where key is the name of shortcode and value is the value to replace.
 * Like: [ 'siteurl => 'http://mysite.com' ]
 *
 * @package WPDesk\Library\WPCoupons\Integration
 */
class ShortCodeReplacer
{
    /**
     * Shortcode values.
     *
     * @var array
     */
    private $shortcode_values;
    /**
     * Replacements.
     *
     * @var array
     */
    private $replacements = [];
    /**
     * @param array $shortcode_values .
     */
    public function __construct(array $shortcode_values)
    {
        $this->shortcode_values = $shortcode_values;
        $this->add_default_replacements();
    }
    /**
     * @return void
     */
    private function add_default_replacements()
    {
        foreach ($this->shortcode_values as $key => $value) {
            $this->add_replacement($key, $value);
        }
        $this->add_replacement('url', get_site_url());
    }
    /**
     * @param string           $shortcode   Short code name. Add without [].
     * @param string|float|int $replacement Value to return.
     */
    public function add_replacement(string $shortcode, $replacement)
    {
        $this->replacements['/\[' . sanitize_key($shortcode) . '\]/i'] = $replacement;
    }
    /**
     * @return array
     */
    private function get_replacements(): array
    {
        /**
         * Add your own shortcodes to replace.
         *
         * @param array $replacements     Exists replacements.
         * @param array $shortcode_values Shortcode values.
         */
        return (array) apply_filters('flexible_coupons_shortcode_replacements', $this->replacements, $this->shortcode_values);
    }
    /**
     * @param string $content
     *
     * @return string
     */
    public function replace_shortcodes(string $content = ''): string
    {
        if (!empty($this->get_replacements())) {
            return (string) preg_replace(array_keys($this->get_replacements()), array_values($this->get_replacements()), $content);
        }
        return $content;
    }
}
