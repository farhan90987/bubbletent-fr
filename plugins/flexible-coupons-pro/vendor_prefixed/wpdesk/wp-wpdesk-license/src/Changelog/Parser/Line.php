<?php

namespace FlexibleCouponsProVendor\WPDesk\License\Changelog\Parser;

/**
 * Can parse single changelog line.
 */
class Line
{
    private string $line;
    public function __construct(string $line)
    {
        $this->line = $line;
    }
    /**
     * @return array{version: string, date: string}
     */
    public function get_release_details(): array
    {
        preg_match('/## \[(.*)\] - (.*)/', $this->line, $output_array);
        if (!isset($output_array[1], $output_array[2])) {
            return [];
        }
        return ['version' => $output_array[1], 'date' => $output_array[2]];
    }
    public function get_type_details(): string
    {
        preg_match('/### (.*)/', $this->line, $output_array);
        if (!isset($output_array[1])) {
            return '';
        }
        return $output_array[1];
    }
    /** @return string[] */
    public function get_types(): array
    {
        preg_match('/##### (.*)/', $this->line, $output_array);
        if (!isset($output_array[1])) {
            return [];
        }
        return (array) wp_parse_list($output_array[1]);
    }
    public function get_value(): string
    {
        return ltrim($this->line, '- ');
    }
}
