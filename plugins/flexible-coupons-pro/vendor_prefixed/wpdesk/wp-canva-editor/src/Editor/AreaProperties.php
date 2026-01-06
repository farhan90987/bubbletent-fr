<?php

/**
 * Editor. Area properties.
 *
 * @package WPDesk\Library\WPCanvaEditor
 */
namespace FlexibleCouponsProVendor\WPDesk\Library\WPCanvaEditor;

use FlexibleCouponsProVendor\WPDesk\Library\WPCanvaEditor\Abstracts\EditorProperties;
use FlexibleCouponsProVendor\WPDesk\Library\WPCanvaEditor\Exceptions\EditorException;
use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\EditorAreaProperties;
/**
 * Define and prepare editor area properties.
 *
 * @package WPDesk\Library\WPCanvaEditor
 */
class AreaProperties implements EditorAreaProperties
{
    /**
     * @var string[]
     */
    private $default_formats = ['A4', 'A5', 'A6'];
    /**
     * @var string[]
     */
    private $default_orientations = ['L', 'P'];
    /**
     * @var EditorProperties
     */
    private $properties;
    /**
     * $post_meta structure: [ 'editor' => [ 'width' => '', 'height' => '', 'format' => '' ..., 'areaObjects' => '' ]
     *
     * @param array $post_meta An array from the editor post, where area properties and objects are stored.
     */
    public function __construct($post_meta)
    {
        $this->properties = new EditorProperties();
        if (isset($post_meta['editor'])) {
            $area = wp_parse_args($post_meta['editor'], (array) $this->properties);
            $this->set_width($area['width']);
            $this->set_height($area['height']);
            $this->set_format($area['format']);
            $this->set_orientation($area['orientation']);
            $this->set_background_color($area['backgroundColor']);
            $this->set_orientation_dimensions();
        }
    }
    /**
     * Set real orientation dimensions.
     */
    private function set_orientation_dimensions()
    {
        if ('L' === $this->get_orientation()) {
            $area_dimension = $this->properties;
            $this->properties->width = $area_dimension->height;
            $this->properties->height = $area_dimension->width;
        }
    }
    /**
     * @param int $width
     */
    public function set_width($width)
    {
        $this->properties->width = (int) $width;
    }
    /**
     * @return int
     */
    public function get_width()
    {
        return $this->properties->width;
    }
    /**
     * @param int $height
     */
    public function set_height($height)
    {
        $this->properties->height = (int) $height;
    }
    /**
     * @return int
     */
    public function get_height()
    {
        return $this->properties->height;
    }
    /**
     * @param string $format
     */
    public function set_format($format)
    {
        if (!in_array($format, $this->default_formats, \true)) {
            throw new EditorException('Unknown format');
        }
        $this->properties->format = $format;
    }
    /**
     * @return string
     */
    public function get_format()
    {
        return $this->properties->format;
    }
    /**
     * @param string $orientation
     */
    public function set_orientation($orientation)
    {
        if (!in_array($orientation, $this->default_orientations, \true)) {
            throw new EditorException('Unknown format');
        }
        $this->properties->orientation = $orientation;
    }
    /**
     * @return string
     */
    public function get_orientation()
    {
        return $this->properties->orientation;
    }
    /**
     * @param string $background_color
     */
    public function set_background_color($background_color)
    {
        if (is_array($background_color) && isset($background_color['r'])) {
            $this->properties->background_color = 'rgba(' . implode(', ', $background_color) . ')';
        } else {
            $this->properties->background_color = 'rgba(255,255,255,1)';
        }
    }
    /**
     * @return string
     */
    public function get_background_color()
    {
        return $this->properties->background_color;
    }
}
