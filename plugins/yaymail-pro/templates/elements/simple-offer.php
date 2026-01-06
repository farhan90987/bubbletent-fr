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
        'background-color' => isset( $data['background_color'] ) ? $data['background_color'] : '#ffffff',
        'padding'          => TemplateHelpers::get_spacing_value( isset( $data['padding'] ) ? $data['padding'] : [] ),
    ]
);

$showing_items  = isset( $data['showing_items'] ) ? $data['showing_items'] : [];
$border_width   = TemplateHelpers::get_dimension_value( isset( $data['border_width'] ) ? $data['border_width'] : '3' );
$border_style   = isset( $data['border_style'] ) ? $data['border_style'] : 'solid';
$border_color   = isset( $data['border_style'] ) ? $data['border_color'] : '#000000';
$content_styles = TemplateHelpers::get_style(
    [
        'border'      => in_array( 'border', $showing_items, true ) ? "{$border_width} {$border_style} {$border_color}" : 'none',
        'width'       => '100%',
        'padding'     => '10px 15px',
        'color'       => isset( $data['text_color'] ) ? $data['text_color'] : YAYMAIL_COLOR_TEXT_DEFAULT,
        'font-family' => isset( $data['font_family'] ) ? TemplateHelpers::get_font_family_value( $data['font_family'] ) : YAYMAIL_DEFAULT_FAMILY,
    ]
);

$button_styles = TemplateHelpers::get_style(
    [
        'background-color' => isset( $data['button_background_color'] ) ? $data['button_background_color'] : '#ec4770',

        'color'            => isset( $data['button_text_color'] ) ? $data['button_text_color'] : '#ffffff',

        'display'          => 'block',
        'font-family'      => isset( $data['font_family'] ) ? TemplateHelpers::get_font_family_value( $data['font_family'] ) : YAYMAIL_DEFAULT_FAMILY,

        'font-size'        => '13px',
        'font-weight'      => 'normal',
        'margin'           => '5px 21px 0 21px',
        'padding'          => '10px 15px',
        'text-align'       => 'center',
        'text-decoration'  => 'none',
    ]
);
ob_start();
?>
    <table style="<?php echo esc_attr( $content_styles ); ?>">
        <tbody>
            <tr>

                <!-- Left Column -->
                <td style="width: 65%;">
                    <div>
                        <?php echo wp_kses_post( isset( $data['rich_text'] ) ? do_shortcode( $data['rich_text'] ) : '' ); ?>
                    </div>
                </td>
                <!-- End Left Column -->

                <!-- Right Column -->
                <td style="width: 35%;">

                    <!-- Button Order Now -->
                    <?php if ( in_array( 'button', $showing_items, true ) ) : ?>
                    <a style="<?php echo esc_attr( $button_styles ); ?>" href="<?php esc_url( isset( $data['button_url'] ) ? $data['button_url'] : '#' ); ?>" >
                        <?php echo esc_html( isset( $data['button_text'] ) ? $data['button_text'] : __( 'ORDER NOW', 'yaymail' ) ); ?>
                    </a>
                    <?php endif; ?>
                    <!-- End Button Order Now -->
               
                </td>
                <!-- End Right Column -->
            </tr>
        </tbody>
    </table>
<?php
$element_content = ob_get_clean();

TemplateHelpers::wrap_element_content( $element_content, $element, $wrapper_style );

