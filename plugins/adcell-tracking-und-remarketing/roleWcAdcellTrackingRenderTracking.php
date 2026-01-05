<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 29.04.19
 * Time: 13:17
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$roleWcAdcellTrackingScriptTracking = '';

add_action('init', 'roleWcAdcellTrackingAllPages');
add_action('woocommerce_thankyou_order_id', 'roleWcAdcellTrackingAfterOrder');

/**
 * Adds trackingcode to html within footerscripts
 *
 * Diese Funktion für den allgemeinen Trackingcode hinzu
 */
function roleWcAdcellTrackingAllPages() {
    global $roleWcAdcellTrackingScriptTracking;
    global $wp_version;

    wp_enqueue_script('roleWcAdcellTrackingAllPages', 'https://t.adcell.com/js/trad.js?s=wordpress&sv='. $wp_version . '&v=1.0.21&cv=' . time(), array(), '1.0.21');
    wp_add_inline_script('roleWcAdcellTrackingAllPages', 'Adcell.Tracking.track();');
    echo $roleWcAdcellTrackingScriptTracking;
}

/**
 * Generates script for ordertracking
 *
 * Diese Funktion fügt den Trackingcode für den Bestellabschluss hinzu
 */
function roleWcAdcellTrackingAfterOrder($orderID) {
    global $roleWcAdcellTrackingScriptTracking;
    global $wp_version;

    $wcOrder = wc_get_order($orderID);
    if (empty($wcOrder)) {
        return $orderID;
    }
    $total = $wcOrder->get_total() - $wcOrder->get_shipping_total() - $wcOrder->get_total_tax();

    $ngpixel = $wcOrder->get_meta('ngpixel-'.$wcOrder->get_order_number(),true);
    $wcOrder->save();
    if ( $total > 0 && ( empty( $ngpixel ) ) ) {
        $wcOrder->update_meta_data('ngpixel-'.$wcOrder->get_order_number(),1);
        $wcOrder->save();

        wp_scripts();

        wp_enqueue_script('roleWcAdcellTrackingAfterOrder', 'https://t.adcell.com/t/track.js?s=wordpress&sv='. $wp_version . '&v=1.0.21&pid='. esc_attr(get_option('roleWcAdcellProgramId')) . '&eventid='. esc_attr(get_option('roleWcAdcellEventId')) .'&referenz='. $wcOrder->get_order_number() .'&betrag='. $total . '&cv=' . time(), array(), '1.0.21');
        do_action('roleWcAdcellOrderFinished', $wcOrder);

        $roleWcAdcellTrackingScriptTracking = '<noscript><img border="0" width="1" height="1" src="https://t.adcell.com/t/track?s=wordpress&sv='. $wp_version . '&v=1.0.21&pid='. esc_attr(get_option('roleWcAdcellProgramId')) . '&eventid='. esc_attr(get_option('roleWcAdcellEventId')) .'&referenz='. $wcOrder->get_order_number() .'&betrag='. $total . '&cv=' . time() . '"></noscript>';
    }

    return $orderID;
}
