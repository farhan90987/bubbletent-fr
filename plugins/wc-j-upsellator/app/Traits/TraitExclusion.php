<?php

namespace WcJUpsellator\Traits;

trait TraitExclusion
{	
	/*
	/* Check if the current page is not in the exclusion list
	*/
	public function pageExcluded( $page_id = null )
	{		
		$excluded_pages = woo_j_exclusion('pages');
		$page_id 		= $page_id ? $page_id : $this->getRealID();

		if( empty( $excluded_pages ) ) return false;

		if( !woo_j_exclusion('mode') && in_array( $page_id , $excluded_pages )) return true;	 
		if( woo_j_exclusion('mode') && !in_array( $page_id , $excluded_pages )) return true;	
	
		return false;
	}

	private function getRealID()
	{

		if( is_shop() ) return wc_get_page_id('shop');		
		elseif( is_home() || is_archive() )
		{

			$query 					= get_queried_object();			
			return $query->ID ?? '';			
			
		}	
		
		$evalued_id = get_the_ID();		
		return  ( !empty( $evalued_id ) ) ? $evalued_id : 0 ;		
		
	}	
  
}