<?php

namespace FlexibleCouponsProVendor\WPDesk\License\Changelog;

use ArrayObject;
use FlexibleCouponsProVendor\WPDesk\License\Changelog\Parser\Line;
/**
 * Can parse changelog.
 */
class Parser
{
    private string $changelog;
    private array $changelog_parsed_data = [];
    private array $types = [];
    /**
     * Parser constructor.
     */
    public function __construct(string $changelog)
    {
        $this->changelog = $changelog;
    }
    /**
     * @return ArrayObject
     */
    public function get_parsed_changelog(): ArrayObject
    {
        return new ArrayObject($this->changelog_parsed_data);
    }
    /**
     * @return Parser $this
     */
    public function parse(): self
    {
        $this->changelog_parsed_data = [];
        $version = $type = null;
        // phpcs:ignore
        foreach ($this->get_lines() as $line) {
            if (!$this->types && $types = $line->get_types()) {
                // phpcs:ignore
                $this->types = $types;
                continue;
            }
            if ($release = $line->get_release_details()) {
                // phpcs:ignore
                $version = $release['version'];
                $type = null;
                continue;
            }
            if ($type_details = $line->get_type_details()) {
                // phpcs:ignore
                $type = $type_details;
                continue;
            }
            if (!$version || !$type) {
                continue;
            }
            if (!isset($this->changelog_parsed_data[$version])) {
                $this->changelog_parsed_data[$version] = ['version' => $version, 'changes' => []];
            }
            $this->changelog_parsed_data[$version]['changes'][$type][] = $line->get_value();
        }
        return $this;
    }
    /**
     * @return array
     */
    public function get_types(): array
    {
        return $this->types;
    }
    /**
     * @return Line[]
     */
    private function get_lines(): array
    {
        $content = base64_decode($this->changelog);
        if (!$content) {
            return [];
        }
        return array_map(function ($line) {
            return new Line($line);
        }, array_filter(preg_split("/\r\n|\n|\r/", wp_kses_post($content))));
    }
}
