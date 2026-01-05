<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 29.04.19
 * Time: 13:23
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if(esc_attr(get_option('roleWcAdcellRetargeting'))) {
    add_action( 'init', 'roleWcAdcellTrackingRetargeting');
    add_action( 'woocommerce_after_shop_loop', 'roleWcAdcellTrackingCategoryRetargeting');
    add_action( 'woocommerce_after_single_product', 'roleWcAdcellTrackingProductRetargeting');
    add_action( 'woocommerce_after_cart', 'roleWcAdcellTrackingCartRetargeting');
    add_action('woocommerce_checkout_after_order_review', 'roleWcAdcellTrackingCartRetargeting');
    add_action('roleWcAdcellOrderFinished', 'roleWcAdcellTrackingOrderRetargeting');
}

/**
 * Adds retargeting script to html within footerscripts
 *
 * Diese Funktion fügt den RT-Code für die Startseite hinzu
 */
function roleWcAdcellTrackingRetargeting() {
    global $wp;
    global $wp_version;
    $home_url = home_url('/');
    $current_url =  (isset($_SERVER['HTTPS']) ? 'https' : 'http') .'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $current_url = str_replace('?'.$_SERVER['QUERY_STRING'], '', $current_url);
    if(is_front_page() || (($home_url === $current_url || $home_url === $current_url.'/') && !isset($_GET['page_id']))) {
        wp_enqueue_script('roleWcAdcellTrackingRetargetingScript', '//t.adcell.com/js/inlineretarget.js?s=wordpress&sv='. $wp_version . '&v=1.0.21' . '&cv=' . time() .'&method=track&pid='.esc_attr(get_option('roleWcAdcellProgramId')).'&type=startpage#asyncload', array(), '1.0.21');
        wp_script_add_data( 'roleWcAdcellTrackingRetargetingScript', 'async' , true );
    }
}

/**
 * Generates categoryretargeting script
 *
 * Diese Funktion fügt den RT-Code für die Kategorieansicht hinzu
 */
function roleWcAdcellTrackingCategoryRetargeting() {
    global $post;
    global $wp_version;
    $productIds = array();
    /**
     * Sammeln der ProductIds aus der Kategorie
     */
    woocommerce_product_loop_start(false);
    while(have_posts()) {
        the_post();
        $productIds[] = get_the_ID();
    }
    woocommerce_product_loop_end(false);
    wp_reset_query();

//    $terms = get_the_terms($post->ID, 'product_cat');

    /**
     * Ermitteln der Kategorie:
     * Da in WooCommerce die aktuell ausgewählte Kategorie nicht zur Verfügung steht,
     * werden hier alle Kategorien aller angezeigten Artikel ausgelesen und die am
     * häufigsten vorkommende Kategorie als die aktuelle Kategorie gewertet.
     */
    $categories = array();
    foreach($productIds as $productId) {
        $productTerms = get_the_terms($productId, 'product_cat');
        if($productTerms) {
            foreach($productTerms as $productTerm) {
                $productCategoryId = $productTerm->term_id;
                $productCategoryName = $productTerm->name;
                if (!array_key_exists($productCategoryId, $categories)) {
                    $categories[$productCategoryId] = array(
                        'name' => $productCategoryName,
                        'counter' => 1
                    );
                } else {
                    $categories[$productCategoryId]['counter']++;
                }
            }
        }
    }

    /**
     * Kategoriename suchen
     */
    $categoryCounter = 0;
    $categoryId = 0;
    $categoryName = '';
    foreach($categories as $id => $category) {
        if($category['counter'] > $categoryCounter) {
            $categoryId = $id;
            $categoryName = urlencode($category['name']);
            $categoryCounter = $category['counter'];
        }
    }

    /**
     * Zusammensetzen der ProduktIds
     */
    $productIdString = implode(';', $productIds);

    /**
     * Einfügen von RT-Code für Kategorie bzw. Suche
     */
    if(is_search()) {
        $roleWcAdcellTrackingScriptRetargeting = '//t.adcell.com/js/inlineretarget.js?s=wordpress&sv='. $wp_version . '&v=1.0.21' . '&cv=' . time() . '&method=search&pid='.esc_attr(get_option('roleWcAdcellProgramId')).'&search='. get_search_query(true) .'&productIds='. $productIdString .'&productSeparator=;'.'&faktor=2#asyncload';
    } else {
        $roleWcAdcellTrackingScriptRetargeting = '//t.adcell.com/js/inlineretarget.js?s=wordpress&sv='. $wp_version . '&v=1.0.21' . '&cv=' . time() . '&method=category&pid='.esc_attr(get_option('roleWcAdcellProgramId')).'&categoryName='.$categoryName.'&categoryId='. $categoryId .'&productIds='. $productIdString . '&productSeparator=;'.'&faktor=2#asyncload';
    }
    wp_enqueue_script('roleWcAdcellTrackingRetargetingScriptCategory', $roleWcAdcellTrackingScriptRetargeting, array(), NULL);
    wp_script_add_data( 'roleWcAdcellTrackingRetargetingScriptCategory', 'async' , true );
}

/**
 * Generates productpagereatrgeting script
 *
 * Diese Funktion fügt den RT-Code für die Produkt Einzelansicht hinzu
 */
function roleWcAdcellTrackingProductRetargeting() {
    global $product;
    global $wp_version;

    $relatedProducts = wc_get_related_products($product->get_id());
    $relatedProductsString = implode(';', $relatedProducts);
    $categoryId = $product->get_category_ids()[0];

    wp_enqueue_script('roleWcAdcellTrackingRetargetingScriptProduct', '//t.adcell.com/js/inlineretarget.js?s=wordpress&sv='. $wp_version . '&v=1.0.21' . '&cv=' . time() . '&method=product&pid='.esc_attr(get_option('roleWcAdcellProgramId')).'&productId='. $product->get_id() .'&productName='. $product->get_name() .'&categoryId='. $categoryId .'&productIds='. $relatedProductsString .'&productSeparator=;'.'&faktor=2#asyncload', array(), '1.0.21');
    wp_script_add_data( 'roleWcAdcellTrackingRetargetingScriptProduct', 'async' , true );
}

/**
 * Generates cartretargeting script
 *
 * Diese Funktion fpgt den RT-Code für die Warenkorbansicht hinzu
 */
function roleWcAdcellTrackingCartRetargeting() {
    global $woocommerce;
    global $wp_version;

    $items = $woocommerce->cart->get_cart();

    $cartProducts = array();
    $cartQuantities = array();
    $cartCount = 0;
    $cartAmount = 0.0;
    /**
     * Auslesen und berechnen der benötigten Werte
     */
    foreach($items as $item => $values) {
        $cartProducts[] = $values['data']->get_id();
        $cartQuantities[] = $values['quantity'];
        $cartCount += $values['quantity'];
        $cartAmount += $values['line_total'];
    }

    $cartProductsString = implode(';', $cartProducts);
    $cartQuantitiesString = implode(';', $cartQuantities);

    /**
     * RT-Code einfügen
     */
    wp_enqueue_script('roleWcAdcellTrackingRetargetingScriptCart', '//t.adcell.com/js/inlineretarget.js?s=wordpress&sv='. $wp_version . '&v=1.0.21' . '&cv=' . time() . '&method=basket&pid='.esc_attr(get_option('roleWcAdcellProgramId')).'&productIds='. $cartProductsString .'&productSeparator=;&quantities='. $cartQuantitiesString .'&basketProductCount='. $cartCount .'&basketTotal='. $cartAmount.'&faktor=2#asyncload', array(), '1.0.21');
    wp_script_add_data( 'roleWcAdcellTrackingRetargetingScriptCart', 'async' , true );
}

/**
 * Generates orderretargeting script
 *
 * Diese Funktion fügt den RT-Code für den Bestellabschluss hinzu
 */
function roleWcAdcellTrackingOrderRetargeting($wcOrder) {
    global $wp_version;

    /**
     * Berechnen und auslesen der benötigten Werte
     */
    $total = $wcOrder->get_total() - $wcOrder->get_shipping_total() - $wcOrder->get_total_tax();
    $items = $wcOrder->get_items();
    $orderProducts = array();
    $orderQuantities = array();
    $orderCount = 0;
    foreach($items as $item => $values) {
        $orderProducts[] = $values['product_id'];
        $orderQuantities[] = $values['quantity'];
        $orderCount += $values['quantity'];
    }

    $orderProductsString = implode(';', $orderProducts);
    $orderQuantitiesString = implode(';', $orderQuantities);

    /**
     * RT-Code einfügen
     */
    wp_enqueue_script('roleWcAdcellTrackingRetargetingScriptOrder', '//t.adcell.com/js/inlineretarget.js?s=wordpress&sv='. $wp_version . '&v=1.0.21' . '&cv=' . time() . '&method=checkout&pid='. esc_attr(get_option('roleWcAdcellProgramId')) . '&basketId='. $wcOrder->get_order_number() .'&basketTotal='. $total .'&basketProductCount='. $orderCount .'&productIds='. $orderProductsString .'&productSeparator=;&quantities='. $orderQuantitiesString.'&faktor=2#asyncload', array(), '1.0.21');
    wp_script_add_data( 'roleWcAdcellTrackingRetargetingScriptOrder', 'async' , true );
}


