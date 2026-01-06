<?php

namespace WcJUpsellator\Api;

use WcJUpsellator\Statistics\Orders;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class StatsApi extends Api
{  

    public function __construct()
    {
            /*
            /* Nothing to do
            */ 
    }

    public static function register()
    {
        $self = new self();

        add_action( 'rest_api_init', array( $self, 'registerStatsApiRoute' ) );
        
    }

    public function registerStatsApiRoute()
    {
        register_rest_route( self::API_NAMESPACE . self::API_VERSION, '/orders/', array(
            'methods' => \WP_REST_Server::READABLE,
            "permission_callback" => function() {                               
                return current_user_can('manage_options');
            },
            'callback' => array( $this, 'getStats' ),
            'args' => $this->routeArgs()            
        ));
    }

    public function getStats( $request )
    {
        $orders         = new Orders();
        $vs             = false;

        $orders->setFrom( $request['from'] );
        $orders->setTo( $request['to'] );
        $orders->setOrderStatus( $request['order-status']  );
       
        $orders->getAll();

        if( !empty( $request['from_vs'] ) && !empty( $request['to_vs'] ) )
        {
            if( $request['from'] != $request['from_vs'] || $request['to'] != $request['to_vs']  )
            {
                $vs              = true;
                $orders2         = new Orders();            
                
                $orders2->setFrom( $request['from_vs'] );
                $orders2->setTo( $request['to_vs'] );
                $orders2->setOrderStatus( $request['order-status']  );
                
                $orders2->getAll();
            }
           
        }

        return new \WP_REST_Response( [
            'body' => [
                'period_1' => $this->generatePeriodResponse( $orders ),
                'period_2' => $vs ? $this->generatePeriodResponse( $orders2 ) : [],
                'comparison' => $vs
            ]                 
        ], 200 );
    }

    public function routeArgs()
    {
        $args = array(); 
     
        $args['from'] = array(            
            'type'              => 'date',    
            'sanitize_callback' => array( $this, 'sanitizeInput' ),      
            'required'          => true,
            'regex'             => self::DATE_VALIDATION
        );

        $args['to'] = array(            
            'type'              => 'date',    
            'sanitize_callback' => array( $this, 'sanitizeInput' ),      
            'required'          => true,
            'regex'             => self::DATE_VALIDATION
        );

        $args['from_vs'] = array(            
            'type'              => 'date',    
            'sanitize_callback' => array( $this, 'sanitizeInput' ),
            'regex'             => self::DATE_VALIDATION
        );

        $args['to_vs'] = array(            
            'type'              => 'date',    
            'sanitize_callback' => array( $this, 'sanitizeInput' ), 
            'regex'             => self::DATE_VALIDATION
        );

        $args['order-status'] = array(            
            'type'              => 'string',    
            'sanitize_callback' => array( $this, 'sanitizeInput' ), 
        );
       
        return $args;
    }

    private function generatePeriodResponse( $orders )
    {
        return [
            'from' => $orders->getFrom(),
            'to' => $orders->getTo(),
            'status' => $orders->orderStatus,
            'orders_total' => $orders->orders_total,
            'orders_count' => $orders->orders_count,
            'order_average' => $orders->getAverageOrder(),
            'upsell_total' => $orders->upsellTotal,
            'upsell_count' => $orders->upsellCount,
            'upsell_average' => $orders->getAverageUpsell(),
            'upsell_gain' => $orders->getAverageIncrease(),
            'items_count' => $orders->itemsCount,
            'upsells' => array_values( $orders->upsells ),
            'gifts' => array_values( $orders->gifts )
        ];
    }    
 }