<?php 

add_shortcode('wjcfw_dynamic_bar','wjcfw_dynamic_bar');

function wjcfw_dynamic_bar( $atts )
{   
    
    if( !woo_j_conf('dynamic_bar_shortcode')  ) return;

    ob_start();	
    
    woo_j_render_view('/shipping_bar/wctimeline_shipping_bar', 
                [
                    'goals' => woo_j_shipping('goals'),
                    'goals_count' => count( woo_j_shipping('goals') ) ?? 0                   
                ] 
    );     

    $result = ob_get_clean();
    return $result;

}


?>