<?php

namespace FlexibleCouponsProVendor\WPDesk\License\Changelog;

use Iterator;
/**
 * Can format changelog.
 */
class Formatter
{
    private Iterator $changes;
    private array $types;
    public function __construct(Iterator $changes)
    {
        $this->changes = $changes;
    }
    public function set_changelog_types(array $types): void
    {
        $this->types = $types;
    }
    public function prepare_formatted_html(): string
    {
        $output = '';
        foreach ($this->get_changes_data() as $name => $changes) {
            if (empty($changes)) {
                continue;
            }
            $output .= sprintf("\n\n<strong>%s</strong>: <br/>* %s", $name, implode(' <br />* ', array_map('esc_html', $changes)));
        }
        return wp_kses_post(nl2br(trim($output)));
    }
    private function get_changes_data(): array
    {
        $changes = [];
        foreach ($this->types as $type) {
            $changes[$type] = [];
        }
        foreach ($this->changes as $item) {
            foreach ($item['changes'] as $type => $change) {
                if (!isset($changes[$type])) {
                    $changes[$type] = [];
                }
                $changes[$type] = array_merge($changes[$type], $change);
            }
        }
        return array_filter($changes);
    }
}
