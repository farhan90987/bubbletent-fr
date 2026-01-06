<?php
defined( 'ABSPATH' ) || exit;

use YayMail\Utils\TemplateHelpers;

/**
 * $args includes
 * $element
 * $render_data
 * $is_nested
 */
if ( empty( $args['element'] ) ) {
    return;
}

$element = $args['element'];
$data    = $element['data'];

$wrapper_style = TemplateHelpers::get_style(
    [
        'word-break'       => 'break-word',
        'text-align'       => 'center',
        'background-color' => $data['background_color'],
        'padding'          => TemplateHelpers::get_spacing_value( isset( $data['padding'] ) ? $data['padding'] : [] ),
    ]
);

$thumbnail_style = TemplateHelpers::get_style(
    [
        'background-image'    => ! empty( $data['src'] ) ? "url({$data['src']})" : '',
        'background-size'     => 'cover',
        'background-position' => 'center',
        'display'             => 'table-cell',
        'vertical-align'      => 'middle',
        'width'               => "{$data['width']}px",
        'height'              => "{$data['height']}px",
    ]
);

$btn_play_style = 'width: 56px; height: 56px;';
ob_start();
?>

    <a class="yaymail-customizer-element-video__anchor" href="<?php echo esc_url( $data['url'] ); ?>" target="_blank" rel="noreferrer">
        <div class="yaymail-customizer-element-video__thumbnail" style="<?php echo esc_attr( $thumbnail_style ); ?>">
            <img class="yaymail-customizer-element-video__btn-play" src="<?php echo esc_url( YAYMAIL_PLUGIN_URL . '/assets/images/play.png' ); ?>" style="<?php echo esc_attr( $btn_play_style ); ?>" alt="yaymail-btn-play" />
        </div>
    </a>
<?php
$element_content = ob_get_clean();
TemplateHelpers::wrap_element_content( $element_content, $element, $wrapper_style );
