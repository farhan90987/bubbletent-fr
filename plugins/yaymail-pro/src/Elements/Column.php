<?php
namespace YayMail\Elements;

use YayMail\Abstracts\BaseElement;
use YayMail\Utils\SingletonTrait;

/**
 * Column Elements
 */
class Column extends BaseElement {

    use SingletonTrait;

    protected static $type = 'column';

    public $available_email_ids = [ YAYMAIL_ALL_EMAILS ];

    public static function get_data( $width = 5, $attributes = [] ) {
        return [
            'id'                    => uniqid(),
            'type'                  => self::$type,
            'group'                 => 'hidden',
            // only appears inside column_layout
                        'available' => true,
            'children'              => isset( $attributes['children'] ) ? $attributes['children'] : [],

            'data'                  => [
                'width' => isset( $attributes['width'] ) ? $attributes['width'] : $width,
            ],
        ];
    }
}
