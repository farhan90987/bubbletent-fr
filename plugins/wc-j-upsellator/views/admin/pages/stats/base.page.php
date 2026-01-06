<?php

if (!defined('ABSPATH') || !is_admin()) {
    exit;
}

$to = date('Y-m-d');
$start = date('Y-m-01');

?>

<div class="woo-upsellator-admin-page">

        <?php woo_j_render_admin_view('/partials/plugin_description', [ 'current_page' => $_GET['page'] ])  ?> 
        
        <?php woo_j_render_admin_view('/partials/stats_nav_bar', [ 'current_page' => sanitize_text_field( $_GET['page'] ), 'tab' => sanitize_text_field( $_GET['tab'] ) ])  ?>

        <div class="woo-upsellator-admin-content">

                <form action="/wp-json/wjufw/v1/orders" data-api="true" method="GET" data-security="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ) ?>" id="getStats" data-event="wooj:stats:received" class="wjufc-ajax woo-upsellator-admin-content flex-row-between">

                        <div class="stats-filters flex-row-start">
                                <div class="stats-filter has-attribute">
                                        <strong><?php _e( 'from', 'woo_j_cart' ) ?></strong>
                                        <i class="wooj-icon-calendar"></i>
                                        <input type="text" class="wc-j-datepicker date-select" name="from" value="<?php echo $start ?>"/>
                                </div>

                                <div class="stats-filter has-attribute">
                                        <strong><?php _e( 'to', 'woo_j_cart' ) ?></strong>
                                        <i class="wooj-icon-calendar"></i>
                                        <input type="text" class="wc-j-datepicker date-select" name="to" value="<?php echo $to ?>"/>
                                </div>

                                <div class="vs flex-row-center">VS</div>

                                <div class="stats-filter has-attribute">
                                        <strong><?php _e( 'from', 'woo_j_cart' ) ?></strong>
                                        <i class="wooj-icon-calendar"></i>
                                        <input type="text" class="wc-j-datepicker date-select" name="from_vs"/>
                                </div>

                                <div class="stats-filter has-attribute">
                                        <strong><?php _e( 'to', 'woo_j_cart' ) ?></strong>
                                        <i class="wooj-icon-calendar"></i>
                                        <input type="text" class="wc-j-datepicker date-select" name="to_vs"/>
                                </div>

                                <div class="stats-filter has-attribute">
                                        <strong><?php _e( 'order status', 'woo_j_cart' ) ?></strong>
                                        
                                        <select name="order-status">
                                                <option value="wc-processing,wc-completed"><?php _e( 'completed/processing', 'woo_j_cart' ) ?></option>
                                                <option value="all"><?php _e( 'all', 'woo_j_cart' ) ?></option>
                                                <option value="wc-completed"><?php _e( 'completed', 'woo_j_cart' ) ?></option>
                                                <option value="wc-processing"><?php _e( 'processing', 'woo_j_cart' ) ?></option>
                                                <option value="wc-cancelled"><?php _e( 'cancelled', 'woo_j_cart' ) ?></option>                                        
                                        </select>
                                </div>
                        </div>

                        <button class="row-submit blue" type="submit" style="margin-top:0px;"> 
                                <i class="icon-list-add"></i>
                                <?php _e( 'Filter', 'woo_j_cart' ) ?>
                        </button>  
                        
                </form> 

                <div class="woo-upsellator-admin-line">
                        <?php _e( 'Summary', 'woo_j_cart' ) ?>
                </div>        
                
                <section class="stats-result flex-row-between" style="margin-top:10px;">

                
                        <div class="block third order_count_chart">

                                        <div class="block-header flex-row-between">
                                                <div class="title"><?php _e( 'Orders', 'woo_j_cart' ) ?></div> 
                                                
                                                <div class="comparison-value">
                                                        <span class="statvalue"></span>
                                                        <span class="percentage">%</span>
                                                </div>
                                                                                
                                        </div>

                                        <div class="canvas-container">
                                                <canvas class="stats-chart" id="order_count_chart" height="70px"></canvas>
                                        </div>

                                        <div class="block-footer">
                                                <?php _e( 'orders during the selected period', 'woo_j_cart' ) ?>
                                        </div>
                                
                        </div>

                        <div class="block third order_total_chart">

                                        <div class="block-header flex-row-between">
                                                <div class="title"><?php _e( 'Total', 'woo_j_cart' ) ?></div>
                                        
                                                <div class="comparison-value">
                                                        <span class="statvalue"></span>
                                                        <span class="percentage">%</span>
                                                </div>
                                                                                
                                        </div>

                                        <div class="canvas-container">
                                                <canvas class="stats-chart" id="order_total_chart" height="70px"></canvas>
                                        </div>

                                        <div class="block-footer">
                                                <?php _e( 'Total amount', 'woo_j_cart' ) ?>       
                                        </div>               
                                
                        </div>

                        <div class="block third total_sold_products_chart">
                        
                                        <div class="block-header flex-row-between">
                                                <div class="title"><?php _e( 'Sold products', 'woo_j_cart' ) ?></div>  
                                                
                                                <div class="comparison-value">
                                                        <span class="statvalue"></span>
                                                        <span class="percentage">%</span>                                                                
                                                </div>
                                                                                
                                        </div>

                                        <div class="canvas-container">
                                                <canvas class="stats-chart" id="total_sold_products_chart" height="70px"></canvas>
                                        </div>

                                        <div class="block-footer">
                                                <?php _e( 'N. of sold products', 'woo_j_cart' ) ?>       
                                        </div>                                 
                        </div>                
                        
                </section> 

                <section class="stats-result flex-row-between">

                
                        <div class="block third total_gain_chart">

                                        <div class="block-header flex-row-between">
                                                <div class="title"><?php _e( 'Upsell gain', 'woo_j_cart' ) ?></div>                                                                                 
                                        </div>

                                        <div class="canvas-container">
                                                <canvas class="stats-chart" id="total_gain_chart" height="70"></canvas>
                                        </div>

                                        <div class="block-footer">
                                                <?php _e( 'total increase thanks to upsells', 'woo_j_cart' ) ?>     
                                        </div> 
                                
                        </div>

                        <div class="block third upsell_total_chart">                            

                                <div class="block-header flex-row-between">
                                        <div class="title"><?php _e( 'Upsell total', 'woo_j_cart' ) ?></div>
                                        
                                        <div class="comparison-value">
                                                <span class="statvalue"></span>
                                                <span class="percentage">%</span>
                                        </div>
                                                                        
                                </div>

                                <div class="canvas-container">
                                        <canvas class="stats-chart" id="upsell_total_chart" height="70"></canvas>
                                </div>

                                <div class="block-footer">
                                        <?php _e( 'Total sold via upsell', 'woo_j_cart' ) ?>       
                                </div> 
                        </div>               

                        <div class="block third total_sold_upsells_chart">

                                        <div class="block-header flex-row-between">
                                                <div class="title"><?php _e( 'Sold upsells', 'woo_j_cart' ) ?></div> 
                                                
                                                <div class="comparison-value">
                                                        <span class="statvalue"></span>     
                                                        <span class="percentage">%</span>                                        
                                                </div>
                                                                                
                                        </div>

                                        <div class="canvas-container">
                                                <canvas class="stats-chart" id="total_sold_upsells_chart" height="70px"></canvas>                            
                                        </div>

                                        <div class="block-footer">
                                                <?php _e( 'N. of sold products via upsell', 'woo_j_cart' ) ?>       
                                        </div>                                 
                        </div>
                        

                </section> 

                <div class="woo-upsellator-admin-line">
                        <?php _e( 'Average order', 'woo_j_cart' ) ?>
                </div>

                <section class="stats-result flex-row-between" style="margin-top:10px;">

                
                        <div class="block third average_total_chart">

                                        <div class="block-header flex-row-between">
                                                <div class="title"><?php _e( 'Average order total', 'woo_j_cart' ) ?></div> 
                                                
                                                <div class="comparison-value">
                                                        <span class="statvalue"></span>
                                                        <span class="percentage">%</span>                                                          
                                                </div>
                                                                                
                                        </div>
                                        
                                        <div class="canvas-container">
                                                <canvas class="stats-chart" id="average_total_chart" height="70"></canvas>
                                        </div>

                                        <div class="block-footer">
                                                <?php _e( 'average order total in the selected period', 'woo_j_cart' ) ?>      
                                        </div> 
                                        
                        </div>

                        <div class="block third average_upsell_chart">

                                        <div class="block-header flex-row-between">
                                                <div class="title"><?php _e( 'Average order upsell', 'woo_j_cart' ) ?></div> 
                                                
                                                <div class="comparison-value">
                                                        <span class="statvalue"></span>
                                                        <span class="percentage">%</span>                                                           
                                                </div>
                                                                                
                                        </div>

                                        <div class="canvas-container">
                                                <canvas class="stats-chart" id="average_upsell_chart" height="70"></canvas>
                                        </div>

                                        <div class="block-footer">
                                                <?php _e( 'average upsell in the selected period', 'woo_j_cart' ) ?>      
                                        </div>                             
                        </div>
                        <div class="block third"></div>

                </section>    

                <section class="stats-result-container">

                        <section class="stats-result stats-result--upsellgift  upsells-list flex-column-center">              

                                <div class="woo-j-stats-header flex-row-between">
                                                <div class="flex-row-start inner-row">                                             
                                                        <div class="product-name">Upsell</div>
                                                </div>
                                                <div class="flex-row-end inner-row">
                                                        <div class="qty"><?php _e( 'Qty', 'woo_j_cart' ) ?></div>
                                                        <div class="total"><?php _e( 'Sold total', 'woo_j_cart' ) ?></div>
                                                </div>
                                </div>

                                <div class="upsells-list--list stats-result--list w-100">

                                </div>

                                <div class="upsells-list--empty woo-j-stats-row hidden w-100">
                                        <?php _e( 'No upsell sold in the selected period', 'woo_j_cart' ) ?>
                                </div>

                                <div class="woo-j-stats-header footer flex-row-between">
                                                <div class="flex-row-start inner-row">                                     
                                                        <div class="product-name"><?php _e('Total', 'woo_j_cart' ) ?></div>        
                                                </div>
                                                <div class="flex-row-end inner-row">
                                                        <div class="qty">0</div> 
                                                        <div class="total">
                                                                <span class="value">0</span>
                                                                <span class="currency"></span>
                                                        </div>                                              
                                                </div>
                                </div>

                        </section>   

                        <section class="stats-result stats-result--upsellgift gifts-list flex-column-center">              

                                <div class="woo-j-stats-header flex-row-between">
                                                <div class="flex-row-start inner-row">                                     
                                                        <div class="product-name"><?php _e( 'Gift', 'woo_j_cart' ) ?></div>
                                                </div>
                                                <div class="flex-row-end inner-row">
                                                        <div class="qty"><?php _e( 'Qty', 'woo_j_cart' ) ?></div> 
                                                        <div class="total"><?php _e( 'Total value', 'woo_j_cart' ) ?></div>                                              
                                                </div>
                                </div>

                                <div class="gifts-list--list stats-result--list w-100">

                                </div>

                                <div class="gifts-list--empty woo-j-stats-row hidden w-100">
                                        <?php _e( 'No gifts given in the selected period', 'woo_j_cart' ) ?>
                                </div>

                                <div class="woo-j-stats-header footer flex-row-between">
                                                <div class="flex-row-start inner-row">                                     
                                                <div class="product-name"><?php _e('Total', 'woo_j_cart' ) ?></div>   
                                                </div>
                                                <div class="flex-row-end inner-row">
                                                        <div class="qty">0</div> 
                                                        <div class="total">
                                                                <span class="value">0</span>
                                                                <span class="currency"></span>
                                                        </div>                                               
                                                </div>
                                </div>
                        </section>  


                </section>
        </div>
</div>