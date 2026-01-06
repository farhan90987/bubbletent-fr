<?php
defined( 'ABSPATH' ) || exit;
use YayMail\Utils\TemplateHelpers;

if ( empty( $args['element'] ) ) {
    return;
}

$element = $args['element'];
$data    = $element['data'];

$data_column_1 = [
    'align'   => 'center',
    'width'   => '100',
    'url'     => '#',
    'image'   => YAYMAIL_PLUGIN_URL . 'assets/images/woocommerce-logo.png',
    'padding' => [
        'top'    => '10',
        'right'  => '10',
        'bottom' => '10',
        'left'   => '50',
    ],
];

if ( is_array( $data['image_list']['column_1'] ) && ! empty( $data['image_list']['column_1'] ) ) {
    $_data_column_1 = $data['image_list']['column_1'];
    foreach ( $_data_column_1 as $key => $value ) {
        $data_column_1[ $key ] = $value['value'];
    }
}

$data_column_2 = [
    'align'   => 'center',
    'width'   => '100',
    'url'     => '#',
    'image'   => YAYMAIL_PLUGIN_URL . 'assets/images/woocommerce-logo.png',
    'padding' => [
        'top'    => '10',
        'right'  => '30',
        'bottom' => '10',
        'left'   => '30',
    ],
];

if ( is_array( $data['image_list']['column_2'] ) && ! empty( $data['image_list']['column_2'] ) ) {
    $_data_column_2 = $data['image_list']['column_2'];
    foreach ( $_data_column_2 as $key => $value ) {
        $data_column_2[ $key ] = $value['value'];
    }
}

$data_column_3 = [
    'align'   => 'center',
    'width'   => '100',
    'url'     => '#',
    'image'   => YAYMAIL_PLUGIN_URL . 'assets/images/woocommerce-logo.png',
    'padding' => [
        'top'    => '10',
        'right'  => '50',
        'bottom' => '10',
        'left'   => '10',
    ],
];

if ( is_array( $data['image_list']['column_3'] ) && ! empty( $data['image_list']['column_3'] ) ) {
    $_data_column_3 = $data['image_list']['column_3'];
    foreach ( $_data_column_3 as $key => $value ) {
        $data_column_3[ $key ] = $value['value'];
    }
}

$wrapper_style = TemplateHelpers::get_style(
    [
        'word-break'       => 'break-word',
        'background-color' => $data['background_color'],
    ]
);

$column_1_style = TemplateHelpers::get_style(
    [
        'padding'    => TemplateHelpers::get_spacing_value( isset( $data_column_1['padding'] ) ? $data_column_1['padding'] : [] ),
        'text_align' => $data['image_list']['column_1']['align'] && 'center',
    ]
);

$column_2_style = TemplateHelpers::get_style(
    [
        'padding'    => TemplateHelpers::get_spacing_value( isset( $data_column_2['padding'] ) ? $data_column_2['padding'] : [] ),
        'text_align' => $data['image_list']['column_2']['align'] && 'center',
    ]
);

$column_3_style = TemplateHelpers::get_style(
    [
        'padding'    => TemplateHelpers::get_spacing_value( isset( $data_column_3['padding'] ) ? $data_column_3['padding'] : [] ),
        'text_align' => $data['image_list']['column_3']['align'] && 'center',
    ]
);

$column_style = TemplateHelpers::get_style(
    [
        'width' => TemplateHelpers::get_dimension_value( 100 / $data['number_column'] ),
    ]
);

ob_start();
?>

<table style="width: 100%;">
        <tbody>
            <tr>
                <td style="<?php echo esc_attr( $column_style ); ?>">
                    <div style="<?php echo esc_attr( $column_1_style ); ?>">
                        <a href="<?php echo esc_html( $data_column_1['url'] ); ?>" target="_blank" rel="noreferrer">
                            <img alt="<?php echo esc_attr( $data_column_1['alt'] ?? '' ); ?>" src="<?php echo esc_html( $data_column_1['image'] ); ?>" style="width: <?php echo esc_attr( TemplateHelpers::get_dimension_value( $data_column_1['width'] ) ); ?>"/>
                        </a>
                    </div>
                </td>
                <?php if ( 2 <= (int) $data['number_column'] ) : ?>
                <td style="<?php echo esc_attr( $column_style ); ?>">
                    <div style="<?php echo esc_attr( $column_2_style ); ?>">
                        <a href="<?php echo esc_html( $data_column_2['url'] ); ?>" target="_blank" rel="noreferrer">
                            <img alt="<?php echo esc_attr( $data_column_2['alt'] ?? '' ); ?>" src="<?php echo esc_html( $data_column_2['image'] ); ?>" style="width: <?php echo esc_attr( TemplateHelpers::get_dimension_value( $data_column_2['width'] ) ); ?>"/>
                        </a>
                    </div>
                </td>
                <?php endif; ?>
                <?php if ( 3 <= (int) $data['number_column'] ) : ?>
                <td style="<?php echo esc_attr( $column_style ); ?>">
                    <div style="<?php echo esc_attr( $column_3_style ); ?>">
                        <a href="<?php echo esc_html( $data_column_3['url'] ); ?>" target="_blank" rel="noreferrer">
                            <img alt="<?php echo esc_attr( $data_column_3['alt'] ?? '' ); ?>" src="<?php echo esc_html( $data_column_3['image'] ); ?>" style="width: <?php echo esc_attr( TemplateHelpers::get_dimension_value( $data_column_3['width'] ) ); ?>"/>
                        </a>
                    </div>
                </td>
                <?php endif; ?>
            </tr>
        </tbody>
</table>
<?php
$element_content = ob_get_clean();

TemplateHelpers::wrap_element_content( $element_content, $element, $wrapper_style );
