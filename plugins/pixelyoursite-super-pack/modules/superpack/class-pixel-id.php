<?php
namespace PixelYourSite\SuperPack;

use JetBrains\PhpStorm\ArrayShape;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class SPPixelId {
   public $pixel = '';
   public $isFireForSignal = true;
   public $isFireForWoo = true;
   public $isFireForEdd = true;
   public $isEnable = true;

    public $isHide = false;
    public $hideCondition = array();
    public $hideTime = '';

    public $isHideByUrl = false;
    public $hideConditionByUrl = array();

    public $isUseServerApi = false;
    public $server_access_api_token = '';
    public $enable_server_container = false;
    public $send_page_view = true;
    public $first_party_collection = true;
    public $server_container_url = '';
    public $transport_url = '';
   public $wpmlActiveLang = null;

   public $logicConditionalTrack = 'track';
   public $displayConditions = [["name"=>"all_site"]]; // [name=>'',sub_name=>'',sub_id=>'',sub_id_name=>'']
   public $extensions = [];

    /**
     * @param $json
     * @return SPPixelId
     */
   static function fromArray($json) {
       $pixel = new SPPixelId();
       $pixel->pixel = isset($json['pixel_id']) ? $json['pixel_id'] : $pixel->pixel;
       $pixel->isFireForSignal = isset($json['is_fire_signal']) ? $json['is_fire_signal'] : $pixel->isFireForSignal;
       $pixel->isFireForWoo = isset($json['is_fire_woo']) ? $json['is_fire_woo'] : $pixel->isFireForWoo;
       $pixel->isFireForEdd = isset($json['is_fire_edd']) ? $json['is_fire_edd'] : $pixel->isFireForEdd;
       $pixel->wpmlActiveLang = isset($json['wpml_active_lang']) ? $json['wpml_active_lang'] : $pixel->wpmlActiveLang;
       $pixel->logicConditionalTrack = isset($json['logic_conditional_track']) ? $json['logic_conditional_track'] : $pixel->logicConditionalTrack;
	   $pixel->displayConditions = !empty($json['condition']) ? $json['condition'] : $pixel->displayConditions;
       $pixel->extensions = isset($json['extensions']) ? $json['extensions'] : $pixel->extensions;
       $pixel->isEnable = isset($json['is_enable']) ? $json['is_enable'] : $pixel->isEnable;
       $pixel->isUseServerApi = isset($json['use_server_api']) ? $json['use_server_api'] : $pixel->isUseServerApi;
       $pixel->server_access_api_token = isset($json['server_access_api_token']) ? $json['server_access_api_token'] : $pixel->server_access_api_token;

       $pixel->enable_server_container = isset($json['enable_server_container']) ? $json['enable_server_container'] : $pixel->enable_server_container;
       $pixel->server_container_url = isset($json['server_container_url']) ? $json['server_container_url'] : $pixel->server_container_url;
       $pixel->transport_url = isset($json['transport_url']) ? $json['transport_url'] : $pixel->transport_url;
       $pixel->send_page_view = isset($json['send_page_view']) ? $json['send_page_view'] : $pixel->send_page_view;
       $pixel->first_party_collection = isset($json['first_party_collection']) ? $json['first_party_collection'] : $pixel->first_party_collection;

       $pixel->isHide = isset($json['is_hide']) ? $json['is_hide'] : $pixel->isHide;
       $pixel->hideCondition = isset($json['hide_condition']) ? $json['hide_condition'] : $pixel->hideCondition;
       $pixel->hideTime = isset($json['hide_time']) ? (float)$json['hide_time'] : (float)$pixel->hideTime;
       $pixel->isHideByUrl = isset($json['is_hide_url']) ? $json['is_hide_url'] : $pixel->isHideByUrl;
       $pixel->hideConditionByUrl = isset($json['hide_condition_url']) ? $json['hide_condition_url'] : $pixel->hideConditionByUrl;
       $pixel->isHidePixel();
       return $pixel;
   }


    /**
     * @return bool
     */
    function isValidForCurrentLang() {
        if(isWPMLActive()) {
            $current_lang_code = apply_filters( 'wpml_current_language', NULL );
            if(is_array($this->wpmlActiveLang) && !in_array($current_lang_code,$this->wpmlActiveLang)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    function isHidePixel() {
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }

        $existing_hide_pixels = apply_filters('hide_pixels', []);
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $full_url = $protocol . $host . $request_uri;

        if (isset($_COOKIE['hide_tag_' . $this->pixel])) {
            $existing_hide_pixels[] = $this->pixel;
        }

        if ($this->isHideByUrl) {
            $hide_keywords = $this->hideConditionByUrl ?? [];
            foreach ($hide_keywords as $keyword) {
                if (!empty($keyword) && stripos($full_url, $keyword) !== false) {
                    $existing_hide_pixels[] = $this->pixel;
                    break;
                }
            }
        }

        if ($existing_hide_pixels) {
            add_filter('hide_pixels', fn() => array_unique($existing_hide_pixels));
        }
    }

    /**
     * @param SPPixelId $pixel
     * @return array
     */
    static function toArray ($pixel) {
        return  [
            "pixel_id" => $pixel->pixel,
            "is_fire_signal" => $pixel->isFireForSignal,
            "is_fire_woo" => $pixel->isFireForWoo,
            "is_fire_edd" => $pixel->isFireForEdd,
            "wpml_active_lang" => $pixel->wpmlActiveLang,
            "logic_conditional_track" => $pixel->logicConditionalTrack,
            "condition" => $pixel->displayConditions,
            "extensions" => $pixel->extensions,
            "is_enable" => $pixel->isEnable,
            "is_hide" => $pixel->isHide,
            "hide_condition" => $pixel->hideCondition,
            "hide_time" => (float)$pixel->hideTime,
            "is_hide_url" => $pixel->isHideByUrl,
            "hide_condition_url" => $pixel->hideConditionByUrl
        ];
    }


    /**
     * @param \PixelYourSite\PYSEvent $event
     * @param String $type
     * @param array $args
     * @return bool
     */
    public function isValidForEvent($event,$args = [], $isJustDataLayer = false) {

        if(!$this->isEnable || ($this->pixel == '' && !$isJustDataLayer) ) {
            return false;
        }

        if(!$this->isFireForEdd && $event->getCategory() == 'edd') {
            return false;
        }

        if(!$this->isFireForWoo && $event->getCategory() == 'woo') {
            return false;
        }
        if(!$this->isFireForSignal && $event->getCategory() == 'automatic') {
            return false;
        }

        if(!$this->isValidForCurrentLang()) {
            return false;
        }

        return true;
    }

    /**
     * @param \PixelYourSite\PYSEvent $event
     * @param String $type
     * @param array $args
     * @return bool
     */
    public function isConditionalValidForEvent($event, $args = [])
    {
        $numPassedConditions = 0;
        foreach ($this->displayConditions as $displayCondition) {

            if ($event->getId() == "woo_add_to_cart_on_button_click" // fix can fire from ajax
                && isset($displayCondition['name']) && $displayCondition['name'] == "woocommerce"
                && isset($displayCondition['sub_name']) && $displayCondition['sub_name'] == "product"
                && isset($displayCondition['sub_id_name']) && $displayCondition['sub_id_name'] == 'All'
            ) {
                continue; // this condition passes, move on to the next
            }

            if (isset($displayCondition['name']) && !(isset($displayCondition['sub_name']) && $displayCondition['sub_name'] != 'all')) {

                $conditional = SpPixelCondition()->getCondition($displayCondition['name']);
                $args = [];
                if (isset($displayCondition)) {
                    $args = $displayCondition;
                }
                if ($conditional && $conditional->check($args)) {
                    $numPassedConditions++;
                }
            }
            if (isset($displayCondition['sub_name']) && $displayCondition['sub_name'] != 'all') {
                $conditional = SpPixelCondition()->getCondition($displayCondition['sub_name']);
                $args = [];
                if (isset($displayCondition['sub_id'])) {
                    $args['id'] = $displayCondition['sub_id'];
                }
                if ($conditional && $conditional->check($args)) {
                    $numPassedConditions++;
                }
            }
            if (isset($displayCondition['options']) && is_array($displayCondition['options'])) {
                $pass_conditional = false;

                foreach ($displayCondition['options'] as $option) {
                    $conditional = SpPixelCondition()->getOption($option);
                    if ($conditional->check()) {
                        $pass_conditional = true;
                    }
                }
                if ($pass_conditional) {
                    $numPassedConditions++;
                }
            }
        }
        if (($this->logicConditionalTrack == 'dont_track' && $numPassedConditions > 0) || ($this->logicConditionalTrack == 'track' && $numPassedConditions == 0)) {
            return false;
        }

        if(($this->logicConditionalTrack == 'track' && $numPassedConditions > 0) || ($this->logicConditionalTrack == 'dont_track' && $numPassedConditions == 0)) {
            return true;
        }

        return true;
    }
    /**
     * @return array{sub_id:int,filter:string}|null
     */

    function getWooFilter(){
        return $this->getFilter('woocommerce');
    }

    function getEddFilter() {
        return $this->getFilter('edd');
    }

    function getFilter($type) {
        $filters = array();
        foreach($this->displayConditions as $displayCondition) {
            if($displayCondition['name'] == $type) {
                if($displayCondition['sub_name'] != "all" && !empty($displayCondition['sub_id']) && $displayCondition['sub_id'] != 'all') {
                    $filters[] = [
                        'sub_id' => $displayCondition['sub_id'],
                        'filter' => $displayCondition['sub_name']
                    ];

                } else {
                    $filters[] = [
                        'sub_id' => -1,
                        'filter' => "all"
                    ];
                }
            }
        }
        return $filters;
    }
}