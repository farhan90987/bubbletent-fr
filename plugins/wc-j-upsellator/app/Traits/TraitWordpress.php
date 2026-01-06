<?php

namespace WcJUpsellator\Traits;

trait TraitWordpress
{
    private  $page_to_exclude = array( 'cart', 'checkout' ) ;
    public function getValidPages()
    {
        $excluded_ids = $this->loadExcludedPages();
        $args = array(
            'post_type'      => 'page',
            'post_status'    => array( 'publish', 'draft', 'future' ),
            'posts_per_page' => -1,
        );
        $result = get_pages( $args );
        $pages = [];
        foreach ( $result as $res ) {
            
            if ( !in_array( $res->ID, $excluded_ids ) ) {
                $page = [];
                $page['title'] = $res->post_title;
                $page['id'] = $res->ID;
                $page['status'] = $res->post_status;
                $page['post_date'] = $res->post_date;
                $pages[] = $page;
            }
        
        }
        usort( $pages, function ( $item1, $item2 ) {
            return $item1['title'] <=> $item2['title'];
        } );
        return $pages;
    }
    
    /*
    /* We pre-load some pages excluded by default
    */
    private function loadExcludedPages()
    {
        $excluded = [];
        foreach ( $this->page_to_exclude as $page_to_exclude ) {
            $page = wc_get_page_id( $page_to_exclude );
            if ( $page > 0 ) {
                array_push( $excluded, $page );
            }
        }
        return $excluded;
    }
    
    /*
    /* Get available coupons
    */
    public function getAvailablesCoupons()
    {
        $coupons = [];
        return $coupons;
    }

}