<?php
/**
 * Template for Advanced Local Pickup shortcode.
 */

use YayMail\Utils\TemplateHelpers;

$element_data = isset( $args['element']['data'] ) ? $args['element']['data'] : [];

$pickup_address_title = isset( $element_data['pickup_address_title'] ) ? $element_data['pickup_address_title'] : '{{pickup_address_title}}';
$pickup_hours_title   = isset( $element_data['pickup_hours_title'] ) ? $element_data['pickup_hours_title'] : '{{pickup_hours_title}}';

$alp      = wc_local_pickup()->admin;
$settings = wc_local_pickup()->customizer->customize_setting_options_func( 'ready_pickup' );
// $padding          = $alp->get_option_value_from_array( 'pickup_instruction_customize_settings', 'padding', $settings['padding']['default'] );
// $background_color = $alp->get_option_value_from_array( 'pickup_instruction_customize_settings', 'background_color', $settings['background_color']['default'] );

$table_td_style = TemplateHelpers::get_style(
    [
        'font-size'        => '14px',
        'padding'          => '15px',
        'text-align'       => yaymail_get_text_align(),
        'font-family'      => TemplateHelpers::get_font_family_value( isset( $element_data['font_family'] ) ? $element_data['font_family'] : 'inherit' ),
        'color'            => isset( $element_data['text_color'] ) ? $element_data['text_color'] : 'inherit',
        'border-width'     => '1px',
        'border-style'     => 'solid',
        'border-color'     => isset( $element_data['border_color'] ) ? $element_data['border_color'] : 'inherit',
        'background-color' => '#f5f5f5',
    ]
);

$w_day = $args['w_day'] ? $args['w_day'] : null;

if ( ! empty( $w_day ) ) {
    $n              = 0;
    $new_array      = [];
    $previous_value = [];

    foreach ( $w_day as $day => $value ) {
        if ( isset( $value['checked'] ) && '1' === $value['checked'] ) {
            if ( $value !== $previous_value ) {
                ++$n;
            }
            $new_array[ $n ][ $day ] = $value;
            $previous_value          = $value;
        } else {
            ++$n;
            $new_array[ $n ][ $day ] = '';
            $previous_value          = '';
        }
    }
}

$hide_hours_header  = $alp->get_option_value_from_array( 'pickup_instruction_customize_settings', 'hide_hours_header', $settings['hide_hours_header']['default'] );
$hide_addres_header = $alp->get_option_value_from_array( 'pickup_instruction_customize_settings', 'hide_addres_header', $settings['hide_addres_header']['default'] );

$location      = $args['location'];
$store_state   = $args['store_state'];
$store_country = $args['store_country'];

?>

<table class="wclp_mail_address" width="100%" cellspacing="0" cellpadding="6" style="<?php echo esc_attr( $table_td_style . 'padding: 0' ); ?>" border="0">
    <thead>
        <tr>
            <?php if ( '1' !== $hide_addres_header ) { ?>
                <th class="td wclp_location_box <?php echo ! empty( $new_array ) ? esc_attr( 'wclp_location_box1' ) : ''; ?>" scope="col" style="<?php echo esc_attr( $table_td_style ); ?>; width: 50%;">
                    <div class="wclp_location_box_heading">
                        <?php echo esc_html( $pickup_address_title ); ?>
                    </div>
                </th>
            <?php } ?>
            <?php if ( ! empty( $w_day ) ) { ?>
                <?php if ( ! empty( $new_array ) ) { ?>
                    <?php if ( '1' !== $hide_hours_header ) { ?>
                        <?php
                        $result_empty = array_filter( array_map( 'array_filter', $new_array ) );
                        // echo count( $result_empty );
                        ?>
                        <th class="td wclp_location_box <?php echo ! empty( $new_array ) ? esc_attr( 'wclp_location_box2' ) : ''; ?>" scope="col" style="<?php echo 0 === count( $result_empty ) ? 'display:' . esc_attr( 'none' ) : ''; ?>; <?php echo esc_attr( $table_td_style ); ?>">
                            <div class="wclp_location_box_heading">
                                <?php echo esc_html( $pickup_hours_title ); ?>
                            </div>
                        </th>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
        <tr style="<?php echo esc_attr( $table_td_style ); ?>">
            <td class="td wclp_location_box" style="<?php echo esc_attr( $table_td_style ); ?>">
                <?php if ( class_exists( 'Advanced_local_pickup_PRO' ) ) { ?>
                    <?php do_action( 'wclp_location_address_display_html', $location, $store_state, $store_country ); ?>
                <?php } else { ?>
                    <div class="wclp_location_box_content">
                        <p class="wclp_pickup_adress_p" style="margin:0; font-size: inherit;">
                            <?php
                            if ( ! empty( $location->store_name ) ) {
                                echo esc_html( $location->store_name );
                                echo ', ';
                            }
                            ?>
                        </p>
                        <p class="wclp_pickup_adress_p"  style="margin:0; font-size: inherit;">
                            <?php
                            if ( ! empty( $location->store_address ) ) {
                                echo esc_html( $location->store_address );
                                if ( ! empty( $location->store_address_2 ) ) {
                                    echo ', ';
                                }
                            }
                            if ( ! empty( $location->store_address_2 ) ) {
                                echo esc_html( $location->store_address_2 );
                                echo ', ';
                            }
                            ?>
                        </p>
                        <p class="wclp_pickup_adress_p"  style="margin:0; font-size: inherit;">
                            <?php
                            if ( ! empty( $location->store_city ) ) {
                                echo esc_html( $location->store_city );
                                if ( '' !== $store_state ) {
                                    echo ', ';
                                }
                            }
                            if ( '' !== $store_state ) {
                                echo esc_html( WC()->countries->get_states( $store_country )[ $store_state ] );
                            }
                            if ( $store_country ) {
                                echo ', ';
                            }
                            if ( $store_country ) {
                                echo esc_html( WC()->countries->countries[ $store_country ] );
                                if ( ! empty( $location->store_postcode ) ) {
                                    echo ', ';
                                }
                            }
                            if ( ! empty( $location->store_postcode ) ) {
                                echo esc_html( $location->store_postcode );
                            }
                            ?>
                        </p>
                        
                        <?php if ( ! empty( $location->store_phone ) ) { ?>
                            <p class="wclp_pickup_adress_p"  style="margin:0; font-size: inherit;"><?php echo esc_html( $location->store_phone ); ?></p>
                        <?php } ?>
                        <?php if ( ! empty( $location->store_instruction ) ) { ?>
                            <p class="wclp_pickup_adress_p"  style="margin:0; font-size: inherit;"><?php echo esc_html( $location->store_instruction ); ?></p>
                        <?php } ?>
                    </div>
                <?php }//end if ?>
            </td>
            <?php if ( ! empty( $w_day ) ) { ?>
                <?php if ( ! empty( $new_array ) ) { ?>
                    <?php
                    $result_empty = array_filter( array_map( 'array_filter', $new_array ) );
                    // echo count( $result_empty );
                    ?>
                    <td class="td wclp_location_box <?php echo ! empty( $new_array ) ? esc_attr( 'wclp_location_box2' ) : ''; ?>" style="<?php echo 0 === count( $result_empty ) ? 'display:' . esc_attr( 'none' ) : ''; ?>; <?php echo esc_attr( $table_td_style ); ?>; align-content: baseline">
                        <div class="wclp_location_box_content">
                            <?php
                            foreach ( $new_array as $key => $data ) {
                                $current_item = array_values( $data )[0] ?? null;
                                if ( empty( $current_item ) ) {
                                    continue;
                                }
                                $store_hours = $current_item['wclp_store_hour'];
                                $store_hours_end = $current_item['wclp_store_hour_end'];
                                if ( 1 === count( $data ) ) {
                                    if ( isset( $store_hours ) && '' != $store_hours && isset( $store_hours_end ) && '' != $store_hours_end ) {
                                        ?>
                                            <p class="wclp_work_hours_p"  style="margin:0; font-size: inherit;">
                                                <?php
                                                echo esc_html( ucfirst( key( $data ) ) ) . ' <span>: ' . esc_html( $store_hours ) . ' - ' . esc_html( $store_hours_end );
                                                do_action( 'wclp_get_more_work_hours_contents', $data );
                                                echo '</span>';
                                                ?>
                                            </p>                                
                                        <?php
                                    }
                                }
                                ?>
                                                            
                                <?php
                                if ( 2 === count( $data ) ) {
                                    if ( isset( $store_hours ) && '' != $store_hours && isset( $store_hours_end ) && '' != $store_hours_end ) {
                                        $array_key_first = array_keys( $data )[0];
                                        $array_key_last = array_keys( $data )[ count( array_keys( $data ) ) - 1 ];
                                        ?>
                                        <p class="wclp_work_hours_p"  style="margin:0; font-size: inherit;">
                                            <?php
                                            echo esc_html( ucfirst( $array_key_first ) ) . esc_html( ' - ' ) . esc_html( ucfirst( $array_key_last ) ) . ' <span>: ' . esc_html( $store_hours ) . ' - ' . esc_html( $store_hours_end );
                                            do_action( 'wclp_get_more_work_hours_contents', $data );
                                            echo '</span>';
                                            ?>
                                        </p>
                                        <?php
                                    }
                                }
                                ?>
                                                                        
                                <?php
                                if ( count( $data ) > 2 ) {
                                    if ( isset( $store_hours ) && '' !== $store_hours && isset( $store_hours_end ) && '' !== $store_hours_end ) {
                                        $array_key_first = array_keys( $data )[0];
                                        $array_key_last = array_keys( $data )[ count( array_keys( $data ) ) - 1 ];
                                        ?>
                                            <p class="wclp_work_hours_p"  style="margin:0; font-size: inherit;">
                                                <?php
                                                $first = ucfirst( $array_key_first );
                                                $last  = ucfirst( $array_key_last );
                                                $start = $store_hours;
                                                $end   = $store_hours_end;
                                                echo ( esc_html( $first ) . esc_html__( ' To ', 'advanced-local-pickup-for-woocommerce' ) . esc_html( $last ) . '<span> : ' . esc_html( $start ) . ' - ' . esc_html( $end ) );
                                                do_action( 'wclp_get_more_work_hours_contents', $data );
                                                echo '</span>';
                                                ?>
                                            </p>
                                            <?php
                                    }//end if
                                }//end if
                            }//end foreach

                            if ( class_exists( 'Advanced_local_pickup_PRO' ) ) {
                                if ( ! empty( $location->store_holiday_message ) ) {
                                    ?>
                                    <p class="wclp_pickup_adress_p"><?php echo esc_html( $location->store_holiday_message ); ?></p>
                                    <?php
                                }
                            }
                            ?>
                        </div>   
                    </td>
                    <?php
                }//end if
                ?>
                <?php
            }//end if
            ?>
        </tr>
    </tbody>
</table>