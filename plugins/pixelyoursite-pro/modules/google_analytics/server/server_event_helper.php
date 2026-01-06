<?php
namespace PixelYourSite;
use PYS_PRO_GLOBAL\Br33f\Ga4\MeasurementProtocol\Dto\Event\RefundEvent;
use PYS_PRO_GLOBAL\Br33f\Ga4\MeasurementProtocol\Dto\Event\ViewItemEvent;
use PYS_PRO_GLOBAL\Br33f\Ga4\MeasurementProtocol\Dto\Event\PurchaseEvent;
use PYS_PRO_GLOBAL\Br33f\Ga4\MeasurementProtocol\Dto\Parameter\ItemParameter;


if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


class GaServerEventHelper {
    static $uaMap = [
        'cn'    => 'traffic_source',
        'ec'    => 'event_category',
        'tt'    => 'tax',
        'tr'    => 'value',
        'ti'    => 'transaction_id',
        'cu'    => 'currency',
        'dr'    => 'traffic_source'
    ];
    /**
     * @param SingleEvent $singleEvent
     * @return array|null
     */
    static public function mapSingleEventToServerData($singleEvent) {
        switch ($singleEvent->payload['name']) {
            case 'purchase': {
                return self::mapPurchaseToServerData($singleEvent);
            }
            case 'refund': {
                return self::mapRefundToServerData($singleEvent);
            }
        }

        return null;
    }

    /**
     * @param SingleEvent $singleEvent
     * @return array|null
     */
    static public function mapSingleEventToServerDataGA4($singleEvent) {

        switch ($singleEvent->payload['name']) {
            case 'purchase': {
                return self::mapPurchaseToServerDataGA4($singleEvent);
            }
            case 'refund': {
                return self::mapRefundToServerDataGA4($singleEvent);
            }
        }

        return null;
    }

    /**
     * @param SingleEvent $singleEvent
     * @return array
     */
    static private function mapPurchaseToServerData($singleEvent) {
        $data = $singleEvent->getData();
        $params = $data['params'];

        $serverParams = [
            't'     => 'event',
            'pa'    => 'purchase',
            'ea'    => 'purchase',
            'el'    => "Server Purchase",
            'cid'   =>  EventIdGenerator::guidv4(),

            'ti'  => $params['transaction_id'],   // transaction ID, required
            'tr'  => $params['value'],          // revenue
            'tt'  => $params['tax'],      // tax
            'cu'  => $params['currency'],              // order currency
        ];
        if(isset( $params['coupon'])) {
            $serverParams['tcc'] = $params['coupon'];  // coupon code
        }

        if(isset($params['shipping'])) {
            $serverParams['ts'] = $params['shipping'];
        }

        foreach (self::$uaMap as $key => $val) {
            if(isset($params[$val])) {
                $serverParams[$key] = $params[$val];
            }
        }

        for($i = 1;$i <= count($params['items']);$i++) {
            $item = $params['items'][$i-1];
            $serverParams["pr{$i}id"] = $item['item_id'] ?? '';
            $serverParams["pr{$i}nm"] = $item['item_name'] ?? '';
            $serverParams["pr{$i}ca"] = $item['item_category'] ?? '';
            $serverParams["pr{$i}pr"] = $item['price'] ?? '';
            $serverParams["pr{$i}qt"] = $item['quantity'] ?? '';
        }

        return $serverParams;
    }
    static private function mapRefundToServerData($singleEvent) {
        $data = $singleEvent->getData();
        $params = $data['params'];

        $serverParams = [
            't'     => 'event',
            'ec'    => 'Ecommerce',
            'ea'    => 'Refund',
            'el'    => "Server Refund",
            'cid'   =>  EventIdGenerator::guidv4(),

            'ti'  => $params['transaction_id'],   // transaction ID, required
            'tr'  => - $params['value'],          // revenue
            'cu'  => $params['currency'],              // order currency
        ];

        return $serverParams;
    }

    /**
     * @param SingleEvent $singleEvent
     * @return PurchaseEvent
	 */
    static private function mapPurchaseToServerDataGA4($singleEvent) {
        $data = $singleEvent->getData();

        $data = EventsManager::filterEventParams( $data, $singleEvent->getCategory(), [
            'event_id' => $singleEvent->getId(),
            'pixel'    => GA()->getSlug()
        ] );

        $params = $data['params'];
        $purchaseEventData = new PurchaseEvent();
        $purchaseEventData->setValue($params['value'])
            ->setCurrency($params['currency'])
            ->setTransactionId($params['transaction_id']);

	    if(isset($params['tax'])){
		    $purchaseEventData->setTax($params['tax']);
	    }
        if(isset($params['shipping'])){
            $purchaseEventData->setShipping($params['shipping']);
        }
        foreach (self::$uaMap as $val) {
            if(isset($params[$val])) {
                $purchaseEventData->setParamValue($val, $params[$val]);
            }
        }
        foreach ($params['items'] as $item) {
            $purchasedItem = new ItemParameter();
            $purchasedItem
                ->setItemId($item['item_id'])
                ->setItemName($item['item_name'])
                ->setCurrency($params['currency'])
                ->setPrice($item['price'])
                ->setQuantity($item['quantity']);
            if (isset($item['item_category'])) {
                $purchasedItem->setItemCategory($item['item_category']);
            }
            if (isset($item['item_category2'])) {
                $purchasedItem->setItemCategory2($item['item_category2']);
            }
            if (isset($item['item_category3'])) {
                $purchasedItem->setItemCategory3($item['item_category3']);
            }
            if (isset($item['item_category4'])) {
                $purchasedItem->setItemCategory4($item['item_category4']);
            }
            if (isset($item['item_category5'])) {
                $purchasedItem->setItemCategory5($item['item_category5']);
            }
            if(isset($item['variant'])) {
                $purchasedItem->setItemVariant($item['variant']);
            }
            if(isset($item['item_list_name'])) {
                $purchasedItem->setItemListName($item['item_list_name']);
            }
            if(isset($item['item_list_id'])) {
                $purchasedItem->setItemListId($item['item_list_id']);
            }

            if (isset($item['item_brand'])) {
                // Set brand using setItemBrand method
                $purchasedItem->setItemBrand($item['item_brand']);
            }
            $purchaseEventData->addItem($purchasedItem);
        }
        if(isset($params['advanced_purchase_tracking']) && !empty($params['advanced_purchase_tracking'])) {
            $purchaseEventData->setParamValue('advanced_purchase_tracking', $params['advanced_purchase_tracking']);
        }

        // Get filter value
        $custom_filter_data_event = apply_filters('pys_event_data',array(),$singleEvent->getCategory(),[
            'event_id'=>$singleEvent->getId(),
            'pixel'=>GA()->getSlug()
        ]);

        if(isset($custom_filter_data_event['params']) && !empty($custom_filter_data_event['params'])) {
            foreach ($custom_filter_data_event['params'] as $key => $value) {
                $purchaseEventData->setParamValue($key, $value);
            }
        }

        return $purchaseEventData;
    }
    static private function mapRefundToServerDataGA4($singleEvent) {
        $data = $singleEvent->getData();
        $params = $data['params'];
        $refundEventData = new RefundEvent();
        $refundEventData->setValue($params['value'])
            ->setCurrency($params['currency'])
            ->setTransactionId($params['transaction_id']);

        foreach (self::$uaMap as $val) {
            if(isset($params[$val])) {
                $refundEventData->setParamValue($val, $params[$val]);
            }
        }

        return $refundEventData;
    }

    public static function getClientId(){
        $clientID = null;

        if (isset($_COOKIE['_ga']) && !empty($_COOKIE['_ga'])) {
            $cookieValue = sanitize_text_field($_COOKIE['_ga']);
            $cookieParts = explode('.', $cookieValue);
            $clientID = $cookieParts[2] . '.' . $cookieParts[3];
        }
        return $clientID;
    }

    /**
     * Parse GA4 cookies: _ga and _ga_<MEASUREMENT_ID>
     *
     * @return array|null
     */
    public static function parseGaCookies()
    {
        $result = [
            'clientId' => null,
            'sessions'  => []
        ];

        // 1. Parse _ga (clientId)
        if (!empty($_COOKIE['_ga'])) {
            // Example: GA1.2.1234567890.1694000000
            $parts = explode('.', $_COOKIE['_ga']);
            if (count($parts) === 4) {
                $cid1 = $parts[2]; // 1234567890
                $cid2 = $parts[3]; // 1694000000
                $result['clientId'] = $cid1 . '.' . $cid2;
            }
        }

        // 2. Parse all _ga_<MEASUREMENT_ID>
        foreach ($_COOKIE as $name => $value) {
            if (preg_match('/^_ga_(.+)$/', $name, $matches)) {
                $measurementId = $matches[1]; // e.g. "7J57J7JPK6"

                $sessionData = [
                    'session_id'     => null,
                    'session_number' => null,
                    'last_event'     => null,
                ];

                // Split by $
                $parts = explode('$', $value);
                
                foreach ($parts as $part) {
                    // Format: GS2.1.s1758214452 or s1758214452 (session_id)
                    if (preg_match('/s(\d+)/', $part, $m)) {
                        $sessionData['session_id'] = (int)$m[1];
                    }
                    // Format: g1 (session_number) 
                    if (preg_match('/^g(\d+)$/', $part, $m)) {
                        $sessionData['session_number'] = (int)$m[1];
                    }
                    // Format: t1758217068 (last_event timestamp)
                    if (preg_match('/^t(\d+)$/', $part, $m)) {
                        $sessionData['last_event'] = (int)$m[1];
                    }
                }

                $result['sessions'][$measurementId] = $sessionData;
            }
        }

        return $result;
    }

    /**
     * Get GA data from order
     * 
     * Supports both new GA4 data structure and old structure for backward compatibility
     * 
     * Usage examples:
     * - getGAStatFromOrder('clientId', $order_id, 'woo') - get client_id
     * - getGAStatFromOrder('session_id', $order_id, 'woo', 'G-XXXXXXXXXX') - get session_id for specific measurement_id
     * 
     * @param string $key Data key ('clientId', 'session_id', etc.)
     * @param string $order_id Order ID
     * @param string $type Order type ('woo' or 'edd')
     * @param string|null $measurement_id GA4 Measurement ID (required for session_id)
     * @return string
     */
    public static function getGAStatFromOrder($key, $order_id, $type, $measurement_id = null) {
        $cleanMeasurementId = $measurement_id ? str_replace('G-', '', $measurement_id) : null;
        $gaCookie = self::getOrderMeta($type, $order_id, 'pys_ga_cookie');

        // If gaCookie is empty, return empty string
        if (empty($gaCookie) || !is_array($gaCookie)) {
            return '';
        }

        // Session values - always take from current order
        if (in_array($key, ['session_id', 'session_number'], true) &&
            $cleanMeasurementId &&
            isset($gaCookie['sessions'][$cleanMeasurementId][$key]))
        {
            return (string) $gaCookie['sessions'][$cleanMeasurementId][$key];
        }

        // Client ID - for renewals/refunds try to get from parent order
        if ($key === 'clientId') {
            // First check current order
            if (!empty($gaCookie['clientId'])) {
                return (string) $gaCookie['clientId'];
            }

            // If clientId is empty, try to get from parent order
            $parentClientId = self::getParentClientId($type, $order_id);
            if ($parentClientId) {
                return $parentClientId;
            }

            // If not found in parent order either, generate new one
            $clientID = EventIdGenerator::guidv4();
            $data = $gaCookie ?: [];
            $data['clientId'] = $clientID;
            self::updateOrderMeta($type, $order_id, 'pys_ga_cookie', $data);
            return $clientID;
        }

        // Old format
        if (!empty($gaCookie[$key])) {
            return (string) $gaCookie[$key];
        }

        return '';
    }

    private static function getOrderMeta($type, $order_id, $key) {
        if ($type === 'woo') {
            $order = wc_get_order($order_id);
            return $order ? $order->get_meta($key, true) : null;
        }
        if ($type === 'edd') {
            return edd_get_order_meta($order_id, $key, true);
        }
        return null;
    }

    /**
     * Get clientId from parent order for renewals/refunds
     * 
     * @param string $type Order type ('woo' or 'edd')
     * @param string $order_id Order ID
     * @return string|null
     */
    private static function getParentClientId($type, $order_id) {
        if ($type === 'woo') {
            $order = wc_get_order($order_id);
            if (!$order) {
                return null;
            }

            $parent_id = $order->get_parent_id();
            if ($parent_id) {
                $parent_order = wc_get_order($parent_id);
                if ($parent_order) {
                    $parentGaCookie = $parent_order->get_meta('pys_ga_cookie', true);
                    if (!empty($parentGaCookie['clientId'])) {
                        return $parentGaCookie['clientId'];
                    }
                }
            }
        }
        
        if ($type === 'edd') {
            $order = edd_get_order($order_id);
            if ($order && ($order->status === 'edd_subscription' || $order->type === 'refund')) {
                $parentGaCookie = edd_get_order_meta($order->parent, 'pys_ga_cookie', true);
                if (!empty($parentGaCookie['clientId'])) {
                    return $parentGaCookie['clientId'];
                }
            }
        }
        
        return null;
    }

    private static function updateOrderMeta($type, $order_id, $key, $value) {
        if ($type === 'woo') {
            $order = wc_get_order($order_id);
            if ($order) {
                $order->update_meta_data($key, $value);
                $order->save();
            }
        }
        if ($type === 'edd') {
            edd_update_payment_meta($order_id, $key, $value);
        }
    }
}