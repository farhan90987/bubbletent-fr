<?php 

use WcJUpsellator\Render;

add_shortcode('wjcfw_upsells','wjcfw_upsells');

function wjcfw_upsells( $atts )
{   
    
    ob_start();	

    ( new Render\UpsellsBlock() )->render( 'cart', 'cart' );  

    $result = ob_get_clean();
    return $result;

}


?>