<?php

namespace WcJUpsellator\Statistics;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Orders
{
	public $from;
	public $to;

	public $status 			= false;
	public $orders			= [];
	public $upsells 		= [];
	public $gifts 			= [];

	public $orders_total    = 0;
	public $upsellTotal 	= 0;
	public $upsellCount 	= 0;
	public $itemsCount  	= 0;
	public $averageTotal 	= 0;
	public $averageUpsell 	= 0;
	public $orderStatuses 	= [];
	public $orders_count 	= 0;
	public $gain 			= 0;
	public $decimals 		= 2;

	private $valid_status = ['wc-processing', 'wc-completed', 'wc-cancelled'];

	public function __construct()
	{
		   $this->from 		= date("Y-m-d", strtotime("-1 months"));
		   $this->to 		= date("Y-m-d");
		   $this->decimals 	= wc_get_price_decimals() ?? 2;
	}

	public function getAll()
    {
		
		global $wpdb;

		$this->status = true;
		
		$this->checkDates();				

		$date = new \DateTime( $this->to );	
		
		$orders   = $wpdb->get_results( 
					$wpdb->prepare("select 
					p.ID as order_id,
					max( CASE WHEN psm.meta_key = '_order_total' THEN psm.meta_value END ) as total,
					max( CASE WHEN psm.meta_key = '_order_tax' THEN psm.meta_value END ) as total_tax					
					from {$wpdb->prefix}posts as p 
					LEFT JOIN {$wpdb->prefix}postmeta psm on psm.post_id = p.id 
					WHERE p.post_type = 'shop_order'					
					AND p.post_date between %s AND %s
					AND p.post_type = 'shop_order'
					AND p.post_status IN ('". implode("','", $this->orderStatuses ) ."')
					AND p.post_status <> 'trash'
					GROUP BY order_id
					", $this->from.' 00:01', $date->format('Y-m-d 23:59')) 
				);		
	
		$products = $wpdb->get_results( 
					$wpdb->prepare("select
					po.post_date,
					po.post_status,
					p.order_id,					
					p.order_item_id,
					p.order_item_name,
					p.order_item_type,
					psm.meta_value as product_price,
					max( CASE WHEN pm.meta_key = '_product_id' and p.order_item_id = pm.order_item_id THEN pm.meta_value END ) as productID,
					max( CASE WHEN pm.meta_key = '_qty' and p.order_item_id = pm.order_item_id THEN pm.meta_value END ) as Qty,
					max( CASE WHEN pm.meta_key = '_variation_id' and p.order_item_id = pm.order_item_id THEN pm.meta_value END ) as variationID,
					max( CASE WHEN pm.meta_key = '_line_total' and p.order_item_id = pm.order_item_id THEN pm.meta_value END ) as lineTotal,				
					max( CASE WHEN pm.meta_key = '_line_subtotal' and p.order_item_id = pm.order_item_id THEN pm.meta_value END ) as subtotal,
					max( CASE WHEN pm.meta_key = '_woo_j_upsellator_upsell' and p.order_item_id = pm.order_item_id THEN pm.meta_value END ) as upsell,
					max( CASE WHEN pm.meta_key = '_woo_j_upsellator_gift' and p.order_item_id = pm.order_item_id THEN pm.meta_value END ) as gift
					from {$wpdb->prefix}woocommerce_order_items as p 					
					LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as pm on pm.order_item_id = p.order_item_id
					LEFT JOIN {$wpdb->prefix}postmeta psm on psm.post_id = ( CASE WHEN pm.meta_key = '_product_id' THEN pm.meta_value END ) AND psm.meta_key = '_price' 
					LEFT JOIN {$wpdb->prefix}posts po on po.ID = p.order_id
					where p.order_item_type = 'line_item'
					AND p.order_item_id = pm.order_item_id
					AND po.post_date between %s AND %s
					AND po.post_status IN ('". implode("','", $this->orderStatuses ) ."')
					AND po.post_status <> 'trash'
					group by
					p.order_item_id", $this->from.' 00:01', $date->format('Y-m-d 23:59') ) 
				);
				
		$this->parseProducts( $products );
		$this->parseOrders( $orders );			
		
	}
	/*
	/* If from > to, we swap
	*/
	private function checkDates()
	{	
		if( strtotime( $this->from ) > strtotime( $this->to )  )
		{			
			[ $this->from, $this->to ] = [ $this->to, $this->from ];
		}
	}
	/*
	/* Setters
	*/
	public function setFrom( $from )
	{
		$this->from = $from;		
	}

	public function setTo( $to )
	{
		$this->to = $to;
	}	
	public function setOrderStatus( $status )
	{			
		$statuses = explode( ',' , $status );

		foreach( $statuses as $stat )
		{
			if( in_array( $stat, $this->valid_status ))
			{
				$this->orderStatuses[] = $stat;

			}
		}

		if( $status == 'all' ) $this->orderStatuses = $this->valid_status;
			
	}
	/*
	/* Getters
	*/
	public function getFrom()
	{
		return $this->from;
	}

	public function getTo()
	{
		return $this->to;
	}	
	public function setOnlyCompleted()
	{
		$this->onlyCompleted = true;
	}					
	/*
	/* Average increase due to upsell
	*/
	public function getAverageIncrease():float
	{	
		$averageUpsell = $this->getAverageUpsell();
		$averageOrder  = $this->getAverageOrder();

		if( $averageOrder <= 0 ) return 0;

		$delta 		= $averageOrder - $averageUpsell;
		return ( $averageUpsell / $delta  ) * 100;

	}
	/*
	/* Average upsell
	*/
	public function getAverageUpsell():float
	{
		if( !$this->orders_count ) return 0;

		return round( $this->upsellTotal / $this->orders_count, $this->decimals );
	}	
	/*
	/* Average order
	*/
	public function getAverageOrder():float
	{
		if( !$this->orders_count ) return 0;

		return round( $this->orders_total / $this->orders_count, $this->decimals );
	}	

	private function parseOrders( $data ):void
	{
		if( empty( $data ) ) return;

		foreach( $data as $row ): 
			
			$this->orders_total += round( $row->total - $row->total_tax, $this->decimals );
			$this->orders_count++;

		endforeach;

	}
	
	private function parseProducts( $data ):void
	{
		foreach( $data as $row ): 

			if( !empty( $row->upsell ) ) $this->parseUpsell( $row );
			if( !empty( $row->gift ) )   $this->parseGift( $row );			
			
			$this->itemsCount += $row->Qty;			

		endforeach;
	}

	private function parseUpsell( $row ):void
	{

			if( !array_key_exists( $row->productID, $this->upsells ))
			{
					$upsell 					= [];
					$upsell['id'] 				= $row->productID;
					$upsell['name'] 			= $row->order_item_name;
					$upsell['qty']  			= $row->Qty;
					$upsell['total'] 			= $row->subtotal;
					$upsell['default_price'] 	= $row->product_price;
					$upsell['thumbnail'] 		= wp_get_attachment_image_src( get_post_thumbnail_id( $row->productID ), 'thumbnail' )[ 0 ] ?? '';

					$this->upsells[ $row->productID ] = $upsell;
			}else{

				$this->upsells[ $row->productID ]['qty'] += $row->Qty;
				$this->upsells[ $row->productID ]['total'] += $row->subtotal;
			}

			$this->upsellTotal += round( $row->subtotal, $this->decimals );
			$this->upsellCount += $row->Qty;

	}

	private function parseGift( $row ):void
	{

			if( !array_key_exists( $row->productID, $this->gifts ))
			{
					$gift 					= [];
					$gift['id'] 			= $row->productID;
					$gift['name'] 			= $row->order_item_name;
					$gift['qty']  			= $row->Qty;
					$gift['total']  		= $row->product_price * $row->Qty;
					$gift['default_price'] 	= $row->product_price;
					$gift['thumbnail'] 		= wp_get_attachment_image_src( get_post_thumbnail_id( $row->productID ), 'thumbnail' )[ 0 ] ?? '';

					$this->gifts[ $row->productID ] = $gift;
			}else{

				$this->gifts[ $row->productID ]['qty']   += $row->Qty;
				$this->gifts[ $row->productID ]['total'] += $row->product_price * $row->Qty;
			}			

	}
}