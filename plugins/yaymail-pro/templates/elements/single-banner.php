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

$showing_items = isset( $data['showing_items'] ) ? $data['showing_items'] : [];

$wrapper_styles = TemplateHelpers::get_style(
    [
        'background-color' => isset( $data['background_color'] ) ? $data['background_color'] : '#ffffff',
        'text-align'       => isset( $data['content_align'] ) ? $data['content_align'] : 'right',
        'padding'          => TemplateHelpers::get_spacing_value( isset( $data['padding'] ) ? $data['padding'] : [] ),
    ]
);
if ( isset( $data['background_image'], $data['background_image']['url'] ) && in_array( 'background_image', $showing_items, true ) ) {
    $background_image = $data['background_image'];

    // Calculate background-position
    $background_position = str_replace( '_', ' ', $background_image['position'] );
    if ( ! isset( $background_image['position'] ) || 'default' === $background_image['position'] ) {
        $background_position = 'unset';
    } elseif ( 'custom' === $background_image['position'] ) {
        $background_position = "{$background_image['x_position']}% {$background_image['y_position']}%";
    }

    // Calculate background-repeat
    if ( ! isset( $background_image['repeat'] ) || 'default' === $background_image['repeat'] ) {
        $background_repeat = 'unset';
    } else {
        $background_repeat = $background_image['repeat'];
    }

    // Calculate background-size
    $background_size = $background_image['size'];

    if ( 'default' === $background_size ) {
        $background_size = 'unset';
    } elseif ( 'custom' === $background_size ) {
        $background_size = "{$background_image['custom_size']}%";
    }

    $wrapper_styles .= TemplateHelpers::get_style(
        [
            'background-image'    => "url({$background_image['url']})",
            'background-position' => $background_position,
            'background-repeat'   => $background_repeat,
            'background-size'     => $background_size,
        ]
    );
}//end if

$content_styles = TemplateHelpers::get_style(
    [
        'width'       => TemplateHelpers::get_dimension_value( isset( $data['content_width'] ) ? $data['content_width'] : '60', '%' ),
        'display'     => 'inline-block',
        'font-family' => isset( $data['font_family'] ) ? TemplateHelpers::get_font_family_value( $data['font_family'] ) : YAYMAIL_DEFAULT_FAMILY,
    ]
);

$button_container_styles = TemplateHelpers::get_style(
    [
        'width'      => '100%',
        'text-align' => isset( $data['button_align'] ) ? $data['button_align'] : 'right',
    ]
);

$button_styles = TemplateHelpers::get_style(
    [
        'display'          => 'inline-block',
        'font-weight'      => 'normal',
        'line-height'      => '21px',
        'text-align'       => 'center',
        'text-decoration'  => 'none',
        'margin-top'       => '5px',
        'font-size'        => '13px',
        'padding'          => '10px 15px',
        'background-color' => isset( $data['button_background_color'] ) ? $data['button_background_color'] : 'initial',
        'color'            => isset( $data['button_text_color'] ) ? $data['button_text_color'] : 'initial',
        'font-family'      => isset( $data['font_family'] ) ? TemplateHelpers::get_font_family_value( $data['font_family'] ) : YAYMAIL_DEFAULT_FAMILY,
    ]
);

ob_start();
?>

    <table style="width: 100%;">
        <tbody>
            <tr>
                <td>
                    <!-- Text Content -->
                    <div style="<?php echo esc_attr( $content_styles ); ?>">
                        <span><?php echo wp_kses_post( isset( $data['rich_text'] ) ? do_shortcode( $data['rich_text'] ) : '' ); ?></span>
                    </div>
                    <!-- End Text Content -->

                    <!-- Button Order Now -->
                    <?php
                    if ( in_array( 'button', $showing_items, true ) ) :
                        ?>
                    <div style="<?php echo esc_attr( $button_container_styles ); ?>"> 
                        <a style="<?php echo esc_attr( $button_styles ); ?>" href="<?php echo esc_url( isset( $data['button_url'] ) ? $data['button_url'] : '#' ); ?>" >
                            <?php echo esc_html( isset( $data['button_text'] ) ? $data['button_text'] : __( 'ORDER NOW', 'yaymail' ) ); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                    <!-- End Button Order Now -->
                </td>
            </tr>
        </tbody>
    </table>

<?php
$element_content = ob_get_clean();

TemplateHelpers::wrap_element_content( $element_content, $element, $wrapper_styles );
