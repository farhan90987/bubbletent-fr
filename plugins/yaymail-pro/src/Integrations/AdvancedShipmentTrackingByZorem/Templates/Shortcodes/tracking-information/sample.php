<?php
/**
 * Tracking Widget template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/fluid-tracking-info.php.
 */
use YayMail\Utils\TemplateHelpers;
use YayMail\Integrations\AdvancedShipmentTrackingByZorem\Utils\Helpers as AstHelpers;

if ( ( ! isset( $args['ast'] ) && empty( $args['ast'] ) ) ) {
    return;
}

$text_align = yaymail_get_text_align();

$text_link_color = isset( $args['text_link_color'] ) ? $args['text_link_color'] : YAYMAIL_COLOR_WC_DEFAULT;
$is_placeholder  = isset( $args['is_placeholder'] ) ? $args['is_placeholder'] : false;
$data            = isset( $args['element']['data'] ) ? $args['element']['data'] : [];

$tracking_info_settings = AstHelpers::get_tracking_info_settings();
$style                  = array_merge( $tracking_info_settings['style'], isset( $data['style'] ) ? $data['style'] : [] );
$button                 = array_merge( $tracking_info_settings['button'], isset( $data['button'] ) ? $data['button'] : [] );



$title_style = TemplateHelpers::get_style(
    [
        'text-align'    => $text_align,
        'color'         => isset( $data['title_color'] ) ? $data['title_color'] : 'inherit',
        'margin-bottom' => '10px',
        'font-size'     => '20px',
        'font-weight'   => 'bold',
        'font-family'   => TemplateHelpers::get_font_family_value( isset( $data['font_family'] ) ? $data['font_family'] : 'inherit' ),
    ]
);


// Widget style option
$display_shipped_header = $style['display_shipped_header'];
$border_color           = $style['table_border_color'];
$border_radius          = $style['table_border_radius'];
$background_color       = $style['table_background_color'];
$hide_provider_image    = $style['hide_provider_image'];
$tracker_type           = $style['tracker_type'];


if ( $hide_provider_image ) {
    $colspan = '2';
} else {
    $colspan = '3';
}

$fluid_provider_img_class = ( $hide_provider_image ) ? 'hide' : '';

// Button option
$button_text             = $button['text'];
$button_background_color = $button['background_color'];
$button_font_color       = $button['font_color'];
$button_radius           = $button['radius'];
$button_size             = $button['size'];
$button_font_size        = ( 'large' === $button_size ) ? 16 : 14;
$button_padding          = ( 'large' === $button_size ) ? '12px 25px' : '10px 15px';

?>

<table align="<?php echo esc_attr( $text_align ); ?>" class="fluid_table fluid_table_2cl">
    <tbody class="fluid_tbody_2cl">
        <?php
        if ( $display_shipped_header ) {
            ?>
            <tr class="<?php echo esc_html( $display_shipped_header ); ?>">
                <td style="padding-bottom:0 !important;" colspan="<?php echo esc_html( $colspan ); ?>">
                    <h2 class="shipped_label"><?php esc_html_e( 'Shipped', 'woo-advanced-shipment-tracking' ); ?></h2>
                </td>
            </tr>
            <tr class="<?php echo esc_html( $display_shipped_header ); ?>">
                <td style="padding-top:0 !important;" colspan="<?php echo esc_html( $colspan ); ?>">
                    <?php
                        echo '<span class="shipped_on">';
                        esc_html_e( 'Shipped on', 'woo-advanced-shipment-tracking' );
                        echo ': <b>';
                        echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( gmdate( 'm-d-y' ) ) ) );
                        echo '</b>';
                        echo '</span>';
                    ?>
                </td>
            </tr>
            <tr class="tracker_tr <?php echo esc_html( $display_shipped_header ); ?>">
                <td class="" style="padding-top:5px !important;" colspan="<?php echo esc_html( $colspan ); ?>">
                    <?php if ( function_exists( 'wc_advanced_shipment_tracking' ) ) : ?>
                        <img class="tracker_image" style="width:100%;" src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>assets/images/<?php echo esc_html( $tracker_type ); ?>.png"></img>
                    <?php endif; ?>

                    <?php if ( function_exists( 'ast_pro' ) ) : ?>
                        <img class="tracker_image" style="width:100%;" src="<?php echo esc_url( ast_pro()->plugin_dir_url() ); ?>assets/images/<?php echo esc_html( $tracker_type ); ?>.png"></img>
                    <?php endif; ?>
                </td>   
            </tr>
            <?php
        }//end if
        ?>
        <tr class="fluid_2cl_tr">
            <?php if ( ! $hide_provider_image ) { ?>
                <td class="fluid_provider_img" style="padding-right:0 !important;">
                    <img src="<?php echo esc_url( wc_placeholder_img_src() ); ?>"></img>
                </td>   
            <?php } ?>              
            <td class="fluid_2cl_td_provider">
                <span class="tracking_provider"><?php echo esc_html( 'YayMail' ); ?></span>
                <a class="tracking_number" href="#" target="_blank"><?php echo esc_html( 123 ); ?></a>   
            </td>
            <td class="fluid_2cl_td_button" style="text-align: right;">
                <a href="#" class="track-button" target="_blank"><?php echo esc_html( $button_text ); ?></a> 
            </td>
        </tr>
    </tbody>
</table>
<div class="clearfix"></div>

<style>
.clearfix{
    display: block;
    content: '';
    clear: both;
}
.fluid_container{
    width: 100%;
    display: block;
}
.fluid_table_2cl{
    width: 100%;    
    margin: 10px 0 !important;
    border: 1px solid <?php echo esc_html( $border_color ); ?> !important;
    border-radius: <?php echo esc_html( $border_radius ); ?>px !important;    
    background: <?php echo esc_html( $background_color ); ?> !important;   
    border-spacing: 0 !important;   
}
.tracker_tr td{ 
    border-bottom: 1px solid <?php echo esc_html( $border_color ); ?>;
}
.fluid_table_2cl .fluid_2cl_tr td.fluid_2cl_td_action{  
    text-align: right;
    vertical-align: middle !important;
}

.fluid_table td{
    padding: 15px !important;
}

.fluid_provider_img {    
    display: inline-block;
    vertical-align: middle;
}
.fluid_provider_img img{
    width: 40px;
    border-radius: 5px;
    margin-right: 10px !important;
}
.provider_name{
    display: inline-block;
    vertical-align: middle;
}
.tracking_provider{
    word-break: break-word;
    margin-right: 5px;  
    font-size: 14px;
    display: block;
}
.tracking_number{
    color: <?php echo esc_html( $text_link_color ); ?>;
    text-decoration: none;    
    font-size: 14px;
    line-height: 19px;
    display: block;
    margin-top: 4px;
}

.shipped_label{
    font-size: 24px !important;
    margin: 0 0 10px !important;    
    display: inline-block;
    color: #333;
    vertical-align: middle;
    font-weight:500;
    line-height: 100%;
}
span.shipped_on{
    margin-top: 5px;
    display: inline-block;
    font-size: 14px;
}

a.track-button {
    background: <?php echo esc_html( $button_background_color ); ?>;
    color: <?php echo esc_html( $button_font_color ); ?> !important;
    padding: <?php echo esc_html( $button_padding ); ?>;
    text-decoration: none;
    display: inline-block;
    border-radius: <?php echo esc_html( $button_radius ); ?>px;
    margin-top: 2px;
    font-size: <?php echo esc_html( $button_font_size ); ?>px !important;
    text-align: center;
    min-height: 10px;
    white-space: nowrap;
}
.track-button-div{
    float: right;
}

@media screen and (max-width: 720px) {
    .fluid_2cl_tr{
        display: block;
    }
    .fluid_2cl_td_provider{
        display: inline-block;
        padding-right: 0 !important;
    }
    .fluid_2cl_td_button{
        display: block;
    }
}   
@media screen and (max-width: 460px) {
    .track-button-div{
        float: none !important;
        margin-top: 15px !important;
    }
    .track-button{
        display: block !important;
    }
}

</style>
